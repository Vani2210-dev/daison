<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FakeGoodsReceiptWithBatchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin?->id ?? 1;

        $warehouses = Warehouse::where('is_active', true)->get();
        if ($warehouses->isEmpty()) {
            $warehouses = Warehouse::all();
        }
        $whMain = $warehouses->first();
        $whSecond = $warehouses->skip(1)->first() ?? $whMain;

        $products = Product::where('is_active', true)->get();
        if ($products->isEmpty()) {
            $products = Product::all();
        }

        if ($products->isEmpty()) {
            $this->command->warn('Không tìm thấy sản phẩm nào để tạo phiếu nhập!');
            return;
        }

        // Chọn một số sản phẩm tiêu biểu
        $sampleProducts = $products->take(12);

        $now = Carbon::now();

        // Danh sách kịch bản phiếu nhập với các tình trạng Date khác nhau
        $receiptTemplates = [
            [
                'code' => 'PNK-2610-001',
                'warehouse_id' => $whMain->id,
                'supplier_name' => 'Tập đoàn Karofi Việt Nam - Nhà máy Lọc nước',
                'reference_order_code' => 'HĐ-KR-99812',
                'notes' => 'Lô hàng linh kiện lõi lọc nước chính hãng và màng lọc thẩm thấu ngược RO tiêu chuẩn',
                'created_at' => $now->copy()->subDays(15),
                'items' => [
                    [
                        'product_index' => 0,
                        'batch_code' => 'LOT-KR26-081',
                        'mfg_days_ago' => 60,
                        'exp_days_ahead' => 730, // HSD 2 năm (còn hạn tốt)
                        'qty' => 150,
                        'price' => 120000,
                    ],
                    [
                        'product_index' => 1,
                        'batch_code' => 'LOT-KR26-082',
                        'mfg_days_ago' => 45,
                        'exp_days_ahead' => 540, // HSD 1.5 năm (còn hạn tốt)
                        'qty' => 80,
                        'price' => 250000,
                    ],
                    [
                        'product_index' => 2,
                        'batch_code' => 'LOT-KR25-015',
                        'mfg_days_ago' => 500,
                        'exp_days_ahead' => 40, // CẬN DATE (Còn 40 ngày)
                        'qty' => 25,
                        'price' => 95000,
                    ],
                ]
            ],
            [
                'code' => 'PNK-2610-002',
                'warehouse_id' => $whSecond->id,
                'supplier_name' => 'Công ty CP Tập đoàn Kangaroo',
                'reference_order_code' => 'HĐ-KG-55219',
                'notes' => 'Nhập bổ sung vật tư phụ kiện lọc nước, lõi khoáng gốm và bơm cao áp',
                'created_at' => $now->copy()->subDays(10),
                'items' => [
                    [
                        'product_index' => 3 % $sampleProducts->count(),
                        'batch_code' => 'LOT-KG-26A',
                        'mfg_days_ago' => 30,
                        'exp_days_ahead' => 365, // HSD 1 năm (tốt)
                        'qty' => 120,
                        'price' => 180000,
                    ],
                    [
                        'product_index' => 4 % $sampleProducts->count(),
                        'batch_code' => 'LOT-KG-24X',
                        'mfg_days_ago' => 700,
                        'exp_days_ahead' => -15, // ĐÃ HẾT HẠN (Quá hạn 15 ngày)
                        'qty' => 10,
                        'price' => 110000,
                    ],
                    [
                        'product_index' => 5 % $sampleProducts->count(),
                        'batch_code' => 'LOT-KG-25M',
                        'mfg_days_ago' => 330,
                        'exp_days_ahead' => 35, // CẬN DATE (Còn 35 ngày)
                        'qty' => 40,
                        'price' => 75000,
                    ],
                ]
            ],
            [
                'code' => 'PNK-2610-003',
                'warehouse_id' => $whMain->id,
                'supplier_name' => 'Công ty Cổ phần Mutosi Quốc Tế',
                'reference_order_code' => 'HĐ-MT-30114',
                'notes' => 'Nhập máy nguyên chiếc và linh phụ kiện thay thế định kỳ',
                'created_at' => $now->copy()->subDays(5),
                'items' => [
                    [
                        'product_index' => 6 % $sampleProducts->count(),
                        'batch_code' => 'LOT-MTS-26-09',
                        'mfg_days_ago' => 20,
                        'exp_days_ahead' => 720, // HSD xa 2 năm
                        'qty' => 60,
                        'price' => 320000,
                    ],
                    [
                        'product_index' => 7 % $sampleProducts->count(),
                        'batch_code' => 'LOT-MTS-26-10',
                        'mfg_days_ago' => 15,
                        'exp_days_ahead' => 1080, // HSD 3 năm
                        'qty' => 50,
                        'price' => 450000,
                    ],
                ]
            ],
            [
                'code' => 'PNK-2610-004',
                'warehouse_id' => $whMain->id,
                'supplier_name' => 'Công ty CP Nhựa Thiếu Niên Tiền Phong',
                'reference_order_code' => 'HĐ-TP-88902',
                'notes' => 'Ống và phụ kiện kết nối hệ thống nước lạnh máy lọc nước',
                'created_at' => $now->copy()->subDays(2),
                'items' => [
                    [
                        'product_index' => 8 % $sampleProducts->count(),
                        'batch_code' => 'LOT-TP-2610-A',
                        'mfg_days_ago' => 10,
                        'exp_days_ahead' => 1800, // Hàng bền 5 năm
                        'qty' => 200,
                        'price' => 85000,
                    ],
                    [
                        'product_index' => 9 % $sampleProducts->count(),
                        'batch_code' => 'LOT-TP-2610-B',
                        'mfg_days_ago' => 10,
                        'exp_days_ahead' => 1800,
                        'qty' => 150,
                        'price' => 65000,
                    ],
                ]
            ],
            [
                'code' => 'PNK-2610-005',
                'warehouse_id' => $whSecond->id,
                'supplier_name' => 'Xưởng Sản Xuất & Lắp Ráp Đại Sơn',
                'reference_order_code' => 'BB-DS-2026-08',
                'notes' => 'Nhập kho lô thành phẩm máy lọc nước RO 9 cấp tủ đứng Đại Sơn Eco',
                'created_at' => $now->copy()->subHours(6),
                'items' => [
                    [
                        'product_index' => 0, // Cùng sản phẩm số 0 nhưng nhập đợt mới để test xem nhiều date
                        'batch_code' => 'LOT-DS-2610-NEW',
                        'mfg_days_ago' => 2,
                        'exp_days_ahead' => 730, // HSD 2 năm
                        'qty' => 90,
                        'price' => 125000,
                    ],
                    [
                        'product_index' => 10 % $sampleProducts->count(),
                        'batch_code' => 'LOT-DS-2610-ECO',
                        'mfg_days_ago' => 3,
                        'exp_days_ahead' => 730,
                        'qty' => 30,
                        'price' => 1850000,
                    ],
                ]
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($receiptTemplates as $t) {
                // Kiểm tra mã phiếu tránh trùng lặp
                $code = $t['code'];
                $counter = 1;
                $originalCode = $code;
                while (InventoryTransaction::where('code', $code)->exists()) {
                    $code = $originalCode . '-' . $counter;
                    $counter++;
                }

                $totalAmount = 0;
                foreach ($t['items'] as $itemData) {
                    $totalAmount += ($itemData['qty'] * $itemData['price']);
                }

                $tx = InventoryTransaction::create([
                    'code' => $code,
                    'type' => 'import',
                    'warehouse_id' => $t['warehouse_id'],
                    'created_by_id' => $adminId,
                    'status' => 'Hoàn thành',
                    'supplier_name' => $t['supplier_name'],
                    'reference_order_code' => $t['reference_order_code'],
                    'total_amount' => $totalAmount,
                    'notes' => $t['notes'],
                    'created_at' => $t['created_at'],
                    'updated_at' => $t['created_at'],
                ]);

                foreach ($t['items'] as $itemData) {
                    $prod = $sampleProducts->get($itemData['product_index']) ?? $sampleProducts->first();
                    $mfgDate = $now->copy()->subDays($itemData['mfg_days_ago'])->format('Y-m-d');
                    $expDate = $now->copy()->addDays($itemData['exp_days_ahead'])->format('Y-m-d');

                    $txItem = InventoryTransactionItem::create([
                        'inventory_transaction_id' => $tx->id,
                        'product_id' => $prod->id,
                        'batch_code' => $itemData['batch_code'],
                        'manufacturing_date' => $mfgDate,
                        'expiry_date' => $expDate,
                        'quantity' => $itemData['qty'],
                        'import_price' => $itemData['price'],
                        'cost_price_at_transaction' => (float)($prod->latest_cost_price ?: $prod->cost_price),
                        'notes' => 'Lô nhập hàng ' . $itemData['batch_code'],
                        'created_at' => $t['created_at'],
                        'updated_at' => $t['created_at'],
                    ]);

                    // Tạo bản ghi Lô hàng & Hạn sử dụng trong bảng product_batches
                    ProductBatch::create([
                        'product_id' => $prod->id,
                        'warehouse_id' => $t['warehouse_id'],
                        'inventory_transaction_item_id' => $txItem->id,
                        'batch_code' => $itemData['batch_code'],
                        'manufacturing_date' => $mfgDate,
                        'expiry_date' => $expDate,
                        'import_quantity' => $itemData['qty'],
                        'remaining_quantity' => $itemData['qty'],
                        'cost_price' => $itemData['price'],
                        'status' => 'active',
                        'notes' => 'Nhập theo phiếu ' . $tx->code,
                        'created_at' => $t['created_at'],
                        'updated_at' => $t['created_at'],
                    ]);

                    // Tăng số lượng tồn kho sản phẩm
                    $prod->increment('stock_quantity', $itemData['qty']);
                    if ($itemData['price'] > 0) {
                        $prod->latest_cost_price = $itemData['price'];
                        if ((float)$prod->cost_price <= 0) {
                            $prod->cost_price = $itemData['price'];
                        }
                        $prod->save();
                    }

                    // Tăng tồn kho theo từng kho
                    $whStock = WarehouseStock::firstOrCreate(
                        ['product_id' => $prod->id, 'warehouse_id' => $t['warehouse_id']],
                        ['stock_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $whStock->increment('stock_quantity', $itemData['qty']);
                }
            }

            DB::commit();
            echo "Da tao thanh cong " . count($receiptTemplates) . " phieu nhap kho mau kem Lo hang & Han su dung (Date)!\n";
        } catch (\Throwable $e) {
            DB::rollBack();
            echo "Loi tao du lieu: " . $e->getMessage() . "\n";
        }
    }
}
