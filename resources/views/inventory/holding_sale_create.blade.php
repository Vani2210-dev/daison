@extends('layouts.app')

@section('title', 'Tạo Đơn Bán Hàng Trực Tiếp Từ Kho Hàng Ôm | ERP Đại Sơn')

@push('styles')
<style>
/* CSS tối ưu riêng cho màn hình Mobile (max-width: 768px) */
@media (max-width: 768px) {
    /* 1. Header Banner trên mobile */
    .mobile-header-banner {
        padding: 0.75rem 0.875rem !important;
        gap: 0.5rem !important;
        border-radius: 1rem !important;
    }
    .mobile-header-title {
        font-size: 0.95rem !important;
        line-height: 1.35 !important;
        font-weight: 800 !important;
    }
    .mobile-header-sub {
        font-size: 0.725rem !important;
        line-height: 1.4 !important;
        margin-top: 0.2rem !important;
    }
    .mobile-header-actions {
        width: 100% !important;
        margin-top: 0.25rem !important;
    }

    /* 2. Ô tìm kiếm & Dropdown gợi ý trên mobile */
    .mobile-search-wrapper {
        width: 100% !important;
        min-width: 100% !important;
    }
    .mobile-search-input {
        font-size: 14px !important;
        padding-top: 0.55rem !important;
        padding-bottom: 0.55rem !important;
    }
    .mobile-search-dropdown {
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        border-radius: 1rem !important;
    }

    /* 3. Bảng sản phẩm chuyển thành Card linh hoạt trên Mobile */
    .holding-sale-table-container {
        border: none !important;
        background: transparent !important;
    }
    .holding-sale-table,
    .holding-sale-table tbody {
        display: block !important;
        width: 100% !important;
    }
    .holding-sale-table thead {
        display: none !important;
    }
    .holding-sale-table tr.holding-sale-tr {
        display: grid !important;
        grid-template-columns: 1fr 1fr auto !important;
        grid-template-areas:
            "product product action"
            "stock stock stock"
            "qty price total";
        gap: 0.5rem 0.625rem !important;
        align-items: center !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1rem !important;
        padding: 0.875rem !important;
        margin-bottom: 0.75rem !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
    }
    .holding-sale-table tr.holding-sale-tr:last-child {
        margin-bottom: 0 !important;
    }
    
    .holding-sale-table td {
        display: block !important;
        padding: 0 !important;
        border: none !important;
    }
    .holding-sale-table td.col-stt {
        display: none !important;
    }
    .holding-sale-table td.col-product {
        grid-area: product !important;
        min-width: 0 !important;
    }
    .holding-sale-table td.col-action {
        grid-area: action !important;
        text-align: right !important;
    }
    .holding-sale-table td.col-stock {
        grid-area: stock !important;
        text-align: left !important;
        background: transparent !important;
        font-size: 0.75rem !important;
        padding: 0.15rem 0 !important;
    }
    .holding-sale-table td.col-stock::before {
        content: "Tồn kho ôm: ";
        color: #64748b;
        font-weight: 600;
    }
    .holding-sale-table td.col-qty {
        grid-area: qty !important;
    }
    .holding-sale-table td.col-qty::before {
        content: "SL BÁN";
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 3px;
    }
    .holding-sale-table td.col-price {
        grid-area: price !important;
    }
    .holding-sale-table td.col-price::before {
        content: "ĐƠN GIÁ (Đ)";
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 3px;
        text-align: right;
    }
    .holding-sale-table td.col-total {
        grid-area: total !important;
        text-align: right !important;
    }
    .holding-sale-table td.col-total::before {
        content: "THÀNH TIỀN";
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 3px;
    }
    .holding-sale-table td.col-qty input,
    .holding-sale-table td.col-price input {
        font-size: 13px !important;
        padding: 0.35rem 0.5rem !important;
        height: 38px !important;
        border-radius: 0.625rem !important;
    }

    .holding-sale-table tr.empty-row {
        display: block !important;
        grid-template-areas: none !important;
        padding: 2rem 1rem !important;
        background: #ffffff !important;
        border: 1px dashed #cbd5e1 !important;
        border-radius: 1rem !important;
    }
    .holding-sale-table tr.empty-row td {
        display: block !important;
        text-align: center !important;
    }

    /* 4. Khách hàng & Thanh toán trên mobile */
    .mobile-customer-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem !important;
    }
    .mobile-customer-toggle {
        display: flex !important;
        width: 100% !important;
    }
    .mobile-customer-toggle button {
        flex: 1 !important;
        text-align: center !important;
        padding: 0.45rem 0.5rem !important;
    }
    .mobile-input {
        font-size: 14px !important;
        padding: 0.55rem 0.75rem !important;
    }

    /* 5. Nút hoàn tất đơn bán */
    .mobile-btn-submit {
        height: 48px !important;
        font-size: 0.925rem !important;
    }
}
</style>
@endpush

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="holdingSaleApp()">

    <!-- 1. Header Banner -->
    <div class="mobile-header-banner bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-start sm:items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
            <a href="{{ url('/inventory/import?tab=stocks') }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#1b4a8c] hover:bg-blue-800 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-900/20 cursor-pointer transition-transform hover:scale-105 mt-0.5 sm:mt-0" title="Quay lại Kho hàng ôm">
                <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
            </a>

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="mobile-header-title text-base sm:text-2xl font-black text-slate-900 tracking-tight">Tạo Đơn Bán Hàng Trực Tiếp Từ Kho Hàng Ôm</h1>
                    <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full bg-blue-100 text-[#1b4a8c] text-[11px] font-extrabold border border-blue-200">Bán hàng ngay & Trừ kho ôm</span>
                </div>
                <div class="mobile-header-sub flex items-center gap-1.5 sm:gap-2 mt-1 text-xs text-slate-500 font-medium flex-wrap">
                    <span>Kho xuất: <strong class="text-blue-900 font-bold" x-text="saleForm.warehouse_name || '---'"></strong></span>
                    <span>&bull;</span>
                    <span>Nhân viên bán: <strong class="text-slate-800 font-bold" x-text="saleForm.employee_name || '---'"></strong></span>
                    <span class="hidden sm:inline">&bull;</span>
                    <span class="hidden sm:inline text-emerald-700 font-semibold"><i class="fa-solid fa-bolt mr-0.5"></i>Tự động trừ tồn kho ôm & đồng bộ công nợ</span>
                </div>
            </div>
        </div>

        <div class="mobile-header-actions flex items-center gap-2">
            @if(count($employeeWarehouses) > 1)
            <div class="flex items-center gap-1.5 text-xs bg-slate-50 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 w-full sm:w-auto justify-between sm:justify-start">
                <label class="font-bold text-slate-600 whitespace-nowrap text-[11px] sm:text-xs">Đổi kho ôm:</label>
                <select @change="onWarehouseChange($event.target.value)" class="bg-white font-bold text-[#1b4a8c] border border-slate-300 rounded-lg px-2 py-1 focus:outline-none text-xs flex-1 sm:flex-initial">
                    @foreach($employeeWarehouses as $ewh)
                        <option value="{{ $ewh->id }}" {{ ($currentWarehouse?->id == $ewh->id) ? 'selected' : '' }}>
                            {{ $ewh->employee_name }} ({{ $ewh->total_qty }} SP)
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <a href="{{ url('/inventory/import?tab=stocks') }}" class="hidden sm:flex px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs items-center gap-2 shadow-2xs cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-arrow-left text-slate-500"></i>
                <span>Hủy & Quay lại</span>
            </a>
        </div>
    </div>

    @if(count($employeeWarehouses) === 0)
    <div class="bg-white rounded-2xl border border-amber-200 p-8 text-center space-y-3">
        <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-2xl">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-base font-bold text-slate-800">Chưa có kho hàng ôm nào có tồn kho sẵn sàng để bán</h3>
        <p class="text-xs text-slate-500 max-w-md mx-auto">Vui lòng điều chuyển / xuất hàng ôm cho nhân viên từ kho công ty trước khi tạo đơn bán hàng trực tiếp.</p>
        <div class="pt-2">
            <a href="{{ url('/inventory/import?tab=stocks') }}" class="px-4 py-2 bg-[#1b4a8c] hover:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-xs transition-colors inline-flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Quay lại Quản lý Kho Hàng Ôm</span>
            </a>
        </div>
    </div>
    @else
    <!-- 2. Form Nội Dung Bán Hàng -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- Cột Trái & Giữa (2/3): Danh Sách Sản Phẩm Bán -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Card Sản phẩm bán -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 sm:p-5 space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-3 border-b border-slate-100 gap-3">
                    <div>
                        <h2 class="font-black text-slate-800 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-[#1b4a8c]"></i>
                            <span>Danh Sách Sản Phẩm Bán</span>
                        </h2>
                    </div>

                    <!-- Ô NHẬP TÌM & CHỌN SẢN PHẨM Ở ĐÂY ĐỂ THÊM VÀO ĐƠN (KHÔNG CẦN NÚT BẤM) -->
                    <div class="mobile-search-wrapper w-full sm:w-80 lg:w-[420px]">
                        <div class="relative w-full" @click.outside="showQuickAddDropdown = false">
                            <div class="relative flex items-center">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs pointer-events-none"></i>
                                <input type="text"
                                       x-ref="quickAddInput"
                                       x-model="quickAddSearch"
                                       @focus="if ((quickAddSearch || '').trim().length > 0) showQuickAddDropdown = true"
                                       @input="showQuickAddDropdown = (quickAddSearch || '').trim().length > 0; quickAddHighlight = 0; selectedQuickAddStock = null"
                                       @keydown.down.prevent="
                                           const list = getQuickAddFilteredStocks();
                                           if (list.length > 0) quickAddHighlight = (quickAddHighlight + 1) % list.length;
                                       "
                                       @keydown.up.prevent="
                                           const list = getQuickAddFilteredStocks();
                                           if (list.length > 0) quickAddHighlight = (quickAddHighlight - 1 + list.length) % list.length;
                                       "
                                       @keydown.enter.prevent="
                                           const list = getQuickAddFilteredStocks();
                                           if (list.length > 0) {
                                               addProductToOrder(list[quickAddHighlight] || list[0]);
                                           }
                                       "
                                       @keydown.escape.prevent="showQuickAddDropdown = false"
                                       placeholder="Gõ tên hoặc mã sản phẩm để tìm..."
                                       class="mobile-search-input w-full pl-8 pr-3 py-2 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:border-blue-600 focus:outline-none shadow-2xs transition-colors">
                            </div>

                            <!-- Dropdown menu gợi ý sản phẩm trong kho ôm -->
                            <div x-show="showQuickAddDropdown && (quickAddSearch || '').trim().length > 0"
                                 x-cloak
                                 class="mobile-search-dropdown absolute right-0 top-full mt-1.5 w-full sm:w-[460px] max-w-[95vw] z-50 bg-white rounded-2xl shadow-2xl border border-slate-200 max-h-72 overflow-y-auto py-1 text-xs">
                                <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                    <span>Kết quả tìm kiếm (<span x-text="getQuickAddFilteredStocks().length"></span>)</span>
                                    <span class="text-blue-700 font-semibold lowercase">Click hoặc Enter để chọn</span>
                                </div>

                                <template x-for="(stk, sIdx) in getQuickAddFilteredStocks()" :key="'top_stk_' + stk.product_id">
                                    <div @mousedown.prevent="addProductToOrder(stk)"
                                         @mouseenter="quickAddHighlight = sIdx"
                                         class="px-3.5 py-2.5 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                         :class="quickAddHighlight === sIdx ? 'bg-blue-50 text-blue-950 font-bold ring-1 ring-blue-300' : 'hover:bg-slate-50'">
                                        <div class="min-w-0 pr-3">
                                            <div class="font-bold text-slate-900 truncate" x-text="stk.product_name"></div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                <span class="font-semibold text-slate-700" x-text="'ĐVT: ' + (stk.unit || 'cái')"></span>
                                                <span>&bull;</span>
                                                <span class="text-emerald-700 font-semibold" x-text="'Tồn kho: ' + (stk.stock_quantity || 0) + ' ' + (stk.unit || 'cái')"></span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <div class="font-black text-blue-700 text-xs" x-text="formatMoney(stk.selling_price || 0) + ' đ'"></div>
                                            <template x-if="getItemQtyInOrder(stk.product_id) > 0">
                                                <span class="inline-flex items-center text-[10px] text-emerald-700 font-bold bg-emerald-100 px-2 py-0.5 rounded-md mt-0.5" x-text="'Đã chọn: ' + getItemQtyInOrder(stk.product_id) + ' (+1)'">
                                                </span>
                                            </template>
                                            <template x-if="!getItemQtyInOrder(stk.product_id)">
                                                <span class="inline-flex items-center text-[10px] text-blue-600 font-bold bg-blue-100 px-2 py-0.5 rounded-md mt-0.5">
                                                    + Thêm
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="getQuickAddFilteredStocks().length === 0">
                                    <div class="px-4 py-6 text-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-magnifying-glass text-xl mb-1 text-slate-300"></i>
                                        <p>Không tìm thấy sản phẩm nào phù hợp trong kho ôm.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bảng danh sách sản phẩm -->
                <div class="holding-sale-table-container border border-slate-200 rounded-xl overflow-visible">
                    <table class="holding-sale-table w-full text-left text-xs divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] uppercase">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">STT</th>
                                <th class="py-3 px-3 min-w-[260px]">Sản Phẩm Trong Kho Ôm</th>
                                <th class="py-3 px-2 w-24 text-center">Tồn Ôm</th>
                                <th class="py-3 px-2 w-24 text-center">SL Bán</th>
                                <th class="py-3 px-2 w-32 text-right">Đơn Giá (đ)</th>
                                <th class="py-3 px-3 w-32 text-right">Thành Tiền</th>
                                <th class="py-3 px-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(it, idx) in saleForm.items" :key="it.id">
                                <tr class="holding-sale-tr align-middle hover:bg-slate-50/50 transition-colors">
                                    <td class="col-stt py-3 px-3 text-center font-mono text-slate-400 font-bold" x-text="idx + 1"></td>
                                    
                                    <!-- Thông tin sản phẩm -->
                                    <td class="col-product py-2.5 px-3 min-w-[260px]">
                                        <div class="font-bold text-slate-900 text-xs" x-text="it.name"></div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5 flex items-center gap-2">
                                            <span x-text="'Mã: ' + (it.sku || '---')"></span>
                                            <span>&bull;</span>
                                            <span class="text-slate-500 font-medium" x-text="'ĐVT: ' + (it.unit || 'cái')"></span>
                                        </div>
                                    </td>

                                    <!-- Tồn Ôm -->
                                    <td class="col-stock py-2.5 px-2 text-center font-mono font-bold text-emerald-700 bg-emerald-50/50">
                                        <span x-text="(it.available_stock || 0) + ' ' + (it.unit || '')"></span>
                                    </td>

                                    <!-- SL Bán -->
                                    <td class="col-qty py-2.5 px-2 text-center">
                                        <input type="number" min="0.1" :max="it.available_stock || 9999" step="any" x-model.number="it.quantity" 
                                               class="w-full px-2.5 py-1.5 text-center font-bold font-mono border border-slate-300 rounded-xl focus:outline-none focus:border-blue-600"
                                               :class="it.quantity > (it.available_stock || 0) ? 'border-rose-500 bg-rose-50 text-rose-700' : ''">
                                    </td>

                                    <!-- Đơn Giá -->
                                    <td class="col-price py-2.5 px-2 text-right">
                                        <input type="number" min="0" x-model.number="it.unit_price" class="w-full px-2.5 py-1.5 text-right font-bold font-mono border border-slate-300 rounded-xl focus:outline-none focus:border-blue-600">
                                    </td>

                                    <!-- Thành Tiền -->
                                    <td class="col-total py-3 px-3 text-right font-mono font-black text-blue-700 text-xs" x-text="formatMoney((it.quantity || 0) * (it.unit_price || 0)) + ' đ'"></td>

                                    <!-- Xóa dòng -->
                                    <td class="col-action py-3 px-2 text-center">
                                        <button type="button" @click="removeItem(idx)" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer" title="Xóa dòng này">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <template x-if="saleForm.items.length === 0">
                                <tr class="empty-row">
                                    <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-cart-arrow-down text-3xl mb-2 text-slate-300"></i>
                                        <p class="font-semibold text-slate-700">Chưa có sản phẩm nào trong đơn.</p>
                                        <p class="text-slate-400 mt-1">Tìm hoặc click chọn sản phẩm ở ô tìm kiếm phía trên để thêm trực tiếp vào đơn bán.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>


            </div>
        </div>

        <!-- Cột Phải (1/3): Thông Tin Khách Hàng, Thanh Toán & Tổng Tiền -->
        <div class="space-y-4">
            
            <!-- Card Khách Hàng & Thanh Toán -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 sm:p-5 space-y-4">
                <div class="mobile-customer-header flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-black text-slate-800 text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-user-tag text-blue-700"></i>
                        <span>Thông Tin Khách Hàng</span>
                    </h3>
                    
                    <!-- Toggle Khách đại lý / Khách lẻ -->
                    <div class="mobile-customer-toggle inline-flex p-0.5 bg-slate-100 rounded-xl text-[11px] font-bold border border-slate-200">
                        <button type="button" 
                                @click="saleForm.customer_type = 'dealer'"
                                :class="saleForm.customer_type === 'dealer' ? 'bg-[#1b4a8c] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                            Đại Lý
                        </button>
                        <button type="button" 
                                @click="saleForm.customer_type = 'retail'; saleForm.dealer_id = ''"
                                :class="saleForm.customer_type === 'retail' ? 'bg-[#1b4a8c] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                            Khách Lẻ
                        </button>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- Chọn Đại lý nếu là khách sỉ -->
                    <template x-if="saleForm.customer_type === 'dealer'">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Chọn Đại Lý Nhận Hàng (*):</label>
                            <select x-model="saleForm.dealer_id" @change="onDealerChange()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-blue-600">
                                <option value="">-- Chọn đại lý trong danh sách --</option>
                                @foreach($dealers as $dl)
                                    <option value="{{ $dl->id }}" data-name="{{ $dl->name }}" data-phone="{{ $dl->phone }}" data-address="{{ $dl->address }}" data-tier="{{ $dl->tier }}">
                                        {{ $dl->name }} ({{ $dl->code ?: 'DL-'.$dl->id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tên Người Nhận / Khách Hàng (*):</label>
                        <input type="text" x-model="saleForm.customer_name" placeholder="Họ tên người nhận hàng" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:outline-none focus:border-blue-600">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số Điện Thoại:</label>
                        <input type="text" x-model="saleForm.customer_phone" placeholder="Số điện thoại liên hệ" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-mono focus:outline-none focus:border-blue-600">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Địa Chỉ Giao / Bàn Giao:</label>
                        <input type="text" x-model="saleForm.customer_address" placeholder="Địa chỉ giao hàng thực tế" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-none focus:border-blue-600">
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <label class="block font-bold text-slate-700 mb-1">Hình Thức Thanh Toán:</label>
                        <select x-model="saleForm.payment_method" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-blue-600">
                            <option value="Tiền mặt">Thanh toán ngay (Tiền mặt / Chuyển khoản)</option>
                            <option value="Công nợ (Ghi nợ đại lý)">Ghi nợ đại lý (Công nợ)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Card Tổng Kết & Nút Bấm -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-2xl border border-blue-200 shadow-2xs p-4 sm:p-5 space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-600 font-semibold">
                        <span>Tổng số mặt hàng:</span>
                        <span class="font-bold text-slate-800" x-text="saleForm.items.length + ' sản phẩm'"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-600 font-semibold">
                        <span>Tổng số lượng xuất bán:</span>
                        <span class="font-bold font-mono text-slate-900" x-text="getTotalQuantity() + ' ĐVT'"></span>
                    </div>
                    <div class="pt-3 border-t border-blue-200/80 flex items-center justify-between">
                        <span class="font-black text-blue-950 text-xs uppercase tracking-wider">TỔNG TIỀN ĐƠN BÁN:</span>
                        <span class="font-mono font-black text-blue-900 text-xl" x-text="formatMoney(getTotalAmount()) + ' đ'"></span>
                    </div>
                </div>

                <div class="pt-2 space-y-2">
                    <button type="button" 
                            @click="submitOrder()" 
                            :disabled="isSubmitting"
                            class="mobile-btn-submit w-full py-3 bg-[#1b4a8c] hover:bg-blue-800 text-white font-black text-sm rounded-xl shadow-md shadow-blue-900/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                        <template x-if="isSubmitting">
                            <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                        </template>
                        <template x-if="!isSubmitting">
                            <i class="fa-solid fa-check text-sm"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Đang Xử Lý & Trừ Kho Ôm...' : 'Xác Nhận & Hoàn Tất Bán Hàng'"></span>
                    </button>

                    <a href="{{ url('/inventory/import?tab=stocks') }}" 
                       class="w-full py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Quay lại trang kho hàng ôm</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
    @endif

</div>

<script>
function holdingSaleApp() {
    return {
        currentWarehouse: @json($currentWarehouse ?? null),
        employeeWarehouses: @json($employeeWarehouses ?? []),
        dealersList: @json($dealers ?? []),
        preselectedProductId: '{{ $preselectedProductId ?? "" }}',
        availableStocks: [],

        quickAddSearch: '',
        selectedQuickAddStock: null,
        showQuickAddDropdown: false,
        quickAddHighlight: 0,
        isSubmitting: false,

        saleForm: {
            warehouse_id: '{{ $currentWarehouse?->id ?? "" }}',
            warehouse_name: '{{ $currentWarehouse?->name ?? "" }}',
            employee_id: '{{ $currentWarehouse?->employee_id ?? "" }}',
            employee_name: '{{ $currentWarehouse?->employee_name ?? "" }}',
            customer_type: 'dealer',
            dealer_id: '',
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            payment_method: 'Tiền mặt',
            notes: '',
            items: []
        },

        init() {
            if (this.currentWarehouse && this.currentWarehouse.stocks) {
                this.availableStocks = this.currentWarehouse.stocks;
            }

            // Nếu người dùng bấm "Bán SP này" từ bảng tồn kho (có preselectedProductId) -> Thêm sẵn sản phẩm đó
            if (this.preselectedProductId && this.availableStocks && this.availableStocks.length > 0) {
                const match = this.availableStocks.find(s => String(s.product_id) === String(this.preselectedProductId));
                if (match) {
                    this.addProductToOrder(match);
                }
            }
        },

        onWarehouseChange(whId) {
            window.location.href = '/inventory/holding-sale/create?warehouse_id=' + whId;
        },

        getQuickAddFilteredStocks() {
            if (!this.availableStocks || this.availableStocks.length === 0) return [];
            const q = (this.quickAddSearch || '').trim();
            if (!q) {
                return [];
            }
            const cleanQ = this.normalizeVnText(q);
            return this.availableStocks.filter(stk => {
                const name = this.normalizeVnText(stk.product_name || '');
                const sku = (stk.sku || '').toLowerCase();
                return name.includes(cleanQ) || sku.includes(q.toLowerCase());
            });
        },

        getItemQtyInOrder(productId) {
            const found = (this.saleForm.items || []).find(i => String(i.product_id) === String(productId));
            return found ? found.quantity : 0;
        },

        addProductToOrder(stk) {
            if (!stk) return;

            // Kiểm tra xem sản phẩm đã có trong bảng chưa
            const existing = this.saleForm.items.find(i => String(i.product_id) === String(stk.product_id));
            if (existing) {
                const maxStock = Number(stk.stock_quantity || 0);
                if (Number(existing.quantity) < maxStock) {
                    existing.quantity = Number(existing.quantity) + 1;
                } else {
                    alert(`Sản phẩm [${stk.sku || ''}] ${stk.product_name} chỉ còn tồn ${maxStock} trong kho ôm!`);
                }
            } else {
                // Thêm mới sản phẩm vào danh sách
                this.saleForm.items.push({
                    id: 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                    product_id: stk.product_id,
                    sku: stk.sku,
                    name: stk.product_name,
                    unit: stk.unit || 'cái',
                    available_stock: Number(stk.stock_quantity || 0),
                    quantity: 1,
                    unit_price: Number(stk.selling_price || 0)
                });
            }

            // Xóa rỗng ô tìm kiếm sau khi thêm để sẵn sàng tìm sản phẩm tiếp theo
            this.selectedQuickAddStock = null;
            this.quickAddSearch = '';
            this.quickAddHighlight = 0;
            this.showQuickAddDropdown = false;
            this.$nextTick(() => {
                if (this.$refs.quickAddInput) {
                    this.$refs.quickAddInput.focus();
                }
            });
        },

        removeItem(idx) {
            this.saleForm.items.splice(idx, 1);
        },

        normalizeVnText(str) {
            if (!str) return '';
            return str.toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'd');
        },

        onDealerChange() {
            const dlId = this.saleForm.dealer_id;
            const dealer = (this.dealersList || []).find(d => String(d.id) === String(dlId));
            if (dealer) {
                this.saleForm.customer_name = dealer.name;
                this.saleForm.customer_phone = dealer.phone || '';
                this.saleForm.customer_address = dealer.address || '';
            }
        },

        getTotalQuantity() {
            return (this.saleForm.items || []).reduce((sum, it) => sum + Number(it.quantity || 0), 0);
        },

        getTotalAmount() {
            return (this.saleForm.items || []).reduce((sum, it) => sum + (Number(it.quantity || 0) * Number(it.unit_price || 0)), 0);
        },

        formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0));
        },

        async submitOrder() {
            if (this.saleForm.customer_type === 'dealer' && !this.saleForm.dealer_id) {
                alert('Vui lòng chọn đại lý nhận hàng!');
                return;
            }
            if (!this.saleForm.customer_name || !this.saleForm.customer_name.trim()) {
                alert('Vui lòng nhập tên người nhận / khách hàng!');
                return;
            }

            const validItems = (this.saleForm.items || [])
                .filter(i => i.product_id && Number(i.quantity) > 0);

            if (validItems.length === 0) {
                alert('Vui lòng chọn ít nhất 1 sản phẩm bán có số lượng > 0!');
                return;
            }

            for (let it of validItems) {
                if (Number(it.quantity) > Number(it.available_stock || 0)) {
                    alert(`Sản phẩm [${it.sku}] ${it.name} chỉ còn tồn ${it.available_stock} trong kho ôm. Không thể bán ${it.quantity}!`);
                    return;
                }
            }

            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/orders/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        warehouse: this.saleForm.warehouse_name,
                        is_holding_stock: true,
                        is_holding_stock_sale: true,
                        status: 'Đã giao hàng',
                        dealerId: this.saleForm.customer_type === 'dealer' ? this.saleForm.dealer_id : null,
                        dealerName: this.saleForm.customer_name,
                        customerName: this.saleForm.customer_name,
                        customerPhone: this.saleForm.customer_phone,
                        customerAddress: this.saleForm.customer_address,
                        deliveryAddress: this.saleForm.customer_address,
                        paymentMethod: this.saleForm.payment_method,
                        paymentStatus: this.saleForm.payment_method === 'Công nợ (Ghi nợ đại lý)' ? 'Chưa thanh toán' : 'Đã thanh toán',
                        notes: '',
                        items: validItems.map(it => ({
                            product_id: parseInt(it.product_id, 10),
                            name: it.name,
                            sku: it.sku,
                            unit: it.unit,
                            quantity: Number(it.quantity),
                            unitPrice: Number(it.unit_price || 0),
                            unit_price: Number(it.unit_price || 0),
                            discount: 0,
                            amount: Number(it.quantity) * Number(it.unit_price || 0),
                            isChecked: true,
                            isPicked: true
                        }))
                    })
                });

                const data = await res.json();
                if (data.success || data.order_id || data.id) {
                    alert(data.message || 'Tạo đơn bán hàng từ kho hàng ôm thành công! Tồn kho đã được trừ trực tiếp.');
                    window.location.href = '/inventory/import?tab=stocks';
                } else {
                    alert('Lỗi tạo đơn bán hàng: ' + (data.message || 'Không thể lưu đơn'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}
</script>
@endsection
