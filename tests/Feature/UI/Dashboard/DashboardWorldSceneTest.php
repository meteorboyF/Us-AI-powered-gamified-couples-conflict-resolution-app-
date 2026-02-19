<?php

namespace Tests\Feature\UI\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\UI\Concerns\CreatesAppHubContext;
use Tests\TestCase;

class DashboardWorldSceneTest extends TestCase
{
    use CreatesAppHubContext;
    use RefreshDatabase;

    public function test_dashboard_ui_renders_world_scene_for_user_with_current_couple(): void
    {
        $user = User::factory()->create();
        $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->get('/dashboard-ui')
            ->assertOk()
            ->assertSee('Home Base')
            ->assertSee('Vibe:');
    }
}
