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
            $cols = [];
            if (Schema::hasColumn('filter_installations', 'tds_in')) $cols[] = 'tds_in';
            if (Schema::hasColumn('filter_installations', 'tds_out')) $cols[] = 'tds_out';
            if (Schema::hasColumn('filter_installations', 'pressure')) $cols[] = 'pressure';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('filter_installed_machines', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('filter_installed_machines', 'tds_in')) $cols[] = 'tds_in';
            if (Schema::hasColumn('filter_installed_machines', 'tds_out')) $cols[] = 'tds_out';
            if (Schema::hasColumn('filter_installed_machines', 'pressure')) $cols[] = 'pressure';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_installations', function (Blueprint $table) {
            $table->integer('tds_in')->nullable();
            $table->integer('tds_out')->nullable();
            $table->string('pressure', 100)->nullable();
        });

        Schema::table('filter_installed_machines', function (Blueprint $table) {
            $table->integer('tds_in')->nullable();
            $table->integer('tds_out')->nullable();
            $table->string('pressure', 100)->nullable();
        });
    }
};
