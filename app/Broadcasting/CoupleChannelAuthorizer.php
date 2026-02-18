<?php

namespace App\Broadcasting;

use App\Models\CoupleMember;
use App\Models\User;

class CoupleChannelAuthorizer
{
    public function __invoke(User $user, int|string $coupleId): bool
    {
        return CoupleMember::query()
            ->where('couple_id', $coupleId)
            ->where('user_id', $user->id)
            ->exists();
    }
}
