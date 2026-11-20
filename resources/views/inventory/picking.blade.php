@extends('layouts.app')

@section('title', 'Soạn Đơn & Kiểm Hàng Xuất Kho | ERP Đại Sơn')

@section('content')
<div x-data="pickingApp()" x-init="initApp()" class="space-y-2.5 sm:space-y-4 max-w-[1920px] mx-auto pb-1 sm:pb-6">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-16 sm:bottom-6 right-4 sm:right-6 z-[9999] px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-bold border max-w-lg"
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
                            <span class="px-2 py-0.5 rounded-full bg-black/25 font-black text-[10px] uppercase tracking-wider text-amber-200 border border-amber-300/30"
                                  x-text="realtimeAlert && realtimeAlert.isDiscrepancy ? 'LỆCH HÀNG TỪ XE' : 'Cập nhật đơn hàng'">
                            </span>
                            <span class="text-sm font-bold text-white">
                                <span x-show="realtimeAlert && realtimeAlert.isDiscrepancy">
                                    Xe <strong class="font-mono font-black text-amber-200" x-text="realtimeAlert ? realtimeAlert.vehiclePlate : ''"></strong> báo lệch đơn <strong class="font-mono font-black text-amber-200 underline" x-text="realtimeAlert ? realtimeAlert.orderCode : ''"></strong>!
                                </span>
                                <span x-show="!realtimeAlert || !realtimeAlert.isDiscrepancy">
                                    Đơn <strong class="font-mono font-black text-amber-200 underline" x-text="realtimeAlert ? realtimeAlert.orderCode : ''"></strong> vừa có cập nhật mới!
                                </span>
                            </span>
                        </div>
                        <div class="text-xs text-white/90 flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1">
                            <span x-show="realtimeAlert && realtimeAlert.time">Lúc: <strong class="font-mono text-amber-100" x-text="realtimeAlert ? realtimeAlert.time : ''"></strong></span>
                            <span x-show="realtimeAlert && realtimeAlert.performer">Bởi: <strong class="text-amber-100" x-text="realtimeAlert ? realtimeAlert.performer : ''"></strong></span>
                            <span x-show="realtimeAlert && realtimeAlert.summary" class="w-full text-amber-100 font-bold mt-0.5" x-text="realtimeAlert ? realtimeAlert.summary : ''"></span>
                        </div>
                        <div class="text-[11px] text-amber-100 mt-0.5" x-show="realtimeAlert && realtimeAlert.supervisor">
                            <strong class="text-white underline font-bold" x-text="realtimeAlert ? realtimeAlert.supervisor : ''"></strong>
                        </div>
                    </div>
                </div>
                <button type="button" @click="realtimeAlert = null" class="text-white/80 hover:text-white text-xl font-bold p-1 cursor-pointer leading-none">&times;</button>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1 border-t border-white/20">
                <template x-if="realtimeAlert && realtimeAlert.isDiscrepancy">
                    <button type="button" @click.stop="openDiscrepanciesModal(); realtimeAlert = null"
                            class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-rose-700 font-black text-xs shadow transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                        <span>Xem & phản hồi cho lái xe</span>
                    </button>
                </template>
                <template x-if="!realtimeAlert || !realtimeAlert.isDiscrepancy">
                    <button type="button" @click="goToAlertOrder(realtimeAlert)"
                            class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-rose-700 font-black text-xs shadow transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                        <span>Xem ngay đơn này</span>
                    </button>
                </template>
                <button type="button" @click="realtimeAlert = null"
                        class="px-3 py-1.5 rounded-xl bg-black/30 hover:bg-black/50 text-white font-bold text-xs transition-all cursor-pointer">
                    <span>Đã nhận TT &times;</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MOBILE DEDICATED HEADER & NAVIGATION (Dành riêng Mobile) -->
    <!-- ======================================================== -->
    <div class="block lg:hidden space-y-2.5" x-show="mobileView === 'list'">
        <!-- Mobile Top Title & Badges Bar -->
        <div class="bg-white rounded-2xl p-3 border border-slate-200/90 shadow-2xs flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <i class="fa-solid fa-clipboard-check text-xs"></i>
                </div>
                <div>
                    <h1 class="text-xs font-black text-slate-900 tracking-tight leading-tight">Soạn & Kiểm Đơn</h1>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 mt-0.5">
                        <span class="inline-flex items-center gap-0.5 text-amber-700 font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span x-text="countPendingPick + ' bốc'"></span>
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-0.5 text-emerald-700 font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span x-text="countReadyQc + ' kiểm'"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Nút bật/tắt bộ lọc nâng cao -->
            <button type="button" @click="showMobileAdvancedFilters = !showMobileAdvancedFilters"
                    class="px-2.5 py-1.5 rounded-xl border text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shrink-0"
                    :class="showMobileAdvancedFilters || selectedBranch || (filterPickingDate && filterPickingDate !== '{{ date('Y-m-d') }}') ? 'bg-amber-50 text-amber-900 border-amber-300' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'">
                <i class="fa-solid fa-sliders text-xs" :class="showMobileAdvancedFilters ? 'text-amber-600' : 'text-slate-400'"></i>
                <span>Lọc</span>
                <i class="fa-solid text-[9px]" :class="showMobileAdvancedFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>

        <!-- Mobile Segmented Main Tabs (3 Tabs) -->
        <div class="grid grid-cols-3 gap-1.5 bg-slate-200/80 p-1 rounded-2xl text-xs font-black">
            <!-- Tab 1: Soạn & Bốc -->
            <button type="button" @click="switchTab('picking')"
                    class="py-2 px-1 rounded-xl flex flex-col items-center justify-center gap-0.5 transition-all cursor-pointer relative"
                    :class="activeTab === 'picking' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-700 hover:bg-white/60'">
                <div class="flex items-center gap-1">
                    <i class="fa-solid fa-box-open text-xs"></i>
                    <span class="text-[11px]">Soạn hàng</span>
                </div>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black"
                      :class="activeTab === 'picking' ? 'bg-black/20 text-white' : 'bg-amber-100 text-amber-800'"
                      x-text="countPendingPick"></span>
            </button>

            <!-- Tab 2: Kiểm QC -->
            <button type="button" @click="switchTab('qc')"
                    class="py-2 px-1 rounded-xl flex flex-col items-center justify-center gap-0.5 transition-all cursor-pointer relative"
                    :class="activeTab === 'qc' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-700 hover:bg-white/60'">
                <div class="flex items-center gap-1">
                    <i class="fa-solid fa-circle-check text-xs"></i>
                    <span class="text-[11px]">Kiểm QC</span>
                </div>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black"
                      :class="activeTab === 'qc' ? 'bg-black/20 text-white' : 'bg-emerald-100 text-emerald-800'"
                      x-text="countReadyQc"></span>
            </button>

            <!-- Tab 3: Lệch Xe -->
            <button type="button" @click.stop="openDiscrepanciesModal()"
                    class="py-2 px-1 rounded-xl flex flex-col items-center justify-center gap-0.5 transition-all cursor-pointer relative border"
                    :class="pendingDiscrepanciesCount > 0 ? 'bg-rose-500 text-white border-rose-600 shadow-xs animate-pulse' : 'text-slate-700 hover:bg-white/60 border-transparent'">
                <div class="flex items-center gap-1">
                    <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                    <span class="text-[11px]">Lệch xe</span>
                </div>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black"
                      :class="pendingDiscrepanciesCount > 0 ? 'bg-white text-rose-700' : 'bg-slate-100 text-slate-600'"
                      x-text="pendingDiscrepanciesCount > 0 ? pendingDiscrepanciesCount : discrepanciesList.length"></span>
            </button>
        </div>

        <!-- Mobile Quick Sub-Toolbar: Mode Switch + Quick Date Toggle -->
        <div class="bg-white rounded-2xl p-2 border border-slate-200/90 shadow-2xs flex items-center justify-between gap-2">
            <!-- Mode switch: Single vs Batch -->
            <div class="inline-flex rounded-xl p-0.5 bg-slate-100 border border-slate-200 text-xs">
                <button type="button" @click="fulfillmentMode = 'single'"
                        class="px-2.5 py-1 rounded-lg font-bold transition-all flex items-center gap-1 cursor-pointer text-[11px]"
                        :class="fulfillmentMode === 'single' ? 'bg-white text-sky-700 shadow-2xs' : 'text-slate-600'">
                    <i class="fa-solid fa-list-check text-[10px]"></i>
                    <span>Từng đơn</span>
                </button>
                <button type="button" @click="fulfillmentMode = 'batch'"
                        class="px-2.5 py-1 rounded-lg font-bold transition-all flex items-center gap-1 cursor-pointer text-[11px]"
                        :class="fulfillmentMode === 'batch' ? 'bg-white text-sky-700 shadow-2xs' : 'text-slate-600'">
                    <i class="fa-solid fa-layer-group text-[10px]"></i>
                    <span>Gom nhiều đơn</span>
                </button>
            </div>

            <!-- Quick Date toggle: Hôm nay vs Tất cả -->
            <div class="flex items-center gap-1 text-[11px] font-bold">
                <button type="button" @click="filterPickingDate = '{{ date('Y-m-d') }}'"
                        class="px-2.5 py-1 rounded-lg border transition-all cursor-pointer"
                        :class="filterPickingDate === '{{ date('Y-m-d') }}' ? 'bg-amber-500 text-white border-amber-600 shadow-2xs' : 'bg-slate-100 text-slate-700 border-slate-200'">
                    Hôm nay
                </button>
                <button type="button" @click="filterPickingDate = ''"
                        class="px-2.5 py-1 rounded-lg border transition-all cursor-pointer"
                        :class="!filterPickingDate ? 'bg-sky-600 text-white border-sky-700 shadow-2xs' : 'bg-slate-100 text-slate-700 border-slate-200'">
                    Tất cả
                </button>
            </div>
        </div>

        <!-- Mobile Expandable Advanced Filter & Permission Panel -->
        <div x-show="showMobileAdvancedFilters" x-collapse
             class="bg-white rounded-2xl p-3 border border-slate-200/90 shadow-2xs space-y-2.5 text-xs">
            <!-- Chi nhánh & Ngày cụ thể -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Chi nhánh</label>
                    <select x-model="selectedBranch" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-1.5 text-xs font-bold text-slate-800 focus:outline-none">
                        <option value="">Toàn bộ chi nhánh</option>
                        @foreach($branches as $br)
                            <option value="{{ $br->name }}">{{ $br->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Ngày soạn cụ thể</label>
                    <input type="date" x-model="filterPickingDate"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-1.5 text-xs font-bold font-mono text-slate-800 focus:outline-none">
                </div>
            </div>

            <!-- Quyền ngành hàng & nhóm phụ trách -->
            <div class="pt-2 border-t border-slate-100">
                <template x-if="canViewAllCategories">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-900 font-bold text-[11px]">
                        <i class="fa-solid fa-layer-group text-indigo-600"></i>
                        <span>Quyền xem: <strong>Toàn bộ ngành hàng</strong></span>
                    </div>
                </template>
                <template x-if="!canViewAllCategories">
                    <div class="space-y-1 text-[11px]">
                        <div class="flex items-center gap-1 text-slate-700">
                            <span class="text-slate-400">Nhóm:</span>
                            <strong class="text-amber-900" x-text="userGroupsSummary"></strong>
                        </div>
                        <div class="flex items-center gap-1 text-slate-700">
                            <span class="text-slate-400">Phụ trách:</span>
                            <strong class="text-sky-900" x-text="userCategoriesSummary"></strong>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 1. Page Header (Desktop) -->
    <div class="hidden lg:flex bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/90 shadow-2xs flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <i class="fa-solid fa-clipboard-check text-sm"></i>
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Soạn Đơn & Kiểm Hàng</span>
                    </h1>
                </div>
            </div>

            <!-- Mobile compact badges -->
            <div class="flex items-center gap-1 sm:hidden">
                <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-900 text-[10px] font-bold"
                      x-text="countPendingPick + ' bốc'"></span>
                <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-900 text-[10px] font-bold"
                      x-text="countReadyQc + ' kiểm'"></span>
            </div>
        </div>

        <!-- Filter & Badges: Thiết kế 1 hàng gọn nhẹ -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <!-- Branch Filter -->
            <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1.5 rounded-xl border border-slate-200 text-slate-700">
                <i class="fa-solid fa-building text-slate-400 text-xs"></i>
                <select x-model="selectedBranch" class="bg-transparent font-bold text-slate-800 focus:outline-none cursor-pointer text-xs">
                    <option value="">Toàn bộ chi nhánh</option>
                    @foreach($branches as $br)
                        <option value="{{ $br->name }}">{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Filter: Bộ lọc ngày soạn hàng -->
            <div class="flex items-center gap-1 bg-slate-50 px-2.5 py-1.5 rounded-xl border border-slate-200 text-slate-700 font-medium">
                <i class="fa-solid fa-calendar-day text-amber-600 text-xs"></i>
                <input type="date" x-model="filterPickingDate"
                       class="bg-transparent font-bold font-mono text-slate-800 focus:outline-none cursor-pointer text-xs">
                <button type="button" x-show="filterPickingDate && filterPickingDate !== '{{ date('Y-m-d') }}'" @click="filterPickingDate = '{{ date('Y-m-d') }}'"
                        class="text-[10px] font-bold text-slate-600 bg-slate-200 hover:bg-slate-300 px-1.5 py-0.5 rounded cursor-pointer transition-colors" title="Chuyển về hôm nay">
                    Hôm nay
                </button>
                <button type="button" x-show="filterPickingDate" @click="filterPickingDate = ''"
                        class="text-slate-400 hover:text-rose-600 text-xs cursor-pointer ml-0.5" title="Xem tất cả ngày">
                    <i class="fa-solid fa-circle-xmark"></i>
                </button>
            </div>

            <!-- Desktop Badges -->
            <div class="hidden sm:flex items-center gap-1.5">
                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
                    <i class="fa-solid fa-clock text-amber-600 text-[11px]"></i>
                    <span x-text="countPendingPick + ' chờ bốc'"></span>
                </div>

                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold">
                    <i class="fa-solid fa-shield-halved text-emerald-600 text-[11px]"></i>
                    <span x-text="countReadyQc + ' chờ kiểm'"></span>
                </div>

                <button type="button" @click="setOrderTypeFilter(filterOrderType === 'consignment' ? 'all' : 'consignment')" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold transition-all cursor-pointer border"
                        :class="filterOrderType === 'consignment' ? 'bg-amber-600 text-white border-amber-700 shadow-2xs' : 'bg-white hover:bg-amber-50 border-amber-300 text-amber-900'"
                        title="Xem đơn hàng ôm">
                    <i class="fa-solid fa-boxes-stacked text-[11px]" :class="filterOrderType === 'consignment' ? 'text-white' : 'text-amber-600'"></i>
                    <span x-text="countConsignmentOrders + ' hàng ôm'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Category & Staff Group Authorization Banner (Desktop) -->
    <div class="hidden lg:flex bg-white rounded-2xl p-3 sm:px-4 border border-slate-200/90 shadow-2xs flex-col md:flex-row md:items-center md:justify-between gap-2.5 text-xs">
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Mode 1: Có quyền xem bốc xếp không phân loại sản phẩm (Toàn quyền mọi loại sản phẩm) -->
            <template x-if="canViewAllCategories">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-900 font-bold text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                    <i class="fa-solid fa-layer-group text-indigo-600 text-xs"></i>
                    <span>Quyền xem: <strong class="text-indigo-950 font-black">Toàn bộ ngành hàng</strong></span>
                    <span class="px-1.5 py-0.5 rounded bg-indigo-200/70 text-indigo-900 font-extrabold text-[9px] uppercase">Toàn quyền</span>
                </div>
            </template>

            <!-- Mode 2: Phân loại theo Nhóm nhân sự phụ trách -->
            <template x-if="!canViewAllCategories">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-950 font-bold text-xs">
                        <i class="fa-solid fa-users-gear text-amber-600 text-xs"></i>
                        <span>Nhóm: <strong class="text-amber-900 font-black" x-text="userGroupsSummary"></strong></span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 border border-sky-200 text-sky-950 font-bold text-xs">
                        <i class="fa-solid fa-boxes-stacked text-sky-600 text-xs"></i>
                        <span>Phụ trách: <strong class="text-sky-900 font-black" x-text="userCategoriesSummary"></strong></span>
                    </div>

                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold"
                          :class="(Array.isArray(leaderCategoryIds) ? leaderCategoryIds.length : Object.keys(leaderCategoryIds || {}).length) > 0 ? 'bg-purple-50 text-purple-900 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200'">
                        <i :class="(Array.isArray(leaderCategoryIds) ? leaderCategoryIds.length : Object.keys(leaderCategoryIds || {}).length) > 0 ? 'fa-solid fa-crown text-purple-600' : 'fa-solid fa-user text-slate-500'"></i>
                        <span x-text="(Array.isArray(leaderCategoryIds) ? leaderCategoryIds.length : Object.keys(leaderCategoryIds || {}).length) > 0 ? 'Trưởng nhóm' : 'Thành viên'"></span>
                    </div>
                </div>
            </template>
        </div>

        <div class="text-slate-500 text-[11px] flex items-center gap-2 font-medium" x-show="userCategoryIds.length === 0 && !canViewAllCategories">
            <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold border border-rose-200 flex items-center gap-1">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Chưa được gán nhóm phụ trách. Vui lòng liên hệ Quản lý kho.</span>
            </span>
        </div>
    </div>

    <!-- 2. Main Process Tabs (Desktop) -->
    <div class="hidden lg:flex items-center gap-2 sm:gap-3">
        <!-- Tab 1: Soạn & Bốc Hàng -->
        <button type="button" @click="switchTab('picking')"
                class="flex-1 sm:flex-initial px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                :class="activeTab === 'picking' ? 'bg-amber-500 text-white shadow-amber-500/25 ring-2 ring-amber-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'">
            <i class="fa-solid fa-box-open"></i>
            <span>1. Soạn & Bốc</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-black"
                  :class="activeTab === 'picking' ? 'bg-black/20 text-white' : 'bg-amber-100 text-amber-800'"
                  x-text="countPendingPick"></span>
        </button>

        <!-- Tab 2: Kiểm Hàng (QC Soạn Đơn) -->
        <button type="button" @click="switchTab('qc')"
                class="flex-1 sm:flex-initial px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                :class="activeTab === 'qc' ? 'bg-emerald-600 text-white shadow-emerald-600/25 ring-2 ring-emerald-600/20' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'">
            <i class="fa-solid fa-circle-check"></i>
            <span>2. Kiểm Hàng (QC)</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-black"
                  :class="activeTab === 'qc' ? 'bg-black/20 text-white' : 'bg-emerald-100 text-emerald-800'"
                  x-text="countReadyQc"></span>
        </button>

        <!-- Tab 3: Phản Ánh Lệch Hàng Từ Xe Xuất Bến (Người bốc ⇄ Lái Xe) -->
        <button type="button" @click.stop="openDiscrepanciesModal()"
                class="flex-1 sm:flex-initial px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs border relative"
                :class="pendingDiscrepanciesCount > 0 
                    ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-300 ring-2 ring-rose-500/20' 
                    : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'">
            <i class="fa-solid fa-truck-ramp-box" :class="pendingDiscrepanciesCount > 0 ? 'text-rose-600 animate-bounce' : 'text-slate-500'"></i>
            <span>3. Lệch Hàng Từ Xe</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-black transition-all"
                  :class="pendingDiscrepanciesCount > 0 ? 'bg-rose-600 text-white animate-pulse' : 'bg-slate-100 text-slate-600'"
                  x-text="pendingDiscrepanciesCount > 0 ? (pendingDiscrepanciesCount + ' tin báo người bốc') : (discrepanciesList.length + ' tin')"></span>
        </button>
    </div>

    <!-- 3. Sub-toolbar: Chế độ soạn / kiểm kho & Mã đơn đang xem (Desktop) -->
    <div class="hidden lg:flex bg-white rounded-2xl px-4 py-2.5 sm:px-5 sm:py-3 border border-slate-200/90 shadow-2xs flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 text-xs">
        <div class="flex items-center gap-3">
            <span class="font-bold text-slate-600 hidden sm:inline" x-text="activeTab === 'picking' ? 'Chế độ soạn:' : 'Chế độ kiểm:'"></span>
            
            <div class="inline-flex rounded-xl p-0.5 bg-slate-100 border border-slate-200 w-full sm:w-auto">
                <button type="button" @click="fulfillmentMode = 'single'"
                        class="flex-1 sm:flex-initial px-3 py-1.5 rounded-lg font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer text-xs"
                        :class="fulfillmentMode === 'single' ? 'bg-white text-sky-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                    <i class="fa-solid fa-list-check text-xs"></i>
                    <span>Từng đơn lẻ</span>
                </button>
                <button type="button" @click="fulfillmentMode = 'batch'"
                        class="flex-1 sm:flex-initial px-3 py-1.5 rounded-lg font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer text-xs"
                        :class="fulfillmentMode === 'batch' ? 'bg-white text-sky-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                    <span x-text="activeTab === 'picking' ? 'Gộp nhiều đơn' : 'Kiểm tra gộp'"></span>
                </button>
            </div>
        </div>

        <!-- Right Label: Viewing Order Code (single) OR Batch selection count (batch) -->
        <div x-show="fulfillmentMode === 'single' && activeOrder" class="text-slate-500 font-medium hidden sm:block">
            <span x-text="activeTab === 'picking' ? 'Đang xem đơn: ' : 'Đang kiểm đơn: '"></span>
            <span class="font-mono font-black text-slate-800 text-sm bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200" x-text="activeOrder ? activeOrder.order_code : ''"></span>
        </div>
        <div x-show="fulfillmentMode === 'batch'" class="text-slate-600 font-medium">
            <span class="px-2.5 py-1 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 font-bold text-xs shadow-2xs"
                  x-text="'Đang chọn gộp: ' + selectedBatchOrderIds.length + ' / ' + filteredOrders.length + ' đơn'"></span>
        </div>
    </div>

    <!-- 4. Main Two-Column Layout (Order List Sidebar + Workspace) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ========================================== -->
        <!-- LEFT COLUMN: Order List Sidebar (3.5 cols) -->
        <!-- ========================================== -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-3" :class="mobileView === 'detail' ? 'hidden lg:block' : 'block'">
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
                                  x-text="countFilteredPending > 0 ? (countFilteredPending + ' chờ · ' + countFilteredCompleted + ' xong') : (filteredOrders.length + '/' + totalOrdersCount + ' đơn')"></span>
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

                <!-- Alert Banner: Nhắc nhở đơn chờ xử lý ở các ngày khác tránh sót đơn -->
                <template x-if="activeTab === 'qc' && filterPickingDate && totalReadyQcAllDates > countReadyQc">
                    <div class="px-2.5 py-2 rounded-xl bg-amber-50/90 border border-amber-300 text-amber-950 text-xs flex items-center justify-between gap-1 shadow-2xs">
                        <span class="flex items-center gap-1.5 font-bold truncate">
                            <i class="fa-solid fa-bell text-amber-600 shrink-0"></i>
                            <span>Còn <strong class="text-rose-600 font-mono text-xs" x-text="totalReadyQcAllDates - countReadyQc"></strong> đơn chờ kiểm ngày khác</span>
                        </span>
                        <button type="button" @click="filterPickingDate = ''" 
                                class="px-2 py-0.5 rounded-lg bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-black text-[10px] shrink-0 cursor-pointer shadow-xs transition-all">
                            Xem tất cả
                        </button>
                    </div>
                </template>
                <template x-if="activeTab === 'picking' && filterPickingDate && totalPendingPickAllDates > countPendingPick">
                    <div class="px-2.5 py-2 rounded-xl bg-amber-50/90 border border-amber-300 text-amber-950 text-xs flex items-center justify-between gap-1 shadow-2xs">
                        <span class="flex items-center gap-1.5 font-bold truncate">
                            <i class="fa-solid fa-bell text-amber-600 shrink-0"></i>
                            <span>Còn <strong class="text-rose-600 font-mono text-xs" x-text="totalPendingPickAllDates - countPendingPick"></strong> đơn chờ soạn ngày khác</span>
                        </span>
                        <button type="button" @click="filterPickingDate = ''" 
                                class="px-2 py-0.5 rounded-lg bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-black text-[10px] shrink-0 cursor-pointer shadow-xs transition-all">
                            Xem tất cả
                        </button>
                    </div>
                </template>

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

                <!-- Nút chuyển sang xem sản phẩm gộp trên mobile -->
                <div x-show="fulfillmentMode === 'batch'" class="lg:hidden pt-1 pb-1">
                    <button type="button" @click="mobileView = 'detail'; $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))"
                            class="w-full py-2.5 px-3 rounded-xl bg-sky-600 hover:bg-sky-500 active:bg-sky-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-sky-600/30 transition-all cursor-pointer">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span x-text="'Xem chi tiết sản phẩm gom (' + selectedBatchOrderIds.length + ' đơn) →'"></span>
                    </button>
                </div>

                <!-- Order Cards Scrollable List (Đơn chưa xong lên trên, hoàn thành đẩy xuống dưới) -->
                <div class="space-y-2.5 lg:max-h-[calc(100vh-330px)] lg:overflow-y-auto pr-1">
                    <template x-for="(ord, idx) in filteredOrders" :key="ord.id">
                        <div class="space-y-2">
                            <!-- Đường phân cách trực quan cho nhóm đơn đã hoàn tất được đẩy xuống dưới -->
                            <template x-if="idx > 0 && (activeTab === 'qc' ? (isOrderDaKiem(ord) && !isOrderDaKiem(filteredOrders[idx-1])) : (isOrderDaBoc(ord) && !isOrderDaBoc(filteredOrders[idx-1])))">
                                <div class="pt-2 pb-1 flex items-center gap-2">
                                    <div class="h-px bg-slate-200 flex-1"></div>
                                    <span class="px-2.5 py-0.5 rounded-full bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-500 flex items-center gap-1.5 shrink-0 shadow-2xs">
                                        <i class="fa-solid fa-circle-check text-emerald-600 text-[10px]"></i>
                                        <span x-text="activeTab === 'qc' ? 'Đã kiểm xong (' + countFilteredCompleted + ' đơn)' : 'Đã bốc xong (' + countFilteredCompleted + ' đơn)'"></span>
                                    </span>
                                    <div class="h-px bg-slate-200 flex-1"></div>
                                </div>
                            </template>

                            <div @click="fulfillmentMode === 'batch' ? toggleBatchOrder(ord.id) : selectOrder(ord)"
                                 class="p-3.5 rounded-2xl border transition-all cursor-pointer relative space-y-2"
                                 :class="fulfillmentMode === 'batch'
                                    ? (selectedBatchOrderIds.includes(ord.id) ? 'border-sky-400 bg-sky-50/60 ring-2 ring-sky-500/20 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/80 shadow-2xs')
                                    : (activeOrderId === ord.id 
                                        ? (activeTab === 'picking' ? 'border-sky-400 bg-sky-50/60 ring-2 ring-sky-500/20 shadow-xs' : 'border-emerald-400 bg-emerald-50/50 ring-2 ring-emerald-500/20 shadow-xs')
                                        : ((activeTab === 'qc' ? isOrderDaKiem(ord) : isOrderDaBoc(ord))
                                            ? 'border-slate-200/80 bg-slate-50/60 opacity-80 hover:opacity-100 hover:bg-white shadow-2xs' 
                                            : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/80 shadow-2xs'))">

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
                                        <span class="truncate max-w-[150px]" x-text="typeof ord.warehouse === 'string' ? ord.warehouse : (ord.warehouse?.name || 'Kho Tổng')"></span>
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
                                                      :class="(activeTab === 'picking' ? grp.is_picking_completed : grp.is_qc_completed) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200'">
                                                    <i class="fa-solid" :class="(activeTab === 'picking' ? grp.is_picking_completed : grp.is_qc_completed) ? 'fa-check text-emerald-600' : 'fa-clock text-amber-500'"></i>
                                                    <span x-text="grp.group_name"></span>:
                                                    <span class="font-mono font-black" x-text="(activeTab === 'picking' ? grp.picked_count : grp.checked_count) + '/' + grp.items_count"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Mobile Click Action Indicator -->
                                    <div x-show="fulfillmentMode === 'single'" class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] lg:hidden">
                                        <span class="text-slate-400 font-medium">Chạm để soạn sản phẩm</span>
                                        <span class="font-bold text-sky-600 flex items-center gap-1">
                                            <span>Xem sản phẩm</span>
                                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                        </span>
                                    </div>
                                </div>
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
        <div class="lg:col-span-8 xl:col-span-9" :class="mobileView === 'list' ? 'hidden lg:block' : 'block pb-28 lg:pb-0'">

            <!-- Mobile Sticky Header Navigation (Quay lại danh sách đơn trên điện thoại) -->
            <div class="lg:hidden mb-3.5 sticky top-2 z-30">
                <button type="button" @click="mobileView = 'list'; $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))"
                        class="w-full py-2.5 px-3.5 rounded-2xl bg-slate-900/95 hover:bg-slate-900 active:bg-slate-950 backdrop-blur-md text-white font-bold text-xs flex items-center justify-between shadow-lg shadow-slate-900/25 active:scale-[0.99] transition-all cursor-pointer border border-slate-700/60">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left text-sky-400 text-sm"></i>
                        <span class="font-extrabold tracking-tight">Danh sách đơn hàng</span>
                    </span>
                    <div class="flex items-center gap-2">
                        <template x-if="fulfillmentMode === 'single' && activeOrder">
                            <span class="px-2 py-0.5 rounded-lg bg-sky-500/20 text-sky-300 border border-sky-400/30 font-mono text-[11px] font-black"
                                  x-text="activeOrder.order_code"></span>
                        </template>
                        <template x-if="fulfillmentMode === 'batch'">
                            <span class="px-2 py-0.5 rounded-lg bg-sky-500/20 text-sky-300 border border-sky-400/30 font-mono text-[11px] font-black"
                                  x-text="selectedBatchOrderIds.length + ' đơn gộp'"></span>
                        </template>
                        <span class="text-slate-400 text-[11px] flex items-center gap-1">
                            <span>Đổi đơn</span>
                            <i class="fa-solid fa-chevron-right text-[9px]"></i>
                        </span>
                    </div>
                </button>
            </div>

            <!-- Active Order Single Mode -->
            <template x-if="fulfillmentMode === 'single' && activeOrder">
                <div>

                    <!-- =============================================== -->
                    <!-- TAB 1 CONTENT: 1. SOẠN & BỐC HÀNG WORKSPACE    -->
                    <!-- =============================================== -->
                    <div x-show="activeTab === 'picking'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mt-0">
                        
                        <!-- Panel Header (Tối giản, gọn gàng) -->
                        <div class="px-3.5 py-2.5 sm:px-5 sm:py-3 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 bg-white">
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight flex items-center gap-1.5 flex-wrap">
                                    <span class="text-sky-700 font-mono" x-text="activeOrder.order_code"></span>
                                    <template x-if="activeOrder.is_consignment">
                                        <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-[10px] inline-flex items-center gap-1">
                                            <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                                            <span>Hàng Ôm</span>
                                        </span>
                                    </template>
                                    <span class="text-slate-300">|</span>
                                    <span x-text="activeOrder.customer_name" class="font-bold truncate"></span>
                                </h3>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-500 mt-0.5">
                                    <span>Kho: <strong class="text-slate-700" x-text="activeOrder.warehouse || 'Kho Tổng'"></strong></span>
                                    <span>·</span>
                                    <span>Lên đơn: <strong class="text-slate-700 font-mono" x-text="formatDate(activeOrder.created_at)"></strong></span>
                                    <template x-if="activeOrder.delivery_date">
                                        <span>· Giao: <strong class="text-emerald-700 font-mono font-bold" x-text="formatDate(activeOrder.delivery_date)"></strong></span>
                                    </template>
                                    <template x-if="activeOrder.picking_date">
                                        <span>· Soạn: <strong class="text-amber-800 font-mono font-bold" x-text="formatDate(activeOrder.picking_date)"></strong></span>
                                    </template>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                                <template x-if="hasUnsavedPicking(activeOrder)">
                                    <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black animate-pulse">
                                        Chưa lưu SL
                                    </span>
                                </template>

                                <!-- Điền đủ nhanh (chỉ điền tạm trên màn hình) -->
                                <button type="button" @click="quickPickAllActiveOrder()"
                                        :disabled="isOrderLockedForPicking(activeOrder) || isSavingBatchOrSingle"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-300 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-2xs disabled:opacity-40"
                                        title="Điền đủ số lượng cần bốc cho tất cả mặt hàng">
                                    <i class="fa-solid fa-bolt-lightning text-sky-600 text-[11px]"></i>
                                    <span x-text="'Bốc đủ (' + activeOrder.items.length + ')'"></span>
                                </button>

                                <!-- Nút Lưu (chỉ lưu số lượng đã bốc, chưa chuyển kiểm) -->
                                <button type="button" @click="savePickingActiveOrder()"
                                        :disabled="isOrderLockedForPicking(activeOrder) || isSavingBatchOrSingle"
                                        class="px-3 py-1.5 rounded-lg text-xs font-black text-white bg-sky-600 hover:bg-sky-700 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-2xs disabled:opacity-40"
                                        title="Lưu số lượng đã bốc (bên kiểm chưa thể giao xe)">
                                    <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                                    <span>Lưu</span>
                                </button>

                                <!-- Nút Chuyển kiểm (chuyển sang kiểm hàng, kể cả khi thiếu hàng vẫn chuyển được) -->
                                <button type="button" @click="transferPickingToQcActiveOrder()"
                                        :disabled="isOrderLockedForPicking(activeOrder) || isSavingBatchOrSingle"
                                        class="px-3 py-1.5 rounded-lg text-xs font-black text-white bg-amber-500 hover:bg-amber-600 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-xs disabled:opacity-40"
                                        title="Chuyển đơn sang bộ phận Kiểm hàng (cho phép giao xe)">
                                    <i class="fa-solid fa-right-to-bracket text-[11px]"></i>
                                    <span>Chuyển kiểm</span>
                                </button>
                            </div>
                        </div>

                        <!-- CẢNH BÁO KHÓA BỐC (nếu đã kiểm xong) -->
                        <template x-if="isOrderLockedForPicking(activeOrder)">
                            <div class="px-3 py-1.5 bg-amber-100/90 border-b border-amber-300 text-amber-950 font-bold text-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-lock text-amber-700 text-xs"></i>
                                <span>Đơn hàng đã kiểm xong — Đã khóa người bốc</span>
                            </div>
                        </template>

                        <!-- Toolbar (Danh sách SP, Giao tất cả, Lọc người soạn, Tài khoản) -->
                        <div class="px-3 py-2 sm:px-4 sm:py-2.5 bg-slate-50/90 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-xs">
                            <!-- Left: Label & Total Count -->
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold text-slate-800 text-xs sm:text-sm flex items-center gap-1.5">
                                    <i class="fa-solid fa-boxes-stacked text-sky-600"></i>
                                    <span>Danh sách mặt hàng</span>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-sky-100 text-sky-800 border border-sky-200" x-text="filteredActiveItems.length + ' món'"></span>
                                </span>
                            </div>

                            <!-- Right Controls: Quick Assign & Picker Filter & User Badge / Admin Override -->
                            <div class="flex items-center gap-1.5 flex-wrap shrink-0 justify-between md:justify-end">
                                <!-- Giao nhanh toàn bộ các mặt hàng chưa bốc -->
                                <template x-if="(canViewAllCategories || leaderCategoryIds.length > 0 || isUserAdmin) && !isOrderLockedForPicking(activeOrder)">
                                    <div class="inline-flex items-center gap-1 bg-white px-2 py-1 rounded-lg border border-amber-300 shadow-2xs"
                                         title="Giao nhanh tất cả mặt hàng cho nhân viên">
                                        <i class="fa-solid fa-user-plus text-amber-600 text-[10px]"></i>
                                        <select @change="assignAllItemsToStaff($event.target.value); $event.target.value = ''"
                                                class="bg-transparent text-[11px] font-bold text-slate-800 focus:outline-none cursor-pointer max-w-[130px] truncate">
                                            <option value="">Giao tất cả...</option>
                                            <template x-for="st in groupStaff" :key="st.id">
                                                <option :value="st.name" x-text="st.name + (st.department ? ' (' + st.department + ')' : '')"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>

                                <!-- Lọc người soạn -->
                                <div class="inline-flex items-center gap-1 bg-white px-2 py-1 rounded-lg border border-slate-300 shadow-2xs"
                                     title="Lọc danh sách theo người bốc">
                                    <i class="fa-solid fa-filter text-slate-400 text-[10px]"></i>
                                    <select x-model="pickerFilter" class="bg-transparent text-[11px] font-semibold text-slate-800 focus:outline-none cursor-pointer max-w-[125px] truncate">
                                        <option value="all" x-text="'Tất cả (' + activeOrder.items.length + ')'"></option>
                                        <option value="unassigned">Chưa giao</option>
                                        <template x-for="st in groupStaff" :key="st.id">
                                            <option :value="st.name" x-text="st.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Admin Override Toggle -->
                                <template x-if="isUserAdmin">
                                    <label class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border text-[11px] font-bold cursor-pointer select-none transition-all shadow-2xs"
                                           :class="adminOverrideMode ? 'bg-amber-500 text-white border-amber-600' : 'bg-white text-slate-600 border-slate-300 hover:border-amber-400'"
                                           title="Bật để Admin có thể trực tiếp sửa số lượng bốc của mọi dòng mà không cần phân công cho chính mình">
                                        <input type="checkbox" x-model="adminOverrideMode" class="w-3 h-3 rounded text-amber-600 cursor-pointer">
                                        <span>Admin sửa</span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Danh sách mặt hàng bốc (Dạng phẳng) -->
                        <div class="p-2.5 sm:p-4">
                            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
                                <!-- Table (Desktop) -->
                                <div class="hidden lg:block overflow-x-auto">
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
                                            <!-- Items inside Order -->
                                            <template x-for="(it, idx) in filteredActiveItems" :key="it.id || (it.sku + '_' + idx)">
                                            <tr class="transition-colors"
                                                :class="it._justModified 
                                                    ? 'bg-amber-50/90 border-l-4 border-l-amber-500 ring-1 ring-amber-300' 
                                                    : (it.is_picked ? 'bg-emerald-50/30' : 'hover:bg-slate-50/80')">
                                                
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
                                                    <span class="px-2 py-1 bg-white/80 rounded-md border border-slate-200 text-slate-700 font-semibold" x-text="activeOrder.warehouse || 'Kho Tổng'"></span>
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
                                                                        <span x-text="(it.assigned_staff && it.assigned_staff !== 'null' ? it.assigned_staff : 'Chưa phân công') + (it.assigned_staff === currentUserName ? ' (Tôi)' : '')"></span>
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
                                                                    <select :value="(it.assigned_staff && it.assigned_staff !== 'null') ? it.assigned_staff : ''"
                                                                            @change="updateItemAssignedStaff(it, $event.target.value)"
                                                                            class="w-full max-w-[190px] px-2.5 py-1.5 text-xs font-bold rounded-lg border focus:outline-none focus:ring-1 focus:ring-amber-500 transition-all cursor-pointer shadow-2xs"
                                                                            :class="(it.assigned_staff && it.assigned_staff !== 'null') ? 'bg-amber-50/70 text-amber-950 border-amber-300' : 'bg-white text-slate-500 border-slate-300 hover:border-slate-400'">
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
                                                            <input type="number"
                                                                   :step="isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) ? '1' : 'any'"
                                                                   :inputmode="isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) ? 'numeric' : 'decimal'"
                                                                   min="0"
                                                                   :max="Number(it.quantity || 0) + Number(it.gift_quantity || 0)"
                                                                   :value="formatQtyInput(it.picked_qty, Number(it.quantity || 0) + Number(it.gift_quantity || 0))"
                                                                   @focus="$event.target.select()"
                                                                   @keydown="if (isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) && ($event.key === '.' || $event.key === ',')) $event.preventDefault()"
                                                                   @change="it.picked_qty = formatQtyInput($event.target.value, Number(it.quantity || 0) + Number(it.gift_quantity || 0)); $event.target.value = it.picked_qty; validateAndSavePickedQty(it)"
                                                                   @keydown.enter="$event.target.blur()"
                                                                   class="w-16 p-1 text-center font-mono font-bold text-xs rounded-lg border focus:outline-none transition-all shadow-2xs border-slate-300 text-slate-900 bg-white focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                                                            
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

                                                <!-- Trạng Thái Soạn -->
                                                <td class="py-3.5 px-4 text-center">
                                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                                          :class="Number(it.picked_qty || 0) >= (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) && (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) > 0
                                                              ? 'bg-emerald-50 text-emerald-700 border-emerald-300'
                                                              : (Number(it.picked_qty || 0) > 0 
                                                                  ? 'bg-sky-50 text-sky-700 border-sky-300' 
                                                                  : 'bg-amber-50 text-amber-800 border-amber-300')"
                                                          x-text="Number(it.picked_qty || 0) >= (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) && (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) > 0
                                                              ? 'Đã bốc đủ' 
                                                              : (Number(it.picked_qty || 0) > 0 
                                                                  ? 'Đang bốc (' + formatQtyInput(it.picked_qty, Number(it.quantity || 0) + Number(it.gift_quantity || 0)) + '/' + (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) + ')' 
                                                                  : 'Chờ bốc')"></span>
                                                </td>

                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Cards (Mobile: Thẻ sản phẩm tối ưu điện thoại) -->
                            <div class="block lg:hidden p-2.5 space-y-2.5">
                                <template x-for="(it, idx) in filteredActiveItems" :key="it.id || (it.sku + '_' + idx)">
                                    <div class="p-3 rounded-xl border transition-all space-y-2.5"
                                         :class="it._justModified 
                                            ? 'bg-amber-50/90 border-amber-300 ring-1 ring-amber-300' 
                                            : (it.is_picked ? 'bg-emerald-50/40 border-emerald-300' : 'bg-white border-slate-200 shadow-2xs')">
                                        
                                        <!-- Row 1: Checkbox + SKU + Badge SL sửa + ĐVT + Trạng thái -->
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" x-model="it.is_picked"
                                                       :disabled="isOrderLockedForPicking(activeOrder) || !canUserPickItem(it)"
                                                       @change="handlePickCheckChange(it)"
                                                       class="w-5 h-5 rounded text-sky-600 focus:ring-0 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-mono font-bold text-sky-700 text-xs" x-text="it.sku"></span>
                                                    <template x-if="it._justModified">
                                                        <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-black text-[9px] animate-pulse flex items-center gap-1">
                                                            <i class="fa-solid fa-triangle-exclamation text-[8px]"></i>
                                                            <span>Sửa SL</span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[10px] uppercase font-mono" x-text="it.unit || 'CÂY'"></span>
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                      :class="getItemStatusBadgeClass(it)"
                                                      x-text="(Number(it.picked_qty || 0) >= (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) && (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) > 0) ? 'Đã bốc đủ' : (Number(it.picked_qty || 0) > 0 ? 'Đang bốc (' + formatQtyInput(it.picked_qty, Number(it.quantity || 0) + Number(it.gift_quantity || 0)) + '/' + (Number(it.quantity || 0) + Number(it.gift_quantity || 0)) + ')' : 'Chờ bốc')"></span>
                                            </div>
                                        </div>

                                        <!-- Row 2: Product Name -->
                                        <div class="text-xs font-bold text-slate-800 leading-snug" x-text="it.name"></div>

                                        <!-- Row 3: Người soạn hàng -->
                                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5 text-xs">
                                            <span class="text-[11px] text-slate-500 font-medium">Người soạn hàng:</span>

                                            <!-- Người soạn -->
                                            <div>
                                                <!-- Case 1: Trưởng nhóm / Admin phân công -->
                                                <template x-if="canAssignItem(it)">
                                                    <div class="flex items-center gap-1.5">
                                                        <template x-if="isItemStaffLocked(it)">
                                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-100 text-amber-950 border border-amber-300">
                                                                <i class="fa-solid fa-user-check text-[10px] text-amber-600"></i>
                                                                <span x-text="(it.assigned_staff && it.assigned_staff !== 'null' ? it.assigned_staff : 'Chưa phân công') + (it.assigned_staff === currentUserName ? ' (Tôi)' : '')"></span>
                                                                <button type="button" x-show="!isOrderLockedForPicking(activeOrder)"
                                                                        @click="it._forceUnlock = true"
                                                                        class="text-[10px] text-sky-700 underline font-bold ml-1">Đổi</button>
                                                            </div>
                                                        </template>
                                                        <template x-if="!isItemStaffLocked(it)">
                                                            <div>
                                                                <template x-if="!isOrderLockedForPicking(activeOrder)">
                                                                    <select :value="(it.assigned_staff && it.assigned_staff !== 'null') ? it.assigned_staff : ''"
                                                                            @change="updateItemAssignedStaff(it, $event.target.value)"
                                                                            class="w-full text-xs font-bold p-1 rounded border border-amber-300 bg-amber-50/60 text-amber-950">
                                                                        <option value="">-- Chỉ định người bốc --</option>
                                                                        <template x-for="st in availableStaffForCategory(it.category_id)" :key="st.id">
                                                                            <option :value="st.name" :selected="it.assigned_staff === st.name" x-text="st.name"></option>
                                                                        </template>
                                                                    </select>
                                                                </template>
                                                                <template x-if="isOrderLockedForPicking(activeOrder)">
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                                        <i class="fa-solid fa-user-slash text-[9px] text-slate-400"></i>
                                                                        <span>Chưa phân công</span>
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                <!-- Case 2: Thành viên tự nhận -->
                                                <template x-if="!canAssignItem(it)">
                                                    <div>
                                                        <template x-if="!it.assigned_staff || it.assigned_staff === 'null'">
                                                            <div>
                                                                <template x-if="!isOrderLockedForPicking(activeOrder)">
                                                                    <button type="button" @click="claimItem(it)"
                                                                            class="px-2 py-1 rounded bg-amber-500 text-white font-bold text-[11px] flex items-center gap-1 shadow-2xs">
                                                                        <i class="fa-solid fa-hand text-[10px]"></i>
                                                                        <span>Tự nhận</span>
                                                                    </button>
                                                                </template>
                                                                <template x-if="isOrderLockedForPicking(activeOrder)">
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                                        <i class="fa-solid fa-user-slash text-[9px] text-slate-400"></i>
                                                                        <span>Chưa phân công</span>
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <template x-if="it.assigned_staff && it.assigned_staff !== 'null' && it.assigned_staff === currentUserName">
                                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-300">
                                                                <i class="fa-solid fa-check text-[10px]"></i>
                                                                <span>Tôi phụ trách</span>
                                                                <button type="button" x-show="!isOrderLockedForPicking(activeOrder) && !it.is_picked && Number(it.picked_qty || 0) === 0"
                                                                        @click="unclaimItem(it)" class="text-[9px] text-rose-600 underline ml-1 font-semibold">Hủy</button>
                                                            </div>
                                                        </template>
                                                        <template x-if="it.assigned_staff && it.assigned_staff !== 'null' && it.assigned_staff !== currentUserName">
                                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[11px] font-medium border border-slate-200" x-text="it.assigned_staff"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Row 4: Quantities (Cần bốc vs Đã bốc với Steppers) -->
                                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 bg-slate-50/80 -mx-3 -mb-3 p-2.5 rounded-b-xl flex-wrap">
                                            <!-- Cần bốc -->
                                            <div class="flex items-center gap-1.5 text-xs">
                                                <span class="text-slate-500 font-bold">Cần:</span>
                                                <span class="font-mono font-black text-slate-900 text-sm" x-text="Number(it.quantity || 0) + Number(it.gift_quantity || 0)"></span>
                                                <template x-if="Number(it.gift_quantity || 0) > 0">
                                                    <span class="text-[9px] font-bold text-amber-800 bg-amber-100 px-1 py-0.2 rounded"
                                                          x-text="'(' + it.quantity + '+' + it.gift_quantity + ' tặng)'"></span>
                                                </template>
                                            </div>

                                            <!-- Đã bốc với Steppers [-] [SL] [+] và Nút [Đủ] -->
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-slate-500 font-semibold text-xs hidden xs:inline">Đã bốc:</span>
                                                <template x-if="canUserPickItem(it)">
                                                    <div class="flex items-center gap-1">
                                                        <button type="button" @click="decrementPickedQty(it)"
                                                                :disabled="Number(it.picked_qty || 0) <= 0"
                                                                class="w-7 h-7 rounded-lg bg-white border border-slate-300 text-slate-700 font-black text-xs flex items-center justify-center active:scale-95 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed shadow-2xs cursor-pointer">
                                                            <i class="fa-solid fa-minus text-[10px]"></i>
                                                        </button>
                                                        <input type="number"
                                                               :step="isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) ? '1' : 'any'"
                                                               :inputmode="isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) ? 'numeric' : 'decimal'"
                                                               min="0"
                                                               :max="Number(it.quantity || 0) + Number(it.gift_quantity || 0)"
                                                               :value="formatQtyInput(it.picked_qty, Number(it.quantity || 0) + Number(it.gift_quantity || 0))"
                                                               @focus="$event.target.select()"
                                                               @keydown="if (isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) && ($event.key === '.' || $event.key === ',')) $event.preventDefault()"
                                                               @change="it.picked_qty = formatQtyInput($event.target.value, Number(it.quantity || 0) + Number(it.gift_quantity || 0)); $event.target.value = it.picked_qty; validateAndSavePickedQty(it)"
                                                               @keydown.enter="$event.target.blur()"
                                                               class="w-14 h-7 p-0.5 text-center font-mono font-black text-xs rounded-lg border border-slate-300 text-slate-900 bg-white focus:border-sky-500 focus:outline-none shadow-2xs">
                                                        <button type="button" @click="incrementPickedQty(it)"
                                                                :disabled="Number(it.picked_qty || 0) >= (Number(it.quantity || 0) + Number(it.gift_quantity || 0))"
                                                                class="w-7 h-7 rounded-lg bg-white border border-slate-300 text-slate-700 font-black text-xs flex items-center justify-center active:scale-95 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed shadow-2xs cursor-pointer">
                                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                                        </button>
                                                        <button type="button" @click="pickFullItem(it)"
                                                                class="h-7 px-2.5 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-black text-xs rounded-lg shadow-2xs flex items-center gap-1 cursor-pointer ml-0.5">
                                                            <span>Đủ</span>
                                                        </button>
                                                    </div>
                                                </template>
                                                <template x-if="!canUserPickItem(it)">
                                                    <span class="px-2 py-0.5 bg-slate-200 text-slate-600 font-mono font-bold text-xs rounded flex items-center gap-1"
                                                          :title="!it.assigned_staff ? 'Chưa phân công' : ('Chỉ ' + it.assigned_staff + ' được sửa')">
                                                        <i class="fa-solid fa-lock text-[9px]"></i>
                                                        <span x-text="Number(it.picked_qty || 0)"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- =============================================== -->
                    <!-- TAB 2 CONTENT: 2. KIỂM HÀNG (QC SOẠN ĐƠN)     -->
                    <!-- =============================================== -->
                    <div x-show="activeTab === 'qc'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mt-0">
                        
                        <!-- Panel Header (Tối giản, gọn gàng) -->
                        <div class="px-3.5 py-2.5 sm:px-5 sm:py-3 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 bg-white">
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight flex items-center gap-1.5 flex-wrap">
                                    <span class="text-emerald-700 font-mono" x-text="activeOrder.order_code"></span>
                                    <template x-if="activeOrder.is_consignment">
                                        <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-[10px] inline-flex items-center gap-1">
                                            <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                                            <span>Hàng Ôm</span>
                                        </span>
                                    </template>
                                    <span class="text-slate-300">|</span>
                                    <span x-text="activeOrder.customer_name" class="font-bold truncate"></span>
                                    <span class="inline-flex px-1.5 py-0.2 rounded-full text-[9px] font-bold border shrink-0"
                                          :class="activeOrder.stock_deducted || ['Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Đang vận chuyển', 'Đã giao'].includes(activeOrder.status) ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300'"
                                          x-text="(activeOrder.stock_deducted || ['Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Đang vận chuyển', 'Đã giao'].includes(activeOrder.status)) ? 'Đã kiểm · Đã trừ kho' : 'Đang kiểm'">
                                    </span>
                                </h3>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-500 mt-0.5">
                                    <span>Kho: <strong class="text-slate-700" x-text="activeOrder.warehouse || 'Kho Tổng'"></strong></span>
                                    <span>·</span>
                                    <span>Tiến độ: <strong class="text-emerald-700 font-bold font-mono" x-text="getProgressRatio(activeOrder)"></strong></span>
                                    <template x-if="activeOrder.delivery_date">
                                        <span>· Giao: <strong class="text-emerald-700 font-mono font-bold" x-text="formatDate(activeOrder.delivery_date)"></strong></span>
                                    </template>
                                </div>
                            </div>

                            <!-- Action Buttons QC: Lưu & Giao xe -->
                            <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                                <template x-if="hasUnsavedQc(activeOrder)">
                                    <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black animate-pulse">
                                        Chưa lưu kiểm
                                    </span>
                                </template>

                                <button type="button" @click="saveQcActiveOrder()"
                                        :disabled="isSavingBatchOrSingle || ['Đang vận chuyển', 'Đã giao', 'Giao thành công'].includes(activeOrder.status)"
                                        class="px-3 py-1.5 rounded-lg text-xs font-black text-white bg-sky-600 hover:bg-sky-700 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-2xs disabled:opacity-40"
                                        title="Lưu số lượng kiểm tạm thời">
                                    <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                                    <span>Lưu</span>
                                </button>

                                <button type="button" @click="handoverQcActiveOrderToVehicle()"
                                        :disabled="!isOrderTransferredToQc(activeOrder) || isSavingBatchOrSingle || ['Đang vận chuyển', 'Đã giao', 'Giao thành công'].includes(activeOrder.status)"
                                        :title="!isOrderTransferredToQc(activeOrder) ? 'Bên Bốc chưa hoàn tất & bấm Chuyển kiểm! Chưa thể giao xe.' : 'Xác nhận kiểm xong & Giao xe (Trừ tồn kho)'"
                                        :class="isOrderTransferredToQc(activeOrder) ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs cursor-pointer' : 'bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-black transition-all flex items-center justify-center gap-1 disabled:opacity-70">
                                    <i class="fa-solid" :class="isOrderTransferredToQc(activeOrder) ? 'fa-truck-fast text-[11px]' : 'fa-lock text-[11px]'"></i>
                                    <span>Giao xe</span>
                                </button>
                            </div>
                        </div>

                        <!-- Cảnh báo nếu bên Bốc chưa bấm Chuyển kiểm -->
                        <template x-if="activeOrder && !isOrderTransferredToQc(activeOrder)">
                            <div class="m-3 p-2.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-950 text-xs flex items-center gap-2 shadow-2xs">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm shrink-0"></i>
                                <div>
                                    <div class="font-black text-xs">Chưa bốc xong / Chưa bấm "Chuyển kiểm" — Không được Giao xe!</div>
                                    <div class="text-[11px] text-amber-800">Chỉ khi bên bốc bấm nút <strong>Chuyển kiểm</strong> thì nút <strong>Giao xe</strong> mới kích hoạt.</div>
                                </div>
                            </div>
                        </template>

                        <!-- Sub-bar: QC Inspector & Batch Action (Gọn gàng) -->
                        <div class="px-3 py-1.5 sm:px-4 sm:py-2 bg-slate-50/90 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-slate-600 text-[11px] shrink-0">
                                    <i class="fa-solid fa-user-shield text-slate-400 mr-1"></i>QC:
                                </span>
                                <select x-model="qcInspector" class="p-1 bg-white rounded-lg border border-slate-300 text-[11px] font-bold text-slate-800 focus:outline-none focus:border-emerald-500 cursor-pointer max-w-[200px] truncate">
                                    @foreach($staff as $st)
                                        <option value="{{ $st->name }} ({{ $st->department }})">{{ $st->name }} ({{ $st->department }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Button Tích kiểm tất cả -->
                            <button type="button" @click="qcCheckAllActiveOrder()"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-2xs">
                                <i class="fa-solid fa-square-check text-emerald-600 text-[11px]"></i>
                                <span>Tích kiểm tất cả</span>
                            </button>
                        </div>

                        <!-- Danh sách mặt hàng kiểm (Dạng phẳng) -->
                        <div class="p-2.5 sm:p-4">
                            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
                                <!-- Table (Desktop) -->
                                <div class="hidden lg:block overflow-x-auto">
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
                                            <!-- Items inside Order -->
                                            <template x-for="(it, idx) in filteredActiveItems" :key="it.id || idx">
                                                    <tr class="hover:bg-slate-50/80 transition-colors"
                                                        :class="it.is_checked ? 'bg-emerald-50/30' : ''">
                                                
                                                <!-- Checkbox Đã Kiểm -->
                                                <td class="py-3.5 px-4 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <input type="checkbox" x-model="it.is_checked"
                                                               :disabled="!isOrderTransferredToQc(activeOrder) && !it.is_picked && Number(it.picked_qty || 0) <= 0"
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
                                                    <input type="number"
                                                           :step="isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) ? '1' : 'any'"
                                                           :inputmode="isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) ? 'numeric' : 'decimal'"
                                                           min="0"
                                                           :max="isOrderTransferredToQc(activeOrder) ? Number(it.picked_qty || 0) : (Number(it.picked_qty) > 0 ? Number(it.picked_qty) : (Number(it.quantity || 0) + Number(it.gift_quantity || 0)))"
                                                           :value="formatQtyInput(it.checked_qty, Number(it.quantity || 0) + Number(it.gift_quantity || 0))"
                                                           @focus="$event.target.select()"
                                                           @keydown="if (isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) && ($event.key === '.' || $event.key === ',')) $event.preventDefault()"
                                                           @change="it.checked_qty = formatQtyInput($event.target.value, Number(it.quantity || 0) + Number(it.gift_quantity || 0)); $event.target.value = it.checked_qty; saveItemQcQty(it)"
                                                           @keydown.enter="$event.target.blur()"
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

                                <!-- Cards (Mobile: Thẻ kiểm hàng QC tối ưu điện thoại) -->
                                <div class="block lg:hidden p-2.5 space-y-2.5">
                                    <template x-for="(it, idx) in filteredActiveItems" :key="it.id || idx">
                                            <div class="p-3 rounded-xl border transition-all space-y-2.5"
                                                 :class="it.is_checked ? 'bg-emerald-50/40 border-emerald-300' : 'bg-white border-slate-200 shadow-2xs'">
                                                
                                                <!-- Row 1: Checkbox QC + SKU + Badge SL sửa + ĐVT + Trạng thái -->
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" x-model="it.is_checked"
                                                               :disabled="!isOrderTransferredToQc(activeOrder) && !it.is_picked && Number(it.picked_qty || 0) <= 0"
                                                               @change="handleQcCheckChange(it)"
                                                               class="w-5 h-5 rounded text-emerald-600 focus:ring-0 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                            <span class="font-mono font-black text-slate-800 text-xs" x-text="it.sku"></span>
                                                            <template x-if="it._justModified">
                                                                <span class="px-1.5 py-0.5 rounded bg-amber-500 text-white font-black text-[9px] animate-pulse">
                                                                    Sửa SL
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 shrink-0">
                                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[10px] uppercase font-mono" x-text="it.unit || 'CÂY'"></span>
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                              :class="it.is_checked ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-500 border-slate-200'"
                                                              x-text="it.is_checked ? 'Đã kiểm' : 'Chưa kiểm'"></span>
                                                    </div>
                                                </div>

                                                <!-- Row 2: Product Name -->
                                                <div class="text-xs font-semibold text-slate-700 leading-snug" x-text="it.name"></div>

                                                <!-- Row 3: Người đã soạn & Trạng thái soạn -->
                                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                                                    <div class="flex items-center gap-1 text-[11px] text-slate-600">
                                                        <i class="fa-solid fa-user-check text-slate-400 text-[10px]"></i>
                                                        <span class="font-medium text-slate-400">Soạn:</span>
                                                        <span class="font-bold text-slate-700" x-text="it.assigned_staff || 'Chưa phân công'"></span>
                                                    </div>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded"
                                                          :class="Number(it.picked_qty) >= (Number(it.quantity) + Number(it.gift_quantity || 0)) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (Number(it.picked_qty) > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-500')"
                                                          x-text="Number(it.picked_qty) >= (Number(it.quantity) + Number(it.gift_quantity || 0)) ? 'Đã bốc đủ' : (Number(it.picked_qty) > 0 ? 'Đang bốc' : 'Chưa bốc')"></span>
                                                </div>

                                                <!-- Row 4: Quantities (Cần / Đã bốc / Thực kiểm với Stepper) -->
                                                <div class="pt-2 border-t border-slate-100 bg-slate-50/80 -mx-3 p-2.5 space-y-2 rounded-b-xl">
                                                    <div class="flex items-center justify-between text-xs">
                                                        <div class="flex items-center gap-3">
                                                            <div>
                                                                <span class="text-[10px] text-slate-400 block font-medium">Cần</span>
                                                                <span class="font-mono font-black text-slate-800 text-xs" x-text="Number(it.quantity || 0) + Number(it.gift_quantity || 0)"></span>
                                                            </div>
                                                            <div>
                                                                <span class="text-[10px] text-slate-400 block font-medium">Đã bốc</span>
                                                                <span class="font-mono font-black text-sky-700 text-xs" x-text="Number(it.picked_qty || 0)"></span>
                                                            </div>
                                                        </div>

                                                        <!-- Thực kiểm với Stepper [-] [SL] [+] [Đủ] -->
                                                        <div class="flex items-center gap-1">
                                                            <span class="text-[10px] text-emerald-700 font-bold mr-0.5">Kiểm:</span>
                                                            <button type="button" @click="decrementCheckedQty(it)"
                                                                    :disabled="Number(it.checked_qty || 0) <= 0"
                                                                    class="w-7 h-7 rounded-lg bg-white border border-slate-300 text-slate-700 font-black text-xs flex items-center justify-center active:scale-95 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed shadow-2xs cursor-pointer">
                                                                <i class="fa-solid fa-minus text-[10px]"></i>
                                                            </button>

                                                            <input type="number"
                                                                   :step="isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) ? '1' : 'any'"
                                                                   :inputmode="isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) ? 'numeric' : 'decimal'"
                                                                   min="0"
                                                                   :max="isOrderTransferredToQc(activeOrder) ? Number(it.picked_qty || 0) : (Number(it.picked_qty) > 0 ? Number(it.picked_qty) : (Number(it.quantity || 0) + Number(it.gift_quantity || 0)))"
                                                                   :value="formatQtyInput(it.checked_qty, Number(it.quantity || 0) + Number(it.gift_quantity || 0))"
                                                                   @focus="$event.target.select()"
                                                                   @keydown="if (isIntegerQty(Number(it.quantity || 0) + Number(it.gift_quantity || 0)) && ($event.key === '.' || $event.key === ',')) $event.preventDefault()"
                                                                   @change="it.checked_qty = formatQtyInput($event.target.value, Number(it.quantity || 0) + Number(it.gift_quantity || 0)); $event.target.value = it.checked_qty; saveItemQcQty(it)"
                                                                   @keydown.enter="$event.target.blur()"
                                                                   class="w-14 h-7 p-0.5 text-center font-mono font-black text-xs rounded-lg border border-slate-300 focus:border-emerald-500 bg-white shadow-2xs">

                                                            <button type="button" @click="incrementCheckedQty(it)"
                                                                    class="w-7 h-7 rounded-lg bg-white border border-slate-300 text-slate-700 font-black text-xs flex items-center justify-center active:scale-95 hover:bg-slate-100 shadow-2xs cursor-pointer">
                                                                <i class="fa-solid fa-plus text-[10px]"></i>
                                                            </button>

                                                            <button type="button" @click="checkFullItemQc(it)"
                                                                    class="h-7 px-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs rounded-lg shadow-2xs flex items-center gap-1 cursor-pointer ml-0.5">
                                                                <span>Đủ</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Row 5: Ghi chú KCS -->
                                                <div class="pt-2 border-t border-slate-100">
                                                    <input type="text" x-model="it.check_notes"
                                                           @blur="saveItemCheckNotes(it)"
                                                           placeholder="Ghi chú tem kiện, KCS..."
                                                           class="w-full p-1.5 text-xs rounded-lg border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500 bg-slate-50/50 focus:bg-white placeholder:text-slate-400">
                                                </div>

                                            </div>
                                        </template>
                                    </div>
                                </div>
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
                    <div x-show="activeTab === 'picking'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden p-3 sm:p-5 space-y-3 sm:space-y-4 mt-0">
                        
                        <!-- 1. Header Toolbar (Responsive & Tối ưu mobile) -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight flex items-center gap-1.5">
                                        <i class="fa-solid fa-layer-group text-sky-600"></i>
                                        <span x-text="'Danh Sách Hàng Hóa Gộp Soạn Bốc (' + aggregatedItems.length + ' mặt hàng)'"></span>
                                    </h3>
                                    <span class="px-2 py-0.5 bg-sky-50 text-sky-700 border border-sky-200 rounded-full font-bold text-[11px]"
                                          x-text="selectedBatchOrderIds.length + ' đơn đã chọn'"></span>
                                </div>
                                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Tự động gom vật tư cùng mã SKU, cộng dồn số lượng cần bốc từ các đơn đã chọn.</p>
                            </div>

                            <!-- Action Buttons Lô Gộp: Bốc đủ lô, Lưu, Chuyển kiểm -->
                            <div class="flex flex-wrap items-center gap-2">
                                <template x-if="hasAnyUnsavedBatchPicking">
                                    <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black animate-pulse">
                                        Chưa lưu SL lô
                                    </span>
                                </template>

                                <button type="button" @click="quickPickAllBatchOrders()"
                                        :disabled="isSavingBatchOrSingle"
                                        class="flex-1 sm:flex-none px-3 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-300 font-bold text-xs rounded-xl shadow-2xs transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-40">
                                    <i class="fa-solid fa-bolt-lightning text-xs"></i>
                                    <span>Bốc đủ cả lô</span>
                                </button>

                                <button type="button" @click="saveBatchPickingOrders()"
                                        :disabled="isSavingBatchOrSingle"
                                        class="flex-1 sm:flex-none px-4 py-2 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-black text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-40">
                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                    <span>Lưu</span>
                                </button>

                                <button type="button" @click="completeAllBatchOrders()"
                                        :disabled="isSavingBatchOrSingle"
                                        class="w-full sm:w-auto px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-black text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-40">
                                    <i class="fa-solid fa-right-to-bracket text-xs"></i>
                                    <span>Chuyển kiểm</span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Batch Staff Assignment Toolbar (Giao cả lô cho) -->
                        <div class="bg-slate-50/90 p-2.5 sm:p-3 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-700 flex items-center gap-1.5 shrink-0 text-[11px] sm:text-xs">
                                    <i class="fa-solid fa-user-plus text-sky-600"></i>
                                    <span>Giao cả lô:</span>
                                </span>

                                <div class="relative flex-1 sm:flex-none min-w-[170px]">
                                    <select x-model="batchAssignStaff" @change="if(batchAssignStaff) assignBatchAll(batchAssignStaff)"
                                            class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:outline-none focus:border-sky-500 shadow-2xs cursor-pointer">
                                        <option value="">-- Tìm nhân sự giao cả lô --</option>
                                        <template x-for="st in staffList" :key="st.id">
                                            <option :value="st.name" x-text="st.name + (st.department ? ' (' + st.department + ')' : '')"></option>
                                        </template>
                                    </select>
                                </div>

                                <button type="button" @click="claimBatchAll()"
                                        class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold rounded-lg transition-all shadow-2xs flex items-center gap-1 cursor-pointer shrink-0 text-xs">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                    <span>Tự nhận cả lô</span>
                                </button>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" @click="unassignBatchAll()"
                                        class="w-full sm:w-auto px-2.5 py-1.5 border border-rose-300 hover:bg-rose-50 text-rose-600 font-bold rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer text-xs">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                    <span>Hủy phân công cả lô</span>
                                </button>
                            </div>
                        </div>

                        <!-- 3. Aggregated Items Table for Desktop (Ẩn trên mobile) -->
                        <div class="hidden lg:block overflow-x-auto border border-slate-200 rounded-xl">
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
                                            <td class="py-3.5 px-4 space-y-1.5">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-mono font-bold text-xs text-sky-800 bg-sky-50 px-2 py-0.5 rounded border border-sky-200 uppercase whitespace-nowrap" x-text="ag.sku"></span>
                                                    <span class="font-black text-slate-900 text-xs sm:text-[13px]" x-text="ag.name"></span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 font-normal flex flex-wrap items-center gap-1.5">
                                                    <span class="font-bold text-slate-600 shrink-0" x-text="'Gồm ' + ag.contributingOrders.length + ' đơn:'"></span>
                                                    <template x-for="c in ag.contributingOrders" :key="c.orderId">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border font-mono text-[11px] whitespace-nowrap shadow-2xs"
                                                              :class="c.pickedQty < c.totalReq && ag.totalPicked < ag.totalRequired ? 'bg-amber-50 border-amber-300' : 'bg-white border-slate-200'">
                                                            <span class="text-sky-700 font-bold" x-text="c.orderCode"></span>:
                                                            <span class="font-extrabold" :class="c.pickedQty < c.totalReq && ag.totalPicked < ag.totalRequired ? 'text-amber-800' : 'text-slate-900'"
                                                                  x-text="formatQtyInput(c.pickedQty, c.totalReq) + '/' + formatQtyInput(c.totalReq, c.totalReq) + ' ' + (ag.unit || '').toLowerCase()"></span>
                                                        </span>
                                                    </template>
                                                </div>
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
                                                    <input type="number"
                                                           :step="isIntegerQty(ag.totalRequired) ? '1' : 'any'"
                                                           :inputmode="isIntegerQty(ag.totalRequired) ? 'numeric' : 'decimal'"
                                                           min="0" :max="ag.totalRequired"
                                                           :value="formatQtyInput(ag.totalPicked, ag.totalRequired)"
                                                           @focus="$event.target.select()"
                                                           @keydown="if (isIntegerQty(ag.totalRequired) && ($event.key === '.' || $event.key === ',')) $event.preventDefault()"
                                                           @change="let cleanVal = formatQtyInput($event.target.value, ag.totalRequired); $event.target.value = cleanVal; updateBatchItemPickedQty(ag, cleanVal)"
                                                           @keydown.enter="$event.target.blur()"
                                                           class="w-16 p-1 text-center font-mono font-bold text-xs rounded-lg border border-slate-300 text-slate-900 bg-white focus:outline-none focus:border-sky-500 shadow-2xs">
                                                    
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
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                          :class="ag.badgeClass"
                                                          x-text="ag.status"></span>
                                                    <template x-if="ag.totalPicked < ag.totalRequired && ag.totalRequired > 0 && ag.contributingOrders.length > 1">
                                                        <button type="button" 
                                                                @click="openVehicleShortageModal(ag)"
                                                                class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-[10.5px] shadow-xs flex items-center gap-1 cursor-pointer transition-all"
                                                                title="Bốc thiếu: Chia số lượng thực bốc cho từng đơn hàng trong lô">
                                                            <i class="fa-solid fa-arrows-split-up-and-left text-[9px]"></i>
                                                            <span>Phân bổ đơn</span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- 4. Mobile Aggregated Items Cards (Chuyên dụng cho điện thoại) -->
                        <div class="block lg:hidden space-y-2.5">
                            <!-- Mobile Select All Toolbar -->
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                                    <input type="checkbox"
                                           :checked="selectedAggregatedSkus.length > 0 && selectedAggregatedSkus.length === aggregatedItems.length"
                                           @change="toggleSelectAllAggregated($event.target.checked)"
                                           class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500 cursor-pointer">
                                    <span>Chọn tất cả (<span x-text="aggregatedItems.length"></span> SP)</span>
                                </label>
                                <span class="text-[11px] font-mono font-bold text-sky-700" x-text="'Đã chọn: ' + selectedAggregatedSkus.length"></span>
                            </div>

                            <template x-for="ag in aggregatedItems" :key="'m_pick_' + ag.sku">
                                <div class="p-3 rounded-2xl border transition-all space-y-2.5 shadow-2xs"
                                     :class="ag.isFullyPicked 
                                         ? 'border-emerald-300 bg-emerald-50/25 ring-1 ring-emerald-300/40' 
                                         : (ag.totalPicked > 0 
                                             ? 'border-amber-300 bg-amber-50/25' 
                                             : 'border-slate-200/90 bg-white')">
                                    
                                    <!-- Row 1: Checkbox + SKU / Mã đơn + Unit + Status (Bỏ hiển thị kho theo yêu cầu) -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2 flex-wrap min-w-0">
                                            <input type="checkbox" :value="ag.sku" x-model="selectedAggregatedSkus"
                                                   class="w-5 h-5 rounded text-sky-600 border-slate-300 focus:ring-sky-500 cursor-pointer shrink-0">
                                            <span class="font-mono font-black text-sky-800 text-xs sm:text-sm whitespace-nowrap" x-text="ag.sku"></span>
                                            
                                            <!-- Nếu gom từ 1 đơn thì hiển thị luôn mã đơn hàng đầy đủ ngay tại hàng đầu -->
                                            <template x-if="ag.contributingOrders && ag.contributingOrders.length === 1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 font-mono font-bold text-[11px] whitespace-nowrap">
                                                    <i class="fa-solid fa-file-invoice text-[9px] text-sky-600"></i>
                                                    <span x-text="ag.contributingOrders[0].orderCode"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <div class="shrink-0 flex items-center justify-end">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                  :class="ag.badgeClass"
                                                  x-text="ag.status"></span>
                                        </div>
                                    </div>

                                    <!-- Row 2: Product Name -->
                                    <div class="text-xs sm:text-sm font-black text-slate-900 leading-snug" x-text="ag.name"></div>

                                    <!-- Row 3: Contributing Orders Chips (Hiển thị đầy đủ mã đơn hàng) -->
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                        <div class="text-[10.5px] font-bold text-slate-600 flex items-center justify-between">
                                            <span x-text="'Gồm ' + ag.contributingOrders.length + ' đơn hàng:'"></span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="c in ag.contributingOrders" :key="c.orderId">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs shadow-2xs whitespace-nowrap"
                                                      :class="c.pickedQty < c.totalReq && ag.totalPicked < ag.totalRequired ? 'bg-amber-50 border-amber-300' : 'bg-white border-sky-200'">
                                                    <span class="font-mono font-black text-sky-700 tracking-tight" x-text="c.orderCode"></span>
                                                    <span class="text-slate-300">:</span>
                                                    <span class="font-bold" :class="c.pickedQty < c.totalReq && ag.totalPicked < ag.totalRequired ? 'text-amber-800' : 'text-slate-900'"
                                                          x-text="formatQtyInput(c.pickedQty, c.totalReq) + '/' + formatQtyInput(c.totalReq, c.totalReq) + ' ' + (ag.unit || '').toLowerCase()"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Row 4: Assigned Staff (Người soạn) -->
                                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 text-xs">
                                        <div class="flex items-center gap-1.5 flex-wrap flex-1 min-w-0">
                                            <span class="text-[11px] text-slate-400 font-medium shrink-0">Soạn:</span>
                                            <div class="flex flex-wrap items-center gap-1 min-w-0">
                                                <template x-for="stName in ag.assignedStaffList" :key="stName">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-100 text-amber-950 font-bold text-[10px] border border-amber-300">
                                                        <span x-text="stName === currentUserName ? stName + ' (Tôi)' : stName"></span>
                                                        <button type="button" @click="unassignBatchItemStaff(ag, stName)" class="hover:text-rose-600 cursor-pointer font-black ml-0.5">&times;</button>
                                                    </span>
                                                </template>
                                                <select @change="if($event.target.value) { assignBatchItemStaff(ag, $event.target.value); $event.target.value = ''; }"
                                                        class="px-2 py-1 text-[10px] rounded-lg border border-slate-300 bg-white text-slate-700 focus:outline-none focus:border-sky-500 shadow-2xs cursor-pointer">
                                                    <option value="" x-text="ag.assignedStaffList && ag.assignedStaffList.length > 0 ? '+ Thêm người' : 'Giao người soạn'"></option>
                                                    <template x-for="st in staffList" :key="st.id">
                                                        <option :value="st.name" x-text="st.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                        <template x-if="!ag.assignedStaffList || !ag.assignedStaffList.includes(currentUserName)">
                                            <button type="button" @click="claimBatchItem(ag)"
                                                    class="px-2 py-1 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold text-[10px] rounded-lg shadow-2xs cursor-pointer flex items-center gap-1 shrink-0">
                                                <i class="fa-solid fa-phone text-[8px]"></i>
                                                <span>Tự nhận</span>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Row 5: Quantities Stepper & Actions -->
                                    <div class="flex items-center justify-between gap-2 p-2 rounded-xl bg-slate-50 border border-slate-100">
                                        <div class="text-xs">
                                            <div class="text-[10px] text-slate-400 font-bold uppercase">CẦN BỐC</div>
                                            <div class="font-mono font-black text-slate-900 text-sm" x-text="ag.totalRequired + ' ' + (ag.unit || '')"></div>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <div class="text-right mr-0.5">
                                                <div class="text-[10px] text-slate-400 font-bold uppercase">ĐÃ BỐC</div>
                                            </div>
                                            <!-- Stepper: Trừ / Input / Cộng / Đủ -->
                                            <div class="flex items-center gap-1">
                                                <button type="button" 
                                                        @click="let val = Math.max(0, (Number(ag.totalPicked) || 0) - 1); updateBatchItemPickedQty(ag, val);"
                                                        class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 font-black text-slate-700 flex items-center justify-center shadow-2xs cursor-pointer text-xs active:scale-95">
                                                    -
                                                </button>
                                                <input type="number"
                                                       :step="isIntegerQty(ag.totalRequired) ? '1' : 'any'"
                                                       :inputmode="isIntegerQty(ag.totalRequired) ? 'numeric' : 'decimal'"
                                                       min="0" :max="ag.totalRequired"
                                                       :value="formatQtyInput(ag.totalPicked, ag.totalRequired)"
                                                       @focus="$event.target.select()"
                                                       @keydown="if (isIntegerQty(ag.totalRequired) && ($event.key === '.' || $event.key === ',')) $event.preventDefault()"
                                                       @change="let cleanVal = formatQtyInput($event.target.value, ag.totalRequired); $event.target.value = cleanVal; updateBatchItemPickedQty(ag, cleanVal)"
                                                       @keydown.enter="$event.target.blur()"
                                                       class="w-14 p-1 text-center font-mono font-black text-xs rounded-lg border border-slate-300 text-slate-900 bg-white focus:outline-none focus:border-sky-500 shadow-2xs">
                                                <button type="button" 
                                                        @click="let val = Math.min(ag.totalRequired, (Number(ag.totalPicked) || 0) + 1); updateBatchItemPickedQty(ag, val);"
                                                        class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 font-black text-slate-700 flex items-center justify-center shadow-2xs cursor-pointer text-xs active:scale-95">
                                                    +
                                                </button>
                                                <button type="button" @click="pickFullBatchItem(ag)"
                                                        class="px-2.5 py-1 font-bold text-[11px] rounded-lg transition-all cursor-pointer shadow-2xs border active:scale-95"
                                                        :class="ag.isFullyPicked ? 'bg-sky-600 text-white border-sky-600' : 'bg-white hover:bg-sky-50 text-slate-700 border-slate-300'">
                                                    Đủ
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nút Phân bổ đơn nếu bốc thiếu ở gộp nhiều đơn -->
                                    <template x-if="ag.totalPicked < ag.totalRequired && ag.totalRequired > 0 && ag.contributingOrders.length > 1">
                                        <div>
                                            <button type="button" 
                                                    @click="openVehicleShortageModal(ag)"
                                                    class="w-full py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs shadow-xs flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                                                <i class="fa-solid fa-arrows-split-up-and-left text-xs"></i>
                                                <span>Phân bổ đơn (chia SL bốc thiếu cho từng đơn)</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                    </div>

                    <!-- ======================================================== -->
                    <!-- TAB 2: KIỂM HÀNG GỘP (BATCH QC WORKSPACE)                 -->
                    <!-- ======================================================== -->
                    <div x-show="activeTab === 'qc'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden p-3 sm:p-5 space-y-3 sm:space-y-4 mt-0">
                        
                        <!-- 1. Header Toolbar for Batch QC (Responsive & Tối ưu mobile) -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight flex items-center gap-1.5">
                                        <i class="fa-solid fa-clipboard-check text-emerald-600"></i>
                                        <span x-text="'Kiểm Tra Hàng Hóa Gộp QC (' + aggregatedItems.length + ' mặt hàng)'"></span>
                                    </h3>
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-[11px]"
                                          x-text="selectedBatchOrderIds.length + ' đơn đã chọn'"></span>
                                </div>
                                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Kiểm tra, đối soát chất lượng và số lượng hàng hóa gom lô trước khi xuất bến.</p>
                            </div>

                            <!-- Action Buttons Batch QC: Lưu & Giao xe -->
                            <div class="flex flex-wrap items-center gap-2">
                                <template x-if="hasAnyUnsavedBatchQc">
                                    <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black animate-pulse">
                                        Chưa lưu kiểm
                                    </span>
                                </template>

                                <button type="button" @click="saveBatchQcOrders()"
                                        :disabled="isSavingBatchOrSingle"
                                        class="flex-1 sm:flex-none px-4 py-2 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-black text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-40">
                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                    <span>Lưu</span>
                                </button>

                                <button type="button" @click="completeAllBatchQcOrders()"
                                        :disabled="!canHandoverBatchQcToVehicle || isSavingBatchOrSingle"
                                        :title="!canHandoverBatchQcToVehicle ? 'Còn đơn trong lô chưa bấm Chuyển kiểm bên Bốc! Không thể giao xe.' : 'Hoàn tất kiểm cả lô & Giao xe'"
                                        :class="canHandoverBatchQcToVehicle ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-xs' : 'bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed'"
                                        class="w-full sm:w-auto px-4 py-2 active:scale-95 font-black text-xs rounded-xl transition-all flex items-center justify-center gap-2 disabled:opacity-75">
                                    <i class="fa-solid" :class="canHandoverBatchQcToVehicle ? 'fa-truck-fast text-sm' : 'fa-lock text-xs'"></i>
                                    <span>Giao xe</span>
                                </button>
                            </div>
                        </div>

                        <!-- Cảnh báo nếu có đơn trong lô chưa chuyển kiểm -->
                        <template x-if="batchOrders.length > 0 && pendingTransferBatchOrders.length > 0">
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-950 text-xs flex items-start gap-2.5 shadow-2xs">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base mt-0.5 shrink-0"></i>
                                <div>
                                    <div class="font-black">Chưa thể Giao xe — Còn <span x-text="pendingTransferBatchOrders.length"></span> đơn chưa bốc xong / chưa bấm "Chuyển kiểm"!</div>
                                    <div class="text-[11px] text-amber-800 mt-0.5">
                                        Các đơn chưa chuyển kiểm: <strong class="font-mono" x-text="pendingTransferBatchOrders.map(o => o.order_code).join(', ')"></strong>.
                                        Vui lòng quay lại tab <strong>Soạn hàng</strong> bấm <strong>Chuyển kiểm</strong> trước khi Giao xe.
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- 2. Batch QC Action Toolbar -->
                        <div class="bg-emerald-50/70 p-2.5 sm:p-3 rounded-xl border border-emerald-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" @click="qcCheckAllBatchItems()"
                                        class="flex-1 sm:flex-none px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-lg shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer text-xs">
                                    <i class="fa-solid fa-check-double text-xs"></i>
                                    <span>Tích kiểm toàn bộ lô</span>
                                </button>

                                <span class="px-2.5 py-1 bg-white border border-emerald-200 rounded-lg text-emerald-900 font-bold flex items-center gap-1.5 shadow-2xs text-[11px] sm:text-xs">
                                    <i class="fa-solid fa-user-check text-emerald-600"></i>
                                    <span class="truncate">Người kiểm tra: <strong x-text="qcInspector"></strong></span>
                                </span>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" @click="uncheckAllBatchItems()"
                                        class="w-full sm:w-auto px-2.5 py-1.5 border border-slate-300 hover:bg-slate-100 text-slate-600 font-bold rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer text-xs">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                    <span>Bỏ tích kiểm cả lô</span>
                                </button>
                            </div>
                        </div>

                        <!-- 3. Batch QC Table for Desktop (Ẩn trên mobile) -->
                        <div class="hidden lg:block overflow-x-auto border border-slate-200 rounded-xl">
                            <table class="w-full text-left text-xs border-collapse min-w-[1050px]">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-600 font-black text-[11px] uppercase tracking-wider border-b border-slate-200">
                                        <th class="py-3 px-3 w-12 text-center">ĐÃ KIỂM</th>
                                        <th class="py-3 px-4">MÃ SKU, TÊN VẬT TƯ & ĐƠN ĐÓNG GÓP</th>
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
                                            <td class="py-3.5 px-4 space-y-1.5">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-mono font-bold text-xs text-sky-800 bg-sky-50 px-2 py-0.5 rounded border border-sky-200 uppercase whitespace-nowrap" x-text="ag.sku"></span>
                                                    <span class="font-black text-slate-900 text-xs sm:text-[13px]" x-text="ag.name"></span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 font-normal flex flex-wrap items-center gap-1.5">
                                                    <span class="font-bold text-slate-600 shrink-0" x-text="'Gồm ' + ag.contributingOrders.length + ' đơn:'"></span>
                                                    <template x-for="c in ag.contributingOrders" :key="c.orderId">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-[11px] whitespace-nowrap shadow-2xs">
                                                            <span class="text-sky-700 font-bold" x-text="c.orderCode"></span>:
                                                            <span class="text-slate-900 font-extrabold" x-text="c.totalReq + ' ' + (ag.unit || '').toLowerCase()"></span>
                                                        </span>
                                                    </template>
                                                </div>
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
                                                    <input type="number"
                                                           :step="isIntegerQty(ag.totalRequired) ? '1' : 'any'"
                                                           :inputmode="isIntegerQty(ag.totalRequired) ? 'numeric' : 'decimal'"
                                                           min="0" :max="ag.totalPicked"
                                                           :value="formatQtyInput(ag.totalChecked, ag.totalRequired)"
                                                           @focus="$event.target.select()"
                                                           @keydown="if (isIntegerQty(ag.totalRequired) && ($event.key === '.' || $event.key === ',')) $event.preventDefault()"
                                                           @change="let cleanVal = formatQtyInput($event.target.value, ag.totalRequired); $event.target.value = cleanVal; updateBatchItemCheckedQty(ag, cleanVal)"
                                                           @keydown.enter="$event.target.blur()"
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

                        <!-- 4. Mobile Aggregated Items Cards for Batch QC (Chuyên dụng cho điện thoại) -->
                        <div class="block lg:hidden space-y-2.5">
                            <template x-for="ag in aggregatedItems" :key="'m_qc_' + ag.sku">
                                <div class="p-3 rounded-2xl border transition-all space-y-2.5 shadow-2xs"
                                     :class="ag.isFullyChecked 
                                         ? 'border-emerald-400 bg-emerald-50/25 ring-1 ring-emerald-400/40' 
                                         : 'border-slate-200/90 bg-white'">
                                    
                                    <!-- Row 1: Checkbox Đã Kiểm + SKU / Mã đơn + Unit + QC status badge (Bỏ hiển thị kho theo yêu cầu) -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2 flex-wrap min-w-0">
                                            <input type="checkbox" :checked="ag.isFullyChecked"
                                                   @change="toggleBatchItemQc(ag, $event.target.checked)"
                                                   class="w-5 h-5 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer shrink-0">
                                            <span class="font-mono font-black text-emerald-800 text-xs sm:text-sm whitespace-nowrap" x-text="ag.sku"></span>
                                            
                                            <!-- Nếu gom từ 1 đơn thì hiển thị luôn mã đơn hàng đầy đủ ngay tại hàng đầu -->
                                            <template x-if="ag.contributingOrders && ag.contributingOrders.length === 1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono font-bold text-[11px] whitespace-nowrap">
                                                    <i class="fa-solid fa-file-invoice text-[9px] text-emerald-600"></i>
                                                    <span x-text="ag.contributingOrders[0].orderCode"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <div class="shrink-0 flex items-center justify-end">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                  :class="ag.qcBadgeClass"
                                                  x-text="ag.qcStatus"></span>
                                        </div>
                                    </div>

                                    <!-- Row 2: Product Name -->
                                    <div class="text-xs sm:text-sm font-black text-slate-900 leading-snug" x-text="ag.name"></div>

                                    <!-- Row 3: Contributing Orders Chips (Hiển thị đầy đủ mã đơn hàng) -->
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                        <div class="text-[10.5px] font-bold text-slate-600 flex items-center justify-between">
                                            <span x-text="'Gồm ' + ag.contributingOrders.length + ' đơn hàng:'"></span>
                                            <span class="text-[10px] text-slate-500 font-normal">
                                                Đã bốc: <strong class="font-mono text-slate-800 font-bold" x-text="ag.totalPicked + '/' + ag.totalRequired"></strong>
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="c in ag.contributingOrders" :key="c.orderId">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-emerald-200 text-xs shadow-2xs whitespace-nowrap">
                                                    <span class="font-mono font-black text-sky-700 tracking-tight" x-text="c.orderCode"></span>
                                                    <span class="text-slate-300">:</span>
                                                    <span class="font-bold text-slate-900" x-text="c.totalReq + ' ' + (ag.unit || '').toLowerCase()"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Row 4: Người đã soạn hàng -->
                                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 text-xs">
                                        <span class="text-[11px] text-slate-400 font-medium shrink-0">Người đã soạn:</span>
                                        <div class="flex flex-wrap items-center gap-1 justify-end">
                                            <template x-for="stName in ag.assignedStaffList" :key="stName">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px] border border-slate-200">
                                                    <i class="fa-solid fa-user text-[8px] text-slate-500"></i>
                                                    <span x-text="stName"></span>
                                                </span>
                                            </template>
                                            <template x-if="!ag.assignedStaffList || ag.assignedStaffList.length === 0">
                                                <span class="text-slate-400 italic text-[10px]">Chưa phân công</span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Row 5: QC Checked Quantity Stepper (+ - Đủ) -->
                                    <div class="flex items-center justify-between gap-2 p-2 rounded-xl bg-emerald-50/50 border border-emerald-100">
                                        <div class="text-xs">
                                            <div class="text-[10px] text-emerald-800 font-bold uppercase">CẦN KIỂM</div>
                                            <div class="font-mono font-black text-slate-900 text-sm" x-text="ag.totalPicked + ' ' + (ag.unit || '')"></div>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <div class="text-right mr-0.5">
                                                <div class="text-[10px] text-emerald-800 font-bold uppercase">THỰC KIỂM</div>
                                            </div>
                                            <!-- Stepper: Trừ / Input / Cộng / Đủ -->
                                            <div class="flex items-center gap-1">
                                                <button type="button" 
                                                        @click="let val = Math.max(0, (Number(ag.totalChecked || 0)) - 1); updateBatchItemCheckedQty(ag, val);"
                                                        class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 font-black text-slate-700 flex items-center justify-center shadow-2xs cursor-pointer text-xs active:scale-95">
                                                    -
                                                </button>
                                                <input type="number"
                                                       :step="isIntegerQty(ag.totalRequired) ? '1' : 'any'"
                                                       :inputmode="isIntegerQty(ag.totalRequired) ? 'numeric' : 'decimal'"
                                                       min="0" :max="ag.totalPicked"
                                                       :value="formatQtyInput(ag.totalChecked, ag.totalRequired)"
                                                       @focus="$event.target.select()"
                                                       @keydown="if (isIntegerQty(ag.totalRequired) && ($event.key === '.' || $event.key === ',')) $event.preventDefault()"
                                                       @change="let cleanVal = formatQtyInput($event.target.value, ag.totalRequired); $event.target.value = cleanVal; updateBatchItemCheckedQty(ag, cleanVal)"
                                                       @keydown.enter="$event.target.blur()"
                                                       class="w-14 p-1 text-center font-mono font-black text-xs rounded-lg border border-slate-300 text-slate-900 bg-white focus:outline-none focus:border-emerald-500 shadow-2xs">
                                                <button type="button" 
                                                        @click="let val = Math.min(ag.totalPicked, (Number(ag.totalChecked || 0) || 0) + 1); updateBatchItemCheckedQty(ag, val);"
                                                        class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 font-black text-slate-700 flex items-center justify-center shadow-2xs cursor-pointer text-xs active:scale-95">
                                                    +
                                                </button>
                                                <button type="button" @click="qcCheckFullBatchItem(ag)"
                                                        class="px-2.5 py-1 font-bold text-[11px] rounded-lg transition-all cursor-pointer shadow-2xs border bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 active:scale-95">
                                                    Đủ
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ghi chú KCS / Tem kiện -->
                                    <div class="pt-0.5">
                                        <input type="text" placeholder="Ghi chú tem kiện, KCS..."
                                               :value="ag.checkNotes || ''"
                                               @blur="updateBatchItemCheckNotes(ag, $event.target.value)"
                                               class="w-full p-2 text-xs rounded-xl border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500 bg-slate-50/70 focus:bg-white placeholder:text-slate-400">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty State when no order selected -->
            <div x-show="fulfillmentMode === 'single' && !activeOrder" class="py-20 text-center text-slate-400 bg-white rounded-2xl border border-slate-200 mt-0">
                <i class="fa-solid fa-clipboard-question text-4xl text-slate-300 mb-3"></i>
                <p class="font-bold text-slate-600 text-sm">Vui lòng chọn một đơn hàng từ danh sách để tiến hành soạn bốc hoặc kiểm tra QC.</p>
                <div class="mt-4 lg:hidden">
                    <button type="button" @click="mobileView = 'list'; $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))" class="px-4 py-2 bg-sky-600 text-white font-bold text-xs rounded-xl shadow-md cursor-pointer">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Về danh sách đơn
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MOBILE STICKY BOTTOM ACTION BAR (Cố định đáy màn hình)    -->
    <!-- ======================================================== -->
    <div x-show="mobileView === 'detail' && activeOrder" 
         x-cloak
         class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 px-3 py-2.5 shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
        <div class="flex items-center gap-2 max-w-lg mx-auto">
            <!-- Nút Về danh sách đơn -->
            <button type="button" @click="mobileView = 'list'; $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))"
                    class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200 shadow-2xs cursor-pointer"
                    title="Quay lại danh sách">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </button>

            <!-- Chế độ 1: Soạn & Bốc hàng -->
            <template x-if="activeTab === 'picking'">
                <div class="flex-1 flex items-center gap-1.5">
                    <button type="button" @click="quickPickAllActiveOrder()"
                            :disabled="isOrderLockedForPicking(activeOrder) || isSavingBatchOrSingle"
                            class="py-2.5 px-2.5 rounded-xl text-xs font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 active:scale-95 border border-sky-300 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-2xs shrink-0 disabled:opacity-40">
                        <i class="fa-solid fa-bolt-lightning text-sky-600 text-xs"></i>
                        <span>Bốc đủ</span>
                    </button>
                    <button type="button" @click="savePickingActiveOrder()"
                            :disabled="isOrderLockedForPicking(activeOrder) || isSavingBatchOrSingle"
                            class="flex-1 py-2.5 px-2.5 rounded-xl text-xs font-black text-white bg-sky-600 hover:bg-sky-700 active:scale-95 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-sm truncate disabled:opacity-40">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Lưu</span>
                    </button>
                    <button type="button" @click="transferPickingToQcActiveOrder()"
                            :disabled="isOrderLockedForPicking(activeOrder) || isSavingBatchOrSingle"
                            class="flex-[1.2] py-2.5 px-2.5 rounded-xl text-xs font-black text-white bg-amber-500 hover:bg-amber-600 active:scale-95 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-md shadow-amber-500/25 truncate disabled:opacity-40">
                        <i class="fa-solid fa-right-to-bracket text-xs"></i>
                        <span>Chuyển kiểm</span>
                    </button>
                </div>
            </template>

            <!-- Chế độ 2: Kiểm Hàng (QC) -->
            <template x-if="activeTab === 'qc'">
                <div class="flex-1 flex items-center gap-1.5">
                    <button type="button" @click="qcCheckAllActiveOrder()"
                            :disabled="isSavingBatchOrSingle || ['Đang vận chuyển', 'Đã giao', 'Giao thành công'].includes(activeOrder.status)"
                            class="py-2.5 px-2.5 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 active:scale-95 border border-emerald-300 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-2xs shrink-0 disabled:opacity-40">
                        <i class="fa-solid fa-square-check text-emerald-600 text-xs"></i>
                        <span>Kiểm đủ</span>
                    </button>
                    <button type="button" @click="saveQcActiveOrder()"
                            :disabled="isSavingBatchOrSingle || ['Đang vận chuyển', 'Đã giao', 'Giao thành công'].includes(activeOrder.status)"
                            class="flex-1 py-2.5 px-2.5 rounded-xl text-xs font-black text-white bg-sky-600 hover:bg-sky-700 active:scale-95 transition-all flex items-center justify-center gap-1 cursor-pointer shadow-sm truncate disabled:opacity-40">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Lưu</span>
                    </button>
                    <button type="button" @click="handoverQcActiveOrderToVehicle()"
                            :disabled="!isOrderTransferredToQc(activeOrder) || isSavingBatchOrSingle || ['Đang vận chuyển', 'Đã giao', 'Giao thành công'].includes(activeOrder.status)"
                            :class="isOrderTransferredToQc(activeOrder) ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 cursor-pointer' : 'bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed'"
                            class="flex-[1.2] py-2.5 px-2.5 rounded-xl text-xs font-black active:scale-95 transition-all flex items-center justify-center gap-1 truncate disabled:opacity-70">
                        <i class="fa-solid" :class="isOrderTransferredToQc(activeOrder) ? 'fa-truck-fast text-xs' : 'fa-lock text-xs'"></i>
                        <span>Giao xe</span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: PHÂN BỔ HÀNG THIẾU CHO CÁC ĐƠN TRÊN XE / LÔ       -->
    <!-- ======================================================== -->
    <div x-show="shortageModal.show" 
         x-cloak
         class="fixed inset-0 z-[99999] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5 overflow-y-auto"
         @keydown.escape.window="if(!shortageModal.saving) shortageModal.show = false">
        
        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-150"
             @click.outside="if(!shortageModal.saving) shortageModal.show = false">
            
            <!-- 1. Modal Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-amber-600 via-rose-600 to-indigo-700 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 shadow-inner">
                        <i class="fa-solid fa-arrows-split-up-and-left text-lg text-white"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-black tracking-tight">Phân Bổ Đơn Hàng Khi Thiếu Hàng</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white border border-white/30">Phân bổ SL bốc</span>
                        </div>
                        <p class="text-xs text-rose-100 mt-0.5">Chia số lượng thực bốc cho từng đơn hàng trong lô. Khi xe giao xong & hoàn thành đơn, số lượng đơn hàng sẽ tự động điều chỉnh theo thực tế.</p>
                    </div>
                </div>
                <button type="button" 
                        @click="shortageModal.show = false"
                        :disabled="shortageModal.saving"
                        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer text-base">
                    &times;
                </button>
            </div>

            <!-- 2. Modal Body (Scrollable) -->
            <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">
                
                <!-- Product Information Card & Overall Numbers -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2.5">
                        <div>
                            <span class="font-mono font-bold text-[11px] text-slate-500 uppercase" x-text="shortageModal.sku"></span>
                            <h4 class="font-black text-slate-900 text-sm" x-text="shortageModal.name"></h4>
                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                <span>Kho xuất: <strong class="text-slate-700" x-text="shortageModal.warehouse"></strong></span>
                                <span>•</span>
                                <span>ĐVT: <strong class="text-slate-700 uppercase" x-text="shortageModal.unit"></strong></span>
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-amber-100 text-amber-900 font-bold text-xs border border-amber-300"
                                  x-text="'Gồm ' + (shortageModal.allocations ? shortageModal.allocations.length : 0) + ' đơn trên xe'"></span>
                        </div>
                    </div>

                    <!-- 3 Stats Inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs">
                            <span class="text-[11px] font-bold text-slate-500 block">Tổng cần bốc cả xe</span>
                            <div class="mt-1 flex items-baseline gap-1">
                                <span class="text-xl font-mono font-black text-slate-900" x-text="shortageModal.totalRequired"></span>
                                <span class="text-xs font-bold text-slate-500 uppercase" x-text="shortageModal.unit"></span>
                            </div>
                        </div>

                        <div class="bg-sky-50/80 p-3 rounded-xl border border-sky-200 shadow-2xs">
                            <span class="text-[11px] font-bold text-sky-800 block">Số thực tế bốc được (Có trên xe):</span>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="number" step="any" min="0" :max="shortageModal.totalRequired"
                                       x-model.number="shortageModal.totalAvailable"
                                       @input="onTotalAvailableChanged($event.target.value)"
                                       class="w-24 px-2.5 py-1 text-lg font-mono font-black text-sky-950 bg-white rounded-lg border border-sky-300 focus:outline-none focus:border-sky-500 shadow-xs">
                                <span class="text-xs font-bold text-sky-700 uppercase" x-text="shortageModal.unit"></span>
                            </div>
                        </div>

                        <div class="bg-rose-50/80 p-3 rounded-xl border border-rose-200 shadow-2xs">
                            <span class="text-[11px] font-bold text-rose-800 block">Tổng số lượng thiếu hụt:</span>
                            <div class="mt-1 flex items-baseline gap-1">
                                <span class="text-xl font-mono font-black text-rose-700"
                                      x-text="Math.max(0, (shortageModal.totalRequired || 0) - (shortageModal.totalAvailable || 0))"></span>
                                <span class="text-xs font-bold text-rose-600 uppercase" x-text="shortageModal.unit"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-indigo-50/70 border border-indigo-200/80 rounded-xl">
                    <div class="flex items-center gap-1.5 font-bold text-indigo-950">
                        <i class="fa-solid fa-wand-magic-sparkles text-indigo-600"></i>
                        <span>Cách phân bổ nhanh:</span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" 
                                @click="allocateByRouteOrder()"
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-route text-[10px]"></i>
                            <span>Ưu tiên đầu tuyến</span>
                        </button>
                        <button type="button" 
                                @click="allocateProportionally()"
                                class="px-3 py-1.5 rounded-lg bg-white hover:bg-indigo-100 text-indigo-700 border border-indigo-300 font-bold text-xs shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-percent text-[10px]"></i>
                            <span>Chia tỷ lệ đều (%)</span>
                        </button>
                        <button type="button" 
                                @click="resetAllocations()"
                                class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 font-bold text-xs shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-rotate-left text-[10px]"></i>
                            <span>Đặt về 0</span>
                        </button>
                    </div>
                </div>

                <!-- Allocations Table -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-2xs">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 font-black text-[11px] uppercase tracking-wider border-b border-slate-200">
                                <th class="py-3 px-3 w-10 text-center">STT</th>
                                <th class="py-3 px-4">ĐƠN HÀNG & KHÁCH HÀNG</th>
                                <th class="py-3 px-3 text-center w-24">CẦN BỐC</th>
                                <th class="py-3 px-4 text-center w-40 bg-sky-50/80">PHÂN BỔ THỰC BỐC</th>
                                <th class="py-3 px-3 text-center w-24 text-rose-700">THIẾU</th>
                                <th class="py-3 px-3 text-center w-44">HỆ THỐNG XỬ LÝ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, idx) in shortageModal.allocations" :key="row.orderId">
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-3 text-center font-bold text-slate-400" x-text="idx + 1"></td>
                                    <td class="py-3 px-4 space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-sky-700 text-xs" x-text="row.orderCode"></span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold" x-text="row.deliveryRoute"></span>
                                        </div>
                                        <div class="font-bold text-slate-900" x-text="row.customerName"></div>
                                        <div class="text-[10px] text-slate-500 truncate max-w-xs" x-text="row.customerAddress"></div>
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono font-black text-slate-800 text-sm" x-text="row.totalReq"></td>
                                    
                                    <!-- Cột phân bổ có nút + - và input -->
                                    <td class="py-3 px-4 text-center bg-sky-50/40">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" 
                                                    @click="row.allocatedQty = Math.max(0, (Number(row.allocatedQty) || 0) - 1)"
                                                    class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 font-black text-slate-700 flex items-center justify-center shadow-2xs cursor-pointer">
                                                -
                                            </button>
                                            <input type="number" step="any" min="0" :max="row.totalReq"
                                                   x-model.number="row.allocatedQty"
                                                   class="w-16 px-1.5 py-1 text-center font-mono font-bold text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:border-sky-500 shadow-2xs">
                                            <button type="button" 
                                                    @click="row.allocatedQty = Math.min(row.totalReq, (Number(row.allocatedQty) || 0) + 1)"
                                                    class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 font-black text-slate-700 flex items-center justify-center shadow-2xs cursor-pointer">
                                                +
                                            </button>
                                            <button type="button" 
                                                    @click="row.allocatedQty = row.totalReq"
                                                    class="px-1.5 py-1 text-[10px] font-bold rounded bg-slate-100 hover:bg-sky-100 text-slate-700 border border-slate-200 cursor-pointer"
                                                    title="Lấy đủ nhu cầu đơn này">
                                                Đủ
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Cột thiếu -->
                                    <td class="py-3 px-3 text-center font-mono font-black text-sm"
                                        :class="row.totalReq - (Number(row.allocatedQty) || 0) > 0 ? 'text-rose-600' : 'text-slate-300'"
                                        x-text="Math.max(0, row.totalReq - (Number(row.allocatedQty) || 0))">
                                    </td>

                                    <!-- Cột trạng thái xử lý tự động -->
                                    <td class="py-3 px-3 text-center">
                                        <template x-if="Number(row.allocatedQty) >= row.totalReq">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                ✓ Giao đủ đợt 1
                                            </span>
                                        </template>
                                        <template x-if="Number(row.allocatedQty) > 0 && Number(row.allocatedQty) < row.totalReq">
                                            <span class="inline-flex flex-col items-center">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300"
                                                      x-text="'Giao ' + formatQtyInput(row.allocatedQty, row.totalReq) + ', thiếu ' + formatQtyInput(row.totalReq - row.allocatedQty, row.totalReq)"></span>
                                            </span>
                                        </template>
                                        <template x-if="Number(row.allocatedQty) <= 0">
                                            <span class="inline-flex flex-col items-center">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Bốc 0 (Thiếu 100%)</span>
                                            </span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Allocation Balance Status -->
                <div class="p-3 rounded-xl border flex items-center justify-between text-xs"
                     :class="shortageAllocatedSum > (shortageModal.totalAvailable || 0) 
                         ? 'bg-rose-50 border-rose-300 text-rose-900' 
                         : (shortageAllocatedSum === (shortageModal.totalAvailable || 0)
                             ? 'bg-emerald-50 border-emerald-300 text-emerald-900'
                             : 'bg-amber-50 border-amber-300 text-amber-900')">
                    <div class="flex items-center gap-2 font-bold">
                        <i :class="shortageAllocatedSum > (shortageModal.totalAvailable || 0) 
                                ? 'fa-solid fa-triangle-exclamation text-rose-600' 
                                : (shortageAllocatedSum === (shortageModal.totalAvailable || 0)
                                    ? 'fa-solid fa-circle-check text-emerald-600'
                                    : 'fa-solid fa-circle-info text-amber-600')"></i>
                        <span>
                            Tổng đã phân bổ: <strong class="font-mono text-sm" x-text="shortageAllocatedSum"></strong> / 
                            <span class="font-mono" x-text="shortageModal.totalAvailable || 0"></span> <span class="uppercase" x-text="shortageModal.unit"></span>
                        </span>
                    </div>

                    <div class="text-[11px] font-bold">
                        <template x-if="shortageAllocatedSum > (shortageModal.totalAvailable || 0)">
                            <span class="text-rose-700">⚠️ Vượt quá <span x-text="shortageAllocatedSum - (shortageModal.totalAvailable || 0)"></span> sản phẩm!</span>
                        </template>
                        <template x-if="shortageAllocatedSum === (shortageModal.totalAvailable || 0)">
                            <span class="text-emerald-700">✓ Khớp hoàn toàn số lượng có thực tế!</span>
                        </template>
                        <template x-if="shortageAllocatedSum < (shortageModal.totalAvailable || 0)">
                            <span class="text-amber-700">Còn dư <span x-text="(shortageModal.totalAvailable || 0) - shortageAllocatedSum"></span> chưa phân bổ</span>
                        </template>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-500 shrink-0 font-bold text-xs">Ghi chú:</span>
                        <input type="text" x-model="shortageModal.note" placeholder="Lý do phân bổ thiếu hàng..."
                               class="flex-1 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

            </div>

            <!-- 3. Modal Footer -->
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                <button type="button" 
                        @click="shortageModal.show = false"
                        :disabled="shortageModal.saving"
                        class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold border border-slate-300 transition-all cursor-pointer">
                    Đóng
                </button>

                <button type="button" 
                        @click="confirmVehicleShortageAllocation()"
                        class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-black shadow-md shadow-sky-600/20 transition-all flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-sm"></i>
                    <span>💾 Xác Nhận Phân Bổ Số Lượng</span>
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL PHẢN ÁNH LỆCH HÀNG TỪ XE XUẤT BẾN (QC ⇄ LÁI XE) -->
    @include('inventory.modals.discrepancies_checker')

</div> <!-- Đóng root container x-data="pickingApp()" -->

<script>
function pickingApp() {
    return {
        activeTab: 'picking', // 'picking' | 'qc'
        fulfillmentMode: 'single', // 'single' | 'batch'
        mobileView: 'list', // 'list' | 'detail' (trên mobile điều hướng giữa danh sách đơn và chi tiết sản phẩm)
        showMobileAdvancedFilters: false,
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
        isSavingBatchOrSingle: false,
        
        // Batch Fulfillment State
        selectedBatchOrderIds: [],
        batchAssignStaff: '',
        shortageModal: {
            show: false,
            agItem: null,
            sku: '',
            name: '',
            unit: '',
            warehouse: '',
            totalRequired: 0,
            totalAvailable: 0,
            markPickedIfReady: false,
            note: 'Phân bổ số lượng thực bốc theo đơn',
            allocations: [],
            saving: false
        },
        selectedAggregatedSkus: [],

        // Discrepancy (Lệch Hàng Từ Xe) State
        discrepanciesList: @json($discrepancies ?? []),
        showDiscrepanciesModal: false,
        discrepancyFilterStatus: 'all',
        discrepancySearch: '',
        activeRespondingId: null,
        isSubmittingResponse: false,
        isPollingDiscrepancies: false,
        discrepancyImageLightbox: { show: false, src: '' },

        get pendingDiscrepanciesCount() {
            return (this.discrepanciesList || []).filter(d => d.status === 'pending').length;
        },

        get filteredDiscrepancies() {
            let list = this.discrepanciesList || [];
            if (this.discrepancyFilterStatus !== 'all') {
                list = list.filter(d => d.status === this.discrepancyFilterStatus);
            }
            if (this.discrepancySearch && this.discrepancySearch.trim()) {
                const q = this.discrepancySearch.trim().toLowerCase();
                list = list.filter(d => 
                    (d.vehicle_plate && d.vehicle_plate.toLowerCase().includes(q)) ||
                    (d.driver_name && d.driver_name.toLowerCase().includes(q)) ||
                    (d.order_code && d.order_code.toLowerCase().includes(q)) ||
                    (d.product_name && d.product_name.toLowerCase().includes(q)) ||
                    (d.sku && d.sku.toLowerCase().includes(q))
                );
            }
            return list;
        },

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
                this.mobileView = 'detail';
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

            // Chuẩn hóa dữ liệu khi nạp vào UI
            (this.orders || []).forEach(o => {
                let allDone = true;
                (o.items || []).forEach(it => {
                    it.quantity = Number(it.quantity || 0);
                    it.gift_quantity = Number(it.gift_quantity || 0);
                    const req = it.quantity + it.gift_quantity;
                    it.picked_qty = this.formatQtyInput(it.picked_qty, req);
                    it.checked_qty = this.formatQtyInput(it.checked_qty, req);
                    it._dirtyPicking = false;
                    it._dirtyQc = false;
                    it.shortage_reason = '';
                    const picked = it.picked_qty;
                    if (req > 0 && picked >= req) {
                        it.item_status = 'Đủ hàng';
                        it.is_picked = true;
                    } else if (req > 0 && picked < req) {
                        allDone = false;
                        it.is_picked = false;
                        it.item_status = picked > 0 ? 'Đang bốc' : 'Chờ bốc';
                    }
                });
                if (o.status === 'Kho báo thiếu hàng' || o.shortage_reported) {
                    if (o.status === 'Kho báo thiếu hàng') {
                        o.status = allDone ? 'Đã bốc xong' : 'Đang bốc hàng';
                    }
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
            if (window.innerWidth < 1024) {
                this.mobileView = 'list';
            }
            setTimeout(() => {
                const list = this.filteredOrders;
                if (list.length > 0) {
                    const current = list.find(o => o.id === this.activeOrderId);
                    const isCurrentDone = current ? (tab === 'qc' ? this.isOrderDaKiem(current) : this.isOrderDaBoc(current)) : true;
                    if (isCurrentDone) {
                        const firstPending = list.find(o => tab === 'qc' ? !this.isOrderDaKiem(o) : !this.isOrderDaBoc(o));
                        this.activeOrderId = (firstPending || list[0]).id;
                    } else if (!list.some(o => o.id === this.activeOrderId)) {
                        this.activeOrderId = list[0].id;
                    }
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
            const completedStatuses = [
                'Đã kiểm hàng', 
                'Sẵn sàng xuất xe', 
                'Đã kiểm hàng (Sẵn sàng xuất xe)', 
                'Kho đã xuất đủ', 
                'Đang vận chuyển', 
                'Đã giao', 
                'Giao thành công', 
                'Hoàn thành'
            ];
            return completedStatuses.includes(o.status) || this.isAllGroupsQcCompleted(o);
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
            return (['Kho đang soạn', 'Đang bốc hàng'].includes(o.status) || 
                   (['Đang kiểm hàng', 'Kiểm hàng'].includes(o.status))) && !this.isAllGroupsPickingCompleted(o);
        },

        isOrderDaBoc(o) {
            if (!o) return false;
            const completedStatuses = [
                'Đã bốc xong', 
                'Đã kiểm hàng', 
                'Sẵn sàng xuất xe', 
                'Đã kiểm hàng (Sẵn sàng xuất xe)', 
                'Kho đã xuất đủ', 
                'Đang vận chuyển', 
                'Đã giao', 
                'Giao thành công', 
                'Hoàn thành'
            ];
            return completedStatuses.includes(o.status) || this.isAllGroupsPickingCompleted(o);
        },

        get totalPendingPickAllDates() {
            return this.orders.filter(o => !!o.picking_date && (
                ['Chốt đơn', 'Đã duyệt', 'Chờ bốc', 'Soạn bốc', 'Soạn hàng', 'Kho đang soạn', 'Đang bốc hàng'].includes(o.status) ||
                ((o.status === 'Đang kiểm hàng' || o.status === 'Kiểm hàng') && !this.isAllGroupsPickingCompleted(o))
            )).length;
        },

        get totalReadyQcAllDates() {
            return this.orders.filter(o => this.isOrderReadyForQc(o) && !this.isOrderDaKiem(o)).length;
        },

        get countPendingPick() {
            const list = this.filterPickingDate
                ? this.orders.filter(o => this.formatDateForInput(o.picking_date) === this.filterPickingDate)
                : this.orders;
            return list.filter(o => !!o.picking_date && (
                ['Chốt đơn', 'Đã duyệt', 'Chờ bốc', 'Soạn bốc', 'Soạn hàng', 'Kho đang soạn', 'Đang bốc hàng'].includes(o.status) ||
                ((o.status === 'Đang kiểm hàng' || o.status === 'Kiểm hàng') && !this.isAllGroupsPickingCompleted(o))
            )).length;
        },

        get countReadyQc() {
            const list = this.filterPickingDate
                ? this.orders.filter(o => this.formatDateForInput(o.picking_date) === this.filterPickingDate)
                : this.orders;
            return list.filter(o => this.isOrderReadyForQc(o) && !this.isOrderDaKiem(o)).length;
        },

        get countFilteredPending() {
            return this.filteredOrders.filter(o => this.activeTab === 'qc' ? !this.isOrderDaKiem(o) : !this.isOrderDaBoc(o)).length;
        },

        get countFilteredCompleted() {
            return this.filteredOrders.filter(o => this.activeTab === 'qc' ? this.isOrderDaKiem(o) : this.isOrderDaBoc(o)).length;
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
            let list = this.orders.filter(o => {
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

            // SẮP XẾP: ĐƠN CHƯA HOÀN THÀNH LÊN TRÊN, ĐÃ XONG ĐẨY XUỐNG DƯỚI (TRÁNH SÓT ĐƠN)
            return list.sort((a, b) => {
                if (this.activeTab === 'qc') {
                    // TAB KIỂM QC:
                    // 1. Đơn đã kiểm xong (isOrderDaKiem = true) ĐẨY XUỐNG DƯỚI CÙNG
                    const aDone = this.isOrderDaKiem(a) ? 1 : 0;
                    const bDone = this.isOrderDaKiem(b) ? 1 : 0;
                    if (aDone !== bDone) {
                        return aDone - bDone; // 0 (chưa kiểm) đứng trước 1 (đã kiểm xong)
                    }

                    // 2. Trong các đơn chưa kiểm xong: ưu tiên đơn đã bốc xong / sẵn sàng kiểm lên trước
                    const aReady = this.isOrderReadyForQc(a) ? 1 : 0;
                    const bReady = this.isOrderReadyForQc(b) ? 1 : 0;
                    if (aReady !== bReady) {
                        return bReady - aReady; // 1 (sẵn sàng kiểm) đứng trước 0 (chưa bốc xong)
                    }
                } else if (this.activeTab === 'picking') {
                    // TAB SOẠN HÀNG:
                    // 1. Đơn đã bốc xong (isOrderDaBoc = true) ĐẨY XUỐNG DƯỚI CÙNG
                    const aDone = this.isOrderDaBoc(a) ? 1 : 0;
                    const bDone = this.isOrderDaBoc(b) ? 1 : 0;
                    if (aDone !== bDone) {
                        return aDone - bDone; // 0 (chưa bốc) đứng trước 1 (đã bốc xong)
                    }
                }

                // Cùng trạng thái: Đơn có ID lớn hơn (mới hơn) lên trước
                return Number(b.id || 0) - Number(a.id || 0);
            });
        },

        get activeOrder() {
            return this.orders.find(o => o.id === this.activeOrderId) || null;
        },

        selectOrder(ord) {
            this.activeOrderId = ord.id;
            this.mobileView = 'detail';
            this.$nextTick(() => {
                if (window.innerWidth < 1024) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
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
                    return Number(it.picked_qty || 0) >= req && req > 0;
                }).length;
                g.isAllPicked = g.items.length > 0 && g.pickedCount >= g.items.length;
                g.checkedCount = g.items.filter(it => it.is_checked || Number(it.checked_qty) > 0).length;
                g.isAllChecked = g.items.length > 0 && g.checkedCount >= g.items.length;
            });

            return groups;
        },

        quickPickCategory(catGroup) {
            if (!this.activeOrder || !catGroup || !catGroup.items) return;
            const itemsToPick = catGroup.items.filter(it => this.canUserPickItem(it));
            if (itemsToPick.length === 0) {
                this.showToast('Bạn không được phân công hoặc không phụ trách loại sản phẩm này!', 'error');
                return;
            }
            itemsToPick.forEach(it => {
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                it.picked_qty = this.formatQtyInput(reqTotal, reqTotal);
                it.is_picked = true;
                it.item_status = 'Đủ hàng';
                it.shortage_reason = '';
                it._dirtyPicking = true;
                if (!it.assigned_staff || it.assigned_staff === 'null') {
                    it.assigned_staff = this.currentUserName;
                    if (!Array.isArray(it.assigned_staff_list)) it.assigned_staff_list = [];
                    if (!it.assigned_staff_list.includes(this.currentUserName)) {
                        it.assigned_staff_list.push(this.currentUserName);
                    }
                }
            });
            this.showToast('Đã điền đủ ' + itemsToPick.length + ' món loại "' + catGroup.name + '" trên màn hình. Hãy bấm Lưu hoặc Chuyển kiểm!', 'info');
        },

        qcCheckAllCategory(catGroup) {
            if (!this.activeOrder || !catGroup || !catGroup.items) return;
            const transferred = this.isOrderTransferredToQc(this.activeOrder);
            const targetItems = transferred
                ? catGroup.items
                : catGroup.items.filter(it => it.is_picked || Number(it.picked_qty || 0) > 0);
            if (targetItems.length === 0) {
                this.showToast('Loại sản phẩm này chưa có mặt hàng nào được bốc để kiểm!', 'warning');
                return;
            }
            targetItems.forEach(it => {
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                it.is_checked = true;
                it.checked_qty = this.formatQtyInput(Number(it.picked_qty || 0), reqTotal);
                it.checked_by = this.qcInspector;
                it._dirtyQc = true;
            });
            this.showToast('Đã tích kiểm loại "' + catGroup.name + '" trên màn hình. Hãy bấm Lưu hoặc Giao xe!', 'info');
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
            if (!this.activeOrder || !this.activeOrder.items) return [];
            let items = this.activeOrder.items;
            if (this.pickerFilter === 'all') return items;
            if (this.pickerFilter === 'unassigned') return items.filter(it => !it.assigned_staff || it.assigned_staff === 'null');
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

        get shortageAllocatedSum() {
            return (this.shortageModal.allocations || []).reduce((sum, a) => sum + (Number(a.allocatedQty) || 0), 0);
        },

        get batchOrders() {
            if (!this.selectedBatchOrderIds || this.selectedBatchOrderIds.length === 0) {
                return [];
            }
            const idSet = new Set(this.selectedBatchOrderIds.map(Number));
            return this.orders.filter(o => idSet.has(Number(o.id)));
        },

        get aggregatedItems() {
            const ordersToAggregate = this.batchOrders;
            
            const map = {};
            ordersToAggregate.forEach(o => {
                (o.items || []).forEach(it => {
                    const key = it.sku || it.name;
                    if (!map[key]) {
                        map[key] = {
                            sku: it.sku || '',
                            name: it.name || '',
                            unit: it.unit || 'CÂY',
                            warehouse: o.warehouse || 'Kho Tổng',
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
                    if (it.assigned_staff && it.assigned_staff !== 'null' && String(it.assigned_staff).trim() !== '') {
                        staffAssigned.push(String(it.assigned_staff).trim());
                    }
                    if (Array.isArray(it.assigned_staff_list)) {
                        it.assigned_staff_list.forEach(s => { 
                            if (s && s !== 'null' && String(s).trim() !== '' && !staffAssigned.includes(String(s).trim())) {
                                staffAssigned.push(String(s).trim()); 
                            }
                        });
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
                const allContribsTransferred = ag.contributingOrders.length > 0 && ag.contributingOrders.every(c => this.isOrderTransferredToQc(c.orderRef));
                if (ag.totalPicked >= ag.totalRequired && ag.totalRequired > 0) {
                    ag.status = 'Đã bốc xong';
                    ag.badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-300';
                    ag.isFullyPicked = true;
                } else if (allContribsTransferred) {
                    ag.status = 'Đã chuyển kiểm';
                    ag.badgeClass = 'bg-amber-50 text-amber-800 border-amber-300';
                    ag.isFullyPicked = false;
                } else if (ag.totalPicked > 0) {
                    ag.status = 'Đang bốc';
                    ag.badgeClass = 'bg-sky-50 text-sky-700 border-sky-300';
                    ag.isFullyPicked = false;
                } else {
                    ag.status = 'Chờ bốc';
                    ag.badgeClass = 'bg-amber-50 text-amber-800 border-amber-300';
                    ag.isFullyPicked = false;
                }

                // QC Batch calculations (dựa trên SL đã bốc thực tế, KHÔNG ghi đè bằng SL đặt khi thiếu)
                ag.totalChecked = ag.contributingOrders.reduce((sum, c) => {
                    const chk = (c.itemRef.checked_qty !== undefined && c.itemRef.checked_qty !== null)
                        ? Number(c.itemRef.checked_qty)
                        : (c.itemRef.is_checked ? Number(c.itemRef.picked_qty || 0) : 0);
                    return sum + chk;
                }, 0);
                ag.totalChecked = this.formatQtyInput(ag.totalChecked, ag.totalRequired);
                ag.totalPicked = this.formatQtyInput(ag.totalPicked, ag.totalRequired);
                ag.totalRequired = this.formatQtyInput(ag.totalRequired, ag.totalRequired);
                ag.isFullyChecked = ag.contributingOrders.length > 0 && ag.contributingOrders.every(c => Boolean(c.itemRef.is_checked));
                if (ag.isFullyChecked || (ag.totalChecked >= ag.totalPicked && ag.totalPicked > 0)) {
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

        pickFullBatchItem(ag) {
            if (!ag || !ag.contributingOrders) return;
            for (const c of ag.contributingOrders) {
                const req = c.totalReq;
                c.itemRef.picked_qty = this.formatQtyInput(req, req);
                c.itemRef.is_picked = true;
                c.itemRef.item_status = 'Đủ hàng';
                c.itemRef.shortage_reason = '';
                c.itemRef._dirtyPicking = true;
                if (!c.itemRef.assigned_staff || c.itemRef.assigned_staff === 'null') {
                    c.itemRef.assigned_staff = this.currentUserName;
                    if (!Array.isArray(c.itemRef.assigned_staff_list)) c.itemRef.assigned_staff_list = [];
                    if (!c.itemRef.assigned_staff_list.includes(this.currentUserName)) {
                        c.itemRef.assigned_staff_list.push(this.currentUserName);
                    }
                }
            }
            if (!ag.assignedStaffList.includes(this.currentUserName)) {
                ag.assignedStaffList.push(this.currentUserName);
            }
            this.showToast('Đã điền đủ ' + ag.sku + ' (' + ag.totalRequired + ' ' + (ag.unit || '') + ') trên màn hình. Hãy bấm Lưu hoặc Chuyển kiểm!', 'info');
        },

        updateBatchItemPickedQty(ag, val) {
            if (!ag || !ag.contributingOrders) return;
            let targetQty = Math.max(0, this.formatQtyInput(val, ag.totalRequired));
            if (targetQty > ag.totalRequired && ag.totalRequired > 0) {
                targetQty = ag.totalRequired;
                this.showToast('Không được nhập quá tổng số lượng cần bốc (' + ag.totalRequired + ')!', 'warning');
            }

            let remaining = targetQty;
            for (const c of ag.contributingOrders) {
                const req = c.totalReq;
                const give = Math.min(remaining, req);
                remaining = Math.max(0, remaining - give);

                c.itemRef.picked_qty = this.formatQtyInput(give, req);
                c.itemRef._dirtyPicking = true;
                c.itemRef.shortage_reason = '';
                if (give >= req && req > 0) {
                    c.itemRef.is_picked = true;
                    c.itemRef.item_status = 'Đủ hàng';
                } else if (give > 0) {
                    c.itemRef.is_picked = false;
                    c.itemRef.item_status = 'Đang bốc';
                } else {
                    c.itemRef.is_picked = false;
                    c.itemRef.item_status = 'Chờ bốc';
                }

                if (give > 0 && (!c.itemRef.assigned_staff || c.itemRef.assigned_staff === 'null')) {
                    c.itemRef.assigned_staff = this.currentUserName;
                    if (!Array.isArray(c.itemRef.assigned_staff_list)) c.itemRef.assigned_staff_list = [];
                    if (!c.itemRef.assigned_staff_list.includes(this.currentUserName)) {
                        c.itemRef.assigned_staff_list.push(this.currentUserName);
                    }
                }
            }
            if (targetQty > 0 && !ag.assignedStaffList.includes(this.currentUserName)) {
                ag.assignedStaffList.push(this.currentUserName);
            }
        },

        openVehicleShortageModal(ag) {
            if (!ag || !ag.contributingOrders) return;

            const allocations = ag.contributingOrders.map((c, index) => {
                const o = c.orderRef || {};
                const it = c.itemRef || {};
                return {
                    orderId: c.orderId,
                    orderCode: c.orderCode,
                    customerName: c.customerName || o.customer_name || 'Khách hàng',
                    customerAddress: o.customer_address || '',
                    deliveryRoute: o.assigned_delivery_route || o.assigned_vehicle_plate || ('Đơn #' + (index + 1)),
                    totalReq: this.formatQtyInput(c.totalReq, c.totalReq),
                    allocatedQty: this.formatQtyInput(it.picked_qty, c.totalReq),
                    itemRef: it,
                    orderRef: o
                };
            });

            const totalAvail = this.formatQtyInput(ag.totalPicked, ag.totalRequired);
            const currentAllocSum = allocations.reduce((s, a) => s + (Number(a.allocatedQty) || 0), 0);

            this.shortageModal = {
                show: true,
                agItem: ag,
                sku: ag.sku,
                name: ag.name,
                unit: ag.unit || 'cây',
                warehouse: ag.warehouse,
                totalRequired: ag.totalRequired,
                totalAvailable: totalAvail > 0 ? totalAvail : 0,
                markPickedIfReady: false,
                note: 'Phân bổ số lượng thực bốc cho các đơn',
                allocations: allocations,
                saving: false
            };

            // Nếu chưa phân bổ gì nhưng có totalAvailable > 0, tự động phân bổ theo thứ tự giao
            if (currentAllocSum === 0 && totalAvail > 0) {
                this.allocateByRouteOrder();
            }
        },

        allocateByRouteOrder() {
            let remain = Math.max(0, Number(this.shortageModal.totalAvailable) || 0);
            this.shortageModal.allocations.forEach(row => {
                const take = Math.min(remain, row.totalReq);
                row.allocatedQty = take;
                remain -= take;
            });
        },

        allocateProportionally() {
            const totalAvail = Math.max(0, Number(this.shortageModal.totalAvailable) || 0);
            const totalReq = this.shortageModal.totalRequired || 1;
            let remain = totalAvail;
            const len = this.shortageModal.allocations.length;
            this.shortageModal.allocations.forEach((row, idx) => {
                if (idx === len - 1) {
                    row.allocatedQty = Math.min(row.totalReq, remain);
                } else {
                    const share = Math.min(row.totalReq, Math.floor((row.totalReq / totalReq) * totalAvail));
                    row.allocatedQty = share;
                    remain -= share;
                }
            });
        },

        resetAllocations() {
            this.shortageModal.allocations.forEach(row => {
                row.allocatedQty = 0;
            });
        },

        onTotalAvailableChanged(val) {
            this.shortageModal.totalAvailable = Math.max(0, Number(val) || 0);
            this.allocateByRouteOrder();
        },

        confirmVehicleShortageAllocation() {
            const sum = (this.shortageModal.allocations || []).reduce((s, a) => s + (Number(a.allocatedQty) || 0), 0);
            const avail = Number(this.shortageModal.totalAvailable) || 0;
            if (sum > avail) {
                if (!confirm(`CẢNH BÁO: Tổng số lượng đã phân bổ (${sum}) lớn hơn số thực tế có (${avail})!\nBạn có chắc chắn muốn tiếp tục áp dụng không?`)) {
                    return;
                }
            }

            for (const a of (this.shortageModal.allocations || [])) {
                if (!a.itemRef) continue;
                const req = Number(a.totalReq || 0);
                const alloc = Math.max(0, Math.min(req, this.formatQtyInput(a.allocatedQty, req)));
                a.itemRef.picked_qty = alloc;
                a.itemRef._dirtyPicking = true;
                if (alloc >= req && req > 0) {
                    a.itemRef.is_picked = true;
                    a.itemRef.item_status = 'Đủ hàng';
                    a.itemRef.shortage_reason = '';
                } else if (alloc > 0) {
                    a.itemRef.is_picked = false;
                    a.itemRef.item_status = 'Đang bốc';
                    a.itemRef.shortage_reason = '';
                } else {
                    a.itemRef.is_picked = false;
                    a.itemRef.item_status = 'Chờ bốc';
                    a.itemRef.shortage_reason = '';
                }
                if (alloc > 0 && (!a.itemRef.assigned_staff || a.itemRef.assigned_staff === 'null')) {
                    a.itemRef.assigned_staff = this.currentUserName;
                    if (!Array.isArray(a.itemRef.assigned_staff_list)) a.itemRef.assigned_staff_list = [];
                    if (!a.itemRef.assigned_staff_list.includes(this.currentUserName)) {
                        a.itemRef.assigned_staff_list.push(this.currentUserName);
                    }
                }
            }
            this.shortageModal.show = false;
            this.showToast('✓ Đã phân bổ ' + this.shortageModal.sku + ' cho ' + this.shortageModal.allocations.length + ' đơn! Hãy bấm Lưu hoặc Chuyển kiểm.', 'success');
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

        isOrderTransferredToQc(ord) {
            if (!ord) return false;
            return [
                'Đã bốc xong', 'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng', 'Đã kiểm',
                'Sẵn sàng xuất xe', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Kho đã xuất đủ',
                'Đang vận chuyển', 'Đang giao hàng', 'Đã giao', 'Giao thành công'
            ].includes(ord.status);
        },

        hasUnsavedPicking(ord) {
            return Boolean(ord && Array.isArray(ord.items) && ord.items.some(i => i._dirtyPicking));
        },

        hasUnsavedQc(ord) {
            return Boolean(ord && Array.isArray(ord.items) && ord.items.some(i => i._dirtyQc));
        },

        get hasAnyUnsavedBatchPicking() {
            return (this.batchOrders || []).some(o => this.hasUnsavedPicking(o));
        },

        get hasAnyUnsavedBatchQc() {
            return (this.batchOrders || []).some(o => this.hasUnsavedQc(o));
        },

        get pendingTransferBatchOrders() {
            return (this.batchOrders || []).filter(o => !this.isOrderTransferredToQc(o));
        },

        get canHandoverBatchQcToVehicle() {
            return (this.batchOrders || []).length > 0 && this.pendingTransferBatchOrders.length === 0;
        },

        async commitPickingOrQc(mode, action, targetOrders) {
            if (!targetOrders || targetOrders.length === 0 || this.isSavingBatchOrSingle) return null;
            this.isSavingBatchOrSingle = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const ordersPayload = targetOrders.map(ord => ({
                    orderId: ord.id,
                    items: (ord.items || []).map(it => ({
                        id: it.id,
                        sku: it.sku,
                        picked_qty: Number(it.picked_qty || 0),
                        is_picked: Boolean(it.is_picked),
                        item_status: it.item_status || 'Chờ bốc',
                        shortage_reason: it.shortage_reason || '',
                        assigned_staff: it.assigned_staff || null,
                        assigned_staff_list: Array.isArray(it.assigned_staff_list) ? it.assigned_staff_list : [],
                        checked_qty: Number(it.checked_qty || 0),
                        is_checked: Boolean(it.is_checked),
                        check_notes: it.check_notes || '',
                        checked_qty_explicit: it._dirtyQc ? true : false
                    }))
                }));

                const res = await fetch('/api/inventory/picking/save-batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        mode: mode,
                        action: action,
                        performerName: this.currentUserName,
                        inspectorName: this.qcInspector,
                        adminOverride: Boolean(this.isUserAdmin && this.adminOverrideMode),
                        orders: ordersPayload
                    })
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    this.showToast(data.message || 'Không thể lưu dữ liệu!', 'error');
                    return data;
                }

                // Đồng bộ kết quả từ server vào state local và xóa cờ dirty
                if (Array.isArray(data.updatedOrders)) {
                    const updatedMap = new Map(data.updatedOrders.map(u => [Number(u.id), u]));
                    this.orders = this.orders.map(ord => {
                        const u = updatedMap.get(Number(ord.id));
                        if (!u) return ord;
                        const uItemsById = new Map((u.items || []).map(ui => [String(ui.id), ui]));
                        const mergedItems = (ord.items || []).map(it => {
                            const ui = uItemsById.get(String(it.id));
                            if (!ui) return it;
                            const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                            return {
                                ...it,
                                picked_qty: this.formatQtyInput(ui.picked_qty, req),
                                is_picked: Boolean(ui.is_picked),
                                item_status: ui.item_status,
                                shortage_reason: ui.shortage_reason || '',
                                assigned_staff: ui.assigned_staff,
                                assigned_staff_list: ui.assigned_staff_list || [],
                                checked_qty: this.formatQtyInput(ui.checked_qty, req),
                                is_checked: Boolean(ui.is_checked),
                                check_notes: ui.check_notes || '',
                                checked_by: ui.checked_by,
                                checked_at: ui.checked_at,
                                _dirtyPicking: mode === 'picking' ? false : Boolean(it._dirtyPicking),
                                _dirtyQc: mode === 'qc' ? false : Boolean(it._dirtyQc),
                            };
                        });
                        return {
                            ...ord,
                            status: u.status,
                            shortage_reported: Boolean(u.shortage_reported),
                            stock_deducted: u.stock_deducted !== undefined ? Boolean(u.stock_deducted) : Boolean(ord.stock_deducted),
                            groups_progress: u.groups_progress || ord.groups_progress,
                            items: mergedItems
                        };
                    });
                }

                this.showToast(data.message || 'Đã lưu thành công!', 'success');

                if (action === 'transfer_to_qc') {
                    setTimeout(() => {
                        this.activeTab = 'qc';
                    }, 500);
                } else if (action === 'handover_to_vehicle') {
                    if (typeof BroadcastChannel !== 'undefined') {
                        try {
                            const bc = new BroadcastChannel('erp_order_sync');
                            bc.postMessage({ type: 'QC_ALL_CHECKED', completedCount: targetOrders.length });
                        } catch(e) {}
                    }
                    setTimeout(() => {
                        const nextPending = this.filteredOrders.find(o => !this.isOrderDaKiem(o));
                        if (nextPending) {
                            this.activeOrderId = nextPending.id;
                            this.showToast('Đã bàn giao xe. Chuyển sang đơn chờ kiểm: ' + nextPending.order_code, 'info');
                        }
                    }, 250);
                }

                return data;
            } catch (e) {
                console.error('commitPickingOrQc error:', e);
                this.showToast('Lỗi kết nối khi lưu: ' + e.message, 'error');
                return null;
            } finally {
                this.isSavingBatchOrSingle = false;
            }
        },

        quickPickAllBatchOrders() {
            const ordersToFill = this.batchOrders.length > 0 ? this.batchOrders : this.filteredOrders;
            if (ordersToFill.length === 0) {
                this.showToast('Chưa chọn đơn hàng nào trong lô!', 'warning');
                return;
            }
            ordersToFill.forEach(ord => {
                (ord.items || []).forEach(it => {
                    const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                    it.picked_qty = this.formatQtyInput(reqTotal, reqTotal);
                    it.is_picked = true;
                    it.item_status = 'Đủ hàng';
                    it.shortage_reason = '';
                    it._dirtyPicking = true;
                    if (!it.assigned_staff || it.assigned_staff === 'null') {
                        it.assigned_staff = this.currentUserName;
                        if (!Array.isArray(it.assigned_staff_list)) it.assigned_staff_list = [];
                        if (!it.assigned_staff_list.includes(this.currentUserName)) {
                            it.assigned_staff_list.push(this.currentUserName);
                        }
                    }
                });
            });
            this.showToast('Đã điền đủ SL trên màn hình cho ' + ordersToFill.length + ' đơn. Hãy bấm Lưu hoặc Chuyển kiểm!', 'info');
        },

        async saveBatchPickingOrders() {
            const ordersToSave = this.batchOrders.length > 0 ? this.batchOrders : this.filteredOrders;
            if (ordersToSave.length === 0) {
                this.showToast('Không có đơn hàng nào được chọn!', 'warning');
                return;
            }
            await this.commitPickingOrQc('picking', 'save', ordersToSave);
        },

        async completeAllBatchOrders() {
            const ordersToComplete = this.batchOrders.length > 0
                ? this.batchOrders
                : this.filteredOrders;

            if (ordersToComplete.length === 0) {
                this.showToast('Không có đơn hàng nào được chọn!', 'warning');
                return;
            }

            let shortSkus = 0;
            (this.aggregatedItems || []).forEach(ag => {
                if (ag.totalPicked < ag.totalRequired) shortSkus++;
            });

            const msg = shortSkus > 0
                ? 'Đơn gộp đang có ' + shortSkus + ' mặt hàng bốc thiếu so với đặt hàng.\n\nBạn có chắc chắn muốn lưu số lượng đã phân bổ hiện tại và CHUYỂN KIỂM (vẫn được Giao xe nếu thiếu)?'
                : 'Xác nhận lưu số lượng đã bốc và CHUYỂN KIỂM cho toàn bộ ' + ordersToComplete.length + ' đơn đã chọn?';

            if (!confirm(msg)) {
                return;
            }

            await this.commitPickingOrQc('picking', 'transfer_to_qc', ordersToComplete);
        },

        // ========================================================
        // TAB 2: BATCH QC HANDLERS (KIỂM HÀNG GỘP NHIỀU ĐƠN)
        // ========================================================
        qcCheckAllBatchItems() {
            if (!this.aggregatedItems || this.aggregatedItems.length === 0) return;
            for (const ag of this.aggregatedItems) {
                for (const c of ag.contributingOrders) {
                    const picked = Number(c.itemRef.picked_qty || 0);
                    c.itemRef.is_checked = true;
                    c.itemRef.checked_qty = this.formatQtyInput(picked, c.totalReq);
                    c.itemRef.checked_by = this.qcInspector;
                    c.itemRef.checked_at = new Date().toLocaleString('vi-VN');
                    c.itemRef._dirtyQc = true;
                }
            }
            this.showToast('Đã tích kiểm theo SL đã bốc cho cả lô trên màn hình. Hãy bấm Lưu hoặc Giao xe!', 'info');
        },

        uncheckAllBatchItems() {
            if (!this.aggregatedItems || this.aggregatedItems.length === 0) return;
            for (const ag of this.aggregatedItems) {
                for (const c of ag.contributingOrders) {
                    c.itemRef.is_checked = false;
                    c.itemRef.checked_qty = 0;
                    c.itemRef._dirtyQc = true;
                }
            }
            this.showToast('Đã bỏ tích kiểm cả lô trên màn hình (chưa lưu).', 'info');
        },

        toggleBatchItemQc(ag, checked) {
            if (!ag || !ag.contributingOrders) return;
            for (const c of ag.contributingOrders) {
                c.itemRef.is_checked = checked;
                c.itemRef._dirtyQc = true;
                if (checked) {
                    const picked = Number(c.itemRef.picked_qty || 0);
                    c.itemRef.checked_qty = this.formatQtyInput(picked, c.totalReq);
                    c.itemRef.checked_by = this.qcInspector;
                    c.itemRef.checked_at = new Date().toLocaleString('vi-VN');
                } else {
                    c.itemRef.checked_qty = 0;
                }
            }
        },

        qcCheckFullBatchItem(ag) {
            if (!ag || !ag.contributingOrders) return;
            for (const c of ag.contributingOrders) {
                const picked = Number(c.itemRef.picked_qty || 0);
                c.itemRef.is_checked = true;
                c.itemRef.checked_qty = this.formatQtyInput(picked, c.totalReq);
                c.itemRef.checked_by = this.qcInspector;
                c.itemRef.checked_at = new Date().toLocaleString('vi-VN');
                c.itemRef._dirtyQc = true;
            }
            this.showToast('Đã điền đủ SL kiểm theo SL đã bốc cho ' + ag.sku + '. Hãy bấm Lưu hoặc Giao xe!', 'info');
        },

        updateBatchItemCheckedQty(ag, val) {
            if (!ag || !ag.contributingOrders) return;
            const maxCap = Number(ag.totalPicked || 0);
            let targetQty = Math.max(0, this.formatQtyInput(val, ag.totalRequired));
            if (targetQty > maxCap) {
                targetQty = maxCap;
                this.showToast('Không được kiểm quá số lượng đã bốc (' + maxCap + ')!', 'warning');
            }

            let remain = targetQty;
            for (let i = 0; i < ag.contributingOrders.length; i++) {
                const c = ag.contributingOrders[i];
                const pickedCap = Number(c.itemRef.picked_qty || 0);
                let allocated = 0;
                if (i === ag.contributingOrders.length - 1) {
                    allocated = Math.min(pickedCap, remain);
                } else {
                    allocated = Math.min(pickedCap, remain);
                    remain = Math.max(0, remain - allocated);
                }

                c.itemRef.checked_qty = this.formatQtyInput(allocated, c.totalReq);
                c.itemRef.is_checked = (pickedCap > 0 && allocated >= pickedCap) || (pickedCap === 0 && allocated === 0 && Boolean(c.itemRef.is_checked));
                c.itemRef.checked_by = this.qcInspector;
                c.itemRef.checked_at = new Date().toLocaleString('vi-VN');
                c.itemRef._dirtyQc = true;
            }
        },

        updateBatchItemCheckNotes(ag, val) {
            if (!ag || !ag.contributingOrders) return;
            for (const c of ag.contributingOrders) {
                c.itemRef.check_notes = val;
                c.itemRef._dirtyQc = true;
            }
        },

        async saveBatchQcOrders() {
            const ordersToSave = this.batchOrders;
            if (!ordersToSave || ordersToSave.length === 0) {
                this.showToast('Chưa chọn đơn hàng nào trong danh sách gộp!', 'warning');
                return;
            }
            await this.commitPickingOrQc('qc', 'save', ordersToSave);
        },

        async completeAllBatchQcOrders() {
            const ordersToComplete = this.batchOrders;
            if (!ordersToComplete || ordersToComplete.length === 0) {
                this.showToast('Chưa chọn đơn hàng nào trong danh sách gộp!', 'warning');
                return;
            }

            if (this.pendingTransferBatchOrders.length > 0) {
                this.showToast('Không thể Giao xe! Còn đơn chưa bấm Chuyển kiểm: ' + this.pendingTransferBatchOrders.map(o => o.order_code).join(', '), 'error');
                return;
            }

            if (!confirm('Xác nhận Kiểm xong & GIAO XE cho cả ' + ordersToComplete.length + ' đơn gộp này?')) {
                return;
            }

            await this.commitPickingOrQc('qc', 'handover_to_vehicle', ordersToComplete);
        },

        // Format Helpers
        isIntegerQty(reqQty) {
            const num = Number(reqQty || 0);
            return Math.abs(num - Math.round(num)) < 0.0001;
        },

        formatQtyInput(val, reqQty = null) {
            let num = Number(val || 0);
            if (isNaN(num)) num = 0;
            if (reqQty !== null && this.isIntegerQty(reqQty)) {
                return Math.round(num);
            }
            return Number(num.toFixed(2));
        },

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
            const first = ord.items.find(i => i.assigned_staff && i.assigned_staff !== 'null' && String(i.assigned_staff).trim() !== '');
            return (first && first.assigned_staff) ? first.assigned_staff : 'Chưa phân công';
        },

        getAssignedItemsCount(ord) {
            if (!ord.items) return 0;
            return ord.items.filter(i => i.assigned_staff && i.assigned_staff !== 'null' && String(i.assigned_staff).trim() !== '').length;
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
            if (it.item_status === 'Đủ hàng' || (Number(it.picked_qty) >= req && req > 0)) {
                return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            }
            if (Number(it.picked_qty || 0) > 0 || it.item_status === 'Đang bốc') {
                return 'bg-sky-50 text-sky-700 border-sky-200';
            }
            return 'bg-amber-50 text-amber-800 border-amber-200';
        },

        isOrderLockedForPicking(ord) {
            return ['Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Đang vận chuyển', 'Đã giao'].includes(ord.status);
        },

        isItemStaffLocked(it) {
            if (!it) return false;
            if (it._forceUnlock) return false;

            // BẮT BUỘC: Phải có tên người bốc hợp lệ mới khóa hiển thị badge người bốc. Nếu chưa có người bốc thì tuyệt đối không khóa!
            const staffName = (typeof it.assigned_staff === 'string' && it.assigned_staff.trim() !== 'null' && it.assigned_staff.trim() !== '') 
                ? it.assigned_staff.trim() 
                : null;
            if (!staffName) return false;

            // 1. Khi thay đổi số lượng (_justModified), LUÔN MỞ PHÂN CÔNG để phân công lại!
            if (it._justModified) return false;

            // 2. Nếu đơn đã bị khóa (đã kiểm xong / xuất bến) thì khóa người bốc
            if (this.isOrderLockedForPicking(this.activeOrder)) return true;

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

            // Lấy tên người được phân công hợp lệ (bỏ qua chuỗi null / rỗng)
            const staffName = (typeof it.assigned_staff === 'string' && it.assigned_staff.trim() !== 'null' && it.assigned_staff.trim() !== '') 
                ? it.assigned_staff.trim() 
                : null;

            const validList = Array.isArray(it.assigned_staff_list) 
                ? it.assigned_staff_list.filter(s => s && String(s).trim() !== '' && String(s).trim() !== 'null').map(s => String(s).trim())
                : [];

            // Nếu chưa phân công cho ai -> Tự động cho phép người bấm Đủ / nhập số bốc (hệ thống sẽ tự nhận cho người này)
            if (!staffName && validList.length === 0) {
                return true;
            }

            // Chỉ người được phân công vào mặt hàng này mới được nhập số lượng bốc
            const curName = (this.currentUserName || '').trim().toLowerCase();
            if (staffName && staffName.toLowerCase() === curName) {
                return true;
            }
            if (validList.some(st => st.toLowerCase() === curName)) {
                return true;
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
                    // Chưa phân công -> Tự động ăn theo người thao tác
                    if (!item.assigned_staff || item.assigned_staff === 'null') {
                        item.assigned_staff = this.currentUserName;
                        if (!Array.isArray(item.assigned_staff_list)) item.assigned_staff_list = [];
                        if (!item.assigned_staff_list.includes(this.currentUserName)) {
                            item.assigned_staff_list.push(this.currentUserName);
                        }
                    }
                    payload.assignedStaff = item.assigned_staff;
                    payload.assignedStaffList = item.assigned_staff_list;
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

        incrementPickedQty(it) {
            if (!this.canUserPickItem(it)) {
                this.showToast('Bạn không được phân công soạn mặt hàng này!', 'error');
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            let val = Number(it.picked_qty || 0);
            if (val < reqTotal || reqTotal === 0) {
                it.picked_qty = this.formatQtyInput(val + 1, reqTotal);
                this.validateAndSavePickedQty(it);
            }
        },

        decrementPickedQty(it) {
            if (!this.canUserPickItem(it)) {
                this.showToast('Bạn không được phân công soạn mặt hàng này!', 'error');
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            let val = Number(it.picked_qty || 0);
            if (val > 0) {
                it.picked_qty = Math.max(0, this.formatQtyInput(val - 1, reqTotal));
                this.validateAndSavePickedQty(it);
            }
        },

        pickFullItem(it) {
            if (!this.canUserPickItem(it)) {
                this.showToast('Mặt hàng này đã được giao cho ' + it.assigned_staff + '!', 'error');
                return;
            }
            if (!it.assigned_staff || it.assigned_staff === 'null') {
                it.assigned_staff = this.currentUserName;
                if (!Array.isArray(it.assigned_staff_list)) it.assigned_staff_list = [];
                if (!it.assigned_staff_list.includes(this.currentUserName)) {
                    it.assigned_staff_list.push(this.currentUserName);
                }
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            it.picked_qty = this.formatQtyInput(reqTotal, reqTotal);
            it.is_picked = true;
            it.item_status = 'Đủ hàng';
            it.shortage_reason = '';
            it._dirtyPicking = true;
        },

        handlePickCheckChange(it) {
            if (!this.canUserPickItem(it)) {
                it.is_picked = !it.is_picked;
                this.showToast('Bạn không được phân công soạn mặt hàng này!', 'error');
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            const picked = Number(it.picked_qty || 0);
            it.shortage_reason = '';
            if (it.is_picked) {
                if (picked <= 0) {
                    it.picked_qty = this.formatQtyInput(reqTotal, reqTotal);
                    it.item_status = 'Đủ hàng';
                } else if (picked < reqTotal) {
                    it.item_status = 'Đang bốc';
                } else {
                    it.item_status = 'Đủ hàng';
                }
                if (!it.assigned_staff || it.assigned_staff === 'null') {
                    it.assigned_staff = this.currentUserName;
                    if (!Array.isArray(it.assigned_staff_list)) it.assigned_staff_list = [];
                    if (!it.assigned_staff_list.includes(this.currentUserName)) {
                        it.assigned_staff_list.push(this.currentUserName);
                    }
                }
            } else {
                if (picked > 0 && picked < reqTotal) {
                    it.item_status = 'Đang bốc';
                } else if (picked >= reqTotal && reqTotal > 0) {
                    it.item_status = 'Đủ hàng';
                } else {
                    it.item_status = 'Chờ bốc';
                }
            }
            it._dirtyPicking = true;
        },

        getOrderSupervisor(ord) {
            if (!ord) return 'Quản trị viên';
            return ord.supervisor || ord.order_creator || (ord.dealer && ord.dealer.staff_in_charge) || 'Quản lý';
        },

        validateAndSavePickedQty(it) {
            if (!this.canUserPickItem(it)) {
                this.showToast('Bạn không được phân công soạn mặt hàng này!', 'error');
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            
            // Chuẩn hóa giá trị nhập vào (loại bỏ số thập phân nếu SL cần là số nguyên)
            let val = this.formatQtyInput(it.picked_qty, reqTotal);
            it.picked_qty = val;

            if (val > reqTotal && reqTotal > 0) {
                val = reqTotal;
                it.picked_qty = reqTotal;
                this.showToast('Không được nhập quá số lượng cần bốc (' + reqTotal + ' ' + (it.unit || '') + ')!', 'warning');
            } else if (val < 0) {
                val = 0;
                it.picked_qty = 0;
            }

            // Nếu mặt hàng chưa phân công và người dùng nhập số lượng > 0 -> Tự động nhận việc cho người này
            if (val > 0 && (!it.assigned_staff || it.assigned_staff === 'null')) {
                it.assigned_staff = this.currentUserName;
                if (!Array.isArray(it.assigned_staff_list)) it.assigned_staff_list = [];
                if (!it.assigned_staff_list.includes(this.currentUserName)) {
                    it.assigned_staff_list.push(this.currentUserName);
                }
            }

            this.saveItemPickedQty(it);
        },

        saveItemPickedQty(it) {
            if (!this.canUserPickItem(it)) {
                return;
            }
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            let picked = this.formatQtyInput(it.picked_qty, reqTotal);

            if (picked > reqTotal && reqTotal > 0) {
                picked = reqTotal;
            } else if (picked < 0) {
                picked = 0;
            }
            it.picked_qty = picked;

            // Đảm bảo nếu chưa phân công mà bốc > 0 thì tự nhận
            if (picked > 0 && (!it.assigned_staff || it.assigned_staff === 'null')) {
                it.assigned_staff = this.currentUserName;
                if (!Array.isArray(it.assigned_staff_list)) it.assigned_staff_list = [];
                if (!it.assigned_staff_list.includes(this.currentUserName)) {
                    it.assigned_staff_list.push(this.currentUserName);
                }
            }

            it.shortage_reason = '';
            if (picked >= reqTotal && reqTotal > 0) {
                it.is_picked = true;
                it.item_status = 'Đủ hàng';
            } else if (picked > 0 && picked < reqTotal) {
                it.is_picked = false;
                it.item_status = 'Đang bốc';
            } else {
                it.is_picked = false;
                it.item_status = 'Chờ bốc';
            }

            // Chỉ cập nhật tạm trên giao diện, không tự lưu lên server cho đến khi bấm [Lưu] hoặc [Chuyển kiểm]
            it._dirtyPicking = true;
        },

        isAllGroupsPickingCompleted(ord) {
            if (!ord) return false;
            if (ord.groups_progress && ord.groups_progress.length > 0) {
                return ord.groups_progress.every(g => g.is_picking_completed);
            }
            if (!ord.items || ord.items.length === 0) return false;
            return ord.items.every(it => {
                const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                return Number(it.picked_qty || 0) >= req && req > 0;
            });
        },

        isAllGroupsQcCompleted(ord) {
            if (!ord) return false;
            if (ord.groups_progress && ord.groups_progress.length > 0) {
                return ord.groups_progress.every(g => g.is_qc_completed);
            }
            if (!ord.items || ord.items.length === 0) return false;
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

        quickPickAllActiveOrder() {
            if (!this.activeOrder) return;
            const itemsToPick = this.activeOrder.items.filter(it => this.canUserPickItem(it));
            if (itemsToPick.length === 0) {
                this.showToast('Bạn không được phân công hoặc không phụ trách mặt hàng nào trong đơn này!', 'error');
                return;
            }
            itemsToPick.forEach(it => {
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                it.picked_qty = this.formatQtyInput(reqTotal, reqTotal);
                it.is_picked = true;
                it.item_status = 'Đủ hàng';
                it.shortage_reason = '';
                it._dirtyPicking = true;
                if (!it.assigned_staff || it.assigned_staff === 'null') {
                    it.assigned_staff = this.currentUserName;
                    if (!Array.isArray(it.assigned_staff_list)) it.assigned_staff_list = [];
                    if (!it.assigned_staff_list.includes(this.currentUserName)) {
                        it.assigned_staff_list.push(this.currentUserName);
                    }
                }
            });
            this.showToast('Đã điền đủ ' + itemsToPick.length + ' mặt hàng trên màn hình. Hãy bấm Lưu hoặc Chuyển kiểm!', 'info');
        },

        async savePickingActiveOrder() {
            if (!this.activeOrder) return;
            await this.commitPickingOrQc('picking', 'save', [this.activeOrder]);
        },

        async transferPickingToQcActiveOrder() {
            if (!this.activeOrder) return;
            const shortItems = (this.activeOrder.items || []).filter(it => {
                const req = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                return Number(it.picked_qty || 0) < req;
            });
            if (shortItems.length > 0) {
                const ok = confirm('Đơn hàng có ' + shortItems.length + ' mặt hàng bốc thiếu so với đơn đặt.\n\nXác nhận vẫn lưu số lượng thực bốc hiện tại và CHUYỂN KIỂM? (Sau khi chuyển, bên Kiểm vẫn được phép Giao xe).');
                if (!ok) return;
            }
            await this.commitPickingOrQc('picking', 'transfer_to_qc', [this.activeOrder]);
        },

        // Tab 2 QC Functions (local-only until bấm Lưu hoặc Giao xe)
        handleQcCheckChange(it) {
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            const pickedCap = Number(it.picked_qty || 0);
            if (it.is_checked) {
                it.checked_qty = this.formatQtyInput(pickedCap, reqTotal);
            } else {
                it.checked_qty = 0;
            }
            it._dirtyQc = true;
        },

        incrementCheckedQty(it) {
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            const maxVal = this.isOrderTransferredToQc(this.activeOrder)
                ? Number(it.picked_qty || 0)
                : (Number(it.picked_qty) > 0 ? Number(it.picked_qty) : reqTotal);
            let val = Number(it.checked_qty || 0);
            if (val < maxVal) {
                it.checked_qty = Math.min(maxVal, this.formatQtyInput(val + 1, reqTotal));
                it.is_checked = (it.checked_qty >= maxVal && maxVal > 0);
                it._dirtyQc = true;
            }
        },

        decrementCheckedQty(it) {
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            let val = Number(it.checked_qty || 0);
            if (val > 0) {
                it.checked_qty = Math.max(0, this.formatQtyInput(val - 1, reqTotal));
                const maxVal = Number(it.picked_qty || 0);
                it.is_checked = (it.checked_qty >= maxVal && maxVal > 0);
                it._dirtyQc = true;
            }
        },

        checkFullItemQc(it) {
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            const pickedCap = this.isOrderTransferredToQc(this.activeOrder)
                ? Number(it.picked_qty || 0)
                : (Number(it.picked_qty) > 0 ? Number(it.picked_qty) : reqTotal);
            it.checked_qty = this.formatQtyInput(pickedCap, reqTotal);
            it.is_checked = true;
            it._dirtyQc = true;
        },

        saveItemQcQty(it) {
            const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
            const maxVal = this.isOrderTransferredToQc(this.activeOrder)
                ? Number(it.picked_qty || 0)
                : (Number(it.picked_qty) > 0 ? Number(it.picked_qty) : reqTotal);
            let val = this.formatQtyInput(it.checked_qty, reqTotal);
            if (val < 0) val = 0;
            if (val > maxVal) {
                val = maxVal;
                this.showToast('Không được kiểm quá số lượng đã bốc (' + maxVal + ' ' + (it.unit || '') + ')!', 'warning');
            }
            it.checked_qty = val;
            it.is_checked = (val >= maxVal && maxVal > 0) || (maxVal === 0 && Boolean(it.is_checked));
            it._dirtyQc = true;
        },

        saveItemCheckNotes(it) {
            it._dirtyQc = true;
        },

        qcCheckAllActiveOrder() {
            if (!this.activeOrder) return;
            this.activeOrder.items.forEach(it => {
                const reqTotal = Number(it.quantity || 0) + Number(it.gift_quantity || 0);
                const pickedCap = Number(it.picked_qty || 0);
                it.is_checked = true;
                it.checked_qty = this.formatQtyInput(pickedCap, reqTotal);
                it._dirtyQc = true;
            });
            this.showToast('Đã tích kiểm theo SL đã bốc trên màn hình. Hãy bấm Lưu hoặc Giao xe!', 'info');
        },

        async saveQcActiveOrder() {
            if (!this.activeOrder) return;
            await this.commitPickingOrQc('qc', 'save', [this.activeOrder]);
        },

        async handoverQcActiveOrderToVehicle() {
            if (!this.activeOrder) return;
            if (!this.isOrderTransferredToQc(this.activeOrder)) {
                this.showToast('Không thể Giao xe! Đơn hàng chưa bốc xong hoặc bên Bốc chưa bấm "Chuyển kiểm".', 'error');
                return;
            }
            await this.commitPickingOrQc('qc', 'handover_to_vehicle', [this.activeOrder]);
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
                this.mobileView = 'detail';
                this.$nextTick(() => {
                    if (window.innerWidth < 1024) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                });
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
                        warehouse: extra.warehouse || 'Kho Tổng',
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
            } else if (ev.eventType === 'ORDER_STATUS_CHANGED' || ev.eventType === 'ORDER_PROGRESS_SAVED') {
                const orderId = Number(ev.orderId);
                const extra = ev.extraData || {};
                const newStatus = extra.newStatus || ev.orderStatus;
                const evItems = Array.isArray(extra.items) ? extra.items : null;
                this.orders = this.orders.map(o => {
                    if (Number(o.id) === orderId || o.order_code === extra.orderCode) {
                        let mergedItems = o.items || [];
                        if (evItems) {
                            const evMap = new Map(evItems.map(ei => [String(ei.id), ei]));
                            mergedItems = mergedItems.map(cur => {
                                const ei = evMap.get(String(cur.id));
                                if (!ei) return cur;
                                const req = Number(cur.quantity || 0) + Number(cur.gift_quantity || 0);
                                return {
                                    ...cur,
                                    picked_qty: cur._dirtyPicking ? cur.picked_qty : this.formatQtyInput(ei.picked_qty, req),
                                    is_picked: cur._dirtyPicking ? cur.is_picked : Boolean(ei.is_picked),
                                    item_status: cur._dirtyPicking ? cur.item_status : ei.item_status,
                                    shortage_reason: cur._dirtyPicking ? cur.shortage_reason : (ei.shortage_reason || ''),
                                    assigned_staff: ei.assigned_staff,
                                    assigned_staff_list: ei.assigned_staff_list || [],
                                    checked_qty: cur._dirtyQc ? cur.checked_qty : this.formatQtyInput(ei.checked_qty, req),
                                    is_checked: cur._dirtyQc ? cur.is_checked : Boolean(ei.is_checked),
                                    check_notes: cur._dirtyQc ? cur.check_notes : (ei.check_notes || ''),
                                    checked_by: ei.checked_by,
                                    checked_at: ei.checked_at,
                                };
                            });
                        }
                        return {
                            ...o,
                            status: newStatus || o.status,
                            shortage_reported: ev.shortageReported !== undefined ? Boolean(ev.shortageReported) : o.shortage_reported,
                            stock_deducted: extra.stockDeducted !== undefined ? Boolean(extra.stockDeducted) : Boolean(o.stock_deducted),
                            groups_progress: extra.groupProgress?.groups || o.groups_progress,
                            items: mergedItems
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
                                const req = Number(updated.quantity || 0) + Number(updated.gift_quantity || 0);
                                if (ev.pickedQty !== undefined && ev.pickedQty !== null) {
                                    updated.picked_qty = this.formatQtyInput(ev.pickedQty, req);
                                    updated.is_picked = updated.picked_qty >= req && req > 0;
                                }
                                if (ev.itemStatus) updated.item_status = ev.itemStatus;
                                if (ev.assignedStaff !== undefined) updated.assigned_staff = ev.assignedStaff;
                                if (ev.assignedStaffList !== undefined) updated.assigned_staff_list = ev.assignedStaffList;
                                if (ev.shortageReason !== undefined) updated.shortage_reason = ev.shortageReason;
                                if (ev.extraData?.isChecked !== undefined) updated.is_checked = ev.extraData.isChecked;
                                if (ev.extraData?.checkedQty !== undefined) updated.checked_qty = this.formatQtyInput(ev.extraData.checkedQty, req);
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
            } else if (ev.eventType === 'DELIVERY_DISCREPANCY_REPORTED') {
                const extra = ev.extraData || {};
                this.playAlertSound();

                // Kích hoạt banner thông báo nổi góc trên màn hình cho Người bốc hàng
                this.realtimeAlert = {
                    isDiscrepancy: true,
                    orderId: ev.orderId,
                    orderCode: extra.order_code || ('Đơn ' + ev.orderId),
                    vehiclePlate: extra.vehicle_plate || '',
                    time: extra.created_at || new Date().toLocaleTimeString('vi-VN'),
                    performer: 'Lái xe ' + (ev.performerName || 'giao hàng'),
                    supervisor: 'Người bốc phụ trách: ' + (extra.checker_name || 'Người bốc'),
                    summary: `${extra.product_name || 'Sản phẩm'} (Lệch: ${extra.diff_qty || ''})` + (extra.driver_note ? ' - ' + extra.driver_note : '')
                };
                if (this.alertDismissTimer) clearTimeout(this.alertDismissTimer);
                this.alertDismissTimer = setTimeout(() => {
                    this.realtimeAlert = null;
                }, 15000);

                const msg = '🚨 LÁI XE BÁO LỆCH HÀNG: Xe ' + (extra.vehicle_plate || '') + ' - Đơn ' + (extra.order_code || '') + ': ' + (extra.product_name || '') + ' (lệch ' + (extra.diff_qty || '') + ') - Báo người bốc: ' + (extra.checker_name || '');
                this.showToast(msg, 'error');
                this.fetchDiscrepancies();
                if (window.Notification && Notification.permission === 'granted') {
                    try {
                        new Notification('ERP Đại Sơn - Lái xe báo lệch hàng tới Người bốc (Xe ' + (extra.vehicle_plate || '') + ')', {
                            body: 'Đơn: ' + (extra.order_code || '') + ' | ' + (extra.product_name || '') + ' (Lệch ' + (extra.diff_qty || '') + ')\n' + (extra.driver_note || ''),
                            icon: '/favicon.ico'
                        });
                    } catch(e) {}
                }
            } else if (ev.eventType === 'DELIVERY_DISCREPANCY_RESPONDED') {
                this.fetchDiscrepancies();
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

        // ─── PHẢN ÁNH LỆCH HÀNG TỪ XE (QC & LÁI XE) ───────────────────
        openDiscrepanciesModal() {
            this.showDiscrepanciesModal = true;
            this.fetchDiscrepancies();
        },

        openImageLightbox(src) {
            this.discrepancyImageLightbox = { show: true, src: src };
        },

        formatDiscrepancyTime(dtStr) {
            if (!dtStr) return '';
            try {
                const d = new Date(dtStr);
                if (isNaN(d.getTime())) return dtStr;
                const hh = String(d.getHours()).padStart(2, '0');
                const mm = String(d.getMinutes()).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                const MM = String(d.getMonth() + 1).padStart(2, '0');
                const yyyy = d.getFullYear();
                return `${hh}:${mm} ${dd}/${MM}/${yyyy}`;
            } catch (_) {
                return dtStr;
            }
        },

        getDiscrepancyTypeLabel(type) {
            switch(type) {
                case 'shortage': return 'Thiếu hàng khi giao';
                case 'excess': return 'Thừa hàng trên xe';
                case 'damaged': return 'Hàng bị hư hỏng/vỡ';
                case 'wrong_item': return 'Sai quy cách/mã';
                default: return type || 'Lệch hàng';
            }
        },

        focusOrderInPicking(orderCode) {
            if (!orderCode) return;
            this.showDiscrepanciesModal = false;
            this.filterPickingDate = '';
            this.filterOrderType = 'all';
            this.searchQuery = orderCode;
            const ord = this.orders.find(o => o.order_code === orderCode);
            if (ord) {
                this.activeOrderId = ord.id;
                this.activeTab = 'picking';
                this.mobileView = 'detail';
            }
            this.showToast('Đang hiển thị đơn hàng: ' + orderCode);
        },

        async fetchDiscrepancies() {
            this.isPollingDiscrepancies = true;
            try {
                const res = await fetch('/api/delivery/discrepancies');
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && Array.isArray(data.discrepancies)) {
                        this.discrepanciesList = data.discrepancies;
                    }
                }
            } catch (e) {
                console.error('Lỗi khi tải danh sách lệch hàng:', e);
            } finally {
                this.isPollingDiscrepancies = false;
            }
        },

        async sendCheckerResponse(id, status, feedback, resolution, onStart, onEnd) {
            if (!feedback || !feedback.trim()) {
                alert('Vui lòng nhập nội dung giải thích / phản hồi cho lái xe!');
                return;
            }
            if (onStart) onStart();
            this.isSubmittingResponse = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/api/delivery/discrepancies/${id}/respond`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        status: status,
                        checker_feedback: feedback.trim(),
                        resolution_note: resolution ? resolution.trim() : null,
                        checker_feedback_by: this.currentUserName
                    })
                });

                const data = await res.json();
                if (data && data.success) {
                    this.showToast('✅ Đã gửi phản hồi thành công tới lái xe!');
                    if (data.discrepancy) {
                        this.discrepanciesList = this.discrepanciesList.map(item => 
                            item.id === id ? data.discrepancy : item
                        );
                    } else {
                        await this.fetchDiscrepancies();
                    }
                    if (typeof BroadcastChannel !== 'undefined') {
                        try {
                            const dc = new BroadcastChannel('erp_discrepancy_sync');
                            dc.postMessage({ type: 'DISCREPANCY_RESPONDED', id: id });

                            const oc = new BroadcastChannel('erp_order_sync');
                            oc.postMessage({ type: 'DELIVERY_DISCREPANCY_RESPONDED', id: id });
                        } catch (_) {}
                    }
                } else {
                    alert('Lỗi: ' + (data?.message || 'Không thể gửi phản hồi'));
                }
            } catch (err) {
                console.error('Lỗi gửi phản hồi cho lái xe:', err);
                alert('Lỗi kết nối: ' + err.message);
            } finally {
                this.isSubmittingResponse = false;
                if (onEnd) onEnd();
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
