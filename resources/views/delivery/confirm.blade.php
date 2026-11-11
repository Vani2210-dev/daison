@extends('layouts.app')

@section('title', 'Xác Nhận Giao Đơn #' . ($order->order_code ?? '') . ' | Vận Tải ERP Đại Sơn')

@section('content')
@php
    $origTotal = (float)($order->total_amount ?? 0);
    $prevDebt = (float)($previousDebt ?? 0);
    $cLimit = (float)($creditLimit ?? 0);
    $custCode = $dealer?->code ?: ($order->dealer_id ? 'KH-' . $order->dealer_id : 'KH-LE');
    $rawOrderCust = $order->customer_name ?: '';
    $orderCustName = html_entity_decode(html_entity_decode($rawOrderCust, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $rawCustName = $dealer?->name ?: ($orderCustName ?: 'Khách hàng');
    $custName = html_entity_decode(html_entity_decode($rawCustName, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $canApprove = !empty($canApproveCredit);
    $initStatus = $order->credit_overrun_status ?? '';
    $initApprover = $order->credit_overrun_approved_name ?? '';
    $initApprovedAt = $order->credit_overrun_approved_at ? \Carbon\Carbon::parse($order->credit_overrun_approved_at)->format('H:i d/m/Y') : '';
    $initReason = $order->credit_overrun_reason ?? '';
    $displayStatus = in_array($order->status, ['Đã kiểm hàng', 'Đã kiểm', 'Sẵn sàng xuất xe', 'Kho đã xuất đủ', 'Đã bốc xong']) ? 'Đang vận chuyển' : $order->status;
@endphp

<style>
/* ========================================================================= */
/* MOBILE RESPONSIVE OPTIMIZATIONS (@media)                                 */
/* ========================================================================= */
@media (max-width: 768px) {
    /* Full width màn hình trên mobile */
    main {
        padding-left: 4px !important;
        padding-right: 4px !important;
        padding-top: 4px !important;
    }
    .confirm-container {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .confirm-container.space-y-5 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 10px !important;
    }

    /* Header Banner & Cards full width */
    .confirm-header-banner,
    .mobile-main-tabs,
    #holding-stock-section,
    .confirm-container .bg-white.rounded-3xl {
        border-radius: 16px !important;
        padding: 12px 10px !important;
        width: 100% !important;
    }
    .confirm-header-banner {
        gap: 10px !important;
    }
    .confirm-header-left {
        width: 100% !important;
    }
    .confirm-back-btn {
        padding: 7px 12px !important;
        font-size: 11.5px !important;
        border-radius: 10px !important;
        white-space: nowrap !important;
    }
    .confirm-header-actions {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 8px !important;
        width: 100% !important;
        padding-top: 8px !important;
        border-top: 1px dashed #e2e8f0 !important;
    }
    .confirm-header-actions > * {
        width: 100% !important;
        justify-content: center !important;
        text-align: center !important;
        padding: 8px 6px !important;
        font-size: 11.5px !important;
        min-height: 40px !important;
        border-radius: 10px !important;
        white-space: nowrap !important;
    }
    .confirm-header-actions > *:last-child:nth-child(odd) {
        grid-column: span 2 !important;
    }

    /* Main 2-Tab Navigation */
    .mobile-main-tabs {
        padding: 5px !important;
        border-radius: 16px !important;
    }
    .mobile-main-tabs .tab-btn-group {
        width: 100% !important;
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 6px !important;
    }
    .mobile-main-tabs button {
        padding: 8px 6px !important;
        font-size: 11.5px !important;
        font-weight: 800 !important;
        justify-content: center !important;
        align-items: center !important;
        text-align: center !important;
        min-height: 46px !important;
        border-radius: 12px !important;
        line-height: 1.25 !important;
    }
    .mobile-main-tabs button i {
        font-size: 13px !important;
        flex-shrink: 0 !important;
    }
    .mobile-main-tabs button span.font-mono {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    /* Payment mode 4 buttons (2x2 on mobile, 4-col on desktop) */
    .payment-mode-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 8px !important;
    }
    .payment-mode-grid button {
        padding: 10px 4px !important;
        min-height: 54px !important;
    }
    .payment-mode-grid button i {
        font-size: 18px !important;
    }
    .payment-mode-grid button span {
        font-size: 11px !important;
    }

    /* Inputs on mobile - 15px/16px to prevent iOS Safari auto-zoom */
    input[type="text"],
    input[type="number"],
    input[type="tel"],
    select,
    textarea {
        font-size: 15px !important;
    }


    /* Tab 2: Chọn kho nhân sự / xe - swipeable pill row on mobile */
    .holding-tabs-grid {
        display: flex !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        gap: 8px !important;
        padding-bottom: 6px !important;
        scrollbar-width: thin;
    }
    .holding-tabs-grid > button {
        flex: 0 0 200px !important;
        min-width: 200px !important;
        padding: 10px 12px !important;
        border-radius: 14px !important;
    }

    /* Tab 2: Product Table transformed into Mobile Cards */
    .mobile-product-table thead {
        display: none !important;
    }
    .mobile-product-table,
    .mobile-product-table tbody,
    .mobile-product-table tr {
        display: block !important;
        width: 100% !important;
    }
    .mobile-product-table tbody {
        padding: 8px !important;
    }
    .mobile-product-table tr {
        margin-bottom: 12px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        padding: 12px 14px !important;
        background: #ffffff !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
    }
    .mobile-product-table td {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 6px 0 !important;
        border: none !important;
        border-bottom: 1px dashed #f1f5f9 !important;
        font-size: 12px !important;
    }
    .mobile-product-table td.m-col-stt {
        display: none !important;
    }
    .mobile-product-table td.m-col-product {
        display: block !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding-bottom: 8px !important;
        margin-bottom: 6px !important;
    }
    .mobile-product-table td.m-col-actions {
        border-bottom: none !important;
        padding-top: 10px !important;
        margin-top: 4px !important;
        display: flex !important;
        justify-content: flex-end !important;
    }
    .mobile-product-table td.m-col-actions .flex {
        width: 100% !important;
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 8px !important;
    }
    .mobile-product-table td.m-col-actions button,
    .mobile-product-table td.m-col-actions a {
        width: 100% !important;
        justify-content: center !important;
        padding: 8px 12px !important;
        font-size: 12px !important;
        border-radius: 10px !important;
    }
    .mobile-product-table td::before {
        content: attr(data-label);
        font-weight: 700;
        font-size: 11px;
        color: #64748b;
        margin-right: 8px;
    }
    .mobile-product-table td.m-col-product::before,
    .mobile-product-table td.m-col-actions::before {
        display: none !important;
    }
    .mobile-product-table td.mobile-hidden {
        display: none !important;
    }
    .mobile-product-table tr.is-collapsed {
        padding: 10px 12px !important;
        margin-bottom: 8px !important;
    }
    .mobile-product-table tr.is-collapsed td.m-col-product {
        border-bottom: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Submit buttons on mobile */
    .mobile-submit-buttons .btn-submit-main {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
        font-size: 13px !important;
        min-height: 46px !important;
        white-space: nowrap !important;
    }
    .mobile-submit-buttons .btn-submit-fail {
        padding-top: 9px !important;
        padding-bottom: 9px !important;
        font-size: 12px !important;
        min-height: 38px !important;
        white-space: nowrap !important;
    }
}

@media (max-width: 480px) {
    .confirm-header-actions > * {
        font-size: 11px !important;
        padding: 7px 4px !important;
    }
}
</style>
<div x-data="deliveryConfirmPage()" x-init="initData()" class="space-y-2.5 sm:space-y-4 max-w-[1600px] mx-auto pb-24 font-sans text-slate-800 antialiased confirm-container" x-cloak>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3"
         class="fixed bottom-6 right-6 z-[9999] px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-bold border backdrop-blur-md"
         :class="toast.type === 'success' ? 'bg-emerald-600/95 text-white border-emerald-400' : 'bg-rose-600/95 text-white border-rose-400'">
        <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check text-lg' : 'fa-solid fa-triangle-exclamation text-lg'"></i>
        <span x-text="toast.message"></span>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. HEADER BANNER TRANG XÁC NHẬN GIAO ĐƠN (Tối ưu gọn gàng cho Mobile)       -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-5 border border-slate-200 shadow-2xs flex flex-col gap-2 sm:gap-3 confirm-header-banner">
        <!-- Hàng 1: Nút Quay Lại + Mã Đơn Hàng + Biển Số Xe + Trạng Thái -->
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <a href="{{ url('/delivery-execution') }}?vehicle={{ urlencode($vehiclePlate ?? '') }}"
                   class="h-8 px-2.5 sm:px-3 rounded-xl bg-slate-100 hover:bg-purple-50 text-slate-700 hover:text-purple-700 border border-slate-200 hover:border-purple-300 font-bold text-xs cursor-pointer transition-all shadow-2xs inline-flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                    <i class="fa-solid fa-arrow-left text-xs text-purple-600"></i>
                    <span class="hidden sm:inline">Quay lại danh sách</span>
                    <span class="sm:hidden">Quay lại</span>
                </a>

                <div class="flex items-center gap-1.5 min-w-0">
                    <h1 class="text-xs sm:text-lg font-black text-slate-900 tracking-tight truncate flex items-center gap-1">
                        <span class="hidden sm:inline text-slate-500 font-bold">Xác nhận giao đơn:</span>
                        <span class="font-mono text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded-lg border border-purple-200 text-xs sm:text-base font-black">#{{ $order->order_code }}</span>
                    </h1>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono font-bold text-[10px] sm:text-xs border border-slate-200 shrink-0">
                        <i class="fa-solid fa-truck text-purple-600 text-[10px]"></i>
                        <span>{{ $vehiclePlate ?? 'Xe giao' }}</span>
                    </span>
                </div>
            </div>

            <!-- Trạng thái đơn -->
            <span class="px-2.5 py-1 rounded-lg text-[10px] sm:text-xs font-bold shrink-0 whitespace-nowrap shadow-2xs"
                  :class="orderStatusBadgeClass()">
                {{ $displayStatus }}
            </span>
        </div>

        <!-- Hàng 2: Thông tin Đại lý / Khách hàng + Nút Gọi & Chỉ đường Maps -->
        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
            <div class="min-w-0 flex-1">
                <div class="text-xs sm:text-sm font-black text-slate-900 truncate">
                    <span>{{ $custName }}</span>
                </div>
                <div class="text-[10.5px] text-slate-500 truncate mt-0.5">
                    <i class="fa-solid fa-location-dot text-slate-400 text-[10px] mr-0.5"></i>
                    <span>{{ $order->customer_address ?: 'Tại cửa hàng / điểm giao' }}</span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                @if($order->customer_phone)
                    <a href="tel:{{ $order->customer_phone }}"
                       class="h-7 px-2.5 sm:h-8 sm:px-3 rounded-lg sm:rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] sm:text-xs flex items-center justify-center gap-1 cursor-pointer shadow-2xs transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-phone text-[10px]"></i>
                        <span class="hidden sm:inline">Gọi: <strong class="font-mono">{{ $order->customer_phone }}</strong></span>
                        <span class="sm:hidden">Gọi</span>
                    </a>
                @endif

                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->customer_address ?: $custName) }}"
                   target="_blank"
                   class="h-7 px-2.5 sm:h-8 sm:px-3 rounded-lg sm:rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-[11px] sm:text-xs flex items-center justify-center gap-1 cursor-pointer transition-colors whitespace-nowrap">
                    <i class="fa-solid fa-location-dot text-rose-500 text-[10px]"></i>
                    <span>Maps ↗</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- THANH ĐIỀU HƯỚNG 2 TAB: GIAO ĐƠN & THU TIỀN vs TỒN KHO HÀNG ÔM            -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-1.5 sm:p-2 border border-slate-200/90 shadow-2xs flex flex-wrap items-center justify-between gap-2 mobile-main-tabs">
        <div class="grid grid-cols-2 gap-1.5 w-full sm:w-auto sm:flex sm:items-center sm:gap-2 tab-btn-group">
            <button type="button"
                    @click="activeMainTab = 'delivery'"
                    :class="activeMainTab === 'delivery'
                        ? 'bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-md shadow-purple-700/20 ring-1 ring-purple-600'
                        : 'bg-slate-100/90 text-slate-700 hover:bg-slate-200/80 border border-slate-200'"
                    class="h-[36px] sm:h-[40px] px-2 sm:px-4 rounded-xl font-black text-xs sm:text-sm flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                <i class="fa-solid fa-truck-ramp-box shrink-0 text-xs sm:text-sm"></i>
                <span class="hidden sm:inline">Xác Nhận Giao Đơn & Thu Tiền</span>
                <span class="sm:hidden">Giao Đơn & Thu Tiền</span>
            </button>

            <button type="button"
                    @click="activeMainTab = 'holding'"
                    :class="activeMainTab === 'holding'
                        ? 'bg-gradient-to-r from-blue-700 to-[#1b4a8c] text-white shadow-md shadow-blue-800/20 ring-1 ring-blue-600'
                        : 'bg-slate-100/90 text-slate-700 hover:bg-slate-200/80 border border-slate-200'"
                    class="h-[36px] sm:h-[40px] px-2 sm:px-4 rounded-xl font-black text-xs sm:text-sm flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                <i class="fa-solid fa-boxes-stacked shrink-0 text-xs sm:text-sm"></i>
                <span class="hidden sm:inline">Tồn Kho Hàng Ôm & Bán Trực Tiếp</span>
                <span class="sm:hidden">Kho Hàng Ôm</span>
                <span :class="activeMainTab === 'holding' ? 'bg-white/20 text-white' : 'bg-blue-100 text-[#1b4a8c]'"
                      class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold whitespace-nowrap shrink-0"
                      x-text="(selectedHoldingWh?.total_qty || 0) + ' SP'"></span>
            </button>
        </div>

        <div class="text-[11px] text-slate-500 font-medium px-2 hidden sm:block">
            <span x-show="activeMainTab === 'delivery'">Đang xử lý giao đơn #<strong class="font-mono text-slate-800">{{ $order->order_code }}</strong></span>
            <span x-show="activeMainTab === 'holding'">Chào mẫu hoặc bán bổ sung hàng ôm tại điểm giao</span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: LAYOUT 2 CỘT CHÍNH (Trái: Kiểm hàng & Ký nhận | Phải: Thanh toán)   -->
    <!-- ========================================================================= -->
    <div x-show="activeMainTab === 'delivery'" class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <!-- CỘT TRÁI (7 CỘT): MỤC 1 KIỂM HÀNG & MỤC 2 BIÊN BẢN KÝ NHẬN -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- 1. KIỂM HÀNG & NHẬP SỐ LƯỢNG TRẢ VỀ (NẾU CÓ) -->
            <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-2xs space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fa-solid fa-rotate-left"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black text-slate-900 text-xs sm:text-sm">
                                <span class="hidden sm:inline">1. Kiểm Hàng & Nhập Số Lượng Trả Về (Nếu có)</span>
                                <span class="inline sm:hidden">1. Kiểm Đếm & Trả Hàng</span>
                            </h3>
                            <p class="text-[11px] text-slate-500 hidden sm:block">Kiểm đếm hàng thực nhận tại điểm giao và ghi nhận hàng hoàn lại</p>
                        </div>
                    </div>

                    <!-- Toggle Checkbox: Khách có trả lại hàng -->
                    <label class="inline-flex items-center gap-1.5 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-xl border cursor-pointer select-none transition-all shadow-2xs shrink-0 whitespace-nowrap text-xs"
                           :class="confirmForm.hasReturns ? 'bg-rose-50 text-rose-800 border-rose-300 font-extrabold ring-1 ring-rose-400' : 'bg-slate-50 text-slate-700 border-slate-200 font-semibold'">
                        <input type="checkbox" x-model="confirmForm.hasReturns" @change="onHasReturnsToggle()" class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded text-rose-600 cursor-pointer">
                        <span class="hidden sm:inline">Khách có trả lại hàng</span>
                        <span class="inline sm:hidden">Có trả hàng</span>
                    </label>
                </div>

                <!-- Bảng danh sách mặt hàng giao (chỉ hiển thị khi tích có trả hàng) -->
                <div x-show="confirmForm.hasReturns" x-cloak class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                        <span class="hidden sm:inline">Danh sách mặt hàng giao (<span x-text="confirmForm.items.length"></span> mục)</span>
                        <span class="inline sm:hidden">Mặt hàng giao (<span x-text="confirmForm.items.length"></span>)</span>
                        <span class="hidden sm:inline">Số lượng xuất kho</span>
                        <span class="inline sm:hidden">SL xuất</span>
                    </div>

                    <div class="space-y-2 max-h-[350px] overflow-y-auto pr-1">
                        <template x-for="(it, iIdx) in confirmForm.items" :key="iIdx">
                            <div class="p-3 rounded-xl border transition-all"
                                 :class="Number(it.returnedQty) > 0 ? 'bg-rose-50/60 border-rose-200' : 'bg-slate-50/80 border-slate-200/80'">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono text-[10px] text-slate-500 font-bold bg-white px-1.5 py-0.5 rounded border border-slate-200" x-text="it.sku"></span>
                                            <span class="font-bold text-xs text-slate-900 leading-snug" x-text="it.name"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2">
                                            <span>Đơn giá: <strong class="text-slate-800 font-mono" x-text="formatNumber(it.unit_price) + ' đ'"></strong> / <span x-text="it.unit"></span></span>
                                            <span>•</span>
                                            <span>Thành tiền: <strong class="text-slate-800 font-mono" x-text="formatNumber(it.amount) + ' đ'"></strong></span>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="inline-block px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-mono font-black text-xs text-slate-800 shadow-2xs">
                                            <span x-text="formatNumber(it.quantity)"></span> <span class="text-[10px] text-slate-500 font-medium" x-text="it.unit"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Khu vực nhập số lượng trả hàng nếu bật toggle -->
                                <div x-show="confirmForm.hasReturns" x-cloak class="mt-2.5 pt-2.5 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2 flex-wrap tab-btn-group">
                                        <span class="font-bold text-rose-700 text-xs">Khách trả lại:</span>
                                        <div class="relative inline-flex items-center">
                                            <input type="number" min="0" :max="it.quantity" step="any"
                                                   x-model.number="it.returnedQty"
                                                   @input="validateReturnQty(it)"
                                                   @change="validateReturnQty(it)"
                                                   @blur="validateReturnQty(it)"
                                                   class="w-20 px-2 py-1 bg-white border rounded-lg text-xs font-mono font-bold text-center focus:outline-none focus:ring-1"
                                                   :class="Number(it.returnedQty) > Number(it.quantity) ? 'border-rose-500 text-rose-600 bg-rose-50 ring-1 ring-rose-500' : 'border-rose-300 text-rose-700 focus:ring-rose-500'">
                                        </div>
                                        <span class="text-[11px] text-slate-500 font-medium" x-text="it.unit"></span>
                                        <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap">
                                            (Tối đa: <button type="button" @click="it.returnedQty = it.quantity; validateReturnQty(it)" class="font-mono font-bold text-rose-600 hover:underline cursor-pointer" title="Bấm để trả toàn bộ" x-text="formatNumber(it.quantity)"></button>)
                                        </span>
                                    </div>

                                    <div class="flex-1 max-w-xs">
                                        <input type="text" placeholder="Lý do trả lại (bể vỡ, thừa, đổi...)"
                                               x-model="it.returnReason"
                                               class="w-full px-2 py-1 bg-white border border-slate-300 rounded-lg text-[11px] text-slate-700 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Tổng kết hàng trả lại nếu có -->
                    <div x-show="confirmForm.hasReturns && returnTotalAmount > 0" x-cloak
                         class="p-3 bg-rose-50 rounded-xl border border-rose-200 flex items-center justify-between text-xs font-bold text-rose-800">
                        <span>Tổng tiền hàng khách trả lại:</span>
                        <span class="font-mono text-sm text-rose-600 font-black" x-text="'- ' + formatNumber(returnTotalAmount) + ' đ'"></span>
                    </div>
                </div>
            </div>

            <!-- 2. BIÊN BẢN BÀN GIAO & KÝ NHẬN -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs space-y-3.5">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm">2. Biên Bản Bàn Giao & Người Nhận</h3>
                        <p class="text-[11px] text-slate-500 hidden sm:block">Thông tin người đại diện đại lý ký nhận vật tư tại điểm giao</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Họ tên người nhận hàng (*):</label>
                        <input type="text" x-model="confirmForm.recipientName"
                               placeholder="VD: Anh Nam (Quản lý kho / Chủ đại lý)"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ghi chú giao nhận phát sinh:</label>
                        <input type="text" x-model="confirmForm.deliveryNotes"
                               placeholder="VD: Khách nhờ giao sau 17h, để gọn sân..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:border-purple-500">
                    </div>
                </div>
            </div>

        </div>

        <!-- CỘT PHẢI (5 CỘT): MỤC 3 HÌNH THỨC THANH TOÁN, TRẠNG THÁI & CÔNG NỢ -->
        <div class="lg:col-span-5 space-y-4">
            
            <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-sm space-y-4 sticky top-6">
                
                <!-- TIÊU ĐỀ SECTION 3 -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs sm:text-sm shrink-0">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 text-sm sm:text-base">3. Thanh Toán & Công Nợ</h3>
                            <p class="text-[11px] text-slate-500 hidden sm:block">Xác lập trạng thái thanh toán & hạch toán công nợ</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-2 bg-purple-50 sm:bg-transparent px-3 py-1.5 sm:p-0 rounded-xl border border-purple-100 sm:border-0">
                        <span class="text-xs text-purple-900 sm:text-slate-400 font-bold uppercase">Cần thu:</span>
                        <span class="font-mono text-base sm:text-lg font-black text-purple-700" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></span>
                    </div>
                </div>

                <!-- 4 NÚT CHỌN NHANH HÌNH THỨC (Tabs) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 payment-mode-grid">
                    <!-- Nút 1: Tiền mặt -->
                    <button type="button" @click="selectPaymentMode('Tiền mặt')"
                            class="py-2.5 px-2 rounded-2xl border flex flex-col items-center justify-center gap-1 transition-all cursor-pointer shadow-2xs"
                            :class="confirmForm.paymentMethod === 'Tiền mặt' ? 'bg-emerald-600 text-white border-emerald-600 font-bold shadow-md shadow-emerald-600/25 ring-2 ring-emerald-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold'">
                        <i class="fa-solid fa-money-bill-wave text-base"></i>
                        <span class="text-xs">Tiền mặt</span>
                    </button>

                    <!-- Nút 2: Quét VietQR -->
                    <button type="button" @click="selectPaymentMode('VietQR')"
                            class="py-2.5 px-2 rounded-2xl border flex flex-col items-center justify-center gap-1 transition-all cursor-pointer shadow-2xs"
                            :class="(confirmForm.paymentMethod === 'Chuyển khoản VietQR' || confirmForm.paymentMethod === 'VietQR') ? 'bg-sky-600 text-white border-sky-600 font-bold shadow-md shadow-sky-600/25 ring-2 ring-sky-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold'">
                        <i class="fa-solid fa-qrcode text-base"></i>
                        <span class="text-xs">Quét VietQR</span>
                    </button>

                    <!-- Nút 3: Ghi nợ -->
                    <button type="button" @click="selectPaymentMode('Ghi nợ')"
                            class="py-2.5 px-2 rounded-2xl border flex flex-col items-center justify-center gap-1 transition-all cursor-pointer shadow-2xs"
                            :class="(confirmForm.isDebt || confirmForm.paymentMethod === 'Ghi nợ') && confirmForm.paymentMethod !== 'Thanh toán kết hợp' ? 'bg-purple-800 text-white border-purple-800 font-bold shadow-md shadow-purple-800/25 ring-2 ring-purple-600/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold'">
                        <i class="fa-solid fa-file-signature text-base"></i>
                        <span class="text-xs">Ghi nợ</span>
                    </button>

                    <!-- Nút 4: Kết hợp (TM + CK + Nợ) -->
                    <button type="button" @click="selectPaymentMode('Thanh toán kết hợp')"
                            class="py-2.5 px-2 rounded-2xl border flex flex-col items-center justify-center gap-1 transition-all cursor-pointer shadow-2xs"
                            :class="confirmForm.paymentMethod === 'Thanh toán kết hợp' ? 'bg-amber-600 text-white border-amber-600 font-bold shadow-md shadow-amber-600/25 ring-2 ring-amber-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold'">
                        <i class="fa-solid fa-layer-group text-base"></i>
                        <span class="text-xs">Kết hợp (TM/CK/Nợ)</span>
                    </button>
                </div>



                                <!-- ======================================================== -->
                <!-- TRƯỜNG HỢP 4: 🔀 THANH TOÁN KẾT HỢP (TM + CK + GHI NỢ)     -->
                <!-- ======================================================== -->
                <template x-if="confirmForm.paymentMethod === 'Thanh toán kết hợp'">
                    <div class="space-y-3.5 p-3.5 sm:p-4 bg-amber-50/60 rounded-2xl border border-amber-200 text-xs">


                        <!-- Thanh trạng thái đối soát phân bổ tiền (2 hàng thông thoáng trên mobile) -->
                        <div class="p-2.5 rounded-xl border space-y-1.5 text-xs"
                             :class="combinedRemaining === 0 ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-bold' : (combinedRemaining > 0 ? 'bg-amber-100/90 border-amber-300 text-amber-900' : 'bg-rose-50 border-rose-300 text-rose-900')">
                            <div class="flex items-center gap-1.5 font-bold">
                                <i :class="combinedRemaining === 0 ? 'fa-solid fa-circle-check text-emerald-600 text-sm' : (combinedRemaining > 0 ? 'fa-solid fa-circle-exclamation text-amber-600 text-sm' : 'fa-solid fa-triangle-exclamation text-rose-600 text-sm')"></i>
                                <span x-show="combinedRemaining === 0" class="text-emerald-800">✓ Đã phân bổ khớp 100% số tiền đơn!</span>
                                <span x-show="combinedRemaining > 0">Còn thiếu: <strong class="font-mono text-amber-800" x-text="formatNumber(combinedRemaining) + ' đ'"></strong></span>
                                <span x-show="combinedRemaining < 0">Vượt quá: <strong class="font-mono text-rose-800" x-text="'+' + formatNumber(Math.abs(combinedRemaining)) + ' đ'"></strong></span>
                            </div>
                            <div class="font-mono text-[11px] flex items-center justify-between text-slate-600 pt-1 border-t"
                                 :class="combinedRemaining === 0 ? 'border-emerald-200' : 'border-amber-200'">
                                <span class="text-slate-500 font-sans">Đã phân bổ:</span>
                                <strong class="text-slate-900" x-text="formatNumber(combinedTotalAllocated) + ' / ' + formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                            </div>
                        </div>

                        <!-- 1. Tiền mặt -->
                        <div class="p-3 bg-white rounded-xl border border-emerald-200 shadow-2xs space-y-1.5">
                            <div class="flex items-center justify-between gap-1">
                                <label class="font-bold text-emerald-900 text-xs flex items-center gap-1.5 truncate">
                                    <i class="fa-solid fa-money-bill-wave text-emerald-600 shrink-0"></i>
                                    <span class="hidden sm:inline">1. Tiền mặt thu tại điểm giao:</span>
                                    <span class="inline sm:hidden">1. Tiền mặt:</span>
                                </label>
                                <button type="button" @click="fillCombinedRemaining('cash')"
                                        class="px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[10px] font-bold cursor-pointer transition-colors whitespace-nowrap shrink-0"
                                        title="Điền phần tiền còn lại vào tiền mặt">
                                    <span class="hidden sm:inline">+ Điền phần còn lại</span>
                                    <span class="inline sm:hidden">+ Còn lại</span>
                                </button>
                            </div>
                            <div class="relative">
                                <input type="text" inputmode="numeric"
                                       x-money="confirmForm.combinedCash"
                                       @input="syncCombinedPaymentStatus()"
                                       placeholder="0"
                                       class="w-full pl-3 pr-12 py-2 bg-slate-50 focus:bg-white border border-emerald-300 rounded-lg text-sm font-mono font-black text-emerald-800 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">VNĐ</span>
                            </div>
                        </div>

                        <!-- 2. Chuyển khoản VietQR -->
                        <div class="p-3 bg-white rounded-xl border border-sky-200 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between gap-1">
                                <label class="font-bold text-sky-900 text-xs flex items-center gap-1.5 truncate">
                                    <i class="fa-solid fa-qrcode text-sky-600 shrink-0"></i>
                                    <span class="hidden sm:inline">2. Chuyển khoản VietQR:</span>
                                    <span class="inline sm:hidden">2. Quét VietQR:</span>
                                </label>
                                <button type="button" @click="fillCombinedRemaining('bank')"
                                        class="px-2 py-0.5 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-[10px] font-bold cursor-pointer transition-colors whitespace-nowrap shrink-0"
                                        title="Điền phần tiền còn lại vào chuyển khoản">
                                    <span class="hidden sm:inline">+ Điền phần còn lại</span>
                                    <span class="inline sm:hidden">+ Còn lại</span>
                                </button>
                            </div>
                            <div class="relative">
                                <input type="text" inputmode="numeric"
                                       x-money="confirmForm.combinedBank"
                                       @input="syncCombinedPaymentStatus()"
                                       placeholder="0"
                                       class="w-full pl-3 pr-12 py-2 bg-slate-50 focus:bg-white border border-sky-300 rounded-lg text-sm font-mono font-black text-sky-800 focus:outline-none focus:ring-1 focus:ring-sky-500">
                                <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">VNĐ</span>
                            </div>

                            <!-- Mã QR VietQR tự động sinh theo đúng số tiền chuyển khoản này -->
                            <div x-show="Number(confirmForm.combinedBank) > 0" class="p-2.5 bg-sky-50/70 rounded-xl border border-sky-200 space-y-2">
                                <div class="flex items-center justify-between gap-1 text-[11px]">
                                    <span class="font-bold text-sky-900 truncate">VietQR: <strong class="font-mono text-emerald-600 font-black" x-text="formatNumber(confirmForm.combinedBank) + ' đ'"></strong></span>
                                    <span class="text-[10px] text-sky-700 font-mono font-bold bg-white px-1.5 py-0.5 rounded border border-sky-200 whitespace-nowrap shrink-0" x-text="(bankName || 'Ngân hàng') + ': ' + (bankAccount || '---')"></span>
                                </div>
                                <div class="flex flex-col sm:flex-row items-center gap-2.5 bg-white p-2.5 rounded-xl border border-sky-100">
                                    <img :src="combinedVietQrUrl" alt="VietQR" class="w-32 h-32 sm:w-28 sm:h-28 object-contain rounded-lg border border-slate-100 shrink-0">
                                    <div class="min-w-0 w-full space-y-1.5 text-[11px]">
                                        <div class="flex items-center justify-between bg-sky-50 px-2 py-1 rounded-lg">
                                            <span class="text-slate-500 font-bold text-[10px]">Chủ TK:</span>
                                            <span class="font-bold text-slate-800 truncate" x-text="bankHolder || '---'"></span>
                                        </div>
                                        <div class="flex items-center justify-between bg-purple-50 px-2 py-1 rounded-lg">
                                            <span class="text-slate-500 font-bold text-[10px]">Nội dung:</span>
                                            <span class="font-mono font-bold text-purple-700 truncate" x-text="qrTransferMemo"></span>
                                        </div>
                                        <button type="button" @click="copyToClipboard(qrTransferMemo, 'memo')"
                                                class="w-full py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-[11px] flex items-center justify-center gap-1 cursor-pointer shadow-2xs">
                                            <i class="fa-regular fa-copy"></i>
                                            <span x-text="copiedField === 'memo' ? 'Đã sao chép' : 'Sao chép nội dung CK'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Ký nợ đại lý -->
                        <div class="p-3 bg-white rounded-xl border border-purple-200 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between gap-1">
                                <label class="font-bold text-purple-900 text-xs flex items-center gap-1.5 truncate">
                                    <i class="fa-solid fa-file-signature text-purple-600 shrink-0"></i>
                                    <span class="hidden sm:inline">3. Ghi nợ đại lý (Ký sổ):</span>
                                    <span class="inline sm:hidden">3. Ký nợ đại lý:</span>
                                </label>
                                <button type="button" @click="fillCombinedRemaining('debt')"
                                        class="px-2 py-0.5 rounded-md bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-[10px] font-bold cursor-pointer transition-colors whitespace-nowrap shrink-0"
                                        title="Điền phần tiền còn lại vào ghi nợ">
                                    <span class="hidden sm:inline">+ Điền phần còn lại</span>
                                    <span class="inline sm:hidden">+ Còn lại</span>
                                </button>
                            </div>
                            <div class="relative">
                                <input type="text" inputmode="numeric"
                                       x-money="confirmForm.combinedDebt"
                                       @input="syncCombinedPaymentStatus()"
                                       placeholder="0"
                                       class="w-full pl-3 pr-12 py-2 bg-slate-50 focus:bg-white border border-purple-300 rounded-lg text-sm font-mono font-black text-purple-800 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">VNĐ</span>
                            </div>

                            <!-- Tóm tắt công nợ khi có phát sinh ghi nợ -->
                            <div x-show="Number(confirmForm.combinedDebt) > 0" class="p-2.5 bg-purple-50/70 rounded-xl border border-purple-200 text-[11px] space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">• Nợ cũ:</span>
                                    <strong class="font-mono text-slate-900">{{ number_format($prevDebt, 0, ',', '.') }} đ</strong>
                                </div>
                                <div class="flex items-center justify-between text-purple-800 font-bold">
                                    <span>• Ghi nợ thêm:</span>
                                    <strong class="font-mono" x-text="'+ ' + formatNumber(confirmForm.combinedDebt) + ' đ'"></strong>
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-purple-200 font-black text-purple-950">
                                    <span>→ Tổng nợ mới:</span>
                                    <strong class="font-mono text-xs" x-text="formatNumber({{ $prevDebt }} + Number(confirmForm.combinedDebt || 0)) + ' đ'"></strong>
                                </div>

                                <!-- Bàn giao nợ cho Sale nếu có nợ -->
                                <div class="pt-2 mt-1 border-t border-purple-200/80">
                                    <label class="block text-[10px] font-bold text-purple-800 uppercase mb-1">Bàn giao nợ này cho Sale:</label>
                                    <select x-model="confirmForm.handoverToUserId"
                                            class="w-full px-2.5 py-1.5 bg-white border border-purple-300 rounded-lg text-[11px] font-semibold text-slate-800 focus:outline-none focus:border-purple-600">
                                        <option value="">-- Ký nợ đại lý trực tiếp (Không bàn giao Sale) --</option>
                                        @foreach(($salesStaff ?? []) as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->phone ?? 'Sale' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ======================================================== -->
                <!-- TRƯỜNG HỢP 1: 💵 TIỀN MẶT                                -->
                <!-- ======================================================== -->
                <template x-if="confirmForm.paymentMethod === 'Tiền mặt' && !confirmForm.isDebt">
                    <div class="space-y-3 p-4 bg-emerald-50/60 rounded-2xl border border-emerald-200 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-900">Số tiền lái xe thực tế đã thu:</span>
                            <span class="text-[10px] text-emerald-700 font-medium">Bằng tiền mặt tại điểm giao</span>
                        </div>

                        <div class="relative">
                            <input type="text" inputmode="numeric"
                                   x-money="confirmForm.collectedAmount"
                                   placeholder="0"
                                   class="w-full pl-3 pr-10 py-2.5 bg-white border border-emerald-300 rounded-xl text-base font-mono font-black text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <span class="absolute right-3 top-3 text-xs font-bold text-slate-400">VNĐ</span>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-600 pt-1 border-t border-emerald-200/60">
                            <span>Tổng đơn sau trừ trả hàng:</span>
                            <strong class="font-mono text-slate-900 font-bold" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                        </div>
                    </div>
                </template>

                <!-- ======================================================== -->
                <!-- TRƯỜNG HỢP 2: 📱 QUÉT MÃ VIETQR                          -->
                <!-- ======================================================== -->
                <template x-if="confirmForm.paymentMethod === 'Chuyển khoản VietQR' || confirmForm.paymentMethod === 'VietQR'">
                    <div class="space-y-3.5 p-4 bg-sky-50/70 rounded-2xl border border-sky-200 text-xs">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 font-bold text-sky-900">
                                <i class="fa-solid fa-qrcode text-sky-600"></i>
                                <span>Mã QR Thanh Toán Tự Động</span>
                            </div>
                            <span class="text-[10px] text-sky-700 font-mono font-bold bg-white px-2 py-0.5 rounded border border-sky-200">VietQR Napas247</span>
                        </div>

                        <!-- Khung hình ảnh QR Code VietQR động -->
                        <div class="bg-white p-3.5 rounded-2xl border border-sky-200 shadow-xs flex flex-col items-center justify-center space-y-2">
                            <img :src="vietQrUrl" alt="VietQR Thanh Toán"
                                 class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-xl border border-slate-100 shadow-inner">
                            <div class="text-center space-y-0.5">
                                <span class="text-[11px] text-slate-500 block">Số tiền thanh toán:</span>
                                <span class="font-mono text-base font-black text-emerald-600" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></span>
                            </div>
                        </div>

                        <!-- Thông tin số tài khoản và nội dung CK -->
                        <div class="space-y-2 pt-1">
                            <div class="flex items-center justify-between py-1 bg-white p-2.5 rounded-xl border border-slate-200">
                                <div>
                                    <span class="text-[10px] text-slate-500 font-bold block uppercase" x-text="'Ngân hàng ' + (bankName || '') + ':'"></span>
                                    <span class="font-mono font-black text-slate-900 text-xs sm:text-sm" x-text="bankAccount || '---'"></span>
                                    <span class="text-[10px] text-slate-500 block" x-text="bankHolder || '---'"></span>
                                </div>
                                <button type="button" @click="copyToClipboard(bankAccount, 'stk')"
                                        class="px-2.5 py-1.5 rounded-lg bg-sky-100 hover:bg-sky-200 text-sky-800 font-bold text-xs cursor-pointer transition-colors flex items-center gap-1">
                                    <i class="fa-regular fa-copy"></i>
                                    <span x-text="copiedField === 'stk' ? 'Đã chép' : 'Chép STK'"></span>
                                </button>
                            </div>

                            <div class="flex items-center justify-between py-1 bg-emerald-50/80 p-2.5 rounded-xl border border-emerald-200">
                                <div>
                                    <span class="text-[10px] text-emerald-800 font-bold block uppercase">Nội dung CK (Mã KH + Mã Đơn):</span>
                                    <span class="font-mono font-black text-slate-900 text-xs sm:text-sm mt-0.5 block" x-text="qrTransferMemo"></span>
                                </div>
                                <button type="button" @click="copyToClipboard(qrTransferMemo, 'memo')"
                                        class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs cursor-pointer shadow-2xs transition-colors flex items-center gap-1">
                                    <i class="fa-regular fa-copy"></i>
                                    <span x-text="copiedField === 'memo' ? 'Đã chép' : 'Sao chép'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ======================================================== -->
                <!-- TRƯỜNG HỢP 3: 📝 GHI NỢ (KÝ NỢ ĐẠI LÝ)                     -->
                <!-- ======================================================== -->
                <template x-if="confirmForm.isDebt || confirmForm.paymentMethod === 'Ghi nợ'">
                    <div class="space-y-4">
                        <!-- Bảng Chi Tiết Biến Động Công Nợ Đại Lý (Hiện khi phát hiện vượt hạn mức) -->
                        <div x-show="isOverLimit" class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                            <div class="font-black text-slate-800 pb-1.5 border-b border-slate-200 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-chart-line text-purple-600"></i>
                                    <span>Dự Tính Biến Động Sổ Nợ Đại Lý</span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">• Nợ cũ trước đơn này:</span>
                                <strong class="font-mono font-bold text-slate-900">{{ number_format($prevDebt, 0, ',', '.') }} đ</strong>
                            </div>
                            <div class="flex items-center justify-between text-purple-700 font-semibold">
                                <span>• Phát sinh ghi nợ đơn này:</span>
                                <strong class="font-mono font-black" x-text="'+ ' + formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-slate-900 font-bold">
                                <span>→ Tổng nợ mới sau khi giao:</span>
                                <strong class="font-mono font-black text-purple-800 text-sm"
                                        :class="isOverLimit ? 'text-rose-700' : 'text-purple-800'"
                                        x-text="formatNumber({{ $prevDebt }} + Number(confirmForm.finalAmount || 0)) + ' đ'"></strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-500 text-[11px] pt-1">
                                <span>Hạn mức công nợ cho phép:</span>
                                <span class="font-mono font-semibold text-slate-700">{{ number_format($cLimit, 0, ',', '.') }} đ</span>
                            </div>
                        </div>



                        @if($cLimit > 0)
                        <!-- ============================================================ -->
                        <!-- PHÁT HIỆN VƯỢT HẠN MỨC: Hiển thị 2 phương án kết hợp      -->
                        <!-- ============================================================ -->
                        <div x-show="isOverLimit" x-cloak class="space-y-3">
                            <!-- Banner cảnh báo đỏ -->
                            <div class="p-4 bg-gradient-to-br from-rose-900 via-red-900 to-orange-900 rounded-2xl text-white space-y-2 shadow-md">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-rose-500/30 border border-rose-400/40 flex items-center justify-center text-rose-200 font-bold text-base shrink-0">
                                        <i class="fa-solid fa-shield-exclamation"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-rose-300 font-bold tracking-wider uppercase block">Cảnh báo hạn mức tín dụng</span>
                                        <h4 class="font-black text-sm text-white">VƯỢT HẠN MỨC CÔNG NỢ!</h4>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-rose-800/60 text-[11px]">
                                    <div class="text-center">
                                        <div class="text-rose-300">Nợ cũ</div>
                                        <div class="font-mono font-black text-white">{{ number_format($prevDebt, 0, ',', '.') }} đ</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-rose-300">Đơn này</div>
                                        <div class="font-mono font-black text-amber-300" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-rose-300">Vượt</div>
                                        <div class="font-mono font-black text-rose-300" x-text="'+' + formatNumber(excessAmount) + ' đ'"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2 Tab phương án -->
                            <div class="rounded-2xl border border-orange-200 overflow-hidden shadow-xs">
                                <!-- Tabs header -->
                                <div class="flex border-b border-orange-200">
                                    <button type="button" @click="overrunMode = 'split'"
                                            class="flex-1 py-2.5 px-3 text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                                            :class="overrunMode === 'split' ? 'bg-orange-600 text-white' : 'bg-orange-50 text-orange-800 hover:bg-orange-100'">
                                        <i class="fa-solid fa-scissors"></i>
                                        <span>Phương án 2: Tách thanh toán</span>
                                    </button>
                                    <button type="button" @click="overrunMode = 'approval'"
                                            class="flex-1 py-2.5 px-3 text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                                            :class="overrunMode === 'approval' ? 'bg-purple-800 text-white' : 'bg-purple-50 text-purple-800 hover:bg-purple-100'">
                                        <i class="fa-solid fa-shield-check"></i>
                                        <span>Phương án 1.B: Duyệt bảo lãnh</span>
                                    </button>
                                </div>

                                <!-- PHƯƠNG ÁN 2: Tách thanh toán (Thu phần vượt ngay + Ký nợ phần trong hạn mức) -->
                                <div x-show="overrunMode === 'split'" class="p-4 bg-orange-50/60 space-y-3.5 text-xs">
                                    <div class="flex items-start gap-2 p-2.5 bg-white rounded-xl border border-orange-200">
                                        <i class="fa-solid fa-circle-info text-orange-500 mt-0.5 shrink-0"></i>
                                        <span class="text-orange-950 leading-relaxed">
                                            Thu tối thiểu phần vượt hạn mức (<strong class="font-mono text-orange-700" x-text="formatNumber(excessAmount) + ' đ'"></strong>). Lái xe có thể <strong>nhập số tiền thu nhiều hơn</strong> để trừ dần vào nợ cũ hoặc thanh toán toàn bộ đơn.
                                        </span>
                                    </div>

                                    <!-- Ô NHẬP SỐ TIỀN THU THỰC TẾ -->
                                    <div class="p-3 bg-white rounded-2xl border border-orange-200 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-[11px] font-black text-slate-800 uppercase tracking-tight">
                                                💵 Số tiền thu ngay:
                                            </label>
                                            <span class="text-[10px] text-slate-500">
                                                Tối thiểu: <strong class="font-mono font-bold text-orange-700" x-text="formatNumber(excessAmount) + ' đ'"></strong>
                                            </span>
                                        </div>

                                        <div class="relative">
                                            <input type="text" inputmode="numeric"
                                                   x-money="customSplitCollectAmount"
                                                   :placeholder="formatNumber(excessAmount)"
                                                   class="w-full pl-3 pr-14 py-2.5 bg-white border-2 rounded-xl text-base font-mono font-black text-slate-900 focus:outline-none transition-all shadow-inner"
                                                   :class="splitCollectAmountActual < excessAmount ? 'border-rose-400 focus:border-rose-600 bg-rose-50/40 text-rose-800' : (splitCollectAmountActual > excessAmount ? 'border-emerald-500 focus:border-emerald-600 ring-2 ring-emerald-500/15 text-emerald-800' : 'border-orange-300 focus:border-orange-500')">
                                            <span class="absolute right-3 top-3 text-xs font-bold text-slate-400 pointer-events-none">VNĐ</span>
                                        </div>

                                        <!-- Các nút bấm chọn nhanh -->
                                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                            <button type="button" @click="customSplitCollectAmount = excessAmount"
                                                    class="px-2.5 py-1 bg-orange-50 hover:bg-orange-100 border border-orange-300 rounded-lg text-[10px] font-bold text-orange-800 transition-colors cursor-pointer"
                                                    :class="splitCollectAmountActual === excessAmount ? 'ring-2 ring-orange-500 font-black' : ''">
                                                Tối thiểu: <span x-text="formatNumber(excessAmount)"></span> đ
                                            </button>
                                            <button type="button" @click="customSplitCollectAmount = confirmForm.finalAmount"
                                                    class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-lg text-[10px] font-bold text-emerald-800 transition-colors cursor-pointer"
                                                    :class="splitCollectAmountActual === confirmForm.finalAmount ? 'ring-2 ring-emerald-500 font-black' : ''">
                                                Thu hết đơn: <span x-text="formatNumber(confirmForm.finalAmount)"></span> đ
                                            </button>
                                            <button type="button" x-show="customSplitCollectAmount !== excessAmount"
                                                    @click="customSplitCollectAmount = excessAmount"
                                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg text-[10px] font-bold text-slate-600 transition-colors cursor-pointer">
                                                <i class="fa-solid fa-rotate-left mr-0.5"></i> Mặc định
                                            </button>
                                        </div>

                                        <!-- Hướng dẫn & phân tích mức tiền thu -->
                                        <div class="p-2 rounded-xl text-[11px] leading-relaxed transition-all"
                                             :class="splitCollectAmountActual < excessAmount ? 'bg-rose-50 text-rose-800 border border-rose-200' : (splitCollectAmountActual > confirmForm.finalAmount ? 'bg-teal-50 text-teal-900 border border-teal-200' : (splitCollectAmountActual > excessAmount ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-orange-50 text-orange-900 border border-orange-200'))">
                                            <template x-if="splitCollectAmountActual < excessAmount">
                                                <div class="flex items-center gap-1.5 font-bold">
                                                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                                                    <span>Chưa đủ mức tối thiểu! Cần thu thêm ít nhất <strong class="font-mono" x-text="formatNumber(excessAmount - splitCollectAmountActual) + ' đ'"></strong> để không vượt hạn mức.</span>
                                                </div>
                                            </template>
                                            <template x-if="splitCollectAmountActual === excessAmount">
                                                <div class="flex items-center gap-1.5 font-medium">
                                                    <i class="fa-solid fa-circle-check text-orange-600"></i>
                                                    <span>Thu vừa đủ phần vượt (<strong class="font-mono" x-text="formatNumber(excessAmount) + ' đ'"></strong>). Ghi nợ trong hạn mức <strong class="font-mono text-purple-700" x-text="formatNumber(allowedDebt) + ' đ'"></strong>.</span>
                                                </div>
                                            </template>
                                            <template x-if="splitCollectAmountActual > excessAmount && splitCollectAmountActual <= confirmForm.finalAmount">
                                                <div class="flex items-center gap-1.5 font-medium">
                                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                                    <span>Thu thêm <strong class="font-mono text-emerald-800" x-text="formatNumber(splitCollectAmountActual - excessAmount) + ' đ'"></strong> → Giảm nợ đơn này còn <strong class="font-mono text-purple-700" x-text="formatNumber(allowedDebt) + ' đ'"></strong> (dưới hạn mức).</span>
                                                </div>
                                            </template>
                                            <template x-if="splitCollectAmountActual > confirmForm.finalAmount">
                                                <div class="flex items-center gap-1.5 font-medium">
                                                    <i class="fa-solid fa-star text-teal-600"></i>
                                                    <span>Thu đủ toàn bộ đơn + cấn trừ <strong class="font-mono text-teal-800" x-text="formatNumber(surplusOverOrder) + ' đ'"></strong> trực tiếp vào nợ cũ của đại lý!</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Hình thức thu -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-orange-900 uppercase mb-1.5">
                                            Hình thức thu (<span class="font-mono" x-text="formatNumber(splitCollectAmountActual)"></span> đ):
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="splitCollectMethod = 'Tiền mặt'"
                                                    class="py-2.5 rounded-xl border flex items-center justify-center gap-1.5 text-xs font-bold transition-all cursor-pointer"
                                                    :class="splitCollectMethod === 'Tiền mặt' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/20' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'">
                                                <i class="fa-solid fa-money-bill-wave"></i>
                                                <span>Tiền mặt</span>
                                            </button>
                                            <button type="button" @click="splitCollectMethod = 'VietQR'"
                                                    class="py-2.5 rounded-xl border flex items-center justify-center gap-1.5 text-xs font-bold transition-all cursor-pointer"
                                                    :class="splitCollectMethod === 'VietQR' ? 'bg-sky-600 text-white border-sky-600 shadow-md shadow-sky-600/20' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'">
                                                <i class="fa-solid fa-qrcode"></i>
                                                <span>VietQR</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- QR nếu chọn VietQR -->
                                    <div x-show="splitCollectMethod === 'VietQR'" class="bg-white p-3.5 rounded-2xl border border-sky-200 flex flex-col items-center gap-2 shadow-2xs">
                                        <img :src="splitVietQrUrl" alt="VietQR" class="w-40 h-40 object-contain rounded-xl border border-slate-100 shadow-inner">
                                        <div class="text-center">
                                            <div class="text-[10px] text-slate-500 font-medium">Số tiền quét mã QR:</div>
                                            <div class="font-mono font-black text-sky-700 text-base" x-text="formatNumber(splitCollectAmountActual) + ' đ'"></div>
                                        </div>
                                        <div class="flex items-center gap-1 text-[10px] text-slate-500 font-mono bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">
                                            <span x-text="qrTransferMemo + ' THU'"></span>
                                        </div>
                                    </div>

                                    <!-- Tóm tắt phân bổ công nợ sau khi thu -->
                                    <div class="p-3.5 bg-white rounded-2xl border border-orange-200 space-y-1.5 shadow-2xs">
                                        <div class="flex justify-between font-bold text-emerald-800">
                                            <span>💵 Thu ngay (<span x-text="splitCollectMethod"></span>):</span>
                                            <strong class="font-mono" x-text="formatNumber(splitCollectAmountActual) + ' đ'"></strong>
                                        </div>
                                        <div class="flex justify-between font-semibold" :class="allowedDebt > 0 ? 'text-purple-700' : 'text-slate-400'">
                                            <span>📝 Ghi nợ đơn này:</span>
                                            <strong class="font-mono" x-text="formatNumber(allowedDebt) + ' đ'"></strong>
                                        </div>
                                        <template x-if="surplusOverOrder > 0">
                                            <div class="flex justify-between font-semibold text-teal-700">
                                                <span>📉 Cấn trừ vào nợ cũ đại lý:</span>
                                                <strong class="font-mono" x-text="'- ' + formatNumber(surplusOverOrder) + ' đ'"></strong>
                                            </div>
                                        </template>
                                        <div class="flex justify-between pt-1.5 border-t border-slate-100 font-black text-slate-900">
                                            <span>= Tổng đơn:</span>
                                            <strong class="font-mono" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                                        </div>
                                        <div class="pt-1.5 border-t border-dashed border-slate-200 flex justify-between items-center text-[11px]">
                                            <span class="text-slate-600 font-medium">Dự tính tổng nợ đại lý sau giao:</span>
                                            <strong class="font-mono font-bold"
                                                    :class="projectedTotalDebt <= creditLimit ? 'text-emerald-700' : 'text-rose-700'"
                                                    x-text="formatNumber(projectedTotalDebt) + ' đ'"></strong>
                                        </div>
                                        <div class="flex justify-between items-center text-[10px] text-slate-400">
                                            <span>Hạn mức cho phép:</span>
                                            <span class="font-mono font-semibold text-slate-600" x-text="formatNumber(creditLimit) + ' đ'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- PHƯƠNG ÁN 1.B: Duyệt bảo lãnh online -->
                                <div x-show="overrunMode === 'approval'" class="p-4 bg-purple-50/60 space-y-3 text-xs">
                                    <!-- Trạng thái hiện tại -->
                                    <!-- Chưa gửi yêu cầu -->
                                    <template x-if="overrunStatus === '' || overrunStatus === null">
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-2 p-2.5 bg-white rounded-xl border border-purple-200">
                                                <i class="fa-solid fa-circle-info text-purple-500 mt-0.5 shrink-0"></i>
                                                <span class="text-purple-900 leading-relaxed">
                                                    Ghi nợ toàn bộ <strong x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong> vào sổ đại lý, bao gồm phần vượt hạn mức <strong x-text="formatNumber(excessAmount) + ' đ'"></strong>. Cần Ban Giám Đốc / Kế Toán trưởng phê duyệt bảo lãnh.
                                                </span>
                                            </div>
                                            <button type="button" @click="sendOverrideRequest()" :disabled="isSendingRequest"
                                                    class="w-full py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold flex items-center justify-center gap-2 cursor-pointer transition-colors disabled:opacity-50">
                                                <i class="fa-solid fa-paper-plane"></i>
                                                <span x-text="isSendingRequest ? 'Đang gửi...' : 'Gửi Yêu Cầu Phê Duyệt Bảo Lãnh Nợ'"></span>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Đang chờ duyệt -->
                                    <template x-if="overrunStatus === 'Chờ duyệt'">
                                        <div class="space-y-3">
                                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-300 flex items-center gap-2.5">
                                                <i class="fa-solid fa-clock text-amber-600 text-base animate-pulse"></i>
                                                <div>
                                                    <div class="font-bold text-amber-900">Đang chờ phê duyệt...</div>
                                                    <div class="text-amber-700 text-[10px]">Hệ thống tự động kiểm tra mỗi 5 giây. Kế toán / Giám đốc đang xem xét.</div>
                                                </div>
                                            </div>
                                            <button type="button" @click="sendOverrideRequest()" :disabled="isSendingRequest"
                                                    class="w-full py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-[11px] flex items-center justify-center gap-2 cursor-pointer transition-colors disabled:opacity-50">
                                                <i class="fa-solid fa-rotate-right"></i>
                                                <span>Gửi lại yêu cầu</span>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Đã được duyệt -->
                                    <template x-if="overrunStatus === 'Đã duyệt'">
                                        <div class="p-4 bg-emerald-50 rounded-xl border-2 border-emerald-400 space-y-2">
                                            <div class="flex items-center gap-2 text-emerald-800 font-black">
                                                <i class="fa-solid fa-shield-check text-emerald-600 text-lg"></i>
                                                <span>ĐÃ ĐƯỢC PHÊ DUYỆT BẢO LÃNH!</span>
                                            </div>
                                            <div class="text-[11px] text-emerald-700">
                                                Phê duyệt bởi: <strong x-text="overrunApprover"></strong>
                                                <span x-show="overrunApprovedAt"> — <span x-text="overrunApprovedAt"></span></span>
                                            </div>
                                            <div class="text-[11px] text-emerald-800 font-semibold">Có thể ghi nợ toàn bộ <strong x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong> mà không cần thu tiền.</div>
                                        </div>
                                    </template>

                                    <!-- Bị từ chối -->
                                    <template x-if="overrunStatus === 'Từ chối'">
                                        <div class="p-3 bg-rose-50 rounded-xl border border-rose-300 space-y-2">
                                            <div class="flex items-center gap-2 text-rose-800 font-black">
                                                <i class="fa-solid fa-ban text-rose-600"></i>
                                                <span>Yêu cầu bị từ chối</span>
                                            </div>
                                            <div class="text-[11px] text-rose-700">Từ chối bởi: <strong x-text="overrunApprover"></strong></div>
                                            <div class="text-[11px] text-rose-700">Vui lòng chuyển sang Phương án 2 (Tách thanh toán) hoặc liên hệ lại quản lý.</div>
                                            <button type="button" @click="overrunMode = 'split'"
                                                    class="w-full py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-[11px] flex items-center justify-center gap-2 cursor-pointer transition-colors">
                                                <i class="fa-solid fa-scissors"></i>
                                                <span>Chuyển sang Phương án 2: Tách thanh toán</span>
                                            </button>
                                        </div>
                                    </template>

                                    @if($canApprove)
                                    <!-- KHỐI DUYỆT DÀNH CHO ADMIN/KẾ TOÁN (hiển thị ngay trên trang) -->
                                    <div x-show="overrunStatus === 'Chờ duyệt'" class="space-y-2 pt-2 border-t border-purple-200">
                                        <div class="text-[10px] font-bold text-purple-800 uppercase tracking-wide">⚡ Phê duyệt nhanh (Quyền Quản lý / Kế toán):</div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600 mb-1">Ghi chú khi duyệt (tùy chọn):</label>
                                            <input type="text" x-model="approvalNote" placeholder="Ví dụ: Đại lý uy tín, đồng ý bảo lãnh lần này..."
                                                   class="w-full px-3 py-2 bg-white border border-purple-300 rounded-xl text-xs focus:outline-none focus:border-purple-500">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="approveOverride('approve')" :disabled="isApprovingOverride"
                                                    class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition-colors disabled:opacity-50">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Phê Duyệt Bảo Lãnh</span>
                                            </button>
                                            <button type="button" @click="approveOverride('reject')" :disabled="isApprovingOverride"
                                                    class="py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition-colors disabled:opacity-50">
                                                <i class="fa-solid fa-ban"></i>
                                                <span>Từ Chối</span>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- THẺ TỔNG HỢP GHI NỢ SỔ ĐẠI LÝ (TINH GỌN, KHÔNG TRÙNG LẶP) -->
                        <div x-show="!isOverLimit" class="p-3.5 sm:p-4 bg-gradient-to-br from-purple-900 via-indigo-950 to-slate-900 rounded-2xl text-white space-y-2.5 shadow-md">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-purple-500/30 border border-purple-400/40 text-purple-200 flex items-center justify-center font-bold text-sm shrink-0">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-purple-300 font-bold uppercase tracking-wider block">Chế độ thanh toán</span>
                                        <h4 class="font-black text-xs sm:text-sm text-white">KÝ NỢ TOÀN BỘ VÀO SỔ ĐẠI LÝ</h4>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-purple-300 block">Tiền nợ đơn này</span>
                                    <strong class="font-mono text-base font-black text-amber-300" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                                </div>
                            </div>

                            <p class="text-[11px] text-purple-200/90 leading-relaxed border-t border-purple-800/60 pt-2">
                                Lái xe nhận biên bản ký nhận từ đại lý. Không thu tiền mặt tại điểm giao.
                            </p>

                            <!-- Bảng thông số biến động sổ nợ dạng 2x2 grid hiện đại -->
                            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-purple-300 text-[10px] block">• Nợ cũ trước đơn:</span>
                                    <span class="font-mono font-bold text-white text-xs">{{ number_format($prevDebt, 0, ',', '.') }} đ</span>
                                </div>
                                <div>
                                    <span class="text-purple-300 text-[10px] block">• Hạn mức cho phép:</span>
                                    <span class="font-mono font-semibold text-purple-200 text-xs">{{ number_format($cLimit, 0, ',', '.') }} đ</span>
                                </div>
                                <div class="pt-1.5 border-t border-white/10">
                                    <span class="text-purple-300 text-[10px] block">→ Tổng nợ mới sau giao:</span>
                                    <span class="font-mono font-black text-emerald-300 text-xs sm:text-sm" x-text="formatNumber({{ $prevDebt }} + Number(confirmForm.finalAmount || 0)) + ' đ'"></span>
                                </div>
                                <div class="pt-1.5 border-t border-white/10">
                                    <span class="text-purple-300 text-[10px] block">• Trạng thái:</span>
                                    <span class="inline-flex items-center gap-1 font-bold text-emerald-400 text-xs">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Trong hạn mức</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Không có credit limit: Thẻ ghi nợ tinh gọn -->
                        <div class="p-3.5 sm:p-4 bg-gradient-to-br from-purple-900 via-indigo-950 to-slate-900 rounded-2xl text-white space-y-2.5 shadow-md">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-purple-500/30 border border-purple-400/40 text-purple-200 flex items-center justify-center font-bold text-sm shrink-0">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-purple-300 font-bold tracking-wider uppercase block">Chế độ thanh toán</span>
                                        <h4 class="font-black text-xs sm:text-sm text-white">KÝ NỢ TOÀN BỘ VÀO SỔ ĐẠI LÝ</h4>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-purple-300 block">Tiền nợ đơn này</span>
                                    <strong class="font-mono text-base font-black text-amber-300" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                                </div>
                            </div>
                            <p class="text-[11px] text-purple-200/90 leading-relaxed border-t border-purple-800/60 pt-2">
                                Lái xe giao hàng và nhận biên bản ký nhận từ đại lý. Không thu tiền mặt.
                            </p>
                        </div>
                        @endif
                    </div>
                </template>

                <!-- Quy Định Giao Hàng & Chứng Từ Alert Box (Gọn gàng 1 dòng) -->
                <div class="px-3.5 py-2 bg-amber-50/90 rounded-xl border border-amber-200 text-[11px] text-amber-900 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-amber-600 shrink-0 text-xs"></i>
                    <span class="truncate">Lái xe nhận biên lai giao hàng <strong>có đủ chữ ký đại lý</strong> trước khi bấm hoàn tất.</span>
                </div>

                <!-- NÚT HOÀN TẤT & BÁO GIAO THẤT BẠI -->
                <div class="space-y-2.5 pt-2 mobile-submit-buttons">
                    <!-- Nút submit tùy theo trạng thái -->
                    <!-- Bị block khi: GHI NỢ + VƯỢT HẠN MỨC + đang chờ duyệt (phương án 1.B) -->
                    <button type="button" @click="submitDeliveryCompletion()"
                            :disabled="isSubmittingDelivery || isSubmitBlocked"
                            :class="overrunStatus === 'Đã duyệt' && overrunMode === 'approval' && isOverLimit ? 
                                'btn-submit-main w-full py-3.5 px-4 rounded-2xl font-black text-sm text-white bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 cursor-pointer hover:scale-[1.01] active:scale-98 disabled:opacity-50' :
                                (isSubmitBlocked ? 'btn-submit-main w-full py-3.5 px-4 rounded-2xl font-black text-sm text-white bg-slate-400 cursor-not-allowed flex items-center justify-center gap-2 disabled:opacity-70' :
                                'btn-submit-main w-full py-3.5 px-4 rounded-2xl font-black text-sm text-white bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 cursor-pointer hover:scale-[1.01] active:scale-98 disabled:opacity-50')">
                        <i class="fa-solid fa-circle-check text-base shrink-0"></i>
                        <span class="hidden sm:inline" x-text="submitBtnLabel"></span>
                        <span class="inline sm:hidden font-bold" x-text="submitBtnLabelMobile"></span>
                    </button>

                    <button type="button" @click="showFailModal = true"
                            class="btn-submit-fail w-full py-2.5 px-3 rounded-xl font-bold text-xs text-rose-700 hover:bg-rose-50 border border-rose-200 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-circle-xmark text-rose-600 shrink-0"></i>
                        <span class="hidden sm:inline">Báo giao hàng không thành công (Khách hẹn lại / Từ chối)</span>
                        <span class="inline sm:hidden">Báo giao không thành công</span>
                    </button>

                    @if($order->status === 'Không thành công')
                    <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200 space-y-1.5 mt-2">
                        <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                            <span>Đơn hàng đang ở trạng thái: Giao không thành công</span>
                        </div>
                        <div class="text-[11px] text-amber-800">
                            Lý do: <strong>{{ $order->failed_delivery_reason ?: 'Không giao được' }}</strong>
                            @if($order->failed_delivery_note) - <em>{{ $order->failed_delivery_note }}</em> @endif
                        </div>
                        <div class="text-[10px] text-amber-700 italic">
                            * Hàng đang lưu trên xe. Phương án xử lý (Hoàn nhập kho hoặc chuyển xe) sẽ thực hiện khi xe trở về bãi / kho.
                        </div>
                    </div>
                    @elseif($order->status === 'Đã hoàn kho')
                    <div class="p-3 bg-blue-50 rounded-2xl border border-blue-200 space-y-2 mt-2">
                        <div class="flex items-center gap-2 text-blue-900 font-bold text-xs">
                            <i class="fa-solid fa-circle-check text-blue-600"></i>
                            <span>Đơn hàng đã hoàn về kho thành công</span>
                        </div>
                        <p class="text-[11px] text-blue-800 leading-relaxed">
                            Hàng đã được nhập lại kho và reset trạng thái bốc/kiểm. Khi kích hoạt giao lại, đơn sẽ chuyển sang "Chờ bốc" để kho bốc và kiểm hàng lại từ đầu.
                        </p>
                        <button type="button" @click="redeliverOrderDirect()" :disabled="isSubmittingDelivery"
                                class="w-full py-2.5 px-3 rounded-xl bg-[#1b4a8c] hover:bg-blue-800 text-white font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-50">
                            <i class="fa-solid fa-rotate-right"></i>
                            <span>Kích hoạt giao lại (Chuyển sang Chờ bốc & Kiểm lại)</span>
                        </button>
                    </div>
                    @endif
                </div>

            </div>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 3. TỒN KHO HÀNG ÔM CỦA NHÂN VIÊN & BÁN HÀNG TẠI ĐIỂM GIAO                 -->
    <!-- ========================================================================= -->
    <div x-show="activeMainTab === 'holding'" id="holding-stock-section" class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4">
        
        <!-- HEADER SECTION -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-blue-100 text-[#1b4a8c] flex items-center justify-center font-bold text-sm sm:text-base shadow-xs shrink-0">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                        <h2 class="font-black text-slate-900 text-sm sm:text-lg">
                            <span class="hidden sm:inline">Tồn Kho Hàng Ôm Của Nhân Viên / Xe</span>
                            <span class="inline sm:hidden">Tồn Kho Hàng Ôm</span>
                        </h2>
                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-[#1b4a8c] font-bold text-[10px] sm:text-[11px] border border-blue-200">
                            Bán trực tiếp
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 hidden sm:block">
                        Hàng hóa nhân viên/xe đang nắm giữ để chào mẫu hoặc bán bổ sung ngay cho khách hàng 
                        <strong class="text-slate-800 font-bold">{{ $custName }}</strong>
                    </p>
                </div>
            </div>

            <!-- Nút chuyển sang trang tạo đơn bán hàng từ kho ôm -->
            <a :href="'/inventory/holding-sale/create?warehouse_id=' + (selectedHoldingWhId || '') + '&dealer_id={{ $order->dealer_id ?? '' }}&return_url=' + encodeURIComponent(window.location.href)"
               class="px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-[#1b4a8c] hover:bg-blue-800 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-xs transition-colors cursor-pointer no-underline shrink-0 whitespace-nowrap"
               title="Mở trang bán hàng đầy đủ với đại lý đã chọn sẵn">
                <i class="fa-solid fa-cart-shopping text-xs"></i>
                <span class="hidden sm:inline">+ Bán Hàng Từ Kho Này</span>
                <span class="inline sm:hidden">+ Bán Hàng</span>
            </a>
        </div>

        <!-- NẾU CÓ NHIỀU KHO ÔM: THẺ CHỌN NHANH NHÂN VIÊN -->
        @if(count($employeeWarehouses) > 1)
        <div class="space-y-1.5 pt-1">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                <span>Chọn kho nhân sự / xe:</span>
                <span class="text-[10px] text-slate-400 font-normal hidden sm:inline">Click vào thẻ để chuyển xem tồn kho của nhân viên đó</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 holding-tabs-grid">
                <template x-for="wh in employeeWarehouses" :key="wh.id">
                    <button type="button"
                            @click="selectHoldingWarehouse(wh.id)"
                            :class="String(selectedHoldingWhId) === String(wh.id) ? 'ring-2 ring-[#1b4a8c] bg-blue-50/80 border-blue-500 shadow-sm' : 'bg-white hover:bg-slate-50 border-slate-200'"
                            class="p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-2 text-left w-full group">
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-xs text-slate-900 truncate group-hover:text-[#1b4a8c] transition-colors" x-text="wh.employee_name || wh.name"></div>
                            <div class="text-[10px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono text-blue-700 font-semibold" x-text="wh.employee_code || ('KHO-OM-' + wh.id)"></span>
                                <span>•</span>
                                <span class="font-mono font-bold text-emerald-700" x-text="formatNumber(wh.total_qty) + ' SP'"></span>
                            </div>
                        </div>
                        <span x-show="String(selectedHoldingWhId) === String(wh.id)" class="w-5 h-5 rounded-full bg-[#1b4a8c] text-white flex items-center justify-center text-[10px] shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </span>
                    </button>
                </template>
            </div>
        </div>
        @endif

        <!-- BẢNG CHI TIẾT TỒN KHO HÀNG ÔM CỦA NHÂN VIÊN ĐANG CHỌN (CHUẨN FORM NHƯ IMPORT.BLADE.PHP) -->
        <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <!-- THANH TÌM KIẾM SẢN PHẨM TRONG KHO ÔM -->
            <div class="p-3 bg-slate-50/90 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700">Chi tiết sản phẩm:</span>
                    <span class="px-2 py-0.5 rounded-lg bg-blue-100 text-[#1b4a8c] font-bold text-[11px] font-mono"
                          x-text="(getSelectedHoldingWh()?.stocks || []).length + ' sản phẩm'"></span>
                </div>

                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" x-model="holdingSearch" placeholder="Lọc theo tên hoặc mã SKU..."
                           class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-500 shadow-2xs">
                </div>
            </div>

            <!-- TABLE CONTENT -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200 mobile-product-table">
                    <thead class="bg-slate-100/80 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-3 text-center w-12">STT</th>
                            <th class="py-3 px-3">Mã Hàng (SKU)</th>
                            <th class="py-3 px-3">Tên Sản Phẩm</th>
                            <th class="py-3 px-3 text-center">ĐVT</th>
                            <th class="py-3 px-3 text-center">Tồn Đang Ôm</th>
                            <th class="py-3 px-3 text-right">Đơn Giá Bán</th>
                            <th class="py-3 px-3 text-right">Thành Tiền (Giá Bán)</th>
                            <th class="py-3 px-3 text-center">Vị Trí Lưu</th>
                            <th class="py-3 px-4 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                        <template x-for="(st, sIdx) in filteredHoldingStocks" :key="String(selectedHoldingWhId) + '_' + (st.id || sIdx)">
                            <tr x-data="{ isExpanded: false }" :class="{ 'is-collapsed': !isExpanded }" class="hover:bg-blue-50/40 transition-colors">
                                <td class="m-col-stt py-2.5 px-3 text-center font-mono text-slate-400" x-text="sIdx + 1"></td>
                                <td class="m-col-product py-2.5 px-3">
                                    <div class="flex items-center justify-between gap-2 cursor-pointer" @click="isExpanded = !isExpanded">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-xs sm:text-xs text-slate-900" x-text="st.name || st.product?.name || '---'"></div>
                                            <div class="font-mono text-[10px] text-blue-700 font-bold mt-0.5" x-text="'SKU: ' + (st.sku || st.product?.sku || '---')"></div>
                                        </div>
                                        <button type="button" @click.stop="isExpanded = !isExpanded"
                                                class="sm:hidden w-7 h-7 rounded-xl border flex items-center justify-center transition-all shrink-0 cursor-pointer shadow-2xs"
                                                :class="isExpanded ? 'bg-blue-100 border-blue-300 text-[#1b4a8c]' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                                                :title="isExpanded ? 'Thu gọn' : 'Xem chi tiết'">
                                            <i class="fa-solid text-xs transition-transform duration-200"
                                               :class="isExpanded ? 'fa-chevron-up text-[#1b4a8c]' : 'fa-chevron-down text-slate-500'"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="m-col-unit py-2.5 px-3 text-center" :class="{ 'mobile-hidden': !isExpanded }" data-label="ĐVT:" x-text="st.unit || st.product?.unit || 'cái'"></td>
                                <td class="m-col-qty py-2.5 px-3 text-center" :class="{ 'mobile-hidden': !isExpanded }" data-label="Tồn đang ôm:">
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-mono font-black text-xs" x-text="formatNumber(st.stock_quantity)"></span>
                                </td>
                                <td class="m-col-price py-2.5 px-3 text-right font-mono text-slate-600" :class="{ 'mobile-hidden': !isExpanded }" data-label="Đơn giá bán:" x-text="formatNumber(st.price || st.product?.price || 0) + ' đ'"></td>
                                <td class="m-col-amount py-2.5 px-3 text-right font-mono font-bold text-emerald-700" :class="{ 'mobile-hidden': !isExpanded }" data-label="Thành tiền:" x-text="formatNumber((st.stock_quantity || 0) * (st.price || st.product?.price || 0)) + ' đ'"></td>
                                <td class="m-col-location py-2.5 px-3 text-center text-slate-500 italic text-[11px]" :class="{ 'mobile-hidden': !isExpanded }" data-label="Vị trí lưu:" x-text="st.location || 'Hàng ôm NV'"></td>
                                <td class="m-col-actions py-2.5 px-4 text-center whitespace-nowrap" :class="{ 'mobile-hidden': !isExpanded }">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Nút 1: Bán nhanh tại chỗ (Mở Modal Bán Nhanh không mất trang) -->
                                        <button type="button" @click="openQuickSaleModal(st)"
                                                class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-2xs transition-colors cursor-pointer inline-flex items-center gap-1">
                                            <i class="fa-solid fa-bolt text-[10px]"></i>
                                            <span>Bán nhanh</span>
                                        </button>

                                        <!-- Nút 2: Mở trang bán hàng đầy đủ (cùng tab, có kèm return_url) -->
                                        <a :href="'/inventory/holding-sale/create?warehouse_id=' + (selectedHoldingWhId || '') + '&dealer_id={{ $order->dealer_id ?? '' }}&product_id=' + (st.product_id || '') + '&return_url=' + encodeURIComponent(window.location.href)"
                                           class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-[#1b4a8c] rounded-lg text-xs font-bold border border-blue-200 transition-colors cursor-pointer inline-flex items-center gap-1 no-underline"
                                           title="Mở form bán hàng chi tiết">
                                            <i class="fa-solid fa-cart-plus text-[10px]"></i>
                                            <span>Trang bán</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- TRẠNG THÁI RỖNG HOẶC KHÔNG TÌM THẤY SẢN PHẨM -->
                        <template x-if="getFilteredHoldingStocks().length === 0">
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-boxes-packing text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-xs font-semibold" x-text="holdingSearch ? 'Không tìm thấy sản phẩm nào khớp với từ khóa tìm kiếm.' : 'Nhân viên / kho này hiện chưa có sản phẩm ôm nào sẵn sàng để bán.'"></p>
                                    <template x-if="holdingSearch">
                                        <button type="button" @click="holdingSearch = ''" class="mt-2 text-blue-600 font-bold hover:underline text-xs cursor-pointer">
                                            Xóa tìm kiếm để xem tất cả
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL BÁN NHANH TRỰC TIẾP TỪ KHO HÀNG ÔM                                    -->
    <!-- ========================================================================= -->
    <div x-show="showQuickSaleModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl max-w-lg w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden" @click.away="showQuickSaleModal = false">
            <!-- Header -->
            <div class="p-3.5 sm:p-4 pb-3 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm sm:text-base">Bán Nhanh Từ Kho Hàng Ôm</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Bán bổ sung ngay tại điểm giao hàng</p>
                    </div>
                </div>
                <button type="button" @click="showQuickSaleModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer w-7 h-7 flex items-center justify-center">&times;</button>
            </div>

            <!-- Body (Cuộn mượt mà trên mobile) -->
            <template x-if="quickSaleProduct">
                <div class="p-3.5 sm:p-4 overflow-y-auto flex-1 space-y-3 text-xs">
                    <!-- Thông tin Khách hàng & Kho xuất (gọn 1 dòng) -->
                    <div class="px-3 py-2 bg-slate-50 rounded-xl border border-slate-200 text-[11px] flex flex-wrap items-center justify-between gap-1">
                        <div>KH: <strong class="text-purple-700 font-bold">{{ $custName }}</strong></div>
                        <div class="text-slate-500 font-medium">Kho: <span class="font-bold text-slate-700" x-text="quickSaleProduct.warehouse_name"></span></div>
                    </div>

                    <!-- Thông tin sản phẩm chọn bán -->
                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-blue-700 font-bold bg-white px-1.5 py-0.5 rounded border border-blue-200" x-text="quickSaleProduct.sku"></span>
                            <span class="text-emerald-700 font-bold text-xs">Tồn còn: <strong class="font-mono" x-text="quickSaleProduct.stock_quantity"></strong> <span x-text="quickSaleProduct.unit"></span></span>
                        </div>
                        <div class="font-black text-slate-900 text-sm" x-text="quickSaleProduct.name"></div>
                    </div>

                    <!-- Số lượng bán & Đơn giá -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Số lượng bán (*):</label>
                            <div class="relative flex items-center">
                                <button type="button" @click="if (quickSaleQty > 1) quickSaleQty--"
                                        class="w-10 h-10 rounded-l-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 border border-slate-300 font-bold text-slate-700 cursor-pointer flex items-center justify-center text-base select-none">-</button>
                                <input type="number" min="1" :max="quickSaleProduct.stock_quantity"
                                       x-model.number="quickSaleQty"
                                       class="w-full h-10 px-2 bg-white border-y border-slate-300 text-center font-mono font-black text-base text-slate-900 focus:outline-none">
                                <button type="button" @click="if (quickSaleQty < quickSaleProduct.stock_quantity) quickSaleQty++"
                                        class="w-10 h-10 rounded-r-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 border border-slate-300 font-bold text-slate-700 cursor-pointer flex items-center justify-center text-base select-none">+</button>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1 flex items-center justify-between">
                                <span>Đơn vị: <strong class="text-slate-700" x-text="quickSaleProduct.unit"></strong></span>
                                <button type="button" @click="quickSaleQty = quickSaleProduct.stock_quantity" class="text-blue-600 font-bold hover:underline cursor-pointer">Bán hết tồn</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Đơn giá bán (*):</label>
                            <div class="relative">
                                <input type="number" min="0" step="1000"
                                       x-model.number="quickSalePrice"
                                       class="w-full h-10 px-3 bg-white border border-slate-300 rounded-xl font-mono font-bold text-sm text-slate-900 focus:outline-none focus:border-blue-500">
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">Đơn vị: VNĐ / <span x-text="quickSaleProduct.unit"></span></div>
                        </div>
                    </div>

                    <!-- Thành tiền tính tự động -->
                    <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                        <span class="font-bold text-emerald-900">Tổng tiền bán:</span>
                        <span class="font-mono text-base font-black text-emerald-700" x-text="formatNumber(quickSaleQty * quickSalePrice) + ' đ'"></span>
                    </div>

                    <!-- Hình thức thanh toán -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Hình thức thanh toán:</label>
                        <select x-model="quickSalePaymentMethod" class="w-full px-3 py-2.5 bg-white border border-slate-300 rounded-xl font-bold text-xs focus:outline-none focus:border-purple-500">
                            <option value="Tiền mặt">Tiền mặt (Đã thu tại chỗ)</option>
                            <option value="Chuyển khoản VietQR">Chuyển khoản VietQR (Đã chuyển)</option>
                            <option value="Ghi nợ">Ghi nợ (Cộng vào công nợ đại lý)</option>
                        </select>
                    </div>

                    <!-- Ghi chú bán -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ghi chú:</label>
                        <input type="text" x-model="quickSaleNotes" placeholder="Ghi chú thêm nếu có..."
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none">
                    </div>
                </div>
            </template>

            <!-- Footer (Cố định ở dưới, luôn thấy rõ nút bấm) -->
            <div class="p-3 sm:p-4 pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0 bg-slate-50/80" style="padding-bottom: max(12px, env(safe-area-inset-bottom, 12px));">
                <button type="button" @click="showQuickSaleModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitQuickSale()" :disabled="isSubmittingQuickSale"
                        class="flex-1 sm:flex-none justify-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer shadow-sm disabled:opacity-50">
                    <i class="fa-solid" :class="isSubmittingQuickSale ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                    <span x-text="isSubmittingQuickSale ? 'Đang tạo đơn...' : 'Xác nhận bán & Trừ kho'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL BÁO GIAO HÀNG THẤT BẠI                                              -->
    <!-- ========================================================================= -->
    <div x-show="showFailModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl max-w-lg w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden" @click.away="showFailModal = false">
            <div class="p-3.5 sm:p-4 pb-3 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm sm:text-base">Báo Giao Đơn Không Thành Công</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Ghi nhận lý do và đưa hàng trở lại xe</p>
                    </div>
                </div>
                <button type="button" @click="showFailModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer w-7 h-7 flex items-center justify-center">&times;</button>
            </div>

            <div class="p-3.5 sm:p-4 overflow-y-auto flex-1 space-y-3 text-xs">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Lý do không giao được (*):</label>
                    <select x-model="failReason" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none">
                        <option value="Khách hẹn giao lại vào ngày mai">Khách hẹn giao lại vào ngày mai</option>
                        <option value="Không liên lạc được khách hàng">Không liên lạc được khách hàng</option>
                        <option value="Cửa hàng / kho khách đóng cửa">Cửa hàng / kho khách đóng cửa</option>
                        <option value="Khách từ chối nhận hàng do sai quy cách">Khách từ chối nhận hàng do sai quy cách</option>
                        <option value="Lý do khác">Lý do khác</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ghi chú chi tiết thêm:</label>
                    <textarea x-model="failNotes" rows="2" placeholder="Ghi chú thêm lời nhắn của khách hoặc tình trạng..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none"></textarea>
                </div>
            </div>

            <div class="p-3 sm:p-4 pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0 bg-slate-50/80" style="padding-bottom: max(12px, env(safe-area-inset-bottom, 12px));">
                <button type="button" @click="showFailModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitDeliveryFailure()" :disabled="isSubmittingDelivery"
                        class="flex-1 sm:flex-none justify-center px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer shadow-sm">
                    <i class="fa-solid fa-check"></i>
                    <span>Xác nhận không thành công</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function deliveryConfirmPage() {
    @php
        $defaultBank = \App\Models\CompanyBankAccount::default()->first() ?? \App\Models\CompanyBankAccount::first();
    @endphp
    return {
        bankCode: '{{ $defaultBank?->bank_code ?? "" }}',
        bankName: '{{ $defaultBank?->bank_name ?? "" }}',
        bankAccount: '{{ $defaultBank?->account_number ?? "" }}',
        bankHolder: '{{ $defaultBank?->account_holder ?? "" }}',
        orderId: {{ $order->id }},
        orderCode: '{{ $order->order_code }}',
        customerCode: '{{ $custCode }}',
        customerName: {!! json_encode($custName) !!},
        vehiclePlate: '{{ $vehiclePlate ?? "" }}',
        previousDebt: {{ $prevDebt }},
        creditLimit: {{ $cLimit }},

        // --- Credit Overrun State ---
        overrunMode: 'split',         // 'split' | 'approval'
        overrunStatus: '{{ $initStatus }}',    // '' | 'Chờ duyệt' | 'Đã duyệt' | 'Từ chối'
        overrunApprover: '{{ $initApprover }}',
        overrunApprovedAt: '{{ $initApprovedAt }}',
        splitCollectMethod: 'Tiền mặt',
        customSplitCollectAmount: {{ max(0, ($prevDebt + $origTotal) - $cLimit) }},
        isSendingRequest: false,
        isApprovingOverride: false,
        approvalNote: '',
        _pollInterval: null,

        toast: { show: false, message: '', type: 'success' },
        copiedField: '',
        isSubmittingDelivery: false,
        showFailModal: false,
        failReason: 'Khách hẹn giao lại vào ngày mai',
        failNotes: '',

        // --- Tồn Kho Hàng Ôm & Bán Hàng Trực Tiếp ---
        employeeWarehouses: @json($employeeWarehouses ?? []),
        selectedHoldingWhId: {{ $defaultWhId ? (int)$defaultWhId : "''" }},
        activeMainTab: (new URLSearchParams(window.location.search)).get('tab') === 'holding' ? 'holding' : 'delivery',
        holdingSearch: '',

        // Quick Sale Modal State
        showQuickSaleModal: false,
        quickSaleProduct: null,
        quickSaleQty: 1,
        quickSalePrice: 0,
        quickSalePaymentMethod: 'Tiền mặt',
        quickSaleNotes: '',
        isSubmittingQuickSale: false,

        confirmForm: {
            orderId: {{ $order->id }},
            orderCode: '{{ $order->order_code }}',
            recipientName: {!! json_encode($orderCustName ?: $custName) !!},
            deliveryNotes: '',
            hasReturns: {{ $order->has_returns ? 'true' : 'false' }},
            paymentMethod: '{{ $order->payment_method ?: "Ghi nợ" }}',
            paymentStatus: '{{ $order->payment_status ?: "Ghi nợ" }}',
            collectedAmount: {{ $origTotal }},
            finalAmount: {{ $origTotal }},
            isDebt: true,
            isCombined: false,
            combinedCash: 0,
            combinedBank: 0,
            combinedDebt: 0,
            handoverToUserId: '',
            items: []
        },

        get combinedTotalAllocated() {
            return (Number(this.confirmForm.combinedCash) || 0) + (Number(this.confirmForm.combinedBank) || 0) + (Number(this.confirmForm.combinedDebt) || 0);
        },

        get combinedRemaining() {
            return Math.round(Number(this.confirmForm.finalAmount || 0) - this.combinedTotalAllocated);
        },

        get combinedVietQrUrl() {
            const amt = Math.round(Number(this.confirmForm.combinedBank) || 0);
            const memo = encodeURIComponent(this.qrTransferMemo);
            const bank = this.bankCode || 'MB';
            const acc = (this.bankAccount || '').replace(/[^0-9]/g, '');
            const holder = encodeURIComponent(this.bankHolder || '');
            return `https://img.vietqr.io/image/${bank}-${acc}-compact2.png?amount=${amt}&addInfo=${memo}&accountName=${holder}`;
        },

        fillCombinedRemaining(target) {
            const rem = this.combinedRemaining;
            if (target === 'cash') {
                this.confirmForm.combinedCash = Math.max(0, (Number(this.confirmForm.combinedCash) || 0) + rem);
            } else if (target === 'bank') {
                this.confirmForm.combinedBank = Math.max(0, (Number(this.confirmForm.combinedBank) || 0) + rem);
            } else if (target === 'debt') {
                this.confirmForm.combinedDebt = Math.max(0, (Number(this.confirmForm.combinedDebt) || 0) + rem);
            }
            this.syncCombinedPaymentStatus();
        },

        setCombinedPreset(type) {
            const tot = Number(this.confirmForm.finalAmount) || 0;
            if (type === 'cash_bank') {
                const half = Math.round(tot / 2);
                this.confirmForm.combinedCash = half;
                this.confirmForm.combinedBank = tot - half;
                this.confirmForm.combinedDebt = 0;
            } else if (type === 'cash_debt') {
                const half = Math.round(tot / 2);
                this.confirmForm.combinedCash = half;
                this.confirmForm.combinedBank = 0;
                this.confirmForm.combinedDebt = tot - half;
            } else if (type === 'bank_debt') {
                const half = Math.round(tot / 2);
                this.confirmForm.combinedCash = 0;
                this.confirmForm.combinedBank = half;
                this.confirmForm.combinedDebt = tot - half;
            }
            this.syncCombinedPaymentStatus();
        },

        syncCombinedPaymentStatus() {
            const debt = Number(this.confirmForm.combinedDebt) || 0;
            const cash = Number(this.confirmForm.combinedCash) || 0;
            const bank = Number(this.confirmForm.combinedBank) || 0;
            if (debt <= 0) {
                this.confirmForm.paymentStatus = 'Đã thanh toán';
            } else if (cash > 0 || bank > 0) {
                this.confirmForm.paymentStatus = 'Thanh toán một phần';
            } else {
                this.confirmForm.paymentStatus = 'Ghi nợ';
            }
        },

        // Computed: vượt hạn mức?
        get isOverLimit() {
            if (this.creditLimit <= 0) return false;
            const isDebtMode = this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ';
            if (!isDebtMode) return false;
            return (this.previousDebt + this.confirmForm.finalAmount) > this.creditLimit;
        },

        // Computed: số tiền vượt tối thiểu cần thu để không vượt trần hạn mức
        get excessAmount() {
            if (!this.isOverLimit) return 0;
            return Math.max(0, (this.previousDebt + this.confirmForm.finalAmount) - this.creditLimit);
        },

        // Computed: số tiền thực tế thu ngay (mặc định là excessAmount, có thể nhập nhiều hơn)
        get splitCollectAmountActual() {
            if (this.customSplitCollectAmount !== null && this.customSplitCollectAmount !== '' && !isNaN(this.customSplitCollectAmount)) {
                return Math.max(0, Number(this.customSplitCollectAmount));
            }
            return this.excessAmount;
        },

        // Computed: số tiền trong hạn mức còn ghi nợ đơn này
        get allowedDebt() {
            return Math.max(0, this.confirmForm.finalAmount - this.splitCollectAmountActual);
        },

        // Computed: số tiền thu vượt giá trị đơn hàng (cấn trừ trực tiếp nợ cũ đại lý)
        get surplusOverOrder() {
            return Math.max(0, this.splitCollectAmountActual - this.confirmForm.finalAmount);
        },

        // Computed: dự tính tổng công nợ đại lý sau khi hoàn tất giao
        get projectedTotalDebt() {
            return Math.max(0, this.previousDebt + this.confirmForm.finalAmount - this.splitCollectAmountActual);
        },

        // Computed: VietQR URL cho số tiền thu thực tế khi split payment
        get splitVietQrUrl() {
            const amt = Math.round(this.splitCollectAmountActual);
            const memo = encodeURIComponent(this.qrTransferMemo + ' THU');
            const bank = this.bankCode || 'MB';
            const acc = (this.bankAccount || '').replace(/[^0-9]/g, '');
            const holder = encodeURIComponent(this.bankHolder || '');
            return `https://img.vietqr.io/image/${bank}-${acc}-compact2.png?amount=${amt}&addInfo=${memo}&accountName=${holder}`;
        },

        // Computed: button submit bị block?
        get isSubmitBlocked() {
            if (!this.isOverLimit) return false;
            if (this.overrunMode === 'approval' && this.overrunStatus !== 'Đã duyệt') return true;
            if (this.overrunMode === 'split' && this.splitCollectAmountActual < this.excessAmount) return true;
            return false;
        },

        // Computed: nhãn nút submit
        get submitBtnLabel() {
            if (this.isSubmittingDelivery) return 'Đang lưu kết quả giao...';
            const isDebt = this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ';
            const isComb = this.confirmForm.paymentMethod === 'Thanh toán kết hợp';
            if (this.isOverLimit) {
                if (this.overrunMode === 'split') {
                    if (this.splitCollectAmountActual < this.excessAmount) {
                        return `Chưa đủ mức thu tối thiểu (${this.formatNumber(this.excessAmount)} đ)`;
                    }
                    const debtPart = this.allowedDebt > 0 
                        ? ` + Ký nợ ${this.formatNumber(this.allowedDebt)} đ` 
                        : (this.surplusOverOrder > 0 ? ` (Trừ nợ cũ ${this.formatNumber(this.surplusOverOrder)} đ)` : ' (Không nợ)');
                    return `Xác nhận: Thu ${this.formatNumber(this.splitCollectAmountActual)} đ${debtPart}`;
                }
                if (this.overrunStatus === 'Đã duyệt') {
                    return `Đã duyệt bảo lãnh — Xác nhận giao & Ghi nợ`;
                }
                return `⏳ Chờ duyệt bảo lãnh — Không thể xác nhận`;
            }
            if (isComb) return 'Xác nhận giao hàng (Đã phân bổ đủ)';
            return isDebt ? 'Xác nhận giao đơn & Ký nợ đại lý' : 'Xác nhận giao hàng & Thu tiền';
        },

        get submitBtnLabelMobile() {
            if (this.isSubmittingDelivery) return 'Đang lưu kết quả...';
            const isDebt = this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ';
            const isComb = this.confirmForm.paymentMethod === 'Thanh toán kết hợp';
            if (this.isOverLimit) {
                if (this.overrunMode === 'split') {
                    if (this.splitCollectAmountActual < this.excessAmount) {
                        return `Thiếu mức thu tối thiểu`;
                    }
                    return `Thu ${this.formatNumber(this.splitCollectAmountActual)} đ + Ký nợ`;
                }
                if (this.overrunStatus === 'Đã duyệt') {
                    return `Đã duyệt — Xác nhận giao đơn`;
                }
                return `⏳ Chờ duyệt bảo lãnh nợ`;
            }
            if (isComb) return 'Xác nhận giao kết hợp';
            return isDebt ? 'Xác nhận giao & Ký nợ' : 'Xác nhận giao & Thu tiền';
        },

        initData() {
            @php
                $itemsJson = json_encode($order->items->map(function($it) {
                    return [
                        'id' => $it->id,
                        'order_item_id' => $it->id,
                        'product_id' => $it->product_id,
                        'sku' => $it->sku,
                        'name' => $it->name,
                        'unit' => $it->unit ?: 'cái',
                        'quantity' => (float)$it->quantity,
                        'unit_price' => (float)$it->unit_price,
                        'amount' => (float)$it->amount,
                        'returnedQty' => 0,
                        'returnReason' => ''
                    ];
                }));
            @endphp
            this.confirmForm.items = {!! $itemsJson !!};

            const origTotal = {{ $origTotal }};
            const currentPm = '{{ $order->payment_method ?: "Ghi nợ" }}';
            const currentPs = '{{ $order->payment_status ?: "Ghi nợ" }}';

            if (currentPm === 'Ghi nợ' || currentPm === 'Ghi nợ đại lý' || currentPs === 'Ghi nợ' || currentPs === 'Ghi nợ đại lý') {
                this.confirmForm.paymentMethod = 'Ghi nợ';
                this.confirmForm.paymentStatus = 'Ghi nợ';
                this.confirmForm.isDebt = true;
                this.confirmForm.collectedAmount = 0;
            } else if (currentPm.includes('VietQR') || currentPm.includes('Chuyển khoản')) {
                this.confirmForm.paymentMethod = 'Chuyển khoản VietQR';
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = origTotal;
            } else {
                this.confirmForm.paymentMethod = 'Tiền mặt';
                this.confirmForm.paymentStatus = currentPs || 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = origTotal;
            }

            this.recalcReturnTotals();

            // Bắt đầu polling nếu đang chờ duyệt
            if (this.overrunStatus === 'Chờ duyệt') {
                this.startPolling();
            }

            // Lắng nghe sự kiện Realtime khi người lên đơn hoặc kế toán phê duyệt/từ chối
            window.addEventListener('erp:credit-override-decided', (e) => {
                const detail = e.detail;
                if (!detail) return;
                const extra = detail.extraData || {};
                const evOrderId = detail.orderId || extra.orderId;
                if (evOrderId && String(evOrderId) === String(this.orderId)) {
                    const prev = this.overrunStatus;
                    this.overrunStatus = extra.status || (extra.decision === 'approve' ? 'Đã duyệt' : 'Từ chối');
                    this.overrunApprover = extra.approverName || '';
                    this.overrunApprovedAt = extra.approvedAt || '';
                    if (this._pollInterval) {
                        clearInterval(this._pollInterval);
                        this._pollInterval = null;
                    }
                    if (this.overrunStatus === 'Đã duyệt' && prev !== 'Đã duyệt') {
                        this.showToast('✅ Đã được phê duyệt bảo lãnh! Lái xe có thể xác nhận giao đơn.', 'success');
                    } else if (this.overrunStatus === 'Từ chối' && prev !== 'Từ chối') {
                        this.showToast('❌ Yêu cầu bảo lãnh nợ bị từ chối.', 'error');
                    }
                }
            });
        },

        startPolling() {
            if (this._pollInterval) return;
            this._pollInterval = setInterval(async () => {
                if (this.overrunStatus === 'Đã duyệt' || this.overrunStatus === 'Từ chối') {
                    clearInterval(this._pollInterval);
                    this._pollInterval = null;
                    return;
                }
                try {
                    const res = await fetch(`/delivery/orders/${this.orderId}/credit-override-status`);
                    const data = await res.json();
                    if (data.success && data.status) {
                        const prev = this.overrunStatus;
                        this.overrunStatus = data.status;
                        this.overrunApprover = data.approvedByName || '';
                        this.overrunApprovedAt = data.approvedAt || '';
                        if (data.status === 'Đã duyệt' && prev !== 'Đã duyệt') {
                            this.showToast('✅ Đã được phê duyệt bảo lãnh! Có thể xác nhận giao đơn.', 'success');
                            clearInterval(this._pollInterval); this._pollInterval = null;
                        } else if (data.status === 'Từ chối' && prev !== 'Từ chối') {
                            this.showToast('❌ Yêu cầu bị từ chối. Vui lòng chọn phương án khác.', 'error');
                            clearInterval(this._pollInterval); this._pollInterval = null;
                        }
                    }
                } catch(e) {}
            }, 5000);
        },

        async sendOverrideRequest() {
            this.isSendingRequest = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/delivery/orders/${this.orderId}/request-credit-override`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        overrunAmount: this.excessAmount,
                        reason: `Đại lý ${this.customerName} xin nợ vượt hạn mức ${this.formatNumber(this.excessAmount)} đ khi giao đơn ${this.orderCode}`
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.overrunStatus = 'Chờ duyệt';
                    this.showToast('📨 Đã gửi yêu cầu realtime tới tài khoản lên đơn!');
                    this.startPolling();
                    // Phát sóng ngay lập tức tới mọi tab mở
                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({
                            type: 'CREDIT_OVERRUN_REQUESTED',
                            orderCode: this.orderCode,
                            orderId: this.orderId,
                            extraData: {
                                orderId: this.orderId,
                                orderCode: this.orderCode,
                                customerName: this.customerName,
                                driverName: '{{ auth()->user()?->name ?? "Lái xe giao hàng" }}',
                                overrunAmount: this.excessAmount,
                                reason: `Đại lý ${this.customerName} xin nợ vượt hạn mức ${this.formatNumber(this.excessAmount)} đ khi giao đơn ${this.orderCode}`
                            }
                        });
                    } catch(e) {}
                    localStorage.setItem('erp_order_sync_trigger', Date.now());
                } else {
                    this.showToast('Lỗi: ' + (data.message || 'Không thể gửi yêu cầu'), 'error');
                }
            } catch(e) {
                this.showToast('Lỗi kết nối: ' + e.message, 'error');
            } finally {
                this.isSendingRequest = false;
            }
        },

        async approveOverride(decision) {
            this.isApprovingOverride = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/delivery/orders/${this.orderId}/approve-credit-override`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ decision: decision, note: this.approvalNote })
                });
                const data = await res.json();
                if (data.success) {
                    this.overrunStatus = data.status;
                    this.overrunApprover = data.approvedByName || '';
                    this.overrunApprovedAt = data.approvedAt || '';
                    const msg = decision === 'approve'
                        ? '✅ Đã phê duyệt bảo lãnh thành công! Lái xe có thể xác nhận giao đơn.'
                        : '❌ Đã từ chối yêu cầu bảo lãnh.';
                    this.showToast(msg, decision === 'approve' ? 'success' : 'error');
                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({ type: 'CREDIT_OVERRUN_DECIDED', orderId: this.orderId, decision: decision });
                    } catch(e) {}
                    localStorage.setItem('erp_order_sync_trigger', Date.now());
                } else {
                    this.showToast('Lỗi: ' + (data.message || 'Thao tác thất bại'), 'error');
                }
            } catch(e) {
                this.showToast('Lỗi kết nối: ' + e.message, 'error');
            } finally {
                this.isApprovingOverride = false;
            }
        },

        selectPaymentMode(mode) {
            if (mode === 'Ghi nợ') {
                this.confirmForm.paymentMethod = 'Ghi nợ';
                this.confirmForm.paymentStatus = 'Ghi nợ';
                this.confirmForm.isDebt = true;
                this.confirmForm.isCombined = false;
                this.confirmForm.collectedAmount = 0;
            } else if (mode === 'VietQR') {
                this.confirmForm.paymentMethod = 'Chuyển khoản VietQR';
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.isCombined = false;
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            } else if (mode === 'Thanh toán kết hợp' || mode === 'Kết hợp') {
                this.confirmForm.paymentMethod = 'Thanh toán kết hợp';
                this.confirmForm.isDebt = false;
                this.confirmForm.isCombined = true;
                if (this.combinedTotalAllocated === 0) {
                    this.setCombinedPreset('cash_bank');
                } else {
                    this.syncCombinedPaymentStatus();
                }
            } else {
                this.confirmForm.paymentMethod = 'Tiền mặt';
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.isCombined = false;
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            }
        },

        onPaymentMethodSelectChange() {
            const pm = this.confirmForm.paymentMethod;
            if (pm === 'Ghi nợ') {
                this.confirmForm.paymentStatus = 'Ghi nợ';
                this.confirmForm.isDebt = true;
                this.confirmForm.isCombined = false;
                this.confirmForm.collectedAmount = 0;
            } else if (pm === 'Chuyển khoản VietQR') {
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.isCombined = false;
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            } else if (pm === 'Thanh toán kết hợp') {
                this.selectPaymentMode('Thanh toán kết hợp');
            } else {
                this.confirmForm.isDebt = false;
                this.confirmForm.isCombined = false;
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            }
        },

        onHasReturnsToggle() {
            if (!this.confirmForm.hasReturns) {
                this.confirmForm.items.forEach(it => {
                    it.returnedQty = 0;
                    it.returnReason = '';
                });
            }
            this.recalcReturnTotals();
        },

        validateReturnQty(it) {
            const maxQty = Number(it.quantity || 0);
            if (it.returnedQty === null || it.returnedQty === undefined || it.returnedQty === '') {
                this.recalcReturnTotals();
                return;
            }
            let val = Number(it.returnedQty);
            if (isNaN(val) || val < 0) {
                it.returnedQty = 0;
            } else if (val > maxQty) {
                it.returnedQty = maxQty;
                this.showToast(`⛔ Số lượng trả không được vượt quá số lượng giao (${this.formatNumber(maxQty)} ${it.unit})!`, 'error');
            }
            this.recalcReturnTotals();
        },

        get returnTotalAmount() {
            if (!this.confirmForm.hasReturns) return 0;
            return this.confirmForm.items.reduce((sum, it) => {
                const maxQty = Number(it.quantity || 0);
                const retQty = Math.min(Math.max(0, Number(it.returnedQty || 0)), maxQty);
                return sum + (retQty * Number(it.unit_price || 0));
            }, 0);
        },

        recalcReturnTotals() {
            const retAmt = this.returnTotalAmount;
            const origTotal = {{ $origTotal }};
            this.confirmForm.finalAmount = Math.max(0, origTotal - retAmt);
            if (!this.confirmForm.isDebt && this.confirmForm.paymentMethod !== 'Ghi nợ') {
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            }
            if (this.isOverLimit && (!this.customSplitCollectAmount || this.customSplitCollectAmount <= 0)) {
                this.customSplitCollectAmount = this.excessAmount;
            }
        },

        get qrTransferMemo() {
            return `${this.customerCode} ${this.orderCode}`.trim();
        },

        get vietQrUrl() {
            const amt = Math.round(Number(this.confirmForm.collectedAmount || this.confirmForm.finalAmount || 0));
            const memo = encodeURIComponent(this.qrTransferMemo);
            const bank = this.bankCode || 'MB';
            const acc = (this.bankAccount || '').replace(/[^0-9]/g, '');
            const holder = encodeURIComponent(this.bankHolder || '');
            return `https://img.vietqr.io/image/${bank}-${acc}-compact2.png?amount=${amt}&addInfo=${memo}&accountName=${holder}`;
        },

        formatNumber(val) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val) || 0));
        },

        orderStatusBadgeClass() {
            const s = '{{ $displayStatus }}';
            if (s === 'Đã giao' || s === 'Giao thành công') return 'bg-emerald-50 text-emerald-800 border border-emerald-300';
            if (s === 'Đang vận chuyển') return 'bg-sky-50 text-sky-800 border border-sky-300';
            return 'bg-amber-50 text-amber-800 border border-amber-300';
        },

        async copyToClipboard(text, fieldName = '') {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(String(text));
                } else {
                    const textArea = document.createElement("textarea");
                    textArea.value = String(text);
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                }
                this.copiedField = fieldName;
                this.showToast('📋 Đã sao chép: ' + text);
                setTimeout(() => { this.copiedField = ''; }, 2000);
            } catch (err) {
                this.showToast('Không thể sao chép: ' + err.message, 'error');
            }
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 4500);
        },

        async submitDeliveryCompletion() {
            if (!this.confirmForm.recipientName || !this.confirmForm.recipientName.trim()) {
                alert('Vui lòng nhập họ tên người đại diện đại lý ký nhận hàng.');
                return;
            }

            // Kiểm tra số lượng trả không được vượt quá số lượng giao
            if (this.confirmForm.hasReturns) {
                for (const it of this.confirmForm.items) {
                    const retQty = Number(it.returnedQty || 0);
                    const maxQty = Number(it.quantity || 0);
                    if (retQty > maxQty) {
                        alert(`⛔ Số lượng trả lại của mặt hàng "${it.name}" (${this.formatNumber(retQty)} ${it.unit}) không được vượt quá số lượng giao (${this.formatNumber(maxQty)} ${it.unit})!`);
                        it.returnedQty = maxQty;
                        this.recalcReturnTotals();
                        return;
                    }
                    if (retQty < 0) {
                        alert(`⛔ Số lượng trả lại của mặt hàng "${it.name}" không thể là số âm!`);
                        it.returnedQty = 0;
                        this.recalcReturnTotals();
                        return;
                    }
                }
            }

            const isDebt = this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ';
            const isSplit = isDebt && this.isOverLimit && this.overrunMode === 'split';

            // Chặn nếu cần duyệt mà chưa duyệt
            if (this.isSubmitBlocked) {
                alert('⛔ Đơn này cần được Kế toán / Giám đốc phê duyệt bảo lãnh trước khi xác nhận giao hàng.');
                return;
            }

            let actionText;
            if (isSplit) {
                if (this.splitCollectAmountActual < this.excessAmount) {
                    alert(`Số tiền thu tối thiểu phải là ${this.formatNumber(this.excessAmount)} đ để đảm bảo nợ đại lý không vượt hạn mức.`);
                    return;
                }
                const debtMsg = this.allowedDebt > 0 
                    ? `• Ghi nợ đơn này: ${this.formatNumber(this.allowedDebt)} đ\n` 
                    : `• Đơn này thanh toán đủ (0 đ ghi nợ đơn)\n`;
                const surplusMsg = this.surplusOverOrder > 0
                    ? `• Cấn trừ vào nợ cũ đại lý: ${this.formatNumber(this.surplusOverOrder)} đ\n`
                    : '';

                actionText = `Xác nhận giao đơn #${this.orderCode}?\n` +
                    `• Thu ngay (${this.splitCollectMethod}): ${this.formatNumber(this.splitCollectAmountActual)} đ\n` +
                    debtMsg + surplusMsg +
                    `• Dự tính tổng nợ đại lý sau giao: ${this.formatNumber(this.projectedTotalDebt)} đ (Hạn mức: ${this.formatNumber(this.creditLimit)} đ)`;
            } else if (this.confirmForm.paymentMethod === 'Thanh toán kết hợp') {
                if (Math.abs(this.combinedRemaining) > 100) {
                    alert('⚠️ Tổng số tiền phân bổ (Tiền mặt + Chuyển khoản + Ghi nợ) chưa khớp với tổng đơn hàng! Còn chênh lệch: ' + this.formatNumber(this.combinedRemaining) + ' đ. Vui lòng kiểm tra lại!');
                    return;
                }
                const parts = [];
                if (Number(this.confirmForm.combinedCash) > 0) parts.push(`• Tiền mặt: ${this.formatNumber(this.confirmForm.combinedCash)} đ`);
                if (Number(this.confirmForm.combinedBank) > 0) parts.push(`• Quét VietQR: ${this.formatNumber(this.confirmForm.combinedBank)} đ`);
                if (Number(this.confirmForm.combinedDebt) > 0) parts.push(`• Ký nợ đại lý: ${this.formatNumber(this.confirmForm.combinedDebt)} đ`);
                actionText = `Xác nhận giao đơn #${this.orderCode} theo phương thức THANH TOÁN KẾT HỢP:\n` + parts.join('\n');
            } else if (isDebt) {
                actionText = `Xác nhận giao đơn #${this.orderCode} và KÝ NỢ TOÀN BỘ ${this.formatNumber(this.confirmForm.finalAmount)} đ vào sổ đại lý?`;
            } else {
                actionText = `Xác nhận giao đơn #${this.orderCode} và ĐÃ THU ${this.formatNumber(this.confirmForm.collectedAmount)} đ (${this.confirmForm.paymentMethod})?`;
            }

            if (!confirm(actionText)) return;

            this.isSubmittingDelivery = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const isComb = this.confirmForm.paymentMethod === 'Thanh toán kết hợp';
                const payload = {
                    orderId: this.orderId,
                    orderCode: this.orderCode,
                    recipientName: this.confirmForm.recipientName.trim(),
                    deliveryNotes: this.confirmForm.deliveryNotes ? this.confirmForm.deliveryNotes.trim() : '',
                    paymentMethod: this.confirmForm.paymentMethod,
                    paymentStatus: this.confirmForm.paymentStatus,
                    collectedAmount: isDebt && !isSplit ? 0 : (isComb ? Number(this.confirmForm.combinedCash || 0) : Number(this.confirmForm.collectedAmount || 0)),
                    isDebt: isDebt && !isSplit,
                    hasReturns: this.confirmForm.hasReturns,
                    returnedItems: this.confirmForm.hasReturns ? this.confirmForm.items.filter(it => Number(it.returnedQty) > 0) : [],
                    // Split payment fields
                    isSplitPayment: isSplit,
                    splitCollectMethod: isSplit ? this.splitCollectMethod : null,
                    splitCollectAmount: isSplit ? this.splitCollectAmountActual : 0,
                    splitDebtAmount: isSplit ? this.allowedDebt : 0,
                    // Combined payment fields
                    isCombinedPayment: isComb,
                    combinedCash: isComb ? Number(this.confirmForm.combinedCash || 0) : 0,
                    combinedBank: isComb ? Number(this.confirmForm.combinedBank || 0) : 0,
                    combinedDebt: isComb ? Number(this.confirmForm.combinedDebt || 0) : 0,
                    handoverToUserId: this.confirmForm.handoverToUserId || null,
                };

                const res = await fetch('/delivery/complete-order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    alert('✅ Xác nhận giao hàng thành công!');
                    window.location.href = '/delivery-execution?vehicle=' + encodeURIComponent(this.vehiclePlate);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lưu kết quả giao hàng'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmittingDelivery = false;
            }
        },

        async submitDeliveryFailure() {
            if (!confirm(`Xác nhận báo giao không thành công cho đơn #${this.orderCode}?\n\n(Hàng sẽ được lưu trên xe và làm phương án xử lý hoàn kho khi xe trở về)`)) return;

            this.isSubmittingDelivery = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery/fail-order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderId: this.orderId,
                        orderCode: this.orderCode,
                        failReason: this.failReason,
                        failNotes: this.failNotes,
                        action: 'keep_on_truck'
                    })
                });

                const data = await res.json();
                if (data.success) {
                    alert('Đã ghi nhận giao không thành công cho đơn #' + this.orderCode + '. Hàng được lưu trên xe để tiếp tục chuyến.');
                    window.location.href = '/delivery-execution?vehicle=' + encodeURIComponent(this.vehiclePlate);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmittingDelivery = false;
            }
        },

        async returnOrderToWarehouseDirect() {
            if (!confirm(`Xác nhận hoàn toàn bộ hàng đơn #${this.orderCode} về kho?\n\n• Hàng được nhập lại tồn kho công ty\n• Gỡ đơn khỏi xe\n• Lần sau giao BẮT BUỘC phải bốc & kiểm hàng lại từ đầu`)) return;
            this.isSubmittingDelivery = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery/return-order-to-warehouse', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderId: this.orderId,
                        orderCode: this.orderCode,
                        reason: 'Hoàn hàng về kho sau khi giao không thành công'
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã hoàn hàng về kho thành công!');
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể hoàn kho'));
                }
            } catch(e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmittingDelivery = false;
            }
        },

        async redeliverOrderDirect() {
            if (!confirm(`Xác nhận kích hoạt giao lại đơn #${this.orderCode}?\n\nĐơn hàng sẽ chuyển sang trạng thái "Chờ bốc" để nhân viên kho tiến hành bốc và kiểm hàng lại từ đầu.`)) return;
            this.isSubmittingDelivery = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery/redeliver-order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderId: this.orderId,
                        orderCode: this.orderCode
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã kích hoạt giao lại đơn thành công! Đơn đã chuyển sang trạng thái "Chờ bốc".');
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể kích hoạt giao lại'));
                }
            } catch(e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmittingDelivery = false;
            }
        },

        // --- Methods for Tồn Kho Hàng Ôm & Bán Hàng Trực Tiếp ---
        selectHoldingWarehouse(whId) {
            this.selectedHoldingWhId = Number(whId) || whId;
            this.holdingSearch = '';
        },

        get selectedHoldingWh() {
            if (!this.employeeWarehouses || this.employeeWarehouses.length === 0) return null;
            const currentId = this.selectedHoldingWhId;
            if (!currentId) return this.employeeWarehouses[0];
            return this.employeeWarehouses.find(w => String(w.id) === String(currentId)) || this.employeeWarehouses[0];
        },

        get filteredHoldingStocks() {
            const wh = this.selectedHoldingWh;
            if (!wh || !wh.stocks) return [];
            const q = (this.holdingSearch || '').trim().toLowerCase();
            if (!q) return wh.stocks;
            return wh.stocks.filter(st => {
                const name = (st.name || '').toLowerCase();
                const sku = (st.sku || '').toLowerCase();
                return name.includes(q) || sku.includes(q);
            });
        },

        getSelectedHoldingWh() {
            return this.selectedHoldingWh;
        },

        getFilteredHoldingStocks() {
            return this.filteredHoldingStocks;
        },

        scrollToHoldingStock() {
            const el = document.getElementById('holding-stock-section');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        openQuickSaleModal(stockItem) {
            const wh = this.getSelectedHoldingWh();
            if (!wh || !stockItem) return;
            this.quickSaleProduct = {
                warehouse_id: wh.id,
                warehouse_name: wh.name,
                employee_id: wh.employee_id,
                employee_name: wh.employee_name,
                product_id: stockItem.product_id,
                name: stockItem.name,
                sku: stockItem.sku,
                unit: stockItem.unit,
                stock_quantity: Number(stockItem.stock_quantity || 0),
                price: Number(stockItem.price || stockItem.selling_price || 0)
            };
            this.quickSaleQty = 1;
            this.quickSalePrice = Number(stockItem.price || stockItem.selling_price || 0);
            this.quickSalePaymentMethod = 'Tiền mặt';
            this.quickSaleNotes = 'Bán tại điểm giao đơn #' + this.orderCode;
            this.showQuickSaleModal = true;
        },

        async submitQuickSale() {
            if (!this.quickSaleProduct) return;
            const maxStock = Number(this.quickSaleProduct.stock_quantity || 0);
            const qty = Number(this.quickSaleQty);
            if (qty <= 0) {
                alert('Số lượng bán phải lớn hơn 0!');
                return;
            }
            if (qty > maxStock) {
                alert(`Số lượng bán (${qty}) vượt quá số lượng tồn đang giữ (${maxStock})!`);
                return;
            }

            this.isSubmittingQuickSale = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const p = this.quickSaleProduct;
                const unitPrice = Number(this.quickSalePrice);
                const totalAmount = qty * unitPrice;

                const payload = {
                    warehouse: p.warehouse_name,
                    warehouse_id: p.warehouse_id,
                    employee_id: p.employee_id,
                    employee_name: p.employee_name,
                    sales_rep_id: p.employee_id,
                    salesRepId: p.employee_id,
                    order_type: 'holding_sale',
                    orderType: 'holding_sale',
                    is_holding_stock: true,
                    is_holding_stock_sale: true,
                    status: 'Đã giao hàng',
                    dealerId: {{ $order->dealer_id ?: 'null' }},
                    dealerName: this.customerName,
                    customerName: this.customerName,
                    customerPhone: '{{ $order->customer_phone ?? "" }}',
                    customerAddress: '{{ addslashes($order->customer_address ?? "") }}',
                    deliveryAddress: '{{ addslashes($order->customer_address ?? "") }}',
                    paymentMethod: this.quickSalePaymentMethod,
                    paymentStatus: this.quickSalePaymentMethod === 'Ghi nợ' || this.quickSalePaymentMethod === 'Công nợ (Ghi nợ đại lý)' ? 'Chưa thanh toán' : 'Đã thanh toán',
                    notes: this.quickSaleNotes || ('Bán từ kho ôm tại điểm giao đơn #' + this.orderCode),
                    items: [{
                        product_id: parseInt(p.product_id, 10),
                        name: p.name,
                        sku: p.sku,
                        unit: p.unit,
                        quantity: qty,
                        unitPrice: unitPrice,
                        unit_price: unitPrice,
                        discount: 0,
                        amount: totalAmount,
                        isChecked: true,
                        isPicked: true
                    }]
                };

                const res = await fetch('/api/orders/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok && (data.success || data.order_id || data.id || data.order?.id)) {
                    // Cập nhật tồn kho cục bộ ngay trên màn hình mà không cần load lại trang
                    p.stock_quantity -= qty;
                    const wh = this.getSelectedHoldingWh();
                    if (wh && wh.stocks) {
                        const targetStock = wh.stocks.find(s => String(s.product_id) === String(p.product_id));
                        if (targetStock) {
                            targetStock.stock_quantity = Math.max(0, Number(targetStock.stock_quantity) - qty);
                        }
                        wh.total_qty = wh.stocks.reduce((sum, s) => sum + Number(s.stock_quantity || 0), 0);
                        wh.total_val = wh.stocks.reduce((sum, s) => sum + (Number(s.stock_quantity || 0) * Number(s.price || 0)), 0);
                    }

                    this.showQuickSaleModal = false;
                    this.showToast('✅ Bán hàng thành công! Đã tạo đơn bán và trừ kho ôm ' + qty + ' ' + p.unit, 'success');
                } else {
                    alert('Lỗi khi tạo đơn bán hàng: ' + (data.message || data.error || 'Vui lòng thử lại'));
                }
            } catch (err) {
                alert('Lỗi kết nối: ' + err.message);
            } finally {
                this.isSubmittingQuickSale = false;
            }
        },

        printDeliverySlip() {
            window.open(`/orders/${this.orderId}/edit`, '_blank');
        }
    };
}
</script>
@endsection
