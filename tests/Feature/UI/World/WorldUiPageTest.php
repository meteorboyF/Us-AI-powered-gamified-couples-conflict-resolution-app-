<?php

namespace Tests\Feature\UI\World;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use App\Models\WorldItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorldUiPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_world_ui_page_renders_for_authenticated_user_with_current_couple(): void
    {
        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);

        $home = WorldItem::query()->create([
            'key' => 'home_base',
            'title' => 'Home Base',
            'description' => 'Your shared anchor.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $couple->worldItems()->syncWithoutDetaching([
            $home->id => ['unlocked_at' => now()],
        ]);

        $this->actingAs($user)
            ->get('/world-ui')
            ->assertOk()
            ->assertSee('World V1')
            ->assertSee('Home Base');
    }

    public function test_world_ui_shows_no_couple_message_when_context_missing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/world-ui')
            ->assertOk()
            ->assertSee('No couple selected');
    }

    private function createCoupleForUser(User $user): Couple
    {
        $couple = Couple::query()->create([
            'name' => 'UI World Couple',
            'invite_code' => 'UIWRLD01',
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
