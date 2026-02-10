<?php

namespace App\Livewire\Couple;

use App\Services\CoupleService;
use Livewire\Component;

class CreateOrJoin extends Component
{
    public $tab = 'create';
    public $selectedTheme = 'garden';
    public $inviteCode = null;
    public $joinCode = '';

    public function createCouple()
    {
        $coupleService = app(CoupleService::class);

        try {
            $couple = $coupleService->createCouple(
                auth()->user(),
                ['theme' => $this->selectedTheme]
            );

            $this->inviteCode = $couple->invite_code;
            session()->flash('message', 'Couple created! Share your invite code with your partner.');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function joinCouple()
    {
        $this->validate([
            'joinCode' => 'required|string|size:8',
        ]);

        $coupleService = app(CoupleService::class);

        try {
            $couple = $coupleService->joinCouple(auth()->user(), $this->joinCode);
            session()->flash('message', 'Successfully joined couple!');
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.couple.create-or-join');
    }
}
