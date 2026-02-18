<?php

namespace Tests\Feature\ChatV1;

use App\Models\ChatParticipant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ChatV1\Concerns\CreatesChatV1Context;
use Tests\TestCase;

class ChatReadReceiptTest extends TestCase
{
    use CreatesChatV1Context;
    use RefreshDatabase;

    public function test_mark_read_updates_last_read_fields(): void
    {
        Carbon::setTestNow('2026-02-18 10:00:00');

        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $messages = $this->seedMessages($chat, $ctx['user'], $ctx['partner'], 3);

        $targetId = $messages->last()->id;

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/read', ['last_read_message_id' => $targetId])
            ->assertOk();

        $this->assertDatabaseHas('chat_participants', [
            'chat_id' => $chat->id,
            'user_id' => $ctx['user']->id,
            'last_read_message_id' => $targetId,
        ]);

        $participant = ChatParticipant::query()
            ->where('chat_id', $chat->id)
            ->where('user_id', $ctx['user']->id)
            ->firstOrFail();

        $this->assertNotNull($participant->last_read_at);
    }

    public function test_mark_read_is_idempotent_for_lower_ids(): void
    {
        Carbon::setTestNow('2026-02-18 10:00:00');

        $ctx = $this->createCouplePair();
        $chat = $this->createChatForCouple($ctx['couple'], $ctx['user']);
        $messages = $this->seedMessages($chat, $ctx['user'], $ctx['partner'], 4);

        $highId = $messages->last()->id;
        $lowId = $messages->first()->id;

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/read', ['last_read_message_id' => $highId])
            ->assertOk();

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/read', ['last_read_message_id' => $lowId])
            ->assertOk();

        $this->assertDatabaseHas('chat_participants', [
            'chat_id' => $chat->id,
            'user_id' => $ctx['user']->id,
            'last_read_message_id' => $highId,
        ]);
    }

    public function test_cannot_mark_read_for_message_outside_chat(): void
    {
        $ctxA = $this->createCouplePair();
        $chatA = $this->createChatForCouple($ctxA['couple'], $ctxA['user']);
        $this->seedMessages($chatA, $ctxA['user'], $ctxA['partner'], 2);

        $ctxB = $this->createCouplePair();
        $chatB = $this->createChatForCouple($ctxB['couple'], $ctxB['user']);
        $externalMessage = $this->seedMessages($chatB, $ctxB['user'], $ctxB['partner'], 1)->first();

        $ctxA['user']->forceFill(['current_couple_id' => $ctxA['couple']->id])->save();

        $this->actingAs($ctxA['user'])
            ->postJson('/chat-v1/read', ['last_read_message_id' => $externalMessage->id])
            ->assertNotFound();
    }
}
