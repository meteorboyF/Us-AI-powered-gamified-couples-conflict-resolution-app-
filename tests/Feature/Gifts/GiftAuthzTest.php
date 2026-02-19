<?php

namespace Tests\Feature\Gifts;

use App\Models\GiftRequest;
use App\Models\GiftSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Gifts\Concerns\CreatesGiftContext;
use Tests\TestCase;

class GiftAuthzTest extends TestCase
{
    use CreatesGiftContext;
    use RefreshDatabase;

    public function test_user_without_current_couple_gets_conflict_on_create_and_generate(): void
    {
        $this->enableGiftsFeature();
        $user = User::factory()->create(['current_couple_id' => null]);
        $owner = User::factory()->create();
        [$ownerCouple] = $this->createCoupleWithPartner($owner);

        $giftRequest = GiftRequest::query()->create([
            'couple_id' => $ownerCouple->id,
            'created_by_user_id' => $owner->id,
            'occasion' => 'sorry',
            'budget_min' => null,
            'budget_max' => null,
            'time_constraint' => null,
            'notes' => null,
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->postJson('/gifts/requests', ['occasion' => 'surprise'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'No couple selected');

        $this->actingAs($user)
            ->postJson('/gifts/requests/'.$giftRequest->id.'/generate')
            ->assertStatus(409)
            ->assertJsonPath('message', 'No couple selected');
    }

    public function test_user_in_different_couple_gets_forbidden_on_show_and_favorite(): void
    {
        $this->enableGiftsFeature();

        $owner = User::factory()->create();
        [$ownerCouple] = $this->createCoupleWithPartner($owner);

        $otherUser = User::factory()->create();
        [$otherCouple] = $this->createCoupleWithPartner($otherUser);

        $giftRequest = GiftRequest::query()->create([
            'couple_id' => $ownerCouple->id,
            'created_by_user_id' => $owner->id,
            'occasion' => 'date_night',
            'budget_min' => null,
            'budget_max' => null,
            'time_constraint' => null,
            'notes' => null,
            'meta' => [],
        ]);

        $suggestion = GiftSuggestion::query()->create([
            'gift_request_id' => $giftRequest->id,
            'title' => 'Home Movie Night',
            'category' => 'experience',
            'price_band' => 'low',
            'rationale' => 'Easy to do at home.',
            'personalization_tip' => null,
            'is_favorite' => false,
            'meta' => [],
        ]);

        $otherUser->forceFill(['current_couple_id' => $otherCouple->id])->save();

        $this->actingAs($otherUser)
            ->getJson('/gifts/requests/'.$giftRequest->id)
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->postJson('/gifts/suggestions/'.$suggestion->id.'/favorite')
            ->assertForbidden();
    }
}
