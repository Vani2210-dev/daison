<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($tableName) || Schema::hasIndex($tableName, $indexName)) {
            return;
        }
        foreach ($columns as $col) {
            if (!Schema::hasColumn($tableName, $col)) {
                return;
            }
        }
        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    public function up(): void
    {
        $this->addIndexIfMissing('dealers', ['status'], 'dealers_status_idx');
        $this->addIndexIfMissing('order_items', ['sku'], 'order_items_sku_idx');
        $this->addIndexIfMissing('orders', ['sales_policy_id'], 'orders_sales_policy_idx');
        $this->addIndexIfMissing('orders', ['assigned_vehicle_plate', 'status'], 'orders_vehicle_status_idx');
        $this->addIndexIfMissing('picking_sync_events', ['order_id', 'id'], 'picking_sync_events_order_id_idx');
        $this->addIndexIfMissing('system_notifications', ['reference_id'], 'system_notifications_ref_idx');
        $this->addIndexIfMissing('system_notifications', ['user_id', 'is_read'], 'system_notifications_user_read_idx');
        $this->addIndexIfMissing('supplier_orders', ['status', 'order_date'], 'supplier_orders_status_date_idx');
        $this->addIndexIfMissing('consignment_orders', ['staff_id', 'status'], 'consignment_orders_staff_status_idx');
        $this->addIndexIfMissing('users', ['dealer_id'], 'users_dealer_id_idx');
    }

    public function down(): void
    {
        $indexes = [
            'dealers' => ['dealers_status_idx'],
            'order_items' => ['order_items_sku_idx'],
            'orders' => ['orders_sales_policy_idx', 'orders_vehicle_status_idx'],
            'picking_sync_events' => ['picking_sync_events_order_id_idx'],
            'system_notifications' => ['system_notifications_ref_idx', 'system_notifications_user_read_idx'],
            'supplier_orders' => ['supplier_orders_status_date_idx'],
            'consignment_orders' => ['consignment_orders_staff_status_idx'],
            'users' => ['users_dealer_id_idx'],
        ];

        foreach ($indexes as $tableName => $indexNames) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexNames) {
                foreach ($indexNames as $indexName) {
                    if (Schema::hasIndex($tableName, $indexName)) {
                        $table->dropIndex($indexName);
                    }
                }
            });
        }
    }
};
