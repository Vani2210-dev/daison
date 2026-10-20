@extends('layouts.app')

@section('title', 'Quản Lý Phiếu Nhập Kho (Goods Receipt) | Máy lọc nước Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="warehouseReceiptApp()">

    <!-- 1. Sub-Section Navigation Tabs (Kho hàng Đại Sơn) -->
    @include('inventory.nav_tabs')

    <!-- 2. Header Banner & Action Buttons -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-900/20">
                <i class="fa-solid fa-dolly text-lg"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Quản Lý Phiếu Nhập Kho (PNK)</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-extrabold border border-emerald-200">Kho Hàng & Vật Tư</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Nhập hàng hóa từ Nhà cung cấp, nhập linh kiện lắp ráp máy lọc nước, tự động cộng tồn kho và cập nhật giá vốn.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-emerald-700/20 cursor-pointer transition-all hover:scale-102">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Lập Phiếu Nhập Kho</span>
            </button>
            <button type="button" onclick="window.location.reload()" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-[#1b4a8c]"></i>
                <span class="hidden sm:inline">Làm mới</span>
            </button>
        </div>
    </div>

    <!-- 3. Top Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Card 1: Tổng số phiếu nhập -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50/50 rounded-2xl p-4 border border-emerald-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-emerald-800 font-bold text-xs">
                <span>Tổng Phiếu Nhập Kho</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-200/80 text-emerald-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-950 font-mono tracking-tight">
                {{ number_format($stats['total_count']) }} <span class="text-xs font-bold text-emerald-700">phiếu</span>
            </div>
            <div class="text-[11px] text-emerald-700 font-medium">Toàn bộ lịch sử nhập kho</div>
        </div>

        <!-- Card 2: Giá trị nhập kho -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-2xl p-4 border border-blue-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-blue-800 font-bold text-xs">
                <span>Tổng Giá Trị Nhập Kho</span>
                <div class="w-8 h-8 rounded-xl bg-blue-200/80 text-blue-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-blue-950 font-mono tracking-tight">
                {{ number_format($stats['total_amount'], 0, ',', '.') }} <span class="text-xs font-bold text-blue-700">đ</span>
            </div>
            <div class="text-[11px] text-blue-700 font-medium">Tổng giá trị tiền hàng đã nhập</div>
        </div>

        <!-- Card 3: Nhập kho hôm nay -->
        <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 rounded-2xl p-4 border border-amber-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-amber-800 font-bold text-xs">
                <span>Phiếu Nhập Hôm Nay</span>
                <div class="w-8 h-8 rounded-xl bg-amber-200/80 text-amber-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-950 font-mono tracking-tight">
                {{ number_format($stats['today_count']) }} <span class="text-xs font-bold text-amber-700">phiếu</span>
            </div>
            <div class="text-[11px] text-amber-700 font-medium">Hàng về trong ngày hôm nay</div>
        </div>

        <!-- Card 4: Giá trị nhập hôm nay -->
        <div class="bg-gradient-to-br from-purple-50 to-pink-50/50 rounded-2xl p-4 border border-purple-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-purple-800 font-bold text-xs">
                <span>Giá Trị Nhập Hôm Nay</span>
                <div class="w-8 h-8 rounded-xl bg-purple-200/80 text-purple-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-purple-950 font-mono tracking-tight">
                {{ number_format($stats['today_amount'], 0, ',', '.') }} <span class="text-xs font-bold text-purple-700">đ</span>
            </div>
            <div class="text-[11px] text-purple-700 font-medium">Giá trị nhập kho trong ngày</div>
        </div>
    </div>

    <!-- 4. Filter Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('inventory.receipts') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <!-- Kho nhập -->
            <div>
                <label class="block font-bold text-slate-600 mb-1">Kho Nhập</label>
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
                    <option value="Đã nhận hàng" {{ request('status') === 'Đã nhận hàng' ? 'selected' : '' }}>Đã nhận hàng</option>
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã phiếu, NCC, hóa đơn, SKU..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1b4a8c]">
                    <button type="submit" class="px-4 py-2 bg-[#1b4a8c] hover:bg-blue-800 text-white font-bold rounded-xl cursor-pointer shrink-0">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request()->anyFilled(['warehouse_id', 'status', 'date_from', 'date_to', 'search']))
                        <a href="{{ route('inventory.receipts') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl cursor-pointer shrink-0" title="Xóa bộ lọc">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- 5. Bảng Danh Sách Phiếu Nhập Kho -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="p-3.5 w-12 text-center">STT</th>
                        <th class="p-3.5">Mã Phiếu</th>
                        <th class="p-3.5">Thời Gian</th>
                        <th class="p-3.5">Kho Nhập</th>
                        <th class="p-3.5">Nhà Cung Cấp / Đối Tác</th>
                        <th class="p-3.5 text-center">Số Mặt Hàng</th>
                        <th class="p-3.5 text-right">Tổng Tiền</th>
                        <th class="p-3.5">Người Lập</th>
                        <th class="p-3.5 text-center">Trạng Thái</th>
                        <th class="p-3.5 text-center w-36">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receipts as $idx => $r)
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $r->status === 'Đã hủy' ? 'opacity-60 bg-slate-50' : '' }}">
                        <td class="p-3.5 text-center text-slate-400 font-medium">
                            {{ $receipts->firstItem() + $idx }}
                        </td>
                        <td class="p-3.5">
                            <div class="font-mono font-black text-emerald-700 hover:underline cursor-pointer" @click="viewDetail({{ $r->id }})">
                                {{ $r->code }}
                            </div>
                            @if($r->reference_order_code)
                                <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-receipt text-slate-400"></i>
                                    <span>HĐ: {{ $r->reference_order_code }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="p-3.5 whitespace-nowrap">
                            <div class="font-bold text-slate-800">{{ $r->created_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $r->created_at->format('H:i') }}</div>
                        </td>
                        <td class="p-3.5 font-bold text-slate-800">
                            {{ $r->warehouse?->name ?? 'Kho Tổng' }}
                        </td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900">{{ $r->supplier_name ?: 'Nhà cung cấp' }}</div>
                            @if($r->notes)
                                <div class="text-[10px] text-slate-500 line-clamp-1 italic">{{ $r->notes }}</div>
                            @endif
                        </td>
                        <td class="p-3.5 text-center font-mono font-bold text-slate-700">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200">
                                {{ $r->items->count() }} SP ({{ number_format($r->items->sum('quantity')) }})
                            </span>
                        </td>
                        <td class="p-3.5 text-right font-mono font-black text-emerald-700 text-sm whitespace-nowrap">
                            {{ number_format((float)$r->total_amount, 0, ',', '.') }} đ
                        </td>
                        <td class="p-3.5 text-slate-700">
                            <div class="font-semibold">{{ $r->createdBy?->name ?? 'Admin' }}</div>
                        </td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            @if($r->status === 'Hoàn thành' || $r->status === 'Đã nhận hàng')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Hoàn thành
                                </span>
                            @elseif($r->status === 'Bản nháp')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-clock mr-1"></i> Bản nháp
                                </span>
                            @elseif($r->status === 'Đã hủy')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                    <i class="fa-solid fa-ban mr-1"></i> Đã hủy
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-700">
                                    {{ $r->status }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" @click="viewDetail({{ $r->id }})" class="w-8 h-8 rounded-lg bg-sky-50 hover:bg-sky-100 text-[#1b4a8c] flex items-center justify-center cursor-pointer transition-colors" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                                <a href="{{ route('inventory.transactions.print', $r->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 flex items-center justify-center cursor-pointer transition-colors" title="In Phiếu Nhập Kho">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </a>
                                @if($r->status !== 'Đã hủy')
                                <button type="button" @click="cancelReceipt({{ $r->id }}, '{{ $r->code }}')" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center cursor-pointer transition-colors" title="Hủy phiếu & Hoàn tồn">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-12 text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div class="font-bold text-slate-600 text-sm">Chưa có phiếu nhập kho nào</div>
                            <p class="text-xs text-slate-400 mt-1">Bấm "+ Lập Phiếu Nhập Kho" để bắt đầu ghi nhận hàng về kho</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $receipts->links() }}
        </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: LẬP PHIẾU NHẬP KHO (QUICK DRAWER) -->
    <!-- ========================================== -->
    <div x-show="showCreateModal" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showCreateModal = false"></div>

        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-5xl overflow-hidden flex flex-col max-h-[92vh]" @click.away="showCreateModal = false">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-700 to-teal-800 text-white p-4 sm:p-5 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-dolly"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-base sm:text-lg">Lập Phiếu Nhập Kho Mới</h3>
                            <p class="text-xs text-emerald-100">Cộng số lượng tồn kho và cập nhật giá vốn vật tư / máy lọc nước</p>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-4 text-xs">
                    <!-- Hàng 1: Kho nhập & Nhà cung cấp & Chứng từ -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kho Nhập Hàng <span class="text-rose-500">*</span></label>
                            <select x-model="form.warehouse_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-600 bg-white font-bold">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nhà Cung Cấp / Đối Tác</label>
                            <input type="text" x-model="form.supplier_name" list="supplier_list" placeholder="Tên công ty / nhà máy / người giao" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-600 bg-white">
                            <datalist id="supplier_list">
                                <option value="Xưởng Lắp Ráp Máy Lọc Nước Đại Sơn">
                                <option value="Công ty CP Nhựa Thiếu Niên Tiền Phong">
                                <option value="Tập đoàn Karofi Việt Nam">
                                <option value="Tập đoàn Kangaroo">
                                <option value="Tập đoàn Sơn Hà">
                                <option value="Công ty CP Mutosi">
                                <option value="Công ty Cổ phần Cúc Phương">
                            </datalist>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Số Hóa Đơn / Chứng Từ Gốc</label>
                            <input type="text" x-model="form.reference_order_code" placeholder="VD: HĐ-009182, BB-998" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-600 bg-white">
                        </div>
                    </div>

                    <!-- Hàng 2: Tìm kiếm & Thêm sản phẩm vào phiếu -->
                    <div class="relative bg-emerald-50/60 p-4 rounded-2xl border border-emerald-200 space-y-2">
                        <label class="block font-black text-emerald-900 text-xs">
                            <i class="fa-solid fa-barcode text-emerald-700 mr-1"></i> Tìm Kiếm & Chọn Sản Phẩm Cần Nhập
                        </label>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.250ms="searchProducts()" placeholder="Gõ tên sản phẩm, mã SKU hoặc quét mã QR..." class="w-full px-4 py-2.5 pl-10 rounded-xl border border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-600 bg-white shadow-xs">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-emerald-700"></i>
                            <div x-show="isSearching" class="absolute right-3.5 top-3 text-emerald-700 font-bold">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                            </div>
                        </div>

                        <!-- Dropdown Gợi Ý Sản Phẩm -->
                        <div x-show="searchResults.length > 0" class="absolute left-4 right-4 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto divide-y divide-slate-100">
                            <template x-for="p in searchResults" :key="p.id">
                                <div @click="addItem(p)" class="p-2.5 hover:bg-emerald-50 cursor-pointer flex items-center justify-between transition-colors">
                                    <div>
                                        <div class="font-bold text-slate-800" x-text="p.name"></div>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            SKU: <span class="font-bold text-emerald-700" x-text="p.sku"></span> | ĐVT: <span x-text="p.unit"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono font-bold text-slate-900" x-text="formatNumber(p.cost_price) + ' đ'"></div>
                                        <div class="text-[10px] text-slate-500">Tồn kho: <span class="font-bold text-blue-700" x-text="p.warehouse_stock"></span></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Hàng 3: Bảng danh sách hàng hóa trong phiếu -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                        <table class="w-full text-left">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                                <tr>
                                    <th class="p-2.5 w-10 text-center">#</th>
                                    <th class="p-2.5 min-w-[180px]">Sản Phẩm</th>
                                    <th class="p-2.5 min-w-[260px] text-center bg-amber-50/70 border-x border-amber-200/60 text-amber-900">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <i class="fa-solid fa-calendar-check text-amber-600"></i>
                                            <span>Số Lô & Hạn Sử Dụng (Date)</span>
                                        </div>
                                    </th>
                                    <th class="p-2.5 w-20 text-center">ĐVT</th>
                                    <th class="p-2.5 w-24 text-center">Số Lượng</th>
                                    <th class="p-2.5 w-32 text-right">Đơn Giá Nhập (đ)</th>
                                    <th class="p-2.5 w-32 text-right">Thành Tiền (đ)</th>
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
                                        
                                        <!-- Cột Lô & Date -->
                                        <td class="p-2 bg-amber-50/30 border-x border-amber-100">
                                            <div class="space-y-1.5">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] font-bold text-slate-500 w-10 shrink-0">Số Lô:</span>
                                                    <input type="text" x-model="item.batch_code" placeholder="Mã lô NSX (VD: L2610-01)" class="flex-1 px-2 py-1 rounded-lg border border-slate-300 text-xs font-mono font-semibold focus:ring-1 focus:ring-emerald-500 bg-white">
                                                </div>
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <div>
                                                        <span class="block text-[9px] font-bold text-slate-400">NSX:</span>
                                                        <input type="date" x-model="item.manufacturing_date" class="w-full px-1.5 py-1 rounded-lg border border-slate-300 text-[11px] font-mono focus:ring-1 focus:ring-emerald-500 bg-white">
                                                    </div>
                                                    <div>
                                                        <span class="block text-[9px] font-bold text-amber-700 flex items-center justify-between">
                                                            <span>HSD (Date):</span>
                                                            <template x-if="item.expiry_date">
                                                                <span class="text-emerald-700 font-black">✓</span>
                                                            </template>
                                                        </span>
                                                        <input type="date" x-model="item.expiry_date" class="w-full px-1.5 py-1 rounded-lg border border-amber-300 text-[11px] font-mono font-bold text-amber-900 focus:ring-1 focus:ring-amber-500 bg-amber-50/40">
                                                    </div>
                                                </div>
                                                <!-- Phím chọn nhanh HSD -->
                                                <div class="flex items-center gap-1 pt-0.5">
                                                    <span class="text-[9px] text-slate-400">Chọn nhanh HSD:</span>
                                                    <button type="button" @click="setQuickExpiry(item, 6)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 rounded text-[9px] font-bold text-slate-600 transition-colors cursor-pointer">+6T</button>
                                                    <button type="button" @click="setQuickExpiry(item, 12)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 rounded text-[9px] font-bold text-slate-600 transition-colors cursor-pointer">+12T</button>
                                                    <button type="button" @click="setQuickExpiry(item, 24)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 rounded text-[9px] font-bold text-slate-600 transition-colors cursor-pointer">+24T</button>
                                                    <button type="button" @click="setQuickExpiry(item, 36)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 rounded text-[9px] font-bold text-slate-600 transition-colors cursor-pointer">+36T</button>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="p-2.5 text-center text-slate-600 font-bold" x-text="item.unit"></td>
                                        <td class="p-2.5 text-center">
                                            <input type="number" min="0.01" step="any" x-model.number="item.quantity" class="w-20 px-2 py-1.5 rounded-lg border border-slate-300 text-center font-mono font-bold focus:ring-2 focus:ring-emerald-500">
                                        </td>
                                        <td class="p-2.5 text-right">
                                            <input type="number" min="0" step="1000" x-model.number="item.import_price" class="w-28 px-2 py-1.5 rounded-lg border border-slate-300 text-right font-mono font-bold focus:ring-2 focus:ring-emerald-500">
                                        </td>
                                        <td class="p-2.5 text-right font-mono font-black text-emerald-800" x-text="formatNumber(item.quantity * item.import_price) + ' đ'"></td>
                                        <td class="p-2.5 text-center">
                                            <button type="button" @click="removeItem(idx)" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center cursor-pointer">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="form.items.length === 0">
                                    <td colspan="8" class="text-center py-8 text-slate-400 italic">
                                        Chưa có sản phẩm nào trong phiếu. Hãy tìm kiếm ở ô phía trên để thêm sản phẩm.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot x-show="form.items.length > 0" class="bg-slate-50 font-bold border-t border-slate-200">
                                <tr>
                                    <td colspan="4" class="p-2.5 text-right uppercase text-slate-600">Tổng Số Lượng:</td>
                                    <td class="p-2.5 text-center font-mono font-black text-slate-900" x-text="totalQty()"></td>
                                    <td class="p-2.5 text-right uppercase text-slate-600">Tổng Tiền Phiếu:</td>
                                    <td class="p-2.5 text-right font-mono font-black text-base text-emerald-800" x-text="formatNumber(totalAmount()) + ' đ'"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Ghi chú -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ghi Chú Phiếu Nhập Kho</label>
                        <textarea x-model="form.notes" rows="2" placeholder="Ghi chú về tình trạng hàng hóa, số xe chở, thỏa thuận bảo hành..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-600 bg-white"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-slate-50 p-4 border-t border-slate-200 flex items-center justify-between shrink-0">
                    <div class="text-xs text-slate-500">
                        <span class="font-bold text-slate-700" x-text="form.items.length"></span> mặt hàng trong phiếu
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs cursor-pointer">
                            Đóng
                        </button>
                        <button type="button" @click="submitReceipt('Hoàn thành')" :disabled="isSubmitting || form.items.length === 0" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center gap-2 shadow-md cursor-pointer disabled:opacity-50">
                            <i class="fa-solid fa-check" x-show="!isSubmitting"></i>
                            <i class="fa-solid fa-spinner fa-spin" x-show="isSubmitting"></i>
                            <span>Hoàn Tất & Tăng Tồn Kho</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: XEM CHI TIẾT PHIẾU NHẬP KHO       -->
    <!-- ========================================== -->
    <div x-show="showDetailModal" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showDetailModal = false"></div>

        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]" @click.away="showDetailModal = false">
                <div class="bg-[#1b4a8c] text-white p-4 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice text-amber-300 text-lg"></i>
                        <div>
                            <h3 class="font-black text-base" x-text="'Chi Tiết Phiếu: ' + (detailTx?.code || '')"></h3>
                            <p class="text-[11px] text-blue-200" x-text="'Ngày lập: ' + (detailTx?.created_at ? new Date(detailTx.created_at).toLocaleString('vi-VN') : '')"></p>
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
                            <div><span class="font-bold text-slate-600">Kho nhập:</span> <span class="font-black text-slate-900" x-text="detailTx?.warehouse?.name"></span></div>
                            <div><span class="font-bold text-slate-600">Nhà cung cấp:</span> <span class="font-bold text-slate-900" x-text="detailTx?.supplier_name || 'N/A'"></span></div>
                            <div><span class="font-bold text-slate-600">Hóa đơn chứng từ:</span> <span class="font-mono text-slate-800" x-text="detailTx?.reference_order_code || 'Không có'"></span></div>
                        </div>
                        <div class="space-y-1 text-right sm:text-left">
                            <div><span class="font-bold text-slate-600">Người lập:</span> <span class="font-bold text-slate-900" x-text="detailTx?.created_by?.name || 'Admin'"></span></div>
                            <div><span class="font-bold text-slate-600">Trạng thái:</span> <span class="font-black text-emerald-700" x-text="detailTx?.status"></span></div>
                            <div><span class="font-bold text-slate-600">Ghi chú:</span> <span class="italic text-slate-600" x-text="detailTx?.notes || 'Không có'"></span></div>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                                <tr>
                                    <th class="p-2.5 w-10 text-center">#</th>
                                    <th class="p-2.5">Sản Phẩm</th>
                                    <th class="p-2.5 w-24 text-center">Mã SKU</th>
                                    <th class="p-2.5 w-36 text-center">Số Lô / HSD (Date)</th>
                                    <th class="p-2.5 w-20 text-center">ĐVT</th>
                                    <th class="p-2.5 w-24 text-center">Số Lượng</th>
                                    <th class="p-2.5 w-32 text-right">Đơn Giá (đ)</th>
                                    <th class="p-2.5 w-36 text-right">Thành Tiền (đ)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(it, idx) in (detailTx?.items || [])" :key="it.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-2.5 text-center text-slate-400 font-mono" x-text="idx + 1"></td>
                                        <td class="p-2.5 font-bold text-slate-900" x-text="it.product?.name || 'Sản phẩm'"></td>
                                        <td class="p-2.5 text-center font-mono font-bold text-emerald-700" x-text="it.product?.sku || '-'"></td>
                                        <td class="p-2.5 text-center text-xs">
                                            <div class="font-mono font-bold text-slate-800" x-text="it.batch_code || '-'"></div>
                                            <template x-if="it.expiry_date">
                                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200" x-text="'HSD: ' + it.expiry_date"></span>
                                            </template>
                                        </td>
                                        <td class="p-2.5 text-center" x-text="it.product?.unit || 'cái'"></td>
                                        <td class="p-2.5 text-center font-mono font-bold" x-text="formatNumber(it.quantity)"></td>
                                        <td class="p-2.5 text-right font-mono" x-text="formatNumber(it.import_price) + ' đ'"></td>
                                        <td class="p-2.5 text-right font-mono font-black text-emerald-800" x-text="formatNumber(it.quantity * it.import_price) + ' đ'"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                                <tr>
                                    <td colspan="7" class="p-2.5 text-right uppercase">Tổng Tiền:</td>
                                    <td class="p-2.5 text-right font-mono font-black text-base text-emerald-800" x-text="formatNumber(detailTx?.total_amount || 0) + ' đ'"></td>
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
function warehouseReceiptApp() {
    return {
        showCreateModal: false,
        showDetailModal: false,
        isSubmitting: false,
        isSearching: false,
        searchQuery: '',
        searchResults: [],
        detailTx: null,

        form: {
            warehouse_id: '{{ $warehouses->firstWhere('code', 'KHO-MQ-01')?->id ?? $warehouses->first()?->id ?? 1 }}',
            supplier_name: '',
            reference_order_code: '',
            notes: '',
            items: []
        },

        openCreateModal() {
            this.form = {
                warehouse_id: '{{ $warehouses->firstWhere('code', 'KHO-MQ-01')?->id ?? $warehouses->first()?->id ?? 1 }}',
                supplier_name: '',
                reference_order_code: '',
                notes: '',
                items: []
            };
            this.searchQuery = '';
            this.searchResults = [];
            this.showCreateModal = true;
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
            this.form.items.push({
                product_id: p.id,
                name: p.name,
                sku: p.sku,
                unit: p.unit,
                quantity: 1,
                import_price: p.cost_price || 0,
                batch_code: '',
                manufacturing_date: '',
                expiry_date: '',
                notes: ''
            });
            this.searchQuery = '';
            this.searchResults = [];
        },

        setQuickExpiry(item, months) {
            const d = new Date();
            d.setMonth(d.getMonth() + months);
            item.expiry_date = d.toISOString().split('T')[0];
            if (!item.batch_code) {
                const ymd = (new Date()).toISOString().slice(2, 10).replace(/-/g, '');
                item.batch_code = 'LÔ-' + ymd + '-' + (item.sku || 'SP');
            }
        },

        removeItem(idx) {
            this.form.items.splice(idx, 1);
        },

        totalQty() {
            return this.form.items.reduce((sum, it) => sum + (parseFloat(it.quantity) || 0), 0);
        },

        totalAmount() {
            return this.form.items.reduce((sum, it) => sum + ((parseFloat(it.quantity) || 0) * (parseFloat(it.import_price) || 0)), 0);
        },

        async submitReceipt(status) {
            if (this.form.items.length === 0) {
                alert('Vui lòng thêm ít nhất một sản phẩm cần nhập kho!');
                return;
            }

            this.isSubmitting = true;
            try {
                const payload = {
                    warehouse_id: this.form.warehouse_id,
                    supplier_name: this.form.supplier_name,
                    reference_order_code: this.form.reference_order_code,
                    notes: this.form.notes,
                    status: status,
                    items: this.form.items
                };

                const res = await fetch('{{ route("inventory.receipts.store") }}', {
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
                    alert('Lập phiếu nhập kho thành công! Mã phiếu: ' + data.code);
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi lưu phiếu nhập kho');
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

        async cancelReceipt(id, code) {
            const reason = prompt(`Bạn có chắc muốn HỦY phiếu nhập kho ${code}?\nSố lượng tồn kho sẽ được trừ trả lại.\nNhập lý do hủy:`);
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
