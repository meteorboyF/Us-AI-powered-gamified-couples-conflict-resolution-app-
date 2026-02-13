<?php

namespace App\Livewire\Vault;

use App\Models\Memory;
use App\Models\MemoryReaction;
use App\Services\VaultService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MemoryView extends Component
{
    public $memory;

    public $myReaction;

    public $partnerReaction;

    public $reactions;

    public $unlockStatus = [];

    public $canViewContent = false;

    public $canToggleComfort = false;

    public function mount($memoryId)
    {
        $this->memory = Memory::with(['creator', 'reactions.user', 'couple.users', 'unlockApprovals'])->findOrFail($memoryId);

        Gate::forUser(auth()->user())->authorize('view', $this->memory);

        $this->loadReactions();
        $this->reactions = MemoryReaction::getReactionTypes();
        $this->refreshUnlockState();
    }

    public function loadReactions()
    {
        $this->myReaction = $this->memory->reactions()
            ->where('user_id', auth()->id())
            ->first();

        $this->partnerReaction = $this->memory->reactions()
            ->where('user_id', '!=', auth()->id())
            ->first();
    }

    public function addReaction($reaction)
    {
        $vaultService = app(VaultService::class);
        if (! $this->canViewContent) {
            session()->flash('error', 'This dual-consent memory is still locked.');

            return;
        }

        try {
            $vaultService->addReaction($this->memory, auth()->user(), $reaction);
            $this->loadReactions();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function removeReaction()
    {
        if (! $this->canViewContent) {
            session()->flash('error', 'This dual-consent memory is still locked.');

            return;
        }

        $vaultService = app(VaultService::class);
        $vaultService->removeReaction($this->memory, auth()->user());
        $this->loadReactions();
    }

    public function updateVisibility($visibility)
    {
        $vaultService = app(VaultService::class);

        try {
            $this->memory = $vaultService->changeVisibility($this->memory, auth()->user(), $visibility);
            $this->refreshUnlockState();
            session()->flash('message', 'Visibility updated.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function lockMemory()
    {
        $vaultService = app(VaultService::class);

        try {
            $this->memory = $vaultService->lockMemory($this->memory, auth()->user());
            $this->refreshUnlockState();
            session()->flash('message', 'Memory locked! +10 XP');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function approveUnlock()
    {
        $vaultService = app(VaultService::class);

        try {
            $this->memory = $vaultService->approveDualUnlock($this->memory, auth()->user());
            $this->refreshUnlockState();
            session()->flash('message', 'Unlock approval recorded.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function toggleComfort()
    {
        $vaultService = app(VaultService::class);

        try {
            $this->memory = $vaultService->toggleComfort($this->memory, auth()->user());
            $this->refreshUnlockState();
            session()->flash('message', 'Comfort flag updated.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteMemory()
    {
        $vaultService = app(VaultService::class);

        try {
            $vaultService->deleteMemory($this->memory, auth()->user());
            session()->flash('message', 'Memory deleted.');

            return redirect()->route('vault.gallery');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.vault.memory-view')->layout('layouts.app');
    }

    protected function refreshUnlockState(): void
    {
        $vaultService = app(VaultService::class);
        $this->memory = $this->memory->fresh(['creator', 'reactions.user', 'couple.users', 'unlockApprovals']);
        $this->unlockStatus = $vaultService->getUnlockStatus($this->memory, auth()->user());
        $this->canViewContent = Gate::forUser(auth()->user())->check('viewContent', $this->memory);
        $this->canToggleComfort = Gate::forUser(auth()->user())->check('toggleComfort', $this->memory);
    }
}
