<?php

namespace Tests\Feature;

use App\Models\AiChat;
use App\Models\Couple;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_expected_records(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'demo.alex@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'demo.sam@example.com']);

        $couple = Couple::query()->where('invite_code', 'DEMOLOVE')->first();
        $this->assertNotNull($couple);

        $alex = User::query()->where('email', 'demo.alex@example.com')->firstOrFail();

        $this->assertTrue($couple->world()->exists());
        $this->assertTrue(Memory::query()->where('couple_id', $couple->id)->exists());
        $this->assertTrue(AiChat::query()->where('couple_id', $couple->id)->exists());
        $this->assertTrue($couple->users()->where('users.id', $alex->id)->exists());
    }
}
