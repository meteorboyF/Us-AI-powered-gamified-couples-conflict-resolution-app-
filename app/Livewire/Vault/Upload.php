<?php

namespace App\Livewire\Vault;

use App\Services\CoupleService;
use App\Services\VaultService;
use Livewire\Component;
use Livewire\WithFileUploads;

class Upload extends Component
{
    use WithFileUploads;

    public $uploadType = 'photo';

    public $file;

    public $title;

    public $description;

    public $visibility = 'shared';

    public $comfort = false;

    public $couple;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $this->couple = $coupleService->getUserCouple(auth()->user());
    }

    public function setUploadType($type)
    {
        $this->uploadType = $type;
        $this->reset(['file', 'title', 'description']);
    }

    public function save()
    {
        $this->validate([
            'file' => $this->uploadType === 'text' ? '' : 'required|file',
            'description' => $this->uploadType === 'text' ? 'required|max:1000' : 'nullable|max:500',
            'title' => 'nullable|max:100',
            'visibility' => 'required|in:private,shared,dual',
            'comfort' => 'boolean',
        ]);

        $vaultService = app(VaultService::class);

        try {
            $data = [
                'title' => $this->title,
                'description' => $this->description,
                'visibility' => $this->visibility,
                'comfort' => $this->comfort,
            ];

            switch ($this->uploadType) {
                case 'photo':
                    $vaultService->uploadPhoto($this->couple, auth()->user(), $this->file, $data);
                    $message = 'Photo uploaded! +5 XP';
                    break;
                case 'video':
                    $vaultService->uploadVideo($this->couple, auth()->user(), $this->file, $data);
                    $message = 'Video uploaded! +10 XP';
                    break;
                case 'voice_note':
                    $vaultService->uploadVoiceNote($this->couple, auth()->user(), $this->file, $data);
                    $message = 'Voice note uploaded! +5 XP';
                    break;
                case 'text':
                    $vaultService->createTextMemory($this->couple, auth()->user(), $data);
                    $message = 'Memory created! +3 XP';
                    break;
            }

            session()->flash('message', $message);

            return redirect()->route('vault.gallery');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.vault.upload');
    }
}
