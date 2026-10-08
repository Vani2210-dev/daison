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
        // 1. Categories Table (Loại hàng / Ngành hàng)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // ONG_PVC, PK_PVC, TB_DIEN, TB_NUOC
            $table->string('name');                   // Ống nhựa PVC, Phụ kiện PVC, Thiết bị Điện, Thiết bị Nước
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Brands Table (Thương hiệu / Nhãn hiệu)
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // BINH_MINH, TIEN_PHONG, HOA_SEN, CADIVI, SINO, etc.
            $table->string('name');                   // Bình Minh, Tiền Phong, Hoa Sen, Cadivi, Sino
            $table->string('country')->nullable();     // Việt Nam, Nhật Bản, etc.
            $table->string('logo_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Category - Brand Pivot Table (N - N Relationship)
        Schema::create('category_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->unique(['category_id', 'brand_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_brands');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
