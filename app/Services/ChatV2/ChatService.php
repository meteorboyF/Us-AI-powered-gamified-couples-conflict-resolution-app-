<?php

namespace App\Services\ChatV2;

use App\Models\ChatV2\Conversation;
use App\Models\ChatV2\Message;
use App\Models\ChatV2\MessageReceipt;
use App\Models\Couple;
use App\Models\User;
use App\Services\XpService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ChatService
{
    private const XP_CHAT_HOURLY_LIMIT = 30;

    public function __construct(
        private readonly XpService $xpService
    ) {}

    public function getOrCreateConversationForCouple(Couple $couple): Conversation
    {
        return Conversation::firstOrCreate([
            'couple_id' => $couple->id,
        ]);
    }

    public function sendMessage(
        Conversation $conversation,
        User $sender,
        string $type,
        ?string $body = null,
        ?UploadedFile $attachment = null,
        ?int $durationMs = null,
        ?int $replyToMessageId = null
    ): Message {
        $this->assertCanAccessConversation($conversation, $sender);

        return DB::transaction(function () use ($conversation, $sender, $type, $body, $attachment, $durationMs, $replyToMessageId) {
            $stored = $this->storeAttachment($conversation, $attachment);

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'type' => $type,
                'body' => $body ? trim($body) : null,
                'media_path' => $stored['path'],
                'media_mime' => $stored['mime'],
                'media_size' => $stored['size'],
                'duration_ms' => $durationMs,
                'reply_to_message_id' => $replyToMessageId,
            ]);

            $recipientIds = $conversation->couple->users()
                ->where('users.id', '!=', $sender->id)
                ->where('couple_user.is_active', true)
                ->pluck('users.id');

            foreach ($recipientIds as $recipientId) {
                MessageReceipt::firstOrCreate([
                    'message_id' => $message->id,
                    'user_id' => $recipientId,
                ]);
            }

            $this->applyChatXp($conversation, $sender, $body);

            return $message->load(['sender', 'receipts']);
        });
    }

    public function markDelivered(Message $message, User $user): MessageReceipt
    {
        $this->assertCanAccessConversation($message->conversation, $user);

        return DB::transaction(function () use ($message, $user) {
            $receipt = MessageReceipt::firstOrCreate([
                'message_id' => $message->id,
                'user_id' => $user->id,
            ]);

            if ($receipt->delivered_at === null) {
                $receipt->forceFill(['delivered_at' => now()])->save();
            }

            return $receipt->fresh();
        });
    }

    public function markRead(Message $message, User $user): MessageReceipt
    {
        $this->assertCanAccessConversation($message->conversation, $user);

        return DB::transaction(function () use ($message, $user) {
            $receipt = MessageReceipt::firstOrCreate([
                'message_id' => $message->id,
                'user_id' => $user->id,
            ]);

            $updates = [];
            if ($receipt->delivered_at === null) {
                $updates['delivered_at'] = now();
            }
            if ($receipt->read_at === null) {
                $updates['read_at'] = now();
            }

            if ($updates !== []) {
                $receipt->forceFill($updates)->save();
            }

            return $receipt->fresh();
        });
    }

    public function assertCanAccessConversation(Conversation $conversation, User $user): void
    {
        $couple = $conversation->couple;

        if (! $couple || ! $couple->isActive()) {
            throw new AuthorizationException('Conversation unavailable.');
        }

        $isMember = $couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();

        if (! $isMember) {
            throw new AuthorizationException('Forbidden.');
        }
    }

    private function storeAttachment(Conversation $conversation, ?UploadedFile $attachment): array
    {
        if (! $attachment) {
            return [
                'path' => null,
                'mime' => null,
                'size' => null,
            ];
        }

        $disk = config('chat_v2.storage_disk', 'public');
        $path = $attachment->store("chat-v2/{$conversation->id}", $disk);

        return [
            'path' => $path,
            'mime' => $attachment->getMimeType(),
            'size' => $attachment->getSize(),
        ];
    }

    private function applyChatXp(Conversation $conversation, User $sender, ?string $body): void
    {
        $text = trim((string) $body);
        if (mb_strlen($text) < 4) {
            return;
        }

        $countInLastHour = Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', $sender->id)
            ->whereNotNull('body')
            ->whereRaw('LENGTH(TRIM(body)) >= 4')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($countInLastHour > self::XP_CHAT_HOURLY_LIMIT) {
            return;
        }

        $this->xpService->awardXp(
            $conversation->couple,
            'chat',
            $sender,
            2,
            ['reason' => 'chat_message_sent']
        );
    }
}
