<?php

namespace App\Livewire\MoodCheckin;

use App\Models\MoodCheckin;
use App\Services\CoupleService;
use Livewire\Component;

class PartnerView extends Component
{
    public $partnerMood;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->getUserCouple(auth()->user());

        if ($couple) {
            // Get partner's user ID
            $partnerId = $couple->users()
                ->where('users.id', '!=', auth()->id())
                ->first()?->id;

            if ($partnerId) {
                // Get partner's mood check-in for today
                $this->partnerMood = MoodCheckin::where('couple_id', $couple->id)
                    ->where('user_id', $partnerId)
                    ->where('date', today()->toDateString())
                    ->first();
            }
        }
    }

    public function render()
    {
        return view('livewire.mood-checkin.partner-view');
    }
}
