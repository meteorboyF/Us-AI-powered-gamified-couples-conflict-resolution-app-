<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Couple;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionCompletion;
use App\Models\Memory;
use App\Models\RepairSession;
use App\Models\RepairAgreement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TestCoupleSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Users
        $romeo = User::firstOrCreate(
            ['email' => 'romeo@example.com'],
            ['name' => 'Romeo', 'password' => Hash::make('password')]
        );

        $juliet = User::firstOrCreate(
            ['email' => 'juliet@example.com'],
            ['name' => 'Juliet', 'password' => Hash::make('password')]
        );

        // Ensure Personal Teams (Fix for Jetstream error)
        foreach ([$romeo, $juliet] as $user) {
            if ($user->ownedTeams()->count() === 0) {
                $user->ownedTeams()->save(\App\Models\Team::forceCreate([
                    'user_id' => $user->id,
                    'name' => explode(' ', $user->name, 2)[0] . "'s Team",
                    'personal_team' => true,
                ]));
                $user->refresh();
                $user->switchTeam($user->ownedTeams()->first());
            }
        }

        // 2. Create Couple
        $couple = Couple::firstOrCreate(
            ['created_by' => $romeo->id],
            ['invite_code' => 'LOVE123', 'status' => 'active']
        );

        // 3. Attach Users to Couple (Conceptually check if attached)
        if (!$couple->users()->where('users.id', $romeo->id)->exists()) {
            $couple->users()->attach($romeo->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);
        }
        if (!$couple->users()->where('users.id', $juliet->id)->exists()) {
            $couple->users()->attach($juliet->id, ['role' => 'member', 'is_active' => true, 'joined_at' => now()]);
        }

        // 4. Create World (XP System)
        if (!$couple->world) {
            $couple->world()->create([
                'level' => 3,
                'xp_total' => 450,
                'ambience_state' => 'sunset',
            ]);
        }

        // 5. Seed Missions and Completions
        // Ensure some missions exist
        if (Mission::count() === 0) {
            $checkin = Mission::create([
                'title' => 'Daily Check-in',
                'description' => 'Ask your partner how their day was.',
                'type' => 'daily',
                'xp_reward' => 50,
                'category' => 'connection',
                'is_active' => true,
            ]);

            $dateNight = Mission::create([
                'title' => 'Plan a Date Night',
                'description' => 'Plan a special evening together.',
                'type' => 'weekly',
                'xp_reward' => 200,
                'category' => 'romance',
                'is_active' => true,
            ]);
        } else {
            $checkin = Mission::where('type', 'daily')->first();
        }

        // Mark a mission assignment as completed yesterday by Romeo
        $assignment = MissionAssignment::firstOrCreate([
            'couple_id' => $couple->id,
            'mission_id' => $checkin->id,
            'assigned_for_date' => Carbon::yesterday()->toDateString(),
        ], [
            'status' => 'completed',
        ]);

        MissionCompletion::firstOrCreate([
            'mission_assignment_id' => $assignment->id,
            'user_id' => $romeo->id,
        ], [
            'completed_at' => Carbon::yesterday(),
            'notes' => 'Completed via demo seeder',
        ]);

        // 6. Seed Repair Session
        $session = RepairSession::create([
            'couple_id' => $couple->id,
            'initiated_by' => $juliet->id,
            'status' => 'completed',
            'conflict_topic' => 'Leaving socks on the floor',
            'initiator_perspective' => 'I feel frustrated when chores are left unfinished.',
            'partner_perspective' => 'I did not realize it was causing that much stress.',
            'shared_goals' => ['communicate', 'patience', 'support'],
            'started_at' => Carbon::now()->subDays(2)->subHour(),
            'completed_at' => Carbon::now()->subDays(2),
        ]);

        RepairAgreement::create([
            'repair_session_id' => $session->id,
            'couple_id' => $couple->id,
            'agreement_text' => 'I will put my socks in the hamper immediately.',
            'created_by' => $juliet->id,
            'partner_acknowledged' => true,
            'acknowledged_at' => Carbon::now()->subDays(2),
        ]);

        // 7. Seed Memories (One Text, One Photo)
        // Text Memory
        Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $romeo->id,
            'type' => 'text',
            'description' => 'Remembering our trip to Verona. It was magical.',
            'visibility' => 'shared',
            'created_at' => Carbon::now()->subMonths(6),
        ]);

        // Locked Text Memory
        Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $juliet->id,
            'type' => 'text',
            'title' => 'My Secret Promise',
            'description' => 'I promise to always love you, even when you leave socks around.',
            'visibility' => 'locked',
            'locked_at' => Carbon::now(),
            'created_at' => Carbon::now()->subMonths(1),
        ]);

        // Fake Photo Memory
        // We'll create a dummy file in storage
        $filename = 'dummy-photo.jpg';
        $path = "memories/{$couple->id}/photo/{$filename}";

        // Create a simple blank image or text file masquerading as image if GD not available, 
        // but let's try to grab a placeholder if possible, or just create a text file.
        // To be safe and simple, we'll write a text file but call it .jpg - browsers won't render it 
        // but it won't crash the server. BETTER: Copy a real asset if we had one.
        // Actually, let's just make a text file. The browser will show broken image icon, which is acceptable for a seeder 
        // unless I download one. Let's try to download one.

        try {
            $imageContent = file_get_contents('https://placehold.co/600x400/purple/white.jpg?text=Us+Memory');
            if ($imageContent) {
                Storage::disk('public')->put($path, $imageContent);

                Memory::create([
                    'couple_id' => $couple->id,
                    'created_by' => $juliet->id,
                    'type' => 'photo',
                    'title' => 'Our First Anniversary',
                    'file_path' => $path,
                    'file_size' => 1024,
                    'mime_type' => 'image/jpeg',
                    'visibility' => 'shared',
                    'created_at' => Carbon::now()->subYear(),
                ]);
            }
        } catch (\Exception $e) {
            // Fallback if no internet or error
        }
    }
}
