<?php

use App\Models\ChatV2\Conversation;
use App\Models\Couple;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum', 'verified']]);

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::query()->find($conversationId);

    if (! $conversation || ! $conversation->couple || ! $conversation->couple->isActive()) {
        return false;
    }

    return $conversation->couple->users()
        ->where('users.id', $user->id)
        ->where('couple_user.is_active', true)
        ->exists();
});

Broadcast::channel('couple.{coupleId}', function ($user, $coupleId) {
    $couple = Couple::query()->find($coupleId);

    if (! $couple || ! $couple->isActive()) {
        return false;
    }

    $isMember = $couple->users()
        ->where('users.id', $user->id)
        ->where('couple_user.is_active', true)
        ->exists();

    if (! $isMember) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->profile_photo_url,
    ];
});
