<?php

namespace App\Livewire\Vault;

use App\Models\Memory;
use App\Services\CoupleService;
use App\Services\VaultService;
use Livewire\Component;

class Gallery extends Component
{
    public $memories;

    public $filterType = 'all';

    public $showLocked = false;

    public $showComfort = false;

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
            $this->memories = $vaultService->getLockedMemories($this->couple, auth()->user());
        } else {
            $type = $this->filterType === 'all' ? null : $this->filterType;
            $this->memories = $vaultService->getMemories($this->couple, auth()->user(), $type, $this->showComfort);
        }
    }

    public function loadStorageStats()
    {
        $vaultService = app(VaultService::class);
        $this->storageStats = $vaultService->getStorageStats($this->couple, auth()->user());
    }

    public function filterByType($type)
    {
        $this->filterType = $type;
        $this->showLocked = false;
        $this->showComfort = false;
        $this->loadMemories();
    }

    public function toggleLockedView()
    {
        $this->showLocked = ! $this->showLocked;
        $this->showComfort = false;
        $this->loadMemories();
    }

    public function toggleComfortView()
    {
        $this->showComfort = ! $this->showComfort;
        $this->showLocked = false;
        $this->loadMemories();
    }

    public function deleteMemory($id)
    {
        $vaultService = app(VaultService::class);
        $memory = Memory::where('id', $id)
            ->where('couple_id', $this->couple->id)
            ->firstOrFail();

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
        $memory = Memory::where('id', $id)
            ->where('couple_id', $this->couple->id)
            ->firstOrFail();

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
        $memory = Memory::where('id', $id)
            ->where('couple_id', $this->couple->id)
            ->firstOrFail();

        try {
            $vaultService->unlockMemory($memory, auth()->user());
            $this->loadMemories();
            session()->flash('message', 'Unlock approval recorded.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function toggleComfort($id)
    {
        $vaultService = app(VaultService::class);
        $memory = Memory::where('id', $id)
            ->where('couple_id', $this->couple->id)
            ->firstOrFail();

        try {
            $vaultService->toggleComfort($memory, auth()->user());
            $this->loadMemories();
            $this->loadStorageStats();
            session()->flash('message', 'Comfort flag updated.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.vault.gallery')->layout('layouts.app');
    }
}
