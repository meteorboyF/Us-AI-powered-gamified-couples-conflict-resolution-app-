<?php

namespace Tests\Feature\AiCoachRealtime;

use App\Events\AiCoach\AiDraftCreated;
use App\Events\AiCoach\AiMessageCreated;
use App\Events\AiCoach\AiSessionClosed;
use App\Models\AiSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachBroadcastDispatchTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_message_and_close_endpoints_dispatch_expected_ai_realtime_events(): void
    {
        $this->forceFakeProvider();
        Event::fake([AiMessageCreated::class, AiDraftCreated::class, AiSessionClosed::class]);

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
                'content' => 'Can you rewrite this gently?',
            ])
            ->assertOk();

        Event::assertDispatchedTimes(AiMessageCreated::class, 2);
        Event::assertDispatched(AiDraftCreated::class);

        $this->actingAs($user)
            ->postJson('/ai/sessions/'.$session->id.'/close')
            ->assertOk();

        Event::assertDispatched(AiSessionClosed::class);
    }
}
