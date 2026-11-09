    <!-- Section 2: General Tasks / Phân Công Công Việc Chung -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-tasks text-indigo-600"></i>
                    <span>Nhiệm Vụ & Phân Công Công Việc</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $tasks->total() }} công việc
                    </span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Tiến độ giao việc, hạn chót hoàn thành và báo cáo công việc nội bộ.</p>
            </div>
        </div>

        <div class="tasks-table-wrapper overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase text-[11px]">
                        <th class="py-3 px-4">Tên Công Việc</th>
                        <th class="py-3 px-4">Người Giao</th>
                        <th class="py-3 px-4">Người Thực Hiện</th>
                        <th class="py-3 px-4">Thời Hạn</th>
                        <th class="py-3 px-4 text-center">Tiến Độ</th>
                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tasks as $tk)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    @if(str_contains($tk->category ?? '', 'water'))
                                        <i class="fa-solid fa-faucet-drip text-blue-500 text-xs"></i>
                                    @else
                                        <i class="fa-solid fa-clipboard-check text-slate-400 text-xs"></i>
                                    @endif
                                    <span>{{ $tk->title ?? 'Nhiệm vụ công việc' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-normal truncate max-w-sm mt-0.5">
                                    {{ $tk->description }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $tk->creator?->name ?? 'Ban Giám Đốc' }}</td>
                            <td class="py-3 px-4 font-semibold text-sky-700">{{ $tk->assignedTo?->name ?? ($tk->assigned_to_name ?? 'Phòng Kỹ Thuật') }}</td>
                            <td class="py-3 px-4 text-slate-600 font-mono">{{ $tk->deadline ? date('d/m/Y', strtotime($tk->deadline)) : '---' }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 bg-slate-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-blue-600 h-full rounded-full" style="width: {{ $tk->progress_percent ?? ($tk->progress ?? 100) }}%"></div>
                                    </div>
                                    <span class="font-bold text-slate-700 text-[11px]">{{ $tk->progress_percent ?? ($tk->progress ?? 100) }}%</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ in_array($tk->status, ['Hoàn thành', 'completed']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ $tk->status ?? 'Đang thực hiện' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">Chưa có nhiệm vụ nào được tạo.</td>
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