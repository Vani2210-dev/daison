@extends('layouts.app')

@section('title', ($installation ? 'Cập Nhật Đơn Lắp Đặt: ' . $installation->code : 'Tạo Mới Đơn Đi Lắp Đặt Máy Lọc') . ' | Đại Sơn ERP')

@push('styles')
<style>
@media (max-width: 767.98px) {
    main {
        padding-left: 0.375rem !important;
        padding-right: 0.375rem !important;
        padding-top: 0.25rem !important;
    }
    input[type="text"],
    input[type="number"],
    input[type="tel"],
    select,
    textarea {
        font-size: 15px !important;
    }
}
</style>
@endpush

@section('content')
@php
    $returnUrl = request('return_url');
    if (!$returnUrl) {
        $referer = request()->headers->get('referer');
        if ($referer && !str_contains($referer, '/water-filter/installations/') && !str_contains($referer, '/edit')) {
            $returnUrl = $referer;
        }
    }
    if (!$returnUrl) {
        $returnUrl = url('/water-filter?tab=installations');
    }
@endphp
<div class="w-full space-y-3 sm:space-y-4 pb-4 sm:pb-10" x-data="installationFormModule()">

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-center gap-2.5 sm:gap-3.5">
            <a href="{{ $returnUrl }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-700 flex items-center justify-center transition shadow-2xs cursor-pointer shrink-0" title="Quay lại">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight leading-snug" x-text="isEdit ? 'CẬP NHẬT ĐƠN ĐI LẮP ĐẶT' : 'LÊN ĐƠN ĐI LẮP ĐẶT MÁY LỌC NƯỚC'"></h1>
                    @if($installation)
                        <span class="px-2 py-0.5 rounded-lg text-[11px] sm:text-xs font-black bg-sky-100 text-sky-800 border border-sky-200 font-mono whitespace-nowrap shrink-0">
                            {{ $installation->code }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-lg text-[11px] sm:text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap shrink-0">
                            Đơn mới
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5 hidden sm:block">
                    Ghi nhận thông tin khách hàng, chọn model máy lọc, điều phối kỹ thuật viên và phụ tùng mang theo
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <a href="{{ $returnUrl }}" class="flex-1 md:flex-none px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 transition text-xs cursor-pointer text-center min-h-[42px] flex items-center justify-center">
                <i class="fa-solid fa-xmark mr-1"></i> Hủy bỏ
            </a>
            <button @click="submitForm()" :disabled="submitting" type="button" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-md shadow-sky-600/20 transition text-xs cursor-pointer text-center min-h-[42px] disabled:opacity-60 disabled:cursor-not-allowed">
                <i class="fa-solid" :class="submitting ? 'fa-spinner fa-spin' : 'fa-floppy-disk text-sm'"></i>
                <span x-text="submitting ? 'Đang lưu...' : (isEdit ? 'Lưu thay đổi' : 'Hoàn tất & Tạo đơn')"></span>
            </button>
        </div>
    </div>

    <!-- BANNER: KẾT QUẢ NGHIỆM THU KỸ THUẬT & TEM QR BẢO HÀNH -->
    @if($installation && ($installation->installedMachine || $installation->warranty_card_code || in_array($installation->status, ['completed', 'inspected'])))
    @php
        $machine = $installation->installedMachine;
        $qrCode = $machine?->qr_code ?? $installation->warranty_card_code;
        $passportUrl = $machine?->qr_url ?? ($machine ? url('/m/' . $machine->qr_token) : null);
        $wMonths = $installation->warranty_months ?: ($machine?->warranty_months ?: 24);
        $mMonths = $installation->filter_replacement_months ?: ($machine?->filter_replacement_months ?: 6);
        $wUntil = $installation->warranty_until ?: $machine?->warranty_until;
        $nextMaint = $installation->next_maintenance_date ?: $machine?->next_maintenance_date;
        $techName = $installation->technician_name ?: ($installation->technician?->name ?: ($machine?->technician_name ?? 'KTV'));
        $installedTime = $installation->installed_at ?: ($machine?->installed_at ?: $installation->completed_date);
        $completionNotes = $installation->completion_notes ?: ($machine?->activation_notes ?: null);
    @endphp
    <div class="bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-sky-500/10 border-2 border-emerald-400/80 rounded-2xl p-4 sm:p-5 shadow-sm shadow-emerald-500/5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-emerald-200/80 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shadow-sm shadow-emerald-600/30 shrink-0">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-sm sm:text-base font-black text-slate-800 tracking-tight uppercase">KẾT QUẢ NGHIỆM THU KỸ THUẬT & TEM QR BẢO HÀNH</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-black bg-emerald-600 text-white shadow-2xs">
                            <i class="fa-solid fa-check-circle mr-1"></i>Đã Nghiệm Thu & Kích Hoạt
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Kỹ thuật viên <strong>{{ $techName }}</strong> đã nghiệm thu lắp đặt và dán tem QR bảo hành điện tử.
                    </p>
                </div>
            </div>

            @if($passportUrl)
            <a href="{{ $passportUrl }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-black shadow-md shadow-emerald-600/25 transition-transform hover:scale-[1.02] cursor-pointer shrink-0">
                <i class="fa-solid fa-passport text-sm"></i>
                <span>Xem Thẻ Bảo Hành Điện Tử</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-80"></i>
            </a>
            @endif
        </div>

        <!-- 3 Cột thông tin nghiệm thu chi tiết -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <!-- 1. Tem QR -->
            <div class="bg-white/90 backdrop-blur-xs p-3.5 rounded-xl border border-emerald-200 shadow-2xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tem QR Dán Trên Máy</span>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-barcode text-emerald-600 text-base"></i>
                    <strong class="font-mono text-base font-black text-emerald-700">{{ $qrCode ?: 'Chưa có mã' }}</strong>
                </div>
                @if($machine?->qr_token)
                <span class="text-[10px] text-slate-400 block font-mono">Token: {{ $machine->qr_token }}</span>
                @endif
            </div>

            <!-- 2. Bảo hành & Chu kỳ -->
            <div class="bg-white/90 backdrop-blur-xs p-3.5 rounded-xl border border-emerald-200 shadow-2xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Thời Hạn Bảo Hành & Chu Kỳ</span>
                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                    <i class="fa-solid fa-shield-halved text-sky-600"></i>
                    <span>Bảo hành: <strong class="text-sky-700 font-black">{{ $wMonths }} tháng</strong></span>
                </div>
                <div class="text-[11px] text-slate-600">
                    Hạn BH: <strong>{{ $wUntil ? date('d/m/Y', strtotime($wUntil)) : 'Chưa cập nhật' }}</strong>
                </div>
                <div class="text-[10px] text-teal-700 font-semibold">
                    Thay lõi: <strong>{{ $mMonths }} tháng/lần</strong>
                </div>
            </div>

            <!-- 3. KTV & Thời gian -->
            <div class="bg-white/90 backdrop-blur-xs p-3.5 rounded-xl border border-emerald-200 shadow-2xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kỹ Thuật Viên Nghiệm Thu</span>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700 text-xs">
                        <i class="fa-solid fa-user-check text-sky-600 text-[10px]"></i>
                    </div>
                    <strong class="font-bold text-slate-800 text-xs truncate">{{ $techName }}</strong>
                </div>
                <div class="text-[11px] text-slate-500 flex items-center gap-1">
                    <i class="fa-regular fa-clock text-[10px]"></i>
                    <span>{{ $installedTime ? date('H:i - d/m/Y', strtotime($installedTime)) : 'Chưa ghi nhận' }}</span>
                </div>
                @if($installation->machine_serial)
                <div class="text-[10px] text-slate-600 font-mono truncate">
                    Serial: <strong>{{ $installation->machine_serial }}</strong>
                </div>
                @endif
            </div>
        </div>

        @if($completionNotes)
        <div class="bg-amber-50/80 border border-amber-200/80 rounded-xl p-3 text-xs flex items-start gap-2.5">
            <i class="fa-solid fa-clipboard-check text-amber-600 text-sm mt-0.5 shrink-0"></i>
            <div>
                <span class="font-bold text-amber-900">Ghi chú của KTV khi nghiệm thu:</span>
                <p class="text-amber-800 mt-0.5">{{ $completionNotes }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- MAIN FORM GRID (2 COLUMNS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 text-xs">

        <!-- COLUMN 1: THÔNG TIN KHÁCH HÀNG & ĐỊA ĐIỂM (5 COLS) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2 font-extrabold text-slate-800 text-sm">
                        <i class="fa-solid fa-user-tag text-sky-600"></i>
                        <span>Thông tin Khách hàng & Địa điểm</span>
                    </div>
                    <span class="text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full font-bold whitespace-nowrap shrink-0">
                        <i class="fa-solid fa-rotate text-[9px] mr-1"></i><span class="hidden sm:inline">Tự động lưu vào </span>Khách hàng
                    </span>
                </div>

                <!-- Ô Họ tên & Gợi ý tìm kiếm khách hàng có sẵn -->
                <div class="relative" @click.away="customerDropdownOpen = false">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-700">Họ tên khách hàng / Đại lý <span class="text-rose-500">*</span></label>
                        <span class="text-[11px] text-sky-600 font-semibold" x-show="form.dealer_id">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i> Đã liên kết Khách hàng
                        </span>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-3.5 top-3 text-slate-400"></i>
                        <input 
                            type="text" 
                            x-model="form.customer_name" 
                            @focus="customerDropdownOpen = true; highlightedCustomerIndex = 0"
                            @input="customerDropdownOpen = true; highlightedCustomerIndex = 0; onInputCustomerSearch()"
                            @keydown.down.prevent="navigateCustomerDown()"
                            @keydown.up.prevent="navigateCustomerUp()"
                            @keydown.enter.prevent="selectHighlightedCustomer()"
                            @keydown.escape.prevent="customerDropdownOpen = false"
                            required 
                            placeholder="Gõ tên hoặc SĐT để tìm khách hàng cũ / hoặc nhập mới..." 
                            class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-semibold text-slate-800 text-xs"
                        >
                    </div>

                    <!-- Dropdown danh sách gợi ý khách hàng -->
                    <div 
                        x-show="customerDropdownOpen && filteredCustomers.length > 0" 
                        x-cloak 
                        class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-60 overflow-y-auto divide-y divide-slate-100"
                    >
                        <div class="px-3 py-1.5 bg-slate-100 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                            <span>Khách hàng có sẵn trong hệ thống (Phím ↑ ↓ Enter)</span>
                            <span>Bấm để tự điền</span>
                        </div>
                        <template x-for="(c, cIdx) in filteredCustomers" :key="c.id">
                            <div 
                                :id="'cust-opt-' + cIdx"
                                @click="selectCustomer(c)" 
                                @mouseenter="highlightedCustomerIndex = cIdx"
                                class="p-2.5 cursor-pointer flex items-center justify-between gap-2 transition"
                                :class="highlightedCustomerIndex === cIdx ? 'bg-sky-100 text-sky-950 font-bold ring-1 ring-sky-300' : 'hover:bg-sky-50'"
                            >
                                <div class="truncate flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-sky-100 text-sky-800" x-text="c.code"></span>
                                        <span class="font-bold text-slate-800 truncate" x-text="c.name"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-3">
                                        <span x-show="c.phone"><i class="fa-solid fa-phone text-[10px] text-slate-400 mr-1"></i><span x-text="c.phone"></span></span>
                                        <span x-show="c.address" class="truncate"><i class="fa-solid fa-location-dot text-[10px] text-slate-400 mr-1"></i><span x-text="c.address"></span></span>
                                    </div>
                                </div>
                                <span class="text-[11px] font-bold text-sky-600 shrink-0">Chọn <i class="fa-solid fa-chevron-right text-[10px] ml-0.5"></i></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Số điện thoại liên hệ <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3.5 top-3 text-slate-400"></i>
                        <input 
                            type="text" 
                            x-model="form.customer_phone" 
                            @input="onInputCustomerPhone()" 
                            required 
                            placeholder="Nhập số điện thoại..." 
                            class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs"
                        >
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Địa chỉ thi công lắp đặt <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-location-dot absolute left-3.5 top-3 text-slate-400"></i>
                        <input type="text" x-model="form.customer_address" required placeholder="Số nhà, đường, xóm/thôn, xã/phường, quận/huyện..." class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-medium text-slate-800 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú yêu cầu của khách hàng</label>
                    <textarea x-model="form.customer_notes" rows="3" placeholder="Nhập ghi chú yêu cầu của khách hàng (nếu có)..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none text-xs text-slate-700"></textarea>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: MODEL MÁY, KỸ THUẬT VIÊN & TRẠNG THÁI ĐƠN (7 COLS) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3.5 sm:space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 sm:pb-3 gap-2">
                    <div class="flex items-center gap-2 font-extrabold text-slate-800 text-xs sm:text-sm min-w-0">
                        <i class="fa-solid fa-faucet-drip text-cyan-600 shrink-0"></i>
                        <span class="truncate">Thiết bị & Điều phối Kỹ thuật</span>
                    </div>
                    <!-- Nút thêm model máy mới -->
                    <button type="button" @click="openModelModal()" class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 font-bold border border-cyan-200 transition cursor-pointer text-xs shrink-0 whitespace-nowrap">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span class="hidden sm:inline">Thêm Model mới</span>
                        <span class="sm:hidden">Model mới</span>
                    </button>
                </div>

                <!-- 1. TÌM MODEL MÁY LỌC BẰNG INPUT NHẬP TRỰC TIẾP -->
                <div>
                    <div class="flex items-center justify-between mb-1 gap-2">
                        <label class="font-bold text-slate-700 text-xs">Model máy lọc nước cần lắp <span class="text-rose-500">*</span></label>
                        <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">Gõ trực tiếp vào ô để tìm kiếm</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                        <!-- Direct Searchable Input for Machine Model -->
                        <div :class="form.machine_model_id ? 'col-span-12' : 'sm:col-span-8 col-span-12'" @click.away="modelDropdownOpen = false">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400"></i>
                                <input 
                                    type="text" 
                                    x-model="modelSearchInput" 
                                    @focus="modelDropdownOpen = true; highlightedModelIndex = 0" 
                                    @input="modelDropdownOpen = true; highlightedModelIndex = 0; onInputModelSearch()"
                                    @keydown.down.prevent="navigateModelDown()"
                                    @keydown.up.prevent="navigateModelUp()"
                                    @keydown.enter.prevent="selectHighlightedModel()"
                                    @keydown.escape.prevent="modelDropdownOpen = false"
                                    placeholder="Gõ tìm model: KG100HA, Karofi, Kangaroo... (phím ↑ ↓ Enter)" 
                                    class="w-full pl-9 pr-9 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs transition"
                                >
                                <button type="button" 
                                        x-show="modelSearchInput" 
                                        @click="clearModel(); modelSearchInput = '';" 
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer"
                                        title="Xóa để chọn lại">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>

                                <!-- Dropdown list directly underneath: RỘNG BẰNG 100% INPUT -->
                                <div 
                                    x-show="modelDropdownOpen" 
                                    x-cloak 
                                    class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-60 overflow-y-auto divide-y divide-slate-100"
                                >
                                    <template x-if="filteredModels.length === 0">
                                        <div class="p-4 text-center text-slate-400">
                                            <p>Không tìm thấy model nào phù hợp</p>
                                            <button type="button" @click="openModelModal(); modelDropdownOpen = false" class="mt-2 text-cyan-600 hover:underline font-bold text-xs inline-flex items-center gap-1 cursor-pointer">
                                                <i class="fa-solid fa-plus text-[10px]"></i> Thêm mới model này
                                            </button>
                                        </div>
                                    </template>
                                    <template x-for="(m, mIdx) in filteredModels" :key="m.id">
                                        <div 
                                            :id="'model-opt-' + mIdx"
                                            @click="selectModel(m)" 
                                            @mouseenter="highlightedModelIndex = mIdx"
                                            class="p-2.5 cursor-pointer flex items-center justify-between gap-2 transition"
                                            :class="highlightedModelIndex === mIdx ? 'bg-sky-100 text-sky-950 font-bold ring-1 ring-sky-300' : (form.machine_model_id == m.id ? 'bg-sky-50 text-sky-800 font-bold' : 'hover:bg-slate-50 text-slate-700')"
                                        >
                                            <div class="truncate flex-1 min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-black" :class="form.machine_model_id == m.id ? 'bg-sky-200 text-sky-800' : 'bg-slate-100 text-slate-600'" x-text="m.brand || 'Khác'"></span>
                                                    <span class="font-bold text-slate-800 text-xs truncate" x-text="m.name"></span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
                                                    <span>Mã: <strong x-text="m.code"></strong></span>
                                                    <span>&bull;</span>
                                                    <span x-text="(m.filter_stages || 10) + ' cấp lọc'"></span>
                                                    <span x-show="m.type">&bull;</span>
                                                    <span x-show="m.type" x-text="m.type"></span>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span x-show="m.standard_price" class="text-cyan-700 font-extrabold text-xs block" x-text="window.formatCurrency(m.standard_price)"></span>
                                                <i x-show="form.machine_model_id == m.id" class="fa-solid fa-check text-sky-600 text-xs"></i>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Ô nhập tên model máy tự do ngoài danh mục (Ẩn khi đã chọn model chuẩn từ danh mục) -->
                        <div class="sm:col-span-4 col-span-12" x-show="!form.machine_model_id">
                            <input type="text" x-model="form.machine_model" placeholder="Hoặc tên model tự nhập..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-semibold text-slate-800 text-xs">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Serial / IMEI máy (nếu có)</label>
                        <input type="text" x-model="form.machine_serial" placeholder="KG-2026-XXXX..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono font-bold text-slate-700 text-xs">
                    </div>

                    <!-- GIÁ MÁY LỌC NƯỚC (PHƯƠNG ÁN 3) -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-bold text-slate-700">Giá máy lọc nước (đ)</label>
                            <span class="text-[10px] text-sky-600 font-extrabold" x-show="form.machine_price > 0" x-text="window.formatCurrency(form.machine_price)"></span>
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-tag absolute left-3.5 top-3 text-slate-400"></i>
                            <input 
                                type="number" 
                                x-model.number="form.machine_price" 
                                min="0" 
                                step="10000" 
                                placeholder="0" 
                                class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-extrabold text-sky-900 text-xs text-right"
                            >
                        </div>
                    </div>

                    <!-- 2. TÌM KỸ THUẬT VIÊN BẰNG INPUT NHẬP TRỰC TIẾP -->
                    <div @click.away="techDropdownOpen = false">
                        <label class="block font-bold text-slate-700 mb-1">Kỹ thuật viên phụ trách lắp đặt</label>
                        <div class="relative">
                            <i class="fa-solid fa-user-gear absolute left-3.5 top-3 text-slate-400"></i>
                            <input 
                                type="text" 
                                x-model="techSearchInput" 
                                @focus="techDropdownOpen = true" 
                                @input="techDropdownOpen = true; onInputTechSearch()"
                                placeholder="Gõ tìm KTV (Tên, SĐT, phòng ban)..." 
                                class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs transition"
                            >

                            <!-- Dropdown list directly underneath: RỘNG BẰNG 100% INPUT -->
                            <div 
                                x-show="techDropdownOpen" 
                                x-cloak 
                                class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-52 overflow-y-auto divide-y divide-slate-100"
                            >
                                <div @click="selectTechnician(null)" class="p-2.5 hover:bg-slate-50 cursor-pointer text-slate-500 italic flex items-center justify-between">
                                    <span>-- Chưa chỉ định (Để sau) --</span>
                                    <i x-show="!form.technician_id" class="fa-solid fa-check text-sky-600 text-xs"></i>
                                </div>
                                <template x-if="filteredTechnicians.length === 0">
                                    <div class="p-3 text-center text-slate-400">Không tìm thấy KTV phù hợp</div>
                                </template>
                                <template x-for="t in filteredTechnicians" :key="t.id">
                                    <div 
                                        @click="selectTechnician(t)" 
                                        class="p-2.5 hover:bg-sky-50/70 cursor-pointer flex items-center justify-between gap-2 transition"
                                        :class="form.technician_id == t.id ? 'bg-sky-50 text-sky-800 font-bold' : 'text-slate-700'"
                                    >
                                        <div class="truncate flex-1 min-w-0">
                                            <div class="font-bold text-slate-800 text-xs" x-text="t.name"></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5" x-text="(t.department || 'Bộ phận Kỹ thuật') + (t.phone ? ' • ' + t.phone : '')"></div>
                                        </div>
                                        <i x-show="form.technician_id == t.id" class="fa-solid fa-check text-sky-600 text-xs"></i>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. TRẠNG THÁI ĐƠN HÀNG CHI TIẾT -->
                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50/80 border border-slate-200 space-y-2.5 sm:space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <label class="font-extrabold text-slate-800 flex items-center gap-1.5 text-xs shrink-0">
                            <i class="fa-solid fa-timeline text-sky-600"></i>
                            <span>Trạng thái đơn:</span>
                        </label>
                        <span class="px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-black uppercase whitespace-nowrap shrink-0" :class="getStatusBadgeClass(form.status)" x-text="getStatusLabel(form.status)"></span>
                    </div>

                    <select x-model="form.status" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-extrabold text-slate-800 text-xs shadow-2xs">
                        @foreach($statuses as $stKey => $stVal)
                            <option value="{{ $stKey }}">{{ $loop->iteration }}. {{ $stVal['label'] }}</option>
                        @endforeach
                    </select>

                    <p class="text-[11px] text-slate-500 hidden sm:block">
                        * Bạn có thể chủ động cập nhật trạng thái đơn qua từng giai đoạn (*Mới tiếp nhận* ➔ *Đã hẹn lịch* ➔ *Đang chuẩn bị* ➔ *Đang lắp* ➔ *Hoàn thành*).
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Lịch hẹn lắp đặt</label>
                        <input type="datetime-local" x-model="form.appointment_date" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Trạng thái thanh toán</label>
                        <select x-model="form.payment_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs">
                            <option value="unpaid">Chưa thu tiền</option>
                            <option value="paid">Đã thanh toán đủ</option>
                            <option value="free">Miễn phí theo máy</option>
                        </select>
                    </div>
                </div>

                <!-- 4. THÔNG TIN TEM QR & BẢO HÀNH NGHIỆM THU (CHỈ HIỂN THỊ KHI ĐƠN ĐÃ CÓ KTV NGHIỆM THU / DÁN TEM QR) -->
                <div x-show="isEdit && (form.warranty_card_code || form.status === 'completed' || form.status === 'inspecting')" 
                     x-cloak 
                     class="p-4 rounded-xl bg-slate-50/80 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-extrabold text-slate-800 flex items-center gap-1.5 text-xs">
                            <i class="fa-solid fa-qrcode text-cyan-600"></i>
                            <span>Thông tin Tem QR & Bảo Hành Nghiệm Thu:</span>
                        </label>
                        <span class="text-[10px] text-slate-400">KTV dán tem và kích hoạt</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 text-[11px]">Mã Tem QR dán máy</label>
                            <input type="text" x-model="form.warranty_card_code" placeholder="vd: ML-202610-6885" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 font-bold font-mono text-cyan-800 text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 text-[11px]">Thời hạn BH (tháng)</label>
                            <input type="number" x-model.number="form.warranty_months" min="0" placeholder="24" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 font-bold text-center text-slate-800 text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 text-[11px]">Chu kỳ thay lõi (tháng)</label>
                            <input type="number" x-model.number="form.filter_replacement_months" min="0" placeholder="6" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 font-bold text-center text-slate-800 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1 text-[11px]">Ghi chú nghiệm thu của KTV</label>
                        <textarea x-model="form.completion_notes" rows="2" placeholder="Ghi nhận tình trạng lắp đặt, lưu ý của KTV..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 text-slate-700 text-xs"></textarea>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION: PHỤ TÙNG, PHỤ KIỆN XUẤT THEO ĐƠN MÁY LỌC (TÌM BẰNG INPUT NHẬP TRỰC TIẾP) -->
    <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3.5 sm:space-y-4 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <div class="flex items-center gap-2 font-extrabold text-slate-800 text-xs sm:text-sm">
                    <i class="fa-solid fa-boxes-stacked text-teal-600 shrink-0"></i>
                    <span class="hidden sm:inline">Phụ Tùng, Vật Tư & Linh Kiện Mang Theo Đơn Lắp Đặt</span>
                    <span class="sm:hidden">Phụ Tùng & Vật Tư Đi Kèm</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5 hidden sm:block">
                    Gõ trực tiếp vào ô input để tìm mã hoặc tên phụ tùng từ kho (tự động trừ kho khi hoàn thành đơn)
                </p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button 
                    type="button" 
                    x-show="getSelectedModelHasDefaultParts()" 
                    @click="applyModelDefaultParts(getSelectedModel(), true)" 
                    class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 text-cyan-800 font-bold transition border border-cyan-200 cursor-pointer text-xs shadow-2xs whitespace-nowrap min-h-[42px]" 
                    title="Nạp lại danh sách phụ tùng cấu hình sẵn của model này"
                >
                    <i class="fa-solid fa-arrows-rotate text-xs text-cyan-600"></i>
                    <span class="hidden sm:inline">Nạp phụ tùng theo Model</span>
                    <span class="sm:hidden">Nạp theo Model</span>
                </button>
                <button type="button" @click="addItemRow()" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold transition shadow-xs cursor-pointer text-xs whitespace-nowrap min-h-[42px]">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span class="hidden sm:inline">Thêm vật tư phụ tùng</span>
                    <span class="sm:hidden">Thêm vật tư</span>
                </button>
            </div>
        </div>

        <!-- MOBILE VIEW: DANH SÁCH PHỤ TÙNG DẠNG THẺ (KHÔNG BỊ TRÀN NGANG) -->
        <div class="block sm:hidden space-y-3">
            <template x-if="form.items.length === 0">
                <div class="py-6 text-center text-slate-400 bg-slate-50/50 rounded-xl border border-slate-200 p-4">
                    <i class="fa-solid fa-box-open text-2xl mb-1 text-slate-300 block"></i>
                    <span>Chưa có phụ kiện nào đi kèm (Nhấn "Thêm vật tư" ở trên)</span>
                </div>
            </template>
            <template x-for="(item, idx) in form.items" :key="idx">
                <div class="p-3 bg-slate-50/90 rounded-xl border border-slate-200/90 space-y-2.5 shadow-2xs">
                    <!-- Row 1: STT + Tồn kho + Xóa -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 font-extrabold text-xs flex items-center justify-center shrink-0" x-text="idx + 1"></span>
                            <span class="text-[11px] font-bold text-slate-500">
                                Tồn kho: <strong class="text-teal-700" x-text="item.stock_available ?? '-'"></strong> <span x-text="item.unit || 'Cái'"></span>
                            </span>
                        </div>
                        <button type="button" @click="removeItemRow(idx)" class="w-7 h-7 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg flex items-center justify-center cursor-pointer transition" title="Xóa phụ tùng">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>

                    <!-- Row 2: Ô tìm kiếm tên phụ tùng -->
                    <div class="relative" x-data="{ open: false, highlightedPartIndex: 0 }" @click.away="open = false">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input 
                            type="text" 
                            x-model="item.part_name" 
                            @focus="open = true; highlightedPartIndex = 0" 
                            @input="open = true; highlightedPartIndex = 0; onTypePartName(idx, $event.target.value)"
                            placeholder="Gõ tìm mã hoặc tên phụ tùng..." 
                            class="w-full pl-8 pr-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-500 focus:outline-none"
                        >

                        <!-- Dropdown list -->
                        <div 
                            x-show="open && filterPartsBy(item.part_name, idx).length > 0" 
                            x-cloak 
                            class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-52 overflow-y-auto divide-y divide-slate-100"
                        >
                            <template x-for="(p, pIdx) in filterPartsBy(item.part_name, idx)" :key="p.id">
                                <div 
                                    @click="onSelectPart(idx, p.id); open = false" 
                                    class="p-2.5 cursor-pointer flex items-center justify-between gap-2 hover:bg-teal-50/70"
                                >
                                    <div class="truncate flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-600" x-text="p.code"></span>
                                            <span class="font-bold text-slate-800 truncate" x-text="p.name"></span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            <span x-text="'Tồn: ' + p.stock_qty + ' ' + p.unit"></span>
                                        </div>
                                    </div>
                                    <span class="text-teal-800 font-bold text-xs shrink-0" x-text="window.formatCurrency(p.out_price)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Row 3: Nút phân loại phí + Số lượng -->
                    <div class="flex items-center justify-between gap-2 pt-0.5">
                        <button 
                            type="button" 
                            @click="toggleItemFree(idx)" 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-black cursor-pointer transition shadow-2xs active:scale-95"
                            :class="item.is_free ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-900 border border-amber-300'"
                        >
                            <i class="fa-solid" :class="item.is_free ? 'fa-gift text-emerald-600' : 'fa-hand-holding-dollar text-amber-600'"></i>
                            <span x-text="item.is_free ? 'Theo máy (0đ)' : 'Thu thêm tiền'"></span>
                        </button>

                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-600">SL:</span>
                            <input type="number" x-model.number="item.quantity" min="1" class="w-16 px-2 py-1 bg-white border border-slate-300 rounded-lg text-xs font-black text-center focus:ring-2 focus:ring-teal-500">
                            <span class="text-xs text-slate-500 font-medium" x-text="item.unit || 'Cái'"></span>
                        </div>
                    </div>

                    <!-- Row 4: Đơn giá và Thành tiền (Chỉ hiện khi tính phí thu thêm) -->
                    <div x-show="!item.is_free" class="p-2 bg-white rounded-lg border border-teal-200 text-xs flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1">
                            <span class="text-slate-500 font-bold">Đơn giá:</span>
                            <input 
                                type="number" 
                                x-model.number="item.unit_price" 
                                @input="onUnitPriceChange(idx)"
                                min="0" 
                                step="1000" 
                                placeholder="0" 
                                class="w-24 px-2 py-0.5 border border-slate-300 rounded font-black text-right text-xs text-teal-800"
                            >
                        </div>
                        <div>
                            <span class="text-slate-500 font-medium">Bù: </span>
                            <strong class="text-teal-800 font-black" x-text="window.formatCurrency(item.quantity * item.unit_price)"></strong>
                        </div>
                    </div>

                    <!-- Row 5: Ghi chú vị trí -->
                    <input type="text" x-model="item.note" placeholder="Vị trí lắp, ghi chú..." class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs text-slate-700">
                </div>
            </template>
        </div>

        <!-- DESKTOP VIEW: BẢNG PHỤ TÙNG 10 CỘT ĐẦY ĐỦ -->
        <div class="hidden sm:block overflow-visible border border-slate-200 rounded-xl min-h-[220px]">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3 w-12 text-center">STT</th>
                        <th class="py-3 px-3 min-w-[280px]">Phụ tùng / Linh kiện (Ô nhập tìm kiếm trực tiếp)</th>
                        <th class="py-3 px-2 w-32 text-center">Loại tính phí</th>
                        <th class="py-3 px-3 w-28 text-center">Tồn kho</th>
                        <th class="py-3 px-3 w-20 text-center">ĐVT</th>
                        <th class="py-3 px-3 w-24 text-center">Số lượng</th>
                        <th class="py-3 px-3 w-32 text-right">Đơn giá thêm (đ)</th>
                        <th class="py-3 px-3 w-32 text-right">Thành tiền (đ)</th>
                        <th class="py-3 px-3 min-w-[160px]">Ghi chú</th>
                        <th class="py-3 px-3 w-20 text-center">Xử lý</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <template x-if="form.items.length === 0">
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-2xl mb-1 text-slate-300 block"></i>
                                Chưa có phụ kiện nào đi kèm (Nhấn "Thêm vật tư phụ tùng" nếu khách mua thêm cút nối, van, vòi, bình áp...)
                            </td>
                        </tr>
                    </template>
                    <template x-for="(item, idx) in form.items" :key="idx">
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3 text-center text-slate-400 font-bold" x-text="idx + 1"></td>
                            
                            <!-- 4. Ô TÌM BẰNG INPUT NHẬP TRỰC TIẾP (RỘNG BẰNG 100% INPUT) -->
                            <td class="py-2.5 px-3" x-data="{ open: false, highlightedPartIndex: 0 }" @click.away="open = false">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                    <input 
                                        type="text" 
                                        x-model="item.part_name" 
                                        @focus="open = true; highlightedPartIndex = 0" 
                                        @input="open = true; highlightedPartIndex = 0; onTypePartName(idx, $event.target.value)"
                                        @keydown.down.prevent="
                                            open = true;
                                            const pList = filterPartsBy(item.part_name, idx);
                                            if (pList.length > 0) {
                                                highlightedPartIndex = Math.min(highlightedPartIndex + 1, pList.length - 1);
                                                $nextTick(() => {
                                                    const el = document.getElementById('part-opt-' + idx + '-' + highlightedPartIndex);
                                                    if (el) el.scrollIntoView({ block: 'nearest' });
                                                });
                                            }
                                        "
                                        @keydown.up.prevent="
                                            if (open) {
                                                highlightedPartIndex = Math.max(highlightedPartIndex - 1, 0);
                                                $nextTick(() => {
                                                    const el = document.getElementById('part-opt-' + idx + '-' + highlightedPartIndex);
                                                    if (el) el.scrollIntoView({ block: 'nearest' });
                                                });
                                            }
                                        "
                                        @keydown.enter.prevent="
                                            if (open) {
                                                const pList = filterPartsBy(item.part_name, idx);
                                                if (pList.length > 0 && pList[highlightedPartIndex]) {
                                                    onSelectPart(idx, pList[highlightedPartIndex].id);
                                                    open = false;
                                                }
                                            }
                                        "
                                        @keydown.escape.prevent="open = false"
                                        placeholder="Gõ tìm mã hoặc tên phụ tùng... (phím ↑ ↓ Enter)" 
                                        class="w-full pl-8 pr-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-500 focus:outline-none placeholder-slate-400 transition"
                                    >

                                    <!-- Dropdown suggestions list: RỘNG BẰNG 100% INPUT (left-0 right-0 w-full) -->
                                    <div 
                                        x-show="open && filterPartsBy(item.part_name, idx).length > 0" 
                                        x-cloak 
                                        class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-60 overflow-y-auto divide-y divide-slate-100"
                                    >
                                        <template x-for="(p, pIdx) in filterPartsBy(item.part_name, idx)" :key="p.id">
                                             <div 
                                                :id="'part-opt-' + idx + '-' + pIdx"
                                                @click="onSelectPart(idx, p.id); open = false" 
                                                @mouseenter="highlightedPartIndex = pIdx"
                                                class="p-2.5 cursor-pointer flex items-center justify-between gap-2 transition"
                                                :class="highlightedPartIndex === pIdx ? 'bg-teal-100 text-teal-950 font-bold ring-1 ring-teal-300' : (item.part_id == p.id ? 'bg-teal-50 font-bold' : 'hover:bg-teal-50/70 text-slate-700')"
                                            >
                                                <div class="truncate flex-1 min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-600 shrink-0" x-text="p.code"></span>
                                                        <span class="font-bold text-slate-800 truncate" x-text="p.name"></span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
                                                        <span x-text="p.category"></span>
                                                        <span>&bull;</span>
                                                        <span :class="p.stock_qty <= p.min_stock_alert ? 'text-rose-600 font-bold' : 'text-teal-700 font-semibold'" x-text="'Tồn: ' + p.stock_qty + ' ' + p.unit"></span>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class="text-teal-800 font-bold text-xs" x-text="window.formatCurrency(p.out_price)"></span>
                                                    <i x-show="item.part_id == p.id" class="fa-solid fa-check text-teal-600 text-xs ml-1.5"></i>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            <!-- NÚT GẠT PHÂN LOẠI: THEO MÁY (0đ) VS THU THÊM TIỀN -->
                            <td class="py-3 px-2 text-center whitespace-nowrap">
                                <button 
                                    type="button" 
                                    @click="toggleItemFree(idx)" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-black cursor-pointer transition shadow-2xs active:scale-95"
                                    :class="item.is_free ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 hover:bg-emerald-100' : 'bg-amber-50 text-amber-800 border border-amber-200/80 hover:bg-amber-100'"
                                    :title="item.is_free ? 'Đang miễn phí kèm theo máy (0đ). Bấm để chuyển sang tính phí thu thêm' : 'Đang tính phí thu thêm. Bấm để chuyển sang miễn phí theo máy'"
                                >
                                    <i class="fa-solid" :class="item.is_free ? 'fa-gift text-emerald-600' : 'fa-hand-holding-dollar text-amber-600'"></i>
                                    <span x-text="item.is_free ? 'Theo máy (0đ)' : 'Thu thêm tiền'"></span>
                                </button>
                            </td>

                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700" x-text="item.stock_available ?? '-'"></span>
                            </td>
                            <td class="py-3 px-3 text-center text-slate-600 font-semibold" x-text="item.unit || 'Cái'"></td>
                            <td class="py-3 px-3 text-center">
                                <input type="number" x-model.number="item.quantity" min="1" class="w-18 px-2 py-1.5 border border-slate-300 rounded-lg text-xs font-black text-center focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            </td>
                            <td class="py-3 px-3 text-right">
                                <input 
                                    type="number" 
                                    x-model.number="item.unit_price" 
                                    @input="onUnitPriceChange(idx)"
                                    min="0" 
                                    step="1000" 
                                    placeholder="0 (bù tiền)"
                                    class="w-28 px-2 py-1.5 border rounded-lg text-xs font-bold text-right focus:ring-2 focus:ring-teal-500 focus:outline-none transition"
                                    :class="item.is_free ? 'bg-slate-50 text-slate-500 border-slate-300' : 'bg-white text-teal-800 font-black border-teal-400 ring-1 ring-teal-200'"
                                >
                            </td>
                            <td class="py-3 px-3 text-right font-extrabold" :class="item.is_free ? 'text-slate-400' : 'text-teal-800'">
                                <template x-if="item.is_free">
                                    <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">0 đ (Theo máy)</span>
                                </template>
                                <template x-if="!item.is_free">
                                    <span x-text="window.formatCurrency(item.quantity * item.unit_price)"></span>
                                </template>
                            </td>
                            <td class="py-3 px-3">
                                <input type="text" x-model="item.note" placeholder="Vị trí lắp, ghi chú..." class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none text-slate-700">
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <button 
                                    type="button" 
                                    @click="removeItemRow(idx)" 
                                    class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer" 
                                    title="Xóa dòng"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- SUMMARY CALCULATION (ĐIỀU CHỈNH GIÁ LINH HOẠT) -->
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 sm:gap-4 p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-slate-50 to-sky-50/50 border border-slate-200/90 shadow-2xs">
            <div class="text-xs text-slate-600 space-y-1">
                <div class="font-extrabold text-slate-800 flex items-center gap-1.5 text-xs sm:text-sm">
                    <i class="fa-solid fa-receipt text-sky-600"></i>
                    <span>TỔNG HỢP THANH TOÁN ĐƠN LẮP ĐẶT</span>
                </div>
                <p class="text-[11px] text-slate-500 hidden sm:block">
                    Giá trọn gói theo máy lọc nước. Phụ tùng đi kèm mặc định không tăng giá (0đ), chỉ tính thêm khi đánh dấu <strong>Thu thêm tiền</strong>.
                </p>
            </div>
            
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2.5 sm:gap-4 w-full lg:w-auto">
                <!-- Tiền máy -->
                <div class="bg-white sm:bg-transparent p-2.5 sm:p-0 rounded-xl border sm:border-0 border-slate-200 text-left sm:text-right">
                    <label class="text-slate-500 text-[10px] sm:text-[11px] font-bold block mb-0.5">1. Tiền máy (đ):</label>
                    <input type="number" 
                           x-model.number="form.machine_price" 
                           min="0" 
                           step="10000" 
                           placeholder="0" 
                           class="w-full sm:w-28 px-2 py-1 text-right text-xs font-black text-slate-800 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-400 focus:outline-none">
                </div>

                <!-- Phụ kiện thu thêm -->
                <div class="bg-white sm:bg-transparent p-2.5 sm:p-0 rounded-xl border sm:border-0 border-slate-200 text-left sm:text-right sm:pl-3 sm:border-l sm:border-slate-200">
                    <span class="text-slate-500 text-[10px] sm:text-[11px] font-bold block mb-0.5">2. Thu thêm:</span>
                    <span class="font-black text-teal-700 text-xs sm:text-sm leading-6" x-text="window.formatCurrency(calcPartsFee())"></span>
                </div>

                <!-- Ô nhập Chiết khấu / Giảm giá (-) -->
                <div class="bg-white sm:bg-transparent p-2.5 sm:p-0 rounded-xl border sm:border-0 border-slate-200 text-left sm:text-right sm:pl-3 sm:border-l sm:border-slate-200">
                    <label class="text-slate-500 text-[10px] sm:text-[11px] font-bold block mb-0.5">3. Giảm giá (-):</label>
                    <input type="number" 
                           x-model.number="form.discount_amount" 
                           min="0" 
                           step="10000" 
                           placeholder="0" 
                           class="w-full sm:w-28 px-2 py-1 text-right text-xs font-black text-rose-600 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-400 focus:outline-none">
                </div>

                <!-- TỔNG CẦN THU -->
                <div class="col-span-2 sm:col-auto bg-white px-3.5 py-2 rounded-xl border-2 border-sky-400 shadow-2xs flex items-center justify-between sm:block text-right">
                    <span class="text-sky-800 text-[10px] sm:text-[11px] font-black uppercase tracking-wider block">TỔNG CẦN THU:</span>
                    <span class="text-base sm:text-xl font-black text-sky-700 ml-2 sm:ml-0" x-text="window.formatCurrency(calcTotalAmount())"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="flex items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 pt-3 border-t border-slate-100 sm:border-0">
        <a href="{{ $returnUrl }}" class="px-4 sm:px-5 py-2.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl transition text-xs cursor-pointer shrink-0 min-h-[42px] flex items-center justify-center shadow-2xs">
            <i class="fa-solid fa-xmark mr-1.5 text-slate-400"></i> Hủy bỏ
        </a>
        <button @click="submitForm()" :disabled="submitting" type="button" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed min-h-[42px] whitespace-nowrap">
            <i class="fa-solid" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check text-sm'"></i>
            <span class="sm:hidden" x-text="submitting ? 'Đang lưu...' : (isEdit ? 'Lưu thay đổi' : 'Tạo đơn lắp đặt')"></span>
            <span class="hidden sm:inline" x-text="submitting ? 'Đang lưu đơn...' : (isEdit ? 'Lưu cập nhật đơn hàng' : 'Tạo mới đơn lắp đặt')"></span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL THÊM MODEL MÁY MỚI (TẠO TRỰC TIẾP TẠI CHỖ)             -->
    <!-- ============================================================== -->
    <div x-show="showModelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showModelModal = false" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 to-sky-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-faucet-drip text-lg"></i>
                    <h3 class="font-bold text-base">THÊM MỚI MODEL MÁY LỌC NƯỚC</h3>
                </div>
                <button type="button" @click="showModelModal = false" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="saveNewModelSubmit()" class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Model máy <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="newModelForm.code" required placeholder="vd: KG100HA, KRF-612..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold uppercase focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thương hiệu / Hãng <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="newModelForm.brand" required placeholder="Kangaroo, Karofi, Sunhouse..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tên Model máy lọc nước <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="newModelForm.name" required placeholder="Máy lọc RO Kangaroo Hydrogen 10 lõi..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-semibold focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số cấp lọc (lõi)</label>
                        <input type="number" x-model.number="newModelForm.filter_stages" min="1" max="20" placeholder="vd: 10" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-center focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Loại kiểu dáng</label>
                        <select x-model="newModelForm.type" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            <option value="Tủ đứng">Tủ đứng</option>
                            <option value="Để bàn">Để bàn mini</option>
                            <option value="Lắp gầm chậu">Lắp gầm chậu rửa</option>
                            <option value="Nóng Lạnh RO">Nóng Lạnh RO</option>
                            <option value="Bán công nghiệp">Bán công nghiệp</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Giá bán niêm yết / tham khảo (đ)</label>
                    <div class="relative">
                        <input type="text" inputmode="numeric" x-money="newModelForm.standard_price" placeholder="0" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-cyan-800 focus:ring-2 focus:ring-cyan-500 focus:outline-none pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">đ</span>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mô tả đặc tính kỹ thuật</label>
                    <textarea x-model="newModelForm.description" rows="2" placeholder="Công nghệ RO vortex, màng Filmtec, bình áp thép..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="showModelModal = false" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu & Chọn model này</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function installationFormModule() {
    const initData = @json($installation ?? null);
    const partsCatalog = @json($parts ?? []);
    const modelsCatalog = @json($machineModels ?? []);
    const techCatalog = @json($technicians ?? []);
    const statusMap = @json($statuses ?? []);
    const customersCatalog = @json($customers ?? []);
    const returnUrl = @json($returnUrl);

    return {
        returnUrl: returnUrl,
        isEdit: !!(initData && initData.id),
        parts: partsCatalog,
        machineModels: modelsCatalog,
        technicians: techCatalog,
        statuses: statusMap,
        customers: customersCatalog,
        showModelModal: false,
        submitting: false,

        // Direct input search states
        customerDropdownOpen: false,
        highlightedCustomerIndex: 0,
        modelDropdownOpen: false,
        highlightedModelIndex: 0,
        modelSearchInput: '',
        techDropdownOpen: false,
        techSearchInput: '',

        form: {
            id: initData ? initData.id : null,
            dealer_id: initData ? (initData.dealer_id || '') : '',
            customer_name: initData ? initData.customer_name : '',
            customer_phone: initData ? initData.customer_phone : '',
            customer_address: initData ? initData.customer_address : '',
            customer_notes: initData ? (initData.customer_notes || '') : '',
            machine_price: initData ? (Number(initData.machine_price) || 0) : 0,
            surcharge_amount: initData ? (Number(initData.surcharge_amount) || 0) : 0,
            discount_amount: initData ? (Number(initData.discount_amount) || 0) : 0,
            machine_model_id: initData ? initData.machine_model_id : '',
            machine_model: (function() {
                if (!initData) return '';
                if (typeof initData.machine_model === 'string') return initData.machine_model;
                if (initData.machine_model && typeof initData.machine_model === 'object' && initData.machine_model.name) return initData.machine_model.name;
                if (initData.model_catalog && initData.model_catalog.name) return initData.model_catalog.name;
                return '';
            })(),
            machine_serial: initData ? (initData.machine_serial || '') : '',
            technician_id: initData ? (initData.technician_id || '') : '',
            status: initData ? initData.status : 'pending',
            appointment_date: initData && initData.appointment_date ? initData.appointment_date.replace(' ', 'T').substring(0, 16) : '',
            payment_status: initData ? initData.payment_status : 'unpaid',
            installed_at: initData && initData.installed_at ? initData.installed_at.replace(' ', 'T').substring(0, 16) : '',
            warranty_months: initData ? (Number(initData.warranty_months) || 24) : 24,
            warranty_until: initData && initData.warranty_until ? (initData.warranty_until.length > 10 ? initData.warranty_until.substring(0, 10) : initData.warranty_until) : '',
            warranty_card_code: initData ? (initData.warranty_card_code || (initData.installed_machine?.qr_code || '')) : '',
            filter_replacement_months: initData ? (Number(initData.filter_replacement_months) || (initData.installed_machine?.filter_replacement_months || 6)) : 6,
            warranty_notes: initData ? (initData.warranty_notes || '') : '',
            completion_notes: initData ? (initData.completion_notes || (initData.installed_machine?.activation_notes || '')) : '',
            items: initData && initData.items ? initData.items.map(it => {
                const foundPart = partsCatalog.find(p => p.id == it.part_id);
                const isFree = (it.is_free !== undefined && it.is_free !== null) ? Boolean(it.is_free) : (Number(it.unit_price || 0) === 0);
                return {
                    part_id: it.part_id,
                    part_name: it.part_name,
                    unit: it.unit,
                    quantity: Number(it.quantity || 1),
                    is_free: isFree,
                    unit_price: isFree ? 0 : Number(it.unit_price || 0),
                    note: it.note || '',
                    stock_available: foundPart ? foundPart.stock_qty : '-'
                };
            }) : []
        },

        newModelForm: {
            code: '',
            brand: 'Karofi',
            name: '',
            filter_stages: 10,
            type: 'Tủ đứng',
            standard_price: 5000000,
            description: ''
        },

        init() {
            // Auto match and populate input values
            if (this.form.machine_model_id) {
                const foundM = this.machineModels.find(m => m.id == this.form.machine_model_id);
                if (foundM) {
                    this.modelSearchInput = '[' + (foundM.brand || 'Khác') + '] ' + foundM.name;
                    if (!this.form.machine_model || typeof this.form.machine_model !== 'string') {
                        this.form.machine_model = foundM.name;
                    }
                }
            } else if (this.form.machine_model) {
                this.modelSearchInput = typeof this.form.machine_model === 'string'
                    ? this.form.machine_model
                    : (this.form.machine_model.name || '');
            }

            if (this.form.technician_id) {
                const foundT = this.technicians.find(t => t.id == this.form.technician_id);
                if (foundT) {
                    this.techSearchInput = foundT.name + ' (' + (foundT.department || 'KTV') + ')';
                }
            }

            // Auto calculate warranty_until if missing but installed_at exists
            if (this.form.installed_at && !this.form.warranty_until) {
                this.recalcWarrantyUntil();
            }
        },

        recalcWarrantyUntil() {
            if (!this.form.installed_at) return;
            try {
                const datePart = this.form.installed_at.split('T')[0];
                const parts = datePart.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + (Number(this.form.warranty_months) || 24));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.form.warranty_until = `${y}-${m}-${day}`;
                }
            } catch (e) {
                console.error(e);
            }
        },

        /* -------------------------------------------------------------
         * 0. SEARCHABLE CUSTOMERS (DIRECT INPUT)
         * ------------------------------------------------------------- */
        get filteredCustomers() {
            const qName = (this.form.customer_name || '').toLowerCase().trim();
            const qPhone = (this.form.customer_phone || '').toLowerCase().trim();
            if (!qName && !qPhone) {
                return (this.customers || []).slice(0, 8);
            }
            return (this.customers || []).filter(c => {
                const matchName = qName && c.name && c.name.toLowerCase().includes(qName);
                const matchPhone = qPhone && c.phone && c.phone.toLowerCase().includes(qPhone);
                const matchCode = qName && c.code && c.code.toLowerCase().includes(qName);
                return matchName || matchPhone || matchCode;
            }).slice(0, 15);
        },

        selectCustomer(c) {
            if (!c) return;
            this.form.dealer_id = c.id;
            this.form.customer_name = c.name;
            this.form.customer_phone = c.phone || '';
            this.form.customer_address = c.address || '';
            this.customerDropdownOpen = false;
        },

        onInputCustomerSearch() {
            const q = (this.form.customer_name || '').toLowerCase().trim();
            if (!q) {
                this.form.dealer_id = '';
                return;
            }
            const found = (this.customers || []).find(c => c.name && c.name.toLowerCase().trim() === q);
            if (found) {
                this.form.dealer_id = found.id;
                if (!this.form.customer_phone && found.phone) this.form.customer_phone = found.phone;
                if (!this.form.customer_address && found.address) this.form.customer_address = found.address;
            } else {
                this.form.dealer_id = '';
            }
        },

        onInputCustomerPhone() {
            const q = (this.form.customer_phone || '').trim();
            if (q.length >= 9) {
                const found = (this.customers || []).find(c => c.phone && c.phone.trim() === q);
                if (found) {
                    this.form.dealer_id = found.id;
                    if (!this.form.customer_name) this.form.customer_name = found.name;
                    if (!this.form.customer_address && found.address) this.form.customer_address = found.address;
                }
            }
        },

        navigateCustomerDown() {
            this.customerDropdownOpen = true;
            const list = this.filteredCustomers;
            if (!list || list.length === 0) return;
            this.highlightedCustomerIndex = Math.min(this.highlightedCustomerIndex + 1, list.length - 1);
            this.scrollCustomerIntoView(this.highlightedCustomerIndex);
        },

        navigateCustomerUp() {
            if (!this.customerDropdownOpen) return;
            this.highlightedCustomerIndex = Math.max(this.highlightedCustomerIndex - 1, 0);
            this.scrollCustomerIntoView(this.highlightedCustomerIndex);
        },

        selectHighlightedCustomer() {
            if (this.customerDropdownOpen) {
                const list = this.filteredCustomers;
                if (list && list.length > 0 && list[this.highlightedCustomerIndex]) {
                    this.selectCustomer(list[this.highlightedCustomerIndex]);
                }
            }
        },

        scrollCustomerIntoView(idx) {
            this.$nextTick(() => {
                const el = document.getElementById('cust-opt-' + idx);
                if (el) {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
        },

        /* -------------------------------------------------------------
         * 1. SEARCHABLE MACHINE MODELS (DIRECT INPUT)
         * ------------------------------------------------------------- */
        get filteredModels() {
            if (!this.modelSearchInput || !this.modelSearchInput.trim()) return this.machineModels;
            const q = this.modelSearchInput.toLowerCase().trim();
            return this.machineModels.filter(m => 
                (m.name && m.name.toLowerCase().includes(q)) ||
                (m.code && m.code.toLowerCase().includes(q)) ||
                (m.brand && m.brand.toLowerCase().includes(q))
            );
        },

        onInputModelSearch() {
            this.form.machine_model = this.modelSearchInput;
            // Check if matches an existing model exactly
            const found = this.machineModels.find(m => m.name.toLowerCase() === this.modelSearchInput.toLowerCase().trim());
            if (found) {
                this.selectModel(found);
            } else {
                this.form.machine_model_id = '';
            }
        },

        navigateModelDown() {
            this.modelDropdownOpen = true;
            const list = this.filteredModels;
            if (!list || list.length === 0) return;
            this.highlightedModelIndex = Math.min(this.highlightedModelIndex + 1, list.length - 1);
            this.scrollModelIntoView(this.highlightedModelIndex);
        },

        navigateModelUp() {
            if (!this.modelDropdownOpen) return;
            this.highlightedModelIndex = Math.max(this.highlightedModelIndex - 1, 0);
            this.scrollModelIntoView(this.highlightedModelIndex);
        },

        selectHighlightedModel() {
            if (this.modelDropdownOpen) {
                const list = this.filteredModels;
                if (list && list.length > 0 && list[this.highlightedModelIndex]) {
                    this.selectModel(list[this.highlightedModelIndex]);
                }
            }
        },

        scrollModelIntoView(idx) {
            this.$nextTick(() => {
                const el = document.getElementById('model-opt-' + idx);
                if (el) {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
        },

        selectModel(model) {
            this.form.machine_model_id = model.id;
            this.form.machine_model = model.name;
            if (model.standard_price && (!this.form.machine_price || this.form.machine_price == 0)) {
                this.form.machine_price = Number(model.standard_price || 0);
            }
            this.modelSearchInput = '[' + (model.brand || 'Khác') + '] ' + model.name;
            this.modelDropdownOpen = false;

            // Tự động thêm các phụ tùng cấu hình sẵn của model này vào đơn
            this.applyModelDefaultParts(model);
        },

        clearModel() {
            this.form.machine_model_id = '';
            this.form.machine_model = '';
            this.modelSearchInput = '';
            this.modelDropdownOpen = false;
        },

        getSelectedModel() {
            if (!this.form.machine_model_id) return null;
            return this.machineModels.find(m => m.id == this.form.machine_model_id) || null;
        },

        getSelectedModelHasDefaultParts() {
            const m = this.getSelectedModel();
            if (!m) return false;
            let dp = m.default_parts;
            if (typeof dp === 'string') {
                try { dp = JSON.parse(dp); } catch (e) { dp = []; }
            }
            return Array.isArray(dp) && dp.length > 0;
        },

        applyModelDefaultParts(model, force = false) {
            if (!model) return;
            let defaultParts = model.default_parts;
            if (typeof defaultParts === 'string') {
                try { defaultParts = JSON.parse(defaultParts); } catch (e) { defaultParts = []; }
            }
            if (!Array.isArray(defaultParts) || defaultParts.length === 0) return;

            // Kiểm tra xem danh sách hiện tại đã có phụ tùng hay chưa
            const hasExistingData = this.form.items && this.form.items.some(it => it.part_id || (it.part_name && it.part_name.trim()));

            if (hasExistingData && !force) {
                if (!confirm(`Model "${model.name}" có ${defaultParts.length} phụ tùng cấu hình sẵn. Bạn có muốn nạp các phụ tùng này vào danh sách vật tư không?\n\n• Bấm OK: Nạp phụ tùng của model này (thay thế phụ tùng hiện tại)\n• Bấm Hủy: Giữ nguyên danh sách hiện tại`)) {
                    return;
                }
            }

            const newItems = [];
            for (const dp of defaultParts) {
                // Tìm kiếm thông tin kho tương ứng
                let foundPart = null;
                if (dp.part_id) {
                    foundPart = this.parts.find(p => p.id == dp.part_id);
                }
                if (!foundPart && dp.part_code) {
                    foundPart = this.parts.find(p => p.code && p.code.toLowerCase() === String(dp.part_code).toLowerCase().trim());
                }
                if (!foundPart && dp.part_name) {
                    foundPart = this.parts.find(p => p.name && p.name.toLowerCase() === String(dp.part_name).toLowerCase().trim());
                }

                newItems.push({
                    part_id: foundPart ? foundPart.id : (dp.part_id || ''),
                    part_name: dp.part_name || (foundPart ? foundPart.name : ''),
                    unit: foundPart ? (foundPart.unit || 'Cái') : 'Cái',
                    quantity: Number(dp.quantity) > 0 ? Number(dp.quantity) : 1,
                    is_free: true,
                    unit_price: 0,
                    note: '',
                    stock_available: foundPart ? foundPart.stock_qty : '-'
                });
            }

            this.form.items = newItems;
        },

        /* -------------------------------------------------------------
         * 2. SEARCHABLE TECHNICIANS (DIRECT INPUT)
         * ------------------------------------------------------------- */
        get filteredTechnicians() {
            if (!this.techSearchInput || !this.techSearchInput.trim()) return this.technicians;
            const q = this.techSearchInput.toLowerCase().trim();
            return this.technicians.filter(t => 
                (t.name && t.name.toLowerCase().includes(q)) ||
                (t.department && t.department.toLowerCase().includes(q)) ||
                (t.phone && t.phone.toLowerCase().includes(q))
            );
        },

        onInputTechSearch() {
            const found = this.technicians.find(t => t.name.toLowerCase() === this.techSearchInput.toLowerCase().trim());
            this.form.technician_id = found ? found.id : '';
        },

        selectTechnician(tech) {
            if (tech) {
                this.form.technician_id = tech.id;
                this.techSearchInput = tech.name + ' (' + (tech.department || 'KTV') + ')';
            } else {
                this.form.technician_id = '';
                this.techSearchInput = '';
            }
            this.techDropdownOpen = false;
        },

        clearTechnician() {
            this.form.technician_id = '';
            this.techSearchInput = '';
            this.techDropdownOpen = false;
        },

        /* -------------------------------------------------------------
         * 3. ORDER STATUS BADGES
         * ------------------------------------------------------------- */
        getStatusLabel(stKey) {
            return this.statuses[stKey] ? this.statuses[stKey].label : stKey;
        },

        getStatusBadgeClass(stKey) {
            return this.statuses[stKey] ? this.statuses[stKey].badge : 'bg-slate-100 text-slate-800';
        },

        /* -------------------------------------------------------------
         * 4. SEARCHABLE PARTS IN ITEMS TABLE (DIRECT INPUT)
         * ------------------------------------------------------------- */
        filterPartsBy(keyword, currentIdx = null) {
            // Danh sách ID và Tên phụ tùng đã có trong bảng ở các dòng khác (loại bỏ không hiển thị lại)
            const selectedPartIds = new Set();
            const selectedPartNames = new Set();

            if (this.form.items && Array.isArray(this.form.items)) {
                this.form.items.forEach((it, i) => {
                    if (currentIdx !== null && i === currentIdx) {
                        return; // Cho phép xem lại phụ tùng của chính dòng hiện tại
                    }
                    if (it.part_id) {
                        selectedPartIds.add(Number(it.part_id));
                    }
                    if (it.part_name && it.part_name.trim()) {
                        selectedPartNames.add(it.part_name.toLowerCase().trim());
                    }
                });
            }

            // Loại trừ các phụ tùng đã được chọn
            const availableParts = (this.parts || []).filter(p => {
                if (selectedPartIds.has(Number(p.id))) return false;
                if (p.name && selectedPartNames.has(p.name.toLowerCase().trim())) return false;
                return true;
            });

            if (!keyword || !keyword.trim()) return availableParts;
            const q = keyword.toLowerCase().trim();
            return availableParts.filter(p => 
                (p.name && p.name.toLowerCase().includes(q)) ||
                (p.code && p.code.toLowerCase().includes(q)) ||
                (p.category && p.category.toLowerCase().includes(q))
            );
        },

        onTypePartName(idx, text) {
            this.form.items[idx].part_name = text;
            // Check if matches an existing part
            const found = this.parts.find(p => p.name.toLowerCase() === text.toLowerCase().trim());
            if (found) {
                this.form.items[idx].part_id = found.id;
                this.form.items[idx].unit = found.unit || 'Cái';
                // Phụ tùng theo máy: mặc định không cộng thêm tiền, giữ nguyên giá máy
                this.form.items[idx].is_free = true;
                this.form.items[idx].unit_price = 0;
                this.form.items[idx].stock_available = found.stock_qty;
            } else {
                this.form.items[idx].part_id = '';
            }
        },

        onSelectPart(idx, partId) {
            const found = this.parts.find(p => p.id == partId);
            if (found) {
                this.form.items[idx].part_id = found.id;
                this.form.items[idx].part_name = found.name;
                this.form.items[idx].unit = found.unit || 'Cái';
                // Phụ tùng theo máy: mặc định không cộng thêm tiền, giữ nguyên giá máy
                this.form.items[idx].is_free = true;
                this.form.items[idx].unit_price = 0;
                this.form.items[idx].stock_available = found.stock_qty;
            } else {
                this.form.items[idx].stock_available = '-';
            }
        },

        toggleItemFree(idx) {
            const it = this.form.items[idx];
            it.is_free = !it.is_free;
            if (it.is_free) {
                it.unit_price = 0;
            } else {
                if (it.part_id) {
                    const p = this.parts.find(x => x.id == it.part_id);
                    it.unit_price = p ? Number(p.out_price || 0) : 0;
                }
            }
        },

        onUnitPriceChange(idx) {
            const it = this.form.items[idx];
            if (Number(it.unit_price) > 0) {
                it.is_free = false;
            } else {
                it.is_free = true;
                it.unit_price = 0;
            }
        },

        clearPartRow(idx) {
            this.form.items[idx].part_id = '';
            this.form.items[idx].part_name = '';
            this.form.items[idx].unit_price = 0;
            this.form.items[idx].is_free = true;
            this.form.items[idx].stock_available = '-';
        },

        addItemRow() {
            this.form.items.push({
                part_id: '',
                part_name: '',
                unit: 'Cái',
                quantity: 1,
                is_free: true,
                unit_price: 0,
                note: '',
                stock_available: '-'
            });
        },

        removeItemRow(idx) {
            this.form.items.splice(idx, 1);
        },

        calcPartsFee() {
            return this.form.items.reduce((sum, it) => {
                if (it.is_free) return sum;
                return sum + ((Number(it.quantity) || 0) * (Number(it.unit_price) || 0));
            }, 0);
        },

        calcTotalAmount() {
            const machine = Number(this.form.machine_price) || 0;
            const parts = this.calcPartsFee();
            const discount = Number(this.form.discount_amount) || 0;
            return Math.max(0, machine + parts - discount);
        },

        /* -------------------------------------------------------------
         * QUICK ADD MACHINE MODEL MODAL
         * ------------------------------------------------------------- */
        openModelModal() {
            this.newModelForm = {
                code: '',
                brand: 'Karofi',
                name: '',
                filter_stages: 10,
                type: 'Tủ đứng',
                standard_price: 5000000,
                description: ''
            };
            this.showModelModal = true;
        },

        async saveNewModelSubmit() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/water-filter/machine-models', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.newModelForm)
                });
                let data = {};
                try { data = await res.json(); } catch (err) {}

                if (!res.ok || !data.success) {
                    let errMsg = data.message || 'Lỗi thêm model máy';
                    if (data.errors) {
                        errMsg += '\n• ' + Object.values(data.errors).flat().join('\n• ');
                    }
                    alert(errMsg);
                    return;
                }

                this.machineModels.push(data.model);
                this.selectModel(data.model);
                this.showModelModal = false;
                alert('Đã thêm và chọn model máy lọc: ' + data.model.name);
            } catch (e) {
                alert('Có lỗi khi thêm model máy: ' + (e.message || 'Lỗi kết nối'));
            }
        },

        async submitForm() {
            if (this.submitting) return;

            const toStr = (v) => {
                if (typeof v === 'string') return v.trim();
                if (v && typeof v === 'object' && v.name) return String(v.name).trim();
                return String(v ?? '').trim();
            };

            const customerName = toStr(this.form.customer_name);
            const customerPhone = toStr(this.form.customer_phone);
            const customerAddress = toStr(this.form.customer_address);

            if (!customerName || !customerPhone || !customerAddress) {
                alert('Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ lắp đặt!');
                return;
            }

            const modelName = toStr(this.form.machine_model) || toStr(this.modelSearchInput);
            if (!modelName && !this.form.machine_model_id) {
                alert('Vui lòng chọn hoặc nhập Model máy lọc nước cần lắp!');
                return;
            }

            this.submitting = true;

            const payload = {
                id: this.form.id || null,
                dealer_id: this.form.dealer_id ? Number(this.form.dealer_id) : null,
                customer_name: customerName,
                customer_phone: customerPhone,
                customer_address: customerAddress,
                customer_notes: this.form.customer_notes || '',
                machine_model_id: this.form.machine_model_id ? Number(this.form.machine_model_id) : null,
                machine_model: modelName,
                machine_price: Number(this.form.machine_price || 0),
                surcharge_amount: Number(this.form.surcharge_amount || 0),
                discount_amount: Number(this.form.discount_amount || 0),
                machine_serial: toStr(this.form.machine_serial),
                technician_id: this.form.technician_id ? Number(this.form.technician_id) : null,
                status: this.form.status || 'pending',
                appointment_date: this.form.appointment_date || null,
                payment_status: this.form.payment_status || 'unpaid',
                installed_at: this.form.installed_at || null,
                warranty_months: Number(this.form.warranty_months || 24),
                warranty_until: this.form.warranty_until || null,
                warranty_card_code: toStr(this.form.warranty_card_code),
                filter_replacement_months: Number(this.form.filter_replacement_months || 6),
                warranty_notes: toStr(this.form.warranty_notes),
                completion_notes: toStr(this.form.completion_notes),
                items: (this.form.items || [])
                    .filter(it => it.part_name && toStr(it.part_name))
                    .map(it => ({
                        part_id: it.part_id ? Number(it.part_id) : null,
                        part_name: toStr(it.part_name),
                        unit: it.unit || 'Cái',
                        quantity: Number(it.quantity || 1),
                        is_free: Boolean(it.is_free),
                        unit_price: it.is_free ? 0 : Number(it.unit_price || 0),
                        note: it.note || ''
                    })),
                return_url: this.returnUrl || null
            };

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/water-filter/installations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                let data = {};
                try {
                    data = await res.json();
                } catch (err) {}

                if (!res.ok || !data.success) {
                    let errMsg = data.message || ('Lỗi lưu đơn hàng (Mã ' + res.status + ')');
                    if (data.errors) {
                        const errList = Object.values(data.errors).flat().join('\n• ');
                        errMsg += '\n• ' + errList;
                    }
                    alert(errMsg);
                    this.submitting = false;
                    return;
                }

                alert(data.message || 'Lưu đơn lắp đặt máy lọc nước thành công!');
                window.location.href = this.returnUrl || data.redirect || '/water-filter?tab=installations';
            } catch (e) {
                alert('Có lỗi kết nối máy chủ: ' + (e.message || 'Vui lòng kiểm tra đường truyền'));
                this.submitting = false;
            }
        }
    };
}
</script>
@endsection
