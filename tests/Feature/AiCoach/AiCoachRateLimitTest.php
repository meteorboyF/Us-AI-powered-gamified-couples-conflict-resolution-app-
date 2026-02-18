<?php

namespace Tests\Feature\AiCoach;

use App\Models\AiSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachRateLimitTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_message_endpoint_hits_rate_limit(): void
    {
        $this->forceFakeProvider();
        Config::set('us.ai.rate_limit_per_minute', 2);

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

        $payload = ['content' => 'Rate limit check'];

        $this->actingAs($user)->postJson('/ai/sessions/'.$session->id.'/user-message', $payload)->assertOk();
        $this->actingAs($user)->postJson('/ai/sessions/'.$session->id.'/user-message', $payload)->assertOk();
        $this->actingAs($user)->postJson('/ai/sessions/'.$session->id.'/user-message', $payload)->assertStatus(429);
    }
}
