<?php

use App\Services\InventoryAvailabilityService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warehouses') || !Schema::hasTable('warehouse_stocks')) {
            return;
        }

        DB::transaction(function () {
            $warehouses = DB::table('warehouses')
                ->whereNotIn('type', ['Kho hàng ôm', 'Kho xe lưu động'])
                ->orderBy('id')
                ->get();

            $byName = [];
            foreach ($warehouses as $wh) {
                $key = mb_strtolower(trim($wh->name));
                $byName[$key][] = $wh;
            }

            foreach ($byName as $group) {
                if (count($group) < 2) {
                    continue;
                }

                $primary = $group[0];

                for ($i = 1; $i < count($group); $i++) {
                    $dup = $group[$i];

                    $dupStocks = DB::table('warehouse_stocks')
                        ->where('warehouse_id', $dup->id)
                        ->get();

                    foreach ($dupStocks as $dupStock) {
                        $existing = DB::table('warehouse_stocks')
                            ->where('warehouse_id', $primary->id)
                            ->where('product_id', $dupStock->product_id)
                            ->first();

                        if ($existing) {
                            DB::table('warehouse_stocks')
                                ->where('id', $existing->id)
                                ->update([
                                    'stock_quantity' => round((float) $existing->stock_quantity + (float) $dupStock->stock_quantity, 2),
                                    'reserved_quantity' => round((float) $existing->reserved_quantity + (float) $dupStock->reserved_quantity, 2),
                                    'location' => $existing->location ?: $dupStock->location,
                                    'updated_at' => now(),
                                ]);

                            DB::table('warehouse_stocks')->where('id', $dupStock->id)->delete();
                        } else {
                            DB::table('warehouse_stocks')
                                ->where('id', $dupStock->id)
                                ->update([
                                    'warehouse_id' => $primary->id,
                                    'updated_at' => now(),
                                ]);
                        }
                    }

                    // Reassign any foreign keys on remaining inventory/order tables
                    foreach (['inventory_transactions', 'consignment_orders', 'supplier_orders', 'stock_checks'] as $tbl) {
                        if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'warehouse_id')) {
                            DB::table($tbl)->where('warehouse_id', $dup->id)->update(['warehouse_id' => $primary->id]);
                        }
                    }
                    if (Schema::hasTable('delivery_returns') && Schema::hasColumn('delivery_returns', 'intake_warehouse_id')) {
                        DB::table('delivery_returns')->where('intake_warehouse_id', $dup->id)->update(['intake_warehouse_id' => $primary->id]);
                    }

                    DB::table('warehouses')->where('id', $dup->id)->delete();
                }
            }
        });

        InventoryAvailabilityService::clearCache();
    }

    public function down(): void
    {
        // Data deduplication merge is non-reversible
    }
};
