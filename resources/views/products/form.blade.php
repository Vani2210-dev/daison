@extends('layouts.app')

@section('title', ($product ? 'Chỉnh Sửa: ' . $product->name : 'Thêm Sản Phẩm Mới') . ' - Đại Sơn ERP')

@section('content')
<div class="space-y-4 w-full" x-data="productForm()">
    <!-- Top Action Header -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ url('/cat_products') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors shadow-2xs">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-sky-600"></i>
                    <span>{{ $product ? 'Chỉnh Sửa Thông Tin Sản Phẩm' : 'Thêm Mới Sản Phẩm' }}</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Quản lý 2 tên gọi (Thương mại & Hóa đơn), quy cách quy đổi, liên kết danh mục kho bãi và bảng giá đa tầng theo cấu hình giá ERP.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ url('/cat_products') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                Hủy Bỏ
            </a>
            <button type="button" @click="submitForm()" :disabled="isSaving" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95">
                <i x-show="isSaving" class="fa-solid fa-spinner fa-spin"></i>
                <i x-show="!isSaving" class="fa-solid fa-floppy-disk"></i>
                <span x-text="isSaving ? 'Đang lưu...' : '{{ $product ? 'Lưu Cập Nhật' : 'Xác Nhận Thêm Mới' }}'"></span>
            </button>
        </div>
    </div>

    <!-- Main Form -->
    <form @submit.prevent="submitForm()" class="space-y-4">
        @csrf
        <input type="hidden" name="id" value="{{ $product?->id }}">

        <!-- 1. THÔNG TIN ĐỊNH DANH VÀ 2 TÊN SẢN PHẨM -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xs">1</div>
                <h2 class="text-sm font-extrabold text-slate-900">Định Danh Sản Phẩm & Tên Thương Mại / Tên Hóa Đơn</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <!-- Mã SKU -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Mã SKU / Mã Sản Phẩm <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="sku" 
                        required 
                        placeholder="VD: BM-PVC-D21-C1" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-mono text-xs font-bold text-sky-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 uppercase"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Mã định danh duy nhất quản lý kho và bán hàng</p>
                </div>

                <!-- Tên Thương Mại -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Tên Thương Mại (Bán Buôn / Thường Dùng) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="name" 
                        @input="if(!tax_name_manual) tax_name = name" 
                        required 
                        placeholder="VD: Ống nhựa uPVC Ø21 Class 1 (dày 1.6mm)" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Tên hiển thị tại báo giá, đơn hàng và ứng dụng bán lẻ</p>
                </div>

                <!-- Tên Hóa Đơn -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Tên Hóa Đơn (Tên Xuất Hóa Đơn VAT) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="tax_name" 
                        @input="tax_name_manual = true" 
                        required 
                        placeholder="VD: Ống nhựa uPVC D21x1.6mm Class 1 Bình Minh" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Tên chuẩn quy định xuất hóa đơn tài chính VAT</p>
                </div>
            </div>
        </div>

        <!-- 2. LIÊN KẾT PHÂN LOẠI & THƯƠNG HIỆU -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">2</div>
                <h2 class="text-sm font-extrabold text-slate-900">Liên Kết Phân Loại Ngành Hàng & Nhãn Hiệu Nhà Sản Xuất</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <!-- Dropdown Phân Loại (Category) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Phân Loại Ngành Hàng <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        x-model="category_id" 
                        required 
                        @change="onCategoryChange()"
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 cursor-pointer"
                    >
                        <option value="">-- Chọn phân loại ngành hàng --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Liên kết trực tiếp bảng Phân loại</p>
                </div>

                <!-- Dropdown Thương Hiệu (Brand) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Thương Hiệu / Nhà Sản Xuất <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        x-model="brand_id" 
                        required 
                        @change="onBrandChange()"
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 cursor-pointer"
                    >
                        <option value="">-- Chọn thương hiệu sản xuất --</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->country ?? 'Việt Nam' }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Liên kết trực tiếp bảng Nhãn hiệu</p>
                </div>

                <!-- Dropdown Nhóm Nhỏ (Subcategory) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Nhóm Nhỏ / Nhóm Con
                    </label>
                    <select 
                        x-model="subcategory_id" 
                        @change="onSubcategoryChange()"
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 cursor-pointer"
                    >
                        <option value="">-- Chọn nhóm con (nếu có) --</option>
                        <template x-for="sub in filteredSubcategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name + (sub.code ? (' - ' + sub.code) : '')"></option>
                        </template>
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Lọc tự động theo Loại & Thương hiệu</p>
                </div>
            </div>
        </div>

        <!-- 3. ĐƠN VỊ TÍNH & TỈ LỆ QUY ĐỔI (QUY CÁCH KỸ THUẬT) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">3</div>
                <h2 class="text-sm font-extrabold text-slate-900">Đơn Vị Tính & Tỉ Lệ Quy Đổi (Quy Cách Kỹ Thuật)</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <!-- Đơn vị tính chuẩn -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Đơn Vị Tính Cơ Bản (Chuẩn) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="unit" 
                        required 
                        placeholder="VD: cây, cuộn, ống, thùng, cái..." 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Đơn vị quản lý nhập xuất kho chính</p>
                </div>

                <!-- Đơn vị tính quy đổi -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Đơn Vị Tính Quy Đổi (Bán lẻ / Đo lường)
                    </label>
                    <input 
                        type="text" 
                        x-model="conversion_unit" 
                        placeholder="VD: mét, kg, cái..." 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Đơn vị quy đổi để cắt lẻ hoặc đo đạc (nếu có)</p>
                </div>

                <!-- Tỉ lệ quy đổi (Quy cách kỹ thuật) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Tỉ Lệ Quy Đổi (Quy Cách Kỹ Thuật)
                    </label>
                    <input 
                        type="text" 
                        inputmode="decimal" 
                        x-number="conversion_rate" 
                        placeholder="VD: 4 (1 cây = 4 mét) hoặc 100" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold text-sky-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Hệ số quy đổi: 1 đơn vị cơ bản bằng bao nhiêu đơn vị quy đổi</p>
                </div>
            </div>

            <!-- Live Preview Box: Hiển thị quy cách quy đổi trực quan -->
            <div class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <div class="text-xs">
                    <div class="font-bold text-emerald-900">Quy ước quy cách kỹ thuật & tỷ lệ quy đổi:</div>
                    <div class="text-emerald-800 font-mono mt-0.5" x-show="conversion_unit && conversion_rate > 1">
                        1 <span class="font-bold" x-text="unit || 'ĐVT chuẩn'"></span> = <span class="font-extrabold text-emerald-900" x-text="conversion_rate"></span> <span class="font-bold" x-text="conversion_unit"></span>
                    </div>
                    <div class="text-slate-500 text-[11px]" x-show="!conversion_unit || conversion_rate <= 1">
                        Chưa thiết lập đơn vị quy đổi (Mặc định tính theo 1 <span x-text="unit || 'sản phẩm'"></span>)
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. BẢNG GIÁ PHÂN KHÚC: CÀI ĐẶT THEO CẤU HÌNH GIÁ VÀ ĐƠN VỊ TÍNH / ĐƠN VỊ QUY ĐỔI -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">4</div>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">Bảng Giá Phân Khúc Bán Hàng (Đồng Bộ Cấu Hình Giá)</h2>
                        <p class="text-[11px] text-slate-500">Cài đặt theo Đơn vị tính chuẩn và Đơn vị quy đổi cho từng phân khúc giá</p>
                    </div>
                </div>
                <a href="{{ url('/cat_price_tiers') }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    <span>Mở trang Cấu hình giá</span>
                </a>
            </div>

            <!-- Giá Vốn BQ (Cost Price) -->
            <div class="p-4 bg-amber-50/60 border border-amber-200 rounded-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold text-amber-950 flex items-center gap-1.5">
                        <i class="fa-solid fa-coins text-amber-600"></i>
                        <span>Giá Vốn Bình Quân (Cost Price)</span>
                    </span>
                    <span class="text-[11px] text-amber-800 font-medium">Làm cơ sở kiểm soát lợi nhuận gộp</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-amber-900 mb-1">
                            Giá Vốn theo <span class="text-slate-900 font-extrabold" x-text="'[' + (unit || 'ĐVT chuẩn') + ']'"></span> (VNĐ)
                        </label>
                        <input 
                            type="text" 
                            inputmode="numeric"
                            x-money="cost_price" 
                            placeholder="0" 
                            class="w-full px-3.5 py-2.5 bg-white border border-amber-300 rounded-xl font-mono text-xs font-black text-amber-950 text-right outline-none focus:ring-2 focus:ring-amber-500/30"
                        >
                    </div>
                    <div>
                        <label class="block font-bold text-amber-900 mb-1">
                            Giá Vốn theo <span class="text-slate-900 font-extrabold" x-text="'[' + (conversion_unit || 'ĐVT quy đổi') + ']'"></span> (VNĐ)
                        </label>
                        <input 
                            type="text" 
                            readonly 
                            :value="calcConversionPrice(cost_price)" 
                            class="w-full px-3.5 py-2.5 bg-amber-100/50 border border-amber-300 rounded-xl font-mono text-xs font-bold text-amber-900 text-right outline-none select-none cursor-not-allowed"
                        >
                    </div>
                </div>
            </div>

            <!-- Các Mức Giá Bán Theo Cấu Hình Giá -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] border-b border-slate-200">
                            <th class="py-3 px-3 w-12 text-center">STT</th>
                            <th class="py-3 px-4 min-w-[200px]">Tên Mức Giá (Theo Cấu Hình Giá)</th>
                            <th class="py-3 px-4 min-w-[220px] text-right bg-sky-50/80 text-sky-900 font-extrabold border-x border-sky-200">
                                Giá Bán theo <span x-text="'[' + (unit || 'ĐVT Chuẩn') + ']'"></span> (VNĐ)
                            </th>
                            <th class="py-3 px-4 min-w-[220px] text-right bg-emerald-50/80 text-emerald-900 font-extrabold">
                                Giá Bán theo <span x-text="'[' + (conversion_unit || 'ĐVT Quy Đổi') + ']'"></span> (VNĐ)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(tier, idx) in price_tiers" :key="tier.name">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-3 text-center text-slate-400 font-bold" x-text="idx + 1"></td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-tag text-sky-500 text-xs"></i>
                                        <template x-if="tier.code">
                                             <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-mono font-bold text-[10px]" x-text="tier.code"></span>
                                        </template>
                                        <span class="font-extrabold text-slate-900 text-xs" x-text="tier.name"></span>
                                    </div>
                                </td>
                                <!-- Giá theo ĐVT Chuẩn -->
                                <td class="py-2.5 px-4 text-right bg-sky-50/30 border-x border-sky-100">
                                    <input 
                                        type="text" 
                                        inputmode="numeric"
                                        x-money="tier.price" 
                                        @input="onTierPriceChange(tier)" 
                                        placeholder="0" 
                                        class="w-full px-3 py-1.5 bg-white border border-sky-300 rounded-lg font-mono text-xs font-bold text-sky-950 text-right outline-none focus:ring-2 focus:ring-sky-500/30"
                                    >
                                </td>
                                <!-- Giá theo ĐVT Quy đổi -->
                                <td class="py-2.5 px-4 text-right bg-emerald-50/30">
                                    <input 
                                        type="text" 
                                        inputmode="numeric"
                                        x-money="tier.conversion_price" 
                                        placeholder="0" 
                                        class="w-full px-3 py-1.5 bg-white border border-emerald-300 rounded-lg font-mono text-xs font-bold text-emerald-950 text-right outline-none focus:ring-2 focus:ring-emerald-500/30"
                                    >
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <p class="text-[11px] text-slate-400 italic">
                * Khi nhập Giá theo ĐVT Chuẩn, hệ thống sẽ tự động tính gợi ý Giá theo ĐVT Quy Đổi dựa trên tỉ lệ quy đổi. Bạn có thể tự do điều chỉnh từng ô.
            </p>
        </div>

        <!-- 5. TỒN KHO, KHO LƯU TRỮ, THUẾ SUẤT VAT & THIẾT LẬP -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">5</div>
                <h2 class="text-sm font-extrabold text-slate-900">Thiết Lập Kho Lưu Trữ, Tồn Kho & Thuế Suất VAT</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 text-xs">
                <!-- Kho Lưu Trữ (Liên kết bảng Warehouses) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Kho Hàng Lưu Trữ <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        x-model="warehouse_name" 
                        @change="updateLocation()"
                        required
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 outline-none focus:border-sky-500 cursor-pointer"
                    >
                        <option value="">-- Chọn kho hàng --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->name }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Liên kết trực tiếp với bảng Danh mục Kho hàng</p>
                </div>

                <!-- Vị trí kệ / ô chi tiết -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Kệ / Ngăn Chi Tiết Trong Kho
                    </label>
                    <input 
                        type="text" 
                        x-model="rack_detail" 
                        @input="updateLocation()"
                        placeholder="VD: Kệ A-02, Bãi Ngoài Ô 4" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs outline-none focus:border-sky-500"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Vị trí thực địa bên trong kho hàng</p>
                </div>

                <!-- Tồn kho ban đầu -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Tồn Kho Ban Đầu (Theo <span x-text="unit || 'ĐVT'"></span>)
                    </label>
                    <input 
                        type="text" 
                        inputmode="numeric"
                        x-money="stock_quantity" 
                        placeholder="0" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-mono text-xs font-bold text-right outline-none focus:border-sky-500"
                    >
                </div>

                <!-- Cảnh báo tồn tối thiểu -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Báo Động Tồn Tối Thiểu
                    </label>
                    <input 
                        type="text" 
                        inputmode="numeric"
                        x-money="min_stock_alert" 
                        placeholder="10" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-mono text-xs font-bold text-right outline-none focus:border-sky-500"
                    >
                </div>

                <!-- Cảnh báo tồn tối đa -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Báo Động Tồn Tối Đa
                    </label>
                    <input 
                        type="text" 
                        inputmode="numeric"
                        x-money="max_stock_alert" 
                        placeholder="VD: 500" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-mono text-xs font-bold text-right outline-none focus:border-sky-500"
                    >
                </div>

                <!-- Thuế suất VAT -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Thuế Suất VAT (%)
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            x-model.number="vat_rate" 
                            min="0" 
                            max="100" 
                            step="any" 
                            placeholder="10" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-mono text-xs font-bold text-right pr-8 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-xs pointer-events-none">%</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active_checkbox" x-model="is_active" class="w-4 h-4 text-sky-600 rounded cursor-pointer">
                    <label for="is_active_checkbox" class="text-xs font-bold text-slate-800 cursor-pointer">
                        Kích hoạt kinh doanh sản phẩm này
                    </label>
                </div>

                <div class="flex items-center gap-2.5">
                    <a href="{{ url('/cat_products') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                        Hủy Bỏ
                    </a>
                    <button type="submit" :disabled="isSaving" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-black text-xs rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95">
                        <i x-show="isSaving" class="fa-solid fa-spinner fa-spin"></i>
                        <i x-show="!isSaving" class="fa-solid fa-floppy-disk"></i>
                        <span x-text="isSaving ? 'Đang lưu...' : '{{ $product ? 'Lưu Cập Nhật Sản Phẩm' : 'Xác Nhận Thêm Sản Phẩm' }}'"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function productForm() {
    const rawProduct = @json($product ?? null);
    const masterTiers = @json($priceTiers ?? []);
    const warehousesList = @json($warehouses ?? []);
    const subcategoriesList = @json($subcategories ?? []);

    // Build initial price tiers synced with Master Price Tiers
    let initialTiers = [];
    const existingTiers = (rawProduct && Array.isArray(rawProduct.price_tiers)) ? rawProduct.price_tiers : [];

    masterTiers.forEach(m => {
        const found = existingTiers.find(t => (t.name || '').toLowerCase() === (m.name || '').toLowerCase());
        initialTiers.push({
            code: m.code || '',
            name: m.name,
            price: found ? Number(found.price || 0) : 0,
            conversion_price: found ? Number(found.conversion_price || 0) : 0
        });
    });

    // If no master tiers were found, fallback to defaults
    if (initialTiers.length === 0) {
        initialTiers = [
            { name: 'Giá Đại lý Cấp 1', price: 0, conversion_price: 0 },
            { name: 'Giá Đại lý Cấp 2', price: 0, conversion_price: 0 },
            { name: 'Giá Bán lẻ', price: 0, conversion_price: 0 },
            { name: 'Giá Dự án / Công trình', price: 0, conversion_price: 0 },
            { name: 'Giá Thợ thi công', price: 0, conversion_price: 0 }
        ];
    }

    // Parse initial warehouse and rack location
    const rawLoc = rawProduct?.location || '';
    let initWarehouse = '';
    let initRack = '';

    if (rawLoc) {
        const matchedWh = warehousesList.find(w => rawLoc.toLowerCase().startsWith(w.name.toLowerCase()));
        if (matchedWh) {
            initWarehouse = matchedWh.name;
            initRack = rawLoc.slice(matchedWh.name.length).replace(/^[\s\-–—:]+/, '').trim();
        } else if (rawLoc.includes(' - ')) {
            const parts = rawLoc.split(' - ');
            initWarehouse = parts[0].trim();
            initRack = parts.slice(1).join(' - ').trim();
        } else {
            initWarehouse = rawLoc;
        }
    }
    if (!initWarehouse && warehousesList.length > 0) {
        initWarehouse = warehousesList[0].name;
    }

    return {
        id: rawProduct?.id || null,
        sku: rawProduct?.sku || '',
        name: rawProduct?.name || '',
        tax_name: rawProduct?.tax_name || rawProduct?.name || '',
        tax_name_manual: !!(rawProduct?.tax_name && rawProduct.tax_name !== rawProduct.name),
        category_id: rawProduct?.category_id || '',
        brand_id: rawProduct?.brand_id || '',
        subcategory_id: rawProduct?.subcategory_id || '',
        subcategory: rawProduct?.subcategory || '',
        subcategoriesList: subcategoriesList,
        unit: rawProduct?.unit || 'cây',
        conversion_unit: rawProduct?.conversion_unit || 'mét',
        conversion_rate: Number(rawProduct?.conversion_rate) || 4,
        cost_price: Number(rawProduct?.cost_price) || 0,
        stock_quantity: Number(rawProduct?.stock_quantity) || 0,
        min_stock_alert: Number(rawProduct?.min_stock_alert) || 10,
        max_stock_alert: (rawProduct?.max_stock_alert !== null && rawProduct?.max_stock_alert !== undefined) ? Number(rawProduct.max_stock_alert) : '',
        vat_rate: rawProduct?.vat_rate !== undefined ? Number(rawProduct.vat_rate) : 10,
        warehouse_name: initWarehouse,
        rack_detail: initRack,
        location: rawLoc || initWarehouse,
        is_active: rawProduct?.is_active !== undefined ? !!rawProduct.is_active : true,
        price_tiers: initialTiers,
        isSaving: false,

        get filteredSubcategories() {
            if (!this.category_id && !this.brand_id) return this.subcategoriesList;
            return this.subcategoriesList.filter(s => {
                const matchCat = !this.category_id || s.category_id == this.category_id;
                const matchBrand = !this.brand_id || !s.brand_id || s.brand_id == this.brand_id;
                return matchCat && matchBrand;
            });
        },

        onCategoryChange() {
            if (this.subcategory_id) {
                const sub = this.subcategoriesList.find(s => s.id == this.subcategory_id);
                if (sub && sub.category_id != this.category_id) {
                    this.subcategory_id = '';
                    this.subcategory = '';
                }
            }
        },

        onBrandChange() {
            if (this.subcategory_id) {
                const sub = this.subcategoriesList.find(s => s.id == this.subcategory_id);
                if (sub && sub.brand_id && sub.brand_id != this.brand_id) {
                    this.subcategory_id = '';
                    this.subcategory = '';
                }
            }
        },

        onSubcategoryChange() {
            const sub = this.subcategoriesList.find(s => s.id == this.subcategory_id);
            if (sub) {
                this.subcategory = sub.name;
            } else {
                this.subcategory = '';
            }
        },

        updateLocation() {
            if (this.warehouse_name) {
                this.location = this.rack_detail ? (this.warehouse_name + ' - ' + this.rack_detail) : this.warehouse_name;
            } else {
                this.location = this.rack_detail || '';
            }
        },

        calcConversionPrice(amount) {
            const num = Number(amount || 0);
            const rate = Number(this.conversion_rate || 1);
            if (!this.conversion_unit || rate <= 1 || num <= 0) return '---';
            const unitPrice = Math.round(num / rate);
            return new Intl.NumberFormat('vi-VN').format(unitPrice) + ' đ';
        },

        onTierPriceChange(tier) {
            const rate = Number(this.conversion_rate || 1);
            if (this.conversion_unit && rate > 1 && tier.price > 0) {
                // Auto compute conversion price as price / conversion_rate
                tier.conversion_price = Math.round(tier.price / rate);
            }
        },

        async submitForm() {
            if (!this.sku.trim()) {
                Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng nhập Mã SKU sản phẩm!' });
                return;
            }
            if (!this.name.trim()) {
                Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng nhập Tên thương mại của sản phẩm!' });
                return;
            }
            if (!this.category_id) {
                Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng chọn Phân loại ngành hàng!' });
                return;
            }
            if (!this.brand_id) {
                Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng chọn Thương hiệu sản xuất!' });
                return;
            }
            if (!this.unit.trim()) {
                Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng nhập Đơn vị tính cơ bản!' });
                return;
            }
            if (!this.warehouse_name) {
                Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng chọn Kho hàng lưu trữ!' });
                return;
            }

            this.updateLocation();
            this.isSaving = true;

            const rate = Number(this.conversion_rate || 1);
            const autoSpec = (this.conversion_unit && rate > 1) 
                ? (`1 ${this.unit} = ${rate} ${this.conversion_unit}`) 
                : (this.unit || '');

            const payload = {
                id: this.id,
                sku: this.sku,
                name: this.name,
                tax_name: this.tax_name || this.name,
                category_id: this.category_id,
                brand_id: this.brand_id,
                subcategory_id: this.subcategory_id || null,
                subcategory: this.subcategory || '',
                unit: this.unit,
                conversion_unit: this.conversion_unit,
                conversion_rate: this.conversion_rate,
                specification: autoSpec,
                cost_price: this.cost_price,
                stock_quantity: this.stock_quantity,
                min_stock_alert: this.min_stock_alert,
                max_stock_alert: (this.max_stock_alert !== '' && this.max_stock_alert !== null) ? Number(this.max_stock_alert) : null,
                vat_rate: this.vat_rate,
                location: this.location,
                is_active: this.is_active,
                price_tiers: this.price_tiers
            };

            try {
                const response = await fetch('{{ url('/cat_products/save') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: result.message || 'Lưu sản phẩm thành công!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.href = '{{ url('/cat_products') }}';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể lưu',
                        text: result.message || 'Có lỗi xảy ra khi lưu sản phẩm.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối',
                    text: 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra và thử lại!'
                });
            } finally {
                this.isSaving = false;
            }
        }
    };
}
</script>
@endsection
