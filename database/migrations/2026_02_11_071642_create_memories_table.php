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
        Schema::create('memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('type'); // photo, video, voice_note, text
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // For media files
            $table->string('thumbnail_path')->nullable(); // For videos
            $table->integer('file_size')->nullable(); // In bytes
            $table->string('mime_type')->nullable();
            $table->string('visibility')->default('shared'); // private, shared, locked
            $table->timestamp('locked_at')->nullable();
            $table->json('metadata')->nullable(); // Duration, dimensions, etc.
            $table->timestamps();

            $table->index(['couple_id', 'visibility']);
            $table->index(['couple_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memories');
    }
};
