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
        Schema::create('promotion_advances', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Mã phiếu ứng: UKM-YYYYMMDD-XXXX
            $table->unsignedBigInteger('order_id')->nullable()->index(); // Đơn hàng liên kết
            $table->string('order_code')->nullable()->index();
            $table->unsignedBigInteger('staff_id')->nullable()->index(); // NV kinh doanh
            $table->string('staff_name')->nullable();
            $table->string('customer_name')->nullable();
            
            // Hãng / Nhà sản xuất chịu chương trình KM
            $table->unsignedBigInteger('brand_id')->nullable()->index();
            $table->string('brand_name')->nullable()->index();
            $table->unsignedBigInteger('supplier_id')->nullable()->index();
            $table->string('supplier_name')->nullable();

            // Kho xuất hàng ứng
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();
            $table->string('warehouse_name')->nullable();

            // Hàng hóa khuyến mại ứng
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_sku')->nullable();
            $table->string('product_name');
            $table->string('unit')->default('cái');
            $table->decimal('advance_quantity', 12, 2)->default(0); // Số lượng ứng
            $table->decimal('returned_quantity', 12, 2)->default(0); // Số lượng đã trả bù/hoàn ứng
            $table->decimal('remaining_quantity', 12, 2)->default(0); // Số lượng còn nợ
            $table->decimal('unit_price', 15, 2)->default(0); // Giá trị tham chiếu
            $table->decimal('total_value', 15, 2)->default(0); // Tổng giá trị hàng ứng

            // Loại ứng & Trạng thái
            $table->string('advance_type')->default('warehouse_advance'); // warehouse_advance | company_owes_customer | staff_advance
            $table->string('status')->default('pending'); // pending | partially_settled | settled
            
            $table->timestamp('advance_date')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('promotion_settlement_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_advance_id')->index();
            $table->string('settlement_type')->default('supplier_repaid'); // supplier_repaid | company_repaid | manual_adjustment
            $table->decimal('quantity', 12, 2);
            $table->decimal('remaining_after', 12, 2)->default(0);
            $table->string('reference_code')->nullable(); // Mã phiếu nhập kho hoặc chứng từ liên quan
            $table->unsignedBigInteger('performer_id')->nullable();
            $table->string('performer_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('promotion_advance_id')
                  ->references('id')
                  ->on('promotion_advances')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_settlement_logs');
        Schema::dropIfExists('promotion_advances');
    }
};
