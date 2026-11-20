<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách quyền chuẩn theo chức năng nghiệp vụ thực tế có hiệu lực trong hệ thống
        $functionalPermissions = [
            // 1. Phân hệ Bán hàng
            ['slug' => 'orders.view', 'name' => 'Xem danh sách & chi tiết đơn hàng', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'view'],
            ['slug' => 'orders.view_all', 'name' => 'Xem toàn bộ đơn hàng tổng công ty', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'view_all'],
            ['slug' => 'orders.create', 'name' => 'Lên đơn hàng mới', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'create'],
            ['slug' => 'orders.edit', 'name' => 'Chỉnh sửa đơn hàng & trần chiết khấu', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'edit'],
            ['slug' => 'orders.approve', 'name' => 'Phê duyệt đơn hàng xuất bán', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'approve'],
            ['slug' => 'orders.cancel', 'name' => 'Hủy đơn hàng', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'cancel'],
            ['slug' => 'orders.delete', 'name' => 'Xóa đơn hàng vĩnh viễn', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'delete'],
            ['slug' => 'orders.status', 'name' => 'Cập nhật trạng thái đơn (kho/xe)', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'status'],

            ['slug' => 'quotation.view', 'name' => 'Xem danh sách báo giá', 'module' => 'quotation', 'category' => 'Bán hàng', 'action' => 'view'],
            ['slug' => 'quotation.create', 'name' => 'Lập bảng báo giá gửi khách', 'module' => 'quotation', 'category' => 'Bán hàng', 'action' => 'create'],

            ['slug' => 'returns.view', 'name' => 'Xem phiếu khách trả hàng', 'module' => 'returns', 'category' => 'Bán hàng', 'action' => 'view'],

            ['slug' => 'supplier_orders.view', 'name' => 'Xem danh sách đơn đặt NCC', 'module' => 'supplier_orders', 'category' => 'Bán hàng', 'action' => 'view'],
            ['slug' => 'supplier_orders.create', 'name' => 'Lập đơn đặt hàng nhà máy Tiền Phong/NCC', 'module' => 'supplier_orders', 'category' => 'Bán hàng', 'action' => 'create'],
            ['slug' => 'supplier_orders.approve', 'name' => 'Duyệt gửi đơn đặt hàng cho NCC', 'module' => 'supplier_orders', 'category' => 'Bán hàng', 'action' => 'approve'],

            ['slug' => 'consignments.view', 'name' => 'Xem danh sách đơn hàng ôm / ký gửi', 'module' => 'consignments', 'category' => 'Bán hàng', 'action' => 'view'],
            ['slug' => 'consignments.create', 'name' => 'Tạo đơn hàng ôm / ký gửi mới', 'module' => 'consignments', 'category' => 'Bán hàng', 'action' => 'create'],
            ['slug' => 'consignments.approve', 'name' => 'Duyệt đơn hàng ôm / ký gửi', 'module' => 'consignments', 'category' => 'Bán hàng', 'action' => 'approve'],

            // 2. Phân hệ Kho hàng
            ['slug' => 'inventory.view', 'name' => 'Tra cứu tồn kho & vị trí kệ', 'module' => 'inventory', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'products.view_all', 'name' => 'Xem toàn bộ danh mục sản phẩm (mọi hãng/NCC)', 'module' => 'inventory', 'category' => 'Kho hàng', 'action' => 'view_all'],
            ['slug' => 'inventory.update_price', 'name' => 'Cập nhật bảng giá niêm yết & trần CK', 'module' => 'inventory', 'category' => 'Kho hàng', 'action' => 'update_price'],
            ['slug' => 'inventory.delete', 'name' => 'Xóa sản phẩm hàng hóa trong danh mục kho', 'module' => 'inventory', 'category' => 'Kho hàng', 'action' => 'delete'],

            ['slug' => 'inv_import.view', 'name' => 'Xem danh sách phiếu nhập kho', 'module' => 'inv_import', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'inv_import.create', 'name' => 'Lập phiếu nhập kho mới', 'module' => 'inv_import', 'category' => 'Kho hàng', 'action' => 'create'],

            ['slug' => 'inv_export.view', 'name' => 'Xem danh sách phiếu xuất kho', 'module' => 'inv_export', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'inv_export.create', 'name' => 'Lập phiếu xuất kho', 'module' => 'inv_export', 'category' => 'Kho hàng', 'action' => 'create'],

            ['slug' => 'inv_transfer.view', 'name' => 'Xem lệnh điều chuyển kho nội bộ', 'module' => 'inv_transfer', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'inv_transfer.create', 'name' => 'Lập lệnh chuyển kho (Tổng - Xe - Chi nhánh)', 'module' => 'inv_transfer', 'category' => 'Kho hàng', 'action' => 'create'],

            ['slug' => 'inv_audit.view', 'name' => 'Xem danh sách đợt kiểm kê', 'module' => 'inv_audit', 'category' => 'Kho hàng', 'action' => 'view'],

            ['slug' => 'delivery_dispatch.view', 'name' => 'Xem bảng điều phối xe & lịch trình', 'module' => 'delivery_dispatch', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'delivery_dispatch.assign', 'name' => 'Điều xe & xếp đơn hàng vào lộ trình', 'module' => 'delivery_dispatch', 'category' => 'Kho hàng', 'action' => 'assign'],
            ['slug' => 'delivery_dispatch.dispatch', 'name' => 'Lệnh xuất bến giao hàng', 'module' => 'delivery_dispatch', 'category' => 'Kho hàng', 'action' => 'dispatch'],

            // 3. Phân hệ Soạn hàng
            ['slug' => 'inv_picking.view', 'name' => 'Xem danh sách đơn chờ bốc hàng', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'view'],
            ['slug' => 'inv_picking.view_all_categories', 'name' => 'Xem bốc xếp không phân loại sản phẩm', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'view_all_categories'],
            ['slug' => 'inv_picking.pick', 'name' => 'Bốc hàng & soạn đơn thực tế', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'pick'],
            ['slug' => 'inv_picking.assign', 'name' => 'Phân công nhân sự bốc hàng', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'assign'],
            ['slug' => 'inv_picking.qc', 'name' => 'Kiểm hàng QC đối chiếu quy cách', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'qc'],

            ['slug' => 'inv_qc.view', 'name' => 'Xem danh sách đơn cần kiểm QC', 'module' => 'inv_qc', 'category' => 'Soạn hàng', 'action' => 'view'],
            ['slug' => 'inv_qc.qc', 'name' => 'Đối chiếu kiểm hàng, tem mác, quy cách', 'module' => 'inv_qc', 'category' => 'Soạn hàng', 'action' => 'qc'],
            ['slug' => 'inv_qc.approve', 'name' => 'Phê duyệt đạt chuẩn kiểm định xuất kho', 'module' => 'inv_qc', 'category' => 'Soạn hàng', 'action' => 'approve'],

            // 4. Phân hệ Tài chính & Quỹ
            ['slug' => 'funds.view', 'name' => 'Xem sổ quỹ tiền mặt & số dư ngân hàng', 'module' => 'funds', 'category' => 'Tài chính', 'action' => 'view'],

            ['slug' => 'fleet.view', 'name' => 'Xem danh sách xe & hồ sơ chi phí xe', 'module' => 'fleet', 'category' => 'Tài chính', 'action' => 'view'],
            ['slug' => 'fleet.manage', 'name' => 'Quản lý thông tin xe, bảo dưỡng, định mức', 'module' => 'fleet', 'category' => 'Tài chính', 'action' => 'manage'],

            ['slug' => 'driver_debt.view', 'name' => 'Xem bảng kê công nợ trách nhiệm lái xe', 'module' => 'driver_debt', 'category' => 'Tài chính', 'action' => 'view'],
            ['slug' => 'staff_debt.view_all', 'name' => 'Xem tất cả công nợ nhân viên', 'module' => 'staff_debt', 'category' => 'Tài chính', 'action' => 'view_all'],

            // 5. Phân hệ Đại lý & Khách hàng
            ['slug' => 'dealers.view', 'name' => 'Xem danh bạ đại lý & thông tin liên hệ', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'view'],
            ['slug' => 'dealers.view_all', 'name' => 'Xem toàn bộ danh bạ khách hàng đại lý', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'view_all'],
            ['slug' => 'dealers.create', 'name' => 'Thêm mới đại lý & định vị tọa độ GPS', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'create'],
            ['slug' => 'dealers.edit', 'name' => 'Chỉnh sửa hồ sơ đại lý', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'edit'],
            ['slug' => 'dealers.delete', 'name' => 'Xóa hồ sơ đại lý khỏi hệ thống', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'delete'],

            ['slug' => 'dealer_care.view', 'name' => 'Xem lịch sử chăm sóc đại lý thực địa', 'module' => 'dealer_care', 'category' => 'Đại lý', 'action' => 'view'],

            ['slug' => 'debt.view', 'name' => 'Xem bảng tổng hợp công nợ & tuổi nợ đại lý', 'module' => 'debt', 'category' => 'Đại lý', 'action' => 'view'],
            ['slug' => 'debt.approve', 'name' => 'Duyệt bảo lãnh xuất hàng vượt hạn mức nợ', 'module' => 'debt', 'category' => 'Đại lý', 'action' => 'approve'],

            ['slug' => 'sales_policy.view', 'name' => 'Xem danh sách chính sách chiết khấu & giá', 'module' => 'sales_policy', 'category' => 'Đại lý', 'action' => 'view'],
            ['slug' => 'sales_policy.create', 'name' => 'Tạo chính sách bán hàng & chiết khấu mới', 'module' => 'sales_policy', 'category' => 'Đại lý', 'action' => 'create'],
            ['slug' => 'sales_policy.edit', 'name' => 'Chỉnh sửa chính sách bán hàng', 'module' => 'sales_policy', 'category' => 'Đại lý', 'action' => 'edit'],

            // 6. Phân hệ Nhân sự & Tiền lương
            ['slug' => 'staff_list.view', 'name' => 'Xem danh sách nhân viên & sơ yếu lý lịch', 'module' => 'staff_list', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'staff_list.create', 'name' => 'Thêm mới hồ sơ nhân viên', 'module' => 'staff_list', 'category' => 'Nhân sự', 'action' => 'create'],
            ['slug' => 'staff_list.edit', 'name' => 'Chỉnh sửa hồ sơ, chức vụ & phòng ban', 'module' => 'staff_list', 'category' => 'Nhân sự', 'action' => 'edit'],
            ['slug' => 'staff_list.lock', 'name' => 'Khóa / Mở khóa tài khoản nhân sự', 'module' => 'staff_list', 'category' => 'Nhân sự', 'action' => 'lock'],

            ['slug' => 'payroll.view', 'name' => 'Xem bảng lương chi tiết nhân sự', 'module' => 'payroll', 'category' => 'Nhân sự', 'action' => 'view'],

            ['slug' => 'attendance.view', 'name' => 'Xem bảng chấm công toàn công ty', 'module' => 'attendance', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'attendance.checkin', 'name' => 'Chấm công GPS Vào ca / Ra ca', 'module' => 'attendance', 'category' => 'Nhân sự', 'action' => 'checkin'],

            ['slug' => 'kpi.view', 'name' => 'Xem báo cáo KPI phòng ban & cá nhân', 'module' => 'kpi', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'kpi.evaluate', 'name' => 'Chấm điểm KPI nhân viên theo tháng', 'module' => 'kpi', 'category' => 'Nhân sự', 'action' => 'evaluate'],

            ['slug' => 'permissions.view', 'name' => 'Xem ma trận phân quyền hệ thống', 'module' => 'permissions', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'permissions.manage_role', 'name' => 'Cấu hình quyền cho từng Vai trò', 'module' => 'permissions', 'category' => 'Nhân sự', 'action' => 'manage_role'],
            ['slug' => 'permissions.manage_user', 'name' => 'Cấu hình quyền cá nhân hóa theo Nhân sự', 'module' => 'permissions', 'category' => 'Nhân sự', 'action' => 'manage_user'],

            // 7. Phân hệ Công việc & Quy trình
            ['slug' => 'task_assignment.view', 'name' => 'Xem danh sách công việc & tiến độ', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'view'],
            ['slug' => 'task_assignment.view_all', 'name' => 'Xem tất cả công việc của toàn bộ nhân viên', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'view_all'],
            ['slug' => 'task_assignment.tab_installations', 'name' => 'Xem Tab Đơn Lắp Đặt Máy Lọc', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'view_installations'],
            ['slug' => 'task_assignment.tab_maintenance', 'name' => 'Xem Tab Đơn Bảo Dưỡng & Thay Lõi', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'view_maintenance'],
            ['slug' => 'task_assignment.tab_tasks', 'name' => 'Xem Tab Nhiệm Vụ & Phân Công Nội Bộ', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'view_tasks'],
            ['slug' => 'task_assignment.tab_warehouse_export', 'name' => 'Xem Tab Lấy Hàng Từ Kho Đi Làm', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'view_warehouse_export'],
            ['slug' => 'task_assignment.assign', 'name' => 'Phân công công việc nội bộ', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'assign'],

            ['slug' => 'proposals.view', 'name' => 'Xem danh sách đề xuất & trình ký', 'module' => 'proposals', 'category' => 'Công việc', 'action' => 'view'],
            ['slug' => 'proposals.create', 'name' => 'Lập đề xuất mới (mua sắm, cọc, chi hộ)', 'module' => 'proposals', 'category' => 'Công việc', 'action' => 'create'],

            ['slug' => 'workflow.view', 'name' => 'Xem cấu hình quy trình phê duyệt', 'module' => 'workflow', 'category' => 'Công việc', 'action' => 'view'],

            // 8. Phân hệ Báo cáo & Hệ thống
            ['slug' => 'dashboard.view', 'name' => 'Xem bảng điều khiển tổng quan', 'module' => 'dashboard', 'category' => 'Hệ thống', 'action' => 'view'],
            ['slug' => 'reports.view', 'name' => 'Xem trung tâm báo cáo tổng hợp', 'module' => 'reports', 'category' => 'Hệ thống', 'action' => 'view'],

            ['slug' => 'audit.view', 'name' => 'Xem nhật ký truy vết người dùng (Audit Log)', 'module' => 'audit', 'category' => 'Hệ thống', 'action' => 'view'],
            ['slug' => 'audit.lock_period', 'name' => 'Khóa kỳ kế toán không cho sửa số liệu', 'module' => 'audit', 'category' => 'Hệ thống', 'action' => 'lock_period'],

            ['slug' => 'danhmuc.view', 'name' => 'Xem danh mục hệ thống (kho, xe, loại quỹ...)', 'module' => 'danhmuc', 'category' => 'Hệ thống', 'action' => 'view'],
            ['slug' => 'danhmuc.manage', 'name' => 'Thêm, sửa, xóa cấu hình danh mục', 'module' => 'danhmuc', 'category' => 'Hệ thống', 'action' => 'manage'],
            ['slug' => 'danhmuc.edit', 'name' => 'Điều chỉnh thông tin danh mục', 'module' => 'danhmuc', 'category' => 'Hệ thống', 'action' => 'edit'],

            ['slug' => 'projects.view', 'name' => 'Xem danh sách dự án', 'module' => 'projects', 'category' => 'Hệ thống', 'action' => 'view'],
            ['slug' => 'suppliers.view', 'name' => 'Xem danh sách nhà cung cấp', 'module' => 'suppliers', 'category' => 'Hệ thống', 'action' => 'view'],
            ['slug' => 'settings.view', 'name' => 'Xem cấu hình hệ thống', 'module' => 'settings', 'category' => 'Hệ thống', 'action' => 'view'],

            // 9. Phân hệ Lắp đặt & Dịch vụ Máy lọc nước
            ['slug' => 'water_filter.view', 'name' => 'Xem phân hệ máy lọc nước', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'view'],
            ['slug' => 'water_filter.install_view', 'name' => 'Xem danh sách & chi tiết đơn lắp đặt', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'view'],
            ['slug' => 'water_filter.install_assign', 'name' => 'Phân công KTV lắp đặt máy', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'assign'],
            ['slug' => 'water_filter.install_delete', 'name' => 'Xóa đơn lắp đặt máy lọc', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'delete'],

            ['slug' => 'water_filter.repair_view', 'name' => 'Xem danh sách tiếp nhận sửa chữa', 'module' => 'water_filter_repair', 'category' => 'Máy lọc nước', 'action' => 'view'],
            ['slug' => 'water_filter.repair_assign', 'name' => 'Phân công KTV sửa chữa & bảo dưỡng', 'module' => 'water_filter_repair', 'category' => 'Máy lọc nước', 'action' => 'assign'],
            ['slug' => 'water_filter.repair_delete', 'name' => 'Xóa phiếu tiếp nhận sửa chữa', 'module' => 'water_filter_repair', 'category' => 'Máy lọc nước', 'action' => 'delete'],

            ['slug' => 'water_filter.parts_view', 'name' => 'Tra cứu tồn kho linh kiện & phụ tùng', 'module' => 'water_filter_parts', 'category' => 'Máy lọc nước', 'action' => 'view'],
            ['slug' => 'water_filter.parts_delete', 'name' => 'Xóa mã phụ tùng linh kiện', 'module' => 'water_filter_parts', 'category' => 'Máy lọc nước', 'action' => 'delete'],
            ['slug' => 'water_filter.models_manage', 'name' => 'Quản lý danh mục Model máy lọc nước', 'module' => 'water_filter_parts', 'category' => 'Máy lọc nước', 'action' => 'manage'],
        ];

        // 1. Tạo hoặc cập nhật các quyền hợp lệ
        foreach ($functionalPermissions as $p) {
            Permission::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'module' => $p['module'],
                    'category' => $p['category'],
                    'action' => $p['action'],
                    'description' => "Quyền {$p['name']} thuộc phân hệ {$p['category']} ({$p['module']})",
                ]
            );
        }

        // 2. Dọn sạch toàn bộ các quyền thừa / không có tác dụng khỏi cơ sở dữ liệu
        $validSlugs = collect($functionalPermissions)->pluck('slug')->toArray();
        Permission::whereNotIn('slug', $validSlugs)->delete();
    }
}
