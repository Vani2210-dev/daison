@extends('layouts.app')

@section('title', ($order ? 'Chỉnh Sửa Đơn Hàng ' . $order->order_code : 'Đơn Hàng ERP Form Standard') . ' | ERP Đại Sơn')

@push('styles')
<style>
/* ==========================================================================
   TỐI ƯU GIAO DIỆN FORM ĐƠN HÀNG TRÊN MÀN HÌNH MOBILE (<= 768px)
   Tất cả thuộc tính được bọc 100% trong @media (max-width: 768px)
   => Tuyệt đối KHÔNG xung đột hay làm thay đổi giao diện Desktop (> 768px).
   ========================================================================== */
@media (max-width: 768px) {
    /* Khóa chặt toàn bộ trang theo chiều ngang, tuyệt đối không cho phép overflow / nhảy layout */
    html, body {
        max-width: 100vw !important;
        overflow-x: clip !important;
    }
    main {
        max-width: 100% !important;
        overflow-x: clip !important;
        box-sizing: border-box !important;
        padding-bottom: 0 !important; /* Gỡ bỏ padding 56px của main vì màn hình này có bottom bar riêng */
    }
    fieldset {
        min-width: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* Ẩn hoàn toàn thanh bottom navigation chung của ERP khi ở trong màn hình lên đơn / sửa đơn để không che nút Lưu */
    .mobile-bottom-nav,
    .mobile-drawer-backdrop,
    .mobile-drawer-sheet {
        display: none !important;
    }

    /* 1. Spacing & Card chính của Form */
    .order-form-app-wrapper,
    main .order-form-app-wrapper {
        padding-bottom: calc(52px + env(safe-area-inset-bottom, 0px)) !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: clip !important;
        overflow-y: visible !important;
        box-sizing: border-box !important;
    }
    .order-form-main-card {
        padding: 0.875rem 0.75rem !important;
        border-radius: 1rem !important;
        gap: 1rem !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow-x: clip !important;
        overflow-y: visible !important;
    }

    /* 2. Thanh Tabs phía trên */
    .order-form-top-tabs {
        padding: 0.5rem 0.5rem !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .order-form-top-tabs::-webkit-scrollbar {
        display: none;
    }
    .order-form-top-tabs kbd,
    .order-form-title-bar .text-xs.text-slate-500.flex,
    .shortcut-hint,
    kbd {
        display: none !important; /* Ẩn toàn bộ chú thích phím tắt trên mobile */
    }
    .order-form-title-bar h1 {
        font-size: 1.25rem !important;
        line-height: 1.25 !important;
    }

    /* 3. Ô nhập liệu thông tin đơn (Row 1 & Row 2) */
    .order-form-main-card input,
    .order-form-main-card select,
    .order-form-main-card textarea {
        font-size: 14.5px !important;
        min-height: 42px !important;
    }
    .order-form-main-card label {
        font-size: 12px !important;
        font-weight: 800 !important;
        margin-bottom: 0.35rem !important;
    }

    /* Dropdown tìm kiếm Khách hàng & Sản phẩm */
    .order-customer-dropdown {
        width: min(440px, calc(100vw - 1.5rem)) !important;
        max-width: calc(100vw - 1.5rem) !important;
        min-width: 100% !important;
        left: 0 !important;
        right: auto !important;
        border-radius: 1rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2) !important;
        font-size: 14px !important;
    }
    .order-customer-dropdown div[id^="dealer-opt-"] .font-bold.text-slate-900 {
        font-size: 15px !important;
        font-weight: 800 !important;
    }
    .order-customer-dropdown div[id^="dealer-opt-"] .text-\[10px\],
    .order-customer-dropdown div[id^="dealer-opt-"] .text-\[9\.5px\],
    .order-customer-dropdown div[id^="dealer-opt-"] .text-\[11px\] {
        font-size: 11.5px !important;
        font-weight: 700 !important;
    }
    .order-customer-dropdown div[id^="dealer-opt-"] span.font-mono {
        font-size: 12.5px !important;
        font-weight: 700 !important;
    }

    .order-product-dropdown {
        width: 100% !important;
        max-width: 100% !important;
        left: 0 !important;
        right: 0 !important;
        border-radius: 1rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2) !important;
        font-size: 14px !important;
    }
    .order-product-dropdown div[id^="prod-opt-"] .font-bold.text-slate-900 {
        font-size: 15px !important;
        font-weight: 800 !important;
    }
    .order-product-dropdown div[id^="prod-opt-"] .text-\[11px\],
    .order-product-dropdown div[id^="prod-opt-"] .text-\[10px\],
    .order-product-dropdown div[id^="prod-opt-"] .text-\[9px\] {
        font-size: 12px !important;
    }
    .order-product-dropdown div[id^="prod-opt-"] .font-black.text-sky-700 {
        font-size: 15px !important;
        font-weight: 900 !important;
    }

    /* 4. CHUYỂN BẢNG SẢN PHẨM THÀNH DẠNG CARD CỰC KỲ DỄ THAO TÁC TRÊN MOBILE */
    .order-items-table-container {
        border: none !important;
        background: transparent !important;
        overflow: visible !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table {
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table,
    .order-items-table tbody {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table thead {
        display: none !important;
    }
    .order-items-table tr.order-item-row {
        display: grid !important;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.95fr) minmax(0, 1.4fr) !important;
        grid-template-areas:
            "stt . action"
            "product product product"
            "unit qty price"
            "discount discount discount"
            "picking picking picking"
            "amount amount amount";
        gap: 0.45rem 0.5rem !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.875rem !important;
        padding: 0.75rem !important;
        margin-bottom: 0.625rem !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        position: relative !important;
    }
    .order-items-table tr.order-item-row:last-child {
        margin-bottom: 0 !important;
    }

    .order-items-table td {
        display: block !important;
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* Dòng 1: STT/Checkbox bên trái, nút Xóa bên phải */
    .order-items-table td.col-stt {
        grid-area: stt !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
    }
    .order-items-table td.col-stt .flex {
        justify-content: flex-start !important;
        gap: 0.375rem !important;
    }
    .order-items-table td.col-actions {
        grid-area: action !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        justify-self: end !important;
    }
    .order-items-table td.col-actions button {
        width: 28px !important;
        height: 28px !important;
        border-radius: 0.375rem !important;
        background: #fff1f2 !important;
        color: #e11d48 !important;
        border: 1px solid #ffe4e6 !important;
    }

    /* Dòng 2: Ô tìm & chọn sản phẩm (chiếm trọn 100% bề ngang) */
    .order-items-table td.col-product {
        grid-area: product !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        padding-top: 0.25rem !important;
        border-top: 1px dashed #f1f5f9 !important;
        overflow: visible !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-product input.product-search-input {
        height: 42px !important;
        width: 100% !important;
        max-width: 100% !important;
        font-size: 14.5px !important;
        font-weight: 700 !important;
        border-radius: 0.5rem !important;
        border-color: #cbd5e1 !important;
        box-sizing: border-box !important;
    }

    /* Dòng 3: ĐVT, Số lượng & Đơn giá (Gom gọn trên 1 hàng) */
    .order-items-table td.col-unit {
        grid-area: unit !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-unit::before {
        content: "ĐƠN VỊ TÍNH";
        display: block;
        font-size: 11px !important;
        font-weight: 800 !important;
        color: #475569 !important;
        letter-spacing: 0.02em;
        margin-bottom: 3px;
    }
    .order-items-table td.col-unit select {
        height: 40px !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        border-radius: 0.5rem !important;
        border: 1px solid #cbd5e1 !important;
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
        box-sizing: border-box !important;
    }

    .order-items-table td.col-qty {
        grid-area: qty !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-qty::before {
        content: "SỐ LƯỢNG";
        display: block;
        font-size: 11px !important;
        font-weight: 800 !important;
        color: #475569 !important;
        letter-spacing: 0.02em;
        margin-bottom: 3px;
        text-align: center;
    }
    .order-items-table td.col-qty input {
        height: 40px !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.5rem !important;
        font-size: 16px !important;
        font-weight: 900 !important;
        text-align: center !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
        padding: 0 0.25rem !important;
        box-sizing: border-box !important;
    }

    .order-items-table td.col-price {
        grid-area: price !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-price::before {
        content: "ĐƠN GIÁ (Đ)";
        display: block;
        font-size: 11px !important;
        font-weight: 800 !important;
        color: #475569 !important;
        letter-spacing: 0.02em;
        margin-bottom: 3px;
        text-align: right;
    }
    .order-items-table td.col-price input {
        height: 40px !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        font-size: 14.5px !important;
        font-weight: 800 !important;
        border-radius: 0.5rem !important;
        border: 1px solid #cbd5e1 !important;
        text-align: right !important;
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
        box-sizing: border-box !important;
    }

    /* Ẩn triệt để các dòng quà tặng khỏi bảng sản phẩm chính, hoàn toàn không chiếm layout */
    .order-items-table tr.order-item-row.is-promo-gift,
    .order-items-table tr.order-item-row[style*="display: none"] {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        max-width: 0 !important;
        max-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        position: absolute !important;
        overflow: hidden !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* Dòng 4: Chiết khấu & VAT (Gom nhãn và các ô nhập lên 1 hàng) */
    .order-items-table td.col-discount {
        grid-area: discount !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.5rem !important;
        min-width: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-discount::before {
        content: "CHIẾT KHẤU:";
        display: inline-block !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        color: #0284c7 !important;
        letter-spacing: 0.025em !important;
        margin-bottom: 0 !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    .order-items-table td.col-discount > div {
        flex: 1 1 auto !important;
        min-width: 0 !important;
    }
    .order-items-table td.col-discount [x-show*="gift_quantity"] {
        display: none !important;
    }
    .order-items-table td.col-discount > div > .flex {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.375rem !important;
        min-width: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-discount > div > .flex > div:first-child {
        display: flex !important;
        align-items: center !important;
        gap: 0.2rem !important;
        flex: 0 0 auto !important;
        width: auto !important;
    }
    .order-items-table td.col-discount > div > .flex > div:first-child input {
        width: 58px !important;
        flex: 0 0 auto !important;
    }
    .order-items-table td.col-discount > div > .flex > div:last-child {
        display: flex !important;
        align-items: center !important;
        gap: 0.25rem !important;
        flex: 1 1 0% !important;
        width: auto !important;
        min-width: 0 !important;
    }
    .order-items-table td.col-discount > div > .flex > div:last-child input {
        width: 100% !important;
        flex: 1 1 auto !important;
        min-width: 0 !important;
    }
    .order-items-table td.col-discount input {
        height: 40px !important;
        min-height: 40px !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        border-radius: 0.5rem !important;
        border-color: #cbd5e1 !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-discount span {
        font-size: 13.5px !important;
        font-weight: 800 !important;
    }

    .order-items-table td.col-vat,
    .order-items-table th.col-vat {
        display: none !important;
    }

    /* Dòng 6 (Nếu có): SL Đã Bốc */
    .order-items-table td.col-picking {
        grid-area: picking !important;
        padding-top: 0.35rem !important;
        border-top: 1px dashed #f1f5f9 !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-picking::before {
        content: "TIẾN ĐỘ BỐC HÀNG: ";
        display: inline-block;
        font-size: 11px !important;
        font-weight: 800 !important;
        color: #ea580c;
        margin-right: 4px;
    }

    /* Dòng 7: Thành tiền nổi bật ở đáy card */
    .order-items-table td.col-amount {
        grid-area: amount !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding-top: 0.5rem !important;
        border-top: 1px dashed #cbd5e1 !important;
        margin-top: 0.2rem !important;
        min-width: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .order-items-table td.col-amount::before {
        content: "THÀNH TIỀN:";
        font-size: 13px !important;
        font-weight: 900 !important;
        color: #1e293b !important;
    }
    .order-items-table td.col-amount > span {
        font-size: 18px !important;
        font-weight: 900 !important;
        color: #0284c7 !important;
    }

    /* 5. Nút Thêm dòng dưới bảng */
    .order-under-table-actions {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.5rem !important;
    }
    .order-under-table-actions button {
        width: 100% !important;
        justify-content: center !important;
        height: 40px !important;
    }
    .order-under-table-actions .text-\[11px\] {
        display: none !important; /* Ẩn gợi ý phím tắt trên điện thoại */
    }

    /* 6. Khối Hàng Khuyến Mại trên mobile: Triệt để ẩn bảng desktop và fit gọn gàng 100% */
    #promo-section {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow: visible !important;
    }
    #promo-section .promo-desktop-table,
    #promo-section .hidden.md\:block,
    #promo-section table {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        max-width: 0 !important;
        max-height: 0 !important;
        overflow: hidden !important;
        visibility: hidden !important;
        position: absolute !important;
        pointer-events: none !important;
    }
    #promo-section input,
    #promo-section select {
        max-width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
    }

    /* 7. Thanh Footer Thao Tác Cố Định (Sticky Bottom Bar) */
    .order-form-bottom-bar {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 99999 !important;
        padding: 0.375rem 0.75rem calc(0.375rem + env(safe-area-inset-bottom, 0px)) 0.75rem !important;
        flex-direction: column !important;
        gap: 0.25rem !important;
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(12px) !important;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.12) !important;
    }
    .order-form-bottom-bar > div:first-child {
        width: 100% !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding-bottom: 0.125rem !important;
    }
    .order-form-bottom-bar > div:last-child {
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.375rem !important;
    }
    .order-form-bottom-bar > div:last-child > div {
        flex: 1 1 auto !important;
        display: flex !important;
        gap: 0.375rem !important;
        width: 100% !important;
    }
    .order-form-bottom-bar > div:first-child span.uppercase {
        font-size: 14px !important;
        font-weight: 900 !important;
    }
    .order-form-bottom-bar > div:first-child .text-sky-600 {
        font-size: 18px !important;
        font-weight: 900 !important;
    }
    .btn-save-print {
        display: none !important;
    }
    .order-form-bottom-bar button,
    .order-form-bottom-bar a {
        flex: 1 1 auto !important;
        height: 44px !important;
        font-size: 14.5px !important;
        font-weight: 800 !important;
        padding: 0 0.75rem !important;
        justify-content: center !important;
        white-space: nowrap !important;
    }
    .order-form-bottom-bar button:first-child {
        flex: 0 0 auto !important;
        min-width: 90px !important;
        padding: 0 0.75rem !important;
    }
    .order-form-bottom-bar > div:last-child > div > button {
        flex: 1 1 auto !important;
        width: 100% !important;
    }
}
</style>
@endpush

@section('content')
@php
    $orderTier = ($order && !empty($order->dealer_tier)) ? $order->dealer_tier : ($order?->dealer?->tier ?: 'Nguyên Giá');
    $orderDiscPct = 0;
    if ($order) {
        if ($order->discount_percent !== null && (float)$order->discount_percent > 0) {
            $orderDiscPct = (float)$order->discount_percent;
        } elseif ((float)$order->discount_amount > 0 && (float)$order->subtotal > 0) {
            $orderDiscPct = round(((float)$order->discount_amount / (float)$order->subtotal) * 100, 2);
        }
    }
    $initialPolicyIds = [];
    if ($order) {
        if (is_array($order->sales_policy_ids) && count($order->sales_policy_ids) > 0) {
            $initialPolicyIds = array_values(array_map('strval', $order->sales_policy_ids));
        } elseif (!empty($order->sales_policy_id)) {
            $initialPolicyIds = [(string)$order->sales_policy_id];
        }
    }
    $initialItems = [];
    if ($order && $order->items && count($order->items) > 0) {
        foreach ($order->items as $it) {
            $qty = (float) $it->quantity;
            $giftQty = (float) ($it->gift_quantity ?? 0);
            $price = (float) $it->unit_price;
            $costPrice = (float) ($it->cost_price ?? ($it->product?->cost_price ?? 0));
            $prodPrice = (float) ($it->product?->price ?: 0);
            if ($price > 0 && $prodPrice > 0 && ($price == $costPrice || $price == (float)($it->product?->cost_price ?? 0))) {
                $price = $prodPrice;
            }
            $amt = (float) $it->amount;
            $tierInput = (string) ($it->applied_tier_policy ?? '');
            $tierLower = mb_strtolower(trim($tierInput));
            $nameLower = mb_strtolower(trim($it->name ?? ''));
            $itTier = ($tierInput && $tierInput !== 'Theo phiếu') ? $tierInput : $orderTier;

            $isPureGift = (
                $tierInput === 'KM' ||
                str_contains($tierLower, 'km') ||
                str_contains($tierLower, 'khuyến mại') ||
                str_contains($tierLower, 'khuyến mãi') ||
                str_contains($tierLower, 'tặng') ||
                ($price == 0 && ($qty > 0 || $giftQty > 0)) ||
                ($qty == 0 && $giftQty > 0) ||
                str_contains($nameLower, '[hàng tặng]') ||
                str_contains($nameLower, '(km)') ||
                str_contains($nameLower, 'hàng tặng')
            );

            if ($isPureGift) {
                $initialItems[] = [
                    'id' => (string) $it->id,
                    'itemId' => (string) ($it->product_id ?? ''),
                    'product_id' => $it->product_id,
                    'sku' => $it->sku,
                    'name' => $it->name,
                    'searchQuery' => (string) $it->name,
                    'unit' => $it->unit ?? '',
                    'quantity' => $giftQty > 0 ? $giftQty : ($qty > 0 ? $qty : 1),
                    'gift_quantity' => $giftQty > 0 ? $giftQty : ($qty > 0 ? $qty : 1),
                    'unitPrice' => 0,
                    'originalPrice' => (float) ($it->original_price > 0 && ($it->original_price != $it->cost_price || !$it->product?->price) ? $it->original_price : ($it->product?->price ?: ($it->original_price ?: $price))),
                    'costPrice' => (float) ($it->cost_price ?? ($it->product?->cost_price ?? 0)),
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'vat_percent' => 0,
                    'vat_amount' => 0,
                    'appliedTierPolicy' => $itTier ?: 'TẶNG KÈM',
                    'price_type' => $itTier ?: 'TẶNG KÈM',
                    'amount' => 0,
                    'picked_qty' => (float) ($it->picked_qty ?? 0),
                    'shortage_reason' => $it->shortage_reason ?? '',
                    'item_status' => $it->item_status ?? '',
                    'item_id_db' => (string) $it->id,
                    'checked' => true,
                    'isPromotionalGift' => true,
                    '_manualGift' => true,
                    '_manualQty' => true,
                ];
            } else {
                $initialItems[] = [
                    'id' => (string) $it->id,
                    'itemId' => (string) ($it->product_id ?? ''),
                    'product_id' => $it->product_id,
                    'sku' => $it->sku,
                    'name' => $it->name,
                    'searchQuery' => (string) $it->name,
                    'unit' => $it->unit ?? '',
                    'quantity' => $qty > 0 ? $qty : 1,
                    'gift_quantity' => 0,
                    'unitPrice' => $price,
                    'originalPrice' => (float) ($it->original_price > 0 && ($it->original_price != $it->cost_price || !$it->product?->price) ? $it->original_price : ($it->product?->price ?: ($it->original_price ?: $price))),
                    'costPrice' => (float) ($it->cost_price ?? ($it->product?->cost_price ?? 0)),
                    'discount_percent' => (float) ($it->discount_percent ?? 0),
                    'discount_amount' => (float) ($it->discount_amount ?? 0),
                    'vat_percent' => 0,
                    'vat_amount' => 0,
                    'appliedTierPolicy' => $itTier,
                    'price_type' => $itTier,
                    'amount' => max(0, round(($qty * $price) - (float)($it->discount_amount ?? 0))),
                    'picked_qty' => (float) ($it->picked_qty ?? 0),
                    'shortage_reason' => $it->shortage_reason ?? '',
                    'item_status' => $it->item_status ?? '',
                    'item_id_db' => (string) $it->id,
                    'checked' => true,
                    'selected_policy_id' => '',
                    'isPromotionalGift' => false,
                    '_manualGift' => false,
                    '_manualQty' => false,
                ];

                if ($giftQty > 0) {
                    $initialItems[] = [
                        'id' => (string) $it->id . '_gift',
                        'itemId' => (string) ($it->product_id ?? ''),
                        'product_id' => $it->product_id,
                        'sku' => $it->sku,
                        'name' => $it->name,
                        'searchQuery' => (string) $it->name,
                        'unit' => $it->unit ?? '',
                        'quantity' => $giftQty,
                        'gift_quantity' => $giftQty,
                        'unitPrice' => 0,
                        'originalPrice' => (float) ($it->original_price > 0 && ($it->original_price != $it->cost_price || !$it->product?->price) ? $it->original_price : ($it->product?->price ?: ($it->original_price ?: $price))),
                        'costPrice' => (float) ($it->cost_price ?? ($it->product?->cost_price ?? 0)),
                        'discount_percent' => 0,
                        'discount_amount' => 0,
                        'vat_percent' => 0,
                        'vat_amount' => 0,
                        'appliedTierPolicy' => 'TẶNG KÈM',
                        'price_type' => 'TẶNG KÈM',
                        'amount' => 0,
                        'picked_qty' => 0,
                        'shortage_reason' => '',
                        'item_status' => '',
                        'item_id_db' => (string) $it->id . '_gift',
                        'checked' => true,
                        'isPromotionalGift' => true,
                        '_manualGift' => true,
                        '_manualQty' => true,
                    ];
                }
            }
        }
    } else {
        // Start with 1 clean default row like in standard ERP form
        $initialItems[] = [
            'id' => '',
            'itemId' => '',
            'product_id' => '',
            'sku' => '',
            'name' => '',
            'searchQuery' => '',
            'unit' => '',
            'quantity' => 1,
            'gift_quantity' => 0,
            'unitPrice' => 0,
            'originalPrice' => 0,
            'costPrice' => 0,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'vat_percent' => 0,
            'vat_amount' => 0,
            'appliedTierPolicy' => $orderTier,
            'price_type' => $orderTier,
            'amount' => 0,
            'checked' => true,
        ];
    }
@endphp

<div x-data="orderFormApp()" x-init="init()" @keydown.window="handleGlobalKeydown($event)" class="order-form-app-wrapper space-y-4 pb-24">

    <!-- Alert / Toast -->
    <div x-show="errorMessage" x-cloak class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-bold text-rose-700 flex items-center justify-between shadow-xs">
        <span x-text="errorMessage"></span>
        <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
    </div>

    @if(request('readonly') || request('mode') === 'readonly')
        <div class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl text-xs font-bold text-amber-900 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-eye text-amber-600"></i>
                <span>Chế độ chỉ đọc: Bạn đang xem chi tiết chứng từ (không chỉnh sửa).</span>
            </div>
            @if($order)
                <a href="{{ url('/orders/' . $order->id . '/edit') }}" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-all shadow-2xs">
                    <i class="fa-solid fa-pen-to-square mr-1"></i> Bổ sung / Chỉnh sửa
                </a>
            @endif
        </div>
    @endif

    <style>
        fieldset[disabled] {
            pointer-events: none !important;
        }
        fieldset[disabled] input, 
        fieldset[disabled] select, 
        fieldset[disabled] textarea {
            background-color: #f8fafc !important;
            color: #0f172a !important;
            cursor: not-allowed !important;
            border-color: #cbd5e1 !important;
            opacity: 0.95 !important;
            user-select: text !important;
        }
        fieldset[disabled] button {
            display: none !important;
        }
    </style>

    <!-- 2. Main ERP Form Standard Container (Locked strictly in readonly mode) -->
    <fieldset :disabled="isReadonly" class="border-0 p-0 m-0 space-y-4 sm:space-y-6 block">
    <div class="order-form-main-card bg-white p-3.5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4 sm:space-y-6">

        <!-- Title Block -->
        <div class="order-form-title-bar flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200">
            <div class="flex items-center gap-3 flex-wrap">
                <button type="button" @click="goBack()" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0 shadow-2xs" :title="isFromPos() ? 'Quay lại Bán Hàng POS' : 'Quay lại danh sách'">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </button>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Đơn hàng</h1>
                <span class="hidden sm:inline-flex px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">ERP Chuẩn</span>
                <span x-show="orderId" class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" x-text="formData.status"></span>
            </div>
            <div class="shortcut-hint hidden md:flex text-xs text-slate-500 font-semibold items-center gap-2 shrink-0">
                <span class="text-slate-400">Phím tắt:</span>
                <kbd class="px-2 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-xs">F9</kbd>
                <span>Lưu đơn</span>
                <span class="text-slate-300 mx-1">|</span>
                <kbd class="px-2 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-xs">F8</kbd>
                <span>Thêm dòng</span>
            </div>
        </div>

        <!-- 3. Form Information: Row 1 & Row 2 -->
        <div class="space-y-4">
            <!-- Thông tin chung đơn hàng -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4 items-start">
                <!-- Mã chứng từ -->
                <div class="hidden md:block md:col-span-3 lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 tracking-wide uppercase mb-1.5">MÃ CHỨNG TỪ</label>
                    <input type="text" readonly :value="formData.order_code || 'TỰ SINH KHI LƯU'" class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-500 cursor-not-allowed select-none truncate">
                </div>

                <!-- Loại đơn hàng -->
                <div class="col-span-1 md:col-span-3 lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 tracking-wide uppercase mb-1.5">LOẠI ĐƠN HÀNG</label>
                    <select x-model="formData.order_type" @change="handleOrderTypeChange()" class="w-full h-10 px-3 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-bold text-slate-800 focus:border-sky-500 focus:outline-none cursor-pointer shadow-2xs">
                        <option value="normal">🛒 Đơn thường</option>
                        <option value="consignment">📦 Đơn gửi</option>
                        <template x-if="formData.order_type && !['normal', 'consignment'].includes(formData.order_type)">
                            <option :value="formData.order_type" x-text="formData.order_type"></option>
                        </template>
                    </select>
                </div>

                <!-- Khách hàng -->
                <div class="col-span-1 md:col-span-6 lg:col-span-3" 
                     x-data="{ 
                         openDealerDropdown: false, 
                         dealerSearch: '', 
                         highlightedDealerIndex: 0,
                         scrollDealerIntoView(idx) {
                             this.$nextTick(() => {
                                 const el = document.getElementById('dealer-opt-' + idx);
                                 if (el) el.scrollIntoView({ block: 'nearest' });
                             });
                         }
                     }" 
                     @click.outside="openDealerDropdown = false">
                    <div class="flex items-center justify-between mb-1.5 gap-1">
                        <label class="text-xs font-bold text-slate-600 tracking-wide uppercase truncate">KHÁCH HÀNG / ĐẠI LÝ</label>
                        <span class="text-xs font-bold truncate max-w-[140px]" :class="formData.customer_name ? 'text-sky-600' : 'text-slate-400'" x-text="formData.customer_name ? formData.customer_name : 'Chưa chọn'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1 min-w-0">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            <input type="text"
                                   autocomplete="off"
                                   autocorrect="off"
                                   spellcheck="false"
                                   x-ref="customerSearchInput"
                                   :value="formData.customer_name"
                                   @focus="openDealerDropdown = true; dealerSearch = ''; highlightedDealerIndex = 0"
                                   @input="dealerSearch = $event.target.value; openDealerDropdown = true; highlightedDealerIndex = 0"
                                   @keydown.down.prevent="
                                       openDealerDropdown = true;
                                       const dList = filteredDealers(dealerSearch);
                                       highlightedDealerIndex = Math.min(highlightedDealerIndex + 1, dList.length - 1);
                                       scrollDealerIntoView(highlightedDealerIndex);
                                   "
                                   @keydown.up.prevent="
                                       highlightedDealerIndex = Math.max(highlightedDealerIndex - 1, 0);
                                       scrollDealerIntoView(highlightedDealerIndex);
                                   "
                                   @keydown.enter.prevent="
                                       if (openDealerDropdown) {
                                           const dList = filteredDealers(dealerSearch);
                                           if (dList.length > 0 && dList[highlightedDealerIndex]) {
                                               selectDealer(dList[highlightedDealerIndex]);
                                               openDealerDropdown = false;
                                               $nextTick(() => {
                                                   if ($refs.customerSearchInput) $refs.customerSearchInput.blur();
                                               });
                                           }
                                       }
                                   "
                                   @keydown.escape.prevent="openDealerDropdown = false"
                                   placeholder="Tìm tên, mã hoặc SĐT khách hàng..." 
                                   class="w-full h-10 pl-9 pr-8 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:border-sky-500 focus:outline-none cursor-pointer shadow-2xs hover:border-slate-400 transition-colors">
                            
                            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center">
                                <button type="button" @click.stop="openDealerDropdown = !openDealerDropdown" class="text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer">
                                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="openDealerDropdown ? 'rotate-180 text-sky-600' : ''"></i>
                                </button>
                            </div>

                            <!-- Menu Dropdown hiển thị danh sách khách hàng -->
                            <div x-show="openDealerDropdown" x-cloak class="order-customer-dropdown absolute left-0 top-full mt-1.5 z-50 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-72 overflow-y-auto py-1 text-xs w-[440px] max-w-[92vw] min-w-full">
                                <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                    <span>Danh sách khách hàng ({{ count($dealers) }})</span>
                                    <span class="text-sky-600 font-normal lowercase hidden md:inline">dùng phím &uarr; &darr; &bull; Enter</span>
                                </div>
                                <template x-if="openDealerDropdown">
                                    <div>
                                        <template x-for="(d, dIdx) in filteredDealers(dealerSearch)" :key="d.id">
                                            <div :id="'dealer-opt-' + dIdx"
                                                 @click="selectDealer(d); openDealerDropdown = false; activeProdDropdownIndex = null; $nextTick(() => { if ($refs.customerSearchInput) $refs.customerSearchInput.blur(); })" 
                                                 @mouseenter="highlightedDealerIndex = dIdx"
                                                 class="px-3.5 py-2.5 cursor-pointer transition-colors border-b border-slate-100 last:border-0"
                                                 :class="highlightedDealerIndex === dIdx ? 'bg-sky-50 text-sky-950 ring-1 ring-sky-300' : (formData.dealer_id == d.id ? 'bg-sky-50/80 font-bold text-sky-700' : 'hover:bg-slate-50')">
                                                <!-- Dòng 1: Tên khách hàng & Cấp đại lý -->
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-bold text-slate-900 text-sm truncate" x-text="d.name"></span>
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 shrink-0" x-text="d.tier || 'Cấp 1'"></span>
                                                </div>
                                                <!-- Dòng 2: MST badge, Số điện thoại, Mã KH -->
                                                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 flex-wrap">
                                                    <template x-if="d.tax_code && d.tax_code.trim()">
                                                        <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0" :title="'MST: ' + d.tax_code">
                                                            ✓ MST: <span x-text="d.tax_code"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!d.tax_code || !d.tax_code.trim()">
                                                        <span class="px-2 py-0.5 rounded text-xs font-bold shrink-0"
                                                              :class="(d.orders_count || 0) >= 3 ? 'bg-rose-100 text-rose-700 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300'">
                                                            Chưa MST (<span x-text="(d.orders_count || 0) + '/3'"></span>)
                                                        </span>
                                                    </template>
                                                    <span x-show="d.code || d.phone" class="font-mono text-slate-700 font-semibold shrink-0 flex items-center gap-1 text-xs" :title="'Mã / SĐT: ' + (d.code || d.phone)">
                                                        <i class="fa-solid fa-id-card text-xs text-slate-400"></i>
                                                        <span x-text="d.code || d.phone"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="filteredDealers(dealerSearch).length === 0" class="px-3 py-6 text-center text-slate-400 italic text-sm">
                                            Không tìm thấy khách hàng. Bấm nút [+] để thêm mới.
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Nút Thêm mới khách hàng -->
                        <button type="button" @click="showAddDealer = true" class="w-10 h-10 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-300 rounded-xl flex items-center justify-center font-bold text-sm cursor-pointer shrink-0 transition-colors shadow-2xs" title="Thêm khách hàng / đại lý mới">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Vị trí giao hàng -->
                <div class="col-span-1 md:col-span-6 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-600 tracking-wide uppercase mb-1.5">VỊ TRÍ GIAO HÀNG</label>
                    <div class="relative">
                        <i class="fa-solid fa-location-dot absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                        <input type="text" 
                               x-model="formData.customer_address" 
                               placeholder="Số nhà, đường, công trình, vị trí nhận hàng..." 
                               class="w-full h-10 pl-9 pr-8 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-medium focus:border-sky-500 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">
                        <button type="button" 
                                x-show="formData.customer_address" 
                                @click="formData.customer_address = ''" 
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-0.5 cursor-pointer" 
                                title="Xóa địa chỉ">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Kho dự kiến xuất -->
                <div class="hidden md:block md:col-span-6 lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 tracking-wide uppercase mb-1.5">KHO XUẤT HÀNG</label>
                    <select x-model="formData.warehouse" 
                            @change="onWarehouseChange()" 
                            class="w-full h-10 px-3 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:border-sky-500 focus:outline-none cursor-pointer shadow-2xs hover:border-slate-400 transition-colors">
                        <option value="">-- Chọn kho xuất --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->name }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Hidden inputs -->
                <input type="hidden" name="status" :value="formData.status">
                <input type="hidden" name="order_date" :value="formData.order_date">
                <input type="hidden" name="picking_date" :value="formData.picking_date">
                <input type="hidden" name="delivery_date" :value="formData.delivery_date">
            </div>

            <!-- Card hiển thị công nợ, hạn mức & MST khách hàng -->
            <div x-show="getSelectedDealer()" x-cloak class="p-3 sm:p-3.5 rounded-xl border text-xs sm:text-sm font-semibold transition-all shadow-2xs"
                 :class="(isDebtOverLimit() || isNoTaxCodeLimitReached()) ? 'bg-rose-50 border-rose-300 text-rose-900 ring-1 ring-rose-200' : (!hasTaxCode() ? 'bg-amber-50/80 border-amber-300 text-amber-900' : 'bg-slate-50 border-slate-200 text-slate-700')">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="flex items-center gap-1.5 font-bold text-slate-900">
                            <i :class="(isDebtOverLimit() || isNoTaxCodeLimitReached()) ? 'fa-solid fa-triangle-exclamation text-rose-600' : 'fa-solid fa-user-check text-sky-600'"></i>
                            <span>Khách: <span class="text-sky-900 font-extrabold" x-text="getSelectedDealer()?.name"></span></span>
                            <template x-if="getSelectedDealer()?.code">
                                <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-slate-200 text-slate-800" x-text="getSelectedDealer()?.code"></span>
                            </template>
                        </span>
                        <span class="text-slate-300">|</span>

                        <!-- Trạng thái MST -->
                        <span>
                            <template x-if="hasTaxCode()">
                                <span class="text-emerald-700 font-mono text-xs font-bold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>MST: <b x-text="getCustomerTaxCode()"></b></span>
                                </span>
                            </template>
                            <template x-if="!hasTaxCode()">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold"
                                      :class="isNoTaxCodeLimitReached() ? 'bg-rose-100 text-rose-700 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300'">
                                    <i class="fa-solid" :class="isNoTaxCodeLimitReached() ? 'fa-triangle-exclamation text-rose-600' : 'fa-circle-exclamation text-amber-600'"></i>
                                    <span x-text="isNoTaxCodeLimitReached() ? ('Chặn lên đơn (Chưa MST: ' + getCustomerOrdersCount() + '/3 đơn)') : ('Chưa MST: Đơn thứ ' + (getCustomerOrdersCount() + 1) + '/3')"></span>
                                </span>
                            </template>
                        </span>
                        <span class="text-slate-300">|</span>

                        <span>Nợ hiện tại: <b class="font-mono text-xs sm:text-sm font-bold text-slate-900" x-text="formatNumber(getSelectedDealer()?.current_debt || 0) + ' đ'"></b></span>
                        <span class="text-slate-300">|</span>
                        <span>Hạn mức: 
                            <b class="font-mono font-bold" :class="Number(getSelectedDealer()?.credit_limit) > 0 ? 'text-slate-800' : 'text-emerald-600'" x-text="Number(getSelectedDealer()?.credit_limit) > 0 ? (formatNumber(getSelectedDealer()?.credit_limit) + ' đ') : 'Không giới hạn'"></b>
                        </span>
                        <span class="text-slate-300">|</span>
                        <span>Sau đơn: 
                            <b class="font-mono font-bold" :class="isDebtOverLimit() ? 'text-rose-700 font-black' : 'text-sky-700'" x-text="formatNumber(getProjectedDebt()) + ' đ'"></b>
                        </span>
                    </div>

                    <!-- Right Alert Badge -->
                    <div class="text-right shrink-0 flex items-center gap-2">
                        <template x-if="isNoTaxCodeLimitReached()">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-rose-600 text-white font-black text-xs tracking-wide shadow-xs animate-pulse">
                                <i class="fa-solid fa-ban"></i>
                                <span>CHẶN: CHƯA MST (3 ĐƠN)</span>
                            </span>
                        </template>
                        <template x-if="isDebtOverLimit()">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-rose-600 text-white font-black text-xs tracking-wide shadow-xs animate-pulse">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>VƯỢT HẠN MỨC (+<span x-text="formatNumber(getDebtExcessAmount()) + ' đ'"></span>)</span>
                            </span>
                        </template>
                        <template x-if="!isDebtOverLimit() && Number(getSelectedDealer()?.credit_limit) > 0">
                            <span class="text-emerald-700 font-bold text-xs bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                Còn được nợ: +<span class="font-mono" x-text="formatNumber(Math.max(0, Number(getSelectedDealer()?.credit_limit) - getProjectedDebt())) + ' đ'"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Product Line Items Table (Chi Tiết Mặt Hàng) -->
        <div class="space-y-3">
            <!-- Banners cảnh báo tổng thể -->
            <div class="space-y-2" x-show="isDebtOverLimit() || getOverStockItems().length > 0 || isNoTaxCodeLimitReached() || (!hasTaxCode() && getSelectedDealer())" x-cloak>
                <!-- 1. Chặn lên đơn khi khách chưa có MST và đã đạt giới hạn 3 đơn -->
                <div x-show="isNoTaxCodeLimitReached()" class="p-3.5 bg-rose-50 border-2 border-rose-500 rounded-2xl flex items-center gap-3 shadow-xs">
                    <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 font-bold text-base">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div class="text-xs sm:text-sm flex-1 text-rose-950 font-semibold">
                        <span class="font-black text-rose-700 uppercase mr-1">CHẶN LÊN ĐƠN:</span>
                        Khách hàng <span class="underline font-black" x-text="formData.customer_name"></span> chưa có Mã số thuế (MST) và đã đạt tối đa 3 đơn hàng. Vui lòng cập nhật MST cho khách hàng trước khi tiếp tục.
                    </div>
                </div>

                <!-- 2. Cảnh báo khách chưa có MST (đang trong hạn mức 1-3 đơn) -->
                <div x-show="!hasTaxCode() && getSelectedDealer() && !isNoTaxCodeLimitReached()" class="p-3 bg-amber-50 border border-amber-300 rounded-xl flex items-center gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold">
                        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    </div>
                    <div class="text-xs sm:text-sm text-amber-950 font-semibold flex-1">
                        <span class="font-black text-amber-800 uppercase mr-1">CẢNH BÁO MST:</span>
                        Khách hàng <strong class="font-bold text-slate-900" x-text="formData.customer_name"></strong> chưa có MST (<span class="font-bold" x-text="'Đơn thứ ' + (getCustomerOrdersCount() + 1) + '/3'"></span>). Hệ thống chỉ cho phép lên tối đa 3 đơn khi chưa có MST.
                    </div>
                </div>

                <!-- Cảnh báo công nợ -->
                <div x-show="isDebtOverLimit()" class="p-3 bg-rose-50 border border-rose-300 rounded-xl flex items-center gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 font-bold">
                        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    </div>
                    <div class="text-xs sm:text-sm text-rose-950 font-semibold flex-1">
                        <span class="font-black text-rose-700 uppercase mr-1">VƯỢT HẠN MỨC CÔNG NỢ:</span>
                        Nợ hiện tại: <b class="font-mono" x-text="formatNumber(getSelectedDealer()?.current_debt || 0) + ' đ'"></b>. Sau đơn này dự kiến: <b class="font-mono text-rose-700 font-black" x-text="formatNumber(getProjectedDebt()) + ' đ'"></b> (<span class="font-bold text-rose-700">vượt hạn mức +<span x-text="formatNumber(getDebtExcessAmount()) + ' đ'"></span></span>).
                    </div>
                </div>

                <!-- Cảnh báo tồn kho khả dụng -->
                <div x-show="getOverStockItems().length > 0" class="p-3.5 bg-rose-50 border-2 border-rose-400 rounded-2xl flex items-start gap-3 shadow-xs">
                    <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 font-bold text-base">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div class="text-xs sm:text-sm leading-relaxed flex-1 text-rose-950">
                        <div class="font-black uppercase tracking-wider text-xs mb-1 text-rose-800">
                            CHẶN LÊN ĐƠN: HẾT TỒN KHO KHẢ DỤNG TẠI [<span x-text="formData.warehouse || 'Kho xuất'"></span>]
                        </div>
                        <p class="text-xs text-rose-900 font-semibold mb-1.5">
                            Vui lòng giảm số lượng đặt về mức khả dụng hoặc chọn kho khác:
                        </p>
                        <div class="space-y-1">
                            <template x-for="(it, idx) in getOverStockItems()" :key="idx">
                                <div class="inline-block mr-2 my-0.5 px-3 py-1 rounded-lg border border-rose-300 bg-white font-medium text-xs shadow-2xs">
                                    <span class="font-bold text-slate-900" x-text="it.name || it.sku"></span>: 
                                    đặt <span class="font-black text-rose-700" x-text="it.quantity + ' ' + (it.unit || '')"></span> 
                                    (Khả dụng: <span class="font-bold font-mono text-emerald-800" x-text="formatNumber(getItemAvailableStockInUnit(it)) + ' ' + (it.unit || '')"></span>)
                                    &rarr; <b class="text-rose-700 underline font-black">thiếu <span x-text="formatNumber(getItemOverStockQty(it))"></span> <span x-text="it.unit || ''"></span></b>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-items-table-container rounded-xl border border-slate-200">
                <table class="order-items-table w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                            <th class="py-2.5 px-3 text-center w-14">
                                <div class="flex items-center justify-center gap-1">
                                    <input type="checkbox" 
                                           :checked="items.length > 0 && items.every(it => it.checked !== false)" 
                                           @change="const val = $event.target.checked; items.forEach(it => it.checked = val); evaluateOrderPolicies();"
                                           class="rounded text-sky-600 focus:ring-0 cursor-pointer">
                                    <span>STT</span>
                                </div>
                            </th>
                            <th class="py-2.5 px-3 font-bold min-w-[280px]">TÊN / MÃ SP</th>
                            <th class="py-2.5 px-2 text-center w-20">ĐVT</th>
                            <th class="py-2.5 px-2 text-right w-28">ĐƠN GIÁ</th>
                            <th class="py-2.5 px-2 text-center w-16">SL</th>
                            <!-- Tiền chiết khấu cột -->
                            <th class="py-2 px-2 text-center w-52 bg-sky-50/60 border-x border-slate-200">
                                <div class="text-[11px] font-bold text-slate-700 uppercase">TIỀN CHIẾT KHẤU</div>
                                <div class="mt-1 flex items-center justify-center">
                                    <div class="inline-flex items-center gap-1 bg-white border border-sky-300 rounded px-1.5 py-0.5 shadow-2xs hover:border-sky-400 focus-within:ring-1 focus-within:ring-sky-400 transition-all">
                                        <input type="number" min="0" max="100" step="any"
                                               x-model.number="bulkDiscountPercent"
                                               autocomplete="off"
                                               @input="applyBulkDiscountPercent()"
                                               @focus="$event.target.select()"
                                               placeholder="%"
                                               title="Nhập % chiết khấu để áp dụng cho toàn bộ sản phẩm"
                                               class="w-12 px-1 py-0.5 bg-sky-50/60 focus:bg-white border border-sky-200 rounded text-center text-xs font-bold text-sky-700 focus:outline-none">
                                        <span class="text-[10px] text-sky-700 font-bold">%</span>
                                    </div>
                                </div>
                            </th>

                            <!-- Cột SL Bốc: chỉ hiện khi đơn đang ở giai đoạn soạn hàng -->
                            <template x-if="isPickingStage()">
                                <th class="py-2.5 px-2 text-center w-24 bg-orange-50/80 border-x border-slate-200">
                                    <div class="text-orange-700 font-bold">SL BỐC</div>
                                    <div class="text-[9px] text-orange-500 font-normal lowercase tracking-normal">(kho đã bốc)</div>
                                </th>
                            </template>
                            <th class="py-2.5 px-3 text-right w-36">THÀNH TIỀN</th>
                            <th class="py-2.5 px-2 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-for="(item, index) in getSalesItems()" :key="item._rowKey || (item._rowKey = 'it-' + (item.item_id_db || item.id || index) + '-' + Math.random())">
                            <tr class="order-item-row hover:bg-slate-50/60 transition-colors" 
                                :class="activeProdDropdownIndex === index ? 'relative z-40 ' : ''">
                                <!-- STT + Checkbox -->
                                <td class="col-stt py-2 px-3 text-center align-top">
                                    <div class="h-10 flex items-center justify-center gap-1.5">
                                        <input type="checkbox" x-model="item.checked" @change="evaluateOrderPolicies()" class="rounded text-sky-600 focus:ring-0 cursor-pointer">
                                        <span class="font-bold text-slate-700" x-text="index + 1"></span>
                                    </div>
                                </td>

                                <!-- Tên / Mã SP Input với Menu gợi ý điều khiển bằng phím mũi tên Lên/Xuống + Enter -->
                                <td class="col-product py-2 px-3 relative align-top" :class="activeProdDropdownIndex === index ? 'z-40 overflow-visible' : ''">
                                    <div class="relative" @click.outside="if (activeProdDropdownIndex === index) activeProdDropdownIndex = null">
                                        <div class="relative flex items-center">
                                            <input type="text"
                                                   autocomplete="off"
                                                   autocorrect="off"
                                                   spellcheck="false"
                                                   :id="'row-product-input-' + index"
                                                   x-model="item.searchQuery" 
                                                   @focus="activeProdDropdownIndex = index; prodHighlightIndex = 0; $event.target.select()"
                                                   @input="item._prodSearching = true; activeProdDropdownIndex = index; prodHighlightIndex = 0"
                                                   @keydown.down.prevent="
                                                       activeProdDropdownIndex = index;
                                                       const prods = getFilteredProducts(getRowProductSearchQuery(item), getItemIndex(item));
                                                       prodHighlightIndex = prods.length > 0 ? Math.min(prodHighlightIndex + 1, prods.length - 1) : 0;
                                                       scrollProdIntoView(index, prodHighlightIndex);
                                                   "
                                                   @keydown.up.prevent="
                                                       prodHighlightIndex = Math.max(prodHighlightIndex - 1, 0);
                                                       scrollProdIntoView(index, prodHighlightIndex);
                                                   "
                                                   @keydown.enter.prevent="
                                                       if (activeProdDropdownIndex === index) {
                                                           const prods = getFilteredProducts(getRowProductSearchQuery(item), getItemIndex(item));
                                                           if (prods.length > 0 && prods[prodHighlightIndex]) {
                                                               selectProductForRow(getItemIndex(item), prods[prodHighlightIndex]);
                                                               activeProdDropdownIndex = null;
                                                               $nextTick(() => {
                                                                   const qtyInput = document.getElementById('row-qty-input-' + index);
                                                                   if (qtyInput) {
                                                                       qtyInput.focus();
                                                                       qtyInput.select();
                                                                   }
                                                               });
                                                           }
                                                       }
                                                   "
                                                   @keydown.escape.prevent="activeProdDropdownIndex = null"
                                                   placeholder="Tìm mã hoặc tên sản phẩm..." 
                                                   :placeholder="isMobileViewport ? 'Tìm mã hoặc tên sản phẩm...' : 'Tìm mã hoặc tên sản phẩm (phím ↑ ↓ Enter)...'" 
                                                   class="product-search-input w-full h-10 pl-3.5 pr-8 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-800 focus:border-sky-500 focus:outline-none shadow-xs hover:border-slate-400 transition-colors">

                                            <button type="button"
                                                    x-show="(item.searchQuery || item.product_id) && !isReadonly"
                                                    x-cloak
                                                    @mousedown.prevent
                                                    @click.stop="clearProductRow(getItemIndex(item), index)"
                                                    class="absolute right-2 text-slate-400 hover:text-rose-500 p-1 rounded-md cursor-pointer transition-colors"
                                                    title="Xóa để tìm sản phẩm khác">
                                                <i class="fa-solid fa-xmark text-xs"></i>
                                            </button>
                                        </div>

                                        <!-- Menu Dropdown gợi ý sản phẩm -->
                                        <div x-show="activeProdDropdownIndex === index" 
                                             x-cloak 
                                             class="order-product-dropdown absolute left-0 top-full mt-1 w-[460px] max-w-[90vw] z-[60] bg-white rounded-xl shadow-2xl border border-slate-200 max-h-60 overflow-y-auto py-1 text-xs">
                                            <template x-if="activeProdDropdownIndex === index">
                                                <div>
                                                    <div class="px-3 py-1 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between sticky top-0 z-10 border-b border-slate-100">
                                                        <span>Gợi ý sản phẩm (<span x-text="getFilteredProducts(getRowProductSearchQuery(item), getItemIndex(item)).length"></span>)</span>
                                                        <span class="text-sky-600 font-normal lowercase hidden sm:inline">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                                    </div>
                                                    <template x-for="(p, pIdx) in getFilteredProducts(getRowProductSearchQuery(item), getItemIndex(item))" :key="p.id">
                                                        <div :id="'prod-opt-' + index + '-' + pIdx"
                                                             @mousedown.prevent
                                                             @click="selectProductForRow(getItemIndex(item), p); activeProdDropdownIndex = null; $nextTick(() => { const q = document.getElementById('row-qty-input-' + index); if(q) { q.focus(); q.select(); } })"
                                                             @mouseenter="prodHighlightIndex = pIdx"
                                                             class="px-3 py-2 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                                             :class="prodHighlightIndex === pIdx ? 'bg-sky-100 text-sky-950 font-bold ring-1 ring-sky-300' : 'hover:bg-slate-50'">
                                                            <div class="min-w-0 pr-3 flex-1">
                                                                <div class="font-bold text-slate-900 leading-snug break-words">
                                                                    <span class="text-sky-600 font-mono mr-1 shrink-0" x-text="'[' + p.sku + ']'"></span>
                                                                    <span x-text="p.name"></span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5 flex-wrap">
                                                                    <span class="font-semibold text-slate-700" x-text="'ĐVT: ' + (p.unit || '') + (p.conversion_unit && p.conversion_unit.trim().toLowerCase() !== (p.unit || '').trim().toLowerCase() ? ' / ' + p.conversion_unit : '')"></span>
                                                                    <span>&bull;</span>
                                                                    <span class="inline-flex items-center gap-1.5">
                                                                        <template x-if="getProductStockData(p, formData.warehouse).available <= 0">
                                                                            <span class="px-1.5 py-0.2 rounded bg-rose-600 text-white font-black text-[9px] uppercase tracking-wider flex items-center gap-1">
                                                                                <i class="fa-solid fa-ban text-[8px]"></i> Hết khả dụng
                                                                            </span>
                                                                        </template>
                                                                        <span class="px-1.5 py-0.2 rounded font-bold text-[10px]"
                                                                              :class="getProductStockData(p, formData.warehouse).available > 0 ? 'bg-emerald-100 text-emerald-800' : (getProductStockData(p, formData.warehouse).on_hand > 0 ? 'bg-amber-100 text-amber-900 ring-1 ring-amber-300' : 'bg-rose-100 text-rose-700')"
                                                                              x-text="'Khả dụng: ' + formatNumber(getProductStockData(p, formData.warehouse).available) + ' ' + (p.unit || '')"></span>
                                                                        <span class="text-slate-400 text-[10px]"
                                                                              x-text="'(Kho: ' + formatNumber(getProductStockData(p, formData.warehouse).on_hand) + (getProductStockData(p, formData.warehouse).reserved > 0 ? ' | Giữ: ' + formatNumber(getProductStockData(p, formData.warehouse).reserved) : '') + ')'"></span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="shrink-0 text-right">
                                                                <div class="font-black text-sky-700 text-xs" x-text="formatNumber(getProductPrice(p)) + ' đ'"></div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div x-show="getFilteredProducts(getRowProductSearchQuery(item), getItemIndex(item)).length === 0" class="px-4 py-5 text-center text-slate-400">
                                                        <i class="fa-solid fa-box-open text-lg text-slate-300 mb-1"></i>
                                                        <div class="font-bold text-slate-600 text-xs">Không tìm thấy sản phẩm phù hợp</div>
                                                        <button type="button"
                                                                @mousedown.prevent
                                                                @click.stop="item.searchQuery = ''; item._prodSearching = true; prodHighlightIndex = 0; $nextTick(() => document.getElementById('row-product-input-' + index)?.focus())"
                                                                class="mt-1.5 px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold rounded-lg text-[11px] cursor-pointer transition-colors">
                                                            Xem tất cả sản phẩm
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>

                                <!-- ĐVT (Luôn cho phép chọn ĐVT và ĐV quy đổi của từng sản phẩm) -->
                                <td class="col-unit py-2 px-2 text-center align-top">
                                    <select x-model="item.unit" 
                                            @change="onItemUnitChange(getItemIndex(item))" 
                                            class="w-full h-10 px-2 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded-xl text-xs sm:text-sm text-center font-bold text-slate-800 focus:outline-none cursor-pointer transition-colors shadow-xs">
                                        <template x-for="u in getItemUnits(item)" :key="u">
                                            <option :value="u" x-text="u" :selected="item.unit === u"></option>
                                        </template>
                                    </select>
                                </td>

                                <!-- Đơn giá (Hỗ trợ phím ↑ ↓ để tăng/giảm giá) -->
                                <td class="col-price py-2 px-2 text-right align-top">
                                    <div class="relative">
                                        <input type="text" 
                                               inputmode="numeric" 
                                               :id="'row-price-input-' + index"
                                               autocomplete="off"
                                               x-money="item.unitPrice" 
                                               @input="item.price_type = 'Tùy chỉnh'; updateLineTotal(getItemIndex(item))" 
                                               @keydown.up.prevent="increasePrice(getItemIndex(item))"
                                               @keydown.down.prevent="decreasePrice(getItemIndex(item))"
                                               title="Phím ↑ tăng giá, phím ↓ giảm giá"
                                               class="w-full h-10 px-2.5 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded-xl text-right font-bold text-sm text-slate-900 focus:outline-none shadow-xs transition-colors">
                                    </div>
                                </td>

                                <!-- Số lượng (Hỗ trợ phím ↑ ↓ và bộ nút bấm +/-) -->
                                <td class="col-qty py-2 px-2 text-center align-top">
                                    <div class="relative">
                                        <div class="flex items-center">
                                            <input type="text" inputmode="decimal" 
                                                   :id="'row-qty-input-' + index"
                                                   autocomplete="off"
                                                   x-number="item.quantity" 
                                                   @input="updateLineTotal(getItemIndex(item))" 
                                                   @keydown.up.prevent="increaseQty(getItemIndex(item))"
                                                   @keydown.down.prevent="decreaseQty(getItemIndex(item))"
                                                   @keydown.enter.prevent="addNewRow()"
                                                   title="Phím ↑ tăng SL, phím ↓ giảm SL"
                                                   :class="getItemStockStatus(item) === 'danger' ? 'bg-rose-50 border-2 border-rose-500 text-rose-800 ring-2 ring-rose-200 font-black' : (getItemStockStatus(item) === 'warning' ? 'bg-amber-50 border-2 border-amber-500 text-amber-900 ring-2 ring-amber-200 font-black' : 'bg-white border border-slate-300 text-slate-900 font-bold hover:border-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-200')"
                                                   class="w-full h-10 px-1 text-center text-sm sm:text-base font-black shadow-xs outline-none transition-all rounded-xl min-w-[36px]">
                                        </div>
                                        
                                        <!-- Cảnh báo thiếu tồn kho dưới ô số lượng -->
                                        <div x-show="getItemStockStatus(item) !== 'ok'" x-cloak class="mt-1 text-[10px] font-bold flex items-center justify-center gap-0.5 leading-tight"
                                             :class="getItemStockStatus(item) === 'danger' ? 'text-rose-600' : 'text-amber-700'">
                                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                                            <span x-text="getItemStockStatus(item) === 'danger' ? 'Thiếu kho: ' + formatNumber(getItemOverStockQty(item)) : 'Lẹm giữ: ' + formatNumber(getItemOverStockQty(item))"></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tiền chiết khấu (% mỗi dòng hoặc VNĐ) -->
                                <td class="col-discount py-2 px-2 bg-sky-50/30 border-x border-slate-200 align-top">
                                    <div>
                                        <div class="flex items-center gap-1.5 justify-between">
                                            <!-- Ô nhập % -->
                                            <div class="flex items-center gap-0.5 shrink-0">
                                                <input type="number" min="0" max="100" step="any" 
                                                       x-model.number="item.discount_percent" 
                                                       autocomplete="off"
                                                       @input="onLineDiscountPercentChange(getItemIndex(item))" 
                                                       placeholder="%" 
                                                       title="Tỷ lệ chiết khấu (%)"
                                                       class="w-16 h-10 px-1.5 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded-xl text-center text-xs sm:text-sm font-bold text-slate-800 focus:outline-none shadow-xs transition-colors">
                                                <span class="text-[10px] text-slate-400 font-bold">%</span>
                                            </div>
                                            <!-- Ô nhập Tiền Chiết Khấu (VNĐ) -->
                                            <div class="flex items-center gap-0.5 flex-1 min-w-0">
                                                <input type="text" inputmode="numeric" 
                                                       x-money="item.discount_amount" 
                                                       autocomplete="off"
                                                       @input="onLineDiscountAmountChange(getItemIndex(item))" 
                                                       placeholder="0" 
                                                       title="Số tiền chiết khấu (đ)"
                                                       class="w-full h-10 px-2 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded-xl text-right text-xs sm:text-sm font-mono font-bold text-slate-900 focus:outline-none shadow-xs transition-colors">
                                                <span class="text-[10px] text-slate-400 font-bold shrink-0">đ</span>
                                            </div>
                                        </div>
                                        <div x-show="Number(item.gift_quantity) > 0" class="mt-0.5 text-[9px] text-emerald-600 font-semibold truncate text-right" title="Dòng này có hàng khuyến mại kèm">
                                            <i class="fa-solid fa-gift text-[8px]"></i> KM kèm: <span x-text="item.gift_quantity"></span>
                                        </div>
                                        <template x-if="item.selected_policy_id && getSalesPolicyById(item.selected_policy_id)">
                                            <div class="mt-1 text-[10px] text-sky-700 font-bold truncate text-right flex items-center justify-end gap-1"
                                                 :title="getSalesPolicyById(item.selected_policy_id)?.name">
                                                <i class="fa-solid fa-tag text-[9px] text-sky-500"></i>
                                                <span class="truncate max-w-[130px]" x-text="getSalesPolicyById(item.selected_policy_id)?.name"></span>
                                            </div>
                                        </template>
                                    </div>
                                </td>

                                <!-- Cột SL Bốc: chỉ render khi đang ở giai đoạn soạn hàng -->
                                <template x-if="isPickingStage()">
                                    <td class="col-picking py-2 px-2 text-center align-top bg-orange-50/20 border-x border-slate-200">
                                        <div>
                                            <div class="font-black text-sm font-mono"
                                                 :class="Number(item.picked_qty||0) <= 0 ? 'text-slate-400' :
                                                         Number(item.picked_qty||0) >= Number(item.quantity||0)+Number(item.gift_quantity||0) ? 'text-emerald-600' :
                                                         'text-orange-600'"
                                                 x-text="Number(item.picked_qty||0) > 0 ? item.picked_qty : '—'"></div>
                                            <div class="text-[9px] text-slate-400 mt-0.5"
                                                 x-show="Number(item.picked_qty||0) > 0"
                                                 x-text="'/' + (Number(item.quantity||0)+Number(item.gift_quantity||0)) + ' ' + (item.unit||'')"></div>
                                        </div>
                                    </td>
                                </template>

                                <!-- Thành tiền -->
                                <td class="col-amount py-2 px-3 text-right align-top">
                                    <div class="h-10 flex items-center justify-end font-black text-sm sm:text-base text-slate-900 whitespace-nowrap" x-text="formatNumber(item.amount) + ' đ'"></div>
                                </td>

                                <!-- Nút Xóa -->
                                <td class="col-actions py-2 px-2 text-center align-top">
                                    <button type="button" @click="removeItem(item)" class="mx-auto w-9 h-9 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 border border-transparent hover:border-rose-200 flex items-center justify-center transition-colors cursor-pointer shadow-2xs" title="Xóa dòng">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Under Table: Thêm dòng button + Shortcut tip -->
            <div class="order-under-table-actions flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" @click="addNewRow()" @mousedown.prevent class="inline-flex items-center gap-1.5 px-4 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-xs rounded-xl border border-sky-200 transition-colors cursor-pointer shadow-2xs shrink-0 active:scale-95">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Thêm dòng sản phẩm</span>
                    </button>
                    <button type="button" @click="addNewGiftRow()" @mousedown.prevent class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-300 transition-colors cursor-pointer shadow-2xs shrink-0 active:scale-95">
                        <i class="fa-solid fa-gift text-xs"></i>
                        <span>Hàng tặng</span>
                    </button>
                </div>
                <div class="shortcut-hint hidden md:flex text-[11px] text-slate-500 font-medium items-center gap-1.5 flex-wrap">
                    <span class="text-slate-400">Mẹo:</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F8</kbd> / <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">Ctrl+Enter</kbd> thêm dòng
                    <span class="text-slate-300">&bull;</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F9</kbd> lưu
                    <span class="text-slate-300">&bull;</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">Ctrl+F9</kbd> lưu và in
                </div>
            </div>

            <!-- KHỐI CHỌN CHÍNH SÁCH BÁN HÀNG TẬP TRUNG (1 VỊ TRÍ PHÍA DƯỚI) -->
            <div class="order-policy-section mt-4 rounded-2xl border-2 border-sky-200 bg-sky-50/50 p-3.5 sm:p-4 space-y-3 shadow-xs">
                <!-- Tiêu đề & Nút Bỏ chọn: Font to, rõ, 1 hàng duy nhất -->
                <div class="flex items-center justify-between gap-3 border-b border-sky-200 pb-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center text-sm shadow-xs shrink-0">
                            <i class="fa-solid fa-tags"></i>
                        </span>
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-sm sm:text-base font-black text-slate-800 uppercase tracking-wide truncate">
                                Chính sách bán hàng
                            </span>
                            <template x-if="getSelectedPolicies().length > 0">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-sky-600 text-white shadow-xs shrink-0"
                                      x-text="getSelectedPolicies().length"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Nút Tự áp CTKM tối ưu & Bỏ chọn -->
                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                        <button type="button"
                                x-show="!isReadonly && getSalesItemsWithProduct().length > 0"
                                x-cloak
                                @click="autoApplyBestPolicies()"
                                class="inline-flex items-center gap-1.5 h-8 px-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs sm:text-sm font-bold transition cursor-pointer shadow-xs active:scale-95"
                                title="Tự động gợi ý & áp dụng gói chính sách lợi nhất không xung đột">
                            <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                            <span>Tự áp CTKM tối ưu</span>
                        </button>
                        <template x-if="getSelectedPolicies().length > 0">
                            <button type="button"
                                    @click="clearAllOrderPolicies()"
                                    class="inline-flex items-center gap-1.5 h-8 px-3 rounded-xl bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-rose-200 hover:border-rose-300 text-xs sm:text-sm font-bold transition cursor-pointer shadow-xs shrink-0"
                                    title="Bỏ chọn tất cả chính sách">
                                <i class="fa-solid fa-xmark text-sm"></i>
                                <span>Bỏ chọn tất cả</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Danh sách các chính sách ĐANG ÁP DỤNG -->
                <template x-if="getSelectedPolicies().length > 0">
                    <div class="space-y-2.5">
                        <template x-for="p in getSelectedPolicies()" :key="p.id">
                            <div class="p-3.5 bg-white rounded-xl border border-sky-300 shadow-xs flex items-center justify-between gap-3 hover:border-sky-400 transition-colors">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <!-- Hàng 1: Quyền lợi ưu đãi (Chữ TO, ĐẬM, RÕ NÉT) -->
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-black uppercase tracking-wider shrink-0"
                                              :class="p.policy_type === 'quantity' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                              x-text="p.policy_type === 'quantity' ? 'Quà tặng' : (p.stacking_mode === 'stackable_order' ? 'CK cộng dồn' : 'Chiết khấu')"></span>
                                        <span class="text-base sm:text-lg font-black leading-snug"
                                              :class="p.policy_type === 'quantity' ? 'text-purple-700' : 'text-emerald-700'"
                                              x-text="getPolicyBenefitDescription(p)"></span>
                                        <template x-if="p.conflict_group">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold"
                                                  x-text="'Nhóm: ' + p.conflict_group"></span>
                                        </template>
                                    </div>

                                    <!-- Hàng 2: Tên chương trình & Sản phẩm áp dụng (Ngắn gọn, súc tích) -->
                                    <div class="text-xs sm:text-sm text-slate-600 font-semibold flex items-center gap-2 flex-wrap">
                                        <span class="text-slate-800 font-bold" x-text="p.name"></span>
                                        <span class="text-slate-300">•</span>
                                        <span class="text-slate-500 font-medium" x-text="'Áp dụng: ' + getPolicyAppliedProductsSummary(p)"></span>
                                    </div>

                                    <!-- Gợi ý nâng bậc tiếp theo (nếu có) -->
                                    <template x-if="getPolicyNextTierHint(p)">
                                        <div class="text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2 py-0.5 inline-flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-arrow-trend-up text-[10px]"></i>
                                            <span x-text="getPolicyNextTierHint(p)"></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Nút Xóa: Kích thước 36x36px to rõ, dễ bấm trên cảm ứng & chuột -->
                                <button type="button"
                                        @click="removeSelectedPolicy(p.id)"
                                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 border border-slate-200 hover:border-rose-300 flex items-center justify-center transition cursor-pointer shrink-0 shadow-xs active:scale-95"
                                        title="Bỏ chính sách này">
                                    <i class="fa-solid fa-trash-can text-sm sm:text-base"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Ô Dropdown để CHỌN THÊM CHÍNH SÁCH -->
                <div>
                    <template x-if="getAvailablePoliciesToAdd().length > 0">
                        <div class="flex items-center gap-2">
                            <select @change="if ($event.target.value) { addPolicyById($event.target.value); $event.target.value = ''; }"
                                    class="w-full h-12 px-4 bg-white border-2 border-slate-300 hover:border-sky-500 focus:border-sky-600 rounded-xl text-sm sm:text-base font-bold text-slate-800 focus:outline-none cursor-pointer shadow-xs transition-colors">
                                <option value="" x-text="getSelectedPolicies().length === 0 ? '+ Chọn chính sách ưu đãi cho đơn hàng' : '+ Chọn thêm chính sách cho sản phẩm khác...'"></option>
                                <template x-for="p in getAvailablePoliciesToAdd()" :key="p.id">
                                    <option :value="p.id" x-text="formatPolicyOptionLabel(p)"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <template x-if="getAvailablePoliciesToAdd().length === 0 && getSelectedPolicies().length === 0">
                        <div class="p-4 bg-white/90 rounded-xl border border-dashed border-slate-300 text-center text-xs sm:text-sm font-semibold text-slate-500">
                            <span x-show="getSalesItemsWithProduct().length === 0">Thêm sản phẩm vào đơn để áp dụng chính sách ưu đãi.</span>
                            <span x-show="getSalesItemsWithProduct().length > 0">Chưa có chính sách ưu đãi nào đạt ngưỡng cho các sản phẩm hiện tại.</span>
                        </div>
                    </template>

                    <!-- Gợi ý chính sách sắp đạt (Up-sell cho Sale) -->
                    <template x-if="getUpcomingPolicyHints().length > 0">
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <template x-for="hint in getUpcomingPolicyHints()" :key="hint.policyId">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-bold">
                                    <i class="fa-solid fa-lightbulb text-amber-500"></i>
                                    <span x-text="hint.text"></span>
                                </span>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- KHỐI NHẬP & QUẢN LÝ HÀNG KHUYẾN MẠI / TẶNG KÈM -->
            <div id="promo-section" 
                 x-show="showPromoSection || getGiftItems().length > 0" 
                 x-cloak
                 class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50/40 p-3 sm:p-4 space-y-3 shadow-2xs max-w-full relative z-20">
                <!-- Header -->
                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-emerald-200">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs shadow-2xs font-bold shrink-0">
                            <i class="fa-solid fa-gift"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="font-bold text-emerald-900 text-xs uppercase tracking-wide">Hàng khuyến mại</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" 
                                x-show="getGiftItems().length === 0" 
                                @click="showPromoSection = false" 
                                class="text-slate-400 hover:text-slate-600 px-2 py-0.5 text-xs font-bold cursor-pointer rounded-lg hover:bg-emerald-100 transition-colors flex items-center gap-1" title="Đóng ô nhập khuyến mại">
                            <i class="fa-solid fa-xmark text-sm"></i>
                            <span class="text-[11px]">Đóng</span>
                        </button>
                    </div>
                </div>

                <!-- Danh sách sản phẩm khuyến mại: Mobile Layout (phẳng, không lồng nhiều khung viền) -->
                <div class="block md:hidden divide-y divide-emerald-200/80">
                    <template x-for="(gItem, gIdx) in getGiftItems()" :key="gItem._rowKey || ('gift-m-' + gIdx)">
                        <div class="py-3 space-y-2.5 first:pt-1 last:pb-1"
                             :class="activeGiftDropdownIndex === gIdx ? 'relative z-30' : 'relative z-10'">
                            <!-- Hàng 1: Ô tìm & chọn sản phẩm (chiếm full chiều ngang) -->
                            <div class="relative" 
                                 :class="activeGiftDropdownIndex === gIdx ? 'z-40' : 'z-10'"
                                 @click.outside="if (activeGiftDropdownIndex === gIdx) activeGiftDropdownIndex = null">
                                <div class="relative flex items-center">
                                    <input type="text"
                                           :id="'gift-product-input-' + gIdx"
                                           autocomplete="off"
                                           autocorrect="off"
                                           spellcheck="false"
                                           x-model="gItem.searchQuery"
                                           @focus="activeGiftDropdownIndex = gIdx; giftHighlightIndex = 0; $event.target.select()"
                                           @input="gItem._prodSearching = true; activeGiftDropdownIndex = gIdx; giftHighlightIndex = 0"
                                           @keydown.down.prevent="
                                               activeGiftDropdownIndex = gIdx;
                                               const prods = getFilteredPromoProducts(getRowProductSearchQuery(gItem));
                                               giftHighlightIndex = prods.length > 0 ? Math.min(giftHighlightIndex + 1, prods.length - 1) : 0;
                                               scrollGiftProdIntoView(gIdx, giftHighlightIndex);
                                           "
                                           @keydown.up.prevent="
                                               giftHighlightIndex = Math.max(giftHighlightIndex - 1, 0);
                                               scrollGiftProdIntoView(gIdx, giftHighlightIndex);
                                           "
                                           @keydown.enter.prevent="
                                               if (activeGiftDropdownIndex === gIdx) {
                                                   const prods = getFilteredPromoProducts(getRowProductSearchQuery(gItem));
                                                   if (prods.length > 0 && prods[giftHighlightIndex]) {
                                                       selectGiftProductForRow(gItem, prods[giftHighlightIndex]);
                                                   }
                                               }
                                           "
                                           @keydown.escape.prevent="activeGiftDropdownIndex = null"
                                           placeholder="Gõ mã hoặc tên SP khuyến mại..."
                                           class="w-full h-10 pl-3.5 pr-8 bg-white border border-slate-300 rounded-xl text-sm font-semibold focus:border-emerald-500 focus:outline-none shadow-xs hover:border-slate-400 transition-colors">

                                    <button type="button"
                                            x-show="(gItem.searchQuery || gItem.product_id) && !isReadonly"
                                            x-cloak
                                            @mousedown.prevent
                                            @click.stop="clearGiftProductRow(gItem, gIdx)"
                                            class="absolute right-2 text-slate-400 hover:text-rose-500 p-1 rounded-md cursor-pointer transition-colors"
                                            title="Xóa để tìm sản phẩm khác">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>

                                <!-- Dropdown gợi ý sản phẩm trên mobile (phủ trọn chiều ngang) -->
                                <div x-show="activeGiftDropdownIndex === gIdx"
                                     x-cloak
                                     class="absolute left-0 top-full mt-1.5 w-full z-[60] bg-white rounded-xl shadow-2xl border border-slate-200 max-h-64 overflow-y-auto py-1 text-xs divide-y divide-slate-100">
                                    <template x-if="activeGiftDropdownIndex === gIdx">
                                        <div>
                                            <template x-for="(p, pIdx) in getFilteredPromoProducts(getRowProductSearchQuery(gItem))" :key="'gift-opt-' + gIdx + '-' + p.id">
                                                <div :id="'gift-opt-' + gIdx + '-' + pIdx"
                                                     @mousedown.prevent
                                                     @click="selectGiftProductForRow(gItem, p)"
                                                     @mouseenter="giftHighlightIndex = pIdx"
                                                     class="px-3 py-2.5 cursor-pointer transition-colors"
                                                     :class="giftHighlightIndex === pIdx ? 'bg-emerald-100 text-emerald-950 font-bold ring-1 ring-emerald-300' : 'hover:bg-slate-50'">
                                                    <div class="font-bold text-slate-900 leading-snug break-words">
                                                        <span class="text-emerald-700 font-mono mr-1 shrink-0" x-text="'[' + p.sku + ']'"></span>
                                                        <span x-text="p.name"></span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-1 flex-wrap">
                                                        <span class="whitespace-nowrap font-medium" x-text="'ĐVT: ' + (p.unit || '')"></span>
                                                        <span>&bull;</span>
                                                        <span class="text-emerald-700 font-semibold whitespace-nowrap" x-text="'Khả dụng: ' + formatNumber(getProductStockData(p, formData.warehouse).available) + ' ' + (p.unit || '')"></span>
                                                        <span>&bull;</span>
                                                        <span class="text-emerald-600 font-bold whitespace-nowrap">Đơn giá: 0đ</span>
                                                    </div>
                                                </div>
                                            </template>
                                            <div x-show="getFilteredPromoProducts(getRowProductSearchQuery(gItem)).length === 0" class="px-4 py-4 text-center text-slate-400">
                                                <div class="font-bold text-slate-600 text-xs">Không tìm thấy sản phẩm khuyến mại</div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Hàng 2: ĐVT, SỐ LƯỢNG & NÚT XÓA ngang hàng -->
                            <div class="flex items-end gap-2.5">
                                <!-- ĐVT -->
                                <div class="flex-1 min-w-0">
                                    <div class="text-[9px] font-bold text-slate-500 uppercase tracking-wide mb-1 text-center">ĐƠN VỊ TÍNH</div>
                                    <select x-model="gItem.unit"
                                            class="w-full h-9 px-2 bg-white border border-slate-300 hover:border-slate-400 focus:border-emerald-500 rounded-lg text-xs text-center font-bold text-slate-800 focus:outline-none cursor-pointer transition-colors shadow-2xs">
                                        <template x-for="u in getItemUnits(gItem)" :key="u">
                                            <option :value="u" x-text="u" :selected="gItem.unit === u"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- SỐ LƯỢNG -->
                                <div class="flex-1 min-w-0">
                                    <div class="text-[9px] font-bold text-slate-500 uppercase tracking-wide mb-1 text-center">SỐ LƯỢNG</div>
                                    <input type="number"
                                           :id="'gift-qty-input-' + gIdx"
                                           x-model.number="gItem.quantity"
                                           min="1"
                                           @input="gItem.gift_quantity = gItem.quantity; gItem._manualQty = true; calculateTotals()"
                                           @keydown.enter.prevent="addNewGiftRow()"
                                           class="w-full h-9 px-2 text-center font-black text-emerald-800 bg-white border border-slate-300 hover:border-slate-400 focus:border-emerald-500 rounded-lg text-sm focus:outline-none shadow-2xs transition-colors">
                                </div>

                                <!-- Nút Xóa (ngang hàng ô số lượng) -->
                                <div class="shrink-0">
                                    <button type="button" 
                                            @click="removeGiftItem(gItem)" 
                                            class="w-9 h-9 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 bg-white border border-slate-300 hover:border-rose-300 flex items-center justify-center transition-colors cursor-pointer shadow-2xs" 
                                            title="Xóa hàng khuyến mại">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Danh sách sản phẩm khuyến mại: Desktop Table Layout -->
                <div class="hidden md:block rounded-xl border border-emerald-200 bg-white"
                     :class="activeGiftDropdownIndex !== null ? 'overflow-visible' : 'overflow-x-auto'">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-emerald-100/80 text-emerald-950 font-bold uppercase text-xs tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3 text-center w-12">STT</th>
                                <th class="py-2.5 px-3">TÊN / MÃ SẢN PHẨM KHUYẾN MẠI</th>
                                <th class="py-2.5 px-2 text-center w-28">ĐVT</th>
                                <th class="py-2.5 px-2 text-center w-24">SỐ LƯỢNG</th>
                                <th class="py-2.5 px-3 text-left">CHÍNH SÁCH / LÝ DO</th>
                                <th class="py-2.5 px-2 text-center w-12">XÓA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 font-medium text-slate-800">
                            <template x-for="(gItem, gIdx) in getGiftItems()" :key="gItem._rowKey || ('gift-d-' + gIdx)">
                                <tr class="hover:bg-emerald-50/50 transition-colors"
                                    :class="activeGiftDropdownIndex === gIdx ? 'relative z-40' : ''">
                                    <td class="py-2 px-3 text-center font-bold text-emerald-700" x-text="gIdx + 1"></td>
                                    <td class="py-2 px-3 relative" @click.outside="if (activeGiftDropdownIndex === gIdx) activeGiftDropdownIndex = null">
                                        <div class="relative flex items-center">
                                            <input type="text"
                                                   :id="'gift-desk-input-' + gIdx"
                                                   autocomplete="off"
                                                   autocorrect="off"
                                                   spellcheck="false"
                                                   x-model="gItem.searchQuery"
                                                   @focus="activeGiftDropdownIndex = gIdx; giftHighlightIndex = 0; $event.target.select()"
                                                   @input="gItem._prodSearching = true; activeGiftDropdownIndex = gIdx; giftHighlightIndex = 0"
                                                   @keydown.down.prevent="
                                                       activeGiftDropdownIndex = gIdx;
                                                       const prods = getFilteredPromoProducts(getRowProductSearchQuery(gItem));
                                                       giftHighlightIndex = prods.length > 0 ? Math.min(giftHighlightIndex + 1, prods.length - 1) : 0;
                                                       scrollGiftProdIntoView(gIdx, giftHighlightIndex);
                                                   "
                                                   @keydown.up.prevent="
                                                       giftHighlightIndex = Math.max(giftHighlightIndex - 1, 0);
                                                       scrollGiftProdIntoView(gIdx, giftHighlightIndex);
                                                   "
                                                   @keydown.enter.prevent="
                                                       if (activeGiftDropdownIndex === gIdx) {
                                                           const prods = getFilteredPromoProducts(getRowProductSearchQuery(gItem));
                                                           if (prods.length > 0 && prods[giftHighlightIndex]) {
                                                               selectGiftProductForRow(gItem, prods[giftHighlightIndex]);
                                                           }
                                                       }
                                                   "
                                                   @keydown.escape.prevent="activeGiftDropdownIndex = null"
                                                   placeholder="Tìm mã hoặc tên sản phẩm khuyến mại..."
                                                   :placeholder="isMobileViewport ? 'Tìm mã hoặc tên sản phẩm khuyến mại...' : 'Tìm mã hoặc tên sản phẩm khuyến mại (phím ↑ ↓ Enter)...'"
                                                   class="w-full h-10 pl-3 pr-8 bg-white border border-slate-300 rounded-xl text-sm font-semibold focus:border-emerald-500 focus:outline-none shadow-xs hover:border-slate-400 transition-colors">

                                            <button type="button"
                                                    x-show="(gItem.searchQuery || gItem.product_id) && !isReadonly"
                                                    x-cloak
                                                    @mousedown.prevent
                                                    @click.stop="clearGiftProductRow(gItem, gIdx)"
                                                    class="absolute right-2 text-slate-400 hover:text-rose-500 p-1 rounded-md cursor-pointer transition-colors"
                                                    title="Xóa để tìm sản phẩm khác">
                                                <i class="fa-solid fa-xmark text-xs"></i>
                                            </button>
                                        </div>

                                        <!-- Dropdown gợi ý trên desktop -->
                                        <div x-show="activeGiftDropdownIndex === gIdx"
                                             x-cloak
                                             class="order-product-dropdown absolute left-3 top-full mt-1 w-[460px] max-w-[90vw] z-[60] bg-white rounded-xl shadow-2xl border border-emerald-200 max-h-56 overflow-y-auto py-1 text-xs">
                                            <template x-if="activeGiftDropdownIndex === gIdx">
                                                <div>
                                                    <div class="px-3 py-1 bg-emerald-50/70 text-[10px] font-bold text-emerald-700 uppercase tracking-wider flex items-center justify-between sticky top-0 z-10 border-b border-emerald-100">
                                                        <span>Sản phẩm khuyến mại (<span x-text="getFilteredPromoProducts(getRowProductSearchQuery(gItem)).length"></span>)</span>
                                                        <span class="text-emerald-600 font-normal lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                                    </div>
                                                    <template x-for="(p, pIdx) in getFilteredPromoProducts(getRowProductSearchQuery(gItem))" :key="'gift-desk-opt-' + gIdx + '-' + p.id">
                                                        <div :id="'gift-desk-opt-' + gIdx + '-' + pIdx"
                                                             @mousedown.prevent
                                                             @click="selectGiftProductForRow(gItem, p)"
                                                             @mouseenter="giftHighlightIndex = pIdx"
                                                             class="px-3 py-2 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                                             :class="giftHighlightIndex === pIdx ? 'bg-emerald-100 text-emerald-950 font-bold ring-1 ring-emerald-300' : 'hover:bg-slate-50'">
                                                            <div class="min-w-0 pr-3 flex-1">
                                                                <div class="font-bold text-slate-900 leading-snug break-words">
                                                                    <span class="text-emerald-700 font-mono mr-1" x-text="'[' + p.sku + ']'"></span>
                                                                    <span x-text="p.name"></span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                                    <span x-text="'ĐVT: ' + (p.unit || '')"></span>
                                                                    <span>&bull;</span>
                                                                    <span class="text-emerald-700 font-semibold" x-text="'Khả dụng: ' + formatNumber(getProductStockData(p, formData.warehouse).available) + ' ' + (p.unit || '')"></span>
                                                                </div>
                                                            </div>
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">KM 0đ</span>
                                                        </div>
                                                    </template>
                                                    <div x-show="getFilteredPromoProducts(getRowProductSearchQuery(gItem)).length === 0" class="px-4 py-4 text-center text-slate-400">
                                                        <div class="font-bold text-slate-600 text-xs">Không tìm thấy sản phẩm khuyến mại</div>
                                                        <button type="button"
                                                                @mousedown.prevent
                                                                @click.stop="gItem.searchQuery = ''; gItem._prodSearching = true; giftHighlightIndex = 0; $nextTick(() => document.getElementById('gift-desk-input-' + gIdx)?.focus())"
                                                                class="mt-1.5 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg text-[11px] cursor-pointer transition-colors">
                                                            Xem tất cả sản phẩm
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-2 px-2 text-center">
                                        <select x-model="gItem.unit" class="w-full h-10 px-2 text-center font-bold text-slate-800 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none">
                                            <template x-for="u in getItemUnits(gItem)" :key="u">
                                                <option :value="u" x-text="u" :selected="gItem.unit === u"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-1.5 px-2 text-center">
                                        <input type="number"
                                               :id="'gift-desk-qty-' + gIdx"
                                               x-model.number="gItem.quantity"
                                               @input="gItem.gift_quantity = gItem.quantity; gItem._manualQty = true; calculateTotals()"
                                               @keydown.enter.prevent="addNewGiftRow()"
                                               min="1"
                                               class="w-20 h-10 px-2 text-center font-black text-emerald-700 bg-emerald-50/60 border border-emerald-300 rounded-xl text-sm sm:text-base focus:bg-white focus:outline-none">
                                    </td>
                                    <td class="py-2 px-3 text-slate-600 text-[11px] font-semibold" x-text="gItem.appliedTierPolicy || gItem.price_type || 'Hàng khuyến mại'"></td>
                                    <td class="py-2 px-2 text-center">
                                        <button type="button" @click="removeGiftItem(gItem)" class="w-6 h-6 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded flex items-center justify-center transition-colors cursor-pointer mx-auto" title="Xóa hàng khuyến mại">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state hint khi chưa có hàng khuyến mại -->
                <div x-show="getGiftItems().length === 0" class="py-3 px-4 text-center text-xs text-emerald-700 italic bg-white/70 rounded-xl border border-dashed border-emerald-200">
                    <i class="fa-solid fa-gift mr-1 text-emerald-500"></i>
                    <span>Chưa có sản phẩm tặng nào. Bấm nút <b>Hàng tặng</b> bên dưới để thêm hàng.</span>
                </div>

                <!-- Nút Hàng tặng phía dưới -->
                <div class="pt-2 flex items-center justify-start border-t border-emerald-100/80">
                    <button type="button" 
                            @click="addNewGiftRow()" 
                            @mousedown.prevent
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-10 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition-colors cursor-pointer shadow-xs active:scale-95 shrink-0">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Hàng tặng</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. Footer / Summary Area -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pt-2">
            <!-- Left Column: Ghi chú & Alert Info -->
            <div class="lg:col-span-7 space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 tracking-wide uppercase mb-1.5">Ghi chú đơn hàng</label>
                    <textarea x-model="formData.notes" rows="3" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium focus:border-sky-500 focus:outline-none shadow-xs"></textarea>
                </div>
            </div>

            <!-- Right Column: Financial Calculations Summary -->
            <div class="lg:col-span-5 bg-slate-50/80 p-4 rounded-xl border border-slate-200/70 space-y-2.5 text-xs font-semibold">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tổng tiền hàng</span>
                        <span class="font-bold text-slate-800" x-text="formatNumber(subtotalBeforeVat) + ' đ'"></span>
                    </div>

                    <div class="flex items-center justify-between gap-2 text-slate-600 pt-1">
                        <span class="shrink-0" title="Chiết khấu đơn hàng">CK</span>
                        <div class="flex items-center justify-end gap-1.5 min-w-0 flex-1 sm:flex-initial">
                            <div class="flex items-center gap-0.5 shrink-0">
                                <input type="number" min="0" max="100" step="any" x-model.number="orderDiscountPercent" @input="onOrderDiscountPercentChange()" placeholder="%" class="w-14 sm:w-20 px-1.5 sm:px-2 py-1 bg-white border border-slate-200 rounded text-center text-xs focus:outline-none font-semibold">
                                <span class="text-[10px] text-slate-400 shrink-0">%</span>
                            </div>
                            <div class="flex items-center gap-1 min-w-0 flex-1 sm:flex-initial">
                                <input type="text" inputmode="numeric" x-money="formData.discount_amount" @input="onOrderDiscountAmountChange()" placeholder="Tiền CK" class="w-full sm:w-28 min-w-0 px-2 py-1 bg-white border border-slate-200 rounded text-right text-xs font-bold focus:border-sky-500 focus:outline-none">
                                <span class="text-slate-400 shrink-0">đ</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-sm font-black text-slate-900">
                        <span class="text-base sm:text-lg font-black text-slate-900">Thành tiền</span>
                        <span class="text-2xl sm:text-3xl text-sky-600 font-black" x-text="formatNumber(finalTotal) + ' đ'"></span>
                    </div>

                    <!-- Dòng công nợ dự kiến sau đơn -->
                    <template x-if="getSelectedDealer()">
                        <div class="pt-2 border-t border-dashed border-slate-200 text-xs">
                            <div class="flex items-center justify-between" :class="isDebtOverLimit() ? 'text-rose-700 font-bold' : 'text-slate-600 font-medium'">
                                <span class="flex items-center gap-1">
                                    <i :class="isDebtOverLimit() ? 'fa-solid fa-triangle-exclamation text-rose-500' : 'fa-solid fa-clock-rotate-left text-slate-400'"></i>
                                    <span>Nợ dự kiến sau đơn:</span>
                                </span>
                                <span class="font-mono font-bold" :class="isDebtOverLimit() ? 'text-rose-700 font-black' : 'text-slate-800'" x-text="formatNumber(getProjectedDebt()) + ' đ'"></span>
                            </div>
                            <div x-show="isDebtOverLimit()" class="text-right text-[10px] text-rose-600 font-extrabold mt-0.5">
                                ⚠️ Vượt hạn mức: +<span x-text="formatNumber(getDebtExcessAmount()) + ' đ'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    </fieldset>

    <!-- 6. Floating / Sticky Action Bar (Bottom Bar: 0 MẶT HÀNG | Đóng Esc | Lưu & In Ctrl+F9 | Lưu F9) -->
    <div class="order-form-bottom-bar fixed bottom-0 left-0 right-0 z-[9999] bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 sm:px-8 py-3.5 shadow-lg flex items-center justify-between gap-3">
        <!-- Left: Items count & Total -->
        <div class="flex items-center gap-3 text-sm sm:text-xs font-bold text-slate-600">
            <span class="uppercase tracking-wider font-black text-sm sm:text-base text-slate-800" x-text="items.length + ' mặt hàng'"></span>
            <span class="text-slate-300">|</span>
            <div class="flex items-center gap-3">
                <span class="text-lg sm:text-2xl font-black text-sky-600" x-text="formatNumber(finalTotal) + ' đ'"></span>
                <!-- Badges cảnh báo nhanh ở thanh footer -->
                <template x-if="isNoTaxCodeLimitReached()">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1 animate-pulse">
                        <i class="fa-solid fa-ban text-rose-600"></i>
                        <span>Chưa MST (đã 3 đơn)</span>
                    </span>
                </template>
                <template x-if="isDebtOverLimit()">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1 animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Vượt nợ (+<span x-text="formatNumber(getDebtExcessAmount())"></span>)</span>
                    </span>
                </template>
                <template x-if="getOverStockItems().length > 0">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold flex items-center gap-1"
                          :class="getPhysicalShortageItems().length > 0 ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300'">
                        <i :class="getPhysicalShortageItems().length > 0 ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-boxes-stacked'"></i>
                        <span x-text="getPhysicalShortageItems().length > 0 ? 'Vượt kho (' + getPhysicalShortageItems().length + ' SP)' : 'Lẹm giữ (' + getReservedShortageItems().length + ' SP)'"></span>
                    </span>
                </template>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex items-center gap-2.5">
            <button type="button" @click="confirmClose()" class="h-10 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span x-text="isFromPos() ? 'Quay lại POS' : 'Đóng'"></span>
                <span class="shortcut-hint hidden md:inline"> (Esc)</span>
            </button>

            <!-- Readonly Mode Actions -->
            <template x-if="isReadonly">
                <div class="flex items-center gap-2">
                    <button type="button" @click="printCurrentOrder()" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-print"></i>
                        <span>In chứng từ</span>
                    </button>
                    @if($order)
                        <a href="{{ url('/orders/' . $order->id . '/edit') }}" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Bổ sung / Chỉnh sửa đơn</span>
                        </a>
                    @endif
                </div>
            </template>

            <!-- Normal Edit / Create Mode Actions -->
            <template x-if="!isReadonly">
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="submitOrder(true)" 
                            :disabled="saving || isNoTaxCodeLimitReached() || getOverStockItems().length > 0" 
                            class="btn-save-print hidden sm:inline-flex h-10 px-5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm rounded-xl shadow-xs transition-all items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" 
                            :title="isNoTaxCodeLimitReached() ? 'Chặn lên đơn: Khách hàng chưa có MST và đã đạt 3 đơn' : (getOverStockItems().length > 0 ? 'Không thể lưu: Hết tồn kho khả dụng' : 'Lưu đơn và in chứng từ (Ctrl+F9)')">
                        <i class="fa-solid fa-print"></i>
                        <span>Lưu & In<span class="shortcut-hint hidden md:inline"> (Ctrl+F9)</span></span>
                    </button>
                    <button type="button" @click="submitOrder(false)" 
                            :disabled="saving || isNoTaxCodeLimitReached() || getOverStockItems().length > 0" 
                            :class="isNoTaxCodeLimitReached() || getOverStockItems().length > 0 ? 'bg-rose-600 hover:bg-rose-700' : 'bg-sky-600 hover:bg-sky-700'"
                            class="h-10 px-6 text-white font-black text-sm sm:text-base rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" 
                            :title="isNoTaxCodeLimitReached() ? 'Chặn lên đơn: Khách hàng chưa có MST và đã đạt 3 đơn' : (getOverStockItems().length > 0 ? 'Không thể lên đơn do hết tồn kho khả dụng' : 'Lưu đơn hàng (F9)')">
                        <i class="fa-solid fa-ban" x-show="!saving && (isNoTaxCodeLimitReached() || getOverStockItems().length > 0)"></i>
                        <i class="fa-solid fa-floppy-disk" x-show="!saving && !isNoTaxCodeLimitReached() && getOverStockItems().length === 0"></i>
                        <i class="fa-solid fa-circle-notch fa-spin" x-show="saving" x-cloak></i>
                        <span><span x-text="saving ? 'Đang lưu...' : (isNoTaxCodeLimitReached() ? 'Chặn: Cần MST (đã 3 đơn)' : (getOverStockItems().length > 0 ? 'Hết tồn khả dụng (' + getOverStockItems().length + ' SP)' : 'Lưu'))"></span><span class="shortcut-hint hidden md:inline" x-show="!saving && !isNoTaxCodeLimitReached() && getOverStockItems().length === 0"> (F9)</span></span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Add Customer Modal -->
    <div x-show="showAddDealer" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" @click.self="showAddDealer = false">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-sky-600"></i>
                    <span>Thêm Khách Hàng / Đại Lý Nhanh</span>
                </h3>
                <button type="button" @click="showAddDealer = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Tên khách hàng / Đại lý <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="newDealer.name" placeholder="Nhập tên đại lý / khách hàng..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Số điện thoại</label>
                    <input type="text" x-model="newDealer.phone" placeholder="Nhập số điện thoại..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Mã số thuế (MST)</label>
                    <input type="text" x-model="newDealer.tax_code" placeholder="Nhập mã số thuế..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Cấp đại lý / Bảng giá</label>
                    <select x-model="newDealer.tier" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none">
                        <template x-for="pt in priceTiersCatalog" :key="pt.id">
                            <option :value="pt.name" x-text="pt.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Địa chỉ giao hàng</label>
                    <textarea x-model="newDealer.address" rows="2" placeholder="Số nhà, đường, huyện/tỉnh..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showAddDealer = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs cursor-pointer">Hủy</button>
                <button type="button" @click="applyNewDealer()" :disabled="addingDealer" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold text-xs cursor-pointer flex items-center gap-1.5 disabled:opacity-50">
                    <i class="fa-solid fa-spinner fa-spin" x-show="addingDealer" x-cloak></i>
                    <span x-text="addingDealer ? 'Đang lưu...' : 'Lưu & Áp dụng'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Pre-load data in JSON scripts to avoid heavy AST compilation in JS -->
    <script id="orders-products-json" type="application/json">{!! json_encode($products, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) !!}</script>
    <script id="orders-dealers-json" type="application/json">{!! json_encode($dealers, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) !!}</script>
    <script id="orders-policies-json" type="application/json">{!! json_encode($salesPolicies ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) !!}</script>

</div>

<script>
// Normalize text for Vietnamese accent-insensitive, punctuation- and spacing-tolerant search
const _normalizeSearchText = (str) => {
    if (str === null || str === undefined) return '';
    return String(str)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
};

// Phạm vi Hãng / Nhà cung cấp được phép xem & tặng của nhân viên (rỗng = toàn bộ)
const _restrictedBrandId = {{ !empty($userRestrictedBrandId) ? (int) $userRestrictedBrandId : 'null' }};
const _restrictedSupplierId = {{ !empty($userRestrictedSupplierId) ? (int) $userRestrictedSupplierId : 'null' }};
const _isProductInUserScope = (p) => {
    if (!_restrictedBrandId && !_restrictedSupplierId) return true;
    if (!p) return false;
    if (_restrictedBrandId && Number(p.brand_id) === Number(_restrictedBrandId)) return true;
    if (_restrictedSupplierId && Number(p.supplier_id) === Number(_restrictedSupplierId)) return true;
    return false;
};

// Parse catalog data once and index by ID + normalized search fields for fast lookup
const _rawProductsCatalog = (JSON.parse(document.getElementById('orders-products-json')?.textContent || '[]')).filter(_isProductInUserScope);
const _rawDealersData = JSON.parse(document.getElementById('orders-dealers-json')?.textContent || '[]');
const _rawSalesPolicies = JSON.parse(document.getElementById('orders-policies-json')?.textContent || '[]');
const _productsMap = new Map();
const _productSearchIndex = new Map();
for (let i = 0; i < _rawProductsCatalog.length; i++) {
    const p = _rawProductsCatalog[i];
    _productsMap.set(String(p.id), p);
    const rawSku = String(p.sku || '').toLowerCase().trim();
    const rawName = String(p.name || '').toLowerCase().trim();
    const cleanSku = _normalizeSearchText(p.sku || '');
    const cleanName = _normalizeSearchText(p.name || '');
    const clean = `${cleanSku} ${cleanName}`.trim();
    _productSearchIndex.set(String(p.id), {
        raw: `${rawSku} ${rawName}`.trim(),
        rawSku,
        rawName,
        cleanSku,
        cleanName,
        clean,
        compactSku: cleanSku.replace(/\s+/g, ''),
        compactName: cleanName.replace(/\s+/g, ''),
        compact: clean.replace(/\s+/g, ''),
    });
}
for (let i = 0; i < _rawDealersData.length; i++) {
    Object.freeze(_rawDealersData[i]);
}


// Global fallback helpers on window so template expressions never throw ReferenceError
window.getSelectedPolicies = function() {
    const el = document.querySelector('.order-form-app-wrapper');
    if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
        const app = window.Alpine.$data(el);
        if (app && typeof app.getSelectedPolicies === 'function') return app.getSelectedPolicies();
    }
    return [];
};
window.getAvailablePoliciesToAdd = function() {
    const el = document.querySelector('.order-form-app-wrapper');
    if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
        const app = window.Alpine.$data(el);
        if (app && typeof app.getAvailablePoliciesToAdd === 'function') return app.getAvailablePoliciesToAdd();
    }
    return [];
};
window.getPolicyBenefitDescription = function(p) {
    const el = document.querySelector('.order-form-app-wrapper');
    if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
        const app = window.Alpine.$data(el);
        if (app && typeof app.getPolicyBenefitDescription === 'function') return app.getPolicyBenefitDescription(p);
    }
    return '';
};
window.getPolicyAppliedProductsSummary = function(p) {
    const el = document.querySelector('.order-form-app-wrapper');
    if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
        const app = window.Alpine.$data(el);
        if (app && typeof app.getPolicyAppliedProductsSummary === 'function') return app.getPolicyAppliedProductsSummary(p);
    }
    return '';
};
window.formatPolicyOptionLabel = function(p) {
    const el = document.querySelector('.order-form-app-wrapper');
    if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
        const app = window.Alpine.$data(el);
        if (app && typeof app.formatPolicyOptionLabel === 'function') return app.formatPolicyOptionLabel(p);
    }
    return '';
};
window.clearAllOrderPolicies = function() {
    const el = document.querySelector('.order-form-app-wrapper');
    if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
        const app = window.Alpine.$data(el);
        if (app && typeof app.clearAllOrderPolicies === 'function') app.clearAllOrderPolicies();
    }
};
window.addPolicyById = function(id) {
    const el = document.querySelector('.order-form-app-wrapper');
    if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
        const app = window.Alpine.$data(el);
        if (app && typeof app.addPolicyById === 'function') app.addPolicyById(id);
    }
};
window.removeSelectedPolicy = function(id) {
    const el = document.querySelector('.order-form-app-wrapper');
    if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
        const app = window.Alpine.$data(el);
        if (app && typeof app.removeSelectedPolicy === 'function') app.removeSelectedPolicy(id);
    }
};

