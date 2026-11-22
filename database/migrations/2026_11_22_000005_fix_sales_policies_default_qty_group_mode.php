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
        if (!Schema::hasTable('sales_policies') || !Schema::hasColumn('sales_policies', 'qty_group_mode')) {
            return;
        }

        // Chính sách cấp dòng / toàn bộ SP (không giới hạn >1 SKU cụ thể) từng bị gán mặc định basket_mix
        // dẫn đến cộng dồn số lượng các sản phẩm khác nhau -> chuyển về per_sku.
        DB::table('sales_policies')
            ->where('apply_scope', '!=', 'order')
            ->where('qty_group_mode', 'basket_mix')
            ->where(function ($q) {
                $q->whereNull('target_product_ids')
                    ->orWhereRaw('JSON_LENGTH(target_product_ids) <= 1');
            })
            ->update(['qty_group_mode' => 'per_sku']);

        Schema::table('sales_policies', function (Blueprint $table) {
            $table->string('qty_group_mode', 40)->default('per_sku')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('sales_policies') || !Schema::hasColumn('sales_policies', 'qty_group_mode')) {
            return;
        }

        Schema::table('sales_policies', function (Blueprint $table) {
            $table->string('qty_group_mode', 40)->default('basket_mix')->change();
        });
    }
};
