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
            if (Schema::hasColumn('filter_installations', 'service_fee')) {
                $table->dropColumn('service_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_installations', function (Blueprint $table) {
            if (!Schema::hasColumn('filter_installations', 'service_fee')) {
                $table->decimal('service_fee', 15, 2)->default(0)->comment('Phí công lắp đặt');
            }
        });
    }
};
