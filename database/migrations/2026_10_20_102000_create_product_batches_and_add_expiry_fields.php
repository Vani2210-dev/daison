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
        // 1. Thêm các trường số lô, NSX, HSD vào bảng chi tiết phiếu kho (inventory_transaction_items)
        Schema::table('inventory_transaction_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transaction_items', 'batch_code')) {
                $table->string('batch_code', 100)->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('inventory_transaction_items', 'manufacturing_date')) {
                $table->date('manufacturing_date')->nullable()->after('batch_code');
            }
            if (!Schema::hasColumn('inventory_transaction_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('manufacturing_date');
            }
        });

        // 2. Tạo bảng product_batches để theo dõi tồn kho theo từng Lô & Hạn sử dụng tại từng kho
        if (!Schema::hasTable('product_batches')) {
            Schema::create('product_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
                $table->foreignId('inventory_transaction_item_id')->nullable()->constrained('inventory_transaction_items')->onDelete('set null');
                $table->string('batch_code', 100)->nullable();
                $table->date('manufacturing_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('import_quantity', 12, 2)->default(0);
                $table->decimal('remaining_quantity', 12, 2)->default(0);
                $table->decimal('cost_price', 15, 2)->default(0);
                $table->string('status', 30)->default('active'); // active, near_expired, expired, out_of_stock
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'warehouse_id']);
                $table->index('expiry_date');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');

        Schema::table('inventory_transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_transaction_items', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
            if (Schema::hasColumn('inventory_transaction_items', 'manufacturing_date')) {
                $table->dropColumn('manufacturing_date');
            }
            if (Schema::hasColumn('inventory_transaction_items', 'batch_code')) {
                $table->dropColumn('batch_code');
            }
        });
    }
};
