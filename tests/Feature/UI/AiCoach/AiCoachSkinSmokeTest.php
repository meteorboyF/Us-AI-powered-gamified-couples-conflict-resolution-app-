<?php

namespace Tests\Feature\UI\AiCoach;

use App\Models\AiSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachSkinSmokeTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_ai_coach_skin_renders_privacy_hint_and_send_form(): void
    {
        $this->forceFakeProvider();

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        AiSession::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'mode' => 'vent',
            'title' => 'Skin Test',
            'status' => 'active',
            'safety_flags' => [],
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->get('/ai-coach')
            ->assertOk()
            ->assertSee('Nothing is sent automatically')
            ->assertSee('Send');
    }
}
