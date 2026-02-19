<?php

namespace App\Policies;

use App\Models\GiftRequest;
use App\Models\User;

class GiftRequestPolicy
{
    public function viewAny(User $user, int $coupleId): bool
    {
        return $user->couples()->whereKey($coupleId)->exists();
    }

    public function create(User $user, int $coupleId): bool
    {
        return $user->couples()->whereKey($coupleId)->exists();
    }

    public function view(User $user, GiftRequest $request): bool
    {
        return $user->couples()->whereKey($request->couple_id)->exists();
    }

    public function update(User $user, GiftRequest $request): bool
    {
        return $user->couples()->whereKey($request->couple_id)->exists();
    }
}
