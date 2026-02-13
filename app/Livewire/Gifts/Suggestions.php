<?php

namespace App\Livewire\Gifts;

use App\Models\GiftSuggestion;
use App\Services\CoupleService;
use App\Services\GiftSuggestionService;
use Livewire\Component;

class Suggestions extends Component
{
    public array $cards = [];

    public ?string $source = null;

    public ?string $statusMessage = null;

    public array $history = [];

    public function mount(CoupleService $coupleService): void
    {
        $couple = $coupleService->getUserCouple(auth()->user());
        if (! $couple) {
            return;
        }

        $this->history = GiftSuggestion::query()
            ->where('couple_id', $couple->id)
            ->where('requested_by', auth()->id())
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function generate(GiftSuggestionService $service): void
    {
        $suggestion = $service->generateForUser(auth()->user());

        $this->cards = $suggestion->suggestions ?? [];
        $this->source = $suggestion->source;
        $this->statusMessage = $suggestion->source === 'fallback'
            ? 'AI was unavailable, so we used safe built-in suggestions.'
            : 'Fresh suggestions are ready.';

        $this->mount(app(CoupleService::class));
    }

    public function render()
    {
        return view('livewire.gifts.suggestions');
    }
}
