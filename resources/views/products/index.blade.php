@extends('layouts.app')

@section('title', 'Danh Mục Sản Phẩm Hàng Tiêu Dùng & Bảng Giá - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="productsManager()" x-init="initData()">
    
    <!-- Sub-Section Navigation Tabs (Phân Hệ Hàng Tiêu Dùng Đại Sơn) -->
    @include('inventory.nav_tabs')

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-sky-600"></i>
                <span>Danh Mục Sản Phẩm & Bảng Giá</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Quản lý thông tin hàng hóa, kho bãi lưu trữ, theo dõi giá vốn và giá bán sản phẩm.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" @click="showImportModal = true" class="px-3.5 py-2 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Import Excel</span>
            </button>
            <a href="{{ url('/cat_products/create') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Thêm Sản Phẩm Mới</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <form method="GET" action="{{ url('/cat_products') }}" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-2.5 flex-1 min-w-[280px]">
            <!-- Search Input -->
            <div class="relative flex-1 min-w-[220px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input 
                    type="text" 
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Tìm theo tên thương mại, tên hóa đơn, mã SKU, quy cách..." 
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all outline-none"
                >
            </div>

            <!-- Filter Category (Linked with Categories Table) -->
            <select name="category_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer focus:border-sky-500">
                <option value="">Tất cả phân loại</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <!-- Filter Brand (Linked with Brands Table) -->
            <select name="brand_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none cursor-pointer focus:border-sky-500">
                <option value="">Tất cả thương hiệu</option>
                @foreach($brands as $b)
                    <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-filter"></i>
                <span>Lọc</span>
            </button>
            @if(request('search') || request('category_id') || request('brand_id'))
                <a href="{{ url('/cat_products') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-medium transition-colors" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <!-- Hint: Drag to scroll -->
            <span class="hidden md:inline-flex items-center gap-1.5 text-[11px] text-slate-500 bg-slate-100/80 px-2.5 py-1 rounded-lg border border-slate-200/80 font-medium">
                <i class="fa-solid fa-arrows-left-right text-slate-400 text-xs"></i>
                <span>Giữ chuột kéo sang ngang để xem đủ cột</span>
            </span>

            <!-- Price View Mode Switcher -->
            <div class="inline-flex items-center bg-slate-100 p-0.5 rounded-xl border border-slate-200 text-xs">
                <button type="button" @click="priceViewMode = 'stacked'" :class="priceViewMode === 'stacked' ? 'bg-white text-emerald-700 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer" title="Hiển thị Giá Chuẩn & Giá Quy Đổi trong cùng 1 cột">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                    <span>Gộp Chuẩn & QĐ</span>
                </button>
                <button type="button" @click="priceViewMode = 'split'" :class="priceViewMode === 'split' ? 'bg-white text-emerald-700 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer" title="Tách riêng cột Giá Chuẩn và Giá Quy Đổi">
                    <i class="fa-solid fa-table-columns text-xs"></i>
                    <span>Tách Cột Riêng</span>
                </button>
            </div>

            <div class="text-slate-500 text-xs font-medium">
                Trang {{ $products->currentPage() }} / {{ $products->lastPage() }} &bull; Tổng: <strong class="text-slate-900 font-bold">{{ $products->total() }}</strong> sản phẩm
            </div>
        </div>
    </form>

    <!-- Main Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto table-scroll-container">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3 text-center w-12">STT</th>
                        <th class="py-3 px-3 whitespace-nowrap">Mã SKU</th>
                        <th class="py-3 px-4 min-w-[240px]">Tên Sản Phẩm (Thương Mại & Hóa Đơn)</th>
                        <th class="py-3 px-3 whitespace-nowrap">Phân Loại</th>
                        <th class="py-3 px-3 whitespace-nowrap">Thương Hiệu</th>
                        <th class="py-3 px-3 whitespace-nowrap text-center">ĐVT Chuẩn</th>
                        <th class="py-3 px-3 whitespace-nowrap">Quy Đổi & Quy Cách</th>

                        <!-- Stacked Price Headers -->
                        <template x-if="priceViewMode === 'stacked'">
                            <th class="py-3 px-3 text-right whitespace-nowrap text-amber-900 bg-amber-50/80 font-bold border-x border-amber-200/60">
                                <div>GIÁ VỐN</div>
                                <div class="text-[9px] font-semibold text-amber-700/80 normal-case">Chuẩn &bull; Quy đổi</div>
                            </th>
                        </template>
                        <template x-if="priceViewMode === 'stacked'">
                            <th class="py-3 px-3 text-right whitespace-nowrap text-sky-900 bg-sky-50/80 font-bold border-r border-sky-200/60">
                                <div>GIÁ BÁN</div>
                                <div class="text-[9px] font-semibold text-sky-700/80 normal-case">Chuẩn &bull; Quy đổi</div>
                            </th>
                        </template>

                        <!-- Split Price Headers -->
                        <template x-if="priceViewMode === 'split'">
                            <th class="py-3 px-3 text-right whitespace-nowrap text-amber-900 bg-amber-50/80 font-bold border-l border-amber-200/60">VỐN (CHUẨN)</th>
                        </template>
                        <template x-if="priceViewMode === 'split'">
                            <th class="py-3 px-3 text-right whitespace-nowrap text-emerald-800 bg-emerald-50/80 font-bold border-r border-amber-200/60">VỐN (QUY ĐỔI)</th>
                        </template>
                        <template x-if="priceViewMode === 'split'">
                            <th class="py-3 px-3 text-right whitespace-nowrap text-sky-900 bg-sky-50/80 font-bold">GIÁ BÁN (CHUẨN)</th>
                        </template>
                        <template x-if="priceViewMode === 'split'">
                            <th class="py-3 px-3 text-right whitespace-nowrap text-emerald-800 bg-emerald-50/80 font-bold border-r border-sky-200/60">GIÁ BÁN (QĐ)</th>
                        </template>

                        <th class="py-3 px-3 text-center whitespace-nowrap">VAT</th>
                        <th class="py-3 px-3 text-right whitespace-nowrap">
                            <div>TỒN KHO</div>
                            <div class="text-[9px] font-semibold text-slate-500 normal-case">Min &bull; Max</div>
                        </th>
                        <th class="py-3 px-3 text-center whitespace-nowrap w-24">Trạng Thái</th>
                        <th class="sticky right-0 bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[11px] py-3 px-4 text-center w-24 z-20 shadow-[-4px_0_8px_rgba(0,0,0,0.05)] border-l border-slate-200">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(item, idx) in filteredProducts" :key="item.id">
                        <tr class="group hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 text-center text-slate-400 font-medium" x-text="idx + 1"></td>
                            <td class="py-3 px-3 font-mono font-bold text-sky-800 whitespace-nowrap" x-text="item.sku"></td>
                            
                            <!-- 2 Tên: Tên thương mại & Tên hóa đơn -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="text-slate-900 font-bold text-xs sm:text-sm" x-text="item.name"></span>
                                    <template x-if="item.tax_name && item.tax_name !== item.name">
                                        <span class="text-[11px] text-slate-500 font-normal italic mt-0.5" x-text="'HĐ: ' + item.tax_name"></span>
                                    </template>
                                </div>
                            </td>

                            <!-- Phân loại liên kết bảng categories -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold text-[11px]" x-text="getCategoryName(item)"></span>
                            </td>

                            <!-- Thương hiệu liên kết bảng brands -->
                            <td class="py-3 px-3 whitespace-nowrap font-bold text-slate-800">
                                <span x-text="getBrandName(item)"></span>
                            </td>

                            <!-- ĐVT chuẩn -->
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-800 font-bold text-xs" x-text="item.unit"></span>
                            </td>

                            <!-- ĐVT quy đổi & Tỉ lệ quy cách -->
                            <td class="py-3 px-3 whitespace-nowrap text-xs">
                                <template x-if="item.conversion_unit && Number(item.conversion_rate) > 1">
                                    <div class="flex flex-col">
                                        <span class="text-emerald-700 font-bold font-mono" x-text="'1 ' + item.unit + ' = ' + Number(item.conversion_rate) + ' ' + item.conversion_unit"></span>
                                        <span x-show="item.specification" class="text-[10px] text-slate-400" x-text="item.specification"></span>
                                    </div>
                                </template>
                                <template x-if="!item.conversion_unit || Number(item.conversion_rate) <= 1">
                                    <span class="text-slate-400" x-text="item.specification || '-'"></span>
                                </template>
                            </td>

                            <!-- =================== STACKED PRICE MODE CELLS =================== -->
                            <!-- Giá Vốn (Stacked) -->
                            <template x-if="priceViewMode === 'stacked'">
                                <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap bg-amber-50/40 border-x border-amber-100">
                                    <div class="font-bold text-amber-950 flex items-center justify-end gap-1">
                                        <span x-text="formatNumber(item.cost_price)"></span>
                                        <span class="text-[10px] font-normal text-amber-800">đ</span>
                                        <span class="text-[10px] font-normal text-amber-700" x-show="item.unit" x-text="'/' + item.unit"></span>
                                    </div>
                                    <template x-if="hasDiffPrice(item.cost_price, getCostConversionPrice(item))">
                                        <div class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60 mt-0.5">
                                            <span class="text-[9px] uppercase tracking-wider text-emerald-600 font-bold">QĐ:</span>
                                            <span x-text="formatNumber(getCostConversionPrice(item))"></span>
                                            <span class="text-[9px]">đ</span>
                                            <span x-show="item.conversion_unit" x-text="'/' + item.conversion_unit"></span>
                                        </div>
                                    </template>
                                </td>
                            </template>

                            <!-- Giá Bán (Stacked) -->
                            <template x-if="priceViewMode === 'stacked'">
                                <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap bg-sky-50/40 border-r border-sky-100">
                                    <div class="font-bold text-sky-950 flex items-center justify-end gap-1">
                                        <span x-text="formatNumber(getSellingPrice(item))"></span>
                                        <span class="text-[10px] font-normal text-sky-800">đ</span>
                                        <span class="text-[10px] font-normal text-sky-700" x-show="item.unit" x-text="'/' + item.unit"></span>
                                    </div>
                                    <template x-if="hasDiffPrice(getSellingPrice(item), getSellingConversionPrice(item))">
                                        <div class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60 mt-0.5">
                                            <span class="text-[9px] uppercase tracking-wider text-emerald-600 font-bold">QĐ:</span>
                                            <span x-text="formatNumber(getSellingConversionPrice(item))"></span>
                                            <span class="text-[9px]">đ</span>
                                            <span x-show="item.conversion_unit" x-text="'/' + item.conversion_unit"></span>
                                        </div>
                                    </template>
                                </td>
                            </template>

                            <!-- =================== SPLIT PRICE MODE CELLS =================== -->
                            <!-- Vốn (Chuẩn & QĐ) -->
                            <template x-if="priceViewMode === 'split'">
                                <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap bg-amber-50/40 border-l border-amber-100 font-bold text-amber-950">
                                    <span x-text="formatNumber(item.cost_price)"></span>
                                    <span class="text-[10px] font-normal text-amber-800">đ</span>
                                    <span class="text-[10px] font-normal text-amber-700" x-show="item.unit" x-text="'/' + item.unit"></span>
                                </td>
                            </template>
                            <template x-if="priceViewMode === 'split'">
                                <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap bg-emerald-50/40 border-r border-amber-100">
                                    <template x-if="hasDiffPrice(item.cost_price, getCostConversionPrice(item))">
                                        <span class="font-bold text-emerald-800">
                                            <span x-text="formatNumber(getCostConversionPrice(item))"></span>
                                            <span class="text-[10px] font-normal text-emerald-600">đ</span>
                                            <span class="text-[10px] font-normal text-emerald-600" x-show="item.conversion_unit" x-text="'/' + item.conversion_unit"></span>
                                        </span>
                                    </template>
                                    <template x-if="!hasDiffPrice(item.cost_price, getCostConversionPrice(item))">
                                        <span class="text-slate-300 font-normal">-</span>
                                    </template>
                                </td>
                            </template>

                            <!-- Giá Bán (Chuẩn & QĐ) -->
                            <template x-if="priceViewMode === 'split'">
                                <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap bg-sky-50/40 font-bold text-sky-950">
                                    <span x-text="formatNumber(getSellingPrice(item))"></span>
                                    <span class="text-[10px] font-normal text-sky-800">đ</span>
                                    <span class="text-[10px] font-normal text-sky-700" x-show="item.unit" x-text="'/' + item.unit"></span>
                                </td>
                            </template>
                            <template x-if="priceViewMode === 'split'">
                                <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap bg-emerald-50/40 border-r border-sky-100">
                                    <template x-if="hasDiffPrice(getSellingPrice(item), getSellingConversionPrice(item))">
                                        <span class="font-bold text-emerald-800">
                                            <span x-text="formatNumber(getSellingConversionPrice(item))"></span>
                                            <span class="text-[10px] font-normal text-emerald-600">đ</span>
                                            <span class="text-[10px] font-normal text-emerald-600" x-show="item.conversion_unit" x-text="'/' + item.conversion_unit"></span>
                                        </span>
                                    </template>
                                    <template x-if="!hasDiffPrice(getSellingPrice(item), getSellingConversionPrice(item))">
                                        <span class="text-slate-300 font-normal">-</span>
                                    </template>
                                </td>
                            </template>

                            <!-- VAT -->
                            <td class="py-3 px-3 text-center font-mono whitespace-nowrap text-slate-600" x-text="(item.vat_rate ?? 10) + '%'"></td>

                            <!-- Tồn Kho & Định mức Min/Max -->
                            <td class="py-3 px-3 text-right font-mono whitespace-nowrap">
                                <div class="flex flex-col items-end">
                                    <span :class="Number(item.stock_quantity || 0) <= Number(item.min_stock_alert || 0) && Number(item.min_stock_alert || 0) > 0 ? 'text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 font-bold' : (Number(item.max_stock_alert) > 0 && Number(item.stock_quantity || 0) > Number(item.max_stock_alert) ? 'text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200 font-bold' : 'text-slate-900 font-bold')" x-text="formatNumber(item.stock_quantity || 0)"></span>
                                    <div class="text-[10px] text-slate-400 font-normal mt-0.5 flex items-center justify-end gap-1">
                                        <span :class="Number(item.min_stock_alert || 0) > 0 ? 'text-amber-700 font-medium' : 'text-slate-400'" title="Định mức tồn tối thiểu">Min: <span x-text="formatNumber(item.min_stock_alert || 0)"></span></span>
                                        <template x-if="item.max_stock_alert !== null && item.max_stock_alert !== undefined && item.max_stock_alert !== ''">
                                            <span class="text-slate-300">&bull;</span>
                                        </template>
                                        <template x-if="item.max_stock_alert !== null && item.max_stock_alert !== undefined && item.max_stock_alert !== ''">
                                            <span :class="Number(item.max_stock_alert) > 0 ? 'text-purple-700 font-medium' : 'text-slate-400'" title="Định mức tồn tối đa">Max: <span x-text="formatNumber(item.max_stock_alert)"></span></span>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            <!-- Trạng Thái -->
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span x-show="item.is_active !== false" class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-[10px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Hoạt động</span>
                                </span>
                                <span x-show="item.is_active === false" class="inline-flex items-center px-2.5 py-0.5 bg-slate-100 text-slate-600 border border-slate-300 rounded-full font-bold text-[10px]">
                                    <span>Tạm ngưng</span>
                                </span>
                            </td>

                            <!-- Thao Tác (Trang riêng) - Cố định bên phải -->
                            <td class="sticky right-0 bg-white group-hover:bg-slate-50 transition-colors py-3 px-4 text-center whitespace-nowrap z-10 shadow-[-4px_0_8px_rgba(0,0,0,0.05)] border-l border-slate-200/80">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a :href="'{{ url('/cat_products') }}/' + item.id + '/edit'" class="p-1.5 bg-slate-100 hover:bg-sky-100 text-slate-600 hover:text-sky-700 rounded-lg transition-colors cursor-pointer" title="Chỉnh sửa sản phẩm (Trang riêng)">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" @click="deleteProduct(item)" class="p-1.5 bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-700 rounded-lg transition-colors cursor-pointer" title="Xóa sản phẩm">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredProducts.length === 0">
                        <td :colspan="priceViewMode === 'split' ? 17 : 15" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-3xl text-slate-300 mb-2"></i>
                            <p class="font-bold text-slate-700">Không tìm thấy sản phẩm phù hợp!</p>
                            <p class="text-xs text-slate-400 mt-1">Hãy thử đổi từ khóa tìm kiếm hoặc bấm "Thêm Sản Phẩm Mới".</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer (20 items per page) -->
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Hiển thị từ <strong class="text-slate-800">{{ $products->firstItem() ?? 0 }}</strong> đến <strong class="text-slate-800">{{ $products->lastItem() ?? 0 }}</strong> trong tổng số <strong class="text-slate-800">{{ $products->total() }}</strong> sản phẩm (20 sản phẩm / trang)
            </div>
            @if($products->hasPages())
                <div>
                    {{ $products->links() }}
                </div>
            @endif
        </div>
        <!-- Modal Import Excel Sản Phẩm & Bảng Giá -->
        <div 
            x-show="showImportModal" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            @keydown.escape.window="if(!isImporting) showImportModal = false"
        >
            <div 
                class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all"
                @click.outside="if(!isImporting) showImportModal = false"
            >
                <!-- Modal Header -->
                <div class="p-4 bg-emerald-600 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                            <i class="fa-solid fa-file-excel text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold">Import Excel Sản Phẩm & Bảng Giá Toàn Diện</h3>
                            <p class="text-[11px] text-emerald-100">Đồng bộ mã, tên thương mại, tên hóa đơn, đơn vị tính, quy cách & bảng giá</p>
                        </div>
                    </div>
                    <button type="button" @click="if(!isImporting) showImportModal = false" class="text-white/80 hover:text-white transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitImportExcel()" class="p-5 space-y-4 text-xs">
                    <!-- Quy tắc giá thông minh box -->
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 space-y-2">
                        <div class="font-bold flex items-center gap-1.5 text-emerald-800">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>Quy tắc bảng giá theo đơn vị tính:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-[11px] text-emerald-800">
                            <li><strong>ĐVT tính và ĐV quy đổi GIỐNG NHAU:</strong> Giá áp dụng chung đồng nhất, không phân biệt đơn vị.</li>
                            <li><strong>ĐVT tính và ĐV quy đổi KHÁC NHAU:</strong> Tự động tính và phân biệt bảng giá riêng biệt cho ĐVT cơ bản (Cây/Cuộn...) và ĐVT quy đổi (Mét...) theo tỷ lệ quy cách.</li>
                            <li><strong>Hỗ trợ phân khúc giá & định mức tồn:</strong> Giá nhập (vốn), Nguyên giá, Cấp 1 (BC1), Cấp 2 (BC2), Cấp 3 (BC3), Khách lẻ, Thuế VAT, Tồn tối thiểu, Tồn tối đa.</li>
                        </ul>
                        <div class="pt-2 border-t border-emerald-200/60 space-y-2">
                            <div class="text-[11px] font-bold text-emerald-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-file-circle-check text-emerald-600"></i>
                                <span>Tải về file Excel mẫu chuẩn (Đã có sẵn cột Tồn tối thiểu [W] & Tồn tối đa [X]):</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <a href="{{ url('/api/products/template-excel?type=hdpe') }}" class="flex items-center justify-between p-2.5 bg-white hover:bg-emerald-50/60 rounded-xl border border-emerald-200 shadow-2xs hover:shadow-xs transition-all group">
                                    <div class="text-left">
                                        <p class="font-bold text-xs text-emerald-900 group-hover:text-emerald-700 flex items-center gap-1.5">
                                            <i class="fa-solid fa-file-excel text-emerald-600"></i>
                                            <span>Mẫu 1: Ống HDPE (1 Đơn Vị Giá)</span>
                                        </p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">ĐVT tính chuẩn (Mét/Cây), giá đồng nhất theo cột</p>
                                    </div>
                                    <i class="fa-solid fa-download text-emerald-600 text-xs ml-2"></i>
                                </a>
                                <a href="{{ url('/api/products/template-excel?type=pvc') }}" class="flex items-center justify-between p-2.5 bg-white hover:bg-emerald-50/60 rounded-xl border border-emerald-200 shadow-2xs hover:shadow-xs transition-all group">
                                    <div class="text-left">
                                        <p class="font-bold text-xs text-emerald-900 group-hover:text-emerald-700 flex items-center gap-1.5">
                                            <i class="fa-solid fa-file-excel text-emerald-600"></i>
                                            <span>Mẫu 2: Ống PVC (2 Đơn Vị Quy Đổi)</span>
                                        </p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">ĐVT Chuẩn (Cây) & ĐVT Lẻ (Mét), 2 bộ cột giá riêng</p>
                                    </div>
                                    <i class="fa-solid fa-download text-emerald-600 text-xs ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- File Drag & Drop Zone -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Chọn file Excel (.xlsx, .xls) <span class="text-rose-500">*</span></label>
                        <div 
                            class="border-2 border-dashed rounded-xl p-5 text-center transition-colors cursor-pointer"
                            :class="importFile ? 'border-emerald-500 bg-emerald-50/40' : 'border-slate-300 hover:border-emerald-400 bg-slate-50'"
                            @click="$refs.productExcelFileInput.click()"
                            @dragover.prevent
                            @drop.prevent="handleFileDrop($event)"
                        >
                            <input 
                                type="file" 
                                x-ref="productExcelFileInput" 
                                accept=".xlsx,.xls" 
                                class="hidden" 
                                @change="handleFileSelect($event)"
                            >
                            <template x-if="!importFile">
                                <div class="space-y-1.5">
                                    <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400"></i>
                                    <p class="font-semibold text-slate-700">Kéo thả file Excel vào đây hoặc <span class="text-emerald-600 underline">nhấn để duyệt file</span></p>
                                    <p class="text-[11px] text-slate-400">Dung lượng tối đa 20MB (.xlsx)</p>
                                </div>
                            </template>
                            <template x-if="importFile">
                                <div class="flex items-center justify-center gap-3 text-emerald-800">
                                    <i class="fa-solid fa-file-excel text-2xl text-emerald-600"></i>
                                    <div class="text-left">
                                        <p class="font-bold text-xs" x-text="importFileName"></p>
                                        <p class="text-[11px] text-emerald-600" x-text="formatFileSize(importFile.size)"></p>
                                    </div>
                                    <button type="button" @click.stop="clearImportFile()" class="ml-2 w-6 h-6 rounded-full bg-slate-200 hover:bg-rose-100 hover:text-rose-600 text-slate-600 flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer buttons -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" :disabled="isImporting" @click="showImportModal = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold transition-colors cursor-pointer disabled:opacity-50">
                            Đóng
                        </button>
                        <button type="submit" :disabled="!importFile || isImporting" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer disabled:opacity-50">
                            <i x-show="isImporting" class="fa-solid fa-spinner fa-spin"></i>
                            <i x-show="!isImporting" class="fa-solid fa-upload"></i>
                            <span x-text="isImporting ? 'Đang phân tích & import...' : 'Bắt Đầu Import Sản Phẩm'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function productsManager() {
    return {
        products: @json($products->items() ?? []),
        categoriesList: @json($categories ?? []),
        brandsList: @json($brands ?? []),
        priceTiersMaster: @json($priceTiers ?? []),
        priceViewMode: 'stacked', // 'stacked' | 'split'
        searchQuery: '',
        selectedCategoryId: '',
        selectedBrandId: '',
        showImportModal: false,
        isImporting: false,
        importFile: null,
        importFileName: '',

        initData() {},

        get filteredProducts() {
            const q = this.searchQuery.trim().toLowerCase();
            return this.products.filter(item => {
                const name = (item.name || '').toLowerCase();
                const taxName = (item.tax_name || '').toLowerCase();
                const sku = (item.sku || '').toLowerCase();
                const spec = (item.specification || '').toLowerCase();

                const matchQ = !q || name.includes(q) || taxName.includes(q) || sku.includes(q) || spec.includes(q);
                const matchCat = !this.selectedCategoryId || String(item.category_id) === String(this.selectedCategoryId);
                const matchBrand = !this.selectedBrandId || String(item.brand_id) === String(this.selectedBrandId);

                return matchQ && matchCat && matchBrand;
            });
        },

        getCategoryName(item) {
            if (item.category && item.category.name) return item.category.name;
            const c = this.categoriesList.find(x => x.id == item.category_id);
            return c ? c.name : 'Chưa phân loại';
        },

        getBrandName(item) {
            if (item.brand && item.brand.name) return item.brand.name;
            const b = this.brandsList.find(x => x.id == item.brand_id);
            return b ? b.name : 'Chưa gắn hiệu';
        },

        getCostPrice(product) {
            return Number(product?.cost_price || 0);
        },

        getCostConversionPrice(product) {
            if (!product) return 0;
            if (Number(product.conversion_price) > 0) return Number(product.conversion_price);
            if (Number(product.cost_price) > 0) {
                const rate = Number(product.conversion_rate) || 1;
                return rate > 1 ? Number((product.cost_price / rate).toFixed(2)) : Number(product.cost_price);
            }
            return 0;
        },

        getSellingPrice(product) {
            if (!product) return 0;
            if (Number(product.price) > 0) return Number(product.price);
            return this.getTierPrice(product, 'Giá Bán', 'Giá Bán lẻ', 'Bán Lẻ', 'retail_price', 'Tiêu Chuẩn', 'Nguyên Giá') || Number(product.cost_price || 0);
        },

        getSellingConversionPrice(product) {
            if (!product) return 0;
            if (Number(product.conversion_price) > 0) return Number(product.conversion_price);
            const sellPrice = this.getSellingPrice(product);
            if (sellPrice > 0) {
                const rate = Number(product.conversion_rate) || 1;
                return rate > 1 ? Number((sellPrice / rate).toFixed(2)) : sellPrice;
            }
            return 0;
        },

        getTierPrice(product, ...tierNames) {
            if (!product) return 0;
            const tiers = product.price_tiers || product.priceTiers;
            if (Array.isArray(tiers)) {
                for (const tName of tierNames) {
                    const found = tiers.find(t => t && t.name && t.name.toLowerCase().includes(tName.toLowerCase()));
                    if (found && Number(found.price) > 0) return Number(found.price);
                }
            }
            return 0;
        },

        getTierConversionPrice(product, ...tierNames) {
            if (!product) return 0;
            const tiers = product.price_tiers || product.priceTiers;
            if (Array.isArray(tiers)) {
                for (const tName of tierNames) {
                    const found = tiers.find(t => t && t.name && t.name.toLowerCase().includes(tName.toLowerCase()));
                    if (found) {
                        if (Number(found.conversion_price) > 0) return Number(found.conversion_price);
                        if (Number(found.price) > 0) {
                            const rate = Number(product.conversion_rate) || 1;
                            return rate > 1 ? Number((found.price / rate).toFixed(2)) : Number(found.price);
                        }
                    }
                }
            }
            return 0;
        },

        hasDiffPrice(stdPrice, convPrice) {
            const p1 = Math.round(Number(stdPrice || 0));
            const p2 = Math.round(Number(convPrice || 0));
            if (p1 <= 0 || p2 <= 0) return false;
            return p1 !== p2;
        },

        formatNumber(num) {
            if (num === null || num === undefined || num === '') return '0';
            const val = Number(num);
            if (isNaN(val)) return '0';
            return Math.round(val).toLocaleString('vi-VN', { maximumFractionDigits: 0 });
        },

        async deleteProduct(item) {
            const confirmResult = await Swal.fire({
                title: 'Xác nhận xóa sản phẩm?',
                html: `Bạn có chắc chắn muốn xóa sản phẩm <strong>[${item.sku}] ${item.name}</strong>?<br><span class="text-xs text-slate-500">Thao tác này sẽ xóa sản phẩm khỏi kho và bảng giá.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy bỏ',
                reverseButtons: true
            });

            if (!confirmResult.isConfirmed) return;

            try {
                const response = await fetch(`{{ url('/cat_products') }}/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.products = this.products.filter(p => p.id !== item.id);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: result.message || 'Đã xóa sản phẩm thành công!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể xóa',
                        text: result.message || 'Có lỗi xảy ra khi xóa sản phẩm.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Không thể kết nối máy chủ.'
                });
            }
        },

        handleFileSelect(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                this.importFile = files[0];
                this.importFileName = files[0].name;
            }
        },

        handleFileDrop(e) {
            const files = e.dataTransfer.files;
            if (files && files.length > 0) {
                this.importFile = files[0];
                this.importFileName = files[0].name;
            }
        },

        clearImportFile() {
            this.importFile = null;
            this.importFileName = '';
            if (this.$refs.productExcelFileInput) {
                this.$refs.productExcelFileInput.value = '';
            }
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },

        async submitImportExcel() {
            if (!this.importFile) {
                Swal.fire({ icon: 'warning', title: 'Chưa chọn file', text: 'Vui lòng chọn một file Excel (.xlsx)!' });
                return;
            }

            this.isImporting = true;
            const formData = new FormData();
            formData.append('file', this.importFile);

            try {
                const response = await fetch('{{ url('/api/products/import-excel') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    this.showImportModal = false;
                    await Swal.fire({
                        icon: 'success',
                        title: 'Import Dữ Liệu Thành Công!',
                        html: `
                            <div class="text-left text-xs space-y-2 p-3 bg-slate-50 rounded-xl border border-slate-200 mt-2">
                                <div class="flex justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-600">Sản phẩm thêm mới:</span>
                                    <strong class="text-emerald-700">${result.stats.created_count}</strong>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-600">Sản phẩm cập nhật:</span>
                                    <strong class="text-sky-700">${result.stats.updated_count}</strong>
                                </div>
                                <div class="flex justify-between py-1 font-bold text-slate-800">
                                    <span>Tổng số sản phẩm trong kho:</span>
                                    <strong class="text-indigo-700">${result.stats.total_products}</strong>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Đồng ý & Xem danh sách'
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Import thất bại',
                        text: result.message || 'Có lỗi xảy ra trong quá trình xử lý file Excel.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Không thể kết nối đến máy chủ hoặc file vượt quá dung lượng cho phép.'
                });
            } finally {
                this.isImporting = false;
            }
        }
    };
}
</script>
@endsection
