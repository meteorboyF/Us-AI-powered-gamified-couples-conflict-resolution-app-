<?php

namespace Tests\Feature\Gifts;

use App\Models\GiftRequest;
use App\Models\GiftSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Gifts\Concerns\CreatesGiftContext;
use Tests\TestCase;

class GiftFavoriteToggleTest extends TestCase
{
    use CreatesGiftContext;
    use RefreshDatabase;

    public function test_toggle_favorite_flips_state_true_then_false(): void
    {
        $this->enableGiftsFeature();
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $giftRequest = GiftRequest::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'occasion' => 'birthday',
            'budget_min' => null,
            'budget_max' => null,
            'time_constraint' => null,
            'notes' => null,
            'meta' => [],
        ]);

        $suggestion = GiftSuggestion::query()->create([
            'gift_request_id' => $giftRequest->id,
            'title' => 'Custom Playlist',
            'category' => 'no_purchase',
            'price_band' => 'free',
            'rationale' => 'Personal and immediate.',
            'personalization_tip' => 'Pick shared songs.',
            'is_favorite' => false,
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->postJson('/gifts/suggestions/'.$suggestion->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('suggestion.is_favorite', true);

        $this->assertDatabaseHas('gift_suggestions', [
            'id' => $suggestion->id,
            'is_favorite' => true,
        ]);

        $this->actingAs($user)
            ->postJson('/gifts/suggestions/'.$suggestion->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('suggestion.is_favorite', false);

        $this->assertDatabaseHas('gift_suggestions', [
            'id' => $suggestion->id,
            'is_favorite' => false,
        ]);
    }
}
