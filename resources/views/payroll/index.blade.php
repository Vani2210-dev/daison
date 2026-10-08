@extends('layouts.app')

@section('title', 'Bảng Lương Nhân Sự & Quyết Toán | ERP Đại Sơn')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-money-check-dollar text-emerald-600"></i>
                <span>Bảng Lương Cán Bộ Nhân Viên & Quyết Toán Thu Nhập</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Tổng hợp ngày công, lương cơ bản, phụ cấp, hoa hồng doanh số bán hàng và thực lĩnh hàng tháng.</p>
        </div>
        <div>
            <span class="px-3.5 py-1.5 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200">
                Tháng {{ date('m/Y') }}
            </span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold border-b border-slate-300 uppercase text-[11px]">
                        <th class="py-3 px-4">Nhân Viên</th>
                        <th class="py-3 px-4">Mã NV</th>
                        <th class="py-3 px-4 text-center">Ngày Công</th>
                        <th class="py-3 px-4 text-right">Lương Cơ Bản</th>
                        <th class="py-3 px-4 text-right">Hoa Hồng / Thưởng</th>
                        <th class="py-3 px-4 text-right">Thực Lĩnh</th>
                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payroll as $pr)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $pr->employee_name ?? 'Nhân viên' }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $pr->employee_code ?? 'NV-01' }}</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $pr->working_days ?? 26 }} ngày</td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-800">{{ number_format($pr->base_salary ?? 8000000, 0, ',', '.') }} đ</td>
                            <td class="py-3 px-4 text-right font-semibold text-emerald-600">+{{ number_format($pr->bonus_amount ?? 1500000, 0, ',', '.') }} đ</td>
                            <td class="py-3 px-4 text-right font-black text-emerald-700 text-sm">{{ number_format($pr->net_salary ?? 9500000, 0, ',', '.') }} đ</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $pr->status ?? 'Đã chốt lương' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 italic">Bảng lương tháng đang được tính toán.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payroll->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payroll->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
