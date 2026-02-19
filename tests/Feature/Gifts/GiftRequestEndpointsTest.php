<?php

namespace Tests\Feature\Gifts;

use App\Models\GiftRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Gifts\Concerns\CreatesGiftContext;
use Tests\TestCase;

class GiftRequestEndpointsTest extends TestCase
{
    use CreatesGiftContext;
    use RefreshDatabase;

    public function test_member_can_create_gift_request(): void
    {
        $this->enableGiftsFeature();
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->postJson('/gifts/requests', [
                'occasion' => 'anniversary',
                'budget_min' => 500,
                'budget_max' => 2000,
                'time_constraint' => 'this week',
                'notes' => 'Simple and meaningful',
            ])
            ->assertCreated()
            ->assertJsonPath('request.couple_id', $couple->id);

        $this->assertDatabaseHas('gift_requests', [
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'occasion' => 'anniversary',
        ]);
    }

    public function test_member_can_show_gift_request_with_same_couple_scope(): void
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

        $this->actingAs($user)
            ->getJson('/gifts/requests/'.$giftRequest->id)
            ->assertOk()
            ->assertJsonPath('request.couple_id', $couple->id);
    }
}
