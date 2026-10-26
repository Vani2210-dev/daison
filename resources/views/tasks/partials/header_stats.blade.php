    <!-- Top Action & View Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 text-lg">
                    <i class="fa-solid fa-list-check"></i>
                </span>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>{{ $view === 'my_tasks' ? 'Công Việc Của Tôi' : 'Quản Lý Công Việc & Phân Công' }}</span>
                        @if($view === 'my_tasks')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                Kỹ Thuật Viên: {{ Auth::user()?->name ?? 'KTV' }}
                            </span>
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">Theo dõi các đơn lắp đặt máy lọc nước, cài đặt kích hoạt tem QR và tiến độ công việc.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- View Switch Tabs -->
            <div class="grid grid-cols-2 sm:flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-bold">
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

            <!-- Fast QR Scan Button -->
            <button type="button" 
                    id="btn-scan-qr-top"
                    @click="openQrModal(null)" 
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer">
                <i class="fa-solid fa-qrcode text-base"></i>
                <span>Quét Mã QR Cài Đặt Máy</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
        <!-- Card 1: Pending installations -->
        <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent p-4 rounded-2xl border border-amber-200/80 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shadow-md shadow-amber-500/30 shrink-0">
                <i class="fa-solid fa-wrench"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Đơn Lắp Cần Làm</div>
                <div class="text-2xl font-black text-amber-950 mt-0.5">
                    {{ $view === 'my_tasks' ? $myInstallPending : $allInstallPending }}
                </div>
            </div>
        </div>

        <!-- Card 2: Completed installations -->
        <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent p-4 rounded-2xl border border-emerald-200/80 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg shadow-md shadow-emerald-500/30 shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Đã Kích Hoạt QR</div>
                <div class="text-2xl font-black text-emerald-950 mt-0.5">
                    {{ $view === 'my_tasks' ? $myInstallCompleted : $allInstallCompleted }}
                </div>
            </div>
        </div>

        <!-- Card 3: Maintenance Jobs -->
        <div class="bg-gradient-to-br from-purple-500/10 via-purple-500/5 to-transparent p-4 rounded-2xl border border-purple-200/80 flex items-center gap-3.5 cursor-pointer hover:border-purple-400 transition" onclick="window.location='{{ url('/tasks?view=' . $view . '&tab=maintenance') }}'">
            <div class="w-11 h-11 rounded-xl bg-purple-600 text-white flex items-center justify-center text-lg shadow-md shadow-purple-600/30 shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-purple-800 uppercase tracking-wider">Đơn Bảo Dưỡng</div>
                <div class="text-2xl font-black text-purple-950 mt-0.5 flex items-baseline gap-1.5">
                    <span>{{ $view === 'my_tasks' ? $myMaintCount : $allMaintCount }}</span>
                    @if($unclaimedMaintCount > 0)
                        <span class="text-[10px] font-bold bg-amber-100 text-amber-800 px-1.5 py-0.2 rounded-full border border-amber-300">
                            {{ $unclaimedMaintCount }} chưa nhận
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 4: Blank QR Available -->
        <div class="bg-gradient-to-br from-indigo-500/10 via-indigo-500/5 to-transparent p-4 rounded-2xl border border-indigo-200/80 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-indigo-500 text-white flex items-center justify-center text-lg shadow-md shadow-indigo-500/30 shrink-0">
                <i class="fa-solid fa-barcode"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-indigo-800 uppercase tracking-wider">Tem QR Sẵn Sàng</div>
                <div class="text-2xl font-black text-indigo-950 mt-0.5">
                    {{ $blankQrCodes->count() }}
                </div>
            </div>
        </div>

        <!-- Card 5: Other Tasks -->
        <div class="bg-gradient-to-br from-sky-500/10 via-sky-500/5 to-transparent p-4 rounded-2xl border border-sky-200/80 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-sky-500 text-white flex items-center justify-center text-lg shadow-md shadow-sky-500/30 shrink-0">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-sky-800 uppercase tracking-wider">Nhiệm Vụ Khác</div>
                <div class="text-2xl font-black text-sky-950 mt-0.5">
                    {{ $tasks->total() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Phân Tách Từng Trang -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-b border-slate-200 pb-2">
        <div class="flex items-center gap-2 overflow-x-auto">
            <a href="{{ url('/tasks?view=' . $view . '&tab=installations') }}" 
               class="flex items-center gap-2.5 px-5 py-3 rounded-2xl font-black text-xs sm:text-sm transition-all shadow-xs cursor-pointer {{ $activeTab === 'installations' ? 'bg-blue-600 text-white shadow-blue-500/25' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 hover:text-blue-600' }}">
                <i class="fa-solid fa-faucet-drip text-sm {{ $activeTab === 'installations' ? 'text-white' : 'text-blue-600' }}"></i>
                <span>Đơn Lắp Đặt Máy Lọc Nước</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $activeTab === 'installations' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">
                    {{ $filterInstallations->total() }} đơn
                </span>
            </a>

            <a href="{{ url('/tasks?view=' . $view . '&tab=maintenance') }}" 
               class="flex items-center gap-2.5 px-5 py-3 rounded-2xl font-black text-xs sm:text-sm transition-all shadow-xs cursor-pointer {{ $activeTab === 'maintenance' ? 'bg-purple-600 text-white shadow-purple-500/25' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 hover:text-purple-600' }}">
                <i class="fa-solid fa-calendar-check text-sm {{ $activeTab === 'maintenance' ? 'text-white' : 'text-purple-600' }}"></i>
                <span>Đơn Bảo Dưỡng & Thay Lõi</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $activeTab === 'maintenance' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800' }}">
                    {{ $view === 'my_tasks' ? $myMaintCount : $allMaintCount }} máy
                </span>
                @if($unclaimedMaintCount > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white animate-pulse" title="Có đơn chưa ai nhận">
                        {{ $unclaimedMaintCount }} cần nhận
                    </span>
                @endif
            </a>

            <a href="{{ url('/tasks?view=' . $view . '&tab=tasks') }}" 
               class="flex items-center gap-2.5 px-5 py-3 rounded-2xl font-black text-xs sm:text-sm transition-all shadow-xs cursor-pointer {{ $activeTab === 'tasks' ? 'bg-indigo-600 text-white shadow-indigo-500/25' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 hover:text-indigo-600' }}">
                <i class="fa-solid fa-tasks text-sm {{ $activeTab === 'tasks' ? 'text-white' : 'text-indigo-600' }}"></i>
                <span>Nhiệm Vụ & Phân Công Nội Bộ</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $activeTab === 'tasks' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-800' }}">
                    {{ $tasks->total() }} việc
                </span>
            </a>

            <a href="{{ url('/tasks?view=' . $view . '&tab=warehouse_export') }}" 
               class="flex items-center gap-2.5 px-5 py-3 rounded-2xl font-black text-xs sm:text-sm transition-all shadow-xs cursor-pointer {{ $activeTab === 'warehouse_export' ? 'bg-emerald-600 text-white shadow-emerald-500/25' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 hover:text-emerald-600' }}">
                <i class="fa-solid fa-boxes-packing text-sm {{ $activeTab === 'warehouse_export' ? 'text-white' : 'text-emerald-600' }}"></i>
                <span>Lấy Hàng Từ Kho (Đi Làm)</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $activeTab === 'warehouse_export' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $myExportCount }} đợt lấy
                </span>
            </a>
        </div>
    </div>