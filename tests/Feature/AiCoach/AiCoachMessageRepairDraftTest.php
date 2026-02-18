<?php

namespace Tests\Feature\AiCoach;

use App\Models\AiSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachMessageRepairDraftTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_repair_mode_creates_structured_draft(): void
    {
        $this->forceFakeProvider();

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $session = AiSession::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'mode' => 'repair',
            'status' => 'active',
            'safety_flags' => [],
            'meta' => [],
        ]);

        $response = $this->actingAs($user)
            ->postJson('/ai/sessions/'.$session->id.'/user-message', [
                'content' => 'Help us make a repair plan.',
            ])
            ->assertOk()
            ->assertJsonPath('draft.draft_type', 'repair_plan');

        $this->assertStringContainsString('1) Pause', $response->json('ai_text'));
        $this->assertStringContainsString('Micro-actions', $response->json('ai_text'));
    }
}
