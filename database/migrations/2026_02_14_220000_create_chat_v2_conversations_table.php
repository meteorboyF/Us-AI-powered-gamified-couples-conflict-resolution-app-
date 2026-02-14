<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_v2_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique('couple_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_v2_conversations');
    }
};
