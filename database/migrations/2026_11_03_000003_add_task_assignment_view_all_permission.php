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
        $perm = Permission::firstOrCreate(
            ['slug' => 'task_assignment.view_all'],
            [
                'name' => 'Xem tất cả công việc (Toàn bộ nhân sự)',
                'module' => 'task_assignment',
                'category' => 'Công việc',
                'action' => 'view_all',
                'description' => 'Cho phép xem toàn bộ danh sách công việc, đơn lắp đặt, bảo dưỡng của tất cả nhân sự. Nếu không có quyền này, người dùng chỉ xem được công việc được phân công cho chính bản thân.',
            ]
        );

        // Gán quyền cho vai trò Quản lý, Giám đốc và Admin mặc định
        $rolesToAssign = Role::whereIn('slug', ['Manager', 'Executive', 'Admin'])->get();
        foreach ($rolesToAssign as $role) {
            if (!$role->permissions->contains('id', $perm->id)) {
                $role->permissions()->attach($perm->id);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $perm = Permission::where('slug', 'task_assignment.view_all')->first();
        if ($perm) {
            $perm->roles()->detach();
            $perm->delete();
        }
    }
};

