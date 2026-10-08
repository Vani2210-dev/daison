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
        if (Schema::hasTable('sales_policies') && !Schema::hasColumn('sales_policies', 'apply_scope')) {
            Schema::table('sales_policies', function (Blueprint $table) {
                $table->string('apply_scope')->default('order')->after('policy_type'); // 'order' (áp dụng toàn đơn) hoặc 'product' (áp dụng từng sản phẩm)
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_policies') && Schema::hasColumn('sales_policies', 'apply_scope')) {
            Schema::table('sales_policies', function (Blueprint $table) {
                $table->dropColumn('apply_scope');
            });
        }
    }
};
