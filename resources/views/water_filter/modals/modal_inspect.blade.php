    <!-- ============================================================== -->
    <!-- MODAL 2: NGHIỆM THU ĐƠN LẮP ĐẶT (TDS, HOÀN THÀNH, TRỪ PHỤ TÙNG) -->
    <!-- ============================================================== -->
    <div x-show="modals.inspectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('inspectModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-lg"></i>
                    <h3 class="font-bold text-base">NGHIỆM THU & BÀN GIAO LẮP ĐẶT</h3>
                </div>
                <button @click="closeModal('inspectModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="submitInspect()" class="p-6 space-y-4 text-xs">
                <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-200 text-emerald-800">
                    <div class="font-bold" x-text="inspectData.code + ' - ' + inspectData.customer_name"></div>
                    <div class="text-[11px] text-emerald-600 mt-0.5" x-text="inspectData.machine_model"></div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cập nhật trạng thái</label>
                    <select x-model="inspectForm.status" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold">
                        <option value="in_progress">Đang lắp đặt</option>
                        <option value="completed">Đã hoàn thành (Trừ kho phụ kiện)</option>
                        <option value="cancelled">Hủy đơn</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chỉ số TDS đầu vào (ppm)</label>
                        <input type="number" x-model="inspectForm.tds_in" placeholder="vd: 180" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chỉ số TDS sau lọc (ppm)</label>
                        <input type="number" x-model="inspectForm.tds_out" placeholder="vd: 8 - 15" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-emerald-700">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tình trạng áp lực / máy hoạt động</label>
                    <input type="text" x-model="inspectForm.pressure" placeholder="Bơm chạy êm, áp lực 2.5 bar, nước tinh khiết chảy đều..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Biên bản nghiệm thu / ghi chú của KTV</label>
                    <textarea x-model="inspectForm.completion_notes" rows="3" placeholder="Đã bàn giao chìa khóa, hướng dẫn khách sử dụng và thời hạn thay lõi..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('inspectModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Đóng</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu nghiệm thu</button>
                </div>
            </form>
        </div>
    </div>

