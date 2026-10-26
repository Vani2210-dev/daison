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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'parent_order_id')) {
                $table->unsignedBigInteger('parent_order_id')->nullable()->index()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'parent_order_code')) {
                $table->string('parent_order_code')->nullable()->index()->after('parent_order_id');
            }
            if (!Schema::hasColumn('orders', 'backorder_status')) {
                $table->string('backorder_status')->nullable()->index()->after('parent_order_code'); // pending_stock, ready_to_deliver, delivered
            }
            if (!Schema::hasColumn('orders', 'has_shortage_warning')) {
                $table->boolean('has_shortage_warning')->default(false)->after('backorder_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['parent_order_id', 'parent_order_code', 'backorder_status', 'has_shortage_warning']);
        });
    }
};
