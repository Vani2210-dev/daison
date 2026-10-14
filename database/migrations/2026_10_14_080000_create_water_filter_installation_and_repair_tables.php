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
        // 1. Kho phụ tùng & phụ kiện máy lọc nước (Độc lập hoàn toàn)
        Schema::create('filter_parts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Mã phụ tùng (vd: LOI-01-PP5M, BOM-24V)');
            $table->string('name')->comment('Tên phụ tùng, linh kiện máy lọc');
            $table->string('category', 100)->default('Lõi lọc')->comment('Phân loại: Lõi lọc thô, Màng RO, Lõi khoáng chức năng, Bơm & Nguồn, Van & Điện từ, Bình áp & Vòi, Cút nối & Dây, Khác');
            $table->string('unit', 30)->default('Cái')->comment('Đơn vị tính: Cái, Chiếc, Bộ, Mét, Cuộn...');
            $table->decimal('in_price', 15, 2)->default(0)->comment('Giá nhập');
            $table->decimal('out_price', 15, 2)->default(0)->comment('Giá bán lẻ / giá xuất thay thế');
            $table->integer('stock_qty')->default(0)->comment('Số lượng tồn kho');
            $table->integer('min_stock_alert')->default(5)->comment('Ngưỡng cảnh báo tồn tối thiểu');
            $table->string('location', 100)->nullable()->comment('Vị trí ngăn/kệ kho phụ tùng');
            $table->text('description')->nullable()->comment('Quy cách, thông số kỹ thuật');
            $table->string('status', 20)->default('active')->comment('active, inactive');
            $table->timestamps();

            $table->index('category');
            $table->index('status');
        });

        // 2. Lịch sử nhập - xuất - hoàn kho phụ tùng
        Schema::create('filter_part_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_id')->index();
            $table->string('type', 30)->comment('import: Nhập kho, export_install: Xuất lắp đặt, export_repair: Xuất sửa chữa, return: Hoàn kho, adjustment: Điều chỉnh kiểm kê');
            $table->integer('quantity')->comment('Số lượng (+ khi nhập/hoàn, - khi xuất)');
            $table->integer('balance_before')->default(0)->comment('Tồn trước giao dịch');
            $table->integer('balance_after')->default(0)->comment('Tồn sau giao dịch');
            $table->decimal('unit_price', 15, 2)->default(0)->comment('Đơn giá');
            $table->decimal('total_price', 15, 2)->default(0)->comment('Thành tiền');
            $table->string('reference_code', 50)->nullable()->comment('Mã phiếu liên quan: LD-..., SC-..., NK-...');
            $table->text('note')->nullable()->comment('Diễn giải');
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('created_by_name')->nullable();
            $table->timestamps();

            $table->foreign('part_id')->references('id')->on('filter_parts')->onDelete('cascade');
        });

        // 3. Đơn điều phối / đi lắp đặt máy lọc nước
        Schema::create('filter_installations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Mã đơn lắp đặt (vd: LD-20261014-001)');
            $table->string('customer_name')->comment('Họ tên khách hàng / Đại lý');
            $table->string('customer_phone', 30)->comment('Số điện thoại liên hệ');
            $table->text('customer_address')->comment('Địa chỉ lắp đặt');
            $table->string('province', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('ward', 100)->nullable();
            $table->string('machine_model')->comment('Tên / Model máy lọc nước cần lắp');
            $table->string('machine_serial', 100)->nullable()->comment('Mã Serial máy');
            $table->string('water_source_type', 100)->nullable()->comment('Nguồn nước cấp: Nước máy, Giếng khoan, Mưa...');
            $table->unsignedBigInteger('technician_id')->nullable()->index()->comment('Kỹ thuật viên phụ trách');
            $table->string('technician_name')->nullable();
            $table->dateTime('appointment_date')->nullable()->comment('Lịch hẹn lắp đặt');
            $table->dateTime('completed_date')->nullable()->comment('Thời gian hoàn tất');
            $table->string('status', 30)->default('pending')->comment('pending: Chờ điều phối, assigned: Đã giao việc, in_progress: Đang lắp, completed: Hoàn thành, cancelled: Đã hủy');
            $table->integer('tds_in')->nullable()->comment('Chỉ số TDS đầu vào (ppm)');
            $table->integer('tds_out')->nullable()->comment('Chỉ số TDS nước sạch sau lọc (ppm)');
            $table->string('pressure', 100)->nullable()->comment('Áp lực nước / tình trạng bơm');
            $table->decimal('service_fee', 15, 2)->default(0)->comment('Phí công lắp đặt');
            $table->decimal('parts_fee', 15, 2)->default(0)->comment('Tiền phụ tùng phụ kiện mang theo');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Tổng tiền thu khách');
            $table->string('payment_status', 30)->default('unpaid')->comment('unpaid: Chưa thu, paid: Đã thu, free: Miễn phí theo máy');
            $table->text('customer_notes')->nullable()->comment('Yêu cầu từ khách hàng');
            $table->text('completion_notes')->nullable()->comment('Ghi chú nghiệm thu của kỹ thuật viên');
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index('status');
            $table->index('appointment_date');
        });

        // 4. Chi tiết phụ tùng/vật tư xuất theo đơn lắp đặt
        Schema::create('filter_installation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installation_id')->index();
            $table->unsignedBigInteger('part_id')->nullable()->index();
            $table->string('part_code', 50)->nullable();
            $table->string('part_name');
            $table->string('unit', 30)->default('Cái');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('installation_id')->references('id')->on('filter_installations')->onDelete('cascade');
        });

        // 5. Phiếu tiếp nhận bảo hành / sửa chữa máy lọc nước
        Schema::create('filter_repairs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Mã phiếu sửa chữa (vd: SC-20261014-001)');
            $table->string('customer_name')->comment('Họ tên khách hàng');
            $table->string('customer_phone', 30)->comment('Số điện thoại');
            $table->text('customer_address')->nullable()->comment('Địa chỉ khách');
            $table->string('repair_type', 30)->default('at_shop')->comment('at_shop: Khách mang tới trạm, at_home: Sửa tận nhà');
            $table->string('machine_model')->comment('Tên / Model máy lọc nước');
            $table->string('machine_serial', 100)->nullable()->comment('Số Serial / IMEI máy');
            $table->string('warranty_type', 30)->default('out_warranty')->comment('in_warranty: Còn bảo hành, out_warranty: Hết bảo hành / tính phí');
            $table->text('symptom')->comment('Mô tả hiện tượng lỗi / khách báo');
            $table->text('diagnosis')->nullable()->comment('Chẩn đoán của kỹ thuật viên');
            $table->text('solution')->nullable()->comment('Phương án khắc phục');
            $table->unsignedBigInteger('technician_id')->nullable()->index()->comment('Kỹ thuật viên phụ trách');
            $table->string('technician_name')->nullable();
            $table->string('status', 30)->default('received')->comment('received: Tiếp nhận, inspecting: Đang kiểm tra, waiting_parts: Chờ linh kiện, repairing: Đang sửa, completed: Đã sửa xong, delivered: Đã trả khách, cancelled: Hủy sửa');
            $table->decimal('labor_fee', 15, 2)->default(0)->comment('Tiền công thợ');
            $table->decimal('parts_fee', 15, 2)->default(0)->comment('Tiền phụ tùng thay thế');
            $table->decimal('discount', 15, 2)->default(0)->comment('Giảm giá / chiết khấu');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Tổng chi phí khách phải trả');
            $table->string('payment_status', 30)->default('unpaid')->comment('unpaid: Chưa thu, paid: Đã thanh toán, warranty_free: Bảo hành miễn phí');
            $table->dateTime('received_at')->nullable()->comment('Ngày giờ tiếp nhận');
            $table->dateTime('completed_at')->nullable()->comment('Ngày giờ sửa xong');
            $table->dateTime('delivered_at')->nullable()->comment('Ngày giờ bàn giao trả máy cho khách');
            $table->date('warranty_until')->nullable()->comment('Thời hạn bảo hành phụ tùng/sửa chữa');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index('status');
            $table->index('warranty_type');
        });

        // 6. Chi tiết phụ tùng thay thế trong phiếu sửa chữa
        Schema::create('filter_repair_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_id')->index();
            $table->unsignedBigInteger('part_id')->nullable()->index();
            $table->string('part_code', 50)->nullable();
            $table->string('part_name');
            $table->string('unit', 30)->default('Cái');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->boolean('is_warranty')->default(false)->comment('Linh kiện này miễn phí bảo hành hay tính tiền');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('repair_id')->references('id')->on('filter_repairs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_repair_items');
        Schema::dropIfExists('filter_repairs');
        Schema::dropIfExists('filter_installation_items');
        Schema::dropIfExists('filter_installations');
        Schema::dropIfExists('filter_part_transactions');
        Schema::dropIfExists('filter_parts');
    }
};
