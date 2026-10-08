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
        Schema::table('products', function (Blueprint $table) {
            $table->string('conversion_unit')->nullable()->after('unit'); // Đơn vị chuyển đổi (vd: "mét", "hộp", "thùng")
            $table->decimal('conversion_rate', 10, 4)->nullable()->after('conversion_unit'); // Hệ số quy đổi (vd: 1 cây/ống = 4 mét)
            $table->decimal('conversion_price', 15, 2)->nullable()->after('conversion_rate'); // Giá bán theo đơn vị chuyển đổi (vd: giá theo mét)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'conversion_unit',
                'conversion_rate',
                'conversion_price',
            ]);
        });
    }
};
