<?php

namespace Tests\Feature\AiCoach;

use App\Models\AiSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachMessageBridgeDraftTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_bridge_mode_creates_draft_but_not_auto_accepted(): void
    {
        $this->forceFakeProvider();

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $session = AiSession::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'mode' => 'bridge',
            'status' => 'active',
            'safety_flags' => [],
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->postJson('/ai/sessions/'.$session->id.'/user-message', [
                'content' => 'Rewrite this in a respectful way.',
            ])
            ->assertOk()
            ->assertJsonPath('draft.status', 'draft')
            ->assertJsonPath('draft.draft_type', 'bridge_message');

        $this->assertDatabaseHas('ai_drafts', [
            'ai_session_id' => $session->id,
            'draft_type' => 'bridge_message',
            'status' => 'draft',
        ]);
    }
}
