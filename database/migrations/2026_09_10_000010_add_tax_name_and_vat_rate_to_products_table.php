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
            $table->string('tax_name')->nullable()->after('name'); // Tên thuế / Tên xuất hóa đơn GTGT
            $table->decimal('vat_rate', 5, 2)->default(10.00)->after('price_tiers'); // Thuế suất VAT (%): 0, 5, 8, 10
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['tax_name', 'vat_rate']);
        });
    }
};
