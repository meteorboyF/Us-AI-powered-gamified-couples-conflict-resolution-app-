<?php

namespace App\Policies;

use App\Domain\Chat\Rules\CoupleScopedChatAccess;
use App\Domain\Couples\CoupleContext;
use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    public function __construct(
        private readonly CoupleContext $coupleContext,
        private readonly CoupleScopedChatAccess $accessRule,
    ) {}

    public function view(User $user, Chat $chat): bool
    {
        return $this->accessRule->canAccess($user, $chat, $this->coupleContext->currentCoupleId());
    }

    public function sendMessage(User $user, Chat $chat): bool
    {
        return $this->accessRule->canAccess($user, $chat, $this->coupleContext->currentCoupleId());
    }
}
