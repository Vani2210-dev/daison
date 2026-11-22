@extends('layouts.app')

@section('title', 'Kiểm Đếm & Duyệt Tiền COD Từ Lái Xe | ERP Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="codVerificationApp()">

    <!-- 1. Sub-Section Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto">
        <a href="{{ url('/orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-box text-sm"></i>
            <span>Đơn hàng</span>
        </a>

        <a href="{{ url('/orders/create') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-file-circle-plus text-sm"></i>
            <span>Báo giá đơn hàng</span>
        </a>

        <a href="{{ url('/orders/cod-verification') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-emerald-600 text-white shadow-md shadow-emerald-600/20">
            <i class="fa-solid fa-money-bill-transfer text-sm"></i>
            <span>Kiểm COD</span>
            @if($totalPendingCount > 0)
                <span class="px-2 py-0.5 bg-rose-500 text-white rounded-full text-[10px] font-bold animate-pulse">{{ $totalPendingCount }}</span>
            @endif
        </a>

        <a href="{{ url('/orders?tab=returns') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-rotate-left text-sm"></i>
            <span>Khách trả hàng</span>
        </a>
    </div>

    <!-- 2. Header Banner -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/orders') }}" class="w-10 h-10 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/20 cursor-pointer transition-transform hover:scale-105" title="Quay lại Đơn hàng">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Kiểm Đếm & Duyệt Tiền COD Từ Lái Xe</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-extrabold border border-emerald-200">Bán hàng & Kế toán</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Kiểm soát tiền mặt thu hộ từ tài xế các chuyến xe, đối soát đơn hàng, nộp quỹ tiền mặt & giải phóng đơn trên xe.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="window.location.reload()" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-emerald-600"></i>
                <span>Làm mới</span>
            </button>
            <a href="{{ url('/delivery-execution') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-truck-fast text-amber-400"></i>
                <span>Giao diện tài xế</span>
            </a>
        </div>
    </div>

    <!-- 3. Top Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Card 1: Chờ duyệt -->
        <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 rounded-2xl p-4 border border-amber-200 shadow-2xs space-y-2">
            <div class="flex items-center justify-between text-amber-800 font-bold text-xs">
                <span>Chờ Kế Toán Duyệt COD</span>
                <div class="w-8 h-8 rounded-xl bg-amber-200/80 text-amber-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-amber-950 font-mono tracking-tight">
                    {{ number_format($totalPendingAmount, 0, ',', '.') }} đ
                </div>
                <div class="text-[11px] text-amber-700 font-bold mt-1 flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-md bg-amber-200 text-amber-900 font-black">{{ $totalPendingCount }} biên bản</span>
                    <span>cần kiểm đếm nộp quỹ</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Đã duyệt hôm nay -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50/50 rounded-2xl p-4 border border-emerald-200 shadow-2xs space-y-2">
            <div class="flex items-center justify-between text-emerald-800 font-bold text-xs">
                <span>Đã Duyệt Thu Quỹ Hôm Nay</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-200/80 text-emerald-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-emerald-950 font-mono tracking-tight">
                    {{ number_format($approvedTodayAmount, 0, ',', '.') }} đ
                </div>
                <div class="text-[11px] text-emerald-700 font-bold mt-1 flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-md bg-emerald-200 text-emerald-900 font-black">{{ $approvedTodayCount }} biên bản</span>
                    <span>đã hoàn tất giải phóng xe</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Tổng thu tháng này -->
        <div class="bg-gradient-to-br from-indigo-50 to-blue-50/50 rounded-2xl p-4 border border-indigo-200 shadow-2xs space-y-2">
            <div class="flex items-center justify-between text-indigo-800 font-bold text-xs">
                <span>Tổng COD Tháng {{ now()->format('m/Y') }}</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-200/80 text-indigo-900 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-indigo-950 font-mono tracking-tight">
                    {{ number_format($totalMonthAmount, 0, ',', '.') }} đ
                </div>
                <div class="text-[11px] text-indigo-700 font-medium mt-1">
                    Đã hạch toán vào Quỹ tiền mặt
                </div>
            </div>
        </div>

        <!-- Card 4: Hướng dẫn nghiệp vụ -->
        <div class="bg-gradient-to-br from-slate-50 to-sky-50/40 rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-1 text-xs">
            <div class="flex items-center gap-1.5 text-sky-800 font-bold">
                <i class="fa-solid fa-shield-halved text-sky-600"></i>
                <span>Quy Trình Kiểm Soát COD</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed">
                1. Lái xe bàn giao tiền mặt & lập biên bản.<br>
                2. Kế toán kiểm đếm tiền thực nhận.<br>
                3. Bấm <strong>Duyệt</strong> để sinh Phiếu thu quỹ & giải phóng đơn trên xe.
            </p>
        </div>
    </div>

    <!-- 4. Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-bold">
            <a href="{{ url('/orders/cod-verification?status=all' . ($search ? '&search=' . urlencode($search) : '')) }}"
               class="px-3 py-2 rounded-xl transition-all whitespace-nowrap {{ $status === 'all' || !$status ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Tất cả
            </a>
            <a href="{{ url('/orders/cod-verification?status=' . urlencode('Chờ kế toán duyệt') . ($search ? '&search=' . urlencode($search) : '')) }}"
               class="px-3 py-2 rounded-xl transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Chờ kế toán duyệt' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                <i class="fa-solid fa-hourglass-half"></i>
                <span>Chờ kế toán duyệt</span>
                @if($totalPendingCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full {{ $status === 'Chờ kế toán duyệt' ? 'bg-white text-amber-600' : 'bg-rose-500 text-white' }} text-[10px] font-extrabold">{{ $totalPendingCount }}</span>
                @endif
            </a>
            <a href="{{ url('/orders/cod-verification?status=' . urlencode('Đã hoàn thành') . ($search ? '&search=' . urlencode($search) : '')) }}"
               class="px-3 py-2 rounded-xl transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Đã hoàn thành' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                <i class="fa-solid fa-check-double"></i>
                <span>Đã duyệt / Hoàn tất</span>
            </a>
            <a href="{{ url('/orders/cod-verification?status=' . urlencode('Đã từ chối') . ($search ? '&search=' . urlencode($search) : '')) }}"
               class="px-3 py-2 rounded-xl transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'Đã từ chối' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-rose-50 text-rose-800 border border-rose-200 hover:bg-rose-100' }}">
                <i class="fa-solid fa-xmark"></i>
                <span>Đã từ chối</span>
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ url('/orders/cod-verification') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative w-full md:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo mã BB, xe, lái xe, kế toán..."
                       class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 focus:outline-none focus:border-emerald-600 bg-white">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs cursor-pointer">
                Lọc
            </button>
            @if($search)
                <a href="{{ url('/orders/cod-verification?status=' . urlencode($status)) }}" class="px-3 py-2 text-slate-500 hover:text-slate-700 text-xs font-bold">
                    Xóa lọc
                </a>
            @endif
        </form>
    </div>

    <!-- 5. Main Table of Cash Handovers -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <!-- Desktop Table Layout -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <th class="py-3 px-4">Mã Biên Bản</th>
                        <th class="py-3 px-4">Xe & Lái Xe</th>
                        <th class="py-3 px-4">Kế Toán Chỉ Định</th>
                        <th class="py-3 px-4 text-right">Số Tiền Nộp COD</th>
                        <th class="py-3 px-4 text-center">Đơn Hàng Giao</th>
                        <th class="py-3 px-4">Ghi Chú Tài Xế</th>
                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                        <th class="py-3 px-4 text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($handovers as $h)
                    @php
                        $codes = is_array($h->order_codes) ? $h->order_codes : (json_decode($h->order_codes, true) ?: []);
                        $isPending = ($h->status === 'Chờ kế toán duyệt');
                        $isApproved = ($h->status === 'Đã hoàn thành');
                        $isRejected = ($h->status === 'Đã từ chối');
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $isPending ? 'bg-amber-50/20' : '' }}">
                        <!-- Mã Biên Bản -->
                        <td class="py-3.5 px-4 font-mono font-extrabold text-slate-900">
                            <div class="text-sm font-black text-indigo-700">{{ $h->code }}</div>
                            <div class="text-[10px] text-slate-400 font-sans mt-0.5">
                                {{ $h->created_at ? $h->created_at->format('H:i d/m/Y') : '' }}
                            </div>
                        </td>

                        <!-- Xe & Lái Xe -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-900 font-mono font-black text-xs border border-amber-300">
                                    {{ $h->vehicle_plate ?: 'Chưa gán xe' }}
                                </span>
                            </div>
                            <div class="font-bold text-slate-800 text-xs mt-1">
                                <i class="fa-solid fa-user-tie text-[10px] text-slate-400 mr-1"></i>{{ $h->driver_name ?: 'Lái xe' }}
                                @if($h->copilot_name)
                                    <span class="text-slate-400 font-normal text-[11px]"> & {{ $h->copilot_name }} (Phụ xe)</span>
                                @endif
                            </div>
                        </td>

                        <!-- Kế Toán Chỉ Định -->
                        <td class="py-3.5 px-4">
                            <div class="font-extrabold text-slate-800 text-xs flex items-center gap-1.5">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-[10px]">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <span>{{ $h->accountant_name ?: 'Chưa chỉ định' }}</span>
                            </div>
                            @if($isApproved && $h->approved_by_name)
                                <div class="text-[10px] text-emerald-700 font-medium mt-1">
                                    ✓ Đã duyệt bởi: <strong>{{ $h->approved_by_name }}</strong> ({{ $h->approved_at ? $h->approved_at->format('H:i d/m') : '' }})
                                </div>
                            @endif
                            @if($isRejected && $h->rejected_by_name)
                                <div class="text-[10px] text-rose-600 font-medium mt-1">
                                    ✗ Từ chối bởi: <strong>{{ $h->rejected_by_name }}</strong>
                                </div>
                            @endif
                        </td>

                        <!-- Số Tiền Nộp COD -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="text-base font-black font-mono {{ $isPending ? 'text-amber-900' : ($isApproved ? 'text-emerald-700' : 'text-slate-500') }}">
                                {{ number_format((float) $h->handover_amount, 0, ',', '.') }} đ
                            </div>
                            <div class="text-[10px] text-slate-400 font-medium">
                                {{ $h->payment_method ?: 'Tiền mặt' }}
                            </div>
                        </td>

                        <!-- Đơn Hàng Giao -->
                        <td class="py-3.5 px-4 text-center">
                            <button type="button" @click="openOrderDetailsModal({{ json_encode($h) }})"
                                    class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 rounded-lg font-bold text-xs cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-boxes-packing text-sky-600"></i>
                                <span>{{ count($codes) }} đơn hàng</span>
                            </button>
                        </td>

                        <!-- Ghi Chú Tài Xế -->
                        <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                            <div class="line-clamp-2 text-xs">
                                {{ $h->notes ?: '—' }}
                            </div>
                            @php
                                $hDocs = is_array($h->documents) ? $h->documents : (json_decode($h->documents, true) ?: []);
                            @endphp
                            @if(!empty($hDocs))
                                <div class="mt-1.5">
                                    <button type="button" @click="openDocsModal({{ json_encode($h) }})"
                                            class="px-2 py-0.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[10.5px] border border-indigo-200 inline-flex items-center gap-1 cursor-pointer transition-colors shadow-2xs">
                                        <i class="fa-solid fa-paperclip text-indigo-500"></i>
                                        <span>{{ count($hDocs) }} ảnh/file chứng từ</span>
                                    </button>
                                </div>
                            @endif
                            @if($isRejected && $h->rejected_reason)
                                <div class="text-[10px] text-rose-700 bg-rose-50 border border-rose-200 p-1.5 rounded-lg mt-1 font-medium">
                                    <strong>Lý do từ chối:</strong> {{ $h->rejected_reason }}
                                </div>
                            @endif
                            @if($isApproved && $h->receipt_code)
                                <div class="text-[10px] text-indigo-700 font-mono font-bold mt-1">
                                    Phiếu thu: {{ $h->receipt_code }}
                                </div>
                            @endif
                        </td>

                        <!-- Trạng Thái -->
                        <td class="py-3.5 px-4 text-center">
                            @if($isPending)
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-900 rounded-full font-extrabold text-[11px] border border-amber-300 inline-flex items-center gap-1 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                    Chờ kế toán duyệt
                                </span>
                            @elseif($isApproved)
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-900 rounded-full font-extrabold text-[11px] border border-emerald-300 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-check text-emerald-600 text-[10px]"></i>
                                    Đã hoàn thành
                                </span>
                            @elseif($isRejected)
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-full font-extrabold text-[11px] border border-rose-300 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-xmark text-rose-600 text-[10px]"></i>
                                    Đã từ chối
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-800 rounded-full font-bold text-[11px]">
                                    {{ $h->status }}
                                </span>
                            @endif
                        </td>

                        <!-- Thao Tác -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            @if($isPending)
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openApproveModal({{ json_encode($h) }})"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs shadow-xs cursor-pointer inline-flex items-center gap-1">
                                        <i class="fa-solid fa-check-double text-emerald-200"></i>
                                        <span>Duyệt & Giải Phóng</span>
                                    </button>
                                    <button type="button" @click="openRejectModal({{ json_encode($h) }})"
                                            class="px-2.5 py-1.5 bg-white hover:bg-rose-50 text-rose-600 border border-rose-300 rounded-xl font-bold text-xs cursor-pointer inline-flex items-center gap-1" title="Từ chối nhận tiền">
                                        <i class="fa-solid fa-xmark"></i>
                                        <span>Từ chối</span>
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openOrderDetailsModal({{ json_encode($h) }})"
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs cursor-pointer inline-flex items-center gap-1">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>Xem chi tiết</span>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                            <div class="text-sm font-bold text-slate-600">Không có yêu cầu kiểm COD nào</div>
                            <div class="text-xs text-slate-400 mt-0.5">Khi tài xế nộp tiền COD cuối chuyến giao hàng, các yêu cầu sẽ hiển thị tại đây.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Layout -->
        <div class="md:hidden divide-y divide-slate-100 p-3 space-y-3">
            @forelse($handovers as $h)
            @php
                $codes = is_array($h->order_codes) ? $h->order_codes : (json_decode($h->order_codes, true) ?: []);
                $isPending = ($h->status === 'Chờ kế toán duyệt');
                $isApproved = ($h->status === 'Đã hoàn thành');
                $isRejected = ($h->status === 'Đã từ chối');
                $hDocs = is_array($h->documents) ? $h->documents : (json_decode($h->documents, true) ?: []);
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3 shadow-2xs {{ $isPending ? 'ring-1 ring-amber-300 bg-amber-50/10' : '' }}">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                    <div>
                        <div class="text-sm font-black text-indigo-700 font-mono">{{ $h->code }}</div>
                        <div class="text-[10px] text-slate-400 font-sans mt-0.5">
                            {{ $h->created_at ? $h->created_at->format('H:i d/m/Y') : '' }}
                        </div>
                    </div>
                    <div>
                        @if($isPending)
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-900 rounded-full font-extrabold text-[10px] border border-amber-300 inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                Chờ duyệt
                            </span>
                        @elseif($isApproved)
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-900 rounded-full font-extrabold text-[10px] border border-emerald-300 inline-flex items-center gap-1">
                                <i class="fa-solid fa-check text-emerald-600 text-[10px]"></i>
                                Hoàn thành
                            </span>
                        @elseif($isRejected)
                            <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-full font-extrabold text-[10px] border border-rose-300 inline-flex items-center gap-1">
                                <i class="fa-solid fa-xmark text-rose-600 text-[10px]"></i>
                                Từ chối
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-800 rounded-full font-bold text-[10px]">
                                {{ $h->status }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] block">Xe & Lái xe</span>
                        <div class="font-mono font-bold text-slate-900 text-xs">{{ $h->vehicle_plate ?: 'Chưa gán xe' }}</div>
                        <div class="text-slate-700 text-xs mt-0.5 font-medium">{{ $h->driver_name ?: 'Lái xe' }}</div>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 text-[11px] block">Số tiền nộp COD</span>
                        <div class="text-base font-black font-mono {{ $isPending ? 'text-amber-900' : ($isApproved ? 'text-emerald-700' : 'text-slate-600') }}">
                            {{ number_format((float) $h->handover_amount, 0, ',', '.') }} đ
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">{{ $h->payment_method ?: 'Tiền mặt' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="openOrderDetailsModal({{ json_encode($h) }})"
                                class="px-2.5 py-1 bg-sky-50 text-sky-800 border border-sky-200 rounded-lg font-bold text-xs inline-flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-boxes-packing text-sky-600"></i>
                            <span>{{ count($codes) }} đơn hàng</span>
                        </button>
                        @if(!empty($hDocs))
                            <button type="button" @click="openDocsModal({{ json_encode($h) }})"
                                    class="px-2 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg font-bold text-xs inline-flex items-center gap-1">
                                <i class="fa-solid fa-paperclip text-indigo-500"></i>
                                <span>{{ count($hDocs) }} tệp</span>
                            </button>
                        @endif
                    </div>
                    <div class="text-right text-[11px] text-slate-500">
                        KT: <strong class="text-slate-800">{{ $h->accountant_name ?: 'Chưa chỉ định' }}</strong>
                    </div>
                </div>

                @if($h->notes)
                    <div class="text-xs text-slate-600 bg-slate-50 p-2 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-semibold">Ghi chú:</span> {{ $h->notes }}
                    </div>
                @endif

                @if($isApproved && $h->receipt_code)
                    <div class="text-[11px] text-emerald-800 font-mono font-bold bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-200">
                        Phiếu thu: {{ $h->receipt_code }}
                    </div>
                @endif

                @if($isRejected && $h->rejected_reason)
                    <div class="text-[11px] text-rose-800 bg-rose-50 px-2 py-1 rounded-lg border border-rose-200">
                        Lý do từ chối: {{ $h->rejected_reason }}
                    </div>
                @endif

                <!-- Thao tác Mobile -->
                <div class="pt-2 border-t border-slate-100">
                    @if($isPending)
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="openApproveModal({{ json_encode($h) }})"
                                    class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs shadow-xs flex items-center justify-center gap-1">
                                <i class="fa-solid fa-check-double text-emerald-200"></i>
                                <span>Duyệt & Nhận</span>
                            </button>
                            <button type="button" @click="openRejectModal({{ json_encode($h) }})"
                                    class="w-full py-2 bg-white text-rose-600 border border-rose-300 rounded-xl font-bold text-xs flex items-center justify-center gap-1">
                                <i class="fa-solid fa-xmark"></i>
                                <span>Từ chối</span>
                            </button>
                        </div>
                    @else
                        <button type="button" @click="openOrderDetailsModal({{ json_encode($h) }})"
                                class="w-full py-2 bg-slate-100 text-slate-700 rounded-xl font-bold text-xs flex items-center justify-center gap-1">
                            <i class="fa-regular fa-eye"></i>
                            <span>Xem chi tiết đơn hàng</span>
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 text-xs">
                Không có yêu cầu kiểm COD nào.
            </div>
            @endforelse
        </div>

        @if($handovers->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $handovers->links() }}
        </div>
        @endif
    </div>

    <!-- Modal 1: Duyệt Nhận Tiền COD & Giải Phóng Đơn Hàng -->
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-xl w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showApproveModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Kế Toán Xác Nhận Nhận Tiền COD</h3>
                        <p class="text-xs text-slate-500 font-medium">Biên bản: <span class="font-mono font-bold text-indigo-700" x-text="activeHandover?.code"></span></p>
                    </div>
                </div>
                <button type="button" @click="showApproveModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3.5 text-xs">
                <!-- Vehicle & Driver Summary -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-slate-500 font-medium">Phương tiện & Lái xe nộp tiền:</div>
                        <div class="font-bold text-slate-900 text-sm mt-0.5">
                            <span class="font-mono px-2 py-0.5 bg-amber-100 text-amber-900 rounded font-black text-xs mr-1" x-text="activeHandover?.vehicle_plate"></span>
                            <span x-text="activeHandover?.driver_name"></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-slate-500 font-medium">Số đơn hàng COD:</div>
                        <div class="font-mono font-black text-sm text-indigo-700" x-text="(activeHandover?.order_codes?.length || activeHandover?.order_count || 0) + ' đơn'"></div>
                    </div>
                </div>

                <!-- Handover Amount (Cố định, không được chỉnh sửa) -->
                <div class="p-4 bg-emerald-50/80 rounded-2xl border border-emerald-200 space-y-1">
                    <div class="flex items-center justify-between text-xs font-bold text-emerald-900">
                        <span>Số tiền COD tài xế nộp về quỹ (Cố định theo biên bản):</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-200 text-emerald-950 font-bold text-[10px] flex items-center gap-1">
                            <i class="fa-solid fa-lock text-[9px]"></i>
                            <span>Cố định</span>
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-950 tracking-tight"
                         x-text="formatNumber(activeHandover?.handover_amount) + ' đ'">
                    </div>
                    <p class="text-[11px] text-emerald-800/80 font-medium">
                        * Lưu ý: Nếu tiền mặt thực đếm bị thiếu hoặc sai lệch, vui lòng bấm <strong>Từ chối</strong> để tài xế nộp lại. Không chỉnh sửa số tiền khi duyệt.
                    </p>
                </div>

                <!-- Hình ảnh & File chứng từ Lái xe đính kèm -->
                <template x-if="activeHandover && activeHandover.documents && activeHandover.documents.length > 0">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                            <span class="flex items-center gap-1.5 text-indigo-900">
                                <i class="fa-solid fa-paperclip text-indigo-600"></i>
                                <span>Hình ảnh & File chứng từ do Lái xe đính kèm:</span>
                            </span>
                            <span class="text-[11px] font-bold text-indigo-700" x-text="activeHandover.documents.length + ' tệp'"></span>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 pt-1 max-h-44 overflow-y-auto pr-1">
                            <template x-for="(doc, idx) in activeHandover.documents" :key="idx">
                                <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-white aspect-square shadow-2xs flex flex-col items-center justify-center p-1 hover:border-indigo-400 transition-colors">
                                    <template x-if="isImageDoc(doc)">
                                        <img :src="getDocUrl(doc)" class="w-full h-full object-cover cursor-pointer rounded-lg hover:opacity-90 transition-opacity"
                                             @click="viewDocPreview(doc)" alt="Chứng từ">
                                    </template>
                                    <template x-if="!isImageDoc(doc)">
                                        <a :href="getDocUrl(doc)" target="_blank" class="w-full h-full flex flex-col items-center justify-center text-center p-1.5 cursor-pointer hover:bg-slate-50 transition-colors no-underline">
                                            <i class="fa-solid text-2xl mb-1" :class="getDocIcon(doc)"></i>
                                            <div class="text-[9.5px] font-bold text-slate-700 truncate w-full" x-text="getDocName(doc)"></div>
                                            <div class="text-[8.5px] text-slate-400 font-mono" x-show="doc.size" x-text="(doc.size / 1024).toFixed(0) + ' KB'"></div>
                                        </a>
                                    </template>
                                    <div class="absolute bottom-0 inset-x-0 bg-slate-900/60 text-white text-[9px] py-0.5 text-center font-bold" x-show="isImageDoc(doc)">
                                        Ảnh <span x-text="idx + 1"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Ghi chú xác nhận của Kế toán:</label>
                    <textarea rows="2" x-model="approveForm.notes" placeholder="VD: Đã kiểm đếm đủ tiền mặt niêm phong..."
                              class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-slate-800 text-xs bg-white"></textarea>
                </div>

                <!-- Impact Notice -->
                <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200 text-emerald-950 flex items-start gap-2 text-[11px]">
                    <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong class="font-bold">Tác động sau khi Kế toán duyệt:</strong>
                        <ul class="list-disc list-inside mt-0.5 space-y-0.5 text-emerald-900">
                            <li>Tự động tạo Phiếu thu tiền mặt hạch toán vào Quỹ tiền mặt.</li>
                            <li>Toàn bộ đơn hàng đã giao sẽ được chuyển sang <strong>"Đã quyết toán"</strong> và <strong>giải phóng hoàn toàn khỏi xe</strong>.</li>
                            <li>Nếu xe không còn đơn nào khác, xe sẽ tự động chuyển về trạng thái <strong>"Sẵn sàng"</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showApproveModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmApprove()" :disabled="isSubmitting"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-emerald-600/20 disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-check-double text-emerald-200"></i>
                    <span x-text="isSubmitting ? 'Đang duyệt & hạch toán...' : 'Xác Nhận Nhận Đủ Tiền & Giải Phóng Đơn'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Từ Chối Biên Bản Nộp Tiền COD -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showRejectModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Từ Chối Biên Bản COD</h3>
                        <p class="text-xs text-slate-500 font-medium">Mã: <span class="font-mono font-bold text-rose-700" x-text="activeHandover?.code"></span></p>
                    </div>
                </div>
                <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Lý do từ chối bàn giao tiền COD <span class="text-rose-500">*</span>:</label>
                    <textarea rows="3" x-model="rejectForm.reason" placeholder="VD: Kiểm đếm thiếu 500.000 đ so với số tiền trên biên bản..."
                              class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-slate-800 text-xs bg-white"></textarea>
                </div>
                <p class="text-[11px] text-slate-500">
                    Hệ thống sẽ gửi thông báo khẩn cấp đến Lái xe để kiểm đếm lại tiền mặt và các đơn hàng.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy
                </button>
                <button type="button" @click="confirmReject()" :disabled="isSubmitting"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-rose-600/20 disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-ban text-rose-200"></i>
                    <span x-text="isSubmitting ? 'Đang gửi...' : 'Xác Nhận Từ Chối'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 3: Chi Tiết Đơn Hàng Trong Biên Bản COD -->
    <div x-show="showOrderDetailsModal" x-cloak class="fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-3xl w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] flex flex-col" @click.away="showOrderDetailsModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Danh Sách Đơn Hàng Trong Biên Bản</h3>
                        <p class="text-xs text-slate-500 font-medium">Biên bản: <span class="font-mono font-bold text-indigo-700" x-text="activeHandover?.code"></span> · Xe: <span class="font-mono font-bold text-amber-800" x-text="activeHandover?.vehicle_plate"></span></p>
                    </div>
                </div>
                <button type="button" @click="showOrderDetailsModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Orders Table List: Desktop Table & Mobile Cards -->
            <div class="overflow-y-auto flex-1 text-xs">
                <!-- Desktop Table -->
                <div class="hidden md:block">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider border-b border-slate-200">
                                <th class="py-2.5 px-3">Mã Đơn</th>
                                <th class="py-2.5 px-3">Khách Hàng / Đại Lý</th>
                                <th class="py-2.5 px-3">Địa Chỉ & SĐT</th>
                                <th class="py-2.5 px-3 text-right">Tổng Tiền Đơn</th>
                                <th class="py-2.5 px-3 text-right">Tiền Thu COD</th>
                                <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="code in (activeHandover?.order_codes || [])" :key="code">
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-2.5 px-3 font-mono font-bold text-indigo-700" x-text="code"></td>
                                    <td class="py-2.5 px-3 font-bold text-slate-800" x-text="getOrderInfo(code)?.customer_name || getOrderInfo(code)?.dealer_name || 'Khách hàng'"></td>
                                    <td class="py-2.5 px-3 text-slate-500 text-[11px]" x-text="getOrderInfo(code)?.customer_address || getOrderInfo(code)?.shipping_address || '—'"></td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-700" x-text="formatNumber(getOrderInfo(code)?.total_amount) + ' đ'"></td>
                                    <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-700" x-text="formatNumber(getOrderInfo(code)?.driver_debt_amount || getOrderInfo(code)?.total_amount) + ' đ'"></td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                              :class="getOrderInfo(code)?.status === 'Đã quyết toán' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800'"
                                              x-text="getOrderInfo(code)?.status || 'Đã giao'">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List -->
                <div class="md:hidden space-y-2.5 max-h-[60vh] overflow-y-auto pr-1">
                    <template x-for="code in (activeHandover?.order_codes || [])" :key="code">
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2 shadow-2xs">
                            <div class="flex items-center justify-between gap-2 border-b border-slate-200/80 pb-2">
                                <span class="font-mono font-bold text-indigo-700 text-xs" x-text="code"></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                      :class="getOrderInfo(code)?.status === 'Đã quyết toán' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800'"
                                      x-text="getOrderInfo(code)?.status || 'Đã giao'">
                                </span>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-xs" x-text="getOrderInfo(code)?.customer_name || getOrderInfo(code)?.dealer_name || 'Khách hàng'"></div>
                                <div class="text-[11px] text-slate-500 mt-0.5" x-text="getOrderInfo(code)?.customer_address || getOrderInfo(code)?.shipping_address || '—'"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-slate-200/60">
                                <div>
                                    <span class="text-slate-400 text-[10px] block">Tổng tiền đơn</span>
                                    <span class="font-mono font-bold text-slate-700" x-text="formatNumber(getOrderInfo(code)?.total_amount) + ' đ'"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-400 text-[10px] block">Tiền thu COD</span>
                                    <span class="font-mono font-black text-emerald-700" x-text="formatNumber(getOrderInfo(code)?.driver_debt_amount || getOrderInfo(code)?.total_amount) + ' đ'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Hình ảnh & File chứng từ Lái xe đính kèm trong chi tiết -->
            <template x-if="activeHandover && activeHandover.documents && activeHandover.documents.length > 0">
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2 shrink-0">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                        <span class="flex items-center gap-1.5 text-indigo-900">
                            <i class="fa-solid fa-paperclip text-indigo-600"></i>
                            <span>Hình ảnh & File chứng từ bàn giao (bảng kê tiền / phiếu nộp / sao kê):</span>
                        </span>
                        <span class="text-[11px] font-bold text-indigo-700" x-text="activeHandover.documents.length + ' tệp'"></span>
                    </div>
                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1 max-h-36 overflow-y-auto pr-1">
                        <template x-for="(doc, idx) in activeHandover.documents" :key="idx">
                            <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-white aspect-square shadow-2xs flex flex-col items-center justify-center p-1 hover:border-indigo-400 transition-colors">
                                <template x-if="isImageDoc(doc)">
                                    <img :src="getDocUrl(doc)" class="w-full h-full object-cover cursor-pointer rounded-lg hover:opacity-90 transition-opacity"
                                         @click="viewDocPreview(doc)" alt="Chứng từ">
                                </template>
                                <template x-if="!isImageDoc(doc)">
                                    <a :href="getDocUrl(doc)" target="_blank" class="w-full h-full flex flex-col items-center justify-center text-center p-1 cursor-pointer hover:bg-slate-50 transition-colors no-underline">
                                        <i class="fa-solid text-xl mb-1" :class="getDocIcon(doc)"></i>
                                        <div class="text-[9px] font-bold text-slate-700 truncate w-full" x-text="getDocName(doc)"></div>
                                    </a>
                                </template>
                                <div class="absolute bottom-0 inset-x-0 bg-slate-900/60 text-white text-[8.5px] py-0.5 text-center font-bold" x-show="isImageDoc(doc)">
                                    Ảnh <span x-text="idx + 1"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs shrink-0">
                <div class="text-slate-600">
                    Tổng số: <strong class="font-bold text-slate-900" x-text="(activeHandover?.order_codes?.length || 0) + ' đơn hàng'"></strong>
                </div>
                <button type="button" @click="showOrderDetailsModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 4: Xem trước ảnh phóng to (Lightbox) -->
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

