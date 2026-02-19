<?php

namespace Tests\Feature\Gifts;

use App\Models\GiftRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Gifts\Concerns\CreatesGiftContext;
use Tests\TestCase;

class GiftGenerateFallbackTest extends TestCase
{
    use CreatesGiftContext;
    use RefreshDatabase;

    public function test_generate_returns_fallback_suggestions_and_is_idempotent(): void
    {
        $this->enableGiftsFeature();
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $giftRequest = GiftRequest::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'occasion' => 'comfort',
            'budget_min' => 0,
            'budget_max' => 1000,
            'time_constraint' => 'today',
            'notes' => 'No pressure ideas',
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->postJson('/gifts/requests/'.$giftRequest->id.'/generate')
            ->assertOk()
            ->assertJsonCount(8, 'suggestions');

        $this->assertDatabaseCount('gift_suggestions', 8);

        $this->actingAs($user)
            ->postJson('/gifts/requests/'.$giftRequest->id.'/generate')
            ->assertOk()
            ->assertJsonCount(8, 'suggestions');

        $this->assertDatabaseCount('gift_suggestions', 8);
    }
}
