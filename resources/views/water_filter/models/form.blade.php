@extends('layouts.app')

@section('title', ($model ? 'Cập Nhật Model: ' . $model->name : 'Thêm Mới Model Máy Lọc Nước') . ' | Đại Sơn ERP')

@section('content')
<div class="w-full space-y-4 pb-12" x-data="modelFormApp()">

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ url('/water-filter?tab=models') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-cyan-50 text-slate-600 hover:text-cyan-700 flex items-center justify-center transition shadow-2xs cursor-pointer" title="Quay lại danh sách Model">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-800 tracking-tight" x-text="isEdit ? 'CẬP NHẬT MODEL MÁY LỌC NƯỚC' : 'THÊM MỚI MODEL MÁY LỌC NƯỚC'"></h1>
                    @if($model)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-cyan-100 text-cyan-800 border border-cyan-200">
                            {{ $model->code }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Model mới
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Cấu hình thông số kỹ thuật, giá niêm yết và danh sách phụ tùng / lõi lọc định hình mặc định cho máy
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <a href="{{ url('/water-filter?tab=models') }}" class="flex-1 md:flex-none px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 transition text-xs cursor-pointer text-center">
                <i class="fa-solid fa-xmark mr-1"></i> Hủy bỏ
            </a>
            <button @click="submitForm()" type="button" :disabled="isSubmitting" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-sky-600 hover:from-cyan-700 hover:to-sky-700 text-white font-bold shadow-md shadow-cyan-600/20 transition text-xs cursor-pointer text-center disabled:opacity-50">
                <i class="fa-solid fa-floppy-disk text-sm" x-show="!isSubmitting"></i>
                <i class="fa-solid fa-circle-notch fa-spin text-sm" x-show="isSubmitting" x-cloak></i>
                <span x-text="isSubmitting ? 'Đang lưu...' : (isEdit ? 'Lưu thay đổi Model' : 'Hoàn tất & Tạo Model')"></span>
            </button>
        </div>
    </div>

    <!-- MAIN FORM GRID (2 COLUMNS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 text-xs">

        <!-- COLUMN 1: THÔNG TIN CƠ BẢN MODEL (4 COLS) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 font-extrabold text-slate-800 text-sm">
                    <i class="fa-solid fa-faucet-drip text-cyan-600"></i>
                    <span>Thông tin Model Máy Lọc</span>
                </div>

                <!-- Mã Model -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mã Model máy <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="form.code" required placeholder="vd: KG100HA, KRF-612..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none font-bold uppercase text-slate-800 text-xs">
                </div>

                <!-- Thương hiệu -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Thương hiệu / Hãng <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="form.brand" list="brandList" required placeholder="Kangaroo, Karofi, Sunhouse, AO Smith..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none font-semibold text-slate-800 text-xs">
                    <datalist id="brandList">
                        <option value="Karofi"></option>
                        <option value="Kangaroo"></option>
                        <option value="Sunhouse"></option>
                        <option value="AO Smith"></option>
                        <option value="Panasonic"></option>
                        <option value="Mutosi"></option>
                        <option value="Korihome"></option>
                    </datalist>
                </div>

                <!-- Tên Model -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tên Model máy lọc nước <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="form.name" required placeholder="vd: Máy lọc nước RO Karofi Optimus O-P1319..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none font-bold text-slate-900 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Số cấp lọc -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số cấp lọc (lõi)</label>
                        <input type="number" x-model.number="form.filter_stages" min="1" max="20" placeholder="vd: 9, 10..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none font-bold text-center text-slate-800 text-xs">
                    </div>

                    <!-- Kiểu dáng -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kiểu dáng</label>
                        <input type="text" x-model="form.type" list="typeList" placeholder="Tủ đứng, để bàn, lắp âm..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none font-semibold text-slate-800 text-xs">
                        <datalist id="typeList">
                            <option value="Tủ đứng"></option>
                            <option value="Để bàn mini"></option>
                            <option value="Lắp gầm chậu rửa"></option>
                            <option value="Lắp âm tủ bếp"></option>
                            <option value="Nóng Lạnh RO"></option>
                            <option value="Bán công nghiệp"></option>
                            <option value="Treo tường"></option>
                        </datalist>
                    </div>
                </div>

                <!-- Giá bán niêm yết -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Giá bán niêm yết (đ)</label>
                    <div class="relative">
                        <input type="text" inputmode="numeric" x-money="form.standard_price" placeholder="0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none font-extrabold text-cyan-800 text-sm pr-9">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">đ</span>
                    </div>
                </div>

                <!-- Mô tả thông số kỹ thuật -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mô tả thông số kỹ thuật</label>
                    <textarea x-model="form.description" rows="4" placeholder="Công nghệ RO vortex, màng Filmtec 100GPD, bình áp thép 10L, kích thước..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:outline-none font-medium text-slate-700 text-xs"></textarea>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: THIẾT LẬP PHỤ TÙNG & LÕI LỌC MẶC ĐỊNH (8 COLS) -->
        <div class="lg:col-span-8 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </span>
                        <div>
                            <h2 class="font-extrabold text-slate-800 text-sm uppercase tracking-wide">Phụ tùng & Linh kiện định hình Model</h2>
                            <p class="text-[11px] text-slate-400 font-normal">Thiết lập trước danh sách lõi lọc, màng lọc, linh kiện mang theo khi lắp đặt và bảo dưỡng</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="autoGenerateStages()" class="px-3 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs border border-amber-200 flex items-center gap-1.5 transition cursor-pointer shadow-2xs" title="Tự động sinh các lõi theo số cấp lọc">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-600"></i>
                            <span>Tạo theo số cấp lọc</span>
                        </button>
                        <button type="button" @click="addPart()" class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-2xs flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fa-solid fa-plus"></i>
                            <span>Thêm phụ tùng</span>
                        </button>
                    </div>
                </div>

                <!-- Table of Default Parts -->
                <div class="border border-slate-200 rounded-xl overflow-visible shadow-2xs min-h-[350px]">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                                <th class="py-3 px-2 text-center w-10 text-slate-400">#</th>
                                <th class="py-3 px-3 min-w-[280px]">Phụ tùng / Linh kiện <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-2 text-center w-16">SL</th>
                                <th class="py-3 px-2 text-center w-28">Hạn thay</th>
                                <th class="py-3 px-3 min-w-[150px]">Ghi chú</th>
                                <th class="py-3 px-2 text-center w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <template x-if="!form.default_parts || form.default_parts.length === 0">
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 bg-slate-50/30">
                                        <i class="fa-solid fa-boxes-packing text-3xl mb-2 text-slate-300 block"></i>
                                        Chưa có phụ tùng nào được gán cho model máy này.<br>
                                        <span class="text-xs text-teal-600 cursor-pointer font-bold hover:underline" @click="autoGenerateStages()">Bấm vào đây để tự động tạo theo số cấp lọc</span> hoặc bấm <strong>+ Thêm phụ tùng</strong>.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(p, pIdx) in form.default_parts" :key="pIdx">
                                <tr class="hover:bg-slate-50/70 transition" :class="activeDropdownIdx === pIdx ? 'relative z-30' : 'relative z-0'">
                                    <!-- STT -->
                                    <td class="py-3 px-2 align-middle text-center font-bold text-slate-400 text-xs" x-text="pIdx + 1"></td>

                                    <!-- Phụ tùng / Linh kiện (1 ô duy nhất: chọn từ kho hoặc gõ tìm/nhập tự do) -->
                                    <td class="py-2.5 px-3 align-top">
                                        <div class="relative" @click.away="if (activeDropdownIdx === pIdx) activeDropdownIdx = null">
                                            <div class="relative">
                                                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-[11px]"></i>
                                                <input 
                                                    type="text" 
                                                    x-model="p.part_name" 
                                                    @focus="if (!p.part_id) activeDropdownIdx = pIdx" 
                                                    @click="if (!p.part_id) activeDropdownIdx = pIdx"
                                                    @input="activeDropdownIdx = pIdx; onTypePartName(pIdx, $event.target.value)"
                                                    required 
                                                    placeholder="Chọn hoặc gõ tên linh kiện..." 
                                                    class="w-full pl-7 pr-7 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-slate-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none bg-white placeholder-slate-400"
                                                >
                                                <button 
                                                    type="button" 
                                                    x-show="p.part_name || p.part_id" 
                                                    @click="clearPart(pIdx)" 
                                                    class="absolute right-2 top-1.5 text-slate-400 hover:text-rose-500 p-0.5 cursor-pointer" 
                                                    title="Xóa lựa chọn"
                                                >
                                                    <i class="fa-solid fa-xmark text-xs"></i>
                                                </button>
                                            </div>

                                            <!-- Dropdown gợi ý linh kiện -->
                                            <div 
                                                x-show="activeDropdownIdx === pIdx && !p.part_id && filterParts(p.part_name, pIdx).length > 0" 
                                                x-cloak 
                                                class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-56 overflow-y-auto divide-y divide-slate-100"
                                            >
                                                <template x-for="item in filterParts(p.part_name, pIdx)" :key="item.id">
                                                    <div 
                                                        @click="selectPart(pIdx, item); activeDropdownIdx = null" 
                                                        class="p-2.5 hover:bg-cyan-50 cursor-pointer flex items-center justify-between gap-2 transition"
                                                        :class="p.part_id == item.id ? 'bg-cyan-50 font-bold' : 'text-slate-700'"
                                                    >
                                                        <div class="truncate flex-1 min-w-0">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-600 shrink-0" x-text="item.code"></span>
                                                                <span class="font-bold text-slate-800 truncate" x-text="item.name"></span>
                                                            </div>
                                                            <div class="text-[10px] text-slate-400 mt-0.5" x-show="item.category">
                                                                <span x-text="item.category"></span>
                                                            </div>
                                                        </div>
                                                        <i x-show="p.part_id == item.id" class="fa-solid fa-check text-cyan-600 text-xs ml-1 shrink-0"></i>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </td>


                                    <!-- Số lượng -->
                                    <td class="py-2.5 px-2 align-top text-center">
                                        <input type="number" x-model.number="p.quantity" min="1" max="99" class="w-12 px-1 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-center focus:border-cyan-500 focus:outline-none bg-white">
                                    </td>

                                    <!-- Hạn thay định kỳ (tháng) -->
                                    <td class="py-2.5 px-2 align-top text-center">
                                        <div class="flex items-center gap-1 justify-center">
                                            <input type="number" x-model.number="p.lifespan_months" min="1" max="120" placeholder="6" class="w-14 px-1 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-center text-teal-700 focus:border-cyan-500 focus:outline-none bg-white">
                                            <span class="text-[10px] text-slate-400 font-bold">th</span>
                                        </div>
                                    </td>

                                    <!-- Ghi chú -->
                                    <td class="py-2.5 px-3 align-top">
                                        <input type="text" x-model="p.note" placeholder="Lưu ý khi thay..." class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs font-medium text-slate-600 focus:border-cyan-500 focus:outline-none bg-white">
                                    </td>

                                    <!-- Nút xóa -->
                                    <td class="py-2.5 px-2 align-top text-center">
                                        <button type="button" @click="removePart(pIdx)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer" title="Xóa dòng này">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Bottom stats bar -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                    <span class="text-slate-600">
                        Tổng số phụ tùng cấu hình: <strong class="text-teal-700 font-bold" x-text="form.default_parts ? form.default_parts.length : 0"></strong>
                    </span>
                    <button type="button" @click="addPart()" class="text-teal-700 hover:text-teal-800 font-bold flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Thêm phụ tùng khác</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- SCRIPT LOGIC FOR MODEL FORM -->
<script>
function modelFormApp() {
    const existingModel = @json($model);
    const partsList = @json($parts);
    let initialParts = [];
    if (existingModel && existingModel.default_parts) {
        if (Array.isArray(existingModel.default_parts)) {
            initialParts = existingModel.default_parts;
        } else if (typeof existingModel.default_parts === 'string') {
            try { initialParts = JSON.parse(existingModel.default_parts); } catch(e) { initialParts = []; }
        }
    }

    return {
        isEdit: Boolean(existingModel && existingModel.id),
        isSubmitting: false,
        parts: partsList,
        activeDropdownIdx: null,

        form: {
            id: existingModel ? existingModel.id : null,
            code: existingModel ? existingModel.code : '',
            brand: existingModel ? (existingModel.brand || '') : '',
            name: existingModel ? existingModel.name : '',
            filter_stages: existingModel ? (existingModel.filter_stages ?? '') : '',
            type: existingModel ? (existingModel.type || '') : '',
            standard_price: existingModel ? (existingModel.standard_price ?? '') : '',
            description: existingModel ? (existingModel.description || '') : '',
            default_parts: initialParts
        },

        addPart() {
            const nextIdx = this.form.default_parts.length + 1;
            let defaultPos = '';
            if (this.form.filter_stages && nextIdx <= this.form.filter_stages) {
                defaultPos = `Cấp ${nextIdx}`;
            }
            this.form.default_parts.push({
                part_id: '',
                part_code: '',
                part_name: '',
                quantity: 1,
                position: defaultPos,
                lifespan_months: '',
                note: ''
            });
        },

        removePart(idx) {
            this.form.default_parts.splice(idx, 1);
        },

        filterParts(keyword, currentIdx = null) {
            const list = partsList || [];
            // Danh sách ID phụ tùng đã chọn ở các dòng khác
            const selectedIds = (this.form.default_parts || [])
                .map((p, idx) => (idx !== currentIdx ? p.part_id : null))
                .filter(id => Boolean(id));

            return list.filter(p => {
                // Đã chọn ở dòng khác thì không hiển thị nữa
                if (selectedIds.includes(p.id)) return false;

                if (!keyword || !keyword.trim()) return true;
                const q = keyword.toLowerCase().trim();
                return (
                    (p.name && p.name.toLowerCase().includes(q)) ||
                    (p.code && p.code.toLowerCase().includes(q)) ||
                    (p.category && p.category.toLowerCase().includes(q))
                );
            });
        },

        onTypePartName(idx, text) {
            const item = this.form.default_parts[idx];
            if (!item) return;
            item.part_name = text;
            const list = partsList || [];
            const found = list.find(p => p.name.toLowerCase() === text.toLowerCase().trim());
            if (found) {
                item.part_id = found.id;
                item.part_code = found.code;
            } else {
                item.part_id = '';
                item.part_code = '';
            }
        },

        selectPart(idx, part) {
            const item = this.form.default_parts[idx];
            if (!item) return;
            item.part_id = part.id;
            item.part_code = part.code;
            item.part_name = part.name;
            if (!item.position && part.category) {
                item.position = part.category;
            }
            this.activeDropdownIdx = null;
        },

        clearPart(idx) {
            const item = this.form.default_parts[idx];
            if (!item) return;
            item.part_id = '';
            item.part_code = '';
            item.part_name = '';
        },

        autoGenerateStages() {
            let stages = parseInt(this.form.filter_stages);
            if (!stages || isNaN(stages)) {
                stages = 10;
                this.form.filter_stages = 10;
            }
            if (this.form.default_parts && this.form.default_parts.length > 0) {
                if (!confirm(`Tạo lại ${stages} cấp lọc chuẩn sẽ thay thế danh sách phụ tùng hiện tại. Bạn có chắc không?`)) {
                    return;
                }
            }
            const standardStages = [
                { pos: 'Cấp 1', name: 'Lõi lọc số 1 PP 5 Micron', months: 3 },
                { pos: 'Cấp 2', name: 'Lõi lọc số 2 Than hoạt tính OCB-GAC', months: 6 },
                { pos: 'Cấp 3', name: 'Lõi lọc số 3 PP 1 Micron (hoặc CTO)', months: 9 },
                { pos: 'Cấp 4', name: 'Màng lọc RO Purifim / Filmtec', months: 24 },
                { pos: 'Cấp 5', name: 'Lõi số 5 T33 Than hoạt tính gáo dừa', months: 12 },
                { pos: 'Cấp 6', name: 'Lõi số 6 Khoáng đá Mineral', months: 12 },
                { pos: 'Cấp 7', name: 'Lõi số 7 Hồng ngoại Far Infrared', months: 12 },
                { pos: 'Cấp 8', name: 'Lõi số 8 Tourmaline / Hydrogen', months: 12 },
                { pos: 'Cấp 9', name: 'Lõi số 9 Nano Bạc diệt khuẩn', months: 12 },
                { pos: 'Cấp 10', name: 'Lõi số 10 Bổ sung kiềm Alkaline', months: 12 },
            ];

            const newParts = [];
            for (let i = 1; i <= stages; i++) {
                const std = standardStages[i - 1] || { pos: `Cấp ${i}`, name: `Lõi lọc số ${i}`, months: 12 };
                const matchInKho = partsList.find(p => 
                    p.name.toLowerCase().includes(`số ${i}`) || 
                    (i === 4 && (p.name.toLowerCase().includes('ro') || p.name.toLowerCase().includes('màng')))
                );

                newParts.push({
                    part_id: matchInKho ? matchInKho.id : '',
                    part_code: matchInKho ? matchInKho.code : '',
                    part_name: matchInKho ? matchInKho.name : std.name,
                    quantity: 1,
                    position: std.pos,
                    lifespan_months: std.months,
                    note: ''
                });
            }
            this.form.default_parts = newParts;
        },

        async submitForm() {
            if (!this.form.code || !this.form.name) {
                alert('Vui lòng nhập Mã model và Tên model máy!');
                return;
            }

            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/api/water-filter/machine-models', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Lưu model máy lọc nước thành công!');
                    window.location.href = '{{ url("/water-filter?tab=models") }}';
                } else {
                    alert(data.message || 'Lỗi lưu model máy');
                }
            } catch (e) {
                alert('Có lỗi kết nối đến máy chủ.');
            } finally {
                this.isSubmitting = false;
            }
        }
    };
}
</script>
@endsection
