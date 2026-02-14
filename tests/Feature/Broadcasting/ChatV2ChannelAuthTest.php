<?php

namespace Tests\Feature\Broadcasting;

use App\Models\ChatV2\Conversation;
use App\Models\Couple;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class ChatV2ChannelAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_outsider_is_denied_private_conversation_subscription(): void
    {
        [$owner, $partner, $couple] = $this->makeCouple();
        $conversation = Conversation::create(['couple_id' => $couple->id]);
        $outsider = User::factory()->create();

        $channels = Broadcast::getChannels();
        $callback = $channels->get('conversation.{conversationId}');

        $this->assertNotNull($callback);
        $this->assertFalse($callback($outsider, (string) $conversation->id));
        $this->assertTrue($callback($owner, (string) $conversation->id));
        $this->assertTrue($callback($partner, (string) $conversation->id));
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

        return [$owner, $partner, $couple];
    }
}
