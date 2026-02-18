<?php

namespace Tests\Feature\Couples;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoupleLinkingEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_couple_and_becomes_owner_with_current_couple_set(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/couples', [
            'name' => 'Us Pair',
        ]);

        $response->assertCreated();

        $coupleId = (int) $response->json('couple_id');

        $this->assertDatabaseHas('couples', [
            'id' => $coupleId,
            'name' => 'Us Pair',
            'created_by_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('couple_members', [
            'couple_id' => $coupleId,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        $this->assertSame($coupleId, $user->fresh()->current_couple_id);
    }

    public function test_user_can_join_by_invite_code_and_becomes_member_with_current_couple_set(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();

        $couple = Couple::query()->create([
            'name' => 'Starter Couple',
            'invite_code' => 'JOINAB12',
            'created_by_user_id' => $owner->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($joiner)->postJson('/couples/join', [
            'invite_code' => 'JOINAB12',
        ]);

        $response->assertOk()
            ->assertJson(['couple_id' => $couple->id]);

        $this->assertDatabaseHas('couple_members', [
            'couple_id' => $couple->id,
            'user_id' => $joiner->id,
            'role' => 'member',
        ]);

        $this->assertSame($couple->id, $joiner->fresh()->current_couple_id);
    }

    public function test_non_member_cannot_switch_to_a_couple_they_do_not_belong_to(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $couple = Couple::query()->create([
            'name' => 'Private Couple',
            'invite_code' => 'LOCKED123',
            'created_by_user_id' => $owner->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $this->actingAs($intruder)->postJson('/couples/switch', [
            'couple_id' => $couple->id,
        ])->assertForbidden();

        $this->assertNull($intruder->fresh()->current_couple_id);
    }
}
