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
        Schema::table('debt_handovers', function (Blueprint $table) {
            if (!Schema::hasColumn('debt_handovers', 'order_ids')) {
                $table->json('order_ids')->nullable()->after('order_code');
            }
            $table->string('order_code', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debt_handovers', function (Blueprint $table) {
            if (Schema::hasColumn('debt_handovers', 'order_ids')) {
                $table->dropColumn('order_ids');
            }
            $table->string('order_code', 50)->nullable()->change();
        });
    }
};
