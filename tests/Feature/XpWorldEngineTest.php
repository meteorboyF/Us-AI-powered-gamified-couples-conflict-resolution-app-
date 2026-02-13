<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CoupleService;
use App\Services\XpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XpWorldEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_xp_creates_event_and_increments_world_total(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);

        app(XpService::class)->awardXp($couple, 'checkin', $user);

        $this->assertDatabaseHas('xp_events', [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'type' => 'checkin',
            'xp_amount' => 10,
        ]);
        $this->assertDatabaseHas('worlds', [
            'couple_id' => $couple->id,
            'xp_total' => 10,
            'level' => 1,
        ]);
    }

    public function test_world_ambience_updates_at_thresholds(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $xp = app(XpService::class);

        $xp->awardXp($couple, 'mission', $user, 200);
        $world = $couple->world()->first();
        $this->assertSame('calm', $world->ambience_state);

        $xp->awardXp($couple, 'mission', $user, 300);
        $world = $couple->world()->first();
        $this->assertSame('quiet', $world->ambience_state);
    }

    public function test_world_cosmetics_unlock_when_leveling_up(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $xp = app(XpService::class);

        $xp->awardXp($couple, 'mission', $user, 200); // level 3
        $world = $couple->world()->first();
        $this->assertContains('blooming_garden', $world->cosmetics ?? []);

        $xp->awardXp($couple, 'mission', $user, 200); // level 5
        $world = $couple->world()->first();
        $this->assertContains('starlit_sky', $world->cosmetics ?? []);
    }
}

