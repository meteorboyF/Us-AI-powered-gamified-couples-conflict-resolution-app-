<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('reminder_daily_checkin_enabled')->default(true)->after('password');
            $table->boolean('reminder_mission_enabled')->default(true)->after('reminder_daily_checkin_enabled');
            $table->boolean('reminder_anniversary_enabled')->default(true)->after('reminder_mission_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_daily_checkin_enabled',
                'reminder_mission_enabled',
                'reminder_anniversary_enabled',
            ]);
        });
    }
};
