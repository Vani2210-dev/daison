    <!-- ============================================================== -->
    <!-- MODAL: CẬP NHẬT THÔNG TIN BẢO HÀNH MÁY                         -->
    <!-- ============================================================== -->
    <div x-show="modals.warrantyEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('warrantyEditModal')" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-gradient-to-r from-emerald-600 to-teal-600 text-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-lg"></i>
                    <h3 class="font-extrabold text-sm">Cập Nhật Thông Tin Bảo Hành Máy Lọc Nước</h3>
                </div>
                <button type="button" @click="closeModal('warrantyEditModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form @submit.prevent="saveWarrantySubmit()" class="p-5 space-y-4 text-xs">
                <!-- Thông tin tóm tắt máy -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800" x-text="warrantyEditForm.customer_name"></span>
                        <span class="text-[10px] font-bold text-sky-700 font-mono" x-text="warrantyEditForm.code"></span>
                    </div>
                    <div class="text-[11px] text-slate-600 flex items-center gap-2">
                        <span>Model: <strong x-text="warrantyEditForm.machine_model"></strong></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ngày lắp đặt thực tế <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            x-model="warrantyEditForm.installed_at" 
                            @change="recalcEditWarrantyUntil()" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-800"
                        >
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Serial / IMEI máy</label>
                        <input 
                            type="text" 
                            x-model="warrantyEditForm.machine_serial" 
                            placeholder="KG-..." 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono font-bold text-slate-800"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thời hạn bảo hành (tháng)</label>
                        <select 
                            x-model.number="warrantyEditForm.warranty_months" 
                            @change="recalcEditWarrantyUntil()" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-800"
                        >
                            <option value="6">6 tháng</option>
                            <option value="12">12 tháng (1 năm)</option>
                            <option value="18">18 tháng</option>
                            <option value="24">24 tháng (2 năm - Tiêu chuẩn)</option>
                            <option value="36">36 tháng (3 năm)</option>
                            <option value="60">60 tháng (5 năm)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hạn bảo hành đến ngày</label>
                        <input 
                            type="date" 
                            x-model="warrantyEditForm.warranty_until" 
                            class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-emerald-900"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã / Số thẻ bảo hành</label>
                        <input 
                            type="text" 
                            x-model="warrantyEditForm.warranty_card_code" 
                            placeholder="BH-2026-..." 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono font-bold text-slate-800"
                        >
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kỳ thay lõi / Bảo dưỡng</label>
                        <select 
                            x-model.number="warrantyEditForm.filter_replacement_months" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-700"
                        >
                            <option value="3">Mỗi 3 tháng (Lõi 1, 2, 3)</option>
                            <option value="6">Mỗi 6 tháng (Khuyến nghị)</option>
                            <option value="9">Mỗi 9 tháng</option>
                            <option value="12">Mỗi 12 tháng (1 năm)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ngày hẹn thay lõi / Bảo dưỡng tiếp theo</label>
                    <input 
                        type="date" 
                        x-model="warrantyEditForm.next_maintenance_date" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-800"
                    >
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú điều kiện bảo hành / Cam kết</label>
                    <textarea 
                        x-model="warrantyEditForm.warranty_notes" 
                        rows="2" 
                        placeholder="Ví dụ: Bảo hành điện 2 năm, màng RO 1 năm, lỗi do nguồn nước cấp không bảo hành..." 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    ></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal('warrantyEditModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Lưu Cập Nhật Bảo Hành</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

