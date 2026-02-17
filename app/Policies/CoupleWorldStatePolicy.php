<?php

namespace App\Policies;

use App\Models\CoupleWorldState;
use App\Models\User;

class CoupleWorldStatePolicy
{
    public function view(User $user, CoupleWorldState $state): bool
    {
        return $state->couple->members()->whereKey($user->id)->exists();
    }

    public function update(User $user, CoupleWorldState $state): bool
    {
        return $state->couple->members()->whereKey($user->id)->exists();
    }
}
