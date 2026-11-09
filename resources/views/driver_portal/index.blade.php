@extends('layouts.app')

@section('title', 'Giao Hàng & Thu Tiền | Lái Xe & Vận Tải ERP Đại Sơn')

@section('content')
@php
    $currentUser = auth()->user();
    $isDriver = $currentUser && in_array($currentUser->role, ['Driver', 'Warehouse_Staff']);
    $isAdmin = $currentUser && in_array($currentUser->role, ['Admin', 'Manager', 'Accountant', 'Sales_Accountant', 'Director']);
@endphp

<div x-data="deliveryExecutionApp()" x-init="initApp()" class="space-y-4 max-w-[1920px] mx-auto pb-20 font-sans text-slate-800 antialiased" x-cloak>

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

    <!-- DANH SÁCH ĐƠN HÀNG TRÊN XE -->
    <div class="space-y-4">
        <!-- 1. Header Banner Chuyến xe -->
        @include('driver_portal.partials.header_banner')

        <!-- 2. Banner Trạng Thái Xuất Bến -->
        @include('driver_portal.partials.departure_banner')

        <!-- 3. 5 Thẻ KPI Tiến Độ & Tài Chính -->
        @include('driver_portal.partials.kpi_cards')

        <!-- 4. Search & Filter Tabs -->
        @include('driver_portal.partials.filter_tabs')

        <!-- 5. Danh Sách Thẻ Đơn Hàng -->
        @include('driver_portal.partials.order_list')
    </div>

    <!-- 7. MODALS PHỤ TRỢ -->
    @include('driver_portal.modals.cod_handover')
    @include('driver_portal.modals.return_warehouse')
    @include('driver_portal.modals.return_items_list')
    @include('driver_portal.modals.failed_delivery')
    @include('driver_portal.modals.order_detail')
    @include('driver_portal.modals.personnel_warehouse')
    @include('driver_portal.modals.holding_sale')
    @include('driver_portal.modals.driver_holding_return')
    @include('driver_portal.modals.debt_handover')
    @include('driver_portal.modals.order_return_warehouse')

</div>

@include('driver_portal.scripts')

@endsection