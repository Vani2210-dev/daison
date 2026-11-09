    <!-- ========================================================================= -->
    <!-- MODAL: XÁC NHẬN HOÀN ĐƠN VÀO KHO (TÌM & CHỌN THỦ KHO XÁC NHẬN)             -->
    <!-- ========================================================================= -->
    <div x-show="showOrderReturnWarehouseModal" x-cloak
         class="fixed inset-0 z-[10000] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl p-4 sm:p-5 max-w-lg w-full shadow-2xl border border-slate-200 flex flex-col max-h-[85vh] sm:max-h-[90vh] my-auto"
             @click.away="if (!orderReturnForm.isSubmitting) showOrderReturnWarehouseModal = false"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-600 flex items-center justify-center font-bold text-lg shrink-0">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Xác Nhận Hoàn Đơn Vào Kho</h3>
                        <p class="text-xs text-slate-500 font-medium">
                            Đơn hàng: <span class="font-mono font-bold text-purple-700" x-text="orderReturnForm.order ? ('#' + orderReturnForm.order.order_code) : ''"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="showOrderReturnWarehouseModal = false" :disabled="orderReturnForm.isSubmitting"
                        class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer p-1 leading-none">
                    &times;
                </button>
            </div>

            <!-- Body (Scrollable if screen is very small) -->
            <div class="overflow-y-auto flex-1 space-y-3.5 text-xs pr-1 py-2">
                <!-- Thông tin tóm tắt đơn hàng -->
                <template x-if="orderReturnForm.order">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-1.5">
                        <div class="flex items-center justify-between font-bold text-slate-800">
                            <span class="truncate pr-2" x-text="orderReturnForm.order.customer_name"></span>
                            <span class="font-mono text-purple-700 shrink-0 font-black" x-text="formatNumber(orderReturnForm.order.total_amount) + ' đ'"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 text-[11px] gap-2 pt-0.5 border-t border-slate-200/60">
                            <span>Mặt hàng: <strong class="text-slate-700 font-mono" x-text="orderReturnForm.order.items?.length || 0"></strong> SP</span>
                            <span class="text-rose-600 font-bold truncate text-right" x-text="orderReturnForm.order.failed_delivery_reason || 'Giao không thành công'"></span>
                        </div>
                    </div>
                </template>

                <!-- Form chọn người xác nhận và kho -->
                <div class="space-y-3">
                    <!-- 1. BẮT BUỘC: Nhập Tìm & Chọn Thủ kho xác nhận -->
                    <div class="space-y-1.5 relative" @click.away="orderReturnForm.isStaffDropdownOpen = false">
                        <label class="font-black text-slate-800 flex items-center justify-between gap-1">
                            <span>Thủ kho / Người nhận hàng <span class="text-rose-600">*</span>:</span>
                            <span class="text-[10px] text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded font-semibold border border-rose-200 shrink-0">Bắt buộc</span>
                        </label>

                        <!-- Ô nhập tìm kiếm -->
                        <div class="relative">
                            <input type="text"
                                   x-model="orderReturnForm.intakeStaffSearch"
                                   @focus="orderReturnForm.isStaffDropdownOpen = true"
                                   @input="orderReturnForm.isStaffDropdownOpen = true; orderReturnForm.intakeStaffId = ''"
                                   placeholder="Gõ tên thủ kho để tìm & chọn..."
                                   class="w-full pl-9 pr-9 py-2.5 rounded-xl border font-bold text-xs focus:outline-none transition-colors shadow-2xs"
                                   :class="orderReturnForm.intakeStaffId ? 'border-emerald-500 bg-emerald-50/25 text-emerald-900' : 'border-slate-300 bg-white text-slate-900 focus:border-amber-500'">
                            
                            <!-- Search Icon -->
                            <div class="absolute left-3 top-2.5 pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-xs" :class="orderReturnForm.intakeStaffId ? 'text-emerald-600' : 'text-slate-400'"></i>
                            </div>

                            <!-- Clear or Toggle dropdown button -->
                            <button type="button"
                                    x-show="orderReturnForm.intakeStaffSearch"
                                    @click="clearIntakeStaff()"
                                    class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 p-1 text-xs font-bold rounded-lg cursor-pointer">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <button type="button"
                                    x-show="!orderReturnForm.intakeStaffSearch"
                                    @click="orderReturnForm.isStaffDropdownOpen = !orderReturnForm.isStaffDropdownOpen"
                                    class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 p-1 text-xs font-bold rounded-lg cursor-pointer">
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </button>
                        </div>

                        <!-- Dropdown Kết quả tìm kiếm thủ kho -->
                        <div x-show="orderReturnForm.isStaffDropdownOpen"
                             x-cloak
                             class="absolute z-50 left-0 right-0 top-full mt-1 max-h-48 overflow-y-auto bg-white rounded-2xl shadow-2xl border border-slate-200 divide-y divide-slate-100 text-xs">
                            <template x-for="staff in filteredWarehouseStaff" :key="staff.id">
                                <button type="button"
                                        @click="selectIntakeStaff(staff)"
                                        class="w-full text-left px-3.5 py-2.5 hover:bg-amber-50 active:bg-amber-100 flex items-center justify-between transition-colors cursor-pointer"
                                        :class="String(orderReturnForm.intakeStaffId) === String(staff.id) ? 'bg-amber-50/70' : ''">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-100/70 border border-amber-200 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0">
                                            <i class="fa-solid fa-user text-[11px]"></i>
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-800" x-text="staff.name"></div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-2">
                                                <span x-text="staff.department || 'Thủ kho'"></span>
                                                <span x-show="staff.phone" class="font-mono text-[10px] text-slate-400" x-text="'• ' + staff.phone"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="String(orderReturnForm.intakeStaffId) === String(staff.id)" class="text-emerald-600 font-black text-sm">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                </button>
                            </template>
                            <div x-show="filteredWarehouseStaff.length === 0" class="p-3 text-center text-slate-400 text-xs italic">
                                Không tìm thấy thủ kho nào với từ khóa trên
                            </div>
                        </div>

                        <!-- Trạng thái xác nhận đã chọn -->
                        <div x-show="orderReturnForm.intakeStaffId" class="flex items-center gap-1.5 text-[11px] text-emerald-700 font-semibold px-1">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                            <span>Đã chọn: <strong x-text="orderReturnForm.intakeStaffSearch"></strong></span>
                        </div>
                    </div>

                    <!-- 2. Kho nhập lại -->
                    <div class="space-y-1">
                        <label class="font-bold text-slate-800 block">Kho nhập lại hàng:</label>
                        <select x-model="orderReturnForm.warehouseId"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-bold text-xs text-slate-900 focus:outline-none focus:border-amber-500 bg-white shadow-2xs">
                            <template x-for="wh in warehouses" :key="wh.id">
                                <option :value="wh.id" x-text="wh.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- 3. Ghi chú hoàn kho -->
                    <div class="space-y-1">
                        <label class="font-bold text-slate-800 block">Ghi chú xác nhận hoàn kho:</label>
                        <input type="text" x-model="orderReturnForm.notes"
                               placeholder="VD: Xe về bãi an toàn, thủ kho đã nhận đủ hàng dỡ từ xe..."
                               class="w-full p-2.5 rounded-xl border border-slate-300 font-medium text-xs text-slate-800 focus:outline-none focus:border-amber-500 bg-white shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- Footer Buttons (Fixed/Pinned at bottom) -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 text-xs shrink-0 mt-1">
                <button type="button" @click="showOrderReturnWarehouseModal = false" :disabled="orderReturnForm.isSubmitting"
                        class="px-4 py-2.5 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitOrderReturnWarehouse()" :disabled="orderReturnForm.isSubmitting"
                        class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black rounded-xl cursor-pointer shadow-md shadow-amber-500/25 disabled:opacity-50 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-boxes-packing text-slate-950"></i>
                    <span x-text="orderReturnForm.isSubmitting ? 'Đang hoàn kho...' : 'Xác Nhận Hoàn Kho'"></span>
                </button>
            </div>

        </div>
    </div>
