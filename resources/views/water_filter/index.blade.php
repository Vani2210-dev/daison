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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5">
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
            <div class="text-[11px] text-slate-400 mt-1">Theo dõi hạn BH & tem QR</div>
        </div>

        <!-- KPI 3: Lịch bảo dưỡng & Thay lõi -->
        <div @click="changeTab('maintenance')" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs cursor-pointer hover:border-purple-400 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Lịch bảo dưỡng</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="text-2xl font-black text-purple-700" x-text="maintenanceStats.total || '{{ $maintenanceDueCount ?? 0 }}'"></div>
                <template x-if="maintenanceStats.overdue > 0">
                    <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full" x-text="maintenanceStats.overdue + ' quá hạn'"></span>
                </template>
                <template x-if="!maintenanceStats.overdue || maintenanceStats.overdue == 0">
                    <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full" x-text="(maintenanceStats.due_30_days || 0) + ' sắp tới'"></span>
                </template>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Theo dõi thay lõi & nhắc khách</div>
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
            <button type="button" @click="changeTab('maintenance')" :class="activeTab === 'maintenance' ? 'text-purple-700 border-b-2 border-purple-600 bg-white' : 'text-slate-500 hover:text-slate-800'" class="px-5 py-3.5 flex items-center gap-2 transition cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-calendar-check text-sm text-purple-600"></i>
                <span>Lịch Bảo Dưỡng & Thay Lõi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-700" x-text="maintenanceStats.total || '{{ $maintenanceDueCount ?? 0 }}'"></span>
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
        <!-- TAB CONTENTS                                                   -->
        <!-- ============================================================== -->
        @include('water_filter.tabs.tab_installations')
        @include('water_filter.tabs.tab_installed_machines')
        @include('water_filter.tabs.tab_maintenance')
        @include('water_filter.tabs.tab_repairs')
        @include('water_filter.tabs.tab_parts')
        @include('water_filter.tabs.tab_models')
        @include('water_filter.tabs.tab_transactions')
    </div>

    <!-- ============================================================== -->
    <!-- MODALS                                                         -->
    <!-- ============================================================== -->
    @include('water_filter.modals.modal_install')
    @include('water_filter.modals.modal_inspect')
    @include('water_filter.modals.modal_repair')
    @include('water_filter.modals.modal_repair_update')
    @include('water_filter.modals.modal_part')
    @include('water_filter.modals.modal_import')
    @include('water_filter.modals.modal_machine_model')
    @include('water_filter.modals.modal_warranty_edit')
    @include('water_filter.modals.modal_batch_qr')
    @include('water_filter.modals.modal_qr_preview')
    @include('water_filter.modals.modal_activate')
    @include('water_filter.modals.modal_machine_edit')
    @include('water_filter.modals.modal_maintenance_reschedule')
    @include('water_filter.modals.modal_maintenance_reminder')
</div>

<!-- ============================================================== -->
<!-- JAVASCRIPT & ALPINE.JS LOGIC                                   -->
<!-- ============================================================== -->
@include('water_filter.partials.scripts')
@endsection
