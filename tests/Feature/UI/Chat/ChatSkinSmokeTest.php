<?php

namespace Tests\Feature\UI\Chat;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatSkinSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_page_contains_skin_placeholder(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/chat')
            ->assertOk()
            ->assertSee('No couple selected');
    }

    public function test_chat_page_with_couple_shows_composer_placeholder(): void
    {
        $user = User::factory()->create();
        $couple = \App\Models\Couple::query()->create([
            'name' => 'Chat Skin Couple',
            'invite_code' => 'CSKN'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'created_by_user_id' => $user->id,
        ]);

        \App\Models\CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $user->forceFill(['current_couple_id' => $couple->id])->save();

        $this->actingAs($user)
            ->get('/chat')
            ->assertOk()
            ->assertSee('Type a message...')
            ->assertSee('Cozy Chat');
    }
}
