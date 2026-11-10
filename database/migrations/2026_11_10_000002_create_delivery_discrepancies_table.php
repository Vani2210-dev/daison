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
        if (!Schema::hasTable('delivery_discrepancies')) {
            Schema::create('delivery_discrepancies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('order_code', 50)->nullable()->index();
                $table->string('vehicle_plate', 30)->nullable()->index();
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->string('driver_name', 100)->nullable();
                $table->unsignedBigInteger('checker_id')->nullable();
                $table->string('checker_name', 100)->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('sku', 100)->nullable();
                $table->string('product_name', 255);
                $table->string('unit', 50)->nullable();
                $table->decimal('expected_qty', 12, 2)->default(0);
                $table->decimal('actual_qty', 12, 2)->default(0);
                $table->decimal('difference_qty', 12, 2)->default(0);
                $table->string('discrepancy_type', 50)->default('shortage'); // shortage (thiếu), excess (thừa), wrong_item (sai hàng), damaged (hư hỏng)
                $table->text('driver_note')->nullable();
                $table->json('images')->nullable();
                $table->text('checker_feedback')->nullable();
                $table->dateTime('checker_feedback_at')->nullable();
                $table->string('checker_feedback_by', 100)->nullable();
                $table->string('status', 50)->default('pending'); // pending (Chờ phản hồi), confirmed (Kiểm hàng xác nhận), rejected (Bác bỏ), resolved (Đã xử lý)
                $table->text('resolution_note')->nullable();
                $table->string('resolved_by', 100)->nullable();
                $table->dateTime('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_discrepancies');
    }
};
