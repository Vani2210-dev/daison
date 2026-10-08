<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $perm = Permission::firstOrCreate(
            ['slug' => 'inv_picking.view_all_categories'],
            [
                'name' => 'Xem bốc xếp không phân loại sản phẩm',
                'module' => 'inv_picking',
                'category' => 'Soạn hàng',
                'action' => 'view_all_categories',
                'description' => 'Xem bốc xếp tất cả các loại sản phẩm, không bị giới hạn theo nhóm nhân sự',
            ]
        );

        // Gán quyền này cho các vai trò quản trị và quản lý kho
        $targetRoles = ['Warehouse_Manager', 'Manager', 'Admin', 'admin'];
        $roles = Role::whereIn('slug', $targetRoles)->get();
        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $perm = Permission::where('slug', 'inv_picking.view_all_categories')->first();
        if ($perm) {
            DB::table('role_permissions')->where('permission_id', $perm->id)->delete();
            DB::table('user_role_permissions')->where('permission_id', $perm->id)->delete();
            $perm->delete();
        }
    }
};
