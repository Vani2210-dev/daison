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
                        <span class="px-2 py-0.5 rounded text-[10px] font-black {{ ($kpi->score ?? 0) >= 90 ? 'bg-emerald-100 text-emerald-800' : (($kpi->score ?? 0) >= 80 ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">
                            Hạng {{ ($kpi->grade ?? '---') }} ({{ $kpi->score ?? 0 }}/100)
                        </span>
                    </div>

                    <div class="py-3 space-y-2.5 text-xs text-slate-600">
                        <!-- Doanh số -->
                        <div>
                            <div class="flex justify-between text-[11px] font-semibold mb-1">
                                <span>Doanh số bán ra:</span>
                                <span class="font-bold text-slate-900">{{ number_format($kpi->sales_actual ?? 0) }} đ / {{ number_format($kpi->sales_target ?? 0) }} đ</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-sky-600 h-full rounded-full" style="width: {{ ($kpi->sales_target ?? 0) > 0 ? min(100, round((($kpi->sales_actual ?? 0) / $kpi->sales_target) * 100)) : 0 }}%"></div>
                            </div>
                        </div>

                        <!-- Chăm sóc đại lý GPS -->
                        <div>
                            <div class="flex justify-between text-[11px] font-semibold mb-1">
                                <span>Chăm sóc đại lý GPS:</span>
                                <span class="font-bold text-slate-900">{{ $kpi->checkin_count ?? 0 }} / {{ $kpi->checkin_target ?? 0 }} lượt</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ ($kpi->checkin_target ?? 0) > 0 ? min(100, round((($kpi->checkin_count ?? 0) / $kpi->checkin_target) * 100)) : 0 }}%"></div>
                            </div>
                        </div>

                        <!-- Thu hồi công nợ -->
                        <div>
                            <div class="flex justify-between text-[11px] font-semibold mb-1">
                                <span>Tỷ lệ thu hồi nợ:</span>
                                <span class="font-bold text-slate-900">{{ $kpi->debt_recovery_rate ?? 0 }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-purple-500 h-full rounded-full" style="width: {{ min(100, (float)($kpi->debt_recovery_rate ?? 0)) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Kỳ đánh giá: Tháng {{ now()->format('m/Y') }}</span>
                        <span class="font-bold text-emerald-600">Thưởng KPI: +{{ number_format($kpi->bonus ?? 0) }} đ</span>
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
            @forelse($deptKpi as $dk)
                <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-xs text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-users-viewfinder text-blue-600"></i>
                            <span>{{ $dk->department_name ?: $dk->department }}</span>
                        </h3>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Đạt {{ (float)$dk->overall_target_percent }}%</span>
                    </div>
                    <div class="py-3 space-y-2 text-xs text-slate-600">
                        @if(!empty($dk->metrics) && is_array($dk->metrics))
                            @foreach($dk->metrics as $m)
                                <div class="flex justify-between">
                                    <span>{{ $m['name'] ?? 'Chỉ tiêu' }}:</span>
                                    <strong class="text-slate-900">{{ $m['actual'] ?? $m['achieved'] ?? '---' }} / {{ $m['target'] ?? '---' }}</strong>
                                </div>
                            @endforeach
                        @else
                            <div class="text-slate-400 italic">Đang cập nhật chỉ tiêu kỳ này.</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl p-8 text-center text-slate-400 border border-slate-200">
                    <i class="fa-solid fa-chart-line text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs">Chưa có dữ liệu KPI phòng ban.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
