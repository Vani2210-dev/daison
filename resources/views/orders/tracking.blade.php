@extends('layouts.app')

@section('title', 'Theo Dõi Tiến Trình Đơn Hàng: ' . $order->order_code . ' | ERP Đại Sơn')

@section('content')
@php
    $isCancelled = in_array($order->status, ['Hủy', 'Đã hủy', 'Đã huỷ']);
    
    // Tìm trạng thái trước khi hủy nếu có
    $lastActiveStatus = $order->status;
    if ($isCancelled && $auditLogs->isNotEmpty()) {
        foreach ($auditLogs as $log) {
            if ($log->old_value && !in_array($log->old_value, ['Hủy', 'Đã hủy', 'Đã huỷ'])) {
                $lastActiveStatus = $log->old_value;
                break;
            }
        }
    }

    $stages = [
        'Báo giá'        => ['label' => '1. Báo giá', 'desc' => 'Lập báo giá và gửi cho khách hàng / đại lý'],
        'Chốt đơn'       => ['label' => '2. Chốt đơn', 'desc' => 'Khách hàng chốt giá & đơn chuyển sang chuẩn bị hàng'],
        'Soạn hàng'      => ['label' => '3. Soạn hàng', 'desc' => 'Thủ kho in lệnh và nhân viên bốc hàng tại kho'],
        'Kiểm hàng'      => ['label' => '4. Kiểm tra', 'desc' => 'Kiểm đếm quy cách, đóng gói và sẵn sàng xuất kho'],
        'Đang vận chuyển' => ['label' => '5. Vận chuyển', 'desc' => 'Lái xe nhận hàng và đang giao trên lộ trình'],
        'Đã giao'        => ['label' => '6. Đã giao hàng', 'desc' => 'Giao hàng thành công và đối soát công nợ / COD'],
    ];

    $stageKeys = array_keys($stages);

    // Chuẩn hóa trạng thái để so khớp stepper
    $normStatus = $isCancelled ? $lastActiveStatus : $order->status;
    if (in_array($normStatus, ['Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong'])) $normStatus = 'Soạn hàng';
    if (in_array($normStatus, ['Đã duyệt', 'Chờ duyệt'])) $normStatus = 'Chốt đơn';
    if (in_array($normStatus, ['Đã kiểm hàng', 'Đã kiểm xong', 'Sẵn sàng xuất xe'])) $normStatus = 'Kiểm hàng';
    if (in_array($normStatus, ['Đang giao hàng'])) $normStatus = 'Đang vận chuyển';
    if (in_array($normStatus, ['Giao thành công'])) $normStatus = 'Đã giao';

    $currentStageIdx = array_search($normStatus, $stageKeys);
    if ($currentStageIdx === false && !$isCancelled) {
        $currentStageIdx = 0;
    }
@endphp

