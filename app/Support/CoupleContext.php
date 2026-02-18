<?php

namespace App\Support;

use App\Models\Couple;
use Illuminate\Support\Facades\Auth;

class CoupleContext
{
    public function resolve(): ?Couple
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->current_couple_id) {
            $current = $user->couples()
                ->whereKey($user->current_couple_id)
                ->first();

            if ($current) {
                return $current;
            }
        }

        $couples = $user->couples()->get();

        if ($couples->count() === 1) {
            $single = $couples->first();

            $user->forceFill(['current_couple_id' => $single->id])->save();

            return $single;
        }

        return null;
    }
}
