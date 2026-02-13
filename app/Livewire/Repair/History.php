<?php

namespace App\Livewire\Repair;

use App\Services\CoupleService;
use App\Services\RepairService;
use Livewire\Component;

class History extends Component
{
    public $sessions;

    public $selectedSession;

    public $couple;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $this->couple = $coupleService->getUserCouple(auth()->user());

        if ($this->couple) {
            $repairService = app(RepairService::class);
            $this->sessions = $repairService->getCompletedSessions($this->couple);
        }
    }

    public function viewSession($sessionId)
    {
        $this->selectedSession = $this->sessions->firstWhere('id', $sessionId);
    }

    public function closeDetails()
    {
        $this->selectedSession = null;
    }

    public function render()
    {
        return view('livewire.repair.history');
    }
}
