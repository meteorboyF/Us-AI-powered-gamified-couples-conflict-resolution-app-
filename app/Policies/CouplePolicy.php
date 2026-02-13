<?php

namespace App\Policies;

use App\Models\Couple;
use App\Models\User;

class CouplePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can see their own couples
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Couple $couple): bool
    {
        // User must be an active member of the couple
        return $couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // User can create a couple if they're not already in an active one
        return ! $user->couples()
            ->where('status', 'active')
            ->where('couple_user.is_active', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Couple $couple): bool
    {
        // User must be an active member
        return $this->view($user, $couple);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Couple $couple): bool
    {
        // Only the creator can unlink the couple
        return $couple->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Couple $couple): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Couple $couple): bool
    {
        return false;
    }
}
