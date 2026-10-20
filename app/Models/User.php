<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'code',
        'phone',
        'dob',
        'citizen_id',
        'education',
        'department',
        'status',
        'base_salary',
        'annual_leave_days',
        'remaining_leave_days',
        'end_date',
        'branch',
        'branch_id',
        'bank_account',
        'bank_name',
        'address',
        'family_members',
        'avatar',
        'affiliate_code',
        'staff_type',
        'dealer_id',
        'credit_limit',
        'current_debt',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'base_salary' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'current_debt' => 'decimal:2',
            'annual_leave_days' => 'integer',
            'remaining_leave_days' => 'decimal:1',
            'end_date' => 'date',
            'dob' => 'date',
            'family_members' => 'array',
        ];
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealer_id');
    }

    public function debtHandoversSent()
    {
        return $this->hasMany(DebtHandover::class, 'from_user_id');
    }

    public function debtHandoversReceived()
    {
        return $this->hasMany(DebtHandover::class, 'to_user_id');
    }

    public function vanSaleDispatches()
    {
        return $this->hasMany(VanSaleDispatch::class, 'driver_id');
    }

    /**
     * Danh sách các vai trò gán cho User (1 user có nhiều vai trò)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    /**
     * Danh sách quyền cụ thể được chọn cho User theo từng vai trò
     */
    public function userRolePermissions()
    {
        return $this->hasMany(UserRolePermission::class);
    }

    public function systemNotifications()
    {
        return $this->hasMany(SystemNotification::class, 'user_id');
    }

    /**
     * Lấy toàn bộ danh sách quyền hợp nhất mà User đang được kích hoạt
     */
    public function allPermissions()
    {
        return Permission::whereIn('id', function ($query) {
            $query->select('permission_id')
                ->from('user_role_permissions')
                ->where('user_id', $this->id);
        })->get();
    }

    /**
     * Kiểm tra User có vai trò cụ thể không
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Kiểm tra User có một quyền cụ thể không (theo slug: orders.view, orders.create...)
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($permissionSlug === 'all' || $permissionSlug === 'ALL') {
            return true;
        }

        // Manager / Admin / Ban Giám Đốc có toàn bộ quyền
        if ($this->hasRole('Manager') || $this->hasRole('Admin') || (isset($this->department) && in_array($this->department, ['Ban Giám Đốc', 'Quản Lý']))) {
            return true;
        }

        // 1. Nếu người dùng có cấu hình phân quyền riêng trong user_role_permissions
        $hasCustom = UserRolePermission::where('user_id', $this->id)->exists();
        if ($hasCustom) {
            return UserRolePermission::where('user_id', $this->id)
                ->whereHas('permission', function ($q) use ($permissionSlug) {
                    $q->where('slug', $permissionSlug);
                })
                ->exists();
        }

        // 2. Mặc định lấy theo các quyền của vai trò (Role)
        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permissionSlug) {
                $q->where('slug', $permissionSlug);
            })
            ->exists();
    }

    /**
     * Kiểm tra User có ít nhất một trong các quyền được truyền vào không
     * Hỗ trợ: hasAnyPermission('orders.view', 'orders.create') hoặc hasAnyPermission(['orders.view', 'orders.create'])
     */
    public function hasAnyPermission(...$permissions): bool
    {
        // Manager / Admin luôn có toàn quyền
        if ($this->hasRole('Manager') || $this->hasRole('Admin') || (isset($this->department) && in_array($this->department, ['Ban Giám Đốc', 'Quản Lý']))) {
            return true;
        }

        $flatPermissions = [];
        foreach ($permissions as $p) {
            if (is_array($p)) {
                $flatPermissions = array_merge($flatPermissions, $p);
            } elseif (is_string($p) && str_contains($p, ',')) {
                $flatPermissions = array_merge($flatPermissions, array_map('trim', explode(',', $p)));
            } else {
                $flatPermissions[] = trim((string)$p);
            }
        }

        foreach ($flatPermissions as $slug) {
            if ($slug === '' || $slug === 'all' || $this->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lấy danh sách toàn bộ slug quyền hiệu lực của người dùng
     */
    public function getEffectivePermissions(): array
    {
        if ($this->hasRole('Manager')) {
            return ['all'];
        }

        $hasCustom = UserRolePermission::where('user_id', $this->id)->exists();
        if ($hasCustom) {
            return UserRolePermission::where('user_id', $this->id)
                ->with('permission')
                ->get()
                ->pluck('permission.slug')
                ->filter()
                ->values()
                ->toArray();
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn ($r) => $r->permissions->pluck('slug'))
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Kiểm tra User có quyền thực hiện hành động trên module không
     */
    public function canDo(string $module, string $action): bool
    {
        return $this->hasPermission("{$module}.{$action}");
    }

    /**
     * Kiểm tra người dùng có quyền Kế toán / Duyệt đơn ôm hay không
     */
    public function isAccountant(): bool
    {
        if ($this->id === 1) {
            return true;
        }

        if ($this->hasRole('Manager') || $this->hasRole('Admin') || $this->hasRole('Sales_Accountant') || $this->hasRole('Finance_Accountant') || $this->hasRole('Accountant')) {
            return true;
        }

        if ($this->hasPermission('consignments.approve') || $this->hasPermission('orders.approve')) {
            return true;
        }

        if (in_array($this->department, ['Ban Giám Đốc', 'Quản Lý', 'Kế toán', 'Accounting', 'Executive'])) {
            return true;
        }

        return false;
    }

    /**
     * Gán vai trò cho User kèm danh sách quyền được chọn
     * Nếu $permissionIds === null, hệ thống sẽ lấy mặc định toàn bộ quyền của vai trò đó
     */
    public function assignRoleWithPermissions($role, array $permissionIds = null): self
    {
        $roleModel = $role instanceof Role ? $role : Role::where('slug', $role)->orWhere('id', $role)->firstOrFail();

        // 1. Gán vai trò vào user_roles
        $this->roles()->syncWithoutDetaching([$roleModel->id]);

        // 2. Xác định danh sách quyền cần cấp
        if ($permissionIds === null) {
            $permissionIds = $roleModel->permissions()->pluck('permissions.id')->toArray();
        }

        // 3. Cập nhật bảng user_role_permissions
        $this->syncRolePermissions($roleModel->id, $permissionIds);

        return $this;
    }

    /**
     * Đồng bộ danh sách quyền được chọn cho một vai trò của User
     */
    public function syncRolePermissions(int $roleId, array $permissionIds): self
    {
        // Xóa các quyền cũ của role này đối với user
        UserRolePermission::where('user_id', $this->id)
            ->where('role_id', $roleId)
            ->delete();

        // Tạo mới danh sách quyền được chọn
        $records = [];
        $now = now();
        foreach (array_unique($permissionIds) as $permId) {
            $records[] = [
                'user_id' => $this->id,
                'role_id' => $roleId,
                'permission_id' => $permId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            UserRolePermission::insert($records);
        }

        return $this;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Các nhóm nhân sự mà user này làm Trưởng nhóm (user_id)
     */
    public function leadingGroups()
    {
        return $this->hasMany(StaffGroup::class, 'user_id');
    }

    /**
     * Tất cả nhóm nhân sự mà user này là thành viên (user_ids)
     */
    public function getStaffGroupsAttribute()
    {
        return StaffGroup::whereJsonContains('user_ids', (int)$this->id)->get();
    }
}
