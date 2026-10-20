<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $machine->machine_model }} - Sổ Bảo Hành Điện Tử Đại Sơn Water</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Be Vietnam Pro"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="min-h-full font-sans text-slate-800 antialiased bg-slate-100 flex flex-col justify-between"
      x-data="customerPortalApp()">

    <!-- Header Gradient Banner -->
    <header class="bg-gradient-to-br from-blue-700 via-indigo-700 to-cyan-600 text-white shadow-lg sticky top-0 z-30">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-white shadow-inner">
                    <i class="fa-solid fa-shield-halved text-xl text-cyan-300"></i>
                </div>
                <div>
                    <h1 class="text-sm font-black tracking-tight leading-tight uppercase flex items-center gap-1.5">
                        ĐẠI SƠN WATER
                        <span class="inline-block px-1.5 py-0.5 text-[9px] font-extrabold bg-cyan-400 text-blue-950 rounded tracking-normal">VERIFIED</span>
                    </h1>
                    <p class="text-xs text-blue-100 font-medium">Bảo Hành Điện Tử Chính Hãng</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
                    <i class="fa-solid fa-circle-check text-emerald-300"></i>
                    Chính Hãng
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-xl mx-auto w-full px-4 py-4 space-y-4 flex-grow">

        <!-- Top Machine Certificate Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-4 text-white">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="text-[10px] font-bold tracking-widest uppercase bg-white/20 px-2 py-0.5 rounded-full border border-white/30">
                            PASSPORT THIẾT BỊ
                        </span>
                        <h2 class="text-xl font-black mt-1.5 tracking-tight leading-snug">
                            {{ $machine->machine_model }}
                        </h2>
                        <p class="text-xs text-blue-100 mt-0.5">
                            Mã máy: <span class="font-mono font-bold text-white">{{ $machine->machine_code }}</span>
                            @if($machine->machine_serial)
                                • Serial: <span class="font-mono text-cyan-200">{{ $machine->machine_serial }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="w-14 h-14 bg-white p-1 rounded-xl shadow-md shrink-0 flex items-center justify-center" id="qrMiniThumb"></div>
                </div>
            </div>

            <!-- Warranty Counter & Status -->
            <div class="p-5 space-y-4">
                @php
                    $wStatus = $machine->warranty_status;
                    $daysRemaining = $machine->warranty_days_remaining;
                    $isExpired = ($wStatus === 'expired');
                    $isExpiring = ($wStatus === 'expiring_soon');
                @endphp

                <div class="p-4 rounded-2xl border transition-all"
                     class="{{ $isExpired ? 'bg-rose-50 border-rose-200' : ($isExpiring ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50/80 border-emerald-200') }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold {{ $isExpired ? 'bg-rose-100 text-rose-700' : ($isExpiring ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                <i class="fa-solid {{ $isExpired ? 'fa-clock text-base' : 'fa-certificate text-base' }}"></i>
                            </span>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Trạng thái bảo hành</p>
                                <h3 class="text-base font-extrabold {{ $isExpired ? 'text-rose-700' : ($isExpiring ? 'text-amber-800' : 'text-emerald-800') }}">
                                    @if($isExpired)
                                        Đã hết hạn bảo hành
                                    @elseif($isExpiring)
                                        Sắp hết hạn (Còn {{ $daysRemaining }} ngày)
                                    @else
                                        Đang trong hạn bảo hành
                                    @endif
                                </h3>
                            </div>
                        </div>

                        @if(!$isExpired)
                            <div class="text-right">
                                <span class="text-2xl font-black text-emerald-600">{{ $daysRemaining }}</span>
                                <span class="text-xs font-bold text-slate-500 block -mt-1">ngày còn lại</span>
                            </div>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold bg-rose-200/80 text-rose-800 rounded-lg">Hết hạn</span>
                        @endif
                    </div>

                    <!-- Warranty Dates Grid -->
                    <div class="grid grid-cols-2 gap-3 mt-3.5 pt-3 border-t {{ $isExpired ? 'border-rose-200/60' : ($isExpiring ? 'border-amber-200/60' : 'border-emerald-200/60') }} text-xs">
                        <div>
                            <span class="text-slate-500 block text-[11px]">Ngày kích hoạt</span>
                            <strong class="text-slate-800 font-semibold">
                                {{ $machine->installed_at ? date('d/m/Y', strtotime($machine->installed_at)) : (date('d/m/Y', strtotime($machine->created_at))) }}
                            </strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Hạn bảo hành đến</span>
                            <strong class="text-slate-800 font-semibold">
                                {{ $machine->warranty_until ? date('d/m/Y', strtotime($machine->warranty_until)) : 'Chưa thiết lập' }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- Water Quality & Maintenance Cards -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl">
                        <div class="flex items-center gap-2 mb-1 text-blue-600">
                            <i class="fa-solid fa-droplet text-sm"></i>
                            <span class="text-[11px] font-bold uppercase text-slate-600">Độ tinh khiết TDS</span>
                        </div>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-2xl font-black text-blue-700">{{ $machine->tds_out ?? 12 }}</span>
                            <span class="text-xs font-semibold text-slate-500">ppm</span>
                        </div>
                        <p class="text-[11px] text-emerald-600 font-bold mt-0.5">
                            <i class="fa-solid fa-check"></i> Uống trực tiếp
                        </p>
                    </div>

                    <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl">
                        <div class="flex items-center gap-2 mb-1 text-indigo-600">
                            <i class="fa-solid fa-arrows-rotate text-sm"></i>
                            <span class="text-[11px] font-bold uppercase text-slate-600">Lịch thay lõi tới</span>
                        </div>
                        <div class="mt-1">
                            <span class="text-base font-extrabold text-slate-800">
                                {{ $machine->next_maintenance_date ? date('d/m/Y', strtotime($machine->next_maintenance_date)) : 'Đang tính...' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                            Chu kỳ: {{ $machine->filter_replacement_months ?: 6 }} tháng/lần
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Installation Location Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-user-check text-blue-600"></i>
                Chủ sở hữu thiết bị
            </h3>

            <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500 text-xs">Khách hàng:</span>
                    <span class="font-bold text-slate-800">{{ $machine->customer_name ?: 'Khách hàng Đại Sơn' }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500 text-xs">Số điện thoại:</span>
                    <span class="font-mono font-semibold text-slate-700">
                        @if($machine->customer_phone)
                            {{ substr($machine->customer_phone, 0, 4) . '***' . substr($machine->customer_phone, -3) }}
                        @else
                            --
                        @endif
                    </span>
                </div>
                <div class="flex items-start justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500 text-xs shrink-0 mr-2">Địa chỉ:</span>
                    <span class="font-medium text-slate-700 text-right text-xs">{{ $machine->customer_address ?: 'Đại Sơn Center' }}</span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-slate-500 text-xs">Kỹ thuật viên phụ trách:</span>
                    <span class="font-semibold text-blue-600 flex items-center gap-1 text-xs">
                        <i class="fa-solid fa-user-gear text-slate-400"></i>
                        {{ $machine->technician_name ?: 'KTV Đại Sơn' }}
                    </span>
                </div>
            </div>
        </div>

        @if(!empty($machine->accessories))
        <!-- Accessories Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 space-y-2.5">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-boxes-stacked text-teal-600"></i>
                    Phụ kiện kèm theo máy
                </span>
                <span class="text-[11px] font-semibold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-lg border border-teal-200">
                    {{ count($machine->accessories_list) }} phụ kiện
                </span>
            </h3>

            @if(count($machine->accessories_list) > 0)
                <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-slate-50/60 overflow-hidden text-xs">
                    @foreach($machine->accessories_list as $acc)
                        @php
                            $accName = is_array($acc) ? ($acc['name'] ?? '') : (string)$acc;
                            $accQty = is_array($acc) ? ($acc['quantity'] ?? $acc['qty'] ?? 1) : 1;
                            $accUnit = is_array($acc) ? ($acc['unit'] ?? '') : '';
                            $accDate = is_array($acc) ? ($acc['installed_date'] ?? null) : null;
                        @endphp
                        @if(!empty($accName))
                            <div class="p-3 flex items-center justify-between gap-2.5">
                                <div class="flex items-start gap-2.5 min-w-0">
                                    <div class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-[11px] shrink-0 mt-0.5">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-800 block text-xs leading-snug">{{ $accName }}</span>
                                        @if($accDate)
                                            <span class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5 font-medium">
                                                <i class="fa-regular fa-calendar text-[9px] text-slate-400"></i>
                                                Lắp ngày: <span class="font-mono text-slate-600">{{ date('d/m/Y', strtotime($accDate)) }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-white border border-slate-200 text-teal-800 font-mono shadow-2xs">
                                        x{{ $accQty }} {{ $accUnit }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="p-3 bg-teal-50/70 rounded-xl border border-teal-200/70 text-xs font-semibold text-teal-900 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-teal-600 mt-0.5 text-sm shrink-0"></i>
                    <div class="leading-relaxed">
                        {{ $machine->accessories }}
                    </div>
                </div>
            @endif
        </div>
        @endif

        <!-- Service & Maintenance History -->
        @if(isset($repairs) && count($repairs) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-screwdriver-wrench text-indigo-600"></i>
                Lịch sử bảo dưỡng & sửa chữa ({{ count($repairs) }})
            </h3>

            <div class="space-y-2.5">
                @foreach($repairs as $r)
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-indigo-700">{{ $r->code }}</span>
                        <span class="text-slate-500 text-[11px]">{{ date('d/m/Y', strtotime($r->received_date ?: $r->created_at)) }}</span>
                    </div>
                    <p class="font-medium text-slate-800 mt-1">{{ $r->symptom }}</p>
                    @if($r->solution)
                        <p class="text-slate-500 mt-0.5"><i class="fa-solid fa-arrow-right text-[10px] text-emerald-500 mr-1"></i>{{ $r->solution }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Quick Action Buttons -->
        <div class="space-y-2.5 pt-1">
            <button type="button" @click="openServiceModal()"
                    class="w-full py-3.5 px-4 rounded-xl font-bold text-white shadow-lg shadow-blue-500/25 bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 flex items-center justify-center gap-2 transition active:scale-[0.99]">
                <i class="fa-solid fa-bell text-cyan-200"></i>
                Yêu Cầu Bảo Hành / Thay Lõi Lọc
            </button>

            <a href="tel:0988000000"
               class="w-full py-3 px-4 rounded-xl font-bold text-slate-700 bg-white border border-slate-300 shadow-xs hover:bg-slate-50 flex items-center justify-center gap-2 transition">
                <i class="fa-solid fa-phone-volume text-emerald-600"></i>
                Hotline Hỗ Trợ 24/7: 0988.xxx.xxx
            </a>
        </div>

    </main>

    <!-- Footer -->
    <footer class="mt-8 border-t border-slate-200 bg-white py-4 text-center text-xs text-slate-500">
        <p class="font-bold text-slate-700">CÔNG TY CỔ PHẦN CÔNG NGHỆ & THƯƠNG MẠI ĐẠI SƠN</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Sản phẩm chính hãng được bảo hành điện tử toàn quốc</p>
    </footer>

    <!-- Service Request Modal -->
    <div x-show="serviceModal.show" x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
         style="display: none;">
        <div class="bg-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto"
             @click.away="serviceModal.show = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-headset"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Yêu Cầu Dịch Vụ / Bảo Hành</h3>
                        <p class="text-xs text-slate-500">KTV Đại Sơn sẽ liên hệ xử lý ngay</p>
                    </div>
                </div>
                <button type="button" @click="serviceModal.show = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form @submit.prevent="submitServiceRequest" class="space-y-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Loại yêu cầu <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2 text-xs font-semibold">
                        <button type="button" @click="serviceForm.service_type = 'maintenance'"
                                :class="serviceForm.service_type === 'maintenance' ? 'bg-cyan-600 text-white border-cyan-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                                class="p-2.5 rounded-xl border text-center transition">
                            <i class="fa-solid fa-filter block mb-1 text-sm"></i>
                            Thay lõi lọc
                        </button>
                        <button type="button" @click="serviceForm.service_type = 'warranty'"
                                :class="serviceForm.service_type === 'warranty' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                                class="p-2.5 rounded-xl border text-center transition">
                            <i class="fa-solid fa-shield block mb-1 text-sm"></i>
                            Bảo hành
                        </button>
                        <button type="button" @click="serviceForm.service_type = 'repair'"
                                :class="serviceForm.service_type === 'repair' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 text-slate-700 border-slate-200'"
                                class="p-2.5 rounded-xl border text-center transition">
                            <i class="fa-solid fa-wrench block mb-1 text-sm"></i>
                            Báo sự cố
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Số điện thoại liên hệ <span class="text-red-500">*</span></label>
                    <input type="tel" x-model="serviceForm.customer_phone" required placeholder="0912 xxx xxx"
                           class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mô tả tình trạng / Yêu cầu chi tiết <span class="text-red-500">*</span></label>
                    <textarea x-model="serviceForm.symptom" rows="3" required placeholder="Ví dụ: Nước chảy yếu, máy kêu cạch cạch, hoặc muốn thay bộ 3 lõi lọc thô..."
                              class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Thời gian thuận tiện tiếp đón KTV</label>
                    <input type="text" x-model="serviceForm.appointment_at" placeholder="Ví dụ: Chiều nay sau 17h, hoặc sáng thứ 7..."
                           class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium">
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="serviceModal.submitting"
                            class="w-full py-3 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2">
                        <template x-if="serviceModal.submitting">
                            <span><i class="fa-solid fa-circle-notch fa-spin"></i> Đang gửi yêu cầu...</span>
                        </template>
                        <template x-if="!serviceModal.submitting">
                            <span><i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Hỗ Trợ</span>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity.duration.300ms
         class="fixed bottom-5 left-1/2 transform -translate-x-1/2 z-50 max-w-sm w-full px-4"
         style="display: none;">
        <div class="p-3.5 rounded-2xl shadow-xl flex items-center gap-3 border text-sm font-semibold"
             :class="toast.type === 'success' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-rose-600 text-white border-rose-500'">
            <i class="fa-solid" :class="toast.type === 'success' ? 'fa-circle-check text-lg' : 'fa-circle-exclamation text-lg'"></i>
            <span x-text="toast.message" class="leading-snug"></span>
        </div>
    </div>

    <script>
        function customerPortalApp() {
            return {
                serviceModal: {
                    show: false,
                    submitting: false,
                },
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                serviceForm: {
                    service_type: 'maintenance',
                    customer_phone: @json($machine->customer_phone ?? ''),
                    symptom: '',
                    appointment_at: ''
                },

                init() {
                    const qrEl = document.getElementById('qrMiniThumb');
                    if (qrEl) {
                        new QRCode(qrEl, {
                            text: @json(url('/m/' . $machine->qr_token)),
                            width: 48,
                            height: 48,
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    }
                },

                openServiceModal() {
                    this.serviceModal.show = true;
                },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                },

                async submitServiceRequest() {
                    if (this.serviceModal.submitting) return;
                    this.serviceModal.submitting = true;

                    try {
                        const response = await fetch(@json(url('/m/' . $machine->qr_token . '/request-service')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.serviceForm)
                        });

                        const res = await response.json();
                        if (res.success) {
                            this.serviceModal.show = false;
                            this.showToast(res.message, 'success');
                            this.serviceForm.symptom = '';
                            this.serviceForm.appointment_at = '';
                        } else {
                            this.showToast(res.message || 'Có lỗi xảy ra khi gửi yêu cầu!', 'error');
                        }
                    } catch (err) {
                        console.error(err);
                        this.showToast('Không thể kết nối đến máy chủ. Vui lòng gọi hotline!', 'error');
                    } finally {
                        this.serviceModal.submitting = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
