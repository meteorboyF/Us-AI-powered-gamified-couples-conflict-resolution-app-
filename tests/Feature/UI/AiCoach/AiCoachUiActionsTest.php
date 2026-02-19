<?php

namespace Tests\Feature\UI\AiCoach;

use App\Models\AiSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachUiActionsTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_create_session_creates_row_and_redirects(): void
    {
        $this->forceFakeProvider();

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->post('/ai-coach/sessions', ['mode' => 'vent'])
            ->assertRedirect();

        $this->assertDatabaseHas('ai_sessions', [
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'mode' => 'vent',
            'status' => 'active',
        ]);
    }

    public function test_send_creates_user_and_ai_messages_and_redirects(): void
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
            ->post('/ai-coach/sessions/'.$session->id.'/send', [
                'content' => 'I feel ignored in arguments.',
            ])
            ->assertRedirect(route('ai.coach.page', ['session' => $session->id]));

        $this->assertDatabaseHas('ai_messages', [
            'ai_session_id' => $session->id,
            'sender_type' => 'user',
            'sender_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'ai_session_id' => $session->id,
            'sender_type' => 'ai',
            'sender_user_id' => null,
        ]);
    }
}
