<?php

namespace App\Livewire\Dashboard;

use Carbon\Carbon;
use App\Models\Memory;
use Livewire\Component;
use App\Services\CoupleService;
use App\Services\MissionService;
use Illuminate\Support\Facades\Auth;

class Home extends Component
{
    public $user;
    public $couple;
    public $partnerName;
    public $stats;
    public $dailyMission;
    public $dailyAssignmentId;
    public $missionCompleted = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->couple = app(CoupleService::class)->getUserCouple($this->user);

        if ($this->couple) {
            $this->partnerName = $this->couple->users()
                ->where('users.id', '!=', $this->user->id)
                ->value('name');
            $this->loadStats();
            $this->loadDailyMission();
        }
    }

    public function loadStats()
    {
        // Calculate streak (mock logic for now, or based on mission completions)
        // For MVP, we'll just check consecutive days of login or mission completion
        $streak = 3; // Placeholder or calculate from tracking table

        $world = $this->couple->world;
        $this->stats = [
            'xp' => $world?->xp_total ?? 0,
            'level' => $world?->level ?? 1,
            'memories' => Memory::where('couple_id', $this->couple->id)->count(),
            'streak' => $streak
        ];
    }

    public function loadDailyMission()
    {
        $missionService = app(MissionService::class);
        $missionService->assignDailyMissions($this->couple);

        $assignment = $missionService->getMissionsForCouple($this->couple, Carbon::today())->first();
        if ($assignment) {
            $this->dailyAssignmentId = $assignment->id;
            $this->dailyMission = $assignment->mission;
            $this->missionCompleted = $assignment->status === 'completed';
            return;
        }

        $this->dailyAssignmentId = null;
        $this->dailyMission = null;
        $this->missionCompleted = false;
    }

    public function completeMission()
    {
        if (!$this->dailyAssignmentId || $this->missionCompleted)
            return;

        $missionService = app(MissionService::class);
        $assignment = \App\Models\MissionAssignment::find($this->dailyAssignmentId);
        if (!$assignment) {
            return;
        }

        $missionService->completeMission($assignment, $this->user);
        $this->missionCompleted = true;
        $this->dispatch('xp-updated'); // trigger confetti if we had it
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.dashboard.home')->layout('layouts.app');
    }
}
