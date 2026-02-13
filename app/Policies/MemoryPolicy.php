<?php

namespace App\Policies;

use App\Models\Memory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MemoryPolicy
{
    public function view(User $user, Memory $memory): bool
    {
        return $memory->canBeAccessedBy($user);
    }

    public function viewContent(User $user, Memory $memory): bool
    {
        return $memory->canBeViewedBy($user);
    }

    public function approveUnlock(User $user, Memory $memory): Response
    {
        if (! $memory->canBeAccessedBy($user)) {
            return Response::deny('Unauthorized couple access.');
        }

        if (! $memory->isDual()) {
            return Response::deny('Unlock approval is only available for dual-consent memories.');
        }

        $alreadyApproved = $memory->unlockApprovals()
            ->where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($alreadyApproved) {
            return Response::deny('You have already approved this unlock.');
        }

        return Response::allow();
    }

    public function toggleComfort(User $user, Memory $memory): bool
    {
        if (! $memory->canBeAccessedBy($user)) {
            return false;
        }

        if ($memory->isPrivate()) {
            return $memory->created_by === $user->id;
        }

        return true;
    }
}
