@extends('layouts.app')

@section('title', 'Tạo Phiếu Nhập Hàng Từ Đơn Đặt NCC | ERP Đại Sơn')

@section('content')
<style>
/* Tắt hoàn toàn spinner mũi tên tăng giảm và autofill tooltip của trình duyệt */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>
<div class="space-y-4 font-sans text-slate-800 antialiased w-full pb-20" x-data="supplierOrderReceiptApp()" @keydown.window="handleGlobalKeydown($event)">
    
    <!-- 1. Sub-Section Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto">
        <a href="{{ url('/orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <span>Đơn hàng</span>
        </a>

        <a href="{{ url('/orders?tab=consignment') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-boxes-stacked text-sm"></i>
            <span>Đơn hàng ôm</span>
        </a>

        <a href="{{ url('/orders?tab=returns') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-rotate-left text-sm"></i>
            <span>Khách trả hàng</span>
        </a>

        <a href="{{ url('/supplier-orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-sky-600 text-white shadow-md shadow-sky-600/20">
            <i class="fa-solid fa-cart-shopping text-sm"></i>
            <span>Đặt hàng NCC</span>
        </a>
    </div>

    <!-- 2. Header Banner -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/supplier-orders') }}" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center shrink-0 border border-slate-300 cursor-pointer transition-transform hover:scale-105" title="Quay lại danh sách Đặt hàng NCC">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Tạo Phiếu Nhập Hàng Từ Đơn Đặt NCC</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[11px] font-extrabold border border-blue-200 font-mono">{{ $order->code }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Xác nhận hàng thực nhận về kho · Cập nhật tồn kho tự động · Xử lý nợ hàng hoặc nợ tiền khi thiếu hàng</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/supplier-orders') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-xmark text-slate-400"></i>
                <span>Hủy bỏ</span>
            </a>
            <button type="button" @click="submitReceipt()" :disabled="receiptSaving" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 cursor-pointer disabled:opacity-50" title="Xác nhận nhập kho (F9)">
                <i class="fa-solid fa-spinner animate-spin" x-show="receiptSaving"></i>
                <i class="fa-solid fa-boxes-packing" x-show="!receiptSaving"></i>
                <span>Xác Nhận Nhập Kho <kbd class="px-1.5 py-0.5 bg-blue-700/80 border border-blue-500 rounded text-[10px] text-white font-mono">F9</kbd></span>
            </button>
        </div>
    </div>

    <!-- 3. Body Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-7 space-y-6">
        
        <!-- Tóm tắt đơn hàng -->
        <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 border border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nhà cung cấp</span>
                <span class="font-black text-slate-900 text-sm mt-0.5 block">{{ $order->supplier_name }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tổng giá trị đơn đặt</span>
                <span class="font-black text-slate-900 text-sm mt-0.5 block">{{ number_format($order->total_amount) }} đ</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Đã thanh toán / Cấn trừ nợ</span>
                <div class="mt-0.5">
                    @php
                        $settledTotal = (float)$order->paid_amount + (float)$order->debt_deducted_amount;
                    @endphp
                    <span class="font-black text-emerald-700 text-sm">{{ number_format($settledTotal) }} đ</span>
                    @if((float)$order->debt_deducted_amount > 0)
                        <div class="text-[10.5px] text-slate-500 font-medium">
                            (Trả {{ number_format($order->paid_amount) }}đ + Trừ nợ {{ number_format($order->debt_deducted_amount) }}đ)
                        </div>
                    @else
                        <span class="text-[11px] font-bold text-slate-500">({{ $order->payment_status }})</span>
                    @endif
                </div>
            </div>
            <div>
                <label class="text-[10px] text-slate-500 font-bold block uppercase tracking-wider mb-1">Kho thực tế nhận hàng <span class="text-rose-500">*</span></label>
                <select x-model="receiptData.warehouse_id" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 font-bold text-xs bg-white focus:border-sky-500 outline-none">
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ ($order->warehouse_id == $w->id) ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Bảng danh sách hàng nhập kho -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                    Xác Nhận Số Lượng Hàng Thực Tế Nhận Về Kho
                </label>
                <div class="flex items-center gap-2">
                    <button type="button" @click="fillFullReceipt()" class="px-3 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-xl text-xs font-bold border border-sky-200 cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-check-double mr-1"></i>Nhận đủ toàn bộ
                    </button>
                    <button type="button" @click="clearReceiptQuantities()" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl text-xs font-bold border border-slate-200 cursor-pointer shadow-2xs">
                        Xóa trắng
                    </button>
                </div>
            </div>

            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black text-[10.5px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-3.5 min-w-[240px]">Sản phẩm / Mặt hàng</th>
                            <th class="py-3 px-2 text-center w-20">ĐVT</th>
                            <th class="py-3 px-2.5 text-right w-28">SL Đặt</th>
                            <th class="py-3 px-2.5 text-right w-28">Đã nhận trước</th>
                            <th class="py-3 px-3.5 text-right w-36 bg-sky-50 text-sky-900 border-x border-sky-200">
                                SL Nhận đợt này <span class="text-rose-500">*</span>
                            </th>
                            <th class="py-3 px-2.5 text-right w-28">Còn thiếu</th>
                            <th class="py-3 px-3 w-40">Mã Lô / HSD (Tùy chọn)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, idx) in receiptData.items" :key="item.item_id">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3.5">
                                    <div class="font-black text-slate-900 text-xs" x-text="item.product_name"></div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5" x-show="item.sku" x-text="'SKU: ' + item.sku"></div>
                                </td>
                                <td class="py-3 px-2 text-center font-semibold text-slate-600" x-text="item.unit"></td>
                                <td class="py-3 px-2.5 text-right font-bold text-slate-700" x-text="item.ordered_quantity"></td>
                                <td class="py-3 px-2.5 text-right font-semibold text-slate-500" x-text="item.received_before"></td>
                                
                                <!-- Input SL Nhận đợt này -->
                                <td class="py-2.5 px-3.5 text-right bg-sky-50/40 border-x border-sky-200">
                                    <input type="number" 
                                           autocomplete="off"
                                           autocorrect="off"
                                           spellcheck="false"
                                           data-lpignore="true"
                                           x-model="item.quantity" 
                                           @input="recalcReceiptStatus()" 
                                           min="0" 
                                           :max="item.ordered_quantity - item.received_before" 
                                           step="any" 
                                           class="w-full px-3 py-1.5 rounded-xl border border-sky-300 text-xs font-black text-right text-sky-900 bg-white focus:ring-1 focus:ring-sky-500 outline-none">
                                </td>

                                <!-- Còn thiếu sau đợt này -->
                                <td class="py-3 px-2.5 text-right font-black" :class="(item.ordered_quantity - item.received_before - (parseFloat(item.quantity) || 0)) > 0 ? 'text-rose-600' : 'text-emerald-600'">
                                    <span x-text="Math.max(0, item.ordered_quantity - item.received_before - (parseFloat(item.quantity) || 0))"></span>
                                </td>

                                <!-- Lô & HSD -->
                                <td class="py-2 px-3">
                                    <input type="text" 
                                           autocomplete="off"
                                           autocorrect="off"
                                           spellcheck="false"
                                           x-model="item.batch_code" 
                                           placeholder="Mã lô hàng" 
                                           class="w-full px-2 py-1 rounded-lg border border-slate-200 text-xs mb-1">
                                    <input type="date" 
                                           autocomplete="off"
                                           x-model="item.expiry_date" 
                                           title="Hạn sử dụng" 
                                           class="w-full px-2 py-0.5 rounded-lg border border-slate-200 text-[10.5px]">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================================================================================ -->
        <!-- KHUNG CẢNH BÁO THIẾU HÀNG & 2 CƠ CHẾ XỬ LÝ (NCC NỢ HÀNG HOẶC NCC NỢ TIỀN)       -->
        <!-- ================================================================================ -->
        <template x-if="receiptShortageCount > 0">
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-300 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-200 text-amber-800 flex items-center justify-center text-xl shrink-0 shadow-xs">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-black text-amber-950 uppercase tracking-wide">
                            Phát Hiện Nhập Không Đủ Số Lượng Đặt Hàng!
                        </div>
                        <p class="text-xs text-amber-900 mt-0.5">
                            Đơn hàng còn thiếu <span class="font-extrabold text-rose-700" x-text="receiptShortageCount"></span> sản phẩm.
                            Tổng giá trị số hàng còn thiếu: <span class="font-extrabold text-amber-950" x-text="formatMoney(receiptShortageValue)"></span>.
                        </p>
                        
                        <!-- Hiển thị tài chính nếu đã thanh toán / cấn trừ nợ -->
                        <template x-if="receiptExcessPaidAmount > 0">
                            <div class="mt-2.5 p-3 rounded-xl bg-white/90 border border-amber-300 text-xs shadow-2xs">
                                <div class="font-bold text-slate-800">
                                    💳 Bạn đã thanh toán & cấn trừ nợ: <span class="text-emerald-700 font-black" x-text="formatMoney(receiptTotalSettledAmount)"></span>
                                    <span class="text-slate-500 font-normal" x-show="parseFloat(orderData?.debt_deducted_amount) > 0" x-text="'(Đã trả ' + formatMoney(orderData.paid_amount) + ' + Cấn trừ nợ ' + formatMoney(orderData.debt_deducted_amount) + ')'"></span>
                                    | Tổng giá trị hàng thực nhận: <span class="text-slate-900 font-black" x-text="formatMoney(receiptTotalReceivedValue)"></span>
                                </div>
                                <div class="text-amber-950 font-black mt-1 text-xs">
                                    💰 Số tiền thanh toán vượt quá giá trị hàng thực nhận: <span class="text-sm underline text-amber-950 font-black" x-text="formatMoney(receiptExcessPaidAmount)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 2 CƠ CHẾ LỰA CHỌN -->
                <div class="pt-3 border-t border-amber-200/80 space-y-2.5">
                    <div class="text-xs font-black text-amber-900 uppercase tracking-wider">
                        Vui lòng chọn 1 trong 2 cơ chế xử lý số hàng thiếu:
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- CƠ CHẾ 1: NCC NỢ HÀNG -->
                        <label class="p-4 rounded-2xl border-2 bg-white cursor-pointer transition-all flex items-start gap-3 shadow-2xs"
                               :class="receiptData.shortage_resolution === 'supplier_owes_goods' ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/40' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="shortage_resolution" value="supplier_owes_goods" x-model="receiptData.shortage_resolution" class="w-4 h-4 text-sky-600 mt-1">
                            <div>
                                <div class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-boxes-packing text-sky-600 text-sm"></i>
                                    <span>Cơ chế 1: NCC nợ hàng (Giao bù đợt sau)</span>
                                </div>
                                <p class="text-[11.5px] text-slate-600 mt-1 leading-relaxed">
                                    NCC cam kết tiếp tục giao bù số hàng còn thiếu vào đợt sau. Đơn hàng chuyển sang trạng thái <strong>"Nhập một phần"</strong>. Số tiền đã thanh toán được giữ nguyên để chờ hàng về nhập tiếp.
                                </p>
                            </div>
                        </label>

                        <!-- CƠ CHẾ 2: NCC NỢ TIỀN -->
                        <label class="p-4 rounded-2xl border-2 bg-white cursor-pointer transition-all flex items-start gap-3 shadow-2xs"
                               :class="receiptData.shortage_resolution === 'supplier_owes_money' ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/40' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="shortage_resolution" value="supplier_owes_money" x-model="receiptData.shortage_resolution" class="w-4 h-4 text-emerald-600 mt-1">
                            <div>
                                <div class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-money-bill-transfer text-emerald-600 text-sm"></i>
                                    <span>Cơ chế 2: NCC không giao tiếp (Chốt sổ theo hàng thực nhận)</span>
                                </div>
                                <div class="text-[11.5px] text-slate-600 mt-1 leading-relaxed">
                                    <template x-if="receiptExcessPaidAmount > 0">
                                        <span>
                                            NCC không giao bù hàng. Bạn đã thanh toán & cấn trừ nợ tổng cộng <strong class="text-slate-900" x-text="formatMoney(receiptTotalSettledAmount)"></strong><span x-show="parseFloat(orderData?.debt_deducted_amount) > 0" x-text="' (Gồm ' + formatMoney(orderData.paid_amount) + ' đã trả + ' + formatMoney(orderData.debt_deducted_amount) + ' trừ nợ cũ)'"></span> vượt quá giá trị hàng nhận (<span x-text="formatMoney(receiptTotalReceivedValue)"></span>). 
                                            Số tiền thanh toán thừa <strong class="text-emerald-700 font-black" x-text="formatMoney(receiptExcessPaidAmount)"></strong> sẽ được chuyển thành <strong>Công nợ NCC nợ công ty</strong> (tự động khấu trừ vào đơn đặt mới sau này).
                                        </span>
                                    </template>
                                    <template x-if="receiptExcessPaidAmount === 0 && receiptTotalSettledAmount < receiptTotalReceivedValue">
                                        <span>
                                            NCC không giao bù hàng. Do bạn mới thanh toán/cấn trừ <strong class="text-slate-900" x-text="formatMoney(receiptTotalSettledAmount)"></strong> cho số hàng thực nhận <strong class="text-slate-900" x-text="formatMoney(receiptTotalReceivedValue)"></strong> nên không có tiền thanh toán thừa (thực tế còn phải thanh toán nốt cho NCC <strong class="text-amber-800 font-black" x-text="formatMoney(receiptTotalReceivedValue - receiptTotalSettledAmount)"></strong>). Đơn hàng sẽ kết thúc chốt theo hàng thực nhận.
                                        </span>
                                    </template>
                                    <template x-if="receiptExcessPaidAmount === 0 && receiptTotalSettledAmount >= receiptTotalReceivedValue">
                                        <span>
                                            NCC không giao bù hàng. Đơn hàng hoàn thành theo số lượng thực nhận và không phát sinh công nợ thừa thiếu.
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </template>

        <!-- Ghi chú phiếu nhập -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Ghi chú phiếu nhập kho</label>
            <input type="text" x-model="receiptData.receipt_notes" placeholder="Tên tài xế giao, biển số xe tải, tình trạng niêm phong bao bì..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-300 text-xs outline-none focus:border-blue-500">
        </div>

        <!-- Footer Bottom Bar -->
        <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-xs font-bold text-slate-600">
                Tổng giá trị hàng nhập kho đợt này: <span class="text-base font-black text-sky-700" x-text="formatMoney(calcBatchReceiptTotal())"></span>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ url('/supplier-orders') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer shadow-2xs">
                    Hủy bỏ
                </a>
                <button type="button" @click="submitReceipt()" :disabled="receiptSaving" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 cursor-pointer disabled:opacity-50 hover:scale-[1.02] transition-all">
                    <i class="fa-solid fa-spinner animate-spin" x-show="receiptSaving"></i>
                    <i class="fa-solid fa-box-archive" x-show="!receiptSaving"></i>
                    <span>Xác Nhận Nhập Kho & Đồng Bộ Tồn</span>
                </button>
            </div>
        </div>

    </div>

</div>

@php
    $receiptItems = $order->items->map(function($it) {
        $rem = (float)$it->remaining_quantity > 0 
            ? (float)$it->remaining_quantity 
            : max(0, (float)$it->ordered_quantity - (float)$it->received_quantity);
        return [
            'item_id'          => $it->id,
            'product_name'     => $it->product_name,
            'sku'              => $it->sku,
            'unit'             => $it->unit,
            'unit_price'       => (float)$it->unit_price,
            'ordered_quantity' => (float)$it->ordered_quantity,
            'received_before'  => (float)$it->received_quantity,
            'quantity'         => $rem, // Mặc định điền số lượng còn thiếu
            'batch_code'       => '',
            'expiry_date'      => '',
        ];
    })->values()->all();
@endphp

<!-- SCRIPT CHO TRANG TẠO PHIẾU NHẬP HÀNG -->
<script>
function supplierOrderReceiptApp() {
    return {
        receiptSaving: false,
        orderData: @json($order),
        receiptData: {
            warehouse_id: '{{ $order->warehouse_id ?: ($warehouses[0]->id ?? "") }}',
            receipt_notes: '',
            shortage_resolution: 'supplier_owes_goods',
            items: @json($receiptItems)
        },

        receiptShortageCount: 0,
        receiptShortageValue: 0,
        receiptTotalReceivedValue: 0,
        receiptTotalSettledAmount: 0,
        receiptExcessPaidAmount: 0,

        init() {
            this.recalcReceiptStatus();
        },

        fillFullReceipt() {
            this.receiptData.items.forEach(it => {
                it.quantity = Math.max(0, it.ordered_quantity - it.received_before);
            });
            this.recalcReceiptStatus();
        },

        clearReceiptQuantities() {
            this.receiptData.items.forEach(it => {
                it.quantity = 0;
            });
            this.recalcReceiptStatus();
        },

        recalcReceiptStatus() {
            let shortageCount = 0;
            let shortageValue = 0;
            let totalReceivedVal = 0;

            this.receiptData.items.forEach(it => {
                const qtyThisBatch = parseFloat(it.quantity) || 0;
                const totalAfter = it.received_before + qtyThisBatch;
                const remaining = Math.max(0, it.ordered_quantity - totalAfter);
                if (remaining > 0) {
                    shortageCount += remaining;
                    shortageValue += (remaining * it.unit_price);
                }
                totalReceivedVal += (totalAfter * it.unit_price);
            });

            this.receiptShortageCount = shortageCount;
            this.receiptShortageValue = shortageValue;
            this.receiptTotalReceivedValue = totalReceivedVal;

            const paidAmt = parseFloat(this.orderData?.paid_amount) || 0;
            const debtDeductedAmt = parseFloat(this.orderData?.debt_deducted_amount) || 0;
            const totalSettledAmt = paidAmt + debtDeductedAmt;
            this.receiptTotalSettledAmount = totalSettledAmt;
            this.receiptExcessPaidAmount = Math.max(0, totalSettledAmt - totalReceivedVal);
        },

        calcBatchReceiptTotal() {
            if (!this.receiptData || !this.receiptData.items) return 0;
            return this.receiptData.items.reduce((sum, it) => sum + ((parseFloat(it.quantity) || 0) * it.unit_price), 0);
        },

        submitReceipt() {
            const hasQty = this.receiptData.items.some(it => (parseFloat(it.quantity) || 0) > 0);
            if (!hasQty) {
                alert('Vui lòng nhập số lượng nhận lớn hơn 0 cho ít nhất 1 sản phẩm');
                return;
            }

            if (this.receiptShortageCount > 0 && !this.receiptData.shortage_resolution) {
                alert('Đơn hàng giao thiếu. Vui lòng chọn Cơ chế 1 (NCC nợ hàng) hoặc Cơ chế 2 (NCC nợ tiền)!');
                return;
            }

            this.receiptSaving = true;
            fetch(`{{ url('/api/supplier-orders') }}/${this.orderData.id}/receipt`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    warehouse_id: this.receiptData.warehouse_id,
                    receipt_notes: this.receiptData.receipt_notes,
                    shortage_resolution: this.receiptShortageCount > 0 ? this.receiptData.shortage_resolution : null,
                    received_items: this.receiptData.items.map(it => ({
                        item_id: it.item_id,
                        quantity: parseFloat(it.quantity) || 0,
                        batch_code: it.batch_code,
                        expiry_date: it.expiry_date
                    }))
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Tạo phiếu nhập kho thành công!');
                    window.location.href = '{{ url("/supplier-orders") }}';
                } else {
                    alert(data.message || 'Lỗi nhập kho');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối máy chủ');
            })
            .finally(() => {
                this.receiptSaving = false;
            });
        },

        handleGlobalKeydown(e) {
            const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);
            if (e.key === 'F9' || (e.ctrlKey && (e.key === 's' || e.key === 'S'))) {
                e.preventDefault();
                if (!this.receiptSaving) this.submitReceipt();
            } else if (e.key === 'Escape' && isInput) {
                document.activeElement.blur();
            }
        },

        formatMoney(amount) {
            const val = parseFloat(amount) || 0;
            return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
        }
    }
}
</script>
@endsection
