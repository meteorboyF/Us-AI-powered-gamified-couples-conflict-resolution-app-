<?php

namespace Tests\Feature\UI\AiCoach;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AiCoach\Concerns\CreatesAiCoachContext;
use Tests\TestCase;

class AiCoachPageTest extends TestCase
{
    use CreatesAiCoachContext;
    use RefreshDatabase;

    public function test_user_with_current_couple_can_load_ai_coach_page(): void
    {
        $this->forceFakeProvider();

        $user = User::factory()->create();
        $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->get('/ai-coach')
            ->assertOk()
            ->assertSee('AI Coach');
    }

    public function test_user_with_no_current_couple_sees_no_couple_selected_state(): void
    {
        $this->forceFakeProvider();
        $user = User::factory()->create(['current_couple_id' => null]);

        $this->actingAs($user)
            ->get('/ai-coach')
            ->assertOk()
            ->assertSee('No couple selected');
    }
}
