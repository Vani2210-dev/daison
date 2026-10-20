@extends('layouts.app')

@section('title', 'Chi Tiết Hạn Sử Dụng & Lô Hàng: ' . $product->name . ' - Đại Sơn ERP')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased">

    <!-- 1. Breadcrumbs & Top Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 sm:p-4 rounded-2xl border border-slate-200 shadow-2xs">
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ url('/cat_products') }}" class="hover:text-sky-700 flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-boxes-stacked text-sky-600"></i>
                <span>Danh mục sản phẩm</span>
            </a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-slate-900 font-bold">Hạn sử dụng & Lô hàng</span>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-amber-700 font-bold truncate max-w-[200px] sm:max-w-xs">{{ $product->name }}</span>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ url('/cat_products') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-1.5 shadow-2xs cursor-pointer transition-all">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Quay lại sản phẩm</span>
            </a>
            <a href="{{ url('/cat_products/' . $product->id . '/edit') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 font-bold text-xs flex items-center gap-1.5 border border-slate-200 transition-all cursor-pointer">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Chỉnh sửa SP</span>
            </a>
            <a href="{{ route('inventory.receipts') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-emerald-700/20 cursor-pointer transition-all hover:scale-102">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>+ Nhập Thêm Hàng (PNK)</span>
            </a>
        </div>
    </div>

    <!-- 2. Product Profile Header Banner -->
    <div class="bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 rounded-3xl p-5 sm:p-6 text-white shadow-lg border border-amber-500/30 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-2xl shrink-0 shadow-inner">
                <i class="fa-solid fa-clock-rotate-left text-amber-100"></i>
            </div>
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight">{{ $product->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-white font-mono font-bold text-xs border border-white/20">
                        SKU: {{ $product->sku }}
                    </span>
                    @if($product->tax_name && $product->tax_name !== $product->name)
                        <span class="px-2.5 py-0.5 rounded-full bg-black/20 text-amber-100 text-[11px]">
                            HĐ: {{ $product->tax_name }}
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-amber-100">
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-layer-group text-amber-200"></i>
                        <span>Phân loại: <strong>{{ $product->category?->name ?? 'Chưa phân loại' }}</strong></span>
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-award text-amber-200"></i>
                        <span>Thương hiệu: <strong>{{ $product->brand?->name ?? 'Đại Sơn' }}</strong></span>
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-scale-balanced text-amber-200"></i>
                        <span>ĐVT: <strong>{{ $product->unit }}</strong></span>
                    </span>
                    @if($product->specification)
                        <span>&bull;</span>
                        <span class="italic text-amber-200">Quy cách: {{ $product->specification }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-black/20 backdrop-blur-xs rounded-2xl p-4 border border-white/10 flex items-center gap-6 shrink-0">
            <div>
                <div class="text-[11px] font-bold text-amber-200 uppercase tracking-wider">Tổng Tồn Kho</div>
                <div class="text-2xl sm:text-3xl font-black font-mono mt-0.5 text-white">
                    {{ number_format($product->stock_quantity, 2) }}
                    <span class="text-xs font-semibold text-amber-200">{{ $product->unit }}</span>
                </div>
            </div>
            <div class="w-px h-10 bg-white/20"></div>
            <div>
                <div class="text-[11px] font-bold text-amber-200 uppercase tracking-wider">Giá Vốn Hiện Tại</div>
                <div class="text-lg sm:text-xl font-black font-mono mt-0.5 text-amber-100">
                    {{ number_format($product->latest_cost_price ?: $product->cost_price, 0, ',', '.') }} đ
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Top Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Card 1: Tổng số lô -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-slate-600 font-bold text-xs uppercase">
                <span>Tổng Số Lô Đã Nhập</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                {{ $stats['total_batches'] }} <span class="text-xs font-bold text-slate-500">lô</span>
            </div>
            <div class="text-[11px] text-slate-400 font-medium">Toàn bộ lịch sử các đợt nhập hàng</div>
        </div>

        <!-- Card 2: Lô còn hàng -->
        <div class="bg-white rounded-2xl p-4 border border-blue-200 shadow-2xs space-y-1.5 bg-gradient-to-br from-white to-blue-50/40">
            <div class="flex items-center justify-between text-blue-800 font-bold text-xs uppercase">
                <span>Số Lô Còn Hàng Tồn</span>
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-blue-950 font-mono tracking-tight">
                {{ $stats['active_batches'] }} <span class="text-xs font-bold text-blue-700">/ {{ $stats['total_batches'] }} lô</span>
            </div>
            <div class="text-[11px] text-blue-700 font-medium">
                Tồn thực tế trong các lô: <strong>{{ number_format($stats['total_remaining'], 2) }}</strong> {{ $product->unit }}
            </div>
        </div>

        <!-- Card 3: Lô Cận Date -->
        <div class="bg-white rounded-2xl p-4 border border-amber-300 shadow-2xs space-y-1.5 bg-gradient-to-br from-white to-amber-50/50">
            <div class="flex items-center justify-between text-amber-900 font-bold text-xs uppercase">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                    <span>Lô Cận Date (≤ 60 ngày)</span>
                </span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-950 font-mono tracking-tight">
                {{ $stats['near_expired'] }} <span class="text-xs font-bold text-amber-800">lô</span>
            </div>
            <div class="text-[11px] text-amber-700 font-medium">
                {{ $stats['near_expired'] > 0 ? 'Cần ưu tiên xuất bán hoặc xả hàng trước' : 'Không có lô nào sắp hết hạn' }}
            </div>
        </div>

        <!-- Card 4: Lô Quá Hạn -->
        <div class="bg-white rounded-2xl p-4 border border-rose-300 shadow-2xs space-y-1.5 bg-gradient-to-br from-white to-rose-50/50">
            <div class="flex items-center justify-between text-rose-900 font-bold text-xs uppercase">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-ban text-rose-600"></i>
                    <span>Lô Quá Hạn (Hết Date)</span>
                </span>
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-rose-950 font-mono tracking-tight">
                {{ $stats['expired'] }} <span class="text-xs font-bold text-rose-800">lô</span>
            </div>
            <div class="text-[11px] text-rose-700 font-medium">
                {{ $stats['expired'] > 0 ? 'Cần thanh lý, hủy bỏ hoặc đổi trả nhà cung cấp' : 'Không có hàng quá hạn' }}
            </div>
        </div>
    </div>

    <!-- 4. Main Batches Table Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <!-- Table Header Toolbar -->
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-black text-sm sm:text-base text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                    <span>Danh Sách Chi Tiết Các Lần Nhập Hàng & Số Lô Tồn Kho</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Hệ thống tự động sắp xếp theo thứ tự ưu tiên xuất kho <strong>FEFO (Hạn sử dụng ngắn hơn xuất trước)</strong>.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.receipts') }}" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200 transition-colors flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-dolly"></i>
                    <span>Lập Phiếu Nhập Thêm</span>
                </a>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3 text-center w-12">STT</th>
                        <th class="py-3 px-3 whitespace-nowrap">Kho Lưu Trữ</th>
                        <th class="py-3 px-3 whitespace-nowrap">Số Lô (Batch)</th>
                        <th class="py-3 px-3 whitespace-nowrap">Phiếu Nhập Gốc</th>
                        <th class="py-3 px-3 whitespace-nowrap">Nhà Cung Cấp</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap">NSX</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap bg-amber-50/80 border-x border-amber-200 text-amber-950 font-bold">HSD (Hạn Dùng)</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap">Đơn Giá Vốn</th>
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
                        <td class="py-3.5 px-3 text-center text-slate-400 font-mono">
                            {{ $idx + 1 }}
                        </td>

                        <!-- Kho lưu trữ -->
                        <td class="py-3.5 px-3 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 font-bold text-xs border border-blue-200/60">
                                <i class="fa-solid fa-warehouse text-blue-600 mr-1"></i>
                                {{ $b->warehouse?->name ?? 'Kho chính' }}
                            </span>
                        </td>

                        <!-- Số Lô -->
                        <td class="py-3.5 px-3 font-mono font-bold text-slate-900 whitespace-nowrap text-xs">
                            <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                {{ $b->batch_code ?: '-' }}
                            </span>
                        </td>

                        <!-- Phiếu Nhập Gốc -->
                        <td class="py-3.5 px-3 whitespace-nowrap">
                            @if($b->transactionItem?->transaction)
                                <a href="{{ route('inventory.transactions.show', $b->transactionItem->transaction->id) }}" class="font-mono font-bold text-emerald-700 hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-file-invoice text-emerald-600"></i>
                                    <span>{{ $b->transactionItem->transaction->code }}</span>
                                </a>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Ngày: {{ $b->transactionItem->transaction->created_at->format('d/m/Y') }}
                                </div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- Nhà Cung Cấp -->
                        <td class="py-3.5 px-3 whitespace-nowrap text-slate-800 font-medium">
                            {{ $b->transactionItem?->transaction?->supplier_name ?? '-' }}
                        </td>

                        <!-- NSX -->
                        <td class="py-3.5 px-3 text-center font-mono text-slate-600 whitespace-nowrap">
                            {{ $b->manufacturing_date ? $b->manufacturing_date->format('d/m/Y') : '-' }}
                        </td>

                        <!-- HSD -->
                        <td class="py-3.5 px-3 text-center font-mono whitespace-nowrap bg-amber-50/40 border-x border-amber-100">
                            @if($b->expiry_date)
                                <div>
                                    <span class="font-bold text-xs {{ $daysLeft < 0 ? 'text-rose-700 font-black' : ($daysLeft <= 60 ? 'text-amber-800 font-black' : 'text-slate-900') }}">
                                        {{ $b->expiry_date->format('d/m/Y') }}
                                    </span>
                                    @if($daysLeft !== null)
                                        <div class="text-[10px] mt-0.5 font-bold {{ $daysLeft < 0 ? 'text-rose-600' : ($daysLeft <= 60 ? 'text-amber-700' : 'text-slate-500 font-normal') }}">
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

                        <!-- Đơn Giá Vốn -->
                        <td class="py-3.5 px-3 text-right font-mono whitespace-nowrap text-slate-700">
                            {{ number_format($b->cost_price, 0, ',', '.') }} đ
                        </td>

                        <!-- SL Nhập -->
                        <td class="py-3.5 px-3 text-right font-mono whitespace-nowrap">
                            {{ number_format($b->import_quantity, 2) }}
                        </td>

                        <!-- Tồn Còn Lại -->
                        <td class="py-3.5 px-3 text-right font-mono whitespace-nowrap">
                            @if($isOutOfStock)
                                <span class="text-slate-400 line-through">0.00</span>
                            @else
                                <span class="text-blue-800 font-black text-sm">{{ number_format($b->remaining_quantity, 2) }}</span>
                                <span class="text-[10px] text-slate-500 font-normal">{{ $product->unit }}</span>
                            @endif
                        </td>

                        <!-- Tình Trạng Date -->
                        <td class="py-3.5 px-3 text-center whitespace-nowrap">
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
                        <td class="py-3.5 px-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($b->transactionItem?->transaction_id)
                                    <a href="{{ route('inventory.transactions.show', $b->transactionItem->transaction_id) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-100 text-slate-600 hover:text-emerald-700 transition-colors" title="Xem chi tiết phiếu nhập gốc">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.transactions.print', $b->transactionItem->transaction_id) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-sky-100 text-slate-600 hover:text-sky-700 transition-colors" title="In phiếu nhập gốc">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-16 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fa-solid fa-calendar-xmark"></i>
                            </div>
                            <div class="font-bold text-slate-700 text-sm">Chưa có thông tin lô hàng hoặc hạn sử dụng nào cho sản phẩm này!</div>
                            <p class="text-xs text-slate-400 mt-1">Khi lập Phiếu Nhập Kho, hãy điền Số Lô & Hạn Sử Dụng (Date) để tự động ghi nhận theo từng đợt hàng.</p>
                            <div class="mt-4">
                                <a href="{{ route('inventory.receipts') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md inline-flex items-center gap-2 cursor-pointer transition-all">
                                    <i class="fa-solid fa-dolly"></i>
                                    <span>Lập Phiếu Nhập Kho Ngay</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
