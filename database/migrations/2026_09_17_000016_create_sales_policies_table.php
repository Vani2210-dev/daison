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
        Schema::create('sales_policies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // CSBH-01
            $table->string('name'); // Tên chính sách bán hàng
            $table->string('policy_type')->default('percentage'); // 'quantity' (mua 10 tặng 1), 'percentage' (chiết khấu %), 'amount' (số tiền)
            
            // Dành cho loại 'quantity' (Số lượng: Mua X tặng Y)
            $table->decimal('buy_qty', 12, 2)->nullable(); // Ví dụ: mua 10
            $table->decimal('free_qty', 12, 2)->nullable(); // Ví dụ: tặng 1
            $table->unsignedBigInteger('free_product_id')->nullable(); // Sản phẩm tặng kèm (nếu null là cùng loại)

            // Dành cho loại 'percentage' (Chiết khấu %)
            $table->decimal('discount_percent', 5, 2)->nullable(); // Ví dụ: 5.5%

            // Dành cho loại 'amount' (Giảm giá tiền mặt)
            $table->decimal('discount_amount', 15, 2)->nullable(); // Ví dụ: 50,000đ

            // Điều kiện bổ trợ
            $table->decimal('min_order_value', 15, 2)->nullable(); // Giá trị đơn hàng tối thiểu

            // Đối tượng áp dụng (Danh sách IDs khách hàng / đại lý)
            // Nếu null hoặc [] thì áp dụng cho TẤT CẢ khách hàng
            $table->json('target_customer_ids')->nullable();

            // Sản phẩm áp dụng (Danh sách IDs sản phẩm)
            // Nếu null hoặc [] thì áp dụng cho TẤT CẢ sản phẩm
            $table->json('target_product_ids')->nullable();

            // Thời gian hiệu lực
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Trạng thái: 'Hoạt động' | 'Tạm ngưng'
            $table->string('status')->default('Hoạt động');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_policies');
    }
};
