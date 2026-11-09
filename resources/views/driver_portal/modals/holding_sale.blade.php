    <!-- ========================================================================= -->
    <!-- MODAL: BÁN HÀNG TRỰC TIẾP TỪ KHO NHÂN SỰ (TƯƠNG TỰ HOLDING SALE CREATE)    -->
    <!-- ========================================================================= -->
    <div x-show="showHoldingSaleModal" x-cloak
         class="fixed inset-0 z-[10000] overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white text-slate-800 rounded-3xl shadow-2xl border border-slate-200 w-full max-w-4xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.away="showHoldingSaleModal = false">
            
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-slate-200 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 text-emerald-400 border border-white/20 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-black tracking-tight text-white">Tạo Đơn Bán Hàng Trực Tiếp Từ Kho Nhân Sự</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Xuất & Trừ Kho Ngay</span>
                        </div>
                        <p class="text-xs text-slate-300 font-medium mt-0.5">
                            Kho: <strong class="text-white" x-text="holdingWarehouse?.name || 'Kho hàng ôm'"></strong> &bull;
                            Nhân sự: <strong class="text-emerald-300" x-text="vehicleManagerName || 'Lái xe'"></strong> &bull;
                            Xe: <strong class="text-purple-300" x-text="selectedVehicle?.plate_number || '{{ $selectedPlate ?? '' }}'"></strong>
                        </p>
                    </div>
                </div>
                <button type="button" @click="showHoldingSaleModal = false" class="text-slate-300 hover:text-white transition-colors cursor-pointer text-xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-6 space-y-4 text-xs overflow-y-auto max-h-[75vh]">
                
                <!-- BƯỚC 1: THÔNG TIN KHÁCH HÀNG / ĐẠI LÝ -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <label class="font-black text-slate-800 text-xs uppercase tracking-tight flex items-center gap-1.5">
                            <i class="fa-solid fa-user-tag text-blue-700"></i>
                            <span>1. Thông tin Khách hàng / Đại lý nhận hàng (*)</span>
                        </label>
                        <!-- Tab chọn kiểu khách -->
                        <div class="inline-flex rounded-xl bg-slate-200/80 p-0.5 text-[11px] font-bold">
                            <button type="button" @click="saleForm.customer_type = 'trip_dealer'"
                                    :class="saleForm.customer_type === 'trip_dealer' ? 'bg-white text-blue-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                                Đại lý trên xe hôm nay
                            </button>
                            <button type="button" @click="saleForm.customer_type = 'dealer'"
                                    :class="saleForm.customer_type === 'dealer' ? 'bg-white text-blue-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                                Tìm đại lý hệ thống
                            </button>
                            <button type="button" @click="saleForm.customer_type = 'walk_in'"
                                    :class="saleForm.customer_type === 'walk_in' ? 'bg-white text-blue-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                                Khách vãng lai / Khác
                            </button>
                        </div>
                    </div>

                    <!-- Trường hợp 1: Chọn nhanh đại lý trên xe hôm nay -->
                    <template x-if="saleForm.customer_type === 'trip_dealer'">
                        <div class="space-y-2">
                            <div class="text-[11px] text-slate-500 font-medium">Bấm chọn nhanh một đại lý trên chuyến giao hôm nay:</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                <template x-for="td in tripDealers" :key="td.dealer_id || td.name">
                                    <div @click="selectTripDealer(td)"
                                         :class="(saleForm.customer_name === td.name) ? 'ring-2 ring-blue-600 bg-blue-50/80 border-blue-400' : 'bg-white hover:bg-slate-100 border-slate-200'"
                                         class="p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-2 shadow-2xs">
                                        <div class="min-w-0">
                                            <div class="font-bold text-xs text-slate-900 truncate" x-text="td.name"></div>
                                            <div class="text-[10px] text-slate-500 truncate" x-text="td.address || td.phone || 'Điểm giao'"></div>
                                        </div>
                                        <span x-show="saleForm.customer_name === td.name" class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] shrink-0">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Trường hợp 2: Tìm đại lý trong toàn bộ hệ thống -->
                    <template x-if="saleForm.customer_type === 'dealer'">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Chọn Đại Lý (*):</label>
                                <select x-model="saleForm.dealer_id" @change="onSystemDealerChange()"
                                        class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600">
                                    <option value="">-- Chọn đại lý từ danh bạ --</option>
                                    <template x-for="dl in dealers" :key="dl.id">
                                        <option :value="dl.id" x-text="dl.name + ' (' + (dl.code || dl.phone || 'Đại lý') + ')'"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Số điện thoại:</label>
                                <input type="text" x-model="saleForm.customer_phone" placeholder="SĐT khách..."
                                       class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-600">
                            </div>
                        </div>
                    </template>

                    <!-- Trường hợp 3: Khách vãng lai / Khách lẻ -->
                    <template x-if="saleForm.customer_type === 'walk_in'">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Tên khách / Đơn vị mua (*):</label>
                                <input type="text" x-model="saleForm.customer_name" placeholder="VD: Anh Tuấn (Công trình X)"
                                       class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Số điện thoại:</label>
                                <input type="text" x-model="saleForm.customer_phone" placeholder="VD: 0912..."
                                       class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-600">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Địa chỉ giao:</label>
                                <input type="text" x-model="saleForm.customer_address" placeholder="Địa chỉ giao nhận..."
                                       class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-600">
                            </div>
                        </div>
                    </template>

                    <!-- Khách hàng đã chọn -->
                    <div class="p-2.5 bg-blue-50/60 rounded-xl border border-blue-200/80 flex flex-wrap items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500 font-semibold">Khách mua:</span>
                            <strong class="text-blue-900 font-black text-sm" x-text="saleForm.customer_name || '(Chưa chọn khách)'"></strong>
                        </div>
                        <div class="flex items-center gap-3 text-slate-600 text-[11px]">
                            <span x-show="saleForm.customer_phone" x-text="'SĐT: ' + saleForm.customer_phone"></span>
                            <span x-show="saleForm.customer_address" class="truncate max-w-xs" x-text="'ĐC: ' + saleForm.customer_address"></span>
                        </div>
                    </div>
                </div>

                <!-- BƯỚC 2: CHỌN SẢN PHẨM TRONG KHO ÔM ĐỂ BÁN -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <label class="font-black text-slate-800 text-xs uppercase tracking-tight flex items-center gap-1.5">
                            <i class="fa-solid fa-boxes-stacked text-emerald-600"></i>
                            <span>2. Danh sách sản phẩm bán từ kho ôm (*)</span>
                        </label>
                        <span class="text-[11px] text-slate-500 font-mono" x-text="saleForm.items.length + ' sản phẩm trong đơn'"></span>
                    </div>

                    <!-- Ô tìm & chọn sản phẩm nhanh -->
                    <div class="relative" @click.outside="showSaleProductDropdown = false">
                        <div class="relative flex items-center">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs pointer-events-none"></i>
                            <input type="text"
                                   x-model="saleProductSearch"
                                   @focus="showSaleProductDropdown = true"
                                   @input="showSaleProductDropdown = true"
                                   placeholder="Gõ tên hoặc mã SKU sản phẩm trong kho ôm để thêm..."
                                   class="w-full pl-8 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-600 shadow-2xs">
                        </div>

                        <!-- Dropdown menu gợi ý -->
                        <div x-show="showSaleProductDropdown && filteredSaleProducts.length > 0"
                             class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100 p-1">
                            <template x-for="stk in filteredSaleProducts" :key="stk.product_id">
                                <div @click="addSaleItem(stk)"
                                     class="p-2.5 hover:bg-emerald-50/80 rounded-xl cursor-pointer transition-colors flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-slate-900 truncate" x-text="stk.product_name"></div>
                                        <div class="text-[10px] text-slate-500 font-mono" x-text="'Mã: ' + (stk.sku || '--') + ' | ĐVT: ' + (stk.unit || 'cái') + ' | Giá bán: ' + formatNumber(stk.price) + ' đ'"></div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="px-2 py-0.5 rounded-md font-mono font-bold text-xs bg-emerald-100 text-emerald-800"
                                              x-text="'Tồn: ' + stk.quantity + ' ' + (stk.unit || '')"></span>
                                        <span class="block text-[10px] text-emerald-600 font-bold mt-0.5">+ Thêm</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Bảng các sản phẩm đã thêm vào đơn -->
                    <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                        <table class="w-full text-left text-xs divide-y divide-slate-200">
                            <thead class="bg-slate-100 text-slate-600 text-[10.5px] font-bold uppercase">
                                <tr>
                                    <th class="p-2.5">Sản phẩm</th>
                                    <th class="p-2.5 text-center">Tồn kho</th>
                                    <th class="p-2.5 text-center w-32">SL Bán</th>
                                    <th class="p-2.5 text-right w-32">Đơn giá</th>
                                    <th class="p-2.5 text-right w-32">Thành tiền</th>
                                    <th class="p-2.5 text-center w-10">Xóa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(it, iIdx) in saleForm.items" :key="it.product_id">
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="p-2.5">
                                            <div class="font-bold text-slate-900 text-xs" x-text="it.name"></div>
                                            <div class="text-[10px] text-slate-500 font-mono" x-text="'SKU: ' + it.sku + ' | ĐVT: ' + it.unit"></div>
                                        </td>
                                        <td class="p-2.5 text-center font-mono font-bold text-emerald-700" x-text="it.available_stock"></td>
                                        <td class="p-2.5 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button" @click="if (it.quantity > 1) it.quantity--"
                                                        class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 font-bold text-slate-700 flex items-center justify-center cursor-pointer">-</button>
                                                <input type="number" min="1" :max="it.available_stock"
                                                       x-model.number="it.quantity"
                                                       class="w-14 h-7 text-center font-mono font-bold text-xs border border-slate-300 rounded focus:outline-none focus:border-blue-600">
                                                <button type="button" @click="if (it.quantity < it.available_stock) it.quantity++"
                                                        class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 font-bold text-slate-700 flex items-center justify-center cursor-pointer">+</button>
                                            </div>
                                        </td>
                                        <td class="p-2.5 text-right">
                                            <input type="number" step="1000" min="0"
                                                   x-model.number="it.unit_price"
                                                   class="w-24 h-7 px-2 text-right font-mono font-bold text-xs border border-slate-300 rounded focus:outline-none focus:border-blue-600">
                                        </td>
                                        <td class="p-2.5 text-right font-mono font-black text-emerald-700 text-xs"
                                            x-text="formatNumber(it.quantity * it.unit_price) + ' đ'"></td>
                                        <td class="p-2.5 text-center">
                                            <button type="button" @click="removeSaleItem(iIdx)"
                                                    class="text-rose-500 hover:text-rose-700 text-sm cursor-pointer" title="Xóa mặt hàng này">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="saleForm.items.length === 0">
                                    <td colspan="6" class="p-6 text-center text-slate-400 italic">
                                        Chưa có sản phẩm nào được chọn. Vui lòng gõ tìm và chọn sản phẩm ở ô trên.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BƯỚC 3: HÌNH THỨC THANH TOÁN & GHI CHÚ -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Thanh toán -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5">
                        <label class="font-black text-slate-800 text-xs uppercase tracking-tight flex items-center gap-1.5">
                            <i class="fa-solid fa-wallet text-amber-600"></i>
                            <span>3. Hình thức thanh toán</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="saleForm.payment_method = 'Tiền mặt'"
                                    :class="saleForm.payment_method === 'Tiền mặt' ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-sm' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'"
                                    class="py-2 rounded-xl border text-xs flex flex-col items-center justify-center gap-1 cursor-pointer transition-colors">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>Tiền mặt</span>
                            </button>
                            <button type="button" @click="saleForm.payment_method = 'VietQR'"
                                    :class="saleForm.payment_method === 'VietQR' ? 'bg-sky-600 text-white font-bold border-sky-600 shadow-sm' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'"
                                    class="py-2 rounded-xl border text-xs flex flex-col items-center justify-center gap-1 cursor-pointer transition-colors">
                                <i class="fa-solid fa-qrcode"></i>
                                <span>VietQR</span>
                            </button>
                            <button type="button" @click="saleForm.payment_method = 'Công nợ (Ghi nợ đại lý)'"
                                    :disabled="!saleForm.dealer_id"
                                    :class="saleForm.payment_method === 'Công nợ (Ghi nợ đại lý)' ? 'bg-purple-700 text-white font-bold border-purple-700 shadow-sm' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'"
                                    class="py-2 rounded-xl border text-xs flex flex-col items-center justify-center gap-1 cursor-pointer transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                <span>Ghi nợ</span>
                            </button>
                        </div>

                        <!-- Mã VietQR thanh toán nhanh tại chỗ -->
                        <div x-show="saleForm.payment_method === 'VietQR' && saleTotalAmount > 0"
                             class="p-3 bg-white rounded-xl border border-sky-200 flex flex-col items-center gap-1.5 text-center">
                            <img :src="holdingSaleVietQrUrl" alt="VietQR" class="w-36 h-36 object-contain rounded-lg border border-slate-100 shadow-inner">
                            <div class="text-[10px] text-slate-500">Quét mã chuyển khoản MBBank:</div>
                            <div class="font-mono font-black text-sky-700 text-sm" x-text="formatNumber(saleTotalAmount) + ' đ'"></div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Ghi chú đơn bán:</label>
                            <input type="text" x-model="saleForm.notes" placeholder="VD: Khách lấy thêm 5 cây tại điểm giao..."
                                   class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-600">
                        </div>
                    </div>

                    <!-- Tổng kết giá trị -->
                    <div class="p-4 bg-slate-900 text-white rounded-2xl flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider block">Tổng Kết Đơn Bán Kho Ôm</span>
                            <div class="mt-2 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between text-slate-300">
                                    <span>Tổng số lượng sản phẩm:</span>
                                    <strong class="font-mono text-white text-sm" x-text="saleTotalQty + ' SP'"></strong>
                                </div>
                                <div class="flex items-center justify-between text-slate-300">
                                    <span>Số loại mặt hàng:</span>
                                    <strong class="font-mono text-white" x-text="saleForm.items.length + ' loại'"></strong>
                                </div>
                                <div class="flex items-center justify-between text-slate-300">
                                    <span>Hình thức:</span>
                                    <span class="font-bold text-emerald-300" x-text="saleForm.payment_method"></span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-800">
                            <div class="flex items-center justify-between">
                                <span class="text-xs uppercase font-bold text-slate-400">Tổng Thành Tiền:</span>
                                <span class="text-2xl font-black font-mono text-emerald-400" x-text="formatNumber(saleTotalAmount) + ' đ'"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <a :href="'/inventory/holding-sale/create?warehouse_id=' + (holdingWarehouse ? holdingWarehouse.id : '')"
                   target="_blank"
                   class="text-xs text-blue-700 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    <span>Mở màn hình bán hàng đầy đủ trong tab mới ↗</span>
                </a>

                <div class="flex items-center gap-2 justify-end">
                    <button type="button" @click="showHoldingSaleModal = false"
                            class="px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                        Hủy
                    </button>
                    <button type="button" @click="submitHoldingSale()" :disabled="isSubmittingHoldingSale || saleForm.items.length === 0"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 text-white font-black text-xs transition-all shadow-md shadow-emerald-600/30 flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-circle-check"></i>
                        <span x-text="isSubmittingHoldingSale ? 'Đang xuất kho...' : 'Xác Nhận Bán & Xuất Kho Ngay'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
