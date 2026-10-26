    <!-- ============================================================== -->
    <!-- MODAL 1: TẠO / SỬA ĐƠN LẮP ĐẶT                                -->
    <!-- ============================================================== -->
    <div x-show="modals.installModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('installModal')" class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 bg-gradient-to-r from-sky-600 to-cyan-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-screwdriver-wrench text-lg"></i>
                    <h3 class="font-bold text-base" x-text="installForm.id ? 'CẬP NHẬT ĐƠN LẮP ĐẶT' : 'TẠO MỚI ĐƠN LẮP ĐẶT MÁY LỌC'"></h3>
                </div>
                <button @click="closeModal('installModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="saveInstallationSubmit()" class="p-6 overflow-y-auto space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Họ tên khách hàng / Đại lý <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="installForm.customer_name" required placeholder="Nguyễn Văn A" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số điện thoại <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="installForm.customer_phone" required placeholder="0912..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Địa chỉ lắp đặt <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="installForm.customer_address" required placeholder="Số nhà, đường, thôn/xã, huyện..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tên / Model máy lọc cần lắp <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="installForm.machine_model" required placeholder="Máy lọc RO Kangaroo Hydrogen..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Serial máy (nếu có)</label>
                        <input type="text" x-model="installForm.machine_serial" placeholder="KG-2026-..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kỹ thuật viên phụ trách</label>
                        <select x-model="installForm.technician_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-medium">
                            <option value="">-- Chưa chỉ định --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->department ?? 'KTV' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Lịch hẹn lắp đặt</label>
                        <input type="datetime-local" x-model="installForm.appointment_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <!-- Phụ kiện xuất kèm mang đi lắp -->
                <div class="border-t border-slate-200 pt-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-700">Phụ tùng, vật tư mang theo kèm máy:</span>
                        <button type="button" @click="addInstallItemRow()" class="px-2.5 py-1 rounded-lg bg-sky-100 hover:bg-sky-200 text-sky-800 font-bold transition">
                            <i class="fa-solid fa-plus"></i> Thêm vật tư
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, idx) in installForm.items" :key="idx">
                            <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                                <select @change="onSelectInstallPart(idx, $event.target.value)" class="w-1/2 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs font-medium">
                                    <option value="">-- Chọn phụ tùng từ kho --</option>
                                    <template x-for="p in parts" :key="p.id">
                                        <option :value="p.id" :selected="p.id == item.part_id" x-text="p.name + ' (Tồn: ' + p.stock_qty + ')'"></option>
                                    </template>
                                </select>
                                <input type="number" x-model.number="item.quantity" min="1" placeholder="SL" class="w-20 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-center font-bold">
                                <input type="number" x-model.number="item.unit_price" min="0" placeholder="Đơn giá" class="w-28 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-right font-bold">
                                <button type="button" @click="removeInstallItemRow(idx)" class="text-rose-500 hover:text-rose-700 p-1"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú yêu cầu của khách</label>
                    <textarea x-model="installForm.customer_notes" rows="2" placeholder="Vị trí lắp, yêu cầu đặc biệt..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('installModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu đơn lắp đặt</button>
                </div>
            </form>
        </div>
    </div>

