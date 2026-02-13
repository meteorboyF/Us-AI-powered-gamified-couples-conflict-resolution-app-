<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionCompletion;
use App\Models\User;
use App\Services\CoupleService;
use App\Services\WorldBuildingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorldBuildingTest extends TestCase
{
    use RefreshDatabase;

    public function test_couple_member_can_view_and_update_world_and_non_member_cannot(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $outsider = User::factory()->create();
        $outsiderPartner = User::factory()->create();
        $coupleService = app(CoupleService::class);

        $couple = $coupleService->createCouple($owner, ['theme' => 'garden']);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $outsiderCouple = $coupleService->createCouple($outsider);
        $coupleService->joinCouple($outsiderPartner, $outsiderCouple->invite_code);

        $worldBuilding = app(WorldBuildingService::class);

        $state = $worldBuilding->getWorldState($couple->fresh(), $partner);
        $this->assertSame($couple->id, $state['world']->couple_id);

        $worldBuilding->purchaseOrUpgradeItem($couple->fresh(), $partner, 'garden_lantern_path');
        $this->assertDatabaseHas('world_items', [
            'couple_id' => $couple->id,
            'item_key' => 'garden_lantern_path',
            'level' => 1,
            'is_built' => true,
        ]);

        $this->expectException(AuthorizationException::class);
        $worldBuilding->getWorldState($couple->fresh(), $outsider);
    }

    public function test_non_member_cannot_purchase_or_upgrade_world_item(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $outsiderPartner = User::factory()->create();
        $coupleService = app(CoupleService::class);

        $couple = $coupleService->createCouple($owner, ['theme' => 'garden']);
        $outsiderCouple = $coupleService->createCouple($outsider);
        $coupleService->joinCouple($outsiderPartner, $outsiderCouple->invite_code);

        $this->expectException(AuthorizationException::class);
        app(WorldBuildingService::class)->purchaseOrUpgradeItem($couple->fresh(), $outsider, 'garden_lantern_path');
    }

    public function test_buying_and_upgrading_item_deducts_currency_and_respects_unlocks_and_max_levels(): void
    {
        $owner = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($owner, ['theme' => 'garden']);
        $worldBuilding = app(WorldBuildingService::class);

        $walletBefore = $couple->wallet()->firstOrFail()->love_seeds_balance;
        $item = $worldBuilding->purchaseOrUpgradeItem($couple->fresh(), $owner, 'garden_lantern_path');
        $this->assertSame(1, $item->level);

        $walletAfterFirstUpgrade = $couple->wallet()->firstOrFail()->love_seeds_balance;
        $this->assertSame($walletBefore - 20, $walletAfterFirstUpgrade);

        $this->expectException(ValidationException::class);
        $worldBuilding->purchaseOrUpgradeItem($couple->fresh(), $owner, 'garden_sun_dial');
    }

    public function test_upgrade_unlocks_after_mission_completion_and_blocks_beyond_max_level(): void
    {
        $owner = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($owner, ['theme' => 'garden']);
        $worldBuilding = app(WorldBuildingService::class);

        $mission = Mission::create([
            'title' => 'Unlock Mission',
            'description' => 'Complete once to unlock an item.',
            'type' => 'daily',
            'xp_reward' => 10,
            'category' => 'connection',
            'is_active' => true,
        ]);

        $assignment = MissionAssignment::create([
            'couple_id' => $couple->id,
            'mission_id' => $mission->id,
            'assigned_for_date' => today(),
            'status' => 'completed',
        ]);

        MissionCompletion::create([
            'mission_assignment_id' => $assignment->id,
            'user_id' => $owner->id,
            'completed_at' => now(),
        ]);

        $sunDial = $worldBuilding->purchaseOrUpgradeItem($couple->fresh(), $owner, 'garden_sun_dial');
        $this->assertSame(1, $sunDial->level);

        $worldBuilding->purchaseOrUpgradeItem($couple->fresh(), $owner, 'garden_lantern_path');
        $worldBuilding->purchaseOrUpgradeItem($couple->fresh(), $owner, 'garden_lantern_path');

        $this->expectException(ValidationException::class);
        $worldBuilding->purchaseOrUpgradeItem($couple->fresh(), $owner, 'garden_lantern_path');
    }

    public function test_placement_updates_persist_for_built_items(): void
    {
        $owner = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($owner, ['theme' => 'garden']);
        $worldBuilding = app(WorldBuildingService::class);

        $worldBuilding->purchaseOrUpgradeItem($couple->fresh(), $owner, 'garden_picnic_set');
        $updated = $worldBuilding->placeItem(
            $couple->fresh(),
            $owner,
            'garden_picnic_set',
            'north_2',
            ['x' => 3, 'y' => 5]
        );

        $this->assertSame('north_2', $updated->slot);
        $this->assertSame(['x' => 3, 'y' => 5], $updated->position);
        $this->assertDatabaseHas('world_items', [
            'couple_id' => $couple->id,
            'item_key' => 'garden_picnic_set',
            'slot' => 'north_2',
        ]);
    }

    public function test_memory_frame_uses_only_shared_comfort_safe_items_and_respects_locks(): void
    {
        $owner = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($owner, ['theme' => 'house']);
        $worldBuilding = app(WorldBuildingService::class);

        Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'title' => 'Shared comfort memory',
            'visibility' => 'shared',
            'comfort' => true,
        ]);

        Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'title' => 'Private memory',
            'visibility' => 'private',
            'comfort' => true,
        ]);

        Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'title' => 'Locked memory',
            'visibility' => 'locked',
            'comfort' => true,
        ]);

        Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'title' => 'Shared but not comfort',
            'visibility' => 'shared',
            'comfort' => false,
        ]);

        $highlight = $worldBuilding->getMemoryFrameHighlight($couple->fresh(), $owner);

        $this->assertNotNull($highlight);
        $this->assertSame('Shared comfort memory', $highlight['title']);
    }
}
