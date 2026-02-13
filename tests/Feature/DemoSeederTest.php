<?php

namespace Tests\Feature;

use App\Models\Couple;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_expected_records(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'couplea1@demo.test']);
        $this->assertDatabaseHas('users', ['email' => 'couplea2@demo.test']);
        $this->assertDatabaseHas('users', ['email' => 'coupleb1@demo.test']);
        $this->assertDatabaseHas('users', ['email' => 'coupleb2@demo.test']);
        $this->assertSame(4, User::where('email', 'like', 'couple%@demo.test')->count());

        $this->assertSame(2, Couple::count());
        $this->assertSame(2, World::count());

        $coupleA = Couple::query()->where('invite_code', 'COUPLEA01')->firstOrFail();
        $coupleB = Couple::query()->where('invite_code', 'COUPLEB01')->firstOrFail();
        $this->assertSame('garden', $coupleA->world->world_type);
        $this->assertSame('space', $coupleB->world->world_type);
    }
}
