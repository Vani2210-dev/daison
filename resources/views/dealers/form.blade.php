@extends('layouts.app')

@section('title', ($isEdit ? 'Chỉnh Sửa Đại Lý: ' . $dealer->name : 'Thêm Mới Khách Hàng / Đại Lý') . ' | ERP Đại Sơn')

@section('content')
<div x-data="dealerFormApp()" x-init="initForm()" class="w-full space-y-6 pb-28">

    <!-- Top Action Header -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ url('/dealers') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors shadow-2xs shrink-0 cursor-pointer" title="Quay lại danh sách đại lý">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-store text-purple-600"></i>
                        <span>{{ $isEdit ? 'Chỉnh Sửa Đại Lý: ' . $dealer->name : 'Thêm Mới Khách Hàng / Đại Lý' }}</span>
                    </h1>
                    <span class="text-[11px] font-mono font-black px-2.5 py-0.5 rounded-lg bg-purple-50 text-purple-800 border border-purple-200 shadow-2xs">
                        {{ $isEdit ? $dealer->code : ($nextCode ?? 'DL-HN-001') }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Hồ sơ đối tác phân phối, thiết lập trần công nợ, địa chỉ giao nhận và phân cấp chiết khấu.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ url('/dealers') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all cursor-pointer">
                Hủy Bỏ
            </a>
            <button type="button" @click="submitForm()" :disabled="saving"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95">
                <i x-show="saving" class="fa-solid fa-spinner fa-spin"></i>
                <i x-show="!saving" class="fa-solid fa-floppy-disk"></i>
                <span x-text="saving ? 'Đang lưu...' : '{{ $isEdit ? 'Lưu Thay Đổi' : 'Xác Nhận Thêm Mới' }}'"></span>
            </button>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <form @submit.prevent="submitForm()" class="space-y-6">
        @csrf
        @if($isEdit)
            <input type="hidden" name="id" value="{{ $dealer->id }}">
        @endif

        <!-- ========================================== -->
        <!-- 1. THÔNG TIN ĐẠI LÝ & NGƯỜI ĐẠI DIỆN -->
        <!-- ========================================== -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-black text-sm shrink-0 border border-purple-100 shadow-2xs">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900">1. Thông Tin Đại Lý & Người Đại Diện</h2>
                    <p class="text-xs text-slate-400 font-medium">Mã khách hàng, tên cửa hàng, mã số thuế, người đại diện và người giới thiệu</p>
                </div>
            </div>

            <div class="space-y-4 text-xs font-semibold">
                <!-- Hàng 1: Mã KH, Tên Đại Lý, MST -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Mã Khách Hàng -->
                    <div class="md:col-span-3">
                        <label class="block font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Mã Khách Hàng <span class="text-slate-400 font-normal">(Mã cố định)</span></span>
                        </label>
                        <input type="text" x-model="formData.code"
                               class="w-full px-3.5 py-2.5 bg-purple-50/60 focus:bg-white border border-purple-200 rounded-xl font-mono text-xs font-black text-purple-800 outline-none focus:border-purple-500 shadow-2xs uppercase transition-all"
                               placeholder="DL-HN-001">
                    </div>

                    <!-- Tên Đại Lý / Cửa Hàng * -->
                    <div class="md:col-span-6">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Tên Đại Lý / Cửa Hàng <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="formData.name" required
                               placeholder="VD: Cửa Hàng Vật Tư Ống Nhựa Thành Công"
                               class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all placeholder:text-slate-400">
                    </div>

                    <!-- Mã Số Thuế (MST) -->
                    <div class="md:col-span-3">
                        <label class="block font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-regular fa-file-lines text-slate-400"></i>
                            <span>Mã Số Thuế (MST)</span>
                        </label>
                        <input type="text" x-model="formData.tax_code"
                               placeholder="VD: 0101234567..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono text-xs font-bold text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <!-- Hàng 2: Chủ Đại Lý, SĐT, Người Giới Thiệu, Phân Cấp KH -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                    <!-- Chủ Đại Lý / Người Đại Diện -->
                    <div class="lg:col-span-3">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Chủ Đại Lý / Người Đại Diện
                        </label>
                        <input type="text" x-model="formData.owner_name"
                               placeholder="Nguyễn Thành Công"
                               class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-semibold text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all placeholder:text-slate-400">
                    </div>

                    <!-- Số Điện Thoại Liên Hệ * -->
                    <div class="lg:col-span-3">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Số Điện Thoại Liên Hệ <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" x-model="formData.phone" required
                                   placeholder="0983 999 444"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono text-xs font-bold text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all placeholder:text-slate-400">
                        </div>
                    </div>

                    <!-- Người Giới Thiệu -->
                    <div class="lg:col-span-3">
                        <label class="block font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-user-plus text-purple-500"></i>
                            <span>Người Giới Thiệu</span>
                        </label>
                        <select x-model="formData.referrer"
                                class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-semibold text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all cursor-pointer">
                            <option value="">-- Không có / Khách tự liên hệ --</option>
                            <option value="Đại lý giới thiệu">Đại lý khác giới thiệu</option>
                            <option value="Thợ giới thiệu">Thợ điện nước giới thiệu</option>
                            <option value="Nhân viên kinh doanh">Nhân viên kinh doanh khai thác</option>
                            <option value="Marketing / Hotline">Marketing / Hotline tổng đài</option>
                            @foreach($salesReps as $rep)
                                <option value="{{ $rep->name }}">{{ $rep->name }} ({{ $rep->department ?? 'Sales' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Phân Cấp Khách Hàng -->
                    <div class="lg:col-span-3">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Phân Cấp Khách Hàng
                        </label>
                        <select x-model="formData.tier"
                                class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-bold text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all cursor-pointer">
                            <option value="Đại lý Cấp 1">Đại lý Cấp 1 (Ưu tiên cao)</option>
                            <option value="Đại lý Cấp 2">Đại lý Cấp 2</option>
                            <option value="Bán lẻ">Khách Mua Lẻ</option>
                            <option value="Dự án">Khách Công Trình / Dự Án</option>
                            <option value="Thợ">Nhà Thầu / Thợ Điện Nước</option>
                            @if(isset($priceTiers))
                                @foreach($priceTiers as $pt)
                                    @if(!in_array($pt->name, ['Đại lý Cấp 1', 'Đại lý Cấp 2', 'Bán lẻ', 'Dự án', 'Thợ']))
                                        <option value="{{ $pt->name }}">{{ $pt->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. ĐỊA CHỈ & ĐỊA GIỚI HÀNH CHÍNH CHUẨN HỆ THỐNG -->
        <!-- ========================================== -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-black text-sm shrink-0 border border-purple-100 shadow-2xs">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900">2. Địa Chỉ & Địa Giới Hành Chính Chuẩn Hệ Thống</h2>
                    <p class="text-xs text-slate-400 font-medium">Hỗ trợ tra cứu tìm kiếm nhanh xã/phường, quận/huyện, tỉnh/tp mới sau sáp nhập và tên cũ đối chiếu</p>
                </div>
            </div>

            <div class="space-y-4 text-xs font-semibold">
                <!-- Địa Chỉ Chi Tiết (Full width) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Địa Chỉ Chi Tiết (Số nhà, tên tòa nhà, ngõ/ngách, đường phố)
                    </label>
                    <input type="text" x-model="formData.address"
                           placeholder="89 Đường Hoàng Quốc Việt"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-semibold text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all placeholder:text-slate-400">
                </div>

                <!-- Sub-box 1: Địa Giới Hành Chính Mới (Chuẩn 2 Cấp 2025 - 34 Tỉnh/TP) -->
                <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-200/80 space-y-3 shadow-2xs">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2 text-purple-900 font-bold text-xs">
                            <i class="fa-solid fa-building text-purple-600 text-sm"></i>
                            <span>Địa Giới Hành Chính Mới (Chuẩn 2 Cấp 2025 - 34 Tỉnh/TP)</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                            34 Tỉnh/TP • 3.321 Xã/Phường
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tỉnh / TP mới (34 tỉnh thành) -->
                        <div class="relative" @click.outside="openNewCity = false">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tỉnh / Thành Phố mới</label>
                            <div class="relative">
                                <input type="text" x-model="formData.new_city"
                                       @focus="openNewCity = true; filterNewProvinces()"
                                       @input="openNewCity = true; filterNewProvinces()"
                                       placeholder="Gõ tìm Tỉnh/TP mới (34 tỉnh thành)..."
                                       class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-200 focus:border-purple-500 rounded-xl font-semibold text-slate-800 outline-none shadow-2xs transition-all text-xs">
                                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1 text-slate-400">
                                    <button type="button" x-show="formData.new_city" @click="clearNewCity()" class="hover:text-rose-500 p-0.5 cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                    <button type="button" @click="openNewCity = !openNewCity; filterNewProvinces()" class="hover:text-slate-600 p-0.5 cursor-pointer">
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openNewCity ? 'rotate-180' : ''"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Dropdown 34 Tỉnh TP mới -->
                            <div x-show="openNewCity" x-cloak
                                 class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 py-1 text-xs">
                                <template x-for="p in filteredNewProvinces" :key="p.code">
                                    <div @click="selectNewCity(p)" 
                                         class="px-3.5 py-2 hover:bg-purple-50 hover:text-purple-700 cursor-pointer flex items-center justify-between transition-colors"
                                         :class="formData.new_city === p.name || formData.new_city === p.short_name ? 'bg-purple-50/70 font-bold text-purple-700' : 'text-slate-700'">
                                        <span x-text="p.name"></span>
                                        <span class="text-[10px] text-purple-600 font-mono font-bold bg-purple-50 px-1.5 py-0.5 rounded" x-text="p.short_name"></span>
                                    </div>
                                </template>
                                <div x-show="filteredNewProvinces.length === 0" class="px-3.5 py-3 text-center text-slate-400 italic">
                                    Không tìm thấy trong 34 Tỉnh/TP
                                </div>
                            </div>
                        </div>

                        <!-- Xã / Phường mới -->
                        <div class="relative" @click.outside="openNewWard = false">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Xã / Phường mới</label>
                            <div class="relative">
                                <input type="text" x-model="formData.new_ward"
                                       @focus="openNewWard = true; onFocusNewWard()"
                                       @input="openNewWard = true; filterNewWards()"
                                       placeholder="Gõ tìm Xã/Phường mới..."
                                       class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-200 focus:border-purple-500 rounded-xl font-semibold text-slate-800 outline-none shadow-2xs transition-all text-xs">
                                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1 text-slate-400">
                                    <span x-show="loadingNewWards" class="animate-spin text-purple-500 mr-0.5">
                                        <i class="fa-solid fa-circle-notch text-xs"></i>
                                    </span>
                                    <button type="button" x-show="formData.new_ward" @click="clearNewWard()" class="hover:text-rose-500 p-0.5 cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                    <button type="button" @click="openNewWard = !openNewWard; if(openNewWard) onFocusNewWard()" class="hover:text-slate-600 p-0.5 cursor-pointer">
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openNewWard ? 'rotate-180' : ''"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Dropdown danh sách gợi ý Xã / Phường mới -->
                            <div x-show="openNewWard" x-cloak
                                 class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 py-1 text-xs">
                                <div x-show="loadingNewWards" class="px-3.5 py-3 text-center text-purple-600 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-circle-notch animate-spin"></i>
                                    <span>Đang tải danh sách Xã/Phường...</span>
                                </div>
                                <template x-for="w in filteredNewWards" :key="w.code || w.name">
                                    <div @click="selectNewWard(w)" 
                                         class="px-3.5 py-2 hover:bg-purple-50 hover:text-purple-700 cursor-pointer flex items-center justify-between transition-colors"
                                         :class="formData.new_ward === w.name ? 'bg-purple-50/70 font-bold text-purple-700' : 'text-slate-700'">
                                        <span x-text="w.name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono" x-text="w.district_name || w.short_name || ''"></span>
                                    </div>
                                </template>
                                <div x-show="!loadingNewWards && filteredNewWards.length === 0" class="px-3.5 py-3 text-center text-slate-400 italic">
                                    <span x-show="!formData.new_city">Vui lòng chọn Tỉnh/TP mới để có danh sách gợi ý</span>
                                    <span x-show="formData.new_city">Không tìm thấy Xã/Phường phù hợp (vẫn có thể dùng tên vừa nhập)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sub-box 2: Địa Giới Hành Chính Cũ (Trước sáp nhập - 3 Cấp truyền thống) -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3 shadow-2xs">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs">
                            <i class="fa-solid fa-location-crosshairs text-slate-400 text-sm"></i>
                            <span>Địa Giới Hành Chính Cũ (Trước sáp nhập - 3 Cấp truyền thống)</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">
                            Đối chiếu chứng từ cũ
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Tỉnh / TP cũ -->
                        <div class="relative" @click.outside="openOldCity = false">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tỉnh / Thành Phố cũ</label>
                            <div class="relative">
                                <input type="text" x-model="formData.old_city"
                                       @focus="openOldCity = true; filterOldProvinces()"
                                       @input="openOldCity = true; filterOldProvinces()"
                                       placeholder="Gõ tìm hoặc chọn Tỉnh/TP..."
                                       class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-200 focus:border-sky-500 rounded-xl font-semibold text-slate-800 outline-none shadow-2xs transition-all text-xs">
                                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1 text-slate-400">
                                    <button type="button" x-show="formData.old_city" @click="clearOldCity()" class="hover:text-rose-500 p-0.5 cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                    <button type="button" @click="openOldCity = !openOldCity; filterOldProvinces()" class="hover:text-slate-600 p-0.5 cursor-pointer">
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openOldCity ? 'rotate-180' : ''"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Dropdown 63 Tỉnh Thành -->
                            <div x-show="openOldCity" x-cloak
                                 class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 py-1 text-xs">
                                <template x-for="p in filteredOldProvinces" :key="p.code">
                                    <div @click="selectOldCity(p)" 
                                         class="px-3.5 py-2 hover:bg-sky-50 hover:text-sky-700 cursor-pointer flex items-center justify-between transition-colors"
                                         :class="formData.old_city === p.name || formData.old_city === p.short_name ? 'bg-sky-50/70 font-bold text-sky-700' : 'text-slate-700'">
                                        <span x-text="p.name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono" x-text="p.short_name"></span>
                                    </div>
                                </template>
                                <div x-show="filteredOldProvinces.length === 0" class="px-3.5 py-3 text-center text-slate-400 italic">
                                    Không tìm thấy tỉnh thành
                                </div>
                            </div>
                        </div>

                        <!-- Quận / Huyện cũ -->
                        <div class="relative" @click.outside="openOldDistrict = false">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Quận / Huyện cũ</label>
                            <div class="relative">
                                <input type="text" x-model="formData.old_district"
                                       @focus="if(formData.old_city) { openOldDistrict = true; filterOldDistricts(); }"
                                       @input="openOldDistrict = true; filterOldDistricts()"
                                       :disabled="!formData.old_city"
                                       :placeholder="formData.old_city ? (loadingDistricts ? 'Đang tải danh sách...' : 'Chọn hoặc gõ tìm Quận/Huyện...') : 'Hãy chọn Tỉnh/TP trước'"
                                       class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-200 focus:border-sky-500 rounded-xl font-semibold text-slate-800 outline-none shadow-2xs transition-all text-xs disabled:bg-slate-100 disabled:text-slate-400">
                                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1 text-slate-400">
                                    <i x-show="loadingDistricts" class="fa-solid fa-spinner fa-spin text-xs text-sky-600"></i>
                                    <button type="button" x-show="formData.old_district && !loadingDistricts" @click="clearOldDistrict()" class="hover:text-rose-500 p-0.5 cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                    <button type="button" @click="if(formData.old_city) { openOldDistrict = !openOldDistrict; filterOldDistricts(); }" class="hover:text-slate-600 p-0.5 cursor-pointer">
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openOldDistrict ? 'rotate-180' : ''"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Dropdown Quận / Huyện -->
                            <div x-show="openOldDistrict" x-cloak
                                 class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 py-1 text-xs">
                                <template x-for="d in filteredOldDistricts" :key="d.code">
                                    <div @click="selectOldDistrict(d)" 
                                         class="px-3.5 py-2 hover:bg-sky-50 hover:text-sky-700 cursor-pointer flex items-center justify-between transition-colors"
                                         :class="formData.old_district === d.name || formData.old_district === d.short_name ? 'bg-sky-50/70 font-bold text-sky-700' : 'text-slate-700'">
                                        <span x-text="d.name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono" x-text="d.short_name"></span>
                                    </div>
                                </template>
                                <div x-show="!loadingDistricts && filteredOldDistricts.length === 0" class="px-3.5 py-3 text-center text-slate-400 italic">
                                    Không tìm thấy quận/huyện
                                </div>
                            </div>
                        </div>

                        <!-- Xã / Phường cũ -->
                        <div class="relative" @click.outside="openOldWard = false">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Xã / Phường cũ</label>
                            <div class="relative">
                                <input type="text" x-model="formData.old_ward"
                                       @focus="if(formData.old_district) { openOldWard = true; filterOldWards(); }"
                                       @input="openOldWard = true; filterOldWards()"
                                       :disabled="!formData.old_district"
                                       :placeholder="formData.old_district ? (loadingWards ? 'Đang tải danh sách...' : 'Gõ tìm Xã/Phường cũ...') : 'Hãy chọn Quận/Huyện trước'"
                                       class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-200 focus:border-sky-500 rounded-xl font-semibold text-slate-800 outline-none shadow-2xs transition-all text-xs disabled:bg-slate-100 disabled:text-slate-400">
                                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1 text-slate-400">
                                    <i x-show="loadingWards" class="fa-solid fa-spinner fa-spin text-xs text-sky-600"></i>
                                    <button type="button" x-show="formData.old_ward && !loadingWards" @click="formData.old_ward = ''" class="hover:text-rose-500 p-0.5 cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                    <button type="button" @click="if(formData.old_district) { openOldWard = !openOldWard; filterOldWards(); }" class="hover:text-slate-600 p-0.5 cursor-pointer">
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openOldWard ? 'rotate-180' : ''"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Dropdown Xã / Phường -->
                            <div x-show="openOldWard" x-cloak
                                 class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 py-1 text-xs">
                                <template x-for="w in filteredOldWards" :key="w.code">
                                    <div @click="selectOldWard(w)" 
                                         class="px-3.5 py-2 hover:bg-sky-50 hover:text-sky-700 cursor-pointer flex items-center justify-between transition-colors"
                                         :class="formData.old_ward === w.name || formData.old_ward === w.short_name ? 'bg-sky-50/70 font-bold text-sky-700' : 'text-slate-700'">
                                        <span x-text="w.name"></span>
                                        <span class="text-[10px] text-slate-400 font-mono" x-text="w.short_name"></span>
                                    </div>
                                </template>
                                <div x-show="!loadingWards && filteredOldWards.length === 0" class="px-3.5 py-3 text-center text-slate-400 italic">
                                    Không tìm thấy xã/phường
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. HẠN MỨC CÔNG NỢ & THANH TOÁN -->
        <!-- ========================================== -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-sm shrink-0 border border-emerald-100 shadow-2xs">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900">3. Hạn Mức Công Nợ & Thanh Toán</h2>
                    <p class="text-xs text-slate-400 font-medium">Cài đặt trần nợ, nợ đầu kỳ và điều khoản thanh toán</p>
                </div>
            </div>

            <div class="space-y-4 text-xs font-semibold">
                <!-- Hạn Mức Công Nợ Cấp (VNĐ) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Hạn Mức Công Nợ Cấp (VNĐ)
                    </label>
                    <input type="text" x-model="displayCreditLimit" @input="updateCreditLimit($event.target.value)"
                           class="w-full px-3.5 py-2.5 bg-emerald-50/40 focus:bg-white border border-emerald-200 rounded-xl font-mono text-sm font-black text-emerald-800 outline-none focus:border-emerald-500 shadow-2xs transition-all"
                           placeholder="250.000.000">
                    <div class="text-xs font-black text-emerald-600 mt-1 pl-0.5" x-text="formatVnd(formData.credit_limit) + ' đ'"></div>
                </div>

                <!-- Nợ Đầu Kỳ (VNĐ) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-coins text-amber-500"></i>
                        <span>Nợ Đầu Kỳ (VNĐ)</span>
                    </label>
                    <input type="text" x-model="displayInitialDebt" @input="updateInitialDebt($event.target.value)"
                           class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-mono text-sm font-bold text-slate-800 outline-none focus:border-amber-500 shadow-2xs transition-all"
                           placeholder="0">
                    <div class="text-xs font-bold text-amber-600 mt-1 pl-0.5" x-text="formatVnd(formData.initial_debt) + ' đ'"></div>
                    <p class="text-[11px] text-slate-400 mt-1 font-normal">Số dư công nợ chuyển từ kỳ/năm trước hoặc từ hệ thống sổ sách cũ.</p>
                </div>

                <!-- Thời Hạn Thanh Toán (Ngày) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Thời Hạn Thanh Toán (Ngày)
                    </label>
                    <select x-model="formData.payment_term_days"
                            class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-semibold text-slate-800 outline-none focus:border-sky-500 shadow-2xs transition-all cursor-pointer">
                        <option value="30">30 ngày (Định kỳ tháng chuẩn)</option>
                        <option value="15">15 ngày (Định kỳ nửa tháng)</option>
                        <option value="45">45 ngày (Công trình tiến độ)</option>
                        <option value="60">60 ngày (Ưu đãi đặc biệt)</option>
                        <option value="0">0 ngày (Thanh toán ngay / COD)</option>
                    </select>
                </div>

                <!-- Nhân Viên Phụ Trách * -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-user text-purple-600"></i>
                            <span>Nhân Viên Phụ Trách <span class="text-rose-500">*</span></span>
                        </label>
                        <span class="text-[11px] font-mono font-bold text-purple-700 bg-purple-50 px-2 py-0.2 rounded border border-purple-200" x-show="formData.sales_rep_id" x-text="'ID: ' + formData.sales_rep_id"></span>
                    </div>
                    <select x-model="formData.sales_rep_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-semibold text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all cursor-pointer">
                        <option value="">-- Chọn nhân viên phụ trách trực tiếp --</option>
                        @foreach($salesReps as $rep)
                            <option value="{{ $rep->id }}">
                                [ID: {{ $rep->id }} • {{ $rep->code ?: ('NV-' . str_pad($rep->id, 3, '0', STR_PAD_LEFT)) }}] {{ $rep->name }} ({{ $rep->department ?? 'Sales_Rep' }})
                            </option>
                        @endforeach
                    </select>
                    <!-- Preview nhân viên phụ trách -->
                    <div class="text-xs font-bold text-purple-700 mt-1 pl-0.5 flex items-center gap-1.5" x-show="getSelectedSalesRepName()">
                        <i class="fa-solid fa-user-check text-purple-600 text-[11px]"></i>
                        <span>Phụ trách: <strong class="text-purple-900" x-text="getSelectedSalesRepName()"></strong></span>
                    </div>
                </div>

                <!-- Ghi Chú Kinh Doanh & Điều Khoản -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Ghi Chú Kinh Doanh & Điều Khoản
                    </label>
                    <textarea x-model="formData.notes" rows="3"
                              placeholder="Ghi chú về mặt bằng cửa hàng, các chủng loại ống nhựa/thiết bị điện nước tiêu thụ chính..."
                              class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl font-normal text-slate-800 outline-none focus:border-purple-500 shadow-2xs transition-all placeholder:text-slate-400"></textarea>
                </div>
            </div>
        </div>

    </form>

    <!-- ======================================================== -->
    <!-- FLOATING SAVE BUTTON (LUÔN NỔI PHÍA GÓC DƯỚI BÊN PHẢI) -->
    <!-- ======================================================== -->
    <div class="fixed bottom-6 right-6 z-[9900] flex items-center gap-3">
        <button type="button" @click="submitForm()" :disabled="saving"
                class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-emerald-600/30 transition-all flex items-center gap-2.5 cursor-pointer active:scale-95 disabled:opacity-50 border border-emerald-400/40">
            <i x-show="saving" class="fa-solid fa-spinner fa-spin text-base"></i>
            <i x-show="!saving" class="fa-solid fa-floppy-disk text-base"></i>
            <span x-text="saving ? 'Đang lưu đại lý...' : '{{ $isEdit ? 'Lưu Thông Tin Đại Lý' : 'Tạo Mới Đại Lý' }}'"></span>
        </button>
    </div>

</div>

<script>
function dealerFormApp() {
    return {
        saving: false,
        salesReps: @json($salesReps ?? []),
        displayCreditLimit: '{{ $dealer ? number_format((float)$dealer->credit_limit, 0, ",", ".") : "250.000.000" }}',
        displayInitialDebt: '{{ $dealer ? number_format((float)($dealer->initial_debt ?? 0), 0, ",", ".") : "0" }}',

        // 1. Địa giới mới (34 Tỉnh/TP 2025)
        newProvinces: [],
        filteredNewProvinces: [],
        openNewCity: false,
        openNewWard: false,
        allNewWards: [],
        filteredNewWards: [],
        loadingNewWards: false,
        newWardSearchTimeout: null,
        lastFetchedProvinceForNewWards: '',

        // 2. Địa giới cũ (63 Tỉnh/TP 3 cấp)
        oldProvinces: [],
        filteredOldProvinces: [],
        openOldCity: false,
        selectedOldProvinceCode: null,

        oldDistricts: [],
        filteredOldDistricts: [],
        openOldDistrict: false,
        selectedOldDistrictCode: null,
        loadingDistricts: false,

        oldWards: [],
        filteredOldWards: [],
        openOldWard: false,
        loadingWards: false,

        formData: {
            id: '{{ $dealer ? $dealer->id : "" }}',
            code: '{{ $dealer ? $dealer->code : ($nextCode ?? "DL-HN-001") }}',
            name: '{{ $dealer ? addslashes($dealer->name) : "" }}',
            owner_name: '{{ $dealer ? addslashes($dealer->owner_name) : "" }}',
            phone: '{{ $dealer ? $dealer->phone : "" }}',
            tax_code: '{{ $dealer ? $dealer->tax_code : "" }}',
            referrer: '{{ $dealer ? addslashes($dealer->referrer) : "" }}',
            tier: '{{ $dealer ? ($dealer->tier ?: "Đại lý Cấp 1") : "Đại lý Cấp 1" }}',
            address: '{{ $dealer ? addslashes($dealer->address) : "" }}',
            new_city: '{{ $dealer ? addslashes($dealer->new_city) : "" }}',
            new_ward: '{{ $dealer ? addslashes($dealer->new_ward) : "" }}',
            old_city: '{{ $dealer ? addslashes($dealer->old_city ?? $dealer->city) : "" }}',
            old_district: '{{ $dealer ? addslashes($dealer->old_district ?? $dealer->district) : "" }}',
            old_ward: '{{ $dealer ? addslashes($dealer->old_ward) : "" }}',
            credit_limit: {{ $dealer ? (float)$dealer->credit_limit : 250000000 }},
            initial_debt: {{ $dealer ? (float)($dealer->initial_debt ?? 0) : 0 }},
            payment_term_days: {{ $dealer ? (int)$dealer->payment_term_days : 30 }},
            sales_rep_id: '{{ $dealer ? ($dealer->sales_rep_id ?: "") : ($salesReps->first()?->id ?? "") }}',
            notes: '{{ $dealer ? addslashes(str_replace(["\r", "\n"], ["", "\\n"], $dealer->expansion_notes ?? "")) : "" }}',
            status: '{{ $dealer ? ($dealer->status ?: "Hoạt động") : "Hoạt động" }}',
        },

        async initForm() {
            // Tải danh sách 34 Tỉnh/TP mới
            try {
                const resNew = await fetch('{{ url("/api/geo/provinces-new") }}');
                this.newProvinces = await resNew.json();
                this.filteredNewProvinces = [...this.newProvinces];

                if (this.formData.new_city) {
                    this.fetchNewWardsForProvince(this.formData.new_city);
                }
            } catch(e) {
                console.error(e);
            }

            // Tải danh sách 63 Tỉnh/TP cũ
            try {
                const resOld = await fetch('{{ url("/api/geo/provinces-old") }}');
                this.oldProvinces = await resOld.json();
                this.filteredOldProvinces = [...this.oldProvinces];

                // Nếu form đã có sẵn tỉnh cũ (đang chỉnh sửa đại lý)
                if (this.formData.old_city) {
                    const matchP = this.oldProvinces.find(p => 
                        p.name.toLowerCase() === this.formData.old_city.toLowerCase() ||
                        p.short_name.toLowerCase() === this.formData.old_city.toLowerCase() ||
                        this.formData.old_city.toLowerCase().includes(p.short_name.toLowerCase()) ||
                        p.name.toLowerCase().includes(this.formData.old_city.toLowerCase())
                    );

                    if (matchP) {
                        this.selectedOldProvinceCode = matchP.code;
                        await this.fetchDistricts(matchP.code);

                        // Nếu form có sẵn huyện cũ
                        if (this.formData.old_district) {
                            const matchD = this.oldDistricts.find(d => 
                                d.name.toLowerCase() === this.formData.old_district.toLowerCase() ||
                                d.short_name.toLowerCase() === this.formData.old_district.toLowerCase() ||
                                this.formData.old_district.toLowerCase().includes(d.short_name.toLowerCase()) ||
                                d.name.toLowerCase().includes(this.formData.old_district.toLowerCase())
                            );

                            if (matchD) {
                                this.selectedOldDistrictCode = matchD.code;
                                await this.fetchWards(matchD.code);
                            }
                        }
                    }
                }
            } catch(e) {
                console.error(e);
            }
        },

        // --- 1. TỈNH / TP MỚI ---
        filterNewProvinces() {
            const q = (this.formData.new_city || '').trim().toLowerCase();
            if (!q) {
                this.filteredNewProvinces = [...this.newProvinces];
                return;
            }
            this.filteredNewProvinces = this.newProvinces.filter(p => 
                p.name.toLowerCase().includes(q) || p.short_name.toLowerCase().includes(q)
            );
        },

        selectNewCity(province) {
            this.formData.new_city = province.name;
            this.openNewCity = false;
            this.formData.new_ward = '';
            this.fetchNewWardsForProvince(province.name);
        },

        clearNewCity() {
            this.formData.new_city = '';
            this.allNewWards = [];
            this.filteredNewWards = [];
            this.lastFetchedProvinceForNewWards = '';
            this.formData.new_ward = '';
            this.filterNewProvinces();
        },

        async fetchNewWardsForProvince(provinceName) {
            if (!provinceName) {
                this.allNewWards = [];
                this.filteredNewWards = [];
                this.lastFetchedProvinceForNewWards = '';
                return;
            }
            this.loadingNewWards = true;
            try {
                const res = await fetch(`{{ url("/api/geo/wards-new") }}?province=${encodeURIComponent(provinceName)}`);
                const data = await res.json();
                this.allNewWards = Array.isArray(data) ? data : [];
                this.lastFetchedProvinceForNewWards = provinceName;
                this.filterNewWards();
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingNewWards = false;
            }
        },

        async onFocusNewWard() {
            this.openNewWard = true;
            if (this.formData.new_city && (this.lastFetchedProvinceForNewWards !== this.formData.new_city || this.allNewWards.length === 0)) {
                await this.fetchNewWardsForProvince(this.formData.new_city);
            } else {
                this.filterNewWards();
            }
        },

        filterNewWards() {
            const rawQ = (this.formData.new_ward || '').trim();
            if (!rawQ) {
                this.filteredNewWards = [...this.allNewWards];
                return;
            }

            const removeAccents = (str) => {
                return (str || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/đ/g, 'd').replace(/Đ/g, 'D')
                    .toLowerCase();
            };

            const qLower = rawQ.toLowerCase();
            const qClean = removeAccents(rawQ);

            if (this.allNewWards.length > 0) {
                this.filteredNewWards = this.allNewWards.filter(w => {
                    const nameLower = (w.name || '').toLowerCase();
                    const nameClean = removeAccents(w.name || '');
                    const shortClean = removeAccents(w.short_name || '');
                    const distClean = removeAccents(w.district_name || '');

                    return nameLower.includes(qLower) || 
                           nameClean.includes(qClean) || 
                           shortClean.includes(qClean) || 
                           distClean.includes(qClean);
                });
            } else {
                if (this.newWardSearchTimeout) clearTimeout(this.newWardSearchTimeout);
                this.newWardSearchTimeout = setTimeout(async () => {
                    if (rawQ.length >= 2) {
                        this.loadingNewWards = true;
                        try {
                            const provParam = this.formData.new_city ? `&province=${encodeURIComponent(this.formData.new_city)}` : '';
                            const res = await fetch(`{{ url("/api/geo/wards-new") }}?q=${encodeURIComponent(rawQ)}${provParam}`);
                            this.filteredNewWards = await res.json();
                        } catch(e) {
                            console.error(e);
                        } finally {
                            this.loadingNewWards = false;
                        }
                    }
                }, 300);
            }
        },

        selectNewWard(ward) {
            this.formData.new_ward = ward.name;
            this.openNewWard = false;
            // Tự động gợi ý điền sang xã cũ nếu xã cũ chưa chọn
            if (!this.formData.old_ward) {
                this.formData.old_ward = ward.name;
            }
        },

        clearNewWard() {
            this.formData.new_ward = '';
            this.filteredNewWards = [...this.allNewWards];
        },

        // --- 2. TỈNH / TP CŨ ---
        filterOldProvinces() {
            const q = (this.formData.old_city || '').trim().toLowerCase();
            if (!q) {
                this.filteredOldProvinces = [...this.oldProvinces];
                return;
            }
            this.filteredOldProvinces = this.oldProvinces.filter(p => 
                p.name.toLowerCase().includes(q) || p.short_name.toLowerCase().includes(q)
            );
        },

        async selectOldCity(province) {
            this.formData.old_city = province.name;
            this.selectedOldProvinceCode = province.code;
            this.openOldCity = false;

            // Xóa sạch huyện, xã cũ trước đó
            this.formData.old_district = '';
            this.formData.old_ward = '';
            this.selectedOldDistrictCode = null;
            this.oldDistricts = [];
            this.filteredOldDistricts = [];
            this.oldWards = [];
            this.filteredOldWards = [];

            // Gợi ý tự động sang Tỉnh/TP mới nếu tỉnh mới chưa điền
            if (!this.formData.new_city) {
                const matchNew = this.newProvinces.find(np => 
                    np.short_name.toLowerCase() === province.short_name.toLowerCase() ||
                    province.name.toLowerCase().includes(np.short_name.toLowerCase())
                );
                if (matchNew) {
                    this.formData.new_city = matchNew.name;
                    this.fetchNewWardsForProvince(matchNew.name);
                }
            }

            await this.fetchDistricts(province.code);
        },

        clearOldCity() {
            this.formData.old_city = '';
            this.selectedOldProvinceCode = null;
            this.formData.old_district = '';
            this.selectedOldDistrictCode = null;
            this.formData.old_ward = '';
            this.oldDistricts = [];
            this.filteredOldDistricts = [];
            this.oldWards = [];
            this.filteredOldWards = [];
            this.filterOldProvinces();
        },

        // --- 3. QUẬN / HUYỆN CŨ ---
        async fetchDistricts(provinceCode) {
            this.loadingDistricts = true;
            try {
                const res = await fetch(`{{ url("/api/geo/districts-old") }}?province_code=${provinceCode}`);
                this.oldDistricts = await res.json();
                this.filteredOldDistricts = [...this.oldDistricts];
            } catch(e) {
                console.error(e);
            } finally {
                this.loadingDistricts = false;
            }
        },

        filterOldDistricts() {
            const q = (this.formData.old_district || '').trim().toLowerCase();
            if (!q) {
                this.filteredOldDistricts = [...this.oldDistricts];
                return;
            }
            this.filteredOldDistricts = this.oldDistricts.filter(d => 
                d.name.toLowerCase().includes(q) || d.short_name.toLowerCase().includes(q)
            );
        },

        async selectOldDistrict(district) {
            this.formData.old_district = district.name;
            this.selectedOldDistrictCode = district.code;
            this.openOldDistrict = false;

            // Xóa xã cũ trước đó
            this.formData.old_ward = '';
            this.oldWards = [];
            this.filteredOldWards = [];

            await this.fetchWards(district.code);
        },

        clearOldDistrict() {
            this.formData.old_district = '';
            this.selectedOldDistrictCode = null;
            this.formData.old_ward = '';
            this.oldWards = [];
            this.filteredOldWards = [];
            this.filterOldDistricts();
        },

        // --- 4. XÃ / PHƯỜNG CŨ ---
        async fetchWards(districtCode) {
            this.loadingWards = true;
            try {
                const res = await fetch(`{{ url("/api/geo/wards-old") }}?district_code=${districtCode}`);
                this.oldWards = await res.json();
                this.filteredOldWards = [...this.oldWards];
            } catch(e) {
                console.error(e);
            } finally {
                this.loadingWards = false;
            }
        },

        filterOldWards() {
            const q = (this.formData.old_ward || '').trim().toLowerCase();
            if (!q) {
                this.filteredOldWards = [...this.oldWards];
                return;
            }
            this.filteredOldWards = this.oldWards.filter(w => 
                w.name.toLowerCase().includes(q) || w.short_name.toLowerCase().includes(q)
            );
        },

        selectOldWard(ward) {
            this.formData.old_ward = ward.name;
            this.openOldWard = false;

            // Gợi ý luôn sang xã phường mới nếu đang trống
            if (!this.formData.new_ward) {
                this.formData.new_ward = ward.name;
            }
        },

        // --- HẠN MỨC & SUBMIT ---
        updateCreditLimit(rawVal) {
            const clean = rawVal.replace(/\D/g, '');
            const num = clean ? parseInt(clean, 10) : 0;
            this.formData.credit_limit = num;
            this.displayCreditLimit = new Intl.NumberFormat('vi-VN').format(num);
        },

        updateInitialDebt(rawVal) {
            const clean = rawVal.replace(/\D/g, '');
            const num = clean ? parseInt(clean, 10) : 0;
            this.formData.initial_debt = num;
            this.displayInitialDebt = new Intl.NumberFormat('vi-VN').format(num);
        },

        formatVnd(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount || 0));
        },

        getSelectedSalesRepName() {
            if (!this.formData.sales_rep_id) return '';
            const rep = this.salesReps.find(r => String(r.id) === String(this.formData.sales_rep_id));
            return rep ? rep.name : '';
        },

        async submitForm() {
            if (!this.formData.name || !this.formData.name.trim()) {
                alert('Vui lòng nhập Tên Đại Lý / Cửa Hàng!');
                return;
            }
            if (!this.formData.phone || !this.formData.phone.trim()) {
                alert('Vui lòng nhập Số Điện Thoại Liên Hệ!');
                return;
            }

            this.saving = true;

            try {
                const res = await fetch('{{ url("/dealers/save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect || '{{ url("/dealers") }}';
                } else {
                    alert(data.message || 'Lỗi khi lưu thông tin đại lý');
                    this.saving = false;
                }
            } catch (err) {
                console.error(err);
                alert('Lỗi kết nối khi gửi thông tin lên máy chủ.');
                this.saving = false;
            }
        }
    };
}
</script>
@endsection
