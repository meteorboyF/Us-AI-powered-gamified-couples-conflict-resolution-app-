<?php

namespace App\Livewire\Mission;

use App\Services\CoupleService;
use App\Services\MissionService;
use Livewire\Component;

class Board extends Component
{
    public $missions;
    public $couple;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $this->couple = $coupleService->getUserCouple(auth()->user());

        if ($this->couple) {
            $missionService = app(MissionService::class);
            // Assign daily missions if needed
            $missionService->assignDailyMissions($this->couple);
            // Get today's missions
            $this->missions = $missionService->getTodayMissions($this->couple);
        }
    }

    public function completeMission($assignmentId, $notes = null)
    {
        $missionService = app(MissionService::class);

        try {
            $assignment = \App\Models\MissionAssignment::findOrFail($assignmentId);

            // Authorize
            if ($assignment->couple_id !== $this->couple->id) {
                session()->flash('error', 'Unauthorized.');
                return;
            }

            $completion = $missionService->completeMission(
                $assignment,
                auth()->user(),
                $notes
            );

            session()->flash('message', "Mission completed! +{$assignment->mission->xp_reward} XP");

            // Refresh missions
            $this->missions = $missionService->getTodayMissions($this->couple);

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.mission.board');
    }
}
