<?php

namespace App\Policies;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;

class CouplePolicy
{
    public function view(User $user, Couple $couple): bool
    {
        return $couple->members()->whereKey($user->id)->exists();
    }

    public function update(User $user, Couple $couple): bool
    {
        return $this->isOwner($user, $couple);
    }

    public function manageMembers(User $user, Couple $couple): bool
    {
        return $this->isOwner($user, $couple);
    }

    private function isOwner(User $user, Couple $couple): bool
    {
        return CoupleMember::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
