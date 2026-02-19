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
        Schema::create('gift_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_request_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category');
            $table->string('price_band');
            $table->text('rationale');
            $table->text('personalization_tip')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['gift_request_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_suggestions');
    }
};