function orderFormApp() {
    return {
        companySetting: @json(\App\Models\CompanySetting::getSettings()),
        isReadonly: {{ (request('readonly') == '1' || request('mode') === 'readonly') ? 'true' : 'false' }},
        saving: false,
        errorMessage: '',
        showAddDealer: false,
        addingDealer: false,
        newDealer: {
            name: '',
            phone: '',
            tier: '',
            address: '',
            tax_code: ''
        },
        originalDealerId: '{{ $order ? $order->dealer_id : "" }}',
        dealersData: [..._rawDealersData],
        warehousesCatalog: @json($warehouses ?? []),
        mainCompanyWarehouse: '{{ $mainCompanyWarehouse ?? ($warehouses->first()?->name ?? "Kho Tổng") }}',
        employeeWarehouse: '{{ $employeeWarehouse ?? "" }}',
        isDriverUser: false,
        productsCatalog: Object.freeze(_rawProductsCatalog),
        salesPoliciesCatalog: Object.freeze(_rawSalesPolicies),
        priceTiersCatalog: @json($priceTiers ?? []),
        autoApplyPromotions: false,
        appliedPromotionNotices: [],
        appliedPromoPolicyIds: [],
        _isApplyingPromotions: false,
        _isApplyingOrderPolicies: false,
        _autoOrderDiscount: false,
        _manualOrderDiscount: false,
        activeProdDropdownIndex: null,
        prodHighlightIndex: 0,
        showPromoSection: false,
        newPromoItem: {
            product_id: '',
            sku: '',
            name: '',
            searchQuery: '',
            unit: '',
            quantity: 1,
            policyName: 'Hàng khuyến mại',
        },
        activePromoDropdown: false,
        promoHighlightIndex: 0,
        activeGiftDropdownIndex: null,
        giftHighlightIndex: 0,
        bulkDiscountPercent: '',
        originalOrderTotal: {{ $order ? (float)$order->total_amount : 0 }},
        isOriginalOrderInDebt: {{ ($order && !in_array($order->status, ['Báo giá', 'Bản nháp', 'Hủy', 'Đã hủy'])) ? 'true' : 'false' }},
        activeTicketIndex: 0,
        ticketCounter: 1,
        tickets: [
            {
                id: 1,
                title: '{{ $order ? ($order->order_code ?: "Phiếu đơn hàng") : "Phiếu 1" }}',
                orderId: '{{ $order ? $order->id : "" }}',
                orderDiscountPercent: {{ $orderDiscPct }},
                bulkDiscountPercent: '',
                subtotalBeforeVat: 0,
                totalVat: 0,
                totalWithVat: 0,
                finalTotal: 0,
                formData: {
                    id: '{{ $order ? $order->id : "" }}',
                    order_code: '{{ $order ? $order->order_code : "" }}',
                    order_date: '{{ $order && $order->created_at ? $order->created_at->format("Y-m-d") : "" }}',
                    delivery_date: '{{ $order ? ($order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format("Y-m-d") : "") : "" }}',
                    picking_date: '{{ $order ? ($order->picking_date ? \Carbon\Carbon::parse($order->picking_date)->format("Y-m-d") : "") : "" }}',
                    order_type: '{{ $order ? ($order->order_type ?? "normal") : (request("order_type") ?: "normal") }}',
                    dealer_id: '{{ $order ? $order->dealer_id : "" }}',
                    customer_code: '{{ $order && $order->dealer ? addslashes($order->dealer->code) : "" }}',
                    customer_name: '{{ $order ? addslashes($order->customer_name) : "" }}',
                    customer_phone: '{{ $order ? addslashes($order->customer_phone) : "" }}',
                    customer_address: '{{ $order ? addslashes($order->customer_address) : "" }}',
                    dealer_tier: '{{ ($order && !empty($order->dealer_tier)) ? $order->dealer_tier : "Nguyên Giá" }}',
                    warehouse: '{{ $order ? $order->warehouse : ((request("order_type") === "consignment") ? ($employeeWarehouse ?? "") : ($mainCompanyWarehouse ?? ($warehouses->first()?->name ?? "Kho Tổng"))) }}',
                    payment_method: '{{ $order ? $order->payment_method : "Ghi nợ đại lý" }}',
                    status: '{{ $order ? $order->status : "Chốt đơn" }}',
                    notes: '{{ $order ? addslashes(str_replace(["\r", "\n"], ["", "\\n"], $order->notes)) : "" }}',
                    discount_percent: {{ $orderDiscPct }},
                    discount_amount: {{ $order ? (float)$order->discount_amount : 0 }},
                    sales_policy_id: '{{ !empty($initialPolicyIds[0]) ? $initialPolicyIds[0] : "" }}',
                    sales_policy_ids: @json($initialPolicyIds),
                    order_policy_id: '',
                    tax_amount: {{ $order ? (float)$order->tax_amount : 0 }},
                    sales_rep_name: '{{ $order ? addslashes($order->salesRep?->name ?: ($order->dealer?->salesRep?->name ?: ($order->order_creator ?: ($order->creator?->name ?: "")))) : addslashes(auth()->user()->name ?? "") }}',
                },
                items: @json($initialItems)
            }
        ],

        // Active references bound to current ticket
        orderId: '',
        orderDiscountPercent: 0,
        subtotalBeforeVat: 0,
        totalVat: 0,
        totalWithVat: 0,
        finalTotal: 0,
        subtotal: 0,
        totalAmount: 0,
        formData: {},
        items: [],
        isMobileViewport: typeof window !== 'undefined' ? window.innerWidth <= 768 : false,
        _initialized: false,

        init() {
            if (this._initialized) return;
            this._initialized = true;
            window.addEventListener('resize', () => {
                this.isMobileViewport = window.innerWidth <= 768;
            });
            this.syncCurrentTicket();
            
            // Đồng bộ loại giá phía trên với phía dưới sản phẩm và VAT theo cài đặt sản phẩm
            const topTier = this.formData.dealer_tier || 'Nguyên Giá';
            this.items.forEach((it, idx) => {
                if (!it._rowKey) {
                    it._rowKey = 'it-' + (it.item_id_db || it.id || idx) + '-' + Math.random();
                }
                if (!it.isPromotionalGift && (!it.price_type || it.price_type === 'Theo phiếu')) {
                    it.price_type = topTier;
                    it.appliedTierPolicy = topTier;
                }
                it.vat_percent = 0;
                it.vat_amount = 0;
            });

            if (this.getGiftItems().length > 0) {
                this.showPromoSection = true;
            }

            // Liên kết lại các quà tặng / chiết khấu đã lưu với chính sách của đơn (nếu đang mở sửa đơn)
            this.rebindSavedOrderPolicies();
            this.calculateTotals();

            const urlParams = new URLSearchParams(window.location.search);
            const isFromPosEntry = !this.orderId && (urlParams.get('from_pos') === '1');
            if (urlParams.get('from_pos') === '1') {
                sessionStorage.setItem('order_from_pos', '1');
                sessionStorage.setItem('order_return_url', '{{ route('orders.pos') }}');
            }

            // Nạp sản phẩm từ Giao diện Bán hàng (POS) nếu đi từ luồng POS (chỉ xóa storage khi đã lưu đơn thành công)
            if (isFromPosEntry) {
                this.loadItemsFromPosCart();
            }

            if (urlParams.get('print') === '1') {
                this.$nextTick(() => {
                    setTimeout(() => this.printCurrentOrder(), 500);
                });
            }
        },

        // Nạp các sản phẩm từ Giỏ hàng Bán hàng (POS) chuyển sang
        loadItemsFromPosCart() {
            try {
                const raw = sessionStorage.getItem('pos_cart_items');
                if (!raw) return;
                const cart = JSON.parse(raw);
                if (!Array.isArray(cart) || cart.length === 0) return;

                const targetTicket = this.tickets[this.activeTicketIndex];
                if (!targetTicket) return;
                targetTicket._fromPos = true;

                // Nếu có chọn đại lý từ giao diện POS
                const savedDealerId = sessionStorage.getItem('pos_dealer_id');
                if (savedDealerId) {
                    const d = (this.dealersData || []).find(x => String(x.id) === String(savedDealerId));
                    if (d) {
                        this.selectDealer(d);
                    } else {
                        this.formData.dealer_id = savedDealerId;
                        targetTicket.formData.dealer_id = savedDealerId;
                    }
                }

                // Nếu có chọn kho từ giao diện POS
                const savedWarehouse = sessionStorage.getItem('pos_warehouse');
                if (savedWarehouse) {
                    this.formData.warehouse = savedWarehouse;
                    targetTicket.formData.warehouse = savedWarehouse;
                }

                const topTier = this.formData.dealer_tier || 'Nguyên Giá';
                const newItems = [];

                cart.forEach(cIt => {
                    const prod = this.getProduct(cIt.product_id) || {};
                    const qty = Number(cIt.quantity) || 1;
                    const price = Number(cIt.price !== undefined ? cIt.price : (prod.price || 0));
                    const lineBase = qty * price;

                    newItems.push({
                        _rowKey: 'pos-' + cIt.product_id + '-' + Date.now() + '-' + Math.random(),
                        id: '',
                        itemId: String(cIt.product_id),
                        product_id: cIt.product_id,
                        sku: cIt.sku || prod.sku || '',
                        name: cIt.name || prod.name || '',
                        searchQuery: cIt.name || prod.name || '',
                        unit: cIt.unit || prod.unit || 'cái',
                        quantity: qty,
                        gift_quantity: 0,
                        unitPrice: price,
                        originalPrice: Number(prod.price > 0 ? prod.price : (prod.cost_price || 0)),
                        costPrice: Number(prod.cost_price || 0),
                        discount_percent: 0,
                        discount_amount: 0,
                        vat_percent: 0,
                        vat_amount: 0,
                        appliedTierPolicy: topTier,
                        price_type: topTier,
                        selected_policy_id: '',
                        amount: lineBase,
                        checked: true,
                        isPromotionalGift: false,
                    });
                });

                if (newItems.length > 0) {
                    // Cập nhật cả ticket đang kích hoạt và biến tham chiếu items
                    targetTicket.items = newItems;
                    this.items = newItems;

                    // Tính lại chi tiết từng dòng và tổng đơn
                    for (let i = 0; i < this.items.length; i++) {
                        this.updateLineTotal(i);
                    }
                    this.evaluateOrderPolicies();
                    this.calculateTotals();
                }
            } catch (err) {
                console.error('Lỗi khi nạp giỏ hàng từ POS:', err);
            }
        },

        syncCurrentTicket() {
            const t = this.tickets[this.activeTicketIndex];
            if (t) {
                if (!Array.isArray(t.formData.sales_policy_ids)) {
                    t.formData.sales_policy_ids = t.formData.sales_policy_id ? [String(t.formData.sales_policy_id)] : [];
                }
                if (t.formData.order_policy_id === undefined) {
                    t.formData.order_policy_id = '';
                }
                this.formData = t.formData;
                this.items = t.items;
                this.orderId = t.orderId || '';
                this.orderDiscountPercent = t.orderDiscountPercent || 0;
                this.bulkDiscountPercent = t.bulkDiscountPercent !== undefined ? t.bulkDiscountPercent : '';
            }
        },

        switchTicket(index) {
            if (index < 0 || index >= this.tickets.length) return;
            this.activeTicketIndex = index;
            this.syncCurrentTicket();
            this.calculateTotals();
        },

        addNewTicket() {
            this.ticketCounter++;
            const newTicket = {
                id: Date.now(),
                title: 'Phiếu ' + this.ticketCounter,
                orderId: '',
                orderDiscountPercent: 0,
                subtotalBeforeVat: 0,
                totalVat: 0,
                totalWithVat: 0,
                finalTotal: 0,
                formData: {
                    id: '',
                    order_code: '',
                    order_date: '',
                    delivery_date: '',
                    picking_date: '',
                    order_type: 'normal',
                    dealer_id: '',
                    customer_code: '',
                    customer_name: '',
                    customer_phone: '',
                    customer_address: '',
                    dealer_tier: 'Nguyên Giá',
                    warehouse: this.mainCompanyWarehouse || 'Kho Tổng',
                    payment_method: 'Ghi nợ đại lý',
                    status: 'Chốt đơn',
                    notes: '',
                    discount_amount: 0,
                    sales_policy_id: '',
                    sales_policy_ids: [],
                    order_policy_id: '',
                    tax_amount: 0,
                },
                items: [
                    {
                        _rowKey: 'init-' + Date.now() + '-' + Math.random(),
                        id: '',
                        itemId: '',
                        product_id: '',
                        sku: '',
                        name: '',
                        searchQuery: '',
                        unit: 'mét',
                        quantity: 1,
                        gift_quantity: 0,
                        unitPrice: 0,
                        originalPrice: 0,
                        costPrice: 0,
                        discount_percent: 0,
                        discount_amount: 0,
                        vat_percent: 0,
                        vat_amount: 0,
                        appliedTierPolicy: 'Theo phiếu',
                        price_type: 'Theo phiếu',
                        selected_policy_id: '',
                        amount: 0,
                        checked: true,
                    }
                ]
            };
            this.tickets.push(newTicket);
            this.switchTicket(this.tickets.length - 1);
        },

        isFromPos() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('from_pos') === '1' || sessionStorage.getItem('order_from_pos') === '1' || document.referrer.includes('/orders/pos');
        },

        getReturnUrl() {
            if (this.isFromPos()) {
                return '{{ route('orders.pos') }}';
            }
            const returnUrl = sessionStorage.getItem('order_return_url');
            if (returnUrl) {
                return returnUrl;
            }
            return '{{ route('orders.index') }}';
        },

        goBack() {
            const returnUrl = this.getReturnUrl();
            const t = this.tickets ? this.tickets[this.activeTicketIndex] : null;
            if (this.isFromPos()) {
                const hasTypedOrderInfo = t && t.formData && (
                    t.formData.customer_name ||
                    t.formData.notes ||
                    (Array.isArray(t.formData.sales_policy_ids) && t.formData.sales_policy_ids.filter(Boolean).length > 0)
                );
                if (hasTypedOrderInfo) {
                    if (!confirm('Đơn hàng chưa lưu (sản phẩm vẫn được giữ lại trong giỏ hàng POS). Bạn có chắc muốn quay lại trang Bán Hàng POS?')) {
                        return;
                    }
                }
                sessionStorage.removeItem('order_from_pos');
                sessionStorage.removeItem('order_return_url');
                window.location.href = returnUrl;
                return;
            }
            const hasData = t && ((t.items && t.items.some(it => it.name || it.unitPrice > 0)) || (t.formData && t.formData.customer_name));
            if (hasData) {
                if (!confirm('Dữ liệu chưa lưu sẽ bị mất. Bạn có chắc muốn quay lại danh sách đơn hàng?')) {
                    return;
                }
            }
            sessionStorage.removeItem('order_from_pos');
            sessionStorage.removeItem('order_return_url');
            window.location.href = returnUrl;
        },

        closeTicket(index) {
            if (this.isFromPos() && this.tickets.length === 1) {
                this.goBack();
                return;
            }
            const t = this.tickets[index];
            if (!t) return;
            const hasData = t.items.some(it => it.name || it.unitPrice > 0) || t.formData.customer_name;
            const displayName = t.formData.customer_name || t.title;

            if (hasData) {
                if (!confirm(`Bạn có chắc muốn đóng [${displayName}]? Dữ liệu chưa lưu của phiếu này sẽ bị mất.`)) {
                    return;
                }
            }

            if (this.tickets.length === 1) {
                const targetName = this.isFromPos() ? 'trang Bán Hàng POS' : 'danh sách đơn hàng';
                if (confirm(`Đây là phiếu duy nhất đang mở. Bạn muốn quay lại ${targetName} (OK) hay làm mới phiếu này (Hủy)?`)) {
                    const targetUrl = this.getReturnUrl();
                    sessionStorage.removeItem('order_from_pos');
                    sessionStorage.removeItem('order_return_url');
                    window.location.href = targetUrl;
                } else {
                    this.tickets = [];
                    this.ticketCounter = 0;
                    this.addNewTicket();
                }
                return;
            }

            this.tickets.splice(index, 1);
            if (this.activeTicketIndex >= this.tickets.length) {
                this.switchTicket(this.tickets.length - 1);
            } else if (this.activeTicketIndex === index) {
                this.switchTicket(Math.max(0, index - 1));
            } else if (this.activeTicketIndex > index) {
                this.activeTicketIndex--;
                this.syncCurrentTicket();
            }
        },

        handleGlobalKeydown(e) {
            const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

            // Ctrl + F9: Save & Print (In readonly mode, just prints)
            if (e.ctrlKey && e.key === 'F9') {
                e.preventDefault();
                if (this.isReadonly) {
                    this.printCurrentOrder();
                    return;
                }
                if (!this.saving) {
                    this.submitOrder(true);
                }
                return;
            }

            // F9: Save (Disabled in readonly mode)
            if (e.key === 'F9') {
                e.preventDefault();
                if (this.isReadonly) return;
                if (!this.saving) {
                    this.submitOrder(false);
                }
                return;
            }

            // F8 or Ctrl + Enter: Add line (Disabled in readonly mode)
            if (e.key === 'F8' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                if (this.isReadonly) return;
                this.activeProdDropdownIndex = null;
                this.addNewRow();
                return;
            }

            // F2: New Ticket (Disabled in readonly mode)
            if (e.key === 'F2') {
                e.preventDefault();
                if (this.isReadonly) return;
                this.addNewTicket();
                return;
            }

            // Esc: Close dropdown first, then input blur, then Ticket
            if (e.key === 'Escape') {
                if (this.activeProdDropdownIndex !== null) {
                    this.activeProdDropdownIndex = null;
                    return;
                }
                if (isInput) {
                    document.activeElement.blur();
                    return;
                }
                this.closeTicket(this.activeTicketIndex);
                return;
            }
        },

        async applyNewDealer() {
            if (!this.newDealer.name || !this.newDealer.name.trim()) {
                alert('Vui lòng nhập tên khách hàng');
                return;
            }
            this.addingDealer = true;
            try {
                const payload = {
                    name: this.newDealer.name.trim(),
                    phone: (this.newDealer.phone || '').trim(),
                    address: (this.newDealer.address || '').trim(),
                    tier: this.newDealer.tier || ((this.priceTiersCatalog && this.priceTiersCatalog.length > 0) ? this.priceTiersCatalog[0].name : 'Đại lý Cấp 2'),
                    taxCode: (this.newDealer.tax_code || '').trim() || null,
                };
                const res = await fetch('/dealers/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success && data.dealer) {
                    const d = Object.freeze({
                        id: Number(data.dealer.id),
                        name: data.dealer.name,
                        code: data.dealer.code,
                        phone: data.dealer.phone,
                        address: data.dealer.address,
                        tier: data.dealer.tier,
                        current_debt: 0,
                        credit_limit: Number(data.dealer.creditLimit || 100000000),
                        tax_code: data.dealer.taxCode || '',
                        orders_count: 0
                    });
                    this.dealersData = [d, ...(this.dealersData || [])];
                    this.selectDealer(d);
                } else {
                    this.formData.customer_name = this.newDealer.name;
                    this.formData.customer_phone = this.newDealer.phone || '';
                    this.formData.customer_address = this.newDealer.address || '';
                }
            } catch (err) {
                console.error('Lỗi lưu đại lý:', err);
                this.formData.customer_name = this.newDealer.name;
                this.formData.customer_phone = this.newDealer.phone || '';
                this.formData.customer_address = this.newDealer.address || '';
            } finally {
                this.addingDealer = false;
                this.showAddDealer = false;
                this.newDealer = { name: '', phone: '', tier: '', address: '', tax_code: '' };
                this.recalculateItemPrices();
            }
        },

        filteredDealers(keyword) {
            if (!keyword) return (this.dealersData || []).slice(0, 60);
            const q = keyword.toLowerCase().trim();
            return (this.dealersData || []).filter(d => 
                (d.name && d.name.toLowerCase().includes(q)) || 
                (d.code && d.code.toLowerCase().includes(q)) || 
                (d.phone && d.phone.includes(q)) ||
                (d.address && d.address.toLowerCase().includes(q))
            ).slice(0, 60);
        },

        selectDealer(dealer) {
            if (!dealer) return;
            this.formData.dealer_id = dealer.id;
            this.formData.customer_code = dealer.code || '';
            this.formData.customer_name = dealer.name;
            this.formData.customer_phone = dealer.phone || '';
            this.formData.customer_address = dealer.address || '';
            if (dealer.tier) {
                this.formData.customer_tier = dealer.tier;
            }
            // Giữ nguyên loại giá của phiếu là 'Nguyên Giá' hoặc loại giá đã chọn
            // Cập nhật tên tab phiếu theo tên khách hàng đã chọn
            const curT = this.tickets[this.activeTicketIndex];
            if (curT) {
                curT.title = dealer.name;
            }
            this.activeProdDropdownIndex = null;
            this.sanitizeSelectedPoliciesForCustomer();
            this.evaluateOrderPolicies();
        },

        clearCustomer() {
            this.formData.dealer_id = '';
            this.formData.customer_code = '';
            this.formData.customer_name = '';
            this.formData.customer_phone = '';
            this.formData.customer_address = '';
            const curT = this.tickets[this.activeTicketIndex];
            if (curT) {
                curT.title = 'Phiếu ' + (this.activeTicketIndex + 1);
            }
            this.activeProdDropdownIndex = null;
            this.sanitizeSelectedPoliciesForCustomer();
            this.evaluateOrderPolicies();
        },

        hasTaxCode() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return true;
            return Boolean(dealer.tax_code && dealer.tax_code.trim());
        },

        getCustomerTaxCode() {
            const dealer = this.getSelectedDealer();
            return dealer ? (dealer.tax_code || '').trim() : '';
        },

        getCustomerOrdersCount() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return 0;
            const total = Number(dealer.orders_count || 0);
            if (this.orderId && String(this.formData.dealer_id) === String(this.originalDealerId) && total > 0) {
                return total - 1;
            }
            return total;
        },

        isNoTaxCodeLimitReached() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return false;
            if (this.hasTaxCode()) return false;
            return this.getCustomerOrdersCount() >= 3;
        },

        getSelectedDealer() {
            if (this.formData.dealer_id) {
                const found = (this.dealersData || []).find(d => String(d.id) === String(this.formData.dealer_id));
                if (found) return found;
            }
            if (this.formData.customer_name) {
                const cName = this.formData.customer_name.trim().toLowerCase();
                return (this.dealersData || []).find(d => (d.name || '').trim().toLowerCase() === cName) || null;
            }
            return null;
        },

        getProjectedDebt() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return this.finalTotal || 0;
            const curDebt = Number(dealer.current_debt || 0);
            if (this.orderId && this.isOriginalOrderInDebt) {
                const delta = (this.finalTotal || 0) - (this.originalOrderTotal || 0);
                return Math.max(0, curDebt + delta);
            }
            return Math.max(0, curDebt + (this.finalTotal || 0));
        },

        isDebtOverLimit() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return false;
            const limit = Number(dealer.credit_limit || 0);
            if (limit <= 0) return false;
            return this.getProjectedDebt() > limit;
        },

        getDebtExcessAmount() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return 0;
            const limit = Number(dealer.credit_limit || 0);
            if (limit <= 0) return 0;
            return Math.max(0, this.getProjectedDebt() - limit);
        },

        getRowProductSearchQuery(item) {
            if (!item) return '';
            // Khi vừa mở dropdown trên dòng đã chọn sẵn SP mà chưa gõ tìm mới, hiển thị toàn bộ danh sách để dễ chọn SP khác
            if (item.product_id && !item._prodSearching && (!item.searchQuery || item.searchQuery === item.name || item.searchQuery === (`[${item.sku || ''}] ${item.name || ''}`).trim())) {
                return '';
            }
            return item.searchQuery || '';
        },

        filterProductListByQuery(list, query) {
            const source = (Array.isArray(list) ? list : []).filter(_isProductInUserScope);
            if (!query || !String(query).trim()) {
                return source.slice(0, 30);
            }

            const rawQ = String(query).toLowerCase().trim();
            const cleanQ = _normalizeSearchText(rawQ);
            const compactQ = cleanQ.replace(/\s+/g, '');
            const tokens = cleanQ ? cleanQ.split(' ').filter(Boolean) : [];
            const matches = [];

            for (let i = 0; i < source.length; i++) {
                const p = source[i];
                let idx = _productSearchIndex.get(String(p.id));
                if (!idx) {
                    const cleanSku = _normalizeSearchText(p.sku || '');
                    const cleanName = _normalizeSearchText(p.name || '');
                    const clean = `${cleanSku} ${cleanName}`.trim();
                    idx = {
                        raw: `${String(p.sku || '').toLowerCase()} ${String(p.name || '').toLowerCase()}`.trim(),
                        rawSku: String(p.sku || '').toLowerCase().trim(),
                        rawName: String(p.name || '').toLowerCase().trim(),
                        cleanSku,
                        cleanName,
                        clean,
                        compactSku: cleanSku.replace(/\s+/g, ''),
                        compactName: cleanName.replace(/\s+/g, ''),
                        compact: clean.replace(/\s+/g, ''),
                    };
                }

                let score = 0;
                if (!cleanQ && !compactQ) {
                    if (idx.raw.includes(rawQ)) score = 5;
                } else if (idx.rawSku === rawQ || idx.cleanSku === cleanQ || (compactQ && idx.compactSku === compactQ)) {
                    score = 1;
                } else if (idx.rawSku.startsWith(rawQ) || idx.cleanSku.startsWith(cleanQ) || (compactQ && idx.compactSku.startsWith(compactQ))) {
                    score = 2;
                } else if (idx.cleanSku.includes(cleanQ) || (compactQ && idx.compactSku.includes(compactQ)) || idx.cleanName === cleanQ) {
                    score = 3;
                } else if (idx.rawName.includes(rawQ) || idx.cleanName.startsWith(cleanQ)) {
                    score = 4;
                } else if (idx.cleanName.includes(cleanQ) || idx.clean.includes(cleanQ)) {
                    score = 5;
                } else if (compactQ.length >= 2 && idx.compact.includes(compactQ)) {
                    score = 6;
                } else if (tokens.length > 1 && tokens.every(t => idx.clean.includes(t) || idx.compact.includes(t))) {
                    score = 7;
                }

                if (score > 0) {
                    matches.push({ p, score });
                }
            }

            matches.sort((a, b) => a.score - b.score);
            return matches.slice(0, 30).map(m => m.p);
        },

        getFilteredProducts(query, currentIndex = -1) {
            // Lấy danh sách ID sản phẩm đã được chọn ở các dòng khác trong đơn hàng để không gợi ý trùng lặp
            const selectedProductIds = new Set();
            (this.items || []).forEach((it, idx) => {
                if (idx !== currentIndex && it.product_id && !it.isPromotionalGift) {
                    selectedProductIds.add(String(it.product_id));
                }
            });

            const list = (this.productsCatalog || []).filter(p => !selectedProductIds.has(String(p.id)));
            return this.filterProductListByQuery(list, query);
        },

        getProduct(productId) {
            if (!productId) return null;
            return _productsMap.get(String(productId)) || null;
        },

        onWarehouseChange() {
            // Khi đổi kho xuất, tính toán và hiển thị lại cảnh báo tồn kho tương ứng của từng mặt hàng
            this.calculateTotals();
        },

        getProductStockData(p, whName) {
            if (!p) return { on_hand: 0, reserved: 0, available: 0, holding_orders: [] };
            const targetWarehouse = (whName || this.formData.warehouse || '').trim();
            if (targetWarehouse && p.warehouse_stocks !== undefined && p.warehouse_stocks !== null) {
                if (typeof p.warehouse_stocks === 'object' && !Array.isArray(p.warehouse_stocks)) {
                    let found = p.warehouse_stocks[targetWarehouse];
                    if (found === undefined) {
                        const targetLower = targetWarehouse.toLowerCase();
                        const matchedKey = Object.keys(p.warehouse_stocks).find(k => {
                            const kl = k.toLowerCase();
                            return kl.includes(targetLower) || targetLower.includes(kl);
                        });
                        if (matchedKey !== undefined) {
                            found = p.warehouse_stocks[matchedKey];
                        }
                    }
                    if (found !== undefined) {
                        if (typeof found === 'object' && found !== null) {
                            return {
                                on_hand: Number(found.on_hand) || 0,
                                reserved: Number(found.reserved) || 0,
                                available: Number(found.available) || 0,
                                holding_orders: Array.isArray(found.holding_orders) ? found.holding_orders : [],
                            };
                        } else {
                            const num = Number(found) || 0;
                            return { on_hand: num, reserved: 0, available: num, holding_orders: [] };
                        }
                    }
                }
                // Sản phẩm đã có bản đồ kho nhưng kho đang chọn không có dòng tồn => tồn tại kho này = 0
                return { on_hand: 0, reserved: 0, available: 0, holding_orders: [] };
            }
            const onHand = Number(p.stock_quantity) || 0;
            const reserved = Number(p.reserved_stock) || 0;
            const available = Number(p.available_stock !== undefined ? p.available_stock : Math.max(0, onHand - reserved));
            return { on_hand: onHand, reserved: reserved, available: available, holding_orders: [] };
        },

        getProductStockInWarehouse(p, whName) {
            return this.getProductStockData(p, whName).available;
        },

        getProductOnHandStockInWarehouse(p, whName) {
            return this.getProductStockData(p, whName).on_hand;
        },

        getProductReservedStockInWarehouse(p, whName) {
            return this.getProductStockData(p, whName).reserved;
        },

        isConvUnitLarger(product) {
            if (!product) return false;
            const u1 = (product.unit || '').trim().toLowerCase();
            const u2 = (product.conversion_unit || '').trim().toLowerCase();
            if (!u2 || u1 === u2) return false;

            const bulkUnits = ['kiện', 'thùng', 'bao', 'lô', 'pallet', 'carton', 'két', 'thưng', 'lốc', 'dây'];
            if (bulkUnits.includes(u2)) return true;

            if (u2 === 'hộp' && ['chai', 'lọ', 'tuýp', 'gói', 'vỉ', 'cái', 'viên', 'miếng', 'ống'].includes(u1)) {
                return true;
            }

            if (['mét', 'm', 'cm', 'gam', 'g'].includes(u1) && ['cuộn', 'cây', 'tấm', 'hộp'].includes(u2)) {
                return true;
            }

            return false;
        },

        getItemStockDataInUnit(item, whName) {
            if (!item || !item.product_id) return { on_hand: 0, reserved: 0, available: 0, holding_orders: [] };
            const p = this.getProduct(item.product_id);
            if (!p) return { on_hand: 0, reserved: 0, available: 0, holding_orders: [] };

            const baseData = this.getProductStockData(p, whName);
            const curUnit = (item.unit || '').trim().toLowerCase();
            const baseUnit = (p.unit || '').trim().toLowerCase();
            const convUnit = (p.conversion_unit || '').trim().toLowerCase();
            const rate = Number(p.conversion_rate) > 0 ? Number(p.conversion_rate) : 1;

            if (convUnit && curUnit === convUnit && convUnit !== baseUnit) {
                const isConvLarger = this.isConvUnitLarger(p);
                return {
                    on_hand: isConvLarger ? Math.round(baseData.on_hand / rate * 100) / 100 : Math.round(baseData.on_hand * rate * 100) / 100,
                    reserved: isConvLarger ? Math.round(baseData.reserved / rate * 100) / 100 : Math.round(baseData.reserved * rate * 100) / 100,
                    available: isConvLarger ? Math.round(baseData.available / rate * 100) / 100 : Math.round(baseData.available * rate * 100) / 100,
                    holding_orders: baseData.holding_orders,
                };
            }
            return baseData;
        },

        getItemAvailableStockInUnit(item, whName) {
            return this.getItemStockDataInUnit(item, whName).available;
        },

        getItemOnHandStockInUnit(item, whName) {
            return this.getItemStockDataInUnit(item, whName).on_hand;
        },

        getItemReservedStockInUnit(item, whName) {
            return this.getItemStockDataInUnit(item, whName).reserved;
        },

        getItemStockStatus(item) {
            if (!item || !item.product_id) return 'ok';
            const data = this.getItemStockDataInUnit(item);
            const reqQty = (Number(item.quantity) || 0) + (Number(item.gift_quantity) || 0);
            if (reqQty <= 0) return 'ok';
            if (reqQty > data.on_hand) return 'danger';
            if (reqQty > data.available) return 'warning';
            return 'ok';
        },

        isItemOverStock(item) {
            return this.getItemStockStatus(item) !== 'ok';
        },

        isItemPhysicalShortage(item) {
            return this.getItemStockStatus(item) === 'danger';
        },

        isItemReservedShortage(item) {
            return this.getItemStockStatus(item) === 'warning';
        },

        getItemOverStockQty(item) {
            if (!this.isItemOverStock(item)) return 0;
            const avail = this.getItemAvailableStockInUnit(item);
            const reqQty = (Number(item.quantity) || 0) + (Number(item.gift_quantity) || 0);
            return Math.round(Math.max(0, reqQty - avail) * 100) / 100;
        },

        getOverStockItems() {
            return (this.items || []).filter(it => it.product_id && this.isItemOverStock(it));
        },

        getPhysicalShortageItems() {
            return (this.items || []).filter(it => it.product_id && this.isItemPhysicalShortage(it));
        },

        getReservedShortageItems() {
            return (this.items || []).filter(it => it.product_id && this.isItemReservedShortage(it));
        },

        getItemUnits(item) {
            if (!item || !item.product_id) {
                return item && item.unit ? [item.unit] : [];
            }
            const p = this.getProduct(item.product_id);
            if (!p) {
                return item && item.unit ? [item.unit] : [];
            }

            const list = [];
            // 1. Đơn vị tính chính của sản phẩm
            if (p.unit && p.unit.trim()) {
                list.push(p.unit.trim());
            }

            // 2. Đơn vị quy đổi của sản phẩm (nếu có và khác ĐVT chính)
            if (p.conversion_unit && p.conversion_unit.trim()) {
                const conv = p.conversion_unit.trim();
                if (!list.some(u => u.toLowerCase() === conv.toLowerCase())) {
                    list.push(conv);
                }
            }

            return list.length > 0 ? list : (item.unit ? [item.unit] : ['cái']);
        },

        hasConversionUnit(item) {
            if (!item || !item.product_id) return false;
            const p = this.getProduct(item.product_id);
            if (!p) return false;
            return !!(p.conversion_unit && p.conversion_unit.trim() && p.unit && p.conversion_unit.trim().toLowerCase() !== p.unit.trim().toLowerCase());
        },

        onItemUnitChange(index) {
            const item = this.items[index];
            if (!item || !item.product_id) return;
            const prod = this.getProduct(item.product_id);
            if (!prod) return;

            item.unitPrice = this.getProductPrice(prod, item.unit, this.getEffectiveTierName(item));
            this.updateLineTotal(index);
        },

        getEffectiveTierName(rowItem) {
            if (rowItem && rowItem.price_type && rowItem.price_type !== 'Theo phiếu' && !rowItem.price_type.startsWith('Theo phiếu')) {
                return rowItem.price_type;
            }
            if (this.formData.dealer_tier) {
                return this.formData.dealer_tier;
            }
            return 'Nguyên Giá';
        },

        isDiscountPolicy(policy) {
            if (!policy) return false;
            return policy.policy_type === 'percentage' || policy.policy_type === 'amount';
        },

        isOriginalPriceTier(tier) {
            if (!tier) return false;
            let s = String(tier).trim().toLowerCase();
            if (s.startsWith('theo phiếu')) {
                const m = s.match(/\(([^)]+)\)/);
                if (m) {
                    s = m[1].trim().toLowerCase();
                } else {
                    return this.isOriginalPriceTier(this.formData.dealer_tier);
                }
            }
            return s.includes('nguyên giá') || s.includes('nguyen gia') || s === 'ng';
        },

        isItemOriginalPriceTier(item) {
            if (!item) return false;
            const effTier = this.getEffectiveTierName(item);
            return this.isOriginalPriceTier(effTier);
        },

        getProductPrice(p, unit, tierName) {
            if (!p) return 0;
            const chosenTier = (tierName || this.getEffectiveTierName()).trim();
            const targetUnit = (unit || p.unit || '').trim().toLowerCase();
            const baseUnit = (p.unit || '').trim().toLowerCase();
            const convUnit = (p.conversion_unit || '').trim().toLowerCase();
            const isConv = convUnit && (targetUnit === convUnit) && (convUnit !== baseUnit);
            const rate = Number(p.conversion_rate) > 0 ? Number(p.conversion_rate) : 1;
            const isConvLarger = this.isConvUnitLarger(p);
            const calcConv = (price) => {
                const num = Number(price || 0);
                if (rate <= 1 || num <= 0) return num;
                return isConvLarger ? Math.round(num * rate) : Math.round(num / rate);
            };

            // 1. Ưu tiên Giá bán của sản phẩm (p.price)
            if (isConv && Number(p.conversion_price) > 0) {
                return Number(p.conversion_price);
            }
            if (Number(p.price) > 0) {
                return isConv ? calcConv(p.price) : Number(p.price);
            }

            // 2. Fallback nếu sản phẩm chưa cài giá bán (p.price <= 0)
            const baseCost = Number(p.cost_price || 0);
            if (baseCost > 0) {
                return isConv ? calcConv(baseCost) : baseCost;
            }
            return 0;
        },

        selectProductForRow(index, p) {
            if (!p) return;
            const item = this.items[index];
            if (!item) return;

            item.product_id = p.id;
            item.sku = p.sku;
            item.name = p.name;
            item.searchQuery = p.name;
            item._prodSearching = false;
            item.unit = p.unit || 'cái';

            // Giá niêm yết / giá bán gốc (lấy giá bán p.price, không lấy giá vốn)
            const sellPrice = Number(p.price) > 0 ? Number(p.price) : (Number(p.cost_price) || 0);
            item.originalPrice = sellPrice;
            item.costPrice = Number(p.cost_price || 0);

            // Không tính VAT
            item.vat_percent = 0;
            item.vat_amount = 0;
            
            const effTier = (item.price_type && item.price_type !== 'Theo phiếu') ? item.price_type : (this.formData.dealer_tier || 'Nguyên Giá');
            item.price_type = effTier;
            item.unitPrice = this.getProductPrice(p, item.unit, effTier);
            item.appliedTierPolicy = effTier;
            item.quantity = item.quantity > 0 ? item.quantity : 1;

            if (this.bulkDiscountPercent !== '' && this.bulkDiscountPercent !== null && Number(this.bulkDiscountPercent) > 0) {
                const bPct = Math.min(100, Math.max(0, Number(this.bulkDiscountPercent) || 0));
                item._manualDiscount = true;
                item.discount_percent = bPct;
                const lineBase = (Number(item.quantity) || 1) * (Number(item.unitPrice) || 0);
                item.discount_amount = Math.round((lineBase * bPct) / 100);
            }

            this.activeProdDropdownIndex = null;
            this.updateLineTotal(index);
        },

        clearProductRow(index, salesIndex = null) {
            const item = this.items[index];
            if (!item) return;

            item.product_id = '';
            item.itemId = '';
            item.sku = '';
            item.name = '';
            item.searchQuery = '';
            item._prodSearching = true;
            item.unit = '';
            item.unitPrice = 0;
            item.originalPrice = 0;
            item.costPrice = 0;
            item._autoPromoDiscount = false;
            item.selected_policy_id = '';
            if (!item._manualDiscount) {
                item.discount_percent = 0;
                item.discount_amount = 0;
            }
            item.vat_percent = 0;
            item.vat_amount = 0;
            item.amount = 0;

            const targetSalesIdx = salesIndex !== null ? salesIndex : this.getSalesItems().indexOf(item);
            if (targetSalesIdx >= 0) {
                this.activeProdDropdownIndex = targetSalesIdx;
                this.prodHighlightIndex = 0;
            }
            this.updateLineTotal(index);
            this.$nextTick(() => {
                const input = targetSalesIdx >= 0 ? document.getElementById('row-product-input-' + targetSalesIdx) : null;
                if (input) input.focus();
            });
        },

        onItemSearch(index, val) {
            if (!val) return;
            const item = this.items[index];
            if (!item) return;

            const raw = val.trim().toLowerCase();
            const clean = _normalizeSearchText(val);
            const compact = clean.replace(/\s+/g, '');

            let matched = this.productsCatalog.find(p => {
                const idx = _productSearchIndex.get(String(p.id));
                const pSkuLower = (p.sku || '').toLowerCase();
                const pNameLower = (p.name || '').toLowerCase();
                if (val.includes(`[${p.sku}]`) || pSkuLower === raw || pNameLower === raw || `[${p.sku}] ${p.name}`.toLowerCase() === raw) {
                    return true;
                }
                if (idx && clean && (idx.cleanSku === clean || idx.cleanName === clean || (compact && idx.compactSku === compact))) {
                    return true;
                }
                return false;
            });

            if (!matched && clean.length >= 3) {
                matched = this.productsCatalog.find(p => {
                    const idx = _productSearchIndex.get(String(p.id));
                    return idx && (idx.cleanSku.includes(clean) || idx.cleanName.includes(clean) || (compact.length >= 3 && idx.compact.includes(compact)));
                });
            }

            if (matched) {
                const isAlreadySelected = (this.items || []).some((it, idx) => idx !== index && !it.isPromotionalGift && String(it.product_id) === String(matched.id));
                if (!isAlreadySelected) {
                    this.selectProductForRow(index, matched);
                }
            }
        },

        getSalesPolicyById(id) {
            if (!id) return null;
            return (this.salesPoliciesCatalog || []).find(p => String(p.id) === String(id)) || null;
        },

        getNormalizedPolicyProductIds(policy) {
            if (!policy) return [];
            let prodIds = policy.target_product_ids;
            if (typeof prodIds === 'string') {
                try { prodIds = JSON.parse(prodIds); } catch(e) { prodIds = []; }
            }
            if (!Array.isArray(prodIds)) return [];
            return prodIds.map(id => String(id));
        },

        isPolicyTargetOverlapping(policyA, policyB) {
            if (!policyA || !policyB) return false;
            if (String(policyA.id) === String(policyB.id)) return true;

            const idsA = this.getNormalizedPolicyProductIds(policyA);
            const idsB = this.getNormalizedPolicyProductIds(policyB);
            if (idsA.length === 0 || idsB.length === 0) return true;

            const setB = new Set(idsB);
            return idsA.some(id => setB.has(id));
        },

        getActiveSalesProductIds() {
            const set = new Set();
            (this.items || []).forEach(it => {
                if (!it.isPromotionalGift && it.checked !== false && it.product_id) {
                    set.add(String(it.product_id));
                }
            });
            return set;
        },

        getSalesItemsWithProduct() {
            return (this.items || []).filter(it => !it.isPromotionalGift && it.product_id);
        },

        getApplicableOrderPolicies() {
            // Danh sách toàn bộ chính sách đang đủ điều kiện áp dụng cho ít nhất 1 sản phẩm hoặc toàn đơn
            const eligibleIds = new Set();
            const items = this.getSalesItemsWithProduct();
            (this.salesPoliciesCatalog || []).forEach(p => {
                if (p.apply_scope === 'order') {
                    if (this.getPolicyEvaluation(p).eligible === true) eligibleIds.add(p.id);
                } else {
                    const anyEligible = items.some(it => this.getItemPolicyEvaluation(it, p).eligible === true);
                    if (anyEligible) eligibleIds.add(p.id);
                }
            });
            return Array.from(eligibleIds).map(id => this.getSalesPolicyById(id)).filter(Boolean);
        },

        resolveScopedGiftProduct(explicitFreeProductId, policy, fallbackProductId = null) {
            if (explicitFreeProductId) {
                const mapProd = this.getProduct(explicitFreeProductId);
                const relProd = (!mapProd && policy?.free_product && String(policy.free_product.id) === String(explicitFreeProductId))
                    ? policy.free_product
                    : null;
                const prod = mapProd || relProd;
                if (!prod || !_isProductInUserScope(prod)) {
                    return { allowed: false, prod: null };
                }
                return { allowed: true, prod };
            }
            const fallbackProd = fallbackProductId ? this.getProduct(fallbackProductId) : null;
            if (!fallbackProd || !_isProductInUserScope(fallbackProd)) {
                return { allowed: false, prod: null };
            }
            return { allowed: true, prod: fallbackProd };
        },

        getSelectedPolicies() {
                    const ids = Array.isArray(this.formData.sales_policy_ids) ? this.formData.sales_policy_ids.filter(Boolean) : [];
                    return ids.map(id => this.getSalesPolicyById(id)).filter(Boolean);
                },
        
                getPolicyAppliedProductsSummary(policy) {
                    if (!policy) return '';
                    const claimed = (this.items || []).filter(it => it._claimedByPolicyId && String(it._claimedByPolicyId) === String(policy.id));
                    if (claimed.length > 0) {
                        const names = Array.from(new Set(claimed.map(it => it.name || it.sku || 'Sản phẩm')));
                        if (names.length <= 2) {
                            return names.join(', ');
                        }
                        return names.slice(0, 2).join(', ') + ' và ' + (names.length - 2) + ' SP khác';
                    }
                    const pTargets = this.getNormalizedPolicyProductIds(policy);
                    if (pTargets.length > 0) {
                        const targetProds = pTargets.map(id => this.getProduct(id)?.name).filter(Boolean);
                        if (targetProds.length > 0) {
                            return targetProds.length <= 2 ? targetProds.join(', ') : targetProds.slice(0, 2).join(', ') + '...';
                        }
                    }
                    return policy.apply_scope === 'order' ? 'Toàn đơn hàng' : 'Nhóm sản phẩm áp dụng';
                },
        
                getPolicyBenefitDescription(policy) {
                    if (!policy) return '';
                    if (policy.policy_type === 'quantity') {
                        const gifts = (this.items || []).filter(it => it.isPromotionalGift && String(it.promoPolicyId || '') === String(policy.id));
                        if (gifts.length > 0) {
                            return gifts.map(g => 'Tặng +' + (g.quantity || 1) + ' ' + (g.unit || 'SP') + ' (' + (g.name || 'Quà') + ')').join('; ');
                        }
                        const ev = this.getPolicyEvaluation(policy);
                        return ev.benefitText ? `Tặng hàng (${ev.benefitText})` : 'Tặng hàng kèm';
                    }
                    if (policy.policy_type === 'percentage') {
                        const claimed = (this.items || []).filter(it => it._claimedByPolicyId && String(it._claimedByPolicyId) === String(policy.id));
                        const totalDisc = claimed.reduce((sum, it) => sum + (Number(it.discount_amount) || 0), 0);
                        const activePcts = Array.from(new Set(claimed.map(it => Number(it.discount_percent) || 0).filter(p => p > 0)));
                        if (activePcts.length === 0 && policy.stacking_mode === 'stackable_order' && this.orderDiscountPercent > 0) {
                            activePcts.push(Number(this.orderDiscountPercent));
                        }
                        const pctLabel = activePcts.length > 0 ? activePcts.map(v => `${v}%`).join(' / ') : `${Number(policy.discount_percent) || 0}%`;
                        return totalDisc > 0
                            ? `Chiết khấu ${pctLabel} (-${this.formatNumber(totalDisc)} đ)`
                            : `Chiết khấu ${pctLabel}`;
                    }
                    if (policy.policy_type === 'amount') {
                        const claimed = (this.items || []).filter(it => it._claimedByPolicyId && String(it._claimedByPolicyId) === String(policy.id));
                        const totalDisc = claimed.reduce((sum, it) => sum + (Number(it.discount_amount) || 0), 0);
                        return totalDisc > 0
                            ? `Giảm trực tiếp -${this.formatNumber(totalDisc)} đ`
                            : `Giảm -${this.formatNumber(policy.discount_amount || 0)} đ`;
                    }
                    return 'Khuyến mại áp dụng';
                },

                getItemQuantityInBaseUnit(item) {
                    if (!item) return 0;
                    const qty = Number(item.quantity) || 0;
                    if (qty <= 0) return 0;
                    const p = this.getProduct(item.product_id);
                    if (!p || !p.conversion_unit || !(Number(p.conversion_rate) > 0)) {
                        return qty;
                    }
                    const curUnit = (item.unit || '').trim().toLowerCase();
                    const baseUnit = (p.unit || '').trim().toLowerCase();
                    const convUnit = (p.conversion_unit || '').trim().toLowerCase();
                    const rate = Number(p.conversion_rate);
                    if (convUnit && curUnit === convUnit && convUnit !== baseUnit) {
                        return this.isConvUnitLarger(p) ? (qty * rate) : (qty / rate);
                    }
                    return qty;
                },

                getItemVariantGroupKey(item) {
                    if (!item || !item.product_id) return '';
                    const prod = this.getProduct(item.product_id);
                    const rawName = String(prod?.name || item.name || '').trim();
                    const sizeMatch = rawName.match(/(\d+(?:[.,]\d+)?)\s*(g|gam|gr|ml|l|kg|cm)\b/i);
                    if (sizeMatch) {
                        const num = sizeMatch[1].replace(',', '.');
                        const unit = sizeMatch[2].toLowerCase().replace(/^gam$|^gr$/, 'g');
                        return `size:${num}${unit}`;
                    }
                    return `sku:${String(item.product_id)}`;
                },

                getEffectivePolicyGroupMode(policy) {
                    if (!policy) return 'basket_mix';
                    if (policy.qty_group_mode) return policy.qty_group_mode;
                    const targets = this.getNormalizedPolicyProductIds(policy);
                    return targets.length === 0 ? 'per_sku' : 'basket_mix';
                },

                bucketItemsByPolicyGroupMode(policy, candidateItems) {
                    const mode = this.getEffectivePolicyGroupMode(policy);
                    const buckets = new Map();
                    (candidateItems || []).forEach(it => {
                        let key = 'all';
                        if (mode === 'per_sku') {
                            key = `sku:${String(it.product_id)}`;
                        } else if (mode === 'per_variant_group') {
                            key = this.getItemVariantGroupKey(it);
                        } else if (mode === 'per_brand') {
                            const prod = this.getProduct(it.product_id);
                            key = `brand:${String(prod?.brand_id || 'none')}`;
                        }
                        if (!buckets.has(key)) buckets.set(key, []);
                        buckets.get(key).push(it);
                    });
                    return buckets;
                },

                policyRequiresOriginalPrice(policy) {
                    if (!policy) return true;
                    return !(policy.require_original_price === false || policy.require_original_price === 0 || policy.require_original_price === '0');
                },

                getNormalizedDiscountTiers(policy) {
                    let list = policy?.discount_tiers;
                    if (typeof list === 'string') {
                        try { list = JSON.parse(list); } catch (e) { list = []; }
                    }
                    if (!Array.isArray(list)) return [];
                    return list
                        .map(t => ({
                            min_qty: Number(t.min_qty) || 0,
                            min_amount: Number(t.min_amount) || 0,
                            discount_percent: (t.discount_percent !== null && t.discount_percent !== '' && t.discount_percent !== undefined) ? Number(t.discount_percent) : 0,
                            discount_amount: (t.discount_amount !== null && t.discount_amount !== '' && t.discount_amount !== undefined) ? Number(t.discount_amount) : 0,
                        }))
                        .filter(t => (t.discount_percent > 0 || t.discount_amount > 0) && (t.min_qty > 0 || t.min_amount > 0));
                },

                calculateOptimalGiftGrants(qtyInBase, sortedDescTiers, fallbackBuyQty, fallbackFreeQty, defaultFreeProductId) {
                    const grants = [];
                    if (sortedDescTiers.length > 0) {
                        let rem = qtyInBase;
                        for (const t of sortedDescTiers) {
                            if (rem >= t.buy_qty) {
                                const count = Math.floor(rem / t.buy_qty);
                                rem = rem % t.buy_qty;
                                grants.push({
                                    tier: t,
                                    freeQty: count * t.free_qty,
                                    explicitGiftProdId: t.free_product_id || defaultFreeProductId || null,
                                });
                            }
                        }
                    } else if (fallbackBuyQty > 0 && fallbackFreeQty > 0 && qtyInBase >= fallbackBuyQty) {
                        const count = Math.floor(qtyInBase / fallbackBuyQty);
                        grants.push({
                            tier: { buy_qty: fallbackBuyQty, free_qty: fallbackFreeQty, free_product_id: defaultFreeProductId || null },
                            freeQty: count * fallbackFreeQty,
                            explicitGiftProdId: defaultFreeProductId || null,
                        });
                    }
                    return grants;
                },

                getActiveConflictGroups(excludePolicyId = null) {
                    const groups = new Set();
                    (this.formData.sales_policy_ids || []).forEach(id => {
                        if (excludePolicyId && String(id) === String(excludePolicyId)) return;
                        const p = this.getSalesPolicyById(id);
                        if (p && p.conflict_group) {
                            groups.add(String(p.conflict_group).trim());
                        }
                    });
                    return groups;
                },

                hasPolicyConflictWithSelected(policy, excludePolicyId = null) {
                    if (!policy || !policy.conflict_group) return false;
                    return this.getActiveConflictGroups(excludePolicyId).has(String(policy.conflict_group).trim());
                },

                getAvailablePoliciesToAdd() {
                    const selectedIds = new Set((this.formData.sales_policy_ids || []).map(id => String(id)));
                    if (this.formData.order_policy_id) selectedIds.add(String(this.formData.order_policy_id));
                    const activeConflicts = this.getActiveConflictGroups();

                    const activeItems = (this.items || []).filter(it =>
                        !it.isPromotionalGift &&
                        it.checked !== false &&
                        it.product_id
                    );
                    if (activeItems.length === 0) return [];

                    const unclaimedItems = activeItems.filter(it => !it._claimedByPolicyId);
                    const available = [];
                    (this.salesPoliciesCatalog || []).forEach(p => {
                        if (!p || selectedIds.has(String(p.id))) return;
                        if (!this.isCustomerEligibleForPolicy(p)) return;
                        if (p.conflict_group && activeConflicts.has(String(p.conflict_group).trim())) return;

                        const candidatePool = p.stacking_mode === 'stackable_order' ? activeItems : unclaimedItems;
                        if (candidatePool.length === 0) return;

                        const ev = this.evaluatePolicyBenefitsOnItems(p, candidatePool);
                        if (ev.eligible) {
                            available.push(p);
                        }
                    });

                    return available;
                },

                getPolicyNextTierHint(policy) {
                    if (!policy) return '';
                    return this.getPolicyEvaluation(policy).nextTierHint || '';
                },

                getUpcomingPolicyHints() {
                    const salesItems = this.getSalesItemsWithProduct();
                    if (salesItems.length === 0) return [];

                    const selectedIds = new Set((this.formData.sales_policy_ids || []).map(id => String(id)));
                    const activeConflicts = this.getActiveConflictGroups();
                    const hints = [];

                    for (const p of (this.salesPoliciesCatalog || [])) {
                        if (!p || selectedIds.has(String(p.id))) continue;
                        if (!this.isCustomerEligibleForPolicy(p)) continue;
                        if (p.conflict_group && activeConflicts.has(String(p.conflict_group).trim())) continue;

                        const matching = salesItems.filter(it => !it._claimedByPolicyId && this.isProductInPolicy(p, it.product_id));
                        if (matching.length === 0) continue;

                        const ev = this.evaluatePolicyBenefitsOnItems(p, salesItems);
                        if (!ev.eligible && ev.upsellHint) {
                            hints.push({ policyId: p.id, text: `${p.code || p.name}: ${ev.upsellHint}` });
                            if (hints.length >= 3) break;
                        }
                    }
                    return hints;
                },

                autoApplyBestPolicies() {
                    const activeItems = (this.items || []).filter(it => !it.isPromotionalGift && it.checked !== false && it.product_id);
                    if (activeItems.length === 0) return;

                    const linePolicies = (this.salesPoliciesCatalog || []).filter(p => p.stacking_mode !== 'stackable_order');
                    const stackPolicies = (this.salesPoliciesCatalog || []).filter(p => p.stacking_mode === 'stackable_order');

                    const chosenIds = [];
                    const usedConflicts = new Set();
                    let remaining = [...activeItems];

                    // Lặp chọn chính sáchExclusive mang lại giá trị lợi ích cao nhất (ưu tiên priority thấp hơn nếu bằng nhau)
                    const maxIter = linePolicies.length;
                    for (let step = 0; step < maxIter && remaining.length > 0; step++) {
                        let best = null;
                        for (const p of linePolicies) {
                            if (chosenIds.includes(String(p.id))) continue;
                            if (!this.isCustomerEligibleForPolicy(p)) continue;
                            if (p.conflict_group && usedConflicts.has(String(p.conflict_group).trim())) continue;

                            const ev = this.evaluatePolicyBenefitsOnItems(p, remaining);
                            if (!ev.eligible || !ev.claimedItems || ev.claimedItems.length === 0) continue;

                            const value = Number(ev.estimatedBenefitValue) || 0;
                            const prio = Number(p.priority ?? 100);
                            if (!best || value > best.value || (value === best.value && prio < best.prio)) {
                                best = { p, ev, value, prio };
                            }
                        }
                        if (!best) break;

                        chosenIds.push(String(best.p.id));
                        if (best.p.conflict_group) {
                            usedConflicts.add(String(best.p.conflict_group).trim());
                        }
                        const claimedSet = new Set(best.ev.claimedItems);
                        remaining = remaining.filter(it => !claimedSet.has(it));
                    }

                    // Áp thêm tối đa 1 chính sách cộng dồn mỗi conflict_group (nếu có)
                    for (const sp of stackPolicies) {
                        if (!this.isCustomerEligibleForPolicy(sp)) continue;
                        if (sp.conflict_group && usedConflicts.has(String(sp.conflict_group).trim())) continue;
                        const ev = this.evaluatePolicyBenefitsOnItems(sp, activeItems);
                        if (ev.eligible) {
                            chosenIds.push(String(sp.id));
                            if (sp.conflict_group) usedConflicts.add(String(sp.conflict_group).trim());
                        }
                    }

                    this.formData.sales_policy_ids = chosenIds;
                    this.formData.sales_policy_id = chosenIds[0] || '';
                    this.evaluateOrderPolicies();
                },
        
                addPolicyById(policyId) {
                    if (!policyId) return;
                    const idStr = String(policyId);
                    if (!Array.isArray(this.formData.sales_policy_ids)) {
                        this.formData.sales_policy_ids = [];
                    }
                    if (!this.formData.sales_policy_ids.map(String).includes(idStr)) {
                        this.formData.sales_policy_ids.push(idStr);
                    }
                    this.evaluateOrderPolicies();
                },
        
                removeSelectedPolicy(policyId) {
                    if (!policyId) return;
                    const idStr = String(policyId);
                    if (Array.isArray(this.formData.sales_policy_ids)) {
                        this.formData.sales_policy_ids = this.formData.sales_policy_ids.filter(id => String(id) !== idStr);
                    }
                    if (String(this.formData.order_policy_id || '') === idStr) {
                        this.formData.order_policy_id = '';
                    }
                    this.cleanupEffectsOfRemovedPolicy(idStr);
                    this.evaluateOrderPolicies();
                },
        
                clearAllOrderPolicies() {
                    const oldIds = Array.isArray(this.formData.sales_policy_ids) ? [...this.formData.sales_policy_ids] : [];
                    if (this.formData.order_policy_id) oldIds.push(this.formData.order_policy_id);
        
                    this.formData.order_policy_id = '';
                    this.formData.sales_policy_ids = [];
                    this.formData.sales_policy_id = '';
        
                    (this.items || []).forEach(it => {
                        if (!it.isPromotionalGift) {
                            it._claimedByPolicyId = null;
                            it.selected_policy_id = '';
                            if (it._autoPromoDiscount && !it._manualDiscount) {
                                it.discount_percent = 0;
                                it.discount_amount = 0;
                                it._autoPromoDiscount = false;
                                it._appliedPromoPolicyId = null;
                                it.amount = Math.max(0, Math.round((Number(it.quantity) || 0) * (Number(it.unitPrice) || 0)));
                            }
                        }
                    });
        
                    for (let i = this.items.length - 1; i >= 0; i--) {
                        const g = this.items[i];
                        if (g.isPromotionalGift && (g._giftKey || g.promoPolicyId)) {
                            this.items.splice(i, 1);
                        }
                    }
        
                    if (this._autoOrderDiscount && !this._manualOrderDiscount) {
                        this.orderDiscountPercent = 0;
                        this.formData.discount_amount = 0;
                        this._autoOrderDiscount = false;
                    }
        
                    this.evaluateOrderPolicies();
                },
        
                evaluateGiftBuckets(policy, matchingItems) {
                    const emptyRes = (reason, statusText, upsellHint = '') => ({
                        eligible: false, reason, benefitText: '', statusText, upsellHint, nextTierHint: '',
                        estimatedBenefitValue: 0, claimedItems: [], itemDiscounts: [], qualifyingGifts: [],
                        totalFree: 0, freeProd: null, giftLabel: '', discountPct: 0, discountAmt: 0
                    });

                    const buyQty = Number(policy.buy_qty) || 0;
                    const freeQty = Number(policy.free_qty) || 0;
                    const defaultFreeProductId = policy.free_product_id || null;

                    let tiersList = policy.gift_tiers;
                    if (typeof tiersList === 'string') {
                        try { tiersList = JSON.parse(tiersList); } catch (e) { tiersList = []; }
                    }

                    let sortedDescTiers = [];
                    let minBuyRequired = buyQty;
                    if (Array.isArray(tiersList) && tiersList.length > 0) {
                        sortedDescTiers = [...tiersList]
                            .map(t => ({
                                buy_qty: Number(t.buy_qty) || 0,
                                free_qty: Number(t.free_qty) || 0,
                                free_product_id: t.free_product_id || null
                            }))
                            .filter(t => t.buy_qty > 0 && t.free_qty > 0)
                            .sort((a, b) => b.buy_qty - a.buy_qty);
                        if (sortedDescTiers.length > 0) {
                            minBuyRequired = sortedDescTiers[sortedDescTiers.length - 1].buy_qty;
                        }
                    }

                    const groupMode = this.getEffectivePolicyGroupMode(policy);
                    const modeSuffix = groupMode === 'per_sku' ? ' cùng loại' : (groupMode === 'per_variant_group' ? ' cùng size' : (groupMode === 'per_brand' ? ' cùng nhãn hàng' : ''));
                    const buckets = this.bucketItemsByPolicyGroupMode(policy, matchingItems);

                    const giftsByProdId = new Map();
                    const claimedItems = [];
                    let minMissingQty = Infinity;
                    let maxBucketQty = 0;
                    let nextTierHint = '';
                    let scopeRejected = false;

                    buckets.forEach((bItems) => {
                        const bucketQty = bItems.reduce((sum, it) => sum + this.getItemQuantityInBaseUnit(it), 0);
                        if (bucketQty > maxBucketQty) maxBucketQty = bucketQty;

                        const grants = this.calculateOptimalGiftGrants(bucketQty, sortedDescTiers, buyQty, freeQty, defaultFreeProductId);
                        if (grants.length === 0) {
                            if (minBuyRequired > bucketQty) {
                                minMissingQty = Math.min(minMissingQty, Math.max(1, Math.ceil(minBuyRequired - bucketQty)));
                            }
                            return;
                        }

                        const bestBucketItem = [...bItems].sort((a, b) => this.getItemQuantityInBaseUnit(b) - this.getItemQuantityInBaseUnit(a))[0];
                        let bucketGranted = false;

                        grants.forEach(gr => {
                            if (gr.freeQty <= 0) return;
                            const giftRes = this.resolveScopedGiftProduct(gr.explicitGiftProdId, policy, bestBucketItem?.product_id);
                            if (!giftRes.allowed || !giftRes.prod) {
                                scopeRejected = true;
                                return;
                            }
                            bucketGranted = true;
                            const pObj = giftRes.prod;
                            const gPid = String(pObj.id);
                            const gLabel = sortedDescTiers.length > 0
                                ? `[TẶNG KÈM MUA ${gr.tier.buy_qty} TẶNG ${gr.tier.free_qty}] ${policy.name}`
                                : `[TẶNG KÈM] ${policy.name}`;
                            if (giftsByProdId.has(gPid)) {
                                giftsByProdId.get(gPid).qty += gr.freeQty;
                            } else {
                                giftsByProdId.set(gPid, { product_id: pObj.id, qty: gr.freeQty, prod: pObj, giftLabel: gLabel });
                            }
                        });

                        if (bucketGranted) {
                            bItems.forEach(it => claimedItems.push(it));
                            if (!nextTierHint && sortedDescTiers.length > 1) {
                                const sortedAsc = [...sortedDescTiers].reverse();
                                const higher = sortedAsc.find(t => t.buy_qty > bucketQty);
                                if (higher) {
                                    const needMore = Math.max(1, Math.ceil(higher.buy_qty - bucketQty));
                                    nextTierHint = `Mua thêm ${needMore} SP${modeSuffix} đạt mốc Mua ${higher.buy_qty} tặng ${higher.free_qty}`;
                                }
                            }
                        }
                    });

                    const qualifyingGifts = Array.from(giftsByProdId.values());
                    const totalFree = qualifyingGifts.reduce((sum, g) => sum + g.qty, 0);

                    if (totalFree <= 0 || qualifyingGifts.length === 0) {
                        if (scopeRejected && minMissingQty === Infinity) {
                            return emptyRes('Ngoài phạm vi NCC', 'Sản phẩm tặng nằm ngoài Hãng / NCC của nhân viên');
                        }
                        const needMore = minMissingQty < Infinity ? minMissingQty : Math.max(1, Math.ceil((minBuyRequired || 1) - maxBucketQty));
                        const unitLabel = groupMode === 'per_sku' ? 'SP cùng mã' : (groupMode === 'per_variant_group' ? 'SP cùng size' : (groupMode === 'per_brand' ? 'SP cùng hãng' : 'SP'));
                        return emptyRes(
                            `Thiếu ${needMore} ${unitLabel}`,
                            `Chưa đạt (cần mua tối thiểu ${minBuyRequired} ${unitLabel}, hiện tối đa ${this.formatNumber(maxBucketQty)})`,
                            `Thiếu ${needMore} ${unitLabel} để tặng +${sortedDescTiers[sortedDescTiers.length - 1]?.free_qty || freeQty || 1}`
                        );
                    }

                    const estimatedBenefitValue = qualifyingGifts.reduce((sum, g) => {
                        const unitVal = Number(g.prod?.price || g.prod?.cost_price || 0);
                        return sum + (g.qty * unitVal);
                    }, 0);
                    const firstGift = qualifyingGifts[0];
                    const summaryGiftName = qualifyingGifts.length === 1
                        ? (firstGift.prod?.name || 'quà tặng')
                        : `${qualifyingGifts.length} loại quà tặng kèm`;

                    return {
                        eligible: true,
                        reason: '',
                        benefitText: `Tặng +${totalFree}`,
                        statusText: `Đạt: Tặng +${totalFree} SP (${summaryGiftName})`,
                        upsellHint: '',
                        nextTierHint,
                        estimatedBenefitValue,
                        claimedItems,
                        itemDiscounts: [],
                        qualifyingGifts,
                        totalFree,
                        freeProd: firstGift.prod,
                        giftLabel: firstGift.giftLabel,
                        discountPct: 0,
                        discountAmt: 0
                    };
                },

                evaluateDiscountBuckets(policy, matchingItems) {
                    const emptyRes = (reason, statusText, upsellHint = '') => ({
                        eligible: false, reason, benefitText: '', statusText, upsellHint, nextTierHint: '',
                        estimatedBenefitValue: 0, claimedItems: [], itemDiscounts: [], qualifyingGifts: [],
                        totalFree: 0, freeProd: null, giftLabel: '', discountPct: 0, discountAmt: 0
                    });

                    const requireOrig = this.policyRequiresOriginalPrice(policy);
                    const evalItems = requireOrig ? matchingItems.filter(it => this.isItemOriginalPriceTier(it)) : matchingItems;
                    if (evalItems.length === 0) {
                        return emptyRes('Cần SP Nguyên Giá', 'Chỉ áp dụng cho dòng sản phẩm giá Nguyên Giá');
                    }

                    let tiers = this.getNormalizedDiscountTiers(policy);
                    if (tiers.length === 0) {
                        const pct = Number(policy.discount_percent) || 0;
                        const amt = Number(policy.discount_amount) || 0;
                        const minQ = Number(policy.buy_qty) || 0;
                        const minA = Number(policy.min_order_value) || 0;
                        if ((policy.policy_type === 'percentage' && pct > 0) || (policy.policy_type === 'amount' && amt > 0)) {
                            tiers = [{ min_qty: minQ, min_amount: minA, discount_percent: pct, discount_amount: amt }];
                        }
                    }
                    if (tiers.length === 0) {
                        return emptyRes('Chưa cấu hình mức giảm', 'Chưa thiết lập % / tiền giảm cho chính sách');
                    }

                    const groupMode = this.getEffectivePolicyGroupMode(policy);
                    const modeSuffix = groupMode === 'per_sku' ? ' cùng loại' : (groupMode === 'per_variant_group' ? ' cùng size' : (groupMode === 'per_brand' ? ' cùng nhãn hàng' : ''));
                    const buckets = this.bucketItemsByPolicyGroupMode(policy, evalItems);

                    const claimedItems = [];
                    const itemDiscounts = [];
                    const pctSet = new Set();
                    let totalDiscAmt = 0;
                    let bestMissingQty = Infinity;
                    let bestMissingAmt = Infinity;
                    let nextTierHint = '';

                    buckets.forEach((bItems) => {
                        const bQty = bItems.reduce((sum, it) => sum + this.getItemQuantityInBaseUnit(it), 0);
                        const bSubtotal = bItems.reduce((sum, it) => {
                            const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                            const manualDisc = (it._manualDiscount && !it._autoPromoDiscount) ? (Number(it.discount_amount) || 0) : 0;
                            return sum + Math.max(0, lineBase - manualDisc);
                        }, 0);

                        const matchingTiers = tiers.filter(t =>
                            (t.min_qty <= 0 || bQty >= t.min_qty) &&
                            (t.min_amount <= 0 || bSubtotal >= t.min_amount)
                        );

                        if (matchingTiers.length === 0) {
                            // Tìm bậc thấp nhất để báo thiếu bao nhiêu
                            const lowestTier = [...tiers].sort((a, b) => (a.min_qty - b.min_qty) || (a.min_amount - b.min_amount))[0];
                            if (lowestTier) {
                                if (lowestTier.min_qty > bQty) bestMissingQty = Math.min(bestMissingQty, Math.max(1, Math.ceil(lowestTier.min_qty - bQty)));
                                if (lowestTier.min_amount > bSubtotal) bestMissingAmt = Math.min(bestMissingAmt, Math.max(0, lowestTier.min_amount - bSubtotal));
                            }
                            return;
                        }

                        // Chọn bậc mang lại mức ưu đãi cao nhất trong bucket này
                        const chosenTier = [...matchingTiers].sort((a, b) => {
                            const valA = policy.policy_type === 'percentage' ? a.discount_percent : a.discount_amount;
                            const valB = policy.policy_type === 'percentage' ? b.discount_percent : b.discount_amount;
                            return valB - valA;
                        })[0];

                        if (policy.policy_type === 'percentage' && chosenTier.discount_percent > 0 && bSubtotal > 0) {
                            const pct = chosenTier.discount_percent;
                            pctSet.add(pct);
                            bItems.forEach(it => {
                                const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                                const discAmt = Math.round((lineBase * pct) / 100);
                                totalDiscAmt += discAmt;
                                claimedItems.push(it);
                                itemDiscounts.push({ item: it, discountPct: pct, discountAmt: discAmt });
                            });
                        } else if (policy.policy_type === 'amount' && chosenTier.discount_amount > 0 && bSubtotal > 0) {
                            const bucketAmt = Math.min(bSubtotal, chosenTier.discount_amount);
                            let remaining = bucketAmt;
                            bItems.forEach((it, idx) => {
                                const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                                const discAmt = (idx === bItems.length - 1) ? Math.min(lineBase, remaining) : Math.min(lineBase, Math.round((lineBase / bSubtotal) * bucketAmt));
                                remaining -= discAmt;
                                const effPct = lineBase > 0 ? Math.round((discAmt / lineBase) * 100 * 10) / 10 : 0;
                                totalDiscAmt += discAmt;
                                claimedItems.push(it);
                                itemDiscounts.push({ item: it, discountPct: effPct, discountAmt: discAmt });
                            });
                        }

                        // Gợi ý nâng bậc nếu bucket hiện tại còn bậc cao hơn
                        if (!nextTierHint && tiers.length > 1) {
                            const curVal = policy.policy_type === 'percentage' ? chosenTier.discount_percent : chosenTier.discount_amount;
                            const higherTier = [...tiers]
                                .filter(t => (policy.policy_type === 'percentage' ? t.discount_percent : t.discount_amount) > curVal)
                                .sort((a, b) => (a.min_qty - b.min_qty) || (a.min_amount - b.min_amount))[0];
                            if (higherTier) {
                                const rewardLabel = policy.policy_type === 'percentage' ? `CK ${higherTier.discount_percent}%` : `giảm ${this.formatNumber(higherTier.discount_amount)}đ`;
                                if (higherTier.min_qty > bQty) {
                                    nextTierHint = `Mua thêm ${Math.ceil(higherTier.min_qty - bQty)} SP${modeSuffix} để nâng lên ${rewardLabel}`;
                                } else if (higherTier.min_amount > bSubtotal) {
                                    nextTierHint = `Mua thêm ${this.formatNumber(higherTier.min_amount - bSubtotal)}đ${modeSuffix} để nâng lên ${rewardLabel}`;
                                }
                            }
                        }
                    });

                    if (claimedItems.length === 0 || totalDiscAmt <= 0) {
                        const targetTier = [...tiers].sort((a, b) => (a.min_qty - b.min_qty) || (a.min_amount - b.min_amount))[0];
                        const rewardStr = policy.policy_type === 'percentage' ? `CK ${targetTier.discount_percent}%` : `-${this.formatNumber(targetTier.discount_amount)}đ`;
                        if (bestMissingQty < Infinity) {
                            return emptyRes(`Thiếu ${bestMissingQty} SP${modeSuffix}`, `Chưa đạt (cần thêm ${bestMissingQty} SP${modeSuffix})`, `Thêm ${bestMissingQty} SP${modeSuffix} để đạt ${rewardStr}`);
                        }
                        if (bestMissingAmt < Infinity) {
                            return emptyRes(`Thiếu ${this.formatNumber(bestMissingAmt)}đ`, `Chưa đạt (cần mua thêm ${this.formatNumber(bestMissingAmt)} đ nhóm SP này)`, `Thêm ${this.formatNumber(bestMissingAmt)}đ${modeSuffix} để đạt ${rewardStr}`);
                        }
                        return emptyRes('Chưa đạt ngưỡng', 'Chưa đủ điều kiện chiết khấu');
                    }

                    const pctArr = Array.from(pctSet);
                    const primaryPct = pctArr[0] || 0;
                    const pctLabel = pctArr.length > 0 ? pctArr.map(v => `${v}%`).join('/') : `${primaryPct}%`;
                    const benefitText = policy.policy_type === 'percentage' ? `CK -${pctLabel}` : `-${this.formatNumber(totalDiscAmt)}đ`;
                    const statusText = policy.policy_type === 'percentage'
                        ? `Đạt: Chiết khấu ${pctLabel} (-${this.formatNumber(totalDiscAmt)} đ)`
                        : `Đạt: Giảm trực tiếp ${this.formatNumber(totalDiscAmt)} đ`;

                    return {
                        eligible: true,
                        reason: '',
                        benefitText,
                        statusText,
                        upsellHint: '',
                        nextTierHint,
                        estimatedBenefitValue: totalDiscAmt,
                        claimedItems,
                        itemDiscounts,
                        qualifyingGifts: [],
                        totalFree: 0,
                        freeProd: null,
                        giftLabel: '',
                        discountPct: primaryPct,
                        discountAmt: totalDiscAmt
                    };
                },

                evaluatePolicyBenefitsOnItems(policy, availableItems) {
                    if (!policy || !this.isCustomerEligibleForPolicy(policy)) {
                        return { eligible: false, reason: 'Chỉ áp dụng kênh / đại lý chỉ định', benefitText: '', statusText: 'Không thuộc đối tượng đại lý', upsellHint: '', nextTierHint: '', estimatedBenefitValue: 0, claimedItems: [], itemDiscounts: [], qualifyingGifts: [], totalFree: 0, freeProd: null, giftLabel: '', discountPct: 0, discountAmt: 0 };
                    }

                    const matchingItems = (availableItems || []).filter(it =>
                        !it.isPromotionalGift &&
                        it.checked !== false &&
                        it.product_id &&
                        this.isProductInPolicy(policy, it.product_id)
                    );

                    if (matchingItems.length === 0) {
                        return { eligible: false, reason: 'Chưa có SP phù hợp', benefitText: '', statusText: 'Chưa có sản phẩm phù hợp', upsellHint: '', nextTierHint: '', estimatedBenefitValue: 0, claimedItems: [], itemDiscounts: [], qualifyingGifts: [], totalFree: 0, freeProd: null, giftLabel: '', discountPct: 0, discountAmt: 0 };
                    }

                    if (policy.policy_type === 'quantity') {
                        return this.evaluateGiftBuckets(policy, matchingItems);
                    }
                    if (policy.policy_type === 'percentage' || policy.policy_type === 'amount') {
                        return this.evaluateDiscountBuckets(policy, matchingItems);
                    }

                    return { eligible: false, reason: 'Chưa hỗ trợ', benefitText: '', statusText: 'Không hợp lệ', upsellHint: '', nextTierHint: '', estimatedBenefitValue: 0, claimedItems: [], itemDiscounts: [], qualifyingGifts: [], totalFree: 0, freeProd: null, giftLabel: '', discountPct: 0, discountAmt: 0 };
                },

                formatPolicyOptionLabel(policy) {
                    if (!policy) return '';
                    const baseItems = (this.items || []).filter(it => !it.isPromotionalGift && it.checked !== false && it.product_id);
                    const pool = policy.stacking_mode === 'stackable_order' ? baseItems : baseItems.filter(it => !it._claimedByPolicyId);
                    const ev = this.evaluatePolicyBenefitsOnItems(policy, pool);
                    const prefix = ev.eligible ? `[Đạt${ev.benefitText ? ': ' + ev.benefitText : ''}]` : `[${ev.reason}]`;
                    return `${prefix} ${policy.name}`;
                },
        
                getPolicyEvaluation(policy) {
                    if (!policy) {
                        return { eligible: false, reason: 'Chưa xác định', benefitText: '', statusText: '', totalMatchedQty: 0, matchedSubtotal: 0, matchingItems: [], totalFree: 0, freeProd: null, giftLabel: '', discountAmt: 0, discountPct: 0 };
                    }
                    const activeItems = (this.items || []).filter(it => !it.isPromotionalGift && it.checked !== false && it.product_id);
                    const ev = this.evaluatePolicyBenefitsOnItems(policy, activeItems);
                    return {
                        ...ev,
                        totalMatchedQty: (ev.claimedItems || []).reduce((s, it) => s + (Number(it.quantity) || 0), 0),
                        matchingItems: ev.claimedItems || []
                    };
                },
        
                getItemPolicyEvaluation(item, policy) {
                    if (!item || !policy) {
                        return { eligible: false, reason: 'Chưa xác định', benefitText: '', statusText: '', totalFree: 0, freeProd: null, giftLabel: '', discountAmt: 0, discountPct: 0 };
                    }
                    const ev = this.evaluatePolicyBenefitsOnItems(policy, [item]);
                    return {
                        ...ev,
                        totalMatchedQty: (Number(item.quantity) || 0),
                        matchingItems: [item]
                    };
                },
        
                getApplicableOrderPolicies() {
                    const unclaimedItems = (this.items || []).filter(it => !it.isPromotionalGift && it.checked !== false && it.product_id && !it._claimedByPolicyId);
                    return (this.salesPoliciesCatalog || []).filter(p => {
                        if (!this.isCustomerEligibleForPolicy(p)) return false;
                        return this.evaluatePolicyBenefitsOnItems(p, unclaimedItems).eligible === true;
                    });
                },
        
                getOrderScopeApplicablePolicies() {
                    return (this.salesPoliciesCatalog || []).filter(p => {
                        if (p.apply_scope !== 'order') return false;
                        if (this.formData.order_policy_id && String(p.id) === String(this.formData.order_policy_id)) return true;
                        return this.getPolicyEvaluation(p).eligible === true;
                    });
                },
        
                getApplicablePoliciesForProduct(item) {
                    if (!item || !item.product_id) return [];
                    return (this.salesPoliciesCatalog || []).filter(p => {
                        return this.getItemPolicyEvaluation(item, p).eligible === true;
                    });
                },
        
                formatItemPolicyOptionLabel(policy, item) {
                    if (!policy) return '';
                    const ev = this.getItemPolicyEvaluation(item, policy);
                    const prefix = ev.eligible ? `[Đạt${ev.benefitText ? ': ' + ev.benefitText : ''}]` : `[${ev.reason}]`;
                    return `${prefix} ${policy.name}`;
                },
        
                getApplicableOrderPoliciesForSlot(slotIndex) {
                    return this.getAvailablePoliciesToAdd();
                },
        
                getRemainingAddablePolicies() {
                    return this.getAvailablePoliciesToAdd();
                },
        
                getSelectedPoliciesSummary() {
                    const ids = (this.formData.sales_policy_ids || []).filter(Boolean);
                    return ids.map(id => {
                        const p = this.getSalesPolicyById(id);
                        if (!p) return null;
                        const ev = this.getPolicyEvaluation(p);
                        return {
                            id: p.id,
                            name: p.name,
                            eligible: ev.eligible,
                            statusText: ev.statusText,
                        };
                    }).filter(Boolean);
                },
        
                sanitizeSelectedPoliciesForCustomer() {
                    if (!Array.isArray(this.formData.sales_policy_ids)) {
                        this.formData.sales_policy_ids = [];
                    }
                    const oldIds = [...this.formData.sales_policy_ids];
                    const filtered = oldIds.filter(id => {
                        if (!id) return false;
                        const p = this.getSalesPolicyById(id);
                        return p && this.isCustomerEligibleForPolicy(p);
                    });
                    oldIds.forEach(oldId => {
                        if (oldId && !filtered.includes(oldId)) {
                            this.cleanupEffectsOfRemovedPolicy(oldId);
                        }
                    });
                    this.formData.sales_policy_ids = filtered;
                    this.formData.sales_policy_id = filtered[0] || '';
                    this.evaluateOrderPolicies();
                },
        
                rebindSavedOrderPolicies() {
                    if (!this.orderId) return;
                    const ids = (this.formData.sales_policy_ids || []).filter(Boolean);
                    if (ids.length === 0) return;
                    this.evaluateOrderPolicies();
                },
        
                onItemPolicyChange(item, policyId) {
                    if (!item) return;
                    if (policyId) {
                        this.addPolicyById(policyId);
                    } else if (item.selected_policy_id) {
                        this.removeSelectedPolicy(item.selected_policy_id);
                    }
                },
        
                onOrderPolicyChange(policyId) {
                    if (policyId) {
                        this.addPolicyById(policyId);
                    } else if (this.formData.order_policy_id) {
                        this.removeSelectedPolicy(this.formData.order_policy_id);
                    }
                },
        
                onPrimaryOrderPolicyChange(newId) {
                    if (newId) this.addPolicyById(newId);
                },
        
                onExtraOrderPolicyChange(slotIndex, newId) {
                    if (newId) this.addPolicyById(newId);
                },
        
                addAnotherPolicySlot() {
                    // No-op kept for backwards compatibility
                },
        
                removePolicySlot(slotIndex) {
                    // No-op kept for backwards compatibility
                },
        
                cleanupEffectsOfRemovedItemPolicy(item, policyId) {
                    if (!policyId) return;
                    this.cleanupEffectsOfRemovedPolicy(policyId);
                },
        
                cleanupEffectsOfRemovedPolicy(policyId) {
                    if (!policyId) return;
                    const pidStr = String(policyId);
                    const p = this.getSalesPolicyById(policyId);
        
                    for (let i = this.items.length - 1; i >= 0; i--) {
                        const g = this.items[i];
                        if (g.isPromotionalGift && (String(g.promoPolicyId || '') === pidStr || (g._giftKey && g._giftKey.startsWith(`order_policy_${pidStr}`)) || (p && (g.appliedTierPolicy || '').includes(p.name)))) {
                            this.items.splice(i, 1);
                        }
                    }
                    (this.items || []).forEach(it => {
                        if (!it.isPromotionalGift && String(it._appliedPromoPolicyId || '') === pidStr) {
                            it.discount_percent = 0;
                            it.discount_amount = 0;
                            it._autoPromoDiscount = false;
                            it._manualDiscount = false;
                            it._appliedPromoPolicyId = null;
                            it.amount = Math.max(0, Math.round((Number(it.quantity) || 0) * (Number(it.unitPrice) || 0)));
                        }
                        if (String(it._claimedByPolicyId || '') === pidStr) {
                            it._claimedByPolicyId = null;
                        }
                    });
                    if (p && p.apply_scope === 'order' && (p.policy_type === 'percentage' || p.policy_type === 'amount') && this._autoOrderDiscount) {
                        this.orderDiscountPercent = 0;
                        this.formData.discount_amount = 0;
                        this._autoOrderDiscount = false;
                    }
                },
        
                evaluateOrderPolicies() {
                    if (this._isApplyingOrderPolicies) return;
                    this._isApplyingOrderPolicies = true;
        
                    try {
                        const activeGiftKeys = [];
                        const salesItems = this.getSalesItemsWithProduct();
        
                        // 1. Reset trạng thái chiếm dụng và chiết khấu tự động trên các dòng hàng
                        salesItems.forEach(it => {
                            it._claimedByPolicyId = null;
                            if (it._autoPromoDiscount && !it._manualDiscount) {
                                it.discount_percent = 0;
                                it.discount_amount = 0;
                                it._autoPromoDiscount = false;
                                it._appliedPromoPolicyId = null;
                                it.amount = Math.max(0, Math.round((Number(it.quantity) || 0) * (Number(it.unitPrice) || 0)));
                            }
                        });
        
                        if (this._autoOrderDiscount && !this._manualOrderDiscount) {
                            this.orderDiscountPercent = 0;
                            this.formData.discount_amount = 0;
                            this._autoOrderDiscount = false;
                        }
        
                        // 2. Phân tách chính sách độc quyền trên nhóm SP (Pass 1) & chính sách cộng dồn toàn đơn (Pass 2)
                        let remainingItems = [...salesItems];
                        const validSelectedPolicyIds = [];
                        const usedConflictGroups = new Set();
                        const selectedIds = Array.isArray(this.formData.sales_policy_ids) ? [...this.formData.sales_policy_ids] : [];
                        const linePolicies = [];
                        const stackPolicies = [];

                        for (const pid of selectedIds) {
                            const policy = this.getSalesPolicyById(pid);
                            if (!policy) continue;
                            if (policy.stacking_mode === 'stackable_order') {
                                stackPolicies.push(policy);
                            } else {
                                linePolicies.push(policy);
                            }
                        }

                        const syncPolicyGifts = (policy, qualifyingGifts) => {
                            (qualifyingGifts || []).forEach(gInfo => {
                                const freeProd = gInfo.prod || this.getProduct(gInfo.product_id);
                                const freeSku = freeProd?.sku || 'KM-SP';
                                const freeName = freeProd?.name || 'Hàng tặng khuyến mại';
                                const freeUnit = freeProd?.unit || 'cái';
                                const giftKey = `order_policy_${policy.id}_${gInfo.product_id}`;
                                activeGiftKeys.push(giftKey);

                                const existing = this.items.find(g => g.isPromotionalGift && (g._giftKey === giftKey || (String(g.promoPolicyId || '') === String(policy.id) && String(g.product_id || '') === String(freeProd?.id || gInfo.product_id))));
                                if (existing) {
                                    existing._giftKey = giftKey;
                                    existing.promoPolicyId = policy.id;
                                    if (!existing._manualQty) {
                                        existing.quantity = gInfo.qty;
                                    }
                                    existing.sku = freeSku;
                                    existing.name = freeName;
                                    existing.searchQuery = freeName;
                                    existing.unit = freeUnit;
                                    existing.appliedTierPolicy = gInfo.giftLabel;
                                } else {
                                    this.items.push({
                                        _rowKey: 'gift-' + Date.now() + '-' + Math.random(),
                                        id: '',
                                        itemId: freeProd?.id ? String(freeProd.id) : String(gInfo.product_id || ''),
                                        product_id: freeProd?.id || gInfo.product_id || '',
                                        sku: freeSku,
                                        name: freeName,
                                        searchQuery: freeName,
                                        unit: freeUnit,
                                        quantity: gInfo.qty,
                                        gift_quantity: 0,
                                        unitPrice: 0,
                                        originalPrice: 0,
                                        costPrice: Number(freeProd?.cost_price || 0),
                                        discount_percent: 0,
                                        discount_amount: 0,
                                        vat_percent: 0,
                                        vat_amount: 0,
                                        appliedTierPolicy: gInfo.giftLabel,
                                        price_type: 'Khuyến mại',
                                        amount: 0,
                                        checked: true,
                                        isPromotionalGift: true,
                                        promoPolicyId: policy.id,
                                        _giftKey: giftKey,
                                    });
                                }
                                this.showPromoSection = true;
                            });
                        };

                        // Pass 1: Chính sách độc quyền theo nhóm SP (Tặng hàng / Chiết khấu dòng & nhóm SP)
                        for (const policy of linePolicies) {
                            const cGroup = policy.conflict_group ? String(policy.conflict_group).trim() : '';
                            if (cGroup && usedConflictGroups.has(cGroup)) continue;

                            const ev = this.evaluatePolicyBenefitsOnItems(policy, remainingItems);
                            if (ev.eligible && ev.claimedItems && ev.claimedItems.length > 0) {
                                validSelectedPolicyIds.push(String(policy.id));
                                if (cGroup) usedConflictGroups.add(cGroup);

                                const claimedSet = new Set(ev.claimedItems);
                                ev.claimedItems.forEach(it => {
                                    it._claimedByPolicyId = policy.id;
                                    it.selected_policy_id = policy.id;
                                });
                                remainingItems = remainingItems.filter(it => !claimedSet.has(it));

                                if (policy.policy_type === 'quantity') {
                                    syncPolicyGifts(policy, ev.qualifyingGifts);
                                } else if (policy.policy_type === 'percentage' || policy.policy_type === 'amount') {
                                    const discEntries = Array.isArray(ev.itemDiscounts) && ev.itemDiscounts.length > 0
                                        ? ev.itemDiscounts
                                        : ev.claimedItems.map(it => {
                                            const lb = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                                            return { item: it, discountPct: ev.discountPct || 0, discountAmt: Math.round((lb * (ev.discountPct || 0)) / 100) };
                                        });
                                    discEntries.forEach(entry => {
                                        const it = entry.item;
                                        if (!it || it._manualDiscount) return;
                                        const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                                        it.discount_percent = entry.discountPct;
                                        it.discount_amount = entry.discountAmt;
                                        it.amount = Math.max(0, Math.round(lineBase - it.discount_amount));
                                        it._autoPromoDiscount = true;
                                        it._appliedPromoPolicyId = policy.id;
                                    });
                                }
                            }
                        }

                        // Pass 2: Chính sách cộng dồn cấp đơn hàng (tính trên phần thành tiền còn lại sau CK dòng)
                        let stackedOrderDiscAmt = 0;
                        const netOrderBase = salesItems.reduce((s, it) => s + (Number(it.amount) || 0), 0);
                        for (const policy of stackPolicies) {
                            const cGroup = policy.conflict_group ? String(policy.conflict_group).trim() : '';
                            if (cGroup && usedConflictGroups.has(cGroup)) continue;

                            const ev = this.evaluatePolicyBenefitsOnItems(policy, salesItems);
                            if (!ev.eligible) continue;

                            validSelectedPolicyIds.push(String(policy.id));
                            if (cGroup) usedConflictGroups.add(cGroup);

                            if (policy.policy_type === 'quantity') {
                                syncPolicyGifts(policy, ev.qualifyingGifts);
                            } else if ((policy.policy_type === 'percentage' || policy.policy_type === 'amount') && !this._manualOrderDiscount) {
                                if (policy.policy_type === 'percentage') {
                                    stackedOrderDiscAmt += Math.round((netOrderBase * (Number(ev.discountPct) || 0)) / 100);
                                } else {
                                    stackedOrderDiscAmt += Math.min(netOrderBase, Number(ev.discountAmt) || 0);
                                }
                            }
                        }

                        if (stackedOrderDiscAmt > 0 && !this._manualOrderDiscount) {
                            const capped = Math.min(netOrderBase, stackedOrderDiscAmt);
                            this.formData.discount_amount = capped;
                            this.orderDiscountPercent = netOrderBase > 0 ? Math.round((capped / netOrderBase) * 100 * 10) / 10 : 0;
                            this._autoOrderDiscount = true;
                        }
        
                        // 3. Cập nhật mảng formData.sales_policy_ids & formData.sales_policy_id
                        this.formData.sales_policy_ids = validSelectedPolicyIds;
                        this.formData.sales_policy_id = validSelectedPolicyIds[0] || '';
        
                        // 4. Xóa các dòng quà tặng tự sinh không còn thỏa điều kiện
                        for (let i = this.items.length - 1; i >= 0; i--) {
                            const g = this.items[i];
                            if (g.isPromotionalGift && g._giftKey && !activeGiftKeys.includes(g._giftKey)) {
                                this.items.splice(i, 1);
                            }
                        }
                        if (this.getGiftItems().length === 0) {
                            const hasManualPromoSection = (this.items || []).some(it => it.isPromotionalGift);
                            if (!hasManualPromoSection) {
                                this.showPromoSection = false;
                            }
                        }
        
                        this.calculateTotals();
                    } finally {
                        this._isApplyingOrderPolicies = false;
                    }
                },

        getSalesItems() {
            return (this.items || []).filter(it => !it.isPromotionalGift);
        },

        getItemIndex(item) {
            return this.items.indexOf(item);
        },

        getGiftItems() {
            return (this.items || []).filter(it => it.isPromotionalGift);
        },

        getFilteredPromoProducts(query) {
            return this.filterProductListByQuery(this.productsCatalog || [], query);
        },

        addNewGiftRow() {
            this.showPromoSection = true;
            this.activeGiftDropdownIndex = null;
            this.activeProdDropdownIndex = null;

            this.items.push({
                _rowKey: 'gift-' + Date.now() + '-' + Math.random(),
                id: '',
                itemId: '',
                product_id: '',
                sku: '',
                name: '',
                searchQuery: '',
                unit: '',
                quantity: 1,
                gift_quantity: 1,
                unitPrice: 0,
                originalPrice: 0,
                costPrice: 0,
                discount_percent: 0,
                discount_amount: 0,
                vat_percent: 0,
                vat_amount: 0,
                amount: 0,
                picked_qty: 0,
                shortage_reason: '',
                item_status: '',
                item_id_db: '',
                checked: true,
                price_type: 'Hàng khuyến mại',
                appliedTierPolicy: 'Hàng khuyến mại',
                isPromotionalGift: true,
                _manualGift: true,
                _manualQty: true,
            });
            this.calculateTotals();
            this.$nextTick(() => {
                setTimeout(() => {
                    const promoSec = document.getElementById('promo-section');
                    if (promoSec) {
                        promoSec.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 50);
            });
        },

        getVisibleGiftElement(mobId, deskId) {
            const mobEl = document.getElementById(mobId);
            const deskEl = document.getElementById(deskId);
            const primary = this.isMobileViewport ? mobEl : deskEl;
            const fallback = this.isMobileViewport ? deskEl : mobEl;
            if (primary && primary.offsetParent !== null) return primary;
            if (fallback && fallback.offsetParent !== null) return fallback;
            return primary || fallback || null;
        },

        selectGiftProductForRow(item, p) {
            if (!p || !item) return;
            item.product_id = p.id;
            item.itemId = String(p.id);
            item.sku = p.sku;
            item.name = p.name;
            item.searchQuery = p.name;
            item._prodSearching = false;
            item.unit = p.unit || 'cái';
            item.unitPrice = 0;
            const sellPrice = Number(p.price) > 0 ? Number(p.price) : (Number(p.base_price) || Number(p.cost_price) || 0);
            item.originalPrice = sellPrice;
            item.costPrice = Number(p.cost_price || 0);
            item.discount_percent = 0;
            item.discount_amount = 0;
            item.amount = 0;
            item.isPromotionalGift = true;
            item._manualGift = true;
            item._manualQty = true;
            this.activeGiftDropdownIndex = null;
            this.calculateTotals();
            this.$nextTick(() => {
                const giftIdx = this.getGiftItems().indexOf(item);
                const qtyInput = this.getVisibleGiftElement('gift-qty-input-' + giftIdx, 'gift-desk-qty-' + giftIdx);
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.select();
                }
            });
        },

        clearGiftProductRow(item, gIdx = null) {
            if (!item) return;
            item.product_id = '';
            item.itemId = '';
            item.sku = '';
            item.name = '';
            item.searchQuery = '';
            item._prodSearching = true;
            item.unit = '';
            item.originalPrice = 0;
            item.costPrice = 0;
            const giftIdx = gIdx !== null ? gIdx : this.getGiftItems().indexOf(item);
            if (giftIdx >= 0) {
                this.activeGiftDropdownIndex = giftIdx;
                this.giftHighlightIndex = 0;
            }
            this.calculateTotals();
            this.$nextTick(() => {
                if (giftIdx < 0) return;
                const input = this.getVisibleGiftElement('gift-product-input-' + giftIdx, 'gift-desk-input-' + giftIdx);
                if (input) input.focus();
            });
        },

        scrollGiftProdIntoView(gIdx, pIdx) {
            this.$nextTick(() => {
                const el = this.getVisibleGiftElement('gift-opt-' + gIdx + '-' + pIdx, 'gift-desk-opt-' + gIdx + '-' + pIdx);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        removeGiftItem(gItem) {
            const idx = this.items.indexOf(gItem);
            if (idx !== -1) {
                this.items.splice(idx, 1);
                if (this.getGiftItems().length === 0) {
                    this.showPromoSection = false;
                }
                this.calculateTotals();
            }
        },

        focusPromoInput() {
            this.addNewGiftRow();
        },

        onItemPriceTypeChange(index) {
            const item = this.items[index];
            if (!item || !item.product_id) return;
            const prod = this.getProduct(item.product_id);
            if (!prod) return;

            const effTier = item.price_type || this.formData.dealer_tier || 'Nguyên Giá';
            item.unitPrice = this.getProductPrice(prod, item.unit, effTier);
            item.appliedTierPolicy = effTier;

            // Nếu đổi sang loại giá khác Nguyên Giá, xóa chiết khấu tự động của dòng này
            if (!this.isItemOriginalPriceTier(item) && item._autoPromoDiscount && !item._manualDiscount) {
                item.discount_percent = 0;
                item.discount_amount = 0;
                item._autoPromoDiscount = false;
                item._appliedPromoPolicyId = null;
            }

            this.updateLineTotal(index);
            this.calculateTotals();
        },

        addNewRow() {
            this.activeProdDropdownIndex = null;
            const currentTier = this.formData.dealer_tier || 'Nguyên Giá';
            this.items.push({
                _rowKey: 'item-' + Date.now() + '-' + Math.random(),
                id: '',
                itemId: '',
                product_id: '',
                sku: '',
                name: '',
                searchQuery: '',
                unit: '',
                quantity: 1,
                gift_quantity: 0,
                unitPrice: 0,
                originalPrice: 0,
                costPrice: 0,
                discount_percent: 0,
                discount_amount: 0,
                vat_percent: 0,
                vat_amount: 0,
                appliedTierPolicy: currentTier,
                price_type: currentTier,
                selected_policy_id: '',
                amount: 0,
                checked: true,
            });
            this.calculateTotals();
            this.$nextTick(() => {
                const nextIdx = this.getSalesItems().length - 1;
                const nextInput = document.getElementById('row-product-input-' + nextIdx);
                if (nextInput) {
                    nextInput.focus();
                }
            });
        },

        scrollProdIntoView(index, pIdx) {
            this.$nextTick(() => {
                const el = document.getElementById('prod-opt-' + index + '-' + pIdx);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        removeItem(indexOrItem) {
            let index = typeof indexOrItem === 'number' ? indexOrItem : this.items.indexOf(indexOrItem);
            if (index === -1) return;
            const currentTier = this.formData.dealer_tier || 'Nguyên Giá';
            const salesCount = this.getSalesItems().length;
            if (salesCount <= 1 && !this.items[index]?.isPromotionalGift) {
                // Keep 1 clean row if last is cleared
                this.items[index] = {
                    _rowKey: 'item-' + Date.now() + '-' + Math.random(),
                    id: '',
                    itemId: '',
                    product_id: '',
                    sku: '',
                    name: '',
                    searchQuery: '',
                    unit: '',
                    quantity: 1,
                    gift_quantity: 0,
                    unitPrice: 0,
                    originalPrice: 0,
                    costPrice: 0,
                    discount_percent: 0,
                    discount_amount: 0,
                    vat_percent: 0,
                    vat_amount: 0,
                    appliedTierPolicy: currentTier,
                    price_type: currentTier,
                    selected_policy_id: '',
                    amount: 0,
                    checked: true,
                };
            } else {
                this.items.splice(index, 1);
            }
            this.evaluateOrderPolicies();
        },

        recalculateItemPrices() {
            const currentTier = this.formData.dealer_tier || 'Nguyên Giá';
            const isOrigTier = this.isOriginalPriceTier(currentTier);
            this.items.forEach((it, idx) => {
                if (it.checked === false) return;
                if (it.isPromotionalGift) return;

                it.price_type = currentTier;
                it.appliedTierPolicy = currentTier;
                if (it.product_id) {
                    const prod = this.getProduct(it.product_id);
                    if (prod) {
                        it.unitPrice = this.getProductPrice(prod, it.unit, currentTier);
                        it.vat_percent = 0;
                        it.vat_amount = 0;
                    }
                }
                // Nếu loại giá mới không phải Nguyên Giá, dọn dẹp chiết khấu tự động của dòng
                if (!isOrigTier && it._autoPromoDiscount && !it._manualDiscount) {
                    it.discount_percent = 0;
                    it.discount_amount = 0;
                    it._autoPromoDiscount = false;
                    it._appliedPromoPolicyId = null;
                }
                this.updateLineTotal(idx);
            });
            if (!isOrigTier && this._autoOrderDiscount && !this._manualOrderDiscount) {
                this.orderDiscountPercent = 0;
                this.formData.discount_amount = 0;
                this._autoOrderDiscount = false;
            }
            this.calculateTotals();
        },

        applyBulkDiscountPercent() {
            const val = this.bulkDiscountPercent;
            if (val === '' || val === null || val === undefined) return;
            const pct = Math.min(100, Math.max(0, Number(val) || 0));

            this.items.forEach((it, idx) => {
                if (it.isPromotionalGift || !it.product_id) return;
                it._manualDiscount = true;
                it.discount_percent = pct;
                const qty = Number(it.quantity) || 0;
                const price = Number(it.unitPrice) || 0;
                const lineBase = qty * price;
                it.discount_amount = Math.round((lineBase * pct) / 100);
                this.updateLineTotal(idx, 'from_percent');
            });
            this.calculateTotals();
        },

        onLineDiscountPercentChange(index) {
            const it = this.items[index];
            if (!it) return;
            it._manualDiscount = true;
            const qty = Number(it.quantity) || 0;
            const price = Number(it.unitPrice) || 0;
            const lineBase = qty * price;
            const pct = Math.min(100, Math.max(0, Number(it.discount_percent) || 0));
            it.discount_percent = pct;
            it.discount_amount = Math.round((lineBase * pct) / 100);

            this.updateLineTotal(index, 'from_percent');
        },

        onLineDiscountAmountChange(index) {
            const it = this.items[index];
            if (!it) return;
            it._manualDiscount = true;
            const qty = Number(it.quantity) || 0;
            const price = Number(it.unitPrice) || 0;
            const lineBase = qty * price;
            const amt = Math.max(0, Number(it.discount_amount) || 0);
            it.discount_amount = amt;
            if (lineBase > 0) {
                it.discount_percent = Math.round((Math.min(lineBase, amt) / lineBase) * 100 * 10) / 10;
            } else {
                it.discount_percent = 0;
            }

            this.updateLineTotal(index, 'from_amount');
        },

        updateLineTotal(index, source = 'from_percent') {
            const it = this.items[index];
            if (it) {
                const qty = Number(it.quantity) || 0;
                const price = Number(it.unitPrice) || 0;
                const lineBase = qty * price;

                let lineDiscount = 0;
                if (source === 'from_amount') {
                    lineDiscount = Math.min(lineBase, Math.max(0, Number(it.discount_amount) || 0));
                    it.discount_amount = lineDiscount;
                    if (lineBase > 0) {
                        it.discount_percent = Math.round((lineDiscount / lineBase) * 100 * 10) / 10;
                    }
                } else {
                    const discPercent = Math.min(100, Math.max(0, Number(it.discount_percent) || 0));
                    lineDiscount = Math.min(lineBase, (lineBase * discPercent) / 100);
                    it.discount_amount = Math.round(lineDiscount);
                }

                const lineAfterDiscount = Math.max(0, lineBase - lineDiscount);
                it.vat_percent = 0;
                it.vat_amount = 0;
                it.amount = Math.round(lineAfterDiscount);
            }
            this.evaluateOrderPolicies();
        },

        onOrderDiscountPercentChange() {
            this._manualOrderDiscount = true;
            this._autoOrderDiscount = false;
            const pct = Number(this.orderDiscountPercent) || 0;
            this.formData.discount_amount = Math.round((this.totalWithVat * pct) / 100);
            this.calculateTotals();
        },

        onOrderDiscountAmountChange() {
            this._manualOrderDiscount = true;
            this._autoOrderDiscount = false;
            const amt = Number(this.formData.discount_amount) || 0;
            if (this.totalWithVat > 0) {
                this.orderDiscountPercent = Math.round((amt / this.totalWithVat) * 100 * 10) / 10;
            }
            this.calculateTotals();
        },

        increaseQty(index) {
            const item = this.items[index];
            if (!item) return;
            let q = Number(item.quantity) || 0;
            item.quantity = Math.max(1, q + 1);
            if (item.isPromotionalGift) {
                item._manualQty = true;
            }
            this.updateLineTotal(index);
        },

        decreaseQty(index) {
            const item = this.items[index];
            if (!item) return;
            let q = Number(item.quantity) || 0;
            item.quantity = Math.max(1, q - 1);
            if (item.isPromotionalGift) {
                item._manualQty = true;
            }
            this.updateLineTotal(index);
        },

        increaseGiftQty(index) {
            const item = this.items[index];
            if (!item || item.isPromotionalGift) return;
            item._manualGift = true;
            let q = Number(item.gift_quantity) || 0;
            item.gift_quantity = q + 1;
            this.onGiftQtyChange(index);
        },

        decreaseGiftQty(index) {
            const item = this.items[index];
            if (!item || item.isPromotionalGift) return;
            item._manualGift = true;
            let q = Number(item.gift_quantity) || 0;
            item.gift_quantity = Math.max(0, q - 1);
            this.onGiftQtyChange(index);
        },

        onGiftQtyChange(index) {
            const item = this.items[index];
            if (!item) return;
            item._manualGift = true;
            if (item.gift_quantity < 0 || isNaN(item.gift_quantity)) {
                item.gift_quantity = 0;
            }
            this.updateLineTotal(index, 'from_amount');
        },

        getPriceStep(price) {
            const p = Math.abs(Number(price) || 0);
            if (p < 50000) return 1000;
            if (p < 200000) return 5000;
            return 10000;
        },

        increasePrice(index) {
            const item = this.items[index];
            if (!item || item.isPromotionalGift) return;
            let p = Number(item.unitPrice) || 0;
            const step = this.getPriceStep(p);
            item.unitPrice = p + step;
            item.price_type = 'Tùy chỉnh';
            this.updateLineTotal(index);
        },

        decreasePrice(index) {
            const item = this.items[index];
            if (!item || item.isPromotionalGift) return;
            let p = Number(item.unitPrice) || 0;
            const step = this.getPriceStep(p);
            item.unitPrice = Math.max(0, p - step);
            item.price_type = 'Tùy chỉnh';
            this.updateLineTotal(index);
        },

        isCustomerEligibleForPolicy(policy) {
            if (!policy) return false;
            let custIds = policy.target_customer_ids;
            if (typeof custIds === 'string') {
                try { custIds = JSON.parse(custIds); } catch (e) { custIds = []; }
            }
            if (!Array.isArray(custIds)) custIds = [];

            let custTiers = policy.target_customer_tiers;
            if (typeof custTiers === 'string') {
                try { custTiers = JSON.parse(custTiers); } catch (e) { custTiers = []; }
            }
            if (!Array.isArray(custTiers)) custTiers = [];
            custTiers = custTiers.map(t => String(t || '').trim()).filter(Boolean);

            if (custIds.length === 0 && custTiers.length === 0) return true;

            const curDealerId = this.formData.dealer_id;
            if (!curDealerId) return false;

            if (custIds.some(id => String(id) === String(curDealerId))) {
                return true;
            }

            if (custTiers.length > 0) {
                const dealer = this.getSelectedDealer();
                const dealerTierNorm = _normalizeSearchText(dealer?.tier || '');
                if (dealerTierNorm && custTiers.some(t => {
                    const targetNorm = _normalizeSearchText(t);
                    return targetNorm && (dealerTierNorm === targetNorm || dealerTierNorm.includes(targetNorm) || targetNorm.includes(dealerTierNorm));
                })) {
                    return true;
                }
            }

            return false;
        },

        isProductInPolicy(policy, productId) {
            if (!policy || !productId) return false;
            let prodIds = policy.target_product_ids;
            if (typeof prodIds === 'string') {
                try { prodIds = JSON.parse(prodIds); } catch(e) { prodIds = []; }
            }
            if (!Array.isArray(prodIds) || prodIds.length === 0) return true;
            return prodIds.some(id => String(id) === String(productId));
        },

        isPolicyApplied(policy) {
            if (!policy) return false;
            return (this.appliedPromoPolicyIds || []).includes(policy.id);
        },

        isPolicyEligible(policy) {
            if (!policy || !this.isCustomerEligibleForPolicy(policy)) return false;
            const activeItems = (this.items || []).filter(it => !it.isPromotionalGift && it.checked !== false && it.product_id);
            return this.evaluatePolicyBenefitsOnItems(policy, activeItems).eligible === true;
        },

        getPolicyConditionDesc(policy) {
            if (!policy) return '';
            const parts = [];
            if (policy.policy_type === 'quantity') {
                parts.push(`Mua ${Math.round(Number(policy.buy_qty) || 0)} tặng ${Math.round(Number(policy.free_qty) || 0)}`);
            } else if (policy.policy_type === 'percentage') {
                parts.push(`Chiết khấu ${Number(policy.discount_percent) || 0}%`);
            } else if (policy.policy_type === 'amount') {
                parts.push(`Giảm ${this.formatNumber(policy.discount_amount || 0)} đ`);
            }

            if (policy.apply_scope === 'order') {
                parts.push('cho toàn đơn');
            } else {
                parts.push('từng sản phẩm');
            }

            const minVal = Number(policy.min_order_value) || 0;
            if (minVal > 0) {
                parts.push(`(tối thiểu ${this.formatNumber(minVal)} đ)`);
            }
            return parts.join(' ');
        },

        getPolicyBenefitDesc(policy) {
            if (!policy) return 'Đã áp dụng';
            if (policy.policy_type === 'quantity') {
                return `Đã tặng quà (Mua ${Math.round(Number(policy.buy_qty) || 0)} tặng ${Math.round(Number(policy.free_qty) || 0)})`;
            }
            if (policy.policy_type === 'percentage') {
                return `Đã chiết khấu ${Number(policy.discount_percent) || 0}%`;
            }
            if (policy.policy_type === 'amount') {
                return `Đã giảm ${this.formatNumber(policy.discount_amount || 0)} đ`;
            }
            return 'Đã áp dụng';
        },

        getPolicyIneligibleReason(policy) {
            if (!policy) return '';
            if (this.isPolicyApplied(policy)) return '';
            if (!this.isCustomerEligibleForPolicy(policy)) {
                return 'Chỉ áp dụng đại lý chỉ định';
            }

            const isDiscount = this.isDiscountPolicy(policy);
            if (isDiscount && policy.apply_scope === 'order' && !this.isOriginalPriceTier(this.formData.dealer_tier)) {
                return 'Chỉ áp dụng loại giá Nguyên Giá';
            }

            const minVal = Number(policy.min_order_value) || 0;
            const orderSubtotal = this.subtotalBeforeVat || 0;
            if (policy.apply_scope === 'order' && minVal > 0 && orderSubtotal < minVal) {
                return `Cần mua thêm ${this.formatNumber(minVal - orderSubtotal)} đ`;
            }
            if (policy.policy_type === 'quantity') {
                const buyQty = Number(policy.buy_qty) || 0;
                const matchingItems = this.items.filter(it => !it.isPromotionalGift && it.product_id && this.isProductInPolicy(policy, it.product_id));
                if (matchingItems.length === 0) return 'Đơn chưa có SP áp dụng';
                if (matchingItems.some(it => (Number(it.quantity) || 0) >= buyQty)) {
                    return 'Đã áp dụng chính sách khác';
                }
                return `Cần mua tối thiểu ${Math.round(buyQty)} SP`;
            }
            if (policy.apply_scope === 'product') {
                const matchingItems = this.items.filter(it => !it.isPromotionalGift && it.product_id && this.isProductInPolicy(policy, it.product_id));
                if (matchingItems.length === 0) return 'Đơn chưa có SP áp dụng';
                if (isDiscount) {
                    const hasOriginalTier = matchingItems.some(it => this.isItemOriginalPriceTier(it));
                    if (!hasOriginalTier) {
                        return 'Chỉ áp dụng SP loại giá Nguyên Giá';
                    }
                }
                const hasReachingValue = matchingItems.some(it => {
                    if (isDiscount && !this.isItemOriginalPriceTier(it)) return false;
                    const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                    return lineBase >= minVal && lineBase > 0;
                });
                if (hasReachingValue) {
                    return 'Đã áp dụng chính sách khác';
                }
                if (minVal > 0) {
                    return `Dòng SP chưa đạt ${this.formatNumber(minVal)} đ`;
                }
            }
            return 'Chưa đủ điều kiện';
        },

        checkAndApplyPromotions(force = false) {
            if (!this.autoApplyPromotions && !force) return;
            if (this._isApplyingPromotions) return;
            this._isApplyingPromotions = true;

            try {
                const notices = [];
                const appliedPolicyIds = [];

                // 1. Dọn dẹp các dòng quà tặng cũ do hệ thống tự sinh (chỉ dọn nếu chưa được sửa tay, dùng splice in-place)
                for (let i = this.items.length - 1; i >= 0; i--) {
                    const it = this.items[i];
                    if (it.isPromotionalGift && !it._manualQty && !it._manualGift) {
                        this.items.splice(i, 1);
                    }
                }
                this.items.forEach(it => {
                    if (it._autoPromoGift && !it._manualGift) {
                        it.gift_quantity = 0;
                        it._autoPromoGift = false;
                    }
                    if (it._autoPromoDiscount && !it._manualDiscount) {
                        it.discount_percent = 0;
                        it.discount_amount = 0;
                        it._autoPromoDiscount = false;
                    }
                    if (!it._manualGift && !it._manualDiscount) {
                        it._appliedPromoPolicyId = null;
                    }
                });

                // Reset chiết khấu đơn hàng tự động cũ nếu có
                if (this._autoOrderDiscount && !this._manualOrderDiscount) {
                    this.orderDiscountPercent = 0;
                    this.formData.discount_amount = 0;
                    this._autoOrderDiscount = false;
                }

                // 2. Xét các chính sách Mua X tặng Y (policy_type === 'quantity')
                for (const policy of this.salesPoliciesCatalog) {
                    if (policy.policy_type !== 'quantity') continue;
                    if (!this.isCustomerEligibleForPolicy(policy)) continue;

                    const buyQty = Number(policy.buy_qty) || 0;
                    const freeQty = Number(policy.free_qty) || 0;
                    if (buyQty <= 0 || freeQty <= 0) continue;

                    // Duyệt các dòng sản phẩm đạt điều kiện mua
                    const currentLen = this.items.length;
                    for (let i = 0; i < currentLen; i++) {
                        const item = this.items[i];
                        if (item.isPromotionalGift || !item.product_id) continue;
                        if (!this.isProductInPolicy(policy, item.product_id)) continue;

                        // QUY TẮC: Dòng này đã nhận 1 chính sách hoặc đã có chiết khấu thì không tự động nhận thêm quà tặng
                        if (item._appliedPromoPolicyId && item._appliedPromoPolicyId !== policy.id) continue;
                        if ((Number(item.discount_percent) > 0 || Number(item.discount_amount) > 0) && !item._autoPromoGift) continue;

                        const itemQty = Number(item.quantity) || 0;
                        if (itemQty >= buyQty) {
                            const freeMultiplier = Math.floor(itemQty / buyQty);
                            const totalFreeQty = freeMultiplier * freeQty;

                            // Xác định sản phẩm quà tặng trong phạm vi Hãng / NCC của nhân viên
                            const giftRes = this.resolveScopedGiftProduct(policy.free_product_id || null, policy, item.product_id);
                            if (!giftRes.allowed || !giftRes.prod) continue;
                            const freeProd = giftRes.prod;

                            const isSameProduct = !policy.free_product_id || String(policy.free_product_id) === String(item.product_id);

                            if (isSameProduct) {
                                // Tặng chính sản phẩm đó -> điền trực tiếp vào cột SL TẶNG của dòng (nếu chưa sửa tay)
                                if (!item._manualGift) {
                                    item.gift_quantity = totalFreeQty;
                                    item._autoPromoGift = true;
                                }
                                item._appliedPromoPolicyId = policy.id;
                                if (!item._manualDiscount) {
                                    item.discount_percent = 0;
                                    item.discount_amount = 0;
                                    item._autoPromoDiscount = false;
                                }
                                this.updateLineTotal(i, 'from_amount');
                                notices.push(`Tặng kèm ${item.gift_quantity} ${item.unit || ''} ${item.name} (theo ${policy.code})`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            } else {
                                // Tặng sản phẩm khác -> thêm dòng quà tặng riêng biệt
                                item._appliedPromoPolicyId = policy.id;
                                if (!item._manualDiscount) {
                                    item.discount_percent = 0;
                                    item.discount_amount = 0;
                                    item._autoPromoDiscount = false;
                                }
                                this.updateLineTotal(i, 'from_amount');

                                const freeSku = freeProd?.sku || 'KM-PVC';
                                const freeName = (freeProd?.name || 'Sản phẩm khuyến mại');
                                const freeUnit = freeProd?.unit || 'cái';

                                const existingGiftRow = this.items.find(it => it.isPromotionalGift && it.promoPolicyId === policy.id);
                                if (existingGiftRow) {
                                    if (!existingGiftRow._manualQty) {
                                        existingGiftRow.quantity = totalFreeQty;
                                    }
                                } else {
                                    this.items.push({
                                        _rowKey: 'gift-' + Date.now() + '-' + Math.random(),
                                        id: '',
                                        itemId: freeProd?.id ? String(freeProd.id) : String(item.product_id),
                                        product_id: freeProd?.id || item.product_id,
                                        sku: freeSku,
                                        name: freeName,
                                        searchQuery: freeName,
                                        unit: freeUnit,
                                        quantity: totalFreeQty,
                                        gift_quantity: 0,
                                        unitPrice: 0,
                                        originalPrice: 0,
                                        costPrice: Number(freeProd?.cost_price || 0),
                                        discount_percent: 0,
                                        discount_amount: 0,
                                        vat_percent: 0,
                                        vat_amount: 0,
                                        appliedTierPolicy: `[TẶNG KÈM] ${policy.name}`,
                                        price_type: 'Khuyến mại',
                                        amount: 0,
                                        checked: true,
                                        isPromotionalGift: true,
                                        promoPolicyId: policy.id,
                                    });
                                }

                                notices.push(`Tặng ${totalFreeQty} ${freeUnit} ${freeName} (theo ${policy.code})`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            }
                        }
                    }
                }

                // 3. Xét các chính sách Chiết khấu % (policy_type === 'percentage')
                // QUY TẮC: Chính sách chiết khấu không được áp cho các loại giá khác loại Nguyên giá
                for (const policy of this.salesPoliciesCatalog) {
                    if (policy.policy_type !== 'percentage') continue;
                    if (!this.isCustomerEligibleForPolicy(policy)) continue;

                    const discPct = Number(policy.discount_percent) || 0;
                    if (discPct <= 0) continue;
                    const minVal = Number(policy.min_order_value) || 0;

                    if (policy.apply_scope === 'order') {
                        // Chỉ áp dụng toàn đơn khi loại giá đơn hàng là Nguyên Giá
                        if (!this.isOriginalPriceTier(this.formData.dealer_tier)) continue;

                        if (this.subtotalBeforeVat >= minVal && this.subtotalBeforeVat > 0) {
                            if (discPct > (this.orderDiscountPercent || 0) || force) {
                                this.orderDiscountPercent = discPct;
                                const amt = Math.round((this.totalWithVat * discPct) / 100);
                                this.formData.discount_amount = amt;
                                this._autoOrderDiscount = true;
                                notices.push(`Giảm ${discPct}% toàn đơn (${policy.name})`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            }
                        }
                    } else if (policy.apply_scope === 'product') {
                        this.items.forEach((it, idx) => {
                            if (it.isPromotionalGift || !it.product_id) return;
                            if (!this.isProductInPolicy(policy, it.product_id)) return;

                            // Chỉ áp dụng khi dòng sản phẩm có loại giá là Nguyên Giá
                            if (!this.isItemOriginalPriceTier(it)) return;

                            // QUY TẮC: Nếu dòng này đã nhận 1 chính sách khác hoặc đã có quà tặng thì KHÔNG tự động áp chiết khấu
                            if (it._appliedPromoPolicyId && it._appliedPromoPolicyId !== policy.id) return;
                            if (Number(it.gift_quantity) > 0 && !it._manualDiscount) return;

                            const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                            if (lineBase >= minVal) {
                                it._appliedPromoPolicyId = policy.id;
                                if (!it._manualDiscount) {
                                    it._autoPromoDiscount = true;
                                    it.discount_percent = discPct;
                                    const lineDiscount = (lineBase * discPct) / 100;
                                    it.discount_amount = Math.round(lineDiscount);
                                    const lineAfterDiscount = Math.max(0, lineBase - lineDiscount);
                                    it.vat_percent = 0;
                                    it.vat_amount = 0;
                                    it.amount = Math.round(lineAfterDiscount);
                                }
                                notices.push(`Giảm ${discPct}% sp ${it.sku || it.name} (${policy.code})`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            }
                        });
                    }
                }

                // 4. Xét các chính sách Giảm tiền mặt (policy_type === 'amount')
                // QUY TẮC: Chính sách chiết khấu không được áp cho các loại giá khác loại Nguyên giá
                for (const policy of this.salesPoliciesCatalog) {
                    if (policy.policy_type !== 'amount') continue;
                    if (!this.isCustomerEligibleForPolicy(policy)) continue;

                    const discAmt = Number(policy.discount_amount) || 0;
                    if (discAmt <= 0) continue;
                    const minVal = Number(policy.min_order_value) || 0;

                    if (policy.apply_scope === 'order') {
                        // Chỉ áp dụng toàn đơn khi loại giá đơn hàng là Nguyên Giá
                        if (!this.isOriginalPriceTier(this.formData.dealer_tier)) continue;

                        if (this.subtotalBeforeVat >= minVal && this.subtotalBeforeVat > 0) {
                            this.formData.discount_amount = discAmt;
                            this._autoOrderDiscount = true;
                            if (this.totalWithVat > 0) {
                                this.orderDiscountPercent = Math.round((discAmt / this.totalWithVat) * 100 * 10) / 10;
                            }
                            notices.push(`Giảm ${this.formatNumber(discAmt)} đ toàn đơn (${policy.name})`);
                            if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                        }
                    } else if (policy.apply_scope === 'product') {
                        this.items.forEach((it, idx) => {
                            if (it.isPromotionalGift || !it.product_id) return;
                            if (!this.isProductInPolicy(policy, it.product_id)) return;

                            // Chỉ áp dụng khi dòng sản phẩm có loại giá là Nguyên Giá
                            if (!this.isItemOriginalPriceTier(it)) return;

                            // QUY TẮC: Nếu dòng này đã nhận 1 chính sách khác hoặc đã có quà tặng hoặc đã có chiết khấu thì KHÔNG tự động áp giảm tiền
                            if (it._appliedPromoPolicyId && it._appliedPromoPolicyId !== policy.id) return;
                            if ((Number(it.gift_quantity) > 0 || Number(it.discount_percent) > 0) && !it._manualDiscount) return;

                            const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                            if (lineBase >= minVal) {
                                it._appliedPromoPolicyId = policy.id;
                                if (!it._manualDiscount) {
                                    it._autoPromoDiscount = true;
                                    it.discount_amount = discAmt;
                                    if (lineBase > 0) {
                                        it.discount_percent = Math.round((discAmt / lineBase) * 100 * 10) / 10;
                                    }
                                    const afterDisc = Math.max(0, lineBase - discAmt);
                                    it.vat_percent = 0;
                                    it.vat_amount = 0;
                                    it.amount = Math.round(afterDisc);
                                }
                                notices.push(`Giảm ${this.formatNumber(discAmt)} đ cho sp ${it.sku || it.name}`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            }
                        });
                    }
                }

                this.appliedPromotionNotices = notices;
                this.appliedPromoPolicyIds = appliedPolicyIds;
            } finally {
                this._isApplyingPromotions = false;
            }
        },

        handleOrderTypeChange() {
            if (this.formData.order_type === 'consignment') {
                if (this.employeeWarehouse) {
                    this.formData.warehouse = this.employeeWarehouse;
                } else {
                    const empWh = this.warehousesCatalog.find(w => w.name && w.name.includes('Kho Hàng Ôm'));
                    if (empWh) {
                        this.formData.warehouse = empWh.name;
                    }
                }
            } else {
                // Loại bình thường: mặc định kho xuất là kho tổng công ty
                this.formData.warehouse = this.mainCompanyWarehouse || 'Kho Tổng';
            }

            this.recalculateItemPrices();
            // this.checkAndApplyPromotions(true);
            this.calculateTotals();
            this.onWarehouseChange();
        },

        calculateTotals() {
            let rawSubtotal = 0;

            this.items.forEach(it => {
                if (!it.checked) return;
                const qty = Number(it.quantity) || 0;
                const price = Number(it.unitPrice) || 0;
                const discAmount = Number(it.discount_amount) || 0;

                it.vat_percent = 0;
                it.vat_amount = 0;

                it.amount = Math.max(0, (qty * price) - discAmount);
                rawSubtotal += it.amount;
            });

            this.subtotalBeforeVat = Math.round(rawSubtotal);
            this.totalVat = 0;
            this.totalWithVat = this.subtotalBeforeVat;

            const overallDiscount = Number(this.formData.discount_amount) || 0;
            this.finalTotal = Math.max(0, this.totalWithVat - overallDiscount);

            this.subtotal = this.subtotalBeforeVat;
            this.formData.tax_amount = 0;
            this.totalAmount = this.finalTotal;

            const curT = this.tickets[this.activeTicketIndex];
            if (curT) {
                curT.subtotalBeforeVat = this.subtotalBeforeVat;
                curT.totalVat = this.totalVat;
                curT.totalWithVat = this.totalWithVat;
                curT.finalTotal = this.finalTotal;
                curT.orderDiscountPercent = this.orderDiscountPercent;
                curT.bulkDiscountPercent = this.bulkDiscountPercent;
            }


        },

        formatNumber(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0));
        },

        confirmClose() {
            this.closeTicket(this.activeTicketIndex);
        },

        resetForm() {
            this.addNewTicket();
        },

        async submitOrder(andPrint = false) {
            if (this.isReadonly) {
                alert('Đơn hàng đang ở chế độ Chỉ đọc, không thể chỉnh sửa.');
                return;
            }
            if (!this.formData.customer_name) {
                this.errorMessage = 'Vui lòng nhập tên khách hàng hoặc chọn đại lý.';
                return;
            }

            const validItems = this.items.filter(it => it.name || it.sku || it.product_id);
            if (validItems.length === 0) {
                this.errorMessage = 'Vui lòng chọn ít nhất một sản phẩm vào đơn hàng.';
                return;
            }

            // 1. Cảnh báo xác nhận khi vượt quá hạn mức công nợ
            if (this.isDebtOverLimit()) {
                const dealer = this.getSelectedDealer();
                const curDebt = Number(dealer?.current_debt || 0);
                const limit = Number(dealer?.credit_limit || 0);
                const projDebt = this.getProjectedDebt();
                const excess = this.getDebtExcessAmount();
                const confirmMsg = 
                    `⚠️ CẢNH BÁO VƯỢT QUÁ HẠN MỨC CÔNG NỢ!\n\n` +
                    `Khách hàng: ${dealer?.name || this.formData.customer_name}\n` +
                    `• Hạn mức công nợ: ${this.formatNumber(limit)} đ\n` +
                    `• Nợ hiện tại: ${this.formatNumber(curDebt)} đ\n` +
                    `• Giá trị đơn này: ${this.formatNumber(this.finalTotal)} đ\n` +
                    `• Dự kiến nợ sau đơn: ${this.formatNumber(projDebt)} đ\n` +
                    `-----------------------------------------\n` +
                    `==> VƯỢT HẠN MỨC: +${this.formatNumber(excess)} đ\n\n` +
                    `Bạn có chắc chắn muốn tiếp tục lưu đơn hàng này không?`;
                if (!confirm(confirmMsg)) {
                    return;
                }
            }

            // 2. Chặn lên đơn khi hết / vượt quá tồn kho khả dụng (áp dụng cho mọi trạng thái kể cả Báo giá)
            const overStockList = this.getOverStockItems();

            if (overStockList.length > 0) {
                const targetWh = this.formData.warehouse || 'Kho xuất';
                const itemsDesc = overStockList.map((it, i) => {
                    const avail = this.getItemAvailableStockInUnit(it);
                    const req = (Number(it.quantity) || 0) + (Number(it.gift_quantity) || 0);
                    const diff = this.getItemOverStockQty(it);
                    return `• [${it.sku || 'SP'}] ${it.name}\n   - Đặt: ${req} ${it.unit} | Tồn khả dụng: ${this.formatNumber(avail)} ${it.unit} (Thiếu: ${this.formatNumber(diff)} ${it.unit})`;
                }).join('\n');

                const alertMsg = 
                    `🚫 KHÔNG THỂ LÊN ĐƠN HÀNG: HẾT TỒN KHO KHẢ DỤNG!\n\n` +
                    `Kho [${targetWh}] không đủ tồn khả dụng cho ${overStockList.length} sản phẩm:\n\n` +
                    `${itemsDesc}\n\n` +
                    `⚠️ QUY ĐỊNH: Hết tồn khả dụng là không được lên đơn.\n` +
                    `Vui lòng giảm số lượng đặt hoặc chọn kho xuất khác có đủ tồn khả dụng trước khi lưu đơn!`;

                alert(alertMsg);
                this.errorMessage = `Không thể lên đơn: ${overStockList.length} sản phẩm đã hết/vượt tồn kho khả dụng tại [${targetWh}].`;
                this.saving = false;
                return;
            }

            // 3. Chặn lên đơn khi khách hàng chưa có MST và đã đạt giới hạn 3 đơn (đơn thứ 4 không thể lên đơn)
            if (this.isNoTaxCodeLimitReached()) {
                const dealer = this.getSelectedDealer();
                const count = this.getCustomerOrdersCount();
                const orderNum = count + 1;
                const alertMsg = 
                    `🚫 KHÔNG THỂ LÊN ĐƠN HÀNG: KHÁCH HÀNG CHƯA CÓ MÃ SỐ THUẾ (MST)!\n\n` +
                    `Khách hàng: ${dealer?.name || this.formData.customer_name}\n` +
                    `• Số đơn đã có: ${count}/3 đơn\n` +
                    `• Đơn hiện tại: Đơn thứ ${orderNum}\n\n` +
                    `⚠️ QUY ĐỊNH HỆ THỐNG:\n` +
                    `- Khách hàng chưa có MST chỉ được lên tối đa 3 đơn hàng.\n` +
                    `- Đơn thứ 4 trở đi sẽ KHÔNG ĐƯỢC PHÉP LÊN ĐƠN!\n\n` +
                    `Vui lòng cập nhật Mã số thuế (MST) cho khách hàng trước khi tiếp tục lên đơn.`;

                alert(alertMsg);
                this.errorMessage = `Chặn lên đơn: Khách hàng [${dealer?.name || this.formData.customer_name}] chưa có MST và đã đạt tối đa 3 đơn (${count}/3). Vui lòng cập nhật MST!`;
                this.saving = false;
                return;
            }

            this.saving = true;
            this.errorMessage = '';

            const cleanPolicyIds = (this.formData.sales_policy_ids || [])
                .map(id => Number(id))
                .filter(id => Number.isFinite(id) && id > 0);

            const payload = {
                id: this.orderId ? this.orderId : undefined,
                orderCode: this.formData.order_code || undefined,
                orderDate: this.formData.order_date,
                order_date: this.formData.order_date,
                deliveryDate: this.formData.delivery_date,
                delivery_date: this.formData.delivery_date,
                pickingDate: this.formData.picking_date,
                picking_date: this.formData.picking_date,
                orderType: this.formData.order_type || 'normal',
                dealerId: this.formData.dealer_id || null,
                dealerName: this.formData.customer_name,
                customer_name: this.formData.customer_name,
                customerPhone: this.formData.customer_phone,
                customer_phone: this.formData.customer_phone,
                address: this.formData.customer_address,
                customer_address: this.formData.customer_address,
                warehouse: this.formData.warehouse,
                paymentMethod: this.formData.payment_method,
                payment_method: this.formData.payment_method,
                status: this.formData.status,
                notes: this.formData.notes,
                subtotal: this.subtotal,
                discountAmount: this.formData.discount_amount,
                discountPercent: this.orderDiscountPercent,
                discount_amount: this.formData.discount_amount,
                discount_percent: this.orderDiscountPercent,
                salesPolicyId: cleanPolicyIds[0] || null,
                sales_policy_id: cleanPolicyIds[0] || null,
                salesPolicyIds: cleanPolicyIds,
                sales_policy_ids: cleanPolicyIds,
                taxAmount: this.formData.tax_amount,
                totalAmount: this.totalAmount,
                hasShortageWarning: this.getOverStockItems().length > 0,
                has_shortage_warning: this.getOverStockItems().length > 0,
                items: validItems.map(it => ({
                    id: it.id || undefined,
                    itemId: it.product_id,
                    product_id: it.product_id,
                    sku: it.sku,
                    name: it.name,
                    unit: it.unit,
                    quantity: it.quantity,
                    gift_quantity: Number(it.gift_quantity) || 0,
                    giftQuantity: Number(it.gift_quantity) || 0,
                    discount_percent: Number(it.discount_percent) || 0,
                    discount_amount: Number(it.discount_amount) || 0,
                    vat_percent: Number(it.vat_percent) || 0,
                    vat_amount: Number(it.vat_amount) || 0,
                    unitPrice: it.unitPrice,
                    unit_price: it.unitPrice,
                    originalPrice: it.originalPrice,
                    costPrice: it.costPrice,
                    appliedTierPolicy: it.price_type || it.appliedTierPolicy,
                    amount: it.amount,
                }))
            };

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
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
                if (data.success || res.ok) {
                    // Phát tín hiệu đồng bộ tức thì cho các tab khác (đặc biệt là trang Soạn hàng /inventory/picking)
                    const finalOrdId = data.order?.id || data.order_id || payload.id;
                    const finalOrdCode = data.order?.orderCode || payload.orderCode || this.formData.order_code;
                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({
                            type: 'ORDER_SAVED',
                            orderId: finalOrdId,
                            orderCode: finalOrdCode,
                            timestamp: Date.now()
                        });
                    } catch(e) {}
                    try {
                        localStorage.setItem('erp_order_sync_trigger', JSON.stringify({
                            time: Date.now(),
                            type: 'ORDER_SAVED',
                            orderCode: finalOrdCode,
                            orderId: finalOrdId
                        }));
                    } catch(e) {}

                    const savedTicket = this.tickets[this.activeTicketIndex];
                    if (savedTicket?._fromPos || this.isFromPos()) {
                        sessionStorage.removeItem('pos_cart_items');
                        sessionStorage.removeItem('pos_dealer_id');
                        sessionStorage.removeItem('pos_warehouse');
                    }

                    const isComingFromPos = (savedTicket?._fromPos || this.isFromPos());

                    if (andPrint) {
                        alert('Lưu đơn thành công! Đang chuyển sang chế độ in chứng từ...');
                        this.printCurrentOrder();
                    } else if (isComingFromPos) {
                        // Tối ưu UX: Không chặn bằng popup alert(), chuyển ngay lập tức và hiển thị toast đẹp mắt ở POS
                        sessionStorage.setItem('pos_flash_toast', 'Lưu đơn hàng ' + finalOrdCode + ' thành công!');
                    } else {
                        alert('Lưu đơn hàng thành công!');
                    }

                    const savedIdx = this.activeTicketIndex;
                    if (this.tickets.length > 1) {
                        this.tickets.splice(savedIdx, 1);
                        this.switchTicket(Math.min(savedIdx, this.tickets.length - 1));
                    } else {
                        const targetUrl = this.getReturnUrl();
                        sessionStorage.removeItem('order_from_pos');
                        sessionStorage.removeItem('order_return_url');
                        window.location.href = targetUrl;
                    }
                } else {
                    this.errorMessage = data.message || 'Lưu đơn thất bại, vui lòng kiểm tra lại.';
                }
            } catch (err) {
                console.error(err);
                this.errorMessage = 'Lỗi kết nối máy chủ: ' + err.message;
            } finally {
                this.saving = false;
            }
        },

        // ─── Helpers: Giai đoạn soạn hàng ─────────────────────────
        isPickingStage() {
            const st = this.formData.status || '';
            return ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong',
                    'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng'].includes(st);
        },

        convertNumberToVietnameseWords(number) {
            const num = Math.round(Number(number) || 0);
            if (num === 0) return 'KHÔNG VIỆT NAM ĐỒNG';
            const digits = ['', 'MỘT', 'HAI', 'BA', 'BỐN', 'NĂM', 'SÁU', 'BẢY', 'TÁM', 'CHÍN'];
            const units = ['', 'NGHÌN', 'TRIỆU', 'TỶ', 'NGHÌN TỶ', 'TRIỆU TỶ'];

            const readThree = (n, isLastGroup) => {
                const hundreds = Math.floor(n / 100);
                const remainder = n % 100;
                const tens = Math.floor(remainder / 10);
                const ones = remainder % 10;
                const res = [];

                if (hundreds > 0 || !isLastGroup) {
                    res.push(digits[hundreds] + ' TRĂM');
                }

                if (tens > 1) {
                    res.push(digits[tens] + ' MƯƠI');
                    if (ones === 1) res.push('MỐT');
                    else if (ones === 4) res.push('TƯ');
                    else if (ones === 5) res.push('LĂM');
                    else if (ones > 0) res.push(digits[ones]);
                } else if (tens === 1) {
                    res.push('MƯỜI');
                    if (ones === 5) res.push('LĂM');
                    else if (ones > 0) res.push(digits[ones]);
                } else {
                    if (ones > 0) {
                        if (hundreds > 0 || !isLastGroup) {
                            res.push('LINH');
                        }
                        res.push(digits[ones]);
                    }
                }
                return res.join(' ');
            };

            const groups = [];
            let temp = Math.abs(num);
            while (temp > 0) {
                groups.push(temp % 1000);
                temp = Math.floor(temp / 1000);
            }

            const words = [];
            for (let i = groups.length - 1; i >= 0; i--) {
                const g = groups[i];
                if (g > 0) {
                    words.push(readThree(g, i === groups.length - 1) + ' ' + units[i]);
                }
            }

            return (words.join(' ').replace(/\s+/g, ' ').trim() + ' VIỆT NAM ĐỒNG').toUpperCase();
        },

        async printCurrentOrder(mode = 'standard') {
            let serverData = null;
            if (this.orderId) {
                try {
                    const res = await fetch('/api/orders/' + this.orderId + '/print-data');
                    if (res.ok) {
                        const json = await res.json();
                        if (json.success && json.order) {
                            serverData = json.order;
                        }
                    }
                } catch(e) {
                    console.error('Error fetching print data:', e);
                }
            }

            // Đồng bộ trực tiếp theo các giá trị hiển thị trên form để đảm bảo giá và chiết khấu 100% đồng nhất
            const subtotalVal = Number(this.subtotalBeforeVat || this.subtotal || 0);
            const discItems = this.items.filter(it => !it.isPromotionalGift).reduce((s, it) => s + (Number(it.discount_amount) || 0), 0);
            const extraDisc = Number(this.formData.discount_amount) || 0;
            const finalVal = Number(this.finalTotal || this.totalAmount || 0);

            const formItems = this.items.filter(it => (it.product_id || it.name) && it.checked !== false);
            const printItems = formItems.map((it, idx) => {
                const isGift = Boolean(it.isPromotionalGift);
                const uPrice = isGift ? 0 : (Number(it.unitPrice) || 0);
                const q = Number(it.quantity) || 0;
                const discAmt = isGift ? 0 : (Number(it.discount_amount) || 0);
                const discPct = isGift ? 0 : (Number(it.discount_percent) || 0);
                const lineBase = isGift ? 0 : (q * uPrice);
                const lineTot = isGift ? 0 : (Number(it.amount) || Math.max(0, lineBase - discAmt));

                return {
                    tt: idx + 1,
                    id: it.id || ('it_' + idx),
                    sku: it.sku || '',
                    name: it.name || '',
                    tax_name: it.tax_name || it.name || '',
                    unit: it.unit || '',
                    unit_price: uPrice,
                    sale_price: uPrice,
                    quantity: q,
                    sold_quantity: isGift ? 0 : q,
                    gift_quantity: isGift ? q : Number(it.gift_quantity || 0),
                    amount: lineBase,
                    discount: discAmt,
                    discount_percent: discPct,
                    total: lineTot,
                    price_type: isGift ? 'Hàng tặng kèm' : (it.price_type || this.formData.dealer_tier || ''),
                    price_tier_code: isGift ? 'KM' : (it.price_tier_code || ''),
                    isPromotionalGift: isGift,
                    is_gift: isGift
                };
            });

            const finalWords = (serverData && Math.round(serverData.final_total) === Math.round(finalVal) && serverData.final_total_words)
                ? serverData.final_total_words
                : this.convertNumberToVietnameseWords(finalVal);

            const o = {
                order_code: this.formData.order_code || serverData?.order_code || (this.orderId ? ('DH-' + this.orderId) : 'CHƯA_LƯU'),
                created_date: serverData?.created_date || new Date().toLocaleDateString('vi-VN'),
                customer_code: serverData?.customer_code || this.formData.customer_code || (this.selectedDealer?.code) || '',
                customer_display: this.formData.customer_name || serverData?.customer_display || 'Khách lẻ',
                customer_name: this.formData.customer_name || serverData?.customer_name || 'Khách lẻ',
                customer_address: this.formData.customer_address || serverData?.customer_address || '',
                customer_phone: this.formData.customer_phone || serverData?.customer_phone || '',
                notes: this.formData.notes || serverData?.notes || '',
                warehouse: this.formData.warehouse || serverData?.warehouse || '',
                payment_method: this.formData.payment_method || serverData?.payment_method || 'Tiền mặt',
                delivery_vehicle: this.formData.delivery_vehicle || serverData?.delivery_vehicle || '',
                delivery_driver: this.formData.delivery_driver || serverData?.delivery_driver || '',
                delivery_date: this.formData.delivery_date || serverData?.delivery_date || '',
                subtotal: subtotalVal,
                total_discount: discItems,
                total_items_amount: subtotalVal,
                extra_discount: extraDisc,
                final_total: finalVal,
                final_total_words: finalWords,
                sales_rep_name: serverData?.sales_rep_name || this.formData.sales_rep_name || serverData?.creator_name || '{{ auth()->user()->name ?? "" }}',
                creator_name: serverData?.creator_name || '{{ auth()->user()->name ?? "" }}',
                updater_name: serverData?.updater_name || '{{ auth()->user()->name ?? "" }}',
                delivery_status: this.formData.status || serverData?.delivery_status || 'Chốt đơn',
                items: printItems
            };

            this.executePrintOrder(o, mode);
        },

        isGiftItem(it) {
            if (!it) return false;
            if (it.price_tier_code === 'KM') return true;
            const pt = (it.price_type || it.appliedTierPolicy || it.applied_tier_policy || '').toLowerCase();
            if (pt.includes('km') || pt.includes('khuyến mại') || pt.includes('khuyến mãi') || pt.includes('tặng')) return true;
            if (it.isPromotionalGift || it.is_gift) return true;
            if (Number(it.unit_price ?? it.unitPrice) === 0) return true;
            if (Number(it.gift_quantity) > 0 && Number(it.sold_quantity ?? it.quantity ?? 0) === 0) return true;
            const name = (it.name || '').toLowerCase();
            if (name.includes('[hàng tặng]') || name.includes('(km)') || name.includes('hàng tặng')) return true;
            return false;
        },

        getRegularPrintItems(order = null) {
            const o = order || this.formData;
            if (!o || !o.items) return [];
            const regular = [];
            o.items.forEach(it => {
                if (!this.isGiftItem(it)) {
                    regular.push({
                        ...it,
                        display_quantity: (it.sold_quantity !== undefined && it.sold_quantity !== null && Number(it.sold_quantity) > 0)
                            ? Number(it.sold_quantity)
                            : Number(it.quantity || 1)
                    });
                }
            });
            return regular;
        },

        getGiftPrintItems(order = null) {
            const o = order || this.formData;
            if (!o || !o.items) return [];
            const gifts = [];
            o.items.forEach(it => {
                if (this.isGiftItem(it)) {
                    gifts.push({
                        ...it,
                        display_quantity: (it.gift_quantity !== undefined && it.gift_quantity !== null && Number(it.gift_quantity) > 0)
                            ? Number(it.gift_quantity)
                            : (it.sold_quantity !== undefined && it.sold_quantity !== null && Number(it.sold_quantity) > 0 ? Number(it.sold_quantity) : Number(it.quantity || 1)),
                        unit_price: 0,
                        discount_percent: 0,
                        discount: 0,
                        total: 0,
                        amount: 0
                    });
                } else if (Number(it.gift_quantity) > 0) {
                    gifts.push({
                        ...it,
                        id: (it.id ? it.id + '_gift' : undefined),
                        price_tier_code: 'KM',
                        price_type: 'Hàng tặng kèm',
                        display_quantity: Number(it.gift_quantity),
                        unit_price: 0,
                        discount_percent: 0,
                        discount: 0,
                        total: 0,
                        amount: 0
                    });
                }
            });
            return gifts;
        },

        getRegularItemsTotal(order = null) {
            return this.getRegularPrintItems(order).reduce((sum, it) => sum + Number(it.total || it.amount || 0), 0);
        },

        getGiftItemsTotal(order = null) {
            return this.getGiftPrintItems(order).reduce((sum, it) => sum + Number(it.total || it.amount || 0), 0);
        },

        getGiftItemsTotalQty(order = null) {
            return this.getGiftPrintItems(order).reduce((sum, it) => sum + Number(it.display_quantity ?? it.gift_quantity ?? it.quantity ?? 0), 0);
        },

        getDiscountDisplay(it) {
            if (!it) return '-';
            if (it.discount_percent !== undefined && it.discount_percent !== null && Number(it.discount_percent) > 0) {
                return Number(it.discount_percent) + '%';
            }
            const lineBase = (Number(it.unit_price || 0) * Number(it.quantity || 0)) || Number(it.amount || 0);
            const disc = Number(it.discount || 0);
            if (disc > 0 && lineBase > 0) {
                return Math.round((disc / lineBase) * 100) + '%';
            }
            return '-';
        },

        getDiscountAmountValue(it) {
            if (!it) return 0;
            let disc = Number(it.discount ?? it.discount_amount ?? 0);
            if (disc <= 0 && Number(it.discount_percent || 0) > 0) {
                const qty = Number(it.display_quantity ?? it.sold_quantity ?? it.quantity ?? 0);
                const base = Number(it.amount || (Number(it.unit_price || 0) * qty) || 0);
                disc = Math.round((base * Number(it.discount_percent)) / 100);
            }
            return Math.max(0, Math.round(disc));
        },

        getDiscountAmountDisplay(it) {
            const amt = this.getDiscountAmountValue(it);
            return amt > 0 ? this.formatNumber(amt) : '-';
        },

        executePrintOrder(o, mode = 'standard') {
            const isNoPrice = (mode === 'no_price' || mode === 'no_price_with_header');
            const isTaxName = (mode === 'tax_name');
            const hasCompanyHeader = (mode !== 'no_price');

            let title = 'PHIẾU BÁN HÀNG KIÊM XUẤT KHO';
            let subTitle = 'HÓA ĐƠN BÁN LẺ & GIAO HÀNG';
            if (mode === 'no_price') {
                title = 'PHIẾU XUẤT KHO - SOẠN HÀNG';
                subTitle = 'DÀNH CHO BỘ PHẬN KHO & BỐC XẾP (KHÔNG HIỂN THỊ ĐƠN GIÁ, KHÔNG TIÊU ĐỀ)';
            } else if (mode === 'no_price_with_header') {
                title = 'PHIẾU GIAO NHẬN HÀNG HÓA';
                subTitle = 'BIÊN BẢN BÀN GIAO & ĐỐI CHIẾU SỐ LƯỢNG HÀNG HÓA';
            } else if (mode === 'tax_name') {
                title = 'BẢNG KÊ BÁN HÀNG THEO HÓA ĐƠN';
                subTitle = 'BẢNG KÊ CHI TIẾT HÀNG HÓA THEO TÊN HÓA ĐƠN GTGT';
            }

            const regularItems = this.getRegularPrintItems(o);
            const giftItems = this.getGiftPrintItems(o);
            const hasGifts = giftItems.length > 0;

            let regularTableRows = '';
            regularItems.forEach((it, idx) => {
                const prodName = isTaxName ? (it.tax_name || it.name) : it.name;
                const subName = (!isTaxName && it.tax_name && it.tax_name !== it.name) ? `<div style="font-size:10.5px;color:#555;font-style:italic;">${it.tax_name}</div>` : '';
                const discText = this.getDiscountDisplay(it);
                const discAmtText = this.getDiscountAmountDisplay(it);
                const qtyVal = it.display_quantity ?? it.sold_quantity ?? it.quantity;
                
                if (isNoPrice) {
                    regularTableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:13px;">${this.formatNumber(qtyVal)}</td>
                        </tr>
                    `;
                } else {
                    regularTableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:12.5px;white-space:nowrap;">${this.formatNumber(qtyVal)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;white-space:nowrap;">${this.formatNumber(it.unit_price)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;white-space:nowrap;">${discText}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;white-space:nowrap;">${discAmtText}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;white-space:nowrap;">${this.formatNumber(it.total || it.amount)}</td>
                        </tr>
                    `;
                }
            });

            const regularTotalAmount = this.getRegularItemsTotal(o);
            const regularTotalQty = regularItems.reduce((s, it) => s + Number(it.display_quantity ?? it.sold_quantity ?? it.quantity ?? 0), 0);

            let regularSummaryHtml = '';
            if (!isNoPrice) {
                if (Number(o.extra_discount || 0) > 0) {
                    regularSummaryHtml = `
                        <tr>
                            <td colspan="8" style="text-align:right;font-weight:bold;padding:5px 8px;border:1px solid #111;white-space:nowrap;">Tổng tiền hàng</td>
                            <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;white-space:nowrap;">${this.formatNumber(regularTotalAmount)} VND</td>
                        </tr>
                        <tr>
                            <td colspan="8" style="text-align:right;font-weight:bold;padding:5px 8px;border:1px solid #111;white-space:nowrap;">Chiết khấu đơn</td>
                            <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;white-space:nowrap;">-${this.formatNumber(o.extra_discount)} VND</td>
                        </tr>
                        <tr>
                            <td colspan="8" style="text-align:right;font-weight:bold;padding:5px 8px;border:1px solid #111;background-color:#f8fafc;-webkit-print-color-adjust:exact;print-color-adjust:exact;white-space:nowrap;">Tổng thanh toán</td>
                            <td style="text-align:right;font-weight:900;padding:5px 6px;border:1px solid #111;font-size:13.5px;color:#000;background-color:#f8fafc;-webkit-print-color-adjust:exact;print-color-adjust:exact;white-space:nowrap;">${this.formatNumber(o.final_total || Math.max(0, regularTotalAmount - Number(o.extra_discount)))} VND</td>
                        </tr>
                    `;
                } else {
                    regularSummaryHtml = `
                        <tr>
                            <td colspan="8" style="text-align:right;font-weight:bold;padding:5px 8px;border:1px solid #111;background-color:#f8fafc;-webkit-print-color-adjust:exact;print-color-adjust:exact;white-space:nowrap;">Tổng thanh toán</td>
                            <td style="text-align:right;font-weight:900;padding:5px 6px;border:1px solid #111;font-size:13px;background-color:#f8fafc;-webkit-print-color-adjust:exact;print-color-adjust:exact;white-space:nowrap;">${this.formatNumber(o.final_total || regularTotalAmount)} VND</td>
                        </tr>
                    `;
                }
            } else {
                regularSummaryHtml = `
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:bold;padding:5px 8px;border:1px solid #111;white-space:nowrap;">Tổng SL:</td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;white-space:nowrap;">${this.formatNumber(regularTotalQty)}</td>
                    </tr>
                `;
            }

            let giftTableHtml = '';
            let giftTotalQty = 0;
            if (hasGifts) {
                let giftTableRows = '';
                giftItems.forEach((it, idx) => {
                    const prodName = isTaxName ? (it.tax_name || it.name) : it.name;
                    const subName = (!isTaxName && it.tax_name && it.tax_name !== it.name) ? `<div style="font-size:10.5px;color:#555;font-style:italic;">${it.tax_name}</div>` : '';
                    const qtyVal = it.display_quantity ?? it.gift_quantity ?? it.quantity;

                    giftTableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:13px;">${this.formatNumber(qtyVal)}</td>
                        </tr>
                    `;
                });

                giftTotalQty = giftItems.reduce((s, it) => s + Number(it.display_quantity ?? it.gift_quantity ?? it.quantity ?? 0), 0);

                const giftSummaryHtml = `
                    <tr>
                        <td colspan="4" style="border:1px solid #111;padding:5px 6px;text-align:right;font-weight:bold;">Tổng SL hàng tặng:</td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;">${this.formatNumber(giftTotalQty)}</td>
                    </tr>
                `;

                giftTableHtml = `
                    <div style="margin-top: 12px; page-break-inside: avoid;">
                        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; background-color: #dfdfdf; padding: 4px 8px; border: 1px solid #111; border-bottom: none; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            Hàng tặng
                        </div>
                        <table class="product-table" style="margin-top: 0; width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="width: 35px;">TT</th>
                                    <th style="width: 95px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 55px;">ĐVT</th>
                                    <th style="width: 65px;">SL</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${giftTableRows}
                                ${giftSummaryHtml}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            const printHtml = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>${title} - ${o.order_code}</title>
                    <style>
                        @page { 
                            size: A4 portrait; 
                            margin: 10mm 12mm 10mm 12mm; 
                        }
                        * {
                            box-sizing: border-box;
                        }
                        body { 
                            font-family: 'Times New Roman', Times, serif; 
                            font-size: 12.5px; 
                            color: #000; 
                            line-height: 1.35; 
                            margin: 0; 
                            padding: 0; 
                            background: #fff;
                        }
                        table { 
                            width: 100%; 
                            border-collapse: collapse; 
                        }
                        .product-table th { 
                            background-color: #f1f5f9; 
                            border: 1px solid #111; 
                            padding: 6px 4px; 
                            font-weight: bold; 
                            font-size: 11.5px; 
                            text-transform: uppercase;
                            text-align: center;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        .product-table td {
                            border: 1px solid #111;
                            padding: 5px 6px;
                            font-size: 12px;
                        }
                        @media print {
                            body { padding: 0; }
                            .no-print { display: none !important; }
                        }
                    </style>
                </head>
                <body>
                    <!-- Header Doanh Nghiệp (Ẩn khi in không TĐ - no_price) -->
                    ${hasCompanyHeader ? `
                    <table style="width: 100%; border: none; border-bottom: 2px solid #1b4a8c; padding-bottom: 6px; margin-bottom: 12px; border-collapse: collapse;">
                        <tr>
                            <td style="width: 65%; vertical-align: top; border: none; padding: 0; text-align: left;">
                                <table style="border: none; border-collapse: collapse; margin-bottom: 2px;">
                                    <tr>
                                        <td style="border: none; padding: 0 10px 0 0; vertical-align: middle;">
                                            <img src="/images/logo.png" alt="Logo Đại Sơn" style="height: 52px; width: auto; display: block; -webkit-print-color-adjust: exact; print-color-adjust: exact;" onerror="this.style.display='none'">
                                        </td>
                                        <td style="border: none; padding: 0; vertical-align: middle;">
                                            <div style="font-size: 14.5px; font-weight: bold; color: #1b4a8c; text-transform: uppercase; letter-spacing: 0.5px;">${this.companySetting?.company_name || 'HỆ THỐNG PHÂN PHỐI ĐẠI SƠN'}</div>
                                            <div style="font-size: 11px; color: #444; font-weight: bold; text-transform: uppercase; margin-top: 1px;">${this.companySetting?.tax_name || this.companySetting?.company_name || ''}</div>
                                        </td>
                                    </tr>
                                </table>
                                <div style="font-size: 11.5px; color: #333; margin-top: 3px; line-height: 1.4;">
                                    ${this.companySetting?.address ? `<div><strong>Địa chỉ:</strong> ${this.companySetting.address}</div>` : ''}
                                    ${(this.companySetting?.hotline || this.companySetting?.phone) ? `<div><strong>Hotline / Zalo:</strong> ${this.companySetting.hotline || this.companySetting.phone} ${this.companySetting?.email ? `&nbsp;|&nbsp; <strong>Email:</strong> ${this.companySetting.email}` : ''}</div>` : ''}
                                </div>
                            </td>
                            <td style="width: 35%; vertical-align: top; text-align: right; border: none; padding: 0;">
                                <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #333;">Mẫu số: 02 - VT</div>
                                <div style="font-size: 10.5px; font-style: italic; color: #555; margin-bottom: 4px;">(Theo TT 200/2014/TT-BTC)</div>
                                <div style="display: inline-block; border: 1px solid #1b4a8c; background-color: #f0f7ff; padding: 3px 8px; border-radius: 4px; font-size: 11.5px; font-weight: bold; color: #1b4a8c; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                    Số: ${o.order_code}
                                </div>
                                <div style="font-size: 11px; color: #555; margin-top: 3px;">Ngày in: ${new Date().toLocaleDateString('vi-VN')} ${new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}</div>
                            </td>
                        </tr>
                    </table>
                    ` : `
                    <!-- In Không TĐ: Tuyệt đối không chứa thông tin công ty, chỉ hiển thị số phiếu và ngày in ở góc phải -->
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                        <div style="text-align: right; font-size: 11px;">
                            <div style="display: inline-block; border: 1px solid #333; background-color: #f8fafc; padding: 2px 8px; border-radius: 4px; font-size: 11.5px; font-weight: bold; color: #111;">
                                Số: ${o.order_code}
                            </div>
                            <div style="font-size: 10.5px; color: #555; margin-top: 2px;">Ngày in: ${new Date().toLocaleDateString('vi-VN')}</div>
                        </div>
                    </div>
                    `}

                    <!-- Tiêu Đề Phiếu -->
                    <div style="text-align: center; margin-bottom: 14px;">
                        <h2 style="margin: 0 0 3px 0; font-size: 19px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: #111;">${title}</h2>
                        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #555; letter-spacing: 0.5px; margin-bottom: 3px;">${subTitle}</div>
                        <div style="font-size: 12px; font-style: italic; color: #333;">
                            Ngày lập: <strong>${o.created_date || '---'}</strong>
                        </div>
                    </div>

                    <!-- Thông Tin Khách Hàng & Giao Vận (2 Cột Rõ Ràng) -->
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px; font-size: 12.5px; line-height: 1.5;">
                        <tr>
                            <td style="width: 60%; vertical-align: top; border: none; padding: 0 12px 0 0;">
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 120px; border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Mã khách hàng:</td>
                                        <td style="border: none; padding: 2px 0; font-size: 13px;"><strong>${o.customer_code || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 120px; border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Khách hàng / Đại lý:</td>
                                        <td style="border: none; padding: 2px 0; font-size: 13px;"><strong style="text-transform: uppercase;">${(o.customer_name || o.customer_display || '---').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Địa chỉ giao hàng:</td>
                                        <td style="border: none; padding: 2px 0;">${o.customer_address || '---'}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Điện thoại liên hệ:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.customer_phone || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Diễn giải / Ghi chú:</td>
                                        <td style="border: none; padding: 2px 0; font-style: italic;">${o.notes || '---'}</td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 40%; vertical-align: top; border: none; padding: 0 0 0 12px; border-left: 1px dashed #bbb;">
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 118px; border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Kho xuất hàng:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.warehouse || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Nhân viên bán hàng:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.sales_rep_name || o.creator_name || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Hình thức TT:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.payment_method || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Xe / Người giao:</td>
                                        <td style="border: none; padding: 2px 0;">${o.delivery_vehicle || o.delivery_driver || '---'}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Ngày giao dự kiến:</td>
                                        <td style="border: none; padding: 2px 0;">${o.delivery_date || '---'}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!-- Bảng 1: Danh Sách Sản Phẩm -->
                    <div style="page-break-inside: avoid;">
                        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; background-color: #dfdfdf; padding: 4px 8px; border: 1px solid #111; border-bottom: none; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            Danh sách sản phẩm
                        </div>
                        <table class="product-table" style="margin-top: 0; width: 100%; border-collapse: collapse;">
                            <thead>
                                ${isNoPrice ? `
                                    <tr>
                                        <th style="width: 35px;">TT</th>
                                        <th style="width: 95px; text-align: left; padding-left: 6px;">Mã SP</th>
                                        <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                        <th style="width: 55px;">ĐVT</th>
                                        <th style="width: 65px;">SL</th>
                                    </tr>
                                ` : `
                                    <tr>
                                        <th style="width: 34px;">TT</th>
                                        <th style="width: 82px; text-align: left; padding-left: 6px;">Mã SP</th>
                                        <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                        <th style="width: 48px;">ĐVT</th>
                                        <th style="width: 44px;">SL</th>
                                        <th style="width: 82px;">Đơn giá</th>
                                        <th style="width: 56px;">C.khấu</th>
                                        <th style="width: 80px;">Tiền CK</th>
                                        <th style="width: 96px;">Thành tiền</th>
                                    </tr>
                                `}
                            </thead>
                            <tbody>
                                ${regularTableRows || `<tr><td colspan="${isNoPrice ? 5 : 9}" style="text-align:center;padding:8px;font-style:italic;color:#666;border:1px solid #111;">Không có sản phẩm bán</td></tr>`}
                                ${regularSummaryHtml}
                            </tbody>
                        </table>
                    </div>

                    <!-- Bảng 2: Hàng Tặng (nếu có) -->
                    ${giftTableHtml}

                    <!-- Bằng Chữ & Trạng Thái -->
                    ${!isNoPrice ? `
                        <div style="margin-top: 8px; font-size: 12.5px; line-height: 1.55;">
                            <div style="margin-bottom: 2px;">
                                <strong>Tổng tiền thanh toán (bằng số):</strong>
                                <span style="font-size: 14px; font-weight: 900; color: #111; margin-left: 4px;">${this.formatNumber(o.final_total || regularTotalAmount)} VND</span>
                            </div>
                            <div>
                                <strong>Tổng tiền thanh toán (bằng chữ):</strong>
                                <em style="text-transform: capitalize; font-weight: bold; margin-left: 4px;">${o.final_total_words || ''}</em>
                            </div>
                        </div>
                    ` : ''}

                    <!-- Dòng Ngày Tháng Phía Trên Chữ Ký -->
                    <div style="margin-top: 12px; text-align: right; font-size: 12px; font-style: italic;">
                        Ngày ..... tháng ..... năm 202...
                    </div>

                    <!-- BẢNG CHỮ KÝ 4 BÊN (Dùng thẻ table chuẩn 4 cột 25% - Tuyệt đối không gãy dòng) -->
                    <table style="width: 100%; border: none; margin-top: 8px; border-collapse: collapse; page-break-inside: avoid;">
                        <tr>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người lập phiếu</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">${o.creator_name || ''}</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Thủ kho xuất</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">&nbsp;</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người giao hàng</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">${o.delivery_driver || ''}</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người nhận hàng</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">&nbsp;</div>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 15px; text-align: center; font-size: 11px; font-style: italic; color: #666;">
                        *(Quý khách vui lòng kiểm tra kỹ số lượng, quy cách và chất lượng hàng hóa trước khi ký nhận)*
                    </div>
                </body>
                </html>
            `;

            window.printHtmlInline(printHtml, `${title} - ${o.order_code}`);
        },

        getPriceTierCode(val, defaultTier = '') {
            if (!val) val = defaultTier || '';
            let str = String(val).trim();
            const matchParen = str.match(/\(([^)]+)\)/);
            if (matchParen) {
                str = matchParen[1].trim();
            } else if (str === 'Theo phiếu' && defaultTier) {
                str = defaultTier.trim();
            }

            const s = str.toLowerCase();
            if (s.includes('cấp 1') || s.includes('cap 1') || s === 'bc1') return 'BC1';
            if (s.includes('cấp 2') || s.includes('cap 2') || s === 'bc2') return 'BC2';
            if (s.includes('cấp 3') || s.includes('cap 3') || s === 'bc3') return 'BC3';
            if (s.includes('bán lẻ') || s.includes('ban le') || s === 'bl') return 'BL';
            if (s.includes('nguyên giá') || s.includes('nguyen gia') || s === 'ng') return 'NG';
            if (s.includes('khuyến mại') || s.includes('tặng') || s === 'km') return 'KM';

            if (this.priceTiersCatalog && Array.isArray(this.priceTiersCatalog)) {
                const found = this.priceTiersCatalog.find(pt => pt.name && pt.name.toLowerCase() === s);
                if (found && found.code) return found.code;
            }

            return str.toUpperCase();
        }
    };
}
</script>
@endsection
