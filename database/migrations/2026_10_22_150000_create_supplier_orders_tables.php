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
        // 1. Bảng Nhà Cung Cấp (Suppliers)
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('tax_code')->nullable();
                $table->string('contact_name')->nullable();
                $table->string('address')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_account')->nullable();
                $table->decimal('supplier_debt_to_company', 15, 2)->default(0); // Tiền NCC nợ công ty (do giao thiếu hàng đã thanh toán)
                $table->decimal('company_debt_to_supplier', 15, 2)->default(0); // Tiền công ty nợ NCC
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
            });

            // Seed dữ liệu mẫu cho Nhà Cung Cấp
            DB::table('suppliers')->insert([
                [
                    'code' => 'NCC-TP',
                    'name' => 'Công ty Cổ phần Nhựa Thiếu Niên Tiền Phong',
                    'phone' => '0225 381 3979',
                    'email' => 'contact@nhuatienphong.vn',
                    'tax_code' => '0200115047',
                    'contact_name' => 'Phòng Kinh Doanh Dự Án',
                    'address' => 'Số 2 An Đà, Phường Lạch Tray, Quận Ngô Quyền, Hải Phòng',
                    'bank_name' => 'Vietcombank',
                    'bank_account' => '0011002345678',
                    'supplier_debt_to_company' => 0,
                    'company_debt_to_supplier' => 0,
                    'is_active' => true,
                    'notes' => 'Nhà máy cung cấp ống nhựa uPVC, HDPE, PPR chính hãng',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'NCC-BM',
                    'name' => 'Công ty Cổ phần Nhựa Bình Minh',
                    'phone' => '028 3969 0973',
                    'email' => 'binhminh@binhminhplastic.com.vn',
                    'tax_code' => '0301464823',
                    'contact_name' => 'Đại diện phân phối Miền Bắc',
                    'address' => '240 Hậu Giang, Phường 9, Quận 6, TP.HCM',
                    'bank_name' => 'BIDV',
                    'bank_account' => '12010000456789',
                    'supplier_debt_to_company' => 0,
                    'company_debt_to_supplier' => 0,
                    'is_active' => true,
                    'notes' => 'Nhà sản xuất ống nhựa và phụ kiện xây dựng',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'NCC-HL',
                    'name' => 'Công ty TNHH Dược phẩm Hoa Linh',
                    'phone' => '024 3767 6986',
                    'email' => 'info@duochoalinh.vn',
                    'tax_code' => '0101258671',
                    'contact_name' => 'Chị Mai - Điều phối đơn',
                    'address' => 'B19 TT4 KĐT Mỹ Đình - Mễ Trì, Nam Từ Liêm, Hà Nội',
                    'bank_name' => 'Techcombank',
                    'bank_account' => '19028889998888',
                    'supplier_debt_to_company' => 0,
                    'company_debt_to_supplier' => 0,
                    'is_active' => true,
                    'notes' => 'Cung cấp sản phẩm Dạ Hương, Ngọc Châu',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'NCC-DS',
                    'name' => 'Tổng Kho Vật Tư Công Nghiệp & Thiết Bị Đại Sơn',
                    'phone' => '0912 345 678',
                    'email' => 'vattu@daison.vn',
                    'tax_code' => '0109988776',
                    'contact_name' => 'Anh Hải - Quản lý nhập hàng',
                    'address' => 'Cụm Công nghiệp Ngọc Hồi, Thanh Trì, Hà Nội',
                    'bank_name' => 'MB Bank',
                    'bank_account' => '6868999999999',
                    'supplier_debt_to_company' => 0,
                    'company_debt_to_supplier' => 0,
                    'is_active' => true,
                    'notes' => 'Cung ứng vật tư thiết bị, lõi lọc nước và linh phụ kiện',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Bảng Đơn Đặt Hàng Nhà Cung Cấp (supplier_orders)
        if (!Schema::hasTable('supplier_orders')) {
            Schema::create('supplier_orders', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // ĐHNCC-20261022-0001
                $table->unsignedBigInteger('supplier_id')->index();
                $table->string('supplier_name');
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->string('warehouse_name')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->string('creator_name')->nullable();
                $table->unsignedBigInteger('approver_id')->nullable();
                $table->string('approver_name')->nullable();
                
                $table->date('order_date');
                $table->date('expected_delivery_date')->nullable();
                
                // Trạng thái đơn hàng: Chờ duyệt, Đã duyệt, Đang giao hàng, Nhập một phần, Hoàn thành, Đã hủy
                $table->string('status')->default('Chờ duyệt');
                
                // Trạng thái thanh toán: Chưa thanh toán, Thanh toán 1 phần, Đã thanh toán
                $table->string('payment_status')->default('Chưa thanh toán');
                
                // Tài chính đơn hàng
                $table->decimal('total_items_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('other_fees', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                
                // Trừ nợ từ công nợ NCC nợ công ty
                $table->decimal('debt_deducted_amount', 15, 2)->default(0);
                
                // Số tiền cần phải thanh toán = total_amount - debt_deducted_amount
                $table->decimal('payable_amount', 15, 2)->default(0);
                
                // Số tiền đã thực tế thanh toán
                $table->decimal('paid_amount', 15, 2)->default(0);
                
                $table->string('payment_method')->nullable(); // Tiền mặt, Chuyển khoản, Thẻ
                $table->text('payment_note')->nullable();
                
                // Xử lý khi nhận thiếu hàng:
                // 'supplier_owes_goods' (NCC nợ hàng - giao bù đợt sau)
                // 'supplier_owes_money' (NCC nợ tiền - hoàn vào công nợ NCC)
                $table->string('shortage_resolution')->nullable();
                $table->decimal('shortage_amount', 15, 2)->default(0);
                $table->decimal('refunded_to_supplier_debt', 15, 2)->default(0); // Tiền thừa chuyển vào công nợ NCC
                $table->decimal('received_amount', 15, 2)->default(0); // Tổng giá trị hàng thực nhận
                $table->integer('receipt_count')->default(0); // Số phiếu nhập kho đã tạo
                
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            });
        }

        // 3. Bảng Chi Tiết Sản Phẩm Đặt Hàng NCC (supplier_order_items)
        if (!Schema::hasTable('supplier_order_items')) {
            Schema::create('supplier_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_order_id')->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_name');
                $table->string('sku')->nullable();
                $table->string('unit')->default('cái');
                $table->decimal('ordered_quantity', 12, 2)->default(1);
                $table->decimal('received_quantity', 12, 2)->default(0);
                $table->decimal('remaining_quantity', 12, 2)->default(0);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('amount', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('supplier_order_id')->references('id')->on('supplier_orders')->onDelete('cascade');
            });
        }

        // 4. Bảng Lịch Sử Biến Động Công Nợ NCC (supplier_debt_logs)
        if (!Schema::hasTable('supplier_debt_logs')) {
            Schema::create('supplier_debt_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('supplier_order_id')->nullable()->index();
                $table->string('type'); // 'increase' (NCC nợ thêm do thiếu hàng), 'deduct' (Trừ nợ vào đơn mới), 'refund' (Hoàn tiền)
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_before', 15, 2)->default(0);
                $table->decimal('balance_after', 15, 2)->default(0);
                $table->string('reason')->nullable();
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->string('created_by_name')->nullable();
                $table->timestamps();

                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_debt_logs');
        Schema::dropIfExists('supplier_order_items');
        Schema::dropIfExists('supplier_orders');
        Schema::dropIfExists('suppliers');
    }
};