</div>

<!-- JSON Pre-loaded Orders Map -->
<script id="cod-orders-map" type="application/json">
{!! json_encode($ordersMap ?? []) !!}
</script>

<script>
function codVerificationApp() {
    let ordersMap = {};
    try {
        ordersMap = JSON.parse(document.getElementById('cod-orders-map')?.textContent || '{}');
    } catch (e) {
        ordersMap = {};
    }

    return {
        ordersMap: ordersMap,
        activeHandover: null,
        showApproveModal: false,
        showRejectModal: false,
        showOrderDetailsModal: false,
        showDocPreviewModal: false,
        previewDocUrl: null,
        isSubmitting: false,

        approveForm: {
            amount: 0,
            notes: ''
        },

        rejectForm: {
            reason: ''
        },

        isImageDoc(doc) {
            if (!doc) return false;
            if (typeof doc === 'object') {
                if (doc.is_image !== undefined) return !!doc.is_image;
                if (doc.type && doc.type.startsWith('image/')) return true;
                const name = doc.name || doc.url || '';
                return /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(name);
            }
            if (typeof doc === 'string') {
                if (doc.startsWith('data:image/')) return true;
                return /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(doc);
            }
            return false;
        },

        getDocUrl(doc) {
            if (!doc) return '';
            return typeof doc === 'object' ? (doc.data || doc.url || '') : doc;
        },

        getDocName(doc, fallback = 'Tập tin') {
            if (!doc) return fallback;
            if (typeof doc === 'object' && doc.name) return doc.name;
            if (typeof doc === 'string') {
                if (doc.startsWith('data:')) return fallback;
                return doc.split('/').pop() || fallback;
            }
            return fallback;
        },

        getDocIcon(doc) {
            const name = (this.getDocName(doc) || '').toLowerCase();
            const type = (typeof doc === 'object' ? (doc.type || '') : '').toLowerCase();
            if (name.endsWith('.pdf') || type.includes('pdf')) return 'fa-file-pdf text-rose-500';
            if (name.endsWith('.doc') || name.endsWith('.docx') || type.includes('word')) return 'fa-file-word text-blue-500';
            if (name.endsWith('.xls') || name.endsWith('.xlsx') || type.includes('excel') || type.includes('sheet')) return 'fa-file-excel text-emerald-500';
            return 'fa-file-lines text-slate-500';
        },

        viewDocPreview(docOrUrl) {
            this.previewDocUrl = this.getDocUrl(docOrUrl);
            this.showDocPreviewModal = true;
        },

        openDocsModal(handover) {
            this.activeHandover = handover;
            this.showOrderDetailsModal = true;
        },

        formatNumber(val) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val) || 0));
        },

        getOrderInfo(code) {
            return this.ordersMap[code] || null;
        },

        openOrderDetailsModal(handover) {
            this.activeHandover = handover;
            this.showOrderDetailsModal = true;
        },

        openApproveModal(handover) {
            this.activeHandover = handover;
            this.approveForm.amount = Number(handover.handover_amount || 0);
            this.approveForm.notes = 'Đã kiểm đếm tiền mặt đầy đủ và duyệt nộp quỹ.';
            this.showApproveModal = true;
        },

        openRejectModal(handover) {
            this.activeHandover = handover;
            this.rejectForm.reason = '';
            this.showRejectModal = true;
        },

        async confirmApprove() {
            if (!this.activeHandover) return;
            if (this.approveForm.amount < 0) {
                alert('Số tiền duyệt không hợp lệ');
                return;
            }

            if (!confirm(`Xác nhận duyệt biên bản [${this.activeHandover.code}], nhận số tiền ${this.formatNumber(this.approveForm.amount)} đ và GIẢI PHÓNG toàn bộ đơn hàng trên xe?`)) {
                return;
            }

            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery-dispatches/settle-cash', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        action: 'approve',
                        code: this.activeHandover.code,
                        vehiclePlate: this.activeHandover.vehicle_plate,
                        handoverAmount: this.approveForm.amount,
                        amount: this.approveForm.amount,
                        notes: this.approveForm.notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('✓ ' + (data.message || 'Duyệt thành công! Đã giải phóng đơn hàng ra khỏi xe.'));
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể duyệt'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmitting = false;
            }
        },

        async confirmReject() {
            if (!this.activeHandover) return;
            if (!this.rejectForm.reason.trim()) {
                alert('Vui lòng nhập lý do từ chối!');
                return;
            }

            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery-dispatches/settle-cash', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        action: 'reject',
                        code: this.activeHandover.code,
                        rejectedReason: this.rejectForm.reason.trim()
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('✓ ' + (data.message || 'Đã từ chối biên bản nộp tiền!'));
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể từ chối'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}
</script>
@endsection
