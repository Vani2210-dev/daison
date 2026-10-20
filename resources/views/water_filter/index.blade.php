@extends('layouts.app')

@section('title', 'Phân Hệ Lắp Đặt & Sửa Chữa Máy Lọc Nước | Đại Sơn ERP')

@section('content')
<div class="w-full space-y-4" x-data="waterFilterModule()">

    <!-- HEADER & TITLE -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-cyan-500 text-white flex items-center justify-center shadow-md shadow-sky-500/20 text-xl shrink-0">
                <i class="fa-solid fa-faucet-drip"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">DỊCH VỤ MÁY LỌC NƯỚC</h1>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">
                        Hệ thống độc lập
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Quản lý đơn đi lắp đặt máy mới, tiếp nhận sửa chữa bảo hành & kho phụ tùng linh kiện máy lọc
                </p>
            </div>
        </div>

        <!-- QUICK ACTION BUTTONS -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <a href="{{ url('/water-filter/installations/create') }}" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span>+ Lên đơn lắp đặt (Trang riêng)</span>
            </a>
            <button @click="openModal('repairModal')" type="button" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                <i class="fa-solid fa-wrench"></i>
                <span>+ Tiếp nhận sửa chữa</span>
            </button>
            <button @click="openModal('importModal')" type="button" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                <i class="fa-solid fa-boxes-packing"></i>
                <span>+ Nhập kho phụ tùng</span>
            </button>
        </div>
    </div>

    <!-- TOP KPI STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- KPI 1: Đơn đi lắp đặt đang chờ/đang thực hiện -->
        <div @click="changeTab('installations')" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs cursor-pointer hover:border-sky-400 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Đơn đi lắp đặt</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm font-black">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl font-black text-slate-800" x-text="installations.length || '{{ $pendingInstallCount }}'"></div>
                <span class="text-xs font-semibold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full">Đang xử lý</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Điều phối KTV đi lắp đặt máy mới</div>
        </div>

        <!-- KPI 2: Máy đã lắp & Bảo hành -->
        <div @click="changeTab('installed_machines')" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs cursor-pointer hover:border-emerald-400 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Máy đã lắp & BH</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-black">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl font-black text-slate-800" x-text="installedMachinesStats.total || '{{ $installedCount ?? 0 }}'"></div>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full" x-text="(installedMachinesStats.active || 0) + ' còn BH'"></span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Theo dõi hạn BH & lịch thay lõi</div>
        </div>

        <!-- KPI 3: Máy đang sửa chữa/bảo hành -->
        <div @click="changeTab('repairs')" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs cursor-pointer hover:border-amber-400 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sửa chữa & BH</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                    <i class="fa-solid fa-screwdriver"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl font-black text-slate-800" x-text="repairs.length || '{{ $activeRepairCount }}'"></div>
                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Đang sửa</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Sửa tại trạm hoặc tại nhà khách</div>
        </div>

        <!-- KPI 4: Tổng linh kiện phụ tùng -->
        <div @click="changeTab('parts')" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs cursor-pointer hover:border-teal-400 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kho phụ tùng</span>
                <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-black">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl font-black text-slate-800" x-text="parts.length || '{{ $totalParts }}'"></div>
                <span class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-full">Mặt hàng</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Lõi lọc, RO, bơm áp, phụ kiện...</div>
        </div>

        <!-- KPI 5: Cảnh báo sắp hết hàng -->
        <div @click="changeTab('parts'); partFilterLowStock = true; filterParts()" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs cursor-pointer hover:border-rose-400 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cảnh báo tồn kho</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-black">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl font-black text-rose-600">{{ $lowStockCount }}</div>
                <span class="text-xs font-semibold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full">Sắp hết hàng</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Phụ tùng dưới ngưỡng tồn tối thiểu</div>
        </div>
    </div>

    <!-- MAIN TABS NAVIGATION -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="flex items-center border-b border-slate-200 px-4 bg-slate-50/50 overflow-x-auto text-xs font-bold select-none">
            <button type="button" @click="changeTab('installations')" :class="activeTab === 'installations' ? 'text-sky-700 border-b-2 border-sky-600 bg-white' : 'text-slate-500 hover:text-slate-800'" class="px-5 py-3.5 flex items-center gap-2 transition cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-screwdriver-wrench text-sm"></i>
                <span>Đơn Đi Lắp Đặt Máy Lọc</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-100 text-sky-700" x-text="installations.length"></span>
            </button>
            <button type="button" @click="changeTab('installed_machines')" :class="activeTab === 'installed_machines' ? 'text-emerald-700 border-b-2 border-emerald-600 bg-white' : 'text-slate-500 hover:text-slate-800'" class="px-5 py-3.5 flex items-center gap-2 transition cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-shield-halved text-sm"></i>
                <span>Máy Đã Lắp & Bảo Hành</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700" x-text="installedMachinesStats.total || '{{ $installedCount ?? 0 }}'"></span>
            </button>
            <button type="button" @click="changeTab('repairs')" :class="activeTab === 'repairs' ? 'text-amber-700 border-b-2 border-amber-600 bg-white' : 'text-slate-500 hover:text-slate-800'" class="px-5 py-3.5 flex items-center gap-2 transition cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-wrench text-sm"></i>
                <span>Tiếp Nhận Sửa Chữa & Bảo Hành</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-700" x-text="repairs.length"></span>
            </button>
            <button type="button" @click="changeTab('parts')" :class="activeTab === 'parts' ? 'text-teal-700 border-b-2 border-teal-600 bg-white' : 'text-slate-500 hover:text-slate-800'" class="px-5 py-3.5 flex items-center gap-2 transition cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-boxes-stacked text-sm"></i>
                <span>Kho Phụ Tùng & Phụ Kiện</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-100 text-teal-700" x-text="parts.length"></span>
            </button>
            <button type="button" @click="changeTab('models')" :class="activeTab === 'models' ? 'text-cyan-700 border-b-2 border-cyan-600 bg-white' : 'text-slate-500 hover:text-slate-800'" class="px-5 py-3.5 flex items-center gap-2 transition cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-faucet-drip text-sm"></i>
                <span>Danh Mục Model Máy</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-100 text-cyan-700" x-text="machineModels.length"></span>
            </button>
            <button type="button" @click="changeTab('transactions')" :class="activeTab === 'transactions' ? 'text-indigo-700 border-b-2 border-indigo-600 bg-white' : 'text-slate-500 hover:text-slate-800'" class="px-5 py-3.5 flex items-center gap-2 transition cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                <span>Nhật Ký Nhập / Xuất Kho</span>
            </button>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 1: DANH SÁCH ĐƠN ĐI LẮP ĐẶT                               -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'installations'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="installSearch" @input.debounce.300ms="fetchInstallations()" placeholder="Tìm theo mã đơn, khách, SĐT, máy lọc..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs">
                    </div>
                    <select x-model="installStatus" @change="fetchInstallations()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs font-bold text-slate-700">
                        <option value="">-- Tất cả trạng thái đơn --</option>
                        @foreach($statuses as $stKey => $stVal)
                            <option value="{{ $stKey }}">{{ $stVal['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ url('/water-filter/installations/create') }}" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>+ Lên đơn lắp đặt mới</span>
                </a>
            </div>

            <!-- Table of Installations -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Mã đơn</th>
                            <th class="py-3 px-3">Khách hàng & SĐT</th>
                            <th class="py-3 px-3">Địa chỉ lắp đặt</th>
                            <th class="py-3 px-3">Model máy lọc</th>
                            <th class="py-3 px-3">Kỹ thuật viên</th>
                            <th class="py-3 px-3">Lịch hẹn</th>
                            <th class="py-3 px-3 text-right">Tổng thu</th>
                            <th class="py-3 px-3 text-center">Trạng thái</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="installations.length === 0">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có đơn lắp đặt nào phù hợp
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in installations" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-bold text-sky-700" x-text="item.code"></td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800" x-text="item.customer_name"></div>
                                    <a :href="'tel:' + item.customer_phone" class="text-sky-600 hover:underline flex items-center gap-1 text-[11px] mt-0.5">
                                        <i class="fa-solid fa-phone text-[9px]"></i>
                                        <span x-text="item.customer_phone"></span>
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-slate-600 min-w-[200px]" :title="item.customer_address" x-text="item.customer_address"></td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800" x-text="item.machine_model"></span>
                                    <div x-show="item.items && item.items.length > 0" class="text-[10px] text-teal-600 font-semibold mt-0.5">
                                        + <span x-text="item.items.length"></span> phụ kiện kèm
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700" x-text="item.technician_name || 'Chưa phân công'"></span>
                                </td>
                                <td class="py-3 px-3 text-slate-500 text-[11px]" x-text="formatDateTime(item.appointment_date)"></td>
                                <td class="py-3 px-3 text-right font-bold text-slate-800" x-text="window.formatCurrency(item.total_amount)"></td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase inline-block" :class="getInstallStatusBadge(item.status)" x-text="getInstallStatusText(item.status)"></span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Quick complete / TDS inspect -->
                                        <button @click="openInspectModal(item)" title="Nghiệm thu & Trừ phụ tùng" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer">
                                            <i class="fa-solid fa-check-double"></i>
                                        </button>
                                        <!-- Edit (Full Page) -->
                                        <a :href="'/water-filter/installations/' + item.id + '/edit'" title="Sửa đơn lắp đặt (Trang riêng)" class="p-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 transition cursor-pointer inline-flex items-center justify-center">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <!-- Delete -->
                                        <button @click="deleteInstallation(item.id)" title="Xóa" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB: MÁY ĐÃ LẮP & QUẢN LÝ BẢO HÀNH                             -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- TAB: MÁY ĐÃ LẮP, TEM QR & QUẢN LÝ BẢO HÀNH                     -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'installed_machines'" class="p-4 sm:p-6 space-y-4">
            <!-- 6 KPI Sub-cards for Warranty & QR Activation Lifecycle -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Card 1: Tổng máy -->
                <div @click="installedWarrantyStatus = ''; fetchInstalledMachines()" class="bg-gradient-to-br from-slate-50 to-white p-3 rounded-xl border border-slate-200/80 shadow-2xs cursor-pointer hover:border-slate-400 transition" :class="installedWarrantyStatus === '' ? 'ring-2 ring-slate-500/30 border-slate-500' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-slate-500">Tổng thiết bị</span>
                        <i class="fa-solid fa-faucet-drip text-slate-400 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-slate-800" x-text="installedMachinesStats.total"></span>
                        <span class="text-[10px] text-slate-400">máy/tem</span>
                    </div>
                </div>

                <!-- Card 2: Tem QR trống (chưa kích hoạt) -->
                <div @click="installedWarrantyStatus = 'unactivated'; fetchInstalledMachines()" class="bg-gradient-to-br from-slate-100/60 to-white p-3 rounded-xl border border-slate-300 shadow-2xs cursor-pointer hover:border-amber-400 transition" :class="installedWarrantyStatus === 'unactivated' ? 'ring-2 ring-amber-500/30 border-amber-500 bg-amber-50/40' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-slate-600">Tem QR trống</span>
                        <i class="fa-solid fa-qrcode text-amber-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-amber-600" x-text="installedMachinesStats.unactivated"></span>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.2 rounded">Chờ dán</span>
                    </div>
                </div>

                <!-- Card 3: Chờ duyệt kích hoạt -->
                <div @click="installedWarrantyStatus = 'pending_activation'; fetchInstalledMachines()" class="bg-gradient-to-br from-orange-50/60 to-white p-3 rounded-xl border border-orange-200 shadow-2xs cursor-pointer hover:border-orange-500 transition relative" :class="installedWarrantyStatus === 'pending_activation' ? 'ring-2 ring-orange-500/30 border-orange-500 bg-orange-50' : ''">
                    <template x-if="installedMachinesStats.pending_activation > 0">
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
                        </span>
                    </template>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-orange-700">Chờ duyệt KH</span>
                        <i class="fa-solid fa-clock text-orange-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-orange-600" x-text="installedMachinesStats.pending_activation"></span>
                        <span class="text-[10px] font-bold text-orange-800 bg-orange-100 px-1.5 py-0.2 rounded">KTV gửi</span>
                    </div>
                </div>

                <!-- Card 4: Đang còn bảo hành -->
                <div @click="installedWarrantyStatus = 'active'; fetchInstalledMachines()" class="bg-gradient-to-br from-emerald-50/50 to-white p-3 rounded-xl border border-emerald-200/80 shadow-2xs cursor-pointer hover:border-emerald-500 transition" :class="installedWarrantyStatus === 'active' ? 'ring-2 ring-emerald-500/30 border-emerald-500 bg-emerald-50/70' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-emerald-700">Đang bảo hành</span>
                        <i class="fa-solid fa-shield-halved text-emerald-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-emerald-600" x-text="installedMachinesStats.active"></span>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded">Trong hạn</span>
                    </div>
                </div>

                <!-- Card 5: Sắp hết hạn (≤ 30 ngày) -->
                <div @click="installedWarrantyStatus = 'expiring_soon'; fetchInstalledMachines()" class="bg-gradient-to-br from-amber-50/50 to-white p-3 rounded-xl border border-amber-200/80 shadow-2xs cursor-pointer hover:border-amber-500 transition" :class="installedWarrantyStatus === 'expiring_soon' ? 'ring-2 ring-amber-500/30 border-amber-500 bg-amber-50/70' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-amber-700">Sắp hết hạn</span>
                        <i class="fa-solid fa-hourglass-half text-amber-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-amber-600" x-text="installedMachinesStats.expiring_soon"></span>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.2 rounded">≤ 30 ngày</span>
                    </div>
                </div>

                <!-- Card 6: Đã hết bảo hành -->
                <div @click="installedWarrantyStatus = 'expired'; fetchInstalledMachines()" class="bg-gradient-to-br from-rose-50/50 to-white p-3 rounded-xl border border-rose-200/80 shadow-2xs cursor-pointer hover:border-rose-500 transition" :class="installedWarrantyStatus === 'expired' ? 'ring-2 ring-rose-500/30 border-rose-500 bg-rose-50/70' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-rose-700">Đã hết hạn</span>
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-rose-600" x-text="installedMachinesStats.expired"></span>
                        <span class="text-[10px] font-bold text-rose-700 bg-rose-100 px-1.5 py-0.2 rounded">Tính phí</span>
                    </div>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs bg-slate-50/80 p-3 rounded-xl border border-slate-200/80">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input 
                            type="text" 
                            x-model="installedSearch" 
                            @input.debounce.300ms="fetchInstalledMachines()" 
                            placeholder="Tìm máy: mã QR, model, khách, SĐT, serial..." 
                            class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs font-medium"
                        >
                    </div>

                    <select 
                        x-model="installedWarrantyStatus" 
                        @change="fetchInstalledMachines()" 
                        class="bg-white border border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs font-bold text-slate-700 shadow-2xs"
                    >
                        <option value="">-- Tất cả trạng thái máy / QR --</option>
                        <option value="unactivated">⚪ Tem QR trống (chưa kích hoạt)</option>
                        <option value="pending_activation">🟠 Chờ KTV / Quản lý duyệt kích hoạt</option>
                        <option value="active">🟢 Đang còn hạn bảo hành</option>
                        <option value="expiring_soon">🟡 Sắp hết hạn bảo hành (≤ 30 ngày)</option>
                        <option value="expired">🔴 Đã hết thời hạn bảo hành</option>
                    </select>

                    <select 
                        x-model="installedTechId" 
                        @change="fetchInstalledMachines()" 
                        class="bg-white border border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs font-medium text-slate-700 shadow-2xs"
                    >
                        <option value="">-- Tất cả KTV --</option>
                        <template x-for="t in technicians" :key="t.id">
                            <option :value="t.id" x-text="t.name + ' (' + (t.department || 'KTV') + ')'"></option>
                        </template>
                    </select>

                    <button 
                        type="button" 
                        @click="fetchInstalledMachines()" 
                        class="px-3 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs" 
                        title="Tải lại dữ liệu"
                    >
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Tải lại</span>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Batch Print Selected Stickers -->
                    <button 
                        type="button"
                        x-show="selectedMachineIds.length > 0"
                        @click="printSelectedStickers()"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition shadow-xs cursor-pointer"
                    >
                        <i class="fa-solid fa-print"></i>
                        <span>In tem đã chọn (<span x-text="selectedMachineIds.length"></span>)</span>
                    </button>

                    <!-- Print All QR Stickers Button -->
                    <a 
                        href="{{ url('/bao-hanh-may-loc/print-qr') }}" 
                        target="_blank" 
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold transition shadow-2xs cursor-pointer"
                    >
                        <i class="fa-solid fa-qrcode text-blue-600"></i>
                        <span>In bảng tem dán QR</span>
                    </a>

                    <!-- Batch Generate Blank QR Codes -->
                    <button 
                        type="button" 
                        @click="openBatchQrModal()" 
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold transition shadow-xs cursor-pointer"
                    >
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>+ Tạo mã QR trống</span>
                    </button>
                </div>
            </div>

            <!-- Table of Installed Machines & QR Stickers -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs bg-white">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/90 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3 w-8 text-center">
                                <input type="checkbox" @change="toggleSelectAll($event)" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                            </th>
                            <th class="py-3 px-3">Mã máy & Tem QR</th>
                            <th class="py-3 px-3">Trạng thái QR</th>
                            <th class="py-3 px-3">Model & Serial</th>
                            <th class="py-3 px-3">Khách hàng & Địa chỉ</th>
                            <th class="py-3 px-3">Kỹ thuật viên & Lắp đặt</th>
                            <th class="py-3 px-3">Bảo hành & Thay lõi</th>
                            <th class="py-3 px-3 text-center">Lượt quét</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="installedMachines.length === 0">
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-qrcode text-4xl mb-2 text-slate-300 block"></i>
                                    <span class="font-semibold">Chưa có máy lọc hoặc tem QR nào phù hợp điều kiện lọc.</span>
                                    <div class="mt-2">
                                        <button type="button" @click="openBatchQrModal()" class="text-xs text-blue-600 font-bold hover:underline">
                                            + Bấm vào đây để tạo lô mã QR thông tin trống mới
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-for="item in installedMachines" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition" :class="item.status === 'pending_activation' ? 'bg-orange-50/40' : ''">
                                <!-- Col 0: Checkbox -->
                                <td class="py-3 px-3 align-top text-center">
                                    <input type="checkbox" :value="item.id" x-model="selectedMachineIds" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                                </td>

                                <!-- Col 1: Mã máy & Tem QR -->
                                <td class="py-3 px-3 align-top min-w-[140px]">
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" @click="openQrPreview(item)" class="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Xem mã QR to & Quét thử">
                                            <i class="fa-solid fa-qrcode text-sm text-blue-600"></i>
                                        </button>
                                        <div>
                                            <div class="font-mono font-bold text-slate-900" x-text="item.machine_code"></div>
                                            <div class="text-[10px] font-mono text-slate-500" x-text="item.qr_code"></div>
                                        </div>
                                    </div>
                                    <template x-if="item.warranty_card_code">
                                        <div class="text-[10px] font-mono font-bold text-emerald-700 mt-1 flex items-center gap-1 bg-emerald-50 px-1.5 py-0.5 rounded w-max border border-emerald-200/60">
                                            <i class="fa-solid fa-id-card text-[9px]"></i>
                                            <span x-text="item.warranty_card_code"></span>
                                        </div>
                                    </template>
                                </td>

                                <!-- Col 2: Trạng thái QR & Kích hoạt -->
                                <td class="py-3 px-3 align-top min-w-[130px]">
                                    <template x-if="item.status === 'unactivated'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Tem QR trống
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'pending_activation'">
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-300 animate-pulse">
                                                <i class="fa-solid fa-clock text-[9px]"></i>
                                                Chờ duyệt KH
                                            </span>
                                            <button type="button" @click="openActivateModal(item)" class="block text-[10px] font-bold text-orange-700 hover:text-orange-900 underline">
                                                Duyệt ngay &raquo;
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="item.status === 'activated'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="getWarrantyBadgeClass(item.warranty_status)">
                                            <i class="fa-solid fa-circle text-[5px]"></i>
                                            <span x-text="getWarrantyStatusLabel(item.warranty_status)"></span>
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'expired'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            Hết hạn BH
                                        </span>
                                    </template>
                                </td>

                                <!-- Col 3: Model & Serial -->
                                <td class="py-3 px-3 align-top min-w-[170px]">
                                    <div class="font-extrabold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-faucet text-cyan-600"></i>
                                        <span x-text="item.machine_model || '(Chưa gán model)'"></span>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-600 mt-0.5 flex items-center gap-1">
                                        <span class="text-slate-400 text-[10px]">Serial:</span>
                                        <span class="font-bold text-slate-700" x-text="item.machine_serial || '(Chưa có)'"></span>
                                    </div>
                                    <template x-if="item.accessories">
                                        <div class="text-[10px] text-teal-800 mt-1 flex items-center gap-1 bg-teal-50 border border-teal-200/70 rounded px-1.5 py-0.5" :title="'Phụ kiện: ' + item.accessories">
                                            <i class="fa-solid fa-boxes-stacked text-[9px] text-teal-600"></i>
                                            <span class="truncate max-w-[150px]" x-text="item.accessories"></span>
                                        </div>
                                    </template>
                                    <template x-if="item.repairs_count > 0">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded mt-1">
                                            <i class="fa-solid fa-wrench text-[8px]"></i>
                                            <span x-text="item.repairs_count + ' lần bảo hành/sửa'"></span>
                                        </span>
                                    </template>
                                </td>

                                <!-- Col 4: Khách hàng & Địa chỉ -->
                                <td class="py-3 px-3 align-top min-w-[190px]">
                                    <template x-if="item.customer_name">
                                        <div>
                                            <div class="font-bold text-slate-800" x-text="item.customer_name"></div>
                                            <div class="text-[11px] font-semibold text-sky-600 flex items-center gap-1 mt-0.5">
                                                <i class="fa-solid fa-phone text-[10px]"></i>
                                                <a :href="'tel:' + item.customer_phone" class="hover:underline" x-text="item.customer_phone"></a>
                                            </div>
                                            <div class="text-[11px] text-slate-500 line-clamp-2 mt-0.5" :title="item.customer_address" x-text="item.customer_address"></div>
                                        </div>
                                    </template>
                                    <template x-if="!item.customer_name">
                                        <span class="text-[11px] text-slate-400 italic">Chưa liên kết khách hàng</span>
                                    </template>
                                </td>

                                <!-- Col 5: Kỹ thuật viên & Lắp đặt -->
                                <td class="py-3 px-3 align-top min-w-[150px]">
                                    <div class="font-bold text-slate-800 flex items-center gap-1">
                                        <i class="fa-regular fa-calendar-check text-emerald-600"></i>
                                        <span x-text="item.installed_at || '-'"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 mt-1 flex items-center gap-1">
                                        <i class="fa-solid fa-user-gear text-slate-400 text-[10px]"></i>
                                        <span class="font-semibold" x-text="item.technician_name || 'Chưa gán KTV'"></span>
                                    </div>
                                    <template x-if="item.tds_in || item.tds_out">
                                        <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1.5">
                                            <span title="TDS vào" class="bg-amber-50 text-amber-800 px-1 rounded font-bold" x-text="'Vào: ' + (item.tds_in || '-')"></span>
                                            <span title="TDS tinh khiết" class="bg-emerald-50 text-emerald-800 px-1 rounded font-bold" x-text="'Ra: ' + (item.tds_out || '-')"></span>
                                        </div>
                                    </template>
                                </td>

                                <!-- Col 6: Bảo hành & Thay lõi -->
                                <td class="py-3 px-3 align-top min-w-[150px]">
                                    <div class="text-[11px] font-bold text-slate-700">
                                        <span>Hạn BH: </span>
                                        <strong class="text-slate-900" x-text="item.warranty_until || '-'"></strong>
                                    </div>
                                    <div class="text-[10px] mt-0.5 font-semibold" :class="item.warranty_status === 'active' ? 'text-emerald-600' : (item.warranty_status === 'expiring_soon' ? 'text-amber-600' : 'text-slate-400')">
                                        <template x-if="item.warranty_status === 'active'">
                                            <span>Còn <strong x-text="item.warranty_days_remaining"></strong> ngày</span>
                                        </template>
                                        <template x-if="item.warranty_status === 'expiring_soon'">
                                            <span>Sắp hết! Còn <strong x-text="item.warranty_days_remaining"></strong> ngày</span>
                                        </template>
                                    </div>
                                    <div class="text-slate-600 text-[10px] mt-1 flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-spin text-teal-600"></i>
                                        <span x-text="'Thay lõi tới: ' + (item.next_maintenance_date || '-')"></span>
                                    </div>
                                </td>

                                <!-- Col 7: Lượt quét -->
                                <td class="py-3 px-3 align-top text-center min-w-[80px]">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span x-text="item.view_count"></span>
                                    </span>
                                    <template x-if="item.last_scanned_at">
                                        <div class="text-[9px] text-slate-400 mt-1 line-clamp-1" :title="'Lần quét gần nhất: ' + item.last_scanned_at" x-text="item.last_scanned_at"></div>
                                    </template>
                                </td>

                                <!-- Col 8: Thao tác -->
                                <td class="py-3 px-3 align-top text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Xem / Quét thử trang QR -->
                                        <a 
                                            :href="'/m/' + item.qr_token" 
                                            target="_blank"
                                            title="Mở trang quét mã QR (/m/...)" 
                                            class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>

                                        <!-- Duyệt kích hoạt (nếu đang chờ duyệt hoặc chưa kích hoạt) -->
                                        <template x-if="item.status === 'pending_activation' || item.status === 'unactivated'">
                                            <button 
                                                @click="openActivateModal(item)" 
                                                title="Duyệt kích hoạt bảo hành máy" 
                                                class="p-1.5 rounded-lg bg-orange-100 hover:bg-orange-200 text-orange-800 transition cursor-pointer font-bold"
                                            >
                                                <i class="fa-solid fa-circle-check"></i>
                                            </button>
                                        </template>

                                        <!-- Sửa thông tin máy & bảo hành -->
                                        <button 
                                            @click="openMachineEditModal(item)" 
                                            title="Chỉnh sửa thông tin máy & bảo hành" 
                                            class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- In tem dán máy này -->
                                        <a 
                                            :href="'/bao-hanh-may-loc/print-qr?ids=' + item.id" 
                                            target="_blank"
                                            title="In tem QR dán máy này" 
                                            class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-print"></i>
                                        </a>

                                        <!-- Tiếp nhận sửa chữa -->
                                        <button 
                                            @click="quickCreateRepairFromInstalled(item)" 
                                            title="Tiếp nhận bảo dưỡng / sửa chữa" 
                                            class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-wrench"></i>
                                        </button>

                                        <!-- Xóa máy -->
                                        <button 
                                            @click="deleteInstalledMachine(item.id)" 
                                            title="Xóa máy khỏi danh sách" 
                                            class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 2: TIẾP NHẬN SỬA CHỮA & BẢO HÀNH                          -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'repairs'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="repairSearch" @input.debounce.300ms="fetchRepairs()" placeholder="Tìm theo mã phiếu, khách, SĐT, lỗi..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs">
                    </div>
                    <select x-model="repairStatus" @change="fetchRepairs()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs font-medium">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="received">Mới tiếp nhận</option>
                        <option value="inspecting">Đang kiểm tra</option>
                        <option value="waiting_parts">Chờ linh kiện</option>
                        <option value="repairing">Đang sửa chữa</option>
                        <option value="completed">Đã sửa xong</option>
                        <option value="delivered">Đã trả khách</option>
                        <option value="cancelled">Hủy sửa</option>
                    </select>
                    <select x-model="repairWarrantyType" @change="fetchRepairs()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs font-medium">
                        <option value="">-- Tất cả loại hình --</option>
                        <option value="in_warranty">Còn bảo hành</option>
                        <option value="out_warranty">Sửa tính phí</option>
                    </select>
                </div>
                <button type="button" @click="openRepairModal()" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tiếp nhận sửa chữa</span>
                </button>
            </div>

            <!-- Table of Repairs -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Mã phiếu</th>
                            <th class="py-3 px-3">Khách hàng</th>
                            <th class="py-3 px-3">Model máy lọc</th>
                            <th class="py-3 px-3">Hiện tượng lỗi</th>
                            <th class="py-3 px-3">Linh kiện thay</th>
                            <th class="py-3 px-3">KTV phụ trách</th>
                            <th class="py-3 px-3 text-right">Chi phí</th>
                            <th class="py-3 px-3 text-center">Trạng thái</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="repairs.length === 0">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có phiếu sửa chữa nào
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in repairs" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-bold text-amber-700" x-text="item.code"></td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800" x-text="item.customer_name"></div>
                                    <div class="text-[11px] text-slate-500" x-text="item.customer_phone"></div>
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold mt-0.5" :class="item.repair_type === 'at_shop' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'" x-text="item.repair_type === 'at_shop' ? 'Tại trạm' : 'Tận nhà'"></span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800" x-text="item.machine_model"></div>
                                    <div class="text-[10px] font-bold" :class="item.warranty_type === 'in_warranty' ? 'text-emerald-600' : 'text-slate-500'" x-text="item.warranty_type === 'in_warranty' ? 'Bảo hành chính hãng' : 'Sửa chữa có phí'"></div>
                                </td>
                                <td class="py-3 px-3 text-slate-600 min-w-[220px] text-[11px]" :title="item.symptom">
                                    <div class="line-clamp-2" x-text="item.symptom"></div>
                                </td>
                                <td class="py-3 px-3">
                                    <template x-if="item.items && item.items.length > 0">
                                        <div class="space-y-0.5">
                                            <template x-for="part in item.items" :key="part.id">
                                                <div class="text-[10px] text-slate-700 flex items-center gap-1">
                                                    <i class="fa-solid fa-circle text-[4px] text-slate-400"></i>
                                                    <span x-text="part.quantity + 'x ' + part.part_name"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!item.items || item.items.length === 0">
                                        <span class="text-slate-400 text-[11px]">Chưa thay</span>
                                    </template>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700" x-text="item.technician_name || 'Chưa gán'"></span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="font-bold text-slate-800" x-text="window.formatCurrency(item.total_amount)"></div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded" :class="item.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : (item.payment_status === 'warranty_free' ? 'bg-sky-100 text-sky-800' : 'bg-rose-100 text-rose-800')" x-text="item.payment_status === 'paid' ? 'Đã thu' : (item.payment_status === 'warranty_free' ? 'Miễn phí BH' : 'Chưa thu')"></span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase inline-block" :class="getRepairStatusBadge(item.status)" x-text="getRepairStatusText(item.status)"></span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openRepairUpdateModal(item)" title="Chẩn đoán & Cập nhật tiến độ" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer">
                                            <i class="fa-solid fa-list-check"></i>
                                        </button>
                                        <button @click="editRepair(item)" title="Sửa phiếu" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="deleteRepair(item.id)" title="Xóa" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 3: KHO PHỤ TÙNG & PHỤ KIỆN MÁY LỌC                         -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'parts'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="partSearch" @input.debounce.300ms="fetchParts()" placeholder="Tìm theo tên phụ tùng, mã, vị trí..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-xs">
                    </div>
                    <select x-model="partCategory" @change="fetchParts()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-xs font-medium">
                        <option value="">-- Tất cả nhóm phụ tùng --</option>
                        <option value="Lõi lọc thô">Lõi lọc thô (1-2-3)</option>
                        <option value="Màng RO & Lọc tinh">Màng RO & Lọc tinh</option>
                        <option value="Lõi khoáng chức năng">Lõi khoáng chức năng</option>
                        <option value="Bơm & Nguồn">Bơm & Nguồn Adapter</option>
                        <option value="Van & Điện từ">Van & Điện từ</option>
                        <option value="Bình áp & Vòi">Bình áp & Vòi lấy nước</option>
                        <option value="Cút nối & Dây">Cút nối & Dây dẫn PE</option>
                        <option value="Khác">Phụ kiện khác</option>
                    </select>
                    <label class="flex items-center gap-2 bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 cursor-pointer text-xs font-semibold select-none">
                        <input type="checkbox" x-model="partFilterLowStock" @change="fetchParts()" class="rounded text-teal-600 focus:ring-teal-500">
                        <span class="text-rose-600">Chỉ hiển thị sắp hết hàng</span>
                    </label>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="openModal('importModal')" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-file-import"></i>
                        <span>Nhập kho NCC</span>
                    </button>
                    <button type="button" @click="openPartModal()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>Thêm mới phụ tùng</span>
                    </button>
                </div>
            </div>

            <!-- Table of Parts -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Mã phụ tùng</th>
                            <th class="py-3 px-3">Tên linh kiện / Phụ tùng</th>
                            <th class="py-3 px-3">Phân loại</th>
                            <th class="py-3 px-3 text-center">ĐVT</th>
                            <th class="py-3 px-3">Vị trí kho</th>
                            <th class="py-3 px-3 text-right">Giá nhập</th>
                            <th class="py-3 px-3 text-right">Giá xuất</th>
                            <th class="py-3 px-3 text-center">Tồn kho</th>
                            <th class="py-3 px-3 text-center">Cảnh báo tối thiểu</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="parts.length === 0">
                            <tr>
                                <td colspan="10" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có phụ tùng linh kiện nào
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in parts" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition" :class="item.stock_qty <= item.min_stock_alert ? 'bg-rose-50/40' : ''">
                                <td class="py-3 px-3 font-bold text-teal-800" x-text="item.code"></td>
                                <td class="py-3 px-3 font-bold text-slate-800" x-text="item.name"></td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600" x-text="item.category"></span>
                                </td>
                                <td class="py-3 px-3 text-center text-slate-600 font-semibold" x-text="item.unit"></td>
                                <td class="py-3 px-3 text-slate-500" x-text="item.location || '-'"></td>
                                <td class="py-3 px-3 text-right text-slate-600" x-text="window.formatCurrency(item.in_price)"></td>
                                <td class="py-3 px-3 text-right font-bold text-teal-700" x-text="window.formatCurrency(item.out_price)"></td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full font-black text-xs" :class="item.stock_qty <= item.min_stock_alert ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-emerald-100 text-emerald-800'" x-text="item.stock_qty"></span>
                                </td>
                                <td class="py-3 px-3 text-center text-slate-400" x-text="item.min_stock_alert"></td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="quickImport(item)" title="Nhập thêm hàng" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer">
                                            <i class="fa-solid fa-cart-plus"></i>
                                        </button>
                                        <button @click="editPart(item)" title="Sửa" class="p-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 transition cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="deletePart(item.id)" title="Xóa" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 4: DANH MỤC MODEL MÁY LỌC NƯỚC                             -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'models'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="modelSearch" @input.debounce.300ms="fetchMachineModels()" placeholder="Tìm theo tên model, mã, thương hiệu..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 text-xs">
                    </div>
                    <select x-model="modelBrand" @change="fetchMachineModels()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 text-xs font-bold text-slate-700">
                        <option value="">-- Tất cả hãng máy --</option>
                        <option value="Karofi">Karofi</option>
                        <option value="Kangaroo">Kangaroo</option>
                        <option value="Sunhouse">Sunhouse</option>
                        <option value="AO Smith">AO Smith</option>
                        <option value="Panasonic">Panasonic</option>
                    </select>
                </div>
                <button type="button" @click="openMachineModelModal()" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>+ Thêm Model Máy Mới</span>
                </button>
            </div>

            <!-- Table of Machine Models -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Mã Model</th>
                            <th class="py-3 px-3">Thương hiệu</th>
                            <th class="py-3 px-3">Tên Model máy lọc nước</th>
                            <th class="py-3 px-3 text-center">Số cấp lọc</th>
                            <th class="py-3 px-3 text-center">Kiểu dáng</th>
                            <th class="py-3 px-3 text-right">Giá niêm yết</th>
                            <th class="py-3 px-3">Đặc tính kỹ thuật</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="machineModels.length === 0">
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-faucet-drip text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có model máy lọc nước nào
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in machineModels" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-bold text-cyan-800" x-text="item.code"></td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800" x-text="item.brand || 'Khác'"></span>
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-800" x-text="item.name"></td>
                                <td class="py-3 px-3 text-center font-bold text-slate-700" x-text="(item.filter_stages || 10) + ' cấp lọc'"></td>
                                <td class="py-3 px-3 text-center text-slate-600 font-semibold" x-text="item.type || 'Tủ đứng'"></td>
                                <td class="py-3 px-3 text-right font-extrabold text-cyan-800" x-text="item.standard_price ? window.formatCurrency(item.standard_price) : '-'"></td>
                                <td class="py-3 px-3 text-slate-500 min-w-[200px]" :title="item.description" x-text="item.description || '-'"></td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="editMachineModel(item)" title="Sửa model" class="p-1.5 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-700 transition cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="deleteMachineModel(item.id)" title="Xóa" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 5: NHẬT KÝ NHẬP - XUẤT KHO PHỤ TÙNG                        -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'transactions'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="transSearch" @input.debounce.300ms="fetchTransactions()" placeholder="Tìm theo mã phiếu, tên phụ tùng..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs">
                    </div>
                    <select x-model="transType" @change="fetchTransactions()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs font-medium">
                        <option value="">-- Tất cả loại giao dịch --</option>
                        <option value="import">Nhập kho mới (+)</option>
                        <option value="export_install">Xuất đi lắp đặt (-)</option>
                        <option value="export_repair">Xuất sửa chữa (-)</option>
                        <option value="return">Hoàn kho (+)</option>
                    </select>
                </div>
            </div>

            <!-- Table of Transactions -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Thời gian</th>
                            <th class="py-3 px-3">Mã phiếu liên quan</th>
                            <th class="py-3 px-3">Phụ tùng / Linh kiện</th>
                            <th class="py-3 px-3">Loại giao dịch</th>
                            <th class="py-3 px-3 text-center">Số lượng</th>
                            <th class="py-3 px-3 text-center">Tồn trước</th>
                            <th class="py-3 px-3 text-center">Tồn sau</th>
                            <th class="py-3 px-3">Người thực hiện</th>
                            <th class="py-3 px-3">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="transactions.length === 0">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có giao dịch kho nào
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in transactions" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 text-slate-500 text-[11px]" x-text="formatDateTime(item.created_at)"></td>
                                <td class="py-3 px-3 font-bold text-slate-800" x-text="item.reference_code || '-'"></td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800" x-text="item.part ? item.part.name : 'Phụ tùng #' + item.part_id"></div>
                                    <div class="text-[10px] text-slate-400" x-text="item.part ? item.part.code : ''"></div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="getTransBadge(item.type)" x-text="getTransText(item.type)"></span>
                                </td>
                                <td class="py-3 px-3 text-center font-black" :class="item.quantity > 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="(item.quantity > 0 ? '+' : '') + item.quantity"></td>
                                <td class="py-3 px-3 text-center text-slate-500" x-text="item.balance_before"></td>
                                <td class="py-3 px-3 text-center font-bold text-slate-800" x-text="item.balance_after"></td>
                                <td class="py-3 px-3 text-slate-600" x-text="item.created_by_name || '-'"></td>
                                <td class="py-3 px-3 text-slate-500 min-w-[200px]" :title="item.note" x-text="item.note || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: TẠO / SỬA ĐƠN LẮP ĐẶT                                -->
    <!-- ============================================================== -->
    <div x-show="modals.installModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('installModal')" class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 bg-gradient-to-r from-sky-600 to-cyan-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-screwdriver-wrench text-lg"></i>
                    <h3 class="font-bold text-base" x-text="installForm.id ? 'CẬP NHẬT ĐƠN LẮP ĐẶT' : 'TẠO MỚI ĐƠN LẮP ĐẶT MÁY LỌC'"></h3>
                </div>
                <button @click="closeModal('installModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="saveInstallationSubmit()" class="p-6 overflow-y-auto space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Họ tên khách hàng / Đại lý <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="installForm.customer_name" required placeholder="Nguyễn Văn A" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số điện thoại <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="installForm.customer_phone" required placeholder="0912..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Địa chỉ lắp đặt <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="installForm.customer_address" required placeholder="Số nhà, đường, thôn/xã, huyện..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tên / Model máy lọc cần lắp <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="installForm.machine_model" required placeholder="Máy lọc RO Kangaroo Hydrogen..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Serial máy (nếu có)</label>
                        <input type="text" x-model="installForm.machine_serial" placeholder="KG-2026-..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kỹ thuật viên phụ trách</label>
                        <select x-model="installForm.technician_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-medium">
                            <option value="">-- Chưa chỉ định --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->department ?? 'KTV' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Lịch hẹn lắp đặt</label>
                        <input type="datetime-local" x-model="installForm.appointment_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phí công lắp đặt (đ)</label>
                        <input type="number" x-model="installForm.service_fee" min="0" step="10000" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-sky-800">
                    </div>
                </div>

                <!-- Phụ kiện xuất kèm mang đi lắp -->
                <div class="border-t border-slate-200 pt-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-700">Phụ tùng, vật tư mang theo kèm máy:</span>
                        <button type="button" @click="addInstallItemRow()" class="px-2.5 py-1 rounded-lg bg-sky-100 hover:bg-sky-200 text-sky-800 font-bold transition">
                            <i class="fa-solid fa-plus"></i> Thêm vật tư
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, idx) in installForm.items" :key="idx">
                            <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                                <select @change="onSelectInstallPart(idx, $event.target.value)" class="w-1/2 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs font-medium">
                                    <option value="">-- Chọn phụ tùng từ kho --</option>
                                    <template x-for="p in parts" :key="p.id">
                                        <option :value="p.id" :selected="p.id == item.part_id" x-text="p.name + ' (Tồn: ' + p.stock_qty + ')'"></option>
                                    </template>
                                </select>
                                <input type="number" x-model.number="item.quantity" min="1" placeholder="SL" class="w-20 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-center font-bold">
                                <input type="number" x-model.number="item.unit_price" min="0" placeholder="Đơn giá" class="w-28 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-right font-bold">
                                <button type="button" @click="removeInstallItemRow(idx)" class="text-rose-500 hover:text-rose-700 p-1"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú yêu cầu của khách</label>
                    <textarea x-model="installForm.customer_notes" rows="2" placeholder="Vị trí lắp, yêu cầu đặc biệt..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('installModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu đơn lắp đặt</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: NGHIỆM THU ĐƠN LẮP ĐẶT (TDS, HOÀN THÀNH, TRỪ PHỤ TÙNG) -->
    <!-- ============================================================== -->
    <div x-show="modals.inspectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('inspectModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-lg"></i>
                    <h3 class="font-bold text-base">NGHIỆM THU & BÀN GIAO LẮP ĐẶT</h3>
                </div>
                <button @click="closeModal('inspectModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="submitInspect()" class="p-6 space-y-4 text-xs">
                <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-200 text-emerald-800">
                    <div class="font-bold" x-text="inspectData.code + ' - ' + inspectData.customer_name"></div>
                    <div class="text-[11px] text-emerald-600 mt-0.5" x-text="inspectData.machine_model"></div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cập nhật trạng thái</label>
                    <select x-model="inspectForm.status" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold">
                        <option value="in_progress">Đang lắp đặt</option>
                        <option value="completed">Đã hoàn thành (Trừ kho phụ kiện)</option>
                        <option value="cancelled">Hủy đơn</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chỉ số TDS đầu vào (ppm)</label>
                        <input type="number" x-model="inspectForm.tds_in" placeholder="vd: 180" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chỉ số TDS sau lọc (ppm)</label>
                        <input type="number" x-model="inspectForm.tds_out" placeholder="vd: 8 - 15" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-emerald-700">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tình trạng áp lực / máy hoạt động</label>
                    <input type="text" x-model="inspectForm.pressure" placeholder="Bơm chạy êm, áp lực 2.5 bar, nước tinh khiết chảy đều..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Biên bản nghiệm thu / ghi chú của KTV</label>
                    <textarea x-model="inspectForm.completion_notes" rows="3" placeholder="Đã bàn giao chìa khóa, hướng dẫn khách sử dụng và thời hạn thay lõi..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('inspectModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Đóng</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu nghiệm thu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: TẠO / SỬA PHIẾU TIẾP NHẬN SỬA CHỮA                   -->
    <!-- ============================================================== -->
    <div x-show="modals.repairModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('repairModal')" class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-wrench text-lg"></i>
                    <h3 class="font-bold text-base" x-text="repairForm.id ? 'CẬP NHẬT PHIẾU SỬA CHỮA' : 'TIẾP NHẬN SỬA CHỮA & BẢO HÀNH'"></h3>
                </div>
                <button @click="closeModal('repairModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="saveRepairSubmit()" class="p-6 overflow-y-auto space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Họ tên khách hàng <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="repairForm.customer_name" required placeholder="Chị Lan..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số điện thoại <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="repairForm.customer_phone" required placeholder="0988..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hình thức sửa chữa</label>
                        <select x-model="repairForm.repair_type" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="at_shop">Khách mang tới trạm</option>
                            <option value="at_home">Đến tận nhà khách</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chế độ bảo hành</label>
                        <select x-model="repairForm.warranty_type" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="out_warranty">Sửa tính phí</option>
                            <option value="in_warranty">Còn bảo hành (Miễn phí)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">KTV tiếp nhận / sửa</label>
                        <select x-model="repairForm.technician_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="">-- Chưa chỉ định --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Model / Hãng máy lọc <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="repairForm.machine_model" required placeholder="Karofi Optimus, Kangaroo, Sunhouse..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số Serial / Mã máy</label>
                        <input type="text" x-model="repairForm.machine_serial" placeholder="KRF-..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Hiện tượng hư hỏng / Khách báo lỗi <span class="text-rose-500">*</span></label>
                    <textarea x-model="repairForm.symptom" required rows="2" placeholder="Máy kêu tạch tạch, nước thải chảy liên tục, nước có mùi, rò rỉ nước..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Linh kiện phụ tùng thay thế -->
                <div class="border-t border-slate-200 pt-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-700">Linh kiện phụ tùng thay thế:</span>
                        <button type="button" @click="addRepairItemRow()" class="px-2.5 py-1 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold transition">
                            <i class="fa-solid fa-plus"></i> Thêm linh kiện
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, idx) in repairForm.items" :key="idx">
                            <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                                <select @change="onSelectRepairPart(idx, $event.target.value)" class="w-1/2 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs font-medium">
                                    <option value="">-- Chọn phụ tùng từ kho --</option>
                                    <template x-for="p in parts" :key="p.id">
                                        <option :value="p.id" :selected="p.id == item.part_id" x-text="p.name + ' (Tồn: ' + p.stock_qty + ')'"></option>
                                    </template>
                                </select>
                                <input type="number" x-model.number="item.quantity" min="1" placeholder="SL" class="w-16 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-center font-bold">
                                <input type="number" x-model.number="item.unit_price" min="0" placeholder="Giá" class="w-24 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-right font-bold">
                                <label class="flex items-center gap-1 text-[11px] text-emerald-700 font-bold select-none whitespace-nowrap">
                                    <input type="checkbox" x-model="item.is_warranty" class="rounded text-emerald-600">
                                    <span>Miễn phí BH</span>
                                </label>
                                <button type="button" @click="removeRepairItemRow(idx)" class="text-rose-500 hover:text-rose-700 p-1"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tiền công thợ (đ)</label>
                        <input type="number" x-model="repairForm.labor_fee" min="0" step="10000" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Giảm giá / Chiết khấu (đ)</label>
                        <input type="number" x-model="repairForm.discount" min="0" step="5000" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('repairModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu phiếu sửa chữa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 4: CẬP NHẬT CHẨN ĐOÁN & TIẾN ĐỘ SỬA CHỮA                -->
    <!-- ============================================================== -->
    <div x-show="modals.repairUpdateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('repairUpdateModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-lg"></i>
                    <h3 class="font-bold text-base">CHẨN ĐOÁN & TIẾN ĐỘ SỬA CHỮA</h3>
                </div>
                <button @click="closeModal('repairUpdateModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="submitRepairUpdate()" class="p-6 space-y-4 text-xs">
                <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-amber-800">
                    <div class="font-bold" x-text="repairUpdateData.code + ' - ' + repairUpdateData.customer_name"></div>
                    <div class="text-[11px] text-amber-700 mt-0.5" x-text="'Lỗi: ' + repairUpdateData.symptom"></div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cập nhật trạng thái sửa</label>
                    <select x-model="repairUpdateForm.status" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none font-bold">
                        <option value="inspecting">Đang kiểm tra</option>
                        <option value="waiting_parts">Chờ linh kiện</option>
                        <option value="repairing">Đang sửa chữa</option>
                        <option value="completed">Đã sửa xong (Trừ kho linh kiện)</option>
                        <option value="delivered">Đã bàn giao trả máy cho khách</option>
                        <option value="cancelled">Khách không sửa / Hủy</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kết quả chẩn đoán kỹ thuật</label>
                    <textarea x-model="repairUpdateForm.diagnosis" rows="2" placeholder="Hỏng van điện từ, tắc màng RO, rách màng bình áp..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phương án khắc phục / Đã xử lý</label>
                    <textarea x-model="repairUpdateForm.solution" rows="2" placeholder="Đã thay van từ 24V mới, sục rửa cốc màng, đo áp đạt 3.0 bar..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Trạng thái thanh toán</label>
                        <select x-model="repairUpdateForm.payment_status" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none font-medium">
                            <option value="unpaid">Chưa thu tiền</option>
                            <option value="paid">Đã thanh toán đủ</option>
                            <option value="warranty_free">Bảo hành miễn phí</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bảo hành sửa chữa đến ngày</label>
                        <input type="date" x-model="repairUpdateForm.warranty_until" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('repairUpdateModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Đóng</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Cập nhật tiến độ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 5: THÊM / SỬA PHỤ TÙNG KHO                               -->
    <!-- ============================================================== -->
    <div x-show="modals.partModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('partModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-teal-600 to-emerald-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-lg"></i>
                    <h3 class="font-bold text-base" x-text="partForm.id ? 'CẬP NHẬT PHỤ TÙNG' : 'THÊM MỚI PHỤ TÙNG KHO'"></h3>
                </div>
                <button @click="closeModal('partModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="savePartSubmit()" class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã phụ tùng <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="partForm.code" required placeholder="LOI-RO-100G" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold uppercase">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Đơn vị tính <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="partForm.unit" required placeholder="Cái, Lõi, Bộ, Mét..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tên phụ tùng linh kiện <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="partForm.name" required placeholder="Màng RO Filmtec DOW 100G..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phân loại <span class="text-rose-500">*</span></label>
                        <select x-model="partForm.category" required class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="Lõi lọc thô">Lõi lọc thô (1-2-3)</option>
                            <option value="Màng RO & Lọc tinh">Màng RO & Lọc tinh</option>
                            <option value="Lõi khoáng chức năng">Lõi khoáng chức năng</option>
                            <option value="Bơm & Nguồn">Bơm & Nguồn</option>
                            <option value="Van & Điện từ">Van & Điện từ</option>
                            <option value="Bình áp & Vòi">Bình áp & Vòi</option>
                            <option value="Cút nối & Dây">Cút nối & Dây dẫn</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Vị trí ngăn/kệ kho</label>
                        <input type="text" x-model="partForm.location" placeholder="Kệ A1-02..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Giá nhập kho (đ) <span class="text-rose-500">*</span></label>
                        <input type="number" x-model="partForm.in_price" required min="0" step="1000" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Giá xuất / Giá bán (đ) <span class="text-rose-500">*</span></label>
                        <input type="number" x-model="partForm.out_price" required min="0" step="1000" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold text-teal-800">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tồn kho ban đầu</label>
                        <input type="number" x-model="partForm.stock_qty" min="0" :disabled="partForm.id" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold" :class="partForm.id ? 'bg-slate-100 text-slate-400' : ''">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ngưỡng báo động tồn tối thiểu</label>
                        <input type="number" x-model="partForm.min_stock_alert" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold text-rose-600">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mô tả / Thông số kỹ thuật</label>
                    <textarea x-model="partForm.description" rows="2" placeholder="Quy cách, áp lực chịu đựng, thời hạn thay..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('partModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu phụ tùng</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 6: NHẬP KHO PHỤ TÙNG TỪ NHÀ CUNG CẤP                    -->
    <!-- ============================================================== -->
    <div x-show="modals.importModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('importModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-import text-lg"></i>
                    <h3 class="font-bold text-base">NHẬP KHO PHỤ TÙNG TỪ NCC</h3>
                </div>
                <button @click="closeModal('importModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="submitImportStock()" class="p-6 space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Chọn linh kiện / phụ tùng <span class="text-rose-500">*</span></label>
                    <select x-model="importForm.part_id" @change="onSelectImportPart($event.target.value)" required class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Chọn phụ tùng cần nhập --</option>
                        <template x-for="p in parts" :key="p.id">
                            <option :value="p.id" x-text="p.code + ' - ' + p.name + ' (Tồn: ' + p.stock_qty + ')'"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số lượng nhập <span class="text-rose-500">*</span></label>
                        <input type="number" x-model.number="importForm.quantity" required min="1" placeholder="10..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-center">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Đơn giá nhập mới (đ) <span class="text-rose-500">*</span></label>
                        <input type="number" x-model.number="importForm.in_price" required min="0" step="1000" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-right">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nhà cung cấp / Nguồn hàng</label>
                    <input type="text" x-model="importForm.supplier" placeholder="Công ty Karofi, Kangaro, Toray Japan..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú phiếu nhập</label>
                    <input type="text" x-model="importForm.note" placeholder="Nhập bổ sung đợt tháng..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('importModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Xác nhận nhập kho</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 7: THÊM / SỬA MODEL MÁY LỌC NƯỚC                         -->
    <!-- ============================================================== -->
    <div x-show="modals.machineModelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('machineModelModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 to-sky-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-faucet-drip text-lg"></i>
                    <h3 class="font-bold text-base" x-text="modelForm.id ? 'CẬP NHẬT MODEL MÁY LỌC' : 'THÊM MỚI MODEL MÁY LỌC NƯỚC'"></h3>
                </div>
                <button type="button" @click="closeModal('machineModelModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="saveMachineModelSubmit()" class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Model máy <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="modelForm.code" required placeholder="vd: KG100HA, KRF-612..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold uppercase focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thương hiệu / Hãng <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="modelForm.brand" required placeholder="Kangaroo, Karofi, Sunhouse..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tên Model máy lọc nước <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="modelForm.name" required placeholder="Máy lọc nước RO Karofi Optimus O-P1319..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-semibold focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số cấp lọc (lõi)</label>
                        <input type="number" x-model.number="modelForm.filter_stages" min="1" max="20" placeholder="10" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-center focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kiểu dáng thiết kế</label>
                        <select x-model="modelForm.type" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            <option value="Tủ đứng">Tủ đứng</option>
                            <option value="Để bàn">Để bàn mini</option>
                            <option value="Lắp gầm chậu">Lắp gầm chậu rửa</option>
                            <option value="Nóng Lạnh RO">Nóng Lạnh RO</option>
                            <option value="Bán công nghiệp">Bán công nghiệp</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Giá bán niêm yết (đ)</label>
                    <input type="number" x-model.number="modelForm.standard_price" min="0" step="50000" placeholder="5000000" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-cyan-800 focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mô tả thông số kỹ thuật</label>
                    <textarea x-model="modelForm.description" rows="2" placeholder="Công nghệ RO vortex, màng Filmtec, bình áp thép..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('machineModelModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu Model máy</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: CẬP NHẬT THÔNG TIN BẢO HÀNH MÁY                         -->
    <!-- ============================================================== -->
    <div x-show="modals.warrantyEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('warrantyEditModal')" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-gradient-to-r from-emerald-600 to-teal-600 text-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-lg"></i>
                    <h3 class="font-extrabold text-sm">Cập Nhật Thông Tin Bảo Hành Máy Lọc Nước</h3>
                </div>
                <button type="button" @click="closeModal('warrantyEditModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form @submit.prevent="saveWarrantySubmit()" class="p-5 space-y-4 text-xs">
                <!-- Thông tin tóm tắt máy -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800" x-text="warrantyEditForm.customer_name"></span>
                        <span class="text-[10px] font-bold text-sky-700 font-mono" x-text="warrantyEditForm.code"></span>
                    </div>
                    <div class="text-[11px] text-slate-600 flex items-center gap-2">
                        <span>Model: <strong x-text="warrantyEditForm.machine_model"></strong></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ngày lắp đặt thực tế <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            x-model="warrantyEditForm.installed_at" 
                            @change="recalcEditWarrantyUntil()" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-800"
                        >
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Serial / IMEI máy</label>
                        <input 
                            type="text" 
                            x-model="warrantyEditForm.machine_serial" 
                            placeholder="KG-..." 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono font-bold text-slate-800"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thời hạn bảo hành (tháng)</label>
                        <select 
                            x-model.number="warrantyEditForm.warranty_months" 
                            @change="recalcEditWarrantyUntil()" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-800"
                        >
                            <option value="6">6 tháng</option>
                            <option value="12">12 tháng (1 năm)</option>
                            <option value="18">18 tháng</option>
                            <option value="24">24 tháng (2 năm - Tiêu chuẩn)</option>
                            <option value="36">36 tháng (3 năm)</option>
                            <option value="60">60 tháng (5 năm)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hạn bảo hành đến ngày</label>
                        <input 
                            type="date" 
                            x-model="warrantyEditForm.warranty_until" 
                            class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-emerald-900"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã / Số thẻ bảo hành</label>
                        <input 
                            type="text" 
                            x-model="warrantyEditForm.warranty_card_code" 
                            placeholder="BH-2026-..." 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono font-bold text-slate-800"
                        >
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kỳ thay lõi / Bảo dưỡng</label>
                        <select 
                            x-model.number="warrantyEditForm.filter_replacement_months" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-700"
                        >
                            <option value="3">Mỗi 3 tháng (Lõi 1, 2, 3)</option>
                            <option value="6">Mỗi 6 tháng (Khuyến nghị)</option>
                            <option value="9">Mỗi 9 tháng</option>
                            <option value="12">Mỗi 12 tháng (1 năm)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ngày hẹn thay lõi / Bảo dưỡng tiếp theo</label>
                    <input 
                        type="date" 
                        x-model="warrantyEditForm.next_maintenance_date" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-800"
                    >
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú điều kiện bảo hành / Cam kết</label>
                    <textarea 
                        x-model="warrantyEditForm.warranty_notes" 
                        rows="2" 
                        placeholder="Ví dụ: Bảo hành điện 2 năm, màng RO 1 năm, lỗi do nguồn nước cấp không bảo hành..." 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    ></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal('warrantyEditModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Lưu Cập Nhật Bảo Hành</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: TẠO MÃ QR THÔNG TIN TRỐNG (HÀNG LOẠT)                   -->
    <!-- ============================================================== -->
    <div x-show="modals.batchQrModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('batchQrModal')" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-600 text-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-qrcode text-lg text-cyan-200"></i>
                    <h3 class="font-extrabold text-sm">Tạo Lô Mã QR Máy Lọc Thông Tin Trống</h3>
                </div>
                <button type="button" @click="closeModal('batchQrModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form @submit.prevent="submitBatchQr()" class="p-5 space-y-4 text-xs">
                <div class="p-3 bg-blue-50/80 border border-blue-200/80 rounded-xl text-blue-900 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-blue-600 mr-1"></i>
                    Hệ thống sẽ tạo sẵn các tem mã QR trống. Bạn có thể in tem dán lên máy xuất xưởng. Khi KTV đi lắp đặt thực tế sẽ quét tem để nhập khách hàng và kích hoạt bảo hành.
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Số lượng tem QR cần tạo <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="number" x-model.number="batchQrForm.quantity" min="1" max="100" required
                               class="w-24 px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-center focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <div class="flex items-center gap-1">
                            <button type="button" @click="batchQrForm.quantity = 5" :class="batchQrForm.quantity == 5 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-2.5 py-1.5 rounded-lg font-bold transition">5</button>
                            <button type="button" @click="batchQrForm.quantity = 10" :class="batchQrForm.quantity == 10 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-2.5 py-1.5 rounded-lg font-bold transition">10</button>
                            <button type="button" @click="batchQrForm.quantity = 20" :class="batchQrForm.quantity == 20 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-2.5 py-1.5 rounded-lg font-bold transition">20</button>
                            <button type="button" @click="batchQrForm.quantity = 50" :class="batchQrForm.quantity == 50 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-2.5 py-1.5 rounded-lg font-bold transition">50</button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Gán trước Model máy (Tùy chọn)</label>
                    <select x-model="batchQrForm.machine_model_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium">
                        <option value="">-- Để trống (KTV sẽ chọn khi lắp đặt) --</option>
                        <template x-for="m in machineModels" :key="m.id">
                            <option :value="m.id" x-text="m.name + ' (' + (m.brand || 'Đại Sơn') + ')'"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Thời hạn bảo hành mặc định</label>
                    <div class="flex items-center gap-2">
                        <input type="number" x-model.number="batchQrForm.warranty_months" min="1" max="120"
                               class="w-24 px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-center focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <span class="text-slate-500 font-semibold">tháng (2 năm)</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú lô tem</label>
                    <input type="text" x-model="batchQrForm.admin_notes" placeholder="VD: Lô tem xuất kho tháng 10/2026..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium">
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal('batchQrModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Tạo Ngay Lô Tem QR</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: XEM MÃ QR & QUÉT THỬ                                    -->
    <!-- ============================================================== -->
    <div x-show="modals.qrPreviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('qrPreviewModal')" class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden text-center">
            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-blue-700 via-indigo-700 to-cyan-600 text-white flex items-center justify-between">
                <span class="font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-cyan-300"></i>
                    Tem QR Bảo Hành Điện Tử
                </span>
                <button type="button" @click="closeModal('qrPreviewModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-center">
                    <div id="largeQrBox" class="p-3 bg-white border-2 border-slate-800 rounded-2xl shadow-md inline-block"></div>
                </div>

                <div>
                    <div class="text-xs font-mono font-bold text-slate-500 uppercase" x-text="previewMachine?.machine_code"></div>
                    <h3 class="text-base font-extrabold text-slate-800 mt-0.5" x-text="previewMachine?.machine_model || 'Máy lọc nước'"></h3>
                    <p class="text-xs text-blue-600 font-mono font-bold mt-1" x-text="previewMachine?.qr_code"></p>
                </div>

                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-left text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Trạng thái:</span>
                        <strong class="text-slate-800" x-text="previewMachine?.status === 'unactivated' ? 'Tem trống (chưa kích hoạt)' : (previewMachine?.status === 'pending_activation' ? 'Chờ duyệt kích hoạt' : 'Đã kích hoạt')"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Khách hàng:</span>
                        <strong class="text-slate-800" x-text="previewMachine?.customer_name || '(Chưa gán)'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Số lần quét:</span>
                        <strong class="text-blue-700" x-text="(previewMachine?.view_count || 0) + ' lượt'"></strong>
                    </div>
                </div>

                <div class="space-y-2 pt-1 text-xs">
                    <a :href="'/m/' + previewMachine?.qr_token" target="_blank"
                       class="w-full py-2.5 px-4 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>Mở Trang Quét QR (/m/...)</span>
                    </a>
                    <a :href="'/bao-hanh-may-loc/print-qr?ids=' + previewMachine?.id" target="_blank"
                       class="w-full py-2 px-4 rounded-xl font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-print"></i>
                        <span>In Tem Dán Này</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: DUYỆT KÍCH HOẠT BẢO HÀNH CHO MÁY                        -->
    <!-- ============================================================== -->
    <div x-show="modals.activateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('activateModal')" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-gradient-to-r from-orange-600 to-amber-600 text-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <h3 class="font-extrabold text-sm">Phê Duyệt Kích Hoạt Bảo Hành Máy Lọc Nước</h3>
                </div>
                <button type="button" @click="closeModal('activateModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form @submit.prevent="submitActivateMachine()" class="p-5 space-y-4 text-xs">
                <!-- Info Summary submitted by technician -->
                <div class="p-3.5 bg-orange-50/70 border border-orange-200/80 rounded-xl space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800 text-sm" x-text="activateData?.customer_name || 'Khách hàng'"></span>
                        <span class="font-mono font-bold text-orange-800 bg-orange-100 px-2 py-0.5 rounded text-[11px]" x-text="activateData?.qr_code"></span>
                    </div>
                    <div class="text-slate-600 flex items-center gap-2">
                        <span>SĐT: <strong class="text-slate-900" x-text="activateData?.customer_phone"></strong></span>
                        <span>•</span>
                        <span>Model: <strong class="text-blue-700" x-text="activateData?.machine_model"></strong></span>
                    </div>
                    <div class="text-slate-500 text-[11px]">
                        Địa chỉ: <span x-text="activateData?.customer_address"></span>
                    </div>
                    <div class="text-slate-600 text-[11px] pt-1 border-t border-orange-200/60 flex items-center justify-between">
                        <span>KTV gửi: <strong class="text-slate-800" x-text="activateData?.technician_name || 'KTV'"></strong></span>
                        <template x-if="activateData?.activation_notes">
                            <span class="italic text-slate-500" x-text="'Ghi chú: ' + activateData.activation_notes"></span>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ngày lắp đặt chính thức <span class="text-rose-500">*</span></label>
                        <input type="date" x-model="activateForm.installed_at" @change="recalcActivateUntil()" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 font-bold text-slate-800">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thời hạn bảo hành</label>
                        <select x-model.number="activateForm.warranty_months" @change="recalcActivateUntil()"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 font-bold text-slate-800">
                            <option value="12">12 tháng (1 năm)</option>
                            <option value="24">24 tháng (2 năm - Chuẩn)</option>
                            <option value="36">36 tháng (3 năm)</option>
                            <option value="60">60 tháng (5 năm)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hạn bảo hành đến ngày</label>
                        <input type="date" x-model="activateForm.warranty_until"
                               class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold text-emerald-900">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã phiếu / Thẻ bảo hành</label>
                        <input type="text" x-model="activateForm.warranty_card_code" placeholder="BH-..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-mono font-bold text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số Serial máy</label>
                        <input type="text" x-model="activateForm.machine_serial" placeholder="SN-..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-mono font-bold text-slate-800 uppercase">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kỳ thay lõi định kỳ</label>
                        <select x-model.number="activateForm.filter_replacement_months"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-bold text-slate-800">
                            <option value="3">3 tháng</option>
                            <option value="6">6 tháng (Khuyến nghị)</option>
                            <option value="9">9 tháng</option>
                            <option value="12">12 tháng</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú duyệt của quản lý</label>
                    <input type="text" x-model="activateForm.admin_notes" placeholder="Đã kiểm tra thông tin, duyệt bảo hành chính hãng..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal('activateModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Phê Duyệt Kích Hoạt Ngay</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: SỬA TOÀN DIỆN THÔNG TIN MÁY LỌC NƯỚC                    -->
    <!-- ============================================================== -->
    <div x-show="modals.machineEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('machineEditModal')" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-gradient-to-r from-emerald-600 to-teal-600 text-white shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                    <div>
                        <h3 class="font-extrabold text-sm">Chỉnh Sửa Chi Tiết Máy Lọc Nước</h3>
                        <p class="text-[10px] text-emerald-100 font-mono" x-text="(machineEditForm.machine_code || '') + ' • ' + (machineEditForm.qr_code || '')"></p>
                    </div>
                </div>
                <button type="button" @click="closeModal('machineEditModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form @submit.prevent="submitMachineEdit()" class="p-5 space-y-4 text-xs overflow-y-auto flex-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Họ tên khách hàng</label>
                        <input type="text" x-model="machineEditForm.customer_name" placeholder="Nguyễn Văn A"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số điện thoại</label>
                        <input type="tel" x-model="machineEditForm.customer_phone" placeholder="0912 xxx xxx"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Địa chỉ lắp đặt</label>
                    <input type="text" x-model="machineEditForm.customer_address" placeholder="Địa chỉ cụ thể..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Model máy lọc</label>
                        <input type="text" x-model="machineEditForm.machine_model" placeholder="Model máy..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số Serial máy</label>
                        <input type="text" x-model="machineEditForm.machine_serial" placeholder="Serial máy..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium uppercase font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Trạng thái máy</label>
                        <select x-model="machineEditForm.status"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-bold text-slate-800">
                            <option value="unactivated">Tem QR trống</option>
                            <option value="pending_activation">Chờ duyệt kích hoạt</option>
                            <option value="activated">Đã kích hoạt</option>
                            <option value="expired">Hết hạn bảo hành</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">KTV phụ trách</label>
                        <select x-model="machineEditForm.technician_id"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                            <option value="">-- Chưa gán KTV --</option>
                            <template x-for="t in technicians" :key="t.id">
                                <option :value="t.id" x-text="t.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nguồn nước</label>
                        <input type="text" x-model="machineEditForm.water_source_type" placeholder="Nước máy..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ngày lắp đặt</label>
                        <input type="date" x-model="machineEditForm.installed_at" @change="recalcMachineEditUntil()"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-bold text-slate-800">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thời hạn BH (tháng)</label>
                        <input type="number" x-model.number="machineEditForm.warranty_months" @change="recalcMachineEditUntil()"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-bold text-slate-800">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hạn BH đến</label>
                        <input type="date" x-model="machineEditForm.warranty_until"
                               class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold text-emerald-900">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">TDS Đầu vào</label>
                        <input type="number" x-model.number="machineEditForm.tds_in" placeholder="ppm"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white text-center font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">TDS Tinh khiết</label>
                        <input type="number" x-model.number="machineEditForm.tds_out" placeholder="ppm"
                               class="w-full px-3 py-2 bg-emerald-50 border border-emerald-300 rounded-xl focus:bg-white text-center font-bold text-emerald-800">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Áp lực nước</label>
                        <input type="text" x-model="machineEditForm.pressure" placeholder="kg/cm²"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white text-center font-medium">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-boxes-stacked text-teal-600"></i>
                        <span>Phụ kiện kèm theo máy</span>
                    </label>
                    <input type="text" x-model="machineEditForm.accessories" placeholder="Ví dụ: Vòi ngỗng Inox SUS304, bình áp 3.2G, khoá chia nước, cờ lê mở cốc..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium text-slate-800">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú quản lý</label>
                    <textarea x-model="machineEditForm.admin_notes" rows="2" placeholder="Ghi chú thêm..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal('machineEditModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Lưu Cập Nhật Máy</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- JAVASCRIPT & ALPINE.JS LOGIC -->
<script>
function waterFilterModule() {
    return {
        activeTab: '{{ $tab ?? "installations" }}',

        // Data arrays
        installations: [],
        installedMachines: [],
        installedMachinesStats: { total: 0, active: 0, expiring_soon: 0, expired: 0 },
        repairs: [],
        parts: @json($parts ?? []),
        machineModels: @json($machineModels ?? []),
        statuses: @json($statuses ?? []),
        technicians: @json($technicians ?? []),
        transactions: [],

        // Search & Filter models
        installSearch: '',
        installStatus: '',
        installedSearch: '',
        installedWarrantyStatus: '',
        installedTechId: '',
        modelSearch: '',
        modelBrand: '',
        repairSearch: '',
        repairStatus: '',
        repairWarrantyType: '',
        partSearch: '',
        partCategory: '',
        partFilterLowStock: false,
        transSearch: '',
        transType: '',

        // Modals state
        modals: {
            installModal: false,
            inspectModal: false,
            repairModal: false,
            repairUpdateModal: false,
            partModal: false,
            importModal: false,
            machineModelModal: false,
            warrantyEditModal: false,
            batchQrModal: false,
            qrPreviewModal: false,
            activateModal: false,
            machineEditModal: false,
        },

        // QR Selection & Batch creation
        selectedMachineIds: [],
        batchQrForm: {
            quantity: 10,
            machine_model_id: '',
            warranty_months: 24,
            admin_notes: 'Tạo tem QR dán máy mới'
        },
        previewMachine: null,
        activateData: null,
        activateForm: {
            installed_at: '',
            warranty_months: 24,
            warranty_until: '',
            warranty_card_code: '',
            machine_serial: '',
            filter_replacement_months: 6,
            next_maintenance_date: '',
            admin_notes: ''
        },
        machineEditForm: {
            id: null,
            machine_code: '',
            qr_code: '',
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            water_source_type: '',
            machine_model_id: '',
            machine_model: '',
            machine_serial: '',
            accessories: '',
            technician_id: '',
            installed_at: '',
            warranty_months: 24,
            warranty_until: '',
            warranty_card_code: '',
            filter_replacement_months: 6,
            next_maintenance_date: '',
            warranty_notes: '',
            tds_in: null,
            tds_out: null,
            pressure: '',
            status: 'activated',
            admin_notes: ''
        },

        // Warranty Edit Form
        warrantyEditForm: {
            id: null,
            code: '',
            customer_name: '',
            machine_model: '',
            machine_serial: '',
            installed_at: '',
            warranty_months: 24,
            warranty_until: '',
            warranty_card_code: '',
            warranty_notes: '',
            filter_replacement_months: 6,
            next_maintenance_date: ''
        },

        // Machine Model Form
        modelForm: {
            id: null,
            code: '',
            brand: 'Karofi',
            name: '',
            filter_stages: 10,
            type: 'Tủ đứng',
            standard_price: 5000000,
            description: ''
        },

        // Forms
        installForm: {
            id: null,
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            machine_model: '',
            machine_serial: '',
            technician_id: '',
            appointment_date: '',
            service_fee: 150000,
            customer_notes: '',
            items: []
        },

        inspectData: {},
        inspectForm: {
            status: 'completed',
            tds_in: null,
            tds_out: null,
            pressure: '',
            completion_notes: ''
        },

        repairForm: {
            id: null,
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            repair_type: 'at_shop',
            warranty_type: 'out_warranty',
            machine_model: '',
            machine_serial: '',
            technician_id: '',
            symptom: '',
            labor_fee: 100000,
            discount: 0,
            items: []
        },

        repairUpdateData: {},
        repairUpdateForm: {
            status: 'completed',
            diagnosis: '',
            solution: '',
            payment_status: 'paid',
            warranty_until: '',
        },

        partForm: {
            id: null,
            code: '',
            name: '',
            category: 'Lõi lọc thô',
            unit: 'Cái',
            in_price: 0,
            out_price: 0,
            stock_qty: 0,
            min_stock_alert: 5,
            location: '',
            description: ''
        },

        importForm: {
            part_id: '',
            quantity: 10,
            in_price: 0,
            supplier: '',
            note: ''
        },

        init() {
            this.fetchInstallations();
            this.fetchInstalledMachines();
            this.fetchRepairs();
            this.fetchParts();
            this.fetchMachineModels();
            this.fetchTransactions();
        },

        changeTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        },

        openModal(name) {
            this.modals[name] = true;
        },

        closeModal(name) {
            this.modals[name] = false;
        },

        formatDateTime(str) {
            if (!str) return '-';
            const d = new Date(str);
            if (isNaN(d)) return str;
            return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        },

        /* ---------------------------------------------------------
         * INSTALLATIONS LOGIC
         * --------------------------------------------------------- */
        async fetchInstallations() {
            try {
                let url = `/api/water-filter/installations?search=${encodeURIComponent(this.installSearch)}&status=${this.installStatus}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.installations = data.installations.data || data.installations;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openInstallModal() {
            this.installForm = {
                id: null,
                customer_name: '',
                customer_phone: '',
                customer_address: '',
                machine_model: '',
                machine_serial: '',
                technician_id: '',
                appointment_date: '',
                service_fee: 150000,
                customer_notes: '',
                items: []
            };
            this.openModal('installModal');
        },

        editInstallation(item) {
            this.installForm = {
                id: item.id,
                customer_name: item.customer_name,
                customer_phone: item.customer_phone,
                customer_address: item.customer_address,
                machine_model: item.machine_model,
                machine_serial: item.machine_serial || '',
                technician_id: item.technician_id || '',
                appointment_date: item.appointment_date ? item.appointment_date.replace(' ', 'T').substring(0, 16) : '',
                service_fee: item.service_fee || 0,
                customer_notes: item.customer_notes || '',
                items: (item.items || []).map(i => ({
                    part_id: i.part_id,
                    part_name: i.part_name,
                    unit: i.unit,
                    quantity: i.quantity,
                    unit_price: i.unit_price,
                    note: i.note
                }))
            };
            this.openModal('installModal');
        },

        addInstallItemRow() {
            this.installForm.items.push({
                part_id: '',
                part_name: '',
                unit: 'Cái',
                quantity: 1,
                unit_price: 0,
                note: ''
            });
        },

        removeInstallItemRow(idx) {
            this.installForm.items.splice(idx, 1);
        },

        onSelectInstallPart(idx, partId) {
            const found = this.parts.find(p => p.id == partId);
            if (found) {
                this.installForm.items[idx].part_id = found.id;
                this.installForm.items[idx].part_name = found.name;
                this.installForm.items[idx].unit = found.unit;
                this.installForm.items[idx].unit_price = found.out_price;
            }
        },

        async saveInstallationSubmit() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/water-filter/installations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.installForm)
                });
                let data = {};
                try { data = await res.json(); } catch (err) {}

                if (!res.ok || !data.success) {
                    let errMsg = data.message || 'Lỗi lưu đơn hàng';
                    if (data.errors) {
                        errMsg += '\n• ' + Object.values(data.errors).flat().join('\n• ');
                    }
                    alert(errMsg);
                    return;
                }

                alert(data.message);
                this.closeModal('installModal');
                this.fetchInstallations();
            } catch (e) {
                alert('Có lỗi kết nối máy chủ: ' + (e.message || ''));
            }
        },

        openInspectModal(item) {
            this.inspectData = item;
            this.inspectForm = {
                status: 'completed',
                tds_in: item.tds_in || null,
                tds_out: item.tds_out || null,
                pressure: item.pressure || 'Bình thường, bơm êm',
                completion_notes: item.completion_notes || ''
            };
            this.openModal('inspectModal');
        },

        async submitInspect() {
            try {
                const res = await fetch(`/api/water-filter/installations/${this.inspectData.id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.inspectForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('inspectModal');
                    this.fetchInstallations();
                    this.fetchParts();
                    this.fetchTransactions();
                } else {
                    alert(data.message || 'Lỗi cập nhật');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        async deleteInstallation(id) {
            if (!confirm('Bạn có chắc muốn xóa đơn lắp đặt này?')) return;
            try {
                const res = await fetch(`/api/water-filter/installations/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchInstallations();
                }
            } catch (e) {
                alert('Lỗi xóa đơn');
            }
        },

        getInstallStatusBadge(st) {
            if (this.statuses && this.statuses[st]) {
                return this.statuses[st].badge;
            }
            switch (st) {
                case 'pending': return 'bg-amber-100 text-amber-800';
                case 'confirmed': return 'bg-sky-100 text-sky-800';
                case 'assigned': return 'bg-blue-100 text-blue-800';
                case 'preparing': return 'bg-indigo-100 text-indigo-800';
                case 'dispatching': return 'bg-purple-100 text-purple-800';
                case 'in_progress': return 'bg-cyan-100 text-cyan-800';
                case 'inspecting': return 'bg-teal-100 text-teal-800';
                case 'completed': return 'bg-emerald-100 text-emerald-800';
                case 'rescheduled': return 'bg-orange-100 text-orange-800';
                case 'cancelled': return 'bg-rose-100 text-rose-800';
                default: return 'bg-slate-100 text-slate-700';
            }
        },

        getInstallStatusText(st) {
            if (this.statuses && this.statuses[st]) {
                return this.statuses[st].label;
            }
            switch (st) {
                case 'pending': return 'Mới tiếp nhận';
                case 'confirmed': return 'Đã hẹn lịch';
                case 'assigned': return 'Đã giao việc';
                case 'preparing': return 'Đang chuẩn bị';
                case 'dispatching': return 'Đang di chuyển';
                case 'in_progress': return 'Đang lắp đặt';
                case 'inspecting': return 'Nghiệm thu TDS';
                case 'completed': return 'Đã hoàn thành';
                case 'rescheduled': return 'Khách hẹn lại';
                case 'cancelled': return 'Đã hủy';
                default: return st;
            }
        },

        /* ---------------------------------------------------------
         * REPAIRS LOGIC
         * --------------------------------------------------------- */
        async fetchRepairs() {
            try {
                let url = `/api/water-filter/repairs?search=${encodeURIComponent(this.repairSearch)}&status=${this.repairStatus}&warranty_type=${this.repairWarrantyType}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.repairs = data.repairs.data || data.repairs;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openRepairModal() {
            this.repairForm = {
                id: null,
                customer_name: '',
                customer_phone: '',
                customer_address: '',
                repair_type: 'at_shop',
                warranty_type: 'out_warranty',
                machine_model: '',
                machine_serial: '',
                technician_id: '',
                symptom: '',
                labor_fee: 100000,
                discount: 0,
                items: []
            };
            this.openModal('repairModal');
        },

        editRepair(item) {
            this.repairForm = {
                id: item.id,
                customer_name: item.customer_name,
                customer_phone: item.customer_phone,
                customer_address: item.customer_address || '',
                repair_type: item.repair_type,
                warranty_type: item.warranty_type,
                machine_model: item.machine_model,
                machine_serial: item.machine_serial || '',
                technician_id: item.technician_id || '',
                symptom: item.symptom,
                labor_fee: item.labor_fee || 0,
                discount: item.discount || 0,
                items: (item.items || []).map(i => ({
                    part_id: i.part_id,
                    part_name: i.part_name,
                    unit: i.unit,
                    quantity: i.quantity,
                    unit_price: i.unit_price,
                    is_warranty: !!i.is_warranty
                }))
            };
            this.openModal('repairModal');
        },

        addRepairItemRow() {
            this.repairForm.items.push({
                part_id: '',
                part_name: '',
                unit: 'Cái',
                quantity: 1,
                unit_price: 0,
                is_warranty: false
            });
        },

        removeRepairItemRow(idx) {
            this.repairForm.items.splice(idx, 1);
        },

        onSelectRepairPart(idx, partId) {
            const found = this.parts.find(p => p.id == partId);
            if (found) {
                this.repairForm.items[idx].part_id = found.id;
                this.repairForm.items[idx].part_name = found.name;
                this.repairForm.items[idx].unit = found.unit;
                this.repairForm.items[idx].unit_price = found.out_price;
            }
        },

        async saveRepairSubmit() {
            try {
                const res = await fetch('/api/water-filter/repairs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.repairForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('repairModal');
                    this.fetchRepairs();
                } else {
                    alert(data.message || 'Lỗi lưu phiếu');
                }
            } catch (e) {
                alert('Có lỗi kết nối máy chủ');
            }
        },

        openRepairUpdateModal(item) {
            this.repairUpdateData = item;
            this.repairUpdateForm = {
                status: item.status === 'completed' ? 'delivered' : 'completed',
                diagnosis: item.diagnosis || '',
                solution: item.solution || '',
                payment_status: item.payment_status || 'unpaid',
                warranty_until: item.warranty_until || '',
            };
            this.openModal('repairUpdateModal');
        },

        async submitRepairUpdate() {
            try {
                const res = await fetch(`/api/water-filter/repairs/${this.repairUpdateData.id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.repairUpdateForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('repairUpdateModal');
                    this.fetchRepairs();
                    this.fetchParts();
                    this.fetchTransactions();
                } else {
                    alert(data.message || 'Lỗi cập nhật');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        async deleteRepair(id) {
            if (!confirm('Bạn có chắc muốn xóa phiếu sửa chữa này?')) return;
            try {
                const res = await fetch(`/api/water-filter/repairs/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchRepairs();
                }
            } catch (e) {
                alert('Lỗi xóa phiếu');
            }
        },

        getRepairStatusBadge(st) {
            switch (st) {
                case 'received': return 'bg-amber-100 text-amber-800';
                case 'inspecting': return 'bg-blue-100 text-blue-800';
                case 'waiting_parts': return 'bg-purple-100 text-purple-800';
                case 'repairing': return 'bg-indigo-100 text-indigo-800';
                case 'completed': return 'bg-emerald-100 text-emerald-800';
                case 'delivered': return 'bg-teal-100 text-teal-800';
                case 'cancelled': return 'bg-rose-100 text-rose-800';
                default: return 'bg-slate-100 text-slate-700';
            }
        },

        getRepairStatusText(st) {
            switch (st) {
                case 'received': return 'Tiếp nhận';
                case 'inspecting': return 'Đang K.tra';
                case 'waiting_parts': return 'Chờ l.kiện';
                case 'repairing': return 'Đang sửa';
                case 'completed': return 'Sửa xong';
                case 'delivered': return 'Đã trả khách';
                case 'cancelled': return 'Hủy sửa';
                default: return st;
            }
        },

        /* ---------------------------------------------------------
         * PARTS WAREHOUSE LOGIC
         * --------------------------------------------------------- */
        async fetchParts() {
            try {
                let url = `/api/water-filter/parts?search=${encodeURIComponent(this.partSearch)}&category=${encodeURIComponent(this.partCategory)}&low_stock=${this.partFilterLowStock ? '1' : '0'}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.parts = data.parts;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openPartModal() {
            this.partForm = {
                id: null,
                code: '',
                name: '',
                category: 'Lõi lọc thô',
                unit: 'Cái',
                in_price: 0,
                out_price: 0,
                stock_qty: 0,
                min_stock_alert: 5,
                location: '',
                description: ''
            };
            this.openModal('partModal');
        },

        editPart(item) {
            this.partForm = {
                id: item.id,
                code: item.code,
                name: item.name,
                category: item.category,
                unit: item.unit,
                in_price: item.in_price,
                out_price: item.out_price,
                stock_qty: item.stock_qty,
                min_stock_alert: item.min_stock_alert,
                location: item.location || '',
                description: item.description || ''
            };
            this.openModal('partModal');
        },

        async savePartSubmit() {
            try {
                const res = await fetch('/api/water-filter/parts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.partForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('partModal');
                    this.fetchParts();
                    this.fetchTransactions();
                } else {
                    alert(data.message || 'Lỗi lưu phụ tùng');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        async deletePart(id) {
            if (!confirm('Bạn có chắc muốn xóa phụ tùng này?')) return;
            try {
                const res = await fetch(`/api/water-filter/parts/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchParts();
                }
            } catch (e) {
                alert('Lỗi xóa');
            }
        },

        quickImport(item) {
            this.importForm = {
                part_id: item.id,
                quantity: 10,
                in_price: item.in_price,
                supplier: '',
                note: 'Nhập bổ sung kho'
            };
            this.openModal('importModal');
        },

        onSelectImportPart(partId) {
            const found = this.parts.find(p => p.id == partId);
            if (found) {
                this.importForm.in_price = found.in_price;
            }
        },

        async submitImportStock() {
            try {
                const res = await fetch('/api/water-filter/parts/import', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.importForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('importModal');
                    this.fetchParts();
                    this.fetchTransactions();
                } else {
                    alert(data.message || 'Lỗi nhập kho');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        /* ---------------------------------------------------------
         * TRANSACTIONS LOGIC
         * --------------------------------------------------------- */
        async fetchTransactions() {
            try {
                let url = `/api/water-filter/transactions?search=${encodeURIComponent(this.transSearch)}&type=${this.transType}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.transactions = data.transactions.data || data.transactions;
                }
            } catch (e) {
                console.error(e);
            }
        },

        getTransBadge(type) {
            switch (type) {
                case 'import': return 'bg-emerald-100 text-emerald-800';
                case 'export_install': return 'bg-sky-100 text-sky-800';
                case 'export_repair': return 'bg-amber-100 text-amber-800';
                case 'return': return 'bg-purple-100 text-purple-800';
                default: return 'bg-slate-100 text-slate-700';
            }
        },

        getTransText(type) {
            switch (type) {
                case 'import': return 'Nhập kho NCC';
                case 'export_install': return 'Xuất đi lắp đặt';
                case 'export_repair': return 'Xuất sửa chữa';
                case 'return': return 'Hoàn kho phụ tùng';
                default: return type;
            }
        },

        /* ---------------------------------------------------------
         * MACHINE MODELS LOGIC
         * --------------------------------------------------------- */
        async fetchMachineModels() {
            try {
                let url = `/api/water-filter/machine-models?search=${encodeURIComponent(this.modelSearch)}&brand=${encodeURIComponent(this.modelBrand)}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.machineModels = data.models;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openMachineModelModal() {
            this.modelForm = {
                id: null,
                code: '',
                brand: 'Karofi',
                name: '',
                filter_stages: 10,
                type: 'Tủ đứng',
                standard_price: 5000000,
                description: ''
            };
            this.openModal('machineModelModal');
        },

        editMachineModel(item) {
            this.modelForm = {
                id: item.id,
                code: item.code,
                brand: item.brand || 'Karofi',
                name: item.name,
                filter_stages: item.filter_stages || 10,
                type: item.type || 'Tủ đứng',
                standard_price: item.standard_price || 0,
                description: item.description || ''
            };
            this.openModal('machineModelModal');
        },

        async saveMachineModelSubmit() {
            try {
                const res = await fetch('/api/water-filter/machine-models', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.modelForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('machineModelModal');
                    this.fetchMachineModels();
                } else {
                    alert(data.message || 'Lỗi lưu model máy');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        async deleteMachineModel(id) {
            if (!confirm('Bạn có chắc muốn xóa model máy lọc nước này?')) return;
            try {
                const res = await fetch(`/api/water-filter/machine-models/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchMachineModels();
                } else {
                    alert(data.message || 'Lỗi xóa model máy');
                }
            } catch (e) {
                alert('Lỗi xóa');
            }
        },

        /* ---------------------------------------------------------
         * INSTALLED MACHINES & QR CODE LIFECYCLE LOGIC
         * --------------------------------------------------------- */
        async fetchInstalledMachines() {
            try {
                let url = `/api/water-filter/installed-machines?search=${encodeURIComponent(this.installedSearch)}&warranty_status=${encodeURIComponent(this.installedWarrantyStatus)}&technician_id=${encodeURIComponent(this.installedTechId)}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.installedMachines = data.machines.data || data.machines;
                    if (data.stats) {
                        this.installedMachinesStats = data.stats;
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedMachineIds = this.installedMachines.map(m => m.id);
            } else {
                this.selectedMachineIds = [];
            }
        },

        printSelectedStickers() {
            if (!this.selectedMachineIds.length) {
                alert('Vui lòng chọn ít nhất 1 tem máy để in!');
                return;
            }
            window.open(`/bao-hanh-may-loc/print-qr?ids=${this.selectedMachineIds.join(',')}`, '_blank');
        },

        openBatchQrModal() {
            this.batchQrForm = {
                quantity: 10,
                machine_model_id: '',
                warranty_months: 24,
                admin_notes: 'Tạo tem QR dán máy mới'
            };
            this.openModal('batchQrModal');
        },

        async submitBatchQr() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/water-filter/installed-machines/generate-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.batchQrForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('batchQrModal');
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi tạo mã QR');
                }
            } catch (e) {
                alert('Có lỗi xảy ra khi tạo mã QR: ' + (e.message || ''));
            }
        },

        openQrPreview(item) {
            this.previewMachine = item;
            this.openModal('qrPreviewModal');
            this.$nextTick(() => {
                const container = document.getElementById('largeQrBox');
                if (container) {
                    container.innerHTML = '';
                    new QRCode(container, {
                        text: window.location.origin + '/m/' + item.qr_token,
                        width: 160,
                        height: 160,
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            });
        },

        openActivateModal(item) {
            this.activateData = item;
            const now = new Date();
            const todayStr = now.toISOString().split('T')[0];
            const wMonths = Number(item.warranty_months || 24);
            const rMonths = Number(item.filter_replacement_months || 6);

            const untilDate = new Date();
            untilDate.setMonth(untilDate.getMonth() + wMonths);
            const untilStr = untilDate.toISOString().split('T')[0];

            this.activateForm = {
                installed_at: item.installed_at_raw || todayStr,
                warranty_months: wMonths,
                warranty_until: item.warranty_until_raw || untilStr,
                warranty_card_code: item.warranty_card_code || item.qr_code,
                machine_serial: item.machine_serial || '',
                filter_replacement_months: rMonths,
                next_maintenance_date: item.next_maintenance_raw || '',
                admin_notes: 'Đã duyệt kích hoạt máy chính hãng Đại Sơn'
            };
            this.openModal('activateModal');
        },

        recalcActivateUntil() {
            if (!this.activateForm.installed_at) return;
            try {
                const parts = this.activateForm.installed_at.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + (Number(this.activateForm.warranty_months) || 24));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.activateForm.warranty_until = `${y}-${m}-${day}`;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async submitActivateMachine() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/water-filter/installed-machines/${this.activateData.id}/activate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.activateForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('activateModal');
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi duyệt kích hoạt');
                }
            } catch (e) {
                alert('Có lỗi xảy ra: ' + (e.message || ''));
            }
        },

        openMachineEditModal(item) {
            this.machineEditForm = {
                id: item.id,
                machine_code: item.machine_code,
                qr_code: item.qr_code,
                customer_name: item.customer_name || '',
                customer_phone: item.customer_phone || '',
                customer_address: item.customer_address || '',
                water_source_type: item.water_source_type || 'Nước máy thành phố',
                machine_model_id: item.machine_model_id || '',
                machine_model: item.machine_model || '',
                machine_serial: item.machine_serial || '',
                accessories: item.accessories || '',
                technician_id: item.technician_id || '',
                installed_at: item.installed_at_raw || '',
                warranty_months: Number(item.warranty_months || 24),
                warranty_until: item.warranty_until_raw || '',
                warranty_card_code: item.warranty_card_code || '',
                filter_replacement_months: Number(item.filter_replacement_months || 6),
                next_maintenance_date: item.next_maintenance_raw || '',
                warranty_notes: item.warranty_notes || '',
                tds_in: item.tds_in || null,
                tds_out: item.tds_out || null,
                pressure: item.pressure || '',
                status: item.status || 'activated',
                admin_notes: item.admin_notes || ''
            };
            this.openModal('machineEditModal');
        },

        recalcMachineEditUntil() {
            if (!this.machineEditForm.installed_at) return;
            try {
                const parts = this.machineEditForm.installed_at.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + (Number(this.machineEditForm.warranty_months) || 24));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.machineEditForm.warranty_until = `${y}-${m}-${day}`;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async submitMachineEdit() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/water-filter/installed-machines/${this.machineEditForm.id}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.machineEditForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('machineEditModal');
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi cập nhật máy');
                }
            } catch (e) {
                alert('Có lỗi xảy ra: ' + (e.message || ''));
            }
        },

        async deleteInstalledMachine(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa bản ghi máy lọc / mã QR này?')) return;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/water-filter/installed-machines/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi xóa máy');
                }
            } catch (e) {
                alert('Có lỗi xảy ra: ' + (e.message || ''));
            }
        },

        openWarrantyModal(item) {
            this.warrantyEditForm = {
                id: item.id,
                code: item.code,
                customer_name: item.customer_name,
                machine_model: item.machine_model,
                machine_serial: item.machine_serial || '',
                installed_at: item.installed_at_raw || '',
                warranty_months: Number(item.warranty_months || 24),
                warranty_until: item.warranty_until_raw || '',
                warranty_card_code: item.warranty_card_code || '',
                warranty_notes: item.warranty_notes || '',
                filter_replacement_months: Number(item.filter_replacement_months || 6),
                next_maintenance_date: item.next_maintenance_date !== '-' ? item.next_maintenance_date : ''
            };
            this.openModal('warrantyEditModal');
        },

        recalcEditWarrantyUntil() {
            if (!this.warrantyEditForm.installed_at) return;
            try {
                const parts = this.warrantyEditForm.installed_at.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + (Number(this.warrantyEditForm.warranty_months) || 24));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.warrantyEditForm.warranty_until = `${y}-${m}-${day}`;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async saveWarrantySubmit() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/water-filter/installed-machines/${this.warrantyEditForm.id}/warranty`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.warrantyEditForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('warrantyEditModal');
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi lưu bảo hành máy');
                }
            } catch (e) {
                alert('Có lỗi xảy ra khi cập nhật bảo hành: ' + (e.message || ''));
            }
        },

        quickCreateRepairFromInstalled(item) {
            const isWarrantyActive = (item.warranty_status === 'active' || item.warranty_status === 'expiring_soon');
            this.repairForm = {
                id: null,
                customer_name: item.customer_name,
                customer_phone: item.customer_phone,
                customer_address: item.customer_address,
                repair_type: 'at_home',
                warranty_type: isWarrantyActive ? 'in_warranty' : 'out_warranty',
                machine_model: item.machine_model,
                machine_serial: item.machine_serial || '',
                technician_id: item.technician_id || '',
                symptom: isWarrantyActive ? `Bảo hành định kỳ máy lọc [${item.machine_model}] (Mã BH: ${item.warranty_card_code || item.code})` : `Kiểm tra sửa chữa máy lọc [${item.machine_model}]`,
                labor_fee: isWarrantyActive ? 0 : 100000,
                discount: 0,
                items: []
            };
            this.openModal('repairModal');
        },

        getWarrantyBadgeClass(st) {
            switch (st) {
                case 'active': return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                case 'expiring_soon': return 'bg-amber-100 text-amber-800 border border-amber-300 animate-pulse';
                case 'expired': return 'bg-rose-100 text-rose-800 border border-rose-300';
                default: return 'bg-slate-100 text-slate-700 border border-slate-300';
            }
        },

        getWarrantyStatusLabel(st) {
            switch (st) {
                case 'active': return 'Còn bảo hành';
                case 'expiring_soon': return 'Sắp hết hạn';
                case 'expired': return 'Hết hạn bảo hành';
                default: return 'Chưa kích hoạt';
            }
        }
    };
}
</script>
@endsection
