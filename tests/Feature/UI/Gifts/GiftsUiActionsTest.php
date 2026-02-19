<?php

namespace Tests\Feature\UI\Gifts;

use App\Models\GiftRequest;
use App\Models\GiftSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\UI\Gifts\Concerns\CreatesGiftsUiContext;
use Tests\TestCase;

class GiftsUiActionsTest extends TestCase
{
    use CreatesGiftsUiContext;
    use RefreshDatabase;

    public function test_create_request_creates_row_and_redirects(): void
    {
        $this->enableGiftsFeature();
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->post('/gifts-ui/request', [
                'occasion' => 'anniversary',
                'budget_min' => 500,
                'budget_max' => 2000,
                'time_constraint' => 'this week',
                'notes' => 'Thoughtful ideas',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('gift_requests', [
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'occasion' => 'anniversary',
        ]);
    }

    public function test_generate_creates_suggestions_and_is_idempotent(): void
    {
        $this->enableGiftsFeature();
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $giftRequest = GiftRequest::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'occasion' => 'comfort',
            'budget_min' => 0,
            'budget_max' => 1500,
            'time_constraint' => 'today',
            'notes' => 'Keep it simple.',
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->post('/gifts-ui/'.$giftRequest->id.'/generate')
            ->assertRedirect(route('gifts.ui', ['request_id' => $giftRequest->id]));

        $this->assertDatabaseCount('gift_suggestions', 8);

        $this->actingAs($user)
            ->post('/gifts-ui/'.$giftRequest->id.'/generate')
            ->assertRedirect(route('gifts.ui', ['request_id' => $giftRequest->id]));

        $this->assertDatabaseCount('gift_suggestions', 8);
    }

    public function test_favorite_toggles_true_then_false(): void
    {
        $this->enableGiftsFeature();
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $giftRequest = GiftRequest::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'occasion' => 'surprise',
            'budget_min' => null,
            'budget_max' => null,
            'time_constraint' => null,
            'notes' => null,
            'meta' => [],
        ]);

        $suggestion = GiftSuggestion::query()->create([
            'gift_request_id' => $giftRequest->id,
            'title' => 'Playlist + Note',
            'category' => 'no_purchase',
            'price_band' => 'free',
            'rationale' => 'Fast and personal',
            'personalization_tip' => 'Use shared songs',
            'is_favorite' => false,
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->post('/gifts-ui/suggestions/'.$suggestion->id.'/favorite')
            ->assertRedirect(route('gifts.ui', ['request_id' => $giftRequest->id]));

        $this->assertDatabaseHas('gift_suggestions', [
            'id' => $suggestion->id,
            'is_favorite' => true,
        ]);

        $this->actingAs($user)
            ->post('/gifts-ui/suggestions/'.$suggestion->id.'/favorite')
            ->assertRedirect(route('gifts.ui', ['request_id' => $giftRequest->id]));

        $this->assertDatabaseHas('gift_suggestions', [
            'id' => $suggestion->id,
            'is_favorite' => false,
        ]);
    }
}
