<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('memories', 'comfort')) {
            Schema::table('memories', function (Blueprint $table) {
                $table->boolean('comfort')->default(false)->after('visibility');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('memories', 'comfort')) {
            Schema::table('memories', function (Blueprint $table) {
                $table->dropColumn('comfort');
            });
        }
    }
};
