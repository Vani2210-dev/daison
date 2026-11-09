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
                if (!Schema::hasColumn('filter_installations', 'dealer_id')) {
                    $table->unsignedBigInteger('dealer_id')->nullable()->index()->after('id');
                }
            });
        }

        if (Schema::hasTable('filter_installed_machines')) {
            Schema::table('filter_installed_machines', function (Blueprint $table) {
                if (!Schema::hasColumn('filter_installed_machines', 'dealer_id')) {
                    $table->unsignedBigInteger('dealer_id')->nullable()->index()->after('installation_id');
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
                if (Schema::hasColumn('filter_installations', 'dealer_id')) {
                    $table->dropColumn('dealer_id');
                }
            });
        }

        if (Schema::hasTable('filter_installed_machines')) {
            Schema::table('filter_installed_machines', function (Blueprint $table) {
                if (Schema::hasColumn('filter_installed_machines', 'dealer_id')) {
                    $table->dropColumn('dealer_id');
                }
            });
        }
    }
};

