@extends('layouts.app')

@section('title', 'Quản Lý Kho Hàng Xe (Nhập Hoàn & Xuất Hàng Lẻ Lưu Động) | ERP Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="vehicleInventoryApp()">

    <!-- 1. Sub-Section Navigation Tabs (Kho hàng Đại Sơn) -->
    @include('inventory.nav_tabs')

    <!-- 2. Header Banner -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/cat_products') }}" class="w-10 h-10 rounded-full bg-[#1b4a8c] hover:bg-blue-800 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-900/20 cursor-pointer transition-transform hover:scale-105" title="Quay lại Kho hàng">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Quản Lý Kho Hàng Xe Lưu Động</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-[#1b4a8c] text-[11px] font-extrabold border border-blue-200">Kho Xe & Nhập Xuất Xe</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Tích hợp đồng bộ: Duyệt nhập hàng hoàn từ xe, Lập phiếu xuất hàng lẻ lên xe, và Theo dõi tồn kho thực tế của các xe lưu động (dùng chung bảng kho & nhập xuất).</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="openDispatchModal()" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>+ Lập phiếu xuất hàng lẻ</span>
            </button>
            <button type="button" @click="window.location.reload()" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-[#1b4a8c]"></i>
                <span>Làm mới</span>
            </button>
        </div>
    </div>

    <!-- 3. Main Navigation Tabs (Nhập Hoàn Xe • Xuất Lẻ Xe • Tồn Kho Xe) -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto text-xs">
        <!-- Tab 1: Hoàn hàng về kho -->
        <button type="button" @click="setMainTab('returns')" 
                :class="activeMainTab === 'returns' ? 'bg-[#1b4a8c] text-white shadow-md shadow-blue-900/20' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap">
            <i class="fa-solid fa-rotate-left text-sm" :class="activeMainTab === 'returns' ? 'text-blue-200' : 'text-blue-600'"></i>
            <span>1. Hoàn Hàng Về Kho (Nhập kho từ xe)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeMainTab === 'returns' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">
                {{ $returns->total() }}
            </span>
        </button>

        <!-- Tab 2: Xuất hàng lẻ lên xe -->
        <button type="button" @click="setMainTab('dispatches')" 
                :class="activeMainTab === 'dispatches' ? 'bg-[#1b4a8c] text-white shadow-md shadow-blue-900/20' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap">
            <i class="fa-solid fa-truck-arrow-right text-sm" :class="activeMainTab === 'dispatches' ? 'text-amber-200' : 'text-amber-600'"></i>
            <span>2. Xuất Hàng Lẻ Lên Xe (Xuất kho ra xe)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeMainTab === 'dispatches' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'">
                {{ $totalVanDispatchesCount }}
            </span>
        </button>

        <!-- Tab 3: Tồn kho trên xe -->
        <button type="button" @click="setMainTab('stocks')" 
                :class="activeMainTab === 'stocks' ? 'bg-[#1b4a8c] text-white shadow-md shadow-blue-900/20' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap">
            <i class="fa-solid fa-boxes-stacked text-sm" :class="activeMainTab === 'stocks' ? 'text-emerald-200' : 'text-emerald-600'"></i>
            <span>3. Tồn Kho Các Xe Lưu Động</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeMainTab === 'stocks' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800'">
                {{ count($vehicleWarehouses) }} xe
            </span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: HOÀN HÀNG VỀ KHO (NHẬP TỪ XE VỀ KHO)             -->
    <!-- ======================================================== -->
    <div x-show="activeMainTab === 'returns'" x-cloak class="space-y-4">
        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <!-- Card 1: Chờ duyệt hoàn kho -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 rounded-2xl p-4 border border-amber-200 shadow-2xs space-y-2">
                <div class="flex items-center justify-between text-amber-800 font-bold text-xs">
                    <span>Chờ Thủ Kho Duyệt Nhập</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-200/80 text-amber-900 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-black text-amber-950 font-mono tracking-tight">
                        {{ $totalPendingCount }} <span class="text-sm font-bold text-amber-800">phiếu</span>
                    </div>
                    <div class="text-[11px] text-amber-700 font-bold mt-1 flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-amber-200 text-amber-900 font-black">{{ number_format($totalPendingItemsCount ?? 0) }} sản phẩm</span>
                        <span>chờ kiểm đếm</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Đã duyệt hôm nay -->
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50/50 rounded-2xl p-4 border border-emerald-200 shadow-2xs space-y-2">
                <div class="flex items-center justify-between text-emerald-800 font-bold text-xs">
                    <span>Đã Nhập Kho Hôm Nay</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-200/80 text-emerald-900 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-black text-emerald-950 font-mono tracking-tight">
                        {{ $approvedTodayCount }} <span class="text-sm font-bold text-emerald-800">phiếu</span>
                    </div>
                    <div class="text-[11px] text-emerald-700 font-bold mt-1 flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-emerald-200 text-emerald-900 font-black">{{ number_format($approvedTodayItemsCount ?? 0) }} sản phẩm</span>
                        <span>đã cộng tồn kho</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Tổng hoàn kho tháng này -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-2xl p-4 border border-blue-200 shadow-2xs space-y-2">
                <div class="flex items-center justify-between text-blue-800 font-bold text-xs">
                    <span>Tổng Hoàn Kho Tháng {{ now()->month }}</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-200/80 text-blue-900 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-black text-blue-950 font-mono tracking-tight">
                        {{ $totalMonthApprovedCount ?? $approvedTodayCount }} <span class="text-sm font-bold text-blue-800">phiếu</span>
                    </div>
                    <div class="text-[11px] text-blue-700 font-bold mt-1 flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-blue-200 text-blue-900 font-black">{{ number_format($totalMonthItemsCount ?? 0) }} sản phẩm</span>
                        <span>đã lưu kho</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Quy trình -->
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-2">
                <div class="flex items-center justify-between text-slate-700 font-bold text-xs">
                    <span>Quy Trình Duyệt Hoàn Kho</span>
                    <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
                <div class="text-[11px] text-slate-600 space-y-1 font-medium leading-relaxed">
                    <div>• <span class="font-bold text-slate-800">Kiểm đếm số lượng & quy cách</span> theo biên bản của lái xe.</div>
                    <div>• Duyệt thành công: tự động <span class="font-bold text-emerald-700">cộng tồn kho</span> & <span class="font-bold text-blue-700">gỡ khỏi xe</span>.</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-3">
            <form method="GET" action="{{ url('/inventory/import') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                <input type="hidden" name="tab" value="returns">
                <!-- Filter Trạng thái Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                    <a href="{{ url('/inventory/import?tab=returns&status=all' . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                       class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap {{ $status === 'all' || !$status ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Tất cả ({{ $returns->total() }})
                    </a>

                    <a href="{{ url('/inventory/import?tab=returns&status=' . urlencode('Chờ thủ kho duyệt nhập') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                       class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Chờ thủ kho duyệt nhập' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                        <i class="fa-solid fa-hourglass-half"></i>
                        <span>Chờ duyệt nhập</span>
                        @if($totalPendingCount > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'Chờ thủ kho duyệt nhập' ? 'bg-white text-amber-600' : 'bg-amber-500 text-white' }}">{{ $totalPendingCount }}</span>
                        @endif
                    </a>

                    <a href="{{ url('/inventory/import?tab=returns&status=' . urlencode('Đã nhập kho lại') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                       class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Đã nhập kho lại' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Đã nhập kho</span>
                    </a>

                    <a href="{{ url('/inventory/import?tab=returns&status=' . urlencode('Đã từ chối nhập kho') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                       class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Đã từ chối nhập kho' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 border border-rose-200 hover:bg-rose-100' }}">
                        <i class="fa-solid fa-ban"></i>
                        <span>Đã từ chối</span>
                    </a>
                </div>

                <!-- Search input & Warehouse filter -->
                <div class="flex items-center gap-2 shrink-0">
                    <select name="warehouse_id" onchange="this.form.submit()"
                            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-700 text-xs focus:outline-none focus:border-[#1b4a8c]">
                        <option value="">-- Tất cả kho nhận --</option>
                        @foreach($physicalWarehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$warehouseId === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>

                    <div class="relative w-full sm:w-64">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Mã phiếu, xe, lái xe, khách..."
                               class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium text-xs text-slate-800 focus:outline-none focus:border-[#1b4a8c]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-[#1b4a8c] hover:bg-blue-800 text-white font-bold rounded-xl cursor-pointer shadow-xs">
                        Lọc
                    </button>
                    @if($search || ($status && $status !== 'all') || $warehouseId)
                        <a href="{{ url('/inventory/import?tab=returns') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl cursor-pointer" title="Xóa bộ lọc">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Returns Slips Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Mã Phiếu & Thời Gian</th>
                            <th class="py-3.5 px-3">Xe & Lái Xe</th>
                            <th class="py-3.5 px-3">Đơn Hàng & Đại Lý</th>
                            <th class="py-3.5 px-3">Mặt Hàng Trả Về</th>
                            <th class="py-3.5 px-3 text-center">Số Mặt Hàng</th>
                            <th class="py-3.5 px-3 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-4 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($returns as $r)
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $r->status === 'Chờ thủ kho duyệt nhập' ? 'bg-amber-50/20' : '' }}">
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-black text-purple-700 text-xs">{{ $r->slip_code }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    <i class="fa-regular fa-clock text-[10px] mr-1"></i>{{ $r->created_at ? $r->created_at->format('H:i d/m/Y') : '---' }}
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-xs font-bold shrink-0">
                                        <i class="fa-solid fa-truck"></i>
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900 font-mono">{{ $r->vehicle_plate ?: '---' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $r->driver_name ?: 'Chưa gán lái xe' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="font-bold text-slate-900 font-mono">{{ $r->order_code }}</div>
                                <div class="text-[11px] text-slate-500 truncate max-w-[180px]">{{ $r->dealer_name ?: 'Khách lẻ' }}</div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="space-y-1 max-w-xs">
                                    @foreach($r->items->take(2) as $it)
                                        <div class="truncate text-[11px]">
                                            <span class="font-bold text-slate-800">{{ $it->returned_qty }} {{ $it->unit }}</span>
                                            <span class="text-slate-600">• {{ $it->name }}</span>
                                        </div>
                                    @endforeach
                                    @if($r->items->count() > 2)
                                        <div class="text-[10px] text-purple-700 font-bold">+ {{ $r->items->count() - 2 }} mặt hàng khác...</div>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-purple-100 text-purple-800 font-mono font-black text-xs">
                                    {{ $r->items->count() }} mặt hàng
                                </span>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                @if($r->status === 'Chờ thủ kho duyệt nhập')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                                        <i class="fa-solid fa-hourglass-half text-[10px]"></i>
                                        <span>Chờ duyệt nhập</span>
                                    </span>
                                @elseif($r->status === 'Đã nhập kho lại')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Đã nhập kho</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                        <i class="fa-solid fa-ban text-[10px]"></i>
                                        <span>{{ $r->status }}</span>
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($r->status === 'Chờ thủ kho duyệt nhập')
                                        <button type="button" @click="openApproveModal(@js($r))"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-1">
                                            <i class="fa-solid fa-check text-xs"></i>
                                            <span>Duyệt Nhập</span>
                                        </button>
                                        <button type="button" @click="openRejectModal(@js($r))"
                                                class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl border border-rose-200 transition-colors cursor-pointer" title="Từ chối nhập kho">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    @endif
                                    <button type="button" @click="openDetailModal(@js($r))"
                                            class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                    <button type="button" @click="printReturnSlip(@js($r))"
                                            class="p-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-xl border border-purple-200 transition-colors cursor-pointer" title="In phiếu hoàn kho">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold">Chưa có phiếu hàng hoàn trả nào cần xử lý</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($returns->hasPages())
                <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: XUẤT HÀNG LẺ LÊN XE (XUẤT KHO XE LƯU ĐỘNG)        -->
    <!-- ======================================================== -->
    <div x-show="activeMainTab === 'dispatches'" x-cloak class="space-y-4">
        <!-- Metrics Xuất hàng lẻ -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50/50 rounded-2xl p-4 border border-emerald-200 shadow-2xs space-y-1">
                <div class="flex items-center justify-between text-emerald-800 font-bold text-xs">
                    <span>Tổng Phiếu Xuất Xe</span>
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600 text-sm"></i>
                </div>
                <div class="text-2xl font-black text-emerald-950 font-mono">
                    {{ $totalVanDispatchesCount }} <span class="text-sm font-bold text-emerald-800">phiếu</span>
                </div>
                <div class="text-[11px] text-emerald-700 font-medium">Hôm nay: <strong class="text-emerald-900">{{ $totalVanDispatchesToday }}</strong> phiếu xuất mới</div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-2xl p-4 border border-blue-200 shadow-2xs space-y-1">
                <div class="flex items-center justify-between text-blue-800 font-bold text-xs">
                    <span>Tổng Giá Trị Hàng Xuất Lên Xe</span>
                    <i class="fa-solid fa-sack-dollar text-blue-600 text-sm"></i>
                </div>
                <div class="text-2xl font-black text-blue-950 font-mono">
                    {{ number_format($totalVanDispatchesAmount) }} <span class="text-sm font-bold text-blue-800">đ</span>
                </div>
                <div class="text-[11px] text-blue-700 font-medium">Được tự động ghi nhận vào công nợ lái xe phụ trách</div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-pink-50/50 rounded-2xl p-4 border border-purple-200 shadow-2xs space-y-1">
                <div class="flex items-center justify-between text-purple-800 font-bold text-xs">
                    <span>Liên Kết Dữ Liệu</span>
                    <i class="fa-solid fa-database text-purple-600 text-sm"></i>
                </div>
                <div class="text-sm font-bold text-purple-950 mt-1 leading-snug">
                    Dùng chung bảng <span class="font-mono bg-purple-200 px-1 py-0.5 rounded text-xs">inventory_transactions</span> & <span class="font-mono bg-purple-200 px-1 py-0.5 rounded text-xs">warehouses</span>
                </div>
                <div class="text-[11px] text-purple-700">Tự động tăng tồn kho thùng xe khi xuất kho</div>
            </div>
        </div>

        <!-- Bảng danh sách phiếu xuất lẻ xe -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="font-black text-slate-800 text-sm">Danh Sách Phiếu Bốc Hàng Lẻ Lưu Động Lên Xe</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">{{ count($vanDispatches) }} phiếu gần nhất</span>
                </div>
                <button type="button" @click="openDispatchModal()" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>+ Lập Phiếu Xuất Hàng Lẻ Lên Xe</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Mã Phiếu & Ngày Xuất</th>
                            <th class="py-3.5 px-3">Xe Nhận & Kho Xuất</th>
                            <th class="py-3.5 px-3">Tài Xế Phụ Trách</th>
                            <th class="py-3.5 px-3 text-center">Số Mặt Hàng</th>
                            <th class="py-3.5 px-3 text-right">Tổng Tiền Ghi Nợ</th>
                            <th class="py-3.5 px-3 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-4 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($vanDispatches as $disp)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-black text-emerald-700 text-xs">{{ $disp->code }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    <i class="fa-regular fa-calendar text-[10px] mr-1"></i>{{ $disp->dispatch_date ? $disp->dispatch_date->format('d/m/Y') : $disp->created_at->format('d/m/Y') }}
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold shrink-0">
                                        <i class="fa-solid fa-truck"></i>
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900 font-mono">{{ $disp->vehicle_plate }}</div>
                                        <div class="text-[11px] text-slate-500">Từ: {{ $disp->warehouse_name ?: 'Kho Tổng Mê Linh' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="font-bold text-slate-900">{{ $disp->driver_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $disp->driver?->phone ?: '---' }}</div>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-mono font-black text-xs border border-emerald-200">
                                    {{ $disp->items->count() }} SP ({{ number_format($disp->items->sum('quantity')) }})
                                </span>
                            </td>

                            <td class="py-3.5 px-3 text-right">
                                <div class="font-mono font-black text-rose-600 text-xs">{{ number_format($disp->total_amount) }} đ</div>
                                <div class="text-[10px] text-slate-400">công nợ tài xế</div>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-check text-[9px] mr-1"></i>Đã xuất xe
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openDispatchDetailModal(@js($disp))"
                                            class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer" title="Xem chi tiết các mặt hàng">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                    <button type="button" @click="printDispatchSlip(@js($disp))"
                                            class="p-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-xl border border-purple-200 transition-colors cursor-pointer" title="In phiếu xuất kho xe">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-truck-ramp-box text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold">Chưa có phiếu xuất hàng lẻ lên xe nào.</p>
                                <button type="button" @click="openDispatchModal()" class="mt-2 text-emerald-600 font-bold hover:underline cursor-pointer">
                                    + Bấm vào đây để lập phiếu bốc hàng lẻ lên xe
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: TỒN KHO HÀNG TRÊN XE (KHO LƯU ĐỘNG)               -->
    <!-- ======================================================== -->
    <div x-show="activeMainTab === 'stocks'" x-cloak class="space-y-4">
        <!-- Thẻ thống kê các kho xe -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5">
            @foreach($vehicleWarehouses as $wh)
            <div @click="selectedVehicleWhId = {{ $wh->id }}"
                 :class="selectedVehicleWhId === {{ $wh->id }} ? 'ring-2 ring-[#1b4a8c] bg-blue-50/40 border-blue-400' : 'bg-white hover:border-slate-300'"
                 class="rounded-2xl p-4 border border-slate-200 shadow-2xs transition-all cursor-pointer space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-[10.5px] border border-emerald-200">
                        <i class="fa-solid fa-truck mr-1"></i>Kho Xe Lưu Động
                    </span>
                    <span class="font-mono text-xs font-black text-slate-900">{{ $wh->plate_number }}</span>
                </div>

                <div>
                    <div class="font-black text-slate-900 text-sm truncate">{{ $wh->name }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Tài xế: <strong class="text-slate-800">{{ $wh->driver_name ?: 'Chưa gán' }}</strong></div>
                </div>

                <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase font-bold">Số Mặt Hàng</div>
                        <div class="font-mono font-bold text-slate-800">{{ $wh->total_skus }} SP ({{ number_format($wh->total_qty) }})</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] text-slate-400 uppercase font-bold">Giá Trị Tồn Xe</div>
                        <div class="font-mono font-bold text-emerald-700">{{ number_format($wh->total_val) }} đ</div>
                    </div>
                </div>

                <div class="pt-1">
                    <button type="button" @click.stop="openDispatchModalWithVehicle('{{ $wh->plate_number }}', '{{ $wh->driver_name }}')"
                            class="w-full py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl text-[11px] font-bold border border-emerald-200 transition-colors flex items-center justify-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Bốc thêm hàng lên xe này</span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Bảng chi tiết tồn kho xe đang chọn -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-[#1b4a8c]"></i>
                        <span>Chi Tiết Tồn Kho Hàng Trên Xe: <span class="text-blue-700 font-mono" x-text="getSelectedVehicleWh()?.name || 'Vui lòng chọn xe'"></span></span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Dữ liệu thực tế từ bảng tồn kho <span class="font-mono text-slate-600 font-bold">warehouse_stocks</span> theo ID kho xe</p>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="getSelectedVehicleWh()">
                        <button type="button" @click="openDispatchModalWithVehicle(getSelectedVehicleWh()?.plate_number, getSelectedVehicleWh()?.driver_name)"
                                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Bốc Hàng Lên Xe Này</span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-12">STT</th>
                            <th class="py-3.5 px-3">Mã Hàng (SKU)</th>
                            <th class="py-3.5 px-3">Tên Sản Phẩm</th>
                            <th class="py-3.5 px-3 text-center">ĐVT</th>
                            <th class="py-3.5 px-3 text-center">Tồn Trên Xe</th>
                            <th class="py-3.5 px-3 text-right">Đơn Giá Vốn</th>
                            <th class="py-3.5 px-3 text-right">Thành Tiền Tồn</th>
                            <th class="py-3.5 px-4 text-center">Vị Trí Lưu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <template x-for="(st, sIdx) in (getSelectedVehicleWh()?.stocks || [])" :key="st.id || sIdx">
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 text-center font-mono text-slate-400" x-text="sIdx + 1"></td>
                                <td class="py-3 px-3 font-mono font-bold text-slate-900" x-text="st.sku || st.product?.sku || '---'"></td>
                                <td class="py-3 px-3 font-semibold text-slate-800" x-text="st.name || st.product?.name || '---'"></td>
                                <td class="py-3 px-3 text-center" x-text="st.unit || st.product?.unit || 'cái'"></td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-mono font-bold text-xs" x-text="st.stock_quantity"></span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-slate-600" x-text="formatMoney(st.cost_price || st.product?.cost_price || 0) + ' đ'"></td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-700" x-text="formatMoney((st.stock_quantity || 0) * (st.cost_price || st.product?.cost_price || 0)) + ' đ'"></td>
                                <td class="py-3 px-4 text-center text-slate-500 italic text-[11px]" x-text="st.location || 'Thùng xe'"></td>
                            </tr>
                        </template>
                        <template x-if="!getSelectedVehicleWh() || (getSelectedVehicleWh()?.stocks || []).length === 0">
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-truck-loading text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-xs font-semibold">Kho xe này hiện chưa có mặt hàng nào lưu trong thùng xe.</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: LẬP PHIẾU BỐC HÀNG LẺ LƯU ĐỘNG LÊN XE             -->
    <!-- ======================================================== -->
    <div x-show="showDispatchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="closeDispatchModal()" class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full border border-slate-200 overflow-hidden flex flex-col max-h-[92vh]">
            <!-- Header Modal -->
            <div class="p-4 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </span>
                    <div>
                        <h3 class="font-black text-base">Lập Phiếu Bốc Hàng Lẻ Lưu Động Lên Xe (PXK-XE)</h3>
                        <p class="text-xs text-emerald-100">Xuất hàng từ kho tổng lên xe, tăng tồn kho thùng xe & ghi nhận công nợ cho tài xế</p>
                    </div>
                </div>
                <button type="button" @click="closeDispatchModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-5 overflow-y-auto space-y-4 text-xs">
                <!-- Chọn Kho & Chọn Xe -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kho Xuất Nguồn (*):</label>
                        <select x-model="vanSaleForm.warehouse_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-emerald-600">
                            @foreach($physicalWarehouses as $pwh)
                                <option value="{{ $pwh->id }}">{{ $pwh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Xe Tải Nhận Hàng (*):</label>
                        <select x-model="vanSaleForm.vehicle_plate" @change="onVehicleSelected($event.target.value)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-emerald-600">
                            <option value="">-- Chọn xe tải --</option>
                            @foreach($vehicles as $veh)
                                <option value="{{ $veh->plate_number }}" data-driver-id="{{ $veh->driver_id }}" data-driver-name="{{ $veh->driver?->name }}">
                                    {{ $veh->plate_number }} ({{ $veh->driver?->name ?: 'Chưa gán lái xe' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tài Xế Nhận Nợ (*):</label>
                        <select x-model="vanSaleForm.driver_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-emerald-600">
                            <option value="">-- Chọn tài xế --</option>
                            @foreach($vehicles as $veh)
                                @if($veh->driver)
                                    <option value="{{ $veh->driver->id }}">{{ $veh->driver->name }} ({{ $veh->plate_number }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Bảng Mặt Hàng -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-black text-slate-800 text-xs">Danh Sách Mặt Hàng Bốc Lên Xe:</label>
                        <button type="button" @click="addVanItem()" class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg border border-emerald-200 cursor-pointer flex items-center gap-1">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Thêm dòng</span>
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-visible">
                        <table class="w-full text-left text-xs divide-y divide-slate-200">
                            <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] uppercase">
                                <tr>
                                    <th class="py-2.5 px-3 w-8 text-center">STT</th>
                                    <th class="py-2.5 px-3 min-w-[280px]">Sản Phẩm (Tìm kiếm chọn nhanh)</th>
                                    <th class="py-2.5 px-2 w-24 text-center">Số Lượng</th>
                                    <th class="py-2.5 px-2 w-32 text-right">Đơn Giá (đ)</th>
                                    <th class="py-2.5 px-3 w-32 text-right">Thành Tiền</th>
                                    <th class="py-2.5 px-2 w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(it, idx) in vanSaleForm.items" :key="it.id || idx">
                                    <tr class="align-middle">
                                        <td class="py-2.5 px-3 text-center font-mono text-slate-400" x-text="idx + 1"></td>
                                        <td class="py-2 px-3 relative min-w-[280px]">
                                            <div class="relative" @click.outside="if (activeProdDropdownIndex === idx) activeProdDropdownIndex = null">
                                                <input type="text"
                                                       x-model="it.searchQuery"
                                                       @focus="activeProdDropdownIndex = idx; prodHighlightIndex = 0"
                                                       @input="activeProdDropdownIndex = idx; prodHighlightIndex = 0; if(!it.searchQuery) { it.product_id = ''; it.unit_price = 0; }"
                                                       @keydown.down.prevent="
                                                           const prods = getFilteredProducts(it.searchQuery, idx);
                                                           if (prods.length > 0) {
                                                               prodHighlightIndex = (prodHighlightIndex + 1) % prods.length;
                                                           }
                                                       "
                                                       @keydown.up.prevent="
                                                           const prods = getFilteredProducts(it.searchQuery, idx);
                                                           if (prods.length > 0) {
                                                               prodHighlightIndex = (prodHighlightIndex - 1 + prods.length) % prods.length;
                                                           }
                                                       "
                                                       @keydown.enter.prevent="
                                                           if (activeProdDropdownIndex === idx) {
                                                               const prods = getFilteredProducts(it.searchQuery, idx);
                                                               if (prods.length > 0 && prods[prodHighlightIndex]) {
                                                                   selectProductForRow(idx, prods[prodHighlightIndex]);
                                                               }
                                                           }
                                                       "
                                                       @keydown.escape.prevent="activeProdDropdownIndex = null"
                                                       placeholder="Tìm mã hoặc tên sản phẩm (phím ↑ ↓ Enter)..."
                                                       class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:border-emerald-600 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">

                                                <!-- Menu Dropdown gợi ý sản phẩm y hệt orders/create -->
                                                <div x-show="activeProdDropdownIndex === idx && getFilteredProducts(it.searchQuery, idx).length > 0"
                                                     x-cloak
                                                     class="absolute left-0 top-full mt-1 w-[460px] max-w-[90vw] z-50 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-60 overflow-y-auto py-1 text-xs">
                                                    <div class="px-3 py-1 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                                        <span>Gợi ý sản phẩm (<span x-text="getFilteredProducts(it.searchQuery, idx).length"></span>)</span>
                                                        <span class="text-emerald-700 font-normal lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                                    </div>
                                                    <template x-for="(p, pIdx) in getFilteredProducts(it.searchQuery, idx)" :key="p.id">
                                                        <div @mousedown.prevent="selectProductForRow(idx, p)"
                                                             @mouseenter="prodHighlightIndex = pIdx"
                                                             class="px-3 py-2 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                                             :class="prodHighlightIndex === pIdx ? 'bg-emerald-50 text-emerald-950 font-bold ring-1 ring-emerald-300' : 'hover:bg-slate-50'">
                                                            <div class="min-w-0 pr-3">
                                                                <div class="font-bold text-slate-900 truncate">
                                                                    <span class="text-emerald-700 font-mono mr-1" x-text="'[' + (p.sku || 'N/A') + ']'"></span>
                                                                    <span x-text="p.name"></span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                                    <span class="font-semibold text-slate-700" x-text="'ĐVT: ' + (p.unit || 'cái')"></span>
                                                                    <span>&bull;</span>
                                                                    <span :class="(p.stock_quantity || 0) > 0 ? 'text-emerald-600 font-semibold' : 'text-rose-500'" 
                                                                          x-text="'Tồn [Kho Tổng]: ' + formatMoney(p.stock_quantity || 0) + ' ' + (p.unit || 'cái')"></span>
                                                                </div>
                                                            </div>
                                                            <div class="shrink-0 text-right">
                                                                <div class="font-black text-emerald-700 text-xs" x-text="formatMoney(p.retail_price || p.cost_price || 0) + ' đ'"></div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <!-- Hiển thị badge tồn kho bên dưới khi đã chọn -->
                                            <template x-if="it.product_id">
                                                <div class="mt-1 flex items-center gap-1.5 text-[10px] text-emerald-700 font-bold">
                                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                                    <span x-text="'Đã chọn | Tồn kho tổng: ' + formatMoney(it.stock_quantity || 0) + ' ' + (it.unit || 'cái')"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="py-2 px-2 text-center">
                                            <input :id="'van-item-qty-' + idx" type="number" min="0.1" step="any" x-model.number="it.quantity" class="w-full px-2 py-1.5 text-center font-bold font-mono border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-600">
                                        </td>
                                        <td class="py-2 px-2 text-right">
                                            <input type="number" min="0" x-model.number="it.unit_price" class="w-full px-2 py-1.5 text-right font-bold font-mono border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-600">
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700" x-text="formatMoney((it.quantity || 0) * (it.unit_price || 0)) + ' đ'"></td>
                                        <td class="py-2.5 px-2 text-center">
                                            <button type="button" @click="removeVanItem(idx)" class="text-slate-400 hover:text-rose-600 p-1 cursor-pointer">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tổng tiền & Ghi chú -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ghi Chú Xuất Xe:</label>
                        <textarea x-model="vanSaleForm.notes" rows="2" placeholder="VD: Hàng lẻ xuất bốc buổi sáng, phụ trách bán tuyến..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-600"></textarea>
                    </div>

                    <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-200 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-900">TỔNG GIÁ TRỊ XUẤT LÊN XE:</span>
                            <span class="font-mono font-black text-emerald-900 text-lg" x-text="formatMoney(getVanSaleTotal()) + ' đ'"></span>
                        </div>
                        <p class="text-[11px] text-emerald-700 mt-1 leading-snug">
                            ⚠️ <strong>Quy tắc công nợ:</strong> Số tiền này sẽ tự động được cộng vào <strong>công nợ của tài xế</strong>. Lái xe bán lẻ thu tiền và lập bàn giao công nợ cho Sale sau khi bán.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs shrink-0">
                <button type="button" @click="closeDispatchModal()" class="px-4 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">
                    Hủy Bỏ
                </button>
                <button type="button" @click="submitVanSale()" :disabled="isSubmittingVanSale" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5 disabled:opacity-50">
                    <i class="fa-solid fa-check"></i>
                    <span x-text="isSubmittingVanSale ? 'Đang lưu...' : 'Xác Nhận & Xuất Hàng Lên Xe'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: XEM CHI TIẾT PHIẾU XUẤT HÀNG LẺ LÊN XE            -->
    <!-- ======================================================== -->
    <div x-show="showDispatchDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="showDispatchDetailModal = false" class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-4 bg-emerald-700 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-lg"></i>
                    <h3 class="font-bold text-sm">Chi Tiết Phiếu Xuất Hàng Lẻ Xe <span class="font-mono font-black" x-text="activeDispatch?.code"></span></h3>
                </div>
                <button type="button" @click="showDispatchDetailModal = false" class="text-white hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="p-4 overflow-y-auto space-y-4 text-xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">XE TẢI NHẬN</span>
                        <span class="font-mono font-bold text-slate-900" x-text="activeDispatch?.vehicle_plate"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">LÁI XE PHỤ TRÁCH</span>
                        <span class="font-bold text-slate-900" x-text="activeDispatch?.driver_name"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">KHO XUẤT NGUỒN</span>
                        <span class="font-bold text-slate-900" x-text="activeDispatch?.warehouse_name || 'Kho Tổng Mê Linh'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">TỔNG TIỀN GHI NỢ</span>
                        <span class="font-mono font-bold text-rose-600" x-text="formatMoney(activeDispatch?.total_amount) + ' đ'"></span>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] uppercase">
                            <tr>
                                <th class="py-2.5 px-3">STT</th>
                                <th class="py-2.5 px-3">Mã SKU & Sản Phẩm</th>
                                <th class="py-2.5 px-3 text-center">ĐVT</th>
                                <th class="py-2.5 px-3 text-center">Số Lượng</th>
                                <th class="py-2.5 px-3 text-right">Đơn Giá</th>
                                <th class="py-2.5 px-3 text-right">Thành Tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(it, iIdx) in (activeDispatch?.items || [])" :key="it.id || iIdx">
                                <tr>
                                    <td class="p-2.5 text-center font-mono text-slate-400" x-text="iIdx + 1"></td>
                                    <td class="p-2.5">
                                        <div class="font-bold text-slate-800" x-text="it.name"></div>
                                        <div class="font-mono text-[10px] text-slate-400" x-text="it.sku"></div>
                                    </td>
                                    <td class="p-2.5 text-center" x-text="it.unit"></td>
                                    <td class="p-2.5 text-center font-mono font-bold text-slate-800" x-text="it.quantity"></td>
                                    <td class="p-2.5 text-right font-mono text-slate-600" x-text="formatMoney(it.unit_price) + ' đ'"></td>
                                    <td class="p-2.5 text-right font-mono font-bold text-emerald-700" x-text="formatMoney(it.amount || ((it.quantity || 0) * (it.unit_price || 0))) + ' đ'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs shrink-0">
                <button type="button" @click="printDispatchSlip(activeDispatch)" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-xl border border-purple-200 cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>In Phiếu Xuất Kho (PXK)</span>
                </button>
                <button type="button" @click="showDispatchDetailModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL DUYỆT NHẬP KHO HÀNG HOÀN TRẢ (TAB 1)               -->
    <!-- ======================================================== -->
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="showApproveModal = false" class="bg-white rounded-2xl shadow-2xl max-w-xl w-full border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-4 bg-[#1b4a8c] text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-lg"></i>
                    <h3 class="font-bold text-sm">Duyệt Nhập Kho Hàng Hoàn Trả: <span class="font-mono font-black" x-text="activeReturn?.slip_code"></span></h3>
                </div>
                <button type="button" @click="showApproveModal = false" class="text-white hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="p-4 overflow-y-auto space-y-4 text-xs">
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900">
                    <div class="font-bold flex items-center gap-1.5 mb-1">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                        <span>Xác nhận kiểm đếm thực tế:</span>
                    </div>
                    <p class="leading-relaxed text-[11px]">Thủ kho vui lòng kiểm đếm đủ số lượng hàng hóa mang về từ xe <strong class="font-mono text-slate-900" x-text="activeReturn?.vehicle_plate"></strong> trước khi duyệt. Khi duyệt thành công, số lượng sẽ được tự động cộng vào tồn kho.</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kho tiếp nhận nhập hàng (*):</label>
                        <select x-model="selectedIntakeWarehouseId" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                            @foreach($physicalWarehouses as $pwh)
                                <option value="{{ $pwh->id }}">{{ $pwh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thủ kho thực hiện kiểm đếm (*):</label>
                        <select x-model="selectedIntakeUserId" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                            @foreach($warehouseStaff as $st)
                                <option value="{{ $st->id }}" {{ auth()->id() == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->department ?: 'Kho' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ghi chú kiểm đếm / Vị trí xếp kho:</label>
                        <textarea x-model="intakeNotes" rows="2" placeholder="VD: Hàng nguyên vẹn, đã xếp kệ B-02..." class="w-full px-3 py-2 border border-slate-300 rounded-xl"></textarea>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <div class="bg-slate-50 p-2.5 font-bold text-[11px] text-slate-700 uppercase border-b border-slate-200 flex items-center justify-between">
                        <span>Danh sách hàng hóa hoàn trả:</span>
                        <span class="font-mono text-purple-700" x-text="(activeReturn?.items || []).length + ' mặt hàng'"></span>
                    </div>
                    <div class="max-h-48 overflow-y-auto divide-y divide-slate-100 text-xs">
                        <template x-for="it in (activeReturn?.items || [])" :key="it.id">
                            <div class="p-2.5 flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-slate-800" x-text="it.name"></div>
                                    <div class="text-[10px] text-slate-400 font-mono" x-text="it.sku"></div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-mono font-bold" x-text="'+ ' + it.returned_qty + ' ' + (it.unit || 'cái')"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs shrink-0">
                <button type="button" @click="showApproveModal = false" class="px-4 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">
                    Hủy
                </button>
                <button type="button" @click="submitApproveReturn()" :disabled="isSubmittingApprove" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5 disabled:opacity-50">
                    <i class="fa-solid fa-circle-check"></i>
                    <span x-text="isSubmittingApprove ? 'Đang xử lý...' : 'Xác Nhận Nhập Kho'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL TỪ CHỐI NHẬP KHO (TAB 1)                           -->
    <!-- ======================================================== -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="showRejectModal = false" class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-slate-200 overflow-hidden">
            <div class="p-4 bg-rose-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <h3 class="font-bold text-sm">Từ Chối Nhập Kho Phiếu <span class="font-mono font-black" x-text="activeReturn?.slip_code"></span></h3>
                </div>
                <button type="button" @click="showRejectModal = false" class="text-white hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
            <div class="p-4 space-y-3 text-xs">
                <p class="text-slate-600">Vui lòng nêu rõ lý do từ chối (hàng hỏng không đạt chuẩn, sai lệch số lượng so với thực tế, v.v.):</p>
                <textarea x-model="rejectReason" rows="3" placeholder="Nhập lý do từ chối nhập kho..." class="w-full p-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-rose-500"></textarea>
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs">
                <button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">
                    Hủy
                </button>
                <button type="button" @click="submitRejectReturn()" :disabled="isSubmittingReject" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5 disabled:opacity-50">
                    <i class="fa-solid fa-ban"></i>
                    <span x-text="isSubmittingReject ? 'Đang gửi...' : 'Xác Nhận Từ Chối'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL XEM CHI TIẾT PHIẾU HOÀN TRẢ (TAB 1)               -->
    <!-- ======================================================== -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="showDetailModal = false" class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-lg text-purple-400"></i>
                    <h3 class="font-bold text-sm">Chi Tiết Phiếu Hoàn Trả: <span class="font-mono font-black text-purple-300" x-text="activeReturn?.slip_code"></span></h3>
                </div>
                <button type="button" @click="showDetailModal = false" class="text-white hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="p-4 overflow-y-auto space-y-4 text-xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">MÃ ĐƠN HÀNG</span>
                        <span class="font-mono font-bold text-slate-900" x-text="activeReturn?.order_code"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">XE VẬN CHUYỂN</span>
                        <span class="font-mono font-bold text-slate-900" x-text="activeReturn?.vehicle_plate"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">LÁI XE</span>
                        <span class="font-bold text-slate-900" x-text="activeReturn?.driver_name"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">TRẠNG THÁI</span>
                        <span class="font-bold text-purple-700" x-text="activeReturn?.status"></span>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] uppercase">
                            <tr>
                                <th class="p-2.5">Sản Phẩm Trả Về</th>
                                <th class="p-2.5 text-center">ĐVT</th>
                                <th class="p-2.5 text-center">Số Lượng</th>
                                <th class="p-2.5">Lý Do Hoàn Trả</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="it in (activeReturn?.items || [])" :key="it.id">
                                <tr>
                                    <td class="p-2.5 font-medium" x-text="it.name"></td>
                                    <td class="p-2.5 text-center" x-text="it.unit"></td>
                                    <td class="p-2.5 text-center font-bold font-mono text-rose-700" x-text="it.returned_qty"></td>
                                    <td class="p-2.5 text-slate-600 italic text-[11px]" x-text="it.reason || 'Khách trả hàng'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <template x-if="activeReturn?.notes">
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-xs whitespace-pre-line">
                        <span class="font-bold">Ghi chú lịch sử:</span>
                        <div class="mt-1" x-text="activeReturn?.notes"></div>
                    </div>
                </template>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs shrink-0">
                <button type="button" @click="printReturnSlip(activeReturn)" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-xl border border-purple-200 cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>In Phiếu Hoàn Kho</span>
                </button>
                <button type="button" @click="showDetailModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== KHUNG IN BIÊN BẢN HOÀN KHO ==================== -->
    <div id="print-return-slip-frame" class="hidden">
        <style>
            @media print {
                body * { visibility: hidden; }
                #print-return-slip-content, #print-return-slip-content * { visibility: visible; }
                #print-return-slip-content { position: absolute; left: 0; top: 0; width: 100%; }
            }
        </style>
        <div id="print-return-slip-content" class="p-8 text-black font-sans bg-white text-xs max-w-3xl mx-auto">
            <div class="text-center pb-4 border-b border-black space-y-1">
                <h2 class="text-lg font-bold uppercase">CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN</h2>
                <p class="text-[11px]">Địa chỉ: Lô 15, KCN Quang Minh, Mê Linh, Hà Nội | Hotline: 098.888.8888</p>
                <h1 class="text-xl font-black uppercase tracking-wider pt-2">BIÊN BẢN NHẬP KHO HÀNG HOÀN TRẢ</h1>
                <p class="font-mono italic">Mã phiếu: <span id="p-slip-code" class="font-bold"></span></p>
            </div>

            <div class="grid grid-cols-2 gap-4 my-4 leading-relaxed">
                <div>
                    <div><strong>Mã đơn gốc:</strong> <span id="p-order-code"></span></div>
                    <div><strong>Khách hàng:</strong> <span id="p-dealer-name"></span></div>
                    <div><strong>Xe vận chuyển:</strong> <span id="p-vehicle-plate"></span></div>
                </div>
                <div>
                    <div><strong>Lái xe:</strong> <span id="p-driver-name"></span></div>
                    <div><strong>Thời gian lập:</strong> <span id="p-date"></span></div>
                    <div><strong>Kho nhập:</strong> <span id="p-warehouse">Kho Tổng Mê Linh (MQ)</span></div>
                </div>
            </div>

            <table class="w-full border-collapse border border-black my-4 text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black p-2 text-center w-10">STT</th>
                        <th class="border border-black p-2 text-left">Tên Hàng Hóa & Quy Cách</th>
                        <th class="border border-black p-2 text-center w-16">ĐVT</th>
                        <th class="border border-black p-2 text-center w-20">SL Trả</th>
                        <th class="border border-black p-2 text-left w-40">Lý Do / Hiện Trạng</th>
                    </tr>
                </thead>
                <tbody id="p-items-body"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="border border-black p-2 font-bold text-right">TỔNG SỐ MẶT HÀNG HOÀN KHO:</td>
                        <td class="border border-black p-2 font-bold text-center font-mono" id="p-total-items-count"></td>
                        <td class="border border-black p-2"></td>
                    </tr>
                </tfoot>
            </table>

            <div class="grid grid-cols-3 text-center my-8 font-bold">
                <div>
                    <div>Người Giao (Lái xe)</div>
                    <div class="h-16"></div>
                    <div id="p-sign-driver" class="font-normal italic"></div>
                </div>
                <div>
                    <div>Người Nhận (Thủ kho)</div>
                    <div class="h-16"></div>
                    <div id="p-sign-stocker" class="font-normal italic"></div>
                </div>
                <div>
                    <div>Xác Nhận Kế Toán</div>
                    <div class="h-16"></div>
                    <div class="font-normal italic">(Ký, họ tên)</div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function vehicleInventoryApp() {
    return {
        activeMainTab: '{{ $activeTab ?? "returns" }}', // 'returns', 'dispatches', 'stocks'
        selectedVehicleWhId: {{ $vehicleWarehouses->first()?->id ?? 1 }},
        vehicleWarehousesData: @json($vehicleWarehouses ?? []),
        availableProducts: @json($productsCatalog ?? []),

        // Modal Xuất Hàng Lẻ
        showDispatchModal: false,
        isSubmittingVanSale: false,
        activeProdDropdownIndex: null,
        prodHighlightIndex: 0,
        vanSaleForm: {
            warehouse_id: 1,
            vehicle_plate: '',
            driver_id: '',
            notes: 'Xuất hàng lẻ lên xe bán lưu động',
            items: [
                { id: 1, product_id: '', searchQuery: '', sku: '', unit: '', stock_quantity: 0, quantity: 1, unit_price: 0 }
            ]
        },

        // Modal Chi tiết phiếu xuất lẻ
        showDispatchDetailModal: false,
        activeDispatch: null,

        // Modal Tab 1: Hoàn hàng
        showApproveModal: false,
        showRejectModal: false,
        showDetailModal: false,
        activeReturn: null,
        selectedIntakeWarehouseId: '{{ $physicalWarehouses->first()?->id ?? 1 }}',
        selectedIntakeUserId: '{{ auth()->id() }}',
        intakeNotes: '',
        rejectReason: '',
        isSubmittingApprove: false,
        isSubmittingReject: false,

        setMainTab(tab) {
            this.activeMainTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },

        getSelectedVehicleWh() {
            return this.vehicleWarehousesData.find(w => w.id === this.selectedVehicleWhId) || this.vehicleWarehousesData[0] || null;
        },

        formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0));
        },

        formatDate(dateStr) {
            if (!dateStr) return '---';
            const d = new Date(dateStr);
            return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('vi-VN');
        },

        // --- Logic Tab 2: Xuất Hàng Lẻ Lên Xe (Autocomplete Thuần Alpine.js) ---
        openDispatchModal() {
            this.showDispatchModal = true;
            this.activeProdDropdownIndex = null;
            this.prodHighlightIndex = 0;
            this.vanSaleForm.warehouse_id = '{{ $physicalWarehouses->first()?->id ?? 1 }}';
            this.vanSaleForm.vehicle_plate = '';
            this.vanSaleForm.driver_id = '';
            this.vanSaleForm.notes = 'Xuất hàng lẻ lên xe bán lưu động';
            this.vanSaleForm.items = [
                { id: Date.now(), product_id: '', searchQuery: '', sku: '', unit: '', stock_quantity: 0, quantity: 1, unit_price: 0 }
            ];
        },

        openDispatchModalWithVehicle(plate, driverName) {
            this.openDispatchModal();
            this.vanSaleForm.vehicle_plate = plate;
            this.onVehicleSelected(plate);
        },

        closeDispatchModal() {
            this.showDispatchModal = false;
            this.activeProdDropdownIndex = null;
        },

        onVehicleSelected(plate) {
            this.vanSaleForm.vehicle_plate = plate;
            const vehSelect = document.querySelector('select[x-model="vanSaleForm.vehicle_plate"]');
            if (vehSelect) {
                const opt = vehSelect.querySelector(`option[value="${plate}"]`);
                if (opt && opt.dataset.driverId) {
                    this.vanSaleForm.driver_id = opt.dataset.driverId;
                }
            }
        },

        addVanItem() {
            this.vanSaleForm.items.push({
                id: Date.now() + Math.floor(Math.random() * 1000),
                product_id: '',
                searchQuery: '',
                sku: '',
                unit: '',
                stock_quantity: 0,
                quantity: 1,
                unit_price: 0
            });
        },

        removeVanItem(idx) {
            if (this.vanSaleForm.items.length > 1) {
                this.vanSaleForm.items.splice(idx, 1);
            }
        },

        getFilteredProducts(query, currentIndex = -1) {
            const list = this.availableProducts || [];
            if (!query || !query.trim()) {
                return list.slice(0, 30);
            }
            const q = query.toLowerCase().trim();
            return list.filter(p => {
                const sku = (p.sku || '').toLowerCase();
                const name = (p.name || '').toLowerCase();
                return sku.includes(q) || name.includes(q);
            }).slice(0, 30);
        },

        selectProductForRow(idx, p) {
            const item = this.vanSaleForm.items[idx];
            if (!item || !p) return;
            item.product_id = p.id;
            item.sku = p.sku || '';
            item.unit = p.unit || 'cái';
            item.stock_quantity = p.stock_quantity || 0;
            item.searchQuery = '[' + (p.sku || 'N/A') + '] ' + p.name;
            const price = Number(p.retail_price || 0) > 0 ? Number(p.retail_price) : Number(p.cost_price || 0);
            item.unit_price = price;
            this.activeProdDropdownIndex = null;
            this.$nextTick(() => {
                const qtyEl = document.getElementById('van-item-qty-' + idx);
                if (qtyEl) {
                    qtyEl.focus();
                    qtyEl.select();
                }
            });
        },

        getVanSaleTotal() {
            return this.vanSaleForm.items.reduce((sum, it) => sum + (Number(it.quantity || 0) * Number(it.unit_price || 0)), 0);
        },

        async submitVanSale() {
            if (!this.vanSaleForm.vehicle_plate) {
                alert('Vui lòng chọn xe tải nhận hàng!');
                return;
            }
            if (!this.vanSaleForm.driver_id) {
                alert('Vui lòng chọn tài xế nhận nợ!');
                return;
            }

            const validItems = this.vanSaleForm.items
                .filter(i => i.product_id && Number(i.quantity) > 0)
                .map(i => ({
                    product_id: parseInt(i.product_id, 10) || i.product_id,
                    quantity: Number(i.quantity),
                    unit_price: Number(i.unit_price || 0)
                }));

            if (validItems.length === 0) {
                alert('Vui lòng chọn sản phẩm và nhập số lượng hợp lệ lớn hơn 0!');
                return;
            }

            this.isSubmittingVanSale = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/van-sale-dispatches', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        warehouse_id: this.vanSaleForm.warehouse_id,
                        vehicle_plate: this.vanSaleForm.vehicle_plate,
                        driver_id: this.vanSaleForm.driver_id,
                        notes: this.vanSaleForm.notes,
                        items: validItems
                    })
                });

                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã xuất hàng lẻ lên xe thành công!');
                    window.location.href = '/inventory/import?tab=dispatches';
                } else {
                    alert('Lỗi xuất hàng: ' + (data.message || 'Không thể thực hiện'));
                }
            } catch(e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmittingVanSale = false;
            }
        },

        openDispatchDetailModal(disp) {
            this.activeDispatch = disp;
            this.showDispatchDetailModal = true;
        },

        printDispatchSlip(disp) {
            if (!disp) return;
            const printWin = window.open('', '_blank');
            if (!printWin) {
                alert('Trình duyệt đang chặn cửa sổ pop-up. Vui lòng cho phép để in.');
                return;
            }

            let itemsRows = '';
            (disp.items || []).forEach((it, idx) => {
                const amt = Number(it.amount || ((it.quantity || 0) * (it.unit_price || 0)));
                itemsRows += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">${idx + 1}</td>
                        <td style="border: 1px solid #000; padding: 6px;"><strong>${it.name || ''}</strong><br><span style="font-size: 10px; color: #555;">${it.sku || ''}</span></td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">${it.unit || ''}</td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center; font-weight: bold;">${it.quantity}</td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: right;">${this.formatMoney(it.unit_price)} đ</td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">${this.formatMoney(amt)} đ</td>
                    </tr>
                `;
            });

            const html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>Phiếu Xuất Kho Hàng Lẻ Xe - ${disp.code}</title>
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #000; padding: 20px; }
                        h1 { text-align: center; font-size: 18px; margin-bottom: 4px; text-transform: uppercase; }
                        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                        th, td { border: 1px solid #000; padding: 6px; font-size: 11px; }
                        th { background-color: #f2f2f2; text-align: center; }
                        .info-grid { display: flex; justify-content: space-between; margin-top: 15px; }
                        .signatures { display: flex; justify-content: space-around; margin-top: 40px; text-align: center; }
                        .sign-box { width: 200px; height: 80px; }
                    </style>
                </head>
                <body>
                    <div style="text-align: center; border-bottom: 1px solid #000; padding-bottom: 8px;">
                        <h3 style="margin: 0; text-transform: uppercase;">CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN</h3>
                        <p style="margin: 2px 0; font-size: 10px;">Địa chỉ: KCN Quang Minh, Mê Linh, Hà Nội | Hotline: 098.888.8888</p>
                        <h1>PHIẾU XUẤT KHO HÀNG LẺ LƯU ĐỘNG (PXK-XE)</h1>
                        <p style="margin: 0; font-style: italic;">Mã phiếu: <strong>${disp.code}</strong> - Ngày xuất: ${disp.dispatch_date ? new Date(disp.dispatch_date).toLocaleDateString('vi-VN') : new Date().toLocaleDateString('vi-VN')}</p>
                    </div>

                    <div class="info-grid">
                        <div>
                            <div><strong>Kho xuất:</strong> ${disp.warehouse_name || 'Kho Tổng Mê Linh'}</div>
                            <div><strong>Xe tải nhận:</strong> ${disp.vehicle_plate}</div>
                        </div>
                        <div>
                            <div><strong>Tài xế nhận nợ:</strong> ${disp.driver_name}</div>
                            <div><strong>Ghi chú:</strong> ${disp.notes || 'Bốc hàng lẻ lên xe'}</div>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên Sản Phẩm & Quy Cách</th>
                                <th>ĐVT</th>
                                <th>Số Lượng</th>
                                <th>Đơn Giá</th>
                                <th>Thành Tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsRows}
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" style="text-align: right;">TỔNG CỘNG:</th>
                                <th style="text-align: center;">${(disp.items || []).reduce((s, i) => s + Number(i.quantity || 0), 0)}</th>
                                <th></th>
                                <th style="text-align: right; color: #b91c1c;">${this.formatMoney(disp.total_amount)} đ</th>
                            </tr>
                        </tfoot>
                    </table>

                    <p style="font-size: 11px; margin-top: 10px; font-style: italic;">
                        * Lưu ý: Tổng tiền <strong>${this.formatMoney(disp.total_amount)} đ</strong> được ghi nhận vào công nợ tạm giữ của tài xế. Tài xế bán hàng và quyết toán công nợ theo quy định.
                    </p>

                    <div class="signatures">
                        <div>
                            <strong>Thủ Kho Xuất</strong>
                            <div class="sign-box"></div>
                            <div>(Ký, họ tên)</div>
                        </div>
                        <div>
                            <strong>Tài Xế Nhận Hàng</strong>
                            <div class="sign-box"></div>
                            <div><strong>${disp.driver_name}</strong></div>
                        </div>
                        <div>
                            <strong>Kế Toán Kho</strong>
                            <div class="sign-box"></div>
                            <div>(Ký, họ tên)</div>
                        </div>
                    </div>
                </body>
                </html>
            `;
            printWin.document.open();
            printWin.document.write(html);
            printWin.document.close();
            printWin.focus();
            setTimeout(() => printWin.print(), 350);
        },

        // --- Logic Tab 1: Hoàn Hàng Về Kho ---
        openApproveModal(returnSlip) {
            this.activeReturn = returnSlip;
            this.selectedIntakeWarehouseId = returnSlip.intake_warehouse_id || '{{ $physicalWarehouses->first()?->id ?? 1 }}';
            this.selectedIntakeUserId = returnSlip.intake_by_user_id || '{{ auth()->id() }}';
            this.intakeNotes = returnSlip.notes || '';
            this.showApproveModal = true;
        },

        openRejectModal(returnSlip) {
            this.activeReturn = returnSlip;
            this.rejectReason = '';
            this.showRejectModal = true;
        },

        openDetailModal(returnSlip) {
            this.activeReturn = returnSlip;
            this.showDetailModal = true;
        },

        async submitApproveReturn() {
            if (!this.activeReturn) return;
            this.isSubmittingApprove = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery-returns/intake-warehouse', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        action: 'approve',
                        id: this.activeReturn.id,
                        slipCode: this.activeReturn.slip_code,
                        warehouseId: this.selectedIntakeWarehouseId,
                        intakeUserId: this.selectedIntakeUserId,
                        notes: this.intakeNotes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã duyệt nhập kho thành công!');
                    window.location.reload();
                } else {
                    alert('Lỗi duyệt nhập kho: ' + (data.message || ''));
                }
            } catch (e) {
                alert('Lỗi kết nối: ' + e.message);
            } finally {
                this.isSubmittingApprove = false;
            }
        },

        async submitRejectReturn() {
            if (!this.activeReturn || !this.rejectReason.trim()) {
                alert('Vui lòng nhập lý do từ chối nhập kho!');
                return;
            }
            if (!confirm(`Xác nhận TỪ CHỐI nhập kho phiếu ${this.activeReturn.slip_code}?`)) return;

            this.isSubmittingReject = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery-returns/intake-warehouse', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        action: 'reject',
                        id: this.activeReturn.id,
                        slipCode: this.activeReturn.slip_code,
                        rejectReason: this.rejectReason
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã từ chối phiếu hoàn trả thành công!');
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || ''));
                }
            } catch (e) {
                alert('Lỗi kết nối: ' + e.message);
            } finally {
                this.isSubmittingReject = false;
            }
        },

        printReturnSlip(returnSlip) {
            if (!returnSlip) return;
            document.getElementById('p-slip-code').innerText = returnSlip.slip_code || '';
            document.getElementById('p-order-code').innerText = returnSlip.order_code || '';
            document.getElementById('p-dealer-name').innerText = returnSlip.dealer_name || 'Khách hàng';
            document.getElementById('p-vehicle-plate').innerText = returnSlip.vehicle_plate || '';
            document.getElementById('p-driver-name').innerText = returnSlip.driver_name || '';
            document.getElementById('p-date').innerText = this.formatDate(returnSlip.created_at);
            const totalCountEl = document.getElementById('p-total-items-count');
            if (totalCountEl) {
                totalCountEl.innerText = (returnSlip.items || []).length + ' mặt hàng';
            }
            document.getElementById('p-sign-driver').innerText = returnSlip.driver_name || '(Ký, họ tên)';
            document.getElementById('p-sign-stocker').innerText = '{{ auth()->user()?->name ?? 'Thủ kho' }}';

            const tbody = document.getElementById('p-items-body');
            tbody.innerHTML = '';
            (returnSlip.items || []).forEach((it, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="border border-black p-2 text-center">${idx + 1}</td>
                    <td class="border border-black p-2"><strong>${it.name || ''}</strong><br><span style="font-size:10px; color:#555">${it.sku || ''}</span></td>
                    <td class="border border-black p-2 text-center">${it.unit || ''}</td>
                    <td class="border border-black p-2 text-center font-bold font-mono">${it.returned_qty || 0}</td>
                    <td class="border border-black p-2 text-left italic">${it.reason || 'Khách hoàn trả'}</td>
                `;
                tbody.appendChild(tr);
            });

            window.print();
        }
    };
}
</script>

@endsection
