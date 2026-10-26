@extends('layouts.app')

@section('title', 'Bảng Quản Lý Công Việc & Lắp Đặt Máy Lọc Nước | ERP Đại Sơn')

@section('content')
<div x-data="technicianTasksApp()" x-init="initApp()" class="space-y-6 pb-12">
    {{-- Header, Thống kê nhanh & Thanh Tabs phân tách trang --}}
    @include('tasks.partials.header_stats')

    {{-- Trang 1: Đơn Hàng Lắp Đặt Máy Lọc Nước --}}
    @if($activeTab === 'installations')
        @include('tasks.tabs.tab_installations')
    @endif

    {{-- Trang 2: Đơn Bảo Dưỡng & Thay Lõi (KTV Tự Nhận Đơn) --}}
    @if($activeTab === 'maintenance')
        @include('tasks.tabs.tab_maintenance')
    @endif

    {{-- Trang 3: Nhiệm Vụ & Phân Công Nội Bộ --}}
    @if($activeTab === 'tasks')
        @include('tasks.tabs.tab_general_tasks')
    @endif

    {{-- Trang 4: Lấy Hàng Từ Kho Đi Làm (Lắp Đặt, Bảo Dưỡng, Sửa Chữa) --}}
    @if($activeTab === 'warehouse_export')
        @include('tasks.tabs.tab_warehouse_export')
    @endif

    {{-- Modal Quét QR & Cài Đặt Thông Tin Máy / Bổ Sung Phụ Kiện --}}
    @include('tasks.modals.modal_qr_setup')

    {{-- Modal Nghiệm Thu Bảo Dưỡng & Phụ Tùng Thay Thế (Có tính tiền hoặc miễn phí) --}}
    @include('tasks.modals.modal_maintenance_complete')
</div>

{{-- Toàn bộ Javascript / Alpine logic --}}
@include('tasks.partials.scripts')
@endsection