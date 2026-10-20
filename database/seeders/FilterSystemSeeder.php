<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FilterPart;
use App\Models\FilterInstallation;
use App\Models\FilterInstallationItem;
use App\Models\FilterRepair;
use App\Models\FilterRepairItem;
use App\Models\FilterPartTransaction;
use App\Models\User;

class FilterSystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Phụ tùng / Linh kiện máy lọc nước mẫu
        $parts = [
            [
                'code' => 'LOI-01-PP5M',
                'name' => 'Lõi lọc số 1 PP 5 Micron (Lọc cặn thô)',
                'category' => 'Lõi lọc thô',
                'unit' => 'Lõi',
                'in_price' => 25000,
                'out_price' => 50000,
                'stock_qty' => 120,
                'min_stock_alert' => 20,
                'location' => 'Kệ A1-01',
                'description' => 'Chặn bùn đất, rỉ sét, kích thước hạt > 5 micron. Thời hạn thay: 3 - 6 tháng.',
            ],
            [
                'code' => 'LOI-02-OCB',
                'name' => 'Lõi lọc số 2 Than hoạt tính OCB/GAC',
                'category' => 'Lõi lọc thô',
                'unit' => 'Lõi',
                'in_price' => 45000,
                'out_price' => 80000,
                'stock_qty' => 85,
                'min_stock_alert' => 15,
                'location' => 'Kệ A1-02',
                'description' => 'Hấp phụ clo, hóa chất hữu cơ, chất tẩy rửa, mùi lạ. Thời hạn thay: 6 - 9 tháng.',
            ],
            [
                'code' => 'LOI-03-CTO',
                'name' => 'Lõi lọc số 3 Than ép CTO 1 Micron',
                'category' => 'Lõi lọc thô',
                'unit' => 'Lõi',
                'in_price' => 45000,
                'out_price' => 80000,
                'stock_qty' => 90,
                'min_stock_alert' => 15,
                'location' => 'Kệ A1-03',
                'description' => 'Loại bỏ tạp chất hữu cơ dạng nhỏ và clo dư. Thời hạn thay: 9 - 12 tháng.',
            ],
            [
                'code' => 'MANG-RO-DOW-100G',
                'name' => 'Màng lọc RO Filmtec DOW 100GPD (USA)',
                'category' => 'Màng RO & Lọc tinh',
                'unit' => 'Màng',
                'in_price' => 320000,
                'out_price' => 550000,
                'stock_qty' => 35,
                'min_stock_alert' => 10,
                'location' => 'Kệ B2-01',
                'description' => 'Màng thẩm thấu ngược kích thước 0.0001 micron, loại bỏ 99.9% kim loại nặng, vi khuẩn.',
            ],
            [
                'code' => 'MANG-RO-TORAY-75G',
                'name' => 'Màng lọc RO Toray 75GPD (Nhật Bản)',
                'category' => 'Màng RO & Lọc tinh',
                'unit' => 'Màng',
                'in_price' => 280000,
                'out_price' => 480000,
                'stock_qty' => 25,
                'min_stock_alert' => 8,
                'location' => 'Kệ B2-02',
                'description' => 'Màng RO công nghệ Nhật Bản, bền bỉ, tiết kiệm nước thải.',
            ],
            [
                'code' => 'LOI-T33-GAC',
                'name' => 'Lõi lọc T33 Than gáo dừa hoạt hóa (Tạo vị ngọt)',
                'category' => 'Lõi khoáng chức năng',
                'unit' => 'Lõi',
                'in_price' => 65000,
                'out_price' => 120000,
                'stock_qty' => 60,
                'min_stock_alert' => 15,
                'location' => 'Kệ A2-01',
                'description' => 'Cân bằng độ pH, tạo vị thanh ngọt tự nhiên cho nước uống.',
            ],
            [
                'code' => 'LOI-KHOANG-MINERAL',
                'name' => 'Lõi Mineral Bổ sung khoáng chất & Vi lượng',
                'category' => 'Lõi khoáng chức năng',
                'unit' => 'Lõi',
                'in_price' => 75000,
                'out_price' => 140000,
                'stock_qty' => 45,
                'min_stock_alert' => 10,
                'location' => 'Kệ A2-02',
                'description' => 'Bổ sung canxi, magie, kali, khoáng chất có lợi cho sức khỏe.',
            ],
            [
                'code' => 'BOM-HEADON-24V',
                'name' => 'Bơm áp lực máy lọc RO Headon 24V-HF8367',
                'category' => 'Bơm & Nguồn',
                'unit' => 'Cái',
                'in_price' => 280000,
                'out_price' => 450000,
                'stock_qty' => 18,
                'min_stock_alert' => 5,
                'location' => 'Kệ C1-01',
                'description' => 'Bơm tăng áp 24V Đài Loan, chạy êm, lực nén cực mạnh, hút sâu.',
            ],
            [
                'code' => 'ADAPTER-24V-15A',
                'name' => 'Nguồn Adapter biến áp 24V - 1.5A dây đồng',
                'category' => 'Bơm & Nguồn',
                'unit' => 'Cái',
                'in_price' => 95000,
                'out_price' => 160000,
                'stock_qty' => 30,
                'min_stock_alert' => 8,
                'location' => 'Kệ C1-02',
                'description' => 'Bộ đổi nguồn AC 220V sang DC 24V cấp điện cho bơm và van từ.',
            ],
            [
                'code' => 'VAN-DIEN-TU-24V',
                'name' => 'Van điện từ phi 6 điện áp 24V',
                'category' => 'Van & Điện từ',
                'unit' => 'Cái',
                'in_price' => 45000,
                'out_price' => 90000,
                'stock_qty' => 40,
                'min_stock_alert' => 10,
                'location' => 'Kệ C2-01',
                'description' => 'Tự động ngắt nguồn cấp nước khi bình đầy hoặc mất điện, chống rỉ nước thải.',
            ],
            [
                'code' => 'VAN-AP-CAO',
                'name' => 'Van áp cao tự ngắt phi 6',
                'category' => 'Van & Điện từ',
                'unit' => 'Cái',
                'in_price' => 30000,
                'out_price' => 60000,
                'stock_qty' => 50,
                'min_stock_alert' => 10,
                'location' => 'Kệ C2-02',
                'description' => 'Ngắt điện máy bơm khi bình áp đầy nước.',
            ],
            [
                'code' => 'BINH-AP-THEP-32G',
                'name' => 'Bình áp thép trữ nước siêu bền 3.2 Gallon',
                'category' => 'Bình áp & Vòi',
                'unit' => 'Bình',
                'in_price' => 280000,
                'out_price' => 420000,
                'stock_qty' => 15,
                'min_stock_alert' => 5,
                'location' => 'Kho D1',
                'description' => 'Bình chứa nước áp lực tiêu chuẩn, dung tích thực chứa ~8-10 lít.',
            ],
            [
                'code' => 'VOI-CO-NGONG-304',
                'name' => 'Vòi lấy nước cổ ngỗng Inox SUS 304 gạt cao cấp',
                'category' => 'Bình áp & Vòi',
                'unit' => 'Cái',
                'in_price' => 120000,
                'out_price' => 200000,
                'stock_qty' => 25,
                'min_stock_alert' => 5,
                'location' => 'Kệ D2-01',
                'description' => 'Vòi inox 304 bóng sáng, chống rỉ sét, khóa gạt êm ái.',
            ],
            [
                'code' => 'DAY-PE-PHI-6',
                'name' => 'Dây dẫn nước PE phi 6 (Cuộn 100m)',
                'category' => 'Cút nối & Dây',
                'unit' => 'Cuộn',
                'in_price' => 180000,
                'out_price' => 280000,
                'stock_qty' => 12,
                'min_stock_alert' => 3,
                'location' => 'Kệ E1-01',
                'description' => 'Dây nhựa PE an toàn thực phẩm dẫn nước sạch và nước thải.',
            ],
            [
                'code' => 'CUT-NOI-NHANH-6',
                'name' => 'Bộ cút nối nhanh chữ L, T, I phi 6 (Bịch 10 cái)',
                'category' => 'Cút nối & Dây',
                'unit' => 'Bộ',
                'in_price' => 35000,
                'out_price' => 70000,
                'stock_qty' => 55,
                'min_stock_alert' => 10,
                'location' => 'Kệ E1-02',
                'description' => 'Cút cắm nhanh chống rò rỉ nước, tháo lắp cực kỳ tiện lợi.',
            ],
        ];

        foreach ($parts as $p) {
            $created = FilterPart::updateOrCreate(['code' => $p['code']], $p);
            // Ghi nhận giao dịch nhập ban đầu
            FilterPartTransaction::updateOrCreate(
                [
                    'part_id' => $created->id,
                    'type' => 'import',
                    'reference_code' => 'NK-BAN-DAU-001'
                ],
                [
                    'quantity' => $created->stock_qty,
                    'balance_before' => 0,
                    'balance_after' => $created->stock_qty,
                    'unit_price' => $created->in_price,
                    'total_price' => $created->in_price * $created->stock_qty,
                    'note' => 'Khởi tạo tồn kho ban đầu phân hệ máy lọc',
                    'created_by_name' => 'Hệ thống Quản trị',
                ]
            );
        }

        // Lấy 1 user mẫu làm KTV
        $firstUser = User::first();
        $techId = $firstUser ? $firstUser->id : null;
        $techName = $firstUser ? $firstUser->name : 'KTV. Nguyễn Văn Hùng';

        // 2. Tạo đơn đi lắp đặt mẫu
        $install = FilterInstallation::updateOrCreate(
            ['code' => 'LD-20261014-001'],
            [
                'customer_name' => 'Anh Trần Hữu Đức',
                'customer_phone' => '0912345678',
                'customer_address' => 'Số 45 Đường Hoàng Văn Thụ, TP. Bắc Giang',
                'machine_model' => 'Máy lọc nước RO Kangaroo Hydrogen 10 lõi KG100HA',
                'machine_serial' => 'KG-2026-98124',
                'water_source_type' => 'Nước máy thành phố',
                'technician_id' => $techId,
                'technician_name' => $techName,
                'appointment_date' => now()->addHours(3),
                'status' => 'assigned',
                'tds_in' => 185,
                'tds_out' => 12,
                'pressure' => 'Bình thường (2.5 bar)',
                'service_fee' => 150000,
                'parts_fee' => 70000,
                'total_amount' => 220000,
                'payment_status' => 'unpaid',
                'customer_notes' => 'Lắp đặt dưới gầm tủ bếp, gia đình có sẵn đường cấp nước.',
            ]
        );

        // Chi tiết phụ kiện mang theo đơn lắp
        FilterInstallationItem::updateOrCreate(
            [
                'installation_id' => $install->id,
                'part_code' => 'CUT-NOI-NHANH-6',
            ],
            [
                'part_name' => 'Bộ cút nối nhanh chữ L, T, I phi 6',
                'unit' => 'Bộ',
                'quantity' => 1,
                'unit_price' => 70000,
                'total_price' => 70000,
                'note' => 'Đấu nối vòi gạt trên mặt đá',
            ]
        );

        // 3. Tạo phiếu tiếp nhận sửa chữa mẫu
        $repair = FilterRepair::updateOrCreate(
            ['code' => 'SC-20261014-001'],
            [
                'customer_name' => 'Chị Mai Lan Hương',
                'customer_phone' => '0988776655',
                'customer_address' => 'Khu đô thị Đồng Sơn, Bắc Giang',
                'repair_type' => 'at_home',
                'machine_model' => 'Máy lọc nước Karofi Optimus Plus 9 lõi O-P1319',
                'machine_serial' => 'KRF-8921-2025',
                'warranty_type' => 'out_warranty',
                'symptom' => 'Máy kêu tạch tạch liên tục không ngắt, nước thải chảy mãi không dừng, vòi không có nước tinh khiết.',
                'diagnosis' => 'Van điện từ bị hỏng kẹt lò xo mở liên tục, màng RO bị tắc sau 2 năm chưa thay dẫn đến áp không nạp bình.',
                'solution' => 'Thay thế van điện từ 24V mới, thay màng RO DOW Filmtec 100G, vệ sinh sục rửa cốc màng.',
                'technician_id' => $techId,
                'technician_name' => $techName,
                'status' => 'repairing',
                'labor_fee' => 150000,
                'parts_fee' => 640000,
                'discount' => 40000,
                'total_amount' => 750000,
                'payment_status' => 'unpaid',
                'received_at' => now()->subDay(),
                'notes' => 'Khách hẹn chiều 15h qua nhà kiểm tra và nghiệm thu.',
            ]
        );

        FilterRepairItem::updateOrCreate(
            [
                'repair_id' => $repair->id,
                'part_code' => 'VAN-DIEN-TU-24V',
            ],
            [
                'part_name' => 'Van điện từ phi 6 điện áp 24V',
                'unit' => 'Cái',
                'quantity' => 1,
                'unit_price' => 90000,
                'total_price' => 90000,
                'is_warranty' => false,
            ]
        );

        FilterRepairItem::updateOrCreate(
            [
                'repair_id' => $repair->id,
                'part_code' => 'MANG-RO-DOW-100G',
            ],
            [
                'part_name' => 'Màng lọc RO Filmtec DOW 100GPD (USA)',
                'unit' => 'Màng',
                'quantity' => 1,
                'unit_price' => 550000,
                'total_price' => 550000,
                'is_warranty' => false,
            ]
        );
    }
}
