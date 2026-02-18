<?php

namespace App\Http\Controllers;

use App\Domain\Chat\ChatThreadResolver;
use App\Events\Chat\MessageDeleted;
use App\Events\Chat\MessageSent;
use App\Events\Chat\ReadReceiptUpdated;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function thread(Request $request, ChatThreadResolver $resolver): JsonResponse
    {
        $this->ensureFeatureEnabled();

        $chat = $resolver->resolveForUser($request->user());
        $this->authorize('view', $chat);

        $participants = $chat->participants()
            ->orderBy('id')
            ->get(['id', 'user_id', 'last_read_message_id', 'last_read_at'])
            ->map(fn (ChatParticipant $participant) => [
                'user_id' => $participant->user_id,
                'last_read_message_id' => $participant->last_read_message_id,
                'last_read_at' => $participant->last_read_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'chat_id' => $chat->id,
            'couple_id' => $chat->couple_id,
            'participants' => $participants,
            'features' => [
                'chat_v1' => true,
            ],
        ]);
    }

    public function messages(Request $request, ChatThreadResolver $resolver): JsonResponse
    {
        $this->ensureFeatureEnabled();

        $validated = $request->validate([
            'before_id' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $chat = $resolver->resolveForUser($request->user());
        $this->authorize('view', $chat);

        $limit = min((int) ($validated['limit'] ?? 30), 100);

        $query = ChatMessage::query()
            ->where('chat_id', $chat->id)
            ->with(['attachments', 'sender:id,name'])
            ->orderByDesc('id')
            ->limit($limit);

        if (isset($validated['before_id'])) {
            $query->where('id', '<', $validated['before_id']);
        }

        $messages = $query->get();

        $serialized = $messages->map(fn (ChatMessage $message) => $this->serializeMessage($message))->values();

        return response()->json([
            'messages' => $serialized,
            'next_before_id' => $messages->last()?->id,
        ]);
    }

    public function send(Request $request, ChatThreadResolver $resolver): JsonResponse
    {
        $this->ensureFeatureEnabled();

        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:text,attachment,audio'],
            'body' => ['nullable', 'string', 'max:'.(int) config('us.chat.max_message_len', 2000)],
            'attachment' => ['nullable', 'file', 'max:'.((int) config('us.chat.max_file_mb', 10) * 1024)],
        ]);

        $type = $validated['type'] ?? 'text';
        $hasAttachment = $request->hasFile('attachment');

        if ($type === 'text' && empty($validated['body'])) {
            throw ValidationException::withMessages([
                'body' => 'The body field is required for text messages.',
            ]);
        }

        if ($type !== 'text' && ! $hasAttachment) {
            throw ValidationException::withMessages([
                'attachment' => 'An attachment file is required for this message type.',
            ]);
        }

        if ($hasAttachment && $type === 'text') {
            throw ValidationException::withMessages([
                'attachment' => 'Attachments require type attachment or audio.',
            ]);
        }

        $attachmentPayload = null;
        if ($hasAttachment) {
            $attachment = $request->file('attachment');
            $mime = $attachment->getMimeType() ?: $attachment->getClientMimeType();

            $allowed = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'audio/mpeg',
                'audio/wav',
                'audio/x-wav',
                'audio/mp4',
                'audio/x-m4a',
                'audio/ogg',
                'application/pdf',
                'text/plain',
            ];

            if (! in_array($mime, $allowed, true)) {
                throw ValidationException::withMessages([
                    'attachment' => 'Unsupported attachment file type.',
                ]);
            }

            $kind = str_starts_with($mime, 'image/')
                ? 'image'
                : (str_starts_with($mime, 'audio/') ? 'audio' : 'file');

            $attachmentPayload = [
                'file' => $attachment,
                'mime' => $mime,
                'kind' => $kind,
                'original_name' => $attachment->getClientOriginalName(),
                'size' => $attachment->getSize(),
            ];
        }

        $chat = $resolver->resolveForUser($request->user());
        $this->authorize('send', $chat);

        $message = ChatMessage::query()->create([
            'chat_id' => $chat->id,
            'sender_id' => $request->user()->id,
            'type' => $type,
            'body' => $validated['body'] ?? null,
            'meta' => null,
            'sent_at' => Carbon::now(),
        ]);

        if ($attachmentPayload) {
            $disk = (string) config('us.chat.attachments_disk', 'public');
            $path = $attachmentPayload['file']->store('chat-v1/'.$chat->id, $disk);

            $message->attachments()->create([
                'disk' => $disk,
                'path' => $path,
                'original_name' => $attachmentPayload['original_name'],
                'mime' => $attachmentPayload['mime'],
                'size' => $attachmentPayload['size'],
                'kind' => $attachmentPayload['kind'],
            ]);
        }

        $chat->forceFill(['last_message_id' => $message->id])->save();

        MessageSent::dispatch($message);

        return response()->json([
            'message' => $this->serializeMessage($message->load(['attachments', 'sender:id,name'])),
        ], 201);
    }

    public function markRead(Request $request, ChatThreadResolver $resolver): JsonResponse
    {
        $this->ensureFeatureEnabled();

        $validated = $request->validate([
            'last_read_message_id' => ['required', 'integer', 'min:1'],
        ]);

        $chat = $resolver->resolveForUser($request->user());
        $this->authorize('view', $chat);

        $message = ChatMessage::query()
            ->where('chat_id', $chat->id)
            ->whereKey($validated['last_read_message_id'])
            ->first();

        if (! $message) {
            abort(404, 'Message not found in this chat.');
        }

        $participant = ChatParticipant::query()
            ->where('chat_id', $chat->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (! $participant->last_read_message_id || $validated['last_read_message_id'] > $participant->last_read_message_id) {
            $participant->forceFill([
                'last_read_message_id' => $validated['last_read_message_id'],
                'last_read_at' => now(),
            ])->save();

            ReadReceiptUpdated::dispatch(
                (int) $chat->couple_id,
                (int) $chat->id,
                (int) $request->user()->id,
                (int) $validated['last_read_message_id'],
                now()->toIso8601String(),
            );
        }

        return response()->json(['ok' => true]);
    }

    public function delete(Request $request, ChatThreadResolver $resolver, ChatMessage $message): JsonResponse
    {
        $this->ensureFeatureEnabled();

        $chat = $resolver->resolveForUser($request->user());
        $this->authorize('view', $chat);

        if ((int) $message->chat_id !== (int) $chat->id) {
            abort(404, 'Message not found in this chat.');
        }

        $this->authorize('delete', $message);

        $message->delete();

        if ((int) $chat->last_message_id === (int) $message->id) {
            $chat->forceFill([
                'last_message_id' => ChatMessage::query()
                    ->where('chat_id', $chat->id)
                    ->orderByDesc('id')
                    ->value('id'),
            ])->save();
        }

        MessageDeleted::dispatch((int) $chat->couple_id, $message);

        return response()->json([], 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMessage(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'chat_id' => $message->chat_id,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name,
            'type' => $message->type,
            'body' => $message->body,
            'sent_at' => $message->sent_at?->toIso8601String(),
            'attachments' => $message->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'kind' => $attachment->kind,
                'disk' => $attachment->disk,
                'path' => $attachment->path,
                'original_name' => $attachment->original_name,
                'mime' => $attachment->mime,
                'size' => $attachment->size,
            ])->values(),
        ];
    }

    private function ensureFeatureEnabled(): void
    {
        if (! config('us.features.chat_v1', true)) {
            abort(404, 'Feature not available.');
        }
    }
}
