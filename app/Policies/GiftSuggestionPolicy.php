<?php

namespace App\Policies;

use App\Models\GiftSuggestion;
use App\Models\User;

class GiftSuggestionPolicy
{
    public function view(User $user, GiftSuggestion $suggestion): bool
    {
        return $user->couples()->whereKey($suggestion->giftRequest->couple_id)->exists();
    }

    public function updateFavorite(User $user, GiftSuggestion $suggestion): bool
    {
        return $user->couples()->whereKey($suggestion->giftRequest->couple_id)->exists();
    }
}
