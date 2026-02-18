<?php

namespace App\Policies;

use App\Models\CoupleWorldItem;
use App\Models\User;

class CoupleWorldItemPolicy
{
    public function view(User $user, CoupleWorldItem $item): bool
    {
        return $item->couple->members()->whereKey($user->id)->exists();
    }

    public function update(User $user, CoupleWorldItem $item): bool
    {
        return $item->couple->members()->whereKey($user->id)->exists();
    }
}
