<?php

namespace App\Policies;

use App\Models\AiBridgeSuggestion;
use App\Models\User;

class AiBridgeSuggestionPolicy
{
    public function view(User $user, AiBridgeSuggestion $suggestion): bool
    {
        return $this->ownsActiveSuggestion($user, $suggestion);
    }

    public function update(User $user, AiBridgeSuggestion $suggestion): bool
    {
        return $this->ownsActiveSuggestion($user, $suggestion);
    }

    public function delete(User $user, AiBridgeSuggestion $suggestion): bool
    {
        return $this->ownsActiveSuggestion($user, $suggestion);
    }

    protected function ownsActiveSuggestion(User $user, AiBridgeSuggestion $suggestion): bool
    {
        if ($suggestion->user_id !== $user->id) {
            return false;
        }

        return $suggestion->couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();
    }
}
