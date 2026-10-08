<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Xử lý đăng nhập tài khoản người dùng
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        $account = trim($validated['email']);
        $password = $validated['password'];
        $remember = $validated['remember'] ?? false;

        $cleanPhone = preg_replace('/\s+/', '', $account);

        // Tìm kiếm theo email, mã nhân viên code hoặc số điện thoại phone
        $user = User::with(['roles', 'userRolePermissions.permission'])
            ->where('email', $account)
            ->orWhere('code', $account)
            ->orWhere('phone', $account)
            ->orWhere('phone', $cleanPhone)
            ->orWhereRaw("REPLACE(phone, ' ', '') = ?", [$cleanPhone])
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản (Email / Mã nhân viên / SĐT) hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại!',
            ], 401);
        }

        if ($user->status && $user->status !== 'Đang làm việc') {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn hiện đang ở trạng thái: ' . $user->status . '. Vui lòng liên hệ Quản trị viên.',
            ], 403);
        }

        // Đăng nhập session Laravel
        Auth::login($user, $remember);

        $roleSlug = $user->roles->first()?->slug ?? 'Sales_Rep';

        $userData = [
            'id' => (string) $user->id,
            'code' => $user->code ?? "NV-{$user->id}",
            'name' => $user->name,
            'role' => $roleSlug,
            'department' => $user->department ?? 'Sales',
            'avatar' => $user->avatar ?? ("https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=0D8ABC&color=fff"),
            'phone' => $user->phone ?? '',
            'email' => $user->email,
            'status' => $user->status ?? 'Đang làm việc',
            'branch' => $user->branch ?? 'Trụ sở Chính',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công! Chào mừng ' . $user->name . ' quay trở lại.',
            'user' => $userData,
        ]);
    }

    /**
     * Xử lý đăng xuất tài khoản
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Đã đăng xuất khỏi hệ thống thành công.',
        ]);
    }

    /**
     * Lấy thông tin tài khoản đang đăng nhập trong phiên làm việc
     */
    public function me(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'user' => null,
            ], 401);
        }

        $user = Auth::user();
        $roleSlug = $user->roles->first()?->slug ?? 'Sales_Rep';

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => (string) $user->id,
                'code' => $user->code ?? "NV-{$user->id}",
                'name' => $user->name,
                'role' => $roleSlug,
                'department' => $user->department ?? 'Sales',
                'avatar' => $user->avatar ?? ("https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=0D8ABC&color=fff"),
                'phone' => $user->phone ?? '',
                'email' => $user->email,
                'status' => $user->status ?? 'Đang làm việc',
                'branch' => $user->branch ?? 'Trụ sở Chính',
            ],
        ]);
    }

    /**
     * Gửi mã xác nhận OTP qua Email cho chức năng Quên mật khẩu
     */
    public function sendResetOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = trim(strtolower($validated['email']));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Địa chỉ email này chưa được đăng ký trong hệ thống ERP. Vui lòng kiểm tra lại!',
            ], 404);
        }

        // Sinh mã OTP ngẫu nhiên 6 chữ số
        $otp = (string) mt_rand(100000, 999999);

        // Lưu vào cache 15 phút
        Cache::put("password_reset_otp_{$email}", $otp, now()->addMinutes(15));

        // Lưu bản ghi vào bảng password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Ghi log để theo dõi kiểm thử
        Log::info("ERP Password Reset OTP for [{$email}]: {$otp}");

        // Gửi email chứa mã xác thực OTP thực tế
        $mailSent = false;
        try {
            Mail::to($email)->send(new ResetPasswordOtpMail($user, $otp));
            $mailSent = true;
            Log::info("ERP: Successfully dispatched OTP email to [{$email}]");
        } catch (\Throwable $e) {
            Log::warning("ERP: Could not send OTP email to [{$email}]: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Mã xác thực OTP (6 chữ số) đã được gửi đến email: ' . $email . '. Vui lòng kiểm tra hộp thư đến (hoặc hòm thư rác/Spam). Mã có hiệu lực trong 15 phút.',
            'email' => $email,
            'mail_sent' => $mailSent,
            'dev_otp' => $otp, // Trợ giúp kiểm thử nhanh khi chưa cấu hình máy chủ SMTP ngoài
        ]);
    }

    /**
     * Kiểm tra tính hợp lệ của mã OTP
     */
    public function verifyResetOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $email = trim(strtolower($validated['email']));
        $otp = trim($validated['otp']);

        $cachedOtp = Cache::get("password_reset_otp_{$email}");

        if ($cachedOtp && $cachedOtp === $otp) {
            return response()->json([
                'success' => true,
                'message' => 'Mã xác thực chính xác! Vui lòng nhập mật khẩu mới.',
            ]);
        }

        // Kiểm tra dự phòng qua bảng password_reset_tokens
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        if ($record && Hash::check($otp, $record->token)) {
            // Kiểm tra hạn 15 phút
            if (now()->diffInMinutes($record->created_at) <= 15) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mã xác thực chính xác! Vui lòng nhập mật khẩu mới.',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Mã OTP không chính xác hoặc đã hết thời gian hiệu lực (15 phút). Vui lòng thử lại!',
        ], 422);
    }

    /**
     * Đặt lại mật khẩu mới sau khi xác thực OTP thành công
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'new_password' => 'required|string|min:6',
        ]);

        $email = trim(strtolower($validated['email']));
        $otp = trim($validated['otp']);
        $newPassword = $validated['new_password'];

        $cachedOtp = Cache::get("password_reset_otp_{$email}");
        $isOtpValid = false;

        if ($cachedOtp && $cachedOtp === $otp) {
            $isOtpValid = true;
        } else {
            $record = DB::table('password_reset_tokens')->where('email', $email)->first();
            if ($record && Hash::check($otp, $record->token) && now()->diffInMinutes($record->created_at) <= 15) {
                $isOtpValid = true;
            }
        }

        if (!$isOtpValid) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực không hợp lệ hoặc phiên đặt lại mật khẩu đã hết hạn. Vui lòng gửi lại yêu cầu.',
            ], 422);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản người dùng.',
            ], 404);
        }

        // Cập nhật mật khẩu mới đã được băm an toàn
        $user->password = Hash::make($newPassword);
        $user->save();

        // Xóa mã OTP đã dùng
        Cache::forget("password_reset_otp_{$email}");
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        Log::info("ERP User [{$email}] successfully reset password.");

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công! Bạn có thể sử dụng mật khẩu mới để đăng nhập ngay.',
        ]);
    }

    /**
     * Bootstrap authentication state for SPA
     */
    public function getBootstrapData(): array
    {
        $authUser = null;
        if (Auth::check()) {
            $u = Auth::user();
            $roleSlug = $u->roles->first()?->slug ?? 'Sales_Rep';
            $authUser = [
                'id'         => (string) $u->id,
                'code'       => $u->code ?? "NV-{$u->id}",
                'name'       => $u->name,
                'role'       => $roleSlug,
                'department' => $u->department ?? 'Sales',
                'avatar'     => $u->avatar ?? ("https://ui-avatars.com/api/?name=" . urlencode($u->name) . "&background=0D8ABC&color=fff"),
                'phone'      => $u->phone ?? '',
                'email'      => $u->email,
                'status'     => $u->status ?? 'Đang làm việc',
                'branch'     => $u->branch ?? 'Trụ sở Chính',
            ];
        }

        return [
            'isLoggedIn' => Auth::check(),
            'user'       => $authUser,
        ];
    }
}

