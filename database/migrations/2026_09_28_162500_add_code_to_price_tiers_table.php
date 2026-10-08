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
        if (Schema::hasTable('price_tiers') && !Schema::hasColumn('price_tiers', 'code')) {
            Schema::table('price_tiers', function (Blueprint $table) {
                $table->string('code', 50)->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('price_tiers') && Schema::hasColumn('price_tiers', 'code')) {
            Schema::table('price_tiers', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};
