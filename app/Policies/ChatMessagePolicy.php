<?php

namespace App\Policies;

use App\Models\ChatMessage;
use App\Models\User;

class ChatMessagePolicy
{
    public function view(User $user, ChatMessage $message): bool
    {
        return $message->chat->couple->members()->whereKey($user->id)->exists();
    }

    public function delete(User $user, ChatMessage $message): bool
    {
        return (int) $message->sender_id === (int) $user->id;
    }
}
