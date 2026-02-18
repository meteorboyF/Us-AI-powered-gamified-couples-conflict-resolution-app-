<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VaultUnlockRequest;

class VaultUnlockRequestPolicy
{
    public function view(User $user, VaultUnlockRequest $unlockRequest): bool
    {
        return $unlockRequest->item->couple->members()->whereKey($user->id)->exists();
    }

    public function respond(User $user, VaultUnlockRequest $unlockRequest): bool
    {
        if (! $unlockRequest->item->couple->members()->whereKey($user->id)->exists()) {
            return false;
        }

        return (int) $unlockRequest->requested_by_user_id !== (int) $user->id;
    }
}
