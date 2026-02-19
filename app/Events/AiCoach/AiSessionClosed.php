<?php

namespace App\Events\AiCoach;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiSessionClosed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private readonly int $coupleId,
        private readonly int $sessionId,
        private readonly string $status,
        private readonly ?string $closedAt,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("couple.{$this->coupleId}");
    }

    public function broadcastAs(): string
    {
        return 'ai.session.closed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'couple_id' => $this->coupleId,
            'session_id' => $this->sessionId,
            'status' => $this->status,
            'closed_at' => $this->closedAt,
        ];
    }
}
