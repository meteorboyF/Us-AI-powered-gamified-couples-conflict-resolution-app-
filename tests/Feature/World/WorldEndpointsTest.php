<?php

namespace Tests\Feature\World;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use App\Models\WorldItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorldEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_world_returns_state_for_authenticated_user_with_current_couple(): void
    {
        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);

        $home = WorldItem::query()->create([
            'key' => 'home_base',
            'title' => 'Home Base',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $garden = WorldItem::query()->create([
            'key' => 'garden',
            'title' => 'Garden',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $couple->worldItems()->syncWithoutDetaching([
            $home->id => ['unlocked_at' => now()],
        ]);

        $response = $this->actingAs($user)->getJson('/world');

        $response->assertOk()
            ->assertJson([
                'vibe' => 'neutral',
                'level' => 1,
                'xp' => 0,
            ]);

        $items = collect($response->json('items'));

        $this->assertTrue((bool) $items->firstWhere('key', 'home_base')['unlocked']);
        $this->assertFalse((bool) $items->firstWhere('key', 'garden')['unlocked']);
    }

    public function test_post_world_vibe_updates_vibe(): void
    {
        $user = User::factory()->create();
        $this->createCoupleForUser($user);

        $this->actingAs($user)->postJson('/world/vibe', [
            'vibe' => 'warm',
        ])->assertOk()
            ->assertJson(['vibe' => 'warm']);

        $this->assertDatabaseHas('couple_world_states', [
            'couple_id' => $user->fresh()->current_couple_id,
            'vibe' => 'warm',
        ]);
    }

    public function test_post_world_unlock_unlocks_item_by_key(): void
    {
        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);

        $item = WorldItem::query()->create([
            'key' => 'cozy_corner',
            'title' => 'Cozy Corner',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($user)->postJson('/world/unlock', [
            'key' => 'cozy_corner',
        ])->assertOk()
            ->assertJson([
                'key' => 'cozy_corner',
                'unlocked' => true,
            ]);

        $this->assertDatabaseHas('couple_world_items', [
            'couple_id' => $couple->id,
            'world_item_id' => $item->id,
        ]);
    }

    public function test_user_not_in_current_couple_gets_forbidden(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $couple = $this->createCoupleForUser($owner);

        $intruder->forceFill(['current_couple_id' => $couple->id])->save();

        $this->actingAs($intruder)->getJson('/world')->assertForbidden();
    }

    public function test_user_with_no_current_couple_gets_conflict(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/world')
            ->assertStatus(409);
    }

    private function createCoupleForUser(User $user): Couple
    {
        $couple = Couple::query()->create([
            'name' => 'World Couple',
            'invite_code' => 'WORLD'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'created_by_user_id' => $user->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $user->forceFill(['current_couple_id' => $couple->id])->save();

        return $couple;
    }
}
