<?php

namespace Database\Seeders;

use App\Models\MissionTemplate;
use Illuminate\Database\Seeder;

class MissionTemplatesSeeder extends Seeder
{
    /**
     * Seed mission template catalog.
     */
    public function run(): void
    {
        $templates = [
            ['key' => 'daily_gratitude', 'title' => 'Daily Gratitude', 'cadence' => 'daily'],
            ['key' => 'daily_kind_word', 'title' => 'Kind Word Exchange', 'cadence' => 'daily'],
            ['key' => 'daily_checkin_reflection', 'title' => 'Reflection Check-in', 'cadence' => 'daily'],
            ['key' => 'weekly_date_planning', 'title' => 'Date Planning', 'cadence' => 'weekly'],
            ['key' => 'weekly_memory_share', 'title' => 'Memory Share', 'cadence' => 'weekly'],
            ['key' => 'repair_conversation', 'title' => 'Repair Conversation', 'cadence' => 'once'],
            ['key' => 'cozy_corner_time', 'title' => 'Cozy Corner Time', 'cadence' => 'weekly'],
            ['key' => 'future_dream_talk', 'title' => 'Future Dream Talk', 'cadence' => 'weekly'],
        ];

        foreach ($templates as $template) {
            MissionTemplate::query()->updateOrCreate(
                ['key' => $template['key']],
                [
                    'title' => $template['title'],
                    'cadence' => $template['cadence'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
