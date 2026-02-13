<?php

namespace Database\Seeders;

use App\Models\AiChat;
use App\Models\Couple;
use App\Models\Memory;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\Team;
use App\Models\User;
use App\Models\World;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alex = User::firstOrCreate(
            ['email' => 'demo.alex@example.com'],
            ['name' => 'Alex Demo', 'password' => Hash::make('password')]
        );

        $sam = User::firstOrCreate(
            ['email' => 'demo.sam@example.com'],
            ['name' => 'Sam Demo', 'password' => Hash::make('password')]
        );

        $this->ensurePersonalTeam($alex);
        $this->ensurePersonalTeam($sam);

        $couple = Couple::firstOrCreate(
            ['created_by' => $alex->id],
            ['invite_code' => 'DEMOLOVE', 'status' => 'active']
        );

        if (! $couple->users()->where('users.id', $alex->id)->exists()) {
            $couple->users()->attach($alex->id, [
                'role' => 'partner',
                'is_active' => true,
                'joined_at' => now(),
            ]);
        }

        if (! $couple->users()->where('users.id', $sam->id)->exists()) {
            $couple->users()->attach($sam->id, [
                'role' => 'partner',
                'is_active' => true,
                'joined_at' => now(),
            ]);
        }

        World::updateOrCreate(
            ['couple_id' => $couple->id],
            [
                'theme_type' => 'garden',
                'level' => 4,
                'xp_total' => 360,
                'ambience_state' => 'calm',
                'cosmetics' => ['blooming_garden'],
            ]
        );

        $dailyMission = Mission::firstOrCreate(
            ['title' => 'Two-minute check-in'],
            [
                'description' => 'Share one feeling and one need for today.',
                'type' => 'daily',
                'xp_reward' => 20,
                'category' => 'communication',
                'is_active' => true,
            ]
        );

        $weeklyMission = Mission::firstOrCreate(
            ['title' => 'Plan a simple date'],
            [
                'description' => 'Pick one low-effort date idea for this week.',
                'type' => 'weekly',
                'xp_reward' => 40,
                'category' => 'quality_time',
                'is_active' => true,
            ]
        );

        MissionAssignment::firstOrCreate([
            'couple_id' => $couple->id,
            'mission_id' => $dailyMission->id,
            'assigned_for_date' => today()->toDateString(),
        ]);

        MissionAssignment::firstOrCreate([
            'couple_id' => $couple->id,
            'mission_id' => $weeklyMission->id,
            'assigned_for_date' => now()->startOfWeek()->toDateString(),
        ]);

        Memory::firstOrCreate([
            'couple_id' => $couple->id,
            'created_by' => $alex->id,
            'type' => 'text',
            'title' => 'Coffee walk memory',
        ], [
            'description' => 'We took a short walk and talked without phones.',
            'visibility' => 'shared',
            'comfort' => true,
        ]);

        Memory::firstOrCreate([
            'couple_id' => $couple->id,
            'created_by' => $sam->id,
            'type' => 'text',
            'title' => 'Bridge statement draft',
        ], [
            'description' => 'I feel overwhelmed when plans change late because I need clarity.',
            'visibility' => 'shared',
            'comfort' => false,
        ]);

        AiChat::updateOrCreate(
            ['user_id' => $alex->id, 'couple_id' => $couple->id, 'type' => 'vent'],
            [
                'messages' => [
                    ['role' => 'assistant', 'content' => "I'm here to listen. What's on your mind?"],
                    ['role' => 'user', 'content' => 'I felt disconnected after our argument.'],
                    ['role' => 'assistant', 'content' => 'That makes sense. What part felt hardest for you?'],
                ],
                'is_active' => true,
            ]
        );

        AiChat::updateOrCreate(
            ['user_id' => $sam->id, 'couple_id' => $couple->id, 'type' => 'bridge'],
            [
                'messages' => [
                    ['role' => 'assistant', 'content' => "Let's reframe this calmly."],
                    ['role' => 'user', 'content' => 'I feel dismissed when timing shifts suddenly.'],
                    ['role' => 'assistant', 'content' => 'Try: "I feel stressed when plans change late because I need predictability."'],
                ],
                'is_active' => true,
            ]
        );
    }

    protected function ensurePersonalTeam(User $user): void
    {
        if ($user->ownedTeams()->exists()) {
            return;
        }

        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);

        $user->switchTeam($team);
    }
}
