<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_v2_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_v2_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20)->default('text');
            $table->text('body')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_mime')->nullable();
            $table->unsignedBigInteger('media_size')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->foreignId('reply_to_message_id')->nullable()->constrained('chat_v2_messages')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
            $table->index(['sender_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_v2_messages');
    }
};
