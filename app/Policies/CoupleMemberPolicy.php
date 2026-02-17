<?php

namespace App\Policies;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;

class CoupleMemberPolicy
{
    public function view(User $user, CoupleMember $membership): bool
    {
        return $this->isOwner($user, $membership->couple_id) || $membership->user_id === $user->id;
    }

    public function delete(User $user, CoupleMember $membership): bool
    {
        return $this->isOwner($user, $membership->couple_id) || $membership->user_id === $user->id;
    }

    private function isOwner(User $user, int $coupleId): bool
    {
        return Couple::query()
            ->whereKey($coupleId)
            ->whereHas('memberships', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('role', 'owner');
            })
            ->exists();
    }
}
