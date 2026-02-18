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
        Schema::create('couple_world_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->unique()->constrained('couples')->cascadeOnDelete();
            $table->string('vibe')->default('neutral');
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('xp')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couple_world_states');
    }
};
