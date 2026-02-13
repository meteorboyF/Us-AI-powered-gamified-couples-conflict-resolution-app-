<?php

namespace App\Livewire\Dashboard;

use App\Services\CoupleService;
use App\Services\XpService;
use Livewire\Component;

class CoupleWorld extends Component
{
    public $couple;

    public $world;

    public $recentXpEvents;

    public $todayXp;

    public $xpForNextLevel;

    public $levelProgress;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $this->couple = $coupleService->getUserCouple(auth()->user());

        if ($this->couple) {
            $this->world = $this->couple->world;

            $xpService = app(XpService::class);
            $this->recentXpEvents = $xpService->getXpHistory($this->couple, 10);
            $this->todayXp = $xpService->getTodayXp($this->couple);

            if ($this->world) {
                $this->xpForNextLevel = $xpService->xpForNextLevel($this->world->level);
                $currentLevelXp = $this->world->xp_total % $this->xpForNextLevel;
                $this->levelProgress = ($currentLevelXp / $this->xpForNextLevel) * 100;
            }
        }
    }

    public function render()
    {
        return view('livewire.dashboard.couple-world')->layout('layouts.app');
    }
}
