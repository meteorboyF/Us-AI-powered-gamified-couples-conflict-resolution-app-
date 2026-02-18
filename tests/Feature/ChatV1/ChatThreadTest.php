<?php

namespace Tests\Feature\ChatV1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ChatV1\Concerns\CreatesChatV1Context;
use Tests\TestCase;

class ChatThreadTest extends TestCase
{
    use CreatesChatV1Context;
    use RefreshDatabase;

    public function test_authenticated_member_can_get_chat_thread(): void
    {
        $ctx = $this->createCouplePair();
        $user = $ctx['user'];
        $couple = $ctx['couple'];

        $this->actingAs($user)
            ->getJson('/chat-v1')
            ->assertOk()
            ->assertJsonPath('couple_id', $couple->id)
            ->assertJsonStructure(['chat_id', 'participants']);
    }

    public function test_non_member_gets_forbidden(): void
    {
        $ctx = $this->createCouplePair();
        $intruder = User::factory()->create();
        $intruder->forceFill(['current_couple_id' => $ctx['couple']->id])->save();

        $this->actingAs($intruder)
            ->getJson('/chat-v1')
            ->assertForbidden();
    }

    public function test_no_current_couple_gets_conflict(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/chat-v1')
            ->assertStatus(409);
    }
}
