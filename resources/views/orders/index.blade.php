@extends('layouts.app')

@section('title', 'Quản Lý Đơn Hàng & Báo Giá | ERP Đại Sơn')

@push('styles')
<style>
/* ==========================================================================
   TỐI ƯU DANH SÁCH ĐƠN HÀNG TRÊN MÀN HÌNH MOBILE (<= 768px)
   Tất cả thuộc tính được bọc 100% trong @media (max-width: 768px)
   => Tuyệt đối KHÔNG xung đột hay làm thay đổi giao diện Desktop (> 768px).
   ========================================================================== */
@media (max-width: 768px) {
    /* 2. Header Banner: Gọn gàng 2 tầng trên Mobile */
    .orders-header-banner {
        padding: 0.75rem 0.875rem !important;
        border-radius: 1rem !important;
        gap: 0.5rem !important;
    }
    .orders-header-title-left {
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    .orders-header-title-left a[title*="Quay lại"] {
        width: 34px !important;
        height: 34px !important;
    }
    .orders-header-title-left h1 {
        font-size: 1.25rem !important;
        line-height: 1.2 !important;
        margin: 0 !important;
    }
    .orders-btn-create-mobile {
        height: 34px !important;
        padding: 0 0.875rem !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border-radius: 0.75rem !important;
        white-space: nowrap !important;
    }
    /* Ẩn hoàn toàn hàng nút công cụ phụ (In phiếu, Bảng kê, Soạn & Kiểm Kho, Xếp Xe...) trên màn hình Mobile */
    .orders-header-actions {
        display: none !important;
    }

    /* 3. Thanh Pills trạng thái (Status Pills) */
    .orders-status-pills {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .orders-status-pills::-webkit-scrollbar {
        display: none;
    }
    .orders-status-pills a {
        white-space: nowrap !important;
        padding: 0.4rem 0.75rem !important;
        font-size: 11px !important;
        border-radius: 0.75rem !important;
        flex-shrink: 0 !important;
    }

    /* 4. Form Bộ Lọc (Filter Form) - Chia đôi 2 cột chuẩn tỉ lệ không tràn viền trên Mobile */
    .orders-filter-container {
        padding: 0.75rem 0.75rem !important;
        border-radius: 1rem !important;
        gap: 0.5rem !important;
        box-sizing: border-box !important;
    }
    /* Ẩn toàn bộ form lọc trên Mobile, chỉ hiện khi ấn nút lọc */
    .orders-filter-body {
        display: none;
    }
    .orders-filter-body.is-open {
        display: block !important;
        animation: fadeInFilter 0.2s ease-out;
    }
    @keyframes fadeInFilter {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .orders-filter-form {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
        gap: 0.5rem 0.5rem !important;
        padding-top: 0.25rem !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .orders-filter-form > div {
        grid-column: span 1 !important;
        min-width: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }
    /* Hàng nút Tìm kiếm + Lọc chiếm trọn cả 2 cột */
    .orders-filter-form > div.col-filter-submit {
        grid-column: span 2 !important;
        margin-top: 0.25rem !important;
        width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
    }
    .orders-filter-form input,
    .orders-filter-form select {
        height: 38px !important;
        font-size: 12.5px !important;
        padding: 0.4rem 0.5rem !important;
        border-radius: 0.75rem !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .orders-filter-form input[type="date"] {
        font-size: 11.5px !important;
        padding: 0.4rem 0.35rem !important;
    }
    /* Chừa khoảng trống bên trái cho icon kính lúp của ô khách hàng không bị đè chữ */
    .orders-filter-form input[name="customer"] {
        padding-left: 2rem !important;
    }
    .orders-filter-form .col-filter-submit button {
        height: 38px !important;
        border-radius: 0.75rem !important;
        box-sizing: border-box !important;
    }
    .orders-filter-form .col-filter-submit button[title*="nâng cao"] {
        width: 38px !important;
        flex-shrink: 0 !important;
    }
    .orders-filter-form label {
        font-size: 10.5px !important;
        font-weight: 700 !important;
        margin-bottom: 2px !important;
    }
    .orders-filter-form label span.font-mono {
        display: none !important; /* Ẩn phím tắt "F2 hoặc /" trên mobile */
    }

    /* 5. Chuyển Bảng Danh Sách Đơn Hàng Thành Card View Đẳng Cấp & Gọn Gàng Trên Mobile */
    .orders-table-container {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        overflow: visible !important;
    }
    .orders-table-wrapper {
        overflow: visible !important;
    }
    .orders-table,
    .orders-table tbody {
        display: block !important;
        width: 100% !important;
        min-width: 0 !important;
    }
    .orders-table thead {
        display: none !important;
    }
    .orders-table tr.order-table-row {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1rem !important;
        padding: 0.75rem 0.875rem !important;
        margin-bottom: 0.75rem !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        position: relative !important;
        row-gap: 0.35rem !important;
        column-gap: 0.5rem !important;
        box-sizing: border-box !important;
    }
    .orders-table tr.order-table-row:last-child {
        margin-bottom: 0 !important;
    }

    .orders-table tr.order-table-row td {
        padding: 0;
        border: none !important;
        background: transparent !important;
        box-sizing: border-box !important;
    }

    /* 1. Hàng 1 (Header Card): Checkbox + STT + Mã Đơn Hàng + Cảnh báo (Trải đều) & Loại Đơn (Góc phải) */
    .orders-table tr.order-table-row td.col-checkbox {
        position: absolute !important;
        top: 0.75rem !important;
        left: 0.75rem !important;
        width: 18px !important;
        height: 18px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 5 !important;
    }
    .orders-table tr.order-table-row td.col-stt {
        position: absolute !important;
        top: 0.78rem !important;
        left: 2.3rem !important;
        font-size: 11.5px !important;
        font-weight: 800 !important;
        color: #94a3b8 !important;
        z-index: 5 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
    }
    .orders-table tr.order-table-row td.col-stt::before {
        content: "#";
    }
    /* Ẩn badge Loại Đơn Hàng (Thương mại / Dự án) trên Mobile theo yêu cầu để giao diện thoáng, không bị đè nhãn */
    .orders-table tr.order-table-row td.col-type {
        display: none !important;
    }
    .orders-table tr.order-table-row td.col-code {
        grid-column: span 2 !important;
        order: 1 !important;
        display: block !important;
        width: 100% !important;
        min-height: 24px !important;
        box-sizing: border-box !important;
    }
    .orders-table tr.order-table-row td.col-code > div {
        display: flex !important;
        align-items: center !important;
        gap: 0.375rem !important;
        flex-wrap: nowrap !important;
        min-width: 0 !important;
        margin-left: 4.85rem !important; /* Đứng sau Checkbox & STT */
        margin-right: 0.5rem !important; /* Mở rộng toàn bộ chiều ngang khi đã ẩn Loại đơn hàng */
    }
    .orders-table td.col-code a {
        font-size: 13.5px !important;
        font-weight: 800 !important;
        color: #0284c7 !important;
        white-space: nowrap !important;
    }
    .orders-table td.col-code span {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    /* 2. Hàng 2: Khách Hàng (To, rõ ràng, chiếm trọn 2 cột) */
    .orders-table td.col-customer {
        grid-column: span 2 !important;
        order: 2 !important;
        display: block !important;
        padding: 0.35rem 0 !important;
        margin: 0.2rem 0 !important;
        border-top: 1px solid #f1f5f9 !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .orders-table td.col-customer > div:first-child {
        font-size: 13.5px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        line-height: 1.35 !important;
    }
    .orders-table td.col-customer > div:last-child {
        font-size: 11px !important;
        color: #64748b !important;
        margin-top: 2px !important;
    }

    /* 3. Hàng 3 & 4: Chi tiết Đơn Hàng (2 cột cân đối, không rớt chữ vụn vặt) */
    /* Cột trái: Ngày tạo & Kho hàng */
    .orders-table td.col-date {
        grid-column: 1 !important;
        order: 3 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
        font-size: 11px !important;
        color: #64748b !important;
        line-height: 1.3 !important;
    }
    .orders-table td.col-warehouse {
        grid-column: 1 !important;
        order: 5 !important;
        display: flex !important;
        align-items: center !important;
        font-size: 11px !important;
        color: #475569 !important;
        overflow: hidden !important;
        line-height: 1.3 !important;
        margin-top: 2px !important;
    }
    .orders-table td.col-warehouse::before {
        content: "Kho: ";
        font-weight: 600;
        color: #94a3b8;
        margin-right: 0.25rem !important;
        white-space: nowrap !important;
    }
    .orders-table td.col-warehouse > div {
        display: flex !important;
        align-items: center !important;
        gap: 0.25rem !important;
        min-width: 0 !important;
    }
    .orders-table td.col-warehouse span {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 140px !important;
    }

    /* Cột phải: Người tạo & Thành tiền */
    .orders-table td.col-creator {
        grid-column: 2 !important;
        order: 4 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        font-size: 11px !important;
        color: #475569 !important;
        text-align: right !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.3 !important;
    }
    .orders-table td.col-creator::before {
        content: "Tạo bởi: ";
        font-weight: 600;
        color: #94a3b8;
        margin-right: 0.25rem !important;
        white-space: nowrap !important;
    }
    .orders-table td.col-total {
        grid-column: 2 !important;
        order: 6 !important;
        display: flex !important;
        align-items: baseline !important;
        justify-content: flex-end !important;
        gap: 0.3rem !important;
        font-size: 14.5px !important;
        font-weight: 900 !important;
        color: #0284c7 !important;
        text-align: right !important;
        white-space: nowrap !important;
        margin-top: 2px !important;
        line-height: 1.3 !important;
    }
    .orders-table td.col-total::before {
        content: "Tổng tiền:";
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #64748b !important;
        white-space: nowrap !important;
    }

    /* 4. Hàng 5 (Footer Card): Trạng thái & Nút thao tác dưới cùng */
    .orders-table td.col-status {
        grid-column: span 2 !important;
        order: 7 !important;
        margin-top: 0.35rem !important;
        padding-top: 0.5rem !important;
        border-top: 1px dashed #e2e8f0 !important;
        display: block !important;
    }
    .orders-table td.col-status > div {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }
    .orders-table td.col-status button,
    .orders-table td.col-status a {
        min-height: 32px !important;
        border-radius: 0.75rem !important;
    }
}
</style>
@endpush

@section('content')
@php
    $allOrders = $orders;
    $counts = [
        'Tất cả' => \App\Models\Order::companyOrders()->count(),
        'Báo giá' => \App\Models\Order::companyOrders()->where('status', 'Báo giá')->count(),
        'Chốt đơn' => \App\Models\Order::companyOrders()->whereIn('status', ['Chốt đơn', 'Đã duyệt', 'Chờ duyệt', 'Chờ kế toán duyệt'])->count(),
        'Chờ hàng' => \App\Models\Order::companyOrders()->where(function ($q) {
            $q->where('has_shortage_warning', true)
              ->orWhereIn('status', ['Chờ hàng', 'Chờ hàng về']);
        })->count(),
        'Soạn hàng' => \App\Models\Order::companyOrders()->whereIn('status', ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong', 'Đang kiểm hàng'])->count(),
        'Soạn bốc' => \App\Models\Order::companyOrders()->whereIn('status', ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong', 'Đang kiểm hàng'])->count(),
        'Đơn thiếu' => \App\Models\Order::companyOrders()->where(function ($q) {
            $q->where('shortage_reported', true)
              ->orWhereIn('status', ['Kho báo thiếu hàng', 'Báo thiếu']);
        })->count(),
        'Giao sau' => \App\Models\Order::companyOrders()->where(function ($q) {
            $q->where('order_type', 'backorder')
              ->orWhereIn('status', ['Giao sau', 'Đơn giao sau', 'Nợ hàng']);
        })->count(),
        'Đơn nợ' => \App\Models\Order::companyOrders()->where(function ($q) {
            $q->where('order_type', 'backorder')
              ->orWhereIn('status', ['Giao sau', 'Đơn giao sau', 'Nợ hàng']);
        })->count(),
        'Đã đủ hàng' => \App\Models\Order::companyOrders()->where(function ($q) {
            $q->where('backorder_status', 'ready_to_deliver')
              ->orWhere('status', 'Đã đủ hàng');
        })->count(),
        'Kiểm hàng' => \App\Models\Order::companyOrders()->whereIn('status', ['Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng', 'Sẵn sàng xuất xe'])->count(),
        'Đang vận chuyển' => \App\Models\Order::companyOrders()->whereIn('status', ['Đang vận chuyển', 'Đang giao hàng'])->count(),
        'Đã giao' => \App\Models\Order::companyOrders()->whereIn('status', ['Đã giao', 'Giao thành công'])->count(),
        'Hủy' => \App\Models\Order::companyOrders()->whereIn('status', ['Hủy', 'Đã hủy'])->count(),
    ];

    $allOrderIds = $orders->pluck('id')->map(fn($id) => (string)$id);
    $tableOrdersData = $orders->map(function($o) {
        return [
            'id' => (string)$o->id,
            'code' => $o->order_code,
            'date' => $o->created_at ? $o->created_at->format('d/m/Y') : '',
            'customer' => $o->dealer?->name ?? $o->customer_name ?? '',
            'phone' => $o->customer_phone ?: ($o->dealer?->phone ?: ''),
            'address' => $o->customer_address ?: ($o->dealer?->address ?: ''),
            'warehouse' => $o->warehouse ?: 'Kho Tổng',
            'total' => (float)$o->total_amount,
            'status' => $o->status ?: 'Báo giá',
            'creator' => $o->order_creator ?: ($o->creator?->name ?? ''),
        ];
    });
@endphp

<div class="space-y-4 font-sans text-slate-800 antialiased">

    @php $currentTab = request('tab', 'orders'); @endphp

    @if($currentTab === 'consignment')
        @include('orders.consignment_tab')
    @else
    @php
        $activeFilterCount = 0;
        if (request('from_date')) $activeFilterCount++;
        if (request('to_date')) $activeFilterCount++;
        if (request('search')) $activeFilterCount++;
        if (request('customer')) $activeFilterCount++;
        if (request('order_type')) $activeFilterCount++;
        if (request('payment_status')) $activeFilterCount++;
    @endphp
    <div class="space-y-4" x-data="ordersIndexApp()" @keydown.window="handleGlobalKeydown($event)" @scroll.window="closeActionMenu()" @resize.window="closeActionMenu()">
    <!-- 2. Header Banner (Matching exact React UI on Desktop, Ergonomic 2-row on Mobile) -->
    <div class="orders-header-banner bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        
        <!-- Left: Blue circular back button + Title + Subtitle -->
        <div class="orders-header-title-left flex items-center justify-between sm:justify-start gap-3 w-full sm:w-auto">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <a href="{{ url('/dashboard') }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-sky-600 hover:bg-sky-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-sky-600/20 cursor-pointer transition-transform hover:scale-105" title="Quay lại Tổng quan">
                    <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
                </a>

                <div>
                    <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">Đơn hàng</h1>
                    <p class="hidden sm:block text-xs text-slate-500 mt-0.5 font-medium">Quản lý đơn hàng theo quy trình: Báo giá · Chốt đơn · Soạn hàng · Đang vận chuyển · Đã giao · Hủy</p>
                </div>
            </div>

            <!-- Nút "+ Thêm mới" nổi bật ngay cạnh tiêu đề trên Mobile -->
            <a href="{{ url('/orders/create') }}" class="sm:hidden orders-btn-create-mobile px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-black rounded-xl text-xs transition-all shadow-md shadow-sky-600/20 flex items-center gap-1.5 cursor-pointer shrink-0" title="Lên đơn hàng mới">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Thêm mới</span>
            </a>
        </div>

        <!-- Right: Action Buttons (Gọn gàng, tập trung các thao tác chính, tiện ích cho vào Submenu) -->
        <div class="orders-header-actions flex items-center gap-2">
            <!-- 1. In phiếu -->
            <button type="button" @click="printSelected()" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer shrink-0" title="In phiếu đã chọn (F9 hoặc P)">
                <i class="fa-solid fa-print text-slate-500"></i>
                <span>In phiếu <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] text-slate-600 font-mono">F9</kbd></span>
            </button>

            <!-- 2. Submenu Tiện ích & Thao tác khác (Bảng kê, Phím tắt, Soạn kho, Xếp xe) -->
            <div x-data="{ openUtilities: false }" @click.outside="openUtilities = false" class="relative">
                <button 
                    type="button" 
                    @click="openUtilities = !openUtilities" 
                    class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer shrink-0"
                    title="Tiện ích & Thao tác liên quan"
                >
                    <i class="fa-solid fa-ellipsis-vertical text-slate-400"></i>
                    <span>Tiện ích</span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-150" :class="openUtilities ? 'rotate-180' : ''"></i>
                </button>

                <div 
                    x-show="openUtilities" 
                    x-cloak 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 top-full mt-1.5 w-60 bg-white border border-slate-200 shadow-xl rounded-2xl py-1.5 z-40 text-xs"
                >
                    <!-- Bảng kê xuất hàng (F10) -->
                    <button 
                        type="button" 
                        @click="exportManifest(); openUtilities = false" 
                        class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center justify-between font-semibold text-slate-700 transition"
                    >
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-emerald-600 w-4"></i>
                            <span>Bảng kê xuất hàng</span>
                        </div>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] text-slate-500 font-mono">F10</kbd>
                    </button>

                    <!-- Xem phím tắt (F1) -->
                    <button 
                        type="button" 
                        @click="showShortcutsModal = true; openUtilities = false" 
                        class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center justify-between font-semibold text-slate-700 transition border-b border-slate-100"
                    >
                        <div class="flex items-center gap-2">
                            <i class="fa-regular fa-keyboard text-amber-600 w-4"></i>
                            <span>Xem phím tắt</span>
                        </div>
                        <kbd class="px-1.5 py-0.5 bg-amber-50 border border-amber-200 rounded text-[10px] text-amber-800 font-mono">F1</kbd>
                    </button>

                    <div class="px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Liên kết quy trình</div>

                    <!-- Soạn & Kiểm Kho -->
                    <a 
                        href="{{ url('/inventory/picking') }}" 
                        class="w-full text-left px-3.5 py-2 hover:bg-amber-50 flex items-center gap-2 font-semibold text-slate-700 hover:text-amber-800 transition"
                    >
                        <i class="fa-solid fa-boxes-packing text-amber-600 w-4"></i>
                        <span>Soạn Đơn & Kiểm Kho</span>
                    </a>

                    <!-- Xếp Xe & Tuyến Giao -->
                    <a 
                        href="{{ url('/delivery-dispatch') }}" 
                        class="w-full text-left px-3.5 py-2 hover:bg-purple-50 flex items-center gap-2 font-semibold text-slate-700 hover:text-purple-800 transition"
                    >
                        <i class="fa-solid fa-truck text-purple-600 w-4"></i>
                        <span>Xếp Xe & Tuyến Giao</span>
                    </a>
                </div>
            </div>

            <!-- 3. Bán hàng POS -->
            <a href="{{ url('/orders/pos') }}" class="px-3.5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer shadow-md shadow-purple-600/20 shrink-0" title="Giao diện bán hàng & chọn sản phẩm nhanh vào giỏ">
                <i class="fa-solid fa-cash-register"></i>
                <span>Bán Hàng POS</span>
            </a>

            <!-- 4. Nút Thêm mới trên Desktop -->
            <a href="{{ url('/orders/create') }}" class="hidden sm:flex px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-black rounded-xl text-xs transition-all shadow-md shadow-sky-600/20 items-center gap-1.5 cursor-pointer shrink-0" title="Lên đơn hàng mới (F8 hoặc N)">
                <i class="fa-solid fa-plus"></i>
                <span>Thêm mới <kbd class="px-1.5 py-0.5 bg-sky-700/80 border border-sky-500 rounded text-[10px] text-white font-mono">F8</kbd></span>
            </a>
        </div>

    </div>

    <!-- 3. Status Filter Pills & Filter Toolbar (Matching exact React UI) -->
    <div class="orders-filter-container bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-3">
        
        <!-- Status Tabs (Pills) -->
        <div class="orders-status-pills flex items-center gap-2 overflow-x-auto pb-1">
            @foreach(['Tất cả', 'Báo giá', 'Chốt đơn', 'Chờ hàng', 'Soạn hàng', 'Đơn thiếu', 'Giao sau', 'Đã đủ hàng', 'Kiểm hàng', 'Đang vận chuyển', 'Đã giao', 'Hủy'] as $pill)
                @php
                    $isSel = (request('status') === $pill) 
                        || (!request('status') && $pill === 'Tất cả') 
                        || (request('status') === 'Soạn bốc' && $pill === 'Soạn hàng')
                        || (in_array(request('status'), ['Chờ hàng', 'Chờ hàng về']) && $pill === 'Chờ hàng')
                        || (in_array(request('status'), ['Đơn nợ', 'Nợ hàng', 'Đơn giao sau']) && $pill === 'Giao sau');
                    $badgeCount = $counts[$pill] ?? 0;

                    $pillClass = match($pill) {
                        'Chờ hàng' => $isSel 
                            ? 'bg-amber-600 text-white shadow-2xs ring-2 ring-amber-600/30 font-black' 
                            : ($badgeCount > 0 ? 'bg-amber-100 text-amber-900 hover:bg-amber-200 border border-amber-400 font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'),
                        'Đơn thiếu' => $isSel 
                            ? 'bg-rose-600 text-white shadow-2xs ring-2 ring-rose-600/30 font-black' 
                            : ($badgeCount > 0 ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-300 font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'),
                        'Giao sau', 'Đơn nợ' => $isSel 
                            ? 'bg-amber-600 text-white shadow-2xs ring-2 ring-amber-600/30 font-black' 
                            : ($badgeCount > 0 ? 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-300 font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'),
                        'Đã đủ hàng' => $isSel 
                            ? 'bg-emerald-600 text-white shadow-2xs ring-2 ring-emerald-600/30 font-black' 
                            : ($badgeCount > 0 ? 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-300 font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'),
                        default => $isSel 
                            ? 'bg-sky-600 text-white shadow-2xs ring-2 ring-sky-600/20' 
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'
                    };

                    $badgePillClass = match($pill) {
                        'Đơn thiếu' => $isSel ? 'bg-white/25 text-white' : ($badgeCount > 0 ? 'bg-rose-200 text-rose-800 font-black' : 'bg-slate-200 text-slate-600'),
                        'Giao sau', 'Đơn nợ' => $isSel ? 'bg-white/25 text-white' : ($badgeCount > 0 ? 'bg-amber-200 text-amber-900 font-black' : 'bg-slate-200 text-slate-600'),
                        'Đã đủ hàng' => $isSel ? 'bg-white/25 text-white' : ($badgeCount > 0 ? 'bg-emerald-200 text-emerald-900 font-black' : 'bg-slate-200 text-slate-600'),
                        default => $isSel ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-600'
                    };
                @endphp
                <a href="{{ url('/orders' . ($pill === 'Tất cả' ? '' : '?status=' . urlencode($pill))) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $pillClass }}">
                    @if($pill === 'Đơn thiếu' && $badgeCount > 0)
                        <i class="fa-solid fa-triangle-exclamation text-[11px] {{ $isSel ? 'text-white' : 'text-rose-600 animate-pulse' }}"></i>
                    @elseif(in_array($pill, ['Giao sau', 'Đơn nợ']) && $badgeCount > 0)
                        <i class="fa-solid fa-clipboard-list text-[11px] {{ $isSel ? 'text-white' : 'text-amber-600' }}"></i>
                    @elseif($pill === 'Đã đủ hàng' && $badgeCount > 0)
                        <i class="fa-solid fa-circle-check text-[11px] {{ $isSel ? 'text-white' : 'text-emerald-600' }}"></i>
                    @endif
                    <span>{{ $pill }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $badgePillClass }}">
                        {{ $badgeCount }}
                    </span>
                </a>
            @endforeach
        </div>

        <!-- Mobile Filter Toggle Bar: Chỉ hiện trên mobile -->
        <div class="sm:hidden pt-1 flex items-center gap-2">
            <button type="button" @click="showMobileFilter = !showMobileFilter" 
                class="flex-1 flex items-center justify-between px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 transition-all shadow-2xs cursor-pointer">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-filter"></i>
                    </span>
                    <span class="text-slate-800 font-extrabold text-[12.5px]">Bộ lọc & Tìm kiếm</span>
                    @if($activeFilterCount > 0)
                        <span class="px-2 py-0.5 bg-sky-600 text-white text-[10px] font-black rounded-full">{{ $activeFilterCount }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 text-slate-500 font-bold text-[11px]">
                    <span x-text="showMobileFilter ? 'Thu gọn' : 'Bấm để lọc'"></span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="{ 'rotate-180': showMobileFilter }"></i>
                </div>
            </button>
            @if($activeFilterCount > 0)
                <a href="{{ url('/orders' . (request('status') ? '?status=' . urlencode(request('status')) : '')) }}" 
                   class="px-3 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold flex items-center gap-1 shrink-0 transition-colors shadow-2xs" 
                   title="Xóa tất cả điều kiện lọc">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i>
                    <span>Xóa lọc</span>
                </a>
            @endif
        </div>

        <!-- Filter Body: Mặc định ẩn trên Mobile, khi ấn nút lọc mới hiện; Trên Desktop luôn hiện -->
        <div class="orders-filter-body space-y-3" :class="{ 'is-open': showMobileFilter }">
        <!-- Filter Form Row 1 -->
        <form action="{{ url('/orders') }}" method="GET" class="orders-filter-form grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 text-xs pt-1 items-end">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Từ ngày -->
            <div class="lg:col-span-2 space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Từ ngày</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full p-2 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white">
            </div>

            <!-- Đến ngày -->
            <div class="lg:col-span-2 space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Đến ngày</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full p-2 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white">
            </div>

            <!-- Số phiếu -->
            <div class="lg:col-span-2 space-y-1">
                <label class="font-bold text-slate-500 text-[11px] flex items-center justify-between">
                    <span>Số phiếu</span>
                    <span class="text-[10px] text-sky-600 font-mono font-semibold">F2 hoặc /</span>
                </label>
                <input type="text" x-ref="searchInput" name="search" value="{{ request('search') }}" placeholder="VD: PN2607..." class="w-full p-2 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white placeholder-slate-400">
            </div>

            <!-- Khách hàng (mã/tên) -->
            <div class="lg:col-span-2 space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Khách hàng (mã/tên)</label>
                <div class="relative flex items-center">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-xs"></i>
                    <input type="text" name="customer" value="{{ request('customer') }}" placeholder="Gõ mã hoặc tên..." class="w-full p-2 pl-8 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white placeholder-slate-400">
                </div>
            </div>

            <!-- Trạng thái -->
            <div class="space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Trạng thái</label>
                <select name="status" class="w-full p-2 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white cursor-pointer">
                    <option value="">Tất cả trạng thái</option>
                    @foreach(['Báo giá', 'Chốt đơn', 'Chờ hàng', 'Soạn hàng', 'Đơn thiếu', 'Giao sau', 'Kiểm hàng', 'Đang vận chuyển', 'Đã giao', 'Hủy'] as $st)
                        <option value="{{ $st }}" {{ (request('status') === $st || (request('status') === 'Soạn bốc' && $st === 'Soạn hàng') || (in_array(request('status'), ['Chờ hàng', 'Chờ hàng về']) && $st === 'Chờ hàng') || (in_array(request('status'), ['Đơn nợ', 'Nợ hàng', 'Đơn giao sau']) && $st === 'Giao sau')) ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Loại đơn hàng -->
            <div class="space-y-1">
                <label class="font-bold text-slate-500 text-[11px]">Loại đơn hàng</label>
                <select name="order_type" class="w-full p-2 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:outline-none focus:border-sky-500 bg-white cursor-pointer">
                    <option value="">Tất cả loại đơn</option>
                    <option value="normal" {{ request('order_type') === 'normal' ? 'selected' : '' }}>🛒 Đơn thường</option>
                    <option value="consignment" {{ request('order_type') === 'consignment' ? 'selected' : '' }}>📦 Đơn gửi</option>
                </select>
            </div>

            <!-- Submit Button & Filter Icon -->
            <div class="col-filter-submit lg:col-span-2 flex items-center gap-1.5">
                <button type="submit" class="flex-1 p-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Tìm kiếm</span>
                </button>
                <div class="relative shrink-0">
                    <button type="button" @click="isAdvancedOpen = !isAdvancedOpen" class="p-2 bg-sky-600 text-white rounded-xl cursor-pointer hover:bg-sky-700 transition-colors" title="Bộ lọc nâng cao">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if($activeFilterCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-rose-600 text-white font-black text-[9px] w-4 h-4 rounded-full flex items-center justify-center shadow-xs">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                </div>
            </div>
        </form>

        <!-- Toggle Row: Mở rộng bộ lọc nâng cao + Checkbox -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs pt-2 border-t border-slate-100">
            <button type="button" @click="isAdvancedOpen = !isAdvancedOpen" class="text-sky-600 hover:text-sky-800 font-bold flex items-center gap-1.5 cursor-pointer select-none">
                <span x-text="isAdvancedOpen ? '^ Thu gọn bộ lọc nâng cao' : 'v Mở rộng bộ lọc nâng cao'"></span>
                @if($activeFilterCount > 0)
                    <span class="px-2 py-0.2 bg-sky-100 text-sky-800 rounded-full text-[10px] font-extrabold">{{ $activeFilterCount }}</span>
                @endif
            </button>

            <label class="flex items-center gap-2 font-bold text-slate-700 select-none cursor-pointer">
                <input type="checkbox" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300">
                <span class="flex items-center gap-1 text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                    Chỉ phiếu cần kiểm tra
                </span>
            </label>
        </div>
        </div>

    </div>

    <!-- 3.5. Thanh Thao Tác Hàng Loạt (Bulk Actions Bar) khi tích chọn đơn hàng -->
    <div x-show="selectedIds.length > 0" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="sticky top-2 z-30 bg-slate-900 text-white rounded-2xl p-3 sm:px-4 sm:py-3 shadow-xl border border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-in fade-in">
        
        <div class="flex items-center justify-between sm:justify-start gap-3 flex-wrap">
            <div class="flex items-center gap-2 text-xs sm:text-sm font-black">
                <span class="w-6 h-6 rounded-full bg-sky-500 text-white flex items-center justify-center text-xs shrink-0 font-bold shadow-xs">
                    <i class="fa-solid fa-check"></i>
                </span>
                <span>Đã chọn <strong class="text-sky-400 font-mono text-sm sm:text-base" x-text="selectedIds.length"></strong> đơn hàng</span>
            </div>

            <!-- Nút Bỏ chọn & Chọn tất cả -->
            <div class="flex items-center gap-1.5 text-xs">
                <button type="button" @click="selectedIds = []" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors cursor-pointer text-[11px] font-bold">
                    Bỏ chọn (Esc)
                </button>
                <button type="button" @click="toggleSelectAll(true)" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-sky-300 hover:text-white rounded-lg transition-colors cursor-pointer text-[11px] font-bold" x-show="selectedIds.length < allOrderIds.length">
                    Chọn tất cả (<span x-text="allOrderIds.length"></span>)
                </button>
            </div>
        </div>

        <!-- Các nút thao tác hàng loạt -->
        <div class="flex items-center gap-2 flex-wrap" x-data="{ openBulkStatus: false }">
            <div x-show="isBulkProcessing" class="text-xs text-sky-400 flex items-center gap-1.5 mr-2">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Đang xử lý...</span>
            </div>

            <!-- 1. In phiếu gộp (Tổng hợp SP) -->
            <button type="button" @click="openBulkPrintModal('consolidated')" :disabled="isBulkProcessing" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95 disabled:opacity-50" title="In phiếu gộp tổng hợp toàn bộ sản phẩm của các đơn đã chọn">
                <i class="fa-solid fa-layer-group text-white"></i>
                <span>In phiếu gộp (<span x-text="selectedIds.length"></span>)</span>
            </button>

            <!-- 2. In phiếu hàng loạt (Từng đơn riêng) -->
            <button type="button" @click="openBulkPrintModal('batch')" :disabled="isBulkProcessing" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-900 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95 disabled:opacity-50" title="In hàng loạt từng đơn hàng riêng biệt">
                <i class="fa-solid fa-print text-sky-600"></i>
                <span>In hàng loạt</span>
            </button>

            <!-- 3. Bảng kê xuất hàng (Excel) -->
            <button type="button" @click="exportSelectedManifest()" :disabled="isBulkProcessing" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95 disabled:opacity-50">
                <i class="fa-solid fa-file-excel"></i>
                <span>Bảng kê Excel</span>
            </button>

            <!-- 3. Nhanh: Chốt đơn hàng loạt -->
            <button type="button" @click="bulkUpdateStatus('Chốt đơn')" :disabled="isBulkProcessing" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95 disabled:opacity-50">
                <i class="fa-solid fa-check-double"></i>
                <span>Chốt đơn</span>
            </button>

            <!-- 4. Dropdown đổi trạng thái hàng loạt -->
            <div class="relative" @click.outside="openBulkStatus = false">
                <button type="button" @click="openBulkStatus = !openBulkStatus" :disabled="isBulkProcessing" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 border border-slate-600 cursor-pointer disabled:opacity-50">
                    <i class="fa-solid fa-arrows-rotate text-amber-400"></i>
                    <span>Chuyển trạng thái</span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-150" :class="openBulkStatus ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="openBulkStatus" x-cloak 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 bottom-full sm:bottom-auto sm:top-full mb-1.5 sm:mb-0 sm:mt-1.5 w-52 bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-2xl py-1.5 z-50 text-xs">
                    <div class="px-3 py-1 text-[10px] font-black uppercase text-slate-400 tracking-wider">Chuyển trạng thái hàng loạt</div>
                    <button type="button" @click="bulkUpdateStatus('Báo giá'); openBulkStatus = false" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <span>Báo giá</span>
                    </button>
                    <button type="button" @click="bulkUpdateStatus('Chốt đơn'); openBulkStatus = false" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 font-semibold text-sky-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                        <span>Chốt đơn</span>
                    </button>
                    <button type="button" @click="bulkUpdateStatus('Soạn hàng'); openBulkStatus = false" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 font-semibold text-amber-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Soạn hàng</span>
                    </button>
                    <button type="button" @click="bulkUpdateStatus('Kiểm hàng'); openBulkStatus = false" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 font-semibold text-indigo-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span>Đã kiểm hàng</span>
                    </button>
                    <button type="button" @click="bulkUpdateStatus('Đang vận chuyển'); openBulkStatus = false" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 font-semibold text-purple-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        <span>Đang vận chuyển</span>
                    </button>
                    <button type="button" @click="bulkUpdateStatus('Đã giao'); openBulkStatus = false" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 font-semibold text-emerald-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Đã giao thành công</span>
                    </button>
                    <div class="border-t border-slate-100 my-1"></div>
                    <button type="button" @click="bulkUpdateStatus('Hủy'); openBulkStatus = false" class="w-full text-left px-3.5 py-2 hover:bg-rose-50 flex items-center gap-2 font-semibold text-rose-600 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span>Hủy các đơn đã chọn</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Main Data Table (100% Matching exact React Table Headers & Row Layout) -->
    <div class="orders-table-container bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="orders-table-wrapper overflow-x-auto">
            <table class="orders-table w-full text-left text-xs border-collapse min-w-[1250px]">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold border-b border-slate-300 uppercase tracking-tight text-[11px]">
                        <th class="py-3 px-3 text-center w-[40px]">
                            <input type="checkbox" @change="toggleSelectAll($event.target.checked)" :checked="isAllSelected()" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300 cursor-pointer">
                        </th>
                        <th class="py-3 px-3 text-center w-[50px]">STT</th>
                        <th class="py-3 px-3 min-w-[130px]">MÃ CHỨNG TỪ</th>
                        <th class="py-3 px-3 min-w-[140px]">LOẠI ĐƠN HÀNG</th>
                        <th class="py-3 px-3 w-[100px]">NGÀY</th>
                        <th class="py-3 px-3 min-w-[120px]">KHO</th>
                        <th class="py-3 px-3 min-w-[220px]">KHÁCH HÀNG</th>
                        <th class="py-3 px-3 w-[130px] text-right">THÀNH TIỀN</th>
                        <th class="py-3 px-3 min-w-[140px]">NGƯỜI LÊN ĐƠN</th>
                        <th class="py-3 px-3 min-w-[220px]">TRẠNG THÁI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($orders as $idx => $order)
                        @php
                            $rowNum = $idx + 1;
                            $orderCode = $order->order_code;
                            $isProject = $order->order_type === 'project';
                            $createdDate = $order->created_at ? $order->created_at->format('Y-m-d') : date('Y-m-d');
                            $whName = $order->warehouse ?: 'Kho Tổng';
                            $customerName = $order->dealer?->name ?? $order->customer_name ?? 'Khách hàng';
                            $customerAddr = $order->customer_address ?: ($order->dealer?->address ?: 'Chưa cập nhật địa chỉ');
                            $totalAmtFormatted = number_format($order->total_amount, 0, ',', '.') . ' đ';
                            $payMethod = $order->payment_method ?: 'Ghi nợ đại lý';
                            $creator = $order->order_creator ?: ($order->creator?->name ?? 'Hệ thống');
                            $st = $order->status ?: 'Báo giá';
                            $isBackOrder = in_array($st, ['Giao sau', 'Đơn giao sau', 'Nợ hàng', 'Đã đủ hàng']) || ($order->order_type === 'backorder');
                            $isReadyBackOrder = ($isBackOrder && ($order->backorder_status === 'ready_to_deliver' || $order->status === 'Đã đủ hàng'));
                            $rowClass = 'hover:bg-sky-50/50';
                            if ($isReadyBackOrder) {
                                $rowClass = 'bg-emerald-50/70 hover:bg-emerald-100/70 border-l-4 border-emerald-500';
                            } elseif ($order->shortage_reported) {
                                $rowClass = 'bg-rose-50/60 hover:bg-rose-100/60 border-l-4 border-rose-500';
                            } elseif ($isBackOrder) {
                                $rowClass = 'bg-amber-50/60 hover:bg-amber-100/60 border-l-4 border-amber-500';
                            }
                        @endphp
                        <tr class="order-table-row {{ $rowClass }} transition-colors">
                            <!-- Checkbox -->
                            <td class="col-checkbox py-3 px-3 text-center">
                                <input type="checkbox" :value="'{{ $order->id }}'" x-model="selectedIds" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300 cursor-pointer">
                            </td>

                            <!-- STT -->
                            <td class="col-stt py-3 px-3 text-center font-bold text-slate-700">{{ $rowNum }}</td>

                            <!-- MÃ CHỨNG TỪ -->
                            <td class="col-code py-3 px-3 font-bold text-sky-700">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <a href="{{ url('/orders/' . $order->id . '/edit') }}" class="hover:underline text-left cursor-pointer font-bold font-mono text-slate-900" title="Bấm để xem và sửa chi tiết chứng từ">
                                        {{ $orderCode }}
                                    </a>
                                    @if($order->has_shortage_warning)
                                        <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[9px] font-bold border border-amber-300" title="Đơn đặt khi kho thiếu tồn">⚠️ Thiếu kho</span>
                                    @endif
                                </div>
                            </td>

                            <!-- LOẠI ĐƠN HÀNG -->
                            <td class="col-type py-3 px-3">
                                @if(in_array($order->order_type, ['consignment', 'holding_sale']))
                                    <div class="flex flex-col items-start gap-0.5">
                                        <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-900 font-extrabold text-[11px] border border-amber-300 shadow-2xs shrink-0 inline-flex items-center gap-1">
                                            <span>📦 Đơn gửi</span>
                                        </span>
                                    </div>
                                @elseif($isProject)
                                    <div class="flex flex-col items-start gap-0.5">
                                        <span class="px-2.5 py-1 rounded-md bg-purple-100 text-purple-900 font-extrabold text-[11px] border border-purple-300 shadow-2xs shrink-0 inline-flex items-center gap-1">
                                            <span>🏗️ Đơn dự án</span>
                                        </span>
                                        @if($order->project_name)
                                            <span class="text-[10px] text-purple-800 font-extrabold max-w-[130px] truncate" title="{{ $order->project_name }}">
                                                {{ $order->project_name }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($isBackOrder)
                                    <div class="flex flex-col items-start gap-0.5">
                                        @if($isReadyBackOrder)
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-900 font-black text-[10px] border border-emerald-300 shadow-2xs shrink-0 inline-flex items-center gap-1" title="Kho đã có đủ hàng, có thể xuất giao ngay!">
                                                <span>🟢 Đã đủ hàng</span>
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 font-extrabold text-[10px] border border-amber-300 shadow-2xs shrink-0 inline-flex items-center gap-1" title="Đơn giao sau / nợ hàng đang chờ hàng về">
                                                <span>📋 Giao sau</span>
                                            </span>
                                        @endif
                                        @if($order->parent_order_code)
                                            <span class="text-[9px] text-slate-500 font-mono">Nợ từ: {{ $order->parent_order_code }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-sky-50 text-sky-800 font-extrabold text-[11px] border border-sky-200 shrink-0 inline-flex items-center gap-1">
                                        <span>🛒 Đơn thường</span>
                                    </span>
                                @endif
                            </td>

                            <!-- NGÀY -->
                            <td class="col-date py-3 px-3 font-semibold text-slate-600">
                                <div>{{ $createdDate }}</div>
                                @if(!empty($order->picking_date))
                                    <div class="text-[10px] text-purple-700 font-bold mt-0.5 flex items-center gap-1" title="Ngày soạn hàng xuất kho">
                                        <i class="fa-solid fa-boxes-stacked text-[9px] text-purple-600"></i>
                                        <span>Soạn: {{ \Carbon\Carbon::parse($order->picking_date)->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if(!empty($order->delivery_date))
                                    <div class="text-[10px] text-sky-700 font-bold mt-0.5 flex items-center gap-1" title="Ngày hẹn giao hàng">
                                        <i class="fa-solid fa-truck-fast text-[9px] text-sky-600"></i>
                                        <span>Giao: {{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- KHO -->
                            <td class="col-warehouse py-3 px-3 font-semibold text-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-warehouse text-slate-400 text-xs shrink-0"></i>
                                    <span class="truncate max-w-[150px]" title="{{ $whName }}">
                                        {{ $whName }}
                                    </span>
                                </div>
                            </td>

                            <!-- KHÁCH HÀNG -->
                            <td class="col-customer py-3 px-3 font-bold text-slate-900">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $customerName }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-normal leading-tight mt-0.5">
                                    {{ $customerAddr }}
                                </div>
                            </td>

                            <!-- THÀNH TIỀN -->
                            <td class="col-total py-3 px-3 text-right font-extrabold text-slate-900">
                                {{ $totalAmtFormatted }}
                            </td>

                            <!-- NGƯỜI LÊN ĐƠN -->
                            <td class="col-creator py-3 px-3 font-semibold text-slate-800">
                                {{ $creator }}
                            </td>

                            <!-- TRẠNG THÁI Column (Badge + Action Buttons matching exact React UI) -->
                            <td class="col-status py-3 px-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex flex-col gap-0.5 min-w-[110px]">
                                        @php
                                            $isWaitingStock = in_array($st, ['Chờ hàng', 'Chờ hàng về']) || ($order->has_shortage_warning && !in_array($st, ['Đã giao', 'Hủy', 'Đã hủy', 'Báo giá']));
                                            $badgeConfig = match(true) {
                                                $isWaitingStock => ['bg' => 'bg-amber-100 text-amber-900 border-amber-300 font-black', 'icon' => '⏳', 'label' => 'Chờ hàng', 'sub' => 'Kho chưa đủ hàng'],
                                                $st === 'Báo giá' => ['bg' => 'bg-amber-50 text-amber-800 border-amber-300', 'icon' => '📋', 'label' => 'Báo giá', 'sub' => 'Phiếu báo giá'],
                                                in_array($st, ['Chốt đơn', 'Đã duyệt']) => ['bg' => 'bg-blue-50 text-blue-800 border-blue-300', 'icon' => '🤝', 'label' => 'Chốt đơn', 'sub' => 'Đơn bán hàng'],
                                                in_array($st, ['Giao sau', 'Đơn giao sau', 'Nợ hàng']) => ['bg' => 'bg-amber-50 text-amber-800 border-amber-300', 'icon' => '📋', 'label' => 'Giao sau', 'sub' => 'Đơn giao sau'],
                                                in_array($st, ['Soạn hàng', 'Đang bốc hàng', 'Soạn bốc', 'Kho đang soạn']) => ['bg' => 'bg-purple-50 text-purple-800 border-purple-300', 'icon' => '📦', 'label' => 'Soạn hàng', 'sub' => 'Đơn bán hàng'],
                                                in_array($st, ['Đã bốc xong', 'Kho đã xuất đủ']) => ['bg' => 'bg-indigo-50 text-indigo-800 border-indigo-300', 'icon' => '📦', 'label' => 'Đã bốc xong', 'sub' => 'Đơn bán hàng'],
                                                in_array($st, ['Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng']) => ['bg' => 'bg-teal-50 text-teal-800 border-teal-300', 'icon' => '🛡️', 'label' => 'Đang kiểm hàng', 'sub' => 'Đơn bán hàng'],
                                                $st === 'Đang vận chuyển' => ['bg' => 'bg-cyan-50 text-cyan-800 border-cyan-300', 'icon' => '🚚', 'label' => 'Đang vận chuyển', 'sub' => 'Đơn bán hàng'],
                                                $st === 'Đã gộp đơn' => ['bg' => 'bg-indigo-50 text-indigo-800 border-indigo-300', 'icon' => '🔀', 'label' => 'Đã gộp đơn', 'sub' => 'Đã gộp vào đơn khác'],
                                                in_array($st, ['Đã giao', 'Đã quyết toán', 'Giao thành công']) => ['bg' => 'bg-emerald-50 text-emerald-800 border-emerald-300', 'icon' => '✅', 'label' => 'Đã giao', 'sub' => 'Đơn bán hàng'],
                                                in_array($st, ['Hủy', 'Đã hủy']) => ['bg' => 'bg-rose-50 text-rose-800 border-rose-300', 'icon' => '❌', 'label' => 'Đã hủy', 'sub' => 'Đơn hủy'],
                                                default => ['bg' => 'bg-slate-100 text-slate-800 border-slate-300', 'icon' => '📄', 'label' => $st, 'sub' => 'Đơn bán hàng']
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-black border shadow-2xs {{ $badgeConfig['bg'] }}">
                                            <span>{{ $badgeConfig['icon'] }}</span>
                                            <span>{{ $badgeConfig['label'] }}</span>
                                        </span>
                                        <div class="text-[10px] text-slate-400 font-medium whitespace-nowrap pl-0.5">
                                            {{ $badgeConfig['sub'] }}
                                        </div>
                                        @if($order->shortage_reported)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-600 text-white font-black text-[9px] tracking-wide shadow-sm mt-0.5 animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation text-[8px]"></i>
                                                KHO BÁO THIẾU
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Quick Action Pill button: "Chốt đơn" khi Báo giá / "Soạn hàng" khi Chốt đơn / "Bổ sung" khi Soạn hàng -->
                                    <div class="flex items-center gap-1 shrink-0">
                                        @if(in_array($st, ['Báo giá', 'Bản nháp', 'Chờ duyệt', 'Chờ kế toán duyệt']))
                                            <button type="button" 
                                                    @click="confirmLockOrder('{{ $order->id }}', '{{ $order->order_code }}')"
                                                    class="px-2.5 py-1 rounded-xl border border-blue-600 text-blue-700 bg-blue-50 hover:bg-blue-100 font-black text-[11px] flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs active:scale-95" 
                                                    title="Chốt đơn hàng & chuyển sang bước soạn hàng">
                                                <i class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                                                <span>Chốt đơn</span>
                                            </button>
                                        @elseif($isWaitingStock)
                                            <button type="button" 
                                                    disabled
                                                    class="px-2.5 py-1 rounded-xl border border-slate-200 text-slate-400 bg-slate-100 font-bold text-[11px] flex items-center gap-1.5 opacity-40 cursor-not-allowed select-none shadow-none pointer-events-none" 
                                                    title="Kho chưa đủ hàng tồn. Không thể soạn hàng!">
                                                <i class="fa-solid fa-boxes-stacked text-slate-400 text-xs"></i>
                                                <span>Soạn hàng</span>
                                            </button>
                                        @elseif(in_array($st, ['Chốt đơn', 'Đã duyệt', 'Giao sau', 'Đơn giao sau', 'Nợ hàng']))
                                            <button type="button" 
                                                    @click="openPickingModal({
                                                        id: '{{ $order->id }}',
                                                        code: '{{ $order->order_code }}',
                                                        customer: '{{ addslashes($customerName) }}',
                                                        warehouse: '{{ addslashes($whName) }}',
                                                        total: '{{ $totalAmtFormatted }}',
                                                        picking_date: '{{ $order->picking_date ? \Carbon\Carbon::parse($order->picking_date)->format('Y-m-d') : date('Y-m-d') }}'
                                                    })"
                                                    class="px-2.5 py-1 rounded-xl border border-purple-500 text-purple-900 bg-purple-50 hover:bg-purple-100 font-black text-[11px] flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs active:scale-95" 
                                                    title="Mở màn hình chọn ngày & bắt đầu soạn hàng xuất kho">
                                                <i class="fa-solid fa-boxes-stacked text-purple-600 text-xs"></i>
                                                <span>Soạn hàng</span>
                                            </button>
                                        @elseif(in_array($st, ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong']))
                                            <a href="{{ url('/orders/' . $order->id . '/edit') }}" 
                                               class="px-2.5 py-1 rounded-xl border border-sky-500 text-sky-700 bg-sky-50 hover:bg-sky-100 font-extrabold text-[11px] flex items-center gap-1 cursor-pointer transition-all shadow-2xs active:scale-95" 
                                               title="Bổ sung thêm hàng hóa / chỉnh sửa chi tiết đơn">
                                                <i class="fa-solid fa-boxes-packing text-sky-600 text-xs"></i>
                                                <span>Bổ sung</span>
                                            </a>
                                        @else
                                            <a href="{{ url('/orders/' . $order->id . '/edit') }}" 
                                               class="px-2.5 py-1 rounded-xl border border-slate-300 text-slate-700 bg-slate-50 hover:bg-slate-100 font-bold text-[11px] flex items-center gap-1 cursor-pointer transition-all shadow-2xs active:scale-95" 
                                               title="Xem chi tiết đơn hàng">
                                                <i class="fa-regular fa-eye text-slate-500 text-xs"></i>
                                                <span>Chi tiết</span>
                                            </a>
                                        @endif

                                        <button type="button" 
                                                @click.stop="openActionMenu({
                                                    id: '{{ $order->id }}',
                                                    code: '{{ $order->order_code }}',
                                                    customer: '{{ addslashes($customerName) }}',
                                                    phone: '{{ addslashes($order->customer_phone ?? '') }}',
                                                    address: '{{ addslashes($customerAddr) }}',
                                                    total: {{ (float)$order->total_amount }},
                                                    status: '{{ $st }}',
                                                    has_shortage_warning: {{ $order->has_shortage_warning ? 'true' : 'false' }},
                                                    picking_date: '{{ $order->picking_date ? \Carbon\Carbon::parse($order->picking_date)->format('Y-m-d') : date('Y-m-d') }}',
                                                    warehouse: '{{ addslashes($whName) }}',
                                                    driver: '{{ addslashes($order->driver?->name ?? 'Chưa phân công') }}',
                                                    vehicle: '{{ $order->assigned_vehicle_plate ?? 'Chưa có xe' }}',
                                                    date: '{{ $createdDate }}'
                                                }, $event)" 
                                                :class="activeActionOrder && activeActionOrder.id === '{{ $order->id }}' ? 'bg-sky-100 text-sky-700 ring-2 ring-sky-300' : 'hover:bg-slate-200 text-slate-600'"
                                                class="p-1 rounded-lg cursor-pointer text-xs font-black tracking-widest px-1.5 transition-all" 
                                                title="Thao tác chứng từ">
                                            •••
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400 font-medium">
                                Không có chứng từ nào khớp điều kiện tìm kiếm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Floating Action Menu (Matching user specification exactly) -->
    <div x-show="activeActionOrder" 
         @click.outside="closeActionMenu()" 
         x-cloak 
         class="fixed z-[9999] w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 py-1.5 text-xs select-none ring-1 ring-black/5"
         :style="actionDropUp 
            ? `bottom: ${actionMenuY}px; left: ${actionMenuX}px;` 
            : `top: ${actionMenuY}px; left: ${actionMenuX}px;`">
        <template x-if="activeActionOrder">
            <div class="space-y-0.5">
                <!-- 1. Xem đơn hàng (Chỉ đọc) -->
                <a :href="'/orders/' + (activeActionOrder?.id || '') + '/edit?readonly=1'" @click="closeActionMenu()" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors">
                    <i class="fa-regular fa-eye text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xem đơn hàng (Chỉ đọc)</span>
                </a>

                <!-- 2. In -->
                <button type="button" @click="openPrintModal(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-solid fa-print text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">In phiếu</span>
                </button>

                <!-- 2.4 Chốt đơn hàng (Nếu đang Báo giá) -->
                <template x-if="['Báo giá', 'Bản nháp', 'Chờ duyệt', 'Chờ kế toán duyệt'].includes(activeActionOrder?.status)">
                    <button type="button" @click="confirmLockOrder(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-blue-50 flex items-center gap-3 text-blue-700 transition-colors font-bold cursor-pointer">
                        <i class="fa-solid fa-circle-check text-blue-600 w-5 text-center text-sm"></i>
                        <span>Chốt đơn hàng</span>
                    </button>
                </template>

                <!-- 2.5 Soạn hàng (Nếu đã chốt đơn) -->
                <template x-if="['Chốt đơn', 'Đã duyệt'].includes(activeActionOrder?.status) && !activeActionOrder?.has_shortage_warning">
                    <button type="button" @click="openPickingModal(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-purple-50 flex items-center gap-3 text-purple-900 transition-colors font-bold cursor-pointer">
                        <i class="fa-solid fa-boxes-stacked text-purple-600 w-5 text-center text-sm"></i>
                        <span>Soạn hàng xuất kho</span>
                    </button>
                </template>
                <template x-if="activeActionOrder?.has_shortage_warning || ['Chờ hàng', 'Chờ hàng về'].includes(activeActionOrder?.status)">
                    <div class="w-full text-left px-3.5 py-2 flex items-center justify-between text-slate-400 opacity-50 cursor-not-allowed select-none bg-slate-50/50" title="Kho chưa đủ hàng tồn khả dụng, không thể soạn hàng">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-boxes-stacked text-slate-400 w-5 text-center text-sm"></i>
                            <span class="font-medium line-through">Soạn hàng xuất kho</span>
                        </div>
                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Chờ hàng</span>
                    </div>
                </template>

                <!-- 3. Bổ sung sản phẩm / Sửa -->
                <a :href="'/orders/' + (activeActionOrder?.id || '') + '/edit'" @click="closeActionMenu()" class="w-full text-left px-3.5 py-2 hover:bg-sky-50 flex items-center gap-3 text-sky-700 transition-colors">
                    <i class="fa-solid fa-boxes-packing text-sky-600 w-5 text-center text-sm"></i>
                    <span class="font-bold">Bổ sung sản phẩm / Sửa</span>
                </a>

                <!-- Divider -->
                <div class="border-t border-slate-100 my-1"></div>

                <!-- 4. Theo dõi đơn hàng -->
                <button type="button" @click="openTrackingModal(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-800 transition-colors cursor-pointer">
                    <i class="fa-solid fa-rotate-left text-sky-600 w-5 text-center text-sm"></i>
                    <span class="font-bold">Theo dõi đơn hàng</span>
                </button>

                <!-- 5. Xuất Excel -->
                <button type="button" @click="exportOrderExcel(activeActionOrder, 'standard')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-regular fa-file-lines text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xuất Excel</span>
                </button>

                <!-- 6. Xuất Excel (tên hoá đơn) -->
                <button type="button" @click="exportOrderExcel(activeActionOrder, 'invoice')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-regular fa-file-lines text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xuất Excel (tên hoá đơn)</span>
                </button>

                <!-- 7. Xuất PDF (tên hoá đơn) -->
                <button type="button" @click="exportOrderPdf(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-solid fa-file-arrow-down text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xuất PDF (tên hoá đơn)</span>
                </button>

                <!-- 8. Xe & người giao hàng -->
                <a :href="'/delivery-dispatch?search=' + encodeURIComponent(activeActionOrder?.code || '')" @click="closeActionMenu()" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors">
                    <i class="fa-solid fa-truck text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Xe & người giao hàng</span>
                </a>

                <!-- 9. Nhập thanh toán -->
                <button type="button" @click="openPaymentModal(activeActionOrder)" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-3 text-slate-700 transition-colors cursor-pointer">
                    <i class="fa-regular fa-credit-card text-slate-500 w-5 text-center text-sm"></i>
                    <span class="font-medium">Nhập thanh toán</span>
                </button>
            </div>
        </template>
    </div>

    <!-- Modal: Theo dõi đơn hàng (Tracking lifecycle) -->
    <div x-show="showTrackingModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.outside="showTrackingModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm">Theo dõi tiến trình đơn hàng</h3>
                        <p class="text-xs text-sky-600 font-mono font-bold" x-text="activeTrackingOrder?.code"></p>
                    </div>
                </div>
                <button type="button" @click="showTrackingModal = false" class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Lifecycle Stepper -->
            <div class="py-2 space-y-3">
                <template x-for="(st, sIdx) in trackingSteps" :key="st.name">
                    <div class="flex items-start gap-3 relative">
                        <div class="flex flex-col items-center">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                                 :class="isStepCompleted(st.name) ? 'bg-emerald-500 text-white shadow-xs' : (isStepCurrent(st.name) ? 'bg-sky-600 text-white ring-4 ring-sky-100' : 'bg-slate-100 text-slate-400')">
                                <template x-if="isStepCompleted(st.name)">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                </template>
                                <template x-if="!isStepCompleted(st.name)">
                                    <span x-text="sIdx + 1"></span>
                                </template>
                            </div>
                            <template x-if="sIdx < trackingSteps.length - 1">
                                <div class="w-0.5 h-6 my-0.5" :class="isStepCompleted(trackingSteps[sIdx + 1].name) ? 'bg-emerald-400' : 'bg-slate-200'"></div>
                            </template>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold" :class="isStepCurrent(st.name) ? 'text-sky-700 font-black' : (isStepCompleted(st.name) ? 'text-slate-800' : 'text-slate-400')" x-text="st.label"></span>
                                <span class="text-[10px]" :class="isStepCurrent(st.name) ? 'text-sky-600 font-bold' : 'text-slate-400'" x-text="isStepCurrent(st.name) ? 'Đang thực hiện' : (isStepCompleted(st.name) ? 'Đã hoàn tất' : 'Chưa đến')"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5" x-text="st.desc"></p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Order info summary -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs space-y-1.5">
                <div class="flex justify-between"><span class="text-slate-500">Khách hàng:</span> <span class="font-bold text-slate-800" x-text="activeTrackingOrder?.customer"></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Kho xuất hàng:</span> <span class="font-bold text-slate-800" x-text="activeTrackingOrder?.warehouse"></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Lái xe & Phương tiện:</span> <span class="font-bold text-sky-700" x-text="(activeTrackingOrder?.driver || 'Chưa phân công') + ' (' + (activeTrackingOrder?.vehicle || 'Chưa xếp xe') + ')'"></span></div>
                <div class="flex justify-between border-t border-slate-200 pt-1.5"><span class="text-slate-500">Tổng giá trị đơn:</span> <span class="font-black text-slate-900" x-text="formatMoney(activeTrackingOrder?.total)"></span></div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <a :href="'/orders/' + activeTrackingOrder?.id + '/edit'" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                    Mở chi tiết đơn hàng
                </a>
            </div>
        </div>
    </div>

    <!-- Modal: Nhập thanh toán (Quick Payment) -->
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.outside="showPaymentModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i class="fa-regular fa-credit-card"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm">Nhập thanh toán đơn hàng</h3>
                        <p class="text-xs text-emerald-600 font-mono font-bold" x-text="activePaymentOrder?.code"></p>
                    </div>
                </div>
                <button type="button" @click="showPaymentModal = false" class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200/60 flex items-center justify-between">
                    <div>
                        <span class="text-emerald-800 font-bold block" x-text="activePaymentOrder?.customer"></span>
                        <span class="text-[11px] text-emerald-600" x-text="'Mã: ' + activePaymentOrder?.code"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Cần thanh toán</span>
                        <span class="text-sm font-black text-emerald-700" x-text="formatMoney(activePaymentOrder?.total)"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Phương thức thanh toán <span class="text-rose-500">*</span></label>
                    <select x-model="paymentForm.method" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none font-semibold">
                        <option value="Tiền mặt">💵 Tiền mặt (Thu trực tiếp)</option>
                        <option value="Chuyển khoản">💳 Chuyển khoản ngân hàng / VietQR</option>
                        <option value="Ghi nợ đại lý">📑 Ghi nợ vào công nợ đại lý</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Số tiền thanh toán (VNĐ) <span class="text-rose-500">*</span></label>
                    <input type="text" inputmode="numeric" x-money="paymentForm.amount" placeholder="0" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none font-bold text-slate-900">
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Ghi chú thanh toán</label>
                    <textarea x-model="paymentForm.notes" rows="2" placeholder="Nhập ghi chú hoặc mã giao dịch ngân hàng..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showPaymentModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs cursor-pointer">Hủy</button>
                <button type="button" @click="submitPayment()" :disabled="paymentSubmitting" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs cursor-pointer shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-check" x-show="!paymentSubmitting"></i>
                    <i class="fa-solid fa-spinner fa-spin" x-show="paymentSubmitting"></i>
                    <span>Xác nhận thu tiền</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- Modal: Xem thông tin đơn hàng & In phiếu (Theo hình mẫu) -->
    <!-- ======================================================== -->
    <div x-show="showPrintModal" x-cloak class="fixed inset-0 z-[10000] flex items-center justify-center p-2 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div @click.outside="showPrintModal = false" class="bg-white rounded-lg shadow-2xl border border-slate-300 w-full max-w-5xl my-auto overflow-hidden text-[12px] sm:text-[13px] text-slate-800 leading-normal font-sans">
            
            <!-- Modal Header: Blue #1976d2 -->
            <div class="bg-[#1976d2] px-4 py-2.5 text-white flex items-center justify-between select-none">
                <h3 class="font-bold text-sm sm:text-base tracking-wide">Xem thông tin đơn hàng</h3>
                <button type="button" @click="showPrintModal = false" class="text-white/80 hover:text-white cursor-pointer text-lg font-bold leading-none px-1">
                    ✕
                </button>
            </div>

            <!-- Modal Loading State -->
            <div x-show="printLoading" class="py-16 text-center text-slate-500 space-y-3">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-[#1976d2]"></i>
                <div class="font-semibold text-xs">Đang tải chi tiết đơn hàng...</div>
            </div>

            <!-- Modal Content -->
            <div x-show="!printLoading && printOrderData" class="p-3 sm:p-5 space-y-3">
                <!-- Top Info Section -->
                <div class="relative pb-1">
                    <!-- Meta Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-2 gap-x-4">
                        <div class="sm:col-span-3 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Số hóa đơn:</span>
                            <span class="font-bold text-slate-900" x-text="printOrderData?.order_code"></span>
                        </div>
                        <div class="sm:col-span-3 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Ngày lập:</span>
                            <span x-text="printOrderData?.created_date"></span>
                        </div>
                        <div class="sm:col-span-6 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Khách hàng:</span>
                            <span class="truncate font-semibold text-slate-900" x-text="(printOrderData?.customer_name || printOrderData?.customer_display || '').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')"></span>
                        </div>

                        <div class="sm:col-span-3 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Kho xuất:</span>
                            <span class="uppercase font-semibold" x-text="printOrderData?.warehouse"></span>
                        </div>
                        <div class="sm:col-span-9 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900 whitespace-nowrap">Diễn giải:</span>
                            <span class="text-slate-600" x-text="printOrderData?.notes || ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Products Table Section Header -->
                <div class="bg-[#dfdfdf] px-3 py-1 font-bold text-slate-900 text-xs sm:text-[13px] border border-slate-300">
                    Danh sách sản phẩm
                </div>

                <!-- Products Table -->
                <div class="overflow-x-auto border border-slate-300">
                    <table class="w-full border-collapse text-[11px] sm:text-xs">
                        <thead>
                            <tr class="bg-[#f0f0f0] font-bold text-slate-800 text-center border-b border-slate-300">
                                <th class="border border-slate-300 py-1.5 px-2 w-10 text-center">TT</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-24 text-left">Mã SP</th>
                                <th class="border border-slate-300 py-1.5 px-2 text-left">Tên sản phẩm</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-16 text-center">ĐVT</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-14 text-right">SL</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-24 text-right">Đơn giá</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-16 text-right">C.khấu</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-24 text-right">Thành tiền</th>
                                <th class="border border-slate-300 py-1.5 px-2 w-16 text-center">Loại giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(it, idx) in (printOrderData?.items || [])" :key="it.id || idx">
                                <tr class="bg-white hover:bg-slate-50 transition-colors border-b border-slate-300 text-slate-800">
                                    <td class="border border-slate-300 py-1.5 px-2 text-center font-medium" x-text="it.tt || (idx + 1)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-left font-mono text-slate-700" x-text="it.sku || ''"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-left">
                                        <div class="font-bold text-slate-900" x-text="it.name"></div>
                                        <div class="text-[11px] text-sky-700 font-medium" x-text="it.tax_name" x-show="it.tax_name && it.tax_name !== it.name"></div>
                                    </td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-center" x-text="it.unit"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-right font-semibold" x-text="formatNumber(it.sold_quantity ?? it.quantity)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-right" x-text="formatNumber(it.unit_price)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-right" x-text="getDiscountDisplay(it)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-right font-bold text-slate-900" x-text="formatNumber(it.total || it.amount)"></td>
                                    <td class="border border-slate-300 py-1.5 px-2 text-center font-bold text-slate-700" x-text="it.price_tier_code || '---'"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-white font-bold text-slate-900">
                            <!-- Dòng Tổng -->
                            <tr class="border-b border-slate-300">
                                <td colspan="6" class="border border-slate-300 py-1.5 px-2"></td>
                                <td class="border border-slate-300 py-1.5 px-2 text-right font-bold">Tổng</td>
                                <td class="border border-slate-300 py-1.5 px-2 text-right font-black" x-text="formatNumber(printOrderData?.final_total || printOrderData?.total_items_amount)"></td>
                                <td class="border border-slate-300 py-1.5 px-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Under-table Information Lines -->
                <div class="pt-1 space-y-1.5 text-xs sm:text-[12.5px] text-slate-800">
                    <div>
                        <span class="font-bold text-slate-900">Bằng chữ:</span>
                        <strong class="uppercase text-slate-900 ml-1" x-text="printOrderData?.final_total_words"></strong>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-1 gap-x-4 pt-1">
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Tiền thanh toán:</span>
                            <span class="font-bold text-slate-900" x-text="formatNumber(printOrderData?.final_total)"></span>
                        </div>
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Hình thức:</span>
                            <span x-text="printOrderData?.payment_method || '---'"></span>
                        </div>
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Tình trạng:</span>
                            <span x-text="printOrderData?.delivery_status || printOrderData?.status || '---'"></span>
                        </div>

                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Nhân viên giao:</span>
                            <span x-text="printOrderData?.delivery_driver || '---'"></span>
                        </div>
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Xe giao:</span>
                            <span x-text="printOrderData?.delivery_vehicle || '---'"></span>
                        </div>
                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Ngày giao:</span>
                            <span x-text="printOrderData?.delivery_date || '---'"></span>
                        </div>

                        <div class="sm:col-span-12 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">ĐC giao hàng:</span>
                            <span x-text="printOrderData?.customer_address || '---'"></span>
                        </div>

                        <div class="sm:col-span-4 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Người lập:</span>
                            <span x-text="printOrderData?.creator_name || '---'"></span>
                        </div>
                        <div class="sm:col-span-8 flex items-baseline gap-1.5">
                            <span class="font-bold text-slate-900">Người sửa cuối:</span>
                            <span x-text="printOrderData?.updater_name || printOrderData?.creator_name || '---'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with All Buttons (Matching screenshot) -->
            <div class="bg-[#f5f5f5] border-t border-slate-300 px-3 sm:px-4 py-2.5 flex flex-wrap items-center justify-between gap-2 select-none">
                <!-- Left Buttons: Excel Exports -->
                <div class="flex items-center gap-2">
                    <button type="button" @click="exportOrderExcelPrint(printOrderData, 'invoice')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-[11px]"></i>
                        <span>Xuất excel tên HĐ</span>
                    </button>
                    <button type="button" @click="exportOrderExcelPrint(printOrderData, 'standard')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-[11px]"></i>
                        <span>Xuất excel</span>
                    </button>
                </div>

                <!-- Middle Print Options (Matching user highlighted buttons) -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button type="button" @click="executePrintOrder('no_price')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In không chứa thông tin tiêu đề công ty">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In không TĐ</span>
                    </button>
                    <button type="button" @click="executePrintOrder('no_price_with_header')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In có thông tin công ty, không hiển thị giá">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In không giá</span>
                    </button>
                    <button type="button" @click="executePrintOrder('tax_name')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In có thông tin công ty theo tên hóa đơn GTGT">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In theo tên HĐ</span>
                    </button>
                    <button type="button" @click="executePrintOrder('standard')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In đơn hàng chuẩn có thông tin công ty">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In đơn hàng</span>
                    </button>
                </div>

                <!-- Right Button: Close -->
                <div>
                    <button type="button" @click="showPrintModal = false"
                            class="px-5 py-1.5 bg-[#eeeeee] hover:bg-slate-200 text-slate-700 border border-slate-300 rounded font-semibold text-xs transition-colors cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: IN PHIẾU GỘP & IN HÀNG LOẠT (TỔNG HỢP SẢN PHẨM) -->
    <!-- ============================================================== -->
    <div x-show="showBulkPrintModal" x-cloak class="fixed inset-0 z-[10001] flex items-center justify-center p-2 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div @click.outside="showBulkPrintModal = false" class="bg-white rounded-xl shadow-2xl border border-slate-300 w-full max-w-6xl my-auto overflow-hidden text-[12px] sm:text-[13px] text-slate-800 leading-normal font-sans">
            
            <!-- Modal Header: Deep Navy / Slate -->
            <div class="bg-slate-900 px-4 py-3 text-white flex flex-wrap items-center justify-between gap-3 select-none border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base tracking-wide flex items-center gap-2">
                            <span>In phiếu đơn hàng đã chọn</span>
                            <span class="px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 text-xs font-semibold border border-sky-400/30">
                                <span x-text="selectedIds.length"></span> đơn hàng
                            </span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Chọn in phiếu gộp tổng hợp sản phẩm hoặc in hàng loạt từng đơn</p>
                    </div>
                </div>

                <!-- Tabs Center / Switcher -->
                <div class="flex items-center bg-slate-800 p-1 rounded-xl border border-slate-700 text-xs font-bold">
                    <button type="button" @click="bulkPrintTab = 'consolidated'" 
                            :class="bulkPrintTab === 'consolidated' ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'"
                            class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-layer-group text-xs"></i>
                        <span>1. Phiếu gộp (Tổng hợp SP)</span>
                    </button>
                    <button type="button" @click="bulkPrintTab = 'batch'" 
                            :class="bulkPrintTab === 'batch' ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'"
                            class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-print text-xs"></i>
                        <span>2. In hàng loạt (Từng đơn)</span>
                    </button>
                </div>

                <!-- Close Button -->
                <button type="button" @click="showBulkPrintModal = false" class="text-white/70 hover:text-white cursor-pointer text-xl font-bold leading-none p-1">
                    ✕
                </button>
            </div>

            <!-- Loading State -->
            <div x-show="bulkPrintLoading" class="py-20 text-center text-slate-500 space-y-3">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-sky-600"></i>
                <div class="font-semibold text-xs sm:text-sm text-slate-700">Đang tổng hợp dữ liệu sản phẩm từ các đơn hàng đã chọn...</div>
                <div class="text-[11px] text-slate-400">Hệ thống đang đối soát SKU, giá bán và phân bổ đơn hàng</div>
            </div>

            <!-- Content Area -->
            <div x-show="!bulkPrintLoading && bulkPrintData" class="p-3 sm:p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                
                <!-- ================= TAB 1: PHIẾU GỘP (TỔNG HỢP SẢN PHẨM) ================= -->
                <div x-show="bulkPrintTab === 'consolidated'" class="space-y-4">
                    
                    <!-- KPI Cards Summary -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-sky-50 border border-sky-200 rounded-xl p-3">
                            <div class="text-[11px] text-sky-700 font-semibold uppercase tracking-wider">Số đơn gộp</div>
                            <div class="text-xl sm:text-2xl font-black text-sky-900 mt-1">
                                <span x-text="bulkPrintData?.consolidated?.total_orders"></span> <span class="text-xs font-normal text-sky-600">đơn</span>
                            </div>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3">
                            <div class="text-[11px] text-indigo-700 font-semibold uppercase tracking-wider">Mặt hàng cần xuất</div>
                            <div class="text-xl sm:text-2xl font-black text-indigo-900 mt-1">
                                <span x-text="bulkPrintData?.consolidated?.total_products_count"></span> <span class="text-xs font-normal text-indigo-600">loại SP</span>
                            </div>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                            <div class="text-[11px] text-amber-700 font-semibold uppercase tracking-wider">Tổng số lượng xuất</div>
                            <div class="text-xl sm:text-2xl font-black text-amber-900 mt-1">
                                <span x-text="formatNumber(bulkPrintData?.consolidated?.total_quantity)"></span> <span class="text-xs font-normal text-amber-600">sp</span>
                            </div>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                            <div class="text-[11px] text-emerald-700 font-semibold uppercase tracking-wider">Tổng tiền hàng</div>
                            <div class="text-xl sm:text-2xl font-black text-emerald-900 mt-1">
                                <span x-text="formatNumber(bulkPrintData?.consolidated?.total_amount)"></span> <span class="text-xs font-normal text-emerald-600">đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information Box -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 space-y-1.5">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-1.5 gap-x-4">
                            <div class="sm:col-span-3">
                                <span class="font-bold text-slate-900">Mã phiếu gộp:</span>
                                <span class="font-mono font-bold text-sky-700" x-text="bulkPrintData?.consolidated?.batch_code"></span>
                            </div>
                            <div class="sm:col-span-3">
                                <span class="font-bold text-slate-900">Kho xuất:</span>
                                <span class="uppercase font-semibold text-slate-800" x-text="bulkPrintData?.consolidated?.warehouse_display"></span>
                            </div>
                            <div class="sm:col-span-6">
                                <span class="font-bold text-slate-900">Ngày lập:</span>
                                <span x-text="bulkPrintData?.consolidated?.created_date"></span>
                            </div>
                            <div class="sm:col-span-12">
                                <span class="font-bold text-slate-900">Danh sách đơn gộp:</span>
                                <span class="font-mono text-slate-700 font-semibold" x-text="bulkPrintData?.consolidated?.order_codes_display"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Consolidated Products Table Header -->
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-slate-900 text-xs sm:text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-600"></span>
                            <span>Bảng tổng hợp toàn bộ sản phẩm cần xuất</span>
                        </div>
                        <span class="text-xs text-slate-500 italic">* Đã gộp các sản phẩm trùng lặp và tính tổng số lượng</span>
                    </div>

                    <!-- Table of Consolidated Products -->
                    <div class="overflow-x-auto border border-slate-300 rounded-lg">
                        <table class="w-full border-collapse text-[11px] sm:text-xs">
                            <thead>
                                <tr class="bg-slate-100 font-bold text-slate-800 text-center border-b border-slate-300">
                                    <th class="border border-slate-300 py-2 px-2 w-10 text-center">TT</th>
                                    <th class="border border-slate-300 py-2 px-2 w-24 text-left">Mã SP</th>
                                    <th class="border border-slate-300 py-2 px-2 text-left">Tên sản phẩm</th>
                                    <th class="border border-slate-300 py-2 px-2 w-16 text-center">ĐVT</th>
                                    <th class="border border-slate-300 py-2 px-2 w-16 text-right">SL Bán</th>
                                    <th class="border border-slate-300 py-2 px-2 w-16 text-right">SL Tặng</th>
                                    <th class="border border-slate-300 py-2 px-2 w-20 text-right bg-amber-50/70 text-amber-900 font-black">Tổng Xuất</th>
                                    <th class="border border-slate-300 py-2 px-2 w-24 text-right">Đơn giá</th>
                                    <th class="border border-slate-300 py-2 px-2 w-28 text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(p, idx) in (bulkPrintData?.consolidated?.products || [])" :key="p.sku || p.name || idx">
                                    <tr class="bg-white hover:bg-slate-50 transition-colors border-b border-slate-300 text-slate-800">
                                        <td class="border border-slate-300 py-1.5 px-2 text-center font-medium" x-text="p.tt || (idx + 1)"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-left font-mono text-slate-700 font-semibold" x-text="p.sku || ''"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-left">
                                            <div class="font-bold text-slate-900" x-text="p.name"></div>
                                            <div class="text-[11px] text-sky-700 font-medium" x-text="p.tax_name" x-show="p.tax_name && p.tax_name !== p.name"></div>
                                        </td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-center" x-text="p.unit"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-right" x-text="formatNumber(p.sold_quantity)"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-right text-emerald-700 font-semibold" x-text="formatNumber(p.gift_quantity)"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-right font-black text-amber-900 bg-amber-50/50 text-xs sm:text-[13px]" x-text="formatNumber(p.total_quantity)"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-right" x-text="formatNumber(p.unit_price)"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-right font-bold text-slate-900" x-text="formatNumber(p.total_amount)"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-400">
                                <tr>
                                    <td colspan="4" class="border border-slate-300 py-2 px-2 text-right font-bold uppercase">Tổng cộng:</td>
                                    <td class="border border-slate-300 py-2 px-2 text-right font-bold" x-text="formatNumber(bulkPrintData?.consolidated?.total_sold_quantity)"></td>
                                    <td class="border border-slate-300 py-2 px-2 text-right font-bold text-emerald-700" x-text="formatNumber(bulkPrintData?.consolidated?.total_gift_quantity)"></td>
                                    <td class="border border-slate-300 py-2 px-2 text-right font-black text-amber-900 bg-amber-100 text-sm" x-text="formatNumber(bulkPrintData?.consolidated?.total_quantity)"></td>
                                    <td class="border border-slate-300 py-2 px-2"></td>
                                    <td class="border border-slate-300 py-2 px-2 text-right font-black text-slate-900 text-sm" x-text="formatNumber(bulkPrintData?.consolidated?.total_amount)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Under table: Words -->
                    <div class="text-xs text-slate-700">
                        <span class="font-bold text-slate-900">Tổng tiền bằng chữ:</span>
                        <em class="font-bold text-slate-900 uppercase ml-1" x-text="bulkPrintData?.consolidated?.total_amount_words"></em>
                    </div>

                    <!-- Collapsible Merged Orders List -->
                    <div x-data="{ showOrderList: false }" class="border border-slate-200 rounded-xl overflow-hidden">
                        <button type="button" @click="showOrderList = !showOrderList" class="w-full bg-slate-100 hover:bg-slate-200/80 px-4 py-2.5 flex items-center justify-between text-xs font-bold text-slate-800 transition-colors cursor-pointer">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-sky-600"></i>
                                <span>Xem danh sách <span x-text="bulkPrintData?.consolidated?.total_orders"></span> đơn hàng được gộp trong phiếu</span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-slate-500 transition-transform duration-150" :class="showOrderList ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="showOrderList" x-cloak class="p-3 border-t border-slate-200 bg-white overflow-x-auto">
                            <table class="w-full border-collapse text-[11px]">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-700 border-b border-slate-200 font-bold text-center">
                                        <th class="py-1.5 px-2 border border-slate-200 w-8">TT</th>
                                        <th class="py-1.5 px-2 border border-slate-200 text-left w-24">Mã đơn</th>
                                        <th class="py-1.5 px-2 border border-slate-200 text-left">Khách hàng</th>
                                        <th class="py-1.5 px-2 border border-slate-200 text-left w-24">Điện thoại</th>
                                        <th class="py-1.5 px-2 border border-slate-200 text-left">Địa chỉ giao hàng</th>
                                        <th class="py-1.5 px-2 border border-slate-200 text-center w-20">Kho</th>
                                        <th class="py-1.5 px-2 border border-slate-200 text-right w-16">SL SP</th>
                                        <th class="py-1.5 px-2 border border-slate-200 text-right w-24">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(o, idx) in (bulkPrintData?.consolidated?.order_list || [])" :key="o.id || idx">
                                        <tr class="hover:bg-slate-50 border-b border-slate-200">
                                            <td class="py-1 px-2 border border-slate-200 text-center" x-text="idx + 1"></td>
                                            <td class="py-1 px-2 border border-slate-200 font-mono font-bold text-sky-700" x-text="o.order_code"></td>
                                            <td class="py-1 px-2 border border-slate-200 font-semibold" x-text="(o.customer_name || '').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')"></td>
                                            <td class="py-1 px-2 border border-slate-200" x-text="o.customer_phone || ''"></td>
                                            <td class="py-1 px-2 border border-slate-200 text-[10.5px]" x-text="o.customer_address || ''"></td>
                                            <td class="py-1 px-2 border border-slate-200 text-center uppercase" x-text="o.warehouse || ''"></td>
                                            <td class="py-1 px-2 border border-slate-200 text-right font-bold" x-text="formatNumber(o.total_quantity)"></td>
                                            <td class="py-1 px-2 border border-slate-200 text-right font-bold text-slate-900" x-text="formatNumber(o.final_total)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 2: IN HÀNG LOẠT (TỪNG ĐƠN RIÊNG) ================= -->
                <div x-show="bulkPrintTab === 'batch'" class="space-y-4">
                    <div class="bg-sky-50 border border-sky-200 rounded-xl p-3 text-sky-900 text-xs flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-info text-sky-600 text-sm mt-0.5 shrink-0"></i>
                        <div>
                            <div class="font-bold text-sm">Chế độ in hàng loạt (<span x-text="bulkPrintData?.orders?.length || 0"></span> đơn hàng)</div>
                            <div class="mt-0.5 text-sky-800">Mỗi đơn hàng sẽ được tạo thành một phiếu xuất/bán hàng độc lập, tự động ngắt trang (A4) khi gửi lệnh in tới máy in.</div>
                        </div>
                    </div>

                    <!-- List of Orders ready to batch print -->
                    <div class="border border-slate-300 rounded-lg overflow-hidden">
                        <table class="w-full border-collapse text-[11px] sm:text-xs">
                            <thead>
                                <tr class="bg-slate-100 font-bold text-slate-800 text-center border-b border-slate-300">
                                    <th class="border border-slate-300 py-2 px-2 w-10 text-center">TT</th>
                                    <th class="border border-slate-300 py-2 px-2 w-28 text-left">Mã đơn hàng</th>
                                    <th class="border border-slate-300 py-2 px-2 text-left">Khách hàng</th>
                                    <th class="border border-slate-300 py-2 px-2 w-28 text-left">Điện thoại</th>
                                    <th class="border border-slate-300 py-2 px-2 text-left">Kho xuất</th>
                                    <th class="border border-slate-300 py-2 px-2 w-20 text-right">Số SP</th>
                                    <th class="border border-slate-300 py-2 px-2 w-28 text-right">Tổng tiền</th>
                                    <th class="border border-slate-300 py-2 px-2 w-24 text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(o, idx) in (bulkPrintData?.orders || [])" :key="o.id || idx">
                                    <tr class="bg-white hover:bg-slate-50 transition-colors border-b border-slate-300 text-slate-800">
                                        <td class="border border-slate-300 py-1.5 px-2 text-center font-medium" x-text="idx + 1"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-left font-mono font-bold text-sky-700" x-text="o.order_code"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-left font-semibold">
                                            <span x-text="(o.customer_name || '').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')"></span>
                                        </td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-left" x-text="o.customer_phone || ''"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-left uppercase text-slate-700" x-text="o.warehouse || ''"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-right font-bold" x-text="o.items?.length || 0"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-right font-bold text-slate-900" x-text="formatNumber(o.final_total)"></td>
                                        <td class="border border-slate-300 py-1.5 px-2 text-center">
                                            <button type="button" @click="openPrintModal(o.id)" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition-colors cursor-pointer" title="In riêng đơn này">
                                                <i class="fa-solid fa-print mr-1"></i> In riêng
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer: Action Buttons -->
            <div class="bg-[#f5f5f5] border-t border-slate-300 px-3 sm:px-4 py-3 flex flex-wrap items-center justify-between gap-2 select-none">
                
                <!-- If on Tab 1: Consolidated -->
                <div x-show="bulkPrintTab === 'consolidated'" class="flex items-center gap-2">
                    <button type="button" @click="exportConsolidatedExcel()"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-[11px]"></i>
                        <span>Xuất Excel phiếu gộp</span>
                    </button>
                </div>

                <div x-show="bulkPrintTab === 'consolidated'" class="flex items-center gap-1.5 sm:gap-2">
                    <button type="button" @click="executePrintConsolidated('no_price')"
                            class="px-3.5 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In phiếu soạn kho không hiển thị giá">
                        <i class="fa-solid fa-boxes-packing text-[11px]"></i>
                        <span>In soạn kho (Không giá)</span>
                    </button>
                    <button type="button" @click="executePrintConsolidated('tax_name')"
                            class="px-3.5 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In theo tên hóa đơn GTGT">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In theo tên HĐ</span>
                    </button>
                    <button type="button" @click="executePrintConsolidated('standard')"
                            class="px-4 py-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded font-bold text-xs shadow-sm transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In phiếu gộp đầy đủ thông tin và giá">
                        <i class="fa-solid fa-layer-group text-[11px]"></i>
                        <span>In phiếu gộp chuẩn</span>
                    </button>
                </div>

                <!-- If on Tab 2: Batch -->
                <div x-show="bulkPrintTab === 'batch'" class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">In liên tiếp <strong class="text-slate-900" x-text="bulkPrintData?.orders?.length || 0"></strong> đơn hàng:</span>
                </div>

                <div x-show="bulkPrintTab === 'batch'" class="flex items-center gap-1.5 sm:gap-2">
                    <button type="button" @click="executePrintBatch('no_price')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In hàng loạt không tiêu đề công ty">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In không TĐ</span>
                    </button>
                    <button type="button" @click="executePrintBatch('no_price_with_header')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In hàng loạt không giá">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In không giá</span>
                    </button>
                    <button type="button" @click="executePrintBatch('tax_name')"
                            class="px-3 py-1.5 bg-[#1976d2] hover:bg-[#1565c0] text-white rounded font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In hàng loạt theo tên HĐ">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In theo tên HĐ</span>
                    </button>
                    <button type="button" @click="executePrintBatch('standard')"
                            class="px-4 py-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded font-bold text-xs shadow-sm transition-colors cursor-pointer flex items-center gap-1.5"
                            title="In hàng loạt chuẩn đầy đủ">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>In hàng loạt chuẩn</span>
                    </button>
                </div>

                <!-- Right: Close Button -->
                <div>
                    <button type="button" @click="showBulkPrintModal = false"
                            class="px-5 py-1.5 bg-[#eeeeee] hover:bg-slate-200 text-slate-700 border border-slate-300 rounded font-semibold text-xs transition-colors cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Chọn Ngày Soạn Hàng Khi Bấm Nút Soạn Hàng -->
    <div x-show="showPickingModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.outside="showPickingModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold shadow-md shadow-amber-500/25">
                        <i class="fa-solid fa-boxes-stacked text-base"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Soạn Hàng Xuất Kho</h3>
                        <p class="text-xs text-sky-700 font-mono font-bold" x-text="activePickingOrder?.code"></p>
                    </div>
                </div>
                <button type="button" @click="showPickingModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Khách hàng:</span>
                    <strong class="text-slate-800 text-right truncate max-w-[240px]" x-text="activePickingOrder?.customer"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kho xuất:</span>
                    <span class="text-slate-700 font-medium" x-text="activePickingOrder?.warehouse"></span>
                </div>
                <div class="flex justify-between" x-show="activePickingOrder?.total">
                    <span class="text-slate-500">Tổng tiền:</span>
                    <strong class="text-emerald-700 font-mono font-black" x-text="activePickingOrder?.total"></strong>
                </div>
            </div>

            <!-- Input Ngày Soạn Hàng -->
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-day text-amber-600"></i>
                    <span>Ngày Soạn Hàng <span class="text-rose-500">*</span></span>
                </label>
                <input type="date" x-model="pickingDate" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 focus:border-amber-500 rounded-xl font-bold font-mono text-sm text-slate-900 outline-none transition-all cursor-pointer shadow-2xs">
                <p class="text-[11px] text-slate-400 mt-1">Hệ thống sẽ lưu ngày soạn hàng và mở giao diện bốc hàng theo ngày này.</p>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="showPickingModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmStartPicking()" :disabled="isSubmittingPicking"
                        class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-black text-xs transition-all shadow-md shadow-amber-500/25 flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <template x-if="!isSubmittingPicking">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>Bắt Đầu Soạn Hàng</span>
                            <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                        </span>
                    </template>
                    <template x-if="isSubmittingPicking">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                            <span>Đang chuyển sang Soạn hàng...</span>
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Keyboard Shortcut Bar -->
    <div class="hidden sm:flex items-center justify-between px-4 py-2.5 bg-slate-50 rounded-2xl border border-slate-200/80 text-xs text-slate-500 font-medium overflow-x-auto gap-3">
        <div class="flex items-center gap-4 shrink-0">
            <span class="font-bold text-slate-700 flex items-center gap-1.5">
                <i class="fa-regular fa-keyboard text-sky-600"></i>
                <span>Phím tắt nhanh:</span>
            </span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">F8</kbd> / <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">N</kbd> Lên đơn</span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">F2</kbd> / <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">/</kbd> Tìm kiếm</span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">F4</kbd> Bộ lọc nâng cao</span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">F9</kbd> In phiếu</span>
            <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">1 - 8</kbd> Lọc trạng thái</span>
        </div>
        <button type="button" @click="showShortcutsModal = true" class="text-sky-600 hover:text-sky-800 font-bold shrink-0 cursor-pointer flex items-center gap-1">
            <span>Chi tiết</span>
            <kbd class="px-1.5 py-0.5 bg-white border border-sky-200 rounded font-mono font-bold text-sky-700 shadow-2xs">F1</kbd>
        </button>
    </div>

    <!-- Keyboard Shortcuts Guide Modal -->
    <div x-show="showShortcutsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click.self="showShortcutsModal = false">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-5 py-4 bg-gradient-to-r from-sky-600 to-blue-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-keyboard text-lg"></i>
                    <h3 class="font-black text-sm">Danh Sách Phím Tắt - Quản Lý Đơn Hàng</h3>
                </div>
                <button type="button" @click="showShortcutsModal = false" class="text-white/80 hover:text-white cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                <div>
                    <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-2">Thao tác chức năng</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Tạo đơn hàng / Báo giá mới</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F8</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">N</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Nhảy nhanh vào ô tìm kiếm</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F2</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">/</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Bật / Tắt bộ lọc nâng cao</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F4</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">In phiếu các đơn đã tích chọn</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F9</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">P</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-semibold text-slate-700">Xuất bảng kê xuất hàng kho</span>
                            <div class="flex items-center gap-1">
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-sky-700 shadow-2xs">F10</kbd>
                                <span class="text-slate-400">hoặc</span>
                                <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 shadow-2xs">E</kbd>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-2">Chuyển nhanh tab trạng thái (Phím số 1 &rarr; 8)</h4>
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">1. Tất cả đơn</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">1</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">2. Báo giá</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">2</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">3. Chốt đơn</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">3</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">4. Soạn bốc</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">4</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">5. Kiểm hàng</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">5</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">6. Vận chuyển</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">6</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">7. Đã giao</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">7</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-700">8. Đơn hủy</span>
                            <kbd class="px-2 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-800 shadow-2xs">8</kbd>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-slate-500 text-[11px]">
                    <span>Bấm <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700">Esc</kbd> để đóng bảng trợ giúp</span>
                    <button type="button" @click="showShortcutsModal = false" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition-colors cursor-pointer">
                        Đã hiểu
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    @endif
</div>

<script>
function ordersIndexApp() {
    return {
        companySetting: @json(\App\Models\CompanySetting::getSettings()),
        showMobileFilter: false,
        isAdvancedOpen: false,
        selectedIds: [],
        allOrderIds: @json($allOrderIds),
        tableOrders: @json($tableOrdersData),
        isBulkProcessing: false,
        showShortcutsModal: false,
        activeActionOrder: null,
        actionMenuX: 0,
        actionMenuY: 0,
        actionDropUp: false,

        // Modal states for Print order modal
        showPrintModal: false,
        printOrderData: null,
        printLoading: false,

        // Modal states for Bulk & Consolidated Print
        showBulkPrintModal: false,
        bulkPrintTab: 'consolidated', // 'consolidated' or 'batch'
        bulkPrintLoading: false,
        bulkPrintData: null,

        // Modal states for Tracking, Payment & Picking
        showTrackingModal: false,
        activeTrackingOrder: null,
        showPaymentModal: false,
        activePaymentOrder: null,
        paymentSubmitting: false,
        paymentForm: {
            method: 'Tiền mặt',
            amount: 0,
            notes: ''
        },

        showPickingModal: false,
        activePickingOrder: null,
        pickingDate: '{{ date("Y-m-d") }}',
        isSubmittingPicking: false,

        openPickingModal(order) {
            this.activePickingOrder = order;
            this.pickingDate = order.picking_date || '{{ date("Y-m-d") }}';
            this.isSubmittingPicking = false;
            this.showPickingModal = true;
            this.closeActionMenu();
        },

        async confirmStartPicking() {
            if (!this.activePickingOrder || this.isSubmittingPicking) return;
            this.isSubmittingPicking = true;
            const orderId = this.activePickingOrder.id;
            const pDate = this.pickingDate || '{{ date("Y-m-d") }}';
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            try {
                const res = await fetch('{{ url("/orders/update-status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        status: 'Soạn hàng',
                        picking_date: pDate
                    })
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    this.showPickingModal = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi khi cập nhật ngày soạn hàng');
                }
            } catch (err) {
                console.error('Lỗi chuyển trạng thái đơn hàng sang Soạn hàng:', err);
                alert('Lỗi kết nối máy chủ khi lưu ngày soạn hàng.');
            } finally {
                this.isSubmittingPicking = false;
            }
        },

        trackingSteps: [
            { name: 'Báo giá', label: '1. Báo giá', desc: 'Lập báo giá và gửi cho khách hàng / đại lý' },
            { name: 'Chốt đơn', label: '2. Chốt đơn', desc: 'Khách hàng chốt giá & đơn chuyển sang chuẩn bị hàng' },
            { name: 'Soạn hàng', label: '3. Soạn hàng', desc: 'Thủ kho in lệnh và nhân viên bốc hàng tại kho' },
            { name: 'Kiểm hàng', label: '4. Kiểm tra', desc: 'Kiểm đếm quy cách, đóng gói và sẵn sàng xuất kho' },
            { name: 'Đang vận chuyển', label: '5. Vận chuyển', desc: 'Lái xe nhận hàng và đang giao trên lộ trình' },
            { name: 'Đã giao', label: '6. Đã giao hàng', desc: 'Giao hàng thành công và đối soát công nợ / COD' }
        ],

        openActionMenu(order, e) {
            if (this.activeActionOrder && this.activeActionOrder.id === order.id) {
                this.activeActionOrder = null;
                return;
            }
            const rect = e.currentTarget.getBoundingClientRect();
            this.activeActionOrder = order;

            const menuWidth = 256; // w-64
            this.actionMenuX = Math.max(10, rect.right - menuWidth);

            const menuHeight = 390;
            const spaceBelow = window.innerHeight - rect.bottom;
            if (spaceBelow < menuHeight && rect.top > menuHeight) {
                this.actionDropUp = true;
                this.actionMenuY = window.innerHeight - rect.top + 4;
            } else {
                this.actionDropUp = false;
                this.actionMenuY = Math.min(rect.bottom + 4, window.innerHeight - menuHeight - 10);
            }
        },

        closeActionMenu() {
            this.activeActionOrder = null;
        },

        handleGlobalKeydown(e) {
            const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

            // F1 or ? (outside input): Toggle shortcut guide modal
            if (e.key === 'F1' || (e.key === '?' && !isInput)) {
                e.preventDefault();
                this.showShortcutsModal = !this.showShortcutsModal;
                return;
            }

            // Esc: Close menu or modal or blur input or clear selection
            if (e.key === 'Escape') {
                if (this.activeActionOrder) {
                    this.activeActionOrder = null;
                    return;
                }
                if (this.showShortcutsModal) {
                    this.showShortcutsModal = false;
                    return;
                }
                if (isInput) {
                    document.activeElement.blur();
                    return;
                }
                if (this.selectedIds.length > 0) {
                    this.selectedIds = [];
                    return;
                }
            }

            // F8 or N (outside input): Create new order
            if (e.key === 'F8' || (e.key.toLowerCase() === 'n' && !isInput && !e.ctrlKey && !e.altKey && !e.metaKey)) {
                e.preventDefault();
                window.location.href = '{{ url("/orders/create") }}';
                return;
            }

            // F2 or / (outside input): Focus search input
            if (e.key === 'F2' || (e.key === '/' && !isInput)) {
                e.preventDefault();
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                    this.$refs.searchInput.select();
                }
                return;
            }

            // F4: Toggle Advanced filters
            if (e.key === 'F4') {
                e.preventDefault();
                this.isAdvancedOpen = !this.isAdvancedOpen;
                return;
            }

            // F9 or P (outside input): Print selected
            if (e.key === 'F9' || (e.key.toLowerCase() === 'p' && !isInput && !e.ctrlKey && !e.altKey && !e.metaKey)) {
                e.preventDefault();
                this.printSelected();
                return;
            }

            // F10 or E (outside input): Export manifest
            if (e.key === 'F10' || (e.key.toLowerCase() === 'e' && !isInput && !e.ctrlKey && !e.altKey && !e.metaKey)) {
                e.preventDefault();
                this.exportManifest();
                return;
            }

            // Number keys 1-8 (outside input): Jump to status tabs
            if (!isInput && !e.ctrlKey && !e.altKey && !e.metaKey) {
                const tabs = {
                    '1': '{{ url("/orders") }}',
                    '2': '{{ url("/orders?status=B%C3%A1o+gi%C3%A1") }}',
                    '3': '{{ url("/orders?status=Ch%E1%BB%91t+%C4%91%C6%A1n") }}',
                    '4': '{{ url("/orders?status=So%E1%BA%A1n+b%E1%BB%91c") }}',
                    '5': '{{ url("/orders?status=Ki%E1%BB%83m+h%C3%A0ng") }}',
                    '6': '{{ url("/orders?status=%C4%90ang+v%E1%BA%ADn+chuy%E1%BB%83n") }}',
                    '7': '{{ url("/orders?status=%C4%90%C3%A3+giao") }}',
                    '8': '{{ url("/orders?status=H%E1%BB%A7y") }}',
                };
                if (tabs[e.key]) {
                    e.preventDefault();
                    window.location.href = tabs[e.key];
                    return;
                }
            }
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.selectedIds = [...this.allOrderIds];
            } else {
                this.selectedIds = [];
            }
        },

        isAllSelected() {
            return this.allOrderIds.length > 0 && this.selectedIds.length === this.allOrderIds.length;
        },

        printSelected() {
            if (this.selectedIds.length === 0) {
                const firstCheckbox = document.querySelector('input[name="selectedOrders[]"]');
                if (firstCheckbox && firstCheckbox.value) {
                    this.openPrintModal(firstCheckbox.value);
                    return;
                }
                alert('Vui lòng tích chọn ít nhất một chứng từ cần in!');
                return;
            }
            if (this.selectedIds.length === 1) {
                this.openPrintModal(this.selectedIds[0]);
            } else {
                this.openBulkPrintModal('consolidated');
            }
        },

        async openBulkPrintModal(tab = 'consolidated') {
            if (!this.selectedIds || this.selectedIds.length === 0) {
                alert('Vui lòng tích chọn ít nhất 1 đơn hàng để in!');
                return;
            }
            this.bulkPrintTab = tab;
            this.showBulkPrintModal = true;
            this.bulkPrintLoading = true;
            this.bulkPrintData = null;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                let res;
                try {
                    res = await fetch('/api/orders/bulk-print-data', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token || ''
                        },
                        body: JSON.stringify({ order_ids: this.selectedIds })
                    });
                } catch (errPost) {
                    console.warn('POST bulk-print-data failed, attempting GET fallback...', errPost);
                }

                // Fallback: nếu POST lỗi hoặc trả về status lỗi, thử qua GET
                if (!res || !res.ok) {
                    const params = new URLSearchParams();
                    this.selectedIds.forEach(id => params.append('order_ids[]', id));
                    res = await fetch(`/api/orders/bulk-print-data?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                }

                if (!res.ok) {
                    let errMsg = `Lỗi máy chủ (${res.status})`;
                    try {
                        const errJson = await res.json();
                        if (errJson.message) errMsg = errJson.message;
                    } catch (_) {}
                    alert(errMsg);
                    this.showBulkPrintModal = false;
                    return;
                }

                const json = await res.json();
                if (json.success) {
                    this.bulkPrintData = json;
                } else {
                    alert(json.message || 'Không thể tải dữ liệu in đơn hàng');
                    this.showBulkPrintModal = false;
                }
            } catch (e) {
                console.error('Error fetching bulk print data:', e);
                alert('Lỗi khi tải dữ liệu in hàng loạt: ' + (e.message || 'Vui lòng thử lại'));
                this.showBulkPrintModal = false;
            } finally {
                this.bulkPrintLoading = false;
            }
        },

        exportSelectedManifest() {
            const list = this.selectedIds.length > 0
                ? this.tableOrders.filter(o => this.selectedIds.includes(String(o.id)))
                : this.tableOrders;

            if (list.length === 0) {
                alert('Không có đơn hàng nào để xuất bảng kê.');
                return;
            }

            let csv = "\uFEFF"; // UTF-8 BOM
            csv += "BẢNG KÊ DANH SÁCH ĐƠN HÀNG XUẤT KHO\n";
            csv += "Thời gian xuất," + new Date().toLocaleString('vi-VN') + "\n";
            csv += "Tổng số chứng từ," + list.length + "\n\n";
            csv += "STT,Mã đơn hàng,Ngày tạo,Khách hàng,Số điện thoại,Địa chỉ nhận hàng,Kho xuất,Thành tiền (đ),Người lên đơn,Trạng thái\n";

            let totalAmt = 0;
            list.forEach((o, idx) => {
                const amt = Number(o.total || 0);
                totalAmt += amt;
                csv += `${idx + 1},"${o.code}","${o.date}","${(o.customer || '').replace(/"/g, '""')}","${o.phone}","${(o.address || '').replace(/"/g, '""')}","${(o.warehouse || '').replace(/"/g, '""')}",${amt},"${(o.creator || '').replace(/"/g, '""')}","${o.status}"\n`;
            });

            csv += `\nTổng cộng,,,,,,,${totalAmt},,\n`;

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `Bang_ke_don_hang_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        exportManifest() {
            this.exportSelectedManifest();
        },

        async bulkUpdateStatus(newStatus) {
            if (!this.selectedIds || this.selectedIds.length === 0) {
                alert('Vui lòng tích chọn ít nhất một đơn hàng!');
                return;
            }
            if (!confirm(`Bạn có chắc chắn muốn chuyển trạng thái của ${this.selectedIds.length} đơn hàng đã chọn sang [${newStatus}]?`)) {
                return;
            }

            this.isBulkProcessing = true;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            let successCount = 0;
            let failCount = 0;

            for (const id of this.selectedIds) {
                try {
                    const res = await fetch('/api/orders/update-status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                        body: JSON.stringify({ orderId: id, status: newStatus })
                    });
                    const data = await res.json();
                    if (data.success || res.ok) {
                        successCount++;
                    } else {
                        failCount++;
                    }
                } catch (e) {
                    failCount++;
                }
            }

            this.isBulkProcessing = false;
            alert(`Đã hoàn tất cập nhật trạng thái [${newStatus}]: Thành công ${successCount} đơn` + (failCount > 0 ? `, thất bại ${failCount} đơn.` : '.'));
            window.location.reload();
        },

        async confirmLockOrder(orderOrId, orderCode) {
            let id = orderOrId;
            let code = orderCode;
            if (typeof orderOrId === 'object' && orderOrId !== null) {
                id = orderOrId.id;
                code = orderOrId.code || orderOrId.id;
            }
            this.closeActionMenu();
            if (!confirm(`Xác nhận chốt đơn hàng [${code || id}] và chuyển sang bước chuẩn bị soạn hàng?`)) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/orders/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ orderId: id, status: 'Chốt đơn' })
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Chốt đơn thất bại.');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ.');
            }
        },

        async updateStatus(orderId, newStatus) {
            if (!confirm(`Bạn có chắc chắn muốn chuyển trạng thái đơn hàng sang: [${newStatus}]?`)) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/orders/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ orderId, status: newStatus })
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    alert('Cập nhật trạng thái thành công!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Cập nhật thất bại.');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ.');
            }
        },

        printOrder(id) {
            this.closeActionMenu();
            this.openPrintModal(id);
        },

        openTrackingModal(order) {
            this.closeActionMenu();
            this.activeTrackingOrder = order;
            this.showTrackingModal = true;
        },

        isStepCompleted(stepName) {
            if (!this.activeTrackingOrder) return false;
            let current = this.activeTrackingOrder.status;
            if (current === 'Hủy') return false;
            if (current === 'Soạn bốc' || current === 'Kho đang soạn' || current === 'Đang bốc hàng' || current === 'Đã bốc xong') current = 'Soạn hàng';
            if (current === 'Đã duyệt' || current === 'Chờ duyệt') current = 'Chốt đơn';
            if (current === 'Đã kiểm hàng') current = 'Kiểm hàng';
            if (current === 'Đang giao hàng') current = 'Đang vận chuyển';
            if (current === 'Giao thành công') current = 'Đã giao';

            let checkStep = stepName === 'Soạn bốc' ? 'Soạn hàng' : stepName;
            const orderStages = ['Báo giá', 'Chốt đơn', 'Soạn hàng', 'Kiểm hàng', 'Đang vận chuyển', 'Đã giao'];
            const curIdx = orderStages.indexOf(current);
            const stepIdx = orderStages.indexOf(checkStep);
            if (curIdx === -1) return false;
            return curIdx > stepIdx;
        },

        isStepCurrent(stepName) {
            if (!this.activeTrackingOrder) return false;
            const current = this.activeTrackingOrder.status;
            if (stepName === current) return true;
            if ((stepName === 'Soạn hàng' || stepName === 'Soạn bốc') && ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong'].includes(current)) return true;
            if (stepName === 'Kiểm hàng' && ['Kiểm hàng', 'Đã kiểm hàng', 'Sẵn sàng xuất xe'].includes(current)) return true;
            if (stepName === 'Đang vận chuyển' && ['Đang vận chuyển', 'Đang giao hàng'].includes(current)) return true;
            if (stepName === 'Đã giao' && ['Đã giao', 'Giao thành công'].includes(current)) return true;
            return false;
        },

        exportOrderExcel(order, type = 'standard') {
            this.closeActionMenu();
            if (!order) return;
            const fileName = (type === 'invoice' ? 'Hoa_don_' : 'Don_hang_') + (order.code || order.id) + '.csv';
            const csvContent = "\uFEFF" + 
                "THÔNG TIN CHỨNG TỪ\n" +
                "Mã đơn hàng," + (order.code || '') + "\n" +
                "Khách hàng," + (order.customer || '') + "\n" +
                "Số điện thoại," + (order.phone || '') + "\n" +
                "Địa chỉ giao hàng," + (order.address || '') + "\n" +
                "Ngày lập," + (order.date || '') + "\n" +
                "Kho xuất," + (order.warehouse || '') + "\n" +
                "Trạng thái," + (order.status || '') + "\n" +
                "Tổng tiền," + (order.total || 0) + "\n";

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        exportOrderPdf(order) {
            this.closeActionMenu();
            if (!order) return;
            window.open('/orders/' + order.id + '/edit?print=1&pdf=1', '_blank');
        },

        openPaymentModal(order) {
            this.closeActionMenu();
            this.activePaymentOrder = order;
            this.paymentForm = {
                method: 'Tiền mặt',
                amount: Number(order.total) || 0,
                notes: ''
            };
            this.showPaymentModal = true;
        },

        async submitPayment() {
            if (!this.activePaymentOrder) return;
            if (!this.paymentForm.amount || Number(this.paymentForm.amount) <= 0) {
                alert('Vui lòng nhập số tiền thanh toán hợp lệ.');
                return;
            }
            this.paymentSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/orders/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        id: this.activePaymentOrder.id,
                        paymentStatus: 'Đã thanh toán',
                        paymentMethod: this.paymentForm.method,
                        notes: (this.paymentForm.notes ? '[THANH TOÁN ' + this.paymentForm.method + ': ' + this.formatMoney(this.paymentForm.amount) + '] ' + this.paymentForm.notes : '')
                    })
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    alert('Nhập thanh toán thành công!');
                    this.showPaymentModal = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'Lưu thanh toán thất bại.');
                }
            } catch (e) {
                alert('Lỗi kết nối khi lưu thanh toán.');
            } finally {
                this.paymentSubmitting = false;
            }
        },

        async openPrintModal(orderOrId) {
            let orderId = orderOrId;
            if (typeof orderOrId === 'object' && orderOrId !== null) {
                orderId = orderOrId.id;
            }
            if (!orderId) {
                if (this.selectedIds.length > 0) {
                    orderId = this.selectedIds[0];
                } else {
                    const firstCheckbox = document.querySelector('input[name="selectedOrders[]"]');
                    if (firstCheckbox && firstCheckbox.value) {
                        orderId = firstCheckbox.value;
                    } else {
                        alert('Vui lòng chọn một đơn hàng để xem và in phiếu!');
                        return;
                    }
                }
            }
            this.closeActionMenu();
            this.printLoading = true;
            this.showPrintModal = true;
            this.printOrderData = null;

            try {
                const res = await fetch(`/api/orders/${orderId}/print-data`);
                const json = await res.json();
                if (json.success && json.order) {
                    this.printOrderData = json.order;
                } else {
                    alert(json.message || 'Không thể tải thông tin in đơn hàng');
                    this.showPrintModal = false;
                }
            } catch (e) {
                console.error('Error fetching print data:', e);
                alert('Lỗi kết nối khi tải dữ liệu đơn hàng');
                this.showPrintModal = false;
            } finally {
                this.printLoading = false;
            }
        },

        formatNumber(val) {
            if (val === null || val === undefined || isNaN(val)) return '0';
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val)));
        },

        getDiscountDisplay(it) {
            if (!it) return '-';
            if (it.discount_percent !== undefined && it.discount_percent !== null && Number(it.discount_percent) > 0) {
                return Number(it.discount_percent) + '%';
            }
            const origSubtotal = Number(it.amount || 0) || (Number(it.unit_price || 0) * Number(it.quantity || 0));
            const disc = Number(it.discount || 0);
            if (disc > 0 && origSubtotal > 0) {
                return Math.round((disc / origSubtotal) * 100) + '%';
            }
            return '-';
        },

        buildSingleOrderContentHtml(o, mode) {
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

            let tableRows = '';
            (o.items || []).forEach((it, idx) => {
                const prodName = isTaxName ? (it.tax_name || it.name) : it.name;
                const subName = (!isTaxName && it.tax_name && it.tax_name !== it.name) ? `<div style="font-size:10.5px;color:#555;font-style:italic;">${it.tax_name}</div>` : '';
                const discText = this.getDiscountDisplay(it);
                
                if (isNoPrice) {
                    tableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:left;font-family:monospace;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:13px;">${this.formatNumber(it.sold_quantity ?? it.quantity)}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:center;font-weight:bold;font-size:12px;">${it.price_tier_code || this.getPriceTierCode(it.price_type, o.dealer_tier)}</td>
                        </tr>
                    `;
                } else {
                    tableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:left;font-family:monospace;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:12.5px;">${this.formatNumber(it.sold_quantity ?? it.quantity)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${this.formatNumber(it.unit_price)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${discText}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;">${this.formatNumber(it.total || it.amount)}</td>
                        </tr>
                    `;
                }
            });

            let summaryHtml = '';
            if (!isNoPrice) {
                summaryHtml = `
                    <tr>
                        <td colspan="5" style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">Tổng</td>
                        <td style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;">${this.formatNumber(o.final_total || o.total_items_amount)}</td>
                    </tr>
                `;
            } else {
                summaryHtml = `
                    <tr>
                        <td colspan="4" style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">Tổng SL:</td>
                        <td style="text-align:center;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;">${this.formatNumber((o.items || []).reduce((s, it) => s + Number(it.sold_quantity ?? it.quantity ?? 0), 0))}</td>
                    </tr>
                `;
            }

            return `
                <div class="order-page" style="page-break-inside: avoid; margin-bottom: 20px;">
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
                                            <div style="font-size: 11.5px; color: #333; margin-top: 3px; line-height: 1.4;">
                                                ${this.companySetting?.address ? `<div><strong>Địa chỉ:</strong> ${this.companySetting.address}</div>` : ''}
                                                ${(this.companySetting?.hotline || this.companySetting?.phone) ? `<div><strong>Hotline / Zalo:</strong> ${this.companySetting.hotline || this.companySetting.phone} ${this.companySetting?.email ? `&nbsp;|&nbsp; <strong>Email:</strong> ${this.companySetting.email}` : ''}</div>` : ''}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
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
                                        <td style="width: 105px; border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Kho xuất hàng:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.warehouse || '---'}</strong></td>
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

                    <!-- Bảng Danh Sách Hàng Hóa -->
                    <table class="product-table">
                        <thead>
                            ${isNoPrice ? `
                                <tr>
                                    <th style="width: 35px;">TT</th>
                                    <th style="width: 95px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 55px;">ĐVT</th>
                                    <th style="width: 65px;">SL</th>
                                    <th style="width: 75px;">Loại Giá</th>
                                </tr>
                            ` : `
                                <tr>
                                    <th style="width: 35px;">TT</th>
                                    <th style="width: 95px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 55px;">ĐVT</th>
                                    <th style="width: 50px;">SL</th>
                                    <th style="width: 90px;">Đơn giá</th>
                                    <th style="width: 70px;">C.khấu</th>
                                    <th style="width: 100px;">Thành tiền</th>
                                </tr>
                            `}
                        </thead>
                        <tbody>
                            ${tableRows}
                            ${summaryHtml}
                        </tbody>
                    </table>

                    <!-- Bằng Chữ & Trạng Thái -->
                    ${!isNoPrice ? `
                        <div style="margin-top: 8px; font-size: 12.5px; line-height: 1.4;">
                            <strong>Số tiền viết bằng chữ:</strong> 
                            <em style="text-transform: capitalize; font-weight: bold;">${o.final_total_words || ''}</em>
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
                </div>
            `;
        },

        openPrintWindow(title, bodyContent) {
            const printHtml = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>${title}</title>
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
                    ${bodyContent}
                </body>
                </html>
            `;

            const printWin = window.open('', '_blank');
            if (!printWin) {
                alert('Trình duyệt đang chặn cửa sổ bật lên. Vui lòng cho phép pop-up để in.');
                return;
            }
            printWin.document.open();
            printWin.document.write(printHtml);
            printWin.document.close();
            printWin.focus();
            setTimeout(() => {
                printWin.print();
            }, 400);
        },

        executePrintOrder(mode) {
            if (!this.printOrderData) {
                alert('Chưa có thông tin đơn hàng để in!');
                return;
            }
            const o = this.printOrderData;
            const content = this.buildSingleOrderContentHtml(o, mode);
            this.openPrintWindow(`Phiếu ${o.order_code}`, content);
        },

        executePrintBatch(mode) {
            if (!this.bulkPrintData || !this.bulkPrintData.orders || this.bulkPrintData.orders.length === 0) {
                alert('Chưa có danh sách đơn hàng để in!');
                return;
            }
            const orders = this.bulkPrintData.orders;
            const pages = orders.map((o, idx) => {
                const pageHtml = this.buildSingleOrderContentHtml(o, mode);
                const breakTag = (idx < orders.length - 1) ? '<div style="page-break-after: always; break-after: page; height: 0; margin: 0; padding: 0;"></div>' : '';
                return pageHtml + breakTag;
            }).join('\n');

            this.openPrintWindow(`In hàng loạt ${orders.length} đơn hàng`, pages);
        },

        executePrintConsolidated(mode = 'standard') {
            if (!this.bulkPrintData || !this.bulkPrintData.consolidated) {
                alert('Chưa có dữ liệu phiếu gộp để in!');
                return;
            }
            const c = this.bulkPrintData.consolidated;
            const isNoPrice = (mode === 'no_price');
            const isTaxName = (mode === 'tax_name');

            let title = 'PHIẾU TỔNG HỢP XUẤT KHO KIÊM BẢNG KÊ';
            let subTitle = `PHIẾU GỘP TỔNG HỢP SẢN PHẨM TỪ ${c.total_orders} ĐƠN HÀNG`;
            if (isNoPrice) {
                title = 'PHIẾU TỔNG HỢP SOẠN HÀNG XUẤT KHO';
                subTitle = `DÀNH CHO BỘ PHẬN KHO & BỐC XẾP (${c.total_orders} ĐƠN HÀNG - KHÔNG HIỂN THỊ ĐƠN GIÁ)`;
            } else if (isTaxName) {
                title = 'BẢNG KÊ TỔNG HỢP XUẤT HÀNG THEO HÓA ĐƠN';
                subTitle = `TỔNG HỢP SẢN PHẨM THEO TÊN HÓA ĐƠN GTGT (${c.total_orders} ĐƠN HÀNG)`;
            }

            let rowsHtml = '';
            (c.products || []).forEach((p, idx) => {
                const prodName = isTaxName ? (p.tax_name || p.name) : p.name;
                const subName = (!isTaxName && p.tax_name && p.tax_name !== p.name) ? `<div style="font-size:10.5px;color:#555;font-style:italic;">${p.tax_name}</div>` : '';

                if (isNoPrice) {
                    rowsHtml += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:left;font-family:monospace;">${p.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">
                                ${prodName}${subName}
                            </td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${p.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${this.formatNumber(p.sold_quantity)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${this.formatNumber(p.gift_quantity)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:13px;background-color:#f8fafc;">${this.formatNumber(p.total_quantity)}</td>
                        </tr>
                    `;
                } else {
                    rowsHtml += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:left;font-family:monospace;">${p.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">
                                ${prodName}${subName}
                            </td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${p.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${this.formatNumber(p.sold_quantity)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${this.formatNumber(p.gift_quantity)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:12.5px;">${this.formatNumber(p.total_quantity)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${this.formatNumber(p.unit_price)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;">${this.formatNumber(p.total_amount)}</td>
                        </tr>
                    `;
                }
            });

            let summaryFoot = '';
            if (isNoPrice) {
                summaryFoot = `
                    <tr>
                        <td colspan="4" style="border:1px solid #111;padding:5px 6px;text-align:right;font-weight:bold;">Tổng cộng:</td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">${this.formatNumber(c.total_sold_quantity)}</td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">${this.formatNumber(c.total_gift_quantity)}</td>
                        <td style="text-align:right;font-weight:black;padding:5px 6px;border:1px solid #111;font-size:13px;background-color:#f1f5f9;">${this.formatNumber(c.total_quantity)}</td>
                    </tr>
                `;
            } else {
                summaryFoot = `
                    <tr>
                        <td colspan="4" style="border:1px solid #111;padding:5px 6px;text-align:right;font-weight:bold;">Tổng cộng:</td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">${this.formatNumber(c.total_sold_quantity)}</td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">${this.formatNumber(c.total_gift_quantity)}</td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:12.5px;">${this.formatNumber(c.total_quantity)}</td>
                        <td style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:black;padding:5px 6px;border:1px solid #111;font-size:13px;">${this.formatNumber(c.total_amount)}</td>
                    </tr>
                `;
            }

            let orderListRows = '';
            (c.order_list || []).forEach((o, idx) => {
                orderListRows += `
                    <tr>
                        <td style="text-align:center;padding:4px 5px;border:1px solid #ccc;">${idx + 1}</td>
                        <td style="font-family:monospace;font-weight:bold;padding:4px 5px;border:1px solid #ccc;">${o.order_code}</td>
                        <td style="padding:4px 5px;border:1px solid #ccc;font-weight:600;">${(o.customer_name || '').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')}</td>
                        <td style="padding:4px 5px;border:1px solid #ccc;">${o.customer_phone || ''}</td>
                        <td style="padding:4px 5px;border:1px solid #ccc;font-size:11px;">${o.customer_address || ''}</td>
                        <td style="text-align:center;padding:4px 5px;border:1px solid #ccc;">${o.warehouse || ''}</td>
                        <td style="text-align:right;padding:4px 5px;border:1px solid #ccc;font-weight:bold;">${this.formatNumber(o.total_quantity)}</td>
                        ${!isNoPrice ? `<td style="text-align:right;padding:4px 5px;border:1px solid #ccc;font-weight:bold;">${this.formatNumber(o.final_total)}</td>` : ''}
                        <td style="padding:4px 5px;border:1px solid #ccc;font-style:italic;font-size:10.5px;">${o.notes || ''}</td>
                    </tr>
                `;
            });

            const consolidatedContent = `
                <div class="order-page">
                    <!-- Header Doanh Nghiệp -->
                    <table style="width: 100%; border: none; border-bottom: 2px solid #1b4a8c; padding-bottom: 6px; margin-bottom: 10px; border-collapse: collapse;">
                        <tr>
                            <td style="width: 65%; vertical-align: top; border: none; padding: 0; text-align: left;">
                                <table style="border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="border: none; padding: 0 10px 0 0; vertical-align: middle;">
                                            <img src="/images/logo.png" alt="Logo Đại Sơn" style="height: 48px; width: auto; display: block;" onerror="this.style.display='none'">
                                        </td>
                                        <td style="border: none; padding: 0; vertical-align: middle;">
                                            <div style="font-size: 14px; font-weight: bold; color: #1b4a8c; text-transform: uppercase;">${this.companySetting?.company_name || 'HỆ THỐNG PHÂN PHỐI ĐẠI SƠN'}</div>
                                            <div style="font-size: 10.5px; color: #444; font-weight: bold; text-transform: uppercase;">${this.companySetting?.tax_name || this.companySetting?.company_name || ''}</div>
                                            <div style="font-size: 11px; color: #333; margin-top: 2px;">${this.companySetting?.address || ''}${(this.companySetting?.hotline || this.companySetting?.phone) ? ` &nbsp;|&nbsp; Hotline: ${this.companySetting.hotline || this.companySetting.phone}` : ''}</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 35%; vertical-align: top; text-align: right; border: none; padding: 0;">
                                <div style="font-size: 10.5px; font-weight: bold; text-transform: uppercase;">PHIẾU XUẤT KHO GỘP</div>
                                <div style="display: inline-block; border: 1px solid #1b4a8c; background-color: #f0f7ff; padding: 3px 8px; border-radius: 4px; font-size: 11.5px; font-weight: bold; color: #1b4a8c; margin-top: 3px;">
                                    ${c.batch_code}
                                </div>
                                <div style="font-size: 10.5px; color: #555; margin-top: 2px;">Ngày in: ${new Date().toLocaleDateString('vi-VN')} ${new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}</div>
                            </td>
                        </tr>
                    </table>

                    <!-- Tiêu Đề Phiếu -->
                    <div style="text-align: center; margin-bottom: 12px;">
                        <h2 style="margin: 0 0 3px 0; font-size: 18px; font-weight: 900; text-transform: uppercase; color: #111;">${title}</h2>
                        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #475569;">${subTitle}</div>
                        <div style="font-size: 11.5px; font-style: italic; color: #333; margin-top: 2px;">Ngày lập phiếu: <strong>${c.created_date}</strong></div>
                    </div>

                    <!-- Khối Thông Tin Gộp -->
                    <table style="width: 100%; border: 1px solid #cbd5e1; background-color: #f8fafc; padding: 6px 10px; margin-bottom: 10px; border-collapse: collapse; font-size: 12px;">
                        <tr>
                            <td style="padding: 4px 8px; border: none; width: 50%;">
                                <div><strong>Kho xuất hàng:</strong> <span style="text-transform: uppercase; font-weight: bold; color: #0f172a;">${c.warehouse_display}</span></div>
                                <div style="margin-top: 2px;"><strong>Tổng số đơn hàng gộp:</strong> <strong style="color: #0284c7;">${c.total_orders} đơn</strong></div>
                            </td>
                            <td style="padding: 4px 8px; border: none; width: 50%;">
                                <div><strong>Tổng số loại sản phẩm:</strong> <strong>${c.total_products_count} loại</strong></div>
                                <div style="margin-top: 2px;"><strong>Tổng số lượng xuất:</strong> <strong style="color: #b91c1c; font-size: 13px;">${this.formatNumber(c.total_quantity)}</strong> sản phẩm</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding: 4px 8px; border-top: 1px dashed #cbd5e1;">
                                <strong>Mã các đơn hàng gộp:</strong> 
                                <span style="font-family: monospace; font-size: 11.5px; font-weight: 600; color: #334155;">${c.order_codes_display}</span>
                            </td>
                        </tr>
                    </table>

                    <!-- BẢNG TỔNG HỢP SẢN PHẨM -->
                    <div style="margin-bottom: 4px; font-weight: bold; font-size: 12px; text-transform: uppercase; color: #0f172a;">
                        I. BẢNG TỔNG HỢP TOÀN BỘ SẢN PHẨM CẦN XUẤT:
                    </div>
                    <table class="product-table">
                        <thead>
                            ${isNoPrice ? `
                                <tr>
                                    <th style="width: 32px;">TT</th>
                                    <th style="width: 90px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 50px;">ĐVT</th>
                                    <th style="width: 60px;">SL Bán</th>
                                    <th style="width: 60px;">SL Tặng</th>
                                    <th style="width: 80px;">Tổng Xuất</th>
                                </tr>
                            ` : `
                                <tr>
                                    <th style="width: 32px;">TT</th>
                                    <th style="width: 85px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 45px;">ĐVT</th>
                                    <th style="width: 55px;">SL Bán</th>
                                    <th style="width: 55px;">SL Tặng</th>
                                    <th style="width: 65px;">Tổng Xuất</th>
                                    <th style="width: 80px;">Đơn giá</th>
                                    <th style="width: 95px;">Thành tiền</th>
                                </tr>
                            `}
                        </thead>
                        <tbody>
                            ${rowsHtml}
                            ${summaryFoot}
                        </tbody>
                    </table>

                    ${!isNoPrice ? `
                        <div style="margin-top: 6px; font-size: 12px;">
                            <strong>Tổng tiền bằng chữ:</strong> <em style="font-weight: bold; text-transform: capitalize;">${c.total_amount_words}</em>
                        </div>
                    ` : ''}

                    <!-- BẢNG DANH SÁCH CÁC ĐƠN HÀNG ĐƯỢC GỘP -->
                    <div style="margin-top: 14px; margin-bottom: 4px; font-weight: bold; font-size: 11.5px; text-transform: uppercase; color: #0f172a;">
                        II. DANH SÁCH CHI TIẾT CÁC ĐƠN HÀNG ĐƯỢC GỘP (${c.total_orders} ĐƠN):
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr style="background-color: #f1f5f9;">
                                <th style="border: 1px solid #ccc; padding: 4px; width: 30px; text-align: center;">TT</th>
                                <th style="border: 1px solid #ccc; padding: 4px; width: 85px; text-align: left;">Mã đơn</th>
                                <th style="border: 1px solid #ccc; padding: 4px; text-align: left;">Khách hàng</th>
                                <th style="border: 1px solid #ccc; padding: 4px; width: 85px;">Điện thoại</th>
                                <th style="border: 1px solid #ccc; padding: 4px; text-align: left;">Địa chỉ giao</th>
                                <th style="border: 1px solid #ccc; padding: 4px; width: 65px; text-align: center;">Kho</th>
                                <th style="border: 1px solid #ccc; padding: 4px; width: 50px; text-align: right;">SL SP</th>
                                ${!isNoPrice ? `<th style="border: 1px solid #ccc; padding: 4px; width: 85px; text-align: right;">Tiền hàng</th>` : ''}
                                <th style="border: 1px solid #ccc; padding: 4px; text-align: left;">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${orderListRows}
                        </tbody>
                    </table>

                    <!-- Dòng Ngày Tháng & Chữ Ký 4 Bên -->
                    <div style="margin-top: 12px; text-align: right; font-size: 11.5px; font-style: italic;">
                        Ngày ..... tháng ..... năm 202...
                    </div>
                    <table style="width: 100%; border: none; margin-top: 6px; border-collapse: collapse; page-break-inside: avoid;">
                        <tr>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12px; text-transform: uppercase;">Người lập phiếu</div>
                                <div style="font-style: italic; font-size: 10.5px; color: #555; margin-top: 1px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 55px;"></div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12px; text-transform: uppercase;">Thủ kho xuất</div>
                                <div style="font-style: italic; font-size: 10.5px; color: #555; margin-top: 1px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 55px;"></div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12px; text-transform: uppercase;">Người giao hàng</div>
                                <div style="font-style: italic; font-size: 10.5px; color: #555; margin-top: 1px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 55px;"></div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12px; text-transform: uppercase;">Kế toán duyệt</div>
                                <div style="font-style: italic; font-size: 10.5px; color: #555; margin-top: 1px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 55px;"></div>
                            </td>
                        </tr>
                    </table>
                </div>
            `;

            this.openPrintWindow(`Phiếu gộp ${c.batch_code}`, consolidatedContent);
        },

        exportConsolidatedExcel() {
            if (!this.bulkPrintData || !this.bulkPrintData.consolidated) {
                alert('Chưa có dữ liệu phiếu gộp để xuất Excel!');
                return;
            }
            const c = this.bulkPrintData.consolidated;
            let csv = "\uFEFF"; // UTF-8 BOM
            csv += "PHIẾU GỘP TỔNG HỢP SẢN PHẨM\n";
            csv += `Mã phiếu gộp,"${c.batch_code}"\n`;
            csv += `Thời gian xuất,"${c.created_date}"\n`;
            csv += `Kho xuất,"${(c.warehouse_display || '').replace(/"/g, '""')}"\n`;
            csv += `Tổng số đơn gộp,${c.total_orders}\n`;
            csv += `Mã các đơn,"${(c.order_codes_display || '').replace(/"/g, '""')}"\n\n`;

            csv += "I. BẢNG TỔNG HỢP SẢN PHẨM\n";
            csv += "STT,Mã sản phẩm,Tên sản phẩm,Tên theo HĐ,ĐVT,SL Bán,SL Tặng,Tổng SL Xuất,Đơn giá (đ),Thành tiền (đ),Chi tiết phân bổ theo đơn hàng\n";

            (c.products || []).forEach((p, idx) => {
                csv += `${idx + 1},"${p.sku || ''}","${(p.name || '').replace(/"/g, '""')}","${(p.tax_name || '').replace(/"/g, '""')}","${p.unit || ''}",${p.sold_quantity},${p.gift_quantity},${p.total_quantity},${p.unit_price},${p.total_amount},"${(p.allocation_text || '').replace(/"/g, '""')}"\n`;
            });

            csv += `Tổng cộng,,,,${c.total_sold_quantity},${c.total_gift_quantity},${c.total_quantity},,${c.total_amount},\n`;
            csv += `Bằng chữ,"${(c.total_amount_words || '').replace(/"/g, '""')}"\n\n`;

            csv += "II. DANH SÁCH CÁC ĐƠN HÀNG ĐƯỢC GỘP\n";
            csv += "STT,Mã đơn hàng,Ngày lập,Khách hàng,Số điện thoại,Địa chỉ nhận hàng,Kho xuất,Số lượng SP,Thành tiền (đ),Ghi chú\n";
            (c.order_list || []).forEach((o, idx) => {
                csv += `${idx + 1},"${o.order_code}","${o.created_date}","${(o.customer_name || '').replace(/"/g, '""')}","${o.customer_phone || ''}","${(o.customer_address || '').replace(/"/g, '""')}","${(o.warehouse || '').replace(/"/g, '""')}",${o.total_quantity},${o.final_total},"${(o.notes || '').replace(/"/g, '""')}"\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `Phieu_gop_tong_hop_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
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

            return str.toUpperCase();
        },

        exportOrderExcelPrint(orderData, type = 'standard') {
            if (!orderData) {
                alert('Chưa có thông tin đơn hàng để xuất Excel!');
                return;
            }
            const o = orderData;
            const isTaxName = (type === 'invoice');
            const fileName = (isTaxName ? 'Hoa_don_' : 'Don_hang_') + (o.order_code || o.id) + '.csv';

            let csvContent = "\uFEFF"; // UTF-8 BOM
            csvContent += "THÔNG TIN ĐƠN HÀNG\n";
            csvContent += "Số hóa đơn," + (o.order_code || '') + "\n";
            csvContent += "Ngày lập," + (o.created_date || '') + "\n";
            csvContent += "Khách hàng,\"" + ((o.customer_name || o.customer_display || '').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')).replace(/"/g, '""') + "\"\n";
            csvContent += "Kho xuất,\"" + (o.warehouse || '').replace(/"/g, '""') + "\"\n";
            csvContent += "Diễn giải,\"" + (o.notes || '').replace(/"/g, '""') + "\"\n";
            csvContent += "Địa chỉ giao hàng,\"" + (o.customer_address || '').replace(/"/g, '""') + "\"\n";
            csvContent += "Số điện thoại," + (o.customer_phone || '') + "\n";
            csvContent += "Hình thức thanh toán," + (o.payment_method || 'Tiền mặt') + "\n";
            csvContent += "Tình trạng," + (o.delivery_status || 'Đã xuất hàng') + "\n\n";

            csvContent += "DANH SÁCH SẢN PHẨM\n";
            csvContent += "TT,Tên sản phẩm,Tên HĐ,Đơn vị tính,Đơn giá,Số lượng,Thành tiền,Chiết khấu,Tổng tiền,Giá theo KH,Loại giá\n";

            (o.items || []).forEach(it => {
                const prodName = isTaxName ? (it.tax_name || it.name) : it.name;
                csvContent += `${it.tt},"${prodName.replace(/"/g, '""')}","${(it.tax_name || '').replace(/"/g, '""')}",${it.unit},${it.unit_price},${it.quantity},${it.amount},${it.discount},${it.total},${it.customer_price},${it.price_type}\n`;
            });

            csvContent += `\nTổng (1),,,,${o.subtotal},,${o.total_discount},${o.total_items_amount},,\n`;
            if (o.extra_discount > 0) {
                csvContent += `Chiết khấu (2),,,,,,,${o.extra_discount},,\n`;
            }
            csvContent += `Phải thanh toán (1-2),,,,,,,${o.final_total},,\n`;
            csvContent += `Bằng chữ,"${(o.final_total_words || '').replace(/"/g, '""')}"\n`;
            csvContent += `Người lập,"${(o.creator_name || '').replace(/"/g, '""')}"\n`;
            csvContent += `Người sửa cuối,"${(o.updater_name || '').replace(/"/g, '""')}"\n`;

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0)) + ' đ';
        }
    };
}
</script>
@endsection
