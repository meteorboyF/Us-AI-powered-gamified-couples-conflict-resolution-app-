<?php

namespace Tests\Feature\AiCoachRealtime;

use App\Events\AiCoach\AiMessageCreated;
use App\Models\AiSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachBroadcastPayloadTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_message_event_payload_has_required_keys_and_excludes_sensitive_fields(): void
    {
        $this->forceFakeProvider();
        Event::fake([AiMessageCreated::class]);

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
                'content' => 'I want help saying this calmly.',
            ])
            ->assertOk();

        Event::assertDispatched(AiMessageCreated::class, function (AiMessageCreated $event) use ($couple, $session): bool {
            $payload = $event->broadcastWith();
            $message = $payload['message'] ?? [];

            if (($payload['couple_id'] ?? null) !== $couple->id) {
                return false;
            }

            if (($payload['session_id'] ?? null) !== $session->id) {
                return false;
            }

            if (
                ! array_key_exists('id', $message)
                || ! array_key_exists('sender_type', $message)
                || ! array_key_exists('content', $message)
                || ! array_key_exists('created_at', $message)
            ) {
                return false;
            }

            return ! array_key_exists('raw_provider_response', $message)
                && ! array_key_exists('tokens', $message)
                && ! array_key_exists('prompt', $message);
        });
    }
}
