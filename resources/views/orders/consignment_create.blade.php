@extends('layouts.app')

@section('title', 'Lập Đơn Hàng Ôm Mới | ERP Đại Sơn')

@section('content')
<div x-data="consignmentFormApp()" x-init="init()" @keydown.window="handleGlobalKeydown($event)" class="space-y-4 pb-24">

    <!-- 1. Top Bar / Title & Breadcrumbs -->
    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-100/80 -mx-4 sm:-mx-6 -mt-4 px-4 sm:px-6 py-2 text-xs overflow-x-auto">
        <div class="flex items-center gap-2">
            <a href="{{ route('orders.index', ['tab' => 'consignments']) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 hover:text-sky-700 font-bold transition-all shadow-2xs cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                <span>Quay lại danh sách đơn ôm</span>
            </a>
            <span class="text-slate-300">|</span>
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-sky-700 font-bold border border-sky-400 rounded-lg shadow-xs ring-1 ring-sky-300">
                <i class="fa-solid fa-boxes-stacked text-[11px] text-sky-600"></i>
                <span>Lập đơn hàng ôm mới</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-xs text-slate-500 hidden sm:flex items-center gap-1.5">
                <span>Phím tắt:</span>
                <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F8</kbd>
                <span>Thêm dòng</span>
                <span class="text-slate-300 mx-1">•</span>
                <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F9</kbd>
                <span>Lưu</span>
                <span class="text-slate-300 mx-1">•</span>
                <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">Esc</kbd>
                <span>Đóng</span>
            </div>
            <button type="button" @click="confirmClose()" class="text-rose-600 hover:text-rose-700 font-bold flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
                <span>Đóng (Esc)</span>
            </button>
        </div>
    </div>

    <!-- Alert / Toast -->
    <div x-show="errorMessage" x-cloak class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-bold text-rose-700 flex items-center justify-between shadow-xs">
        <span x-text="errorMessage"></span>
        <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <!-- Main Container Card (Matching orders/form.blade.php) -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">

        <!-- Title Block -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Đơn Hàng Ôm & Ký Gửi</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                        ERP Form Standard
                    </span>
                    @if($isAccountant)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-check"></i>
                            <span>Kế toán lập &bull; Tự động duyệt</span>
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-clock"></i>
                            <span>Nhân sự tự đặt &bull; Chờ kế toán duyệt</span>
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Nhân viên nhận hàng ôm từ kho công ty, cộng vào công nợ cá nhân và phân phối lẻ cho các khách hàng
                </p>
            </div>

            <!-- Workflow Summary Tag -->
            <div class="text-xs text-slate-600 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200 flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-circle-nodes text-sky-600"></i>
                <span>Quy trình: <strong>Lập đơn</strong> &rarr; <strong>Duyệt</strong> &rarr; <strong>Kho bốc</strong> &rarr; <strong>Vào kho ôm NV</strong></span>
            </div>
        </div>

        <!-- 3. Form Information: Row 1 & Row 2 (Grid matching orders/form.blade.php) -->
        <div class="space-y-4">
            
            <!-- Row 1: 6 columns (lg:grid-cols-12) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-12 gap-4">
                
                <!-- Mã chứng từ -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">MÃ CHỨNG TỪ</label>
                    <input type="text" readonly value="TỰ SINH KHI LƯU" class="w-full px-3 py-2 bg-slate-100/80 border border-slate-200 rounded-xl text-xs font-bold text-slate-500 cursor-not-allowed select-none">
                </div>

                <!-- Ngày chứng từ -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">NGÀY CHỨNG TỪ</label>
                    <input type="date" x-model="formData.order_date" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs">
                </div>

                <!-- Ngày soạn / bốc hàng -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">NGÀY SOẠN / BỐC</label>
                    <input type="date" x-model="formData.picking_date" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs">
                </div>

                <!-- Loại đơn hàng -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">LOẠI ĐƠN HÀNG</label>
                    <div class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 flex items-center gap-1.5 select-none">
                        <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
                        <span>Đơn hàng ôm</span>
                    </div>
                </div>

                <!-- Tình trạng duyệt -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">TÌNH TRẠNG DUYỆT</label>
                    @if($isAccountant)
                        <div class="w-full py-2 px-3 bg-emerald-50 border border-emerald-300 font-bold text-emerald-800 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-2xs">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>Duyệt ngay (Chờ bốc)</span>
                        </div>
                    @else
                        <div class="w-full py-2 px-3 bg-amber-50 border border-amber-300 font-bold text-amber-800 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-2xs">
                            <i class="fa-solid fa-clock text-amber-600"></i>
                            <span>Chờ kế toán duyệt</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Row 2: 3 columns (Nhân viên, Kho xuất, Ghi chú) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                
                <!-- Nhân viên nhận hàng ôm (md:col-span-5) -->
                <div class="md:col-span-5">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-500 tracking-wide uppercase">
                            NHÂN VIÊN NHẬN HÀNG ÔM <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] font-semibold text-slate-500">
                            Nợ hiện tại: <span class="font-bold text-rose-600" x-text="formatMoney(selectedStaff?.current_debt || 0)"></span>
                        </span>
                    </div>

                    @if($isAccountant)
                        <div class="relative" @click.outside="closeStaffDropdown()">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                <input type="text"
                                       autocomplete="off"
                                       autocorrect="off"
                                       spellcheck="false"
                                       x-model="staffSearch"
                                       @focus="openStaffDropdown = true; highlightedStaffIndex = 0"
                                       @input="openStaffDropdown = true; highlightedStaffIndex = 0; onStaffInput()"
                                       @keydown.down.prevent="
                                           openStaffDropdown = true;
                                           const list = getFilteredStaff(staffSearch);
                                           highlightedStaffIndex = Math.min(highlightedStaffIndex + 1, list.length - 1);
                                           scrollStaffIntoView(highlightedStaffIndex);
                                       "
                                       @keydown.up.prevent="
                                           highlightedStaffIndex = Math.max(highlightedStaffIndex - 1, 0);
                                           scrollStaffIntoView(highlightedStaffIndex);
                                       "
                                       @keydown.enter.prevent="
                                           if (openStaffDropdown) {
                                               const list = getFilteredStaff(staffSearch);
                                               if (list.length > 0 && list[highlightedStaffIndex]) {
                                                   selectStaff(list[highlightedStaffIndex]);
                                               }
                                           }
                                       "
                                       @keydown.escape.prevent="closeStaffDropdown()"
                                       placeholder="🔍 Gõ tên, mã NV hoặc SĐT để tìm (phím ↑ ↓ Enter)..."
                                       class="w-full pl-9 pr-14 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">

                                <!-- Nút xóa & mũi tên dropdown -->
                                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                                    <button type="button" x-show="formData.staff_id" @click.stop="clearStaff()" class="text-slate-400 hover:text-rose-500 p-0.5 cursor-pointer" title="Hủy chọn">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                    <button type="button" @click.stop="openStaffDropdown = !openStaffDropdown; if(openStaffDropdown) highlightedStaffIndex = 0" class="text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer">
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="openStaffDropdown ? 'rotate-180 text-sky-600' : ''"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Menu Dropdown hiển thị danh sách nhân viên -->
                            <div x-show="openStaffDropdown" 
                                 x-cloak 
                                 class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-64 overflow-y-auto py-1 text-xs divide-y divide-slate-100">
                                <div class="px-3 py-1.5 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between sticky top-0 z-10">
                                    <span>Danh sách nhân viên (<span x-text="getFilteredStaff(staffSearch).length"></span>)</span>
                                    <span class="text-sky-600 font-normal lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                </div>
                                <template x-for="(s, sIdx) in getFilteredStaff(staffSearch)" :key="s.id">
                                    <div :id="'staff-opt-' + sIdx"
                                         @mousedown.prevent="selectStaff(s)"
                                         @mouseenter="highlightedStaffIndex = sIdx"
                                         class="px-3 py-2 cursor-pointer flex items-center justify-between transition-colors"
                                         :class="highlightedStaffIndex === sIdx ? 'bg-sky-100 text-sky-950 font-bold ring-1 ring-sky-300' : (formData.staff_id == s.id ? 'bg-sky-50 font-bold text-sky-700' : 'hover:bg-slate-50')">
                                        <div class="min-w-0 pr-3">
                                            <div class="font-bold text-slate-900 truncate flex items-center gap-1.5">
                                                <i class="fa-solid fa-user text-[11px] text-slate-400"></i>
                                                <span x-text="s.name"></span>
                                                <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-mono text-[10px]" x-show="s.code" x-text="'[' + s.code + ']'"></span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                <span x-show="s.phone" x-text="'SĐT: ' + s.phone"></span>
                                                <span x-show="s.phone && (s.department || s.branch)">&bull;</span>
                                                <span x-show="s.department || s.branch" x-text="s.department || s.branch"></span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <div class="text-[10px] text-slate-400 font-medium">Nợ hiện tại:</div>
                                            <div class="font-bold text-xs" :class="Number(s.current_debt || 0) > 0 ? 'text-rose-600' : 'text-slate-600'" x-text="formatMoney(s.current_debt || 0)"></div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="getFilteredStaff(staffSearch).length === 0" class="px-4 py-6 text-center text-slate-400">
                                    <i class="fa-solid fa-user-slash text-xl text-slate-300 mb-1.5"></i>
                                    <div class="font-bold text-slate-600 text-xs">Không tìm thấy nhân viên nào</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Thử tìm theo tên, mã NV hoặc số điện thoại</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                                <span class="font-bold text-slate-900 truncate">{{ $currentUser?->name }}</span>
                                <span class="text-slate-400 font-medium">({{ $currentUser?->code ?? 'NV' }})</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold border border-blue-200 shrink-0">
                                Tài khoản của bạn
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Kho xuất hàng công ty (md:col-span-4) -->
                <div class="md:col-span-4">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-500 tracking-wide uppercase">
                            KHO XUẤT HÀNG CÔNG TY <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] font-medium text-slate-400">Kho bảo lưu / Kho tổng</span>
                    </div>
                    <select x-model="formData.warehouse_id" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">
                        <option value="">-- Chọn kho xuất hàng --</option>
                        <template x-for="wh in warehousesList" :key="wh.id">
                            <option :value="wh.id" x-text="wh.name + (wh.address ? ' (' + wh.address + ')' : '')"></option>
                        </template>
                    </select>
                </div>

                <!-- Ghi chú nhanh (md:col-span-3) -->
                <div class="md:col-span-3">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-bold text-slate-500 tracking-wide uppercase">GHI CHÚ / MỤC ĐÍCH</label>
                    </div>
                    <input type="text" 
                           x-model="formData.notes" 
                           placeholder="VD: Ôm hàng bán sự kiện tuần này..." 
                           class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">
                </div>
            </div>
        </div>

        <!-- 4. Product Items Table (Exact Standard Design from orders/form.blade.php) -->
        <div class="space-y-2">
            <div class="flex items-center justify-between pb-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black text-slate-800 uppercase tracking-wide">DANH SÁCH MẶT HÀNG ÔM</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200" x-text="items.length + ' mặt hàng'"></span>
                </div>
                <div class="text-[11px] text-slate-400">
                    Gõ mã hoặc tên SP &bull; Dùng phím &uarr; &darr; &bull; Enter để chọn nhanh
                </div>
            </div>

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
                            <th class="py-2.5 px-3 font-bold min-w-[280px]">TÊN / MÃ SẢN PHẨM</th>
                            <th class="py-2.5 px-2 text-center w-20">ĐVT</th>
                            <th class="py-2.5 px-2 text-center w-24">TỒN KHO</th>
                            <th class="py-2.5 px-2 text-right w-32">GIÁ BÁN</th>
                            <th class="py-2.5 px-2 text-center w-24">SL ÔM</th>
                            <th class="py-2.5 px-3 text-right w-36">THÀNH TIỀN</th>
                            <th class="py-2.5 px-2 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                
                                <!-- STT + Checkbox -->
                                <td class="py-2 px-3 text-center align-middle">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <input type="checkbox" x-model="item.checked" @change="calculateTotals()" class="rounded text-sky-600 focus:ring-0 cursor-pointer">
                                        <span class="font-bold text-slate-700" x-text="index + 1"></span>
                                    </div>
                                </td>

                                <!-- Tên / Mã SP Input với Autocomplete Dropdown điều khiển bằng phím mũi tên Lên/Xuống + Enter -->
                                <td class="py-2 px-3 relative align-middle">
                                    <div class="relative" @click.outside="if (activeProdDropdownIndex === index) activeProdDropdownIndex = null">
                                        <div class="relative flex items-center">
                                            <input type="text"
                                                   autocomplete="off"
                                                   autocorrect="off"
                                                   spellcheck="false"
                                                   :id="'row-product-input-' + index"
                                                   x-model="item.searchQuery" 
                                                   @focus="activeProdDropdownIndex = index; prodHighlightIndex = 0; $event.target.select()"
                                                   @input="activeProdDropdownIndex = index; prodHighlightIndex = 0"
                                                   @keydown.down.prevent="
                                                       activeProdDropdownIndex = index;
                                                       const prods = getFilteredProducts(item.searchQuery);
                                                       prodHighlightIndex = Math.min(prodHighlightIndex + 1, prods.length - 1);
                                                       scrollProdIntoView(index, prodHighlightIndex);
                                                   "
                                                   @keydown.up.prevent="
                                                       prodHighlightIndex = Math.max(prodHighlightIndex - 1, 0);
                                                       scrollProdIntoView(index, prodHighlightIndex);
                                                   "
                                                   @keydown.enter.prevent="
                                                       if (activeProdDropdownIndex === index) {
                                                           const prods = getFilteredProducts(item.searchQuery);
                                                           if (prods.length > 0 && prods[prodHighlightIndex]) {
                                                               selectProductForRow(index, prods[prodHighlightIndex]);
                                                               focusQty(index);
                                                           }
                                                       }
                                                   "
                                                   @keydown.escape.prevent="activeProdDropdownIndex = null"
                                                   placeholder="Tìm mã hoặc tên sản phẩm (phím ↑ ↓ Enter)..." 
                                                   class="w-full pl-3 pr-8 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:border-sky-500 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">

                                            <!-- Nút xóa nhanh để tìm lại -->
                                            <button type="button" 
                                                    x-show="item.searchQuery || item.product_id" 
                                                    @click.stop="clearProductRow(index)" 
                                                    class="absolute right-2 text-slate-400 hover:text-rose-500 p-0.5 rounded cursor-pointer"
                                                    title="Xóa chọn lại">
                                                <i class="fa-solid fa-xmark text-xs"></i>
                                            </button>
                                        </div>

                                        <!-- Menu Dropdown gợi ý sản phẩm -->
                                        <div x-show="activeProdDropdownIndex === index" 
                                             x-cloak 
                                             class="absolute left-0 top-full mt-1 w-[480px] max-w-[90vw] z-50 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-60 overflow-y-auto py-1 text-xs divide-y divide-slate-100">
                                            <div class="px-3 py-1 bg-slate-50 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between sticky top-0 z-10">
                                                <span>Gợi ý sản phẩm (<span x-text="getFilteredProducts(item.searchQuery).length"></span>)</span>
                                                <span class="text-sky-600 font-normal lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                            </div>
                                            <template x-for="(p, pIdx) in getFilteredProducts(item.searchQuery)" :key="p.id">
                                                <div :id="'prod-opt-' + index + '-' + pIdx"
                                                     @mousedown.prevent="selectProductForRow(index, p); focusQty(index)"
                                                     @click="selectProductForRow(index, p); focusQty(index)"
                                                     @mouseenter="prodHighlightIndex = pIdx"
                                                     class="px-3 py-2 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                                     :class="prodHighlightIndex === pIdx ? 'bg-sky-100 text-sky-950 font-bold ring-1 ring-sky-300' : 'hover:bg-slate-50'">
                                                    <div class="min-w-0 pr-3">
                                                        <div class="font-bold text-slate-900 truncate">
                                                            <span class="text-sky-600 font-mono mr-1" x-text="'[' + p.sku + ']'"></span>
                                                            <span x-text="p.name"></span>
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                            <span class="font-semibold text-slate-700" x-text="'ĐVT: ' + (p.unit || 'cái')"></span>
                                                            <span>&bull;</span>
                                                            <span :class="p.stock_quantity > 0 ? 'text-emerald-600 font-semibold' : 'text-rose-500'" 
                                                                  x-text="'Tồn kho: ' + formatNumber(p.stock_quantity) + ' ' + (p.unit || '')"></span>
                                                        </div>
                                                    </div>
                                                    <div class="shrink-0 text-right">
                                                        <div class="font-bold text-sky-600 text-xs" x-text="'Giá: ' + formatMoney(p.price || p.sell_price || 0)"></div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Khi không tìm thấy sản phẩm nào -->
                                            <div x-show="getFilteredProducts(item.searchQuery).length === 0" class="px-4 py-6 text-center text-slate-400">
                                                <i class="fa-solid fa-box-open text-xl text-slate-300 mb-1.5"></i>
                                                <div class="font-bold text-slate-600 text-xs">Không tìm thấy sản phẩm nào</div>
                                                <div class="text-[11px] text-slate-400 mt-0.5">Thử tìm theo mã SKU hoặc tên sản phẩm khác</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hiển thị SKU & tồn kho dưới ô tìm kiếm -->
                                    <div x-show="item.product_id" x-cloak class="mt-1 flex items-center gap-1.5 text-[10px] text-slate-400">
                                        <span class="font-mono text-sky-600 font-bold" x-text="'[' + item.sku + ']'"></span>
                                        <span>&bull;</span>
                                        <span>Tồn kho:</span>
                                        <span class="font-bold" :class="item.stock_quantity > 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="formatNumber(item.stock_quantity) + ' ' + (item.unit || '')"></span>
                                    </div>
                                </td>

                                <!-- ĐVT -->
                                <td class="py-2 px-2 text-center align-middle">
                                    <span class="font-semibold text-slate-600 text-xs" x-text="item.unit || '---'"></span>
                                </td>

                                <!-- Tồn kho -->
                                <td class="py-2 px-2 text-center align-middle">
                                    <span class="font-bold text-xs" :class="item.stock_quantity > 0 ? 'text-emerald-600' : 'text-slate-400'" x-text="formatNumber(item.stock_quantity || 0)"></span>
                                </td>

                                <!-- Giá bán -->
                                <td class="py-2 px-2 text-right align-middle">
                                    <input type="number" 
                                           x-model.number="item.unit_price" 
                                           @input="calculateTotals()" 
                                           step="any"
                                           placeholder="0"
                                           class="w-28 px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-right text-xs font-bold focus:border-sky-500 focus:outline-none shadow-2xs">
                                </td>

                                <!-- SL Ôm -->
                                <td class="py-2 px-2 text-center align-middle">
                                    <input type="number" 
                                           :id="'row-qty-input-' + index"
                                           x-model.number="item.quantity" 
                                           @input="calculateTotals()" 
                                           min="0.1" 
                                           step="any"
                                           class="w-16 px-1.5 py-1.5 bg-white border border-slate-200 rounded-lg text-center text-xs font-black focus:border-sky-500 focus:outline-none shadow-2xs">
                                </td>

                                <!-- Thành tiền -->
                                <td class="py-2 px-3 text-right align-middle font-bold text-xs text-amber-700">
                                    <span x-text="formatMoney((Number(item.quantity) || 0) * (Number(item.unit_price) || 0))"></span>
                                </td>

                                <!-- Nút Xóa -->
                                <td class="py-2 px-2 text-center align-middle">
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
                    <span>Thêm dòng (F8)</span>
                </button>
                <div class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                    <span class="text-slate-400">Mẹo:</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F8</kbd> thêm dòng
                    <span class="text-slate-300">&bull;</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F9</kbd> lưu đơn
                    <span class="text-slate-300">&bull;</span>
                    <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">Esc</kbd> đóng
                </div>
            </div>
        </div>

        <!-- 5. Footer / Summary Area (Grid matching orders/form.blade.php) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pt-2">
            
            <!-- Left Column: Ghi chú chi tiết & Quy trình -->
            <div class="lg:col-span-7 space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 tracking-wide uppercase mb-1">GHI CHÚ ĐƠN HÀNG ÔM</label>
                    <textarea x-model="formData.notes" rows="3" placeholder="Nhập ghi chú đơn ôm, tuyến khách hàng dự kiến phân phối, sự kiện bán lẻ..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:border-sky-500 focus:outline-none"></textarea>
                </div>

                <!-- Card thông tin quy trình duyệt & bốc hàng -->
                @if($isAccountant)
                    <div class="p-3.5 bg-emerald-50/80 border border-emerald-200 rounded-xl text-xs text-emerald-900 space-y-1 shadow-2xs">
                        <div class="font-bold flex items-center gap-1.5 text-emerald-950">
                            <i class="fa-solid fa-shield-check text-emerald-600"></i>
                            <span>Quyền Kế toán / Quản trị viên:</span>
                        </div>
                        <p class="text-[11px] leading-relaxed text-emerald-800">
                            Đơn hàng sẽ được <strong>tự động duyệt ngay</strong> khi tạo và hiển thị trực tiếp trên màn hình <strong>Bốc hàng kho</strong>. Khi kho hoàn tất bốc và kiểm tra, hàng sẽ chuyển vào <strong>Kho Hàng Ôm</strong> của nhân viên.
                        </p>
                    </div>
                @else
                    <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1 shadow-2xs">
                        <div class="font-bold flex items-center gap-1.5 text-amber-950">
                            <i class="fa-solid fa-clock text-amber-600"></i>
                            <span>Quyền Nhân sự kinh doanh:</span>
                        </div>
                        <p class="text-[11px] leading-relaxed text-amber-800">
                            Đơn hàng sau khi gửi sẽ ở trạng thái <strong>Chờ kế toán duyệt</strong>. Sau khi Kế toán duyệt chứng từ, đơn mới xuất hiện bên bộ phận Bốc hàng để kho chuẩn bị hàng xuất.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Right Column: Financial Calculations Summary (Matching orders/form.blade.php) -->
            <div class="lg:col-span-5 bg-slate-50/80 p-4 rounded-xl border border-slate-200/70 space-y-2.5 text-xs font-semibold">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tổng số lượng ôm:</span>
                        <span class="font-bold text-slate-800" x-text="formatNumber(totalQuantity) + ' sản phẩm'"></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600 pt-1 border-t border-slate-200/60">
                        <span>Công nợ hiện tại của NV:</span>
                        <span class="font-bold text-slate-700" x-text="formatMoney(selectedStaff?.current_debt || 0)"></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Công nợ dự kiến sau đơn ôm:</span>
                        <span class="font-mono font-bold text-rose-600" x-text="formatMoney(Number(selectedStaff?.current_debt || 0) + totalConsignmentAmount)"></span>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-sm font-black text-slate-900">
                        <span class="text-base text-slate-900">Tổng tiền đơn ôm:</span>
                        <span class="text-xl text-amber-600 font-black" x-text="formatMoney(totalConsignmentAmount)"></span>
                    </div>
                    <div class="text-[10px] text-slate-400 text-right -mt-1 font-medium">
                        (Số tiền sẽ được ghi nhận vào công nợ cá nhân của nhân viên)
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 6. Floating / Sticky Action Bar (Matching orders/form.blade.php lines 1270-1320) -->
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 sm:px-8 py-3 shadow-lg flex items-center justify-between gap-3">
        
        <!-- Left: Items count & Total -->
        <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
            <span class="uppercase tracking-wider" x-text="items.length + ' MẶT HÀNG'"></span>
            <span class="text-slate-300">|</span>
            <div class="flex items-center gap-3">
                <span class="text-sm font-black text-amber-600" x-text="formatMoney(totalConsignmentAmount)"></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                    Ôm hàng
                </span>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex items-center gap-2.5">
            <button type="button" @click="confirmClose()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Đóng (Esc)</span>
            </button>

            <button type="button" 
                    @click="submitOrder()" 
                    :disabled="submitting || items.length === 0"
                    class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-black text-xs rounded-xl shadow-md shadow-amber-600/20 transition-all flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-spinner fa-spin text-xs" x-show="submitting" style="display: none;"></i>
                <i class="fa-solid fa-check text-xs" x-show="!submitting"></i>
                <span x-text="submitting ? 'Đang lưu đơn...' : '{{ $isAccountant ? 'Lưu & Duyệt bốc hàng (F9)' : 'Gửi đơn chờ duyệt (F9)' }}'"></span>
            </button>
        </div>
    </div>
