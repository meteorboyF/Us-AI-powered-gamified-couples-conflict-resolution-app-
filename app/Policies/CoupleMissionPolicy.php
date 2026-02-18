<?php

namespace App\Policies;

use App\Models\CoupleMission;
use App\Models\User;

class CoupleMissionPolicy
{
    public function view(User $user, CoupleMission $mission): bool
    {
        return $mission->couple->members()->whereKey($user->id)->exists();
    }

    public function create(User $user, int $coupleId): bool
    {
        return $user->couples()->whereKey($coupleId)->exists();
    }

    public function update(User $user, CoupleMission $mission): bool
    {
        return $mission->couple->members()->whereKey($user->id)->exists();
    }
}
