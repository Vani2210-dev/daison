    <!-- ============================================================== -->
    <!-- MODAL: DUYỆT KÍCH HOẠT BẢO HÀNH CHO MÁY                        -->
    <!-- ============================================================== -->
    <div x-show="modals.activateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('activateModal')" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-gradient-to-r from-orange-600 to-amber-600 text-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <h3 class="font-extrabold text-sm">Phê Duyệt Kích Hoạt Bảo Hành Máy Lọc Nước</h3>
                </div>
                <button type="button" @click="closeModal('activateModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form @submit.prevent="submitActivateMachine()" class="p-5 space-y-4 text-xs">
                <!-- Info Summary submitted by technician -->
                <div class="p-3.5 bg-orange-50/70 border border-orange-200/80 rounded-xl space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800 text-sm" x-text="activateData?.customer_name || 'Khách hàng'"></span>
                        <span class="font-mono font-bold text-orange-800 bg-orange-100 px-2 py-0.5 rounded text-[11px]" x-text="activateData?.qr_code"></span>
                    </div>
                    <div class="text-slate-600 flex items-center gap-2">
                        <span>SĐT: <strong class="text-slate-900" x-text="activateData?.customer_phone"></strong></span>
                        <span>•</span>
                        <span>Model: <strong class="text-blue-700" x-text="activateData?.machine_model"></strong></span>
                    </div>
                    <div class="text-slate-500 text-[11px]">
                        Địa chỉ: <span x-text="activateData?.customer_address"></span>
                    </div>
                    <div class="text-slate-600 text-[11px] pt-1 border-t border-orange-200/60 flex items-center justify-between">
                        <span>KTV gửi: <strong class="text-slate-800" x-text="activateData?.technician_name || 'KTV'"></strong></span>
                        <template x-if="activateData?.activation_notes">
                            <span class="italic text-slate-500" x-text="'Ghi chú: ' + activateData.activation_notes"></span>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ngày lắp đặt chính thức <span class="text-rose-500">*</span></label>
                        <input type="date" x-model="activateForm.installed_at" @change="recalcActivateUntil()" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 font-bold text-slate-800">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thời hạn bảo hành</label>
                        <select x-model.number="activateForm.warranty_months" @change="recalcActivateUntil()"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 font-bold text-slate-800">
                            <option value="12">12 tháng (1 năm)</option>
                            <option value="24">24 tháng (2 năm - Chuẩn)</option>
                            <option value="36">36 tháng (3 năm)</option>
                            <option value="60">60 tháng (5 năm)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hạn bảo hành đến ngày</label>
                        <input type="date" x-model="activateForm.warranty_until"
                               class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold text-emerald-900">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã phiếu / Thẻ bảo hành</label>
                        <input type="text" x-model="activateForm.warranty_card_code" placeholder="BH-..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-mono font-bold text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số Serial máy</label>
                        <input type="text" x-model="activateForm.machine_serial" placeholder="SN-..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-mono font-bold text-slate-800 uppercase">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kỳ thay lõi định kỳ</label>
                        <select x-model.number="activateForm.filter_replacement_months"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-bold text-slate-800">
                            <option value="3">3 tháng</option>
                            <option value="6">6 tháng (Khuyến nghị)</option>
                            <option value="9">9 tháng</option>
                            <option value="12">12 tháng</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú duyệt của quản lý</label>
                    <input type="text" x-model="activateForm.admin_notes" placeholder="Đã kiểm tra thông tin, duyệt bảo hành chính hãng..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white font-medium">
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal('activateModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Phê Duyệt Kích Hoạt Ngay</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

