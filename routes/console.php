<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reminders:send-daily', function () {
    $count = app(\App\Services\DailyReminderService::class)->sendForDate(now());

    $this->info("Daily reminders created: {$count}");
})->purpose('Create in-app daily reminder notifications');

Schedule::command('reminders:send-daily')->dailyAt('09:00');
