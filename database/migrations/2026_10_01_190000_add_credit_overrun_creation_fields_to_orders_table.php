<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                // Ngày kế toán yêu cầu đại lý phải trả tiền (khi duyệt đơn vượt hạn mức lúc lập đơn)
                if (!Schema::hasColumn('orders', 'credit_overrun_payment_due_date')) {
                    $table->date('credit_overrun_payment_due_date')->nullable()->after('credit_overrun_reason');
                }
                // Ghi chú kế toán gửi cho đại lý khi phê duyệt vượt hạn mức
                if (!Schema::hasColumn('orders', 'credit_overrun_note_for_dealer')) {
                    $table->text('credit_overrun_note_for_dealer')->nullable()->after('credit_overrun_payment_due_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                foreach (['credit_overrun_payment_due_date', 'credit_overrun_note_for_dealer'] as $c) {
                    if (Schema::hasColumn('orders', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
};
