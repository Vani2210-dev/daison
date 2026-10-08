@extends('layouts.app')

@section('title', 'Sổ Quỹ, Thu Chi & Ngân Hàng | ERP Đại Sơn')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-wallet text-emerald-600"></i>
                <span>Sổ Quỹ Tiền Mặt, Ngân Hàng & Thu Chi</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Quản lý dòng tiền, phiếu thu, phiếu chi, chuyển quỹ nội bộ và quyết toán COD lái xe.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="px-3.5 py-1.5 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200">
                Tổng thu: +{{ number_format($totalThu, 0, ',', '.') }} đ
            </span>
            <span class="px-3.5 py-1.5 bg-rose-50 text-rose-700 font-bold text-xs rounded-xl border border-rose-200">
                Tổng chi: -{{ number_format($totalChi, 0, ',', '.') }} đ
            </span>
        </div>
    </div>

    <!-- Funds Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($funds as $f)
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">{{ $f->name }}</span>
                    <i class="fa-solid fa-building-columns text-sky-600"></i>
                </div>
                <div class="text-xl font-black text-slate-900">
                    {{ number_format($f->balance ?? 0, 0, ',', '.') }} đ
                </div>
                <div class="text-[11px] text-slate-400">
                    {{ $f->bank_name ? $f->bank_name . ' - ' . $f->account_number : 'Quỹ tiền mặt tại két' }}
                </div>
            </div>
        @empty
            <div class="col-span-full py-4 text-center text-slate-400 italic bg-white rounded-2xl border border-slate-200">
                Chưa có cấu hình quỹ nào.
            </div>
        @endforelse
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
            <form action="{{ url('/funds') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã phiếu, khách hàng, nội dung..." class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none w-64">
                <select name="type" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none">
                    <option value="">Tất cả loại giao dịch</option>
                    <option value="Thu" {{ request('type') === 'Thu' ? 'selected' : '' }}>Thu tiền (+)</option>
                    <option value="Chi" {{ request('type') === 'Chi' ? 'selected' : '' }}>Chi tiền (-)</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl cursor-pointer">
                    Lọc sổ quỹ
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold border-b border-slate-300 uppercase text-[11px]">
                        <th class="py-3 px-4">Mã Phiếu</th>
                        <th class="py-3 px-4">Ngày</th>
                        <th class="py-3 px-4">Loại Quỹ</th>
                        <th class="py-3 px-4">Hạng Mục</th>
                        <th class="py-3 px-4">Đối Tác / Khách Hàng</th>
                        <th class="py-3 px-4 text-right">Số Tiền</th>
                        <th class="py-3 px-4">Ghi Chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-bold font-mono text-sky-700">{{ $t->code }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $t->date ? $t->date->format('d/m/Y') : '' }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-700">{{ $t->fund?->name ?? 'Quỹ chính' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $t->type === 'Thu' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $t->category ?? $t->type }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $t->customer_partner_name }}</td>
                            <td class="py-3 px-4 text-right font-black {{ $t->type === 'Thu' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $t->type === 'Thu' ? '+' : '-' }}{{ number_format($t->amount, 0, ',', '.') }} đ
                            </td>
                            <td class="py-3 px-4 text-slate-500 max-w-xs truncate">{{ $t->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 italic">Chưa có giao dịch thu chi nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
