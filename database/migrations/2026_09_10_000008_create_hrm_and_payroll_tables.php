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
        // 1. Attendance Records (Chấm công GPS hàng ngày)
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->string('check_in_address')->nullable();
            $table->string('selfie_url')->nullable();
            $table->string('status')->default('Đúng giờ'); // Đúng giờ, Đi muộn, Về sớm, Vắng mặt
            $table->decimal('work_hours', 4, 2)->default(8.0);
            $table->text('notes')->nullable();
            $table->unique(['user_id', 'date']);
            $table->timestamps();
        });

        // 2. Payroll Records (Bảng lương nhân sự hàng tháng)
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('month');                  // 2026-03
            $table->decimal('base_salary', 15, 2);    // Lương cơ bản
            $table->decimal('work_days', 4, 1)->default(26.0); // Ngày công thực tế
            $table->decimal('kpi_bonus', 15, 2)->default(0);   // Thưởng KPI
            $table->decimal('sales_commission', 15, 2)->default(0); // Hoa hồng doanh số
            $table->decimal('allowances', 15, 2)->default(0); // Phụ cấp (ăn trưa, điện thoại, xăng xe)
            $table->decimal('deductions', 15, 2)->default(0); // Các khoản giảm trừ / phạt
            $table->decimal('net_salary', 15, 2);     // Lương thực nhận (Net)
            $table->string('status')->default('Bản nháp'); // Bản nháp, Chờ duyệt, Đã khóa lương, Đã thanh toán
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->unique(['user_id', 'month']);
        });

        // 3. KPI Evaluations (Đánh giá KPI nhân sự)
        Schema::create('kpi_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('month');                  // 2026-03
            $table->decimal('score_percent', 5, 2);   // 95.5%
            $table->string('grade')->default('A (Tốt)'); // A+ (Xuất sắc), A (Tốt), B (Đạt), C (Cần cố gắng)
            $table->json('metrics_breakdown')->nullable(); // Chi tiết các tiêu chí KPI
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_evaluations');
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('attendance_records');
    }
};
