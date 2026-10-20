@extends('layouts.app')

@section('title', 'Sổ Chi Tiết Công Nợ Nhân Viên - ' . ($staff->name ?? 'Nhân sự'))

@section('content')
<div x-data="{ activeTab: '{{ request('tab', 'debt') }}' }" class="space-y-4 print:space-y-2 font-sans text-slate-800 antialiased">

    <!-- ==================== HEADER ACTION BAR (ẨN KHI IN) ==================== -->
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200 p-4 print:hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ url('/staff-debt') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors shadow-2xs" title="Quay lại danh sách công nợ nhân viên">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                        <a href="{{ url('/staff-debt') }}" class="hover:text-sky-700 transition-colors font-semibold">Công nợ nhân sự</a>
                        <i class="fa-solid fa-chevron-right text-[9px]"></i>
                        <span class="text-slate-700 font-bold">Sổ chi tiết công nợ</span>
                    </nav>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-black bg-purple-100 text-purple-900 border border-purple-200 font-mono">
                            {{ $staff->code ?: ('NV-' . str_pad($staff->id, 3, '0', STR_PAD_LEFT)) }}
                        </span>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ $staff->name }}</h1>
                        @if($staff->staff_type === 'driver')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                🚚 Lái xe / Vận chuyển
                            </span>
                        @elseif($staff->staff_type === 'dealer_sales')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                🤝 Sale Đại Lý Phụ Thuộc
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-900 border border-sky-200">
                                💼 Sale Công Ty
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-print text-slate-600"></i>
                    <span>In Sổ Công Nợ</span>
                </button>
                <a href="{{ url('/orders?tab=consignment') }}" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer" title="Lập đơn xuất hàng từ kho công ty vào kho ôm của nhân viên">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>+ Thêm Hàng Vào Kho Ôm</span>
                </a>
                <a href="{{ url('/funds') }}?partner={{ urlencode($staff->name) }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Lập Phiếu Thu Quỹ</span>
                </a>
                <a href="{{ url('/staff-debt') }}" class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                    <i class="fa-solid fa-share-from-square"></i>
                    <span>Lập Bàn Giao Nợ</span>
                </a>
            </div>
        </div>

        <!-- Metric Financial Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-4 pt-4 border-t border-slate-100">
            <!-- 1. Dư nợ hiện tại -->
            <div class="p-3.5 rounded-2xl border {{ $currentDebt > 0 ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50 border-slate-200' }}">
                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider {{ $currentDebt > 0 ? 'text-rose-800' : 'text-slate-500' }}">
                    <span>Dư Nợ Hiện Tại</span>
                    @if($currentDebt > 0)
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    @endif
                </div>
                <p class="text-xl sm:text-2xl font-black font-mono mt-1.5 {{ $currentDebt > 0 ? 'text-rose-700' : 'text-slate-800' }}">
                    {{ number_format($currentDebt, 0, ',', '.') }} đ
                </p>
                <div class="text-[10px] {{ $currentDebt > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }} mt-0.5">
                    {{ $currentDebt > 0 ? 'Nhân sự đang chịu trách nhiệm nợ' : 'Không có nợ tồn đọng' }}
                </div>
            </div>

            <!-- 2. Hàng trong kho ôm -->
            <div class="p-3.5 rounded-2xl border bg-amber-50/70 border-amber-300">
                <div class="flex items-center justify-between text-[11px] font-bold text-amber-900 uppercase tracking-wider">
                    <span>Hàng Trong Kho Ôm</span>
                    <i class="fa-solid fa-boxes-stacked text-amber-600 text-xs"></i>
                </div>
                <p class="text-xl sm:text-2xl font-black font-mono text-amber-950 mt-1.5">
                    {{ number_format($totalConsignmentRemaining, 0, ',', '.') }} đ
                </p>
                <div class="text-[10px] text-amber-800 font-medium mt-0.5">
                    {{ $totalHoldingItemsCount }} sản phẩm đang giữ trong kho
                </div>
            </div>

            <!-- 3. Hạn mức công nợ -->
            <div class="p-3.5 rounded-2xl border bg-blue-50/60 border-blue-200">
                <span class="text-[11px] font-bold text-blue-800 uppercase tracking-wider">Hạn Mức Cho Phép</span>
                <p class="text-xl sm:text-2xl font-black font-mono text-blue-950 mt-1.5">
                    {{ number_format($creditLimit, 0, ',', '.') }} đ
                </p>
                <div class="text-[10px] text-blue-700 font-medium mt-0.5">
                    Hạn mức tối đa công ty cấp
                </div>
            </div>

            <!-- 4. Nợ lái xe / COD treo -->
            <div class="p-3.5 rounded-2xl border bg-orange-50/60 border-orange-200">
                <span class="text-[11px] font-bold text-orange-800 uppercase tracking-wider">Nợ Đơn Hàng / COD Xe</span>
                <p class="text-xl sm:text-2xl font-black font-mono text-orange-950 mt-1.5">
                    {{ number_format($totalDriverDebt, 0, ',', '.') }} đ
                </p>
                <div class="text-[10px] text-orange-700 font-medium mt-0.5">
                    COD và nợ treo theo chuyến giao
                </div>
            </div>

            <!-- 5. Đã bàn giao nợ đi -->
            <div class="p-3.5 rounded-2xl border bg-emerald-50/60 border-emerald-200">
                <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Đã Bàn Giao Đi</span>
                <p class="text-xl sm:text-2xl font-black font-mono text-emerald-950 mt-1.5">
                    {{ number_format($totalHandoverConfirmed, 0, ',', '.') }} đ
                </p>
                <div class="text-[10px] text-emerald-700 font-medium mt-0.5">
                    {{ $handoversSent->where('status', 'confirmed')->count() }} biên bản đã xác nhận
                </div>
            </div>

            <!-- 6. Nhận nợ về -->
            <div class="p-3.5 rounded-2xl border bg-purple-50/60 border-purple-200">
                <span class="text-[11px] font-bold text-purple-800 uppercase tracking-wider">Nhận Bàn Giao Về</span>
                <p class="text-xl sm:text-2xl font-black font-mono text-purple-950 mt-1.5">
                    {{ number_format($totalHandoverReceived, 0, ',', '.') }} đ
                </p>
                <div class="text-[10px] text-purple-700 font-medium mt-0.5">
                    {{ $handoversReceived->where('status', 'confirmed')->count() }} biên bản nhận nợ
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== SUB-TABS NAVIGATION (2 TABS: CÔNG NỢ & HÀNG ĐANG GIỮ) ==================== -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 print:hidden">
        <!-- Tab 1: Công Nợ -->
        <button type="button" @click="activeTab = 'debt'"
                :class="activeTab === 'debt' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all cursor-pointer">
            <i class="fa-solid fa-hand-holding-dollar text-sm"></i>
            <span>1. Sổ Công Nợ ({{ number_format($currentDebt, 0, ',', '.') }} đ)</span>
            <span :class="activeTab === 'debt' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">
                {{ count($consignmentOrders) }} nhận ôm &bull; {{ count($holdingSaleOrders) }} bán kho ôm &bull; {{ count($deliveryOrders) }} đơn xe &bull; {{ count($handoversSent) + count($handoversReceived) }} bàn giao
            </span>
        </button>

        <!-- Tab 2: Hàng Đang Giữ -->
        <button type="button" @click="activeTab = 'holding_stock'"
                :class="activeTab === 'holding_stock' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all cursor-pointer">
            <i class="fa-solid fa-boxes-stacked text-sm"></i>
            <span>2. Hàng Đang Giữ Trong Kho Ôm</span>
            <span :class="activeTab === 'holding_stock' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-900 border border-amber-200'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">
                {{ count($holdingStocks) }} mặt hàng ({{ $totalHoldingItemsCount }} sp)
            </span>
        </button>
    </div>

    <!-- ==================== TIÊU ĐỀ BẢN IN (CHỈ HIỂN THỊ KHI IN) ==================== -->
    <div class="hidden print:block text-center py-2 border-b border-slate-300">
        <h2 class="text-xl font-bold uppercase tracking-wide">CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN</h2>
        <h3 class="text-lg font-bold uppercase text-slate-800 mt-1">SỔ CHI TIẾT CÔNG NỢ NHÂN VIÊN & NGƯỜI VẬN CHUYỂN</h3>
        <p class="text-xs text-slate-600 mt-0.5">
            Nhân viên: <strong>{{ $staff->name }}</strong> (Mã: {{ $staff->code ?: ('NV-' . str_pad($staff->id, 3, '0', STR_PAD_LEFT)) }}) 
            - Phòng ban: <strong>{{ $staff->department ?: 'N/A' }}</strong> - ĐT: <strong>{{ $staff->phone ?: '---' }}</strong>
        </p>
        <p class="text-xs text-slate-700 font-bold mt-0.5">
            Dư nợ hiện tại: <span class="text-rose-700">{{ number_format($currentDebt, 0, ',', '.') }} đ</span> 
            - Hàng trong kho ôm: {{ number_format($totalConsignmentRemaining, 0, ',', '.') }} đ
            - Hạn mức: {{ number_format($creditLimit, 0, ',', '.') }} đ
        </p>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: SỔ CÔNG NỢ & GIAO NHẬN (ĐƠN HÀNG ÔM, ĐƠN BÁN, BÀN GIAO & QUỸ)       -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'debt'" class="space-y-4 print:block">

        <!-- ==================== KHỐI 1: ĐƠN XUẤT KHO HÀNG ÔM (KÝ GỬI PHÁT SINH CÔNG NỢ) ==================== -->
        <div class="bg-white rounded-2xl shadow-2xs border border-amber-200 overflow-hidden">
            <div class="p-4 border-b border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-amber-50/80 via-orange-50/40 to-white">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                        <span>Đơn Xuất Kho Hàng Ôm (Ký Gửi Bán Lẻ & Phát Sinh Công Nợ)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Các đợt nhận hàng ôm từ kho công ty giao cho nhân viên đi thị trường – số tiền nợ tính trên các mặt hàng chưa bán lẻ hoặc chưa quyết toán
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-black text-xs border border-amber-200">
                        {{ count($consignmentOrders) }} đợt nhận ôm
                    </span>
                    <a href="{{ url('/orders?tab=consignment') }}" class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>+ Thêm Đơn Ôm</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-amber-50 text-slate-700 font-bold uppercase text-[11px] border-b border-amber-200">
                            <th class="py-2.5 px-3 w-10 text-center">STT</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Mã Đơn Ôm</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Ngày Nhận</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Kho Xuất Công Ty</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Tổng Trị Giá Ôm</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Đã Xuất Bán</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap bg-rose-50/70 border-x border-rose-200 text-rose-900 font-black">Nợ Hàng Ôm Còn Lại</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Trạng Thái</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap print:hidden">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($consignmentOrders as $idx => $co)
                            @php
                                $whId = $holdingWarehouse ? $holdingWarehouse->id : 9;
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold text-sky-800 whitespace-nowrap">
                                    {{ $co->code }}
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                    {{ $co->created_at ? $co->created_at->format('d/m/Y') : '---' }}
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                    {{ $co->warehouse?->name ?? 'Kho tổng công ty' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ number_format($co->total_amount, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono text-emerald-700 font-bold whitespace-nowrap">
                                    {{ number_format($co->sold_amount ?? 0, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-rose-700 whitespace-nowrap bg-rose-50/40 border-x border-rose-100 text-sm">
                                    {{ number_format($co->remaining_amount, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $co->status === 'in_progress' ? 'bg-amber-100 text-amber-900 border border-amber-200' : ($co->status === 'settled' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                        {{ $co->status_label }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap print:hidden">
                                    <a href="{{ url('/inventory/holding-sale/create?warehouse_id=' . $whId) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-[11px] inline-flex items-center gap-1 transition-colors shadow-2xs" 
                                       title="Bán hàng trực tiếp từ kho hàng ôm này">
                                        <i class="fa-solid fa-cart-shopping text-[10px]"></i>
                                        <span>Bán Hàng</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-boxes-stacked text-2xl text-slate-300 mb-1"></i>
                                    <p class="font-bold text-slate-600">Nhân sự này chưa có đơn hàng ôm nào.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== KHỐI 2: ĐƠN HÀNG BÁN TRỰC TIẾP TỪ KHO HÀNG ÔM ==================== -->
        <div class="bg-white rounded-2xl shadow-2xs border border-emerald-200 overflow-hidden">
            <div class="p-4 border-b border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gradient-to-r from-emerald-50/80 via-teal-50/40 to-white">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-cart-shopping text-emerald-600"></i>
                        <span>Đơn Hàng Bán Trực Tiếp Từ Kho Hàng Ôm (Đã Xuất Bán / Giao Khách)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Các đơn hàng xuất bán trừ trực tiếp tồn kho hàng ôm của nhân sự &bull; Thu tiền mặt trực tiếp hoặc chuyển ghi nợ đại lý
                    </p>
                </div>
                <div class="flex items-center flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 font-black text-xs border border-emerald-200">
                        {{ count($holdingSaleOrders) }} đơn bán kho ôm ({{ number_format($totalHoldingSalesAmount, 0, ',', '.') }} đ)
                    </span>
                    @php
                        $whTargetId = $holdingWarehouse ? $holdingWarehouse->id : 9;
                    @endphp
                    <a href="{{ url('/inventory/holding-sale/create?warehouse_id=' . $whTargetId) }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-plus"></i>
                        <span>+ Bán Hàng Từ Kho Ôm</span>
                    </a>
                </div>
            </div>

            <!-- Thống kê nhanh đơn bán kho ôm -->
            @if(count($holdingSaleOrders) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-3 bg-emerald-50/30 border-b border-emerald-100 text-xs">
                    <div class="bg-white p-2.5 rounded-xl border border-emerald-100 shadow-2xs">
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Tổng Số Đơn Bán</div>
                        <div class="font-mono font-black text-slate-800 text-sm mt-0.5">{{ count($holdingSaleOrders) }} đơn</div>
                    </div>
                    <div class="bg-white p-2.5 rounded-xl border border-emerald-100 shadow-2xs">
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Tổng Doanh Thu Bán</div>
                        <div class="font-mono font-black text-emerald-700 text-sm mt-0.5">{{ number_format($totalHoldingSalesAmount, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="bg-white p-2.5 rounded-xl border border-emerald-100 shadow-2xs">
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Thu Tiền Mặt (NV Giữ)</div>
                        <div class="font-mono font-black text-blue-700 text-sm mt-0.5">{{ number_format($totalHoldingSalesCash, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="bg-white p-2.5 rounded-xl border border-emerald-100 shadow-2xs">
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Chuyển Nợ Đại Lý</div>
                        <div class="font-mono font-black text-amber-700 text-sm mt-0.5">{{ number_format($totalHoldingSalesDebt, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-emerald-50 text-slate-700 font-bold uppercase text-[11px] border-b border-emerald-200">
                            <th class="py-2.5 px-3 w-10 text-center">STT</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Mã Đơn Bán</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Ngày Bán</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Khách Hàng / Đại Lý Nhận</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Mặt Hàng & Số Lượng Bán</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Doanh Thu Đơn</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Hình Thức TT</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Trừ Kho Ôm</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Trạng Thái</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap print:hidden">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($holdingSaleOrders as $idx => $hOrd)
                            @php
                                $isCash = in_array($hOrd->payment_method, ['Tiền mặt', 'Thanh toán tiền mặt']);
                                $isDealerDebt = str_contains($hOrd->payment_method, 'Công nợ') || str_contains($hOrd->payment_method, 'đại lý');
                            @endphp
                            <tr class="hover:bg-emerald-50/30 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold whitespace-nowrap">
                                    <a href="{{ url('/orders/' . $hOrd->id . '/edit') }}" class="text-emerald-700 hover:underline inline-flex items-center gap-1 font-bold" target="_blank">
                                        <i class="fa-solid fa-file-invoice text-emerald-600 text-[11px]"></i>
                                        <span>{{ $hOrd->order_code }}</span>
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                    {{ $hOrd->created_at ? $hOrd->created_at->format('d/m/Y H:i') : '---' }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-slate-900">{{ $hOrd->dealer?->name ?? $hOrd->customer_name ?? 'Khách lẻ' }}</div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-500">
                                        @if($hOrd->customer_phone)
                                            <span class="font-mono">{{ $hOrd->customer_phone }}</span>
                                        @endif
                                        @if($hOrd->dealer_id)
                                            <span class="px-1.5 py-0.2 bg-sky-50 text-sky-700 rounded border border-sky-200 font-bold">Đại lý</span>
                                        @else
                                            <span class="px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded border border-slate-200">Khách lẻ</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-3">
                                    @if($hOrd->items && $hOrd->items->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($hOrd->items as $item)
                                                <div class="flex items-center justify-between gap-3 text-[11px] bg-slate-50 px-2 py-0.5 rounded border border-slate-100">
                                                    <span class="font-medium text-slate-800 truncate max-w-[200px]" title="{{ $item->name }}">
                                                        [{{ $item->sku ?: 'SP' }}] {{ $item->name }}
                                                    </span>
                                                    <span class="font-mono font-bold text-slate-900 whitespace-nowrap">
                                                        {{ (float)$item->quantity }} {{ $item->unit ?: 'cái' }} &times; {{ number_format($item->unit_price, 0, ',', '.') }} đ
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Chi tiết sản phẩm theo đơn</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900 whitespace-nowrap text-sm">
                                    {{ number_format($hOrd->total_amount, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if($isCash)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-200 flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-money-bill-wave text-[10px]"></i>
                                            <span>Tiền mặt (NV giữ)</span>
                                        </span>
                                    @elseif($isDealerDebt)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200 flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-building text-[10px]"></i>
                                            <span>Ghi nợ đại lý</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $hOrd->payment_method ?: '---' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-check text-[9px]"></i>
                                        <span>Đã trừ kho ôm</span>
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $hOrd->status ?: 'Hoàn tất' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap print:hidden">
                                    <a href="{{ url('/orders/' . $hOrd->id . '/edit') }}" target="_blank" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors inline-block" title="Xem chi tiết đơn bán">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-cart-shopping text-2xl text-slate-300 mb-1"></i>
                                    <p class="font-bold text-slate-600">Chưa ghi nhận đơn hàng bán nào xuất từ kho hàng ôm của nhân sự này.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Khi nhân viên xuất bán hàng trong kho ôm, các đơn bán sẽ tự động tổng hợp và trừ tồn kho tại đây.</p>
                                    <a href="{{ url('/inventory/holding-sale/create?warehouse_id=' . ($holdingWarehouse ? $holdingWarehouse->id : 9)) }}" 
                                       class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition-colors">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>+ Tạo Đơn Bán Hàng Từ Kho Ôm Ngay</span>
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== KHỐI 3: DANH SÁCH ĐƠN HÀNG VẬN CHUYỂN / LÊN ĐƠN ==================== -->
        <div class="bg-white rounded-2xl shadow-2xs border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/70">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-packing text-sky-600"></i>
                        <span>Danh Sách Đơn Hàng Vận Chuyển & Chịu Nợ (Bán Hàng / COD Xe)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Các đơn hàng nhân viên được phân công làm tài xế vận chuyển hoặc người lên đơn, kèm số nợ COD/treo nợ
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full bg-sky-100 text-sky-800 font-extrabold text-xs">
                    {{ count($deliveryOrders) }} đơn vận chuyển
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                            <th class="py-2.5 px-3 w-10 text-center">STT</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Mã Đơn Hàng</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Ngày Lập</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Khách Hàng / Đại Lý</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Kho / Xe Giao</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Thành Tiền Đơn</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap bg-amber-50/70 border-x border-amber-200 text-amber-950 font-bold">Nợ Tài Xế / COD</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Trạng Thái Đơn</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Thanh Toán</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap print:hidden">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($deliveryOrders as $idx => $ord)
                            @php
                                $isPaid = in_array($ord->payment_status, ['Đã thanh toán', 'Thanh toán đủ', 'paid', 'Thanh toán tiền mặt']);
                                $isDebt = (float)$ord->driver_debt_amount > 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $isDebt ? 'bg-amber-50/30' : '' }}">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold whitespace-nowrap">
                                    <a href="{{ url('/orders/' . $ord->id . '/edit') }}" class="text-sky-700 hover:underline" target="_blank">
                                        {{ $ord->order_code }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                    {{ $ord->created_at ? $ord->created_at->format('d/m/Y') : '---' }}
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $ord->dealer?->name ?? $ord->customer_name ?? 'Khách lẻ' }}</div>
                                    @if($ord->customer_phone)
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $ord->customer_phone }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-[11px] font-semibold border border-slate-200">
                                        {{ $ord->warehouse ?: 'Kho xe' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ number_format($ord->total_amount, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black whitespace-nowrap bg-amber-50/40 border-x border-amber-100 {{ $isDebt ? 'text-rose-700 text-xs' : 'text-slate-400' }}">
                                    {{ number_format($ord->driver_debt_amount ?? 0, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ in_array($ord->status, ['Đã giao', 'Giao thành công']) ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        {{ $ord->status ?: '---' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if($isPaid)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Đã thanh toán
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                            {{ $ord->payment_status ?: 'Chưa thanh toán' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap print:hidden">
                                    <a href="{{ url('/orders/' . $ord->id . '/edit') }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-sky-100 text-slate-600 hover:text-sky-700 transition-colors inline-block" title="Xem chi tiết đơn hàng">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-box-open text-2xl text-slate-300 mb-1"></i>
                                    <p class="font-bold text-slate-600">Chưa ghi nhận đơn hàng vận chuyển nào liên quan đến nhân sự này.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== KHỐI 3: LỊCH SỬ BIÊN BẢN BÀN GIAO CÔNG NỢ ==================== -->
        <div class="bg-white rounded-2xl shadow-2xs border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/70">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-share-from-square text-sky-600"></i>
                        <span>Lịch Sử Biên Bản Bàn Giao Công Nợ</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Tất cả các lần nhân viên bàn giao nợ cho Sale/Đại lý hoặc nhận nợ bàn giao từ người khác
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[11px]">
                        Bàn giao đi: {{ count($handoversSent) }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 font-bold text-[11px]">
                        Nhận nợ: {{ count($handoversReceived) }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                            <th class="py-2.5 px-3 w-10 text-center">STT</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Mã Biên Bản</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Vai Trò</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Bên Giao (Lái xe)</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Bên Nhận (Sale / Đại lý)</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Đơn Hàng Kèm Theo</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Số Tiền Nợ</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Ngày Lập</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @php
                            $allHandovers = $handoversSent->concat($handoversReceived)->sortByDesc('id')->values();
                        @endphp
                        @forelse($allHandovers as $idx => $h)
                            @php
                                $isSender = ($h->from_user_id == $staff->id);
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold text-sky-800 whitespace-nowrap">{{ $h->code }}</td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    @if($isSender)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                            📤 Bên bàn giao đi
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                            📥 Bên nhận nợ về
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap font-bold text-slate-800">
                                    {{ $h->fromUser?->name ?? $h->from_user_name ?? '---' }}
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="font-bold text-slate-800">{{ $h->toUser?->name ?? $h->to_user_name ?? '---' }}</span>
                                    @if($h->targetDealer)
                                        <div class="text-[10px] text-purple-700 font-semibold">{{ $h->targetDealer->name }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap font-mono text-slate-600">
                                    {{ $h->order_code ?: 'N/A' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-700 whitespace-nowrap">
                                    {{ number_format($h->amount, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-500">
                                    {{ $h->created_at ? $h->created_at->format('d/m/Y H:i') : '---' }}
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    @if($h->status === 'confirmed')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ✅ Đã xác nhận
                                        </span>
                                    @elseif($h->status === 'rejected')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            ❌ Đã từ chối
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                                            ⏳ Chờ xác nhận
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <p class="font-bold text-slate-600">Chưa có biên bản bàn giao công nợ nào liên quan đến nhân sự này.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== KHỐI 4: NHẬT KÝ THU / CHI QUỸ TIỀN MẶT ==================== -->
        <div class="bg-white rounded-2xl shadow-2xs border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/70">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                        <span>Nhật Ký Thu / Chi Quỹ (Nộp Tiền Nợ & Quyết Toán)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Lịch sử các phiếu thu nộp tiền mặt COD, tạm ứng và thanh toán công nợ qua sổ quỹ
                    </p>
                </div>
                <a href="{{ url('/funds') }}?partner={{ urlencode($staff->name) }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Lập Phiếu Thu Ngay</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                            <th class="py-2.5 px-3 w-10 text-center">STT</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Mã Phiếu</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Ngày Giao Dịch</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Loại Giao Dịch</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Số Tiền</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Quỹ / Tài Khoản</th>
                            <th class="py-2.5 px-3">Nội Dung / Diễn Giải</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($transactions as $idx => $tr)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold text-sky-800 whitespace-nowrap">{{ $tr->code }}</td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                    {{ $tr->date ? $tr->date->format('d/m/Y') : ($tr->created_at ? $tr->created_at->format('d/m/Y') : '---') }}
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    @if($tr->type === 'Thu')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            📥 Phiếu Thu
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            📤 Phiếu Chi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold whitespace-nowrap {{ $tr->type === 'Thu' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $tr->type === 'Thu' ? '+' : '-' }}{{ number_format($tr->amount, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-700">
                                    {{ $tr->fund?->name ?? 'Quỹ tiền mặt' }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-600">
                                    {{ $tr->notes ?: '---' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    <p class="font-bold text-slate-600">Chưa ghi nhận giao dịch phiếu thu/chi quỹ nào cho nhân sự này.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: HÀNG ĐANG GIỮ TRONG KHO ÔM (CHI TIẾT MẶT HÀNG & ĐƠN HÀNG ÔM)        -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'holding_stock'" class="space-y-4 print:block">
        <div class="bg-white rounded-2xl shadow-2xs border border-amber-200/90 overflow-hidden">
            <div class="p-4 border-b border-amber-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-amber-50/90 via-orange-50/50 to-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-base shadow-xs shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>Chi Tiết Mặt Hàng Đang Giữ Trong Kho Ôm</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                {{ $holdingWarehouse ? $holdingWarehouse->name : ('Kho Hàng Ôm - ' . $staff->name) }}
                            </span>
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Số lượng thực tế các mặt hàng nhân viên đang nắm giữ từ các đợt nhận hàng ôm để đi thị trường bán lẻ
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ url('/orders?tab=consignment') }}" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>+ Thêm Hàng Vào Kho Ôm</span>
                    </a>
                    <a href="{{ url('/inventory/import?tab=stocks') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 flex items-center gap-1 transition-colors">
                        <i class="fa-solid fa-warehouse text-xs text-slate-500"></i>
                        <span>Xem Tất Cả Kho Ôm</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-amber-50/70 text-slate-700 font-bold uppercase text-[11px] border-b border-amber-200">
                            <th class="py-2.5 px-3 w-10 text-center">STT</th>
                            <th class="py-2.5 px-3 whitespace-nowrap">Mã SKU</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Tên Sản Phẩm</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">ĐVT</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap bg-amber-100/60 font-black text-amber-950">Tồn Trong Kho Ôm</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap">Đơn Giá Bán</th>
                            <th class="py-2.5 px-3 text-right whitespace-nowrap font-black text-slate-900">Thành Tiền (Giá Bán)</th>
                            <th class="py-2.5 px-3 text-center whitespace-nowrap">Tình Trạng</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($holdingStocks as $idx => $stk)
                            @php
                                $prod = $stk->product;
                                $sellPrice = (float)($prod?->price ?: ($prod?->latest_cost_price ?? $prod?->cost_price ?? 0));
                                $lineTotal = (float)$stk->stock_quantity * $sellPrice;
                            @endphp
                            <tr class="hover:bg-amber-50/40 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold text-sky-800 whitespace-nowrap">
                                    {{ $prod?->sku ?: '---' }}
                                </td>
                                <td class="py-2.5 px-3 font-bold text-slate-900">
                                    <div>{{ $prod?->name ?: 'Sản phẩm đã xóa' }}</div>
                                    @if($prod?->tax_name && $prod->tax_name !== $prod->name)
                                        <div class="text-[10px] text-slate-400 font-normal">HĐ: {{ $prod->tax_name }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center text-slate-600 font-semibold whitespace-nowrap">
                                    {{ $prod?->unit ?: 'cái' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-amber-950 bg-amber-50/50 whitespace-nowrap text-sm">
                                    {{ number_format($stk->stock_quantity, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap text-slate-700">
                                    {{ number_format($sellPrice, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-700 whitespace-nowrap">
                                    {{ number_format($lineTotal, 0, ',', '.') }} đ
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Đang sẵn sàng bán
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-boxes-stacked text-2xl text-slate-300 mb-1"></i>
                                    <p class="font-bold text-slate-600">Hiện tại nhân sự này chưa có mặt hàng nào trong kho ôm.</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Bấm nút "<strong>+ Thêm Hàng Vào Kho Ôm</strong>" phía trên để lập đơn xuất hàng vào kho ôm.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Danh sách các Đơn Ôm Hàng Gốc -->
            @if(count($consignmentOrders) > 0)
            <div class="p-3 bg-slate-50 border-t border-slate-200">
                <div class="text-xs font-bold text-slate-700 mb-2 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-receipt text-amber-600"></i>
                        <span>Lịch Sử Các Đơn Nhập Hàng Ôm (Ký Gửi) Từ Kho Công Ty</span>
                    </span>
                    <span class="text-slate-500 font-semibold">{{ count($consignmentOrders) }} đợt nhận hàng ôm</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @foreach($consignmentOrders as $co)
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-sky-900 text-xs">{{ $co->code }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold 
                                    {{ $co->status === 'in_progress' ? 'bg-amber-100 text-amber-900 border border-amber-200' : ($co->status === 'settled' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $co->status_label }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500">
                                Kho xuất: <strong>{{ $co->warehouse?->name ?? 'Kho công ty' }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 font-mono">
                                <span class="text-slate-500">Tổng ôm: {{ number_format($co->total_amount, 0, ',', '.') }}đ</span>
                                <span class="font-bold text-rose-700">Còn nợ: {{ number_format($co->remaining_amount, 0, ',', '.') }}đ</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Lịch sử Đơn Bán Xuất Từ Kho Ôm Này -->
            @if(count($holdingSaleOrders) > 0)
            <div class="p-3 bg-emerald-50/50 border-t border-emerald-200">
                <div class="text-xs font-bold text-slate-800 mb-2 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-cart-shopping text-emerald-600"></i>
                        <span>Lịch Sử Đơn Bán Hàng Xuất Từ Kho Ôm Này</span>
                    </span>
                    <span class="text-emerald-800 font-bold">{{ count($holdingSaleOrders) }} đơn đã bán ({{ number_format($totalHoldingSalesAmount, 0, ',', '.') }} đ)</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @foreach($holdingSaleOrders as $hOrd)
                        <div class="p-3 bg-white rounded-xl border border-emerald-100 shadow-2xs space-y-1">
                            <div class="flex items-center justify-between">
                                <a href="{{ url('/orders/' . $hOrd->id . '/edit') }}" target="_blank" class="font-mono font-bold text-emerald-700 hover:underline text-xs">
                                    {{ $hOrd->order_code }}
                                </a>
                                <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $hOrd->payment_method ?: 'Đã bán' }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-600 font-semibold truncate">
                                Khách: <strong>{{ $hOrd->dealer?->name ?? $hOrd->customer_name ?? 'Khách lẻ' }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 font-mono">
                                <span class="text-slate-400">{{ $hOrd->created_at ? $hOrd->created_at->format('d/m/Y') : '' }}</span>
                                <span class="font-bold text-emerald-700">{{ number_format($hOrd->total_amount, 0, ',', '.') }} đ</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
