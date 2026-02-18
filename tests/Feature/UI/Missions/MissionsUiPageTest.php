<?php

namespace Tests\Feature\UI\Missions;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\CoupleMission;
use App\Models\MissionTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionsUiPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_missions_ui_and_see_assigned_mission(): void
    {
        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);

        $template = MissionTemplate::query()->create([
            'key' => 'daily_gratitude',
            'title' => 'Daily Gratitude',
            'cadence' => 'daily',
            'is_active' => true,
        ]);

        CoupleMission::query()->create([
            'couple_id' => $couple->id,
            'mission_template_id' => $template->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/missions-ui')
            ->assertOk()
            ->assertSee('Daily Gratitude')
            ->assertSee('Today Check-in');
    }

    private function createCoupleForUser(User $user): Couple
    {
        $couple = Couple::query()->create([
            'name' => 'UI Missions Couple',
            'invite_code' => 'UIMS'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
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
