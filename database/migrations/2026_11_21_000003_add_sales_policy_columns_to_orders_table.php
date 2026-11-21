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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'sales_policy_id')) {
                $table->unsignedBigInteger('sales_policy_id')->nullable()->after('discount_amount');
            }
            if (!Schema::hasColumn('orders', 'sales_policy_ids')) {
                $table->json('sales_policy_ids')->nullable()->after('sales_policy_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('orders', 'sales_policy_ids')) {
                $colsToDrop[] = 'sales_policy_ids';
            }
            if (Schema::hasColumn('orders', 'sales_policy_id')) {
                $colsToDrop[] = 'sales_policy_id';
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
