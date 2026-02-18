<?php

namespace Tests\Feature\UI\Chat;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_current_couple_can_view_chat_page(): void
    {
        $user = User::factory()->create();
        $couple = Couple::query()->create([
            'name' => 'Chat UI Couple',
            'invite_code' => 'CHUI'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'created_by_user_id' => $user->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $user->forceFill(['current_couple_id' => $couple->id])->save();

        $this->actingAs($user)
            ->get('/chat')
            ->assertOk()
            ->assertSee('data-chat-realtime="1"', false)
            ->assertSee('Chat');
    }

    public function test_user_without_current_couple_sees_guidance(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/chat')
            ->assertOk()
            ->assertSee('No couple selected')
            ->assertSee('/couple');
    }
}
