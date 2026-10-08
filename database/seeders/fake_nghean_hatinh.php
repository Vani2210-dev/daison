<?php

use Illuminate\Support\Facades\DB;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\DealerCareLog;
use App\Models\Transaction;

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::beginTransaction();

try {
    echo "1. Đang dọn dẹp dữ liệu đơn hàng và khách hàng cũ...\n";

    // Xóa liên kết hoặc các bảng phụ thuộc trước
    DB::table('dealer_care_logs')->delete();
    DB::table('order_items')->delete();
    DB::table('picking_sync_events')->delete();
    
    // Nếu có transactions liên quan tới order cũ, cập nhật order_id = null
    DB::table('transactions')->update(['order_id' => null]);
    DB::table('transactions')->delete(); // Xóa transaction công nợ cũ

    DB::table('orders')->delete();
    DB::table('dealers')->delete();

    // Reset auto-increment nếu được
    try {
        DB::statement("ALTER TABLE order_items AUTO_INCREMENT = 1");
        DB::statement("ALTER TABLE orders AUTO_INCREMENT = 1");
        DB::statement("ALTER TABLE dealers AUTO_INCREMENT = 1");
    } catch (\Throwable $e) {}

    echo "2. Đang tạo danh sách khách hàng / đại lý Nghệ An & Hà Tĩnh...\n";

    $salesRepIds = User::whereIn('id', [5, 6, 8])->pluck('id')->toArray();
    if (empty($salesRepIds)) {
        $salesRepIds = [User::first()->id ?? 1];
    }

    $dealersData = [
        // --- NGHỆ AN ---
        [
            'code' => 'DL-NA-001',
            'name' => 'Đại Lý Điện Nước Thành Vinh (TP. Vinh)',
            'owner_name' => 'Nguyễn Cảnh Vinh',
            'phone' => '0912 345 888',
            'tax_code' => '2901889901',
            'address' => '78 Đường Lê Lợi, Phường Hưng Bình, TP. Vinh, Nghệ An',
            'district' => 'TP. Vinh',
            'city' => 'Nghệ An',
            'tier' => 'Đại lý Cấp 1',
            'credit_limit' => 300000000,
            'initial_debt' => 0,
            'current_debt' => 45000000,
            'payment_term_days' => 30,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[0],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-NA-002',
            'name' => 'Cửa Hàng Ống Nhựa Diễn Châu (Ngã 3 Cầu Bùng)',
            'owner_name' => 'Trần Văn Tiến',
            'phone' => '0988 567 123',
            'tax_code' => '2901889902',
            'address' => 'Khối 3, Thị trấn Diễn Châu, Huyện Diễn Châu, Nghệ An',
            'district' => 'Huyện Diễn Châu',
            'city' => 'Nghệ An',
            'tier' => 'Đại lý Cấp 1',
            'credit_limit' => 200000000,
            'initial_debt' => 0,
            'current_debt' => 62500000,
            'payment_term_days' => 45,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[0],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-NA-003',
            'name' => 'Nhà Thầu Cơ Điện & Nước Nghi Lộc',
            'owner_name' => 'Phạm Đình Hùng',
            'phone' => '0977 234 567',
            'tax_code' => '2901889903',
            'address' => 'KCN Nam Cấm, Xã Nghi Xá, Huyện Nghi Lộc, Nghệ An',
            'district' => 'Huyện Nghi Lộc',
            'city' => 'Nghệ An',
            'tier' => 'Khách Dự Án',
            'credit_limit' => 500000000,
            'initial_debt' => 0,
            'current_debt' => 115000000,
            'payment_term_days' => 60,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[min(1, count($salesRepIds)-1)],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-NA-004',
            'name' => 'Đại Lý Vật Tư Cửa Lò - Lam Giang',
            'owner_name' => 'Lê Thị Lam',
            'phone' => '0966 889 900',
            'tax_code' => '2901889904',
            'address' => '125 Đường Bình Minh, Phường Nghi Hương, Thị xã Cửa Lò, Nghệ An',
            'district' => 'Thị xã Cửa Lò',
            'city' => 'Nghệ An',
            'tier' => 'Đại lý Cấp 2',
            'credit_limit' => 150000000,
            'initial_debt' => 0,
            'current_debt' => 28000000,
            'payment_term_days' => 30,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[min(1, count($salesRepIds)-1)],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-NA-005',
            'name' => 'Cửa Hàng Điện Dân Dụng Hưng Nguyên',
            'owner_name' => 'Ngô Văn Khang',
            'phone' => '0904 112 233',
            'tax_code' => '2901889905',
            'address' => 'Khối 6, Thị trấn Hưng Nguyên, Huyện Hưng Nguyên, Nghệ An',
            'district' => 'Huyện Hưng Nguyên',
            'city' => 'Nghệ An',
            'tier' => 'Đại lý Cấp 2',
            'credit_limit' => 100000000,
            'initial_debt' => 0,
            'current_debt' => 18500000,
            'payment_term_days' => 30,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[0],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-NA-006',
            'name' => 'Đại Lý Nhựa Tiền Phong Nam Đàn',
            'owner_name' => 'Võ Đình Toàn',
            'phone' => '0983 456 789',
            'tax_code' => '2901889906',
            'address' => 'Thị trấn Nam Đàn, Huyện Nam Đàn, Nghệ An',
            'district' => 'Huyện Nam Đàn',
            'city' => 'Nghệ An',
            'tier' => 'Đại lý Cấp 1',
            'credit_limit' => 250000000,
            'initial_debt' => 0,
            'current_debt' => 37000000,
            'payment_term_days' => 45,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[0],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],

        // --- HÀ TĨNH ---
        [
            'code' => 'DL-HT-001',
            'name' => 'Đại Lý Điện Nước Hà Tĩnh - Thành Sen',
            'owner_name' => 'Phan Nhật Minh',
            'phone' => '0915 789 123',
            'tax_code' => '3001998801',
            'address' => '245 Đường Hà Huy Tập, Phường Nam Hà, TP. Hà Tĩnh, Hà Tĩnh',
            'district' => 'TP. Hà Tĩnh',
            'city' => 'Hà Tĩnh',
            'tier' => 'Đại lý Cấp 1',
            'credit_limit' => 350000000,
            'initial_debt' => 0,
            'current_debt' => 54000000,
            'payment_term_days' => 30,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[min(2, count($salesRepIds)-1)],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-HT-002',
            'name' => 'Cửa Hàng Vật Tư Ống Nước Nghi Xuân',
            'owner_name' => 'Bùi Đức Thắng',
            'phone' => '0982 345 678',
            'tax_code' => '3001998802',
            'address' => 'Thị trấn Tiên Điền, Huyện Nghi Xuân, Hà Tĩnh',
            'district' => 'Huyện Nghi Xuân',
            'city' => 'Hà Tĩnh',
            'tier' => 'Đại lý Cấp 1',
            'credit_limit' => 200000000,
            'initial_debt' => 0,
            'current_debt' => 32000000,
            'payment_term_days' => 30,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[min(2, count($salesRepIds)-1)],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-HT-003',
            'name' => 'Tổng Kho Điện Nước Hồng Lĩnh',
            'owner_name' => 'Nguyễn Hữu Chiến',
            'phone' => '0979 667 889',
            'tax_code' => '3001998803',
            'address' => '156 Đường Quang Trung, Phường Bắc Hồng, Thị xã Hồng Lĩnh, Hà Tĩnh',
            'district' => 'Thị xã Hồng Lĩnh',
            'city' => 'Hà Tĩnh',
            'tier' => 'Đại lý Cấp 1',
            'credit_limit' => 300000000,
            'initial_debt' => 0,
            'current_debt' => 48500000,
            'payment_term_days' => 45,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[min(2, count($salesRepIds)-1)],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-HT-004',
            'name' => 'Nhà Thầu Cơ Điện KKT Vũng Áng (Kỳ Anh)',
            'owner_name' => 'Đặng Văn Lộc',
            'phone' => '0968 123 999',
            'tax_code' => '3001998804',
            'address' => 'KKT Vũng Áng, Phường Kỳ Long, Thị xã Kỳ Anh, Hà Tĩnh',
            'district' => 'Thị xã Kỳ Anh',
            'city' => 'Hà Tĩnh',
            'tier' => 'Khách Dự Án',
            'credit_limit' => 600000000,
            'initial_debt' => 0,
            'current_debt' => 140000000,
            'payment_term_days' => 60,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[min(2, count($salesRepIds)-1)],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-HT-005',
            'name' => 'Cửa Hàng Thiết Bị Nước Can Lộc',
            'owner_name' => 'Hoàng Văn Quý',
            'phone' => '0913 556 778',
            'tax_code' => '3001998805',
            'address' => 'Thị trấn Nghèn, Huyện Can Lộc, Hà Tĩnh',
            'district' => 'Huyện Can Lộc',
            'city' => 'Hà Tĩnh',
            'tier' => 'Đại lý Cấp 2',
            'credit_limit' => 120000000,
            'initial_debt' => 0,
            'current_debt' => 15000000,
            'payment_term_days' => 30,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[min(2, count($salesRepIds)-1)],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
        [
            'code' => 'DL-HT-006',
            'name' => 'Đại Lý Ống PVC Cẩm Xuyên - Đức Phát',
            'owner_name' => 'Lê Hữu Đức',
            'phone' => '0905 443 322',
            'tax_code' => '3001998806',
            'address' => 'Thị trấn Cẩm Xuyên, Huyện Cẩm Xuyên, Hà Tĩnh',
            'district' => 'Huyện Cẩm Xuyên',
            'city' => 'Hà Tĩnh',
            'tier' => 'Đại lý Cấp 2',
            'credit_limit' => 120000000,
            'initial_debt' => 0,
            'current_debt' => 21000000,
            'payment_term_days' => 30,
            'overdue_days' => 0,
            'sales_rep_id' => $salesRepIds[min(2, count($salesRepIds)-1)],
            'status' => 'Hoạt động',
            'truck_accessible' => true,
        ],
    ];

    $createdDealers = [];
    foreach ($dealersData as $d) {
        $createdDealers[] = Dealer::create($d);
    }
    echo "   -> Đã tạo thành công " . count($createdDealers) . " đại lý tại Nghệ An & Hà Tĩnh!\n";

    echo "3. Đang tạo các đơn hàng mới liên kết theo các tuyến Nghệ An - Hà Tĩnh...\n";

    $products = Product::all();
    if ($products->isEmpty()) {
        throw new \Exception("Không tìm thấy sản phẩm trong hệ thống!");
    }

    $vehicles = DB::table('vehicles')->get();
    $v1 = $vehicles->where('plate_number', '29C-888.99')->first() ?? $vehicles->first();
    $v2 = $vehicles->where('plate_number', '29C-777.66')->first() ?? $vehicles->first();

    $ordersConfig = [
        // TUYẾN NGHỆ AN 1 (Hưng Nguyên - Nam Đàn)
        [
            'dealer_idx' => 4, // Hưng Nguyên
            'order_code' => 'DH-2026-NA01',
            'status' => 'Chốt đơn',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'VietQR',
            'notes' => 'Tuyến 01: Giao Hưng Nguyên - Nghệ An sáng sớm',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 0, 'qty' => 400], // Ống D21
                ['p_idx' => 2, 'qty' => 200], // Co 90 D21
                ['p_idx' => 3, 'qty' => 5],   // Dây điện Cadivi
            ]
        ],
        [
            'dealer_idx' => 5, // Nam Đàn
            'order_code' => 'DH-2026-NA02',
            'status' => 'Kho đã xuất đủ',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'Tiền mặt',
            'notes' => 'Tuyến 01: Nam Đàn - đường rộng xe 5 tấn vào tận kho',
            'assigned_vehicle_plate' => $v1 ? $v1->plate_number : null,
            'driver_id' => $v1 ? $v1->driver_id : null,
            'items' => [
                ['p_idx' => 1, 'qty' => 120], // Ống D110
                ['p_idx' => 4, 'qty' => 50],  // Aptomat Sino
            ]
        ],

        // TUYẾN NGHỆ AN 2 & 3 (Vinh, Nghi Lộc, Diễn Châu, Cửa Lò)
        [
            'dealer_idx' => 0, // TP Vinh
            'order_code' => 'DH-2026-NA03',
            'status' => 'Kho đã xuất đủ',
            'payment_status' => 'Đã thanh toán',
            'payment_method' => 'VietQR',
            'notes' => 'Tuyến Nghệ An: Trung tâm TP. Vinh - mặt đường Lê Lợi',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 0, 'qty' => 600],
                ['p_idx' => 1, 'qty' => 100],
                ['p_idx' => 3, 'qty' => 15],
            ]
        ],
        [
            'dealer_idx' => 1, // Diễn Châu
            'order_code' => 'DH-2026-NA04',
            'status' => 'Đã kiểm hàng',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'Chuyển khoản',
            'notes' => 'Tuyến Nghệ An 3: Diễn Châu - trả hàng tại Ngã 3 Cầu Bùng',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 1, 'qty' => 200],
                ['p_idx' => 0, 'qty' => 300],
            ]
        ],
        [
            'dealer_idx' => 2, // Nghi Lộc
            'order_code' => 'DH-2026-NA05',
            'status' => 'Chốt đơn',
            'payment_status' => 'Đã cọc',
            'payment_method' => 'Chuyển khoản',
            'notes' => 'Tuyến Nghệ An 3: Dự án công trình KCN Nam Cấm Nghi Lộc',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 1, 'qty' => 350],
                ['p_idx' => 3, 'qty' => 25],
            ]
        ],
        [
            'dealer_idx' => 3, // Cửa Lò
            'order_code' => 'DH-2026-NA06',
            'status' => 'Đã kiểm hàng',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'VietQR',
            'notes' => 'Tuyến Nghệ An 2: Thị xã Cửa Lò - đường Bình Minh',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 0, 'qty' => 500],
                ['p_idx' => 2, 'qty' => 150],
                ['p_idx' => 4, 'qty' => 40],
            ]
        ],

        // TUYẾN HÀ TĨNH (TP. Hà Tĩnh, Nghi Xuân, Hồng Lĩnh, Can Lộc, Cẩm Xuyên, Kỳ Anh)
        [
            'dealer_idx' => 6, // TP Hà Tĩnh
            'order_code' => 'DH-2026-HT01',
            'status' => 'Kho đã xuất đủ',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'VietQR',
            'notes' => 'Tuyến Hà Tĩnh 2: Trung tâm TP. Hà Tĩnh - đường Hà Huy Tập',
            'assigned_vehicle_plate' => $v2 ? $v2->plate_number : null,
            'driver_id' => $v2 ? $v2->driver_id : null,
            'items' => [
                ['p_idx' => 0, 'qty' => 500],
                ['p_idx' => 1, 'qty' => 150],
                ['p_idx' => 3, 'qty' => 12],
            ]
        ],
        [
            'dealer_idx' => 7, // Nghi Xuân
            'order_code' => 'DH-2026-HT02',
            'status' => 'Chốt đơn',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'Tiền mặt',
            'notes' => 'Tuyến Hà Tĩnh 1: Huyện Nghi Xuân - gần cầu Bến Thủy',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 0, 'qty' => 400],
                ['p_idx' => 2, 'qty' => 300],
            ]
        ],
        [
            'dealer_idx' => 8, // Hồng Lĩnh
            'order_code' => 'DH-2026-HT03',
            'status' => 'Đã kiểm hàng',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'VietQR',
            'notes' => 'Tuyến Hà Tĩnh: Thị xã Hồng Lĩnh - mặt đường QL1A',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 1, 'qty' => 180],
                ['p_idx' => 4, 'qty' => 80],
                ['p_idx' => 3, 'qty' => 8],
            ]
        ],
        [
            'dealer_idx' => 9, // Kỳ Anh - Vũng Áng
            'order_code' => 'DH-2026-HT04',
            'status' => 'Chốt đơn',
            'payment_status' => 'Đã cọc',
            'payment_method' => 'Chuyển khoản',
            'notes' => 'Tuyến Hà Tĩnh: Dự án KKT Vũng Áng Kỳ Anh - xe cẩu hoặc tải nặng',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 1, 'qty' => 450],
                ['p_idx' => 3, 'qty' => 30],
            ]
        ],
        [
            'dealer_idx' => 10, // Can Lộc
            'order_code' => 'DH-2026-HT05',
            'status' => 'Kho đã xuất đủ',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'Tiền mặt',
            'notes' => 'Tuyến Hà Tĩnh: Can Lộc - giao thị trấn Nghèn',
            'assigned_vehicle_plate' => null,
            'driver_id' => null,
            'items' => [
                ['p_idx' => 0, 'qty' => 350],
                ['p_idx' => 2, 'qty' => 100],
            ]
        ],
        [
            'dealer_idx' => 11, // Cẩm Xuyên
            'order_code' => 'DH-2026-HT06',
            'status' => 'Đang vận chuyển',
            'payment_status' => 'Chưa thanh toán',
            'payment_method' => 'VietQR',
            'notes' => 'Tuyến Hà Tĩnh: Huyện Cẩm Xuyên - giao trước 17h',
            'assigned_vehicle_plate' => $v2 ? $v2->plate_number : null,
            'driver_id' => $v2 ? $v2->driver_id : null,
            'items' => [
                ['p_idx' => 0, 'qty' => 300],
                ['p_idx' => 1, 'qty' => 80],
                ['p_idx' => 4, 'qty' => 60],
            ]
        ],
    ];

    $createdOrdersCount = 0;
    foreach ($ordersConfig as $cfg) {
        $dealer = $createdDealers[$cfg['dealer_idx']];

        $subtotal = 0;
        $orderItemsToCreate = [];

        foreach ($cfg['items'] as $it) {
            $product = $products->get($it['p_idx']) ?? $products->first();
            $qty = $it['qty'];
            
            // Tính giá theo tier đại lý
            $unitPrice = (float) $product->cost_price * 1.15; // Mặc định markup 15%
            if (!empty($product->price_tiers)) {
                foreach ($product->price_tiers as $pt) {
                    if (str_contains(mb_strtolower($dealer->tier), mb_strtolower($pt['name'] ?? ''))) {
                        $unitPrice = (float) $pt['price'];
                        break;
                    }
                }
            }

            $lineTotal = $qty * $unitPrice;
            $subtotal += $lineTotal;

            $orderItemsToCreate[] = [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'unit' => $product->unit,
                'quantity' => $qty,
                'cost_price' => $product->cost_price,
                'unit_price' => $unitPrice,
                'original_price' => $unitPrice * 1.05,
                'applied_tier_policy' => $dealer->tier,
                'amount' => $lineTotal,
            ];
        }

        $discount = round($subtotal * 0.05); // Chiết khấu 5%
        $tax = round(($subtotal - $discount) * 0.08); // VAT 8%
        $totalAmount = $subtotal - $discount + $tax;

        $order = Order::create([
            'order_code' => $cfg['order_code'],
            'dealer_id' => $dealer->id,
            'creator_id' => 1,
            'order_creator' => 'Admin KBTech',
            'customer_name' => $dealer->name,
            'customer_phone' => $dealer->phone,
            'customer_address' => $dealer->address,
            'warehouse' => 'Kho Tổng Mê Linh (MQ)',
            'sales_rep_id' => $dealer->sales_rep_id,
            'sales_accountant_id' => 4,
            'warehouse_staff_id' => 7,
            'assigned_driver_id' => $cfg['driver_id'],
            'assigned_vehicle_plate' => $cfg['assigned_vehicle_plate'],
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $totalAmount,
            'status' => $cfg['status'],
            'payment_status' => $cfg['payment_status'],
            'payment_method' => $cfg['payment_method'],
            'driver_debt_amount' => 0,
            'settled_with_accountant' => false,
            'order_type' => 'Đơn bán đại lý',
            'notes' => $cfg['notes'],
        ]);

        foreach ($orderItemsToCreate as $itData) {
            $itData['order_id'] = $order->id;
            OrderItem::create($itData);
        }

        $createdOrdersCount++;
    }

    echo "   -> Đã tạo thành công {$createdOrdersCount} đơn hàng mới tại Nghệ An & Hà Tĩnh!\n";

    DB::commit();
    echo "\n=== HOÀN TẤT THÀNH CÔNG QUÁ TRÌNH FAKE DATA! ===\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "LỖI: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
