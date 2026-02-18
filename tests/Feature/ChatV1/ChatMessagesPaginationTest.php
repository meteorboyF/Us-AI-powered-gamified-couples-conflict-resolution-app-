<?php

namespace Tests\Feature\ChatV1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ChatV1\Concerns\CreatesChatV1Context;
use Tests\TestCase;

class ChatMessagesPaginationTest extends TestCase
{
    use CreatesChatV1Context;
    use RefreshDatabase;

    public function test_first_page_returns_newest_first_and_respects_limit(): void
    {
        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $messages = $this->seedMessages($chat, $ctx['user'], $ctx['partner'], 60);

        $response = $this->actingAs($ctx['user'])
            ->getJson('/chat-v1/messages?limit=30')
            ->assertOk()
            ->json();

        $this->assertCount(30, $response['messages']);
        $this->assertSame($messages->last()->id, $response['messages'][0]['id']);
        $this->assertSame($messages->get(30)->id, $response['messages'][29]['id']);
        $this->assertSame($messages->get(30)->id, $response['next_before_id']);
    }

    public function test_before_id_fetches_older_messages(): void
    {
        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $messages = $this->seedMessages($chat, $ctx['user'], $ctx['partner'], 40);

        $beforeId = $messages->last()->id - 10;

        $response = $this->actingAs($ctx['user'])
            ->getJson('/chat-v1/messages?before_id='.$beforeId.'&limit=5')
            ->assertOk()
            ->json();

        $this->assertCount(5, $response['messages']);
        $this->assertTrue(collect($response['messages'])->every(fn ($m) => $m['id'] < $beforeId));
    }

    public function test_max_limit_is_enforced(): void
    {
        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $this->seedMessages($chat, $ctx['user'], $ctx['partner'], 120);

        $response = $this->actingAs($ctx['user'])
            ->getJson('/chat-v1/messages?limit=500')
            ->assertOk()
            ->json();

        $this->assertCount(100, $response['messages']);
    }
}
