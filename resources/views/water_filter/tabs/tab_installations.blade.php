        <!-- ============================================================== -->
        <!-- TAB 1: DANH SÁCH ĐƠN ĐI LẮP ĐẶT                               -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'installations'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="installSearch" @input.debounce.300ms="fetchInstallations()" placeholder="Tìm theo mã đơn, khách, SĐT, máy lọc..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs">
                    </div>
                    <select x-model="installStatus" @change="fetchInstallations()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs font-bold text-slate-700">
                        <option value="">-- Tất cả trạng thái đơn --</option>
                        @foreach($statuses as $stKey => $stVal)
                            <option value="{{ $stKey }}">{{ $stVal['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ url('/water-filter/installations/create') }}" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>+ Lên đơn lắp đặt mới</span>
                </a>
            </div>

            <!-- Table of Installations -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Mã đơn</th>
                            <th class="py-3 px-3">Khách hàng & SĐT</th>
                            <th class="py-3 px-3">Địa chỉ lắp đặt</th>
                            <th class="py-3 px-3">Model máy lọc</th>
                            <th class="py-3 px-3">Kỹ thuật viên</th>
                            <th class="py-3 px-3">Lịch hẹn</th>
                            <th class="py-3 px-3 text-right">Tổng thu</th>
                            <th class="py-3 px-3 text-center">Trạng thái</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="installations.length === 0">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có đơn lắp đặt nào phù hợp
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in installations" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-bold text-sky-700" x-text="item.code"></td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800" x-text="item.customer_name"></div>
                                    <a :href="'tel:' + item.customer_phone" class="text-sky-600 hover:underline flex items-center gap-1 text-[11px] mt-0.5">
                                        <i class="fa-solid fa-phone text-[9px]"></i>
                                        <span x-text="item.customer_phone"></span>
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-slate-600 min-w-[200px]" :title="item.customer_address" x-text="item.customer_address"></td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800" x-text="item.machine_model"></span>
                                    <div x-show="item.items && item.items.length > 0" class="text-[10px] text-teal-600 font-semibold mt-0.5">
                                        + <span x-text="item.items.length"></span> phụ kiện kèm
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700" x-text="item.technician_name || 'Chưa phân công'"></span>
                                </td>
                                <td class="py-3 px-3 text-slate-500 text-[11px]" x-text="formatDateTime(item.appointment_date)"></td>
                                <td class="py-3 px-3 text-right font-bold text-slate-800" x-text="window.formatCurrency(item.total_amount)"></td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase inline-block" :class="getInstallStatusBadge(item.status)" x-text="getInstallStatusText(item.status)"></span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Quick complete / TDS inspect -->
                                        <button @click="openInspectModal(item)" title="Nghiệm thu & Trừ phụ tùng" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer">
                                            <i class="fa-solid fa-check-double"></i>
                                        </button>
                                        <!-- Edit (Full Page) -->
                                        <a :href="'/water-filter/installations/' + item.id + '/edit'" title="Sửa đơn lắp đặt (Trang riêng)" class="p-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 transition cursor-pointer inline-flex items-center justify-center">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <!-- Delete -->
                                        <button @click="deleteInstallation(item.id)" title="Xóa" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer">
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

