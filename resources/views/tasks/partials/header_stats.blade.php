    <!-- Top Action & View Bar -->
    <div class="tasks-header-card bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
        <div class="w-full md:w-auto">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <span class="header-icon-box w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 text-base sm:text-lg shrink-0">
                    <i class="fa-solid fa-list-check"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <h1 class="text-base sm:text-xl font-black text-slate-900 tracking-tight whitespace-nowrap">
                        {{ $view === 'my_tasks' ? 'Công Việc Của Tôi' : 'Quản Lý Công Việc & Phân Công' }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5 hidden sm:block">Theo dõi các đơn lắp đặt máy lọc nước, cài đặt kích hoạt tem QR và tiến độ công việc.</p>
                </div>
            </div>
        </div>

        <div class="tasks-header-actions flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- View Switch Tabs (Chỉ người có quyền xem tất cả mới có nút chuyển đổi) -->
            @if(!empty($canViewAll) && $canViewAll)
            <div class="tasks-view-switch grid grid-cols-2 sm:flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-bold">
                <a href="{{ url('/tasks?view=my_tasks&tab=' . $activeTab) }}" 
                   class="px-3.5 py-2 rounded-lg transition-all flex items-center justify-center gap-2 {{ $view === 'my_tasks' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-user-check text-emerald-600"></i>
                    <span>Công việc của tôi</span>
                    @if($myInstallPending > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-500 text-white">
                            {{ $myInstallPending }}
                        </span>
                    @endif
                </a>
                <a href="{{ url('/tasks?view=all&tab=' . $activeTab) }}" 
                   class="px-3.5 py-2 rounded-lg transition-all flex items-center justify-center gap-2 {{ $view !== 'my_tasks' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-layer-group text-slate-500"></i>
                    <span>Tất cả công việc</span>
                </a>
            </div>
            @endif

            <!-- Fast QR Scan Button (Chỉ hiển thị khi có quyền xem Đơn Lắp Đặt) -->
            @if(!empty($canViewInstallations) && $canViewInstallations)
            <button type="button" 
                    id="btn-scan-qr-top"
                    @click="openQrModal(null)" 
                    class="hidden sm:flex w-auto px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer">
                <i class="fa-solid fa-qrcode text-base"></i>
                <span>Quét Mã QR Cài Đặt Máy</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards (Ẩn trên điện thoại, hiển thị từ máy tính bảng/desktop md:grid) -->
    <div class="quick-stats-grid hidden md:grid grid-cols-2 lg:grid-cols-5 gap-3.5">
        <!-- Card 1: Pending installations -->
        @if(!empty($canViewInstallations) && $canViewInstallations)
        <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent p-4 rounded-2xl border border-amber-200/80 flex items-center gap-3.5 cursor-pointer hover:border-amber-400 transition" onclick="window.location='{{ url('/tasks?view=' . $view . '&tab=installations') }}'">
            <div class="stat-icon w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shadow-md shadow-amber-500/30 shrink-0">
                <i class="fa-solid fa-wrench"></i>
            </div>
            <div>
                <div class="stat-title text-[11px] font-bold text-amber-800 uppercase tracking-wider">Đơn Lắp Cần Làm</div>
                <div class="stat-number text-2xl font-black text-amber-950 mt-0.5">
                    {{ $view === 'my_tasks' ? $myInstallPending : $allInstallPending }}
                </div>
            </div>
        </div>

        <!-- Card 2: Completed installations -->
        <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent p-4 rounded-2xl border border-emerald-200/80 flex items-center gap-3.5 cursor-pointer hover:border-emerald-400 transition" onclick="window.location='{{ url('/tasks?view=' . $view . '&tab=installations') }}'">
            <div class="stat-icon w-11 h-11 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg shadow-md shadow-emerald-500/30 shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-title text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Đã Kích Hoạt QR</div>
                <div class="stat-number text-2xl font-black text-emerald-950 mt-0.5">
                    {{ $view === 'my_tasks' ? $myInstallCompleted : $allInstallCompleted }}
                </div>
            </div>
        </div>
        @endif

        <!-- Card 3: Maintenance Jobs -->
        @if(!empty($canViewMaintenance) && $canViewMaintenance)
        <div class="bg-gradient-to-br from-purple-500/10 via-purple-500/5 to-transparent p-4 rounded-2xl border border-purple-200/80 flex items-center gap-3.5 cursor-pointer hover:border-purple-400 transition" onclick="window.location='{{ url('/tasks?view=' . $view . '&tab=maintenance') }}'">
            <div class="stat-icon w-11 h-11 rounded-xl bg-purple-600 text-white flex items-center justify-center text-lg shadow-md shadow-purple-600/30 shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div class="stat-title text-[11px] font-bold text-purple-800 uppercase tracking-wider">Đơn Bảo Dưỡng</div>
                <div class="stat-number text-2xl font-black text-purple-950 mt-0.5 flex items-baseline gap-1.5 flex-wrap">
                    <span>{{ $view === 'my_tasks' ? $myMaintCount : $allMaintCount }}</span>
                    @if($unclaimedMaintCount > 0)
                        <span class="text-[9px] font-bold bg-amber-100 text-amber-800 px-1.5 py-0.2 rounded-full border border-amber-300">
                            {{ $unclaimedMaintCount }} chưa nhận
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Card 4: Blank QR Available -->
        @if(!empty($canViewInstallations) && $canViewInstallations)
        <div class="bg-gradient-to-br from-indigo-500/10 via-indigo-500/5 to-transparent p-4 rounded-2xl border border-indigo-200/80 flex items-center gap-3.5">
            <div class="stat-icon w-11 h-11 rounded-xl bg-indigo-500 text-white flex items-center justify-center text-lg shadow-md shadow-indigo-500/30 shrink-0">
                <i class="fa-solid fa-barcode"></i>
            </div>
            <div>
                <div class="stat-title text-[11px] font-bold text-indigo-800 uppercase tracking-wider">Tem QR Sẵn Sàng</div>
                <div class="stat-number text-2xl font-black text-indigo-950 mt-0.5">
                    {{ $blankQrCodes->count() }}
                </div>
            </div>
        </div>
        @endif

        <!-- Card 5: Other Tasks -->
        @if(!empty($canViewTasks) && $canViewTasks)
        <div class="stat-card-5 bg-gradient-to-br from-sky-500/10 via-sky-500/5 to-transparent p-4 rounded-2xl border border-sky-200/80 flex items-center justify-between gap-3.5 cursor-pointer hover:border-sky-400 transition" onclick="window.location='{{ url('/tasks?view=' . $view . '&tab=tasks') }}'">
            <div class="flex items-center gap-3">
                <div class="stat-icon w-11 h-11 rounded-xl bg-sky-500 text-white flex items-center justify-center text-lg shadow-md shadow-sky-500/30 shrink-0">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div>
                    <div class="stat-title text-[11px] font-bold text-sky-800 uppercase tracking-wider">Nhiệm Vụ Khác</div>
                    <div class="text-[10px] text-sky-600 sm:hidden">Việc nội bộ & tồn đọng</div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <div class="stat-number text-2xl font-black text-sky-950">
                    {{ $tasks->total() }}
                </div>
                <i class="fa-solid fa-chevron-right text-sky-400 text-xs sm:hidden ml-1"></i>
            </div>
        </div>
        @endif
    </div>

    <!-- Navigation Tabs Phân Tách Từng Trang -->
    <div class="tasks-nav-tabs-wrapper flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-b border-slate-200 pb-2">
        <div class="tasks-nav-tabs grid grid-cols-4 sm:flex items-center gap-1.5 sm:gap-2 w-full">
            <!-- Tab 1: Đơn Lắp Đặt -->
            @if(!empty($canViewInstallations) && $canViewInstallations)
            <a href="{{ url('/tasks?view=' . $view . '&tab=installations') }}" 
               title="Đơn Lắp Đặt Máy Lọc Nước ({{ $filterInstallations->total() }})"
               class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2.5 px-1.5 sm:px-5 py-2 sm:py-3 rounded-xl sm:rounded-2xl font-black transition-all shadow-xs cursor-pointer {{ $activeTab === 'installations' ? 'bg-blue-600 text-white shadow-blue-500/25 ring-2 ring-blue-600/30' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 hover:text-blue-600' }}">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-faucet-drip text-base sm:text-sm {{ $activeTab === 'installations' ? 'text-white' : 'text-blue-600' }}"></i>
                    <span class="px-1.5 sm:px-2.5 py-0.2 sm:py-0.5 rounded-full text-[10px] sm:text-[11px] font-black {{ $activeTab === 'installations' ? 'bg-white/25 text-white' : 'bg-blue-100 text-blue-800' }}">
                        {{ $filterInstallations->total() }}<span class="hidden sm:inline"> đơn</span>
                    </span>
                </div>
                <span class="hidden sm:inline text-xs sm:text-sm">Đơn Lắp Đặt Máy Lọc Nước</span>
                <span class="text-[10px] font-bold sm:hidden leading-none mt-0.5">Lắp đặt</span>
            </a>
            @endif

            <!-- Tab 2: Đơn Bảo Dưỡng -->
            @if(!empty($canViewMaintenance) && $canViewMaintenance)
            <a href="{{ url('/tasks?view=' . $view . '&tab=maintenance') }}" 
               title="Đơn Bảo Dưỡng & Thay Lõi ({{ $view === 'my_tasks' ? $myMaintCount : $allMaintCount }})"
               class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2.5 px-1.5 sm:px-5 py-2 sm:py-3 rounded-xl sm:rounded-2xl font-black transition-all shadow-xs cursor-pointer relative {{ $activeTab === 'maintenance' ? 'bg-purple-600 text-white shadow-purple-500/25 ring-2 ring-purple-600/30' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 hover:text-purple-600' }}">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-check text-base sm:text-sm {{ $activeTab === 'maintenance' ? 'text-white' : 'text-purple-600' }}"></i>
                    <span class="px-1.5 sm:px-2.5 py-0.2 sm:py-0.5 rounded-full text-[10px] sm:text-[11px] font-black {{ $activeTab === 'maintenance' ? 'bg-white/25 text-white' : 'bg-purple-100 text-purple-800' }}">
                        {{ $view === 'my_tasks' ? $myMaintCount : $allMaintCount }}<span class="hidden sm:inline"> máy</span>
                    </span>
                </div>
                <span class="hidden sm:inline text-xs sm:text-sm">Đơn Bảo Dưỡng & Thay Lõi</span>
                <span class="text-[10px] font-bold sm:hidden leading-none mt-0.5">Bảo dưỡng</span>
                @if($unclaimedMaintCount > 0)
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-amber-400 ring-2 ring-white animate-pulse sm:hidden" title="Có đơn chưa nhận"></span>
                    <span class="hidden sm:inline px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white animate-pulse" title="Có đơn chưa ai nhận">
                        {{ $unclaimedMaintCount }} cần nhận
                    </span>
                @endif
            </a>
            @endif

            <!-- Tab 3: Nhiệm Vụ Nội Bộ -->
            @if(!empty($canViewTasks) && $canViewTasks)
            <a href="{{ url('/tasks?view=' . $view . '&tab=tasks') }}" 
               title="Nhiệm Vụ & Phân Công Nội Bộ ({{ $tasks->total() }})"
               class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2.5 px-1.5 sm:px-5 py-2 sm:py-3 rounded-xl sm:rounded-2xl font-black transition-all shadow-xs cursor-pointer {{ $activeTab === 'tasks' ? 'bg-indigo-600 text-white shadow-indigo-500/25 ring-2 ring-indigo-600/30' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 hover:text-indigo-600' }}">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-tasks text-base sm:text-sm {{ $activeTab === 'tasks' ? 'text-white' : 'text-indigo-600' }}"></i>
                    <span class="px-1.5 sm:px-2.5 py-0.2 sm:py-0.5 rounded-full text-[10px] sm:text-[11px] font-black {{ $activeTab === 'tasks' ? 'bg-white/25 text-white' : 'bg-indigo-100 text-indigo-800' }}">
                        {{ $tasks->total() }}<span class="hidden sm:inline"> việc</span>
                    </span>
                </div>
                <span class="hidden sm:inline text-xs sm:text-sm">Nhiệm Vụ & Phân Công Nội Bộ</span>
                <span class="text-[10px] font-bold sm:hidden leading-none mt-0.5">Nhiệm vụ</span>
            </a>
            @endif

            <!-- Tab 4: Lấy Hàng Từ Kho -->
            @if(!empty($canViewWarehouseExport) && $canViewWarehouseExport)
            <a href="{{ url('/tasks?view=' . $view . '&tab=warehouse_export') }}" 
               title="Lấy Hàng Từ Kho (Đi Làm) ({{ $myExportCount }})"
               class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2.5 px-1.5 sm:px-5 py-2 sm:py-3 rounded-xl sm:rounded-2xl font-black transition-all shadow-xs cursor-pointer {{ $activeTab === 'warehouse_export' ? 'bg-emerald-600 text-white shadow-emerald-500/25 ring-2 ring-emerald-600/30' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 hover:text-emerald-600' }}">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-boxes-packing text-base sm:text-sm {{ $activeTab === 'warehouse_export' ? 'text-white' : 'text-emerald-600' }}"></i>
                    <span class="px-1.5 sm:px-2.5 py-0.2 sm:py-0.5 rounded-full text-[10px] sm:text-[11px] font-black {{ $activeTab === 'warehouse_export' ? 'bg-white/25 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $myExportCount }}<span class="hidden sm:inline"> đợt</span>
                    </span>
                </div>
                <span class="hidden sm:inline text-xs sm:text-sm">Lấy Hàng Từ Kho (Đi Làm)</span>
                <span class="text-[10px] font-bold sm:hidden leading-none mt-0.5">Xuất kho</span>
            </a>
            @endif
        </div>
    </div>