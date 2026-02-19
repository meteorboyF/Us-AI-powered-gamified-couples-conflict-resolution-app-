<?php

namespace App\Policies;

use App\Models\AiSession;
use App\Models\User;

class AiSessionPolicy
{
    public function viewAny(User $user, int $coupleId): bool
    {
        return $user->couples()->whereKey($coupleId)->exists();
    }

    public function create(User $user, int $coupleId): bool
    {
        return $user->couples()->whereKey($coupleId)->exists();
    }

    public function view(User $user, AiSession $session): bool
    {
        return $session->couple->members()->whereKey($user->id)->exists();
    }

    public function update(User $user, AiSession $session): bool
    {
        return $session->couple->members()->whereKey($user->id)->exists();
    }
}
