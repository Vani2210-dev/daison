@extends('layouts.app')

@section('title', ($isEdit ? 'Chỉnh Sửa Hồ Sơ: ' . $user->name : 'Thêm Mới Cán Bộ Nhân Sự') . ' | ERP Đại Sơn')

@section('content')
<div x-data="staffFormApp()" x-init="initForm()" class="w-full space-y-5 pb-20">

    <!-- Top Action Header -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ url('/staff') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors shadow-2xs">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-user-gear text-sky-600"></i>
                        <span>{{ $isEdit ? 'Chỉnh Sửa Hồ Sơ & Phân Quyền: ' . $user->name : 'Thêm Mới Cán Bộ Nhân Sự' }}</span>
                    </h1>
                    @if($isEdit)
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded bg-sky-100 text-sky-800 border border-sky-200">
                            {{ $user->code ?: ('NV-' . $user->id) }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Quản lý thông tin hồ sơ và cấu hình phân quyền chi tiết thuộc vai trò được phân công.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ url('/staff') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all cursor-pointer">
                Hủy Bỏ
            </a>
            <button type="button" @click="submitForm()" :disabled="saving"
                    class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-sky-600/20 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95">
                <i x-show="saving" class="fa-solid fa-spinner fa-spin"></i>
                <i x-show="!saving" class="fa-solid fa-floppy-disk"></i>
                <span x-text="saving ? 'Đang lưu...' : '{{ $isEdit ? 'Lưu Cập Nhật & Phân Quyền' : 'Xác Nhận Thêm Mới' }}'"></span>
            </button>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form @submit.prevent="submitForm()" class="space-y-5">
        @csrf
        <input type="hidden" name="id" value="{{ $user->id }}">

        <!-- 1. THÔNG TIN HỒ SƠ & TÀI KHOẢN -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-black text-xs">1</div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Thông Tin Định Danh & Tài Khoản Truy Cập</h2>
                    <p class="text-[11px] text-slate-400 font-medium">Họ tên, mã nhân sự, thông tin đăng nhập và cơ cấu làm việc</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <!-- Họ và tên -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Họ và Tên Nhân Viên <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" x-model="formData.name" required
                           placeholder="VD: Nguyễn Văn An"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-sky-500 transition-all">
                </div>

                <!-- Mã Nhân Viên -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Mã Nhân Sự (Code)
                    </label>
                    <input type="text" x-model="formData.code"
                           placeholder="VD: NV-001 (để trống tự sinh)"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono text-xs font-bold text-sky-900 outline-none focus:border-sky-500 uppercase transition-all">
                </div>

                <!-- Số Điện Thoại -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Số Điện Thoại Liên Hệ <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" x-model="formData.phone" required
                           placeholder="VD: 0988.888.999"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono text-xs font-bold text-slate-800 outline-none focus:border-sky-500 transition-all">
                </div>

                <!-- Email Đăng Nhập -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Email Đăng Nhập Hệ Thống <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" x-model="formData.email" required
                           placeholder="VD: an.nv@daison.vn"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-medium text-slate-800 outline-none focus:border-sky-500 transition-all">
                </div>

                <!-- Mật Khẩu -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Mật Khẩu Đăng Nhập {{ $isEdit ? '(Để trống nếu giữ nguyên)' : '*' }}
                    </label>
                    <input type="password" x-model="formData.password" {{ $isEdit ? '' : 'required' }}
                           placeholder="{{ $isEdit ? '•••••••• (Nhập nếu muốn đổi)' : 'Tối thiểu 6 ký tự' }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-medium text-slate-800 outline-none focus:border-sky-500 transition-all">
                </div>

                <!-- Trạng Thái Làm Việc -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Trạng Thái Hoạt Động</label>
                    <select x-model="formData.status"
                            class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-sky-500 transition-all cursor-pointer">
                        <option value="Đang làm việc">🟢 Đang làm việc</option>
                        <option value="Tạm khóa / Thôi việc">🔴 Tạm khóa / Thôi việc</option>
                    </select>
                </div>

                <!-- Phòng Ban -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Phòng Ban Công Tác</label>
                    <select x-model="formData.department"
                            class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-sky-500 transition-all cursor-pointer">
                        <option value="Executive">Ban Giám Đốc / Điều Hành</option>
                        <option value="Accounting">Kế Toán & Tài Chính</option>
                        <option value="Sales">Kinh Doanh & Bán Hàng</option>
                        <option value="Warehouse">Kho Vận & Quản Lý Vật Tư</option>
                        <option value="Logistics">Vận Tải & Đội Xe Giao Hàng</option>
                    </select>
                </div>

                <!-- Phân Loại Vai Trò Công Nợ -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Phân Loại Vai Trò & Công Nợ <span class="text-rose-500">*</span>
                    </label>
                    <select x-model="formData.staff_type"
                            class="w-full px-3.5 py-2.5 bg-purple-50/70 focus:bg-white border border-purple-200 rounded-xl font-bold text-purple-900 outline-none focus:border-purple-500 transition-all cursor-pointer">
                        <option value="company_sales">🏢 Sale Công Ty (Kinh doanh nội bộ)</option>
                        <option value="dealer_sales">🏪 Sale Đại Lý Phụ Thuộc (Từ Đại lý cài vào)</option>
                        <option value="driver">🚚 Người Vận Chuyển / Lái Xe (Chịu nợ hàng xe)</option>
                        <option value="warehouse">📦 Nhân Viên Kho (Soạn hàng & QC)</option>
                        <option value="accountant">💰 Kế Toán Công Nợ & Quỹ</option>
                        <option value="manager">👑 Ban Lãnh Đạo / Quản Lý</option>
                    </select>
                </div>

                <!-- Đại Lý Phụ Thuộc (Hiển thị nếu là Sale đại lý phụ thuộc) -->
                <div x-show="formData.staff_type === 'dealer_sales'" x-cloak>
                    <label class="block font-bold text-purple-900 mb-1.5">
                        Đại Lý Trực Thuộc (* Khi lên đơn tự động ghi nợ vào ĐL này)
                    </label>
                    <select x-model="formData.dealer_id"
                            class="w-full px-3.5 py-2.5 bg-purple-100/60 focus:bg-white border border-purple-300 rounded-xl font-bold text-purple-950 outline-none focus:border-purple-600 transition-all cursor-pointer">
                        <option value="">-- Chọn đại lý trực thuộc --</option>
                        <template x-for="dl in dealersList" :key="dl.id">
                            <option :value="dl.id" x-text="dl.name + ' (' + (dl.code || 'DL-' + dl.id) + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Hạn Mức Quản Lý / Giữ Nợ -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Hạn Mức Công Nợ Được Phép Giữ (VNĐ)</label>
                    <input type="number" x-model="formData.credit_limit" min="0" step="1000000"
                           placeholder="VD: 50000000"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono font-bold text-rose-700 outline-none focus:border-sky-500 transition-all">
                </div>

                <!-- Chi Nhánh Làm Việc -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Chi Nhánh / Kho Cơ Sở</label>
                    <select x-model="formData.branch_id"
                            class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-sky-500 transition-all cursor-pointer">
                        <template x-for="br in branchesList" :key="br.id">
                            <option :value="br.id" x-text="br.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Mức Lương Cơ Bản -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Mức Lương Cơ Bản (VNĐ / Tháng)</label>
                    <input type="number" x-model="formData.base_salary" step="500000" min="0"
                           placeholder="VD: 12000000"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono font-bold text-emerald-700 outline-none focus:border-sky-500 transition-all">
                </div>

                <!-- Chế Độ Phép Năm -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Tổng Phép Năm (Ngày / Năm)</label>
                    <input type="number" x-model="formData.annual_leave_days" min="0" max="30"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-sky-500 transition-all">
                </div>

                <!-- Số CCCD / CMND -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Số CCCD / Định Danh</label>
                    <input type="text" x-model="formData.citizen_id"
                           placeholder="VD: 038095001234"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono text-slate-800 outline-none focus:border-sky-500 transition-all">
                </div>

                <!-- Tài Khoản Ngân Hàng -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Số TK Ngân Hàng & Tên NH</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" x-model="formData.bank_account" placeholder="Số TK"
                               class="w-full px-3 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono text-slate-800 outline-none focus:border-sky-500 transition-all">
                        <input type="text" x-model="formData.bank_name" placeholder="Tên NH (MB, Vietcombank...)"
                               class="w-full px-3 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-sky-500 transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CHỌN VAI TRÒ HỆ THỐNG -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-black text-xs">2</div>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">Vai Trò Hệ Thống (System Role)</h2>
                        <p class="text-[11px] text-slate-400 font-medium">Chọn vai trò chính để xác định nhóm quyền hạn áp dụng cho nhân sự</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-purple-700 bg-purple-50 px-3 py-1 rounded-xl border border-purple-200 font-mono">
                    Vai trò hiện tại: <span x-text="getCurrentRoleName()"></span>
                </span>
            </div>

            <!-- Role Selection Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3">
                <template x-for="r in rolesList" :key="r.id">
                    <div @click="selectRole(r.slug)"
                         class="p-3.5 rounded-2xl border transition-all cursor-pointer relative flex flex-col justify-between select-none"
                         :class="formData.role === r.slug
                            ? 'border-[#1b4a8c] bg-sky-50/90 shadow-md ring-2 ring-[#1b4a8c]/25 transform scale-[1.02]'
                            : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/70 shadow-2xs'">
                        
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs shadow-2xs"
                                      :class="formData.role === r.slug ? 'bg-[#1b4a8c] text-white' : 'bg-slate-100 text-slate-600'">
                                    <i :class="getRoleIconClass(r.slug)"></i>
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-full"
                                      :class="formData.role === r.slug ? 'bg-sky-200 text-sky-900' : 'bg-slate-100 text-slate-500'"
                                      x-text="(r.permissions ? r.permissions.length : 0) + ' quyền'">
                                </span>
                            </div>
                            <span class="font-black text-xs text-slate-900 tracking-tight line-clamp-1" :title="r.name" x-text="r.name"></span>
                            <p class="text-[10px] text-slate-400 line-clamp-2 mt-1 leading-snug" :title="r.description" x-text="r.description || 'Chưa có mô tả'"></p>
                        </div>

                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                            <span class="font-bold" :class="formData.role === r.slug ? 'text-[#1b4a8c]' : 'text-slate-400'">
                                <i class="fa-solid" :class="formData.role === r.slug ? 'fa-circle-check text-sky-600' : 'fa-circle text-slate-300'"></i>
                                <span x-text="formData.role === r.slug ? 'Đang chọn' : 'Chọn vai trò'"></span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. PHÂN QUYỀN TRONG TRANG NÀY - CHỈ HIỂN THỊ CÁC QUYỀN CỦA VAI TRÒ -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-xs">3</div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-extrabold text-slate-900">Phân Quyền Cho Nhân Sự Thuộc Vai Trò</h2>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-mono"
                                  x-text="activePermissionsCount + ' / ' + currentRolePermissions.length + ' quyền được cấp'"></span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Chỉ hiển thị các quyền hạn thuộc vai trò <strong class="text-slate-700" x-text="getCurrentRoleName()"></strong>. Bật/tắt switch để tùy biến quyền riêng cho nhân sự này.</p>
                    </div>
                </div>

                <!-- Select All / Deselect All Controls -->
                <div x-show="formData.role !== 'Manager'" class="flex items-center gap-2">
                    <button type="button" @click="selectAllRolePermissions()"
                            class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs border border-emerald-200 transition-colors flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-check-double text-xs"></i>
                        <span>Cấp Tất Cả</span>
                    </button>
                    <button type="button" @click="deselectAllRolePermissions()"
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs border border-slate-200 transition-colors flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Bỏ Chọn Hết</span>
                    </button>
                </div>
            </div>

            <!-- Notice for Manager Role -->
            <div x-show="formData.role === 'Manager'" class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 flex items-center gap-3">
                <i class="fa-solid fa-crown text-amber-500 text-2xl shrink-0"></i>
                <div class="text-xs">
                    <h4 class="font-black text-sm text-sky-950">Toàn Quyền Quản Trị Hệ Thống (Super Administrator)</h4>
                    <p class="text-sky-700 mt-0.5 font-medium">Tài khoản thuộc vai trò <strong>Ban Giám Đốc / Quản Lý</strong> tự động có toàn quyền truy cập, vận hành, kiểm toán và cấu hình toàn bộ phân hệ ERP.</p>
                </div>
            </div>

            <!-- Role Permissions Grid -->
            <div x-show="formData.role !== 'Manager'">
                <template x-if="currentRolePermissions.length === 0">
                    <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i class="fa-solid fa-shield-halved text-slate-300 text-3xl mb-2"></i>
                        <p class="text-xs font-bold text-slate-600">Vai trò này hiện chưa được cấu hình quyền hạn mặc định nào.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Bạn có thể cấu hình ma trận vai trò tại menu <a href="{{ url('/roles') }}" class="text-sky-600 underline font-bold">Phân quyền vai trò</a>.</p>
                    </div>
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <template x-for="perm in currentRolePermissions" :key="perm.slug">
                        <div class="p-3.5 rounded-2xl border transition-all flex items-start justify-between gap-3 select-none"
                             :class="isPermGranted(perm.slug)
                                ? 'bg-white border-emerald-300 shadow-xs ring-1 ring-emerald-500/20'
                                : 'bg-slate-50/70 border-slate-200 hover:border-slate-300'">
                            
                            <div class="flex items-start gap-2.5 min-w-0">
                                <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs shrink-0 mt-0.5 shadow-2xs"
                                      :class="isPermGranted(perm.slug) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-500'">
                                    <i :class="getPermIcon(perm.slug)"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-extrabold text-xs text-slate-900 leading-tight" x-text="getPermName(perm)"></span>
                                        <span x-show="isPermGranted(perm.slug)"
                                              class="text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Đã cấp
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-medium leading-tight mt-0.5 line-clamp-2"
                                       x-text="getPermDesc(perm)"></p>
                                </div>
                            </div>

                            <!-- Toggle Switch -->
                            <button type="button" @click="togglePermission(perm.slug)"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none mt-1"
                                    :class="isPermGranted(perm.slug) ? 'bg-emerald-600' : 'bg-slate-300'"
                                    role="switch" :aria-checked="isPermGranted(perm.slug)">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                      :class="isPermGranted(perm.slug) ? 'translate-x-4' : 'translate-x-0'"></span>
                            </button>

                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Bar -->
        <div class="sticky bottom-4 z-40 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-slate-200 shadow-xl flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-bold text-slate-700">Vai trò đã chọn:</span>
                <span class="font-extrabold text-sky-800 font-mono" x-text="getCurrentRoleName()"></span>
                <span class="text-slate-300">•</span>
                <span class="text-slate-500 font-medium">Số quyền hiệu lực: <strong class="text-emerald-700 font-mono font-bold" x-text="activePermissionsCount"></strong> quyền</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ url('/staff') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all cursor-pointer">
                    Hủy Bỏ
                </a>
                <button type="button" @click="submitForm()" :disabled="saving"
                        class="px-6 py-2.5 bg-[#1b4a8c] hover:bg-[#153a6f] text-white font-black text-xs rounded-xl shadow-md shadow-sky-900/20 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95">
                    <i x-show="saving" class="fa-solid fa-spinner fa-spin"></i>
                    <i x-show="!saving" class="fa-solid fa-floppy-disk"></i>
                    <span x-text="saving ? 'Đang lưu dữ liệu...' : 'Lưu Thông Tin & Phân Quyền'"></span>
                </button>
            </div>
        </div>

    </form>

</div>

<script>
// Friendly Metadata for permissions
const PERM_DICT = {
    'orders.view': { name: 'Xem Đơn Hàng', desc: 'Tra cứu danh sách & chi tiết các đơn đặt hàng', icon: 'fa-regular fa-eye' },
    'orders.create': { name: 'Lên Đơn Mới', desc: 'Khởi tạo đơn hàng xuất bán cho đại lý/công trình', icon: 'fa-solid fa-plus' },
    'orders.edit': { name: 'Sửa Đơn Hàng', desc: 'Chỉnh sửa mặt hàng, số lượng và trần chiết khấu', icon: 'fa-solid fa-pen' },
    'orders.approve': { name: 'Duyệt Đơn Bán', desc: 'Kế toán/Quản lý chốt duyệt đơn đủ điều kiện xuất kho', icon: 'fa-solid fa-shield-halved' },
    'orders.cancel': { name: 'Hủy Đơn Hàng', desc: 'Hủy đơn hàng chưa xuất kho khi khách hủy yêu cầu', icon: 'fa-regular fa-trash-can' },
    'orders.status': { name: 'Đổi Trạng Thái', desc: 'Chuyển trạng thái đơn sang kho soạn, giao hàng, hoàn thành', icon: 'fa-solid fa-arrows-rotate' },
    'orders.export': { name: 'In Phiếu & Excel', desc: 'In phiếu bán hàng, phiếu giao hàng và xuất excel', icon: 'fa-regular fa-file-lines' },
    
    'quotation.view': { name: 'Xem Báo Giá', desc: 'Tra cứu danh sách bảng báo giá đã lập', icon: 'fa-regular fa-eye' },
    'quotation.create': { name: 'Lập Báo Giá', desc: 'Tạo bảng báo giá sản phẩm, ống nhựa gửi đại lý', icon: 'fa-solid fa-plus' },
    'quotation.edit': { name: 'Sửa Báo Giá', desc: 'Điều chỉnh đơn giá và chiết khấu báo giá', icon: 'fa-solid fa-pen' },
    'quotation.convert': { name: 'Chuyển Đơn Bán', desc: 'Tự động tạo đơn hàng chính thức từ bảng báo giá', icon: 'fa-solid fa-cart-shopping' },
    
    'dealers.view': { name: 'Xem Hồ Sơ Đại Lý', desc: 'Tra cứu danh sách đại lý, cấp bậc, địa chỉ giao hàng', icon: 'fa-regular fa-eye' },
    'dealers.create': { name: 'Thêm Đại Lý Mới', desc: 'Tạo mới hồ sơ khách hàng công trình & đại lý', icon: 'fa-solid fa-plus' },
    'dealers.edit': { name: 'Sửa Thông Tin', desc: 'Cập nhật địa chỉ nhận hàng, số điện thoại, tuyến đường', icon: 'fa-solid fa-pen' },
    
    'debt.view': { name: 'Xem Công Nợ Đại Lý', desc: 'Tra cứu số nợ hiện tại, nợ trong hạn & nợ quá hạn', icon: 'fa-regular fa-eye' },
    'debt.adjust': { name: 'Chỉnh Hạn Mức Nợ', desc: 'Nâng/hạ hạn mức nợ và thời gian gối đầu cho đại lý', icon: 'fa-solid fa-sliders' },
    'debt.approve': { name: 'Duyệt Vượt Hạn Mức', desc: 'Bảo lãnh xuất hàng khi đại lý đang chạm trần công nợ', icon: 'fa-solid fa-shield-halved' },
    
    'inventory.view': { name: 'Xem Tồn Kho', desc: 'Tra cứu số lượng tồn kho theo cây, cuộn và kho bãi', icon: 'fa-regular fa-eye' },
    'inventory.create': { name: 'Thêm Mặt Hàng', desc: 'Tạo mã sản phẩm, ống nhựa, phụ kiện mới', icon: 'fa-solid fa-plus' },
    'inventory.edit': { name: 'Sửa Thông Tin', desc: 'Chỉnh sửa đơn vị tính, quy cách ống và giá vốn', icon: 'fa-solid fa-pen' },
    
    'inv_picking.view': { name: 'Xem Lệnh Bốc Hàng', desc: 'Tra cứu danh sách đơn hàng cần bốc xếp và soạn', icon: 'fa-regular fa-eye' },
    'inv_picking.view_all_categories': { name: 'Xem Bốc Xếp Không Phân Loại', desc: 'Xem bốc xếp tất cả các loại sản phẩm không giới hạn nhóm', icon: 'fa-solid fa-layer-group' },
    'inv_picking.pick': { name: 'Bốc Hàng & Ghi Số', desc: 'Bốc ống, phụ kiện và nhập số lượng thực bốc', icon: 'fa-solid fa-boxes-packing' },
    'inv_picking.approve': { name: 'Chốt Soạn Xong', desc: 'Xác nhận hoàn tất khâu bốc hàng chuyển sang KCS', icon: 'fa-solid fa-shield-halved' },
    
    'inv_qc.view': { name: 'Xem Danh Sách QC', desc: 'Tra cứu các đơn hàng đã soạn xong chờ kiểm duyệt', icon: 'fa-regular fa-eye' },
    'inv_qc.qc': { name: 'Kiểm Hàng Thực Tế', desc: 'Đối chiếu số lượng thực tế với đơn hàng và khuyến mãi', icon: 'fa-solid fa-clipboard-check' },
    
    'inv_import.view': { name: 'Xem Phiếu Nhập', desc: 'Xem danh sách phiếu nhập kho nhà máy & hoàn trả', icon: 'fa-regular fa-eye' },
    'inv_import.create': { name: 'Tạo Phiếu Nhập', desc: 'Lập phiếu nhập kho thành phẩm ống PVC và phụ kiện', icon: 'fa-solid fa-plus' },
    
    'inv_export.view': { name: 'Xem Phiếu Xuất', desc: 'Tra cứu phiếu xuất kho bán lẻ, đại lý, điều chuyển', icon: 'fa-regular fa-eye' },
    'inv_export.create': { name: 'Tạo Phiếu Xuất', desc: 'Lập phiếu xuất kho hàng hóa theo đơn bán', icon: 'fa-solid fa-plus' },
    
    'delivery_dispatch.view': { name: 'Xem Điều Phối Xe', desc: 'Tra cứu lệnh điều xe giao hàng và trạng thái lộ trình', icon: 'fa-regular fa-eye' },
    'delivery_dispatch.create': { name: 'Tạo Lệnh Giao Hàng', desc: 'Gộp các đơn hàng cùng tuyến vào một chuyến xe tải', icon: 'fa-solid fa-truck-fast' },
    'delivery_dispatch.settle': { name: 'Quyết Toán Chuyến Xe', desc: 'Xác nhận hoàn tất chuyến xe và bàn giao chứng từ', icon: 'fa-solid fa-clipboard-check' },
    
    'driver_debt.view': { name: 'Xem Tiền Thu COD', desc: 'Theo dõi tiền hàng lái xe thu hộ từ các đại lý', icon: 'fa-regular fa-eye' },
    'driver_debt.settle': { name: 'Quyết Toán Tiền Mặt', desc: 'Xác nhận thu tiền mặt lái xe bàn giao về quỹ', icon: 'fa-solid fa-hand-holding-dollar' },
    
    'fleet.view': { name: 'Xem Đội Xe & Chi Phí', desc: 'Tra cứu danh sách xe tải, nhiên liệu và bảo dưỡng', icon: 'fa-regular fa-eye' },
    
    'funds.view': { name: 'Xem Sổ Quỹ', desc: 'Tra cứu biến động số dư tiền mặt & tài khoản ngân hàng', icon: 'fa-regular fa-eye' },
    'funds.create_receipt': { name: 'Lập Phiếu Thu', desc: 'Thu tiền khách hàng, thu cọc, thu hồi nợ đại lý', icon: 'fa-solid fa-plus' },
    'funds.create_payment': { name: 'Lập Phiếu Chi', desc: 'Chi trả tiền nhà máy Tiền Phong, chi phí vận hành', icon: 'fa-solid fa-money-bill-wave' },
    
    'staff_list.view': { name: 'Xem Hồ Sơ Nhân Sự', desc: 'Tra cứu danh sách cán bộ công nhân viên công ty', icon: 'fa-regular fa-eye' },
    'staff_list.create': { name: 'Thêm Nhân Viên', desc: 'Tạo tài khoản và hồ sơ nhân viên mới', icon: 'fa-solid fa-plus' },
    'staff_list.edit': { name: 'Sửa Thông Tin', desc: 'Cập nhật phòng ban, chức vụ, mức lương cơ bản', icon: 'fa-solid fa-pen' },
    'staff_list.lock': { name: 'Khóa / Thôi Việc', desc: 'Khóa tài khoản truy cập khi nhân sự nghỉ việc', icon: 'fa-solid fa-user-slash' },
    
    'payroll.view': { name: 'Xem Bảng Lương', desc: 'Tra cứu phiếu lương cá nhân và bảng lương phòng ban', icon: 'fa-regular fa-eye' },
    'attendance.view': { name: 'Xem Bảng Chấm Công', desc: 'Theo dõi giờ vào/ra ca và lịch sử định vị GPS chấm công', icon: 'fa-regular fa-eye' },
    'attendance.checkin': { name: 'Chấm Công GPS', desc: 'Thực hiện điểm danh ca làm việc qua điện thoại', icon: 'fa-solid fa-location-dot' },
    'kpi.view': { name: 'Xem Đánh Giá KPI', desc: 'Theo dõi điểm số hiệu suất công việc cá nhân/phòng ban', icon: 'fa-regular fa-eye' },
    'permissions.view': { name: 'Xem Ma Trận Quyền', desc: 'Tra cứu bảng phân quyền vai trò và tài khoản', icon: 'fa-regular fa-eye' },
    'task_assignment.view': { name: 'Xem Danh Sách Việc', desc: 'Tra cứu công việc cá nhân và tiến độ phòng ban', icon: 'fa-regular fa-eye' },
    'proposals.view': { name: 'Xem Đề Xuất Trình Ký', desc: 'Tra cứu các tờ trình, đề xuất mua sắm, xin nghỉ phép', icon: 'fa-regular fa-eye' },
    'danhmuc.view': { name: 'Xem Danh Mục', desc: 'Tra cứu đơn vị tính, nhóm sản phẩm, tài khoản ngân hàng', icon: 'fa-regular fa-eye' },
    'audit.view': { name: 'Xem Nhật Ký Kiểm Toán', desc: 'Tra cứu lịch sử đăng nhập, tạo, sửa, xóa dữ liệu', icon: 'fa-regular fa-eye' }
};

function staffFormApp() {
    return {
        rolesList: @json($roles ?? []),
        branchesList: @json($branches ?? []),
        dealersList: @json($dealers ?? []),
        userPermissions: @json($userPermissions ?? []),
        saving: false,

        formData: {
            id: {{ $user->id ?: 'null' }},
            name: '{{ addslashes($user->name ?? '') }}',
            code: '{{ addslashes($user->code ?? '') }}',
            phone: '{{ addslashes($user->phone ?? '') }}',
            email: '{{ addslashes($user->email ?? '') }}',
            password: '',
            department: '{{ addslashes($user->department ?? 'Sales') }}',
            staff_type: '{{ addslashes($user->staff_type ?? 'company_sales') }}',
            dealer_id: {{ $user->dealer_id ?: 'null' }},
            credit_limit: {{ (float)($user->credit_limit ?? 50000000) }},
            role: '{{ $user->roles->first()?->slug ?? 'Sales_Rep' }}',
            branch_id: {{ $user->branch_id ?? ($branches->first()?->id ?? 1) }},
            base_salary: {{ (float)($user->base_salary ?? 12000000) }},
            annual_leave_days: {{ (int)($user->annual_leave_days ?? 12) }},
            remaining_leave_days: {{ (float)($user->remaining_leave_days ?? 12) }},
            status: '{{ addslashes($user->status ?? 'Đang làm việc') }}',
            citizen_id: '{{ addslashes($user->citizen_id ?? '') }}',
            bank_account: '{{ addslashes($user->bank_account ?? '') }}',
            bank_name: '{{ addslashes($user->bank_name ?? '') }}',
            address: '{{ addslashes($user->address ?? '') }}'
        },

        initForm() {
            // If new user with no customized permissions, initialize with role permissions
            if (!this.formData.id && this.userPermissions.length === 0) {
                this.syncPermissionsWithRole();
            }
        },

        get currentRolePermissions() {
            const roleObj = this.rolesList.find(r => r.slug === this.formData.role);
            if (!roleObj || !roleObj.permissions) return [];
            return roleObj.permissions;
        },

        get activePermissionsCount() {
            if (this.formData.role === 'Manager') {
                return 'Toàn bộ';
            }
            const rolePermSlugs = this.currentRolePermissions.map(p => p.slug);
            return this.userPermissions.filter(slug => rolePermSlugs.includes(slug)).length;
        },

        getCurrentRoleName() {
            const r = this.rolesList.find(item => item.slug === this.formData.role);
            return r ? r.name : this.formData.role;
        },

        selectRole(slug) {
            if (this.formData.role === slug) return;
            this.formData.role = slug;
            // When switching role, automatically populate with the permissions of that new role
            this.syncPermissionsWithRole();
        },

        syncPermissionsWithRole() {
            const roleObj = this.rolesList.find(r => r.slug === this.formData.role);
            if (roleObj && roleObj.permissions) {
                this.userPermissions = roleObj.permissions.map(p => p.slug);
            } else {
                this.userPermissions = [];
            }
        },

        isPermGranted(slug) {
            if (this.formData.role === 'Manager') return true;
            return this.userPermissions.includes(slug);
        },

        togglePermission(slug) {
            if (this.formData.role === 'Manager') return;
            if (this.userPermissions.includes(slug)) {
                this.userPermissions = this.userPermissions.filter(s => s !== slug);
            } else {
                this.userPermissions.push(slug);
            }
        },

        selectAllRolePermissions() {
            this.userPermissions = this.currentRolePermissions.map(p => p.slug);
        },

        deselectAllRolePermissions() {
            this.userPermissions = [];
        },

        getRoleIconClass(slug) {
            const icons = {
                'Manager': 'fa-solid fa-crown',
                'Sales_Accountant': 'fa-solid fa-file-invoice-dollar',
                'Sales_Rep': 'fa-solid fa-handshake',
                'Warehouse_Manager': 'fa-solid fa-boxes-stacked',
                'Warehouse_Staff': 'fa-solid fa-dolly',
                'Driver': 'fa-solid fa-truck-fast',
                'Accountant_Chief': 'fa-solid fa-calculator'
            };
            return icons[slug] || 'fa-solid fa-user-shield';
        },

        getPermName(perm) {
            if (PERM_DICT[perm.slug] && PERM_DICT[perm.slug].name) {
                return PERM_DICT[perm.slug].name;
            }
            return perm.name || perm.slug;
        },

        getPermDesc(perm) {
            if (PERM_DICT[perm.slug] && PERM_DICT[perm.slug].desc) {
                return PERM_DICT[perm.slug].desc;
            }
            return perm.description || 'Chức năng thuộc hệ thống ERP';
        },

        getPermIcon(slug) {
            if (PERM_DICT[slug] && PERM_DICT[slug].icon) {
                return PERM_DICT[slug].icon;
            }
            return 'fa-solid fa-shield-halved';
        },

        async submitForm() {
            if (!this.formData.name || !this.formData.phone || !this.formData.email) {
                alert('Vui lòng điền đầy đủ Họ tên, Số điện thoại và Email đăng nhập!');
                return;
            }

            this.saving = true;

            const payload = {
                ...this.formData,
                permissions: this.userPermissions
            };

            try {
                const res = await fetch('{{ url("/staff/save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect || '{{ url("/staff") }}';
                } else {
                    alert(data.message || 'Lỗi khi lưu thông tin nhân sự');
                    this.saving = false;
                }
            } catch (err) {
                console.error(err);
                alert('Lỗi kết nối máy chủ khi lưu thông tin nhân sự.');
                this.saving = false;
            }
        }
    };
}
</script>
@endsection
