<!-- MODAL: SOẠN & SAO CHÉP MẪU TIN NHẮN NHẮC BẢO DƯỠNG ZALO/SMS -->
<div 
    x-show="modals.reminderModal" 
    x-cloak 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
>
    <div 
        @click.away="closeModal('reminderModal')" 
        class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-8"
    >
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-white">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow-xs">
                    <i class="fa-regular fa-comment-dots"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base">Mẫu Tin Nhắn Nhắc Thay Lõi & Bảo Dưỡng</h3>
                    <p class="text-[11px] text-slate-400">Gửi chăm sóc khách hàng qua Zalo hoặc tin nhắn SMS</p>
                </div>
            </div>
            <button 
                type="button" 
                @click="closeModal('reminderModal')" 
                class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-5 space-y-4 text-xs">
            <!-- Thông tin khách -->
            <template x-if="reminderTarget">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800" x-text="reminderTarget.customer_name"></span>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            Model: <strong x-text="reminderTarget.machine_model"></strong>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-bold text-emerald-700" x-text="reminderTarget.customer_phone"></span>
                        <div class="text-[10px] text-purple-700 font-semibold" x-text="'Hẹn tới: ' + reminderTarget.next_maintenance_date"></div>
                    </div>
                </div>
            </template>

            <!-- Chọn loại mẫu tin -->
            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Chọn phong cách tin nhắn:</label>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        type="button" 
                        @click="reminderTemplateType = 'friendly'; generateReminderText()" 
                        class="p-2.5 rounded-xl border text-center font-bold text-xs transition cursor-pointer"
                        :class="reminderTemplateType === 'friendly' ? 'border-blue-600 bg-blue-50 text-blue-700 ring-2 ring-blue-600/20' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                    >
                        <i class="fa-regular fa-face-smile block text-sm mb-1"></i>
                        Thân thiện / Zalo
                    </button>
                    <button 
                        type="button" 
                        @click="reminderTemplateType = 'formal'; generateReminderText()" 
                        class="p-2.5 rounded-xl border text-center font-bold text-xs transition cursor-pointer"
                        :class="reminderTemplateType === 'formal' ? 'border-blue-600 bg-blue-50 text-blue-700 ring-2 ring-blue-600/20' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                    >
                        <i class="fa-solid fa-briefcase block text-sm mb-1"></i>
                        Trang trọng / CSKH
                    </button>
                    <button 
                        type="button" 
                        @click="reminderTemplateType = 'urgent'; generateReminderText()" 
                        class="p-2.5 rounded-xl border text-center font-bold text-xs transition cursor-pointer"
                        :class="reminderTemplateType === 'urgent' ? 'border-rose-600 bg-rose-50 text-rose-700 ring-2 ring-rose-600/20' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                    >
                        <i class="fa-solid fa-triangle-exclamation block text-sm mb-1"></i>
                        Nhắc quá hạn
                    </button>
                </div>
            </div>

            <!-- Khung nội dung tin nhắn -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="font-bold text-slate-700">Nội dung tin nhắn:</label>
                    <span class="text-[10px] text-slate-400">Có thể chỉnh sửa trực tiếp trước khi gửi</span>
                </div>
                <textarea 
                    x-model="reminderMessage" 
                    rows="6" 
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 font-medium text-xs text-slate-800 leading-relaxed"
                ></textarea>
            </div>

            <!-- Footer actions -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <template x-if="reminderTarget?.customer_phone">
                    <a 
                        :href="'https://zalo.me/' + reminderTarget.customer_phone.replace(/[^0-9]/g, '')" 
                        target="_blank" 
                        class="px-4 py-2.5 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold transition flex items-center gap-1.5 shadow-xs"
                    >
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Mở Chat Zalo</span>
                    </a>
                </template>

                <div class="flex items-center gap-2 ml-auto">
                    <button 
                        type="button" 
                        @click="copyReminderMessage()" 
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center gap-1.5 shadow-md shadow-emerald-600/20 cursor-pointer"
                    >
                        <i class="fa-regular fa-copy"></i>
                        <span x-text="copiedMessage ? 'Đã Sao Chép!' : 'Sao Chép Tin Nhắn'"></span>
                    </button>
                    <button 
                        type="button" 
                        @click="closeModal('reminderModal')" 
                        class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50 transition"
                    >
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
