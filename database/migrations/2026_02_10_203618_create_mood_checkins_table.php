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
        Schema::create('mood_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('mood_level'); // 1-5
            $table->json('reason_tags')->nullable(); // work, health, family, relationship, random
            $table->json('needs')->nullable(); // space, talk, reassurance, help, affection
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['couple_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mood_checkins');
    }
};
