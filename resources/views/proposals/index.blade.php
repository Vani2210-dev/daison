@extends('layouts.app')

@section('title', 'Đề Xuất & Phê Duyệt Trình Ký | ERP Đại Sơn')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-circle-check text-purple-600"></i>
                <span>Duyệt Đề Xuất & Trình Ký Kế Toán / Ban Giám Đốc</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Quản lý quy trình phê duyệt chi hộ, giảm giá đặc biệt, xin cấp vật tư và thanh toán công nợ.</p>
        </div>
        <div>
            <span class="px-3.5 py-1.5 bg-purple-50 text-purple-700 font-bold text-xs rounded-xl border border-purple-200">
                Tổng cộng: {{ $proposals->total() }} đề xuất
            </span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold border-b border-slate-300 uppercase text-[11px]">
                        <th class="py-3 px-4">Mã Đề Xuất</th>
                        <th class="py-3 px-4">Tiêu Đề / Nội Dung</th>
                        <th class="py-3 px-4">Người Tạo</th>
                        <th class="py-3 px-4 text-right">Số Tiền (Nếu có)</th>
                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                        <th class="py-3 px-4">Ngày Gửi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($proposals as $p)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-sky-700">{{ $p->code ?? ('DX-' . $p->id) }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900 max-w-sm">
                                <div>{{ $p->title ?? 'Đề xuất duyệt chi' }}</div>
                                <div class="text-[11px] text-slate-400 font-normal truncate">{{ $p->content ?? $p->description }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $p->creator?->name ?? 'Nhân viên' }}</td>
                            <td class="py-3 px-4 text-right font-black text-slate-800">
                                {{ $p->amount ? number_format($p->amount, 0, ',', '.') . ' đ' : '---' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @php
                                    $pBadge = match($p->status) {
                                        'Đã duyệt' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Từ chối' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-amber-50 text-amber-700 border-amber-200'
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $pBadge }}">
                                    {{ $p->status ?? 'Chờ duyệt' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $p->created_at?->format('d/m/Y') ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 italic">Chưa có đề xuất nào cần xử lý.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($proposals->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $proposals->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
