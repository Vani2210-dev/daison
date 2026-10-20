<!DOCTYPE html>
<html lang="vi" class="h-full bg-[#060a13]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng Nhập Hệ Thống | ĐẠI ĐẠI SƠN ERP</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Be Vietnam Pro"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0084ff',
                            700: '#0369a1',
                            900: '#072448',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>
<body class="min-h-full flex items-center justify-center p-3 sm:p-6 bg-[#060a13] font-sans relative overflow-x-hidden selection:bg-sky-500 selection:text-white" x-data="loginApp()">

    <!-- Ambient glowing lights background -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/15 rounded-full blur-[120px]"></div>
        <div class="absolute top-1/2 -right-40 w-[480px] h-[480px] bg-sky-500/10 rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 left-1/3 w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[140px]"></div>
        <div class="absolute inset-0 bg-grid-pattern opacity-40"></div>
    </div>

    <!-- Main Container Card -->
    <div class="relative z-10 w-full max-w-5xl bg-[#091122]/90 backdrop-blur-xl rounded-[28px] sm:rounded-[32px] border border-slate-700/50 shadow-2xl shadow-blue-950/80 overflow-hidden">
        
        <div class="grid grid-cols-1 lg:grid-cols-12">
            
            <!-- LEFT COLUMN: Brand showcase & Logo centered -->
            <div class="lg:col-span-6 p-6 sm:p-10 lg:p-12 flex flex-col justify-between bg-gradient-to-b from-[#0b162c] to-[#091224] border-b lg:border-b-0 lg:border-r border-slate-800/80 relative overflow-hidden">
                
                <!-- Subtle internal decorative glow -->
                <div class="absolute -top-24 -left-24 w-72 h-72 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Top Badge -->
                <div class="relative z-10">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-500/10 border border-sky-400/30 text-sky-300 text-xs font-semibold shadow-xs">
                        <i class="fa-solid fa-layer-group text-sky-400 text-xs"></i>
                        <span>Hệ Thống Quản Trị Doanh Nghiệp Tập Trung</span>
                    </span>
                </div>

                <!-- Center Logo Showcase (Clean & Modern) -->
                <div class="relative z-10 my-auto py-10 flex flex-col items-center justify-center text-center space-y-5">
                    <div class="bg-white p-5 sm:p-6 rounded-3xl shadow-2xl shadow-blue-950/80 border border-white/60 max-w-[280px] sm:max-w-[320px] transition-transform duration-300 hover:scale-105">
                        <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="Nhà Phân Phối Đại Sơn" class="w-full h-auto object-contain">
                    </div>
                    
                    <div class="space-y-1.5">
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight uppercase">
                            ĐẠI ĐẠI SƠN <span class="text-sky-400">ERP</span>
                        </h1>
                        <p class="text-xs text-slate-400 font-medium tracking-wide">
                            Nhà Phân Phối Đại Sơn &bull; Phiên Bản 2026
                        </p>
                    </div>
                </div>

                <!-- Left Footer Info -->
                <div class="relative z-10 pt-6 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                    <div class="flex items-center gap-2 text-emerald-400/90 font-medium text-[11px] sm:text-xs">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                        <span>Bảo mật dữ liệu nội bộ</span>
                    </div>
                    <div class="text-slate-500 font-medium text-[11px] sm:text-xs">
                        &copy; 2026 Đại Sơn
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Login Form -->
            <div class="lg:col-span-6 p-6 sm:p-10 lg:p-12 flex flex-col justify-center bg-[#0d1527] relative">
                
                <div class="space-y-6">

                    <!-- Title & Subtitle -->
                    <div class="space-y-1.5">
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                            Đăng Nhập Tài Khoản
                        </h2>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Nhập email hoặc mã nhân viên được cấp để truy cập phân hệ làm việc
                        </p>
                    </div>

                    <!-- Flash messages -->
                    @if(session('error'))
                        <div class="p-3 bg-rose-500/15 border border-rose-500/30 text-rose-300 rounded-xl text-xs font-semibold flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-exclamation text-rose-400 shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="p-3 bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 rounded-xl text-xs font-semibold flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-400 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form action="{{ url('/login') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Field 1: Email / Employee Code -->
                        <div>
                            <label class="block text-slate-300 font-semibold text-xs mb-1.5">
                                Email Hoặc Mã Nhân Viên <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input 
                                    type="text" 
                                    name="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    autofocus 
                                    placeholder="admin@kbtech.com hoặc NV-001" 
                                    class="w-full pl-10 pr-3.5 py-3 bg-[#131d31] hover:bg-[#162138] focus:bg-[#17233c] border border-slate-700/70 focus:border-sky-500 text-white placeholder-slate-500 rounded-xl text-xs font-medium focus:outline-none transition-all shadow-inner"
                                >
                            </div>
                        </div>

                        <!-- Field 2: Password -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-slate-300 font-semibold text-xs">
                                    Mật Khẩu <span class="text-rose-500">*</span>
                                </label>
                                <button 
                                    type="button" 
                                    @click="showForgotModal = true" 
                                    class="text-xs font-semibold text-sky-400 hover:text-sky-300 hover:underline cursor-pointer"
                                >
                                    Quên mật khẩu?
                                </button>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input 
                                    :type="showPass ? 'text' : 'password'" 
                                    name="password" 
                                    required 
                                    placeholder="••••••••" 
                                    class="w-full pl-10 pr-10 py-3 bg-[#131d31] hover:bg-[#162138] focus:bg-[#17233c] border border-slate-700/70 focus:border-sky-500 text-white placeholder-slate-500 rounded-xl text-xs font-medium focus:outline-none transition-all tracking-wider shadow-inner"
                                >
                                <button 
                                    type="button" 
                                    @click="showPass = !showPass" 
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors cursor-pointer"
                                    title="Hiển thị / ẩn mật khẩu"
                                >
                                    <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Checkbox: Remember me -->
                        <div class="flex items-center justify-between pt-0.5">
                            <label class="flex items-center gap-2.5 cursor-pointer text-xs text-slate-300 select-none">
                                <input 
                                    type="checkbox" 
                                    name="remember" 
                                    checked 
                                    class="w-4 h-4 rounded bg-[#131d31] border-slate-600 text-sky-500 focus:ring-0 focus:ring-offset-0 cursor-pointer accent-sky-500"
                                >
                                <span>Ghi nhớ phiên đăng nhập trên thiết bị này</span>
                            </label>
                        </div>

                        <!-- Primary Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full py-3.5 bg-gradient-to-r from-blue-600 via-sky-600 to-blue-500 hover:from-blue-500 hover:to-sky-400 text-white font-extrabold text-xs sm:text-sm tracking-wider uppercase rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-500/50 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]"
                        >
                            <span>ĐĂNG NHẬP VÀO HỆ THỐNG</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t border-slate-800"></div>
                        <span class="flex-shrink mx-3 text-[10px] uppercase font-bold tracking-widest text-slate-500">
                            KHÔI PHỤC QUYỀN TRUY CẬP
                        </span>
                        <div class="flex-grow border-t border-slate-800"></div>
                    </div>

                    <!-- Secondary Action: OTP Reset -->
                    <button 
                        type="button" 
                        @click="showForgotModal = true" 
                        class="w-full py-3 bg-[#131d31] hover:bg-[#18243c] border border-slate-700/70 hover:border-slate-600 text-slate-300 hover:text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer shadow-xs"
                    >
                        <i class="fa-solid fa-key text-sky-400 text-xs"></i>
                        <span>Quên mật khẩu? Lấy mã OTP qua Email</span>
                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- FORGOT PASSWORD OTP MODAL (Matching Dark Aesthetic) -->
    <div x-show="showForgotModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div @click.away="showForgotModal = false" class="w-full max-w-md bg-[#0e172a] rounded-3xl shadow-2xl p-6 sm:p-7 space-y-5 border border-slate-700/80 text-white">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-black text-white text-sm sm:text-base flex items-center gap-2">
                    <i class="fa-solid fa-key text-sky-400"></i>
                    <span>Khôi Phục Mật Khẩu Qua Email OTP</span>
                </h3>
                <button type="button" @click="showForgotModal = false" class="text-slate-400 hover:text-white transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Step 1: Send OTP -->
            <div x-show="forgotStep === 1" class="space-y-4">
                <p class="text-xs text-slate-400 leading-relaxed">
                    Nhập địa chỉ Email tài khoản của bạn để nhận mã xác thực OTP 6 số.
                </p>
                <div>
                    <label class="block text-slate-300 font-semibold text-xs mb-1.5">Email Tài Khoản</label>
                    <input 
                        type="email" 
                        x-model="forgotEmail" 
                        placeholder="your-email@kbtech.com" 
                        class="w-full px-3.5 py-2.5 bg-[#131d31] border border-slate-700/80 rounded-xl text-xs text-white placeholder-slate-500 font-semibold focus:border-sky-500 focus:outline-none"
                    >
                </div>
                <div x-show="forgotMsg" class="text-xs font-bold text-rose-400" x-text="forgotMsg"></div>
                <button 
                    type="button" 
                    @click="sendOtp()" 
                    :disabled="forgotLoading" 
                    class="w-full py-3 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                >
                    <i class="fa-solid fa-paper-plane" x-show="!forgotLoading"></i>
                    <i class="fa-solid fa-circle-notch fa-spin" x-show="forgotLoading"></i>
                    <span x-text="forgotLoading ? 'Đang gửi mã...' : 'Gửi Mã Xác Thực OTP'"></span>
                </button>
            </div>

            <!-- Step 2: Verify OTP & New Password -->
            <div x-show="forgotStep === 2" class="space-y-4">
                <p class="text-xs text-emerald-400 font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Mã OTP đã được gửi đến <strong class="text-white" x-text="forgotEmail"></strong></span>
                </p>
                <div>
                    <label class="block text-slate-300 font-semibold text-xs mb-1.5">Mã OTP (6 chữ số)</label>
                    <input 
                        type="text" 
                        maxlength="6" 
                        x-model="forgotOtp" 
                        placeholder="123456" 
                        class="w-full px-3.5 py-2.5 bg-[#131d31] border border-slate-700/80 rounded-xl text-center text-lg font-mono font-black tracking-widest text-sky-400 focus:border-sky-500 focus:outline-none"
                    >
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold text-xs mb-1.5">Mật Khẩu Mới</label>
                    <input 
                        type="password" 
                        x-model="newPassword" 
                        placeholder="Tối thiểu 6 ký tự..." 
                        class="w-full px-3.5 py-2.5 bg-[#131d31] border border-slate-700/80 rounded-xl text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                    >
                </div>
                <div x-show="forgotMsg" class="text-xs font-bold text-rose-400" x-text="forgotMsg"></div>
                <button 
                    type="button" 
                    @click="resetPasswordWithOtp()" 
                    :disabled="forgotLoading" 
                    class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                >
                    <i class="fa-solid fa-shield-check" x-show="!forgotLoading"></i>
                    <i class="fa-solid fa-circle-notch fa-spin" x-show="forgotLoading"></i>
                    <span x-text="forgotLoading ? 'Đang xử lý...' : 'Xác Nhận Đổi Mật Khẩu'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
    function loginApp() {
        return {
            showPass: false,
            showForgotModal: false,
            forgotStep: 1,
            forgotEmail: '',
            forgotOtp: '',
            newPassword: '',
            forgotLoading: false,
            forgotMsg: '',

            async sendOtp() {
                if (!this.forgotEmail) {
                    this.forgotMsg = 'Vui lòng nhập email.';
                    return;
                }
                this.forgotLoading = true;
                this.forgotMsg = '';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch('/api/auth/forgot-password/send-otp', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                        body: JSON.stringify({ email: this.forgotEmail })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.forgotStep = 2;
                    } else {
                        this.forgotMsg = data.message || 'Không tìm thấy tài khoản.';
                    }
                } catch (e) {
                    this.forgotMsg = 'Lỗi kết nối máy chủ.';
                } finally {
                    this.forgotLoading = false;
                }
            },

            async resetPasswordWithOtp() {
                if (!this.forgotOtp || !this.newPassword) {
                    this.forgotMsg = 'Vui lòng nhập đủ OTP và mật khẩu mới.';
                    return;
                }
                this.forgotLoading = true;
                this.forgotMsg = '';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch('/api/auth/forgot-password/reset-password', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                        body: JSON.stringify({
                            email: this.forgotEmail,
                            otp: this.forgotOtp,
                            password: this.newPassword
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert('Đổi mật khẩu thành công! Bạn có thể đăng nhập ngay bây giờ.');
                        this.showForgotModal = false;
                        this.forgotStep = 1;
                        this.forgotOtp = '';
                        this.newPassword = '';
                    } else {
                        this.forgotMsg = data.message || 'Mã OTP không chính xác.';
                    }
                } catch (e) {
                    this.forgotMsg = 'Lỗi kết nối máy chủ.';
                } finally {
                    this.forgotLoading = false;
                }
            }
        };
    }
    </script>
</body>
</html>
