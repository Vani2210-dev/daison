    <!-- ============================================================== -->
    <!-- MODAL 6: NHẬP KHO PHỤ TÙNG TỪ NHÀ CUNG CẤP                    -->
    <!-- ============================================================== -->
    <div x-show="modals.importModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('importModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-import text-lg"></i>
                    <h3 class="font-bold text-base">NHẬP KHO PHỤ TÙNG TỪ NCC</h3>
                </div>
                <button @click="closeModal('importModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="submitImportStock()" class="p-6 space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Chọn linh kiện / phụ tùng <span class="text-rose-500">*</span></label>
                    <select x-model="importForm.part_id" @change="onSelectImportPart($event.target.value)" required class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Chọn phụ tùng cần nhập --</option>
                        <template x-for="p in parts" :key="p.id">
                            <option :value="p.id" x-text="p.code + ' - ' + p.name + ' (Tồn: ' + p.stock_qty + ')'"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số lượng nhập <span class="text-rose-500">*</span></label>
                        <input type="number" x-model.number="importForm.quantity" required min="1" placeholder="10..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-center">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Đơn giá nhập mới (đ) <span class="text-rose-500">*</span></label>
                        <input type="number" x-model.number="importForm.in_price" required min="0" step="1000" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-right">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nhà cung cấp / Nguồn hàng</label>
                    <input type="text" x-model="importForm.supplier" placeholder="Công ty Karofi, Kangaro, Toray Japan..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú phiếu nhập</label>
                    <input type="text" x-model="importForm.note" placeholder="Nhập bổ sung đợt tháng..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('importModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Xác nhận nhập kho</button>
                </div>
            </form>
        </div>
    </div>

