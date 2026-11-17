    <!-- Modal 2: Hoàn Hàng Vào Kho -->
    <div x-show="showIntakeWarehouseModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-2.5 sm:p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 max-w-2xl w-full shadow-2xl border border-slate-200 flex flex-col max-h-[94vh]"
             @click.away="showIntakeWarehouseModal = false">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-black text-slate-900 text-sm sm:text-base leading-tight truncate">Bàn Giao Hàng Hoàn Trả Cho Thủ Kho</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium truncate">Gửi biên bản hoàn trả cho Thủ kho kiểm đếm</p>
                    </div>
                </div>
                <button type="button" @click="showIntakeWarehouseModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-bold p-1 cursor-pointer leading-none ml-2 shrink-0">&times;</button>
            </div>

            <!-- Scrollable Content Body -->
            <div class="space-y-3 text-xs overflow-y-auto pr-1 py-1 flex-1 min-h-0">
                <!-- Summary banner -->
                <div class="p-2.5 sm:p-3 bg-rose-50 rounded-2xl border border-rose-200 flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-slate-600 font-medium text-[11px] sm:text-xs">Hàng hoàn trả đang chờ bàn giao trên xe:</div>
                        <div class="text-base sm:text-lg font-black text-rose-950 font-mono mt-0.5"
                             x-text="returnsList.length + ' phiếu hoàn trả (' + formatNumber(returnsTotalAmount) + ' đ)'"></div>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-rose-200/70 text-rose-800 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-box-open text-xs"></i>
                    </div>
                </div>

                <!-- Bắt buộc chọn Thủ kho / Người xác nhận nhập kho -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-slate-700 block text-xs">
                            Thủ kho nhận bàn giao <span class="text-rose-500">*</span>:
                        </label>
                        <select x-model="intakeForm.intakeUserId" @change="onIntakeUserSelectChange($event)"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-rose-600 bg-white">
                            <option value="">-- Bắt buộc chọn Thủ kho --</option>
                            <template x-for="staff in warehouseStaff" :key="staff.id">
                                <option :value="staff.id" x-text="staff.name + ' (' + (staff.department || 'Kho') + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-slate-700 block text-xs">Kho lưu trữ bàn giao:</label>
                        <select x-model="intakeForm.warehouseId"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-rose-600 bg-white">
                            <template x-for="wh in (companyWarehouses && companyWarehouses.length ? companyWarehouses : warehouses)" :key="wh.id">
                                <option :value="wh.id" x-text="wh.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block text-xs">Ghi chú biên bản bàn giao:</label>
                    <input type="text" x-model="intakeForm.notes" placeholder="VD: Bàn giao hàng hoàn trả cuối ca cho thủ kho kiểm đếm..."
                           class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-xs text-slate-800 focus:outline-none focus:border-rose-600 bg-white">
                </div>

                <!-- Đính kèm ảnh & file chứng từ bàn giao hàng trả -->
                <div class="space-y-2 p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center justify-between gap-2">
                        <label class="font-bold text-slate-800 flex items-center gap-1.5 text-xs truncate">
                            <i class="fa-solid fa-paperclip text-rose-600 shrink-0"></i>
                            <span class="truncate">Ảnh & File chứng từ hàng trả:</span>
                        </label>
                        <span class="shrink-0 text-[10.5px] font-bold text-rose-700 bg-rose-100/80 px-2 py-0.5 rounded-full whitespace-nowrap"
                              x-text="(intakeForm.documents ? intakeForm.documents.length : 0) + ' tệp'"></span>
                    </div>
                    <div class="text-[10px] text-slate-400 -mt-1">Ảnh chụp thực tế, tem mác lỗi hoặc biên bản ký trả</div>

                    <!-- Hidden file inputs -->
                    <input type="file" x-ref="intakeCameraInput" accept="image/*" capture="environment" class="hidden"
                           @change="handleIntakeDocUpload($event)">
                    <input type="file" x-ref="intakeFileInput" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple class="hidden"
                           @change="handleIntakeDocUpload($event)">

                    <!-- Upload buttons -->
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="$refs.intakeCameraInput.click()"
                                class="py-2.5 px-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-xs cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-camera"></i>
                            <span>Chụp Ảnh Ngay</span>
                        </button>
                        <button type="button" @click="$refs.intakeFileInput.click()"
                                class="py-2.5 px-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-folder-open text-amber-500"></i>
                            <span>Tải Ảnh / File</span>
                        </button>
                    </div>

                    <!-- Preview Grid -->
                    <template x-if="intakeForm.documents && intakeForm.documents.length > 0">
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 pt-1 max-h-36 sm:max-h-40 overflow-y-auto pr-1">
                            <template x-for="(doc, idx) in intakeForm.documents" :key="idx">
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
                                    <button type="button" @click.stop="removeIntakeDoc(idx)"
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

                    <template x-if="!intakeForm.documents || intakeForm.documents.length === 0">
                        <div class="p-2 sm:p-2.5 rounded-xl border border-dashed border-slate-300 text-center text-slate-400 text-[10.5px] sm:text-[11px] bg-white">
                            Chưa có chứng từ đính kèm (Ảnh chụp hàng trả thực tế, phiếu ký trả hoặc file scan).
                        </div>
                    </template>
                </div>

                <!-- Danh sách phiếu hoàn trả trên xe -->
                <div class="max-h-36 sm:max-h-48 overflow-y-auto space-y-2">
                    <template x-for="r in returnsList" :key="r.id">
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-xs">
                                    <span class="font-mono font-bold text-purple-700" x-text="r.slipCode || r.slip_code || ('TH-' + (r.orderCode || r.order_code))"></span>
                                    <span class="text-slate-600 ml-1 font-medium" x-text="'- ' + (r.dealerName || r.dealer_name || 'Khách hàng')"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="(r.items ? r.items.length : 0) + ' mặt hàng trả'"></div>
                            </div>
                            <span class="font-mono font-bold text-rose-700 shrink-0 whitespace-nowrap text-xs sm:text-sm"
                                  x-text="formatNumber(r.totalReturnedAmount ?? r.total_returned_amount ?? 0) + ' đ'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs shrink-0">
                <button type="button" @click="showIntakeWarehouseModal = false"
                        class="px-3.5 py-2.5 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer whitespace-nowrap shrink-0 transition-colors">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmIntakeAllReturns()" :disabled="isIntakingReturns || returnsList.length === 0"
                        class="flex-1 sm:flex-initial px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl cursor-pointer shadow-md shadow-rose-600/20 disabled:opacity-50 flex items-center justify-center gap-1.5 text-center text-xs whitespace-nowrap transition-all">
                    <i class="fa-solid fa-paper-plane text-rose-200"></i>
                    <span x-text="isIntakingReturns ? 'Đang gửi...' : 'Gửi Thủ Kho Duyệt Nhập Kho'"></span>
                </button>
            </div>
        </div>
    </div>
