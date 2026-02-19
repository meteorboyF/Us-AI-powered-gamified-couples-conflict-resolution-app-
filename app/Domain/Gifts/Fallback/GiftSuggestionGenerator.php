<?php

namespace App\Domain\Gifts\Fallback;

use App\Models\GiftRequest;

class GiftSuggestionGenerator
{
    /**
     * @return list<array<string, mixed>>
     */
    public function generate(GiftRequest $request): array
    {
        $priceBand = $this->resolvePriceBand($request->budget_max);
        $occasion = $request->occasion;
        $timeConstraint = $request->time_constraint ?: 'flexible';

        return [
            $this->item('Handwritten Appreciation Letter', 'no_purchase', 'free', "Write a heartfelt {$occasion} letter focused on gratitude and specific memories.", 'Mention one moment from this week that made you smile.'),
            $this->item('Curated Playlist + Voice Note', 'no_purchase', 'free', "A personal playlist gives emotional support without spending and works well for {$timeConstraint}.", 'Add songs tied to inside jokes or milestones.'),
            $this->item('Coupon Jar', 'no_purchase', 'free', 'Create redeemable mini-promises like coffee dates, chores swap, or one uninterrupted listening session.', 'Include one coupon tailored to your partner’s current stress.'),
            $this->item('Mini At-Home Date Plan', 'no_purchase', 'free', 'Plan a 45-minute date with one activity, one snack, and one check-in question.', 'Use a theme tied to your first date or favorite shared hobby.'),
            $this->item($this->titleForOccasion($occasion, 'Comfort Box'), 'care_package', $priceBand, "A compact care package shows practical care and fits {$timeConstraint} delivery constraints.", $this->tipForBudget($request->budget_max)),
            $this->item($this->titleForOccasion($occasion, 'Memory Photo Set'), 'memories', $priceBand, 'Printed photos or a mini album reinforces positive shared memories during reconnection.', 'Add short captions with what you felt in each moment.'),
            $this->item($this->titleForOccasion($occasion, 'Experience Voucher'), 'experience', $priceBand, 'A planned experience builds anticipation and shared time beyond material gifts.', 'Pick an activity that can be done within the next seven days.'),
            $this->item($this->titleForOccasion($occasion, 'Favorite Snack + Note'), 'food', $priceBand, 'A small familiar treat paired with a note keeps the gesture simple and warm.', 'Reference a recent conversation where they felt unheard and validate it.'),
        ];
    }

    private function resolvePriceBand(?int $budgetMax): string
    {
        if ($budgetMax === null || $budgetMax <= 0) {
            return 'low';
        }

        if ($budgetMax <= 1000) {
            return 'low';
        }

        if ($budgetMax <= 3000) {
            return 'mid';
        }

        return 'high';
    }

    private function titleForOccasion(string $occasion, string $base): string
    {
        return ucfirst($occasion).' '.$base;
    }

    private function tipForBudget(?int $budgetMax): string
    {
        if ($budgetMax === null || $budgetMax <= 0) {
            return 'Use items already at home and add a personal note.';
        }

        return "Keep total spend below {$budgetMax} and prioritize one meaningful item.";
    }

    /**
     * @return array<string, mixed>
     */
    private function item(string $title, string $category, string $priceBand, string $rationale, ?string $tip): array
    {
        return [
            'title' => $title,
            'category' => $category,
            'price_band' => $priceBand,
            'rationale' => $rationale,
            'personalization_tip' => $tip,
            'meta' => [],
        ];
    }
}
