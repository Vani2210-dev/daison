@extends('layouts.app')

@section('title', 'Tạo Đơn Đặt Hàng Nhà Cung Cấp Mới | ERP Đại Sơn')

@section('content')
<style>
/* Tắt hoàn toàn spinner mũi tên tăng giảm và autofill tooltip của trình duyệt */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>
<div class="space-y-4 font-sans text-slate-800 antialiased w-full pb-20" x-data="supplierOrderCreateApp()" x-init="init()" @keydown.window="handleGlobalKeydown($event)">
    
    <!-- 1. Sub-Section Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto">
        <a href="{{ url('/orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <span>Đơn hàng</span>
        </a>

        <a href="{{ url('/orders?tab=consignment') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-boxes-stacked text-sm"></i>
            <span>Đơn hàng ôm</span>
        </a>

        <a href="{{ url('/orders?tab=returns') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-rotate-left text-sm"></i>
            <span>Khách trả hàng</span>
        </a>

        <a href="{{ url('/supplier-orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-sky-600 text-white shadow-md shadow-sky-600/20">
            <i class="fa-solid fa-cart-shopping text-sm"></i>
            <span>Đặt hàng NCC</span>
        </a>
    </div>

    <!-- 2. Header Banner (Không dùng modal, trang riêng biệt) -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/supplier-orders') }}" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center shrink-0 border border-slate-300 cursor-pointer transition-transform hover:scale-105" title="Quay lại danh sách Đặt hàng NCC">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Tạo Đơn Đặt Hàng Nhà Cung Cấp Mới</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800 text-[11px] font-extrabold border border-sky-200">Form Lập Đơn NCC</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Đặt hàng Nhà máy Tiền Phong / NCC · Chọn trạng thái thanh toán · Cấn trừ công nợ trực tiếp</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="showShortcutsModal = true" class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Xem danh sách phím tắt (F1 hoặc ?)">
                <i class="fa-regular fa-keyboard text-amber-600"></i>
                <span>Phím tắt <kbd class="px-1.5 py-0.5 bg-amber-100/80 border border-amber-300 rounded text-[10px] text-amber-900 font-mono">F1</kbd></span>
            </button>

            <a href="{{ url('/supplier-orders') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-xmark text-slate-400"></i>
                <span>Hủy bỏ</span>
            </a>

            <button type="button" @click="submitCreateOrder()" :disabled="saving" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black text-xs flex items-center gap-2 shadow-md shadow-sky-600/20 cursor-pointer disabled:opacity-50" title="Lưu đơn hàng (F9 hoặc Ctrl+S)">
                <i class="fa-solid fa-spinner animate-spin" x-show="saving"></i>
                <i class="fa-solid fa-check" x-show="!saving"></i>
                <span>Tạo Đơn Đặt Hàng <kbd class="px-1.5 py-0.5 bg-sky-700/80 border border-sky-500 rounded text-[10px] text-white font-mono">F9</kbd></span>
            </button>
        </div>
    </div>

    <!-- 3. Form Body Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-7 space-y-6">
        
        <!-- Hàng 1: Thông tin chung (NCC, Kho, Ngày đặt, Hẹn giao) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Nhà Cung Cấp -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Nhà Cung Cấp <span class="text-rose-500">*</span>
                </label>
                <select x-model="formData.supplier_id" @change="onSupplierChange()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold bg-white text-slate-800 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none">
                    <option value="">-- Chọn Nhà Cung Cấp --</option>
                    <template x-for="s in suppliers" :key="s.id">
                        <option :value="s.id" :selected="formData.supplier_id == s.id" x-text="s.name + (s.supplier_debt_to_company > 0 ? ' [Đang nợ: ' + formatMoney(s.supplier_debt_to_company) + ']' : '')"></option>
                    </template>
                </select>
            </div>

            <!-- Kho nhập hàng dự kiến -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kho Nhập Hàng Dự Kiến</label>
                <select x-model="formData.warehouse_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold bg-white text-slate-800 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none">
                    <option value="">-- Chọn kho nhập hàng --</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Ngày đặt -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Ngày đặt hàng <span class="text-rose-500">*</span>
                </label>
                <input type="date" x-model="formData.order_date" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:border-sky-500 outline-none">
            </div>

            <!-- Hẹn giao -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Ngày hẹn giao</label>
                <input type="date" x-model="formData.expected_delivery_date" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:border-sky-500 outline-none">
            </div>
        </div>

        <!-- KHUNG ĐẶC BIỆT: HIỂN THỊ CÔNG NỢ NCC ĐANG NỢ & TÙY CHỌN TRỪ NỢ -->
        <template x-if="selectedSupplierDebtBalance > 0">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-300 rounded-2xl p-4 sm:p-5 shadow-sm space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start sm:items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-amber-200 text-amber-800 flex items-center justify-center text-xl shrink-0 shadow-xs">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <div class="text-sm font-black text-amber-950">
                                Nhà Cung Cấp Này Đang Nợ Công Ty: <span class="text-base font-black text-amber-950 underline" x-text="formatMoney(selectedSupplierDebtBalance)"></span>
                            </div>
                            <p class="text-xs text-amber-800 mt-0.5">
                                Khoản tiền thanh toán dư từ các đơn hàng trước nhận thiếu hàng (cơ chế NCC nợ tiền).
                            </p>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-xl border border-amber-300 shadow-2xs hover:bg-amber-50 transition-colors">
                        <input type="checkbox" x-model="formData.apply_debt_deduction" @change="onToggleApplyDebt()" class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500 cursor-pointer">
                        <span class="text-xs font-black text-slate-800 select-none">Trừ nợ vào đơn hàng này</span>
                    </label>
                </div>

                <!-- Input số tiền trừ nợ nếu người dùng chọn trừ -->
                <div x-show="formData.apply_debt_deduction" class="pt-3 border-t border-amber-200/80 flex flex-wrap items-center gap-3">
                    <div class="text-xs font-bold text-amber-900">Số tiền muốn cấn trừ:</div>
                    <div class="relative flex-1 max-w-xs">
                        <input type="number" 
                               autocomplete="off"
                               autocorrect="off"
                               spellcheck="false"
                               data-lpignore="true"
                               x-model="formData.debt_deducted_amount" 
                               @input="recalcTotals()" 
                               min="0" 
                               :max="selectedSupplierDebtBalance" 
                               class="w-full px-3.5 py-2 rounded-xl border border-amber-300 text-xs font-black text-slate-800 bg-white focus:border-sky-500 outline-none">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[11px] text-slate-400 font-bold">VNĐ</span>
                    </div>
                    <button type="button" @click="applyMaxDebtDeduction()" class="px-3 py-1.5 bg-amber-200 hover:bg-amber-300 text-amber-900 rounded-xl text-xs font-black cursor-pointer transition-colors shadow-2xs">
                        Trừ tối đa
                    </button>
                    <div class="text-xs text-amber-700 italic">
                        (Tối đa: <span class="font-bold" x-text="formatMoney(Math.min(selectedSupplierDebtBalance, calcOrderTotal()))"></span>)
                    </div>
                </div>
            </div>
        </template>

        <!-- Khung cảnh báo nếu NCC còn các mặt hàng nợ chưa giao bù -->
        <template x-if="owingItemsList.length > 0">
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs space-y-2">
                <div class="font-bold text-blue-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing text-blue-600 text-sm"></i>
                    <span>Nhà cung cấp này đang nợ giao bù <span class="font-black text-blue-950" x-text="owingItemsList.length"></span> mặt hàng từ các đơn trước:</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-1">
                    <template x-for="item in owingItemsList" :key="item.order_code + item.product_name">
                        <div class="flex items-center justify-between text-xs bg-white px-3 py-2 rounded-xl border border-blue-100 text-slate-700 shadow-2xs">
                            <div>
                                <span class="font-mono font-bold text-sky-700" x-text="item.order_code"></span>:
                                <span class="font-semibold text-slate-900" x-text="item.product_name"></span>
                            </div>
                            <div class="font-black text-amber-700">
                                Còn nợ: <span x-text="item.remaining_quantity + ' ' + item.unit"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Hàng 2: DANH SÁCH SẢN PHẨM CẦN ĐẶT HÀNG -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                        Danh Sách Sản Phẩm Cần Đặt Hàng <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400 font-normal">(Gợi ý & phím tắt: &uarr; &darr; &bull; Enter &bull; F8 thêm dòng &bull; F2 tìm SP)</span>
                </div>
                <button type="button" @click="addItemRow()" class="px-3.5 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-2xs transition-colors" title="Thêm dòng mới (F8 hoặc Ctrl+Enter)">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Thêm sản phẩm <kbd class="px-1.5 py-0.5 bg-sky-100 border border-sky-300 rounded text-[10px] text-sky-800 font-mono">F8</kbd></span>
                </button>
            </div>

            <div class="border border-slate-200 rounded-2xl overflow-visible shadow-2xs">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black text-[10.5px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-3.5 min-w-[320px]">Sản phẩm / Mặt hàng (gõ mã, tên để tìm kiếm)</th>
                            <th class="py-3 px-2 w-20 text-center">ĐVT</th>
                            <th class="py-3 px-2 w-36 text-center">Số lượng đặt</th>
                            <th class="py-3 px-2 w-36 text-right">Đơn giá nhập</th>
                            <th class="py-3 px-2 w-24 text-right">CK (%)</th>
                            <th class="py-3 px-3 w-36 text-right">Thành tiền</th>
                            <th class="py-3 px-2 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(it, idx) in formData.items" :key="idx">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Chọn SP kiểu autocomplete như bên bán hàng -->
                                <td class="py-2.5 px-3.5 align-top">
                                    <div class="relative" @click.outside="if (activeProdDropdownIndex === idx) activeProdDropdownIndex = null">
                                        <div class="relative">
                                            <input type="text"
                                                   autocomplete="off"
                                                   autocorrect="off"
                                                   spellcheck="false"
                                                   :id="'row-product-input-' + idx"
                                                   x-model="it.searchQuery" 
                                                   @focus="activeProdDropdownIndex = idx; prodHighlightIndex = 0"
                                                   @input="activeProdDropdownIndex = idx; prodHighlightIndex = 0; onSearchInput(idx)"
                                                   @keydown.down.prevent="
                                                       activeProdDropdownIndex = idx;
                                                       const prods = getFilteredProducts(it.searchQuery, idx);
                                                       prodHighlightIndex = Math.min(prodHighlightIndex + 1, prods.length - 1);
                                                       scrollProdIntoView(idx, prodHighlightIndex);
                                                   "
                                                   @keydown.up.prevent="
                                                       prodHighlightIndex = Math.max(prodHighlightIndex - 1, 0);
                                                       scrollProdIntoView(idx, prodHighlightIndex);
                                                   "
                                                   @keydown.enter.prevent="
                                                       if (activeProdDropdownIndex === idx) {
                                                           const prods = getFilteredProducts(it.searchQuery, idx);
                                                           if (prods.length > 0 && prods[prodHighlightIndex]) {
                                                               selectProductForRow(idx, prods[prodHighlightIndex]);
                                                           } else {
                                                               activeProdDropdownIndex = null;
                                                               focusQtyInput(idx);
                                                           }
                                                       } else {
                                                           focusQtyInput(idx);
                                                       }
                                                   "
                                                   @keydown.escape.prevent="activeProdDropdownIndex = null"
                                                   placeholder="Tìm mã hoặc tên sản phẩm (phím ↑ ↓ Enter hoặc gõ tự do)..." 
                                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors pr-7">

                                            <button type="button" 
                                                    x-show="it.searchQuery" 
                                                    @click="clearProductRow(idx)" 
                                                    class="absolute right-2.5 top-2.5 text-slate-400 hover:text-rose-500 text-xs cursor-pointer transition-colors"
                                                    title="Xóa để chọn lại">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                            </button>
                                        </div>

                                        <!-- Menu Dropdown gợi ý sản phẩm -->
                                        <div x-show="activeProdDropdownIndex === idx && getFilteredProducts(it.searchQuery, idx).length > 0" 
                                             x-cloak 
                                             class="absolute left-0 top-full mt-1.5 w-[520px] max-w-[90vw] z-50 bg-white rounded-2xl shadow-2xl border border-slate-200 max-h-72 overflow-y-auto py-1 text-xs">
                                            <div class="px-3.5 py-1.5 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                                <span>Gợi ý sản phẩm (<span x-text="getFilteredProducts(it.searchQuery, idx).length"></span>)</span>
                                                <span class="text-sky-600 font-semibold lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                            </div>
                                            <template x-for="(p, pIdx) in getFilteredProducts(it.searchQuery, idx)" :key="p.id">
                                                <div :id="'prod-opt-' + idx + '-' + pIdx"
                                                     @mousedown.prevent="selectProductForRow(idx, p)"
                                                     @click="selectProductForRow(idx, p)"
                                                     @mouseenter="prodHighlightIndex = pIdx"
                                                     class="px-3.5 py-2 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                                     :class="prodHighlightIndex === pIdx ? 'bg-sky-100 text-sky-950 font-bold ring-1 ring-sky-300' : 'hover:bg-slate-50'">
                                                    <div class="min-w-0 pr-3">
                                                        <div class="font-bold text-slate-900 truncate flex items-center gap-1.5">
                                                            <span class="text-sky-600 font-mono text-xs" x-text="'[' + p.sku + ']'"></span>
                                                            <span x-text="p.name"></span>
                                                            <span x-show="p.brand_name" class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 text-[10px] font-normal" x-text="p.brand_name"></span>
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                            <span class="font-semibold text-slate-700" x-text="'ĐVT: ' + (p.unit || 'cái')"></span>
                                                            <span>&bull;</span>
                                                            <span class="text-emerald-600 font-medium" x-text="'Tồn kho: ' + formatNumber(p.stock_quantity) + ' ' + (p.unit || '')"></span>
                                                        </div>
                                                    </div>
                                                    <div class="shrink-0 text-right">
                                                        <div class="font-black text-sky-700 text-xs" x-text="formatMoney(p.cost_price)"></div>
                                                        <div class="text-[10px] text-slate-400">giá nhập tham khảo</div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Tag thông tin phụ dưới ô tìm kiếm -->
                                        <div x-show="it.product_id" x-cloak class="mt-1 flex items-center gap-2 text-[11px]">
                                            <span class="font-mono text-sky-700 font-bold" x-text="'[' + it.sku + ']'"></span>
                                            <span class="text-slate-300">&bull;</span>
                                            <span class="text-slate-500 font-medium" x-text="'Tồn kho hiện tại: ' + formatNumber(it.stock_quantity || 0) + ' ' + (it.unit || '')"></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- ĐVT -->
                                <td class="py-2.5 px-2 text-center align-top">
                                    <input type="text" 
                                           autocomplete="off"
                                           autocorrect="off"
                                           spellcheck="false"
                                           x-model="it.unit" 
                                           class="w-full px-2 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold outline-none focus:border-sky-500">
                                </td>

                                <!-- Số lượng đặt (hỗ trợ phím ↑ ↓ và Enter sang dòng mới) -->
                                <td class="py-2.5 px-2 align-top">
                                    <div class="flex items-center">
                                        <button type="button" @click="decreaseQty(idx)" class="w-7 h-9 flex items-center justify-center bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 rounded-l-xl border border-r-0 border-slate-300 font-black text-xs select-none cursor-pointer shrink-0" title="Giảm (Phím ↓)">-</button>
                                        <input type="number" 
                                               :id="'row-qty-input-' + idx"
                                               autocomplete="off"
                                               autocorrect="off"
                                               spellcheck="false"
                                               data-lpignore="true"
                                               x-model="it.ordered_quantity" 
                                               @input="recalcTotals()" 
                                               @keydown.up.prevent="increaseQty(idx)"
                                               @keydown.down.prevent="decreaseQty(idx)"
                                               @keydown.enter.prevent="onEnterQty(idx)"
                                               min="0.01" step="any" 
                                               class="w-full h-9 px-2 border-y border-slate-300 text-xs font-black text-center outline-none focus:border-sky-500 focus:bg-sky-50/50">
                                        <button type="button" @click="increaseQty(idx)" class="w-7 h-9 flex items-center justify-center bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 rounded-r-xl border border-l-0 border-slate-300 font-black text-xs select-none cursor-pointer shrink-0" title="Tăng (Phím ↑)">+</button>
                                    </div>
                                </td>

                                <!-- Đơn giá nhập (hỗ trợ phím ↑ ↓ tăng giảm 1.000đ) -->
                                <td class="py-2.5 px-2 text-right align-top">
                                    <input type="number" 
                                           :id="'row-price-input-' + idx"
                                           autocomplete="off"
                                           autocorrect="off"
                                           spellcheck="false"
                                           data-lpignore="true"
                                           x-model="it.unit_price" 
                                           @input="recalcTotals()" 
                                           @keydown.up.prevent="increasePrice(idx)"
                                           @keydown.down.prevent="decreasePrice(idx)"
                                           @keydown.enter.prevent="onEnterPrice(idx)"
                                           min="0" step="any" 
                                           class="w-full h-9 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-black text-right outline-none focus:border-sky-500 focus:bg-sky-50/50"
                                           title="Phím ↑ tăng 1.000đ, phím ↓ giảm 1.000đ">
                                </td>

                                <!-- Chiết khấu % -->
                                <td class="py-2.5 px-2 text-right align-top">
                                    <input type="number" 
                                           :id="'row-ck-input-' + idx"
                                           autocomplete="off"
                                           autocorrect="off"
                                           spellcheck="false"
                                           data-lpignore="true"
                                           x-model="it.discount_percent" 
                                           @input="recalcTotals()" 
                                           @keydown.enter.prevent="addNewRowFrom(idx)"
                                           min="0" max="100" 
                                           class="w-full h-9 px-2 py-1.5 rounded-xl border border-slate-300 text-xs text-right font-bold outline-none focus:border-sky-500">
                                </td>

                                <!-- Thành tiền -->
                                <td class="py-2.5 px-3 text-right font-black text-slate-900 text-xs align-top pt-3.5" x-text="formatMoney(calcItemAmount(it))">
                                </td>

                                <!-- Xóa dòng -->
                                <td class="py-2.5 px-2 text-center align-top pt-3">
                                    <button type="button" @click="removeItemRow(idx)" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center cursor-pointer transition-colors" title="Xóa dòng này" x-show="formData.items.length > 1">
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hàng 3: THANH TOÁN & TỔNG KẾT TÀI CHÍNH -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-3 border-t border-slate-200">
            
            <!-- Cột Trái: CHỌN TRẠNG THÁI THANH TOÁN -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3.5">
                <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                    Chọn Trạng Thái Thanh Toán <span class="text-rose-500">*</span>
                </label>

                <!-- 3 Radio buttons -->
                <div class="space-y-2.5">
                    <!-- Option 1: Chưa thanh toán -->
                    <label class="flex items-center gap-3 p-3 rounded-xl border bg-white cursor-pointer transition-all hover:border-slate-300"
                           :class="formData.payment_status === 'Chưa thanh toán' ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/30' : 'border-slate-200'">
                        <input type="radio" name="payment_status" value="Chưa thanh toán" x-model="formData.payment_status" @change="onPaymentStatusChange()" class="w-4 h-4 text-sky-600">
                        <div>
                            <div class="text-xs font-black text-slate-800">Chưa thanh toán</div>
                            <div class="text-[11px] text-slate-500">Đặt hàng trước, chưa thanh toán tiền cho NCC</div>
                        </div>
                    </label>

                    <!-- Option 2: Thanh toán 1 phần -->
                    <label class="flex items-center gap-3 p-3 rounded-xl border bg-white cursor-pointer transition-all hover:border-slate-300"
                           :class="formData.payment_status === 'Thanh toán 1 phần' ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/30' : 'border-slate-200'">
                        <input type="radio" name="payment_status" value="Thanh toán 1 phần" x-model="formData.payment_status" @change="onPaymentStatusChange()" class="w-4 h-4 text-sky-600">
                        <div>
                            <div class="text-xs font-black text-slate-800">Thanh toán 1 phần (Đặt cọc)</div>
                            <div class="text-[11px] text-slate-500">Đã thanh toán trước một phần tiền cho NCC</div>
                        </div>
                    </label>

                    <!-- Option 3: Đã thanh toán -->
                    <label class="flex items-center gap-3 p-3 rounded-xl border bg-white cursor-pointer transition-all hover:border-slate-300"
                           :class="formData.payment_status === 'Đã thanh toán' ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/30' : 'border-slate-200'">
                        <input type="radio" name="payment_status" value="Đã thanh toán" x-model="formData.payment_status" @change="onPaymentStatusChange()" class="w-4 h-4 text-sky-600">
                        <div>
                            <div class="text-xs font-black text-slate-800">Đã thanh toán (100%)</div>
                            <div class="text-[11px] text-slate-500">Đã thanh toán đủ toàn bộ số tiền cần trả</div>
                        </div>
                    </label>
                </div>

                <!-- Input số tiền đã thanh toán nếu chọn Thanh toán 1 phần -->
                <div x-show="formData.payment_status === 'Thanh toán 1 phần'" class="pt-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Số tiền đã thanh toán đợt này: <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               autocomplete="off"
                               autocorrect="off"
                               spellcheck="false"
                               data-lpignore="true"
                               x-model="formData.paid_amount" 
                               min="0" 
                               :max="calcPayableTotal()" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 text-xs font-black text-slate-800 focus:border-sky-500 outline-none">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[11px] text-slate-400 font-bold">VNĐ</span>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Phương thức</label>
                        <select x-model="formData.payment_method" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                            <option value="Chuyển khoản">Chuyển khoản</option>
                            <option value="Tiền mặt">Tiền mặt</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Ghi chú thanh toán</label>
                        <input type="text" 
                               autocomplete="off"
                               autocorrect="off"
                               spellcheck="false"
                               x-model="formData.payment_note" 
                               placeholder="Số ủy nhiệm chi, mã GD..." 
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>
            </div>

            <!-- Cột Phải: BẢNG TÍNH TIỀN & TRỪ CÔNG NỢ NCC -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-2.5 text-xs">
                <div class="font-black text-slate-800 uppercase tracking-wider mb-2">Tóm Tắt Thanh Toán</div>

                <div class="flex items-center justify-between text-slate-600 py-1.5 border-b border-slate-200/60">
                    <span>Tổng tiền hàng:</span>
                    <span class="font-bold text-slate-800 text-xs" x-text="formatMoney(calcItemsTotal())">0 đ</span>
                </div>

                <div class="flex items-center justify-between text-slate-600 py-1.5 border-b border-slate-200/60">
                    <span>Chiết khấu đơn:</span>
                    <input type="number" 
                           autocomplete="off"
                           autocorrect="off"
                           spellcheck="false"
                           data-lpignore="true"
                           x-model="formData.discount_amount" 
                           @input="recalcTotals()" 
                           min="0" 
                           class="w-32 px-2.5 py-1 rounded-lg border border-slate-300 text-xs text-right font-bold">
                </div>

                <div class="flex items-center justify-between text-slate-600 py-1.5 border-b border-slate-200/60">
                    <span>Phí vận chuyển / Chi phí khác:</span>
                    <input type="number" 
                           autocomplete="off"
                           autocorrect="off"
                           spellcheck="false"
                           data-lpignore="true"
                           x-model="formData.other_fees" 
                           @input="recalcTotals()" 
                           min="0" 
                           class="w-32 px-2.5 py-1 rounded-lg border border-slate-300 text-xs text-right font-bold">
                </div>

                <div class="flex items-center justify-between text-slate-700 py-2 border-b border-slate-200 font-bold">
                    <span>Tổng giá trị đơn hàng:</span>
                    <span class="font-black text-slate-900 text-sm" x-text="formatMoney(calcOrderTotal())">0 đ</span>
                </div>

                <!-- Dòng trừ nợ NCC (Nổi bật) -->
                <template x-if="formData.debt_deducted_amount > 0">
                    <div class="flex items-center justify-between text-amber-800 bg-amber-100/60 px-3 py-2 rounded-xl border border-amber-200 font-bold">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-hand-holding-dollar text-amber-600"></i>
                            <span>Trừ công nợ NCC nợ:</span>
                        </span>
                        <span class="font-black text-amber-900 text-xs">-<span x-text="formatMoney(formData.debt_deducted_amount)"></span></span>
                    </div>
                </template>

                <!-- Số tiền cần thanh toán thực tế -->
                <div class="flex items-center justify-between py-2.5 border-t-2 border-slate-300">
                    <span class="font-black text-slate-900 text-xs">SỐ TIỀN CẦN THANH TOÁN:</span>
                    <span class="font-black text-sky-700 text-lg" x-text="formatMoney(calcPayableTotal())">0 đ</span>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-500">
                    <span>Đã thanh toán đợt này:</span>
                    <span class="font-black text-emerald-700 text-xs" x-text="formatMoney(calcActualPaidAmount())">0 đ</span>
                </div>
            </div>
        </div>

        <!-- Ghi chú chung -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Ghi chú đơn đặt hàng</label>
            <textarea x-model="formData.notes" rows="3" placeholder="Ghi chú quy cách, thời gian giao hàng, yêu cầu đóng gói..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-300 text-xs outline-none focus:border-sky-500"></textarea>
        </div>

        <!-- Action Bottom Bar -->
        <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            @if(Auth::check() && Auth::user()->hasPermission('supplier_orders.approve'))
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-600 select-none">
                    <input type="checkbox" x-model="formData.auto_approve" class="w-4 h-4 text-sky-600 rounded border-slate-300">
                    <span>Duyệt đơn ngay sau khi tạo</span>
                </label>
            @else
                <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-bell text-amber-500"></i>
                    <span>Sau khi tạo, hệ thống sẽ gửi thông báo tự động tới người có quyền duyệt đơn.</span>
                </div>
            @endif

            <div class="flex items-center gap-2.5">
                <a href="{{ url('/supplier-orders') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer shadow-2xs">
                    Hủy bỏ
                </a>
                <button type="button" @click="submitCreateOrder()" :disabled="saving" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black text-xs flex items-center gap-2 shadow-md shadow-sky-600/20 cursor-pointer disabled:opacity-50 hover:scale-[1.02] transition-all">
                    <i class="fa-solid fa-spinner animate-spin" x-show="saving"></i>
                    <i class="fa-solid fa-check" x-show="!saving"></i>
                    <span>Tạo Đơn Đặt Hàng NCC</span>
                </button>
            </div>
        </div>

    </div>

    <!-- MODAL DANH SÁCH PHÍM TẮT (F1 HOẶC ?) -->
    <div x-show="showShortcutsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click.self="showShortcutsModal = false">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150">
            <div class="px-5 py-4 bg-gradient-to-r from-sky-600 to-blue-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-keyboard text-lg"></i>
                    <h3 class="font-black text-sm">Danh Sách Phím Tắt - Đặt Hàng NCC</h3>
                </div>
                <button type="button" @click="showShortcutsModal = false" class="text-white/80 hover:text-white cursor-pointer transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                <div>
                    <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-2.5">Thao tác tạo đơn nhanh</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Thêm dòng sản phẩm mới</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F8</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">Ctrl + Enter</kbd>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Lưu / Hoàn tất đơn đặt hàng</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-emerald-700 shadow-2xs">F9</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">Ctrl + S</kbd>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Nhảy vào ô tìm sản phẩm</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F2</kbd>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Bật / Tắt bảng phím tắt này</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-amber-700 shadow-2xs">F1</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">?</kbd>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-2.5">Thao tác trong từng dòng sản phẩm</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Di chuyển lên/xuống danh sách gợi ý SP</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">&uarr;</kbd>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">&darr;</kbd>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Chọn SP đang chọn & nhảy sang ô Số lượng</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">Enter</kbd>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Tăng / Giảm số lượng (+1, -1)</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">&uarr;</kbd>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">&darr;</kbd>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Tăng / Giảm đơn giá (+1.000, -1.000đ)</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">&uarr;</kbd>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">&darr;</kbd>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Nhảy sang dòng mới từ ô Số lượng / Đơn giá</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">Enter</kbd>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Đóng gợi ý / Thoát ô nhập</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">Esc</kbd>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" @click="showShortcutsModal = false" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl transition-colors cursor-pointer text-xs">
                    Đã hiểu
                </button>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT CHO TRANG TẠO ĐƠN NCC -->
<script>
function supplierOrderCreateApp() {
    return {
        saving: false,
        showShortcutsModal: false,
        activeProdDropdownIndex: null,
        prodHighlightIndex: 0,

        suppliers: @json($suppliers ?? []),
        warehouses: @json($warehouses ?? []),
        catalogProducts: @json($products ?? []),

        selectedSupplierDebtBalance: 0,
        owingItemsList: [],

        formData: {
            supplier_id: '{{ $selectedSupplierId ?? "" }}',
            warehouse_id: '{{ $warehouses[0]->id ?? "" }}',
            order_date: new Date().toISOString().slice(0, 10),
            expected_delivery_date: '',
            payment_status: 'Chưa thanh toán',
            paid_amount: 0,
            payment_method: 'Chuyển khoản',
            payment_note: '',
            apply_debt_deduction: false,
            debt_deducted_amount: 0,
            discount_amount: 0,
            other_fees: 0,
            notes: '',
            auto_approve: false,
            items: [
                { product_id: '', product_name: '', sku: '', searchQuery: '', unit: 'cái', stock_quantity: 0, ordered_quantity: 1, unit_price: 0, discount_percent: 0, discount_amount: 0 }
            ]
        },

        init() {
            if (this.formData.supplier_id) {
                this.onSupplierChange();
            }
            this.$nextTick(() => {
                const firstInput = document.getElementById('row-product-input-0');
                if (firstInput) firstInput.focus();
            });
        },

        onSupplierChange() {
            const supId = this.formData.supplier_id;
            this.selectedSupplierDebtBalance = 0;
            this.owingItemsList = [];
            this.formData.apply_debt_deduction = false;
            this.formData.debt_deducted_amount = 0;

            if (!supId) return;

            fetch(`{{ url('/api/suppliers') }}/${supId}/info`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.selectedSupplierDebtBalance = parseFloat(data.debt_balance) || 0;
                        this.owingItemsList = data.owing_items || [];
                        if (this.selectedSupplierDebtBalance > 0) {
                            this.formData.apply_debt_deduction = true;
                            this.applyMaxDebtDeduction();
                        }
                    }
                })
                .catch(e => console.error(e));
        },

        onToggleApplyDebt() {
            if (this.formData.apply_debt_deduction) {
                this.applyMaxDebtDeduction();
            } else {
                this.formData.debt_deducted_amount = 0;
                this.recalcTotals();
            }
        },

        applyMaxDebtDeduction() {
            const maxDeduct = Math.min(this.selectedSupplierDebtBalance, this.calcOrderTotal());
            this.formData.debt_deducted_amount = maxDeduct;
            this.recalcTotals();
        },

        // Gợi ý sản phẩm lọc theo search query
        getFilteredProducts(query, currentIndex = -1) {
            const selectedIds = new Set();
            (this.formData.items || []).forEach((it, idx) => {
                if (idx !== currentIndex && it.product_id) {
                    selectedIds.add(String(it.product_id));
                }
            });

            const list = (this.catalogProducts || []).filter(p => !selectedIds.has(String(p.id)));
            if (!query || !query.trim()) {
                return list.slice(0, 35);
            }
            const q = query.toLowerCase().trim();
            return list.filter(p => {
                const sku = (p.sku || '').toLowerCase();
                const name = (p.name || '').toLowerCase();
                const brand = (p.brand_name || '').toLowerCase();
                return sku.includes(q) || name.includes(q) || brand.includes(q);
            }).slice(0, 35);
        },

        scrollProdIntoView(index, pIdx) {
            this.$nextTick(() => {
                const el = document.getElementById('prod-opt-' + index + '-' + pIdx);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        selectProductForRow(index, p) {
            if (!p) return;
            const it = this.formData.items[index];
            if (!it) return;

            it.product_id = p.id;
            it.sku = p.sku;
            it.product_name = p.name;
            it.searchQuery = `[${p.sku}] ${p.name}`;
            it.unit = p.unit || 'cái';
            it.stock_quantity = p.stock_quantity || 0;
            it.unit_price = p.cost_price || 0;
            it.ordered_quantity = (parseFloat(it.ordered_quantity) > 0) ? it.ordered_quantity : 1;

            this.activeProdDropdownIndex = null;
            this.recalcTotals();

            // Tự động focus sang ô số lượng và bôi đen toàn bộ để nhập nhanh
            this.$nextTick(() => {
                const q = document.getElementById('row-qty-input-' + index);
                if (q) {
                    q.focus();
                    q.select();
                }
            });
        },

        clearProductRow(index) {
            const it = this.formData.items[index];
            if (!it) return;
            it.product_id = '';
            it.sku = '';
            it.product_name = '';
            it.searchQuery = '';
            it.stock_quantity = 0;
            it.unit_price = 0;
            this.activeProdDropdownIndex = index;
            this.prodHighlightIndex = 0;
            this.$nextTick(() => {
                const inp = document.getElementById('row-product-input-' + index);
                if (inp) inp.focus();
            });
        },

        onSearchInput(index) {
            const it = this.formData.items[index];
            if (!it) return;
            it.product_name = it.searchQuery;
            if (it.sku && !it.searchQuery.includes(it.sku)) {
                it.product_id = '';
                it.sku = '';
            }
        },

        focusQtyInput(index) {
            this.$nextTick(() => {
                const q = document.getElementById('row-qty-input-' + index);
                if (q) {
                    q.focus();
                    q.select();
                }
            });
        },

        increaseQty(idx) {
            const it = this.formData.items[idx];
            if (!it) return;
            it.ordered_quantity = (parseFloat(it.ordered_quantity) || 0) + 1;
            this.recalcTotals();
        },

        decreaseQty(idx) {
            const it = this.formData.items[idx];
            if (!it) return;
            it.ordered_quantity = Math.max(1, (parseFloat(it.ordered_quantity) || 1) - 1);
            this.recalcTotals();
        },

        increasePrice(idx) {
            const it = this.formData.items[idx];
            if (!it) return;
            it.unit_price = (parseFloat(it.unit_price) || 0) + 1000;
            this.recalcTotals();
        },

        decreasePrice(idx) {
            const it = this.formData.items[idx];
            if (!it) return;
            it.unit_price = Math.max(0, (parseFloat(it.unit_price) || 0) - 1000);
            this.recalcTotals();
        },

        onEnterQty(idx) {
            const it = this.formData.items[idx];
            if (it && (!it.unit_price || it.unit_price <= 0)) {
                const pInp = document.getElementById('row-price-input-' + idx);
                if (pInp) {
                    pInp.focus();
                    pInp.select();
                    return;
                }
            }
            this.addNewRowFrom(idx);
        },

        onEnterPrice(idx) {
            this.addNewRowFrom(idx);
        },

        addNewRowFrom(idx) {
            if (idx >= this.formData.items.length - 1) {
                this.addItemRow();
            } else {
                const nextInput = document.getElementById('row-product-input-' + (idx + 1));
                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                }
            }
        },

        addItemRow() {
            this.formData.items.push({
                product_id: '',
                product_name: '',
                sku: '',
                searchQuery: '',
                unit: 'cái',
                stock_quantity: 0,
                ordered_quantity: 1,
                unit_price: 0,
                discount_percent: 0,
                discount_amount: 0
            });
            const newIdx = this.formData.items.length - 1;
            this.$nextTick(() => {
                const inp = document.getElementById('row-product-input-' + newIdx);
                if (inp) {
                    inp.focus();
                }
            });
        },

        removeItemRow(idx) {
            if (this.formData.items.length > 1) {
                this.formData.items.splice(idx, 1);
                this.recalcTotals();
            }
        },

        handleGlobalKeydown(e) {
            const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

            // F1 hoặc ?: Bật / Tắt modal phím tắt
            if (e.key === 'F1' || (e.key === '?' && !isInput)) {
                e.preventDefault();
                this.showShortcutsModal = !this.showShortcutsModal;
                return;
            }

            // F9 hoặc Ctrl + S: Lưu đơn đặt hàng NCC
            if (e.key === 'F9' || (e.ctrlKey && (e.key === 's' || e.key === 'S'))) {
                e.preventDefault();
                if (!this.saving) {
                    this.submitCreateOrder();
                }
                return;
            }

            // F8 hoặc Ctrl + Enter: Thêm dòng sản phẩm mới
            if (e.key === 'F8' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                this.activeProdDropdownIndex = null;
                this.addItemRow();
                return;
            }

            // F2: Nhảy vào ô tìm kiếm sản phẩm dòng đầu tiên hoặc dòng chưa có sản phẩm
            if (e.key === 'F2') {
                e.preventDefault();
                let targetIdx = this.formData.items.findIndex(it => !it.product_id);
                if (targetIdx === -1) targetIdx = 0;
                const inp = document.getElementById('row-product-input-' + targetIdx);
                if (inp) {
                    inp.focus();
                    inp.select();
                }
                return;
            }

            // Escape: Đóng modal phím tắt hoặc dropdown gợi ý hoặc blur input
            if (e.key === 'Escape') {
                if (this.showShortcutsModal) {
                    this.showShortcutsModal = false;
                    return;
                }
                if (this.activeProdDropdownIndex !== null) {
                    this.activeProdDropdownIndex = null;
                    return;
                }
                if (isInput) {
                    document.activeElement.blur();
                    return;
                }
            }
        },

        calcItemAmount(it) {
            const qty = parseFloat(it.ordered_quantity) || 0;
            const price = parseFloat(it.unit_price) || 0;
            const discPct = parseFloat(it.discount_percent) || 0;
            let discAmt = parseFloat(it.discount_amount) || 0;
            if (discPct > 0 && discAmt === 0) {
                discAmt = (qty * price) * (discPct / 100);
            }
            return Math.max(0, (qty * price) - discAmt);
        },

        calcItemsTotal() {
            return this.formData.items.reduce((sum, it) => sum + this.calcItemAmount(it), 0);
        },

        calcOrderTotal() {
            const itemsTot = this.calcItemsTotal();
            const disc = parseFloat(this.formData.discount_amount) || 0;
            const fees = parseFloat(this.formData.other_fees) || 0;
            return Math.max(0, itemsTot - disc + fees);
        },

        calcPayableTotal() {
            const total = this.calcOrderTotal();
            const deduct = parseFloat(this.formData.debt_deducted_amount) || 0;
            return Math.max(0, total - deduct);
        },

        calcActualPaidAmount() {
            if (this.formData.payment_status === 'Đã thanh toán') {
                return this.calcPayableTotal();
            } else if (this.formData.payment_status === 'Thanh toán 1 phần') {
                return parseFloat(this.formData.paid_amount) || 0;
            }
            return 0;
        },

        onPaymentStatusChange() {
            if (this.formData.payment_status === 'Đã thanh toán') {
                this.formData.paid_amount = this.calcPayableTotal();
            } else if (this.formData.payment_status === 'Chưa thanh toán') {
                this.formData.paid_amount = 0;
            }
        },

        recalcTotals() {
            if (this.formData.apply_debt_deduction) {
                const maxAllowed = Math.min(this.selectedSupplierDebtBalance, this.calcOrderTotal());
                if (this.formData.debt_deducted_amount > maxAllowed) {
                    this.formData.debt_deducted_amount = maxAllowed;
                }
            }
            if (this.formData.payment_status === 'Đã thanh toán') {
                this.formData.paid_amount = this.calcPayableTotal();
            }
        },

        submitCreateOrder() {
            if (!this.formData.supplier_id) {
                alert('Vui lòng chọn Nhà Cung Cấp');
                return;
            }
            const invalidItem = this.formData.items.find(it => !it.product_name || (parseFloat(it.ordered_quantity) || 0) <= 0);
            if (invalidItem) {
                alert('Vui lòng kiểm tra lại tên và số lượng sản phẩm');
                return;
            }

            this.saving = true;
            fetch('{{ url("/api/supplier-orders") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    try {
                        sessionStorage.setItem('pending_toast', JSON.stringify({
                            title: 'Đơn đặt NCC mới chờ duyệt: ' + (data.order?.code || ''),
                            message: `Đơn đặt hàng ${data.order?.code || ''} (NCC: ${data.order?.supplier_name || ''}) đã được gửi đến người có quyền duyệt.`,
                            type: 'supplier_order_approval',
                            action_url: '{{ url("/supplier-orders") }}',
                            data: {
                                supplier_name: data.order?.supplier_name,
                                order_code: data.order?.code,
                                payable_amount: data.order?.payable_amount
                            }
                        }));
                    } catch(e) {}

                    window.location.href = '{{ url("/supplier-orders") }}';
                } else {
                    alert(data.message || 'Lỗi tạo đơn đặt hàng');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối máy chủ');
            })
            .finally(() => {
                this.saving = false;
            });
        },

        formatNumber(val) {
            const n = parseFloat(val) || 0;
            return new Intl.NumberFormat('vi-VN').format(n);
        },

        formatMoney(amount) {
            const val = parseFloat(amount) || 0;
            return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
        }
    }
}
</script>
@endsection
