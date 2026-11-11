    <!-- ========================================================================= -->
    <!-- MODAL: LÁI XE TỰ NHẬN ÔM HÀNG HOÀN TRẢ VÀO KHO NHÂN SỰ                    -->
    <!-- ========================================================================= -->
    <div x-show="showAcceptHoldingModal" x-cloak 
         class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="if(!isAcceptingHolding) showAcceptHoldingModal = false">
        
        <div class="bg-white rounded-3xl p-5 sm:p-6 max-w-2xl w-full shadow-2xl border border-slate-200 space-y-4 text-xs animate-in fade-in zoom-in-95 duration-150"
             @click.away="if(!isAcceptingHolding) showAcceptHoldingModal = false">
            
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base shadow-xs shrink-0">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-black text-slate-900 text-base">Tài Xế Nhận Ôm Hàng Hoàn Trả</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Bán lưu động</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Hàng hoàn trả từ khách sẽ được chuyển thẳng vào Kho nhân sự / Kho xe của bạn để bán lưu động mà không cần nhập lại kho tổng.</p>
                    </div>
                </div>
                <button type="button" @click="showAcceptHoldingModal = false" :disabled="isAcceptingHolding"
                        class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 text-xl font-bold flex items-center justify-center cursor-pointer transition-colors">&times;</button>
            </div>

            <!-- Body -->
            <div class="space-y-3.5">
                
                <!-- Destination Info Card -->
                <div class="p-3.5 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200/80 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-950 flex items-center gap-1.5">
                            <i class="fa-solid fa-warehouse text-emerald-600"></i>
                            <span>Kho nhận hàng ôm:</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-600 text-white font-mono font-bold text-xs shadow-xs"
                              x-text="holdingWarehouse?.name || ('Kho Hàng Ôm - ' + (vehicleManagerName || currentUserName || '{{ auth()->user()?->name ?? 'Lái xe' }}'))"></span>
                    </div>
                    <div class="text-[11px] text-emerald-800">
                        Phụ trách kho: <strong x-text="vehicleManagerName || currentUserName || '{{ auth()->user()?->name ?? 'Lái xe' }}'"></strong> 
                        &bull; Xe: <strong class="font-mono" x-text="selectedVehicle?.plate_number || '{{ $selectedPlate ?? '' }}'"></strong>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200">
                        <span class="text-slate-500 font-bold block text-[11px]">Tổng phiếu hoàn trả nhận ôm:</span>
                        <span class="text-lg font-black font-mono text-slate-900 mt-0.5 block"
                              x-text="(holdingReturnSlipsToAccept.length) + ' phiếu'"></span>
                    </div>
                    <div class="bg-rose-50/80 p-3 rounded-2xl border border-rose-200">
                        <span class="text-rose-800 font-bold block text-[11px]">Tổng giá trị hàng ôm chịu nợ:</span>
                        <span class="text-lg font-black font-mono text-rose-700 mt-0.5 block"
                              x-text="formatNumber(holdingReturnSlipsTotalAmount) + ' đ'"></span>
                    </div>
                </div>

                <!-- Slips & Items List to Accept -->
                <div class="space-y-2 max-h-[260px] overflow-y-auto">
                    <template x-for="r in holdingReturnSlipsToAccept" :key="r.id">
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                            <div class="flex items-center justify-between font-bold">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-purple-700 text-xs" x-text="r.slipCode || r.slip_code"></span>
                                    <span class="text-slate-800" x-text="r.dealerName || r.dealer_name"></span>
                                </div>
                                <span class="font-mono text-rose-700" x-text="formatNumber(r.totalReturnedAmount ?? r.total_returned_amount ?? 0) + ' đ'"></span>
                            </div>

                            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                                <table class="w-full text-left text-[11px]">
                                    <thead class="bg-slate-100 text-slate-600 font-semibold">
                                        <tr>
                                            <th class="p-1.5 px-2.5">Sản phẩm</th>
                                            <th class="p-1.5 px-2.5 text-center">SL Nhận</th>
                                            <th class="p-1.5 px-2.5 text-right">Đơn giá</th>
                                            <th class="p-1.5 px-2.5 text-right">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="it in (r.items || [])" :key="it.id">
                                            <tr>
                                                <td class="p-1.5 px-2.5 font-medium text-slate-800" x-text="it.name"></td>
                                                <td class="p-1.5 px-2.5 text-center font-bold font-mono text-emerald-700" x-text="(it.returnedQty ?? it.returned_qty) + ' ' + (it.unit || '')"></td>
                                                <td class="p-1.5 px-2.5 text-right font-mono text-slate-600" x-text="formatNumber(it.unitPrice ?? it.unit_price ?? 0) + ' đ'"></td>
                                                <td class="p-1.5 px-2.5 text-right font-mono font-bold text-rose-700" x-text="formatNumber((Number(it.returnedQty ?? it.returned_qty) || 0) * (Number(it.unitPrice ?? it.unit_price) || 0)) + ' đ'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Driver Agreement -->
                <div class="p-3 bg-amber-50 border border-amber-300/80 rounded-xl flex items-start gap-2.5">
                    <input type="checkbox" id="agreeHoldingTerms" x-model="agreeHoldingTerms"
                           class="w-4 h-4 mt-0.5 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer shrink-0">
                    <label for="agreeHoldingTerms" class="text-[11px] text-amber-950 font-medium leading-relaxed cursor-pointer select-none">
                        Tôi xác nhận tự nhận ôm số lượng hàng hoàn trả trên vào <strong>Kho nhân sự / Kho xe</strong> của tôi để đi bán lưu động, đồng ý chịu trách nhiệm công nợ hàng hóa và sẽ thanh quyết toán khi kết thúc ca làm việc.
                    </label>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" @click="showAcceptHoldingModal = false" :disabled="isAcceptingHolding"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer transition-colors">
                    Hủy bỏ
                </button>

                <button type="button" @click="confirmAcceptHoldingReturn()" 
                        :disabled="!agreeHoldingTerms || isAcceptingHolding || holdingReturnSlipsToAccept.length === 0"
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black rounded-xl cursor-pointer shadow-md shadow-emerald-600/20 disabled:opacity-50 flex items-center gap-2 transition-all active:scale-95">
                    <template x-if="isAcceptingHolding">
                        <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                    </template>
                    <template x-if="!isAcceptingHolding">
                        <i class="fa-solid fa-box-archive text-sm"></i>
                    </template>
                    <span x-text="isAcceptingHolding ? 'Đang chuyển vào kho nhân sự...' : 'Xác Nhận Nhận Ôm Hàng Ngay'"></span>
                </button>
            </div>

        </div>
    </div>
