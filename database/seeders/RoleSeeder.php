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
                    'orders.view', 'orders.create', 'orders.edit', 'orders.approve', 'orders.cancel', 'orders.status', 'orders.export',
                    'quotation.view', 'quotation.create', 'quotation.edit', 'quotation.convert', 'quotation.export',
                    'returns.view', 'returns.create', 'returns.approve', 'returns.cancel',
                    'dealers.view', 'dealers.create', 'dealers.edit', 'dealers.export',
                    'debt.view', 'debt.set_limit', 'debt.adjust', 'debt.export',
                    'sales_policy.view', 'sales_policy.create', 'sales_policy.edit', 'sales_policy.delete',
                    'funds.view', 'funds.create_receipt', 'funds.export',
                    'inventory.view', 'inventory.export',
                    'task_assignment.view', 'task_assignment.create', 'task_assignment.report', 'task_assignment.evaluate',
                    'proposals.view', 'proposals.create',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Sales_Rep',
                'name' => 'Nhân Viên Kinh Doanh / Thị Trường',
                'description' => 'Mở rộng đại lý, check-in GPS thực địa, lên đơn hàng và báo cáo công việc',
                'permissions' => [
                    'orders.view', 'orders.create', 'orders.export',
                    'quotation.view', 'quotation.create', 'quotation.edit', 'quotation.convert', 'quotation.export',
                    'dealers.view', 'dealers.create', 'dealers.edit',
                    'dealer_care.view', 'dealer_care.checkin', 'dealer_care.feedback',
                    'sales_policy.view',
                    'inventory.view',
                    'task_assignment.view', 'task_assignment.report',
                    'proposals.view', 'proposals.create',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Warehouse_Manager',
                'name' => 'Quản Lý Kho Tổng',
                'description' => 'Quản lý tồn kho, duyệt phiếu nhập xuất, điều phối xe, bốc hàng và kiểm kê kho',
                'permissions' => [
                    'inventory.view', 'inventory.update_price', 'inventory.export',
                    'inv_import.view', 'inv_import.create', 'inv_import.approve', 'inv_import.export',
                    'inv_export.view', 'inv_export.create', 'inv_export.approve', 'inv_export.export',
                    'inv_transfer.view', 'inv_transfer.create', 'inv_transfer.approve', 'inv_transfer.export',
                    'inv_audit.view', 'inv_audit.create', 'inv_audit.count', 'inv_audit.balance', 'inv_audit.approve',
                    'delivery_dispatch.view', 'delivery_dispatch.assign', 'delivery_dispatch.dispatch', 'delivery_dispatch.settle',
                    'inv_picking.view', 'inv_picking.view_all_categories', 'inv_picking.pick', 'inv_picking.assign', 'inv_picking.qc', 'inv_picking.approve', 'inv_picking.export',
                    'inv_qc.view', 'inv_qc.qc', 'inv_qc.approve', 'inv_qc.export',
                    'orders.view', 'orders.status', 'orders.export',
                    'supplier_orders.view', 'supplier_orders.receive',
                    'task_assignment.view', 'task_assignment.create', 'task_assignment.report',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Warehouse_Staff',
                'name' => 'Nhân Viên Kho Bốc Xếp',
                'description' => 'Thực hiện soạn hàng, bốc xếp hàng hóa vật lý và tham gia kiểm đếm kho',
                'permissions' => [
                    'inv_picking.view', 'inv_picking.pick', 'inv_picking.export',
                    'inventory.view',
                    'inv_import.view',
                    'inv_export.view',
                    'inv_audit.view', 'inv_audit.count',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Driver',
                'name' => 'Lái Xe / Vận Tải',
                'description' => 'Giao hàng đại lý, thu COD tiền mặt, quyết toán công nợ chuyến xe',
                'permissions' => [
                    'delivery_dispatch.view', 'delivery_dispatch.settle',
                    'driver_debt.view', 'driver_debt.settle',
                    'fleet.view',
                    'orders.view',
                    'task_assignment.view', 'task_assignment.report',
                    'attendance.checkin',
                ],
            ],
            [
                'slug' => 'Finance_Accountant',
                'name' => 'Kế Toán Tài Chính / Thủ Quỹ',
                'description' => 'Quản lý sổ quỹ tiền mặt, tài khoản ngân hàng, dòng tiền và bảng lương',
                'permissions' => [
                    'funds.view', 'funds.create_receipt', 'funds.create_payment', 'funds.transfer', 'funds.approve', 'funds.export',
                    'fleet.view', 'fleet.settle', 'fleet.manage',
                    'driver_debt.view', 'driver_debt.settle',
                    'debt.view', 'debt.set_limit', 'debt.adjust', 'debt.export',
                    'payroll.view', 'payroll.calculate', 'payroll.lock', 'payroll.export',
                    'orders.view', 'orders.export',
                    'proposals.view', 'proposals.approve', 'proposals.reject',
                    'task_assignment.view', 'task_assignment.create', 'task_assignment.report',
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
