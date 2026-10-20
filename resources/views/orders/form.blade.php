@extends('layouts.app')

@section('title', ($order ? 'Chỉnh Sửa Đơn Hàng ' . $order->order_code : 'Đơn Hàng ERP Form Standard') . ' | ERP Đại Sơn')

@section('content')
@php
    $orderTier = ($order && !empty($order->dealer_tier)) ? $order->dealer_tier : ($order?->dealer?->tier ?: 'Nguyên Giá');
    $initialItems = [];
    if ($order && $order->items && count($order->items) > 0) {
        foreach ($order->items as $it) {
            $qty = (float) $it->quantity;
            $giftQty = (float) ($it->gift_quantity ?? 0);
            $price = (float) $it->unit_price;
            $amt = (float) $it->amount;
            $isGift = ($price == 0 && (str_contains($it->applied_tier_policy ?? '', 'TẶNG KÈM') || str_contains($it->applied_tier_policy ?? '', 'Khuyến mại')));
            $itTier = ($it->applied_tier_policy && $it->applied_tier_policy !== 'Theo phiếu') ? $it->applied_tier_policy : $orderTier;
            $vatPercent = ($it->product && $it->product->vat_rate !== null) ? (float) $it->product->vat_rate : 10.0;
            $vatAmt = round(($qty * $price) * ($vatPercent / 100));
            $initialItems[] = [
                'id' => (string) $it->id,
                'itemId' => (string) ($it->product_id ?? ''),
                'product_id' => $it->product_id,
                'sku' => $it->sku,
                'name' => $it->name,
                'searchQuery' => '[' . $it->sku . '] ' . $it->name,
                'unit' => $it->unit ?? '',
                'quantity' => $qty > 0 ? $qty : 1,
                'gift_quantity' => $giftQty,
                'unitPrice' => $price,
                'originalPrice' => (float) ($it->original_price ?? $price),
                'costPrice' => (float) ($it->cost_price ?? 0),
                'discount_percent' => 0,
                'discount_amount' => 0,
                'vat_percent' => $vatPercent,
                'vat_amount' => $vatAmt,
                'appliedTierPolicy' => $itTier,
                'price_type' => $itTier,
                'amount' => $amt > 0 ? $amt : round($qty * $price * (1 + $vatPercent / 100)),
                'picked_qty' => (float) ($it->picked_qty ?? 0),
                'shortage_reason' => $it->shortage_reason ?? '',
                'item_status' => $it->item_status ?? '',
                'item_id_db' => (string) $it->id,
                'checked' => true,
                'isPromotionalGift' => $isGift,
            ];
        }
    } else {
        // Start with 1 clean default row like in standard ERP form
        $initialItems[] = [
            'id' => '',
            'itemId' => '',
            'product_id' => '',
            'sku' => '',
            'name' => '',
            'searchQuery' => '',
            'unit' => '',
            'quantity' => 1,
            'gift_quantity' => 0,
            'unitPrice' => 0,
            'originalPrice' => 0,
            'costPrice' => 0,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'vat_percent' => 10,
            'vat_amount' => 0,
            'appliedTierPolicy' => $orderTier,
            'price_type' => $orderTier,
            'amount' => 0,
            'checked' => true,
        ];
    }
@endphp

