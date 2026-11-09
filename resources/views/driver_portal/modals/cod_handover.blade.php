
    <!-- Modal 1: Bàn Giao Tiền COD Cho Kế Toán (Lái xe gửi duyệt) -->
    <div x-show="showCodApprovalModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showCodApprovalModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Bàn Giao Tiền COD Cho Kế Toán</h3>
                        <p class="text-xs text-slate-500 font-medium">Lái xe lập biên bản bàn giao nộp tiền COD để Kế toán kiểm đếm & duyệt giải phóng đơn</p>
                    </div>
                </div>
                <button type="button" @click="showCodApprovalModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200 flex items-center justify-between">
                    <div>
                        <div class="text-slate-600 font-medium">Tổng tiền mặt lái xe đang cầm trên xe:</div>
                        <div class="text-xl font-black text-amber-950 font-mono" x-text="formatNumber(cashInHand) + ' đ'"></div>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-amber-200/80 text-amber-950 font-extrabold text-xs" x-text="selectedVehicle?.plate_number"></span>
                </div>

                <div class="space-y-2">
                    <label class="font-bold text-slate-700 block">Số tiền thực tế lái xe nộp về quỹ:</label>
                    <input type="number" step="1000" x-model="codApprovalForm.amount"
                           class="w-full p-2.5 rounded-xl border border-slate-300 font-mono font-black text-base text-slate-900 focus:outline-none focus:border-indigo-600 bg-white">
                </div>

                <!-- Bắt buộc chọn Kế toán / Thủ quỹ xác nhận -->
                <div class="space-y-2">
                    <label class="font-bold text-slate-700 block">
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

                <div class="space-y-2">
                    <label class="font-bold text-slate-700 block">Ghi chú của Lái xe khi nộp tiền:</label>
                    <textarea rows="2" x-model="codApprovalForm.notes" placeholder="VD: Bàn giao tiền mặt COD cuối ca..."
                              class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-slate-800 text-xs bg-white"></textarea>
                </div>

                <!-- Chú thích quy trình giải phóng đơn -->
                <div class="p-3 bg-sky-50 rounded-2xl border border-sky-200 text-sky-900 flex items-start gap-2 text-[11px]">
                    <i class="fa-solid fa-circle-info text-sky-600 mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="font-bold text-sky-950">Quy trình nộp & kiểm COD:</strong>
                        <span class="text-sky-800 ml-1">
                            Sau khi gửi yêu cầu, thông tin sẽ được chuyển tới menu <strong>Bán hàng > Kiểm COD</strong>. Kế toán sẽ kiểm đếm tiền mặt thực tế và xác nhận duyệt. <strong>Sau khi Kế toán xác nhận duyệt thì toàn bộ các đơn hàng đã giao mới được giải phóng ra khỏi xe</strong>.
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showCodApprovalModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="confirmSettleCash()" :disabled="isSettlingCash"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-indigo-600/20 disabled:opacity-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane text-indigo-200"></i>
                    <span x-text="isSettlingCash ? 'Đang gửi yêu cầu...' : 'Gửi Kế Toán Duyệt COD'"></span>
                </button>
            </div>
        </div>
    </div>

