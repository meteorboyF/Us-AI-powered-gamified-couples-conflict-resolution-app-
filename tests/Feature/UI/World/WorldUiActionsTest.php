<?php

namespace Tests\Feature\UI\World;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorldUiActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_vibe_from_ui_flow(): void
    {
        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);

        $this->actingAs($user)->postJson('/world/vibe', [
            'vibe' => 'playful',
        ])->assertOk();

        $this->assertDatabaseHas('couple_world_states', [
            'couple_id' => $couple->id,
            'vibe' => 'playful',
        ]);
    }

    private function createCoupleForUser(User $user): Couple
    {
        $couple = Couple::query()->create([
            'name' => 'UI World Couple',
            'invite_code' => 'UIACTN01',
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
