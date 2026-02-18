<?php

namespace App\Events\Chat;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(private readonly ChatMessage $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('couple.'.$this->message->chat->couple_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message.sent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $message = $this->message->loadMissing(['attachments', 'sender:id,name', 'chat:id,couple_id']);

        return [
            'chat_id' => $message->chat_id,
            'couple_id' => $message->chat->couple_id,
            'message' => [
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
                ])->values()->all(),
            ],
        ];
    }
}
