<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorldItem;

class WorldItemPolicy
{
    public function view(User $user, WorldItem $worldItem): bool
    {
        return $worldItem->couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();
    }

    public function update(User $user, WorldItem $worldItem): bool
    {
        return $this->view($user, $worldItem);
    }
}
