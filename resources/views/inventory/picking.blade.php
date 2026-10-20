@extends('layouts.app')

@section('title', 'Soạn Đơn & Kiểm Hàng Xuất Kho | ERP Đại Sơn')

@section('content')
<div x-data="pickingApp()" x-init="initApp()" class="space-y-4 max-w-[1920px] mx-auto pb-12">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-[9999] px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-bold border max-w-lg"
         :class="toast.type === 'success' ? 'bg-emerald-600 text-white border-emerald-500' : (toast.type === 'warning' ? 'bg-amber-600 text-white border-amber-500 ring-4 ring-amber-400/30' : 'bg-rose-600 text-white border-rose-500')">
        <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check text-lg shrink-0' : (toast.type === 'warning' ? 'fa-solid fa-triangle-exclamation text-lg shrink-0 animate-bounce' : 'fa-solid fa-circle-exclamation text-lg shrink-0')"></i>
        <div class="space-y-0.5 leading-snug">
            <span x-text="toast.message" class="block"></span>
        </div>
    </div>

    <!-- THÔNG BÁO NỔI REALTIME (Floating Notification Popup góc trên màn hình, không choán diện tích giao diện) -->
    <div x-show="realtimeAlert" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
         class="fixed top-5 right-5 z-[99999] max-w-md w-full drop-shadow-2xl pointer-events-auto">
        <div class="bg-gradient-to-r from-amber-600 via-rose-600 to-amber-700 text-white p-4 rounded-2xl shadow-2xl border-2 border-white/40 ring-4 ring-rose-500/25 flex flex-col gap-2.5">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white text-rose-600 flex items-center justify-center shrink-0 shadow-md text-xl animate-bounce">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="px-2 py-0.5 rounded-full bg-black/25 font-black text-[10px] uppercase tracking-wider text-amber-200 border border-amber-300/30">
                                Cập nhật đơn hàng
                            </span>
                            <span class="text-sm font-bold text-white">
                                Đơn <strong class="font-mono font-black text-amber-200 underline" x-text="realtimeAlert ? realtimeAlert.orderCode : ''"></strong> vừa có cập nhật mới!
                            </span>
                        </div>
                        <div class="text-xs text-white/90 flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1">
                            <span x-show="realtimeAlert && realtimeAlert.time">Lúc: <strong class="font-mono text-amber-100" x-text="realtimeAlert ? realtimeAlert.time : ''"></strong></span>
                            <span x-show="realtimeAlert && realtimeAlert.performer">Bởi: <strong class="text-amber-100" x-text="realtimeAlert ? realtimeAlert.performer : ''"></strong></span>
                        </div>
                        <div class="text-[11px] text-amber-100 mt-0.5" x-show="realtimeAlert && realtimeAlert.supervisor">
                            Phụ trách đơn: <strong class="text-white underline font-bold" x-text="realtimeAlert ? realtimeAlert.supervisor : ''"></strong>
                        </div>
                    </div>
                </div>
                <button type="button" @click="realtimeAlert = null" class="text-white/80 hover:text-white text-xl font-bold p-1 cursor-pointer leading-none">&times;</button>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1 border-t border-white/20">
                <button type="button" @click="goToAlertOrder(realtimeAlert)"
                        class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-rose-700 font-black text-xs shadow transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                    <span>Xem ngay đơn này</span>
                </button>
                <button type="button" @click="realtimeAlert = null"
                        class="px-3 py-1.5 rounded-xl bg-black/30 hover:bg-black/50 text-white font-bold text-xs transition-all cursor-pointer">
                    <span>Đã nhận TT &times;</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 1. Page Header (Exact style from mockup) -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/25">
                <i class="fa-solid fa-clipboard-check text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Soạn Đơn & Kiểm Hàng Xuất Kho</span>
                </h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Quy trình 2 bước: 1. Soạn & bốc hàng (tách đơn hoặc gộp đơn) - 2. Kiểm hàng đối chiếu trước khi bàn giao xe xuất kho
                </p>
            </div>
        </div>

        <!-- Top Right Filter & Badges -->
        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
            <!-- Branch Filter -->
            <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                <i class="fa-solid fa-building text-slate-400"></i>
                <span class="text-slate-500 font-medium">Chi nhánh:</span>
                <select x-model="selectedBranch" class="bg-transparent font-bold text-slate-800 focus:outline-none cursor-pointer">
                    <option value="">Toàn bộ chi nhánh</option>
                    @foreach($branches as $br)
                        <option value="{{ $br->name }}">{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Filter: Bộ lọc ngày soạn hàng (Mặc định hôm nay) -->
            <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                <i class="fa-solid fa-calendar-day text-amber-600"></i>
                <span class="text-slate-500 font-medium">Lọc ngày soạn:</span>
                <input type="date" x-model="filterPickingDate"
                       class="bg-transparent font-bold font-mono text-slate-800 focus:outline-none cursor-pointer text-xs">
                <button type="button" x-show="filterPickingDate === '{{ date('Y-m-d') }}'" @click="filterPickingDate = ''"
                        class="text-[10px] font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 px-2 py-0.5 rounded-md cursor-pointer transition-colors" title="Đang lọc hôm nay. Nhấn để xem tất cả ngày">
                    Hôm nay
                </button>
                <button type="button" x-show="filterPickingDate && filterPickingDate !== '{{ date('Y-m-d') }}'" @click="filterPickingDate = '{{ date('Y-m-d') }}'"
                        class="text-[10px] font-bold text-slate-600 bg-slate-200 hover:bg-slate-300 px-2 py-0.5 rounded-md cursor-pointer transition-colors" title="Chuyển về hôm nay">
                    Về hôm nay
                </button>
                <button type="button" x-show="filterPickingDate" @click="filterPickingDate = ''"
                        class="text-slate-400 hover:text-rose-600 ml-0.5 text-xs cursor-pointer" title="Xem tất cả các ngày">
                    <i class="fa-solid fa-circle-xmark"></i>
                </button>
            </div>

            <!-- Badges -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                <i class="fa-solid fa-clock text-amber-600"></i>
                <span x-text="countPendingPick + ' đơn chờ bốc'"></span>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                <span x-text="countReadyQc + ' đơn sẵn sàng kiểm'"></span>
            </div>

            <button type="button" @click="setOrderTypeFilter(filterOrderType === 'consignment' ? 'all' : 'consignment')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border"
                    :class="filterOrderType === 'consignment' ? 'bg-amber-600 text-white border-amber-700 shadow-2xs ring-2 ring-amber-500/30' : 'bg-white hover:bg-amber-50 border-amber-300 text-amber-900 shadow-2xs'"
                    title="Nhấn để xem riêng danh sách đơn hàng ôm của nhân viên">
                <i class="fa-solid fa-boxes-stacked text-amber-600" :class="{'text-white': filterOrderType === 'consignment'}"></i>
                <span x-text="countConsignmentOrders + ' đơn hàng ôm'"></span>
            </button>
        </div>
    </div>

    <!-- Category & Staff Group Authorization Banner -->
    <div class="bg-white rounded-2xl p-3.5 sm:px-5 border border-slate-200/90 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Mode 1: Có quyền xem bốc xếp không phân loại sản phẩm (Toàn quyền mọi loại sản phẩm) -->
            <template x-if="canViewAllCategories">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-900 font-bold">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    <i class="fa-solid fa-layer-group text-indigo-600 text-sm"></i>
                    <span>Quyền xem bốc xếp: <strong class="text-indigo-950 font-black">Không phân loại sản phẩm (Toàn bộ ngành hàng)</strong></span>
                    <span class="px-2 py-0.5 rounded-md bg-indigo-200/70 text-indigo-900 font-extrabold text-[10px] uppercase">Toàn quyền</span>
                </div>
            </template>

            <!-- Mode 2: Phân loại theo Nhóm nhân sự phụ trách -->
            <template x-if="!canViewAllCategories">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 font-bold">
                        <i class="fa-solid fa-users-gear text-amber-600 text-sm"></i>
                        <span>Nhóm của bạn: <strong class="text-amber-900 font-black" x-text="userGroupsSummary"></strong></span>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-950 font-bold">
                        <i class="fa-solid fa-boxes-stacked text-sky-600 text-sm"></i>
                        <span>Phụ trách loại SP: <strong class="text-sky-900 font-black" x-text="userCategoriesSummary"></strong></span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[11px] font-bold"
                          :class="(Array.isArray(leaderCategoryIds) ? leaderCategoryIds.length : Object.keys(leaderCategoryIds || {}).length) > 0 ? 'bg-purple-50 text-purple-900 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200'">
                        <i :class="(Array.isArray(leaderCategoryIds) ? leaderCategoryIds.length : Object.keys(leaderCategoryIds || {}).length) > 0 ? 'fa-solid fa-crown text-purple-600' : 'fa-solid fa-user text-slate-500'"></i>
                        <span x-text="(Array.isArray(leaderCategoryIds) ? leaderCategoryIds.length : Object.keys(leaderCategoryIds || {}).length) > 0 ? 'Vai trò: Trưởng nhóm (Được quyền phân người soạn)' : 'Vai trò: Thành viên (Tự nhận khi chưa có người)'"></span>
                    </div>
                </div>
            </template>
        </div>

        <div class="text-slate-500 text-[11px] flex items-center gap-2 font-medium" x-show="userCategoryIds.length === 0 && !canViewAllCategories">
            <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold border border-rose-200 flex items-center gap-1">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Chưa được gán nhóm phụ trách loại sản phẩm. Vui lòng liên hệ Quản lý kho.</span>
            </span>
        </div>
    </div>

    <!-- 2. Main Process Tabs -->
    <div class="flex items-center gap-3">
        <!-- Tab 1: Soạn & Bốc Hàng -->
        <button type="button" @click="switchTab('picking')"
                class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center gap-2 cursor-pointer shadow-xs"
                :class="activeTab === 'picking' ? 'bg-amber-500 text-white shadow-amber-500/25 ring-2 ring-amber-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'">
            <i class="fa-solid fa-box-open"></i>
            <span>1. Soạn & Bốc Hàng</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-black"
                  :class="activeTab === 'picking' ? 'bg-black/20 text-white' : 'bg-amber-100 text-amber-800'"
                  x-text="countPendingPick"></span>
        </button>

        <!-- Tab 2: Kiểm Hàng (QC Soạn Đơn) -->
        <button type="button" @click="switchTab('qc')"
                class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center gap-2 cursor-pointer shadow-xs"
                :class="activeTab === 'qc' ? 'bg-emerald-600 text-white shadow-emerald-600/25 ring-2 ring-emerald-600/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'">
            <i class="fa-solid fa-circle-check"></i>
            <span>2. Kiểm Hàng (QC Soạn Đơn)</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-black"
                  :class="activeTab === 'qc' ? 'bg-black/20 text-white' : 'bg-emerald-100 text-emerald-800'"
                  x-text="countReadyQc"></span>
        </button>
    </div>

    <!-- 3. Sub-toolbar: Chế độ soạn / kiểm kho & Mã đơn đang xem -->
    <div class="bg-white rounded-2xl px-5 py-3 border border-slate-200/90 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
        <div class="flex items-center gap-4">
            <span class="font-bold text-slate-600" x-text="activeTab === 'picking' ? 'Chế độ soạn kho:' : 'Chế độ kiểm kho:'"></span>
            
            <div class="inline-flex rounded-xl p-0.5 bg-slate-100 border border-slate-200">
                <button type="button" @click="fulfillmentMode = 'single'"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                        :class="fulfillmentMode === 'single' ? 'bg-white text-sky-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                    <i class="fa-solid fa-list-check text-xs"></i>
                    <span>Tách từng đơn lẻ</span>
                </button>
                <button type="button" @click="fulfillmentMode = 'batch'"
                        class="px-3 py-1.5 rounded-lg font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                        :class="fulfillmentMode === 'batch' ? 'bg-white text-sky-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                    <span x-text="activeTab === 'picking' ? 'Gộp nhiều đơn (Soạn tổng hợp)' : 'Gộp nhiều đơn (Kiểm tra tổng hợp)'"></span>
                </button>
            </div>
        </div>

        <!-- Right Label: Viewing Order Code (single) OR Batch selection count (batch) -->
        <div x-show="fulfillmentMode === 'single' && activeOrder" class="text-slate-500 font-medium">
            <span x-text="activeTab === 'picking' ? 'Đang xem chi tiết đơn: ' : 'Đang kiểm tra chi tiết đơn: '"></span>
            <span class="font-mono font-black text-slate-800 text-sm bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200" x-text="activeOrder ? activeOrder.order_code : ''"></span>
        </div>
        <div x-show="fulfillmentMode === 'batch'" class="text-slate-600 font-medium">
            <span class="px-3 py-1.5 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 font-bold text-xs shadow-2xs"
                  x-text="'Đang chọn gộp: ' + selectedBatchOrderIds.length + ' / ' + filteredOrders.length + ' đơn'"></span>
        </div>
    </div>

    <!-- 4. Main Two-Column Layout (Order List Sidebar + Workspace) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ========================================== -->
        <!-- LEFT COLUMN: Order List Sidebar (3.5 cols) -->
        <!-- ========================================== -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-3">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs space-y-3.5">
                
                <!-- Sidebar Header -->
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <template x-if="fulfillmentMode === 'batch'">
                        <div class="flex items-center justify-between w-full">
                            <h2 class="font-black text-slate-900 text-sm flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-sky-600"></i>
                                <span x-text="'Chọn Đơn Gộp (' + selectedBatchOrderIds.length + '/' + filteredOrders.length + ')'"></span>
                            </h2>
                            <span class="px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200 font-mono text-[11px] font-bold"
                                  x-text="filteredOrders.length + ' hiển thị'"></span>
                        </div>
                    </template>
                    <template x-if="fulfillmentMode === 'single'">
                        <div class="flex items-center justify-between w-full">
                            <h2 class="font-black text-slate-900 text-sm flex items-center gap-2">
                                <i :class="activeTab === 'picking' ? 'fa-solid fa-boxes-packing text-amber-500' : 'fa-solid fa-clipboard-check text-emerald-600'"></i>
                                <span x-text="activeTab === 'picking' ? 'Đơn Hàng Chờ Soạn' : 'Đơn Hàng Chờ Kiểm'"></span>
                            </h2>
                            <span class="font-mono text-xs font-bold text-slate-500"
                                  x-text="filteredOrders.length + '/' + totalOrdersCount + ' đơn'"></span>
                        </div>
                    </template>
                </div>

                <!-- Search Input -->
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery"
                           placeholder="Tìm mã đơn, khách hàng, kho..."
                           class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 font-medium text-slate-800 bg-slate-50/70 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all placeholder:text-slate-400">
                </div>

                <!-- Order Type Filter: Lọc loại đơn (Tất cả / Đơn bán hàng / Đơn hàng ôm) -->
                <div class="grid grid-cols-3 gap-1 bg-slate-100 p-1 rounded-xl text-[11px] font-bold">
                    <button type="button" @click="setOrderTypeFilter('all')" 
                            class="py-1 px-1 rounded-lg text-center transition-all cursor-pointer truncate"
                            :class="filterOrderType === 'all' ? 'bg-white text-slate-900 shadow-2xs font-black' : 'text-slate-600 hover:text-slate-900'">
                        Tất cả (<span x-text="orders.length"></span>)
                    </button>
                    <button type="button" @click="setOrderTypeFilter('sales')" 
                            class="py-1 px-1 rounded-lg text-center transition-all cursor-pointer truncate"
                            :class="filterOrderType === 'sales' ? 'bg-white text-sky-700 shadow-2xs font-black' : 'text-slate-600 hover:text-slate-900'">
                        Đơn bán (<span x-text="countSalesOrders"></span>)
                    </button>
                    <button type="button" @click="setOrderTypeFilter('consignment')" 
                            class="py-1 px-1 rounded-lg text-center transition-all cursor-pointer truncate flex items-center justify-center gap-1"
                            :class="filterOrderType === 'consignment' ? 'bg-amber-600 text-white shadow-2xs font-black' : 'text-amber-800 hover:text-amber-900 bg-amber-50/80'">
                        <i class="fa-solid fa-boxes-stacked text-[10px]"></i>
                        <span>Hàng ôm (<span x-text="countConsignmentOrders"></span>)</span>
                    </button>
                </div>

                <!-- Date Filter: Bộ lọc ngày soạn hàng -->
                <div class="bg-slate-50/80 p-2.5 rounded-xl border border-slate-200/80 space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-day text-amber-600 text-xs"></i>
                            <span>Lọc ngày soạn hàng:</span>
                        </span>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="filterPickingDate = '{{ date('Y-m-d') }}'" 
                                    class="px-2 py-0.5 rounded text-[10px] font-bold border transition-colors cursor-pointer"
                                    :class="filterPickingDate === '{{ date('Y-m-d') }}' ? 'bg-amber-500 text-white border-amber-600 shadow-2xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'">
                                Hôm nay
                            </button>
                            <button type="button" @click="filterPickingDate = ''" 
                                    class="px-2 py-0.5 rounded text-[10px] font-bold border transition-colors cursor-pointer"
                                    :class="!filterPickingDate ? 'bg-sky-600 text-white border-sky-700 shadow-2xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'">
                                Tất cả
                            </button>
                        </div>
                    </div>
                    <div class="relative">
                        <input type="date" x-model="filterPickingDate"
                               class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 font-mono font-bold text-slate-800 bg-white focus:outline-none focus:border-amber-500 transition-all cursor-pointer">
                    </div>
                </div>

                <!-- Batch Action Buttons: Chọn tất cả & Bỏ chọn lọc -->
                <div x-show="fulfillmentMode === 'batch'" class="grid grid-cols-2 gap-2 pt-1 pb-1">
                    <button type="button" @click="selectAllBatchOrders()"
                            class="px-2.5 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 font-bold text-xs transition-colors cursor-pointer text-center"
                            x-text="'Chọn tất cả (' + filteredOrders.length + ')'">
                    </button>
                    <button type="button" @click="deselectAllBatchOrders()"
                            class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 font-bold text-xs transition-colors cursor-pointer text-center">
                        Bỏ chọn lọc
                    </button>
                </div>

                <!-- Order Cards Scrollable List -->
                <div class="space-y-2.5 max-h-[calc(100vh-330px)] overflow-y-auto pr-1">
                    <template x-for="ord in filteredOrders" :key="ord.id">
                        <div @click="fulfillmentMode === 'batch' ? toggleBatchOrder(ord.id) : selectOrder(ord)"
                             class="p-3.5 rounded-2xl border transition-all cursor-pointer relative space-y-2"
                             :class="fulfillmentMode === 'batch'
                                ? (selectedBatchOrderIds.includes(ord.id) ? 'border-sky-400 bg-sky-50/60 ring-2 ring-sky-500/20 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/80 shadow-2xs')
                                : (activeOrderId === ord.id 
                                    ? (activeTab === 'picking' ? 'border-sky-400 bg-sky-50/60 ring-2 ring-sky-500/20 shadow-xs' : 'border-emerald-400 bg-emerald-50/50 ring-2 ring-emerald-500/20 shadow-xs')
                                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/80 shadow-2xs')">

                            <div class="flex items-start gap-2.5">
                                <!-- Checkbox when in batch mode -->
                                <div x-show="fulfillmentMode === 'batch'" class="pt-0.5 shrink-0">
                                    <input type="checkbox"
                                           :checked="selectedBatchOrderIds.includes(ord.id)"
                                           @click.stop="toggleBatchOrder(ord.id)"
                                           class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500 cursor-pointer">
                                </div>

                                <div class="flex-1 space-y-2 min-w-0">
                                    <!-- Row 1: Code & Status Badge & Modified Badge -->
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono font-black text-xs text-sky-700 tracking-tight" x-text="ord.order_code"></span>
                                            <template x-if="ord.is_consignment">
                                                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-[9px] flex items-center gap-1">
                                                    <i class="fa-solid fa-boxes-stacked text-[8px] text-amber-600"></i>
                                                    <span>Hàng Ôm</span>
                                                </span>
                                            </template>
                                            <template x-if="ord._hasItemsModified">
                                                <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-black text-[9px] animate-pulse shadow-2xs">
                                                    ⚠️ Sửa SL
                                                </span>
                                            </template>
                                        </div>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0"
                                              :class="getOrderStatusBadgeClass(ord.status)"
                                              x-text="ord.status"></span>
                                    </div>

                                    <!-- Row 2: Customer Name & Category Badges -->
                                    <div class="flex items-center justify-between gap-1 flex-wrap">
                                        <div class="font-bold text-slate-900 text-xs line-clamp-1" x-text="ord.customer_name"></div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <template x-for="catName in getOrderCategoryNames(ord)" :key="catName">
                                                <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 font-bold text-[9px] border border-slate-200" x-text="catName"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Row 3: Warehouse & Date -->
                                    <div class="text-[11px] text-slate-500 flex items-center justify-between">
                                        <span class="truncate max-w-[150px]" x-text="typeof ord.warehouse === 'string' ? ord.warehouse : (ord.warehouse?.name || 'Kho Tổng Mê Linh (MQ)')"></span>
                                        <div class="text-right shrink-0">
                                            <template x-if="ord.delivery_date">
                                                <span class="font-mono text-[10px] text-emerald-700 font-bold block" x-text="'Giao: ' + formatDate(ord.delivery_date)"></span>
                                            </template>
                                            <template x-if="ord.picking_date">
                                                <span class="font-mono text-[10px] text-amber-700 font-bold block" x-text="'Soạn: ' + formatDate(ord.picking_date)"></span>
                                            </template>
                                            <template x-if="!ord.picking_date">
                                                <span class="font-mono text-[10px] text-rose-600 font-bold block">Chưa có ngày soạn</span>
                                            </template>
                                            <span class="font-mono text-[10px] text-slate-400 block" x-text="formatDate(ord.created_at)"></span>
                                        </div>
                                    </div>

                                    <!-- Row 4: Assigned Staff & Item Count -->
                                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                                        <div class="flex items-center gap-1.5 text-slate-600 truncate max-w-[180px]">
                                            <template x-if="getAssignedStaffText(ord) !== 'Chưa phân công'">
                                                <div class="flex items-center gap-1 text-slate-700 font-semibold truncate">
                                                    <i class="fa-solid fa-user-check text-sky-600 text-[10px]"></i>
                                                    <span class="truncate" x-text="getAssignedStaffText(ord)"></span>
                                                </div>
                                            </template>
                                            <template x-if="getAssignedStaffText(ord) === 'Chưa phân công'">
                                                <div class="flex items-center gap-1 text-amber-700 font-semibold">
                                                    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-[10px]"></i>
                                                    <span>Chưa phân công ai soạn</span>
                                                </div>
                                            </template>
                                        </div>
                                        <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-600 font-mono"
                                              x-text="getAssignedItemsCount(ord) + '/' + ord.items.length + ' món'"></span>
                                    </div>

                                    <!-- Row 5: Progress Bar & Items ratio -->
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-[10px] text-slate-500 font-semibold">
                                            <span x-text="activeTab === 'picking' ? 'Tiến độ bốc hàng:' : 'Tiến độ kiểm hàng:'"></span>
                                            <span class="font-mono font-bold text-slate-700"
                                                  x-text="getProgressRatio(ord)"></span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-300 rounded-full"
                                                 :class="activeTab === 'picking' ? 'bg-amber-500' : 'bg-emerald-500'"
                                                 :style="'width: ' + getProgressPercent(ord) + '%'"></div>
                                        </div>
                                    </div>

                                    <!-- Row 6: Multi-Group Progress Badges -->
                                    <template x-if="ord.groups_progress && ord.groups_progress.length > 0">
                                        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                                            <template x-for="grp in ord.groups_progress" :key="grp.group_id">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold border transition-colors"
                                                      :class="(activeTab === 'picking' ? grp.is_picking_completed : grp.is_qc_completed) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (grp.has_shortage ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-200')">
                                                    <i class="fa-solid" :class="(activeTab === 'picking' ? grp.is_picking_completed : grp.is_qc_completed) ? 'fa-check text-emerald-600' : (grp.has_shortage ? 'fa-triangle-exclamation text-rose-500' : 'fa-clock text-amber-500')"></i>
                                                    <span x-text="grp.group_name"></span>:
                                                    <span class="font-mono font-black" x-text="(activeTab === 'picking' ? grp.picked_count : grp.checked_count) + '/' + grp.items_count"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="filteredOrders.length === 0" class="py-10 text-center text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        Không tìm thấy đơn hàng nào phù hợp bộ lọc.
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: Order Detail Workspace (8.5 cols) -->
        <!-- ========================================== -->
        <div class="lg:col-span-8 xl:col-span-9">

            <!-- Active Order Single Mode -->
            <template x-if="fulfillmentMode === 'single' && activeOrder">
                <div>

                    <!-- =============================================== -->
                    <!-- TAB 1 CONTENT: 1. SOẠN & BỐC HÀNG WORKSPACE    -->
                    <!-- =============================================== -->
                    <div x-show="activeTab === 'picking'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mt-0">
                        
                        <!-- Panel Header -->
                        <div class="px-5 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2 flex-wrap">
                                    <span class="text-sky-700 font-mono" x-text="activeOrder.order_code"></span>
                                    <template x-if="activeOrder.is_consignment">
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-xs inline-flex items-center gap-1">
                                            <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                                            <span>Đơn Hàng Ôm (Xuất NVKD)</span>
                                        </span>
                                    </template>
                                    <span class="text-slate-400">|</span>
                                    <span x-text="activeOrder.customer_name"></span>
                                </h3>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-500 mt-1">
                                    <span>Địa điểm kho: <strong class="text-slate-700 font-bold" x-text="activeOrder.warehouse || 'Kho Tổng Mê Linh (MQ)'"></strong></span>
                                    <span>·</span>
                                    <span>Ngày lên đơn: <strong class="text-slate-700 font-bold font-mono" x-text="formatDate(activeOrder.created_at)"></strong></span>
                                    <template x-if="activeOrder.delivery_date">
                                        <div class="flex items-center gap-1.5">
                                            <span>·</span>
                                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-900 px-2 py-0.5 rounded-lg border border-emerald-200">
                                                <i class="fa-solid fa-truck-fast text-emerald-600 text-xs"></i>
                                                <span class="font-bold">Ngày giao hàng:</span>
                                                <strong class="font-mono font-bold text-emerald-950" x-text="formatDate(activeOrder.delivery_date)"></strong>
                                            </span>
                                        </div>
                                    </template>
                                    <template x-if="activeOrder.picking_date">
                                        <div class="flex items-center gap-1.5">
                                            <span>·</span>
                                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-900 px-2 py-0.5 rounded-lg border border-amber-200">
                                                <i class="fa-solid fa-calendar-day text-amber-600 text-xs"></i>
                                                <span class="font-bold">Ngày soạn hàng:</span>
                                                <strong class="font-mono font-bold text-amber-950" x-text="formatDate(activeOrder.picking_date)"></strong>
                                            </span>
                                        </div>
                                    </template>
                                    <template x-if="!activeOrder.picking_date">
                                        <div class="flex items-center gap-1.5">
                                            <span>·</span>
                                            <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-800 px-2 py-0.5 rounded-lg border border-rose-200">
                                                <i class="fa-solid fa-calendar-xmark text-rose-600 text-xs"></i>
                                                <span class="font-bold">Chưa có ngày soạn hàng</span>
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2.5 shrink-0">
                                <!-- Bốc đủ nhanh -->
                                <button type="button" @click="quickPickAllActiveOrder()"
                                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-300 transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                    <i class="fa-solid fa-bolt-lightning text-sky-600"></i>
                                    <span x-text="'Bốc đủ nhanh (' + activeOrder.items.length + ')'"></span>
                                </button>

                                <!-- Hoàn Tất Bốc & Chuyển Kiểm -->
                                <button type="button" @click="completePickingAndSendToQc()"
                                        class="px-4 py-2 rounded-xl text-xs font-black text-white bg-amber-500 hover:bg-amber-600 transition-all flex items-center gap-2 cursor-pointer shadow-md shadow-amber-500/20">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Hoàn Tất Bốc & Chuyển Kiểm</span>
                                </button>
                            </div>
                        </div>

                        <!-- GROUP-LEVEL PROGRESS TRACKER (Tiến độ theo từng nhóm bốc hàng) -->
                        <template x-if="activeOrder && activeOrder.groups_progress && activeOrder.groups_progress.length > 0">
                            <div class="m-5 mb-1 p-4 rounded-2xl bg-gradient-to-r from-slate-50 via-sky-50/40 to-slate-50 border border-slate-200 shadow-2xs space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200/80 pb-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                            <i class="fa-solid fa-users-gear text-sm"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                                                <span>Tiến Độ Theo Nhóm Bốc Hàng & Kiểm Hàng</span>
                                                <span class="text-[10px] font-bold text-slate-400 font-mono" x-text="'(' + activeOrder.groups_progress.length + ' nhóm phụ trách)'"></span>
                                            </h4>
                                            <p class="text-[11px] text-slate-500 font-medium">
                                                Soạn & kiểm hàng theo loại SP (ví dụ: phụ kiện bốc xong là kiểm được ngay, không cần chờ loại khác). Đơn hàng hoàn tất khi <strong class="text-sky-700 font-bold">100% các nhóm</strong> hoàn thành.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Overall Completion Status Badge -->
                                    <div class="shrink-0">
                                        <template x-if="activeTab === 'picking'">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border shadow-2xs transition-all"
                                                  :class="isAllGroupsPickingCompleted(activeOrder) ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse'">
                                                <i class="fa-solid" :class="isAllGroupsPickingCompleted(activeOrder) ? 'fa-circle-check text-emerald-600' : 'fa-hourglass-half text-amber-600'"></i>
                                                <span x-text="isAllGroupsPickingCompleted(activeOrder) ? '100% CÁC NHÓM ĐÃ BỐC XONG' : 'ĐANG CHỜ CÁC NHÓM HOÀN TẤT'"></span>
                                            </span>
                                        </template>
                                        <template x-if="activeTab === 'qc'">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border shadow-2xs transition-all"
                                                  :class="isAllGroupsQcCompleted(activeOrder) ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse'">
                                                <i class="fa-solid" :class="isAllGroupsQcCompleted(activeOrder) ? 'fa-shield-check text-emerald-600' : 'fa-hourglass-half text-amber-600'"></i>
                                                <span x-text="isAllGroupsQcCompleted(activeOrder) ? '100% CÁC NHÓM ĐÃ KIỂM ĐẠT QC' : 'ĐANG CHỜ CÁC NHÓM KIỂM HÀNG'"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Cards for each StaffGroup -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                    <template x-for="grp in activeOrder.groups_progress" :key="grp.group_id">
                                        <div class="p-3 rounded-xl border bg-white shadow-2xs space-y-2 transition-all"
                                             :class="(activeTab === 'picking' ? grp.is_picking_completed : grp.is_qc_completed) ? 'border-emerald-200 bg-emerald-50/20' : (grp.has_shortage ? 'border-rose-200 bg-rose-50/20' : 'border-amber-200 bg-amber-50/20')">
                                            
                                            <div class="flex items-center justify-between gap-1.5">
                                                <div class="font-bold text-xs text-slate-800 truncate" x-text="grp.group_name"></div>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black border shrink-0"
                                                      :class="(activeTab === 'picking' ? grp.is_picking_completed : grp.is_qc_completed) ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : (grp.has_shortage ? 'bg-rose-100 text-rose-700 border-rose-300' : 'bg-amber-100 text-amber-800 border-amber-300')">
                                                    <span x-text="activeTab === 'picking' ? (grp.is_picking_completed ? '✓ Đã bốc đủ' : (grp.has_shortage ? 'Báo thiếu' : 'Đang bốc')) : (grp.is_qc_completed ? '✓ Đã kiểm' : 'Chờ kiểm')"></span>
                                                </span>
                                            </div>

                                            <div class="text-[11px] text-slate-500 flex items-center justify-between">
                                                <span>Trưởng nhóm: <strong class="text-slate-700" x-text="grp.leader_name || 'Admin Kho'"></strong></span>
                                                <span class="font-mono font-bold text-slate-700" x-text="(activeTab === 'picking' ? grp.picked_count : grp.checked_count) + '/' + grp.items_count + ' món'"></span>
                                            </div>

                                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-300"
                                                     :class="(activeTab === 'picking' ? grp.is_picking_completed : grp.is_qc_completed) ? 'bg-emerald-500' : (grp.has_shortage ? 'bg-rose-500' : 'bg-amber-500')"
                                                     :style="'width: ' + (activeTab === 'picking' ? grp.picking_progress_percent : grp.qc_progress_percent) + '%'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- CẢNH BÁO THIẾU HÀNG TOÀN ĐƠN (Exact style from Mockup Image 2) -->
                        <template x-if="activeOrder && countShortageItems(activeOrder) > 0">
                            <div class="m-5 p-4 rounded-2xl bg-[#fee2e2] border border-rose-300 text-rose-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-rose-200 text-rose-600 flex items-center justify-center shrink-0 shadow-xs text-xl font-bold">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-extrabold text-sm text-slate-900 tracking-tight">
                                                Cảnh báo: Kho không đủ hàng so với đơn đặt!
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#fecaca] text-[#b91c1c] border border-rose-300"
                                                  x-text="countShortageItems(activeOrder) + ' sản phẩm thiếu'"></span>
                                        </div>
                                        <div class="text-xs text-slate-700 font-medium">
                                            <span>Đơn hàng: <strong class="font-mono text-slate-900 font-black" x-text="activeOrder.order_code"></strong> (<span x-text="activeOrder.customer_name"></span>)</span>
                                            <span> • Kho xuất: <strong class="text-slate-800 font-bold" x-text="activeOrder.warehouse || 'Kho Tổng Mê Linh (MQ)'"></strong></span>
                                            <span> • Người phụ trách đơn: <strong class="text-sky-700 underline font-black" x-text="getOrderSupervisor(activeOrder)"></strong></span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="reportShortageActiveOrder()"
                                        class="px-5 py-2.5 bg-[#e11d48] hover:bg-rose-700 text-white text-xs font-bold rounded-full shrink-0 cursor-pointer shadow-md transition-all flex items-center gap-2">
                                    <i class="fa-solid fa-bell"></i>
                                    <span>Báo thiếu hàng</span>
                                </button>
                            </div>
                        </template>

                        <!-- Category Tabs Filter (Chỉ hiển thị khi có quyền xem tất cả không phân loại) -->
                        <div x-show="canViewAllCategories" class="px-5 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2 overflow-x-auto text-xs">
                            <span class="font-bold text-slate-500 shrink-0 flex items-center gap-1">
                                <i class="fa-solid fa-layer-group text-slate-400"></i>
                                <span>Lọc loại SP:</span>
                            </span>
                            <button type="button" @click="selectedCategoryFilter = 'all'"
                                    class="px-3 py-1 rounded-xl text-xs font-bold transition-all border cursor-pointer shrink-0"
                                    :class="selectedCategoryFilter === 'all' ? 'bg-sky-600 text-white border-sky-600 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'">
                                <span>Tất cả loại SP</span>
                                <span class="ml-1 opacity-80 font-mono" x-text="'(' + (activeOrder ? activeOrder.items.length : 0) + ')'"></span>
                            </button>
                            <template x-for="cat in availableCategoriesInActiveOrder" :key="cat.id">
                                <button type="button" @click="selectedCategoryFilter = String(cat.id)"
                                        class="px-3 py-1 rounded-xl text-xs font-bold transition-all border cursor-pointer shrink-0 flex items-center gap-1.5"
                                        :class="selectedCategoryFilter === String(cat.id) ? 'bg-sky-600 text-white border-sky-600 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'">
                                    <span x-text="cat.name"></span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                                          :class="selectedCategoryFilter === String(cat.id) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                                          x-text="cat.count"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Info Banner & Staff Filter & Quick Assign -->
                        <div class="px-5 py-3 bg-amber-50/70 border-b border-amber-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2 text-amber-900 font-bold">
                                <i class="fa-solid fa-lock text-amber-600" x-show="isOrderLockedForPicking(activeOrder)"></i>
                                <span x-show="isOrderLockedForPicking(activeOrder)">Đơn hàng đã kiểm xong - Đã khóa người bốc</span>
                                <span x-show="!isOrderLockedForPicking(activeOrder)" class="text-amber-800 font-semibold flex items-center gap-1.5">
                                    <i class="fa-solid fa-truck-ramp-box text-amber-600"></i>
                                    <span>Tách soạn theo loại SP • Trưởng nhóm phân công người soạn, thành viên tự nhận mặt hàng</span>
                                </span>
                            </div>

                            <!-- Right Controls: Phân công nhanh & Lọc người soạn -->
                            <div class="flex flex-wrap items-center gap-3 text-xs">
                                <!-- Giao nhanh toàn bộ các mặt hàng chưa bốc (cho Trưởng nhóm / Admin) -->
                                <template x-if="(canViewAllCategories || leaderCategoryIds.length > 0 || isUserAdmin) && !isOrderLockedForPicking(activeOrder)">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-600 shrink-0">
                                            <i class="fa-solid fa-user-plus text-amber-600 mr-1"></i>Giao tất cả:
                                        </span>
                                        <select @change="assignAllItemsToStaff($event.target.value); $event.target.value = ''"
                                                class="p-1.5 bg-white rounded-lg border border-amber-300 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs">
                                            <option value="">-- Chọn người nhận --</option>
                                            <template x-for="st in groupStaff" :key="st.id">
                                                <option :value="st.name" x-text="st.name + (st.department ? ' (' + st.department + ')' : '')"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>

                                <!-- Lọc người soạn -->
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="font-bold text-slate-600 shrink-0">
                                        <i class="fa-solid fa-filter text-slate-400 mr-1"></i>Lọc:
                                    </span>
                                    <select x-model="pickerFilter" class="p-1.5 bg-white rounded-lg border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-sky-500 cursor-pointer">
                                        <option value="all" x-text="'Tất cả (' + activeOrder.items.length + ')'"></option>
                                        <option value="unassigned">Chưa phân công</option>
                                        <template x-for="st in groupStaff" :key="st.id">
                                            <option :value="st.name" x-text="st.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Current User Badge & Admin Override Toggle -->
                                <div class="flex items-center gap-2">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-700 font-bold text-xs shadow-2xs">
                                        <i class="fa-solid fa-circle-user text-sky-600 text-sm"></i>
                                        <span class="text-slate-500 font-medium">Tôi:</span>
                                        <span>{{ $currentUser->name }}</span>
                                    </div>
                                    <template x-if="isUserAdmin">
                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs font-bold cursor-pointer select-none transition-all shadow-2xs"
                                               :class="adminOverrideMode ? 'bg-amber-500 text-white border-amber-600' : 'bg-white text-slate-600 border-slate-300 hover:border-amber-400'"
                                               title="Bật để Admin có thể trực tiếp sửa số lượng bốc của mọi dòng mà không cần phân công cho chính mình">
                                            <input type="checkbox" x-model="adminOverrideMode" class="w-3.5 h-3.5 rounded text-amber-600 cursor-pointer">
                                            <span>Admin sửa tất cả</span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Danh sách các Loại sản phẩm bốc hàng -->
                        <div class="space-y-4 p-4 sm:p-5">
                            <template x-for="catGroup in categorizedActiveItems" :key="catGroup.id">
                                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
                                    <!-- Category Group Section Header -->
                                    <div class="bg-gradient-to-r from-sky-50/90 via-slate-50 to-sky-50/40 p-3 sm:px-4 border-b border-sky-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <div class="w-7 h-7 rounded-lg bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs shrink-0">
                                                <i class="fa-solid fa-shapes"></i>
                                            </div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-tight" x-text="catGroup.name"></span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                      :class="catGroup.isAllPicked ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300'"
                                                      x-text="catGroup.isAllPicked ? '✓ Đã bốc xong 100%' : ('Tiến độ: ' + getCatGroupProgress(catGroup))">
                                                </span>
                                                <span class="text-[11px] text-slate-500 font-medium" x-text="'(' + catGroup.items.length + ' mặt hàng)'"></span>
                                            </div>
                                        </div>

                                        <!-- Group Action: Trưởng nhóm hoặc Thành viên tự nhận & Bốc đủ theo loại & Sang kiểm -->
                                        <div class="flex items-center gap-2 flex-wrap" x-show="!isOrderLockedForPicking(activeOrder)">
                                            <!-- Nút Bốc đủ cả loại này -->
                                            <button type="button" @click="quickPickCategory(catGroup)"
                                                    class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold border border-sky-300 text-[11px] shadow-2xs transition-all flex items-center gap-1 cursor-pointer"
                                                    title="Bốc đủ tất cả mặt hàng thuộc loại này">
                                                <i class="fa-solid fa-bolt-lightning text-sky-600 text-[10px]"></i>
                                                <span x-text="'Bốc đủ loại này (' + catGroup.items.length + ')'"></span>
                                            </button>

                                            <!-- Nút Chuyển sang kiểm hàng loại này khi đã bốc xong -->
                                            <template x-if="catGroup.isAllPicked">
                                                <button type="button" @click="activeTab = 'qc'"
                                                        class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-2xs transition-all flex items-center gap-1 cursor-pointer"
                                                        title="Loại này đã bốc xong 100%! Bấm để sang tab Kiểm hàng kiểm tra ngay mà không cần chờ loại khác">
                                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                                    <span>Sang kiểm loại này</span>
                                                </button>
                                            </template>

                                            <!-- Case 1: Trưởng nhóm loại này (Được quyền phân người soạn) -->
                                            <template x-if="catGroup.isLeader">
                                                <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-amber-300 shadow-2xs text-[11px]">
                                                    <span class="font-bold text-amber-900 shrink-0 flex items-center gap-1">
                                                        <i class="fa-solid fa-crown text-amber-600 text-[10px]"></i>
                                                        <span>Giao cả loại này:</span>
                                                    </span>
                                                    <select @change="assignCategoryItemsToStaff(catGroup.id, $event.target.value); $event.target.value = ''"
                                                            class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer">
                                                        <option value="">-- Chọn người nhận --</option>
                                                        <template x-for="st in availableStaffForCategory(catGroup.id)" :key="st.id">
                                                            <option :value="st.name" x-text="st.name + (st.department ? ' (' + st.department + ')' : '')"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>

                                            <!-- Case 2: Thành viên loại này (Tự nhận các món chưa giao) -->
                                            <template x-if="!catGroup.isLeader">
                                                <button type="button" @click="claimAllUnassignedInCategory(catGroup.id)"
                                                        x-show="catGroup.items.some(it => !it.assigned_staff)"
                                                        class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                                                    <i class="fa-solid fa-hand text-[10px]"></i>
                                                    <span>Tự nhận các món chưa giao</span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Table for this category -->
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-xs border-collapse min-w-[950px]">
                                            <thead>
                                                <tr class="bg-slate-50/80 text-slate-700 font-bold text-[11px] uppercase tracking-wider border-b border-slate-200">
                                                    <th class="py-3 px-4 text-center w-16">XÁC NHẬN</th>
                                                    <th class="py-3 px-4">MÃ SKU & TÊN SẢN PHẨM</th>
                                                    <th class="py-3 px-3">KHO XUẤT</th>
                                                    <th class="py-3 px-4">NGƯỜI SOẠN HÀNG (PHÂN CÔNG / TỰ NHẬN)</th>
                                                    <th class="py-3 px-3 text-center">ĐVT</th>
                                                    <th class="py-3 px-4 text-center bg-amber-50/50">CẦN BỐC</th>
                                                    <th class="py-3 px-4 text-center bg-sky-50/40">ĐÃ BỐC (NHẬP SL)</th>
                                                    <th class="py-3 px-4 text-center">TRẠNG THÁI & LÝ DO</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 font-medium">
                                                <!-- Items inside Category Group -->
                                                <template x-for="(it, idx) in catGroup.items" :key="it.id || (it.sku + '_' + idx)">
                                            <tr class="transition-colors"
                                                :class="isItemShort(it) 
                                                    ? 'bg-[#fee2e2]/75 border-l-4 border-l-[#b91c1c]' 
                                                    : (it._justModified 
                                                        ? 'bg-amber-50/90 border-l-4 border-l-amber-500 ring-1 ring-amber-300' 
                                                        : (it.is_picked ? 'bg-emerald-50/30' : 'hover:bg-slate-50/80'))">
                                                
                                                <!-- Checkbox Xác nhận -->
                                                <td class="py-3.5 px-4 text-center">
                                                    <input type="checkbox" x-model="it.is_picked"
                                                           :disabled="isOrderLockedForPicking(activeOrder) || !canUserPickItem(it)"
                                                           @change="handlePickCheckChange(it)"
                                                           class="w-4 h-4 rounded text-sky-600 focus:ring-0 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                                </td>

                                                <!-- SKU & Product Name -->
                                                <td class="py-3.5 px-4">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="font-mono font-bold text-sky-700 text-xs" x-text="it.sku"></span>
                                                        <template x-if="it._justModified">
                                                            <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-black text-[9px] animate-pulse shadow-sm flex items-center gap-1">
                                                                <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                                                                <span>Vừa sửa SL</span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                    <div class="text-xs font-bold text-slate-800 mt-0.5 line-clamp-2" x-text="it.name"></div>
                                                </td>

                                                <!-- Kho Xuất -->
                                                <td class="py-3.5 px-3 text-slate-600 text-[11px] font-semibold">
                                                    <span class="px-2 py-1 bg-white/80 rounded-md border border-slate-200 text-slate-700 font-semibold" x-text="activeOrder.warehouse || 'Kho Tổng Mê Linh (MQ)'"></span>
                                                </td>

                                                <!-- Người Soạn Hàng (Trưởng nhóm phân công hoặc Thành viên tự nhận) -->
                                                <td class="py-3.5 px-4">
                                                    <!-- TRƯỜNG HỢP 1: Trưởng nhóm (hoặc Admin/Quyền xem tất cả) có quyền phân công -->
                                                    <template x-if="canAssignItem(it)">
                                                        <div>
                                                            <!-- Đã khóa người bốc (đã bốc đủ 100%) -->
                                                            <template x-if="isItemStaffLocked(it)">
                                                                <div class="space-y-1">
                                                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-950 border border-amber-300">
                                                                        <i class="fa-solid fa-user text-[11px] text-amber-600"></i>
                                                                        <span x-text="it.assigned_staff + (it.assigned_staff === currentUserName ? ' (Tôi)' : '')"></span>
                                                                    </div>
                                                                    <div class="text-[10px] text-slate-500 font-semibold flex items-center gap-1.5 mt-0.5">
                                                                        <i class="fa-solid fa-lock text-[9px] text-slate-400"></i>
                                                                        <span x-text="isOrderLockedForPicking(activeOrder) ? 'Đã khóa người bốc (Đã kiểm)' : 'Đã khóa người bốc (Đã bốc đủ)'"></span>
                                                                        <button type="button" 
                                                                                x-show="!isOrderLockedForPicking(activeOrder)"
                                                                                @click="it._forceUnlock = true" 
                                                                                class="text-[10px] text-sky-700 hover:text-sky-900 underline font-bold cursor-pointer ml-1"
                                                                                title="Bấm để mở phân công lại hoặc đổi người bốc">
                                                                            Đổi người
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            <!-- Chưa khóa: Dropdown cho phép trưởng nhóm phân công -->
                                                            <template x-if="!isItemStaffLocked(it)">
                                                                <div class="space-y-1">
                                                                    <select :value="it.assigned_staff || ''"
                                                                            @change="updateItemAssignedStaff(it, $event.target.value)"
                                                                            class="w-full max-w-[190px] px-2.5 py-1.5 text-xs font-bold rounded-lg border focus:outline-none focus:ring-1 focus:ring-amber-500 transition-all cursor-pointer shadow-2xs"
                                                                            :class="it.assigned_staff ? 'bg-amber-50/70 text-amber-950 border-amber-300' : 'bg-white text-slate-500 border-slate-300 hover:border-slate-400'">
                                                                        <option value="">-- Chưa phân công --</option>
                                                                        <template x-if="it.assigned_staff && !availableStaffForCategory(it.category_id).some(s => s.name === it.assigned_staff)">
                                                                            <option :value="it.assigned_staff" :selected="true" x-text="it.assigned_staff + ' (Hiện tại)'"></option>
                                                                        </template>
                                                                        <template x-for="st in availableStaffForCategory(it.category_id)" :key="st.id">
                                                                            <option :value="st.name" :selected="it.assigned_staff === st.name" x-text="st.name + (st.department ? ' (' + st.department + ')' : '')"></option>
                                                                        </template>
                                                                    </select>
                                                                    <div class="text-[10px] flex items-center gap-1" x-show="!it.assigned_staff">
                                                                        <i class="fa-solid fa-circle-exclamation text-amber-500 text-[9px]"></i>
                                                                        <span class="text-amber-700 font-semibold">Chưa chỉ định người bốc</span>
                                                                    </div>
                                                                    <div class="text-[10px] flex items-center gap-1 text-emerald-700 font-bold" x-show="it._justModified">
                                                                        <i class="fa-solid fa-rotate text-[9px] text-emerald-600"></i>
                                                                        <span>Vừa đổi SL - Đã mở phân công</span>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>

                                                    <!-- TRƯỜNG HỢP 2: Thành viên thông thường (Không có quyền phân công người khác) -->
                                                    <template x-if="!canAssignItem(it)">
                                                        <div class="space-y-1">
                                                            <!-- 2A. Chưa ai nhận: Thành viên ĐƯỢC TỰ NHẬN -->
                                                            <template x-if="!it.assigned_staff">
                                                                <div class="space-y-1">
                                                                    <button type="button" @click="claimItem(it)"
                                                                            :disabled="isOrderLockedForPicking(activeOrder)"
                                                                            class="px-2.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-black text-xs shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                                                                        <i class="fa-solid fa-hand text-xs"></i>
                                                                        <span>Tự nhận soạn</span>
                                                                    </button>
                                                                    <span class="text-[10px] text-amber-700 font-semibold block">Chưa giao cho ai</span>
                                                                </div>
                                                            </template>

                                                            <!-- 2B. Đã giao cho chính mình (Tôi phụ trách) -->
                                                            <template x-if="it.assigned_staff && it.assigned_staff === currentUserName">
                                                                <div class="space-y-1">
                                                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-950 border border-emerald-300">
                                                                        <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                                                                        <span>Tôi phụ trách</span>
                                                                    </div>
                                                                    <div x-show="!isOrderLockedForPicking(activeOrder) && !it.is_picked && Number(it.picked_qty || 0) === 0">
                                                                        <button type="button" @click="unclaimItem(it)"
                                                                                class="text-[10px] text-rose-600 hover:text-rose-800 underline font-semibold cursor-pointer">
                                                                            Hủy nhận (Trả lại)
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            <!-- 2C. Đã giao cho người khác trong nhóm -->
                                                            <template x-if="it.assigned_staff && it.assigned_staff !== currentUserName">
                                                                <div class="space-y-1">
                                                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                                        <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                                                                        <span x-text="it.assigned_staff"></span>
                                                                    </div>
                                                                    <span class="text-[10px] text-slate-400 block font-medium">Đã giao người khác</span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </td>

                                                <!-- ĐVT -->
                                                <td class="py-3.5 px-3 text-center font-bold text-slate-700 text-xs uppercase" x-text="it.unit || 'CÂY'"></td>

                                                <!-- CẦN BỐC (Total = Sold + Gift Breakdown) -->
                                                <td class="py-3.5 px-4 text-center bg-amber-50/40">
                                                    <div class="font-bold text-slate-900 text-sm font-mono"
                                                         x-text="Number(it.quantity || 0) + Number(it.gift_quantity || 0)"></div>
                                                    
                                                    <!-- If item has gift quantity, display breakdown badge -->
                                                    <template x-if="Number(it.gift_quantity || 0) > 0">
                                                        <div class="mt-0.5 inline-block px-1.5 py-0.5 rounded bg-amber-100 border border-amber-300 text-amber-900 font-bold text-[10px]">
                                                            <span x-text="Number(it.quantity) + ' bán + ' + Number(it.gift_quantity) + ' tặng'"></span>
                                                        </div>
                                                    </template>
                                                </td>

                                                <!-- ĐÃ BỐC (Nhập SL + Nút Đủ) -->
                                                <td class="py-3.5 px-4 text-center bg-sky-50/30">
                                                    <!-- TRƯỜNG HỢP 1: Người dùng ĐƯỢC PHÂN CÔNG (hoặc Admin bật override) -> Được sửa số lượng và bấm Đủ -->
                                                    <template x-if="canUserPickItem(it)">
                                                        <div class="flex items-center justify-center gap-1.5">
                                                            <input type="number" step="any" min="0"
                                                                   :max="Number(it.quantity || 0) + Number(it.gift_quantity || 0)"
                                                                   x-model.number="it.picked_qty"
                                                                   @input="validateAndSavePickedQty(it)"
                                                                   class="w-14 p-1 text-center font-mono font-bold text-xs rounded-lg border focus:outline-none transition-all"
                                                                   :class="isItemShort(it) ? 'border-rose-400 text-rose-700 bg-rose-50/40 font-black' : 'border-slate-300 text-slate-900 bg-white focus:border-sky-500 focus:ring-1 focus:ring-sky-500'">
                                                            
                                                            <button type="button" @click="pickFullItem(it)"
                                                                    class="px-2 py-1 bg-[#0284c7] hover:bg-sky-700 text-white font-bold text-[11px] rounded-lg transition-colors cursor-pointer shadow-2xs"
                                                                    title="Bốc đủ toàn bộ số lượng cần">
                                                                Đủ
                                                            </button>
                                                        </div>
                                                    </template>

                                                    <!-- TRƯỜNG HỢP 2: KHÔNG ĐƯỢC PHÂN CÔNG (hoặc Chưa phân công) -> KHÓA KHÔNG CHO SỬA -->
                                                    <template x-if="!canUserPickItem(it)">
                                                        <div class="flex flex-col items-center justify-center">
                                                            <div class="inline-flex items-center justify-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-500 font-mono font-bold text-xs cursor-not-allowed"
                                                                 :title="!it.assigned_staff ? 'Mặt hàng chưa được phân công. Hãy bấm Tự nhận để bốc.' : ('Chỉ ' + it.assigned_staff + ' mới được quyền sửa số lượng bốc.')">
                                                                <i class="fa-solid fa-lock text-[10px] text-slate-400"></i>
                                                                <span x-text="Number(it.picked_qty || 0)"></span>
                                                            </div>
                                                            <span class="text-[9px] font-semibold mt-0.5"
                                                                  :class="!it.assigned_staff ? 'text-amber-600' : 'text-slate-400'"
                                                                  x-text="!it.assigned_staff ? 'Chưa nhận việc' : ('Chỉ ' + it.assigned_staff + ' được sửa')"></span>
                                                        </div>
                                                    </template>
                                                </td>

                                                <!-- Trạng Thái & Lý Do (Hình 2: Hiển thị Badge Thiếu X ĐVT + Select Lý do + Nút Gửi cảnh báo cho người phụ trách) -->
                                                <td class="py-3.5 px-4 text-center">
                                                    <!-- Trường hợp 1: Thiếu hàng -->
                                                    <template x-if="isItemShort(it)">
                                                        <div class="space-y-1.5 flex flex-col items-center">
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#fee2e2] text-[#b91c1c] border border-rose-300 shadow-2xs">
                                                                <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                                                <span x-text="'Thiếu ' + getShortageQty(it) + ' ' + (it.unit || 'cây').toLowerCase()"></span>
                                                            </span>

                                                            <select x-model="it.shortage_reason"
                                                                    @change="saveItemShortageReason(it)"
                                                                    class="w-full max-w-[185px] p-1 text-[11px] font-semibold rounded-lg border border-slate-300 bg-white text-slate-800 focus:outline-none focus:border-rose-500 shadow-2xs cursor-pointer">
                                                                <option value="Hết hàng trên kệ kho">Hết hàng trên kệ kho</option>
                                                                <option value="Hàng lỗi / vỡ / hỏng">Hàng lỗi / vỡ / hỏng</option>
                                                                <option value="Không tìm thấy hàng trong kho">Không tìm thấy hàng trong kho</option>
                                                                <option value="Chờ lô hàng nhập mới">Chờ lô hàng nhập mới</option>
                                                                <option value="Lý do khác">Lý do khác</option>
                                                            </select>

                                                            <button type="button" @click="reportShortageItem(it)"
                                                                    class="text-[11px] text-rose-600 hover:text-rose-800 font-bold flex items-center justify-center gap-1 cursor-pointer hover:underline"
                                                                    title="Gửi thông báo thiếu hàng tới người phụ trách đơn hàng">
                                                                <i class="fa-solid fa-bell text-[10px]"></i>
                                                                <span>Gửi cảnh báo thiếu hàng</span>
                                                            </button>
                                                        </div>
                                                    </template>

                                                    <!-- Trường hợp 2: Bình thường -->
                                                    <template x-if="!isItemShort(it)">
                                                        <div>
                                                            <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                                                  :class="getItemStatusBadgeClass(it)"
                                                                  x-text="(Number(it.picked_qty || 0) >= (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) && (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) > 0) ? 'Đủ hàng' : (it.item_status || 'Chờ bốc')"></span>
                                                        </div>
                                                    </template>
                                                </td>

                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>

                    </div>

                    <!-- =============================================== -->
                    <!-- TAB 2 CONTENT: 2. KIỂM HÀNG (QC SOẠN ĐƠN)     -->
                    <!-- =============================================== -->
                    <div x-show="activeTab === 'qc'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mt-0">
                        
                        <!-- Panel Header -->
                        <div class="px-5 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2 flex-wrap">
                                    <span class="text-emerald-700 font-mono" x-text="activeOrder.order_code"></span>
                                    <template x-if="activeOrder.is_consignment">
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-xs inline-flex items-center gap-1">
                                            <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                                            <span>Đơn Hàng Ôm (Xuất NVKD)</span>
                                        </span>
                                    </template>
                                    <span class="text-slate-400">|</span>
                                    <span x-text="activeOrder.customer_name"></span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        Đang kiểm hàng
                                    </span>
                                </h3>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-500 mt-1">
                                    <span>Kho hàng: <strong class="text-slate-700 font-bold" x-text="activeOrder.warehouse || 'Kho Tổng Mê Linh (MQ)'"></strong></span>
                                    <span>·</span>
                                    <span>Tiến độ kiểm: <strong class="text-emerald-700 font-bold font-mono" x-text="getProgressRatio(activeOrder)"></strong></span>
                                    <template x-if="activeOrder.delivery_date">
                                        <div class="flex items-center gap-1.5">
                                            <span>·</span>
                                            <span class="inline-flex items-center gap-1 text-emerald-800 font-medium">
                                                <i class="fa-solid fa-truck-fast text-emerald-600 text-[10px]"></i>
                                                <span>Ngày giao: <strong class="font-mono font-bold" x-text="formatDate(activeOrder.delivery_date)"></strong></span>
                                            </span>
                                        </div>
                                    </template>
                                    <template x-if="activeOrder.picking_date">
                                        <div class="flex items-center gap-1.5">
                                            <span>·</span>
                                            <span>Ngày soạn: <strong class="text-amber-800 font-bold font-mono" x-text="formatDate(activeOrder.picking_date)"></strong></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Button Xác nhận QC hoàn tất -->
                            <button type="button" @click="confirmQcAndReadyForDelivery()"
                                    class="px-4 py-2.5 rounded-xl text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 transition-all flex items-center gap-2 cursor-pointer shadow-md shadow-emerald-600/20">
                                <i class="fa-solid fa-shield-check"></i>
                                <span>Xác Nhận Đã Kiểm & Bàn Giao Xe Xuất Kho</span>
                            </button>
                        </div>

                        <!-- Sub-bar: QC Inspector & Batch Action -->
                        <div class="px-5 py-3 bg-slate-50/90 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-600">
                                    <i class="fa-solid fa-user-shield text-slate-400 mr-1"></i>Người kiểm hàng (QC/Thủ kho):
                                </span>
                                <select x-model="qcInspector" class="p-1.5 bg-white rounded-lg border border-slate-300 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 cursor-pointer">
                                    @foreach($staff as $st)
                                        <option value="{{ $st->name }} ({{ $st->department }})">{{ $st->name }} ({{ $st->department }})</option>
                                    @endforeach
                                </select>
                                <span class="ml-2 text-slate-400 font-medium">· Đơn đã kiểm xong - Đã khóa người kiểm</span>
                            </div>

                            <!-- Button Tích kiểm tất cả -->
                            <button type="button" @click="qcCheckAllActiveOrder()"
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                <i class="fa-solid fa-square-check text-emerald-600"></i>
                                <span>Tích kiểm tất cả</span>
                            </button>
                        </div>

                        <!-- GROUP-LEVEL PROGRESS TRACKER IN QC TAB -->
                        <template x-if="activeOrder && activeOrder.groups_progress && activeOrder.groups_progress.length > 0">
                            <div class="m-5 mb-1 p-4 rounded-2xl bg-gradient-to-r from-emerald-50/60 via-slate-50 to-emerald-50/40 border border-emerald-200 shadow-2xs space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-emerald-200/70 pb-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                            <i class="fa-solid fa-clipboard-check text-sm"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                                                <span>Tiến Độ Kiểm Hàng Theo Nhóm (QC)</span>
                                                <span class="text-[10px] font-bold text-emerald-700 font-mono" x-text="'(' + activeOrder.groups_progress.length + ' nhóm phụ trách)'"></span>
                                            </h4>
                                            <p class="text-[11px] text-slate-500 font-medium">
                                                Kiểm tra độc lập theo loại SP (loại nào bốc xong là kiểm được ngay). Toàn đơn đạt <span class="font-bold text-slate-700">"Đã kiểm hàng (Sẵn sàng xuất xe)"</span> khi <strong class="text-emerald-700 font-bold">100% các nhóm</strong> hoàn thành QC.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="shrink-0">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border shadow-2xs transition-all"
                                              :class="isAllGroupsQcCompleted(activeOrder) ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse'">
                                            <i class="fa-solid" :class="isAllGroupsQcCompleted(activeOrder) ? 'fa-shield-check text-emerald-600' : 'fa-hourglass-half text-amber-600'"></i>
                                            <span x-text="isAllGroupsQcCompleted(activeOrder) ? '100% CÁC NHÓM ĐÃ KIỂM ĐẠT QC' : 'ĐANG CHỜ CÁC NHÓM KIỂM HÀNG'"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                    <template x-for="grp in activeOrder.groups_progress" :key="grp.group_id">
                                        <div class="p-3 rounded-xl border bg-white shadow-2xs space-y-2 transition-all"
                                             :class="grp.is_qc_completed ? 'border-emerald-200 bg-emerald-50/20' : 'border-amber-200 bg-amber-50/20'">
                                            
                                            <div class="flex items-center justify-between gap-1.5">
                                                <div class="font-bold text-xs text-slate-800 truncate" x-text="grp.group_name"></div>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black border shrink-0"
                                                      :class="grp.is_qc_completed ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300'">
                                                    <span x-text="grp.is_qc_completed ? '✓ Đã kiểm xong' : 'Đang chờ kiểm'"></span>
                                                </span>
                                            </div>

                                            <div class="text-[11px] text-slate-500 flex items-center justify-between">
                                                <span>Trưởng nhóm: <strong class="text-slate-700" x-text="grp.leader_name || 'Admin Kho'"></strong></span>
                                                <span class="font-mono font-bold text-slate-700" x-text="grp.checked_count + '/' + grp.items_count + ' món'"></span>
                                            </div>

                                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-300"
                                                     :class="grp.is_qc_completed ? 'bg-emerald-500' : 'bg-amber-500'"
                                                     :style="'width: ' + grp.qc_progress_percent + '%'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Danh sách các Loại sản phẩm kiểm hàng (QC) -->
                        <div class="space-y-4 p-4 sm:p-5">
                            <template x-for="catGroup in categorizedActiveItems" :key="catGroup.id">
                                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
                                    <!-- Category Group Header in QC -->
                                    <div class="bg-gradient-to-r from-emerald-50/80 via-slate-50 to-emerald-50/40 p-3 sm:px-4 border-b border-emerald-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs shrink-0">
                                                <i class="fa-solid fa-shapes"></i>
                                            </div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-tight" x-text="catGroup.name"></span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0"
                                                      :class="catGroup.isAllPicked ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300'">
                                                    <span x-text="catGroup.isAllPicked ? '✓ Đã bốc xong (Sẵn sàng kiểm)' : ('⏳ Đang bốc hàng: ' + getCatGroupProgress(catGroup))"></span>
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0"
                                                      :class="catGroup.isAllChecked ? 'bg-teal-100 text-teal-800 border-teal-300' : 'bg-slate-100 text-slate-700 border-slate-200'">
                                                    <i class="fa-solid fa-circle-check text-teal-600 mr-1"></i>
                                                    <span x-text="'Đã kiểm: ' + catGroup.items.filter(i => i.is_checked).length + '/' + catGroup.items.length + ' món'"></span>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Category Action in QC -->
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" @click="qcCheckAllCategory(catGroup)"
                                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-800 bg-white hover:bg-emerald-50 border border-emerald-300 transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                                <i class="fa-solid fa-check-double text-emerald-600"></i>
                                                <span x-text="catGroup.isAllPicked ? ('Tích kiểm cả loại ' + catGroup.name) : 'Tích kiểm các món đã bốc'"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Table for this category QC -->
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-xs border-collapse min-w-[1050px]">
                                            <thead>
                                                <tr class="bg-slate-50/80 text-slate-600 font-black text-[11px] uppercase tracking-wider border-b border-slate-200">
                                                    <th class="py-3 px-4 text-center w-12">Đã Kiểm</th>
                                                    <th class="py-3 px-4">Mã SKU & Tên Sản Phẩm</th>
                                                    <th class="py-3 px-4">Người Đã Soạn</th>
                                                    <th class="py-3 px-3 text-center">ĐVT</th>
                                                    <th class="py-3 px-3 text-center bg-amber-50/40">SL Cần</th>
                                                    <th class="py-3 px-3 text-center bg-sky-50/40">SL Đã Bốc</th>
                                                    <th class="py-3 px-3 text-center">Trạng Thái Soạn</th>
                                                    <th class="py-3 px-4 text-center bg-emerald-50/40">SL Thực Kiểm</th>
                                                    <th class="py-3 px-3 text-center">Trạng Thái Kiểm</th>
                                                    <th class="py-3 px-4">Ghi Chú KCS / Tem Kiện</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 font-medium">
                                                <!-- Items inside this category -->
                                                <template x-for="(it, idx) in catGroup.items" :key="it.id || idx">
                                                    <tr class="hover:bg-slate-50/80 transition-colors"
                                                        :class="it.is_checked ? 'bg-emerald-50/30' : ''">
                                                
                                                <!-- Checkbox Đã Kiểm -->
                                                <td class="py-3.5 px-4 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <input type="checkbox" x-model="it.is_checked"
                                                               :disabled="!it.is_picked && Number(it.picked_qty || 0) <= 0"
                                                               :title="(!it.is_picked && Number(it.picked_qty || 0) <= 0) ? 'Mặt hàng này chưa bốc xong. Cần bốc hàng trước khi kiểm.' : 'Tích chọn để xác nhận đạt chuẩn QC'"
                                                               @change="handleQcCheckChange(it)"
                                                               class="w-4 h-4 rounded text-emerald-600 focus:ring-0 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                                                        <template x-if="!it.is_picked && Number(it.picked_qty || 0) <= 0">
                                                            <span class="text-[9px] text-amber-600 font-bold mt-0.5 leading-none">Chưa bốc</span>
                                                        </template>
                                                    </div>
                                                </td>

                                                <!-- SKU & Tên Sản Phẩm -->
                                                <td class="py-3.5 px-4">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="font-mono font-black text-slate-800 text-xs" x-text="it.sku"></span>
                                                        <template x-if="it._justModified">
                                                            <span class="px-1.5 py-0.5 rounded bg-amber-500 text-white font-black text-[9px] animate-pulse shadow-2xs">
                                                                Vừa sửa SL
                                                            </span>
                                                        </template>
                                                    </div>
                                                    <div class="text-xs font-semibold text-slate-600 mt-0.5 line-clamp-2" x-text="it.name"></div>
                                                </td>

                                                <!-- Người Đã Soạn -->
                                                <td class="py-3.5 px-4">
                                                    <span class="inline-flex px-2 py-0.5 rounded-md border font-bold text-[11px]"
                                                          :class="it.assigned_staff ? 'bg-slate-100 text-slate-700 border-slate-200' : 'bg-amber-50 text-amber-800 border-amber-200'">
                                                        <i class="fa-solid fa-user text-[10px] mr-1" :class="it.assigned_staff ? 'text-slate-500' : 'text-amber-500'"></i>
                                                        <span x-text="it.assigned_staff || 'Chưa phân công'"></span>
                                                    </span>
                                                </td>

                                                <!-- ĐVT -->
                                                <td class="py-3.5 px-3 text-center font-bold text-slate-700 uppercase" x-text="it.unit || 'CÂY'"></td>

                                                <!-- SL CẦN (Total Required = Sold + Gift) -->
                                                <td class="py-3.5 px-3 text-center bg-amber-50/30">
                                                    <div class="font-black text-slate-800 text-xs font-mono"
                                                         x-text="Number(it.quantity || 0) + Number(it.gift_quantity || 0)"></div>
                                                    <template x-if="Number(it.gift_quantity || 0) > 0">
                                                        <div class="text-[9px] font-bold text-amber-800 bg-amber-100 rounded px-1 mt-0.5">
                                                            <span x-text="Number(it.quantity) + '+' + Number(it.gift_quantity) + ' tặng'"></span>
                                                        </div>
                                                    </template>
                                                </td>

                                                <!-- SL Đã Bốc -->
                                                <td class="py-3.5 px-3 text-center bg-sky-50/30 font-black font-mono text-slate-900 text-xs"
                                                    x-text="Number(it.picked_qty || 0)"></td>

                                                <!-- Trạng Thái Soạn -->
                                                <td class="py-3.5 px-3 text-center">
                                                    <span class="text-[10px] font-bold"
                                                          :class="Number(it.picked_qty) >= (Number(it.quantity) + Number(it.gift_quantity || 0)) ? 'text-emerald-700' : (Number(it.picked_qty) > 0 ? 'text-blue-700' : 'text-slate-400')"
                                                          x-text="Number(it.picked_qty) >= (Number(it.quantity) + Number(it.gift_quantity || 0)) ? 'Đã bốc đủ' : (Number(it.picked_qty) > 0 ? 'Đang bốc' : 'Chưa bốc')"></span>
                                                </td>

                                                <!-- SL Thực Kiểm -->
                                                <td class="py-3.5 px-4 text-center bg-emerald-50/30">
                                                    <input type="number" step="any" min="0"
                                                           x-model.number="it.checked_qty"
                                                           @input.debounce.400ms="saveItemQcQty(it)"
                                                           class="w-16 p-1.5 text-center font-mono font-bold text-xs rounded-lg border border-slate-300 focus:outline-none focus:border-emerald-500 bg-white">
                                                </td>

                                                <!-- Trạng Thái Kiểm -->
                                                <td class="py-3.5 px-3 text-center">
                                                    <span class="text-[10px] font-bold"
                                                          :class="it.is_checked ? 'text-emerald-700' : 'text-slate-400'"
                                                          x-text="it.is_checked ? 'Đã kiểm' : 'Chưa kiểm'"></span>
                                                </td>

                                                <!-- Ghi Chú KCS / Tem Kiện -->
                                                <td class="py-3.5 px-4">
                                                    <input type="text" x-model="it.check_notes"
                                                           @blur="saveItemCheckNotes(it)"
                                                           placeholder="Ghi chú tem, quy cách..."
                                                           class="w-full p-1.5 text-xs rounded-lg border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500 bg-slate-50/50 focus:bg-white placeholder:text-slate-400">
                                                </td>

                                            </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>

                </div>
            </template>

            <!-- ======================================================== -->
            <!-- CONSOLIDATED BATCH FULFILLMENT MODE (Gộp Nhiều Đơn)      -->
            <!-- ======================================================== -->
            <template x-if="fulfillmentMode === 'batch'">
                <div>
                    <!-- ======================================================== -->
                    <!-- TAB 1: BỐC HÀNG GỘP (BATCH PICKING WORKSPACE)             -->
                    <!-- ======================================================== -->
                    <div x-show="activeTab === 'picking'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden p-5 space-y-4 mt-0">
                        
                        <!-- 1. Header Toolbar (Matching user screenshot) -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h3 class="text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
                                        <i class="fa-solid fa-layer-group text-sky-600"></i>
                                        <span x-text="'Danh Sách Hàng Hóa Gộp Soạn Bốc (' + aggregatedItems.length + ' mặt hàng)'"></span>
                                    </h3>
                                    <span class="px-2.5 py-0.5 bg-sky-50 text-sky-700 border border-sky-200 rounded-full font-bold text-xs"
                                          x-text="selectedBatchOrderIds.length + ' đơn đã chọn'"></span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Tự động gom vật tư cùng mã SKU, cộng dồn số lượng cần bốc từ các đơn đã chọn bên trái.</p>
                            </div>

                            <!-- Button Hoàn Tất Bốc Cả Lô & Chuyển Kiểm Hàng -->
                            <div>
                                <button type="button" @click="completeAllBatchOrders()"
                                        class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-circle-check text-sm"></i>
                                    <span>Hoàn Tất Bốc Cả Lô & Chuyển Kiểm Hàng</span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Batch Staff Assignment Toolbar (Giao cả lô cho) -->
                        <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-plus text-sky-600"></i>
                                    <span>Giao cả lô cho:</span>
                                </span>

                                <div class="relative">
                                    <select x-model="batchAssignStaff" @change="if(batchAssignStaff) assignBatchAll(batchAssignStaff)"
                                            class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:outline-none focus:border-sky-500 shadow-2xs cursor-pointer min-w-[200px]">
                                        <option value="">-- Tìm tên nhân sự giao cả lô... --</option>
                                        <template x-for="st in staffList" :key="st.id">
                                            <option :value="st.name" x-text="st.name + (st.department ? ' (' + st.department + ')' : '')"></option>
                                        </template>
                                    </select>
                                </div>

                                <button type="button" @click="claimBatchAll()"
                                        class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg transition-all shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                    <span>Tự nhận cả lô</span>
                                </button>
                            </div>

                            <div>
                                <button type="button" @click="unassignBatchAll()"
                                        class="px-3 py-1.5 border border-rose-300 hover:bg-rose-50 text-rose-600 font-bold rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                    <span>Hủy phân công cả lô</span>
                                </button>
                            </div>
                        </div>

                        <!-- 3. Aggregated Items Table for Picking -->
                        <div class="overflow-x-auto border border-slate-200 rounded-xl">
                            <table class="w-full text-left text-xs border-collapse min-w-[900px]">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-600 font-black text-[11px] uppercase tracking-wider border-b border-slate-200">
                                        <th class="py-3 px-3 w-12 text-center">
                                            <input type="checkbox"
                                                   :checked="selectedAggregatedSkus.length > 0 && selectedAggregatedSkus.length === aggregatedItems.length"
                                                   @change="toggleSelectAllAggregated($event.target.checked)"
                                                   class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500 cursor-pointer">
                                        </th>
                                        <th class="py-3 px-4">MÃ SKU, TÊN VẬT TƯ & ĐƠN ĐÓNG GÓP</th>
                                        <th class="py-3 px-3 text-center">KHO XUẤT</th>
                                        <th class="py-3 px-4">NGƯỜI SOẠN HÀNG (GIAO NHIỀU NGƯỜI)</th>
                                        <th class="py-3 px-2 text-center w-16">ĐVT</th>
                                        <th class="py-3 px-3 text-center w-24 bg-amber-50 font-black text-slate-800">TỔNG CẦN BỐC</th>
                                        <th class="py-3 px-3 text-center w-28 bg-sky-50 font-black text-sky-900">TỔNG ĐÃ BỐC</th>
                                        <th class="py-3 px-3 text-center w-32">TRẠNG THÁI & LÝ DO</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="ag in aggregatedItems" :key="'pick_' + ag.sku">
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <!-- Checkbox -->
                                            <td class="py-3.5 px-3 text-center">
                                                <input type="checkbox"
                                                       :value="ag.sku"
                                                       x-model="selectedAggregatedSkus"
                                                       class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500 cursor-pointer">
                                            </td>

                                            <!-- MÃ SKU, TÊN VẬT TƯ & ĐƠN ĐÓNG GÓP -->
                                            <td class="py-3.5 px-4 space-y-1">
                                                <div class="font-mono font-bold text-[11px] text-slate-500 uppercase" x-text="ag.sku"></div>
                                                <div class="font-black text-slate-900 text-xs sm:text-[13px]" x-text="ag.name"></div>
                                                <div class="text-[11px] text-slate-500 font-normal flex flex-wrap items-center gap-1.5">
                                                    <span class="font-bold text-slate-600" x-text="'Gồm ' + ag.contributingOrders.length + ' đơn:'"></span>
                                                    <template x-for="(c, cIdx) in ag.contributingOrders" :key="c.orderId">
                                                        <span class="inline-flex items-center gap-1 font-mono text-[10px]">
                                                            <span class="text-sky-700 font-bold" x-text="c.orderCode"></span>:
                                                            <span class="text-slate-800 font-bold" x-text="c.totalReq + ' ' + (ag.unit || '').toLowerCase()"></span>
                                                            <span x-show="cIdx < ag.contributingOrders.length - 1" class="text-slate-300 mx-0.5 font-sans">|</span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </td>

                                            <!-- KHO XUẤT -->
                                            <td class="py-3.5 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px] border border-slate-200 whitespace-nowrap"
                                                      x-text="ag.warehouse"></span>
                                            </td>

                                            <!-- NGƯỜI SOẠN HÀNG (GIAO NHIỀU NGƯỜI) -->
                                            <td class="py-3.5 px-4 space-y-1.5">
                                                <div class="flex flex-wrap items-center gap-1">
                                                    <template x-for="stName in ag.assignedStaffList" :key="stName">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-100 text-amber-950 font-bold text-[11px] border border-amber-300">
                                                            <span x-text="stName === currentUserName ? stName + ' (Tôi)' : stName"></span>
                                                            <button type="button" @click="unassignBatchItemStaff(ag, stName)" class="hover:text-rose-600 cursor-pointer font-black ml-0.5">&times;</button>
                                                        </span>
                                                    </template>
                                                </div>

                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <select @change="if($event.target.value) { assignBatchItemStaff(ag, $event.target.value); $event.target.value = ''; }"
                                                            class="px-2 py-1 text-[11px] rounded-lg border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-sky-500 shadow-2xs cursor-pointer max-w-[150px]">
                                                        <option value="" x-text="ag.assignedStaffList && ag.assignedStaffList.length > 0 ? '🔍 Giao thêm...' : '🔍 Giao người soạn...'"></option>
                                                        <template x-for="st in staffList" :key="st.id">
                                                            <option :value="st.name" x-text="st.name"></option>
                                                        </template>
                                                    </select>

                                                    <button type="button" x-show="!ag.assignedStaffList || !ag.assignedStaffList.includes(currentUserName)"
                                                            @click="claimBatchItem(ag)"
                                                            class="px-2 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] rounded-lg shadow-2xs cursor-pointer flex items-center gap-1 transition-all">
                                                        <i class="fa-solid fa-phone text-[9px]"></i>
                                                        <span>Tự nhận</span>
                                                    </button>
                                                </div>
                                            </td>

                                            <!-- ĐVT -->
                                            <td class="py-3.5 px-2 text-center font-bold text-slate-700 text-xs uppercase" x-text="ag.unit"></td>

                                            <!-- TỔNG CẦN BỐC -->
                                            <td class="py-3.5 px-3 text-center bg-amber-50/40">
                                                <span class="font-mono font-black text-slate-900 text-sm" x-text="ag.totalRequired"></span>
                                            </td>

                                            <!-- TỔNG ĐÃ BỐC -->
                                            <td class="py-3.5 px-3 text-center bg-sky-50/40">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <input type="number" step="any" min="0" :max="ag.totalRequired"
                                                           :value="ag.totalPicked"
                                                           @change="updateBatchItemPickedQty(ag, $event.target.value)"
                                                           class="w-14 p-1 text-center font-mono font-bold text-xs rounded-lg border border-slate-300 text-slate-900 bg-white focus:outline-none focus:border-sky-500 shadow-2xs">
                                                    
                                                    <button type="button" @click="pickFullBatchItem(ag)"
                                                            class="px-2 py-1 font-bold text-[11px] rounded-lg transition-colors cursor-pointer shadow-2xs border"
                                                            :class="ag.isFullyPicked ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-100 hover:bg-sky-50 text-slate-700 border-slate-300'"
                                                            title="Bốc đủ toàn bộ số lượng cần cho tất cả các đơn">
                                                        Đủ
                                                    </button>
                                                </div>
                                            </td>

                                            <!-- TRẠNG THÁI & LÝ DO -->
                                            <td class="py-3.5 px-3 text-center">
                                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                      :class="ag.badgeClass"
                                                      x-text="ag.status"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- ======================================================== -->
                    <!-- TAB 2: KIỂM HÀNG GỘP (BATCH QC WORKSPACE)                 -->
                    <!-- ======================================================== -->
                    <div x-show="activeTab === 'qc'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden p-5 space-y-4 mt-0">
                        
                        <!-- 1. Header Toolbar for Batch QC -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h3 class="text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
                                        <i class="fa-solid fa-clipboard-check text-emerald-600"></i>
                                        <span x-text="'Kiểm Tra Hàng Hóa Gộp QC (' + aggregatedItems.length + ' mặt hàng)'"></span>
                                    </h3>
                                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-xs"
                                          x-text="selectedBatchOrderIds.length + ' đơn đã chọn'"></span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Kiểm tra, đối soát chất lượng và số lượng hàng hóa gom lô từ các đơn đã chọn trước khi xuất bến.</p>
                            </div>

                            <!-- Button Hoàn Tất Kiểm Cả Lô & Chuyển Xuất Xe -->
                            <div>
                                <button type="button" @click="completeAllBatchQcOrders()"
                                        class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-truck-fast text-sm"></i>
                                    <span>Hoàn Tất Kiểm Cả Lô & Chuyển Xuất Xe</span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Batch QC Action Toolbar -->
                        <div class="bg-emerald-50/60 p-3 rounded-xl border border-emerald-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <button type="button" @click="qcCheckAllBatchItems()"
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-check-double text-xs"></i>
                                    <span>Tích kiểm toàn bộ lô hàng</span>
                                </button>

                                <span class="px-2.5 py-1 bg-white border border-emerald-200 rounded-lg text-emerald-900 font-bold flex items-center gap-1.5 shadow-2xs">
                                    <i class="fa-solid fa-user-check text-emerald-600"></i>
                                    <span>Người kiểm tra: <strong x-text="qcInspector"></strong></span>
                                </span>
                            </div>

                            <div>
                                <button type="button" @click="uncheckAllBatchItems()"
                                        class="px-3 py-1.5 border border-slate-300 hover:bg-slate-100 text-slate-600 font-bold rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                    <span>Bỏ tích kiểm cả lô</span>
                                </button>
                            </div>
                        </div>

                        <!-- 3. Batch QC Table -->
                        <div class="overflow-x-auto border border-slate-200 rounded-xl">
                            <table class="w-full text-left text-xs border-collapse min-w-[1050px]">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-600 font-black text-[11px] uppercase tracking-wider border-b border-slate-200">
                                        <th class="py-3 px-3 w-12 text-center">ĐÃ KIỂM</th>
                                        <th class="py-3 px-4">MÃ SKU, TÊN VẬT TƯ & ĐƠN ĐÓNG GÓP</th>
                                        <th class="py-3 px-3 text-center">KHO XUẤT</th>
                                        <th class="py-3 px-4">NGƯỜI ĐÃ SOẠN</th>
                                        <th class="py-3 px-2 text-center w-16">ĐVT</th>
                                        <th class="py-3 px-3 text-center w-24 bg-amber-50 font-black text-slate-800">TỔNG CẦN</th>
                                        <th class="py-3 px-3 text-center w-24 bg-sky-50 font-black text-sky-900">TỔNG ĐÃ BỐC</th>
                                        <th class="py-3 px-3 text-center w-28">TRẠNG THÁI SOẠN</th>
                                        <th class="py-3 px-4 text-center w-36 bg-emerald-50 font-black text-emerald-900">SL THỰC KIỂM</th>
                                        <th class="py-3 px-3 text-center w-28">TRẠNG THÁI KIỂM</th>
                                        <th class="py-3 px-4 w-48">GHI CHÚ KCS / TEM KIỆN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="ag in aggregatedItems" :key="'qc_' + ag.sku">
                                        <tr class="hover:bg-slate-50/80 transition-colors" :class="ag.isFullyChecked ? 'bg-emerald-50/30' : ''">
                                            <!-- Checkbox Đã Kiểm -->
                                            <td class="py-3.5 px-3 text-center">
                                                <input type="checkbox"
                                                       :checked="ag.isFullyChecked"
                                                       @change="toggleBatchItemQc(ag, $event.target.checked)"
                                                       class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                                            </td>

                                            <!-- MÃ SKU, TÊN VẬT TƯ & ĐƠN ĐÓNG GÓP -->
                                            <td class="py-3.5 px-4 space-y-1">
                                                <div class="font-mono font-bold text-[11px] text-slate-500 uppercase" x-text="ag.sku"></div>
                                                <div class="font-black text-slate-900 text-xs sm:text-[13px]" x-text="ag.name"></div>
                                                <div class="text-[11px] text-slate-500 font-normal flex flex-wrap items-center gap-1.5">
                                                    <span class="font-bold text-slate-600" x-text="'Gồm ' + ag.contributingOrders.length + ' đơn:'"></span>
                                                    <template x-for="(c, cIdx) in ag.contributingOrders" :key="c.orderId">
                                                        <span class="inline-flex items-center gap-1 font-mono text-[10px]">
                                                            <span class="text-sky-700 font-bold" x-text="c.orderCode"></span>:
                                                            <span class="text-slate-800 font-bold" x-text="c.totalReq + ' ' + (ag.unit || '').toLowerCase()"></span>
                                                            <span x-show="cIdx < ag.contributingOrders.length - 1" class="text-slate-300 mx-0.5 font-sans">|</span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </td>

                                            <!-- KHO XUẤT -->
                                            <td class="py-3.5 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px] border border-slate-200 whitespace-nowrap"
                                                      x-text="ag.warehouse"></span>
                                            </td>

                                            <!-- NGƯỜI ĐÃ SOẠN -->
                                            <td class="py-3.5 px-4">
                                                <div class="flex flex-wrap items-center gap-1">
                                                    <template x-for="stName in ag.assignedStaffList" :key="stName">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px] border border-slate-200">
                                                            <i class="fa-solid fa-user text-[9px] text-slate-500"></i>
                                                            <span x-text="stName"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!ag.assignedStaffList || ag.assignedStaffList.length === 0">
                                                        <span class="text-slate-400 italic text-[11px]">Chưa phân công</span>
                                                    </template>
                                                </div>
                                            </td>

                                            <!-- ĐVT -->
                                            <td class="py-3.5 px-2 text-center font-bold text-slate-700 text-xs uppercase" x-text="ag.unit"></td>

                                            <!-- TỔNG CẦN -->
                                            <td class="py-3.5 px-3 text-center bg-amber-50/40">
                                                <span class="font-mono font-black text-slate-900 text-sm" x-text="ag.totalRequired"></span>
                                            </td>

                                            <!-- TỔNG ĐÃ BỐC -->
                                            <td class="py-3.5 px-3 text-center bg-sky-50/40">
                                                <span class="font-mono font-black text-slate-900 text-sm" x-text="ag.totalPicked"></span>
                                            </td>

                                            <!-- TRẠNG THÁI SOẠN -->
                                            <td class="py-3.5 px-3 text-center">
                                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                      :class="ag.badgeClass"
                                                      x-text="ag.status"></span>
                                            </td>

                                            <!-- SL THỰC KIỂM -->
                                            <td class="py-3.5 px-4 text-center bg-emerald-50/40">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <input type="number" step="any" min="0"
                                                           :value="ag.totalChecked || (ag.isFullyChecked ? ag.totalPicked : 0)"
                                                           @change="updateBatchItemCheckedQty(ag, $event.target.value)"
                                                           class="w-16 p-1.5 text-center font-mono font-bold text-xs rounded-lg border border-slate-300 text-slate-900 bg-white focus:outline-none focus:border-emerald-500 shadow-2xs">
                                                    <button type="button" @click="qcCheckFullBatchItem(ag)"
                                                            class="px-2 py-1 font-bold text-[11px] rounded-lg cursor-pointer shadow-2xs border bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100">
                                                        Đủ
                                                    </button>
                                                </div>
                                            </td>

                                            <!-- TRẠNG THÁI KIỂM -->
                                            <td class="py-3.5 px-3 text-center">
                                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                      :class="ag.qcBadgeClass"
                                                      x-text="ag.qcStatus"></span>
                                            </td>

                                            <!-- GHI CHÚ KCS -->
                                            <td class="py-3.5 px-4">
                                                <input type="text" placeholder="Ghi chú tem, quy cách..."
                                                       :value="ag.checkNotes || ''"
                                                       @blur="updateBatchItemCheckNotes(ag, $event.target.value)"
                                                       class="w-full p-1.5 text-xs rounded-lg border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500 bg-slate-50/50 focus:bg-white placeholder:text-slate-400">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </template>

            <!-- Empty State when no order selected -->
            <div x-show="fulfillmentMode === 'single' && !activeOrder" class="py-20 text-center text-slate-400 bg-white rounded-2xl border border-slate-200 mt-0">
                <i class="fa-solid fa-clipboard-question text-4xl text-slate-300 mb-3"></i>
                <p class="font-bold text-slate-600 text-sm">Vui lòng chọn một đơn hàng từ danh sách bên trái để tiến hành soạn bốc hoặc kiểm tra QC.</p>
            </div>

        </div>

    </div>

</div>

<script>
function pickingApp() {
    return {
        activeTab: 'picking', // 'picking' | 'qc'
        fulfillmentMode: 'single', // 'single' | 'batch'
        selectedBranch: '',
        filterPickingDate: '{{ date("Y-m-d") }}',
        filterOrderType: 'all', // 'all' | 'sales' | 'consignment'
        searchQuery: '',
        pickerFilter: 'all',
        qcInspector: '{{ $currentUser->name }} ({{ $currentUser->department ?? "Manager" }})',
        currentUserName: '{{ $currentUser->name }}',
        currentUserId: {{ $currentUser->id }},
        isUserAdmin: {{ ($currentUser->hasRole('admin') || $currentUser->hasRole('super-admin') || $currentUser->id === 1 || in_array($currentUser->department ?? '', ['Ban Giám Đốc', 'IT'])) ? 'true' : 'false' }},
        adminOverrideMode: false,
        
        // Batch Fulfillment State
        selectedBatchOrderIds: [],
        batchAssignStaff: '',
        selectedAggregatedSkus: [],

        // Data injected from Blade
        orders: @json($orders),
        staffList: @json($staff),
        activeOrderId: null,
        canViewAllCategories: {{ $canViewAllCategories ? 'true' : 'false' }},
        userCategoryIds: Array.isArray(@json($userCategoryIds)) ? @json($userCategoryIds) : Object.values(@json($userCategoryIds) || {}),
        leaderCategoryIds: Array.isArray(@json($leaderCategoryIds)) ? @json($leaderCategoryIds) : Object.values(@json($leaderCategoryIds) || {}),
        allCategories: @json($allCategories),
        userGroups: @json($userGroupsData ?? []),
        groupStaff: @json($groupStaff),
        categoryStaffMap: @json($categoryStaffMap ?? []),
        selectedCategoryFilter: 'all',

        // Realtime sync tracking & Alert State
        lastSyncEventId: {{ \App\Models\PickingSyncEvent::max('id') ?? 0 }},
        realtimePollTimer: null,
        isPolling: false,
        realtimeAlert: null,
        crossTabChannel: null,

        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        initApp() {
            const urlParams = new URLSearchParams(window.location.search);
            const reqOrderId = urlParams.get('order_id');
            const reqPickingDate = urlParams.get('picking_date');
            if (reqPickingDate !== null && reqPickingDate !== undefined) {
                this.filterPickingDate = reqPickingDate;
            } else if (reqOrderId && this.orders.some(o => String(o.id) === String(reqOrderId))) {
                const targetOrder = this.orders.find(o => String(o.id) === String(reqOrderId));
                this.filterPickingDate = targetOrder && targetOrder.picking_date ? this.formatDateForInput(targetOrder.picking_date) : '';
            } else {
                const todayStr = '{{ date("Y-m-d") }}';
                const hasTodayOrders = this.orders.some(o => o.picking_date && this.formatDateForInput(o.picking_date) === todayStr);
                this.filterPickingDate = hasTodayOrders ? todayStr : '';
            }

            if (reqOrderId && this.orders.some(o => String(o.id) === String(reqOrderId))) {
                this.activeOrderId = Number(reqOrderId);
            } else if (this.filteredOrders.length > 0) {
                this.activeOrderId = this.filteredOrders[0].id;
            } else if (this.orders.length > 0) {
                this.activeOrderId = this.orders[0].id;
            }

            // Mặc định chọn 5 đơn đầu tiên khi vào giao diện gộp đơn (giống ảnh mẫu 5 / 7 đơn)
            if (this.filteredOrders.length > 0) {
                this.selectedBatchOrderIds = this.filteredOrders.slice(0, 5).map(o => o.id);
            }
            this.$watch('fulfillmentMode', (mode) => {
                if (mode === 'batch' && this.selectedBatchOrderIds.length === 0 && this.filteredOrders.length > 0) {
                    this.selectedBatchOrderIds = this.filteredOrders.slice(0, 5).map(o => o.id);
                }
            });

            // Không tự động bật popup cảnh báo trên trang khi tải lại trang
            const initialModOrder = this.orders.find(o => o._modifiedAlert);
            if (initialModOrder && initialModOrder._modifiedAlert) {
                // Chỉ lưu state trên đơn, không bung popup làm che màn hình
            }

            // Tự động kiểm tra và dọn dẹp cờ thiếu hàng cho các mặt hàng đã bốc đủ (hoặc đã cắt đơn cho vừa đủ)
            (this.orders || []).forEach(o => {
                let hasActualShortage = false;
                let allDone = true;
                (o.items || []).forEach(it => {
                    const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                    const picked = Number(it.picked_qty || 0);
                    if (req > 0 && picked >= req) {
                        it.item_status = 'Đủ hàng';
                        it.shortage_reason = '';
                        it.is_picked = true;
                    } else if (req > 0 && picked < req) {
                        allDone = false;
                        if (it.item_status === 'Thiếu hàng' || it.shortage_reason) {
                            hasActualShortage = true;
                            if (!it.shortage_reason) {
                                it.shortage_reason = 'Hết hàng trên kệ kho';
                            }
                        }
                    }
                });
                if (!hasActualShortage && o.status === 'Kho báo thiếu hàng') {
                    o.status = allDone ? 'Đã bốc xong' : 'Soạn hàng';
                    o.shortage_reported = false;
                }
            });

            this.$watch('filterPickingDate', () => {
                setTimeout(() => {
                    const list = this.filteredOrders;
                    if (list.length > 0 && !list.some(o => o.id === this.activeOrderId)) {
                        this.activeOrderId = list[0].id;
                    }
                }, 50);
            });

            this.$watch('selectedBranch', () => {
                setTimeout(() => {
                    const list = this.filteredOrders;
                    if (list.length > 0 && !list.some(o => o.id === this.activeOrderId)) {
                        this.activeOrderId = list[0].id;
                    }
                }, 50);
            });

            this.$watch('filterOrderType', () => {
                setTimeout(() => {
                    const list = this.filteredOrders;
                    if (list.length > 0 && !list.some(o => o.id === this.activeOrderId)) {
                        this.activeOrderId = list[0].id;
                    }
                }, 50);
            });

            // Kênh BroadcastChannel đồng bộ tức thì 0ms giữa các tab cùng trình duyệt
            try {
                this.crossTabChannel = new BroadcastChannel('erp_order_sync');
                this.crossTabChannel.onmessage = (event) => {
                    console.log('⚡ Nhận tín hiệu Realtime qua BroadcastChannel:', event.data);
                    this.fetchRealtimeEvents();
                };
            } catch(e) {}

            // Lắng nghe qua storage event (dự phòng)
            window.addEventListener('storage', (e) => {
                if (e.key === 'erp_order_sync_trigger') {
                    console.log('⚡ Nhận tín hiệu Realtime qua Storage Event');
                    this.fetchRealtimeEvents();
                }
            });

            // Yêu cầu quyền thông báo hệ điều hành / trình duyệt
            if (window.Notification && Notification.permission === 'default') {
                Notification.requestPermission();
            }

            // Khởi động lắng nghe Realtime Polling
            this.startRealtimePolling();
            // Gọi kiểm tra ngay một lần sau 300ms
            setTimeout(() => { this.fetchRealtimeEvents(); }, 300);
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        switchTab(tab) {
            this.activeTab = tab;
            setTimeout(() => {
                const list = this.filteredOrders;
                if (list.length > 0 && !list.some(o => o.id === this.activeOrderId)) {
                    this.activeOrderId = list[0].id;
                }
            }, 50);
        },

        formatDateForInput(d) {
            if (!d) return '';
            try {
                if (typeof d === 'string' && d.length >= 10 && d.includes('-')) {
                    return d.substring(0, 10);
                }
                return new Date(d).toISOString().split('T')[0];
            } catch(e) {
                return '';
            }
        },

        get totalOrdersCount() {
            return this.orders.length;
        },

        isOrderReadyForQc(o) {
            if (!o || !o.picking_date) return false;
            if (['Đã bốc xong', 'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Kho đã xuất đủ'].includes(o.status)) {
                return true;
            }
            if (o.groups_progress && o.groups_progress.some(g => g.is_picking_completed || g.checked_count > 0)) {
                return true;
            }
            if (o.items && o.items.some(it => it.is_picked || Number(it.picked_qty || 0) > 0 || it.is_checked)) {
                return true;
            }
            return false;
        },

        isOrderDaKiem(o) {
            if (!o) return false;
            return ['Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Kho đã xuất đủ'].includes(o.status) || this.isAllGroupsQcCompleted(o);
        },

        isOrderDangKiem(o) {
            if (!o || this.isOrderDaKiem(o)) return false;
            if (o.status === 'Đang kiểm hàng') return true;
            if (o.groups_progress && o.groups_progress.some(g => g.checked_count > 0)) return true;
            if (o.items && o.items.some(it => it.is_checked)) return true;
            return false;
        },

        isOrderChoKiem(o) {
            if (!o || this.isOrderDaKiem(o) || this.isOrderDangKiem(o)) return false;
            return this.isOrderReadyForQc(o);
        },

        isOrderChoBoc(o) {
            if (!o) return false;
            return ['Chờ bốc', 'Chốt đơn', 'Đã duyệt', 'Soạn bốc', 'Soạn hàng'].includes(o.status) && !this.isAllGroupsPickingCompleted(o);
        },

        isOrderDangBoc(o) {
            if (!o) return false;
            return (['Kho đang soạn', 'Đang bốc hàng', 'Kho báo thiếu hàng'].includes(o.status) || 
                   (['Đang kiểm hàng', 'Kiểm hàng'].includes(o.status))) && !this.isAllGroupsPickingCompleted(o);
        },

        isOrderDaBoc(o) {
            if (!o) return false;
            return ['Đã bốc xong', 'Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Kho đã xuất đủ'].includes(o.status) || this.isAllGroupsPickingCompleted(o);
        },

        get countPendingPick() {
            return this.orders.filter(o => !!o.picking_date && (
                ['Chốt đơn', 'Đã duyệt', 'Chờ bốc', 'Soạn bốc', 'Soạn hàng', 'Kho đang soạn', 'Đang bốc hàng', 'Kho báo thiếu hàng'].includes(o.status) ||
                ((o.status === 'Đang kiểm hàng' || o.status === 'Kiểm hàng') && !this.isAllGroupsPickingCompleted(o))
            )).length;
        },

        get countReadyQc() {
            return this.orders.filter(o => this.isOrderReadyForQc(o) && !this.isOrderDaKiem(o)).length;
        },

        get countConsignmentOrders() {
            return this.orders.filter(o => !!o.is_consignment).length;
        },

        get countSalesOrders() {
            return this.orders.filter(o => !o.is_consignment).length;
        },

        setOrderTypeFilter(type) {
            this.filterOrderType = type;
            if (type === 'consignment') {
                const hasOnDate = !this.filterPickingDate || this.orders.some(o => o.is_consignment && this.formatDateForInput(o.picking_date) === this.filterPickingDate);
                if (!hasOnDate && this.countConsignmentOrders > 0) {
                    this.filterPickingDate = '';
                }
            }
            setTimeout(() => {
                const list = this.filteredOrders;
                if (list.length > 0 && !list.some(o => o.id === this.activeOrderId)) {
                    this.activeOrderId = list[0].id;
                }
            }, 50);
        },

        get filteredOrders() {
            return this.orders.filter(o => {
                // Filter theo loại đơn: Đơn bán thông thường vs Đơn hàng ôm của nhân viên
                if (this.filterOrderType === 'sales' && o.is_consignment) {
                    return false;
                }
                if (this.filterOrderType === 'consignment' && !o.is_consignment) {
                    return false;
                }

                // Chỉ lấy các đơn hàng có ngày soạn hàng
                if (!o.picking_date) {
                    return false;
                }

                // Lọc theo ngày soạn hàng (nếu có chọn ngày)
                if (this.filterPickingDate) {
                    const pDate = this.formatDateForInput(o.picking_date);
                    if (pDate !== this.filterPickingDate) {
                        return false;
                    }
                }

                // Branch filter (hỗ trợ cả string và object)
                const whName = typeof o.warehouse === 'string' ? o.warehouse : (o.warehouse?.name || '');
                if (this.selectedBranch && whName && !whName.includes(this.selectedBranch)) {
                    return false;
                }

                // Text search (mã đơn, tên khách/hàng ôm, kho xuất, hoặc nhân viên tạo/ôm đơn)
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const matchCode = (o.order_code || '').toLowerCase().includes(q);
                    const matchCust = (o.customer_name || '').toLowerCase().includes(q);
                    const matchWh = whName.toLowerCase().includes(q);
                    const matchStaff = (o.order_creator || o.supervisor || '').toLowerCase().includes(q);
                    if (!matchCode && !matchCust && !matchWh && !matchStaff) return false;
                }

                return true;
            });
        },

        get activeOrder() {
            return this.orders.find(o => o.id === this.activeOrderId) || null;
        },

        selectOrder(ord) {
            this.activeOrderId = ord.id;
        },

        get userGroupsSummary() {
            if (!this.userGroups || this.userGroups.length === 0) return 'Chưa phân nhóm';
            return this.userGroups.map(g => g.name).join(', ');
        },

        get userCategoriesSummary() {
            if (this.canViewAllCategories) return 'Tất cả loại sản phẩm';
            if (!this.userCategoryIds || this.userCategoryIds.length === 0) return 'Chưa có loại SP';
            const names = (this.allCategories || [])
                .filter(c => this.userCategoryIds.map(Number).includes(Number(c.id)))
                .map(c => c.name);
            return names.length > 0 ? names.join(', ') : 'Chưa phân loại';
        },

        get availableCategoriesInActiveOrder() {
            if (!this.activeOrder || !this.activeOrder.items) return [];
            const counts = {};
            this.activeOrder.items.forEach(it => {
                const cId = it.category_id || 0;
                counts[cId] = (counts[cId] || 0) + 1;
            });
            return (this.allCategories || [])
                .filter(c => counts[c.id])
                .map(c => ({
                    id: c.id,
                    name: c.name,
                    count: counts[c.id] || 0
                }));
        },

        getOrderCategoryNames(ord) {
            if (!ord || !ord.items) return [];
            const catMap = {};
            ord.items.forEach(it => {
                if (it.category_name) catMap[it.category_name] = true;
            });
            return Object.keys(catMap);
        },

        isLeaderForCategory(catId) {
            if (this.canViewAllCategories || this.isUserAdmin) return true;
            if (!catId) return false;
            const ids = Array.isArray(this.leaderCategoryIds) ? this.leaderCategoryIds : Object.values(this.leaderCategoryIds || {});
            return ids.map(Number).includes(Number(catId));
        },

        canAssignItem(item) {
            if (this.canViewAllCategories || this.isUserAdmin) return true;
            if (!item) return false;
            return this.isLeaderForCategory(item.category_id);
        },

        canClaimItem(item) {
            if (!item) return false;
            if (this.isOrderLockedForPicking(this.activeOrder)) return false;
            if (item.assigned_staff) return false;
            if (this.canViewAllCategories || this.isUserAdmin) return true;
            const catId = Number(item.category_id);
            return (this.userCategoryIds || []).map(Number).includes(catId);
        },

        availableStaffForCategory(catId) {
            if (this.categoryStaffMap) {
                const id = Number(catId);
                if (this.categoryStaffMap[id] && this.categoryStaffMap[id].length > 0) {
                    return this.categoryStaffMap[id];
                }
                if (this.categoryStaffMap['default'] && this.categoryStaffMap['default'].length > 0) {
                    return this.categoryStaffMap['default'];
                }
            }
            return this.groupStaff && this.groupStaff.length > 0 ? this.groupStaff : this.staffList;
        },

        getCatGroupProgress(catGroup) {
            if (!catGroup || !catGroup.items || catGroup.items.length === 0) return '0/0';
            const done = catGroup.items.filter(i => {
                if (this.activeTab === 'picking') {
                    const req = Number(i.quantity) + Number(i.gift_quantity || 0);
                    return i.is_picked || (Number(i.picked_qty) >= req && req > 0);
                } else {
                    return i.is_checked;
                }
            }).length;
            return done + '/' + catGroup.items.length;
        },

        get categorizedActiveItems() {
            if (!this.activeOrder || !this.activeOrder.items) return [];
            let items = this.activeOrder.items;

            // Lọc theo loại sản phẩm đã chọn
            if (this.selectedCategoryFilter !== 'all') {
                items = items.filter(it => String(it.category_id) === String(this.selectedCategoryFilter));
            }

            // Lọc theo người soạn đã chọn
            if (this.pickerFilter === 'unassigned') {
                items = items.filter(it => !it.assigned_staff);
            } else if (this.pickerFilter !== 'all') {
                items = items.filter(it => it.assigned_staff === this.pickerFilter);
            }

            // Gom nhóm theo category_id
            const groupsMap = {};
            items.forEach(it => {
                const catId = it.category_id || 0;
                const catName = it.category_name || 'Khác / Chưa phân loại';
                if (!groupsMap[catId]) {
                    groupsMap[catId] = {
                        id: catId,
                        name: catName,
                        items: [],
                        isLeader: this.isLeaderForCategory(catId),
                        totalReq: 0,
                        totalPicked: 0,
                        isAllPicked: false
                    };
                }
                groupsMap[catId].items.push(it);
                const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                groupsMap[catId].totalReq += req;
                groupsMap[catId].totalPicked += Number(it.picked_qty || 0);
            });

            const groups = Object.values(groupsMap);
            groups.forEach(g => {
                g.pickedCount = g.items.filter(it => {
                    const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                    return (it.is_picked || (Number(it.picked_qty) >= req && req > 0)) && it.item_status !== 'Thiếu hàng';
                }).length;
                g.isAllPicked = g.items.length > 0 && g.pickedCount >= g.items.length;
                g.checkedCount = g.items.filter(it => it.is_checked || Number(it.checked_qty) > 0).length;
                g.isAllChecked = g.items.length > 0 && g.checkedCount >= g.items.length;
            });

            return groups;
        },

        async quickPickCategory(catGroup) {
            if (!this.activeOrder || !catGroup || !catGroup.items) return;
            const itemsToPick = catGroup.items.filter(it => this.canUserPickItem(it) || !it.is_picked);
            if (itemsToPick.length === 0) {
                this.showToast('Bạn không được phân công hoặc không phụ trách loại sản phẩm này!', 'error');
                return;
            }
            itemsToPick.forEach(it => {
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                it.picked_qty = reqTotal;
                it.is_picked = true;
                it.item_status = 'Đủ hàng';
                it.shortage_reason = null;
            });

            const targetItem = itemsToPick[0] || catGroup.items[0];
            const res = await this.saveItemPickingState(targetItem, {
                quickPickCategory: true,
                categoryId: catGroup.id
            });

            this.showToast('🎉 Đã bốc đủ ' + itemsToPick.length + ' mặt hàng của loại "' + catGroup.name + '"! Bạn có thể kiểm tra ngay loại này ở tab Kiểm Hàng.', 'success');
        },

        async qcCheckAllCategory(catGroup) {
            if (!this.activeOrder || !catGroup || !catGroup.items) return;
            const targetItems = catGroup.items.filter(it => it.is_picked || Number(it.picked_qty || 0) > 0);
            if (targetItems.length === 0) {
                this.showToast('Loại sản phẩm này chưa có mặt hàng nào được bốc xong để kiểm tra!', 'warning');
                return;
            }
            targetItems.forEach(it => {
                it.is_checked = true;
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                it.checked_qty = Number(it.picked_qty) > 0 ? it.picked_qty : reqTotal;
                it.checked_by = this.qcInspector;
            });

            const res = await this.saveItemPickingState(targetItems[0], {
                qcCategoryChecked: true,
                categoryId: catGroup.id
            });

            if (res && res.success) {
                if (res.allChecked) {
                    this.showToast('🎉 Toàn bộ các nhóm đã kiểm hàng xong! Đơn đã chuyển sang trạng thái "Đã kiểm hàng".', 'success');
                } else {
                    this.showToast('✓ Đã tích kiểm xong loại "' + catGroup.name + '" (' + targetItems.length + ' món)!', 'success');
                }
            }
        },

        async claimItem(item) {
            if (!this.canClaimItem(item)) return;
            item.assigned_staff = this.currentUserName;
            const ok = await this.updateItemAssignedStaff(item, this.currentUserName);
            if (ok !== false) {
                this.showToast('Bạn đã tự nhận soạn mặt hàng: ' + (item.sku || item.name));
            }
        },

        async unclaimItem(item) {
            if (!item || item.assigned_staff !== this.currentUserName) return;
            if (this.isOrderLockedForPicking(this.activeOrder)) return;
            item.assigned_staff = null;
            await this.updateItemAssignedStaff(item, '');
            this.showToast('Đã hủy nhận mặt hàng. Thành viên khác có thể tự nhận.');
        },

        async claimAllUnassignedInCategory(categoryId) {
            if (!this.activeOrder) return;
            const unassigned = (this.activeOrder.items || []).filter(it => 
                Number(it.category_id) === Number(categoryId) && 
                !it.assigned_staff && 
                !this.isItemStaffLocked(it)
            );
            if (unassigned.length === 0) {
                this.showToast('Không có mặt hàng nào chưa nhận trong loại này', 'warning');
                return;
            }
            for (const it of unassigned) {
                it.assigned_staff = this.currentUserName;
                this.updateItemAssignedStaff(it, this.currentUserName);
            }
            this.showToast('Đã tự nhận ' + unassigned.length + ' mặt hàng trong loại này!');
        },

        async assignCategoryItemsToStaff(categoryId, staffName) {
            if (!this.activeOrder || !staffName) return;
            const targetItems = (this.activeOrder.items || []).filter(it => 
                Number(it.category_id) === Number(categoryId) && 
                !this.isItemStaffLocked(it)
            );
            if (targetItems.length === 0) {
                this.showToast('Tất cả mặt hàng trong loại này đã bốc hoặc đã khóa', 'warning');
                return;
            }
            for (const it of targetItems) {
                it.assigned_staff = staffName;
                this.updateItemAssignedStaff(it, staffName);
            }
            this.showToast('Đã phân công ' + targetItems.length + ' mặt hàng cho: ' + staffName);
        },

        get filteredActiveItems() {
            if (!this.activeOrder) return [];
            let items = this.activeOrder.items;
            if (this.selectedCategoryFilter !== 'all') {
                items = items.filter(it => String(it.category_id) === String(this.selectedCategoryFilter));
            }
            if (this.pickerFilter === 'all') return items;
            if (this.pickerFilter === 'unassigned') return items.filter(it => !it.assigned_staff);
            return items.filter(it => it.assigned_staff === this.pickerFilter);
        },

        // ==========================================
        // BATCH FULFILLMENT (GỘP NHIỀU ĐƠN) METHODS
        // ==========================================
        toggleBatchOrder(ordId) {
            const id = Number(ordId);
            if (this.selectedBatchOrderIds.includes(id)) {
                this.selectedBatchOrderIds = this.selectedBatchOrderIds.filter(i => i !== id);
            } else {
                this.selectedBatchOrderIds.push(id);
            }
        },

        selectAllBatchOrders() {
            this.selectedBatchOrderIds = this.filteredOrders.map(o => o.id);
        },

        deselectAllBatchOrders() {
            this.selectedBatchOrderIds = [];
        },

        toggleSelectAllAggregated(checked) {
            this.selectedAggregatedSkus = checked ? this.aggregatedItems.map(a => a.sku) : [];
        },

        get aggregatedItems() {
            const ordersToAggregate = this.selectedBatchOrderIds.length > 0
                ? this.filteredOrders.filter(o => this.selectedBatchOrderIds.includes(o.id))
                : [];
            
            const map = {};
            ordersToAggregate.forEach(o => {
                (o.items || []).forEach(it => {
                    const key = it.sku || it.name;
                    if (!map[key]) {
                        map[key] = {
                            sku: it.sku || '',
                            name: it.name || '',
                            unit: it.unit || 'CÂY',
                            warehouse: o.warehouse || 'Kho Tổng Mê Linh (MQ)',
                            category_id: it.category_id || 0,
                            category_name: it.category_name || '',
                            totalSold: 0,
                            totalGift: 0,
                            totalRequired: 0,
                            totalPicked: 0,
                            contributingOrders: [],
                            assignedStaffList: []
                        };
                    }
                    const q = Number(it.quantity) || 0;
                    const g = Number(it.gift_quantity) || 0;
                    const req = q + g;
                    const p = Number(it.picked_qty) || 0;

                    map[key].totalSold += q;
                    map[key].totalGift += g;
                    map[key].totalRequired += req;
                    map[key].totalPicked += p;

                    map[key].contributingOrders.push({
                        orderId: o.id,
                        orderCode: o.order_code,
                        customerName: o.customer_name,
                        qty: q,
                        gift_quantity: g,
                        totalReq: req,
                        pickedQty: p,
                        itemRef: it,
                        orderRef: o
                    });

                    // Gom danh sách nhân sự đã được phân công
                    const staffAssigned = [];
                    if (it.assigned_staff) staffAssigned.push(it.assigned_staff);
                    if (Array.isArray(it.assigned_staff_list)) {
                        it.assigned_staff_list.forEach(s => { if (s && !staffAssigned.includes(s)) staffAssigned.push(s); });
                    }
                    staffAssigned.forEach(st => {
                        if (st && !map[key].assignedStaffList.includes(st)) {
                            map[key].assignedStaffList.push(st);
                        }
                    });
                });
            });

            const list = Object.values(map);
            list.forEach(ag => {
                if (ag.totalPicked >= ag.totalRequired && ag.totalRequired > 0) {
                    ag.status = 'Đã bốc xong';
                    ag.badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-300';
                    ag.isFullyPicked = true;
                } else if (ag.totalPicked > 0) {
                    ag.status = 'Đang bốc';
                    ag.badgeClass = 'bg-sky-50 text-sky-700 border-sky-300';
                    ag.isFullyPicked = false;
                } else {
                    ag.status = 'Chờ bốc';
                    ag.badgeClass = 'bg-amber-50 text-amber-800 border-amber-300';
                    ag.isFullyPicked = false;
                }

                // QC Batch calculations
                ag.totalChecked = ag.contributingOrders.reduce((sum, c) => sum + (Number(c.itemRef.checked_qty) || (c.itemRef.is_checked ? c.totalReq : 0)), 0);
                ag.isFullyChecked = ag.contributingOrders.length > 0 && ag.contributingOrders.every(c => Boolean(c.itemRef.is_checked));
                if (ag.isFullyChecked || (ag.totalChecked >= ag.totalRequired && ag.totalRequired > 0)) {
                    ag.qcStatus = 'Đã kiểm';
                    ag.qcBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-300';
                } else if (ag.totalChecked > 0) {
                    ag.qcStatus = 'Đang kiểm';
                    ag.qcBadgeClass = 'bg-teal-50 text-teal-700 border-teal-300';
                } else {
                    ag.qcStatus = 'Chờ kiểm';
                    ag.qcBadgeClass = 'bg-slate-100 text-slate-500 border-slate-200';
                }
                ag.checkNotes = ag.contributingOrders.map(c => c.itemRef.check_notes).filter(Boolean)[0] || '';
            });

            return list;
        },

        async pickFullBatchItem(ag) {
            if (!ag || !ag.contributingOrders) return;
            for (const c of ag.contributingOrders) {
                const req = c.totalReq;
                c.itemRef.picked_qty = req;
                c.itemRef.is_picked = true;
                c.itemRef.item_status = 'Đủ hàng';
                c.itemRef.shortage_reason = '';
                await this.saveItemPickingState(c.itemRef, {}, c.orderRef);
            }
            this.showToast('✓ Đã bốc đủ cả lô ' + ag.totalRequired + ' ' + (ag.unit || '') + ' cho mã ' + ag.sku, 'success');
        },

        async updateBatchItemPickedQty(ag, val) {
            if (!ag || !ag.contributingOrders) return;
            let targetQty = Math.max(0, Number(val) || 0);
            if (targetQty > ag.totalRequired && ag.totalRequired > 0) {
                targetQty = ag.totalRequired;
                this.showToast('Không được nhập quá tổng số lượng cần bốc (' + ag.totalRequired + ')!', 'warning');
            }

            let remaining = targetQty;
            for (const c of ag.contributingOrders) {
                const req = c.totalReq;
                const give = Math.min(remaining, req);
                remaining = Math.max(0, remaining - give);

                c.itemRef.picked_qty = give;
                if (give >= req && req > 0) {
                    c.itemRef.is_picked = true;
                    c.itemRef.item_status = 'Đủ hàng';
                    c.itemRef.shortage_reason = '';
                } else if (give > 0) {
                    c.itemRef.is_picked = true;
                    c.itemRef.item_status = 'Thiếu hàng';
                    if (!c.itemRef.shortage_reason) c.itemRef.shortage_reason = 'Hết hàng trên kệ kho';
                } else {
                    c.itemRef.is_picked = false;
                    c.itemRef.item_status = 'Chờ bốc';
                    c.itemRef.shortage_reason = '';
                }

                await this.saveItemPickingState(c.itemRef, { shortageReason: c.itemRef.shortage_reason }, c.orderRef);
            }
            this.showToast('Đã cập nhật số lượng bốc cho ' + ag.sku + ': ' + targetQty + ' / ' + ag.totalRequired + ' ' + (ag.unit || ''));
        },

        async assignBatchItemStaff(ag, staffName) {
            if (!ag || !staffName) return;
            for (const c of ag.contributingOrders) {
                c.itemRef.assigned_staff = staffName;
                if (!Array.isArray(c.itemRef.assigned_staff_list)) {
                    c.itemRef.assigned_staff_list = [];
                }
                if (!c.itemRef.assigned_staff_list.includes(staffName)) {
                    c.itemRef.assigned_staff_list.push(staffName);
                }
                await this.updateItemAssignedStaff(c.itemRef, staffName, c.orderRef);
            }
            this.showToast('Đã giao soạn ' + ag.sku + ' cho: ' + staffName);
        },

        async unassignBatchItemStaff(ag, staffName) {
            if (!ag || !staffName) return;
            for (const c of ag.contributingOrders) {
                if (Array.isArray(c.itemRef.assigned_staff_list)) {
                    c.itemRef.assigned_staff_list = c.itemRef.assigned_staff_list.filter(s => s !== staffName);
                }
                if (c.itemRef.assigned_staff === staffName) {
                    c.itemRef.assigned_staff = (c.itemRef.assigned_staff_list && c.itemRef.assigned_staff_list.length > 0) 
                        ? c.itemRef.assigned_staff_list[0] 
                        : null;
                }
                await this.updateItemAssignedStaff(c.itemRef, c.itemRef.assigned_staff || '', c.orderRef);
            }
            this.showToast('Đã hủy giao ' + staffName + ' cho mã ' + ag.sku);
        },

        async claimBatchItem(ag) {
            await this.assignBatchItemStaff(ag, this.currentUserName);
        },

        async claimBatchAll() {
            await this.assignBatchAll(this.currentUserName);
        },

        async assignBatchAll(staffName) {
            if (!staffName) return;
            const ordersToAssign = this.selectedBatchOrderIds.length > 0
                ? this.orders.filter(o => this.selectedBatchOrderIds.includes(o.id))
                : this.filteredOrders;
            
            if (ordersToAssign.length === 0) {
                this.showToast('Chưa chọn đơn hàng nào để phân công!', 'warning');
                return;
            }

            let count = 0;
            for (const ord of ordersToAssign) {
                for (const it of (ord.items || [])) {
                    if (!this.isItemStaffLocked(it)) {
                        it.assigned_staff = staffName;
                        if (!Array.isArray(it.assigned_staff_list)) it.assigned_staff_list = [];
                        if (!it.assigned_staff_list.includes(staffName)) it.assigned_staff_list.push(staffName);
                        this.updateItemAssignedStaff(it, staffName, ord);
                        count++;
                    }
                }
            }
            this.batchAssignStaff = '';
            this.showToast('Đã giao toàn bộ ' + count + ' mặt hàng trong các đơn đã chọn cho: ' + staffName, 'success');
        },

        async unassignBatchAll() {
            const ordersToUnassign = this.selectedBatchOrderIds.length > 0
                ? this.orders.filter(o => this.selectedBatchOrderIds.includes(o.id))
                : this.filteredOrders;
            
            if (ordersToUnassign.length === 0) {
                this.showToast('Chưa chọn đơn hàng nào!', 'warning');
                return;
            }

            let count = 0;
            for (const ord of ordersToUnassign) {
                for (const it of (ord.items || [])) {
                    if (!this.isItemStaffLocked(it)) {
                        it.assigned_staff = null;
                        it.assigned_staff_list = [];
                        this.updateItemAssignedStaff(it, '', ord);
                        count++;
                    }
                }
            }
            this.showToast('Đã hủy phân công toàn bộ ' + count + ' mặt hàng trong lô!', 'info');
        },

        async completeAllBatchOrders() {
            const ordersToComplete = this.selectedBatchOrderIds.length > 0
                ? this.orders.filter(o => this.selectedBatchOrderIds.includes(o.id))
                : this.filteredOrders;

            if (ordersToComplete.length === 0) {
                this.showToast('Không có đơn hàng nào được chọn!', 'warning');
                return;
            }

            if (!confirm('Xác nhận hoàn tất bốc hàng cho toàn bộ ' + ordersToComplete.length + ' đơn đã chọn và chuyển sang bước Kiểm Hàng?')) {
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                for (const ord of ordersToComplete) {
                    (ord.items || []).forEach(it => {
                        const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                        it.picked_qty = reqTotal;
                        it.is_picked = true;
                        it.item_status = 'Đủ hàng';
                        it.shortage_reason = null;
                    });
                    ord.status = 'Đã bốc xong';

                    // Gửi API cập nhật trạng thái đơn
                    await fetch('/api/orders/update-status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token || '' },
                        body: JSON.stringify({
                            id: ord.id,
                            status: 'Đã bốc xong'
                        })
                    });

                    // Đồng thời lưu trạng thái picking item
                    if (ord.items && ord.items.length > 0) {
                        this.saveItemPickingState(ord.items[0], { quickPickAll: true }, ord);
                    }
                }

                this.showToast('🎉 Hoàn tất bốc cả lô ' + ordersToComplete.length + ' đơn! Tất cả đã chuyển sang trạng thái "Đã bốc xong".', 'success');
                setTimeout(() => {
                    this.activeTab = 'qc';
                }, 800);
            } catch(e) {
                console.error('Error completing batch orders:', e);
                this.showToast('Lỗi khi hoàn tất bốc cả lô', 'error');
            }
        },

        // ========================================================
        // TAB 2: BATCH QC HANDLERS (KIỂM HÀNG GỘP NHIỀU ĐƠN)
        // ========================================================
        async qcCheckAllBatchItems() {
            if (!this.aggregatedItems || this.aggregatedItems.length === 0) return;
            try {
                for (const ag of this.aggregatedItems) {
                    for (const c of ag.contributingOrders) {
                        c.itemRef.is_checked = true;
                        const reqTotal = c.totalReq;
                        const picked = Number(c.itemRef.picked_qty) || 0;
                        c.itemRef.checked_qty = picked > 0 ? picked : reqTotal;
                        c.itemRef.checked_by = this.qcInspector;
                        c.itemRef.checked_at = new Date().toLocaleString('vi-VN');
                        await this.saveItemPickingState(c.itemRef, {
                            isQc: true,
                            isChecked: true,
                            checkedQty: c.itemRef.checked_qty,
                            checkedBy: this.qcInspector,
                            checkedAt: c.itemRef.checked_at
                        }, c.orderRef);
                    }
                }
                this.showToast('✓ Đã tích kiểm toàn bộ lô ' + this.aggregatedItems.length + ' mặt hàng gộp!', 'success');
            } catch(e) {
                console.error('Error qcCheckAllBatchItems:', e);
                this.showToast('Lỗi khi tích kiểm cả lô', 'error');
            }
        },

        async uncheckAllBatchItems() {
            if (!this.aggregatedItems || this.aggregatedItems.length === 0) return;
            try {
                for (const ag of this.aggregatedItems) {
                    for (const c of ag.contributingOrders) {
                        c.itemRef.is_checked = false;
                        c.itemRef.checked_qty = 0;
                        await this.saveItemPickingState(c.itemRef, {
                            isQc: true,
                            isChecked: false,
                            checkedQty: 0
                        }, c.orderRef);
                    }
                }
                this.showToast('Đã bỏ tích kiểm cả lô hàng', 'info');
            } catch(e) {
                console.error('Error uncheckAllBatchItems:', e);
                this.showToast('Lỗi khi bỏ tích kiểm cả lô', 'error');
            }
        },

        async toggleBatchItemQc(ag, checked) {
            if (!ag || !ag.contributingOrders) return;
            try {
                for (const c of ag.contributingOrders) {
                    c.itemRef.is_checked = checked;
                    if (checked) {
                        const picked = Number(c.itemRef.picked_qty) || 0;
                        c.itemRef.checked_qty = picked > 0 ? picked : c.totalReq;
                        c.itemRef.checked_by = this.qcInspector;
                        c.itemRef.checked_at = new Date().toLocaleString('vi-VN');
                    } else {
                        c.itemRef.checked_qty = 0;
                    }
                    await this.saveItemPickingState(c.itemRef, {
                        isQc: true,
                        isChecked: checked,
                        checkedQty: c.itemRef.checked_qty,
                        checkedBy: this.qcInspector,
                        checkedAt: c.itemRef.checked_at
                    }, c.orderRef);
                }
                this.showToast((checked ? '✓ Đã kiểm mặt hàng ' : 'Đã bỏ kiểm mặt hàng ') + ag.sku);
            } catch(e) {
                console.error('Error toggleBatchItemQc:', e);
            }
        },

        async qcCheckFullBatchItem(ag) {
            if (!ag || !ag.contributingOrders) return;
            try {
                for (const c of ag.contributingOrders) {
                    c.itemRef.is_checked = true;
                    const picked = Number(c.itemRef.picked_qty) || 0;
                    c.itemRef.checked_qty = picked > 0 ? picked : c.totalReq;
                    c.itemRef.checked_by = this.qcInspector;
                    c.itemRef.checked_at = new Date().toLocaleString('vi-VN');
                    await this.saveItemPickingState(c.itemRef, {
                        isQc: true,
                        isChecked: true,
                        checkedQty: c.itemRef.checked_qty,
                        checkedBy: this.qcInspector,
                        checkedAt: c.itemRef.checked_at
                    }, c.orderRef);
                }
                this.showToast('✓ Đã xác nhận kiểm đủ cả lô cho mã: ' + ag.sku, 'success');
            } catch(e) {
                console.error('Error qcCheckFullBatchItem:', e);
            }
        },

        async updateBatchItemCheckedQty(ag, val) {
            if (!ag || !ag.contributingOrders) return;
            let targetQty = Math.max(0, Number(val) || 0);
            if (targetQty > ag.totalRequired && ag.totalRequired > 0) {
                targetQty = ag.totalRequired;
                this.showToast('Không được nhập quá tổng số lượng cần kiểm (' + ag.totalRequired + ')!', 'warning');
            }

            let remain = targetQty;
            for (let i = 0; i < ag.contributingOrders.length; i++) {
                const c = ag.contributingOrders[i];
                const req = c.totalReq;
                let allocated = 0;
                if (i === ag.contributingOrders.length - 1) {
                    allocated = remain;
                } else {
                    allocated = Math.min(req, remain);
                    remain -= allocated;
                }

                c.itemRef.checked_qty = allocated;
                c.itemRef.is_checked = allocated >= req && req > 0;
                c.itemRef.checked_by = this.qcInspector;
                c.itemRef.checked_at = new Date().toLocaleString('vi-VN');

                await this.saveItemPickingState(c.itemRef, {
                    isQc: true,
                    isChecked: c.itemRef.is_checked,
                    checkedQty: allocated,
                    checkedBy: this.qcInspector,
                    checkedAt: c.itemRef.checked_at
                }, c.orderRef);
            }
            this.showToast('Đã lưu SL thực kiểm ' + targetQty + ' cho mã ' + ag.sku);
        },

        async updateBatchItemCheckNotes(ag, val) {
            if (!ag || !ag.contributingOrders) return;
            for (const c of ag.contributingOrders) {
                c.itemRef.check_notes = val;
                await this.saveItemPickingState(c.itemRef, {
                    isQc: true,
                    checkNotes: val
                }, c.orderRef);
            }
            this.showToast('Đã lưu ghi chú kiểm hàng cho mã ' + ag.sku);
        },

        async completeAllBatchQcOrders() {
            const ordersToComplete = this.batchOrders;
            if (!ordersToComplete || ordersToComplete.length === 0) {
                this.showToast('Chưa chọn đơn hàng nào trong danh sách gộp!', 'warning');
                return;
            }

            if (!confirm('Bạn có chắc chắn muốn hoàn tất kiểm hàng (QC) cho cả ' + ordersToComplete.length + ' đơn gộp này để chuyển sang trạng thái "Sẵn sàng xuất xe"?')) {
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                for (const ord of ordersToComplete) {
                    (ord.items || []).forEach(it => {
                        const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                        it.is_checked = true;
                        it.checked_qty = Number(it.picked_qty) > 0 ? it.picked_qty : reqTotal;
                        it.checked_by = this.qcInspector;
                        it.checked_at = new Date().toLocaleString('vi-VN');
                    });
                    ord.status = 'Đã kiểm hàng';

                    // Gửi API cập nhật trạng thái đơn
                    await fetch('/api/orders/update-status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token || '' },
                        body: JSON.stringify({
                            id: ord.id,
                            status: 'Đã kiểm hàng'
                        })
                    });

                    // Đồng thời gửi sự kiện qcAllChecked
                    if (ord.items && ord.items.length > 0) {
                        await this.saveItemPickingState(ord.items[0], { isQc: true, qcAllChecked: true }, ord);
                    }
                }

                this.showToast('🎉 Hoàn tất kiểm hàng (QC) cả lô ' + ordersToComplete.length + ' đơn! Tất cả đã chuyển sang trạng thái "Đã kiểm hàng" sẵn sàng xuất xe.', 'success');
            } catch(e) {
                console.error('Error completing batch QC orders:', e);
                this.showToast('Lỗi khi hoàn tất kiểm cả lô', 'error');
            }
        },

        // Format Helpers
        formatDate(dateStr) {
            if (!dateStr) return '';
            try {
                const parts = String(dateStr).substring(0, 10).split('-');
                if (parts.length === 3) {
                    return `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
                return dateStr;
            } catch(e) {
                return dateStr;
            }
        },

        getAssignedStaffText(ord) {
            if (!ord.items || ord.items.length === 0) return 'Chưa phân công';
            const first = ord.items.find(i => i.assigned_staff);
            return first ? first.assigned_staff : 'Chưa phân công';
        },

        getAssignedItemsCount(ord) {
            if (!ord.items) return 0;
            return ord.items.filter(i => i.assigned_staff).length;
        },

        getProgressRatio(ord) {
            if (!ord.items || ord.items.length === 0) return '0/0 mặt hàng';
            const done = ord.items.filter(i => {
                if (this.activeTab === 'picking') {
                    const req = Number(i.quantity) + Number(i.gift_quantity || 0);
                    return i.is_picked || (Number(i.picked_qty) >= req && req > 0);
                } else {
                    return i.is_checked;
                }
            }).length;
            return done + '/' + ord.items.length + ' mặt hàng';
        },

        getProgressPercent(ord) {
            if (!ord.items || ord.items.length === 0) return 0;
            const done = ord.items.filter(i => {
                if (this.activeTab === 'picking') {
                    const req = Number(i.quantity) + Number(i.gift_quantity || 0);
                    return i.is_picked || (Number(i.picked_qty) >= req && req > 0);
                } else {
                    return i.is_checked;
                }
            }).length;
            return Math.round((done / ord.items.length) * 100);
        },

        getOrderStatusBadgeClass(status) {
            switch (status) {
                case 'Đã bốc xong':
                case 'Đã kiểm hàng':
                case 'Sẵn sàng xuất xe':
                    return 'bg-emerald-50 text-emerald-700 border-emerald-300';
                case 'Đang bốc hàng':
                case 'Kho đang soạn':
                case 'Đang kiểm hàng':
                    return 'bg-sky-50 text-sky-700 border-sky-300';
                case 'Soạn hàng':
                case 'Soạn bốc':
                    return 'bg-purple-50 text-purple-700 border-purple-300';
                case 'Chờ bốc':
                case 'Chốt đơn':
                case 'Báo giá':
                default:
                    return 'bg-amber-50 text-amber-800 border-amber-300';
            }
        },

        getItemStatusBadgeClass(it) {
            const req = Number(it.quantity) + Number(it.gift_quantity || 0);
            if (it.item_status === 'Đủ hàng' || it.is_picked || (Number(it.picked_qty) >= req && req > 0)) {
                return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            }
            if (it.item_status === 'Thiếu hàng') {
                return 'bg-rose-50 text-rose-700 border-rose-200';
            }
            return 'bg-slate-100 text-slate-700 border-slate-200';
        },

        isOrderLockedForPicking(ord) {
            return ['Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Đang vận chuyển', 'Đã giao'].includes(ord.status);
        },

        isItemStaffLocked(it) {
            if (!it) return false;
            if (it._forceUnlock) return false;
            if (this.isOrderLockedForPicking(this.activeOrder)) return true;

            // 1. Khi thay đổi số lượng (_justModified), LUÔN MỞ PHÂN CÔNG để phân công lại!
            if (it._justModified) return false;

            // 2. Nếu chưa phân công thì không bao giờ khóa phân công
            if (!it.assigned_staff && (!it.assigned_staff_list || it.assigned_staff_list.length === 0)) return false;

            // 3. Nếu số lượng đã bốc < số lượng cần bốc (chưa bốc xong hoặc thiếu hàng), MỞ PHÂN CÔNG
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            if (Number(it.picked_qty || 0) < reqTotal) {
                return false;
            }

            // 4. Chỉ khóa khi đã bốc đủ 100% số lượng (is_picked và picked_qty >= reqTotal)
            return Boolean(it.is_picked && Number(it.picked_qty || 0) >= reqTotal && reqTotal > 0);
        },

        canUserPickItem(it) {
            if (!it) return false;
            // Nếu đơn đã khóa kiểm xong / đang giao thì không ai được sửa
            if (this.isOrderLockedForPicking(this.activeOrder)) return false;

            // Nếu là Admin và bật chế độ can thiệp trực tiếp
            if (this.isUserAdmin && this.adminOverrideMode) return true;

            // Chưa phân công -> Không ai được nhập số lượng bốc (phải chỉ định người bốc trước)
            if (!it.assigned_staff && (!it.assigned_staff_list || it.assigned_staff_list.length === 0)) {
                return false;
            }

            // Chỉ người được phân công vào mặt hàng này mới được nhập số lượng bốc
            const curName = (this.currentUserName || '').trim().toLowerCase();
            if (it.assigned_staff && it.assigned_staff.trim().toLowerCase() === curName) {
                return true;
            }
            if (Array.isArray(it.assigned_staff_list)) {
                return it.assigned_staff_list.some(st => st && st.trim().toLowerCase() === curName);
            }
            return false;
        },

        async updateItemAssignedStaff(item, staffName, explicitOrder = null) {
            item.assigned_staff = staffName || null;
            const targetOrder = explicitOrder || (item && item._order) || this.activeOrder;
            const orderId = targetOrder ? targetOrder.id : (item ? item.order_id : null);
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                await fetch('/api/inventory/picking/update-item', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderId: orderId,
                        itemId: item.id,
                        sku: item.sku,
                        assignedStaff: staffName || null,
                        performerName: '{{ $currentUser->name }}'
                    })
                });
                if (staffName) {
                    this.showToast('Đã phân công soạn cho: ' + staffName);
                } else {
                    this.showToast('Đã hủy phân công người soạn');
                }
            } catch(e) {
                console.error('Error assigning staff:', e);
                this.showToast('Không thể cập nhật người soạn', 'error');
            }
        },

        async assignAllItemsToStaff(staffName) {
            if (!staffName || !this.activeOrder) return;
            const unlockedItems = this.activeOrder.items.filter(it => !this.isItemStaffLocked(it));
            if (unlockedItems.length === 0) {
                this.showToast('Tất cả mặt hàng đã bốc hoặc đã bị khóa!', 'error');
                return;
            }
            unlockedItems.forEach(it => {
                it.assigned_staff = staffName;
            });

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                for (const it of unlockedItems) {
                    fetch('/api/inventory/picking/update-item', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                        body: JSON.stringify({
                            orderId: this.activeOrder.id,
                            itemId: it.id,
                            sku: it.sku,
                            assignedStaff: staffName,
                            performerName: '{{ $currentUser->name }}'
                        })
                    });
                }
                this.showToast('Đã phân công ' + unlockedItems.length + ' mặt hàng cho: ' + staffName);
            } catch(e) {
                console.error('Bulk assign error:', e);
                this.showToast('Không thể phân công hàng loạt', 'error');
            }
        },

        // API Updates
        async saveItemPickingState(item, extra = {}, explicitOrder = null) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const targetOrder = explicitOrder || (item && item._order) || this.activeOrder;
                const orderId = targetOrder ? targetOrder.id : (item ? item.order_id : null);
                const isQcAction = Boolean(extra && (extra.isQc || extra.isChecked !== undefined || extra.checkedQty !== undefined || extra.checkNotes !== undefined || extra.qcAllChecked || extra.qcCategoryChecked));
                const payload = {
                    orderId: orderId,
                    itemId: item ? item.id : null,
                    sku: item ? item.sku : null,
                    performerName: '{{ $currentUser->name }}',
                    adminOverride: Boolean(this.isUserAdmin && this.adminOverrideMode),
                    extraData: extra
                };
                if (!isQcAction && item) {
                    payload.pickedQty = item.picked_qty;
                    payload.itemStatus = item.item_status;
                    payload.shortageReason = item.shortage_reason;
                }
                const res = await fetch('/api/inventory/picking/update-item', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data && data.success === false && data.message) {
                    this.showToast(data.message, 'error');
                } else if (data && data.success) {
                    if (data.orderStatus && targetOrder) {
                        targetOrder.status = data.orderStatus;
                        const matchedOrd = this.orders.find(o => o.id === targetOrder.id);
                        if (matchedOrd) matchedOrd.status = data.orderStatus;
                    }
                    if (data.groupProgress && data.groupProgress.groups && targetOrder) {
                        targetOrder.groups_progress = data.groupProgress.groups;
                        const matchedOrd = this.orders.find(o => o.id === targetOrder.id);
                        if (matchedOrd) matchedOrd.groups_progress = data.groupProgress.groups;
                    }
                }
                return data;
            } catch (e) {
                console.error('Picking sync error:', e);
            }
        },

        pickFullItem(it) {
            if (!this.canUserPickItem(it)) {
                this.showToast('Bạn không được phân công soạn mặt hàng này!', 'error');
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            it.picked_qty = reqTotal;
            it.is_picked = true;
            it.item_status = 'Đủ hàng';
            this.saveItemPickingState(it);
            this.showToast('Đã bốc đủ ' + reqTotal + ' ' + (it.unit || '') + ' cho ' + it.sku);
        },

        handlePickCheckChange(it) {
            if (!this.canUserPickItem(it)) {
                it.is_picked = !it.is_picked;
                this.showToast('Bạn không được phân công soạn mặt hàng này!', 'error');
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            const picked = Number(it.picked_qty || 0);
            if (it.is_picked) {
                if (picked <= 0) {
                    it.picked_qty = reqTotal;
                    it.item_status = 'Đủ hàng';
                    it.shortage_reason = '';
                } else if (picked < reqTotal) {
                    it.item_status = 'Thiếu hàng';
                    if (!it.shortage_reason) {
                        it.shortage_reason = 'Hết hàng trên kệ kho';
                    }
                } else {
                    it.item_status = 'Đủ hàng';
                    it.shortage_reason = '';
                }
            } else {
                if (picked > 0 && picked < reqTotal) {
                    it.item_status = 'Thiếu hàng';
                    if (!it.shortage_reason) {
                        it.shortage_reason = 'Hết hàng trên kệ kho';
                    }
                } else if (picked >= reqTotal && reqTotal > 0) {
                    it.item_status = 'Đủ hàng';
                } else {
                    it.item_status = 'Chờ bốc';
                }
            }
            this.saveItemPickingState(it, {
                shortageReason: it.shortage_reason
            });
        },

        getOrderSupervisor(ord) {
            if (!ord) return 'Admin Kho';
            return ord.supervisor || ord.order_creator || (ord.dealer && ord.dealer.staff_in_charge) || 'Admin KBTech';
        },

        isItemShort(it) {
            if (!it) return false;
            const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            const picked = Number(it.picked_qty || 0);
            // Nếu đã bốc đủ hoặc bốc dư số lượng cần, TUYỆT ĐỐI KHÔNG THIẾU
            if (req > 0 && picked >= req) {
                if (it.item_status === 'Thiếu hàng' || it.shortage_reason) {
                    it.item_status = 'Đủ hàng';
                    it.shortage_reason = '';
                    it.is_picked = true;
                }
                return false;
            }
            // Chỉ coi là thiếu khi số lượng thực tế bốc < số lượng cần bốc
            return (picked < req) && (it.item_status === 'Thiếu hàng' || Boolean(it.shortage_reason));
        },

        getShortageQty(it) {
            if (!it) return 0;
            const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            const picked = Number(it.picked_qty || 0);
            return Math.max(0, req - picked);
        },

        getShortageItems(ord) {
            if (!ord || !ord.items) return [];
            return ord.items.filter(it => this.isItemShort(it));
        },

        countShortageItems(ord) {
            return this.getShortageItems(ord).length;
        },

        validateAndSavePickedQty(it) {
            if (!this.canUserPickItem(it)) {
                this.showToast('Bạn không được phân công soạn mặt hàng này!', 'error');
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            let val = Number(it.picked_qty || 0);

            if (val > reqTotal && reqTotal > 0) {
                it.picked_qty = reqTotal;
                this.showToast('Không được nhập quá số lượng cần bốc (' + reqTotal + ' ' + (it.unit || '') + ')!', 'warning');
            } else if (val < 0) {
                it.picked_qty = 0;
            }
            this.saveItemPickedQty(it);
        },

        saveItemPickedQty(it) {
            if (!this.canUserPickItem(it)) {
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            let picked = Number(it.picked_qty || 0);

            if (picked > reqTotal && reqTotal > 0) {
                picked = reqTotal;
                it.picked_qty = reqTotal;
            } else if (picked < 0) {
                picked = 0;
                it.picked_qty = 0;
            }

            if (picked >= reqTotal && reqTotal > 0) {
                it.is_picked = true;
                it.item_status = 'Đủ hàng';
                it.shortage_reason = '';
            } else if (picked > 0 && picked < reqTotal) {
                it.is_picked = true;
                it.item_status = 'Thiếu hàng';
                if (!it.shortage_reason) {
                    it.shortage_reason = 'Hết hàng trên kệ kho';
                }
            } else {
                it.is_picked = false;
                it.item_status = 'Chờ bốc';
                it.shortage_reason = '';
            }

            this.saveItemPickingState(it, {
                shortageReason: it.shortage_reason
            });
        },

        saveItemShortageReason(it) {
            it.item_status = 'Thiếu hàng';
            this.saveItemPickingState(it, {
                shortageReason: it.shortage_reason
            });
            this.showToast('Đã lưu lý do thiếu: ' + it.shortage_reason);
        },

        async reportShortageActiveOrder() {
            if (!this.activeOrder) return;
            const shortItems = this.getShortageItems(this.activeOrder);
            if (shortItems.length === 0) {
                this.showToast('Không có sản phẩm nào bị thiếu trong đơn này!', 'warning');
                return;
            }

            const supervisor = this.getOrderSupervisor(this.activeOrder);
            const payloadItems = shortItems.map(it => ({
                id: it.id,
                sku: it.sku,
                name: it.name,
                unit: it.unit || 'cây',
                req_qty: Number(it.quantity || 0) + Number(it.gift_quantity || 0),
                picked_qty: Number(it.picked_qty || 0),
                shortage_qty: this.getShortageQty(it),
                reason: it.shortage_reason || 'Hết hàng trên kệ kho'
            }));

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/inventory/picking/report-shortage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        orderId: this.activeOrder.id,
                        reporterName: '{{ $currentUser->name }}',
                        shortageItems: payloadItems
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.activeOrder.status = 'Kho báo thiếu hàng';
                    this.activeOrder.shortage_reported = true;

                    // Phát tín hiệu đồng bộ tức thì cho các tab khác
                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({
                            type: 'SHORTAGE_REPORTED',
                            orderId: this.activeOrder.id,
                            orderCode: this.activeOrder.order_code,
                            supervisor: supervisor,
                            reporter: '{{ $currentUser->name }}',
                            shortageItems: payloadItems,
                            timestamp: Date.now()
                        });
                    } catch(e) {}
                    try {
                        localStorage.setItem('erp_order_sync_trigger', JSON.stringify({
                            time: Date.now(),
                            type: 'SHORTAGE_REPORTED',
                            orderId: this.activeOrder.id,
                            supervisor: supervisor
                        }));
                    } catch(e) {}

                    this.showToast('🔔 ĐÃ GỬI THÔNG BÁO THIẾU HÀNG TỚI NGƯỜI PHỤ TRÁCH ĐƠN: ' + supervisor + '!', 'warning');
                    this.playAlertSound();
                } else {
                    this.showToast(data.message || 'Lỗi gửi cảnh báo thiếu hàng', 'error');
                }
            } catch (e) {
                console.error('Error reporting shortage:', e);
                this.showToast('Không thể gửi cảnh báo thiếu hàng', 'error');
            }
        },

        async reportShortageItem(it) {
            if (!this.activeOrder) return;
            const supervisor = this.getOrderSupervisor(this.activeOrder);
            const shortQty = this.getShortageQty(it);
            const reason = it.shortage_reason || 'Hết hàng trên kệ kho';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/inventory/picking/report-shortage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        orderId: this.activeOrder.id,
                        reporterName: '{{ $currentUser->name }}',
                        shortageItems: [{
                            id: it.id,
                            sku: it.sku,
                            name: it.name,
                            unit: it.unit || 'cây',
                            req_qty: Number(it.quantity || 0) + Number(it.gift_quantity || 0),
                            picked_qty: Number(it.picked_qty || 0),
                            shortage_qty: shortQty,
                            reason: reason
                        }]
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.activeOrder.status = 'Kho báo thiếu hàng';
                    it.item_status = 'Thiếu hàng';
                    it.shortage_reason = reason;

                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({
                            type: 'SHORTAGE_REPORTED',
                            orderId: this.activeOrder.id,
                            orderCode: this.activeOrder.order_code,
                            supervisor: supervisor,
                            reporter: '{{ $currentUser->name }}',
                            shortageItems: [{
                                sku: it.sku,
                                name: it.name,
                                unit: it.unit || 'cây',
                                shortage_qty: shortQty,
                                reason: reason
                            }],
                            timestamp: Date.now()
                        });
                    } catch(e) {}
                    try {
                        localStorage.setItem('erp_order_sync_trigger', JSON.stringify({
                            time: Date.now(),
                            type: 'SHORTAGE_REPORTED',
                            orderId: this.activeOrder.id,
                            supervisor: supervisor
                        }));
                    } catch(e) {}

                    this.showToast('🔔 ĐÃ GỬI CẢNH BÁO THIẾU ' + shortQty + ' ' + (it.unit || '') + ' (' + it.sku + ') TỚI NGƯỜI PHỤ TRÁCH ĐƠN: ' + supervisor, 'warning');
                    this.playAlertSound();
                } else {
                    this.showToast(data.message || 'Lỗi gửi cảnh báo thiếu hàng', 'error');
                }
            } catch(e) {
                console.error('Error reporting shortage item:', e);
                this.showToast('Không thể gửi cảnh báo thiếu hàng', 'error');
            }
        },

        isAllGroupsPickingCompleted(ord) {
            if (!ord) return false;
            if (ord.groups_progress && ord.groups_progress.length > 0) {
                return ord.groups_progress.every(g => g.is_picking_completed);
            }
            if (!ord.items || ord.items.length === 0) return true;
            return ord.items.every(it => {
                const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                return (it.is_picked || Number(it.picked_qty) >= req) && it.item_status !== 'Thiếu hàng';
            });
        },

        isAllGroupsQcCompleted(ord) {
            if (!ord) return false;
            if (ord.groups_progress && ord.groups_progress.length > 0) {
                return ord.groups_progress.every(g => g.is_qc_completed);
            }
            if (!ord.items || ord.items.length === 0) return true;
            return ord.items.every(it => {
                const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                return it.is_checked || Number(it.checked_qty) >= req;
            });
        },

        getPendingPickingGroupsText(ord) {
            if (!ord || !ord.groups_progress) return '';
            const pending = ord.groups_progress.filter(g => !g.is_picking_completed);
            return pending.map(g => g.group_name + ' (' + g.picked_count + '/' + g.items_count + ')').join(', ');
        },

        getPendingQcGroupsText(ord) {
            if (!ord || !ord.groups_progress) return '';
            const pending = ord.groups_progress.filter(g => !g.is_qc_completed);
            return pending.map(g => g.group_name + ' (' + g.checked_count + '/' + g.items_count + ')').join(', ');
        },

        async quickPickAllActiveOrder() {
            if (!this.activeOrder) return;
            const itemsToPick = this.activeOrder.items.filter(it => this.canUserPickItem(it));
            if (itemsToPick.length === 0) {
                this.showToast('Bạn không được phân công hoặc không phụ trách mặt hàng nào trong đơn này!', 'error');
                return;
            }
            itemsToPick.forEach(it => {
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                it.picked_qty = reqTotal;
                it.is_picked = true;
                it.item_status = 'Đủ hàng';
                it.shortage_reason = null;
            });

            const res = await this.saveItemPickingState(itemsToPick[0], {
                quickPickAll: true
            });

            if (res && res.success) {
                if (res.allPicked) {
                    this.showToast('🎉 TẤT CẢ các nhóm đã bốc xong! Đơn đã chuyển sang trạng thái "Đã bốc xong".', 'success');
                } else {
                    const pendingText = this.getPendingPickingGroupsText(this.activeOrder);
                    this.showToast('Đã bốc đủ ' + itemsToPick.length + ' món của nhóm bạn! Đơn đang chờ nhóm: ' + (pendingText || 'khác') + ' hoàn tất để chuyển trạng thái.', 'warning');
                }
            } else {
                this.showToast('Đã bốc đủ ' + itemsToPick.length + ' mặt hàng được phân công cho bạn!');
            }
        },

        async completePickingAndSendToQc() {
            if (!this.activeOrder) return;
            const itemsToPick = this.activeOrder.items.filter(it => this.canUserPickItem(it) || !it.is_picked);
            itemsToPick.forEach(it => {
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                if (Number(it.picked_qty || 0) <= 0) {
                    it.picked_qty = reqTotal;
                }
                it.is_picked = true;
                it.item_status = 'Đủ hàng';
                it.shortage_reason = null;
            });

            const targetItem = itemsToPick[0] || this.activeOrder.items[0];
            const res = await this.saveItemPickingState(targetItem, {
                quickPickAll: true
            });

            if (res && res.success) {
                if (res.allPicked) {
                    this.showToast('🎉 TẤT CẢ các nhóm đã bốc xong! Đơn đã chuyển sang trạng thái "Đã bốc xong" & sẵn sàng kiểm hàng.', 'success');
                    setTimeout(() => {
                        this.activeTab = 'qc';
                    }, 600);
                } else {
                    const pendingText = this.getPendingPickingGroupsText(this.activeOrder);
                    this.showToast('Nhóm của bạn đã hoàn thành phần việc! Đơn hàng đang chờ nhóm [' + (pendingText || 'các nhóm khác') + '] hoàn tất để chuyển sang "Đã bốc xong".', 'warning');
                }
            }
        },

        // Tab 2 QC Functions
        handleQcCheckChange(it) {
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            if (it.is_checked) {
                if (!it.checked_qty || Number(it.checked_qty) <= 0) {
                    it.checked_qty = Number(it.picked_qty) > 0 ? it.picked_qty : reqTotal;
                }
            }
            this.saveItemQcQty(it);
        },

        saveItemQcQty(it) {
            this.saveItemPickingState(it, {
                isChecked: it.is_checked,
                checkedQty: it.checked_qty,
                checkedBy: this.qcInspector,
                checkedAt: new Date().toLocaleString('vi-VN')
            });
        },

        saveItemCheckNotes(it) {
            this.saveItemPickingState(it, {
                checkNotes: it.check_notes
            });
        },

        async qcCheckAllActiveOrder() {
            if (!this.activeOrder) return;
            this.activeOrder.items.forEach(it => {
                it.is_checked = true;
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                it.checked_qty = Number(it.picked_qty) > 0 ? it.picked_qty : reqTotal;
            });

            const res = await this.saveItemPickingState(this.activeOrder.items[0], {
                qcAllChecked: true
            });

            if (res && res.success) {
                if (res.allChecked) {
                    this.showToast('🎉 Toàn bộ các nhóm đã kiểm hàng (QC) xong! Đơn đã chuyển sang trạng thái "Đã kiểm hàng".', 'success');
                } else {
                    const pendingText = this.getPendingQcGroupsText(this.activeOrder);
                    this.showToast('Đã tích kiểm xong phần hàng của nhóm bạn! Đơn đang chờ nhóm [' + (pendingText || 'các nhóm khác') + '] kiểm tra đạt chuẩn.', 'warning');
                }
            }
        },

        async confirmQcAndReadyForDelivery() {
            if (!this.activeOrder) return;
            const res = await this.saveItemPickingState(this.activeOrder.items[0], {
                qcAllChecked: true
            });

            if (res && res.success) {
                if (res.allChecked) {
                    this.showToast('✅ Xác nhận hoàn tất QC toàn đơn! Tất cả các nhóm đã xong, đơn hàng sẵn sàng xuất bến xếp lên xe.', 'success');
                } else {
                    const pendingText = this.getPendingQcGroupsText(this.activeOrder);
                    this.showToast('⚠️ Chưa thể chuyển trạng thái "Đã kiểm hàng"! Còn các nhóm chưa hoàn thành kiểm tra: ' + pendingText, 'warning');
                }
            }
        },

        // ==========================================
        // REALTIME SYNC & NOTIFICATION ENGINE
        // ==========================================
        startRealtimePolling() {
            if (this.realtimePollTimer) clearInterval(this.realtimePollTimer);
            this.realtimePollTimer = setInterval(() => {
                this.fetchRealtimeEvents();
            }, 1500);
        },

        async fetchRealtimeEvents() {
            if (this.isPolling) return;
            this.isPolling = true;
            try {
                const res = await fetch('/inventory/picking/events?since_id=' + this.lastSyncEventId);
                if (!res.ok) return;
                const data = await res.json();
                if (data.success && Array.isArray(data.events) && data.events.length > 0) {
                    this.lastSyncEventId = Number(data.latest_id) || this.lastSyncEventId;
                    for (const ev of data.events) {
                        this.handleRealtimeEvent(ev);
                    }
                }
            } catch(e) {
                // Silently handle brief network drop during non-blocking poll
            } finally {
                this.isPolling = false;
            }
        },

        goToAlertOrder(alert) {
            if (!alert) return;
            this.filterPickingDate = ''; // Tắt lọc ngày để đảm bảo luôn hiển thị đơn
            this.filterOrderType = 'all'; // Đảm bảo không bị ẩn bởi bộ lọc loại đơn
            this.searchQuery = alert.orderCode || '';
            const ord = this.orders.find(o => o.order_code === alert.orderCode || Number(o.id) === Number(alert.orderId));
            if (ord) {
                this.activeOrderId = ord.id;
                this.pickerFilter = 'all';
                this.activeTab = 'picking';
            }
            this.showToast('Đang hiển thị đơn: ' + (alert.orderCode || ''));
        },

        handleRealtimeEvent(ev) {
            console.log('⚡ Realtime picking event received:', ev);

            if (ev.eventType === 'ORDER_ITEMS_MODIFIED' || ev.eventType === 'ORDER_UPDATED') {
                const orderId = Number(ev.orderId);
                const extra = ev.extraData || {};
                const changedSkus = (extra.changes || []).map(c => c.sku).filter(Boolean);
                const changeSummary = (extra.changes || []).map(c => c.message).filter(Boolean).join('; ');
                
                const modifiedAlertObj = {
                    orderId: orderId,
                    orderCode: extra.orderCode || ('DH-' + orderId),
                    time: extra.modifiedAt || new Date().toLocaleTimeString('vi-VN'),
                    performer: ev.performerName || extra.performer || 'Kinh doanh',
                    pickers: ev.assignedStaff || (extra.assignedPickers && extra.assignedPickers.length ? extra.assignedPickers.join(', ') : 'Chưa phân công'),
                    supervisor: extra.supervisor || extra.orderCreator || 'Admin Kho',
                    changes: extra.changes || []
                };

                const orderExists = this.orders.some(o => Number(o.id) === orderId || o.order_code === extra.orderCode);

                if (orderExists) {
                    // Cập nhật lại toàn bộ mảng orders để kích hoạt reactivity của Alpine.js
                    this.orders = this.orders.map(o => {
                        if (Number(o.id) === orderId || o.order_code === extra.orderCode) {
                            const newItems = Array.isArray(extra.items) ? extra.items : (o.items || []);
                            const taggedItems = newItems.map(it => {
                                const isMod = changedSkus.length === 0 || changedSkus.includes(it.sku);
                                return {
                                    ...it,
                                    _justModified: isMod,
                                    _forceUnlock: isMod ? true : Boolean(it._forceUnlock)
                                };
                            });
                            return {
                                ...o,
                                items: taggedItems,
                                status: ev.orderStatus || o.status,
                                _hasItemsModified: true,
                                _modifiedAlert: modifiedAlertObj,
                                _lastChangeSummary: changeSummary
                            };
                        }
                        return o;
                    });

                    // Nếu đang xem đúng đơn này, kích hoạt lại reactivity để DOM bảng cập nhật số lượng ngay lập tức
                    if (this.activeOrderId === orderId || (this.activeOrder && this.activeOrder.order_code === extra.orderCode)) {
                        const curId = this.activeOrderId;
                        this.activeOrderId = null;
                        this.$nextTick(() => { this.activeOrderId = curId; });
                    }
                } else if (extra.items && (extra.orderCode || ev.orderId)) {
                    // Đơn hàng mới bổ sung hoặc được chuyển sang trạng thái soạn hàng
                    const newOrd = {
                        id: orderId,
                        order_code: extra.orderCode || ('DH-' + orderId),
                        customer_name: extra.customerName || 'Khách hàng',
                        warehouse: extra.warehouse || 'Kho Tổng Mê Linh (MQ)',
                        status: extra.status || 'Soạn hàng',
                        picking_date: extra.pickingDate || '{{ date("Y-m-d") }}',
                        items: (extra.items || []).map(it => {
                            const isMod = changedSkus.length === 0 || changedSkus.includes(it.sku);
                            return {
                                ...it,
                                _justModified: isMod,
                                _forceUnlock: isMod ? true : Boolean(it._forceUnlock)
                            };
                        }),
                        _hasItemsModified: true,
                        _modifiedAlert: modifiedAlertObj,
                        _lastChangeSummary: changeSummary
                    };
                    this.orders = [newOrd, ...this.orders];
                }

                // KÍCH HOẠT THÔNG BÁO NỔI GÓC MÀN HÌNH CHO NGƯỜI BỐC HÀNG
                this.realtimeAlert = modifiedAlertObj;
                this.playAlertSound();
                if (this.alertDismissTimer) clearTimeout(this.alertDismissTimer);
                this.alertDismissTimer = setTimeout(() => {
                    this.realtimeAlert = null;
                }, 15000);

                const notifTitle = '🔔 Đơn hàng ' + modifiedAlertObj.orderCode + ' vừa có cập nhật mới!';
                const notifBody = 'Người bốc: ' + modifiedAlertObj.pickers + ' | Phụ trách: ' + modifiedAlertObj.supervisor;
                this.showToast(notifTitle + (changeSummary ? ' (' + changeSummary + ')' : ''), 'warning');

                if (window.Notification && Notification.permission === 'granted') {
                    try {
                        new Notification('ERP Đại Sơn - Đơn ' + modifiedAlertObj.orderCode + ' cập nhật', {
                            body: notifBody + '\n' + changeSummary,
                            icon: '/favicon.ico'
                        });
                    } catch(e) {}
                }
            } else if (ev.eventType === 'ORDER_STATUS_CHANGED') {
                const orderId = Number(ev.orderId);
                const extra = ev.extraData || {};
                const newStatus = extra.newStatus || ev.orderStatus;
                this.orders = this.orders.map(o => {
                    if (Number(o.id) === orderId || o.order_code === extra.orderCode) {
                        return {
                            ...o,
                            status: newStatus || o.status
                        };
                    }
                    return o;
                });
            } else if (ev.eventType === 'ORDER_CREATED') {
                const newOrd = ev.extraData?.order;
                if (newOrd && !this.orders.some(o => Number(o.id) === Number(newOrd.id))) {
                    this.orders = [newOrd, ...this.orders];
                    this.playAlertSound();
                    this.showToast('📦 Đơn hàng mới chờ soạn: ' + newOrd.order_code + ' (' + newOrd.customer_name + ')');
                }
            } else if (ev.eventType === 'ITEM_UPDATE') {
                const orderId = Number(ev.orderId);
                this.orders = this.orders.map(targetOrder => {
                    if (Number(targetOrder.id) === orderId) {
                        const newItems = (targetOrder.items || []).map(targetItem => {
                            if ((ev.itemId && targetItem.id == ev.itemId) || (ev.sku && targetItem.sku == ev.sku)) {
                                const updated = { ...targetItem };
                                if (ev.pickedQty !== undefined && ev.pickedQty !== null) {
                                    updated.picked_qty = ev.pickedQty;
                                    const req = Number(updated.quantity || 0) + Number(updated.gift_quantity || 0);
                                    updated.is_picked = ev.pickedQty >= req && req > 0;
                                }
                                if (ev.itemStatus) updated.item_status = ev.itemStatus;
                                if (ev.assignedStaff !== undefined) updated.assigned_staff = ev.assignedStaff;
                                if (ev.assignedStaffList !== undefined) updated.assigned_staff_list = ev.assignedStaffList;
                                if (ev.shortageReason !== undefined) updated.shortage_reason = ev.shortageReason;
                                if (ev.extraData?.isChecked !== undefined) updated.is_checked = ev.extraData.isChecked;
                                if (ev.extraData?.checkedQty !== undefined) updated.checked_qty = ev.extraData.checkedQty;
                                return updated;
                            }
                            return targetItem;
                        });
                        const updatedGroups = ev.extraData?.groupProgress?.groups || targetOrder.groups_progress;
                        if (this.activeOrder && (Number(this.activeOrder.id) === orderId || this.activeOrder.order_code == ev.orderId)) {
                            if (ev.orderStatus) this.activeOrder.status = ev.orderStatus;
                            if (updatedGroups) this.activeOrder.groups_progress = updatedGroups;
                        }
                        return {
                            ...targetOrder,
                            items: newItems,
                            status: ev.orderStatus || targetOrder.status,
                            groups_progress: updatedGroups
                        };
                    }
                    return targetOrder;
                });
            } else if (ev.eventType === 'STAFF_ASSIGN') {
                const orderId = Number(ev.orderId);
                this.orders = this.orders.map(targetOrder => {
                    if (Number(targetOrder.id) === orderId) {
                        const newItems = (targetOrder.items || []).map(targetItem => {
                            if ((ev.itemId && targetItem.id == ev.itemId) || (ev.sku && targetItem.sku == ev.sku) || ev.extraData?.assignAllStaff) {
                                return {
                                    ...targetItem,
                                    assigned_staff: ev.assignedStaff,
                                    assigned_staff_list: ev.assignedStaffList || (ev.assignedStaff ? [ev.assignedStaff] : [])
                                };
                            }
                            return targetItem;
                        });
                        return {
                            ...targetOrder,
                            items: newItems
                        };
                    }
                    return targetOrder;
                });
            } else if (ev.eventType === 'SHORTAGE_REPORTED') {
                const orderId = Number(ev.orderId);
                const extra = ev.extraData || {};
                const supervisor = extra.supervisor || 'Admin Kho';
                const reporter = extra.reporter || ev.performerName || 'Nhân sự kho';

                this.orders = this.orders.map(targetOrder => {
                    if (Number(targetOrder.id) === orderId || targetOrder.order_code === extra.orderCode) {
                        return {
                            ...targetOrder,
                            status: 'Kho báo thiếu hàng',
                            shortage_reported: true
                        };
                    }
                    return targetOrder;
                });

                this.playAlertSound();
                const shortSum = (extra.shortageItems || []).map(i => i.name + ' (thiếu ' + (i.shortage_qty || i.shortageQty) + ' ' + (i.unit || '') + ')').join('; ');
                const notifMsg = '⚠️ CẢNH BÁO THIẾU HÀNG: Đơn ' + (extra.orderCode || orderId) + ' do ' + reporter + ' báo tới người phụ trách ' + supervisor + (shortSum ? ' [' + shortSum + ']' : '');
                this.showToast(notifMsg, 'warning');

                if (window.Notification && Notification.permission === 'granted') {
                    try {
                        new Notification('ERP Đại Sơn - Cảnh báo thiếu hàng ' + (extra.orderCode || orderId), {
                            body: 'Gửi người phụ trách: ' + supervisor + '\n' + shortSum,
                            icon: '/favicon.ico'
                        });
                    } catch(e) {}
                }
            }
        },

        dismissModifiedAlert(ord) {
            if (ord) {
                ord._hasItemsModified = false;
                ord._modifiedAlert = null;
                (ord.items || []).forEach(it => {
                    it._justModified = false;
                });
            }
            if (this.realtimeAlert && ord && (this.realtimeAlert.orderCode === ord.order_code || Number(this.realtimeAlert.orderId) === Number(ord.id))) {
                this.realtimeAlert = null;
            }
        },

        playAlertSound() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }

                // Tiếng chuông cảnh báo ngân vang 2 tầng
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'triangle';
                osc1.frequency.setValueAtTime(659.25, ctx.currentTime); // E5
                gain1.gain.setValueAtTime(0.3, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(ctx.currentTime);
                osc1.stop(ctx.currentTime + 0.4);

                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(987.77, ctx.currentTime + 0.15); // B5
                gain2.gain.setValueAtTime(0.35, ctx.currentTime + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.7);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(ctx.currentTime + 0.15);
                osc2.stop(ctx.currentTime + 0.7);
            } catch(e) {
                console.warn('Audio play error:', e);
            }
        }
    };
}
</script>
@endsection
