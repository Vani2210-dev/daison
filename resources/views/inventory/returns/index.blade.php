@extends('layouts.app')

@section('title', 'Duyệt Nhập Kho Hàng Hoàn Trả (Khách trả & Xe hoàn) | ERP Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="returnsIntakeApp()">

<!-- 2. Header Banner -->
    <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-2xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <a href="{{ url('/cat_products') }}" class="w-8 h-8 rounded-xl bg-[#1b4a8c] hover:bg-blue-800 text-white flex items-center justify-center shrink-0 shadow-xs cursor-pointer transition-transform active:scale-95" title="Quay lại danh mục sản phẩm">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Duyệt Nhập Kho Hàng Hoàn Trả</span>
                    @if(($totalPendingCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                            {{ $totalPendingCount }} phiếu chờ duyệt
                        </span>
                    @endif
                </h1>
                <p class="text-[11px] text-slate-500 hidden sm:block">Kiểm đếm và xác nhận nhập lại hàng từ xe giao hàng hoặc khách trả vào tồn kho công ty</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/inventory/holding') }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-boxes-stacked text-[#1b4a8c]"></i>
                <span class="hidden sm:inline">Xem</span> <span>Kho hàng ôm</span>
            </a>
            <button type="button" @click="window.location.reload()" class="p-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs cursor-pointer" title="Làm mới">
                <i class="fa-solid fa-arrows-rotate text-[#1b4a8c]"></i>
            </button>
        </div>
    </div>

    <!-- 3. Top Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-gradient-to-br from-amber-50 to-orange-50/40 rounded-2xl p-3.5 border border-amber-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-amber-800">Chờ duyệt nhập</div>
                <div class="text-2xl font-black text-amber-950 font-mono tracking-tight mt-0.5">
                    {{ $totalPendingCount }} <span class="text-xs font-bold text-amber-700">phiếu ({{ number_format($totalPendingItemsCount ?? 0) }} SP)</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-amber-200/70 text-amber-900 flex items-center justify-center text-sm font-bold shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-teal-50/40 rounded-2xl p-3.5 border border-emerald-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-emerald-800">Đã nhập hôm nay</div>
                <div class="text-2xl font-black text-emerald-950 font-mono tracking-tight mt-0.5">
                    {{ $approvedTodayCount }} <span class="text-xs font-bold text-emerald-700">phiếu ({{ number_format($approvedTodayItemsCount ?? 0) }} SP)</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-emerald-200/70 text-emerald-900 flex items-center justify-center text-sm font-bold shrink-0">
                <i class="fa-solid fa-check"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-indigo-50/40 rounded-2xl p-3.5 border border-blue-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-blue-800">Đã nhập tháng {{ now()->month }}</div>
                <div class="text-2xl font-black text-blue-950 font-mono tracking-tight mt-0.5">
                    {{ $totalMonthApprovedCount ?? $approvedTodayCount }} <span class="text-xs font-bold text-blue-700">phiếu ({{ number_format($totalMonthItemsCount ?? 0) }} SP)</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-blue-200/70 text-blue-900 flex items-center justify-center text-sm font-bold shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <!-- 4. Filter & Search Controls -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-3">
        <form method="GET" action="{{ url('/inventory/returns') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
            <!-- Filter Trạng thái Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ url('/inventory/returns?status=all' . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap {{ $status === 'all' || !$status ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Tất cả ({{ $returns->total() }})
                </a>

                <a href="{{ url('/inventory/returns?status=' . urlencode('Chờ thủ kho duyệt nhập') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Chờ thủ kho duyệt nhập' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <span>Chờ duyệt</span>
                    @if($totalPendingCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'Chờ thủ kho duyệt nhập' ? 'bg-white text-amber-600' : 'bg-amber-500 text-white' }}">{{ $totalPendingCount }}</span>
                    @endif
                </a>

                <a href="{{ url('/inventory/returns?status=' . urlencode('Đã nhập kho lại') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Đã nhập kho lại' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Đã nhập</span>
                </a>

                <a href="{{ url('/inventory/returns?status=' . urlencode('Đã từ chối nhập kho') . ($warehouseId ? '&warehouse_id='.$warehouseId : '') . ($search ? '&search='.$search : '')) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Đã từ chối nhập kho' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 border border-rose-200 hover:bg-rose-100' }}">
                    <i class="fa-solid fa-ban"></i>
                    <span>Từ chối</span>
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
                    <a href="{{ url('/inventory/returns') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl cursor-pointer" title="Xóa bộ lọc">
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
                        <th class="py-3 px-3">Mã & Ngày</th>
                        <th class="py-3 px-3">Người trả</th>
                        <th class="py-3 px-3">Đơn & Khách</th>
                        <th class="py-3 px-3">Mặt hàng</th>
                        <th class="py-3 px-3 text-center">Số SP</th>
                        <th class="py-3 px-3 text-center">Trạng thái</th>
                        <th class="py-3 px-3 text-center">Thao tác</th>
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
                                <span class="w-7 h-7 rounded-lg bg-blue-100 text-[#1b4a8c] flex items-center justify-center text-xs font-bold shrink-0">
                                    <i class="fa-solid fa-user-tag"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $r->driver_name ?: ($r->order?->order_creator ?: 'Nhân viên kinh doanh') }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $r->vehicle_plate ?: 'Hàng ôm NVKD' }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-3">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                @if($r->order_code)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                                        <i class="fa-solid fa-truck-ramp-box text-[9px]"></i> Xe hoàn đơn
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fa-solid fa-boxes-packing text-[9px]"></i> Hàng ôm
                                    </span>
                                @endif
                            </div>
                            <div class="font-bold text-slate-900 font-mono">{{ $r->order_code ?: 'Hàng ôm lưu động' }}</div>
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
                                    <span>Chờ duyệt</span>
                                </span>
                            @elseif($r->status === 'Đã nhập kho lại')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                    <span>Đã nhập</span>
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
                            <p class="text-xs font-semibold">Chưa có phiếu hoàn hàng nào cần xử lý</p>
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

    <!-- ======================================================== -->
    <!-- MODAL DUYỆT NHẬP KHO HÀNG HOÀN TRẢ                       -->
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
    <!-- MODAL TỪ CHỐI NHẬP KHO                                   -->
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
    <!-- MODAL XEM CHI TIẾT PHIẾU HOÀN TRẢ                        -->
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
                        <span class="text-slate-400 block text-[10px] font-bold">LÁI XE / NGƯỜI TRẢ</span>
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
            @php
                $returnComp = \App\Models\CompanySetting::getSettings();
            @endphp
            <div class="text-center pb-4 border-b border-black space-y-1">
                <h2 class="text-lg font-bold uppercase">{{ $returnComp->company_name ?? 'CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN' }}</h2>
                <p class="text-[11px]">{{ $returnComp->address ? 'Địa chỉ: ' . $returnComp->address : '' }}{{ ($returnComp->hotline || $returnComp->phone) ? ' | Hotline: ' . ($returnComp->hotline ?: $returnComp->phone) : '' }}</p>
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
                    <div>Người Giao (Lái xe / NVKD)</div>
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
function returnsIntakeApp() {
    return {
        activeReturn: null,
        showApproveModal: false,
        showRejectModal: false,
        showDetailModal: false,

        selectedIntakeWarehouseId: '{{ $physicalWarehouses->first()?->id ?? 1 }}',
        selectedIntakeUserId: '{{ auth()->id() }}',
        intakeNotes: '',

        rejectReason: '',
        isSubmittingApprove: false,
        isSubmittingReject: false,

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

        formatDate(dateStr) {
            if (!dateStr) return '---';
            try {
                const d = new Date(dateStr);
                return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')} ${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
            } catch (e) {
                return dateStr;
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

