@extends('layouts.app')

@section('title', 'Trung Tâm Báo Cáo Phân Tích ERP - Đại Sơn')

@section('content')
<div class="space-y-4" x-data="{
    activeTab: '{{ $type ?? 'sales' }}',
    dateRange: 'month'
}">
    <!-- Header Banner -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ url('/dashboard') }}" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-emerald-600"></i>
                        <span>Trung Tâm Báo Cáo & Thống Kê Quản Trị</span>
                    </h1>
                    <p class="text-xs text-slate-500">Phân tích đa chiều: Doanh thu bán hàng, Xuất nhập tồn vật tư Tiền Phong, Dòng tiền thu chi & Công nợ</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <select x-model="dateRange" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 bg-white focus:outline-none">
                    <option value="today">Hôm nay</option>
                    <option value="week">Tuần này</option>
                    <option value="month" selected>Tháng này ({{ now()->format('m/Y') }})</option>
                    <option value="quarter">Quý này (Q{{ ceil(now()->month/3) }})</option>
                    <option value="year">Cả năm {{ now()->year }}</option>
                </select>
                <button type="button" @click="window.print()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-print"></i>
                    <span>In Báo Cáo</span>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto pt-4 mt-3 border-t border-slate-100 text-xs font-semibold scrollbar-thin">
            <button @click="activeTab = 'sales'" :class="activeTab === 'sales' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>1. BC Bán Hàng & Lợi Nhuận</span>
            </button>
            <button @click="activeTab = 'inventory'" :class="activeTab === 'inventory' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>2. BC Xuất - Nhập - Tồn Kho</span>
            </button>
            <button @click="activeTab = 'funds'" :class="activeTab === 'funds' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5">
                <i class="fa-solid fa-wallet"></i>
                <span>3. BC Dòng Tiền & Quỹ</span>
            </button>
            <button @click="activeTab = 'dealers'" :class="activeTab === 'dealers' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5">
                <i class="fa-solid fa-credit-card"></i>
                <span>4. BC Công Nợ & Tích Điểm Đại Lý</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: BÁO CÁO BÁN HÀNG -->
    <div x-show="activeTab === 'sales'" class="space-y-4">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-4 bg-white rounded-xl shadow-xs border border-slate-200">
                <span class="text-xs text-slate-500 font-medium">Tổng Doanh Số Bán</span>
                <p class="text-xl font-black text-slate-900 mt-1">{{ number_format($salesRevenue ?? 2840000000, 0, ',', '.') }} đ</p>
                <div class="mt-2 text-[11px] text-emerald-600 font-bold flex items-center gap-1">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    <span>Tăng 14.8% so với kỳ trước</span>
                </div>
            </div>
            <div class="p-4 bg-white rounded-xl shadow-xs border border-slate-200">
                <span class="text-xs text-slate-500 font-medium">Tổng Số Đơn Hoàn Tất</span>
                <p class="text-xl font-black text-sky-700 mt-1">{{ $totalOrdersCount ?? 142 }} Đơn</p>
                <div class="mt-2 text-[11px] text-slate-500 font-medium">Trung bình: {{ number_format(round(($salesRevenue ?? 2840000000) / max(1, $totalOrdersCount ?? 142)), 0, ',', '.') }} đ/đơn</div>
            </div>
            <div class="p-4 bg-white rounded-xl shadow-xs border border-slate-200">
                <span class="text-xs text-slate-500 font-medium">Ước Tính Lợi Nhuận Gộp</span>
                <p class="text-xl font-black text-emerald-600 mt-1">{{ number_format(($salesRevenue ?? 2840000000) * 0.185, 0, ',', '.') }} đ</p>
                <div class="mt-2 text-[11px] text-emerald-600 font-bold">Biên độ LN: ~18.5%</div>
            </div>
            <div class="p-4 bg-white rounded-xl shadow-xs border border-slate-200">
                <span class="text-xs text-slate-500 font-medium">Đơn Khách Trả Hàng</span>
                <p class="text-xl font-black text-rose-600 mt-1">3 Đơn (0.2%)</p>
                <div class="mt-2 text-[11px] text-emerald-600 font-bold">Nằm trong ngưỡng an toàn</div>
            </div>
        </div>

        <!-- Sales by Brand Table -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-award text-amber-600"></i>
                    <span>Doanh Thu & Tỷ Trọng Theo Thương Hiệu Phân Phối</span>
                </h3>
            </div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3">Thương Hiệu</th>
                        <th class="py-2.5 px-3 text-center">Số Mặt Hàng Đã Bán</th>
                        <th class="py-2.5 px-3 text-right">Doanh Số (VND)</th>
                        <th class="py-2.5 px-3 text-right">Tỷ Trọng Doanh Thu</th>
                        <th class="py-2.5 px-3 text-center">Xu Hướng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <tr class="hover:bg-sky-50/50">
                        <td class="py-2.5 px-3 font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span>Ống Nhựa Tiền Phong (Chủ lực)</span>
                        </td>
                        <td class="py-2.5 px-3 text-center">1,240 SP</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">1,980,000,000 đ</td>
                        <td class="py-2.5 px-3 text-right font-bold text-sky-700">69.7 %</td>
                        <td class="py-2.5 px-3 text-center text-emerald-600 font-bold"><i class="fa-solid fa-arrow-up mr-1"></i> Tăng trưởng mạnh</td>
                    </tr>
                    <tr class="hover:bg-sky-50/50">
                        <td class="py-2.5 px-3 font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>Ống & Phụ Tùng Hoa Sen</span>
                        </td>
                        <td class="py-2.5 px-3 text-center">420 SP</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">450,000,000 đ</td>
                        <td class="py-2.5 px-3 text-right font-bold text-sky-700">15.8 %</td>
                        <td class="py-2.5 px-3 text-center text-slate-600 font-medium">Ổn định</td>
                    </tr>
                    <tr class="hover:bg-sky-50/50">
                        <td class="py-2.5 px-3 font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>Thiết Bị Điện Sino & Panasonic</span>
                        </td>
                        <td class="py-2.5 px-3 text-center">560 SP</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">410,000,000 đ</td>
                        <td class="py-2.5 px-3 text-right font-bold text-sky-700">14.5 %</td>
                        <td class="py-2.5 px-3 text-center text-emerald-600 font-bold"><i class="fa-solid fa-arrow-up mr-1"></i> Mở rộng</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: XUẤT NHẬP TỒN KHO -->
    <div x-show="activeTab === 'inventory'" class="space-y-4">
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-sky-600"></i>
                    <span>Tổng Quan Tồn Kho Vật Tư Toàn Hệ Thống Đại Sơn</span>
                </h3>
                <a href="{{ url('/cat_products') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center gap-1">
                    <span>Xem Chi Tiết Tồn Sản Phẩm</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-4">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[11px] text-slate-500">Tổng Mã Mặt Hàng</span>
                    <p class="text-lg font-bold text-slate-900 mt-1">{{ $totalProducts ?? 340 }} SKU</p>
                </div>
                <div class="p-3 bg-sky-50 rounded-xl border border-sky-200">
                    <span class="text-[11px] text-sky-800">Tổng Giá Trị Tồn Kho</span>
                    <p class="text-lg font-bold text-sky-900 mt-1">~ 4.62 Tỷ đ</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                    <span class="text-[11px] text-amber-800">Cảnh Báo Sắp Hết Hàng</span>
                    <p class="text-lg font-bold text-amber-900 mt-1">8 Mặt hàng</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                    <span class="text-[11px] text-emerald-800">Vòng Quay Tồn Kho</span>
                    <p class="text-lg font-bold text-emerald-900 mt-1">18 Ngày / Vòng</p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: DÒNG TIỀN & THU CHI -->
    <div x-show="activeTab === 'funds'" class="space-y-4">
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-emerald-600"></i>
                    <span>Cân Đối Dòng Tiền Thu & Chi Doanh Nghiệp</span>
                </h3>
                <a href="{{ url('/funds') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                    <span>Mở Sổ Quỹ Thu Chi</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 my-4">
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                    <span class="text-[11px] text-emerald-800">Tổng Thu Tiền Mặt & Ngân Hàng</span>
                    <p class="text-xl font-bold text-emerald-900 mt-1">{{ number_format($totalThu ?? 3120000000, 0, ',', '.') }} đ</p>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl border border-rose-200">
                    <span class="text-[11px] text-rose-800">Tổng Chi Nhập Hàng & Vận Hành</span>
                    <p class="text-xl font-bold text-rose-900 mt-1">{{ number_format($totalChi ?? 2280000000, 0, ',', '.') }} đ</p>
                </div>
                <div class="p-3 bg-sky-50 rounded-xl border border-sky-200">
                    <span class="text-[11px] text-sky-800">Dòng Tiền Thuần (Net Cash Flow)</span>
                    <p class="text-xl font-bold text-sky-900 mt-1">+{{ number_format(($totalThu ?? 3120000000) - ($totalChi ?? 2280000000), 0, ',', '.') }} đ</p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: CÔNG NỢ ĐẠI LÝ -->
    <div x-show="activeTab === 'dealers'" class="space-y-4">
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-rose-600"></i>
                    <span>Tình Hình Công Nợ & Hạn Mức Tín Dụng Khách Hàng</span>
                </h3>
                <a href="{{ url('/debt') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800 flex items-center gap-1">
                    <span>Mở Bảng Kê Chi Tiết Công Nợ</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 my-4">
                <div class="p-3 bg-rose-50 rounded-xl border border-rose-200">
                    <span class="text-[11px] text-rose-800">Tổng Dư Nợ Toàn Hệ Thống</span>
                    <p class="text-xl font-bold text-rose-900 mt-1">{{ number_format($totalDebt ?? 1450000000, 0, ',', '.') }} đ</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                    <span class="text-[11px] text-amber-800">Nợ Đến Hạn Trong Tuần</span>
                    <p class="text-xl font-bold text-amber-900 mt-1">{{ number_format(320000000, 0, ',', '.') }} đ</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                    <span class="text-[11px] text-emerald-800">Tỷ Lệ Thu Hồi Đúng Hạn</span>
                    <p class="text-xl font-bold text-emerald-900 mt-1">94.2 %</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
