<!-- ============================================================== -->
<!-- TAB: LỊCH BẢO DƯỠNG & THAY LÕI ĐỊNH KỲ                         -->
<!-- ============================================================== -->
<div x-show="activeTab === 'maintenance'" class="p-4 sm:p-6 space-y-4">
    <!-- 4 KPI Sub-cards for Maintenance Schedule -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <!-- Card 1: Tổng máy có lịch -->
        <div @click="maintStatusFilter = 'all'; fetchMaintenance()" 
             class="bg-gradient-to-br from-slate-50 to-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs cursor-pointer hover:border-purple-400 transition" 
             :class="maintStatusFilter === 'all' ? 'ring-2 ring-purple-500/30 border-purple-500 bg-purple-50/20' : ''">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase text-slate-500">Tổng máy theo dõi</span>
                <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-slate-800" x-text="maintenanceStats.total || 0"></span>
                <span class="text-[10px] text-slate-400">thiết bị</span>
            </div>
            <div class="text-[10px] text-slate-500 mt-1">Đang hoạt động trên hệ thống</div>
        </div>

        <!-- Card 2: Quá hạn bảo dưỡng (CẦN XỬ LÝ GẤP) -->
        <div @click="maintStatusFilter = 'overdue'; fetchMaintenance()" 
             class="bg-gradient-to-br from-rose-50/60 to-white p-3.5 rounded-xl border border-rose-200 shadow-2xs cursor-pointer hover:border-rose-500 transition relative" 
             :class="maintStatusFilter === 'overdue' ? 'ring-2 ring-rose-500/30 border-rose-500 bg-rose-50' : ''">
            <template x-if="maintenanceStats.overdue > 0">
                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
            </template>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase text-rose-700">Quá hạn bảo dưỡng</span>
                <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-rose-600" x-text="maintenanceStats.overdue || 0"></span>
                <span class="text-[10px] font-bold text-rose-800 bg-rose-100 px-1.5 py-0.5 rounded">Cần gọi gấp</span>
            </div>
            <div class="text-[10px] text-rose-600 mt-1">Lõi lọc quá hạn gây tắc màng</div>
        </div>

        <!-- Card 3: Đến hạn trong 7 ngày tới -->
        <div @click="maintStatusFilter = 'due_7_days'; fetchMaintenance()" 
             class="bg-gradient-to-br from-amber-50/60 to-white p-3.5 rounded-xl border border-amber-200 shadow-2xs cursor-pointer hover:border-amber-500 transition" 
             :class="maintStatusFilter === 'due_7_days' ? 'ring-2 ring-amber-500/30 border-amber-500 bg-amber-50' : ''">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase text-amber-700">Đến hạn tuần này</span>
                <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-hourglass-half"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-amber-600" x-text="maintenanceStats.due_7_days || 0"></span>
                <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded">≤ 7 ngày</span>
            </div>
            <div class="text-[10px] text-amber-600 mt-1">Lên lịch hẹn và nhắc khách</div>
        </div>

        <!-- Card 4: Đến hạn trong 30 ngày tới -->
        <div @click="maintStatusFilter = 'due_30_days'; fetchMaintenance()" 
             class="bg-gradient-to-br from-sky-50/60 to-white p-3.5 rounded-xl border border-sky-200 shadow-2xs cursor-pointer hover:border-sky-500 transition" 
             :class="maintStatusFilter === 'due_30_days' ? 'ring-2 ring-sky-500/30 border-sky-500 bg-sky-50' : ''">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase text-sky-700">Đến hạn tháng này</span>
                <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-sky-600" x-text="maintenanceStats.due_30_days || 0"></span>
                <span class="text-[10px] font-bold text-sky-800 bg-sky-100 px-1.5 py-0.5 rounded">≤ 30 ngày</span>
            </div>
            <div class="text-[10px] text-sky-600 mt-1">Kế hoạch chuẩn bị vật tư lõi lọc</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs bg-slate-50/80 p-3 rounded-xl border border-slate-200/80">
        <div class="flex flex-wrap items-center gap-2 flex-1">
            <!-- Search input -->
            <div class="relative min-w-[240px] flex-1 sm:flex-none">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                <input 
                    type="text" 
                    x-model="maintSearch" 
                    @input.debounce.300ms="fetchMaintenance()" 
                    placeholder="Tìm theo tên khách, SĐT, máy, địa chỉ, serial..." 
                    class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-xs font-medium"
                >
            </div>

            <!-- Filter Status -->
            <select 
                x-model="maintStatusFilter" 
                @change="fetchMaintenance()" 
                class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
                <option value="all">Tất cả trạng thái lịch</option>
                <option value="overdue">🚨 Đã quá hạn bảo dưỡng</option>
                <option value="due_7_days">⏳ Đến hạn trong 7 ngày</option>
                <option value="due_30_days">📅 Đến hạn trong 30 ngày</option>
                <option value="this_month">🗓️ Đến hạn trong tháng này</option>
            </select>

            <!-- Filter Technician -->
            <select 
                x-model="maintTechFilter" 
                @change="fetchMaintenance()" 
                class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
                <option value="">Tất cả kỹ thuật viên</option>
                <template x-for="tech in technicians" :key="tech.id">
                    <option :value="tech.id" x-text="tech.name + (tech.phone ? ' (' + tech.phone + ')' : '')"></option>
                </template>
            </select>

            <!-- Reset Filter -->
            <button 
                type="button" 
                @click="maintSearch = ''; maintStatusFilter = 'all'; maintTechFilter = ''; fetchMaintenance()" 
                class="px-3 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-100 text-slate-600 font-semibold transition" 
                title="Làm mới bộ lọc"
            >
                <i class="fa-solid fa-rotate-left"></i>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <!-- Nút xuất danh sách / Hướng dẫn -->
            <button 
                type="button" 
                @click="openGlobalReminderModal()" 
                class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold transition border border-purple-200 cursor-pointer shadow-2xs"
            >
                <i class="fa-regular fa-comment-dots text-purple-600"></i>
                <span>Mẫu Tin Nhắn Nhắc Khách</span>
            </button>
        </div>
    </div>

    <!-- Table of Maintenance Schedules -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3 text-center w-12">STT</th>
                        <th class="py-3 px-3 min-w-[220px]">Khách Hàng & Địa Chỉ</th>
                        <th class="py-3 px-3 min-w-[220px]">Thiết Bị & Model Máy</th>
                        <th class="py-3 px-3 min-w-[130px] text-center">Kỳ Bảo Dưỡng</th>
                        <th class="py-3 px-3 min-w-[180px]">Hạn Bảo Dưỡng Tới</th>
                        <th class="py-3 px-3 min-w-[150px]">KTV Phụ Trách</th>
                        <th class="py-3 px-3 text-center min-w-[140px]">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-if="maintenanceList.length === 0">
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-600">Không tìm thấy lịch bảo dưỡng nào phù hợp</p>
                                <p class="text-xs text-slate-400 mt-1">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="(item, index) in maintenanceList" :key="item.id">
                        <tr class="hover:bg-slate-50/80 transition group" :class="item.maintenance_status === 'overdue' ? 'bg-rose-50/20' : (item.maintenance_status === 'due_7_days' ? 'bg-amber-50/20' : '')">
                            <!-- STT -->
                            <td class="py-3.5 px-3 text-center text-slate-400 font-mono font-semibold" x-text="index + 1"></td>

                            <!-- Khách hàng -->
                            <td class="py-3.5 px-3 align-top">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span x-text="item.customer_name"></span>
                                    <template x-if="item.qr_token">
                                        <a :href="'/m/' + item.qr_token" target="_blank" title="Xem Passport máy" class="text-sky-600 hover:text-sky-700 text-[10px]">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </template>
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    <template x-if="item.customer_phone">
                                        <div class="flex items-center gap-1">
                                            <a :href="'tel:' + item.customer_phone" class="font-mono text-emerald-700 font-bold hover:underline flex items-center gap-1" :title="'Gọi: ' + item.customer_phone">
                                                <i class="fa-solid fa-phone text-[10px]"></i>
                                                <span x-text="item.customer_phone"></span>
                                            </a>
                                            <!-- Link chat Zalo nhanh -->
                                            <a :href="'https://zalo.me/' + item.customer_phone.replace(/[^0-9]/g, '')" target="_blank" class="px-1.5 py-0.2 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold text-[9px]" title="Chat Zalo">
                                                Zalo
                                            </a>
                                        </div>
                                    </template>
                                    <template x-if="!item.customer_phone">
                                        <span class="text-slate-400 italic text-[11px]">Chưa có SĐT</span>
                                    </template>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-start gap-1 line-clamp-1" :title="item.customer_address">
                                    <i class="fa-solid fa-location-dot text-slate-400 text-[10px] mt-0.5 shrink-0"></i>
                                    <span x-text="item.customer_address || 'Chưa có địa chỉ'"></span>
                                </div>
                            </td>

                            <!-- Thiết bị & Model -->
                            <td class="py-3.5 px-3 align-top">
                                <div class="font-bold text-slate-800" x-text="item.machine_model"></div>
                                <div class="flex items-center gap-2 mt-1 text-[11px]">
                                    <template x-if="item.machine_serial">
                                        <span class="font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]" :title="'Mã Serial: ' + item.machine_serial" x-text="'SN: ' + item.machine_serial"></span>
                                    </template>
                                    <template x-if="item.qr_code">
                                        <span class="font-mono text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded text-[10px] font-bold" x-text="'QR: ' + item.qr_code"></span>
                                    </template>
                                </div>
                                <!-- Gợi ý lõi lọc đi kèm model nếu có -->
                                <template x-if="item.default_parts && item.default_parts.length > 0">
                                    <div class="text-[10px] text-teal-700 bg-teal-50/80 px-2 py-1 rounded-lg mt-1.5 border border-teal-100">
                                        <span class="font-bold">Lõi chuẩn: </span>
                                        <span x-text="item.default_parts.map(p => p.part_name || p.name).slice(0, 3).join(', ') + (item.default_parts.length > 3 ? '...' : '')"></span>
                                    </div>
                                </template>
                            </td>

                            <!-- Kỳ bảo dưỡng -->
                            <td class="py-3.5 px-3 align-top text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-black bg-slate-100 text-slate-700 border border-slate-200">
                                    <span x-text="item.filter_replacement_months || 6"></span> Tháng/Lần
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1" :title="'Ngày lắp: ' + item.installed_at">
                                    Lắp: <span x-text="item.installed_at"></span>
                                </div>
                            </td>

                            <!-- Hạn bảo dưỡng tới -->
                            <td class="py-3.5 px-3 align-top">
                                <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar-check text-purple-600"></i>
                                    <span x-text="item.next_maintenance_date"></span>
                                </div>

                                <!-- Badge đếm ngày trực quan -->
                                <div class="mt-1.5">
                                    <template x-if="item.maintenance_status === 'overdue'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                                            <span>Quá hạn <strong x-text="Math.abs(item.days_diff)"></strong> ngày</span>
                                        </span>
                                    </template>

                                    <template x-if="item.maintenance_status === 'due_7_days'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                                            <i class="fa-solid fa-clock text-[9px]"></i>
                                            <span>Đến hạn! Còn <strong x-text="item.days_diff"></strong> ngày</span>
                                        </span>
                                    </template>

                                    <template x-if="item.maintenance_status === 'due_30_days'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-300">
                                            <i class="fa-solid fa-hourglass-start text-[9px]"></i>
                                            <span>Còn <strong x-text="item.days_diff"></strong> ngày</span>
                                        </span>
                                    </template>

                                    <template x-if="item.maintenance_status === 'upcoming'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span>Còn <strong x-text="item.days_diff"></strong> ngày</span>
                                        </span>
                                    </template>

                                    <template x-if="item.maintenance_status === 'not_set'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-400 italic">
                                            <span>Chưa hẹn ngày</span>
                                        </span>
                                    </template>
                                </div>
                            </td>

                            <!-- KTV Phụ Trách -->
                            <td class="py-3.5 px-3 align-top">
                                <div class="flex items-center gap-1.5 font-semibold text-slate-800 text-[11px]">
                                    <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                                        <i class="fa-solid fa-user-gear text-[9px]"></i>
                                    </div>
                                    <span x-text="item.technician_name"></span>
                                </div>
                                <template x-if="item.warranty_status === 'active' || item.warranty_status === 'expiring_soon'">
                                    <div class="text-[10px] text-emerald-600 font-semibold mt-1">
                                        BH: <span x-text="item.warranty_until"></span>
                                    </div>
                                </template>
                            </td>

                            <!-- Thao tác -->
                            <td class="py-3.5 px-3 align-top text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- 1. Dời / Đặt lại lịch hẹn -->
                                    <button 
                                        type="button" 
                                        @click="openRescheduleModal(item)" 
                                        title="Dời hoặc đặt lại ngày hẹn bảo dưỡng" 
                                        class="p-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 transition cursor-pointer"
                                    >
                                        <i class="fa-regular fa-calendar-plus"></i>
                                    </button>

                                    <!-- 2. Lập phiếu thay lõi / bảo dưỡng -->
                                    <button 
                                        type="button" 
                                        @click="quickCreateRepairFromInstalled(item)" 
                                        title="Tiếp nhận bảo dưỡng / thay lõi (Mở phiếu dịch vụ)" 
                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition cursor-pointer"
                                    >
                                        <i class="fa-solid fa-wrench"></i>
                                    </button>

                                    <!-- 3. Đã hoàn tất bảo dưỡng (+6 tháng) -->
                                    <button 
                                        type="button" 
                                        @click="confirmCompleteMaintenance(item)" 
                                        title="Đã bảo dưỡng xong (Tự động cộng thêm kỳ tới)" 
                                        class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer"
                                    >
                                        <i class="fa-solid fa-circle-check"></i>
                                    </button>

                                    <!-- 4. Nhắc Zalo/SMS -->
                                    <button 
                                        type="button" 
                                        @click="openReminderModal(item)" 
                                        title="Soạn tin nhắn nhắc khách hàng" 
                                        class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition cursor-pointer"
                                    >
                                        <i class="fa-regular fa-comment-dots"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination summary -->
        <div class="px-4 py-3 bg-slate-50/70 border-t border-slate-200 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
            <div>
                Hiển thị <strong x-text="maintenanceList.length"></strong> lịch hẹn bảo dưỡng
                <template x-if="maintStatusFilter !== 'all'">
                    <span class="text-purple-700 font-bold" x-text="'(Đang lọc: ' + maintStatusFilter + ')'"></span>
                </template>
            </div>
            <div class="text-[11px] text-slate-400">
                <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i>
                Bấm vào <i class="fa-solid fa-circle-check text-emerald-600"></i> để đánh dấu đã bảo dưỡng xong và tự động gia hạn kỳ hẹn kế tiếp.
            </div>
        </div>
    </div>
</div>
