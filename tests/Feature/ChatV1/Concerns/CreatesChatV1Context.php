<?php

namespace Tests\Feature\ChatV1\Concerns;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use Illuminate\Support\Collection;

trait CreatesChatV1Context
{
    /**
     * @return array{user: User,partner: User,couple: Couple}
     */
    protected function createCouplePair(): array
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $couple = Couple::query()->create([
            'name' => 'Chat Couple',
            'invite_code' => 'CHAT'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'created_by_user_id' => $user->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $partner->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $user->forceFill(['current_couple_id' => $couple->id])->save();
        $partner->forceFill(['current_couple_id' => $couple->id])->save();

        return [
            'user' => $user,
            'partner' => $partner,
            'couple' => $couple,
        ];
    }

    protected function createChatForCouple(Couple $couple, User $creator): Chat
    {
        $chat = Chat::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $creator->id,
        ]);

        foreach ($couple->members()->pluck('users.id') as $memberId) {
            ChatParticipant::query()->create([
                'chat_id' => $chat->id,
                'user_id' => $memberId,
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        return $chat;
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    protected function seedMessages(Chat $chat, User $sender, User $partner, int $count): Collection
    {
        $messages = collect();

        for ($i = 1; $i <= $count; $i++) {
            $messages->push(ChatMessage::query()->create([
                'chat_id' => $chat->id,
                'sender_id' => $i % 2 === 0 ? $partner->id : $sender->id,
                'type' => 'text',
                'body' => "Message {$i}",
                'sent_at' => now()->subMinutes($count - $i),
            ]));
        }

        $chat->forceFill(['last_message_id' => $messages->last()?->id])->save();

        return $messages;
    }
}
