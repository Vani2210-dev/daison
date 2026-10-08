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
        if (!Schema::hasTable('driver_cash_handovers')) {
            Schema::create('driver_cash_handovers', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // VD: BG-TIEN-20260927-268
                $table->string('vehicle_plate');
                $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('driver_name');
                $table->string('copilot_name')->nullable();
                $table->foreignId('accountant_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('accountant_name');
                $table->decimal('handover_amount', 15, 2)->default(0);
                $table->string('payment_method')->default('Tiền mặt');
                $table->json('order_codes')->nullable();
                $table->integer('order_count')->default(0);
                $table->text('notes')->nullable();
                $table->string('receipt_code')->nullable();
                $table->string('status')->default('Chờ kế toán duyệt'); // 'Chờ kế toán duyệt', 'Đã hoàn thành', 'Đã từ chối'
                $table->timestamp('approved_at')->nullable();
                $table->string('approved_by_name')->nullable();
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('approval_notes')->nullable();
                $table->text('rejected_reason')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->string('rejected_by_name')->nullable();
                $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->json('order_receipt_codes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_cash_handovers');
    }
};
