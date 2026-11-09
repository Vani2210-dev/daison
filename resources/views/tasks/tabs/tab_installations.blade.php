    <!-- Section 1: Water Filter Installation Orders -->
    <div class="tasks-tab-card bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="installations-header p-3.5 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4 bg-slate-50/50">
            <div>
                <h2 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-faucet-drip text-blue-600"></i>
                    <span>Đơn Hàng Lắp Đặt Máy Lọc Nước</span>
                    <span class="px-2 py-0.5 sm:px-2.5 rounded-full text-[11px] sm:text-xs font-bold bg-blue-100 text-blue-800">
                        {{ $filterInstallations->total() }} đơn
                    </span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 hidden sm:block">Danh sách đơn hàng cần đi lắp đặt thực tế tại nhà khách hàng & kích hoạt tem QR điện tử.</p>
            </div>

            <!-- Search Filter for installations (Hỗ trợ tìm trực tiếp hoặc gõ Enter) -->
            <form method="GET" action="{{ url('/tasks') }}" class="installations-search-form flex items-center gap-2">
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="tab" value="installations">
                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search"
                           value="{{ request('search') }}"
                           x-model="searchFilter" 
                           placeholder="Tìm tên KH, SĐT, địa chỉ, mã..." 
                           class="w-full pl-8 pr-8 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @if(request('search'))
                        <a href="{{ url('/tasks?view=' . $view . '&tab=installations') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($filterInstallations->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Chưa có đơn lắp đặt nào</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    {{ $view === 'my_tasks' ? 'Bạn hiện không có đơn lắp đặt máy lọc nước nào được phân công.' : 'Hệ thống chưa có đơn lắp đặt máy lọc nước nào.' }}
                </p>
            </div>
        @else
            <!-- Installation Cards Grid -->
            <div class="divide-y divide-slate-100">
                @foreach($filterInstallations as $inst)
                    @php
                        $isCompleted = ($inst->status === 'completed');
                        $hasMachine = $inst->installedMachine;
                        $timeText = $inst->appointment_date ? date('H:i - d/m/Y', strtotime($inst->appointment_date)) : 'Chưa hẹn giờ';
                    @endphp
                    <div x-show="matchesSearch('{{ addslashes($inst->code) }} {{ addslashes($inst->customer_name) }} {{ addslashes($inst->customer_phone) }} {{ addslashes($inst->customer_address) }} {{ addslashes($inst->machine_model) }}')" 
                         class="installation-card p-3.5 sm:p-5 hover:bg-blue-50/30 transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-5">
                        
                        <!-- Left Info -->
                        <div class="space-y-1.5 sm:space-y-2 flex-1 min-w-0">
                            <div class="order-badge-row flex items-center gap-1.5 sm:gap-2.5 flex-wrap">
                                <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg text-[10px] sm:text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $inst->code }}
                                </span>

                                @if($isCompleted)
                                    <span class="px-2 py-0.5 sm:px-2.5 rounded-full text-[10px] sm:text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                        <i class="fa-solid fa-check-circle text-[9px] sm:text-[11px]"></i>
                                        <span>Đã Hoàn Thành</span>
                                    </span>
                                @elseif($inst->status === 'in_progress')
                                    <span class="px-2 py-0.5 sm:px-2.5 rounded-full text-[10px] sm:text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1">
                                        <i class="fa-solid fa-spinner fa-spin text-[9px] sm:text-[11px]"></i>
                                        <span>Đang Lắp</span>
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 sm:px-2.5 rounded-full text-[10px] sm:text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                        <i class="fa-solid fa-clock text-[9px] sm:text-[11px]"></i>
                                        <span>Cần Lắp Đặt</span>
                                    </span>
                                @endif

                                @if($hasMachine && $hasMachine->status === 'activated')
                                    <span class="px-2 py-0.5 sm:px-2.5 rounded-full text-[10px] sm:text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 flex items-center gap-1">
                                        <i class="fa-solid fa-qrcode text-[9px] sm:text-[11px]"></i>
                                        <span>QR: {{ $hasMachine->qr_code }}</span>
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 sm:px-2.5 rounded-full text-[10px] sm:text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation text-[9px] sm:text-[11px]"></i>
                                        <span>Chưa Gán Tem QR</span>
                                    </span>
                                @endif
                            </div>

                            <!-- Customer & Machine details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-3 pt-0.5">
                                <div class="space-y-1 min-w-0">
                                    <div class="text-sm font-black text-slate-900 flex items-center gap-1.5 truncate">
                                        <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                                        <span>{{ $inst->customer_name }}</span>
                                    </div>
                                    <div class="text-xs text-slate-600 flex items-center gap-2 flex-wrap">
                                        <a href="tel:{{ $inst->customer_phone }}" class="customer-phone-pill inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 hover:bg-emerald-100 transition">
                                            <i class="fa-solid fa-phone text-emerald-600 text-[10px]"></i>
                                            <span>{{ $inst->customer_phone }}</span>
                                            <span class="text-[10px] text-emerald-600 font-semibold">(Gọi)</span>
                                        </a>
                                    </div>
                                    <div class="text-[11px] sm:text-xs text-slate-600 flex items-start gap-1.5 pt-0.5">
                                        <i class="fa-solid fa-location-dot text-rose-500 text-xs mt-0.5 shrink-0"></i>
                                        <span class="line-clamp-2 sm:line-clamp-none">{{ $inst->customer_address }}</span>
                                    </div>
                                </div>

                                <div class="bg-slate-50 p-2 sm:p-3 rounded-xl border border-slate-100 space-y-1 text-xs">
                                    <div class="font-bold text-slate-800 flex items-start sm:items-center gap-1.5">
                                        <i class="fa-solid fa-microchip text-blue-600 text-xs mt-0.5 sm:mt-0 shrink-0"></i>
                                        <span class="line-clamp-1 sm:line-clamp-none">Model: <strong class="text-slate-900 font-extrabold">{{ $inst->machine_model }}</strong></span>
                                    </div>
                                    <div class="text-slate-500 flex items-center gap-1.5 text-[11px] sm:text-xs flex-wrap">
                                        <span><i class="fa-solid fa-calendar-day text-slate-400 text-xs mr-1"></i>Hẹn: <strong class="text-slate-700">{{ $timeText }}</strong></span>
                                        @if($inst->technician_name)
                                            <span class="text-slate-300 hidden sm:inline">&bull;</span>
                                            <span class="text-slate-500"><i class="fa-solid fa-user-gear text-slate-400 text-xs mr-1"></i>KTV: <strong class="text-sky-700">{{ $inst->technician_name }}</strong></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="installation-card-actions flex items-center justify-between gap-2 shrink-0 pt-2 border-t border-dashed border-slate-200 sm:border-0 sm:pt-0 w-full lg:w-auto">
                            <!-- Button: Quét QR & Cài Đặt Máy (Chỉ hiển thị với đơn chưa gán tem QR) -->
                            @if(!$isCompleted && (!$hasMachine || $hasMachine->status !== 'activated'))
                                <button type="button" 
                                        id="btn-scan-qr-order-{{ $inst->id }}"
                                        @click="openQrModal({{ $inst->id }})" 
                                        class="flex-1 sm:flex-none px-3.5 py-2 sm:py-2.5 rounded-xl font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-emerald-600/20 active:scale-95 min-h-[38px] sm:min-h-[42px]">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                    <span>Quét Mã QR Cài Đặt Máy</span>
                                </button>
                            @endif

                            <div class="installation-sub-actions flex items-center gap-1.5 shrink-0">
                                @if($hasMachine && $hasMachine->qr_token)
                                    <a href="{{ url('/m/' . $hasMachine->qr_token) }}" 
                                       target="_blank" 
                                       class="px-2.5 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-blue-700 hover:bg-blue-50 border border-slate-200 flex items-center justify-center gap-1 transition-all text-center min-h-[38px]">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                        <span class="hidden sm:inline">Hồ Sơ Bảo Hành</span>
                                    </a>
                                @endif

                                <a href="{{ url('/water-filter/installations/' . $inst->id . '/edit?return_url=' . urlencode(request()->fullUrl())) }}" 
                                   class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all flex items-center justify-center gap-1.5 text-center min-h-[38px]"
                                   title="Xem chi tiết đơn">
                                    <i class="fa-solid fa-pen-to-square text-xs text-slate-500"></i>
                                    <span>Chi tiết</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($filterInstallations->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $filterInstallations->links() }}
            </div>
        @endif
    </div>