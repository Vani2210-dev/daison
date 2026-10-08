<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dealer;
use App\Models\User;
use App\Models\Product;
use App\Models\Vehicle;
use App\Models\DeliveryDispatch;
use App\Models\Transaction;
use App\Models\FinancialFund;

class OrderWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================================
        // 1. XÓA SẠCH TOÀN BỘ ĐƠN HÀNG, SỰ KIỆN KHO, VẬN CHUYỂN, GIAO DỊCH CŨ VÀ CÔNG NỢ
        // =========================================================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        DB::table('picking_sync_events')->truncate();
        DB::table('delivery_return_items')->truncate();
        DB::table('delivery_returns')->truncate();
        DB::table('delivery_dispatch_orders')->truncate();
        DB::table('driver_cash_collections')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('transactions')->truncate();

        // Reset toàn bộ công nợ của tất cả các đại lý về 0
        DB::table('dealers')->update([
            'initial_debt' => 0.00,
            'current_debt' => 0.00,
            'overdue_days' => 0,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =========================================================================
        // 2. LẤY THÔNG TIN CƠ SỞ: USERS, FUNDS, VEHICLES, DEALERS, PRODUCTS
        // =========================================================================
        $admin = User::first();
        $salesRep = User::where('department', 'like', '%Sales%')->orWhere('department', 'like', '%Kinh doanh%')->first() ?? $admin;
        $accountant = User::where('department', 'like', '%Account%')->orWhere('department', 'like', '%Kế toán%')->first() ?? $admin;
        $warehouseUser = User::where('department', 'like', '%Warehouse%')->orWhere('department', 'like', '%Kho%')->first() ?? $admin;

        // Các tài xế theo xe
        $driverThanh = User::where('name', 'like', '%Thành%')->first() ?? $admin;
        $driverHung = User::where('name', 'like', '%Hùng%')->first() ?? $driverThanh;
        $driverTrong = User::where('name', 'like', '%Trọng%')->first() ?? $driverThanh;
        $driverCuong = User::where('name', 'like', '%Cường%')->first() ?? $driverThanh;

        // Các quỹ tài chính
        $fundCash = FinancialFund::where('name', 'like', '%Tiền Mặt%')->first();
        $fundMB   = FinancialFund::where('name', 'like', '%MB%')->first();
        $fundVCB  = FinancialFund::where('name', 'like', '%Vietcombank%')->first();

        // Cập nhật trạng thái các xe vận chuyển:
        // Xe 29C-888.99: Sẵn sàng (CHƯA XUẤT BẾN - Đơn trên xe CHỈ CÓ các đơn ĐÃ KIỂM HÀNG chờ xuất bến)
        $veh88 = Vehicle::where('plate_number', '29C-888.99')->first();
        if ($veh88) {
            $veh88->update([
                'status' => 'Sẵn sàng',
                'driver_id' => $driverThanh->id,
                'copilot_name' => 'Lê Văn Nam'
            ]);
        }

        // Xe 29H-555.44: Đang di chuyển (ĐÃ XUẤT BẾN lúc 08:30 sáng nay - Đơn trên xe CHỈ CÓ các đơn ĐANG VẬN CHUYỂN)
        $veh55 = Vehicle::where('plate_number', '29H-555.44')->first();
        if ($veh55) {
            $veh55->update([
                'status' => 'Đang di chuyển',
                'driver_id' => $driverTrong->id,
                'copilot_name' => 'Vũ Đình Trọng'
            ]);
        }

        // Xe 29C-777.66: Sẵn sàng (Sau khi đã hoàn thành chuyến giao hôm qua, đã về bến quyết toán)
        $veh77 = Vehicle::where('plate_number', '29C-777.66')->first();
        if ($veh77) {
            $veh77->update([
                'status' => 'Sẵn sàng',
                'driver_id' => $driverHung->id,
                'copilot_name' => 'Phạm Quốc Hùng'
            ]);
        }

        // Xe 29H-333.22: Sẵn sàng
        Vehicle::where('plate_number', '29H-333.22')->update([
            'status' => 'Sẵn sàng',
            'driver_id' => $driverCuong->id,
            'copilot_name' => 'Hoàng Văn Cường'
        ]);

        // Lấy danh sách đại lý theo mã
        $dealers = Dealer::all()->keyBy('code');
        $dlNA01 = $dealers->get('DL-NA-001') ?? Dealer::find(1);
        $dlNA02 = $dealers->get('DL-NA-002') ?? Dealer::find(2);
        $dlNA03 = $dealers->get('DL-NA-003') ?? Dealer::find(3);
        $dlNA04 = $dealers->get('DL-NA-004') ?? Dealer::find(4);
        $dlNA05 = $dealers->get('DL-NA-005') ?? Dealer::find(5);
        $dlNA06 = $dealers->get('DL-NA-006') ?? Dealer::find(6);

        $dlHT01 = $dealers->get('DL-HT-001') ?? Dealer::find(7);
        $dlHT02 = $dealers->get('DL-HT-002') ?? Dealer::find(8);
        $dlHT03 = $dealers->get('DL-HT-003') ?? Dealer::find(9);
        $dlHT05 = $dealers->get('DL-HT-005') ?? Dealer::find(11);
        $dlHT06 = $dealers->get('DL-HT-006') ?? Dealer::find(12);

        $dlHN01 = $dealers->get('DL-HN-001') ?? Dealer::find(13);
        $dlHN02 = $dealers->get('DL-HN-002') ?? Dealer::find(14);

        // Lấy danh mục sản phẩm mẫu
        $products = Product::all();
        $p1 = $products->where('sku', 'PVC-BM-90-C2')->first() ?? $products->first();
        $p2 = $products->where('sku', 'PVC-BM-110-C2')->first() ?? $products->skip(1)->first() ?? $p1;
        $p3 = $products->where('sku', 'PK-CD-90')->first() ?? $products->skip(2)->first() ?? $p1;
        $p4 = $products->where('sku', 'DAY-CADIVI-2.5')->first() ?? $products->skip(3)->first() ?? $p1;
        $p5 = $products->where('sku', 'KEO-PVC-500G')->first() ?? $products->skip(4)->first() ?? $p1;

        // =========================================================================
        // 3. DANH SÁCH 13 ĐƠN HÀNG MẪU CHUẨN ĐÚNG QUY TRÌNH 7 GIAI ĐOẠN ERP
        // =========================================================================
        $workflowOrders = [
            // -------------------------------------------------------------
            // GIAI ĐOẠN 1: BÁO GIÁ (2 đơn) - Chưa chốt, không nợ, không kho, KHÔNG LÊN XE
            // -------------------------------------------------------------
            [
                'order_code' => 'DH-2026-BG01',
                'dealer_id' => $dlNA04?->id,
                'customer_name' => $dlNA04?->name ?? 'Đại Lý Vật Tư Cửa Lò - Lam Giang',
                'customer_phone' => $dlNA04?->phone ?? '0966 889 900',
                'customer_address' => $dlNA04?->address ?? 'Khối 3, Phường Nghi Hương, TX Cửa Lò, Nghệ An',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => 18500000,
                'discount_amount' => 1500000,
                'tax_amount' => 1700000,
                'total_amount' => 18700000,
                'status' => 'Báo giá',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => '[BÁO GIÁ DỰ TOÁN] Đơn gửi bảng chào giá vật tư công trình nhà nghỉ dưỡng Cửa Lò. Chưa chốt, không tính vào công nợ.',
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p1, 'qty' => 50, 'price' => 125000],
                    ['product' => $p3, 'qty' => 100, 'price' => 38000],
                    ['product' => $p5, 'qty' => 20, 'price' => 45000],
                ]
            ],
            [
                'order_code' => 'DH-2026-BG02',
                'dealer_id' => $dlHT05?->id,
                'customer_name' => $dlHT05?->name ?? 'Cửa Hàng Thiết Bị Nước Can Lộc',
                'customer_phone' => $dlHT05?->phone ?? '0984 567 111',
                'customer_address' => $dlHT05?->address ?? 'Khối 6, Thị trấn Nghèn, Can Lộc, Hà Tĩnh',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => 24000000,
                'discount_amount' => 1500000,
                'tax_amount' => 2000000,
                'total_amount' => 24500000,
                'status' => 'Báo giá',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 0,
                'notes' => '[BÁO GIÁ DỰ ÁN] Chào giá đợt 1 đường cấp nước nội bộ Can Lộc. Chưa chốt, không tính nợ.',
                'order_type' => 'project',
                'project_name' => 'Dự án Cấp nước Thị trấn Nghèn',
                'items' => [
                    ['product' => $p2, 'qty' => 80, 'price' => 245000],
                    ['product' => $p4, 'qty' => 5, 'price' => 1250000],
                ]
            ],

            // -------------------------------------------------------------
            // GIAI ĐOẠN 2: CHỐT ĐƠN (2 đơn) - Đã duyệt bán, ghi nợ, chưa vào kho, KHÔNG LÊN XE
            // -------------------------------------------------------------
            [
                'order_code' => 'DH-2026-CD01',
                'dealer_id' => $dlHT02?->id,
                'customer_name' => $dlHT02?->name ?? 'Cửa Hàng Vật Tư Ống Nước Nghi Xuân',
                'customer_phone' => $dlHT02?->phone ?? '0978 222 333',
                'customer_address' => $dlHT02?->address ?? 'Thị trấn Tiên Điền, Huyện Nghi Xuân, Hà Tĩnh',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => 15000000,
                'discount_amount' => 800000,
                'tax_amount' => 1400000,
                'total_amount' => 15600000,
                'status' => 'Chốt đơn',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => '[CHỐT ĐƠN BÁN HÀNG] Đã duyệt bán hàng và ghi nhận công nợ. Chờ thủ kho tiếp nhận tạo lệnh bốc hàng.',
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p1, 'qty' => 60, 'price' => 125000],
                    ['product' => $p3, 'qty' => 120, 'price' => 38000],
                    ['product' => $p5, 'qty' => 15, 'price' => 45000],
                ]
            ],
            [
                'order_code' => 'DH-2026-CD02',
                'dealer_id' => $dlHN01?->id,
                'customer_name' => $dlHN01?->name ?? 'Đại Lý Điện Nước Minh Quang',
                'customer_phone' => $dlHN01?->phone ?? '0912 888 999',
                'customer_address' => $dlHN01?->address ?? 'Số 25 Phố Huế, Hoàn Kiếm, Hà Nội',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => 20000000,
                'discount_amount' => 1500000,
                'tax_amount' => 1500000,
                'total_amount' => 20000000,
                'status' => 'Chốt đơn',
                'payment_status' => 'Đã cọc', // Đã cọc 30% = 6.000.000đ, nợ 70% = 14.000.000đ
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => '[CHỐT ĐƠN - ĐÃ CỌC 30%] Đại lý đã chuyển khoản đặt cọc 6.000.000đ qua MBBank. Còn nợ 14.000.000đ.',
                'order_type' => 'normal',
                'deposit_amount' => 6000000,
                'items' => [
                    ['product' => $p2, 'qty' => 50, 'price' => 245000],
                    ['product' => $p4, 'qty' => 6, 'price' => 1250000],
                ]
            ],

            // -------------------------------------------------------------
            // GIAI ĐOẠN 3: KHO ĐANG SOẠN / CHỜ BỐC (2 đơn) - Trong kho, KHÔNG LÊN XE
            // -------------------------------------------------------------
            [
                'order_code' => 'DH-2026-BS01',
                'dealer_id' => $dlNA05?->id,
                'customer_name' => $dlNA05?->name ?? 'Cửa Hàng Điện Dân Dụng Hưng Nguyên',
                'customer_phone' => $dlNA05?->phone ?? '0977 123 456',
                'customer_address' => $dlNA05?->address ?? 'Khối 8, Thị trấn Hưng Nguyên, Huyện Hưng Nguyên, Nghệ An',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => 25000000,
                'discount_amount' => 1800000,
                'tax_amount' => 2300000,
                'total_amount' => 25500000,
                'status' => 'Kho đang soạn',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Tiền mặt',
                'driver_debt_amount' => 0,
                'notes' => '[KHO ĐANG SOẠN] Lệnh bốc hàng đã chuyển kho. Thủ kho đang phân công soạn hàng.',
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p1, 'qty' => 100, 'price' => 125000],
                    ['product' => $p3, 'qty' => 200, 'price' => 38000],
                    ['product' => $p5, 'qty' => 30, 'price' => 45000],
                ]
            ],
            [
                'order_code' => 'DH-2026-BS02',
                'dealer_id' => $dlHT06?->id,
                'customer_name' => $dlHT06?->name ?? 'Đại Lý Ống PVC Cẩm Xuyên - Đức Phát',
                'customer_phone' => $dlHT06?->phone ?? '0915 678 999',
                'customer_address' => $dlHT06?->address ?? 'Thị trấn Cẩm Xuyên, Huyện Cẩm Xuyên, Hà Tĩnh',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => 31000000,
                'discount_amount' => 2000000,
                'tax_amount' => 3000000,
                'total_amount' => 32000000,
                'status' => 'Kho đang soạn',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 0,
                'notes' => '[KHO ĐANG SOẠN] Nhân viên kho đang gom sản phẩm ống Tiền Phong & phụ kiện tại kệ A3-B2.',
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p2, 'qty' => 90, 'price' => 245000],
                    ['product' => $p4, 'qty' => 8, 'price' => 1250000],
                ]
            ],

            // -------------------------------------------------------------
            // GIAI ĐOẠN 4: ĐÃ BỐC XONG / KHO ĐÃ XUẤT ĐỦ (2 đơn) - Tại kho, KHÔNG LÊN XE
            // -------------------------------------------------------------
            [
                'order_code' => 'DH-2026-DB01',
                'dealer_id' => $dlHN02?->id,
                'customer_name' => $dlHN02?->name ?? 'Cửa Hàng Vật Tư Ống Nhựa Thành Công',
                'customer_phone' => $dlHN02?->phone ?? '0903 456 789',
                'customer_address' => $dlHN02?->address ?? 'Km12 Đường Ngọc Hồi, Thanh Trì, Hà Nội',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => 27500000,
                'discount_amount' => 1600000,
                'tax_amount' => 2500000,
                'total_amount' => 28400000,
                'status' => 'Kho đã xuất đủ',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => '[ĐÃ BỐC XONG 100%] Hàng đã gom đủ tại bến xuất kho số 1. Sẵn sàng cho nhân viên KCS tiến hành kiểm hàng đối chiếu.',
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p1, 'qty' => 120, 'price' => 125000],
                    ['product' => $p3, 'qty' => 180, 'price' => 38000],
                    ['product' => $p4, 'qty' => 5, 'price' => 1250000],
                ]
            ],
            [
                'order_code' => 'DH-2026-DB02',
                'dealer_id' => $dlHT03?->id,
                'customer_name' => $dlHT03?->name ?? 'Tổng Kho Điện Nước Hồng Lĩnh',
                'customer_phone' => $dlHT03?->phone ?? '0912 345 678',
                'customer_address' => $dlHT03?->address ?? 'Số 45 Đường Quang Trung, TX Hồng Lĩnh, Hà Tĩnh',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => 44000000,
                'discount_amount' => 3000000,
                'tax_amount' => 4000000,
                'total_amount' => 45000000,
                'status' => 'Kho đã xuất đủ',
                'payment_status' => 'Đã cọc', // Đã cọc 30% = 13.500.000đ, nợ 70% = 31.500.000đ
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 0,
                'notes' => '[ĐÃ BỐC XONG 100%] Khách đã đặt cọc 30% (13.500.000đ). Kho đã soạn đủ toàn bộ đơn hàng.',
                'order_type' => 'normal',
                'deposit_amount' => 13500000,
                'items' => [
                    ['product' => $p2, 'qty' => 120, 'price' => 245000],
                    ['product' => $p4, 'qty' => 10, 'price' => 1250000],
                ]
            ],

            // -------------------------------------------------------------
            // GIAI ĐOẠN 5: ĐÃ KIỂM HÀNG (2 đơn)
            // (Đã kiểm duyệt QC 100%, XẾP LÊN XE 29C-888.99, XE SẴN SÀNG CHƯA XUẤT BẾN)
            // -------------------------------------------------------------
            [
                'order_code' => 'DH-2026-KH01',
                'dealer_id' => $dlNA01?->id,
                'customer_name' => $dlNA01?->name ?? 'Đại Lý Điện Nước Thành Vinh (TP. Vinh)',
                'customer_phone' => $dlNA01?->phone ?? '0912 345 888',
                'customer_address' => $dlNA01?->address ?? 'Số 186 Đường Trần Phú, Phường Lê Mao, TP. Vinh, Nghệ An',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-888.99', // XE 29C-888.99 (Chưa xuất bến)
                'subtotal' => 19500000,
                'discount_amount' => 1500000,
                'tax_amount' => 1800000,
                'total_amount' => 19800000,
                'status' => 'Đã kiểm hàng',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Tiền mặt',
                'driver_debt_amount' => 0,
                'notes' => '[ĐÃ KIỂM HÀNG] Đã kiểm hàng đạt QC 100%. Đã xếp lên xe tải 29C-888.99, xe đang ở bãi chờ lệnh xuất bến.',
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p1, 'qty' => 80, 'price' => 125000],
                    ['product' => $p3, 'qty' => 150, 'price' => 38000],
                    ['product' => $p5, 'qty' => 20, 'price' => 45000],
                ]
            ],
            [
                'order_code' => 'DH-2026-KH02',
                'dealer_id' => $dlNA06?->id,
                'customer_name' => $dlNA06?->name ?? 'Đại Lý Nhựa Tiền Phong Nam Đàn',
                'customer_phone' => $dlNA06?->phone ?? '0983 456 789',
                'customer_address' => $dlNA06?->address ?? 'Khối Ba Hà, Thị trấn Nam Đàn, Huyện Nam Đàn, Nghệ An',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-888.99', // XE 29C-888.99 (Chưa xuất bến)
                'subtotal' => 16000000,
                'discount_amount' => 1000000,
                'tax_amount' => 1500000,
                'total_amount' => 16500000,
                'status' => 'Đã kiểm hàng',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => '[ĐÃ KIỂM HÀNG] Đơn thứ 2 trên xe 29C-888.99. Cả 2 đơn trên xe đều đã kiểm hàng xong -> Đủ điều kiện xuất bến!',
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p2, 'qty' => 40, 'price' => 245000],
                    ['product' => $p4, 'qty' => 5, 'price' => 1250000],
                ]
            ],

            // -------------------------------------------------------------
            // GIAI ĐOẠN 6: ĐANG VẬN CHUYỂN (2 đơn)
            // (XE 29H-555.44 ĐÃ XUẤT BẾN LÚC 08:30 SÁNG NAY, XE ĐANG DI CHUYỂN TRÊN ĐƯỜNG)
            // -------------------------------------------------------------
            [
                'order_code' => 'DH-2026-VC01',
                'dealer_id' => $dlNA03?->id,
                'customer_name' => $dlNA03?->name ?? 'Nhà Thầu Cơ Điện & Nước Nghi Lộc',
                'customer_phone' => $dlNA03?->phone ?? '0979 123 456',
                'customer_address' => $dlNA03?->address ?? 'Khu Công Nghiệp Nam Cấm, Huyện Nghi Lộc, Nghệ An',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => $driverTrong->id,
                'assigned_vehicle_plate' => '29H-555.44', // XE 29H-555.44 (Đang di chuyển)
                'subtotal' => 42000000,
                'discount_amount' => 3000000,
                'tax_amount' => 3900000,
                'total_amount' => 42900000,
                'status' => 'Đang vận chuyển',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 0,
                'notes' => '[ĐANG VẬN CHUYỂN] Xe 29H-555.44 đã xuất bến lúc 08:30 sáng. Đang di chuyển theo tuyến QL1A về phía Nghi Lộc.',
                'order_type' => 'project',
                'project_name' => 'Công trình Cấp Thoát Nước Nghi Xá',
                'items' => [
                    ['product' => $p2, 'qty' => 100, 'price' => 245000],
                    ['product' => $p1, 'qty' => 80, 'price' => 125000],
                    ['product' => $p3, 'qty' => 150, 'price' => 38000],
                ]
            ],
            [
                'order_code' => 'DH-2026-VC02',
                'dealer_id' => $dlNA02?->id,
                'customer_name' => $dlNA02?->name ?? 'Cửa Hàng Ống Nhựa Diễn Châu (Ngã 3 Cầu Bùng)',
                'customer_phone' => $dlNA02?->phone ?? '0988 567 123',
                'customer_address' => $dlNA02?->address ?? 'Ngã 3 Cầu Bùng, Xã Diễn Kỷ, Huyện Diễn Châu, Nghệ An',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => $driverTrong->id,
                'assigned_vehicle_plate' => '29H-555.44', // XE 29H-555.44 (Đang di chuyển)
                'subtotal' => 19500000,
                'discount_amount' => 1500000,
                'tax_amount' => 1800000,
                'total_amount' => 19800000,
                'status' => 'Đang vận chuyển',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Tiền mặt',
                'driver_debt_amount' => 0,
                'notes' => '[ĐANG VẬN CHUYỂN] Điểm giao thứ 2 trên tuyến xe 29H-555.44. Lái xe đang gọi điện hẹn giờ nhận hàng với khách.',
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p1, 'qty' => 50, 'price' => 125000],
                    ['product' => $p4, 'qty' => 7, 'price' => 1250000],
                ]
            ],

            // -------------------------------------------------------------
            // GIAI ĐOẠN 7: ĐÃ GIAO (3 đơn)
            // (THUỘC CHUYẾN XE CX-2026-080 CỦA XE 29C-777.66 ĐÃ XUẤT BẾN HÔM QUA VÀ ĐÃ VỀ BẾN QUYẾT TOÁN XONG!)
            // TUYỆT ĐỐI KHÔNG GÁN VÀO XE 29C-888.99 (ĐANG CHỜ XUẤT BẾN) HAY XE 29H-555.44 (ĐANG CHẠY)!
            // -------------------------------------------------------------
            [
                'order_code' => 'DH-2026-DG01',
                'dealer_id' => $dlNA01?->id,
                'customer_name' => $dlNA01?->name ?? 'Đại Lý Điện Nước Thành Vinh (TP. Vinh)',
                'customer_phone' => $dlNA01?->phone ?? '0912 345 888',
                'customer_address' => $dlNA01?->address ?? 'Số 186 Đường Trần Phú, Phường Lê Mao, TP. Vinh, Nghệ An',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => $driverHung->id, // Tài xế Phạm Quốc Hùng (Xe 29C-777.66)
                'assigned_vehicle_plate' => '29C-777.66', // XE 29C-777.66 (Chuyến hôm qua đã về bến)
                'subtotal' => 22000000,
                'discount_amount' => 1800000,
                'tax_amount' => 2020000,
                'total_amount' => 22220000,
                'status' => 'Đã giao',
                'payment_status' => 'Đã thu tiền mặt', // Lái xe thu đủ 100%, nợ = 0đ
                'payment_method' => 'Tiền mặt',
                'driver_debt_amount' => 22220000,
                'notes' => "[ĐÃ GIAO HÔM QUA - ĐÃ THU TIỀN MẶT] Lái xe Phạm Quốc Hùng (xe 29C-777.66) đã thu 22.220.000đ tiền mặt. Xe đã về bến quyết toán xong.",
                'order_type' => 'normal',
                'pay_full_cash' => true,
                'items' => [
                    ['product' => $p1, 'qty' => 70, 'price' => 125000],
                    ['product' => $p3, 'qty' => 100, 'price' => 38000],
                    ['product' => $p5, 'qty' => 15, 'price' => 45000],
                ]
            ],
            [
                'order_code' => 'DH-2026-DG02',
                'dealer_id' => $dlNA06?->id,
                'customer_name' => $dlNA06?->name ?? 'Đại Lý Nhựa Tiền Phong Nam Đàn',
                'customer_phone' => $dlNA06?->phone ?? '0983 456 789',
                'customer_address' => $dlNA06?->address ?? 'Khối Ba Hà, Thị trấn Nam Đàn, Huyện Nam Đàn, Nghệ An',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => $driverHung->id, // Tài xế Phạm Quốc Hùng (Xe 29C-777.66)
                'assigned_vehicle_plate' => '29C-777.66', // XE 29C-777.66 (Chuyến hôm qua đã về bến)
                'subtotal' => 35000000,
                'discount_amount' => 2500000,
                'tax_amount' => 3250000,
                'total_amount' => 35750000,
                'status' => 'Đã giao',
                'payment_status' => 'Đã thanh toán QR', // Khách quét mã VietQR MBBank, nợ = 0đ
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 0,
                'notes' => "[ĐÃ GIAO HÔM QUA - ĐÃ THANH TOÁN QR] Khách quét mã VietQR MBBank 35.750.000đ lúc giao hàng trên tuyến xe 29C-777.66. Kế toán đã đối soát ngân hàng.",
                'order_type' => 'normal',
                'pay_full_qr' => true,
                'items' => [
                    ['product' => $p2, 'qty' => 60, 'price' => 245000],
                    ['product' => $p4, 'qty' => 10, 'price' => 1250000],
                ]
            ],
            [
                'order_code' => 'DH-2026-DG03',
                'dealer_id' => $dlHT01?->id,
                'customer_name' => $dlHT01?->name ?? 'Đại Lý Điện Nước Hà Tĩnh - Thành Sen',
                'customer_phone' => $dlHT01?->phone ?? '0913 222 555',
                'customer_address' => $dlHT01?->address ?? 'Số 98 Đường Hà Huy Tập, Phường Nam Hà, TP. Hà Tĩnh, Hà Tĩnh',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'sales_rep_id' => $salesRep->id,
                'sales_accountant_id' => $accountant->id,
                'order_creator' => $salesRep->name,
                'warehouse_staff_id' => $warehouseUser->id,
                'assigned_driver_id' => $driverHung->id, // Tài xế Phạm Quốc Hùng (Xe 29C-777.66)
                'assigned_vehicle_plate' => '29C-777.66', // XE 29C-777.66 (Chuyến hôm qua đã về bến)
                'subtotal' => 48000000,
                'discount_amount' => 3000000,
                'tax_amount' => 3600000,
                'total_amount' => 48600000,
                'status' => 'Đã giao',
                'payment_status' => 'Chưa thanh toán', // Khách mua nợ gối đầu 30 ngày -> Nợ = 48.600.000đ
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => "[ĐÃ GIAO HÔM QUA - NỢ GỐI ĐẦU 30 NGÀY] Giao thành công theo chuyến 29C-777.66 hôm qua. Khách nhận đủ hàng, ký biên bản bàn giao. Công nợ ghi sổ hạn thanh toán 30 ngày.",
                'order_type' => 'normal',
                'items' => [
                    ['product' => $p1, 'qty' => 100, 'price' => 125000],
                    ['product' => $p2, 'qty' => 80, 'price' => 245000],
                    ['product' => $p4, 'qty' => 10, 'price' => 1250000],
                ]
            ],
        ];

        // =========================================================================
        // 4. LƯU ĐƠN HÀNG VÀ TẠO GIAO DỊCH THU TIỀN TƯƠNG ỨNG
        // =========================================================================
        $createdOrders = [];
        $txCounter = 1;

        foreach ($workflowOrders as $ord) {
            $itemsList = $ord['items'] ?? [];
            $depositAmount = $ord['deposit_amount'] ?? 0;
            $payFullCash = $ord['pay_full_cash'] ?? false;
            $payFullQr = $ord['pay_full_qr'] ?? false;

            unset($ord['items'], $ord['deposit_amount'], $ord['pay_full_cash'], $ord['pay_full_qr']);

            $order = Order::create($ord);
            $createdOrders[$order->order_code] = $order;

            // Tạo các dòng hàng trong OrderItem
            foreach ($itemsList as $it) {
                $prod = $it['product'];
                if (!$prod) continue;

                $qty = $it['qty'];
                $price = $it['price'];
                $amount = $qty * $price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $prod->id,
                    'sku' => $prod->sku,
                    'name' => $prod->name,
                    'unit' => $prod->unit,
                    'quantity' => $qty,
                    'cost_price' => $prod->cost_price ?? 0,
                    'unit_price' => $price,
                    'original_price' => $price,
                    'applied_tier_policy' => 'Đại lý Cấp 1',
                    'amount' => $amount,
                    'returned_qty' => 0,
                    'delivered_qty' => $order->status === 'Đã giao' ? $qty : 0,
                ]);
            }

            // Tạo Transaction thu tiền nếu là đơn đã cọc hoặc đã thanh toán
            if ($payFullCash) {
                Transaction::create([
                    'code' => 'PT-' . date('Y') . '-' . str_pad((string)$txCounter++, 4, '0', STR_PAD_LEFT),
                    'type' => 'Thu',
                    'fund_id' => $fundCash?->id ?? 1,
                    'category' => 'Thu tiền bán hàng',
                    'amount' => $order->total_amount,
                    'customer_partner_name' => $order->customer_name,
                    'order_id' => $order->id,
                    'created_by_id' => $accountant->id,
                    'date' => now()->subDay()->toDateString(), // Thu tiền hôm qua khi giao hàng
                    'notes' => 'Lái xe nộp tiền mặt thu từ đơn hàng ' . $order->order_code,
                    'has_vat_invoice' => false,
                ]);
            } elseif ($payFullQr) {
                Transaction::create([
                    'code' => 'PT-' . date('Y') . '-' . str_pad((string)$txCounter++, 4, '0', STR_PAD_LEFT),
                    'type' => 'Thu',
                    'fund_id' => $fundMB?->id ?? 2,
                    'category' => 'Thu tiền bán hàng',
                    'amount' => $order->total_amount,
                    'customer_partner_name' => $order->customer_name,
                    'order_id' => $order->id,
                    'created_by_id' => $accountant->id,
                    'date' => now()->subDay()->toDateString(), // Quét QR hôm qua khi giao hàng
                    'notes' => 'Khách thanh toán VietQR MBBank cho đơn hàng ' . $order->order_code,
                    'has_vat_invoice' => false,
                ]);
            } elseif ($depositAmount > 0) {
                Transaction::create([
                    'code' => 'PT-' . date('Y') . '-' . str_pad((string)$txCounter++, 4, '0', STR_PAD_LEFT),
                    'type' => 'Thu',
                    'fund_id' => $fundMB?->id ?? 2,
                    'category' => 'Thu tiền cọc đơn hàng',
                    'amount' => $depositAmount,
                    'customer_partner_name' => $order->customer_name,
                    'order_id' => $order->id,
                    'created_by_id' => $accountant->id,
                    'date' => now()->toDateString(),
                    'notes' => 'Khách đặt cọc 30% tiền hàng đơn ' . $order->order_code,
                    'has_vat_invoice' => false,
                ]);
            }
        }

        // =========================================================================
        // 5. CẬP NHẬT ĐIỀU VẬN XE & CHUYẾN GIAO HÀNG KHỚP 100%
        // =========================================================================
        // CHUYẾN 1: CX-2026-082 trên XE 29C-888.99
        // - Trạng thái: "Chờ xuất bến" (Xe SẴN SÀNG, chưa xuất bến, departure_time = null)
        // - Đơn hàng: CHỈ CÓ 2 đơn ĐÃ KIỂM HÀNG (DH-2026-KH01, DH-2026-KH02)
        // - TUYỆT ĐỐI KHÔNG CÓ đơn nào đã giao trên xe này!
        $dispatchKH = DeliveryDispatch::updateOrCreate(
            ['code' => 'CX-2026-082'],
            [
                'vehicle_id' => $veh88?->id ?? 1,
                'driver_id' => $driverThanh->id,
                'status' => 'Chờ xuất bến',
                'departure_time' => null,
                'return_time' => null,
                'route_summary' => 'Kho Mê Linh -> TP. Vinh -> Nam Đàn (Nghệ An)',
                'total_weight_kg' => 2100,
                'total_distance_km' => 310,
                'details' => [
                    'tripCode' => 'CX-2026-082',
                    'routeName' => 'Kho Mê Linh -> TP. Vinh -> Nam Đàn (Nghệ An)',
                    'departureWarehouseId' => 'wh-001',
                    'departureWarehouseName' => 'Kho Tổng Mê Linh (MQ)',
                    'vehicleId' => (string) ($veh88?->id ?? 1),
                    'vehiclePlate' => '29C-888.99',
                    'mainDriverId' => (string) $driverThanh->id,
                    'mainDriverName' => 'Bùi Văn Thành',
                    'dispatchDate' => now()->toDateString(),
                    'departureTime' => null,
                    'status' => 'Chờ xuất bến',
                ]
            ]
        );

        // CHUYẾN 2: CX-2026-081 trên XE 29H-555.44
        // - Trạng thái: "Đang giao hàng" (Xe ĐANG DI CHUYỂN, đã xuất bến lúc 08:30 sáng nay)
        // - Đơn hàng: CHỈ CÓ 2 đơn ĐANG VẬN CHUYỂN (DH-2026-VC01, DH-2026-VC02)
        // - TUYỆT ĐỐI KHÔNG CÓ đơn nào đã giao trên xe này!
        $dispatchVC = DeliveryDispatch::updateOrCreate(
            ['code' => 'CX-2026-081'],
            [
                'vehicle_id' => $veh55?->id ?? 3,
                'driver_id' => $driverTrong->id,
                'status' => 'Đang giao hàng',
                'departure_time' => now()->setTime(8, 30),
                'return_time' => null,
                'route_summary' => 'Hà Nội -> Nghi Lộc -> Diễn Châu (QL1A)',
                'total_weight_kg' => 2850,
                'total_distance_km' => 295,
                'details' => [
                    'tripCode' => 'CX-2026-081',
                    'routeName' => 'Hà Nội -> Nghi Lộc -> Diễn Châu (QL1A)',
                    'departureWarehouseId' => 'wh-001',
                    'departureWarehouseName' => 'Kho Tổng Mê Linh (MQ)',
                    'vehicleId' => (string) ($veh55?->id ?? 3),
                    'vehiclePlate' => '29H-555.44',
                    'mainDriverId' => (string) $driverTrong->id,
                    'mainDriverName' => 'Vũ Đình Trọng',
                    'dispatchDate' => now()->toDateString(),
                    'departureTime' => '08:30',
                    'status' => 'Đang giao hàng',
                ]
            ]
        );

        // CHUYẾN 3: CX-2026-080 trên XE 29C-777.66
        // - Trạng thái: "Đã về bến (Đã quyết toán)" (Chuyến xe hôm qua đã giao xong 100% và đã về bến)
        // - Đơn hàng: CẢ 3 ĐƠN ĐÃ GIAO (DH-2026-DG01, DH-2026-DG02, DH-2026-DG03)
        $dispatchDone = DeliveryDispatch::updateOrCreate(
            ['code' => 'CX-2026-080'],
            [
                'vehicle_id' => $veh77?->id ?? 2,
                'driver_id' => $driverHung->id,
                'status' => 'Đã về bến (Đã quyết toán)',
                'departure_time' => now()->subDay()->setTime(7, 30),
                'return_time' => now()->subDay()->setTime(17, 00),
                'route_summary' => 'Hà Nội -> TP. Vinh -> Nam Đàn -> TP. Hà Tĩnh',
                'total_weight_kg' => 3200,
                'total_distance_km' => 360,
                'details' => [
                    'tripCode' => 'CX-2026-080',
                    'routeName' => 'Hà Nội -> TP. Vinh -> Nam Đàn -> TP. Hà Tĩnh',
                    'departureWarehouseId' => 'wh-001',
                    'departureWarehouseName' => 'Kho Tổng Mê Linh (MQ)',
                    'vehicleId' => (string) ($veh77?->id ?? 2),
                    'vehiclePlate' => '29C-777.66',
                    'mainDriverId' => (string) $driverHung->id,
                    'mainDriverName' => 'Phạm Quốc Hùng',
                    'dispatchDate' => now()->subDay()->toDateString(),
                    'departureTime' => '07:30',
                    'status' => 'Đã về bến (Đã quyết toán)',
                ]
            ]
        );

        // Gán đơn vào chuyến xe qua bảng trung gian delivery_dispatch_orders
        // 1. Xe 29C-888.99 (Chờ xuất bến - Chỉ 2 đơn Đã kiểm hàng):
        if (isset($createdOrders['DH-2026-KH01'])) {
            DB::table('delivery_dispatch_orders')->insert([
                'delivery_dispatch_id' => $dispatchKH->id,
                'order_id' => $createdOrders['DH-2026-KH01']->id,
                'delivery_order' => 1,
                'status' => 'Chờ giao hàng',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (isset($createdOrders['DH-2026-KH02'])) {
            DB::table('delivery_dispatch_orders')->insert([
                'delivery_dispatch_id' => $dispatchKH->id,
                'order_id' => $createdOrders['DH-2026-KH02']->id,
                'delivery_order' => 2,
                'status' => 'Chờ giao hàng',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Xe 29H-555.44 (Đang di chuyển - Chỉ 2 đơn Đang vận chuyển):
        if (isset($createdOrders['DH-2026-VC01'])) {
            DB::table('delivery_dispatch_orders')->insert([
                'delivery_dispatch_id' => $dispatchVC->id,
                'order_id' => $createdOrders['DH-2026-VC01']->id,
                'delivery_order' => 1,
                'status' => 'Đang giao hàng',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (isset($createdOrders['DH-2026-VC02'])) {
            DB::table('delivery_dispatch_orders')->insert([
                'delivery_dispatch_id' => $dispatchVC->id,
                'order_id' => $createdOrders['DH-2026-VC02']->id,
                'delivery_order' => 2,
                'status' => 'Đang giao hàng',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Xe 29C-777.66 (Chuyến hôm qua đã hoàn thành - 3 đơn Đã giao):
        if (isset($createdOrders['DH-2026-DG01'])) {
            DB::table('delivery_dispatch_orders')->insert([
                'delivery_dispatch_id' => $dispatchDone->id,
                'order_id' => $createdOrders['DH-2026-DG01']->id,
                'delivery_order' => 1,
                'status' => 'Đã giao',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);
        }
        if (isset($createdOrders['DH-2026-DG02'])) {
            DB::table('delivery_dispatch_orders')->insert([
                'delivery_dispatch_id' => $dispatchDone->id,
                'order_id' => $createdOrders['DH-2026-DG02']->id,
                'delivery_order' => 2,
                'status' => 'Đã giao',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);
        }
        if (isset($createdOrders['DH-2026-DG03'])) {
            DB::table('delivery_dispatch_orders')->insert([
                'delivery_dispatch_id' => $dispatchDone->id,
                'order_id' => $createdOrders['DH-2026-DG03']->id,
                'delivery_order' => 3,
                'status' => 'Đã giao',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);
        }

        // =========================================================================
        // 6. TÍNH TOÁN VÀ ĐỒNG BỘ CHÍNH XÁC CÔNG NỢ 100% CHO TẤT CẢ CÁC ĐẠI LÝ
        // =========================================================================
        $allDealers = Dealer::all();
        foreach ($allDealers as $dl) {
            $dl->recalculateCurrentDebt();
        }
    }
}
