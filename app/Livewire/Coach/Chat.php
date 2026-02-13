<?php

namespace App\Livewire\Coach;

use App\Models\AiBridgeSuggestion;
use App\Models\AiChat;
use App\Services\CoupleService;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $chat;

    public $messages = [];

    public $newMessage = '';

    public $mode = 'vent'; // 'vent' or 'bridge'

    public $isTyping = false;

    public $bridgeSuggestions = [];

    public function mount()
    {
        $user = Auth::user();
        $couple = app(CoupleService::class)->getUserCouple($user);

        if (! $couple) {
            return redirect()->route('dashboard');
        }

        // Find or create active chat session
        $this->chat = AiChat::firstOrCreate(
            ['user_id' => $user->id, 'couple_id' => $couple->id, 'is_active' => true],
            ['type' => 'vent', 'messages' => []]
        );

        $this->mode = $this->chat->type;
        $this->messages = $this->chat->messages ?? [];
        $this->loadBridgeSuggestions();

        // Add initial greeting if empty
        if (empty($this->messages)) {
            $this->addBotMessage($this->getGreeting());
        }
    }

    public function getGreeting()
    {
        return $this->mode === 'vent'
            ? "I'm here to listen. What's on your mind? You can say anything here safely."
            : "Let's turn that frustration into a constructive message. What do you want to tell your partner?";
    }

    public function sendMessage(GeminiService $aiService)
    {
        if (trim($this->newMessage) === '') {
            return;
        }

        $userMessage = $this->newMessage;
        $this->newMessage = '';

        // Add user message to local state and DB
        $this->addMessage('user', $userMessage);

        // Set typing state (simulated for UI)
        $this->isTyping = true;
    }

    public function generateResponse(GeminiService $aiService)
    {
        // Retrieve fresh from DB to ensure context
        $history = $this->chat->refresh()->messages ?? [];

        // Filter history for API context (limit to last 10 messages for cost/efficiency)
        $context = array_slice($history, -10);

        // Call AI
        $response = $aiService->chat($context, $this->mode);

        if ($this->mode === 'bridge') {
            $aiService->createDraftSuggestion(
                $this->chat->couple,
                Auth::user(),
                $response,
                [
                    'mode' => 'bridge',
                    'ai_chat_id' => $this->chat->id,
                ]
            );

            $this->loadBridgeSuggestions();
        } else {
            $this->addMessage('assistant', $response);
        }

        $this->isTyping = false;
    }

    protected function addMessage($role, $content)
    {
        // Update local state
        $this->messages[] = ['role' => $role, 'content' => $content];

        // Persist
        $this->chat->addMessage($role, $content);
    }

    // Helper to add bot message directly (for greeting)
    protected function addBotMessage($content)
    {
        $this->addMessage('assistant', $content);
    }

    public function switchMode($newMode)
    {
        if ($this->mode === $newMode) {
            return;
        }

        $this->mode = $newMode;

        // Archive current chat and start fresh one
        $this->chat->update(['is_active' => false]);

        $this->chat = AiChat::create([
            'user_id' => Auth::id(),
            'couple_id' => $this->chat->couple_id,
            'type' => $newMode,
            'messages' => [],
            'is_active' => true,
        ]);

        $this->messages = [];
        $this->loadBridgeSuggestions();
        $this->addBotMessage($this->getGreeting());
    }

    public function approveSuggestion(int $suggestionId, GeminiService $aiService)
    {
        try {
            $aiService->approveSuggestion($suggestionId, Auth::id());
            $this->loadBridgeSuggestions();
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function discardSuggestion(int $suggestionId, GeminiService $aiService)
    {
        try {
            $aiService->discardSuggestion($suggestionId, Auth::id());
            $this->loadBridgeSuggestions();
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function sendApprovedSuggestionToChat(int $suggestionId, GeminiService $aiService)
    {
        try {
            $aiService->sendApprovedSuggestionToChat($suggestionId, Auth::id());
            $this->loadBridgeSuggestions();
            session()->flash('message', 'Suggestion sent to partner.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    protected function loadBridgeSuggestions(): void
    {
        $this->bridgeSuggestions = AiBridgeSuggestion::query()
            ->where('user_id', Auth::id())
            ->where('couple_id', $this->chat->couple_id)
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.coach.chat')->layout('layouts.app');
    }
}
