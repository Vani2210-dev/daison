@extends('layouts.app')

@section('title', 'Quản Lý Đơn Hàng & Báo Giá | ERP Đại Sơn')

@section('content')
@php
    $allOrders = $orders;
    $counts = [
        'Tất cả' => \App\Models\Order::companyOrders()->count(),
        'Báo giá' => \App\Models\Order::companyOrders()->where('status', 'Báo giá')->count(),
        'Chốt đơn' => \App\Models\Order::companyOrders()->whereIn('status', ['Chốt đơn', 'Đã duyệt', 'Chờ duyệt', 'Chờ kế toán duyệt'])->count(),
        'Soạn hàng' => \App\Models\Order::companyOrders()->whereIn('status', ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong', 'Đang kiểm hàng'])->count(),
        'Soạn bốc' => \App\Models\Order::companyOrders()->whereIn('status', ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong', 'Đang kiểm hàng'])->count(),
        'Đơn thiếu' => \App\Models\Order::companyOrders()->where(function ($q) {
            $q->where('shortage_reported', true)
              ->orWhereIn('status', ['Kho báo thiếu hàng', 'Báo thiếu']);
        })->count(),
        'Giao sau' => \App\Models\Order::companyOrders()->whereIn('status', ['Giao sau', 'Đơn giao sau', 'Nợ hàng'])->count(),
        'Đơn nợ' => \App\Models\Order::companyOrders()->whereIn('status', ['Giao sau', 'Đơn giao sau', 'Nợ hàng'])->count(),
        'Kiểm hàng' => \App\Models\Order::companyOrders()->whereIn('status', ['Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng', 'Sẵn sàng xuất xe'])->count(),
        'Đang vận chuyển' => \App\Models\Order::companyOrders()->whereIn('status', ['Đang vận chuyển', 'Đang giao hàng'])->count(),
        'Đã giao' => \App\Models\Order::companyOrders()->whereIn('status', ['Đã giao', 'Giao thành công'])->count(),
        'Hủy' => \App\Models\Order::companyOrders()->whereIn('status', ['Hủy', 'Đã hủy'])->count(),
    ];
@endphp

<div class="space-y-4 font-sans text-slate-800 antialiased">

    @php $currentTab = request('tab', 'orders'); @endphp
    <!-- 1. Sub-Section Navigation Tabs (Matching exact React UI) -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto">
        <a href="{{ url('/orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $currentTab === 'orders' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span>Đơn hàng</span>
        </a>

        <a href="{{ url('/orders?tab=consignment') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $currentTab === 'consignment' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-boxes-stacked text-sm"></i>
            <span>Đơn hàng ôm</span>
        </a>

        <a href="{{ url('/orders?tab=returns') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $currentTab === 'returns' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-rotate-left text-sm"></i>
            <span>Khách trả hàng</span>
        </a>

        <a href="{{ url('/orders?tab=suppliers') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $currentTab === 'suppliers' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-cart-shopping text-sm"></i>
            <span>Đặt hàng NCC</span>
        </a>
    </div>

    @if($currentTab === 'consignment')
        @include('orders.consignment_tab')
    @else
    <div class="space-y-4" x-data="ordersIndexApp()" @keydown.window="handleGlobalKeydown($event)" @scroll.window="closeActionMenu()" @resize.window="closeActionMenu()">
    <!-- 2. Header Banner (Matching exact React UI) -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        
        <!-- Left: Blue circular back button + Title + Subtitle -->
        <div class="flex items-center gap-3">
            <a href="{{ url('/dashboard') }}" class="w-10 h-10 rounded-full bg-sky-600 hover:bg-sky-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-sky-600/20 cursor-pointer transition-transform hover:scale-105" title="Quay lại Tổng quan">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>

            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Đơn hàng</h1>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Quản lý đơn hàng theo quy trình: Báo giá · Chốt đơn · Soạn hàng · Đang vận chuyển · Đã giao · Hủy</p>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="printSelected()" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer" title="In phiếu đã chọn (F9 hoặc P)">
                <i class="fa-solid fa-print text-slate-500"></i>
                <span>In phiếu <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] text-slate-600 font-mono">F9</kbd></span>
            </button>

            <button type="button" @click="exportManifest()" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Bảng kê xuất hàng (F10 hoặc E)">
                <i class="fa-solid fa-file-excel text-slate-500"></i>
                <span>Bảng kê <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] text-slate-600 font-mono">F10</kbd></span>
            </button>

            <button type="button" @click="showShortcutsModal = true" class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Xem danh sách phím tắt (F1 hoặc ?)">
                <i class="fa-regular fa-keyboard text-amber-600"></i>
                <span>Phím tắt <kbd class="px-1.5 py-0.5 bg-amber-100/80 border border-amber-300 rounded text-[10px] text-amber-900 font-mono">F1</kbd></span>
            </button>

            <a href="{{ url('/inventory/picking') }}" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Soạn Đơn & Kiểm Hàng Xuất Kho">
                <i class="fa-solid fa-boxes-packing text-amber-600"></i>
                <span>Soạn & Kiểm Kho</span>
            </a>

            <a href="{{ url('/delivery-dispatch') }}" class="px-3.5 py-2 bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Điều phối xe & Xếp người vào xe giao hàng">
                <i class="fa-solid fa-truck text-purple-600"></i>
                <span>Xếp Xe & Tuyến Giao</span>
            </a>

            <a href="{{ url('/orders/create') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-black rounded-xl text-xs transition-all shadow-md shadow-sky-600/20 flex items-center gap-1.5 cursor-pointer" title="Lên đơn hàng mới (F8 hoặc N)">
                <i class="fa-solid fa-plus"></i>
                <span>+ Thêm mới <kbd class="px-1.5 py-0.5 bg-sky-700/80 border border-sky-500 rounded text-[10px] text-white font-mono">F8</kbd></span>
            </a>
        </div>

    </div>

    <!-- 3. Status Filter Pills & Filter Toolbar (Matching exact React UI) -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-3">
        
        <!-- Status Tabs (Pills) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            @foreach(['Tất cả', 'Báo giá', 'Chốt đơn', 'Soạn hàng', 'Đơn thiếu', 'Giao sau', 'Kiểm hàng', 'Đang vận chuyển', 'Đã giao', 'Hủy'] as $pill)
                @php
                    $isSel = (request('status') === $pill) 
                        || (!request('status') && $pill === 'Tất cả') 
                        || (request('status') === 'Soạn bốc' && $pill === 'Soạn hàng')
                        || (in_array(request('status'), ['Đơn nợ', 'Nợ hàng', 'Đơn giao sau']) && $pill === 'Giao sau');
                    $badgeCount = $counts[$pill] ?? 0;

                    $pillClass = match($pill) {
                        'Đơn thiếu' => $isSel 
                            ? 'bg-rose-600 text-white shadow-2xs ring-2 ring-rose-600/30 font-black' 
                            : ($badgeCount > 0 ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-300 font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'),
                        'Giao sau', 'Đơn nợ' => $isSel 
                            ? 'bg-amber-600 text-white shadow-2xs ring-2 ring-amber-600/30 font-black' 
                            : ($badgeCount > 0 ? 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-300 font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'),
                        default => $isSel 
                            ? 'bg-sky-600 text-white shadow-2xs ring-2 ring-sky-600/20' 
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'
                    };

                    $badgePillClass = match($pill) {
                        'Đơn thiếu' => $isSel ? 'bg-white/25 text-white' : ($badgeCount > 0 ? 'bg-rose-200 text-rose-800 font-black' : 'bg-slate-200 text-slate-600'),
                        'Giao sau', 'Đơn nợ' => $isSel ? 'bg-white/25 text-white' : ($badgeCount > 0 ? 'bg-amber-200 text-amber-900 font-black' : 'bg-slate-200 text-slate-600'),
                        default => $isSel ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-600'
                    };
                @endphp
                <a href="{{ url('/orders' . ($pill === 'Tất cả' ? '' : '?status=' . urlencode($pill))) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $pillClass }}">
                    @if($pill === 'Đơn thiếu' && $badgeCount > 0)
                        <i class="fa-solid fa-triangle-exclamation text-[11px] {{ $isSel ? 'text-white' : 'text-rose-600 animate-pulse' }}"></i>
                    @elseif(in_array($pill, ['Giao sau', 'Đơn nợ']) && $badgeCount > 0)
                        <i class="fa-solid fa-clipboard-list text-[11px] {{ $isSel ? 'text-white' : 'text-amber-600' }}"></i>
                    @endif
                    <span>{{ $pill }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $badgePillClass }}">
                        {{ $badgeCount }}
                    </span>
                </a>
            @endforeach
        </div>

        @php
            $activeFilterCount = 0;
            if (request('from_date')) $activeFilterCount++;
            if (request('to_date')) $activeFilterCount++;
            if (request('search')) $activeFilterCount++;
            if (request('customer')) $activeFilterCount++;
            if (request('order_type')) $activeFilterCount++;
            if (request('payment_status')) $activeFilterCount++;
        @endphp

        <!-- Filter Form Row 1 -->
        <form action="{{ url('/orders') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 text-xs pt-1 items-end">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Từ ngày -->
            <div class="lg:col-span-2 space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Từ ngày</label>
                <div class="relative">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full p-2 pr-8 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white">
                    <i class="fa-solid fa-calendar-days text-slate-400 absolute right-2.5 top-3 pointer-events-none"></i>
                </div>
            </div>

            <!-- Đến ngày -->
            <div class="lg:col-span-2 space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Đến ngày</label>
                <div class="relative">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full p-2 pr-8 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white">
                    <i class="fa-solid fa-calendar-days text-slate-400 absolute right-2.5 top-3 pointer-events-none"></i>
                </div>
            </div>

            <!-- Số phiếu -->
            <div class="lg:col-span-2 space-y-1">
                <label class="font-bold text-slate-500 text-[11px] flex items-center justify-between">
                    <span>Số phiếu</span>
                    <span class="text-[10px] text-sky-600 font-mono font-semibold">F2 hoặc /</span>
                </label>
                <input type="text" x-ref="searchInput" name="search" value="{{ request('search') }}" placeholder="VD: PN2607..." class="w-full p-2 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white placeholder-slate-400">
            </div>

            <!-- Khách hàng (mã/tên) -->
            <div class="lg:col-span-2 space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Khách hàng (mã/tên)</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-2.5 top-3 pointer-events-none"></i>
                    <input type="text" name="customer" value="{{ request('customer') }}" placeholder="Gõ mã hoặc tên..." class="w-full p-2 pl-8 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white placeholder-slate-400">
                </div>
            </div>

            <!-- Trạng thái -->
            <div class="space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Trạng thái</label>
                <select name="status" class="w-full p-2 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white cursor-pointer">
                    <option value="">Tất cả trạng thái</option>
                    @foreach(['Báo giá', 'Chốt đơn', 'Soạn hàng', 'Đơn thiếu', 'Giao sau', 'Kiểm hàng', 'Đang vận chuyển', 'Đã giao', 'Hủy'] as $st)
                        <option value="{{ $st }}" {{ (request('status') === $st || (request('status') === 'Soạn bốc' && $st === 'Soạn hàng') || (in_array(request('status'), ['Đơn nợ', 'Nợ hàng', 'Đơn giao sau']) && $st === 'Giao sau')) ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Loại đơn hàng -->
            <div class="space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Loại đơn hàng</label>
                <select name="order_type" class="w-full p-2 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white">
                    <option value="">Tất cả loại đơn</option>
                    <option value="normal" {{ request('order_type') === 'normal' ? 'selected' : '' }}>🛒 Thương mại</option>
                    <option value="project" {{ request('order_type') === 'project' ? 'selected' : '' }}>🏗️ Đơn dự án</option>
                    <option value="backorder" {{ request('order_type') === 'backorder' ? 'selected' : '' }}>📋 Đơn giao sau (nợ hàng)</option>
                </select>
            </div>

            <!-- Submit Button & Filter Icon -->
            <div class="lg:col-span-2 flex items-center gap-1.5">
                <button type="submit" class="flex-1 p-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Tìm kiếm</span>
                </button>
                <div class="relative shrink-0">
                    <button type="button" @click="isAdvancedOpen = !isAdvancedOpen" class="p-2 bg-sky-600 text-white rounded-xl cursor-pointer hover:bg-sky-700 transition-colors" title="Bộ lọc nâng cao">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if($activeFilterCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-rose-600 text-white font-black text-[9px] w-4 h-4 rounded-full flex items-center justify-center shadow-xs">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                </div>
            </div>
        </form>

        <!-- Toggle Row: Mở rộng bộ lọc nâng cao + Checkbox -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs pt-2 border-t border-slate-100">
            <button type="button" @click="isAdvancedOpen = !isAdvancedOpen" class="text-sky-600 hover:text-sky-800 font-bold flex items-center gap-1.5 cursor-pointer select-none">
                <span x-text="isAdvancedOpen ? '^ Thu gọn bộ lọc nâng cao' : 'v Mở rộng bộ lọc nâng cao'"></span>
                @if($activeFilterCount > 0)
                    <span class="px-2 py-0.2 bg-sky-100 text-sky-800 rounded-full text-[10px] font-extrabold">{{ $activeFilterCount }}</span>
                @endif
            </button>

            <label class="flex items-center gap-2 font-bold text-slate-700 select-none cursor-pointer">
                <input type="checkbox" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300">
                <span class="flex items-center gap-1 text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                    Chỉ phiếu cần kiểm tra
                </span>
            </label>
        </div>

    </div>

    <!-- 4. Main Data Table (100% Matching exact React Table Headers & Row Layout) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[1250px]">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold border-b border-slate-300 uppercase tracking-tight text-[11px]">
                        <th class="py-3 px-3 text-center w-[40px]">
                            <input type="checkbox" @change="toggleSelectAll($event.target.checked)" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300 cursor-pointer">
                        </th>
                        <th class="py-3 px-3 text-center w-[50px]">STT</th>
                        <th class="py-3 px-3 min-w-[130px]">MÃ CHỨNG TỪ</th>
                        <th class="py-3 px-3 min-w-[140px]">LOẠI ĐƠN HÀNG</th>
                        <th class="py-3 px-3 w-[100px]">NGÀY</th>
                        <th class="py-3 px-3 min-w-[120px]">KHO</th>
                        <th class="py-3 px-3 min-w-[220px]">KHÁCH HÀNG</th>
                        <th class="py-3 px-3 w-[130px] text-right">THÀNH TIỀN</th>
                        <th class="py-3 px-3 min-w-[140px]">NGƯỜI LÊN ĐƠN</th>
                        <th class="py-3 px-3 min-w-[220px]">TRẠNG THÁI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($orders as $idx => $order)
                        @php
                            $rowNum = $idx + 1;
                            $orderCode = $order->order_code;
                            $isProject = $order->order_type === 'project';
                            $createdDate = $order->created_at ? $order->created_at->format('Y-m-d') : date('Y-m-d');
                            $whName = $order->warehouse ?: 'Kho Tổng Mê Linh (MQ)';
                            $customerName = $order->dealer?->name ?? $order->customer_name ?? 'Đại lý phân phối';
                            $customerAddr = $order->customer_address ?: ($order->dealer?->address ?: 'Khối 7, Phường Vinh Hưng, Tỉnh Nghệ An');
                            $totalAmtFormatted = number_format($order->total_amount, 0, ',', '.') . ' đ';
                            $payMethod = $order->payment_method ?: 'Ghi nợ đại lý';
                            $creator = $order->order_creator ?: ($order->creator?->name ?? 'Admin KBTech');
                            $st = $order->status ?: 'Báo giá';
                            $isBackOrder = in_array($st, ['Giao sau', 'Đơn giao sau', 'Nợ hàng']) || ($order->order_type === 'backorder');
                            $rowClass = 'hover:bg-sky-50/50';
                            if ($order->shortage_reported) {
                                $rowClass = 'bg-rose-50/60 hover:bg-rose-100/60 border-l-4 border-rose-500';
                            } elseif ($isBackOrder) {
                                $rowClass = 'bg-amber-50/60 hover:bg-amber-100/60 border-l-4 border-amber-500';
                            }
                        @endphp
                        <tr class="{{ $rowClass }} transition-colors">
                            <!-- Checkbox -->
                            <td class="py-3 px-3 text-center">
                                <input type="checkbox" :value="'{{ $order->id }}'" x-model="selectedIds" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300 cursor-pointer">
                            </td>

                            <!-- STT -->
                            <td class="py-3 px-3 text-center font-bold text-slate-700">
                                {{ $rowNum }}
                            </td>

                            <!-- MÃ CHỨNG TỪ -->
                            <td class="py-3 px-3 font-bold text-sky-700">
                                <a href="{{ url('/orders/' . $order->id . '/edit') }}" class="hover:underline text-left cursor-pointer font-bold font-mono text-slate-900" title="Bấm để xem và sửa chi tiết chứng từ">
                                    {{ $orderCode }}
                                </a>
                            </td>

                            <!-- LOẠI ĐƠN HÀNG -->
                            <td class="py-3 px-3">
                                @if($isProject)
                                    <div class="flex flex-col items-start gap-0.5">
                                        <span class="px-2.5 py-1 rounded-md bg-purple-100 text-purple-900 font-extrabold text-[11px] border border-purple-300 shadow-2xs shrink-0 inline-flex items-center gap-1">
                                            <span>🏗️ Đơn dự án</span>
                                        </span>
                                        @if($order->project_name)
                                            <span class="text-[10px] text-purple-800 font-extrabold max-w-[130px] truncate" title="{{ $order->project_name }}">
                                                {{ $order->project_name }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($isBackOrder)
                                    <span class="px-2.5 py-1 rounded-md bg-amber-100 text-amber-900 font-extrabold text-[11px] border border-amber-300 shadow-2xs shrink-0 inline-flex items-center gap-1" title="Đơn giao sau / nợ hàng">
                                        <span>📋 Giao sau</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-sky-50 text-sky-800 font-extrabold text-[11px] border border-sky-200 shrink-0 inline-flex items-center gap-1">
                                        <span>🛒 Thương mại</span>
                                    </span>
                                @endif
                            </td>

                            <!-- NGÀY -->
                            <td class="py-3 px-3 font-semibold text-slate-600">
                                <div>{{ $createdDate }}</div>
                                @if(!empty($order->picking_date))
                                    <div class="text-[10px] text-purple-700 font-bold mt-0.5 flex items-center gap-1" title="Ngày soạn hàng xuất kho">
                                        <i class="fa-solid fa-boxes-stacked text-[9px] text-purple-600"></i>
                                        <span>Soạn: {{ \Carbon\Carbon::parse($order->picking_date)->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if(!empty($order->delivery_date))
                                    <div class="text-[10px] text-sky-700 font-bold mt-0.5 flex items-center gap-1" title="Ngày hẹn giao hàng">
                                        <i class="fa-solid fa-truck-fast text-[9px] text-sky-600"></i>
                                        <span>Giao: {{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- KHO -->
                            <td class="py-3 px-3 font-semibold text-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-warehouse text-slate-400 text-xs shrink-0"></i>
                                    <span class="truncate max-w-[150px]" title="{{ $whName }}">
                                        {{ $whName }}
                                    </span>
                                </div>
                            </td>

                            <!-- KHÁCH HÀNG -->
                            <td class="py-3 px-3 font-bold text-slate-900">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $customerName }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-normal leading-tight mt-0.5">
                                    {{ $customerAddr }}
                                </div>
                            </td>

                            <!-- THÀNH TIỀN -->
                            <td class="py-3 px-3 text-right font-extrabold text-slate-900">
                                {{ $totalAmtFormatted }}
                            </td>

                            <!-- NGƯỜI LÊN ĐƠN -->
                            <td class="py-3 px-3 font-semibold text-slate-800">
                                {{ $creator }}
                            </td>

                            <!-- TRẠNG THÁI Column (Badge + Action Buttons matching exact React UI) -->
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex flex-col gap-0.5 min-w-[110px]">
                                        @php
                                            $badgeConfig = match($st) {
                                                'Báo giá' => ['bg' => 'bg-amber-50 text-amber-800 border-amber-300', 'icon' => '📋', 'label' => 'Báo giá', 'sub' => 'Phiếu báo giá'],
                                                'Chốt đơn', 'Đã duyệt' => ['bg' => 'bg-blue-50 text-blue-800 border-blue-300', 'icon' => '🤝', 'label' => 'Chốt đơn', 'sub' => 'Đơn bán hàng'],
                                                'Giao sau', 'Đơn giao sau', 'Nợ hàng' => ['bg' => 'bg-amber-50 text-amber-800 border-amber-300', 'icon' => '📋', 'label' => 'Giao sau', 'sub' => 'Đơn giao sau'],
                                                'Soạn hàng', 'Đang bốc hàng', 'Soạn bốc', 'Kho đang soạn' => ['bg' => 'bg-purple-50 text-purple-800 border-purple-300', 'icon' => '📦', 'label' => 'Soạn hàng', 'sub' => 'Đơn bán hàng'],
                                                'Đã bốc xong', 'Kho đã xuất đủ' => ['bg' => 'bg-indigo-50 text-indigo-800 border-indigo-300', 'icon' => '📦', 'label' => 'Đã bốc xong', 'sub' => 'Đơn bán hàng'],
                                                'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng' => ['bg' => 'bg-teal-50 text-teal-800 border-teal-300', 'icon' => '🛡️', 'label' => 'Đang kiểm hàng', 'sub' => 'Đơn bán hàng'],
                                                'Đang vận chuyển' => ['bg' => 'bg-cyan-50 text-cyan-800 border-cyan-300', 'icon' => '🚚', 'label' => 'Đang vận chuyển', 'sub' => 'Đơn bán hàng'],
                                                'Đã gộp đơn' => ['bg' => 'bg-indigo-50 text-indigo-800 border-indigo-300', 'icon' => '🔀', 'label' => 'Đã gộp đơn', 'sub' => 'Đã gộp vào đơn khác'],
                                                'Đã giao', 'Đã quyết toán', 'Giao thành công' => ['bg' => 'bg-emerald-50 text-emerald-800 border-emerald-300', 'icon' => '✅', 'label' => 'Đã giao', 'sub' => 'Đơn bán hàng'],
                                                'Hủy', 'Đã hủy' => ['bg' => 'bg-rose-50 text-rose-800 border-rose-300', 'icon' => '❌', 'label' => 'Đã hủy', 'sub' => 'Đơn hủy'],
                                                default => ['bg' => 'bg-slate-100 text-slate-800 border-slate-300', 'icon' => '📄', 'label' => $st, 'sub' => 'Đơn bán hàng']
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-black border shadow-2xs {{ $badgeConfig['bg'] }}">
                                            <span>{{ $badgeConfig['icon'] }}</span>
                                            <span>{{ $badgeConfig['label'] }}</span>
                                        </span>
                                        <div class="text-[10px] text-slate-400 font-medium whitespace-nowrap pl-0.5">
                                            {{ $badgeConfig['sub'] }}
                                        </div>
                                        @if($order->shortage_reported)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-600 text-white font-black text-[9px] tracking-wide shadow-sm mt-0.5 animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation text-[8px]"></i>
                                                KHO BÁO THIẾU
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Quick Action Pill button: "Chốt đơn" khi Báo giá / "Soạn hàng" khi Chốt đơn / "Bổ sung" khi Soạn hàng -->
                                    <div class="flex items-center gap-1 shrink-0">
                                        @if(in_array($st, ['Báo giá', 'Bản nháp', 'Chờ duyệt', 'Chờ kế toán duyệt']))
                                            <button type="button" 
                                                    @click="confirmLockOrder('{{ $order->id }}', '{{ $order->order_code }}')"
                                                    class="px-2.5 py-1 rounded-xl border border-blue-600 text-blue-700 bg-blue-50 hover:bg-blue-100 font-black text-[11px] flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs active:scale-95" 
                                                    title="Chốt đơn hàng & chuyển sang bước soạn hàng">
                                                <i class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                                                <span>Chốt đơn</span>
                                            </button>
                                        @elseif(in_array($st, ['Chốt đơn', 'Đã duyệt']))
                                            <button type="button" 
                                                    @click="openPickingModal({
                                                        id: '{{ $order->id }}',
                                                        code: '{{ $order->order_code }}',
                                                        customer: '{{ addslashes($customerName) }}',
                                                        warehouse: '{{ addslashes($whName) }}',
                                                        total: '{{ $totalAmtFormatted }}',
                                                        picking_date: '{{ $order->picking_date ? \Carbon\Carbon::parse($order->picking_date)->format('Y-m-d') : date('Y-m-d') }}'
                                                    })"
                                                    class="px-2.5 py-1 rounded-xl border border-purple-500 text-purple-900 bg-purple-50 hover:bg-purple-100 font-black text-[11px] flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs active:scale-95" 
                                                    title="Mở màn hình chọn ngày & bắt đầu soạn hàng xuất kho">
                                                <i class="fa-solid fa-boxes-stacked text-purple-600 text-xs"></i>
                                                <span>Soạn hàng</span>
                                            </button>
                                        @elseif(in_array($st, ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong']))
                                            <a href="{{ url('/orders/' . $order->id . '/edit') }}" 
                                               class="px-2.5 py-1 rounded-xl border border-sky-500 text-sky-700 bg-sky-50 hover:bg-sky-100 font-extrabold text-[11px] flex items-center gap-1 cursor-pointer transition-all shadow-2xs active:scale-95" 
                                               title="Bổ sung thêm hàng hóa / chỉnh sửa chi tiết đơn">
                                                <i class="fa-solid fa-boxes-packing text-sky-600 text-xs"></i>
                                                <span>Bổ sung</span>
                                            </a>
                                        @else
                                            <a href="{{ url('/orders/' . $order->id . '/edit') }}" 
                                               class="px-2.5 py-1 rounded-xl border border-slate-300 text-slate-700 bg-slate-50 hover:bg-slate-100 font-bold text-[11px] flex items-center gap-1 cursor-pointer transition-all shadow-2xs active:scale-95" 
                                               title="Xem chi tiết đơn hàng">
                                                <i class="fa-regular fa-eye text-slate-500 text-xs"></i>
                                                <span>Chi tiết</span>
                                            </a>
                                        @endif

                                        <button type="button" 
                                                @click.stop="openActionMenu({
                                                    id: '{{ $order->id }}',
                                                    code: '{{ $order->order_code }}',
                                                    customer: '{{ addslashes($customerName) }}',
                                                    phone: '{{ addslashes($order->customer_phone ?? '') }}',
                                                    address: '{{ addslashes($customerAddr) }}',
                                                    total: {{ (float)$order->total_amount }},
                                                    status: '{{ $st }}',
                                                    warehouse: '{{ addslashes($whName) }}',
                                                    driver: '{{ addslashes($order->driver?->name ?? 'Chưa phân công') }}',
                                                    vehicle: '{{ $order->assigned_vehicle_plate ?? 'Chưa có xe' }}',
                                                    date: '{{ $createdDate }}'
                                                }, $event)" 
                                                :class="activeActionOrder && activeActionOrder.id === '{{ $order->id }}' ? 'bg-sky-100 text-sky-700 ring-2 ring-sky-300' : 'hover:bg-slate-200 text-slate-600'"
                                                class="p-1 rounded-lg cursor-pointer text-xs font-black tracking-widest px-1.5 transition-all" 
                                                title="Thao tác chứng từ">
                                            •••
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400 font-medium">
                                Không có chứng từ nào khớp điều kiện tìm kiếm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Floating Action Menu (Matching user specification exactly) -->
    <div x-show="activeActionOrder" 
         @click.outside="closeActionMenu()" 
         x-cloak 
         class="fixed z-[9999] w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 py-1.5 text-xs select-none ring-1 ring-black/5"
         :style="actionDropUp 
            ? `bottom: ${actionMenuY}px; left: ${actionMenuX}px;` 
            : `top: ${actionMenuY}px; left: ${actionMenuX}px;`">
        <template x-if="activeActionOrder">
            <div class="space-y-0.5">
                <!-- 1. Xem đơn hàng (Chỉ đọc) -->
                <a :href="'/orders/' + (activeActionOrder?.id || '') + '/edit?readonly=1'" @click="closeActionMenu()" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors">
                    <i class="fa-regular fa-eye text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xem đơn hàng (Chỉ đọc)</span>
                </a>

                <!-- 2. In -->
                <button type="button" @click="openPrintModal(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-solid fa-print text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">In phiếu</span>
                </button>

                <!-- 2.4 Chốt đơn hàng (Nếu đang Báo giá) -->
                <template x-if="['Báo giá', 'Bản nháp', 'Chờ duyệt', 'Chờ kế toán duyệt'].includes(activeActionOrder?.status)">
                    <button type="button" @click="confirmLockOrder(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-blue-50 flex items-center gap-3 text-blue-700 transition-colors font-bold cursor-pointer">
                        <i class="fa-solid fa-circle-check text-blue-600 w-5 text-center text-sm"></i>
                        <span>Chốt đơn hàng</span>
                    </button>
                </template>

                <!-- 2.5 Soạn hàng (Nếu đã chốt đơn) -->
                <template x-if="['Chốt đơn', 'Đã duyệt'].includes(activeActionOrder?.status)">
                    <button type="button" @click="openPickingModal(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-purple-50 flex items-center gap-3 text-purple-900 transition-colors font-bold cursor-pointer">
                        <i class="fa-solid fa-boxes-stacked text-purple-600 w-5 text-center text-sm"></i>
                        <span>Soạn hàng xuất kho</span>
                    </button>
                </template>

                <!-- 3. Bổ sung sản phẩm / Sửa -->
                <a :href="'/orders/' + (activeActionOrder?.id || '') + '/edit'" @click="closeActionMenu()" class="w-full text-left px-3.5 py-2 hover:bg-sky-50 flex items-center gap-3 text-sky-700 transition-colors">
                    <i class="fa-solid fa-boxes-packing text-sky-600 w-5 text-center text-sm"></i>
                    <span class="font-bold">Bổ sung sản phẩm / Sửa</span>
                </a>

                <!-- Divider -->
                <div class="border-t border-slate-100 my-1"></div>

                <!-- 4. Theo dõi đơn hàng -->
                <button type="button" @click="openTrackingModal(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-800 transition-colors cursor-pointer">
                    <i class="fa-solid fa-rotate-left text-sky-600 w-5 text-center text-sm"></i>
                    <span class="font-bold">Theo dõi đơn hàng</span>
                </button>

                <!-- 5. Xuất Excel -->
                <button type="button" @click="exportOrderExcel(activeActionOrder, 'standard')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-regular fa-file-lines text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xuất Excel</span>
                </button>

                <!-- 6. Xuất Excel (tên hoá đơn) -->
                <button type="button" @click="exportOrderExcel(activeActionOrder, 'invoice')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-regular fa-file-lines text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xuất Excel (tên hoá đơn)</span>
                </button>

                <!-- 7. Xuất PDF (tên hoá đơn) -->
                <button type="button" @click="exportOrderPdf(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-solid fa-file-arrow-down text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xuất PDF (tên hoá đơn)</span>
                </button>

                <!-- 8. Xe & người giao hàng -->
                <a :href="'/delivery-dispatch?search=' + encodeURIComponent(activeActionOrder?.code || '')" @click="closeActionMenu()" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors">
                    <i class="fa-solid fa-truck text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xe & người giao hàng</span>
                </a>

                <!-- 9. Nhập thanh toán -->
                <button type="button" @click="openPaymentModal(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-regular fa-credit-card text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Nhập thanh toán</span>
                </button>
            </div>
        </template>
    </div>

    <!-- Modal: Theo dõi đơn hàng (Tracking lifecycle) -->
    <div x-show="showTrackingModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.outside="showTrackingModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm">Theo dõi tiến trình đơn hàng</h3>
                        <p class="text-xs text-sky-600 font-mono font-bold" x-text="activeTrackingOrder?.code"></p>
                    </div>
                </div>
                <button type="button" @click="showTrackingModal = false" class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Lifecycle Stepper -->
            <div class="py-2 space-y-3">
                <template x-for="(st, sIdx) in trackingSteps" :key="st.name">
                    <div class="flex items-start gap-3 relative">
                        <div class="flex flex-col items-center">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                                 :class="isStepCompleted(st.name) ? 'bg-emerald-500 text-white shadow-xs' : (isStepCurrent(st.name) ? 'bg-sky-600 text-white ring-4 ring-sky-100' : 'bg-slate-100 text-slate-400')">
                                <template x-if="isStepCompleted(st.name)">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                </template>
                                <template x-if="!isStepCompleted(st.name)">
                                    <span x-text="sIdx + 1"></span>
                                </template>
                            </div>
                            <template x-if="sIdx < trackingSteps.length - 1">
                                <div class="w-0.5 h-6 my-0.5" :class="isStepCompleted(trackingSteps[sIdx + 1].name) ? 'bg-emerald-400' : 'bg-slate-200'"></div>
                            </template>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold" :class="isStepCurrent(st.name) ? 'text-sky-700 font-black' : (isStepCompleted(st.name) ? 'text-slate-800' : 'text-slate-400')" x-text="st.label"></span>
                                <span class="text-[10px]" :class="isStepCurrent(st.name) ? 'text-sky-600 font-bold' : 'text-slate-400'" x-text="isStepCurrent(st.name) ? 'Đang thực hiện' : (isStepCompleted(st.name) ? 'Đã hoàn tất' : 'Chưa đến')"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5" x-text="st.desc"></p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Order info summary -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs space-y-1.5">
                <div class="flex justify-between"><span class="text-slate-500">Khách hàng:</span> <span class="font-bold text-slate-800" x-text="activeTrackingOrder?.customer"></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Kho xuất hàng:</span> <span class="font-bold text-slate-800" x-text="activeTrackingOrder?.warehouse"></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Lái xe & Phương tiện:</span> <span class="font-bold text-sky-700" x-text="(activeTrackingOrder?.driver || 'Chưa phân công') + ' (' + (activeTrackingOrder?.vehicle || 'Chưa xếp xe') + ')'"></span></div>
                <div class="flex justify-between border-t border-slate-200 pt-1.5"><span class="text-slate-500">Tổng giá trị đơn:</span> <span class="font-black text-slate-900" x-text="formatMoney(activeTrackingOrder?.total)"></span></div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <a :href="'/orders/' + activeTrackingOrder?.id + '/edit'" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                    Mở chi tiết đơn hàng
                </a>
            </div>
        </div>
    </div>

    <!-- Modal: Nhập thanh toán (Quick Payment) -->
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.outside="showPaymentModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i class="fa-regular fa-credit-card"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm">Nhập thanh toán đơn hàng</h3>
                        <p class="text-xs text-emerald-600 font-mono font-bold" x-text="activePaymentOrder?.code"></p>
                    </div>
                </div>
                <button type="button" @click="showPaymentModal = false" class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200/60 flex items-center justify-between">
                    <div>
                        <span class="text-emerald-800 font-bold block" x-text="activePaymentOrder?.customer"></span>
                        <span class="text-[11px] text-emerald-600" x-text="'Mã: ' + activePaymentOrder?.code"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Cần thanh toán</span>
                        <span class="text-sm font-black text-emerald-700" x-text="formatMoney(activePaymentOrder?.total)"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Phương thức thanh toán <span class="text-rose-500">*</span></label>
                    <select x-model="paymentForm.method" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none font-semibold">
                        <option value="Tiền mặt">💵 Tiền mặt (Thu trực tiếp)</option>
                        <option value="Chuyển khoản">💳 Chuyển khoản ngân hàng / VietQR</option>
                        <option value="Ghi nợ đại lý">📑 Ghi nợ vào công nợ đại lý</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Số tiền thanh toán (VNĐ) <span class="text-rose-500">*</span></label>
                    <input type="text" inputmode="numeric" x-money="paymentForm.amount" placeholder="0" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none font-bold text-slate-900">
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Ghi chú thanh toán</label>
                    <textarea x-model="paymentForm.notes" rows="2" placeholder="Nhập ghi chú hoặc mã giao dịch ngân hàng..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showPaymentModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs cursor-pointer">Hủy</button>
                <button type="button" @click="submitPayment()" :disabled="paymentSubmitting" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs cursor-pointer shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-check" x-show="!paymentSubmitting"></i>
                    <i class="fa-solid fa-spinner fa-spin" x-show="paymentSubmitting"></i>
                    <span>Xác nhận thu tiền</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- Modal: Xem thông tin đơn hàng & In phiếu (Theo hình mẫu) -->
    <!-- ======================================================== -->
    <div x-show="showPrintModal" x-cloak class="fixed inset-0 z-[10000] flex items-center justify-center p-2 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div @click.outside="showPrintModal = false" class="bg-white rounded-lg shadow-2xl border border-slate-300 w-full max-w-5xl my-auto overflow-hidden text-[12px] sm:text-[13px] text-slate-800 leading-normal font-sans">
            
            <!-- Modal Header: Blue #1976d2 -->
            <div class="bg-[#1976d2] px-4 py-2.5 text-white flex items-center justify-between select-none">
                <h3 class="font-bold text-sm sm:text-base tracking-wide">Xem thông tin đơn hàng</h3>
                <button type="button" @click="showPrintModal = false" class="text-white/80 hover:text-white cursor-pointer text-lg font-bold leading-none px-1">
                    ✕
                </button>
            </div>

            <!-- Modal Loading State -->
            <div x-show="printLoading" class="py-16 text-center text-slate-500 space-y-3">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-[#1976d2]"></i>
                <div class="font-semibold text-xs">Đang tải chi tiết đơn hàng...</div>
            </div>

            <!-- Modal Content -->
            <div x-show="!printLoading && printOrderData" class="p-3 sm:p-5 space-y-3">
                <!-- Top Info Section -->
                <div class="relative pb-1">
                    <!-- Meta Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-2 gap-x-4">
                        <div class="sm:col-span-3 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Số hóa đơn:</span>
                            <span class="font-bold text-slate-900" x-text="printOrderData?.order_code"></span>
                        </div>
                        <div class="sm:col-span-3 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Ngày lập:</span>
                            <span x-text="printOrderData?.created_date"></span>
                        </div>
                        <div class="sm:col-span-6 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Khách hàng:</span>
                            <span class="truncate font-semibold text-slate-900" x-text="(printOrderData?.customer_name || printOrderData?.customer_display || '').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')"></span>
                        </div>

                        <div class="sm:col-span-3 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Kho xuất:</span>
                            <span class="uppercase font-semibold" x-text="printOrderData?.warehouse"></span>
                        </div>
                        <div class="sm:col-span-9 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Diễn giải:</span>
                            <span class="text-slate-600" x-text="printOrderData?.notes || ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Products Table Section Header -->
                <div class="bg-[#dfdfdf] px-3 py-1 font-bold text-slate-900 text-xs sm:text-[13px] border border-slate-300">
                    Danh sách sản phẩm
                </div>

                <!-- Products Table -->
                <div class="overflow-x-auto border border-slate-300">
                    <table class="w-full border-collapse text-[11px] sm:text-xs">
                        <thead>
                            <tr class="bg-[#f0f0f0] font-bold text-slate-800 text-center border-b border-slate-300">
                                <th class="border border-slate-300 py-1.5 px-2 w-10 text-center">TT</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-24 text-left">Mã SP</th>
                                <th class="border border-slate-300 py-1.5 px-2 text-left">Tên sản phẩm</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-16 text-center">ĐVT</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-14 text-right">SL</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-24 text-right">Đơn giá</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-16 text-right">C.khấu</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-24 text-right">Thành tiền</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-16 text-center">Loại giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(it, idx) in (printOrderData?.items || [])" :key="it.id || idx">
                                <tr class="bg-white hover:bg-slate-50 transition-colors border-b border-slate-300 text-slate-800">
                                    <td class="border border-slate-300 py-1.5 px-2 text-center font-medium" x-text="it.tt || (idx + 1)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-left font-mono text-slate-700" x-text="it.sku || ''"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-left">
                                        <div class="font-bold text-slate-900" x-text="it.name"></div>
                                        <div class="text-[11px] text-sky-700 font-medium" x-text="it.tax_name" x-show="it.tax_name && it.tax_name !== it.name"></div>
                                    </td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-center" x-text="it.unit"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-right font-semibold" x-text="formatNumber(it.sold_quantity ?? it.quantity)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-right" x-text="formatNumber(it.unit_price)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-right" x-text="getDiscountDisplay(it)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-right font-bold text-slate-900" x-text="formatNumber(it.total || it.amount)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-center font-bold text-slate-700" x-text="it.price_tier_code || '---'"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-white font-bold text-slate-900">
                            <!-- Dòng Tổng -->
                            <tr class="border-b border-slate-300">
                                <td colspan="6" class="border border-slate-300 py-1.5 px-2"></td>
                                <td class="border border-slate-300 py-1.5 px-2 text-right font-bold">Tổng</td>
                                <td class="border border-slate-300 py-1.5 px-2 text-right font-black" x-text="formatNumber(printOrderData?.final_total || printOrderData?.total_items_amount)"></td>
                                <td class="border border-slate-300 py-1.5 px-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Under-table Information Lines -->
                <div class="pt-1 space-y-1.5 text-xs sm:text-[12.5px] text-slate-800">
                    <div>
                        <span class="font-bold text-slate-900">Bằng chữ:</span>
                        <strong class="uppercase text-slate-900 ml-1" x-text="printOrderData?.final_total_words"></strong>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-1 gap-x-4 pt-1">
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Tiền thanh toán:</span>
                            <span class="font-bold text-slate-900" x-text="formatNumber(printOrderData?.final_total)"></span>
                        </div>
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Hình thức:</span>
                            <span x-text="printOrderData?.payment_method || '---'"></span>
                        </div>
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Tình trạng:</span>
                            <span x-text="printOrderData?.delivery_status || printOrderData?.status || '---'"></span>
                        </div>

                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Nhân viên giao:</span>
                            <span x-text="printOrderData?.delivery_driver || '---'"></span>
                        </div>
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Xe giao:</span>
                            <span x-text="printOrderData?.delivery_vehicle || '---'"></span>
                        </div>
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Ngày giao:</span>
                            <span x-text="printOrderData?.delivery_date || '---'"></span>
                        </div>

                        <div class="sm:col-span-12 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">ĐC giao hàng:</span>
                            <span x-text="printOrderData?.customer_address || '---'"></span>
                        </div>

                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Người lập:</span>
                            <span x-text="printOrderData?.creator_name || '---'"></span>
                        </div>
                        <div class="sm:col-span-8 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Người sửa cuối:</span>
                            <span x-text="printOrderData?.updater_name || printOrderData?.creator_name || '---'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with All Buttons (Matching screenshot) -->
            <div class="bg-[#f5f5f5] border-t border-slate-300 px-3 sm:px-4 py-2.5 flex flex-wrap items-center justify-between gap-2 select-none">
                <!-- Left Buttons: Excel Exports -->
                <div class="flex items-center gap-2">
                    <button type="button" @click="exportOrderExcelPrint(printOrderData, 'invoice')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-[11px]"></i>
                        <span>Xuất excel tên HĐ</span>
                    </button>
                    <button type="button" @click="exportOrderExcelPrint(printOrderData, 'standard')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-[11px]"></i>
                        <span>Xuất excel</span>
                    </button>
                </div>

                <!-- Middle Print Options (Matching user highlighted buttons) -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button type="button" @click="executePrintOrder('no_price')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In không chứa thông tin tiêu đề công ty">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In không TĐ</span>
                    </button>
                    <button type="button" @click="executePrintOrder('no_price_with_header')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In có thông tin công ty, không hiển thị giá">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In không giá</span>
                    </button>
                    <button type="button" @click="executePrintOrder('tax_name')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In có thông tin công ty theo tên hóa đơn GTGT">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In theo tên HĐ</span>
                    </button>
                    <button type="button" @click="executePrintOrder('standard')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In đơn hàng chuẩn có thông tin công ty">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In đơn hàng</span>
                    </button>
                </div>

                <!-- Right Button: Close -->
                <div>
                    <button type="button" @click="showPrintModal = false"
                            class="px-5 py-1.5 bg-[#eeeeee] hover:bg-slate-200 text-slate-700 border border-slate-300 rounded font-semibold text-xs transition-colors cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Chọn Ngày Soạn Hàng Khi Bấm Nút Soạn Hàng -->
    <div x-show="showPickingModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.outside="showPickingModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold shadow-md shadow-amber-500/25">
                        <i class="fa-solid fa-boxes-stacked text-base"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Soạn Hàng Xuất Kho</h3>
                        <p class="text-xs text-sky-700 font-mono font-bold" x-text="activePickingOrder?.code"></p>
                    </div>
                </div>
                <button type="button" @click="showPickingModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Khách hàng:</span>
                    <strong class="text-slate-800 text-right truncate max-w-[240px]" x-text="activePickingOrder?.customer"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kho xuất:</span>
                    <span class="text-slate-700 font-medium" x-text="activePickingOrder?.warehouse"></span>
                </div>
                <div class="flex justify-between" x-show="activePickingOrder?.total">
                    <span class="text-slate-500">Tổng tiền:</span>
                    <strong class="text-emerald-700 font-mono font-black" x-text="activePickingOrder?.total"></strong>
                </div>
            </div>

            <!-- Input Ngày Soạn Hàng -->
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-day text-amber-600"></i>
                    <span>Ngày Soạn Hàng <span class="text-rose-500">*</span></span>
                </label>
                <input type="date" x-model="pickingDate" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 focus:border-amber-500 rounded-xl font-bold font-mono text-sm text-slate-900 outline-none transition-all cursor-pointer shadow-2xs">
                <p class="text-[11px] text-slate-400 mt-1">Hệ thống sẽ lưu ngày soạn hàng và mở giao diện bốc hàng theo ngày này.</p>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="showPickingModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmStartPicking()" :disabled="isSubmittingPicking"
                        class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-black text-xs transition-all shadow-md shadow-amber-500/25 flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <template x-if="!isSubmittingPicking">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>Bắt Đầu Soạn Hàng</span>
                            <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                        </span>
                    </template>
                    <template x-if="isSubmittingPicking">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                            <span>Đang chuyển sang Soạn hàng...</span>
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Keyboard Shortcut Bar -->
    <div class="hidden sm:flex items-center justify-between px-4 py-2.5 bg-slate-50 rounded-2xl border border-slate-200/80 text-xs text-slate-500 font-medium overflow-x-auto gap-3">
        <div class="flex items-center gap-4 shrink-0">
            <span class="font-bold text-slate-700 flex items-center gap-1.5">
                <i class="fa-regular fa-keyboard text-sky-600"></i>
                <span>Phím tắt nhanh:</span>
            </span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">F8</kbd> / <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">N</kbd> Lên đơn</span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">F2</kbd> / <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">/</kbd> Tìm kiếm</span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">F4</kbd> Bộ lọc nâng cao</span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">F9</kbd> In phiếu</span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">1 - 8</kbd> Lọc trạng thái</span>
        </div>
        <button type="button" @click="showShortcutsModal = true" class="text-sky-600 hover:text-sky-800 font-bold shrink-0 cursor-pointer flex items-center gap-1">
            <span>Chi tiết</span>
            <kbd class="px-1.5 py-0.5 bg-white border border-sky-200 rounded font-mono font-bold text-sky-700 shadow-2xs">F1</kbd>
        </button>
    </div>

    <!-- Keyboard Shortcuts Guide Modal -->
    <div x-show="showShortcutsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click.self="showShortcutsModal = false">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-5 py-4 bg-gradient-to-r from-sky-600 to-blue-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-keyboard text-lg"></i>
                    <h3 class="font-black text-sm">Danh Sách Phím Tắt - Quản Lý Đơn Hàng</h3>
                </div>
                <button type="button" @click="showShortcutsModal = false" class="text-white/80 hover:text-white cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                <div>
                    <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-2">Thao tác chức năng</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Tạo đơn hàng / Báo giá mới</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F8</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">N</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Nhảy nhanh vào ô tìm kiếm</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F2</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">/</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Bật / Tắt bộ lọc nâng cao</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F4</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">In phiếu các đơn đã tích chọn</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F9</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">P</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Xuất bảng kê xuất hàng kho</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F10</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">E</kbd>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-2">Chuyển nhanh tab trạng thái (Phím số 1 &rarr; 8)</h4>
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">1. Tất cả đơn</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">1</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">2. Báo giá</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">2</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">3. Chốt đơn</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">3</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">4. Soạn bốc</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">4</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">5. Kiểm hàng</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">5</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">6. Vận chuyển</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">6</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">7. Đã giao</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">7</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">8. Đơn hủy</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">8</kbd>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-slate-500 text-[11px]">
                    <span>Bấm <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700">Esc</kbd> để đóng bảng trợ giúp</span>
                    <button type="button" @click="showShortcutsModal = false" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition-colors cursor-pointer">
                        Đã hiểu
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    @endif
</div>

<script>
function ordersIndexApp() {
    return {
        isAdvancedOpen: false,
        selectedIds: [],
        showShortcutsModal: false,
        activeActionOrder: null,
        actionMenuX: 0,
        actionMenuY: 0,
        actionDropUp: false,

        // Modal states for Print order modal
        showPrintModal: false,
        printOrderData: null,
        printLoading: false,

        // Modal states for Tracking, Payment & Picking
        showTrackingModal: false,
        activeTrackingOrder: null,
        showPaymentModal: false,
        activePaymentOrder: null,
        paymentSubmitting: false,
        paymentForm: {
            method: 'Tiền mặt',
            amount: 0,
            notes: ''
        },

        showPickingModal: false,
        activePickingOrder: null,
        pickingDate: '{{ date("Y-m-d") }}',
        isSubmittingPicking: false,

        openPickingModal(order) {
            this.activePickingOrder = order;
            this.pickingDate = order.picking_date || '{{ date("Y-m-d") }}';
            this.isSubmittingPicking = false;
            this.showPickingModal = true;
            this.closeActionMenu();
        },

        async confirmStartPicking() {
            if (!this.activePickingOrder || this.isSubmittingPicking) return;
            this.isSubmittingPicking = true;
            const orderId = this.activePickingOrder.id;
            const pDate = this.pickingDate || '{{ date("Y-m-d") }}';
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            try {
                const res = await fetch('{{ url("/orders/update-status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        status: 'Soạn hàng',
                        picking_date: pDate
                    })
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    this.showPickingModal = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi khi cập nhật ngày soạn hàng');
                }
            } catch (err) {
                console.error('Lỗi chuyển trạng thái đơn hàng sang Soạn hàng:', err);
                alert('Lỗi kết nối máy chủ khi lưu ngày soạn hàng.');
            } finally {
                this.isSubmittingPicking = false;
            }
        },

        trackingSteps: [
            { name: 'Báo giá', label: '1. Báo giá', desc: 'Lập báo giá và gửi cho khách hàng / đại lý' },
            { name: 'Chốt đơn', label: '2. Chốt đơn', desc: 'Khách hàng chốt giá & đơn chuyển sang chuẩn bị hàng' },
            { name: 'Soạn hàng', label: '3. Soạn hàng', desc: 'Thủ kho in lệnh và nhân viên bốc hàng tại kho' },
            { name: 'Kiểm hàng', label: '4. Kiểm tra', desc: 'Kiểm đếm quy cách, đóng gói và sẵn sàng xuất kho' },
            { name: 'Đang vận chuyển', label: '5. Vận chuyển', desc: 'Lái xe nhận hàng và đang giao trên lộ trình' },
            { name: 'Đã giao', label: '6. Đã giao hàng', desc: 'Giao hàng thành công và đối soát công nợ / COD' }
        ],

        openActionMenu(order, e) {
            if (this.activeActionOrder && this.activeActionOrder.id === order.id) {
                this.activeActionOrder = null;
                return;
            }
            const rect = e.currentTarget.getBoundingClientRect();
            this.activeActionOrder = order;

            const menuWidth = 256; // w-64
            this.actionMenuX = Math.max(10, rect.right - menuWidth);

            const menuHeight = 390;
            const spaceBelow = window.innerHeight - rect.bottom;
            if (spaceBelow < menuHeight && rect.top > menuHeight) {
                this.actionDropUp = true;
                this.actionMenuY = window.innerHeight - rect.top + 4;
            } else {
                this.actionDropUp = false;
                this.actionMenuY = Math.min(rect.bottom + 4, window.innerHeight - menuHeight - 10);
            }
        },

        closeActionMenu() {
            this.activeActionOrder = null;
        },

        handleGlobalKeydown(e) {
            const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

            // F1 or ? (outside input): Toggle shortcut guide modal
            if (e.key === 'F1' || (e.key === '?' && !isInput)) {
                e.preventDefault();
                this.showShortcutsModal = !this.showShortcutsModal;
                return;
            }

            // Esc: Close menu or modal or blur input or clear selection
            if (e.key === 'Escape') {
                if (this.activeActionOrder) {
                    this.activeActionOrder = null;
                    return;
                }
                if (this.showShortcutsModal) {
                    this.showShortcutsModal = false;
                    return;
                }
                if (isInput) {
                    document.activeElement.blur();
                    return;
                }
                if (this.selectedIds.length > 0) {
                    this.selectedIds = [];
                    return;
                }
            }

            // F8 or N (outside input): Create new order
            if (e.key === 'F8' || (e.key.toLowerCase() === 'n' && !isInput && !e.ctrlKey && !e.altKey && !e.metaKey)) {
                e.preventDefault();
                window.location.href = '{{ url("/orders/create") }}';
                return;
            }

            // F2 or / (outside input): Focus search input
            if (e.key === 'F2' || (e.key === '/' && !isInput)) {
                e.preventDefault();
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                    this.$refs.searchInput.select();
                }
                return;
            }

            // F4: Toggle Advanced filters
            if (e.key === 'F4') {
                e.preventDefault();
                this.isAdvancedOpen = !this.isAdvancedOpen;
                return;
            }

            // F9 or P (outside input): Print selected
            if (e.key === 'F9' || (e.key.toLowerCase() === 'p' && !isInput && !e.ctrlKey && !e.altKey && !e.metaKey)) {
                e.preventDefault();
                this.printSelected();
                return;
            }

            // F10 or E (outside input): Export manifest
            if (e.key === 'F10' || (e.key.toLowerCase() === 'e' && !isInput && !e.ctrlKey && !e.altKey && !e.metaKey)) {
                e.preventDefault();
                this.exportManifest();
                return;
            }

            // Number keys 1-8 (outside input): Jump to status tabs
            if (!isInput && !e.ctrlKey && !e.altKey && !e.metaKey) {
                const tabs = {
                    '1': '{{ url("/orders") }}',
                    '2': '{{ url("/orders?status=B%C3%A1o+gi%C3%A1") }}',
                    '3': '{{ url("/orders?status=Ch%E1%BB%91t+%C4%91%C6%A1n") }}',
                    '4': '{{ url("/orders?status=So%E1%BA%A1n+b%E1%BB%91c") }}',
                    '5': '{{ url("/orders?status=Ki%E1%BB%83m+h%C3%A0ng") }}',
                    '6': '{{ url("/orders?status=%C4%90ang+v%E1%BA%ADn+chuy%E1%BB%83n") }}',
                    '7': '{{ url("/orders?status=%C4%90%C3%A3+giao") }}',
                    '8': '{{ url("/orders?status=H%E1%BB%A7y") }}',
                };
                if (tabs[e.key]) {
                    e.preventDefault();
                    window.location.href = tabs[e.key];
                    return;
                }
            }
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.selectedIds = @json($orders->pluck('id'));
            } else {
                this.selectedIds = [];
            }
        },

        printSelected() {
            if (this.selectedIds.length === 0) {
                const firstCheckbox = document.querySelector('input[name="selectedOrders[]"]');
                if (firstCheckbox && firstCheckbox.value) {
                    this.openPrintModal(firstCheckbox.value);
                    return;
                }
                alert('Vui lòng tích chọn ít nhất một chứng từ cần in!');
                return;
            }
            this.openPrintModal(this.selectedIds[0]);
        },

        exportManifest() {
            alert('Đang kết xuất bảng kê hàng xuất kho theo các chứng từ đã chọn.');
        },

        async confirmLockOrder(orderOrId, orderCode) {
            let id = orderOrId;
            let code = orderCode;
            if (typeof orderOrId === 'object' && orderOrId !== null) {
                id = orderOrId.id;
                code = orderOrId.code || orderOrId.id;
            }
            this.closeActionMenu();
            if (!confirm(`Xác nhận chốt đơn hàng [${code || id}] và chuyển sang bước chuẩn bị soạn hàng?`)) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/orders/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ orderId: id, status: 'Chốt đơn' })
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Chốt đơn thất bại.');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ.');
            }
        },

        async updateStatus(orderId, newStatus) {
            if (!confirm(`Bạn có chắc chắn muốn chuyển trạng thái đơn hàng sang: [${newStatus}]?`)) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/orders/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ orderId, status: newStatus })
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    alert('Cập nhật trạng thái thành công!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Cập nhật thất bại.');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ.');
            }
        },

        printOrder(id) {
            this.closeActionMenu();
            this.openPrintModal(id);
        },

        openTrackingModal(order) {
            this.closeActionMenu();
            this.activeTrackingOrder = order;
            this.showTrackingModal = true;
        },

        isStepCompleted(stepName) {
            if (!this.activeTrackingOrder) return false;
            let current = this.activeTrackingOrder.status;
            if (current === 'Hủy') return false;
            if (current === 'Soạn bốc' || current === 'Kho đang soạn' || current === 'Đang bốc hàng' || current === 'Đã bốc xong') current = 'Soạn hàng';
            if (current === 'Đã duyệt' || current === 'Chờ duyệt') current = 'Chốt đơn';
            if (current === 'Đã kiểm hàng') current = 'Kiểm hàng';
            if (current === 'Đang giao hàng') current = 'Đang vận chuyển';
            if (current === 'Giao thành công') current = 'Đã giao';

            let checkStep = stepName === 'Soạn bốc' ? 'Soạn hàng' : stepName;
            const orderStages = ['Báo giá', 'Chốt đơn', 'Soạn hàng', 'Kiểm hàng', 'Đang vận chuyển', 'Đã giao'];
            const curIdx = orderStages.indexOf(current);
            const stepIdx = orderStages.indexOf(checkStep);
            if (curIdx === -1) return false;
            return curIdx > stepIdx;
        },

        isStepCurrent(stepName) {
            if (!this.activeTrackingOrder) return false;
            const current = this.activeTrackingOrder.status;
            if (stepName === current) return true;
            if ((stepName === 'Soạn hàng' || stepName === 'Soạn bốc') && ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong'].includes(current)) return true;
            if (stepName === 'Kiểm hàng' && ['Kiểm hàng', 'Đã kiểm hàng', 'Sẵn sàng xuất xe'].includes(current)) return true;
            if (stepName === 'Đang vận chuyển' && ['Đang vận chuyển', 'Đang giao hàng'].includes(current)) return true;
            if (stepName === 'Đã giao' && ['Đã giao', 'Giao thành công'].includes(current)) return true;
            return false;
        },

        exportOrderExcel(order, type = 'standard') {
            this.closeActionMenu();
            if (!order) return;
            const fileName = (type === 'invoice' ? 'Hoa_don_' : 'Don_hang_') + (order.code || order.id) + '.csv';
            const csvContent = "\uFEFF" + 
                "THÔNG TIN CHỨNG TỪ\n" +
                "Mã đơn hàng," + (order.code || '') + "\n" +
                "Khách hàng," + (order.customer || '') + "\n" +
                "Số điện thoại," + (order.phone || '') + "\n" +
                "Địa chỉ giao hàng," + (order.address || '') + "\n" +
                "Ngày lập," + (order.date || '') + "\n" +
                "Kho xuất," + (order.warehouse || '') + "\n" +
                "Trạng thái," + (order.status || '') + "\n" +
                "Tổng tiền," + (order.total || 0) + "\n";

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        exportOrderPdf(order) {
            this.closeActionMenu();
            if (!order) return;
            window.open('/orders/' + order.id + '/edit?print=1&pdf=1', '_blank');
        },

        openPaymentModal(order) {
            this.closeActionMenu();
            this.activePaymentOrder = order;
            this.paymentForm = {
                method: 'Tiền mặt',
                amount: Number(order.total) || 0,
                notes: ''
            };
            this.showPaymentModal = true;
        },

        async submitPayment() {
            if (!this.activePaymentOrder) return;
            if (!this.paymentForm.amount || Number(this.paymentForm.amount) <= 0) {
                alert('Vui lòng nhập số tiền thanh toán hợp lệ.');
                return;
            }
            this.paymentSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/orders/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        id: this.activePaymentOrder.id,
                        paymentStatus: 'Đã thanh toán',
                        paymentMethod: this.paymentForm.method,
                        notes: (this.paymentForm.notes ? '[THANH TOÁN ' + this.paymentForm.method + ': ' + this.formatMoney(this.paymentForm.amount) + '] ' + this.paymentForm.notes : '')
                    })
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    alert('Nhập thanh toán thành công!');
                    this.showPaymentModal = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'Lưu thanh toán thất bại.');
                }
            } catch (e) {
                alert('Lỗi kết nối khi lưu thanh toán.');
            } finally {
                this.paymentSubmitting = false;
            }
        },

        async openPrintModal(orderOrId) {
            let orderId = orderOrId;
            if (typeof orderOrId === 'object' && orderOrId !== null) {
                orderId = orderOrId.id;
            }
            if (!orderId) {
                if (this.selectedIds.length > 0) {
                    orderId = this.selectedIds[0];
                } else {
                    const firstCheckbox = document.querySelector('input[name="selectedOrders[]"]');
                    if (firstCheckbox && firstCheckbox.value) {
                        orderId = firstCheckbox.value;
                    } else {
                        alert('Vui lòng chọn một đơn hàng để xem và in phiếu!');
                        return;
                    }
                }
            }
            this.closeActionMenu();
            this.printLoading = true;
            this.showPrintModal = true;
            this.printOrderData = null;

            try {
                const res = await fetch(`/api/orders/${orderId}/print-data`);
                const json = await res.json();
                if (json.success && json.order) {
                    this.printOrderData = json.order;
                } else {
                    alert(json.message || 'Không thể tải thông tin in đơn hàng');
                    this.showPrintModal = false;
                }
            } catch (e) {
                console.error('Error fetching print data:', e);
                alert('Lỗi kết nối khi tải dữ liệu đơn hàng');
                this.showPrintModal = false;
            } finally {
                this.printLoading = false;
            }
        },

        formatNumber(val) {
            if (val === null || val === undefined || isNaN(val)) return '0';
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val)));
        },

        getDiscountDisplay(it) {
            if (!it) return '-';
            if (it.discount_percent !== undefined && it.discount_percent !== null && Number(it.discount_percent) > 0) {
                return Number(it.discount_percent) + '%';
            }
            const origSubtotal = Number(it.amount || 0) || (Number(it.unit_price || 0) * Number(it.quantity || 0));
            const disc = Number(it.discount || 0);
            if (disc > 0 && origSubtotal > 0) {
                return Math.round((disc / origSubtotal) * 100) + '%';
            }
            return '-';
        },

        executePrintOrder(mode) {
            if (!this.printOrderData) {
                alert('Chưa có thông tin đơn hàng để in!');
                return;
            }
            const o = this.printOrderData;
            const isNoPrice = (mode === 'no_price' || mode === 'no_price_with_header');
            const isTaxName = (mode === 'tax_name');
            const hasCompanyHeader = (mode !== 'no_price');

            let title = 'PHIẾU BÁN HÀNG KIÊM XUẤT KHO';
            let subTitle = 'HÓA ĐƠN BÁN LẺ & GIAO HÀNG';
            if (mode === 'no_price') {
                title = 'PHIẾU XUẤT KHO - SOẠN HÀNG';
                subTitle = 'DÀNH CHO BỘ PHẬN KHO & BỐC XẾP (KHÔNG HIỂN THỊ ĐƠN GIÁ, KHÔNG TIÊU ĐỀ)';
            } else if (mode === 'no_price_with_header') {
                title = 'PHIẾU GIAO NHẬN HÀNG HÓA';
                subTitle = 'BIÊN BẢN BÀN GIAO & ĐỐI CHIẾU SỐ LƯỢNG HÀNG HÓA';
            } else if (mode === 'tax_name') {
                title = 'BẢNG KÊ BÁN HÀNG THEO HÓA ĐƠN';
                subTitle = 'BẢNG KÊ CHI TIẾT HÀNG HÓA THEO TÊN HÓA ĐƠN GTGT';
            }

            let tableRows = '';
            (o.items || []).forEach((it, idx) => {
                const prodName = isTaxName ? (it.tax_name || it.name) : it.name;
                const subName = (!isTaxName && it.tax_name && it.tax_name !== it.name) ? `<div style="font-size:10.5px;color:#555;font-style:italic;">${it.tax_name}</div>` : '';
                const discText = this.getDiscountDisplay(it);
                
                if (isNoPrice) {
                    tableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:left;font-family:monospace;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:13px;">${this.formatNumber(it.sold_quantity ?? it.quantity)}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:center;font-weight:bold;font-size:12px;">${it.price_tier_code || this.getPriceTierCode(it.price_type, o.dealer_tier)}</td>
                        </tr>
                    `;
                } else {
                    tableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:left;font-family:monospace;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:12.5px;">${this.formatNumber(it.sold_quantity ?? it.quantity)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${this.formatNumber(it.unit_price)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${discText}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;">${this.formatNumber(it.total || it.amount)}</td>
                        </tr>
                    `;
                }
            });

            let summaryHtml = '';
            if (!isNoPrice) {
                summaryHtml = `
                    <tr>
                        <td colspan="5" style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">Tổng</td>
                        <td style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;">${this.formatNumber(o.final_total || o.total_items_amount)}</td>
                    </tr>
                `;
            } else {
                summaryHtml = `
                    <tr>
                        <td colspan="4" style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">Tổng SL:</td>
                        <td style="text-align:center;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;">${this.formatNumber((o.items || []).reduce((s, it) => s + Number(it.sold_quantity ?? it.quantity ?? 0), 0))}</td>
                    </tr>
                `;
            }

            const printHtml = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>${title} - ${o.order_code}</title>
                    <style>
                        @page { 
                            size: A4 portrait; 
                            margin: 10mm 12mm 10mm 12mm; 
                        }
                        * {
                            box-sizing: border-box;
                        }
                        body { 
                            font-family: 'Times New Roman', Times, serif; 
                            font-size: 12.5px; 
                            color: #000; 
                            line-height: 1.35; 
                            margin: 0; 
                            padding: 0; 
                            background: #fff;
                        }
                        table { 
                            width: 100%; 
                            border-collapse: collapse; 
                        }
                        .product-table th { 
                            background-color: #f1f5f9; 
                            border: 1px solid #111; 
                            padding: 6px 4px; 
                            font-weight: bold; 
                            font-size: 11.5px; 
                            text-transform: uppercase;
                            text-align: center;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        .product-table td {
                            border: 1px solid #111;
                            padding: 5px 6px;
                            font-size: 12px;
                        }
                        @media print {
                            body { padding: 0; }
                            .no-print { display: none !important; }
                        }
                    </style>
                </head>
                <body>
                    <!-- Header Doanh Nghiệp (Ẩn khi in không TĐ - no_price) -->
                    ${hasCompanyHeader ? `
                    <table style="width: 100%; border: none; border-bottom: 2px solid #1b4a8c; padding-bottom: 6px; margin-bottom: 12px; border-collapse: collapse;">
                        <tr>
                            <td style="width: 65%; vertical-align: top; border: none; padding: 0; text-align: left;">
                                <table style="border: none; border-collapse: collapse; margin-bottom: 2px;">
                                    <tr>
                                        <td style="border: none; padding: 0 10px 0 0; vertical-align: middle;">
                                            <img src="/images/logo.png" alt="Logo Đại Sơn" style="height: 52px; width: auto; display: block; -webkit-print-color-adjust: exact; print-color-adjust: exact;" onerror="this.style.display='none'">
                                        </td>
                                        <td style="border: none; padding: 0; vertical-align: middle;">
                                            <div style="font-size: 14.5px; font-weight: bold; color: #1b4a8c; text-transform: uppercase; letter-spacing: 0.5px;">NHÀ PHÂN PHỐI MÁY LỌC NƯỚC ĐẠI SƠN</div>
                                            <div style="font-size: 11px; color: #444; font-weight: bold; text-transform: uppercase; margin-top: 1px;">CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN</div>
                                            <div style="font-size: 11.5px; color: #333; margin-top: 3px; line-height: 1.4;">
                                                <div><strong>Địa chỉ:</strong> Cụm Công Nghiệp Quất Động, Huyện Thường Tín, TP. Hà Nội</div>
                                                <div><strong>Hotline / Zalo:</strong> 0912.345.678 - 0987.654.321 &nbsp;|&nbsp; <strong>Email:</strong> kd@daison.vn</div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 35%; vertical-align: top; text-align: right; border: none; padding: 0;">
                                <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #333;">Mẫu số: 02 - VT</div>
                                <div style="font-size: 10.5px; font-style: italic; color: #555; margin-bottom: 4px;">(Theo TT 200/2014/TT-BTC)</div>
                                <div style="display: inline-block; border: 1px solid #1b4a8c; background-color: #f0f7ff; padding: 3px 8px; border-radius: 4px; font-size: 11.5px; font-weight: bold; color: #1b4a8c; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                    Số: ${o.order_code}
                                </div>
                                <div style="font-size: 11px; color: #555; margin-top: 3px;">Ngày in: ${new Date().toLocaleDateString('vi-VN')} ${new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}</div>
                            </td>
                        </tr>
                    </table>
                    ` : `
                    <!-- In Không TĐ: Tuyệt đối không chứa thông tin công ty, chỉ hiển thị số phiếu và ngày in ở góc phải -->
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                        <div style="text-align: right; font-size: 11px;">
                            <div style="display: inline-block; border: 1px solid #333; background-color: #f8fafc; padding: 2px 8px; border-radius: 4px; font-size: 11.5px; font-weight: bold; color: #111;">
                                Số: ${o.order_code}
                            </div>
                            <div style="font-size: 10.5px; color: #555; margin-top: 2px;">Ngày in: ${new Date().toLocaleDateString('vi-VN')}</div>
                        </div>
                    </div>
                    `}

                    <!-- Tiêu Đề Phiếu -->
                    <div style="text-align: center; margin-bottom: 14px;">
                        <h2 style="margin: 0 0 3px 0; font-size: 19px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: #111;">${title}</h2>
                        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #555; letter-spacing: 0.5px; margin-bottom: 3px;">${subTitle}</div>
                        <div style="font-size: 12px; font-style: italic; color: #333;">
                            Ngày lập: <strong>${o.created_date || '---'}</strong>
                        </div>
                    </div>

                    <!-- Thông Tin Khách Hàng & Giao Vận (2 Cột Rõ Ràng) -->
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px; font-size: 12.5px; line-height: 1.5;">
                        <tr>
                            <td style="width: 60%; vertical-align: top; border: none; padding: 0 12px 0 0;">
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 120px; border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Khách hàng / Đại lý:</td>
                                        <td style="border: none; padding: 2px 0; font-size: 13px;"><strong style="text-transform: uppercase;">${(o.customer_name || o.customer_display || '---').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Địa chỉ giao hàng:</td>
                                        <td style="border: none; padding: 2px 0;">${o.customer_address || '---'}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Điện thoại liên hệ:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.customer_phone || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Diễn giải / Ghi chú:</td>
                                        <td style="border: none; padding: 2px 0; font-style: italic;">${o.notes || '---'}</td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 40%; vertical-align: top; border: none; padding: 0 0 0 12px; border-left: 1px dashed #bbb;">
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 105px; border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Kho xuất hàng:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.warehouse || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Hình thức TT:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.payment_method || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Xe / Người giao:</td>
                                        <td style="border: none; padding: 2px 0;">${o.delivery_vehicle || o.delivery_driver || '---'}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Ngày giao dự kiến:</td>
                                        <td style="border: none; padding: 2px 0;">${o.delivery_date || '---'}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!-- Bảng Danh Sách Hàng Hóa -->
                    <table class="product-table">
                        <thead>
                            ${isNoPrice ? `
                                <tr>
                                    <th style="width: 35px;">TT</th>
                                    <th style="width: 95px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 55px;">ĐVT</th>
                                    <th style="width: 65px;">SL</th>
                                    <th style="width: 75px;">Loại Giá</th>
                                </tr>
                            ` : `
                                <tr>
                                    <th style="width: 35px;">TT</th>
                                    <th style="width: 95px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 55px;">ĐVT</th>
                                    <th style="width: 50px;">SL</th>
                                    <th style="width: 90px;">Đơn giá</th>
                                    <th style="width: 70px;">C.khấu</th>
                                    <th style="width: 100px;">Thành tiền</th>
                                </tr>
                            `}
                        </thead>
                        <tbody>
                            ${tableRows}
                            ${summaryHtml}
                        </tbody>
                    </table>

                    <!-- Bằng Chữ & Trạng Thái -->
                    ${!isNoPrice ? `
                        <div style="margin-top: 8px; font-size: 12.5px; line-height: 1.4;">
                            <strong>Số tiền viết bằng chữ:</strong> 
                            <em style="text-transform: capitalize; font-weight: bold;">${o.final_total_words || ''}</em>
                        </div>
                    ` : ''}

                    <!-- Dòng Ngày Tháng Phía Trên Chữ Ký -->
                    <div style="margin-top: 12px; text-align: right; font-size: 12px; font-style: italic;">
                        Ngày ..... tháng ..... năm 202...
                    </div>

                    <!-- BẢNG CHỮ KÝ 4 BÊN (Dùng thẻ table chuẩn 4 cột 25% - Tuyệt đối không gãy dòng) -->
                    <table style="width: 100%; border: none; margin-top: 8px; border-collapse: collapse; page-break-inside: avoid;">
                        <tr>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người lập phiếu</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">${o.creator_name || ''}</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Thủ kho xuất</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">&nbsp;</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người giao hàng</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">${o.delivery_driver || ''}</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người nhận hàng</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">&nbsp;</div>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 15px; text-align: center; font-size: 11px; font-style: italic; color: #666;">
                        *(Quý khách vui lòng kiểm tra kỹ số lượng, quy cách và chất lượng hàng hóa trước khi ký nhận)*
                    </div>
                </body>
                </html>
            `;

            const printWin = window.open('', '_blank');
            if (!printWin) {
                alert('Trình duyệt đang chặn cửa sổ bật lên. Vui lòng cho phép pop-up để in.');
                return;
            }
            printWin.document.open();
            printWin.document.write(printHtml);
            printWin.document.close();
            printWin.focus();
            setTimeout(() => {
                printWin.print();
            }, 400);
        },

        getPriceTierCode(val, defaultTier = '') {
            if (!val) val = defaultTier || '';
            let str = String(val).trim();
            const matchParen = str.match(/\(([^)]+)\)/);
            if (matchParen) {
                str = matchParen[1].trim();
            } else if (str === 'Theo phiếu' && defaultTier) {
                str = defaultTier.trim();
            }

            const s = str.toLowerCase();
            if (s.includes('cấp 1') || s.includes('cap 1') || s === 'bc1') return 'BC1';
            if (s.includes('cấp 2') || s.includes('cap 2') || s === 'bc2') return 'BC2';
            if (s.includes('cấp 3') || s.includes('cap 3') || s === 'bc3') return 'BC3';
            if (s.includes('bán lẻ') || s.includes('ban le') || s === 'bl') return 'BL';
            if (s.includes('nguyên giá') || s.includes('nguyen gia') || s === 'ng') return 'NG';
            if (s.includes('khuyến mại') || s.includes('tặng') || s === 'km') return 'KM';

            return str.toUpperCase();
        },

        exportOrderExcelPrint(orderData, type = 'standard') {
            if (!orderData) {
                alert('Chưa có thông tin đơn hàng để xuất Excel!');
                return;
            }
            const o = orderData;
            const isTaxName = (type === 'invoice');
            const fileName = (isTaxName ? 'Hoa_don_' : 'Don_hang_') + (o.order_code || o.id) + '.csv';

            let csvContent = "\uFEFF"; // UTF-8 BOM
            csvContent += "THÔNG TIN ĐƠN HÀNG\n";
            csvContent += "Số hóa đơn," + (o.order_code || '') + "\n";
            csvContent += "Ngày lập," + (o.created_date || '') + "\n";
            csvContent += "Khách hàng,\"" + ((o.customer_name || o.customer_display || '').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')).replace(/"/g, '""') + "\"\n";
            csvContent += "Kho xuất,\"" + (o.warehouse || '').replace(/"/g, '""') + "\"\n";
            csvContent += "Diễn giải,\"" + (o.notes || '').replace(/"/g, '""') + "\"\n";
            csvContent += "Địa chỉ giao hàng,\"" + (o.customer_address || '').replace(/"/g, '""') + "\"\n";
            csvContent += "Số điện thoại," + (o.customer_phone || '') + "\n";
            csvContent += "Hình thức thanh toán," + (o.payment_method || 'Tiền mặt') + "\n";
            csvContent += "Tình trạng," + (o.delivery_status || 'Đã xuất hàng') + "\n\n";

            csvContent += "DANH SÁCH SẢN PHẨM\n";
            csvContent += "TT,Tên sản phẩm,Tên HĐ,Đơn vị tính,Đơn giá,Số lượng,Thành tiền,Chiết khấu,Tổng tiền,Giá theo KH,Loại giá\n";

            (o.items || []).forEach(it => {
                const prodName = isTaxName ? (it.tax_name || it.name) : it.name;
                csvContent += `${it.tt},"${prodName.replace(/"/g, '""')}","${(it.tax_name || '').replace(/"/g, '""')}",${it.unit},${it.unit_price},${it.quantity},${it.amount},${it.discount},${it.total},${it.customer_price},${it.price_type}\n`;
            });

            csvContent += `\nTổng (1),,,,${o.subtotal},,${o.total_discount},${o.total_items_amount},,\n`;
            if (o.extra_discount > 0) {
                csvContent += `Chiết khấu (2),,,,,,,${o.extra_discount},,\n`;
            }
            csvContent += `Phải thanh toán (1-2),,,,,,,${o.final_total},,\n`;
            csvContent += `Bằng chữ,"${(o.final_total_words || '').replace(/"/g, '""')}"\n`;
            csvContent += `Người lập,"${(o.creator_name || '').replace(/"/g, '""')}"\n`;
            csvContent += `Người sửa cuối,"${(o.updater_name || '').replace(/"/g, '""')}"\n`;

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0)) + ' đ';
        }
    };
}
</script>
@endsection
