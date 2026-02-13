<?php

namespace Tests\Feature;

use App\Models\Couple;
use App\Models\User;
use App\Services\CoupleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoupleLinkingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_couple_with_world_and_membership(): void
    {
        $user = User::factory()->create();
        $service = app(CoupleService::class);

        $couple = $service->createCouple($user, ['theme' => 'house']);

        $this->assertSame('active', $couple->status);
        $this->assertNotNull($couple->invite_code);
        $this->assertSame(8, strlen($couple->invite_code));
        $this->assertDatabaseHas('couple_user', [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('worlds', [
            'couple_id' => $couple->id,
            'theme_type' => 'house',
            'level' => 1,
            'xp_total' => 0,
        ]);
    }

    public function test_user_cannot_create_second_active_couple(): void
    {
        $user = User::factory()->create();
        $service = app(CoupleService::class);

        $service->createCouple($user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You are already in an active couple.');

        $service->createCouple($user);
    }

    public function test_user_can_join_couple_by_invite_code(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $service = app(CoupleService::class);

        $couple = $service->createCouple($owner);
        $joined = $service->joinCouple($partner, strtolower($couple->invite_code));

        $this->assertSame($couple->id, $joined->id);
        $this->assertDatabaseHas('couple_user', [
            'couple_id' => $couple->id,
            'user_id' => $partner->id,
            'is_active' => true,
        ]);
    }

    public function test_couple_has_max_two_active_members(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $thirdUser = User::factory()->create();
        $service = app(CoupleService::class);

        $couple = $service->createCouple($owner);
        $service->joinCouple($partner, $couple->invite_code);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This couple already has 2 members.');

        $service->joinCouple($thirdUser, $couple->invite_code);
    }

    public function test_unlink_revokes_active_couple_access_for_members(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $service = app(CoupleService::class);

        $couple = $service->createCouple($owner);
        $service->joinCouple($partner, $couple->invite_code);

        $service->unlinkCouple($couple->fresh());

        $this->assertDatabaseHas('couples', [
            'id' => $couple->id,
            'status' => 'unlinked',
        ]);
        $this->assertDatabaseMissing('couple_user', [
            'couple_id' => $couple->id,
            'user_id' => $owner->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseMissing('couple_user', [
            'couple_id' => $couple->id,
            'user_id' => $partner->id,
            'is_active' => true,
        ]);
        $this->assertNull($service->getUserCouple($owner));
        $this->assertNull($service->getUserCouple($partner));
    }
}

