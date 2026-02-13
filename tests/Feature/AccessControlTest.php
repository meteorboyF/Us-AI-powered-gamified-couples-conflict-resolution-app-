<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\RepairSession;
use App\Models\User;
use App\Services\ChatService;
use App\Services\CoupleService;
use App\Services\MissionService;
use App\Services\RepairService;
use App\Services\VaultService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_send_or_read_chat_for_another_couple(): void
    {
        [$owner, $partner, $outsider, $targetCouple] = $this->makeTwoCouples();
        $chatService = app(ChatService::class);

        $chatService->sendMessage($targetCouple, $owner, 'hello partner');

        $this->expectException(AuthorizationException::class);
        $chatService->getMessages($targetCouple, $outsider);
    }

    public function test_user_cannot_react_to_memory_in_another_couple(): void
    {
        [$owner, $partner, $outsider, $targetCouple] = $this->makeTwoCouples();
        $vaultService = app(VaultService::class);

        $memory = Memory::create([
            'couple_id' => $targetCouple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'description' => 'private note',
            'visibility' => 'shared',
        ]);

        $this->expectException(AuthorizationException::class);
        $vaultService->addReaction($memory, $outsider, 'heart');
    }

    public function test_user_cannot_update_repair_session_for_another_couple(): void
    {
        [$owner, $partner, $outsider, $targetCouple] = $this->makeTwoCouples();
        $repairService = app(RepairService::class);

        $session = RepairSession::create([
            'couple_id' => $targetCouple->id,
            'initiated_by' => $owner->id,
            'status' => 'in_progress',
        ]);

        $this->expectException(AuthorizationException::class);
        $repairService->updatePerspective($session, $outsider, 'I should not edit this');
    }

    public function test_user_cannot_complete_mission_for_another_couple(): void
    {
        [$owner, $partner, $outsider, $targetCouple] = $this->makeTwoCouples();
        $missionService = app(MissionService::class);

        $mission = Mission::create([
            'title' => 'Test Mission',
            'description' => 'Desc',
            'type' => 'daily',
            'xp_reward' => 20,
            'is_active' => true,
        ]);

        $assignment = MissionAssignment::create([
            'couple_id' => $targetCouple->id,
            'mission_id' => $mission->id,
            'assigned_for_date' => today(),
            'status' => 'pending',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not part of this couple.');
        $missionService->completeMission($assignment, $outsider);
    }

    public function test_user_cannot_open_other_couples_memory_and_repair_routes(): void
    {
        [$owner, $partner, $outsider, $targetCouple] = $this->makeTwoCouples();

        $memory = Memory::create([
            'couple_id' => $targetCouple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'description' => 'memory',
            'visibility' => 'shared',
        ]);

        $session = RepairSession::create([
            'couple_id' => $targetCouple->id,
            'initiated_by' => $owner->id,
            'status' => 'pending',
        ]);

        $this->actingAs($outsider)
            ->get(route('vault.memory', ['memoryId' => $memory->id]))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->get(route('repair.wizard', ['sessionId' => $session->id]))
            ->assertForbidden();
    }

    protected function makeTwoCouples(): array
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $outsider = User::factory()->create();
        $outsiderPartner = User::factory()->create();

        $coupleService = app(CoupleService::class);
        $targetCouple = $coupleService->createCouple($owner);
        $coupleService->joinCouple($partner, $targetCouple->invite_code);

        $outsiderCouple = $coupleService->createCouple($outsider);
        $coupleService->joinCouple($outsiderPartner, $outsiderCouple->invite_code);

        return [$owner, $partner, $outsider, $targetCouple];
    }
}

