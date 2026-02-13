<?php

use Database\Seeders\DemoSeeder;
use Database\Seeders\MissionSeeder;
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

Artisan::command('us:seed-demo', function () {
    $this->components->info('Seeding demo data for Us...');

    $this->call('db:seed', ['--class' => MissionSeeder::class, '--force' => true]);
    $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

    $this->newLine();
    $this->components->info('Demo data seeded successfully.');
    $this->line('Login credentials (demo-only):');

    foreach (DemoSeeder::demoCredentials() as $credential) {
        $this->line(sprintf(
            ' - %s: %s / %s',
            $credential['label'],
            $credential['email'],
            $credential['password']
        ));
    }
})->purpose('Seed deterministic demo couples with rich historical data');
