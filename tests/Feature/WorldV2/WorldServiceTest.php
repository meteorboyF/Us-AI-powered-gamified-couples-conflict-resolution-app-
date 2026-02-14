<?php

namespace Tests\Feature\WorldV2;

use App\Models\MoodCheckin;
use App\Models\RepairSession;
use App\Models\User;
use App\Models\XpEvent;
use App\Services\CoupleService;
use App\Services\WorldV2\WorldService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorldServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_world_state_bootstraps_starter_item_and_slots(): void
    {
        $owner = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($owner, ['theme' => 'garden']);

        $state = app(WorldService::class)->stateFor($couple->fresh(), $owner);

        $this->assertSame('garden', $state['world_type']);
        $this->assertNotEmpty($state['slots']);
        $this->assertArrayHasKey('garden_core_gazebo', $state['catalog']);

        $this->assertDatabaseHas('world_items', [
            'couple_id' => $couple->id,
            'item_key' => 'garden_core_gazebo',
            'level' => 1,
            'is_built' => true,
        ]);
    }

    public function test_non_member_cannot_buy_or_upgrade(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($owner, ['theme' => 'garden']);

        $this->expectException(AuthorizationException::class);

        app(WorldService::class)->buyOrUpgrade($couple->fresh(), $outsider, 'garden_decor_flowerbed');
    }

    public function test_buy_and_upgrade_deducts_love_seeds_and_honors_unlocks_and_max_level(): void
    {
        $owner = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($owner, ['theme' => 'garden']);
        $service = app(WorldService::class);

        $state = $service->stateFor($couple->fresh(), $owner);
        $balanceBefore = $state['wallet']->love_seeds_balance;

        $item = $service->buyOrUpgrade($couple->fresh(), $owner, 'garden_decor_flowerbed');

        $this->assertSame(1, $item->level);
        $this->assertDatabaseHas('couple_wallets', [
            'couple_id' => $couple->id,
            'love_seeds_balance' => $balanceBefore - 18,
        ]);

        $this->expectException(ValidationException::class);
        $service->buyOrUpgrade($couple->fresh(), $owner, 'garden_decor_fountain');
    }

    public function test_refresh_vibe_sets_quiet_or_bright_based_on_recent_metrics(): void
    {
        $owner = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($owner, ['theme' => 'garden']);
        $service = app(WorldService::class);

        $quietWorld = $service->refreshVibe($couple->fresh());
        $this->assertSame('quiet', $quietWorld?->ambience_state);

        MoodCheckin::create([
            'couple_id' => $couple->id,
            'user_id' => $owner->id,
            'date' => now()->toDateString(),
            'mood_level' => 5,
        ]);

        XpEvent::create([
            'couple_id' => $couple->id,
            'user_id' => $owner->id,
            'type' => 'mission',
            'xp_amount' => 240,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        RepairSession::create([
            'couple_id' => $couple->id,
            'initiated_by' => $owner->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $brightWorld = $service->refreshVibe($couple->fresh());

        $this->assertSame('bright', $brightWorld?->ambience_state);
        $this->assertIsArray($brightWorld?->cosmetics['__meta'] ?? null);
        $this->assertGreaterThanOrEqual(68, (int) ($brightWorld?->cosmetics['__meta']['vibe_score'] ?? 0));
    }
}
