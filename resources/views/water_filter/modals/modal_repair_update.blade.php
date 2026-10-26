    <!-- ============================================================== -->
    <!-- MODAL 4: CẬP NHẬT CHẨN ĐOÁN & TIẾN ĐỘ SỬA CHỮA                -->
    <!-- ============================================================== -->
    <div x-show="modals.repairUpdateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('repairUpdateModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-lg"></i>
                    <h3 class="font-bold text-base">CHẨN ĐOÁN & TIẾN ĐỘ SỬA CHỮA</h3>
                </div>
                <button @click="closeModal('repairUpdateModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="submitRepairUpdate()" class="p-6 space-y-4 text-xs">
                <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-amber-800">
                    <div class="font-bold" x-text="repairUpdateData.code + ' - ' + repairUpdateData.customer_name"></div>
                    <div class="text-[11px] text-amber-700 mt-0.5" x-text="'Lỗi: ' + repairUpdateData.symptom"></div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cập nhật trạng thái sửa</label>
                    <select x-model="repairUpdateForm.status" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none font-bold">
                        <option value="inspecting">Đang kiểm tra</option>
                        <option value="waiting_parts">Chờ linh kiện</option>
                        <option value="repairing">Đang sửa chữa</option>
                        <option value="completed">Đã sửa xong (Trừ kho linh kiện)</option>
                        <option value="delivered">Đã bàn giao trả máy cho khách</option>
                        <option value="cancelled">Khách không sửa / Hủy</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kết quả chẩn đoán kỹ thuật</label>
                    <textarea x-model="repairUpdateForm.diagnosis" rows="2" placeholder="Hỏng van điện từ, tắc màng RO, rách màng bình áp..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phương án khắc phục / Đã xử lý</label>
                    <textarea x-model="repairUpdateForm.solution" rows="2" placeholder="Đã thay van từ 24V mới, sục rửa cốc màng, đo áp đạt 3.0 bar..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Trạng thái thanh toán</label>
                        <select x-model="repairUpdateForm.payment_status" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none font-medium">
                            <option value="unpaid">Chưa thu tiền</option>
                            <option value="paid">Đã thanh toán đủ</option>
                            <option value="warranty_free">Bảo hành miễn phí</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bảo hành sửa chữa đến ngày</label>
                        <input type="date" x-model="repairUpdateForm.warranty_until" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('repairUpdateModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Đóng</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Cập nhật tiến độ</button>
                </div>
            </form>
        </div>
    </div>

