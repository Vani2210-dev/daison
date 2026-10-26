    <!-- ============================================================== -->
    <!-- MODAL: SỬA TOÀN DIỆN THÔNG TIN MÁY LỌC NƯỚC                    -->
    <!-- ============================================================== -->
    <div x-show="modals.machineEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('machineEditModal')" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-gradient-to-r from-emerald-600 to-teal-600 text-white shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                    <div>
                        <h3 class="font-extrabold text-sm">Chỉnh Sửa Chi Tiết Máy Lọc Nước</h3>
                        <p class="text-[10px] text-emerald-100 font-mono" x-text="(machineEditForm.machine_code || '') + ' • ' + (machineEditForm.qr_code || '')"></p>
                    </div>
                </div>
                <button type="button" @click="closeModal('machineEditModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form @submit.prevent="submitMachineEdit()" class="p-5 space-y-4 text-xs overflow-y-auto flex-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Họ tên khách hàng</label>
                        <input type="text" x-model="machineEditForm.customer_name" placeholder="Nguyễn Văn A"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số điện thoại</label>
                        <input type="tel" x-model="machineEditForm.customer_phone" placeholder="0912 xxx xxx"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Địa chỉ lắp đặt</label>
                    <input type="text" x-model="machineEditForm.customer_address" placeholder="Địa chỉ cụ thể..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Model máy lọc</label>
                        <input type="text" x-model="machineEditForm.machine_model" placeholder="Model máy..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số Serial máy</label>
                        <input type="text" x-model="machineEditForm.machine_serial" placeholder="Serial máy..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium uppercase font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Trạng thái máy</label>
                        <select x-model="machineEditForm.status"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-bold text-slate-800">
                            <option value="unactivated">Tem QR trống</option>
                            <option value="pending_activation">Chờ duyệt kích hoạt</option>
                            <option value="activated">Đã kích hoạt</option>
                            <option value="expired">Hết hạn bảo hành</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">KTV phụ trách</label>
                        <select x-model="machineEditForm.technician_id"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                            <option value="">-- Chưa gán KTV --</option>
                            <template x-for="t in technicians" :key="t.id">
                                <option :value="t.id" x-text="t.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ngày lắp đặt</label>
                        <input type="date" x-model="machineEditForm.installed_at" @change="recalcMachineEditUntil()"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-bold text-slate-800">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thời hạn BH (tháng)</label>
                        <input type="number" x-model.number="machineEditForm.warranty_months" @change="recalcMachineEditUntil()"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-bold text-slate-800">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hạn BH đến</label>
                        <input type="date" x-model="machineEditForm.warranty_until"
                               class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold text-emerald-900">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">TDS Đầu vào</label>
                        <input type="number" x-model.number="machineEditForm.tds_in" placeholder="ppm"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white text-center font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">TDS Tinh khiết</label>
                        <input type="number" x-model.number="machineEditForm.tds_out" placeholder="ppm"
                               class="w-full px-3 py-2 bg-emerald-50 border border-emerald-300 rounded-xl focus:bg-white text-center font-bold text-emerald-800">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Áp lực nước</label>
                        <input type="text" x-model="machineEditForm.pressure" placeholder="kg/cm²"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white text-center font-medium">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-boxes-stacked text-teal-600"></i>
                        <span>Phụ kiện kèm theo máy</span>
                    </label>
                    <input type="text" x-model="machineEditForm.accessories" placeholder="Ví dụ: Vòi ngỗng Inox SUS304, bình áp 3.2G, khoá chia nước, cờ lê mở cốc..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium text-slate-800">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú quản lý</label>
                    <textarea x-model="machineEditForm.admin_notes" rows="2" placeholder="Ghi chú thêm..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal('machineEditModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Lưu Cập Nhật Máy</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

