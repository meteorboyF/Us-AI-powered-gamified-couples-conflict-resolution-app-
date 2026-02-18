<?php

namespace Tests\Feature\AiCoach;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachSessionTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_user_can_create_and_list_sessions(): void
    {
        $this->forceFakeProvider();

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        foreach (['vent', 'bridge', 'repair'] as $mode) {
            $this->actingAs($user)
                ->postJson('/ai/sessions', ['mode' => $mode, 'title' => strtoupper($mode)])
                ->assertCreated()
                ->assertJsonPath('mode', $mode);
        }

        $this->actingAs($user)
            ->getJson('/ai/sessions')
            ->assertOk()
            ->assertJsonCount(3, 'sessions');

        $this->assertDatabaseCount('ai_sessions', 3);
        $this->assertDatabaseHas('ai_sessions', [
            'couple_id' => $couple->id,
            'mode' => 'vent',
        ]);
    }
}
