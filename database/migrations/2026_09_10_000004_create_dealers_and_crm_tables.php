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
        // 1. Dealers Table (Đại lý & Khách hàng)
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // DL-001, DL-002
            $table->string('name');                   // Đại lý Điện Nước Minh Quang
            $table->string('owner_name');             // Nguyễn Văn Quang
            $table->string('phone');
            $table->string('address');
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('tier')->default('Đại lý Cấp 2'); // Đại lý Cấp 1, Cấp 2, Tiềm năng, Bán Lẻ, Dự Án
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(50000000);  // Hạn mức nợ (VNĐ)
            $table->decimal('current_debt', 15, 2)->default(0);         // Nợ hiện tại (VNĐ)
            $table->integer('payment_term_days')->default(30);          // Hạn thanh toán (ngày)
            $table->integer('overdue_days')->default(0);
            $table->foreignId('sales_rep_id')->nullable()->constrained('users')->onDelete('set null'); // NVTT phụ trách
            $table->string('status')->default('Hoạt động'); // Hoạt động, Cảnh báo nợ, Khóa công nợ
            $table->boolean('truck_accessible')->default(true);
            $table->decimal('storefront_width_meters', 5, 2)->nullable();
            $table->text('expansion_notes')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamps();
        });

        // 2. Dealer Care Logs (Nhật ký chăm sóc & Check-in GPS thực địa)
        Schema::create('dealer_care_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers')->onDelete('cascade');
            $table->foreignId('sales_rep_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('visited_date');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->integer('distance_meters')->default(0); // Khoảng cách GPS thực tế tới đại lý
            $table->boolean('is_within_radius')->default(true); // < 100m là chuẩn
            $table->string('purpose');                // Phát triển mới, Thăm định kỳ, Tư vấn giá, Thu nợ...
            $table->text('marketFeedback')->nullable(); // Phản hồi thị trường PVC, điện nước
            $table->text('competitor_info')->nullable(); // Tình hình đối thủ
            $table->string('next_order_estimate')->nullable(); // Đơn dự kiến tiếp theo
            $table->integer('satisfaction_rating')->default(5); // 1 - 5 sao
            $table->string('photo_url')->nullable();  // Ảnh chụp biển hiệu/quầy kệ
            $table->text('notes')->nullable();
            $table->boolean('kpi_credited')->default(false); // Đã tính điểm KPI
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealer_care_logs');
        Schema::dropIfExists('dealers');
    }
};
