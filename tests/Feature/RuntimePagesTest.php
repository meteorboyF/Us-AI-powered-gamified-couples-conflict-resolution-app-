<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use App\Services\CoupleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RuntimePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_missions_route_returns_ok_for_authenticated_couple_member(): void
    {
        $user = User::factory()->create();
        app(CoupleService::class)->createCouple($user);

        $this->actingAs($user)
            ->get(route('missions.board'))
            ->assertOk()
            ->assertSeeText("Today's Missions", false);
    }

    public function test_repair_history_route_returns_ok_for_authenticated_couple_member(): void
    {
        $user = User::factory()->create();
        app(CoupleService::class)->createCouple($user);

        $this->actingAs($user)
            ->get(route('repair.history'))
            ->assertOk()
            ->assertSee('Repair History');
    }

    public function test_vault_gallery_and_memory_view_render_with_missing_media_fallback(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);

        $memory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $user->id,
            'type' => 'photo',
            'title' => 'Broken file',
            'file_path' => 'memories/'.$couple->id.'/photo/missing-file.jpg',
            'visibility' => 'shared',
        ]);

        $this->actingAs($user)
            ->get(route('vault.gallery'))
            ->assertOk()
            ->assertSee('Memory unavailable')
            ->assertDontSee('src=""', false);

        $this->actingAs($user)
            ->get(route('vault.memory', ['memoryId' => $memory->id]))
            ->assertOk()
            ->assertSee('Memory file is unavailable.');
    }
}
