@extends('layouts.app')

@section('title', 'Tổng Quan Hoạt Động | ERP Đại Sơn')

@section('content')
<div class="space-y-6">

    <!-- Header & Welcome -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>Xin chào, {{ Auth::user()->name ?? 'Quản trị viên' }}!</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    Hệ Thống Trực Tuyến
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Tổng hợp số liệu kinh doanh, kho bãi và tiến độ giao vận hôm nay ({{ now()->format('d/m/Y') }}).</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ url('/orders/create') }}" class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-700 hover:to-blue-700 text-white font-bold text-xs rounded-xl shadow-sm hover:shadow transition-all flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Tạo Đơn Hàng Mới</span>
            </a>
            <a href="{{ url('/cat_products') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="hidden sm:inline">Tra Cứu Giá</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Today Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Đơn Hàng Hôm Nay</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1.5">{{ number_format($todayOrdersCount) }}</h3>
                <p class="text-[11px] text-sky-600 font-medium mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-calendar-day"></i> Cập nhật liên tục
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
        </div>

        <!-- Card 2: Today Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Doanh Thu Hôm Nay</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1.5">{{ number_format($todayRevenue, 0, ',', '.') }} đ</h3>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Đã trừ các đơn hủy</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
        </div>

        <!-- Card 3: Month Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Doanh Thu Tháng {{ now()->format('m') }}</p>
                <h3 class="text-2xl font-black text-blue-600 mt-1.5">{{ number_format($monthRevenue, 0, ',', '.') }} đ</h3>
                <p class="text-[11px] text-blue-500 font-medium mt-1">Doanh số lũy kế tháng</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <!-- Card 4: Products & Fleet -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Vật Tư & Đội Xe</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1.5">{{ number_format($totalProducts) }} <span class="text-xs font-normal text-slate-500">vật tư</span></h3>
                <p class="text-[11px] text-amber-600 font-medium mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-truck"></i> {{ $activeVehicles }} xe sẵn sàng giao
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
        </div>
    </div>

    <!-- Grid: Recent Orders & Status Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Recent Orders (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base">Đơn Hàng Gần Đây</h2>
                    <p class="text-xs text-slate-400">Danh sách các đơn mới tạo và cần xử lý</p>
                </div>
                <a href="{{ url('/orders') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 hover:underline flex items-center gap-1">
                    <span>Xem tất cả</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <th class="py-3 px-4 font-bold">Mã Đơn</th>
                            <th class="py-3 px-4 font-bold">Khách Hàng / Đại Lý</th>
                            <th class="py-3 px-4 font-bold text-right">Tổng Tiền</th>
                            <th class="py-3 px-4 font-bold text-center">Trạng Thái</th>
                            <th class="py-3 px-4 font-bold text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentOrders as $o)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ url('/orders/' . $o->id . '/edit') }}" class="text-sky-600 hover:underline">
                                        {{ $o->order_code }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $o->created_at ? $o->created_at->format('H:i d/m') : '' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800">{{ $o->dealer?->name ?? $o->customer_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $o->customer_phone }}</div>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900">
                                    {{ number_format($o->total_amount, 0, ',', '.') }} đ
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $badgeColor = match($o->status) {
                                            'Báo giá' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'Chốt đơn', 'Đã duyệt' => 'bg-sky-50 text-sky-700 border-sky-200',
                                            'Đã bốc xong', 'Đã kiểm hàng' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'Đang vận chuyển' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'Đã giao' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'Hủy', 'Đã hủy' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-slate-50 text-slate-700 border-slate-200'
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeColor }}">
                                        {{ $o->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ url('/orders/' . $o->id . '/edit') }}" class="px-2.5 py-1 text-slate-600 hover:text-sky-600 bg-slate-100 hover:bg-sky-50 rounded-lg font-semibold text-[11px] transition-colors">
                                        Sửa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 italic">Chưa có đơn hàng nào trong hệ thống.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Status Distribution & Quick Actions -->
        <div class="space-y-6">

            <!-- Status Distribution -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <h3 class="font-bold text-slate-900 text-sm mb-3">Tình Trạng Xử Lý Đơn</h3>
                <div class="space-y-2.5">
                    @php
                        $statuses = [
                            'Báo giá' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-700'],
                            'Chốt đơn' => ['bg' => 'bg-sky-500', 'text' => 'text-sky-700'],
                            'Đang vận chuyển' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-700'],
                            'Đã giao' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-700'],
                            'Hủy' => ['bg' => 'bg-rose-500', 'text' => 'text-rose-700'],
                        ];
                    @endphp
                    @foreach($statuses as $st => $cfg)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-600 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $cfg['bg'] }}"></span>
                                {{ $st }}
                            </span>
                            <span class="font-black text-slate-800">{{ $ordersByStatus[$st] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Navigation Tiles -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <h3 class="font-bold text-slate-900 text-sm">Truy Cập Nhanh Nghiệp Vụ</h3>
                <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                    <a href="{{ url('/cat_products') }}" class="p-3 rounded-xl bg-slate-50 hover:bg-sky-50 hover:text-sky-700 transition-colors flex flex-col items-center justify-center text-center gap-1.5 border border-slate-100">
                        <i class="fa-solid fa-boxes-stacked text-lg text-sky-600"></i>
                        <span>Bảng Giá & Kho</span>
                    </a>
                    <a href="{{ url('/dealers') }}" class="p-3 rounded-xl bg-slate-50 hover:bg-sky-50 hover:text-sky-700 transition-colors flex flex-col items-center justify-center text-center gap-1.5 border border-slate-100">
                        <i class="fa-solid fa-store text-lg text-emerald-600"></i>
                        <span>Đại Lý & Nợ</span>
                    </a>
                    <a href="{{ url('/delivery-dispatch') }}" class="p-3 rounded-xl bg-slate-50 hover:bg-sky-50 hover:text-sky-700 transition-colors flex flex-col items-center justify-center text-center gap-1.5 border border-slate-100">
                        <i class="fa-solid fa-truck text-lg text-purple-600"></i>
                        <span>Lịch Xe Giao</span>
                    </a>
                    <a href="{{ url('/orders/create') }}" class="p-3 rounded-xl bg-slate-50 hover:bg-sky-50 hover:text-sky-700 transition-colors flex flex-col items-center justify-center text-center gap-1.5 border border-slate-100">
                        <i class="fa-solid fa-file-circle-plus text-lg text-blue-600"></i>
                        <span>Lên Đơn Mới</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
