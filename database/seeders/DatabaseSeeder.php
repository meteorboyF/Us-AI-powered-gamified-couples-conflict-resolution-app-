<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $alex = User::factory()->create([
            'name' => 'Alex',
            'email' => 'alex@example.com',
        ]);

        $sam = User::factory()->create([
            'name' => 'Sam',
            'email' => 'sam@example.com',
        ]);

        $chat = Chat::query()->create([
            'couple_id' => 1,
        ]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $alex->id,
            'joined_at' => now()->subDay(),
        ]);
        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $sam->id,
            'joined_at' => now()->subDay(),
        ]);

        ChatMessage::query()->create([
            'chat_id' => $chat->id,
            'couple_id' => $chat->couple_id,
            'sender_id' => $alex->id,
            'body' => 'Hey, can we reset and talk calmly tonight?',
            'sent_at' => now()->subMinutes(15),
            'read_at' => now()->subMinutes(14),
        ]);
        ChatMessage::query()->create([
            'chat_id' => $chat->id,
            'couple_id' => $chat->couple_id,
            'sender_id' => $sam->id,
            'body' => 'Yes, thank you for saying that. Let us do it after dinner.',
            'sent_at' => now()->subMinutes(8),
            'read_at' => null,
        ]);
    }
}
