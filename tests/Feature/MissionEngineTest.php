<?php

namespace Tests\Feature;

use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\User;
use App\Services\CoupleService;
use App\Services\MissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_assignments_are_created_and_not_duplicated(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $service = app(MissionService::class);

        foreach (range(1, 4) as $i) {
            Mission::create([
                'title' => "Daily {$i}",
                'description' => "Daily mission {$i}",
                'type' => 'daily',
                'xp_reward' => 20,
                'is_active' => true,
            ]);
        }

        $service->assignDailyMissions($couple, 3);
        $service->assignDailyMissions($couple, 3);

        $this->assertDatabaseCount('mission_assignments', 3);
    }

    public function test_weekly_assignments_are_created_once_per_week(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $service = app(MissionService::class);

        foreach (range(1, 4) as $i) {
            Mission::create([
                'title' => "Weekly {$i}",
                'description' => "Weekly mission {$i}",
                'type' => 'weekly',
                'xp_reward' => 40,
                'is_active' => true,
            ]);
        }

        $service->assignWeeklyMissions($couple, 2);
        $service->assignWeeklyMissions($couple, 2);

        $this->assertSame(2, MissionAssignment::where('couple_id', $couple->id)->count());
    }

    public function test_mission_completion_marks_completed_and_grants_xp_once(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $mission = Mission::create([
            'title' => 'Daily A',
            'description' => 'Desc',
            'type' => 'daily',
            'xp_reward' => 25,
            'is_active' => true,
        ]);

        $assignment = MissionAssignment::create([
            'couple_id' => $couple->id,
            'mission_id' => $mission->id,
            'assigned_for_date' => today(),
            'status' => 'pending',
        ]);

        $service = app(MissionService::class);
        $service->completeMission($assignment, $user);
        $service->completeMission($assignment->fresh(), $user);

        $this->assertDatabaseHas('mission_assignments', [
            'id' => $assignment->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseCount('mission_completions', 1);
        $this->assertDatabaseHas('xp_events', [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'type' => 'mission',
            'xp_amount' => 25,
        ]);
    }

    public function test_partner_can_acknowledge_but_owner_cannot_acknowledge_own_completion(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $mission = Mission::create([
            'title' => 'Daily B',
            'description' => 'Desc',
            'type' => 'daily',
            'xp_reward' => 20,
            'is_active' => true,
        ]);

        $assignment = MissionAssignment::create([
            'couple_id' => $couple->id,
            'mission_id' => $mission->id,
            'assigned_for_date' => today(),
            'status' => 'pending',
        ]);

        $service = app(MissionService::class);
        $completion = $service->completeMission($assignment, $user);

        $service->acknowledgeMission($completion, $partner);
        $this->assertNotNull($completion->fresh()->partner_acknowledged_at);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot acknowledge your own completion.');
        $service->acknowledgeMission($completion->fresh(), $user);
    }
}
