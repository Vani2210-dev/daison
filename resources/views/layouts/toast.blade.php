{{-- ====================================================================
     HỆ THỐNG THÔNG BÁO NỔI TOÀN CỤC (GLOBAL FLOATING TOAST SYSTEM)
     Tự động xử lý Flash session, API JS, và thay thế alert() của trình duyệt
     ==================================================================== --}}

<!-- Container chứa các thông báo nổi ở góc trên bên phải màn hình -->
<div id="erp-global-toast-container" 
     class="fixed top-4 left-3 right-3 sm:left-auto sm:right-5 z-[9999999] pointer-events-none flex flex-col gap-2.5 max-w-none sm:max-w-md sm:w-full select-none"
     aria-live="polite">
</div>

<style>
@keyframes erpToastSlideIn {
    0% {
        opacity: 0;
        transform: translateY(-24px) scale(0.94);
    }
    70% {
        opacity: 1;
        transform: translateY(4px) scale(1.01);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
@keyframes erpToastSlideOut {
    0% {
        opacity: 1;
        transform: translateY(0) scale(1);
        max-height: 140px;
        margin-bottom: 0.625rem;
    }
    100% {
        opacity: 0;
        transform: translateY(-20px) scale(0.92);
        max-height: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
}
.erp-toast-item {
    animation: erpToastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.erp-toast-item.dismissing {
    animation: erpToastSlideOut 0.28s cubic-bezier(0.4, 0, 1, 1) forwards !important;
}
</style>

<script>
(function() {
    'use strict';

    const TOAST_THEMES = {
        success: {
            border: 'border-emerald-500/80',
            bg: 'bg-white/95',
            ring: 'ring-4 ring-emerald-500/15',
            shadow: 'shadow-2xl shadow-emerald-600/20',
            iconBg: 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white',
            icon: 'fa-solid fa-circle-check',
            titleColor: 'text-emerald-950',
            textColor: 'text-emerald-900',
            barColor: 'bg-gradient-to-r from-emerald-500 to-teal-500',
            defaultTitle: 'Thành công'
        },
        error: {
            border: 'border-rose-500/80',
            bg: 'bg-white/95',
            ring: 'ring-4 ring-rose-500/15',
            shadow: 'shadow-2xl shadow-rose-600/20',
            iconBg: 'bg-gradient-to-br from-rose-500 to-red-600 text-white',
            icon: 'fa-solid fa-circle-xmark',
            titleColor: 'text-rose-950',
            textColor: 'text-rose-900',
            barColor: 'bg-gradient-to-r from-rose-500 to-red-500',
            defaultTitle: 'Thông báo lỗi'
        },
        warning: {
            border: 'border-amber-500/80',
            bg: 'bg-white/95',
            ring: 'ring-4 ring-amber-500/15',
            shadow: 'shadow-2xl shadow-amber-600/20',
            iconBg: 'bg-gradient-to-br from-amber-500 to-orange-600 text-white',
            icon: 'fa-solid fa-triangle-exclamation',
            titleColor: 'text-amber-950',
            textColor: 'text-amber-900',
            barColor: 'bg-gradient-to-r from-amber-500 to-orange-500',
            defaultTitle: 'Cảnh báo'
        },
        info: {
            border: 'border-sky-500/80',
            bg: 'bg-white/95',
            ring: 'ring-4 ring-sky-500/15',
            shadow: 'shadow-2xl shadow-sky-600/20',
            iconBg: 'bg-gradient-to-br from-sky-500 to-indigo-600 text-white',
            icon: 'fa-solid fa-circle-info',
            titleColor: 'text-sky-950',
            textColor: 'text-sky-900',
            barColor: 'bg-gradient-to-r from-sky-500 to-indigo-500',
            defaultTitle: 'Thông báo'
        }
    };

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function playChime(type) {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            const freq = type === 'error' ? 380 : (type === 'warning' ? 520 : 660);
            osc.frequency.setValueAtTime(freq, now);
            gain.gain.setValueAtTime(0.08, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.28);
        } catch (e) {}
    }

    function createFloatingToast(message, type = 'success', title = null, duration = 4500) {
        const container = document.getElementById('erp-global-toast-container');
        if (!container) return;

        const theme = TOAST_THEMES[type] || TOAST_THEMES.info;
        const toastTitle = title || theme.defaultTitle;

        const toastEl = document.createElement('div');
        toastEl.className = `erp-toast-item pointer-events-auto rounded-2xl border ${theme.border} ${theme.bg} ${theme.ring} ${theme.shadow} backdrop-blur-md overflow-hidden relative flex flex-col p-3.5 transition-all`;

        toastEl.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl ${theme.iconBg} flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                    <i class="${theme.icon} text-sm"></i>
                </div>
                <div class="flex-1 min-w-0 pr-2">
                    <div class="font-black text-xs ${theme.titleColor} tracking-tight">${escapeHtml(toastTitle)}</div>
                    <div class="text-xs font-semibold ${theme.textColor} mt-0.5 leading-relaxed break-words">${escapeHtml(message)}</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 -mr-1 -mt-1 rounded-lg transition-colors cursor-pointer shrink-0" title="Đóng thông báo">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
            <div class="w-full bg-slate-100/80 h-1 rounded-full mt-2.5 overflow-hidden">
                <div class="toast-progress-bar h-full ${theme.barColor} transition-all" style="width: 100%;"></div>
            </div>
        `;

        const closeBtn = toastEl.querySelector('button');
        const progressBar = toastEl.querySelector('.toast-progress-bar');

        let remainingTime = duration;
        const step = 50;
        let timer = null;
        let isPaused = false;
        let isDismissed = false;

        function dismiss() {
            if (isDismissed) return;
            isDismissed = true;
            if (timer) clearInterval(timer);
            toastEl.classList.add('dismissing');
            setTimeout(() => {
                if (toastEl.parentElement) toastEl.remove();
            }, 280);
        }

        closeBtn.addEventListener('click', dismiss);
        toastEl.addEventListener('mouseenter', () => { isPaused = true; });
        toastEl.addEventListener('mouseleave', () => { isPaused = false; });

        if (duration > 0) {
            timer = setInterval(() => {
                if (!isPaused) {
                    remainingTime -= step;
                    const pct = Math.max(0, (remainingTime / duration) * 100);
                    if (progressBar) progressBar.style.width = pct + '%';
                    if (remainingTime <= 0) {
                        dismiss();
                    }
                }
            }, step);
        } else {
            if (progressBar && progressBar.parentElement) {
                progressBar.parentElement.style.display = 'none';
            }
        }

        // Tối đa 3 toast cùng lúc trên màn hình, tự đẩy cái cũ ra
        while (container.children.length >= 3) {
            container.children[0].remove();
        }

        container.appendChild(toastEl);
        playChime(type);
    }

    // Cung cấp API toàn cục cho toàn bộ hệ thống
    window.showToast = function(msg, type = 'success', title = null, duration = 4500) {
        createFloatingToast(msg, type, title, duration);
    };

    window.toast = {
        success: (msg, title, duration) => createFloatingToast(msg, 'success', title || 'Thành công', duration || 4500),
        error: (msg, title, duration) => createFloatingToast(msg, 'error', title || 'Thông báo lỗi', duration || 5500),
        warning: (msg, title, duration) => createFloatingToast(msg, 'warning', title || 'Cảnh báo', duration || 5000),
        info: (msg, title, duration) => createFloatingToast(msg, 'info', title || 'Thông báo', duration || 4500)
    };

    // Override window.alert để tự động chuyển tất cả thông báo JS thành toast nổi
    const _nativeAlert = window.alert;
    window.alert = function(message) {
        if (!message) return;
        const text = String(message);
        const lower = text.toLowerCase();
        let type = 'warning';
        let title = 'Thông báo hệ thống';

        if (lower.includes('thành công') || lower.includes('hoàn tất') || lower.includes('đã lưu') || lower.includes('đã tạo') || lower.includes('đã xóa') || lower.includes('đã hủy') || lower.includes('đã duyệt') || lower.includes('đã cập nhật')) {
            type = 'success';
            title = 'Thao tác thành công';
        } else if (lower.includes('lỗi') || lower.includes('thất bại') || lower.includes('không thể') || lower.includes('sai') || lower.includes('từ chối') || lower.includes('vượt')) {
            type = 'error';
            title = 'Thông báo lỗi';
        }

        createFloatingToast(text, type, title, 5000);
    };

})();
</script>

{{-- Tự động kích hoạt Toast nổi khi có Flash Session từ Laravel Controller --}}
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.showToast === 'function') {
            window.showToast(@json(session('success')), 'success', 'Thành công', 4500);
        }
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.showToast === 'function') {
            window.showToast(@json(session('error')), 'error', 'Thông báo lỗi', 5500);
        }
    });
</script>
@endif

@if(session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.showToast === 'function') {
            window.showToast(@json(session('warning')), 'warning', 'Cảnh báo', 5000);
        }
    });
</script>
@endif

@if(session('status'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.showToast === 'function') {
            window.showToast(@json(session('status')), 'info', 'Thông báo hệ thống', 4500);
        }
    });
</script>
@endif

@if(session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.showToast === 'function') {
            window.showToast(@json(session('info')), 'info', 'Thông tin', 4500);
        }
    });
</script>
@endif
