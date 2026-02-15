<?php

namespace Tests\Feature\Chat;

use App\Domain\Couples\CoupleContext;
use App\Livewire\Chat\ChatThread;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatThreadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_view_chat_thread(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $chat = Chat::factory()->create(['couple_id' => 101]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);
        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $partner->id,
            'joined_at' => now(),
        ]);

        ChatMessage::factory()->create([
            'chat_id' => $chat->id,
            'couple_id' => $chat->couple_id,
            'sender_id' => $partner->id,
            'body' => 'Hello from your partner',
            'sent_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)
            ->withSession(['current_couple_id' => 101])
            ->get('/chat')
            ->assertOk()
            ->assertSee('Hello from your partner');
    }

    public function test_unauthorized_user_cannot_view_chat_thread(): void
    {
        $participant = User::factory()->create();
        $partner = User::factory()->create();
        $outsider = User::factory()->create();
        $chat = Chat::factory()->create(['couple_id' => 202]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $participant->id,
            'joined_at' => now(),
        ]);
        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $partner->id,
            'joined_at' => now(),
        ]);

        $this->actingAs($outsider)
            ->withSession(['current_couple_id' => 202])
            ->get('/chat')
            ->assertForbidden();
    }

    public function test_sending_message_persists_and_appears_in_thread(): void
    {
        $sender = User::factory()->create();
        $partner = User::factory()->create();
        $chat = Chat::factory()->create(['couple_id' => 303]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $sender->id,
            'joined_at' => now(),
        ]);
        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $partner->id,
            'joined_at' => now(),
        ]);

        app()->instance(CoupleContext::class, new class extends CoupleContext
        {
            public function currentCoupleId(): ?int
            {
                return 303;
            }
        });

        Livewire::actingAs($sender)
            ->test(ChatThread::class)
            ->set('body', '  hi   there  ')
            ->call('sendMessage')
            ->assertSee('hi there');

        $this->assertDatabaseHas('chat_messages', [
            'chat_id' => $chat->id,
            'couple_id' => 303,
            'sender_id' => $sender->id,
            'body' => 'hi there',
        ]);
    }

    public function test_read_receipt_is_set_when_recipient_loads_thread(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $chat = Chat::factory()->create(['couple_id' => 404]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $sender->id,
            'joined_at' => now(),
        ]);
        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $recipient->id,
            'joined_at' => now(),
        ]);

        $message = ChatMessage::factory()->create([
            'chat_id' => $chat->id,
            'couple_id' => 404,
            'sender_id' => $sender->id,
            'body' => 'Unread message',
            'sent_at' => now()->subMinutes(3),
            'read_at' => null,
        ]);

        $this->actingAs($recipient)
            ->withSession(['current_couple_id' => 404])
            ->get('/chat')
            ->assertOk();

        $message->refresh();

        $this->assertNotNull($message->read_at);
    }

    public function test_chat_route_returns_404_when_feature_is_disabled(): void
    {
        config()->set('us.features.chat_v1', false);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['current_couple_id' => 1])
            ->get('/chat')
            ->assertNotFound();
    }
}
