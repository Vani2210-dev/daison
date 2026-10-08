<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

class WarehouseStockSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        if ($warehouses->isEmpty()) {
            return;
        }

        $products = Product::all();
        foreach ($products as $p) {
            $stock = (float)$p->stock_quantity;
            $mq = round($stock * 0.55);
            $mvq = round($stock * 0.25);
            $dxh = round($stock * 0.15);
            $bl = max(0, $stock - $mq - $mvq - $dxh);

            foreach ($warehouses as $wh) {
                $qty = 0;
                if (str_contains($wh->name, 'Mê Linh')) {
                    $qty = $mq;
                } elseif (str_contains($wh->name, 'Vân Quần')) {
                    $qty = $mvq;
                } elseif (str_contains($wh->name, 'Đông Anh')) {
                    $qty = $dxh;
                } else {
                    $qty = $bl;
                }

                WarehouseStock::updateOrCreate(
                    [
                        'product_id' => $p->id,
                        'warehouse_id' => $wh->id
                    ],
                    [
                        'stock_quantity' => $qty,
                        'reserved_quantity' => 0,
                        'location' => $p->location
                    ]
                );
            }
        }
    }
}
