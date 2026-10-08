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
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // TUYEN-01
            $table->string('name');            // Tuyến 01: Kho Tổng Mê Linh - Giải Phóng - Trường Chinh
            $table->string('departure_warehouse')->default('Kho Tổng Mê Linh (MQ)');
            $table->json('stops')->nullable(); // ['Mê Linh', 'Giải Phóng', 'Trường Chinh', 'Nguyễn Trãi']
            $table->decimal('distance_km', 8, 2)->default(35);
            $table->decimal('estimated_time_hours', 5, 2)->default(4.0);
            $table->decimal('fuel_liters', 6, 2)->default(6.5);
            $table->decimal('toll_fee', 12, 2)->default(0);
            $table->string('assigned_vehicle_plate')->nullable(); // Biển số xe mặc định gán
            $table->string('assigned_driver_name')->nullable();
            $table->boolean('is_active')->default(true); // Đang hoạt động / Tạm dừng
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_routes');
    }
};
