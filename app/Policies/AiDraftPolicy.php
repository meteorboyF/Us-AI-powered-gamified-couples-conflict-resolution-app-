<?php

namespace App\Policies;

use App\Models\AiDraft;
use App\Models\User;

class AiDraftPolicy
{
    public function view(User $user, AiDraft $draft): bool
    {
        return $draft->session->couple->members()->whereKey($user->id)->exists();
    }

    public function update(User $user, AiDraft $draft): bool
    {
        return $draft->session->couple->members()->whereKey($user->id)->exists();
    }
}
