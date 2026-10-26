        <!-- ============================================================== -->
        <!-- TAB 2: TIẾP NHẬN SỬA CHỮA & BẢO HÀNH                          -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'repairs'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="repairSearch" @input.debounce.300ms="fetchRepairs()" placeholder="Tìm theo mã phiếu, khách, SĐT, lỗi..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs">
                    </div>
                    <select x-model="repairStatus" @change="fetchRepairs()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs font-medium">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="received">Mới tiếp nhận</option>
                        <option value="inspecting">Đang kiểm tra</option>
                        <option value="waiting_parts">Chờ linh kiện</option>
                        <option value="repairing">Đang sửa chữa</option>
                        <option value="completed">Đã sửa xong</option>
                        <option value="delivered">Đã trả khách</option>
                        <option value="cancelled">Hủy sửa</option>
                    </select>
                    <select x-model="repairWarrantyType" @change="fetchRepairs()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs font-medium">
                        <option value="">-- Tất cả loại hình --</option>
                        <option value="in_warranty">Còn bảo hành</option>
                        <option value="out_warranty">Sửa tính phí</option>
                    </select>
                </div>
                <button type="button" @click="openRepairModal()" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tiếp nhận sửa chữa</span>
                </button>
            </div>

            <!-- Table of Repairs -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Mã phiếu</th>
                            <th class="py-3 px-3">Khách hàng</th>
                            <th class="py-3 px-3">Model máy lọc</th>
                            <th class="py-3 px-3">Hiện tượng lỗi</th>
                            <th class="py-3 px-3">Linh kiện thay</th>
                            <th class="py-3 px-3">KTV phụ trách</th>
                            <th class="py-3 px-3 text-right">Chi phí</th>
                            <th class="py-3 px-3 text-center">Trạng thái</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="repairs.length === 0">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có phiếu sửa chữa nào
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in repairs" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-bold text-amber-700" x-text="item.code"></td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800" x-text="item.customer_name"></div>
                                    <div class="text-[11px] text-slate-500" x-text="item.customer_phone"></div>
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold mt-0.5" :class="item.repair_type === 'at_shop' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'" x-text="item.repair_type === 'at_shop' ? 'Tại trạm' : 'Tận nhà'"></span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800" x-text="item.machine_model"></div>
                                    <div class="text-[10px] font-bold" :class="item.warranty_type === 'in_warranty' ? 'text-emerald-600' : 'text-slate-500'" x-text="item.warranty_type === 'in_warranty' ? 'Bảo hành chính hãng' : 'Sửa chữa có phí'"></div>
                                </td>
                                <td class="py-3 px-3 text-slate-600 min-w-[220px] text-[11px]" :title="item.symptom">
                                    <div class="line-clamp-2" x-text="item.symptom"></div>
                                </td>
                                <td class="py-3 px-3">
                                    <template x-if="item.items && item.items.length > 0">
                                        <div class="space-y-0.5">
                                            <template x-for="part in item.items" :key="part.id">
                                                <div class="text-[10px] text-slate-700 flex items-center gap-1">
                                                    <i class="fa-solid fa-circle text-[4px] text-slate-400"></i>
                                                    <span x-text="part.quantity + 'x ' + part.part_name"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!item.items || item.items.length === 0">
                                        <span class="text-slate-400 text-[11px]">Chưa thay</span>
                                    </template>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700" x-text="item.technician_name || 'Chưa gán'"></span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="font-bold text-slate-800" x-text="window.formatCurrency(item.total_amount)"></div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded" :class="item.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : (item.payment_status === 'warranty_free' ? 'bg-sky-100 text-sky-800' : 'bg-rose-100 text-rose-800')" x-text="item.payment_status === 'paid' ? 'Đã thu' : (item.payment_status === 'warranty_free' ? 'Miễn phí BH' : 'Chưa thu')"></span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase inline-block" :class="getRepairStatusBadge(item.status)" x-text="getRepairStatusText(item.status)"></span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openRepairUpdateModal(item)" title="Chẩn đoán & Cập nhật tiến độ" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer">
                                            <i class="fa-solid fa-list-check"></i>
                                        </button>
                                        <button @click="editRepair(item)" title="Sửa phiếu" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="deleteRepair(item.id)" title="Xóa" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer">
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

