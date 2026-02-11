<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Mission;
use App\Models\MissionCompletion;
use App\Models\Memory;
use Carbon\Carbon;

class Home extends Component
{
    public $user;
    public $couple;
    public $stats;
    public $dailyMission;
    public $missionCompleted = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->couple = $this->user->couple;

        if ($this->couple) {
            $this->loadStats();
            $this->loadDailyMission();
        }
    }

    public function loadStats()
    {
        // Calculate streak (mock logic for now, or based on mission completions)
        // For MVP, we'll just check consecutive days of login or mission completion
        $streak = 3; // Placeholder or calculate from tracking table

        $this->stats = [
            'xp' => $this->couple->current_xp,
            'level' => $this->couple->level,
            'memories' => Memory::where('couple_id', $this->couple->id)->count(),
            'streak' => $streak
        ];
    }

    public function loadDailyMission()
    {
        // Get a random mission for today if not already assigned/completed
        // For MVP, just pick one random active mission
        $this->dailyMission = Mission::inRandomOrder()->first();

        if ($this->dailyMission) {
            $today = Carbon::today();
            $completion = MissionCompletion::where('couple_id', $this->couple->id)
                ->where('mission_id', $this->dailyMission->id)
                ->whereDate('completed_at', $today)
                ->first();

            $this->missionCompleted = !is_null($completion);
        }
    }

    public function completeMission()
    {
        if (!$this->dailyMission || $this->missionCompleted)
            return;

        MissionCompletion::create([
            'couple_id' => $this->couple->id,
            'user_id' => $this->user->id,
            'mission_id' => $this->dailyMission->id,
            'completed_at' => now(),
            'xp_earned' => $this->dailyMission->xp_reward
        ]);

        $this->couple->increment('current_xp', $this->dailyMission->xp_reward);
        $this->missionCompleted = true;
        $this->dispatch('xp-updated'); // trigger confetti if we had it
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.dashboard.home');
    }
}
