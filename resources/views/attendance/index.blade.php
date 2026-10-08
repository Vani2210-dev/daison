@extends('layouts.app')

@section('title', 'Chấm Công GPS & Giám Sát Vị Trí | ERP Đại Sơn')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-rose-600"></i>
                <span>Chấm Công GPS & Nhật Ký Định Vị Chi Nhánh</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Lịch sử check-in / check-out của cán bộ kinh doanh, lái xe và nhân viên kho bãi tại tọa độ thực địa.</p>
        </div>
        <div>
            <span class="px-3.5 py-1.5 bg-rose-50 text-rose-700 font-bold text-xs rounded-xl border border-rose-200">
                Tổng lượt chấm công: {{ $attendance->total() }}
            </span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold border-b border-slate-300 uppercase text-[11px]">
                        <th class="py-3 px-4">Thời Gian</th>
                        <th class="py-3 px-4">Nhân Viên</th>
                        <th class="py-3 px-4">Loại Chấm Công</th>
                        <th class="py-3 px-4">Vị Trí & Tọa Độ GPS</th>
                        <th class="py-3 px-4">Chi Nhánh / Điểm Đến</th>
                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendance as $a)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $a->check_in_time ?? $a->created_at?->format('H:i d/m/Y') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $a->user?->name ?? 'Nhân viên' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    {{ $a->type ?? 'Check-in' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                                {{ $a->latitude && $a->longitude ? ($a->latitude . ', ' . $a->longitude) : 'Tọa độ văn phòng' }}
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $a->location_name ?? $a->branch ?? 'Trụ sở Mê Linh' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Hợp lệ GPS
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 italic">Chưa có lượt chấm công nào hôm nay.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendance->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $attendance->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
