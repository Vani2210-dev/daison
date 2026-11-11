    <!-- ========================================================================= -->
    <!-- 4. SEARCH & STATUS FILTER TABS                                              -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl p-2.5 sm:p-3 border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-2.5 text-xs">
        
        <!-- Search input -->
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 pointer-events-none text-xs"></i>
            <input type="text" x-model="searchQuery"
                   placeholder="Tìm theo mã đơn, tên đại lý, số điện thoại, địa chỉ..."
                   class="w-full h-[36px] pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-800 focus:outline-none focus:border-sky-500 focus:bg-white text-xs transition-colors">
        </div>

        <!-- Filter tabs: Tất cả, Chờ giao, Đã giao xong, Không thành công (Ẩn scrollbar xấu xí) -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 no-scrollbar [&::-webkit-scrollbar]:hidden" style="-ms-overflow-style: none; scrollbar-width: none;">
            <button type="button" @click="statusFilter = 'all'"
                    class="h-[34px] px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                <span>Tất cả</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'all' ? 'bg-white/20' : 'bg-slate-200'" x-text="orders.length"></span>
            </button>

            <button type="button" @click="statusFilter = 'pending'"
                    class="h-[34px] px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-2xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200'">
                <i class="fa-regular fa-clock text-[11px]"></i>
                <span>Chờ giao</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'pending' ? 'bg-white/20' : 'bg-amber-200'" x-text="pendingOrdersCount"></span>
            </button>

            <button type="button" @click="statusFilter = 'completed'"
                    class="h-[34px] px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200'">
                <i class="fa-solid fa-check text-[11px]"></i>
                <span>Đã giao xong</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'completed' ? 'bg-white/20' : 'bg-emerald-200'" x-text="completedOrdersCount"></span>
            </button>

            <button type="button" @click="statusFilter = 'failed'"
                    class="h-[34px] px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'failed' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200'">
                <i class="fa-solid fa-xmark text-[11px]"></i>
                <span>Không thành công</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'failed' ? 'bg-white/20' : 'bg-rose-200'" x-text="failedOrdersCount"></span>
            </button>
        </div>

    </div>

    <!-- Banner Thông báo Đang chờ Kế toán duyệt COD (Thiết kế ngang gọn 1 hàng) -->
    <div x-show="hasPendingCodSubmission" x-cloak class="p-2.5 sm:p-3 bg-amber-50 border border-amber-300/90 rounded-xl sm:rounded-2xl flex items-center justify-between gap-2.5 text-xs text-amber-950 shadow-xs">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-900 flex items-center justify-center font-bold shrink-0">
                <i class="fa-solid fa-hourglass-half text-xs animate-pulse"></i>
            </div>
            <div class="min-w-0">
                <div class="font-extrabold text-amber-900 text-xs truncate">
                    Chờ Kế Toán Duyệt COD:
                    <span class="font-mono text-amber-950 font-black ml-1" x-text="formatNumber(pendingCodInfo.amount) + ' đ'"></span>
                </div>
                <div class="text-[10px] sm:text-[11px] text-amber-800 truncate mt-0.5">
                    BB: <strong class="font-mono" x-text="pendingCodInfo.code"></strong> • Chỉ định: <strong x-text="pendingCodInfo.accountantName"></strong>
                </div>
            </div>
        </div>
        <button type="button" @click="checkPendingCodStatus()" :disabled="isCheckingCodStatus"
                class="px-2.5 py-1.5 bg-amber-200 hover:bg-amber-300 active:scale-95 text-amber-950 font-bold rounded-lg text-xs cursor-pointer flex items-center gap-1.5 transition-all shrink-0 whitespace-nowrap shadow-xs">
            <i class="fa-solid fa-arrows-rotate text-xs" :class="{ 'fa-spin': isCheckingCodStatus }"></i>
            <span class="hidden sm:inline">Kiểm tra duyệt</span>
            <span class="sm:hidden">Kiểm tra</span>
        </button>
    </div>

    <!-- Banner Thông báo Đang chờ Thủ kho duyệt nhập kho hoàn trả (Thiết kế ngang gọn 1 hàng) -->
    <div x-show="hasPendingReturnsSubmission" x-cloak class="p-2.5 sm:p-3 bg-rose-50 border border-rose-300/90 rounded-xl sm:rounded-2xl flex items-center justify-between gap-2.5 text-xs text-rose-950 shadow-xs">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-rose-200 text-rose-900 flex items-center justify-center font-bold shrink-0">
                <i class="fa-solid fa-boxes-packing text-xs animate-pulse"></i>
            </div>
            <div class="min-w-0">
                <div class="font-extrabold text-rose-900 text-xs truncate">
                    Chờ Thủ Kho Duyệt Hoàn:
                    <span class="font-mono text-rose-950 font-black ml-1" x-text="returnsList.length + ' phiếu (' + formatNumber(returnsTotalAmount) + ' đ)'"></span>
                </div>
                <div class="text-[10px] sm:text-[11px] text-rose-800 truncate mt-0.5">
                    Thủ kho: <strong x-text="pendingReturnsInfo.staffName || intakeForm.intakeUserName || 'Thủ kho'"></strong>
                </div>
            </div>
        </div>
        <button type="button" @click="checkPendingReturnsStatus()" :disabled="isCheckingReturnsStatus"
                class="px-2.5 py-1.5 bg-rose-200 hover:bg-rose-300 active:scale-95 text-rose-950 font-bold rounded-lg text-xs cursor-pointer flex items-center gap-1.5 transition-all shrink-0 whitespace-nowrap shadow-xs">
            <i class="fa-solid fa-arrows-rotate text-xs" :class="{ 'fa-spin': isCheckingReturnsStatus }"></i>
            <span class="hidden sm:inline">Kiểm tra duyệt</span>
            <span class="sm:hidden">Kiểm tra</span>
        </button>
    </div>

