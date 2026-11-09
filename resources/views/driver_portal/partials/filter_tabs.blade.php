    <!-- ========================================================================= -->
    <!-- 4. SEARCH & STATUS FILTER TABS (Ảnh 1)                                     -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl p-3 border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
        
        <!-- Search input -->
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-3 pointer-events-none"></i>
            <input type="text" x-model="searchQuery"
                   placeholder="Tìm theo mã đơn, tên đại lý, số điện thoại, địa chỉ..."
                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-800 focus:outline-none focus:border-sky-500 focus:bg-white text-xs transition-colors">
        </div>

        <!-- Filter tabs: Tất cả, Chờ giao, Đã giao xong, Không thành công -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5">
            <button type="button" @click="statusFilter = 'all'"
                    class="px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                <span>Tất cả</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'all' ? 'bg-white/20' : 'bg-slate-200'" x-text="orders.length"></span>
            </button>

            <button type="button" @click="statusFilter = 'pending'"
                    class="px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-2xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200'">
                <i class="fa-regular fa-clock text-[11px]"></i>
                <span>Chờ giao</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'pending' ? 'bg-white/20' : 'bg-amber-200'" x-text="pendingOrdersCount"></span>
            </button>

            <button type="button" @click="statusFilter = 'completed'"
                    class="px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200'">
                <i class="fa-solid fa-check text-[11px]"></i>
                <span>Đã giao xong</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'completed' ? 'bg-white/20' : 'bg-emerald-200'" x-text="completedOrdersCount"></span>
            </button>

            <button type="button" @click="statusFilter = 'failed'"
                    class="px-3.5 py-1.5 rounded-xl font-black text-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
                    :class="statusFilter === 'failed' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200'">
                <i class="fa-solid fa-xmark text-[11px]"></i>
                <span>Không thành công</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'failed' ? 'bg-white/20' : 'bg-rose-200'" x-text="failedOrdersCount"></span>
            </button>
        </div>

    </div>

    <!-- Banner Thông báo Đang chờ Kế toán duyệt COD -->
    <div x-show="hasPendingCodSubmission" x-cloak class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-950 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-900 flex items-center justify-center font-bold shrink-0">
                <i class="fa-solid fa-hourglass-half text-sm animate-pulse"></i>
            </div>
            <div>
                <div class="font-extrabold text-amber-900">
                    Đang Chờ Kế Toán Duyệt COD:
                    <span class="font-mono text-amber-950 font-black ml-1" x-text="formatNumber(pendingCodInfo.amount) + ' đ'"></span>
                </div>
                <div class="text-[11px] text-amber-800 mt-0.5">
                    Biên bản: <strong class="font-mono" x-text="pendingCodInfo.code"></strong> · Chỉ định: <strong x-text="pendingCodInfo.accountantName"></strong>.
                    <span class="text-amber-700 ml-1">Đơn hàng sẽ tự động giải phóng sau khi Kế toán kiểm đếm & duyệt tại mục Bán hàng > Kiểm COD.</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
            <button type="button" @click="checkPendingCodStatus()" :disabled="isCheckingCodStatus"
                    class="px-3 py-1.5 bg-amber-200 hover:bg-amber-300 text-amber-950 font-bold rounded-xl text-xs cursor-pointer flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': isCheckingCodStatus }"></i>
                <span>Kiểm tra trạng thái duyệt</span>
            </button>
        </div>
    </div>

    <!-- Banner Thông báo Đang chờ Thủ kho duyệt nhập kho hoàn trả -->
    <div x-show="hasPendingReturnsSubmission" x-cloak class="p-3.5 bg-rose-50 border border-rose-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-rose-950 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-200 text-rose-900 flex items-center justify-center font-bold shrink-0">
                <i class="fa-solid fa-boxes-packing text-sm animate-pulse"></i>
            </div>
            <div>
                <div class="font-extrabold text-rose-900">
                    Đang Chờ Thủ Kho Duyệt Nhập Kho Hoàn Trả:
                    <span class="font-mono text-rose-950 font-black ml-1" x-text="returnsList.length + ' phiếu (' + formatNumber(returnsTotalAmount) + ' đ)'"></span>
                </div>
                <div class="text-[11px] text-rose-800 mt-0.5">
                    Thủ kho chỉ định: <strong x-text="pendingReturnsInfo.staffName || intakeForm.intakeUserName || 'Thủ kho'"></strong>.
                    <span class="text-rose-700 ml-1">Hàng hoàn trả sẽ được gỡ bỏ khỏi xe và cộng tồn kho sau khi Thủ kho duyệt tại mục Kho hàng > Nhập kho.</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
            <button type="button" @click="checkPendingReturnsStatus()" :disabled="isCheckingReturnsStatus"
                    class="px-3 py-1.5 bg-rose-200 hover:bg-rose-300 text-rose-950 font-bold rounded-xl text-xs cursor-pointer flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': isCheckingReturnsStatus }"></i>
                <span>Kiểm tra duyệt hoàn kho</span>
            </button>
        </div>
    </div>

