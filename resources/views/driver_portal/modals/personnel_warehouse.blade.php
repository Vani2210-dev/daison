    <!-- ========================================================================= -->
    <!-- MODAL: CHI TIẾT HÀNG KHO NHÂN SỰ QUẢN LÝ XE                               -->
    <!-- ========================================================================= -->
    <div x-show="showHoldingStockModal || showVanSaleItemsModal" x-cloak
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
        <div class="bg-slate-900 text-white rounded-3xl shadow-2xl border border-slate-800 w-full max-w-3xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.away="showHoldingStockModal = false; showVanSaleItemsModal = false">
            
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-slate-800 bg-gradient-to-r from-emerald-900/80 via-slate-900 to-teal-900/80 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black tracking-tight text-white">Chi Tiết Hàng Kho Nhân Sự Quản Lý Xe</h3>
                        <p class="text-xs text-emerald-300 font-mono mt-0.5" 
                           x-text="'Kho: ' + (holdingWarehouse?.name || 'Kho hàng ôm') + ' &bull; Phụ trách: ' + (vehicleManagerName || 'Lái xe') + ' (' + (selectedVehicle?.plate_number || '{{ $selectedPlate ?? '' }}') + ')'"></p>
                    </div>
                </div>
                <button type="button" @click="showHoldingStockModal = false; showVanSaleItemsModal = false" class="text-slate-400 hover:text-white transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-5 space-y-4 text-xs overflow-y-auto max-h-[75vh]">
                
                <!-- Quick KPI row -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <div class="p-3 bg-slate-950 rounded-2xl border border-slate-800">
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Tổng số SKU</span>
                        <span class="text-lg font-black font-mono text-emerald-400" x-text="holdingStocks.length + ' SP'"></span>
                    </div>
                    <div class="p-3 bg-slate-950 rounded-2xl border border-slate-800">
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Tổng số lượng tồn</span>
                        <span class="text-lg font-black font-mono text-sky-400" x-text="formatNumber(totalHoldingQty)"></span>
                    </div>
                    <div class="p-3 bg-slate-950 rounded-2xl border border-slate-800">
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Tổng giá trị tồn</span>
                        <span class="text-lg font-black font-mono text-amber-400" x-text="formatNumber(totalHoldingVal) + ' đ'"></span>
                    </div>
                    <div class="p-3 bg-slate-950 rounded-2xl border border-slate-800">
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Nợ hàng nhân sự</span>
                        <span class="text-lg font-black font-mono text-rose-400" x-text="formatNumber(empDebt) + ' đ'"></span>
                    </div>
                </div>

                <!-- Search bar in modal -->
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 pointer-events-none"></i>
                    <input type="text" x-model="holdingSearchQuery"
                           placeholder="Tìm nhanh theo tên sản phẩm hoặc mã SKU trong kho..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 text-xs">
                </div>

                <!-- Bảng danh sách hàng tồn -->
                <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/90 text-slate-400 text-[11px] font-bold uppercase border-b border-slate-800">
                            <tr>
                                <th class="p-3">Sản phẩm</th>
                                <th class="p-3 text-center">ĐVT</th>
                                <th class="p-3 text-right">SL Tồn</th>
                                <th class="p-3 text-right">Giá bán</th>
                                <th class="p-3 text-right">Thành tiền</th>
                                <th class="p-3 text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <template x-for="(it, idx) in filteredHoldingStocks" :key="it.id || idx">
                                <tr class="hover:bg-slate-900/50 transition-colors">
                                    <td class="p-3">
                                        <div class="font-bold text-white" x-text="it.product_name"></div>
                                        <div class="font-mono text-[10px] text-slate-400" x-text="'Mã: ' + (it.sku || '--')"></div>
                                    </td>
                                    <td class="p-3 text-center text-slate-300 font-medium" x-text="it.unit || 'cái'"></td>
                                    <td class="p-3 text-right font-mono font-black text-emerald-400 text-sm" x-text="it.quantity"></td>
                                    <td class="p-3 text-right font-mono text-slate-300" x-text="formatNumber(it.price) + ' đ'"></td>
                                    <td class="p-3 text-right font-mono font-bold text-emerald-300" x-text="formatNumber(it.amount) + ' đ'"></td>
                                    <td class="p-3 text-center">
                                        <a :href="'/inventory/holding-sale/create?warehouse_id=' + (holdingWarehouse ? holdingWarehouse.id : '') + '&product_id=' + it.product_id + '&return_url=' + encodeURIComponent(window.location.href)"
                                           class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] transition-colors inline-flex items-center gap-1 shadow-sm no-underline">
                                            <i class="fa-solid fa-cart-plus text-[10px]"></i>
                                            <span>Bán</span>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredHoldingStocks.length === 0">
                                <td colspan="6" class="p-8 text-center text-slate-400 italic">
                                    Không tìm thấy mặt hàng nào trong kho nhân sự phù hợp.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-slate-800/60 rounded-2xl border border-slate-700/60 text-slate-300 text-[11px] leading-relaxed flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        💡 <strong>Lưu ý:</strong> Hàng tồn kho này thuộc trách nhiệm quản lý của nhân sự <strong><span x-text="vehicleManagerName"></span></strong>. Khi phát sinh bán lẻ cho khách dọc đường, nhân sự có thể bấm "Bán hàng" để tạo đơn xuất trực tiếp trừ kho.
                    </div>
                    <a :href="'/inventory/holding-sale/create?warehouse_id=' + (holdingWarehouse ? holdingWarehouse.id : '') + '&return_url=' + encodeURIComponent(window.location.href)"
                       class="shrink-0 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm no-underline">
                        <i class="fa-solid fa-cart-plus"></i>
                        <span>+ Bán hàng từ kho này</span>
                    </a>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-3.5 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-400 font-mono" x-text="'Mã kho: ' + (holdingWarehouse?.code || '--')"></span>
                <button type="button" @click="showHoldingStockModal = false; showVanSaleItemsModal = false"
                        class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

