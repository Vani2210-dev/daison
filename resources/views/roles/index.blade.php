@extends('layouts.app')

@section('title', 'Phân Quyền & Vai Trò Hệ Thống (RBAC Matrix) - Đại Sơn ERP')

@section('content')
<div x-data="rolesApp()" x-init="initApp()" class="w-full space-y-5 pb-16">

    <!-- Floating Toast Notification (Above Floating Save Button) -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-22 right-6 z-[9999] px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold border"
         :class="toast.type === 'success' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-rose-600 text-white border-rose-500'">
        <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check text-base' : 'fa-solid fa-circle-exclamation text-base'"></i>
        <span x-text="toast.message"></span>
    </div>

    <!-- Floating Sticky Save Button (Luôn nổi ở góc dưới phải) -->
    <div class="fixed bottom-6 right-6 z-[9900]">
        <button type="button" @click="savePermissions()" :disabled="saving"
                class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs sm:text-sm font-black shadow-2xl shadow-emerald-700/50 border border-emerald-400/50 flex items-center gap-2.5 cursor-pointer transition-all disabled:opacity-50 group">
            <i class="fa-solid" :class="saving ? 'fa-spinner fa-spin' : 'fa-floppy-disk text-base group-hover:scale-110 transition-transform'"></i>
            <span x-text="saving ? 'Đang Lưu...' : 'Lưu Cấu Hình'"></span>
        </button>
    </div>

    <!-- 1. Header Banner -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-4 sm:p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <a href="{{ url('/dashboard') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors shadow-2xs">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-purple-600"></i>
                        <span>Sơ Đồ Phân Quyền & Vai Trò (RBAC Matrix)</span>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Phân quyền chi tiết theo từng Phân Hệ và Chức Năng nghiệp vụ cho các vai trò trong hệ thống</p>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ url('/audit-logs') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 flex items-center gap-1.5 transition-all shadow-2xs">
                    <i class="fa-solid fa-clock-rotate-left text-sky-600"></i>
                    <span>Xem Nhật Ký Audit Log</span>
                </a>
                <button type="button" @click="savePermissions()" :disabled="saving"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md shadow-emerald-600/20 flex items-center gap-2 cursor-pointer transition-all disabled:opacity-50">
                    <i class="fa-solid" :class="saving ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i>
                    <span x-text="saving ? 'Đang Lưu...' : 'Lưu Cấu Hình'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. TOP HORIZONTAL ROLES SELECTOR & TOOLBAR (DÀN NGANG TOÀN BỘ) -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-4 sm:p-5 space-y-4">
        
        <!-- Controls Row: Title, Subsystem Filter, Search, Quick Save -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i class="fa-solid fa-user-shield text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm sm:text-base font-black text-slate-900 uppercase tracking-wider">
                            Các Vai Trò Hệ Thống
                        </h2>
                        <span class="text-[11px] bg-purple-100 text-purple-800 px-2.5 py-0.5 rounded-full font-bold font-mono"
                              x-text="rolesData.length + ' vai trò'"></span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Nhấp chọn vai trò bên dưới để kiểm tra và kích hoạt ma trận quyền hạn tương ứng</p>
                </div>
            </div>

            <!-- Right Controls: Filter Subsystem, Search, Global Toggle -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Filter by Subsystem -->
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 shadow-2xs">
                    <i class="fa-solid fa-filter text-slate-400 text-xs"></i>
                    <span class="text-xs font-bold text-slate-500 hidden sm:inline">Lọc Phân Hệ:</span>
                    <select x-model="filterSubsystem" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer">
                        <option value="all">Tất cả phân hệ (Đầy đủ)</option>
                        <template x-for="sub in subsystems" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Search Permission Input -->
                <div class="relative min-w-[220px] sm:min-w-[280px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchKeyword"
                           placeholder="Tìm nhanh quyền hạn, chức năng..."
                           class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 font-medium text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:border-sky-500 transition-all placeholder:text-slate-400 shadow-2xs">
                </div>
            </div>
        </div>

        <!-- 7 Roles Cards Dàn Ngang (Horizontal Grid) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3">
            <template x-for="r in rolesData" :key="r.id">
                <div @click="selectRole(r)"
                     class="p-3 rounded-2xl border transition-all cursor-pointer relative flex flex-col justify-between select-none"
                     :class="selectedRoleSlug === r.slug 
                        ? 'border-[#1b4a8c] bg-sky-50/90 shadow-md ring-2 ring-[#1b4a8c]/25 transform scale-[1.01]' 
                        : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/80 shadow-2xs'">
                    
                    <div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="font-black text-xs text-slate-900 tracking-tight line-clamp-1" :title="r.name" x-text="r.name"></span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded-full font-mono shrink-0"
                                  :class="selectedRoleSlug === r.slug ? 'bg-sky-200/80 text-sky-900' : 'bg-slate-100 text-slate-600'"
                                  x-text="getRoleGrantedPercent(r.slug) + '%'"></span>
                        </div>
                        <p class="text-[10px] text-slate-400 line-clamp-1 mt-1 leading-snug" :title="r.description" x-text="r.description || 'Chưa có mô tả'"></p>
                    </div>

                    <div class="mt-2.5 pt-2 border-t border-slate-100">
                        <!-- Mini Progress Bar -->
                        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mb-1.5">
                            <div class="h-full transition-all duration-300 rounded-full"
                                 :class="selectedRoleSlug === r.slug ? 'bg-[#1b4a8c]' : 'bg-emerald-500'"
                                 :style="'width: ' + getRoleGrantedPercent(r.slug) + '%'"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="font-mono font-bold text-slate-500" x-text="getRoleGrantedCount(r.slug) + '/' + totalPermissionsCount"></span>
                            <span class="text-slate-400"><strong class="text-slate-600" x-text="getStaffCountForRole(r.slug)"></strong> nhân sự</span>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        <!-- Active Role Summary Bar -->
        <div class="bg-gradient-to-r from-sky-50/80 via-white to-sky-50/50 rounded-xl p-3 border border-sky-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs shadow-2xs">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-slate-500 font-medium">Đang thiết lập ma trận cho vai trò:</span>
                <span class="px-2.5 py-1 rounded-lg bg-[#1b4a8c] text-white font-black text-xs shadow-2xs flex items-center gap-1.5">
                    <i class="fa-solid fa-user-tag text-[10px]"></i>
                    <span x-text="currentRole?.name"></span>
                </span>
                <span class="font-mono text-[11px] text-slate-500 font-bold bg-white px-2 py-0.5 rounded border border-slate-200" x-text="currentRole?.slug"></span>
                <span class="text-slate-400 hidden lg:inline">&bull;</span>
                <span class="text-slate-500 hidden lg:inline" x-text="currentRole?.description"></span>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <div class="font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 px-3 py-1 rounded-lg text-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                    <span x-text="currentRoleGrantedCount + ' / ' + totalPermissionsCount + ' Quyền (' + currentRoleGrantedPercent + '%)'"></span>
                </div>
                <button type="button" @click="toggleAllSubsystems(true)"
                        class="px-2.5 py-1 bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-300 rounded-lg text-xs font-bold transition-all shadow-2xs cursor-pointer">
                    Bật tất cả
                </button>
                <button type="button" @click="toggleAllSubsystems(false)"
                        class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-600 border border-slate-300 rounded-lg text-xs font-bold transition-all shadow-2xs cursor-pointer">
                    Tắt tất cả
                </button>
            </div>
        </div>

    </div>

    <!-- 3. MAIN WORKSPACE: FULL WIDTH SUBSYSTEMS & FEATURES (KHÔNG CÓ KHOẢNG TRỐNG) -->
    <div class="space-y-6">

        <!-- Loop Through Subsystems (Phân hệ) -->
        <template x-for="sub in filteredSubsystems" :key="sub.id">
            <div class="space-y-4">
                
                <!-- Subsystem Banner Header (Matching user's mockup) -->
                <div class="bg-[#f0fbf6] border border-[#bbf0d8] rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-white text-emerald-600 border border-[#a7f3d0] flex items-center justify-center shrink-0 shadow-xs">
                            <i :class="sub.icon" class="text-xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight" x-text="sub.name"></h2>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#dcfce7] text-[#15803d] border border-[#86efac] font-mono"
                                      x-text="getSubsystemGrantedCount(sub) + ' / ' + getSubsystemTotalCount(sub) + ' Quyền được cấp'"></span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium mt-0.5" x-text="sub.desc"></p>
                        </div>
                    </div>

                    <!-- Top Right Master Action for Subsystem -->
                    <div class="flex items-center gap-2.5 shrink-0">
                        <button type="button" @click="toggleSubsystemPermissions(sub, true)"
                                class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 border border-[#86efac] text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-regular fa-square-check text-emerald-600"></i>
                            <span>Bật Hết Phân Hệ</span>
                        </button>
                        <button type="button" @click="toggleSubsystemPermissions(sub, false)"
                                class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-600 border border-slate-300 text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-regular fa-square text-slate-400"></i>
                            <span>Tắt Hết Phân Hệ</span>
                        </button>
                    </div>
                </div>

                <!-- Feature Cards: FLEX WRAP ZERO-GAP LAYOUT (DÀN ĐẦY HÀNG, KHÔNG ĐỂ TRỐNG) -->
                <div class="flex flex-wrap gap-4 items-stretch">
                    <template x-for="feat in sub.features" :key="feat.key">
                        <div x-show="isFeatureMatching(feat)" 
                             class="flex-1 min-w-[300px] lg:min-w-[340px] xl:min-w-[360px] max-w-full bg-white rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col overflow-hidden">
                            
                            <!-- Card Header -->
                            <div class="px-4 py-3.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-black text-xs sm:text-sm text-slate-900 tracking-tight" x-text="feat.name"></h3>
                                        <span class="font-mono text-[9px] px-1.5 py-0.2 rounded-md bg-slate-200/70 text-slate-600 font-medium" x-text="feat.key"></span>
                                    </div>
                                    <div class="inline-block mt-1 px-2 py-0.5 rounded-md border border-slate-200/90 bg-white text-[10px] font-bold text-slate-600 font-mono"
                                         x-text="getFeatureGrantedCount(feat) + ' / ' + feat.permissions.length + ' Quyền'"></div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" @click="toggleFeaturePermissions(feat, true)"
                                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white text-emerald-700 border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 transition-colors shadow-2xs cursor-pointer">
                                        Bật hết
                                    </button>
                                    <button type="button" @click="toggleFeaturePermissions(feat, false)"
                                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white text-slate-500 border border-slate-200 hover:bg-slate-100 transition-colors shadow-2xs cursor-pointer">
                                        Tắt hết
                                    </button>
                                </div>
                            </div>

                            <!-- Card Body: Permission Items List -->
                            <div class="p-3.5 space-y-2 flex-1">
                                <template x-for="perm in feat.permissions" :key="perm.id || perm.slug">
                                    <div class="flex items-center justify-between gap-2.5 p-2 rounded-xl hover:bg-slate-50/80 transition-colors">
                                        
                                        <!-- Left: Icon + Label + Subtitle -->
                                        <div class="flex items-start gap-2.5 min-w-0">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 border mt-0.5"
                                                 :class="getPermBadgeStyle(perm)">
                                                <i :class="perm.iconClass" class="text-xs"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-bold text-xs text-slate-800 leading-tight" x-text="perm.cleanName"></span>
                                                    <span x-show="hasPermission(perm.slug)"
                                                          class="text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        Đã cấp
                                                    </span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 font-medium leading-tight mt-0.5 line-clamp-2"
                                                     x-text="perm.cleanDesc"></div>
                                            </div>
                                        </div>

                                        <!-- Right: Toggle Switch -->
                                        <button type="button" @click="togglePermission(perm.slug)"
                                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                                :class="hasPermission(perm.slug) ? getPermSwitchColor(perm) : 'bg-slate-200'"
                                                role="switch" :aria-checked="hasPermission(perm.slug)">
                                            <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                                  :class="hasPermission(perm.slug) ? 'translate-x-4' : 'translate-x-0'"></span>
                                        </button>

                                    </div>
                                </template>
                            </div>

                        </div>
                    </template>
                </div>

            </div>
        </template>

    </div>

</div>

<script>
// Comprehensive Dictionary for ERP Permissions
const PERMISSION_META = {
    // 1. orders
    'orders.view': { name: 'Xem Đơn Hàng', desc: 'Tra cứu danh sách & chi tiết các đơn đặt hàng', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'orders.create': { name: 'Lên Đơn Mới', desc: 'Khởi tạo đơn hàng xuất bán cho đại lý/công trình', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'orders.edit': { name: 'Sửa Đơn Hàng', desc: 'Chỉnh sửa mặt hàng, số lượng và trần chiết khấu', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'orders.approve': { name: 'Duyệt Đơn Bán', desc: 'Kế toán/Quản lý chốt duyệt đơn đủ điều kiện xuất kho', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },
    'orders.cancel': { name: 'Hủy Đơn Hàng', desc: 'Hủy đơn hàng chưa xuất kho khi khách hủy yêu cầu', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 5 },
    'orders.status': { name: 'Đổi Trạng Thái', desc: 'Chuyển trạng thái đơn sang kho soạn, giao hàng, hoàn thành', icon: 'fa-solid fa-arrows-rotate', color: 'blue', weight: 6 },
    'orders.export': { name: 'In Phiếu & Excel', desc: 'In phiếu bán hàng, phiếu giao hàng và xuất excel', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 7 },
    'orders.delete': { name: 'Xóa Đơn Hàng', desc: 'Xóa hoàn toàn đơn hàng nháp khỏi hệ thống', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 8 },

    // 2. quotation
    'quotation.view': { name: 'Xem Báo Giá', desc: 'Tra cứu danh sách bảng báo giá đã lập', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'quotation.create': { name: 'Lập Báo Giá', desc: 'Tạo bảng báo giá sản phẩm, ống nhựa gửi đại lý', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'quotation.edit': { name: 'Sửa Báo Giá', desc: 'Điều chỉnh đơn giá và chiết khấu báo giá', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'quotation.convert': { name: 'Chuyển Đơn Bán', desc: 'Tự động tạo đơn hàng chính thức từ bảng báo giá', icon: 'fa-solid fa-cart-shopping', color: 'sky', weight: 4 },
    'quotation.approve': { name: 'Duyệt Báo Giá', desc: 'Quản lý phê duyệt mức chiết khấu đặc biệt', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 5 },
    'quotation.export': { name: 'In / Xuất Báo Giá', desc: 'In phiếu báo giá chuẩn công ty hoặc xuất PDF gửi Zalo', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 6 },
    'quotation.delete': { name: 'Hủy Báo Giá', desc: 'Hủy bỏ báo giá không còn hiệu lực', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 7 },

    // 3. returns
    'returns.view': { name: 'Xem Phiếu Trả', desc: 'Xem danh sách yêu cầu và phiếu khách hoàn trả', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'returns.create': { name: 'Tạo Yêu Cầu Trả', desc: 'Lập phiếu yêu cầu khách trả hàng thừa hoặc lỗi', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'returns.edit': { name: 'Sửa Phiếu Trả', desc: 'Điều chỉnh mặt hàng và lý do trả lại', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'returns.approve': { name: 'Duyệt Nhập Hoàn', desc: 'Xác nhận kiểm tra hàng và nhập lại tồn kho', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },
    'returns.cancel': { name: 'Hủy Yêu Cầu', desc: 'Hủy bỏ phiếu yêu cầu trả hàng', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 5 },
    'returns.delete': { name: 'Xóa Phiếu Trả', desc: 'Xóa phiếu yêu cầu khách hoàn trả khỏi hệ thống', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 6 },
    'returns.export': { name: 'Xuất Báo Cáo Trả', desc: 'In phiếu kiểm tra và xuất báo cáo hàng hoàn', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 7 },

    // 4. supplier_orders
    'supplier_orders.view': { name: 'Xem Đơn NCC', desc: 'Tra cứu đơn đặt hàng sang Nhà máy Tiền Phong', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'supplier_orders.create': { name: 'Đặt Hàng Nhà Máy', desc: 'Khởi tạo đơn đặt hàng ống nhựa, phụ kiện từ NCC', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'supplier_orders.edit': { name: 'Sửa Đơn Đặt Hàng', desc: 'Điều chỉnh số lượng cuộn/cây và đơn giá nhập', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'supplier_orders.approve': { name: 'Duyệt Đặt Hàng', desc: 'Giám đốc duyệt chi gửi đơn sản xuất cho nhà máy', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },
    'supplier_orders.receive': { name: 'Xác Nhận Nhận Hàng', desc: 'Đánh dấu nhà máy đã xuất hàng về kho Đại Sơn', icon: 'fa-solid fa-boxes-packing', color: 'blue', weight: 5 },
    'supplier_orders.export': { name: 'Xuất PO Đặt Hàng', desc: 'In hợp đồng đơn đặt hàng gửi nhà sản xuất', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 6 },
    'supplier_orders.cancel': { name: 'Hủy Đơn Đặt', desc: 'Hủy đơn hàng chưa tiến hành sản xuất', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 7 },
    'supplier_orders.delete': { name: 'Xóa Đơn Đặt', desc: 'Xóa hoàn toàn đơn đặt hàng chưa gửi nhà máy', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 8 },

    // 5. inventory
    'inventory.view': { name: 'Xem Tồn Kho', desc: 'Tra cứu số lượng tồn kho theo cây, cuộn và kho bãi', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'inventory.create': { name: 'Thêm Mặt Hàng', desc: 'Tạo mã sản phẩm, ống nhựa, phụ kiện mới', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'inventory.edit': { name: 'Sửa Thông Tin', desc: 'Chỉnh sửa đơn vị tính, quy cách ống và giá vốn', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'inventory.adjust': { name: 'Cân Bằng Tồn Kho', desc: 'Cập nhật điều chỉnh chênh lệch số dư tức thời', icon: 'fa-solid fa-scale-balanced', color: 'purple', weight: 4 },
    'inventory.export': { name: 'Xuất Báo Cáo Kho', desc: 'In thẻ kho, xuất excel tồn kho phục vụ bán hàng', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 5 },
    'inventory.delete': { name: 'Xóa Mặt Hàng', desc: 'Xóa mã hàng chưa từng phát sinh chứng từ', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 6 },

    // 6. inv_picking
    'inv_picking.view': { name: 'Xem Lệnh Bốc Hàng', desc: 'Tra cứu danh sách đơn hàng cần bốc xếp và soạn', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'inv_picking.view_all_categories': { name: 'Xem Không Phân Loại SP', desc: 'Xem bốc xếp không phân loại sản phẩm, không giới hạn theo nhóm', icon: 'fa-solid fa-layer-group', color: 'indigo', weight: 1.5 },
    'inv_picking.pick': { name: 'Bốc Hàng & Ghi Số', desc: 'Bốc ống, phụ kiện và nhập số lượng thực bốc', icon: 'fa-solid fa-boxes-packing', color: 'emerald', weight: 2 },
    'inv_picking.edit': { name: 'Sửa Lệnh Soạn', desc: 'Điều chỉnh kiện hàng, cây ống bốc thực tế', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'inv_picking.approve': { name: 'Chốt Soạn Xong', desc: 'Xác nhận hoàn tất khâu bốc hàng chuyển sang KCS', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },
    'inv_picking.cancel': { name: 'Hủy Lệnh Bốc', desc: 'Hủy bỏ lệnh bốc hàng khi đơn bị thay đổi', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 5 },
    'inv_picking.export': { name: 'In Phiếu Soạn Kho', desc: 'In phiếu nhặt hàng theo từng vị trí kệ/bãi ống', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 6 },

    // 7. inv_qc
    'inv_qc.view': { name: 'Xem Danh Sách QC', desc: 'Tra cứu các đơn hàng đã soạn xong chờ kiểm duyệt', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'inv_qc.qc': { name: 'Kiểm Hàng Thực Tế', desc: 'Đối chiếu số lượng thực tế với đơn hàng và khuyến mãi', icon: 'fa-solid fa-clipboard-check', color: 'emerald', weight: 2 },
    'inv_qc.approve': { name: 'Duyệt Đạt KCS', desc: 'Ký duyệt kiện hàng đạt chuẩn sẵn sàng bàn giao xe', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 3 },
    'inv_qc.export': { name: 'In Biên Bản KCS', desc: 'In biên bản kiểm tra chất lượng đơn hàng', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 4 },

    // 8. inv_import
    'inv_import.view': { name: 'Xem Phiếu Nhập', desc: 'Xem danh sách phiếu nhập kho nhà máy & hoàn trả', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'inv_import.create': { name: 'Tạo Phiếu Nhập', desc: 'Lập phiếu nhập kho thành phẩm ống PVC và phụ kiện', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'inv_import.edit': { name: 'Sửa Phiếu Nhập', desc: 'Điều chỉnh số lượng và đơn giá nhập kho', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'inv_import.approve': { name: 'Duyệt Nhập Kho', desc: 'Thủ kho xác nhận nhập và ghi tăng tồn kho vật lý', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },
    'inv_import.cancel': { name: 'Hủy Phiếu Nhập', desc: 'Hủy phiếu nhập kho chưa ghi sổ', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 5 },
    'inv_import.export': { name: 'In Phiếu Nhập Kho', desc: 'In phiếu nhập kho và biên bản bàn giao nhà máy', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 6 },

    // 9. inv_export
    'inv_export.view': { name: 'Xem Phiếu Xuất', desc: 'Tra cứu phiếu xuất kho bán lẻ, đại lý, điều chuyển', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'inv_export.create': { name: 'Tạo Phiếu Xuất', desc: 'Lập phiếu xuất kho hàng hóa theo đơn bán', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'inv_export.edit': { name: 'Sửa Phiếu Xuất', desc: 'Chỉnh sửa mặt hàng và số lượng xuất thực tế', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'inv_export.approve': { name: 'Duyệt Xuất Kho', desc: 'Thủ kho duyệt xuất trừ số lượng tồn kho vật lý', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },
    'inv_export.cancel': { name: 'Hủy Phiếu Xuất', desc: 'Hủy bỏ phiếu xuất kho chưa bàn giao xe', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 5 },
    'inv_export.export': { name: 'In Phiếu Xuất Kho', desc: 'In phiếu xuất kho kiêm vận chuyển nội bộ', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 6 },

    // 10. inv_transfer
    'inv_transfer.view': { name: 'Xem Điều Chuyển', desc: 'Theo dõi các lệnh chuyển kho giữa các chi nhánh', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'inv_transfer.create': { name: 'Tạo Lệnh Chuyển Kho', desc: 'Lập phiếu xuất điều chuyển hàng sang kho phụ', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'inv_transfer.approve': { name: 'Duyệt Chuyển Kho', desc: 'Xác nhận xuất kho gốc và nhập kho đích', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 3 },
    'inv_transfer.cancel': { name: 'Hủy Lệnh Chuyển', desc: 'Hủy yêu cầu điều chuyển kho chưa thực hiện', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 4 },

    // 11. inv_audit
    'inv_audit.view': { name: 'Xem Kỳ Kiểm Kê', desc: 'Xem danh sách các đợt kiểm kê định kỳ bãi ống', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'inv_audit.create': { name: 'Tạo Kỳ Kiểm Kê', desc: 'Khởi tạo đợt kiểm kê kho bãi, vật tư ống PVC', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'inv_audit.edit': { name: 'Ghi Nhận Thực Tế', desc: 'Cập nhật số đếm cây/cuộn thực tế tại kho', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'inv_audit.approve': { name: 'Chốt Biên Bản Kiểm Kê', desc: 'Kế toán trưởng phê duyệt điều chỉnh sau kiểm kê', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },
    'inv_audit.cancel': { name: 'Hủy Đợt Kiểm Kê', desc: 'Hủy đợt kiểm kê chưa hoàn tất', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 5 },
    'inv_audit.export': { name: 'In Biên Bản Kiểm Kê', desc: 'In danh sách đối chiếu thừa/thiếu kiểm kê', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 6 },

    // 12. funds
    'funds.view': { name: 'Xem Sổ Quỹ', desc: 'Tra cứu biến động số dư tiền mặt & tài khoản ngân hàng', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'funds.create_receipt': { name: 'Lập Phiếu Thu', desc: 'Thu tiền khách hàng, thu cọc, thu hồi nợ đại lý', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'funds.create_payment': { name: 'Lập Phiếu Chi', desc: 'Chi trả tiền nhà máy Tiền Phong, chi phí vận hành', icon: 'fa-solid fa-money-bill-wave', color: 'amber', weight: 3 },
    'funds.approve': { name: 'Duyệt Thu Chi', desc: 'Kế toán trưởng/Giám đốc duyệt phiếu chi tiền', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },
    'funds.cancel': { name: 'Hủy Phiếu Thu Chi', desc: 'Hủy chứng từ thu chi lập sai chưa hạch toán', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 5 },
    'funds.export': { name: 'Xuất Sổ Nhật Ký', desc: 'In sổ quỹ tiền mặt, sao kê chi tiết tài khoản', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 6 },

    // 13. driver_debt
    'driver_debt.view': { name: 'Xem Tiền Thu COD', desc: 'Theo dõi tiền hàng lái xe thu hộ từ các đại lý', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'driver_debt.settle': { name: 'Quyết Toán Tiền Mặt', desc: 'Xác nhận thu tiền mặt lái xe bàn giao về quỹ', icon: 'fa-solid fa-hand-holding-dollar', color: 'emerald', weight: 2 },
    'driver_debt.edit': { name: 'Điều Chỉnh Tiền Thu', desc: 'Xử lý chênh lệch thanh toán hoặc đổi phương thức CK', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'driver_debt.export': { name: 'In Bảng Kê Quyết Toán', desc: 'In phiếu bàn giao và quyết toán COD chuyến xe', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 4 },

    // 14. fleet
    'fleet.view': { name: 'Xem Đội Xe & Chi Phí', desc: 'Tra cứu danh sách xe tải, nhiên liệu và bảo dưỡng', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'fleet.create': { name: 'Thêm Xe / Chi Phí', desc: 'Thêm phương tiện vận tải hoặc ghi nhận chi phí dầu/cầu đường', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'fleet.edit': { name: 'Sửa Hồ Sơ Xe', desc: 'Cập nhật hạn đăng kiểm, định mức tiêu hao nhiên liệu', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'fleet.approve': { name: 'Duyệt Quyết Toán Chi Phí', desc: 'Phê duyệt chi phí nhiên liệu & sửa chữa xe', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },

    // 15. dealers
    'dealers.view': { name: 'Xem Hồ Sơ Đại Lý', desc: 'Tra cứu danh sách đại lý, cấp bậc, địa chỉ giao hàng', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'dealers.create': { name: 'Thêm Đại Lý Mới', desc: 'Tạo mới hồ sơ khách hàng công trình & đại lý', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'dealers.edit': { name: 'Sửa Thông Tin', desc: 'Cập nhật địa chỉ nhận hàng, số điện thoại, tuyến đường', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'dealers.delete': { name: 'Xóa Đại Lý', desc: 'Xóa hồ sơ đối tác chưa từng phát sinh đơn hàng', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 4 },

    // 16. debt
    'debt.view': { name: 'Xem Công Nợ Đại Lý', desc: 'Tra cứu số nợ hiện tại, nợ trong hạn & nợ quá hạn', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'debt.adjust': { name: 'Chỉnh Hạn Mức Nợ', desc: 'Nâng/hạ hạn mức nợ và thời gian gối đầu cho đại lý', icon: 'fa-solid fa-sliders', color: 'amber', weight: 2 },
    'debt.approve': { name: 'Duyệt Vượt Hạn Mức', desc: 'Bảo lãnh xuất hàng khi đại lý đang chạm trần công nợ', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 3 },
    'debt.export': { name: 'Xuất Đối Soát Công Nợ', desc: 'In biên bản đối soát công nợ gửi khách ký xác nhận', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 4 },

    // 17. sales_policy
    'sales_policy.view': { name: 'Xem Chính Sách Bán', desc: 'Xem bảng giá chiết khấu ống PVC và chính sách tặng kèm', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'sales_policy.create': { name: 'Tạo Chính Sách Mới', desc: 'Thiết lập bảng chiết khấu, khuyến mãi bậc thang', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'sales_policy.edit': { name: 'Sửa Chính Sách', desc: 'Điều chỉnh tỷ lệ % chiết khấu hoặc số lượng tặng', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'sales_policy.approve': { name: 'Ban Hành Chính Sách', desc: 'Giám đốc phê duyệt áp dụng chính sách giá toàn hệ thống', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },

    // 18. dealer_care
    'dealer_care.view': { name: 'Xem Nhật Ký Chăm Sóc', desc: 'Xem lịch trình viếng thăm và chăm sóc đại lý của Sales', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'dealer_care.create': { name: 'Ghi Nhật Ký Viếng Thăm', desc: 'Nhân viên kinh doanh check-in GPS và ghi báo cáo thị trường', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'dealer_care.approve': { name: 'Duyệt Báo Cáo Thị Trường', desc: 'Quản lý kinh doanh duyệt lộ trình và đề xuất hỗ trợ đại lý', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 3 },

    // 19. delivery_dispatch
    'delivery_dispatch.view': { name: 'Xem Điều Phối Xe', desc: 'Tra cứu lệnh điều xe giao hàng và trạng thái lộ trình', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'delivery_dispatch.create': { name: 'Tạo Lệnh Giao Hàng', desc: 'Gộp các đơn hàng cùng tuyến vào một chuyến xe tải', icon: 'fa-solid fa-truck-fast', color: 'emerald', weight: 2 },
    'delivery_dispatch.approve': { name: 'Duyệt Xe Xuất Bến', desc: 'Điều phối viên duyệt lệnh cho xe lăn bánh rời kho', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 3 },
    'delivery_dispatch.cancel': { name: 'Hủy Chuyến Giao', desc: 'Hủy chuyến xe khi có thay đổi đột xuất', icon: 'fa-regular fa-trash-can', color: 'rose', weight: 4 },

    // 20. staff_list
    'staff_list.view': { name: 'Xem Hồ Sơ Nhân Sự', desc: 'Tra cứu danh sách cán bộ công nhân viên công ty', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'staff_list.create': { name: 'Thêm Nhân Viên', desc: 'Tạo tài khoản và hồ sơ nhân viên mới', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'staff_list.edit': { name: 'Sửa Thông Tin', desc: 'Cập nhật phòng ban, chức vụ, mức lương cơ bản', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'staff_list.lock': { name: 'Khóa / Thôi Việc', desc: 'Khóa tài khoản truy cập khi nhân sự nghỉ việc', icon: 'fa-solid fa-user-slash', color: 'rose', weight: 4 },

    // 21. attendance
    'attendance.view': { name: 'Xem Bảng Chấm Công', desc: 'Theo dõi giờ vào/ra ca và lịch sử định vị GPS chấm công', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'attendance.checkin': { name: 'Chấm Công GPS', desc: 'Thực hiện điểm danh ca làm việc qua điện thoại', icon: 'fa-solid fa-location-dot', color: 'emerald', weight: 2 },
    'attendance.edit': { name: 'Sửa Công / Giải Trình', desc: 'Bổ sung công quên chấm hoặc giải trình đi muộn/về sớm', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'attendance.approve': { name: 'Chốt Công Tháng', desc: 'Quản lý duyệt chốt số công thực tế tính lương', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 4 },

    // 22. payroll
    'payroll.view': { name: 'Xem Bảng Lương', desc: 'Tra cứu phiếu lương cá nhân và bảng lương phòng ban', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'payroll.create': { name: 'Tính Lương Tự Động', desc: 'Chạy bảng tính lương theo số công, phụ cấp và KPI', icon: 'fa-solid fa-calculator', color: 'emerald', weight: 2 },
    'payroll.approve': { name: 'Duyệt Chi Trả Lương', desc: 'Ban Giám Đốc phê duyệt lệnh thanh toán chuyển khoản', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 3 },
    'payroll.export': { name: 'In Phiếu & Gửi Lương', desc: 'Xuất file ủy nhiệm chi ngân hàng và gửi phiếu lương Zalo', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 4 },

    // 23. kpi
    'kpi.view': { name: 'Xem Đánh Giá KPI', desc: 'Theo dõi điểm số hiệu suất công việc cá nhân/phòng ban', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'kpi.evaluate': { name: 'Chấm Điểm Đánh Giá', desc: 'Trưởng bộ phận chấm điểm tiến độ và thái độ làm việc', icon: 'fa-solid fa-award', color: 'emerald', weight: 2 },
    'kpi.approve': { name: 'Duyệt Kết Quả KPI', desc: 'Phê duyệt hệ số KPI áp dụng vào thưởng tháng/quý', icon: 'fa-solid fa-shield-halved', color: 'purple', weight: 3 },

    // 24. permissions
    'permissions.view': { name: 'Xem Ma Trận Quyền', desc: 'Tra cứu bảng phân quyền vai trò và tài khoản', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'permissions.manage_role': { name: 'Cấu Hình Vai Trò', desc: 'Thiết lập bật/tắt quyền hạn theo từng phân hệ chức năng', icon: 'fa-solid fa-user-shield', color: 'purple', weight: 2 },
    'permissions.manage_user': { name: 'Gán Quyền Cá Nhân', desc: 'Tùy biến quyền đặc biệt riêng cho từng nhân viên', icon: 'fa-solid fa-user-gear', color: 'amber', weight: 3 },

    // 25. task_assignment
    'task_assignment.view': { name: 'Xem Danh Sách Việc', desc: 'Tra cứu công việc cá nhân và tiến độ phòng ban', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'task_assignment.create': { name: 'Giao Việc Mới', desc: 'Phân công nhiệm vụ, gắn deadline và người thực hiện', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'task_assignment.edit': { name: 'Cập Nhật Tiến Độ', desc: 'Báo cáo phần trăm hoàn thành và đính kèm kết quả', icon: 'fa-solid fa-pen', color: 'amber', weight: 3 },
    'task_assignment.approve': { name: 'Nghiệm Thu Công Việc', desc: 'Người giao việc xác nhận hoàn thành đạt yêu cầu', icon: 'fa-solid fa-check-double', color: 'purple', weight: 4 },

    // 26. proposals
    'proposals.view': { name: 'Xem Đề Xuất Trình Ký', desc: 'Tra cứu các tờ trình, đề xuất mua sắm, xin nghỉ phép', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'proposals.create': { name: 'Tạo Đề Xuất Mới', desc: 'Lập đơn xin nghỉ, đề xuất mua vật tư hoặc chi phí phát sinh', icon: 'fa-solid fa-plus', color: 'emerald', weight: 2 },
    'proposals.approve': { name: 'Ký Duyệt Đề Xuất', desc: 'Cấp quản lý ký duyệt điện tử tờ trình', icon: 'fa-solid fa-signature', color: 'purple', weight: 3 },

    // 27. workflow
    'workflow.view': { name: 'Xem Sơ Đồ Quy Trình', desc: 'Theo dõi sơ đồ luân chuyển chứng từ giữa các bộ phận', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'workflow.edit': { name: 'Cấu Hình Bước Duyệt', desc: 'Thiết lập thứ tự duyệt qua trưởng phòng đến ban giám đốc', icon: 'fa-solid fa-diagram-project', color: 'amber', weight: 2 },

    // 28. dashboard
    'dashboard.view': { name: 'Xem Tổng Quan', desc: 'Theo dõi biểu đồ doanh thu, tồn kho và chỉ số tài chính', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'dashboard.export': { name: 'Xuất Báo Cáo Điều Hành', desc: 'Tải báo cáo tổng kết kinh doanh PDF/Excel', icon: 'fa-regular fa-file-lines', color: 'teal', weight: 2 },

    // 29. audit
    'audit.view': { name: 'Xem Nhật Ký Kiểm Toán', desc: 'Tra cứu lịch sử đăng nhập, tạo, sửa, xóa dữ liệu', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'audit.lock_period': { name: 'Khóa Sổ Kế Toán', desc: 'Đóng kỳ kế toán không cho phép sửa đổi số liệu quá khứ', icon: 'fa-solid fa-lock', color: 'rose', weight: 2 },

    // 30. danhmuc
    'danhmuc.view': { name: 'Xem Danh Mục', desc: 'Tra cứu đơn vị tính, nhóm sản phẩm, tài khoản ngân hàng', icon: 'fa-regular fa-eye', color: 'sky', weight: 1 },
    'danhmuc.edit': { name: 'Cập Nhật Danh Mục', desc: 'Thêm, sửa các thông số cấu hình dùng chung toàn hệ thống', icon: 'fa-solid fa-pen', color: 'amber', weight: 2 }
};

function rolesApp() {
    return {
        // Raw data from Laravel Blade
        rawRoles: @json($roles ?? []),
        rawPermissions: @json($permissions ?? []),
        usersList: @json($users ?? []),

        rolesData: [],
        selectedRoleSlug: 'Manager',
        filterSubsystem: 'all',
        searchKeyword: '',
        saving: false,

        // Map role slug to array of permission slugs
        rolePermissionsMap: {},

        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        initApp() {
            // Transform roles and initialize permissions map
            this.rolesData = this.rawRoles.map(r => ({
                id: r.id,
                name: r.name,
                slug: r.slug,
                description: r.description
            }));

            this.rawRoles.forEach(r => {
                const perms = (r.permissions || []).map(p => p.slug);
                this.rolePermissionsMap[r.slug] = perms;
            });

            if (this.rolesData.length > 0) {
                this.selectedRoleSlug = this.rolesData[0].slug;
            }
        },

        get currentRole() {
            return this.rolesData.find(r => r.slug === this.selectedRoleSlug) || null;
        },

        get currentRolePermissions() {
            return this.rolePermissionsMap[this.selectedRoleSlug] || [];
        },

        get currentRoleGrantedPercent() {
            return this.getRoleGrantedPercent(this.selectedRoleSlug);
        },

        selectRole(r) {
            this.selectedRoleSlug = r.slug;
        },

        hasPermission(slug) {
            const list = this.rolePermissionsMap[this.selectedRoleSlug] || [];
            return list.includes(slug);
        },

        togglePermission(slug) {
            let list = [...(this.rolePermissionsMap[this.selectedRoleSlug] || [])];
            if (list.includes(slug)) {
                list = list.filter(s => s !== slug);
            } else {
                list.push(slug);
            }
            this.rolePermissionsMap[this.selectedRoleSlug] = list;
        },

        toggleFeaturePermissions(feat, state) {
            let list = [...(this.rolePermissionsMap[this.selectedRoleSlug] || [])];
            const slugs = feat.permissions.map(p => p.slug);
            if (state) {
                slugs.forEach(s => {
                    if (!list.includes(s)) list.push(s);
                });
            } else {
                list = list.filter(s => !slugs.includes(s));
            }
            this.rolePermissionsMap[this.selectedRoleSlug] = list;
        },

        toggleSubsystemPermissions(sub, state) {
            let list = [...(this.rolePermissionsMap[this.selectedRoleSlug] || [])];
            const allSlugs = [];
            sub.features.forEach(f => {
                f.permissions.forEach(p => allSlugs.push(p.slug));
            });

            if (state) {
                allSlugs.forEach(s => {
                    if (!list.includes(s)) list.push(s);
                });
            } else {
                list = list.filter(s => !allSlugs.includes(s));
            }
            this.rolePermissionsMap[this.selectedRoleSlug] = list;
        },

        toggleAllSubsystems(state) {
            if (state) {
                const all = this.rawPermissions.map(p => p.slug);
                this.rolePermissionsMap[this.selectedRoleSlug] = all;
            } else {
                this.rolePermissionsMap[this.selectedRoleSlug] = [];
            }
        },

        // Save to Database via API
        async savePermissions() {
            this.saving = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const role = this.selectedRoleSlug;
                const permissions = this.rolePermissionsMap[role] || [];

                const res = await fetch('{{ url('/roles/save-permissions') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        role: role,
                        permissions: permissions
                    })
                });

                const data = await res.json();
                if (data.success || res.ok) {
                    this.showToast(`Đã lưu cấu hình ma trận phân quyền cho vai trò [${this.currentRole?.name}] thành công!`);
                } else {
                    this.showToast(data.message || 'Lỗi khi lưu phân quyền', 'error');
                }
            } catch (e) {
                this.showToast('Lỗi kết nối máy chủ', 'error');
            } finally {
                this.saving = false;
            }
        },

        // Counts & Stats
        get totalPermissionsCount() {
            return this.rawPermissions.length;
        },

        get currentRoleGrantedCount() {
            return (this.rolePermissionsMap[this.selectedRoleSlug] || []).length;
        },

        getRoleGrantedCount(roleSlug) {
            return (this.rolePermissionsMap[roleSlug] || []).length;
        },

        getRoleGrantedPercent(roleSlug) {
            if (this.totalPermissionsCount === 0) return 0;
            const count = this.getRoleGrantedCount(roleSlug);
            return Math.round((count / this.totalPermissionsCount) * 100);
        },

        getStaffCountForRole(roleSlug) {
            return this.usersList.filter(u => (u.department || '').toLowerCase().includes(roleSlug.toLowerCase())).length || 2;
        },

        getFeatureGrantedCount(feat) {
            const list = this.rolePermissionsMap[this.selectedRoleSlug] || [];
            return feat.permissions.filter(p => list.includes(p.slug)).length;
        },

        getSubsystemGrantedCount(sub) {
            const list = this.rolePermissionsMap[this.selectedRoleSlug] || [];
            let c = 0;
            sub.features.forEach(f => {
                c += f.permissions.filter(p => list.includes(p.slug)).length;
            });
            return c;
        },

        getSubsystemTotalCount(sub) {
            let c = 0;
            sub.features.forEach(f => {
                c += f.permissions.length;
            });
            return c;
        },

        // Filter / Search
        get filteredSubsystems() {
            let list = this.subsystems;
            if (this.filterSubsystem !== 'all') {
                list = list.filter(s => s.id === this.filterSubsystem);
            }
            if (this.searchKeyword) {
                const kw = this.searchKeyword.toLowerCase();
                list = list.filter(sub => {
                    const matchSub = sub.name.toLowerCase().includes(kw);
                    const matchFeat = sub.features.some(f => 
                        f.name.toLowerCase().includes(kw) || 
                        f.permissions.some(p => p.cleanName.toLowerCase().includes(kw) || p.cleanDesc.toLowerCase().includes(kw))
                    );
                    return matchSub || matchFeat;
                });
            }
            return list;
        },

        isFeatureMatching(feat) {
            if (!this.searchKeyword) return true;
            const kw = this.searchKeyword.toLowerCase();
            return feat.name.toLowerCase().includes(kw) || 
                   feat.permissions.some(p => p.cleanName.toLowerCase().includes(kw) || p.cleanDesc.toLowerCase().includes(kw));
        },

        // Visual helpers
        getPermBadgeStyle(perm) {
            const c = perm.colorTheme || 'sky';
            switch(c) {
                case 'sky': return 'bg-sky-50 text-sky-600 border-sky-200';
                case 'emerald': return 'bg-emerald-50 text-emerald-600 border-emerald-200';
                case 'amber': return 'bg-amber-50 text-amber-600 border-amber-200';
                case 'purple': return 'bg-purple-50 text-purple-600 border-purple-200';
                case 'teal': return 'bg-teal-50 text-teal-600 border-teal-200';
                case 'blue': return 'bg-blue-50 text-blue-600 border-blue-200';
                case 'rose': return 'bg-rose-50 text-rose-500 border-rose-200';
                default: return 'bg-slate-50 text-slate-500 border-slate-200';
            }
        },

        getPermSwitchColor(perm) {
            const c = perm.colorTheme || 'sky';
            switch(c) {
                case 'sky': return 'bg-[#0284c7]';
                case 'emerald': return 'bg-[#10b981]';
                case 'amber': return 'bg-[#f59e0b]';
                case 'purple': return 'bg-[#9333ea]';
                case 'teal': return 'bg-[#0d9488]';
                case 'blue': return 'bg-[#2563eb]';
                case 'rose': return 'bg-[#f43f5e]';
                default: return 'bg-[#0284c7]';
            }
        },

        // Helper to enrich and sort permissions within a module
        enrichAndSortPermissions(mod) {
            const perms = this.rawPermissions.filter(p => p.module === mod);
            return perms.map(p => {
                const meta = PERMISSION_META[p.slug];
                if (meta) {
                    return {
                        ...p,
                        cleanName: meta.name,
                        cleanDesc: meta.desc,
                        iconClass: meta.icon,
                        colorTheme: meta.color,
                        weight: meta.weight || 10
                    };
                }
                
                // Intelligent fallback for any permissions not in map
                let cleanName = p.name || '';
                let cleanDesc = p.description || '';
                let iconClass = 'fa-regular fa-circle-check';
                let colorTheme = 'sky';
                let weight = 15;

                if (p.action === 'view') {
                    cleanName = 'Xem Danh Sách & Chi Tiết';
                    cleanDesc = 'Tra cứu danh sách và xem chi tiết dữ liệu';
                    iconClass = 'fa-regular fa-eye';
                    colorTheme = 'sky';
                    weight = 1;
                } else if (p.action === 'create') {
                    cleanName = 'Thêm Mới / Khởi Tạo';
                    cleanDesc = 'Tạo mới bản ghi và dữ liệu nghiệp vụ';
                    iconClass = 'fa-solid fa-plus';
                    colorTheme = 'emerald';
                    weight = 2;
                } else if (p.action === 'edit' || p.action === 'adjust' || p.action === 'update_price') {
                    cleanName = 'Chỉnh Sửa Thông Tin';
                    cleanDesc = 'Điều chỉnh và cập nhật thông tin chi tiết';
                    iconClass = 'fa-solid fa-pen';
                    colorTheme = 'amber';
                    weight = 3;
                } else if (p.action === 'approve') {
                    cleanName = 'Phê Duyệt Nghiệp Vụ';
                    cleanDesc = 'Xác nhận kiểm duyệt chứng từ / yêu cầu';
                    iconClass = 'fa-solid fa-shield-halved';
                    colorTheme = 'purple';
                    weight = 4;
                } else if (p.action === 'export') {
                    cleanName = 'In Ấn & Xuất Excel';
                    cleanDesc = 'In phiếu chứng từ và trích xuất dữ liệu Excel';
                    iconClass = 'fa-regular fa-file-lines';
                    colorTheme = 'teal';
                    weight = 6;
                } else if (p.action === 'delete') {
                    cleanName = 'Xóa Dữ Liệu';
                    cleanDesc = 'Xóa vĩnh viễn bản ghi khỏi hệ thống khi chưa phát sinh';
                    iconClass = 'fa-regular fa-trash-can';
                    colorTheme = 'rose';
                    weight = 8;
                } else if (p.action === 'cancel') {
                    cleanName = 'Hủy Bỏ';
                    cleanDesc = 'Hủy bỏ chứng từ / giao dịch đang xử lý';
                    iconClass = 'fa-regular fa-trash-can';
                    colorTheme = 'rose';
                    weight = 7;
                }

                if (p.name && !p.name.includes(' - ')) {
                    cleanName = p.name.replace(/^Quyền /, '');
                }

                return {
                    ...p,
                    cleanName: cleanName || p.slug,
                    cleanDesc: cleanDesc,
                    iconClass: iconClass,
                    colorTheme: colorTheme,
                    weight: weight
                };
            }).sort((a, b) => (a.weight || 99) - (b.weight || 99));
        },

        // Subsystems and Features Definition (Structured hierarchically by business logic)
        get subsystems() {
            return [
                {
                    id: 'sales',
                    name: 'Phân Hệ Quản Lý Bán Hàng & Đơn Hàng',
                    desc: 'Báo giá sản phẩm, tiếp nhận đơn đặt hàng, xuất kho bán và quản lý hàng đổi trả',
                    icon: 'fa-solid fa-cart-shopping',
                    features: [
                        { key: 'orders', name: 'Quản lý đơn hàng & Xuất kho', permissions: this.enrichAndSortPermissions('orders') },
                        { key: 'quotation', name: 'Báo giá sản phẩm gửi khách', permissions: this.enrichAndSortPermissions('quotation') },
                        { key: 'returns', name: 'Khách trả hàng (Nhập lại kho)', permissions: this.enrichAndSortPermissions('returns') },
                        { key: 'supplier_orders', name: 'Đặt hàng Nhà máy / NCC Tiền Phong', permissions: this.enrichAndSortPermissions('supplier_orders') }
                    ]
                },
                {
                    id: 'inventory',
                    name: 'Phân Hệ Quản Trị Kho Bãi & Bốc Xếp',
                    desc: 'Kiểm soát tồn kho ống nhựa PVC, nhập xuất vật tư, bốc xếp và kiểm duyệt QC',
                    icon: 'fa-solid fa-boxes-stacked',
                    features: [
                        { key: 'inventory', name: 'Danh mục & Tồn kho sản phẩm', permissions: this.enrichAndSortPermissions('inventory') },
                        { key: 'inv_picking', name: 'Soạn đơn & Bốc hàng xuất kho', permissions: this.enrichAndSortPermissions('inv_picking') },
                        { key: 'inv_qc', name: 'Kiểm hàng QC & KCS xuất kho', permissions: this.enrichAndSortPermissions('inv_qc') },
                        { key: 'inv_import', name: 'Nhập kho (Nhà máy & Hoàn hàng)', permissions: this.enrichAndSortPermissions('inv_import') },
                        { key: 'inv_export', name: 'Xuất kho (Bán hàng & Điều chuyển)', permissions: this.enrichAndSortPermissions('inv_export') },
                        { key: 'inv_transfer', name: 'Điều chuyển kho chi nhánh', permissions: this.enrichAndSortPermissions('inv_transfer') },
                        { key: 'inv_audit', name: 'Kiểm kê & Điều chỉnh tồn kho', permissions: this.enrichAndSortPermissions('inv_audit') }
                    ]
                },
                {
                    id: 'finance',
                    name: 'Phân Hệ Tài Chính, Sổ Quỹ & Dòng Tiền',
                    desc: 'Thu chi tiền mặt, tài khoản ngân hàng VietQR, sổ quỹ và quyết toán lái xe',
                    icon: 'fa-solid fa-wallet',
                    features: [
                        { key: 'funds', name: 'Quản lý sổ quỹ & Thu chi tiền mặt/NH', permissions: this.enrichAndSortPermissions('funds') },
                        { key: 'driver_debt', name: 'Bàn giao tiền mặt COD lái xe', permissions: this.enrichAndSortPermissions('driver_debt') },
                        { key: 'fleet', name: 'Chi phí & Định mức đội xe', permissions: this.enrichAndSortPermissions('fleet') }
                    ]
                },
                {
                    id: 'dealers',
                    name: 'Phân Hệ Khách Hàng, Đại Lý & Công Nợ',
                    desc: 'Hồ sơ đại lý, hạn mức công nợ, định tuyến giao hàng và chính sách chiết khấu',
                    icon: 'fa-solid fa-users-gear',
                    features: [
                        { key: 'dealers', name: 'Hồ sơ đại lý & Khách hàng dự án', permissions: this.enrichAndSortPermissions('dealers') },
                        { key: 'debt', name: 'Hạn mức nợ & Theo dõi quá hạn', permissions: this.enrichAndSortPermissions('debt') },
                        { key: 'sales_policy', name: 'Chính sách giá & Chiết khấu KM', permissions: this.enrichAndSortPermissions('sales_policy') },
                        { key: 'dealer_care', name: 'Chăm sóc & Nhật ký đại lý GPS', permissions: this.enrichAndSortPermissions('dealer_care') }
                    ]
                },
                {
                    id: 'logistics',
                    name: 'Phân Hệ Điều Phối Vận Tải & Đội Xe',
                    desc: 'Điều phối chuyến xe, xếp lịch giao hàng, quản lý phương tiện và theo dõi lộ trình',
                    icon: 'fa-solid fa-truck-fast',
                    features: [
                        { key: 'delivery_dispatch', name: 'Lệnh điều xe & Phân bổ đơn hàng', permissions: this.enrichAndSortPermissions('delivery_dispatch') },
                        { key: 'fleet', name: 'Hồ sơ phương tiện & Xe giao', permissions: this.enrichAndSortPermissions('fleet') }
                    ]
                },
                {
                    id: 'hrm',
                    name: 'Phân Hệ Nhân Sự, Chấm Công & Tiền Lương',
                    desc: 'Hồ sơ nhân viên, chấm công định vị GPS, tính lương tự động và đánh giá KPI',
                    icon: 'fa-solid fa-id-card-clip',
                    features: [
                        { key: 'staff_list', name: 'Danh sách & Hồ sơ nhân sự', permissions: this.enrichAndSortPermissions('staff_list') },
                        { key: 'attendance', name: 'Chấm công GPS vào/ra ca', permissions: this.enrichAndSortPermissions('attendance') },
                        { key: 'payroll', name: 'Bảng lương & Tính lương tự động', permissions: this.enrichAndSortPermissions('payroll') },
                        { key: 'kpi', name: 'Đánh giá hiệu suất KPI phòng ban', permissions: this.enrichAndSortPermissions('kpi') },
                        { key: 'permissions', name: 'Phân quyền vai trò hệ thống', permissions: this.enrichAndSortPermissions('permissions') }
                    ]
                },
                {
                    id: 'workflow',
                    name: 'Phân Hệ Công Việc & Trình Ký Đề Xuất',
                    desc: 'Giao việc, giám sát tiến độ công việc và quy trình trình ký duyệt điện tử',
                    icon: 'fa-solid fa-list-check',
                    features: [
                        { key: 'task_assignment', name: 'Quản lý & Giao việc nhân viên', permissions: this.enrichAndSortPermissions('task_assignment') },
                        { key: 'proposals', name: 'Trình ký & Phê duyệt đề xuất', permissions: this.enrichAndSortPermissions('proposals') },
                        { key: 'workflow', name: 'Quy trình làm việc liên phòng ban', permissions: this.enrichAndSortPermissions('workflow') }
                    ]
                },
                {
                    id: 'system',
                    name: 'Phân Hệ Quản Trị Hệ Thống & Cấu Hình',
                    desc: 'Tổng quan điều hành, nhật ký kiểm toán Audit Log và danh mục dùng chung',
                    icon: 'fa-solid fa-gears',
                    features: [
                        { key: 'dashboard', name: 'Bàn làm việc & Báo cáo tổng quan', permissions: this.enrichAndSortPermissions('dashboard') },
                        { key: 'audit', name: 'Nhật ký kiểm toán & Khóa kỳ kế toán', permissions: this.enrichAndSortPermissions('audit') },
                        { key: 'danhmuc', name: 'Danh mục dùng chung hệ thống', permissions: this.enrichAndSortPermissions('danhmuc') }
                    ]
                }
            ];
        }
    };
}
</script>
@endsection
