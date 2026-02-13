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
        Schema::create('repair_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('couple_id')->constrained()->onDelete('cascade');
            $table->text('agreement_text');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('partner_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_agreements');
    }
};
