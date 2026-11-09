    <!-- Modal 2: Hoàn Hàng Vào Kho -->
    <div x-show="showIntakeWarehouseModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showIntakeWarehouseModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Bàn Giao Hàng Hoàn Trả Cho Thủ Kho</h3>
                        <p class="text-xs text-slate-500 font-medium">Gửi biên bản hoàn trả cho Thủ kho kiểm đếm và duyệt nhập kho</p>
                    </div>
                </div>
                <button type="button" @click="showIntakeWarehouseModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-rose-50 rounded-2xl border border-rose-200">
                    <div class="text-slate-600 font-medium">Hàng hoàn trả đang chờ bàn giao trên xe:</div>
                    <div class="text-lg font-black text-rose-950 font-mono mt-0.5"
                         x-text="returnsList.length + ' phiếu hoàn trả (' + formatNumber(returnsTotalAmount) + ' đ)'"></div>
                </div>

                <!-- Bắt buộc chọn Thủ kho / Người xác nhận nhập kho -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-700 block">
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

                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-700 block">Kho lưu trữ bàn giao:</label>
                        <select x-model="intakeForm.warehouseId"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-rose-600 bg-white">
                            <template x-for="wh in warehouses" :key="wh.id">
                                <option :value="wh.id" x-text="wh.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Ghi chú biên bản bàn giao:</label>
                    <input type="text" x-model="intakeForm.notes" placeholder="VD: Bàn giao hàng hoàn trả cuối ca cho thủ kho kiểm đếm..."
                           class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-xs text-slate-800 focus:outline-none focus:border-rose-600 bg-white">
                </div>

                <div class="max-h-[200px] overflow-y-auto space-y-2">
                    <template x-for="r in returnsList" :key="r.id">
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-purple-700" x-text="r.slipCode || r.slip_code || ('TH-' + (r.orderCode || r.order_code))"></span>
                                <span class="text-slate-600 ml-1 font-medium" x-text="'- ' + (r.dealerName || r.dealer_name || 'Khách hàng')"></span>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="(r.items ? r.items.length : 0) + ' mặt hàng trả'"></div>
                            </div>
                            <span class="font-mono font-bold text-rose-700" x-text="formatNumber(r.totalReturnedAmount ?? r.total_returned_amount ?? 0) + ' đ'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showIntakeWarehouseModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmIntakeAllReturns()" :disabled="isIntakingReturns || returnsList.length === 0"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-rose-600/20 disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane text-rose-200"></i>
                    <span x-text="isIntakingReturns ? 'Đang gửi bàn giao...' : 'Gửi Thủ Kho Duyệt Nhập Kho'"></span>
                </button>
            </div>
        </div>
    </div>

