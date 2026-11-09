<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tabPerms = [
            [
                'slug' => 'task_assignment.tab_installations',
                'name' => 'Xem Tab Đơn Lắp Đặt',
                'module' => 'task_assignment',
                'category' => 'Công việc',
                'action' => 'view_installations',
                'description' => 'Cho phép xem và thao tác tab Đơn Hàng Lắp Đặt Máy Lọc Nước trong trang Công việc.',
                'roles' => ['Admin', 'Executive', 'Manager', 'Technician'],
            ],
            [
                'slug' => 'task_assignment.tab_maintenance',
                'name' => 'Xem Tab Đơn Bảo Dưỡng',
                'module' => 'task_assignment',
                'category' => 'Công việc',
                'action' => 'view_maintenance',
                'description' => 'Cho phép xem và thao tác tab Đơn Bảo Dưỡng & Thay Lõi trong trang Công việc.',
                'roles' => ['Admin', 'Executive', 'Manager', 'Technician'],
            ],
            [
                'slug' => 'task_assignment.tab_tasks',
                'name' => 'Xem Tab Nhiệm Vụ Nội Bộ',
                'module' => 'task_assignment',
                'category' => 'Công việc',
                'action' => 'view_tasks',
                'description' => 'Cho phép xem và thao tác tab Nhiệm Vụ & Phân Công Nội Bộ trong trang Công việc.',
                'roles' => ['Admin', 'Executive', 'Manager', 'Technician', 'Sales_Rep', 'Sales_Manager', 'Warehouse_Manager', 'Warehouse_Staff', 'Driver', 'Finance_Accountant'],
            ],
            [
                'slug' => 'task_assignment.tab_warehouse_export',
                'name' => 'Xem Tab Lấy Hàng Từ Kho',
                'module' => 'task_assignment',
                'category' => 'Công việc',
                'action' => 'view_warehouse_export',
                'description' => 'Cho phép xem và thao tác tab Lấy Hàng Từ Kho Đi Làm trong trang Công việc.',
                'roles' => ['Admin', 'Executive', 'Manager', 'Technician', 'Warehouse_Manager'],
            ],
        ];

        foreach ($tabPerms as $item) {
            $roles = $item['roles'];
            unset($item['roles']);

            $perm = Permission::firstOrCreate(['slug' => $item['slug']], $item);

            $matchedRoles = Role::whereIn('slug', $roles)->get();
            foreach ($matchedRoles as $role) {
                if (!$role->permissions->contains('id', $perm->id)) {
                    $role->permissions()->attach($perm->id);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $slugs = [
            'task_assignment.tab_installations',
            'task_assignment.tab_maintenance',
            'task_assignment.tab_tasks',
            'task_assignment.tab_warehouse_export',
        ];

        $perms = Permission::whereIn('slug', $slugs)->get();
        foreach ($perms as $perm) {
            $perm->roles()->detach();
            $perm->delete();
        }
    }
};

