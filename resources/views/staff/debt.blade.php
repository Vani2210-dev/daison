@extends('layouts.app')

@section('title', 'Quản Lý Công Nợ Nhân Viên & Bàn Giao Nợ - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="staffDebtApp()" x-init="init()">

    <!-- ==================== HEADER ==================== -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-600 to-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span>Quản Lý Công Nợ Nhân Viên & Bàn Giao Nợ</span>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                            Phần Nhân Sự
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500">Quản lý nợ tạm ứng / nợ đơn hàng nhân viên, tài xế vận chuyển và quy trình 2 bước bàn giao nợ</p>
                </div>
            </div>
            
            <div class="flex items-center flex-wrap gap-2">
                <button type="button" @click="openHandoverModal()" class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-all cursor-pointer hover:shadow-md">
                    <i class="fa-solid fa-share-from-square"></i>
                    <span>+ Lập Bàn Giao Công Nợ</span>
                </button>
                <a href="{{ url('/debt') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors" title="Chuyển sang Quản lý công nợ khách hàng">
                    <i class="fa-solid fa-users text-blue-600"></i>
                    <span>Công Nợ Khách Hàng</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-slate-400"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ==================== KPI CARDS ==================== -->
    @if(empty($canViewAll))
        <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-900 flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold shrink-0">
                    <i class="fa-solid fa-user-lock text-xs"></i>
                </span>
                <div>
                    <span class="font-bold">Chế độ xem cá nhân:</span>
                    <span>Bạn đang xem thông tin công nợ và các biên bản bàn giao của chính mình. (Chỉ tài khoản có quyền <strong>Xem tất cả công nợ nhân viên</strong> mới xem được dữ liệu của toàn bộ nhân sự).</span>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-white text-indigo-800 border border-indigo-200 shrink-0">
                👤 Cá nhân: {{ auth()->user()->name }}
            </span>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white p-3.5 rounded-xl shadow-xs border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-money-bill-wave text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-500">{{ !empty($canViewAll) ? 'Tổng Công Nợ Nhân Sự' : 'Công Nợ Của Bạn' }}</p>
                <p class="text-base font-bold text-rose-700 font-mono mt-0.5">{{ number_format($totalStaffDebt ?? 0, 0, ',', '.') }} đ</p>
            </div>
        </div>
        <div class="bg-white p-3.5 rounded-xl shadow-xs border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-users text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-500">{{ !empty($canViewAll) ? 'Nhân Viên Đang Giữ Nợ' : 'Trạng Thái Công Nợ' }}</p>
                <p class="text-base font-bold text-slate-900 mt-0.5">{{ !empty($canViewAll) ? (($debtStaffCount ?? 0) . ' người') : (($totalStaffDebt ?? 0) > 0 ? 'Đang có nợ' : 'Không có nợ') }}</p>
            </div>
        </div>
        <div class="bg-white p-3.5 rounded-xl shadow-xs border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-500">{{ !empty($canViewAll) ? 'Biên Bản Chờ Xác Nhận' : 'Biên Bản Của Bạn Chờ Duyệt' }}</p>
                <p class="text-base font-bold text-amber-700 mt-0.5">{{ $pendingHandoversCount ?? 0 }} biên bản</p>
            </div>
        </div>
    </div>

    <!-- ==================== TABS CHỨC NĂNG ==================== -->
    <div class="bg-white p-1 rounded-xl shadow-xs border border-slate-200 flex items-center gap-1 overflow-x-auto text-xs font-bold">
        <button type="button" @click="currentMainTab = 'staff'"
                class="px-4 py-2 rounded-lg flex items-center gap-2 transition-all cursor-pointer shrink-0"
                :class="currentMainTab === 'staff' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'">
            <i class="fa-solid fa-user-tie"></i>
            <span>1. {{ !empty($canViewAll) ? 'Công Nợ Nhân Viên & Tài Xế' : 'Sổ Công Nợ Của Tôi' }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentMainTab === 'staff' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ count($staffList ?? []) }}</span>
        </button>

        <button type="button" @click="currentMainTab = 'handovers'"
                class="px-4 py-2 rounded-lg flex items-center gap-2 transition-all cursor-pointer shrink-0"
                :class="currentMainTab === 'handovers' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'">
            <i class="fa-solid fa-share-from-square"></i>
            <span>2. Biên Bản Bàn Giao Nợ (Quy trình 2 bước)</span>
            @if(($pendingHandoversCount ?? 0) > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-500 text-white animate-pulse">{{ $pendingHandoversCount }} Chờ duyệt</span>
            @else
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentMainTab === 'handovers' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ count($handovers ?? []) }}</span>
            @endif
        </button>
    </div>

    <!-- ==================== TAB 1: CÔNG NỢ NHÂN VIÊN & NGƯỜI VẬN CHUYỂN ==================== -->
    <div x-show="currentMainTab === 'staff'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 bg-slate-50/70">
            <div class="flex items-center gap-2 flex-wrap">
                @if(!empty($canViewAll))
                <input type="text" x-model="search" placeholder="Tìm theo tên, SĐT, mã NV..." 
                       class="px-3 py-1.5 text-xs rounded-lg border border-slate-300 w-60 bg-white focus:ring-1 focus:ring-sky-500 font-medium">
                <select x-model="deptFilter" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white">
                    <option value="all">Tất cả phòng ban</option>
                    <option value="Vận tải">🚚 Bộ phận Vận tải / Lái xe</option>
                    <option value="Kinh doanh">💼 Phòng Kinh Doanh</option>
                    <option value="Kho">📦 Bộ phận Kho vận</option>
                </select>
                <select x-model="staffTypeFilter" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white">
                    <option value="all">Tất cả loại nhân sự</option>
                    <option value="driver">Lái xe / Người vận chuyển</option>
                    <option value="company_sales">Sale Công Ty</option>
                    <option value="dealer_sales">Sale Đại Lý Phụ Thuộc</option>
                </select>
                @else
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5 py-1">
                    <i class="fa-solid fa-user-check text-emerald-600"></i>
                    <span>Tài khoản cá nhân: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->department ?: 'Nhân viên' }})</span>
                </span>
                @endif
            </div>
            <div class="text-xs text-slate-500">
                Hiển thị <span class="font-bold text-slate-800">{{ count($staffList ?? []) }}</span> nhân sự
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-10 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã NV</th>
                        <th class="py-2.5 px-3">Họ Tên Nhân Viên</th>
                        <th class="py-2.5 px-3">Số Điện Thoại</th>
                        <th class="py-2.5 px-3">Loại Nhân Sự / Chức Vụ</th>
                        <th class="py-2.5 px-3">Đại Lý Trực Thuộc (Nếu có)</th>
                        <th class="py-2.5 px-3 text-right">Dư Nợ Hiện Tại</th>
                        <th class="py-2.5 px-3 text-center min-w-[130px]">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($staffList ?? [] as $sIdx => $st)
                        <tr class="hover:bg-slate-50 transition-colors"
                            x-show="(deptFilter === 'all' || '{{ $st->department }}'.includes(deptFilter)) &&
                                    (staffTypeFilter === 'all' || '{{ $st->staff_type }}' === staffTypeFilter) &&
                                    (search === '' || '{{ strtolower($st->name . ' ' . $st->phone . ' ' . $st->code) }}'.includes(search.toLowerCase()))">
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $sIdx + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold whitespace-nowrap">
                                <a href="{{ url('/staff-debt/' . $st->id) }}" class="text-sky-800 hover:text-sky-950 hover:underline font-bold" title="Xem sổ chi tiết công nợ nhân sự">
                                    {{ $st->code ?: ('NV-' . str_pad($st->id, 3, '0', STR_PAD_LEFT)) }}
                                </a>
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">
                                <div>
                                    <a href="{{ url('/staff-debt/' . $st->id) }}" class="hover:text-sky-700 hover:underline transition-colors" title="Xem sổ chi tiết công nợ nhân sự">
                                        {{ $st->name }}
                                    </a>
                                </div>
                                <span class="text-[10px] text-slate-400 font-normal">{{ $st->department ?: 'Chưa phân ban' }}</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-600">{{ $st->phone ?: '---' }}</td>
                            <td class="py-2.5 px-3">
                                @if($st->staff_type === 'driver')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        🚚 Lái xe / Vận chuyển
                                    </span>
                                @elseif($st->staff_type === 'dealer_sales')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        🤝 Sale Đại Lý Phụ Thuộc
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                        💼 Sale Công Ty
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                @if($st->dealer)
                                    <span class="font-bold text-indigo-900">{{ $st->dealer->name }}</span>
                                    <div class="text-[10px] text-slate-400">Ghi nợ tự động vào ĐL này</div>
                                @else
                                    <span class="text-slate-400">---</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $st->current_debt > 0 ? 'text-rose-700' : 'text-slate-400' }}">
                                {{ number_format($st->current_debt ?? 0, 0, ',', '.') }} đ
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ url('/staff-debt/' . $st->id) }}" 
                                       class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-[11px] font-bold transition-colors cursor-pointer flex items-center gap-1" 
                                       title="Xem sổ chi tiết công nợ nhân sự (Mở trang riêng)">
                                        <i class="fa-solid fa-eye text-[10px] text-slate-500"></i>
                                        <span>Chi tiết</span>
                                    </a>
                                    <button type="button" @click="openHandoverModal({{ $st->id }})" 
                                            class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-[11px] font-bold transition-colors cursor-pointer flex items-center gap-1"
                                            title="Lập biên bản bàn giao nợ">
                                        <i class="fa-solid fa-share-from-square text-[10px]"></i>
                                        <span>Bàn giao nợ</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Không có dữ liệu nhân sự công nợ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== TAB 2: BIÊN BẢN BÀN GIAO CÔNG NỢ ==================== -->
    <div x-show="currentMainTab === 'handovers'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden" style="display: none;">
        <div class="p-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 bg-slate-50/70">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-600">Trạng thái biên bản:</span>
                <select x-model="handoverStatusFilter" class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-300 bg-white">
                    <option value="all">Tất cả biên bản</option>
                    <option value="pending_accountant">⏳ Bước 1: Chờ Kế toán duyệt chứng từ</option>
                    <option value="pending_sale">⏳ Bước 2: Chờ Sale nhận nợ</option>
                    <option value="confirmed">✅ Bước 3: Đã hoàn tất nhận nợ</option>
                    <option value="rejected">❌ Đã từ chối</option>
                </select>
            </div>
            <button type="button" @click="openHandoverModal()" class="px-3 py-1 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-lg shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Tạo Biên Bản Bàn Giao Nợ Mới</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-10 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Biên Bản</th>
                        <th class="py-2.5 px-3">Đơn Hàng</th>
                        <th class="py-2.5 px-3">Tài Xế Bàn Giao</th>
                        <th class="py-2.5 px-3">Kế Toán Kiểm Tra</th>
                        <th class="py-2.5 px-3">Sale Nhận Nợ</th>
                        <th class="py-2.5 px-3 text-center">Chứng Từ</th>
                        <th class="py-2.5 px-3 text-right">Số Tiền Nợ</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center min-w-[150px]">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($handovers ?? [] as $hIdx => $h)
                        @php
                            $isPendingAccountant = in_array($h->status, ['pending_accountant', 'pending']) && empty($h->accountant_verified_at);
                            $isPendingSale = $h->status === 'pending_sale' || ($h->status === 'pending' && !empty($h->accountant_verified_at));
                            $docs = is_array($h->documents) ? $h->documents : (is_string($h->documents) ? json_decode($h->documents, true) : []);
                            if (!is_array($docs)) $docs = [];
                        @endphp
                        <tr id="handover-row-{{ $h->id }}" data-code="{{ $h->code }}" class="hover:bg-slate-50 transition-colors"
                            x-show="handoverStatusFilter === 'all' || 
                                   (handoverStatusFilter === 'pending_accountant' && {{ $isPendingAccountant ? 'true' : 'false' }}) ||
                                   (handoverStatusFilter === 'pending_sale' && {{ $isPendingSale ? 'true' : 'false' }}) ||
                                   (handoverStatusFilter === '{{ $h->status }}')">
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $hIdx + 1 }}</td>
                            <td class="py-2.5 px-3">
                                <span class="font-mono font-bold text-sky-800">{{ $h->code }}</span>
                                <div class="text-[10px] text-slate-400">{{ $h->created_at ? $h->created_at->format('H:i d/m/Y') : '' }}</div>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-900">
                                @php
                                    $codes = array_filter(array_map('trim', explode(',', $h->order_code ?? '')));
                                @endphp
                                @if(count($codes) > 1)
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach($codes as $c)
                                            <a href="{{ url('/orders?search=' . urlencode($c)) }}" target="_blank"
                                               class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-bold bg-sky-50 text-sky-800 border border-sky-200 hover:bg-sky-100 hover:border-sky-400 transition-colors"
                                               title="Bấm để xem chi tiết đơn {{ $c }}">
                                                <i class="fa-solid fa-file-invoice text-[9px] mr-1 text-sky-600"></i>{{ $c }}
                                            </a>
                                        @endforeach
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-sans font-semibold">({{ count($codes) }} đơn hàng)</span>
                                @elseif(count($codes) === 1)
                                    @if($h->order_id)
                                        <a href="{{ url('/orders/' . $h->order_id . '/edit') }}" class="hover:underline text-sky-700 font-bold" target="_blank">{{ $codes[0] }}</a>
                                    @else
                                        <span class="font-bold">{{ $codes[0] }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 font-sans">N/A (Bàn giao tự do)</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900">{{ $h->from_user_name ?: 'Lái xe' }}</div>
                                <span class="text-[10px] text-slate-500">Giảm nợ khi hoàn tất</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-amber-900">{{ $h->accountant_name ?: ($h->accountant?->name ?: 'Kế toán') }}</div>
                                @if($h->accountant_verified_at)
                                    <span class="text-[9.5px] text-emerald-700 font-semibold flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-check-double text-[9px]"></i>
                                        <span>Đã duyệt ({{ $h->accountant_verified_at->format('H:i d/m') }})</span>
                                    </span>
                                    @if($h->accountant_notes)
                                        <div class="text-[9.5px] text-slate-500 italic truncate max-w-[120px]" title="{{ $h->accountant_notes }}">{{ $h->accountant_notes }}</div>
                                    @endif
                                @else
                                    <span class="text-[9.5px] text-amber-600 font-medium flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-clock text-[9px]"></i>
                                        <span>Chờ kiểm tra</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-indigo-900">{{ $h->to_user_name ?: 'Chờ chỉ định' }}</div>
                                @if($h->target_dealer_name)
                                    <div class="text-[10px] text-purple-700 font-semibold flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-building text-[9px]"></i>
                                        <span>ĐL: {{ $h->target_dealer_name }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if(!empty($docs))
                                    <div class="flex items-center justify-center gap-1">
                                        @foreach(array_slice($docs, 0, 2) as $dIdx => $dUrl)
                                            <img src="{{ $dUrl }}" @click="viewDocPreview('{{ $dUrl }}')"
                                                 class="w-7 h-7 rounded-lg object-cover border border-slate-300 hover:border-sky-500 cursor-pointer shadow-2xs transition-transform hover:scale-110"
                                                 title="Click để phóng to chứng từ">
                                        @endforeach
                                        @if(count($docs) > 2)
                                            <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-800 font-bold text-[10px] flex items-center justify-center cursor-pointer"
                                                  @click="viewDocPreview('{{ $docs[0] }}')" title="Xem toàn bộ chứng từ">
                                                +{{ count($docs) - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[10px] italic">Không có ảnh</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-700">
                                {{ number_format($h->amount, 0, ',', '.') }} đ
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($isPendingAccountant)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-user-clock"></i> Chờ Kế toán duyệt
                                    </span>
                                @elseif($isPendingSale)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-clock"></i> Chờ Sale nhận nợ
                                    </span>
                                @elseif($h->status === 'confirmed')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-check"></i> Đã hoàn tất nhận nợ
                                    </span>
                                    <div class="text-[9px] text-slate-400 mt-0.5">{{ $h->confirmed_by_name }} ({{ $h->confirmed_at ? $h->confirmed_at->format('H:i d/m') : '' }})</div>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-xmark"></i> Từ chối
                                    </span>
                                    <div class="text-[9px] text-rose-600 mt-0.5 truncate max-w-[120px]" title="{{ $h->rejected_reason }}">{{ $h->rejected_reason }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($isPendingAccountant)
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" 
                                                @click="openAccountantForwardModal({
                                                    id: {{ $h->id }},
                                                    code: '{{ $h->code }}',
                                                    amount: {{ (float)$h->amount }},
                                                    from_name: '{{ addslashes($h->from_user_name ?: '') }}',
                                                    to_user_id: '{{ $h->to_user_id ?: '' }}',
                                                    to_user_name: '{{ addslashes($h->to_user_name ?: '') }}',
                                                    documents: {{ json_encode($docs) }}
                                                })"
                                                class="px-2 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-[10.5px] transition-colors shadow-2xs cursor-pointer flex items-center gap-1"
                                                title="Kế toán duyệt chứng từ hợp lệ & chuyển giao nợ cho Sale">
                                            <i class="fa-solid fa-user-check"></i>
                                            <span>Duyệt & Chuyển Sale</span>
                                        </button>
                                        <button type="button" @click="rejectHandover({{ $h->id }})"
                                                class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[10.5px] transition-colors cursor-pointer"
                                                title="Từ chối chứng từ">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                @elseif($isPendingSale)
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="confirmHandover({{ $h->id }})"
                                                class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10.5px] transition-colors shadow-2xs cursor-pointer flex items-center gap-1"
                                                title="Sale xác nhận nhận nợ">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Sale Nhận Nợ</span>
                                        </button>
                                        <button type="button" @click="rejectHandover({{ $h->id }})"
                                                class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[10.5px] transition-colors cursor-pointer"
                                                title="Từ chối nhận nợ">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">Đã xử lý</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">Chưa có biên bản bàn giao công nợ nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <!-- ==================== MODAL: LẬP BIÊN BẢN BÀN GIAO CÔNG NỢ ==================== -->
    <div x-show="showHandoverModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg flex flex-col overflow-visible animate-in fade-in zoom-in-95 duration-150"
             @click.away="closeHandoverModal()">
            <div class="p-4 border-b border-slate-200 bg-sky-50/80 flex items-center justify-between">
                <h3 class="text-sm font-bold text-sky-950 flex items-center gap-2">
                    <i class="fa-solid fa-share-from-square text-sky-600"></i>
                    <span>Lập Biên Bản Bàn Giao Công Nợ (Quy trình 2 bước)</span>
                </h3>
                <button type="button" @click="closeHandoverModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="p-4 space-y-3.5 text-xs">
                <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11.5px] leading-relaxed">
                    💡 <strong>Quy trình bàn giao đơn hàng & công nợ:</strong> Khi lái xe bán đơn hàng trên xe xong (đơn đã bán / đã thu tiền), lái xe lập biên bản bàn giao đơn hàng này lại cho Nhân viên Sale hoặc Nhân viên Đại lý để tính công nợ đại lý / doanh số. Khi Bên Nhận bấm <strong>'Xác nhận nhận nợ'</strong>, trách nhiệm giữ tiền của lái xe sẽ được giảm trừ và công nợ/đơn hàng sẽ chuyển sang cho Sale/Đại lý.
                </div>

                <!-- Chọn các đơn hàng -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-700">
                            <i class="fa-solid fa-file-invoice-dollar text-sky-600 mr-1"></i>Chọn Các Đơn Hàng (Chọn được nhiều đơn):
                        </label>
                        <template x-if="handoverForm.order_ids && handoverForm.order_ids.length > 0">
                            <span class="text-[10.5px] font-bold text-sky-700 bg-sky-100 px-2 py-0.5 rounded-full border border-sky-200">
                                Đã chọn <span x-text="handoverForm.order_ids.length"></span> đơn
                            </span>
                        </template>
                    </div>

                    <!-- Hiển thị các tag đơn hàng đã chọn -->
                    <template x-if="handoverForm.order_ids && handoverForm.order_ids.length > 0">
                        <div class="flex flex-wrap gap-1.5 p-2 bg-slate-50 border border-slate-200 rounded-lg mb-2 max-h-28 overflow-y-auto">
                            <template x-for="ordId in handoverForm.order_ids" :key="ordId">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-sky-100 border border-sky-300 text-sky-900 text-xs font-mono">
                                    <strong x-text="getOrderById(ordId)?.order_code || ordId"></strong>
                                    <span class="text-[10px] text-emerald-700 font-bold" x-text="'(' + formatNumber(getOrderById(ordId)?.amount) + ' đ)'"></span>
                                    <button type="button" @click="toggleSelectOrder(ordId)" class="text-sky-500 hover:text-rose-600 ml-0.5 cursor-pointer font-bold">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </span>
                            </template>
                        </div>
                    </template>

                    <!-- Input tìm kiếm & Dropdown gợi ý đơn hàng -->
                    <div class="relative" @click.outside="showOrderDropdown = false">
                        <div class="relative">
                            <input type="text"
                                   x-model="orderSearchQuery"
                                   @focus="showOrderDropdown = true"
                                   @input="showOrderDropdown = true"
                                   placeholder="-- Gõ mã đơn, tên khách hoặc kho xe để tìm --"
                                   class="w-full pl-8 pr-8 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-xs transition-all shadow-xs">
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <button type="button" 
                                    x-show="orderSearchQuery" 
                                    @click="orderSearchQuery = ''" 
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-circle-xmark text-xs"></i>
                            </button>
                        </div>

                        <!-- Dropdown menu gợi ý đơn hàng -->
                        <div x-show="showOrderDropdown"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 max-h-64 overflow-y-auto divide-y divide-slate-100">
                            
                            <template x-for="ord in getFilteredOrders()" :key="ord.id">
                                <div @click="toggleSelectOrder(ord.id)"
                                     class="p-2.5 hover:bg-sky-50 cursor-pointer transition-colors flex items-center justify-between gap-2"
                                     :class="isOrderSelected(ord.id) ? 'bg-sky-50/70' : ''">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <input type="checkbox" :checked="isOrderSelected(ord.id)" class="rounded text-sky-600 focus:ring-sky-500 pointer-events-none">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-mono font-bold text-sky-900 text-xs" x-text="ord.order_code"></span>
                                                <template x-if="ord.is_paid">
                                                    <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">✅ Đã thanh toán</span>
                                                </template>
                                                <template x-if="!ord.is_paid">
                                                    <span class="px-1.5 py-0.5 rounded text-[9.5px] font-semibold bg-amber-50 text-amber-800 border border-amber-200" x-text="ord.payment_status || 'Chưa thanh toán'"></span>
                                                </template>
                                                <template x-if="ord.is_vehicle">
                                                    <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-sky-50 text-sky-700 border border-sky-200" x-text="'🚚 ' + (ord.warehouse || 'Kho xe')"></span>
                                                </template>
                                            </div>
                                            <div class="text-[11px] text-slate-700 font-semibold truncate mt-0.5" x-text="ord.customer_name || 'Khách lẻ'"></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5" x-text="'TT Đơn: ' + ord.status"></div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-mono font-bold text-emerald-700 text-xs" x-text="formatNumber(ord.amount) + ' đ'"></div>
                                        <div class="text-[9.5px] text-slate-400">tiền bán đơn</div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="getFilteredOrders().length === 0">
                                <div class="p-4 text-center text-slate-400 text-xs">
                                    Không tìm thấy đơn hàng nào phù hợp
                                </div>
                            </template>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-info text-sky-500"></i>
                        <span>Có thể chọn nhiều đơn. Tiền nợ sẽ <strong>tự động tính tổng</strong> và nhảy vào ô bên dưới.</span>
                    </p>
                </div>

                <!-- Người Nhận Bàn Giao Nợ -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-user-check text-sky-600 mr-1"></i>Người Nhận Bàn Giao Nợ (*):
                    </label>

                    <!-- Nếu đã chọn user -->
                    <template x-if="handoverForm.to_user_id">
                        <div class="p-2.5 bg-sky-50 border border-sky-200 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-xs" x-text="getSelectedStaff()?.name"></div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                        <span class="font-semibold text-sky-700" x-text="getStaffTypeLabel(getSelectedStaff())"></span>
                                        <template x-if="getSelectedStaff()?.phone">
                                            <span x-text="'• 📞 ' + getSelectedStaff()?.phone"></span>
                                        </template>
                                        <template x-if="getSelectedStaff()?.dealer && getSelectedStaff()?.dealer?.name">
                                            <span x-text="'• 🏢 ' + getSelectedStaff()?.dealer?.name"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="clearSelectedStaff()" class="px-2 py-1 text-[11px] rounded bg-white border border-slate-200 text-rose-600 hover:bg-rose-50 font-bold transition-colors cursor-pointer">
                                Thay đổi
                            </button>
                        </div>
                    </template>

                    <!-- Nếu chưa chọn user -->
                    <div x-show="!handoverForm.to_user_id" class="relative" @click.outside="showUserDropdown = false">
                        <div class="relative">
                            <input type="text"
                                   x-model="userSearchQuery"
                                   @focus="showUserDropdown = true"
                                   @input="showUserDropdown = true"
                                   placeholder="-- Tìm kiếm theo tên, SĐT hoặc chức vụ --"
                                   class="w-full pl-8 pr-8 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-xs transition-all shadow-xs">
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <button type="button" 
                                    x-show="userSearchQuery" 
                                    @click="userSearchQuery = ''" 
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-circle-xmark text-xs"></i>
                            </button>
                        </div>

                        <!-- Dropdown menu gợi ý nhân sự -->
                        <div x-show="showUserDropdown"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 max-h-60 overflow-y-auto divide-y divide-slate-100">
                            
                            <template x-for="st in getFilteredStaff()" :key="st.id">
                                <div @click="selectStaff(st)"
                                     class="p-2.5 hover:bg-sky-50 cursor-pointer transition-colors flex items-center justify-between gap-2">
                                    <div>
                                        <div class="font-bold text-slate-800 text-xs" x-text="st.name"></div>
                                        <div class="text-[10.5px] text-slate-500 mt-0.5">
                                            <template x-if="st.phone">
                                                <span x-text="'📞 ' + st.phone + ' '"></span>
                                            </template>
                                            <template x-if="st.dealer && st.dealer.name">
                                                <span x-text="'• 🏢 ĐL: ' + st.dealer.name"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                          :class="st.staff_type === 'dealer_sales' ? 'bg-purple-100 text-purple-800' : (st.staff_type === 'driver' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800')"
                                          x-text="getStaffTypeLabel(st)">
                                    </span>
                                </div>
                            </template>

                            <template x-if="getFilteredStaff().length === 0">
                                <div class="p-4 text-center text-slate-400 text-xs">
                                    Không tìm thấy nhân viên nào phù hợp
                                </div>
                            </template>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 mt-1">
                        Tìm kiếm nhanh Sale công ty, Sale đại lý phụ thuộc hoặc Kế toán theo tên hoặc SĐT.
                    </p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-700">
                            <i class="fa-solid fa-money-bill-wave text-rose-600 mr-1"></i>Số Tiền Công Nợ Bàn Giao (*):
                        </label>
                        <template x-if="handoverForm.amount > 0">
                            <span class="text-[11.5px] font-bold text-rose-700 font-mono bg-rose-50 px-2 py-0.5 rounded border border-rose-200" 
                                  x-text="formatNumber(handoverForm.amount) + ' đ'"></span>
                        </template>
                    </div>
                    <div class="relative">
                        <input type="number" x-model="handoverForm.amount" placeholder="VD: 5000000" class="w-full px-3 py-2 pr-12 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono font-bold text-rose-700 text-sm">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">đ</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                        <span>Tiền tự động tính tổng từ các đơn đã chọn (bạn vẫn có thể sửa tay nếu khách trả 1 phần).</span>
                    </p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <i class="fa-solid fa-note-sticky text-slate-500 mr-1"></i>Ghi Chú Bàn Giao:
                    </label>
                    <textarea x-model="handoverForm.notes" rows="2" placeholder="VD: Bàn giao nợ sau khi giao hàng, khách hẹn thanh toán cho Sale vào ngày 15/10..." class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500"></textarea>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs">
                <button type="button" @click="closeHandoverModal()" class="px-4 py-1.5 rounded-lg border border-slate-300 font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                    Hủy
                </button>
                <button type="button" @click="submitHandover()" class="px-4 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 font-bold text-white shadow-xs cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Gửi Yêu Cầu Bàn Giao</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
@php
    $targetHJson = isset($targetHandover) && $targetHandover ? [
        'id' => $targetHandover->id,
        'code' => $targetHandover->code,
        'amount' => (float)$targetHandover->amount,
        'from_name' => $targetHandover->from_user_name ?: '',
        'to_user_id' => $targetHandover->to_user_id ?: '',
        'to_user_name' => $targetHandover->to_user_name ?: '',
        'status' => $targetHandover->status,
        'documents' => is_array($targetHandover->documents) ? $targetHandover->documents : (is_string($targetHandover->documents) ? json_decode($targetHandover->documents, true) : [])
    ] : null;
@endphp
function staffDebtApp() {
    return {
        currentMainTab: 'staff', // 'staff', 'handovers', 'vansales'
        targetHandoverData: @json($targetHJson ?? null),

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            const targetId = urlParams.get('handover_id') || urlParams.get('id');
            const targetCode = urlParams.get('handover_code') || urlParams.get('code');

            if (targetId || targetCode || this.targetHandoverData) {
                this.currentMainTab = 'handovers';
                this.handoverStatusFilter = 'all';

                setTimeout(() => {
                    let el = targetId ? document.getElementById(`handover-row-${targetId}`) : null;
                    if (!el && targetCode) {
                        el = document.querySelector(`tr[data-code="${targetCode}"]`);
                    }
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('bg-amber-100', 'ring-4', 'ring-amber-400');
                        setTimeout(() => {
                            el.classList.remove('ring-4', 'ring-amber-400');
                        }, 4000);
                    }

                    if (this.targetHandoverData && ['pending_accountant', 'pending'].includes(this.targetHandoverData.status)) {
                        this.openAccountantForwardModal(this.targetHandoverData);
                    }
                }, 300);
            }
        },
        search: '',
        deptFilter: 'all',
        staffTypeFilter: 'all',
        handoverStatusFilter: 'all',

        // Modal Bàn giao công nợ
        showHandoverModal: false,
        handoverForm: {
            order_ids: [],
            order_codes: [],
            order_code: '',
            to_user_id: '',
            amount: '',
            notes: ''
        },
        orderSearchQuery: '',
        userSearchQuery: '',
        showOrderDropdown: false,
        showUserDropdown: false,
        allOrders: @json($allOrders ?? []),
        allStaff: @json($allStaff ?? []),

        openHandoverModal(prefillUserId = null) {
            this.showHandoverModal = true;
            this.orderSearchQuery = '';
            this.userSearchQuery = '';
            this.showOrderDropdown = false;
            this.showUserDropdown = false;
            if (prefillUserId) {
                this.handoverForm.to_user_id = String(prefillUserId);
            }
        },

        closeHandoverModal() {
            this.showHandoverModal = false;
            this.handoverForm = {
                order_ids: [],
                order_codes: [],
                order_code: '',
                to_user_id: '',
                amount: '',
                notes: ''
            };
            this.orderSearchQuery = '';
            this.userSearchQuery = '';
            this.showOrderDropdown = false;
            this.showUserDropdown = false;
        },

        getOrderById(id) {
            return this.allOrders.find(o => String(o.id) === String(id));
        },

        isOrderSelected(id) {
            return this.handoverForm.order_ids.some(selectedId => String(selectedId) === String(id));
        },

        toggleSelectOrder(orderId) {
            const strId = String(orderId);
            const idx = this.handoverForm.order_ids.findIndex(id => String(id) === strId);
            if (idx > -1) {
                this.handoverForm.order_ids.splice(idx, 1);
            } else {
                this.handoverForm.order_ids.push(orderId);
            }
            this.recalculateHandoverOrders();
        },

        recalculateHandoverOrders() {
            const selected = this.allOrders.filter(o => this.isOrderSelected(o.id));
            this.handoverForm.order_codes = selected.map(o => o.order_code);
            this.handoverForm.order_code = this.handoverForm.order_codes.join(', ');

            if (selected.length > 0) {
                const total = selected.reduce((sum, ord) => sum + Number(ord.amount || 0), 0);
                this.handoverForm.amount = total;
            } else {
                this.handoverForm.amount = '';
            }
        },

        getFilteredOrders() {
            if (!this.orderSearchQuery) {
                return this.allOrders.slice(0, 30);
            }
            const q = this.orderSearchQuery.toLowerCase().trim();
            return this.allOrders.filter(o => {
                const code = (o.order_code || '').toLowerCase();
                const cust = (o.customer_name || '').toLowerCase();
                const wh = (o.warehouse || '').toLowerCase();
                return code.includes(q) || cust.includes(q) || wh.includes(q);
            }).slice(0, 30);
        },

        getSelectedStaff() {
            if (!this.handoverForm.to_user_id) return null;
            return this.allStaff.find(s => String(s.id) === String(this.handoverForm.to_user_id));
        },

        selectStaff(st) {
            this.handoverForm.to_user_id = String(st.id);
            this.userSearchQuery = '';
            this.showUserDropdown = false;
        },

        clearSelectedStaff() {
            this.handoverForm.to_user_id = '';
            this.userSearchQuery = '';
        },

        getStaffTypeLabel(st) {
            if (!st) return '';
            if (st.staff_type === 'dealer_sales') return 'Sale Đại Lý Phụ Thuộc';
            if (st.staff_type === 'driver') return 'Lái xe / Vận chuyển';
            return 'Sale Công Ty';
        },

        getFilteredStaff() {
            if (!this.userSearchQuery) {
                return this.allStaff.slice(0, 30);
            }
            const q = this.userSearchQuery.toLowerCase().trim();
            return this.allStaff.filter(st => {
                const name = (st.name || '').toLowerCase();
                const phone = (st.phone || '').toLowerCase();
                const code = (st.code || '').toLowerCase();
                const dealer = (st.dealer && st.dealer.name ? st.dealer.name : '').toLowerCase();
                return name.includes(q) || phone.includes(q) || code.includes(q) || dealer.includes(q);
            }).slice(0, 30);
        },

        formatNumber(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0));
        },

        formatDate(dateStr) {
            if (!dateStr) return '---';
            const d = new Date(dateStr);
            return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('vi-VN');
        },

        async submitHandover() {
            if (!this.handoverForm.to_user_id) {
                alert('Vui lòng chọn người nhận bàn giao nợ!');
                return;
            }
            if (!this.handoverForm.amount || Number(this.handoverForm.amount) <= 0) {
                alert('Vui lòng chọn đơn hàng hoặc nhập số tiền công nợ hợp lệ!');
                return;
            }

            try {
                const res = await fetch('/debt-handovers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.handoverForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi lập biên bản bàn giao');
                }
            } catch(e) {
                alert('Lỗi kết nối máy chủ!');
            }
        },

        showDocPreviewModal: false,
        previewDocUrl: null,
        showAccountantForwardModal: false,
        isSubmittingForward: false,
        forwardData: {
            id: null,
            code: '',
            amount: 0,
            from_name: '',
            to_user_id: '',
            accountant_notes: 'Đã kiểm tra chứng từ hợp lệ, phiếu giao có đầy đủ chữ ký khách hàng.',
            documents: []
        },

        viewDocPreview(url) {
            this.previewDocUrl = url;
            this.showDocPreviewModal = true;
        },

        openAccountantForwardModal(h) {
            this.forwardData.id = h.id;
            this.forwardData.code = h.code;
            this.forwardData.amount = h.amount;
            this.forwardData.from_name = h.from_name;
            this.forwardData.to_user_id = h.to_user_id || '';
            this.forwardData.accountant_notes = 'Đã kiểm tra chứng từ hợp lệ, phiếu giao có đầy đủ chữ ký khách hàng.';
            this.forwardData.documents = h.documents || [];
            this.showAccountantForwardModal = true;
        },

        async submitAccountantForward() {
            if (!this.forwardData.to_user_id) {
                alert('Vui lòng chọn Nhân viên Kinh doanh (Sale) nhận nợ!');
                return;
            }

            this.isSubmittingForward = true;
            try {
                const res = await fetch(`/debt-handovers/${this.forwardData.id}/forward`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        to_user_id: this.forwardData.to_user_id,
                        accountant_notes: this.forwardData.accountant_notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi khi duyệt chứng từ');
                }
            } catch(e) {
                alert('Lỗi kết nối máy chủ!');
            } finally {
                this.isSubmittingForward = false;
            }
        },

        async confirmHandover(id) {
            if (!confirm('Bạn có chắc chắn muốn XÁC NHẬN NHẬN NỢ cho biên bản này? Công nợ sẽ được chuyển chính thức.')) return;
            try {
                const res = await fetch(`/debt-handovers/${id}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Không thể xác nhận');
                }
            } catch(e) {
                alert('Lỗi kết nối máy chủ!');
            }
        },

        async rejectHandover(id) {
            const reason = prompt('Nhập lý do từ chối nhận nợ (nếu có):');
            if (reason === null) return;
            try {
                const res = await fetch(`/debt-handovers/${id}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ reason: reason })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Không thể từ chối');
                }
            } catch(e) {
                alert('Lỗi kết nối máy chủ!');
            }
        }
    };
}
</script>
    <!-- ==================== MODAL: KẾ TOÁN DUYỆT CHỨNG TỪ & CHUYỂN SALE ==================== -->
    <div x-show="showAccountantForwardModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.away="showAccountantForwardModal = false">
            <div class="p-4 border-b border-slate-200 bg-amber-50/80 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Kế Toán Duyệt Chứng Từ & Chuyển Nợ</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Bước 2: Đối soát chứng từ có chữ ký khách trước khi giao nợ cho Sale</p>
                    </div>
                </div>
                <button type="button" @click="showAccountantForwardModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1 cursor-pointer">&times;</button>
            </div>
            
            <div class="p-4 space-y-3.5 text-xs">
                <!-- Thông tin biên bản -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Mã biên bản:</span>
                        <span class="font-mono font-bold text-sky-800" x-text="forwardData.code"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Tài xế bàn giao:</span>
                        <span class="font-bold text-slate-900" x-text="forwardData.from_name"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Tổng tiền nợ:</span>
                        <span class="font-mono font-black text-rose-700 text-sm" x-text="formatNumber(forwardData.amount) + ' đ'"></span>
                    </div>
                </div>

                <!-- Danh sách chứng từ ảnh đính kèm -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5 text-sky-900">
                            <i class="fa-solid fa-images text-sky-600"></i>
                            <span>Chứng từ ảnh đính kèm (Phiếu giao / Chữ ký khách):</span>
                        </span>
                        <span class="text-[10.5px] font-bold text-sky-700" x-text="(forwardData.documents?.length || 0) + ' ảnh'"></span>
                    </label>
                    <template x-if="forwardData.documents && forwardData.documents.length > 0">
                        <div class="grid grid-cols-3 gap-2 p-2 bg-slate-50 rounded-xl border border-slate-200">
                            <template x-for="(doc, idx) in forwardData.documents" :key="idx">
                                <div class="relative group rounded-lg overflow-hidden border border-slate-300 aspect-square shadow-2xs">
                                    <img :src="doc" class="w-full h-full object-cover cursor-pointer hover:opacity-90"
                                         @click="viewDocPreview(doc)" title="Bấm để xem ảnh to">
                                    <div class="absolute bottom-0 inset-x-0 bg-slate-900/60 text-white text-[9px] py-0.5 text-center font-bold">
                                        Ảnh <span x-text="idx + 1"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!forwardData.documents || forwardData.documents.length === 0">
                        <div class="p-3 rounded-xl border border-dashed border-slate-300 text-center text-slate-400 text-xs">
                            Không có ảnh chứng từ đính kèm
                        </div>
                    </template>
                </div>

                <!-- Chọn Sale nhận nợ -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        <i class="fa-solid fa-user-tie text-purple-600 mr-1"></i>Chọn Nhân Viên Kinh Doanh (Sale) Nhận Nợ (*):
                    </label>
                    <select x-model="forwardData.to_user_id"
                            class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-amber-600 bg-white">
                        <option value="">-- Bắt buộc chọn Sale nhận nợ --</option>
                        <template x-for="st in allStaff.filter(s => s.staff_type === 'company_sales' || s.staff_type === 'dealer_sales' || (s.department && s.department.includes('Kinh doanh')))" :key="st.id">
                            <option :value="st.id" x-text="st.name + ' (' + (st.phone ? st.phone + ' - ' : '') + getStaffTypeLabel(st) + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Ghi chú của Kế toán -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Ghi chú đối soát của Kế toán:</label>
                    <textarea rows="2" x-model="forwardData.accountant_notes"
                              placeholder="VD: Đã kiểm tra chứng từ hợp lệ, phiếu giao có đầy đủ chữ ký nhận nợ..."
                              class="w-full p-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 bg-white"></textarea>
                </div>
            </div>

            <div class="p-3 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-2">
                <button type="button" @click="showAccountantForwardModal = false"
                        class="px-3.5 py-1.5 text-slate-600 font-bold hover:bg-slate-200 rounded-lg cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitAccountantForward()" :disabled="isSubmittingForward"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg cursor-pointer shadow-xs disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane text-amber-200"></i>
                    <span x-text="isSubmittingForward ? 'Đang duyệt...' : 'Duyệt Chứng Từ & Chuyển Cho Sale'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal xem trước ảnh chứng từ phóng to -->
    <div x-show="showDocPreviewModal" x-cloak
         class="fixed inset-0 z-[12000] bg-black/90 backdrop-blur-md flex items-center justify-center p-3"
         @click="showDocPreviewModal = false">
        <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center" @click.stop>
            <button type="button" @click="showDocPreviewModal = false"
                    class="absolute -top-10 right-0 text-white/80 hover:text-white text-3xl font-bold cursor-pointer">
                &times;
            </button>
            <img :src="previewDocUrl" class="max-w-full max-h-[85vh] rounded-2xl object-contain shadow-2xl border border-white/20">
        </div>
    </div>
@endsection
