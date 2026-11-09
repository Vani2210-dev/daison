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
        Schema::table('sales_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_policies', 'gift_tiers')) {
                $table->json('gift_tiers')->nullable()->after('free_product_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_policies', function (Blueprint $table) {
            if (Schema::hasColumn('sales_policies', 'gift_tiers')) {
                $table->dropColumn('gift_tiers');
            }
        });
    }
};
