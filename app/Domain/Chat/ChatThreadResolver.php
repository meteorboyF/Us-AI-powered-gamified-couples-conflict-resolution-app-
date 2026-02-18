<?php

namespace App\Domain\Chat;

use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\User;
use App\Support\CoupleContext;

class ChatThreadResolver
{
    public function __construct(private readonly CoupleContext $coupleContext) {}

    public function resolveForUser(User $user): Chat
    {
        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            abort(403, 'Current couple is not accessible.');
        }

        $couple = $this->coupleContext->resolve();
        if (! $couple) {
            abort(409, 'No active couple selected.');
        }

        $chat = Chat::query()->firstOrCreate(
            ['couple_id' => $couple->id],
            ['created_by_user_id' => $user->id]
        );

        $memberIds = $couple->members()->pluck('users.id');

        foreach ($memberIds as $memberId) {
            ChatParticipant::query()->updateOrCreate(
                [
                    'chat_id' => $chat->id,
                    'user_id' => $memberId,
                ],
                [
                    'role' => 'member',
                    'joined_at' => now(),
                ]
            );
        }

        return $chat->load('participants');
    }
}
