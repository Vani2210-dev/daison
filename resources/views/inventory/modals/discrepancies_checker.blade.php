<!-- ======================================================== -->
<!-- MODAL: PHẢN ÁNH LỆCH HÀNG TỪ XE XUẤT BẾN (QC & LÁI XE)   -->
<!-- ======================================================== -->
<div x-show="showDiscrepanciesModal" 
     x-cloak
     class="fixed inset-0 z-[99999] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 md:p-6 overflow-y-auto"
     @keydown.escape.window="if(!isSubmittingResponse) showDiscrepanciesModal = false">
    
    <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[94vh] animate-in fade-in zoom-in-95 duration-150"
         @click.outside="if(!isSubmittingResponse) showDiscrepanciesModal = false">
        
        <!-- 1. Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-rose-700 via-amber-700 to-indigo-800 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 shadow-inner">
                    <i class="fa-solid fa-truck-ramp-box text-lg text-white"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base font-black tracking-tight">Lệch Hàng Từ Xe Xuất Bến</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white border border-white/30">Lái xe ⇄ Người bốc hàng</span>
                        <template x-if="pendingDiscrepanciesCount > 0">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse"
                                  x-text="pendingDiscrepanciesCount + ' cần phản hồi'"></span>
                        </template>
                    </div>
                    <p class="text-xs text-rose-100 mt-0.5">Tiếp nhận phản ánh thiếu/thừa hàng từ lái xe khi xe đã lăn bánh và phản hồi hướng xử lý trực tiếp xuống màn hình lái xe.</p>
                </div>
            </div>
            <button type="button" 
                    @click="showDiscrepanciesModal = false"
                    :disabled="isSubmittingResponse"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors cursor-pointer text-base">
                &times;
            </button>
        </div>

        <!-- 2. Toolbar (Bộ lọc & Tìm kiếm) -->
        <div class="p-3 sm:px-5 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs shrink-0">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-thin">
                <button type="button" @click="discrepancyFilterStatus = 'all'"
                        class="px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap"
                        :class="discrepancyFilterStatus === 'all' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                    <span>Tất cả</span>
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]"
                          :class="discrepancyFilterStatus === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                          x-text="discrepanciesList.length"></span>
                </button>

                <button type="button" @click="discrepancyFilterStatus = 'pending'"
                        class="px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1"
                        :class="discrepancyFilterStatus === 'pending' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200'">
                    <i class="fa-solid fa-clock"></i>
                    <span>Chờ người bốc phản hồi</span>
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]"
                          :class="discrepancyFilterStatus === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'"
                          x-text="discrepanciesList.filter(d => d.status === 'pending').length"></span>
                </button>

                <button type="button" @click="discrepancyFilterStatus = 'confirmed'"
                        class="px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1"
                        :class="discrepancyFilterStatus === 'confirmed' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200'">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Kho nhận lệch</span>
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]"
                          :class="discrepancyFilterStatus === 'confirmed' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800'"
                          x-text="discrepanciesList.filter(d => d.status === 'confirmed').length"></span>
                </button>

                <button type="button" @click="discrepancyFilterStatus = 'rejected'"
                        class="px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1"
                        :class="discrepancyFilterStatus === 'rejected' ? 'bg-slate-700 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'">
                    <i class="fa-solid fa-ban"></i>
                    <span>Bác bỏ</span>
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]"
                          :class="discrepancyFilterStatus === 'rejected' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'"
                          x-text="discrepanciesList.filter(d => d.status === 'rejected').length"></span>
                </button>

                <button type="button" @click="discrepancyFilterStatus = 'resolved'"
                        class="px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1"
                        :class="discrepancyFilterStatus === 'resolved' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200'">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Đã giải quyết</span>
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]"
                          :class="discrepancyFilterStatus === 'resolved' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'"
                          x-text="discrepanciesList.filter(d => d.status === 'resolved').length"></span>
                </button>
            </div>

            <!-- Search & Refresh -->
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-60">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" x-model="discrepancySearch" placeholder="Tìm biển số, tài xế, mã đơn, SP..."
                           class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-rose-500 shadow-2xs">
                    <button type="button" x-show="discrepancySearch" @click="discrepancySearch = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>

                <button type="button" @click="fetchDiscrepancies()" :disabled="isPollingDiscrepancies"
                        title="Tải lại danh sách"
                        class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl font-bold transition-all flex items-center gap-1.5 shrink-0 shadow-2xs cursor-pointer disabled:opacity-50">
                    <i class="fa-solid fa-rotate-right" :class="isPollingDiscrepancies ? 'animate-spin text-rose-600' : ''"></i>
                    <span class="hidden sm:inline">Làm mới</span>
                </button>
            </div>
        </div>

        <!-- 3. Body: Danh sách phản ánh -->
        <div class="p-3 sm:p-5 space-y-4 overflow-y-auto flex-1 text-xs bg-slate-100/60">
            
            <template x-if="filteredDiscrepancies.length === 0">
                <div class="py-16 text-center bg-white rounded-2xl border border-dashed border-slate-200 shadow-2xs">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-2xl mb-3">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="font-bold text-slate-700 text-sm">Không có phản ánh nào</div>
                    <p class="text-slate-400 text-xs mt-1">Không tìm thấy phản ánh lệch hàng phù hợp với bộ lọc hiện tại.</p>
                </div>
            </template>

            <template x-for="d in filteredDiscrepancies" :key="d.id">
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden transition-all hover:border-slate-300">
                    
                    <!-- Card Top: Thông tin chuyến xe & Trạng thái -->
                    <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2.5">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-mono font-black text-xs flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-truck text-[10px] text-amber-400"></i>
                                <span x-text="d.vehicle_plate || 'Chưa rõ xe'"></span>
                            </span>

                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-id-badge text-slate-400"></i>
                                <span>Lái xe:</span>
                                <strong class="text-slate-900" x-text="d.driver_name || 'Tài xế'"></strong>
                            </span>

                            <template x-if="d.order_code">
                                <span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 font-mono font-bold border border-sky-200 flex items-center gap-1 cursor-pointer hover:bg-sky-100"
                                      @click="focusOrderInPicking(d.order_code)"
                                      :title="'Nhấp để tìm đơn ' + d.order_code + ' trong danh sách bốc'">
                                    <i class="fa-solid fa-file-invoice text-[10px]"></i>
                                    <span x-text="d.order_code"></span>
                                </span>
                            </template>

                            <span class="text-slate-400 text-[11px] flex items-center gap-1">
                                <i class="fa-regular fa-clock"></i>
                                <span x-text="formatDiscrepancyTime(d.created_at)"></span>
                            </span>
                        </div>

                        <!-- Trạng thái Badge -->
                        <div class="flex items-center gap-2">
                            <template x-if="d.status === 'pending'">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1 animate-pulse">
                                    <i class="fa-solid fa-hourglass-half"></i>
                                    <span>Chờ QC phản hồi</span>
                                </span>
                            </template>
                            <template x-if="d.status === 'confirmed'">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Kho nhận lệch</span>
                                </span>
                            </template>
                            <template x-if="d.status === 'rejected'">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-slate-200 text-slate-700 border border-slate-300 flex items-center gap-1">
                                    <i class="fa-solid fa-ban"></i>
                                    <span>Bác bỏ</span>
                                </span>
                            </template>
                            <template x-if="d.status === 'resolved'">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Đã giải quyết</span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Card Middle: Chi tiết sản phẩm & Lệch số lượng -->
                    <div class="p-4 space-y-3">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/60 p-3 rounded-xl border border-slate-200/70">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                                          :class="{
                                              'bg-rose-100 text-rose-800 border border-rose-300': d.discrepancy_type === 'shortage',
                                              'bg-amber-100 text-amber-800 border border-amber-300': d.discrepancy_type === 'excess',
                                              'bg-purple-100 text-purple-800 border border-purple-300': d.discrepancy_type === 'damaged',
                                              'bg-sky-100 text-sky-800 border border-sky-300': d.discrepancy_type === 'wrong_item'
                                          }"
                                          x-text="getDiscrepancyTypeLabel(d.discrepancy_type)"></span>
                                    
                                    <template x-if="d.sku">
                                        <span class="font-mono font-bold text-slate-500" x-text="'[' + d.sku + ']'"></span>
                                    </template>
                                    
                                    <h4 class="font-black text-slate-900 text-sm" x-text="d.product_name"></h4>
                                </div>

                                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                                    <span>ĐVT: <strong class="text-slate-700 uppercase" x-text="d.unit || 'ĐVT'"></strong></span>
                                    <span>•</span>
                                    <span>Người bốc phụ trách: <strong class="text-indigo-700" x-text="d.checker_name || 'Người bốc hàng'"></strong></span>
                                </div>
                            </div>

                            <!-- Thống kê số lượng -->
                            <div class="flex items-center gap-3 shrink-0 bg-white p-2.5 rounded-xl border border-slate-200">
                                <div class="text-center px-2">
                                    <span class="text-[10px] text-slate-400 block font-medium">Theo phiếu</span>
                                    <span class="font-mono font-bold text-slate-700 text-sm" x-text="d.expected_qty"></span>
                                </div>
                                <div class="h-6 w-px bg-slate-200"></div>
                                <div class="text-center px-2">
                                    <span class="text-[10px] text-slate-400 block font-medium">Thực trên xe</span>
                                    <span class="font-mono font-bold text-slate-900 text-sm" x-text="d.actual_qty"></span>
                                </div>
                                <div class="h-6 w-px bg-slate-200"></div>
                                <div class="text-center px-2">
                                    <span class="text-[10px] block font-medium"
                                          :class="Number(d.difference_qty) < 0 ? 'text-rose-500' : 'text-amber-600'">Độ lệch</span>
                                    <span class="font-mono font-black text-sm"
                                          :class="Number(d.difference_qty) < 0 ? 'text-rose-600' : 'text-amber-600'"
                                          x-text="(Number(d.difference_qty) > 0 ? '+' : '') + d.difference_qty + ' ' + (d.unit || '')"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú từ lái xe -->
                        <template x-if="d.driver_note">
                            <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200/80 text-amber-950 flex items-start gap-2.5">
                                <i class="fa-solid fa-comment-dots text-amber-600 mt-0.5 shrink-0 text-sm"></i>
                                <div class="flex-1">
                                    <span class="font-bold text-[11px] text-amber-900">Lái xe giải trình:</span>
                                    <p class="text-xs mt-0.5 leading-relaxed font-medium" x-text="d.driver_note"></p>
                                </div>
                            </div>
                        </template>

                        <!-- Hình ảnh đính kèm từ lái xe -->
                        <template x-if="d.images && d.images.length > 0">
                            <div>
                                <span class="font-bold text-[11px] text-slate-600 block mb-1.5">
                                    <i class="fa-solid fa-camera text-slate-400 mr-1"></i>
                                    Ảnh chụp hiện trường từ lái xe (<span x-text="d.images.length"></span> ảnh):
                                </span>
                                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                                    <template x-for="(img, imgIdx) in d.images" :key="imgIdx">
                                        <div class="relative w-16 h-16 rounded-xl border border-slate-200 overflow-hidden cursor-pointer hover:opacity-85 shadow-2xs group shrink-0"
                                             @click="openImageLightbox(img)">
                                            <img :src="img" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs">
                                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Khối Phản Hồi Của Người Kiểm Hàng / QC -->
                        <div class="mt-3 pt-3 border-t border-slate-200">
                            <!-- Hiển thị phản hồi đã có -->
                            <template x-if="d.checker_feedback">
                                <div class="p-3.5 bg-indigo-50/80 rounded-xl border border-indigo-200/90 text-indigo-950 mb-3 space-y-1.5">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-box-open text-indigo-600"></i>
                                            <span class="font-black text-indigo-900 text-xs">Phản hồi từ Người bốc:</span>
                                            <strong class="text-indigo-800" x-text="d.checker_feedback_by || 'Người bốc hàng'"></strong>
                                        </div>
                                        <span class="text-[11px] text-indigo-500 font-medium" x-text="d.checker_feedback_at ? formatDiscrepancyTime(d.checker_feedback_at) : ''"></span>
                                    </div>
                                    <p class="text-xs leading-relaxed font-medium bg-white/70 p-2.5 rounded-lg border border-indigo-100" x-text="d.checker_feedback"></p>
                                    
                                    <template x-if="d.resolution_note">
                                        <div class="text-[11px] text-indigo-800 flex items-center gap-1.5 pt-0.5">
                                            <i class="fa-solid fa-hand-holding-hand text-indigo-500"></i>
                                            <span>Hướng xử lý: <strong class="text-indigo-900" x-text="d.resolution_note"></strong></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Nút hoặc Form gửi phản hồi cho Người bốc hàng -->
                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-3"
                                 x-data="{ 
                                     showForm: d.status === 'pending' || activeRespondingId === d.id,
                                     status: d.status === 'pending' ? 'confirmed' : d.status,
                                     feedback: d.checker_feedback || '',
                                     resolution: d.resolution_note || '',
                                     submitting: false
                                 }">
                                
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                        <i class="fa-solid fa-reply text-indigo-600"></i>
                                        <span x-text="d.checker_feedback ? 'Cập nhật phản hồi cho lái xe:' : 'Gửi ý kiến phản hồi cho lái xe:'"></span>
                                    </span>
                                    <button type="button" @click="showForm = !showForm"
                                            class="text-indigo-600 hover:text-indigo-800 font-bold text-[11px] cursor-pointer">
                                        <span x-text="showForm ? 'Thu gọn ▲' : 'Mở form phản hồi ▼'"></span>
                                    </button>
                                </div>

                                <div x-show="showForm" class="space-y-2.5 pt-1">
                                    <!-- Chọn hành động kết luận -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Kết luận của Người bốc hàng:</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer transition-all text-xs"
                                                   :class="status === 'confirmed' ? 'bg-rose-50 border-rose-300 text-rose-900 font-bold' : 'bg-white border-slate-200 text-slate-700'">
                                                <input type="radio" value="confirmed" x-model="status" class="text-rose-600">
                                                <span>Xác nhận bốc thiếu/thừa</span>
                                            </label>

                                            <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer transition-all text-xs"
                                                   :class="status === 'rejected' ? 'bg-slate-100 border-slate-400 text-slate-900 font-bold' : 'bg-white border-slate-200 text-slate-700'">
                                                <input type="radio" value="rejected" x-model="status" class="text-slate-700">
                                                <span>Bác bỏ phản ánh</span>
                                            </label>

                                            <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer transition-all text-xs"
                                                   :class="status === 'resolved' ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-bold' : 'bg-white border-slate-200 text-slate-700'">
                                                <input type="radio" value="resolved" x-model="status" class="text-emerald-600">
                                                <span>Đã giải quyết xong</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Nội dung giải trình phản hồi -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nội dung giải thích / Chỉ dẫn cho tài xế:</label>
                                        <textarea x-model="feedback" rows="2" 
                                                  placeholder="Ví dụ: Người bốc đã kiểm tra lại pallet ở kho, xác nhận bốc thiếu 2 thùng. Lái xe giao đủ số lượng thực tế trên xe, kế toán sẽ trừ công nợ khách..."
                                                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500"></textarea>
                                    </div>

                                    <!-- Hướng xử lý -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Phương án xử lý (nếu có):</label>
                                        <input type="text" x-model="resolution"
                                               placeholder="Ví dụ: Kế toán sửa hóa đơn / Giao bù chuyến mai / Bù tiền mặt..."
                                               class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500">
                                    </div>

                                    <!-- Nút gửi -->
                                    <div class="flex justify-end pt-1">
                                        <button type="button" 
                                                @click="sendCheckerResponse(d.id, status, feedback, resolution, () => { submitting = true }, () => { submitting = false })"
                                                :disabled="submitting || isSubmittingResponse || !feedback.trim()"
                                                class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-rose-600 hover:from-indigo-700 hover:to-rose-700 text-white font-bold rounded-xl transition-all shadow-xs cursor-pointer flex items-center gap-2 disabled:opacity-50">
                                            <template x-if="submitting">
                                                <i class="fa-solid fa-spinner fa-spin"></i>
                                            </template>
                                            <template x-if="!submitting">
                                                <i class="fa-solid fa-paper-plane text-xs"></i>
                                            </template>
                                            <span x-text="submitting ? 'Đang gửi...' : 'Gửi phản hồi cho lái xe'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </template>

        </div>

        <!-- 4. Modal Footer -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0 text-xs">
            <span class="text-slate-500">
                Hiển thị <strong class="text-slate-800" x-text="filteredDiscrepancies.length"></strong> / <span x-text="discrepanciesList.length"></span> phản ánh
            </span>

            <button type="button" @click="showDiscrepanciesModal = false"
                    class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold border border-slate-300 rounded-xl transition-all cursor-pointer">
                Đóng
            </button>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL PHÓNG TO HÌNH ẢNH (LIGHTBOX)                       -->
<!-- ======================================================== -->
<div x-show="discrepancyImageLightbox.show" 
     x-cloak
     class="fixed inset-0 z-[100000] bg-black/80 backdrop-blur-xs flex items-center justify-center p-4"
     @keydown.escape.window="discrepancyImageLightbox.show = false"
     @click="discrepancyImageLightbox.show = false">
    <div class="relative max-w-4xl max-h-[90vh] bg-transparent" @click.stop>
        <button type="button" @click="discrepancyImageLightbox.show = false"
                class="absolute -top-10 right-0 text-white hover:text-slate-300 text-2xl font-black">
            &times;
        </button>
        <img :src="discrepancyImageLightbox.src" class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl border border-white/20">
    </div>
</div>
