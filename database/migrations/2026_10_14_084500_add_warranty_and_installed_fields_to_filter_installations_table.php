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
        Schema::table('filter_installations', function (Blueprint $table) {
            $table->dateTime('installed_at')->nullable()->after('completed_date')->comment('Ngày lắp đặt thực tế / Bàn giao nghiệm thu');
            $table->integer('warranty_months')->default(24)->after('installed_at')->comment('Thời hạn bảo hành (tháng)');
            $table->date('warranty_until')->nullable()->after('warranty_months')->comment('Hạn bảo hành đến ngày');
            $table->string('warranty_card_code', 50)->nullable()->after('warranty_until')->comment('Mã thẻ / Sổ bảo hành');
            $table->text('warranty_notes')->nullable()->after('warranty_card_code')->comment('Ghi chú / Điều kiện bảo hành');
            $table->integer('filter_replacement_months')->default(6)->after('warranty_notes')->comment('Chu kỳ thay lõi lọc định kỳ (tháng)');
            $table->date('next_maintenance_date')->nullable()->after('filter_replacement_months')->comment('Lịch bảo dưỡng / thay lõi tiếp theo');

            $table->index('installed_at');
            $table->index('warranty_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_installations', function (Blueprint $table) {
            $table->dropIndex(['installed_at']);
            $table->dropIndex(['warranty_until']);
            $table->dropColumn([
                'installed_at',
                'warranty_months',
                'warranty_until',
                'warranty_card_code',
                'warranty_notes',
                'filter_replacement_months',
                'next_maintenance_date',
            ]);
        });
    }
};
