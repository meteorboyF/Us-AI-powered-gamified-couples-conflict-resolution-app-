<?php

namespace Tests\Feature;

use App\Models\Mission;
use App\Models\User;
use App\Services\CoupleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_for_user_in_couple(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['name' => 'Alex']);
        $partner = User::factory()->withPersonalTeam()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        Mission::create([
            'title' => 'Test Daily',
            'description' => 'Do something kind',
            'type' => 'daily',
            'xp_reward' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hi, Alex');
    }
}
