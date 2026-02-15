<?php

namespace Tests\Feature\Dev;

use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetCoupleSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->detectEnvironment(fn () => 'local');
    }

    public function test_guest_cannot_access_route(): void
    {
        $this->getJson('/_dev/set-couple/1')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_set_allowed_couple_session(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['couple_id' => 7]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson('/_dev/set-couple/7')
            ->assertOk()
            ->assertExactJson([
                'current_couple_id' => 7,
            ]);

        $this->assertSame(7, session('current_couple_id'));
    }

    public function test_authenticated_user_cannot_set_disallowed_couple_session(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create(['couple_id' => 9]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $otherUser->id,
            'joined_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson('/_dev/set-couple/9')
            ->assertForbidden();
    }
}
