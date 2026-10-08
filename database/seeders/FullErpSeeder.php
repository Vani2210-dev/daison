<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Dealer;
use App\Models\DealerCareLog;
use App\Models\FinancialFund;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayrollRecord;
use App\Models\Product;
use App\Models\Proposal;
use App\Models\ProposalStep;
use App\Models\Role;
use App\Models\TaskAssignment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FullErpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. USERS & ROLES
        $staffMembers = [
            [
                'code' => 'NV-001',
                'name' => 'Nguyễn Văn Hùng',
                'role' => 'Manager',
                'department' => 'Executive',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150',
                'phone' => '0988 123 456',
                'email' => 'hung.nguyen@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 35000000,
                'branch' => 'Trụ sở Chính - Vinh',
                'bank_account' => '190288888888',
                'bank_name' => 'Techcombank',
                'address' => 'Số 12 Lê Lợi, TP Vinh, Nghệ An',
                'affiliate_code' => 'AFF-NV001',
            ],
            [
                'code' => 'NV-002',
                'name' => 'Trần Thị Thu Hà',
                'role' => 'Sales_Accountant',
                'department' => 'Accounting',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150',
                'phone' => '0912 345 678',
                'email' => 'ha.tran@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 18000000,
                'branch' => 'Trụ sở Chính - Vinh',
                'bank_account' => '1012345678',
                'bank_name' => 'Vietcombank',
                'address' => 'Chung cư Bảo Sơn, TP Vinh',
                'affiliate_code' => 'AFF-NV002',
            ],
            [
                'code' => 'NV-003',
                'name' => 'Phạm Minh Tuấn',
                'role' => 'Sales_Rep',
                'department' => 'Sales',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150',
                'phone' => '0903 888 999',
                'email' => 'tuan.pham@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 12000000,
                'branch' => 'Chi nhánh Miền Trung',
                'bank_account' => '0903888999',
                'bank_name' => 'MBBank',
                'address' => '45 Nguyễn Thị Minh Khai, TP Vinh',
                'affiliate_code' => 'AFF-NV003',
            ],
            [
                'code' => 'NV-004',
                'name' => 'Lê Văn Trung',
                'role' => 'Warehouse_Manager',
                'department' => 'Warehouse',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150',
                'phone' => '0977 654 321',
                'email' => 'trung.le@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 16000000,
                'branch' => 'Kho Tổng Nghi Kim',
                'bank_account' => '0977654321',
                'bank_name' => 'BIDV',
                'address' => 'Nghi Kim, TP Vinh, Nghệ An',
                'affiliate_code' => 'AFF-NV004',
            ],
            [
                'code' => 'NV-005',
                'name' => 'Hoàng Minh Đức',
                'role' => 'Warehouse_Staff',
                'department' => 'Warehouse',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150',
                'phone' => '0934 112 233',
                'email' => 'duc.hoang@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 9500000,
                'branch' => 'Kho Tổng Nghi Kim',
                'bank_account' => '0934112233',
                'bank_name' => 'Agribank',
                'address' => 'Xã Hưng Lộc, TP Vinh',
                'affiliate_code' => 'AFF-NV005',
            ],
            [
                'code' => 'NV-006',
                'name' => 'Bùi Văn Thành',
                'role' => 'Driver',
                'department' => 'Logistics',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150',
                'phone' => '0966 334 455',
                'email' => 'thanh.bui@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 13500000,
                'branch' => 'Đội xe Vận Tải',
                'bank_account' => '0966334455',
                'bank_name' => 'VPBank',
                'address' => 'Khối 8, Phường Quán Bàu, TP Vinh',
                'affiliate_code' => 'AFF-NV006',
            ],
            [
                'code' => 'NV-007',
                'name' => 'Đỗ Phương Mai',
                'role' => 'Finance_Accountant',
                'department' => 'Accounting',
                'avatar' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150',
                'phone' => '0945 778 899',
                'email' => 'mai.do@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 17000000,
                'branch' => 'Trụ sở Chính - Vinh',
                'bank_account' => '0945778899',
                'bank_name' => 'Techcombank',
                'address' => 'Phường Hưng Dũng, TP Vinh',
                'affiliate_code' => 'AFF-NV007',
            ],
            // 7 TÀI KHOẢN LÁI XE & PHỤ XE MỚI ĐƯỢC BỔ SUNG
            [
                'code' => 'NV-011',
                'name' => 'Lê Văn Nam',
                'role' => 'Driver',
                'department' => 'Logistics',
                'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150',
                'phone' => '0971 111 222',
                'email' => 'nam.le@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 11000000,
                'branch' => 'Đội xe Vận Tải',
                'bank_account' => '0971111222',
                'bank_name' => 'VPBank',
                'address' => 'Khối 3, Phường Lê Lợi, TP Vinh',
                'affiliate_code' => 'AFF-NV011',
            ],
            [
                'code' => 'NV-012',
                'name' => 'Nguyễn Văn Đạt',
                'role' => 'Driver',
                'department' => 'Logistics',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150',
                'phone' => '0972 222 333',
                'email' => 'dat.nguyen@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 14000000,
                'branch' => 'Đội xe Vận Tải',
                'bank_account' => '0972222333',
                'bank_name' => 'MBBank',
                'address' => 'Khối 5, Phường Đông Vĩnh, TP Vinh',
                'affiliate_code' => 'AFF-NV012',
            ],
            [
                'code' => 'NV-013',
                'name' => 'Phạm Quốc Hùng',
                'role' => 'Driver',
                'department' => 'Logistics',
                'avatar' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=150',
                'phone' => '0973 333 444',
                'email' => 'hung.pham@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 11500000,
                'branch' => 'Đội xe Vận Tải',
                'bank_account' => '0973333444',
                'bank_name' => 'Techcombank',
                'address' => 'Khối 2, Phường Trường Thi, TP Vinh',
                'affiliate_code' => 'AFF-NV013',
            ],
            [
                'code' => 'NV-014',
                'name' => 'Trần Văn Kiên',
                'role' => 'Driver',
                'department' => 'Logistics',
                'avatar' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=150',
                'phone' => '0974 444 555',
                'email' => 'kien.tran@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 15000000,
                'branch' => 'Đội xe Vận Tải',
                'bank_account' => '0974444555',
                'bank_name' => 'Vietcombank',
                'address' => 'Khối 9, Phường Bến Thủy, TP Vinh',
                'affiliate_code' => 'AFF-NV014',
            ],
            [
                'code' => 'NV-015',
                'name' => 'Vũ Đình Trọng',
                'role' => 'Driver',
                'department' => 'Logistics',
                'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150',
                'phone' => '0975 555 666',
                'email' => 'trong.vu@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 11500000,
                'branch' => 'Đội xe Vận Tải',
                'bank_account' => '0975555666',
                'bank_name' => 'Agribank',
                'address' => 'Khối 4, Phường Cửa Nam, TP Vinh',
                'affiliate_code' => 'AFF-NV015',
            ],
            [
                'code' => 'NV-016',
                'name' => 'Đoàn Minh Tuấn',
                'role' => 'Driver',
                'department' => 'Logistics',
                'avatar' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=150',
                'phone' => '0976 666 777',
                'email' => 'tuan.doan@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 13000000,
                'branch' => 'Đội xe Vận Tải',
                'bank_account' => '0976666777',
                'bank_name' => 'BIDV',
                'address' => 'Khối 7, Phường Hưng Bình, TP Vinh',
                'affiliate_code' => 'AFF-NV016',
            ],
            [
                'code' => 'NV-017',
                'name' => 'Hoàng Văn Cường',
                'role' => 'Driver',
                'department' => 'Logistics',
                'avatar' => 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=150',
                'phone' => '0977 777 888',
                'email' => 'cuong.hoang@pvc-erp.vn',
                'status' => 'Đang làm việc',
                'base_salary' => 11000000,
                'branch' => 'Đội xe Vận Tải',
                'bank_account' => '0977777888',
                'bank_name' => 'Vietinbank',
                'address' => 'Khối 1, Phường Quán Bàu, TP Vinh',
                'affiliate_code' => 'AFF-NV017',
            ],
        ];

        $usersMap = [];
        foreach ($staffMembers as $member) {
            $user = User::updateOrCreate(
                ['email' => $member['email']],
                [
                    'code' => $member['code'],
                    'name' => $member['name'],
                    'password' => Hash::make('password123'),
                    'department' => $member['department'],
                    'avatar' => $member['avatar'],
                    'phone' => $member['phone'],
                    'status' => $member['status'],
                    'base_salary' => $member['base_salary'],
                    'branch' => $member['branch'],
                    'bank_account' => $member['bank_account'],
                    'bank_name' => $member['bank_name'],
                    'address' => $member['address'],
                    'affiliate_code' => $member['affiliate_code'],
                    'email_verified_at' => now(),
                ]
            );

            // Gán vai trò
            $roleModel = Role::where('slug', $member['role'])->first();
            if ($roleModel) {
                $user->assignRoleWithPermissions($roleModel);
            }
            $usersMap[$member['code']] = $user;
        }

        // Đảm bảo admin@kbtech.com luôn tồn tại
        $admin = User::updateOrCreate(
            ['email' => 'admin@kbtech.com'],
            [
                'code' => 'NV-ADMIN',
                'name' => 'Admin KBTech',
                'password' => Hash::make('password123'),
                'department' => 'Executive',
                'status' => 'Đang làm việc',
                'base_salary' => 35000000,
                'phone' => '0988.888.999',
                'email_verified_at' => now(),
            ]
        );
        $managerRole = Role::where('slug', 'Manager')->first();
        if ($managerRole) {
            $admin->assignRoleWithPermissions($managerRole);
        }

        // 2. FINANCIAL FUNDS
        $funds = [
            ['code' => 'FUND_CASH', 'name' => 'Quỹ Tiền Mặt', 'account_number' => 'TM-01', 'bank_name' => 'Két Sắt Trụ Sở Chính', 'current_balance' => 450000000],
            ['code' => 'FUND_MB', 'name' => 'Tài Khoản MBBank', 'account_number' => '0903888999', 'bank_name' => 'Ngân hàng Quân Đội MBBank', 'current_balance' => 1250000000],
            ['code' => 'FUND_VCB', 'name' => 'Tài Khoản Vietcombank', 'account_number' => '1012345678', 'bank_name' => 'Ngân hàng Ngoại Thương Vietcombank', 'current_balance' => 890000000],
        ];
        $fundsMap = [];
        foreach ($funds as $f) {
            $fundsMap[$f['name']] = FinancialFund::updateOrCreate(['code' => $f['code']], $f);
        }

        // 3. VEHICLES (4 xe x 2 nhân sự = 8 nhân sự lái xe & phụ xe)
        $vehicles = [
            [
                'plate_number' => '29C-888.99',
                'vehicle_type' => 'Xe tải Isuzu 2.5T',
                'driver_code'  => 'NV-006', // Bùi Văn Thành (Giữ nguyên)
                'copilot_name' => 'Lê Văn Nam', // NV-011
                'capacity_kg'  => 2500,
                'km_odometer'  => 45200,
                'status'       => 'Sẵn sàng'
            ],
            [
                'plate_number' => '29C-777.66',
                'vehicle_type' => 'Xe tải Hyundai 5T',
                'driver_code'  => 'NV-012', // Nguyễn Văn Đạt
                'copilot_name' => 'Phạm Quốc Hùng', // NV-013
                'capacity_kg'  => 5000,
                'km_odometer'  => 68500,
                'status'       => 'Sẵn sàng'
            ],
            [
                'plate_number' => '29H-555.44',
                'vehicle_type' => 'Xe cẩu tự hành Hino 8T',
                'driver_code'  => 'NV-014', // Trần Văn Kiên
                'copilot_name' => 'Vũ Đình Trọng', // NV-015
                'capacity_kg'  => 8000,
                'km_odometer'  => 32100,
                'status'       => 'Đang di chuyển'
            ],
            [
                'plate_number' => '29H-333.22',
                'vehicle_type' => 'Xe bán tải Ford Ranger',
                'driver_code'  => 'NV-016', // Đoàn Minh Tuấn
                'copilot_name' => 'Hoàng Văn Cường', // NV-017
                'capacity_kg'  => 1000,
                'km_odometer'  => 18900,
                'status'       => 'Sẵn sàng'
            ],
        ];

        $vehiclesMap = [];
        foreach ($vehicles as $v) {
            $driverCode = $v['driver_code'] ?? 'NV-006';
            $driverUser = $usersMap[$driverCode] ?? ($usersMap['NV-006'] ?? $admin);
            unset($v['driver_code']);
            $v['driver_id'] = $driverUser->id;
            $v['next_inspection_date'] = now()->addMonths(6)->toDateString();
            $vehiclesMap[$v['plate_number']] = Vehicle::updateOrCreate(['plate_number' => $v['plate_number']], $v);
        }

        // 4. DEALERS
        $salesRepUser = $usersMap['NV-003'] ?? $admin;
        $dealersData = [
            [
                'code' => 'DL-HN-001',
                'name' => 'Đại Lý Điện Nước Minh Quang',
                'owner_name' => 'Vũ Minh Quang',
                'phone' => '0912 888 123',
                'address' => '158 Đường Giải Phóng, Phương Liệt',
                'district' => 'Thanh Xuân',
                'city' => 'Hà Nội',
                'tier' => 'Đại lý Cấp 1',
                'lat' => 20.9982,
                'lng' => 105.8415,
                'credit_limit' => 300000000,
                'current_debt' => 185000000,
                'payment_term_days' => 30,
                'status' => 'Hoạt động',
            ],
            [
                'code' => 'DL-HN-002',
                'name' => 'Cửa Hàng Vật Tư Ống Nhựa Thành Công',
                'owner_name' => 'Nguyễn Thành Công',
                'phone' => '0983 999 444',
                'address' => '89 Đường Hoàng Quốc Việt',
                'district' => 'Cầu Giấy',
                'city' => 'Hà Nội',
                'tier' => 'Đại lý Cấp 1',
                'lat' => 21.0458,
                'lng' => 105.7923,
                'credit_limit' => 250000000,
                'current_debt' => 268000000,
                'payment_term_days' => 30,
                'overdue_days' => 12,
                'status' => 'Cảnh báo nợ',
            ],
            [
                'code' => 'DL-HN-003',
                'name' => 'Đại Lý Điện Nước Phát Tiến',
                'owner_name' => 'Lê Văn Tiến',
                'phone' => '0978 111 555',
                'address' => '423 Đường Nguyễn Trãi',
                'district' => 'Thanh Xuân',
                'city' => 'Hà Nội',
                'tier' => 'Đại lý Cấp 2',
                'lat' => 20.9931,
                'lng' => 105.8089,
                'credit_limit' => 100000000,
                'current_debt' => 45000000,
                'payment_term_days' => 15,
                'status' => 'Hoạt động',
            ],
            [
                'code' => 'DL-HN-004',
                'name' => 'Nhà Thầu Cơ Điện & Ống Nhựa An Bình',
                'owner_name' => 'Trịnh Quốc An',
                'phone' => '0936 222 777',
                'address' => 'Lô C2 Khu Đô Thị Nam Trung Yên',
                'district' => 'Cầu Giấy',
                'city' => 'Hà Nội',
                'tier' => 'Khách Dự Án',
                'lat' => 21.0125,
                'lng' => 105.7891,
                'credit_limit' => 500000000,
                'current_debt' => 320000000,
                'payment_term_days' => 45,
                'status' => 'Hoạt động',
            ],
            [
                'code' => 'DL-HCM-005',
                'name' => 'Đại Lý PVC & Thiết Bị Nước Hoàng Long',
                'owner_name' => 'Hoàng Văn Long',
                'phone' => '0908 555 888',
                'address' => '245 Đường Lý Thường Kiệt',
                'district' => 'Quận 10',
                'city' => 'TP. Hồ Chí Minh',
                'tier' => 'Đại lý Cấp 1',
                'lat' => 10.7712,
                'lng' => 106.6582,
                'credit_limit' => 400000000,
                'current_debt' => 120000000,
                'payment_term_days' => 30,
                'status' => 'Hoạt động',
            ],
        ];

        $dealersMap = [];
        foreach ($dealersData as $d) {
            $d['sales_rep_id'] = $salesRepUser->id;
            $dealersMap[$d['code']] = Dealer::updateOrCreate(['code' => $d['code']], $d);
        }

        // 5. DEALER CARE LOGS (Check-in GPS thực địa)
        $sampleDealer = $dealersMap['DL-HN-001'] ?? null;
        if ($sampleDealer) {
            DealerCareLog::updateOrCreate(
                [
                    'dealer_id' => $sampleDealer->id,
                    'visited_date' => now()->subDays(2)->toDateTimeString(),
                ],
                [
                    'sales_rep_id' => $salesRepUser->id,
                    'lat' => 20.9982,
                    'lng' => 105.8415,
                    'distance_meters' => 35,
                    'is_within_radius' => true,
                    'purpose' => 'Thăm hỏi định kỳ',
                    'marketFeedback' => 'Sức tiêu thụ ống PVC Ø21, Ø110 tăng mạnh do các công trình xây dựng khởi công nhiều.',
                    'competitor_info' => 'Hàng Tiền Phong đang có chương trình khuyến mãi chiết khấu thêm 2% cho đơn trên 50 triệu.',
                    'next_order_estimate' => 'Dự kiến đặt đơn mới khoảng 120 triệu vào đầu tuần tới.',
                    'satisfaction_rating' => 5,
                    'photo_url' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=400',
                    'kpi_credited' => true,
                ]
            );
        }

        // 6. ORDERS & ORDER ITEMS
        $p1 = Product::where('sku', 'BM-PVC-D21-C1')->first();
        $p2 = Product::where('sku', 'TP-PVC-D110-C2')->first();
        $ordersData = [
            [
                'order_code' => 'DH-2026-0001',
                'dealer_id' => $dealersMap['DL-HN-001']->id ?? null,
                'customer_name' => 'Đại Lý Điện Nước Minh Quang',
                'customer_phone' => '0912 888 123',
                'customer_address' => '158 Đường Giải Phóng, Phương Liệt, Thanh Xuân, Hà Nội',
                'sales_rep_id' => $salesRepUser->id,
                'sales_accountant_id' => $usersMap['NV-002']->id ?? null,
                'warehouse_staff_id' => $usersMap['NV-004']->id ?? null,
                'assigned_driver_id' => $driverUser->id,
                'assigned_vehicle_plate' => '29C-888.99',
                'subtotal' => 73500000,
                'discount_amount' => 3500000,
                'tax_amount' => 7000000,
                'total_amount' => 77000000,
                'status' => 'Đang giao hàng',
                'payment_status' => 'Đã thu tiền mặt',
                'payment_method' => 'Tiền mặt',
                'driver_debt_amount' => 77000000,
                'notes' => 'Giao trước 11h sáng, cẩn thận bó ống D110 tránh trầy xước.',
            ],
            [
                'order_code' => 'DH-2026-0002',
                'dealer_id' => $dealersMap['DL-HN-002']->id ?? null,
                'customer_name' => 'Cửa Hàng Vật Tư Ống Nhựa Thành Công',
                'customer_phone' => '0983 999 444',
                'customer_address' => '89 Đường Hoàng Quốc Việt, Cầu Giấy, Hà Nội',
                'sales_rep_id' => $salesRepUser->id,
                'sales_accountant_id' => $usersMap['NV-002']->id ?? null,
                'subtotal' => 125000000,
                'discount_amount' => 6250000,
                'tax_amount' => 11875000,
                'total_amount' => 130625000,
                'status' => 'Chờ kế toán duyệt',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Ghi nợ đại lý',
                'notes' => 'Đơn hàng vượt hạn mức nợ, cần Kế toán trưởng và Giám đốc phê duyệt.',
            ],
        ];

        foreach ($ordersData as $o) {
            $order = Order::updateOrCreate(['order_code' => $o['order_code']], $o);
            if ($p1 && $p2) {
                OrderItem::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $p1->id],
                    [
                        'sku' => $p1->sku,
                        'name' => $p1->name,
                        'unit' => $p1->unit,
                        'quantity' => 500,
                        'cost_price' => $p1->cost_price,
                        'unit_price' => 58000,
                        'original_price' => 72000,
                        'applied_tier_policy' => 'Đại lý Cấp 1',
                        'amount' => 29000000,
                    ]
                );
                OrderItem::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $p2->id],
                    [
                        'sku' => $p2->sku,
                        'name' => $p2->name,
                        'unit' => $p2->unit,
                        'quantity' => 100,
                        'cost_price' => $p2->cost_price,
                        'unit_price' => 440000,
                        'original_price' => 520000,
                        'applied_tier_policy' => 'Đại lý Cấp 1',
                        'amount' => 44000000,
                    ]
                );
            }
        }

        // 7. TRANSACTIONS (Sổ quỹ & Hóa đơn VAT)
        $cashFund = $fundsMap['Quỹ Tiền Mặt'] ?? FinancialFund::first();
        $transactionsData = [
            [
                'code' => 'PT-2026-0001',
                'type' => 'Thu',
                'fund_id' => $cashFund->id,
                'category' => 'Thu nợ đại lý',
                'amount' => 50000000,
                'customer_partner_name' => 'Đại Lý Điện Nước Minh Quang',
                'created_by_id' => $usersMap['NV-007']->id ?? $admin->id,
                'date' => now()->subDay()->toDateString(),
                'notes' => 'Thu nợ đợt 1 tiền hàng tháng 08',
                'has_vat_invoice' => false,
            ],
            [
                'code' => 'PC-2026-0001',
                'type' => 'Chi',
                'fund_id' => $cashFund->id,
                'category' => 'Chi nhiên liệu',
                'amount' => 3500000,
                'customer_partner_name' => 'Cây xăng Petrolimex Số 12',
                'created_by_id' => $usersMap['NV-007']->id ?? $admin->id,
                'date' => now()->toDateString(),
                'notes' => 'Đổ dầu xe tải 29C-888.99 giao hàng tuyến Bắc Ninh',
                'has_vat_invoice' => true,
                'vat_invoice_no' => '0012890',
                'tax_code' => '0100107624',
                'vat_rate' => 10.0,
                'vat_amount' => 350000,
                'total_with_vat' => 3850000,
            ],
        ];

        foreach ($transactionsData as $t) {
            Transaction::updateOrCreate(['code' => $t['code']], $t);
        }

        // 8. ATTENDANCE RECORDS (Chấm công GPS)
        foreach ($usersMap as $emp) {
            AttendanceRecord::updateOrCreate(
                [
                    'user_id' => $emp->id,
                    'date' => now()->toDateString(),
                ],
                [
                    'check_in_time' => '07:55:00',
                    'check_out_time' => null,
                    'check_in_lat' => 20.9982,
                    'check_in_lng' => 105.8415,
                    'check_in_address' => 'Văn phòng Trụ sở Chính',
                    'status' => 'Đúng giờ',
                    'work_hours' => 8.0,
                ]
            );
        }

        // 9. TASK ASSIGNMENTS
        TaskAssignment::updateOrCreate(
            ['code' => 'CV-2026-001'],
            [
                'title' => 'Soạn hàng xuất kho đơn DH-2026-0001',
                'description' => 'Soạn đủ 500 ống Ø21 C1 và 100 ống Ø110 C2 cho xe 29C-888.99',
                'category' => 'Kho vận',
                'assigned_to_id' => $usersMap['NV-005']->id ?? $admin->id,
                'created_by_id' => $usersMap['NV-004']->id ?? $admin->id,
                'deadline' => now()->addHours(3),
                'priority' => 'Cao',
                'status' => 'Đang thực hiện',
                'progress_percent' => 70,
                'related_order_code' => 'DH-2026-0001',
                'checklist' => [
                    ['id' => 'chk-1', 'text' => 'Bốc 500 ống D21 C1 từ Dãy A1', 'completed' => true],
                    ['id' => 'chk-2', 'text' => 'Bốc 100 ống D110 C2 từ Dãy A3', 'completed' => true],
                    ['id' => 'chk-3', 'text' => 'Kiểm tra phiếu xuất và bàn giao lái xe', 'completed' => false],
                ],
            ]
        );

        // 10. PAYROLL RECORDS
        foreach ($usersMap as $emp) {
            $base = (float) $emp->base_salary;
            PayrollRecord::updateOrCreate(
                [
                    'user_id' => $emp->id,
                    'month' => now()->format('Y-m'),
                ],
                [
                    'base_salary' => $base,
                    'work_days' => 26.0,
                    'kpi_bonus' => 2500000,
                    'sales_commission' => $emp->department === 'Sales' ? 4500000 : 0,
                    'allowances' => 1500000,
                    'deductions' => 0,
                    'net_salary' => $base + 2500000 + ($emp->department === 'Sales' ? 4500000 : 0) + 1500000,
                    'status' => 'Bản nháp',
                ]
            );
        }

        // 11. PROPOSALS & STEPS
        $prop = Proposal::updateOrCreate(
            ['code' => 'DX-2026-001'],
            [
                'title' => 'Đề xuất tăng hạn mức nợ cho Đại lý Minh Quang lên 400 triệu',
                'requester_id' => $salesRepUser->id,
                'department' => 'Sales',
                'type' => 'Tăng hạn mức công nợ',
                'amount' => 100000000,
                'description' => 'Đại lý Minh Quang đang thi công dự án trường học tại Giải Phóng, nhu cầu lấy thêm vật tư trong tháng 9.',
                'status' => 'Chờ duyệt',
                'current_step' => 1,
                'total_steps' => 3,
            ]
        );

        ProposalStep::updateOrCreate(
            ['proposal_id' => $prop->id, 'step_index' => 1],
            ['role_name' => 'Trưởng phòng kinh doanh', 'approver_id' => $admin->id, 'status' => 'Chờ duyệt']
        );
        ProposalStep::updateOrCreate(
            ['proposal_id' => $prop->id, 'step_index' => 2],
            ['role_name' => 'Kế toán trưởng', 'approver_id' => $usersMap['NV-002']->id ?? $admin->id, 'status' => 'Chờ duyệt']
        );
        ProposalStep::updateOrCreate(
            ['proposal_id' => $prop->id, 'step_index' => 3],
            ['role_name' => 'Ban Giám Đốc', 'approver_id' => $admin->id, 'status' => 'Chờ duyệt']
        );
    }
}
