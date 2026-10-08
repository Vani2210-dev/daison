<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserRolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Bootstrap data for Roles in SPA
     */
    public function getBootstrapData()
    {
        return Role::with('permissions')->get()->map(function ($r) {
            return [
                'id'          => (string) $r->id,
                'name'        => $r->name,
                'slug'        => $r->slug,
                'description' => $r->description,
                'permissions' => $r->permissions->pluck('slug'),
            ];
        });
    }

    /**
     * API: Get all roles and permissions
     */
    public function getRoles()
    {
        return response()->json(['roles' => $this->getBootstrapData()]);
    }

    /**
     * API: Save Role Permissions Matrix to Database
     */
    public function saveRolePermissions(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }

        $roleSlug = $request->input('role') ?: ($raw['role'] ?? ($request->json('role') ?: null));
        $permissions = $request->input('permissions') ?: ($raw['permissions'] ?? ($request->json('permissions') ?: []));
        $allRolesData = $request->input('roles') ?: ($raw['roles'] ?? ($request->json('roles') ?: []));

        DB::beginTransaction();
        try {
            if (!empty($allRolesData) && is_array($allRolesData)) {
                foreach ($allRolesData as $slug => $slugs) {
                    $role = Role::where('slug', $slug)->first();
                    if ($role) {
                        $permissionIds = Permission::whereIn('slug', (array) $slugs)->pluck('id');
                        $role->permissions()->sync($permissionIds);
                    }
                }
            } elseif ($roleSlug) {
                $role = Role::where('slug', $roleSlug)->first();
                if (!$role) {
                    return response()->json([
                        'success' => false,
                        'message' => "Không tìm thấy vai trò: {$roleSlug}",
                    ], 404);
                }

                $permissionIds = Permission::whereIn('slug', (array) $permissions)->pluck('id');
                $role->permissions()->sync($permissionIds);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu phân quyền không hợp lệ',
                ], 422);
            }

            DB::commit();

            // Refresh roles list to return
            $updatedRoles = Role::with('permissions')->get()->map(function ($r) {
                return [
                    'id'          => (string) $r->id,
                    'name'        => $r->name,
                    'slug'        => $r->slug,
                    'permissions' => $r->permissions->pluck('slug'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu cấu hình ma trận phân quyền vào cơ sở dữ liệu thành công!',
                'roles'   => $updatedRoles,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu phân quyền: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Save customized permissions for a specific User
     * Rule: User can ONLY be granted permissions within their Role!
     */
    public function saveUserPermissions(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }

        $userId = $request->input('user_id') ?? ($raw['user_id'] ?? null);
        $permissions = $request->input('permissions') ?? ($raw['permissions'] ?? []);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã nhân sự (user_id)',
            ], 422);
        }

        $user = User::with('roles.permissions')->find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy nhân sự ID {$userId}",
            ], 404);
        }

        $role = $user->roles->first();
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => "Nhân sự {$user->name} chưa được gán vai trò",
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Delete existing custom permissions for this user & role
            UserRolePermission::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->delete();

            // Permissions that belong to the user's role:
            $rolePermissionIds = $role->permissions->pluck('id')->toArray();

            // Filter requested permission slugs so only those allowed by the role can be saved!
            if (!empty($permissions) && is_array($permissions)) {
                $allowedPermissionIds = Permission::whereIn('slug', $permissions)
                    ->whereIn('id', $rolePermissionIds)
                    ->pluck('id');

                $insertData = [];
                $now = now();
                foreach ($allowedPermissionIds as $pId) {
                    $insertData[] = [
                        'user_id'       => $user->id,
                        'role_id'       => $role->id,
                        'permission_id' => $pId,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }

                if (!empty($insertData)) {
                    UserRolePermission::insert($insertData);
                }
            }

            DB::commit();

            // Get updated list of permission slugs for this user
            $activeSlugs = UserRolePermission::where('user_id', $user->id)
                ->with('permission')
                ->get()
                ->pluck('permission.slug')
                ->filter()
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'message' => "Đã lưu phân quyền tùy biến cho nhân sự {$user->name} ({$role->name}) thành công!",
                'userId' => (string) $user->id,
                'roleSlug' => $role->slug,
                'permissions' => $activeSlugs,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu phân quyền nhân sự: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Reset user permissions back to default role permissions
     */
    public function resetUserPermissions(Request $request, $id)
    {
        $user = User::with('roles.permissions')->find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
        }

        UserRolePermission::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã khôi phục phân quyền mặc định theo vai trò cho nhân sự {$user->name}",
            'permissions' => $user->roles->first()?->permissions->pluck('slug') ?? [],
        ]);
    }
}
