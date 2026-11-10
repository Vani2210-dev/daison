@extends('layouts.app')

@section('title', 'Giao Diện Bán Hàng & Chọn Sản Phẩm Nhanh (POS) - Đại Sơn ERP')

@section('content')
<div 
    x-data="posApp()" 
    x-init="init()" 
    @keydown.window="handleGlobalKeydown($event)"
    class="min-h-screen bg-slate-100/70 pb-36 sm:pb-28 lg:pb-16"
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
                            <h1 class="text-sm sm:text-lg font-black text-slate-800 tracking-tight flex items-center gap-1.5 truncate">
                                <i class="fa-solid fa-cash-register text-purple-600 text-xs sm:text-base"></i>
                                <span class="truncate">Bán Hàng POS</span>
                            </h1>
                            <span class="hidden md:inline-block px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[10px] font-extrabold uppercase">
                                Chọn Nhanh
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Nút Vào Trang Tạo Đơn -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <button 
                        type="button" 
                        @click="proceedToOrderCreate()" 
                        :disabled="cart.length === 0"
                        class="flex px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-xl text-xs font-black transition items-center gap-2 shadow-md cursor-pointer select-none"
                        :class="cart.length > 0 ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-emerald-600/30 active:scale-95' : 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'"
                        title="Chuyển toàn bộ sản phẩm trong giỏ vào trang tạo đơn (F8)"
                    >
                        <i class="fa-solid fa-file-circle-plus"></i>
                        <span class="hidden xs:inline">Vào Trang Tạo Đơn</span>
                        <span class="xs:hidden">Tạo Đơn</span>
                        <span 
                            x-show="cart.length > 0" 
                            class="px-1.5 sm:px-2 py-0.5 rounded-full bg-white/20 text-white text-[10px] sm:text-[11px] font-mono"
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
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs sm:text-sm"></i>
                            <input 
                                x-ref="searchInput"
                                type="text" 
                                x-model="searchQuery" 
                                @keydown.escape="searchQuery = ''"
                                placeholder="Tìm kiếm tên sản phẩm, mã SKU... (/)" 
                                class="w-full pl-8 sm:pl-9 pr-8 py-1.5 sm:py-2 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition shadow-2xs"
                            >
                            <button 
                                x-show="searchQuery" 
                                @click="searchQuery = ''; $refs.searchInput.focus()" 
                                type="button" 
                                class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 transition"
                            >
                                <i class="fa-solid fa-circle-xmark text-xs sm:text-sm"></i>
                            </button>
                        </div>

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
                <div class="flex items-center justify-between text-[11px] sm:text-xs text-slate-500 px-1">
                    <div>
                        <strong class="text-purple-700 font-bold" x-text="filteredProducts.length"></strong> sản phẩm
                        <span x-show="searchQuery">khớp "<strong class="text-slate-800" x-text="searchQuery"></strong>"</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="hidden xs:inline">Xếp:</span>
                        <select 
                            x-model="sortBy" 
                            class="bg-transparent font-bold text-slate-700 border-none focus:outline-none cursor-pointer text-[11px] sm:text-xs"
                        >
                            <option value="name_asc">Tên A-Z</option>
                            <option value="name_desc">Tên Z-A</option>
                            <option value="price_asc">Giá tăng dần</option>
                            <option value="price_desc">Giá giảm dần</option>
                            <option value="stock_desc">Tồn kho nhiều nhất</option>
                        </select>
                    </div>
                </div>

                <!-- 3. DANH SÁCH SẢN PHẨM DẠNG GRID (THẺ) TỐI ƯU CỰC ĐẸP TRÊN MOBILE & DESKTOP -->
                <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2.5 sm:gap-3">
                    <template x-for="p in filteredProducts" :key="p.id">
                        <div 
                            @click="openQuantityModal(p)"
                            class="group bg-white rounded-2xl border transition-all duration-150 p-2.5 sm:p-3 flex flex-col justify-between cursor-pointer relative shadow-2xs hover:shadow-md hover:border-purple-300 active:scale-98 select-none"
                            :class="getItemQuantityInCart(p.id) > 0 ? 'border-purple-400 bg-purple-50/20 ring-1 ring-purple-300' : 'border-slate-200/90'"
                        >
                            <!-- Badge Đã Thêm Vào Giỏ -->
                            <template x-if="getItemQuantityInCart(p.id) > 0">
                                <div class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black shadow-sm flex items-center gap-1 z-10 animate-bounce">
                                    <i class="fa-solid fa-check text-[8px]"></i>
                                    <span>Đã chọn <strong x-text="getItemQuantityInCart(p.id)"></strong></span>
                                </div>
                            </template>

                            <!-- Phần trên: Mã SKU & Tên sản phẩm -->
                            <div>
                                <!-- SKU Tag rõ ràng, không bị co giật trên mobile -->
                                <div class="flex items-center justify-between gap-1 mb-1.5">
                                    <span class="font-mono text-[9px] sm:text-[10px] font-extrabold px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-700 tracking-wider truncate" x-text="p.sku" :title="p.sku"></span>
                                    <span class="hidden sm:inline-block text-[9px] font-bold px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 truncate max-w-[90px]" x-text="p.category_name || 'Hàng hóa'"></span>
                                </div>

                                <!-- Tên sản phẩm -->
                                <h3 class="font-bold text-xs sm:text-sm text-slate-900 line-clamp-2 leading-snug group-hover:text-purple-700 transition min-h-[32px]" :title="p.name" x-text="p.name"></h3>

                                <!-- Quy cách rút gọn -->
                                <template x-if="p.specification">
                                    <p class="text-[9px] sm:text-[10px] text-slate-400 mt-0.5 line-clamp-1 italic" x-text="p.specification"></p>
                                </template>
                            </div>

                            <!-- Phần dưới: Giá bán & Tồn kho & Nút Thêm -->
                            <div class="mt-2 pt-2 border-t border-slate-100 flex items-end justify-between gap-1">
                                <div class="min-w-0">
                                    <!-- Đơn giá -->
                                    <div class="font-black text-xs sm:text-base text-purple-700 leading-tight truncate">
                                        <span x-text="formatCurrency(p.price)"></span>
                                        <span class="text-[9px] sm:text-[10px] font-normal text-slate-400" x-text="'/' + (p.unit || 'cái')"></span>
                                    </div>

                                    <!-- Tồn kho tại kho đang chọn -->
                                    <div class="mt-1 flex items-center gap-1 text-[10px] sm:text-[11px]">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="getProductAvailableStock(p) > 0 ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                        <span 
                                            class="font-bold truncate"
                                            :class="getProductAvailableStock(p) > 0 ? 'text-emerald-700' : 'text-rose-600'"
                                            x-text="getProductAvailableStock(p) > 0 ? 'Còn ' + formatNumber(getProductAvailableStock(p)) + ' ' + (p.unit || '') : 'Hết hàng'"
                                        ></span>
                                    </div>
                                </div>

                                <!-- Nút + chọn nhanh tiện lợi -->
                                <button 
                                    type="button" 
                                    @click.stop="openQuantityModal(p)"
                                    :disabled="getProductAvailableStock(p) <= 0"
                                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center transition shadow-2xs shrink-0 select-none"
                                    :class="getProductAvailableStock(p) <= 0 ? 'bg-slate-100 text-slate-300 border border-slate-200 cursor-not-allowed' : (getItemQuantityInCart(p.id) > 0 ? 'bg-purple-600 text-white shadow-purple-600/30 cursor-pointer active:scale-95' : 'bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 cursor-pointer active:scale-95')"
                                    :title="getProductAvailableStock(p) <= 0 ? 'Hết hàng trong kho' : 'Nhập số lượng'"
                                >
                                    <i class="fa-solid text-xs" :class="getProductAvailableStock(p) <= 0 ? 'fa-ban text-[10px]' : 'fa-plus'"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- 4. DANH SÁCH SẢN PHẨM DẠNG LIST (BẢNG) -->
                <div x-show="viewMode === 'list'" class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/90 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
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
                                        :class="getItemQuantityInCart(p.id) > 0 ? 'bg-purple-50/30' : ''"
                                    >
                                        <td class="py-3 px-3 text-center text-slate-400 font-mono" x-text="idx + 1"></td>
                                        <td class="py-3 px-3 font-mono font-bold text-slate-800 text-[11px]" x-text="p.sku"></td>
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-slate-900 group-hover:text-purple-700 transition" x-text="p.name"></div>
                                            <template x-if="p.specification">
                                                <div class="text-[10px] text-slate-400 line-clamp-1 italic" x-text="p.specification"></div>
                                            </template>
                                        </td>
                                        <td class="py-3 px-3 text-slate-600" x-text="p.category_name || '-'"></td>
                                        <td class="py-3 px-3 text-center font-bold text-slate-700" x-text="p.unit || '-'"></td>
                                        <td class="py-3 px-3 text-right font-black text-purple-700" x-text="formatCurrency(p.price)"></td>
                                        <td class="py-3 px-3 text-center">
                                            <span 
                                                class="px-2 py-0.5 rounded-full text-[11px] font-extrabold"
                                                :class="getProductAvailableStock(p) > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                                                x-text="getProductAvailableStock(p) > 0 ? formatNumber(getProductAvailableStock(p)) : 'Hết hàng'"
                                            ></span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <template x-if="getItemQuantityInCart(p.id) > 0">
                                                <span class="px-2 py-0.5 rounded-full bg-purple-600 text-white font-black text-[11px]" x-text="getItemQuantityInCart(p.id)"></span>
                                            </template>
                                            <template x-if="!getItemQuantityInCart(p.id)">
                                                <span class="text-slate-300">-</span>
                                            </template>
                                        </td>
                                        <td class="py-3 px-3 text-center" @click.stop>
                                            <button 
                                                type="button" 
                                                @click="openQuantityModal(p)"
                                                :disabled="getProductAvailableStock(p) <= 0"
                                                class="px-2.5 py-1 rounded-lg font-bold text-xs transition select-none flex items-center justify-center gap-1 mx-auto"
                                                :class="getProductAvailableStock(p) <= 0 ? 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed' : 'bg-purple-100 hover:bg-purple-200 text-purple-800 cursor-pointer active:scale-95'"
                                            >
                                                <i class="fa-solid" :class="getProductAvailableStock(p) <= 0 ? 'fa-ban text-[10px]' : 'fa-plus text-[10px]'"></i>
                                                <span x-text="getProductAvailableStock(p) <= 0 ? 'Hết hàng' : 'Chọn'"></span>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

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
        class="fixed bottom-0 sm:bottom-20 left-0 right-0 sm:left-auto sm:right-6 z-[9998] w-full sm:w-[420px] max-w-full sm:max-w-[calc(100vw-24px)] bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200/90 flex flex-col max-h-[82vh] sm:max-h-[600px] overflow-hidden"
        @click.stop
    >
        <!-- Header Popup Giỏ Hàng -->
        <div class="p-3.5 sm:p-4 bg-gradient-to-r from-purple-700 via-indigo-700 to-purple-800 text-white flex items-center justify-between shrink-0 shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-white/15 flex items-center justify-center text-sm shadow-inner">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div>
                    <h2 class="font-black text-sm uppercase tracking-wide">Giỏ Hàng Tạm</h2>
                    <span class="text-[11px] text-purple-200" x-text="cartTotalCount + ' món (' + cart.length + ' mã hàng)'"></span>
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                <button 
                    type="button" 
                    x-show="cart.length > 0" 
                    @click="clearCart()" 
                    class="px-2.5 py-1 rounded-xl bg-white/10 hover:bg-rose-500/80 text-white text-[11px] font-bold transition cursor-pointer flex items-center gap-1"
                    title="Xóa toàn bộ giỏ"
                >
                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                    <span>Xóa hết</span>
                </button>

                <button 
                    type="button" 
                    @click="isCartOpen = false" 
                    class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer"
                    title="Đóng popup"
                >
                    <i class="fa-solid fa-xmark text-sm"></i>
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
                    <p class="text-xs font-bold text-slate-700">Giỏ hàng đang trống</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Bấm vào bất kỳ sản phẩm nào để thêm vào giỏ</p>
                </div>
            </template>

            <template x-for="(item, idx) in cart" :key="item.product_id">
                <div class="p-2.5 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white transition flex flex-col gap-1.5">
                    <div class="flex items-start justify-between gap-1.5">
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-xs text-slate-900 truncate" :title="item.name" x-text="item.name"></div>
                            <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
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
                            <i class="fa-regular fa-trash-can text-xs"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100">
                        <div class="flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden shadow-2xs">
                            <button 
                                type="button" 
                                @click="updateCartQty(idx, item.quantity - 1)" 
                                class="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition cursor-pointer text-xs"
                            >
                                <i class="fa-solid fa-minus text-[8px]"></i>
                            </button>
                            <input 
                                type="number" 
                                x-model.number="item.quantity" 
                                @change="updateCartQty(idx, item.quantity)"
                                min="1" 
                                :max="getProductAvailableStock(getProductById(item.product_id))"
                                class="w-10 h-6 text-center font-bold text-xs text-slate-800 border-none focus:outline-none"
                            >
                            <button 
                                type="button" 
                                @click="updateCartQty(idx, item.quantity + 1)" 
                                :disabled="item.quantity >= getProductAvailableStock(getProductById(item.product_id))"
                                class="w-6 h-6 flex items-center justify-center text-slate-600 transition text-xs"
                                :class="item.quantity >= getProductAvailableStock(getProductById(item.product_id)) ? 'opacity-30 cursor-not-allowed' : 'hover:bg-slate-100 cursor-pointer'"
                            >
                                <i class="fa-solid fa-plus text-[8px]"></i>
                            </button>
                        </div>

                        <div class="font-black text-xs text-purple-800" x-text="formatCurrency(item.quantity * item.price)"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer Giỏ Hàng: Tạm tính & Nút chuyển sang trang thêm đơn -->
        <div class="p-3 sm:p-3.5 bg-slate-50 border-t border-slate-200 shrink-0 space-y-2.5 pb-[max(14px,env(safe-area-inset-bottom,14px))] sm:pb-3.5">
            <div class="space-y-1 text-xs">
                <div class="flex items-center justify-between text-slate-500 text-[11px]">
                    <span>Tổng số lượng:</span>
                    <span class="font-bold text-slate-800" x-text="formatNumber(cartTotalCount) + ' món'"></span>
                </div>
                <div class="flex items-center justify-between text-xs sm:text-sm pt-1 border-t border-slate-200/80">
                    <span class="font-bold text-slate-700">Tạm tính tiền hàng:</span>
                    <span class="font-black text-sm sm:text-base text-purple-700" x-text="formatCurrency(cartTotalAmount)"></span>
                </div>
            </div>

            <button 
                type="button" 
                @click="proceedToOrderCreate()" 
                :disabled="cart.length === 0"
                class="w-full py-2.5 sm:py-3 rounded-2xl font-black text-xs sm:text-sm flex items-center justify-center gap-2 transition cursor-pointer shadow-md select-none"
                :class="cart.length > 0 ? 'bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 hover:from-purple-700 text-white shadow-purple-600/30 active:scale-98' : 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'"
            >
                <i class="fa-solid fa-file-circle-plus"></i>
                <span>CHUYỂN VÀO TẠO ĐƠN</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </div>
    </div>

    <!-- NÚT GIỎ HÀNG NỔI TRÒN Ở DƯỚI GÓC MÀN HÌNH (FAB ICON CHỈ HIỆN SỐ SẢN PHẨM) -->
    <button 
        type="button" 
        @click="isCartOpen = !isCartOpen" 
        class="pos-floating-cart-btn fixed right-4 sm:right-6 z-[9995] w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 text-white shadow-xl hover:shadow-purple-600/40 hover:scale-105 active:scale-95 transition-all flex items-center justify-center cursor-pointer border-2 border-white select-none"
        :class="cart.length > 0 ? 'ring-4 ring-purple-400/30' : ''"
        :title="'Xem giỏ hàng (' + cart.length + ' sản phẩm)'"
    >
        <i class="fa-solid fa-cart-shopping text-base sm:text-lg"></i>
        <span 
            x-show="cart.length > 0" 
            class="absolute -top-1.5 -right-1.5 min-w-[20px] h-[20px] px-1 rounded-full bg-rose-500 text-white text-[10px] sm:text-[11px] font-black flex items-center justify-center shadow-md border-2 border-white animate-pulse"
            x-text="cart.length"
        ></span>
    </button>

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
                        <h3 class="font-black text-sm">Nhập Số Lượng Vào Giỏ</h3>
                        <p class="text-[11px] text-purple-100" x-text="selectedProduct?.sku"></p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="closeQuantityModal()" 
                    class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-4 sm:p-5 space-y-3 sm:space-y-4">
                <!-- Thông tin sản phẩm -->
                <div class="p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <h4 class="font-bold text-xs sm:text-sm text-slate-900" x-text="selectedProduct?.name"></h4>
                    <div class="flex items-center justify-between text-xs mt-1.5 pt-1.5 border-t border-slate-200">
                        <span class="text-slate-500">Đơn giá:</span>
                        <span class="font-black text-purple-700" x-text="formatCurrency(selectedProduct?.price) + ' / ' + (selectedProduct?.unit || 'cái')"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs mt-1">
                        <span class="text-slate-500">Tồn khả dụng (<span x-text="selectedWarehouse"></span>):</span>
                        <span 
                            class="font-bold"
                            :class="getProductAvailableStock(selectedProduct) > 0 ? 'text-emerald-700' : 'text-rose-600'"
                            x-text="formatNumber(getProductAvailableStock(selectedProduct)) + ' ' + (selectedProduct?.unit || '')"
                        ></span>
                    </div>
                </div>

                <!-- Cảnh báo khi HẾT HÀNG TỒN KHẢ DỤNG -->
                <div x-show="getProductAvailableStock(selectedProduct) <= 0" class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-base shrink-0"></i>
                    <div>
                        <div class="font-black text-rose-800">Sản phẩm hiện đã hết hàng tồn kho khả dụng!</div>
                        <div class="text-[11px] font-medium text-rose-600 mt-0.5">Không thể thêm vào giỏ hàng. Vui lòng chọn sản phẩm khác.</div>
                    </div>
                </div>

                <!-- Ô nhập số lượng (Chỉ hiện khi CÒN HÀNG) -->
                <div x-show="getProductAvailableStock(selectedProduct) > 0">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
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
                            class="w-full h-11 text-center font-black text-xl text-purple-900 border-none focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
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
                    <span class="block text-[11px] font-semibold text-slate-400 mb-1">Chọn nhanh:</span>
                    <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                        <template x-for="q in [1, 5, 10, 20, 50, 100]" :key="q">
                            <button 
                                type="button" 
                                @click="modalQuantity = Math.min(getProductAvailableStock(selectedProduct), q)" 
                                :disabled="q > getProductAvailableStock(selectedProduct)"
                                class="px-2.5 py-1 rounded-xl transition cursor-pointer"
                                :class="q <= getProductAvailableStock(selectedProduct) ? 'bg-slate-100 hover:bg-purple-100 hover:text-purple-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'"
                                x-text="q"
                            ></button>
                        </template>
                        <button 
                            type="button" 
                            @click="modalQuantity = getProductAvailableStock(selectedProduct)" 
                            class="px-2.5 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer"
                        >
                            Hết tồn (<span x-text="formatNumber(getProductAvailableStock(selectedProduct))"></span>)
                        </button>
                    </div>
                </div>

                <!-- Tạm tính tiền cho món này (Chỉ hiện khi CÒN HÀNG) -->
                <div x-show="getProductAvailableStock(selectedProduct) > 0" class="p-2.5 rounded-2xl bg-purple-50/60 border border-purple-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-purple-900">Thành tiền:</span>
                    <span class="text-sm sm:text-base font-black text-purple-700" x-text="formatCurrency(modalQuantity * (selectedProduct?.price || 0))"></span>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-3 sm:p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2 pb-[max(16px,env(safe-area-inset-bottom,16px))] sm:pb-4">
                <button 
                    type="button" 
                    @click="closeQuantityModal()" 
                    class="px-3.5 py-2 rounded-xl border border-slate-300 font-bold text-xs text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                >
                    Hủy (Esc)
                </button>
                <button 
                    type="button" 
                    @click="submitQuantityModal()" 
                    :disabled="getProductAvailableStock(selectedProduct) <= 0 || modalQuantity <= 0"
                    class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-black text-xs transition cursor-pointer shadow-md flex items-center justify-center gap-1.5 select-none"
                    :class="getProductAvailableStock(selectedProduct) > 0 && modalQuantity > 0 ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-purple-600/30 active:scale-95' : 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'"
                >
                    <i class="fa-solid" :class="getProductAvailableStock(selectedProduct) > 0 ? 'fa-cart-plus' : 'fa-ban'"></i>
                    <span x-text="getProductAvailableStock(selectedProduct) > 0 ? 'Thêm Vào Giỏ' : 'Hết Hàng'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        // Dữ liệu từ Controller
        products: @json($products ?? []),
        categories: @json($categories ?? []),
        brands: @json($brands ?? []),
        dealers: @json($dealers ?? []),
        warehouses: @json($warehouses ?? []),
        priceTiers: @json($priceTiers ?? []),

        // Bộ lọc & Trạng thái UI
        searchQuery: '',
        selectedCategory: 'all',
        selectedBrand: 'all',
        stockFilter: 'all',
        sortBy: 'name_asc',
        viewMode: 'grid', // 'grid' | 'list'
        selectedWarehouse: '{{ \App\Models\Warehouse::first()?->name ?? "Kho Tổng" }}',
        selectedDealerId: '',
        isCartOpen: false,

        // Giỏ hàng tạm thời
        cart: [],

        // Modal số lượng
        isQtyModalOpen: false,
        selectedProduct: null,
        modalQuantity: 1,

        init() {
            // Đặt kho mặc định
            if (this.warehouses && this.warehouses.length > 0) {
                this.selectedWarehouse = this.warehouses[0].name;
            }

            // Tải giỏ hàng từ sessionStorage nếu có lưu dở trước đó
            try {
                const savedCart = sessionStorage.getItem('pos_cart_items');
                if (savedCart) {
                    const parsed = JSON.parse(savedCart);
                    if (Array.isArray(parsed)) {
                        this.cart = parsed;
                    }
                }
                const savedDealer = sessionStorage.getItem('pos_dealer_id');
                if (savedDealer) {
                    this.selectedDealerId = savedDealer;
                }
            } catch (e) {
                console.error('Lỗi khi tải giỏ tạm:', e);
            }
        },

        // Danh sách sản phẩm sau lọc & tìm kiếm
        get filteredProducts() {
            let list = this.products;

            // 1. Lọc theo từ khóa tìm kiếm
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(p => {
                    const name = (p.name || '').toLowerCase();
                    const sku = (p.sku || '').toLowerCase();
                    const spec = (p.specification || '').toLowerCase();
                    return name.includes(q) || sku.includes(q) || spec.includes(q);
                });
            }

            // 2. Lọc theo Danh mục
            if (this.selectedCategory !== 'all') {
                list = list.filter(p => String(p.category_id) === String(this.selectedCategory));
            }

            // 3. Lọc theo Thương hiệu
            if (this.selectedBrand !== 'all') {
                list = list.filter(p => String(p.brand_id) === String(this.selectedBrand));
            }

            // 4. Lọc theo Tồn kho
            if (this.stockFilter === 'in_stock') {
                list = list.filter(p => this.getProductAvailableStock(p) > 0);
            } else if (this.stockFilter === 'low_stock') {
                list = list.filter(p => {
                    const st = this.getProductAvailableStock(p);
                    return st > 0 && st <= 20;
                });
            } else if (this.stockFilter === 'out_of_stock') {
                list = list.filter(p => this.getProductAvailableStock(p) <= 0);
            }

            // 5. Sắp xếp
            list = [...list].sort((a, b) => {
                if (this.sortBy === 'name_asc') return (a.name || '').localeCompare(b.name || '');
                if (this.sortBy === 'name_desc') return (b.name || '').localeCompare(a.name || '');
                if (this.sortBy === 'price_asc') return (Number(a.price) || 0) - (Number(b.price) || 0);
                if (this.sortBy === 'price_desc') return (Number(b.price) || 0) - (Number(a.price) || 0);
                if (this.sortBy === 'stock_desc') return this.getProductAvailableStock(b) - this.getProductAvailableStock(a);
                return 0;
            });

            return list;
        },

        // Tính tồn kho khả dụng của sản phẩm theo kho đang chọn
        getProductAvailableStock(product) {
            if (!product) return 0;
            if (product.warehouse_stocks && this.selectedWarehouse && product.warehouse_stocks[this.selectedWarehouse]) {
                return Number(product.warehouse_stocks[this.selectedWarehouse].available || 0);
            }
            return Number(product.available_stock !== undefined ? product.available_stock : (product.stock_quantity || 0));
        },

        // Đếm số sản phẩm trong 1 danh mục
        getCategoryCount(categoryId) {
            return this.products.filter(p => String(p.category_id) === String(categoryId)).length;
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

        // Cập nhật số lượng trực tiếp trong giỏ hàng
        updateCartQty(index, newQty) {
            if (index < 0 || index >= this.cart.length) return;
            const item = this.cart[index];
            const p = this.getProductById(item.product_id);
            const avail = p ? this.getProductAvailableStock(p) : 999999;
            let qty = Number(newQty) || 1;
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
/* Vị trí nút Giỏ hàng nổi vượt lên trên thanh menu bottom mobile */
.pos-floating-cart-btn {
    bottom: calc(58px + env(safe-area-inset-bottom, 0px) + 12px);
}
@media (min-width: 640px) {
    .pos-floating-cart-btn {
        bottom: 1.5rem !important; /* 24px trên tablet / desktop */
    }
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
