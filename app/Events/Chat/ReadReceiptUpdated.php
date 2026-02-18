<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReadReceiptUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private readonly int $coupleId,
        private readonly int $chatId,
        private readonly int $userId,
        private readonly int $lastReadMessageId,
        private readonly string $lastReadAt,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('couple.'.$this->coupleId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.read.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chatId,
            'couple_id' => $this->coupleId,
            'user_id' => $this->userId,
            'last_read_message_id' => $this->lastReadMessageId,
            'last_read_at' => $this->lastReadAt,
        ];
    }
}
