@extends('layouts.app')

@section('title', 'Nhập Kho Hàng Hoàn Trả (Khách Trả Hàng) | ERP Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="returnsIntakeApp()">

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
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Duyệt Nhập Kho Hàng Hoàn Trả Từ Xe Giao Hàng</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-[#1b4a8c] text-[11px] font-extrabold border border-blue-200">Kho hàng & Hoàn kho</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Kiểm đếm sản phẩm khách trả lại mang về từ chuyến xe, duyệt nhập kho lưu trữ, tự động cộng lại tồn kho và giải phóng hàng trả về khỏi xe.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="window.location.reload()" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-[#1b4a8c]"></i>
                <span>Làm mới</span>
            </button>
            <a href="{{ url('/delivery-execution') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-truck-fast text-amber-400"></i>
                <span>Giao diện tài xế</span>
            </a>
        </div>
    </div>

    <!-- 3. Top Metrics Cards (Kiểm soát số lượng hàng hóa & phiếu hoàn kho, không hiển thị tiền) -->
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
                    <span>đã lưu kho lưu trữ</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Hướng dẫn nhanh -->
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

    <!-- 4. Filter & Search Controls -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-3">
        <form method="GET" action="{{ url('/inventory/import') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
            <!-- Filter Trạng thái Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ url('/inventory/import?status=all' . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap {{ $status === 'all' || !$status ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Tất cả ({{ $returns->total() }})
                </a>

                <a href="{{ url('/inventory/import?status=' . urlencode('Chờ thủ kho duyệt nhập') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Chờ thủ kho duyệt nhập' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <span>Chờ duyệt nhập</span>
                    @if($totalPendingCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'Chờ thủ kho duyệt nhập' ? 'bg-white text-amber-600' : 'bg-amber-500 text-white' }}">{{ $totalPendingCount }}</span>
                    @endif
                </a>

                <a href="{{ url('/inventory/import?status=' . urlencode('Đã nhập kho lại') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Đã nhập kho lại' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Đã nhập kho</span>
                </a>

                <a href="{{ url('/inventory/import?status=' . urlencode('Đã từ chối nhập kho') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Đã từ chối nhập kho' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 border border-rose-200 hover:bg-rose-100' }}">
                    <i class="fa-solid fa-ban"></i>
                    <span>Đã từ chối</span>
                </a>
            </div>

            <!-- Search input & Warehouse filter -->
            <div class="flex items-center gap-2 shrink-0">
                <select name="warehouse_id" onchange="this.form.submit()"
                        class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-700 text-xs focus:outline-none focus:border-[#1b4a8c]">
                    <option value="">-- Tất cả kho --</option>
                    @foreach($warehouses as $wh)
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
                    <a href="{{ url('/inventory/import') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl cursor-pointer" title="Xóa bộ lọc">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 5. Returns Slips Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-200">
                <thead class="bg-slate-50 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Mã Phiếu & Thời Gian</th>
                        <th class="py-3.5 px-3">Xe & Lái Xe</th>
                        <th class="py-3.5 px-3">Đơn Hàng & Đại Lý</th>
                        <th class="py-3.5 px-3">Mặt Hàng Trả Về</th>
                        <th class="py-3.5 px-3 text-center">Số Mặt Hàng Hoàn Trả</th>
                        <th class="py-3.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-3.5 px-4 text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($returns as $r)
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $r->status === 'Chờ thủ kho duyệt nhập' ? 'bg-amber-50/20' : '' }}">
                        <!-- Cột 1: Mã phiếu & Thời gian -->
                        <td class="py-3.5 px-4">
                            <div class="font-mono font-black text-purple-700 text-xs">{{ $r->slip_code }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                <i class="fa-regular fa-clock text-[10px] mr-1"></i>{{ $r->created_at ? $r->created_at->format('H:i d/m/Y') : '---' }}
                            </div>
                        </td>

                        <!-- Cột 2: Xe & Lái xe -->
                        <td class="py-3.5 px-3">
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-900 rounded font-black font-mono text-[11px]">
                                    <i class="fa-solid fa-truck text-[9px] mr-1"></i>{{ $r->vehicle_plate ?: '---' }}
                                </span>
                            </div>
                            <div class="font-bold text-slate-800 text-[11px] mt-1">{{ $r->driver_name ?: '---' }}</div>
                            @if($r->copilot_name)
                                <div class="text-[10px] text-slate-400">Phụ: {{ $r->copilot_name }}</div>
                            @endif
                        </td>

                        <!-- Cột 3: Đơn hàng & Đại lý -->
                        <td class="py-3.5 px-3">
                            <div class="font-mono font-bold text-slate-900">{{ $r->order_code ?: '---' }}</div>
                            <div class="font-semibold text-slate-700 text-[11px] truncate max-w-[200px]" title="{{ $r->dealer_name }}">
                                {{ $r->dealer_name ?: 'Khách hàng lẻ' }}
                            </div>
                        </td>

                        <!-- Cột 4: Mặt hàng trả về (tóm tắt) -->
                        <td class="py-3.5 px-3">
                            <div class="space-y-1 max-w-[260px]">
                                @foreach($r->items->take(2) as $it)
                                    <div class="text-[11px] text-slate-700 truncate">
                                        <span class="font-bold text-rose-700">{{ (float)$it->returned_qty }} {{ $it->unit }}</span>
                                        <span class="font-medium text-slate-800">- {{ $it->name }}</span>
                                    </div>
                                @endforeach
                                @if($r->items->count() > 2)
                                    <div class="text-[10px] text-slate-400 font-bold italic">
                                        + {{ $r->items->count() - 2 }} sản phẩm khác...
                                    </div>
                                @endif
                                @if($r->items->isEmpty())
                                    <span class="text-slate-400 italic">Chưa có chi tiết</span>
                                @endif
                            </div>
                        </td>

                        <!-- Cột 5: Quy mô mặt hàng hoàn trả (Không hiển thị tiền) -->
                        <td class="py-3.5 px-3 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 text-purple-900 font-extrabold rounded-lg text-xs border border-purple-200 shadow-2xs">
                                <i class="fa-solid fa-boxes-stacked text-purple-600 text-[10px]"></i>
                                <span>{{ $r->items->count() }} mặt hàng</span>
                            </span>
                            <div class="text-[11px] text-slate-500 font-semibold mt-1">
                                Tổng SL: <strong class="text-slate-800 font-mono">{{ number_format($r->items->sum('returned_qty')) }}</strong>
                            </div>
                        </td>

                        <!-- Cột 6: Trạng thái -->
                        <td class="py-3.5 px-3 text-center">
                            @if($r->status === 'Chờ thủ kho duyệt nhập')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                                    <i class="fa-solid fa-hourglass-half text-[10px]"></i>
                                    <span>Chờ duyệt nhập</span>
                                </span>
                            @elseif($r->status === 'Đã nhập kho lại')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                    <span>Đã nhập kho</span>
                                </span>
                                @if($r->intake_at)
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($r->intake_at)->format('H:i d/m') }}</div>
                                @endif
                            @elseif($r->status === 'Đã từ chối nhập kho')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-rose-100 text-rose-900 border border-rose-300">
                                    <i class="fa-solid fa-ban text-[10px]"></i>
                                    <span>Đã từ chối</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-blue-100 text-blue-900 border border-blue-200">
                                    <i class="fa-solid fa-truck text-[10px]"></i>
                                    <span>{{ $r->status }}</span>
                                </span>
                            @endif
                        </td>

                        <!-- Cột 7: Thao tác -->
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($r->status === 'Chờ thủ kho duyệt nhập')
                                    <button type="button" @click="openApproveModal({{ json_encode($r) }})"
                                            class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-xs cursor-pointer flex items-center gap-1"
                                            title="Kiểm đếm & Duyệt nhập kho lại">
                                        <i class="fa-solid fa-box-archive text-[10px]"></i>
                                        <span>Duyệt</span>
                                    </button>

                                    <button type="button" @click="openRejectModal({{ json_encode($r) }})"
                                            class="px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-bold text-xs cursor-pointer"
                                            title="Từ chối nhập kho">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                @endif

                                <button type="button" @click="openDetailModal({{ json_encode($r) }})"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold text-xs cursor-pointer"
                                        title="Xem chi tiết phiếu hoàn trả">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>

                                <button type="button" @click="printReturnSlip({{ json_encode($r) }})"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-purple-700 rounded-lg font-bold text-xs cursor-pointer"
                                        title="In biên bản hoàn kho">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-xl">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            Không tìm thấy phiếu hàng hoàn trả nào phù hợp điều kiện lọc.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs">
            <div class="text-slate-500 font-medium">
                Hiển thị từ {{ $returns->firstItem() }} đến {{ $returns->lastItem() }} trên tổng số {{ $returns->total() }} phiếu
            </div>
            <div>
                {{ $returns->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- ==================== MODAL 1: DUYỆT NHẬP KHO ==================== -->
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto" @click.away="showApproveModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Xác Nhận Kiểm Đếm & Duyệt Nhập Kho</h3>
                        <p class="text-xs text-slate-500 font-medium">Thủ kho xác nhận đã nhận bàn giao đủ hàng hoàn trả và cộng lại tồn kho</p>
                    </div>
                </div>
                <button type="button" @click="showApproveModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Tóm tắt phiếu -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-slate-500 font-medium">Mã phiếu hoàn trả:</span>
                        <span class="font-mono font-black text-purple-700 text-sm ml-1" x-text="activeReturn?.slip_code"></span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium">Mã đơn gốc:</span>
                        <span class="font-mono font-bold text-slate-900 ml-1" x-text="activeReturn?.order_code"></span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                    <div>
                        <span class="text-slate-500 font-medium">Xe & Lái xe:</span>
                        <span class="font-bold text-slate-800 ml-1" x-text="(activeReturn?.vehicle_plate || '') + ' - ' + (activeReturn?.driver_name || '')"></span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium">Khách hàng:</span>
                        <span class="font-bold text-slate-800 ml-1" x-text="activeReturn?.dealer_name || 'Khách lẻ'"></span>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách mặt hàng hoàn trả: KIỂM ĐẾM SỐ LƯỢNG (KHÔNG HIỂN THỊ TIỀN) -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800">Danh sách sản phẩm hoàn trả kiểm đếm:</span>
                    <span class="text-[11px] text-slate-400 font-medium">Kiểm đếm quy cách & số lượng thực tế nhận từ xe</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold text-[11px]">
                            <tr>
                                <th class="p-2.5">Sản phẩm & SKU</th>
                                <th class="p-2.5 text-center">ĐVT</th>
                                <th class="p-2.5 text-center">SL Hoàn Trả</th>
                                <th class="p-2.5 text-left">Lý Do Trả Hàng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="it in (activeReturn?.items || [])" :key="it.id">
                                <tr>
                                    <td class="p-2.5">
                                        <div class="font-bold text-slate-800" x-text="it.name"></div>
                                        <div class="text-[10px] font-mono text-slate-400" x-text="it.sku"></div>
                                    </td>
                                    <td class="p-2.5 text-center font-medium text-slate-600" x-text="it.unit"></td>
                                    <td class="p-2.5 text-center font-black font-mono text-rose-700 bg-rose-50/50" x-text="it.returned_qty"></td>
                                    <td class="p-2.5 text-slate-600 italic text-[11px]" x-text="it.reason || 'Khách trả hàng'"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-purple-50/60 font-black text-xs border-t border-purple-200">
                            <tr>
                                <td colspan="2" class="p-2.5 text-right text-slate-700">TỔNG SỐ LƯỢNG MẶT HÀNG:</td>
                                <td class="p-2.5 text-center font-mono text-sm text-purple-900" x-text="(activeReturn?.items || []).length + ' mặt hàng'"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Form chọn Kho nhập và Ghi chú -->
            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-700 block">Kho lưu trữ nhập lại <span class="text-rose-500">*</span>:</label>
                        <select x-model="approveForm.warehouseId"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-emerald-600 bg-white">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-700 block">Thủ kho / Người duyệt:</label>
                        <input type="text" readonly :value="'{{ auth()->user()?->name ?? 'Thủ kho' }}'"
                               class="w-full p-2.5 rounded-xl border border-slate-200 font-bold text-xs text-slate-700 bg-slate-100 cursor-not-allowed">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Ghi chú xác nhận nhập kho:</label>
                    <input type="text" x-model="approveForm.notes" placeholder="VD: Đã kiểm đếm đầy đủ quy cách, bao bì nguyên vẹn..."
                           class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-xs text-slate-800 focus:outline-none focus:border-emerald-600 bg-white">
                </div>

                <!-- Alert lưu ý quan trọng -->
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-start gap-2 text-emerald-900 text-xs">
                    <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 shrink-0"></i>
                    <div>
                        <div class="font-bold">Quy trình tự động sau khi duyệt:</div>
                        <div class="text-[11px] text-emerald-800 mt-0.5">
                            Hệ thống sẽ tự động <strong>CỘNG LẠI TỒN KHO</strong> cho từng sản phẩm theo số lượng hoàn trả và <strong>GIẢI PHÓNG</strong> hàng hoàn trả ra khỏi xe của tài xế.
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showApproveModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitApproveReturn()" :disabled="isSubmittingApprove"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-emerald-600/20 disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-box-archive"></i>
                    <span x-text="isSubmittingApprove ? 'Đang duyệt nhập kho...' : 'Xác Nhận Duyệt Nhập Kho & Cộng Tồn Kho'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL 2: TỪ CHỐI NHẬP KHO ==================== -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showRejectModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Từ Chối Nhập Kho Hàng Hoàn Trả</h3>
                        <p class="text-xs text-slate-500 font-medium">Phiếu <span class="font-mono font-bold text-purple-700" x-text="activeReturn?.slip_code"></span></p>
                    </div>
                </div>
                <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 text-rose-900">
                    <div class="font-bold">Lưu ý khi từ chối nhập kho:</div>
                    <div class="text-[11px] text-rose-800 mt-0.5">
                        Hàng hoàn trả sẽ không được nhập lại kho và không cộng số lượng tồn. Lái xe và điều phối sẽ nhận được thông báo về lý do từ chối.
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Lý do từ chối nhập kho <span class="text-rose-500">*</span>:</label>
                    <textarea x-model="rejectReason" rows="3" placeholder="Nhập lý do cụ thể: Hàng bị vỡ hỏng, thiếu hàng so với biên bản, sai quy cách..."
                              class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-xs text-slate-800 focus:outline-none focus:border-rose-600 bg-white"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitRejectReturn()" :disabled="isSubmittingReject || !rejectReason.trim()"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-rose-600/20 disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-xmark"></i>
                    <span x-text="isSubmittingReject ? 'Đang từ chối...' : 'Xác Nhận Từ Chối Nhập Kho'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL 3: XEM CHI TIẾT PHIẾU ==================== -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto" @click.away="showDetailModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Chi Tiết Biên Bản Hoàn Kho</h3>
                        <p class="text-xs text-slate-500 font-medium">Phiếu <span class="font-mono font-bold text-purple-700" x-text="activeReturn?.slip_code"></span></p>
                    </div>
                </div>
                <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <div><span class="text-slate-500">Mã đơn hàng:</span> <span class="font-mono font-bold ml-1" x-text="activeReturn?.order_code"></span></div>
                    <div><span class="text-slate-500">Khách hàng:</span> <span class="font-bold ml-1" x-text="activeReturn?.dealer_name"></span></div>
                    <div><span class="text-slate-500">Xe chở về:</span> <span class="font-mono font-bold text-blue-700 ml-1" x-text="activeReturn?.vehicle_plate"></span></div>
                    <div><span class="text-slate-500">Lái xe:</span> <span class="font-bold ml-1" x-text="activeReturn?.driver_name"></span></div>
                    <div><span class="text-slate-500">Thời gian tạo:</span> <span class="font-medium ml-1" x-text="formatDate(activeReturn?.created_at)"></span></div>
                    <div><span class="text-slate-500">Trạng thái:</span> <span class="font-bold ml-1" x-text="activeReturn?.status"></span></div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold text-[11px]">
                            <tr>
                                <th class="p-2.5">Sản phẩm</th>
                                <th class="p-2.5 text-center">ĐVT</th>
                                <th class="p-2.5 text-center">SL Hoàn Trả</th>
                                <th class="p-2.5 text-left">Lý Do Trả Hàng</th>
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
                        <tfoot class="bg-purple-50/60 font-bold text-xs border-t border-purple-200">
                            <tr>
                                <td colspan="2" class="p-2.5 text-right font-black text-slate-700">TỔNG SỐ LƯỢNG MẶT HÀNG:</td>
                                <td class="p-2.5 text-center font-mono font-black text-purple-900 text-xs" x-text="(activeReturn?.items || []).length + ' mặt hàng'"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <template x-if="activeReturn?.notes">
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-xs whitespace-pre-line">
                        <span class="font-bold">Ghi chú lịch sử:</span>
                        <div class="mt-1" x-text="activeReturn?.notes"></div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
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
                    <div>Kế Toán Kho</div>
                    <div class="h-16"></div>
                    <div class="font-normal italic">(Ký, họ tên)</div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Alpine.js Application Logic -->
<script>
function returnsIntakeApp() {
    return {
        showApproveModal: false,
        showRejectModal: false,
        showDetailModal: false,
        isSubmittingApprove: false,
        isSubmittingReject: false,
        activeReturn: null,
        rejectReason: '',
        approveForm: {
            warehouseId: {{ $warehouses->first()->id ?? 4 }},
            notes: ''
        },

        formatNumber(val) {
            return (Number(val) || 0).toLocaleString('vi-VN');
        },

        formatDate(dateStr) {
            if (!dateStr) return '---';
            try {
                const d = new Date(dateStr);
                return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) + ' ' + d.toLocaleDateString('vi-VN');
            } catch (e) {
                return dateStr;
            }
        },

        openApproveModal(returnSlip) {
            this.activeReturn = returnSlip;
            this.approveForm.warehouseId = returnSlip.intake_warehouse_id || {{ $warehouses->first()->id ?? 4 }};
            this.approveForm.notes = 'Đã kiểm đếm đầy đủ và duyệt nhập kho lại hàng hoàn trả.';
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
            const itemCount = (this.activeReturn.items || []).length;
            const confirmMsg = `Xác nhận duyệt nhập kho lại phiếu ${this.activeReturn.slip_code} (${itemCount} mặt hàng) và TỰ ĐỘNG CỘNG LẠI TỒN KHO?`;
            if (!confirm(confirmMsg)) return;

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
                        warehouseId: this.approveForm.warehouseId,
                        notes: this.approveForm.notes
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
    }
}
</script>
@endsection
