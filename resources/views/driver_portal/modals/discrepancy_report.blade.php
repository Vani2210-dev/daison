<!-- ========================================================================= -->
<!-- MODAL: PHẢN ÁNH THỪA / THIẾU HÀNG SAU KHI XUẤT BẾN (LÁI XE & KIỂM HÀNG) -->
<!-- ========================================================================= -->
<div x-show="showDiscrepancyModal" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4 bg-slate-900/80 backdrop-blur-sm overflow-y-auto"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="showDiscrepancyModal = false"
         class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden my-auto flex flex-col max-h-[92vh]">

        <!-- Header Modal -->
        <div class="px-5 py-4 bg-gradient-to-r from-amber-600 via-amber-700 to-orange-700 text-white flex items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center text-lg shadow-inner shrink-0">
                    <i class="fa-solid fa-scale-unbalanced text-white"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-black text-sm sm:text-base leading-tight truncate">
                        Phản Ánh Lệch Hàng (Thừa / Thiếu)
                    </h3>
                    <p class="text-[11px] text-amber-100/90 truncate mt-0.5">
                        Kênh trao đổi trực tiếp giữa Người giao hàng & Người kiểm hàng sau khi xe xuất bến
                    </p>
                </div>
            </div>

            <button type="button" @click="showDiscrepancyModal = false"
                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer shrink-0">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="px-5 pt-3 pb-2 border-b border-slate-100 flex items-center gap-2 bg-slate-50 shrink-0">
            <button type="button" @click="discrepancyTab = 'create'"
                    class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 cursor-pointer"
                    :class="discrepancyTab === 'create' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                <i class="fa-solid fa-circle-plus text-xs"></i>
                <span>Tạo phản ánh mới</span>
            </button>

            <button type="button" @click="discrepancyTab = 'history'"
                    class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 cursor-pointer"
                    :class="discrepancyTab === 'history' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                <i class="fa-solid fa-comments text-xs"></i>
                <span>Lịch sử & Phản hồi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                      :class="discrepancyTab === 'history' ? 'bg-black/20 text-white' : 'bg-amber-100 text-amber-800'"
                      x-text="discrepancies.length"></span>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-5 overflow-y-auto space-y-4 text-xs">

            <!-- ============================================================= -->
            <!-- TAB 1: TẠO PHẢN ÁNH MỚI                                      -->
            <!-- ============================================================= -->
            <div x-show="discrepancyTab === 'create'" class="space-y-4">

                <!-- 1. Chọn Đơn Hàng -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Đơn hàng bị lệch <span class="text-rose-500">*</span></span>
                        <span class="text-[11px] text-slate-400 font-normal">Xe: <strong class="text-purple-700 font-mono" x-text="selectedVehicle?.plate_number"></strong></span>
                    </label>
                    <select x-model="discrepancyForm.order_id" @change="onDiscrepancyOrderChange($event.target.value)"
                            class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-amber-500 transition-all cursor-pointer">
                        <option value="">-- Chọn đơn hàng trên xe --</option>
                        <template x-for="ord in orders" :key="ord.id">
                            <option :value="ord.id" x-text="ord.order_code + ' - ' + ord.customer_name + ' (' + (ord.items ? ord.items.length : 0) + ' SP)'"></option>
                        </template>
                    </select>
                </div>

                <!-- Thẻ tóm tắt thông tin Người Kiểm Hàng của đơn hàng -->
                <div x-show="discrepancySelectedOrder" class="p-3 bg-amber-50/80 border border-amber-200 rounded-2xl flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fa-solid fa-clipboard-user"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] uppercase font-bold text-amber-800 tracking-wider">Người kiểm hàng phụ trách:</div>
                            <div class="font-black text-slate-900 truncate text-xs" x-text="discrepancyCheckerName || 'Bộ phận QC / Kho xuất'"></div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 whitespace-nowrap">
                        QC Kho xuất
                    </span>
                </div>

                <!-- 2. Loại Lệch (4 Cards: Thiếu, Thừa, Hư hỏng, Sai mã) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Tính chất lệch hàng <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <!-- Thiếu hàng -->
                        <button type="button" @click="discrepancyForm.discrepancy_type = 'shortage'; calcDiscrepancyDiff()"
                                class="p-2.5 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-1"
                                :class="discrepancyForm.discrepancy_type === 'shortage' ? 'bg-rose-50 border-rose-500 text-rose-800 ring-2 ring-rose-500/20 font-black' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold'">
                            <i class="fa-solid fa-circle-minus text-rose-600 text-base"></i>
                            <span class="text-xs">Thiếu hàng</span>
                        </button>

                        <!-- Thừa hàng -->
                        <button type="button" @click="discrepancyForm.discrepancy_type = 'excess'; calcDiscrepancyDiff()"
                                class="p-2.5 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-1"
                                :class="discrepancyForm.discrepancy_type === 'excess' ? 'bg-blue-50 border-blue-500 text-blue-800 ring-2 ring-blue-500/20 font-black' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold'">
                            <i class="fa-solid fa-circle-plus text-blue-600 text-base"></i>
                            <span class="text-xs">Thừa hàng</span>
                        </button>

                        <!-- Hàng hư hỏng / Bể vỡ -->
                        <button type="button" @click="discrepancyForm.discrepancy_type = 'damaged'; calcDiscrepancyDiff()"
                                class="p-2.5 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-1"
                                :class="discrepancyForm.discrepancy_type === 'damaged' ? 'bg-orange-50 border-orange-500 text-orange-800 ring-2 ring-orange-500/20 font-black' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold'">
                            <i class="fa-solid fa-box-tissue text-orange-600 text-base"></i>
                            <span class="text-xs">Bể vỡ/Móp</span>
                        </button>

                        <!-- Sai quy cách / Sai mặt hàng -->
                        <button type="button" @click="discrepancyForm.discrepancy_type = 'wrong_item'; calcDiscrepancyDiff()"
                                class="p-2.5 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-1"
                                :class="discrepancyForm.discrepancy_type === 'wrong_item' ? 'bg-purple-50 border-purple-500 text-purple-800 ring-2 ring-purple-500/20 font-black' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold'">
                            <i class="fa-solid fa-shuffle text-purple-600 text-base"></i>
                            <span class="text-xs">Sai quy cách</span>
                        </button>
                    </div>
                </div>

                <!-- 3. Chọn Mặt Hàng Bị Lệch -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Sản phẩm bị lệch <span class="text-rose-500">*</span></span>
                        <template x-if="discrepancyOrderItems.length > 0">
                            <span class="text-[11px] text-slate-400 font-normal" x-text="discrepancyOrderItems.length + ' mặt hàng trong đơn'"></span>
                        </template>
                    </label>

                    <template x-if="discrepancyOrderItems.length > 0">
                        <select @change="onDiscrepancyItemSelect($event.target.value)"
                                class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-amber-500 transition-all cursor-pointer">
                            <option value="">-- Chọn mặt hàng từ danh sách đơn --</option>
                            <template x-for="item in discrepancyOrderItems" :key="item.id || item.sku">
                                <option :value="item.sku || item.product_id" x-text="item.name + ' (' + item.quantity + ' ' + (item.unit || 'ĐVT') + ')'"></option>
                            </template>
                        </select>
                    </template>

                    <div class="mt-2 space-y-2">
                        <input type="text" x-model="discrepancyForm.product_name"
                               placeholder="Tên sản phẩm thực tế bị lệch..."
                               class="w-full px-3.5 py-2 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-amber-500 transition-all">

                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" x-model="discrepancyForm.sku"
                                   placeholder="Mã SKU (nếu có)..."
                                   class="px-3.5 py-2 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono text-xs text-slate-700 outline-none focus:border-amber-500 transition-all">
                            <input type="text" x-model="discrepancyForm.unit"
                                   placeholder="Đơn vị tính (Thùng, Chai, Hộp...)..."
                                   class="px-3.5 py-2 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-xs text-slate-700 outline-none focus:border-amber-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- 4. Số lượng theo phiếu vs Thực tế trên xe -->
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2.5">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <span class="block text-[11px] font-bold text-slate-500 mb-1">Theo phiếu kiểm</span>
                            <input type="number" step="any" min="0" x-model.number="discrepancyForm.expected_qty" @input="calcDiscrepancyDiff()"
                                   class="w-full py-2 px-2 text-center font-mono font-black text-sm text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-amber-500 outline-none">
                        </div>

                        <div>
                            <span class="block text-[11px] font-bold text-slate-500 mb-1">Thực tế trên xe</span>
                            <input type="number" step="any" min="0" x-model.number="discrepancyForm.actual_qty" @input="calcDiscrepancyDiff()"
                                   class="w-full py-2 px-2 text-center font-mono font-black text-sm text-amber-700 bg-white border-2 border-amber-400 rounded-xl focus:border-amber-600 outline-none shadow-xs">
                        </div>

                        <div>
                            <span class="block text-[11px] font-bold text-slate-500 mb-1">Chênh lệch</span>
                            <div class="py-2 px-2 rounded-xl border font-mono font-black text-sm flex items-center justify-center gap-1 shadow-2xs"
                                 :class="discrepancyForm.difference_qty < 0 ? 'bg-rose-100 text-rose-800 border-rose-300' : (discrepancyForm.difference_qty > 0 ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-slate-200 text-slate-700 border-slate-300')">
                                <span x-text="(discrepancyForm.difference_qty > 0 ? '+' : '') + discrepancyForm.difference_qty"></span>
                                <span class="text-[10px]" x-text="discrepancyForm.unit || 'ĐVT'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Lời giải thích tự động về độ lệch -->
                    <div class="text-[11px] font-semibold text-center py-1 px-3 rounded-lg"
                         :class="discrepancyForm.difference_qty < 0 ? 'bg-rose-50 text-rose-700' : (discrepancyForm.difference_qty > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600')">
                        <template x-if="discrepancyForm.difference_qty < 0">
                            <span>Phát hiện <strong class="text-rose-800 font-bold">THIẾU <span x-text="Math.abs(discrepancyForm.difference_qty)"></span> <span x-text="discrepancyForm.unit || 'ĐVT'"></span></strong> so với phiếu kiểm hàng của kho</span>
                        </template>
                        <template x-if="discrepancyForm.difference_qty > 0">
                            <span>Phát hiện <strong class="text-blue-800 font-bold">THỪA <span x-text="discrepancyForm.difference_qty"></span> <span x-text="discrepancyForm.unit || 'ĐVT'"></span></strong> so với phiếu kiểm hàng của kho</span>
                        </template>
                        <template x-if="discrepancyForm.difference_qty === 0">
                            <span>Số lượng thực tế khớp với số lượng kiểm</span>
                        </template>
                    </div>
                </div>

                <!-- 5. Ghi Chú Phản Ánh Của Lái Xe -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Ghi chú phản ánh của Người Giao Hàng <span class="text-rose-500">*</span>
                    </label>
                    <textarea x-model="discrepancyForm.driver_note" rows="3"
                              placeholder="Mô tả chi tiết vị trí hàng, tình trạng phát hiện, nguyên nhân nghi vấn (ví dụ: kho đếm nhầm pallet, thùng bị rách mất chai bên trong, v.v.)..."
                              class="w-full p-3 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none focus:border-amber-500 transition-all"></textarea>
                </div>

                <!-- 6. Chụp ảnh / Tải ảnh hiện trường (Bằng chứng) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Hình ảnh chứng thực trên xe (tùy chọn)</span>
                        <span class="text-[10px] text-slate-400">Tối đa 4 ảnh</span>
                    </label>

                    <div class="flex items-center gap-3 flex-wrap">
                        <label class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 font-bold text-xs flex items-center gap-2 cursor-pointer transition-colors shadow-2xs">
                            <i class="fa-solid fa-camera text-sm text-amber-700"></i>
                            <span>Chụp ảnh / Chọn ảnh</span>
                            <input type="file" accept="image/*" multiple @change="handleDiscrepancyImages($event)" class="hidden">
                        </label>

                        <template x-for="(img, idx) in discrepancyForm.base64_images" :key="idx">
                            <div class="relative w-12 h-12 rounded-xl border border-slate-200 overflow-hidden shadow-2xs group">
                                <img :src="img" class="w-full h-full object-cover">
                                <button type="button" @click="removeDiscrepancyImage(idx)"
                                        class="absolute top-0.5 right-0.5 w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[9px] cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Action Button: Gửi phản ánh -->
                <div class="pt-2">
                    <button type="button" @click="submitDiscrepancyReport()" :disabled="discrepancyForm.isSubmitting"
                            class="w-full py-3 rounded-2xl font-black text-xs sm:text-sm text-white bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 hover:from-amber-700 hover:to-orange-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-amber-600/25 cursor-pointer disabled:opacity-50">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span x-text="discrepancyForm.isSubmitting ? 'Đang gửi phản ánh...' : 'Gửi Phản Ánh Tới Người Kiểm Hàng'"></span>
                    </button>
                </div>

            </div>

            <!-- ============================================================= -->
            <!-- TAB 2: LỊCH SỬ PHẢN ÁNH & PHẢN HỒI TỪ NGƯỜI KIỂM HÀNG        -->
            <!-- ============================================================= -->
            <div x-show="discrepancyTab === 'history'" class="space-y-3">

                <template x-if="discrepancies.length === 0">
                    <div class="py-12 text-center text-slate-400 space-y-2">
                        <i class="fa-solid fa-scale-unbalanced text-3xl text-slate-300"></i>
                        <p class="font-bold text-xs">Chưa có phản ánh lệch hàng nào trên chuyến xe này.</p>
                        <p class="text-[11px] text-slate-400">Nếu phát hiện thiếu hoặc thừa hàng khi đang đi giao, bấm tab "Tạo phản ánh mới".</p>
                    </div>
                </template>

                <template x-for="item in discrepancies" :key="item.id">
                    <div class="bg-white rounded-2xl border p-4 space-y-3 shadow-2xs hover:border-slate-300 transition-all"
                         :class="item.status === 'confirmed' ? 'border-emerald-300 bg-emerald-50/10' : (item.status === 'rejected' ? 'border-rose-300 bg-rose-50/10' : 'border-amber-200 bg-amber-50/10')">
                        
                        <!-- Top: Đơn hàng, Biển số, Trạng thái -->
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-mono font-black text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200 text-xs"
                                      x-text="item.order_code || 'Lệch chung trên xe'"></span>
                                <span class="text-slate-400">•</span>
                                <span class="font-mono text-slate-600 font-bold" x-text="item.vehicle_plate"></span>
                            </div>

                            <!-- Trạng thái badge -->
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider"
                                  :class="item.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : (item.status === 'rejected' ? 'bg-rose-100 text-rose-800 border-rose-300' : (item.status === 'resolved' ? 'bg-sky-100 text-sky-800 border-sky-300' : 'bg-amber-100 text-amber-900 border-amber-300 animate-pulse'))">
                                <span x-text="item.status === 'confirmed' ? 'Kiểm hàng đã xác nhận' : (item.status === 'rejected' ? 'Bác bỏ' : (item.status === 'resolved' ? 'Đã giải quyết' : 'Chờ kiểm hàng phản hồi'))"></span>
                            </span>
                        </div>

                        <!-- Mid: Thông tin sản phẩm & Số lượng lệch -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <div class="font-black text-slate-900 text-xs sm:text-sm" x-text="item.product_name"></div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-2 font-mono">
                                    <span x-text="'SKU: ' + (item.sku || '---')"></span>
                                    <span>•</span>
                                    <span x-text="'ĐVT: ' + (item.unit || 'ĐVT')"></span>
                                </div>
                            </div>

                            <!-- Chênh lệch badge -->
                            <div class="text-right shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black shadow-2xs"
                                      :class="item.discrepancy_type === 'shortage' ? 'bg-rose-100 text-rose-800 border border-rose-300' : (item.discrepancy_type === 'excess' ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-orange-100 text-orange-800 border border-orange-300')">
                                    <span x-text="item.discrepancy_type === 'shortage' ? 'THIẾU' : (item.discrepancy_type === 'excess' ? 'THỪA' : 'HƯ HỎNG')"></span>
                                    <span class="font-mono" x-text="(item.difference_qty > 0 ? '+' : '') + item.difference_qty"></span>
                                    <span class="text-[10px]" x-text="item.unit || ''"></span>
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                    <span x-text="'Phiếu: ' + item.expected_qty + ' | Xe: ' + item.actual_qty"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú của lái xe & ảnh -->
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-[11px] space-y-1.5">
                            <div class="flex items-center justify-between text-slate-500 text-[10px]">
                                <span>Lái xe phản ánh: <strong class="text-slate-800" x-text="item.driver_name"></strong></span>
                                <span class="font-mono" x-text="formatDate(item.created_at)"></span>
                            </div>
                            <p class="text-slate-700 italic" x-text="item.driver_note || 'Không có ghi chú thêm.'"></p>

                            <!-- Ảnh đính kèm -->
                            <template x-if="item.images && item.images.length > 0">
                                <div class="flex items-center gap-2 pt-1 flex-wrap">
                                    <template x-for="(imgUrl, iIdx) in item.images" :key="iIdx">
                                        <a :href="imgUrl" target="_blank" class="w-10 h-10 rounded-lg border border-slate-200 overflow-hidden block hover:opacity-80 transition-opacity">
                                            <img :src="imgUrl" class="w-full h-full object-cover">
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- ========================================================= -->
                        <!-- KHUNG PHẢN HỒI TỪ NGƯỜI KIỂM HÀNG (2-WAY COMMUNICATION)  -->
                        <!-- ========================================================= -->
                        <div class="rounded-xl p-3 border text-[11px] space-y-1.5"
                             :class="item.checker_feedback ? 'bg-gradient-to-r from-emerald-50 via-teal-50 to-white border-emerald-300 text-emerald-950' : 'bg-slate-50 border-dashed border-slate-300 text-slate-500'">
                            <div class="flex items-center justify-between font-bold">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid" :class="item.checker_feedback ? 'fa-circle-check text-emerald-600' : 'fa-hourglass-half text-amber-500'"></i>
                                    <span>Ý kiến Người Kiểm Hàng (<span x-text="item.checker_feedback_by || item.checker_name || 'Người kiểm kho'"></span>):</span>
                                </div>
                                <template x-if="item.checker_feedback_at">
                                    <span class="text-[10px] text-slate-400 font-mono font-normal" x-text="formatDate(item.checker_feedback_at)"></span>
                                </template>
                            </div>

                            <template x-if="item.checker_feedback">
                                <div>
                                    <p class="font-medium text-slate-800 bg-white/80 p-2 rounded-lg border border-emerald-200" x-text="item.checker_feedback"></p>
                                    <template x-if="item.resolution_note">
                                        <div class="mt-1 text-[10px] text-emerald-800 font-semibold flex items-center gap-1">
                                            <i class="fa-solid fa-lightbulb text-amber-500"></i>
                                            <span>Phương án xử lý: <strong x-text="item.resolution_note"></strong></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!item.checker_feedback">
                                <div class="italic text-slate-400 text-center py-1">
                                    Người kiểm hàng đang rà soát dữ liệu xuất kho và sẽ phản hồi qua hệ thống...
                                </div>
                            </template>
                        </div>

                    </div>
                </template>

            </div>

        </div>

        <!-- Footer Modal -->
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs shrink-0">
            <span class="text-[11px] text-slate-400">
                Lái xe & Người kiểm hàng nhận thông báo tức thời
            </span>

            <button type="button" @click="showDiscrepancyModal = false"
                    class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl transition-colors cursor-pointer">
                Đóng
            </button>
        </div>

    </div>
</div>
