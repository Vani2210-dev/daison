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
        $perms = [
            [
                'slug' => 'water_filter.repair_assign',
                'name' => 'Phân công kỹ thuật viên sửa & bảo dưỡng',
                'module' => 'water_filter_repair',
                'category' => 'Máy lọc nước',
                'action' => 'assign',
                'description' => 'Chỉ định, phân công hoặc điều phối kỹ thuật viên phụ trách sửa chữa & bảo dưỡng máy lọc',
                'roles' => ['Manager', 'Admin'],
            ],
            [
                'slug' => 'water_filter.install_assign',
                'name' => 'Phân công KTV lắp đặt máy',
                'module' => 'water_filter_install',
                'category' => 'Máy lọc nước',
                'action' => 'assign',
                'description' => 'Chỉ định, phân công hoặc điều phối kỹ thuật viên đi giao hàng & lắp đặt máy lọc',
                'roles' => ['Manager', 'Admin'],
            ],
        ];

        foreach ($perms as $item) {
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
        Permission::whereIn('slug', [
            'water_filter.repair_assign',
            'water_filter.install_assign',
        ])->delete();
    }
};

