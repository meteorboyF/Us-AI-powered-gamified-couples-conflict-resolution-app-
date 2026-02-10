<?php

namespace Database\Seeders;

use App\Models\Mission;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $missions = [
            // Daily Missions - Gratitude
            [
                'title' => 'Share One Thing You\'re Grateful For',
                'description' => 'Tell your partner one thing you\'re grateful for today.',
                'type' => 'daily',
                'xp_reward' => 15,
                'category' => 'gratitude',
            ],
            [
                'title' => 'Compliment Your Partner',
                'description' => 'Give your partner a genuine compliment about something specific.',
                'type' => 'daily',
                'xp_reward' => 15,
                'category' => 'gratitude',
            ],
            [
                'title' => 'Express Appreciation',
                'description' => 'Thank your partner for something they did recently.',
                'type' => 'daily',
                'xp_reward' => 15,
                'category' => 'gratitude',
            ],

            // Daily Missions - Communication
            [
                'title' => 'Ask About Their Day',
                'description' => 'Ask your partner about their day and really listen to their answer.',
                'type' => 'daily',
                'xp_reward' => 20,
                'category' => 'communication',
            ],
            [
                'title' => 'Share a Feeling',
                'description' => 'Share one emotion you felt today and why.',
                'type' => 'daily',
                'xp_reward' => 20,
                'category' => 'communication',
            ],
            [
                'title' => 'Active Listening',
                'description' => 'Have a 10-minute conversation where you focus entirely on listening.',
                'type' => 'daily',
                'xp_reward' => 25,
                'category' => 'communication',
            ],

            // Daily Missions - Affection
            [
                'title' => 'Send a Sweet Message',
                'description' => 'Send your partner a loving text or note.',
                'type' => 'daily',
                'xp_reward' => 10,
                'category' => 'affection',
            ],
            [
                'title' => 'Physical Touch',
                'description' => 'Give your partner a hug, kiss, or hold hands for a moment.',
                'type' => 'daily',
                'xp_reward' => 15,
                'category' => 'affection',
            ],
            [
                'title' => 'Say "I Love You"',
                'description' => 'Tell your partner you love them with genuine feeling.',
                'type' => 'daily',
                'xp_reward' => 10,
                'category' => 'affection',
            ],

            // Weekly Missions - Quality Time
            [
                'title' => 'Plan a Date Night',
                'description' => 'Plan and schedule a date night together this week.',
                'type' => 'weekly',
                'xp_reward' => 50,
                'category' => 'quality_time',
            ],
            [
                'title' => 'Cook Together',
                'description' => 'Prepare a meal together and enjoy it.',
                'type' => 'weekly',
                'xp_reward' => 40,
                'category' => 'quality_time',
            ],
            [
                'title' => 'Try Something New',
                'description' => 'Do an activity together that neither of you has done before.',
                'type' => 'weekly',
                'xp_reward' => 60,
                'category' => 'quality_time',
            ],

            // Weekly Missions - Memories
            [
                'title' => 'Share a Favorite Memory',
                'description' => 'Tell your partner about a favorite memory you have together.',
                'type' => 'weekly',
                'xp_reward' => 30,
                'category' => 'memories',
            ],
            [
                'title' => 'Take a Photo Together',
                'description' => 'Take a new photo together and save it to your vault.',
                'type' => 'weekly',
                'xp_reward' => 35,
                'category' => 'memories',
            ],

            // Weekly Missions - Growth
            [
                'title' => 'Discuss a Goal',
                'description' => 'Share a personal goal and how your partner can support you.',
                'type' => 'weekly',
                'xp_reward' => 45,
                'category' => 'growth',
            ],
            [
                'title' => 'Give Constructive Feedback',
                'description' => 'Share one thing your partner does well and one area for growth (kindly).',
                'type' => 'weekly',
                'xp_reward' => 50,
                'category' => 'growth',
            ],

            // Repair Missions
            [
                'title' => 'Apologize Sincerely',
                'description' => 'If there\'s been a conflict, offer a genuine apology.',
                'type' => 'repair',
                'xp_reward' => 75,
                'category' => 'repair',
            ],
            [
                'title' => 'Express Your Needs',
                'description' => 'Calmly share what you need from your partner right now.',
                'type' => 'repair',
                'xp_reward' => 60,
                'category' => 'repair',
            ],
            [
                'title' => 'Find Common Ground',
                'description' => 'Identify one thing you both agree on in a disagreement.',
                'type' => 'repair',
                'xp_reward' => 70,
                'category' => 'repair',
            ],
        ];

        foreach ($missions as $mission) {
            Mission::create($mission);
        }
    }
}
