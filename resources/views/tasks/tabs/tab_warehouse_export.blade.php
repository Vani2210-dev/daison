{{-- Tab: Lấy Hàng Từ Kho Để Đi Lắp Đặt, Bảo Dưỡng, Sửa Chữa (KTV Tự Tạo Phiếu & Trừ Kho) --}}
<div class="space-y-6">

    <!-- Header Banner & Thống Kê Nhanh -->
    <div class="warehouse-export-banner bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 p-5 sm:p-6 rounded-3xl text-white shadow-xl border border-slate-700/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/30 shrink-0">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-lg sm:text-xl font-black tracking-tight text-white">
                        Lấy Hàng Từ Kho Để Đi Làm
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        KTV Tự Tạo & Trừ Kho Ngay
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Kỹ thuật viên chủ động xuất phụ tùng, linh kiện và máy lọc mang đi phục vụ <strong>Lắp Đặt</strong>, <strong>Bảo Dưỡng</strong> hoặc <strong>Sửa Chữa</strong>. Hỗ trợ hoàn trả linh kiện dư thừa về kho bất kỳ lúc nào.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto shrink-0">
            <button type="button" 
                    @click="scrollToCreateExportForm()" 
                    class="w-full md:w-auto px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-black text-xs sm:text-sm shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-2 transition hover:scale-[1.02] active:scale-[0.98] cursor-pointer">
                <i class="fa-solid fa-plus-circle text-base"></i>
                <span>Tạo Phiếu Lấy Hàng Mới</span>
            </button>
        </div>
    </div>

    <!-- KHU VỰC 1: FORM TẠO PHIẾU XUẤT KHO / LẤY HÀNG MANG ĐI (Mobile-First) -->
    <div id="form-create-tech-export" class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/90 shadow-xs space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-dolly"></i>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">
                        Tạo Phiếu Xuất Kho Lấy Hàng Đi Làm
                    </h3>
                    <p class="text-[11px] text-slate-500">Chọn mục đích, linh kiện cần lấy và số lượng tương ứng</p>
                </div>
            </div>

            <button type="button" 
                    @click="resetTechExportForm()" 
                    class="text-xs font-bold text-slate-500 hover:text-slate-800 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                Làm mới form
            </button>
        </div>

        <!-- BƯỚC 1: CHỌN MỤC ĐÍCH LẤY HÀNG (4 NÚT TO BẢN) -->
        <div>
            <label class="text-xs font-bold text-slate-700 block mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-bullseye text-teal-600"></i>
                <span>1. Chọn mục đích lấy hàng mang đi:</span>
                <span class="text-rose-500">*</span>
            </label>

            <div class="warehouse-purpose-grid grid grid-cols-2 lg:grid-cols-4 gap-2.5">
                <!-- 1. Đi Lắp Đặt -->
                <button type="button" 
                        @click="techExportForm.purpose = 'install'" 
                        :class="techExportForm.purpose === 'install' ? 'bg-blue-600 text-white font-black shadow-md shadow-blue-600/30 border-blue-600' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200 font-bold'"
                        class="p-3 rounded-2xl border text-left transition-all cursor-pointer flex flex-col gap-1 active:scale-95">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-faucet-drip text-lg" :class="techExportForm.purpose === 'install' ? 'text-white' : 'text-blue-600'"></i>
                        <span x-show="techExportForm.purpose === 'install'" class="text-xs">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>
                    <span class="text-xs sm:text-sm font-black mt-1">Đi Lắp Đặt Máy</span>
                    <span class="text-[10px] opacity-80 font-normal">Máy lọc, vòi, cút nối...</span>
                </button>

                <!-- 2. Đi Bảo Dưỡng -->
                <button type="button" 
                        @click="techExportForm.purpose = 'maintenance'" 
                        :class="techExportForm.purpose === 'maintenance' ? 'bg-purple-600 text-white font-black shadow-md shadow-purple-600/30 border-purple-600' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200 font-bold'"
                        class="p-3 rounded-2xl border text-left transition-all cursor-pointer flex flex-col gap-1 active:scale-95">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-calendar-check text-lg" :class="techExportForm.purpose === 'maintenance' ? 'text-white' : 'text-purple-600'"></i>
                        <span x-show="techExportForm.purpose === 'maintenance'" class="text-xs">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>
                    <span class="text-xs sm:text-sm font-black mt-1">Đi Bảo Dưỡng Định Kỳ</span>
                    <span class="text-[10px] opacity-80 font-normal">Bộ lõi 1 2 3, màng RO...</span>
                </button>

                <!-- 3. Đi Sửa Chữa -->
                <button type="button" 
                        @click="techExportForm.purpose = 'repair'" 
                        :class="techExportForm.purpose === 'repair' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-600/30 border-amber-600' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200 font-bold'"
                        class="p-3 rounded-2xl border text-left transition-all cursor-pointer flex flex-col gap-1 active:scale-95">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-screwdriver-wrench text-lg" :class="techExportForm.purpose === 'repair' ? 'text-white' : 'text-amber-600'"></i>
                        <span x-show="techExportForm.purpose === 'repair'" class="text-xs">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>
                    <span class="text-xs sm:text-sm font-black mt-1">Đi Sửa Chữa Sự Cố</span>
                    <span class="text-[10px] opacity-80 font-normal">Bơm, adaptor, van áp...</span>
                </button>

                <!-- 4. Túi Đồ Nghề / Dự Phòng -->
                <button type="button" 
                        @click="techExportForm.purpose = 'general'" 
                        :class="techExportForm.purpose === 'general' ? 'bg-emerald-600 text-white font-black shadow-md shadow-emerald-600/30 border-emerald-600' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200 font-bold'"
                        class="p-3 rounded-2xl border text-left transition-all cursor-pointer flex flex-col gap-1 active:scale-95">
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-toolbox text-lg" :class="techExportForm.purpose === 'general' ? 'text-white' : 'text-emerald-600'"></i>
                        <span x-show="techExportForm.purpose === 'general'" class="text-xs">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>
                    <span class="text-xs sm:text-sm font-black mt-1">Túi Vật Tư Mang Theo</span>
                    <span class="text-[10px] opacity-80 font-normal">Băng tan, dây, cút phụ...</span>
                </button>
            </div>
        </div>

        <!-- BƯỚC 2: THÔNG TIN KHÁCH HÀNG / ĐƠN HÀNG MỤC TIÊU (TÙY CHỌN) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1 flex items-center gap-1">
                    <i class="fa-solid fa-user-tag text-slate-400"></i>
                    <span>Tên khách hàng / Đơn hàng liên quan (tùy chọn):</span>
                </label>
                <input type="text" 
                       x-model="techExportForm.target_name" 
                       placeholder="VD: Đơn Bác Dương Hồng Sơn, hoặc Khu đô thị An Khánh..." 
                       class="w-full text-xs sm:text-sm font-semibold text-slate-800 rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 py-2 px-3 bg-slate-50/50">
            </div>

            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1 flex items-center gap-1">
                    <i class="fa-solid fa-note-sticky text-slate-400"></i>
                    <span>Ghi chú phiếu xuất:</span>
                </label>
                <input type="text" 
                       x-model="techExportForm.notes" 
                       placeholder="VD: Mang đi thay cho máy kangaroo 10 lõi..." 
                       class="w-full text-xs sm:text-sm font-semibold text-slate-800 rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 py-2 px-3 bg-slate-50/50">
            </div>
        </div>

        <!-- BƯỚC 3: DANH SÁCH LINH KIỆN CẦN LẤY TỪ KHO -->
        <div class="bg-slate-50/70 p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/70">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-teal-700 text-sm"></i>
                    <h4 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider">
                        Danh Sách Phụ Tùng / Linh Kiện Lấy Từ Kho
                    </h4>
                    <span class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 font-bold text-xs" x-text="techExportForm.items.length"></span>
                </div>

                <button type="button" 
                        @click="addTechExportItemRow()" 
                        class="px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm cursor-pointer active:scale-95">
                    <i class="fa-solid fa-plus text-[11px]"></i>
                    <span>+ Thêm linh kiện</span>
                </button>
            </div>

            <!-- Danh sách từng dòng linh kiện (Card Responsive) -->
            <div class="space-y-2.5">
                <template x-for="(it, idx) in techExportForm.items" :key="idx">
                    <div class="bg-white p-3 rounded-xl border border-slate-200/90 shadow-2xs transition-all">
                        <div class="flex items-start gap-2">
                            <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 font-black text-xs flex items-center justify-center shrink-0 mt-1" x-text="idx + 1"></span>

                            <!-- Ô Searchable Autocomplete chọn phụ tùng kho -->
                            <div class="relative flex-1 min-w-0" @click.away="it.showDropdown = false">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" 
                                           x-model="it.part_name" 
                                           @focus="it.showDropdown = true" 
                                           @input="it.showDropdown = true" 
                                           placeholder="Gõ tìm kiếm hoặc bấm mũi tên để chọn linh kiện từ kho..." 
                                           class="w-full text-xs sm:text-sm font-bold text-slate-800 rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 py-2 pl-8 pr-8 bg-white shadow-2xs">

                                    <button type="button" 
                                            @click.stop="it.showDropdown = !it.showDropdown" 
                                            class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400 hover:text-teal-600 flex items-center justify-center cursor-pointer transition">
                                        <i class="fa-solid fa-chevron-down text-[11px] transition-transform duration-200" :class="{ 'rotate-180': it.showDropdown }"></i>
                                    </button>
                                </div>

                                <!-- Badge thông tin phụ tùng đã chọn -->
                                <div x-show="it.part_id" class="text-[11px] text-slate-600 mt-1.5 flex flex-wrap items-center gap-2 pl-1">
                                    <span class="font-mono text-teal-700 font-bold" x-text="`Mã: ${it.part_code || '---'}`"></span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px]" 
                                          :class="it.current_stock > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'"
                                          x-text="`Tồn kho hiện có: ${it.current_stock} ${it.unit || ''}`">
                                    </span>
                                </div>

                                <!-- DROPDOWN GỢI Ý PHỤ TÙNG TỪ KHO -->
                                <div x-show="it.showDropdown" 
                                     x-cloak 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 max-h-56 overflow-y-auto">
                                    
                                    <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between sticky top-0 border-b border-slate-100 z-10">
                                        <span>Danh mục Kho Phụ Tùng (<span x-text="getFilteredPartsForMaint(it.part_name).length"></span>)</span>
                                        <span class="text-teal-700 font-normal">Chạm để chọn</span>
                                    </div>

                                    <template x-for="p in getFilteredPartsForMaint(it.part_name)" :key="p.id">
                                        <div @click="selectTechExportPart(it, p)" 
                                             class="px-3 py-2.5 hover:bg-teal-50/80 cursor-pointer flex items-center justify-between gap-2 transition active:bg-teal-100">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold text-xs text-slate-800 truncate" x-text="p.name"></div>
                                                <div class="text-[10px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                    <span class="font-mono text-slate-400 font-bold" x-text="p.code"></span>
                                                    <span class="text-slate-300">&bull;</span>
                                                    <span class="font-semibold" 
                                                          :class="p.stock_qty > 0 ? 'text-emerald-700' : 'text-rose-600 font-bold'" 
                                                          x-text="`Tồn: ${p.stock_qty} ${p.unit || ''}`"></span>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="font-mono font-bold text-xs text-slate-700" x-text="`${(p.out_price || 0).toLocaleString('vi-VN')} đ`"></span>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="getFilteredPartsForMaint(it.part_name).length === 0" 
                                         class="p-3 text-center text-xs text-slate-500">
                                        Không tìm thấy linh kiện nào trong kho khớp với từ khóa.
                                    </div>
                                </div>
                            </div>

                            <!-- Nút xóa dòng -->
                            <button type="button" 
                                    @click="removeTechExportItemRow(idx)" 
                                    class="text-slate-400 hover:text-rose-600 p-2 rounded-xl hover:bg-rose-50 transition cursor-pointer shrink-0 mt-0.5" 
                                    title="Xóa dòng này">
                                <i class="fa-regular fa-trash-can text-sm"></i>
                            </button>
                        </div>

                        <!-- Cụm chọn số lượng lấy & Ghi chú dòng -->
                        <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-600">Số lượng cần lấy:</span>
                                <div class="flex items-center border border-slate-300 rounded-lg bg-white overflow-hidden shrink-0">
                                    <button type="button" 
                                            @click="it.quantity = Math.max(1, (it.quantity || 1) - 1)" 
                                            class="px-2.5 py-1 text-slate-500 hover:bg-slate-100 active:bg-slate-200 font-bold text-xs cursor-pointer">-</button>
                                    <input type="number" 
                                           min="1" 
                                           :max="it.current_stock || 9999"
                                           x-model.number="it.quantity" 
                                           @input="it.quantity = Math.max(1, it.quantity || 1)"
                                           class="w-14 text-center text-xs font-black text-slate-900 border-x border-slate-200 py-1 px-1 focus:outline-hidden">
                                    <button type="button" 
                                            @click="it.quantity = Math.min((it.current_stock || 9999), (it.quantity || 1) + 1)" 
                                            class="px-2.5 py-1 text-slate-500 hover:bg-slate-100 active:bg-slate-200 font-bold text-xs cursor-pointer">+</button>
                                </div>
                                <span class="text-xs font-bold text-teal-700" x-text="it.unit || 'Cái'"></span>
                                <span x-show="it.part_id && it.quantity > it.current_stock" class="text-[11px] font-bold text-rose-600">
                                    (Vượt tồn kho!)
                                </span>
                            </div>

                            <div class="flex-1 sm:max-w-xs">
                                <input type="text" 
                                       x-model="it.note" 
                                       placeholder="Ghi chú món này (vd: mang dự phòng)..." 
                                       class="w-full text-xs text-slate-600 rounded-lg border-slate-200 focus:border-teal-500 focus:ring-teal-500 py-1 px-2.5 bg-slate-50">
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="techExportForm.items.length === 0" class="py-6 text-center text-slate-400 bg-white rounded-xl border border-dashed border-slate-200">
                    <i class="fa-solid fa-cart-flatbed text-2xl text-slate-300 mb-1 block"></i>
                    <span class="text-xs font-bold">Chưa chọn linh kiện nào.</span>
                    <p class="text-[11px] text-slate-400 mt-0.5">Bấm nút <strong>"+ Thêm linh kiện"</strong> ở góc trên bên phải để bắt đầu lấy hàng.</p>
                </div>
            </div>
        </div>

        <!-- NÚT XÁC NHẬN XUẤT KHO -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info text-teal-600"></i>
                <span>Tồn kho của linh kiện sẽ tự động <strong>trừ ngay lập tức</strong> sau khi bấm xác nhận.</span>
            </div>

            <button type="button" 
                    @click="submitTechExport()" 
                    :disabled="techExportSubmitting || techExportForm.items.length === 0"
                    class="px-7 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs sm:text-sm shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition hover:scale-[1.02] active:scale-[0.98] cursor-pointer disabled:opacity-50">
                <template x-if="techExportSubmitting">
                    <i class="fa-solid fa-circle-notch fa-spin text-base"></i>
                </template>
                <template x-if="!techExportSubmitting">
                    <i class="fa-solid fa-arrow-up-from-bracket text-base"></i>
                </template>
                <span x-text="techExportSubmitting ? 'Đang tạo phiếu xuất kho...' : 'XÁC NHẬN XUẤT KHO & LẤY HÀNG'"></span>
            </button>
        </div>
    </div>

    <!-- KHU VỰC 2: LỊCH SỬ PHIẾU LẤY HÀNG CỦA KTV & HOÀN TRẢ KHO -->
    <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200/90 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">
                        Lịch Sử Phiếu Lấy Hàng & Hoàn Kho
                    </h3>
                    <p class="text-[11px] text-slate-500">
                        {{ $view === 'my_tasks' ? 'Danh sách các lần lấy hàng của bạn' : 'Tất cả các lần xuất kho của kỹ thuật viên' }}
                    </p>
                </div>
            </div>

            <!-- Ô tìm kiếm phiếu xuất -->
            <form action="{{ url('/tasks') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="tab" value="warehouse_export">
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="export_search" 
                           value="{{ request('export_search') }}" 
                           placeholder="Tìm mã phiếu, tên phụ tùng..." 
                           class="w-full pl-8 pr-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-300 focus:border-purple-500 focus:ring-purple-500 bg-slate-50">
                </div>
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition cursor-pointer">
                    Tìm
                </button>
                @if(request('export_search'))
                    <a href="{{ url('/tasks?view=' . $view . '&tab=warehouse_export') }}" class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200">
                        Xóa
                    </a>
                @endif
            </form>
        </div>

        <!-- DANH SÁCH CÁC GIAO DỊCH XUẤT / HOÀN KHO -->
        @if($techExportTransactions->isEmpty())
            <div class="py-12 text-center text-slate-400">
                <i class="fa-solid fa-receipt text-3xl text-slate-300 mb-2 block"></i>
                <span class="text-sm font-bold text-slate-600">Chưa có lịch sử lấy hàng nào.</span>
                <p class="text-xs text-slate-400 mt-1">Khi bạn tạo phiếu lấy hàng đi lắp đặt/bảo dưỡng/sửa chữa ở trên, lịch sử sẽ xuất hiện tại đây.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @foreach($techExportTransactions as $tx)
                    @php
                        $isReturn = ($tx->type === 'return_tech_part');
                        $isInstall = str_contains($tx->type, 'install');
                        $isMaint = str_contains($tx->type, 'maintenance');
                        $isRepair = str_contains($tx->type, 'repair');
                    @endphp
                    <div class="bg-slate-50/70 hover:bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between gap-3">
                        <div>
                            <!-- Header thẻ giao dịch -->
                            <div class="flex items-start justify-between gap-2 mb-2 pb-2 border-b border-slate-200/60">
                                <div>
                                    <span class="font-mono text-xs font-black text-slate-900 block">
                                        {{ $tx->reference_code ?: ('TX-' . $tx->id) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                        <i class="fa-regular fa-clock"></i>
                                        <span>{{ $tx->created_at ? $tx->created_at->format('H:i d/m/Y') : '---' }}</span>
                                    </span>
                                </div>

                                <!-- Badge loại phiếu -->
                                @if($isReturn)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-100 text-sky-800 border border-sky-300">
                                        <i class="fa-solid fa-rotate-left"></i> Hoàn trả kho
                                    </span>
                                @elseif($isInstall)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-300">
                                        <i class="fa-solid fa-faucet-drip"></i> Đi lắp đặt
                                    </span>
                                @elseif($isMaint)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-300">
                                        <i class="fa-solid fa-calendar-check"></i> Đi bảo dưỡng
                                    </span>
                                @elseif($isRepair)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                        <i class="fa-solid fa-wrench"></i> Đi sửa chữa
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-toolbox"></i> Túi vật tư
                                    </span>
                                @endif
                            </div>

                            <!-- Nội dung linh kiện -->
                            <div>
                                <h4 class="text-xs sm:text-sm font-black text-slate-900 line-clamp-1" title="{{ $tx->part?->name }}">
                                    {{ $tx->part?->name ?: 'Linh kiện #' . $tx->part_id }}
                                </h4>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                    Mã: {{ $tx->part?->code ?: '---' }}
                                </div>

                                <!-- Số lượng xuất / hoàn -->
                                <div class="mt-2 flex items-center justify-between bg-white p-2 rounded-xl border border-slate-200/80">
                                    <span class="text-[11px] font-bold text-slate-500">
                                        {{ $isReturn ? 'Số lượng hoàn lại:' : 'Số lượng đã lấy:' }}
                                    </span>
                                    <span class="font-mono text-sm font-black {{ $isReturn ? 'text-sky-600' : 'text-rose-600' }}">
                                        {{ $isReturn ? '+' : '' }}{{ $tx->quantity }} {{ $tx->part?->unit ?: 'Cái' }}
                                    </span>
                                </div>

                                <!-- Ghi chú / Mục tiêu -->
                                @if($tx->note)
                                    <div class="mt-2 text-[11px] text-slate-600 bg-slate-100/70 p-2 rounded-xl line-clamp-2" title="{{ $tx->note }}">
                                        {{ $tx->note }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Footer thẻ & Nút Hoàn Trả Kho -->
                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between gap-2">
                            <span class="text-[10px] text-slate-500 truncate">
                                Người lấy: <strong>{{ $tx->created_by_name ?: ($tx->creator?->name ?? 'KTV') }}</strong>
                            </span>

                            @if(!$isReturn && $tx->part_id)
                                <button type="button" 
                                        @click="openReturnModal({{ $tx->part_id }}, '{{ addslashes($tx->part?->name ?? '') }}', '{{ $tx->reference_code }}', {{ abs($tx->quantity) }}, '{{ $tx->part?->unit ?? 'Cái' }}')"
                                        class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-[11px] border border-sky-200 transition flex items-center gap-1 cursor-pointer shrink-0 active:scale-95"
                                        title="Hoàn trả số lượng dư thừa về lại kho">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                    <span>Hoàn kho thừa</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $techExportTransactions->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL HOÀN TRẢ LINH KIỆN THỪA VỀ KHO --}}
<div x-show="returnPartModalOpen" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     role="dialog" 
     aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="returnPartModalOpen = false"></div>

        <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-rotate-left"></i>
                    </span>
                    <h3 class="text-sm sm:text-base font-black text-slate-800">
                        Hoàn Trả Linh Kiện Thừa Về Kho
                    </h3>
                </div>
                <button type="button" @click="returnPartModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-slate-400 font-bold uppercase block">Linh kiện hoàn trả:</span>
                    <div class="font-bold text-sm text-slate-800 mt-0.5" x-text="returnForm.part_name"></div>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                        <span>Xuất từ phiếu: <strong class="font-mono text-purple-700" x-text="returnForm.original_reference_code || '---'"></strong></span>
                        <span>&bull;</span>
                        <span>Đã lấy: <strong class="text-slate-700" x-text="`${returnForm.max_qty} ${returnForm.unit}`"></strong></span>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">
                        Số lượng trả lại kho:
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="number" 
                               min="1" 
                               :max="returnForm.max_qty" 
                               x-model.number="returnForm.quantity" 
                               class="w-32 text-center text-sm font-black text-slate-900 rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500 py-2 px-2 bg-white">
                        <span class="text-xs font-bold text-slate-600" x-text="returnForm.unit"></span>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">
                        Lý do hoàn trả (tùy chọn):
                    </label>
                    <input type="text" 
                           x-model="returnForm.note" 
                           placeholder="VD: Không dùng hết, máy khách còn dùng tốt..." 
                           class="w-full text-xs text-slate-800 rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500 py-2 px-3 bg-white">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 mt-5 border-t border-slate-100">
                <button type="button" @click="returnPartModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 font-bold text-xs hover:bg-slate-100 transition cursor-pointer">
                    Hủy bỏ
                </button>

                <button type="button" 
                        @click="submitReturnPart()" 
                        :disabled="returnSubmitting"
                        class="px-5 py-2 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-700 hover:to-blue-700 text-white font-bold text-xs shadow-md shadow-sky-600/30 flex items-center gap-2 transition cursor-pointer disabled:opacity-50">
                    <template x-if="returnSubmitting">
                        <i class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                    </template>
                    <span x-text="returnSubmitting ? 'Đang hoàn kho...' : 'Xác Nhận Hoàn Kho'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
