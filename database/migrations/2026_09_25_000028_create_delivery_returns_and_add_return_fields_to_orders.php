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
        // 1. Thêm các trường hoàn trả vào bảng orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'has_returns')) {
                $table->boolean('has_returns')->default(false)->after('driver_debt_amount');
            }
            if (!Schema::hasColumn('orders', 'total_returned_amount')) {
                $table->decimal('total_returned_amount', 15, 2)->default(0)->after('has_returns');
            }
            if (!Schema::hasColumn('orders', 'return_slip_code')) {
                $table->string('return_slip_code')->nullable()->after('total_returned_amount');
            }
        });

        // 2. Thêm các trường hoàn trả vào bảng order_items
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'returned_qty')) {
                $table->decimal('returned_qty', 12, 2)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('order_items', 'delivered_qty')) {
                $table->decimal('delivered_qty', 12, 2)->nullable()->after('returned_qty');
            }
            if (!Schema::hasColumn('order_items', 'return_reason')) {
                $table->string('return_reason')->nullable()->after('delivered_qty');
            }
        });

        // 3. Tạo bảng delivery_returns (Phiếu hoàn trả hàng khi đi giao)
        if (!Schema::hasTable('delivery_returns')) {
            Schema::create('delivery_returns', function (Blueprint $table) {
                $table->id();
                $table->string('slip_code')->unique(); // VD: TH-DH-2026-1088
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->string('order_code');
                $table->foreignId('dealer_id')->nullable()->constrained('dealers')->onDelete('set null');
                $table->string('dealer_name');
                $table->string('vehicle_plate');
                $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('driver_name');
                $table->string('copilot_name')->nullable();
                $table->decimal('total_returned_amount', 15, 2)->default(0);
                $table->string('status')->default('Chở trên xe'); // 'Chở trên xe', 'Đã nhập kho lại', 'Đã hủy'
                $table->text('notes')->nullable();
                $table->foreignId('intake_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
                $table->foreignId('intake_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('intake_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Tạo bảng delivery_return_items (Chi tiết từng mặt hàng hoàn trả)
        if (!Schema::hasTable('delivery_return_items')) {
            Schema::create('delivery_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_return_id')->constrained('delivery_returns')->onDelete('cascade');
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('set null');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->string('sku');
                $table->string('name');
                $table->string('unit')->default('cái');
                $table->decimal('returned_qty', 12, 2)->default(0);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_return_items');
        Schema::dropIfExists('delivery_returns');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['returned_qty', 'delivered_qty', 'return_reason']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['has_returns', 'total_returned_amount', 'return_slip_code']);
        });
    }
};
