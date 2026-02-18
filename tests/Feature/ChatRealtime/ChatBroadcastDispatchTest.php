<?php

namespace Tests\Feature\ChatRealtime;

use App\Events\Chat\MessageDeleted;
use App\Events\Chat\MessageSent;
use App\Events\Chat\ReadReceiptUpdated;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Feature\ChatV1\Concerns\CreatesChatV1Context;
use Tests\TestCase;

class ChatBroadcastDispatchTest extends TestCase
{
    use CreatesChatV1Context;
    use RefreshDatabase;

    public function test_send_dispatches_message_sent_event(): void
    {
        Event::fake([MessageSent::class]);

        $ctx = $this->createCouplePair();

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/messages', [
                'type' => 'text',
                'body' => 'Realtime send',
            ])
            ->assertCreated();

        Event::assertDispatched(MessageSent::class);
    }

    public function test_delete_dispatches_message_deleted_event(): void
    {
        Event::fake([MessageDeleted::class]);

        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $message = ChatMessage::query()->create([
            'chat_id' => $chat->id,
            'sender_id' => $ctx['user']->id,
            'type' => 'text',
            'body' => 'Delete event',
            'sent_at' => now(),
        ]);

        $this->actingAs($ctx['user'])
            ->deleteJson('/chat-v1/messages/'.$message->id)
            ->assertNoContent();

        Event::assertDispatched(MessageDeleted::class);
    }

    public function test_mark_read_dispatches_only_when_pointer_advances(): void
    {
        Event::fake([ReadReceiptUpdated::class]);

        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $messages = $this->seedMessages($chat, $ctx['user'], $ctx['partner'], 3);
        $lowId = $messages->first()->id;
        $highId = $messages->last()->id;

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/read', ['last_read_message_id' => $highId])
            ->assertOk();

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/read', ['last_read_message_id' => $lowId])
            ->assertOk();

        Event::assertDispatchedTimes(ReadReceiptUpdated::class, 1);
    }
}
