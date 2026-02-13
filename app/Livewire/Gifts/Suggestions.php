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
        $meta = $service->getLastGenerationMeta();

        $this->cards = $suggestion->suggestions ?? [];
        $this->source = $suggestion->source;
        $this->statusMessage = $suggestion->source === 'fallback'
            ? $this->formatFallbackNotice(
                'AI is busy right now, showing a safe fallback suggestion.',
                $meta['correlation_id'] ?? null
            )
            : 'Fresh suggestions are ready.';

        $this->mount(app(CoupleService::class));
    }

    public function render()
    {
        return view('livewire.gifts.suggestions');
    }

    protected function formatFallbackNotice(string $message, ?string $correlationId): string
    {
        if (! config('app.debug') || empty($correlationId)) {
            return $message;
        }

        return $message.' Ref: '.$correlationId;
    }
}
