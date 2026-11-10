<!-- ========================================================================= -->
<!-- MODAL: PHẢN ÁNH THỪA / THIẾU HÀNG SAU KHI XUẤT BẾN (TỐI ƯU CHO MOBILE)   -->
<!-- ========================================================================= -->
<div x-show="showDiscrepancyModal" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-2 sm:p-4 bg-slate-900/80 backdrop-blur-xs overflow-y-auto"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="showDiscrepancyModal = false"
         class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden my-auto flex flex-col max-h-[96vh] sm:max-h-[90vh]">

        <!-- 1. Header Modal (Tối ưu nhỏ gọn, không tràn chữ trên Mobile) -->
        <div class="px-3.5 py-2.5 sm:px-5 sm:py-3.5 bg-gradient-to-r from-amber-600 via-amber-700 to-orange-700 text-white flex items-center justify-between gap-2.5 shrink-0">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center text-sm sm:text-base shadow-inner shrink-0">
                    <i class="fa-solid fa-scale-unbalanced text-white"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-black text-xs sm:text-base leading-tight">
                        Phản Ánh Lệch Hàng Trên Xe
                    </h3>
                    <p class="text-[10px] sm:text-[11px] text-amber-100/90 truncate mt-0.5">
                        Lái xe ⇄ QC Kiểm hàng sau xuất bến
                    </p>
                </div>
            </div>

            <button type="button" @click="showDiscrepancyModal = false"
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer shrink-0">
                <i class="fa-solid fa-xmark text-xs sm:text-sm"></i>
            </button>
        </div>

        <!-- 2. Navigation Tabs (Chia đều 50-50, chạm cảm ứng dễ dàng) -->
        <div class="px-3 sm:px-5 py-2 border-b border-slate-100 bg-slate-50 shrink-0">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="discrepancyTab = 'create'"
                        class="py-1.5 sm:py-2 px-2 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-1.5 cursor-pointer text-center"
                        :class="discrepancyTab === 'create' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    <i class="fa-solid fa-circle-plus text-xs"></i>
                    <span>Tạo phản ánh mới</span>
                </button>

                <button type="button" @click="discrepancyTab = 'history'"
                        class="py-1.5 sm:py-2 px-2 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-1.5 cursor-pointer text-center"
                        :class="discrepancyTab === 'history' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    <i class="fa-solid fa-comments text-xs"></i>
                    <span>Lịch sử & Phản hồi</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                          :class="discrepancyTab === 'history' ? 'bg-black/20 text-white' : 'bg-amber-100 text-amber-800'"
                          x-text="discrepancies.length"></span>
                </button>
            </div>
        </div>

        <!-- 3. Modal Body (Cuộn mượt, padding và khoảng cách gọn nhẹ) -->
        <div class="p-3 sm:p-4.5 overflow-y-auto space-y-3 sm:space-y-3.5 text-xs flex-1 scrollbar-thin">

            <!-- ============================================================= -->
            <!-- TAB 1: TẠO PHẢN ÁNH MỚI                                      -->
            <!-- ============================================================= -->
            <div x-show="discrepancyTab === 'create'" class="space-y-3 sm:space-y-3.5">

                <!-- 1. Chọn Đơn Hàng -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700 text-xs">
                            Đơn hàng bị lệch <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500 font-normal">
                            Xe: <strong class="text-purple-700 font-mono font-bold" x-text="selectedVehicle?.plate_number"></strong>
                        </span>
                    </div>

                    <select x-model="discrepancyForm.order_id" @change="onDiscrepancyOrderChange($event.target.value)"
                            class="w-full px-3 py-2 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-bold text-slate-800 outline-none focus:border-amber-500 transition-all cursor-pointer text-xs sm:text-sm">
                        <option value="">-- Chọn đơn hàng trên xe --</option>
                        <template x-for="ord in orders" :key="ord.id">
                            <option :value="ord.id" x-text="ord.order_code + ' - ' + ord.customer_name + ' (' + (ord.items ? ord.items.length : 0) + ' SP)'"></option>
                        </template>
                    </select>

                    <!-- QC phụ trách hiển thị dạng pill gọn gàng ngay dưới select -->
                    <template x-if="discrepancySelectedOrder">
                        <div class="mt-1.5 px-2.5 py-1 bg-amber-50/90 border border-amber-200/90 rounded-lg flex items-center justify-between text-[11px] text-amber-950">
                            <span class="flex items-center gap-1.5 truncate">
                                <i class="fa-solid fa-clipboard-user text-amber-600"></i>
                                <span>QC đơn: <strong class="text-slate-900" x-text="discrepancyCheckerName || 'Bộ phận QC / Kho xuất'"></strong></span>
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-200 text-amber-900 shrink-0">
                                Kho xuất
                            </span>
                        </div>
                    </template>
                </div>

                <!-- 2. Loại Lệch (4 nút xếp 1 hàng trên mobile, dễ chạm) -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">
                        Tính chất lệch hàng <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-4 gap-1.5">
                        <!-- Thiếu hàng -->
                        <button type="button" @click="discrepancyForm.discrepancy_type = 'shortage'; calcDiscrepancyDiff()"
                                class="py-2 px-1 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                :class="discrepancyForm.discrepancy_type === 'shortage' ? 'bg-rose-50 border-rose-500 text-rose-800 ring-2 ring-rose-500/20 font-black' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold'">
                            <i class="fa-solid fa-circle-minus text-rose-600 text-sm"></i>
                            <span class="text-[11px] leading-tight">Thiếu hàng</span>
                        </button>

                        <!-- Thừa hàng -->
                        <button type="button" @click="discrepancyForm.discrepancy_type = 'excess'; calcDiscrepancyDiff()"
                                class="py-2 px-1 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                :class="discrepancyForm.discrepancy_type === 'excess' ? 'bg-blue-50 border-blue-500 text-blue-800 ring-2 ring-blue-500/20 font-black' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold'">
                            <i class="fa-solid fa-circle-plus text-blue-600 text-sm"></i>
                            <span class="text-[11px] leading-tight">Thừa hàng</span>
                        </button>

                        <!-- Hàng hư hỏng / Bể vỡ -->
                        <button type="button" @click="discrepancyForm.discrepancy_type = 'damaged'; calcDiscrepancyDiff()"
                                class="py-2 px-1 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                :class="discrepancyForm.discrepancy_type === 'damaged' ? 'bg-orange-50 border-orange-500 text-orange-800 ring-2 ring-orange-500/20 font-black' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold'">
                            <i class="fa-solid fa-box-tissue text-orange-600 text-sm"></i>
                            <span class="text-[11px] leading-tight">Bể vỡ/Móp</span>
                        </button>

                        <!-- Sai quy cách / Sai mặt hàng -->
                        <button type="button" @click="discrepancyForm.discrepancy_type = 'wrong_item'; calcDiscrepancyDiff()"
                                class="py-2 px-1 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                :class="discrepancyForm.discrepancy_type === 'wrong_item' ? 'bg-purple-50 border-purple-500 text-purple-800 ring-2 ring-purple-500/20 font-black' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold'">
                            <i class="fa-solid fa-shuffle text-purple-600 text-sm"></i>
                            <span class="text-[11px] leading-tight">Sai mã/cách</span>
                        </button>
                    </div>
                </div>

                <!-- 3. Chọn Mặt Hàng Bị Lệch -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700 text-xs">
                            Sản phẩm bị lệch <span class="text-rose-500">*</span>
                        </label>
                        <template x-if="discrepancyOrderItems.length > 0">
                            <span class="text-[10px] text-slate-400 font-normal" x-text="discrepancyOrderItems.length + ' SP trong đơn'"></span>
                        </template>
                    </div>

                    <!-- Dropdown danh sách SP trong đơn -->
                    <template x-if="discrepancyOrderItems.length > 0">
                        <select @change="onDiscrepancyItemSelect($event.target.value)"
                                class="w-full px-3 py-2 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-bold text-slate-800 outline-none focus:border-amber-500 transition-all cursor-pointer text-xs mb-1.5">
                            <option value="">-- Chọn mặt hàng từ danh sách đơn --</option>
                            <template x-for="item in discrepancyOrderItems" :key="item.id || item.sku">
                                <option :value="item.sku || item.product_id" x-text="item.name + ' (' + item.quantity + ' ' + (item.unit || 'ĐVT') + ')'"></option>
                            </template>
                        </select>
                    </template>

                    <!-- Nhập tên SP và thông tin bổ trợ gọn gàng -->
                    <div class="space-y-1.5">
                        <input type="text" x-model="discrepancyForm.product_name"
                               placeholder="Tên sản phẩm thực tế bị lệch..."
                               class="w-full px-3 py-1.5 sm:py-2 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-bold text-slate-800 outline-none focus:border-amber-500 transition-all text-xs">

                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" x-model="discrepancyForm.sku"
                                   placeholder="Mã SKU (nếu có)..."
                                   class="px-3 py-1.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-mono text-[11px] text-slate-700 outline-none focus:border-amber-500 transition-all">
                            <input type="text" x-model="discrepancyForm.unit"
                                   placeholder="ĐVT (Thùng, Hộp...)"
                                   class="px-3 py-1.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-[11px] text-slate-700 outline-none focus:border-amber-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- 4. Số lượng theo phiếu vs Thực tế trên xe (Tích hợp nút tăng giảm nhanh) -->
                <div class="p-2.5 sm:p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
                    <div class="grid grid-cols-3 gap-1.5 sm:gap-2 text-center items-center">
                        <!-- Cột 1: Theo phiếu -->
                        <div class="bg-white p-2 rounded-xl border border-slate-200">
                            <span class="block text-[10px] font-bold text-slate-500 mb-0.5">Theo phiếu</span>
                            <input type="number" step="any" min="0" x-model.number="discrepancyForm.expected_qty" @input="calcDiscrepancyDiff()"
                                   class="w-full text-center font-mono font-bold text-xs sm:text-sm text-slate-700 bg-transparent outline-none">
                        </div>

                        <!-- Cột 2: Thực trên xe với nút tăng giảm + / - -->
                        <div class="bg-amber-50/80 p-1.5 sm:p-2 rounded-xl border-2 border-amber-400 shadow-2xs">
                            <span class="block text-[10px] font-bold text-amber-800 mb-0.5">Thực trên xe</span>
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" @click="discrepancyForm.actual_qty = Math.max(0, Number(discrepancyForm.actual_qty || 0) - 1); calcDiscrepancyDiff()"
                                        class="w-5 h-5 rounded-md bg-white hover:bg-slate-100 text-slate-700 font-black text-xs flex items-center justify-center border border-slate-200 cursor-pointer active:scale-90">
                                    -
                                </button>
                                <input type="number" step="any" min="0" x-model.number="discrepancyForm.actual_qty" @input="calcDiscrepancyDiff()"
                                       class="w-12 text-center font-mono font-black text-sm sm:text-base text-amber-900 bg-transparent outline-none">
                                <button type="button" @click="discrepancyForm.actual_qty = Number(discrepancyForm.actual_qty || 0) + 1; calcDiscrepancyDiff()"
                                        class="w-5 h-5 rounded-md bg-amber-500 hover:bg-amber-600 text-white font-black text-xs flex items-center justify-center cursor-pointer active:scale-90">
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Cột 3: Chênh lệch -->
                        <div class="p-2 rounded-xl border text-center shadow-2xs"
                             :class="discrepancyForm.difference_qty < 0 ? 'bg-rose-50 text-rose-800 border-rose-300' : (discrepancyForm.difference_qty > 0 ? 'bg-blue-50 text-blue-800 border-blue-300' : 'bg-white text-slate-600 border-slate-200')">
                            <span class="block text-[10px] font-bold mb-0.5"
                                  :class="discrepancyForm.difference_qty < 0 ? 'text-rose-600' : (discrepancyForm.difference_qty > 0 ? 'text-blue-600' : 'text-slate-500')">
                                Chênh lệch
                            </span>
                            <div class="font-mono font-black text-xs sm:text-sm">
                                <span x-text="(discrepancyForm.difference_qty > 0 ? '+' : '') + discrepancyForm.difference_qty"></span>
                                <span class="text-[9px] uppercase" x-text="discrepancyForm.unit || ''"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Tóm tắt dòng chữ thông báo tự động -->
                    <div class="text-[10.5px] font-bold text-center py-1 px-2 rounded-lg"
                         :class="discrepancyForm.difference_qty < 0 ? 'bg-rose-100/70 text-rose-800' : (discrepancyForm.difference_qty > 0 ? 'bg-blue-100/70 text-blue-800' : 'bg-slate-200/60 text-slate-600')">
                        <template x-if="discrepancyForm.difference_qty < 0">
                            <span>Phát hiện <strong class="text-rose-900">THIẾU <span x-text="Math.abs(discrepancyForm.difference_qty)"></span> <span x-text="discrepancyForm.unit || 'ĐVT'"></span></strong> so với phiếu kho</span>
                        </template>
                        <template x-if="discrepancyForm.difference_qty > 0">
                            <span>Phát hiện <strong class="text-blue-900">THỪA <span x-text="discrepancyForm.difference_qty"></span> <span x-text="discrepancyForm.unit || 'ĐVT'"></span></strong> so với phiếu kho</span>
                        </template>
                        <template x-if="discrepancyForm.difference_qty === 0">
                            <span>Số lượng thực tế khớp với phiếu kiểm</span>
                        </template>
                    </div>
                </div>

                <!-- 5. Ghi Chú Phản Ánh Của Lái Xe -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">
                        Ghi chú giải trình của Lái Xe <span class="text-rose-500">*</span>
                    </label>
                    <textarea x-model="discrepancyForm.driver_note" rows="2"
                              placeholder="Mô tả vị trí phát hiện, nghi vấn (kho đếm nhầm pallet, mất nắp chai, v.v.)..."
                              class="w-full p-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 outline-none focus:border-amber-500 transition-all"></textarea>
                </div>

                <!-- 6. Chụp ảnh / Tải ảnh hiện trường -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700 text-xs">
                            Ảnh chứng thực trên xe (tùy chọn)
                        </label>
                        <span class="text-[10px] text-slate-400">Tối đa 4 ảnh</span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <label class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 font-bold text-xs flex items-center gap-1.5 cursor-pointer transition-colors shadow-2xs">
                            <i class="fa-solid fa-camera text-sm text-amber-700"></i>
                            <span>Chụp / Chọn ảnh</span>
                            <input type="file" accept="image/*" multiple @change="handleDiscrepancyImages($event)" class="hidden">
                        </label>

                        <template x-for="(img, idx) in discrepancyForm.base64_images" :key="idx">
                            <div class="relative w-10 h-10 rounded-xl border border-slate-200 overflow-hidden shadow-2xs group">
                                <img :src="img" class="w-full h-full object-cover">
                                <button type="button" @click="removeDiscrepancyImage(idx)"
                                        class="absolute top-0.5 right-0.5 w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[9px] cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- ============================================================= -->
            <!-- TAB 2: LỊCH SỬ PHẢN ÁNH & PHẢN HỒI TỪ NGƯỜI KIỂM HÀNG        -->
            <!-- ============================================================= -->
            <div x-show="discrepancyTab === 'history'" class="space-y-2.5">

                <template x-if="discrepancies.length === 0">
                    <div class="py-10 text-center text-slate-400 space-y-1.5">
                        <i class="fa-solid fa-scale-unbalanced text-3xl text-slate-300"></i>
                        <p class="font-bold text-xs">Chưa có phản ánh lệch hàng nào trên chuyến xe này.</p>
                        <p class="text-[11px] text-slate-400">Nếu phát hiện thiếu hoặc thừa hàng khi đang đi giao, bấm tab "Tạo phản ánh mới".</p>
                    </div>
                </template>

                <template x-for="item in discrepancies" :key="item.id">
                    <div class="bg-white rounded-2xl border p-3 sm:p-3.5 space-y-2 shadow-2xs hover:border-slate-300 transition-all"
                         :class="item.status === 'confirmed' ? 'border-emerald-300 bg-emerald-50/10' : (item.status === 'rejected' ? 'border-rose-300 bg-rose-50/10' : 'border-amber-200 bg-amber-50/10')">
                        
                        <!-- Top: Đơn hàng, Biển số, Trạng thái -->
                        <div class="flex items-center justify-between gap-1.5 border-b border-slate-100 pb-1.5 flex-wrap">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="font-mono font-black text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200 text-[11px]"
                                      x-text="item.order_code || 'Lệch chung'"></span>
                                <span class="text-slate-300">•</span>
                                <span class="font-mono text-slate-600 font-bold text-[11px]" x-text="item.vehicle_plate"></span>
                            </div>

                            <!-- Trạng thái badge -->
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider"
                                  :class="item.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : (item.status === 'rejected' ? 'bg-rose-100 text-rose-800 border-rose-300' : (item.status === 'resolved' ? 'bg-sky-100 text-sky-800 border-sky-300' : 'bg-amber-100 text-amber-900 border-amber-300 animate-pulse'))">
                                <span x-text="item.status === 'confirmed' ? 'Kho nhận lệch' : (item.status === 'rejected' ? 'Bác bỏ' : (item.status === 'resolved' ? 'Đã giải quyết' : 'Chờ QC'))"></span>
                            </span>
                        </div>

                        <!-- Mid: Thông tin sản phẩm & Số lượng lệch -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-black text-slate-900 text-xs sm:text-sm" x-text="item.product_name"></div>
                                <div class="text-[10.5px] text-slate-500 flex items-center gap-1.5 font-mono mt-0.5">
                                    <span x-text="'SKU: ' + (item.sku || '---')"></span>
                                    <span>•</span>
                                    <span x-text="'ĐVT: ' + (item.unit || 'ĐVT')"></span>
                                </div>
                            </div>

                            <!-- Chênh lệch badge -->
                            <div class="text-right shrink-0">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-black shadow-2xs"
                                      :class="item.discrepancy_type === 'shortage' ? 'bg-rose-100 text-rose-800 border border-rose-300' : (item.discrepancy_type === 'excess' ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-orange-100 text-orange-800 border border-orange-300')">
                                    <span x-text="item.discrepancy_type === 'shortage' ? 'THIẾU' : (item.discrepancy_type === 'excess' ? 'THỪA' : 'HƯ HỎNG')"></span>
                                    <span class="font-mono" x-text="(item.difference_qty > 0 ? '+' : '') + item.difference_qty"></span>
                                    <span class="text-[9px]" x-text="item.unit || ''"></span>
                                </span>
                                <div class="text-[9.5px] text-slate-400 mt-0.5 font-mono">
                                    <span x-text="'Phiếu: ' + item.expected_qty + ' | Xe: ' + item.actual_qty"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú của lái xe & ảnh -->
                        <div class="p-2 bg-slate-50 rounded-xl border border-slate-100 text-[10.5px] space-y-1">
                            <div class="flex items-center justify-between text-slate-500 text-[10px]">
                                <span>Lái xe: <strong class="text-slate-800" x-text="item.driver_name"></strong></span>
                                <span class="font-mono" x-text="formatDate(item.created_at)"></span>
                            </div>
                            <p class="text-slate-700 italic" x-text="item.driver_note || 'Không có ghi chú thêm.'"></p>

                            <!-- Ảnh đính kèm -->
                            <template x-if="item.images && item.images.length > 0">
                                <div class="flex items-center gap-1.5 pt-0.5 flex-wrap">
                                    <template x-for="(imgUrl, iIdx) in item.images" :key="iIdx">
                                        <a :href="imgUrl" target="_blank" class="w-8 h-8 rounded-lg border border-slate-200 overflow-hidden block hover:opacity-80 transition-opacity">
                                            <img :src="imgUrl" class="w-full h-full object-cover">
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- KHUNG PHẢN HỒI TỪ NGƯỜI KIỂM HÀNG (2-WAY COMMUNICATION) -->
                        <div class="rounded-xl p-2.5 border text-[11px] space-y-1"
                             :class="item.checker_feedback ? 'bg-gradient-to-r from-emerald-50 via-teal-50 to-white border-emerald-300 text-emerald-950' : 'bg-slate-50 border-dashed border-slate-300 text-slate-500'">
                            <div class="flex items-center justify-between font-bold">
                                <div class="flex items-center gap-1.5 text-[10.5px]">
                                    <i class="fa-solid" :class="item.checker_feedback ? 'fa-circle-check text-emerald-600' : 'fa-hourglass-half text-amber-500'"></i>
                                    <span>Ý kiến QC (<span x-text="item.checker_feedback_by || item.checker_name || 'Người kiểm kho'"></span>):</span>
                                </div>
                                <template x-if="item.checker_feedback_at">
                                    <span class="text-[9.5px] text-slate-400 font-mono font-normal" x-text="formatDate(item.checker_feedback_at)"></span>
                                </template>
                            </div>

                            <template x-if="item.checker_feedback">
                                <div>
                                    <p class="font-medium text-slate-800 bg-white/90 p-2 rounded-lg border border-emerald-200 text-xs" x-text="item.checker_feedback"></p>
                                    <template x-if="item.resolution_note">
                                        <div class="mt-1 text-[10px] text-emerald-800 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-lightbulb text-amber-500"></i>
                                            <span>Xử lý: <span class="font-semibold" x-text="item.resolution_note"></span></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!item.checker_feedback">
                                <div class="italic text-slate-400 text-center py-0.5 text-[10px]">
                                    QC đang kiểm tra và sẽ gửi phản hồi xuống xe...
                                </div>
                            </template>
                        </div>

                    </div>
                </template>

            </div>

        </div>

        <!-- 4. Footer Modal CỐ ĐỊNH (Sticky Footer - Nút Gửi Luôn Hiển Thị Trên Mobile) -->
        <div class="px-3.5 py-2.5 sm:px-5 sm:py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-2 text-xs shrink-0">
            <button type="button" @click="showDiscrepancyModal = false"
                    :disabled="discrepancyForm.isSubmitting"
                    class="px-3.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl transition-colors cursor-pointer shrink-0">
                Đóng
            </button>

            <!-- Nút Hành Động Chính Luôn Ghim Ở Dưới Màn Hình Mobile -->
            <template x-if="discrepancyTab === 'create'">
                <button type="button" @click="submitDiscrepancyReport()" :disabled="discrepancyForm.isSubmitting"
                        class="flex-1 sm:flex-initial px-4 py-2 bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 hover:from-amber-700 hover:to-orange-800 text-white font-black rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-md shadow-amber-600/20 cursor-pointer disabled:opacity-50">
                    <template x-if="discrepancyForm.isSubmitting">
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    </template>
                    <template x-if="!discrepancyForm.isSubmitting">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </template>
                    <span x-text="discrepancyForm.isSubmitting ? 'Đang gửi...' : 'Gửi Phản Ánh Cho QC'"></span>
                </button>
            </template>

            <template x-if="discrepancyTab === 'history'">
                <button type="button" @click="refreshDiscrepancies()"
                        class="flex-1 sm:flex-initial px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold border border-slate-300 rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                    <span>Cập nhật</span>
                </button>
            </template>
        </div>

    </div>
</div>
