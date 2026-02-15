<?php

namespace App\Policies;

use App\Domain\Chat\Rules\CoupleScopedChatAccess;
use App\Domain\Couples\CoupleContext;
use App\Models\ChatMessage;
use App\Models\User;

class ChatMessagePolicy
{
    public function __construct(
        private readonly CoupleContext $coupleContext,
        private readonly CoupleScopedChatAccess $accessRule,
    ) {}

    public function view(User $user, ChatMessage $chatMessage): bool
    {
        return $this->accessRule->canAccess(
            $user,
            $chatMessage->chat,
            $this->coupleContext->currentCoupleId(),
        );
    }
}
