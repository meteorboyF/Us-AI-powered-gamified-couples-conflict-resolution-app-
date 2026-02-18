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
        Schema::create('vault_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('media_disk')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_mime')->nullable();
            $table->unsignedBigInteger('media_size')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->string('locked_pin_hash')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['couple_id', 'id']);
            $table->index(['couple_id', 'type']);
            $table->index('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_items');
    }
};