<div x-data="orderFormApp()" x-init="init()" @keydown.window="handleGlobalKeydown($event)" class="space-y-4 pb-24">

    <!-- 1. Top Bar / Tabs (Hỗ trợ mở nhiều phiếu tạo đơn đồng thời) -->
    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-100/80 -mx-4 sm:-mx-6 -mt-4 px-4 sm:px-6 py-2 text-xs overflow-x-auto">
        <div class="flex items-center gap-1.5 min-w-0">
            <!-- Danh sách các tab phiếu -->
            <template x-for="(t, idx) in tickets" :key="t.id">
                <div @click="switchTicket(idx)" 
                     :class="activeTicketIndex === idx ? 'bg-white text-sky-700 font-bold border-sky-400 shadow-xs ring-1 ring-sky-300' : 'bg-slate-200/80 text-slate-600 hover:bg-slate-200 border-slate-300 font-semibold'"
                     class="inline-flex items-center gap-2 px-3 py-1.5 border rounded-lg cursor-pointer transition-all shrink-0 max-w-[200px] select-none">
                    <i class="fa-solid fa-file-invoice text-[10px]" :class="activeTicketIndex === idx ? 'text-sky-600' : 'text-slate-400'"></i>
                    <span class="truncate" x-text="t.formData.customer_name ? (t.formData.customer_name.length > 18 ? t.formData.customer_name.substring(0, 18) + '...' : t.formData.customer_name) : t.title"></span>
                    <button type="button" @click.stop="closeTicket(idx)" class="text-slate-400 hover:text-rose-600 font-bold rounded p-0.5 hover:bg-slate-100 transition-colors ml-1" title="Đóng phiếu này">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                </div>
            </template>

            <!-- Nút Thêm phiếu mới (F2) -->
            <template x-if="!isReadonly">
                <button type="button" @click="addNewTicket()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg transition-colors shadow-2xs cursor-pointer shrink-0 ml-1" title="Thêm phiếu tạo đơn mới (F2)">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Thêm phiếu (F2)</span>
                </button>
            </template>
        </div>

        <div class="shrink-0 ml-3">
            <button type="button" @click="closeTicket(activeTicketIndex)" class="text-rose-600 hover:text-rose-700 font-bold flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
                <span>Đóng phiếu (Esc)</span>
            </button>
        </div>
    </div>

    <!-- Alert / Toast -->
    <div x-show="errorMessage" x-cloak class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-bold text-rose-700 flex items-center justify-between shadow-xs">
        <span x-text="errorMessage"></span>
        <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
    </div>

    @if(request('readonly') || request('mode') === 'readonly')
        <div class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl text-xs font-bold text-amber-900 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-eye text-amber-600"></i>
                <span>Chế độ chỉ đọc: Bạn đang xem chi tiết chứng từ (không chỉnh sửa).</span>
            </div>
            @if($order)
                <a href="{{ url('/orders/' . $order->id . '/edit') }}" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-all shadow-2xs">
                    <i class="fa-solid fa-pen-to-square mr-1"></i> Bổ sung / Chỉnh sửa
                </a>
            @endif
        </div>
    @endif

    <style>
        fieldset[disabled] {
            pointer-events: none !important;
        }
        fieldset[disabled] input, 
        fieldset[disabled] select, 
        fieldset[disabled] textarea {
            background-color: #f8fafc !important;
            color: #0f172a !important;
            cursor: not-allowed !important;
            border-color: #cbd5e1 !important;
            opacity: 0.95 !important;
            user-select: text !important;
        }
        fieldset[disabled] button {
            display: none !important;
        }
    </style>

    <!-- 2. Main ERP Form Standard Container (Locked strictly in readonly mode) -->
    <fieldset :disabled="isReadonly" class="border-0 p-0 m-0 space-y-6 block">
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">

        <!-- Title Block -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Đơn hàng</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">ERP Form Standard</span>
                    <span x-show="formData.order_type === 'backorder' || formData.status === 'Giao sau'" class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300">📋 Đơn giao sau</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold" :class="formData.status === 'Báo giá' ? 'bg-amber-100 text-amber-800 border border-amber-300' : (formData.status === 'Giao sau' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300')" x-text="formData.status"></span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Quản lý đơn hàng theo quy trình: Báo giá &bull; Chốt đơn &bull; Soạn hàng &bull; Đang vận chuyển &bull; Đã giao &bull; Hủy
                </p>
            </div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5 shrink-0">
                <span>Phím tắt:</span>
                <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F2</kbd>
                <span>Thêm phiếu</span>
                <span class="text-slate-300 mx-1">|</span>
                <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">Esc</kbd>
                <span>Đóng phiếu</span>
            </div>
        </div>

        <!-- 3. Form Information: Row 1 & Row 2 -->
        <div class="space-y-4">
            <!-- Row 1: 6 columns (lg:grid-cols-12) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-12 gap-4">
                <!-- Mã chứng từ -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">MÃ CHỨNG TỪ</label>
                    <input type="text" readonly :value="formData.order_code || 'TỰ SINH KHI LƯU'" class="w-full px-3 py-2 bg-slate-100/80 border border-slate-200 rounded-xl text-xs font-bold text-slate-500 cursor-not-allowed select-none">
                </div>

                <!-- Ngày chứng từ -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">NGÀY CHỨNG TỪ</label>
                    <div class="relative">
                        <input type="date" x-model="formData.order_date" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none">
                    </div>
                </div>

                <!-- Ngày soạn hàng -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">NGÀY SOẠN HÀNG</label>
                    <div class="relative">
                        <input type="date" x-model="formData.picking_date" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none">
                    </div>
                </div>

                <!-- Ngày giao hàng -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">NGÀY GIAO HÀNG</label>
                    <div class="relative">
                        <input type="date" x-model="formData.delivery_date" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none">
                    </div>
                </div>

                <!-- Loại đơn hàng -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">LOẠI ĐƠN HÀNG</label>
                    <select x-model="formData.order_type" @change="handleOrderTypeChange()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none">
                        <option value="normal">🛒 Bình thường</option>
                        <option value="project">📦 Công trình / dự án</option>
                        <option value="wholesale">🚚 Xuất đại lý</option>
                        <option value="backorder">📋 Đơn giao sau (nợ hàng)</option>
                    </select>
                </div>

                <!-- Tình trạng đơn -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">TÌNH TRẠNG ĐƠN</label>
                    <template x-if="formData.order_type === 'backorder' || formData.status === 'Giao sau'">
                        <div class="py-2 px-3 bg-amber-600 text-white font-black rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <span>Giao sau</span>
                        </div>
                    </template>
                    <template x-if="formData.order_type !== 'backorder' && formData.status !== 'Giao sau'">
                        <div>
                            <template x-if="['Báo giá', 'Chốt đơn'].includes(formData.status)">
                                <div class="grid grid-cols-2 gap-1.5">
                                    <button type="button" @click="formData.status = 'Báo giá'" :class="formData.status === 'Báo giá' ? 'bg-amber-500 text-white font-bold ring-2 ring-amber-300 shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'" class="py-2 px-1 rounded-xl text-xs transition-all flex items-center justify-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-receipt text-[10px]"></i>
                                        <span>Báo giá</span>
                                    </button>
                                    <button type="button" @click="formData.status = 'Chốt đơn'" :class="formData.status === 'Chốt đơn' ? 'bg-sky-600 text-white font-bold ring-2 ring-sky-300 shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'" class="py-2 px-1 rounded-xl text-xs transition-all flex items-center justify-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Chốt đơn</span>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!['Báo giá', 'Chốt đơn'].includes(formData.status)">
                                <select x-model="formData.status" class="w-full px-2 py-2 bg-purple-50 border border-purple-300 font-bold text-purple-900 rounded-xl text-xs focus:border-sky-500 focus:outline-none cursor-pointer">
                                    <option value="Báo giá">📋 Báo giá</option>
                                    <option value="Chốt đơn">🤝 Chốt đơn</option>
                                    <option value="Giao sau">📋 Giao sau</option>
                                    <option value="Soạn hàng">📦 Soạn hàng</option>
                                    <option value="Kiểm hàng">🛡️ Kiểm hàng</option>
                                    <option value="Đang vận chuyển">🚚 Đang vận chuyển</option>
                                    <option value="Đã giao">✅ Đã giao</option>
                                    <option value="Hủy">❌ Hủy đơn</option>
                                </select>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Row 2: 3 columns (Top aligned, labels & inputs perfectly level) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                <!-- Khách hàng (Chọn từ danh sách đại lý / khách hàng kèm phím mũi tên Lên/Xuống + Enter) -->
                <div class="md:col-span-6 lg:col-span-4" 
                     x-data="{ 
                         openDealerDropdown: false, 
                         dealerSearch: '', 
                         highlightedDealerIndex: 0,
                         scrollDealerIntoView(idx) {
                             this.$nextTick(() => {
                                 const el = document.getElementById('dealer-opt-' + idx);
                                 if (el) el.scrollIntoView({ block: 'nearest' });
                             });
                         }
                     }" 
                     @click.outside="openDealerDropdown = false">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-500 tracking-wide uppercase">KHÁCH HÀNG / ĐẠI LÝ</label>
                        <span class="text-[11px] font-medium" :class="formData.customer_name ? 'text-sky-600 font-semibold' : 'text-slate-400'" x-text="formData.customer_name ? (formData.customer_phone ? formData.customer_name + ' (' + formData.customer_phone + ')' : formData.customer_name) : 'Chưa chọn khách hàng'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            <input type="text"
                                   autocomplete="off"
                                   autocorrect="off"
                                   spellcheck="false"
                                   x-ref="customerSearchInput"
                                   :value="formData.customer_name"
                                   @focus="openDealerDropdown = true; dealerSearch = ''; highlightedDealerIndex = 0"
                                   @input="dealerSearch = $event.target.value; openDealerDropdown = true; highlightedDealerIndex = 0"
                                   @keydown.down.prevent="
                                       openDealerDropdown = true;
                                       const dList = filteredDealers(dealerSearch);
                                       highlightedDealerIndex = Math.min(highlightedDealerIndex + 1, dList.length - 1);
                                       scrollDealerIntoView(highlightedDealerIndex);
                                   "
                                   @keydown.up.prevent="
                                       highlightedDealerIndex = Math.max(highlightedDealerIndex - 1, 0);
                                       scrollDealerIntoView(highlightedDealerIndex);
                                   "
                                   @keydown.enter.prevent="
                                       if (openDealerDropdown) {
                                           const dList = filteredDealers(dealerSearch);
                                           if (dList.length > 0 && dList[highlightedDealerIndex]) {
                                               selectDealer(dList[highlightedDealerIndex]);
                                               openDealerDropdown = false;
                                               $nextTick(() => {
                                                   const pInput = document.getElementById('row-product-input-0');
                                                   if (pInput) pInput.focus();
                                               });
                                           }
                                       }
                                   "
                                   @keydown.escape.prevent="openDealerDropdown = false"
                                   placeholder="🔍 Bấm chọn hoặc gõ tìm khách hàng, đại lý, SĐT (phím ↑ ↓ Enter)..." 
                                   class="w-full pl-9 pr-14 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none cursor-pointer shadow-2xs hover:border-slate-400 transition-colors">
                            
                            <!-- Nút xóa & mũi tên dropdown -->
                            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                                <button type="button" x-show="formData.customer_name" @click.stop="clearCustomer()" class="text-slate-400 hover:text-rose-500 p-0.5 cursor-pointer" title="Hủy chọn">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                                <button type="button" @click.stop="openDealerDropdown = !openDealerDropdown" class="text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer">
                                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="openDealerDropdown ? 'rotate-180 text-sky-600' : ''"></i>
                                </button>
                            </div>

                            <!-- Menu Dropdown hiển thị danh sách khách hàng -->
                            <div x-show="openDealerDropdown" x-cloak class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-64 overflow-y-auto py-1 text-xs">
                                <div class="px-3 py-1.5 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                    <span>Chọn khách hàng / đại lý có sẵn ({{ count($dealers) }})</span>
                                    <span class="text-sky-600 font-normal lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                </div>
                                <template x-for="(d, dIdx) in filteredDealers(dealerSearch)" :key="d.id">
                                    <div :id="'dealer-opt-' + dIdx"
                                         @click="selectDealer(d); openDealerDropdown = false; $nextTick(() => { const p = document.getElementById('row-product-input-0'); if(p) p.focus(); })" 
                                         @mouseenter="highlightedDealerIndex = dIdx"
                                         class="px-3 py-2.5 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                         :class="highlightedDealerIndex === dIdx ? 'bg-sky-100 text-sky-950 font-bold ring-1 ring-sky-300' : (formData.dealer_id == d.id ? 'bg-sky-50 font-bold text-sky-700' : 'hover:bg-slate-50')">
                                        <div class="min-w-0 pr-2">
                                            <div class="font-bold text-slate-900 truncate" x-text="d.name"></div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                <span x-show="d.phone" class="font-mono text-slate-600" x-text="d.phone"></span>
                                                <span x-show="d.address" class="text-slate-400 truncate max-w-[220px]" x-text="'· ' + d.address"></span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800" x-text="d.tier || 'Cấp 1'"></span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredDealers(dealerSearch).length === 0" class="px-3 py-6 text-center text-slate-400 italic">
                                    Không tìm thấy khách hàng. Bấm nút [+] để thêm mới.
                                </div>
                            </div>
                        </div>

                        <!-- Nút Thêm mới khách hàng nhanh -->
                        <button type="button" @click="showAddDealer = true" class="w-9 h-9 bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 rounded-xl flex items-center justify-center font-bold text-base cursor-pointer shrink-0 transition-colors shadow-2xs" title="Thêm khách hàng / đại lý mới">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Vị trí giao hàng -->
                <div class="md:col-span-6 lg:col-span-5">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-500 tracking-wide uppercase">VỊ TRÍ GIAO HÀNG</label>
                        <span class="text-[11px] text-slate-400 font-medium" x-show="formData.customer_address" x-text="'Địa chỉ nhận hàng'"></span>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-location-dot absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                        <input type="text" 
                               x-model="formData.customer_address" 
                               placeholder="Địa chỉ giao hàng, vị trí công trình, số nhà, tuyến giao..." 
                               class="w-full pl-9 pr-8 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">
                        <button type="button" 
                                x-show="formData.customer_address" 
                                @click="formData.customer_address = ''" 
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-0.5 cursor-pointer" 
                                title="Xóa địa chỉ">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Kho dự kiến xuất -->
                <div class="md:col-span-12 lg:col-span-3">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-500 tracking-wide uppercase">KHO DỰ KIẾN XUẤT</label>
                        @if($isDriverUser ?? false)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                <i class="fa-solid fa-truck text-[9px] text-emerald-600"></i>
                                <span>Kho trên xe của bạn</span>
                            </span>
                        @endif
                    </div>
                    <select x-model="formData.warehouse" 
                            @change="onWarehouseChange()" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none cursor-pointer shadow-2xs hover:border-slate-400 transition-colors {{ ($isDriverUser ?? false) ? 'bg-slate-100 cursor-not-allowed text-slate-500 opacity-90' : '' }}"
                            {{ ($isDriverUser ?? false) ? 'disabled' : '' }}>
                        <option value="">-- Chọn kho xuất hàng --</option>
                        @foreach($warehouses as $wh)
                            @if(empty($wh->is_holding_warehouse) && !str_starts_with($wh->code ?? '', 'KHO-OM-') && !str_contains($wh->name ?? '', 'Kho Hàng Ôm'))
                                <option value="{{ $wh->name }}">
                                    {{ $wh->name }} {{ !empty($wh->code) ? "({$wh->code})" : '' }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @if($isDriverUser ?? false)
                        <p class="text-[10.5px] text-emerald-700 mt-1 flex items-center gap-1 font-semibold">
                            <i class="fa-solid fa-lock text-[9.5px]"></i>
                            <span>Lái xe chỉ được xuất bán từ kho trên xe của mình.</span>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Badge / Card hiển thị công nợ & hạn mức tín dụng khách hàng -->
            <div x-show="getSelectedDealer()" x-cloak class="p-2.5 rounded-xl border text-[11px] transition-all"
                 :class="isDebtOverLimit() ? 'bg-rose-50 border-rose-300 text-rose-900 ring-1 ring-rose-200 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700'">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="flex items-center gap-1.5 font-semibold">
                            <i :class="isDebtOverLimit() ? 'fa-solid fa-triangle-exclamation text-rose-600' : 'fa-solid fa-wallet text-sky-600'"></i>
                            <span>Khách: <b class="text-slate-900" x-text="getSelectedDealer()?.name"></b></span>
                        </span>
                        <span class="text-slate-300">|</span>
                        <span>Nợ hiện tại: <b class="font-mono text-xs font-bold text-slate-900" x-text="formatNumber(getSelectedDealer()?.current_debt || 0) + ' đ'"></b></span>
                        <span class="text-slate-300">|</span>
                        <span>Hạn mức: 
                            <b class="font-mono font-bold" :class="Number(getSelectedDealer()?.credit_limit) > 0 ? 'text-slate-800' : 'text-emerald-600'" x-text="Number(getSelectedDealer()?.credit_limit) > 0 ? (formatNumber(getSelectedDealer()?.credit_limit) + ' đ') : 'Không giới hạn'"></b>
                        </span>
                        <span class="text-slate-300">|</span>
                        <span>Sau đơn này: 
                            <b class="font-mono font-bold" :class="isDebtOverLimit() ? 'text-rose-700 font-black' : 'text-sky-700'" x-text="formatNumber(getProjectedDebt()) + ' đ'"></b>
                        </span>
                    </div>
                    <div class="text-right shrink-0">
                        <template x-if="isDebtOverLimit()">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-rose-600 text-white font-black text-[10px] tracking-wide animate-pulse">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>VƯỢT HẠN MỨC: +<span x-text="formatNumber(getDebtExcessAmount()) + ' đ'"></span></span>
                            </span>
                        </template>
                        <template x-if="!isDebtOverLimit() && Number(getSelectedDealer()?.credit_limit) > 0">
                            <span class="text-emerald-700 font-bold text-[10px] bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-200">
                                Còn được nợ: +<span class="font-mono" x-text="formatNumber(Math.max(0, Number(getSelectedDealer()?.credit_limit) - getProjectedDebt())) + ' đ'"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Product Line Items Table (Chi Tiết Mặt Hàng) -->
        <div class="space-y-3">
            <!-- Banners cảnh báo tổng thể (Vượt hạn mức công nợ & Vượt tồn kho) -->
            <div class="space-y-2" x-show="isDebtOverLimit() || getOverStockItems().length > 0" x-cloak>
                <!-- Cảnh báo công nợ -->
                <div x-show="isDebtOverLimit()" class="p-3 bg-rose-50 border border-rose-300 rounded-xl flex items-start gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 font-bold">
                        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    </div>
                    <div class="text-xs text-rose-900 leading-relaxed flex-1">
                        <div class="font-black uppercase tracking-wider text-rose-700 text-[11px] mb-0.5 flex items-center gap-1.5">
                            <span>CẢNH BÁO VƯỢT QUÁ HẠN MỨC CÔNG NỢ</span>
                            <span class="px-1.5 py-0.5 rounded bg-rose-200 text-rose-800 text-[10px] font-mono font-bold" x-text="'+' + formatNumber(getDebtExcessAmount()) + ' đ'"></span>
                        </div>
                        <span>Khách hàng <strong class="font-bold text-rose-950" x-text="formData.customer_name"></strong> có hạn mức công nợ là <b class="font-mono font-bold" x-text="formatNumber(getSelectedDealer()?.credit_limit || 0) + ' đ'"></b>. Nợ hiện tại: <b class="font-mono" x-text="formatNumber(getSelectedDealer()?.current_debt || 0) + ' đ'"></b>. Sau đơn này (giá trị <b class="font-mono" x-text="formatNumber(finalTotal) + ' đ'"></b>), tổng nợ dự kiến là <b class="font-mono font-black text-rose-700" x-text="formatNumber(getProjectedDebt()) + ' đ'"></b> (<span class="font-bold underline">vượt hạn mức +<span x-text="formatNumber(getDebtExcessAmount()) + ' đ'"></span></span>).</span>
                    </div>
                </div>

                <!-- Cảnh báo tồn kho -->
                <div x-show="getOverStockItems().length > 0" class="p-3 bg-amber-50 border border-amber-300 rounded-xl flex items-start gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold">
                        <i class="fa-solid fa-boxes-stacked text-sm"></i>
                    </div>
                    <div class="text-xs text-amber-900 leading-relaxed flex-1">
                        <div class="font-black uppercase tracking-wider text-amber-800 text-[11px] mb-0.5 flex items-center gap-1.5">
                            <span>CẢNH BÁO THIẾU TỒN KHO TẠI [<span x-text="formData.warehouse || 'Kho xuất'"></span>]</span>
                            <span class="px-1.5 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] font-bold" x-text="getOverStockItems().length + ' sản phẩm vượt tồn kho này'"></span>
                        </div>
                        <div>
                            Có <strong class="font-bold text-amber-950" x-text="getOverStockItems().length"></strong> sản phẩm đặt vượt quá số lượng tồn khả dụng tại kho này:
                            <template x-for="(it, idx) in getOverStockItems()" :key="idx">
                                <span class="inline-block mr-2 my-0.5 px-2 py-0.5 bg-amber-100/80 border border-amber-300 rounded font-medium text-[11px]">
                                    <span class="font-bold text-slate-800" x-text="it.name || it.sku"></span>: 
                                    đặt <span class="font-bold text-rose-600" x-text="it.quantity + ' ' + (it.unit || '')"></span> 
                                    (tại kho còn <span class="font-bold text-slate-700" x-text="formatNumber(getItemAvailableStockInUnit(it)) + ' ' + (it.unit || '')"></span> &rarr; 
                                    <span class="text-rose-700 font-bold">thiếu <span x-text="formatNumber(getItemOverStockQty(it))"></span></span>, 
                                    <span class="text-slate-500 text-[10px]">tổng toàn bộ kho: <span class="font-semibold text-slate-700" x-text="formatNumber(getProduct(it.product_id)?.stock_quantity || 0)"></span></span>)
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BANNER NHẮC GỢI Ý GỘP ĐƠN GIAO SAU (NỢ HÀNG) CỦA KHÁCH HÀNG -->
            <template x-if="pendingMergeOrders && pendingMergeOrders.length > 0 && !dismissedPendingAlert">
                <div class="mb-4 bg-gradient-to-r from-indigo-50 via-purple-50 to-sky-50 border-2 border-indigo-300 rounded-2xl p-4 shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4 animate-in fade-in duration-200">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md text-lg">
                            <i class="fa-solid fa-code-merge"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-600 text-white shadow-2xs">
                                    Gợi ý gộp đơn
                                </span>
                                <span class="font-extrabold text-indigo-950 text-sm">
                                    Khách hàng có <span class="text-indigo-600 underline font-black text-base" x-text="pendingMergeOrders.length"></span> đơn giao sau (nợ hàng) chưa hoàn thành!
                                </span>
                            </div>
                            <div class="text-xs text-indigo-900 mt-1.5 flex flex-wrap items-center gap-2">
                                <template x-for="pOrd in pendingMergeOrders" :key="pOrd.id">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold shadow-2xs bg-amber-100 text-amber-900 border border-amber-300">
                                        <i class="fa-solid fa-clipboard-list text-amber-600"></i>
                                        <span class="font-bold font-mono" x-text="pOrd.order_code"></span>
                                        <span x-text="'(' + pOrd.type_label + ' - ' + pOrd.items_count + ' SP)'"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto shrink-0">
                        <button type="button" 
                                @click="showMergeModal = true" 
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Xem & Gộp đơn</span>
                        </button>
                        <button type="button" 
                                @click="mergeAllPendingOrders()" 
                                class="px-3.5 py-2 bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-300 font-bold text-xs rounded-xl shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-circle-check text-indigo-600"></i>
                            <span>Gộp tất cả</span>
                        </button>
                        <button type="button" 
                                @click="dismissedPendingAlert = true" 
                                class="p-2 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer transition-colors" title="Ẩn thông báo này">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </div>
            </template>

            <!-- BANNER XỬ LÝ THIẾU HÀNG TỔNG (Hiện khi đang ở giai đoạn soạn hàng và có ít nhất 1 sản phẩm bốc thiếu) -->
            <template x-if="isPickingStage() && getShortageItems().length > 0">
                <div class="mb-4 bg-gradient-to-r from-rose-50 via-amber-50 to-rose-50 border-2 border-rose-300 rounded-2xl p-4 shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4 animate-in fade-in duration-200">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md text-lg animate-bounce">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white shadow-2xs">
                                    Kho Báo Thiếu Hàng
                                </span>
                                <span class="font-extrabold text-rose-900 text-sm">
                                    Có <span class="text-rose-600 underline font-black text-base" x-text="getShortageItems().length"></span> sản phẩm bốc thiếu!
                                </span>
                            </div>
                            <div class="text-xs text-rose-700 mt-1 leading-relaxed">
                                Bạn có thể <strong>cắt tất cả theo SL bốc</strong> hoặc <strong>tách tạo đơn giao sau (nợ hàng)</strong> cho các mặt hàng thiếu.
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto shrink-0">
                        <!-- Nút Cắt Tổng -->
                        <button type="button" 
                                @click="cutAllToPickedQty()" 
                                class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-rose-50 text-rose-700 font-bold text-xs rounded-xl border-2 border-rose-300 shadow-sm hover:border-rose-400 active:scale-95 transition-all cursor-pointer"
                                title="Cắt giảm SL các sản phẩm thiếu theo đúng số lượng kho thực tế đã bốc">
                            <i class="fa-solid fa-scissors text-rose-600"></i>
                            <span>Cắt tất cả theo SL bốc (<span x-text="getShortageItems().length"></span>)</span>
                        </button>
                        <!-- Nút Tạo Đơn Giao Sau Tổng -->
                        <button type="button" 
                                @click="createBackOrderAll()" 
                                :disabled="backorderSaving"
                                class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-3.5 py-2.5 bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-700 hover:to-amber-700 text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-lg active:scale-95 transition-all cursor-pointer disabled:opacity-50"
                                title="Cắt đơn này theo số bốc và tự động tạo đơn giao sau (nợ hàng) cho toàn bộ phần thiếu">
                            <svg x-show="backorderSaving" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <i class="fa-solid fa-boxes-packing" x-show="!backorderSaving"></i>
                            <span>Tạo đơn giao sau tổng (<span x-text="getShortageItems().length"></span> SP)</span>
                        </button>
                    </div>
                </div>
            </template>

            <div class="rounded-xl border border-slate-200">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                            <th class="py-2.5 px-3 text-center w-14">
                                <div class="flex items-center justify-center gap-1">
                                    <input type="checkbox" 
                                           :checked="items.length > 0 && items.every(it => it.checked !== false)" 
                                           @change="const val = $event.target.checked; items.forEach(it => it.checked = val); calculateTotals();"
                                           class="rounded text-sky-600 focus:ring-0 cursor-pointer">
                                    <span>STT</span>
                                </div>
                            </th>
                            <th class="py-2.5 px-3 font-bold min-w-[280px]">TÊN / MÃ SP</th>
                            <th class="py-2.5 px-2 text-center w-20">ĐVT</th>
                            <th class="py-2.5 px-2 text-right w-28">ĐƠN GIÁ</th>
                            <th class="py-2.5 px-2 text-center w-16">SL</th>
                            <!-- Cột Số Lượng Tặng -->
                            <th class="py-2.5 px-2 text-center w-24 bg-emerald-50/70 border-x border-slate-200">
                                <div class="text-emerald-800 font-bold">SL TẶNG</div>
                                <div class="text-[9px] text-emerald-600 font-normal lowercase tracking-normal">(quà tặng)</div>
                            </th>
                            <!-- Tiền chiết khấu cột -->
                            <th class="py-2 px-2 text-center w-52 bg-sky-50/60 border-r border-slate-200">
                                <div class="text-[11px] font-bold text-slate-700 uppercase">TIỀN CHIẾT KHẤU</div>
                                <div class="mt-1 flex items-center justify-center">
                                    <div class="inline-flex items-center gap-1 bg-white border border-sky-300 rounded px-1.5 py-0.5 shadow-2xs hover:border-sky-400 focus-within:ring-1 focus-within:ring-sky-400 transition-all">
                                        <span class="text-[10px] text-slate-500 font-semibold tracking-normal lowercase">tất cả:</span>
                                        <input type="number" min="0" max="100" step="any"
                                               x-model.number="bulkDiscountPercent"
                                               @input="applyBulkDiscountPercent()"
                                               @focus="$event.target.select()"
                                               placeholder="%"
                                               title="Nhập % chiết khấu để áp dụng cho toàn bộ sản phẩm"
                                               class="w-10 px-1 py-0.5 bg-sky-50/60 focus:bg-white border border-sky-200 rounded text-center text-xs font-bold text-sky-700 focus:outline-none">
                                        <span class="text-[10px] text-sky-700 font-bold">%</span>
                                    </div>
                                </div>
                            </th>
                            <!-- VAT cột -->
                            <th class="py-2.5 px-2 text-center w-36 bg-sky-50/60 border-r border-slate-200">
                                <div>VAT</div>
                                <div class="text-[9px] text-slate-400 font-normal lowercase tracking-normal">(ăn theo sp chọn)</div>
                            </th>
                            <!-- Cột SL Bốc: chỉ hiện khi đơn đang ở giai đoạn soạn hàng -->
                            <template x-if="isPickingStage()">
                                <th class="py-2.5 px-2 text-center w-24 bg-orange-50/80 border-x border-slate-200">
                                    <div class="text-orange-700 font-bold">SL BỐC</div>
                                    <div class="text-[9px] text-orange-500 font-normal lowercase tracking-normal">(kho đã bốc)</div>
                                </th>
                            </template>
                            <th class="py-2.5 px-3 text-right w-36">THÀNH TIỀN</th>
                            <th class="py-2.5 px-2 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- STT + Checkbox -->
                                <td class="py-2 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <input type="checkbox" x-model="item.checked" @change="calculateTotals()" class="rounded text-sky-600 focus:ring-0 cursor-pointer">
                                        <span class="font-bold text-slate-700" x-text="index + 1"></span>
                                    </div>
                                </td>

                                <!-- Tên / Mã SP Input với Menu gợi ý điều khiển bằng phím mũi tên Lên/Xuống + Enter -->
                                <td class="py-2 px-3 relative">
                                    <!-- Nếu là hàng tặng kèm khuyến mại -->
                                    <template x-if="item.isPromotionalGift">
                                        <div class="py-1">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-300 font-black text-[10px] tracking-wide shrink-0">
                                                    <i class="fa-solid fa-gift text-emerald-600"></i>
                                                    <span>TẶNG KÈM 0đ</span>
                                                </span>
                                                <span class="font-bold text-slate-900 text-xs" x-text="item.name"></span>
                                            </div>
                                            <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-2">
                                                <span class="font-mono text-sky-600 font-semibold" x-text="'[' + item.sku + ']'"></span>
                                                <span>&bull;</span>
                                                <span class="text-amber-700 font-bold" x-text="item.appliedTierPolicy"></span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Nếu là hàng bán thông thường -->
                                    <template x-if="!item.isPromotionalGift">
                                    <div class="relative" @click.outside="if (activeProdDropdownIndex === index) activeProdDropdownIndex = null">
                                        <input type="text"
                                               autocomplete="off"
                                               autocorrect="off"
                                               spellcheck="false"
                                               :id="'row-product-input-' + index"
                                               x-model="item.searchQuery" 
                                               @focus="activeProdDropdownIndex = index; prodHighlightIndex = 0"
                                               @input="activeProdDropdownIndex = index; prodHighlightIndex = 0"
                                               @keydown.down.prevent="
                                                   activeProdDropdownIndex = index;
                                                   const prods = getFilteredProducts(item.searchQuery, index);
                                                   prodHighlightIndex = Math.min(prodHighlightIndex + 1, prods.length - 1);
                                                   scrollProdIntoView(index, prodHighlightIndex);
                                               "
                                               @keydown.up.prevent="
                                                   prodHighlightIndex = Math.max(prodHighlightIndex - 1, 0);
                                                   scrollProdIntoView(index, prodHighlightIndex);
                                               "
                                               @keydown.enter.prevent="
                                                   if (activeProdDropdownIndex === index) {
                                                       const prods = getFilteredProducts(item.searchQuery, index);
                                                       if (prods.length > 0 && prods[prodHighlightIndex]) {
                                                           selectProductForRow(index, prods[prodHighlightIndex]);
                                                           activeProdDropdownIndex = null;
                                                           $nextTick(() => {
                                                               const qtyInput = document.getElementById('row-qty-input-' + index);
                                                               if (qtyInput) {
                                                                   qtyInput.focus();
                                                                   qtyInput.select();
                                                               }
                                                           });
                                                       }
                                                   }
                                               "
                                               @keydown.escape.prevent="activeProdDropdownIndex = null"
                                               placeholder="Tìm mã hoặc tên sản phẩm (phím ↑ ↓ Enter)..." 
                                               class="product-search-input w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">

                                        <!-- Menu Dropdown gợi ý sản phẩm -->
                                        <div x-show="activeProdDropdownIndex === index && getFilteredProducts(item.searchQuery, index).length > 0" 
                                             x-cloak 
                                             class="absolute left-0 top-full mt-1 w-[460px] max-w-[90vw] z-50 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-60 overflow-y-auto py-1 text-xs">
                                            <div class="px-3 py-1 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                                <span>Gợi ý sản phẩm (<span x-text="getFilteredProducts(item.searchQuery, index).length"></span>)</span>
                                                <span class="text-sky-600 font-normal lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                            </div>
                                            <template x-for="(p, pIdx) in getFilteredProducts(item.searchQuery, index)" :key="p.id">
                                                <div :id="'prod-opt-' + index + '-' + pIdx"
                                                     @mousedown.prevent="selectProductForRow(index, p); activeProdDropdownIndex = null; $nextTick(() => { const q = document.getElementById('row-qty-input-' + index); if(q) { q.focus(); q.select(); } })"
                                                     @click="selectProductForRow(index, p); activeProdDropdownIndex = null; $nextTick(() => { const q = document.getElementById('row-qty-input-' + index); if(q) { q.focus(); q.select(); } })"
                                                     @mouseenter="prodHighlightIndex = pIdx"
                                                     class="px-3 py-2 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                                     :class="prodHighlightIndex === pIdx ? 'bg-sky-100 text-sky-950 font-bold ring-1 ring-sky-300' : 'hover:bg-slate-50'">
                                                    <div class="min-w-0 pr-3">
                                                        <div class="font-bold text-slate-900 truncate">
                                                            <span class="text-sky-600 font-mono mr-1" x-text="'[' + p.sku + ']'"></span>
                                                            <span x-text="p.name"></span>
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                            <span class="font-semibold text-slate-700" x-text="'ĐVT: ' + (p.unit || '') + (p.conversion_unit && p.conversion_unit.trim().toLowerCase() !== (p.unit || '').trim().toLowerCase() ? ' / ' + p.conversion_unit : '')"></span>
                                                            <span>&bull;</span>
                                                            <span :class="getProductStockInWarehouse(p, formData.warehouse) > 0 ? 'text-emerald-600 font-semibold' : 'text-rose-500'" 
                                                                  x-text="'Tồn ' + (formData.warehouse ? '[' + formData.warehouse.replace(/\s*\(.*?\)/, '') + ']: ' : ': ') + formatNumber(getProductStockInWarehouse(p, formData.warehouse)) + ' ' + (p.unit || '') + ' (Tổng: ' + formatNumber(p.stock_quantity) + ')'"></span>
                                                        </div>
                                                    </div>
                                                    <div class="shrink-0 text-right">
                                                        <div class="font-black text-sky-700 text-xs" x-text="formatNumber(getProductPrice(p)) + ' đ'"></div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Hiển thị tồn kho khả dụng dưới ô tìm sản phẩm -->
                                    <div x-show="item.product_id" x-cloak class="mt-1 flex items-center gap-1.5 text-[10px] flex-wrap">
                                        <span class="text-slate-400 font-medium" x-text="'Tồn tại ' + (formData.warehouse ? formData.warehouse.replace(/\s*\(.*?\)/, '') : 'kho') + ':'"></span>
                                        <span class="font-bold font-mono" :class="isItemOverStock(item) ? 'text-rose-600 font-black' : 'text-emerald-600'" x-text="formatNumber(getItemAvailableStockInUnit(item)) + ' ' + (item.unit || '')"></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="text-slate-400 font-medium" x-text="'Tổng các kho: ' + formatNumber(item.unit === getProduct(item.product_id)?.conversion_unit ? Math.round(Number(getProduct(item.product_id)?.stock_quantity || 0) * Number(getProduct(item.product_id)?.conversion_rate || 1)) : Number(getProduct(item.product_id)?.stock_quantity || 0)) + ' ' + (item.unit || '')"></span>
                                        <span x-show="isItemOverStock(item)" class="px-1.5 py-0.2 rounded bg-rose-100 text-rose-700 font-bold text-[9px] flex items-center gap-0.5">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            <span>Vượt tồn kho này</span>
                                        </span>
                                    </div>
                                    </template>
                                </td>

                                <!-- ĐVT (Luôn cho phép chọn ĐVT và ĐV quy đổi của từng sản phẩm) -->
                                <td class="py-2 px-2 text-center align-top">
                                    <template x-if="!item.isPromotionalGift">
                                        <select x-model="item.unit" 
                                                @change="onItemUnitChange(index)" 
                                                class="w-full px-1.5 py-1.5 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded-lg text-xs text-center font-bold text-slate-800 focus:outline-none cursor-pointer transition-colors shadow-2xs">
                                            <template x-for="u in getItemUnits(item)" :key="u">
                                                <option :value="u" x-text="u" :selected="item.unit === u"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="item.isPromotionalGift">
                                        <span class="inline-block py-1.5 px-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-lg border border-slate-200" x-text="item.unit || 'cây'"></span>
                                    </template>
                                </td>

                                <!-- Đơn giá (Hỗ trợ phím ↑ ↓ để tăng/giảm giá) -->
                                <td class="py-2 px-2 text-right align-top">
                                    <template x-if="!item.isPromotionalGift">
                                        <div class="relative">
                                            <input type="text" 
                                                   inputmode="numeric" 
                                                   :id="'row-price-input-' + index"
                                                   x-money="item.unitPrice" 
                                                   @input="item.price_type = 'Tùy chỉnh'; updateLineTotal(index)" 
                                                   @keydown.up.prevent="increasePrice(index)"
                                                   @keydown.down.prevent="decreasePrice(index)"
                                                   title="Phím ↑ tăng giá, phím ↓ giảm giá"
                                                   class="w-full px-2 py-1.5 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded-lg text-right font-bold text-xs text-slate-900 focus:outline-none shadow-2xs transition-colors">
                                        </div>
                                    </template>
                                    <template x-if="item.isPromotionalGift">
                                        <div class="py-1.5 text-right font-black text-emerald-600 text-xs">
                                            0 đ
                                        </div>
                                    </template>
                                </td>

                                <!-- Số lượng (Hỗ trợ phím ↑ ↓ và bộ nút bấm +/-) -->
                                <td class="py-2 px-2 text-center align-top">
                                    <template x-if="!item.isPromotionalGift">
                                        <div class="relative">
                                            <div class="flex items-center">
                                                <button type="button" 
                                                        @click="decreaseQty(index)" 
                                                        class="w-5 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 rounded-l border border-r-0 border-slate-300 font-black text-xs select-none transition-colors cursor-pointer shrink-0" 
                                                        title="Giảm số lượng (Phím ↓)">-</button>
                                                <input type="text" inputmode="decimal" 
                                                       :id="'row-qty-input-' + index"
                                                       x-number="item.quantity" 
                                                       @input="updateLineTotal(index)" 
                                                       @keydown.up.prevent="increaseQty(index)"
                                                       @keydown.down.prevent="decreaseQty(index)"
                                                       @keydown.enter.prevent="addNewRow()"
                                                       title="Phím ↑ tăng SL, phím ↓ giảm SL"
                                                       :class="isItemOverStock(item) ? 'bg-rose-50 border-2 border-rose-500 text-rose-800 ring-2 ring-rose-200 font-black' : 'bg-white border-y border-slate-300 text-slate-900 font-bold hover:border-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-200'"
                                                       class="w-full h-7 px-1 text-center text-xs shadow-2xs outline-none transition-all rounded-none min-w-[32px]">
                                                <button type="button" 
                                                        @click="increaseQty(index)" 
                                                        class="w-5 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 rounded-r border border-l-0 border-slate-300 font-black text-xs select-none transition-colors cursor-pointer shrink-0" 
                                                        title="Tăng số lượng (Phím ↑)">+</button>
                                            </div>
                                            
                                            <!-- Cảnh báo thiếu tồn kho dưới ô số lượng -->
                                            <div x-show="isItemOverStock(item)" x-cloak class="mt-1 text-[10px] text-rose-600 font-bold flex items-center justify-center gap-0.5 leading-tight">
                                                <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                                                <span>Thiếu: <span x-text="formatNumber(getItemOverStockQty(item))"></span></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="item.isPromotionalGift">
                                        <div class="relative">
                                            <div class="flex items-center justify-center">
                                                <button type="button" 
                                                        @click="decreaseQty(index)" 
                                                        class="w-5 h-7 flex items-center justify-center bg-emerald-100 hover:bg-emerald-200 active:bg-emerald-300 text-emerald-800 rounded-l border border-r-0 border-emerald-300 font-black text-xs select-none transition-colors cursor-pointer shrink-0" 
                                                        title="Giảm số lượng">-</button>
                                                <input type="text" inputmode="decimal" 
                                                        :id="'row-qty-input-' + index"
                                                        x-number="item.quantity" 
                                                        @input="item._manualQty = true; updateLineTotal(index)" 
                                                        @keydown.up.prevent="increaseQty(index)"
                                                        @keydown.down.prevent="decreaseQty(index)"
                                                        title="Số lượng quà tặng (có thể sửa)"
                                                        class="w-full h-7 px-1 text-center text-xs shadow-2xs outline-none transition-all rounded-none min-w-[32px] bg-emerald-50 border-y border-emerald-400 text-emerald-900 font-black">
                                                <button type="button" 
                                                        @click="increaseQty(index)" 
                                                        class="w-5 h-7 flex items-center justify-center bg-emerald-100 hover:bg-emerald-200 active:bg-emerald-300 text-emerald-800 rounded-r border border-l-0 border-emerald-300 font-black text-xs select-none transition-colors cursor-pointer shrink-0" 
                                                        title="Tăng số lượng">+</button>
                                            </div>
                                        </div>
                                    </template>
                                </td>

                                <!-- Cột Số Lượng Tặng (SL Tặng) -->
                                <td class="py-2 px-2 text-center align-top bg-emerald-50/20 border-x border-slate-200">
                                    <template x-if="!item.isPromotionalGift">
                                        <div class="relative">
                                            <div class="flex items-center justify-center">
                                                <button type="button" 
                                                        @click="decreaseGiftQty(index)" 
                                                        class="w-5 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 rounded-l border border-r-0 border-slate-300 font-black text-xs select-none transition-colors cursor-pointer shrink-0" 
                                                        title="Giảm SL tặng">-</button>
                                                <input type="text" inputmode="decimal" 
                                                       :id="'row-gift-qty-input-' + index"
                                                       x-number="item.gift_quantity" 
                                                       @input="onGiftQtyChange(index)" 
                                                       @keydown.up.prevent="increaseGiftQty(index)"
                                                       @keydown.down.prevent="decreaseGiftQty(index)"
                                                       placeholder="0"
                                                       title="Số lượng tặng kèm (0đ)"
                                                       :class="Number(item.gift_quantity) > 0 ? 'bg-emerald-100/90 border-2 border-emerald-500 text-emerald-900 ring-2 ring-emerald-200 font-black' : 'bg-white border-y border-slate-300 text-slate-700 font-bold hover:border-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-200'"
                                                       class="w-full h-7 px-1 text-center text-xs shadow-2xs outline-none transition-all rounded-none min-w-[32px]">
                                                <button type="button" 
                                                        @click="increaseGiftQty(index)" 
                                                        class="w-5 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 rounded-r border border-l-0 border-slate-300 font-black text-xs select-none transition-colors cursor-pointer shrink-0" 
                                                        title="Tăng SL tặng">+</button>
                                            </div>
                                            <div x-show="Number(item.gift_quantity) > 0" class="mt-0.5 text-[9px] text-emerald-700 font-bold truncate">
                                                Tặng +<span x-text="item.gift_quantity"></span>
                                            </div>
                                            <div x-show="Number(item.discount_percent) > 0 || Number(item.discount_amount) > 0" class="mt-0.5 text-[9px] text-amber-600 font-semibold truncate" title="Đang có chiết khấu">
                                                CK: <span x-text="item.discount_percent > 0 ? (item.discount_percent + '%') : (formatNumber(item.discount_amount) + 'đ')"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="item.isPromotionalGift">
                                        <div class="py-1 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">Quà tặng</span>
                                        </div>
                                    </template>
                                </td>

                                <!-- Tiền chiết khấu (% mỗi dòng hoặc VNĐ) -->
                                <td class="py-2 px-2 bg-sky-50/30 border-x border-slate-200 align-top">
                                    <template x-if="!item.isPromotionalGift">
                                        <div>
                                            <div class="flex items-center gap-1.5 justify-between">
                                                <!-- Ô nhập % -->
                                                <div class="flex items-center gap-0.5 shrink-0">
                                                    <input type="number" min="0" max="100" step="any" 
                                                           x-model.number="item.discount_percent" 
                                                           @input="onLineDiscountPercentChange(index)" 
                                                           placeholder="%" 
                                                           title="Tỷ lệ chiết khấu (%)"
                                                           class="w-11 px-1 py-1.5 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded text-center text-xs font-semibold focus:outline-none shadow-2xs transition-colors">
                                                    <span class="text-[10px] text-slate-400 font-bold">%</span>
                                                </div>
                                                <!-- Ô nhập Tiền Chiết Khấu (VNĐ) -->
                                                <div class="flex items-center gap-0.5 flex-1 min-w-0">
                                                    <input type="text" inputmode="numeric" 
                                                           x-money="item.discount_amount" 
                                                           @input="onLineDiscountAmountChange(index)" 
                                                           placeholder="0" 
                                                           title="Số tiền chiết khấu (đ)"
                                                           class="w-full px-1.5 py-1.5 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded text-right text-xs font-mono font-bold text-slate-800 focus:outline-none shadow-2xs transition-colors">
                                                    <span class="text-[10px] text-slate-400 font-bold shrink-0">đ</span>
                                                </div>
                                            </div>
                                            <div x-show="Number(item.gift_quantity) > 0" class="mt-0.5 text-[9px] text-emerald-600 font-semibold truncate text-right" title="Dòng này có quà tặng kèm">
                                                <i class="fa-solid fa-gift text-[8px]"></i> Quà tặng: <span x-text="item.gift_quantity"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="item.isPromotionalGift">
                                        <div class="py-1.5 text-center text-slate-400 text-xs italic font-medium">0 đ</div>
                                    </template>
                                </td>

                                <!-- VAT (ăn theo sản phẩm chọn) -->
                                <td class="py-2 px-2 bg-sky-50/30 border-r border-slate-200 align-top">
                                    <template x-if="!item.isPromotionalGift">
                                        <div class="flex items-center gap-1 justify-between">
                                            <div class="flex items-center gap-0.5">
                                                <input type="number" min="0" max="100" step="any" x-model.number="item.vat_percent" @input="updateLineTotal(index)" placeholder="10" class="w-10 px-1 py-1.5 bg-white border border-slate-300 hover:border-slate-400 focus:border-sky-500 rounded text-center text-xs font-semibold focus:outline-none shadow-2xs transition-colors">
                                                <span class="text-[10px] text-slate-400">%</span>
                                            </div>
                                            <span class="text-[11px] font-mono text-slate-700 font-semibold truncate text-right w-16" x-text="formatNumber(item.vat_amount) + ' đ'"></span>
                                        </div>
                                    </template>
                                    <template x-if="item.isPromotionalGift">
                                        <div class="py-1.5 text-center text-slate-400 text-xs italic font-medium">0 đ</div>
                                    </template>
                                </td>

                                <!-- Cột SL Bốc: chỉ render khi đang ở giai đoạn soạn hàng -->
                                <template x-if="isPickingStage()">
                                    <td class="py-2 px-2 text-center align-top bg-orange-50/20 border-x border-slate-200">
                                        <template x-if="!item.isPromotionalGift">
                                            <div>
                                                <div class="font-black text-sm font-mono"
                                                     :class="Number(item.picked_qty||0) <= 0 ? 'text-slate-400' :
                                                             Number(item.picked_qty||0) >= Number(item.quantity||0)+Number(item.gift_quantity||0) ? 'text-emerald-600' :
                                                             'text-orange-600'"
                                                     x-text="Number(item.picked_qty||0) > 0 ? item.picked_qty : '—'"></div>
                                                <div class="text-[9px] text-slate-400 mt-0.5"
                                                     x-show="Number(item.picked_qty||0) > 0"
                                                     x-text="'/' + (Number(item.quantity||0)+Number(item.gift_quantity||0)) + ' ' + (item.unit||'')"></div>
                                                <!-- Badge Thiếu -->
                                                <template x-if="isItemShortage(item)">
                                                    <button type="button"
                                                            @click="openShortageModal(item, index)"
                                                            class="mt-1 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-rose-100 hover:bg-rose-200 text-rose-700 font-black text-[9px] border border-rose-300 cursor-pointer transition-colors">
                                                        <i class="fa-solid fa-scissors text-[8px]"></i>
                                                        <span>Thiếu <span x-text="getShortageQty(item)"></span></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="item.isPromotionalGift">
                                            <span class="text-slate-300 text-xs">—</span>
                                        </template>
                                    </td>
                                </template>

                                <!-- Thành tiền -->
                                <td class="py-2 px-3 text-right align-top">
                                    <span class="font-bold text-xs block" :class="item.isPromotionalGift ? 'text-emerald-700' : 'text-slate-900'" x-text="formatNumber(item.amount) + ' đ'"></span>
                                    <!-- Hiển thị thiếu hàng dưới thành tiền khi có -->
                                    <template x-if="isItemShortage(item)">
                                        <div class="mt-0.5 text-[9px] text-rose-600 font-bold flex items-center justify-end gap-0.5">
                                            <i class="fa-solid fa-triangle-exclamation text-[8px]"></i>
                                            <span>Thiếu <span x-text="getShortageQty(item)"></span> <span x-text="item.unit"></span></span>
                                        </div>
                                    </template>
                                </td>

                                <!-- Nút Xóa -->
                                <td class="py-2 px-2 text-center">
                                    <button type="button" @click="removeItem(index)" class="w-7 h-7 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-colors cursor-pointer" title="Xóa dòng">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Under Table: Thêm dòng button + Shortcut tip -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                <button type="button" @click="addNewRow()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-xs rounded-xl border border-sky-200 transition-colors cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Thêm dòng</span>
                </button>
                <div class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                    <span class="text-slate-400">Mẹo:</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F8</kbd> / <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">Ctrl+Enter</kbd> thêm dòng
                    <span class="text-slate-300">&bull;</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F9</kbd> lưu
                    <span class="text-slate-300">&bull;</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">Ctrl+F9</kbd> lưu và in
                </div>
            </div>
        </div>

        <!-- 5. Footer / Summary Area -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pt-2">
            <!-- Left Column: Ghi chú & Alert Info -->
            <div class="lg:col-span-7 space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">GHI CHÚ</label>
                    <textarea x-model="formData.notes" rows="3" placeholder="Nhập ghi chú đơn hàng, địa điểm giao hoặc điều khoản chiết khấu..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:border-sky-500 focus:outline-none"></textarea>
                </div>
                <div class="px-3.5 py-2.5 bg-sky-50/70 border border-sky-200/60 rounded-xl text-xs text-sky-700 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-sky-500"></i>
                    <span>Tiền VAT được tính trực tiếp trên đơn giá của từng sản phẩm.</span>
                </div>

                <!-- Khối Chương Trình Khuyến Mại & Chính Sách Ưu Đãi -->
                <div class="rounded-xl border border-amber-200 bg-linear-to-br from-amber-50/80 via-white to-amber-50/30 p-3.5 space-y-2.5 shadow-2xs">
                    <div class="flex items-center justify-between gap-2 flex-wrap pb-2 border-b border-amber-200/70">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs shadow-2xs font-bold">
                                <i class="fa-solid fa-gift"></i>
                            </span>
                            <span class="font-bold text-slate-800 text-xs uppercase tracking-wide">Chương Trình Khuyến Mại & Ưu Đãi</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300" x-text="salesPoliciesCatalog.length + ' chương trình'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-1.5 cursor-pointer text-[11px] font-semibold text-slate-700 select-none">
                                <input type="checkbox" x-model="autoApplyPromotions" @change="checkAndApplyPromotions(true)" class="rounded text-amber-600 focus:ring-0 cursor-pointer">
                                <span>Tự động áp dụng</span>
                            </label>
                            <button type="button" @click="checkAndApplyPromotions(true)" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[11px] font-bold transition-all shadow-2xs cursor-pointer flex items-center gap-1">
                                <i class="fa-solid fa-arrows-rotate text-[10px]"></i>
                                <span>Kiểm tra & Áp dụng</span>
                            </button>
                        </div>
                    </div>

                    <!-- Danh sách các chính sách khuyến mại đang hiệu lực -->
                    <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                        <template x-for="policy in salesPoliciesCatalog" :key="policy.id">
                            <div class="p-2 rounded-lg border transition-all text-xs flex items-center justify-between gap-2"
                                 :class="isPolicyApplied(policy) ? 'bg-emerald-50/90 border-emerald-300 text-emerald-950 shadow-2xs' : 'bg-white/80 border-slate-200 text-slate-600'">
                                <div class="min-w-0 flex items-center gap-2">
                                    <span class="font-mono font-bold text-[10px] px-1.5 py-0.5 rounded shrink-0"
                                          :class="isPolicyApplied(policy) ? 'bg-emerald-200 text-emerald-900 font-extrabold' : 'bg-slate-100 text-slate-600'" 
                                          x-text="policy.code"></span>
                                    <div class="truncate">
                                        <span class="font-bold text-slate-900" x-text="policy.name"></span>
                                        <span class="text-[11px] text-slate-500 ml-1" x-text="getPolicyConditionDesc(policy)"></span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center gap-1.5">
                                    <template x-if="isPolicyApplied(policy)">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white flex items-center gap-1 shadow-2xs">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i>
                                            <span x-text="getPolicyBenefitDesc(policy)"></span>
                                        </span>
                                    </template>
                                    <template x-if="!isPolicyApplied(policy)">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500" x-text="getPolicyIneligibleReason(policy)"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="salesPoliciesCatalog.length === 0">
                            <div class="text-center py-2 text-slate-400 italic text-[11px]">
                                Hiện chưa có chương trình khuyến mại nào đang hoạt động trong hệ thống.
                            </div>
                        </template>
                    </div>

                    <!-- Badge thông báo các ưu đãi đã được áp dụng vào đơn -->
                    <div x-show="appliedPromotionNotices.length > 0" x-cloak class="pt-1.5 border-t border-amber-200/60 flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wide">Đã áp dụng:</span>
                        <template x-for="(notice, nIdx) in appliedPromotionNotices" :key="nIdx">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px] border border-emerald-300">
                                <i class="fa-solid fa-gift text-emerald-600 text-[9px]"></i>
                                <span x-text="notice"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Column: Financial Calculations Summary -->
            <div class="lg:col-span-5 bg-slate-50/80 p-4 rounded-xl border border-slate-200/70 space-y-2.5 text-xs font-semibold">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tiền hàng (chưa VAT)</span>
                        <span class="font-bold text-slate-800" x-text="formatNumber(subtotalBeforeVat) + ' đ'"></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tiền VAT</span>
                        <span class="font-bold text-slate-800" x-text="formatNumber(totalVat) + ' đ'"></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tổng tiền (đã gồm VAT)</span>
                        <span class="font-bold text-slate-900" x-text="formatNumber(totalWithVat) + ' đ'"></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600 pt-1">
                        <span>Chiết khấu tổng (sau VAT)</span>
                        <div class="flex items-center gap-1.5">
                            <div class="flex items-center gap-0.5">
                                <input type="number" min="0" max="100" step="any" x-model.number="orderDiscountPercent" @input="onOrderDiscountPercentChange()" placeholder="%" class="w-12 px-1.5 py-1 bg-white border border-slate-200 rounded text-center text-xs focus:outline-none">
                                <span class="text-[10px] text-slate-400">%</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <input type="text" inputmode="numeric" x-money="formData.discount_amount" @input="onOrderDiscountAmountChange()" placeholder="Tiền CK" class="w-28 px-2 py-1 bg-white border border-slate-200 rounded text-right text-xs font-bold focus:border-sky-500 focus:outline-none">
                                <span class="text-slate-400">đ</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-sm font-black text-slate-900">
                        <span class="text-base text-slate-900">Thành tiền</span>
                        <span class="text-xl text-sky-600 font-black" x-text="formatNumber(finalTotal) + ' đ'"></span>
                    </div>

                    <!-- Dòng công nợ dự kiến sau đơn -->
                    <template x-if="getSelectedDealer()">
                        <div class="pt-2 border-t border-dashed border-slate-200 text-xs">
                            <div class="flex items-center justify-between" :class="isDebtOverLimit() ? 'text-rose-700 font-bold' : 'text-slate-600 font-medium'">
                                <span class="flex items-center gap-1">
                                    <i :class="isDebtOverLimit() ? 'fa-solid fa-triangle-exclamation text-rose-500' : 'fa-solid fa-clock-rotate-left text-slate-400'"></i>
                                    <span>Nợ dự kiến sau đơn:</span>
                                </span>
                                <span class="font-mono font-bold" :class="isDebtOverLimit() ? 'text-rose-700 font-black' : 'text-slate-800'" x-text="formatNumber(getProjectedDebt()) + ' đ'"></span>
                            </div>
                            <div x-show="isDebtOverLimit()" class="text-right text-[10px] text-rose-600 font-extrabold mt-0.5">
                                ⚠️ Vượt hạn mức: +<span x-text="formatNumber(getDebtExcessAmount()) + ' đ'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <!-- ===== Modal xử lý Thiếu Hàng ===== -->
    <div x-show="shortageModal.show" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.55);backdrop-filter:blur(3px);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg border border-slate-200 overflow-hidden" @click.stop>
            <!-- Header -->
            <div class="bg-gradient-to-r from-rose-700 to-rose-500 p-5 text-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl">🚨</div>
                    <div>
                        <div class="font-black text-base">Xử lý thiếu hàng kho</div>
                        <div class="text-rose-100 text-xs mt-0.5" x-text="shortageModal.item ? '[' + shortageModal.item.sku + '] ' + shortageModal.item.name : ''"></div>
                    </div>
                </div>
            </div>
            <!-- Body -->
            <template x-if="shortageModal.item">
            <div class="p-5 space-y-4">
                <!-- Thông tin SL -->
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wide mb-1">SL đặt</div>
                        <div class="text-2xl font-black text-slate-800" x-text="Number(shortageModal.item.quantity||0)+Number(shortageModal.item.gift_quantity||0)"></div>
                        <div class="text-[11px] text-slate-400" x-text="shortageModal.item.unit || ''"></div>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-3 border border-orange-200">
                        <div class="text-[10px] text-orange-600 font-bold uppercase tracking-wide mb-1">Kho đã bốc</div>
                        <div class="text-2xl font-black text-orange-700" x-text="shortageModal.item.picked_qty || 0"></div>
                        <div class="text-[11px] text-orange-400" x-text="shortageModal.item.unit || ''"></div>
                    </div>
                    <div class="bg-rose-50 rounded-xl p-3 border border-rose-300">
                        <div class="text-[10px] text-rose-600 font-bold uppercase tracking-wide mb-1">Thiếu</div>
                        <div class="text-2xl font-black text-rose-700" x-text="getShortageQty(shortageModal.item)"></div>
                        <div class="text-[11px] text-rose-400" x-text="shortageModal.item.unit || ''"></div>
                    </div>
                </div>

                <!-- Lý do thiếu (nếu có) -->
                <template x-if="shortageModal.item._shortageReason">
                    <div class="px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-amber-500"></i>
                        <span>Lý do: <strong x-text="shortageModal.item._shortageReason"></strong></span>
                    </div>
                </template>

                <!-- Lựa chọn xử lý -->
                <div class="space-y-3">
                    <!-- Ưu tiên 1: Cắt SL theo bốc -->
                    <button type="button" @click="cutToPickedQty()"
                            :disabled="backorderSaving"
                            class="w-full flex items-start gap-3 p-4 bg-orange-50 hover:bg-orange-100 border-2 border-orange-300 rounded-xl text-left transition-all cursor-pointer group disabled:opacity-60">
                        <div class="w-9 h-9 rounded-lg bg-orange-500 text-white flex items-center justify-center shrink-0 font-black text-base group-hover:scale-110 transition-transform">✂</div>
                        <div>
                            <div class="font-black text-slate-800 text-sm">Cắt số lượng theo số đã bốc</div>
                            <div class="text-xs text-slate-500 mt-0.5">Chỉnh SL xuống bằng số kho đã bốc (<span x-text="shortageModal.item.picked_qty||0" class="font-bold text-orange-700"></span> <span x-text="shortageModal.item.unit||''"></span>). Phần thiếu sẽ bị xóa khỏi đơn này.</div>
                        </div>
                    </button>

                    <!-- Ưu tiên 2: Tạo đơn giao sau -->
                    <button type="button" @click="createBackOrder()"
                            :disabled="backorderSaving"
                            class="w-full flex items-start gap-3 p-4 bg-blue-50 hover:bg-blue-100 border-2 border-blue-300 rounded-xl text-left transition-all cursor-pointer group disabled:opacity-60">
                        <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 font-black text-base group-hover:scale-110 transition-transform">📋</div>
                        <div>
                            <div class="font-black text-slate-800 text-sm">Tạo đơn giao sau (nợ hàng)</div>
                            <div class="text-xs text-slate-500 mt-0.5">Cắt đơn này xuống bằng số đã bốc VÀ tự động tạo đơn nợ hàng mới cho phần thiếu (<span x-text="getShortageQty(shortageModal.item)" class="font-bold text-blue-700"></span> <span x-text="shortageModal.item.unit||''"></span>).</div>
                        </div>
                    </button>

                    <!-- Lựa chọn ÁP DỤNG TẤT CẢ khi có nhiều hơn 1 SP thiếu -->
                    <template x-if="getShortageItems().length > 1">
                        <div class="pt-2 border-t border-slate-200 space-y-2">
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                <span>Hoặc áp dụng cho tất cả sản phẩm thiếu:</span>
                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-black" x-text="getShortageItems().length + ' SP'"></span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <button type="button" @click="cutAllToPickedQty()" :disabled="backorderSaving" class="px-2.5 py-2 bg-orange-100 hover:bg-orange-200 text-orange-800 font-bold text-xs rounded-xl border border-orange-300 transition-colors flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
                                    <i class="fa-solid fa-scissors text-xs"></i>
                                    <span>Cắt tất cả (<span x-text="getShortageItems().length"></span>)</span>
                                </button>
                                <button type="button" @click="createBackOrderAll()" :disabled="backorderSaving" class="px-2.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
                                    <i class="fa-solid fa-boxes-packing text-xs"></i>
                                    <span>Đơn giao sau tổng</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span x-show="backorderSaving" class="text-xs text-blue-600 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-spinner animate-spin"></i> Đang xử lý...
                    </span>
                    <button type="button" @click="shortageModal.show = false"
                            :disabled="backorderSaving"
                            class="ml-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl border border-slate-300 cursor-pointer transition-colors">Hủy</button>
                </div>
            </div>
            </template>
        </div>
    </div>

    </fieldset>

    <!-- 6. Floating / Sticky Action Bar (Bottom Bar: 0 MẶT HÀNG | Đóng Esc | Lưu & In Ctrl+F9 | Lưu F9) -->
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 sm:px-8 py-3 shadow-lg flex items-center justify-between gap-3">
        <!-- Left: Items count & Total -->
        <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
            <span class="uppercase tracking-wider" x-text="items.length + ' MẶT HÀNG'"></span>
            <span class="text-slate-300">|</span>
            <div class="flex items-center gap-3">
                <span class="text-sm font-black text-sky-600" x-text="formatNumber(finalTotal) + ' đ'"></span>
                <!-- Badges cảnh báo nhanh ở thanh footer -->
                <template x-if="isDebtOverLimit()">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1 animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Vượt nợ (+<span x-text="formatNumber(getDebtExcessAmount())"></span>)</span>
                    </span>
                </template>
                <template x-if="getOverStockItems().length > 0">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Thiếu kho (<span x-text="getOverStockItems().length"></span> SP)</span>
                    </span>
                </template>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex items-center gap-2.5">
            <button type="button" @click="confirmClose()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Đóng (Esc)</span>
            </button>

            <!-- Readonly Mode Actions -->
            <template x-if="isReadonly">
                <div class="flex items-center gap-2">
                    <button type="button" @click="printCurrentOrder()" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-print"></i>
                        <span>In chứng từ</span>
                    </button>
                    @if($order)
                        <a href="{{ url('/orders/' . $order->id . '/edit') }}" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Bổ sung / Chỉnh sửa đơn</span>
                        </a>
                    @endif
                </div>
            </template>

            <!-- Bulk Shortage Actions in Bottom Bar -->
            <template x-if="!isReadonly && isPickingStage() && getShortageItems().length > 0">
                <div class="flex items-center gap-1.5 border-r border-slate-200 pr-2.5">
                    <button type="button" @click="cutAllToPickedQty()" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 shadow-2xs transition-all flex items-center gap-1 cursor-pointer" title="Cắt tất cả sản phẩm thiếu theo SL kho đã bốc">
                        <i class="fa-solid fa-scissors"></i>
                        <span>Cắt tổng (<span x-text="getShortageItems().length"></span>)</span>
                    </button>
                    <button type="button" @click="createBackOrderAll()" :disabled="backorderSaving" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1 cursor-pointer disabled:opacity-50" title="Tạo đơn giao sau (nợ hàng) cho toàn bộ phần thiếu">
                        <i class="fa-solid fa-boxes-packing"></i>
                        <span>Tạo đơn nợ tổng</span>
                    </button>
                </div>
            </template>

            <!-- Normal Edit / Create Mode Actions -->
            <template x-if="!isReadonly">
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="submitOrder(true)" :disabled="saving" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50" title="Lưu đơn và in chứng từ (Ctrl+F9)">
                        <i class="fa-solid fa-print"></i>
                        <span>Lưu & In (Ctrl+F9)</span>
                    </button>
                    <button type="button" @click="submitOrder(false)" :disabled="saving" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50" title="Lưu đơn hàng (F9)">
                        <i class="fa-solid fa-floppy-disk" x-show="!saving"></i>
                        <i class="fa-solid fa-circle-notch fa-spin" x-show="saving" x-cloak></i>
                        <span x-text="saving ? 'Đang lưu...' : 'Lưu (F9)'"></span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Add Customer Modal -->
    <div x-show="showAddDealer" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" @click.self="showAddDealer = false">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-sky-600"></i>
                    <span>Thêm Khách Hàng / Đại Lý Nhanh</span>
                </h3>
                <button type="button" @click="showAddDealer = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Tên khách hàng / Đại lý <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="newDealer.name" placeholder="Ví dụ: Đại Lý Điện Nước Hoàng Gia" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Số điện thoại</label>
                    <input type="text" x-model="newDealer.phone" placeholder="09xxxx..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Cấp đại lý / Bảng giá</label>
                    <select x-model="newDealer.tier" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none">
                        <template x-for="pt in priceTiersCatalog" :key="pt.id">
                            <option :value="pt.name" x-text="pt.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Địa chỉ giao hàng</label>
                    <textarea x-model="newDealer.address" rows="2" placeholder="Số nhà, đường, huyện/tỉnh..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-sky-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showAddDealer = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs cursor-pointer">Hủy</button>
                <button type="button" @click="applyNewDealer()" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold text-xs cursor-pointer">Áp dụng vào đơn</button>
            </div>
        </div>
    </div>

    <!-- Modal Chi tiết và Gộp đơn Giao sau (Nợ hàng) -->
    <div x-show="showMergeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" @click.self="showMergeModal = false">
        <div class="bg-white rounded-2xl max-w-3xl w-full shadow-2xl border border-slate-200 max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center shadow-xs">
                        <i class="fa-solid fa-code-merge text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Danh Sách Đơn Giao Sau (Nợ Hàng) Chờ Gộp</h3>
                        <p class="text-[11px] text-slate-500">Khách hàng: <span class="font-bold text-slate-800" x-text="formData.customer_name"></span></p>
                    </div>
                </div>
                <button type="button" @click="showMergeModal = false" class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                <template x-if="!pendingMergeOrders || pendingMergeOrders.length === 0">
                    <div class="text-center py-8 text-slate-400 italic">
                        Không có đơn nợ hàng nào cần gộp.
                    </div>
                </template>
                <template x-for="(pOrd, pIdx) in pendingMergeOrders" :key="pOrd.id">
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                        <div class="flex items-center justify-between px-4 py-3 bg-slate-100/80 border-b border-slate-200">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-black text-slate-900 text-xs" x-text="pOrd.order_code"></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800"
                                      x-text="pOrd.type_label"></span>
                                <span class="text-[11px] text-slate-400" x-text="'Tạo ngày: ' + pOrd.created_at"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-700">Tổng: <span class="font-mono text-indigo-700 font-extrabold" x-text="formatNumber(pOrd.total_amount) + ' đ'"></span></span>
                                <button type="button" 
                                        @click="mergeOrder(pOrd)" 
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs transition-colors flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                    <span>Gộp đơn này</span>
                                </button>
                            </div>
                        </div>
                        <!-- Table items of order -->
                        <div class="p-3 bg-white overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="text-[10px] text-slate-500 uppercase bg-slate-50">
                                    <tr>
                                        <th class="px-2 py-1.5">Sản phẩm</th>
                                        <th class="px-2 py-1.5 text-center">ĐVT</th>
                                        <th class="px-2 py-1.5 text-right">Số lượng</th>
                                        <th class="px-2 py-1.5 text-right">Đơn giá</th>
                                        <th class="px-2 py-1.5 text-right">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="item in pOrd.items" :key="item.id">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="px-2 py-1.5 font-medium text-slate-800">
                                                <span class="font-mono text-[10px] text-slate-400 mr-1" x-text="'[' + item.sku + ']'"></span>
                                                <span x-text="item.name"></span>
                                            </td>
                                            <td class="px-2 py-1.5 text-center text-slate-600" x-text="item.unit"></td>
                                            <td class="px-2 py-1.5 text-right font-bold text-indigo-600" x-text="item.quantity"></td>
                                            <td class="px-2 py-1.5 text-right font-mono text-slate-600" x-text="formatNumber(item.unit_price) + ' đ'"></td>
                                            <td class="px-2 py-1.5 text-right font-mono font-bold text-slate-800" x-text="formatNumber(item.amount) + ' đ'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-3 border-t border-slate-200 bg-slate-50">
                <span class="text-xs text-slate-500">Các mặt hàng sẽ được thêm vào bảng sản phẩm của đơn hiện tại.</span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showMergeModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl cursor-pointer">
                        Đóng
                    </button>
                    <button type="button" 
                            x-show="pendingMergeOrders && pendingMergeOrders.length > 0"
                            @click="mergeAllPendingOrders()" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-code-merge"></i>
                        <span>Gộp tất cả vào đơn này</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-load data in JSON scripts to avoid heavy AST compilation in JS -->
    <script id="orders-products-json" type="application/json">{!! json_encode($products, JSON_UNESCAPED_UNICODE) !!}</script>
    <script id="orders-dealers-json" type="application/json">{!! json_encode($dealers, JSON_UNESCAPED_UNICODE) !!}</script>
    <script id="orders-policies-json" type="application/json">{!! json_encode($salesPolicies ?? [], JSON_UNESCAPED_UNICODE) !!}</script>

