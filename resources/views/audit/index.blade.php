@extends('layouts.app')

@section('title', 'Nhật Ký Truy Vết System Audit | ERP Đại Sơn')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-sky-600"></i>
                <span>Nhật Ký Truy Vết Hệ Thống (System Audit Trail)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Ghi nhận toàn bộ thao tác thêm, sửa, đổi trạng thái chứng từ, chốt công nợ và cập nhật dữ liệu của người dùng.</p>
        </div>
        <div>
            <span class="px-3.5 py-1.5 bg-sky-50 text-sky-700 font-bold text-xs rounded-xl border border-sky-200">
                Tổng nhật ký: {{ $auditLogs->total() }} bản ghi
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
                        <th class="py-3 px-4">Người Thực Hiện</th>
                        <th class="py-3 px-4">Hành Động</th>
                        <th class="py-3 px-4">Phân Hệ</th>
                        <th class="py-3 px-4">Mã Chứng Từ</th>
                        <th class="py-3 px-4">Chi Tiết Thay Đổi</th>
                        <th class="py-3 px-4">Ghi Chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px] whitespace-nowrap">{{ $log->timestamp ?? $log->created_at?->format('H:i:s d/m/Y') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $log->performer_name ?? 'Hệ thống' }}</td>
                            <td class="py-3 px-4 font-semibold text-sky-700">{{ $log->action }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $log->module ?? 'Đơn Hàng' }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $log->target_document_code ?? '---' }}</td>
                            <td class="py-3 px-4 text-[11px]">
                                @if($log->old_value || $log->new_value)
                                    <span class="text-rose-600 line-through">{{ $log->old_value }}</span>
                                    <i class="fa-solid fa-arrow-right text-[9px] text-slate-400 mx-1"></i>
                                    <span class="text-emerald-700 font-bold">{{ $log->new_value }}</span>
                                @else
                                    <span class="text-slate-400">---</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 max-w-xs truncate">{{ $log->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 italic">Chưa có nhật ký kiểm toán nào được lưu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
