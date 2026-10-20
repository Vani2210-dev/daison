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
        Schema::table('filter_installed_machines', function (Blueprint $table) {
            $table->text('accessories')->nullable()->after('pressure')->comment('Danh sách phụ kiện kèm theo máy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_installed_machines', function (Blueprint $table) {
            $table->dropColumn('accessories');
        });
    }
};
