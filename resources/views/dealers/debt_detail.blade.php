@extends('layouts.app')

@section('title', 'Sổ Chi Tiết Công Nợ - ' . ($dealer->name ?? 'Đại lý'))

@section('content')
<div class="space-y-4 print:space-y-2">
    <!-- Header Action Bar (Ẩn khi in) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 print:hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ url('/debt') }}" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors" title="Quay lại danh sách công nợ">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[11px] font-black bg-blue-100 text-blue-800 font-mono">{{ $dealer->code ?? ('DL-0' . $dealer->id) }}</span>
                        <h1 class="text-lg font-bold text-slate-900">{{ $dealer->name }}</h1>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $dealer->tier ?? 'Đại lý Cấp 1' }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <i class="fa-solid fa-phone text-slate-400 mr-1"></i>{{ $dealer->phone ?? '---' }} &bull; 
                        <i class="fa-solid fa-location-dot text-slate-400 mx-1"></i>{{ $dealer->address ?? 'Khu vực Bắc Miền Trung' }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-print text-slate-600"></i>
                    <span>In Sổ Công Nợ</span>
                </button>
                <a href="{{ url('/funds') }}?partner={{ urlencode($dealer->name) }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Lập Phiếu Thu Tiền</span>
                </a>
                <a href="{{ url('/orders/create') }}?dealer_id={{ $dealer->id }}" class="px-3.5 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#215ba6] text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-plus"></i>
                    <span>Lên Đơn Mới</span>
                </a>
            </div>
        </div>

        <!-- Metric Financial Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-3 border-t border-slate-100">
            <div class="p-3 bg-sky-50/60 rounded-xl border border-sky-100">
                <span class="text-[11px] font-semibold text-sky-800">Hạn Mức Tín Dụng</span>
                <p class="text-xl font-bold font-mono text-sky-950 mt-1">{{ number_format($creditLimit ?? 0, 0, ',', '.') }} đ</p>
            </div>
            <div class="p-3 {{ ($currentDebt ?? 0) > ($creditLimit ?? 0) ? 'bg-rose-50/70 border-rose-200' : 'bg-amber-50/60 border-amber-100' }} rounded-xl border">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold {{ ($currentDebt ?? 0) > ($creditLimit ?? 0) ? 'text-rose-800' : 'text-amber-800' }}">Dư Nợ Hiện Tại</span>
                    @if(($currentDebt ?? 0) > ($creditLimit ?? 0))
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-rose-600 text-white uppercase">Vượt hạn mức</span>
                    @endif
                </div>
                <p class="text-xl font-bold font-mono {{ ($currentDebt ?? 0) > ($creditLimit ?? 0) ? 'text-rose-700' : 'text-slate-900' }} mt-1">
                    {{ number_format($currentDebt ?? 0, 0, ',', '.') }} đ
                </p>
            </div>
            <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100">
                <span class="text-[11px] font-semibold text-emerald-800">Còn Được Phép Nợ</span>
                <p class="text-xl font-bold font-mono text-emerald-900 mt-1">{{ number_format($remainingCredit ?? 0, 0, ',', '.') }} đ</p>
            </div>
            <div class="p-3 bg-indigo-50/60 rounded-xl border border-indigo-100">
                <span class="text-[11px] font-semibold text-indigo-800">Tổng Mua Hàng Lũy Kế</span>
                <p class="text-xl font-bold font-mono text-indigo-950 mt-1">{{ number_format($totalOrderAmount ?? 0, 0, ',', '.') }} đ</p>
            </div>
        </div>
    </div>

    <!-- Tiêu đề bản in (Chỉ hiển thị khi in) -->
    <div class="hidden print:block text-center py-2 border-b border-slate-300">
        <h2 class="text-xl font-bold uppercase tracking-wide">CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN</h2>
        <h3 class="text-lg font-bold uppercase text-slate-800 mt-1">SỔ CHI TIẾT CÔNG NỢ KHÁCH HÀNG</h3>
        <p class="text-xs text-slate-600">Khách hàng: <strong>{{ $dealer->name }}</strong> (Mã: {{ $dealer->code }}) - ĐT: {{ $dealer->phone }}</p>
        <p class="text-xs text-slate-500">Dư nợ hiện tại: <strong>{{ number_format($currentDebt ?? 0, 0, ',', '.') }} đ</strong> - Hạn mức: {{ number_format($creditLimit ?? 0, 0, ',', '.') }} đ</p>
    </div>

    <!-- Khối 1: Danh sách Đơn hàng & Trạng thái thanh toán, Hình thức thanh toán -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/70">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing text-[#1b4a8c]"></i>
                    <span>Danh Sách Đơn Hàng & Đối Soát Thanh Toán</span>
                </h2>
                <p class="text-[11px] text-slate-500">Trạng thái đơn hàng (chỉ dừng ở Đã giao) & Chi tiết Hình thức thanh toán, Trạng thái quyết toán công nợ</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-extrabold text-xs">
                {{ count($orders ?? []) }} đơn hàng
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-10 text-center">STT</th>
                        <th class="py-2.5 px-3 min-w-[130px]">Mã Chứng Từ</th>
                        <th class="py-2.5 px-3 min-w-[110px]">Ngày Lập</th>
                        <th class="py-2.5 px-3 min-w-[130px]">Kho Xuất</th>
                        <th class="py-2.5 px-3 text-right min-w-[120px]">Giá Trị Đơn Hàng</th>
                        <th class="py-2.5 px-3 min-w-[150px]">Hình Thức Thanh Toán</th>
                        <th class="py-2.5 px-3 min-w-[160px]">Trạng Thái Thanh Toán</th>
                        <th class="py-2.5 px-3 min-w-[130px]">Trạng Thái Đơn</th>
                        <th class="py-2.5 px-3 text-center w-20 print:hidden">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($orders ?? [] as $idx => $o)
                        @php
                            $st = $o->status ?? 'Báo giá';
                            $pStatus = $o->payment_status ?? 'Chưa thanh toán';
                            $pMethod = $o->payment_method ?? 'Ghi nợ đại lý';
                            
                            // Map payment status badge
                            $pStatusBadge = match($pStatus) {
                                'Đã thanh toán', 'Đã quyết toán', 'Quyết toán xong' => ['bg' => 'bg-emerald-50 text-emerald-800 border-emerald-300', 'icon' => 'fa-circle-check', 'label' => 'Đã thanh toán'],
                                'Ghi nợ đại lý', 'Ghi nợ', 'Ký nợ' => ['bg' => 'bg-amber-50 text-amber-800 border-amber-300', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Ghi nợ đại lý'],
                                'Chờ duyệt nộp quỹ', 'Chờ kế toán xác nhận' => ['bg' => 'bg-blue-50 text-blue-800 border-blue-300', 'icon' => 'fa-clock', 'label' => 'Chờ duyệt nộp quỹ'],
                                default => ['bg' => 'bg-slate-100 text-slate-800 border-slate-300', 'icon' => 'fa-circle-question', 'label' => $pStatus]
                            };

                            // Map payment method badge
                            $pMethodBadge = match(true) {
                                str_contains(mb_strtolower($pMethod), 'tiền mặt') => ['bg' => 'bg-emerald-50 text-emerald-900 border-emerald-200', 'icon' => 'fa-money-bill-wave', 'label' => 'Tiền mặt (COD)'],
                                str_contains(mb_strtolower($pMethod), 'chuyển khoản') || str_contains(mb_strtolower($pMethod), 'vietqr') => ['bg' => 'bg-blue-50 text-blue-900 border-blue-200', 'icon' => 'fa-qrcode', 'label' => 'VietQR / Chuyển khoản'],
                                default => ['bg' => 'bg-amber-50 text-amber-900 border-amber-200', 'icon' => 'fa-handshake', 'label' => $pMethod]
                            };

                            // Map order status badge (Đơn hàng chỉ dừng ở 'Đã giao', không hiển thị 'Đã quyết toán')
                            $orderStatusBadge = match($st) {
                                'Đã giao', 'Đã quyết toán', 'Giao thành công' => ['bg' => 'bg-emerald-50 text-emerald-800 border-emerald-300', 'icon' => 'fa-truck-fast', 'label' => 'Đã giao'],
                                'Đang vận chuyển' => ['bg' => 'bg-cyan-50 text-cyan-800 border-cyan-300', 'icon' => 'fa-truck', 'label' => 'Đang vận chuyển'],
                                'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng' => ['bg' => 'bg-teal-50 text-teal-800 border-teal-300', 'icon' => 'fa-boxes-packing', 'label' => 'Đang kiểm hàng'],
                                'Soạn hàng', 'Đang bốc hàng' => ['bg' => 'bg-purple-50 text-purple-800 border-purple-300', 'icon' => 'fa-box', 'label' => 'Soạn hàng'],
                                'Chốt đơn', 'Đã duyệt' => ['bg' => 'bg-blue-50 text-blue-800 border-blue-300', 'icon' => 'fa-handshake', 'label' => 'Chốt đơn'],
                                'Hủy', 'Đã hủy' => ['bg' => 'bg-rose-50 text-rose-800 border-rose-300', 'icon' => 'fa-xmark', 'label' => 'Đã hủy'],
                                default => ['bg' => 'bg-slate-100 text-slate-800 border-slate-300', 'icon' => 'fa-file-lines', 'label' => $st]
                            };
                        @endphp
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            
                            <!-- Mã chứng từ -->
                            <td class="py-2.5 px-3 font-mono font-bold text-sky-800">
                                <a href="{{ url('/orders/' . $o->id . '/edit') }}" class="hover:underline" title="Xem chi tiết đơn hàng">
                                    {{ $o->order_code }}
                                </a>
                            </td>
                            
                            <!-- Ngày lập -->
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $o->created_at ? $o->created_at->format('d/m/Y') : '---' }}
                                @if(!empty($o->delivery_date))
                                    <div class="text-[10px] text-sky-700 font-bold mt-0.5">
                                        Giao: {{ \Carbon\Carbon::parse($o->delivery_date)->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>
                            
                            <!-- Kho xuất -->
                            <td class="py-2.5 px-3 text-slate-700">
                                <div class="truncate max-w-[140px]" title="{{ $o->warehouse ?? 'Kho Tổng Mê Linh' }}">
                                    {{ $o->warehouse ?? 'Kho Tổng Mê Linh' }}
                                </div>
                            </td>
                            
                            <!-- Giá trị đơn hàng -->
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($o->total_amount ?? 0, 0, ',', '.') }} đ
                            </td>
                            
                            <!-- Hình thức thanh toán -->
                            <td class="py-2.5 px-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold border {{ $pMethodBadge['bg'] }}">
                                    <i class="fa-solid {{ $pMethodBadge['icon'] }} text-[10px]"></i>
                                    <span>{{ $pMethodBadge['label'] }}</span>
                                </span>
                            </td>
                            
                            <!-- Trạng thái thanh toán (Quyết toán hay Ghi nợ đại lý) -->
                            <td class="py-2.5 px-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-black border shadow-2xs {{ $pStatusBadge['bg'] }}">
                                    <i class="fa-solid {{ $pStatusBadge['icon'] }} text-[10px]"></i>
                                    <span>{{ $pStatusBadge['label'] }}</span>
                                </span>
                            </td>
                            
                            <!-- Trạng thái đơn hàng (Dừng ở 'Đã giao') -->
                            <td class="py-2.5 px-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $orderStatusBadge['bg'] }}">
                                    <i class="fa-solid {{ $orderStatusBadge['icon'] }} text-[9px]"></i>
                                    <span>{{ $orderStatusBadge['label'] }}</span>
                                </span>
                            </td>
                            
                            <!-- Thao tác -->
                            <td class="py-2.5 px-3 text-center print:hidden">
                                <a href="{{ url('/orders/' . $o->id . '/edit') }}" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition-colors inline-flex items-center gap-1">
                                    <i class="fa-solid fa-eye text-sky-600"></i>
                                    <span>Chi tiết</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Khách hàng chưa phát sinh đơn hàng nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Khối 2: Lịch sử Phiếu thu tiền & Đối trừ công nợ -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/70">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <span>Lịch Sử Giao Dịch Thu Tiền & Đối Trừ Sổ Nợ</span>
                </h2>
                <p class="text-[11px] text-slate-500">Các phiếu thu quỹ tiền mặt / ngân hàng đã hạch toán trừ nợ cho khách hàng</p>
            </div>
            <a href="{{ url('/funds') }}?partner={{ urlencode($dealer->name) }}" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1 transition-colors print:hidden">
                <i class="fa-solid fa-plus"></i>
                <span>Tạo Phiếu Thu Mới</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-10 text-center">STT</th>
                        <th class="py-2.5 px-3 min-w-[120px]">Mã Phiếu Thu</th>
                        <th class="py-2.5 px-3 min-w-[100px]">Ngày Thu</th>
                        <th class="py-2.5 px-3 min-w-[140px]">Loại Giao Dịch</th>
                        <th class="py-2.5 px-3 text-right min-w-[120px]">Số Tiền Thu</th>
                        <th class="py-2.5 px-3 min-w-[200px]">Nội Dung / Diễn Giải</th>
                        <th class="py-2.5 px-3 text-center min-w-[110px]">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($transactions ?? [] as $tIdx => $t)
                        <tr class="hover:bg-emerald-50/40 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $tIdx + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-emerald-700">{{ $t->code ?? ('PT-' . $t->id) }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $t->date ? \Carbon\Carbon::parse($t->date)->format('d/m/Y') : ($t->created_at ? $t->created_at->format('d/m/Y') : '---') }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $t->category ?? 'Thu tiền hàng đại lý' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-700">
                                +{{ number_format($t->amount ?? 0, 0, ',', '.') }} đ
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $t->notes ?? 'Thu công nợ khách hàng' }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Đã hạch toán
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Chưa có giao dịch phiếu thu nào được ghi nhận cho đại lý này</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
