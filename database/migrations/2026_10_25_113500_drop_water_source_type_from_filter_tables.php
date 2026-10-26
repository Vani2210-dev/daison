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
                if (Schema::hasColumn('filter_installations', 'water_source_type')) {
                    $table->dropColumn('water_source_type');
                }
            });
        }

        if (Schema::hasTable('filter_installed_machines')) {
            Schema::table('filter_installed_machines', function (Blueprint $table) {
                if (Schema::hasColumn('filter_installed_machines', 'water_source_type')) {
                    $table->dropColumn('water_source_type');
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
                if (!Schema::hasColumn('filter_installations', 'water_source_type')) {
                    $table->string('water_source_type', 100)->nullable()->after('machine_serial');
                }
            });
        }

        if (Schema::hasTable('filter_installed_machines')) {
            Schema::table('filter_installed_machines', function (Blueprint $table) {
                if (!Schema::hasColumn('filter_installed_machines', 'water_source_type')) {
                    $table->string('water_source_type', 100)->nullable()->after('customer_address');
                }
            });
        }
    }
};
