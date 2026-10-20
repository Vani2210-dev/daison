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
        Schema::table('consignment_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('consignment_orders', 'stock_deducted')) {
                $table->boolean('stock_deducted')->default(false)->after('status');
            }
            if (!Schema::hasColumn('consignment_orders', 'dispatched_at')) {
                $table->dateTime('dispatched_at')->nullable()->after('stock_deducted');
            }
            if (!Schema::hasColumn('consignment_orders', 'dispatched_by')) {
                $table->unsignedBigInteger('dispatched_by')->nullable()->after('dispatched_at');
            }
            if (!Schema::hasColumn('consignment_orders', 'dispatched_by_name')) {
                $table->string('dispatched_by_name')->nullable()->after('dispatched_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consignment_orders', function (Blueprint $table) {
            $table->dropColumn([
                'stock_deducted',
                'dispatched_at',
                'dispatched_by',
                'dispatched_by_name',
            ]);
        });
    }
};
