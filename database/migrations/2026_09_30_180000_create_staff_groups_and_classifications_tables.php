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
        // 1. Bảng Phân loại nhóm nhân sự (Phân loại ID)
        Schema::create('staff_group_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // Tên phân loại: Kinh doanh, Kho vận, Giao nhận, Kế toán...
            $table->string('code')->nullable();          // Mã phân loại: KD, KV, GN, KT...
            $table->string('color')->default('blue');    // Màu sắc hiển thị badge: blue, emerald, amber, purple, rose...
            $table->text('description')->nullable();     // Mô tả phân loại
            $table->timestamps();
        });

        // 2. Bảng Nhóm nhân sự
        Schema::create('staff_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                      // Tên nhóm nhân sự
            $table->string('code')->nullable()->unique();                 // Mã nhóm (vd: NNS-001)
            $table->foreignId('classification_id')->nullable()           // Phân loại ID
                  ->constrained('staff_group_classifications')
                  ->nullOnDelete();
            $table->foreignId('user_id')->nullable()                     // user_id (Trưởng nhóm)
                  ->constrained('users')
                  ->nullOnDelete();
            $table->json('user_ids')->nullable();                        // user_ids (Danh sách ID thành viên nhóm)
            $table->text('description')->nullable();                     // Mô tả chức năng, nhiệm vụ nhóm
            $table->string('status')->default('active');                 // Trạng thái: active, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_groups');
        Schema::dropIfExists('staff_group_classifications');
    }
};
