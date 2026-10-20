@extends('layouts.app')

@section('title', ($installation ? 'Cập Nhật Đơn Lắp Đặt: ' . $installation->code : 'Tạo Mới Đơn Đi Lắp Đặt Máy Lọc') . ' | Đại Sơn ERP')

@section('content')
<div class="w-full space-y-4 pb-12" x-data="installationFormModule()">

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ url('/water-filter?tab=installations') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-700 flex items-center justify-center transition shadow-2xs cursor-pointer" title="Quay lại danh sách">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-800 tracking-tight" x-text="isEdit ? 'CẬP NHẬT ĐƠN ĐI LẮP ĐẶT' : 'LÊN ĐƠN ĐI LẮP ĐẶT MÁY LỌC NƯỚC'"></h1>
                    @if($installation)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-sky-100 text-sky-800 border border-sky-200">
                            {{ $installation->code }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Đơn mới
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Ghi nhận thông tin khách hàng, chọn model máy lọc, điều phối kỹ thuật viên và phụ tùng mang theo
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <a href="{{ url('/water-filter?tab=installations') }}" class="flex-1 md:flex-none px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 transition text-xs cursor-pointer text-center">
                <i class="fa-solid fa-xmark mr-1"></i> Hủy bỏ
            </a>
            <button @click="submitForm()" type="button" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-md shadow-sky-600/20 transition text-xs cursor-pointer text-center">
                <i class="fa-solid fa-floppy-disk text-sm"></i>
                <span x-text="isEdit ? 'Lưu thay đổi' : 'Hoàn tất & Tạo đơn lắp đặt'"></span>
            </button>
        </div>
    </div>

    <!-- MAIN FORM GRID (2 COLUMNS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 text-xs">

        <!-- COLUMN 1: THÔNG TIN KHÁCH HÀNG & ĐỊA ĐIỂM (5 COLS) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 font-extrabold text-slate-800 text-sm">
                    <i class="fa-solid fa-user-tag text-sky-600"></i>
                    <span>Thông tin Khách hàng & Địa điểm</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Họ tên khách hàng / Đại lý <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="form.customer_name" required placeholder="Anh Nguyễn Văn A..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-semibold text-slate-800 text-xs">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Số điện thoại liên hệ <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3.5 top-3 text-slate-400"></i>
                        <input type="text" x-model="form.customer_phone" required placeholder="0912 345 678..." class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Địa chỉ thi công lắp đặt <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-location-dot absolute left-3.5 top-3 text-slate-400"></i>
                        <input type="text" x-model="form.customer_address" required placeholder="Số nhà, đường, xóm/thôn, xã/phường, quận/huyện..." class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-medium text-slate-800 text-xs">
                    </div>
                </div>

                <!-- Nguồn nước cấp (Có ô nhập tìm kiếm / Datalist) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">Nguồn nước cấp đầu vào</label>
                        <span class="text-[10px] text-slate-400">Nhập tìm hoặc chọn nhanh</span>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-droplet absolute left-3.5 top-3 text-sky-500"></i>
                        <input 
                            type="text" 
                            list="waterSourcesList" 
                            x-model="form.water_source_type" 
                            placeholder="Nhập hoặc chọn nguồn nước..." 
                            class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-medium text-slate-800 text-xs"
                        >
                        <datalist id="waterSourcesList">
                            <option value="Nước máy thành phố">Nước máy thành phố (Đã qua xử lý)</option>
                            <option value="Nước giếng khoan (Chưa lọc thô)">Nước giếng khoan (Chưa lọc thô)</option>
                            <option value="Nước giếng khoan (Đã qua bể lọc cát)">Nước giếng khoan (Đã qua bể lọc cát)</option>
                            <option value="Nước mưa / Bể trữ tự nhiên">Nước mưa / Bể trữ tự nhiên</option>
                            <option value="Nước ao hồ / Sông suối">Nước ao hồ / Sông suối</option>
                            <option value="Khác">Nguồn nước khác</option>
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú yêu cầu của khách hàng</label>
                    <textarea x-model="form.customer_notes" rows="3" placeholder="Ví dụ: Vị trí gầm tủ bếp chật, khách cần lắp thêm vòi phụ trên mặt đá, gọi trước 30 phút..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none text-xs text-slate-700"></textarea>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: MODEL MÁY, KỸ THUẬT VIÊN & TRẠNG THÁI ĐƠN (7 COLS) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2 font-extrabold text-slate-800 text-sm">
                        <i class="fa-solid fa-faucet-drip text-cyan-600"></i>
                        <span>Thiết bị & Điều phối Kỹ thuật</span>
                    </div>
                    <!-- Nút thêm model máy mới -->
                    <button type="button" @click="openModelModal()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 font-bold border border-cyan-200 transition cursor-pointer text-xs">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>+ Thêm Model Máy mới</span>
                    </button>
                </div>

                <!-- 1. TÌM MODEL MÁY LỌC BẰNG INPUT NHẬP TRỰC TIẾP -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">Model máy lọc nước cần lắp <span class="text-rose-500">*</span></label>
                        <span class="text-[11px] text-slate-400 font-medium">Gõ trực tiếp vào ô để tìm kiếm</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                        <!-- Direct Searchable Input for Machine Model -->
                        <div class="sm:col-span-8" @click.away="modelDropdownOpen = false">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400"></i>
                                <input 
                                    type="text" 
                                    x-model="modelSearchInput" 
                                    @focus="modelDropdownOpen = true" 
                                    @input="modelDropdownOpen = true; onInputModelSearch()"
                                    placeholder="Gõ tìm model: KG100HA, Karofi, Kangaroo..." 
                                    class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs transition"
                                >
                                <button 
                                    type="button" 
                                    x-show="form.machine_model_id || modelSearchInput" 
                                    @click="clearModel()" 
                                    class="absolute right-3 top-2.5 text-slate-400 hover:text-rose-500 p-0.5" 
                                    title="Xóa lựa chọn"
                                >
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>

                                <!-- Dropdown list directly underneath: RỘNG BẰNG 100% INPUT -->
                                <div 
                                    x-show="modelDropdownOpen" 
                                    x-cloak 
                                    class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-60 overflow-y-auto divide-y divide-slate-100"
                                >
                                    <template x-if="filteredModels.length === 0">
                                        <div class="p-4 text-center text-slate-400">
                                            <p>Không tìm thấy model nào phù hợp</p>
                                            <button type="button" @click="openModelModal(); modelDropdownOpen = false" class="mt-2 text-cyan-600 hover:underline font-bold text-xs inline-flex items-center gap-1 cursor-pointer">
                                                <i class="fa-solid fa-plus text-[10px]"></i> Thêm mới model này
                                            </button>
                                        </div>
                                    </template>
                                    <template x-for="m in filteredModels" :key="m.id">
                                        <div 
                                            @click="selectModel(m)" 
                                            class="p-2.5 hover:bg-sky-50/70 cursor-pointer flex items-center justify-between gap-2 transition"
                                            :class="form.machine_model_id == m.id ? 'bg-sky-50 text-sky-800 font-bold' : 'text-slate-700'"
                                        >
                                            <div class="truncate flex-1 min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-black" :class="form.machine_model_id == m.id ? 'bg-sky-200 text-sky-800' : 'bg-slate-100 text-slate-600'" x-text="m.brand || 'Khác'"></span>
                                                    <span class="font-bold text-slate-800 text-xs truncate" x-text="m.name"></span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
                                                    <span>Mã: <strong x-text="m.code"></strong></span>
                                                    <span>&bull;</span>
                                                    <span x-text="(m.filter_stages || 10) + ' cấp lọc'"></span>
                                                    <span x-show="m.type">&bull;</span>
                                                    <span x-show="m.type" x-text="m.type"></span>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span x-show="m.standard_price" class="text-cyan-700 font-extrabold text-xs block" x-text="window.formatCurrency(m.standard_price)"></span>
                                                <i x-show="form.machine_model_id == m.id" class="fa-solid fa-check text-sky-600 text-xs"></i>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Ô nhập tên model máy tự do ngoài danh mục -->
                        <div class="sm:col-span-4">
                            <input type="text" x-model="form.machine_model" placeholder="Hoặc tên model tự nhập..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-semibold text-slate-800 text-xs">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Serial / IMEI máy (nếu có)</label>
                        <input type="text" x-model="form.machine_serial" placeholder="KG-2026-XXXX..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono font-bold text-slate-700 text-xs">
                    </div>

                    <!-- 2. TÌM KỸ THUẬT VIÊN BẰNG INPUT NHẬP TRỰC TIẾP -->
                    <div @click.away="techDropdownOpen = false">
                        <label class="block font-bold text-slate-700 mb-1">Kỹ thuật viên phụ trách lắp đặt</label>
                        <div class="relative">
                            <i class="fa-solid fa-user-gear absolute left-3.5 top-3 text-slate-400"></i>
                            <input 
                                type="text" 
                                x-model="techSearchInput" 
                                @focus="techDropdownOpen = true" 
                                @input="techDropdownOpen = true; onInputTechSearch()"
                                placeholder="Gõ tìm KTV (Tên, SĐT, phòng ban)..." 
                                class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs transition"
                            >
                            <button 
                                type="button" 
                                x-show="form.technician_id || techSearchInput" 
                                @click="clearTechnician()" 
                                class="absolute right-3 top-2.5 text-slate-400 hover:text-rose-500 p-0.5" 
                                title="Xóa lựa chọn"
                            >
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>

                            <!-- Dropdown list directly underneath: RỘNG BẰNG 100% INPUT -->
                            <div 
                                x-show="techDropdownOpen" 
                                x-cloak 
                                class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-52 overflow-y-auto divide-y divide-slate-100"
                            >
                                <div @click="selectTechnician(null)" class="p-2.5 hover:bg-slate-50 cursor-pointer text-slate-500 italic flex items-center justify-between">
                                    <span>-- Chưa chỉ định (Để sau) --</span>
                                    <i x-show="!form.technician_id" class="fa-solid fa-check text-sky-600 text-xs"></i>
                                </div>
                                <template x-if="filteredTechnicians.length === 0">
                                    <div class="p-3 text-center text-slate-400">Không tìm thấy KTV phù hợp</div>
                                </template>
                                <template x-for="t in filteredTechnicians" :key="t.id">
                                    <div 
                                        @click="selectTechnician(t)" 
                                        class="p-2.5 hover:bg-sky-50/70 cursor-pointer flex items-center justify-between gap-2 transition"
                                        :class="form.technician_id == t.id ? 'bg-sky-50 text-sky-800 font-bold' : 'text-slate-700'"
                                    >
                                        <div class="truncate flex-1 min-w-0">
                                            <div class="font-bold text-slate-800 text-xs" x-text="t.name"></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5" x-text="(t.department || 'Bộ phận Kỹ thuật') + (t.phone ? ' • ' + t.phone : '')"></div>
                                        </div>
                                        <i x-show="form.technician_id == t.id" class="fa-solid fa-check text-sky-600 text-xs"></i>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. TRẠNG THÁI ĐƠN HÀNG CHI TIẾT -->
                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-extrabold text-slate-800 flex items-center gap-1.5 text-xs">
                            <i class="fa-solid fa-timeline text-sky-600"></i>
                            <span>Trạng thái đơn hàng lắp đặt:</span>
                        </label>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase" :class="getStatusBadgeClass(form.status)" x-text="getStatusLabel(form.status)"></span>
                    </div>

                    <select x-model="form.status" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-extrabold text-slate-800 text-xs shadow-2xs">
                        @foreach($statuses as $stKey => $stVal)
                            <option value="{{ $stKey }}">{{ $loop->iteration }}. {{ $stVal['label'] }}</option>
                        @endforeach
                    </select>

                    <p class="text-[11px] text-slate-500">
                        * Bạn có thể chủ động cập nhật trạng thái đơn qua từng giai đoạn (*Mới tiếp nhận* ➔ *Đã hẹn lịch* ➔ *Đang chuẩn bị* ➔ *Đang lắp* ➔ *Hoàn thành*).
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Lịch hẹn lắp đặt</label>
                        <input type="datetime-local" x-model="form.appointment_date" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phí công lắp đặt (đ)</label>
                        <input type="number" x-model.number="form.service_fee" min="0" step="10000" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-extrabold text-sky-800 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thanh toán công lắp</label>
                        <select x-model="form.payment_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold text-slate-800 text-xs">
                            <option value="unpaid">Chưa thu tiền</option>
                            <option value="paid">Đã thanh toán đủ</option>
                            <option value="free">Miễn phí theo máy</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION: PHỤ TÙNG, PHỤ KIỆN XUẤT THEO ĐƠN MÁY LỌC (TÌM BẰNG INPUT NHẬP TRỰC TIẾP) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <div class="flex items-center gap-2 font-extrabold text-slate-800 text-sm">
                    <i class="fa-solid fa-boxes-stacked text-teal-600"></i>
                    <span>Phụ Tùng, Vật Tư & Linh Kiện Mang Theo Đơn Lắp Đặt</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Gõ trực tiếp vào ô input để tìm mã hoặc tên phụ tùng từ kho (tự động trừ kho khi hoàn thành đơn)
                </p>
            </div>
            <button type="button" @click="addItemRow()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold transition shadow-xs cursor-pointer text-xs self-start sm:self-auto">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Thêm vật tư phụ tùng</span>
            </button>
        </div>

        <div class="overflow-visible border border-slate-200 rounded-xl min-h-[300px]">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3 w-12 text-center">STT</th>
                        <th class="py-3 px-3 min-w-[320px]">Phụ tùng / Linh kiện (Ô nhập tìm kiếm trực tiếp)</th>
                        <th class="py-3 px-3 w-28 text-center">Tồn kho hiện có</th>
                        <th class="py-3 px-3 w-24 text-center">ĐVT</th>
                        <th class="py-3 px-3 w-28 text-center">Số lượng</th>
                        <th class="py-3 px-3 w-36 text-right">Đơn giá xuất (đ)</th>
                        <th class="py-3 px-3 w-36 text-right">Thành tiền (đ)</th>
                        <th class="py-3 px-3 min-w-[180px]">Ghi chú</th>
                        <th class="py-3 px-3 w-14 text-center">Xóa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <template x-if="form.items.length === 0">
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-2xl mb-1 text-slate-300 block"></i>
                                Chưa có phụ kiện nào đi kèm (Nhấn "Thêm vật tư phụ tùng" nếu khách mua thêm cút nối, van, vòi, bình áp...)
                            </td>
                        </tr>
                    </template>
                    <template x-for="(item, idx) in form.items" :key="idx">
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3 text-center text-slate-400 font-bold" x-text="idx + 1"></td>
                            
                            <!-- 4. Ô TÌM BẰNG INPUT NHẬP TRỰC TIẾP (RỘNG BẰNG 100% INPUT) -->
                            <td class="py-2.5 px-3" x-data="{ open: false }" @click.away="open = false">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                    <input 
                                        type="text" 
                                        x-model="item.part_name" 
                                        @focus="open = true" 
                                        @input="open = true; onTypePartName(idx, $event.target.value)"
                                        placeholder="Gõ tìm mã hoặc tên phụ tùng..." 
                                        class="w-full pl-8 pr-7 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-500 focus:outline-none placeholder-slate-400 transition"
                                    >
                                    <button 
                                        type="button" 
                                        x-show="item.part_id || item.part_name" 
                                        @click="clearPartRow(idx)" 
                                        class="absolute right-2.5 top-2 text-slate-400 hover:text-rose-500 p-0.5" 
                                        title="Xóa lựa chọn"
                                    >
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>

                                    <!-- Dropdown suggestions list: RỘNG BẰNG 100% INPUT (left-0 right-0 w-full) -->
                                    <div 
                                        x-show="open && filterPartsBy(item.part_name).length > 0" 
                                        x-cloak 
                                        class="absolute left-0 right-0 w-full top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden text-xs max-h-60 overflow-y-auto divide-y divide-slate-100"
                                    >
                                        <template x-for="p in filterPartsBy(item.part_name)" :key="p.id">
                                            <div 
                                                @click="onSelectPart(idx, p.id); open = false" 
                                                class="p-2.5 hover:bg-teal-50 cursor-pointer flex items-center justify-between gap-2 transition"
                                                :class="item.part_id == p.id ? 'bg-teal-50 font-bold' : 'text-slate-700'"
                                            >
                                                <div class="truncate flex-1 min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-600 shrink-0" x-text="p.code"></span>
                                                        <span class="font-bold text-slate-800 truncate" x-text="p.name"></span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
                                                        <span x-text="p.category"></span>
                                                        <span>&bull;</span>
                                                        <span :class="p.stock_qty <= p.min_stock_alert ? 'text-rose-600 font-bold' : 'text-teal-700 font-semibold'" x-text="'Tồn: ' + p.stock_qty + ' ' + p.unit"></span>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class="text-teal-800 font-bold text-xs" x-text="window.formatCurrency(p.out_price)"></span>
                                                    <i x-show="item.part_id == p.id" class="fa-solid fa-check text-teal-600 text-xs ml-1.5"></i>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700" x-text="item.stock_available ?? '-'"></span>
                            </td>
                            <td class="py-3 px-3 text-center text-slate-600 font-semibold" x-text="item.unit || 'Cái'"></td>
                            <td class="py-3 px-3 text-center">
                                <input type="number" x-model.number="item.quantity" min="1" class="w-20 px-2 py-1.5 border border-slate-300 rounded-lg text-xs font-black text-center focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            </td>
                            <td class="py-3 px-3 text-right">
                                <input type="number" x-model.number="item.unit_price" min="0" step="1000" class="w-28 px-2 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-right focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            </td>
                            <td class="py-3 px-3 text-right font-extrabold text-teal-800" x-text="window.formatCurrency(item.quantity * item.unit_price)"></td>
                            <td class="py-3 px-3">
                                <input type="text" x-model="item.note" placeholder="Vị trí lắp, ghi chú..." class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none text-slate-700">
                            </td>
                            <td class="py-3 px-3 text-center">
                                <button type="button" @click="removeItemRow(idx)" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Xóa dòng">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- SUMMARY CALCULATION -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info text-sky-500 mr-1"></i>
                Tổng tiền = Phí công lắp đặt + Tổng tiền phụ kiện xuất kèm máy
            </div>
            <div class="flex items-center gap-6">
                <div>
                    <span class="text-slate-500 text-xs font-medium mr-2">Tiền phụ kiện:</span>
                    <span class="font-extrabold text-slate-800" x-text="window.formatCurrency(calcPartsFee())"></span>
                </div>
                <div>
                    <span class="text-slate-500 text-xs font-medium mr-2">Công lắp:</span>
                    <span class="font-extrabold text-slate-800" x-text="window.formatCurrency(form.service_fee || 0)"></span>
                </div>
                <div class="pl-4 border-l border-slate-300">
                    <span class="text-slate-700 text-xs font-extrabold mr-2">TỔNG THU:</span>
                    <span class="text-xl font-black text-sky-700" x-text="window.formatCurrency(calcTotalAmount())"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ url('/water-filter?tab=installations') }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-slate-700 font-bold hover:bg-slate-50 transition text-xs cursor-pointer">
            Hủy bỏ
        </a>
        <button @click="submitForm()" :disabled="submitting" type="button" class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black shadow-md shadow-sky-600/20 transition text-xs cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
            <i class="fa-solid" :class="submitting ? 'fa-spinner fa-spin' : 'fa-check text-sm'"></i>
            <span x-text="submitting ? 'ĐANG LƯU ĐƠN...' : (isEdit ? 'LƯU CẬP NHẬT ĐƠN HÀNG' : 'TẠO MỚI ĐƠN LẮP ĐẶT')"></span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL THÊM MODEL MÁY MỚI (TẠO TRỰC TIẾP TẠI CHỖ)             -->
    <!-- ============================================================== -->
    <div x-show="showModelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showModelModal = false" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 to-sky-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-faucet-drip text-lg"></i>
                    <h3 class="font-bold text-base">THÊM MỚI MODEL MÁY LỌC NƯỚC</h3>
                </div>
                <button type="button" @click="showModelModal = false" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form @submit.prevent="saveNewModelSubmit()" class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã Model máy <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="newModelForm.code" required placeholder="vd: KG100HA, KRF-612..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold uppercase focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thương hiệu / Hãng <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="newModelForm.brand" required placeholder="Kangaroo, Karofi, Sunhouse..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tên Model máy lọc nước <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="newModelForm.name" required placeholder="Máy lọc RO Kangaroo Hydrogen 10 lõi..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-semibold focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số cấp lọc (lõi)</label>
                        <input type="number" x-model.number="newModelForm.filter_stages" min="1" max="20" placeholder="vd: 10" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-center focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Loại kiểu dáng</label>
                        <select x-model="newModelForm.type" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            <option value="Tủ đứng">Tủ đứng</option>
                            <option value="Để bàn">Để bàn mini</option>
                            <option value="Lắp gầm chậu">Lắp gầm chậu rửa</option>
                            <option value="Nóng Lạnh RO">Nóng Lạnh RO</option>
                            <option value="Bán công nghiệp">Bán công nghiệp</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Giá bán niêm yết / tham khảo (đ)</label>
                    <input type="number" x-model.number="newModelForm.standard_price" min="0" step="50000" placeholder="5.500.000..." class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-cyan-800 focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mô tả đặc tính kỹ thuật</label>
                    <textarea x-model="newModelForm.description" rows="2" placeholder="Công nghệ RO vortex, màng Filmtec, bình áp thép..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:outline-none"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="showModelModal = false" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Lưu & Chọn model này</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function installationFormModule() {
    const initData = @json($installation ?? null);
    const partsCatalog = @json($parts ?? []);
    const modelsCatalog = @json($machineModels ?? []);
    const techCatalog = @json($technicians ?? []);
    const statusMap = @json($statuses ?? []);

    return {
        isEdit: !!(initData && initData.id),
        parts: partsCatalog,
        machineModels: modelsCatalog,
        technicians: techCatalog,
        statuses: statusMap,
        showModelModal: false,
        submitting: false,

        // Direct input search states
        modelDropdownOpen: false,
        modelSearchInput: '',
        techDropdownOpen: false,
        techSearchInput: '',

        form: {
            id: initData ? initData.id : null,
            customer_name: initData ? initData.customer_name : '',
            customer_phone: initData ? initData.customer_phone : '',
            customer_address: initData ? initData.customer_address : '',
            water_source_type: initData ? (initData.water_source_type || 'Nước máy thành phố') : 'Nước máy thành phố',
            customer_notes: initData ? (initData.customer_notes || '') : '',
            machine_model_id: initData ? initData.machine_model_id : '',
            machine_model: (function() {
                if (!initData) return '';
                if (typeof initData.machine_model === 'string') return initData.machine_model;
                if (initData.machine_model && typeof initData.machine_model === 'object' && initData.machine_model.name) return initData.machine_model.name;
                if (initData.model_catalog && initData.model_catalog.name) return initData.model_catalog.name;
                return '';
            })(),
            machine_serial: initData ? (initData.machine_serial || '') : '',
            technician_id: initData ? (initData.technician_id || '') : '',
            status: initData ? initData.status : 'pending',
            appointment_date: initData && initData.appointment_date ? initData.appointment_date.replace(' ', 'T').substring(0, 16) : '',
            service_fee: initData ? Number(initData.service_fee || 0) : 150000,
            payment_status: initData ? initData.payment_status : 'unpaid',
            installed_at: initData && initData.installed_at ? initData.installed_at.replace(' ', 'T').substring(0, 16) : '',
            warranty_months: initData ? (Number(initData.warranty_months) || 24) : 24,
            warranty_until: initData && initData.warranty_until ? (initData.warranty_until.length > 10 ? initData.warranty_until.substring(0, 10) : initData.warranty_until) : '',
            warranty_card_code: initData ? (initData.warranty_card_code || '') : '',
            filter_replacement_months: initData ? (Number(initData.filter_replacement_months) || 6) : 6,
            warranty_notes: initData ? (initData.warranty_notes || '') : '',
            items: initData && initData.items ? initData.items.map(it => {
                const foundPart = partsCatalog.find(p => p.id == it.part_id);
                return {
                    part_id: it.part_id,
                    part_name: it.part_name,
                    unit: it.unit,
                    quantity: it.quantity,
                    unit_price: Number(it.unit_price || 0),
                    note: it.note || '',
                    stock_available: foundPart ? foundPart.stock_qty : '-'
                };
            }) : []
        },

        newModelForm: {
            code: '',
            brand: 'Karofi',
            name: '',
            filter_stages: 10,
            type: 'Tủ đứng',
            standard_price: 5000000,
            description: ''
        },

        init() {
            // Auto match and populate input values
            if (this.form.machine_model_id) {
                const foundM = this.machineModels.find(m => m.id == this.form.machine_model_id);
                if (foundM) {
                    this.modelSearchInput = '[' + (foundM.brand || 'Khác') + '] ' + foundM.name;
                    if (!this.form.machine_model || typeof this.form.machine_model !== 'string') {
                        this.form.machine_model = foundM.name;
                    }
                }
            } else if (this.form.machine_model) {
                this.modelSearchInput = typeof this.form.machine_model === 'string'
                    ? this.form.machine_model
                    : (this.form.machine_model.name || '');
            }

            if (this.form.technician_id) {
                const foundT = this.technicians.find(t => t.id == this.form.technician_id);
                if (foundT) {
                    this.techSearchInput = foundT.name + ' (' + (foundT.department || 'KTV') + ')';
                }
            }

            // Auto calculate warranty_until if missing but installed_at exists
            if (this.form.installed_at && !this.form.warranty_until) {
                this.recalcWarrantyUntil();
            }
        },

        recalcWarrantyUntil() {
            if (!this.form.installed_at) return;
            try {
                const datePart = this.form.installed_at.split('T')[0];
                const parts = datePart.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + (Number(this.form.warranty_months) || 24));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.form.warranty_until = `${y}-${m}-${day}`;
                }
            } catch (e) {
                console.error(e);
            }
        },

        /* -------------------------------------------------------------
         * 1. SEARCHABLE MACHINE MODELS (DIRECT INPUT)
         * ------------------------------------------------------------- */
        get filteredModels() {
            if (!this.modelSearchInput || !this.modelSearchInput.trim()) return this.machineModels;
            const q = this.modelSearchInput.toLowerCase().trim();
            return this.machineModels.filter(m => 
                (m.name && m.name.toLowerCase().includes(q)) ||
                (m.code && m.code.toLowerCase().includes(q)) ||
                (m.brand && m.brand.toLowerCase().includes(q))
            );
        },

        onInputModelSearch() {
            this.form.machine_model = this.modelSearchInput;
            // Check if matches an existing model exactly
            const found = this.machineModels.find(m => m.name.toLowerCase() === this.modelSearchInput.toLowerCase().trim());
            this.form.machine_model_id = found ? found.id : '';
        },

        selectModel(model) {
            this.form.machine_model_id = model.id;
            this.form.machine_model = model.name;
            this.modelSearchInput = '[' + (model.brand || 'Khác') + '] ' + model.name;
            this.modelDropdownOpen = false;
        },

        clearModel() {
            this.form.machine_model_id = '';
            this.form.machine_model = '';
            this.modelSearchInput = '';
            this.modelDropdownOpen = false;
        },

        /* -------------------------------------------------------------
         * 2. SEARCHABLE TECHNICIANS (DIRECT INPUT)
         * ------------------------------------------------------------- */
        get filteredTechnicians() {
            if (!this.techSearchInput || !this.techSearchInput.trim()) return this.technicians;
            const q = this.techSearchInput.toLowerCase().trim();
            return this.technicians.filter(t => 
                (t.name && t.name.toLowerCase().includes(q)) ||
                (t.department && t.department.toLowerCase().includes(q)) ||
                (t.phone && t.phone.toLowerCase().includes(q))
            );
        },

        onInputTechSearch() {
            const found = this.technicians.find(t => t.name.toLowerCase() === this.techSearchInput.toLowerCase().trim());
            this.form.technician_id = found ? found.id : '';
        },

        selectTechnician(tech) {
            if (tech) {
                this.form.technician_id = tech.id;
                this.techSearchInput = tech.name + ' (' + (tech.department || 'KTV') + ')';
            } else {
                this.form.technician_id = '';
                this.techSearchInput = '';
            }
            this.techDropdownOpen = false;
        },

        clearTechnician() {
            this.form.technician_id = '';
            this.techSearchInput = '';
            this.techDropdownOpen = false;
        },

        /* -------------------------------------------------------------
         * 3. ORDER STATUS BADGES
         * ------------------------------------------------------------- */
        getStatusLabel(stKey) {
            return this.statuses[stKey] ? this.statuses[stKey].label : stKey;
        },

        getStatusBadgeClass(stKey) {
            return this.statuses[stKey] ? this.statuses[stKey].badge : 'bg-slate-100 text-slate-800';
        },

        /* -------------------------------------------------------------
         * 4. SEARCHABLE PARTS IN ITEMS TABLE (DIRECT INPUT)
         * ------------------------------------------------------------- */
        filterPartsBy(keyword) {
            if (!keyword || !keyword.trim()) return this.parts;
            const q = keyword.toLowerCase().trim();
            return this.parts.filter(p => 
                (p.name && p.name.toLowerCase().includes(q)) ||
                (p.code && p.code.toLowerCase().includes(q)) ||
                (p.category && p.category.toLowerCase().includes(q))
            );
        },

        onTypePartName(idx, text) {
            this.form.items[idx].part_name = text;
            // Check if matches an existing part
            const found = this.parts.find(p => p.name.toLowerCase() === text.toLowerCase().trim());
            if (found) {
                this.form.items[idx].part_id = found.id;
                this.form.items[idx].unit = found.unit;
                this.form.items[idx].unit_price = Number(found.out_price || 0);
                this.form.items[idx].stock_available = found.stock_qty;
            } else {
                this.form.items[idx].part_id = '';
            }
        },

        onSelectPart(idx, partId) {
            const found = this.parts.find(p => p.id == partId);
            if (found) {
                this.form.items[idx].part_id = found.id;
                this.form.items[idx].part_name = found.name;
                this.form.items[idx].unit = found.unit;
                this.form.items[idx].unit_price = Number(found.out_price || 0);
                this.form.items[idx].stock_available = found.stock_qty;
            } else {
                this.form.items[idx].stock_available = '-';
            }
        },

        clearPartRow(idx) {
            this.form.items[idx].part_id = '';
            this.form.items[idx].part_name = '';
            this.form.items[idx].unit_price = 0;
            this.form.items[idx].stock_available = '-';
        },

        addItemRow() {
            this.form.items.push({
                part_id: '',
                part_name: '',
                unit: 'Cái',
                quantity: 1,
                unit_price: 0,
                note: '',
                stock_available: '-'
            });
        },

        removeItemRow(idx) {
            this.form.items.splice(idx, 1);
        },

        calcPartsFee() {
            return this.form.items.reduce((sum, it) => sum + (it.quantity * it.unit_price), 0);
        },

        calcTotalAmount() {
            return (Number(this.form.service_fee) || 0) + this.calcPartsFee();
        },

        /* -------------------------------------------------------------
         * QUICK ADD MACHINE MODEL MODAL
         * ------------------------------------------------------------- */
        openModelModal() {
            this.newModelForm = {
                code: '',
                brand: 'Karofi',
                name: '',
                filter_stages: 10,
                type: 'Tủ đứng',
                standard_price: 5000000,
                description: ''
            };
            this.showModelModal = true;
        },

        async saveNewModelSubmit() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/water-filter/machine-models', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.newModelForm)
                });
                let data = {};
                try { data = await res.json(); } catch (err) {}

                if (!res.ok || !data.success) {
                    let errMsg = data.message || 'Lỗi thêm model máy';
                    if (data.errors) {
                        errMsg += '\n• ' + Object.values(data.errors).flat().join('\n• ');
                    }
                    alert(errMsg);
                    return;
                }

                this.machineModels.push(data.model);
                this.form.machine_model_id = data.model.id;
                this.form.machine_model = data.model.name;
                this.modelSearchInput = '[' + (data.model.brand || 'Khác') + '] ' + data.model.name;
                this.showModelModal = false;
                alert('Đã thêm model máy lọc: ' + data.model.name);
            } catch (e) {
                alert('Có lỗi khi thêm model máy: ' + (e.message || 'Lỗi kết nối'));
            }
        },

        async submitForm() {
            if (this.submitting) return;

            const toStr = (v) => {
                if (typeof v === 'string') return v.trim();
                if (v && typeof v === 'object' && v.name) return String(v.name).trim();
                return String(v ?? '').trim();
            };

            const customerName = toStr(this.form.customer_name);
            const customerPhone = toStr(this.form.customer_phone);
            const customerAddress = toStr(this.form.customer_address);

            if (!customerName || !customerPhone || !customerAddress) {
                alert('Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ lắp đặt!');
                return;
            }

            const modelName = toStr(this.form.machine_model) || toStr(this.modelSearchInput);
            if (!modelName && !this.form.machine_model_id) {
                alert('Vui lòng chọn hoặc nhập Model máy lọc nước cần lắp!');
                return;
            }

            this.submitting = true;

            const payload = {
                id: this.form.id || null,
                customer_name: customerName,
                customer_phone: customerPhone,
                customer_address: customerAddress,
                water_source_type: this.form.water_source_type || 'Nước máy thành phố',
                customer_notes: this.form.customer_notes || '',
                machine_model_id: this.form.machine_model_id ? Number(this.form.machine_model_id) : null,
                machine_model: modelName,
                machine_serial: toStr(this.form.machine_serial),
                technician_id: this.form.technician_id ? Number(this.form.technician_id) : null,
                status: this.form.status || 'pending',
                appointment_date: this.form.appointment_date || null,
                service_fee: Number(this.form.service_fee || 0),
                payment_status: this.form.payment_status || 'unpaid',
                installed_at: this.form.installed_at || null,
                warranty_months: Number(this.form.warranty_months || 24),
                warranty_until: this.form.warranty_until || null,
                warranty_card_code: toStr(this.form.warranty_card_code),
                filter_replacement_months: Number(this.form.filter_replacement_months || 6),
                warranty_notes: toStr(this.form.warranty_notes),
                items: (this.form.items || [])
                    .filter(it => it.part_name && toStr(it.part_name))
                    .map(it => ({
                        part_id: it.part_id ? Number(it.part_id) : null,
                        part_name: toStr(it.part_name),
                        unit: it.unit || 'Cái',
                        quantity: Number(it.quantity || 1),
                        unit_price: Number(it.unit_price || 0),
                        note: it.note || ''
                    }))
            };

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/water-filter/installations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                let data = {};
                try {
                    data = await res.json();
                } catch (err) {}

                if (!res.ok || !data.success) {
                    let errMsg = data.message || ('Lỗi lưu đơn hàng (Mã ' + res.status + ')');
                    if (data.errors) {
                        const errList = Object.values(data.errors).flat().join('\n• ');
                        errMsg += '\n• ' + errList;
                    }
                    alert(errMsg);
                    this.submitting = false;
                    return;
                }

                alert(data.message || 'Lưu đơn lắp đặt máy lọc nước thành công!');
                window.location.href = '/water-filter?tab=installations';
            } catch (e) {
                alert('Có lỗi kết nối máy chủ: ' + (e.message || 'Vui lòng kiểm tra đường truyền'));
                this.submitting = false;
            }
        }
    };
}
</script>
@endsection
