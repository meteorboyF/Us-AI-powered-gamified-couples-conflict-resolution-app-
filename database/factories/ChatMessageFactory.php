<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $chat = Chat::factory()->create();

        return [
            'chat_id' => $chat->id,
            'couple_id' => $chat->couple_id,
            'sender_id' => User::factory(),
            'body' => fake()->sentence(),
            'sent_at' => now(),
            'read_at' => null,
        ];
    }
}
