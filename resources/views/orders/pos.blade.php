@extends('layouts.app')

@section('title', 'Giao Diện Bán Hàng & Chọn Sản Phẩm Nhanh (POS) - Đại Sơn ERP')

@section('content')
<div 
    x-data="posApp()" 
    x-init="init()" 
    @keydown.window="handleGlobalKeydown($event)"
    class="pos-app-wrapper min-h-screen bg-slate-100/70"
>
    <!-- 1. TOP BAR: TIÊU ĐỀ & ĐIỀU HƯỚNG TỐI ƯU CẢ MOBILE LẪN DESKTOP -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-[1700px] mx-auto px-3 sm:px-6 py-2 sm:py-2.5">
            <!-- Hàng chính: Tiêu đề + Nút hành động -->
            <div class="flex items-center justify-between gap-2 sm:gap-3">
                <!-- Left: Back Button & Title -->
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <a href="{{ url('/orders') }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition cursor-pointer shrink-0" title="Quay lại danh sách đơn hàng">
                        <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
                    </a>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2 truncate">
                                <i class="fa-solid fa-cash-register text-purple-600 text-base sm:text-lg"></i>
                                <span class="truncate">Bán Hàng POS</span>
                            </h1>
                            <span class="hidden md:inline-block px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 text-xs font-extrabold uppercase">
                                Chọn Nhanh
                            </span>
                            @if(!empty($userRestrictedBrandName))
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-xs font-extrabold flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-building-flag text-amber-600"></i>
                                <span>Hãng phụ trách: <strong>{{ $userRestrictedBrandName }}</strong></span>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Nút Vào Trang Tạo Đơn -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <button 
                        type="button" 
                        @click="proceedToOrderCreate()" 
                        :disabled="cart.length === 0"
                        class="hidden sm:flex px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-black transition items-center gap-2 shadow-md cursor-pointer select-none"
                        :class="cart.length > 0 ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-emerald-600/30 active:scale-95' : 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'"
                        title="Chuyển toàn bộ sản phẩm trong giỏ vào trang tạo đơn (F8)"
                    >
                        <i class="fa-solid fa-file-circle-plus"></i>
                        <span class="hidden xs:inline">Vào Trang Tạo Đơn</span>
                        <span class="xs:hidden">Tạo Đơn</span>
                        <span 
                            x-show="cart.length > 0" 
                            class="px-1.5 sm:px-2 py-0.5 rounded-full bg-white/20 text-white text-[11px] sm:text-xs font-mono"
                            x-text="cart.length + ' món'"
                        ></span>
                        <kbd class="hidden sm:inline-block px-1.5 py-0.5 rounded bg-black/20 text-[10px] font-mono text-white/90">F8</kbd>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MAIN CONTAINER -->
    <div class="max-w-[1700px] mx-auto px-2.5 sm:px-6 pt-3 sm:pt-4">
        <div class="w-full space-y-3">
                
                <!-- Thanh Tìm Kiếm Tối Ưu -->
                <div class="bg-white p-2 sm:p-2.5 rounded-2xl border border-slate-200/90 shadow-xs">
                    <!-- Ô tìm kiếm Full-width trên mobile kèm nút lọc & view mode -->
                    <div class="flex items-center gap-2">
                        <!-- Ô tìm kiếm thông minh -->
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-base"></i>
                            <input 
                                x-ref="searchInput"
                                type="text" 
                                x-model="searchQuery" 
                                @keydown.escape="searchQuery = ''"
                                placeholder="Tìm kiếm tên sản phẩm, mã SKU..."
                                :placeholder="isMobileViewport ? 'Tìm kiếm tên sản phẩm, mã SKU...' : 'Tìm kiếm tên sản phẩm, mã SKU... (/)'" 
                                class="w-full pl-10 pr-10 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-base font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition shadow-2xs"
                            >
                            <button 
                                x-show="searchQuery" 
                                @click="searchQuery = ''; $refs.searchInput.focus()" 
                                type="button" 
                                class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 transition"
                            >
                                <i class="fa-solid fa-circle-xmark text-base"></i>
                            </button>
                        </div>

                        <!-- Chọn kho xuất -->
                        <template x-if="warehouses && warehouses.length > 0">
                            <select
                                x-model="selectedWarehouse"
                                @change="onWarehouseChange()"
                                class="h-10 max-w-[145px] sm:max-w-[180px] px-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer shrink-0"
                                title="Chọn kho kiểm tra tồn & xuất hàng"
                            >
                                <template x-for="wh in warehouses" :key="wh.id || wh.name">
                                    <option :value="wh.name" x-text="wh.name"></option>
                                </template>
                            </select>
                        </template>

                        <!-- Chuyển đổi hiển thị Grid / List -->
                        <div class="hidden sm:flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 shrink-0">
                            <button 
                                type="button" 
                                @click="viewMode = 'grid'" 
                                class="px-2 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                :class="viewMode === 'grid' ? 'bg-white text-purple-700 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                title="Hiển thị dạng thẻ ảnh (Grid)"
                            >
                                <i class="fa-solid fa-table-cells-large"></i>
                            </button>
                            <button 
                                type="button" 
                                @click="viewMode = 'list'" 
                                class="px-2 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                :class="viewMode === 'list' ? 'bg-white text-purple-700 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                title="Hiển thị dạng danh sách chi tiết (List)"
                            >
                                <i class="fa-solid fa-list-ul"></i>
                            </button>
                        </div>
                    </div>


                </div>

                <!-- Thống kê & Sắp xếp -->
                <div class="flex items-center justify-between text-sm sm:text-base text-slate-500 px-1">
                    <div>
                        <strong class="text-purple-700 font-bold" x-text="filteredProducts.length"></strong> sản phẩm
                        <span x-show="searchQuery">khớp "<strong class="text-slate-800" x-text="searchQuery"></strong>"</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="hidden xs:inline">Xếp:</span>
                        <select 
                            x-model="sortBy" 
                            class="bg-transparent font-bold text-slate-700 border-none focus:outline-none cursor-pointer text-sm sm:text-base"
                        >
                            <option value="name_asc">Tên A-Z</option>
                            <option value="name_desc">Tên Z-A</option>
                            <option value="price_asc">Giá tăng dần</option>
                            <option value="price_desc">Giá giảm dần</option>
                            <option value="stock_desc">Tồn kho nhiều nhất</option>
                        </select>
                    </div>
                </div>

                <!-- 3. DANH SÁCH SẢN PHẨM DẠNG THẺ (GRID) - Dùng x-if để chỉ dựng DOM khi active, giảm 50% DOM nodes -->
                <template x-if="viewMode === 'grid'">
                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-2.5 sm:gap-3">
                        <template x-for="p in filteredProducts" :key="p.id">
                            <div 
                                @click="openQuantityModal(p)"
                                class="group bg-white rounded-2xl border transition-all duration-150 p-3 sm:p-4 cursor-pointer relative shadow-xs hover:shadow-md hover:border-purple-300 select-none flex flex-col gap-2"
                                :class="p._inCart > 0 ? 'border-purple-400 bg-purple-50/15 ring-1 ring-purple-300' : 'border-slate-200/90'"
                            >
                                <!-- Dòng 1: Mã SKU & Đơn vị tính -->
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="font-mono text-xs sm:text-sm font-bold px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200/80 text-slate-700 tracking-wide truncate" x-text="p.sku" :title="p.sku"></span>
                                        <template x-if="p._inCart > 0">
                                            <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-700 border border-purple-200 text-xs font-extrabold whitespace-nowrap flex items-center gap-1">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                                <span>Đã thêm <strong x-text="p._inCart"></strong></span>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="text-xs sm:text-sm text-slate-500 font-medium whitespace-nowrap shrink-0">
                                        Đơn vị: <span class="font-bold text-slate-700" x-text="p.unit || 'Cái'"></span>
                                    </div>
                                </div>

                                <!-- Dòng 2: Tên sản phẩm -->
                                <div class="w-full min-w-0 mt-0.5">
                                    <h3 class="font-bold text-xl sm:text-2xl text-slate-900 leading-snug break-words group-hover:text-purple-700 transition" x-text="p.name"></h3>
                                    <template x-if="p.specification">
                                        <p class="text-xs sm:text-sm text-slate-400 mt-0.5 italic break-words" x-text="p.specification"></p>
                                    </template>
                                </div>

                                <!-- Dòng 3: Giá & Tồn kho + Bộ điều chỉnh số lượng -->
                                <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100/90">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-black text-lg sm:text-xl text-purple-700 leading-tight whitespace-nowrap" x-text="formatCurrency(p.price)"></div>
                                        <div class="flex items-center gap-1 text-[13px] sm:text-sm font-bold mt-0.5 min-w-0"
                                             :class="p._avail > 0 ? 'text-emerald-700' : 'text-rose-600'">
                                            <i class="fa-solid shrink-0" :class="p._avail > 0 ? 'fa-circle-check text-emerald-500' : 'fa-circle-xmark text-rose-500'"></i>
                                            <span class="truncate" x-text="p._avail > 0 ? 'Còn ' + formatNumber(p._avail) + ' ' + (p.unit || '') : 'Hết hàng'"></span>
                                        </div>
                                    </div>

                                    <!-- Bộ điều chỉnh SL trực tiếp + Nút Thêm -->
                                    <div @click.stop class="shrink-0 flex items-center gap-1.5 sm:gap-2">
                                        <div class="flex items-center bg-slate-50 border border-slate-200/90 rounded-xl p-0.5 shadow-2xs">
                                            <button type="button"
                                                    @click="decrementDraftQty(p)"
                                                    :disabled="p._avail <= 0 || getDraftQty(p) <= 1"
                                                    class="w-7 h-9 sm:w-8 sm:h-9 flex items-center justify-center rounded-lg text-purple-700 hover:bg-purple-100/70 active:scale-95 transition cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                                                    title="Giảm số lượng">
                                                <i class="fa-solid fa-minus text-xs"></i>
                                            </button>
                                            <input type="number"
                                                   min="1"
                                                   :max="p._avail"
                                                   :value="getDraftQty(p)"
                                                   @input="setDraftQty(p, $event.target.value)"
                                                   @blur="clampDraftQtyInput(p, $event.target)"
                                                   @keydown.enter.prevent="quickAddToCart(p)"
                                                   :disabled="p._avail <= 0"
                                                   class="w-8 sm:w-10 h-9 bg-transparent text-center font-black text-base text-slate-900 border-none focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                            <button type="button"
                                                    @click="incrementDraftQty(p)"
                                                    :disabled="p._avail <= 0 || getDraftQty(p) >= p._avail"
                                                    class="w-7 h-9 sm:w-8 sm:h-9 flex items-center justify-center rounded-lg text-purple-700 hover:bg-purple-100/70 active:scale-95 transition cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                                                    title="Tăng số lượng">
                                                <i class="fa-solid fa-plus text-xs"></i>
                                            </button>
                                        </div>

                                        <button type="button"
                                                @click.stop="quickAddToCart(p)"
                                                :disabled="p._avail <= 0"
                                                class="h-10 px-3 sm:px-4 rounded-xl font-bold text-sm sm:text-base flex items-center justify-center gap-1.5 transition shadow-sm select-none whitespace-nowrap"
                                                :class="p._avail <= 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white shadow-purple-600/25 cursor-pointer active:scale-95'">
                                            <i class="fa-solid" :class="p._avail <= 0 ? 'fa-ban' : 'fa-cart-plus'"></i>
                                            <span x-text="p._avail <= 0 ? 'Hết' : 'Thêm'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- 4. DANH SÁCH SẢN PHẨM DẠNG LIST (BẢNG) - Dùng x-if để chỉ dựng bảng khi chuyển sang List view -->
                <template x-if="viewMode === 'list'">
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                                <thead>
                                    <tr class="bg-slate-50/90 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-xs">
                                        <th class="py-3 px-3 text-center w-12">STT</th>
                                        <th class="py-3 px-3 min-w-[110px]">Mã SKU</th>
                                        <th class="py-3 px-3 min-w-[240px]">Tên Sản Phẩm & Quy Cách</th>
                                        <th class="py-3 px-3 min-w-[120px]">Danh Mục</th>
                                        <th class="py-3 px-3 text-center min-w-[80px]">ĐVT</th>
                                        <th class="py-3 px-3 text-right min-w-[110px]">Giá Bán</th>
                                        <th class="py-3 px-3 text-center min-w-[110px]">Tồn Khả Dụng</th>
                                        <th class="py-3 px-3 text-center min-w-[90px]">Đã Chọn</th>
                                        <th class="py-3 px-3 text-center w-20">Chọn</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(p, idx) in filteredProducts" :key="p.id">
                                        <tr 
                                            @click="openQuantityModal(p)"
                                            class="hover:bg-purple-50/40 transition cursor-pointer group"
                                            :class="p._inCart > 0 ? 'bg-purple-50/30' : ''"
                                        >
                                            <td class="py-3 px-3 text-center text-slate-400 font-mono" x-text="idx + 1"></td>
                                            <td class="py-3 px-3 font-mono font-bold text-slate-800 text-xs" x-text="p.sku"></td>
                                            <td class="py-3 px-3">
                                                <div class="font-bold text-base sm:text-lg text-slate-900 group-hover:text-purple-700 transition" x-text="p.name"></div>
                                                <template x-if="p.specification">
                                                    <div class="text-xs text-slate-400 line-clamp-1 italic" x-text="p.specification"></div>
                                                </template>
                                            </td>
                                            <td class="py-3 px-3 text-slate-600" x-text="p.category_name || '-'"></td>
                                            <td class="py-3 px-3 text-center font-bold text-slate-700" x-text="p.unit || '-'"></td>
                                            <td class="py-3 px-3 text-right font-black text-purple-700 text-sm sm:text-base" x-text="formatCurrency(p.price)"></td>
                                            <td class="py-3 px-3 text-center">
                                                <span 
                                                    class="px-2 py-0.5 rounded-full text-xs font-extrabold"
                                                    :class="p._avail > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                                                    x-text="p._avail > 0 ? formatNumber(p._avail) : 'Hết hàng'"
                                                ></span>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <template x-if="p._inCart > 0">
                                                    <span class="px-2 py-0.5 rounded-full bg-purple-600 text-white font-black text-xs" x-text="p._inCart"></span>
                                                </template>
                                                <template x-if="!p._inCart">
                                                    <span class="text-slate-300">-</span>
                                                </template>
                                            </td>
                                            <td class="py-3 px-3 text-center" @click.stop>
                                                <button 
                                                    type="button" 
                                                    @click="openQuantityModal(p)"
                                                    :disabled="p._avail <= 0"
                                                    class="px-2.5 py-1 rounded-lg font-bold text-xs transition select-none flex items-center justify-center gap-1 mx-auto"
                                                    :class="p._avail <= 0 ? 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed' : 'bg-purple-100 hover:bg-purple-200 text-purple-800 cursor-pointer active:scale-95'"
                                                >
                                                    <i class="fa-solid" :class="p._avail <= 0 ? 'fa-ban text-[10px]' : 'fa-plus text-[10px]'"></i>
                                                    <span x-text="p._avail <= 0 ? 'Hết hàng' : 'Chọn'"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="filteredProducts.length === 0" class="bg-white p-10 rounded-2xl border border-slate-200 text-center text-slate-400">
                    <div class="w-14 h-14 mx-auto mb-2 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl text-slate-400">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Không tìm thấy sản phẩm</p>
                    <p class="text-xs text-slate-400 mt-1">Thử đổi từ khóa hoặc bỏ lọc</p>
                    <button 
                        type="button" 
                        @click="searchQuery = ''; selectedCategory = 'all'; stockFilter = 'all'" 
                        class="mt-3 px-3 py-1.5 rounded-xl bg-purple-100 hover:bg-purple-200 text-purple-800 text-xs font-bold transition cursor-pointer"
                    >
                        Đặt lại bộ lọc
                    </button>
                </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. NÚT GIỎ HÀNG & POPUP NỔI Ở GÓC DƯỚI MÀN HÌNH (FAB + FLOATING POPUP)   -->
    <!-- ========================================================================= -->

    <!-- Lớp nền mờ (chạm ngoài để đóng popup) -->
    <div 
        x-show="isCartOpen" 
        x-cloak 
        @click="isCartOpen = false" 
        class="fixed inset-0 z-[9996] bg-black/40 backdrop-blur-2xs transition-opacity"
    ></div>

    <!-- POPUP GIỎ HÀNG NỔI Ở GÓC DƯỚI MÀN HÌNH -->
    <div 
        x-show="isCartOpen" 
        x-cloak
        x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200 transform"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        class="fixed bottom-0 sm:bottom-20 left-0 right-0 sm:left-6 sm:right-auto z-[9998] w-full sm:w-[420px] max-w-full sm:max-w-[calc(100vw-24px)] bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200/90 flex flex-col max-h-[82vh] sm:max-h-[600px] overflow-hidden"
        @click.stop
    >
        <!-- Header Popup Giỏ Hàng -->
        <div class="p-3.5 sm:p-4 bg-gradient-to-r from-purple-700 via-indigo-700 to-purple-800 text-white flex items-center justify-between shrink-0 shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-white/15 flex items-center justify-center text-sm shadow-inner">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div>
                    <h2 class="font-black text-base sm:text-lg uppercase tracking-wide">Giỏ Hàng Tạm</h2>
                    <span class="text-xs sm:text-sm text-purple-200" x-text="cartTotalCount + ' món (' + cart.length + ' mã hàng)'"></span>
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                <button 
                    type="button" 
                    x-show="cart.length > 0" 
                    @click="clearCart()" 
                    class="px-2.5 py-1 rounded-xl bg-white/10 hover:bg-rose-500/80 text-white text-xs sm:text-sm font-bold transition cursor-pointer flex items-center gap-1"
                    title="Xóa toàn bộ giỏ"
                >
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Xóa hết</span>
                </button>

                <button 
                    type="button" 
                    @click="isCartOpen = false" 
                    class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer"
                    title="Đóng popup"
                >
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
        </div>

        <!-- Danh sách các sản phẩm trong giỏ -->
        <div class="p-3 overflow-y-auto flex-1 space-y-2 max-h-[380px]">
            <template x-if="cart.length === 0">
                <div class="py-12 text-center text-slate-400">
                    <div class="w-14 h-14 mx-auto mb-2 rounded-3xl bg-purple-50 text-purple-400 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <p class="text-base font-bold text-slate-700">Giỏ hàng đang trống</p>
                    <p class="text-sm text-slate-400 mt-0.5">Bấm vào bất kỳ sản phẩm nào để thêm vào giỏ</p>
                </div>
            </template>

            <template x-for="(item, idx) in cart" :key="item.product_id">
                <div class="p-3 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white transition flex flex-col gap-1.5">
                    <div class="flex items-start justify-between gap-1.5">
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-base sm:text-lg text-slate-900 break-words" x-text="item.name"></div>
                            <div class="text-xs sm:text-sm text-slate-400 flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono text-purple-700 font-bold" x-text="item.sku"></span>
                                <span>•</span>
                                <span x-text="formatCurrency(item.price)"></span>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            @click="removeFromCart(idx)" 
                            class="text-slate-300 hover:text-rose-600 p-1 transition cursor-pointer"
                            title="Xóa món này"
                        >
                            <i class="fa-regular fa-trash-can text-base"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100">
                        <div class="flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden shadow-2xs">
                            <button 
                                type="button" 
                                @click="updateCartQty(idx, item.quantity - 1)" 
                                class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition cursor-pointer text-sm"
                            >
                                <i class="fa-solid fa-minus text-xs"></i>
                            </button>
                            <input 
                                type="number" 
                                x-model.number="item.quantity" 
                                @change="updateCartQty(idx, item.quantity)"
                                min="1" 
                                :max="getProductAvailableStock(getProductById(item.product_id))"
                                class="w-12 h-8 text-center font-bold text-base text-slate-800 border-none focus:outline-none"
                            >
                            <button 
                                type="button" 
                                @click="updateCartQty(idx, item.quantity + 1)" 
                                :disabled="item.quantity >= getProductAvailableStock(getProductById(item.product_id))"
                                class="w-8 h-8 flex items-center justify-center text-slate-600 transition text-sm"
                                :class="item.quantity >= getProductAvailableStock(getProductById(item.product_id)) ? 'opacity-30 cursor-not-allowed' : 'hover:bg-slate-100 cursor-pointer'"
                            >
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>

                        <div class="font-black text-base text-purple-800" x-text="formatCurrency(item.quantity * item.price)"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer Giỏ Hàng: Tạm tính & Nút chuyển sang trang thêm đơn -->
        <div class="p-3 sm:p-4 bg-slate-50 border-t border-slate-200 shrink-0 space-y-2.5 pb-[max(14px,env(safe-area-inset-bottom,14px))] sm:pb-4">
            <div class="space-y-1 text-sm">
                <div class="flex items-center justify-between text-slate-500 text-sm">
                    <span>Tổng số lượng:</span>
                    <span class="font-bold text-slate-800" x-text="formatNumber(cartTotalCount) + ' món'"></span>
                </div>
                <div class="flex items-center justify-between text-base pt-1 border-t border-slate-200/80">
                    <span class="font-bold text-slate-700">Tạm tính tiền hàng:</span>
                    <span class="font-black text-lg text-purple-700" x-text="formatCurrency(cartTotalAmount)"></span>
                </div>
            </div>

            <button 
                type="button" 
                @click="proceedToOrderCreate()" 
                :disabled="cart.length === 0"
                class="w-full py-3 rounded-2xl font-black text-base flex items-center justify-center gap-2 transition cursor-pointer shadow-md select-none"
                :class="cart.length > 0 ? 'bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 hover:from-purple-700 text-white shadow-purple-600/30 active:scale-98' : 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'"
            >
                <i class="fa-solid fa-file-circle-plus"></i>
                <span>CHUYỂN VÀO TẠO ĐƠN</span>
                <i class="fa-solid fa-arrow-right text-sm"></i>
            </button>
        </div>
    </div>

    <!-- TOAST THÔNG BÁO THÊM NHANH VÀO GIỎ -->
    <div x-show="posToastMessage"
         x-cloak
         x-transition:enter="transition ease-out duration-200 transform"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="fixed bottom-20 left-1/2 -translate-x-1/2 z-[10000] px-4 py-2 rounded-full bg-slate-900/90 text-white text-xs sm:text-sm font-bold shadow-xl flex items-center gap-2 whitespace-nowrap max-w-[92vw] truncate pointer-events-none">
        <i class="fa-solid fa-circle-check text-emerald-400 shrink-0"></i>
        <span class="truncate" x-text="posToastMessage"></span>
    </div>

    <!-- THANH THANH TOÁN CỐ ĐỊNH DƯỚI ĐÁY MÀN HÌNH (GIỎ HÀNG + TỔNG TIỀN + NÚT THANH TOÁN) -->
    <div class="pos-bottom-bar fixed bottom-0 left-0 right-0 z-[9995] bg-white/95 backdrop-blur-md border-t border-slate-200 rounded-t-2xl sm:rounded-none shadow-[0_-6px_25px_rgba(15,23,42,0.12)] px-3 sm:px-6 py-2.5 pb-[max(10px,env(safe-area-inset-bottom,10px))] sm:py-3 flex items-center justify-between gap-3">
        <!-- Trái: Icon giỏ hàng + Tổng tiền & Số mặt hàng (chạm để mở popup giỏ hàng) -->
        <button type="button"
                @click="isCartOpen = !isCartOpen"
                class="flex items-center gap-3 min-w-0 text-left cursor-pointer group active:opacity-80 transition">
            <div class="relative shrink-0 p-1">
                <i class="fa-solid fa-cart-shopping text-purple-700 text-2xl sm:text-[26px] group-hover:scale-105 transition-transform"></i>
                <span x-show="cart.length > 0"
                      class="absolute -top-1 -right-1.5 min-w-[20px] h-[20px] px-1 rounded-full bg-rose-500 text-white text-[11px] font-black flex items-center justify-center border-2 border-white shadow-xs"
                      x-text="cart.length"></span>
            </div>
            <div class="h-8 w-px bg-slate-200 shrink-0"></div>
            <div class="min-w-0">
                <div class="text-lg sm:text-2xl font-black text-purple-800 leading-tight whitespace-nowrap truncate" x-text="formatCurrency(cartTotalAmount)"></div>
                <div class="text-sm text-slate-500 font-medium leading-tight whitespace-nowrap" x-text="cart.length + ' mặt hàng'"></div>
            </div>
        </button>

        <!-- Phải: Nút Lên Đơn / Vào Tạo Đơn -->
        <button type="button"
                @click="proceedToOrderCreate()"
                :disabled="cart.length === 0"
                class="rounded-full px-6 sm:px-8 py-3 font-black text-base sm:text-lg flex items-center justify-center gap-2 transition shadow-md select-none shrink-0 whitespace-nowrap"
                :class="cart.length > 0 ? 'bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 hover:from-purple-700 hover:to-indigo-700 text-white shadow-purple-600/30 cursor-pointer active:scale-95' : 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'">
            <i class="fa-solid fa-file-circle-plus text-base"></i>
            <span>Lên Đơn</span>
            <i class="fa-solid fa-chevron-right text-sm"></i>
        </button>
    </div>

    <!-- 6. MODAL NHẬP SỐ LƯỢNG KHI CLICK VÀO SẢN PHẨM -->
    <div 
        x-show="isQtyModalOpen" 
        x-cloak 
        class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-xs"
        @click="closeQuantityModal()"
    >
        <div 
            @click.stop 
            class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden animate-in slide-in-from-bottom sm:zoom-in-95 duration-150"
        >
            <!-- Header Modal -->
            <div class="p-3.5 sm:p-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-cart-plus"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-base sm:text-lg">Nhập Số Lượng Vào Giỏ</h3>
                        <p class="text-xs sm:text-sm text-purple-100" x-text="selectedProduct?.sku"></p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="closeQuantityModal()" 
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-4 sm:p-5 space-y-3 sm:space-y-4">
                <!-- Thông tin sản phẩm -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <h4 class="font-bold text-lg sm:text-xl text-slate-900 leading-snug" x-text="selectedProduct?.name"></h4>
                    <div class="flex items-center justify-between text-sm sm:text-base mt-1.5 pt-1.5 border-t border-slate-200">
                        <span class="text-slate-500">Đơn giá:</span>
                        <span class="font-black text-purple-700" x-text="formatCurrency(selectedProduct?.price) + ' / ' + (selectedProduct?.unit || 'cái')"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm sm:text-base mt-1">
                        <span class="text-slate-500">Tồn khả dụng (<span x-text="selectedWarehouse"></span>):</span>
                        <span 
                            class="font-bold"
                            :class="getProductAvailableStock(selectedProduct) > 0 ? 'text-emerald-700' : 'text-rose-600'"
                            x-text="formatNumber(getProductAvailableStock(selectedProduct)) + ' ' + (selectedProduct?.unit || '')"
                        ></span>
                    </div>
                </div>

                <!-- Cảnh báo khi HẾT HÀNG TỒN KHẢ DỤNG -->
                <div x-show="getProductAvailableStock(selectedProduct) <= 0" class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-bold flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg shrink-0"></i>
                    <div>
                        <div class="font-black text-rose-800">Sản phẩm hiện đã hết hàng tồn kho khả dụng!</div>
                        <div class="text-xs sm:text-sm font-medium text-rose-600 mt-0.5">Không thể thêm vào giỏ hàng. Vui lòng chọn sản phẩm khác.</div>
                    </div>
                </div>

                <!-- Ô nhập số lượng (Chỉ hiện khi CÒN HÀNG) -->
                <div x-show="getProductAvailableStock(selectedProduct) > 0">
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">
                        Số lượng mua (Tối đa <span class="text-purple-700 font-extrabold" x-text="formatNumber(getProductAvailableStock(selectedProduct))"></span>):
                    </label>
                    <div class="flex items-center border-2 border-purple-300 focus-within:border-purple-600 rounded-2xl bg-white overflow-hidden shadow-xs">
                        <button 
                            type="button" 
                            @click="modalQuantity = Math.max(1, modalQuantity - 1)" 
                            :disabled="modalQuantity <= 1"
                            class="w-11 h-11 flex items-center justify-center text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition cursor-pointer text-base disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input 
                            x-ref="modalQuantityInput"
                            type="number" 
                            x-model.number="modalQuantity" 
                            @input="clampModalQuantity()"
                            @keydown.enter="submitQuantityModal()"
                            min="1" 
                            :max="getProductAvailableStock(selectedProduct)"
                            class="w-full h-11 text-center font-black text-2xl text-purple-900 border-none focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                        >
                        <button 
                            type="button" 
                            @click="modalQuantity = Math.min(getProductAvailableStock(selectedProduct), modalQuantity + 1)" 
                            :disabled="modalQuantity >= getProductAvailableStock(selectedProduct)"
                            class="w-11 h-11 flex items-center justify-center text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition cursor-pointer text-base disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Các nút bấm chọn số lượng nhanh (Chỉ hiện khi CÒN HÀNG) -->
                <div x-show="getProductAvailableStock(selectedProduct) > 0">
                    <span class="block text-xs sm:text-sm font-semibold text-slate-400 mb-1.5">Chọn nhanh:</span>
                    <div class="flex flex-wrap items-center gap-1.5 text-sm font-bold">
                        <template x-for="q in [1, 5, 10, 20, 50, 100]" :key="q">
                            <button 
                                type="button" 
                                @click="modalQuantity = Math.min(getProductAvailableStock(selectedProduct), q)" 
                                :disabled="q > getProductAvailableStock(selectedProduct)"
                                class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                                :class="q <= getProductAvailableStock(selectedProduct) ? 'bg-slate-100 hover:bg-purple-100 hover:text-purple-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'"
                                x-text="q"
                            ></button>
                        </template>
                        <button 
                            type="button" 
                            @click="modalQuantity = getProductAvailableStock(selectedProduct)" 
                            class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer"
                        >
                            Hết tồn (<span x-text="formatNumber(getProductAvailableStock(selectedProduct))"></span>)
                        </button>
                    </div>
                </div>

                <!-- Tạm tính tiền cho món này (Chỉ hiện khi CÒN HÀNG) -->
                <div x-show="getProductAvailableStock(selectedProduct) > 0" class="p-3 rounded-2xl bg-purple-50/60 border border-purple-100 flex items-center justify-between">
                    <span class="text-sm font-bold text-purple-900">Thành tiền:</span>
                    <span class="text-lg sm:text-xl font-black text-purple-700" x-text="formatCurrency(modalQuantity * (selectedProduct?.price || 0))"></span>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-3 sm:p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2 pb-[max(16px,env(safe-area-inset-bottom,16px))] sm:pb-4">
                <button 
                    type="button" 
                    @click="closeQuantityModal()" 
                    class="px-4 py-2.5 rounded-xl border border-slate-300 font-bold text-sm sm:text-base text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                >
                    Hủy<span class="shortcut-hint hidden md:inline"> (Esc)</span>
                </button>
                <button 
                    type="button" 
                    @click="submitQuantityModal()" 
                    :disabled="getProductAvailableStock(selectedProduct) <= 0 || modalQuantity <= 0"
                    class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-black text-sm sm:text-base transition cursor-pointer shadow-md flex items-center justify-center gap-1.5 select-none"
                    :class="getProductAvailableStock(selectedProduct) > 0 && modalQuantity > 0 ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-purple-600/30 active:scale-95' : 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'"
                >
                    <i class="fa-solid" :class="getProductAvailableStock(selectedProduct) > 0 ? 'fa-cart-plus' : 'fa-ban'"></i>
                    <span x-text="getProductAvailableStock(selectedProduct) > 0 ? 'Thêm Vào Giỏ' : 'Hết Hàng'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script id="pos-products-json" type="application/json">{!! json_encode($products, JSON_UNESCAPED_UNICODE) !!}</script>
