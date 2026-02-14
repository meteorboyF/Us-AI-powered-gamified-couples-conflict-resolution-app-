<?php

namespace App\Events\ChatV2;

use App\Models\ChatV2\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.'.$this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chatv2.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'type' => $this->message->type,
            'body' => $this->message->body,
            'media_path' => $this->message->media_path,
            'media_mime' => $this->message->media_mime,
            'media_size' => $this->message->media_size,
            'duration_ms' => $this->message->duration_ms,
            'reply_to_message_id' => $this->message->reply_to_message_id,
            'created_at' => $this->message->created_at?->toISOString(),
        ];
    }
}
