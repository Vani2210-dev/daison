    <!-- Modal 3: Danh Sách Hàng Trả Về -->
    <div x-show="showReturnsModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4" @click.away="showReturnsModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Danh Sách Hàng Khách Trả Về Trên Xe</h3>
                        <p class="text-xs text-slate-500 font-medium">Theo dõi các mặt hàng được khách hoàn trả mang về kho</p>
                    </div>
                </div>
                <button type="button" @click="showReturnsModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="max-h-[350px] overflow-y-auto space-y-2.5 text-xs">
                <template x-for="r in returnsList" :key="r.id">
                    <div class="p-3 rounded-2xl border border-rose-200 bg-rose-50/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-black text-purple-700" x-text="r.slipCode || r.slip_code || ('TH-' + (r.orderCode || r.order_code))"></span>
                                <span class="font-bold text-slate-900" x-text="r.dealerName || r.dealer_name || 'Khách hàng'"></span>
                                <template x-if="r.status === 'Tài xế đã nhận ôm hàng'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ✓ Đã ôm vào Kho nhân sự
                                    </span>
                                </template>
                                <template x-if="r.status === 'Chờ thủ kho duyệt nhập'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        ⏳ Chờ thủ kho duyệt nhập
                                    </span>
                                </template>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-rose-700" x-text="formatNumber(r.totalReturnedAmount ?? r.total_returned_amount ?? 0) + ' đ'"></span>
                                <template x-if="r.status !== 'Tài xế đã nhận ôm hàng' && r.status !== 'Đã nhập kho lại'">
                                    <button type="button" @click="openAcceptHoldingModal(r)"
                                            class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] rounded-lg shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-box-archive text-[9px]"></i>
                                        <span>Tự ôm phiếu này</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-rose-200/80 bg-white">
                            <table class="w-full text-left text-[11px]">
                                <thead class="bg-rose-100/60 text-rose-900 font-bold">
                                    <tr>
                                        <th class="p-2">Sản phẩm</th>
                                        <th class="p-2 text-center">SL</th>
                                        <th class="p-2 text-right">Giá</th>
                                        <th class="p-2 text-right">Lý do</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-rose-100">
                                    <template x-for="it in (r.items || [])" :key="it.id">
                                        <tr>
                                            <td class="p-2 font-medium" x-text="it.name"></td>
                                            <td class="p-2 text-center font-bold font-mono text-rose-700" x-text="(it.returnedQty ?? it.returned_qty) + ' ' + (it.unit || '')"></td>
                                            <td class="p-2 text-right font-mono" x-text="formatNumber(it.unitPrice ?? it.unit_price ?? 0) + ' đ'"></td>
                                            <td class="p-2 text-right text-slate-500 italic" x-text="it.reason || 'Khách trả hàng'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <div x-show="returnsList.length === 0" class="py-8 text-center text-slate-400 font-medium">
                    Không có hàng trả lại nào trên chuyến xe này.
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
                <div>
                    <template x-if="pendingReturnsList.length > 0">
                        <button type="button" @click="openAcceptHoldingModal()"
                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl cursor-pointer flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                            <i class="fa-solid fa-box-archive text-xs"></i>
                            <span>Tự Ôm Toàn Bộ Hàng Trả Về</span>
                        </button>
                    </template>
                </div>
                <button type="button" @click="showReturnsModal = false" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-xl cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

