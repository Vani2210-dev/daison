    <!-- Modal 4: Báo Giao Không Thành Công -->
    <div x-show="showFailModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showFailModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="font-black text-slate-900 text-base">Báo Giao Thất Bại</h3>
                </div>
                <button type="button" @click="showFailModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Lý do giao hàng không thành công:</label>
                    <select x-model="failForm.reason" class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold text-slate-900 bg-white">
                        <option value="Khách hẹn giao lại vào buổi sau">Khách hẹn giao lại vào buổi sau</option>
                        <option value="Khách vắng mặt, không liên lạc được">Khách vắng mặt, không liên lạc được</option>
                        <option value="Đường cấm tải / sự cố không vào được">Đường cấm tải / sự cố không vào được</option>
                        <option value="Khách từ chối nhận hàng do đổi ý">Khách từ chối nhận hàng do đổi ý</option>
                        <option value="Khác">Lý do khác...</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Ngày hẹn giao lại (nếu có):</label>
                    <input type="date" x-model="failForm.rescheduledDate" class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold text-slate-900 bg-white">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Ghi chú cụ thể:</label>
                    <textarea rows="2" x-model="failForm.notes" placeholder="Mô tả thêm tình hình thực tế tại điểm giao..."
                              class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-slate-800 text-xs bg-white"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs">
                <button type="button" @click="showFailModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitFailDelivery()" :disabled="isSubmittingFail"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-rose-600/20 disabled:opacity-50">
                    <span x-text="isSubmittingFail ? 'Đang lưu...' : 'Xác Nhận Báo Thất Bại'"></span>
                </button>
            </div>
        </div>
    </div>

