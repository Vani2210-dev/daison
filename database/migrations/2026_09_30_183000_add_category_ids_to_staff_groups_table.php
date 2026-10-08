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
        Schema::table('staff_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_groups', 'category_ids')) {
                $table->json('category_ids')->nullable()->after('code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_groups', function (Blueprint $table) {
            if (Schema::hasColumn('staff_groups', 'category_ids')) {
                $table->dropColumn('category_ids');
            }
        });
    }
};
