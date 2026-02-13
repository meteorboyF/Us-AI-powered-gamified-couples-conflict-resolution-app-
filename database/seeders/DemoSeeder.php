<?php

namespace Database\Seeders;

use App\Models\AiBridgeSuggestion;
use App\Models\AiChat;
use App\Models\Couple;
use App\Models\CoupleWallet;
use App\Models\GiftSuggestion;
use App\Models\Memory;
use App\Models\MemoryReaction;
use App\Models\Message;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionCompletion;
use App\Models\MoodCheckin;
use App\Models\RepairAgreement;
use App\Models\RepairSession;
use App\Models\Team;
use App\Models\User;
use App\Models\VaultUnlock;
use App\Models\Wishlist;
use App\Models\World;
use App\Models\WorldItem;
use App\Models\XpEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'DemoPass123!';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->cleanupExistingDemoData();

            $coupleAUsers = [
                $this->createDemoUser('Partner A1', 'couplea1@demo.test'),
                $this->createDemoUser('Partner A2', 'couplea2@demo.test'),
            ];

            $coupleBUsers = [
                $this->createDemoUser('Partner B1', 'coupleb1@demo.test'),
                $this->createDemoUser('Partner B2', 'coupleb2@demo.test'),
            ];

            $coupleA = $this->createCoupleWithWorld(
                inviteCode: 'COUPLEA01',
                users: $coupleAUsers,
                worldType: 'garden',
                worldLevel: 8,
                worldXp: 790,
                ambienceState: 'bright',
                loveSeeds: 420,
                worldItemLevels: [
                    'garden_heart_tree' => 3,
                    'garden_lantern_path' => 2,
                    'garden_koi_pond' => 2,
                    'garden_butterfly_haven' => 2,
                    'garden_sun_dial' => 1,
                    'garden_picnic_set' => 2,
                    'garden_wishing_well' => 2,
                    'garden_flower_arch' => 2,
                ],
                worldSlots: ['slot_a', 'slot_b', 'slot_c', 'slot_d', 'slot_e', 'slot_f', 'slot_g', 'slot_h']
            );

            $coupleB = $this->createCoupleWithWorld(
                inviteCode: 'COUPLEB01',
                users: $coupleBUsers,
                worldType: 'space',
                worldLevel: 9,
                worldXp: 910,
                ambienceState: 'calm',
                loveSeeds: 480,
                worldItemLevels: [
                    'space_hab_core' => 3,
                    'space_star_window' => 2,
                    'space_holo_garden' => 2,
                    'space_satellite_dish' => 2,
                    'space_robot_pet' => 1,
                    'space_nebula_projector' => 2,
                    'space_comms_panel' => 2,
                    'space_memory_frame' => 1,
                ],
                worldSlots: ['slot_a', 'slot_b', 'slot_c', 'slot_d', 'slot_e', 'slot_f', 'slot_g', 'slot_h']
            );

            $this->seedCoupleHistory($coupleA, $coupleAUsers, 'garden');
            $this->seedCoupleHistory($coupleB, $coupleBUsers, 'space');
        });
    }

    protected function cleanupExistingDemoData(): void
    {
        $emails = [
            'couplea1@demo.test',
            'couplea2@demo.test',
            'coupleb1@demo.test',
            'coupleb2@demo.test',
        ];

        $demoUsers = User::whereIn('email', $emails)->get();
        if ($demoUsers->isEmpty()) {
            return;
        }

        $userIds = $demoUsers->pluck('id')->all();

        DB::table('team_user')->whereIn('user_id', $userIds)->delete();
        Team::whereIn('user_id', $userIds)->delete();
        DB::table('couple_user')->whereIn('user_id', $userIds)->delete();

        $coupleIds = Couple::whereIn('created_by', $userIds)->pluck('id')->all();
        if (! empty($coupleIds)) {
            Couple::whereIn('id', $coupleIds)->delete();
        }

        User::whereIn('id', $userIds)->delete();
    }

    protected function createDemoUser(string $name, string $email): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make(self::DEMO_PASSWORD),
        ]);

        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);

        $user->forceFill(['current_team_id' => $team->id])->save();

        return $user->fresh();
    }

    protected function createCoupleWithWorld(
        string $inviteCode,
        array $users,
        string $worldType,
        int $worldLevel,
        int $worldXp,
        string $ambienceState,
        int $loveSeeds,
        array $worldItemLevels,
        array $worldSlots
    ): Couple {
        $couple = Couple::create([
            'invite_code' => $inviteCode,
            'created_by' => $users[0]->id,
            'status' => 'active',
        ]);

        foreach ($users as $user) {
            $couple->users()->attach($user->id, [
                'role' => 'partner',
                'is_active' => true,
                'joined_at' => now()->subDays(45),
                'created_at' => now()->subDays(45),
                'updated_at' => now()->subDays(45),
            ]);
        }

        World::create([
            'couple_id' => $couple->id,
            'theme_type' => $worldType,
            'world_type' => $worldType,
            'level' => $worldLevel,
            'xp_total' => $worldXp,
            'ambience_state' => $ambienceState,
            'cosmetics' => [
                'blooming_garden',
                'starlit_sky',
                '__meta' => [
                    'vibe_score' => $ambienceState === 'bright' ? 78 : 60,
                    'vibe_refreshed_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        CoupleWallet::create([
            'couple_id' => $couple->id,
            'love_seeds_balance' => $loveSeeds,
        ]);

        $slotIndex = 0;
        foreach ($worldItemLevels as $itemKey => $level) {
            WorldItem::create([
                'couple_id' => $couple->id,
                'world_type' => $worldType,
                'item_key' => $itemKey,
                'level' => $level,
                'slot' => $worldSlots[$slotIndex % count($worldSlots)],
                'position' => ['x' => ($slotIndex % 4) + 1, 'y' => intdiv($slotIndex, 4) + 1],
                'is_built' => true,
                'created_at' => now()->subDays(30 - $slotIndex),
                'updated_at' => now()->subDays(2),
            ]);
            $slotIndex++;
        }

        return $couple->fresh();
    }

    protected function seedCoupleHistory(Couple $couple, array $users, string $theme): void
    {
        $this->seedXpEvents($couple, $users);
        $this->seedMoodCheckins($couple, $users);
        $this->seedMissions($couple, $users);
        $this->seedMessages($couple, $users);
        $this->seedRepairSessions($couple, $users);
        $this->seedVault($couple, $users, $theme);
        $this->seedAiData($couple, $users);
        $this->seedGiftData($couple, $users);
    }

    protected function seedXpEvents(Couple $couple, array $users): void
    {
        $types = ['checkin', 'mission', 'repair', 'vault', 'chat'];

        for ($day = 30; $day >= 1; $day--) {
            $date = now()->subDays($day);
            $type = $types[$day % count($types)];
            $user = $users[$day % 2];
            $amount = match ($type) {
                'mission' => 25 + ($day % 20),
                'repair' => 20 + ($day % 30),
                'vault' => 12 + ($day % 6),
                'chat' => 4 + ($day % 3),
                default => 10 + ($day % 4),
            };

            XpEvent::create([
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'type' => $type,
                'xp_amount' => $amount,
                'metadata' => ['seeded' => true, 'day_offset' => $day],
                'created_at' => $date->copy()->setHour(9 + ($day % 10)),
                'updated_at' => $date->copy()->setHour(9 + ($day % 10)),
            ]);
        }
    }

    protected function seedMoodCheckins(Couple $couple, array $users): void
    {
        $reasonCycle = ['work', 'relationship', 'family', 'health', 'random'];
        $needsCycle = ['talk', 'affection', 'reassurance', 'space', 'help'];

        for ($day = 29; $day >= 0; $day--) {
            $date = now()->subDays($day)->toDateString();

            foreach ($users as $index => $user) {
                $mood = 3 + (($day + $index) % 3); // 3..5
                if ($day % 9 === 0) {
                    $mood = 2;
                }

                MoodCheckin::create([
                    'couple_id' => $couple->id,
                    'user_id' => $user->id,
                    'date' => $date,
                    'mood_level' => $mood,
                    'reason_tags' => [$reasonCycle[($day + $index) % count($reasonCycle)]],
                    'needs' => [$needsCycle[($day + 2 + $index) % count($needsCycle)]],
                    'note' => "Check-in for {$date}: feeling {$mood}/5 and staying connected.",
                    'created_at' => Carbon::parse($date)->setHour(8 + $index),
                    'updated_at' => Carbon::parse($date)->setHour(8 + $index),
                ]);
            }
        }
    }

    protected function seedMissions(Couple $couple, array $users): void
    {
        $dailyMissions = Mission::where('type', 'daily')->limit(6)->get();
        $weeklyMissions = Mission::where('type', 'weekly')->limit(4)->get();

        if ($dailyMissions->isEmpty() || $weeklyMissions->isEmpty()) {
            return;
        }

        for ($day = 13; $day >= 0; $day--) {
            $date = now()->subDays($day)->toDateString();
            $mission = $dailyMissions[$day % $dailyMissions->count()];

            $assignment = MissionAssignment::create([
                'couple_id' => $couple->id,
                'mission_id' => $mission->id,
                'assigned_for_date' => $date,
                'status' => 'completed',
                'created_at' => Carbon::parse($date)->startOfDay(),
                'updated_at' => Carbon::parse($date)->setHour(21),
            ]);

            foreach ($users as $index => $user) {
                $completion = MissionCompletion::create([
                    'mission_assignment_id' => $assignment->id,
                    'user_id' => $user->id,
                    'completed_at' => Carbon::parse($date)->setHour(18 + $index),
                    'partner_acknowledged_at' => $index === 0 || $day % 3 === 0 ? Carbon::parse($date)->setHour(22) : null,
                    'notes' => 'Completed together during demo seeding.',
                    'created_at' => Carbon::parse($date)->setHour(18 + $index),
                    'updated_at' => Carbon::parse($date)->setHour(22),
                ]);

                if ($index === 1 && $day % 4 === 0) {
                    $completion->update(['partner_acknowledged_at' => Carbon::parse($date)->setHour(23)]);
                }
            }
        }

        for ($week = 3; $week >= 0; $week--) {
            $start = now()->subWeeks($week)->startOfWeek();
            $mission = $weeklyMissions[$week % $weeklyMissions->count()];

            $assignment = MissionAssignment::create([
                'couple_id' => $couple->id,
                'mission_id' => $mission->id,
                'assigned_for_date' => $start->toDateString(),
                'status' => $week === 3 ? 'pending' : 'completed',
                'created_at' => $start->copy()->setHour(9),
                'updated_at' => $start->copy()->addDays(3)->setHour(20),
            ]);

            if ($assignment->status === 'completed') {
                MissionCompletion::create([
                    'mission_assignment_id' => $assignment->id,
                    'user_id' => $users[$week % 2]->id,
                    'completed_at' => $start->copy()->addDays(2)->setHour(19),
                    'partner_acknowledged_at' => $start->copy()->addDays(2)->setHour(21),
                    'notes' => 'Weekly mission completed and acknowledged.',
                    'created_at' => $start->copy()->addDays(2)->setHour(19),
                    'updated_at' => $start->copy()->addDays(2)->setHour(21),
                ]);
            }
        }
    }

    protected function seedMessages(Couple $couple, array $users): void
    {
        $timeline = [
            [$users[0], 'Good morning love. Hope your day starts gently.', 'text', []],
            [$users[1], 'Thanks babe. You always calm me down.', 'text', []],
            [$users[0], 'I felt ignored earlier when we rushed dinner plans.', 'text', []],
            [$users[1], 'I hear you. I got overwhelmed, not disconnected from you.', 'text', []],
            [$users[0], 'Let us reset tonight. 20 minutes, phones away?', 'text', []],
            [$users[1], 'Deal. I appreciate you saying it directly.', 'text', []],
            [$users[0], 'quick_love: You are my safe place.', 'love_button', ['label' => 'You are my safe place']],
            [$users[1], 'reaction: heart', 'reaction', ['reaction' => 'heart', 'target' => 'quick_love']],
            [$users[1], 'Mini win today: we handled tension kindly.', 'text', []],
            [$users[0], 'Proud of us. Date idea this weekend: sunset walk + ramen.', 'text', []],
        ];

        $base = now()->subDays(20);
        foreach ($timeline as $index => [$user, $content, $type, $meta]) {
            Message::create([
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'content' => $content,
                'type' => $type,
                'metadata' => empty($meta) ? null : $meta,
                'read_at' => $base->copy()->addMinutes(($index + 1) * 12),
                'created_at' => $base->copy()->addMinutes($index * 10),
                'updated_at' => $base->copy()->addMinutes($index * 10),
            ]);
        }
    }

    protected function seedRepairSessions(Couple $couple, array $users): void
    {
        $topics = [
            'Schedule mismatch after work, intensity medium, immediate needs: reassurance + clarity.',
            'Tone escalation during chores talk, intensity high, immediate needs: space + repair.',
        ];

        foreach ($topics as $index => $topic) {
            $started = now()->subDays(16 - ($index * 6))->setHour(19);

            $session = RepairSession::create([
                'couple_id' => $couple->id,
                'initiated_by' => $users[$index % 2]->id,
                'status' => 'completed',
                'conflict_topic' => $topic,
                'initiator_perspective' => 'I felt disconnected when our plan changed suddenly.',
                'partner_perspective' => 'I felt pressured and reacted defensively.',
                'shared_goals' => ['communicate', 'listen', 'support'],
                'started_at' => $started,
                'completed_at' => $started->copy()->addHour(),
                'created_at' => $started,
                'updated_at' => $started->copy()->addHour(),
            ]);

            RepairAgreement::create([
                'repair_session_id' => $session->id,
                'couple_id' => $couple->id,
                'agreement_text' => 'I will ask for a 10-minute pause before reacting. Follow-up: tomorrow 8:30 PM.',
                'created_by' => $users[0]->id,
                'partner_acknowledged' => true,
                'acknowledged_at' => $started->copy()->addMinutes(40),
                'created_at' => $started->copy()->addMinutes(20),
                'updated_at' => $started->copy()->addMinutes(40),
            ]);

            RepairAgreement::create([
                'repair_session_id' => $session->id,
                'couple_id' => $couple->id,
                'agreement_text' => 'I will reflect back what I heard before responding. Follow-up: Sunday check-in.',
                'created_by' => $users[1]->id,
                'partner_acknowledged' => true,
                'acknowledged_at' => $started->copy()->addMinutes(50),
                'created_at' => $started->copy()->addMinutes(25),
                'updated_at' => $started->copy()->addMinutes(50),
            ]);
        }
    }

    protected function seedVault(Couple $couple, array $users, string $theme): void
    {
        $disk = Storage::disk('public');
        $baseDir = "demo/couple-{$couple->id}";
        $disk->makeDirectory($baseDir);

        $photoA = "{$baseDir}/milestone-trip.jpg";
        $photoB = "{$baseDir}/anniversary-toast.jpg";
        $disk->put($photoA, "Demo placeholder image for {$theme} trip.");
        $disk->put($photoB, 'Demo placeholder image for anniversary.');

        $sharedComfort = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $users[0]->id,
            'type' => 'photo',
            'title' => 'First Trip Together',
            'description' => 'Captured during our first weekend getaway.',
            'file_path' => $photoA,
            'file_size' => 2048,
            'mime_type' => 'image/jpeg',
            'visibility' => 'shared',
            'comfort' => true,
            'created_at' => now()->subDays(25),
            'updated_at' => now()->subDays(25),
        ]);

        $private = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $users[1]->id,
            'type' => 'text',
            'title' => 'Private Reflection',
            'description' => 'A personal note to process emotions before sharing.',
            'visibility' => 'private',
            'comfort' => false,
            'created_at' => now()->subDays(17),
            'updated_at' => now()->subDays(17),
        ]);

        $dual = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $users[0]->id,
            'type' => 'photo',
            'title' => 'Anniversary Dinner',
            'description' => 'Special memory we unlock together.',
            'file_path' => $photoB,
            'file_size' => 2300,
            'mime_type' => 'image/jpeg',
            'visibility' => 'locked',
            'comfort' => true,
            'locked_at' => now()->subDays(12),
            'created_at' => now()->subDays(12),
            'updated_at' => now()->subDays(12),
        ]);

        foreach ($users as $index => $user) {
            VaultUnlock::create([
                'memory_id' => $dual->id,
                'user_id' => $user->id,
                'approved_at' => now()->subDays(11)->setHour(10 + $index),
                'expires_at' => now()->addDays(3),
                'created_at' => now()->subDays(11)->setHour(10 + $index),
                'updated_at' => now()->subDays(11)->setHour(10 + $index),
            ]);
        }

        MemoryReaction::create([
            'memory_id' => $sharedComfort->id,
            'user_id' => $users[1]->id,
            'reaction' => 'heart',
            'created_at' => now()->subDays(24),
            'updated_at' => now()->subDays(24),
        ]);

        MemoryReaction::create([
            'memory_id' => $private->id,
            'user_id' => $users[1]->id,
            'reaction' => 'smile',
            'created_at' => now()->subDays(16),
            'updated_at' => now()->subDays(16),
        ]);
    }

    protected function seedAiData(Couple $couple, array $users): void
    {
        foreach ($users as $index => $user) {
            AiChat::create([
                'user_id' => $user->id,
                'couple_id' => $couple->id,
                'type' => 'vent',
                'messages' => [
                    ['role' => 'assistant', 'content' => "I'm here with you. What felt hardest today?"],
                    ['role' => 'user', 'content' => 'I felt tense when communication got rushed.'],
                    ['role' => 'assistant', 'content' => 'That makes sense. What would support look like tonight?'],
                ],
                'is_active' => false,
                'created_at' => now()->subDays(15 - $index),
                'updated_at' => now()->subDays(15 - $index),
            ]);

            AiChat::create([
                'user_id' => $user->id,
                'couple_id' => $couple->id,
                'type' => 'bridge',
                'messages' => [
                    ['role' => 'assistant', 'content' => 'Let us reframe this with an I statement.'],
                    ['role' => 'user', 'content' => 'I get upset when plans switch suddenly.'],
                    ['role' => 'assistant', 'content' => 'Try: I feel unsettled when plans shift late because I need predictability.'],
                ],
                'is_active' => $index === 0,
                'created_at' => now()->subDays(8 - $index),
                'updated_at' => now()->subDays(8 - $index),
            ]);
        }

        $draft = AiBridgeSuggestion::create([
            'couple_id' => $couple->id,
            'user_id' => $users[0]->id,
            'source_context' => ['mode' => 'bridge', 'seeded' => true],
            'suggested_message' => 'I feel disconnected when we multitask during serious talks. Can we set 15 focused minutes tonight?',
            'status' => AiBridgeSuggestion::STATUS_DRAFT,
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        $sentSuggestion = AiBridgeSuggestion::create([
            'couple_id' => $couple->id,
            'user_id' => $users[1]->id,
            'source_context' => ['mode' => 'bridge', 'seeded' => true],
            'suggested_message' => 'I feel anxious when plans change late because I need clarity. Could we confirm dinner plans by 5 PM?',
            'status' => AiBridgeSuggestion::STATUS_SENT,
            'approved_at' => now()->subDays(5)->setHour(11),
            'sent_at' => now()->subDays(5)->setHour(12),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        Message::create([
            'couple_id' => $couple->id,
            'user_id' => $users[1]->id,
            'content' => $sentSuggestion->suggested_message,
            'type' => 'text',
            'metadata' => [
                'source' => 'ai_bridge',
                'ai_bridge_suggestion_id' => $sentSuggestion->id,
            ],
            'created_at' => now()->subDays(5)->setHour(12),
            'updated_at' => now()->subDays(5)->setHour(12),
        ]);
    }

    protected function seedGiftData(Couple $couple, array $users): void
    {
        Wishlist::create([
            'couple_id' => $couple->id,
            'user_id' => $users[0]->id,
            'budget_min' => 25,
            'budget_max' => 120,
            'currency' => 'USD',
            'love_languages' => ['words_of_affirmation', 'quality_time'],
            'likes' => ['tea sets', 'bookstores', 'sunset walks'],
            'dislikes' => ['strong perfumes'],
            'share_with_partner' => true,
            'created_at' => now()->subDays(22),
            'updated_at' => now()->subDays(2),
        ]);

        Wishlist::create([
            'couple_id' => $couple->id,
            'user_id' => $users[1]->id,
            'budget_min' => 20,
            'budget_max' => 150,
            'currency' => 'USD',
            'love_languages' => ['acts_of_service', 'physical_touch'],
            'likes' => ['cozy hoodies', 'vinyl', 'coffee tools'],
            'dislikes' => ['novelty gadgets'],
            'share_with_partner' => false,
            'created_at' => now()->subDays(21),
            'updated_at' => now()->subDays(3),
        ]);

        GiftSuggestion::create([
            'couple_id' => $couple->id,
            'requested_by' => $users[0]->id,
            'input_snapshot' => [
                'occasion' => 'anniversary',
                'budget' => '40-90',
                'seeded' => true,
            ],
            'suggestions' => [
                [
                    'title' => 'Custom photo book',
                    'category' => 'memory',
                    'description' => 'A curated album from your first year together.',
                ],
                [
                    'title' => 'Home coffee date kit',
                    'category' => 'experience',
                    'description' => 'Beans, mugs, and a playlist for a cozy date night.',
                ],
            ],
            'source' => 'fallback',
            'created_at' => now()->subDays(10)->setHour(13),
        ]);

        GiftSuggestion::create([
            'couple_id' => $couple->id,
            'requested_by' => $users[1]->id,
            'input_snapshot' => [
                'occasion' => 'just_because',
                'budget' => '20-70',
                'seeded' => true,
            ],
            'suggestions' => [
                [
                    'title' => 'Handwritten 7-day note bundle',
                    'category' => 'thoughtful',
                    'description' => 'One short encouragement note for each day of the week.',
                ],
            ],
            'source' => 'fallback',
            'created_at' => now()->subDays(7)->setHour(16),
        ]);
    }

    /**
     * Helper for the artisan command output.
     */
    public static function demoCredentials(): array
    {
        return [
            ['email' => 'couplea1@demo.test', 'password' => self::DEMO_PASSWORD, 'label' => 'Couple A Partner 1'],
            ['email' => 'couplea2@demo.test', 'password' => self::DEMO_PASSWORD, 'label' => 'Couple A Partner 2'],
            ['email' => 'coupleb1@demo.test', 'password' => self::DEMO_PASSWORD, 'label' => 'Couple B Partner 1'],
            ['email' => 'coupleb2@demo.test', 'password' => self::DEMO_PASSWORD, 'label' => 'Couple B Partner 2'],
        ];
    }
}
