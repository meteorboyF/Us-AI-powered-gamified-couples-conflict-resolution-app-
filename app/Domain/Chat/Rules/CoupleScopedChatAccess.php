<?php

namespace App\Domain\Chat\Rules;

use App\Models\Chat;
use App\Models\User;

class CoupleScopedChatAccess
{
    public function canAccess(User $user, Chat $chat, ?int $currentCoupleId): bool
    {
        if ($currentCoupleId === null || $chat->couple_id !== $currentCoupleId) {
            return false;
        }

        return $chat->participants()
            ->where('user_id', $user->id)
            ->exists();
    }
}
