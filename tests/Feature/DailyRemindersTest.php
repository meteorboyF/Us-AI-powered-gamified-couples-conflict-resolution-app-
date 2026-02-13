<?php

namespace Tests\Feature;

use App\Models\CoupleDate;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\Notification;
use App\Models\User;
use App\Services\CoupleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DailyRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_reminder_command_creates_records_for_eligible_users(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 14, 9, 0, 0));
        try {
            $user = User::factory()->create();
            $partner = User::factory()->create();
            $coupleService = app(CoupleService::class);
            $couple = $coupleService->createCouple($user);
            $coupleService->joinCouple($partner, $couple->invite_code);

            $mission = Mission::create([
                'title' => 'Daily Support',
                'description' => 'Check in',
                'type' => 'daily',
                'xp_reward' => 10,
                'is_active' => true,
            ]);

            MissionAssignment::create([
                'couple_id' => $couple->id,
                'mission_id' => $mission->id,
                'assigned_for_date' => today()->toDateString(),
                'status' => 'pending',
            ]);

            CoupleDate::create([
                'couple_id' => $couple->id,
                'created_by' => $user->id,
                'title' => 'Our Anniversary',
                'event_date' => Carbon::create(2024, 2, 14)->toDateString(),
                'is_anniversary' => true,
            ]);

            Artisan::call('reminders:send-daily');

            $this->assertDatabaseCount('notifications', 6);
            $this->assertDatabaseHas('notifications', [
                'user_id' => $user->id,
                'type' => 'daily_checkin_reminder',
            ]);
            $this->assertDatabaseHas('notifications', [
                'user_id' => $user->id,
                'type' => 'mission_reminder',
            ]);
            $this->assertDatabaseHas('notifications', [
                'user_id' => $user->id,
                'type' => 'anniversary_reminder',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_opt_out_prevents_daily_reminders(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 14, 9, 0, 0));
        try {
            $user = User::factory()->create([
                'reminder_daily_checkin_enabled' => false,
                'reminder_mission_enabled' => false,
                'reminder_anniversary_enabled' => false,
            ]);
            $partner = User::factory()->create();
            $coupleService = app(CoupleService::class);
            $couple = $coupleService->createCouple($user);
            $coupleService->joinCouple($partner, $couple->invite_code);

            $mission = Mission::create([
                'title' => 'Daily Support',
                'description' => 'Check in',
                'type' => 'daily',
                'xp_reward' => 10,
                'is_active' => true,
            ]);

            MissionAssignment::create([
                'couple_id' => $couple->id,
                'mission_id' => $mission->id,
                'assigned_for_date' => today()->toDateString(),
                'status' => 'pending',
            ]);

            CoupleDate::create([
                'couple_id' => $couple->id,
                'created_by' => $partner->id,
                'title' => 'Our Anniversary',
                'event_date' => Carbon::create(2020, 2, 14)->toDateString(),
                'is_anniversary' => true,
            ]);

            Artisan::call('reminders:send-daily');
            Artisan::call('reminders:send-daily');

            $this->assertSame(0, Notification::where('user_id', $user->id)->count());
            $this->assertSame(3, Notification::where('user_id', $partner->id)->count());
        } finally {
            Carbon::setTestNow();
        }
    }
}
