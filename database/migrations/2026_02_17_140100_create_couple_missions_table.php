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
        Schema::create('couple_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained('couples')->cascadeOnDelete();
            $table->foreignId('mission_template_id')->constrained('mission_templates')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['couple_id', 'mission_template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couple_missions');
    }
};
