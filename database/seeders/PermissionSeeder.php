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
        // Danh sách quyền theo chức năng nghiệp vụ thực tế của từng phân hệ
        $functionalPermissions = [
            // 1. Phân hệ Bán hàng
            ['slug' => 'orders.view', 'name' => 'Xem danh sách & chi tiết đơn hàng', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'view'],
            ['slug' => 'orders.create', 'name' => 'Lên đơn hàng mới', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'create'],
            ['slug' => 'orders.edit', 'name' => 'Chỉnh sửa đơn hàng & trần chiết khấu', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'edit'],
            ['slug' => 'orders.approve', 'name' => 'Phê duyệt đơn hàng xuất bán', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'approve'],
            ['slug' => 'orders.cancel', 'name' => 'Hủy đơn hàng', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'cancel'],
            ['slug' => 'orders.status', 'name' => 'Cập nhật trạng thái đơn (kho/xe)', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'status'],
            ['slug' => 'orders.export', 'name' => 'In phiếu bán hàng & Xuất Excel', 'module' => 'orders', 'category' => 'Bán hàng', 'action' => 'export'],

            ['slug' => 'quotation.view', 'name' => 'Xem danh sách báo giá', 'module' => 'quotation', 'category' => 'Bán hàng', 'action' => 'view'],
            ['slug' => 'quotation.create', 'name' => 'Lập bảng báo giá gửi khách', 'module' => 'quotation', 'category' => 'Bán hàng', 'action' => 'create'],
            ['slug' => 'quotation.edit', 'name' => 'Sửa đơn giá & chiết khấu báo giá', 'module' => 'quotation', 'category' => 'Bán hàng', 'action' => 'edit'],
            ['slug' => 'quotation.convert', 'name' => 'Chuyển báo giá thành đơn hàng', 'module' => 'quotation', 'category' => 'Bán hàng', 'action' => 'convert'],
            ['slug' => 'quotation.export', 'name' => 'In & xuất file báo giá gửi khách', 'module' => 'quotation', 'category' => 'Bán hàng', 'action' => 'export'],

            ['slug' => 'returns.view', 'name' => 'Xem phiếu khách trả hàng', 'module' => 'returns', 'category' => 'Bán hàng', 'action' => 'view'],
            ['slug' => 'returns.create', 'name' => 'Lập phiếu yêu cầu khách trả hàng', 'module' => 'returns', 'category' => 'Bán hàng', 'action' => 'create'],
            ['slug' => 'returns.approve', 'name' => 'Duyệt nhận hàng trả lại hoàn kho', 'module' => 'returns', 'category' => 'Bán hàng', 'action' => 'approve'],
            ['slug' => 'returns.cancel', 'name' => 'Hủy yêu cầu trả hàng', 'module' => 'returns', 'category' => 'Bán hàng', 'action' => 'cancel'],

            ['slug' => 'supplier_orders.view', 'name' => 'Xem danh sách đơn đặt NCC', 'module' => 'supplier_orders', 'category' => 'Bán hàng', 'action' => 'view'],
            ['slug' => 'supplier_orders.create', 'name' => 'Lập đơn đặt hàng nhà máy Tiền Phong/NCC', 'module' => 'supplier_orders', 'category' => 'Bán hàng', 'action' => 'create'],
            ['slug' => 'supplier_orders.approve', 'name' => 'Duyệt gửi đơn đặt hàng cho NCC', 'module' => 'supplier_orders', 'category' => 'Bán hàng', 'action' => 'approve'],
            ['slug' => 'supplier_orders.receive', 'name' => 'Xác nhận nhập hàng từ đơn NCC', 'module' => 'supplier_orders', 'category' => 'Bán hàng', 'action' => 'receive'],

            // 2. Phân hệ Kho hàng
            ['slug' => 'inventory.view', 'name' => 'Tra cứu tồn kho & vị trí kệ', 'module' => 'inventory', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'inventory.update_price', 'name' => 'Cập nhật bảng giá niêm yết & trần CK', 'module' => 'inventory', 'category' => 'Kho hàng', 'action' => 'update_price'],
            ['slug' => 'inventory.export', 'name' => 'Xuất bảng kê tồn kho Excel', 'module' => 'inventory', 'category' => 'Kho hàng', 'action' => 'export'],

            ['slug' => 'inv_import.view', 'name' => 'Xem danh sách phiếu nhập kho', 'module' => 'inv_import', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'inv_import.create', 'name' => 'Lập phiếu nhập kho mới', 'module' => 'inv_import', 'category' => 'Kho hàng', 'action' => 'create'],
            ['slug' => 'inv_import.approve', 'name' => 'Xác nhận nhập kho thực tế (tăng tồn)', 'module' => 'inv_import', 'category' => 'Kho hàng', 'action' => 'approve'],
            ['slug' => 'inv_import.export', 'name' => 'In phiếu nhập kho', 'module' => 'inv_import', 'category' => 'Kho hàng', 'action' => 'export'],

            ['slug' => 'inv_export.view', 'name' => 'Xem danh sách phiếu xuất kho', 'module' => 'inv_export', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'inv_export.create', 'name' => 'Lập phiếu xuất kho', 'module' => 'inv_export', 'category' => 'Kho hàng', 'action' => 'create'],
            ['slug' => 'inv_export.approve', 'name' => 'Xác nhận xuất kho thực tế (trừ tồn)', 'module' => 'inv_export', 'category' => 'Kho hàng', 'action' => 'approve'],
            ['slug' => 'inv_export.export', 'name' => 'In lệnh xuất kho & phiếu xuất', 'module' => 'inv_export', 'category' => 'Kho hàng', 'action' => 'export'],

            ['slug' => 'inv_transfer.view', 'name' => 'Xem lệnh điều chuyển kho nội bộ', 'module' => 'inv_transfer', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'inv_transfer.create', 'name' => 'Lập lệnh chuyển kho (Tổng - Xe - Chi nhánh)', 'module' => 'inv_transfer', 'category' => 'Kho hàng', 'action' => 'create'],
            ['slug' => 'inv_transfer.approve', 'name' => 'Xác nhận chuyển kho đi & nhập kho đích', 'module' => 'inv_transfer', 'category' => 'Kho hàng', 'action' => 'approve'],
            ['slug' => 'inv_transfer.export', 'name' => 'In phiếu điều chuyển kho', 'module' => 'inv_transfer', 'category' => 'Kho hàng', 'action' => 'export'],

            ['slug' => 'inv_audit.view', 'name' => 'Xem danh sách đợt kiểm kê', 'module' => 'inv_audit', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'inv_audit.create', 'name' => 'Tạo đợt kiểm kê kho mới', 'module' => 'inv_audit', 'category' => 'Kho hàng', 'action' => 'create'],
            ['slug' => 'inv_audit.count', 'name' => 'Nhập số lượng kiểm đếm thực tế', 'module' => 'inv_audit', 'category' => 'Kho hàng', 'action' => 'count'],
            ['slug' => 'inv_audit.balance', 'name' => 'Cân đối kho (điều chỉnh lệch thực tế vs sổ)', 'module' => 'inv_audit', 'category' => 'Kho hàng', 'action' => 'balance'],
            ['slug' => 'inv_audit.approve', 'name' => 'Duyệt kết quả kiểm kê & khóa kỳ', 'module' => 'inv_audit', 'category' => 'Kho hàng', 'action' => 'approve'],

            ['slug' => 'delivery_dispatch.view', 'name' => 'Xem bảng điều phối xe & lịch trình', 'module' => 'delivery_dispatch', 'category' => 'Kho hàng', 'action' => 'view'],
            ['slug' => 'delivery_dispatch.assign', 'name' => 'Điều xe & xếp đơn hàng vào lộ trình', 'module' => 'delivery_dispatch', 'category' => 'Kho hàng', 'action' => 'assign'],
            ['slug' => 'delivery_dispatch.dispatch', 'name' => 'Lệnh xuất bến giao hàng', 'module' => 'delivery_dispatch', 'category' => 'Kho hàng', 'action' => 'dispatch'],
            ['slug' => 'delivery_dispatch.settle', 'name' => 'Quyết toán chuyến xe (tiền COD & chi phí)', 'module' => 'delivery_dispatch', 'category' => 'Kho hàng', 'action' => 'settle'],

            // 3. Phân hệ Soạn hàng
            ['slug' => 'inv_picking.view', 'name' => 'Xem danh sách đơn chờ bốc hàng', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'view'],
            ['slug' => 'inv_picking.view_all_categories', 'name' => 'Xem bốc xếp không phân loại sản phẩm', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'view_all_categories'],
            ['slug' => 'inv_picking.pick', 'name' => 'Bốc hàng & soạn đơn thực tế, báo thiếu', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'pick'],
            ['slug' => 'inv_picking.assign', 'name' => 'Phân công nhân sự bốc hàng', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'assign'],
            ['slug' => 'inv_picking.qc', 'name' => 'Kiểm hàng QC đối chiếu quy cách', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'qc'],
            ['slug' => 'inv_picking.approve', 'name' => 'Phê duyệt QC đạt chuẩn xuất xe', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'approve'],
            ['slug' => 'inv_picking.export', 'name' => 'In phiếu nhặt hàng (Pick-list) & xuất file', 'module' => 'inv_picking', 'category' => 'Soạn hàng', 'action' => 'export'],

            ['slug' => 'inv_qc.view', 'name' => 'Xem danh sách đơn cần kiểm QC', 'module' => 'inv_qc', 'category' => 'Soạn hàng', 'action' => 'view'],
            ['slug' => 'inv_qc.qc', 'name' => 'Đối chiếu kiểm hàng, tem mác, quy cách', 'module' => 'inv_qc', 'category' => 'Soạn hàng', 'action' => 'qc'],
            ['slug' => 'inv_qc.approve', 'name' => 'Phê duyệt đạt chuẩn kiểm định xuất kho', 'module' => 'inv_qc', 'category' => 'Soạn hàng', 'action' => 'approve'],
            ['slug' => 'inv_qc.export', 'name' => 'In biên bản kiểm định QC', 'module' => 'inv_qc', 'category' => 'Soạn hàng', 'action' => 'export'],

            // 4. Phân hệ Tài chính & Quỹ
            ['slug' => 'funds.view', 'name' => 'Xem sổ quỹ tiền mặt & số dư ngân hàng', 'module' => 'funds', 'category' => 'Tài chính', 'action' => 'view'],
            ['slug' => 'funds.create_receipt', 'name' => 'Lập phiếu thu tiền (thu nợ, bán hàng)', 'module' => 'funds', 'category' => 'Tài chính', 'action' => 'create_receipt'],
            ['slug' => 'funds.create_payment', 'name' => 'Lập phiếu chi tiền (mua hàng, chi phí)', 'module' => 'funds', 'category' => 'Tài chính', 'action' => 'create_payment'],
            ['slug' => 'funds.transfer', 'name' => 'Chuyển quỹ nội bộ giữa các tài khoản', 'module' => 'funds', 'category' => 'Tài chính', 'action' => 'transfer'],
            ['slug' => 'funds.approve', 'name' => 'Phê duyệt phiếu chi tiền', 'module' => 'funds', 'category' => 'Tài chính', 'action' => 'approve'],
            ['slug' => 'funds.export', 'name' => 'In phiếu thu chi & xuất sổ quỹ Excel', 'module' => 'funds', 'category' => 'Tài chính', 'action' => 'export'],

            ['slug' => 'fleet.view', 'name' => 'Xem danh sách xe & hồ sơ chi phí xe', 'module' => 'fleet', 'category' => 'Tài chính', 'action' => 'view'],
            ['slug' => 'fleet.settle', 'name' => 'Chốt quyết toán chuyến xe & tiền mặt COD', 'module' => 'fleet', 'category' => 'Tài chính', 'action' => 'settle'],
            ['slug' => 'fleet.manage', 'name' => 'Quản lý thông tin xe, bảo dưỡng, định mức', 'module' => 'fleet', 'category' => 'Tài chính', 'action' => 'manage'],

            ['slug' => 'driver_debt.view', 'name' => 'Xem bảng kê công nợ trách nhiệm lái xe', 'module' => 'driver_debt', 'category' => 'Tài chính', 'action' => 'view'],
            ['slug' => 'driver_debt.settle', 'name' => 'Thu hồi tiền mặt lái xe nộp về quỹ kế toán', 'module' => 'driver_debt', 'category' => 'Tài chính', 'action' => 'settle'],

            // 5. Phân hệ Đại lý & Khách hàng
            ['slug' => 'dealers.view', 'name' => 'Xem danh bạ đại lý & thông tin liên hệ', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'view'],
            ['slug' => 'dealers.create', 'name' => 'Thêm mới đại lý & định vị tọa độ GPS', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'create'],
            ['slug' => 'dealers.edit', 'name' => 'Chỉnh sửa hồ sơ đại lý', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'edit'],
            ['slug' => 'dealers.export', 'name' => 'Xuất danh bạ đại lý ra Excel', 'module' => 'dealers', 'category' => 'Đại lý', 'action' => 'export'],

            ['slug' => 'dealer_care.view', 'name' => 'Xem lịch sử chăm sóc đại lý thực địa', 'module' => 'dealer_care', 'category' => 'Đại lý', 'action' => 'view'],
            ['slug' => 'dealer_care.checkin', 'name' => 'Check-in GPS chụp ảnh thực địa tại đại lý', 'module' => 'dealer_care', 'category' => 'Đại lý', 'action' => 'checkin'],
            ['slug' => 'dealer_care.feedback', 'name' => 'Ghi nhận ý kiến phản hồi thị trường', 'module' => 'dealer_care', 'category' => 'Đại lý', 'action' => 'feedback'],

            ['slug' => 'debt.view', 'name' => 'Xem bảng tổng hợp công nợ & tuổi nợ đại lý', 'module' => 'debt', 'category' => 'Đại lý', 'action' => 'view'],
            ['slug' => 'debt.set_limit', 'name' => 'Cài đặt hạn mức nợ & số ngày nợ tối đa', 'module' => 'debt', 'category' => 'Đại lý', 'action' => 'set_limit'],
            ['slug' => 'debt.adjust', 'name' => 'Điều chỉnh & đối trừ công nợ đại lý', 'module' => 'debt', 'category' => 'Đại lý', 'action' => 'adjust'],
            ['slug' => 'debt.export', 'name' => 'In biên bản đối chiếu nợ & xuất file', 'module' => 'debt', 'category' => 'Đại lý', 'action' => 'export'],

            ['slug' => 'sales_policy.view', 'name' => 'Xem danh sách chính sách chiết khấu & giá', 'module' => 'sales_policy', 'category' => 'Đại lý', 'action' => 'view'],
            ['slug' => 'sales_policy.create', 'name' => 'Tạo chính sách bán hàng & chiết khấu mới', 'module' => 'sales_policy', 'category' => 'Đại lý', 'action' => 'create'],
            ['slug' => 'sales_policy.edit', 'name' => 'Chỉnh sửa chính sách bán hàng', 'module' => 'sales_policy', 'category' => 'Đại lý', 'action' => 'edit'],
            ['slug' => 'sales_policy.delete', 'name' => 'Xóa / Ngừng kích hoạt chính sách', 'module' => 'sales_policy', 'category' => 'Đại lý', 'action' => 'delete'],

            // 6. Phân hệ Nhân sự & Tiền lương
            ['slug' => 'staff_list.view', 'name' => 'Xem danh sách nhân viên & sơ yếu lý lịch', 'module' => 'staff_list', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'staff_list.create', 'name' => 'Thêm mới hồ sơ nhân viên', 'module' => 'staff_list', 'category' => 'Nhân sự', 'action' => 'create'],
            ['slug' => 'staff_list.edit', 'name' => 'Chỉnh sửa hồ sơ, chức vụ & phòng ban', 'module' => 'staff_list', 'category' => 'Nhân sự', 'action' => 'edit'],
            ['slug' => 'staff_list.lock', 'name' => 'Khóa / Mở khóa tài khoản nhân sự', 'module' => 'staff_list', 'category' => 'Nhân sự', 'action' => 'lock'],

            ['slug' => 'payroll.view', 'name' => 'Xem bảng lương chi tiết nhân sự', 'module' => 'payroll', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'payroll.calculate', 'name' => 'Tính toán bảng lương theo KPI & công', 'module' => 'payroll', 'category' => 'Nhân sự', 'action' => 'calculate'],
            ['slug' => 'payroll.lock', 'name' => 'Khóa bảng lương chuyển chi trả', 'module' => 'payroll', 'category' => 'Nhân sự', 'action' => 'lock'],
            ['slug' => 'payroll.export', 'name' => 'In phiếu lương & xuất bảng lương Excel', 'module' => 'payroll', 'category' => 'Nhân sự', 'action' => 'export'],

            ['slug' => 'attendance.view', 'name' => 'Xem bảng chấm công toàn công ty', 'module' => 'attendance', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'attendance.checkin', 'name' => 'Chấm công GPS Vào ca / Ra ca', 'module' => 'attendance', 'category' => 'Nhân sự', 'action' => 'checkin'],
            ['slug' => 'attendance.approve', 'name' => 'Duyệt giải trình quên chấm công & nghỉ phép', 'module' => 'attendance', 'category' => 'Nhân sự', 'action' => 'approve'],

            ['slug' => 'kpi.view', 'name' => 'Xem báo cáo KPI phòng ban & cá nhân', 'module' => 'kpi', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'kpi.evaluate', 'name' => 'Chấm điểm KPI nhân viên theo tháng', 'module' => 'kpi', 'category' => 'Nhân sự', 'action' => 'evaluate'],
            ['slug' => 'kpi.approve', 'name' => 'Phê duyệt kết quả đánh giá KPI', 'module' => 'kpi', 'category' => 'Nhân sự', 'action' => 'approve'],

            ['slug' => 'permissions.view', 'name' => 'Xem ma trận phân quyền hệ thống', 'module' => 'permissions', 'category' => 'Nhân sự', 'action' => 'view'],
            ['slug' => 'permissions.manage_role', 'name' => 'Cấu hình quyền cho từng Vai trò', 'module' => 'permissions', 'category' => 'Nhân sự', 'action' => 'manage_role'],
            ['slug' => 'permissions.manage_user', 'name' => 'Cấu hình quyền cá nhân hóa theo Nhân sự', 'module' => 'permissions', 'category' => 'Nhân sự', 'action' => 'manage_user'],

            // 7. Phân hệ Công việc & Quy trình
            ['slug' => 'task_assignment.view', 'name' => 'Xem danh sách công việc & tiến độ', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'view'],
            ['slug' => 'task_assignment.create', 'name' => 'Giao việc mới cho nhân viên/phòng ban', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'create'],
            ['slug' => 'task_assignment.report', 'name' => 'Cập nhật tiến độ & hoàn thành công việc', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'report'],
            ['slug' => 'task_assignment.evaluate', 'name' => 'Đánh giá chất lượng hoàn thành công việc', 'module' => 'task_assignment', 'category' => 'Công việc', 'action' => 'evaluate'],

            ['slug' => 'proposals.view', 'name' => 'Xem danh sách đề xuất & trình ký', 'module' => 'proposals', 'category' => 'Công việc', 'action' => 'view'],
            ['slug' => 'proposals.create', 'name' => 'Lập đề xuất mới (mua sắm, cọc, chi hộ)', 'module' => 'proposals', 'category' => 'Công việc', 'action' => 'create'],
            ['slug' => 'proposals.approve', 'name' => 'Ký phê duyệt đề xuất', 'module' => 'proposals', 'category' => 'Công việc', 'action' => 'approve'],
            ['slug' => 'proposals.reject', 'name' => 'Từ chối duyệt đề xuất', 'module' => 'proposals', 'category' => 'Công việc', 'action' => 'reject'],

            ['slug' => 'workflow.view', 'name' => 'Xem cấu hình quy trình phê duyệt', 'module' => 'workflow', 'category' => 'Công việc', 'action' => 'view'],
            ['slug' => 'workflow.manage', 'name' => 'Thiết lập các cấp duyệt & người phê duyệt', 'module' => 'workflow', 'category' => 'Công việc', 'action' => 'manage'],

            // 8. Phân hệ Báo cáo & Hệ thống
            ['slug' => 'dashboard.view_revenue', 'name' => 'Xem báo cáo doanh thu & lợi nhuận', 'module' => 'dashboard', 'category' => 'Hệ thống', 'action' => 'view_revenue'],
            ['slug' => 'dashboard.view_debt', 'name' => 'Xem báo cáo tổng hợp công nợ', 'module' => 'dashboard', 'category' => 'Hệ thống', 'action' => 'view_debt'],
            ['slug' => 'dashboard.view_inventory', 'name' => 'Xem báo cáo xuất nhập tồn kho', 'module' => 'dashboard', 'category' => 'Hệ thống', 'action' => 'view_inventory'],

            ['slug' => 'audit.view', 'name' => 'Xem nhật ký truy vết người dùng (Audit Log)', 'module' => 'audit', 'category' => 'Hệ thống', 'action' => 'view'],
            ['slug' => 'audit.lock_period', 'name' => 'Khóa kỳ kế toán không cho sửa số liệu', 'module' => 'audit', 'category' => 'Hệ thống', 'action' => 'lock_period'],

            ['slug' => 'danhmuc.view', 'name' => 'Xem danh mục hệ thống (kho, xe, loại quỹ...)', 'module' => 'danhmuc', 'category' => 'Hệ thống', 'action' => 'view'],
            ['slug' => 'danhmuc.manage', 'name' => 'Thêm, sửa, xóa cấu hình danh mục', 'module' => 'danhmuc', 'category' => 'Hệ thống', 'action' => 'manage'],

            // 9. Phân hệ Lắp đặt & Dịch vụ Máy lọc nước
            ['slug' => 'water_filter.view', 'name' => 'Xem phân hệ máy lọc nước', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'view'],
            ['slug' => 'water_filter.install_view', 'name' => 'Xem danh sách & chi tiết đơn lắp đặt', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'view'],
            ['slug' => 'water_filter.install_create', 'name' => 'Lên đơn đi lắp đặt máy lọc mới', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'create'],
            ['slug' => 'water_filter.install_edit', 'name' => 'Chỉnh sửa đơn lắp đặt & điều KTV', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'edit'],
            ['slug' => 'water_filter.install_status', 'name' => 'Cập nhật trạng thái, TDS & nghiệm thu', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'status'],
            ['slug' => 'water_filter.install_delete', 'name' => 'Xóa đơn lắp đặt máy lọc', 'module' => 'water_filter_install', 'category' => 'Máy lọc nước', 'action' => 'delete'],

            ['slug' => 'water_filter.repair_view', 'name' => 'Xem danh sách tiếp nhận sửa chữa', 'module' => 'water_filter_repair', 'category' => 'Máy lọc nước', 'action' => 'view'],
            ['slug' => 'water_filter.repair_create', 'name' => 'Tiếp nhận máy sửa chữa / bảo hành mới', 'module' => 'water_filter_repair', 'category' => 'Máy lọc nước', 'action' => 'create'],
            ['slug' => 'water_filter.repair_edit', 'name' => 'Cập nhật chẩn đoán & linh kiện thay', 'module' => 'water_filter_repair', 'category' => 'Máy lọc nước', 'action' => 'edit'],
            ['slug' => 'water_filter.repair_status', 'name' => 'Cập nhật tiến độ & bàn giao máy sửa', 'module' => 'water_filter_repair', 'category' => 'Máy lọc nước', 'action' => 'status'],
            ['slug' => 'water_filter.repair_delete', 'name' => 'Xóa phiếu tiếp nhận sửa chữa', 'module' => 'water_filter_repair', 'category' => 'Máy lọc nước', 'action' => 'delete'],

            ['slug' => 'water_filter.parts_view', 'name' => 'Tra cứu tồn kho linh kiện & phụ tùng', 'module' => 'water_filter_parts', 'category' => 'Máy lọc nước', 'action' => 'view'],
            ['slug' => 'water_filter.parts_manage', 'name' => 'Quản lý thông tin & giá xuất phụ tùng', 'module' => 'water_filter_parts', 'category' => 'Máy lọc nước', 'action' => 'manage'],
            ['slug' => 'water_filter.parts_import', 'name' => 'Lập phiếu nhập kho phụ tùng / linh kiện', 'module' => 'water_filter_parts', 'category' => 'Máy lọc nước', 'action' => 'create'],
            ['slug' => 'water_filter.parts_delete', 'name' => 'Xóa mã phụ tùng linh kiện', 'module' => 'water_filter_parts', 'category' => 'Máy lọc nước', 'action' => 'delete'],
            ['slug' => 'water_filter.models_manage', 'name' => 'Quản lý danh mục Model máy lọc nước', 'module' => 'water_filter_parts', 'category' => 'Máy lọc nước', 'action' => 'manage'],
        ];

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
    }
}
