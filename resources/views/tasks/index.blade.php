@extends('layouts.app')

@section('title', 'Quản Lý Công Việc & Nhiệm Vụ | ERP Đại Sơn')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-list-check text-blue-600"></i>
                <span>Giao Việc & Quản Lý Tiến Độ Công Việc</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Theo dõi phân công nhiệm vụ, thời hạn hoàn thành (deadline) và kết quả triển khai công việc.</p>
        </div>
        <div>
            <span class="px-3.5 py-1.5 bg-blue-50 text-blue-700 font-bold text-xs rounded-xl border border-blue-200">
                Tổng cộng: {{ $tasks->total() }} công việc
            </span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold border-b border-slate-300 uppercase text-[11px]">
                        <th class="py-3 px-4">Tên Công Việc</th>
                        <th class="py-3 px-4">Người Giao</th>
                        <th class="py-3 px-4">Người Thực Hiện</th>
                        <th class="py-3 px-4">Thời Hạn (Deadline)</th>
                        <th class="py-3 px-4 text-center">Tiến Độ</th>
                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tasks as $tk)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <div>{{ $tk->title ?? 'Nhiệm vụ bán hàng / kho' }}</div>
                                <div class="text-[11px] text-slate-400 font-normal truncate max-w-sm">{{ $tk->description }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $tk->creator?->name ?? 'Ban Giám Đốc' }}</td>
                            <td class="py-3 px-4 font-semibold text-sky-700">{{ $tk->assigned_to_name ?? 'Phòng Kinh Doanh' }}</td>
                            <td class="py-3 px-4 text-slate-600 font-mono">{{ $tk->deadline ? date('d/m/Y', strtotime($tk->deadline)) : '---' }}</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $tk->progress ?? 100 }}%</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $tk->status ?? 'Đang thực hiện' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 italic">Chưa có công việc nào được tạo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
