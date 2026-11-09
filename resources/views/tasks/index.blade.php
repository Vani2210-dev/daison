@extends('layouts.app')

@section('title', 'Bảng Quản Lý Công Việc & Lắp Đặt Máy Lọc Nước | ERP Đại Sơn')

@push('styles')
<style>
/* ========================================================================= */
/* TỐI ƯU HÓA GIAO DIỆN MOBILE DÀNH RIÊNG CHO MÀN HÌNH < 768px             */
/* Được bao bọc hoàn toàn trong @media, không ảnh hưởng đến màn hình Desktop */
/* ========================================================================= */
@media (max-width: 767.98px) {
    /* Ép thẻ main của Layout không bị thụt lề trên Mobile để tràn full màn hình */
    main {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 52px !important; /* Áp sát mép trên thanh menu dưới, khử sạch dải xám thừa */
    }

    /* 1. Container & Khoảng đệm trang trên Mobile: Full 100% chiều rộng màn hình */
    .tasks-page-container {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }

    /* 2. Top Header Card: Tràn full 100% chiều rộng */
    .tasks-header-card {
        width: 100% !important;
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
        padding: 0.75rem 0.75rem !important;
        margin-bottom: 0.375rem !important;
        gap: 0.5rem !important;
    }
    .tasks-header-card h1 {
        font-size: 0.95rem !important;
        line-height: 1.25 !important;
    }
    .header-icon-box {
        width: 2rem !important;
        height: 2rem !important;
        font-size: 0.85rem !important;
        border-radius: 0.625rem !important;
    }
    .tasks-header-actions {
        width: 100% !important;
        flex-direction: column !important;
        gap: 0.375rem !important;
    }
    .tasks-view-switch {
        width: 100% !important;
        grid-template-columns: 1fr 1fr !important;
        padding: 0.2rem !important;
        border-radius: 0.75rem !important;
    }
    .tasks-view-switch a {
        padding: 0.45rem 0.25rem !important;
        font-size: 0.72rem !important;
        justify-content: center !important;
        border-radius: 0.6rem !important;
    }
    #btn-scan-qr-top {
        display: none !important;
    }

    /* 3. Thống kê nhanh Quick Stats: Ẩn trên Mobile */
    .quick-stats-grid {
        display: none !important;
    }
    .stat-icon {
        width: 2rem !important;
        height: 2rem !important;
        font-size: 0.85rem !important;
        border-radius: 0.625rem !important;
    }
    .stat-title {
        font-size: 0.62rem !important;
        letter-spacing: 0 !important;
        line-height: 1.15 !important;
    }
    .stat-number {
        font-size: 1.2rem !important;
        line-height: 1.15 !important;
    }

    /* 4. Thanh Tabs điều hướng trang: Full 100% chiều ngang */
    .tasks-nav-tabs-wrapper {
        margin-bottom: 0.25rem !important;
        padding-bottom: 0.25rem !important;
        border-bottom: none !important;
        padding-left: 0.375rem !important;
        padding-right: 0.375rem !important;
        width: 100% !important;
    }
    .tasks-nav-tabs {
        display: grid !important;
        grid-auto-flow: column !important;
        grid-auto-columns: minmax(0, 1fr) !important;
        gap: 0.25rem !important;
        overflow-x: visible !important;
        padding-bottom: 0 !important;
        width: 100% !important;
    }
    .tasks-nav-tabs a {
        white-space: nowrap !important;
        padding: 0.5rem 0.15rem !important;
        border-radius: 0.625rem !important;
        text-align: center !important;
    }

    /* 5. Tab 1: Đơn Lắp Đặt Máy Lọc Nước Full chiều rộng màn hình */
    .tasks-tab-card {
        width: 100% !important;
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
        margin-bottom: 0 !important;
    }
    .installations-header {
        flex-direction: column !important;
        gap: 0.5rem !important;
        padding: 0.75rem !important;
    }
    .installations-search-form {
        max-width: 100% !important;
        width: 100% !important;
    }
    .installation-card {
        padding: 0.75rem 0.875rem !important;
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
    }
    .installation-card-actions {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        gap: 0.5rem !important;
        padding-top: 0.5rem !important;
        margin-top: 0.375rem !important;
        border-top: 1px dashed #e2e8f0 !important;
    }
    .installation-card-actions > button {
        flex: 1 !important;
        min-height: 38px !important;
        justify-content: center !important;
        font-size: 0.775rem !important;
        padding: 0.5rem 0.75rem !important;
    }
    .installation-sub-actions {
        display: flex !important;
        gap: 0.375rem !important;
        flex-shrink: 0 !important;
    }
    .installation-sub-actions a {
        min-height: 38px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.75rem !important;
    }
    .customer-phone-pill {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
    }

    /* 6. Tab 2: Đơn Bảo Dưỡng & Thay Lõi Full chiều rộng */
    .maintenance-filters-card {
        padding: 0.75rem !important;
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
        width: 100% !important;
    }
    .maintenance-pills-row {
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
        padding-bottom: 0.25rem !important;
    }
    .maintenance-pills-row::-webkit-scrollbar {
        display: none !important;
    }
    .maintenance-item-card {
        padding: 0.75rem 0.875rem !important;
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
    }
    .maintenance-actions-col {
        flex-direction: column !important;
        align-items: stretch !important;
        width: 100% !important;
        gap: 0.5rem !important;
        padding-top: 0.75rem !important;
        margin-top: 0.5rem !important;
        border-top: 1px dashed #e2e8f0 !important;
    }
    .maintenance-actions-col > button {
        width: 100% !important;
        min-height: 44px !important;
        justify-content: center !important;
        font-size: 0.8125rem !important;
    }
    .sub-buttons-row {
        display: flex !important;
        width: 100% !important;
        gap: 0.375rem !important;
        justify-content: space-between !important;
    }
    .sub-buttons-row a,
    .sub-buttons-row button {
        flex: 1 !important;
        justify-content: center !important;
        min-height: 38px !important;
        padding: 0.375rem 0.5rem !important;
    }

    /* 7. Tab 3: Bảng Nhiệm Vụ Nội Bộ */
    .tasks-table-wrapper {
        border-radius: 0.875rem !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    /* 8. Tab 4: Phiếu Xuất Kho */
    .warehouse-export-banner {
        padding: 1rem !important;
        border-radius: 1.25rem !important;
    }
    .warehouse-purpose-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 0.5rem !important;
    }

    /* 9. Ngăn ngừa tự động zoom trên iOS Safari cho ô nhập liệu */
    input[type="text"],
    input[type="number"],
    input[type="tel"],
    select,
    textarea {
        font-size: 15px !important;
    }

    /* 10. Nút Icon Quét Mã QR nổi tròn ở góc màn hình trên Mobile (Nổi phía trên thanh menu dưới) */
    .tasks-mobile-fab {
        display: flex !important;
        bottom: calc(75px + env(safe-area-inset-bottom, 0px)) !important;
        right: 1rem !important;
        z-index: 9995 !important;
        box-shadow: 0 10px 25px -3px rgba(13, 148, 136, 0.5), 0 4px 6px -4px rgba(13, 148, 136, 0.3) !important;
    }
}

