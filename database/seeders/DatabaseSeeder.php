<?php

namespace Database\Seeders;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
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
        }
    }
}
