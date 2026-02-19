<?php

namespace Tests\Feature\AiCoach;

use App\Models\AiSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachMessageVentTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_vent_message_creates_user_and_ai_messages(): void
    {
        $this->forceFakeProvider();

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $session = AiSession::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'mode' => 'vent',
            'status' => 'active',
            'safety_flags' => [],
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->postJson('/ai/sessions/'.$session->id.'/user-message', [
                'content' => 'I need to vent about our argument.',
            ])
            ->assertOk()
            ->assertJsonPath('ai_text', 'Vent Reflection:
Summary: You are feeling intense pressure and want to be understood.
Questions: What happened right before this? What do you need right now? What can help you feel 10% calmer?
Grounding: Take 5 slow breaths and relax your shoulders.');

        $this->assertDatabaseHas('ai_messages', [
            'ai_session_id' => $session->id,
            'sender_type' => 'user',
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'ai_session_id' => $session->id,
            'sender_type' => 'ai',
        ]);
    }
}
