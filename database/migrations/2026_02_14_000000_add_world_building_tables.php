<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            $table->string('world_type')->default('garden')->after('theme_type');
        });

        DB::table('worlds')
            ->select(['id', 'theme_type'])
            ->orderBy('id')
            ->chunkById(100, function ($worlds) {
                foreach ($worlds as $world) {
                    DB::table('worlds')
                        ->where('id', $world->id)
                        ->update(['world_type' => $world->theme_type ?: 'garden']);
                }
            });

        Schema::create('couple_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->unique()->constrained()->onDelete('cascade');
            $table->unsignedInteger('love_seeds_balance')->default(0);
            $table->timestamps();
        });

        Schema::create('world_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained()->onDelete('cascade');
            $table->string('world_type');
            $table->string('item_key');
            $table->unsignedTinyInteger('level')->default(0);
            $table->string('slot')->nullable();
            $table->json('position')->nullable();
            $table->boolean('is_built')->default(false);
            $table->timestamps();

            $table->unique(['couple_id', 'item_key']);
            $table->index('couple_id');
            $table->index('world_type');
            $table->index(['couple_id', 'world_type']);
            $table->index(['couple_id', 'slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_items');
        Schema::dropIfExists('couple_wallets');

        Schema::table('worlds', function (Blueprint $table) {
            $table->dropColumn('world_type');
        });
    }
};
