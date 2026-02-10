<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('worlds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->unique()->constrained()->onDelete('cascade');
            $table->string('theme_type')->default('garden'); // garden, house, kitchen, farm
            $table->integer('level')->default(1);
            $table->integer('xp_total')->default(0);
            $table->string('ambience_state')->default('bright'); // bright, calm, quiet
            $table->json('cosmetics')->nullable(); // unlocked cosmetics
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worlds');
    }
};
