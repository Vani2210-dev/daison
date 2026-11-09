<!-- TAB: DANH SÁCH ĐƠN BẢO DƯỠNG & THAY LÕI CHO KỸ THUẬT VIÊN -->
<div class="space-y-4">
    <!-- Filter & Sub-tab Bar -->
    <div class="maintenance-filters-card bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-2.5">
        <div class="flex items-center gap-2">
            <!-- Search Bar -->
            <form action="{{ url('/tasks') }}" method="GET" class="flex-1 min-w-0">
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="tab" value="maintenance">
                @if(request('maint_filter'))
                    <input type="hidden" name="maint_filter" value="{{ request('maint_filter') }}">
                @endif
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Tìm khách, SĐT, máy, serial..." 
                           class="w-full pl-8 {{ request('search') ? 'pr-20' : 'pr-14' }} py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-purple-500 font-medium">
                    @if(request('search'))
                        <a href="{{ url('/tasks?view=' . $view . '&tab=maintenance' . (request('maint_filter') ? '&maint_filter='.request('maint_filter') : '')) }}" 
                           class="absolute right-14 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1"
                           title="Xóa tìm kiếm">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                    <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                        Tìm
                    </button>
                </div>
            </form>

            <a href="{{ url('/tasks?view=' . $view . '&tab=maintenance') }}" class="px-2.5 sm:px-3 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition shrink-0 flex items-center justify-center gap-1 shadow-2xs whitespace-nowrap min-h-[36px]" title="Làm mới bộ lọc">
                <i class="fa-solid fa-rotate-left text-xs"></i>
                <span>Làm mới</span>
            </a>
        </div>

        <!-- Filter Sub-Pills -->
        <div class="maintenance-pills-row flex items-center gap-2 overflow-x-auto text-xs font-bold select-none pt-2 border-t border-slate-100">
            <a href="{{ url('/tasks?view=' . $view . '&tab=maintenance') }}" 
               class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap {{ !request('maint_filter') ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>Tất Cả Lịch Hẹn</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ !request('maint_filter') ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $allMaintCount }}
                </span>
            </a>

            <!-- Nút lọc đơn chưa ai nhận (BẤM ĐỂ TỰ NHẬN VIỆC) -->
            <a href="{{ url('/tasks?view=' . $view . '&tab=maintenance&maint_filter=unclaimed') }}" 
               class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1.5 {{ request('maint_filter') === 'unclaimed' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                <i class="fa-solid fa-hand-holding-hand text-xs"></i>
                <span>Đơn Chưa Ai Nhận</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ request('maint_filter') === 'unclaimed' ? 'bg-white text-amber-800' : 'bg-amber-200 text-amber-900' }}">
                    {{ $unclaimedMaintCount }}
                </span>
            </a>

            <a href="{{ url('/tasks?view=' . $view . '&tab=maintenance&maint_filter=overdue') }}" 
               class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap {{ request('maint_filter') === 'overdue' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' }}">
                <span>🚨 Quá Hạn Bảo Dưỡng</span>
            </a>

            <a href="{{ url('/tasks?view=' . $view . '&tab=maintenance&maint_filter=due_soon') }}" 
               class="px-3.5 py-1.5 rounded-xl transition-all whitespace-nowrap {{ request('maint_filter') === 'due_soon' ? 'bg-sky-600 text-white shadow-xs' : 'bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100' }}">
                <span>⏳ Đến Hạn Trong 30 Ngày</span>
            </a>
        </div>
    </div>

    <!-- Empty State -->
    @if($maintenanceMachines->count() === 0)
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">Không Có Đơn Bảo Dưỡng Nào</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">Hiện tại chưa có đơn bảo dưỡng nào phù hợp với bộ lọc này. Hãy kiểm tra lại các mục khác hoặc làm mới bộ lọc.</p>
        </div>
    @else
        <!-- Cards List -->
        <div class="space-y-3">
            @foreach($maintenanceMachines as $m)
                @php
                    $isOverdue = $m->next_maintenance_date && $m->next_maintenance_date->lt(now()->startOfDay());
                    $daysRemaining = $m->next_maintenance_date ? (int) now()->startOfDay()->diffInDays($m->next_maintenance_date, false) : null;
                    $isAssignedToMe = Auth::check() && $m->technician_id == Auth::id();
                    $isUnclaimed = empty($m->technician_id);
                    $accessories = $m->accessories_list ?? [];
                @endphp
                <div class="maintenance-item-card bg-white rounded-2xl border transition-all hover:shadow-md p-4 sm:p-5 {{ $isAssignedToMe ? 'border-purple-300 ring-2 ring-purple-500/10' : ($isOverdue ? 'border-rose-200 bg-rose-50/10' : 'border-slate-200/90') }}"
                     id="maint-card-{{ $m->id }}">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <!-- Left Info Column -->
                        <div class="space-y-2.5 flex-1 min-w-0">
                            <!-- Status Badges Row -->
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Trạng thái KTV nhận đơn -->
                                @if($isAssignedToMe)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Bạn đang phụ trách đơn này</span>
                                    </span>
                                @elseif($isUnclaimed)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                                        <i class="fa-solid fa-hand-holding-hand text-[10px]"></i>
                                        <span>Chưa có KTV nhận &bull; Hãy nhận đơn</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="fa-solid fa-user-gear text-[10px]"></i>
                                        <span>KTV: {{ $m->technician_name ?: ($m->technician?->name ?? 'KTV khác') }}</span>
                                    </span>
                                @endif

                                <!-- Badge Hạn bảo dưỡng -->
                                @if($isOverdue)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 border border-rose-300">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                        <span>Quá hạn {{ abs($daysRemaining) }} ngày</span>
                                    </span>
                                @elseif($daysRemaining !== null && $daysRemaining <= 7)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                        <i class="fa-solid fa-clock text-[10px]"></i>
                                        <span>Đến hạn! Còn {{ $daysRemaining }} ngày</span>
                                    </span>
                                @elseif($daysRemaining !== null)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-300">
                                        <i class="fa-solid fa-hourglass-start text-[10px]"></i>
                                        <span>Còn {{ $daysRemaining }} ngày</span>
                                    </span>
                                @endif

                                <!-- QR token / code -->
                                @if($m->qr_code)
                                    <span class="font-mono text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200/60">
                                        QR: {{ $m->qr_code }}
                                    </span>
                                @endif
                            </div>

                            <!-- Customer Info -->
                            <div>
                                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                                    <span>{{ $m->customer_name }}</span>
                                    @if($m->qr_token)
                                        <a href="{{ url('/m/' . $m->qr_token) }}" target="_blank" title="Mở Passport bảo hành điện tử" class="text-sky-600 hover:text-sky-700 text-xs font-normal">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    @endif
                                </h3>

                                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-600">
                                    @if($m->customer_phone)
                                        <div class="flex items-center gap-1.5">
                                            <a href="tel:{{ $m->customer_phone }}" class="font-mono font-bold text-emerald-700 hover:underline flex items-center gap-1">
                                                <i class="fa-solid fa-phone text-xs"></i>
                                                <span>{{ $m->customer_phone }}</span>
                                            </a>
                                            <a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $m->customer_phone) }}" target="_blank" class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold text-[10px] transition border border-blue-200">
                                                Chat Zalo
                                            </a>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-1 text-slate-500">
                                        <i class="fa-solid fa-location-dot text-slate-400"></i>
                                        <span>{{ $m->customer_address ?: 'Chưa có địa chỉ' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Machine Model & Specifications -->
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 space-y-2 text-xs">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-faucet-drip text-purple-600"></i>
                                        <span>Model: {{ $m->machine_model ?: 'Chưa gán model' }}</span>
                                        @if($m->machine_serial)
                                            <span class="font-mono font-normal text-slate-500 text-[11px] bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                                SN: {{ $m->machine_serial }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-[11px] text-slate-500">
                                        Kỳ bảo dưỡng: <strong class="text-slate-800">{{ $m->filter_replacement_months ?: 6 }} Tháng/Lần</strong>
                                        &bull; Hẹn tới: <strong class="text-purple-700 font-mono">{{ $m->next_maintenance_date ? $m->next_maintenance_date->format('d/m/Y') : 'Chưa đặt' }}</strong>
                                    </div>
                                </div>

                                <!-- Danh Sách Phụ Tùng & Thời Hạn Bảo Dưỡng Từng Món -->
                                @if(!empty($accessories) && count($accessories) > 0)
                                    <div class="pt-2 border-t border-slate-200/60">
                                        <div class="text-[11px] font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                                            <i class="fa-solid fa-boxes-stacked text-teal-600"></i>
                                            <span>Phụ tùng / Lõi lọc & Thời hạn bảo dưỡng từng món:</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($accessories as $acc)
                                                @php
                                                    $partName = is_array($acc) ? ($acc['name'] ?? 'Phụ tùng') : (is_object($acc) ? ($acc->name ?? 'Phụ tùng') : $acc);
                                                    $partQty = is_array($acc) ? ($acc['quantity'] ?? 1) : 1;
                                                    $partUnit = is_array($acc) ? ($acc['unit'] ?? 'Cái') : 'Cái';
                                                    $partDate = is_array($acc) ? ($acc['installed_date'] ?? null) : null;
                                                    $partMaint = is_array($acc) ? ($acc['maintenance_months'] ?? ($m->filter_replacement_months ?: 6)) : 6;
                                                    $hasMaint = $partMaint !== null && (int)$partMaint > 0;
                                                @endphp
                                                <div class="bg-white p-2 rounded-lg border border-slate-200/80 flex items-center justify-between gap-2 shadow-2xs">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="font-bold text-slate-800 text-[11px] truncate" title="{{ $partName }}">
                                                            {{ $partName }}
                                                        </div>
                                                        <div class="text-[10px] text-slate-500 mt-0.5">
                                                            SL: <strong>{{ $partQty }} {{ $partUnit }}</strong>
                                                            @if($partDate)
                                                                &bull; Lắp: {{ date('d/m/Y', strtotime($partDate)) }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <!-- Badge thời hạn bảo dưỡng từng phụ tùng -->
                                                    @if($hasMaint)
                                                        <span class="px-2 py-1 rounded-md bg-purple-50 text-purple-800 border border-purple-200 font-bold text-[10px] shrink-0 text-center">
                                                            Hạn: {{ $partMaint }} Tháng
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 rounded-md bg-slate-100 text-slate-500 border border-slate-200 font-semibold text-[10px] shrink-0 text-center" title="Phụ kiện cố định / Không thay thế định kỳ">
                                                            Không định kỳ
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($m->modelCatalog && !empty($m->modelCatalog->default_parts))
                                    <!-- Lõi lọc mặc định theo model -->
                                    <div class="pt-1.5 border-t border-slate-200/60 text-[11px] text-teal-700">
                                        <span class="font-bold">Lõi lọc tiêu chuẩn theo model: </span>
                                        <span>{{ implode(', ', array_map(fn($p) => $p['name'] ?? $p['part_name'] ?? '', array_slice($m->modelCatalog->default_parts, 0, 4))) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Actions Column (NÚT TỰ NHẬN ĐƠN BẢO DƯỠNG) -->
                        <div class="maintenance-actions-col flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-start gap-2.5 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                            <!-- NÚT HÀNH ĐỘNG CHÍNH -->
                            @if(!$isAssignedToMe)
                                <button type="button" 
                                        @click="claimMaintenanceJob({{ $m->id }}, '{{ addslashes($m->customer_name) }}')" 
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black text-xs shadow-md shadow-purple-600/30 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer">
                                    <i class="fa-solid fa-hand-holding-hand text-sm"></i>
                                    <span>{{ $isUnclaimed ? 'Tự Nhận Đơn Này' : 'Nhận Thay Đơn Này' }}</span>
                                </button>
                            @else
                                <div class="flex flex-col items-stretch sm:items-end gap-1 w-full sm:w-auto">
                                    <button type="button" 
                                            @click="openCompleteMaintenanceModal({{ json_encode([
                                                'id' => $m->id,
                                                'customer_name' => $m->customer_name,
                                                'customer_phone' => $m->customer_phone,
                                                'customer_address' => $m->customer_address,
                                                'machine_model' => $m->machine_model,
                                                'machine_serial' => $m->machine_serial,
                                                'filter_replacement_months' => $m->filter_replacement_months ?: 6,
                                                'accessories' => $m->accessories_list,
                                                'default_parts' => $m->modelCatalog?->default_parts ?? [],
                                            ]) }})" 
                                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer"
                                            title="Bấm để ghi nhận thay lõi / tính tiền phụ tùng và hoàn tất">
                                        <i class="fa-solid fa-circle-check text-sm"></i>
                                        <span>Đã Bảo Dưỡng Xong</span>
                                    </button>
                                    <span class="text-[10px] text-emerald-700 font-bold flex items-center gap-1 justify-end">
                                        <i class="fa-solid fa-user-check"></i> Đang phụ trách
                                    </span>
                                </div>
                            @endif

                            <!-- Các nút thao tác phụ trợ -->
                            <div class="sub-buttons-row flex items-center gap-1.5">
                                <!-- Gọi điện -->
                                @if($m->customer_phone)
                                    <a href="tel:{{ $m->customer_phone }}" class="p-2 rounded-xl bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 transition" title="Gọi khách hàng">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                    </a>
                                @endif

                                <!-- Chat Zalo -->
                                @if($m->customer_phone)
                                    <a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $m->customer_phone) }}" target="_blank" class="p-2 rounded-xl bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-600 transition" title="Mở Zalo">
                                        <i class="fa-solid fa-comment-dots text-xs"></i>
                                    </a>
                                @endif

                                <!-- Xem Sổ bảo hành điện tử & Lịch sử bảo dưỡng (Passport QR) -->
                                @if($m->qr_token || $m->qr_code)
                                    <a href="{{ url('/m/' . ($m->qr_token ?: $m->qr_code)) }}" target="_blank" class="p-2 rounded-xl bg-slate-100 hover:bg-purple-50 hover:text-purple-700 text-slate-600 transition" title="Xem Sổ Bảo Hành Điện Tử & Lịch Sử Máy">
                                        <i class="fa-solid fa-qrcode text-xs"></i>
                                    </a>
                                @endif

                                <!-- Dời / Hẹn lại ngày bảo dưỡng -->
                                <button type="button" 
                                        @click="rescheduleMaintenanceJob({{ $m->id }}, '{{ addslashes($m->customer_name) }}', '{{ $m->next_maintenance_date ? (is_string($m->next_maintenance_date) ? substr($m->next_maintenance_date, 0, 10) : $m->next_maintenance_date->format('Y-m-d')) : '' }}')" 
                                        class="p-2 rounded-xl bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-600 transition cursor-pointer" 
                                        title="Dời / Hẹn lại ngày bảo dưỡng">
                                    <i class="fa-regular fa-calendar-plus text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $maintenanceMachines->links() }}
        </div>
    @endif
</div>
