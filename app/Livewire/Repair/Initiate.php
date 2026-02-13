<?php

namespace App\Livewire\Repair;

use App\Services\CoupleService;
use App\Services\RepairService;
use Livewire\Component;

class Initiate extends Component
{
    public $conflictTopic = '';

    public $couple;

    public $activeSession;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $this->couple = $coupleService->getUserCouple(auth()->user());

        if ($this->couple) {
            $repairService = app(RepairService::class);
            $this->activeSession = $repairService->getActiveSession($this->couple);
        }
    }

    public function startRepair()
    {
        $this->validate([
            'conflictTopic' => 'required|string|max:200',
        ]);

        $repairService = app(RepairService::class);

        try {
            $session = $repairService->initiateRepair(
                $this->couple,
                auth()->user(),
                $this->conflictTopic
            );

            session()->flash('message', 'Repair session started. Your partner has been notified.');

            return redirect()->route('repair.wizard', ['sessionId' => $session->id]);

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function joinActiveSession()
    {
        if ($this->activeSession) {
            return redirect()->route('repair.wizard', ['sessionId' => $this->activeSession->id]);
        }
    }

    public function render()
    {
        return view('livewire.repair.initiate');
    }
}
