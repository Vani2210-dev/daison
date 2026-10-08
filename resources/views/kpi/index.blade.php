@extends('layouts.app')

@section('title', 'Quản Lý Đánh Giá KPI Nhân Sự & Phòng Ban - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="{
    activeTab: 'staff',
    filterDept: 'all'
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
                        <i class="fa-solid fa-bullseye text-amber-600"></i>
                        <span>Quản Lý Đánh Giá KPI Hiệu Quả Công Việc</span>
                    </h1>
                    <p class="text-xs text-slate-500">Chỉ tiêu doanh số bán hàng, mở đại lý mới, chăm sóc GPS thực địa & kiểm soát công nợ</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-slate-100 p-1 rounded-lg border border-slate-200 text-xs font-semibold">
                    <button @click="activeTab = 'staff'" :class="activeTab === 'staff' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1 rounded-md transition-all">
                        KPI Cá Nhân ({{ $staffKpi->total() ?? count($staffKpi ?? []) }})
                    </button>
                    <button @click="activeTab = 'department'" :class="activeTab === 'department' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1 rounded-md transition-all">
                        KPI Phòng Ban ({{ count($deptKpi ?? []) }})
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 1: KPI CÁ NHÂN -->
    <div x-show="activeTab === 'staff'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($staffKpi ?? [] as $kpi)
                <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 hover:shadow-md transition-all">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#1b4a8c] to-sky-500 text-white font-bold flex items-center justify-center text-xs">
                                {{ substr($kpi->user->name ?? $kpi->staff_name ?? 'NV', 0, 2) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-xs text-slate-900">{{ $kpi->user->name ?? $kpi->staff_name ?? 'Nhân viên kinh doanh' }}</h3>
                                <p class="text-[11px] text-slate-500">{{ $kpi->department ?? 'Phòng Kinh Doanh' }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black {{ ($kpi->score ?? 85) >= 90 ? 'bg-emerald-100 text-emerald-800' : (($kpi->score ?? 85) >= 80 ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">
                            Hạng {{ ($kpi->grade ?? 'A') }} ({{ $kpi->score ?? 85 }}/100)
                        </span>
                    </div>

                    <div class="py-3 space-y-2.5 text-xs text-slate-600">
                        <!-- Doanh số -->
                        <div>
                            <div class="flex justify-between text-[11px] font-semibold mb-1">
                                <span>Doanh số bán ra:</span>
                                <span class="font-bold text-slate-900">{{ number_format($kpi->sales_actual ?? 125000000) }} đ / {{ number_format($kpi->sales_target ?? 150000000) }} đ</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-sky-600 h-full rounded-full" style="width: {{ min(100, round((($kpi->sales_actual ?? 125) / ($kpi->sales_target ?? 150)) * 100)) }}%"></div>
                            </div>
                        </div>

                        <!-- Chăm sóc đại lý GPS -->
                        <div>
                            <div class="flex justify-between text-[11px] font-semibold mb-1">
                                <span>Chăm sóc đại lý GPS:</span>
                                <span class="font-bold text-slate-900">{{ $kpi->checkin_count ?? 18 }} / {{ $kpi->checkin_target ?? 20 }} lượt</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: 90%"></div>
                            </div>
                        </div>

                        <!-- Thu hồi công nợ -->
                        <div>
                            <div class="flex justify-between text-[11px] font-semibold mb-1">
                                <span>Tỷ lệ thu hồi nợ:</span>
                                <span class="font-bold text-slate-900">95% (Đạt)</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-purple-500 h-full rounded-full" style="width: 95%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Kỳ đánh giá: Tháng {{ now()->format('m/Y') }}</span>
                        <span class="font-bold text-emerald-600">Thưởng KPI: +{{ number_format($kpi->bonus ?? 2500000) }} đ</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl p-8 text-center text-slate-400 border border-slate-200">
                    <i class="fa-solid fa-bullseye text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs">Đang đồng bộ số liệu chỉ tiêu KPI nhân viên kỳ hiện tại</p>
                </div>
            @endforelse
        </div>

        @if($staffKpi->hasPages())
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-6 py-4">
                {{ $staffKpi->links() }}
            </div>
        @endif
    </div>

    <!-- TAB 2: KPI PHÒNG BAN -->
    <div x-show="activeTab === 'department'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-xs text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-shop text-blue-600"></i>
                        <span>Khối Bán Hàng & Kinh Doanh</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Đạt 94%</span>
                </div>
                <div class="py-3 space-y-2 text-xs text-slate-600">
                    <div class="flex justify-between"><span>Doanh thu tháng:</span><strong class="text-slate-900">1.85 Tỷ / 2.0 Tỷ</strong></div>
                    <div class="flex justify-between"><span>Số đơn chốt mới:</span><strong class="text-slate-900">420 đơn</strong></div>
                    <div class="flex justify-between"><span>Đại lý phát triển:</span><strong class="text-slate-900">+14 Đại lý cấp 2</strong></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-xs text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                        <span>Khối Kho Bãi & Giao Nhận</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 text-[10px] font-bold">Đạt 98%</span>
                </div>
                <div class="py-3 space-y-2 text-xs text-slate-600">
                    <div class="flex justify-between"><span>Thời gian soạn hàng TB:</span><strong class="text-slate-900">18 phút / đơn</strong></div>
                    <div class="flex justify-between"><span>Tỷ lệ giao đúng giờ:</span><strong class="text-slate-900">97.6%</strong></div>
                    <div class="flex justify-between"><span>Tỷ lệ sai sót / trả hàng:</span><strong class="text-emerald-600 font-bold">&lt; 0.4%</strong></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-xs text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-emerald-600"></i>
                        <span>Khối Kế Toán & Quản Trị</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Đạt 99%</span>
                </div>
                <div class="py-3 space-y-2 text-xs text-slate-600">
                    <div class="flex justify-between"><span>Đối soát công nợ:</span><strong class="text-slate-900">100% đúng hạn</strong></div>
                    <div class="flex justify-between"><span>Quyết toán quỹ COD lái xe:</span><strong class="text-slate-900">Hoàn tất trong ngày</strong></div>
                    <div class="flex justify-between"><span>Khóa sổ định kỳ:</span><strong class="text-slate-900">Chuẩn xác</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
