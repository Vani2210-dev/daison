<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolesData = [
            [
                'slug' => 'Manager',
                'name' => 'Ban Giám Đốc / Quản Lý',
                'description' => 'Toàn quyền điều hành và xem toàn bộ hệ thống ERP',
                'permissions' => ['ALL'],
            ],
            [
                'slug' => 'Sales_Accountant',
                'name' => 'Kế Toán Bán Hàng',
                'description' => 'Quản lý đơn hàng, xuất bán, duyệt công nợ đại lý và thu chi bán hàng',
                'permissions' => [
                    'orders.view', 'orders.create', 'orders.edit', 'orders.approve', 'orders.cancel', 'orders.delete', 'orders.status',
                    'quotation.view', 'quotation.create',
                    'returns.view',
                    'dealers.view', 'dealers.create', 'dealers.edit', 'dealers.delete',
                    'debt.view', 'debt.approve', 'staff_debt.view_all',
                    'sales_policy.view', 'sales_policy.create', 'sales_policy.edit',
                    'funds.view',
                    'inventory.view',
                    'task_assignment.view',
                    'proposals.view', 'proposals.create',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Sales_Rep',
                'name' => 'Nhân Viên Kinh Doanh / Thị Trường',
                'description' => 'Mở rộng đại lý, check-in GPS thực địa, lên đơn hàng và báo cáo công việc',
                'permissions' => [
                    'orders.view', 'orders.create',
                    'quotation.view', 'quotation.create',
                    'dealers.view', 'dealers.create', 'dealers.edit',
                    'dealer_care.view',
                    'sales_policy.view',
                    'inventory.view',
                    'task_assignment.view',
                    'proposals.view', 'proposals.create',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Warehouse_Manager',
                'name' => 'Quản Lý Kho Tổng',
                'description' => 'Quản lý tồn kho, duyệt phiếu nhập xuất, điều phối xe, bốc hàng và kiểm kê kho',
                'permissions' => [
                    'inventory.view', 'inventory.update_price', 'inventory.delete',
                    'inv_import.view', 'inv_import.create',
                    'inv_export.view', 'inv_export.create',
                    'inv_transfer.view', 'inv_transfer.create',
                    'inv_audit.view',
                    'delivery_dispatch.view', 'delivery_dispatch.assign', 'delivery_dispatch.dispatch',
                    'inv_picking.view', 'inv_picking.view_all_categories', 'inv_picking.pick', 'inv_picking.assign', 'inv_picking.qc',
                    'inv_qc.view', 'inv_qc.qc', 'inv_qc.approve',
                    'orders.view', 'orders.status',
                    'supplier_orders.view', 'supplier_orders.create', 'supplier_orders.approve',
                    'task_assignment.view', 'task_assignment.view_all', 'task_assignment.assign',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Warehouse_Staff',
                'name' => 'Nhân Viên Kho Bốc Xếp',
                'description' => 'Thực hiện soạn hàng, bốc xếp hàng hóa vật lý và tham gia kiểm đếm kho',
                'permissions' => [
                    'inv_picking.view', 'inv_picking.pick',
                    'inventory.view',
                    'inv_import.view',
                    'inv_export.view',
                    'inv_audit.view',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Driver',
                'name' => 'Lái Xe / Vận Tải',
                'description' => 'Giao hàng đại lý, thu COD tiền mặt, quyết toán công nợ chuyến xe',
                'permissions' => [
                    'delivery_dispatch.view',
                    'driver_debt.view',
                    'fleet.view',
                    'orders.view',
                    'task_assignment.view',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Finance_Accountant',
                'name' => 'Kế Toán Tài Chính / Thủ Quỹ',
                'description' => 'Quản lý sổ quỹ tiền mặt, tài khoản ngân hàng, dòng tiền và bảng lương',
                'permissions' => [
                    'funds.view',
                    'fleet.view', 'fleet.manage',
                    'driver_debt.view',
                    'debt.view', 'debt.approve', 'staff_debt.view_all',
                    'payroll.view',
                    'orders.view',
                    'proposals.view', 'proposals.create',
                    'task_assignment.view',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Technician',
                'name' => 'Kỹ Thuật Viên',
                'description' => 'Trực tiếp đi lắp đặt máy lọc nước, tiếp nhận sửa chữa, thay thế linh kiện phụ tùng và báo cáo',
                'permissions' => [
                    'water_filter.view',
                    'water_filter.install_view',
                    'water_filter.install_assign',
                    'water_filter.install_delete',
                    'water_filter.repair_view',
                    'water_filter.repair_assign',
                    'water_filter.repair_delete',
                    'water_filter.parts_view',
                    'water_filter.parts_delete',
                    'water_filter.models_manage',
                    'task_assignment.view',
                    'task_assignment.tab_installations',
                    'task_assignment.tab_maintenance',
                    'task_assignment.tab_tasks',
                    'task_assignment.tab_warehouse_export',
                    'proposals.view', 'proposals.create',
                    'attendance.checkin',
                ],
            ],
        ];

        foreach ($rolesData as $data) {
            $role = Role::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );

            if (in_array('ALL', $data['permissions'])) {
                $permissionIds = Permission::pluck('id')->toArray();
            } else {
                $permissionIds = Permission::whereIn('slug', $data['permissions'])->pluck('id')->toArray();
            }

            $role->permissions()->sync($permissionIds);
        }
    }
}
