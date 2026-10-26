    <!-- Section 1: Water Filter Installation Orders -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-faucet-drip text-blue-600"></i>
                    <span>Đơn Hàng Lắp Đặt Máy Lọc Nước</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        {{ $filterInstallations->total() }} đơn
                    </span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Danh sách đơn hàng cần đi lắp đặt thực tế tại nhà khách hàng & kích hoạt tem QR điện tử.</p>
            </div>

            <!-- Search Filter for installations (Hỗ trợ tìm trực tiếp hoặc gõ Enter) -->
            <form method="GET" action="{{ url('/tasks') }}" class="flex items-center gap-2">
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
                         class="p-5 hover:bg-blue-50/30 transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                        
                        <!-- Left Info -->
                        <div class="space-y-2 flex-1">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $inst->code }}
                                </span>

                                @if($isCompleted)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                        <i class="fa-solid fa-check-circle"></i>
                                        <span>Đã Hoàn Thành Lắp Đặt</span>
                                    </span>
                                @elseif($inst->status === 'in_progress')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1">
                                        <i class="fa-solid fa-spinner fa-spin"></i>
                                        <span>Đang Tiến Hành Lắp</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                        <i class="fa-solid fa-clock"></i>
                                        <span>Cần Lắp Đặt</span>
                                    </span>
                                @endif

                                @if($hasMachine && $hasMachine->status === 'activated')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 flex items-center gap-1">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>Đã Kích Hoạt QR: {{ $hasMachine->qr_code }}</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span>Chưa Gán Tem QR</span>
                                    </span>
                                @endif
                            </div>

                            <!-- Customer & Machine details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                                        <span>{{ $inst->customer_name }}</span>
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1 flex items-center gap-2">
                                        <i class="fa-solid fa-phone text-emerald-600 text-xs"></i>
                                        <a href="tel:{{ $inst->customer_phone }}" class="font-bold text-emerald-700 hover:underline">
                                            {{ $inst->customer_phone }}
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <span class="text-slate-500">Gọi điện xác nhận hẹn</span>
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1 flex items-start gap-2">
                                        <i class="fa-solid fa-location-dot text-rose-500 text-xs mt-0.5 shrink-0"></i>
                                        <span>{{ $inst->customer_address }}</span>
                                    </div>
                                </div>

                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1 text-xs">
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-microchip text-blue-600"></i>
                                        <span>Model: {{ $inst->machine_model }}</span>
                                    </div>
                                    <div class="text-slate-500 flex items-center gap-1.5">
                                        <i class="fa-solid fa-calendar-day text-slate-400"></i>
                                        <span>Hẹn lắp: <strong class="text-slate-700">{{ $timeText }}</strong></span>
                                    </div>
                                    @if($inst->technician_name)
                                        <div class="text-slate-500 flex items-center gap-1.5">
                                            <i class="fa-solid fa-user-gear text-slate-400"></i>
                                            <span>Kỹ thuật viên: <strong class="text-sky-700">{{ $inst->technician_name }}</strong></span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end justify-end gap-2 shrink-0 pt-2 lg:pt-0 w-full lg:w-auto">
                            <!-- Button: Quét QR & Cài Đặt Máy (Chỉ hiển thị với đơn chưa gán tem QR) -->
                            @if(!$isCompleted && (!$hasMachine || $hasMachine->status !== 'activated'))
                                <button type="button" 
                                        id="btn-scan-qr-order-{{ $inst->id }}"
                                        @click="openQrModal({{ $inst->id }})" 
                                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-emerald-600/20 hover:scale-[1.02]">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                    <span>Quét Mã QR Cài Đặt Máy</span>
                                </button>
                            @endif

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                @if($hasMachine && $hasMachine->qr_token)
                                    <a href="{{ url('/m/' . $hasMachine->qr_token) }}" 
                                       target="_blank" 
                                       class="flex-1 sm:flex-none px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-blue-700 hover:bg-blue-50 border border-slate-200 flex items-center justify-center gap-1.5 transition-all text-center">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                        <span>Hồ Sơ Bảo Hành</span>
                                    </a>
                                @endif

                                <a href="{{ url('/water-filter/installations/' . $inst->id . '/edit') }}" 
                                   class="flex-1 sm:flex-none px-3 py-2 rounded-xl text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 text-center">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    <span>Chi tiết đơn</span>
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