<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\DepartmentKpi;
use App\Models\PeriodLock;
use App\Models\PolicyPreservation;
use App\Models\AffiliateReferral;
use App\Models\VehicleAssignment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class InitialErpRemainingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Audit Logs
        if (AuditLog::count() === 0) {
            $auditLogs = [
                [
                    'timestamp' => '2026-09-07 16:45:12',
                    'performer_name' => 'Trần Thị Thu Hà (Kế toán trưởng)',
                    'action' => 'Duyệt Chi Hộ',
                    'module' => 'Tài chính',
                    'target_document_code' => 'DX-2026-004',
                    'field_name' => 'Trạng Thái Phê Duyệt',
                    'old_value' => 'Chờ duyệt cấp 2',
                    'new_value' => 'Đã duyệt chi hộ',
                    'notes' => 'Ký duyệt chi tiền mua phụ kiện ống Bình Minh phát sinh',
                ],
                [
                    'timestamp' => '2026-09-07 14:15:30',
                    'performer_name' => 'Hoàng Văn Khoa (Điều vận)',
                    'action' => 'Xếp Xe Chuyến',
                    'module' => 'Giao hàng',
                    'target_document_code' => 'CX-2026-088',
                    'field_name' => 'Tài xế & Xe giao',
                    'old_value' => 'Chờ điều phối',
                    'new_value' => 'Xe 29C-888.99 (Bùi Văn Thành)',
                    'notes' => 'Chuyển đơn giao đại lý Tuấn Cường sang xe 2.5 tấn',
                ],
                [
                    'timestamp' => '2026-09-06 10:20:00',
                    'performer_name' => 'Phạm Minh Tuấn (Sale Thị trường)',
                    'action' => 'Tạo Đơn Hàng',
                    'module' => 'Bán hàng',
                    'target_document_code' => 'DH-2026-0902',
                    'field_name' => 'Tổng tiền đơn hàng',
                    'old_value' => '0 đ',
                    'new_value' => '48,500,000 đ',
                    'notes' => 'Lên đơn 50 cây ống PVC D110 cho Đại Lý Hợp Phát',
                ],
                [
                    'timestamp' => '2026-09-05 09:00:00',
                    'performer_name' => 'Ban Giám Đốc',
                    'action' => 'Khóa Sổ Kỳ',
                    'module' => 'Kế toán',
                    'target_document_code' => 'LOCK-T08-2026',
                    'field_name' => 'Khóa kỳ kế toán',
                    'old_value' => 'Mở',
                    'new_value' => 'Đã khóa hoàn toàn',
                    'notes' => 'Hoàn tất quyết toán thuế và chốt công nợ tháng 08/2026',
                ],
                [
                    'timestamp' => '2026-09-04 15:30:10',
                    'performer_name' => 'Đỗ Phương Mai (Thủ quỹ)',
                    'action' => 'Thu Nợ Lái Xe',
                    'module' => 'Sổ quỹ',
                    'target_document_code' => 'THU-COD-0904',
                    'field_name' => 'Tiền nộp quỹ COD',
                    'old_value' => '0 đ',
                    'new_value' => '23,650,000 đ',
                    'notes' => 'Quyết toán tiền mặt COD chuyến giao buổi sáng',
                ],
            ];

            foreach ($auditLogs as $log) {
                AuditLog::create($log);
            }
        }

        // 2. Period Locks
        if (PeriodLock::count() === 0) {
            $user = User::first();
            PeriodLock::create([
                'ledger_type'   => 'Sổ Cái Kế Toán Tổng Hợp',
                'from_date'     => '2026-08-01',
                'to_date'       => '2026-08-31',
                'status'        => 'locked',
                'locked_by_id'  => $user?->id ?? 1,
                'reason'        => 'Khóa sổ kỳ kế toán Tháng 08/2026 sau khi hoàn tất kiểm toán thuế nội bộ',
            ]);

            PeriodLock::create([
                'ledger_type'   => 'Sổ Bán Hàng & Công Nợ Đại Lý',
                'from_date'     => '2026-09-01',
                'to_date'       => '2026-09-30',
                'status'        => 'unlocked',
                'locked_by_id'  => $user?->id ?? 1,
                'reason'        => 'Kỳ Tháng 09/2026 đang mở cho phép tạo và sửa phiếu bán hàng',
            ]);
        }

        // 3. Announcements
        if (Announcement::count() === 0) {
            $announcements = [
                [
                    'title'             => '📢 Thông báo cập nhật Bảng Giá Ống Nhựa PVC Bình Minh & Hoa Sen Tháng 09/2026',
                    'content'           => 'Ban Giám Đốc trân trọng thông báo bảng giá chiết khấu mới áp dụng từ 01/09/2026. Tất cả đơn hàng Đại lý Cấp 1 và Cấp 2 khi lên đơn POS sẽ tự động áp dụng giá theo phân loại đối tượng.',
                    'author'            => 'Nguyễn Văn Hùng',
                    'author_role'       => 'Giám đốc Điều hành',
                    'priority'          => 'Quan trọng',
                    'target_department' => 'Tất cả',
                    'read_by'           => ['usr-001', 'usr-002', 'usr-003'],
                ],
                [
                    'title'             => '⚡ Quy định bắt buộc: Chấm công GPS & Chụp ảnh Check-in Đại lý',
                    'content'           => 'Nhân viên thị trường lưu ý: Khi chăm sóc đại lý phải bật GPS và chụp ảnh thực tế tại cửa hàng để hệ thống tự động cộng điểm KPI. Các trường hợp không có GPS hợp lệ sẽ không được ghi nhận.',
                    'author'            => 'Trần Thị Thu Hà',
                    'author_role'       => 'Trưởng phòng Nhân sự & Kế toán',
                    'priority'          => 'Khẩn cấp',
                    'target_department' => 'Sales',
                    'read_by'           => ['usr-003'],
                ],
                [
                    'title'             => '🎉 Chúc mừng sinh nhật Thành viên Công ty trong Tháng 9',
                    'content'           => 'Công ty thân ái chúc mừng sinh nhật Giám Đốc Nguyễn Văn Hùng (07/09) và các cán bộ nhân viên sinh nhật tháng này. Chúc anh chị luôn dồi dào sức khỏe và gặt hái nhiều thắng lợi mới!',
                    'author'            => 'Công đoàn Công ty',
                    'author_role'       => 'Ban Nhân sự',
                    'priority'          => 'Bình thường',
                    'target_department' => 'Tất cả',
                    'read_by'           => [],
                ],
            ];

            foreach ($announcements as $ann) {
                Announcement::create($ann);
            }
        }

        // 4. Department KPIs
        if (DepartmentKpi::count() === 0) {
            $deptKpis = [
                [
                    'department'             => 'Sales',
                    'department_name'        => 'Phòng Thị Trường & Bán Hàng',
                    'overall_target_percent' => 92,
                    'month'                  => '2026-09',
                    'metrics'                => [
                        ['id' => 'm1', 'name' => 'Phát triển đại lý mới (Có vị trí GPS)', 'targetValue' => 10, 'currentValue' => 9, 'unit' => 'Đại lý', 'weightPercent' => 35, 'description' => 'Yêu cầu nhân viên đến tận nơi, chụp ảnh GPS xác nhận'],
                        ['id' => 'm2', 'name' => 'Số lượt chăm sóc đại lý có ảnh GPS', 'targetValue' => 50, 'currentValue' => 46, 'unit' => 'Lượt', 'weightPercent' => 35, 'description' => 'Tối thiểu 15 phút tại điểm bán'],
                        ['id' => 'm3', 'name' => 'Doanh số bán hàng đạt chỉ tiêu', 'targetValue' => 1500000000, 'currentValue' => 1380000000, 'unit' => 'VNĐ', 'weightPercent' => 30, 'description' => 'Tổng doanh thu ghi nhận'],
                    ],
                ],
                [
                    'department'             => 'Accounting',
                    'department_name'        => 'Phòng Kế Toán Bán Hàng & Tài Chính',
                    'overall_target_percent' => 96,
                    'month'                  => '2026-09',
                    'metrics'                => [
                        ['id' => 'm4', 'name' => 'Tốc độ lên đơn báo giá & chuyển Kho', 'targetValue' => 100, 'currentValue' => 98, 'unit' => '%', 'weightPercent' => 40, 'description' => 'Thời gian xử lý < 15 phút/đơn'],
                        ['id' => 'm5', 'name' => 'Kiểm soát hạn mức & công nợ quá hạn', 'targetValue' => 100, 'currentValue' => 95, 'unit' => '%', 'weightPercent' => 40, 'description' => 'Phát cảnh báo tự động khi quá hạn'],
                        ['id' => 'm6', 'name' => 'Quyết toán quỹ tài xế đúng ngày', 'targetValue' => 100, 'currentValue' => 95, 'unit' => '%', 'weightPercent' => 20, 'description' => 'Xử lý tiền mặt/QR hoàn tất'],
                    ],
                ],
                [
                    'department'             => 'Warehouse',
                    'department_name'        => 'Bộ Phận Kho Vật Tư Ống Nhựa',
                    'overall_target_percent' => 98,
                    'month'                  => '2026-09',
                    'metrics'                => [
                        ['id' => 'm7', 'name' => 'Độ chính xác soạn hàng theo phiếu xuất', 'targetValue' => 100, 'currentValue' => 99.2, 'unit' => '%', 'weightPercent' => 50, 'description' => 'Không sai mã vật tư PVC/Điện nước'],
                        ['id' => 'm8', 'name' => 'Tốc độ hoàn tất phiếu xuất kho', 'targetValue' => 45, 'currentValue' => 38, 'unit' => 'Phút', 'weightPercent' => 30, 'description' => 'Tính từ khi nhận đơn từ Kế toán'],
                        ['id' => 'm9', 'name' => 'Phân bổ đơn lên xe vận tải', 'targetValue' => 100, 'currentValue' => 96, 'unit' => '%', 'weightPercent' => 20, 'description' => 'Tối ưu tải trọng xe'],
                    ],
                ],
                [
                    'department'             => 'Logistics',
                    'department_name'        => 'Bộ Phận Lái Xe & Giao Hàng',
                    'overall_target_percent' => 94,
                    'month'                  => '2026-09',
                    'metrics'                => [
                        ['id' => 'm10', 'name' => 'Tỷ lệ chuyến giao hàng thành công', 'targetValue' => 100, 'currentValue' => 97, 'unit' => '%', 'weightPercent' => 40, 'description' => 'Giao đúng giờ và đủ hàng'],
                        ['id' => 'm11', 'name' => 'Thu hồi công nợ bằng QR/Tiền mặt', 'targetValue' => 100, 'currentValue' => 94, 'unit' => '%', 'weightPercent' => 40, 'description' => 'Khách quét QR hoặc thu tiền mặt nộp lại'],
                        ['id' => 'm12', 'name' => 'An toàn lộ trình & tiết kiệm xăng dầu', 'targetValue' => 100, 'currentValue' => 91, 'unit' => '%', 'weightPercent' => 20, 'description' => 'Tuân thủ định mức nhiên liệu'],
                    ],
                ],
            ];

            foreach ($deptKpis as $kpi) {
                DepartmentKpi::create($kpi);
            }
        }

        // 5. Policy Preservations
        if (PolicyPreservation::count() === 0) {
            $policies = [
                [
                    'code'                 => 'BL-001',
                    'customer_id'          => '1',
                    'customer_name'        => 'Đại Lý Vật Tư Xây Dựng Tuấn Cường',
                    'product_id'           => '1',
                    'product_name'         => 'Ống nhựa PVC-U Bình Minh Ø21 x 1.2mm (Class 1)',
                    'total_deposit_amount' => 50000000,
                    'used_amount'          => 18500000,
                    'remaining_amount'     => 31500000,
                    'start_date'           => '2026-08-01',
                    'end_date'             => '2026-09-30',
                    'status'               => 'Còn hiệu lực',
                    'created_by_name'      => 'Trần Thị Thu Hà',
                    'notes'                => 'Bảo lưu giá khuyến mãi Nhựa Tiền Phong 22.31% tháng 8 cho lô hàng tháng 9',
                    'history_logs'         => [
                        [
                            'id'              => 'bl-log-1',
                            'orderCode'       => 'DH-2026-0812',
                            'date'            => '2026-08-15',
                            'quantity'        => 1000,
                            'unitPrice'       => 12500,
                            'discountPercent' => 22.31,
                            'deductedAmount'  => 9711250,
                            'remainingAfter'  => 40288750,
                        ],
                        [
                            'id'              => 'bl-log-2',
                            'orderCode'       => 'DH-2026-0890',
                            'date'            => '2026-08-28',
                            'quantity'        => 900,
                            'unitPrice'       => 12500,
                            'discountPercent' => 22.31,
                            'deductedAmount'  => 8788750,
                            'remainingAfter'  => 31500000,
                        ],
                    ],
                ],
                [
                    'code'                 => 'BL-002',
                    'customer_id'          => '2',
                    'customer_name'        => 'Cửa Hàng Điện Nước Minh Khôi',
                    'product_id'           => '2',
                    'product_name'         => 'Ống nhựa PVC-U Tiền Phong Ø27 x 1.5mm',
                    'total_deposit_amount' => 30000000,
                    'used_amount'          => 12000000,
                    'remaining_amount'     => 18000000,
                    'start_date'           => '2026-08-10',
                    'end_date'             => '2026-10-10',
                    'status'               => 'Còn hiệu lực',
                    'created_by_name'      => 'Phạm Minh Tuấn',
                    'notes'                => 'Bảo lưu chính sách đặt cọc dự án công trình trường học',
                    'history_logs'         => [
                        [
                            'id'              => 'bl-log-3',
                            'orderCode'       => 'DH-2026-0845',
                            'date'            => '2026-08-20',
                            'quantity'        => 600,
                            'unitPrice'       => 20000,
                            'discountPercent' => 20.0,
                            'deductedAmount'  => 12000000,
                            'remainingAfter'  => 18000000,
                        ],
                    ],
                ],
            ];

            foreach ($policies as $pol) {
                PolicyPreservation::create($pol);
            }
        }

        // 6. Affiliate Referrals
        if (AffiliateReferral::count() === 0) {
            $user = User::first();
            $affiliates = [
                [
                    'user_id'                 => $user?->id ?? 1,
                    'affiliate_code'          => 'AFF-NV003',
                    'referrer_name'           => 'Phạm Minh Tuấn (NV Kinh Doanh)',
                    'referred_customer_name'  => 'Công trình Biệt thự Vườn Mê Linh',
                    'referred_customer_phone' => '0981 112 233',
                    'order_count'             => 3,
                    'total_revenue'           => 128500000,
                    'kpi_points'              => 1.5,
                    'bonus_vnd'               => 1285000,
                    'status'                  => 'Thành công',
                    'date'                    => '2026-09-02',
                ],
                [
                    'user_id'                 => $user?->id ?? 1,
                    'affiliate_code'          => 'AFF-NV003',
                    'referrer_name'           => 'Phạm Minh Tuấn (NV Kinh Doanh)',
                    'referred_customer_name'  => 'Nhà thầu Cơ điện Thăng Long',
                    'referred_customer_phone' => '0912 888 999',
                    'order_count'             => 1,
                    'total_revenue'           => 85000000,
                    'kpi_points'              => 1.0,
                    'bonus_vnd'               => 850000,
                    'status'                  => 'Thành công',
                    'date'                    => '2026-09-05',
                ],
            ];

            foreach ($affiliates as $aff) {
                AffiliateReferral::create($aff);
            }
        }

        // 7. Vehicle Assignments
        if (VehicleAssignment::count() === 0) {
            $veh = Vehicle::first();
            $assignments = [
                [
                    'code'           => 'PC-2026-081',
                    'vehicle_id'     => $veh?->id ?? 1,
                    'vehicle_plate'  => '29C-888.99',
                    'driver_name'    => 'Vũ Quốc Nam',
                    'driver_phone'   => '0933 111 222',
                    'copilot_name'   => 'Nguyễn Tiến Đạt',
                    'copilot_phone'  => '0978 222 333',
                    'manager_name'   => 'Hoàng Văn Khoa',
                    'manager_phone'  => '0988 222 111',
                    'assigned_date'  => '2026-09-01',
                    'effective_from' => '2026-09-01',
                    'effective_to'   => 'Không thời hạn',
                    'assigned_by'    => 'Hoàng Văn Khoa (Trưởng đội xe)',
                    'shift'          => 'Toàn thời gian (07:30 - 17:30)',
                    'route_id'       => '1',
                    'route_zone'     => 'Tuyến 01: Kho Mê Linh - Giải Phóng - Trường Chinh - Nguyễn Trãi',
                    'status'         => 'Đang áp dụng',
                    'notes'          => 'Bàn giao chìa khóa xe, thẻ đổ dầu Petrolimex số 08 và bộ dây tăng đơ ràng ống PVC.',
                ],
                [
                    'code'           => 'PC-2026-075',
                    'vehicle_id'     => $veh?->id ?? 1,
                    'vehicle_plate'  => '29C-888.99',
                    'driver_name'    => 'Trần Văn Mạnh',
                    'driver_phone'   => '0915 444 555',
                    'copilot_name'   => 'Lê Văn Kiên',
                    'copilot_phone'  => '0912 345 678',
                    'manager_name'   => 'Hoàng Văn Khoa',
                    'manager_phone'  => '0988 222 111',
                    'assigned_date'  => '2026-08-01',
                    'effective_from' => '2026-08-01',
                    'effective_to'   => '2026-08-31',
                    'assigned_by'    => 'Hoàng Văn Khoa (Trưởng đội xe)',
                    'shift'          => 'Ca sáng (07:00 - 12:00)',
                    'route_id'       => '1',
                    'route_zone'     => 'Tuyến 01: Kho Mê Linh - Đông Anh',
                    'status'         => 'Hết hạn bàn giao',
                    'notes'          => 'Đã hoàn tất bàn giao lại xe và thẻ xăng dầu cuối tháng 8.',
                ],
            ];

            foreach ($assignments as $asg) {
                VehicleAssignment::create($asg);
            }
        }
    }
}
