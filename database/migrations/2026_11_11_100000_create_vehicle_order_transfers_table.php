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
        if (!Schema::hasTable('vehicle_order_transfers')) {
            Schema::create('vehicle_order_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_code', 50)->unique();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('order_code', 50)->index();
                $table->string('customer_name', 255)->nullable();
                $table->string('customer_address', 255)->nullable();
                $table->decimal('order_amount', 15, 2)->default(0);

                // Xe gửi & Người gửi
                $table->string('from_vehicle_plate', 30)->index();
                $table->unsignedBigInteger('from_driver_id')->nullable();
                $table->string('from_driver_name', 100)->nullable();

                // Xe nhận & Người nhận
                $table->string('to_vehicle_plate', 30)->index();
                $table->unsignedBigInteger('to_driver_id')->nullable();
                $table->string('to_driver_name', 100)->nullable();

                // Lý do chuyển
                $table->text('reason');

                // Trạng thái: pending (chờ nhận), accepted (đã tiếp nhận), rejected (từ chối), cancelled (hủy)
                $table->string('status', 50)->default('pending')->index();
                $table->text('rejection_reason')->nullable();

                // Phản hồi từ người nhận
                $table->dateTime('responded_at')->nullable();
                $table->unsignedBigInteger('responded_by_id')->nullable();
                $table->string('responded_by_name', 100)->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_order_transfers');
    }
};