<script>
function posApp() {
    const _rawPosProducts = JSON.parse(document.getElementById('pos-products-json')?.textContent || '[]');
    return {
        // Dữ liệu từ Controller (Object.freeze ngăn Alpine.js tạo deep proxy reactivity cho hàng nghìn thuộc tính tĩnh, tăng tốc UI)
        products: Object.freeze(_rawPosProducts),
        warehouses: Object.freeze(@json($warehouses ?? [])),
        userRestrictedBrandId: @json($userRestrictedBrandId ?? null),
        userRestrictedBrandName: @json($userRestrictedBrandName ?? null),

        // Bộ lọc & Trạng thái UI
        searchQuery: '',
        isMobileViewport: typeof window !== 'undefined' ? window.innerWidth <= 768 : false,
        selectedCategory: 'all',
        selectedBrand: @json($userRestrictedBrandId ? (string)$userRestrictedBrandId : 'all'),
        stockFilter: 'all',
        sortBy: 'name_asc',
        viewMode: 'grid', // 'grid' | 'list'
        selectedWarehouse: @json($mainCompanyWarehouse ?? 'Kho Tổng'),
        selectedDealerId: '',
        isCartOpen: false,

        // Giỏ hàng tạm thời
        cart: [],
        draftQuantities: {},
        posToastMessage: '',
        posToastTimer: null,

        // Modal số lượng
        isQtyModalOpen: false,
        selectedProduct: null,
        modalQuantity: 1,

        init() {
            window.addEventListener('resize', () => {
                this.isMobileViewport = window.innerWidth <= 768;
            });
            window.addEventListener('pageshow', () => {
                this.loadCartFromStorage();
            });
            // Đặt kho mặc định
            if (this.warehouses && this.warehouses.length > 0) {
                this.selectedWarehouse = this.warehouses[0].name;
            }

            this.loadCartFromStorage();

            // Hiển thị thông báo Toast nếu vừa lưu đơn hàng thành công từ POS chuyển về
            const flashToast = sessionStorage.getItem('pos_flash_toast');
            if (flashToast) {
                sessionStorage.removeItem('pos_flash_toast');
                this.showPosToast(flashToast);
            }
        },

        loadCartFromStorage() {
            // Tải giỏ hàng từ sessionStorage nếu có lưu dở trước đó (chưa lưu đơn)
            try {
                sessionStorage.removeItem('order_from_pos');
                sessionStorage.removeItem('order_return_url');

                const savedCart = sessionStorage.getItem('pos_cart_items');
                if (savedCart) {
                    const parsed = JSON.parse(savedCart);
                    if (Array.isArray(parsed)) {
                        this.cart = parsed;
                    }
                } else {
                    this.cart = [];
                }
                const savedDealer = sessionStorage.getItem('pos_dealer_id');
                this.selectedDealerId = savedDealer || '';

                const savedWarehouse = sessionStorage.getItem('pos_warehouse');
                if (savedWarehouse && this.warehouses && this.warehouses.some(w => w.name === savedWarehouse)) {
                    this.selectedWarehouse = savedWarehouse;
                }
            } catch (e) {
                console.error('Lỗi khi tải giỏ tạm:', e);
            }
        },

        // Danh sách sản phẩm sau lọc, tìm kiếm & sắp xếp (Tối ưu: tính sẵn _avail & _inCart để template truy xuất O(1))
        get filteredProducts() {
            const list = this.products;
            const wh = this.selectedWarehouse;

            // Map số lượng trong giỏ để tra cứu O(1)
            const cartMap = {};
            for (let i = 0; i < this.cart.length; i++) {
                cartMap[this.cart[i].product_id] = this.cart[i].quantity;
            }

            // Gán _avail và _inCart 1 lần duy nhất cho danh sách
            let res = list.map(p => {
                let avail = 0;
                if (p.warehouse_stocks && wh && p.warehouse_stocks[wh]) {
                    avail = Number(p.warehouse_stocks[wh].available || 0);
                } else {
                    avail = Number(p.available_stock !== undefined ? p.available_stock : (p.stock_quantity || 0));
                }
                return {
                    ...p,
                    _avail: avail,
                    _inCart: cartMap[p.id] || 0
                };
            });

            // 1. Lọc theo từ khóa tìm kiếm
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                res = res.filter(p => {
                    const name = (p.name || '').toLowerCase();
                    const sku = (p.sku || '').toLowerCase();
                    const spec = (p.specification || '').toLowerCase();
                    return name.includes(q) || sku.includes(q) || spec.includes(q);
                });
            }

            // 2. Lọc theo Danh mục
            if (this.selectedCategory !== 'all') {
                res = res.filter(p => String(p.category_id) === String(this.selectedCategory));
            }

            // 3. Lọc theo Thương hiệu
            if (this.selectedBrand !== 'all') {
                res = res.filter(p => String(p.brand_id) === String(this.selectedBrand));
            }

            // 4. Lọc theo Tồn kho
            if (this.stockFilter === 'in_stock') {
                res = res.filter(p => p._avail > 0);
            } else if (this.stockFilter === 'low_stock') {
                res = res.filter(p => p._avail > 0 && p._avail <= 20);
            } else if (this.stockFilter === 'out_of_stock') {
                res = res.filter(p => p._avail <= 0);
            }

            // 5. Sắp xếp (sử dụng trực tiếp thuộc tính đã tính, không gọi hàm lặp lại)
            if (this.sortBy === 'name_asc') {
                res.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
            } else if (this.sortBy === 'name_desc') {
                res.sort((a, b) => (b.name || '').localeCompare(a.name || ''));
            } else if (this.sortBy === 'price_asc') {
                res.sort((a, b) => (Number(a.price) || 0) - (Number(b.price) || 0));
            } else if (this.sortBy === 'price_desc') {
                res.sort((a, b) => (Number(b.price) || 0) - (Number(a.price) || 0));
            } else if (this.sortBy === 'stock_desc') {
                res.sort((a, b) => b._avail - a._avail);
            }

            return res;
        },

        // Tính tồn kho khả dụng của sản phẩm theo kho đang chọn
        getProductAvailableStock(product) {
            if (!product) return 0;
            if (product.warehouse_stocks !== undefined && product.warehouse_stocks !== null && this.selectedWarehouse) {
                const whStock = !Array.isArray(product.warehouse_stocks) ? product.warehouse_stocks[this.selectedWarehouse] : null;
                return whStock ? Number(whStock.available || 0) : 0;
            }
            return Number(product.available_stock !== undefined ? product.available_stock : (product.stock_quantity || 0));
        },

        // Kiểm tra sản phẩm đã có bao nhiêu cái trong giỏ hàng
        getItemQuantityInCart(productId) {
            const item = this.cart.find(it => String(it.product_id) === String(productId));
            return item ? item.quantity : 0;
        },

        // Lấy thông tin sản phẩm theo ID
        getProductById(id) {
            return this.products.find(p => String(p.id) === String(id));
        },

        // Mở Modal nhập số lượng khi click vào sản phẩm
        openQuantityModal(product) {
            this.selectedProduct = product;
            const avail = this.getProductAvailableStock(product);
            if (avail <= 0) {
                this.modalQuantity = 0;
            } else {
                const existingQty = this.getItemQuantityInCart(product.id);
                this.modalQuantity = existingQty > 0 ? Math.min(avail, existingQty) : 1;
            }
            this.isQtyModalOpen = true;

            this.$nextTick(() => {
                if (this.$refs.modalQuantityInput && avail > 0) {
                    this.$refs.modalQuantityInput.focus();
                    this.$refs.modalQuantityInput.select();
                }
            });
        },

        // Giới hạn số lượng không vượt quá tồn kho khả dụng
        clampModalQuantity() {
            if (!this.selectedProduct) return;
            const avail = this.getProductAvailableStock(this.selectedProduct);
            if (avail <= 0) {
                this.modalQuantity = 0;
                return;
            }
            let val = Number(this.modalQuantity);
            if (isNaN(val) || val < 1) {
                this.modalQuantity = 1;
            } else if (val > avail) {
                this.modalQuantity = avail;
            }
        },

        // Đóng Modal số lượng
        closeQuantityModal() {
            this.isQtyModalOpen = false;
            this.selectedProduct = null;
        },

        // Xác nhận thêm vào giỏ hàng
        submitQuantityModal() {
            if (!this.selectedProduct) return;
            const p = this.selectedProduct;
            const avail = this.getProductAvailableStock(p);
            if (avail <= 0) {
                alert('Sản phẩm "' + (p.name || '') + '" hiện đã hết tồn kho khả dụng! Không thể thêm vào giỏ hàng.');
                return;
            }

            let qty = Math.max(1, Number(this.modalQuantity) || 1);
            if (qty > avail) {
                alert('Số lượng chọn (' + qty + ') vượt quá tồn kho khả dụng (' + avail + ')! Đã tự động điều chỉnh về ' + avail + '.');
                qty = avail;
                this.modalQuantity = avail;
            }

            const existingIndex = this.cart.findIndex(it => String(it.product_id) === String(p.id));
            if (existingIndex >= 0) {
                this.cart[existingIndex].quantity = qty;
            } else {
                this.cart.push({
                    product_id: p.id,
                    sku: p.sku || '',
                    name: p.name || '',
                    unit: p.unit || 'cái',
                    price: Number(p.price) || 0,
                    quantity: qty,
                    category_name: p.category_name || '',
                });
            }

            this.saveCartToStorage();
            this.closeQuantityModal();
        },

        // ─── Thao tác nhanh ngay trên từng dòng thẻ sản phẩm ─────────
        getDraftQty(product) {
            if (!product) return 1;
            const avail = product._avail !== undefined ? product._avail : this.getProductAvailableStock(product);
            if (avail <= 0) return 0;
            const val = this.draftQuantities[product.id];
            const num = Number(val);
            if (!num || num < 1) return 1;
            return Math.min(avail, Math.floor(num));
        },

        setDraftQty(product, rawVal) {
            if (!product) return;
            const avail = product._avail !== undefined ? product._avail : this.getProductAvailableStock(product);
            if (avail <= 0) {
                this.draftQuantities[product.id] = 0;
                return;
            }
            if (rawVal === '' || rawVal === null || rawVal === undefined) {
                this.draftQuantities[product.id] = '';
                return;
            }
            let num = parseInt(rawVal, 10);
            if (isNaN(num)) num = 1;
            if (num > avail) num = avail;
            this.draftQuantities[product.id] = num;
        },

        clampDraftQtyInput(product, inputEl) {
            if (!product) return;
            const avail = product._avail !== undefined ? product._avail : this.getProductAvailableStock(product);
            let num = parseInt(this.draftQuantities[product.id], 10);
            if (avail <= 0) {
                num = 0;
            } else {
                if (isNaN(num) || num < 1) num = 1;
                if (num > avail) num = avail;
            }
            this.draftQuantities[product.id] = num;
            if (inputEl) inputEl.value = num;
        },

        incrementDraftQty(product) {
            if (!product) return;
            const avail = product._avail !== undefined ? product._avail : this.getProductAvailableStock(product);
            if (avail <= 0) return;
            const cur = this.getDraftQty(product);
            this.draftQuantities[product.id] = Math.min(avail, cur + 1);
        },

        decrementDraftQty(product) {
            if (!product) return;
            const cur = this.getDraftQty(product);
            this.draftQuantities[product.id] = Math.max(1, cur - 1);
        },

        quickAddToCart(product) {
            if (!product) return;
            const avail = product._avail !== undefined ? product._avail : this.getProductAvailableStock(product);
            if (avail <= 0) {
                this.showPosToast('Sản phẩm "' + (product.name || '') + '" đã hết tồn kho!');
                return;
            }

            let addQty = this.getDraftQty(product);
            if (addQty < 1) addQty = 1;

            const existingIndex = this.cart.findIndex(it => String(it.product_id) === String(product.id));
            if (existingIndex >= 0) {
                const nextQty = this.cart[existingIndex].quantity + addQty;
                if (nextQty > avail) {
                    this.cart[existingIndex].quantity = avail;
                    this.showPosToast('Đã cập nhật tối đa tồn khả dụng (' + avail + ' ' + (product.unit || '') + ')');
                } else {
                    this.cart[existingIndex].quantity = nextQty;
                    this.showPosToast('Đã thêm +' + addQty + ' ' + (product.unit || '') + ' "' + (product.name || '') + '" (Tổng: ' + nextQty + ')');
                }
            } else {
                const finalQty = Math.min(avail, addQty);
                this.cart.push({
                    product_id: product.id,
                    sku: product.sku || '',
                    name: product.name || '',
                    unit: product.unit || 'cái',
                    price: Number(product.price) || 0,
                    quantity: finalQty,
                    category_name: product.category_name || '',
                });
                this.showPosToast('Đã thêm ' + finalQty + ' ' + (product.unit || '') + ' "' + (product.name || '') + '" vào giỏ');
            }

            this.draftQuantities[product.id] = 1;
            this.saveCartToStorage();
        },

        showPosToast(msg) {
            this.posToastMessage = msg;
            if (this.posToastTimer) clearTimeout(this.posToastTimer);
            this.posToastTimer = setTimeout(() => {
                this.posToastMessage = '';
            }, 2000);
        },

        // Cập nhật số lượng trực tiếp trong giỏ hàng
        updateCartQty(index, newQty) {
            if (index < 0 || index >= this.cart.length) return;
            const item = this.cart[index];
            const p = this.getProductById(item.product_id);
            const avail = p ? this.getProductAvailableStock(p) : 999999;
            let qty = (newQty === '' || newQty === null || newQty === undefined) ? 1 : Number(newQty);
            if (Number.isNaN(qty)) qty = 1;
            if (qty > avail) {
                alert('Số lượng sản phẩm "' + item.name + '" vượt quá tồn kho khả dụng (' + avail + ')!');
                qty = Math.max(1, avail);
            }
            if (qty <= 0) {
                this.removeFromCart(index);
                return;
            }
            this.cart[index].quantity = qty;
            this.saveCartToStorage();
        },

        // Xóa một món khỏi giỏ
        removeFromCart(index) {
            if (index >= 0 && index < this.cart.length) {
                this.cart.splice(index, 1);
                this.saveCartToStorage();
            }
        },

        // Xóa toàn bộ giỏ hàng
        clearCart() {
            if (confirm('Bạn có chắc muốn xóa toàn bộ sản phẩm trong giỏ hàng?')) {
                this.cart = [];
                this.saveCartToStorage();
            }
        },

        // Lưu giỏ hàng vào sessionStorage
        saveCartToStorage() {
            try {
                sessionStorage.setItem('pos_cart_items', JSON.stringify(this.cart));
                if (this.selectedDealerId) {
                    sessionStorage.setItem('pos_dealer_id', this.selectedDealerId);
                } else {
                    sessionStorage.removeItem('pos_dealer_id');
                }
                if (this.selectedWarehouse) {
                    sessionStorage.setItem('pos_warehouse', this.selectedWarehouse);
                } else {
                    sessionStorage.removeItem('pos_warehouse');
                }
            } catch (e) {
                console.error('Lỗi lưu giỏ tạm:', e);
            }
        },

        onDealerChange() {
            this.saveCartToStorage();
        },

        onWarehouseChange() {
            this.saveCartToStorage();
        },

        get cartTotalCount() {
            return this.cart.reduce((sum, it) => sum + (Number(it.quantity) || 0), 0);
        },

        get cartTotalAmount() {
            return this.cart.reduce((sum, it) => sum + ((Number(it.quantity) || 0) * (Number(it.price) || 0)), 0);
        },

        proceedToOrderCreate() {
            if (this.cart.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm vào giỏ hàng trước khi lên đơn!');
                return;
            }

            this.saveCartToStorage();
            sessionStorage.setItem('order_from_pos', '1');
            sessionStorage.setItem('order_return_url', window.location.href);
            window.location.href = "{{ route('orders.create') }}?from_pos=1";
        },

        handleGlobalKeydown(e) {
            if (e.key === 'F8') {
                e.preventDefault();
                this.proceedToOrderCreate();
                return;
            }

            if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                e.preventDefault();
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                    this.$refs.searchInput.select();
                }
                return;
            }

            if (e.key === 'Escape' && this.isQtyModalOpen) {
                this.closeQuantityModal();
                return;
            }
        },

        formatCurrency(num) {
            const val = Number(num) || 0;
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
        },

        formatNumber(num) {
            const val = Number(num) || 0;
            return new Intl.NumberFormat('vi-VN').format(val);
        }
    };
}
</script>

<style>
@media (max-width: 768px) {
    /* Ẩn thanh bottom nav chung của ERP trên màn hình POS để nhường chỗ cho thanh Thanh Toán cố định */
    .mobile-bottom-nav,
    .mobile-drawer-backdrop,
    .mobile-drawer-sheet {
        display: none !important;
    }
    main:has(.pos-bottom-bar),
    body:has(.pos-bottom-bar) main {
        padding-bottom: 0 !important;
    }
    main div.pos-app-wrapper {
        padding-bottom: calc(88px + env(safe-area-inset-bottom, 0px)) !important;
    }
}
.pos-app-wrapper {
    padding-bottom: calc(88px + env(safe-area-inset-bottom, 0px));
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
@endsection
