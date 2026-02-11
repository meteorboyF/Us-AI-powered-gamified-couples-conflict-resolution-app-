<?php

namespace App\Livewire\Vault;

use App\Services\CoupleService;
use App\Services\VaultService;
use Livewire\Component;

class Gallery extends Component
{
    public $memories;
    public $filterType = 'all';
    public $showLocked = false;
    public $couple;
    public $storageStats;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $this->couple = $coupleService->getUserCouple(auth()->user());

        if ($this->couple) {
            $this->loadMemories();
            $this->loadStorageStats();
        }
    }

    public function loadMemories()
    {
        $vaultService = app(VaultService::class);

        if ($this->showLocked) {
            $this->memories = $vaultService->getLockedMemories($this->couple);
        } else {
            $type = $this->filterType === 'all' ? null : $this->filterType;
            $this->memories = $vaultService->getMemories($this->couple, auth()->user(), $type);
        }
    }

    public function loadStorageStats()
    {
        $vaultService = app(VaultService::class);
        $this->storageStats = $vaultService->getStorageStats($this->couple);
    }

    public function filterByType($type)
    {
        $this->filterType = $type;
        $this->showLocked = false;
        $this->loadMemories();
    }

    public function toggleLockedView()
    {
        $this->showLocked = !$this->showLocked;
        $this->loadMemories();
    }

    public function deleteMemory($id)
    {
        $vaultService = app(VaultService::class);
        $memory = $this->memories->firstWhere('id', $id);

        try {
            $vaultService->deleteMemory($memory, auth()->user());
            $this->loadMemories();
            $this->loadStorageStats();
            session()->flash('message', 'Memory deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function lockMemory($id)
    {
        $vaultService = app(VaultService::class);
        $memory = $this->memories->firstWhere('id', $id);

        try {
            $vaultService->lockMemory($memory, auth()->user());
            $this->loadMemories();
            session()->flash('message', 'Memory locked! +10 XP');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function unlockMemory($id)
    {
        $vaultService = app(VaultService::class);
        $memory = $this->memories->firstWhere('id', $id);

        try {
            $vaultService->unlockMemory($memory, auth()->user());
            $this->loadMemories();
            session()->flash('message', 'Memory unlocked.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.vault.gallery');
    }
}
