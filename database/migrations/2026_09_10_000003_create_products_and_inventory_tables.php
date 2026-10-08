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
        // 1. Warehouses Table (Danh mục kho hàng)
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // KHO-TONG, KHO-D2, KHO-LOI, etc.
            $table->string('name');                   // Kho tổng chính, Kho ống PVC, Kho thiết bị điện
            $table->string('type')->default('Thường'); // Thường, Lỗi hỏng, Lưu động, Kỹ thuật
            $table->string('address')->nullable();
            $table->string('manager_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Products Table (3000+ mã vật tư, giá vốn bình quân liên hoàn & đa phân khúc giá bán JSON)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('restrict');
            $table->string('sku')->unique();          // BM-PVC-D21-C1, TP-PVC-D110-C2, etc.
            $table->string('name');                   // Tên vật tư
            $table->string('subcategory')->nullable();// Class 1, Class 2, Co, Tê, Dây điện, Van
            $table->string('specification')->nullable();// Ø21, Ø110, 2x2.5mm2
            $table->string('unit')->default('cái');    // mét, cái, cuộn, cây, bộ
            
            // Giá nhập & Giá vốn theo Phương án 2 (Bình quân gia quyền liên hoàn)
            $table->decimal('cost_price', 15, 2)->default(0);        // Giá vốn bình quân gia quyền liên hoàn hiện tại
            $table->decimal('latest_cost_price', 15, 2)->default(0); // Giá nhập của lần gần nhất (tham khảo)
            
            // Giá bán đa phân khúc dạng JSON: [{"name": "Đại lý Cấp 1", "price": 45000}, ...]
            $table->json('price_tiers')->nullable();
            
            // Tồn kho & Cảnh báo
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('reserved_quantity', 12, 2)->default(0);
            $table->decimal('min_stock_alert', 12, 2)->default(10);
            $table->string('location')->nullable();   // Khu A-01, Kệ B-04
            $table->string('qr_code')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Inventory Transactions (Phiếu Nhập, Xuất, Kiểm kê, Chuyển kho)
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // PNK-2026-001, PXK-2026-001, PKK-2026-001
            $table->string('type');                   // import, export, audit, transfer
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('target_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('Hoàn thành'); // Bản nháp, Chờ duyệt, Hoàn thành, Đã hủy
            $table->string('supplier_name')->nullable();    // Nhà máy Tiền Phong, Bình Minh, etc.
            $table->string('reference_order_code')->nullable(); // Mã đơn hàng xuất kho (nếu có)
            $table->decimal('total_amount', 15, 2)->default(0); // Tổng giá trị tiền phiếu
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Inventory Transaction Items (Chi tiết sản phẩm trong phiếu kho)
        Schema::create('inventory_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_transaction_id')->constrained('inventory_transactions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 12, 2);
            $table->decimal('import_price', 15, 2)->default(0); // Giá nhập thực tế của lần này (lưu giá lịch sử)
            $table->decimal('cost_price_at_transaction', 15, 2)->default(0); // Giá vốn tại thời điểm giao dịch
            $table->decimal('actual_audit_quantity', 12, 2)->nullable(); // Số kiểm kê thực tế
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transaction_items');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');
    }
};
