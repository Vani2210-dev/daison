<!-- Modal: Bàn Giao Công Nợ Kèm Chứng Từ (Quy trình 3 bước: Tài xế → Kế toán → Sale) -->
<div x-show="showDebtHandoverModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4">
    <div class="bg-white rounded-2xl sm:rounded-3xl p-3.5 sm:p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-3 sm:space-y-4 max-h-[94vh] flex flex-col"
         @click.away="showDebtHandoverModal = false">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-sm sm:text-base shadow-sm shrink-0">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <h3 class="font-black text-slate-900 text-sm sm:text-base leading-tight truncate">Bàn Giao Công Nợ Kèm Chứng Từ</h3>
                        <span class="px-2 py-0.2 rounded-full bg-purple-100 text-purple-700 font-bold text-[9px] sm:text-[10px] uppercase tracking-wider whitespace-nowrap">
                            3 Bước
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium truncate mt-0.5">Tài xế gửi chứng từ cho Kế toán đối soát chuyển tiếp Sale</p>
                </div>
            </div>
            <button type="button" @click="showDebtHandoverModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-bold p-1 cursor-pointer leading-none ml-2 shrink-0">&times;</button>
        </div>

        <!-- Scrollable Body -->
        <div class="space-y-3 sm:space-y-4 text-xs overflow-y-auto pr-1 py-0.5 flex-1 min-h-0">
            
            <!-- Summary Card -->
            <div class="p-3 sm:p-3.5 bg-gradient-to-r from-purple-50 to-indigo-50/60 rounded-2xl border border-purple-200/80 flex items-center justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <div class="text-slate-600 font-medium text-[11px] truncate">Công nợ đại lý bàn giao:</div>
                    <div class="text-xl sm:text-2xl font-black text-purple-950 font-mono tracking-tight whitespace-nowrap mt-0.5"
                         x-text="formatNumber(debtHandoverForm.amount || 0) + ' đ'"></div>
                    <div class="text-[10.5px] sm:text-[11px] text-purple-700 font-semibold mt-0.5 truncate"
                         x-text="'Đã chọn ' + (debtHandoverForm.selectedOrderIds.length) + ' đơn hàng'"></div>
                </div>
                <div class="text-right shrink-0">
                    <span class="px-2.5 py-1 rounded-xl bg-purple-200/80 text-purple-950 font-extrabold text-xs inline-block whitespace-nowrap"
                          x-text="selectedVehicle?.plate_number"></span>
                    <div class="text-[10px] text-slate-500 mt-0.5 whitespace-nowrap">Xe vận chuyển</div>
                </div>
            </div>

            <!-- Danh sách đơn hàng nợ trên xe để chọn -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between gap-2">
                    <label class="font-bold text-slate-800 flex items-center gap-1.5 text-xs truncate">
                        <i class="fa-solid fa-list-check text-purple-600 shrink-0"></i>
                        <span class="truncate">Chọn đơn bàn giao nợ:</span>
                    </label>
                    <div class="flex items-center gap-2 text-[11px] shrink-0 whitespace-nowrap">
                        <button type="button" @click="selectAllDebtOrders(true)" class="text-purple-600 hover:underline font-bold cursor-pointer whitespace-nowrap">
                            Chọn tất cả
                        </button>
                        <span class="text-slate-300">|</span>
                        <button type="button" @click="selectAllDebtOrders(false)" class="text-slate-500 hover:underline font-bold cursor-pointer whitespace-nowrap">
                            Bỏ chọn
                        </button>
                    </div>
                </div>

                <div class="max-h-40 sm:max-h-48 overflow-y-auto rounded-2xl border border-slate-200 divide-y divide-slate-100 bg-slate-50/50">
                    <template x-if="candidateDebtOrders.length === 0">
                        <div class="p-3.5 text-center text-slate-400 text-xs">
                            Không có đơn hàng nào có công nợ trên xe hoặc tất cả đã bàn giao xong.
                        </div>
                    </template>

                    <template x-for="ord in candidateDebtOrders" :key="ord.id">
                        <label class="p-2.5 sm:p-3 flex items-start gap-2.5 hover:bg-white transition-colors cursor-pointer block">
                            <input type="checkbox" :value="ord.id" x-model="debtHandoverForm.selectedOrderIds"
                                   @change="recalcDebtHandoverAmount()"
                                   class="mt-1 rounded text-purple-600 focus:ring-purple-500 w-4 h-4 shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono font-bold text-slate-900 truncate" x-text="ord.order_code"></span>
                                    <span class="font-mono font-black text-purple-700 text-xs sm:text-sm shrink-0 whitespace-nowrap"
                                          x-text="formatNumber(getOrderDebtValue(ord)) + ' đ'"></span>
                                </div>
                                <div class="text-slate-600 truncate mt-0.5 font-medium text-[11px]" x-text="ord.customer_name"></div>
                                <div class="flex items-center gap-1.5 mt-1 text-[10px]">
                                    <span class="px-1.5 py-0.2 rounded bg-purple-100 text-purple-700 font-bold whitespace-nowrap"
                                          x-text="ord.payment_status || 'Ghi nợ đại lý'"></span>
                                    <span class="text-slate-400 truncate" x-text="ord.status"></span>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Chọn Kế toán kiểm tra chứng từ (*) -->
            <div class="space-y-1.5 p-2.5 sm:p-3 rounded-2xl bg-amber-50/60 border border-amber-200/80">
                <div class="flex items-center justify-between gap-2">
                    <label class="font-bold text-slate-800 flex items-center gap-1.5 text-xs text-amber-950 truncate">
                        <i class="fa-solid fa-user-check text-amber-600 shrink-0"></i>
                        <span class="truncate">Kế toán nhận & duyệt chứng từ <span class="text-rose-500">*</span>:</span>
                    </label>
                    <span class="text-[10px] text-amber-700 font-medium shrink-0 whitespace-nowrap hidden sm:inline">Kế toán đối soát & chuyển cho Sale</span>
                </div>
                <select x-model="debtHandoverForm.accountantId"
                        class="w-full p-2.5 rounded-xl border border-amber-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-amber-600 focus:ring-1 focus:ring-amber-600 bg-white">
                    <option value="">-- Bắt buộc chọn Kế toán nhận chứng từ (*) --</option>
                    <template x-for="acc in accountants" :key="acc.id">
                        <option :value="acc.id"
                                x-text="acc.name + (acc.phone ? ' (' + acc.phone + ')' : '') + (acc.department ? ' - ' + acc.department : '')"></option>
                    </template>
                </select>
            </div>

            <!-- Đính kèm chứng từ (Ảnh phiếu giao hàng / chữ ký nhận nợ / file biên bản) (*) -->
            <div class="space-y-2 p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="flex items-center justify-between gap-2">
                    <label class="font-bold text-slate-800 flex items-center gap-1.5 text-xs truncate">
                        <i class="fa-solid fa-paperclip text-sky-600 shrink-0"></i>
                        <span class="truncate">Ảnh & File chứng từ công nợ <span class="text-rose-500">*</span>:</span>
                    </label>
                    <span class="shrink-0 text-[10.5px] font-bold text-sky-700 bg-sky-100/80 px-2 py-0.5 rounded-full whitespace-nowrap"
                          x-text="(debtHandoverForm.documents ? debtHandoverForm.documents.length : 0) + ' tệp'"></span>
                </div>
                <div class="text-[10px] text-slate-400 -mt-1">Ảnh chụp phiếu giao, chữ ký nhận nợ hoặc biên bản đối soát</div>

                <!-- Hidden file inputs -->
                <input type="file" x-ref="cameraInput" accept="image/*" capture="environment" class="hidden"
                       @change="handleDebtDocUpload($event)">
                <input type="file" x-ref="fileInput" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden"
                       @change="handleDebtDocUpload($event)">

                <!-- Upload buttons -->
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="$refs.cameraInput.click()"
                            class="py-2.5 px-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-xs cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-camera"></i>
                        <span>Chụp Ảnh Ngay</span>
                    </button>
                    <button type="button" @click="$refs.fileInput.click()"
                            class="py-2.5 px-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-folder-open text-amber-500"></i>
                        <span>Tải Ảnh / File</span>
                    </button>
                </div>

                <!-- Preview Grid -->
                <template x-if="debtHandoverForm.documents && debtHandoverForm.documents.length > 0">
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 pt-1 max-h-36 sm:max-h-40 overflow-y-auto pr-1">
                        <template x-for="(doc, idx) in debtHandoverForm.documents" :key="idx">
                            <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-white aspect-square shadow-2xs flex flex-col items-center justify-center p-1">
                                <template x-if="isImageDoc(doc)">
                                    <img :src="getDocUrl(doc)" class="w-full h-full object-cover cursor-pointer rounded-lg hover:opacity-90 transition-opacity"
                                         @click="viewDocPreview(doc)" alt="Chứng từ">
                                </template>
                                <template x-if="!isImageDoc(doc)">
                                    <div class="w-full h-full flex flex-col items-center justify-center text-center p-1 cursor-pointer hover:bg-slate-50 transition-colors"
                                         @click="viewOrOpenDoc(doc)">
                                        <i class="fa-solid text-xl sm:text-2xl mb-1" :class="getDocIcon(doc)"></i>
                                        <div class="text-[9.5px] font-bold text-slate-700 truncate w-full" x-text="getDocName(doc)"></div>
                                        <div class="text-[8px] text-slate-400 font-mono" x-show="doc.size" x-text="(doc.size / 1024).toFixed(0) + ' KB'"></div>
                                    </div>
                                </template>
                                <button type="button" @click.stop="removeDebtDoc(idx)"
                                        class="absolute top-1 right-1 w-6 h-6 rounded-full bg-rose-600 text-white text-xs flex items-center justify-center hover:bg-rose-700 shadow-md cursor-pointer transition-transform group-hover:scale-110">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <div class="absolute bottom-0 inset-x-0 bg-slate-900/60 text-white text-[9px] py-0.5 text-center font-bold" x-show="isImageDoc(doc)">
                                    Ảnh <span x-text="idx + 1"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!debtHandoverForm.documents || debtHandoverForm.documents.length === 0">
                    <div class="p-2 sm:p-2.5 rounded-xl border border-dashed border-slate-300 text-center text-slate-400 text-[10.5px] sm:text-[11px] bg-white">
                        Chưa có chứng từ đính kèm (Ảnh chụp phiếu giao ký nhận hoặc file scan).
                    </div>
                </template>
            </div>

            <!-- Số tiền bàn giao thực tế -->
            <div class="space-y-1">
                <label class="font-bold text-slate-800 block text-xs">Số tiền công nợ bàn giao (VNĐ):</label>
                <input type="number" step="1000" x-model="debtHandoverForm.amount"
                       class="w-full p-2.5 rounded-xl border border-slate-300 font-mono font-black text-sm sm:text-base text-slate-900 focus:outline-none focus:border-purple-600 bg-white">
            </div>

            <!-- Ghi chú của lái xe -->
            <div class="space-y-1">
                <label class="font-bold text-slate-800 block text-xs">Ghi chú bàn giao nợ:</label>
                <textarea rows="2" x-model="debtHandoverForm.notes"
                          placeholder="VD: Gửi Kế toán đối soát phiếu giao hàng có chữ ký khách của các đơn nợ trên xe..."
                          class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-slate-800 text-xs bg-white"></textarea>
            </div>

            <!-- Box hướng dẫn quy trình 3 bước -->
            <div class="p-2.5 sm:p-3 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl border border-purple-200 text-purple-900 text-[10.5px] sm:text-[11px] leading-relaxed space-y-1.5">
                <div class="font-bold text-purple-950 flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-purple-600 text-xs"></i>
                    <span>Quy trình 3 bước bàn giao nợ:</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5 sm:gap-2 pt-0.5 text-[10px] sm:text-[10.5px]">
                    <div class="p-2 rounded-xl bg-white border border-purple-100">
                        <strong class="text-purple-900 block">1. Tài xế:</strong>
                        <span class="text-slate-600">Đính kèm ảnh chứng từ ký nhận & gửi Kế toán.</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-purple-100">
                        <strong class="text-amber-800 block">2. Kế toán:</strong>
                        <span class="text-slate-600">Kiểm tra tính hợp lệ chứng từ và chuyển tiếp Sale.</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-purple-100">
                        <strong class="text-emerald-800 block">3. Sale:</strong>
                        <span class="text-slate-600">Bấm "Xác nhận nhận nợ" → Giải phóng nợ cho xe.</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 shrink-0 text-xs">
            <button type="button" @click="showDebtHandoverModal = false"
                    class="px-3.5 py-2.5 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer whitespace-nowrap shrink-0 transition-colors">
                Hủy bỏ
            </button>
            <button type="button" @click="submitDebtHandover()" :disabled="isSubmittingDebtHandover"
                    class="flex-1 sm:flex-initial px-4 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl cursor-pointer shadow-md shadow-purple-600/25 disabled:opacity-50 flex items-center justify-center gap-1.5 transition-all whitespace-nowrap text-xs">
                <i class="fa-solid fa-paper-plane text-purple-200"></i>
                <span x-text="isSubmittingDebtHandover ? 'Đang gửi...' : 'Gửi Kế Toán (Bước 1)'"></span>
            </button>
        </div>

    </div>
</div>

<!-- Modal xem trước ảnh chứng từ phóng to -->
<div x-show="showDocPreviewModal" x-cloak class="fixed inset-0 z-[11000] bg-black/90 backdrop-blur-md flex items-center justify-center p-3"
     @click="showDocPreviewModal = false">
    <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center" @click.stop>
        <button type="button" @click="showDocPreviewModal = false"
                class="absolute -top-10 right-0 text-white/80 hover:text-white text-3xl font-bold cursor-pointer">
            &times;
        </button>
        <img :src="previewDocUrl" class="max-w-full max-h-[85vh] rounded-2xl object-contain shadow-2xl border border-white/20">
    </div>
</div>