</div>

<script>
// Parse catalog data once and index by ID for O(1) instant lookup
const _rawProductsCatalog = JSON.parse(document.getElementById('orders-products-json')?.textContent || '[]');
const _rawDealersData = JSON.parse(document.getElementById('orders-dealers-json')?.textContent || '[]');
const _rawSalesPolicies = JSON.parse(document.getElementById('orders-policies-json')?.textContent || '[]');
const _productsMap = new Map();
for (let i = 0; i < _rawProductsCatalog.length; i++) {
    _productsMap.set(String(_rawProductsCatalog[i].id), _rawProductsCatalog[i]);
}

function orderFormApp() {
    return {
        isReadonly: {{ (request('readonly') == '1' || request('mode') === 'readonly') ? 'true' : 'false' }},
        saving: false,
        errorMessage: '',
        shortageModal: { show: false, item: null, index: -1 },
        backorderSaving: false,
        showAddDealer: false,
        newDealer: {
            name: '',
            phone: '',
            tier: '',
            address: ''
        },
        pendingMergeOrders: [],
        isLoadingMergeOrders: false,
        showMergeModal: false,
        mergedOrderIds: [],
        dismissedPendingAlert: false,
        dealersData: Object.freeze(_rawDealersData),
        warehousesCatalog: @json($warehouses ?? []),
        isDriverUser: {{ ($isDriverUser ?? false) ? 'true' : 'false' }},
        productsCatalog: Object.freeze(_rawProductsCatalog),
        salesPoliciesCatalog: Object.freeze(_rawSalesPolicies),
        priceTiersCatalog: @json($priceTiers ?? []),
        autoApplyPromotions: true,
        appliedPromotionNotices: [],
        appliedPromoPolicyIds: [],
        _isApplyingPromotions: false,
        _autoOrderDiscount: false,
        _manualOrderDiscount: false,
        activeProdDropdownIndex: null,
        prodHighlightIndex: 0,
        bulkDiscountPercent: '',
        originalOrderTotal: {{ $order ? (float)$order->total_amount : 0 }},
        isOriginalOrderInDebt: {{ ($order && !in_array($order->status, ['Báo giá', 'Bản nháp', 'Hủy', 'Đã hủy'])) ? 'true' : 'false' }},
        activeTicketIndex: 0,
        ticketCounter: 1,
        tickets: [
            {
                id: 1,
                title: '{{ $order ? ($order->order_code ?: "Phiếu đơn hàng") : (request("order_type") === "backorder" ? "Đơn giao sau mới" : "Phiếu mới 1") }}',
                orderId: '{{ $order ? $order->id : "" }}',
                orderDiscountPercent: 0,
                bulkDiscountPercent: '',
                subtotalBeforeVat: 0,
                totalVat: 0,
                totalWithVat: 0,
                finalTotal: 0,
                formData: {
                    id: '{{ $order ? $order->id : "" }}',
                    order_code: '{{ $order ? $order->order_code : "" }}',
                    order_date: '{{ $order && $order->created_at ? $order->created_at->format("Y-m-d") : date("Y-m-d") }}',
                    delivery_date: '{{ $order ? ($order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format("Y-m-d") : "") : date("Y-m-d") }}',
                    picking_date: '{{ $order ? ($order->picking_date ? \Carbon\Carbon::parse($order->picking_date)->format("Y-m-d") : "") : date("Y-m-d") }}',
                    order_type: '{{ $order ? ($order->order_type ?? "normal") : (request("order_type") ?: "normal") }}',
                    dealer_id: '{{ $order ? $order->dealer_id : "" }}',
                    customer_name: '{{ $order ? addslashes($order->customer_name) : "" }}',
                    customer_phone: '{{ $order ? addslashes($order->customer_phone) : "" }}',
                    customer_address: '{{ $order ? addslashes($order->customer_address) : "" }}',
                    dealer_tier: '{{ ($order && !empty($order->dealer_tier)) ? $order->dealer_tier : "Nguyên Giá" }}',
                    warehouse: '{{ $order ? $order->warehouse : ($warehouses->first()?->name ?? "Kho Tổng Mê Linh (MQ)") }}',
                    payment_method: '{{ $order ? $order->payment_method : "Ghi nợ đại lý" }}',
                    status: '{{ $order ? $order->status : (request("order_type") === "backorder" ? "Giao sau" : "Báo giá") }}',
                    notes: '{{ $order ? addslashes(str_replace(["\r", "\n"], ["", "\\n"], $order->notes)) : "" }}',
                    discount_amount: {{ $order ? (float)$order->discount_amount : 0 }},
                    tax_amount: {{ $order ? (float)$order->tax_amount : 0 }},
                    shortage_reported: {{ ($order && $order->shortage_reported) ? 'true' : 'false' }},
                },
                items: @json($initialItems)
            }
        ],

        // Active references bound to current ticket
        orderId: '',
        orderDiscountPercent: 0,
        subtotalBeforeVat: 0,
        totalVat: 0,
        totalWithVat: 0,
        finalTotal: 0,
        subtotal: 0,
        totalAmount: 0,
        formData: {},
        items: [],

        init() {
            this.syncCurrentTicket();
            
            // Đồng bộ loại giá phía trên với phía dưới sản phẩm và VAT theo cài đặt sản phẩm
            const topTier = this.formData.dealer_tier || 'Nguyên Giá';
            this.items.forEach(it => {
                if (!it.price_type || it.price_type === 'Theo phiếu') {
                    it.price_type = topTier;
                    it.appliedTierPolicy = topTier;
                }
                if (it.product_id && !it.isPromotionalGift) {
                    const prod = this.getProduct(it.product_id);
                    if (prod && prod.vat_rate !== undefined && prod.vat_rate !== null && prod.vat_rate !== '') {
                        it.vat_percent = Number(prod.vat_rate);
                    }
                }
            });

            this.calculateTotals();
            if (this.formData.dealer_id || this.formData.customer_name || this.formData.customer_phone) {
                this.checkPendingMergeOrders();
            }

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === '1') {
                this.$nextTick(() => {
                    setTimeout(() => this.printCurrentOrder(), 500);
                });
            }

            // Lắng nghe sự kiện Kho báo thiếu hàng Realtime
            window.addEventListener('erp:shortage-reported', (e) => {
                const detail = e.detail || {};
                const evOrder = detail.extraData || detail;
                const orderId = Number(detail.orderId || evOrder.orderId);
                const orderCode = evOrder.orderCode;

                if ((this.orderId && Number(this.orderId) === orderId) || (this.formData && this.formData.order_code === orderCode)) {
                    this.formData.status = 'Kho báo thiếu hàng';
                    if (this.tickets && this.tickets[this.activeTicketIndex]) {
                        this.tickets[this.activeTicketIndex].formData.status = 'Kho báo thiếu hàng';
                    }
                    if (Array.isArray(evOrder.shortageItems)) {
                        for (const sIt of evOrder.shortageItems) {
                            const matched = this.items.find(it => it.sku === sIt.sku || (sIt.id && it.id == sIt.id));
                            if (matched) {
                                matched._isShortage = true;
                                matched._shortageQty = sIt.shortage_qty || sIt.shortageQty;
                                matched._shortageReason = sIt.reason;
                            }
                        }
                    }
                }
            });
        },

        syncCurrentTicket() {
            const t = this.tickets[this.activeTicketIndex];
            if (t) {
                this.formData = t.formData;
                this.items = t.items;
                this.orderId = t.orderId || '';
                this.orderDiscountPercent = t.orderDiscountPercent || 0;
                this.bulkDiscountPercent = t.bulkDiscountPercent !== undefined ? t.bulkDiscountPercent : '';
            }
        },

        switchTicket(index) {
            if (index < 0 || index >= this.tickets.length) return;
            this.activeTicketIndex = index;
            this.syncCurrentTicket();
            this.calculateTotals();
        },

        addNewTicket() {
            this.ticketCounter++;
            const newTicket = {
                id: Date.now(),
                title: 'Phiếu mới ' + this.ticketCounter,
                orderId: '',
                orderDiscountPercent: 0,
                subtotalBeforeVat: 0,
                totalVat: 0,
                totalWithVat: 0,
                finalTotal: 0,
                formData: {
                    id: '',
                    order_code: '',
                    order_date: new Date().toISOString().split('T')[0],
                    delivery_date: new Date().toISOString().split('T')[0],
                    picking_date: new Date().toISOString().split('T')[0],
                    order_type: 'normal',
                    dealer_id: '',
                    customer_name: '',
                    customer_phone: '',
                    customer_address: '',
                    dealer_tier: 'Nguyên Giá',
                    warehouse: (this.warehousesCatalog && this.warehousesCatalog.length > 0) ? this.warehousesCatalog[0].name : 'Kho Tổng Mê Linh (MQ)',
                    payment_method: 'Ghi nợ đại lý',
                    status: 'Báo giá',
                    notes: '',
                    discount_amount: 0,
                    tax_amount: 0,
                },
                items: [
                    {
                        id: '',
                        itemId: '',
                        product_id: '',
                        sku: '',
                        name: '',
                        searchQuery: '',
                        unit: 'mét',
                        quantity: 1,
                        gift_quantity: 0,
                        unitPrice: 0,
                        originalPrice: 0,
                        costPrice: 0,
                        discount_percent: 0,
                        discount_amount: 0,
                        vat_percent: 10,
                        vat_amount: 0,
                        appliedTierPolicy: 'Theo phiếu',
                        price_type: 'Theo phiếu',
                        amount: 0,
                        checked: true,
                    }
                ]
            };
            this.tickets.push(newTicket);
            this.switchTicket(this.tickets.length - 1);
        },

        closeTicket(index) {
            const t = this.tickets[index];
            if (!t) return;
            const hasData = t.items.some(it => it.name || it.unitPrice > 0) || t.formData.customer_name;
            const displayName = t.formData.customer_name || t.title;

            if (hasData) {
                if (!confirm(`Bạn có chắc muốn đóng [${displayName}]? Dữ liệu chưa lưu của phiếu này sẽ bị mất.`)) {
                    return;
                }
            }

            if (this.tickets.length === 1) {
                if (confirm('Đây là phiếu duy nhất đang mở. Bạn muốn quay lại danh sách đơn hàng (OK) hay làm mới phiếu này (Hủy)?')) {
                    window.location.href = '/orders';
                } else {
                    this.tickets = [];
                    this.ticketCounter = 0;
                    this.addNewTicket();
                }
                return;
            }

            this.tickets.splice(index, 1);
            if (this.activeTicketIndex >= this.tickets.length) {
                this.switchTicket(this.tickets.length - 1);
            } else if (this.activeTicketIndex === index) {
                this.switchTicket(Math.max(0, index - 1));
            } else if (this.activeTicketIndex > index) {
                this.activeTicketIndex--;
                this.syncCurrentTicket();
            }
        },

        handleGlobalKeydown(e) {
            const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

            // Ctrl + F9: Save & Print (In readonly mode, just prints)
            if (e.ctrlKey && e.key === 'F9') {
                e.preventDefault();
                if (this.isReadonly) {
                    this.printCurrentOrder();
                    return;
                }
                if (!this.saving) {
                    this.submitOrder(true);
                }
                return;
            }

            // F9: Save (Disabled in readonly mode)
            if (e.key === 'F9') {
                e.preventDefault();
                if (this.isReadonly) return;
                if (!this.saving) {
                    this.submitOrder(false);
                }
                return;
            }

            // F8 or Ctrl + Enter: Add line (Disabled in readonly mode)
            if (e.key === 'F8' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                if (this.isReadonly) return;
                this.activeProdDropdownIndex = null;
                this.addNewRow();
                return;
            }

            // F2: New Ticket (Disabled in readonly mode)
            if (e.key === 'F2') {
                e.preventDefault();
                if (this.isReadonly) return;
                this.addNewTicket();
                return;
            }

            // Esc: Close dropdown first, then input blur, then Ticket
            if (e.key === 'Escape') {
                if (this.activeProdDropdownIndex !== null) {
                    this.activeProdDropdownIndex = null;
                    return;
                }
                if (isInput) {
                    document.activeElement.blur();
                    return;
                }
                this.closeTicket(this.activeTicketIndex);
                return;
            }
        },

        applyNewDealer() {
            if (!this.newDealer.name) {
                alert('Vui lòng nhập tên khách hàng');
                return;
            }
            this.formData.customer_name = this.newDealer.name;
            this.formData.customer_phone = this.newDealer.phone || '';
            this.formData.customer_address = this.newDealer.address || '';
            this.formData.dealer_tier = this.newDealer.tier || ((this.priceTiersCatalog && this.priceTiersCatalog.length > 0) ? this.priceTiersCatalog[0].name : 'Giá Đại lý Cấp 1');
            this.recalculateItemPrices();
            this.showAddDealer = false;
            this.checkPendingMergeOrders();
        },

        filteredDealers(keyword) {
            if (!keyword) return this.dealersData;
            const q = keyword.toLowerCase().trim();
            return this.dealersData.filter(d => 
                (d.name && d.name.toLowerCase().includes(q)) || 
                (d.phone && d.phone.includes(q)) ||
                (d.address && d.address.toLowerCase().includes(q))
            );
        },

        selectDealer(dealer) {
            if (!dealer) return;
            this.formData.dealer_id = dealer.id;
            this.formData.customer_name = dealer.name;
            this.formData.customer_phone = dealer.phone || '';
            this.formData.customer_address = dealer.address || '';
            if (dealer.tier) {
                this.formData.customer_tier = dealer.tier;
            }
            // Giữ nguyên loại giá của phiếu là 'Nguyên Giá' hoặc loại giá đã chọn
            // Cập nhật tên tab phiếu theo tên khách hàng đã chọn
            const curT = this.tickets[this.activeTicketIndex];
            if (curT) {
                curT.title = dealer.name;
            }
            this.dismissedPendingAlert = false;
            this.checkPendingMergeOrders();
            this.checkAndApplyPromotions(true);
        },

        clearCustomer() {
            this.formData.dealer_id = '';
            this.formData.customer_name = '';
            this.formData.customer_phone = '';
            this.formData.customer_address = '';
            this.pendingMergeOrders = [];
            this.mergedOrderIds = [];
            this.dismissedPendingAlert = false;
            const curT = this.tickets[this.activeTicketIndex];
            if (curT) {
                curT.title = 'Phiếu mới ' + (this.activeTicketIndex + 1);
            }
            this.checkAndApplyPromotions(true);
        },

        getSelectedDealer() {
            if (this.formData.dealer_id) {
                const found = (this.dealersData || []).find(d => String(d.id) === String(this.formData.dealer_id));
                if (found) return found;
            }
            if (this.formData.customer_name) {
                const cName = this.formData.customer_name.trim().toLowerCase();
                return (this.dealersData || []).find(d => (d.name || '').trim().toLowerCase() === cName) || null;
            }
            return null;
        },

        getProjectedDebt() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return this.finalTotal || 0;
            const curDebt = Number(dealer.current_debt || 0);
            if (this.orderId && this.isOriginalOrderInDebt) {
                const delta = (this.finalTotal || 0) - (this.originalOrderTotal || 0);
                return Math.max(0, curDebt + delta);
            }
            return Math.max(0, curDebt + (this.finalTotal || 0));
        },

        isDebtOverLimit() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return false;
            const limit = Number(dealer.credit_limit || 0);
            if (limit <= 0) return false;
            return this.getProjectedDebt() > limit;
        },

        getDebtExcessAmount() {
            const dealer = this.getSelectedDealer();
            if (!dealer) return 0;
            const limit = Number(dealer.credit_limit || 0);
            if (limit <= 0) return 0;
            return Math.max(0, this.getProjectedDebt() - limit);
        },

        getFilteredProducts(query, currentIndex = -1) {
            // Lấy danh sách ID sản phẩm đã được chọn ở các dòng khác trong đơn hàng để không gợi ý trùng lặp
            const selectedProductIds = new Set();
            (this.items || []).forEach((it, idx) => {
                if (idx !== currentIndex && it.product_id && !it.isPromotionalGift) {
                    selectedProductIds.add(String(it.product_id));
                }
            });

            const list = (this.productsCatalog || []).filter(p => !selectedProductIds.has(String(p.id)));

            if (!query || !query.trim()) {
                return list.slice(0, 30);
            }
            const q = query.toLowerCase().trim();
            return list.filter(p => {
                const sku = (p.sku || '').toLowerCase();
                const name = (p.name || '').toLowerCase();
                return sku.includes(q) || name.includes(q);
            }).slice(0, 30);
        },

        getProduct(productId) {
            if (!productId) return null;
            return _productsMap.get(String(productId)) || null;
        },

        onWarehouseChange() {
            // Khi đổi kho xuất, tính toán và hiển thị lại cảnh báo tồn kho tương ứng của từng mặt hàng
            this.calculateTotals();
        },

        getProductStockInWarehouse(p, whName) {
            if (!p) return 0;
            const targetWarehouse = (whName || this.formData.warehouse || '').trim();
            if (targetWarehouse && p.warehouse_stocks && typeof p.warehouse_stocks === 'object') {
                if (p.warehouse_stocks[targetWarehouse] !== undefined) {
                    return Number(p.warehouse_stocks[targetWarehouse]) || 0;
                }
                const matchedKey = Object.keys(p.warehouse_stocks).find(k => 
                    k.toLowerCase().includes(targetWarehouse.toLowerCase()) || 
                    targetWarehouse.toLowerCase().includes(k.toLowerCase())
                );
                if (matchedKey !== undefined) {
                    return Number(p.warehouse_stocks[matchedKey]) || 0;
                }
            }
            return Number(p.stock_quantity) || 0;
        },

        getItemAvailableStockInUnit(item, whName) {
            if (!item || !item.product_id) return 0;
            const p = this.getProduct(item.product_id);
            if (!p) return 0;

            const baseStock = this.getProductStockInWarehouse(p, whName);
            const curUnit = (item.unit || '').trim().toLowerCase();
            const baseUnit = (p.unit || '').trim().toLowerCase();
            const convUnit = (p.conversion_unit || '').trim().toLowerCase();
            const rate = Number(p.conversion_rate) > 0 ? Number(p.conversion_rate) : 1;

            if (convUnit && curUnit === convUnit && convUnit !== baseUnit) {
                return Math.round(baseStock * rate * 100) / 100;
            }
            return baseStock;
        },

        isItemOverStock(item) {
            if (!item || !item.product_id) return false;
            const avail = this.getItemAvailableStockInUnit(item);
            const reqQty = (Number(item.quantity) || 0) + (Number(item.gift_quantity) || 0);
            return reqQty > avail;
        },

        getItemOverStockQty(item) {
            if (!this.isItemOverStock(item)) return 0;
            const avail = this.getItemAvailableStockInUnit(item);
            const reqQty = (Number(item.quantity) || 0) + (Number(item.gift_quantity) || 0);
            return Math.round(Math.max(0, reqQty - avail) * 100) / 100;
        },

        getOverStockItems() {
            return (this.items || []).filter(it => it.product_id && this.isItemOverStock(it));
        },

        getItemUnits(item) {
            if (!item || !item.product_id) {
                return item && item.unit ? [item.unit] : [];
            }
            const p = this.getProduct(item.product_id);
            if (!p) {
                return item && item.unit ? [item.unit] : [];
            }

            const list = [];
            // 1. Đơn vị tính chính của sản phẩm
            if (p.unit && p.unit.trim()) {
                list.push(p.unit.trim());
            }

            // 2. Đơn vị quy đổi của sản phẩm (nếu có và khác ĐVT chính)
            if (p.conversion_unit && p.conversion_unit.trim()) {
                const conv = p.conversion_unit.trim();
                if (!list.some(u => u.toLowerCase() === conv.toLowerCase())) {
                    list.push(conv);
                }
            }

            return list.length > 0 ? list : (item.unit ? [item.unit] : ['cái']);
        },

        hasConversionUnit(item) {
            if (!item || !item.product_id) return false;
            const p = this.getProduct(item.product_id);
            if (!p) return false;
            return !!(p.conversion_unit && p.conversion_unit.trim() && p.unit && p.conversion_unit.trim().toLowerCase() !== p.unit.trim().toLowerCase());
        },

        onItemUnitChange(index) {
            const item = this.items[index];
            if (!item || !item.product_id) return;
            const prod = this.getProduct(item.product_id);
            if (!prod) return;

            item.unitPrice = this.getProductPrice(prod, item.unit);
            this.updateLineTotal(index);
        },

        getEffectiveTierName(rowItem) {
            if (rowItem && rowItem.price_type && rowItem.price_type !== 'Theo phiếu' && !rowItem.price_type.startsWith('Theo phiếu')) {
                return rowItem.price_type;
            }
            if (this.formData.dealer_tier) {
                return this.formData.dealer_tier;
            }
            return 'Nguyên Giá';
        },

        isDiscountPolicy(policy) {
            if (!policy) return false;
            return policy.policy_type === 'percentage' || policy.policy_type === 'amount';
        },

        isOriginalPriceTier(tier) {
            if (!tier) return false;
            let s = String(tier).trim().toLowerCase();
            if (s.startsWith('theo phiếu')) {
                const m = s.match(/\(([^)]+)\)/);
                if (m) {
                    s = m[1].trim().toLowerCase();
                } else {
                    return this.isOriginalPriceTier(this.formData.dealer_tier);
                }
            }
            return s.includes('nguyên giá') || s.includes('nguyen gia') || s === 'ng';
        },

        isItemOriginalPriceTier(item) {
            if (!item) return false;
            const effTier = this.getEffectiveTierName(item);
            return this.isOriginalPriceTier(effTier);
        },

        getProductPrice(p, unit, tierName) {
            if (!p) return 0;
            const chosenTier = (tierName || this.getEffectiveTierName()).trim();
            const targetUnit = (unit || p.unit || '').trim().toLowerCase();
            const baseUnit = (p.unit || '').trim().toLowerCase();
            const convUnit = (p.conversion_unit || '').trim().toLowerCase();
            const isConv = convUnit && (targetUnit === convUnit) && (convUnit !== baseUnit);
            const rate = Number(p.conversion_rate) > 0 ? Number(p.conversion_rate) : 1;

            const cleanChosen = chosenTier.toLowerCase();

            // 1. Array format: [{ name: 'Giá Bán lẻ', price: 100000, conversion_price: 25000 }]
            if (Array.isArray(p.price_tiers) && p.price_tiers.length > 0) {
                let matchedTier = p.price_tiers.find(t => t.name && t.name.trim().toLowerCase() === cleanChosen);
                
                if (!matchedTier) {
                    matchedTier = p.price_tiers.find(t => {
                        if (!t.name) return false;
                        const tn = t.name.trim().toLowerCase();
                        return tn.includes(cleanChosen) || cleanChosen.includes(tn);
                    });
                }

                if (matchedTier) {
                    if (isConv) {
                        if (Number(matchedTier.conversion_price) > 0) {
                            return Number(matchedTier.conversion_price);
                        }
                        if (Number(matchedTier.price) > 0) {
                            return rate > 1 ? Math.round(Number(matchedTier.price) / rate) : Number(matchedTier.price);
                        }
                    } else if (Number(matchedTier.price) > 0) {
                        return Number(matchedTier.price);
                    }
                }

                // If chosen tier price is 0, fallback to "Nguyên Giá" or positive price tier
                const ngTier = p.price_tiers.find(t => t.name && t.name.toLowerCase().includes('nguyên giá') && Number(t.price) > 0);
                if (ngTier) {
                    if (isConv) {
                        return Number(ngTier.conversion_price) > 0 ? Number(ngTier.conversion_price) : (rate > 1 ? Math.round(Number(ngTier.price) / rate) : Number(ngTier.price));
                    }
                    return Number(ngTier.price);
                }

                const fallbackTier = p.price_tiers.find(t => Number(t.price) > 0);
                if (fallbackTier) {
                    if (isConv) {
                        return Number(fallbackTier.conversion_price) > 0 ? Number(fallbackTier.conversion_price) : (rate > 1 ? Math.round(Number(fallbackTier.price) / rate) : Number(fallbackTier.price));
                    }
                    return Number(fallbackTier.price);
                }
            }

            // 2. Object format: { c1, c2, retail, ... }
            if (p.price_tiers && typeof p.price_tiers === 'object') {
                let key = 'c1';
                if (cleanChosen.includes('cấp 2') || cleanChosen.includes('c2')) key = 'c2';
                else if (cleanChosen.includes('bán lẻ') || cleanChosen.includes('retail')) key = 'retail';
                else if (cleanChosen.includes('dự án') || cleanChosen.includes('project')) key = 'project';
                else if (cleanChosen.includes('thợ') || cleanChosen.includes('mechanic')) key = 'mechanic';

                if (p.price_tiers[key] && Number(p.price_tiers[key]) > 0) {
                    const base = Number(p.price_tiers[key]);
                    return isConv && rate > 1 ? Math.round(base / rate) : base;
                }
            }

            // 3. Fallback: giá nhập + 10%
            if (isConv && Number(p.conversion_price) > 0) {
                return Number(p.conversion_price);
            }
            const baseCost = Number(p.cost_price || 0) * 1.1;
            if (isConv && rate > 1) {
                return Math.round(baseCost / rate);
            }
            return Math.round(baseCost);
        },

        selectProductForRow(index, p) {
            if (!p) return;
            const item = this.items[index];
            if (!item) return;

            item.product_id = p.id;
            item.sku = p.sku;
            item.name = p.name;
            item.searchQuery = `[${p.sku}] ${p.name}`;
            item.unit = p.unit || 'cái';

            item.originalPrice = Number(p.cost_price || 0);
            item.costPrice = Number(p.cost_price || 0);

            // Cài đặt VAT theo sản phẩm
            if (p.vat_rate !== undefined && p.vat_rate !== null && p.vat_rate !== '') {
                item.vat_percent = Number(p.vat_rate);
            } else {
                item.vat_percent = 10;
            }
            
            const effTier = (item.price_type && item.price_type !== 'Theo phiếu') ? item.price_type : (this.formData.dealer_tier || 'Nguyên Giá');
            item.price_type = effTier;
            item.unitPrice = this.getProductPrice(p, item.unit, effTier);
            item.appliedTierPolicy = effTier;
            item.quantity = item.quantity > 0 ? item.quantity : 1;

            if (this.bulkDiscountPercent !== '' && this.bulkDiscountPercent !== null && Number(this.bulkDiscountPercent) > 0) {
                const bPct = Math.min(100, Math.max(0, Number(this.bulkDiscountPercent) || 0));
                item._manualDiscount = true;
                item.discount_percent = bPct;
                const lineBase = (Number(item.quantity) || 1) * (Number(item.unitPrice) || 0);
                item.discount_amount = Math.round((lineBase * bPct) / 100);
            }

            this.activeProdDropdownIndex = null;
            this.updateLineTotal(index);
            this.calculateTotals();
        },

        onItemSearch(index, val) {
            if (!val) return;
            const item = this.items[index];
            if (!item) return;

            const lower = val.trim().toLowerCase();
            let matched = this.productsCatalog.find(p => 
                val.includes(`[${p.sku}]`) ||
                p.sku.toLowerCase() === lower || 
                p.name.toLowerCase() === lower ||
                `[${p.sku}] ${p.name}`.toLowerCase() === lower
            );

            if (!matched && lower.length >= 3) {
                matched = this.productsCatalog.find(p => 
                    p.sku.toLowerCase().includes(lower) || 
                    p.name.toLowerCase().includes(lower)
                );
            }

            if (matched) {
                const isAlreadySelected = (this.items || []).some((it, idx) => idx !== index && String(it.product_id) === String(matched.id));
                if (!isAlreadySelected) {
                    this.selectProductForRow(index, matched);
                }
            }
        },

        onItemPriceTypeChange(index) {
            const item = this.items[index];
            if (!item || !item.product_id) return;
            const prod = this.getProduct(item.product_id);
            if (!prod) return;

            const effTier = item.price_type || this.formData.dealer_tier || 'Nguyên Giá';
            item.unitPrice = this.getProductPrice(prod, item.unit, effTier);
            item.appliedTierPolicy = effTier;

            // Nếu đổi sang loại giá khác Nguyên Giá, xóa chiết khấu tự động của dòng này
            if (!this.isItemOriginalPriceTier(item) && item._autoPromoDiscount && !item._manualDiscount) {
                item.discount_percent = 0;
                item.discount_amount = 0;
                item._autoPromoDiscount = false;
                item._appliedPromoPolicyId = null;
            }

            this.updateLineTotal(index);
            this.calculateTotals();
        },

        addNewRow() {
            this.activeProdDropdownIndex = null;
            const currentTier = this.formData.dealer_tier || 'Nguyên Giá';
            this.items.push({
                id: '',
                itemId: '',
                product_id: '',
                sku: '',
                name: '',
                searchQuery: '',
                unit: '',
                quantity: 1,
                gift_quantity: 0,
                unitPrice: 0,
                originalPrice: 0,
                costPrice: 0,
                discount_percent: 0,
                discount_amount: 0,
                vat_percent: 10,
                vat_amount: 0,
                appliedTierPolicy: currentTier,
                price_type: currentTier,
                amount: 0,
                checked: true,
            });
            this.calculateTotals();
            this.$nextTick(() => {
                const nextIdx = this.items.length - 1;
                const nextInput = document.getElementById('row-product-input-' + nextIdx);
                if (nextInput) {
                    nextInput.focus();
                }
            });
        },

        scrollProdIntoView(index, pIdx) {
            this.$nextTick(() => {
                const el = document.getElementById('prod-opt-' + index + '-' + pIdx);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        removeItem(index) {
            const currentTier = this.formData.dealer_tier || 'Nguyên Giá';
            if (this.items.length <= 1) {
                // Keep 1 clean row if last is cleared
                this.items = [{
                    id: '',
                    itemId: '',
                    product_id: '',
                    sku: '',
                    name: '',
                    searchQuery: '',
                    unit: '',
                    quantity: 1,
                    gift_quantity: 0,
                    unitPrice: 0,
                    originalPrice: 0,
                    costPrice: 0,
                    discount_percent: 0,
                    discount_amount: 0,
                    vat_percent: 10,
                    vat_amount: 0,
                    appliedTierPolicy: currentTier,
                    price_type: currentTier,
                    amount: 0,
                    checked: true,
                }];
            } else {
                this.items.splice(index, 1);
            }
            this.calculateTotals();
        },

        recalculateItemPrices() {
            const currentTier = this.formData.dealer_tier || 'Nguyên Giá';
            const isOrigTier = this.isOriginalPriceTier(currentTier);
            this.items.forEach((it, idx) => {
                if (it.checked === false) return;
                if (it.isPromotionalGift) return;

                it.price_type = currentTier;
                it.appliedTierPolicy = currentTier;
                if (it.product_id) {
                    const prod = this.getProduct(it.product_id);
                    if (prod) {
                        it.unitPrice = this.getProductPrice(prod, it.unit, currentTier);
                        if (prod.vat_rate !== undefined && prod.vat_rate !== null && prod.vat_rate !== '') {
                            it.vat_percent = Number(prod.vat_rate);
                        }
                    }
                }
                // Nếu loại giá mới không phải Nguyên Giá, dọn dẹp chiết khấu tự động của dòng
                if (!isOrigTier && it._autoPromoDiscount && !it._manualDiscount) {
                    it.discount_percent = 0;
                    it.discount_amount = 0;
                    it._autoPromoDiscount = false;
                    it._appliedPromoPolicyId = null;
                }
                this.updateLineTotal(idx);
            });
            if (!isOrigTier && this._autoOrderDiscount && !this._manualOrderDiscount) {
                this.orderDiscountPercent = 0;
                this.formData.discount_amount = 0;
                this._autoOrderDiscount = false;
            }
            this.calculateTotals();
        },

        applyBulkDiscountPercent() {
            const val = this.bulkDiscountPercent;
            if (val === '' || val === null || val === undefined) return;
            const pct = Math.min(100, Math.max(0, Number(val) || 0));

            this.items.forEach((it, idx) => {
                if (it.isPromotionalGift || !it.product_id) return;
                it._manualDiscount = true;
                it.discount_percent = pct;
                const qty = Number(it.quantity) || 0;
                const price = Number(it.unitPrice) || 0;
                const lineBase = qty * price;
                it.discount_amount = Math.round((lineBase * pct) / 100);
                this.updateLineTotal(idx, 'from_percent');
            });
            this.calculateTotals();
        },

        onLineDiscountPercentChange(index) {
            const it = this.items[index];
            if (!it) return;
            it._manualDiscount = true;
            const qty = Number(it.quantity) || 0;
            const price = Number(it.unitPrice) || 0;
            const lineBase = qty * price;
            const pct = Math.min(100, Math.max(0, Number(it.discount_percent) || 0));
            it.discount_percent = pct;
            it.discount_amount = Math.round((lineBase * pct) / 100);

            this.updateLineTotal(index, 'from_percent');
        },

        onLineDiscountAmountChange(index) {
            const it = this.items[index];
            if (!it) return;
            it._manualDiscount = true;
            const qty = Number(it.quantity) || 0;
            const price = Number(it.unitPrice) || 0;
            const lineBase = qty * price;
            const amt = Math.max(0, Number(it.discount_amount) || 0);
            it.discount_amount = amt;
            if (lineBase > 0) {
                it.discount_percent = Math.round((Math.min(lineBase, amt) / lineBase) * 100 * 10) / 10;
            } else {
                it.discount_percent = 0;
            }

            this.updateLineTotal(index, 'from_amount');
        },

        updateLineTotal(index, source = 'from_percent') {
            const it = this.items[index];
            if (it) {
                const qty = Number(it.quantity) || 0;
                const price = Number(it.unitPrice) || 0;
                const lineBase = qty * price;

                let lineDiscount = 0;
                if (source === 'from_amount') {
                    lineDiscount = Math.min(lineBase, Math.max(0, Number(it.discount_amount) || 0));
                    it.discount_amount = lineDiscount;
                    if (lineBase > 0) {
                        it.discount_percent = Math.round((lineDiscount / lineBase) * 100 * 10) / 10;
                    }
                } else {
                    const discPercent = Math.min(100, Math.max(0, Number(it.discount_percent) || 0));
                    lineDiscount = Math.min(lineBase, (lineBase * discPercent) / 100);
                    it.discount_amount = Math.round(lineDiscount);
                }

                const lineAfterDiscount = Math.max(0, lineBase - lineDiscount);
                const vatPercent = Number(it.vat_percent) || 0;
                const lineVat = (lineAfterDiscount * vatPercent) / 100;
                it.vat_amount = Math.round(lineVat);

                it.amount = Math.round(lineAfterDiscount + lineVat);
            }
            this.calculateTotals();
        },

        onOrderDiscountPercentChange() {
            this._manualOrderDiscount = true;
            this._autoOrderDiscount = false;
            const pct = Number(this.orderDiscountPercent) || 0;
            this.formData.discount_amount = Math.round((this.totalWithVat * pct) / 100);
            this.calculateTotals();
        },

        onOrderDiscountAmountChange() {
            this._manualOrderDiscount = true;
            this._autoOrderDiscount = false;
            const amt = Number(this.formData.discount_amount) || 0;
            if (this.totalWithVat > 0) {
                this.orderDiscountPercent = Math.round((amt / this.totalWithVat) * 100 * 10) / 10;
            }
            this.calculateTotals();
        },

        increaseQty(index) {
            const item = this.items[index];
            if (!item) return;
            let q = Number(item.quantity) || 0;
            item.quantity = Math.max(1, q + 1);
            if (item.isPromotionalGift) {
                item._manualQty = true;
            }
            this.updateLineTotal(index);
        },

        decreaseQty(index) {
            const item = this.items[index];
            if (!item) return;
            let q = Number(item.quantity) || 0;
            item.quantity = Math.max(1, q - 1);
            if (item.isPromotionalGift) {
                item._manualQty = true;
            }
            this.updateLineTotal(index);
        },

        increaseGiftQty(index) {
            const item = this.items[index];
            if (!item || item.isPromotionalGift) return;
            item._manualGift = true;
            let q = Number(item.gift_quantity) || 0;
            item.gift_quantity = q + 1;
            this.onGiftQtyChange(index);
        },

        decreaseGiftQty(index) {
            const item = this.items[index];
            if (!item || item.isPromotionalGift) return;
            item._manualGift = true;
            let q = Number(item.gift_quantity) || 0;
            item.gift_quantity = Math.max(0, q - 1);
            this.onGiftQtyChange(index);
        },

        onGiftQtyChange(index) {
            const item = this.items[index];
            if (!item) return;
            item._manualGift = true;
            if (item.gift_quantity < 0 || isNaN(item.gift_quantity)) {
                item.gift_quantity = 0;
            }
            this.updateLineTotal(index, 'from_amount');
        },

        getPriceStep(price) {
            const p = Math.abs(Number(price) || 0);
            if (p < 50000) return 1000;
            if (p < 200000) return 5000;
            return 10000;
        },

        increasePrice(index) {
            const item = this.items[index];
            if (!item || item.isPromotionalGift) return;
            let p = Number(item.unitPrice) || 0;
            const step = this.getPriceStep(p);
            item.unitPrice = p + step;
            item.price_type = 'Tùy chỉnh';
            this.updateLineTotal(index);
        },

        decreasePrice(index) {
            const item = this.items[index];
            if (!item || item.isPromotionalGift) return;
            let p = Number(item.unitPrice) || 0;
            const step = this.getPriceStep(p);
            item.unitPrice = Math.max(0, p - step);
            item.price_type = 'Tùy chỉnh';
            this.updateLineTotal(index);
        },

        isCustomerEligibleForPolicy(policy) {
            if (!policy) return false;
            let custIds = policy.target_customer_ids;
            if (typeof custIds === 'string') {
                try { custIds = JSON.parse(custIds); } catch(e) { custIds = []; }
            }
            if (!Array.isArray(custIds) || custIds.length === 0) return true;
            const curDealerId = this.formData.dealer_id;
            if (!curDealerId) return false;
            return custIds.some(id => String(id) === String(curDealerId));
        },

        isProductInPolicy(policy, productId) {
            if (!policy || !productId) return false;
            let prodIds = policy.target_product_ids;
            if (typeof prodIds === 'string') {
                try { prodIds = JSON.parse(prodIds); } catch(e) { prodIds = []; }
            }
            if (!Array.isArray(prodIds) || prodIds.length === 0) return true;
            return prodIds.some(id => String(id) === String(productId));
        },

        isPolicyApplied(policy) {
            if (!policy) return false;
            return (this.appliedPromoPolicyIds || []).includes(policy.id);
        },

        isPolicyEligible(policy) {
            if (!policy) return false;
            if (!this.isCustomerEligibleForPolicy(policy)) return false;

            const isDiscount = this.isDiscountPolicy(policy);
            // Chính sách chiết khấu không được áp cho các loại giá khác loại Nguyên giá
            if (isDiscount && policy.apply_scope === 'order' && !this.isOriginalPriceTier(this.formData.dealer_tier)) {
                return false;
            }

            const minVal = Number(policy.min_order_value) || 0;
            const orderSubtotal = this.subtotalBeforeVat || 0;

            if (policy.policy_type === 'quantity') {
                const buyQty = Number(policy.buy_qty) || 0;
                if (buyQty <= 0) return false;
                return this.items.some(it => 
                    !it.isPromotionalGift && 
                    it.product_id && 
                    this.isProductInPolicy(policy, it.product_id) && 
                    (Number(it.quantity) || 0) >= buyQty
                );
            }

            if (policy.apply_scope === 'order') {
                return orderSubtotal >= minVal && orderSubtotal > 0;
            }

            if (policy.apply_scope === 'product') {
                return this.items.some(it => {
                    if (it.isPromotionalGift || !it.product_id) return false;
                    if (!this.isProductInPolicy(policy, it.product_id)) return false;
                    // Dòng sản phẩm phải là loại giá Nguyên Giá mới được áp chiết khấu
                    if (isDiscount && !this.isItemOriginalPriceTier(it)) return false;
                    const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                    return lineBase >= minVal && lineBase > 0;
                });
            }

            return false;
        },

        getPolicyConditionDesc(policy) {
            if (!policy) return '';
            const parts = [];
            if (policy.policy_type === 'quantity') {
                parts.push(`Mua ${Math.round(Number(policy.buy_qty) || 0)} tặng ${Math.round(Number(policy.free_qty) || 0)}`);
            } else if (policy.policy_type === 'percentage') {
                parts.push(`Chiết khấu ${Number(policy.discount_percent) || 0}%`);
            } else if (policy.policy_type === 'amount') {
                parts.push(`Giảm ${this.formatNumber(policy.discount_amount || 0)} đ`);
            }

            if (policy.apply_scope === 'order') {
                parts.push('cho toàn đơn');
            } else {
                parts.push('từng sản phẩm');
            }

            const minVal = Number(policy.min_order_value) || 0;
            if (minVal > 0) {
                parts.push(`(tối thiểu ${this.formatNumber(minVal)} đ)`);
            }
            return parts.join(' ');
        },

        getPolicyBenefitDesc(policy) {
            if (!policy) return 'Đã áp dụng';
            if (policy.policy_type === 'quantity') {
                return `Đã tặng quà (Mua ${Math.round(Number(policy.buy_qty) || 0)} tặng ${Math.round(Number(policy.free_qty) || 0)})`;
            }
            if (policy.policy_type === 'percentage') {
                return `Đã chiết khấu ${Number(policy.discount_percent) || 0}%`;
            }
            if (policy.policy_type === 'amount') {
                return `Đã giảm ${this.formatNumber(policy.discount_amount || 0)} đ`;
            }
            return 'Đã áp dụng';
        },

        getPolicyIneligibleReason(policy) {
            if (!policy) return '';
            if (this.isPolicyApplied(policy)) return '';
            if (!this.isCustomerEligibleForPolicy(policy)) {
                return 'Chỉ áp dụng đại lý chỉ định';
            }

            const isDiscount = this.isDiscountPolicy(policy);
            if (isDiscount && policy.apply_scope === 'order' && !this.isOriginalPriceTier(this.formData.dealer_tier)) {
                return 'Chỉ áp dụng loại giá Nguyên Giá';
            }

            const minVal = Number(policy.min_order_value) || 0;
            const orderSubtotal = this.subtotalBeforeVat || 0;
            if (policy.apply_scope === 'order' && minVal > 0 && orderSubtotal < minVal) {
                return `Cần mua thêm ${this.formatNumber(minVal - orderSubtotal)} đ`;
            }
            if (policy.policy_type === 'quantity') {
                const buyQty = Number(policy.buy_qty) || 0;
                const matchingItems = this.items.filter(it => !it.isPromotionalGift && it.product_id && this.isProductInPolicy(policy, it.product_id));
                if (matchingItems.length === 0) return 'Đơn chưa có SP áp dụng';
                if (matchingItems.some(it => (Number(it.quantity) || 0) >= buyQty)) {
                    return 'Đã áp dụng chính sách khác';
                }
                return `Cần mua tối thiểu ${Math.round(buyQty)} SP`;
            }
            if (policy.apply_scope === 'product') {
                const matchingItems = this.items.filter(it => !it.isPromotionalGift && it.product_id && this.isProductInPolicy(policy, it.product_id));
                if (matchingItems.length === 0) return 'Đơn chưa có SP áp dụng';
                if (isDiscount) {
                    const hasOriginalTier = matchingItems.some(it => this.isItemOriginalPriceTier(it));
                    if (!hasOriginalTier) {
                        return 'Chỉ áp dụng SP loại giá Nguyên Giá';
                    }
                }
                const hasReachingValue = matchingItems.some(it => {
                    if (isDiscount && !this.isItemOriginalPriceTier(it)) return false;
                    const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                    return lineBase >= minVal && lineBase > 0;
                });
                if (hasReachingValue) {
                    return 'Đã áp dụng chính sách khác';
                }
                if (minVal > 0) {
                    return `Dòng SP chưa đạt ${this.formatNumber(minVal)} đ`;
                }
            }
            return 'Chưa đủ điều kiện';
        },

        checkAndApplyPromotions(force = false) {
            if (!this.autoApplyPromotions && !force) return;
            if (this._isApplyingPromotions) return;
            this._isApplyingPromotions = true;

            try {
                const notices = [];
                const appliedPolicyIds = [];

                // 1. Dọn dẹp các dòng quà tặng cũ do hệ thống tự sinh (chỉ dọn nếu chưa được sửa tay)
                this.items = this.items.filter(it => !it.isPromotionalGift || it._manualQty);
                this.items.forEach(it => {
                    if (it._autoPromoGift && !it._manualGift) {
                        it.gift_quantity = 0;
                        it._autoPromoGift = false;
                    }
                    if (it._autoPromoDiscount && !it._manualDiscount) {
                        it.discount_percent = 0;
                        it.discount_amount = 0;
                        it._autoPromoDiscount = false;
                    }
                    if (!it._manualGift && !it._manualDiscount) {
                        it._appliedPromoPolicyId = null;
                    }
                });

                // Reset chiết khấu đơn hàng tự động cũ nếu có
                if (this._autoOrderDiscount && !this._manualOrderDiscount) {
                    this.orderDiscountPercent = 0;
                    this.formData.discount_amount = 0;
                    this._autoOrderDiscount = false;
                }

                // 2. Xét các chính sách Mua X tặng Y (policy_type === 'quantity')
                for (const policy of this.salesPoliciesCatalog) {
                    if (policy.policy_type !== 'quantity') continue;
                    if (!this.isCustomerEligibleForPolicy(policy)) continue;

                    const buyQty = Number(policy.buy_qty) || 0;
                    const freeQty = Number(policy.free_qty) || 0;
                    if (buyQty <= 0 || freeQty <= 0) continue;

                    // Duyệt các dòng sản phẩm đạt điều kiện mua
                    const currentLen = this.items.length;
                    for (let i = 0; i < currentLen; i++) {
                        const item = this.items[i];
                        if (item.isPromotionalGift || !item.product_id) continue;
                        if (!this.isProductInPolicy(policy, item.product_id)) continue;

                        // QUY TẮC: Dòng này đã nhận 1 chính sách hoặc đã có chiết khấu thì không tự động nhận thêm quà tặng
                        if (item._appliedPromoPolicyId && item._appliedPromoPolicyId !== policy.id) continue;
                        if ((Number(item.discount_percent) > 0 || Number(item.discount_amount) > 0) && !item._autoPromoGift) continue;

                        const itemQty = Number(item.quantity) || 0;
                        if (itemQty >= buyQty) {
                            const freeMultiplier = Math.floor(itemQty / buyQty);
                            const totalFreeQty = freeMultiplier * freeQty;

                            // Xác định sản phẩm quà tặng
                            let freeProd = policy.free_product || null;
                            if (!freeProd && policy.free_product_id) {
                                freeProd = this.getProduct(policy.free_product_id);
                            }
                            if (!freeProd) {
                                freeProd = this.getProduct(item.product_id);
                            }

                            const isSameProduct = !policy.free_product_id || String(policy.free_product_id) === String(item.product_id);

                            if (isSameProduct) {
                                // Tặng chính sản phẩm đó -> điền trực tiếp vào cột SL TẶNG của dòng (nếu chưa sửa tay)
                                if (!item._manualGift) {
                                    item.gift_quantity = totalFreeQty;
                                    item._autoPromoGift = true;
                                }
                                item._appliedPromoPolicyId = policy.id;
                                if (!item._manualDiscount) {
                                    item.discount_percent = 0;
                                    item.discount_amount = 0;
                                    item._autoPromoDiscount = false;
                                }
                                this.updateLineTotal(i, 'from_amount');
                                notices.push(`Tặng kèm ${item.gift_quantity} ${item.unit || ''} ${item.name} (theo ${policy.code})`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            } else {
                                // Tặng sản phẩm khác -> thêm dòng quà tặng riêng biệt
                                item._appliedPromoPolicyId = policy.id;
                                if (!item._manualDiscount) {
                                    item.discount_percent = 0;
                                    item.discount_amount = 0;
                                    item._autoPromoDiscount = false;
                                }
                                this.updateLineTotal(i, 'from_amount');

                                const freeSku = freeProd?.sku || 'KM-PVC';
                                const freeName = (freeProd?.name || 'Sản phẩm khuyến mại');
                                const freeUnit = freeProd?.unit || 'cái';

                                const existingGiftRow = this.items.find(it => it.isPromotionalGift && it.promoPolicyId === policy.id);
                                if (existingGiftRow) {
                                    if (!existingGiftRow._manualQty) {
                                        existingGiftRow.quantity = totalFreeQty;
                                    }
                                } else {
                                    this.items.push({
                                        id: '',
                                        itemId: freeProd?.id ? String(freeProd.id) : String(item.product_id),
                                        product_id: freeProd?.id || item.product_id,
                                        sku: freeSku,
                                        name: freeName,
                                        searchQuery: `[${freeSku}] ${freeName}`,
                                        unit: freeUnit,
                                        quantity: totalFreeQty,
                                        gift_quantity: 0,
                                        unitPrice: 0,
                                        originalPrice: 0,
                                        costPrice: Number(freeProd?.cost_price || 0),
                                        discount_percent: 0,
                                        discount_amount: 0,
                                        vat_percent: 0,
                                        vat_amount: 0,
                                        appliedTierPolicy: `[TẶNG KÈM] ${policy.name}`,
                                        price_type: 'Khuyến mại',
                                        amount: 0,
                                        checked: true,
                                        isPromotionalGift: true,
                                        promoPolicyId: policy.id,
                                    });
                                }

                                notices.push(`Tặng ${totalFreeQty} ${freeUnit} ${freeName} (theo ${policy.code})`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            }
                        }
                    }
                }

                // 3. Xét các chính sách Chiết khấu % (policy_type === 'percentage')
                // QUY TẮC: Chính sách chiết khấu không được áp cho các loại giá khác loại Nguyên giá
                for (const policy of this.salesPoliciesCatalog) {
                    if (policy.policy_type !== 'percentage') continue;
                    if (!this.isCustomerEligibleForPolicy(policy)) continue;

                    const discPct = Number(policy.discount_percent) || 0;
                    if (discPct <= 0) continue;
                    const minVal = Number(policy.min_order_value) || 0;

                    if (policy.apply_scope === 'order') {
                        // Chỉ áp dụng toàn đơn khi loại giá đơn hàng là Nguyên Giá
                        if (!this.isOriginalPriceTier(this.formData.dealer_tier)) continue;

                        if (this.subtotalBeforeVat >= minVal && this.subtotalBeforeVat > 0) {
                            if (discPct > (this.orderDiscountPercent || 0) || force) {
                                this.orderDiscountPercent = discPct;
                                const amt = Math.round((this.totalWithVat * discPct) / 100);
                                this.formData.discount_amount = amt;
                                this._autoOrderDiscount = true;
                                notices.push(`Giảm ${discPct}% toàn đơn (${policy.name})`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            }
                        }
                    } else if (policy.apply_scope === 'product') {
                        this.items.forEach((it, idx) => {
                            if (it.isPromotionalGift || !it.product_id) return;
                            if (!this.isProductInPolicy(policy, it.product_id)) return;

                            // Chỉ áp dụng khi dòng sản phẩm có loại giá là Nguyên Giá
                            if (!this.isItemOriginalPriceTier(it)) return;

                            // QUY TẮC: Nếu dòng này đã nhận 1 chính sách khác hoặc đã có quà tặng thì KHÔNG tự động áp chiết khấu
                            if (it._appliedPromoPolicyId && it._appliedPromoPolicyId !== policy.id) return;
                            if (Number(it.gift_quantity) > 0 && !it._manualDiscount) return;

                            const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                            if (lineBase >= minVal) {
                                it._appliedPromoPolicyId = policy.id;
                                if (!it._manualDiscount) {
                                    it._autoPromoDiscount = true;
                                    it.discount_percent = discPct;
                                    const lineDiscount = (lineBase * discPct) / 100;
                                    it.discount_amount = Math.round(lineDiscount);
                                    const lineAfterDiscount = Math.max(0, lineBase - lineDiscount);
                                    const vatPercent = Number(it.vat_percent) || 0;
                                    it.vat_amount = Math.round((lineAfterDiscount * vatPercent) / 100);
                                    it.amount = Math.round(lineAfterDiscount + it.vat_amount);
                                }
                                notices.push(`Giảm ${discPct}% sp ${it.sku || it.name} (${policy.code})`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            }
                        });
                    }
                }

                // 4. Xét các chính sách Giảm tiền mặt (policy_type === 'amount')
                // QUY TẮC: Chính sách chiết khấu không được áp cho các loại giá khác loại Nguyên giá
                for (const policy of this.salesPoliciesCatalog) {
                    if (policy.policy_type !== 'amount') continue;
                    if (!this.isCustomerEligibleForPolicy(policy)) continue;

                    const discAmt = Number(policy.discount_amount) || 0;
                    if (discAmt <= 0) continue;
                    const minVal = Number(policy.min_order_value) || 0;

                    if (policy.apply_scope === 'order') {
                        // Chỉ áp dụng toàn đơn khi loại giá đơn hàng là Nguyên Giá
                        if (!this.isOriginalPriceTier(this.formData.dealer_tier)) continue;

                        if (this.subtotalBeforeVat >= minVal && this.subtotalBeforeVat > 0) {
                            this.formData.discount_amount = discAmt;
                            this._autoOrderDiscount = true;
                            if (this.totalWithVat > 0) {
                                this.orderDiscountPercent = Math.round((discAmt / this.totalWithVat) * 100 * 10) / 10;
                            }
                            notices.push(`Giảm ${this.formatNumber(discAmt)} đ toàn đơn (${policy.name})`);
                            if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                        }
                    } else if (policy.apply_scope === 'product') {
                        this.items.forEach((it, idx) => {
                            if (it.isPromotionalGift || !it.product_id) return;
                            if (!this.isProductInPolicy(policy, it.product_id)) return;

                            // Chỉ áp dụng khi dòng sản phẩm có loại giá là Nguyên Giá
                            if (!this.isItemOriginalPriceTier(it)) return;

                            // QUY TẮC: Nếu dòng này đã nhận 1 chính sách khác hoặc đã có quà tặng hoặc đã có chiết khấu thì KHÔNG tự động áp giảm tiền
                            if (it._appliedPromoPolicyId && it._appliedPromoPolicyId !== policy.id) return;
                            if ((Number(it.gift_quantity) > 0 || Number(it.discount_percent) > 0) && !it._manualDiscount) return;

                            const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                            if (lineBase >= minVal) {
                                it._appliedPromoPolicyId = policy.id;
                                if (!it._manualDiscount) {
                                    it._autoPromoDiscount = true;
                                    it.discount_amount = discAmt;
                                    if (lineBase > 0) {
                                        it.discount_percent = Math.round((discAmt / lineBase) * 100 * 10) / 10;
                                    }
                                    const vatPct = Number(it.vat_percent) || 0;
                                    const afterDisc = Math.max(0, lineBase - discAmt);
                                    it.vat_amount = Math.round((afterDisc * vatPct) / 100);
                                    it.amount = Math.round(afterDisc + it.vat_amount);
                                }
                                notices.push(`Giảm ${this.formatNumber(discAmt)} đ cho sp ${it.sku || it.name}`);
                                if (!appliedPolicyIds.includes(policy.id)) appliedPolicyIds.push(policy.id);
                            }
                        });
                    }
                }

                this.appliedPromotionNotices = notices;
                this.appliedPromoPolicyIds = appliedPolicyIds;
            } finally {
                this._isApplyingPromotions = false;
            }
        },

        handleOrderTypeChange() {
            if (this.formData.order_type === 'backorder') {
                this.formData.status = 'Giao sau';
                this.recalculateItemPrices();
                this.checkAndApplyPromotions(true);
            } else {
                if (this.formData.status === 'Giao sau') {
                    this.formData.status = 'Báo giá';
                }
                this.recalculateItemPrices();
                this.checkAndApplyPromotions(true);
            }
            this.calculateTotals();
        },

        async checkPendingMergeOrders() {
            const dealerId = this.formData.dealer_id;
            const custName = this.formData.customer_name;
            const custPhone = this.formData.customer_phone;
            const excludeId = this.orderId || '';

            if (!dealerId && !custName && !custPhone) {
                this.pendingMergeOrders = [];
                return;
            }

            this.isLoadingMergeOrders = true;
            try {
                const params = new URLSearchParams();
                if (dealerId) params.append('dealer_id', dealerId);
                if (custName) params.append('customer_name', custName);
                if (custPhone) params.append('customer_phone', custPhone);
                if (excludeId) params.append('exclude_id', excludeId);

                const res = await fetch(`/api/orders/pending-debts-and-borrows?${params.toString()}`);
                const data = await res.json();
                if (data.success && Array.isArray(data.orders)) {
                    // Loại các đơn đã được gộp trong phiên này
                    this.pendingMergeOrders = data.orders.filter(o => !this.mergedOrderIds.includes(o.id));
                } else {
                    this.pendingMergeOrders = [];
                }
            } catch(err) {
                console.error('Error fetching pending debt/borrow orders:', err);
            } finally {
                this.isLoadingMergeOrders = false;
            }
        },

        mergeOrder(order) {
            if (!order || !order.items || order.items.length === 0) {
                alert('Đơn hàng này không có sản phẩm để gộp.');
                return;
            }

            // Nếu dòng đầu tiên đang trống hoàn toàn, loại bỏ dòng trống đó
            if (this.items.length === 1 && !this.items[0].product_id && !this.items[0].name) {
                this.items = [];
            }

            let mergedCount = 0;
            for (const it of order.items) {
                const prodId = it.product_id;
                const price = Number(it.unit_price) || 0;
                const qty = Number(it.quantity) || 0;

                // Tìm dòng hiện có cùng sản phẩm & cùng đơn giá
                const existing = this.items.find(row => row.product_id == prodId && Number(row.unitPrice) === price && !row.isPromotionalGift);
                if (existing) {
                    existing.quantity = (Number(existing.quantity) || 0) + qty;
                    const idx = this.items.indexOf(existing);
                    this.updateLineTotal(idx, 'quantity');
                } else {
                    const newRow = {
                        id: '',
                        itemId: String(prodId),
                        product_id: prodId,
                        sku: it.sku || '',
                        name: it.name || '',
                        searchQuery: `[${it.sku || ''}] ${it.name || ''}`,
                        unit: it.unit || 'cái',
                        quantity: qty,
                        gift_quantity: 0,
                        unitPrice: price,
                        originalPrice: price,
                        costPrice: 0,
                        discount_percent: Number(it.discount_percent) || 0,
                        discount_amount: Number(it.discount_amount) || 0,
                        vat_percent: Number(it.vat_percent) || 0,
                        vat_amount: Number(it.vat_amount) || 0,
                        appliedTierPolicy: it.price_type || 'Theo phiếu',
                        price_type: it.price_type || 'Theo phiếu',
                        amount: Number(it.amount) || (qty * price),
                        checked: true,
                    };
                    this.items.push(newRow);
                    this.updateLineTotal(this.items.length - 1);
                }
                mergedCount++;
            }

            // Ghi chú gộp đơn
            const mergeNote = `[Gộp từ ${order.order_code} (${order.type_label})]`;
            if (!this.formData.notes) {
                this.formData.notes = mergeNote;
            } else if (!this.formData.notes.includes(order.order_code)) {
                this.formData.notes = this.formData.notes.trim() + ' ' + mergeNote;
            }

            // Đánh dấu id đơn đã gộp
            if (!this.mergedOrderIds.includes(order.id)) {
                this.mergedOrderIds.push(order.id);
            }

            // Loại đơn này khỏi danh sách gợi ý
            this.pendingMergeOrders = this.pendingMergeOrders.filter(o => o.id !== order.id);

            this.calculateTotals();
            alert(`Đã gộp thành công ${mergedCount} mặt hàng từ đơn ${order.order_code} vào đơn hiện tại!`);
        },

        mergeAllPendingOrders() {
            if (!this.pendingMergeOrders || this.pendingMergeOrders.length === 0) return;
            const count = this.pendingMergeOrders.length;
            const toMerge = [...this.pendingMergeOrders];
            for (const ord of toMerge) {
                this.mergeOrder(ord);
            }
            this.showMergeModal = false;
        },

        calculateTotals() {
            let rawSubtotal = 0;
            let vatSum = 0;

            this.items.forEach(it => {
                if (!it.checked) return;
                const qty = Number(it.quantity) || 0;
                const price = Number(it.unitPrice) || 0;
                const discAmount = Number(it.discount_amount) || 0;
                const vatAmount = Number(it.vat_amount) || 0;

                rawSubtotal += Math.max(0, (qty * price) - discAmount);
                vatSum += vatAmount;
            });

            this.subtotalBeforeVat = Math.round(rawSubtotal);
            this.totalVat = Math.round(vatSum);
            this.totalWithVat = Math.round(this.subtotalBeforeVat + this.totalVat);

            const overallDiscount = Number(this.formData.discount_amount) || 0;
            this.finalTotal = Math.max(0, this.totalWithVat - overallDiscount);

            this.subtotal = this.subtotalBeforeVat;
            this.formData.tax_amount = this.totalVat;
            this.totalAmount = this.finalTotal;

            const curT = this.tickets[this.activeTicketIndex];
            if (curT) {
                curT.subtotalBeforeVat = this.subtotalBeforeVat;
                curT.totalVat = this.totalVat;
                curT.totalWithVat = this.totalWithVat;
                curT.finalTotal = this.finalTotal;
                curT.orderDiscountPercent = this.orderDiscountPercent;
                curT.bulkDiscountPercent = this.bulkDiscountPercent;
            }

            // Tự động kiểm tra và áp dụng khuyến mại khi giỏ hàng hoặc thông tin đơn thay đổi
            if (this.autoApplyPromotions && !this._isApplyingPromotions) {
                this.checkAndApplyPromotions();
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0));
        },

        confirmClose() {
            this.closeTicket(this.activeTicketIndex);
        },

        resetForm() {
            this.addNewTicket();
        },

        async submitOrder(andPrint = false) {
            if (this.isReadonly) {
                alert('Đơn hàng đang ở chế độ Chỉ đọc, không thể chỉnh sửa.');
                return;
            }
            if (!this.formData.customer_name) {
                this.errorMessage = 'Vui lòng nhập tên khách hàng hoặc chọn đại lý.';
                return;
            }

            const validItems = this.items.filter(it => it.name || it.sku || it.product_id);
            if (validItems.length === 0) {
                this.errorMessage = 'Vui lòng chọn ít nhất một sản phẩm vào đơn hàng.';
                return;
            }

            // 1. Cảnh báo xác nhận khi vượt quá hạn mức công nợ
            if (this.isDebtOverLimit()) {
                const dealer = this.getSelectedDealer();
                const curDebt = Number(dealer?.current_debt || 0);
                const limit = Number(dealer?.credit_limit || 0);
                const projDebt = this.getProjectedDebt();
                const excess = this.getDebtExcessAmount();
                const confirmMsg = 
                    `⚠️ CẢNH BÁO VƯỢT QUÁ HẠN MỨC CÔNG NỢ!\n\n` +
                    `Khách hàng: ${dealer?.name || this.formData.customer_name}\n` +
                    `• Hạn mức công nợ: ${this.formatNumber(limit)} đ\n` +
                    `• Nợ hiện tại: ${this.formatNumber(curDebt)} đ\n` +
                    `• Giá trị đơn này: ${this.formatNumber(this.finalTotal)} đ\n` +
                    `• Dự kiến nợ sau đơn: ${this.formatNumber(projDebt)} đ\n` +
                    `-----------------------------------------\n` +
                    `==> VƯỢT HẠN MỨC: +${this.formatNumber(excess)} đ\n\n` +
                    `Bạn có chắc chắn muốn tiếp tục lưu đơn hàng này không?`;
                if (!confirm(confirmMsg)) {
                    return;
                }
            }

            // 2. Cảnh báo xác nhận khi vượt quá tồn kho thực tế của kho đã chọn
            const overStockList = this.getOverStockItems();
            if (overStockList.length > 0) {
                const targetWh = this.formData.warehouse || 'Kho xuất';
                const itemsDesc = overStockList.map((it, i) => {
                    const avail = this.getItemAvailableStockInUnit(it);
                    const diff = this.getItemOverStockQty(it);
                    const prod = this.getProduct(it.product_id);
                    const totalAcrossWh = prod ? Number(prod.stock_quantity) || 0 : avail;
                    return `${i + 1}. [${it.sku || 'SP'}] ${it.name}: Đặt ${it.quantity} ${it.unit} (Tại kho "${targetWh}" chỉ còn ${this.formatNumber(avail)} ${it.unit} -> Thiếu ${this.formatNumber(diff)} ${it.unit} | Tổng toàn công ty: ${this.formatNumber(totalAcrossWh)} ${it.unit})`;
                }).join('\n');
                const confirmMsg = 
                    `⚠️ CẢNH BÁO THIẾU TỒN KHO TẠI [${targetWh.toUpperCase()}]!\n\n` +
                    `Có ${overStockList.length} sản phẩm trong đơn vượt quá tồn kho khả dụng tại kho này:\n` +
                    `${itemsDesc}\n\n` +
                    `Bạn có chắc chắn muốn tiếp tục lưu đơn (chấp nhận xuất âm kho này / tạo đơn nợ hàng) không?`;
                if (!confirm(confirmMsg)) {
                    return;
                }
            }

            this.saving = true;
            this.errorMessage = '';

            const payload = {
                id: this.orderId ? this.orderId : undefined,
                orderCode: this.formData.order_code || undefined,
                orderDate: this.formData.order_date,
                order_date: this.formData.order_date,
                deliveryDate: this.formData.delivery_date,
                delivery_date: this.formData.delivery_date,
                pickingDate: this.formData.picking_date,
                picking_date: this.formData.picking_date,
                orderType: this.formData.order_type || 'normal',
                dealerId: this.formData.dealer_id || null,
                dealerName: this.formData.customer_name,
                customer_name: this.formData.customer_name,
                customerPhone: this.formData.customer_phone,
                customer_phone: this.formData.customer_phone,
                address: this.formData.customer_address,
                customer_address: this.formData.customer_address,
                warehouse: this.formData.warehouse,
                paymentMethod: this.formData.payment_method,
                payment_method: this.formData.payment_method,
                status: this.formData.status,
                notes: this.formData.notes,
                subtotal: this.subtotal,
                discountAmount: this.formData.discount_amount,
                taxAmount: this.formData.tax_amount,
                totalAmount: this.totalAmount,
                items: validItems.map(it => ({
                    id: it.id || undefined,
                    itemId: it.product_id,
                    product_id: it.product_id,
                    sku: it.sku,
                    name: it.name,
                    unit: it.unit,
                    quantity: it.quantity,
                    gift_quantity: Number(it.gift_quantity) || 0,
                    giftQuantity: Number(it.gift_quantity) || 0,
                    discount_percent: Number(it.discount_percent) || 0,
                    discount_amount: Number(it.discount_amount) || 0,
                    vat_percent: Number(it.vat_percent) || 0,
                    vat_amount: Number(it.vat_amount) || 0,
                    unitPrice: it.unitPrice,
                    unit_price: it.unitPrice,
                    originalPrice: it.originalPrice,
                    costPrice: it.costPrice,
                    appliedTierPolicy: it.price_type || it.appliedTierPolicy,
                    amount: it.amount,
                }))
            };

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
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
                if (data.success || res.ok) {
                    // Phát tín hiệu đồng bộ tức thì cho các tab khác (đặc biệt là trang Soạn hàng /inventory/picking)
                    const finalOrdId = data.order?.id || data.order_id || payload.id;
                    const finalOrdCode = data.order?.orderCode || payload.orderCode || this.formData.order_code;
                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({
                            type: 'ORDER_SAVED',
                            orderId: finalOrdId,
                            orderCode: finalOrdCode,
                            timestamp: Date.now()
                        });
                    } catch(e) {}
                    try {
                        localStorage.setItem('erp_order_sync_trigger', JSON.stringify({
                            time: Date.now(),
                            type: 'ORDER_SAVED',
                            orderCode: finalOrdCode,
                            orderId: finalOrdId
                        }));
                    } catch(e) {}

                    // Nếu có đơn giao sau được gộp vào đơn này, cập nhật trạng thái đơn đó thành "Đã gộp đơn"
                    if (this.mergedOrderIds && this.mergedOrderIds.length > 0) {
                        try {
                            await fetch('/api/orders/mark-merged', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token || ''
                                },
                                body: JSON.stringify({
                                    merged_order_ids: this.mergedOrderIds,
                                    target_order_code: finalOrdCode
                                })
                            });
                        } catch(mErr) {
                            console.error('Error marking orders as merged:', mErr);
                        }
                    }

                    if (andPrint) {
                        alert('Lưu đơn thành công! Đang chuyển sang chế độ in chứng từ...');
                        this.printCurrentOrder();
                    } else {
                        alert('Lưu đơn hàng thành công!');
                    }

                    const savedIdx = this.activeTicketIndex;
                    if (this.tickets.length > 1) {
                        this.tickets.splice(savedIdx, 1);
                        this.switchTicket(Math.min(savedIdx, this.tickets.length - 1));
                    } else {
                window.location.href = '/orders';
                    }
                } else {
                    this.errorMessage = data.message || 'Lưu đơn thất bại, vui lòng kiểm tra lại.';
                }
            } catch (err) {
                console.error(err);
                this.errorMessage = 'Lỗi kết nối máy chủ: ' + err.message;
            } finally {
                this.saving = false;
            }
        },

        // ─── Helpers: Giai đoạn soạn hàng & Xử lý thiếu hàng ──────
        isPickingStage() {
            const st = this.formData.status || '';
            return ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong',
                    'Kiểm hàng', 'Đã kiểm hàng', 'Kho báo thiếu hàng'].includes(st);
        },

        isItemShortage(item) {
            if (!item || (!item.name && !item.sku && !item.product_id)) return false;
            const req = Number(item.quantity || 0) + Number(item.gift_quantity || 0);
            if (req <= 0) return false;
            const picked = Number(item.picked_qty || 0);

            // QUAN TRỌNG: Nếu kho đã bốc >= số lượng đặt (đã bốc đủ hoặc đã cắt đơn theo bốc), CHẮC CHẮN KHÔNG THIẾU
            if (picked >= req) return false;

            return (item.item_status === 'Thiếu hàng') ||
                   Boolean(item.shortage_reason) ||
                   (picked < req && (picked > 0 || this.formData.shortage_reported || this.formData.status === 'Kho báo thiếu hàng'));
        },

        getShortageQty(item) {
            if (!item) return 0;
            const req = Number(item.quantity || 0) + Number(item.gift_quantity || 0);
            const picked = Number(item.picked_qty || 0);
            return Math.max(0, req - picked);
        },

        getShortageItems() {
            return (this.items || []).filter(it => (it.name || it.sku || it.product_id) && this.isItemShortage(it));
        },

        openShortageModal(item, index) {
            this.shortageModal = { show: true, item: item, index: index };
        },

        // ─── Cắt SL theo số đã bốc (cho 1 món đơn lẻ) ─────────────
        cutToPickedQty() {
            const { item, index } = this.shortageModal;
            if (!item) return;
            const picked = Number(item.picked_qty || 0);
            if (picked <= 0) {
                if (!confirm('Số lượng kho đã bốc là 0. Bạn có chắc muốn cắt số lượng sản phẩm này về 0?')) return;
            }
            // Cắt quantity xuống bằng picked, gift_quantity về 0, xóa trạng thái thiếu
            this.items[index].quantity = picked;
            this.items[index].gift_quantity = 0;
            this.items[index].shortage_reason = '';
            this.items[index].item_status = picked > 0 ? 'Đủ hàng' : 'Chờ bốc';
            this.items[index].is_picked = picked > 0;
            this.updateLineTotal(index);
            this.calculateTotals();
            this.shortageModal.show = false;
            alert('Đã cắt số lượng [' + (item.name || '') + '] xuống còn ' + picked + ' ' + (item.unit || '') + '.\n\nNhớ bấm "Lưu (F9)" để xác nhận!');
        },

        // ─── Cắt TẤT CẢ sản phẩm thiếu theo SL kho đã bốc (CẮT TỔNG) ───
        cutAllToPickedQty() {
            const shortageList = this.getShortageItems();
            if (shortageList.length === 0) {
                alert('Không có sản phẩm nào đang bị thiếu!');
                return;
            }

            const zeroCount = shortageList.filter(it => Number(it.picked_qty || 0) <= 0).length;
            let confirmMsg = 'Xác nhận CẮT TẤT CẢ ' + shortageList.length + ' sản phẩm thiếu theo số lượng kho thực tế đã bốc?';
            if (zeroCount > 0) {
                confirmMsg += '\n\n(Lưu ý: Có ' + zeroCount + ' sản phẩm kho chưa bốc được cái nào - SL sẽ về 0)';
            }

            if (!confirm(confirmMsg)) return;

            let count = 0;
            this.items.forEach((it, index) => {
                if (this.isItemShortage(it)) {
                    it.quantity = Math.max(0, Number(it.picked_qty || 0));
                    it.gift_quantity = 0;
                    it.shortage_reason = '';
                    it.item_status = it.quantity > 0 ? 'Đủ hàng' : 'Chờ bốc';
                    it.is_picked = it.quantity > 0;
                    this.updateLineTotal(index);
                    count++;
                }
            });

            this.calculateTotals();
            if (this.shortageModal) this.shortageModal.show = false;
            alert('✅ ĐÃ CẮT TỔNG ' + count + ' SẢN PHẨM THIẾU!\n\nSố lượng đã được chỉnh về đúng số kho đã bốc.\nVui lòng kiểm tra lại tổng tiền và bấm nút "LƯU (F9)" để lưu thay đổi.');
        },

        // ─── Tạo đơn nợ hàng mới cho 1 món đơn lẻ ─────────────────
        async createBackOrder() {
            const { item, index } = this.shortageModal;
            if (!item) return;
            const picked      = Number(item.picked_qty || 0);
            const needed      = Number(item.quantity || 0) + Number(item.gift_quantity || 0);
            const shortage    = Math.max(0, needed - picked);
            if (shortage <= 0) { alert('Không có hàng thiếu!'); return; }
            if (picked <= 0) {
                if (!confirm('Kho chưa bốc được cái nào. Vẫn muốn tạo đơn nợ hàng?')) return;
            }

            this.backorderSaving = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                // 1. Lưu đơn hiện tại với SL đã bốc (cắt)
                const currentItems = this.items.map((it, i) => {
                    if (i === index) {
                        return { 
                            ...it, 
                            quantity: picked > 0 ? picked : it.quantity, 
                            gift_quantity: 0,
                            shortage_reason: '',
                            item_status: picked > 0 ? 'Đủ hàng' : 'Chờ bốc',
                            is_picked: picked > 0
                        };
                    }
                    return it;
                }).filter(it => it.name || it.sku || it.product_id);

                const saveRes = await fetch('/api/orders/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        id: this.orderId || undefined,
                        orderCode: this.formData.order_code || undefined,
                        dealerName: this.formData.customer_name,
                        customer_name: this.formData.customer_name,
                        customerPhone: this.formData.customer_phone,
                        address: this.formData.customer_address,
                        warehouse: this.formData.warehouse,
                        paymentMethod: this.formData.payment_method,
                        payment_method: this.formData.payment_method,
                        status: this.formData.status === 'Kho báo thiếu hàng' ? 'Soạn hàng' : this.formData.status,
                        shortage_reported: false,
                        shortageReported: false,
                        notes: (this.formData.notes || '') + '\n[Đã tách phần thiếu sang phiếu giao sau lúc ' + new Date().toLocaleTimeString('vi-VN') + ']',
                        subtotal: this.subtotal,
                        totalAmount: this.totalAmount,
                        items: currentItems.map(it => ({
                            id: it.id || undefined,
                            itemId: it.product_id, product_id: it.product_id,
                            sku: it.sku, name: it.name, unit: it.unit,
                            quantity: it.quantity, gift_quantity: Number(it.gift_quantity)||0,
                            unitPrice: it.unitPrice, amount: it.amount,
                            appliedTierPolicy: it.price_type || it.appliedTierPolicy,
                            shortage_reason: '',
                            item_status: Number(it.quantity) > 0 ? 'Đủ hàng' : 'Chờ bốc',
                            is_picked: Number(it.quantity) > 0,
                        }))
                    })
                });
                const saveData = await saveRes.json();
                if (!saveData.success && !saveRes.ok) throw new Error(saveData.message || 'Lỗi lưu đơn gốc');

                // 2. Tạo đơn nợ hàng mới cho phần thiếu
                const backOrderItems = [{
                    itemId: item.product_id, product_id: item.product_id,
                    sku: item.sku, name: item.name, unit: item.unit,
                    quantity: shortage, gift_quantity: 0,
                    unitPrice: item.unitPrice, amount: shortage * Number(item.unitPrice || 0),
                    appliedTierPolicy: item.price_type || item.appliedTierPolicy,
                }];
                const parentCode = this.formData.order_code || this.orderId || '';
                const backRes = await fetch('/api/orders/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        dealerName: this.formData.customer_name,
                        customer_name: this.formData.customer_name,
                        customerPhone: this.formData.customer_phone,
                        address: this.formData.customer_address,
                        warehouse: this.formData.warehouse,
                        paymentMethod: this.formData.payment_method,
                        payment_method: this.formData.payment_method,
                        orderType: 'backorder',
                        order_type: 'backorder',
                        status: 'Giao sau',
                        deliveryDate: null,
                        delivery_date: null,
                        pickingDate: null,
                        picking_date: null,
                        notes: '[ĐƠN GIAO SAU] Tách từ đơn gốc ' + parentCode + ' do kho thiếu hàng.\n' + (item.name || '') + ' thiếu ' + shortage + ' ' + (item.unit || ''),
                        parentOrderCode: parentCode,
                        items: backOrderItems
                    })
                });
                const backData = await backRes.json();

                this.shortageModal.show = false;
                if (backData.success || backRes.ok) {
                    const newCode = backData.order?.order_code || backData.order_code || '';
                    alert('✅ Đã tạo đơn giao sau mới' + (newCode ? ' (' + newCode + ')' : '') + ' cho ' + shortage + ' ' + (item.unit || '') + ' ' + (item.name || '') + '!\n\nĐơn gốc đã được cắt xuống ' + (picked > 0 ? picked : needed) + ' ' + (item.unit || '') + '.');
                    window.location.href = '/orders';
                } else {
                    alert('Lưu đơn gốc thành công, nhưng tạo đơn giao sau thất bại: ' + (backData.message || ''));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.backorderSaving = false;
            }
        },

        // ─── Tạo đơn nợ hàng TỔNG cho TẤT CẢ sản phẩm thiếu (ĐƠN GIAO SAU TỔNG) ───
        async createBackOrderAll() {
            const shortageList = this.getShortageItems();
            if (shortageList.length === 0) {
                alert('Không có sản phẩm nào đang bị thiếu!');
                return;
            }

            const confirmMsg = 'Xác nhận tạo ĐƠN GIAO SAU (nợ hàng) TỔNG cho toàn bộ ' + shortageList.length + ' sản phẩm bốc thiếu?\n\n' +
                '- Đơn hiện tại sẽ tự động cắt số lượng theo số thực bốc và lưu lại.\n' +
                '- Một đơn giao sau mới (trạng thái Giao sau) sẽ tự động tạo cho toàn bộ phần thiếu.';
            if (!confirm(confirmMsg)) return;

            this.backorderSaving = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                // 1. Chuẩn bị danh sách sản phẩm nợ hàng
                const backOrderItems = [];
                shortageList.forEach(it => {
                    const shortage = this.getShortageQty(it);
                    if (shortage > 0) {
                        const price = Number(it.unitPrice || 0);
                        const vat = Number(it.vat_percent || 10);
                        backOrderItems.push({
                            itemId: it.product_id,
                            product_id: it.product_id,
                            sku: it.sku,
                            name: it.name,
                            unit: it.unit,
                            quantity: shortage,
                            gift_quantity: 0,
                            unitPrice: price,
                            originalPrice: it.originalPrice || price,
                            costPrice: it.costPrice || 0,
                            discount_percent: 0,
                            discount_amount: 0,
                            vat_percent: vat,
                            vat_amount: Math.round((shortage * price) * (vat / 100)),
                            amount: Math.round(shortage * price * (1 + vat / 100)),
                            appliedTierPolicy: it.price_type || it.appliedTierPolicy || 'Theo phiếu',
                        });
                    }
                });

                if (backOrderItems.length === 0) {
                    alert('Không tính được số lượng thiếu hợp lệ để tạo đơn nợ hàng!');
                    return;
                }

                // 2. Cắt đơn hiện tại theo số lượng thực bốc
                this.items.forEach((it, index) => {
                    if (this.isItemShortage(it)) {
                        it.quantity = Math.max(0, Number(it.picked_qty || 0));
                        it.gift_quantity = 0;
                        it.shortage_reason = '';
                        it.item_status = it.quantity > 0 ? 'Đủ hàng' : 'Chờ bốc';
                        it.is_picked = it.quantity > 0;
                        this.updateLineTotal(index);
                    }
                });
                this.calculateTotals();

                // Payload sản phẩm còn lại cho đơn gốc (gỡ bỏ cờ thiếu hàng)
                const currentItemsPayload = this.items
                    .filter(it => (it.name || it.sku || it.product_id) && Number(it.quantity || 0) > 0)
                    .map(it => ({
                        id: it.id || undefined,
                        itemId: it.product_id,
                        product_id: it.product_id,
                        sku: it.sku,
                        name: it.name,
                        unit: it.unit,
                        quantity: it.quantity,
                        gift_quantity: Number(it.gift_quantity) || 0,
                        unitPrice: it.unitPrice,
                        originalPrice: it.originalPrice,
                        costPrice: it.costPrice,
                        discount_percent: it.discount_percent || 0,
                        discount_amount: it.discount_amount || 0,
                        vat_percent: it.vat_percent || 10,
                        vat_amount: it.vat_amount || 0,
                        amount: it.amount,
                        appliedTierPolicy: it.price_type || it.appliedTierPolicy,
                        shortage_reason: '',
                        item_status: Number(it.quantity) > 0 ? 'Đủ hàng' : 'Chờ bốc',
                        is_picked: Number(it.quantity) > 0,
                    }));

                const parentCode = this.formData.order_code || this.orderId || '';

                // 3. Lưu đơn gốc đã cắt
                const saveRes = await fetch('/api/orders/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        id: this.orderId || undefined,
                        orderCode: this.formData.order_code || undefined,
                        dealerName: this.formData.customer_name,
                        customer_name: this.formData.customer_name,
                        customerPhone: this.formData.customer_phone,
                        address: this.formData.customer_address,
                        warehouse: this.formData.warehouse,
                        paymentMethod: this.formData.payment_method,
                        payment_method: this.formData.payment_method,
                        status: this.formData.status === 'Kho báo thiếu hàng' ? 'Soạn hàng' : this.formData.status,
                        shortage_reported: false,
                        shortageReported: false,
                        notes: (this.formData.notes || '') + '\n[Đã tách ' + backOrderItems.length + ' SP thiếu sang đơn giao sau lúc ' + new Date().toLocaleTimeString('vi-VN') + ']',
                        subtotal: this.subtotal,
                        totalAmount: this.totalAmount,
                        items: currentItemsPayload
                    })
                });
                const saveData = await saveRes.json();
                if (!saveData.success && !saveRes.ok) {
                    throw new Error(saveData.message || 'Lỗi khi cập nhật đơn gốc');
                }

                // 4. Lưu đơn giao sau mới
                const backNotes = '[ĐƠN GIAO SAU TỔNG] Tách từ đơn gốc ' + parentCode + ' do kho báo thiếu hàng:\n' +
                    backOrderItems.map(b => '- ' + b.name + ': thiếu ' + b.quantity + ' ' + (b.unit || '')).join('\n');

                const backRes = await fetch('/api/orders/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        dealerName: this.formData.customer_name,
                        customer_name: this.formData.customer_name,
                        customerPhone: this.formData.customer_phone,
                        address: this.formData.customer_address,
                        warehouse: this.formData.warehouse,
                        paymentMethod: this.formData.payment_method,
                        payment_method: this.formData.payment_method,
                        orderType: 'backorder',
                        order_type: 'backorder',
                        status: 'Giao sau',
                        deliveryDate: null,
                        delivery_date: null,
                        pickingDate: null,
                        picking_date: null,
                        notes: backNotes,
                        parentOrderCode: parentCode,
                        items: backOrderItems
                    })
                });
                const backData = await backRes.json();

                if (this.shortageModal) this.shortageModal.show = false;
                if (backData.success || backRes.ok) {
                    const newCode = backData.order?.order_code || backData.order_code || '';
                    alert('🎉 ĐÃ XỬ LÝ THIẾU HÀNG TỔNG THÀNH CÔNG!\n\n1. Đơn gốc ' + parentCode + ' đã được cắt số lượng theo số thực bốc và lưu lại.\n2. Đã tự động tạo đơn giao sau mới ' + (newCode ? 'MÃ: ' + newCode : '') + ' cho toàn bộ ' + backOrderItems.length + ' sản phẩm thiếu.');
                    window.location.href = '/orders';
                } else {
                    alert('Đơn gốc đã được cắt, nhưng tạo đơn giao sau mới thất bại: ' + (backData.message || ''));
                }
            } catch (e) {
                console.error(e);
                alert('Lỗi: ' + e.message);
            } finally {
                this.backorderSaving = false;
            }
        },




        async printCurrentOrder(mode = 'standard') {
            let o = null;
            if (this.orderId) {
                try {
                    const res = await fetch('/api/orders/' + this.orderId + '/print-data');
                    if (res.ok) {
                        const json = await res.json();
                        if (json.success && json.order) {
                            o = json.order;
                        }
                    }
                } catch(e) {
                    console.error('Error fetching print data:', e);
                }
            }
            if (!o) {
                const subtotalVal = Number(this.subtotalBeforeVat || this.subtotal || 0);
                const discItems = this.items.reduce((s, it) => s + (Number(it.discount_amount) || 0), 0);
                const extraDisc = Number(this.formData.discount_amount) || 0;
                const finalVal = Number(this.finalTotal || this.totalAmount || 0);
                o = {
                    order_code: this.formData.order_code || (this.orderId ? ('DH-' + this.orderId) : 'CHƯA_LƯU'),
                    created_date: new Date().toLocaleDateString('vi-VN'),
                    customer_display: this.formData.customer_name || 'Khách lẻ',
                    customer_address: this.formData.customer_address || '',
                    customer_phone: this.formData.customer_phone || '',
                    notes: this.formData.notes || '',
                    warehouse: this.formData.warehouse || '',
                    payment_method: this.formData.payment_method || 'Tiền mặt',
                    delivery_vehicle: this.formData.delivery_vehicle || '',
                    delivery_driver: this.formData.delivery_driver || '',
                    delivery_date: this.formData.delivery_date || '',
                    subtotal: subtotalVal,
                    total_discount: discItems,
                    total_items_amount: subtotalVal,
                    extra_discount: extraDisc,
                    final_total: finalVal,
                    final_total_words: '',
                    creator_name: '{{ auth()->user()->name ?? "" }}',
                    updater_name: '{{ auth()->user()->name ?? "" }}',
                    delivery_status: this.formData.status || 'Báo giá',
                    items: this.items.filter(it => it.product_id).map((it, idx) => ({
                        tt: idx + 1,
                        name: it.name,
                        sku: it.sku,
                        tax_name: it.tax_name || it.name,
                        unit: it.unit,
                        unit_price: Number(it.unitPrice) || 0,
                        quantity: Number(it.quantity) || 0,
                        amount: (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0),
                        discount: Number(it.discount_amount) || 0,
                        total: Number(it.amount) || ((Number(it.quantity) || 0) * (Number(it.unitPrice) || 0)),
                        price_type: it.price_type || this.formData.dealer_tier
                    }))
                };
            }
            this.executePrintOrder(o, mode);
        },

        getDiscountDisplay(it) {
            if (!it) return '-';
            if (it.discount_percent !== undefined && it.discount_percent !== null && Number(it.discount_percent) > 0) {
                return Number(it.discount_percent) + '%';
            }
            const origSubtotal = Number(it.amount || 0) || (Number(it.unit_price || 0) * Number(it.quantity || 0));
            const disc = Number(it.discount || 0);
            if (disc > 0 && origSubtotal > 0) {
                return Math.round((disc / origSubtotal) * 100) + '%';
            }
            return '-';
        },

        executePrintOrder(o, mode = 'standard') {
            const isNoPrice = (mode === 'no_price' || mode === 'no_price_with_header');
            const isTaxName = (mode === 'tax_name');
            const hasCompanyHeader = (mode !== 'no_price');

            let title = 'PHIẾU BÁN HÀNG KIÊM XUẤT KHO';
            let subTitle = 'HÓA ĐƠN BÁN LẺ & GIAO HÀNG';
            if (mode === 'no_price') {
                title = 'PHIẾU XUẤT KHO - SOẠN HÀNG';
                subTitle = 'DÀNH CHO BỘ PHẬN KHO & BỐC XẾP (KHÔNG TIÊU ĐỀ)';
            } else if (mode === 'no_price_with_header') {
                title = 'PHIẾU GIAO NHẬN HÀNG HÓA';
                subTitle = 'BIÊN BẢN BÀN GIAO & ĐỐI CHIẾU SỐ LƯỢNG HÀNG HÓA';
            } else if (mode === 'tax_name') {
                title = 'BẢNG KÊ BÁN HÀNG THEO HÓA ĐƠN';
                subTitle = 'BẢNG KÊ CHI TIẾT HÀNG HÓA THEO TÊN HÓA ĐƠN GTGT';
            }

            let tableRows = '';
            (o.items || []).forEach((it, idx) => {
                const prodName = isTaxName ? (it.tax_name || it.name) : it.name;
                const subName = (!isTaxName && it.tax_name && it.tax_name !== it.name) ? `<div style="font-size:10.5px;color:#555;font-style:italic;">${it.tax_name}</div>` : '';
                const discText = this.getDiscountDisplay(it);
                
                if (isNoPrice) {
                    tableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:left;font-family:monospace;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:13px;">${this.formatNumber(it.quantity)}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:center;font-weight:bold;font-size:12px;">${this.getPriceTierCode(it.price_tier_code || it.price_type, o.dealer_tier)}</td>
                        </tr>
                    `;
                } else {
                    tableRows += `
                        <tr>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${idx + 1}</td>
                            <td style="padding:5px 6px;border:1px solid #111;text-align:left;font-family:monospace;">${it.sku || ''}</td>
                            <td style="padding:5px 6px;border:1px solid #111;font-weight:600;text-align:left;">${prodName}${subName}</td>
                            <td style="text-align:center;padding:5px 6px;border:1px solid #111;">${it.unit || ''}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;font-size:12.5px;">${this.formatNumber(it.quantity)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${this.formatNumber(it.unit_price)}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;">${discText}</td>
                            <td style="text-align:right;padding:5px 6px;border:1px solid #111;font-weight:bold;">${this.formatNumber(it.total || it.amount)}</td>
                        </tr>
                    `;
                }
            });

            let summaryHtml = '';
            if (!isNoPrice) {
                summaryHtml = `
                    <tr>
                        <td colspan="5" style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">Tổng</td>
                        <td style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;">${this.formatNumber(o.final_total || o.total_items_amount)}</td>
                    </tr>
                `;
            } else {
                summaryHtml = `
                    <tr>
                        <td colspan="4" style="border:1px solid #111;padding:5px 6px;"></td>
                        <td style="text-align:right;font-weight:bold;padding:5px 6px;border:1px solid #111;">Tổng SL:</td>
                        <td style="text-align:center;font-weight:bold;padding:5px 6px;border:1px solid #111;font-size:13px;">${this.formatNumber((o.items || []).reduce((s, it) => s + Number(it.quantity || 0), 0))}</td>
                    </tr>
                `;
            }

            const printHtml = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>${title} - ${o.order_code}</title>
                    <style>
                        @page { 
                            size: A4 portrait; 
                            margin: 10mm 12mm 10mm 12mm; 
                        }
                        * {
                            box-sizing: border-box;
                        }
                        body { 
                            font-family: 'Times New Roman', Times, serif; 
                            font-size: 12.5px; 
                            color: #000; 
                            line-height: 1.35; 
                            margin: 0; 
                            padding: 0; 
                            background: #fff;
                        }
                        table { 
                            width: 100%; 
                            border-collapse: collapse; 
                        }
                        .product-table th { 
                            background-color: #f1f5f9; 
                            border: 1px solid #111; 
                            padding: 6px 4px; 
                            font-weight: bold; 
                            font-size: 11.5px; 
                            text-transform: uppercase;
                            text-align: center;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        .product-table td {
                            border: 1px solid #111;
                            padding: 5px 6px;
                            font-size: 12px;
                        }
                        @media print {
                            body { padding: 0; }
                            .no-print { display: none !important; }
                        }
                    </style>
                </head>
                <body>
                    <!-- Header Doanh Nghiệp (Ẩn khi in không TĐ - no_price) -->
                    ${hasCompanyHeader ? `
                    <table style="width: 100%; border: none; border-bottom: 2px solid #1b4a8c; padding-bottom: 6px; margin-bottom: 12px; border-collapse: collapse;">
                        <tr>
                            <td style="width: 65%; vertical-align: top; border: none; padding: 0; text-align: left;">
                                <div style="font-size: 14.5px; font-weight: bold; color: #1b4a8c; text-transform: uppercase; letter-spacing: 0.5px;">NHÀ PHÂN PHỐI MÁY LỌC NƯỚC ĐẠI SƠN</div>
                                <div style="font-size: 11px; color: #444; font-weight: bold; text-transform: uppercase; margin-top: 1px;">CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN</div>
                                <div style="font-size: 11.5px; color: #333; margin-top: 3px; line-height: 1.4;">
                                    <div><strong>Địa chỉ:</strong> Cụm Công Nghiệp Quất Động, Huyện Thường Tín, TP. Hà Nội</div>
                                    <div><strong>Hotline / Zalo:</strong> 0912.345.678 - 0987.654.321 &nbsp;|&nbsp; <strong>Email:</strong> kd@daison.vn</div>
                                </div>
                            </td>
                            <td style="width: 35%; vertical-align: top; text-align: right; border: none; padding: 0;">
                                <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #333;">Mẫu số: 02 - VT</div>
                                <div style="font-size: 10.5px; font-style: italic; color: #555; margin-bottom: 4px;">(Theo TT 200/2014/TT-BTC)</div>
                                <div style="display: inline-block; border: 1px solid #1b4a8c; background-color: #f0f7ff; padding: 3px 8px; border-radius: 4px; font-size: 11.5px; font-weight: bold; color: #1b4a8c; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                    Số: ${o.order_code}
                                </div>
                                <div style="font-size: 11px; color: #555; margin-top: 3px;">Ngày in: ${new Date().toLocaleDateString('vi-VN')} ${new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}</div>
                            </td>
                        </tr>
                    </table>
                    ` : `
                    <!-- In Không TĐ: Tuyệt đối không chứa thông tin công ty, chỉ hiển thị số phiếu và ngày in ở góc phải -->
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                        <div style="text-align: right; font-size: 11px;">
                            <div style="display: inline-block; border: 1px solid #333; background-color: #f8fafc; padding: 2px 8px; border-radius: 4px; font-size: 11.5px; font-weight: bold; color: #111;">
                                Số: ${o.order_code}
                            </div>
                            <div style="font-size: 10.5px; color: #555; margin-top: 2px;">Ngày in: ${new Date().toLocaleDateString('vi-VN')}</div>
                        </div>
                    </div>
                    `}

                    <!-- Tiêu Đề Phiếu -->
                    <div style="text-align: center; margin-bottom: 14px;">
                        <h2 style="margin: 0 0 3px 0; font-size: 19px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: #111;">${title}</h2>
                        <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #555; letter-spacing: 0.5px; margin-bottom: 3px;">${subTitle}</div>
                        <div style="font-size: 12px; font-style: italic; color: #333;">
                            Ngày lập: <strong>${o.created_date || '---'}</strong>
                        </div>
                    </div>

                    <!-- Thông Tin Khách Hàng & Giao Vận (2 Cột Rõ Ràng) -->
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px; font-size: 12.5px; line-height: 1.5;">
                        <tr>
                            <td style="width: 60%; vertical-align: top; border: none; padding: 0 12px 0 0;">
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 120px; border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Khách hàng / Đại lý:</td>
                                        <td style="border: none; padding: 2px 0; font-size: 13px;"><strong style="text-transform: uppercase;">${(o.customer_name || o.customer_display || '---').replace(/^[A-Za-z0-9_-]+\s*-\s*/, '')}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Địa chỉ giao hàng:</td>
                                        <td style="border: none; padding: 2px 0;">${o.customer_address || '---'}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Điện thoại liên hệ:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.customer_phone || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Diễn giải / Ghi chú:</td>
                                        <td style="border: none; padding: 2px 0; font-style: italic;">${o.notes || '---'}</td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width: 40%; vertical-align: top; border: none; padding: 0 0 0 12px; border-left: 1px dashed #bbb;">
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 105px; border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Kho xuất hàng:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.warehouse || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Hình thức TT:</td>
                                        <td style="border: none; padding: 2px 0;"><strong>${o.payment_method || '---'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Xe / Người giao:</td>
                                        <td style="border: none; padding: 2px 0;">${o.delivery_vehicle || o.delivery_driver || '---'}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2px 0; font-weight: bold; color: #333; white-space: nowrap;">Ngày giao dự kiến:</td>
                                        <td style="border: none; padding: 2px 0;">${o.delivery_date || '---'}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!-- Bảng Danh Sách Hàng Hóa -->
                    <table class="product-table">
                        <thead>
                            ${isNoPrice ? `
                                <tr>
                                    <th style="width: 35px;">TT</th>
                                    <th style="width: 95px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 55px;">ĐVT</th>
                                    <th style="width: 65px;">SL</th>
                                    <th style="width: 75px;">Loại Giá</th>
                                </tr>
                            ` : `
                                <tr>
                                    <th style="width: 35px;">TT</th>
                                    <th style="width: 95px; text-align: left; padding-left: 6px;">Mã SP</th>
                                    <th style="text-align: left; padding-left: 6px;">Tên sản phẩm</th>
                                    <th style="width: 55px;">ĐVT</th>
                                    <th style="width: 50px;">SL</th>
                                    <th style="width: 90px;">Đơn giá</th>
                                    <th style="width: 70px;">C.khấu</th>
                                    <th style="width: 100px;">Thành tiền</th>
                                </tr>
                            `}
                        </thead>
                        <tbody>
                            ${tableRows}
                            ${summaryHtml}
                        </tbody>
                    </table>

                    <!-- Bằng Chữ & Trạng Thái -->
                    ${!isNoPrice ? `
                        <div style="margin-top: 8px; font-size: 12.5px; line-height: 1.4;">
                            <strong>Số tiền viết bằng chữ:</strong> 
                            <em style="text-transform: capitalize; font-weight: bold;">${o.final_total_words || ''}</em>
                        </div>
                    ` : ''}

                    <!-- Dòng Ngày Tháng Phía Trên Chữ Ký -->
                    <div style="margin-top: 12px; text-align: right; font-size: 12px; font-style: italic;">
                        Ngày ..... tháng ..... năm 202...
                    </div>

                    <!-- BẢNG CHỮ KÝ 4 BÊN (Dùng thẻ table chuẩn 4 cột 25% - Tuyệt đối không gãy dòng) -->
                    <table style="width: 100%; border: none; margin-top: 8px; border-collapse: collapse; page-break-inside: avoid;">
                        <tr>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người lập phiếu</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">${o.creator_name || ''}</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Thủ kho xuất</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">&nbsp;</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người giao hàng</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">${o.delivery_driver || ''}</div>
                            </td>
                            <td style="width: 25%; text-align: center; vertical-align: top; border: none; padding: 0 4px;">
                                <div style="font-weight: bold; font-size: 12.5px; text-transform: uppercase;">Người nhận hàng</div>
                                <div style="font-style: italic; font-size: 11px; color: #555; margin-top: 2px;">(Ký, ghi rõ họ tên)</div>
                                <div style="height: 65px;"></div>
                                <div style="font-weight: bold; font-size: 12.5px; color: #000;">&nbsp;</div>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 15px; text-align: center; font-size: 11px; font-style: italic; color: #666;">
                        *(Quý khách vui lòng kiểm tra kỹ số lượng, quy cách và chất lượng hàng hóa trước khi ký nhận)*
                    </div>
                </body>
                </html>
            `;

            const printWin = window.open('', '_blank');
            if (!printWin) {
                alert('Trình duyệt đang chặn cửa sổ bật lên. Vui lòng cho phép pop-up để in.');
                return;
            }
            printWin.document.open();
            printWin.document.write(printHtml);
            printWin.document.close();
            printWin.focus();
            setTimeout(() => {
                printWin.print();
            }, 400);
        },

        getPriceTierCode(val, defaultTier = '') {
            if (!val) val = defaultTier || '';
            let str = String(val).trim();
            const matchParen = str.match(/\(([^)]+)\)/);
            if (matchParen) {
                str = matchParen[1].trim();
            } else if (str === 'Theo phiếu' && defaultTier) {
                str = defaultTier.trim();
            }

            const s = str.toLowerCase();
            if (s.includes('cấp 1') || s.includes('cap 1') || s === 'bc1') return 'BC1';
            if (s.includes('cấp 2') || s.includes('cap 2') || s === 'bc2') return 'BC2';
            if (s.includes('cấp 3') || s.includes('cap 3') || s === 'bc3') return 'BC3';
            if (s.includes('bán lẻ') || s.includes('ban le') || s === 'bl') return 'BL';
            if (s.includes('nguyên giá') || s.includes('nguyen gia') || s === 'ng') return 'NG';
            if (s.includes('khuyến mại') || s.includes('tặng') || s === 'km') return 'KM';

            if (this.priceTiersCatalog && Array.isArray(this.priceTiersCatalog)) {
                const found = this.priceTiersCatalog.find(pt => pt.name && pt.name.toLowerCase() === s);
                if (found && found.code) return found.code;
            }

            return str.toUpperCase();
        }
    };
}
</script>
@endsection
