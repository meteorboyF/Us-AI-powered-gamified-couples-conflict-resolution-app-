<?php

namespace App\Policies\ChatV2;

use App\Models\ChatV2\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $this->isActiveMember($user, $conversation);
    }

    public function send(User $user, Conversation $conversation): bool
    {
        return $this->isActiveMember($user, $conversation);
    }

    protected function isActiveMember(User $user, Conversation $conversation): bool
    {
        $couple = $conversation->couple;

        if (! $couple || ! $couple->isActive()) {
            return false;
        }

        return $couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();
    }
}
