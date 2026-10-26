        <!-- ============================================================== -->
        <!-- TAB 4: DANH MỤC MODEL MÁY LỌC NƯỚC                             -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'models'" class="p-4 sm:p-6 space-y-4">
            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" x-model="modelSearch" @input.debounce.300ms="fetchMachineModels()" placeholder="Tìm theo tên model, mã, thương hiệu..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 text-xs">
                    </div>
                    <select x-model="modelBrand" @change="fetchMachineModels()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 text-xs font-bold text-slate-700">
                        <option value="">-- Tất cả hãng máy --</option>
                        <option value="Karofi">Karofi</option>
                        <option value="Kangaroo">Kangaroo</option>
                        <option value="Sunhouse">Sunhouse</option>
                        <option value="AO Smith">AO Smith</option>
                        <option value="Panasonic">Panasonic</option>
                    </select>
                </div>
                <a href="{{ url('/water-filter/models/create') }}" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>+ Thêm Model Máy Mới</span>
                </a>
            </div>

            <!-- Table of Machine Models -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Mã Model</th>
                            <th class="py-3 px-3">Thương hiệu</th>
                            <th class="py-3 px-3">Tên Model máy lọc nước</th>
                            <th class="py-3 px-3 text-center">Số cấp lọc</th>
                            <th class="py-3 px-3">Phụ tùng định hình</th>
                            <th class="py-3 px-3 text-center">Kiểu dáng</th>
                            <th class="py-3 px-3 text-right">Giá niêm yết</th>
                            <th class="py-3 px-3">Đặc tính kỹ thuật</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="machineModels.length === 0">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-faucet-drip text-3xl mb-2 text-slate-300 block"></i>
                                    Chưa có model máy lọc nước nào
                                </td>
                            </tr>
                        </template>
                        <template x-for="item in machineModels" :key="item.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 font-bold text-cyan-800" x-text="item.code"></td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800" x-text="item.brand || 'Khác'"></span>
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-800" x-text="item.name"></td>
                                <td class="py-3 px-3 text-center font-bold text-slate-700" x-text="(item.filter_stages || 10) + ' cấp lọc'"></td>
                                <td class="py-3 px-3">
                                    <template x-if="item.default_parts && item.default_parts.length > 0">
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-100 text-teal-800 border border-teal-200">
                                                <i class="fa-solid fa-boxes-stacked text-[9px]"></i>
                                                <span x-text="item.default_parts.length + ' phụ tùng'"></span>
                                            </span>
                                            <div class="text-[10px] text-slate-500 truncate max-w-[170px]" :title="item.default_parts.map(p => (p.position ? p.position + ': ' : '') + p.part_name).join(', ')">
                                                <span x-text="item.default_parts.map(p => (p.position ? p.position + ': ' : '') + p.part_name).slice(0, 2).join(', ') + (item.default_parts.length > 2 ? '...' : '')"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!item.default_parts || item.default_parts.length === 0">
                                        <span class="text-slate-400 text-[11px] italic">Chưa thiết lập</span>
                                    </template>
                                </td>
                                <td class="py-3 px-3 text-center text-slate-600 font-semibold" x-text="item.type || 'Tủ đứng'"></td>
                                <td class="py-3 px-3 text-right font-extrabold text-cyan-800" x-text="item.standard_price ? window.formatCurrency(item.standard_price) : '-'"></td>
                                <td class="py-3 px-3 text-slate-500 min-w-[200px]" :title="item.description" x-text="item.description || '-'"></td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a :href="'{{ url('/water-filter/models') }}/' + item.id + '/edit'" title="Sửa model" class="p-1.5 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-700 transition cursor-pointer inline-flex items-center justify-center">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button @click="deleteMachineModel(item.id)" title="Xóa" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer">
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

