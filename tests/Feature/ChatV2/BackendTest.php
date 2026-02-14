<?php

namespace Tests\Feature\ChatV2;

use App\Models\ChatV2\Conversation;
use App\Models\ChatV2\Message;
use App\Models\Couple;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_couple_member_can_open_chat_v2_route(): void
    {
        [$user] = $this->makeCouple();

        $this->actingAs($user)
            ->get(route('chatv2.room'))
            ->assertOk();
    }

    public function test_send_message_persists_and_creates_partner_receipt(): void
    {
        [$sender, $partner] = $this->makeCouple();

        $response = $this->actingAs($sender)
            ->postJson(route('chatv2.messages.send'), [
                'type' => 'text',
                'body' => 'hello from chat v2',
            ])
            ->assertCreated();

        $messageId = $response->json('message.id');
        $conversationId = $response->json('message.conversation_id');

        $this->assertDatabaseHas('chat_v2_messages', [
            'id' => $messageId,
            'conversation_id' => $conversationId,
            'sender_id' => $sender->id,
            'body' => 'hello from chat v2',
            'type' => 'text',
        ]);

        $this->assertDatabaseHas('chat_v2_message_receipts', [
            'message_id' => $messageId,
            'user_id' => $partner->id,
            'delivered_at' => null,
            'read_at' => null,
        ]);
    }

    public function test_outsider_cannot_update_receipts_for_another_conversation_message(): void
    {
        [$sender] = $this->makeCouple();
        [$outsider] = $this->makeCouple();

        $conversation = Conversation::query()->first();
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'type' => 'text',
            'body' => 'private',
        ]);

        $this->actingAs($outsider)
            ->postJson(route('chatv2.messages.delivered', $message))
            ->assertForbidden();
    }

    public function test_outsider_cannot_open_another_couples_conversation(): void
    {
        [$owner, $partner, $couple] = $this->makeCouple();
        [$outsider] = $this->makeCouple();
        $conversation = Conversation::query()->where('couple_id', $couple->id)->firstOrFail();

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $owner->id,
            'type' => 'text',
            'body' => 'private',
        ]);

        $this->actingAs($partner)
            ->getJson(route('chatv2.conversation.show', $conversation))
            ->assertOk()
            ->assertJsonPath('messages.0.id', $message->id);

        $this->actingAs($outsider)
            ->getJson(route('chatv2.conversation.show', $conversation))
            ->assertForbidden();
    }

    public function test_delivered_and_read_endpoints_are_idempotent(): void
    {
        [$sender, $partner] = $this->makeCouple();

        $message = $this->createTextMessage($sender, 'status test');

        $delivered = $this->actingAs($partner)
            ->postJson(route('chatv2.messages.delivered', $message))
            ->assertOk()
            ->json('delivered_at');

        $this->actingAs($partner)
            ->postJson(route('chatv2.messages.delivered', $message))
            ->assertOk()
            ->assertJsonPath('delivered_at', $delivered);

        $read = $this->actingAs($partner)
            ->postJson(route('chatv2.messages.read', $message))
            ->assertOk()
            ->json('read_at');

        $this->actingAs($partner)
            ->postJson(route('chatv2.messages.read', $message))
            ->assertOk()
            ->assertJsonPath('read_at', $read);
    }

    public function test_upload_validation_blocks_bad_mime_and_oversize(): void
    {
        [$sender] = $this->makeCouple();
        Storage::fake('public');

        $this->actingAs($sender)
            ->post(route('chatv2.messages.send'), [
                'type' => 'image',
                'attachment' => UploadedFile::fake()->create('bad.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('attachment');

        $this->actingAs($sender)
            ->post(route('chatv2.messages.send'), [
                'type' => 'file',
                'attachment' => UploadedFile::fake()->create('huge.pdf', 11000, 'application/pdf'),
            ])
            ->assertSessionHasErrors('attachment');
    }

    public function test_audio_upload_is_accepted_for_allowed_mime(): void
    {
        [$sender] = $this->makeCouple();
        Storage::fake('public');

        $this->actingAs($sender)
            ->post(route('chatv2.messages.send'), [
                'type' => 'audio',
                'attachment' => UploadedFile::fake()->create('note.webm', 120, 'audio/webm'),
                'duration_ms' => 12000,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('chat_v2_messages', [
            'sender_id' => $sender->id,
            'type' => 'audio',
            'duration_ms' => 12000,
        ]);
    }

    public function test_xp_hook_creates_chat_message_sent_event_with_anti_spam_rules(): void
    {
        [$sender] = $this->makeCouple();

        $this->actingAs($sender)
            ->postJson(route('chatv2.messages.send'), [
                'type' => 'text',
                'body' => 'hey',
            ])
            ->assertCreated();

        $this->assertDatabaseMissing('xp_events', [
            'user_id' => $sender->id,
            'type' => 'chat',
        ]);

        $this->actingAs($sender)
            ->postJson(route('chatv2.messages.send'), [
                'type' => 'text',
                'body' => 'meaningful message',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('xp_events', [
            'user_id' => $sender->id,
            'type' => 'chat',
            'xp_amount' => 2,
        ]);

        for ($i = 0; $i < 40; $i++) {
            $this->actingAs($sender)->postJson(route('chatv2.messages.send'), [
                'type' => 'text',
                'body' => "message number {$i} is long enough",
            ])->assertCreated();
        }

        $xpCount = \App\Models\XpEvent::query()
            ->where('user_id', $sender->id)
            ->where('type', 'chat')
            ->count();

        $this->assertSame(30, $xpCount);
    }

    private function createTextMessage(User $sender, string $body): Message
    {
        $conversation = Conversation::query()
            ->whereHas('couple.users', function ($query) use ($sender) {
                $query->where('users.id', $sender->id);
            })
            ->first();

        if (! $conversation) {
            $couple = $sender->activeCouple();
            $conversation = Conversation::create(['couple_id' => $couple->id]);
        }

        return Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'type' => 'text',
            'body' => $body,
        ]);
    }

    private function makeCouple(): array
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();

        $couple = Couple::create([
            'invite_code' => strtoupper(fake()->bothify('########')),
            'created_by' => $owner->id,
            'status' => 'active',
        ]);

        $couple->users()->attach($owner->id, [
            'role' => 'partner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $couple->users()->attach($partner->id, [
            'role' => 'partner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        Conversation::firstOrCreate(['couple_id' => $couple->id]);

        return [$owner, $partner, $couple];
    }
}
