@extends('layouts.app')

@section('title', 'Quản Lý Phiếu Xuất Kho (Goods Issue) | Máy lọc nước Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="warehouseIssueApp()">

    <!-- 1. Sub-Section Navigation Tabs (Kho hàng Đại Sơn) -->
    @include('inventory.nav_tabs')

    <!-- 2. Header Banner & Action Buttons -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-900/20">
                <i class="fa-solid fa-arrow-up-right-from-square text-lg"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Quản Lý Phiếu Xuất Kho (PXK)</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold border border-amber-200">Kho Hàng & Sản Xuất</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Xuất linh kiện lắp ráp máy lọc nước, xuất bảo hành, xuất hao hụt, xuất sử dụng nội bộ, tự động trừ tồn kho theo từng kho.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-amber-700/20 cursor-pointer transition-all hover:scale-102">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>+ Lập Phiếu Xuất Kho</span>
            </button>
            <button type="button" onclick="window.location.reload()" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-[#1b4a8c]"></i>
                <span class="hidden sm:inline">Làm mới</span>
            </button>
        </div>
    </div>

    <!-- 3. Top Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Card 1: Tổng số phiếu xuất -->
        <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 rounded-2xl p-4 border border-amber-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-amber-800 font-bold text-xs">
                <span>Tổng Phiếu Xuất Kho</span>
                <div class="w-8 h-8 rounded-xl bg-amber-200/80 text-amber-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-950 font-mono tracking-tight">
                {{ number_format($stats['total_count']) }} <span class="text-xs font-bold text-amber-700">phiếu</span>
            </div>
            <div class="text-[11px] text-amber-700 font-medium">Toàn bộ lịch sử xuất kho nội bộ</div>
        </div>

        <!-- Card 2: Giá trị xuất kho -->
        <div class="bg-gradient-to-br from-rose-50 to-red-50/50 rounded-2xl p-4 border border-rose-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-rose-800 font-bold text-xs">
                <span>Tổng Giá Trị Xuất Kho</span>
                <div class="w-8 h-8 rounded-xl bg-rose-200/80 text-rose-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-rose-950 font-mono tracking-tight">
                {{ number_format($stats['total_amount'], 0, ',', '.') }} <span class="text-xs font-bold text-rose-700">đ</span>
            </div>
            <div class="text-[11px] text-rose-700 font-medium">Theo giá vốn tại thời điểm xuất</div>
        </div>

        <!-- Card 3: Xuất kho hôm nay -->
        <div class="bg-gradient-to-br from-sky-50 to-blue-50/50 rounded-2xl p-4 border border-sky-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-sky-800 font-bold text-xs">
                <span>Phiếu Xuất Hôm Nay</span>
                <div class="w-8 h-8 rounded-xl bg-sky-200/80 text-sky-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-sky-950 font-mono tracking-tight">
                {{ number_format($stats['today_count']) }} <span class="text-xs font-bold text-sky-700">phiếu</span>
            </div>
            <div class="text-[11px] text-sky-700 font-medium">Số phiếu xuất phát sinh trong ngày</div>
        </div>

        <!-- Card 4: Giá trị xuất hôm nay -->
        <div class="bg-gradient-to-br from-indigo-50 to-violet-50/50 rounded-2xl p-4 border border-indigo-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-indigo-800 font-bold text-xs">
                <span>Giá Trị Xuất Hôm Nay</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-200/80 text-indigo-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-indigo-950 font-mono tracking-tight">
                {{ number_format($stats['today_amount'], 0, ',', '.') }} <span class="text-xs font-bold text-indigo-700">đ</span>
            </div>
            <div class="text-[11px] text-indigo-700 font-medium">Giá trị hàng xuất trong ngày</div>
        </div>
    </div>

    <!-- 4. Filter Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('inventory.issues') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <!-- Kho xuất -->
            <div>
                <label class="block font-bold text-slate-600 mb-1">Kho Xuất</label>
                <select name="warehouse_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c] bg-white">
                    <option value="">-- Tất cả kho --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Trạng thái -->
            <div>
                <label class="block font-bold text-slate-600 mb-1">Trạng Thái</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c] bg-white">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Hoàn thành" {{ request('status') === 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="Bản nháp" {{ request('status') === 'Bản nháp' ? 'selected' : '' }}>Bản nháp</option>
                    <option value="Đã hủy" {{ request('status') === 'Đã hủy' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>

            <!-- Từ ngày -->
            <div>
                <label class="block font-bold text-slate-600 mb-1">Từ Ngày</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c]">
            </div>

            <!-- Đến ngày -->
            <div>
                <label class="block font-bold text-slate-600 mb-1">Đến Ngày</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c]">
            </div>

            <!-- Ô tìm kiếm -->
            <div class="lg:col-span-2">
                <label class="block font-bold text-slate-600 mb-1">Tìm Kiếm</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã phiếu, người nhận, lý do, SKU..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c]">
                    <button type="submit" class="px-4 py-2 bg-[#1b4a8c] hover:bg-blue-800 text-white font-bold rounded-xl cursor-pointer shrink-0">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request()->anyFilled(['warehouse_id', 'status', 'date_from', 'date_to', 'search']))
                        <a href="{{ route('inventory.issues') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl cursor-pointer shrink-0" title="Xóa bộ lọc">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- 5. Bảng Danh Sách Phiếu Xuất Kho -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="p-3.5 w-12 text-center">STT</th>
                        <th class="p-3.5">Mã Phiếu</th>
                        <th class="p-3.5">Thời Gian</th>
                        <th class="p-3.5">Kho Xuất</th>
                        <th class="p-3.5">Người Nhận / Bộ Phận</th>
                        <th class="p-3.5">Lý Do Xuất</th>
                        <th class="p-3.5 text-center">Số Mặt Hàng</th>
                        <th class="p-3.5 text-right">Tổng Tiền (Vốn)</th>
                        <th class="p-3.5">Người Lập</th>
                        <th class="p-3.5 text-center">Trạng Thái</th>
                        <th class="p-3.5 text-center w-36">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($issues as $idx => $issue)
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $issue->status === 'Đã hủy' ? 'opacity-60 bg-slate-50' : '' }}">
                        <td class="p-3.5 text-center text-slate-400 font-medium">
                            {{ $issues->firstItem() + $idx }}
                        </td>
                        <td class="p-3.5">
                            <div class="font-mono font-black text-amber-800 hover:underline cursor-pointer" @click="viewDetail({{ $issue->id }})">
                                {{ $issue->code }}
                            </div>
                            @if($issue->reference_order_code)
                                <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-receipt text-slate-400"></i>
                                    <span>CT: {{ $issue->reference_order_code }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="p-3.5 whitespace-nowrap">
                            <div class="font-bold text-slate-800">{{ $issue->created_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $issue->created_at->format('H:i') }}</div>
                        </td>
                        <td class="p-3.5 font-bold text-slate-800">
                            {{ $issue->warehouse?->name ?? 'Kho Tổng' }}
                        </td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900">{{ $issue->supplier_name ?: 'Nội bộ công ty' }}</div>
                        </td>
                        <td class="p-3.5">
                            <div class="text-slate-700 font-medium line-clamp-1 max-w-[200px]" title="{{ $issue->notes }}">
                                {{ $issue->notes ?: 'Xuất kho sử dụng / sản xuất' }}
                            </div>
                        </td>
                        <td class="p-3.5 text-center font-mono font-bold text-slate-700">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200">
                                {{ $issue->items->count() }} SP ({{ number_format($issue->items->sum('quantity')) }})
                            </span>
                        </td>
                        <td class="p-3.5 text-right font-mono font-black text-amber-800 text-sm whitespace-nowrap">
                            {{ number_format((float)$issue->total_amount, 0, ',', '.') }} đ
                        </td>
                        <td class="p-3.5 text-slate-700">
                            <div class="font-semibold">{{ $issue->createdBy?->name ?? 'Admin' }}</div>
                        </td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            @if($issue->status === 'Hoàn thành')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Hoàn thành
                                </span>
                            @elseif($issue->status === 'Bản nháp')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-clock mr-1"></i> Bản nháp
                                </span>
                            @elseif($issue->status === 'Đã hủy')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                    <i class="fa-solid fa-ban mr-1"></i> Đã hủy
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-700">
                                    {{ $issue->status }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" @click="viewDetail({{ $issue->id }})" class="w-8 h-8 rounded-lg bg-sky-50 hover:bg-sky-100 text-[#1b4a8c] flex items-center justify-center cursor-pointer transition-colors" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                                <a href="{{ route('inventory.transactions.print', $issue->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 flex items-center justify-center cursor-pointer transition-colors" title="In Phiếu Xuất Kho">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </a>
                                @if($issue->status !== 'Đã hủy')
                                <button type="button" @click="cancelIssue({{ $issue->id }}, '{{ $issue->code }}')" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center cursor-pointer transition-colors" title="Hủy phiếu & Hoàn tồn">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-12 text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div class="font-bold text-slate-600 text-sm">Chưa có phiếu xuất kho nào</div>
                            <p class="text-xs text-slate-400 mt-1">Bấm "+ Lập Phiếu Xuất Kho" để bắt đầu ghi nhận hàng xuất dùng hoặc sản xuất</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($issues->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $issues->links() }}
        </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: LẬP PHIẾU XUẤT KHO                -->
    <!-- ========================================== -->
    <div x-show="showCreateModal" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showCreateModal = false"></div>

        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-5xl overflow-hidden flex flex-col max-h-[92vh]" @click.away="showCreateModal = false">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-amber-700 to-orange-800 text-white p-4 sm:p-5 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-base sm:text-lg">Lập Phiếu Xuất Kho Mới</h3>
                            <p class="text-xs text-amber-100">Xuất linh kiện lắp ráp máy lọc nước, bảo hành, hao hụt, tự động trừ tồn kho</p>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-4 text-xs">
                    <!-- Hàng 1: Kho xuất & Lý do xuất & Người nhận -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kho Xuất Hàng <span class="text-rose-500">*</span></label>
                            <select x-model="form.warehouse_id" @change="onWarehouseChange()" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-600 bg-white font-bold">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Lý Do Xuất Kho <span class="text-rose-500">*</span></label>
                            <select x-model="form.reason_type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-600 bg-white font-bold text-amber-900">
                                <option value="Xuất Lắp Ráp & Sản Xuất Máy Lọc Nước">Xuất Lắp Ráp & Sản Xuất Máy Lọc Nước</option>
                                <option value="Xuất Bảo Hành & Thay Thế Linh Kiện">Xuất Bảo Hành & Thay Thế Linh Kiện</option>
                                <option value="Xuất Sử Dụng Nội Bộ / Văn Phòng">Xuất Sử Dụng Nội Bộ / Văn Phòng</option>
                                <option value="Xuất Hao Hụt / Hư Hỏng / Hủy Hàng">Xuất Hao Hụt / Hư Hỏng / Hủy Hàng</option>
                                <option value="Xuất Trả Lại Nhà Cung Cấp">Xuất Trả Lại Nhà Cung Cấp</option>
                                <option value="Xuất Khác Ngoài Đơn Bán Hàng">Xuất Khác Ngoài Đơn Bán Hàng</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Người Nhận / Bộ Phận Nhận</label>
                            <input type="text" x-model="form.supplier_name" placeholder="Tên kỹ thuật viên, tổ sản xuất..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-600 bg-white">
                        </div>
                    </div>

                    <!-- Hàng 2: Tìm kiếm & Thêm sản phẩm vào phiếu -->
                    <div class="relative bg-amber-50/60 p-4 rounded-2xl border border-amber-200 space-y-2">
                        <label class="block font-black text-amber-900 text-xs">
                            <i class="fa-solid fa-barcode text-amber-700 mr-1"></i> Tìm Kiếm & Chọn Sản Phẩm Cần Xuất
                        </label>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.250ms="searchProducts()" placeholder="Gõ tên sản phẩm, mã SKU hoặc quét mã QR..." class="w-full px-4 py-2.5 pl-10 rounded-xl border border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-600 bg-white shadow-xs">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-amber-700"></i>
                            <div x-show="isSearching" class="absolute right-3.5 top-3 text-amber-700 font-bold">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                            </div>
                        </div>

                        <!-- Dropdown Gợi Ý Sản Phẩm -->
                        <div x-show="searchResults.length > 0" class="absolute left-4 right-4 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto divide-y divide-slate-100">
                            <template x-for="p in searchResults" :key="p.id">
                                <div @click="addItem(p)" class="p-2.5 hover:bg-amber-50 cursor-pointer flex items-center justify-between transition-colors">
                                    <div>
                                        <div class="font-bold text-slate-800" x-text="p.name"></div>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            SKU: <span class="font-bold text-amber-700" x-text="p.sku"></span> | ĐVT: <span x-text="p.unit"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[11px] font-bold text-slate-700">
                                            Tồn kho: <span :class="p.warehouse_stock > 0 ? 'text-emerald-700 font-black' : 'text-rose-600 font-black'" x-text="p.warehouse_stock"></span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono">
                                            Giá vốn: <span x-text="formatNumber(p.cost_price) + ' đ'"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Hàng 3: Bảng danh sách hàng hóa trong phiếu xuất -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                        <table class="w-full text-left">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                                <tr>
                                    <th class="p-2.5 w-10 text-center">#</th>
                                    <th class="p-2.5">Sản Phẩm</th>
                                    <th class="p-2.5 w-20 text-center">ĐVT</th>
                                    <th class="p-2.5 w-24 text-center">Tồn Hiện Tại</th>
                                    <th class="p-2.5 w-28 text-center">Số Lượng Xuất</th>
                                    <th class="p-2.5 w-32 text-right">Đơn Giá Vốn (đ)</th>
                                    <th class="p-2.5 w-36 text-right">Thành Tiền (đ)</th>
                                    <th class="p-2.5 w-12 text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(item, idx) in form.items" :key="idx">
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-2.5 text-center text-slate-400 font-mono" x-text="idx + 1"></td>
                                        <td class="p-2.5">
                                            <div class="font-bold text-slate-800" x-text="item.name"></div>
                                            <div class="text-[10px] text-slate-500 font-mono" x-text="item.sku"></div>
                                        </td>
                                        <td class="p-2.5 text-center text-slate-600 font-bold" x-text="item.unit"></td>
                                        <td class="p-2.5 text-center font-mono font-bold" :class="item.stock_available < item.quantity ? 'text-rose-600 font-black' : 'text-emerald-700'" x-text="item.stock_available"></td>
                                        <td class="p-2.5 text-center">
                                            <input type="number" min="0.01" step="any" x-model.number="item.quantity" class="w-24 px-2.5 py-1.5 rounded-lg border text-center font-mono font-bold focus:ring-2 focus:ring-amber-500" :class="item.quantity > item.stock_available ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-slate-300'">
                                            <div x-show="item.quantity > item.stock_available" class="text-[10px] text-rose-600 font-bold mt-0.5">Vượt tồn kho!</div>
                                        </td>
                                        <td class="p-2.5 text-right font-mono font-bold text-slate-800" x-text="formatNumber(item.export_price) + ' đ'"></td>
                                        <td class="p-2.5 text-right font-mono font-black text-amber-900" x-text="formatNumber(item.quantity * item.export_price) + ' đ'"></td>
                                        <td class="p-2.5 text-center">
                                            <button type="button" @click="removeItem(idx)" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center cursor-pointer">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="form.items.length === 0">
                                    <td colspan="8" class="text-center py-8 text-slate-400 italic">
                                        Chưa có sản phẩm nào trong phiếu. Hãy tìm kiếm ở ô phía trên để thêm sản phẩm cần xuất kho.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot x-show="form.items.length > 0" class="bg-slate-50 font-bold border-t border-slate-200">
                                <tr>
                                    <td colspan="4" class="p-2.5 text-right uppercase text-slate-600">Tổng Số Lượng:</td>
                                    <td class="p-2.5 text-center font-mono font-black text-slate-900" x-text="totalQty()"></td>
                                    <td class="p-2.5 text-right uppercase text-slate-600">Tổng Giá Trị:</td>
                                    <td class="p-2.5 text-right font-mono font-black text-base text-amber-900" x-text="formatNumber(totalAmount()) + ' đ'"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Ghi chú & Tùy chọn -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Ghi Chú Chi Tiết Xuất Kho</label>
                            <textarea x-model="form.notes" rows="2" placeholder="Ghi chú chi tiết mục đích xuất, máy cần lắp ráp, bảo hành cho khách nào..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-600 bg-white"></textarea>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 font-bold cursor-pointer">
                                <input type="checkbox" x-model="form.allow_negative_stock" class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                                <span class="text-[11px] leading-tight">Cho phép xuất trước kể cả khi số lượng vượt tồn kho</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-slate-50 p-4 border-t border-slate-200 flex items-center justify-between shrink-0">
                    <div class="text-xs text-slate-500">
                        <span class="font-bold text-slate-700" x-text="form.items.length"></span> mặt hàng cần xuất
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs cursor-pointer">
                            Đóng
                        </button>
                        <button type="button" @click="submitIssue('Hoàn thành')" :disabled="isSubmitting || form.items.length === 0" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs flex items-center gap-2 shadow-md cursor-pointer disabled:opacity-50">
                            <i class="fa-solid fa-check" x-show="!isSubmitting"></i>
                            <i class="fa-solid fa-spinner fa-spin" x-show="isSubmitting"></i>
                            <span>Hoàn Tất & Trừ Tồn Kho</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: XEM CHI TIẾT PHIẾU XUẤT KHO        -->
    <!-- ========================================== -->
    <div x-show="showDetailModal" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showDetailModal = false"></div>

        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]" @click.away="showDetailModal = false">
                <div class="bg-amber-800 text-white p-4 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice text-amber-200 text-lg"></i>
                        <div>
                            <h3 class="font-black text-base" x-text="'Chi Tiết Phiếu Xuất: ' + (detailTx?.code || '')"></h3>
                            <p class="text-[11px] text-amber-200" x-text="'Ngày lập: ' + (detailTx?.created_at ? new Date(detailTx.created_at).toLocaleString('vi-VN') : '')"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="'/inventory/transactions/' + detailTx?.id + '/print'" target="_blank" class="px-3 py-1.5 rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-print"></i> In Phiếu
                        </a>
                        <button type="button" @click="showDetailModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <div class="space-y-1">
                            <div><span class="font-bold text-slate-600">Kho xuất:</span> <span class="font-black text-slate-900" x-text="detailTx?.warehouse?.name"></span></div>
                            <div><span class="font-bold text-slate-600">Người nhận:</span> <span class="font-bold text-slate-900" x-text="detailTx?.supplier_name || 'N/A'"></span></div>
                            <div><span class="font-bold text-slate-600">Lý do & Ghi chú:</span> <span class="text-slate-800 italic" x-text="detailTx?.notes || 'Không có'"></span></div>
                        </div>
                        <div class="space-y-1 text-right sm:text-left">
                            <div><span class="font-bold text-slate-600">Người lập:</span> <span class="font-bold text-slate-900" x-text="detailTx?.created_by?.name || 'Admin'"></span></div>
                            <div><span class="font-bold text-slate-600">Trạng thái:</span> <span class="font-black text-emerald-700" x-text="detailTx?.status"></span></div>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                                <tr>
                                    <th class="p-2.5 w-10 text-center">#</th>
                                    <th class="p-2.5">Sản Phẩm</th>
                                    <th class="p-2.5 w-24 text-center">Mã SKU</th>
                                    <th class="p-2.5 w-20 text-center">ĐVT</th>
                                    <th class="p-2.5 w-24 text-center">Số Lượng</th>
                                    <th class="p-2.5 w-32 text-right">Đơn Giá Vốn (đ)</th>
                                    <th class="p-2.5 w-36 text-right">Thành Tiền (đ)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(it, idx) in (detailTx?.items || [])" :key="it.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-2.5 text-center text-slate-400 font-mono" x-text="idx + 1"></td>
                                        <td class="p-2.5 font-bold text-slate-900" x-text="it.product?.name || 'Sản phẩm'"></td>
                                        <td class="p-2.5 text-center font-mono font-bold text-amber-700" x-text="it.product?.sku || '-'"></td>
                                        <td class="p-2.5 text-center" x-text="it.product?.unit || 'cái'"></td>
                                        <td class="p-2.5 text-center font-mono font-bold" x-text="formatNumber(it.quantity)"></td>
                                        <td class="p-2.5 text-right font-mono" x-text="formatNumber(it.cost_price_at_transaction || it.import_price) + ' đ'"></td>
                                        <td class="p-2.5 text-right font-mono font-black text-amber-900" x-text="formatNumber(it.quantity * (it.cost_price_at_transaction || it.import_price)) + ' đ'"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                                <tr>
                                    <td colspan="6" class="p-2.5 text-right uppercase">Tổng Tiền:</td>
                                    <td class="p-2.5 text-right font-mono font-black text-base text-amber-900" x-text="formatNumber(detailTx?.total_amount || 0) + ' đ'"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 border-t border-slate-200 text-right shrink-0">
                    <button type="button" @click="showDetailModal = false" class="px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 font-bold text-xs cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function warehouseIssueApp() {
    return {
        showCreateModal: false,
        showDetailModal: false,
        isSubmitting: false,
        isSearching: false,
        searchQuery: '',
        searchResults: [],
        detailTx: null,

        form: {
            warehouse_id: '{{ $warehouses->first()?->id ?? 1 }}',
            reason_type: 'Xuất Lắp Ráp & Sản Xuất Máy Lọc Nước',
            supplier_name: 'Xưởng sản xuất / Lắp ráp',
            reference_order_code: '',
            notes: '',
            allow_negative_stock: false,
            items: []
        },

        openCreateModal() {
            this.form = {
                warehouse_id: '{{ $warehouses->first()?->id ?? 1 }}',
                reason_type: 'Xuất Lắp Ráp & Sản Xuất Máy Lọc Nước',
                supplier_name: 'Xưởng sản xuất / Lắp ráp',
                reference_order_code: '',
                notes: '',
                allow_negative_stock: false,
                items: []
            };
            this.searchQuery = '';
            this.searchResults = [];
            this.showCreateModal = true;
        },

        async onWarehouseChange() {
            // Khi đổi kho, cập nhật lại tồn kho của các items đang có
            for (let it of this.form.items) {
                try {
                    const res = await fetch(`/api/inventory/search-products?q=${encodeURIComponent(it.sku)}&warehouse_id=${this.form.warehouse_id}`);
                    const data = await res.json();
                    if (data.products && data.products.length > 0) {
                        const found = data.products.find(p => p.id == it.product_id);
                        if (found) it.stock_available = found.warehouse_stock;
                    }
                } catch(e) {}
            }
        },

        async searchProducts() {
            if (!this.searchQuery || this.searchQuery.trim().length < 1) {
                this.searchResults = [];
                return;
            }
            this.isSearching = true;
            try {
                const res = await fetch(`/api/inventory/search-products?q=${encodeURIComponent(this.searchQuery)}&warehouse_id=${this.form.warehouse_id}`);
                const data = await res.json();
                this.searchResults = data.products || [];
            } catch (e) {
                console.error(e);
            } finally {
                this.isSearching = false;
            }
        },

        addItem(p) {
            const exist = this.form.items.find(x => x.product_id == p.id);
            if (exist) {
                exist.quantity += 1;
            } else {
                this.form.items.push({
                    product_id: p.id,
                    name: p.name,
                    sku: p.sku,
                    unit: p.unit,
                    quantity: 1,
                    stock_available: p.warehouse_stock,
                    export_price: p.cost_price || 0,
                    notes: ''
                });
            }
            this.searchQuery = '';
            this.searchResults = [];
        },

        removeItem(idx) {
            this.form.items.splice(idx, 1);
        },

        totalQty() {
            return this.form.items.reduce((sum, it) => sum + (parseFloat(it.quantity) || 0), 0);
        },

        totalAmount() {
            return this.form.items.reduce((sum, it) => sum + ((parseFloat(it.quantity) || 0) * (parseFloat(it.export_price) || 0)), 0);
        },

        async submitIssue(status) {
            if (this.form.items.length === 0) {
                alert('Vui lòng thêm ít nhất một sản phẩm cần xuất kho!');
                return;
            }

            // Kiểm tra cảnh báo nếu vượt tồn kho mà không tick cho phép âm
            if (!this.form.allow_negative_stock) {
                const overItem = this.form.items.find(it => it.quantity > it.stock_available);
                if (overItem) {
                    alert(`Sản phẩm "${overItem.name}" chỉ còn tồn ${overItem.stock_available} tại kho được chọn, không đủ xuất ${overItem.quantity}!\nVui lòng giảm số lượng hoặc tích chọn "Cho phép xuất trước".`);
                    return;
                }
            }

            this.isSubmitting = true;
            try {
                const payload = {
                    warehouse_id: this.form.warehouse_id,
                    reason_type: this.form.reason_type,
                    supplier_name: this.form.supplier_name,
                    reference_order_code: this.form.reference_order_code,
                    notes: this.form.notes,
                    status: status,
                    allow_negative_stock: this.form.allow_negative_stock,
                    items: this.form.items
                };

                const res = await fetch('{{ route("inventory.issues.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    alert('Lập phiếu xuất kho thành công! Mã phiếu: ' + data.code);
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi lưu phiếu xuất kho');
                }
            } catch (e) {
                alert('Có lỗi xảy ra: ' + e.message);
            } finally {
                this.isSubmitting = false;
            }
        },

        async viewDetail(id) {
            try {
                const res = await fetch(`/inventory/transactions/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.detailTx = data.transaction;
                    this.showDetailModal = true;
                } else {
                    alert(data.message || 'Không thể tìm thấy phiếu');
                }
            } catch (e) {
                alert('Không thể tải chi tiết phiếu: ' + e.message);
            }
        },

        async cancelIssue(id, code) {
            const reason = prompt(`Bạn có chắc muốn HỦY phiếu xuất kho ${code}?\nSố lượng tồn kho sẽ được cộng hoàn trả lại kho.\nNhập lý do hủy:`);
            if (reason === null) return;

            try {
                const res = await fetch(`/inventory/transactions/${id}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason: reason })
                });

                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Không thể hủy phiếu');
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            }
        },

        formatNumber(num) {
            if (!num) return '0';
            return new Intl.NumberFormat('vi-VN').format(num);
        }
    }
}
</script>
@endsection
