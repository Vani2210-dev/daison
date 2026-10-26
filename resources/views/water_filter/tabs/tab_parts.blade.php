        <!-- ============================================================== -->
        <!-- TAB 3: KHO PHỤ TÙNG & PHỤ KIỆN MÁY LỌC                         -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'parts'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="partSearch" @input.debounce.300ms="fetchParts()" placeholder="Tìm theo tên phụ tùng, mã, vị trí..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-xs">
                    </div>
                    <select x-model="partCategory" @change="fetchParts()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-xs font-medium">
                        <option value="">-- Tất cả nhóm phụ tùng --</option>
                        <option value="Lõi lọc thô">Lõi lọc thô (1-2-3)</option>
                        <option value="Màng RO & Lọc tinh">Màng RO & Lọc tinh</option>
                        <option value="Lõi khoáng chức năng">Lõi khoáng chức năng</option>
                        <option value="Bơm & Nguồn">Bơm & Nguồn Adapter</option>
                        <option value="Van & Điện từ">Van & Điện từ</option>
                        <option value="Bình áp & Vòi">Bình áp & Vòi lấy nước</option>
                        <option value="Cút nối & Dây">Cút nối & Dây dẫn PE</option>
                        <option value="Khác">Phụ kiện khác</option>
                    </select>
                    <label class="flex items-center gap-2 bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 cursor-pointer text-xs font-semibold select-none">
                        <input type="checkbox" x-model="partFilterLowStock" @change="fetchParts()" class="rounded text-teal-600 focus:ring-teal-500">
                        <span class="text-rose-600">Chỉ hiển thị sắp hết hàng</span>
                    </label>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="openModal('importModal')" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-file-import"></i>
                        <span>Nhập kho NCC</span>
                    </button>
                    <button type="button" @click="openPartModal()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>Thêm mới phụ tùng</span>
                    </button>
                </div>
            </div>

            <!-- Table of Parts -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Mã phụ tùng</th>
                            <th class="py-3 px-3">Tên linh kiện / Phụ tùng</th>
                            <th class="py-3 px-3">Phân loại</th>
                            <th class="py-3 px-3 text-center">ĐVT</th>
                            <th class="py-3 px-3">Vị trí kho</th>
                            <th class="py-3 px-3 text-right">Giá nhập</th>
                            <th class="py-3 px-3 text-right">Giá xuất</th>
                            <th class="py-3 px-3 text-center">Tồn kho</th>
                            <th class="py-3 px-3 text-center">Cảnh báo tối thiểu</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="parts.length === 0">
                            <tr>
                                <td colspan="10" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có phụ tùng linh kiện nào
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in parts" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition" :class="item.stock_qty <= item.min_stock_alert ? 'bg-rose-50/40' : ''">
                                <td class="py-3 px-3 font-bold text-teal-800" x-text="item.code"></td>
                                <td class="py-3 px-3 font-bold text-slate-800" x-text="item.name"></td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600" x-text="item.category"></span>
                                </td>
                                <td class="py-3 px-3 text-center text-slate-600 font-semibold" x-text="item.unit"></td>
                                <td class="py-3 px-3 text-slate-500" x-text="item.location || '-'"></td>
                                <td class="py-3 px-3 text-right text-slate-600" x-text="window.formatCurrency(item.in_price)"></td>
                                <td class="py-3 px-3 text-right font-bold text-teal-700" x-text="window.formatCurrency(item.out_price)"></td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full font-black text-xs" :class="item.stock_qty <= item.min_stock_alert ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-emerald-100 text-emerald-800'" x-text="item.stock_qty"></span>
                                </td>
                                <td class="py-3 px-3 text-center text-slate-400" x-text="item.min_stock_alert"></td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="quickImport(item)" title="Nhập thêm hàng" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer">
                                            <i class="fa-solid fa-cart-plus"></i>
                                        </button>
                                        <button @click="editPart(item)" title="Sửa" class="p-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 transition cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="deletePart(item.id)" title="Xóa" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

