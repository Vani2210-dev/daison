    <!-- ============================================================== -->
    <!-- MODAL 3: TẠO / SỬA PHIẾU TIẾP NHẬN SỬA CHỮA                   -->
    <!-- ============================================================== -->
    <div x-show="modals.repairModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('repairModal')" class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-wrench text-lg"></i>
                    <h3 class="font-bold text-base" x-text="repairForm.id ? 'CẬP NHẬT PHIẾU SỬA CHỮA' : 'TIẾP NHẬN SỬA CHỮA & BẢO HÀNH'"></h3>
                </div>
                <button @click="closeModal('repairModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="saveRepairSubmit()" class="p-6 overflow-y-auto space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Họ tên khách hàng <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="repairForm.customer_name" required placeholder="Nhập họ và tên..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số điện thoại <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="repairForm.customer_phone" required placeholder="Nhập số điện thoại..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hình thức sửa chữa</label>
                        <select x-model="repairForm.repair_type" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="at_shop">Khách mang tới trạm</option>
                            <option value="at_home">Đến tận nhà khách</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chế độ bảo hành</label>
                        <select x-model="repairForm.warranty_type" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="out_warranty">Sửa tính phí</option>
                            <option value="in_warranty">Còn bảo hành (Miễn phí)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">KTV tiếp nhận / sửa</label>
                        <select x-model="repairForm.technician_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="">-- Chưa chỉ định --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Model / Hãng máy lọc <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="repairForm.machine_model" required placeholder="Karofi Optimus, Kangaroo, Sunhouse..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số Serial / Mã máy</label>
                        <input type="text" x-model="repairForm.machine_serial" placeholder="KRF-..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Hiện tượng hư hỏng / Khách báo lỗi <span class="text-rose-500">*</span></label>
                    <textarea x-model="repairForm.symptom" required rows="2" placeholder="Máy kêu tạch tạch, nước thải chảy liên tục, nước có mùi, rò rỉ nước..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Linh kiện phụ tùng thay thế -->
                <div class="border-t border-slate-200 pt-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-700">Linh kiện phụ tùng thay thế:</span>
                        <button type="button" @click="addRepairItemRow()" class="px-2.5 py-1 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold transition">
                            <i class="fa-solid fa-plus"></i> Thêm linh kiện
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(item, idx) in repairForm.items" :key="idx">
                            <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200">
                                <select @change="onSelectRepairPart(idx, $event.target.value)" class="w-1/2 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs font-medium">
                                    <option value="">-- Chọn phụ tùng từ kho --</option>
                                    <template x-for="p in parts" :key="p.id">
                                        <option :value="p.id" :selected="p.id == item.part_id" x-text="p.name + ' (Tồn: ' + p.stock_qty + ')'"></option>
                                    </template>
                                </select>
                                <input type="number" x-model.number="item.quantity" min="1" placeholder="SL" class="w-16 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-center font-bold">
                                <input type="number" x-model.number="item.unit_price" min="0" placeholder="Giá" class="w-24 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-right font-bold">
                                <label class="flex items-center gap-1 text-[11px] text-emerald-700 font-bold select-none whitespace-nowrap">
                                    <input type="checkbox" x-model="item.is_warranty" class="rounded text-emerald-600">
                                    <span>Miễn phí BH</span>
                                </label>
                                <button type="button" @click="removeRepairItemRow(idx)" class="text-rose-500 hover:text-rose-700 p-1"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tiền công thợ (đ)</label>
                        <input type="number" x-model="repairForm.labor_fee" min="0" step="10000" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Giảm giá / Chiết khấu (đ)</label>
                        <input type="number" x-model="repairForm.discount" min="0" step="5000" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('repairModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu phiếu sửa chữa</button>
                </div>
            </form>
        </div>
    </div>

