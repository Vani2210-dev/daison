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
        Schema::table('filter_machine_models', function (Blueprint $table) {
            if (!Schema::hasColumn('filter_machine_models', 'default_parts')) {
                $table->json('default_parts')->nullable()->after('description')->comment('Danh sách phụ tùng linh kiện mặc định đi kèm model máy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_machine_models', function (Blueprint $table) {
            if (Schema::hasColumn('filter_machine_models', 'default_parts')) {
                $table->dropColumn('default_parts');
            }
        });
    }
};
