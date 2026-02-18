<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatAttachment;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\CoupleMission;
use App\Models\CoupleWorldState;
use App\Models\DailyCheckin;
use App\Models\MissionTemplate;
use App\Models\User;
use App\Models\VaultItem;
use App\Models\WorldItem;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(WorldItemsSeeder::class);
        $this->call(MissionTemplatesSeeder::class);

        if (app()->environment(['local', 'testing'])) {
            $demoUser = User::updateOrCreate(
                ['email' => 'demo@us.test'],
                [
                    'name' => 'Demo User',
                    'password' => Hash::make('password'),
                ]
            );

            $partnerUser = User::updateOrCreate(
                ['email' => 'partner@us.test'],
                [
                    'name' => 'Partner User',
                    'password' => Hash::make('password'),
                ]
            );

            $couple = Couple::query()->updateOrCreate(
                ['invite_code' => 'DEMOUS01'],
                [
                    'name' => 'Demo Couple',
                    'created_by_user_id' => $demoUser->id,
                ]
            );

            CoupleMember::query()->updateOrCreate(
                [
                    'couple_id' => $couple->id,
                    'user_id' => $demoUser->id,
                ],
                [
                    'role' => 'owner',
                    'joined_at' => now(),
                ]
            );

            CoupleMember::query()->updateOrCreate(
                [
                    'couple_id' => $couple->id,
                    'user_id' => $partnerUser->id,
                ],
                [
                    'role' => 'member',
                    'joined_at' => now(),
                ]
            );

            $demoUser->forceFill(['current_couple_id' => $couple->id])->save();
            $partnerUser->forceFill(['current_couple_id' => $couple->id])->save();

            CoupleWorldState::query()->updateOrCreate(
                ['couple_id' => $couple->id],
                [
                    'vibe' => 'neutral',
                    'level' => 1,
                    'xp' => 0,
                ]
            );

            $homeBase = WorldItem::query()->where('key', 'home_base')->first();

            if ($homeBase) {
                $couple->worldItems()->syncWithoutDetaching([
                    $homeBase->id => ['unlocked_at' => now()],
                ]);
            }

            $missionKeys = ['daily_gratitude', 'weekly_date_planning'];

            $templates = MissionTemplate::query()
                ->whereIn('key', $missionKeys)
                ->get()
                ->keyBy('key');

            foreach ($missionKeys as $key) {
                if (! $templates->has($key)) {
                    continue;
                }

                CoupleMission::query()->updateOrCreate(
                    [
                        'couple_id' => $couple->id,
                        'mission_template_id' => $templates[$key]->id,
                    ],
                    [
                        'status' => 'active',
                        'started_at' => Carbon::today(),
                        'completed_at' => null,
                    ]
                );
            }

            DailyCheckin::query()->updateOrCreate(
                [
                    'couple_id' => $couple->id,
                    'user_id' => $demoUser->id,
                    'checkin_date' => Carbon::today(),
                ],
                [
                    'mood' => 'good',
                    'note' => 'Feeling steady and connected today.',
                ]
            );

            DailyCheckin::query()->updateOrCreate(
                [
                    'couple_id' => $couple->id,
                    'user_id' => $partnerUser->id,
                    'checkin_date' => Carbon::yesterday(),
                ],
                [
                    'mood' => 'okay',
                    'note' => 'A little tired but optimistic.',
                ]
            );

            $chat = Chat::query()->updateOrCreate(
                ['couple_id' => $couple->id],
                ['created_by_user_id' => $demoUser->id]
            );

            ChatParticipant::query()->updateOrCreate(
                ['chat_id' => $chat->id, 'user_id' => $demoUser->id],
                ['role' => 'member', 'joined_at' => now()]
            );

            ChatParticipant::query()->updateOrCreate(
                ['chat_id' => $chat->id, 'user_id' => $partnerUser->id],
                ['role' => 'member', 'joined_at' => now()]
            );

            if (! $chat->messages()->exists()) {
                for ($i = 1; $i <= 15; $i++) {
                    $senderId = $i % 2 === 0 ? $partnerUser->id : $demoUser->id;

                    ChatMessage::query()->create([
                        'chat_id' => $chat->id,
                        'sender_id' => $senderId,
                        'type' => 'text',
                        'body' => "Demo chat message {$i}",
                        'sent_at' => Carbon::now()->subMinutes(16 - $i),
                    ]);
                }
            }

            $latestMessageId = $chat->messages()->orderByDesc('id')->value('id');
            $chat->forceFill(['last_message_id' => $latestMessageId])->save();

            $firstMessage = $chat->messages()->orderBy('id')->first();
            if ($firstMessage) {
                ChatAttachment::query()->updateOrCreate(
                    [
                        'chat_message_id' => $firstMessage->id,
                        'path' => 'chat-v1/demo/sample-guide.pdf',
                    ],
                    [
                        'disk' => 'public',
                        'original_name' => 'sample-guide.pdf',
                        'mime' => 'application/pdf',
                        'size' => 102400,
                        'kind' => 'file',
                    ]
                );
            }

            VaultItem::query()->updateOrCreate(
                [
                    'couple_id' => $couple->id,
                    'title' => 'Shared Gratitude Note',
                ],
                [
                    'created_by_user_id' => $demoUser->id,
                    'type' => 'note',
                    'body' => 'Thank you for the support this week.',
                    'is_sensitive' => false,
                    'is_locked' => false,
                    'meta' => ['consent_required' => false],
                ]
            );

            VaultItem::query()->updateOrCreate(
                [
                    'couple_id' => $couple->id,
                    'title' => 'Why I Appreciate You',
                ],
                [
                    'created_by_user_id' => $partnerUser->id,
                    'type' => 'reason',
                    'body' => 'You always listen before reacting.',
                    'is_sensitive' => false,
                    'is_locked' => false,
                    'meta' => ['consent_required' => false],
                ]
            );

            VaultItem::query()->updateOrCreate(
                [
                    'couple_id' => $couple->id,
                    'title' => 'Sensitive Memory',
                ],
                [
                    'created_by_user_id' => $demoUser->id,
                    'type' => 'timeline',
                    'body' => 'Private memory details stay protected.',
                    'is_sensitive' => true,
                    'is_locked' => true,
                    'meta' => ['consent_required' => true],
                ]
            );
        }
    }
}
