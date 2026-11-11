<!-- ========================================================================= -->
<!-- MODAL: ĐIỀU CHUYỂN ĐƠN HÀNG GIỮA CÁC XE (TỐI ƯU GIAO DIỆN MOBILE & DESKTOP) -->
<!-- ========================================================================= -->
<div x-show="showTransferModal" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-2 sm:p-4 bg-slate-900/80 backdrop-blur-xs overflow-y-auto"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="showTransferModal = false"
         class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden my-auto flex flex-col max-h-[96vh] sm:max-h-[90vh]">

        <!-- 1. Header Modal -->
        <div class="px-3.5 py-2.5 sm:px-5 sm:py-3.5 bg-gradient-to-r from-indigo-700 via-indigo-800 to-purple-800 text-white flex items-center justify-between gap-2.5 shrink-0">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center text-sm sm:text-base shadow-inner shrink-0">
                    <i class="fa-solid fa-right-left text-white"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-black text-xs sm:text-base leading-tight">
                        Điều Chuyển Đơn Hàng Giữa Các Xe
                    </h3>
                    <p class="text-[10px] sm:text-[11px] text-indigo-100/90 truncate mt-0.5">
                        Chuyển đơn ⇄ Xác nhận tiếp nhận ⇄ Thông báo tức thời
                    </p>
                </div>
            </div>

            <button type="button" @click="showTransferModal = false"
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer shrink-0">
                <i class="fa-solid fa-xmark text-xs sm:text-sm"></i>
            </button>
        </div>

        <!-- 2. Navigation Tabs (3 tabs chạm cảm ứng dễ dàng) -->
        <div class="px-2.5 sm:px-4 py-2 border-b border-slate-100 bg-slate-50 shrink-0">
            <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                <!-- Tab Chờ tiếp nhận -->
                <button type="button" @click="transferTab = 'incoming'"
                        class="py-1.5 sm:py-2 px-1.5 rounded-xl text-[11px] sm:text-xs font-black transition-all flex items-center justify-center gap-1 cursor-pointer text-center relative"
                        :class="transferTab === 'incoming' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'">
                    <i class="fa-solid fa-inbox text-xs"></i>
                    <span>Chờ nhận</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                          :class="transferTab === 'incoming' ? 'bg-white/20 text-white' : (pendingIncomingTransfers.length > 0 ? 'bg-rose-500 text-white font-bold animate-pulse' : 'bg-slate-200 text-slate-700')"
                          x-text="pendingIncomingTransfers.length"></span>
                </button>

                <!-- Tab Tạo yêu cầu chuyển đi -->
                <button type="button" @click="transferTab = 'create'"
                        class="py-1.5 sm:py-2 px-1.5 rounded-xl text-[11px] sm:text-xs font-black transition-all flex items-center justify-center gap-1 cursor-pointer text-center"
                        :class="transferTab === 'create' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Chuyển đi</span>
                </button>

                <!-- Tab Lịch sử -->
                <button type="button" @click="transferTab = 'history'"
                        class="py-1.5 sm:py-2 px-1.5 rounded-xl text-[11px] sm:text-xs font-black transition-all flex items-center justify-center gap-1 cursor-pointer text-center"
                        :class="transferTab === 'history' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'">
                    <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                    <span>Lịch sử</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                          :class="transferTab === 'history' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-800'"
                          x-text="transfers.length"></span>
                </button>
            </div>
        </div>

        <!-- 3. Modal Body -->
        <div class="p-3 sm:p-4.5 overflow-y-auto space-y-3 sm:space-y-3.5 text-xs flex-1 scrollbar-thin">

            <!-- ============================================================= -->
            <!-- TAB 1: ĐƠN CHỜ TIẾP NHẬN (INCOMING)                            -->
            <!-- ============================================================= -->
            <div x-show="transferTab === 'incoming'" class="space-y-3">
                <template x-if="pendingIncomingTransfers.length === 0">
                    <div class="py-10 px-4 text-center space-y-2">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <h4 class="font-black text-slate-700 text-sm">Không có đơn hàng nào chờ tiếp nhận</h4>
                        <p class="text-slate-500 text-xs max-w-sm mx-auto">
                            Khi xe khác gửi yêu cầu điều chuyển đơn hàng sang xe <strong class="text-indigo-600 font-mono" x-text="selectedVehicle?.plate_number"></strong>, đơn sẽ hiển thị tại đây để bạn xác nhận.
                        </p>
                    </div>
                </template>

                <template x-for="t in pendingIncomingTransfers" :key="t.id">
                    <div class="bg-gradient-to-br from-indigo-50/70 via-white to-purple-50/50 border-2 border-indigo-200 rounded-2xl p-3 sm:p-3.5 space-y-2.5 shadow-sm">
                        <!-- Header thẻ đơn chuyển -->
                        <div class="flex items-start justify-between gap-2 border-b border-indigo-100 pb-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-lg bg-indigo-600 text-white font-mono font-black text-[11px]" x-text="'#' + t.order_code"></span>
                                    <span class="px-1.5 py-0.2 rounded-md bg-amber-100 text-amber-900 font-bold text-[10px] border border-amber-200">
                                        <i class="fa-solid fa-clock text-[9px]"></i> Chờ bạn nhận
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                    <span>Từ xe:</span>
                                    <strong class="text-purple-700 font-mono font-bold" x-text="t.from_vehicle_plate"></strong>
                                    <span>(Tài xế: <span class="font-bold text-slate-800" x-text="t.from_driver_name || 'Tài xế'"></span>)</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-black text-xs sm:text-sm text-indigo-700 font-mono" x-text="formatCurrency(t.order_amount) + ' đ'"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="formatDate(t.created_at)"></div>
                            </div>
                        </div>

                        <!-- Khách hàng & địa chỉ -->
                        <div class="space-y-1 text-xs">
                            <div class="font-black text-slate-800 truncate" x-text="t.customer_name"></div>
                            <div class="text-[11px] text-slate-600 flex items-start gap-1">
                                <i class="fa-solid fa-location-dot text-rose-500 text-[10px] mt-0.5 shrink-0"></i>
                                <span class="line-clamp-2" x-text="t.customer_address || 'Địa chỉ đang cập nhật'"></span>
                            </div>
                            <div class="p-2 rounded-xl bg-white border border-indigo-100 text-[11px] text-slate-700">
                                <span class="font-bold text-indigo-900">Lý do chuyển:</span>
                                <span class="italic text-slate-600" x-text="t.reason"></span>
                            </div>
                        </div>

                        <!-- Hai nút hành động: Đồng ý tiếp nhận hoặc Từ chối -->
                        <div class="pt-1 grid grid-cols-2 gap-2">
                            <button type="button" @click="promptRejectTransfer(t)"
                                    class="py-2 px-2.5 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-300 transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-98">
                                <i class="fa-solid fa-xmark text-xs"></i>
                                <span>Từ chối</span>
                            </button>

                            <button type="button" @click="respondVehicleTransfer(t.id, 'accept')"
                                    class="py-2 px-2.5 rounded-xl text-xs font-black text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25 transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-98">
                                <i class="fa-solid fa-check text-xs"></i>
                                <span>✓ Nhận vào xe</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 2: TẠO YÊU CẦU ĐIỀU CHUYỂN ĐI (CREATE)                     -->
            <!-- ============================================================= -->
            <div x-show="transferTab === 'create'" class="space-y-3 sm:space-y-3.5">

                <!-- 1. Chọn Đơn Hàng Cần Chuyển -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700 text-xs">
                            Chọn đơn hàng cần chuyển <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500 font-normal">
                            Từ xe: <strong class="text-indigo-700 font-mono font-bold" x-text="selectedVehicle?.plate_number"></strong>
                        </span>
                    </div>

                    <select x-model="transferForm.order_id"
                            class="w-full px-3 py-2 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-bold text-slate-800 outline-none focus:border-indigo-500 transition-all cursor-pointer text-xs sm:text-sm">
                        <option value="">-- Chọn đơn hàng trên xe --</option>
                        <template x-for="ord in transferableOrders" :key="ord.id">
                            <option :value="ord.id" x-text="'#' + ord.order_code + ' - ' + ord.customer_name + ' (' + formatCurrency(ord.total_amount) + ' đ)'"></option>
                        </template>
                    </select>
                </div>

                <!-- Preview thông tin đơn hàng đang chọn -->
                <template x-if="selectedTransferOrder">
                    <div class="p-2.5 rounded-xl bg-indigo-50/70 border border-indigo-200 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between font-bold text-indigo-950">
                            <span x-text="'Đơn #' + selectedTransferOrder.order_code"></span>
                            <span class="font-mono text-indigo-700" x-text="formatCurrency(selectedTransferOrder.total_amount) + ' đ'"></span>
                        </div>
                        <div class="text-[11px] text-slate-700">
                            <strong>Khách hàng:</strong> <span x-text="selectedTransferOrder.customer_name"></span>
                        </div>
                        <div class="text-[11px] text-slate-600 line-clamp-2">
                            <strong>Địa chỉ:</strong> <span x-text="selectedTransferOrder.customer_address || 'Chưa có địa chỉ'"></span>
                        </div>
                    </div>
                </template>

                <!-- 2. Chọn Xe Tiếp Nhận -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">
                        Xe tiếp nhận đơn hàng <span class="text-rose-500">*</span>
                    </label>

                    <select x-model="transferForm.to_vehicle_plate"
                            class="w-full px-3 py-2 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-bold text-slate-800 outline-none focus:border-indigo-500 transition-all cursor-pointer text-xs sm:text-sm">
                        <option value="">-- Chọn xe nhận đơn hàng --</option>
                        <template x-for="veh in targetAvailableVehicles" :key="veh.id || veh.plate_number">
                            <option :value="veh.plate_number" 
                                    x-text="veh.plate_number + ' - ' + (veh.driver ? veh.driver.name : (veh.driver_name || 'Chưa gán lái xe')) + (veh.delivery_route ? ' [' + veh.delivery_route.name + ']' : '')"></option>
                        </template>
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1 italic">
                        * Sau khi gửi, tài xế xe nhận sẽ nhận được thông báo để xác nhận đưa đơn vào xe.
                    </p>
                </div>

                <!-- 3. Lý Do Điều Chuyển -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1">
                        Lý do điều chuyển đơn hàng <span class="text-rose-500">*</span>
                    </label>

                    <!-- Chọn nhanh lý do mẫu -->
                    <div class="grid grid-cols-2 gap-1.5 mb-2">
                        <button type="button" @click="transferForm.reason = 'Xếp ghép tuyến giao hàng tiện đường hơn'"
                                class="p-1.5 rounded-lg border text-left text-[11px] font-semibold transition-colors cursor-pointer"
                                :class="transferForm.reason === 'Xếp ghép tuyến giao hàng tiện đường hơn' ? 'bg-indigo-100 text-indigo-900 border-indigo-400' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            🛣️ Tiện ghép tuyến đường
                        </button>
                        <button type="button" @click="transferForm.reason = 'Xe quá tải trọng / đầy khoang chứa'"
                                class="p-1.5 rounded-lg border text-left text-[11px] font-semibold transition-colors cursor-pointer"
                                :class="transferForm.reason === 'Xe quá tải trọng / đầy khoang chứa' ? 'bg-indigo-100 text-indigo-900 border-indigo-400' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            ⚖️ Xe quá tải trọng
                        </button>
                        <button type="button" @click="transferForm.reason = 'Xe gặp sự cố kỹ thuật / hỏng hóc'"
                                class="p-1.5 rounded-lg border text-left text-[11px] font-semibold transition-colors cursor-pointer"
                                :class="transferForm.reason === 'Xe gặp sự cố kỹ thuật / hỏng hóc' ? 'bg-indigo-100 text-indigo-900 border-indigo-400' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            🔧 Xe sự cố kỹ thuật
                        </button>
                        <button type="button" @click="transferForm.reason = 'Khách hẹn giờ gấp, xe khác giao sớm hơn'"
                                class="p-1.5 rounded-lg border text-left text-[11px] font-semibold transition-colors cursor-pointer"
                                :class="transferForm.reason === 'Khách hẹn giờ gấp, xe khác giao sớm hơn' ? 'bg-indigo-100 text-indigo-900 border-indigo-400' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            ⏱️ Khách hẹn giờ gấp
                        </button>
                    </div>

                    <textarea x-model="transferForm.reason" rows="2"
                              placeholder="Nhập chi tiết lý do điều chuyển đơn hàng..."
                              class="w-full px-3 py-2 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-medium text-slate-800 outline-none focus:border-indigo-500 transition-all text-xs"></textarea>
                </div>

                <!-- Nút Gửi Yêu Cầu -->
                <div class="pt-2">
                    <button type="button" @click="submitVehicleTransfer()"
                            :disabled="transferForm.isSubmitting || !transferForm.order_id || !transferForm.to_vehicle_plate"
                            class="w-full py-2.5 px-4 rounded-xl font-black text-xs sm:text-sm text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 hover:from-indigo-700 hover:to-purple-800 shadow-md shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="transferForm.isSubmitting">
                            <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                        </template>
                        <template x-if="!transferForm.isSubmitting">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                        </template>
                        <span x-text="transferForm.isSubmitting ? 'Đang gửi yêu cầu...' : 'Gửi Yêu Cầu Điều Chuyển Đơn'"></span>
                    </button>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 3: LỊCH SỬ ĐIỀU CHUYỂN (HISTORY)                           -->
            <!-- ============================================================= -->
            <div x-show="transferTab === 'history'" class="space-y-3">
                <template x-if="transfers.length === 0">
                    <div class="py-10 px-4 text-center space-y-2">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <h4 class="font-black text-slate-700 text-sm">Chưa có lịch sử điều chuyển</h4>
                        <p class="text-slate-500 text-xs">Tất cả các lượt điều chuyển gửi đi và nhận về sẽ được lưu vết tại đây.</p>
                    </div>
                </template>

                <template x-for="t in transfers" :key="t.id">
                    <div class="p-3 rounded-2xl border space-y-2 text-xs transition-colors"
                         :class="{
                             'bg-amber-50/50 border-amber-200': t.status === 'pending',
                             'bg-emerald-50/40 border-emerald-200': t.status === 'accepted',
                             'bg-rose-50/40 border-rose-200': t.status === 'rejected',
                             'bg-slate-50 border-slate-200': t.status === 'cancelled'
                         }">
                        <!-- Dòng 1: Mã đơn & Trạng thái -->
                        <div class="flex items-center justify-between gap-2 border-b border-slate-200/60 pb-1.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-900 text-white font-mono font-black text-[11px]" x-text="'#' + t.order_code"></span>
                                <span class="text-[10px] text-slate-500 font-mono" x-text="t.transfer_code"></span>
                            </div>

                            <!-- Badge trạng thái -->
                            <div>
                                <template x-if="t.status === 'pending'">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 font-black text-[10px] inline-flex items-center gap-1">
                                        <i class="fa-solid fa-hourglass-half text-[9px] animate-pulse"></i> Chờ tiếp nhận
                                    </span>
                                </template>
                                <template x-if="t.status === 'accepted'">
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900 border border-emerald-300 font-black text-[10px] inline-flex items-center gap-1">
                                        <i class="fa-solid fa-check text-[9px]"></i> Đã tiếp nhận
                                    </span>
                                </template>
                                <template x-if="t.status === 'rejected'">
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-900 border border-rose-300 font-black text-[10px] inline-flex items-center gap-1">
                                        <i class="fa-solid fa-xmark text-[9px]"></i> Đã từ chối
                                    </span>
                                </template>
                                <template x-if="t.status === 'cancelled'">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 border border-slate-300 font-black text-[10px]">
                                        Đã hủy
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Tuyến chuyển: Xe A -> Xe B -->
                        <div class="flex items-center justify-between gap-2 text-[11px] font-bold">
                            <div class="flex items-center gap-1.5 text-slate-700 truncate">
                                <span class="text-purple-700 font-mono" x-text="'Xe ' + t.from_vehicle_plate"></span>
                                <i class="fa-solid fa-arrow-right text-[10px] text-slate-400"></i>
                                <span class="text-indigo-700 font-mono" x-text="'Xe ' + t.to_vehicle_plate"></span>
                            </div>
                            <div class="text-right text-indigo-700 font-mono font-black" x-text="formatCurrency(t.order_amount) + ' đ'"></div>
                        </div>

                        <!-- Khách hàng & Lý do -->
                        <div class="space-y-1 text-[11px]">
                            <div class="font-black text-slate-800 truncate" x-text="t.customer_name"></div>
                            <div class="text-slate-600">
                                <strong>Lý do:</strong> <span class="italic" x-text="t.reason"></span>
                            </div>
                            <template x-if="t.rejection_reason">
                                <div class="text-rose-700 bg-rose-100/60 p-1.5 rounded-lg border border-rose-200">
                                    <strong>Lý do từ chối:</strong> <span x-text="t.rejection_reason"></span>
                                </div>
                            </template>
                            <template x-if="t.responded_at">
                                <div class="text-[10px] text-slate-500">
                                    Người duyệt: <strong x-text="t.responded_by_name || 'Tài xế nhận'"></strong> vào lúc <span x-text="formatDate(t.responded_at)"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Nút hủy yêu cầu (nếu người gửi là xe này và đang pending) -->
                        <template x-if="t.status === 'pending' && t.from_vehicle_plate === selectedVehicle?.plate_number">
                            <div class="pt-1 text-right">
                                <button type="button" @click="cancelVehicleTransfer(t.id)"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-600 hover:text-rose-700 bg-white hover:bg-rose-50 border border-slate-300 transition-colors cursor-pointer">
                                    <i class="fa-solid fa-ban text-[10px]"></i> Hủy yêu cầu chuyển
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

        </div>

    </div>
</div>

