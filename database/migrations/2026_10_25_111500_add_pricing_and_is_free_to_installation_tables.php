<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filter_installations', function (Blueprint $table) {
            if (!Schema::hasColumn('filter_installations', 'machine_price')) {
                $table->decimal('machine_price', 15, 2)->default(0)->after('machine_serial')->comment('Giá máy lọc nước');
            }
            if (!Schema::hasColumn('filter_installations', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('parts_fee')->comment('Chiết khấu / Giảm giá');
            }
        });

        Schema::table('filter_installation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('filter_installation_items', 'is_free')) {
                $table->boolean('is_free')->default(false)->after('total_price')->comment('0: Tính phí, 1: Miễn phí kèm theo máy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('filter_installations', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('filter_installations', 'machine_price')) $cols[] = 'machine_price';
            if (Schema::hasColumn('filter_installations', 'discount_amount')) $cols[] = 'discount_amount';
            if (!empty($cols)) $table->dropColumn($cols);
        });

        Schema::table('filter_installation_items', function (Blueprint $table) {
            if (Schema::hasColumn('filter_installation_items', 'is_free')) {
                $table->dropColumn('is_free');
            }
        });
    }
};
