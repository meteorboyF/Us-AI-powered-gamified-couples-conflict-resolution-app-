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
        Schema::create('mission_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained()->onDelete('cascade');
            $table->foreignId('mission_id')->constrained()->onDelete('cascade');
            $table->date('assigned_for_date');
            $table->enum('status', ['pending', 'completed', 'expired'])->default('pending');
            $table->timestamps();

            // Custom short name to avoid MySQL 64-char limit
            $table->unique(['couple_id', 'mission_id', 'assigned_for_date'], 'ma_unique');
            $table->index(['couple_id', 'assigned_for_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_assignments');
    }
};
