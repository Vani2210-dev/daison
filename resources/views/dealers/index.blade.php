@extends('layouts.app')

@section('title', 'Danh Sách Đại Lý & Khách Hàng | ERP Đại Sơn')

@section('content')
<div x-data="dealersIndexApp()" class="space-y-6 pb-20">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-store text-purple-600"></i>
                <span>Danh Sách Đại Lý & Khách Hàng</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Quản lý mạng lưới khách hàng, cấp bậc đại lý, địa giới hành chính, hạn mức và công nợ.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3.5 py-2 bg-purple-50 text-purple-800 font-bold text-xs rounded-xl border border-purple-200/60 shadow-2xs">
                Tổng: {{ $dealers->total() }} đối tác
            </span>
            <a href="{{ url('/dealers/create') }}" 
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Thêm Mới Đại Lý</span>
            </a>
        </div>
    </div>

    <!-- Filters & View Switcher Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
        <form action="{{ url('/dealers') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs font-semibold">
            <input type="hidden" name="view" :value="viewMode">

            <!-- Search -->
            <div class="sm:col-span-6 lg:col-span-5">
                <label class="block text-slate-500 mb-1 text-[11px] font-bold">Tìm kiếm đại lý / SĐT / Địa chỉ</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nhập tên đại lý, số điện thoại, mã..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-purple-500 focus:outline-none transition-all">
                </div>
            </div>

            <!-- Tier Filter -->
            <div class="sm:col-span-4 lg:col-span-4">
                <label class="block text-slate-500 mb-1 text-[11px] font-bold">Cấp bậc đại lý</label>
                <select name="tier" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-purple-500 focus:outline-none transition-all cursor-pointer">
                    <option value="">-- Tất cả cấp bậc --</option>
                    @foreach(['Đại lý Cấp 1', 'Đại lý Cấp 2', 'Bán lẻ', 'Dự án', 'Khách Dự Án', 'Thợ'] as $t)
                        <option value="{{ $t }}" {{ request('tier') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit -->
            <div class="sm:col-span-2 lg:col-span-3 flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter"></i>
                    <span>Lọc</span>
                </button>
                @if(request()->hasAny(['search', 'tier']))
                    <a href="{{ url('/dealers?view=') }}" :href="'{{ url('/dealers') }}?view=' + viewMode" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold transition-colors" title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- View Switcher Tabs (Prominent & Clear) -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-sliders text-slate-400"></i>
                    <span>Chế độ xem:</span>
                </span>
                <div class="inline-flex p-1 bg-slate-100/90 rounded-xl border border-slate-200/90 shadow-2xs">
                    <!-- Nút Xem Thẻ (Card) -->
                    <button type="button" @click="setView('card')" 
                            :class="viewMode === 'card' ? 'bg-purple-600 text-white font-extrabold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                            class="px-4 py-1.5 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-table-cells-large"></i>
                        <span>Xem Dạng Thẻ (Card)</span>
                    </button>

                    <!-- Nút Xem Bảng (Table) -->
                    <button type="button" @click="setView('table')" 
                            :class="viewMode === 'table' ? 'bg-purple-600 text-white font-extrabold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                            class="px-4 py-1.5 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-table-list"></i>
                        <span>Xem Dạng Bảng</span>
                    </button>
                </div>
            </div>

            <div class="text-xs text-slate-400">
                Hiển thị <span class="font-bold text-slate-700">{{ $dealers->count() }}</span> / <span class="font-bold text-slate-700">{{ $dealers->total() }}</span> khách hàng
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- VIEW 1: DẠNG THẺ (CARD GRID) -->
    <!-- ========================================== -->
    <div x-show="viewMode === 'card'" class="space-y-6">
        @if($dealers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($dealers as $d)
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden">
                        <!-- Top Content -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <!-- Header row: Code badge on left, Tier badge + pen on right -->
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-mono font-bold text-sky-700 bg-sky-50 border border-sky-200">
                                        {{ $d->code ?? ('DL-HN-' . str_pad($d->id, 3, '0', STR_PAD_LEFT)) }}
                                    </span>

                                    <div class="flex items-center gap-1.5">
                                        @php
                                            $tColor = match($d->tier) {
                                                'Đại lý Cấp 1' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'Đại lý Cấp 2' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'Khách Dự Án', 'Dự án' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'Thợ' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                default => 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold border {{ $tColor }}">
                                            {{ $d->tier ?? 'Đại lý Cấp 1' }}
                                        </span>
                                        <a href="{{ url('/dealers/' . $d->id . '/edit') }}" 
                                           class="text-slate-400 hover:text-sky-600 transition-colors p-0.5" 
                                           title="Chỉnh sửa nhanh">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Dealer Name -->
                                <h3 class="text-base font-extrabold text-slate-900 leading-snug tracking-tight mb-2 hover:text-purple-700 transition-colors">
                                    <a href="{{ url('/dealers/' . $d->id . '/edit') }}">
                                        {{ $d->name }}
                                    </a>
                                </h3>

                                <!-- Info Lines -->
                                <div class="space-y-1 text-xs text-slate-600">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-500">Chủ đại lý:</span>
                                        <span class="font-semibold text-slate-800">{{ $d->owner_name ?: 'Chưa cập nhật' }}</span>
                                    </div>

                                    <div class="flex items-center gap-1.5 text-slate-500 font-mono">
                                        <i class="fa-solid fa-phone text-slate-400 text-[11px]"></i>
                                        <span>{{ $d->phone ?: 'Chưa có SĐT' }}</span>
                                    </div>

                                    <div class="flex items-center gap-1.5 text-slate-600 pt-0.5">
                                        <i class="fa-solid fa-user text-slate-400 text-[11px]"></i>
                                        <span>NV phụ trách:</span>
                                        <span class="font-bold text-slate-800">{{ $d->salesRep?->name ?? 'Phạm Minh Tuấn' }}</span>
                                    </div>

                                    <div class="text-slate-600 truncate pt-0.5" title="{{ $d->address }}">
                                        {{ $d->address ?: 'Chưa cập nhật địa chỉ đường/phố' }}
                                    </div>
                                </div>

                                <!-- Sub-box: Địa giới hành chính -->
                                <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 my-3.5 text-xs space-y-1.5">
                                    <!-- Địa giới mới -->
                                    <div class="flex items-start gap-2">
                                        <i class="fa-regular fa-compass text-purple-600 text-xs mt-0.5 shrink-0"></i>
                                        <div>
                                            <span class="text-slate-600 font-medium">Địa giới mới:</span>
                                            @php
                                                $newGeo = array_filter([$d->new_ward, $d->new_district, $d->new_city]);
                                            @endphp
                                            @if(count($newGeo) > 0)
                                                <span class="font-bold text-slate-800">{{ implode(', ', $newGeo) }}</span>
                                            @else
                                                <span class="font-bold text-purple-700">Chưa cập nhật</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Địa giới cũ -->
                                    <div class="pl-5 text-[11px] text-slate-500">
                                        <span>Địa giới cũ:</span>
                                        @php
                                            $oldParts = array_filter([$d->old_ward, $d->old_district, $d->old_city]);
                                        @endphp
                                        @if(count($oldParts) > 0)
                                            <span>{{ implode(', ', $oldParts) }}</span>
                                        @else
                                            <span>—, {{ $d->district ?: ($d->city ?: 'Hà Nội') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Rows -->
                            <div class="border-t border-slate-100 pt-3 space-y-1.5 text-xs">
                                <!-- Nợ đầu kỳ -->
                                <div class="flex items-center justify-between text-slate-500">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-coins text-slate-400 text-[11px]"></i>
                                        <span>Nợ đầu kỳ:</span>
                                    </div>
                                    <div class="font-mono font-semibold text-slate-700">
                                        {{ number_format((float)($d->initial_debt ?? 0), 0, ',', '.') }} đ
                                    </div>
                                </div>

                                <!-- Hạn mức nợ -->
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">Hạn mức nợ:</span>
                                    <span class="font-mono font-bold text-slate-900 text-[13px]">
                                        {{ number_format((float)($d->credit_limit ?? 0), 0, ',', '.') }} đ
                                    </span>
                                </div>

                                <!-- Nợ hiện tại -->
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">Nợ hiện tại:</span>
                                    <span class="font-mono font-extrabold {{ (float)$d->current_debt > 0 ? 'text-slate-900' : 'text-slate-900' }} text-[13px]">
                                        {{ number_format((float)($d->current_debt ?? 0), 0, ',', '.') }} đ
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Actions -->
                        <div class="border-t border-slate-100 bg-slate-50/50 flex items-center divide-x divide-slate-100">
                            <a href="{{ url('/dealers/' . $d->id . '/edit') }}" 
                               class="flex-1 py-3 px-3 flex items-center justify-center gap-2 text-xs font-semibold text-slate-700 hover:text-purple-700 hover:bg-white transition-colors cursor-pointer">
                                <i class="fa-solid fa-pen text-slate-400 text-xs"></i>
                                <span>Chỉnh sửa thông tin</span>
                            </a>
                            <button type="button" 
                                    @click="confirmDelete('{{ $d->id }}', '{{ addslashes($d->name) }}', {{ $d->orders->count() }})" 
                                    class="px-4 py-3 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                    title="Xóa đại lý">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 italic">
                Không tìm thấy đại lý nào phù hợp.
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- VIEW 2: DẠNG BẢNG (TABLE LIST) -->
    <!-- ========================================== -->
    <div x-show="viewMode === 'table'" class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-100 font-bold">
                        <th class="py-3.5 px-4 font-bold">Mã / Tên Khách Hàng</th>
                        <th class="py-3.5 px-4 font-bold text-center">Cấp Bậc</th>
                        <th class="py-3.5 px-4 font-bold">Chủ Đại Lý & SĐT</th>
                        <th class="py-3.5 px-4 font-bold">NV Phụ Trách & Địa Chỉ</th>
                        <th class="py-3.5 px-4 font-bold">Địa Giới Hành Chính</th>
                        <th class="py-3.5 px-4 font-bold text-right">Nợ Đầu Kỳ</th>
                        <th class="py-3.5 px-4 font-bold text-right">Hạn Mức</th>
                        <th class="py-3.5 px-4 font-bold text-right">Nợ Hiện Tại</th>
                        <th class="py-3.5 px-4 font-bold text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($dealers as $d)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <a href="{{ url('/dealers/' . $d->id . '/edit') }}" class="font-extrabold text-slate-900 text-sm hover:text-purple-700 transition-colors">
                                    {{ $d->name }}
                                </a>
                                <div class="text-[10px] text-purple-700 font-mono font-bold">{{ $d->code ?? ('DL-HN-' . str_pad($d->id, 3, '0', STR_PAD_LEFT)) }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium border bg-emerald-50 text-emerald-700 border-emerald-200">
                                    {{ $d->tier ?? 'Đại lý Cấp 1' }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 font-mono">{{ $d->phone ?: '—' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $d->owner_name ?? 'Chưa cập nhật' }}</div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $d->salesRep?->name ?? 'Phạm Minh Tuấn' }}</div>
                                <div class="text-[10px] text-slate-400 truncate max-w-xs" title="{{ $d->address }}">{{ $d->address ?: 'Chưa có địa chỉ' }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-[11px]">
                                @php
                                    $newGeo = array_filter([$d->new_ward, $d->new_district, $d->new_city]);
                                    $oldParts = array_filter([$d->old_ward, $d->old_district, $d->old_city]);
                                @endphp
                                <div>
                                    <span class="text-slate-400">Mới:</span> 
                                    @if(count($newGeo) > 0)
                                        <span class="font-semibold text-slate-700">{{ implode(', ', $newGeo) }}</span>
                                    @else
                                        <span class="text-purple-600 font-medium">Chưa cập nhật</span>
                                    @endif
                                </div>
                                <div class="text-slate-400">
                                    Cũ: {{ count($oldParts) > 0 ? implode(', ', $oldParts) : ($d->district ?: ($d->city ?: 'Hà Nội')) }}
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="font-mono text-slate-600">
                                    {{ number_format((float)($d->initial_debt ?? 0), 0, ',', '.') }} đ
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="font-bold font-mono text-slate-700">
                                    {{ number_format((float)($d->credit_limit ?? 0), 0, ',', '.') }} đ
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="font-black font-mono {{ (float)$d->current_debt > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                    {{ number_format((float)($d->current_debt ?? 0), 0, ',', '.') }} đ
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Sửa -->
                                    <a href="{{ url('/dealers/' . $d->id . '/edit') }}" 
                                       class="w-8 h-8 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 flex items-center justify-center transition-all shadow-2xs" 
                                       title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>

                                    <!-- Xóa -->
                                    <button type="button" 
                                            @click="confirmDelete('{{ $d->id }}', '{{ addslashes($d->name) }}', {{ $d->orders->count() }})" 
                                            class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center transition-all shadow-2xs cursor-pointer" 
                                            title="Xóa đại lý">
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 italic">
                                Không tìm thấy đại lý nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($dealers->hasPages())
        <div class="px-6 py-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs flex justify-center">
            {{ $dealers->links() }}
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL XÁC NHẬN XÓA ĐẠI LÝ -->
    <!-- ========================================== -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div @click.outside="showDeleteModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg shrink-0">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Xác Nhận Xóa Đại Lý</h3>
                    <p class="text-xs text-slate-500">Hành động này không thể hoàn tác</p>
                </div>
            </div>

            <!-- Nếu có đơn hàng liên kết -->
            <template x-if="deleteOrderCount > 0">
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-2">
                    <div class="font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                        <span>Không thể xóa đại lý này!</span>
                    </div>
                    <p>
                        Đại lý <strong x-text="deleteDealerName"></strong> đã có <strong class="text-rose-600" x-text="deleteOrderCount"></strong> đơn hàng phát sinh trong hệ thống. Để bảo toàn số liệu kế toán và lịch sử giao dịch, bạn không thể xóa đại lý này.
                    </p>
                    <p class="text-slate-500 text-[11px]">
                        Gợi ý: Bạn có thể vào chỉnh sửa và đổi trạng thái sang "Khóa" hoặc "Đóng hoạt động".
                    </p>
                </div>
            </template>

            <!-- Nếu chưa có đơn hàng liên kết -->
            <template x-if="deleteOrderCount === 0">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1">
                    <p>Bạn có chắc chắn muốn xóa hồ sơ khách hàng / đại lý:</p>
                    <p class="font-extrabold text-slate-900 text-sm" x-text="deleteDealerName"></p>
                    <p class="text-[11px] text-slate-400 mt-1">Đại lý chưa có đơn hàng liên kết, có thể xóa an toàn khỏi danh bạ.</p>
                </div>
            </template>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                    Đóng
                </button>
                <template x-if="deleteOrderCount === 0">
                    <button type="button" @click="executeDelete()" :disabled="isDeleting"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs transition-all shadow-md shadow-rose-600/20 flex items-center gap-2 cursor-pointer disabled:opacity-50">
                        <i x-show="isDeleting" class="fa-solid fa-spinner fa-spin"></i>
                        <i x-show="!isDeleting" class="fa-solid fa-trash-can"></i>
                        <span x-text="isDeleting ? 'Đang xóa...' : 'Xác Nhận Xóa'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

</div>

<script>
function dealersIndexApp() {
    const urlParams = new URLSearchParams(window.location.search);
    const initialMode = urlParams.get('view') || localStorage.getItem('dealer_view_mode') || '{{ $currentView ?? "card" }}';

    return {
        viewMode: initialMode === 'table' ? 'table' : 'card',
        showDeleteModal: false,
        deleteDealerId: null,
        deleteDealerName: '',
        deleteOrderCount: 0,
        isDeleting: false,

        setView(mode) {
            this.viewMode = mode;
            try {
                localStorage.setItem('dealer_view_mode', mode);
                const url = new URL(window.location);
                url.searchParams.set('view', mode);
                window.history.replaceState({}, '', url);
            } catch(e) {}
        },

        confirmDelete(id, name, orderCount) {
            this.deleteDealerId = id;
            this.deleteDealerName = name;
            this.deleteOrderCount = orderCount;
            this.showDeleteModal = true;
        },

        async executeDelete() {
            if (!this.deleteDealerId || this.isDeleting) return;
            this.isDeleting = true;

            try {
                const res = await fetch('{{ url("/dealers") }}/' + this.deleteDealerId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi khi xóa đại lý');
                    this.isDeleting = false;
                }
            } catch (err) {
                console.error(err);
                alert('Lỗi kết nối khi xóa đại lý.');
                this.isDeleting = false;
            }
        }
    };
}
</script>
@endsection
