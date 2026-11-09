    <!-- Modal 5: Xem Chi Tiết Đơn Hàng (Read-only quick view) -->
    <div x-show="showOrderViewModal" x-cloak class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-3xl w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto" @click.away="showOrderViewModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base" x-text="'Chi Tiết Đơn #' + (inspectOrder?.order_code || '')"></h3>
                        <p class="text-xs text-slate-500 font-medium" x-text="inspectOrder?.customer_name"></p>
                    </div>
                </div>
                <button type="button" @click="showOrderViewModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Trạng thái:</span>
                        <strong class="font-bold text-slate-900" x-text="inspectOrder?.status"></strong>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Thanh toán:</span>
                        <strong class="font-bold text-slate-900" x-text="inspectOrder?.payment_status"></strong>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Tổng tiền:</span>
                        <strong class="font-mono font-black text-purple-700" x-text="formatNumber(inspectOrder?.total_amount) + ' đ'"></strong>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-500 block text-[10px]">Tiền lái xe thu:</span>
                        <strong class="font-mono font-black text-amber-700" x-text="formatNumber(inspectOrder?.driver_debt_amount || 0) + ' đ'"></strong>
                    </div>
                </div>

                <template x-if="inspectOrder?.status === 'Chờ thủ kho duyệt hoàn'">
                    <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center font-bold text-sm shrink-0">
                                <i class="fa-solid fa-hourglass-half"></i>
                            </div>
                            <div>
                                <div class="font-bold text-amber-900 text-xs">Đơn hàng đang chờ thủ kho kiểm đếm và duyệt nhập kho</div>
                                <div class="text-[11px] text-amber-800/80">Tồn kho sẽ được cộng lại ngay sau khi thủ kho xác nhận.</div>
                            </div>
                        </div>
                        <button type="button" @click="approveOrderReturnDirectly(inspectOrder)"
                                class="px-3 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 text-xs active:scale-95">
                            <i class="fa-solid fa-check"></i>
                            <span>Duyệt Nhập Kho Ngay</span>
                        </button>
                    </div>
                </template>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold text-[11px]">
                            <tr>
                                <th class="p-2.5">Sản phẩm</th>
                                <th class="p-2.5 text-center">ĐVT</th>
                                <th x-show="hasReturns(inspectOrder)" class="p-2.5 text-right text-slate-600">SL Xuất</th>
                                <th x-show="hasReturns(inspectOrder)" class="p-2.5 text-right text-rose-600 font-bold">SL Trả</th>
                                <th class="p-2.5 text-right" :class="hasReturns(inspectOrder) ? 'text-emerald-700 font-bold' : ''">SL Giao</th>
                                <th x-show="!hasReturns(inspectOrder)" class="p-2.5 text-right">Đơn giá</th>
                                <th class="p-2.5 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="it in (inspectOrder?.items || [])" :key="it.id">
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900">
                                        <div x-text="it.name"></div>
                                        <div class="text-[10px] text-slate-400 font-mono" x-text="it.sku"></div>
                                    </td>
                                    <td class="p-2.5 text-center text-slate-600" x-text="it.unit"></td>
                                    <td x-show="hasReturns(inspectOrder)" class="p-2.5 text-right font-mono text-slate-500" x-text="formatNumber(it.quantity)"></td>
                                    <td x-show="hasReturns(inspectOrder)" class="p-2.5 text-right font-bold font-mono text-rose-600" x-text="formatNumber(it.returned_qty || 0)"></td>
                                    <td class="p-2.5 text-right font-mono font-black"
                                        :class="hasReturns(inspectOrder) ? 'text-emerald-700' : 'text-slate-800'"
                                        x-text="formatNumber(getItemDeliveredQty(inspectOrder, it))"></td>
                                    <td x-show="!hasReturns(inspectOrder)" class="p-2.5 text-right font-mono" x-text="formatNumber(it.unit_price) + ' đ'"></td>
                                    <td class="p-2.5 text-right font-mono font-black text-slate-900"
                                        x-text="formatNumber(getItemAmount(inspectOrder, it)) + ' đ'"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot x-show="hasReturns(inspectOrder)" class="bg-slate-50 font-bold border-t border-slate-200 text-xs">
                            <tr>
                                <td colspan="2" class="p-2.5 text-slate-700">Tổng cộng thực giao:</td>
                                <td class="p-2.5 text-right font-mono text-slate-500" x-text="formatNumber(inspectOrder?.items?.reduce((s, i) => s + Number(i.quantity || 0), 0) || 0)"></td>
                                <td class="p-2.5 text-right font-mono text-rose-600 font-bold" x-text="formatNumber(inspectOrder?.items?.reduce((s, i) => s + Number(i.returned_qty || 0), 0) || 0)"></td>
                                <td class="p-2.5 text-right font-mono text-emerald-700 font-black" x-text="formatNumber(inspectOrder?.items?.reduce((s, i) => s + getItemDeliveredQty(inspectOrder, i), 0) || 0)"></td>
                                <td class="p-2.5 text-right font-mono font-black text-slate-900" x-text="formatNumber(inspectOrder?.items?.reduce((s, i) => s + getItemAmount(inspectOrder, i), 0) || 0) + ' đ'"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-600">
                    <strong>Ghi chú đơn:</strong> <span x-text="inspectOrder?.notes || 'Không có ghi chú'"></span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
                <a :href="'/orders/' + inspectOrder?.id + '/edit'" class="text-sky-600 hover:underline font-bold flex items-center gap-1">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    <span>Mở màn hình chỉnh sửa chi tiết</span>
                </a>
                <button type="button" @click="showOrderViewModal = false" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-xl cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

