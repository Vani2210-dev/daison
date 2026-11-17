    <!-- Modal 1: Bàn Giao Tiền COD Cho Kế Toán (Lái xe gửi duyệt) -->
    <div x-show="showCodApprovalModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-2.5 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 max-w-2xl w-full shadow-2xl border border-slate-200 flex flex-col max-h-[94vh]"
             @click.away="showCodApprovalModal = false">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-black text-slate-900 text-sm sm:text-base leading-tight truncate">Bàn Giao Tiền COD Cho Kế Toán</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium truncate">Lái xe nộp tiền COD để Kế toán duyệt giải phóng đơn</p>
                    </div>
                </div>
                <button type="button" @click="showCodApprovalModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-bold p-1 cursor-pointer leading-none ml-2 shrink-0">&times;</button>
            </div>

            <!-- Scrollable Content Body -->
            <div class="space-y-3 text-xs overflow-y-auto pr-1 py-1 flex-1 min-h-0">
                <div class="p-2.5 sm:p-3 bg-amber-50 rounded-2xl border border-amber-200 flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-slate-600 font-medium text-[11px] sm:text-xs">Tổng tiền mặt lái xe đang cầm trên xe:</div>
                        <div class="text-lg sm:text-xl font-black text-amber-950 font-mono mt-0.5" x-text="formatNumber(cashInHand) + ' đ'"></div>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-amber-200/80 text-amber-950 font-extrabold text-xs shrink-0" x-text="selectedVehicle?.plate_number"></span>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block text-xs">Số tiền thực tế lái xe nộp về quỹ:</label>
                    <input type="number" step="1000" x-model="codApprovalForm.amount"
                           class="w-full p-2.5 rounded-xl border border-slate-300 font-mono font-black text-base text-slate-900 focus:outline-none focus:border-indigo-600 bg-white">
                </div>

                <!-- Bắt buộc chọn Kế toán / Thủ quỹ xác nhận -->
                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block text-xs">
                        Chọn Kế toán / Thủ quỹ nhận tiền bàn giao <span class="text-rose-500">*</span>:
                    </label>
                    <select x-model="codApprovalForm.accountantId" @change="onAccountantSelectChange($event)"
                            class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-indigo-600 bg-white">
                        <option value="">-- Bắt buộc chọn Kế toán / Thủ quỹ nhận tiền --</option>
                        <template x-for="acc in accountants" :key="acc.id">
                            <option :value="acc.id" x-text="acc.name + ' (' + (acc.department || 'Kế toán') + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Đính kèm ảnh & file chứng từ nộp tiền -->
                <div class="space-y-2 p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center justify-between gap-2">
                        <label class="font-bold text-slate-800 flex items-center gap-1.5 text-xs truncate">
                            <i class="fa-solid fa-paperclip text-indigo-600 shrink-0"></i>
                            <span class="truncate">Ảnh & File chứng từ bàn giao tiền:</span>
                        </label>
                        <span class="shrink-0 text-[10.5px] font-bold text-indigo-700 bg-indigo-100/80 px-2 py-0.5 rounded-full whitespace-nowrap"
                              x-text="(codApprovalForm.documents ? codApprovalForm.documents.length : 0) + ' tệp'"></span>
                    </div>
                    <div class="text-[10px] text-slate-400 -mt-1">Ảnh chụp xấp tiền mặt, giấy nộp tiền hoặc sao kê chuyển khoản</div>

                    <!-- Hidden file inputs -->
                    <input type="file" x-ref="codCameraInput" accept="image/*" capture="environment" class="hidden"
                           @change="handleCodDocUpload($event)">
                    <input type="file" x-ref="codFileInput" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden"
                           @change="handleCodDocUpload($event)">

                    <!-- Upload buttons -->
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="$refs.codCameraInput.click()"
                                class="py-2.5 px-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-xs cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-camera"></i>
                            <span>Chụp Ảnh Ngay</span>
                        </button>
                        <button type="button" @click="$refs.codFileInput.click()"
                                class="py-2.5 px-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-folder-open text-amber-500"></i>
                            <span>Tải Ảnh / File</span>
                        </button>
                    </div>

                    <!-- Preview Grid -->
                    <template x-if="codApprovalForm.documents && codApprovalForm.documents.length > 0">
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 pt-1 max-h-36 sm:max-h-40 overflow-y-auto pr-1">
                            <template x-for="(doc, idx) in codApprovalForm.documents" :key="idx">
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
                                    <button type="button" @click.stop="removeCodDoc(idx)"
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

                    <template x-if="!codApprovalForm.documents || codApprovalForm.documents.length === 0">
                        <div class="p-2 sm:p-2.5 rounded-xl border border-dashed border-slate-300 text-center text-slate-400 text-[10.5px] sm:text-[11px] bg-white">
                            Chưa có chứng từ đính kèm (Ảnh chụp xấp tiền mặt nộp hoặc biên lai).
                        </div>
                    </template>
                </div>

                <!-- Chú thích quy trình giải phóng đơn -->
                <div class="p-2.5 sm:p-3 bg-sky-50 rounded-2xl border border-sky-200 text-sky-900 flex items-start gap-2 text-[11px]">
                    <i class="fa-solid fa-circle-info text-sky-600 mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="font-bold text-sky-950">Quy trình nộp & kiểm COD:</strong>
                        <span class="text-sky-800 ml-1">
                            Sau khi gửi, Kế toán sẽ kiểm đếm tiền mặt thực tế và xác nhận duyệt. <strong>Sau khi duyệt, đơn hàng mới được giải phóng ra khỏi xe</strong>.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs shrink-0">
                <button type="button" @click="showCodApprovalModal = false"
                        class="px-3.5 py-2.5 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer whitespace-nowrap shrink-0 transition-colors">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmSettleCash()" :disabled="isSettlingCash"
                        class="flex-1 sm:flex-initial px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl cursor-pointer shadow-md shadow-indigo-600/20 disabled:opacity-50 flex items-center justify-center gap-1.5 text-center text-xs whitespace-nowrap transition-all">
                    <i class="fa-solid fa-paper-plane text-indigo-200"></i>
                    <span x-text="isSettlingCash ? 'Đang gửi...' : 'Gửi Kế Toán Duyệt COD'"></span>
                </button>
            </div>
        </div>
    </div>
