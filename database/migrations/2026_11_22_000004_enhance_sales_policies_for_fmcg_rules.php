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
        if (!Schema::hasTable('sales_policies')) {
            return;
        }

        Schema::table('sales_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_policies', 'target_customer_tiers')) {
                $table->json('target_customer_tiers')->nullable()->after('target_customer_ids');
            }
            if (!Schema::hasColumn('sales_policies', 'qty_group_mode')) {
                $table->string('qty_group_mode', 40)->default('basket_mix')->after('apply_scope');
            }
            if (!Schema::hasColumn('sales_policies', 'discount_tiers')) {
                $table->json('discount_tiers')->nullable()->after('discount_amount');
            }
            if (!Schema::hasColumn('sales_policies', 'conflict_group')) {
                $table->string('conflict_group', 80)->nullable()->after('qty_group_mode');
            }
            if (!Schema::hasColumn('sales_policies', 'stacking_mode')) {
                $table->string('stacking_mode', 40)->default('exclusive_line')->after('conflict_group');
            }
            if (!Schema::hasColumn('sales_policies', 'require_original_price')) {
                $table->boolean('require_original_price')->default(true)->after('stacking_mode');
            }
            if (!Schema::hasColumn('sales_policies', 'priority')) {
                $table->integer('priority')->default(100)->after('require_original_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('sales_policies')) {
            return;
        }

        Schema::table('sales_policies', function (Blueprint $table) {
            $cols = [
                'target_customer_tiers',
                'qty_group_mode',
                'discount_tiers',
                'conflict_group',
                'stacking_mode',
                'require_original_price',
                'priority',
            ];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('sales_policies', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
