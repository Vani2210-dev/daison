@extends('layouts.app')

@section('title', 'Quản Lý Hạn Sử Dụng (Date) & Lô Hàng Tồn Kho | Đại Sơn ERP')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased">

    <!-- 1. Sub-Section Navigation Tabs -->
    @include('inventory.nav_tabs')

    <!-- 2. Header Banner & Action Buttons -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-900/20">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Quản Lý Hạn Sử Dụng (Date) & Lô Hàng</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[11px] font-extrabold border border-amber-300">Kho & Lô Hàng</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Theo dõi hạn sử dụng của từng lần nhập kho, cảnh báo hàng cận date và ưu tiên xuất kho theo nguyên tắc FEFO.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.receipts') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-emerald-700/20 cursor-pointer transition-all hover:scale-102">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>+ Lập Phiếu Nhập Kho (Có Date)</span>
            </a>
            <button type="button" onclick="window.location.reload()" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-[#1b4a8c]"></i>
                <span class="hidden sm:inline">Làm mới</span>
            </button>
        </div>
    </div>

    <!-- 3. Top Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Card 1: Tổng số lô -->
        <div class="bg-gradient-to-br from-slate-50 to-slate-100/60 rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-slate-700 font-bold text-xs">
                <span>Tổng Lô Đã Nhập Kho</span>
                <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-800 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                {{ number_format($stats['total_batches']) }} <span class="text-xs font-bold text-slate-500">lô</span>
            </div>
            <div class="text-[11px] text-slate-500 font-medium">Lịch sử toàn bộ các lô nhập</div>
        </div>

        <!-- Card 2: Lô còn hàng -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-2xl p-4 border border-blue-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-blue-800 font-bold text-xs">
                <span>Lô Hàng Đang Còn Tồn</span>
                <div class="w-8 h-8 rounded-xl bg-blue-200/80 text-blue-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-blue-950 font-mono tracking-tight">
                {{ number_format($stats['active_batches']) }} <span class="text-xs font-bold text-blue-700">lô</span>
            </div>
            <div class="text-[11px] text-blue-700 font-medium">Số lượng tồn kho > 0</div>
        </div>

        <!-- Card 3: Lô Cận Date (<= 60 ngày) -->
        <a href="{{ route('inventory.batches', ['date_filter' => 'near_expired']) }}" class="bg-gradient-to-br from-amber-50 to-orange-50/60 rounded-2xl p-4 border border-amber-300 shadow-2xs space-y-1.5 transition-all hover:scale-101 block">
            <div class="flex items-center justify-between text-amber-900 font-bold text-xs">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                    <span>Hàng Cận Date (≤ 60 ngày)</span>
                </span>
                <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-950 font-mono tracking-tight">
                {{ number_format($stats['near_expired']) }} <span class="text-xs font-bold text-amber-800">lô</span>
            </div>
            <div class="text-[11px] text-amber-700 font-medium flex items-center gap-1">
                <span>Ưu tiên xuất bán hoặc xả kho</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </div>
        </a>

        <!-- Card 4: Lô Đã Hết Hạn -->
        <a href="{{ route('inventory.batches', ['date_filter' => 'expired']) }}" class="bg-gradient-to-br from-rose-50 to-red-50/60 rounded-2xl p-4 border border-rose-300 shadow-2xs space-y-1.5 transition-all hover:scale-101 block">
            <div class="flex items-center justify-between text-rose-900 font-bold text-xs">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-ban text-rose-600"></i>
                    <span>Hàng Đã Hết Hạn (Quá Date)</span>
                </span>
                <div class="w-8 h-8 rounded-xl bg-rose-200 text-rose-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-rose-950 font-mono tracking-tight">
                {{ number_format($stats['expired']) }} <span class="text-xs font-bold text-rose-800">lô</span>
            </div>
            <div class="text-[11px] text-rose-700 font-medium flex items-center gap-1">
                <span>Cần hủy bỏ hoặc đổi trả NSX</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </div>
        </a>
    </div>

    <!-- 4. Filter Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('inventory.batches') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <!-- Tìm kiếm -->
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Tìm theo mã lô, tên sản phẩm, SKU..." 
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c] bg-slate-50 focus:bg-white"
                >
            </div>

            <!-- Kho hàng -->
            <div>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c] bg-slate-50 focus:bg-white font-semibold">
                    <option value="">-- Tất cả kho lưu trữ --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Bộ lọc Date -->
            <div>
                <select name="date_filter" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c] bg-slate-50 focus:bg-white font-bold text-slate-700">
                    <option value="">-- Tất cả trạng thái Date --</option>
                    <option value="active" {{ request('date_filter') == 'active' ? 'selected' : '' }}>Chỉ lô còn hàng trong kho (Tồn > 0)</option>
                    <option value="near_expired" {{ request('date_filter') == 'near_expired' ? 'selected' : '' }}>⚠️ Hàng cận Date (≤ 60 ngày)</option>
                    <option value="expired" {{ request('date_filter') == 'expired' ? 'selected' : '' }}>⛔ Hàng đã hết hạn (Quá Date)</option>
                </select>
            </div>

            <!-- Nút hành động -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs flex items-center justify-center gap-2 cursor-pointer transition-colors">
                    <i class="fa-solid fa-filter"></i> Lọc dữ liệu
                </button>
                @if(request('search') || request('warehouse_id') || request('date_filter'))
                <a href="{{ route('inventory.batches') }}" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center cursor-pointer" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 5. Main Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3 text-center w-12">STT</th>
                        <th class="py-3 px-3 whitespace-nowrap min-w-[200px]">Sản Phẩm</th>
                        <th class="py-3 px-3 whitespace-nowrap">Kho Lưu Trữ</th>
                        <th class="py-3 px-3 whitespace-nowrap">Số Lô (Batch)</th>
                        <th class="py-3 px-3 whitespace-nowrap">Phiếu Nhập Gốc</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap">NSX</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap bg-amber-50/80 border-x border-amber-200 text-amber-950 font-bold">HSD (Hạn Dùng)</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap">SL Nhập</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap font-bold text-slate-900">Tồn Còn Lại</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap">Tình Trạng Date</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($batches as $idx => $b)
                    @php
                        $daysLeft = $b->days_until_expiry;
                        $statusCode = $b->expiry_status_code;
                        $isOutOfStock = (float)$b->remaining_quantity <= 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $statusCode === 'expired' && !$isOutOfStock ? 'bg-rose-50/40' : ($statusCode === 'near_expired' && !$isOutOfStock ? 'bg-amber-50/40' : '') }}">
                        <td class="py-3 px-3 text-center text-slate-400 font-mono">
                            {{ $batches->firstItem() + $idx }}
                        </td>

                        <!-- Tên sản phẩm & SKU -->
                        <td class="py-3 px-3">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900">{{ $b->product?->name ?? 'Sản phẩm đã xóa' }}</span>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                    SKU: <span class="font-bold text-sky-800">{{ $b->product?->sku ?? '-' }}</span> | ĐVT: <span class="font-semibold">{{ $b->product?->unit ?? 'cái' }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Kho lưu trữ -->
                        <td class="py-3 px-3 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold text-[11px]">
                                {{ $b->warehouse?->name ?? 'Kho chính' }}
                            </span>
                        </td>

                        <!-- Số Lô -->
                        <td class="py-3 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                            {{ $b->batch_code ?: '-' }}
                        </td>

                        <!-- Phiếu Nhập Gốc -->
                        <td class="py-3 px-3 whitespace-nowrap">
                            @if($b->transactionItem?->transaction)
                                <a href="{{ route('inventory.transactions.show', $b->transactionItem->transaction->id) }}" class="font-mono font-bold text-emerald-700 hover:underline">
                                    {{ $b->transactionItem->transaction->code }}
                                </a>
                                <div class="text-[10px] text-slate-400">
                                    {{ $b->transactionItem->transaction->created_at->format('d/m/Y') }}
                                </div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- NSX -->
                        <td class="py-3 px-3 text-center font-mono text-slate-600 whitespace-nowrap">
                            {{ $b->manufacturing_date ? $b->manufacturing_date->format('d/m/Y') : '-' }}
                        </td>

                        <!-- HSD -->
                        <td class="py-3 px-3 text-center font-mono whitespace-nowrap bg-amber-50/40 border-x border-amber-100">
                            @if($b->expiry_date)
                                <div>
                                    <span class="font-bold text-xs {{ $daysLeft < 0 ? 'text-rose-700 font-black' : ($daysLeft <= 60 ? 'text-amber-800 font-black' : 'text-slate-800') }}">
                                        {{ $b->expiry_date->format('d/m/Y') }}
                                    </span>
                                    @if($daysLeft !== null)
                                        <div class="text-[10px] mt-0.5 {{ $daysLeft < 0 ? 'text-rose-600 font-bold' : ($daysLeft <= 60 ? 'text-amber-700 font-bold' : 'text-slate-500') }}">
                                            @if($daysLeft < 0)
                                                Quá hạn {{ abs($daysLeft) }} ngày
                                            @else
                                                Còn {{ $daysLeft }} ngày
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400 italic">Không có date</span>
                            @endif
                        </td>

                        <!-- SL Nhập -->
                        <td class="py-3 px-3 text-right font-mono whitespace-nowrap">
                            {{ number_format($b->import_quantity, 2) }}
                        </td>

                        <!-- Tồn Còn Lại -->
                        <td class="py-3 px-3 text-right font-mono font-black whitespace-nowrap">
                            @if($isOutOfStock)
                                <span class="text-slate-400 line-through">0.00</span>
                            @else
                                <span class="text-blue-800 font-black text-sm">{{ number_format($b->remaining_quantity, 2) }}</span>
                            @endif
                        </td>

                        <!-- Tình Trạng Date -->
                        <td class="py-3 px-3 text-center whitespace-nowrap">
                            @if($isOutOfStock)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                    Đã hết hàng
                                </span>
                            @elseif($statusCode === 'expired')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Đã hết hạn
                                </span>
                            @elseif($statusCode === 'near_expired')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                                    <i class="fa-solid fa-clock"></i> Cận Date (≤60n)
                                </span>
                            @elseif($statusCode === 'good')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check"></i> Còn hạn tốt
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Bình thường
                                </span>
                            @endif
                        </td>

                        <!-- Thao Tác -->
                        <td class="py-3 px-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($b->product_id)
                                    <a href="{{ url('/cat_products/' . $b->product_id . '/edit') }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-sky-100 text-slate-600 hover:text-sky-700 transition-colors" title="Xem sản phẩm">
                                        <i class="fa-solid fa-box"></i>
                                    </a>
                                @endif
                                @if($b->transactionItem?->transaction_id)
                                    <a href="{{ route('inventory.transactions.show', $b->transactionItem->transaction_id) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-100 text-slate-600 hover:text-emerald-700 transition-colors" title="Xem phiếu nhập gốc">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fa-solid fa-calendar-xmark"></i>
                            </div>
                            <div class="font-bold text-slate-700 text-sm">Chưa có lô hàng hoặc hạn sử dụng nào phù hợp!</div>
                            <p class="text-xs text-slate-400 mt-1">Hãy lập Phiếu Nhập Kho mới và nhập Số Lô / Hạn Sử Dụng (Date) để hệ thống tự động ghi nhận.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
