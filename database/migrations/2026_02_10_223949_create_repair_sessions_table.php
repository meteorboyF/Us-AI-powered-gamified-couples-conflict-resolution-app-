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
        Schema::create('repair_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained()->onDelete('cascade');
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, in_progress, completed, abandoned
            $table->string('conflict_topic')->nullable();
            $table->text('initiator_perspective')->nullable();
            $table->text('partner_perspective')->nullable();
            $table->json('shared_goals')->nullable(); // Array of selected goals
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['couple_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_sessions');
    }
};
