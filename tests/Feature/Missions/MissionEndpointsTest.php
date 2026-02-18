<?php

namespace Tests\Feature\Missions;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\CoupleMission;
use App\Models\MissionTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_missions_returns_assigned_missions_for_current_couple(): void
    {
        Carbon::setTestNow('2026-02-18');

        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);
        $template = $this->createTemplate('daily_gratitude', 'Daily Gratitude');

        $mission = CoupleMission::query()->create([
            'couple_id' => $couple->id,
            'mission_template_id' => $template->id,
            'status' => 'active',
            'started_at' => Carbon::today(),
        ]);

        $mission->completions()->create([
            'completed_on' => Carbon::today()->toDateString(),
            'completed_by_user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->getJson('/missions')
            ->assertOk()
            ->assertJsonPath('missions.0.template.key', 'daily_gratitude')
            ->assertJsonPath('missions.0.today_completed', true);
    }

    public function test_post_missions_assign_is_idempotent_for_same_template(): void
    {
        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);
        $this->createTemplate('weekly_date_planning', 'Date Planning');

        $payload = ['key' => 'weekly_date_planning'];

        $this->actingAs($user)->postJson('/missions/assign', $payload)->assertOk();
        $this->actingAs($user)->postJson('/missions/assign', $payload)->assertOk();

        $this->assertDatabaseCount('couple_missions', 1);
        $this->assertDatabaseHas('couple_missions', [
            'couple_id' => $couple->id,
            'status' => 'active',
        ]);
    }

    public function test_post_missions_complete_is_idempotent_for_today(): void
    {
        Carbon::setTestNow('2026-02-18');

        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);
        $template = $this->createTemplate('repair_conversation', 'Repair Conversation');

        $mission = CoupleMission::query()->create([
            'couple_id' => $couple->id,
            'mission_template_id' => $template->id,
            'status' => 'active',
            'started_at' => Carbon::today(),
        ]);

        $payload = ['couple_mission_id' => $mission->id, 'notes' => 'Done today'];

        $this->actingAs($user)->postJson('/missions/complete', $payload)->assertOk();
        $this->actingAs($user)->postJson('/missions/complete', $payload)->assertOk();

        $this->assertDatabaseCount('mission_completions', 1);
        $this->assertSame(
            1,
            \App\Models\MissionCompletion::query()
                ->where('couple_mission_id', $mission->id)
                ->whereDate('completed_on', Carbon::today()->toDateString())
                ->count()
        );
    }

    public function test_user_not_in_current_couple_gets_forbidden_on_missions(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $couple = $this->createCoupleForUser($owner);

        $intruder->forceFill(['current_couple_id' => $couple->id])->save();

        $this->actingAs($intruder)
            ->getJson('/missions')
            ->assertForbidden();
    }

    public function test_user_with_no_current_couple_gets_conflict_on_missions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/missions')
            ->assertStatus(409);
    }

    private function createCoupleForUser(User $user): Couple
    {
        $couple = Couple::query()->create([
            'name' => 'Mission Couple',
            'invite_code' => 'MISN'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
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

    private function createTemplate(string $key, string $title): MissionTemplate
    {
        return MissionTemplate::query()->create([
            'key' => $key,
            'title' => $title,
            'cadence' => 'daily',
            'is_active' => true,
        ]);
    }
}
