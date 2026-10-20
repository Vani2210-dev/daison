@extends('layouts.app')

@section('title', 'Xác Nhận Giao Đơn #' . ($order->order_code ?? '') . ' | Vận Tải ERP Đại Sơn')

@section('content')
@php
    $origTotal = (float)($order->total_amount ?? 0);
    $prevDebt = (float)($previousDebt ?? 0);
    $cLimit = (float)($creditLimit ?? 0);
    $custCode = $dealer?->code ?: ($order->dealer_id ? 'KH-' . $order->dealer_id : 'KH-LE');
    $custName = $dealer?->name ?: ($order->customer_name ?: 'Khách hàng');
    $canApprove = !empty($canApproveCredit);
    $initStatus = $order->credit_overrun_status ?? '';
    $initApprover = $order->credit_overrun_approved_name ?? '';
    $initApprovedAt = $order->credit_overrun_approved_at ? \Carbon\Carbon::parse($order->credit_overrun_approved_at)->format('H:i d/m/Y') : '';
    $initReason = $order->credit_overrun_reason ?? '';
@endphp

<div x-data="deliveryConfirmPage()" x-init="initData()" class="space-y-5 max-w-[1600px] mx-auto pb-24 font-sans text-slate-800 antialiased" x-cloak>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3"
         class="fixed bottom-6 right-6 z-[9999] px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-bold border backdrop-blur-md"
         :class="toast.type === 'success' ? 'bg-emerald-600/95 text-white border-emerald-400' : 'bg-rose-600/95 text-white border-rose-400'">
        <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check text-lg' : 'fa-solid fa-triangle-exclamation text-lg'"></i>
        <span x-text="toast.message"></span>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. HEADER BANNER TRANG XÁC NHẬN GIAO ĐƠN                                    -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ url('/delivery-execution') }}?vehicle={{ urlencode($vehiclePlate ?? '') }}"
               class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-purple-50 text-slate-700 hover:text-purple-700 border border-slate-200 hover:border-purple-300 font-bold text-xs sm:text-sm cursor-pointer transition-all shadow-xs inline-flex items-center gap-2 group">
                <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1 text-purple-600"></i>
                <span>Quay lại danh sách đơn</span>
            </a>

            <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 font-semibold mb-0.5">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-800 font-mono font-bold">
                        <i class="fa-solid fa-truck text-purple-600"></i>
                        <span>{{ $vehiclePlate ?? 'Xe giao hàng' }}</span>
                    </span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-purple-50 text-purple-700 font-mono font-bold">
                        <i class="fa-regular fa-id-badge"></i>
                        <span>Mã KH: {{ $custCode }}</span>
                    </span>
                    <span>•</span>
                    <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold"
                          :class="orderStatusBadgeClass()">
                        {{ $order->status }}
                    </span>
                </div>
                <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Xác nhận giao đơn hàng</span>
                    <span class="font-mono text-purple-700">#{{ $order->order_code }}</span>
                </h1>
            </div>
        </div>

        <!-- Quick Actions: In phiếu, Gọi khách, Chỉ đường, Tồn kho ôm -->
        <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
            <button type="button" @click="scrollToHoldingStock()"
                    class="px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-[#1b4a8c] font-bold text-xs flex items-center gap-1.5 cursor-pointer transition-colors shadow-2xs"
                    title="Xem tồn kho hàng ôm của nhân viên/xe">
                <i class="fa-solid fa-boxes-stacked text-[#1b4a8c]"></i>
                <span>Tồn kho hàng ôm (<strong class="font-mono" x-text="getSelectedHoldingWh()?.total_qty || 0"></strong> SP)</span>
            </button>

            <button type="button" @click="printDeliverySlip()"
                    class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-bold text-xs flex items-center gap-1.5 cursor-pointer shadow-2xs transition-colors">
                <i class="fa-solid fa-print text-sky-600"></i>
                <span>In phiếu giao</span>
            </button>

            @if($order->customer_phone)
                <a href="tel:{{ $order->customer_phone }}"
                   class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 cursor-pointer shadow-sm transition-colors">
                    <i class="fa-solid fa-phone"></i>
                    <span>Gọi khách: <strong class="font-mono">{{ $order->customer_phone }}</strong></span>
                </a>
            @endif

            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->customer_address ?: $custName) }}"
               target="_blank"
               class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs flex items-center gap-1.5 cursor-pointer transition-colors">
                <i class="fa-solid fa-location-dot text-rose-500"></i>
                <span>Chỉ đường Maps ↗</span>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. LAYOUT 2 CỘT CHÍNH (Trái: Kiểm hàng & Ký nhận | Phải: Thanh toán & Nợ) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <!-- CỘT TRÁI (7 CỘT): MỤC 1 KIỂM HÀNG & MỤC 2 BIÊN BẢN KÝ NHẬN -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- 1. KIỂM HÀNG & NHẬP SỐ LƯỢNG TRẢ VỀ (NẾU CÓ) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs space-y-3.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-xs">
                            <i class="fa-solid fa-rotate-left"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 text-sm">1. Kiểm Hàng & Nhập Số Lượng Trả Về (Nếu có)</h3>
                            <p class="text-[11px] text-slate-500">Kiểm đếm hàng thực nhận tại điểm giao và ghi nhận hàng hoàn lại</p>
                        </div>
                    </div>

                    <!-- Toggle Checkbox: Khách có trả lại hàng -->
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border cursor-pointer select-none transition-all shadow-2xs"
                           :class="confirmForm.hasReturns ? 'bg-rose-50 text-rose-800 border-rose-300 font-extrabold ring-1 ring-rose-400' : 'bg-slate-50 text-slate-700 border-slate-200 font-semibold'">
                        <input type="checkbox" x-model="confirmForm.hasReturns" @change="onHasReturnsToggle()" class="w-4 h-4 rounded text-rose-600 cursor-pointer">
                        <span>Khách có trả lại hàng</span>
                    </label>
                </div>

                <!-- Banner khi không trả lại: Giao đúng và đủ 100% -->
                <div x-show="!confirmForm.hasReturns"
                     class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>Giao đúng và đủ 100% toàn bộ hàng trên đơn, không có hàng trả lại.</span>
                </div>

                <!-- Bảng danh sách mặt hàng giao -->
                <div class="space-y-2 pt-1">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                        <span>Danh sách mặt hàng giao (<span x-text="confirmForm.items.length"></span> mục)</span>
                        <span>Số lượng xuất kho</span>
                    </div>

                    <div class="space-y-2 max-h-[350px] overflow-y-auto pr-1">
                        <template x-for="(it, iIdx) in confirmForm.items" :key="iIdx">
                            <div class="p-3 rounded-xl border transition-all"
                                 :class="Number(it.returnedQty) > 0 ? 'bg-rose-50/60 border-rose-200' : 'bg-slate-50/80 border-slate-200/80'">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono text-[10px] text-slate-500 font-bold bg-white px-1.5 py-0.5 rounded border border-slate-200" x-text="it.sku"></span>
                                            <span class="font-bold text-xs text-slate-900 leading-snug" x-text="it.name"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2">
                                            <span>Đơn giá: <strong class="text-slate-800 font-mono" x-text="formatNumber(it.unit_price) + ' đ'"></strong> / <span x-text="it.unit"></span></span>
                                            <span>•</span>
                                            <span>Thành tiền: <strong class="text-slate-800 font-mono" x-text="formatNumber(it.amount) + ' đ'"></strong></span>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="inline-block px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-mono font-black text-xs text-slate-800 shadow-2xs">
                                            <span x-text="formatNumber(it.quantity)"></span> <span class="text-[10px] text-slate-500 font-medium" x-text="it.unit"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Khu vực nhập số lượng trả hàng nếu bật toggle -->
                                <div x-show="confirmForm.hasReturns" x-cloak class="mt-2.5 pt-2.5 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-rose-700 text-xs">Khách trả lại:</span>
                                        <div class="relative inline-flex items-center">
                                            <input type="number" min="0" :max="it.quantity" step="any"
                                                   x-model.number="it.returnedQty"
                                                   @input="validateReturnQty(it)"
                                                   @change="validateReturnQty(it)"
                                                   @blur="validateReturnQty(it)"
                                                   class="w-20 px-2 py-1 bg-white border rounded-lg text-xs font-mono font-bold text-center focus:outline-none focus:ring-1"
                                                   :class="Number(it.returnedQty) > Number(it.quantity) ? 'border-rose-500 text-rose-600 bg-rose-50 ring-1 ring-rose-500' : 'border-rose-300 text-rose-700 focus:ring-rose-500'">
                                        </div>
                                        <span class="text-[11px] text-slate-500 font-medium" x-text="it.unit"></span>
                                        <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap">
                                            (Tối đa: <button type="button" @click="it.returnedQty = it.quantity; validateReturnQty(it)" class="font-mono font-bold text-rose-600 hover:underline cursor-pointer" title="Bấm để trả toàn bộ" x-text="formatNumber(it.quantity)"></button>)
                                        </span>
                                    </div>

                                    <div class="flex-1 max-w-xs">
                                        <input type="text" placeholder="Lý do trả lại (bể vỡ, thừa, đổi...)"
                                               x-model="it.returnReason"
                                               class="w-full px-2 py-1 bg-white border border-slate-300 rounded-lg text-[11px] text-slate-700 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Tổng kết hàng trả lại nếu có -->
                    <div x-show="confirmForm.hasReturns && returnTotalAmount > 0" x-cloak
                         class="p-3 bg-rose-50 rounded-xl border border-rose-200 flex items-center justify-between text-xs font-bold text-rose-800">
                        <span>Tổng tiền hàng khách trả lại:</span>
                        <span class="font-mono text-sm text-rose-600 font-black" x-text="'- ' + formatNumber(returnTotalAmount) + ' đ'"></span>
                    </div>
                </div>
            </div>

            <!-- 2. BIÊN BẢN BÀN GIAO & KÝ NHẬN -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs space-y-3.5">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm">2. Biên Bản Bàn Giao & Người Nhận</h3>
                        <p class="text-[11px] text-slate-500">Thông tin người đại diện đại lý ký nhận vật tư tại điểm giao</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Họ tên người nhận hàng (*):</label>
                        <input type="text" x-model="confirmForm.recipientName"
                               placeholder="VD: Anh Nam (Quản lý kho / Chủ đại lý)"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ghi chú giao nhận phát sinh:</label>
                        <input type="text" x-model="confirmForm.deliveryNotes"
                               placeholder="VD: Khách nhờ giao sau 17h, để gọn sân..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-700 focus:bg-white focus:outline-none focus:border-purple-500">
                    </div>
                </div>
            </div>

        </div>

        <!-- CỘT PHẢI (5 CỘT): MỤC 3 HÌNH THỨC THANH TOÁN, TRẠNG THÁI & CÔNG NỢ -->
        <div class="lg:col-span-5 space-y-4">
            
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4 sticky top-6">
                
                <!-- TIÊU ĐỀ SECTION 3 -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 text-sm sm:text-base">3. Thanh Toán & Công Nợ</h3>
                            <p class="text-[11px] text-slate-500">Xác lập trạng thái thanh toán & hạch toán công nợ</p>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 font-bold block uppercase">Cần thu thực tế:</span>
                        <span class="font-mono text-base font-black text-purple-700" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></span>
                    </div>
                </div>

                <!-- 3 NÚT CHỌN NHANH HÌNH THỨC (Tabs) -->
                <div class="grid grid-cols-3 gap-2">
                    <!-- Nút 1: Tiền mặt -->
                    <button type="button" @click="selectPaymentMode('Tiền mặt')"
                            class="py-2.5 px-2 rounded-2xl border flex flex-col items-center justify-center gap-1 transition-all cursor-pointer shadow-2xs"
                            :class="confirmForm.paymentMethod === 'Tiền mặt' ? 'bg-emerald-600 text-white border-emerald-600 font-bold shadow-md shadow-emerald-600/25 ring-2 ring-emerald-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold'">
                        <i class="fa-solid fa-money-bill-wave text-base"></i>
                        <span class="text-xs">Tiền mặt</span>
                    </button>

                    <!-- Nút 2: Quét VietQR -->
                    <button type="button" @click="selectPaymentMode('VietQR')"
                            class="py-2.5 px-2 rounded-2xl border flex flex-col items-center justify-center gap-1 transition-all cursor-pointer shadow-2xs"
                            :class="(confirmForm.paymentMethod === 'Chuyển khoản VietQR' || confirmForm.paymentMethod === 'VietQR') ? 'bg-sky-600 text-white border-sky-600 font-bold shadow-md shadow-sky-600/25 ring-2 ring-sky-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold'">
                        <i class="fa-solid fa-qrcode text-base"></i>
                        <span class="text-xs">Quét VietQR</span>
                    </button>

                    <!-- Nút 3: Ghi nợ -->
                    <button type="button" @click="selectPaymentMode('Ghi nợ')"
                            class="py-2.5 px-2 rounded-2xl border flex flex-col items-center justify-center gap-1 transition-all cursor-pointer shadow-2xs"
                            :class="confirmForm.isDebt || confirmForm.paymentMethod === 'Ghi nợ' ? 'bg-purple-800 text-white border-purple-800 font-bold shadow-md shadow-purple-800/25 ring-2 ring-purple-600/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 font-semibold'">
                        <i class="fa-solid fa-file-signature text-base"></i>
                        <span class="text-xs">Ghi nợ</span>
                    </button>
                </div>

                <!-- ======================================================== -->
                <!-- CẶP TRƯỜNG THÔNG TIN: HÌNH THỨC & TRẠNG THÁI THANH TOÁN   -->
                <!-- ======================================================== -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 bg-slate-50/90 rounded-2xl border border-slate-200">
                    <!-- Hình thức thanh toán -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Hình thức thanh toán:</label>
                        <select x-model="confirmForm.paymentMethod" @change="onPaymentMethodSelectChange()"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-purple-500 cursor-pointer shadow-2xs">
                            <option value="Ghi nợ">Ghi nợ (Ký sổ đại lý)</option>
                            <option value="Tiền mặt">Tiền mặt</option>
                            <option value="Chuyển khoản VietQR">Chuyển khoản VietQR</option>
                        </select>
                    </div>

                    <!-- Trạng thái thanh toán -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Trạng thái thanh toán:</label>
                        <select x-model="confirmForm.paymentStatus"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-purple-500 cursor-pointer shadow-2xs">
                            <option value="Ghi nợ">Ghi nợ</option>
                            <option value="Chưa thanh toán">Chưa thanh toán</option>
                            <option value="Đã thanh toán">Đã thanh toán</option>
                            <option value="Thanh toán một phần">Thanh toán một phần</option>
                        </select>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- TRƯỜNG HỢP 1: 💵 TIỀN MẶT                                -->
                <!-- ======================================================== -->
                <template x-if="confirmForm.paymentMethod === 'Tiền mặt' && !confirmForm.isDebt">
                    <div class="space-y-3 p-4 bg-emerald-50/60 rounded-2xl border border-emerald-200 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-900">Số tiền lái xe thực tế đã thu:</span>
                            <span class="text-[10px] text-emerald-700 font-medium">Bằng tiền mặt tại điểm giao</span>
                        </div>

                        <div class="relative">
                            <input type="text" inputmode="numeric"
                                   x-money="confirmForm.collectedAmount"
                                   placeholder="0"
                                   class="w-full pl-3 pr-10 py-2.5 bg-white border border-emerald-300 rounded-xl text-base font-mono font-black text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <span class="absolute right-3 top-3 text-xs font-bold text-slate-400">VNĐ</span>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-600 pt-1 border-t border-emerald-200/60">
                            <span>Tổng đơn sau trừ trả hàng:</span>
                            <strong class="font-mono text-slate-900 font-bold" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                        </div>
                    </div>
                </template>

                <!-- ======================================================== -->
                <!-- TRƯỜNG HỢP 2: 📱 QUÉT MÃ VIETQR                          -->
                <!-- ======================================================== -->
                <template x-if="confirmForm.paymentMethod === 'Chuyển khoản VietQR' || confirmForm.paymentMethod === 'VietQR'">
                    <div class="space-y-3.5 p-4 bg-sky-50/70 rounded-2xl border border-sky-200 text-xs">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 font-bold text-sky-900">
                                <i class="fa-solid fa-qrcode text-sky-600"></i>
                                <span>Mã QR Thanh Toán Tự Động</span>
                            </div>
                            <span class="text-[10px] text-sky-700 font-mono font-bold bg-white px-2 py-0.5 rounded border border-sky-200">VietQR Napas247</span>
                        </div>

                        <!-- Khung hình ảnh QR Code VietQR động -->
                        <div class="bg-white p-3.5 rounded-2xl border border-sky-200 shadow-xs flex flex-col items-center justify-center space-y-2">
                            <img :src="vietQrUrl" alt="VietQR Thanh Toán"
                                 class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-xl border border-slate-100 shadow-inner">
                            <div class="text-center space-y-0.5">
                                <span class="text-[11px] text-slate-500 block">Số tiền thanh toán:</span>
                                <span class="font-mono text-base font-black text-emerald-600" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></span>
                            </div>
                        </div>

                        <!-- Thông tin số tài khoản và nội dung CK -->
                        <div class="space-y-2 pt-1">
                            <div class="flex items-center justify-between py-1 bg-white p-2.5 rounded-xl border border-slate-200">
                                <div>
                                    <span class="text-[10px] text-slate-500 font-bold block uppercase">Ngân hàng MB Bank:</span>
                                    <span class="font-mono font-black text-slate-900 text-xs sm:text-sm">0903888999</span>
                                    <span class="text-[10px] text-slate-500 block">CONG TY TNHH MANH QUAN</span>
                                </div>
                                <button type="button" @click="copyToClipboard('0903888999', 'stk')"
                                        class="px-2.5 py-1.5 rounded-lg bg-sky-100 hover:bg-sky-200 text-sky-800 font-bold text-xs cursor-pointer transition-colors flex items-center gap-1">
                                    <i class="fa-regular fa-copy"></i>
                                    <span x-text="copiedField === 'stk' ? 'Đã chép' : 'Chép STK'"></span>
                                </button>
                            </div>

                            <div class="flex items-center justify-between py-1 bg-emerald-50/80 p-2.5 rounded-xl border border-emerald-200">
                                <div>
                                    <span class="text-[10px] text-emerald-800 font-bold block uppercase">Nội dung CK (Mã KH + Mã Đơn):</span>
                                    <span class="font-mono font-black text-slate-900 text-xs sm:text-sm mt-0.5 block" x-text="qrTransferMemo"></span>
                                </div>
                                <button type="button" @click="copyToClipboard(qrTransferMemo, 'memo')"
                                        class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs cursor-pointer shadow-2xs transition-colors flex items-center gap-1">
                                    <i class="fa-regular fa-copy"></i>
                                    <span x-text="copiedField === 'memo' ? 'Đã chép' : 'Sao chép'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ======================================================== -->
                <!-- TRƯỜNG HỢP 3: 📝 GHI NỢ (KÝ NỢ ĐẠI LÝ)                     -->
                <!-- ======================================================== -->
                <template x-if="confirmForm.isDebt || confirmForm.paymentMethod === 'Ghi nợ'">
                    <div class="space-y-4">
                        <!-- Bảng Chi Tiết Biến Động Công Nợ Đại Lý -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5 text-xs">
                            <div class="font-black text-slate-800 pb-2 border-b border-slate-200 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-chart-line text-purple-600"></i>
                                    <span>Dự Tính Biến Động Sổ Nợ Đại Lý</span>
                                </span>
                                <span class="text-[10px] font-mono text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md">{{ $custCode }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">• Nợ cũ trước đơn này:</span>
                                <strong class="font-mono font-bold text-slate-900">{{ number_format($prevDebt, 0, ',', '.') }} đ</strong>
                            </div>
                            <div class="flex items-center justify-between text-purple-700 font-semibold">
                                <span>• Phát sinh ghi nợ đơn này:</span>
                                <strong class="font-mono font-black" x-text="'+ ' + formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-slate-900 font-bold">
                                <span>→ Tổng nợ mới sau khi giao:</span>
                                <strong class="font-mono font-black text-purple-800 text-sm"
                                        :class="isOverLimit ? 'text-rose-700' : 'text-purple-800'"
                                        x-text="formatNumber({{ $prevDebt }} + Number(confirmForm.finalAmount || 0)) + ' đ'"></strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-500 text-[11px] pt-1">
                                <span>Hạn mức công nợ cho phép:</span>
                                <span class="font-mono font-semibold text-slate-700">{{ number_format($cLimit, 0, ',', '.') }} đ</span>
                            </div>
                        </div>

                        <!-- Khối tùy chọn Bàn giao công nợ cho Sale (Quy trình 2 bước) -->
                        <div class="p-3.5 bg-purple-50/70 border border-purple-200 rounded-2xl space-y-2 text-xs">
                            <label class="block font-bold text-purple-900 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-share-from-square text-purple-600"></i>
                                    <span>Bàn Giao Nợ Cho Sale (Không Tính Nợ Lái Xe):</span>
                                </span>
                                <span class="text-[10px] text-purple-600 font-semibold">Quy trình 2 bước</span>
                            </label>
                            <select x-model="confirmForm.handoverToUserId" class="w-full px-3 py-2 bg-white border border-purple-300 rounded-xl text-xs font-semibold text-slate-800 focus:ring-1 focus:ring-purple-500 shadow-2xs">
                                <option value="">-- Ký nợ đại lý trực tiếp (Không bàn giao Sale) --</option>
                                @foreach($salesStaff ?? [] as $st)
                                    <option value="{{ $st->id }}">
                                        {{ $st->name }} ({{ $st->staff_type === 'dealer_sales' ? 'Sale Đại Lý Phụ Thuộc' : 'Sale Công Ty' }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10.5px] text-purple-700 leading-tight">
                                💡 <em>Khi chọn Sale, hệ thống sẽ gửi yêu cầu bàn giao nợ đến Sale. Sau khi Sale bấm 'Xác nhận nhận nợ', công nợ được chuyển chính thức và lái xe được giải phóng hoàn toàn trách nhiệm tiền hàng.</em>
                            </p>
                        </div>

                        @if($cLimit > 0)
                        <!-- ============================================================ -->
                        <!-- PHÁT HIỆN VƯỢT HẠN MỨC: Hiển thị 2 phương án kết hợp      -->
                        <!-- ============================================================ -->
                        <div x-show="isOverLimit" x-cloak class="space-y-3">
                            <!-- Banner cảnh báo đỏ -->
                            <div class="p-4 bg-gradient-to-br from-rose-900 via-red-900 to-orange-900 rounded-2xl text-white space-y-2 shadow-md">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-rose-500/30 border border-rose-400/40 flex items-center justify-center text-rose-200 font-bold text-base shrink-0">
                                        <i class="fa-solid fa-shield-exclamation"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-rose-300 font-bold tracking-wider uppercase block">Cảnh báo hạn mức tín dụng</span>
                                        <h4 class="font-black text-sm text-white">VƯỢT HẠN MỨC CÔNG NỢ!</h4>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-rose-800/60 text-[11px]">
                                    <div class="text-center">
                                        <div class="text-rose-300">Nợ cũ</div>
                                        <div class="font-mono font-black text-white">{{ number_format($prevDebt, 0, ',', '.') }} đ</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-rose-300">Đơn này</div>
                                        <div class="font-mono font-black text-amber-300" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-rose-300">Vượt</div>
                                        <div class="font-mono font-black text-rose-300" x-text="'+' + formatNumber(excessAmount) + ' đ'"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2 Tab phương án -->
                            <div class="rounded-2xl border border-orange-200 overflow-hidden shadow-xs">
                                <!-- Tabs header -->
                                <div class="flex border-b border-orange-200">
                                    <button type="button" @click="overrunMode = 'split'"
                                            class="flex-1 py-2.5 px-3 text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                                            :class="overrunMode === 'split' ? 'bg-orange-600 text-white' : 'bg-orange-50 text-orange-800 hover:bg-orange-100'">
                                        <i class="fa-solid fa-scissors"></i>
                                        <span>Phương án 2: Tách thanh toán</span>
                                    </button>
                                    <button type="button" @click="overrunMode = 'approval'"
                                            class="flex-1 py-2.5 px-3 text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                                            :class="overrunMode === 'approval' ? 'bg-purple-800 text-white' : 'bg-purple-50 text-purple-800 hover:bg-purple-100'">
                                        <i class="fa-solid fa-shield-check"></i>
                                        <span>Phương án 1.B: Duyệt bảo lãnh</span>
                                    </button>
                                </div>

                                <!-- PHƯƠNG ÁN 2: Tách thanh toán (Thu phần vượt ngay + Ký nợ phần trong hạn mức) -->
                                <div x-show="overrunMode === 'split'" class="p-4 bg-orange-50/60 space-y-3.5 text-xs">
                                    <div class="flex items-start gap-2 p-2.5 bg-white rounded-xl border border-orange-200">
                                        <i class="fa-solid fa-circle-info text-orange-500 mt-0.5 shrink-0"></i>
                                        <span class="text-orange-950 leading-relaxed">
                                            Thu tối thiểu phần vượt hạn mức (<strong class="font-mono text-orange-700" x-text="formatNumber(excessAmount) + ' đ'"></strong>). Lái xe có thể <strong>nhập số tiền thu nhiều hơn</strong> để trừ dần vào nợ cũ hoặc thanh toán toàn bộ đơn.
                                        </span>
                                    </div>

                                    <!-- Ô NHẬP SỐ TIỀN THU THỰC TẾ -->
                                    <div class="p-3 bg-white rounded-2xl border border-orange-200 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-[11px] font-black text-slate-800 uppercase tracking-tight">
                                                💵 Số tiền thu ngay:
                                            </label>
                                            <span class="text-[10px] text-slate-500">
                                                Tối thiểu: <strong class="font-mono font-bold text-orange-700" x-text="formatNumber(excessAmount) + ' đ'"></strong>
                                            </span>
                                        </div>

                                        <div class="relative">
                                            <input type="text" inputmode="numeric"
                                                   x-money="customSplitCollectAmount"
                                                   :placeholder="formatNumber(excessAmount)"
                                                   class="w-full pl-3 pr-14 py-2.5 bg-white border-2 rounded-xl text-base font-mono font-black text-slate-900 focus:outline-none transition-all shadow-inner"
                                                   :class="splitCollectAmountActual < excessAmount ? 'border-rose-400 focus:border-rose-600 bg-rose-50/40 text-rose-800' : (splitCollectAmountActual > excessAmount ? 'border-emerald-500 focus:border-emerald-600 ring-2 ring-emerald-500/15 text-emerald-800' : 'border-orange-300 focus:border-orange-500')">
                                            <span class="absolute right-3 top-3 text-xs font-bold text-slate-400 pointer-events-none">VNĐ</span>
                                        </div>

                                        <!-- Các nút bấm chọn nhanh -->
                                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                            <button type="button" @click="customSplitCollectAmount = excessAmount"
                                                    class="px-2.5 py-1 bg-orange-50 hover:bg-orange-100 border border-orange-300 rounded-lg text-[10px] font-bold text-orange-800 transition-colors cursor-pointer"
                                                    :class="splitCollectAmountActual === excessAmount ? 'ring-2 ring-orange-500 font-black' : ''">
                                                Tối thiểu: <span x-text="formatNumber(excessAmount)"></span> đ
                                            </button>
                                            <button type="button" @click="customSplitCollectAmount = confirmForm.finalAmount"
                                                    class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-lg text-[10px] font-bold text-emerald-800 transition-colors cursor-pointer"
                                                    :class="splitCollectAmountActual === confirmForm.finalAmount ? 'ring-2 ring-emerald-500 font-black' : ''">
                                                Thu hết đơn: <span x-text="formatNumber(confirmForm.finalAmount)"></span> đ
                                            </button>
                                            <button type="button" x-show="customSplitCollectAmount !== excessAmount"
                                                    @click="customSplitCollectAmount = excessAmount"
                                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg text-[10px] font-bold text-slate-600 transition-colors cursor-pointer">
                                                <i class="fa-solid fa-rotate-left mr-0.5"></i> Mặc định
                                            </button>
                                        </div>

                                        <!-- Hướng dẫn & phân tích mức tiền thu -->
                                        <div class="p-2 rounded-xl text-[11px] leading-relaxed transition-all"
                                             :class="splitCollectAmountActual < excessAmount ? 'bg-rose-50 text-rose-800 border border-rose-200' : (splitCollectAmountActual > confirmForm.finalAmount ? 'bg-teal-50 text-teal-900 border border-teal-200' : (splitCollectAmountActual > excessAmount ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-orange-50 text-orange-900 border border-orange-200'))">
                                            <template x-if="splitCollectAmountActual < excessAmount">
                                                <div class="flex items-center gap-1.5 font-bold">
                                                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                                                    <span>Chưa đủ mức tối thiểu! Cần thu thêm ít nhất <strong class="font-mono" x-text="formatNumber(excessAmount - splitCollectAmountActual) + ' đ'"></strong> để không vượt hạn mức.</span>
                                                </div>
                                            </template>
                                            <template x-if="splitCollectAmountActual === excessAmount">
                                                <div class="flex items-center gap-1.5 font-medium">
                                                    <i class="fa-solid fa-circle-check text-orange-600"></i>
                                                    <span>Thu vừa đủ phần vượt (<strong class="font-mono" x-text="formatNumber(excessAmount) + ' đ'"></strong>). Ghi nợ trong hạn mức <strong class="font-mono text-purple-700" x-text="formatNumber(allowedDebt) + ' đ'"></strong>.</span>
                                                </div>
                                            </template>
                                            <template x-if="splitCollectAmountActual > excessAmount && splitCollectAmountActual <= confirmForm.finalAmount">
                                                <div class="flex items-center gap-1.5 font-medium">
                                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                                    <span>Thu thêm <strong class="font-mono text-emerald-800" x-text="formatNumber(splitCollectAmountActual - excessAmount) + ' đ'"></strong> → Giảm nợ đơn này còn <strong class="font-mono text-purple-700" x-text="formatNumber(allowedDebt) + ' đ'"></strong> (dưới hạn mức).</span>
                                                </div>
                                            </template>
                                            <template x-if="splitCollectAmountActual > confirmForm.finalAmount">
                                                <div class="flex items-center gap-1.5 font-medium">
                                                    <i class="fa-solid fa-star text-teal-600"></i>
                                                    <span>Thu đủ toàn bộ đơn + cấn trừ <strong class="font-mono text-teal-800" x-text="formatNumber(surplusOverOrder) + ' đ'"></strong> trực tiếp vào nợ cũ của đại lý!</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Hình thức thu -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-orange-900 uppercase mb-1.5">
                                            Hình thức thu (<span class="font-mono" x-text="formatNumber(splitCollectAmountActual)"></span> đ):
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="splitCollectMethod = 'Tiền mặt'"
                                                    class="py-2.5 rounded-xl border flex items-center justify-center gap-1.5 text-xs font-bold transition-all cursor-pointer"
                                                    :class="splitCollectMethod === 'Tiền mặt' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/20' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'">
                                                <i class="fa-solid fa-money-bill-wave"></i>
                                                <span>Tiền mặt</span>
                                            </button>
                                            <button type="button" @click="splitCollectMethod = 'VietQR'"
                                                    class="py-2.5 rounded-xl border flex items-center justify-center gap-1.5 text-xs font-bold transition-all cursor-pointer"
                                                    :class="splitCollectMethod === 'VietQR' ? 'bg-sky-600 text-white border-sky-600 shadow-md shadow-sky-600/20' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'">
                                                <i class="fa-solid fa-qrcode"></i>
                                                <span>VietQR</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- QR nếu chọn VietQR -->
                                    <div x-show="splitCollectMethod === 'VietQR'" class="bg-white p-3.5 rounded-2xl border border-sky-200 flex flex-col items-center gap-2 shadow-2xs">
                                        <img :src="splitVietQrUrl" alt="VietQR" class="w-40 h-40 object-contain rounded-xl border border-slate-100 shadow-inner">
                                        <div class="text-center">
                                            <div class="text-[10px] text-slate-500 font-medium">Số tiền quét mã QR:</div>
                                            <div class="font-mono font-black text-sky-700 text-base" x-text="formatNumber(splitCollectAmountActual) + ' đ'"></div>
                                        </div>
                                        <div class="flex items-center gap-1 text-[10px] text-slate-500 font-mono bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">
                                            <span x-text="qrTransferMemo + ' THU'"></span>
                                        </div>
                                    </div>

                                    <!-- Tóm tắt phân bổ công nợ sau khi thu -->
                                    <div class="p-3.5 bg-white rounded-2xl border border-orange-200 space-y-1.5 shadow-2xs">
                                        <div class="flex justify-between font-bold text-emerald-800">
                                            <span>💵 Thu ngay (<span x-text="splitCollectMethod"></span>):</span>
                                            <strong class="font-mono" x-text="formatNumber(splitCollectAmountActual) + ' đ'"></strong>
                                        </div>
                                        <div class="flex justify-between font-semibold" :class="allowedDebt > 0 ? 'text-purple-700' : 'text-slate-400'">
                                            <span>📝 Ghi nợ đơn này:</span>
                                            <strong class="font-mono" x-text="formatNumber(allowedDebt) + ' đ'"></strong>
                                        </div>
                                        <template x-if="surplusOverOrder > 0">
                                            <div class="flex justify-between font-semibold text-teal-700">
                                                <span>📉 Cấn trừ vào nợ cũ đại lý:</span>
                                                <strong class="font-mono" x-text="'- ' + formatNumber(surplusOverOrder) + ' đ'"></strong>
                                            </div>
                                        </template>
                                        <div class="flex justify-between pt-1.5 border-t border-slate-100 font-black text-slate-900">
                                            <span>= Tổng đơn:</span>
                                            <strong class="font-mono" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                                        </div>
                                        <div class="pt-1.5 border-t border-dashed border-slate-200 flex justify-between items-center text-[11px]">
                                            <span class="text-slate-600 font-medium">Dự tính tổng nợ đại lý sau giao:</span>
                                            <strong class="font-mono font-bold"
                                                    :class="projectedTotalDebt <= creditLimit ? 'text-emerald-700' : 'text-rose-700'"
                                                    x-text="formatNumber(projectedTotalDebt) + ' đ'"></strong>
                                        </div>
                                        <div class="flex justify-between items-center text-[10px] text-slate-400">
                                            <span>Hạn mức cho phép:</span>
                                            <span class="font-mono font-semibold text-slate-600" x-text="formatNumber(creditLimit) + ' đ'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- PHƯƠNG ÁN 1.B: Duyệt bảo lãnh online -->
                                <div x-show="overrunMode === 'approval'" class="p-4 bg-purple-50/60 space-y-3 text-xs">
                                    <!-- Trạng thái hiện tại -->
                                    <!-- Chưa gửi yêu cầu -->
                                    <template x-if="overrunStatus === '' || overrunStatus === null">
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-2 p-2.5 bg-white rounded-xl border border-purple-200">
                                                <i class="fa-solid fa-circle-info text-purple-500 mt-0.5 shrink-0"></i>
                                                <span class="text-purple-900 leading-relaxed">
                                                    Ghi nợ toàn bộ <strong x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong> vào sổ đại lý, bao gồm phần vượt hạn mức <strong x-text="formatNumber(excessAmount) + ' đ'"></strong>. Cần Ban Giám Đốc / Kế Toán trưởng phê duyệt bảo lãnh.
                                                </span>
                                            </div>
                                            <button type="button" @click="sendOverrideRequest()" :disabled="isSendingRequest"
                                                    class="w-full py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold flex items-center justify-center gap-2 cursor-pointer transition-colors disabled:opacity-50">
                                                <i class="fa-solid fa-paper-plane"></i>
                                                <span x-text="isSendingRequest ? 'Đang gửi...' : 'Gửi Yêu Cầu Phê Duyệt Bảo Lãnh Nợ'"></span>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Đang chờ duyệt -->
                                    <template x-if="overrunStatus === 'Chờ duyệt'">
                                        <div class="space-y-3">
                                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-300 flex items-center gap-2.5">
                                                <i class="fa-solid fa-clock text-amber-600 text-base animate-pulse"></i>
                                                <div>
                                                    <div class="font-bold text-amber-900">Đang chờ phê duyệt...</div>
                                                    <div class="text-amber-700 text-[10px]">Hệ thống tự động kiểm tra mỗi 5 giây. Kế toán / Giám đốc đang xem xét.</div>
                                                </div>
                                            </div>
                                            <button type="button" @click="sendOverrideRequest()" :disabled="isSendingRequest"
                                                    class="w-full py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-[11px] flex items-center justify-center gap-2 cursor-pointer transition-colors disabled:opacity-50">
                                                <i class="fa-solid fa-rotate-right"></i>
                                                <span>Gửi lại yêu cầu</span>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Đã được duyệt -->
                                    <template x-if="overrunStatus === 'Đã duyệt'">
                                        <div class="p-4 bg-emerald-50 rounded-xl border-2 border-emerald-400 space-y-2">
                                            <div class="flex items-center gap-2 text-emerald-800 font-black">
                                                <i class="fa-solid fa-shield-check text-emerald-600 text-lg"></i>
                                                <span>ĐÃ ĐƯỢC PHÊ DUYỆT BẢO LÃNH!</span>
                                            </div>
                                            <div class="text-[11px] text-emerald-700">
                                                Phê duyệt bởi: <strong x-text="overrunApprover"></strong>
                                                <span x-show="overrunApprovedAt"> — <span x-text="overrunApprovedAt"></span></span>
                                            </div>
                                            <div class="text-[11px] text-emerald-800 font-semibold">Có thể ghi nợ toàn bộ <strong x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong> mà không cần thu tiền.</div>
                                        </div>
                                    </template>

                                    <!-- Bị từ chối -->
                                    <template x-if="overrunStatus === 'Từ chối'">
                                        <div class="p-3 bg-rose-50 rounded-xl border border-rose-300 space-y-2">
                                            <div class="flex items-center gap-2 text-rose-800 font-black">
                                                <i class="fa-solid fa-ban text-rose-600"></i>
                                                <span>Yêu cầu bị từ chối</span>
                                            </div>
                                            <div class="text-[11px] text-rose-700">Từ chối bởi: <strong x-text="overrunApprover"></strong></div>
                                            <div class="text-[11px] text-rose-700">Vui lòng chuyển sang Phương án 2 (Tách thanh toán) hoặc liên hệ lại quản lý.</div>
                                            <button type="button" @click="overrunMode = 'split'"
                                                    class="w-full py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-[11px] flex items-center justify-center gap-2 cursor-pointer transition-colors">
                                                <i class="fa-solid fa-scissors"></i>
                                                <span>Chuyển sang Phương án 2: Tách thanh toán</span>
                                            </button>
                                        </div>
                                    </template>

                                    @if($canApprove)
                                    <!-- KHỐI DUYỆT DÀNH CHO ADMIN/KẾ TOÁN (hiển thị ngay trên trang) -->
                                    <div x-show="overrunStatus === 'Chờ duyệt'" class="space-y-2 pt-2 border-t border-purple-200">
                                        <div class="text-[10px] font-bold text-purple-800 uppercase tracking-wide">⚡ Phê duyệt nhanh (Quyền Quản lý / Kế toán):</div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600 mb-1">Ghi chú khi duyệt (tùy chọn):</label>
                                            <input type="text" x-model="approvalNote" placeholder="Ví dụ: Đại lý uy tín, đồng ý bảo lãnh lần này..."
                                                   class="w-full px-3 py-2 bg-white border border-purple-300 rounded-xl text-xs focus:outline-none focus:border-purple-500">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="approveOverride('approve')" :disabled="isApprovingOverride"
                                                    class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition-colors disabled:opacity-50">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Phê Duyệt Bảo Lãnh</span>
                                            </button>
                                            <button type="button" @click="approveOverride('reject')" :disabled="isApprovingOverride"
                                                    class="py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition-colors disabled:opacity-50">
                                                <i class="fa-solid fa-ban"></i>
                                                <span>Từ Chối</span>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Trường hợp bình thường (không vượt hạn mức): Banner ghi nợ thông thường -->
                        <div x-show="!isOverLimit" class="p-4 sm:p-5 bg-gradient-to-br from-purple-900 via-indigo-900 to-slate-900 rounded-2xl text-white space-y-3 shadow-md">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/30 border border-purple-400/40 text-purple-200 flex items-center justify-center font-bold text-lg shrink-0">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] text-purple-300 font-bold tracking-wider uppercase block">Chế độ thanh toán</span>
                                    <h4 class="font-black text-sm text-white">KÝ NỢ TOÀN BỘ VÀO SỔ ĐẠI LÝ</h4>
                                </div>
                            </div>
                            <p class="text-xs text-purple-200/90 leading-relaxed">
                                Lái xe giao hàng và nhận biên bản ký nhận từ đại lý <strong class="text-white">{{ $custName }}</strong>.
                                Không thu tiền mặt tại điểm giao.
                            </p>
                            <div class="pt-2 border-t border-purple-800/80 flex items-center justify-between text-xs">
                                <span class="text-purple-300">Tiền ghi nợ đơn này:</span>
                                <strong class="font-mono text-base font-black text-amber-300" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                            </div>
                        </div>
                        @else
                        <!-- Không có credit limit: Banner ghi nợ thông thường -->
                        <div class="p-4 sm:p-5 bg-gradient-to-br from-purple-900 via-indigo-900 to-slate-900 rounded-2xl text-white space-y-3 shadow-md">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/30 border border-purple-400/40 text-purple-200 flex items-center justify-center font-bold text-lg shrink-0">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] text-purple-300 font-bold tracking-wider uppercase block">Chế độ thanh toán</span>
                                    <h4 class="font-black text-sm text-white">KÝ NỢ TOÀN BỘ VÀO SỔ ĐẠI LÝ</h4>
                                </div>
                            </div>
                            <p class="text-xs text-purple-200/90 leading-relaxed">
                                Lái xe giao hàng và nhận biên bản ký nhận từ đại lý <strong class="text-white">{{ $custName }}</strong>.
                            </p>
                            <div class="pt-2 border-t border-purple-800/80 flex items-center justify-between text-xs">
                                <span class="text-purple-300">Tiền ghi nợ đơn này:</span>
                                <strong class="font-mono text-base font-black text-amber-300" x-text="formatNumber(confirmForm.finalAmount) + ' đ'"></strong>
                            </div>
                        </div>
                        @endif
                    </div>
                </template>

                <!-- Quy Định Giao Hàng & Chứng Từ Alert Box -->
                <div class="p-3 bg-amber-50/80 rounded-2xl border border-amber-200 text-[11px] text-amber-900 flex items-start gap-2 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-amber-600 mt-0.5 shrink-0"></i>
                    <span>
                        <strong>Quy định chứng từ:</strong> Lái xe kiểm tra kỹ họ tên người nhận và biên lai giao hàng có chữ ký của đại lý trước khi bấm hoàn tất.
                    </span>
                </div>

                <!-- NÚT HOÀN TẤT & BÁO GIAO THẤT BẠI -->
                <div class="space-y-2.5 pt-2">
                    <!-- Nút submit tùy theo trạng thái -->
                    <!-- Bị block khi: GHI NỢ + VƯỢT HẠN MỨC + đang chờ duyệt (phương án 1.B) -->
                    <button type="button" @click="submitDeliveryCompletion()"
                            :disabled="isSubmittingDelivery || isSubmitBlocked"
                            :class="overrunStatus === 'Đã duyệt' && overrunMode === 'approval' && isOverLimit ? 
                                'w-full py-3.5 px-4 rounded-2xl font-black text-sm text-white bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 cursor-pointer hover:scale-[1.01] active:scale-98 disabled:opacity-50' :
                                (isSubmitBlocked ? 'w-full py-3.5 px-4 rounded-2xl font-black text-sm text-white bg-slate-400 cursor-not-allowed flex items-center justify-center gap-2 disabled:opacity-70' :
                                'w-full py-3.5 px-4 rounded-2xl font-black text-sm text-white bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 cursor-pointer hover:scale-[1.01] active:scale-98 disabled:opacity-50')">
                        <i class="fa-solid fa-circle-check text-base"></i>
                        <span x-text="submitBtnLabel"></span>
                    </button>

                    <button type="button" @click="showFailModal = true"
                            class="w-full py-2.5 px-3 rounded-xl font-bold text-xs text-rose-700 hover:bg-rose-50 border border-rose-200 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                        <span>Báo Giao Hàng Không Thành Công (Khách hẹn lại / Từ chối)</span>
                    </button>
                </div>

            </div>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 3. TỒN KHO HÀNG ÔM CỦA NHÂN VIÊN & BÁN HÀNG TẠI ĐIỂM GIAO                 -->
    <!-- ========================================================================= -->
    <div id="holding-stock-section" class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4">
        
        <!-- HEADER SECTION -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-100 text-[#1b4a8c] flex items-center justify-center font-bold text-base shadow-xs">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="font-black text-slate-900 text-base sm:text-lg">Tồn Kho Hàng Ôm Của Nhân Viên / Xe</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-[#1b4a8c] font-bold text-[11px] border border-blue-200">
                            Bán trực tiếp tại điểm giao
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Hàng hóa nhân viên/xe đang nắm giữ để chào mẫu hoặc bán bổ sung ngay cho khách hàng 
                        <strong class="text-slate-800 font-bold">{{ $custName }}</strong>
                    </p>
                </div>
            </div>

            <!-- ACTIONS & WAREHOUSE SELECTOR -->
            <div class="flex flex-wrap items-center gap-2">
                @if(count($employeeWarehouses) > 1)
                <div class="flex items-center gap-1.5 text-xs bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                    <label class="font-bold text-slate-600 whitespace-nowrap text-xs">Kho ôm:</label>
                    <select x-model="selectedHoldingWhId" class="bg-white font-bold text-[#1b4a8c] border border-slate-300 rounded-lg px-2 py-1 text-xs focus:outline-none">
                        @foreach($employeeWarehouses as $ewh)
                            <option value="{{ $ewh->id }}">
                                {{ $ewh->employee_name ?? $ewh->name }} ({{ number_format($ewh->total_qty) }} SP)
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Nút chuyển sang trang tạo đơn bán hàng từ kho ôm -->
                <a :href="'/inventory/holding-sale/create?warehouse_id=' + (selectedHoldingWhId || '') + '&dealer_id={{ $order->dealer_id ?? '' }}'"
                   target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-[#1b4a8c] hover:bg-blue-800 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer no-underline"
                   title="Mở trang bán hàng đầy đủ trong tab mới với đại lý đã chọn sẵn">
                    <i class="fa-solid fa-cart-shopping text-xs"></i>
                    <span>+ Bán Hàng Từ Kho Này ↗</span>
                </a>
            </div>
        </div>

        <!-- THẺ TỔNG QUAN KHO ĐANG CHỌN (TƯƠNG TỰ BÊN IMPORT.BLADE.PHP) -->
        <template x-if="getSelectedHoldingWh()">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Thẻ 1: Thông tin nhân sự giữ kho -->
                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider">Nhân viên giữ kho</span>
                        <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200" x-text="getSelectedHoldingWh()?.employee_code || ('KHO-OM-' + getSelectedHoldingWh()?.id)"></span>
                    </div>
                    <div class="font-black text-slate-900 text-sm truncate" x-text="getSelectedHoldingWh()?.employee_name || getSelectedHoldingWh()?.name"></div>
                    <div class="text-[11px] text-slate-500 flex items-center justify-between">
                        <span x-text="getSelectedHoldingWh()?.department || 'Kinh doanh'"></span>
                        <span class="font-mono text-slate-700 font-semibold" x-text="getSelectedHoldingWh()?.phone || '---'"></span>
                    </div>
                </div>

                <!-- Thẻ 2: Số mặt hàng & tổng số lượng -->
                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-1">
                    <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider">Số mặt hàng đang ôm</span>
                    <div class="font-black text-slate-900 text-base">
                        <span class="text-blue-700 font-mono" x-text="getSelectedHoldingWh()?.total_skus || 0"></span> mặt hàng
                    </div>
                    <div class="text-[11px] text-slate-500">
                        Tổng cộng: <strong class="text-slate-800 font-mono" x-text="formatNumber(getSelectedHoldingWh()?.total_qty || 0)"></strong> sản phẩm
                    </div>
                </div>

                <!-- Thẻ 3: Tổng giá trị hàng ôm -->
                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-1">
                    <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider">Tổng giá trị hàng ôm</span>
                    <div class="font-black text-emerald-700 text-base font-mono" x-text="formatNumber(getSelectedHoldingWh()?.total_val || 0) + ' đ'"></div>
                    <div class="text-[11px] text-slate-500">Theo đơn giá bán niêm yết</div>
                </div>

                <!-- Thẻ 4: Công nợ nhân viên ôm hàng -->
                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider">Công nợ nhân viên</span>
                        <span class="text-[10px] text-amber-700 font-semibold bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Đang nợ cty</span>
                    </div>
                    <div class="font-black text-amber-900 text-base font-mono" x-text="formatNumber(getSelectedHoldingWh()?.debt || 0) + ' đ'"></div>
                    <div class="text-[11px] text-slate-500">
                        Đơn ôm đang giữ: <strong class="text-slate-800 font-mono" x-text="(getSelectedHoldingWh()?.active_orders || []).length"></strong> đơn
                    </div>
                </div>
            </div>
        </template>

        <!-- NẾU CÓ NHIỀU KHO ÔM: THẺ CHỌN NHANH NHÂN VIÊN (GIỐNG IMPORT.BLADE.PHP) -->
        @if(count($employeeWarehouses) > 1)
        <div class="space-y-1.5 pt-1">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                <span>Chọn kho nhân sự / xe:</span>
                <span class="text-[10px] text-slate-400 font-normal">Click vào thẻ để chuyển xem tồn kho của nhân viên đó</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5">
                <template x-for="wh in employeeWarehouses" :key="wh.id">
                    <div @click="selectedHoldingWhId = wh.id"
                         :class="String(selectedHoldingWhId) === String(wh.id) ? 'ring-2 ring-[#1b4a8c] bg-blue-50/60 border-blue-400 shadow-xs' : 'bg-white hover:bg-slate-50 border-slate-200'"
                         class="p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-xs text-slate-900 truncate" x-text="wh.employee_name || wh.name"></div>
                            <div class="text-[10px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono text-blue-700 font-semibold" x-text="wh.employee_code || ('KHO-OM-' + wh.id)"></span>
                                <span>•</span>
                                <span class="font-mono font-bold text-emerald-700" x-text="wh.total_qty + ' SP'"></span>
                            </div>
                        </div>
                        <span x-show="String(selectedHoldingWhId) === String(wh.id)" class="w-5 h-5 rounded-full bg-[#1b4a8c] text-white flex items-center justify-center text-[10px] shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </span>
                    </div>
                </template>
            </div>
        </div>
        @endif

        <!-- BẢNG CHI TIẾT TỒN KHO HÀNG ÔM CỦA NHÂN VIÊN ĐANG CHỌN (CHUẨN FORM NHƯ IMPORT.BLADE.PHP) -->
        <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <!-- THANH TÌM KIẾM SẢN PHẨM TRONG KHO ÔM -->
            <div class="p-3 bg-slate-50/90 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700">Chi tiết sản phẩm:</span>
                    <span class="px-2 py-0.5 rounded-lg bg-blue-100 text-[#1b4a8c] font-bold text-[11px] font-mono"
                          x-text="(getSelectedHoldingWh()?.stocks || []).length + ' sản phẩm'"></span>
                </div>

                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" x-model="holdingSearch" placeholder="Lọc theo tên hoặc mã SKU..."
                           class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-500 shadow-2xs">
                </div>
            </div>

            <!-- TABLE CONTENT -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-100/80 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-3 text-center w-12">STT</th>
                            <th class="py-3 px-3">Mã Hàng (SKU)</th>
                            <th class="py-3 px-3">Tên Sản Phẩm</th>
                            <th class="py-3 px-3 text-center">ĐVT</th>
                            <th class="py-3 px-3 text-center">Tồn Đang Ôm</th>
                            <th class="py-3 px-3 text-right">Đơn Giá Bán</th>
                            <th class="py-3 px-3 text-right">Thành Tiền (Giá Bán)</th>
                            <th class="py-3 px-3 text-center">Vị Trí Lưu</th>
                            <th class="py-3 px-4 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                        <template x-for="(st, sIdx) in getFilteredHoldingStocks()" :key="st.id || sIdx">
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="py-2.5 px-3 text-center font-mono text-slate-400" x-text="sIdx + 1"></td>
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900" x-text="st.sku || st.product?.sku || '---'"></td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800" x-text="st.name || st.product?.name || '---'"></td>
                                <td class="py-2.5 px-3 text-center" x-text="st.unit || st.product?.unit || 'cái'"></td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-mono font-black text-xs" x-text="formatNumber(st.stock_quantity)"></span>
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono text-slate-600" x-text="formatNumber(st.price || st.product?.price || 0) + ' đ'"></td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700" x-text="formatNumber((st.stock_quantity || 0) * (st.price || st.product?.price || 0)) + ' đ'"></td>
                                <td class="py-2.5 px-3 text-center text-slate-500 italic text-[11px]" x-text="st.location || 'Hàng ôm NV'"></td>
                                <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Nút 1: Bán nhanh tại chỗ (Mở Modal Bán Nhanh không mất trang) -->
                                        <button type="button" @click="openQuickSaleModal(st)"
                                                class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-2xs transition-colors cursor-pointer inline-flex items-center gap-1">
                                            <i class="fa-solid fa-bolt text-[10px]"></i>
                                            <span>Bán nhanh</span>
                                        </button>

                                        <!-- Nút 2: Mở trang bán hàng đầy đủ (tab mới) -->
                                        <a :href="'/inventory/holding-sale/create?warehouse_id=' + (selectedHoldingWhId || '') + '&dealer_id={{ $order->dealer_id ?? '' }}&product_id=' + (st.product_id || '')"
                                           target="_blank"
                                           class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-[#1b4a8c] rounded-lg text-xs font-bold border border-blue-200 transition-colors cursor-pointer inline-flex items-center gap-1 no-underline"
                                           title="Mở form bán hàng chi tiết trong tab mới">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                            <span>Trang bán ↗</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- TRẠNG THÁI RỖNG HOẶC KHÔNG TÌM THẤY SẢN PHẨM -->
                        <template x-if="getFilteredHoldingStocks().length === 0">
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-boxes-packing text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-xs font-semibold" x-text="holdingSearch ? 'Không tìm thấy sản phẩm nào khớp với từ khóa tìm kiếm.' : 'Nhân viên / kho này hiện chưa có sản phẩm ôm nào sẵn sàng để bán.'"></p>
                                    <template x-if="holdingSearch">
                                        <button type="button" @click="holdingSearch = ''" class="mt-2 text-blue-600 font-bold hover:underline text-xs cursor-pointer">
                                            Xóa tìm kiếm để xem tất cả
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL BÁN NHANH TRỰC TIẾP TỪ KHO HÀNG ÔM                                    -->
    <!-- ========================================================================= -->
    <div x-show="showQuickSaleModal" x-cloak class="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showQuickSaleModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Bán Nhanh Từ Kho Hàng Ôm</h3>
                        <p class="text-xs text-slate-500 font-medium">Bán bổ sung ngay tại điểm giao hàng</p>
                    </div>
                </div>
                <button type="button" @click="showQuickSaleModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <template x-if="quickSaleProduct">
                <div class="space-y-3.5 text-xs">
                    <!-- Thông tin Khách hàng & Kho xuất -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-semibold">Khách hàng nhận:</span>
                            <span class="font-black text-purple-700">{{ $custName }} ({{ $custCode }})</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-semibold">Kho ôm xuất:</span>
                            <span class="font-bold text-slate-800" x-text="quickSaleProduct.warehouse_name"></span>
                        </div>
                    </div>

                    <!-- Thông tin sản phẩm chọn bán -->
                    <div class="p-3.5 bg-blue-50/60 rounded-xl border border-blue-200 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] text-blue-700 font-bold bg-white px-1.5 py-0.5 rounded border border-blue-200" x-text="quickSaleProduct.sku"></span>
                            <span class="text-emerald-700 font-bold">Tồn còn: <strong class="font-mono" x-text="quickSaleProduct.stock_quantity"></strong> <span x-text="quickSaleProduct.unit"></span></span>
                        </div>
                        <div class="font-black text-slate-900 text-sm" x-text="quickSaleProduct.name"></div>
                    </div>

                    <!-- Số lượng bán & Đơn giá -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Số lượng bán (*):</label>
                            <div class="relative flex items-center">
                                <button type="button" @click="if (quickSaleQty > 1) quickSaleQty--"
                                        class="w-8 h-9 rounded-l-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 font-bold text-slate-700 cursor-pointer flex items-center justify-center">-</button>
                                <input type="number" min="1" :max="quickSaleProduct.stock_quantity"
                                       x-model.number="quickSaleQty"
                                       class="w-full h-9 px-2 bg-white border-y border-slate-300 text-center font-mono font-black text-sm text-slate-900 focus:outline-none">
                                <button type="button" @click="if (quickSaleQty < quickSaleProduct.stock_quantity) quickSaleQty++"
                                        class="w-8 h-9 rounded-r-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 font-bold text-slate-700 cursor-pointer flex items-center justify-center">+</button>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1 flex items-center justify-between">
                                <span>Đơn vị: <strong x-text="quickSaleProduct.unit"></strong></span>
                                <button type="button" @click="quickSaleQty = quickSaleProduct.stock_quantity" class="text-blue-600 font-bold hover:underline cursor-pointer">Bán hết tồn</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Đơn giá bán (*):</label>
                            <div class="relative">
                                <input type="number" min="0" step="1000"
                                       x-model.number="quickSalePrice"
                                       class="w-full h-9 px-3 bg-white border border-slate-300 rounded-xl font-mono font-bold text-sm text-slate-900 focus:outline-none focus:border-blue-500">
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">Đơn vị: VNĐ / <span x-text="quickSaleProduct.unit"></span></div>
                        </div>
                    </div>

                    <!-- Thành tiền tính tự động -->
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                        <span class="font-bold text-emerald-900">Tổng tiền bán:</span>
                        <span class="font-mono text-base font-black text-emerald-700" x-text="formatNumber(quickSaleQty * quickSalePrice) + ' đ'"></span>
                    </div>

                    <!-- Hình thức thanh toán -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Hình thức thanh toán:</label>
                        <select x-model="quickSalePaymentMethod" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-bold text-xs focus:outline-none focus:border-purple-500">
                            <option value="Tiền mặt">Tiền mặt (Đã thu tại chỗ)</option>
                            <option value="Chuyển khoản VietQR">Chuyển khoản VietQR (Đã chuyển)</option>
                            <option value="Ghi nợ">Ghi nợ (Cộng vào công nợ đại lý)</option>
                        </select>
                    </div>

                    <!-- Ghi chú bán -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ghi chú:</label>
                        <input type="text" x-model="quickSaleNotes" placeholder="Ghi chú thêm nếu có..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none">
                    </div>
                </div>
            </template>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showQuickSaleModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitQuickSale()" :disabled="isSubmittingQuickSale"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer shadow-sm disabled:opacity-50">
                    <i class="fa-solid" :class="isSubmittingQuickSale ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                    <span x-text="isSubmittingQuickSale ? 'Đang tạo đơn & trừ kho ôm...' : 'Xác nhận bán & Trừ kho ôm'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL BÁO GIAO HÀNG THẤT BẠI                                              -->
    <!-- ========================================================================= -->
    <div x-show="showFailModal" x-cloak class="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showFailModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Báo Giao Đơn Không Thành Công</h3>
                        <p class="text-xs text-slate-500 font-medium">Ghi nhận lý do và đưa hàng trở lại xe</p>
                    </div>
                </div>
                <button type="button" @click="showFailModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Lý do không giao được (*):</label>
                    <select x-model="failReason" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none">
                        <option value="Khách hẹn giao lại vào ngày mai">Khách hẹn giao lại vào ngày mai</option>
                        <option value="Không liên lạc được khách hàng">Không liên lạc được khách hàng</option>
                        <option value="Cửa hàng / kho khách đóng cửa">Cửa hàng / kho khách đóng cửa</option>
                        <option value="Khách từ chối nhận hàng do sai quy cách">Khách từ chối nhận hàng do sai quy cách</option>
                        <option value="Lý do khác">Lý do khác</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ghi chú chi tiết thêm:</label>
                    <textarea x-model="failNotes" rows="2" placeholder="Ghi chú thêm lời nhắn của khách hoặc tình trạng..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showFailModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitDeliveryFailure()" :disabled="isSubmittingDelivery"
                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer shadow-sm">
                    <i class="fa-solid fa-check"></i>
                    <span>Xác nhận không thành công</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function deliveryConfirmPage() {
    return {
        orderId: {{ $order->id }},
        orderCode: '{{ $order->order_code }}',
        customerCode: '{{ $custCode }}',
        customerName: '{{ addslashes($custName) }}',
        vehiclePlate: '{{ $vehiclePlate ?? "" }}',
        previousDebt: {{ $prevDebt }},
        creditLimit: {{ $cLimit }},

        // --- Credit Overrun State ---
        overrunMode: 'split',         // 'split' | 'approval'
        overrunStatus: '{{ $initStatus }}',    // '' | 'Chờ duyệt' | 'Đã duyệt' | 'Từ chối'
        overrunApprover: '{{ $initApprover }}',
        overrunApprovedAt: '{{ $initApprovedAt }}',
        splitCollectMethod: 'Tiền mặt',
        customSplitCollectAmount: {{ max(0, ($prevDebt + $origTotal) - $cLimit) }},
        isSendingRequest: false,
        isApprovingOverride: false,
        approvalNote: '',
        _pollInterval: null,

        toast: { show: false, message: '', type: 'success' },
        copiedField: '',
        isSubmittingDelivery: false,
        showFailModal: false,
        failReason: 'Khách hẹn giao lại vào ngày mai',
        failNotes: '',

        // --- Tồn Kho Hàng Ôm & Bán Hàng Trực Tiếp ---
        employeeWarehouses: @json($employeeWarehouses ?? []),
        selectedHoldingWhId: {{ $defaultWhId ? (int)$defaultWhId : "''" }},
        holdingSearch: '',

        // Quick Sale Modal State
        showQuickSaleModal: false,
        quickSaleProduct: null,
        quickSaleQty: 1,
        quickSalePrice: 0,
        quickSalePaymentMethod: 'Tiền mặt',
        quickSaleNotes: '',
        isSubmittingQuickSale: false,

        confirmForm: {
            orderId: {{ $order->id }},
            orderCode: '{{ $order->order_code }}',
            recipientName: '{{ addslashes($order->customer_name ?: $custName) }}',
            deliveryNotes: '',
            hasReturns: {{ $order->has_returns ? 'true' : 'false' }},
            paymentMethod: '{{ $order->payment_method ?: "Ghi nợ" }}',
            paymentStatus: '{{ $order->payment_status ?: "Ghi nợ" }}',
            collectedAmount: {{ $origTotal }},
            finalAmount: {{ $origTotal }},
            isDebt: true,
            handoverToUserId: '',
            items: []
        },

        // Computed: vượt hạn mức?
        get isOverLimit() {
            if (this.creditLimit <= 0) return false;
            const isDebtMode = this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ';
            if (!isDebtMode) return false;
            return (this.previousDebt + this.confirmForm.finalAmount) > this.creditLimit;
        },

        // Computed: số tiền vượt tối thiểu cần thu để không vượt trần hạn mức
        get excessAmount() {
            if (!this.isOverLimit) return 0;
            return Math.max(0, (this.previousDebt + this.confirmForm.finalAmount) - this.creditLimit);
        },

        // Computed: số tiền thực tế thu ngay (mặc định là excessAmount, có thể nhập nhiều hơn)
        get splitCollectAmountActual() {
            if (this.customSplitCollectAmount !== null && this.customSplitCollectAmount !== '' && !isNaN(this.customSplitCollectAmount)) {
                return Math.max(0, Number(this.customSplitCollectAmount));
            }
            return this.excessAmount;
        },

        // Computed: số tiền trong hạn mức còn ghi nợ đơn này
        get allowedDebt() {
            return Math.max(0, this.confirmForm.finalAmount - this.splitCollectAmountActual);
        },

        // Computed: số tiền thu vượt giá trị đơn hàng (cấn trừ trực tiếp nợ cũ đại lý)
        get surplusOverOrder() {
            return Math.max(0, this.splitCollectAmountActual - this.confirmForm.finalAmount);
        },

        // Computed: dự tính tổng công nợ đại lý sau khi hoàn tất giao
        get projectedTotalDebt() {
            return Math.max(0, this.previousDebt + this.confirmForm.finalAmount - this.splitCollectAmountActual);
        },

        // Computed: VietQR URL cho số tiền thu thực tế khi split payment
        get splitVietQrUrl() {
            const amt = Math.round(this.splitCollectAmountActual);
            const memo = encodeURIComponent(this.qrTransferMemo + ' THU');
            return `https://img.vietqr.io/image/MB-0903888999-compact2.png?amount=${amt}&addInfo=${memo}&accountName=CONG%20TY%20TNHH%20MANH%20QUAN`;
        },

        // Computed: button submit bị block?
        get isSubmitBlocked() {
            if (!this.isOverLimit) return false;
            if (this.overrunMode === 'approval' && this.overrunStatus !== 'Đã duyệt') return true;
            if (this.overrunMode === 'split' && this.splitCollectAmountActual < this.excessAmount) return true;
            return false;
        },

        // Computed: nhãn nút submit
        get submitBtnLabel() {
            if (this.isSubmittingDelivery) return 'Đang lưu kết quả giao...';
            const isDebt = this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ';
            if (this.isOverLimit) {
                if (this.overrunMode === 'split') {
                    if (this.splitCollectAmountActual < this.excessAmount) {
                        return `CHƯA ĐỦ MỨC THU TỐI THIỂU (${this.formatNumber(this.excessAmount)} đ)`;
                    }
                    const debtPart = this.allowedDebt > 0 
                        ? ` + KÝ NỢ ${this.formatNumber(this.allowedDebt)} đ` 
                        : (this.surplusOverOrder > 0 ? ` (TRỪ NỢ CŨ ${this.formatNumber(this.surplusOverOrder)} đ)` : ' (KHÔNG KÝ NỢ)');
                    return `XÁC NHẬN: THU ${this.formatNumber(this.splitCollectAmountActual)} đ${debtPart}`;
                }
                if (this.overrunStatus === 'Đã duyệt') {
                    return `ĐÃ ĐƯỢC DUYỆT BẢO LÃNH — XÁC NHẬN GIAO ĐƠN & GHI NỢ`;
                }
                return `⏳ CHỜ PHÊ DUYỆT BẢO LÃNH — KHÔNG THỂ XÁC NHẬN`;
            }
            return isDebt ? 'XÁC NHẬN GIAO ĐƠN & GHI NỢ ĐẠI LÝ' : 'XÁC NHẬN GIAO HÀNG & THU TIỀN';
        },

        initData() {
            @php
                $itemsJson = json_encode($order->items->map(function($it) {
                    return [
                        'id' => $it->id,
                        'order_item_id' => $it->id,
                        'product_id' => $it->product_id,
                        'sku' => $it->sku,
                        'name' => $it->name,
                        'unit' => $it->unit ?: 'cái',
                        'quantity' => (float)$it->quantity,
                        'unit_price' => (float)$it->unit_price,
                        'amount' => (float)$it->amount,
                        'returnedQty' => 0,
                        'returnReason' => ''
                    ];
                }));
            @endphp
            this.confirmForm.items = {!! $itemsJson !!};

            const origTotal = {{ $origTotal }};
            const currentPm = '{{ $order->payment_method ?: "Ghi nợ" }}';
            const currentPs = '{{ $order->payment_status ?: "Ghi nợ" }}';

            if (currentPm === 'Ghi nợ' || currentPm === 'Ghi nợ đại lý' || currentPs === 'Ghi nợ' || currentPs === 'Ghi nợ đại lý') {
                this.confirmForm.paymentMethod = 'Ghi nợ';
                this.confirmForm.paymentStatus = 'Ghi nợ';
                this.confirmForm.isDebt = true;
                this.confirmForm.collectedAmount = 0;
            } else if (currentPm.includes('VietQR') || currentPm.includes('Chuyển khoản')) {
                this.confirmForm.paymentMethod = 'Chuyển khoản VietQR';
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = origTotal;
            } else {
                this.confirmForm.paymentMethod = 'Tiền mặt';
                this.confirmForm.paymentStatus = currentPs || 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = origTotal;
            }

            this.recalcReturnTotals();

            // Bắt đầu polling nếu đang chờ duyệt
            if (this.overrunStatus === 'Chờ duyệt') {
                this.startPolling();
            }

            // Lắng nghe sự kiện Realtime khi người lên đơn hoặc kế toán phê duyệt/từ chối
            window.addEventListener('erp:credit-override-decided', (e) => {
                const detail = e.detail;
                if (!detail) return;
                const extra = detail.extraData || {};
                const evOrderId = detail.orderId || extra.orderId;
                if (evOrderId && String(evOrderId) === String(this.orderId)) {
                    const prev = this.overrunStatus;
                    this.overrunStatus = extra.status || (extra.decision === 'approve' ? 'Đã duyệt' : 'Từ chối');
                    this.overrunApprover = extra.approverName || '';
                    this.overrunApprovedAt = extra.approvedAt || '';
                    if (this._pollInterval) {
                        clearInterval(this._pollInterval);
                        this._pollInterval = null;
                    }
                    if (this.overrunStatus === 'Đã duyệt' && prev !== 'Đã duyệt') {
                        this.showToast('✅ Đã được phê duyệt bảo lãnh! Lái xe có thể xác nhận giao đơn.', 'success');
                    } else if (this.overrunStatus === 'Từ chối' && prev !== 'Từ chối') {
                        this.showToast('❌ Yêu cầu bảo lãnh nợ bị từ chối.', 'error');
                    }
                }
            });
        },

        startPolling() {
            if (this._pollInterval) return;
            this._pollInterval = setInterval(async () => {
                if (this.overrunStatus === 'Đã duyệt' || this.overrunStatus === 'Từ chối') {
                    clearInterval(this._pollInterval);
                    this._pollInterval = null;
                    return;
                }
                try {
                    const res = await fetch(`/delivery/orders/${this.orderId}/credit-override-status`);
                    const data = await res.json();
                    if (data.success && data.status) {
                        const prev = this.overrunStatus;
                        this.overrunStatus = data.status;
                        this.overrunApprover = data.approvedByName || '';
                        this.overrunApprovedAt = data.approvedAt || '';
                        if (data.status === 'Đã duyệt' && prev !== 'Đã duyệt') {
                            this.showToast('✅ Đã được phê duyệt bảo lãnh! Có thể xác nhận giao đơn.', 'success');
                            clearInterval(this._pollInterval); this._pollInterval = null;
                        } else if (data.status === 'Từ chối' && prev !== 'Từ chối') {
                            this.showToast('❌ Yêu cầu bị từ chối. Vui lòng chọn phương án khác.', 'error');
                            clearInterval(this._pollInterval); this._pollInterval = null;
                        }
                    }
                } catch(e) {}
            }, 5000);
        },

        async sendOverrideRequest() {
            this.isSendingRequest = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/delivery/orders/${this.orderId}/request-credit-override`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        overrunAmount: this.excessAmount,
                        reason: `Đại lý ${this.customerName} xin nợ vượt hạn mức ${this.formatNumber(this.excessAmount)} đ khi giao đơn ${this.orderCode}`
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.overrunStatus = 'Chờ duyệt';
                    this.showToast('📨 Đã gửi yêu cầu realtime tới tài khoản lên đơn!');
                    this.startPolling();
                    // Phát sóng ngay lập tức tới mọi tab mở
                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({
                            type: 'CREDIT_OVERRUN_REQUESTED',
                            orderCode: this.orderCode,
                            orderId: this.orderId,
                            extraData: {
                                orderId: this.orderId,
                                orderCode: this.orderCode,
                                customerName: this.customerName,
                                driverName: '{{ auth()->user()?->name ?? "Lái xe giao hàng" }}',
                                overrunAmount: this.excessAmount,
                                reason: `Đại lý ${this.customerName} xin nợ vượt hạn mức ${this.formatNumber(this.excessAmount)} đ khi giao đơn ${this.orderCode}`
                            }
                        });
                    } catch(e) {}
                    localStorage.setItem('erp_order_sync_trigger', Date.now());
                } else {
                    this.showToast('Lỗi: ' + (data.message || 'Không thể gửi yêu cầu'), 'error');
                }
            } catch(e) {
                this.showToast('Lỗi kết nối: ' + e.message, 'error');
            } finally {
                this.isSendingRequest = false;
            }
        },

        async approveOverride(decision) {
            this.isApprovingOverride = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/delivery/orders/${this.orderId}/approve-credit-override`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ decision: decision, note: this.approvalNote })
                });
                const data = await res.json();
                if (data.success) {
                    this.overrunStatus = data.status;
                    this.overrunApprover = data.approvedByName || '';
                    this.overrunApprovedAt = data.approvedAt || '';
                    const msg = decision === 'approve'
                        ? '✅ Đã phê duyệt bảo lãnh thành công! Lái xe có thể xác nhận giao đơn.'
                        : '❌ Đã từ chối yêu cầu bảo lãnh.';
                    this.showToast(msg, decision === 'approve' ? 'success' : 'error');
                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({ type: 'CREDIT_OVERRUN_DECIDED', orderId: this.orderId, decision: decision });
                    } catch(e) {}
                    localStorage.setItem('erp_order_sync_trigger', Date.now());
                } else {
                    this.showToast('Lỗi: ' + (data.message || 'Thao tác thất bại'), 'error');
                }
            } catch(e) {
                this.showToast('Lỗi kết nối: ' + e.message, 'error');
            } finally {
                this.isApprovingOverride = false;
            }
        },

        selectPaymentMode(mode) {
            if (mode === 'Ghi nợ') {
                this.confirmForm.paymentMethod = 'Ghi nợ';
                this.confirmForm.paymentStatus = 'Ghi nợ';
                this.confirmForm.isDebt = true;
                this.confirmForm.collectedAmount = 0;
            } else if (mode === 'VietQR') {
                this.confirmForm.paymentMethod = 'Chuyển khoản VietQR';
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            } else {
                this.confirmForm.paymentMethod = 'Tiền mặt';
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            }
        },

        onPaymentMethodSelectChange() {
            const pm = this.confirmForm.paymentMethod;
            if (pm === 'Ghi nợ') {
                this.confirmForm.paymentStatus = 'Ghi nợ';
                this.confirmForm.isDebt = true;
                this.confirmForm.collectedAmount = 0;
            } else if (pm === 'Chuyển khoản VietQR') {
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            } else {
                this.confirmForm.isDebt = false;
                this.confirmForm.paymentStatus = 'Đã thanh toán';
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            }
        },

        onHasReturnsToggle() {
            if (!this.confirmForm.hasReturns) {
                this.confirmForm.items.forEach(it => {
                    it.returnedQty = 0;
                    it.returnReason = '';
                });
            }
            this.recalcReturnTotals();
        },

        validateReturnQty(it) {
            const maxQty = Number(it.quantity || 0);
            if (it.returnedQty === null || it.returnedQty === undefined || it.returnedQty === '') {
                this.recalcReturnTotals();
                return;
            }
            let val = Number(it.returnedQty);
            if (isNaN(val) || val < 0) {
                it.returnedQty = 0;
            } else if (val > maxQty) {
                it.returnedQty = maxQty;
                this.showToast(`⛔ Số lượng trả không được vượt quá số lượng giao (${this.formatNumber(maxQty)} ${it.unit})!`, 'error');
            }
            this.recalcReturnTotals();
        },

        get returnTotalAmount() {
            if (!this.confirmForm.hasReturns) return 0;
            return this.confirmForm.items.reduce((sum, it) => {
                const maxQty = Number(it.quantity || 0);
                const retQty = Math.min(Math.max(0, Number(it.returnedQty || 0)), maxQty);
                return sum + (retQty * Number(it.unit_price || 0));
            }, 0);
        },

        recalcReturnTotals() {
            const retAmt = this.returnTotalAmount;
            const origTotal = {{ $origTotal }};
            this.confirmForm.finalAmount = Math.max(0, origTotal - retAmt);
            if (!this.confirmForm.isDebt && this.confirmForm.paymentMethod !== 'Ghi nợ') {
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            }
            if (this.isOverLimit && (!this.customSplitCollectAmount || this.customSplitCollectAmount <= 0)) {
                this.customSplitCollectAmount = this.excessAmount;
            }
        },

        get qrTransferMemo() {
            return `${this.customerCode} ${this.orderCode}`.trim();
        },

        get vietQrUrl() {
            const amt = Math.round(Number(this.confirmForm.collectedAmount || this.confirmForm.finalAmount || 0));
            const memo = encodeURIComponent(this.qrTransferMemo);
            return `https://img.vietqr.io/image/MB-0903888999-compact2.png?amount=${amt}&addInfo=${memo}&accountName=CONG%20TY%20TNHH%20MANH%20QUAN`;
        },

        formatNumber(val) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val) || 0));
        },

        orderStatusBadgeClass() {
            const s = '{{ $order->status }}';
            if (s === 'Đã giao' || s === 'Giao thành công') return 'bg-emerald-50 text-emerald-800 border border-emerald-300';
            if (s === 'Đang vận chuyển') return 'bg-sky-50 text-sky-800 border border-sky-300';
            return 'bg-amber-50 text-amber-800 border border-amber-300';
        },

        async copyToClipboard(text, fieldName = '') {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(String(text));
                } else {
                    const textArea = document.createElement("textarea");
                    textArea.value = String(text);
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                }
                this.copiedField = fieldName;
                this.showToast('📋 Đã sao chép: ' + text);
                setTimeout(() => { this.copiedField = ''; }, 2000);
            } catch (err) {
                this.showToast('Không thể sao chép: ' + err.message, 'error');
            }
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 4500);
        },

        async submitDeliveryCompletion() {
            if (!this.confirmForm.recipientName || !this.confirmForm.recipientName.trim()) {
                alert('Vui lòng nhập họ tên người đại diện đại lý ký nhận hàng.');
                return;
            }

            // Kiểm tra số lượng trả không được vượt quá số lượng giao
            if (this.confirmForm.hasReturns) {
                for (const it of this.confirmForm.items) {
                    const retQty = Number(it.returnedQty || 0);
                    const maxQty = Number(it.quantity || 0);
                    if (retQty > maxQty) {
                        alert(`⛔ Số lượng trả lại của mặt hàng "${it.name}" (${this.formatNumber(retQty)} ${it.unit}) không được vượt quá số lượng giao (${this.formatNumber(maxQty)} ${it.unit})!`);
                        it.returnedQty = maxQty;
                        this.recalcReturnTotals();
                        return;
                    }
                    if (retQty < 0) {
                        alert(`⛔ Số lượng trả lại của mặt hàng "${it.name}" không thể là số âm!`);
                        it.returnedQty = 0;
                        this.recalcReturnTotals();
                        return;
                    }
                }
            }

            const isDebt = this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ';
            const isSplit = isDebt && this.isOverLimit && this.overrunMode === 'split';

            // Chặn nếu cần duyệt mà chưa duyệt
            if (this.isSubmitBlocked) {
                alert('⛔ Đơn này cần được Kế toán / Giám đốc phê duyệt bảo lãnh trước khi xác nhận giao hàng.');
                return;
            }

            let actionText;
            if (isSplit) {
                if (this.splitCollectAmountActual < this.excessAmount) {
                    alert(`Số tiền thu tối thiểu phải là ${this.formatNumber(this.excessAmount)} đ để đảm bảo nợ đại lý không vượt hạn mức.`);
                    return;
                }
                const debtMsg = this.allowedDebt > 0 
                    ? `• Ghi nợ đơn này: ${this.formatNumber(this.allowedDebt)} đ\n` 
                    : `• Đơn này thanh toán đủ (0 đ ghi nợ đơn)\n`;
                const surplusMsg = this.surplusOverOrder > 0
                    ? `• Cấn trừ vào nợ cũ đại lý: ${this.formatNumber(this.surplusOverOrder)} đ\n`
                    : '';

                actionText = `Xác nhận giao đơn #${this.orderCode}?\n` +
                    `• Thu ngay (${this.splitCollectMethod}): ${this.formatNumber(this.splitCollectAmountActual)} đ\n` +
                    debtMsg + surplusMsg +
                    `• Dự tính tổng nợ đại lý sau giao: ${this.formatNumber(this.projectedTotalDebt)} đ (Hạn mức: ${this.formatNumber(this.creditLimit)} đ)`;
            } else if (isDebt) {
                actionText = `Xác nhận giao đơn #${this.orderCode} và KÝ NỢ TOÀN BỘ ${this.formatNumber(this.confirmForm.finalAmount)} đ vào sổ đại lý?`;
            } else {
                actionText = `Xác nhận giao đơn #${this.orderCode} và ĐÃ THU ${this.formatNumber(this.confirmForm.collectedAmount)} đ (${this.confirmForm.paymentMethod})?`;
            }

            if (!confirm(actionText)) return;

            this.isSubmittingDelivery = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const payload = {
                    orderId: this.orderId,
                    orderCode: this.orderCode,
                    recipientName: this.confirmForm.recipientName.trim(),
                    deliveryNotes: this.confirmForm.deliveryNotes ? this.confirmForm.deliveryNotes.trim() : '',
                    paymentMethod: this.confirmForm.paymentMethod,
                    paymentStatus: this.confirmForm.paymentStatus,
                    collectedAmount: isDebt && !isSplit ? 0 : Number(this.confirmForm.collectedAmount || 0),
                    isDebt: isDebt && !isSplit,
                    hasReturns: this.confirmForm.hasReturns,
                    returnedItems: this.confirmForm.hasReturns ? this.confirmForm.items.filter(it => Number(it.returnedQty) > 0) : [],
                    // Split payment fields
                    isSplitPayment: isSplit,
                    splitCollectMethod: isSplit ? this.splitCollectMethod : null,
                    splitCollectAmount: isSplit ? this.splitCollectAmountActual : 0,
                    splitDebtAmount: isSplit ? this.allowedDebt : 0,
                    handoverToUserId: this.confirmForm.handoverToUserId || null,
                };

                const res = await fetch('/delivery/complete-order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    alert('✅ Xác nhận giao hàng thành công!');
                    window.location.href = '/delivery-execution?vehicle=' + encodeURIComponent(this.vehiclePlate);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lưu kết quả giao hàng'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmittingDelivery = false;
            }
        },

        async submitDeliveryFailure() {
            if (!confirm(`Xác nhận đánh dấu đơn #${this.orderCode} giao không thành công?`)) return;

            this.isSubmittingDelivery = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/delivery/fail-order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        orderId: this.orderId,
                        failReason: this.failReason,
                        failNotes: this.failNotes
                    })
                });

                const data = await res.json();
                if (data.success) {
                    alert('Đã cập nhật trạng thái đơn hàng: Không thành công.');
                    window.location.href = '/delivery-execution?vehicle=' + encodeURIComponent(this.vehiclePlate);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmittingDelivery = false;
            }
        },

        // --- Methods for Tồn Kho Hàng Ôm & Bán Hàng Trực Tiếp ---
        getSelectedHoldingWh() {
            if (!this.employeeWarehouses || this.employeeWarehouses.length === 0) return null;
            if (!this.selectedHoldingWhId) return this.employeeWarehouses[0];
            return this.employeeWarehouses.find(w => String(w.id) === String(this.selectedHoldingWhId)) || this.employeeWarehouses[0];
        },

        getFilteredHoldingStocks() {
            const wh = this.getSelectedHoldingWh();
            if (!wh || !wh.stocks) return [];
            const q = (this.holdingSearch || '').trim().toLowerCase();
            if (!q) return wh.stocks;
            return wh.stocks.filter(st => {
                const name = (st.name || '').toLowerCase();
                const sku = (st.sku || '').toLowerCase();
                return name.includes(q) || sku.includes(q);
            });
        },

        scrollToHoldingStock() {
            const el = document.getElementById('holding-stock-section');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        openQuickSaleModal(stockItem) {
            const wh = this.getSelectedHoldingWh();
            if (!wh || !stockItem) return;
            this.quickSaleProduct = {
                warehouse_id: wh.id,
                warehouse_name: wh.name,
                employee_id: wh.employee_id,
                employee_name: wh.employee_name,
                product_id: stockItem.product_id,
                name: stockItem.name,
                sku: stockItem.sku,
                unit: stockItem.unit,
                stock_quantity: Number(stockItem.stock_quantity || 0),
                price: Number(stockItem.price || stockItem.selling_price || 0)
            };
            this.quickSaleQty = 1;
            this.quickSalePrice = Number(stockItem.price || stockItem.selling_price || 0);
            this.quickSalePaymentMethod = 'Tiền mặt';
            this.quickSaleNotes = 'Bán tại điểm giao đơn #' + this.orderCode;
            this.showQuickSaleModal = true;
        },

        async submitQuickSale() {
            if (!this.quickSaleProduct) return;
            const maxStock = Number(this.quickSaleProduct.stock_quantity || 0);
            const qty = Number(this.quickSaleQty);
            if (qty <= 0) {
                alert('Số lượng bán phải lớn hơn 0!');
                return;
            }
            if (qty > maxStock) {
                alert(`Số lượng bán (${qty}) vượt quá số lượng tồn đang giữ (${maxStock})!`);
                return;
            }

            this.isSubmittingQuickSale = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const p = this.quickSaleProduct;
                const unitPrice = Number(this.quickSalePrice);
                const totalAmount = qty * unitPrice;

                const payload = {
                    warehouse: p.warehouse_name,
                    warehouse_id: p.warehouse_id,
                    employee_id: p.employee_id,
                    employee_name: p.employee_name,
                    sales_rep_id: p.employee_id,
                    salesRepId: p.employee_id,
                    order_type: 'holding_sale',
                    orderType: 'holding_sale',
                    is_holding_stock: true,
                    is_holding_stock_sale: true,
                    status: 'Đã giao hàng',
                    dealerId: {{ $order->dealer_id ?: 'null' }},
                    dealerName: this.customerName,
                    customerName: this.customerName,
                    customerPhone: '{{ $order->customer_phone ?? "" }}',
                    customerAddress: '{{ addslashes($order->customer_address ?? "") }}',
                    deliveryAddress: '{{ addslashes($order->customer_address ?? "") }}',
                    paymentMethod: this.quickSalePaymentMethod,
                    paymentStatus: this.quickSalePaymentMethod === 'Ghi nợ' || this.quickSalePaymentMethod === 'Công nợ (Ghi nợ đại lý)' ? 'Chưa thanh toán' : 'Đã thanh toán',
                    notes: this.quickSaleNotes || ('Bán từ kho ôm tại điểm giao đơn #' + this.orderCode),
                    items: [{
                        product_id: parseInt(p.product_id, 10),
                        name: p.name,
                        sku: p.sku,
                        unit: p.unit,
                        quantity: qty,
                        unitPrice: unitPrice,
                        unit_price: unitPrice,
                        discount: 0,
                        amount: totalAmount,
                        isChecked: true,
                        isPicked: true
                    }]
                };

                const res = await fetch('/api/orders/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok && (data.success || data.order_id || data.id || data.order?.id)) {
                    // Cập nhật tồn kho cục bộ ngay trên màn hình mà không cần load lại trang
                    p.stock_quantity -= qty;
                    const wh = this.getSelectedHoldingWh();
                    if (wh && wh.stocks) {
                        const targetStock = wh.stocks.find(s => String(s.product_id) === String(p.product_id));
                        if (targetStock) {
                            targetStock.stock_quantity = Math.max(0, Number(targetStock.stock_quantity) - qty);
                        }
                        wh.total_qty = wh.stocks.reduce((sum, s) => sum + Number(s.stock_quantity || 0), 0);
                        wh.total_val = wh.stocks.reduce((sum, s) => sum + (Number(s.stock_quantity || 0) * Number(s.price || 0)), 0);
                    }

                    this.showQuickSaleModal = false;
                    this.showToast('✅ Bán hàng thành công! Đã tạo đơn bán và trừ kho ôm ' + qty + ' ' + p.unit, 'success');
                } else {
                    alert('Lỗi khi tạo đơn bán hàng: ' + (data.message || data.error || 'Vui lòng thử lại'));
                }
            } catch (err) {
                alert('Lỗi kết nối: ' + err.message);
            } finally {
                this.isSubmittingQuickSale = false;
            }
        },

        printDeliverySlip() {
            window.open(`/orders/${this.orderId}/edit`, '_blank');
        }
    };
}
</script>
@endsection
