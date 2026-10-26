<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\CompanyBankAccount;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    /**
     * Danh sách các ngân hàng phổ biến tại Việt Nam kèm mã BIN Napas
     */
    public static function getSupportedBanks(): array
    {
        return [
            ['code' => 'MB',       'bin' => '970422', 'short_name' => 'MBBank',     'name' => 'Ngân hàng TMCP Quân Đội'],
            ['code' => 'VCB',      'bin' => '970436', 'short_name' => 'Vietcombank', 'name' => 'Ngân hàng TMCP Ngoại Thương Việt Nam'],
            ['code' => 'TCB',      'bin' => '970407', 'short_name' => 'Techcombank', 'name' => 'Ngân hàng TMCP Kỹ Thương Việt Nam'],
            ['code' => 'BIDV',     'bin' => '970418', 'short_name' => 'BIDV',        'name' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam'],
            ['code' => 'ICB',      'bin' => '970415', 'short_name' => 'VietinBank',  'name' => 'Ngân hàng TMCP Công Thương Việt Nam'],
            ['code' => 'ACB',      'bin' => '970416', 'short_name' => 'ACB',         'name' => 'Ngân hàng TMCP Á Châu'],
            ['code' => 'VPB',      'bin' => '970432', 'short_name' => 'VPBank',      'name' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng'],
            ['code' => 'TPB',      'bin' => '970423', 'short_name' => 'TPBank',      'name' => 'Ngân hàng TMCP Tiên Phong'],
            ['code' => 'STB',      'bin' => '970403', 'short_name' => 'Sacombank',  'name' => 'Ngân hàng TMCP Sài Gòn Thương Tín'],
            ['code' => 'VIB',      'bin' => '970441', 'short_name' => 'VIB',         'name' => 'Ngân hàng TMCP Quốc tế Việt Nam'],
            ['code' => 'HDB',      'bin' => '970437', 'short_name' => 'HDBank',      'name' => 'Ngân hàng TMCP Phát triển TP.HCM'],
            ['code' => 'SHB',      'bin' => '970443', 'short_name' => 'SHB',         'name' => 'Ngân hàng TMCP Sài Gòn - Hà Nội'],
            ['code' => 'MSB',      'bin' => '970426', 'short_name' => 'MSB',         'name' => 'Ngân hàng TMCP Hàng Hải Việt Nam'],
            ['code' => 'OCB',      'bin' => '970448', 'short_name' => 'OCB',         'name' => 'Ngân hàng TMCP Phương Đông'],
            ['code' => 'LPB',      'bin' => '970449', 'short_name' => 'LPBank',      'name' => 'Ngân hàng TMCP Lộc Phát Việt Nam'],
            ['code' => 'SEAB',     'bin' => '970440', 'short_name' => 'SeABank',     'name' => 'Ngân hàng TMCP Đông Nam Á'],
            ['code' => 'ABB',      'bin' => '970425', 'short_name' => 'ABBank',      'name' => 'Ngân hàng TMCP An Bình'],
            ['code' => 'BAB',      'bin' => '970409', 'short_name' => 'BacABank',    'name' => 'Ngân hàng TMCP Bắc Á'],
            ['code' => 'VAB',      'bin' => '970427', 'short_name' => 'VietABank',   'name' => 'Ngân hàng TMCP Việt Á'],
            ['code' => 'BVB',      'bin' => '970454', 'short_name' => 'BVBank',      'name' => 'Ngân hàng TMCP Bản Việt'],
            ['code' => 'EIB',      'bin' => '970431', 'short_name' => 'Eximbank',    'name' => 'Ngân hàng TMCP Xuất Nhập Khẩu Việt Nam'],
        ];
    }

    /**
     * Màn hình chính Cấu hình Hệ thống
     */
    public function index(Request $request)
    {
        $company = CompanySetting::getSettings();
        $bankAccounts = CompanyBankAccount::with('branchRef')->orderByDesc('is_default')->orderBy('id')->get();
        $supportedBanks = self::getSupportedBanks();
        $branches = Branch::where('is_active', true)->orWhereNull('is_active')->orderBy('name')->get(['id', 'code', 'name', 'address']);

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'company'        => $company,
                'bankAccounts'   => $bankAccounts,
                'supportedBanks' => $supportedBanks,
                'branches'       => $branches,
            ]);
        }

        return view('settings.index', [
            'company'        => $company,
            'bankAccounts'   => $bankAccounts,
            'supportedBanks' => $supportedBanks,
            'branches'       => $branches,
            'currentTab'     => $request->query('tab', 'company'),
        ]);
    }

    /**
     * Cập nhật thông tin công ty
     */
    public function updateCompany(Request $request)
    {
        $request->validate([
            'company_name'       => 'required|string|max:255',
            'company_legal_name' => 'nullable|string|max:255',
            'tax_code'           => 'nullable|string|max:50',
            'phone'              => 'nullable|string|max:50',
            'hotline'            => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:100',
            'address'            => 'nullable|string|max:500',
            'warehouse_address'  => 'nullable|string|max:500',
            'website'            => 'nullable|string|max:255',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        try {
            $company = CompanySetting::getSettings();
            $data = $request->only([
                'company_name',
                'company_legal_name',
                'tax_code',
                'phone',
                'hotline',
                'email',
                'address',
                'warehouse_address',
                'website',
                'representative_name',
                'representative_title',
                'invoice_footer_note',
                'order_terms',
            ]);

            // Xử lý upload logo mới
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                $data['logo_url'] = '/images/' . $filename;
            }

            $company->update($data);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật thông tin công ty thành công!',
                    'company' => $company->fresh(),
                ]);
            }

            return redirect()->back()->with('success', 'Cập nhật thông tin công ty thành công!');
        } catch (\Throwable $e) {
            Log::error("Update company settings error: " . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }

    /**
     * Thêm tài khoản ngân hàng mới
     */
    public function storeBankAccount(Request $request)
    {
        $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:150',
            'bank_code'      => 'nullable|string|max:50',
            'branch_id'      => 'nullable|integer',
            'branch'         => 'nullable|string|max:150',
        ]);

        try {
            $isDefault = (bool)$request->is_default;
            if ($isDefault) {
                CompanyBankAccount::query()->update(['is_default' => false]);
            } else {
                // Nếu đây là tài khoản đầu tiên thì tự động đặt làm mặc định
                if (CompanyBankAccount::count() === 0) {
                    $isDefault = true;
                }
            }

            $branchId = $request->branch_id ? (int)$request->branch_id : null;
            $branchName = trim($request->branch ?: '');
            if ($branchId && empty($branchName)) {
                $b = Branch::find($branchId);
                if ($b) $branchName = $b->name;
            }

            $account = CompanyBankAccount::create([
                'bank_name'      => trim($request->bank_name),
                'bank_code'      => strtoupper(trim($request->bank_code ?: '')),
                'account_number' => trim($request->account_number),
                'account_holder' => mb_strtoupper(trim($request->account_holder)),
                'branch_id'      => $branchId,
                'branch'         => $branchName,
                'is_default'     => $isDefault,
                'is_active'      => $request->has('is_active') ? (bool)$request->is_active : true,
                'qr_template'    => $request->qr_template ?: 'compact2',
                'notes'          => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Đã thêm tài khoản ngân hàng {$account->bank_name} - {$account->account_number}!",
                'account' => $account->load('branchRef'),
            ]);
        } catch (\Throwable $e) {
            Log::error("Store bank account error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi lưu tài khoản: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật thông tin tài khoản ngân hàng
     */
    public function updateBankAccount(Request $request, $id)
    {
        $account = CompanyBankAccount::findOrFail($id);

        $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:150',
            'branch_id'      => 'nullable|integer',
            'branch'         => 'nullable|string|max:150',
        ]);

        try {
            $isDefault = (bool)$request->is_default;
            if ($isDefault && !$account->is_default) {
                CompanyBankAccount::where('id', '!=', $id)->update(['is_default' => false]);
            }

            $branchId = $request->has('branch_id') ? ($request->branch_id ? (int)$request->branch_id : null) : $account->branch_id;
            $branchName = trim($request->branch ?: '');
            if ($branchId && empty($branchName)) {
                $b = Branch::find($branchId);
                if ($b) $branchName = $b->name;
            }
            if (empty($branchName) && !$request->has('branch')) {
                $branchName = $account->branch;
            }

            $account->update([
                'bank_name'      => trim($request->bank_name),
                'bank_code'      => strtoupper(trim($request->bank_code ?: $account->bank_code)),
                'account_number' => trim($request->account_number),
                'account_holder' => mb_strtoupper(trim($request->account_holder)),
                'branch_id'      => $branchId,
                'branch'         => $branchName,
                'is_default'     => $isDefault,
                'is_active'      => $request->has('is_active') ? (bool)$request->is_active : $account->is_active,
                'qr_template'    => $request->qr_template ?: ($account->qr_template ?: 'compact2'),
                'notes'          => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tài khoản ngân hàng thành công!',
                'account' => $account->fresh()->load('branchRef'),
            ]);
        } catch (\Throwable $e) {
            Log::error("Update bank account error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xóa tài khoản ngân hàng
     */
    public function deleteBankAccount($id)
    {
        try {
            $account = CompanyBankAccount::findOrFail($id);
            $wasDefault = $account->is_default;
            $account->delete();

            // Nếu tài khoản bị xóa là mặc định, chọn tài khoản đầu tiên còn lại làm mặc định
            if ($wasDefault) {
                $next = CompanyBankAccount::first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tài khoản ngân hàng thành công!',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xóa tài khoản: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Đặt làm tài khoản ngân hàng mặc định
     */
    public function setDefaultBankAccount($id)
    {
        try {
            DB::transaction(function () use ($id) {
                CompanyBankAccount::query()->update(['is_default' => false]);
                $account = CompanyBankAccount::findOrFail($id);
                $account->update(['is_default' => true, 'is_active' => true]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Đã đặt làm tài khoản ngân hàng nhận thanh toán mặc định!',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bật/tắt trạng thái hoạt động tài khoản
     */
    public function toggleActiveBankAccount($id)
    {
        try {
            $account = CompanyBankAccount::findOrFail($id);
            $account->is_active = !$account->is_active;
            // Nếu tài khoản mặc định bị tắt, bỏ mặc định
            if (!$account->is_active && $account->is_default) {
                $account->is_default = false;
            }
            $account->save();

            return response()->json([
                'success'   => true,
                'is_active' => $account->is_active,
                'message'   => $account->is_active ? 'Đã kích hoạt tài khoản' : 'Đã tạm ngưng tài khoản',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
