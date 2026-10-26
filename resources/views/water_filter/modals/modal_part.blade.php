    <!-- ============================================================== -->
    <!-- MODAL 5: THÊM / SỬA PHỤ TÙNG KHO                               -->
    <!-- ============================================================== -->
    <div x-show="modals.partModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('partModal')" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-teal-600 to-emerald-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-lg"></i>
                    <h3 class="font-bold text-base" x-text="partForm.id ? 'CẬP NHẬT PHỤ TÙNG' : 'THÊM MỚI PHỤ TÙNG KHO'"></h3>
                </div>
                <button @click="closeModal('partModal')" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="savePartSubmit()" class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã phụ tùng <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="partForm.code" required placeholder="LOI-RO-100G" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold uppercase">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Đơn vị tính <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="partForm.unit" required placeholder="Cái, Lõi, Bộ, Mét..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tên phụ tùng linh kiện <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="partForm.name" required placeholder="Màng RO Filmtec DOW 100G..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phân loại <span class="text-rose-500">*</span></label>
                        <select x-model="partForm.category" required class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="Lõi lọc thô">Lõi lọc thô (1-2-3)</option>
                            <option value="Màng RO & Lọc tinh">Màng RO & Lọc tinh</option>
                            <option value="Lõi khoáng chức năng">Lõi khoáng chức năng</option>
                            <option value="Bơm & Nguồn">Bơm & Nguồn</option>
                            <option value="Van & Điện từ">Van & Điện từ</option>
                            <option value="Bình áp & Vòi">Bình áp & Vòi</option>
                            <option value="Cút nối & Dây">Cút nối & Dây dẫn</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Vị trí ngăn/kệ kho</label>
                        <input type="text" x-model="partForm.location" placeholder="Kệ A1-02..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Giá nhập kho (đ) <span class="text-rose-500">*</span></label>
                        <input type="number" x-model="partForm.in_price" required min="0" step="1000" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Giá xuất / Giá bán (đ) <span class="text-rose-500">*</span></label>
                        <input type="number" x-model="partForm.out_price" required min="0" step="1000" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold text-teal-800">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tồn kho ban đầu</label>
                        <input type="number" x-model="partForm.stock_qty" min="0" :disabled="partForm.id" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold" :class="partForm.id ? 'bg-slate-100 text-slate-400' : ''">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ngưỡng báo động tồn tối thiểu</label>
                        <input type="number" x-model="partForm.min_stock_alert" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none font-bold text-rose-600">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mô tả / Thông số kỹ thuật</label>
                    <textarea x-model="partForm.description" rows="2" placeholder="Quy cách, áp lực chịu đựng, thời hạn thay..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal('partModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu phụ tùng</button>
                </div>
            </form>
        </div>
    </div>

