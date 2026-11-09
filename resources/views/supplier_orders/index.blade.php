@extends('layouts.app')

@section('title', 'Đặt Hàng Nhà Cung Cấp | ERP Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased">
    @php $currentTab = 'suppliers'; @endphp

    <!-- 1. Sub-Section Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto">
        <a href="{{ url('/orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $currentTab === 'orders' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span>Đơn hàng</span>
        </a>

        <a href="{{ url('/orders?tab=consignment') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $currentTab === 'consignment' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-boxes-stacked text-sm"></i>
            <span>Đơn hàng ôm</span>
        </a>

        <a href="{{ url('/orders?tab=returns') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $currentTab === 'returns' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-rotate-left text-sm"></i>
            <span>Khách trả hàng</span>
        </a>

        <a href="{{ url('/supplier-orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ $currentTab === 'suppliers' || request()->is('supplier-orders*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-cart-shopping text-sm"></i>
            <span>Đặt hàng NCC</span>
        </a>

        <a href="{{ url('/promotion-advances') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer {{ request()->is('promotion-advances*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-gift text-sm"></i>
            <span>Ứng khuyến mãi</span>
        </a>
    </div>

    <!-- 2. Supplier Orders Tab Content -->
    @include('supplier_orders.supplier_tab')
</div>
@endsection
