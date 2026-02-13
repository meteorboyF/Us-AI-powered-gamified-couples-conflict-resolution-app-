<?php

namespace App\Policies;

use App\Models\User;
use App\Models\World;

class WorldPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, World $world): bool
    {
        // User must be a member of the couple that owns this world
        return $world->couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Worlds are created automatically with couples
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, World $world): bool
    {
        // Prevent direct updates - XP should only be modified via XpService
        // Only theme/cosmetics can be updated
        return $this->view($user, $world);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, World $world): bool
    {
        // Worlds cannot be deleted independently
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, World $world): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, World $world): bool
    {
        return false;
    }
}
