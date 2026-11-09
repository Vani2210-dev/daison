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
            ['slug' => 'staff_debt.view_all'],
            [
                'name' => 'Xem tất cả công nợ nhân viên',
                'module' => 'staff_debt',
                'category' => 'Tài chính',
                'action' => 'view_all',
                'description' => 'Xem toàn bộ danh sách và sổ chi tiết công nợ của tất cả nhân sự. Nếu không có quyền này, nhân viên chỉ xem được công nợ của chính bản thân.',
            ]
        );

        // Gán quyền cho vai trò Quản lý và Kế toán mặc định
        $rolesToAssign = Role::whereIn('slug', ['Manager', 'Sales_Accountant', 'Finance_Accountant'])->get();
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
        $perm = Permission::where('slug', 'staff_debt.view_all')->first();
        if ($perm) {
            $perm->roles()->detach();
            $perm->delete();
        }
    }
};

