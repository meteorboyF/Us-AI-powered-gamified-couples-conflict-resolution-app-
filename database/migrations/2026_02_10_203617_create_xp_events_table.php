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
        Schema::create('xp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // checkin, mission, repair, vault, chat
            $table->integer('xp_amount');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['couple_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xp_events');
    }
};
