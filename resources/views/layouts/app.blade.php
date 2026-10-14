<!DOCTYPE html>
<html lang="vi" class="h-full bg-[#f0f2f5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ Thống Quản Trị ERP Điện Nước & Ống Nhựa PVC')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Be Vietnam Pro"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Global Money Input & Display Formatting Utilities -->
    <script>
        window.formatMoney = function(val) {
            if (val === null || val === undefined || val === '') return '0';
            const num = Number(val);
            if (isNaN(num)) return '0';
            return num.toLocaleString('vi-VN', { maximumFractionDigits: 2 });
        };

        window.formatCurrency = function(val) {
            return window.formatMoney(val) + ' đ';
        };

        window.parseMoney = function(val) {
            if (typeof val === 'number') return val;
            const digits = String(val || '').replace(/[^\d.-]/g, '');
            return digits ? parseFloat(digits) : 0;
        };

        // Alpine.js Directives: x-money & x-number
        document.addEventListener('alpine:init', () => {
            if (window.Alpine) {
                // Magic helpers
                Alpine.magic('money', () => (val) => window.formatMoney(val));
                Alpine.magic('number', () => (val) => window.formatMoney(val));

                // 1. Directive: x-money="propertyName" (Cho tiền tệ & số nguyên)
                Alpine.directive('money', (el, { expression }, { evaluateLater, effect }) => {
                    if (el.type === 'number') {
                        el.type = 'text';
                    }
                    if (!el.getAttribute('inputmode')) {
                        el.setAttribute('inputmode', 'numeric');
                    }

                    const getModel = evaluateLater(expression);
                    const setModel = evaluateLater(`${expression} = $event`);

                    // Reactive update from Model -> DOM Input
                    effect(() => {
                        getModel(val => {
                            if (document.activeElement === el) return;
                            if (val === null || val === undefined || val === '') {
                                el.value = '';
                                return;
                            }
                            const targetNum = Math.round(Number(val) || 0);
                            el.value = targetNum > 0 
                                ? String(targetNum).replace(/\B(?=(\d{3})+(?!\d))/g, '.') 
                                : (targetNum === 0 && val !== '' ? '0' : '');
                        });
                    });

                    // Input -> Format with dots and update Alpine Model
                    el.addEventListener('input', () => {
                        const cursor = el.selectionStart;
                        const oldLength = el.value.length;
                        const digits = el.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
                        const num = digits ? parseInt(digits, 10) : 0;
                        const formatted = digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                        el.value = formatted;

                        const diff = formatted.length - oldLength;
                        const newPos = Math.max(0, cursor + diff);
                        el.setSelectionRange(newPos, newPos);

                        setModel(undefined, { scope: { $event: num } });
                    });

                    // Keyboard ArrowUp / ArrowDown support for increment/decrement
                    el.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                            e.preventDefault();
                            const isUp = e.key === 'ArrowUp';
                            let cur = parseInt(String(el.value).replace(/\D/g, '') || '0', 10);
                            let step = 1000;
                            if (el.dataset.step) {
                                step = parseInt(el.dataset.step, 10) || 1000;
                            } else if (cur >= 100000) {
                                step = 10000;
                            } else if (cur >= 10000) {
                                step = 1000;
                            } else {
                                step = 500;
                            }
                            let next = isUp ? cur + step : Math.max(0, cur - step);
                            el.value = next > 0 ? String(next).replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '0';
                            setModel(undefined, { scope: { $event: next } });
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });

                    el.addEventListener('blur', () => {
                        getModel(val => {
                            if (val === null || val === undefined || val === '') {
                                el.value = '';
                                return;
                            }
                            const num = Math.round(Number(val) || 0);
                            el.value = num > 0 
                                ? String(num).replace(/\B(?=(\d{3})+(?!\d))/g, '.') 
                                : (num === 0 && val !== '' ? '0' : '');
                        });
                    });
                });

                // 2. Directive: x-number="propertyName" (Cho số lượng & số có thể thập phân)
                Alpine.directive('number', (el, { expression }, { evaluateLater, effect }) => {
                    if (el.type === 'number') {
                        el.type = 'text';
                    }
                    if (!el.getAttribute('inputmode')) {
                        el.setAttribute('inputmode', 'decimal');
                    }

                    const getModel = evaluateLater(expression);
                    const setModel = evaluateLater(`${expression} = $event`);

                    function formatNumberDisplay(val) {
                        if (val === null || val === undefined || val === '') return '';
                        const str = String(val);
                        const parts = str.split('.');
                        const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        return parts.length > 1 ? intPart + ',' + parts[1] : intPart;
                    }

                    effect(() => {
                        getModel(val => {
                            if (document.activeElement === el) return;
                            el.value = formatNumberDisplay(val);
                        });
                    });

                    // Keyboard ArrowUp / ArrowDown support for increment/decrement
                    el.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                            e.preventDefault();
                            const isUp = e.key === 'ArrowUp';
                            let raw = String(el.value).replace(/\./g, '').replace(',', '.');
                            let cur = parseFloat(raw) || 0;
                            let step = 1;
                            if (el.dataset.step) {
                                step = parseFloat(el.dataset.step) || 1;
                            }
                            let next = isUp ? cur + step : Math.max(0, cur - step);
                            next = Math.round(next * 100) / 100;
                            el.value = formatNumberDisplay(next);
                            setModel(undefined, { scope: { $event: next } });
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });

                    el.addEventListener('input', () => {
                        const cursor = el.selectionStart;
                        const oldLength = el.value.length;
                        
                        let val = el.value;
                        let hasComma = val.includes(',') || val.includes('.');
                        let raw = val.replace(/\./g, '').replace(',', '.');
                        let parts = raw.split('.');

                        let intDigits = parts[0].replace(/\D/g, '').replace(/^0+(?=\d)/, '');
                        let formattedInt = intDigits ? intDigits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : (hasComma ? '0' : '');
                        
                        let formatted = formattedInt;
                        let num = intDigits ? parseInt(intDigits, 10) : 0;

                        if (parts.length > 1) {
                            let decDigits = parts[1].replace(/\D/g, '');
                            formatted = (formattedInt || '0') + ',' + decDigits;
                            num = parseFloat((intDigits || '0') + '.' + decDigits);
                        }

                        el.value = formatted;
                        const diff = formatted.length - oldLength;
                        const newPos = Math.max(0, cursor + diff);
                        el.setSelectionRange(newPos, newPos);

                        setModel(undefined, { scope: { $event: num } });
                    });

                    el.addEventListener('blur', () => {
                        getModel(val => {
                            el.value = formatNumberDisplay(val);
                        });
                    });
                });
            }
        });
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Be Vietnam Pro', system-ui, -apple-system, sans-serif; background-color: #f0f2f5; color: #1e293b; }
        * { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* CSS HOVER FOR DROPDOWN SUBMENU SO IT NEVER FAILS */
        .nav-item-group:hover .nav-submenu {
            display: block !important;
        }

        /* Drag-to-scroll horizontal tables */
        .is-dragging-scroll {
            cursor: grabbing !important;
            user-select: none !important;
        }
        .is-dragging-scroll * {
            cursor: grabbing !important;
            user-select: none !important;
            pointer-events: none !important;
        }
        .overflow-x-auto, .table-scroll-container {
            -webkit-overflow-scrolling: touch;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col antialiased text-slate-800 bg-[#f0f2f5] font-sans" x-data="{ openMenu: null }">

    <!-- TOP HEADER SECTION (IDENTICAL TO REACT ERP) -->
    <header class="w-full bg-white border-b border-slate-200/90 shadow-xs font-sans select-none sticky top-0 z-50">
        
        <!-- Top Bar: Corporate Identity & User Profile -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#1a3a68] text-white px-4 py-2 flex flex-wrap items-center justify-between gap-3 text-xs">
            
            <!-- Left: Company Logo & Brand -->
            <div class="flex items-center gap-3.5">
                <a href="{{ url('/dashboard') }}" class="flex items-center gap-2.5 bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20 shadow-xs hover:bg-white/15 transition-all">
                    <div class="h-9 px-2 rounded-xl bg-white flex items-center justify-center shadow-xs shrink-0 ring-1 ring-white/30">
                        <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="Logo Đại Sơn" class="h-7 w-auto object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-extrabold text-white tracking-wide text-xs sm:text-sm flex items-center gap-1.5">
                            ĐẠI ĐẠI SƠN ERP
                        </span>
                        <span class="text-[10px] text-slate-300 font-medium tracking-tight">
                            Hệ Thống Phân Phối Máy Lọc Nước & Thiết Bị Xử Lý Nước
                        </span>
                    </div>
                </a>

                <div class="hidden lg:block h-6 w-px bg-white/20"></div>

                <!-- Branch Switcher -->
                <div class="hidden md:flex items-center gap-1.5 bg-white/10 backdrop-blur-md text-white border border-white/20 rounded-lg px-2.5 py-1 text-xs">
                    <i class="fa-solid fa-location-dot text-sky-400 shrink-0 text-xs"></i>
                    <span class="text-slate-300 font-semibold text-[11px]">Trụ sở:</span>
                    <select class="bg-transparent font-bold text-sky-200 focus:outline-none cursor-pointer pr-1 text-xs">
                        <option value="br-1" class="bg-slate-900 text-white">Công ty TNHH Máy Lọc Nước Đại Sơn</option>
                        <option value="br-2" class="bg-slate-900 text-white">Chi nhánh Mai Văn Quang (Kho xe lưu động)</option>
                        <option value="br-3" class="bg-slate-900 text-white">Chi nhánh Dương Xuân Hải (Kho Hải)</option>
                    </select>
                </div>
            </div>

            <!-- Right: User Profile & Quick Actions -->
            <div class="flex items-center gap-2 sm:gap-3 text-xs font-medium">
                @if(Auth::check())
                    <!-- NOTIFICATION BELL & DROPDOWN -->
                    <div class="relative" x-data @click.away="$store.notifications ? ($store.notifications.openDropdown = false) : null">
                        <button type="button" 
                                @click="$store.notifications ? $store.notifications.toggleDropdown() : null"
                                class="relative p-1.5 px-2.5 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-slate-200 hover:text-white transition-all cursor-pointer flex items-center gap-1.5 text-xs font-semibold select-none"
                                :class="$store.notifications && $store.notifications.openDropdown ? 'ring-2 ring-sky-400 bg-white/25 text-white' : ''"
                                title="Thông báo hệ thống">
                            <i class="fa-solid fa-bell text-xs" :class="$store.notifications && $store.notifications.unreadCount > 0 ? 'text-amber-300 animate-bounce' : 'text-slate-300'"></i>
                            <span class="hidden md:inline">Thông báo</span>
                            <template x-if="$store.notifications && $store.notifications.unreadCount > 0">
                                <span class="px-1.5 py-0.2 rounded-full bg-rose-500 text-white font-black text-[10px] min-w-[18px] text-center shadow-xs animate-pulse" 
                                      x-text="$store.notifications.unreadCount > 99 ? '99+' : $store.notifications.unreadCount"></span>
                            </template>
                        </button>

                        <!-- DROPDOWN PANEL -->
                        <div x-show="$store.notifications && $store.notifications.openDropdown" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white text-slate-800 rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-[9999] text-xs">
                             
                            <!-- Dropdown Header -->
                            <div class="p-3 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between border-b border-slate-700">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-bell text-amber-400"></i>
                                    <span class="font-extrabold text-xs">Thông Báo Hệ Thống</span>
                                    <template x-if="$store.notifications && $store.notifications.unreadCount > 0">
                                        <span class="px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-black" x-text="$store.notifications.unreadCount + ' mới'"></span>
                                    </template>
                                </div>
                                <button type="button" 
                                        @click="$store.notifications ? $store.notifications.markAllAsRead() : null"
                                        class="text-[11px] text-sky-300 hover:text-white font-bold transition flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-check-double text-[10px]"></i>
                                    <span>Đọc tất cả</span>
                                </button>
                            </div>

                            <!-- Dropdown List Container -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                <template x-if="$store.notifications && $store.notifications.loadingList">
                                    <div class="p-8 text-center text-slate-400">
                                        <i class="fa-solid fa-spinner fa-spin text-xl text-sky-600 mb-2"></i>
                                        <p class="font-medium text-xs">Đang tải thông báo...</p>
                                    </div>
                                </template>

                                <template x-if="$store.notifications && !$store.notifications.loadingList && $store.notifications.notifications.length === 0">
                                    <div class="p-8 text-center text-slate-400 space-y-1">
                                        <i class="fa-regular fa-bell-slash text-2xl text-slate-300"></i>
                                        <p class="font-bold text-xs text-slate-600">Không có thông báo nào</p>
                                        <p class="text-[11px] text-slate-400">Khi có phân công đơn hoặc tin mới, thông báo sẽ hiển thị tại đây</p>
                                    </div>
                                </template>

                                <template x-if="$store.notifications">
                                    <template x-for="n in $store.notifications.notifications" :key="n.id">
                                        <div class="p-3 hover:bg-slate-50 transition cursor-pointer flex items-start gap-3"
                                             :class="!n.is_read ? 'bg-sky-50/60' : 'bg-white'"
                                             @click="$store.notifications.markAsRead(n.id); if (n.action_url) { window.location.href = n.action_url; $store.notifications.openDropdown = false; }">
                                            
                                            <!-- Type Icon -->
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 shadow-xs"
                                                 :class="{
                                                     'bg-sky-100 text-sky-700': n.type === 'installation_assigned',
                                                     'bg-amber-100 text-amber-700': n.type === 'repair_assigned',
                                                     'bg-emerald-100 text-emerald-700': n.type === 'completed',
                                                     'bg-slate-100 text-slate-600': !['installation_assigned', 'repair_assigned', 'completed'].includes(n.type)
                                                 }">
                                                <i :class="$store.notifications.getIconClass(n.type)"></i>
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                                    <span class="font-extrabold text-xs text-slate-900 truncate" x-text="n.title"></span>
                                                    <span x-show="!n.is_read" class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                                                </div>
                                                <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed" x-text="n.message"></p>
                                                <div class="flex items-center justify-between mt-1 text-[10px] text-slate-400 font-medium">
                                                    <span x-text="n.time_ago"></span>
                                                    <span class="px-1.5 py-0.2 rounded font-black text-[9px]"
                                                          :class="$store.notifications.getTypeBadge(n.type).class"
                                                          x-text="$store.notifications.getTypeBadge(n.type).text"></span>
                                                </div>
                                            </div>

                                        </div>
                                    </template>
                                </template>
                            </div>

                        </div>
                    </div>

                    <div class="flex items-center gap-2 bg-white/10 text-white px-3 py-1 rounded-lg border border-white/20 select-none">
                        <div class="w-6 h-6 rounded-full bg-sky-500 text-white font-bold text-[11px] flex items-center justify-center shadow-xs">
                            {{ mb_substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="text-left leading-tight hidden sm:block">
                            <div class="font-bold text-white text-xs">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-sky-300 font-semibold">
                                {{ Auth::user()->department ?? 'Quản Trị Viên' }}
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()" class="p-1.5 px-2.5 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-slate-200 hover:text-white transition-all cursor-pointer flex items-center gap-1.5 text-xs font-semibold" title="Phóng to toàn màn hình">
                        <i class="fa-solid fa-expand text-sky-300 text-xs"></i>
                        <span class="hidden md:inline">Toàn màn hình</span>
                    </button>

                    <form action="{{ url('/logout') }}" method="POST" class="m-0 inline">
                        @csrf
                        <button type="submit" class="p-1.5 px-2.5 rounded-lg bg-rose-500/20 hover:bg-rose-600 text-rose-200 hover:text-white border border-rose-400/30 font-bold transition-all cursor-pointer flex items-center gap-1 text-xs" title="Đăng xuất khỏi hệ thống">
                            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                            <span class="hidden sm:inline">Thoát</span>
                        </button>
                    </form>
                @else
                    <a href="{{ url('/login') }}" class="flex items-center gap-2 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white font-black px-4 py-1.5 rounded-xl shadow-lg shadow-sky-600/30 transition-all cursor-pointer border border-sky-400/50 text-xs">
                        <i class="fa-solid fa-right-to-bracket text-xs"></i>
                        <span>ĐĂNG NHẬP</span>
                    </a>
                @endif
            </div>

        </div>

        <!-- Main Horizontal Nav Bar: Exactly matching the React HorizontalNavbar -->
        <!-- NOTE: overflow MUST BE VISIBLE so dropdowns can float freely without being cut off! -->
        <nav class="bg-gradient-to-r from-[#1b4a8c] via-[#215ba6] to-[#1e5296] border-t border-b border-[#18427e] px-2 sm:px-4 flex items-center gap-1 sm:gap-1.5 relative z-40 shadow-md">
            @php
                $authUser = Auth::user();
                $pendingCodBadge = \App\Models\DriverCashHandover::where('status', 'Chờ kế toán duyệt')->count();
                $pendingReturnBadge = \App\Models\DeliveryReturn::where('status', 'Chờ thủ kho duyệt nhập')->count();
            @endphp
            
            <!-- 1. Dashboard -->
            <div class="relative shrink-0">
                <a href="{{ url('/dashboard') }}" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('dashboard') || request()->is('/') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-chart-pie text-sm {{ request()->is('dashboard') || request()->is('/') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Dashboard</span>
                    @if(request()->is('dashboard') || request()->is('/'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </a>
            </div>

            <!-- 2. Công việc -->
            @if(!$authUser || $authUser->hasAnyPermission('task_assignment.view', 'dealers.view', 'dealer_care.view', 'proposals.view', 'workflow.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('tasks*') || request()->is('proposals*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-list-check text-sm {{ request()->is('tasks*') || request()->is('proposals*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Công việc</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('tasks*') || request()->is('proposals*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[250px] py-2 text-xs">
                    @if(!$authUser || $authUser->hasPermission('task_assignment.view'))
                    <a href="{{ url('/tasks') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-tasks text-sky-600 w-4"></i><span>Giao việc & Báo cáo công việc</span>
                    </a>
                    <a href="{{ url('/tasks?view=my_tasks') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-user-check text-emerald-600 w-4"></i><span>Công việc của tôi</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('dealers.view', 'dealer_care.view'))
                    <a href="{{ url('/dealers') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-store text-amber-600 w-4"></i><span>Mở rộng & Chăm sóc đại lý</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-700">GPS</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('proposals.view'))
                    <a href="{{ url('/proposals') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-file-circle-check text-purple-600 w-4"></i><span>Duyệt Đề xuất & Trình ký</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('workflow.view', 'permissions.view'))
                    <a href="{{ url('/tasks') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-diagram-project text-teal-600 w-4"></i><span>Quản lý quy trình duyệt</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 3. Bán hàng (Active in Orders) -->
            @if(!$authUser || $authUser->hasAnyPermission('orders.view', 'orders.create', 'quotation.view', 'quotation.create', 'returns.view', 'supplier_orders.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('orders*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-cart-shopping text-sm {{ request()->is('orders*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Bán hàng</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 {{ request()->is('orders*') ? 'text-[#1b4a8c]' : 'opacity-70' }}"></i>
                    @if(request()->is('orders*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[260px] py-2 text-xs">
                    @if(!$authUser || $authUser->hasPermission('orders.view'))
                    <a href="{{ url('/orders') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('orders') && !request()->is('orders/create') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <i class="fa-solid fa-box text-sky-600 w-4"></i><span>Xem danh sách đơn hàng</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('orders.create', 'quotation.create', 'quotation.view'))
                    <a href="{{ url('/orders/create') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('orders/create') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <i class="fa-solid fa-file-circle-plus text-emerald-600 w-4"></i><span>Báo giá đơn hàng</span>
                    </a>
                    @endif
                    <a href="{{ url('/orders/cod-verification') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('orders/cod-verification*') || request()->is('sales/cod-verification*') || request()->is('cod-verification*') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-money-bill-transfer text-emerald-600 w-4"></i><span>Kiểm COD</span>
                        </div>
                        @if(($pendingCodBadge ?? 0) > 0)
                            <span class="px-2 py-0.5 bg-rose-500 text-white rounded-full text-[10px] font-bold shadow-xs animate-pulse">{{ $pendingCodBadge }}</span>
                        @endif
                    </a>
                    @if(!$authUser || $authUser->hasPermission('returns.view'))
                    <a href="{{ url('/inventory/import') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('inventory/import*') || request()->is('returns*') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-rotate-left text-amber-600 w-4"></i><span>Khách trả hàng (Nhập kho)</span>
                        </div>
                        @if(($pendingReturnBadge ?? 0) > 0)
                            <span class="px-2 py-0.5 bg-rose-500 text-white rounded-full text-[10px] font-bold shadow-xs animate-pulse">{{ $pendingReturnBadge }}</span>
                        @endif
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('supplier_orders.view'))
                    <a href="{{ url('/orders') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-cart-flatbed text-blue-600 w-4"></i><span>Đặt hàng NCC</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 4. Kho hàng -->
            @if(!$authUser || $authUser->hasAnyPermission('inventory.view', 'orders.view', 'inv_picking.view', 'inv_qc.view', 'inv_import.view', 'inv_export.view', 'inv_transfer.view', 'supplier_orders.view', 'returns.view', 'inv_audit.view', 'delivery_dispatch.view', 'driver_debt.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('cat_products*') || request()->is('delivery*') || request()->is('inventory*') || request()->is('picking*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-boxes-stacked text-sm {{ request()->is('cat_products*') || request()->is('delivery*') || request()->is('inventory*') || request()->is('picking*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Kho hàng</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('cat_products*') || request()->is('delivery*') || request()->is('inventory*') || request()->is('picking*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[270px] py-2 text-xs">
                    @if(!$authUser || $authUser->hasPermission('inventory.view'))
                    <a href="{{ url('/cat_products') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('cat_products*') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <i class="fa-solid fa-box text-sky-600 w-4"></i><span>Danh sách tồn kho</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('inv_import.view'))
                    <a href="{{ route('inventory.receipts') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('inventory/receipts*') || request()->is('warehouse/receipts*') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-arrow-down-left-across-segments text-emerald-600 w-4"></i><span>Phiếu Nhập kho (PNK)</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Nhập NCC</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('inv_export.view'))
                    <a href="{{ route('inventory.issues') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('inventory/issues*') || request()->is('warehouse/issues*') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-arrow-up-right-from-square text-amber-600 w-4"></i><span>Phiếu Xuất kho (PXK)</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Xuất nội bộ</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('inv_picking.view', 'inv_qc.view'))
                    <a href="{{ url('/inventory/picking') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('inventory/picking*') || request()->is('picking*') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <i class="fa-solid fa-boxes-packing text-purple-600 w-4"></i><span>Bốc hàng & Kiểm hàng xuất kho</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('returns.view'))
                    <a href="{{ url('/inventory/import') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700 {{ request()->is('inventory/import*') || request()->is('returns*') ? 'bg-sky-100/80 text-[#1b4a8c] font-bold border-l-4 border-l-[#1b4a8c]' : '' }}">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-rotate-left text-blue-600 w-4"></i><span>Hoàn kho từ xe giao hàng</span>
                        </div>
                        @if(($pendingReturnBadge ?? 0) > 0)
                            <span class="px-2 py-0.5 bg-rose-500 text-white rounded-full text-[10px] font-bold shadow-xs animate-pulse">{{ $pendingReturnBadge }}</span>
                        @endif
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('orders.view', 'inv_export.view'))
                    <a href="{{ url('/orders') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-file-invoice text-indigo-600 w-4"></i><span>Danh sách đơn xuất kho</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('supplier_orders.view', 'returns.view'))
                    <a href="{{ url('/cat_products') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-rotate-left text-rose-600 w-4"></i><span>Trả hàng NCC</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('inv_audit.view'))
                    <a href="{{ url('/cat_products') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-clipboard-check text-indigo-600 w-4"></i><span>Kiểm kê chi tiết kho</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('delivery_dispatch.view'))
                    <a href="{{ url('/delivery-dispatch') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-route text-sky-600 w-4"></i><span>Điều phối xe</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('delivery_dispatch.view', 'driver_debt.view'))
                    <a href="{{ url('/driver-portal') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between font-semibold text-slate-700">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-truck-fast text-purple-600 w-4"></i><span>Giao hàng & Thu tiền</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Lái xe</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 4.1 Máy lọc nước (Lắp đặt, Sửa chữa & Kho phụ tùng độc lập) -->
            @if(!$authUser || $authUser->hasAnyPermission('water_filter.view', 'water_filter.install_view', 'water_filter.repair_view', 'water_filter.parts_view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('water-filter*') || request()->is('may-loc-nuoc*') || request()->is('lap-dat-may-loc*') || request()->is('sua-chua-may-loc*') || request()->is('kho-phu-tung*') || request()->is('bao-hanh-may-loc*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-faucet-drip text-sm {{ request()->is('water-filter*') || request()->is('may-loc-nuoc*') || request()->is('bao-hanh-may-loc*') ? 'text-[#1b4a8c]' : 'text-cyan-300' }}"></i>
                    <span>Máy lọc nước</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('water-filter*') || request()->is('may-loc-nuoc*') || request()->is('lap-dat-may-loc*') || request()->is('sua-chua-may-loc*') || request()->is('kho-phu-tung*') || request()->is('bao-hanh-may-loc*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[270px] py-2 text-xs">
                    @if(!$authUser || $authUser->hasAnyPermission('water_filter.view', 'water_filter.install_view'))
                    <a href="{{ url('/lap-dat-may-loc') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-screwdriver-wrench text-sky-600 w-4"></i><span>Đơn đi lắp đặt máy lọc</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">Điều phối</span>
                    </a>
                    <a href="{{ url('/bao-hanh-may-loc') }}" class="w-full text-left px-4 py-2.5 hover:bg-emerald-50/70 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-shield-halved text-emerald-600 w-4"></i><span>Danh sách máy đã lắp</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Bảo hành</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('water_filter.view', 'water_filter.repair_view'))
                    <a href="{{ url('/sua-chua-may-loc') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-wrench text-amber-600 w-4"></i><span>Tiếp nhận sửa chữa & BH</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Dịch vụ</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('water_filter.view', 'water_filter.parts_view'))
                    <a href="{{ url('/kho-phu-tung-may-loc') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-boxes-stacked text-teal-600 w-4"></i><span>Kho phụ tùng & phụ kiện</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800">Kho riêng</span>
                    </a>
                    <a href="{{ url('/water-filter?tab=transactions') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-clock-rotate-left text-indigo-600 w-4"></i><span>Lịch sử nhập / xuất kho phụ tùng</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 5. Thu chi -->
            @if(!$authUser || $authUser->hasAnyPermission('funds.view', 'proposals.view', 'fleet.view', 'driver_debt.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('funds*') || request()->is('fleet*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-wallet text-sm {{ request()->is('funds*') || request()->is('fleet*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Thu chi</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('funds*') || request()->is('fleet*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[270px] py-2 text-xs">
                    @if(!$authUser || $authUser->hasPermission('funds.view'))
                    <a href="{{ url('/funds') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-wallet text-emerald-600 w-4"></i><span>Sổ quỹ, Thu chi & Chuyển quỹ</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('proposals.view'))
                    <a href="{{ url('/proposals') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-file-circle-check text-blue-600 w-4"></i><span>Duyệt Đề xuất & Chi hộ</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('fleet.view', 'driver_debt.view'))
                    <a href="{{ url('/fleet') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-truck text-purple-600 w-4"></i><span>Quản lý Fleet Xe & Quyết toán Lái xe</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 6. Khách hàng -->
            @if(!$authUser || $authUser->hasAnyPermission('dealers.view', 'dealers.create', 'debt.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('dealers*') || request()->is('debt*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-users text-sm {{ request()->is('dealers*') || request()->is('debt*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Khách hàng</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('dealers*') || request()->is('debt*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[260px] py-2 text-xs">
                    @if(!$authUser || $authUser->hasPermission('dealers.view'))
                    <a href="{{ url('/dealers') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-store text-emerald-600 w-4"></i><span>Danh sách khách hàng</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('dealers.create'))
                    <a href="{{ url('/dealers/create') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-user-plus text-sky-600 w-4"></i><span>Thêm mới khách hàng</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('debt.view'))
                    <a href="{{ url('/debt') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-credit-card text-amber-600 w-4"></i><span>Hạn mức & công nợ khách hàng</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 7. Nhân sự -->
            @if(!$authUser || $authUser->hasAnyPermission('staff_list.view', 'payroll.view', 'attendance.view', 'attendance.checkin', 'kpi.view', 'permissions.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('staff*') || request()->is('payroll*') || request()->is('attendance*') || request()->is('kpi*') || request()->is('roles*') || request()->is('profile*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-user-group text-sm {{ request()->is('staff*') || request()->is('payroll*') || request()->is('attendance*') || request()->is('kpi*') || request()->is('roles*') || request()->is('profile*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Nhân sự</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('staff*') || request()->is('payroll*') || request()->is('attendance*') || request()->is('kpi*') || request()->is('roles*') || request()->is('profile*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[260px] py-2 text-xs">
                    <a href="{{ url('/profile') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-id-badge text-sky-600 w-4"></i><span>Trang cá nhân & Hồ sơ nhân viên</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Mới</span>
                    </a>
                    @if(!$authUser || $authUser->hasPermission('staff_list.view'))
                    <a href="{{ url('/staff') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-users text-blue-600 w-4"></i><span>Danh sách nhân sự</span>
                    </a>
                    <a href="{{ url('/staff-groups') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center justify-between border-b border-slate-100 font-semibold text-slate-700">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-people-group text-indigo-600 w-4"></i><span>Nhóm nhân sự</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">Mới</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('payroll.view'))
                    <a href="{{ url('/payroll') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-money-check-dollar text-emerald-600 w-4"></i><span>Bảng lương nhân sự</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasAnyPermission('attendance.view', 'attendance.checkin'))
                    <a href="{{ url('/attendance') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-map-location-dot text-rose-600 w-4"></i><span>Chấm công GPS & Định vị Chi nhánh</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('kpi.view'))
                    <a href="{{ url('/kpi') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-bullseye text-amber-600 w-4"></i><span>Quản lý KPI & Đánh giá nhân sự</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('permissions.view'))
                    <a href="{{ url('/roles') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-shield-halved text-purple-600 w-4"></i><span>Phân quyền & Vai trò</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 8. Báo cáo -->
            @if(!$authUser || $authUser->hasAnyPermission('dashboard.view', 'orders.view', 'inventory.view', 'debt.view', 'funds.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('reports*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-chart-column text-sm {{ request()->is('reports*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Báo cáo</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('reports*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[260px] py-2 text-xs">
                    @if(!$authUser || $authUser->hasPermission('orders.view'))
                    <a href="{{ url('/reports/sales') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-chart-line text-emerald-600 w-4"></i><span>BC Bán hàng theo ngày</span>
                    </a>
                    <a href="{{ url('/orders') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-file-lines text-sky-600 w-4"></i><span>BC Bán hàng chi tiết theo đơn</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('inventory.view'))
                    <a href="{{ url('/reports/inventory') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-boxes-stacked text-amber-600 w-4"></i><span>BC Tồn kho vật tư Tiền Phong</span>
                    </a>
                    <a href="{{ url('/reports/inventory') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-boxes-packing text-indigo-600 w-4"></i><span>BC Xuất - Nhập - Tồn kho</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('debt.view'))
                    <a href="{{ url('/debt') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-credit-card text-rose-600 w-4"></i><span>BC Tổng hợp công nợ đại lý</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- 9. Danh mục -->
            @if(!$authUser || $authUser->hasPermission('danhmuc.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('cat_*') || request()->is('categories*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-folder-tree text-sm {{ request()->is('cat_*') || request()->is('categories*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Danh mục</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('cat_*') || request()->is('categories*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[250px] py-2 text-xs">
                    <a href="{{ url('/cat_warehouses') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-warehouse text-sky-600 w-4"></i><span>Kho hàng</span>
                    </a>
                    <a href="{{ url('/cat_fund_types') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-wallet text-emerald-600 w-4"></i><span>Loại quỹ</span>
                    </a>
                    <a href="{{ url('/cat_branches') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-code-branch text-purple-600 w-4"></i><span>Chi nhánh</span>
                    </a>
                    <a href="{{ url('/cat_categories') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-layer-group text-blue-600 w-4"></i><span>Phân loại</span>
                    </a>
                    <a href="{{ url('/cat_brands') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-award text-amber-600 w-4"></i><span>Nhãn hiệu</span>
                    </a>
                    <a href="{{ url('/cat_suppliers') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-industry text-slate-600 w-4"></i><span>Nhà cung cấp</span>
                    </a>
                    <a href="{{ url('/cat_products') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-boxes-packing text-teal-600 w-4"></i><span>Sản phẩm</span>
                    </a>
                    <a href="{{ url('/cat_price_tiers') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-tags text-indigo-600 w-4"></i><span>Cấu hình giá</span>
                    </a>
                    <a href="{{ url('/cat_delivery_routes') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-location-arrow text-rose-600 w-4"></i><span>Tuyến GH</span>
                    </a>
                    <a href="{{ url('/cat_vehicles') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-car text-blue-600 w-4"></i><span>Xe giao hàng</span>
                    </a>
                    <a href="{{ url('/cat_sales_policies') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-percent text-emerald-600 w-4"></i><span>Chính sách bán hàng</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- 10. Hệ thống -->
            @if(!$authUser || $authUser->hasAnyPermission('audit.view', 'permissions.view'))
            <div class="nav-item-group relative shrink-0">
                <button type="button" class="flex items-center gap-2 px-3.5 py-2.5 text-xs sm:text-sm font-bold transition-colors select-none rounded-t-lg relative whitespace-nowrap cursor-pointer {{ request()->is('audit*') ? 'bg-white text-[#1b4a8c] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <i class="fa-solid fa-gear text-sm {{ request()->is('audit*') ? 'text-[#1b4a8c]' : 'text-sky-200' }}"></i>
                    <span>Hệ thống</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-70"></i>
                    @if(request()->is('audit*'))
                        <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-amber-400 rounded-full"></span>
                    @endif
                </button>
                <div class="nav-submenu hidden absolute left-0 top-full z-[9999] bg-white text-slate-800 border border-slate-200 shadow-2xl rounded-b-xl rounded-tr-xl min-w-[260px] py-2 text-xs">
                    @if(!$authUser || $authUser->hasPermission('audit.view'))
                    <a href="{{ url('/audit-logs') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 border-b border-slate-100 font-semibold text-slate-700">
                        <i class="fa-solid fa-shield-halved text-sky-600 w-4"></i><span>Nhật ký Truy vết System Audit</span>
                    </a>
                    @endif
                    @if(!$authUser || $authUser->hasPermission('permissions.view'))
                    <a href="{{ url('/roles') }}" class="w-full text-left px-4 py-2.5 hover:bg-sky-50 flex items-center gap-2.5 font-semibold text-slate-700">
                        <i class="fa-solid fa-diagram-project text-purple-600 w-4"></i><span>Sơ đồ Phân quyền & Vai trò</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

        </nav>

    </header>

    <!-- MAIN BODY CONTAINER -->
    <main class="flex-1 w-full mx-auto px-2 sm:px-4 lg:px-6 py-4">
        
        <!-- Flash Toasts -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-400">
        <span>© 2026 CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN &bull; Hệ Thống ERP Phân Phối Máy Lọc Nước & Thiết Bị Xử Lý Nước</span>
    </footer>

    <!-- Global Table Click-and-Drag Horizontal Scroll -->
    <script>
        (function() {
            let activeContainer = null;
            let isDown = false;
            let startX = 0;
            let scrollLeft = 0;
            let hasDragged = false;
            let downX = 0;
            let downY = 0;

            function isInteractiveElement(target) {
                return !!target.closest('a, button, input, select, textarea, label, [role="button"], .no-drag, th.sticky, td.sticky');
            }

            function getScrollContainer(target) {
                const container = target.closest('.overflow-x-auto, .table-scroll-container, [data-drag-scroll]');
                if (!container) return null;
                // Only consider as drag-scrollable if it actually overflows horizontally
                if (container.scrollWidth <= container.clientWidth + 2) return null;
                return container;
            }

            document.addEventListener('mouseover', function(e) {
                if (isDown) return;
                const container = getScrollContainer(e.target);
                if (container && !isInteractiveElement(e.target)) {
                    if (container.style.cursor !== 'grab') {
                        container.style.cursor = 'grab';
                    }
                } else if (container && isInteractiveElement(e.target)) {
                    if (container.style.cursor === 'grab') {
                        container.style.cursor = '';
                    }
                }
            });

            document.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return; // Only left click
                if (isInteractiveElement(e.target)) return;

                const container = getScrollContainer(e.target);
                if (!container) return;

                activeContainer = container;
                isDown = true;
                hasDragged = false;
                downX = e.pageX;
                downY = e.pageY;
                startX = e.pageX - container.offsetLeft;
                scrollLeft = container.scrollLeft;
            });

            window.addEventListener('mousemove', function(e) {
                if (!isDown || !activeContainer) return;

                const dx = e.pageX - downX;
                const dy = e.pageY - downY;

                if (!hasDragged && (Math.abs(dx) > 3 || Math.abs(dy) > 3)) {
                    hasDragged = true;
                    activeContainer.classList.add('is-dragging-scroll');
                    document.body.style.userSelect = 'none';
                }

                if (hasDragged) {
                    e.preventDefault();
                    const x = e.pageX - activeContainer.offsetLeft;
                    const walk = (x - startX) * 1.25;
                    activeContainer.scrollLeft = scrollLeft - walk;
                }
            });

            function endDrag() {
                if (!isDown) return;
                isDown = false;
                if (activeContainer) {
                    activeContainer.classList.remove('is-dragging-scroll');
                    activeContainer.style.cursor = 'grab';
                }
                document.body.style.userSelect = '';

                if (hasDragged) {
                    const blockClick = function(ev) {
                        ev.stopPropagation();
                        ev.preventDefault();
                        window.removeEventListener('click', blockClick, true);
                    };
                    window.addEventListener('click', blockClick, true);
                    setTimeout(function() {
                        window.removeEventListener('click', blockClick, true);
                    }, 80);
                }

                activeContainer = null;
                hasDragged = false;
            }

            window.addEventListener('mouseup', endDrag);
        })();
    </script>

    <!-- ====================================================================
         Global ERP Realtime Notification Engine
         Hoạt động trên MỌI trang - không phụ thuộc route
         Xử lý: SHORTAGE_REPORTED, ORDER_ITEMS_MODIFIED, ORDER_UPDATED
         ==================================================================== -->
    <div id="erp-notif-container" class="fixed top-4 right-4 z-[99999] flex flex-col gap-2.5 pointer-events-none max-w-sm w-full"></div>

    <script>
    (function () {
        'use strict';

        /* ─── Cấu hình ─────────────────────────────────────────────── */
        const POLL_INTERVAL   = 2000;   // ms — poll mọi trang (bao gồm picking)
        const TOAST_TTL       = 18000;  // ms — tự đóng toast
        @php
            $curUser = auth()->user();
            $canApproveCreditLayout = $curUser && (
                $curUser->id === 1 ||
                $curUser->hasRole('admin') ||
                $curUser->hasRole('Admin') ||
                $curUser->hasRole('manager') ||
                $curUser->hasRole('Manager') ||
                $curUser->hasRole('director') ||
                $curUser->hasRole('accountant') ||
                $curUser->hasRole('finance_accountant') ||
                $curUser->hasRole('sales_accountant') ||
                $curUser->hasPermission('orders.approve') ||
                $curUser->hasPermission('debt.approve') ||
                in_array($curUser->department ?? '', ['Executive', 'Ban Giám Đốc', 'Quản Lý', 'Kế Toán']) ||
                !empty($curUser->is_admin)
            );
        @endphp
        const CURRENT_USER    = '{{ auth()->user()?->name ?? "" }}';
        const CURRENT_USER_ID = {{ auth()->id() ?? 'null' }};
        const IS_ADMIN_OR_ACCOUNTANT = {{ $canApproveCreditLayout ? 'true' : 'false' }};

        /* ─── State ─────────────────────────────────────────────────── */
        let lastSyncId    = {{ \App\Models\PickingSyncEvent::max('id') ?? 0 }};
        let isPolling     = false;
        let unreadCount   = 0;
        const activePendingOrderIds = new Set();

        /* ─── Helpers: Âm thanh ─────────────────────────────────────── */
        function playBeep(type = 'warning') {
            try {
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (!Ctx) return;
                const ctx  = new Ctx();
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = type === 'info' ? 'sine' : 'triangle';
                osc.frequency.setValueAtTime(type === 'info' ? 520 : 587, ctx.currentTime);
                gain.gain.setValueAtTime(0.28, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.55);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.55);
            } catch (e) {}
        }

        /* ─── Helpers: Desktop Notification ────────────────────────── */
        function desktopNotif(title, body) {
            if (window.Notification && Notification.permission === 'granted') {
                try { new Notification(title, { body, icon: '/favicon.ico' }); } catch(e) {}
            }
        }

        /* ─── Helpers: Cập nhật badge đếm thông báo ────────────────── */
        function bumpBadge() {
            unreadCount++;
            let badge = document.getElementById('erp-notif-badge');
            if (!badge) {
                // Gắn badge vào phần tử nav nếu có, fallback là title
                badge = document.createElement('span');
                badge.id = 'erp-notif-badge';
                badge.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;position:fixed;top:12px;right:12px;min-width:20px;height:20px;padding:0 5px;border-radius:999px;background:#ef4444;color:#fff;font-size:11px;font-weight:900;z-index:100000;box-shadow:0 2px 8px rgba(239,68,68,.5);pointer-events:none;';
                document.body.appendChild(badge);
            }
            badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            badge.style.display = 'inline-flex';
            // Cập nhật document title
            document.title = '(' + unreadCount + ' thông báo) ' + document.title.replace(/^\(\d+ thông báo\) /, '');
        }

        function clearBadge() {
            unreadCount = 0;
            const badge = document.getElementById('erp-notif-badge');
            if (badge) badge.style.display = 'none';
            document.title = document.title.replace(/^\(\d+ thông báo\) /, '');
        }

        /* ─── Core: Hiển thị toast ───────────────────────────────────── */
        function showToast({ type = 'shortage', orderCode, supervisor, reporter, changes, shortageItems, orderId, orderCreator, customerName, driverName, overrunAmount, reason }) {
            const container = document.getElementById('erp-notif-container');
            if (!container) return;

            // Xử lý riêng loại thông báo Yêu cầu duyệt bảo lãnh nợ vượt hạn mức (Gửi về tài khoản lên đơn)
            if (type === 'credit_overrun') {
                const gradFrom    = '#3b0764'; // purple-950
                const gradTo      = '#6b21a8'; // purple-800
                const borderClr   = '#d8b4fe'; // purple-300
                const badgeText   = '🛡️ XIN DUYỆT BẢO LÃNH NỢ';
                const badgeBg     = 'rgba(255,255,255,.18)';
                const badgeColor  = '#fde047'; // amber-300

                const wrap = document.createElement('div');
                wrap.style.cssText = `pointer-events:auto;background:linear-gradient(135deg,${gradFrom},${gradTo});color:#fff;padding:14px 16px;border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,.5);border:1.5px solid ${borderClr};display:flex;flex-direction:column;gap:10px;animation:erpSlideIn .3s ease;`;
                wrap.setAttribute('data-erp-toast', '1');

                const overrunFormatted = new Intl.NumberFormat('vi-VN').format(Math.round(overrunAmount || 0)) + ' đ';

                wrap.innerHTML = `
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                        <div style="display:flex;align-items:flex-start;gap:10px;min-width:0;">
                            <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">🛡️</div>
                            <div style="min-width:0;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:3px;">
                                    <span style="padding:2px 8px;border-radius:999px;background:${badgeBg};color:${badgeColor};font-size:9.5px;font-weight:900;letter-spacing:.05em;text-transform:uppercase;">${badgeText}</span>
                                    <strong style="font-family:monospace;font-size:13px;color:#fff;">${orderCode || ''}</strong>
                                </div>
                                <div style="font-size:11.5px;opacity:.95;line-height:1.4;">
                                    Gửi người lên đơn: <strong style="color:#fde047;">${orderCreator || 'Bạn'}</strong>
                                </div>
                                <div style="font-size:11px;opacity:.85;margin-top:1px;">
                                    Đại lý: <strong>${customerName || ''}</strong>
                                </div>
                                <div style="font-size:10.5px;opacity:.8;margin-top:1px;">
                                    Lái xe gửi: <strong>${driverName || 'Lái xe'}</strong>
                                </div>
                            </div>
                        </div>
                        <button onclick="this.closest('[data-erp-toast]').remove()" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:18px;font-weight:900;cursor:pointer;padding:0;line-height:1;flex-shrink:0;">&times;</button>
                    </div>

                    <div style="background:rgba(0,0,0,.3);border-radius:10px;padding:8px 10px;font-size:11.5px;color:#f3e8ff;line-height:1.5;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span>Số tiền vượt hạn mức:</span>
                            <strong style="font-size:13px;color:#fde047;font-family:monospace;">+${overrunFormatted}</strong>
                        </div>
                        ${reason ? `<div style="margin-top:4px;font-size:10.5px;color:#e9d5ff;font-style:italic;border-top:1px dashed rgba(255,255,255,.15);padding-top:4px;">Lý do: "${reason}"</div>` : ''}
                    </div>

                    <div id="override-action-box-${orderId}" style="display:flex;align-items:center;justify-content:flex-end;gap:6px;padding-top:2px;">
                        <a href="/orders?search=${encodeURIComponent(orderCode || '')}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:rgba(255,255,255,.15);color:#fff;font-size:11px;font-weight:600;border-radius:8px;text-decoration:none;border:1px solid rgba(255,255,255,.2);transition:background .2s;"
                           onmouseover="this.style.background='rgba(255,255,255,.25)'" onmouseout="this.style.background='rgba(255,255,255,.15)'">
                           👁 Xem đơn
                        </a>
                        <button type="button" onclick="window.erpApproveCreditOverride('${orderId}', 'reject', this)"
                                style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#e11d48;color:#fff;font-size:11px;font-weight:700;border-radius:8px;border:none;cursor:pointer;transition:background .2s;"
                                onmouseover="this.style.background='#be123c'" onmouseout="this.style.background='#e11d48'">
                           ❌ Từ chối
                        </button>
                        <button type="button" onclick="window.erpApproveCreditOverride('${orderId}', 'approve', this)"
                                style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;background:#16a34a;color:#fff;font-size:11px;font-weight:700;border-radius:8px;border:none;cursor:pointer;box-shadow:0 2px 8px rgba(22,163,74,.4);transition:background .2s;"
                                onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                           ⚡ Phê duyệt
                        </button>
                    </div>
                `;

                container.appendChild(wrap);
                bumpBadge();

                setTimeout(() => { if (wrap.parentElement) wrap.remove(); }, 30000);
                return;
            }

            const isShortage  = type === 'shortage';
            const gradFrom    = isShortage ? '#7f1d1d' : '#1e3a5f';
            const gradTo      = isShortage ? '#b91c1c' : '#1e40af';
            const borderClr   = isShortage ? '#fca5a5' : '#93c5fd';
            const badgeText   = isShortage ? '⚠️ KHO BÁO THIẾU HÀNG' : '🔔 CẬP NHẬT ĐƠN HÀNG';
            const badgeBg     = isShortage ? 'rgba(0,0,0,.35)' : 'rgba(255,255,255,.15)';
            const badgeColor  = isShortage ? '#fde68a' : '#bae6fd';

            // Nội dung chi tiết
            let detailHtml = '';
            if (isShortage && shortageItems && shortageItems.length) {
                const rows = shortageItems.map(i =>
                    `<div style="display:flex;gap:6px;align-items:center;">
                        <span style="width:5px;height:5px;border-radius:50%;background:#fca5a5;flex-shrink:0;"></span>
                        <span>${i.name || i.sku} — thiếu <strong>${i.shortage_qty ?? i.shortageQty ?? ''} ${i.unit || ''}</strong></span>
                    </div>`
                ).join('');
                detailHtml = `<div style="background:rgba(0,0,0,.28);border-radius:10px;padding:8px 10px;font-size:11px;color:#fecaca;line-height:1.6;margin-top:2px;">${rows}</div>`;
            } else if (!isShortage && changes && changes.length) {
                const rows = changes.slice(0, 4).map(c =>
                    `<div style="display:flex;gap:6px;align-items:flex-start;">
                        <span style="width:5px;height:5px;border-radius:50%;background:#93c5fd;flex-shrink:0;margin-top:5px;"></span>
                        <span>${c.message || (c.name + (c.delta > 0 ? ' +' : ' ') + c.delta + ' ' + (c.unit || ''))}</span>
                    </div>`
                ).join('');
                detailHtml = `<div style="background:rgba(0,0,0,.22);border-radius:10px;padding:8px 10px;font-size:11px;color:#bfdbfe;line-height:1.6;margin-top:2px;">${rows}</div>`;
            }

            // Link hành động
            const actionLink = orderId
                ? (isShortage ? `/orders?search=${encodeURIComponent(orderCode||'')}` : `/inventory/picking?order_id=${orderId}`)
                : '#';
            const actionLabel = isShortage ? '👁 Xem đơn hàng' : '📦 Mở trang bốc hàng';

            const wrap = document.createElement('div');
            wrap.style.cssText = `pointer-events:auto;background:linear-gradient(135deg,${gradFrom},${gradTo});color:#fff;padding:14px 16px;border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,.45);border:1.5px solid ${borderClr};display:flex;flex-direction:column;gap:8px;animation:erpSlideIn .3s ease;`;
            wrap.innerHTML = `
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;min-width:0;">
                        <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">${isShortage ? '🚨' : '📝'}</div>
                        <div style="min-width:0;">
                            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:5px;margin-bottom:3px;">
                                <span style="padding:2px 8px;border-radius:999px;background:${badgeBg};color:${badgeColor};font-size:9px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;">${badgeText}</span>
                                <strong style="font-family:monospace;font-size:13px;">${orderCode || ''}</strong>
                            </div>
                            <div style="font-size:11px;opacity:.85;">${isShortage
                                ? `Gửi người phụ trách: <strong style="color:${badgeColor}">${supervisor || ''}</strong>`
                                : `Cập nhật bởi: <strong style="color:${badgeColor}">${reporter || ''}</strong>`}
                            </div>
                            ${!isShortage && supervisor ? `<div style="font-size:10px;opacity:.7;margin-top:1px;">Người phụ trách đơn: ${supervisor}</div>` : ''}
                            ${isShortage && reporter ? `<div style="font-size:10px;opacity:.7;margin-top:1px;">Báo bởi: ${reporter}</div>` : ''}
                        </div>
                    </div>
                    <button onclick="this.closest('[data-erp-toast]').remove()" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:18px;font-weight:900;cursor:pointer;padding:0;line-height:1;flex-shrink:0;">&times;</button>
                </div>
                ${detailHtml}
                <div style="display:flex;justify-content:flex-end;gap:6px;padding-top:2px;">
                    <a href="${actionLink}" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(255,255,255,.18);color:#fff;font-size:11px;font-weight:700;border-radius:8px;text-decoration:none;border:1px solid rgba(255,255,255,.25);transition:background .2s;"
                       onmouseover="this.style.background='rgba(255,255,255,.28)'" onmouseout="this.style.background='rgba(255,255,255,.18)'">${actionLabel}</a>
                    <button onclick="this.closest('[data-erp-toast]').remove();window.erpClearBadge&&window.erpClearBadge();" style="padding:5px 10px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);font-size:11px;font-weight:600;border-radius:8px;border:1px solid rgba(255,255,255,.15);cursor:pointer;">Đã xem</button>
                </div>
            `;
            wrap.setAttribute('data-erp-toast', '1');

            container.appendChild(wrap);
            bumpBadge();

            // Tự đóng sau TOAST_TTL
            setTimeout(() => { if (wrap.parentElement) wrap.remove(); }, TOAST_TTL);
        }

        // Phê duyệt nhanh bảo lãnh nợ ngay từ toast thông báo
        window.erpApproveCreditOverride = async function(orderId, decision, btnEl) {
            const actionBox = btnEl.closest('div');
            if (actionBox) {
                actionBox.innerHTML = '<span style="font-size:11px;color:#fde047;font-weight:bold;">Đang xử lý...</span>';
            }
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/delivery/orders/${orderId}/approve-credit-override`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({ decision: decision, note: 'Xác nhận nhanh từ thông báo realtime' })
                });
                const data = await res.json();
                if (data.success) {
                    activePendingOrderIds.delete(String(orderId));
                    if (actionBox) {
                        const isApprove = decision === 'approve';
                        actionBox.innerHTML = `<span style="font-size:11px;color:${isApprove ? '#86efac' : '#fca5a5'};font-weight:bold;">${isApprove ? '✅ Đã duyệt bảo lãnh thành công!' : '❌ Đã từ chối bảo lãnh!'}</span>`;
                    }
                    try {
                        const bc = new BroadcastChannel('erp_order_sync');
                        bc.postMessage({ type: 'CREDIT_OVERRUN_DECIDED', orderId: orderId, decision: decision });
                    } catch(e) {}
                    localStorage.setItem('erp_order_sync_trigger', Date.now());
                } else {
                    if (actionBox) {
                        actionBox.innerHTML = `<span style="font-size:11px;color:#fca5a5;">Lỗi: ${data.message || 'Không thể thao tác'}</span>`;
                    }
                }
            } catch(e) {
                if (actionBox) {
                    actionBox.innerHTML = `<span style="font-size:11px;color:#fca5a5;">Lỗi kết nối: ${e.message}</span>`;
                }
            }
        };

        // Expose clearBadge globally
        window.erpClearBadge = clearBadge;

        /* ─── Core: Nạp các yêu cầu bảo lãnh nợ đang chờ duyệt ─────── */
        async function checkPendingCreditOverrides() {
            try {
                const res = await fetch('/delivery/orders/pending-credit-overrides');
                if (!res.ok) return;
                const data = await res.json();
                if (data.success && Array.isArray(data.orders)) {
                    data.orders.forEach(order => {
                        const oid = String(order.orderId);
                        if (!activePendingOrderIds.has(oid)) {
                            activePendingOrderIds.add(oid);
                            showToast({
                                type: 'credit_overrun',
                                orderCode: order.orderCode || ('Đơn #' + oid),
                                orderCreator: order.orderCreator || 'Người lên đơn',
                                customerName: order.customerName || '',
                                driverName: order.driverName || 'Lái xe giao hàng',
                                overrunAmount: order.overrunAmount || 0,
                                reason: order.reason || '',
                                orderId: oid
                            });
                            playBeep('warning');
                            desktopNotif(
                                '🛡️ Yêu cầu duyệt bảo lãnh nợ – ' + (order.orderCode || oid),
                                'Đại lý: ' + (order.customerName || '') + '\nVượt: ' + (order.overrunAmount ? new Intl.NumberFormat('vi-VN').format(Math.round(order.overrunAmount)) + ' đ' : '')
                            );
                        }
                    });
                }
            } catch(e) {}
        }

        /* ─── Xác định xem event có liên quan đến user hiện tại không ─── */
        function isRelevantToMe(ev) {
            if (!CURRENT_USER) return true; // Không xác định được user → hiển thị hết
            const extra = ev.extraData || {};

            // Xử lý riêng cho yêu cầu duyệt bảo lãnh nợ
            if (ev.eventType === 'CREDIT_OVERRUN_REQUESTED') {
                const driver = (extra.driverName || ev.performerName || '').trim();
                const creator = (extra.orderCreator || ev.assignedStaff || '').trim();
                const isCreator = (creator && CURRENT_USER && (creator.includes(CURRENT_USER) || CURRENT_USER.includes(creator)))
                    || (extra.orderCreatorId && CURRENT_USER_ID && Number(extra.orderCreatorId) === Number(CURRENT_USER_ID));

                // Nếu user là người lên đơn hoặc là Ban Giám Đốc / Kế toán duyệt → luôn hiển thị
                if (isCreator || IS_ADMIN_OR_ACCOUNTANT) {
                    return true;
                }

                // Nếu chỉ là tài xế tự gửi và không có quyền duyệt → ẩn
                if (driver && CURRENT_USER && driver.includes(CURRENT_USER)) {
                    return false;
                }

                const assignedList = ev.assignedStaffList || [];
                if (Array.isArray(assignedList) && assignedList.some(p => p && (p.includes(CURRENT_USER) || CURRENT_USER.includes(p)))) return true;

                return false;
            }

            // Trưởng nhóm / người lên đơn (supervisor)
            const supervisor = extra.supervisor || extra.orderCreator || ev.assignedStaff || '';
            if (supervisor && supervisor.includes(CURRENT_USER)) return true;

            // Người được phân công bốc
            const pickers = extra.assignedPickers || [];
            if (Array.isArray(pickers) && pickers.some(p => p && p.includes(CURRENT_USER))) return true;

            // assigned_staff string
            const assigned = ev.assignedStaff || ev.assignedStaffList || '';
            if (typeof assigned === 'string' && assigned.includes(CURRENT_USER)) return true;
            if (Array.isArray(assigned) && assigned.some(p => p && p.includes(CURRENT_USER))) return true;

            // Reporter chính là mình (tự báo) → không cần nhận thêm
            const reporter = extra.reporter || ev.performerName || '';
            if (reporter && reporter.includes(CURRENT_USER) && ev.eventType === 'SHORTAGE_REPORTED') return false;

            return false;
        }

        /* ─── Core: Xử lý event ─────────────────────────────────────── */
        function handleEvent(ev) {
            if (!ev || !ev.eventType) return;

            if (ev.eventType === 'CREDIT_OVERRUN_REQUESTED') {
                const extra = ev.extraData || {};
                const oid = String(ev.orderId || extra.orderId || '');

                if (isRelevantToMe(ev)) {
                    if (!activePendingOrderIds.has(oid)) {
                        activePendingOrderIds.add(oid);
                        showToast({
                            type: 'credit_overrun',
                            orderCode: extra.orderCode || ('Đơn #' + (ev.orderId || '')),
                            orderCreator: extra.orderCreator || ev.assignedStaff || 'Người lên đơn',
                            customerName: extra.customerName || extra.dealerName || '',
                            driverName: extra.driverName || ev.performerName || 'Lái xe giao hàng',
                            overrunAmount: extra.overrunAmount || 0,
                            reason: extra.reason || '',
                            orderId: ev.orderId
                        });
                        playBeep('warning');
                        desktopNotif(
                            '🛡️ Lái xe xin duyệt bảo lãnh nợ – ' + (extra.orderCode || ev.orderId),
                            'Đại lý: ' + (extra.customerName || '') + '\nVượt: ' + (extra.overrunAmount ? new Intl.NumberFormat('vi-VN').format(Math.round(extra.overrunAmount)) + ' đ' : '') + '\nLái xe: ' + (extra.driverName || ev.performerName || '')
                        );
                    }
                }
                window.dispatchEvent(new CustomEvent('erp:credit-overrun-requested', { detail: ev }));

            } else if (ev.eventType === 'CREDIT_OVERRUN_DECIDED') {
                const extra = ev.extraData || {};
                const oid = String(ev.orderId || extra.orderId || '');
                activePendingOrderIds.delete(oid);
                const existingActionBox = document.getElementById('override-action-box-' + oid);
                if (existingActionBox) {
                    const isApprove = (extra.decision === 'approve' || extra.status === 'Đã duyệt' || ev.orderStatus === 'Đã duyệt');
                    existingActionBox.innerHTML = `<span style="font-size:11px;color:${isApprove ? '#86efac' : '#fca5a5'};font-weight:bold;">${isApprove ? '✅ Đã được phê duyệt!' : '❌ Đã từ chối!'}</span>`;
                    setTimeout(() => {
                        const toastWrap = existingActionBox.closest('[data-erp-toast]');
                        if (toastWrap) toastWrap.remove();
                    }, 4000);
                }
                window.dispatchEvent(new CustomEvent('erp:credit-override-decided', { detail: ev }));

            } else if (ev.eventType === 'SHORTAGE_REPORTED') {
                // Thiếu hàng → luôn hiển thị cho mọi người (admin/kinh doanh/nhóm trưởng)
                // Chỉ ẩn với chính người báo thiếu
                const extra = ev.extraData || {};
                const reporter = extra.reporter || ev.performerName || '';
                const isSelfReport = reporter && CURRENT_USER && reporter.includes(CURRENT_USER);

                // Trang picking đã có UI riêng nhưng vẫn cần show toast nếu người đó là supervisor
                const supervisor = extra.supervisor || extra.orderCreator || '';
                const isMyOrder = supervisor && CURRENT_USER && supervisor.includes(CURRENT_USER);
                const isOnPickingPage = window.location.pathname.includes('/inventory/picking') || window.location.pathname.includes('/picking');

                // Hiển thị nếu: không phải tự báo VÀ (là supervisor HOẶC không ở trang picking)
                if (!isSelfReport && (isMyOrder || !isOnPickingPage)) {
                    showToast({
                        type: 'shortage',
                        orderCode: extra.orderCode || ('Đơn #' + (ev.orderId || '')),
                        supervisor: extra.supervisor || 'Người phụ trách',
                        reporter: extra.reporter || ev.performerName || 'Nhân sự kho',
                        shortageItems: extra.shortageItems || [],
                        orderId: ev.orderId
                    });
                    playBeep('warning');
                    desktopNotif(
                        '⚠️ Kho báo thiếu hàng – Đơn ' + (extra.orderCode || ev.orderId),
                        'Người phụ trách: ' + (extra.supervisor || '') + '\nBáo bởi: ' + (extra.reporter || ev.performerName || '')
                    );
                }
                window.dispatchEvent(new CustomEvent('erp:shortage-reported', { detail: ev }));

            } else if (ev.eventType === 'ORDER_ITEMS_MODIFIED' || ev.eventType === 'ORDER_UPDATED') {
                const extra = ev.extraData || {};
                // Hiển thị cho: người bốc hàng được phân công VÀ người phụ trách
                // Không hiển thị cho chính người vừa sửa đơn
                const performer = ev.performerName || extra.performer || '';
                const isSelf = performer && CURRENT_USER && performer.includes(CURRENT_USER);

                if (!isSelf && isRelevantToMe(ev)) {
                    showToast({
                        type: 'order_modified',
                        orderCode: extra.orderCode || ('Đơn #' + (ev.orderId || '')),
                        supervisor: extra.supervisor || extra.orderCreator || '',
                        reporter: performer || 'Kinh doanh',
                        changes: extra.changes || [],
                        orderId: ev.orderId
                    });
                    playBeep('info');
                    desktopNotif(
                        '🔔 Cập nhật đơn hàng ' + (extra.orderCode || ev.orderId),
                        'Bởi: ' + (performer || 'Kinh doanh') + '\n' + (extra.changes || []).slice(0, 2).map(c => c.message || '').join('\n')
                    );
                }
                window.dispatchEvent(new CustomEvent('erp:order-modified', { detail: ev }));
            }
        }

        /* ─── Core: Polling ─────────────────────────────────────────── */
        async function poll() {
            if (isPolling) return;
            isPolling = true;
            try {
                const res = await fetch('/api/inventory/picking/events?since_id=' + lastSyncId);
                if (!res.ok) return;
                const data = await res.json();
                if (data.success && Array.isArray(data.events) && data.events.length > 0) {
                    lastSyncId = Number(data.latest_id) || lastSyncId;
                    data.events.forEach(handleEvent);
                }
            } catch (e) {
                // Bỏ qua lỗi mạng tạm thời
            } finally {
                isPolling = false;
            }
        }

        // Khởi động nạp ngay các yêu cầu bảo lãnh nợ & polling
        setTimeout(checkPendingCreditOverrides, 300);
        setTimeout(poll, 800);
        setInterval(poll, POLL_INTERVAL);
        setInterval(checkPendingCreditOverrides, 10000); // kiểm tra định kỳ mỗi 10s tránh sót đơn

        /* ─── BroadcastChannel: phản ứng 0ms giữa các tab ─────────── */
        try {
            const bc = new BroadcastChannel('erp_order_sync');
            bc.onmessage = function (e) {
                const d = e.data;
                if (!d) return;
                if (d.type === 'CREDIT_OVERRUN_REQUESTED') {
                    checkPendingCreditOverrides();
                    poll();
                } else if (d.type === 'SHORTAGE_REPORTED' || d.type === 'CREDIT_OVERRUN_DECIDED') {
                    poll();
                } else if (d.type === 'ORDER_SAVED' || d.type === 'ORDER_MODIFIED') {
                    poll();
                }
            };
        } catch (e) {}

        /* ─── Storage Event: dự phòng đa tab Safari / Firefox ───────── */
        window.addEventListener('storage', function (e) {
            if (e.key === 'erp_order_sync_trigger') {
                checkPendingCreditOverrides();
                poll();
            }
        });

        /* ─── Yêu cầu quyền thông báo hệ điều hành ─────────────────── */
        if (window.Notification && Notification.permission === 'default') {
            document.addEventListener('click', function askOnce() {
                Notification.requestPermission();
                document.removeEventListener('click', askOnce);
            }, { once: true });
        }

        /* ─── CSS animation cho toast ────────────────────────────────── */
        const style = document.createElement('style');
        style.textContent = `
            @keyframes erpSlideIn {
                from { opacity: 0; transform: translateX(30px) scale(.96); }
                to   { opacity: 1; transform: translateX(0) scale(1); }
            }
        `;
        document.head.appendChild(style);

    })();
    </script>

    @include('layouts.notifications')

    @stack('scripts')
</body>
</html>
