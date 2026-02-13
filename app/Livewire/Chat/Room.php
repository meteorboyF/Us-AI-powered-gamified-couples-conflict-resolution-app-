<?php

namespace App\Livewire\Chat;

use App\Services\ChatService;
use App\Services\CoupleService;
use Livewire\Component;

class Room extends Component
{
    public $messages;

    public $newMessage = '';

    public $couple;

    public $partner;

    public $loveButtons;

    public $remainingButtons;

    public $nextAvailableAt;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $this->couple = $coupleService->getUserCouple(auth()->user());

        if ($this->couple) {
            $this->partner = $this->couple->users()
                ->where('users.id', '!=', auth()->id())
                ->first();

            $this->loadMessages();
            $this->loadLoveButtonStatus();
        }
    }

    public function loadMessages()
    {
        if (! $this->couple) {
            return;
        }

        $chatService = app(ChatService::class);
        $this->messages = $chatService->getMessages($this->couple, auth()->user());

        // Mark messages as read
        $chatService->markMessagesAsRead($this->couple, auth()->user());
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:1000',
        ]);

        $chatService = app(ChatService::class);

        try {
            $chatService->sendMessage($this->couple, auth()->user(), $this->newMessage);
            $this->newMessage = '';
            $this->loadMessages();

            // Dispatch browser event to scroll to bottom
            $this->dispatch('message-sent');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function sendLoveButton($type)
    {
        $chatService = app(ChatService::class);

        try {
            $chatService->sendLoveButton($this->couple, auth()->user(), $type);
            $this->loadMessages();
            $this->loadLoveButtonStatus();

            session()->flash('message', 'Love button sent! +5 XP');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function loadLoveButtonStatus()
    {
        $chatService = app(ChatService::class);
        $this->loveButtons = ChatService::getLoveButtons();
        $this->remainingButtons = $chatService->getRemainingLoveButtons(auth()->user());
        $this->nextAvailableAt = $chatService->getNextLoveButtonAvailableAt(auth()->user());
    }

    public function render()
    {
        return view('livewire.chat.room');
    }
}
