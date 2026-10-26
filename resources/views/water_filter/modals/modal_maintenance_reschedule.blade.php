<!-- MODAL: DỜI / THIẾT LẬP LỊCH BẢO DƯỠNG & THAY LÕI -->
<div 
    x-show="modals.rescheduleModal" 
    x-cloak 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
>
    <div 
        @click.away="closeModal('rescheduleModal')" 
        class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-8"
    >
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-purple-50 to-white">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm shadow-xs">
                    <i class="fa-regular fa-calendar-plus"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base">Thiết Lập / Dời Lịch Bảo Dưỡng</h3>
                    <p class="text-[11px] text-slate-400">Điều chỉnh ngày hẹn thay lõi lọc và bảo dưỡng máy lọc nước</p>
                </div>
            </div>
            <button 
                type="button" 
                @click="closeModal('rescheduleModal')" 
                class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submitRescheduleMaintenance()" class="p-5 space-y-4 text-xs">
            <!-- Machine & Customer info preview -->
            <div class="bg-purple-50/60 p-3.5 rounded-xl border border-purple-100 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-purple-900" x-text="rescheduleTarget?.customer_name"></span>
                    <span class="text-[10px] font-mono text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full" x-text="rescheduleTarget?.customer_phone"></span>
                </div>
                <div class="text-[11px] text-slate-600 flex items-center gap-1.5">
                    <i class="fa-solid fa-faucet-drip text-purple-500"></i>
                    <span x-text="rescheduleTarget?.machine_model"></span>
                </div>
                <div class="text-[10px] text-slate-500 flex items-center gap-1">
                    <i class="fa-solid fa-location-dot text-slate-400"></i>
                    <span x-text="rescheduleTarget?.customer_address"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <!-- Ngày hẹn mới -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Ngày hẹn bảo dưỡng mới <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        x-model="rescheduleForm.next_maintenance_date" 
                        required 
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-500 font-bold text-slate-800 text-xs"
                    >
                </div>

                <!-- Kỳ lặp lại (tháng) -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Kỳ thay lõi / Bảo dưỡng
                    </label>
                    <select 
                        x-model.number="rescheduleForm.filter_replacement_months" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-500 font-semibold text-slate-800 text-xs"
                    >
                        <option value="3">3 Tháng / Lần (Nước giếng/Tần suất cao)</option>
                        <option value="6">6 Tháng / Lần (Tiêu chuẩn mặc định)</option>
                        <option value="9">9 Tháng / Lần</option>
                        <option value="12">12 Tháng (1 Năm / Lần)</option>
                    </select>
                </div>
            </div>

            <!-- Gợi ý chọn nhanh ngày hẹn -->
            <div>
                <span class="text-[11px] text-slate-400">Chọn nhanh ngày hẹn:</span>
                <div class="flex flex-wrap gap-1.5 mt-1">
                    <button 
                        type="button" 
                        @click="setRescheduleQuickDays(3)" 
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-700 font-semibold text-[11px] transition"
                    >
                        +3 ngày
                    </button>
                    <button 
                        type="button" 
                        @click="setRescheduleQuickDays(7)" 
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-700 font-semibold text-[11px] transition"
                    >
                        +1 tuần
                    </button>
                    <button 
                        type="button" 
                        @click="setRescheduleQuickDays(14)" 
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-700 font-semibold text-[11px] transition"
                    >
                        +2 tuần
                    </button>
                    <button 
                        type="button" 
                        @click="setRescheduleQuickMonths(1)" 
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-700 font-semibold text-[11px] transition"
                    >
                        +1 tháng
                    </button>
                    <button 
                        type="button" 
                        @click="setRescheduleQuickMonths(6)" 
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-700 font-semibold text-[11px] transition"
                    >
                        +6 tháng
                    </button>
                </div>
            </div>

            <!-- Phân công KTV -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Kỹ thuật viên phụ trách lịch hẹn</label>
                <select 
                    x-model="rescheduleForm.technician_id" 
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-500 text-xs font-medium text-slate-800"
                >
                    <option value="">-- Chọn kỹ thuật viên phụ trách --</option>
                    <template x-for="tech in technicians" :key="tech.id">
                        <option :value="tech.id" x-text="tech.name + (tech.phone ? ' - ' + tech.phone : '')"></option>
                    </template>
                </select>
            </div>

            <!-- Ghi chú / Lý do dời hẹn -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Ghi chú hẹn / Lý do dời</label>
                <textarea 
                    x-model="rescheduleForm.notes" 
                    rows="2.5" 
                    placeholder="Ví dụ: Khách hẹn cuối tuần gọi trước 30p, cần mang theo lõi 1, 2, 3 và màng RO..." 
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-purple-500 text-xs text-slate-700"
                ></textarea>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                <button 
                    type="button" 
                    @click="closeModal('rescheduleModal')" 
                    class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition"
                >
                    Hủy bỏ
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition shadow-md shadow-purple-600/20 cursor-pointer"
                >
                    Lưu Lịch Bảo Dưỡng
                </button>
            </div>
        </form>
    </div>
</div>
