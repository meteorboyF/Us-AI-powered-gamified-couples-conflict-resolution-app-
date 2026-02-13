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
                ->where('couple_user.is_active', true)
                ->value('users.id');

            if ($partnerId) {
                $checkin = MoodCheckin::where('couple_id', $couple->id)
                    ->where('user_id', $partnerId)
                    ->whereDate('date', today())
                    ->first();

                if ($checkin) {
                    $this->partnerMood = [
                        'mood_level' => $checkin->mood_level,
                        'needs' => $checkin->needs ?? [],
                        'checked_in_at' => $checkin->created_at?->diffForHumans(),
                    ];
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.mood-checkin.partner-view')->layout('layouts.app');
    }
}
