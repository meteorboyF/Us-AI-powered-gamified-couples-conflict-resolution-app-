<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memory_id')->constrained('memories')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['memory_id', 'user_id']);
            $table->index(['memory_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_unlocks');
    }
};
