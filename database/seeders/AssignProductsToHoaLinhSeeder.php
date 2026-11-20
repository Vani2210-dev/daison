<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Services\InventoryAvailabilityService;
use Illuminate\Database\Seeder;

class AssignProductsToHoaLinhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplier = Supplier::where('name', 'LIKE', '%Hoa Linh%')
            ->orWhere('code', 'NCC-HL')
            ->first();

        if (!$supplier) {
            $supplier = Supplier::create([
                'code' => 'NCC-HL',
                'name' => 'CÔNG TY CỔ PHẦN DƯỢC PHẨM HOA LINH',
                'is_active' => true,
            ]);
        } else {
            $supplier->name = 'CÔNG TY CỔ PHẦN DƯỢC PHẨM HOA LINH';
            if (empty($supplier->code)) {
                $supplier->code = 'NCC-HL';
            }
            $supplier->is_active = true;
            $supplier->save();
        }

        $count = Product::query()->update(['supplier_id' => $supplier->id]);

        if (class_exists(InventoryAvailabilityService::class)) {
            InventoryAvailabilityService::clearCache();
        }

        $this->command->info("Đã gán toàn bộ {$count} sản phẩm cho Nhà Cung Cấp: {$supplier->name} (ID: {$supplier->id}, Code: {$supplier->code})");
    }
}
