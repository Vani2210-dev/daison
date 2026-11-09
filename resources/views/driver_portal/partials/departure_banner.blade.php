    <!-- ========================================================================= -->
    <!-- 2. BANNER TRẠNG THÁI XUẤT BẾN (Chỉ hiện nút Xuất Bến khi 100% đơn Đã Kiểm Hàng) -->
    <!-- ========================================================================= -->
    <!-- Trường hợp 1: Xe đã đủ điều kiện xuất bến (100% Đơn Đã Kiểm Hàng) -->
    <div x-show="!isDeparted && isReadyToDepart"
         class="rounded-xl sm:rounded-2xl p-3 sm:p-4 bg-gradient-to-r from-emerald-950/70 via-emerald-900/50 to-slate-900 border-2 border-emerald-500/70 text-emerald-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-lg shadow-emerald-950/40">
        <div class="flex items-center gap-2.5 sm:gap-3.5">
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/30 text-base sm:text-xl font-black">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="font-black text-xs sm:text-base text-white tracking-tight">
                        Đủ điều kiện xuất bến (100% Đã Kiểm)
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-wider bg-emerald-400/20 text-emerald-300 border border-emerald-400/40 animate-pulse">
                        SẴN SÀNG
                    </span>
                </div>
                <p class="hidden sm:block text-xs text-emerald-200/80 mt-0.5 font-medium">
                    Toàn bộ <span class="font-bold text-white font-mono" x-text="orders.length"></span> đơn hàng đã được kiểm hàng 100%. Lái xe bấm "Bấm Xuất Bến" để bắt đầu lộ trình.
                </p>
            </div>
        </div>

        <button type="button" @click="departTrip()" :disabled="isDeparting"
                class="w-full sm:w-auto px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl font-black text-xs sm:text-sm text-emerald-950 bg-gradient-to-r from-emerald-300 to-emerald-400 hover:from-emerald-200 hover:to-emerald-300 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/25 shrink-0 cursor-pointer active:scale-95 disabled:opacity-50">
            <i class="fa-solid fa-rocket text-xs sm:text-sm"></i>
            <span x-text="isDeparting ? 'Đang xuất bến...' : 'Bấm Xuất Bến (Bắt đầu)'"></span>
        </button>
    </div>

    <!-- Trường hợp 2: Xe chưa xuất bến và CHƯA đủ điều kiện (có đơn chưa kiểm hàng hoặc chưa có đơn xếp) -->
    <div x-show="!isDeparted && !isReadyToDepart"
         class="rounded-xl sm:rounded-2xl p-2.5 sm:p-4 bg-gradient-to-r from-amber-950/60 via-slate-900 to-slate-950 border border-amber-500/40 text-amber-100 flex items-center justify-between gap-2.5 sm:gap-3.5 shadow-md">
        <div class="flex items-center gap-2.5 sm:gap-3.5">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0 text-sm sm:text-lg font-black">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-bold text-xs sm:text-sm text-white tracking-tight">
                        <template x-if="orders.length === 0">
                            <span>Xe chưa có đơn hàng được xếp</span>
                        </template>
                        <template x-if="orders.length > 0">
                            <span>Chờ kiểm hàng xuất bến</span>
                        </template>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/30">
                        <template x-if="orders.length === 0">
                            <span>CHỜ XẾP ĐƠN</span>
                        </template>
                        <template x-if="orders.length > 0">
                            <span x-text="'ĐÃ KIỂM ' + checkedOrdersCount + '/' + orders.length"></span>
                        </template>
                    </span>
                </div>
                <p class="hidden sm:block text-xs text-slate-300 mt-0.5">
                    <template x-if="orders.length === 0">
                        <span>Xe hiện chưa có đơn hàng nào. Vào điều phối xe để xếp đơn trước khi xuất bến.</span>
                    </template>
                    <template x-if="orders.length > 0">
                        <span>Còn <strong class="text-amber-400 font-mono" x-text="uncheckedOrders.length"></strong> đơn chưa kiểm hàng. Cần kiểm 100% để xuất bến.</span>
                    </template>
                </p>
            </div>
        </div>
    </div>

    <!-- Banner khi xe đang trên đường quay về bãi / kho -->
    <div x-show="isReturning"
         class="rounded-xl sm:rounded-2xl p-3 sm:p-4 bg-gradient-to-r from-amber-950/80 via-slate-900 to-indigo-950/70 border-2 border-amber-500/70 text-amber-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-lg shadow-amber-950/40">
        <div class="flex items-center gap-2.5 sm:gap-3.5">
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center shrink-0 text-base sm:text-xl font-black">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-black text-xs sm:text-base text-white tracking-tight">
                        Xe đang trên đường về bãi / kho
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/40 animate-pulse">
                        TRÊN ĐƯỜNG VỀ
                    </span>
                </div>
                <p class="hidden sm:block text-xs text-amber-200/80 mt-0.5 font-medium">
                    Toàn bộ đơn hàng đã giao xong. Khi xe về tới bãi an toàn, bấm "Đã Về Tới Bãi" để kết thúc chuyến.
                </p>
            </div>
        </div>

        <button type="button" @click="finishTripArrival()" :disabled="isReturningVehicle"
                class="w-full sm:w-auto px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl font-black text-xs sm:text-sm text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 shrink-0 cursor-pointer active:scale-95 disabled:opacity-50">
            <i class="fa-solid fa-flag-checkered text-xs sm:text-sm"></i>
            <span x-text="isReturningVehicle ? 'Đang cập nhật...' : '🏁 Đã Về Tới Bãi (Kết thúc)'"></span>
        </button>
    </div>

    <!-- Banner khi xe đã xuất bến và ĐÃ GIAO XONG TOÀN BỘ ĐƠN HÀNG (Hiển thị nút Quay Về) -->
    <div x-show="isDeparted && !isReturning && isAllCompleted"
         class="rounded-xl sm:rounded-2xl p-3 sm:p-4 bg-gradient-to-r from-emerald-950/80 via-teal-950/60 to-slate-900 border-2 border-emerald-400/70 text-emerald-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-lg shadow-emerald-950/40">
        <div class="flex items-center gap-2.5 sm:gap-3.5">
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-slate-950 flex items-center justify-center shrink-0 text-base sm:text-xl font-black">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-black text-xs sm:text-base text-white tracking-tight">
                        Đã giao xong toàn bộ đơn hàng!
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-wider bg-emerald-400/20 text-emerald-300 border border-emerald-400/40"
                          x-text="completedOrdersCount + '/' + orders.length + ' ĐƠN XONG'">
                    </span>
                </div>
                <p class="hidden sm:block text-xs text-emerald-200/90 mt-0.5 font-medium">
                    Tất cả các điểm giao trên xe đã được phục vụ xong. Lái xe bấm nút "Quay Về" để di chuyển về kho bãi.
                </p>
            </div>
        </div>

        <button type="button" @click="returnToDepot()" :disabled="isReturningVehicle"
                class="w-full sm:w-auto px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl font-black text-xs sm:text-sm text-slate-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-400 hover:from-amber-200 hover:to-yellow-300 transition-all flex items-center justify-center gap-2 shadow-lg shadow-amber-500/35 shrink-0 cursor-pointer active:scale-95 disabled:opacity-50">
            <i class="fa-solid fa-house-chimney text-xs sm:text-sm"></i>
            <span x-text="isReturningVehicle ? 'Đang cập nhật...' : 'Bấm Quay Về (Về Bãi / Kho)'"></span>
        </button>
    </div>

    <!-- Banner khi xe đã xuất bến đang chạy trên đường giao hàng (còn đơn chưa xong) -->
    <div x-show="isDeparted && !isReturning && !isAllCompleted"
         class="rounded-xl sm:rounded-2xl p-2.5 sm:p-3.5 bg-gradient-to-r from-sky-950/70 via-indigo-950/50 to-slate-900 border border-sky-500/50 text-sky-100 flex items-center justify-between gap-2.5 shadow-md">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center shrink-0 text-sm sm:text-lg shadow-md shadow-sky-500/30">
                <i class="fa-solid fa-truck-moving"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                    <span class="font-bold text-xs sm:text-sm text-white truncate">Đang trên lộ trình giao hàng</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-sky-500/30 text-sky-200 border border-sky-400/30 font-mono font-bold whitespace-nowrap"
                          x-text="'Rời bến ' + (departureTimeStr || '08:00')"></span>
                </div>
                <div class="text-[11px] sm:text-xs text-sky-200/80 mt-0.5 font-medium flex items-center gap-1.5">
                    <span x-text="'Tiến độ: ' + completedOrdersCount + '/' + orders.length + ' đơn'"></span>
                    <span>•</span>
                    <span class="text-amber-300 font-bold" x-text="'Còn ' + pendingOrdersCount + ' đơn'"></span>
                </div>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2 shrink-0">
            <div class="px-3 py-1.5 rounded-xl text-xs font-bold text-sky-200 bg-sky-950/80 border border-sky-600/40 flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-route text-sky-400"></i>
                <span x-text="'Tiến độ: ' + completedOrdersCount + '/' + orders.length + ' đơn (Còn ' + pendingOrdersCount + ' đơn)'"></span>
            </div>
        </div>
    </div>
