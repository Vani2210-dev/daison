    <!-- ============================================================== -->
    <!-- MODAL: TẠO MÃ QR THÔNG TIN TRỐNG (HÀNG LOẠT)                   -->
    <!-- ============================================================== -->
    <div x-show="modals.batchQrModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('batchQrModal')" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-600 text-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-qrcode text-lg text-cyan-200"></i>
                    <h3 class="font-extrabold text-sm">Tạo Lô Mã QR Máy Lọc Thông Tin Trống</h3>
                </div>
                <button type="button" @click="closeModal('batchQrModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form @submit.prevent="submitBatchQr()" class="p-5 space-y-4 text-xs">
                <div class="p-3 bg-blue-50/80 border border-blue-200/80 rounded-xl text-blue-900 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-blue-600 mr-1"></i>
                    Hệ thống sẽ tạo sẵn các tem mã QR trống. Bạn có thể in tem dán lên máy xuất xưởng. Khi KTV đi lắp đặt thực tế sẽ quét tem để nhập khách hàng và kích hoạt bảo hành.
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Số lượng tem QR cần tạo <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="number" x-model.number="batchQrForm.quantity" min="1" max="100" required
                               class="w-24 px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-center focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <div class="flex items-center gap-1">
                            <button type="button" @click="batchQrForm.quantity = 5" :class="batchQrForm.quantity == 5 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-2.5 py-1.5 rounded-lg font-bold transition">5</button>
                            <button type="button" @click="batchQrForm.quantity = 10" :class="batchQrForm.quantity == 10 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-2.5 py-1.5 rounded-lg font-bold transition">10</button>
                            <button type="button" @click="batchQrForm.quantity = 20" :class="batchQrForm.quantity == 20 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-2.5 py-1.5 rounded-lg font-bold transition">20</button>
                            <button type="button" @click="batchQrForm.quantity = 50" :class="batchQrForm.quantity == 50 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-2.5 py-1.5 rounded-lg font-bold transition">50</button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Gán trước Model máy (Tùy chọn)</label>
                    <select x-model="batchQrForm.machine_model_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium">
                        <option value="">-- Để trống (KTV sẽ chọn khi lắp đặt) --</option>
                        <template x-for="m in machineModels" :key="m.id">
                            <option :value="m.id" x-text="m.name + ' (' + (m.brand || 'Đại Sơn') + ')'"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Thời hạn bảo hành mặc định</label>
                    <div class="flex items-center gap-2">
                        <input type="number" x-model.number="batchQrForm.warranty_months" min="1" max="120"
                               class="w-24 px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-center focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <span class="text-slate-500 font-semibold">tháng (2 năm)</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú lô tem</label>
                    <input type="text" x-model="batchQrForm.admin_notes" placeholder="VD: Lô tem xuất kho tháng 10/2026..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium">
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal('batchQrModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Tạo Ngay Lô Tem QR</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

