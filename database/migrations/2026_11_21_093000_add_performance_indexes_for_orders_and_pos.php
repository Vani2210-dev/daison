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
        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    public function up(): void
    {
        $this->addIndexIfMissing('orders', ['dealer_id', 'status'], 'orders_dealer_status_idx');
        $this->addIndexIfMissing('orders', ['stock_deducted', 'status'], 'orders_stock_deducted_status_idx');
        $this->addIndexIfMissing('consignment_orders', ['stock_deducted', 'status'], 'consignment_orders_stock_deducted_status_idx');
        $this->addIndexIfMissing('products', ['is_active', 'name'], 'products_active_name_idx');
        $this->addIndexIfMissing('warehouses', ['is_active'], 'warehouses_active_idx');
        $this->addIndexIfMissing('dealers', ['name'], 'dealers_name_idx');
        $this->addIndexIfMissing('sales_policies', ['status', 'start_date', 'end_date'], 'sales_policies_status_dates_idx');
        $this->addIndexIfMissing('price_tiers', ['status', 'order_num'], 'price_tiers_status_order_idx');
        $this->addIndexIfMissing('driver_cash_handovers', ['status'], 'driver_cash_handovers_status_idx');
        $this->addIndexIfMissing('delivery_returns', ['status'], 'delivery_returns_status_idx');
    }

    public function down(): void
    {
        $indexes = [
            'orders' => ['orders_dealer_status_idx', 'orders_stock_deducted_status_idx'],
            'consignment_orders' => ['consignment_orders_stock_deducted_status_idx'],
            'products' => ['products_active_name_idx'],
            'warehouses' => ['warehouses_active_idx'],
            'dealers' => ['dealers_name_idx'],
            'sales_policies' => ['sales_policies_status_dates_idx'],
            'price_tiers' => ['price_tiers_status_order_idx'],
            'driver_cash_handovers' => ['driver_cash_handovers_status_idx'],
            'delivery_returns' => ['delivery_returns_status_idx'],
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