<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Top Navigation & Action Header -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.index') }}" 
               class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer shadow-2xs"
               title="Quay lại danh sách đơn hàng">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Theo dõi tiến trình đơn hàng</h1>
                    <span class="px-2.5 py-0.5 rounded-lg bg-sky-50 text-sky-700 border border-sky-200 font-mono font-black text-sm">
                        {{ $order->order_code }}
                    </span>
                    @if($isCancelled)
                        <span class="px-2.5 py-0.5 rounded-lg bg-rose-100 text-rose-700 border border-rose-300 font-black text-xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-ban text-[11px]"></i>
                            <span>ĐÃ HỦY ĐƠN</span>
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-xs">
                            {{ $order->status }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Khách hàng: <strong class="text-slate-700">{{ $order->customer_name }}</strong> &bull; Ngày lập: {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A' }}
                </p>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.location.reload()" 
                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center gap-2 transition-all cursor-pointer shadow-2xs">
                <i class="fa-solid fa-rotate text-slate-500"></i>
                <span class="hidden sm:inline">Tải lại</span>
            </button>
            <a href="{{ url('/orders/' . $order->id . '/edit') }}" 
               class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs flex items-center gap-2 transition-all cursor-pointer shadow-xs">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Xem & Sửa đơn</span>
            </a>
        </div>
    </div>

    @if($isCancelled)
        <!-- Cảnh báo đơn đã bị hủy -->
        <div class="bg-gradient-to-r from-rose-50 to-amber-50 rounded-2xl border border-rose-200 p-4 sm:p-5 shadow-2xs flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div class="flex-1 space-y-1">
                <h3 class="font-black text-rose-900 text-sm sm:text-base">Đơn hàng này đã bị HỦY</h3>
                <p class="text-xs text-rose-800 leading-relaxed">
                    Tiến trình thực hiện đơn hàng đã dừng lại. Số lượng tồn kho nếu đã từng trừ đã được hoàn trả lại kho xuất ban đầu, và công nợ đại lý đã được tự động tính toán lại.
                </p>
                @if($order->notes && str_contains($order->notes, 'Lý do hủy:'))
                    <div class="mt-2 p-2.5 bg-white/80 rounded-xl border border-rose-200 text-xs text-rose-900 font-medium">
                        <strong>Ghi chú lý do hủy:</strong> {{ $order->notes }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Main Grid 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Cột trái: Thông tin tóm tắt & Quy trình Stepper -->
        <div class="lg:col-span-5 space-y-6">
            <!-- 1. Card: Thông tin chứng từ -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-sky-600"></i>
                        <span>Thông tin chứng từ</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-slate-500">{{ $order->order_code }}</span>
                </div>

                <div class="text-xs space-y-2.5 text-slate-600">
                    <div class="flex justify-between items-start gap-2">
                        <span class="text-slate-400 shrink-0">Khách hàng:</span>
                        <span class="font-bold text-slate-900 text-right">{{ $order->customer_name }}</span>
                    </div>
                    @if($order->customer_phone)
                        <div class="flex justify-between items-center gap-2">
                            <span class="text-slate-400">Số điện thoại:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $order->customer_phone }}</span>
                        </div>
                    @endif
                    @if($order->customer_address)
                        <div class="flex justify-between items-start gap-2">
                            <span class="text-slate-400 shrink-0">Địa chỉ giao:</span>
                            <span class="font-medium text-slate-800 text-right">{{ $order->customer_address }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-slate-400">Kho xuất hàng:</span>
                        <span class="font-bold text-slate-800">{{ $order->warehouse ?: 'Kho Tổng' }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-slate-400">Lái xe & Xe:</span>
                        <span class="font-bold text-sky-700">
                            {{ $order->assignedDriver?->name ?: 'Chưa phân công' }} 
                            @if($order->assigned_vehicle_plate)
                                ({{ $order->assigned_vehicle_plate }})
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-slate-400">Người lập đơn:</span>
                        <span class="font-bold text-slate-800">{{ $order->order_creator ?: ($order->creator?->name ?: 'Hệ thống') }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-slate-400">Hình thức thanh toán:</span>
                        <span class="font-bold text-slate-800">{{ $order->payment_method ?: 'Chưa xác định' }}</span>
                    </div>
                    <div class="border-t border-slate-100 pt-2.5 flex justify-between items-center gap-2 text-sm">
                        <span class="font-bold text-slate-500">Tổng giá trị đơn:</span>
                        <span class="font-black text-slate-900 text-base text-sky-700">
                            {{ number_format((float)$order->total_amount, 0, ',', '.') }} đ
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Card: Tiến trình 6 bước (Lifecycle Stepper) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-route text-sky-600"></i>
                        <span>Tiến trình luân chuyển chứng từ</span>
                    </h3>
                    @if($isCancelled)
                        <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                            Đã dừng do hủy
                        </span>
                    @endif
                </div>

                <div class="py-1 space-y-3">
                    @foreach($stages as $key => $st)
                        @php
                            $stepIdx = array_search($key, $stageKeys);
                            $isCompleted = ($currentStageIdx !== false && $currentStageIdx > $stepIdx) || (!$isCancelled && $currentStageIdx === $stepIdx && $key === 'Đã giao');
                            $isCurrent = !$isCancelled && $currentStageIdx === $stepIdx && $key !== 'Đã giao';
                        @endphp
                        <div class="flex items-start gap-3 relative">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all {{ $isCompleted ? 'bg-emerald-500 text-white shadow-xs' : ($isCurrent ? 'bg-sky-600 text-white ring-4 ring-sky-100' : 'bg-slate-100 text-slate-400') }}">
                                    @if($isCompleted)
                                        <i class="fa-solid fa-check text-xs"></i>
                                    @else
                                        <span>{{ $stepIdx + 1 }}</span>
                                    @endif
                                </div>
                                @if($stepIdx < count($stageKeys) - 1)
                                    <div class="w-0.5 h-8 my-1 {{ $currentStageIdx > $stepIdx ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                                @endif
                            </div>
                            <div class="flex-1 pt-0.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold {{ $isCurrent ? 'text-sky-700 font-black' : ($isCompleted ? 'text-slate-800' : 'text-slate-400') }}">
                                        {{ $st['label'] }}
                                    </span>
                                    <span class="text-[10px] {{ $isCurrent ? 'text-sky-600 font-black px-2 py-0.5 rounded-full bg-sky-50 border border-sky-200' : ($isCompleted ? 'text-emerald-700 font-bold' : 'text-slate-400') }}">
                                        @if($isCurrent)
                                            Đang thực hiện
                                        @elseif($isCompleted)
                                            Đã hoàn tất
                                        @else
                                            Chưa đến
                                        @endif
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ $st['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. Card: Mặt hàng trong đơn -->
            @if($order->items->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-sky-600"></i>
                            <span>Danh sách mặt hàng</span>
                        </h3>
                        <span class="text-xs font-bold text-slate-500">{{ $order->items->count() }} mặt hàng</span>
                    </div>
                    <div class="divide-y divide-slate-100 max-h-60 overflow-y-auto pr-1 text-xs">
                        @foreach($order->items as $it)
                            <div class="py-2 flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-bold text-slate-800">{{ $it->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $it->sku }} &bull; ĐVT: {{ $it->unit }}</div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black text-slate-900">x{{ (float)$it->quantity }}</div>
                                    <div class="text-[11px] text-slate-500">{{ number_format((float)$it->amount, 0, ',', '.') }} đ</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Cột phải: Nhật ký thao tác chi tiết & Nội dung File Log gốc -->
        <div class="lg:col-span-7 space-y-6">
            <!-- 1. Card: Nhật ký thao tác chi tiết (AuditLog Timeline) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-black text-slate-800 text-sm sm:text-base flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-sky-600"></i>
                            <span>Lịch sử thao tác & Tiến trình</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Ghi nhận toàn bộ thao tác, chuyển trạng thái và người thực hiện</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-mono font-bold text-xs">
                        {{ $auditLogs->count() }} bản ghi
                    </span>
                </div>

                @if($auditLogs->isEmpty())
                    <div class="py-8 text-center text-xs text-slate-400">
                        <i class="fa-regular fa-clipboard text-3xl text-slate-300 block mb-2"></i>
                        Chưa có lịch sử thao tác được ghi nhận trong cơ sở dữ liệu.
                    </div>
                @else
                    <div class="relative pl-6 space-y-4 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        @foreach($auditLogs as $idx => $log)
                            @php
                                $isHuy = in_array($log->action, ['Hủy Đơn Hàng', 'Hủy Chứng Từ']) || in_array($log->new_value, ['Hủy', 'Đã hủy']);
                                $isGiao = in_array($log->action, ['Hoàn Tất Giao Hàng & Thu Tiền', 'Giao Hàng Thành Công']);
                            @endphp
                            <div class="relative group">
                                <!-- Dot on timeline -->
                                <div class="absolute -left-6 top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white shadow-xs {{ $isHuy ? 'bg-rose-500 ring-2 ring-rose-200' : ($isGiao ? 'bg-emerald-500 ring-2 ring-emerald-200' : 'bg-sky-500 ring-2 ring-sky-200') }}"></div>
                                
                                <div class="p-3.5 rounded-2xl border {{ $isHuy ? 'border-rose-200 bg-rose-50/50' : 'border-slate-200/90 bg-slate-50/50' }} hover:bg-white hover:shadow-xs transition-all space-y-1.5 text-xs">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <span class="font-black {{ $isHuy ? 'text-rose-700' : 'text-slate-900' }} text-sm flex items-center gap-1.5">
                                            @if($isHuy)
                                                <i class="fa-solid fa-ban text-rose-500"></i>
                                            @else
                                                <i class="fa-solid fa-circle-check text-sky-500"></i>
                                            @endif
                                            <span>{{ $log->action }}</span>
                                        </span>
                                        <span class="text-slate-400 font-mono text-[11px] font-bold">
                                            {{ $log->timestamp ?: ($log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '') }}
                                        </span>
                                    </div>

                                    @if($log->notes)
                                        <div class="text-slate-700 leading-relaxed font-medium bg-white/70 p-2 rounded-xl border border-slate-200/60">
                                            {{ $log->notes }}
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-user text-slate-400"></i>
                                            <span>Thực hiện: <strong class="text-slate-800">{{ $log->performer_name ?: 'Hệ thống' }}</strong></span>
                                        </span>
                                        @if($log->old_value || $log->new_value)
                                            <span class="font-mono text-[10px] px-2 py-0.5 rounded-md bg-slate-200/70 text-slate-700 font-bold">
                                                {{ $log->old_value ?? '-' }} &rarr; {{ $log->new_value ?? '-' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

