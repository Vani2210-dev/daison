    <!-- MODAL: QUÉT MÃ QR CÀI ĐẶT THÔNG TIN MÁY LỌC NƯỚC           -->
    <!-- ======================================================== -->
    <div x-show="showModal" 
         id="qr-setup-modal"
         x-cloak 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-2.5 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        
        <div @click.away="closeModal()" 
             class="bg-white w-full max-w-3xl rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-auto max-h-[92vh] sm:max-h-[95vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-emerald-400 text-base sm:text-lg border border-white/20 shrink-0">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-black tracking-tight leading-snug">Cài Đặt Máy & Kích Hoạt Mã QR Bảo Hành</h3>
                        <p class="text-[11px] sm:text-xs text-blue-200 truncate sm:whitespace-normal">Quét tem QR dán trên máy hoặc chọn tem từ kho để liên kết đơn lắp đặt</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white transition-colors cursor-pointer shrink-0">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-4 sm:p-6 overflow-y-auto space-y-5 flex-1">

                <!-- Success Screen State -->
                <template x-if="successState">
                    <div class="text-center py-5 sm:py-6 space-y-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl sm:text-4xl mx-auto shadow-lg shadow-emerald-500/20 animate-bounce">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <h4 class="text-base sm:text-lg font-black text-slate-900" x-text="successMessage || 'Kích Hoạt Tem QR Thành Công!'"></h4>
                            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">Đơn lắp đặt đã được đánh dấu hoàn thành và khách hàng có thể quét mã QR để tra cứu bảo hành điện tử.</p>
                        </div>

                        <!-- Passport Badge -->
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200 max-w-md mx-auto text-left space-y-2 text-xs">
                            <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                                <span class="text-slate-500">Mã Tem QR:</span>
                                <strong class="text-blue-700 font-mono text-sm" x-text="activatedQrCode"></strong>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                                <span class="text-slate-500">Khách Hàng:</span>
                                <strong class="text-slate-900" x-text="form.customer_name"></strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Model Máy:</span>
                                <strong class="text-slate-900" x-text="form.machine_model"></strong>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2.5 sm:gap-3 pt-2">
                            <a :href="successPassportUrl" 
                               target="_blank" 
                               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-xs shadow-md shadow-blue-500/20 flex items-center justify-center gap-2 hover:scale-[1.02] transition-transform">
                                <i class="fa-solid fa-passport"></i>
                                <span>Xem Thẻ Bảo Hành Cho Khách</span>
                            </a>
                            <button type="button" 
                                    @click="closeModal(); window.location.reload();" 
                                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                                <span>Hoàn Tất & Đóng</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Input Form State -->
                <template x-if="!successState">
                    <form @submit.prevent="submitBindQr()" class="space-y-5">

                        <!-- SECTION 1: QUÉT / CHỌN TEM QR -->
                        <div class="bg-slate-50/80 p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-qrcode text-blue-600 text-sm"></i>
                                    <span>Bước 1: Quét Hoặc Nhập Tem QR Dán Trên Máy</span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                
                                <!-- Mode Selector: Chỉ còn 2 chế độ Camera và Nhập Mã -->
                                <div class="grid grid-cols-2 sm:flex items-center bg-white p-1 rounded-xl border border-slate-200 text-xs font-bold w-full sm:w-auto shadow-2xs">
                                    <button type="button" 
                                            @click="qrMode = 'camera'; startScanner();" 
                                            :class="qrMode === 'camera' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                                            class="py-2 sm:py-1 px-3.5 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer text-center">
                                        <i class="fa-solid fa-camera text-xs"></i>
                                        <span>Camera</span>
                                    </button>
                                    <button type="button" 
                                            @click="qrMode = 'manual'; stopScanner();" 
                                            :class="qrMode === 'manual' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                                            class="py-2 sm:py-1 px-3.5 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer text-center">
                                        <i class="fa-solid fa-keyboard text-xs"></i>
                                        <span>Nhập Mã</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Camera Scanner Viewport -->
                            <div x-show="qrMode === 'camera'" class="space-y-2.5">
                                <div id="qr-reader" class="w-full max-w-sm mx-auto overflow-hidden rounded-2xl border-2 border-dashed border-blue-400 bg-black min-h-[220px]"></div>
                                <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 max-w-sm mx-auto gap-2">
                                    <span class="flex items-center gap-1 text-[11px]">
                                        <i class="fa-solid fa-bolt text-amber-500"></i>
                                        <span>Hướng camera vào tem QR để quét tự động</span>
                                    </span>
                                    <button type="button" @click="startScanner()" class="text-blue-600 font-bold hover:underline flex items-center gap-1 cursor-pointer py-1 px-2 rounded-lg hover:bg-blue-50">
                                        <i class="fa-solid fa-rotate-right"></i>
                                        <span>Bật lại Camera</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Manual Input -->
                            <div x-show="qrMode === 'manual'">
                                <div class="relative">
                                    <input type="text" 
                                           x-model="form.qr_code" 
                                           @input="form.qr_code = extractQrCode($event.target.value)"
                                           @paste="$nextTick(() => form.qr_code = extractQrCode(form.qr_code))"
                                           placeholder="Nhập mã tem (vd: QR-10001) hoặc quét/dán tem..." 
                                           class="w-full pl-3.5 pr-10 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-mono font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 min-h-[44px]">
                                    <i class="fa-solid fa-qrcode absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                </div>
                            </div>

                            <!-- Display Scanned / Selected QR -->
                            <div x-show="form.qr_code" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                                    <div>
                                        <span class="text-slate-600 text-xs">Đã chọn mã QR:</span>
                                        <strong class="font-mono font-bold text-emerald-800 text-sm ml-1" x-text="form.qr_code"></strong>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-200 text-emerald-900 uppercase tracking-wide">Sẵn sàng liên kết</span>
                            </div>
                        </div>

                        <!-- SECTION 2: CHỌN ĐƠN LẮP ĐẶT & THÔNG TIN KHÁCH HÀNG -->
                        <div class="space-y-3">
                            <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-file-invoice text-indigo-600"></i>
                                <span>Bước 2: Chọn Đơn Lắp Đặt & Thông Tin Khách Hàng</span>
                                <span class="text-rose-500">*</span>
                            </label>

                            <div>
                                @php
                                    $unassignedInstallations = $installationsData->filter(function($f) {
                                        $hasQr = ($f->status === 'completed') 
                                            || ($f->installedMachine && ($f->installedMachine->status === 'activated' || !empty($f->installedMachine->qr_code))) 
                                            || !empty($f->warranty_card_code);
                                        return !$hasQr;
                                    });
                                @endphp
                                <select x-model="form.installation_id" 
                                        @change="onInstallationChange()" 
                                        required 
                                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 min-h-[44px]">
                                    <option value="">-- Chọn đơn lắp đặt --</option>
                                    @foreach($unassignedInstallations as $f)
                                        <option value="{{ $f->id }}">
                                            [{{ $f->code }}] {{ $f->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Editable Customer Info -->
                            <div class="p-3.5 bg-slate-50/90 rounded-2xl border border-slate-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-pen text-indigo-600"></i>
                                        <span>Thông Tin Khách Hàng</span>
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-bold text-slate-700 block mb-1">
                                            Tên Khách Hàng <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   x-model="form.customer_name" 
                                                   required 
                                                   placeholder="Nhập họ tên khách hàng..." 
                                                   class="w-full pl-8 pr-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 min-h-[40px]">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-700 block mb-1">
                                            Số Điện Thoại <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   x-model="form.customer_phone" 
                                                   required 
                                                   placeholder="Nhập số điện thoại..." 
                                                   class="w-full pl-8 pr-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-mono font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 min-h-[40px]">
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="text-xs font-bold text-slate-700 block mb-1">
                                            Địa Chỉ Lắp Đặt <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   x-model="form.customer_address" 
                                                   required 
                                                   placeholder="Nhập địa chỉ nhà khách hàng..." 
                                                   class="w-full pl-8 pr-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 min-h-[40px]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: THÔNG SỐ KỸ THUẬT & MÁY LỌC NƯỚC -->
                        <div class="space-y-4 pt-2 border-t border-slate-100">
                            <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-sliders text-teal-600"></i>
                                <span>Bước 3: Cài Đặt Thông Số Kỹ Thuật Thực Tế</span>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <!-- Machine Model -->
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Model Máy Lọc Nước <span class="text-rose-500">*</span></label>
                                    <input type="text" 
                                           x-model="form.machine_model" 
                                           required 
                                           placeholder="vd: Karofi Optimus O-s129..." 
                                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 min-h-[42px]">
                                </div>

                                <!-- Serial Number -->
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Mã Serial / Số Máy (Nếu có)</label>
                                    <input type="text" 
                                           x-model="form.machine_serial" 
                                           placeholder="vd: SN-RO-2026-981..." 
                                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-blue-500 min-h-[42px]">
                                </div>

                                <!-- Warranty Months (Nhập trực tiếp số tháng) -->
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Thời Hạn Bảo Hành Chính Hãng (Tháng)</label>
                                    <div class="relative">
                                        <input type="number" 
                                               x-model.number="form.warranty_months" 
                                               min="1" 
                                               max="120" 
                                               placeholder="24" 
                                               class="w-full pl-3.5 pr-16 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 min-h-[42px]">
                                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">Tháng</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <span class="text-[11px] text-slate-400">Gợi ý nhanh:</span>
                                        <button type="button" @click="form.warranty_months = 12" class="px-2 py-0.5 text-[11px] rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition-colors">12 tháng</button>
                                        <button type="button" @click="form.warranty_months = 24" class="px-2 py-0.5 text-[11px] rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition-colors">24 tháng</button>
                                        <button type="button" @click="form.warranty_months = 36" class="px-2 py-0.5 text-[11px] rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition-colors">36 tháng</button>
                                    </div>
                                </div>

                                <!-- Accessories Field: Lưu dạng JSON (tên phụ kiện, số lượng, ngày lắp) -->
                                <div class="sm:col-span-2 space-y-3 pt-1">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                            <i class="fa-solid fa-boxes-stacked text-teal-600"></i>
                                            <span>Bổ Sung Phụ Kiện</span>
                                        </label>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <template x-if="selectedAccessories.length > 0">
                                                <button type="button" @click="selectedAccessories = []; syncAccessoriesJson();" class="text-rose-500 hover:underline cursor-pointer text-[10px]">
                                                    Xóa tất cả
                                                </button>
                                            </template>
                                            <span class="text-[11px] text-teal-700 font-bold bg-teal-50 px-2 py-0.5 rounded-lg border border-teal-200">
                                                Đã chọn: <span x-text="selectedAccessories.length"></span> phụ kiện bổ sung
                                            </span>
                                            <template x-if="calcAccessoriesFee() > 0">
                                                <span class="text-[11px] text-amber-800 font-extrabold bg-amber-100/80 px-2 py-0.5 rounded-lg border border-amber-300">
                                                    Thu thêm: +<span x-text="new Intl.NumberFormat('vi-VN').format(calcAccessoriesFee())"></span> đ
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Searchable Accessory Combobox & Custom Part Toggle -->
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                        <!-- Search Box with Autocomplete Dropdown -->
                                        <div class="relative flex-1" @click.away="partDropdownOpen = false">
                                            <div class="relative">
                                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                                <input type="text" 
                                                       x-model="partSearchQuery" 
                                                       @focus="partDropdownOpen = true" 
                                                       @input="partDropdownOpen = true" 
                                                       @keydown.enter.prevent="if(filteredPartsCatalog.length > 0) { addPartToAccessories(filteredPartsCatalog[0].id); }"
                                                       placeholder="Tìm phụ kiện theo tên, mã (vd: Vòi, Bơm, Bình áp...)..." 
                                                       class="w-full pl-9 pr-9 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 min-h-[42px] shadow-2xs">
                                                
                                                <!-- Dropdown toggle button -->
                                                <button type="button" 
                                                        @click="partDropdownOpen = !partDropdownOpen" 
                                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400 hover:text-teal-600 flex items-center justify-center cursor-pointer">
                                                    <i class="fa-solid fa-chevron-down text-[11px] transition-transform duration-200" :class="{ 'rotate-180': partDropdownOpen }"></i>
                                                </button>
                                            </div>

                                            <!-- Autocomplete Suggestions List Dropdown -->
                                            <div x-show="partDropdownOpen" 
                                                 x-cloak 
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden divide-y divide-slate-100 max-h-64 overflow-y-auto">
                                                
                                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                                    <span>Kho phụ tùng (<span x-text="filteredPartsCatalog.length"></span>)</span>
                                                    <span class="text-teal-700 font-normal">Chạm để chọn & thêm</span>
                                                </div>

                                                <template x-if="filteredPartsCatalog.length === 0">
                                                    <div class="p-4 text-center text-xs text-slate-500 space-y-2">
                                                        <p>Không tìm thấy phụ kiện nào với từ khóa "<strong class="text-slate-800" x-text="partSearchQuery"></strong>"</p>
                                                        <button type="button" 
                                                                @click="customPartName = partSearchQuery; showCustomPartForm = true; partDropdownOpen = false;" 
                                                                class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-xs rounded-lg border border-teal-200 inline-flex items-center gap-1.5 cursor-pointer">
                                                            <i class="fa-solid fa-plus"></i>
                                                            <span>Thêm "<span x-text="partSearchQuery"></span>" làm phụ kiện ngoài kho</span>
                                                        </button>
                                                    </div>
                                                </template>

                                                <template x-for="p in filteredPartsCatalog" :key="p.id">
                                                    <div @click="addPartToAccessories(p.id)" 
                                                         class="p-2.5 hover:bg-teal-50/70 cursor-pointer flex items-center justify-between gap-2.5 transition active:bg-teal-100">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600 border border-slate-200/60" x-text="p.code"></span>
                                                                <span class="font-bold text-slate-800 text-xs sm:text-sm truncate" x-text="p.name"></span>
                                                            </div>
                                                            <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                                                <span x-text="p.category || 'Phụ kiện'"></span>
                                                                <span>&bull;</span>
                                                                <span :class="(p.stock_qty || 0) <= 0 ? 'text-rose-600 font-bold' : 'text-teal-700 font-semibold'" 
                                                                      x-text="'Tồn kho: ' + (p.stock_qty || 0) + ' ' + (p.unit || 'Cái')"></span>
                                                            </div>
                                                        </div>
                                                        <div class="shrink-0 flex items-center gap-2">
                                                            <span class="text-xs font-black text-teal-800" x-text="(Number(p.out_price) > 0 ? new Intl.NumberFormat('vi-VN').format(p.out_price) + ' đ' : '0 đ')"></span>
                                                            <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold shadow-2xs hover:scale-105 transition" title="Thêm phụ kiện này">
                                                                <i class="fa-solid fa-plus"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Toggle button for non-catalog items -->
                                        <button type="button" 
                                                @click="showCustomPartForm = !showCustomPartForm" 
                                                class="px-4 py-2.5 rounded-xl border border-dashed border-teal-400 bg-teal-50/50 hover:bg-teal-100 text-teal-700 font-bold text-xs flex items-center justify-center gap-1.5 shrink-0 min-h-[42px] cursor-pointer transition-all shadow-2xs">
                                            <i class="fa-solid fa-circle-plus text-xs"></i>
                                            <span>Thêm Ngoài Kho</span>
                                        </button>
                                    </div>

                                    <!-- Form nhập phụ kiện ngoài kho nếu có -->
                                    <div x-show="showCustomPartForm" x-cloak class="p-3 bg-teal-50/70 rounded-xl border border-teal-200 space-y-2.5">
                                        <div class="flex items-center justify-between text-xs font-bold text-teal-900">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-wrench text-teal-600"></i>
                                                <span>Thêm Phụ Kiện Ngoài Kho</span>
                                            </span>
                                            <button type="button" @click="showCustomPartForm = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                            <div class="sm:col-span-4">
                                                <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Tên Phụ Kiện <span class="text-rose-500">*</span></label>
                                                <input type="text" 
                                                       x-model="customPartName" 
                                                       placeholder="vd: Vòi gạt Inox 304..." 
                                                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                            </div>
                                            <div class="grid grid-cols-2 sm:col-span-2 gap-1.5">
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Số Lượng</label>
                                                    <input type="number" 
                                                           x-model.number="customPartQty" 
                                                           min="1" 
                                                           placeholder="1" 
                                                           class="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300 bg-white font-bold text-center focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Đơn Vị</label>
                                                    <input type="text" 
                                                           x-model="customPartUnit" 
                                                           placeholder="Cái" 
                                                           class="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300 bg-white text-center focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                                </div>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Loại Tính Phí</label>
                                                <div class="flex items-center gap-1.5">
                                                    <button type="button" 
                                                            @click="customPartIsFree = !customPartIsFree" 
                                                            class="px-2 py-1.5 rounded-lg text-[10px] font-black border transition cursor-pointer min-h-[36px] flex items-center gap-1 shrink-0" 
                                                            :class="customPartIsFree ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-900 border-amber-300'">
                                                        <i class="fa-solid" :class="customPartIsFree ? 'fa-gift' : 'fa-hand-holding-dollar'"></i>
                                                        <span x-text="customPartIsFree ? 'Miễn phí (0đ)' : 'Tính phí'"></span>
                                                    </button>
                                                    <div x-show="!customPartIsFree" class="flex-1 min-w-0">
                                                        <input type="number" 
                                                               x-model.number="customPartPrice" 
                                                               min="0" 
                                                               step="1000" 
                                                               placeholder="Đơn giá (đ)" 
                                                               class="w-full px-2 py-1.5 text-xs rounded-lg border border-amber-300 bg-white font-bold text-right text-amber-900 focus:ring-2 focus:ring-amber-500 min-h-[36px]">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Ngày Lắp Đặt</label>
                                                <input type="date" 
                                                       x-model="customPartDate" 
                                                       class="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300 bg-white font-mono focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                            </div>
                                            <div class="sm:col-span-1">
                                                <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Bảo Dưỡng</label>
                                                <div class="relative">
                                                    <input type="number" 
                                                           x-model.number="customPartMaintenanceMonths" 
                                                           min="0" 
                                                           max="120" 
                                                           placeholder="0" 
                                                           class="w-full pl-1.5 pr-4 py-1.5 text-xs rounded-lg border border-slate-300 bg-white font-bold text-center focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                                    <span class="absolute right-1 top-1/2 -translate-y-1/2 text-[9px] font-semibold text-slate-400 pointer-events-none">T</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-1 border-t border-teal-200/60">
                                            <button type="button" 
                                                    @click="showCustomPartForm = false" 
                                                    class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700 cursor-pointer">
                                                Hủy
                                            </button>
                                            <button type="button" 
                                                    @click="addCustomAccessory()" 
                                                    :disabled="!customPartName.trim()" 
                                                    class="px-3.5 py-1.5 bg-teal-600 hover:bg-teal-700 disabled:opacity-40 text-white font-bold text-xs rounded-lg cursor-pointer transition shadow-2xs">
                                                Lưu Phụ Kiện
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Danh sách phụ kiện: Thiết kế dạng thẻ Card tối ưu hoàn hảo trên mobile -->
                                    <div class="space-y-2">
                                        <template x-if="selectedAccessories.length === 0">
                                            <div class="p-4 text-center text-xs text-slate-400 italic rounded-xl border border-dashed border-slate-200 bg-slate-50/50">
                                                <i class="fa-solid fa-boxes-stacked text-lg mb-1 block text-slate-300"></i>
                                                Chưa có phụ kiện nào được thêm. Hãy chọn từ kho hoặc bấm "Thêm Ngoài Kho".
                                            </div>
                                        </template>

                                        <template x-for="(acc, idx) in selectedAccessories" :key="idx">
                                            <div class="p-3 bg-white rounded-xl border border-slate-200/90 shadow-2xs hover:border-teal-300 transition-all">
                                                <!-- Hàng 1: Tên phụ kiện, ĐVT, Phân loại & Nút xóa -->
                                                <div class="flex items-start justify-between gap-2.5">
                                                    <div class="flex items-start gap-2 min-w-0 flex-1">
                                                        <span class="w-5 h-5 rounded-md bg-teal-50 text-teal-600 flex items-center justify-center text-[11px] shrink-0 mt-0.5 border border-teal-200/60">
                                                            <i class="fa-solid fa-check"></i>
                                                        </span>
                                                        <div class="min-w-0 flex-1">
                                                            <h4 class="font-bold text-slate-800 text-xs sm:text-sm leading-snug break-words" x-text="acc.name"></h4>
                                                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                                                <template x-if="acc.unit">
                                                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60" x-text="'ĐVT: ' + acc.unit"></span>
                                                                </template>
                                                                
                                                                <!-- Nút gạt: Miễn phí (0đ) vs Tính phí thu thêm -->
                                                                <button type="button" 
                                                                        @click="toggleAccessoryFree(idx)" 
                                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black cursor-pointer transition shadow-2xs active:scale-95 border"
                                                                        :class="acc.is_free ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100' : 'bg-amber-50 text-amber-800 border-amber-300 hover:bg-amber-100'">
                                                                    <i class="fa-solid" :class="acc.is_free ? 'fa-gift text-emerald-600' : 'fa-hand-holding-dollar text-amber-600'"></i>
                                                                    <span x-text="acc.is_free ? 'Miễn phí (0đ)' : 'Tính phí thu thêm'"></span>
                                                                </button>

                                                                <!-- Ô nhập đơn giá thu thêm nếu chọn Tính phí -->
                                                                <template x-if="!acc.is_free">
                                                                    <div class="inline-flex items-center gap-2 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-300 text-xs flex-wrap">
                                                                        <span class="text-amber-800 font-bold">Đơn giá:</span>
                                                                        <input type="number" 
                                                                               x-model.number="acc.unit_price" 
                                                                               @input="syncAccessoriesJson()" 
                                                                               min="0" 
                                                                               step="1000" 
                                                                               placeholder="0" 
                                                                               class="w-24 px-2 py-0.5 text-right font-black rounded border border-amber-300 bg-white text-amber-900 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                                                        <span class="text-amber-800 font-bold">đ</span>
                                                                        <span class="text-slate-300">|</span>
                                                                        <span class="text-amber-900 font-extrabold text-[11px]">
                                                                            Thành tiền: <span x-text="new Intl.NumberFormat('vi-VN').format((parseInt(acc.quantity) || 1) * (parseFloat(acc.unit_price) || 0))"></span> đ
                                                                        </span>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Nút xóa dễ bấm trên mobile -->
                                                    <button type="button" 
                                                            @click="removeAccessory(idx)" 
                                                            class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center shrink-0 transition-all cursor-pointer border border-slate-200/80 active:scale-95" 
                                                            title="Xóa phụ kiện này">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </div>

                                                <!-- Hàng 2: Điều chỉnh Số lượng, Ngày lắp đặt & Thời hạn bảo dưỡng -->
                                                <div class="flex flex-wrap items-center justify-between gap-2.5 pt-2.5 mt-2 border-t border-slate-100 text-xs">
                                                    <!-- Bộ chỉnh Số lượng -->
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Số lượng:</span>
                                                        <div class="inline-flex items-center bg-slate-50 rounded-lg p-0.5 border border-slate-200 shadow-2xs">
                                                            <button type="button" 
                                                                    @click="changeAccessoryQty(idx, -1)" 
                                                                    class="w-7 h-7 rounded-md bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center font-black cursor-pointer shadow-2xs text-xs active:scale-90 transition border border-slate-200/60">-</button>
                                                            <span class="font-mono font-bold text-slate-900 w-8 text-center text-xs" x-text="acc.quantity"></span>
                                                            <button type="button" 
                                                                    @click="changeAccessoryQty(idx, 1)" 
                                                                    class="w-7 h-7 rounded-md bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center font-black cursor-pointer shadow-2xs text-xs active:scale-90 transition border border-slate-200/60">+</button>
                                                        </div>
                                                    </div>

                                                    <!-- Ô chọn Ngày lắp rộng rãi, đầy đủ icon lịch -->
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ngày lắp:</span>
                                                        <div class="relative inline-flex items-center">
                                                            <input type="date" 
                                                                   x-model="acc.installed_date" 
                                                                   @change="syncAccessoriesJson()" 
                                                                   class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 font-mono font-medium text-slate-800 bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer shadow-2xs">
                                                        </div>
                                                    </div>

                                                    <!-- Thời hạn bảo dưỡng cho từng phụ tùng -->
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1"
                                                              :class="acc.maintenance_months == 0 ? 'text-slate-500' : 'text-purple-700'">
                                                            <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                                                            <span>Bảo dưỡng:</span>
                                                        </span>
                                                        <div class="relative inline-flex items-center">
                                                            <input type="number" 
                                                                   x-model.number="acc.maintenance_months" 
                                                                   @input="syncAccessoriesJson()" 
                                                                   min="0" 
                                                                   max="120" 
                                                                   placeholder="0" 
                                                                   class="w-20 pl-2 pr-7 py-1 text-xs rounded-lg border font-bold text-center shadow-2xs transition-colors"
                                                                   :class="acc.maintenance_months == 0 ? 'border-slate-300 text-slate-500 bg-slate-100/80 focus:bg-white focus:border-slate-400' : 'border-purple-200 text-purple-900 bg-purple-50/50 focus:bg-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500'">
                                                            <span class="absolute right-2 text-[10px] font-bold pointer-events-none" 
                                                                  :class="acc.maintenance_months == 0 ? 'text-slate-400' : 'text-purple-400'">Tháng</span>
                                                        </div>
                                                        <template x-if="acc.maintenance_months == 0">
                                                            <span class="text-[10px] font-semibold text-slate-400 italic bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">Không định kỳ</span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <!-- Input hidden để submit form -->
                                    <input type="hidden" name="accessories" :value="form.accessories">
                                </div>
                            </div>


                            <!-- Notes -->
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Ghi Chú Nghiệm Thu Của Kỹ Thuật Viên</label>
                                <textarea x-model="form.activation_notes" 
                                          rows="2" 
                                          placeholder="Nhập ghi chú nghiệm thu (nếu có)..." 
                                          class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 min-h-[58px]"></textarea>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-3.5 sm:pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 sm:gap-3">
                            <div class="flex items-center gap-2">
                                <template x-if="calcAccessoriesFee() > 0">
                                    <div class="w-full sm:w-auto px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-bold flex items-center justify-between sm:justify-start gap-2 shadow-2xs">
                                        <i class="fa-solid fa-circle-exclamation text-amber-600 text-sm shrink-0"></i>
                                        <span>Phụ kiện thu thêm: <strong class="text-amber-800 font-black text-sm" x-text="new Intl.NumberFormat('vi-VN').format(calcAccessoriesFee()) + ' đ'"></strong></span>
                                    </div>
                                </template>
                                <template x-if="calcAccessoriesFee() === 0 && selectedAccessories.length > 0">
                                    <div class="w-full sm:w-auto px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-1.5">
                                        <i class="fa-solid fa-check text-emerald-600 shrink-0"></i>
                                        <span>Tất cả phụ kiện đều miễn phí (0đ)</span>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-stretch sm:items-center gap-2 w-full sm:w-auto justify-end">
                                <button type="button" 
                                        @click="closeModal()" 
                                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer text-center shrink-0 min-h-[44px] flex items-center justify-center">
                                    Hủy Bỏ
                                </button>
                                <button type="submit" 
                                        :disabled="submitting || !form.qr_code || !form.installation_id" 
                                        class="flex-1 sm:flex-none px-4 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 active:scale-[0.98] transition-all cursor-pointer min-h-[44px] whitespace-nowrap">
                                    <template x-if="submitting">
                                        <i class="fa-solid fa-spinner fa-spin"></i>
                                    </template>
                                    <template x-if="!submitting">
                                        <i class="fa-solid fa-circle-check text-sm"></i>
                                    </template>
                                    <span class="sm:hidden">Kích Hoạt & Hoàn Tất</span>
                                    <span class="hidden sm:inline">Kích Hoạt Tem QR & Hoàn Tất Đơn Lắp Đặt</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>