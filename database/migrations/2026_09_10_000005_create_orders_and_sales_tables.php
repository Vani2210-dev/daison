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
        // 1. Orders Table (Đơn hàng Bán buôn & Bán lẻ & Dự án)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();   // DH-2026-0001
            $table->foreignId('dealer_id')->nullable()->constrained('dealers')->onDelete('set null');
            $table->string('customer_name')->nullable(); // Dùng cho khách lẻ hoặc đại lý
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            
            // Nhân sự liên quan trong luồng đơn
            $table->foreignId('sales_rep_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('sales_accountant_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('warehouse_staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('assigned_vehicle_plate')->nullable();

            // Tài chính & Trạng thái đơn
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            
            $table->string('status')->default('Chờ kế toán duyệt'); 
            // Bản nháp, Chờ kế toán duyệt, Đã chuyển Kho, Kho đang soạn, Kho đã xuất đủ, Đang giao hàng, Giao thành công, Đã quyết toán, Đã hủy
            
            $table->string('payment_status')->default('Chưa thanh toán');
            // Chưa thanh toán, Đã cọc, Đã thanh toán QR, Đã thu tiền mặt, Chờ tài xế nộp quỹ
            
            $table->string('payment_method')->default('Ghi nợ đại lý');
            // VietQR, Tiền mặt, Chuyển khoản, Ghi nợ đại lý

            $table->decimal('driver_debt_amount', 15, 2)->default(0); // Tiền mặt tài xế cầm hộ
            $table->boolean('settled_with_accountant')->default(false);

            // Thuộc tính Đơn Hàng Dự Án
            $table->string('order_type')->default('normal'); // normal, project
            $table->string('project_name')->nullable();
            $table->string('contract_number')->nullable();
            $table->string('site_contact_name')->nullable();
            $table->string('site_contact_phone')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Order Items Table (Chi tiết vật tư trong đơn)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->string('sku');
            $table->string('name');
            $table->string('unit')->default('cái');
            $table->decimal('quantity', 12, 2);
            $table->decimal('cost_price', 15, 2)->default(0);       // Giá vốn bình quân tại thời điểm xuất bán (tính lãi gộp)
            $table->decimal('unit_price', 15, 2)->default(0);       // Giá bán thực tế sau phân khúc
            $table->decimal('original_price', 15, 2)->default(0);   // Giá niêm yết
            $table->string('applied_tier_policy')->nullable();      // Tên phân khúc giá áp dụng (Đại lý Cấp 1, Cấp 2...)
            $table->decimal('amount', 15, 2)->default(0);           // Thành tiền
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
