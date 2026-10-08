@php $authUser = Auth::user(); @endphp
@extends('layouts.app')

@section('title', 'Danh Sách Cán Bộ Nhân Sự & Phân Quyền | ERP Đại Sơn')

@section('content')
<div x-data="staffApp()" x-init="initApp()" class="w-full space-y-4 pb-16">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-[9999] px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold border"
         :class="toast.type === 'success' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-rose-600 text-white border-rose-500'">
        <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check text-base' : 'fa-solid fa-circle-exclamation text-base'"></i>
        <span x-text="toast.message"></span>
    </div>

    <!-- 1. TOP SEARCH & ACTION BAR -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Search Input -->
        <div class="relative flex-1 max-w-xl">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            <input type="text" x-model="searchKeyword"
                   placeholder="Tìm theo Mã NV, Tên nhân viên, Số điện thoại, Email..."
                   class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 font-medium text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:border-sky-500 transition-all placeholder:text-slate-400 shadow-2xs">
        </div>

        <!-- Right Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            
            <!-- View Mode Switcher -->
            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/80 shadow-2xs text-xs">
                <button type="button" @click="viewMode = 'grid'"
                        class="px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-all font-bold cursor-pointer"
                        :class="viewMode === 'grid' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60 font-black' : 'text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-grip text-xs"></i>
                    <span>Dạng Thẻ</span>
                </button>
                <button type="button" @click="viewMode = 'table'"
                        class="px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-all font-bold cursor-pointer"
                        :class="viewMode === 'table' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/60 font-black' : 'text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-table-list text-xs"></i>
                    <span>Bảng Chi Tiết</span>
                </button>
            </div>

            <!-- Phân Quyền Button -->
            <a href="{{ url('/roles') }}" x-show="canManagePerm"
               class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-xs font-bold flex items-center gap-1.5 transition-all shadow-2xs">
                <i class="fa-solid fa-shield-halved text-purple-600 text-xs"></i>
                <span>Phân Quyền</span>
            </a>

            <!-- Thêm Mới Nhân Sự Button -->
            <a href="{{ url('/staff/create') }}" x-show="canCreate"
               class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-black shadow-md shadow-sky-600/20 flex items-center gap-2 cursor-pointer transition-all">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>Thêm Mới Nhân Sự</span>
            </a>

        </div>

    </div>

    <!-- 2. FILTER STRIP -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 px-4 py-3 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
        
        <!-- Filter Dropdowns -->
        <div class="flex flex-wrap items-center gap-3">
            <span class="font-bold text-slate-500 flex items-center gap-1.5 text-xs">
                <i class="fa-solid fa-filter text-slate-400"></i>
                <span>Lọc theo:</span>
            </span>

            <!-- Filter Phòng Ban -->
            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 shadow-2xs">
                <span class="text-slate-400 font-medium text-[11px]">Phòng ban:</span>
                <select x-model="filterDepartment" class="bg-transparent font-bold text-slate-800 focus:outline-none cursor-pointer text-xs pr-1">
                    <option value="all">Tất cả</option>
                    <option value="Executive">Ban Giám Đốc / Điều Hành</option>
                    <option value="Accounting">Kế Toán & Tài Chính</option>
                    <option value="Sales">Kinh Doanh & Bán Hàng</option>
                    <option value="Warehouse">Kho Vận & Vật Tư</option>
                    <option value="Logistics">Vận Tải & Đội Xe</option>
                </select>
            </div>

            <!-- Filter Chức Danh / Vai Trò -->
            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 shadow-2xs">
                <span class="text-slate-400 font-medium text-[11px]">Chức danh / Vai trò:</span>
                <select x-model="filterRole" class="bg-transparent font-bold text-slate-800 focus:outline-none cursor-pointer text-xs pr-1">
                    <option value="all">Tất cả</option>
                    <option value="Manager">Ban Giám Đốc / Quản Lý</option>
                    <option value="Sales_Accountant">Kế Toán Bán Hàng</option>
                    <option value="Sales_Rep">NV Kinh Doanh (Sales)</option>
                    <option value="Warehouse_Manager">Quản Lý Kho / Thủ Kho</option>
                    <option value="Warehouse_Staff">Nhân Viên Kho / Bốc Xếp</option>
                    <option value="Driver">Lái Xe / Vận Tải</option>
                    <option value="Finance_Accountant">Kế Toán Tài Chính / Thủ Quỹ</option>
                </select>
            </div>

            <!-- Filter Trạng Thái -->
            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 shadow-2xs">
                <span class="text-slate-400 font-medium text-[11px]">Trạng thái:</span>
                <select x-model="filterStatus" class="bg-transparent font-bold text-slate-800 focus:outline-none cursor-pointer text-xs pr-1">
                    <option value="all">Tất cả</option>
                    <option value="Đang làm việc">Đang làm việc</option>
                    <option value="Tạm nghỉ">Tạm nghỉ</option>
                    <option value="Đã nghỉ việc">Đã nghỉ việc</option>
                </select>
            </div>
        </div>

        <!-- Counter Indicator -->
        <div class="text-xs text-slate-500 font-medium text-right shrink-0">
            <span>Hiển thị</span>
            <strong class="font-black text-slate-900" x-text="filteredUsers.length"></strong>
            <span>/</span>
            <span x-text="usersList.length + ' cán bộ nhân viên'"></span>
        </div>

    </div>

    <!-- 3. VIEW MODE: DẠNG THẺ (CARD GRID) -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <template x-for="user in filteredUsers" :key="user.id">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all p-4 sm:p-5 flex flex-col justify-between group space-y-4">
                
                <!-- Card Header -->
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <template x-if="user.avatar">
                                    <img :src="user.avatar" class="w-12 h-12 rounded-full object-cover shadow-2xs border border-slate-200">
                                </template>
                                <template x-if="!user.avatar">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-sm text-white shadow-2xs"
                                         :class="getAvatarBgClass(user)">
                                        <span x-text="getInitials(user.name)"></span>
                                    </div>
                                </template>
                                <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white"
                                      :class="user.status === 'Đang làm việc' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                            </div>

                            <!-- Name & Code & Department -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 border border-slate-200/80"
                                          x-text="user.code || ('NV - ' + user.id)"></span>
                                    
                                    <span class="text-[10px] font-bold px-2 py-0.2 rounded-md"
                                          :class="getDepartmentBadgeClass(user.department)"
                                          x-text="getDepartmentLabel(user.department)"></span>
                                </div>
                                <h3 class="font-black text-sm sm:text-base text-slate-900 tracking-tight mt-1 truncate"
                                    :title="user.name" x-text="user.name"></h3>
                            </div>
                        </div>

                        <!-- Edit Button (Pencil Icon) -->
                        <a :href="'{{ url('/staff') }}/' + user.id + '/edit'" x-show="canEdit"
                           class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-sky-600 flex items-center justify-center transition-colors cursor-pointer shrink-0"
                           title="Chỉnh sửa hồ sơ & phân quyền">
                            <i class="fa-solid fa-pencil text-xs"></i>
                        </a>
                    </div>

                    <!-- Role Badge Banner (Under Header) -->
                    <div class="mt-3.5 px-3 py-1.5 rounded-xl border text-xs font-bold flex items-center justify-between gap-2"
                         :class="getRoleBannerClass(user)">
                        <div class="flex items-center gap-2">
                            <i :class="getRoleIcon(user)" class="text-xs"></i>
                            <span x-text="getRoleLabel(user)"></span>
                        </div>
                        <span class="text-[10px] font-medium opacity-80" x-text="user.branch || 'Trụ sở chính'"></span>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                    <!-- Xem hồ sơ -->
                    <button type="button" @click="openProfileModal(user)"
                            class="text-sky-600 hover:text-sky-800 font-bold flex items-center gap-1.5 cursor-pointer transition-colors">
                        <i class="fa-regular fa-eye text-xs"></i>
                        <span>Xem hồ sơ</span>
                    </button>

                    <!-- Phân quyền -->
                    <a :href="'{{ url('/staff') }}/' + user.id + '/edit'" x-show="canManagePerm"
                       class="text-purple-600 hover:text-purple-800 font-bold flex items-center gap-1 cursor-pointer transition-colors">
                        <i class="fa-solid fa-shield-halved text-[11px]"></i>
                        <span>Phân quyền</span>
                        <i class="fa-solid fa-chevron-right text-[9px]"></i>
                    </a>
                </div>

            </div>
        </template>
    </div>

    <!-- 4. VIEW MODE: BẢNG CHI TIẾT (TABLE VIEW) -->
    <div x-show="viewMode === 'table'" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase text-[11px]">
                        <th class="py-3 px-4">Cán Bộ Nhân Sự</th>
                        <th class="py-3 px-3">Mã NV</th>
                        <th class="py-3 px-4">Phòng Ban</th>
                        <th class="py-3 px-4">Vai Trò Hệ Thống</th>
                        <th class="py-3 px-4">Chi Nhánh</th>
                        <th class="py-3 px-4">Liên Hệ</th>
                        <th class="py-3 px-4 text-right">Lương Cơ Bản</th>
                        <th class="py-3 px-3 text-center">Phép Năm</th>
                        <th class="py-3 px-3 text-center">Trạng Thái</th>
                        <th class="py-3 px-4 text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Nhân sự -->
                            <td class="py-3 px-4 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs text-white shadow-2xs shrink-0"
                                     :class="getAvatarBgClass(user)">
                                    <span x-text="getInitials(user.name)"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-black text-slate-900 truncate" x-text="user.name"></div>
                                    <div class="text-[11px] text-slate-400 truncate" x-text="user.email"></div>
                                </div>
                            </td>

                            <!-- Mã NV -->
                            <td class="py-3 px-3 font-mono font-bold text-slate-600" x-text="user.code || ('NV-' + user.id)"></td>

                            <!-- Phòng Ban -->
                            <td class="py-3 px-4">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md"
                                      :class="getDepartmentBadgeClass(user.department)"
                                      x-text="getDepartmentLabel(user.department)"></span>
                            </td>

                            <!-- Vai Trò -->
                            <td class="py-3 px-4">
                                <span class="font-bold text-xs" :class="getRoleTextClass(user)" x-text="getRoleLabel(user)"></span>
                            </td>

                            <!-- Chi Nhánh -->
                            <td class="py-3 px-4 text-slate-700 font-medium" x-text="user.branch || 'Trụ sở Chính - Vinh'"></td>

                            <!-- Liên Hệ -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-700" x-text="user.phone || '---'"></td>

                            <!-- Lương -->
                            <td class="py-3 px-4 text-right font-mono font-black text-emerald-600" x-text="formatSalary(user.base_salary)"></td>

                            <!-- Phép Năm -->
                            <td class="py-3 px-3 text-center font-bold text-slate-700">
                                <span x-text="(user.remaining_leave_days || 12) + ' / ' + (user.annual_leave_days || 12)"></span>
                            </td>

                            <!-- Trạng Thái -->
                            <td class="py-3 px-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                      :class="user.status === 'Đang làm việc' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'"
                                      x-text="user.status || 'Đang làm việc'"></span>
                            </td>

                            <!-- Thao Tác -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openProfileModal(user)"
                                            class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 flex items-center justify-center transition-colors"
                                            title="Xem hồ sơ chi tiết">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </button>
                                    <a :href="'{{ url('/staff') }}/' + user.id + '/edit'" x-show="canEdit"
                                       class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center transition-colors"
                                       title="Chỉnh sửa hồ sơ & phân quyền">
                                        <i class="fa-solid fa-pencil text-xs"></i>
                                    </a>
                                    <a :href="'{{ url('/staff') }}/' + user.id + '/edit'" x-show="canManagePerm"
                                       class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 flex items-center justify-center transition-colors"
                                       title="Phân quyền vai trò">
                                        <i class="fa-solid fa-shield-halved text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="filteredUsers.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-3">
        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
            <i class="fa-solid fa-user-slash"></i>
        </div>
        <h4 class="font-black text-slate-800 text-sm">Không tìm thấy cán bộ nhân sự phù hợp</h4>
        <p class="text-xs text-slate-500 max-w-sm mx-auto">Vui lòng thử tìm kiếm với từ khóa khác hoặc điều chỉnh các bộ lọc phòng ban, chức danh.</p>
        <button type="button" @click="resetFilters()"
                class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
            Xóa bộ lọc
        </button>
    </div>

    <!-- (Đã chuyển sang trang /staff/create và /staff/{id}/edit riêng) -->

    <!-- ======================================================== -->
    <!-- MODAL 2: XEM HỒ SƠ CHI TIẾT -->
    <!-- ======================================================== -->
    <div x-show="showProfileModal" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showProfileModal = false"
             class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden flex flex-col">
            
            <!-- Profile Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-[#1a3a68] to-slate-800 p-6 text-white text-center relative">
                <button type="button" @click="showProfileModal = false" class="absolute top-4 right-4 text-white/70 hover:text-white cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
                
                <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center font-black text-xl text-white shadow-xl ring-4 ring-white/20"
                     :class="getAvatarBgClass(selectedUser)">
                    <span x-text="getInitials(selectedUser?.name)"></span>
                </div>

                <h3 class="font-black text-lg text-white mt-3" x-text="selectedUser?.name"></h3>
                <div class="flex items-center justify-center gap-2 mt-1">
                    <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-white/20 text-sky-200" x-text="selectedUser?.code || ('NV-' + selectedUser?.id)"></span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-400/30" x-text="selectedUser?.status || 'Đang làm việc'"></span>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="p-6 space-y-3.5 text-xs divide-y divide-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">Phòng ban:</span>
                    <span class="font-bold text-slate-900" x-text="getDepartmentLabel(selectedUser?.department)"></span>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <span class="text-slate-400 font-medium">Chức danh / Vai trò:</span>
                    <span class="font-bold text-purple-700" x-text="getRoleLabel(selectedUser)"></span>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <span class="text-slate-400 font-medium">Điện thoại liên hệ:</span>
                    <span class="font-bold text-slate-900 font-mono" x-text="selectedUser?.phone || 'Chưa cập nhật'"></span>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <span class="text-slate-400 font-medium">Email hệ thống:</span>
                    <span class="font-bold text-slate-800" x-text="selectedUser?.email"></span>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <span class="text-slate-400 font-medium">Chi nhánh làm việc:</span>
                    <span class="font-bold text-slate-800" x-text="selectedUser?.branch || 'Trụ sở Chính - Vinh'"></span>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <span class="text-slate-400 font-medium">Mức lương cơ bản:</span>
                    <span class="font-black text-emerald-600 font-mono text-sm" x-text="formatSalary(selectedUser?.base_salary)"></span>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <span class="text-slate-400 font-medium">Chế độ phép năm:</span>
                    <span class="font-bold text-slate-900">
                        Còn lại <strong class="text-emerald-600 font-black" x-text="Math.round(selectedUser?.remaining_leave_days || 12)"></strong> / <span x-text="(selectedUser?.annual_leave_days || 12) + ' ngày'"></span>
                    </span>
                </div>
            </div>

            <!-- Profile Actions -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                <a :href="'{{ url('/staff') }}/' + selectedUser?.id + '/edit'" x-show="canManagePerm"
                   class="px-3.5 py-2 rounded-xl bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Xem phân quyền</span>
                </a>
                <div class="flex items-center gap-2">
                    <a :href="'{{ url('/staff') }}/' + selectedUser?.id + '/edit'" x-show="canEdit"
                       class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-xs cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-pencil text-xs"></i>
                        <span>Chỉnh sửa hồ sơ</span>
                    </a>
                    <button type="button" @click="showProfileModal = false"
                            class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 font-bold hover:bg-slate-100 cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
const PERM_DICT = {
    'orders.view': { name: 'Xem Đơn Hàng', desc: 'Tra cứu danh sách & chi tiết các đơn đặt hàng' },
    'orders.create': { name: 'Lên Đơn Mới', desc: 'Khởi tạo đơn hàng xuất bán cho đại lý/công trình' },
    'orders.edit': { name: 'Sửa Đơn Hàng', desc: 'Chỉnh sửa mặt hàng, số lượng và trần chiết khấu' },
    'orders.approve': { name: 'Duyệt Đơn Bán', desc: 'Kế toán/Quản lý chốt duyệt đơn đủ điều kiện xuất kho' },
    'orders.cancel': { name: 'Hủy Đơn Hàng', desc: 'Hủy đơn hàng chưa xuất kho khi khách hủy yêu cầu' },
    'orders.status': { name: 'Đổi Trạng Thái', desc: 'Chuyển trạng thái đơn sang kho soạn, giao hàng, hoàn thành' },
    'orders.export': { name: 'In Phiếu & Excel', desc: 'In phiếu bán hàng, phiếu giao hàng và xuất excel' },
    'quotation.view': { name: 'Xem Báo Giá', desc: 'Tra cứu danh sách bảng báo giá đã lập' },
    'quotation.create': { name: 'Lập Báo Giá', desc: 'Tạo bảng báo giá sản phẩm, ống nhựa gửi đại lý' },
    'quotation.edit': { name: 'Sửa Báo Giá', desc: 'Điều chỉnh đơn giá và chiết khấu báo giá' },
    'quotation.convert': { name: 'Chuyển Đơn Bán', desc: 'Tự động tạo đơn hàng chính thức từ bảng báo giá' },
    'dealers.view': { name: 'Xem Hồ Sơ Đại Lý', desc: 'Tra cứu danh sách đại lý, cấp bậc, địa chỉ giao hàng' },
    'dealers.create': { name: 'Thêm Đại Lý Mới', desc: 'Tạo mới hồ sơ khách hàng công trình & đại lý' },
    'dealers.edit': { name: 'Sửa Thông Tin', desc: 'Cập nhật địa chỉ nhận hàng, số điện thoại, tuyến đường' },
    'debt.view': { name: 'Xem Công Nợ Đại Lý', desc: 'Tra cứu số nợ hiện tại, nợ trong hạn & nợ quá hạn' },
    'debt.adjust': { name: 'Chỉnh Hạn Mức Nợ', desc: 'Nâng/hạ hạn mức nợ và thời gian gối đầu cho đại lý' },
    'debt.approve': { name: 'Duyệt Vượt Hạn Mức', desc: 'Bảo lãnh xuất hàng khi đại lý đang chạm trần công nợ' },
    'inventory.view': { name: 'Xem Tồn Kho', desc: 'Tra cứu số lượng tồn kho theo cây, cuộn và kho bãi' },
    'inventory.create': { name: 'Thêm Mặt Hàng', desc: 'Tạo mã sản phẩm, ống nhựa, phụ kiện mới' },
    'inventory.edit': { name: 'Sửa Thông Tin', desc: 'Chỉnh sửa đơn vị tính, quy cách ống và giá vốn' },
    'inv_picking.view': { name: 'Xem Lệnh Bốc Hàng', desc: 'Tra cứu danh sách đơn hàng cần bốc xếp và soạn' },
    'inv_picking.view_all_categories': { name: 'Xem Bốc Xếp Không Phân Loại', desc: 'Xem bốc xếp tất cả các loại sản phẩm không giới hạn nhóm' },
    'inv_picking.pick': { name: 'Bốc Hàng & Ghi Số', desc: 'Bốc ống, phụ kiện và nhập số lượng thực bốc' },
    'inv_picking.approve': { name: 'Chốt Soạn Xong', desc: 'Xác nhận hoàn tất khâu bốc hàng chuyển sang KCS' },
    'inv_qc.view': { name: 'Xem Danh Sách QC', desc: 'Tra cứu các đơn hàng đã soạn xong chờ kiểm duyệt' },
    'inv_qc.qc': { name: 'Kiểm Hàng Thực Tế', desc: 'Đối chiếu số lượng thực tế với đơn hàng và khuyến mãi' },
    'inv_import.view': { name: 'Xem Phiếu Nhập', desc: 'Xem danh sách phiếu nhập kho nhà máy & hoàn trả' },
    'inv_import.create': { name: 'Tạo Phiếu Nhập', desc: 'Lập phiếu nhập kho thành phẩm ống PVC và phụ kiện' },
    'inv_export.view': { name: 'Xem Phiếu Xuất', desc: 'Tra cứu phiếu xuất kho bán lẻ, đại lý, điều chuyển' },
    'inv_export.create': { name: 'Tạo Phiếu Xuất', desc: 'Lập phiếu xuất kho hàng hóa theo đơn bán' },
    'delivery_dispatch.view': { name: 'Xem Điều Phối Xe', desc: 'Tra cứu lệnh điều xe giao hàng và trạng thái lộ trình' },
    'delivery_dispatch.create': { name: 'Tạo Lệnh Giao Hàng', desc: 'Gộp các đơn hàng cùng tuyến vào một chuyến xe tải' },
    'delivery_dispatch.settle': { name: 'Quyết Toán Chuyến Xe', desc: 'Xác nhận hoàn tất chuyến xe và bàn giao chứng từ' },
    'driver_debt.view': { name: 'Xem Tiền Thu COD', desc: 'Theo dõi tiền hàng lái xe thu hộ từ các đại lý' },
    'driver_debt.settle': { name: 'Quyết Toán Tiền Mặt', desc: 'Xác nhận thu tiền mặt lái xe bàn giao về quỹ' },
    'fleet.view': { name: 'Xem Đội Xe & Chi Phí', desc: 'Tra cứu danh sách xe tải, nhiên liệu và bảo dưỡng' },
    'funds.view': { name: 'Xem Sổ Quỹ', desc: 'Tra cứu biến động số dư tiền mặt & tài khoản ngân hàng' },
    'funds.create_receipt': { name: 'Lập Phiếu Thu', desc: 'Thu tiền khách hàng, thu cọc, thu hồi nợ đại lý' },
    'funds.create_payment': { name: 'Lập Phiếu Chi', desc: 'Chi trả tiền nhà máy Tiền Phong, chi phí vận hành' },
    'staff_list.view': { name: 'Xem Hồ Sơ Nhân Sự', desc: 'Tra cứu danh sách cán bộ công nhân viên công ty' },
    'staff_list.create': { name: 'Thêm Nhân Viên', desc: 'Tạo tài khoản và hồ sơ nhân viên mới' },
    'staff_list.edit': { name: 'Sửa Thông Tin', desc: 'Cập nhật phòng ban, chức vụ, mức lương cơ bản' },
    'staff_list.lock': { name: 'Khóa / Thôi Việc', desc: 'Khóa tài khoản truy cập khi nhân sự nghỉ việc' },
    'payroll.view': { name: 'Xem Bảng Lương', desc: 'Tra cứu phiếu lương cá nhân và bảng lương phòng ban' },
    'attendance.view': { name: 'Xem Bảng Chấm Công', desc: 'Theo dõi giờ vào/ra ca và lịch sử định vị GPS chấm công' },
    'attendance.checkin': { name: 'Chấm Công GPS', desc: 'Thực hiện điểm danh ca làm việc qua điện thoại' },
    'kpi.view': { name: 'Xem Đánh Giá KPI', desc: 'Theo dõi điểm số hiệu suất công việc cá nhân/phòng ban' },
    'permissions.view': { name: 'Xem Ma Trận Quyền', desc: 'Tra cứu bảng phân quyền vai trò và tài khoản' },
    'task_assignment.view': { name: 'Xem Danh Sách Việc', desc: 'Tra cứu công việc cá nhân và tiến độ phòng ban' },
    'proposals.view': { name: 'Xem Đề Xuất Trình Ký', desc: 'Tra cứu các tờ trình, đề xuất mua sắm, xin nghỉ phép' },
    'danhmuc.view': { name: 'Xem Danh Mục', desc: 'Tra cứu đơn vị tính, nhóm sản phẩm, tài khoản ngân hàng' },
    'audit.view': { name: 'Xem Nhật Ký Kiểm Toán', desc: 'Tra cứu lịch sử đăng nhập, tạo, sửa, xóa dữ liệu' }
};

function staffApp() {
    return {
        // Permissions
        canCreate: {{ (!$authUser || $authUser->hasPermission('staff_list.create')) ? 'true' : 'false' }},
        canEdit: {{ (!$authUser || $authUser->hasPermission('staff_list.edit')) ? 'true' : 'false' }},
        canLock: {{ (!$authUser || $authUser->hasPermission('staff_list.lock')) ? 'true' : 'false' }},
        canManagePerm: {{ (!$authUser || $authUser->hasPermission('permissions.view')) ? 'true' : 'false' }},

        // Raw Data
        usersList: @json($users ?? []),
        rolesList: @json($roles ?? []),
        branchesList: @json($branches ?? []),

        getRolePermissions(user) {
            if (!user) return [];

            // If user has custom permissions:
            if (user.custom_permissions && Array.isArray(user.custom_permissions)) {
                return user.custom_permissions.map(slug => ({
                    slug: slug,
                    name: (PERM_DICT[slug]?.name) || slug,
                    desc: (PERM_DICT[slug]?.desc) || ''
                }));
            }

            const role = (user.roles && user.roles.length > 0) ? user.roles[0] : null;
            if (!role) return [];

            const fullRole = this.rolesList.find(r => r.slug === role.slug) || role;
            if (!fullRole || !fullRole.permissions) return [];

            return fullRole.permissions.map(p => ({
                slug: p.slug,
                name: (PERM_DICT[p.slug]?.name) || p.name || p.slug,
                desc: (PERM_DICT[p.slug]?.desc) || p.description || ''
            }));
        },

        // View & Filters State
        viewMode: 'grid', // 'grid' | 'table'
        searchKeyword: '',
        filterDepartment: 'all',
        filterRole: 'all',
        filterStatus: 'all',

        // Modals State
        showEditModal: false,
        showProfileModal: false,
        saving: false,
        selectedUser: null,

        // Form Data
        formData: {
            id: null,
            name: '',
            code: '',
            phone: '',
            email: '',
            department: 'Sales',
            role: 'Sales_Rep',
            branch: 'Trụ sở Chính - Vinh',
            base_salary: 12000000,
            annual_leave_days: 12,
            status: 'Đang làm việc',
            password: ''
        },

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
            // Initial data check
        },

        // Filtered Users List
        get filteredUsers() {
            let list = this.usersList;

            // 1. Department filter
            if (this.filterDepartment !== 'all') {
                list = list.filter(u => {
                    const dept = (u.department || '').toLowerCase();
                    const filter = this.filterDepartment.toLowerCase();
                    return dept.includes(filter) || 
                           (filter === 'executive' && (dept.includes('giám đốc') || dept.includes('điều hành'))) ||
                           (filter === 'accounting' && (dept.includes('kế toán') || dept.includes('tài chính'))) ||
                           (filter === 'sales' && (dept.includes('kinh doanh') || dept.includes('thị trường'))) ||
                           (filter === 'warehouse' && (dept.includes('kho') || dept.includes('vật tư'))) ||
                           (filter === 'logistics' && (dept.includes('vận tải') || dept.includes('lái xe')));
                });
            }

            // 2. Role filter
            if (this.filterRole !== 'all') {
                list = list.filter(u => {
                    return (u.roles || []).some(r => r.slug === this.filterRole);
                });
            }

            // 3. Status filter
            if (this.filterStatus !== 'all') {
                list = list.filter(u => (u.status || 'Đang làm việc') === this.filterStatus);
            }

            // 4. Search keyword
            if (this.searchKeyword) {
                const kw = this.searchKeyword.toLowerCase().trim();
                list = list.filter(u => {
                    return (u.name || '').toLowerCase().includes(kw) ||
                           (u.code || '').toLowerCase().includes(kw) ||
                           (u.phone || '').toLowerCase().includes(kw) ||
                           (u.email || '').toLowerCase().includes(kw) ||
                           (u.branch || '').toLowerCase().includes(kw);
                });
            }

            return list;
        },

        resetFilters() {
            this.searchKeyword = '';
            this.filterDepartment = 'all';
            this.filterRole = 'all';
            this.filterStatus = 'all';
        },

        // Open Modal Create
        openCreateModal() {
            this.formData = {
                id: null,
                name: '',
                code: 'NV-' + String(this.usersList.length + 1).padStart(3, '0'),
                phone: '',
                email: '',
                department: 'Sales',
                role: 'Sales_Rep',
                branch: 'Trụ sở Chính - Vinh',
                base_salary: 12000000,
                annual_leave_days: 12,
                status: 'Đang làm việc',
                password: ''
            };
            this.showEditModal = true;
        },

        // Open Modal Edit
        openEditModal(user) {
            this.formData = {
                id: user.id,
                name: user.name || '',
                code: user.code || ('NV-' + user.id),
                phone: user.phone || '',
                email: user.email || '',
                department: user.department || 'Sales',
                role: user.roles[0]?.slug || 'Sales_Rep',
                branch: user.branch || 'Trụ sở Chính - Vinh',
                base_salary: user.base_salary ? Math.round(user.base_salary) : 12000000,
                annual_leave_days: user.annual_leave_days || 12,
                status: user.status || 'Đang làm việc',
                password: ''
            };
            this.showEditModal = true;
        },

        // Open Profile View Modal
        openProfileModal(user) {
            this.selectedUser = user;
            this.showProfileModal = true;
        },

        // Submit form via API
        async submitUserForm() {
            this.saving = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/users/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await res.json();
                if (data.success || res.ok) {
                    this.showToast(data.message || 'Cập nhật thông tin nhân sự thành công!');
                    this.showEditModal = false;
                    
                    // Reload or refresh local list
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    this.showToast(data.message || 'Lỗi khi lưu thông tin nhân sự', 'error');
                }
            } catch (e) {
                this.showToast('Lỗi kết nối máy chủ', 'error');
            } finally {
                this.saving = false;
            }
        },

        // Label and Styling Helpers
        getDepartmentLabel(dept) {
            if (!dept) return 'Kinh Doanh & Bán Hàng';
            const d = dept.toLowerCase();
            if (d.includes('executive') || d.includes('giám đốc')) return 'Ban Giám Đốc / Điều Hành';
            if (d.includes('accounting') || d.includes('kế toán')) return 'Kế Toán & Tài Chính';
            if (d.includes('sales') || d.includes('kinh doanh')) return 'Kinh Doanh & Bán Hàng';
            if (d.includes('warehouse') || d.includes('kho')) return 'Kho Vận & Vật Tư';
            if (d.includes('logistics') || d.includes('vận tải')) return 'Vận Tải & Đội Xe';
            return dept;
        },

        getDepartmentBadgeClass(dept) {
            if (!dept) return 'bg-sky-100 text-sky-800 border border-sky-200';
            const d = dept.toLowerCase();
            if (d.includes('executive') || d.includes('giám đốc')) return 'bg-purple-100 text-purple-800 border border-purple-200';
            if (d.includes('accounting') || d.includes('kế toán')) return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
            if (d.includes('sales') || d.includes('kinh doanh')) return 'bg-sky-100 text-sky-800 border border-sky-200';
            if (d.includes('warehouse') || d.includes('kho')) return 'bg-amber-100 text-amber-800 border border-amber-200';
            if (d.includes('logistics') || d.includes('vận tải')) return 'bg-indigo-100 text-indigo-800 border border-indigo-200';
            return 'bg-slate-100 text-slate-800 border border-slate-200';
        },

        getRoleLabel(user) {
            if (!user) return 'Nhân Viên';
            const role = user.roles?.[0];
            if (!role) return 'Nhân Viên';
            switch (role.slug) {
                case 'Manager': return 'Ban Giám Đốc / Quản Lý';
                case 'Sales_Accountant': return 'Kế Toán Bán Hàng';
                case 'Sales_Rep': return 'NV Kinh Doanh (Sales)';
                case 'Warehouse_Manager': return 'Quản Lý Kho / Thủ Kho';
                case 'Warehouse_Staff': return 'Nhân Viên Kho / Bốc Xếp';
                case 'Driver': return 'Lái Xe / Vận Tải';
                case 'Finance_Accountant': return 'Kế Toán Tài Chính / Thủ Quỹ';
                default: return role.name || 'Nhân Viên';
            }
        },

        getRoleIcon(user) {
            const role = user?.roles?.[0];
            if (!role) return 'fa-solid fa-user';
            switch (role.slug) {
                case 'Manager': return 'fa-solid fa-landmark';
                case 'Sales_Accountant': return 'fa-solid fa-file-invoice-dollar';
                case 'Sales_Rep': return 'fa-solid fa-briefcase';
                case 'Warehouse_Manager': return 'fa-solid fa-boxes-stacked';
                case 'Warehouse_Staff': return 'fa-solid fa-dolly';
                case 'Driver': return 'fa-solid fa-truck';
                case 'Finance_Accountant': return 'fa-solid fa-wallet';
                default: return 'fa-solid fa-user-tie';
            }
        },

        getRoleBannerClass(user) {
            const role = user?.roles?.[0];
            if (!role) return 'bg-slate-50 text-slate-700 border-slate-200';
            switch (role.slug) {
                case 'Manager': return 'bg-purple-50 text-purple-700 border-purple-200';
                case 'Sales_Accountant': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
                case 'Sales_Rep': return 'bg-sky-50 text-sky-700 border-sky-200';
                case 'Warehouse_Manager':
                case 'Warehouse_Staff': return 'bg-amber-50 text-amber-700 border-amber-200';
                case 'Driver': return 'bg-blue-50 text-blue-700 border-blue-200';
                case 'Finance_Accountant': return 'bg-teal-50 text-teal-700 border-teal-200';
                default: return 'bg-slate-50 text-slate-700 border-slate-200';
            }
        },

        getRoleTextClass(user) {
            const role = user?.roles?.[0];
            if (!role) return 'text-slate-700';
            switch (role.slug) {
                case 'Manager': return 'text-purple-700';
                case 'Sales_Accountant': return 'text-emerald-700';
                case 'Sales_Rep': return 'text-sky-700';
                case 'Warehouse_Manager':
                case 'Warehouse_Staff': return 'text-amber-700';
                case 'Driver': return 'text-blue-700';
                case 'Finance_Accountant': return 'text-teal-700';
                default: return 'text-slate-700';
            }
        },

        getAvatarBgClass(user) {
            if (!user) return 'bg-slate-700';
            const role = user.roles?.[0]?.slug;
            switch (role) {
                case 'Manager': return 'bg-[#0284c7]'; // AK blue
                case 'Sales_Accountant': return 'bg-[#059669]';
                case 'Sales_Rep': return 'bg-[#d97706]';
                case 'Warehouse_Manager': return 'bg-[#0d9488]';
                case 'Warehouse_Staff': return 'bg-[#64748b]';
                case 'Driver': return 'bg-[#2563eb]';
                case 'Finance_Accountant': return 'bg-[#7c3aed]';
                default: return 'bg-slate-700';
            }
        },

        getInitials(name) {
            if (!name) return 'NV';
            if (name.includes('KBTech') || name.includes('Admin')) return 'AK';
            const parts = name.trim().split(/\s+/);
            if (parts.length >= 2) {
                return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        },

        formatSalary(val) {
            if (!val) return '0 đ';
            const num = Number(val);
            if (isNaN(num)) return '0 đ';
            return num.toLocaleString('vi-VN') + ' đ';
        }
    };
}
</script>
@endsection