</div>

<script>
window._consignProducts = @json($products);

function consignmentFormApp() {
    return {
        submitting: false,
        errorMessage: '',
        isAccountant: {{ $isAccountant ? 'true' : 'false' }},
        currentUser: @json($currentUser),
        staffList: @json($staff),
        warehousesList: @json($warehouses),
        productsList: @json($products),

        selectedStaff: null,
        activeProdDropdownIndex: null,
        prodHighlightIndex: 0,
        openStaffDropdown: false,
        staffSearch: '',
        highlightedStaffIndex: 0,

        totalQuantity: 0,
        totalConsignmentAmount: 0,

        formData: {
            order_date: '{{ date("Y-m-d") }}',
            picking_date: '{{ date("Y-m-d") }}',
            staff_id: '{{ $isAccountant ? "" : ($currentUser?->id ?? "") }}',
            warehouse_id: '{{ $warehouses->first()?->id ?? "" }}',
            notes: ''
        },

        items: [
            {
                product_id: '',
                sku: '',
                name: '',
                searchQuery: '',
                unit: 'cái',
                stock_quantity: 0,
                unit_price: 0,
                quantity: 1,
                sell_price: 0,
                amount: 0,
                checked: true
            }
        ],

        init() {
            if (this.formData.staff_id) {
                const found = (this.staffList || []).find(s => s.id == this.formData.staff_id);
                if (found) {
                    this.selectedStaff = found;
                    this.staffSearch = found.name + (found.code ? ' (' + found.code + ')' : '');
                } else {
                    this.selectedStaff = this.isAccountant ? null : this.currentUser;
                }
            } else {
                this.selectedStaff = this.isAccountant ? null : this.currentUser;
                if (!this.isAccountant && this.currentUser) {
                    this.formData.staff_id = this.currentUser.id;
                }
            }
            this.calculateTotals();
        },

        getFilteredStaff(query) {
            const list = this.staffList || [];
            if (!query || !query.trim()) {
                return list;
            }
            const cleanQ = this.removeAccents(query);
            const rawQ = query.toLowerCase().trim();
            const words = cleanQ.split(/\s+/).filter(Boolean);

            return list.filter(s => {
                const name = (s.name || '').toLowerCase();
                const code = (s.code || '').toLowerCase();
                const phone = (s.phone || '').toLowerCase();
                const cleanName = this.removeAccents(s.name || '');
                const cleanCode = this.removeAccents(s.code || '');
                const fullClean = `${cleanCode} ${cleanName} ${phone}`;

                if (name.includes(rawQ) || code.includes(rawQ) || phone.includes(rawQ) || cleanName.includes(cleanQ)) {
                    return true;
                }
                return words.every(w => fullClean.includes(w));
            });
        },

        selectStaff(s) {
            if (!s) return;
            this.formData.staff_id = s.id;
            this.selectedStaff = s;
            this.staffSearch = s.name + (s.code ? ' (' + s.code + ')' : '');
            this.openStaffDropdown = false;
        },

        clearStaff() {
            this.formData.staff_id = '';
            this.selectedStaff = null;
            this.staffSearch = '';
            this.openStaffDropdown = false;
        },

        onStaffInput() {
            if (!this.staffSearch.trim()) {
                this.formData.staff_id = '';
                this.selectedStaff = null;
            }
        },

        closeStaffDropdown() {
            this.openStaffDropdown = false;
            if (this.selectedStaff && this.formData.staff_id) {
                this.staffSearch = this.selectedStaff.name + (this.selectedStaff.code ? ' (' + this.selectedStaff.code + ')' : '');
            } else if (!this.formData.staff_id) {
                this.staffSearch = '';
            }
        },

        scrollStaffIntoView(idx) {
            this.$nextTick(() => {
                const el = document.getElementById('staff-opt-' + idx);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        onStaffSelected() {
            const found = (this.staffList || []).find(s => s.id == this.formData.staff_id);
            this.selectedStaff = found || this.currentUser;
        },

        handleGlobalKeydown(e) {
            // F8 or Ctrl+Enter: Thêm dòng
            if (e.key === 'F8' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                this.addNewRow();
                return;
            }

            // F9: Lưu đơn hàng
            if (e.key === 'F9') {
                e.preventDefault();
                this.submitOrder();
                return;
            }

            // Escape: Đóng hoặc quay lại
            if (e.key === 'Escape' && this.activeProdDropdownIndex === null) {
                this.confirmClose();
            }
        },

        onStaffSelected() {
            const found = (this.staffList || []).find(s => s.id == this.formData.staff_id);
            this.selectedStaff = found || this.currentUser;
        },

        addNewRow() {
            this.items.push({
                product_id: '',
                sku: '',
                name: '',
                searchQuery: '',
                unit: 'cái',
                stock_quantity: 0,
                unit_price: 0,
                quantity: 1,
                sell_price: 0,
                amount: 0,
                checked: true
            });
            const newIndex = this.items.length - 1;
            this.activeProdDropdownIndex = newIndex;
            this.prodHighlightIndex = 0;
            this.$nextTick(() => {
                const input = document.getElementById('row-product-input-' + newIndex);
                if (input) input.focus();
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            } else {
                this.items = [{
                    product_id: '',
                    sku: '',
                    name: '',
                    searchQuery: '',
                    unit: 'cái',
                    stock_quantity: 0,
                    unit_price: 0,
                    quantity: 1,
                    sell_price: 0,
                    amount: 0,
                    checked: true
                }];
            }
            this.activeProdDropdownIndex = null;
            this.calculateTotals();
        },

        removeAccents(str) {
            if (!str) return '';
            return str
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'D')
                .toLowerCase()
                .trim();
        },

        clearProductRow(index) {
            const it = this.items[index];
            if (!it) return;
            it.product_id = '';
            it.sku = '';
            it.name = '';
            it.searchQuery = '';
            it.unit = 'cái';
            it.stock_quantity = 0;
            it.unit_price = 0;
            it.sell_price = 0;
            it.amount = 0;
            this.activeProdDropdownIndex = index;
            this.prodHighlightIndex = 0;
            this.calculateTotals();
            this.$nextTick(() => {
                const inp = document.getElementById('row-product-input-' + index);
                if (inp) {
                    inp.focus();
                    inp.select();
                }
            });
        },

        getFilteredProducts(query) {
            const list = (this.productsList && this.productsList.length > 0) ? this.productsList : (window._consignProducts || []);
            if (!query || !query.trim()) {
                return list.slice(0, 30);
            }

            // Xóa dấu ngoặc vuông nếu query có dạng "[SKU] Tên sản phẩm"
            const cleanStr = query.replace(/[\[\]\(\)\-\,\.]/g, ' ').trim();
            const cleanQ = this.removeAccents(cleanStr);
            const rawQ = cleanStr.toLowerCase();
            const words = cleanQ.split(/\s+/).filter(Boolean);

            return list.filter(p => {
                const sku = (p.sku || '').toLowerCase();
                const name = (p.name || '').toLowerCase();
                const cleanName = this.removeAccents(p.name || '');
                const cleanSku = this.removeAccents(p.sku || '');
                const fullClean = `${cleanSku} ${cleanName}`;

                if (sku.includes(rawQ) || name.includes(rawQ) || cleanSku.includes(cleanQ) || cleanName.includes(cleanQ)) {
                    return true;
                }
                return words.every(w => fullClean.includes(w));
            }).slice(0, 35);
        },

        selectProductForRow(index, prod) {
            if (!prod) return;
            const item = this.items[index];
            item.product_id = prod.id;
            item.sku = prod.sku || '';
            item.name = prod.name || '';
            item.searchQuery = (prod.sku ? `[${prod.sku}] ` : '') + prod.name;
            item.unit = prod.unit || 'cái';
            item.stock_quantity = prod.stock_quantity || 0;
            const price = Number(prod.price || prod.sell_price || prod.cost_price || 0);
            item.unit_price = price;
            item.sell_price = price;
            if (!item.quantity || Number(item.quantity) <= 0) {
                item.quantity = 1;
            }
            this.activeProdDropdownIndex = null;
            this.calculateTotals();
        },

        focusQty(index) {
            this.activeProdDropdownIndex = null;
            this.$nextTick(() => {
                const q = document.getElementById('row-qty-input-' + index);
                if (q) {
                    q.focus();
                    q.select();
                }
            });
        },

        scrollProdIntoView(rowIndex, prodIndex) {
            this.$nextTick(() => {
                const itemEl = document.getElementById('prod-opt-' + rowIndex + '-' + prodIndex);
                if (itemEl) itemEl.scrollIntoView({ block: 'nearest' });
            });
        },

        calculateTotals() {
            let totalQty = 0;
            let totalConsignment = 0;

            this.items.forEach(it => {
                if (it.checked !== false && it.product_id) {
                    const q = Number(it.quantity) || 0;
                    const uPrice = Number(it.unit_price) || 0;

                    it.amount = q * uPrice;
                    it.sell_price = uPrice;
                    totalQty += q;
                    totalConsignment += it.amount;
                } else {
                    it.amount = 0;
                }
            });

            this.totalQuantity = totalQty;
            this.totalConsignmentAmount = totalConsignment;
        },

        confirmClose() {
            if (this.items.some(it => it.product_id) && !confirm('Bạn có chắc muốn thoát? Các thông tin đang nhập sẽ không được lưu.')) {
                return;
            }
            window.location.href = '{{ route("orders.index", ["tab" => "consignments"]) }}';
        },

        async submitOrder() {
            this.errorMessage = '';

            if (!this.formData.staff_id) {
                this.errorMessage = 'Vui lòng chọn nhân viên nhận hàng ôm!';
                return;
            }

            if (!this.formData.warehouse_id) {
                this.errorMessage = 'Vui lòng chọn kho xuất hàng công ty!';
                return;
            }

            const validItems = this.items.filter(it => it.product_id && (it.checked !== false) && Number(it.quantity) > 0);
            if (validItems.length === 0) {
                this.errorMessage = 'Vui lòng chọn ít nhất một sản phẩm và nhập số lượng lớn hơn 0!';
                return;
            }

            this.submitting = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/consignment-orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        staff_id: this.formData.staff_id,
                        warehouse_id: this.formData.warehouse_id,
                        notes: this.formData.notes,
                        items: validItems.map(it => ({
                            product_id: it.product_id,
                            quantity: Number(it.quantity),
                            unit_price: Number(it.unit_price),
                            sell_price: Number(it.sell_price),
                            unit: it.unit
                        }))
                    })
                });

                const data = await res.json().catch(() => ({ success: false, message: 'Phản hồi không hợp lệ từ máy chủ' }));

                if (data.success) {
                    alert(data.message || 'Tạo đơn hàng ôm thành công!');
                    window.location.href = data.redirect || '{{ route("orders.index", ["tab" => "consignments"]) }}';
                } else {
                    this.errorMessage = data.message || 'Lỗi khi tạo đơn hàng ôm';
                }
            } catch (e) {
                this.errorMessage = 'Lỗi kết nối khi gửi đơn hàng ôm. Vui lòng thử lại!';
            } finally {
                this.submitting = false;
            }
        },

        formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0)) + ' đ';
        },

        formatNumber(num) {
            return new Intl.NumberFormat('vi-VN').format(num || 0);
        }
    };
}
</script>
@endsection
