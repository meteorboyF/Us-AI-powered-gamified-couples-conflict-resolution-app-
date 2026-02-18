<?php

namespace Tests\Feature\ChatV1;

use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ChatV1\Concerns\CreatesChatV1Context;
use Tests\TestCase;

class ChatDeleteMessageTest extends TestCase
{
    use CreatesChatV1Context;
    use RefreshDatabase;

    public function test_sender_can_soft_delete_message(): void
    {
        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $message = ChatMessage::query()->create([
            'chat_id' => $chat->id,
            'sender_id' => $ctx['user']->id,
            'type' => 'text',
            'body' => 'Delete me',
            'sent_at' => now(),
        ]);

        $this->actingAs($ctx['user'])
            ->deleteJson('/chat-v1/messages/'.$message->id)
            ->assertNoContent();

        $this->assertSoftDeleted('chat_messages', ['id' => $message->id]);
    }

    public function test_partner_cannot_delete_other_users_message(): void
    {
        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $message = ChatMessage::query()->create([
            'chat_id' => $chat->id,
            'sender_id' => $ctx['user']->id,
            'type' => 'text',
            'body' => 'Not yours',
            'sent_at' => now(),
        ]);

        $this->actingAs($ctx['partner'])
            ->deleteJson('/chat-v1/messages/'.$message->id)
            ->assertForbidden();
    }
}
