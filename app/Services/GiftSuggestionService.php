<?php

namespace App\Services;

use App\Models\GiftSuggestion;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class GiftSuggestionService
{
    protected const CATEGORIES = [
        'low-effort',
        'experience',
        'budget',
        'thoughtful',
        'physical',
    ];

    public function __construct(
        protected CoupleService $coupleService,
        protected GeminiService $geminiService
    ) {}

    public function generateForUser(User $requester): GiftSuggestion
    {
        $couple = $this->coupleService->getUserCouple($requester);

        if (! $couple) {
            throw new AuthorizationException('Unauthorized couple access.');
        }

        $requesterWishlist = Wishlist::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', $requester->id)
            ->first();

        $partner = $couple->users()
            ->where('users.id', '!=', $requester->id)
            ->first();

        $partnerWishlist = null;
        if ($partner) {
            $candidate = Wishlist::query()
                ->where('couple_id', $couple->id)
                ->where('user_id', $partner->id)
                ->first();
            $partnerWishlist = ($candidate && $candidate->share_with_partner) ? $candidate : null;
        }

        $snapshot = [
            'requester_wishlist' => $this->wishlistSnapshot($requesterWishlist),
            'partner_wishlist' => $this->wishlistSnapshot($partnerWishlist),
            'budget' => $this->resolveBudget($requesterWishlist, $partnerWishlist),
            'theme_type' => optional($couple->world)->theme_type,
        ];

        $geminiSuggestions = $this->generateWithGemini($snapshot);

        if ($geminiSuggestions !== null) {
            return GiftSuggestion::create([
                'couple_id' => $couple->id,
                'requested_by' => $requester->id,
                'input_snapshot' => $snapshot,
                'suggestions' => $geminiSuggestions,
                'source' => 'gemini',
                'created_at' => now(),
            ]);
        }

        $fallbackSuggestions = $this->generateFallbackSuggestions($snapshot);

        return GiftSuggestion::create([
            'couple_id' => $couple->id,
            'requested_by' => $requester->id,
            'input_snapshot' => $snapshot,
            'suggestions' => $fallbackSuggestions,
            'source' => 'fallback',
            'created_at' => now(),
        ]);
    }

    protected function generateWithGemini(array $snapshot): ?array
    {
        $systemInstruction = 'You are a couples gift/date suggestion assistant. Return JSON only. Do not diagnose, do not include therapy claims, and avoid explicit or manipulative content.';

        $basePrompt = 'Generate exactly 10 to 12 suggestion cards as a JSON array. '.
            'Each card must include: title, category, description, why_it_fits, estimated_cost (optional), time_required (optional). '.
            'Allowed categories: low-effort, experience, budget, thoughtful, physical. '.
            'description must be 1 to 2 lines. why_it_fits must be 1 line. '.
            'Respect dislikes and budget when possible. Input: '.json_encode($snapshot);

        $firstAttempt = $this->geminiService->generateWithSystemInstruction([
            ['role' => 'user', 'content' => $basePrompt],
        ], $systemInstruction, 'bridge');

        if (($firstAttempt['source'] ?? 'fallback') !== 'gemini') {
            return null;
        }

        $parsed = $this->parseAndValidateSuggestions($firstAttempt['text'] ?? '');
        if ($parsed !== null) {
            return $parsed;
        }

        $retryPrompt = $basePrompt.' Return valid JSON only. Do not include markdown fences.';
        $secondAttempt = $this->geminiService->generateWithSystemInstruction([
            ['role' => 'user', 'content' => $retryPrompt],
        ], $systemInstruction, 'bridge');

        if (($secondAttempt['source'] ?? 'fallback') !== 'gemini') {
            return null;
        }

        $retryParsed = $this->parseAndValidateSuggestions($secondAttempt['text'] ?? '');
        if ($retryParsed !== null) {
            return $retryParsed;
        }

        Log::warning('Gemini gift suggestions returned invalid JSON structure', [
            'has_text' => ! empty($secondAttempt['text'] ?? ''),
        ]);

        return null;
    }

    protected function parseAndValidateSuggestions(string $raw): ?array
    {
        $clean = trim($raw);
        if ($clean === '') {
            return null;
        }

        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $clean) ?? $clean;
            $clean = trim($clean);
        }

        $decoded = json_decode($clean, true);
        if (! is_array($decoded)) {
            return null;
        }

        if (count($decoded) < 10 || count($decoded) > 12) {
            return null;
        }

        $normalized = [];
        foreach ($decoded as $card) {
            if (! is_array($card)) {
                return null;
            }

            $title = trim((string) ($card['title'] ?? ''));
            $category = trim((string) ($card['category'] ?? ''));
            $description = trim((string) ($card['description'] ?? ''));
            $why = trim((string) ($card['why_it_fits'] ?? ''));

            if ($title === '' || $description === '' || $why === '' || ! in_array($category, self::CATEGORIES, true)) {
                return null;
            }

            $normalized[] = [
                'title' => $title,
                'category' => $category,
                'description' => $description,
                'why_it_fits' => $why,
                'estimated_cost' => Arr::has($card, 'estimated_cost') ? (string) $card['estimated_cost'] : null,
                'time_required' => Arr::has($card, 'time_required') ? (string) $card['time_required'] : null,
            ];
        }

        return $normalized;
    }

    protected function generateFallbackSuggestions(array $snapshot): array
    {
        $budget = $snapshot['budget'] ?? [];
        $currency = $budget['currency'] ?? 'USD';
        $dislikes = collect($snapshot['requester_wishlist']['dislikes'] ?? [])
            ->merge($snapshot['partner_wishlist']['dislikes'] ?? [])
            ->map(fn ($item) => mb_strtolower((string) $item))
            ->filter()
            ->values()
            ->all();

        $pool = [
            ['title' => 'Sunset Walk + Voice Note', 'category' => 'low-effort', 'description' => 'Take a short walk and end by trading one appreciation each.', 'why_it_fits' => 'Low pressure and connection-focused.', 'estimated_cost' => $currency.'0', 'time_required' => '30-45 min'],
            ['title' => 'Favorite Snack Surprise', 'category' => 'budget', 'description' => 'Pick up one snack or drink they already love and leave a note with it.', 'why_it_fits' => 'Small effort that feels personal.', 'estimated_cost' => $currency.'5-'.$currency.'15', 'time_required' => '15 min'],
            ['title' => 'At-Home Cafe Date', 'category' => 'experience', 'description' => 'Make coffee or tea at home, set a playlist, and have a no-phone chat.', 'why_it_fits' => 'Creates quality time without travel.', 'estimated_cost' => $currency.'5-'.$currency.'20', 'time_required' => '45 min'],
            ['title' => 'Memory Jar Prompt Cards', 'category' => 'thoughtful', 'description' => 'Write 10 memories or future plans on folded cards in a jar.', 'why_it_fits' => 'Builds emotional safety and shared meaning.', 'estimated_cost' => $currency.'0-'.$currency.'10', 'time_required' => '30 min'],
            ['title' => 'Mini Home Spa Kit', 'category' => 'physical', 'description' => 'Assemble a simple comfort kit: candle, lotion, and calming playlist.', 'why_it_fits' => 'Supports rest and affection.', 'estimated_cost' => $currency.'15-'.$currency.'40', 'time_required' => '20 min'],
            ['title' => 'Two-Hour Adventure Block', 'category' => 'experience', 'description' => 'Pick a nearby park, bookstore, or cafe and plan a fixed two-hour outing.', 'why_it_fits' => 'Predictable structure helps follow-through.', 'estimated_cost' => $currency.'10-'.$currency.'50', 'time_required' => '2 hours'],
            ['title' => 'Shared Chore Relief Coupon', 'category' => 'thoughtful', 'description' => 'Offer a specific task takeover this week with a handwritten coupon.', 'why_it_fits' => 'Turns care into practical support.', 'estimated_cost' => $currency.'0', 'time_required' => 'Varies'],
            ['title' => 'Photo Recap Slideshow', 'category' => 'low-effort', 'description' => 'Create a short slideshow of favorite moments and one hopeful caption each.', 'why_it_fits' => 'Reinforces positive memories.', 'estimated_cost' => $currency.'0', 'time_required' => '30 min'],
            ['title' => 'Budget Picnic Pack', 'category' => 'budget', 'description' => 'Pack simple food from home and spend time outdoors with one conversation prompt.', 'why_it_fits' => 'Affordable quality time.', 'estimated_cost' => $currency.'10-'.$currency.'25', 'time_required' => '1-2 hours'],
            ['title' => 'Comfort Hoodie + Note', 'category' => 'physical', 'description' => 'Gift a cozy wearable item with one sentence of appreciation.', 'why_it_fits' => 'Tangible comfort plus emotional warmth.', 'estimated_cost' => $currency.'20-'.$currency.'60', 'time_required' => '20 min'],
            ['title' => 'Theme Night at Home', 'category' => 'experience', 'description' => 'Choose a country/theme, cook one dish, and watch one related film clip.', 'why_it_fits' => 'Fun novelty without big planning overhead.', 'estimated_cost' => $currency.'15-'.$currency.'40', 'time_required' => '2 hours'],
            ['title' => 'Repair Conversation Starter Card', 'category' => 'thoughtful', 'description' => 'Write: "I feel... when... because I need... I would appreciate..." and invite a calm chat.', 'why_it_fits' => 'Supports constructive conflict repair.', 'estimated_cost' => $currency.'0', 'time_required' => '20 min'],
        ];

        $filtered = array_values(array_filter($pool, function ($card) use ($dislikes) {
            $haystack = mb_strtolower($card['title'].' '.$card['description'].' '.$card['why_it_fits']);

            foreach ($dislikes as $dislike) {
                if ($dislike !== '' && str_contains($haystack, $dislike)) {
                    return false;
                }
            }

            return true;
        }));

        if (count($filtered) < 10) {
            $filtered = $pool;
        }

        return array_slice($filtered, 0, 10);
    }

    protected function wishlistSnapshot(?Wishlist $wishlist): ?array
    {
        if (! $wishlist) {
            return null;
        }

        return [
            'budget_min' => $wishlist->budget_min,
            'budget_max' => $wishlist->budget_max,
            'currency' => $wishlist->currency,
            'love_languages' => $wishlist->love_languages ?? [],
            'likes' => $wishlist->likes ?? [],
            'dislikes' => $wishlist->dislikes ?? [],
            'share_with_partner' => $wishlist->share_with_partner,
        ];
    }

    protected function resolveBudget(?Wishlist $requesterWishlist, ?Wishlist $partnerWishlist): array
    {
        $budgetMin = $requesterWishlist?->budget_min ?? $partnerWishlist?->budget_min;
        $budgetMax = $requesterWishlist?->budget_max ?? $partnerWishlist?->budget_max;
        $currency = $requesterWishlist?->currency ?? $partnerWishlist?->currency ?? 'USD';

        return [
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'currency' => $currency,
        ];
    }
}
