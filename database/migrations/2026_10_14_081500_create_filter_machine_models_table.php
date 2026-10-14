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
        // 1. Danh sách model máy lọc nước (độc lập)
        Schema::create('filter_machine_models', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Mã model (vd: KG100HA, KRF-OP1319)');
            $table->string('name')->comment('Tên model máy lọc nước');
            $table->string('brand', 100)->nullable()->comment('Hãng/Thương hiệu: Kangaroo, Karofi, Sunhouse, AO Smith, Panasonic...');
            $table->integer('filter_stages')->default(10)->comment('Số cấp lọc (lõi): 8, 9, 10, 11...');
            $table->string('type', 100)->default('Tủ đứng')->comment('Loại: Tủ đứng, Để bàn, Lắp gầm chậu, Nóng Lạnh RO...');
            $table->decimal('standard_price', 15, 2)->default(0)->comment('Giá bán tham khảo');
            $table->text('description')->nullable()->comment('Đặc tính kỹ thuật');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('brand');
            $table->index('is_active');
        });

        // 2. Thêm machine_model_id vào bảng filter_installations nếu cần liên kết
        Schema::table('filter_installations', function (Blueprint $table) {
            $table->unsignedBigInteger('machine_model_id')->nullable()->after('machine_model')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_installations', function (Blueprint $table) {
            $table->dropColumn('machine_model_id');
        });
        Schema::dropIfExists('filter_machine_models');
    }
};
