<?php

namespace Tests\Feature\ChatV1;

use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ChatV1\Concerns\CreatesChatV1Context;
use Tests\TestCase;

class ChatSendMessageTest extends TestCase
{
    use CreatesChatV1Context;
    use RefreshDatabase;

    public function test_send_text_message_persists_and_returns_created(): void
    {
        $ctx = $this->createCouplePair();

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/messages', [
                'type' => 'text',
                'body' => 'Hello from chat v1',
            ])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Hello from chat v1');

        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $ctx['user']->id,
            'type' => 'text',
            'body' => 'Hello from chat v1',
        ]);
    }

    public function test_send_too_long_body_returns_unprocessable(): void
    {
        $ctx = $this->createCouplePair();
        $body = str_repeat('x', 2001);

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/messages', [
                'type' => 'text',
                'body' => $body,
            ])
            ->assertStatus(422);
    }

    public function test_send_is_throttled(): void
    {
        config(['us.chat.rate_limit_per_minute' => 1]);

        $ctx = $this->createCouplePair();

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/messages', [
                'type' => 'text',
                'body' => 'First',
            ])
            ->assertCreated();

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/messages', [
                'type' => 'text',
                'body' => 'Second',
            ])
            ->assertStatus(429);
    }

    public function test_feature_disabled_returns_not_found(): void
    {
        config(['us.features.chat_v1' => false]);

        $ctx = $this->createCouplePair();

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/messages', [
                'type' => 'text',
                'body' => 'Blocked',
            ])
            ->assertNotFound();

        $this->assertSame(0, ChatMessage::query()->count());
    }
}
