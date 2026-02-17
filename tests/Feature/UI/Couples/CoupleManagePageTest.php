<?php

namespace Tests\Feature\UI\Couples;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoupleManagePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_couple_manage_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/couple')
            ->assertOk()
            ->assertSee('Couple Linking');
    }

    public function test_user_can_submit_create_form_and_is_redirected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/couples', [
            'name' => 'UI Couple',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('couples', [
            'name' => 'UI Couple',
            'created_by_user_id' => $user->id,
        ]);
    }

    public function test_user_can_submit_join_form_and_is_redirected(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();

        $couple = Couple::query()->create([
            'name' => 'Joinable Couple',
            'invite_code' => 'JOINUI01',
            'created_by_user_id' => $owner->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($joiner)->post('/couples/join', [
            'invite_code' => 'joinui01',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('couple_members', [
            'couple_id' => $couple->id,
            'user_id' => $joiner->id,
            'role' => 'member',
        ]);
    }
}
