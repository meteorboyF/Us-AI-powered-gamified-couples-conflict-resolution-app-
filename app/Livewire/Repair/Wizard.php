<?php

namespace App\Livewire\Repair;

use App\Models\RepairSession;
use App\Services\RepairService;
use Livewire\Component;

class Wizard extends Component
{
    public $session;
    public $step = 1;
    public $myPerspective = '';
    public $partnerPerspective = '';
    public $selectedGoals = [];
    public $newAgreement = '';
    public $agreements;
    public $sharedGoals;
    public $isInitiator;

    public function mount($sessionId)
    {
        $this->session = RepairSession::with(['agreements', 'initiator'])->findOrFail($sessionId);

        // Verify user is part of this couple
        if (!$this->session->couple->users()->where('users.id', auth()->id())->exists()) {
            abort(403);
        }

        $this->isInitiator = $this->session->initiated_by === auth()->id();
        $this->sharedGoals = RepairService::getSharedGoals();
        $this->loadData();

        // If partner is joining for first time, mark as in_progress
        if ($this->session->status === 'pending' && !$this->isInitiator) {
            $repairService = app(RepairService::class);
            $repairService->joinRepair($this->session, auth()->user());
            $this->session = $this->session->fresh();
        }
    }

    public function loadData()
    {
        // Load perspectives
        if ($this->isInitiator) {
            $this->myPerspective = $this->session->initiator_perspective ?? '';
            $this->partnerPerspective = $this->session->partner_perspective ?? '';
        } else {
            $this->myPerspective = $this->session->partner_perspective ?? '';
            $this->partnerPerspective = $this->session->initiator_perspective ?? '';
        }

        // Load goals
        $this->selectedGoals = $this->session->shared_goals ?? [];

        // Load agreements
        $this->agreements = $this->session->agreements;
    }

    public function nextStep()
    {
        if ($this->step < 5) {
            $this->step++;
        }
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function savePerspective()
    {
        $this->validate([
            'myPerspective' => 'required|string|max:500',
        ]);

        $repairService = app(RepairService::class);

        try {
            $this->session = $repairService->updatePerspective(
                $this->session,
                auth()->user(),
                $this->myPerspective
            );

            $this->nextStep();

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function saveGoals()
    {
        $this->validate([
            'selectedGoals' => 'required|array|min:3|max:5',
        ]);

        $repairService = app(RepairService::class);

        try {
            $this->session = $repairService->selectSharedGoals(
                $this->session,
                $this->selectedGoals
            );

            $this->nextStep();

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function addAgreement()
    {
        $this->validate([
            'newAgreement' => 'required|string|max:300',
        ]);

        $repairService = app(RepairService::class);

        try {
            $repairService->createAgreement(
                $this->session,
                auth()->user(),
                $this->newAgreement
            );

            $this->newAgreement = '';
            $this->loadData();
            session()->flash('message', 'Agreement added!');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function acknowledgeAgreement($agreementId)
    {
        $repairService = app(RepairService::class);
        $agreement = $this->session->agreements()->findOrFail($agreementId);

        try {
            $repairService->acknowledgeAgreement($agreement, auth()->user());
            $this->loadData();
            session()->flash('message', 'Agreement acknowledged! +10 XP');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function completeRepair()
    {
        $repairService = app(RepairService::class);

        try {
            $repairService->completeRepair($this->session);
            session()->flash('message', 'Repair completed! +50 XP');
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function abandonRepair()
    {
        $repairService = app(RepairService::class);
        $repairService->abandonRepair($this->session, auth()->user());

        session()->flash('message', 'Repair session ended.');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.repair.wizard');
    }
}
