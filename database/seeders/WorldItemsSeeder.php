<?php

namespace Database\Seeders;

use App\Models\WorldItem;
use Illuminate\Database\Seeder;

class WorldItemsSeeder extends Seeder
{
    /**
     * Seed the application's world item catalog.
     */
    public function run(): void
    {
        $items = [
            ['key' => 'home_base', 'title' => 'Home Base', 'description' => 'Your shared anchor in the world.', 'sort_order' => 1],
            ['key' => 'garden', 'title' => 'Garden', 'description' => 'A space that grows with consistency.', 'sort_order' => 2],
            ['key' => 'repair_bench', 'title' => 'Repair Bench', 'description' => 'Where difficult moments are repaired.', 'sort_order' => 3],
            ['key' => 'memory_chest', 'title' => 'Memory Chest', 'description' => 'Keeps important relationship moments.', 'sort_order' => 4],
            ['key' => 'cozy_corner', 'title' => 'Cozy Corner', 'description' => 'A calm place to reconnect.', 'sort_order' => 5],
            ['key' => 'stargaze_deck', 'title' => 'Stargaze Deck', 'description' => 'A reflective space for future dreams.', 'sort_order' => 6],
            ['key' => 'music_nook', 'title' => 'Music Nook', 'description' => 'A playful station for shared moods.', 'sort_order' => 7],
            ['key' => 'tea_table', 'title' => 'Tea Table', 'description' => 'A gentle reset point for conversations.', 'sort_order' => 8],
        ];

        foreach ($items as $item) {
            WorldItem::query()->updateOrCreate(
                ['key' => $item['key']],
                [
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
