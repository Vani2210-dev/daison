<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Audit Logs
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('timestamp')->nullable();
                $table->string('performer_name')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action'); // 'Sửa Giá', 'Xóa Nợ Lái Xe', 'Khóa Kỳ Sổ', 'Tạo Phiếu', etc.
                $table->string('module')->default('Hệ Thống');
                $table->string('target_document_code')->nullable();
                $table->string('field_name')->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Announcements
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->string('author');
                $table->string('author_role')->nullable();
                $table->string('priority')->default('Bình thường'); // 'Quan trọng', 'Khẩn cấp', 'Bình thường'
                $table->string('target_department')->default('Tất cả');
                $table->json('read_by')->nullable();
                $table->timestamps();
            });
        }

        // 3. Department KPIs
        if (!Schema::hasTable('department_kpis')) {
            Schema::create('department_kpis', function (Blueprint $table) {
                $table->id();
                $table->string('department'); // 'Sales', 'Accounting', 'Warehouse', 'Logistics'
                $table->string('department_name');
                $table->decimal('overall_target_percent', 5, 2)->default(100.0);
                $table->json('metrics')->nullable();
                $table->string('month')->nullable();
                $table->timestamps();
            });
        }

        // 4. Policy Preservations (Bảo lưu giá & chính sách)
        if (!Schema::hasTable('policy_preservations')) {
            Schema::create('policy_preservations', function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable();
                $table->string('customer_id')->nullable();
                $table->string('customer_name');
                $table->string('product_id')->nullable();
                $table->string('product_name');
                $table->decimal('total_deposit_amount', 15, 2)->default(0);
                $table->decimal('used_amount', 15, 2)->default(0);
                $table->decimal('remaining_amount', 15, 2)->default(0);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('status')->default('Còn hiệu lực');
                $table->string('created_by_name')->nullable();
                $table->text('notes')->nullable();
                $table->json('history_logs')->nullable();
                $table->timestamps();
            });
        }

        // 5. Affiliate Referrals
        if (!Schema::hasTable('affiliate_referrals')) {
            Schema::create('affiliate_referrals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('affiliate_code')->nullable();
                $table->string('referrer_name');
                $table->string('referred_customer_name');
                $table->string('referred_customer_phone')->nullable();
                $table->integer('order_count')->default(0);
                $table->decimal('total_revenue', 15, 2)->default(0);
                $table->decimal('kpi_points', 8, 2)->default(0);
                $table->decimal('bonus_vnd', 15, 2)->default(0);
                $table->string('status')->default('Thành công');
                $table->date('date')->nullable();
                $table->timestamps();
            });
        }

        // 6. Vehicle Assignments
        if (!Schema::hasTable('vehicle_assignments')) {
            Schema::create('vehicle_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable(); // 'PC-2026-081'
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->string('vehicle_plate');
                $table->string('driver_name');
                $table->string('driver_phone')->nullable();
                $table->string('copilot_name')->nullable();
                $table->string('copilot_phone')->nullable();
                $table->string('manager_name')->nullable();
                $table->string('manager_phone')->nullable();
                $table->date('assigned_date')->nullable();
                $table->date('effective_from')->nullable();
                $table->string('effective_to')->default('Không thời hạn');
                $table->string('assigned_by')->nullable();
                $table->string('shift')->nullable();
                $table->string('route_id')->nullable();
                $table->string('route_zone')->nullable();
                $table->string('status')->default('Đang áp dụng');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_assignments');
        Schema::dropIfExists('affiliate_referrals');
        Schema::dropIfExists('policy_preservations');
        Schema::dropIfExists('department_kpis');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('audit_logs');
    }
};
