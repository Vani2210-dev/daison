{{-- Modal Hoàn Tất Bảo Dưỡng & Nhập Thay Thế Phụ Tùng (Tính tiền hoặc Miễn phí) --}}
<div x-show="maintenanceCompleteModalOpen" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    <div class="flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4 text-center">
        <!-- Backdrop -->
        <div x-show="maintenanceCompleteModalOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 transition-opacity bg-slate-900/70 backdrop-blur-xs" 
             @click="closeCompleteMaintenanceModal()"></div>

        <!-- Modal Dialog -->
        <div x-show="maintenanceCompleteModalOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" 
             class="relative w-full max-w-4xl bg-white text-left align-bottom sm:align-middle transition-all transform rounded-t-2xl sm:rounded-2xl shadow-2xl flex flex-col max-h-[94vh] sm:max-h-[90vh] overflow-hidden border border-slate-200">

            <!-- HEADER (Cố định ở trên) -->
            <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white shrink-0 flex items-center justify-between border-b border-slate-700">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 shrink-0">
                        <i class="fa-solid fa-wrench text-base sm:text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-black text-white tracking-tight truncate" id="modal-title">
                            Nghiệm Thu Bảo Dưỡng & Thay Phụ Tùng
                        </h3>
                        <div class="text-[11px] sm:text-xs text-slate-300 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                            <span class="truncate">
                                Khách: <strong class="text-white" x-text="maintCurrentMachine.customer_name || 'Khách hàng'"></strong>
                            </span>
                            <span class="text-slate-500" x-show="maintCurrentMachine.customer_phone">&bull;</span>
                            <a :href="`tel:${maintCurrentMachine.customer_phone}`" 
                               x-show="maintCurrentMachine.customer_phone" 
                               class="font-mono text-emerald-400 font-bold hover:underline">
                                <i class="fa-solid fa-phone text-[10px]"></i>
                                <span x-text="maintCurrentMachine.customer_phone"></span>
                            </a>
                            <span class="text-slate-500 hidden sm:inline" x-show="maintCurrentMachine.machine_model">&bull;</span>
                            <span class="text-indigo-300 truncate hidden sm:inline" x-text="maintCurrentMachine.machine_model"></span>
                        </div>
                    </div>
                </div>

                <button type="button" 
                        @click="closeCompleteMaintenanceModal()" 
                        class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition cursor-pointer shrink-0 ml-2">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- BODY (Cuộn linh hoạt, hỗ trợ mobile chạm vuốt mượt mà) -->
            <div class="p-3 sm:p-5 overflow-y-auto space-y-4 sm:space-y-5 bg-slate-50/60 flex-1">

                <!-- SECTION 1: PHỤ TÙNG / LÕI LỌC THAY THẾ -->
                <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
                    <!-- Tiêu đề + Nút công cụ -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">
                                <i class="fa-solid fa-boxes-stacked text-xs"></i>
                            </span>
                            <h4 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider">
                                Phụ Tùng / Lõi Lọc Thay Đợt Này
                            </h4>
                            <span class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 font-bold text-[11px]" x-text="maintItems.length"></span>
                        </div>

                        <!-- Các nút tác vụ nhanh -->
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button type="button" 
                                    @click="loadMachineAccessories()" 
                                    class="flex-1 sm:flex-none px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200/70 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs active:scale-95">
                                <i class="fa-solid fa-rotate text-[11px]"></i>
                                <span>Nạp lõi theo máy</span>
                            </button>

                            <button type="button" 
                                    @click="addCustomMaintPartRow()" 
                                    class="flex-1 sm:flex-none px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs border border-emerald-200/70 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs active:scale-95">
                                <i class="fa-solid fa-plus text-[11px]"></i>
                                <span>+ Thêm dòng ngoài</span>
                            </button>
                        </div>
                    </div>

                    <!-- Thanh chọn nhanh từ KHO PHỤ TÙNG -->
                    <div class="mt-3 p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600 shrink-0">
                            <i class="fa-solid fa-warehouse text-amber-600"></i>
                            <span>Lấy từ Kho:</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <select x-model="selectedWarehousePartId" 
                                    class="w-full text-xs font-semibold rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500 py-1.5 bg-white">
                                <option value="">-- Bấm để chọn phụ tùng từ kho --</option>
                                <template x-for="p in filterPartsList" :key="p.id">
                                    <option :value="p.id" x-text="`${p.name} - Tồn: ${p.stock_qty} ${p.unit || ''} - Giá xuất: ${(p.out_price || 0).toLocaleString('vi-VN')}đ`"></option>
                                </template>
                            </select>
                        </div>
                        <button type="button" 
                                @click="addSelectedWarehousePart()" 
                                :disabled="!selectedWarehousePartId"
                                :class="!selectedWarehousePartId ? 'opacity-50 cursor-not-allowed bg-slate-200 text-slate-400' : 'bg-teal-600 hover:bg-teal-700 text-white cursor-pointer shadow-sm active:scale-95'"
                                class="px-3.5 py-1.5 rounded-lg font-bold text-xs transition flex items-center justify-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-cart-plus text-xs"></i>
                            <span>Thêm vào đơn</span>
                        </button>
                    </div>

                    <!-- DANH SÁCH CÁC THẺ LINH KIỆN (Responsive cực đẹp cả trên Mobile & Desktop) -->
                    <div class="mt-3 space-y-2.5">
                        <template x-for="(item, idx) in maintItems" :key="idx">
                            <div class="bg-slate-50/70 hover:bg-slate-50 p-3 rounded-xl border border-slate-200/90 transition-all shadow-2xs">
                                <!-- DÒNG 1: TÌM / CHỌN TỪ KHO HOẶC TỰ NHẬP TÊN -->
                                <div class="flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 font-black text-xs flex items-center justify-center shrink-0 mt-1.5" x-text="idx + 1"></div>
                                    
                                    <!-- Ô Autocomplete tìm kiếm & chọn tên phụ tùng -->
                                    <div class="relative flex-1 min-w-0" @click.away="item.showDropdown = false">
                                        <div class="relative">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   x-model="item.part_name" 
                                                   @focus="item.showDropdown = true" 
                                                   @input="item.showDropdown = true" 
                                                   placeholder="Gõ tìm kiếm hoặc nhập tên phụ tùng / lõi lọc..." 
                                                   class="w-full text-xs sm:text-sm font-bold text-slate-800 rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 py-2 pl-8 pr-8 bg-white shadow-2xs">

                                            <!-- Nút bấm mở/đóng danh sách gợi ý -->
                                            <button type="button" 
                                                    @click.stop="item.showDropdown = !item.showDropdown" 
                                                    class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400 hover:text-teal-600 flex items-center justify-center cursor-pointer transition"
                                                    title="Bấm để chọn từ danh mục phụ tùng kho">
                                                <i class="fa-solid fa-chevron-down text-[11px] transition-transform duration-200" :class="{ 'rotate-180': item.showDropdown }"></i>
                                            </button>
                                        </div>

                                        <!-- Badge mã kho nếu đã chọn -->
                                        <div x-show="item.part_code" class="text-[10px] text-teal-700 font-mono mt-1 flex items-center gap-1.5 pl-1">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                            <span>Mã kho: <strong x-text="item.part_code"></strong></span>
                                        </div>

                                        <!-- DROPDOWN GỢI Ý PHỤ TÙNG TỪ KHO -->
                                        <div x-show="item.showDropdown" 
                                             x-cloak 
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 translate-y-1"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 max-h-56 overflow-y-auto">
                                            
                                            <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between sticky top-0 border-b border-slate-100 z-10">
                                                <span>Gợi ý từ Kho Phụ Tùng (<span x-text="getFilteredPartsForMaint(item.part_name).length"></span>)</span>
                                                <span class="text-teal-700 font-normal">Chạm để chọn</span>
                                            </div>

                                            <template x-for="p in getFilteredPartsForMaint(item.part_name)" :key="p.id">
                                                <div @click="selectPartForItem(item, p)" 
                                                     class="px-3 py-2 hover:bg-teal-50/80 cursor-pointer flex items-center justify-between gap-2 transition active:bg-teal-100">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="font-bold text-xs text-slate-800 truncate" x-text="p.name"></div>
                                                        <div class="text-[10px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                            <span class="font-mono text-slate-400 font-bold" x-text="p.code"></span>
                                                            <span class="text-slate-300">&bull;</span>
                                                            <span class="text-emerald-700 font-semibold" x-text="`Tồn: ${p.stock_qty} ${p.unit || ''}`"></span>
                                                        </div>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="font-mono font-bold text-xs text-amber-700" x-text="`${(p.out_price || 0).toLocaleString('vi-VN')} đ`"></span>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Nếu không có kết quả khớp -->
                                            <div x-show="getFilteredPartsForMaint(item.part_name).length === 0" 
                                                 class="p-3 text-center text-xs text-slate-500">
                                                <p class="font-medium">Không tìm thấy linh kiện nào trong kho khớp với từ khóa.</p>
                                                <p class="text-[11px] text-teal-700 mt-1">Bạn có thể giữ nguyên tên vừa nhập để dùng làm linh kiện ngoài kho.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" 
                                            @click="removeMaintItemRow(idx)" 
                                            class="text-slate-400 hover:text-rose-600 p-2 rounded-xl hover:bg-rose-50 transition cursor-pointer shrink-0 mt-0.5" 
                                            title="Xóa linh kiện này">
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>
                                </div>

                                <!-- DÒNG 2: THÔNG SỐ, CHẾ ĐỘ TÍNH TIỀN, ĐƠN GIÁ & THÀNH TIỀN -->
                                <div class="mt-2.5 pt-2.5 border-t border-slate-200/60 grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center">
                                    <!-- Số lượng & ĐVT (sm:col-span-3) -->
                                    <div class="flex items-center gap-1.5 sm:col-span-3">
                                        <span class="text-[11px] font-bold text-slate-500 shrink-0">SL:</span>
                                        <div class="flex items-center border border-slate-300 rounded-lg bg-white overflow-hidden shrink-0">
                                            <button type="button" 
                                                    @click="item.quantity = Math.max(1, (item.quantity || 1) - 1)" 
                                                    class="px-2 py-1 text-slate-500 hover:bg-slate-100 active:bg-slate-200 font-bold text-xs cursor-pointer">-</button>
                                            <input type="number" 
                                                   min="1" 
                                                   x-model.number="item.quantity" 
                                                   @input="item.quantity = Math.max(1, item.quantity || 1)"
                                                   class="w-12 text-center text-xs font-black text-slate-800 border-x border-slate-200 py-1 px-0.5 focus:outline-hidden">
                                            <button type="button" 
                                                    @click="item.quantity = (item.quantity || 1) + 1" 
                                                    class="px-2 py-1 text-slate-500 hover:bg-slate-100 active:bg-slate-200 font-bold text-xs cursor-pointer">+</button>
                                        </div>
                                        <input type="text" 
                                               x-model="item.unit" 
                                               placeholder="ĐVT" 
                                               class="w-14 text-center text-xs text-slate-700 rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500 py-1 px-1 bg-white" title="Đơn vị tính">
                                    </div>

                                    <!-- Bộ chọn tính phí: Miễn phí vs Thu tiền (sm:col-span-4) -->
                                    <div class="sm:col-span-4 flex items-center justify-start sm:justify-center">
                                        <div class="inline-flex rounded-xl p-0.5 bg-slate-200/90 border border-slate-300/80 w-full sm:w-auto shadow-2xs">
                                            <button type="button" 
                                                    @click="setPartPricing(item, true)" 
                                                    :class="item.is_free ? 'bg-emerald-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-slate-900 font-bold'"
                                                    class="flex-1 sm:flex-none px-3 py-1.5 text-[11px] rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                                <i class="fa-solid fa-gift text-[11px]"></i>
                                                <span>Miễn phí (0đ)</span>
                                            </button>
                                            <button type="button" 
                                                    @click="setPartPricing(item, false)" 
                                                    :class="!item.is_free ? 'bg-amber-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-slate-900 font-bold'"
                                                    class="flex-1 sm:flex-none px-3 py-1.5 text-[11px] rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                                <i class="fa-solid fa-dollar-sign text-[11px]"></i>
                                                <span>Thu tiền</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Đơn giá & Thành tiền (sm:col-span-5) -->
                                    <div class="sm:col-span-5 flex items-center justify-between sm:justify-end gap-2">
                                        <!-- Khi miễn phí -->
                                        <template x-if="item.is_free">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/70">
                                                    0 đ <span class="text-[10px] font-medium text-emerald-600">(Bảo hành/Tặng)</span>
                                                </span>
                                            </div>
                                        </template>

                                        <!-- Khi thu tiền -->
                                        <template x-if="!item.is_free">
                                            <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-[11px] text-slate-500 font-bold">Đơn giá:</span>
                                                    <input type="number" 
                                                           min="0" 
                                                           step="1000"
                                                           x-model.number="item.unit_price" 
                                                           placeholder="0"
                                                           class="w-24 sm:w-28 text-right text-xs font-black text-amber-900 rounded-lg border-amber-300 focus:border-amber-500 focus:ring-amber-500 py-1 px-2 bg-amber-50/50">
                                                    <span class="text-[11px] font-bold text-slate-500">đ</span>
                                                </div>
                                                <div class="text-right pl-2 border-l border-slate-200">
                                                    <div class="text-[10px] text-slate-400 font-bold">Thành tiền:</div>
                                                    <div class="text-xs sm:text-sm font-black font-mono text-slate-900" 
                                                         x-text="((item.quantity * (item.unit_price || 0)).toLocaleString('vi-VN') + ' đ')">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- DÒNG 3: GHI CHÚ NHANH CHO LINH KIỆN -->
                                <div class="mt-2 pt-2 border-t border-slate-200/50">
                                    <input type="text" 
                                           x-model="item.note" 
                                           placeholder="Ghi chú linh kiện này (VD: Lõi 1 PP 5 micron, bảo hành theo máy...)" 
                                           class="w-full text-[11px] text-slate-600 rounded-lg border-slate-200/80 focus:border-teal-500 focus:ring-teal-500 py-1 px-2 bg-white/80 placeholder:text-slate-400">
                                </div>
                            </div>
                        </template>

                        <!-- Empty state -->
                        <div x-show="maintItems.length === 0" 
                             class="py-6 px-4 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50">
                            <i class="fa-solid fa-toolbox text-slate-300 text-2xl mb-1.5 block"></i>
                            <p class="text-xs font-bold text-slate-600">Chưa có phụ tùng thay thế nào được thêm</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Nếu lần này chỉ vệ sinh máy định kỳ mà không thay phụ tùng, bạn có thể để trống và bấm Lưu hoàn tất bên dưới.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: TỔNG HỢP CHI PHÍ & THANH TOÁN DỊCH VỤ -->
                <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-3.5 sm:p-4 rounded-2xl shadow-md border border-slate-700">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center font-bold text-xs">
                            <i class="fa-solid fa-receipt text-xs"></i>
                        </span>
                        <h4 class="text-xs sm:text-sm font-black text-white uppercase tracking-wider">
                            Tổng Hợp Chi Phí Dịch Vụ
                        </h4>
                    </div>

                    <!-- 4 Khối Tài Chính (Responsive 2x2 trên mobile, 4 cột trên desktop) -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
                        <!-- 1. Tiền linh kiện -->
                        <div class="bg-slate-800/80 p-2.5 sm:p-3 rounded-xl border border-slate-700">
                            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 block uppercase">1. Tiền phụ tùng (A)</span>
                            <div class="text-sm sm:text-base font-black text-white mt-1 font-mono tracking-tight" x-text="calcMaintPartsFee().toLocaleString('vi-VN') + ' đ'"></div>
                            <span class="text-[9px] text-slate-400 hidden sm:block">Chỉ tính phụ tùng có phí</span>
                        </div>

                        <!-- 2. Tiền công kỹ thuật -->
                        <div class="bg-slate-800/80 p-2.5 sm:p-3 rounded-xl border border-slate-700">
                            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 block uppercase">2. Tiền công KTV (B)</span>
                            <div class="mt-1 flex items-center gap-1">
                                <input type="number" 
                                       min="0" 
                                       step="10000"
                                       x-model.number="maintForm.labor_fee" 
                                       placeholder="0"
                                       class="w-full text-xs sm:text-sm font-black text-teal-300 rounded-lg border-slate-600 bg-slate-900/80 focus:border-teal-400 focus:ring-teal-400 py-1 px-2 font-mono">
                                <span class="text-xs text-slate-400">đ</span>
                            </div>
                        </div>

                        <!-- 3. Giảm giá / Chiết khấu -->
                        <div class="bg-slate-800/80 p-2.5 sm:p-3 rounded-xl border border-slate-700">
                            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 block uppercase">3. Giảm giá (C)</span>
                            <div class="mt-1 flex items-center gap-1">
                                <input type="number" 
                                       min="0" 
                                       step="10000"
                                       x-model.number="maintForm.discount" 
                                       placeholder="0"
                                       class="w-full text-xs sm:text-sm font-black text-rose-300 rounded-lg border-slate-600 bg-slate-900/80 focus:border-rose-400 focus:ring-rose-400 py-1 px-2 font-mono">
                                <span class="text-xs text-slate-400">đ</span>
                            </div>
                        </div>

                        <!-- 4. TỔNG CẦN THU (A + B - C) -->
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-2.5 sm:p-3 rounded-xl shadow-lg shadow-emerald-500/20 col-span-2 sm:col-span-1 lg:col-span-1">
                            <span class="text-[10px] sm:text-[11px] font-bold text-emerald-100 block uppercase">TỔNG CẦN THU (A+B-C)</span>
                            <div class="text-base sm:text-xl font-black mt-0.5 font-mono tracking-tight" x-text="calcMaintTotalAmount().toLocaleString('vi-VN') + ' đ'"></div>
                            <span class="text-[9px] text-emerald-100" x-show="calcMaintTotalAmount() === 0">Miễn phí hoàn toàn</span>
                            <span class="text-[9px] text-emerald-100" x-show="calcMaintTotalAmount() > 0">Khách cần thanh toán</span>
                        </div>
                    </div>

                    <!-- TRẠNG THÁI THANH TOÁN (Dạng Segmented Buttons cực nhạy trên Mobile) -->
                    <div class="mt-3.5 pt-3 border-t border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <span class="text-xs font-bold text-slate-300">Trạng thái thu tiền từ khách:</span>
                        <div class="grid grid-cols-3 gap-1.5 w-full sm:w-auto">
                            <!-- Đã thu đủ -->
                            <button type="button" 
                                    @click="maintForm.payment_status = 'paid'"
                                    :disabled="calcMaintTotalAmount() === 0"
                                    :class="maintForm.payment_status === 'paid' ? 'bg-emerald-500 text-white font-black shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white border border-slate-700 font-semibold'"
                                    class="py-2 px-2.5 rounded-xl text-xs transition cursor-pointer text-center flex items-center justify-center gap-1 disabled:opacity-40">
                                <i class="fa-solid fa-circle-check text-[10px]"></i>
                                <span>Đã thu tiền</span>
                            </button>

                            <!-- Khách nợ -->
                            <button type="button" 
                                    @click="maintForm.payment_status = 'unpaid'"
                                    :disabled="calcMaintTotalAmount() === 0"
                                    :class="maintForm.payment_status === 'unpaid' ? 'bg-amber-500 text-white font-black shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white border border-slate-700 font-semibold'"
                                    class="py-2 px-2.5 rounded-xl text-xs transition cursor-pointer text-center flex items-center justify-center gap-1 disabled:opacity-40">
                                <i class="fa-solid fa-clock text-[10px]"></i>
                                <span>Khách nợ</span>
                            </button>

                            <!-- Miễn phí -->
                            <button type="button" 
                                    @click="maintForm.payment_status = 'free'"
                                    :class="maintForm.payment_status === 'free' ? 'bg-sky-500 text-white font-black shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white border border-slate-700 font-semibold'"
                                    class="py-2 px-2.5 rounded-xl text-xs transition cursor-pointer text-center flex items-center justify-center gap-1">
                                <i class="fa-solid fa-gift text-[10px]"></i>
                                <span>Miễn phí 0đ</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: HẸN KỲ TIẾP THEO & GHI CHÚ KỸ THUẬT -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Hẹn chu kỳ -->
                    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar-check text-indigo-600"></i>
                                <span>Chu kỳ hẹn tiếp theo:</span>
                            </label>
                            <span class="text-xs font-mono font-black text-indigo-700" x-text="`+${maintForm.filter_replacement_months} Tháng`"></span>
                        </div>

                        <!-- 4 nút chọn tháng nhanh -->
                        <div class="grid grid-cols-4 gap-1.5 mb-2.5">
                            <button type="button" 
                                    @click="setMaintMonths(3)" 
                                    :class="maintForm.filter_replacement_months === 3 ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold'"
                                    class="py-2 rounded-xl text-xs transition cursor-pointer text-center active:scale-95">
                                +3 Tháng
                            </button>
                            <button type="button" 
                                    @click="setMaintMonths(6)" 
                                    :class="maintForm.filter_replacement_months === 6 ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold'"
                                    class="py-2 rounded-xl text-xs transition cursor-pointer text-center active:scale-95">
                                +6 Tháng
                            </button>
                            <button type="button" 
                                    @click="setMaintMonths(9)" 
                                    :class="maintForm.filter_replacement_months === 9 ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold'"
                                    class="py-2 rounded-xl text-xs transition cursor-pointer text-center active:scale-95">
                                +9 Tháng
                            </button>
                            <button type="button" 
                                    @click="setMaintMonths(12)" 
                                    :class="maintForm.filter_replacement_months === 12 ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold'"
                                    class="py-2 rounded-xl text-xs transition cursor-pointer text-center active:scale-95">
                                +12 Tháng
                            </button>
                        </div>

                        <!-- Ngày hẹn dự kiến -->
                        <div>
                            <span class="text-[11px] text-slate-500 font-bold block mb-1">Ngày hẹn dự kiến:</span>
                            <input type="date" 
                                   x-model="maintForm.next_maintenance_date" 
                                   class="w-full text-xs font-bold text-slate-800 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 bg-slate-50">
                        </div>
                    </div>

                    <!-- Ghi chú bảo dưỡng -->
                    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col">
                        <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5 mb-2">
                            <i class="fa-solid fa-notes-medical text-amber-600"></i>
                            <span>Ghi chú kỹ thuật & Tình trạng máy:</span>
                        </label>
                        <textarea x-model="maintForm.notes" 
                                  rows="3" 
                                  placeholder="Nhập ghi chú (VD: Đã súc rửa màng RO, thay lõi 1 2 3, đo nước đầu vào 150ppm, nước ra 6ppm, áp lực nước đạt 2.5 bar...)"
                                  class="w-full flex-1 text-xs text-slate-800 rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 p-2.5 bg-slate-50 placeholder:text-slate-400"></textarea>
                    </div>
                </div>
            </div>

            <!-- FOOTER (Cố định dưới cùng, bấm cực kỳ chắc chắn) -->
            <div class="px-4 py-3 sm:px-6 sm:py-3.5 bg-white border-t border-slate-200 shrink-0 flex items-center justify-between gap-3">
                <button type="button" 
                        @click="closeCompleteMaintenanceModal()" 
                        class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition cursor-pointer active:scale-95">
                    Hủy bỏ
                </button>

                <button type="button" 
                        @click="submitCompleteMaintenance()" 
                        :disabled="maintSubmitting"
                        class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs sm:text-sm shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition hover:scale-[1.02] active:scale-[0.98] cursor-pointer disabled:opacity-50">
                    <template x-if="maintSubmitting">
                        <i class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                    </template>
                    <template x-if="!maintSubmitting">
                        <i class="fa-solid fa-circle-check text-sm sm:text-base"></i>
                    </template>
                    <span x-text="maintSubmitting ? 'Đang lưu bảo dưỡng...' : 'LƯU & HOÀN TẤT BẢO DƯỠNG'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
