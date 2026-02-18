<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    public function view(User $user, Chat $chat): bool
    {
        return $chat->couple->members()->whereKey($user->id)->exists();
    }

    public function send(User $user, Chat $chat): bool
    {
        return $chat->couple->members()->whereKey($user->id)->exists();
    }
}
