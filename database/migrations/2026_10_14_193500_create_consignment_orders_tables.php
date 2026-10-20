<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Đơn Hàng Ôm (Consignment Orders) — NV Sale nhận hàng từ kho để bán lẻ
     */
    public function up(): void
    {
        // 1. Bảng đơn hàng ôm (consignment_orders)
        if (!Schema::hasTable('consignment_orders')) {
            Schema::create('consignment_orders', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique(); // OM-2026-0001

                // NV Sale nhận hàng ôm
                $table->unsignedBigInteger('staff_id')->index();
                $table->string('staff_name', 150);

                // Kho xuất hàng
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('warehouse_name', 150)->nullable();

                // Tài chính
                $table->decimal('total_amount', 15, 2)->default(0);     // Tổng giá trị hàng ôm
                $table->decimal('sold_amount', 15, 2)->default(0);      // Tổng đã bán lẻ
                $table->decimal('returned_amount', 15, 2)->default(0);  // Tổng hàng trả lại
                $table->decimal('remaining_amount', 15, 2)->default(0); // Còn lại chưa quyết toán

                // Trạng thái
                $table->string('status', 50)->default('draft');
                // draft → pending → approved → in_progress → settled / cancelled

                // Duyệt
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->string('approved_by_name', 150)->nullable();
                $table->dateTime('approved_at')->nullable();

                // Từ chối
                $table->text('rejected_reason')->nullable();
                $table->dateTime('rejected_at')->nullable();

                // Quyết toán
                $table->dateTime('settled_at')->nullable();
                $table->unsignedBigInteger('settled_by')->nullable();
                $table->string('settled_by_name', 150)->nullable();

                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->timestamps();

                $table->foreign('staff_id')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // 2. Chi tiết sản phẩm ôm (consignment_order_items)
        if (!Schema::hasTable('consignment_order_items')) {
            Schema::create('consignment_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('consignment_order_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('sku', 100);
                $table->string('name', 255);
                $table->string('unit', 50)->default('cái');

                $table->decimal('quantity', 10, 2)->default(0);           // SL ôm ban đầu
                $table->decimal('unit_price', 15, 2)->default(0);         // Giá ôm (giá NPP/bán buôn)
                $table->decimal('sell_price', 15, 2)->default(0);         // Giá bán lẻ đề xuất
                $table->decimal('amount', 15, 2)->default(0);             // Thành tiền ôm

                $table->decimal('sold_quantity', 10, 2)->default(0);      // Đã bán
                $table->decimal('returned_quantity', 10, 2)->default(0);  // Đã trả lại
                $table->decimal('remaining_quantity', 10, 2)->default(0); // Còn lại

                $table->timestamps();

                $table->foreign('consignment_order_id')
                    ->references('id')->on('consignment_orders')
                    ->onDelete('cascade');
                $table->foreign('product_id')
                    ->references('id')->on('products')
                    ->onDelete('restrict');
            });
        }

        // 3. Đơn bán lẻ từ hàng ôm (consignment_retail_orders)
        if (!Schema::hasTable('consignment_retail_orders')) {
            Schema::create('consignment_retail_orders', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique(); // OL-2026-0001

                $table->unsignedBigInteger('consignment_order_id')->index();
                $table->unsignedBigInteger('staff_id')->index();

                // Thông tin khách hàng lẻ
                $table->string('customer_name', 200)->nullable();
                $table->string('customer_phone', 20)->nullable();
                $table->text('customer_address')->nullable();

                // Tài chính
                $table->decimal('total_amount', 15, 2)->default(0);

                $table->string('payment_method', 50)->default('cash'); // cash, transfer, qr
                $table->string('payment_status', 50)->default('paid'); // unpaid, paid
                $table->string('status', 50)->default('completed');    // completed, cancelled

                $table->text('notes')->nullable();
                $table->dateTime('sold_at')->nullable();
                $table->timestamps();

                $table->foreign('consignment_order_id')
                    ->references('id')->on('consignment_orders')
                    ->onDelete('cascade');
                $table->foreign('staff_id')
                    ->references('id')->on('users')
                    ->onDelete('restrict');
            });
        }

        // 4. Chi tiết đơn bán lẻ (consignment_retail_order_items)
        if (!Schema::hasTable('consignment_retail_order_items')) {
            Schema::create('consignment_retail_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('consignment_retail_order_id')->index();
                $table->unsignedBigInteger('consignment_order_item_id')->nullable()->index(); // Dòng SP ôm gốc
                $table->unsignedBigInteger('product_id')->index();
                $table->string('sku', 100);
                $table->string('name', 255);
                $table->string('unit', 50)->default('cái');

                $table->decimal('quantity', 10, 2)->default(0);    // SL bán
                $table->decimal('unit_price', 15, 2)->default(0);  // Giá bán lẻ thực tế
                $table->decimal('amount', 15, 2)->default(0);      // Thành tiền

                $table->timestamps();

                $table->foreign('consignment_retail_order_id', 'cro_items_retail_order_fk')
                    ->references('id')->on('consignment_retail_orders')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_retail_order_items');
        Schema::dropIfExists('consignment_retail_orders');
        Schema::dropIfExists('consignment_order_items');
        Schema::dropIfExists('consignment_orders');
    }
};
