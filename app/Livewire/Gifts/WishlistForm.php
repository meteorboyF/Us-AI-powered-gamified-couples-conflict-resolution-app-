<?php

namespace App\Livewire\Gifts;

use App\Models\Wishlist;
use App\Services\CoupleService;
use Livewire\Component;

class WishlistForm extends Component
{
    public ?int $budgetMin = null;

    public ?int $budgetMax = null;

    public string $currency = 'USD';

    public string $loveLanguages = '';

    public string $likes = '';

    public string $dislikes = '';

    public bool $shareWithPartner = true;

    protected array $rules = [
        'budgetMin' => 'nullable|integer|min:0|max:1000000',
        'budgetMax' => 'nullable|integer|min:0|max:1000000|gte:budgetMin',
        'currency' => 'nullable|string|max:10',
        'loveLanguages' => 'nullable|string|max:1000',
        'likes' => 'nullable|string|max:2000',
        'dislikes' => 'nullable|string|max:2000',
        'shareWithPartner' => 'boolean',
    ];

    public function mount(CoupleService $coupleService): void
    {
        $couple = $coupleService->getUserCouple(auth()->user());
        if (! $couple) {
            return;
        }

        $wishlist = Wishlist::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $wishlist) {
            return;
        }

        $this->budgetMin = $wishlist->budget_min;
        $this->budgetMax = $wishlist->budget_max;
        $this->currency = $wishlist->currency ?? 'USD';
        $this->loveLanguages = $this->csv($wishlist->love_languages ?? []);
        $this->likes = $this->csv($wishlist->likes ?? []);
        $this->dislikes = $this->csv($wishlist->dislikes ?? []);
        $this->shareWithPartner = (bool) $wishlist->share_with_partner;
    }

    public function save(CoupleService $coupleService): void
    {
        $this->validate();

        $couple = $coupleService->getUserCouple(auth()->user());
        if (! $couple) {
            session()->flash('error', 'You need to be in a couple to use wishlist features.');

            return;
        }

        Wishlist::updateOrCreate(
            [
                'couple_id' => $couple->id,
                'user_id' => auth()->id(),
            ],
            [
                'budget_min' => $this->budgetMin,
                'budget_max' => $this->budgetMax,
                'currency' => $this->currency !== '' ? strtoupper($this->currency) : null,
                'love_languages' => $this->listFromCsv($this->loveLanguages),
                'likes' => $this->listFromCsv($this->likes),
                'dislikes' => $this->listFromCsv($this->dislikes),
                'share_with_partner' => $this->shareWithPartner,
            ]
        );

        session()->flash('message', 'Wishlist saved.');
    }

    protected function listFromCsv(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    protected function csv(array $items): string
    {
        return implode(', ', $items);
    }

    public function render()
    {
        return view('livewire.gifts.wishlist-form');
    }
}
