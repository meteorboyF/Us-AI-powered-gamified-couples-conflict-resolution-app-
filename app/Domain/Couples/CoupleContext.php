<?php

namespace App\Domain\Couples;

class CoupleContext
{
    public function currentCoupleId(): ?int
    {
        $request = request();

        if (! $request || ! $request->hasSession()) {
            return null;
        }

        $coupleId = $request->session()->get('current_couple_id');

        return is_numeric($coupleId) ? (int) $coupleId : null;
    }

    public function setCurrentCoupleId(int $coupleId): void
    {
        $request = request();

        if (! $request || ! $request->hasSession()) {
            return;
        }

        $request->session()->put('current_couple_id', $coupleId);
    }
}
