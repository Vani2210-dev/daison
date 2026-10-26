        <!-- ============================================================== -->
        <!-- TAB 5: NHẬT KÝ NHẬP - XUẤT KHO PHỤ TÙNG                        -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'transactions'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="transSearch" @input.debounce.300ms="fetchTransactions()" placeholder="Tìm theo mã phiếu, tên phụ tùng..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs">
                    </div>
                    <select x-model="transType" @change="fetchTransactions()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs font-medium">
                        <option value="">-- Tất cả loại giao dịch --</option>
                        <option value="import">Nhập kho mới (+)</option>
                        <option value="export_install">Xuất đi lắp đặt (-)</option>
                        <option value="export_repair">Xuất sửa chữa (-)</option>
                        <option value="return">Hoàn kho (+)</option>
                    </select>
                </div>
            </div>

            <!-- Table of Transactions -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Thời gian</th>
                            <th class="py-3 px-3">Mã phiếu liên quan</th>
                            <th class="py-3 px-3">Phụ tùng / Linh kiện</th>
                            <th class="py-3 px-3">Loại giao dịch</th>
                            <th class="py-3 px-3 text-center">Số lượng</th>
                            <th class="py-3 px-3 text-center">Tồn trước</th>
                            <th class="py-3 px-3 text-center">Tồn sau</th>
                            <th class="py-3 px-3">Người thực hiện</th>
                            <th class="py-3 px-3">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="transactions.length === 0">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có giao dịch kho nào
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in transactions" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 text-slate-500 text-[11px]" x-text="formatDateTime(item.created_at)"></td>
                                <td class="py-3 px-3 font-bold text-slate-800" x-text="item.reference_code || '-'"></td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800" x-text="item.part ? item.part.name : 'Phụ tùng #' + item.part_id"></div>
                                    <div class="text-[10px] text-slate-400" x-text="item.part ? item.part.code : ''"></div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="getTransBadge(item.type)" x-text="getTransText(item.type)"></span>
                                </td>
                                <td class="py-3 px-3 text-center font-black" :class="item.quantity > 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="(item.quantity > 0 ? '+' : '') + item.quantity"></td>
                                <td class="py-3 px-3 text-center text-slate-500" x-text="item.balance_before"></td>
                                <td class="py-3 px-3 text-center font-bold text-slate-800" x-text="item.balance_after"></td>
                                <td class="py-3 px-3 text-slate-600" x-text="item.created_by_name || '-'"></td>
                                <td class="py-3 px-3 text-slate-500 min-w-[200px]" :title="item.note" x-text="item.note || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