/* Ẩn hoàn toàn Floating Button trên Desktop (>= 768px) */
@media (min-width: 768px) {
    .tasks-mobile-fab {
        display: none !important;
    }
}
</style>
@endpush

@section('content')
<div x-data="technicianTasksApp()" x-init="initApp()" class="tasks-page-container md:space-y-6 pb-0 md:pb-6">
    {{-- Header, Thống kê nhanh & Thanh Tabs phân tách trang --}}
    @include('tasks.partials.header_stats')

    {{-- Trang 1: Đơn Hàng Lắp Đặt Máy Lọc Nước --}}
    @if($activeTab === 'installations' && !empty($canViewInstallations))
        @include('tasks.tabs.tab_installations')
    @endif

    {{-- Trang 2: Đơn Bảo Dưỡng & Thay Lõi (KTV Tự Nhận Đơn) --}}
    @if($activeTab === 'maintenance' && !empty($canViewMaintenance))
        @include('tasks.tabs.tab_maintenance')
    @endif

    {{-- Trang 3: Nhiệm Vụ & Phân Công Nội Bộ --}}
    @if($activeTab === 'tasks' && !empty($canViewTasks))
        @include('tasks.tabs.tab_general_tasks')
    @endif

    {{-- Trang 4: Lấy Hàng Từ Kho Đi Làm (Lắp Đặt, Bảo Dưỡng, Sửa Chữa) --}}
    @if($activeTab === 'warehouse_export' && !empty($canViewWarehouseExport))
        @include('tasks.tabs.tab_warehouse_export')
    @endif

    {{-- Modal Quét QR & Cài Đặt Thông Tin Máy / Bổ Sung Phụ Kiện --}}
    @include('tasks.modals.modal_qr_setup')

    {{-- Modal Nghiệm Thu Bảo Dưỡng & Phụ Tùng Thay Thế (Có tính tiền hoặc miễn phí) --}}
    @include('tasks.modals.modal_maintenance_complete')

    {{-- Nút Icon Quét Mã QR Nổi Ở Góc Màn Hình (Chỉ hiển thị khi có quyền xem tab Đơn Hàng Lắp Đặt) --}}
    @if($activeTab === 'installations' && !empty($canViewInstallations))
    <button type="button" 
            @click="openQrModal(null)" 
            class="tasks-mobile-fab fixed right-4 bottom-[75px] z-[9995] w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-600 via-teal-600 to-teal-500 text-white shadow-2xl items-center justify-center border-2 border-white active:scale-90 transition-transform cursor-pointer"
            title="Quét Mã QR Cài Đặt Máy"
            aria-label="Quét Mã QR Cài Đặt Máy">
        <i class="fa-solid fa-qrcode text-2xl"></i>
    </button>
    @endif
</div>

{{-- Toàn bộ Javascript / Alpine logic --}}
@include('tasks.partials.scripts')
@endsection
