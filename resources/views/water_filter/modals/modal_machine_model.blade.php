    <!-- ============================================================== -->
    <!-- MODAL 7: THÊM / SỬA MODEL MÁY LỌC NƯỚC                         -->
    <!-- ============================================================== -->
    <div x-show="modals.machineModelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('machineModelModal')" class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 to-sky-600 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-faucet-drip text-lg"></i>
                    <h3 class="font-bold text-base" x-text="modelForm.id ? 'CẬP NHẬT MODEL MÁY LỌC NƯỚC' : 'THÊM MỚI MODEL MÁY LỌC NƯỚC'"></h3>
                </div>
                <button type="button" @click="closeModal('machineModelModal')" class="text-white/80 hover:text-white cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <!-- Form Body with Scroll -->
            <form @submit.prevent="saveMachineModelSubmit()" class="flex flex-col flex-1 overflow-hidden text-xs">
                <div class="p-6 overflow-y-auto space-y-5 flex-1">
                    <!-- THÔNG TIN CƠ BẢN MODEL -->
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-2 text-cyan-800 font-extrabold text-xs uppercase tracking-wider pb-1 border-b border-cyan-100">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Thông tin cơ bản Model máy</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Mã Model máy <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="modelForm.code" required placeholder="vd: KG100HA, KRF-612..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold uppercase focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Thương hiệu / Hãng <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="modelForm.brand" required placeholder="Kangaroo, Karofi, Sunhouse..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tên Model máy lọc nước <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="modelForm.name" required placeholder="vd: Máy lọc nước RO Karofi Optimus O-P1319..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-semibold focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Số cấp lọc (lõi)</label>
                                <input type="number" x-model.number="modelForm.filter_stages" min="1" max="20" placeholder="vd: 9, 10..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-center focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Kiểu dáng thiết kế</label>
                                <input type="text" x-model="modelForm.type" list="modalTypeList" placeholder="Tủ đứng, để bàn, lắp âm..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-semibold focus:ring-2 focus:ring-cyan-500 focus:outline-none bg-white">
                                <datalist id="modalTypeList">
                                    <option value="Tủ đứng"></option>
                                    <option value="Để bàn mini"></option>
                                    <option value="Lắp gầm chậu rửa"></option>
                                    <option value="Lắp âm tủ bếp"></option>
                                    <option value="Nóng Lạnh RO"></option>
                                    <option value="Bán công nghiệp"></option>
                                    <option value="Treo tường"></option>
                                </datalist>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Giá bán niêm yết (đ)</label>
                                <div class="relative">
                                    <input type="text" inputmode="numeric" x-money="modelForm.standard_price" placeholder="0" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-cyan-800 focus:ring-2 focus:ring-cyan-500 focus:outline-none pr-8">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">đ</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Mô tả thông số kỹ thuật</label>
                            <textarea x-model="modelForm.description" rows="2" placeholder="Công nghệ RO vortex, màng Filmtec, bình áp thép..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none"></textarea>
                        </div>
                    </div>

                    <!-- THIẾT LẬP PHỤ TÙNG & LÕI LỌC MẶC ĐỊNH -->
                    <div class="space-y-3 pt-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-1 border-b border-teal-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </span>
                                <span class="font-extrabold text-slate-800 text-xs uppercase tracking-wide">Phụ tùng & Linh kiện định hình Model</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-100 text-teal-800" x-text="(modelForm.default_parts ? modelForm.default_parts.length : 0) + ' mục'"></span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="autoGenerateModelStages()" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-[11px] border border-amber-200 flex items-center gap-1 transition cursor-pointer" title="Tự động sinh danh sách lõi chuẩn theo số cấp lọc">
                                    <i class="fa-solid fa-wand-magic-sparkles text-amber-600"></i>
                                    <span>Tạo theo số cấp lọc</span>
                                </button>
                                <button type="button" @click="addModelPart()" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] shadow-2xs flex items-center gap-1 transition cursor-pointer">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span>Thêm phụ tùng</span>
                                </button>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-500 italic">
                            💡 Thiết lập sẵn các lõi lọc, màng RO, bơm áp, bình áp... đi kèm model này để khi lên đơn hoặc lập lịch bảo dưỡng định kỳ sẽ tự động nhận diện chính xác.
                        </p>

                        <!-- Bảng danh sách phụ tùng cấu hình -->
                        <div class="border border-slate-200 rounded-xl overflow-visible shadow-2xs bg-white min-h-[300px]">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-600 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                                        <th class="py-2.5 px-2 text-center w-10 text-slate-400">#</th>
                                        <th class="py-2.5 px-2.5 min-w-[260px]">Phụ tùng / Linh kiện <span class="text-rose-500">*</span></th>
                                        <th class="py-2.5 px-2 text-center w-14">SL</th>
                                        <th class="py-2.5 px-2 text-center w-24">Hạn thay</th>
                                        <th class="py-2.5 px-2.5 min-w-[140px]">Ghi chú</th>
                                        <th class="py-2.5 px-2 text-center w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-if="!modelForm.default_parts || modelForm.default_parts.length === 0">
                                        <tr>
                                            <td colspan="6" class="py-6 text-center text-slate-400 bg-slate-50/40">
                                                <i class="fa-solid fa-boxes-packing text-2xl mb-1 text-slate-300 block"></i>
                                                Chưa có phụ tùng mặc định nào cho model này.<br>
                                                <span class="text-[11px] text-teal-600 cursor-pointer font-bold hover:underline" @click="autoGenerateModelStages()">Bấm vào đây để tự động tạo theo số cấp lọc</span> hoặc bấm <strong>+ Thêm phụ tùng</strong>.
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="(p, pIdx) in (modelForm.default_parts || [])" :key="pIdx">
                                        <tr class="hover:bg-slate-50/70 transition" :class="activeModelDropdownIdx === pIdx ? 'relative z-30' : 'relative z-0'">
                                            <!-- STT -->
                                            <td class="py-2.5 px-2 align-middle text-center font-bold text-slate-400 text-xs" x-text="pIdx + 1"></td>

                                            <!-- Phụ tùng / Linh kiện (1 ô duy nhất: chọn từ kho hoặc gõ tìm/nhập tự do) -->
                                            <td class="py-2 px-2.5 align-top">
                                                <div class="relative" @click.away="if (activeModelDropdownIdx === pIdx) activeModelDropdownIdx = null">
                                                    <div class="relative">
                                                        <i class="fa-solid fa-magnifying-glass absolute left-2 top-2 text-slate-400 text-[10px]"></i>
                                                        <input 
                                                            type="text" 
                                                            x-model="p.part_name" 
                                                            @focus="if (!p.part_id) activeModelDropdownIdx = pIdx" 
                                                            @click="if (!p.part_id) activeModelDropdownIdx = pIdx"
                                                            @input="activeModelDropdownIdx = pIdx; onTypeModelPartName(pIdx, $event.target.value)"
                                                            required 
                                                            placeholder="Chọn hoặc gõ tên..." 
                                                            class="w-full pl-6 pr-6 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-slate-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none placeholder-slate-400"
                                                        >
                                                        
                                                    </div>

                                                    <!-- Dropdown danh sách gợi ý linh kiện -->
                                                    <div 
                                                        x-show="activeModelDropdownIdx === pIdx && !p.part_id && filterModelParts(p.part_name, pIdx).length > 0" 
                                                        x-cloak 
                                                        class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-48 overflow-y-auto divide-y divide-slate-100"
                                                    >
                                                        <template x-for="item in filterModelParts(p.part_name, pIdx)" :key="item.id">
                                                            <div 
                                                                @click="selectModelPart(pIdx, item); activeModelDropdownIdx = null" 
                                                                class="p-2 hover:bg-cyan-50 cursor-pointer flex items-center justify-between gap-1.5 transition"
                                                                :class="p.part_id == item.id ? 'bg-cyan-50 font-bold' : 'text-slate-700'"
                                                            >
                                                                <div class="truncate flex-1 min-w-0">
                                                                    <div class="flex items-center gap-1">
                                                                        <span class="px-1 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600 shrink-0" x-text="item.code"></span>
                                                                        <span class="font-bold text-slate-800 truncate" x-text="item.name"></span>
                                                                    </div>
                                                                    <div class="text-[9px] text-slate-400 mt-0.5" x-show="item.category">
                                                                        <span x-text="item.category"></span>
                                                                    </div>
                                                                </div>
                                                                <i x-show="p.part_id == item.id" class="fa-solid fa-check text-cyan-600 text-xs shrink-0"></i>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Số lượng -->
                                            <td class="py-2 px-2 align-top text-center">
                                                <input type="number" x-model.number="p.quantity" min="1" max="99" class="w-12 px-1 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-center focus:border-cyan-500 focus:outline-none">
                                            </td>

                                            <!-- Hạn thay định kỳ (tháng) -->
                                            <td class="py-2 px-2 align-top text-center">
                                                <div class="flex items-center gap-1">
                                                    <input type="number" x-model.number="p.lifespan_months" min="1" max="120" placeholder="6" class="w-12 px-1 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-center text-teal-700 focus:border-cyan-500 focus:outline-none">
                                                    <span class="text-[10px] text-slate-400 font-bold">th</span>
                                                </div>
                                            </td>

                                            <!-- Ghi chú -->
                                            <td class="py-2 px-2.5 align-top">
                                                <input type="text" x-model="p.note" placeholder="Lưu ý khi thay..." class="w-full px-2 py-1.5 border border-slate-300 rounded-lg text-xs font-medium text-slate-600 focus:border-cyan-500 focus:outline-none">
                                            </td>

                                            <!-- Nút xóa -->
                                            <td class="py-2 px-2 align-top text-center">
                                                <button type="button" @click="removeModelPart(pIdx)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer" title="Xóa dòng này">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                    <div class="text-[11px] text-slate-500">
                        <span x-show="modelForm.default_parts && modelForm.default_parts.length > 0">
                            Đã cấu hình <strong class="text-teal-700" x-text="modelForm.default_parts.length"></strong> phụ tùng / lõi lọc cho model này.
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="closeModal('machineModelModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-white cursor-pointer transition">Hủy</button>
                        <button type="submit" class="px-5 py-2 bg-gradient-to-r from-cyan-600 to-sky-600 hover:from-cyan-700 hover:to-sky-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">
                            Lưu Model & Phụ Tùng
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
