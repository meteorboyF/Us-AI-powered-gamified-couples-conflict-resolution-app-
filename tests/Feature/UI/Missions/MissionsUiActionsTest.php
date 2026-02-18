<?php

namespace Tests\Feature\UI\Missions;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\CoupleMission;
use App\Models\MissionCompletion;
use App\Models\MissionTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionsUiActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_checkin_from_form_endpoint(): void
    {
        Carbon::setTestNow('2026-02-18');

        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);

        $this->actingAs($user)
            ->post('/checkins', [
                'mood' => 'good',
                'note' => 'UI checkin note',
            ])
            ->assertRedirect(route('missions.ui'));

        $this->assertDatabaseHas('daily_checkins', [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'mood' => 'good',
            'note' => 'UI checkin note',
        ]);
    }

    public function test_user_can_complete_mission_from_form_endpoint(): void
    {
        Carbon::setTestNow('2026-02-18');

        $user = User::factory()->create();
        $couple = $this->createCoupleForUser($user);

        $template = MissionTemplate::query()->create([
            'key' => 'daily_kind_word',
            'title' => 'Kind Word',
            'cadence' => 'daily',
            'is_active' => true,
        ]);

        $mission = CoupleMission::query()->create([
            'couple_id' => $couple->id,
            'mission_template_id' => $template->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post('/missions/complete', [
                'couple_mission_id' => $mission->id,
                'notes' => 'Completed via UI',
            ])
            ->assertRedirect(route('missions.ui'));

        $this->assertSame(
            1,
            MissionCompletion::query()
                ->where('couple_mission_id', $mission->id)
                ->whereDate('completed_on', Carbon::today()->toDateString())
                ->count()
        );
    }

    private function createCoupleForUser(User $user): Couple
    {
        $couple = Couple::query()->create([
            'name' => 'UI Actions Couple',
            'invite_code' => 'UIAC'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
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
