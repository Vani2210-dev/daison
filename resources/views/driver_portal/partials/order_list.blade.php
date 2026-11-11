    <!-- ========================================================================= -->
    <!-- 5. DANH SÁCH THẺ ĐƠN HÀNG (Exact cards from Screenshot 2)                 -->
    <!-- ========================================================================= -->
    <div class="space-y-3.5">
        <template x-for="(order, index) in filteredOrders" :key="order.id">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs hover:border-slate-300 transition-all overflow-hidden space-y-2.5 p-3 sm:p-4">
                
                <!-- Row 1: Header đơn hàng (Gọn gàng, tối ưu không gian dọc) -->
                <div class="pb-2.5 border-b border-slate-100 space-y-1.5">
                    
                    <!-- Dòng 1: Định danh đơn (Trái) & Trạng thái đơn (Phải) -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="w-5 h-5 rounded-md bg-purple-600 text-white font-black text-[11px] flex items-center justify-center shrink-0 shadow-2xs"
                                  x-text="index + 1"></span>
                            <span class="font-mono font-black text-xs sm:text-sm text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200"
                                  x-text="order.order_code"></span>
                            <span class="text-[11px] text-slate-500 font-bold truncate hidden sm:inline" x-text="order.dealer?.tier || 'Đại lý Cấp 1'"></span>
                        </div>

                        <!-- Trạng thái đơn -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10.5px] sm:text-[11px] font-bold border shrink-0 shadow-2xs"
                              :class="order.status === 'Không thành công' ? 'bg-rose-50 text-rose-700 border-rose-300' : (order.status === 'Chờ thủ kho duyệt hoàn' ? 'bg-amber-50 text-amber-900 border-amber-300' : (order.status === 'Đã hoàn kho' ? 'bg-blue-50 text-blue-700 border-blue-300' : (isOrderChecked(order) ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-900 border-amber-300')))">
                            <i :class="order.status === 'Không thành công' ? 'fa-solid fa-circle-xmark text-rose-600 text-[10px]' : (order.status === 'Chờ thủ kho duyệt hoàn' ? 'fa-solid fa-hourglass-half text-amber-600 text-[10px]' : (order.status === 'Đã hoàn kho' ? 'fa-solid fa-warehouse text-blue-600 text-[10px]' : (isOrderChecked(order) ? 'fa-solid fa-clipboard-check text-emerald-600 text-[10px]' : 'fa-regular fa-clock text-amber-600 text-[10px]')))"></i>
                            <span x-text="order.status"></span>
                        </span>
                    </div>

                    <!-- Dòng 2: Thông tin đại lý, thanh toán & Nút Xem đơn -->
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-1.5 text-slate-500 font-medium truncate">
                            <span class="font-bold text-slate-700 sm:hidden text-[11px] truncate" x-text="order.dealer?.tier || 'Đại lý Cấp 1'"></span>
                            <span class="sm:hidden text-slate-300">•</span>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10.5px] sm:text-[11px] font-semibold border shrink-0"
                                  :class="getPaymentBadgeClass(order.payment_status)">
                                <i class="fa-regular fa-clock text-[9px]"></i>
                                <span x-text="order.payment_status || 'Chưa thu tiền'"></span>
                            </span>
                            <template x-if="hasReturns(order)">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300 shrink-0">
                                    <i class="fa-solid fa-rotate-left text-[8px]"></i>
                                    <span>TRẢ HÀNG</span>
                                </span>
                            </template>
                        </div>

                        <!-- Nút Xem đơn nhỏ gọn ở góc phải -->
                        <button type="button" @click="openViewOrderModal(order)"
                                class="px-2 py-0.5 rounded-lg text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-[11px] font-bold flex items-center gap-1 cursor-pointer transition-colors shadow-2xs shrink-0">
                            <i class="fa-regular fa-eye text-slate-500 text-[10px]"></i>
                            <span>Xem đơn</span>
                        </button>
                    </div>

                    <!-- Dòng 3: Cụm nút thao tác nghiệp vụ (Chỉ hiện khi cần hành động) -->
                    <div>
                        <!-- 1. Khi đơn Không thành công: Nút Hoàn kho -->
                        <template x-if="order.status === 'Không thành công'">
                            <div class="pt-1">
                                <button type="button" @click="returnFailedOrderToWarehouse(order)"
                                        class="w-full py-1.5 px-3 rounded-xl font-black text-xs text-amber-950 bg-gradient-to-r from-amber-200 to-amber-300 hover:from-amber-300 hover:to-amber-400 border border-amber-400/80 transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-98">
                                    <i class="fa-solid fa-boxes-packing text-amber-800 text-xs"></i>
                                    <span>Hoàn hàng vào kho</span>
                                </button>
                            </div>
                        </template>

                        <!-- 2. Khi đơn Đã giao xong & Có công nợ: Nút Bàn giao nợ -->
                        <template x-if="isOrderCompleted(order) && (order.payment_status === 'Ghi nợ đại lý' || Number(order.driver_debt_amount || 0) > 0)">
                            <div class="pt-1">
                                <template x-if="order.is_debt_handed_over">
                                    <div class="py-1.5 px-2 rounded-xl text-center text-[11px] font-bold border flex items-center justify-center gap-1 shadow-2xs select-none"
                                         :class="order.debt_handover_info?.status === 'confirmed' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-800 border-amber-300'">
                                        <i class="fa-solid fa-check text-emerald-600" x-show="order.debt_handover_info?.status === 'confirmed'"></i>
                                        <i class="fa-solid fa-clock text-amber-600" x-show="order.debt_handover_info?.status !== 'confirmed'"></i>
                                        <span x-text="order.debt_handover_info?.status === 'confirmed' ? 'Đã bàn giao nợ' : 'Đang chờ duyệt nợ'"></span>
                                    </div>
                                </template>
                                <template x-if="!order.is_debt_handed_over">
                                    <button type="button" @click="openDebtHandoverModal(order)"
                                            class="w-full py-1.5 px-3 rounded-xl font-bold text-xs text-purple-800 bg-purple-100 hover:bg-purple-200 border border-purple-300 transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-98">
                                        <i class="fa-solid fa-share-from-square text-purple-700 text-xs"></i>
                                        <span>Bàn giao công nợ cho Kế toán</span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- 3. Khi chưa xuất bến & chưa kiểm hàng -->
                        <template x-if="!isOrderCompleted(order) && !isDeparted && !isOrderChecked(order)">
                            <div class="pt-1">
                                <button type="button" @click="quickConfirmChecked(order)"
                                        class="w-full py-1.5 px-3 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                    <i class="fa-solid fa-check text-emerald-700 text-xs"></i>
                                    <span>Xác nhận đã kiểm hàng</span>
                                </button>
                            </div>
                        </template>

                        <!-- 4. Khi xe đã xuất bến và đang đi giao: Nút Báo lệch + Giao Đơn Này -->
                        <template x-if="!isOrderCompleted(order) && isDeparted && order.status !== 'Không thành công' && order.status !== 'Đã hoàn kho' && order.status !== 'Chờ thủ kho duyệt hoàn'">
                            <div class="flex items-center gap-2 pt-1">
                                <button type="button" @click="openDiscrepancyModal(order)"
                                        class="py-1.5 px-2.5 rounded-xl text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-300 transition-all flex items-center justify-center gap-1 cursor-pointer shrink-0"
                                        title="Báo thừa/thiếu hàng phát hiện trên xe tới người kiểm hàng">
                                    <i class="fa-solid fa-scale-unbalanced text-amber-600 text-xs"></i>
                                    <span>Báo lệch</span>
                                </button>
                                <a :href="'/delivery/orders/' + order.id + '/confirm'"
                                   class="flex-1 py-1.5 px-3 rounded-xl font-black text-xs text-white bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 hover:from-purple-700 hover:to-indigo-800 shadow-md shadow-purple-600/25 transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-98">
                                    <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                                    <span>Giao Đơn Này (Xác nhận)</span>
                                </a>
                            </div>
                        </template>
                    </div>

                </div>

                <!-- Row 2: Customer Name -->
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 tracking-tight leading-snug"
                        x-text="order.customer_name"></h2>
                </div>

                <!-- Row 3 & 4: Address, Google Maps link & Phone -->
                <div class="space-y-1.5 text-xs">
                    <!-- Địa chỉ & Nút Chỉ đường Maps -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-1.5 min-w-0 text-slate-700 font-medium">
                            <i class="fa-solid fa-location-dot text-rose-500 text-xs mt-0.5 shrink-0"></i>
                            <span class="text-slate-800 line-clamp-2" x-text="order.customer_address || 'Địa chỉ đang cập nhật'"></span>
                        </div>

                        <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(order.customer_address || order.customer_name)"
                           target="_blank"
                           class="px-2 py-0.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold flex items-center gap-1 shrink-0 whitespace-nowrap shadow-2xs">
                            <i class="fa-solid fa-diamond-turn-right text-rose-500 text-[10px]"></i>
                            <span>Chỉ đường</span>
                        </a>
                    </div>

                    <!-- Người nhận & Nút Gọi điện -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0 text-slate-600 font-medium">
                            <i class="fa-solid fa-phone text-sky-600 text-xs shrink-0"></i>
                            <span class="truncate">SĐT: <strong class="font-mono text-slate-900 font-bold" x-text="order.customer_phone || 'Chưa có SĐT'"></strong></span>
                        </div>

                        <template x-if="order.customer_phone">
                            <a :href="'tel:' + order.customer_phone"
                               class="px-2.5 py-1 rounded-lg text-xs font-black text-white bg-purple-600 hover:bg-purple-700 transition-colors flex items-center gap-1 shrink-0 shadow-xs">
                                <i class="fa-solid fa-phone-volume text-[10px]"></i>
                                <span>Gọi khách</span>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Row 5: Financials Row (Lưới 2 cột siêu gọn cho Mobile, 3 cột cho Desktop) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1">
                    
                    <!-- Box 1 (Mobile: Col 1): Cần Thu Tại Điểm Giao (Nổi bật) -->
                    <div class="p-2 sm:p-2.5 bg-amber-50/80 rounded-xl border-2 border-amber-300/80 text-xs flex flex-col justify-between">
                        <div class="text-[10.5px] text-amber-900 font-bold flex items-center justify-between">
                            <span>Cần thu:</span>
                            <span class="text-[9.5px] text-amber-700 font-bold" x-text="order.payment_status === 'Đã thanh toán' ? '(Đã TT)' : '(Thu tiền)'"></span>
                        </div>
                        <div class="text-sm sm:text-base font-black text-amber-950 font-mono mt-0.5 truncate"
                             x-text="getCollectionAmountDisplay(order)"></div>
                        <template x-if="hasReturns(order)">
                            <div class="text-[10px] text-rose-600 font-semibold truncate mt-0.5">
                                (Gốc: <span x-text="formatNumber(order.total_amount) + ' đ'"></span>)
                            </div>
                        </template>
                    </div>

                    <!-- Box 2 (Desktop only): Tổng tiền gốc -->
                    <div class="hidden sm:flex p-2 sm:p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs flex-col justify-between">
                        <div class="text-[11px] text-slate-500 font-bold" x-text="hasReturns(order) ? 'Tổng tiền gốc:' : 'Tổng tiền hàng gốc:'"></div>
                        <div class="text-sm sm:text-base font-black text-slate-800 font-mono mt-0.5 truncate"
                             x-text="formatNumber(order.total_amount) + ' đ'"></div>
                        <template x-if="hasReturns(order)">
                            <div class="mt-0.5 text-[10px] text-rose-600 font-bold truncate">
                                Trả: <span class="font-mono" x-text="'-' + formatNumber(order.total_returned_amount || 0) + ' đ'"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Box 3 (Mobile: Col 2): Hàng hóa dỡ & Chi tiết -->
                    <div class="p-2 sm:p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10.5px] text-slate-500 font-bold">Hàng dỡ:</span>
                            <button type="button" @click="order._showItems = !order._showItems"
                                    class="text-sky-600 hover:text-sky-700 font-bold text-[11px] flex items-center gap-0.5 cursor-pointer">
                                <span x-text="order._showItems ? 'Thu gọn' : 'Chi tiết'"></span>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform" :class="order._showItems ? 'rotate-180' : ''"></i>
                            </button>
                        </div>
                        <div class="text-sm sm:text-base font-black text-slate-800 mt-0.5 truncate"
                             x-text="(order.items ? order.items.length : 0) + ' mặt hàng'"></div>
                    </div>
                </div>

                <!-- Collapsible Items Table -->
                <div x-show="order._showItems" x-collapse class="pt-2 border-t border-slate-100">
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold text-[11px]">
                                <tr>
                                    <th class="p-2">Sản phẩm</th>
                                    <th class="p-2 text-center">ĐVT</th>
                                    <th x-show="hasReturns(order)" class="p-2 text-right text-slate-600">SL Xuất</th>
                                    <th x-show="hasReturns(order)" class="p-2 text-right text-rose-600 font-bold">SL Trả</th>
                                    <th class="p-2 text-right" :class="hasReturns(order) ? 'text-emerald-700 font-bold' : ''">SL Giao</th>
                                    <th x-show="!hasReturns(order)" class="p-2 text-right">Đơn giá</th>
                                    <th class="p-2 text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <template x-for="it in (order.items || [])" :key="it.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-2 font-medium">
                                            <div class="font-bold text-slate-900" x-text="it.name"></div>
                                            <div class="text-[10px] text-slate-400 font-mono" x-text="it.sku"></div>
                                        </td>
                                        <td class="p-2 text-center text-slate-600" x-text="it.unit"></td>
                                        <td x-show="hasReturns(order)" class="p-2 text-right font-mono text-slate-500" x-text="formatNumber(it.quantity)"></td>
                                        <td x-show="hasReturns(order)" class="p-2 text-right font-bold font-mono text-rose-600" x-text="formatNumber(it.returned_qty || 0)"></td>
                                        <td class="p-2 text-right font-black font-mono"
                                            :class="hasReturns(order) ? 'text-emerald-700' : 'text-purple-700'"
                                            x-text="formatNumber(getItemDeliveredQty(order, it))"></td>
                                        <td x-show="!hasReturns(order)" class="p-2 text-right font-mono text-slate-700" x-text="formatNumber(it.unit_price) + ' đ'"></td>
                                        <td class="p-2 text-right font-black font-mono text-slate-900"
                                            x-text="formatNumber(getItemAmount(order, it)) + ' đ'"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot x-show="hasReturns(order)" class="bg-slate-50 font-bold border-t border-slate-200 text-xs">
                                <tr>
                                    <td colspan="2" class="p-2 text-slate-700">Tổng thực giao:</td>
                                    <td class="p-2 text-right font-mono text-slate-500" x-text="formatNumber(order.items?.reduce((s, i) => s + Number(i.quantity || 0), 0) || 0)"></td>
                                    <td class="p-2 text-right font-mono text-rose-600 font-bold" x-text="formatNumber(order.items?.reduce((s, i) => s + Number(i.returned_qty || 0), 0) || 0)"></td>
                                    <td class="p-2 text-right font-mono text-emerald-700 font-black" x-text="formatNumber(order.items?.reduce((s, i) => s + getItemDeliveredQty(order, i), 0) || 0)"></td>
                                    <td class="p-2 text-right font-mono font-black text-slate-900" x-text="formatNumber(order.items?.reduce((s, i) => s + getItemAmount(order, i), 0) || 0) + ' đ'"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Row 6: Notes / Special Instructions (1 dòng thanh mảnh) -->
                <div class="px-2.5 py-1.5 bg-slate-50/80 rounded-xl border border-slate-100 text-[11px] text-slate-600 font-medium flex items-center gap-1.5 truncate">
                    <i class="fa-regular fa-clipboard text-slate-400 shrink-0 text-xs"></i>
                    <span class="font-bold text-slate-700 shrink-0">Ghi chú:</span>
                    <span class="text-slate-600 italic truncate" x-text="order.notes || 'Giao hàng đúng giờ và kiểm đếm theo phiếu xuất kho.'"></span>
                </div>

            </div>
        </template>

        <!-- Card tổng kết ở cuối danh sách đơn khi đã giao xong 100% toàn bộ đơn hàng -->
        <div x-show="isDeparted && !isReturning && isAllCompleted"
             class="bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 border-2 border-emerald-400/80 rounded-3xl p-6 text-center space-y-3 shadow-md">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-white flex items-center justify-center mx-auto text-2xl shadow-md shadow-emerald-500/30">
                <i class="fa-solid fa-flag-checkered"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">Đã giao xong toàn bộ đơn hàng của chuyến này!</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Toàn bộ <span class="font-bold font-mono text-emerald-700" x-text="orders.length"></span> đơn hàng đã được giao hoàn tất. Lái xe bấm nút <strong>Quay Về</strong> để bắt đầu di chuyển về bãi xe / kho công ty.
                </p>
            </div>
            <div class="pt-1">
                <button type="button" @click="returnToDepot()" :disabled="isReturningVehicle"
                        class="px-6 py-3 rounded-2xl font-black text-sm text-slate-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-400 hover:from-amber-200 hover:to-yellow-300 transition-all inline-flex items-center gap-2 shadow-xl shadow-amber-500/30 cursor-pointer hover:scale-105 active:scale-95 disabled:opacity-50">
                    <i class="fa-solid fa-house-chimney text-base"></i>
                    <span x-text="isReturningVehicle ? 'Đang cập nhật...' : 'Bấm Quay Về (Về Bãi / Kho)'"></span>
                </button>
            </div>
        </div>

        <!-- Card khi xe đang trên đường quay về bãi / kho -->
        <div x-show="isReturning"
             class="bg-gradient-to-r from-amber-50 via-slate-50 to-indigo-50 border-2 border-amber-400/80 rounded-3xl p-6 text-center space-y-3 shadow-md">
            <div class="w-14 h-14 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center mx-auto text-2xl shadow-md shadow-amber-500/30">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">Xe đang trên đường quay về bãi / kho</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Khi xe đã về đến bãi an toàn, vui lòng xác nhận để hoàn tất toàn bộ hành trình chuyến đi.
                </p>
            </div>
            <div class="pt-1">
                <button type="button" @click="finishTripArrival()" :disabled="isReturningVehicle"
                        class="px-6 py-3 rounded-2xl font-black text-sm text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 transition-all inline-flex items-center gap-2 shadow-xl shadow-emerald-600/30 cursor-pointer hover:scale-105 active:scale-95 disabled:opacity-50">
                    <i class="fa-solid fa-flag-checkered text-base"></i>
                    <span x-text="isReturningVehicle ? 'Đang cập nhật...' : '🏁 Đã Về Tới Bãi / Kho (Kết thúc chuyến)'"></span>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="filteredOrders.length === 0"
             class="bg-white rounded-3xl p-12 border border-slate-200 text-center space-y-3 shadow-2xs">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">Không có đơn hàng nào khớp bộ lọc trên xe này</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                Hiện tại xe <strong class="font-mono text-slate-700" x-text="selectedVehicle?.plate_number"></strong> chưa có đơn hàng nào cần xử lý trong danh sách này.
            </p>
            <div class="pt-2">
                <a href="{{ url('/delivery-dispatch') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-route"></i>
                    <span>Sang màn hình Điều Phối Xe để xếp đơn vào xe</span>
                </a>
            </div>
        </div>
    </div>
