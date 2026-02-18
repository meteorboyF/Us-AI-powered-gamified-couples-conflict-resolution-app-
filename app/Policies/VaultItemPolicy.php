<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VaultItem;

class VaultItemPolicy
{
    public function viewAny(User $user, int $coupleId): bool
    {
        return $user->couples()->whereKey($coupleId)->exists();
    }

    public function create(User $user, int $coupleId): bool
    {
        return $user->couples()->whereKey($coupleId)->exists();
    }

    public function view(User $user, VaultItem $item): bool
    {
        return $item->couple->members()->whereKey($user->id)->exists();
    }

    public function upload(User $user, VaultItem $item): bool
    {
        return $item->couple->members()->whereKey($user->id)->exists();
    }

    public function lock(User $user, VaultItem $item): bool
    {
        return $item->couple->members()->whereKey($user->id)->exists();
    }

    public function requestUnlock(User $user, VaultItem $item): bool
    {
        return $item->couple->members()->whereKey($user->id)->exists();
    }
}
