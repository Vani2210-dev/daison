<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class InventoryTransactionSeeder extends Seeder
{
    /**
     * Seed authentic inventory transactions and line items.
     */
    public function run(): void
    {
        $whTong = Warehouse::where('code', 'KHO-MQ-01')->first() ?? Warehouse::first();
        $whVanQuan = Warehouse::where('code', 'KHO-MVQ-01')->first() ?? $whTong;
        $whDongAnh = Warehouse::where('code', 'KHO-DXH-01')->first() ?? $whTong;
        $whBaoLuu = Warehouse::where('code', 'KHO-BL-01')->first() ?? $whTong;

        $user = User::first();
        $userId = $user?->id;

        $products = Product::all()->keyBy('sku');

        // 1. PHIẾU NHẬP KHO (type: import)
        $importSlips = [
            [
                'code' => 'PN-2609-0001',
                'type' => 'import',
                'warehouse_id' => $whTong->id,
                'created_by_id' => $userId,
                'status' => 'Đã nhận hàng',
                'supplier_name' => 'Công ty CP Nhựa Thiếu Niên Tiền Phong',
                'total_amount' => 24750000,
                'notes' => 'Hóa đơn GTGT số 0029312 - Xe giao nhà máy Tiền Phong Hải Phòng',
                'created_at' => '2026-09-06 09:30:00',
                'items' => [
                    ['sku' => 'PVC-TP-110-C1', 'quantity' => 100, 'import_price' => 185000, 'cost_price_at_transaction' => 185000],
                    ['sku' => 'CO-TP-110', 'quantity' => 50, 'import_price' => 45000, 'cost_price_at_transaction' => 45000],
                    ['sku' => 'PPR-TP-25', 'quantity' => 20, 'import_price' => 200000, 'cost_price_at_transaction' => 200000],
                ]
            ],
            [
                'code' => 'PN-2609-0002',
                'type' => 'import',
                'warehouse_id' => $whBaoLuu->id,
                'created_by_id' => $userId,
                'status' => 'Đã nhận hàng',
                'supplier_name' => 'Công ty Cổ phần Cúc Phương',
                'total_amount' => 9030130,
                'notes' => 'Nhập bổ sung ống thoát nước và phụ kiện theo hợp đồng nguyên tắc',
                'created_at' => '2026-09-07 16:40:00',
                'items' => [
                    ['sku' => 'CP-PVC-110', 'quantity' => 100, 'import_price' => 50000, 'cost_price_at_transaction' => 50000],
                    ['sku' => 'CO-TP-110', 'quantity' => 50, 'import_price' => 80602.6, 'cost_price_at_transaction' => 80602.6],
                ]
            ],
            [
                'code' => 'PN-2609-0003',
                'type' => 'import',
                'warehouse_id' => $whVanQuan->id,
                'created_by_id' => $userId,
                'status' => 'Đã nhận hàng',
                'supplier_name' => 'Công ty CP Nhựa Bình Minh',
                'total_amount' => 45750000,
                'notes' => 'Lô hàng ống uPVC Bình Minh đợt 1 tháng 9/2026',
                'created_at' => '2026-09-05 14:15:00',
                'items' => [
                    ['sku' => 'PVC-BM-21-C1', 'quantity' => 2500, 'import_price' => 9500, 'cost_price_at_transaction' => 9500],
                    ['sku' => 'PVC-BM-27-C2', 'quantity' => 1000, 'import_price' => 14200, 'cost_price_at_transaction' => 14200],
                    ['sku' => 'PK-CO-BM-21', 'quantity' => 3000, 'import_price' => 1800, 'cost_price_at_transaction' => 1800],
                    ['sku' => 'PK-VAN-BM-21', 'quantity' => 80, 'import_price' => 28000, 'cost_price_at_transaction' => 28000],
                ]
            ],
            [
                'code' => 'PN-2609-0004',
                'type' => 'import',
                'warehouse_id' => $whTong->id,
                'created_by_id' => $userId,
                'status' => 'Đã nhận hàng',
                'supplier_name' => 'Công ty CP Dây Cáp Điện Cadivi',
                'total_amount' => 48200000,
                'notes' => 'Nhập cuộn cáp CV 1.5 và CV 2.5 theo đơn đặt hàng Quý 3',
                'created_at' => '2026-09-04 11:00:00',
                'items' => [
                    ['sku' => 'CAD-2.5-RD', 'quantity' => 40, 'import_price' => 680000, 'cost_price_at_transaction' => 680000],
                    ['sku' => 'ELEC-CAD-CV15', 'quantity' => 50, 'import_price' => 420000, 'cost_price_at_transaction' => 420000],
                ]
            ],
        ];

        // 2. PHIẾU XUẤT KHO (type: export)
        $exportSlips = [
            [
                'code' => 'PX-2609-0001',
                'type' => 'export',
                'warehouse_id' => $whTong->id,
                'created_by_id' => $userId,
                'status' => 'Đã nhận hàng',
                'supplier_name' => 'Đại Lý Điện Nước Minh Quang',
                'reference_order_code' => 'DH-2026-089',
                'total_amount' => 23650000,
                'notes' => 'Xuất giao đại lý cấp 1 Minh Quang xe tải 29C-888.99',
                'created_at' => '2026-09-07 10:15:00',
                'items' => [
                    ['sku' => 'PVC-TP-110-C1', 'quantity' => 50, 'import_price' => 225000, 'cost_price_at_transaction' => 185000],
                    ['sku' => 'CO-TP-110', 'quantity' => 20, 'import_price' => 55000, 'cost_price_at_transaction' => 45000],
                    ['sku' => 'CAD-2.5-RD', 'quantity' => 15, 'import_price' => 755000, 'cost_price_at_transaction' => 680000],
                ]
            ],
            [
                'code' => 'PX-2609-0002',
                'type' => 'export',
                'warehouse_id' => $whVanQuan->id,
                'created_by_id' => $userId,
                'status' => 'Đã nhận hàng',
                'supplier_name' => 'Công ty XD Hòa Bình (Dự án Goldmark)',
                'reference_order_code' => 'DH-2026-092',
                'total_amount' => 19700000,
                'notes' => 'Xuất cấp vật tư hệ thống cơ điện công trình',
                'created_at' => '2026-09-06 15:45:00',
                'items' => [
                    ['sku' => 'PVC-BM-21-C1', 'quantity' => 500, 'import_price' => 12500, 'cost_price_at_transaction' => 9500],
                    ['sku' => 'VALVE-MH-20', 'quantity' => 25, 'import_price' => 105000, 'cost_price_at_transaction' => 85000],
                    ['sku' => 'SINO-CB-2P-32A', 'quantity' => 50, 'import_price' => 62000, 'cost_price_at_transaction' => 52000],
                ]
            ],
        ];

        // 3. PHIẾU ĐIỀU CHUYỂN KHO (type: transfer)
        $transferSlips = [
            [
                'code' => 'CK-2609-0001',
                'type' => 'transfer',
                'warehouse_id' => $whTong->id,
                'target_warehouse_id' => $whVanQuan->id,
                'created_by_id' => $userId,
                'status' => 'Đã nhận hàng',
                'supplier_name' => 'Xe tải 29C-888.99 (Tài xế Bùi Văn Thành)',
                'total_amount' => 15800000,
                'notes' => 'Điều chuyển cân đối hàng tồn kho từ Kho Tổng Mê Linh sang Kho Vân Quần',
                'created_at' => '2026-09-07 14:20:00',
                'items' => [
                    ['sku' => 'PVC-BM-21-C1', 'quantity' => 800, 'import_price' => 9500, 'cost_price_at_transaction' => 9500],
                    ['sku' => 'PK-CO-BM-21', 'quantity' => 1000, 'import_price' => 1800, 'cost_price_at_transaction' => 1800],
                    ['sku' => 'VALVE-MH-20', 'quantity' => 30, 'import_price' => 85000, 'cost_price_at_transaction' => 85000],
                ]
            ],
            [
                'code' => 'CK-2609-0002',
                'type' => 'transfer',
                'warehouse_id' => $whTong->id,
                'target_warehouse_id' => $whDongAnh->id,
                'created_by_id' => $userId,
                'status' => 'Đã nhận hàng',
                'supplier_name' => 'Xe tải Hyundai 5T (29C-777.66)',
                'total_amount' => 28500000,
                'notes' => 'Chuyển thiết bị điện và ống thoát nước sang Kho Chi Nhánh Đông Anh',
                'created_at' => '2026-09-05 08:30:00',
                'items' => [
                    ['sku' => 'PVC-TP-60-C2', 'quantity' => 100, 'import_price' => 125000, 'cost_price_at_transaction' => 125000],
                    ['sku' => 'CAD-2.5-RD', 'quantity' => 15, 'import_price' => 680000, 'cost_price_at_transaction' => 680000],
                    ['sku' => 'SINO-CB-2P-32A', 'quantity' => 60, 'import_price' => 52000, 'cost_price_at_transaction' => 52000],
                ]
            ],
        ];

        // 4. PHIẾU KIỂM KÊ KHO (type: audit)
        $auditSlips = [
            [
                'code' => 'PKK-2609-0001',
                'type' => 'audit',
                'warehouse_id' => $whTong->id,
                'created_by_id' => $userId,
                'status' => 'Hoàn thành',
                'supplier_name' => 'Ban Kiểm Kê Nội Bộ MQ',
                'total_amount' => 0,
                'notes' => 'Kiểm kê định kỳ đầu tháng 9/2026 tại Kho Tổng Mê Linh - 10 mã vật tư chủ lực',
                'created_at' => '2026-09-03 17:00:00',
                'items' => [
                    ['sku' => 'PVC-TP-110-C1', 'quantity' => 520, 'actual_audit_quantity' => 518, 'notes' => 'Hao hụt 2 cây do trầy xước phế liệu'],
                    ['sku' => 'PVC-BM-21-C1', 'quantity' => 12500, 'actual_audit_quantity' => 12500, 'notes' => 'Khớp 100%'],
                    ['sku' => 'CO-TP-110', 'quantity' => 850, 'actual_audit_quantity' => 855, 'notes' => 'Thừa 5 cái do xếp nhầm kiện'],
                    ['sku' => 'CAD-2.5-RD', 'quantity' => 280, 'actual_audit_quantity' => 280, 'notes' => 'Khớp 100%'],
                ]
            ],
        ];

        $allTransactions = array_merge($importSlips, $exportSlips, $transferSlips, $auditSlips);

        foreach ($allTransactions as $tData) {
            $items = $tData['items'] ?? [];
            unset($tData['items']);

            $trans = InventoryTransaction::updateOrCreate(
                ['code' => $tData['code']],
                $tData
            );

            // Clean old items and re-insert
            $trans->items()->delete();

            foreach ($items as $it) {
                $p = $products[$it['sku']] ?? null;
                if ($p) {
                    InventoryTransactionItem::create([
                        'inventory_transaction_id' => $trans->id,
                        'product_id' => $p->id,
                        'quantity' => $it['quantity'] ?? 1,
                        'import_price' => $it['import_price'] ?? ($p->cost_price ?? 0),
                        'cost_price_at_transaction' => $it['cost_price_at_transaction'] ?? ($p->cost_price ?? 0),
                        'actual_audit_quantity' => $it['actual_audit_quantity'] ?? null,
                        'notes' => $it['notes'] ?? null,
                    ]);
                }
            }
        }
    }
}
