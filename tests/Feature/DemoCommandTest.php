<?php

namespace Tests\Feature;

use App\Models\Couple;
use App\Models\Message;
use App\Models\MissionAssignment;
use App\Models\MoodCheckin;
use App\Models\User;
use App\Models\World;
use App\Models\XpEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_us_seed_demo_command_creates_two_couples_with_key_history(): void
    {
        $this->artisan('us:seed-demo')
            ->expectsOutputToContain('Demo data seeded successfully.')
            ->expectsOutputToContain('couplea1@demo.test')
            ->expectsOutputToContain('coupleb2@demo.test')
            ->assertSuccessful();

        $this->artisan('us:seed-demo')->assertSuccessful();

        $this->assertSame(4, User::where('email', 'like', 'couple%@demo.test')->count());
        $this->assertSame(2, Couple::count());
        $this->assertSame(2, World::count());
        $this->assertSame(4, DB::table('couple_user')->count());

        $this->assertGreaterThan(0, XpEvent::count());
        $this->assertGreaterThan(0, MoodCheckin::count());
        $this->assertGreaterThan(0, MissionAssignment::count());
        $this->assertGreaterThan(0, Message::count());
        $this->assertGreaterThan(0, DB::table('repair_sessions')->count());
        $this->assertGreaterThan(0, DB::table('memories')->count());
    }
}
