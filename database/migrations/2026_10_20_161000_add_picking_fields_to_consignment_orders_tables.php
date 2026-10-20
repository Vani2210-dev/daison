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
            if (!Schema::hasColumn('consignment_orders', 'picking_date')) {
                $table->date('picking_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('consignment_orders', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->after('picking_date');
            }
            if (!Schema::hasColumn('consignment_orders', 'shortage_reported')) {
                $table->boolean('shortage_reported')->default(false)->after('delivery_date');
            }
            if (!Schema::hasColumn('consignment_orders', 'shortage_reported_at')) {
                $table->string('shortage_reported_at')->nullable()->after('shortage_reported');
            }
            if (!Schema::hasColumn('consignment_orders', 'shortage_reported_note')) {
                $table->text('shortage_reported_note')->nullable()->after('shortage_reported_at');
            }
        });

        Schema::table('consignment_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('consignment_order_items', 'picked_qty')) {
                $table->decimal('picked_qty', 12, 2)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('consignment_order_items', 'item_status')) {
                $table->string('item_status')->default('Chờ bốc')->after('picked_qty');
            }
            if (!Schema::hasColumn('consignment_order_items', 'assigned_staff')) {
                $table->string('assigned_staff')->nullable()->after('item_status');
            }
            if (!Schema::hasColumn('consignment_order_items', 'assigned_staff_list')) {
                $table->json('assigned_staff_list')->nullable()->after('assigned_staff');
            }
            if (!Schema::hasColumn('consignment_order_items', 'shortage_reason')) {
                $table->text('shortage_reason')->nullable()->after('assigned_staff_list');
            }
            if (!Schema::hasColumn('consignment_order_items', 'is_picked')) {
                $table->boolean('is_picked')->default(false)->after('shortage_reason');
            }
            if (!Schema::hasColumn('consignment_order_items', 'is_checked')) {
                $table->boolean('is_checked')->default(false)->after('is_picked');
            }
            if (!Schema::hasColumn('consignment_order_items', 'checked_qty')) {
                $table->decimal('checked_qty', 12, 2)->default(0)->after('is_checked');
            }
            if (!Schema::hasColumn('consignment_order_items', 'checked_by')) {
                $table->string('checked_by')->nullable()->after('checked_qty');
            }
            if (!Schema::hasColumn('consignment_order_items', 'checked_at')) {
                $table->string('checked_at')->nullable()->after('checked_by');
            }
            if (!Schema::hasColumn('consignment_order_items', 'check_notes')) {
                $table->text('check_notes')->nullable()->after('checked_at');
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
                'picking_date',
                'delivery_date',
                'shortage_reported',
                'shortage_reported_at',
                'shortage_reported_note'
            ]);
        });

        Schema::table('consignment_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'picked_qty',
                'item_status',
                'assigned_staff',
                'assigned_staff_list',
                'shortage_reason',
                'is_picked',
                'is_checked',
                'checked_qty',
                'checked_by',
                'checked_at',
                'check_notes',
            ]);
        });
    }
};
