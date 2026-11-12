<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm cột discount_percent vào bảng orders (lưu % chiết khấu đơn hàng)
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('subtotal');
            }
        });

        // 2. Thêm cột discount_percent & discount_amount vào bảng order_items (lưu chiết khấu theo từng dòng sản phẩm)
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('order_items', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percent');
            }
        });

        // 3. Tự động tính toán & cập nhật lại % chiết khấu cho các đơn hàng cũ đã có tiền chiết khấu
        try {
            DB::statement("
                UPDATE orders 
                SET discount_percent = ROUND((discount_amount / subtotal) * 100, 2) 
                WHERE subtotal > 0 AND discount_amount > 0 AND (discount_percent IS NULL OR discount_percent = 0)
            ");
        } catch (\Throwable $e) {
            // Không ngắt migration nếu cập nhật dữ liệu gặp cảnh báo
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'discount_percent')) {
                $table->dropColumn('discount_percent');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('order_items', 'discount_percent')) {
                $colsToDrop[] = 'discount_percent';
            }
            if (Schema::hasColumn('order_items', 'discount_amount')) {
                $colsToDrop[] = 'discount_amount';
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};

