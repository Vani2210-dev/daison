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
        if (Schema::hasTable('filter_installations')) {
            Schema::table('filter_installations', function (Blueprint $table) {
                if (!Schema::hasColumn('filter_installations', 'surcharge_amount')) {
                    $table->decimal('surcharge_amount', 15, 2)->default(0)->after('parts_fee');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('filter_installations')) {
            Schema::table('filter_installations', function (Blueprint $table) {
                if (Schema::hasColumn('filter_installations', 'surcharge_amount')) {
                    $table->dropColumn('surcharge_amount');
                }
            });
        }
    }
};
