<?php

namespace App\Policies;

use App\Models\DailyCheckin;
use App\Models\User;

class DailyCheckinPolicy
{
    public function view(User $user, DailyCheckin $checkin): bool
    {
        return $checkin->couple->members()->whereKey($user->id)->exists();
    }

    public function create(User $user, int $coupleId, int $actorUserId): bool
    {
        return $actorUserId === $user->id
            && $user->couples()->whereKey($coupleId)->exists();
    }
}
