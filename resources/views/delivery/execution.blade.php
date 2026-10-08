@extends('layouts.app')

@section('title', 'Giao Hàng & Thu Tiền | Lái Xe & Vận Tải ERP Đại Sơn')

@section('content')
@php
    $currentUser = auth()->user();
    $isDriver = $currentUser && in_array($currentUser->role, ['Driver', 'Warehouse_Staff']);
    $isAdmin = $currentUser && in_array($currentUser->role, ['Admin', 'Manager', 'Accountant', 'Sales_Accountant', 'Director']);
@endphp

<div x-data="deliveryExecutionApp()" x-init="initApp()" class="space-y-4 max-w-[1920px] mx-auto pb-20 font-sans text-slate-800 antialiased" x-cloak>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3"
         class="fixed bottom-6 right-6 z-[9999] px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-bold border backdrop-blur-md"
         :class="toast.type === 'success' ? 'bg-emerald-600/95 text-white border-emerald-400' : 'bg-rose-600/95 text-white border-rose-400'">
        <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check text-lg' : 'fa-solid fa-triangle-exclamation text-lg'"></i>
        <span x-text="toast.message"></span>
    </div>

    <!-- ========================================================================= -->
    <!-- DANH SÁCH ĐƠN HÀNG TRÊN XE                                                -->
    <!-- ========================================================================= -->
    <div class="space-y-4">

    <!-- ========================================================================= -->
    <!-- 1. HEADER BANNER CHUYẾN XE (Thiết kế Cockpit Hiện Đại & Tinh Gọn)          -->
    <!-- ========================================================================= -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-3xl p-5 sm:p-6 border border-slate-800 text-white shadow-2xl space-y-4">
        
        <!-- Top Row: Tiêu đề, Biển số xe & Thanh công cụ thao tác -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            
            <!-- Left: Icon xe + Tiêu đề + Biển số + Trạng thái -->
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-700 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-purple-600/30 border border-purple-400/30">
                    <i class="fa-solid fa-truck-fast text-xl"></i>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <!-- Biển số xe nổi bật -->
                        <span class="px-2.5 py-0.5 rounded-lg bg-purple-950/80 border border-purple-500/40 text-purple-200 font-mono font-black text-xs tracking-wider shadow-inner"
                              x-text="selectedVehicle?.plate_number || '{{ $selectedPlate ?? '' }}'"></span>

                        <!-- Loại xe -->
                        <span class="text-xs text-slate-300 font-bold" x-text="selectedVehicle?.vehicle_type || 'Xe tải vận chuyển'"></span>

                        <!-- Trạng thái xe -->
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black tracking-wide"
                              :class="isReturning ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : (isDeparted ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-sky-500/20 text-sky-300 border border-sky-500/30')">
                            <span class="w-1.5 h-1.5 rounded-full" :class="isReturning ? 'bg-amber-400 animate-pulse' : (isDeparted ? 'bg-emerald-400 animate-pulse' : 'bg-sky-400')"></span>
                            <span x-text="isReturning ? 'TRÊN ĐƯỜNG VỀ' : (isDeparted ? 'ĐANG ĐI GIAO HÀNG' : 'SẴN SÀNG NHẬN CHUYẾN')"></span>
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                            Giao Hàng & Thu Tiền
                        </h1>
                        <span class="text-xs text-slate-400 font-medium">
                            {{ \Carbon\Carbon::now()->locale('vi')->isoFormat('dddd, DD/MM/YYYY') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Đổi xe (nếu quản trị nhiều xe) & Các nút nghiệp vụ tinh gọn -->
            <div class="flex flex-wrap items-center gap-2">
                @if(count($vehicles) > 1)
                    <!-- Selector chuyển xe khi quản lý nhiều xe -->
                    <div class="flex items-center gap-1.5 bg-slate-800/90 border border-slate-700/80 px-2.5 py-1.5 rounded-xl text-xs shadow-inner">
                        <i class="fa-solid fa-truck text-purple-400 text-xs"></i>
                        <select @change="changeVehicle($event.target.value)" class="bg-transparent font-bold text-white focus:outline-none cursor-pointer pr-1 text-xs">
                            @foreach($vehicles as $v)
                                <option value="{{ $v->plate_number }}" {{ ($selectedPlate ?? '') === $v->plate_number ? 'selected' : '' }} class="bg-slate-900 text-white font-semibold">
                                    {{ $v->plate_number }} ({{ $v->driver?->name ?? 'Lái xe' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <!-- Badge định danh xe phụ trách -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-500/15 border border-purple-500/30 text-purple-200 text-xs font-bold shadow-2xs">
                        <i class="fa-solid fa-circle-check text-purple-400 text-xs"></i>
                        <span>Xe bạn phụ trách</span>
                    </div>
                @endif

                <!-- Button 1: Hàng Trả Về -->
                <button type="button" @click="showReturnsModal = true"
                        class="px-3 py-1.5 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-200 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-rotate-left text-rose-400 text-xs"></i>
                    <span>Hàng Trả Về</span>
                    <span class="px-1.5 py-0.2 rounded-full font-mono text-[10px] font-black bg-rose-500/30 text-rose-200 border border-rose-500/40" x-text="returnsList.length">0</span>
                </button>

                <!-- Button 2: Hoàn Hàng Vào Kho -->
                <button type="button" @click="showIntakeWarehouseModal = true"
                        class="px-3 py-1.5 rounded-xl bg-slate-800/90 hover:bg-slate-700/90 border border-slate-700 text-slate-200 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-warehouse text-amber-400 text-xs"></i>
                    <span>Hoàn kho</span>
                </button>

                <!-- Button 3: Kiểm Đếm & Duyệt COD -->
                <button type="button" @click="showCodApprovalModal = true"
                        class="px-3 py-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-200 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-money-bill-transfer text-indigo-400 text-xs"></i>
                    <span>Duyệt COD</span>
                </button>

                <!-- Button 4: Quyết Toán Quỹ -->
                <button type="button" @click="openHandoverModal()"
                        class="px-3 py-1.5 rounded-xl bg-fuchsia-500/15 hover:bg-fuchsia-500/25 border border-fuchsia-500/30 text-fuchsia-200 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-wallet text-fuchsia-400 text-xs"></i>
                    <span>Quyết toán</span>
                </button>
            </div>

        </div>

        <!-- Bottom Row: Thông tin Nhân Sự & Lộ Trình Phương Tiện (Dark Glass Ribbon) -->
        <div class="bg-slate-900/60 rounded-2xl p-3 border border-slate-800/80 flex flex-wrap items-center justify-between gap-y-2 gap-x-4 text-xs">
            <!-- Lái xe & Phụ xe -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/30 flex items-center justify-center font-bold text-[11px]">
                        <i class="fa-solid fa-user-tie"></i>
                    </span>
                    <span class="text-slate-400">Lái xe chính:</span>
                    <strong class="text-white font-bold" x-text="selectedVehicle?.driver?.name || 'Bùi Văn Thành'"></strong>
                    <template x-if="selectedVehicle?.driver?.phone">
                        <a :href="'tel:' + selectedVehicle.driver.phone"
                           class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[11px] font-mono font-bold text-purple-300 bg-purple-950/60 border border-purple-700/50 hover:bg-purple-800/60 transition-colors"
                           title="Gọi điện cho lái xe">
                            <i class="fa-solid fa-phone text-[9px]"></i>
                            <span x-text="selectedVehicle.driver.phone"></span>
                        </a>
                    </template>
                </div>

                <div class="flex items-center gap-1.5 text-slate-400">
                    <span>•</span>
                    <span>Phụ xe:</span>
                    <strong class="text-slate-200 font-semibold" x-text="selectedVehicle?.copilot_name || 'Không có'"></strong>
                </div>
            </div>

            <!-- Lộ trình & Thông số xe -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-400">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot text-rose-400 text-xs"></i>
                    <span>Lộ trình: <strong class="text-slate-200" x-text="selectedVehicle?.current_route_name || selectedVehicle?.delivery_route?.name || 'Theo điều phối thực tế'"></strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span>•</span>
                    <span>Kho xuất: <strong class="text-slate-200">Kho Mê Linh (Tổng MQ)</strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span>•</span>
                    <span>Tải trọng: <strong class="text-slate-200 font-mono" x-text="formatNumber(selectedVehicle?.capacity_kg || 2500) + ' kg'"></strong></span>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. BANNER TRẠNG THÁI XUẤT BẾN (Chỉ hiện nút Xuất Bến khi 100% đơn Đã Kiểm Hàng) -->
    <!-- ========================================================================= -->
    <!-- Trường hợp 1: Xe đã đủ điều kiện xuất bến (100% Đơn Đã Kiểm Hàng) -->
    <div x-show="!isDeparted && isReadyToDepart"
         class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-emerald-950/70 via-emerald-900/50 to-slate-900 border-2 border-emerald-500/70 text-emerald-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-lg shadow-emerald-950/40">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/30 text-xl font-black">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-black text-sm sm:text-base text-white tracking-tight">
                        Xe đã đủ điều kiện xuất bến (100% Đơn Đã Kiểm Hàng)
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400/20 text-emerald-300 border border-emerald-400/40 animate-pulse">
                        SẴN SÀNG XUẤT BẾN
                    </span>
                </div>
                <p class="text-xs text-emerald-200/80 mt-0.5 font-medium">
                    Toàn bộ <span class="font-bold text-white font-mono" x-text="orders.length"></span> đơn hàng trên xe đã được kiểm hàng đạt chuẩn 100%. Lái xe bấm "🚀 Bấm Xuất Bến" để bắt đầu lộ trình.
                </p>
            </div>
        </div>

        <button type="button" @click="departTrip()" :disabled="isDeparting"
                class="px-5 py-2.5 rounded-xl font-black text-xs sm:text-sm text-emerald-950 bg-gradient-to-r from-emerald-300 to-emerald-400 hover:from-emerald-200 hover:to-emerald-300 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/25 shrink-0 cursor-pointer hover:scale-105 active:scale-95 disabled:opacity-50">
            <i class="fa-solid fa-rocket text-sm"></i>
            <span x-text="isDeparting ? 'Đang xuất bến...' : 'Bấm Xuất Bến (Bắt đầu giao)'"></span>
        </button>
    </div>

    <!-- Trường hợp 2: Xe chưa xuất bến và CHƯA đủ điều kiện (có đơn chưa kiểm hàng hoặc chưa có đơn xếp) -->
    <div x-show="!isDeparted && !isReadyToDepart"
         class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-amber-950/60 via-slate-900 to-slate-950 border border-amber-500/40 text-amber-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-md">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0 shadow-md text-xl font-black">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-black text-sm sm:text-base text-white tracking-tight">
                        <template x-if="orders.length === 0">
                            <span>Xe chưa có đơn hàng được xếp</span>
                        </template>
                        <template x-if="orders.length > 0">
                            <span>Chưa đủ điều kiện xuất bến (Đang chờ kiểm hàng)</span>
                        </template>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/30">
                        <template x-if="orders.length === 0">
                            <span>CHỜ XẾP ĐƠN</span>
                        </template>
                        <template x-if="orders.length > 0">
                            <span x-text="'ĐÃ KIỂM ' + checkedOrdersCount + '/' + orders.length + ' ĐƠN'"></span>
                        </template>
                    </span>
                </div>
                <p class="text-xs text-slate-300 mt-1 font-medium">
                    <template x-if="orders.length === 0">
                        <span>Xe hiện chưa có đơn hàng nào. Vui lòng vào màn hình <strong>Điều phối xe</strong> để xếp đơn vào xe này trước khi xuất bến.</span>
                    </template>
                    <template x-if="orders.length > 0">
                        <span>
                            Còn <strong class="text-amber-400 font-mono" x-text="uncheckedOrders.length"></strong> đơn hàng chưa kiểm hàng xong (yêu cầu trạng thái <strong>"Đã kiểm hàng"</strong>). Nút xuất bến chỉ hiển thị khi toàn bộ đơn đã kiểm xong.
                        </span>
                    </template>
                </p>
            </div>
        </div>
    </div>

    <!-- Banner khi xe đang trên đường quay về bãi / kho -->
    <div x-show="isReturning"
         class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-amber-950/80 via-slate-900 to-indigo-950/70 border-2 border-amber-500/70 text-amber-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-lg shadow-amber-950/40">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center shrink-0 shadow-md shadow-amber-500/30 text-xl font-black">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-black text-sm sm:text-base text-white tracking-tight">
                        Xe đang trên đường quay về bãi / kho
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/40 animate-pulse">
                        TRÊN ĐƯỜNG VỀ
                    </span>
                </div>
                <p class="text-xs text-amber-200/80 mt-0.5 font-medium">
                    Toàn bộ <span class="font-bold text-white font-mono" x-text="orders.length"></span> đơn hàng đã hoàn tất giao hàng. Khi xe về tới bãi an toàn, bấm "Đã Về Tới Bãi" để kết thúc chuyến.
                </p>
            </div>
        </div>

        <button type="button" @click="finishTripArrival()" :disabled="isReturningVehicle"
                class="px-5 py-2.5 rounded-xl font-black text-xs sm:text-sm text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 shrink-0 cursor-pointer hover:scale-105 active:scale-95 disabled:opacity-50">
            <i class="fa-solid fa-flag-checkered text-sm"></i>
            <span x-text="isReturningVehicle ? 'Đang cập nhật...' : '🏁 Đã Về Tới Bãi (Kết thúc chuyến)'"></span>
        </button>
    </div>

    <!-- Banner khi xe đã xuất bến và ĐÃ GIAO XONG TOÀN BỘ ĐƠN HÀNG (Hiển thị nút Quay Về) -->
    <div x-show="isDeparted && !isReturning && isAllCompleted"
         class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-emerald-950/80 via-teal-950/60 to-slate-900 border-2 border-emerald-400/70 text-emerald-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-lg shadow-emerald-950/40">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-slate-950 flex items-center justify-center shrink-0 shadow-md shadow-emerald-400/30 text-xl font-black">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-black text-sm sm:text-base text-white tracking-tight">
                        Đã giao xong toàn bộ đơn hàng!
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400/20 text-emerald-300 border border-emerald-400/40"
                          x-text="completedOrdersCount + '/' + orders.length + ' ĐƠN HOÀN TẤT'">
                    </span>
                </div>
                <p class="text-xs text-emerald-200/90 mt-0.5 font-medium">
                    Tất cả các điểm giao trên xe đã được phục vụ xong. Lái xe bấm nút <strong>"Quay Về"</strong> để bắt đầu di chuyển về kho bãi.
                </p>
            </div>
        </div>

        <button type="button" @click="returnToDepot()" :disabled="isReturningVehicle"
                class="px-5 py-2.5 rounded-xl font-black text-xs sm:text-sm text-slate-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-400 hover:from-amber-200 hover:to-yellow-300 transition-all flex items-center justify-center gap-2 shadow-lg shadow-amber-500/35 shrink-0 cursor-pointer hover:scale-105 active:scale-95 disabled:opacity-50">
            <i class="fa-solid fa-house-chimney text-sm"></i>
            <span x-text="isReturningVehicle ? 'Đang cập nhật...' : 'Bấm Quay Về (Về Bãi / Kho)'"></span>
        </button>
    </div>

    <!-- Banner khi xe đã xuất bến đang chạy trên đường giao hàng (còn đơn chưa xong) -->
    <div x-show="isDeparted && !isReturning && !isAllCompleted"
         class="rounded-2xl p-4 bg-gradient-to-r from-sky-950/70 via-indigo-950/50 to-slate-900 border border-sky-500/50 text-sky-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3 shadow-md">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center shrink-0 text-lg">
                <i class="fa-solid fa-truck-moving"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 font-bold text-white text-sm">
                    <span>Xe đang trên lộ trình đi giao hàng thực tế</span>
                    <span class="px-2 py-0.5 rounded text-[10px] bg-sky-500/30 text-sky-200 border border-sky-400/30 font-mono font-bold" x-text="'Xuất bến lúc: ' + (departureTimeStr || '08:00')"></span>
                </div>
                <p class="text-xs text-slate-300 mt-0.5">
                    Lái xe đến từng điểm giao, đối chiếu hàng hóa và bấm "Giao Đơn Này" để xác nhận số tiền thu hoặc lập phiếu trả hàng.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="px-3.5 py-2 rounded-xl text-xs font-bold text-sky-200 bg-sky-950/80 border border-sky-600/40 flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-route text-sky-400"></i>
                <span x-text="'Tiến độ: ' + completedOrdersCount + '/' + orders.length + ' đơn (Còn ' + pendingOrdersCount + ' đơn)'"></span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. 5 THẺ KPI TIẾN ĐỘ & TÀI CHÍNH (Exact 5 cards from Screenshot 1)        -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 text-xs">
        
        <!-- 1. Tiến độ giao hàng -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-2 flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 font-bold">
                <span>Tiến độ giao hàng</span>
                <i class="fa-solid fa-box text-purple-600 text-sm"></i>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight"
                     x-text="completedOrdersCount + '/' + orders.length + ' đơn'">
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold mt-1">
                    <span>Đã hoàn thành</span>
                    <span class="font-mono font-bold text-purple-700" x-text="progressPercent + '%'"></span>
                </div>
                <!-- Progress bar -->
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                    <div class="bg-purple-600 h-1.5 rounded-full transition-all duration-500" :style="'width: ' + progressPercent + '%'"></div>
                </div>
            </div>
        </div>

        <!-- 2. Tiền mặt Lái xe đang cầm -->
        <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-200 shadow-2xs space-y-2 flex flex-col justify-between">
            <div class="flex items-center justify-between text-amber-800 font-bold">
                <span>Tiền mặt Lái xe đang cầm</span>
                <i class="fa-solid fa-sack-dollar text-amber-600 text-base"></i>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-amber-950 font-mono tracking-tight"
                     x-text="formatNumber(cashInHand) + ' đ'">
                </div>
                <div class="text-[11px] text-amber-800/80 font-medium mt-1 flex items-center gap-1">
                    <i class="fa-regular fa-clock text-[10px]"></i>
                    <span>Nộp về thủ quỹ cuối ca</span>
                </div>
            </div>
            <button type="button" @click="openHandoverModal()"
                    class="w-full py-1.5 px-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-[11px] transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                <i class="fa-solid fa-money-check-dollar"></i>
                <span x-text="hasPendingCodSubmission ? 'Đã gửi nộp COD (Gửi lại) →' : 'Nộp tiền COD cho Kế toán →'"></span>
            </button>
        </div>

        <!-- 3. Đã thanh toán VietQR -->
        <div class="bg-emerald-50/60 rounded-2xl p-4 border border-emerald-200 shadow-2xs space-y-2 flex flex-col justify-between">
            <div class="flex items-center justify-between text-emerald-800 font-bold">
                <span>Đã thanh toán VietQR</span>
                <i class="fa-solid fa-qrcode text-emerald-600 text-base"></i>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-emerald-950 font-mono tracking-tight"
                     x-text="formatNumber(vietQrAmount) + ' đ'">
                </div>
                <div class="text-[11px] text-emerald-800/80 font-medium mt-1 flex items-center gap-1">
                    <i class="fa-regular fa-circle-check text-[10px] text-emerald-600"></i>
                    <span>Đã vào TK Ngân hàng</span>
                </div>
            </div>
        </div>

        <!-- 4. Ký Ghi nợ đại lý -->
        <div class="bg-purple-50/60 rounded-2xl p-4 border border-purple-200 shadow-2xs space-y-2 flex flex-col justify-between">
            <div class="flex items-center justify-between text-purple-800 font-bold">
                <span>Ký Ghi nợ đại lý</span>
                <i class="fa-solid fa-file-invoice text-purple-600 text-base"></i>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-purple-950 font-mono tracking-tight"
                     x-text="formatNumber(debtAmount) + ' đ'">
                </div>
                <div class="text-[11px] text-purple-800/80 font-medium mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-scale-balanced text-[10px] text-purple-600"></i>
                    <span>Đối soát nợ cuối tháng</span>
                </div>
            </div>
        </div>

        <!-- 5. Hàng hoàn trả trên xe -->
        <div class="bg-rose-50/60 rounded-2xl p-4 border border-rose-200 shadow-2xs space-y-2 flex flex-col justify-between">
            <div class="flex items-center justify-between text-rose-800 font-bold">
                <span>Hàng hoàn trả trên xe</span>
                <i class="fa-solid fa-rotate-left text-rose-600 text-base"></i>
            </div>
            <div>
                <div class="flex items-baseline justify-between">
                    <span class="text-xl sm:text-2xl font-black text-rose-950 font-mono tracking-tight"
                          x-text="returnsList.length + ' món'"></span>
                    <span class="text-xs font-mono font-bold text-rose-700"
                          x-text="formatNumber(returnsTotalAmount) + ' đ'"></span>
                </div>
                <div class="text-[11px] text-rose-700/80 font-medium mt-1 cursor-pointer hover:underline" @click="showReturnsModal = true">
                    Bấm xem danh sách chờ về →
                </div>
            </div>
            <button type="button" @click="showIntakeWarehouseModal = true"
                    class="w-full py-1.5 px-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-[11px] transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                <i class="fa-solid fa-warehouse"></i>
                <span>Hoàn hàng vào kho →</span>
            </button>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 4. SEARCH & STATUS FILTER TABS (Ảnh 1)                                     -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl p-3 border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
        
        <!-- Search input -->
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-3 pointer-events-none"></i>
            <input type="text" x-model="searchQuery"
                   placeholder="Tìm theo mã đơn, tên đại lý, số điện thoại, địa chỉ..."
                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-800 focus:outline-none focus:border-sky-500 focus:bg-white text-xs transition-colors">
        </div>

        <!-- Filter tabs: Tất cả, Chờ giao, Đã giao xong, Không thành công -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5">
            <button type="button" @click="statusFilter = 'all'"
                    class="px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                <span>Tất cả</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'all' ? 'bg-white/20' : 'bg-slate-200'" x-text="orders.length"></span>
            </button>

            <button type="button" @click="statusFilter = 'pending'"
                    class="px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-2xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200'">
                <i class="fa-regular fa-clock text-[11px]"></i>
                <span>Chờ giao</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'pending' ? 'bg-white/20' : 'bg-amber-200'" x-text="pendingOrdersCount"></span>
            </button>

            <button type="button" @click="statusFilter = 'completed'"
                    class="px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200'">
                <i class="fa-solid fa-check text-[11px]"></i>
                <span>Đã giao xong</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'completed' ? 'bg-white/20' : 'bg-emerald-200'" x-text="completedOrdersCount"></span>
            </button>

            <button type="button" @click="statusFilter = 'failed'"
                    class="px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'failed' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200'">
                <i class="fa-solid fa-xmark text-[11px]"></i>
                <span>Không thành công</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'failed' ? 'bg-white/20' : 'bg-rose-200'" x-text="failedOrdersCount"></span>
            </button>
        </div>

    </div>

    <!-- Banner Thông báo Đang chờ Kế toán duyệt COD -->
    <div x-show="hasPendingCodSubmission" x-cloak class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-950 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-900 flex items-center justify-center font-bold shrink-0">
                <i class="fa-solid fa-hourglass-half text-sm animate-pulse"></i>
            </div>
            <div>
                <div class="font-extrabold text-amber-900">
                    Đang Chờ Kế Toán Duyệt COD:
                    <span class="font-mono text-amber-950 font-black ml-1" x-text="formatNumber(pendingCodInfo.amount) + ' đ'"></span>
                </div>
                <div class="text-[11px] text-amber-800 mt-0.5">
                    Biên bản: <strong class="font-mono" x-text="pendingCodInfo.code"></strong> · Chỉ định: <strong x-text="pendingCodInfo.accountantName"></strong>.
                    <span class="text-amber-700 ml-1">Đơn hàng sẽ tự động giải phóng sau khi Kế toán kiểm đếm & duyệt tại mục Bán hàng > Kiểm COD.</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
            <button type="button" @click="checkPendingCodStatus()" :disabled="isCheckingCodStatus"
                    class="px-3 py-1.5 bg-amber-200 hover:bg-amber-300 text-amber-950 font-bold rounded-xl text-xs cursor-pointer flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': isCheckingCodStatus }"></i>
                <span>Kiểm tra trạng thái duyệt</span>
            </button>
        </div>
    </div>

    <!-- Banner Thông báo Đang chờ Thủ kho duyệt nhập kho hoàn trả -->
    <div x-show="hasPendingReturnsSubmission" x-cloak class="p-3.5 bg-rose-50 border border-rose-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-rose-950 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-200 text-rose-900 flex items-center justify-center font-bold shrink-0">
                <i class="fa-solid fa-boxes-packing text-sm animate-pulse"></i>
            </div>
            <div>
                <div class="font-extrabold text-rose-900">
                    Đang Chờ Thủ Kho Duyệt Nhập Kho Hoàn Trả:
                    <span class="font-mono text-rose-950 font-black ml-1" x-text="returnsList.length + ' phiếu (' + formatNumber(returnsTotalAmount) + ' đ)'"></span>
                </div>
                <div class="text-[11px] text-rose-800 mt-0.5">
                    Thủ kho chỉ định: <strong x-text="pendingReturnsInfo.staffName || intakeForm.intakeUserName || 'Thủ kho'"></strong>.
                    <span class="text-rose-700 ml-1">Hàng hoàn trả sẽ được gỡ bỏ khỏi xe và cộng tồn kho sau khi Thủ kho duyệt tại mục Kho hàng > Nhập kho.</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
            <button type="button" @click="checkPendingReturnsStatus()" :disabled="isCheckingReturnsStatus"
                    class="px-3 py-1.5 bg-rose-200 hover:bg-rose-300 text-rose-950 font-bold rounded-xl text-xs cursor-pointer flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': isCheckingReturnsStatus }"></i>
                <span>Kiểm tra duyệt hoàn kho</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. DANH SÁCH THẺ ĐƠN HÀNG (Exact cards from Screenshot 2)                 -->
    <!-- ========================================================================= -->
    <div class="space-y-3.5">
        <template x-for="(order, index) in filteredOrders" :key="order.id">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs hover:border-slate-300 transition-all overflow-hidden space-y-3 p-4 sm:p-5">
                
                <!-- Row 1: Sequence badge + Order code + Payment badge + Tier + Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Vòng tròn số thứ tự điểm giao -->
                        <span class="w-7 h-7 rounded-xl bg-purple-600 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-2xs"
                              x-text="index + 1"></span>

                        <!-- Mã đơn hàng -->
                        <span class="font-mono font-black text-xs text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200"
                              x-text="order.order_code"></span>

                        <!-- Trạng thái kiểm hàng của đơn -->
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold border"
                              :class="isOrderChecked(order) ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-900 border-amber-300'">
                            <i :class="isOrderChecked(order) ? 'fa-solid fa-clipboard-check text-emerald-600 text-[10px]' : 'fa-regular fa-clock text-amber-600 text-[10px]'"></i>
                            <span x-text="order.status"></span>
                        </span>

                        <!-- Trạng thái thanh toán badge -->
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold border"
                              :class="getPaymentBadgeClass(order.payment_status)">
                            <i class="fa-regular fa-clock text-[10px]"></i>
                            <span x-text="order.payment_status || 'Chưa thu tiền'"></span>
                        </span>

                        <!-- Đại lý Tier -->
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200"
                              x-text="order.dealer?.tier || 'Đại lý Cấp 1'"></span>

                        <!-- Shortage / Return Alert Badges -->
                        <template x-if="hasReturns(order)">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1">
                                <i class="fa-solid fa-rotate-left text-[9px]"></i>
                                <span>CÓ TRẢ HÀNG</span>
                            </span>
                        </template>
                    </div>

                    <!-- Right Buttons: Xem đơn, Phiếu giao, Trạng thái xuất bến -->
                    <div class="flex items-center gap-2 shrink-0">
                        <!-- Nút xác nhận đã kiểm hàng nếu đơn chưa kiểm -->
                        <template x-if="!isOrderChecked(order) && !isDeparted">
                            <button type="button" @click="quickConfirmChecked(order)"
                                    class="px-2.5 py-1 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 transition-colors flex items-center gap-1 cursor-pointer"
                                    title="Xác nhận đơn hàng này đã kiểm hàng xong">
                                <i class="fa-solid fa-check text-emerald-600"></i>
                                <span>Xác nhận đã kiểm</span>
                            </button>
                        </template>

                        <button type="button" @click="openViewOrderModal(order)"
                                class="px-2.5 py-1 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition-colors flex items-center gap-1 cursor-pointer">
                            <i class="fa-regular fa-eye text-slate-400"></i>
                            <span>Xem đơn</span>
                        </button>

                        <!-- Button Giao đơn này / Chờ xe xuất bến / Đã giao -->
                        <template x-if="isOrderCompleted(order)">
                            <span class="px-3 py-1.5 rounded-xl font-black text-xs text-emerald-800 bg-emerald-100 border border-emerald-300 flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                <span>Đã giao xong</span>
                            </span>
                        </template>

                        <template x-if="!isOrderCompleted(order) && !isDeparted">
                            <span class="px-3 py-1.5 rounded-xl font-bold text-xs text-slate-600 bg-slate-100 border border-slate-200 flex items-center gap-1.5"
                                  title="Lái xe bấm 'Bấm Xuất Bến' ở trên đầu trang để bắt đầu mở quyền giao đơn">
                                <i class="fa-solid fa-lock text-slate-400"></i>
                                <span>Chờ xe xuất bến</span>
                            </span>
                        </template>

                        <template x-if="!isOrderCompleted(order) && isDeparted">
                            <a :href="'/delivery/orders/' + order.id + '/confirm'"
                               class="px-4 py-1.5 rounded-xl font-black text-xs text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-md shadow-purple-600/25 transition-all flex items-center gap-1.5 cursor-pointer hover:scale-105 active:scale-95">
                                <i class="fa-solid fa-truck-ramp-box"></i>
                                <span>Giao Đơn Này (Xác nhận)</span>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Row 2: Customer Name -->
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight"
                        x-text="order.customer_name"></h2>
                </div>

                <!-- Row 3: Address & Google Maps link -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs pt-1">
                    <div class="flex items-start gap-1.5 text-slate-700 font-semibold">
                        <i class="fa-solid fa-location-dot text-rose-500 mt-0.5"></i>
                        <span>Địa chỉ giao: <span class="text-slate-900 font-bold" x-text="order.customer_address || 'Địa chỉ đang cập nhật'"></span></span>
                    </div>

                    <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(order.customer_address || order.customer_name)"
                       target="_blank"
                       class="text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline flex items-center gap-1 shrink-0">
                        <i class="fa-solid fa-diamond-turn-right text-rose-500"></i>
                        <span>Chỉ đường Maps ↗</span>
                    </a>
                </div>

                <!-- Row 4: Phone & Call Button -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-1.5 text-slate-700 font-semibold">
                        <i class="fa-solid fa-phone text-sky-600"></i>
                        <span>Người liên hệ / SĐT: <strong class="font-mono text-slate-900 font-bold" x-text="order.customer_phone || 'Chưa có SĐT'"></strong></span>
                    </div>

                    <template x-if="order.customer_phone">
                        <a :href="'tel:' + order.customer_phone"
                           class="px-3 py-1 rounded-xl text-xs font-black text-white bg-purple-600 hover:bg-purple-700 transition-colors flex items-center gap-1.5 shrink-0 shadow-2xs">
                            <i class="fa-solid fa-phone-volume"></i>
                            <span>Gọi khách ngay</span>
                        </a>
                    </template>
                </div>

                <!-- Row 5: Financials Row (Tổng tiền, Cần thu tại điểm giao, Số mặt hàng) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                        <div class="text-[11px] text-slate-500 font-bold" x-text="hasReturns(order) ? 'Tổng tiền gốc:' : 'Tổng tiền hàng gốc:'"></div>
                        <div class="text-sm sm:text-base font-black text-slate-800 font-mono mt-0.5"
                             x-text="formatNumber(order.total_amount) + ' đ'"></div>
                        <template x-if="hasReturns(order)">
                            <div class="mt-1 pt-1 border-t border-slate-200/80 text-[11px] flex items-center justify-between text-rose-600 font-bold">
                                <span>Hàng trả lại:</span>
                                <span class="font-mono" x-text="'-' + formatNumber(order.total_returned_amount || (order.items?.reduce((s, i) => s + (Number(i.returned_qty || 0) * Number(i.unit_price || 0)), 0) || 0)) + ' đ'"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Highlighted Box Cần Thu Tại Điểm Giao (Nền vàng nổi bật) -->
                    <div class="p-3 bg-amber-50/80 rounded-xl border-2 border-amber-300/80 text-xs">
                        <div class="text-[11px] text-amber-900 font-bold flex items-center justify-between">
                            <span>Cần thu tại điểm giao:</span>
                            <span class="text-[10px] text-amber-700 font-bold" x-text="order.payment_status === 'Đã thanh toán' ? '(Đã TT)' : '(Thu tiền)'"></span>
                        </div>
                        <div class="text-base sm:text-lg font-black text-amber-950 font-mono mt-0.5"
                             x-text="getCollectionAmountDisplay(order)"></div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs flex items-center justify-between">
                        <div>
                            <div class="text-[11px] text-slate-500 font-bold">Hàng hóa dỡ:</div>
                            <div class="text-sm font-black text-slate-800 mt-0.5"
                                 x-text="(order.items ? order.items.length : 0) + ' mặt hàng'"></div>
                        </div>
                        <button type="button" @click="order._showItems = !order._showItems"
                                class="text-sky-600 hover:text-sky-700 font-bold text-xs flex items-center gap-1 cursor-pointer">
                            <span x-text="order._showItems ? 'Thu gọn' : 'Chi tiết'"></span>
                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="order._showItems ? 'rotate-180' : ''"></i>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Items Table -->
                <div x-show="order._showItems" x-collapse class="pt-2 border-t border-slate-100">
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold text-[11px]">
                                <tr>
                                    <th class="p-2.5">Sản phẩm</th>
                                    <th class="p-2.5 text-center">ĐVT</th>
                                    <th x-show="hasReturns(order)" class="p-2.5 text-right text-slate-600">SL Xuất</th>
                                    <th x-show="hasReturns(order)" class="p-2.5 text-right text-rose-600 font-bold">SL Trả</th>
                                    <th class="p-2.5 text-right" :class="hasReturns(order) ? 'text-emerald-700 font-bold' : ''">SL Giao</th>
                                    <th x-show="!hasReturns(order)" class="p-2.5 text-right">Đơn giá</th>
                                    <th class="p-2.5 text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <template x-for="it in (order.items || [])" :key="it.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-2.5 font-medium">
                                            <div class="font-bold text-slate-900" x-text="it.name"></div>
                                            <div class="text-[10px] text-slate-400 font-mono" x-text="it.sku"></div>
                                        </td>
                                        <td class="p-2.5 text-center text-slate-600" x-text="it.unit"></td>
                                        <td x-show="hasReturns(order)" class="p-2.5 text-right font-mono text-slate-500" x-text="formatNumber(it.quantity)"></td>
                                        <td x-show="hasReturns(order)" class="p-2.5 text-right font-bold font-mono text-rose-600" x-text="formatNumber(it.returned_qty || 0)"></td>
                                        <td class="p-2.5 text-right font-black font-mono"
                                            :class="hasReturns(order) ? 'text-emerald-700' : 'text-purple-700'"
                                            x-text="formatNumber(getItemDeliveredQty(order, it))"></td>
                                        <td x-show="!hasReturns(order)" class="p-2.5 text-right font-mono text-slate-700" x-text="formatNumber(it.unit_price) + ' đ'"></td>
                                        <td class="p-2.5 text-right font-black font-mono text-slate-900"
                                            x-text="formatNumber(getItemAmount(order, it)) + ' đ'"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot x-show="hasReturns(order)" class="bg-slate-50 font-bold border-t border-slate-200 text-xs">
                                <tr>
                                    <td colspan="2" class="p-2.5 text-slate-700">Tổng cộng thực giao:</td>
                                    <td class="p-2.5 text-right font-mono text-slate-500" x-text="formatNumber(order.items?.reduce((s, i) => s + Number(i.quantity || 0), 0) || 0)"></td>
                                    <td class="p-2.5 text-right font-mono text-rose-600 font-bold" x-text="formatNumber(order.items?.reduce((s, i) => s + Number(i.returned_qty || 0), 0) || 0)"></td>
                                    <td class="p-2.5 text-right font-mono text-emerald-700 font-black" x-text="formatNumber(order.items?.reduce((s, i) => s + getItemDeliveredQty(order, i), 0) || 0)"></td>
                                    <td class="p-2.5 text-right font-mono font-black text-slate-900" x-text="formatNumber(order.items?.reduce((s, i) => s + getItemAmount(order, i), 0) || 0) + ' đ'"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Row 6: Notes / Special Instructions -->
                <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-100 text-xs text-slate-600 font-medium flex items-start gap-2">
                    <i class="fa-regular fa-clipboard text-slate-400 mt-0.5"></i>
                    <div>
                        <span class="font-bold text-slate-700">Nhật ký / Ghi chú:</span>
                        <span class="text-slate-600 italic ml-1" x-text="order.notes || 'Giao hàng đúng giờ và kiểm đếm theo phiếu xuất kho.'"></span>
                    </div>
                </div>

            </div>
        </template>

        <!-- Card tổng kết ở cuối danh sách đơn khi đã giao xong 100% toàn bộ đơn hàng -->
        <div x-show="isDeparted && !isReturning && isAllCompleted"
             class="bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 border-2 border-emerald-400/80 rounded-3xl p-6 text-center space-y-3 shadow-md">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-white flex items-center justify-center mx-auto text-2xl shadow-md shadow-emerald-500/30">
                <i class="fa-solid fa-flag-checkered"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">Đã giao xong toàn bộ đơn hàng của chuyến này!</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Toàn bộ <span class="font-bold font-mono text-emerald-700" x-text="orders.length"></span> đơn hàng đã được giao hoàn tất. Lái xe bấm nút <strong>Quay Về</strong> để bắt đầu di chuyển về bãi xe / kho công ty.
                </p>
            </div>
            <div class="pt-1">
                <button type="button" @click="returnToDepot()" :disabled="isReturningVehicle"
                        class="px-6 py-3 rounded-2xl font-black text-sm text-slate-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-400 hover:from-amber-200 hover:to-yellow-300 transition-all inline-flex items-center gap-2 shadow-xl shadow-amber-500/30 cursor-pointer hover:scale-105 active:scale-95 disabled:opacity-50">
                    <i class="fa-solid fa-house-chimney text-base"></i>
                    <span x-text="isReturningVehicle ? 'Đang cập nhật...' : 'Bấm Quay Về (Về Bãi / Kho)'"></span>
                </button>
            </div>
        </div>

        <!-- Card khi xe đang trên đường quay về bãi / kho -->
        <div x-show="isReturning"
             class="bg-gradient-to-r from-amber-50 via-slate-50 to-indigo-50 border-2 border-amber-400/80 rounded-3xl p-6 text-center space-y-3 shadow-md">
            <div class="w-14 h-14 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center mx-auto text-2xl shadow-md shadow-amber-500/30">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">Xe đang trên đường quay về bãi / kho</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Khi xe đã về đến bãi an toàn, vui lòng xác nhận để hoàn tất toàn bộ hành trình chuyến đi.
                </p>
            </div>
            <div class="pt-1">
                <button type="button" @click="finishTripArrival()" :disabled="isReturningVehicle"
                        class="px-6 py-3 rounded-2xl font-black text-sm text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 transition-all inline-flex items-center gap-2 shadow-xl shadow-emerald-600/30 cursor-pointer hover:scale-105 active:scale-95 disabled:opacity-50">
                    <i class="fa-solid fa-flag-checkered text-base"></i>
                    <span x-text="isReturningVehicle ? 'Đang cập nhật...' : '🏁 Đã Về Tới Bãi / Kho (Kết thúc chuyến)'"></span>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="filteredOrders.length === 0"
             class="bg-white rounded-3xl p-12 border border-slate-200 text-center space-y-3 shadow-2xs">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">Không có đơn hàng nào khớp bộ lọc trên xe này</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                Hiện tại xe <strong class="font-mono text-slate-700" x-text="selectedVehicle?.plate_number"></strong> chưa có đơn hàng nào cần xử lý trong danh sách này.
            </p>
            <div class="pt-2">
                <a href="{{ url('/delivery-dispatch') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-route"></i>
                    <span>Sang màn hình Điều Phối Xe để xếp đơn vào xe</span>
                </a>
            </div>
        </div>
    </div>
    <!-- ========================================================================= -->
    <!-- 7. MODALS PHỤ TRỢ (Duyệt COD, Hoàn hàng kho, Quyết toán, Chi tiết đơn...)   -->
    <!-- ========================================================================= -->

    <!-- Modal 1: Bàn Giao Tiền COD Cho Kế Toán (Lái xe gửi duyệt) -->
    <div x-show="showCodApprovalModal" x-cloak class="fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showCodApprovalModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Bàn Giao Tiền COD Cho Kế Toán</h3>
                        <p class="text-xs text-slate-500 font-medium">Lái xe lập biên bản bàn giao nộp tiền COD để Kế toán kiểm đếm & duyệt giải phóng đơn</p>
                    </div>
                </div>
                <button type="button" @click="showCodApprovalModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200 flex items-center justify-between">
                    <div>
                        <div class="text-slate-600 font-medium">Tổng tiền mặt lái xe đang cầm trên xe:</div>
                        <div class="text-xl font-black text-amber-950 font-mono" x-text="formatNumber(cashInHand) + ' đ'"></div>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-amber-200/80 text-amber-950 font-extrabold text-xs" x-text="selectedVehicle?.plate_number"></span>
                </div>

                <div class="space-y-2">
                    <label class="font-bold text-slate-700 block">Số tiền thực tế lái xe nộp về quỹ:</label>
                    <input type="number" step="1000" x-model="codApprovalForm.amount"
                           class="w-full p-2.5 rounded-xl border border-slate-300 font-mono font-black text-base text-slate-900 focus:outline-none focus:border-indigo-600 bg-white">
                </div>

                <!-- Bắt buộc chọn Kế toán / Thủ quỹ xác nhận -->
                <div class="space-y-2">
                    <label class="font-bold text-slate-700 block">
                        Chọn Kế toán / Thủ quỹ nhận tiền bàn giao <span class="text-rose-500">*</span>:
                    </label>
                    <select x-model="codApprovalForm.accountantId" @change="onAccountantSelectChange($event)"
                            class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-indigo-600 bg-white">
                        <option value="">-- Bắt buộc chọn Kế toán / Thủ quỹ nhận tiền --</option>
                        <template x-for="acc in accountants" :key="acc.id">
                            <option :value="acc.id" x-text="acc.name + ' (' + (acc.department || 'Kế toán') + ')'"></option>
                        </template>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="font-bold text-slate-700 block">Ghi chú của Lái xe khi nộp tiền:</label>
                    <textarea rows="2" x-model="codApprovalForm.notes" placeholder="VD: Bàn giao tiền mặt COD cuối ca..."
                              class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-slate-800 text-xs bg-white"></textarea>
                </div>

                <!-- Chú thích quy trình giải phóng đơn -->
                <div class="p-3 bg-sky-50 rounded-2xl border border-sky-200 text-sky-900 flex items-start gap-2 text-[11px]">
                    <i class="fa-solid fa-circle-info text-sky-600 mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="font-bold text-sky-950">Quy trình nộp & kiểm COD:</strong>
                        <span class="text-sky-800 ml-1">
                            Sau khi gửi yêu cầu, thông tin sẽ được chuyển tới menu <strong>Bán hàng > Kiểm COD</strong>. Kế toán sẽ kiểm đếm tiền mặt thực tế và xác nhận duyệt. <strong>Sau khi Kế toán xác nhận duyệt thì toàn bộ các đơn hàng đã giao mới được giải phóng ra khỏi xe</strong>.
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showCodApprovalModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmSettleCash()" :disabled="isSettlingCash"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-indigo-600/20 disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane text-indigo-200"></i>
                    <span x-text="isSettlingCash ? 'Đang gửi yêu cầu...' : 'Gửi Kế Toán Duyệt COD'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Hoàn Hàng Vào Kho -->
    <div x-show="showIntakeWarehouseModal" x-cloak class="fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showIntakeWarehouseModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Bàn Giao Hàng Hoàn Trả Cho Thủ Kho</h3>
                        <p class="text-xs text-slate-500 font-medium">Gửi biên bản hoàn trả cho Thủ kho kiểm đếm và duyệt nhập kho</p>
                    </div>
                </div>
                <button type="button" @click="showIntakeWarehouseModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-rose-50 rounded-2xl border border-rose-200">
                    <div class="text-slate-600 font-medium">Hàng hoàn trả đang chờ bàn giao trên xe:</div>
                    <div class="text-lg font-black text-rose-950 font-mono mt-0.5"
                         x-text="returnsList.length + ' phiếu hoàn trả (' + formatNumber(returnsTotalAmount) + ' đ)'"></div>
                </div>

                <!-- Bắt buộc chọn Thủ kho / Người xác nhận nhập kho -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-700 block">
                            Thủ kho nhận bàn giao <span class="text-rose-500">*</span>:
                        </label>
                        <select x-model="intakeForm.intakeUserId" @change="onIntakeUserSelectChange($event)"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-rose-600 bg-white">
                            <option value="">-- Bắt buộc chọn Thủ kho --</option>
                            <template x-for="staff in warehouseStaff" :key="staff.id">
                                <option :value="staff.id" x-text="staff.name + ' (' + (staff.department || 'Kho') + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-700 block">Kho lưu trữ bàn giao:</label>
                        <select x-model="intakeForm.warehouseId"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-rose-600 bg-white">
                            <template x-for="wh in warehouses" :key="wh.id">
                                <option :value="wh.id" x-text="wh.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Ghi chú biên bản bàn giao:</label>
                    <input type="text" x-model="intakeForm.notes" placeholder="VD: Bàn giao hàng hoàn trả cuối ca cho thủ kho kiểm đếm..."
                           class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-xs text-slate-800 focus:outline-none focus:border-rose-600 bg-white">
                </div>

                <div class="max-h-[200px] overflow-y-auto space-y-2">
                    <template x-for="r in returnsList" :key="r.id">
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-purple-700" x-text="r.slipCode || r.slip_code || ('TH-' + (r.orderCode || r.order_code))"></span>
                                <span class="text-slate-600 ml-1 font-medium" x-text="'- ' + (r.dealerName || r.dealer_name || 'Khách hàng')"></span>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="(r.items ? r.items.length : 0) + ' mặt hàng trả'"></div>
                            </div>
                            <span class="font-mono font-bold text-rose-700" x-text="formatNumber(r.totalReturnedAmount ?? r.total_returned_amount ?? 0) + ' đ'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showIntakeWarehouseModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmIntakeAllReturns()" :disabled="isIntakingReturns || returnsList.length === 0"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-rose-600/20 disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane text-rose-200"></i>
                    <span x-text="isIntakingReturns ? 'Đang gửi bàn giao...' : 'Gửi Thủ Kho Duyệt Nhập Kho'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 3: Danh Sách Hàng Trả Về -->
    <div x-show="showReturnsModal" x-cloak class="fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showReturnsModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Danh Sách Hàng Khách Trả Về Trên Xe</h3>
                        <p class="text-xs text-slate-500 font-medium">Theo dõi các mặt hàng được khách hoàn trả mang về kho</p>
                    </div>
                </div>
                <button type="button" @click="showReturnsModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="max-h-[350px] overflow-y-auto space-y-2.5 text-xs">
                <template x-for="r in returnsList" :key="r.id">
                    <div class="p-3 rounded-2xl border border-rose-200 bg-rose-50/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-purple-700" x-text="r.slipCode || r.slip_code || ('TH-' + (r.orderCode || r.order_code))"></span>
                                <span class="font-bold text-slate-900" x-text="r.dealerName || r.dealer_name || 'Khách hàng'"></span>
                            </div>
                            <span class="font-mono font-black text-rose-700" x-text="formatNumber(r.totalReturnedAmount ?? r.total_returned_amount ?? 0) + ' đ'"></span>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-rose-200/80 bg-white">
                            <table class="w-full text-left text-[11px]">
                                <thead class="bg-rose-100/60 text-rose-900 font-bold">
                                    <tr>
                                        <th class="p-2">Sản phẩm</th>
                                        <th class="p-2 text-center">SL</th>
                                        <th class="p-2 text-right">Giá</th>
                                        <th class="p-2 text-right">Lý do</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-rose-100">
                                    <template x-for="it in (r.items || [])" :key="it.id">
                                        <tr>
                                            <td class="p-2 font-medium" x-text="it.name"></td>
                                            <td class="p-2 text-center font-bold font-mono text-rose-700" x-text="(it.returnedQty ?? it.returned_qty) + ' ' + (it.unit || '')"></td>
                                            <td class="p-2 text-right font-mono" x-text="formatNumber(it.unitPrice ?? it.unit_price ?? 0) + ' đ'"></td>
                                            <td class="p-2 text-right text-slate-500 italic" x-text="it.reason || 'Khách trả hàng'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <div x-show="returnsList.length === 0" class="py-8 text-center text-slate-400 font-medium">
                    Không có hàng trả lại nào trên chuyến xe này.
                </div>
            </div>

            <div class="flex items-center justify-end pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showReturnsModal = false" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-xl cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 4: Báo Giao Không Thành Công -->
    <div x-show="showFailModal" x-cloak class="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showFailModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="font-black text-slate-900 text-base">Báo Giao Thất Bại</h3>
                </div>
                <button type="button" @click="showFailModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Lý do giao hàng không thành công:</label>
                    <select x-model="failForm.reason" class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold text-slate-900 bg-white">
                        <option value="Khách hẹn giao lại vào buổi sau">Khách hẹn giao lại vào buổi sau</option>
                        <option value="Khách vắng mặt, không liên lạc được">Khách vắng mặt, không liên lạc được</option>
                        <option value="Đường cấm tải / sự cố không vào được">Đường cấm tải / sự cố không vào được</option>
                        <option value="Khách từ chối nhận hàng do đổi ý">Khách từ chối nhận hàng do đổi ý</option>
                        <option value="Khác">Lý do khác...</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Ngày hẹn giao lại (nếu có):</label>
                    <input type="date" x-model="failForm.rescheduledDate" class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold text-slate-900 bg-white">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Ghi chú cụ thể:</label>
                    <textarea rows="2" x-model="failForm.notes" placeholder="Mô tả thêm tình hình thực tế tại điểm giao..."
                              class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-slate-800 text-xs bg-white"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showFailModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitFailDelivery()" :disabled="isSubmittingFail"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-rose-600/20 disabled:opacity-50">
                    <span x-text="isSubmittingFail ? 'Đang lưu...' : 'Xác Nhận Báo Thất Bại'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 5: Xem Chi Tiết Đơn Hàng (Read-only quick view) -->
    <div x-show="showOrderViewModal" x-cloak class="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-3xl w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto" @click.away="showOrderViewModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base" x-text="'Chi Tiết Đơn #' + (inspectOrder?.order_code || '')"></h3>
                        <p class="text-xs text-slate-500 font-medium" x-text="inspectOrder?.customer_name"></p>
                    </div>
                </div>
                <button type="button" @click="showOrderViewModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Trạng thái:</span>
                        <strong class="font-bold text-slate-900" x-text="inspectOrder?.status"></strong>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Thanh toán:</span>
                        <strong class="font-bold text-slate-900" x-text="inspectOrder?.payment_status"></strong>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Tổng tiền:</span>
                        <strong class="font-mono font-black text-purple-700" x-text="formatNumber(inspectOrder?.total_amount) + ' đ'"></strong>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Tiền lái xe thu:</span>
                        <strong class="font-mono font-black text-amber-700" x-text="formatNumber(inspectOrder?.driver_debt_amount || 0) + ' đ'"></strong>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold text-[11px]">
                            <tr>
                                <th class="p-2.5">Sản phẩm</th>
                                <th class="p-2.5 text-center">ĐVT</th>
                                <th x-show="hasReturns(inspectOrder)" class="p-2.5 text-right text-slate-600">SL Xuất</th>
                                <th x-show="hasReturns(inspectOrder)" class="p-2.5 text-right text-rose-600 font-bold">SL Trả</th>
                                <th class="p-2.5 text-right" :class="hasReturns(inspectOrder) ? 'text-emerald-700 font-bold' : ''">SL Giao</th>
                                <th x-show="!hasReturns(inspectOrder)" class="p-2.5 text-right">Đơn giá</th>
                                <th class="p-2.5 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="it in (inspectOrder?.items || [])" :key="it.id">
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900">
                                        <div x-text="it.name"></div>
                                        <div class="text-[10px] text-slate-400 font-mono" x-text="it.sku"></div>
                                    </td>
                                    <td class="p-2.5 text-center text-slate-600" x-text="it.unit"></td>
                                    <td x-show="hasReturns(inspectOrder)" class="p-2.5 text-right font-mono text-slate-500" x-text="formatNumber(it.quantity)"></td>
                                    <td x-show="hasReturns(inspectOrder)" class="p-2.5 text-right font-bold font-mono text-rose-600" x-text="formatNumber(it.returned_qty || 0)"></td>
                                    <td class="p-2.5 text-right font-mono font-black"
                                        :class="hasReturns(inspectOrder) ? 'text-emerald-700' : 'text-slate-800'"
                                        x-text="formatNumber(getItemDeliveredQty(inspectOrder, it))"></td>
                                    <td x-show="!hasReturns(inspectOrder)" class="p-2.5 text-right font-mono" x-text="formatNumber(it.unit_price) + ' đ'"></td>
                                    <td class="p-2.5 text-right font-mono font-black text-slate-900"
                                        x-text="formatNumber(getItemAmount(inspectOrder, it)) + ' đ'"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot x-show="hasReturns(inspectOrder)" class="bg-slate-50 font-bold border-t border-slate-200 text-xs">
                            <tr>
                                <td colspan="2" class="p-2.5 text-slate-700">Tổng cộng thực giao:</td>
                                <td class="p-2.5 text-right font-mono text-slate-500" x-text="formatNumber(inspectOrder?.items?.reduce((s, i) => s + Number(i.quantity || 0), 0) || 0)"></td>
                                <td class="p-2.5 text-right font-mono text-rose-600 font-bold" x-text="formatNumber(inspectOrder?.items?.reduce((s, i) => s + Number(i.returned_qty || 0), 0) || 0)"></td>
                                <td class="p-2.5 text-right font-mono text-emerald-700 font-black" x-text="formatNumber(inspectOrder?.items?.reduce((s, i) => s + getItemDeliveredQty(inspectOrder, i), 0) || 0)"></td>
                                <td class="p-2.5 text-right font-mono font-black text-slate-900" x-text="formatNumber(inspectOrder?.items?.reduce((s, i) => s + getItemAmount(inspectOrder, i), 0) || 0) + ' đ'"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-600">
                    <strong>Ghi chú đơn:</strong> <span x-text="inspectOrder?.notes || 'Không có ghi chú'"></span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
                <a :href="'/orders/' + inspectOrder?.id + '/edit'" class="text-sky-600 hover:underline font-bold flex items-center gap-1">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    <span>Mở màn hình chỉnh sửa chi tiết</span>
                </a>
                <button type="button" @click="showOrderViewModal = false" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-xl cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    </div>
</div>

<!-- Embed initial dataset from Laravel controller into JSON script -->
<script id="driver-execution-data" type="application/json">
{
    "selectedVehicle": @json($selectedVehicle ?? null),
    "orders": @json($orders ?? []),
    "deliveryReturns": @json($deliveryReturns ?? []),
    "cashHandovers": @json($cashHandovers ?? []),
    "accountants": @json($accountants ?? []),
    "warehouseStaff": @json($warehouseStaff ?? []),
    "warehouses": @json($warehouses ?? [])
}
</script>

<script>
function deliveryExecutionApp() {
    const rawData = JSON.parse(document.getElementById('driver-execution-data')?.textContent || '{}');

    return {
        selectedVehicle: rawData.selectedVehicle || null,
        orders: (rawData.orders || []).map(o => ({ ...o, _showItems: false })),
        returnsList: (rawData.deliveryReturns || []).map(r => ({
            ...r,
            slipCode: r.slipCode || r.slip_code || ('TH-' + (r.orderCode || r.order_code)),
            orderCode: r.orderCode || r.order_code,
            dealerName: r.dealerName || r.dealer_name || 'Khách hàng',
            totalReturnedAmount: Number(r.totalReturnedAmount ?? r.total_returned_amount ?? 0),
            items: (r.items || []).map(it => ({
                ...it,
                returnedQty: Number(it.returnedQty ?? it.returned_qty ?? 0),
                unitPrice: Number(it.unitPrice ?? it.unit_price ?? 0),
                amount: Number(it.amount || 0)
            }))
        })),
        cashHandovers: rawData.cashHandovers || [],
        accountants: rawData.accountants || [],
        warehouseStaff: rawData.warehouseStaff || [],
        warehouses: rawData.warehouses || [],

        searchQuery: '',
        statusFilter: 'all',

        // Action Modals State & View Navigation
        showDeliveryModal: false,
        currentView: 'list', // 'list' (danh sách đơn trên xe) | 'deliver' (trang giao đơn chuyên biệt)
        copiedField: '',
        showCodApprovalModal: false,
        showIntakeWarehouseModal: false,
        showReturnsModal: false,
        showFailModal: false,
        showOrderViewModal: false,

        // Inspecting order
        activeOrder: null,
        inspectOrder: null,

        // Thông tin tài khoản ngân hàng MBBank để sinh mã VietQR
        bankInfo: {
            bankId: 'MB',
            bankName: 'MBBank (Ngân hàng TMCP Quân Đội)',
            accountNo: '0903888999',
            accountName: 'CONG TY TNHH MANH QUAN'
        },

        // Form state for delivery confirmation
        confirmForm: {
            orderId: null,
            orderCode: '',
            hasReturns: false,
            items: [],
            recipientName: '',
            deliveryNotes: '',
            paymentMethod: 'Tiền mặt',
            collectedAmount: 0,
            finalAmount: 0,
            isDebt: false,
            appliedToThisOrder: 0,
            appliedToOldDebt: 0,
            projectedDebtAfter: 0,
        },

        // Form state for COD approval (bắt buộc chọn kế toán)
        codApprovalForm: {
            amount: 0,
            accountantId: '',
            accountantName: '',
            notes: ''
        },

        // Form state for warehouse intake (bắt buộc chọn người xác nhận)
        intakeForm: {
            intakeUserId: '',
            intakeUserName: '',
            warehouseId: {{ $warehouses->first()->id ?? 4 }},
            notes: ''
        },

        // Form state for fail delivery
        failForm: {
            reason: 'Khách hẹn giao lại vào buổi sau',
            rescheduledDate: '',
            notes: ''
        },

        // Process states
        isDeparting: false,
        isReturningVehicle: false,
        isSubmittingDelivery: false,
        isSubmittingFail: false,
        isSettlingCash: false,
        isIntakingReturns: false,
        hasPendingCodSubmission: false,
        pendingCodInfo: { code: '', amount: 0, accountantName: '' },
        isCheckingCodStatus: false,
        hasPendingReturnsSubmission: false,
        pendingReturnsInfo: { staffName: '', count: 0, amount: 0 },
        isCheckingReturnsStatus: false,

        toast: { show: false, message: '', type: 'success' },

        initApp() {
            this.recalcVehicleStats();

            // Kiểm tra xem xe này có biên bản nộp tiền đang chờ duyệt không
            const pendingHandover = (this.cashHandovers || []).find(h => 
                (h.vehicle_plate === this.selectedVehicle?.plate_number || h.vehiclePlate === this.selectedVehicle?.plate_number)
                && h.status === 'Chờ kế toán duyệt'
            );
            if (pendingHandover) {
                this.hasPendingCodSubmission = true;
                this.pendingCodInfo = {
                    code: pendingHandover.code,
                    amount: pendingHandover.handover_amount || pendingHandover.handoverAmount || 0,
                    accountantName: pendingHandover.accountant_name || pendingHandover.accountantName || 'Kế toán'
                };
            }

            // Kiểm tra xem xe này có phiếu hoàn trả nào đang chờ thủ kho duyệt không
            const pendingReturns = (this.returnsList || []).filter(r => 
                r.status === 'Chờ thủ kho duyệt nhập' || r.status === 'Chờ nhập kho lại'
            );
            if (pendingReturns.length > 0) {
                this.hasPendingReturnsSubmission = true;
                const staff = (this.warehouseStaff || []).find(s => String(s.id) === String(pendingReturns[0].intakeUserId));
                this.pendingReturnsInfo = {
                    staffName: staff ? staff.name : (this.intakeForm.intakeUserName || 'Thủ kho'),
                    count: pendingReturns.length,
                    amount: pendingReturns.reduce((s, r) => s + Number(r.totalReturnedAmount ?? r.total_returned_amount ?? 0), 0)
                };
            }

            // Kiểm tra query parameter mở trực tiếp trang giao đơn khi tải trang
            const params = new URLSearchParams(window.location.search);
            if (params.get('view') === 'deliver' && params.get('order_id')) {
                window.location.href = `/delivery/orders/${params.get('order_id')}/confirm`;
            }
        },

        get isDeparted() {
            return this.selectedVehicle?.status === 'Đang di chuyển' 
                || this.selectedVehicle?.dispatch_status === 'Đang giao hàng'
                || this.selectedVehicle?.dispatch_status === 'Trên đường về';
        },

        get isReturning() {
            return this.selectedVehicle?.dispatch_status === 'Trên đường về';
        },

        get isAllCompleted() {
            return this.orders.length > 0 && this.pendingOrdersCount === 0;
        },

        get departureTimeStr() {
            if (!this.selectedVehicle?.departure_time) return '';
            try {
                const d = new Date(this.selectedVehicle.departure_time);
                return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return '';
            }
        },

        // Kiểm tra đơn hàng đã kiểm xong chưa (yêu cầu "Đã kiểm hàng")
        isOrderChecked(o) {
            return ['Đã kiểm hàng', 'Đã kiểm', 'Đang vận chuyển', 'Đã giao', 'Giao thành công'].includes(o.status);
        },

        // Danh sách đơn chưa kiểm hàng trên xe
        get uncheckedOrders() {
            return this.orders.filter(o => !this.isOrderChecked(o));
        },

        // Số lượng đơn đã kiểm hàng
        get checkedOrdersCount() {
            return this.orders.filter(o => this.isOrderChecked(o)).length;
        },

        // Điều kiện xuất bến: Phải có đơn hàng VÀ 100% đơn hàng trên xe đã có trạng thái "Đã kiểm hàng"
        get isReadyToDepart() {
            return this.orders.length > 0 && this.uncheckedOrders.length === 0;
        },

        get completedOrdersCount() {
            return this.orders.filter(o => ['Đã giao', 'Giao thành công'].includes(o.status)).length;
        },

        get pendingOrdersCount() {
            return this.orders.filter(o => !['Đã giao', 'Giao thành công', 'Không thành công'].includes(o.status)).length;
        },

        get failedOrdersCount() {
            return this.orders.filter(o => o.status === 'Không thành công').length;
        },

        get progressPercent() {
            if (this.orders.length === 0) return 0;
            return Math.round((this.completedOrdersCount / this.orders.length) * 100);
        },

        get cashInHand() {
            return this.orders
                .filter(o => ['Đã giao', 'Giao thành công'].includes(o.status) && o.payment_method === 'Tiền mặt')
                .reduce((sum, o) => sum + Number(o.driver_debt_amount || 0), 0);
        },

        get vietQrAmount() {
            return this.orders
                .filter(o => ['Đã giao', 'Giao thành công'].includes(o.status) && (o.payment_method === 'VietQR' || o.payment_method === 'Chuyển khoản VietQR'))
                .reduce((sum, o) => sum + Number(o.total_amount || 0), 0);
        },

        get debtAmount() {
            return this.orders
                .filter(o => ['Đã giao', 'Giao thành công'].includes(o.status) && o.payment_status === 'Ghi nợ đại lý')
                .reduce((sum, o) => sum + Number(o.total_amount || 0), 0);
        },

        get returnsTotalAmount() {
            return this.returnsList.reduce((sum, r) => sum + Number(r.totalReturnedAmount ?? r.total_returned_amount ?? 0), 0);
        },

        get filteredOrders() {
            return this.orders.filter(o => {
                if (this.statusFilter === 'pending') {
                    if (['Đã giao', 'Giao thành công', 'Không thành công'].includes(o.status)) return false;
                } else if (this.statusFilter === 'completed') {
                    if (!['Đã giao', 'Giao thành công'].includes(o.status)) return false;
                } else if (this.statusFilter === 'failed') {
                    if (o.status !== 'Không thành công') return false;
                }

                if (this.searchQuery && this.searchQuery.trim()) {
                    const q = this.searchQuery.toLowerCase().trim();
                    const code = (o.order_code || '').toLowerCase();
                    const name = (o.customer_name || '').toLowerCase();
                    const phone = (o.customer_phone || '').toLowerCase();
                    const addr = (o.customer_address || '').toLowerCase();
                    return code.includes(q) || name.includes(q) || phone.includes(q) || addr.includes(q);
                }

                return true;
            });
        },

        get isExactAmount() {
            return Math.abs(Number(this.confirmForm.collectedAmount || 0) - Number(this.confirmForm.finalAmount || 0)) < 100 && !this.confirmForm.isDebt;
        },

        // ─── Actions & Helpers ─────────────────────────────────────

        recalcVehicleStats() {
            this.codApprovalForm.amount = this.cashInHand;
        },

        formatNumber(val) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val) || 0));
        },

        isOrderCompleted(order) {
            return ['Đã giao', 'Giao thành công'].includes(order.status);
        },

        getPaymentBadgeClass(status) {
            if (status === 'Đã thanh toán') return 'bg-emerald-50 text-emerald-800 border-emerald-300';
            if (status === 'Ghi nợ đại lý') return 'bg-purple-50 text-purple-800 border-purple-300';
            if (status === 'Thanh toán một phần') return 'bg-amber-50 text-amber-800 border-amber-300';
            return 'bg-amber-50 text-amber-900 border-amber-300';
        },

        hasReturns(order) {
            if (!order) return false;
            if (order.has_returns) return true;
            if (order.total_returned_amount && Number(order.total_returned_amount) > 0) return true;
            if (Array.isArray(order.items) && order.items.some(i => Number(i.returned_qty || 0) > 0)) return true;
            return false;
        },

        getItemDeliveredQty(order, it) {
            if (!it) return 0;
            if (!this.hasReturns(order)) {
                return Number(it.quantity || 0);
            }
            if (it.delivered_qty !== undefined && it.delivered_qty !== null && it.delivered_qty !== '') {
                return Number(it.delivered_qty);
            }
            const q = Number(it.quantity || 0);
            const r = Number(it.returned_qty || 0);
            return Math.max(0, q - r);
        },

        getItemAmount(order, it) {
            if (!it) return 0;
            if (!this.hasReturns(order)) {
                return Number(it.amount || (Number(it.quantity || 0) * Number(it.unit_price || 0)));
            }
            const delivQty = this.getItemDeliveredQty(order, it);
            const price = Number(it.unit_price || 0);
            return delivQty * price;
        },

        getOrderNetDeliveryAmount(order) {
            if (!order) return 0;
            if (!this.hasReturns(order)) return Number(order.total_amount || 0);
            if (Array.isArray(order.items) && order.items.length > 0) {
                return order.items.reduce((s, it) => s + this.getItemAmount(order, it), 0);
            }
            const retAmt = Number(order.total_returned_amount || 0);
            return Math.max(0, Number(order.total_amount || 0) - retAmt);
        },

        getCollectionAmountDisplay(order) {
            if (order.payment_status === 'Đã thanh toán') {
                return '0 đ (Đã thanh toán)';
            }
            if (order.payment_status === 'Ghi nợ đại lý') {
                return '0 đ (Ký nợ đại lý)';
            }
            if (this.isOrderCompleted(order) && order.driver_debt_amount !== undefined && order.driver_debt_amount !== null) {
                return this.formatNumber(order.driver_debt_amount) + ' đ';
            }
            return this.formatNumber(this.getOrderNetDeliveryAmount(order)) + ' đ';
        },

        changeVehicle(plate) {
            window.location.href = '/driver-portal?vehicle=' + encodeURIComponent(plate);
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 4000);
        },

        // ─── Xác nhận nhanh đơn hàng đã kiểm xong ───────────────────
        async quickConfirmChecked(order) {
            if (!confirm(`Xác nhận đơn hàng #${order.order_code} đã kiểm hàng xong (chuyển sang trạng thái "Đã kiểm hàng")?`)) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/orders/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ orderId: order.id, status: 'Đã kiểm hàng' })
                });
                const data = await res.json();
                if (data.success) {
                    order.status = 'Đã kiểm hàng';
                    this.showToast(`✅ Đơn #${order.order_code} đã cập nhật sang trạng thái "Đã kiểm hàng"!`);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            }
        },

        // ─── Bấm Xuất Bến ──────────────────────────────────────────
        async departTrip() {
            if (!this.selectedVehicle) return;
            if (!this.isReadyToDepart) {
                alert('Chưa đủ điều kiện xuất bến! Còn đơn hàng chưa kiểm xong. Toàn bộ đơn hàng trên xe phải ở trạng thái "Đã kiểm hàng".');
                return;
            }
            if (!confirm('Xác nhận XE ' + this.selectedVehicle.plate_number + ' BẮT ĐẦU XUẤT BẾN ĐI GIAO HÀNG?\n\nTrạng thái các đơn hàng trên xe sẽ chuyển sang "Đang vận chuyển".')) {
                return;
            }

            this.isDeparting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/delivery/depart', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ vehiclePlate: this.selectedVehicle.plate_number })
                });
                const data = await res.json();
                if (data.success) {
                    this.selectedVehicle.status = 'Đang di chuyển';
                    this.selectedVehicle.dispatch_status = 'Đang giao hàng';
                    this.selectedVehicle.departure_time = new Date().toISOString();
                    this.orders.forEach(o => {
                        if (!this.isOrderCompleted(o)) o.status = 'Đang vận chuyển';
                    });
                    this.showToast('🚀 Xe đã xuất bến thành công! Bắt đầu lộ trình.');
                } else {
                    alert('Lỗi xuất bến: ' + (data.message || ''));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isDeparting = false;
            }
        },

        // ─── Getters cho Khách Hàng, Mã VietQR & Chuyển Khoản ───────
        get customerCodeDisplay() {
            return this.activeOrder?.dealer?.code || ('KH-' + (this.activeOrder?.dealer_id || this.activeOrder?.customer_name || 'LE'));
        },

        get qrTransferMemo() {
            const customerCode = this.customerCodeDisplay;
            const orderCode = this.activeOrder?.order_code || '';
            return `${customerCode} ${orderCode}`.trim();
        },

        get vietQrUrl() {
            const amount = Number(this.confirmForm.collectedAmount || this.confirmForm.finalAmount || 0);
            const memo = encodeURIComponent(this.qrTransferMemo);
            return `https://img.vietqr.io/image/MB-0903888999-compact2.png?amount=${amount}&addInfo=${memo}&accountName=CONG%20TY%20TNHH%20MANH%20QUAN`;
        },

        get isExactAmount() {
            return !this.confirmForm.isDebt && Number(this.confirmForm.collectedAmount) === Number(this.confirmForm.finalAmount);
        },

        selectPaymentMethod(method) {
            this.confirmForm.paymentMethod = method;
            if (method === 'Ghi nợ') {
                this.confirmForm.isDebt = true;
                this.confirmForm.collectedAmount = 0;
            } else if (method === 'VietQR') {
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            } else { // Tiền mặt
                this.confirmForm.isDebt = false;
                if (this.confirmForm.collectedAmount === 0) {
                    this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
                }
            }
            this.recalcPaymentAllocation();
        },

        async copyToClipboard(text, fieldName = '') {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(String(text));
                } else {
                    const textArea = document.createElement("textarea");
                    textArea.value = String(text);
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                }
                this.copiedField = fieldName;
                this.showToast('📋 Đã sao chép: ' + text);
                setTimeout(() => { this.copiedField = ''; }, 2000);
            } catch (err) {
                this.showToast('Không thể sao chép: ' + err.message, 'error');
            }
        },

        goBackToList() {
            this.currentView = 'list';
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete('view');
                url.searchParams.delete('order_id');
                window.history.pushState({ view: 'list' }, '', url.toString());
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) {}
        },

        // ─── Mở Màn hình Xác Nhận Giao Đơn (Chuyển sang trang deliver) ────────
        openDeliveryConfirmModal(order) {
            this.activeOrder = order;
            const itemsCopy = (order.items || []).map(it => ({
                id: it.id,
                order_item_id: it.id,
                product_id: it.product_id,
                sku: it.sku,
                name: it.name,
                unit: it.unit,
                quantity: Number(it.quantity || 1),
                unit_price: Number(it.unit_price || 0),
                amount: Number(it.amount || 0),
                returnedQty: 0,
                returnReason: ''
            }));

            const origTotal = Number(order.total_amount || 0);
            const isOrderDebt = order.payment_status === 'Ghi nợ đại lý' || order.payment_method === 'Ghi nợ' || order.payment_method === 'Ký nợ';
            const isOrderQr = order.payment_method === 'Chuyển khoản VietQR' || order.payment_method === 'VietQR';

            let initialMethod = 'Tiền mặt';
            if (isOrderDebt) initialMethod = 'Ghi nợ';
            else if (isOrderQr) initialMethod = 'VietQR';

            this.confirmForm = {
                orderId: order.id,
                orderCode: order.order_code,
                hasReturns: Boolean(order.has_returns),
                items: itemsCopy,
                recipientName: order.customer_name || '',
                deliveryNotes: '',
                paymentMethod: initialMethod,
                collectedAmount: (order.payment_status === 'Đã thanh toán' || isOrderDebt) ? 0 : origTotal,
                finalAmount: origTotal,
                isDebt: isOrderDebt,
                appliedToThisOrder: isOrderDebt ? 0 : origTotal,
                appliedToOldDebt: 0,
                projectedDebtAfter: Number(order.dealer?.current_debt || 0) + (isOrderDebt ? origTotal : 0)
            };

            this.recalcReturnTotals();
            this.currentView = 'deliver';

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('view', 'deliver');
                url.searchParams.set('order_id', order.id);
                window.history.pushState({ view: 'deliver', orderId: order.id }, '', url.toString());
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) {}
        },

        onHasReturnsToggle() {
            if (!this.confirmForm.hasReturns) {
                this.confirmForm.items.forEach(it => {
                    it.returnedQty = 0;
                    it.returnReason = '';
                });
            }
            this.recalcReturnTotals();
        },

        recalcReturnTotals() {
            let totalReturnAmt = 0;
            if (this.confirmForm.hasReturns) {
                this.confirmForm.items.forEach(it => {
                    const rQty = Math.max(0, Math.min(Number(it.quantity || 0), Number(it.returnedQty || 0)));
                    it.returnedQty = rQty;
                    totalReturnAmt += (rQty * Number(it.unit_price || 0));
                });
            }
            const origTotal = Number(this.activeOrder?.total_amount || 0);
            this.confirmForm.finalAmount = Math.max(0, origTotal - totalReturnAmt);

            if (!this.confirmForm.isDebt && this.confirmForm.collectedAmount > this.confirmForm.finalAmount) {
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            }
            this.recalcPaymentAllocation();
        },

        setExactPayment() {
            this.confirmForm.isDebt = false;
            this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            this.recalcPaymentAllocation();
        },

        setFullDebt() {
            this.selectPaymentMethod('Ghi nợ');
        },

        recalcPaymentAllocation() {
            const collected = Math.max(0, Number(this.confirmForm.collectedAmount || 0));
            const finalAmt = Number(this.confirmForm.finalAmount || 0);
            const curDebt = Number(this.activeOrder?.dealer?.current_debt || 0);

            if (this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ') {
                this.confirmForm.appliedToThisOrder = 0;
                this.confirmForm.appliedToOldDebt = 0;
                this.confirmForm.projectedDebtAfter = curDebt + finalAmt;
                return;
            }

            if (collected >= finalAmt) {
                this.confirmForm.appliedToThisOrder = finalAmt;
                this.confirmForm.appliedToOldDebt = collected - finalAmt;
                this.confirmForm.projectedDebtAfter = Math.max(0, curDebt - this.confirmForm.appliedToOldDebt);
            } else {
                this.confirmForm.appliedToThisOrder = collected;
                this.confirmForm.appliedToOldDebt = 0;
                this.confirmForm.projectedDebtAfter = curDebt + (finalAmt - collected);
            }
        },

        // ─── Xác Nhận Giao Hàng & Thu Tiền ─────────────────────────
        async submitDeliveryCompletion() {
            if (!this.activeOrder) return;
            if (!this.confirmForm.recipientName.trim()) {
                alert('Vui lòng nhập họ tên người nhận hàng tại điểm giao!');
                return;
            }

            let methodLabel = this.confirmForm.paymentMethod;
            if (this.confirmForm.isDebt) methodLabel = 'Ghi nợ đại lý';

            const confirmMsg = `XÁC NHẬN GIAO ĐƠN #${this.activeOrder.order_code}?\n\n` +
                `- Người nhận: ${this.confirmForm.recipientName}\n` +
                `- Hình thức: ${methodLabel}\n` +
                (this.confirmForm.isDebt ? `- Ghi nợ vào sổ đại lý: ${this.formatNumber(this.confirmForm.finalAmount)} đ\n` : `- Tiền thu: ${this.formatNumber(this.confirmForm.collectedAmount)} đ\n`) +
                (this.confirmForm.hasReturns ? `- Có hàng trả lại: trừ ${this.formatNumber(this.activeOrder.total_amount - this.confirmForm.finalAmount)} đ\n` : '- Giao đủ 100% không trả hàng\n');

            if (!confirm(confirmMsg)) return;

            this.isSubmittingDelivery = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const payload = {
                    orderId: this.activeOrder.id,
                    orderCode: this.activeOrder.order_code,
                    recipientName: this.confirmForm.recipientName,
                    deliveryNotes: this.confirmForm.deliveryNotes,
                    paymentMethod: this.confirmForm.paymentMethod,
                    collectedAmount: this.confirmForm.collectedAmount,
                    isDebt: this.confirmForm.isDebt,
                    hasReturns: this.confirmForm.hasReturns,
                    returnedItems: this.confirmForm.hasReturns ? this.confirmForm.items.filter(it => it.returnedQty > 0) : []
                };

                const res = await fetch('/api/delivery/complete-order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    this.activeOrder.status = 'Đã giao';
                    this.activeOrder.payment_status = data.order?.paymentStatus || (this.confirmForm.isDebt ? 'Ghi nợ đại lý' : 'Đã thanh toán');
                    this.activeOrder.payment_method = data.order?.paymentMethod || this.confirmForm.paymentMethod;
                    this.activeOrder.driver_debt_amount = data.order?.driverDebtAmount || (this.confirmForm.paymentMethod === 'Tiền mặt' ? this.confirmForm.collectedAmount : 0);
                    this.activeOrder.has_returns = data.order?.hasReturns || false;
                    this.activeOrder.total_returned_amount = data.order?.totalReturnedAmount || 0;

                    if (payload.hasReturns && payload.returnedItems.length > 0) {
                        this.activeOrder.has_returns = true;
                        payload.returnedItems.forEach(rit => {
                            const item = (this.activeOrder.items || []).find(i => (i.id && i.id === rit.id) || (i.sku && i.sku === rit.sku));
                            if (item) {
                                item.returned_qty = Number(rit.returnedQty || 0);
                                item.delivered_qty = Math.max(0, Number(item.quantity) - Number(rit.returnedQty || 0));
                            }
                        });
                        const newReturnSlip = {
                            id: Date.now(),
                            slipCode: 'TH-' + this.activeOrder.order_code,
                            orderCode: this.activeOrder.order_code,
                            dealerName: this.activeOrder.customer_name,
                            totalReturnedAmount: payload.returnedItems.reduce((s, it) => s + (it.returnedQty * it.unit_price), 0),
                            items: payload.returnedItems.map(it => ({
                                name: it.name,
                                returnedQty: it.returnedQty,
                                unit: it.unit,
                                unitPrice: it.unit_price,
                                reason: it.returnReason
                            }))
                        };
                        this.returnsList = [newReturnSlip, ...this.returnsList];
                    }

                    this.goBackToList();
                    this.recalcVehicleStats();
                    this.showToast('🎉 Đã xác nhận giao hàng và hoàn tất đơn thành công!');
                } else {
                    alert('Lỗi xác nhận: ' + (data.message || ''));
                }
            } catch (e) {
                alert('Lỗi kết nối: ' + e.message);
            } finally {
                this.isSubmittingDelivery = false;
            }
        },

        // ─── Báo Giao Không Thành Công ─────────────────────────────
        openFailModal() {
            this.failForm = {
                reason: 'Khách hẹn giao lại vào buổi sau',
                rescheduledDate: new Date(Date.now() + 86400000).toISOString().split('T')[0],
                notes: ''
            };
            this.showFailModal = true;
        },

        async submitFailDelivery() {
            if (!this.activeOrder) return;
            this.isSubmittingFail = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/delivery/fail-order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderId: this.activeOrder.id,
                        orderCode: this.activeOrder.order_code,
                        reason: this.failForm.reason,
                        rescheduledDate: this.failForm.rescheduledDate,
                        notes: this.failForm.notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.activeOrder.status = 'Không thành công';
                    this.showFailModal = false;
                    this.showDeliveryModal = false;
                    this.showToast('Đã ghi nhận báo giao hàng không thành công.');
                } else {
                    alert('Lỗi: ' + (data.message || ''));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isSubmittingFail = false;
            }
        },

        onAccountantSelectChange(e) {
            const acc = this.accountants.find(a => String(a.id) === String(e.target.value));
            this.codApprovalForm.accountantName = acc ? acc.name : '';
        },

        onIntakeUserSelectChange(e) {
            const staff = this.warehouseStaff.find(s => String(s.id) === String(e.target.value));
            this.intakeForm.intakeUserName = staff ? staff.name : '';
        },

        // ─── Gửi Bàn Giao Tiền COD Cho Kế Toán Duyệt ─────────────
        openHandoverModal() {
            this.codApprovalForm.amount = this.cashInHand;
            this.codApprovalForm.notes = 'Bàn giao nộp tiền COD cuối chuyến xe cho Kế toán kiểm đếm.';
            if (this.accountants.length > 0 && !this.codApprovalForm.accountantId) {
                this.codApprovalForm.accountantId = this.accountants[0].id;
                this.codApprovalForm.accountantName = this.accountants[0].name;
            }
            this.showCodApprovalModal = true;
        },

        async confirmSettleCash() {
            if (!this.codApprovalForm.accountantId) {
                alert('Vui lòng chọn Kế toán / Thủ quỹ nhận tiền bàn giao!');
                return;
            }

            const completedCodes = this.orders
                .filter(o => ['Đã giao', 'Giao thành công'].includes(o.status))
                .map(o => o.order_code);

            const confirmMsg = `Xác nhận gửi biên bản nộp tiền ${this.formatNumber(this.codApprovalForm.amount)} đ cho Kế toán [${this.codApprovalForm.accountantName}] kiểm đếm và duyệt?`;
            if (!confirm(confirmMsg)) {
                return;
            }

            this.isSettlingCash = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery-dispatches/settle-cash', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        action: 'submit', // Lái xe gửi yêu cầu bàn giao nộp tiền chờ duyệt
                        vehiclePlate: this.selectedVehicle?.plate_number,
                        driverName: this.selectedVehicle?.driver?.name,
                        accountantId: this.codApprovalForm.accountantId,
                        accountantName: this.codApprovalForm.accountantName,
                        amount: this.codApprovalForm.amount,
                        handoverAmount: this.codApprovalForm.amount,
                        orderCodes: completedCodes,
                        notes: this.codApprovalForm.notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.showCodApprovalModal = false;
                    this.hasPendingCodSubmission = true;
                    this.pendingCodInfo = {
                        code: data.code,
                        amount: this.codApprovalForm.amount,
                        accountantName: this.codApprovalForm.accountantName
                    };

                    // Cập nhật trạng thái hiển thị của các đơn hàng thành "Chờ duyệt nộp quỹ"
                    this.orders.forEach(o => {
                        if (['Đã giao', 'Giao thành công'].includes(o.status)) {
                            o.payment_status = 'Chờ duyệt nộp quỹ';
                        }
                    });

                    this.showToast(`📩 Đã gửi biên bản nộp tiền COD cho Kế toán ${this.codApprovalForm.accountantName} chờ duyệt! Đơn hàng sẽ được giải phóng ngay sau khi Kế toán kiểm đếm xong.`);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể gửi yêu cầu'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isSettlingCash = false;
            }
        },

        async checkPendingCodStatus() {
            if (!this.selectedVehicle) return;
            this.isCheckingCodStatus = true;
            try {
                const res = await fetch('/delivery-dispatches/driver-cash-handovers');
                const data = await res.json();
                if (data.handovers && Array.isArray(data.handovers)) {
                    const approved = data.handovers.find(h => 
                        (h.vehiclePlate === this.selectedVehicle?.plate_number || h.vehicle_plate === this.selectedVehicle?.plate_number)
                        && h.status === 'Đã hoàn thành'
                    );
                    if (approved) {
                        // Kế toán đã duyệt thành công -> Giải phóng đơn hàng trên xe
                        this.orders = this.orders.filter(o => !['Đã giao', 'Giao thành công', 'Đã quyết toán'].includes(o.status));
                        this.hasPendingCodSubmission = false;
                        this.recalcVehicleStats();
                        if (this.orders.length === 0) {
                            this.selectedVehicle.status = 'Sẵn sàng';
                            this.selectedVehicle.dispatch_status = 'Rảnh';
                            this.selectedVehicle.departure_time = null;
                        }
                        this.showToast(`🎉 Kế toán ${approved.approvedByName || approved.accountantName || ''} đã duyệt COD (${this.formatNumber(approved.handoverAmount || approved.handover_amount)} đ) và giải phóng các đơn hàng ra khỏi xe!`);
                        return;
                    }
                }
                this.showToast('⏳ Kế toán đang kiểm đếm tiền mặt, chưa hoàn tất duyệt. Vui lòng kiểm tra lại sau ít phút!');
            } catch (e) {
                console.error(e);
            } finally {
                this.isCheckingCodStatus = false;
            }
        },

        // ─── Gửi Bàn Giao Hàng Hoàn Trả Cho Thủ Kho Duyệt ─────────
        async confirmIntakeAllReturns() {
            if (this.returnsList.length === 0) {
                alert('Không có phiếu hàng hoàn trả nào trên xe.');
                return;
            }
            if (!this.intakeForm.intakeUserId) {
                alert('Vui lòng chọn Thủ kho nhận bàn giao!');
                return;
            }

            const confirmMsg = `Xác nhận gửi biên bản bàn giao ${this.returnsList.length} phiếu hàng hoàn trả (${this.formatNumber(this.returnsTotalAmount)} đ) cho Thủ kho [${this.intakeForm.intakeUserName}] kiểm đếm và duyệt nhập kho?`;
            if (!confirm(confirmMsg)) {
                return;
            }

            this.isIntakingReturns = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const slipIds = this.returnsList.map(r => r.id);
                const res = await fetch('/delivery-returns/intake-warehouse', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        action: 'submit',
                        slipIds: slipIds,
                        vehiclePlate: this.selectedVehicle?.plate_number,
                        intakeUserId: this.intakeForm.intakeUserId,
                        intakeUserName: this.intakeForm.intakeUserName,
                        warehouseId: this.intakeForm.warehouseId,
                        notes: this.intakeForm.notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.showIntakeWarehouseModal = false;
                    this.hasPendingReturnsSubmission = true;
                    this.pendingReturnsInfo = {
                        staffName: this.intakeForm.intakeUserName,
                        count: this.returnsList.length,
                        amount: this.returnsTotalAmount
                    };

                    this.returnsList.forEach(r => {
                        r.status = 'Chờ thủ kho duyệt nhập';
                    });

                    this.showToast(`📩 Đã gửi biên bản bàn giao ${this.returnsList.length} phiếu hoàn trả cho Thủ kho ${this.intakeForm.intakeUserName} chờ duyệt! Hàng sẽ được giải phóng ngay sau khi Thủ kho duyệt tại phân hệ Nhập kho.`);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể gửi yêu cầu bàn giao'));
                }
            } catch (e) {
                alert('Lỗi kết nối: ' + e.message);
            } finally {
                this.isIntakingReturns = false;
            }
        },

        async checkPendingReturnsStatus() {
            if (!this.selectedVehicle) return;
            this.isCheckingReturnsStatus = true;
            try {
                const res = await fetch('/delivery-returns');
                const data = await res.json();
                if (data.returns && Array.isArray(data.returns)) {
                    // Lấy tất cả phiếu của xe này
                    const currentVehicleSlips = data.returns.filter(r => 
                        (r.vehiclePlate === this.selectedVehicle?.plate_number || r.vehicle_plate === this.selectedVehicle?.plate_number)
                    );

                    // Kiểm tra các phiếu còn đang chờ duyệt trên xe
                    const stillPending = currentVehicleSlips.filter(r => 
                        ['Chở trên xe', 'Chờ thủ kho duyệt nhập', 'Chờ nhập kho lại'].includes(r.status)
                    );

                    if (stillPending.length === 0 && this.returnsList.length > 0) {
                        // Toàn bộ đã được duyệt nhập kho -> Giải phóng khỏi xe
                        this.returnsList = [];
                        this.hasPendingReturnsSubmission = false;
                        this.showToast('🎉 Thủ kho đã kiểm đếm và duyệt nhập kho lại toàn bộ hàng hoàn trả! Đã giải phóng hàng trả về khỏi xe.');
                        return;
                    } else if (stillPending.length < this.returnsList.length) {
                        // Một số phiếu đã duyệt
                        this.returnsList = stillPending;
                        this.showToast(`Đã duyệt một phần! Còn lại ${this.returnsList.length} phiếu hoàn trả trên xe.`);
                        return;
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.isCheckingReturnsStatus = false;
            }
        },

        // ─── Bấm Quay Về (Về Bãi / Kho) ──────────────────────────
        async returnToDepot() {
            if (!this.selectedVehicle) return;
            if (!confirm(`Xác nhận xe ${this.selectedVehicle.plate_number} đã hoàn thành giao toàn bộ đơn và bắt đầu quay về bãi / kho?`)) {
                return;
            }

            this.isReturningVehicle = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/vehicles/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        plateNumber: this.selectedVehicle.plate_number,
                        status: 'Đang di chuyển',
                        dispatchStatus: 'Trên đường về'
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.selectedVehicle.dispatch_status = 'Trên đường về';
                    this.selectedVehicle.status = 'Đang di chuyển';
                    this.showToast('🚚 Xe đang trên đường quay về bãi / kho!');
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isReturningVehicle = false;
            }
        },

        // ─── Đã Về Tới Bãi / Kho (Kết thúc chuyến) ────────────────
        async finishTripArrival() {
            if (!this.selectedVehicle) return;
            if (!confirm(`Xác nhận xe ${this.selectedVehicle.plate_number} đã về tới bãi / kho an toàn và kết thúc chuyến hàng?`)) {
                return;
            }

            this.isReturningVehicle = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/vehicles/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        plateNumber: this.selectedVehicle.plate_number,
                        status: 'Sẵn sàng',
                        dispatchStatus: 'Rảnh'
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.selectedVehicle.dispatch_status = 'Rảnh';
                    this.selectedVehicle.status = 'Sẵn sàng';
                    this.selectedVehicle.departure_time = null;
                    this.showToast('🏁 Xe đã về bãi an toàn! Chuyến giao hàng đã hoàn thành.');
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isReturningVehicle = false;
            }
        },

        // ─── Quick Views & Prints ──────────────────────────────────
        openViewOrderModal(order) {
            this.inspectOrder = order;
            this.showOrderViewModal = true;
        },

        printDeliverySlip(order) {
            if (!order) return;
            const url = '/orders/' + order.id + '/edit?mode=print';
            window.open(url, '_blank', 'width=900,height=800');
        }
    };
}
</script>
@endsection
