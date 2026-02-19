<?php

namespace Tests\Feature\UI;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\UI\Concerns\CreatesAppHubContext;
use Tests\TestCase;

class AppHubSmokeTest extends TestCase
{
    use CreatesAppHubContext;
    use RefreshDatabase;

    public function test_authenticated_user_can_open_app_hub_and_see_module_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app')
            ->assertOk()
            ->assertSee('Couple')
            ->assertSee('World')
            ->assertSee('Missions')
            ->assertSee('Chat')
            ->assertSee('Vault')
            ->assertSee('AI Coach')
            ->assertSee('Gifts');
    }

    public function test_authenticated_user_with_current_couple_can_open_all_main_ui_pages(): void
    {
        $user = User::factory()->create();
        $this->createCoupleWithPartner($user);

        $paths = ['/couple', '/world-ui', '/missions-ui', '/chat', '/vault-ui', '/ai-coach', '/gifts-ui'];

        foreach ($paths as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertOk();
        }
    }

    public function test_authenticated_user_without_current_couple_sees_no_couple_guidance(): void
    {
        $user = User::factory()->create(['current_couple_id' => null]);

        $this->actingAs($user)
            ->get('/app')
            ->assertOk()
            ->assertSee('No couple selected');

        $paths = ['/world-ui', '/missions-ui', '/vault-ui', '/chat', '/ai-coach', '/gifts-ui'];

        foreach ($paths as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertOk()
                ->assertSee('No couple');
        }
    }
}
