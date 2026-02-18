<?php

namespace Tests\Feature\AiCoach;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachAuthzTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_no_current_couple_returns_conflict(): void
    {
        $this->forceFakeProvider();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/ai/sessions')
            ->assertStatus(409);
    }

    public function test_non_member_current_couple_returns_forbidden(): void
    {
        $this->forceFakeProvider();

        $owner = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($owner);

        $intruder = User::factory()->create();
        $intruder->forceFill(['current_couple_id' => $couple->id])->save();

        $this->actingAs($intruder)
            ->getJson('/ai/sessions')
            ->assertForbidden();
    }
}
