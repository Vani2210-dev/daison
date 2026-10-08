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
        // 1. Vehicles Table (Quản lý đội xe)
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique(); // 29C-888.99
            $table->string('vehicle_type');           // Xe tải 2.5T, Xe tải 5T, Xe cẩu 8T
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('copilot_name')->nullable(); // Lái phụ
            $table->decimal('capacity_kg', 10, 2)->default(2500); // Tải trọng
            $table->string('status')->default('Sẵn sàng'); // Sẵn sàng, Đang di chuyển, Bảo trì, Tạm ngưng
            $table->integer('km_odometer')->default(0); // Số km đã đi
            $table->date('next_inspection_date')->nullable(); // Hạn đăng kiểm
            $table->date('last_maintenance_date')->nullable();
            $table->timestamps();
        });

        // 2. Delivery Dispatches (Lệnh điều xe giao hàng theo chuyến)
        Schema::create('delivery_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // CX-2026-001
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('Đang chuẩn bị'); // Đang chuẩn bị, Đang giao, Hoàn thành
            $table->dateTime('departure_time')->nullable();
            $table->dateTime('return_time')->nullable();
            $table->decimal('total_weight_kg', 10, 2)->default(0);
            $table->decimal('total_distance_km', 8, 2)->default(0);
            $table->text('route_summary')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Dispatch Orders Pivot (Các đơn hàng trong chuyến xe)
        Schema::create('delivery_dispatch_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_dispatch_id')->constrained('delivery_dispatches')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->integer('delivery_order')->default(1); // Thứ tự giao hàng
            $table->string('status')->default('Chờ giao'); // Chờ giao, Đã giao, Giao thất bại
            $table->timestamps();
        });

        // 4. Fuel Logs (Nhật trình cấp nhiên liệu xăng dầu)
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('driver_name')->nullable();
            $table->date('date');
            $table->string('fuel_type')->default('Dầu Diesel');
            $table->decimal('liters', 8, 2);
            $table->decimal('price_per_liter', 10, 2);
            $table->decimal('total_cost', 15, 2);
            $table->integer('odometer_km');
            $table->string('gas_station')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
        Schema::dropIfExists('delivery_dispatch_orders');
        Schema::dropIfExists('delivery_dispatches');
        Schema::dropIfExists('vehicles');
    }
};
