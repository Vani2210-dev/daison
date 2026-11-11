    <!-- ========================================================================= -->
    <!-- 3. 5 THẺ KPI TIẾN ĐỘ & TÀI CHÍNH (Đã tối ưu lưới 2 cột siêu gọn cho Mobile) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-2 sm:gap-3.5 text-xs">
        
        <!-- 1. Tiến độ giao hàng (Mobile: Dải tiến độ Full-width siêu gọn; Desktop: 1 cột chuẩn) -->
        <div class="col-span-2 lg:col-span-1 bg-white rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-slate-200 shadow-2xs flex flex-col justify-between gap-1.5">
            <div class="flex items-center justify-between text-slate-700 font-bold">
                <span class="flex items-center gap-1.5 text-xs">
                    <i class="fa-solid fa-box text-purple-600 text-xs"></i>
                    <span>Tiến độ giao hàng</span>
                </span>
                <div class="flex items-center gap-1.5 font-mono">
                    <span class="text-xs sm:text-sm font-black text-slate-900" x-text="completedOrdersCount + '/' + orders.length + ' đơn'"></span>
                    <span class="text-[10px] sm:text-[11px] font-black text-purple-700 bg-purple-50 border border-purple-200/60 px-1.5 py-0.5 rounded-full" x-text="progressPercent + '%'"></span>
                </div>
            </div>
            <!-- Progress bar -->
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-purple-600 h-1.5 rounded-full transition-all duration-500" :style="'width: ' + progressPercent + '%'"></div>
            </div>
        </div>

        <!-- 2. Tiền mặt Lái xe đang cầm -->
        <div class="col-span-1 bg-amber-50/70 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-amber-200/90 shadow-2xs flex flex-col justify-between gap-1.5">
            <div>
                <div class="flex items-center justify-between text-amber-900 font-bold text-[11px] sm:text-xs">
                    <span class="truncate">Tiền mặt cầm</span>
                    <i class="fa-solid fa-sack-dollar text-amber-600 text-xs sm:text-sm shrink-0"></i>
                </div>
                <div class="text-sm sm:text-xl font-black text-amber-950 font-mono tracking-tight mt-0.5 truncate"
                     x-text="formatNumber(cashInHand) + ' đ'">
                </div>
                <div class="text-[10px] sm:text-[11px] text-amber-800/80 font-medium mt-0.5 flex items-center gap-1 truncate">
                    <i class="fa-regular fa-clock text-[9px]"></i>
                    <span class="truncate">Nộp cuối ca</span>
                </div>
            </div>
            <button type="button" @click="openHandoverModal()"
                    class="w-full h-[28px] sm:h-[32px] px-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-lg sm:rounded-xl text-[10.5px] sm:text-[11px] transition-colors flex items-center justify-center gap-1 cursor-pointer shadow-xs truncate">
                <i class="fa-solid fa-money-check-dollar text-[10px]"></i>
                <span class="truncate" x-text="hasPendingCodSubmission ? 'Đã gửi COD →' : 'Nộp COD →'"></span>
            </button>
        </div>

        <!-- 3. Đã thanh toán VietQR -->
        <div class="col-span-1 bg-emerald-50/70 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-emerald-200/90 shadow-2xs flex flex-col justify-between gap-1.5">
            <div>
                <div class="flex items-center justify-between text-emerald-900 font-bold text-[11px] sm:text-xs">
                    <span class="truncate">Thu VietQR</span>
                    <i class="fa-solid fa-qrcode text-emerald-600 text-xs sm:text-sm shrink-0"></i>
                </div>
                <div class="text-sm sm:text-xl font-black text-emerald-950 font-mono tracking-tight mt-0.5 truncate"
                     x-text="formatNumber(vietQrAmount) + ' đ'">
                </div>
                <div class="text-[10px] sm:text-[11px] text-emerald-800/80 font-medium mt-0.5 flex items-center gap-1 truncate">
                    <i class="fa-regular fa-circle-check text-[9px] text-emerald-600"></i>
                    <span class="truncate">Vào TK Ngân hàng</span>
                </div>
            </div>
            <div class="w-full h-[28px] sm:h-[32px] text-[10px] sm:text-[11px] text-emerald-700 font-bold bg-emerald-100/70 rounded-lg sm:rounded-xl flex items-center justify-center gap-1 truncate">
                <i class="fa-solid fa-bolt text-[9px]"></i> Tự động đối soát
            </div>
        </div>

        <!-- 4. Ký Ghi nợ đại lý -->
        <div class="col-span-1 bg-purple-50/70 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-purple-200/90 shadow-2xs flex flex-col justify-between gap-1.5">
            <div>
                <div class="flex items-center justify-between text-purple-900 font-bold text-[11px] sm:text-xs">
                    <span class="truncate">Ghi nợ đại lý</span>
                    <i class="fa-solid fa-file-invoice text-purple-600 text-xs sm:text-sm shrink-0"></i>
                </div>
                <div class="text-sm sm:text-xl font-black text-purple-950 font-mono tracking-tight mt-0.5 truncate"
                     x-text="formatNumber(debtAmount) + ' đ'">
                </div>
                <div class="text-[10px] sm:text-[11px] text-purple-800/80 font-medium mt-0.5 flex items-center justify-between">
                    <span class="truncate">Đơn nợ</span>
                    <span class="font-bold text-purple-700 font-mono" x-text="candidateDebtOrders.length + ' đơn'"></span>
                </div>
            </div>
            <button type="button" @click="openDebtHandoverModal()"
                    class="w-full h-[28px] sm:h-[32px] px-1.5 bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-800 hover:to-indigo-800 text-white font-extrabold rounded-lg sm:rounded-xl text-[10.5px] sm:text-[11px] transition-all flex items-center justify-center gap-1 cursor-pointer shadow-xs active:scale-95 truncate">
                <i class="fa-solid fa-share-from-square text-[10px]"></i>
                <span class="truncate">Giao nợ Sale →</span>
            </button>
        </div>

        <!-- 5. Hàng hoàn trả trên xe -->
        <div class="col-span-1 bg-rose-50/70 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-rose-200/90 shadow-2xs flex flex-col justify-between gap-1.5">
            <div>
                <div class="flex items-center justify-between text-rose-900 font-bold text-[11px] sm:text-xs">
                    <span class="truncate">Hàng hoàn trả</span>
                    <i class="fa-solid fa-rotate-left text-rose-600 text-xs sm:text-sm shrink-0 cursor-pointer" @click="showReturnsModal = true"></i>
                </div>
                <div class="flex items-baseline justify-between mt-0.5 gap-1">
                    <span class="text-sm sm:text-xl font-black text-rose-950 font-mono tracking-tight truncate"
                          x-text="pendingReturnsList.length + ' phiếu'"></span>
                    <span class="text-[10px] sm:text-xs font-mono font-bold text-rose-700 truncate"
                          x-text="formatNumber(pendingReturnsTotalAmount) + ' đ'"></span>
                </div>
                <div class="text-[10px] sm:text-[11px] text-rose-700/90 font-medium mt-0.5 flex items-center justify-between cursor-pointer hover:underline" @click="showReturnsModal = true">
                    <span class="truncate">Chi tiết</span>
                    <span class="text-[9px] font-bold">Xem →</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-1">
                <button type="button" @click="showIntakeWarehouseModal = true"
                        :disabled="pendingReturnsList.length === 0"
                        class="h-[28px] sm:h-[32px] px-1 bg-slate-700 hover:bg-slate-800 text-white font-bold rounded-lg sm:rounded-xl text-[10px] sm:text-[11px] transition-colors flex items-center justify-center gap-0.5 cursor-pointer shadow-xs disabled:opacity-40 truncate"
                        title="Bàn giao hàng hoàn trả cho thủ kho">
                    <i class="fa-solid fa-warehouse text-[9px]"></i>
                    <span class="truncate">Hoàn kho</span>
                </button>
                <button type="button" @click="openAcceptHoldingModal()"
                        :disabled="pendingReturnsList.length === 0"
                        class="h-[28px] sm:h-[32px] px-1 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black rounded-lg sm:rounded-xl text-[10px] sm:text-[11px] transition-all flex items-center justify-center gap-0.5 cursor-pointer shadow-xs disabled:opacity-40 truncate"
                        title="Lái xe tự nhận ôm hàng vào kho nhân sự">
                    <i class="fa-solid fa-box-archive text-[9px]"></i>
                    <span class="truncate">Tự ôm</span>
                </button>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- KHỐI: HÀNG KHO CỦA NHÂN SỰ QUẢN LÝ XE                                    -->
    <!-- ========================================================================= -->
    <div x-show="holdingStocks.length > 0 || totalHoldingQty > 0 || holdingWarehouse"
         class="rounded-3xl p-4 sm:p-5 bg-gradient-to-r from-emerald-950/80 via-slate-900 to-teal-950/80 border border-emerald-500/40 text-white shadow-xl space-y-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-emerald-500/30 shrink-0">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-black text-sm sm:text-base text-white tracking-tight">
                            Hàng Kho Của Nhân Sự Quản Lý Xe
                        </h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30"
                              x-text="(holdingStocks.length || 0) + ' mặt hàng tồn'"></span>
                        <template x-if="holdingWarehouse">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30"
                                  x-text="holdingWarehouse.name"></span>
                        </template>
                    </div>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Nhân sự quản lý: <strong class="text-emerald-300" x-text="vehicleManagerName || 'Lái xe'"></strong> &bull;
                        Biển số xe: <strong class="text-purple-300" x-text="selectedVehicle?.plate_number || '{{ $selectedPlate ?? '' }}'"></strong>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0 self-end sm:self-center">
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-emerald-400 block tracking-wider">Tổng Tồn Kho / Giá Trị</span>
                    <span class="text-lg sm:text-xl font-black text-emerald-300 font-mono">
                        <span x-text="formatNumber(totalHoldingQty)"></span> <span class="text-xs font-normal text-slate-300">SP</span> &bull; 
                        <span x-text="formatNumber(totalHoldingVal) + ' đ'"></span>
                    </span>
                    <template x-if="empDebt > 0">
                        <span class="block text-[11px] font-bold text-amber-400">
                            Nợ hàng tồn: <span x-text="formatNumber(empDebt) + ' đ'"></span>
                        </span>
                    </template>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showHoldingStockModal = true"
                            class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-list-check text-emerald-400"></i>
                        <span>Chi tiết kho</span>
                    </button>
                    <a :href="'/inventory/holding-sale/create?warehouse_id=' + (holdingWarehouse ? holdingWarehouse.id : '') + '&return_url=' + encodeURIComponent(window.location.href)"
                       class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all shadow-md shadow-emerald-600/30 flex items-center gap-1.5 cursor-pointer active:scale-95 no-underline">
                        <i class="fa-solid fa-cart-plus"></i>
                        <span>+ Bán Hàng</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bảng xem nhanh các mặt hàng tồn trong kho -->
        <div class="bg-slate-950/60 rounded-2xl p-3 border border-slate-800/80 overflow-x-auto text-xs">
            <div class="flex items-center justify-between text-slate-400 text-[11px] font-bold uppercase mb-2 px-1">
                <span>Danh mục hàng tồn kho nhân sự:</span>
                <span class="text-emerald-400 font-mono" x-text="holdingStocks.length + ' loại mặt hàng'"></span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                <template x-for="(it, iIdx) in holdingStocks" :key="it.id || iIdx">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-emerald-500/40 transition-colors">
                        <div class="truncate mr-2">
                            <p class="font-bold text-white text-xs truncate" x-text="it.product_name"></p>
                            <p class="text-[10px] text-slate-400 font-mono" x-text="'Mã: ' + (it.sku || '--') + ' | ĐVT: ' + (it.unit || 'cái') + ' | Giá: ' + formatNumber(it.price) + ' đ'"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2 py-0.5 rounded-md font-mono font-black text-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/30"
                                  x-text="'SL: ' + it.quantity"></span>
                            <p class="text-[11px] font-mono font-bold text-slate-300 mt-1" x-text="formatNumber(it.amount) + ' đ'"></p>
                            <a :href="'/inventory/holding-sale/create?warehouse_id=' + (holdingWarehouse ? holdingWarehouse.id : '') + '&product_id=' + it.product_id + '&return_url=' + encodeURIComponent(window.location.href)"
                               class="inline-block mt-1 text-[10px] font-bold text-emerald-400 hover:text-emerald-300 hover:underline">
                                + Bán SP này &rarr;
                            </a>
                        </div>
                    </div>
                </template>
                <div x-show="holdingStocks.length === 0" class="col-span-full py-4 text-center text-slate-400 italic">
                    Kho của nhân sự phụ trách xe hiện chưa có hàng tồn.
                </div>
            </div>
        </div>
    </div>

