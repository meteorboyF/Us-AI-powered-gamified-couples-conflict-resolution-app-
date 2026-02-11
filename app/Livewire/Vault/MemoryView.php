<?php

namespace App\Livewire\Vault;

use App\Models\Memory;
use App\Models\MemoryReaction;
use App\Services\VaultService;
use Livewire\Component;

class MemoryView extends Component
{
    public $memory;
    public $myReaction;
    public $partnerReaction;
    public $reactions;

    public function mount($memoryId)
    {
        $this->memory = Memory::with(['creator', 'reactions.user'])->findOrFail($memoryId);

        // Verify user can view this memory
        if (!$this->memory->canBeViewedBy(auth()->user())) {
            abort(403);
        }

        $this->loadReactions();
        $this->reactions = MemoryReaction::getReactionTypes();
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

        try {
            $vaultService->addReaction($this->memory, auth()->user(), $reaction);
            $this->loadReactions();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function removeReaction()
    {
        $vaultService = app(VaultService::class);
        $vaultService->removeReaction($this->memory, auth()->user());
        $this->loadReactions();
    }

    public function updateVisibility($visibility)
    {
        $vaultService = app(VaultService::class);

        try {
            $this->memory = $vaultService->changeVisibility($this->memory, auth()->user(), $visibility);
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
            session()->flash('message', 'Memory locked! +10 XP');
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
        return view('livewire.vault.memory-view');
    }
}
