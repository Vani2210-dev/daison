<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AffiliateReferral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Bootstrap data for SPA initial load
     */
    public function getBootstrapData()
    {
        return User::with(['roles.permissions', 'userRolePermissions.permission'])->get()->map(function ($u) {
            $roleSlug = $u->roles->first()?->slug ?? null;
            $affiliateRecords = AffiliateReferral::where('user_id', $u->id)
                ->orWhere('affiliate_code', $u->affiliate_code)
                ->get();

            $customPerms = $u->userRolePermissions->map(fn($urp) => $urp->permission?->slug)->filter()->values()->all();
            $hasCustom = $u->userRolePermissions->count() > 0;

            return [
                'id' => (string) $u->id,
                'code' => $u->code ?? "NV-{$u->id}",
                'name' => $u->name,
                'role' => $roleSlug,
                'department' => $u->department ?? null,
                'avatar' => $u->avatar ?? "https://ui-avatars.com/api/?name=" . urlencode($u->name) . "&background=0D8ABC&color=fff",
                'phone' => $u->phone ?? '',
                'dob' => $u->dob ? (is_string($u->dob) ? substr($u->dob, 0, 10) : $u->dob->format('Y-m-d')) : null,
                'citizenId' => $u->citizen_id ?? '',
                'education' => $u->education ?? '',
                'email' => $u->email,
                'joinDate' => $u->created_at ? $u->created_at->format('Y-m-d') : null,
                'status' => $u->status ?? 'Đang làm việc',
                'baseSalary' => (float) ($u->base_salary ?? 0),
                'branch' => $u->branch ?? null,
                'branchId' => $u->branch_id ? (string) $u->branch_id : '',
                'bankAccount' => $u->bank_account ?? '',
                'bankName' => $u->bank_name ?? '',
                'address' => $u->address ?? '',
                'familyMembers' => $u->family_members ?? [],
                'annualLeaveDays' => (int) ($u->annual_leave_days ?? 12),
                'remainingLeaveDays' => (float) ($u->remaining_leave_days ?? 12),
                'endDate' => $u->end_date ? (is_string($u->end_date) ? substr($u->end_date, 0, 10) : $u->end_date->format('Y-m-d')) : null,
                'affiliateCode' => $u->affiliate_code ?? "AFF-{$u->id}",
                'totalAffiliateReferrals' => $affiliateRecords->count(),
                'totalAffiliateKpiPoints' => (float) $affiliateRecords->sum('kpi_points'),
                'totalAffiliateBonusVND' => (float) $affiliateRecords->sum('bonus_vnd'),
                'customPermissions' => $customPerms,
                'hasCustomPermissions' => $hasCustom,
            ];
        });
    }

    /**
     * Bootstrap data for Affiliate Referrals in SPA
     */
    public function getAffiliateBootstrapData()
    {
        return AffiliateReferral::latest('id')->get()->map(function ($aff) {
            return [
                'id'                    => (string) $aff->id,
                'userId'                => (string) ($aff->user_id ?? ''),
                'affiliateCode'         => $aff->affiliate_code,
                'referrerName'          => $aff->referrer_name,
                'referredCustomerName'  => $aff->referred_customer_name,
                'referredCustomerPhone' => $aff->referred_customer_phone ?? '',
                'orderCount'            => (int) $aff->order_count,
                'totalRevenue'          => (float) $aff->total_revenue,
                'kpiPointsEarned'       => (float) $aff->kpi_points,
                'bonusVnd'              => (float) $aff->bonus_vnd,
                'status'                => $aff->status,
                'date'                  => $aff->date ? $aff->date->format('Y-m-d') : date('Y-m-d'),
            ];
        });
    }

    /**
     * API: Get affiliate referrals
     */
    public function getAffiliates()
    {
        return response()->json(['affiliateReferrals' => $this->getAffiliateBootstrapData()]);
    }

    /**
     * API: Get all users / staff members
     */
    public function getUsers()
    {
        return response()->json(['users' => $this->getBootstrapData()]);
    }

    /**
     * API: Create or Update User
     */
    public function saveUser(Request $request)
    {
        $id = $request->input('id');
        $roleSlug = $request->input('role', 'Sales_Rep');

        $avatar = $request->input('avatar');
        if ($avatar && str_starts_with($avatar, 'data:image/')) {
            try {
                if (preg_match('/^data:image\/(\w+);base64,/', $avatar, $typeMatch)) {
                    $ext = strtolower($typeMatch[1]);
                    if (in_array($ext, ['jpeg', 'jpg', 'png', 'webp', 'gif'])) {
                        $binary = base64_decode(substr($avatar, strpos($avatar, ',') + 1));
                        if ($binary !== false) {
                            $dir = public_path('uploads/avatars');
                            if (!is_dir($dir)) {
                                @mkdir($dir, 0755, true);
                            }
                            $filename = 'avatar_' . ($id ?: time()) . '_' . uniqid() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                            file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $binary);
                            $avatar = '/uploads/avatars/' . $filename;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Keep avatar as is
            }
        }

        $branchId = $request->input('branchId') ?: $request->input('branch_id');
        $branchName = $request->input('branch');
        if ($branchId && is_numeric($branchId)) {
            $branchObj = \App\Models\Branch::find($branchId);
            if ($branchObj) {
                $branchName = $branchObj->name;
            }
        } elseif ($branchName) {
            $foundBr = \App\Models\Branch::where('name', $branchName)->orWhere('code', $branchName)->first();
            if ($foundBr) {
                $branchId = $foundBr->id;
            }
        }

        $data = [
            'name'                 => $request->input('name'),
            'email'                => $request->input('email'),
            'code'                 => $request->input('code'),
            'phone'                => $request->input('phone'),
            'dob'                  => $request->input('dob') ?: null,
            'citizen_id'           => $request->input('citizenId') ?: $request->input('citizen_id'),
            'education'            => $request->input('education'),
            'department'           => $request->input('department', 'Sales'),
            'status'               => $request->input('status', 'Đang làm việc'),
            'base_salary'          => (float) $request->input('baseSalary', 0),
            'annual_leave_days'    => (int) $request->input('annualLeaveDays', 12),
            'remaining_leave_days' => (float) $request->input('remainingLeaveDays', 12),
            'end_date'             => $request->input('endDate') ?: null,
            'branch'               => $branchName ?: 'Trụ sở Chính - Vinh',
            'branch_id'            => $branchId ?: null,
            'bank_account'         => $request->input('bankAccount'),
            'bank_name'            => $request->input('bankName'),
            'address'              => $request->input('address'),
            'family_members'       => $request->input('familyMembers', []),
            'avatar'               => $avatar,
        ];

        try {
            $user = null;
            if ($id && is_numeric($id)) {
                $user = User::find($id);
            }
            if (!$user && !empty($data['email'])) {
                $user = User::where('email', $data['email'])->first();
            }
            if (!$user && !empty($data['code'])) {
                $user = User::where('code', $data['code'])->first();
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make(trim($request->input('password')));
            } else {
                unset($data['password']);
            }

            if ($user) {
                $user->update($data);
            } else {
                if (empty($data['password'])) {
                    $data['password'] = Hash::make('123456'); // default password
                }
                $user = User::create($data);
            }

            if ($roleSlug) {
                $role = Role::where('slug', $roleSlug)->first();
                if ($role) {
                    $user->roles()->sync([$role->id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $id ? 'Cập nhật nhân sự thành công' : 'Thêm nhân sự mới thành công',
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $roleSlug,
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('saveUser Error: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->except(['password']),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lưu thông tin nhân sự: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Delete / Deactivate User
     */
    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy nhân viên'], 404);
        }

        $user->update(['status' => 'Đã nghỉ việc']);
        return response()->json(['success' => true, 'message' => 'Đã cập nhật trạng thái nhân sự thành Đã nghỉ việc']);
    }
}
