@extends('layouts.app')

@section('title', 'Điều Phối Xe & Quản Lý Đội Xe Vận Tải | ERP Đại Sơn')

@section('content')
<div x-data="dispatchApp()" x-init="initApp()" class="space-y-4 max-w-[1920px] mx-auto pb-16">

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

    <!-- 1. Top Header -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-600/25">
                <i class="fa-solid fa-truck-ramp-box text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Điều Phối Xe</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                        Thời gian thực
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Theo dõi trạng thái đội xe vận tải & Cài đặt lộ tuyến hoạt động
                </p>
            </div>
        </div>

        <!-- Top Right Actions & Nav -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
            <!-- Tab 1: Điều phối chuyến xe -->
            <button type="button" @click="currentMainTab = 'dispatch'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 border shadow-2xs cursor-pointer"
                    :class="currentMainTab === 'dispatch' ? 'bg-purple-600 text-white border-purple-600 shadow-purple-600/20' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-200'">
                <i class="fa-solid fa-layer-group"></i>
                <span>Điều phối xe</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black ml-0.5"
                      :class="currentMainTab === 'dispatch' ? 'bg-purple-800 text-purple-100' : 'bg-purple-100 text-purple-800'"
                      x-text="unassignedOrders.length"></span>
            </button>

            <!-- Tab 2: Hàng lẻ lưu động trên xe (Bán dạo) -->
            <button type="button" @click="currentMainTab = 'vansale'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 border shadow-2xs cursor-pointer"
                    :class="currentMainTab === 'vansale' ? 'bg-purple-600 text-white border-purple-600 shadow-purple-600/20' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-200'">
                <i class="fa-solid fa-boxes-packing"></i>
                <span>Hàng lẻ xe (Bán dạo)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black ml-0.5"
                      :class="currentMainTab === 'vansale' ? 'bg-purple-800 text-purple-100' : 'bg-emerald-100 text-emerald-800'"
                      x-text="vanDispatches.length"></span>
            </button>

            <!-- Link: Danh sách xe -->
            <a href="/vehicles"
               class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition-all flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-truck text-slate-400"></i>
                <span>Danh sách xe</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-slate-100 text-slate-700 font-bold" x-text="vehicles.length"></span>
            </a>

            <!-- Link: Lộ tuyến -->
            <a href="/delivery-routes"
               class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition-all flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-route text-slate-400"></i>
                <span>Lộ tuyến</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-slate-100 text-slate-700 font-bold" x-text="routes.length"></span>
            </a>

            <!-- Link: Giao diện Lái Xe Đi Giao -->
            <a href="/driver-portal" target="_blank"
               class="px-4 py-2 rounded-xl text-xs font-black text-white bg-slate-900 hover:bg-slate-800 transition-all flex items-center gap-2 shadow-sm active:scale-95">
                <i class="fa-solid fa-mobile-screen text-emerald-400"></i>
                <span>Cổng Lái Xe</span>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- KHỐI 1: ĐIỀU PHỐI CHUYẾN XE THEO LỘ TUYẾN (DISPATCH)                       -->
    <!-- ========================================================================= -->
    <div x-show="currentMainTab === 'dispatch'" class="space-y-4">

    <!-- 2. Smart Process Banner -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 font-bold text-sm">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div class="text-xs">
                <div class="flex items-center gap-2 font-black text-slate-900">
                    <span>Quy trình điều phối thông minh theo lộ tuyến</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        3 Bước Đơn Giản
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-slate-600 mt-1 text-[11px] font-medium">
                    <span><strong class="text-purple-700 font-bold">1</strong> Chọn xe đích ở cột trái (hoặc để tự động)</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-slate-300"></i>
                    <span><strong class="text-purple-700 font-bold">2</strong> Xem đơn hàng gợi ý theo lộ tuyến ở cột phải</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-slate-300"></i>
                    <span><strong class="text-purple-700 font-bold">3</strong> Bấm <strong>+ Xếp vào xe</strong>, tick chọn hàng loạt hoặc kéo thả</span>
                </div>
            </div>
        </div>

        <!-- Banner Right Actions -->
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Nút giải phóng đơn hoàn thành ra khỏi xe nếu có -->
            <button type="button" @click="releaseCompletedFromSelectedVehicle()"
                    x-show="hasCompletedOrdersOnActiveVehicle"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 transition-colors flex items-center gap-1.5 cursor-pointer shadow-2xs">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span x-text="'Cho ' + completedOrdersCountOnActiveVehicle + ' đơn hoàn thành ra khỏi xe'"></span>
            </button>

            <!-- Đặt lại -->
            <button type="button" @click="resetFilters()"
                    class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-rotate-left text-slate-500"></i>
                <span>Đặt lại</span>
            </button>
        </div>
    </div>

    <!-- 3. Main Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ========================================== -->
        <!-- LEFT COLUMN: Đội Xe Theo Lộ Tuyến (5 cols) -->
        <!-- ========================================== -->
        <div class="lg:col-span-5 xl:col-span-5 space-y-3">
            
            <!-- Left Header Card -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center justify-between">
                <div>
                    <h2 class="font-black text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-truck-moving text-purple-600"></i>
                        <span>Đội Xe Theo Lộ Tuyến</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-slate-100 text-slate-700" x-text="vehicles.length + ' xe'"></span>
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Click vào xe để chọn làm xe nhận đơn hàng</p>
                </div>

                <div class="text-right">
                    <span class="text-[9px] font-bold tracking-wider uppercase text-purple-600 block">ĐANG CHỌN XE</span>
                    <span class="font-mono font-black text-xs text-purple-900 bg-purple-100 px-2 py-0.5 rounded-md border border-purple-200 inline-block mt-0.5"
                          x-text="selectedVehiclePlate"></span>
                </div>
            </div>

            <!-- Vehicles List -->
            <div class="space-y-3">
                <template x-for="v in vehicles" :key="v.id">
                    <div @click="selectVehicle(v.plate_number)"
                         class="p-4 rounded-2xl border transition-all cursor-pointer relative space-y-3"
                         :class="selectedVehiclePlate === v.plate_number 
                            ? 'border-purple-400 bg-purple-50/20 ring-2 ring-purple-500/25 shadow-xs' 
                            : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/80 shadow-2xs'">

                        <!-- Row 1: Plate & Vehicle Name & Status Badges -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-slate-900 text-white font-mono font-black text-xs rounded-lg shadow-2xs tracking-wider"
                                      x-text="v.plate_number"></span>
                                <span class="font-bold text-slate-800 text-xs" x-text="v.vehicle_type"></span>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <span x-show="selectedVehiclePlate === v.plate_number"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                    <i class="fa-solid fa-check text-[9px]"></i>
                                    <span>Đang chọn</span>
                                </span>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                      :class="getVehicleBadgeClass(v.dispatch_status || v.status)"
                                      x-text="v.dispatch_status || v.status || 'Rảnh'"></span>
                            </div>
                        </div>

                        <!-- Row 2: Route Pill -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50/80 text-purple-900 border border-purple-200/80 rounded-xl text-xs font-bold">
                            <i class="fa-solid fa-route text-purple-600 text-xs"></i>
                            <span x-text="getVehicleRouteText(v)"></span>
                        </div>

                        <!-- Row 3: Driver & Copilot -->
                        <div class="text-xs text-slate-600 flex items-center justify-between pt-1 border-t border-slate-100">
                            <div>
                                <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                                    <i class="fa-solid fa-user text-purple-600 text-[10px]"></i>
                                    <span>Lái xe: <strong class="font-bold" x-text="v.driver?.name || v.driverName || 'Chưa phân công'"></strong></span>
                                    <a :href="'{{ url('/cat_vehicles?action=assignment&plate=') }}' + v.plate_number" class="ml-1 text-[10px] text-indigo-600 hover:text-indigo-800 font-bold hover:underline" title="Đổi phân công lái xe / phụ xe">
                                        <i class="fa-solid fa-user-pen"></i>
                                    </a>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    <span>Phụ xe: <span class="font-medium text-slate-700" x-text="v.copilot_name || v.copilotName || 'Không có'"></span></span>
                                </div>
                            </div>

                            <template x-if="v.driver?.phone || v.driverPhone">
                                <div class="text-right text-[11px] text-slate-500 font-mono font-medium flex items-center gap-1">
                                    <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                                    <span x-text="v.driver?.phone || v.driverPhone"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Row 4: Stats Loaded & Total Value -->
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                            <span class="text-slate-600 font-semibold">
                                Đã xếp: <strong class="font-mono font-black text-purple-700" x-text="getAssignedOrdersCount(v.plate_number) + ' đơn hàng'"></strong>
                            </span>
                            <span class="text-slate-600 font-semibold">
                                Tổng giá trị: <strong class="font-mono font-black text-slate-900" x-text="formatCurrency(getAssignedOrdersTotal(v.plate_number))"></strong>
                            </span>
                        </div>

                        <!-- Row 5: Notification if completed orders on vehicle -->
                        <template x-if="getCompletedOrdersCount(v.plate_number) > 0">
                            <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5 text-emerald-800 font-bold">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span x-text="'Có ' + getCompletedOrdersCount(v.plate_number) + ' đơn đã hoàn thành'"></span>
                                </div>
                                <button type="button" @click.stop="releaseCompletedFromVehicle(v.plate_number)"
                                        class="px-2.5 py-1 bg-emerald-700 hover:bg-emerald-800 text-white font-black text-[11px] rounded-lg transition-all cursor-pointer shadow-2xs">
                                    Cho ra khỏi xe &rarr;
                                </button>
                            </div>
                        </template>

                        <!-- Row 6: Dropdown toggle for loaded orders -->
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                            <!-- Toggle view loaded orders -->
                            <button type="button" @click.stop="toggleVehicleOrders(v.plate_number)"
                                    class="text-xs font-bold text-slate-700 hover:text-purple-700 flex items-center gap-1.5 cursor-pointer py-1">
                                <i class="fa-solid fa-box text-purple-600 text-xs"></i>
                                <span x-text="getAssignedOrdersCount(v.plate_number) + ' đơn hàng'"></span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                    <i class="fa-solid fa-check text-[9px]"></i>
                                    <span>Đã kiểm đủ 100%</span>
                                </span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
                                   :class="expandedVehiclePlates.includes(v.plate_number) ? 'rotate-180' : ''"></i>
                            </button>
                        </div>

                        <!-- Collapsible Orders Table inside Vehicle Card -->
                        <div x-show="expandedVehiclePlates.includes(v.plate_number)" x-collapse class="pt-2 space-y-2">
                            <template x-for="ord in getAssignedOrdersList(v.plate_number)" :key="ord.id">
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-black text-sky-700 text-xs" x-text="ord.order_code"></span>
                                            <span class="font-bold text-slate-800 truncate max-w-[180px]" x-text="ord.customer_name"></span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5" x-text="formatCurrency(ord.total_amount)"></div>
                                    </div>

                                    <button type="button" @click.stop="unassignOrderFromVehicle(ord)"
                                            class="px-2 py-1 bg-white hover:bg-rose-50 text-rose-600 border border-slate-200 hover:border-rose-300 rounded-lg font-bold text-[10px] transition-colors cursor-pointer"
                                            title="Rút đơn hàng khỏi xe này về hàng chờ">
                                        <i class="fa-solid fa-xmark mr-0.5"></i> Rút đơn
                                    </button>
                                </div>
                            </template>
                            <div x-show="getAssignedOrdersCount(v.plate_number) === 0" class="py-3 text-center text-slate-400 text-xs italic">
                                Chưa có đơn hàng nào xếp trên xe này.
                            </div>
                        </div>

                    </div>
                </template>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: Đơn Hàng Chờ Xếp Xe (7 cols) -->
        <!-- ========================================== -->
        <div class="lg:col-span-7 xl:col-span-7 space-y-3">

            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs space-y-3.5">
                
                <!-- Right Header: Title & Target Vehicle -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-slate-100 gap-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-box text-purple-600 text-base"></i>
                        <h2 class="font-black text-slate-900 text-sm">Đơn Hàng Chờ Xếp Xe</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300"
                              x-text="unassignedOrders.length + ' đơn chưa xếp'"></span>
                    </div>

                    <div class="text-xs font-semibold text-slate-600">
                        <span>Mục tiêu xếp: </span>
                        <strong class="font-mono font-black text-purple-700" x-text="selectedVehiclePlate + ' (' + getVehicleTypeName(selectedVehiclePlate) + ')'"></strong>
                    </div>
                </div>

                <!-- Row 1: Search, Route Filter & Delivery Date Filter -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                    <!-- Search Input (5 cols) -->
                    <div class="sm:col-span-5 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                        <input type="text" x-model="searchQuery"
                               placeholder="Tìm theo mã đơn, đại lý, địa chỉ..."
                               class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 font-medium text-slate-800 bg-slate-50/70 focus:bg-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all placeholder:text-slate-400">
                    </div>

                    <!-- Route Filter (4 cols) -->
                    <div class="sm:col-span-4">
                        <select x-model="selectedRouteFilter"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 font-bold text-slate-700 bg-slate-50/70 focus:bg-white focus:outline-none focus:border-purple-500 cursor-pointer">
                            <option value="">Tất cả lộ tuyến gợi ý</option>
                            @foreach($routes as $r)
                                <option value="{{ $r->name }}">{{ $r->code }} - {{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Delivery Date Filter (3 cols) -->
                    <div class="sm:col-span-3 relative">
                        <i class="fa-regular fa-calendar absolute left-2.5 top-2.5 text-purple-600 text-xs pointer-events-none"></i>
                        <input type="date" x-model="deliveryDateFilter"
                               title="Lọc theo ngày giao hàng"
                               class="w-full pl-7 pr-6 py-2 text-xs rounded-xl border border-slate-200 font-bold text-slate-700 bg-slate-50/70 focus:bg-white focus:outline-none focus:border-purple-500 cursor-pointer">
                        <button type="button" x-show="deliveryDateFilter" @click="deliveryDateFilter = ''"
                                class="absolute right-2 top-2 text-slate-400 hover:text-rose-500 text-xs cursor-pointer"
                                title="Xóa lọc ngày">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Row 2: Checkbox Match Route, Quick Date Pills & Select All -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs pt-1 gap-2">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                            <input type="checkbox" x-model="onlyMatchingRoute" class="rounded text-purple-600 focus:ring-0 cursor-pointer">
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                                <span>Chỉ khớp tuyến (<span class="font-mono" x-text="selectedVehiclePlate"></span>)</span>
                            </span>
                        </label>

                        <!-- Quick date filter pills -->
                        <div class="flex items-center gap-1 pl-2.5 border-l border-slate-200">
                            <span class="text-[11px] text-slate-400 font-bold">Ngày:</span>
                            <button type="button" @click="deliveryDateFilter = ''"
                                    :class="!deliveryDateFilter ? 'bg-purple-600 text-white shadow-2xs font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                                    class="px-2 py-0.5 rounded-lg text-[10px] transition-all cursor-pointer">
                                Tất cả
                            </button>
                            <button type="button" @click="deliveryDateFilter = '{{ date('Y-m-d') }}'"
                                    :class="deliveryDateFilter === '{{ date('Y-m-d') }}' ? 'bg-purple-600 text-white shadow-2xs font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                                    class="px-2 py-0.5 rounded-lg text-[10px] transition-all cursor-pointer">
                                Hôm nay
                            </button>
                            <button type="button" @click="deliveryDateFilter = '{{ date('Y-m-d', strtotime('+1 day')) }}'"
                                    :class="deliveryDateFilter === '{{ date('Y-m-d', strtotime('+1 day')) }}' ? 'bg-purple-600 text-white shadow-2xs font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                                    class="px-2 py-0.5 rounded-lg text-[10px] transition-all cursor-pointer">
                                Ngày mai
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-slate-500 text-xs font-semibold shrink-0">
                        <span>Hiển thị: <strong class="text-slate-800" x-text="filteredUnassignedOrders.length + ' / ' + unassignedOrders.length + ' đơn'"></strong></span>
                        <button type="button" @click="toggleSelectAllOrders()" class="text-purple-700 hover:text-purple-900 font-bold cursor-pointer">
                            <span x-text="selectedOrderIds.length === filteredUnassignedOrders.length && filteredUnassignedOrders.length > 0 ? 'Bỏ chọn tất cả' : 'Chọn tất cả'"></span>
                        </button>
                    </div>
                </div>

                <!-- Floating Bulk Action Toolbar if orders are checked -->
                <div x-show="selectedOrderIds.length > 0" x-cloak
                     class="p-3 bg-purple-50 border border-purple-200 rounded-2xl flex items-center justify-between gap-3 text-xs animate-fade-in">
                    <div class="flex items-center gap-2 text-purple-900 font-bold">
                        <i class="fa-solid fa-circle-check text-purple-600"></i>
                        <span x-text="'Đã chọn ' + selectedOrderIds.length + ' đơn hàng'"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="assignBulkOrdersToVehicle(selectedVehiclePlate)"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-black rounded-xl transition-all cursor-pointer shadow-sm shadow-purple-600/25 flex items-center gap-1.5 active:scale-95">
                            <i class="fa-solid fa-plus"></i>
                            <span x-text="'Xếp & Lưu ' + selectedOrderIds.length + ' đơn vào ' + selectedVehiclePlate"></span>
                        </button>
                    </div>
                </div>

                <!-- Orders Cards List -->
                <div class="space-y-3 max-h-[calc(100vh-380px)] overflow-y-auto pr-1">
                    <template x-for="ord in filteredUnassignedOrders" :key="ord.id">
                        <div class="p-4 rounded-2xl border transition-all space-y-2.5"
                             :class="isOrderMatchingActiveVehicle(ord) 
                                     ? 'border-purple-300 bg-purple-50/25 shadow-xs' 
                                     : 'border-slate-200/90 bg-white hover:border-slate-300 hover:shadow-2xs'">
                            
                            <!-- Row 1: Checkbox, Code, Customer Name, Badge, Amount & Status -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2.5">
                                    <input type="checkbox" :value="ord.id" x-model="selectedOrderIds"
                                           class="mt-1 rounded text-purple-600 focus:ring-0 cursor-pointer">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200 font-mono font-black text-xs"
                                                  x-text="ord.order_code"></span>
                                            <span class="font-black text-slate-900 text-xs sm:text-sm" x-text="ord.customer_name"></span>
                                            <template x-if="ord.order_type === 'project'">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                                    Khách Dự Án
                                                </span>
                                            </template>
                                            <template x-if="isOrderMatchingActiveVehicle(ord)">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 flex items-center gap-1 shadow-2xs">
                                                    <i class="fa-solid fa-star text-amber-500 text-[9px]"></i>
                                                    <span>Khớp xe đang chọn</span>
                                                </span>
                                            </template>
                                        </div>

                                        <!-- Sales Rep info -->
                                        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-3">
                                            <span>NV Bán hàng: <strong class="text-slate-700" x-text="ord.creator_name || ord.order_creator || 'Nguyễn Văn Nam'"></strong></span>
                                            <template x-if="ord.customer_phone">
                                                <span>• SĐT: <span class="font-mono font-medium text-slate-700" x-text="ord.customer_phone"></span></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <div class="font-mono font-black text-emerald-700 text-sm" x-text="formatCurrency(ord.total_amount)"></div>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border mt-0.5"
                                          :class="getOrderStatusBadgeClass(ord.status)"
                                          x-text="ord.status"></span>
                                </div>
                            </div>

                            <!-- Row 2: Address & Notes -->
                            <div class="text-xs text-slate-600 bg-slate-50/70 p-2.5 rounded-xl border border-slate-100 space-y-1">
                                <div class="flex items-center gap-1.5 font-medium text-slate-700">
                                    <i class="fa-solid fa-location-dot text-rose-500 text-xs"></i>
                                    <span x-text="ord.customer_address || 'Địa chỉ khách hàng'"></span>
                                </div>
                                <template x-if="ord.notes">
                                    <div class="text-[11px] text-slate-500 italic pl-4" x-text="'Ghi chú: ' + ord.notes"></div>
                                </template>
                            </div>

                            <!-- Row 3: Route Suggestion & Actions -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-1 border-t border-slate-100 gap-2 text-xs">
                                <div class="flex flex-wrap items-center gap-2.5 text-slate-600">
                                    <span class="inline-flex items-center gap-1.5 font-bold text-purple-800 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">
                                        <i class="fa-solid fa-route text-purple-600 text-xs"></i>
                                        <span x-text="'Gợi ý: ' + getSuggestedRouteText(ord)"></span>
                                    </span>
                                    <template x-if="ord.delivery_date">
                                        <span class="inline-flex items-center gap-1 font-bold text-blue-800 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200 text-[11px]" title="Ngày giao hàng dự kiến">
                                            <i class="fa-regular fa-calendar text-blue-600 text-[10px]"></i>
                                            <span>Ngày giao: <strong class="font-mono text-blue-900" x-text="formatDate(ord.delivery_date)"></strong></span>
                                        </span>
                                    </template>
                                    <span class="font-semibold text-slate-500 text-[11px]">
                                        SL: <strong class="text-slate-700 font-mono" x-text="(ord.items ? ord.items.length : 4) + ' sp'"></strong>
                                    </span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <!-- Primary: Xếp vào xe đang chọn -->
                                    <button type="button" @click="assignSingleOrderToVehicle(ord, selectedVehiclePlate)"
                                            class="px-3.5 py-1.5 rounded-xl font-black text-xs text-white bg-purple-600 hover:bg-purple-700 transition-all flex items-center gap-1.5 cursor-pointer shadow-xs active:scale-95">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                        <span x-text="'Xếp vào ' + selectedVehiclePlate"></span>
                                    </button>

                                    <!-- Secondary: Xe khác dropdown -->
                                    <div class="relative" x-data="{ openOther: false }">
                                        <button type="button" @click="openOther = !openOther"
                                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors flex items-center gap-1 cursor-pointer">
                                            <span>Xe khác</span>
                                            <i class="fa-solid fa-chevron-down text-[9px]"></i>
                                        </button>

                                        <div x-show="openOther" @click.outside="openOther = false" x-cloak
                                             class="absolute right-0 bottom-full mb-1 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-50">
                                            <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Chọn xe nhận đơn</div>
                                            <template x-for="ov in vehicles" :key="ov.id">
                                                <button type="button" @click="assignSingleOrderToVehicle(ord, ov.plate_number); openOther = false"
                                                        class="w-full text-left px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-purple-50 hover:text-purple-900 flex items-center justify-between cursor-pointer">
                                                    <span class="font-mono font-bold" x-text="ov.plate_number"></span>
                                                    <span class="text-[10px] text-slate-400" x-text="ov.vehicle_type"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>

                    <div x-show="filteredUnassignedOrders.length === 0" class="py-12 text-center text-slate-400 text-xs italic bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        Không tìm thấy đơn hàng nào chờ xếp xe phù hợp bộ lọc.
                    </div>
                </div>

            </div>

        </div>

    </div>
    </div> <!-- Đóng KHỐI 1: currentMainTab === 'dispatch' -->

    <!-- ========================================================================= -->
    <!-- KHỐI 2: HÀNG LẺ LƯU ĐỘNG TRÊN XE (VAN SALES / BÁN DẠO DỌC ĐƯỜNG)           -->
    <!-- ========================================================================= -->
    <div x-show="currentMainTab === 'vansale'" class="space-y-4" style="display: none;">
        
        <!-- Stats & Actions Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-200/90 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-xl font-bold">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tổng Hàng Lẻ Trên Xe</p>
                    <p class="text-lg font-black text-emerald-700 font-mono mt-0.5" x-text="formatCurrency(totalVanStockAmount)"></p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-200/90 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 text-xl font-bold">
                    <i class="fa-solid fa-truck-moving"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Phiếu Đang Trên Xe</p>
                    <p class="text-lg font-black text-sky-800 font-mono mt-0.5" x-text="activeVanDispatchesCount + ' phiếu xe'"></p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-200/90 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 text-xl font-bold">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mặt Hàng Lưu Động</p>
                    <p class="text-lg font-black text-purple-900 font-mono mt-0.5" x-text="totalVanItemsCount + ' mặt hàng'"></p>
                </div>
            </div>

            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-4 rounded-2xl text-white shadow-md flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-emerald-100">Bốc thêm hàng lên xe?</p>
                    <p class="text-[11px] text-emerald-200 mt-0.5">Xuất hàng lẻ đi bán dạo & tính nợ xe</p>
                </div>
                <button type="button" @click="openVanSaleModal()"
                        class="px-3.5 py-2 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 font-black text-xs transition-all shadow-sm active:scale-95 cursor-pointer shrink-0">
                    <i class="fa-solid fa-plus mr-1"></i>Lập phiếu
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap flex-1 max-w-2xl">
                <div class="relative flex-1 min-w-[220px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="vanSearchQuery" placeholder="Tìm theo mã phiếu, tên lái xe, biển số xe..."
                           class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 font-medium outline-none">
                </div>

                <select x-model="vanStatusFilter" class="px-3 py-2 text-xs font-bold rounded-xl border border-slate-200 bg-slate-50 text-slate-700 outline-none">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="dispatched">🚚 Đang trên xe (Chưa quyết toán)</option>
                    <option value="settled">✅ Đã quyết toán</option>
                </select>

                <select x-model="vanVehicleFilter" class="px-3 py-2 text-xs font-bold rounded-xl border border-slate-200 bg-slate-50 text-slate-700 outline-none">
                    <option value="all">Tất cả xe</option>
                    <template x-for="v in vehicles" :key="v.id">
                        <option :value="v.plate_number" x-text="v.plate_number + ' (' + (v.driver?.name || 'Lái xe') + ')'"></option>
                    </template>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="vanSearchQuery = ''; vanStatusFilter = 'all'; vanVehicleFilter = 'all';"
                        class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-rotate-left text-slate-400"></i>
                    <span>Đặt lại lọc</span>
                </button>
            </div>
        </div>

        <!-- Bảng Danh Sách Phiếu Hàng Lẻ Xe -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600 text-base"></i>
                    <h3 class="font-black text-slate-900 text-sm">Danh Sách Phiếu Hàng Lẻ Đang Lưu Động Trên Xe</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-200 text-slate-700" x-text="filteredVanDispatches.length + ' phiếu'"></span>
                </div>
                <p class="text-[11px] text-slate-400 hidden sm:block">
                    Hàng hóa xuất lẻ bán dạo dọc đường do tài xế phụ trách và tính vào công nợ xe
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 font-bold uppercase text-[11px] border-b border-slate-200">
                            <th class="py-3 px-3.5 w-12 text-center">STT</th>
                            <th class="py-3 px-3.5">Mã Phiếu Xe</th>
                            <th class="py-3 px-3.5">Xe Vận Tải</th>
                            <th class="py-3 px-3.5">Lái Xe / Phụ Trách</th>
                            <th class="py-3 px-3.5">Chi Tiết Mặt Hàng Bốc Lên Xe</th>
                            <th class="py-3 px-3.5 text-right">Tổng Tiền Xuất Xe</th>
                            <th class="py-3 px-3.5">Ngày Xuất</th>
                            <th class="py-3 px-3.5 text-center">Trạng Thái</th>
                            <th class="py-3 px-3.5 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <template x-for="(v, vIdx) in filteredVanDispatches" :key="v.id">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-3.5 text-center text-slate-400 font-mono" x-text="vIdx + 1"></td>
                                <td class="py-3 px-3.5">
                                    <span class="font-mono font-black text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200" x-text="v.code"></span>
                                    <div class="text-[11px] text-slate-400 mt-1" x-text="v.notes || 'Hàng bán dạo lưu động'"></div>
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-mono font-black text-xs text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200 inline-block" x-text="v.vehicle_plate || 'Xe lưu động'"></span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="font-bold text-slate-900 text-xs" x-text="v.driver_name || (v.driver?.name || 'Tài xế')"></div>
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5" x-text="v.driver?.phone || '---'"></div>
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="space-y-1 max-w-sm">
                                        <template x-for="(it, iIdx) in (v.items || [])" :key="iIdx">
                                            <div class="flex items-center justify-between text-[11.5px] bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                                <span class="font-semibold text-slate-800 truncate mr-2" x-text="it.name"></span>
                                                <span class="font-mono text-emerald-700 shrink-0 font-bold" x-text="it.quantity + ' ' + (it.unit || 'cái') + ' × ' + formatCurrency(it.unit_price)"></span>
                                            </div>
                                        </template>
                                        <div x-show="!v.items || v.items.length === 0" class="text-slate-400 text-[11px] italic">Chưa có chi tiết item</div>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-black text-emerald-700 text-sm whitespace-nowrap" x-text="formatCurrency(v.total_amount)"></td>
                                <td class="py-3 px-3.5 text-slate-600 text-[11px] whitespace-nowrap" x-text="formatDate(v.dispatch_date || v.created_at)"></td>
                                <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                    <template x-if="v.status === 'dispatched' || v.status === 'pending_settle'">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Đang trên xe
                                        </span>
                                    </template>
                                    <template x-if="v.status === 'settled' || v.status === 'completed'">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            Đã quyết toán
                                        </span>
                                    </template>
                                </td>
                                <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                    <button type="button" @click="openVanSaleModal(v.vehicle_plate)"
                                            class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] border border-emerald-200 transition-colors cursor-pointer"
                                            title="Bốc thêm hàng cho xe này">
                                        <i class="fa-solid fa-plus mr-0.5"></i>Bốc thêm
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredVanDispatches.length === 0">
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <p class="font-bold">Chưa có phiếu xuất hàng lẻ nào trên xe</p>
                                <p class="text-xs text-slate-400 mt-0.5">Bấm nút bên dưới để lập phiếu bốc hàng lẻ bán dạo lên xe cho tài xế</p>
                                <button type="button" @click="openVanSaleModal()" class="mt-3 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer">
                                    + Lập Phiếu Bốc Hàng Lên Xe Đầu Tiên
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal: Xác Nhận Xuất Bến Cho Xe -->
    <div x-show="showDepartureModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.outside="showDepartureModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-600/25">
                        <i class="fa-solid fa-paper-plane text-base"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Lập Lệnh Xuất Bến</h3>
                        <p class="text-xs text-purple-700 font-mono font-bold" x-text="departingVehicle?.plate_number"></p>
                    </div>
                </div>
                <button type="button" @click="showDepartureModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Lái xe chính:</span>
                    <strong class="text-slate-800" x-text="departingVehicle?.driver?.name || departingVehicle?.driverName || 'Chưa chỉ định'"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Số đơn xếp trên xe:</span>
                    <strong class="text-purple-700 font-mono font-bold" x-text="getAssignedOrdersCount(departingVehicle?.plate_number) + ' đơn'"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tổng giá trị hàng:</span>
                    <strong class="text-emerald-700 font-mono font-black" x-text="formatCurrency(getAssignedOrdersTotal(departingVehicle?.plate_number))"></strong>
                </div>
            </div>

            <!-- Departure Time Field -->
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1.5">Giờ Xuất Bến Dự Kiến</label>
                <input type="time" x-model="departureTimeInput"
                       class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 focus:border-purple-500 rounded-xl font-bold font-mono text-sm text-slate-900 outline-none transition-all">
            </div>

            <!-- Notes -->
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1.5">Ghi Chú Chuyến Đi</label>
                <input type="text" x-model="departureNoteInput" placeholder="Ví dụ: Giao xong quay lại lấy hàng đợt 2..."
                       class="w-full px-3.5 py-2 bg-slate-50 focus:bg-white border border-slate-200 focus:border-purple-500 rounded-xl text-xs text-slate-800 outline-none transition-all">
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="showDepartureModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmDeparture()"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition-all shadow-md shadow-emerald-600/25 flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Xác Nhận Xuất Bến</span>
                </button>
            </div>
        </div>
    </div>
    <!-- ========================================================================= -->
    <!-- MODAL: LẬP PHIẾU BỐC HÀNG LẺ LƯU ĐỘNG LÊN XE (TÍNH NỢ XE)                  -->
    <!-- ========================================================================= -->
    <div x-show="showVanSaleModal" x-cloak
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.away="showVanSaleModal = false">
            
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-slate-100 bg-gradient-to-r from-emerald-700 to-teal-800 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 text-white flex items-center justify-center font-bold text-lg border border-white/20 shadow-inner">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black tracking-tight">Lập Phiếu Bốc Hàng Lẻ Lưu Động Lên Xe</h3>
                        <p class="text-xs text-emerald-200 mt-0.5">Tính nợ hàng lưu động trên xe cho tài xế & tự động trừ tồn kho</p>
                    </div>
                </div>
                <button type="button" @click="showVanSaleModal = false" class="text-white/70 hover:text-white transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-5 space-y-4 text-xs">
                
                <!-- Notice Banner -->
                <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200/80 text-amber-900 text-[11.5px] leading-relaxed flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-info text-amber-600 text-sm mt-0.5 shrink-0"></i>
                    <div>
                        <strong>Cơ chế hàng lẻ xe:</strong> Hàng xuất bốc lên xe bán dạo dọc đường sẽ được <strong>trừ kho thực tế</strong> và ghi trực tiếp vào <strong>công nợ tài xế chịu trách nhiệm</strong> cho đến khi xe về quyết toán hoặc thu tiền về nộp quỹ.
                    </div>
                </div>

                <!-- Chọn Xe & Tài Xế -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-black text-slate-700 mb-1.5">
                            <i class="fa-solid fa-truck text-emerald-600 mr-1"></i>Biển Số Xe (*):
                        </label>
                        <select x-model="vanSaleForm.vehicle_plate" @change="onVehicleChange()"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 font-mono font-bold text-xs text-slate-800 outline-none">
                            <option value="">-- Chọn xe nhận hàng lẻ --</option>
                            <template x-for="v in vehicles" :key="v.id">
                                <option :value="v.plate_number" x-text="v.plate_number + ' (' + v.vehicle_type + ' - ' + (v.driver?.name || 'Chưa gán') + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block font-black text-slate-700 mb-1.5">
                            <i class="fa-solid fa-user-shield text-emerald-600 mr-1"></i>Lái Xe / Người Vận Chuyển (*):
                        </label>
                        <select x-model="vanSaleForm.driver_id" @change="onDriverChange()"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 font-bold text-xs text-slate-800 outline-none">
                            <option value="">-- Chọn tài xế chịu trách nhiệm --</option>
                            <template x-for="d in drivers" :key="d.id">
                                <option :value="d.id" x-text="d.name + ' (' + (d.phone || 'Không SĐT') + ')'"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Danh Sách Sản Phẩm -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="font-black text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-boxes-stacked text-emerald-600"></i>
                            <span>Danh Sách Sản Phẩm Bốc Lên Xe:</span>
                        </label>
                        <button type="button" @click="addVanItem()"
                                class="text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1 cursor-pointer text-xs">
                            <i class="fa-solid fa-circle-plus"></i> Thêm sản phẩm
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden max-h-60 overflow-y-auto">
                        <table class="w-full text-left border-collapse text-[11px]">
                            <thead class="bg-slate-100 text-slate-700 font-bold sticky top-0 z-10">
                                <tr>
                                    <th class="py-2.5 px-3">Sản Phẩm (Tồn Kho Thực Tế)</th>
                                    <th class="py-2.5 px-2.5 w-24 text-center">Số Lượng</th>
                                    <th class="py-2.5 px-2.5 w-28 text-right">Đơn Giá Xuất</th>
                                    <th class="py-2.5 px-2.5 w-28 text-right">Thành Tiền</th>
                                    <th class="py-2.5 px-2 w-8 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(it, idx) in vanSaleForm.items" :key="it._uid">
                                    <tr>
                                        <td class="p-2 relative min-w-[260px]">
                                            <div class="relative" @click.outside="if (activeProdDropdownIndex === idx) activeProdDropdownIndex = null">
                                                <input type="text"
                                                       x-model="it.searchQuery"
                                                       @focus="activeProdDropdownIndex = idx; prodHighlightIndex = 0"
                                                       @input="activeProdDropdownIndex = idx; prodHighlightIndex = 0; if(!it.searchQuery) { it.product_id = ''; it.unit_price = 0; }"
                                                       @keydown.down.prevent="
                                                           const prods = getFilteredProducts(it.searchQuery);
                                                           if (prods.length > 0) prodHighlightIndex = (prodHighlightIndex + 1) % prods.length;
                                                       "
                                                       @keydown.up.prevent="
                                                           const prods = getFilteredProducts(it.searchQuery);
                                                           if (prods.length > 0) prodHighlightIndex = (prodHighlightIndex - 1 + prods.length) % prods.length;
                                                       "
                                                       @keydown.enter.prevent="
                                                           if (activeProdDropdownIndex === idx) {
                                                               const prods = getFilteredProducts(it.searchQuery);
                                                               if (prods.length > 0 && prods[prodHighlightIndex]) {
                                                                   selectProductForRow(idx, prods[prodHighlightIndex]);
                                                               }
                                                           }
                                                       "
                                                       @keydown.escape.prevent="activeProdDropdownIndex = null"
                                                       placeholder="Tìm mã hoặc tên sản phẩm..."
                                                       class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:border-emerald-500 focus:outline-none shadow-2xs">

                                                <div x-show="activeProdDropdownIndex === idx && getFilteredProducts(it.searchQuery).length > 0"
                                                     x-cloak
                                                     class="absolute left-0 top-full mt-1 w-[440px] max-w-[90vw] z-50 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-60 overflow-y-auto py-1 text-xs">
                                                    <template x-for="(p, pIdx) in getFilteredProducts(it.searchQuery)" :key="p.id">
                                                        <div @mousedown.prevent="selectProductForRow(idx, p)"
                                                             @mouseenter="prodHighlightIndex = pIdx"
                                                             class="px-3 py-2 cursor-pointer flex items-center justify-between border-b border-slate-50 last:border-0"
                                                             :class="prodHighlightIndex === pIdx ? 'bg-emerald-50 text-emerald-950 font-bold' : 'hover:bg-slate-50'">
                                                            <div class="min-w-0 pr-3">
                                                                <div class="font-bold text-slate-900 truncate">
                                                                    <span class="text-emerald-700 font-mono mr-1" x-text="'[' + (p.sku || 'N/A') + ']'"></span>
                                                                    <span x-text="p.name"></span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                                    <span x-text="'ĐVT: ' + (p.unit || 'cái')"></span>
                                                                    <span>&bull;</span>
                                                                    <span x-text="'Tồn: ' + (p.stock_quantity || 0) + ' ' + (p.unit || 'cái')"></span>
                                                                </div>
                                                            </div>
                                                            <div class="shrink-0 font-black text-emerald-700 text-xs" x-text="formatCurrency(p.cost_price || 0)"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2 text-center">
                                            <input type="number" min="1" x-model.number="it.quantity"
                                                   class="w-full p-2 text-center rounded-xl border border-slate-200 text-xs font-mono font-bold outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 bg-slate-50 focus:bg-white transition-all">
                                        </td>
                                        <td class="p-2 text-right">
                                            <input type="number" min="0" x-model.number="it.unit_price"
                                                   class="w-full p-2 text-right rounded-xl border border-slate-200 text-xs font-mono outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 bg-slate-50 focus:bg-white transition-all">
                                        </td>
                                        <td class="p-2 text-right font-mono font-black text-slate-900 whitespace-nowrap"
                                            x-text="formatCurrency((it.quantity || 0) * (it.unit_price || 0))"></td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="removeVanItem(idx)" class="text-rose-400 hover:text-rose-600 cursor-pointer p-1 transition-colors" title="Xóa dòng">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Box Tổng Tiền -->
                <div class="flex items-center justify-between p-3.5 bg-emerald-50/80 rounded-2xl border border-emerald-200/90 font-bold">
                    <div>
                        <span class="text-emerald-950 font-black block">Tổng Trị Giá Ghi Nợ Cho Lái Xe:</span>
                        <span class="text-[11px] text-emerald-700 font-normal">Tự động cộng vào công nợ tài xế chịu trách nhiệm</span>
                    </div>
                    <span class="text-lg text-emerald-800 font-mono font-black" x-text="formatCurrency(getVanSaleTotal())"></span>
                </div>

                <!-- Ghi Chú -->
                <div>
                    <label class="block font-black text-slate-700 mb-1.5">Ghi Chú Lộ Trình / Địa Bàn Bán Dạo:</label>
                    <textarea x-model="vanSaleForm.notes" rows="2" placeholder="Ví dụ: Bốc thêm 10 bao phụ kiện PVC + 5 lõi lọc đi bán dạo khu vực Hoài Đức - Quốc Oai..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 text-xs outline-none"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5 text-xs">
                <button type="button" @click="showVanSaleModal = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitVanSale()"
                        class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 font-black text-white shadow-md shadow-emerald-600/25 flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <i class="fa-solid fa-check"></i>
                    <span>Xác Nhận Xuất Hàng Lên Xe</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function dispatchApp() {
    return {
        currentMainTab: 'dispatch',
        vehicles: @json($vehicles),
        routes: @json($routes),
        orders: @json($readyOrders),
        
        // Dữ liệu Hàng Lẻ Xe
        totalVanStockAmount: {{ (float)($totalVanStockAmount ?? 0) }},
        vanDispatches: @json($vanDispatches ?? []),
        availableProducts: @json($availableProducts ?? []),
        drivers: @json($allDrivers ?? []),
        
        vanSearchQuery: '',
        vanStatusFilter: 'all',
        vanVehicleFilter: 'all',

        showVanSaleModal: false,
        vanSaleForm: {
            driver_id: '',
            vehicle_plate: '',
            notes: '',
            items: [
                { product_id: '', quantity: 1, unit_price: 0 }
            ]
        },

        selectedVehiclePlate: '29C-888.99',
        searchQuery: '',
        selectedRouteFilter: '',
        deliveryDateFilter: '',
        onlyMatchingRoute: false,
        selectedOrderIds: [],
        expandedVehiclePlates: ['29C-888.99'],

        showDepartureModal: false,
        departingVehicle: null,
        departureTimeInput: '{{ date("H:i") }}',
        departureNoteInput: '',

        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        get activeVanDispatchesCount() {
            return this.vanDispatches.filter(v => v.status === 'dispatched' || v.status === 'pending_settle').length;
        },

        get totalVanItemsCount() {
            return this.vanDispatches.reduce((acc, v) => acc + (v.items ? v.items.length : 0), 0);
        },

        get filteredVanDispatches() {
            return this.vanDispatches.filter(v => {
                if (this.vanSearchQuery) {
                    const q = this.vanSearchQuery.toLowerCase();
                    const code = (v.code || '').toLowerCase();
                    const driver = (v.driver_name || (v.driver?.name || '')).toLowerCase();
                    const plate = (v.vehicle_plate || '').toLowerCase();
                    if (!code.includes(q) && !driver.includes(q) && !plate.includes(q)) return false;
                }
                if (this.vanStatusFilter !== 'all') {
                    if (this.vanStatusFilter === 'dispatched' && v.status !== 'dispatched' && v.status !== 'pending_settle') return false;
                    if (this.vanStatusFilter === 'settled' && v.status !== 'settled' && v.status !== 'completed') return false;
                }
                if (this.vanVehicleFilter !== 'all') {
                    if (v.vehicle_plate !== this.vanVehicleFilter) return false;
                }
                return true;
            });
        },

        openVanSaleModal(plate = null) {
            const defaultPlate = plate || this.selectedVehiclePlate || (this.vehicles[0]?.plate_number || '');
            this.vanSaleForm = {
                driver_id: '',
                vehicle_plate: defaultPlate,
                notes: '',
                items: [
                    { _uid: 'it_' + Math.random().toString(36).substr(2, 9), product_id: '', quantity: 1, unit_price: 0 },
                    { _uid: 'it_' + Math.random().toString(36).substr(2, 9), product_id: '', quantity: 1, unit_price: 0 },
                    { _uid: 'it_' + Math.random().toString(36).substr(2, 9), product_id: '', quantity: 1, unit_price: 0 }
                ]
            };
            if (defaultPlate) {
                const veh = this.vehicles.find(v => v.plate_number === defaultPlate);
                if (veh && veh.driver_id) {
                    this.vanSaleForm.driver_id = veh.driver_id;
                } else if (veh && veh.driver && veh.driver.id) {
                    this.vanSaleForm.driver_id = veh.driver.id;
                }
            }
            this.showVanSaleModal = true;
        },

        onVehicleChange() {
            if (!this.vanSaleForm.vehicle_plate) return;
            const veh = this.vehicles.find(v => v.plate_number === this.vanSaleForm.vehicle_plate);
            if (veh && (veh.driver_id || (veh.driver && veh.driver.id))) {
                this.vanSaleForm.driver_id = veh.driver_id || veh.driver.id;
            }
        },

        onDriverChange() {
            if (!this.vanSaleForm.driver_id) return;
            const veh = this.vehicles.find(v => v.driver_id == this.vanSaleForm.driver_id || (v.driver && v.driver.id == this.vanSaleForm.driver_id));
            if (veh) {
                this.vanSaleForm.vehicle_plate = veh.plate_number;
            }
        },

        addVanItem() {
            this.vanSaleForm.items.push({
                _uid: 'it_' + Math.random().toString(36).substr(2, 9),
                product_id: '',
                quantity: 1,
                unit_price: 0
            });
        },

        removeVanItem(idx) {
            if (this.vanSaleForm.items.length > 1) {
                this.vanSaleForm.items.splice(idx, 1);
            }
        },

        getFilteredProducts(query) {
            const list = this.availableProducts || [];
            if (!query || !query.trim()) return list.slice(0, 30);
            const q = query.toLowerCase().trim();
            return list.filter(p => (p.sku || '').toLowerCase().includes(q) || (p.name || '').toLowerCase().includes(q)).slice(0, 30);
        },

        selectProductForRow(idx, p) {
            const item = this.vanSaleForm.items[idx];
            if (!item || !p) return;
            item.product_id = p.id;
            item.sku = p.sku || '';
            item.name = p.name;
            item.unit = p.unit || 'cái';
            item.stock_quantity = p.stock_quantity || 0;
            item.searchQuery = '[' + (p.sku || 'N/A') + '] ' + p.name;
            item.unit_price = Number(p.cost_price || 0);
            this.activeProdDropdownIndex = null;
        },

        getVanSaleTotal() {
            return this.vanSaleForm.items.reduce((sum, it) => sum + (Number(it.quantity || 0) * Number(it.unit_price || 0)), 0);
        },

        async submitVanSale() {
            if (!this.vanSaleForm.driver_id) {
                this.showToast('Vui lòng chọn tài xế / người phụ trách xe!', 'error');
                return;
            }
            const validItems = this.vanSaleForm.items.filter(i => i.product_id && Number(i.quantity) > 0);
            if (validItems.length === 0) {
                this.showToast('Vui lòng chọn ít nhất 1 sản phẩm có số lượng hợp lệ!', 'error');
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const payload = {
                    driver_id: this.vanSaleForm.driver_id,
                    vehicle_plate: this.vanSaleForm.vehicle_plate,
                    notes: this.vanSaleForm.notes,
                    items: validItems.map(i => ({
                        product_id: i.product_id,
                        quantity: Number(i.quantity),
                        unit_price: Number(i.unit_price)
                    }))
                };
                const res = await fetch('/van-sale-dispatches', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message, 'success');
                    if (data.dispatch) {
                        this.vanDispatches.unshift(data.dispatch);
                        this.totalVanStockAmount += Number(data.dispatch.total_amount || 0);
                    }
                    this.showVanSaleModal = false;
                    this.currentMainTab = 'vansale';
                } else {
                    this.showToast(data.message || 'Lỗi lập phiếu xuất hàng lẻ', 'error');
                }
            } catch(e) {
                this.showToast('Lỗi kết nối máy chủ: ' + e.message, 'error');
            }
        },

        initApp() {
            if (this.vehicles.length > 0 && !this.vehicles.some(v => v.plate_number === this.selectedVehiclePlate)) {
                this.selectedVehiclePlate = this.vehicles[0].plate_number;
            }
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        selectVehicle(plate) {
            this.selectedVehiclePlate = plate;
        },

        toggleVehicleOrders(plate) {
            if (this.expandedVehiclePlates.includes(plate)) {
                this.expandedVehiclePlates = this.expandedVehiclePlates.filter(p => p !== plate);
            } else {
                this.expandedVehiclePlates.push(plate);
            }
        },

        getVehicleTypeName(plate) {
            const v = this.vehicles.find(item => item.plate_number === plate);
            return v ? v.vehicle_type : 'Xe tải';
        },

        getVehicleRouteText(v) {
            if (v.delivery_route && v.delivery_route.name) return v.delivery_route.name;
            if (v.deliveryRoute && v.deliveryRoute.name) return v.deliveryRoute.name;
            return 'Tuyến tự do / Nội thành';
        },

        getVehicleBadgeClass(status) {
            switch(status) {
                case 'Rảnh':
                case 'Sẵn sàng':
                    return 'bg-emerald-50 text-emerald-800 border-emerald-300';
                case 'Đang giao hàng':
                case 'Đang di chuyển':
                    return 'bg-sky-50 text-sky-800 border-sky-300';
                case 'Bảo dưỡng':
                    return 'bg-rose-50 text-rose-800 border-rose-300';
                default:
                    return 'bg-slate-100 text-slate-700 border-slate-200';
            }
        },

        getOrderStatusBadgeClass(status) {
            switch(status) {
                case 'Đã bốc xong':
                case 'Đã kiểm hàng':
                case 'Sẵn sàng xuất xe':
                    return 'bg-emerald-50 text-emerald-800 border-emerald-300';
                case 'Đang bốc hàng':
                case 'Soạn hàng':
                case 'Đang vận chuyển':
                    return 'bg-sky-50 text-sky-800 border-sky-300';
                case 'Giao thành công':
                case 'Đã giao':
                    return 'bg-purple-50 text-purple-800 border-purple-300';
                default:
                    return 'bg-amber-50 text-amber-800 border-amber-300';
            }
        },

        get unassignedOrders() {
            return this.orders.filter(o => !o.assigned_vehicle_plate);
        },

        isOrderMatchingActiveVehicle(o) {
            const activeVeh = this.vehicles.find(v => v.plate_number === this.selectedVehiclePlate);
            if (!activeVeh) return false;
            const vRoute = this.getVehicleRouteText(activeVeh).toLowerCase().trim();
            if (!vRoute || vRoute.includes('tuyến tự do')) return false;
            const oRoute = this.getSuggestedRouteText(o).toLowerCase().trim();
            if (!oRoute || oRoute.includes('tuyến tự do')) return false;

            return vRoute === oRoute || vRoute.includes(oRoute) || oRoute.includes(vRoute);
        },

        get filteredUnassignedOrders() {
            const list = this.unassignedOrders.filter(o => {
                // Text search
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const matchCode = (o.order_code || '').toLowerCase().includes(q);
                    const matchName = (o.customer_name || '').toLowerCase().includes(q);
                    const matchAddr = (o.customer_address || '').toLowerCase().includes(q);
                    if (!matchCode && !matchName && !matchAddr) return false;
                }

                // Route filter
                if (this.selectedRouteFilter) {
                    const rText = this.getSuggestedRouteText(o).toLowerCase();
                    if (!rText.includes(this.selectedRouteFilter.toLowerCase())) {
                        return false;
                    }
                }

                // Delivery date filter
                if (this.deliveryDateFilter) {
                    const oDate = (o.delivery_date || '').substring(0, 10);
                    if (oDate !== this.deliveryDateFilter) {
                        return false;
                    }
                }

                // Only match active vehicle's route
                if (this.onlyMatchingRoute) {
                    if (!this.isOrderMatchingActiveVehicle(o)) {
                        return false;
                    }
                }

                return true;
            });

            // Sắp xếp ưu tiên các đơn có gợi ý tuyến cùng xe đang chọn lên phía trên
            return list.sort((a, b) => {
                const aMatch = this.isOrderMatchingActiveVehicle(a) ? 1 : 0;
                const bMatch = this.isOrderMatchingActiveVehicle(b) ? 1 : 0;

                // Nếu có đơn khớp tuyến xe đang chọn, đưa lên đầu danh sách (1 trước 0)
                if (aMatch !== bMatch) {
                    return bMatch - aMatch;
                }

                // Cùng độ ưu tiên thì giữ theo thứ tự ID
                return Number(a.id) - Number(b.id);
            });
        },

        getSuggestedRouteText(o) {
            const addr = (o.customer_address || '').toLowerCase();
            if (!addr) return 'Tuyến tự do';

            // Khớp động theo danh mục lộ tuyến thực tế trong CSDL
            if (this.routes && this.routes.length > 0) {
                for (const r of this.routes) {
                    // 1. Kiểm tra theo danh sách điểm dừng (stops) của tuyến
                    let stops = r.stops;
                    if (typeof stops === 'string') {
                        try { stops = JSON.parse(stops); } catch(e) { stops = []; }
                    }
                    if (Array.isArray(stops)) {
                        for (const s of stops) {
                            const kw = String(s).toLowerCase()
                                .replace(/^(huyện|thị xã|tp\.|thành phố|tx\.)\s*/i, '')
                                .trim();
                            if (kw && kw.length >= 2 && addr.includes(kw)) {
                                return r.name;
                            }
                        }
                    }

                    // 2. Kiểm tra các từ khóa trong tên tuyến (loại bỏ tiền tố không cần thiết)
                    const nameParts = r.name.toLowerCase().split(/[-–()]/);
                    for (const part of nameParts) {
                        const kw = part
                            .replace(/^(tuyến\s*\d+:?|bắc|nam|đông|tây|nghệ an|hà tĩnh|huyện|thị xã|tp\.|thành phố|tx\.)\s*/i, '')
                            .trim();
                        if (kw && kw.length >= 3 && addr.includes(kw)) {
                            return r.name;
                        }
                    }
                }
            }

            // Mặc định nếu không tìm thấy từ khóa khớp
            if (this.routes && this.routes.length > 0) {
                return this.routes[0].name;
            }
            return 'Tuyến tự do / Nội thành';
        },

        getAssignedOrdersList(plate) {
            return this.orders.filter(o => o.assigned_vehicle_plate === plate);
        },

        getAssignedOrdersCount(plate) {
            return this.getAssignedOrdersList(plate).length;
        },

        getAssignedOrdersTotal(plate) {
            return this.getAssignedOrdersList(plate).reduce((sum, o) => sum + Number(o.total_amount || 0), 0);
        },

        getCompletedOrdersCount(plate) {
            return this.getAssignedOrdersList(plate).filter(o => ['Giao thành công', 'Đã giao', 'Đã hoàn thành', 'Đã quyết toán'].includes(o.status)).length;
        },

        get hasCompletedOrdersOnActiveVehicle() {
            return this.getCompletedOrdersCount(this.selectedVehiclePlate) > 0;
        },

        get completedOrdersCountOnActiveVehicle() {
            return this.getCompletedOrdersCount(this.selectedVehiclePlate);
        },

        toggleSelectAllOrders() {
            if (this.selectedOrderIds.length === this.filteredUnassignedOrders.length && this.filteredUnassignedOrders.length > 0) {
                this.selectedOrderIds = [];
            } else {
                this.selectedOrderIds = this.filteredUnassignedOrders.map(o => o.id);
            }
        },

        resetFilters() {
            this.searchQuery = '';
            this.selectedRouteFilter = '';
            this.deliveryDateFilter = '';
            this.onlyMatchingRoute = false;
            this.selectedOrderIds = [];
            this.showToast('Đã đặt lại các bộ lọc tìm kiếm.');
        },

        // Assign Single Order
        async assignSingleOrderToVehicle(order, targetPlate) {
            if (!targetPlate) return;
            const targetVeh = this.vehicles.find(v => v.plate_number === targetPlate);
            const driverId = targetVeh ? (targetVeh.driver_id || targetVeh.driver?.id) : null;
            const oldPlate = order.assigned_vehicle_plate;
            const oldDriverId = order.assigned_driver_id;

            // Optimistic update
            order.assigned_vehicle_plate = targetPlate;
            if (driverId) order.assigned_driver_id = driverId;

            // Remove from selected list if checked
            this.selectedOrderIds = this.selectedOrderIds.filter(id => id !== order.id);

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery/transfer-order-vehicle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderId: order.id,
                        orderCode: order.order_code,
                        targetVehiclePlate: targetPlate,
                        targetDriverId: driverId,
                        reason: 'Xếp đơn vào xe điều phối'
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Lỗi khi xếp đơn vào xe');
                }
                this.showToast('Đã xếp & lưu đơn ' + order.order_code + ' vào xe ' + targetPlate + ' (Đã lưu CSDL)!');
            } catch(e) {
                console.error(e);
                order.assigned_vehicle_plate = oldPlate;
                order.assigned_driver_id = oldDriverId;
                this.showToast('Lỗi khi lưu điều phối: ' + e.message, 'error');
            }
        },

        // Bulk Assign Orders
        async assignBulkOrdersToVehicle(targetPlate) {
            if (!targetPlate || this.selectedOrderIds.length === 0) return;
            const selectedIds = [...this.selectedOrderIds];
            const ordersToAssign = this.orders.filter(o => selectedIds.includes(o.id));
            const count = ordersToAssign.length;
            const targetVeh = this.vehicles.find(v => v.plate_number === targetPlate);
            const driverId = targetVeh ? (targetVeh.driver_id || targetVeh.driver?.id) : null;

            // Optimistic update
            ordersToAssign.forEach(o => {
                o.assigned_vehicle_plate = targetPlate;
                if (driverId) o.assigned_driver_id = driverId;
            });
            this.selectedOrderIds = [];

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery/transfer-order-vehicle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderIds: selectedIds,
                        targetVehiclePlate: targetPlate,
                        targetDriverId: driverId,
                        reason: 'Xếp hàng loạt vào xe điều phối'
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Lỗi khi xếp hàng loạt vào xe');
                }
                this.showToast('Đã xếp & lưu thành công ' + count + ' đơn hàng vào xe ' + targetPlate + ' (Đã lưu CSDL)!');
            } catch(e) {
                console.error(e);
                this.showToast('Không thể lưu xếp đơn: ' + e.message, 'error');
            }
        },

        // Unassign Order back to waiting queue
        async unassignOrderFromVehicle(order) {
            const oldPlate = order.assigned_vehicle_plate;
            const oldDriverId = order.assigned_driver_id;
            order.assigned_vehicle_plate = null;
            order.assigned_driver_id = null;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery/transfer-order-vehicle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderId: order.id,
                        orderCode: order.order_code,
                        targetVehiclePlate: '',
                        reason: 'Rút đơn hàng khỏi xe về hàng chờ'
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Lỗi khi rút đơn khỏi xe');
                }
                this.showToast('Đã rút đơn ' + order.order_code + ' khỏi xe ' + oldPlate + ' về hàng chờ (Đã lưu CSDL)!');
            } catch(e) {
                console.error(e);
                order.assigned_vehicle_plate = oldPlate;
                order.assigned_driver_id = oldDriverId;
                this.showToast('Lỗi khi rút đơn hàng: ' + e.message, 'error');
            }
        },

        // Release completed orders from vehicle
        async releaseCompletedFromVehicle(plate = null) {
            const targetPlate = plate || this.selectedVehiclePlate;
            const completed = this.getAssignedOrdersList(targetPlate).filter(o => ['Giao thành công', 'Đã giao', 'Đã hoàn thành', 'Đã quyết toán'].includes(o.status));
            if (completed.length === 0) return;

            completed.forEach(o => {
                o.assigned_vehicle_plate = null;
            });

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery/release-completed-orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        vehiclePlate: targetPlate,
                        orderCodes: completed.map(o => o.order_code)
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Lỗi giải phóng đơn');
                }
                this.showToast('Đã giải phóng ' + completed.length + ' đơn hoàn thành ra khỏi xe ' + targetPlate + ' (Đã lưu CSDL)!');
            } catch(e) {
                console.error(e);
                this.showToast('Lỗi giải phóng đơn: ' + e.message, 'error');
            }
        },

        releaseCompletedFromSelectedVehicle() {
            this.releaseCompletedFromVehicle(this.selectedVehiclePlate);
        },

        // Auto Suggest Dispatch
        async autoSuggestDispatch() {
            const suggestions = [];
            this.unassignedOrders.forEach(o => {
                const routeText = this.getSuggestedRouteText(o).toLowerCase();
                const matchedVeh = this.vehicles.find(v => {
                    const vRoute = this.getVehicleRouteText(v).toLowerCase();
                    return vRoute.includes(routeText) || routeText.includes(vRoute);
                });
                if (matchedVeh) {
                    suggestions.push({
                        order: o,
                        orderId: o.id,
                        targetPlate: matchedVeh.plate_number,
                        driverId: matchedVeh.driver_id || matchedVeh.driver?.id || null
                    });
                }
            });

            if (suggestions.length === 0) {
                this.showToast('Không có đơn hàng nào khớp tuyến đường mới để xếp.');
                return;
            }

            const grouped = {};
            suggestions.forEach(item => {
                if (!grouped[item.targetPlate]) grouped[item.targetPlate] = [];
                grouped[item.targetPlate].push(item);
            });

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                for (const plate in grouped) {
                    const items = grouped[plate];
                    const ids = items.map(i => i.orderId);
                    const driverId = items[0].driverId;

                    items.forEach(i => {
                        i.order.assigned_vehicle_plate = plate;
                        if (driverId) i.order.assigned_driver_id = driverId;
                    });

                    await fetch('/delivery/transfer-order-vehicle', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                        body: JSON.stringify({
                            orderIds: ids,
                            targetVehiclePlate: plate,
                            targetDriverId: driverId,
                            reason: 'Tự động gợi ý xếp xe theo lộ tuyến'
                        })
                    });
                }
                this.showToast('Đã tự động xếp & lưu ' + suggestions.length + ' đơn hàng vào các xe theo lộ tuyến (Đã lưu CSDL)!');
            } catch(e) {
                console.error(e);
                this.showToast('Lỗi khi tự động lưu xếp xe: ' + e.message, 'error');
            }
        },

        // Departure Modal
        openDepartureModal(veh) {
            this.departingVehicle = veh;
            this.departureTimeInput = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            this.departureNoteInput = '';
            this.showDepartureModal = true;
        },

        async confirmDeparture() {
            if (!this.departingVehicle) return;
            const plate = this.departingVehicle.plate_number;
            const veh = this.vehicles.find(v => v.plate_number === plate);

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/vehicles/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        vehicleId: this.departingVehicle.id,
                        status: 'Đang di chuyển',
                        dispatch_status: 'Đang giao hàng',
                        departureTime: this.departureTimeInput,
                        notes: this.departureNoteInput
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Lỗi khi xuất bến xe');
                }
                if (veh) {
                    veh.status = 'Đang di chuyển';
                    veh.dispatch_status = 'Đang giao hàng';
                }
                this.showDepartureModal = false;
                this.showToast('Xe ' + plate + ' đã chính thức xuất bến khởi hành giao hàng (Đã lưu CSDL)!');
            } catch(e) {
                console.error(e);
                this.showToast('Không thể xuất bến: ' + e.message, 'error');
            }
        },

        // Format Helpers
        formatCurrency(num) {
            if (!num) return '0 đ';
            return new Intl.NumberFormat('vi-VN').format(num) + ' đ';
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const s = String(dateStr).substring(0, 10);
            const parts = s.split('-');
            if (parts.length === 3) {
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            return s;
        }
    };
}
</script>

@endsection
