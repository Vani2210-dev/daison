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
        // 1. Task Assignments Table (Giao việc & Báo cáo tiến độ)
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // CV-2026-001
            $table->string('title');                  // Soạn hàng xuất kho, Thăm đại lý...
            $table->text('description')->nullable();
            $table->string('category')->default('Vận hành');
            $table->foreignId('assigned_to_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('deadline');
            $table->string('priority')->default('Trung bình'); // Khẩn cấp, Cao, Trung bình, Thấp
            $table->string('status')->default('Chờ tiếp nhận'); // Chờ tiếp nhận, Đang thực hiện, Chờ duyệt nghiệm thu, Hoàn thành, Từ chối
            $table->integer('progress_percent')->default(0);
            $table->string('related_order_code')->nullable();
            $table->json('checklist')->nullable();     // Danh sách các đầu việc con
            $table->text('feedback')->nullable();
            $table->integer('rating_score')->nullable(); // 1 - 5 sao
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        // 2. Proposals Table (Đề xuất mua sắm, chi hộ, vượt hạn mức)
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // DX-2026-001
            $table->string('title');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->string('department');
            $table->string('type');                   // Mua sắm vật tư, Chi phí xăng dầu, Tăng hạn mức nợ...
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('Chờ duyệt'); // Chờ duyệt, Đã duyệt toàn bộ, Bị từ chối
            $table->integer('current_step')->default(1);
            $table->integer('total_steps')->default(3);
            $table->string('attachment_url')->nullable();
            $table->timestamps();
        });

        // 3. Proposal Steps (Các cấp ký duyệt trong quy trình đề xuất)
        Schema::create('proposal_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->onDelete('cascade');
            $table->integer('step_index');            // 1, 2, 3
            $table->string('role_name');              // Trưởng bộ phận, Kế toán trưởng, Ban Giám Đốc
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('Chờ duyệt'); // Chờ duyệt, Đã duyệt, Từ chối
            $table->dateTime('signed_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // 4. Period Locks Table (Khóa kỳ kế toán kho, sổ quỹ, công nợ)
        Schema::create('period_locks', function (Blueprint $table) {
            $table->id();
            $table->string('ledger_type');            // Sổ kho, Sổ công nợ, Sổ quỹ, Tất cả
            $table->date('from_date');
            $table->date('to_date');
            $table->string('status')->default('Đang khóa'); // Đang khóa, Đã mở
            $table->foreignId('locked_by_id')->constrained('users')->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_locks');
        Schema::dropIfExists('proposal_steps');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('task_assignments');
    }
};
