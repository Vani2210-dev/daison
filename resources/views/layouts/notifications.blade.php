{{-- Hệ Thống Thông Báo Nổi (Floating Toast & Push Notification System) --}}
@auth
<!-- KHU VỰC THÔNG BÁO NỔI (FLOATING TOASTS STACK) -->
<div id="floating-notifications-container" 
     class="fixed bottom-20 left-3 right-3 sm:left-auto sm:right-6 sm:bottom-6 z-[99999] flex flex-col-reverse gap-3 max-w-none sm:max-w-md sm:w-full pointer-events-none select-none"
     x-data>
    <template x-for="item in $store.notifications.toasts" :key="item.id">
        <div class="pointer-events-auto bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border-2 transition-all duration-300 overflow-hidden transform"
             :class="{
                 'border-amber-500 ring-4 ring-amber-500/25 shadow-amber-500/20': item.type === 'delivery_discrepancy',
                 'border-teal-500 ring-4 ring-teal-500/25 shadow-teal-500/20': item.type === 'delivery_discrepancy_response',
                 'border-purple-500 ring-4 ring-purple-500/20 shadow-purple-500/10': ['supplier_order_approval', 'debt_handover_pending_sale'].includes(item.type),
                 'border-emerald-500 ring-4 ring-emerald-500/20 shadow-emerald-500/10': ['completed', 'supplier_order_approved', 'debt_handover_confirmed'].includes(item.type),
                 'border-rose-500 ring-4 ring-rose-500/20 shadow-rose-500/10': item.type === 'debt_handover_rejected',
                 'border-sky-500 ring-4 ring-sky-500/20 shadow-sky-500/10': item.type === 'installation_assigned',
                 'border-amber-500 ring-4 ring-amber-500/20 shadow-amber-500/10': ['repair_assigned', 'warehouse_intake', 'debt_handover_pending_accountant'].includes(item.type),
                 'border-indigo-500 ring-4 ring-indigo-500/20 shadow-indigo-500/10': !['delivery_discrepancy', 'delivery_discrepancy_response', 'installation_assigned', 'repair_assigned', 'warehouse_intake', 'completed', 'supplier_order_approval', 'supplier_order_approved', 'debt_handover_pending_accountant', 'debt_handover_pending_sale', 'debt_handover_confirmed', 'debt_handover_rejected'].includes(item.type)
             }"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-90"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-12 scale-95">

            <!-- Toast Header -->
            <div class="px-4 py-3 flex items-center justify-between border-b"
                 :class="{
                     'bg-gradient-to-r from-amber-600 via-orange-600 to-rose-600 text-white border-amber-600': item.type === 'delivery_discrepancy',
                     'bg-gradient-to-r from-teal-600 via-emerald-600 to-teal-700 text-white border-teal-600': item.type === 'delivery_discrepancy_response',
                     'bg-gradient-to-r from-purple-600 to-indigo-700 text-white border-purple-600': ['supplier_order_approval', 'debt_handover_pending_sale'].includes(item.type),
                     'bg-gradient-to-r from-emerald-600 to-teal-700 text-white border-emerald-600': ['completed', 'supplier_order_approved', 'debt_handover_confirmed'].includes(item.type),
                     'bg-gradient-to-r from-rose-600 to-red-700 text-white border-rose-600': item.type === 'debt_handover_rejected',
                     'bg-gradient-to-r from-sky-600 to-blue-700 text-white border-sky-600': item.type === 'installation_assigned',
                     'bg-gradient-to-r from-amber-600 to-orange-700 text-white border-amber-600': ['repair_assigned', 'warehouse_intake', 'debt_handover_pending_accountant'].includes(item.type),
                     'bg-gradient-to-r from-indigo-600 to-purple-700 text-white border-indigo-600': !['delivery_discrepancy', 'delivery_discrepancy_response', 'installation_assigned', 'repair_assigned', 'warehouse_intake', 'completed', 'supplier_order_approval', 'supplier_order_approved', 'debt_handover_pending_accountant', 'debt_handover_pending_sale', 'debt_handover_confirmed', 'debt_handover_rejected'].includes(item.type)
                 }">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs shadow-xs animate-bounce">
                        <i :class="$store.notifications.getIconClass(item.type)"></i>
                    </span>
                    <span class="font-extrabold text-xs tracking-tight truncate flex-1" x-text="item.title"></span>
                </div>
                <button type="button" @click="$store.notifications.removeToast(item.id)" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/20 transition cursor-pointer" title="Đóng">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Toast Body -->
            <div class="p-4 space-y-2.5 text-xs text-slate-700 bg-white">
                <p class="font-bold text-slate-800 leading-snug" x-text="item.message"></p>

                <!-- Thông tin thẻ nhanh KTV hoặc Đơn NCC nếu có data -->
                <template x-if="item.data">
                    <div class="bg-slate-50/90 rounded-xl p-2.5 border border-slate-200/80 space-y-1.5 text-[11px]">
                        <!-- Phản ánh lệch hàng từ xe (Thừa / Thiếu) -->
                        <template x-if="item.type === 'delivery_discrepancy' || item.type === 'delivery_discrepancy_response' || (item.data && item.data.discrepancy_id)">
                            <div class="space-y-1.5 bg-amber-50/70 p-2 rounded-lg border border-amber-200/70">
                                <div class="flex items-center justify-between gap-1 text-slate-800">
                                    <span class="flex items-center gap-1.5 font-bold text-amber-900">
                                        <i class="fa-solid fa-truck text-amber-600"></i>
                                        <span x-text="item.data.vehicle_plate || 'Xe giao hàng'"></span>
                                    </span>
                                    <template x-if="item.data.order_code">
                                        <span class="font-mono font-black text-slate-800 bg-white px-1.5 py-0.5 rounded border border-amber-300" x-text="'#' + item.data.order_code"></span>
                                    </template>
                                </div>
                                <template x-if="item.data.product_name">
                                    <div class="flex items-center justify-between text-slate-700 font-semibold gap-2">
                                        <span class="truncate flex-1" x-text="item.data.product_name"></span>
                                        <template x-if="item.data.diff_qty !== undefined">
                                            <span :class="Number(item.data.diff_qty) < 0 ? 'text-rose-600 font-black' : 'text-blue-600 font-black'"
                                                  x-text="(Number(item.data.diff_qty) > 0 ? '+' : '') + item.data.diff_qty + ' ' + (item.data.unit || '')"></span>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="item.data.checker_name">
                                    <div class="text-[10.5px] text-slate-600">
                                        <span>Người bốc: </span><strong class="text-amber-800" x-text="item.data.checker_name"></strong>
                                    </div>
                                </template>
                                <template x-if="item.data.feedback">
                                    <div class="text-teal-900 bg-teal-50 p-1.5 rounded border border-teal-200 text-[11px]">
                                        <span class="font-bold">Phản hồi: </span><span x-text="item.data.feedback"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Biên bản bàn giao công nợ -->
                        <template x-if="item.data && item.data.handover_code">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-1 text-slate-800">
                                    <span class="flex items-center gap-1.5 font-bold">
                                        <i class="fa-solid fa-file-invoice-dollar text-purple-600"></i>
                                        <span x-text="item.data.handover_code"></span>
                                    </span>
                                    <template x-if="item.data.amount !== undefined">
                                        <span class="font-mono font-black text-rose-700 text-xs" x-text="new Intl.NumberFormat('vi-VN').format(item.data.amount) + ' đ'"></span>
                                    </template>
                                </div>
                                <template x-if="item.data.from_user_name">
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span>Tài xế giao:</span>
                                        <span class="font-bold text-slate-800" x-text="item.data.from_user_name"></span>
                                    </div>
                                </template>
                                <template x-if="item.data.documents_count !== undefined">
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span>Chứng từ:</span>
                                        <span class="font-bold text-sky-700" x-text="item.data.documents_count + ' ảnh đính kèm'"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Đơn đặt hàng NCC -->
                        <template x-if="item.data.supplier_name">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-1 text-slate-800">
                                    <span class="flex items-center gap-1.5 font-bold">
                                        <i class="fa-solid fa-truck-field text-purple-600"></i>
                                        <span x-text="item.data.supplier_name"></span>
                                    </span>
                                    <template x-if="item.data.order_code">
                                        <span class="font-mono font-black text-slate-900 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200" x-text="item.data.order_code"></span>
                                    </template>
                                </div>
                                <template x-if="item.data.payable_amount !== undefined">
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span>Cần thanh toán:</span>
                                        <span class="text-emerald-700 font-black text-xs" x-text="new Intl.NumberFormat('vi-VN').format(item.data.payable_amount) + ' đ'"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Máy lọc nước -->
                        <template x-if="item.data.customer_name">
                            <div class="flex items-center justify-between gap-1 text-slate-800">
                                <span class="flex items-center gap-1.5 font-bold">
                                    <i class="fa-solid fa-user text-slate-400"></i>
                                    <span x-text="item.data.customer_name"></span>
                                </span>
                                <template x-if="item.data.customer_phone">
                                    <a :href="'tel:' + item.data.customer_phone" class="text-sky-600 font-bold hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-phone text-[10px]"></i>
                                        <span x-text="item.data.customer_phone"></span>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <template x-if="item.data.customer_address">
                            <div class="flex items-start gap-1.5 text-slate-600 line-clamp-2">
                                <i class="fa-solid fa-location-dot text-rose-500 mt-0.5 shrink-0"></i>
                                <span x-text="item.data.customer_address"></span>
                            </div>
                        </template>

                        <template x-if="item.data.machine_model">
                            <div class="flex items-center gap-1.5 text-cyan-800 font-extrabold">
                                <i class="fa-solid fa-faucet-drip text-cyan-600"></i>
                                <span x-text="item.data.machine_model"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Actions -->
                <div class="pt-1 flex items-center justify-between gap-2">
                    <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap shrink-0" x-text="item.time_ago || 'Vừa xong'"></span>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" 
                                @click="$store.notifications.removeToast(item.id)" 
                                class="px-2.5 py-1 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg font-bold text-xs transition cursor-pointer whitespace-nowrap shrink-0">
                            Để sau
                        </button>
                        <template x-if="item.action_url">
                            <a :href="item.action_url" 
                               @click="$store.notifications.markAndNavigate(item)"
                               class="px-3.5 py-1.5 rounded-xl font-black text-xs text-white transition shadow-sm flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0"
                               :class="{
                                   'bg-purple-600 hover:bg-purple-700 shadow-purple-600/30': item.type === 'supplier_order_approval',
                                   'bg-sky-600 hover:bg-sky-700 shadow-sky-600/30': item.type === 'installation_assigned',
                                   'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 shadow-amber-500/30 text-slate-950 font-black': item.type === 'warehouse_intake',
                                   'bg-amber-600 hover:bg-amber-700 shadow-amber-600/30': item.type === 'repair_assigned',
                                   'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30': ['completed', 'supplier_order_approved', 'order_return_approved'].includes(item.type),
                                   'bg-slate-800 hover:bg-slate-900': !['installation_assigned', 'repair_assigned', 'warehouse_intake', 'completed', 'supplier_order_approval', 'supplier_order_approved', 'order_return_approved'].includes(item.type)
                               }">
                                <span x-text="item.type === 'supplier_order_approval' ? 'Xem & Duyệt Đơn' : (item.type === 'supplier_order_approved' ? 'Xem Đơn' : (item.type === 'warehouse_intake' ? 'Duyệt Nhập Kho' : 'Xem & Nhận đơn'))"></span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Progress Bar đếm ngược tự ẩn -->
            <div class="w-full bg-slate-100 h-1 overflow-hidden">
                <div class="h-full transition-all duration-100 ease-linear"
                     :class="{
                         'bg-purple-500': item.type === 'supplier_order_approval',
                         'bg-sky-500': item.type === 'installation_assigned',
                         'bg-amber-500': ['repair_assigned', 'warehouse_intake'].includes(item.type),
                         'bg-emerald-500': ['completed', 'supplier_order_approved', 'order_return_approved'].includes(item.type),
                         'bg-indigo-500': !['installation_assigned', 'repair_assigned', 'warehouse_intake', 'completed', 'supplier_order_approval', 'supplier_order_approved', 'order_return_approved'].includes(item.type)
                     }"
                     :style="'width: ' + (item.progressPercent || 100) + '%'"></div>
            </div>

        </div>
    </template>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('notifications', {
        unreadCount: 0,
        notifications: [],
        toasts: [],
        openDropdown: false,
        loadingList: false,
        pollTimer: null,
        csrfToken: '',

        init() {
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            // Kiểm tra thông báo nổi lưu từ trang trước (sessionStorage)
            try {
                const pending = sessionStorage.getItem('pending_toast');
                if (pending) {
                    sessionStorage.removeItem('pending_toast');
                    const item = JSON.parse(pending);
                    setTimeout(() => {
                        this.addToast({
                            id: 'local-' + Date.now(),
                            ...item,
                            time_ago: 'Vừa xong'
                        });
                    }, 400);
                }
            } catch (e) {}

            // Lần thăm dò đầu tiên sau 1.2s khi load trang
            setTimeout(() => {
                this.poll();
            }, 1200);

            // Bắt đầu chu kỳ thăm dò định kỳ mỗi 6 giây
            this.startPolling(6000);

            // Tối ưu khi người dùng chuyển tab hoặc ẩn trình duyệt
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.startPolling(30000); // Tab ẩn: giảm tải xuống 30s
                } else {
                    this.poll(); // Quay lại tab: kiểm tra ngay
                    this.startPolling(6000);
                }
            });
        },

        startPolling(interval) {
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.pollTimer = setInterval(() => {
                this.poll();
            }, interval);
        },

        async poll() {
            try {
                const res = await fetch('/api/notifications/poll', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.success) {
                    this.unreadCount = data.unread_count || 0;

                    // Nếu có popup nổi mới trả về
                    if (data.popups && data.popups.length > 0) {
                        data.popups.forEach(popup => {
                            this.addToast(popup);
                        });
                    }
                }
            } catch (e) {
                // Ignore silent network failure in background
            }
        },

        async loadNotificationsList() {
            this.loadingList = true;
            try {
                const res = await fetch('/api/notifications', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.success) {
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                }
            } catch (e) {
                console.error('Error fetching notifications list', e);
            } finally {
                this.loadingList = false;
            }
        },

        toggleDropdown() {
            this.openDropdown = !this.openDropdown;
            if (this.openDropdown) {
                this.loadNotificationsList();
            }
        },

        addToast(item) {
            // Tránh trùng lặp toast cùng id
            if (this.toasts.some(t => t.id === item.id)) return;

            const duration = 12000; // 12 giây tự động tắt
            const startTime = Date.now();
            const toastItem = {
                ...item,
                progressPercent: 100,
                intervalId: null,
            };

            this.toasts.unshift(toastItem);
            this.playNotificationSound();

            // Progress bar interval
            toastItem.intervalId = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const remainPercent = Math.max(0, 100 - (elapsed / duration) * 100);
                toastItem.progressPercent = remainPercent;

                if (remainPercent <= 0) {
                    clearInterval(toastItem.intervalId);
                    this.removeToast(item.id);
                }
            }, 100);
        },

        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                if (this.toasts[index].intervalId) {
                    clearInterval(this.toasts[index].intervalId);
                }
                this.toasts.splice(index, 1);
            }
        },

        async markAsRead(id) {
            try {
                const res = await fetch('/api/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.unreadCount = data.unread_count || 0;
                    const notif = this.notifications.find(n => n.id === id);
                    if (notif) notif.is_read = true;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async markAllAsRead() {
            try {
                const res = await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.unreadCount = 0;
                    this.notifications.forEach(n => n.is_read = true);
                }
            } catch (e) {
                console.error(e);
            }
        },

        markAndNavigate(item) {
            this.markAsRead(item.id);
            this.removeToast(item.id);
            this.openDropdown = false;
        },

        getIconClass(type) {
            switch (type) {
                case 'delivery_discrepancy': return 'fa-solid fa-scale-unbalanced text-amber-300';
                case 'delivery_discrepancy_response': return 'fa-solid fa-comments text-teal-300';
                case 'supplier_order_approval': return 'fa-solid fa-cart-shopping';
                case 'supplier_order_approved': return 'fa-solid fa-check-double';
                case 'installation_assigned': return 'fa-solid fa-screwdriver-wrench';
                case 'repair_assigned': return 'fa-solid fa-wrench';
                case 'completed': return 'fa-solid fa-circle-check';
                case 'status_update': return 'fa-solid fa-arrows-rotate';
                case 'warehouse_intake': return 'fa-solid fa-boxes-packing';
                case 'order_return_approved': return 'fa-solid fa-warehouse';
                case 'debt_handover_pending_accountant': return 'fa-solid fa-file-invoice-dollar';
                case 'debt_handover_pending_sale': return 'fa-solid fa-hand-holding-dollar';
                case 'debt_handover_confirmed': return 'fa-solid fa-circle-check';
                case 'debt_handover_rejected': return 'fa-solid fa-circle-xmark';
                default: return 'fa-solid fa-bell';
            }
        },

        getTypeBadge(type) {
            switch (type) {
                case 'delivery_discrepancy': return { text: 'Lệch hàng từ xe', class: 'bg-amber-100 text-amber-900 border border-amber-300' };
                case 'delivery_discrepancy_response': return { text: 'Phản hồi lệch hàng', class: 'bg-teal-100 text-teal-900 border border-teal-300' };
                case 'supplier_order_approval': return { text: 'Duyệt đặt NCC', class: 'bg-purple-100 text-purple-800' };
                case 'supplier_order_approved': return { text: 'Đã duyệt NCC', class: 'bg-emerald-100 text-emerald-800' };
                case 'installation_assigned': return { text: 'Lắp đặt', class: 'bg-sky-100 text-sky-800' };
                case 'repair_assigned': return { text: 'Sửa chữa', class: 'bg-amber-100 text-amber-800' };
                case 'completed': return { text: 'Hoàn thành', class: 'bg-emerald-100 text-emerald-800' };
                case 'warehouse_intake': return { text: 'Nhập hàng hoàn', class: 'bg-amber-100 text-amber-800' };
                case 'order_return_approved': return { text: 'Đã hoàn kho', class: 'bg-emerald-100 text-emerald-800' };
                case 'debt_handover_pending_accountant': return { text: 'Duyệt nợ', class: 'bg-amber-100 text-amber-800' };
                case 'debt_handover_pending_sale': return { text: 'Nhận nợ', class: 'bg-purple-100 text-purple-800' };
                case 'debt_handover_confirmed': return { text: 'Đã nhận nợ', class: 'bg-emerald-100 text-emerald-800' };
                case 'debt_handover_rejected': return { text: 'Từ chối nợ', class: 'bg-rose-100 text-rose-800' };
                default: return { text: 'Hệ thống', class: 'bg-indigo-100 text-indigo-800' };
            }
        },

        playNotificationSound() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const audioCtx = new AudioContext();
                const now = audioCtx.currentTime;

                // Tinh tế: Âm chime đôi tinh gọn sang trọng
                // Tone 1: E6 (~1318.5 Hz)
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(1318.5, now);
                gain1.gain.setValueAtTime(0.12, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start(now);
                osc1.stop(now + 0.3);

                // Tone 2: G#6 (~1661.2 Hz)
                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(1661.2, now + 0.1);
                gain2.gain.setValueAtTime(0.18, now + 0.1);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start(now + 0.1);
                osc2.stop(now + 0.5);
            } catch (e) {
                // AudioContext có thể bị chặn nếu user chưa từng click chuột trên trang
            }
        }
    });
});

// Global API hỗ trợ gọi thông báo nổi từ bất kỳ trang nào
window.showFloatingNotification = function(options) {
    if (window.Alpine && window.Alpine.store('notifications')) {
        window.Alpine.store('notifications').addToast({
            id: 'temp-' + Date.now(),
            title: options.title || 'Thông báo hệ thống',
            message: options.message || '',
            action_url: options.url || options.action_url || null,
            type: options.type || 'general',
            data: options.data || null,
            time_ago: 'Vừa xong'
        });
    }
};
</script>
@endauth
