<?php

namespace Tests\Feature\UI\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\UI\Concerns\CreatesAppHubContext;
use Tests\TestCase;

class DashboardUiTest extends TestCase
{
    use CreatesAppHubContext;
    use RefreshDatabase;

    public function test_authenticated_user_with_current_couple_can_open_dashboard_ui(): void
    {
        $user = User::factory()->create();
        $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->get('/dashboard-ui')
            ->assertOk()
            ->assertSee('Tap to plant Love Seeds');
    }

    public function test_authenticated_user_without_current_couple_sees_no_couple_selected_message(): void
    {
        $user = User::factory()->create(['current_couple_id' => null]);

        $this->actingAs($user)
            ->get('/dashboard-ui')
            ->assertOk()
            ->assertSee('No couple selected')
            ->assertSee('/couple');
    }
}
