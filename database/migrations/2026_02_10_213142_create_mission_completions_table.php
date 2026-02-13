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
        Schema::create('mission_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamp('partner_acknowledged_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['mission_assignment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_completions');
    }
};
