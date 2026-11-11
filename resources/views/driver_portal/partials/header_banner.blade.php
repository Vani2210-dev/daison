    <!-- ========================================================================= -->
    <!-- 1. HEADER BANNER CHUYẾN XE (Thiết kế Cockpit Hiện Đại & Tinh Gọn)          -->
    <!-- ========================================================================= -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-2xl sm:rounded-3xl p-3 sm:p-5 border border-slate-800 text-white shadow-2xl space-y-3 sm:space-y-4">
        
        <!-- ========================================================================= -->
        <!-- ROW 1: Tiêu đề trang + Trạng thái xe (1 dòng duy nhất, không vỡ layout)    -->
        <!-- ========================================================================= -->
        <div class="flex items-center justify-between gap-2.5 sm:gap-4">
            
            <!-- Left: Icon xe + Tiêu đề + Ngày tháng -->
            <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-gradient-to-tr from-purple-700 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-purple-600/30 border border-purple-400/30">
                    <i class="fa-solid fa-truck-fast text-sm sm:text-lg"></i>
                </div>

                <div class="min-w-0">
                    <h1 class="text-sm sm:text-lg font-black tracking-tight text-white truncate">
                        Giao Hàng & Thu Tiền
                    </h1>
                    <div class="text-[11px] sm:text-xs text-slate-400 mt-0.5 truncate">
                        {{ \Carbon\Carbon::now()->locale('vi')->isoFormat('dddd, DD/MM/YYYY') }}
                    </div>
                </div>
            </div>

            <!-- Right: Trạng thái xe badge (Gọn gàng cho cả Mobile và Desktop) -->
            <div class="shrink-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-black tracking-wide"
                      :class="isReturning ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : (isDeparted ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-sky-500/20 text-sky-300 border border-sky-500/30')">
                    <span class="w-1.5 h-1.5 rounded-full" :class="isReturning ? 'bg-amber-400 animate-pulse' : (isDeparted ? 'bg-emerald-400 animate-pulse' : 'bg-sky-400')"></span>
                    <span class="hidden sm:inline" x-text="isReturning ? 'TRÊN ĐƯỜNG VỀ' : (isDeparted ? 'ĐANG ĐI GIAO HÀNG' : 'SẴN SÀNG NHẬN CHUYẾN')"></span>
                    <span class="sm:hidden font-bold" x-text="isReturning ? 'Đang về' : (isDeparted ? 'Đang giao' : 'Sẵn sàng')"></span>
                </span>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- ROW 2: Điều khiển xe + Menu Nghiệp Vụ (Ngang hàng 50-50 trên Mobile)       -->
        <!-- ========================================================================= -->
        <div class="relative flex items-center gap-2 pt-0.5" x-data="{ menuOpen: false }" @click.outside="menuOpen = false">
            
            <!-- 1. Chọn / Quản lý Xe phụ trách -->
            <div class="flex-1 sm:flex-none sm:w-64 min-w-0">
                @if(count($vehicles) > 1)
                    <div class="relative flex items-center bg-slate-800/90 hover:bg-slate-800 border border-slate-700/80 rounded-xl px-2.5 h-[38px] text-xs shadow-inner">
                        <i class="fa-solid fa-truck text-purple-400 text-xs shrink-0 mr-1.5"></i>
                        <select @change="changeVehicle($event.target.value)" class="bg-transparent font-bold text-white focus:outline-none cursor-pointer text-xs w-full pr-4 appearance-none truncate">
                            @foreach($vehicles as $v)
                                <option value="{{ $v->plate_number }}" {{ ($selectedPlate ?? '') === $v->plate_number ? 'selected' : '' }} class="bg-slate-900 text-white font-semibold">
                                    {{ $v->plate_number }} ({{ $v->driver?->name ?? 'Lái xe' }})
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 absolute right-2.5 pointer-events-none"></i>
                    </div>
                @else
                    <div class="flex items-center justify-between bg-purple-950/40 border border-purple-500/30 text-purple-200 px-3 h-[38px] rounded-xl text-xs font-bold shadow-inner">
                        <div class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-truck text-purple-400 text-xs shrink-0"></i>
                            <span class="font-mono font-black" x-text="selectedVehicle?.plate_number || '{{ $selectedPlate ?? '' }}'"></span>
                        </div>
                        <span class="text-[10px] font-semibold text-purple-300/80 bg-purple-900/60 px-1.5 py-0.5 rounded">Xe chính</span>
                    </div>
                @endif
            </div>

            <!-- 2. Nút Menu Nghiệp Vụ Chuyến Xe -->
            <div class="flex-1 sm:flex-none min-w-0">
                <button type="button" 
                        @click="menuOpen = !menuOpen"
                        class="w-full sm:w-auto h-[38px] px-3 rounded-xl bg-gradient-to-r from-purple-900/60 via-slate-800 to-indigo-900/60 hover:from-purple-800/80 hover:to-indigo-800/80 border border-purple-500/40 hover:border-purple-400 text-white font-bold text-xs shadow-lg shadow-purple-950/40 flex items-center justify-between sm:justify-start gap-2 transition-all active:scale-[0.98] cursor-pointer">
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="w-5 h-5 rounded-md bg-purple-500/25 text-purple-300 border border-purple-500/40 flex items-center justify-center text-[10px] shrink-0">
                            <i class="fa-solid fa-bars-staggered"></i>
                        </span>
                        <span class="text-slate-100 font-extrabold text-xs tracking-tight truncate">Menu Nghiệp Vụ</span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <template x-if="(returnsList?.length || 0) + (discrepancies?.length || 0) + (holdingStocks?.length || 0) > 0">
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-rose-500 text-white font-mono shadow-xs"
                                  x-text="((returnsList?.length || 0) + (discrepancies?.length || 0) + (holdingStocks?.length || 0))"></span>
                        </template>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
                           :class="menuOpen ? 'rotate-180 text-purple-300' : ''"></i>
                    </div>
                </button>
            </div>

            <!-- Dropdown Menu Nghiệp Vụ (7 Nghiệp Vụ Chuyến Xe) - Nền đen tuyệt đối 100% không mờ, không trong suốt -->
            <div x-show="menuOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                 class="absolute left-0 right-0 sm:left-auto sm:right-0 top-full mt-2 w-full sm:w-84 rounded-2xl shadow-2xl p-2.5 z-[9999] space-y-1.5 select-none max-h-[82vh] overflow-y-auto"
                 style="display: none; background-color: #0b1120 !important; background: #0b1120 !important; border: 2px solid #334155 !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.95), 0 0 0 1px rgba(255, 255, 255, 0.15) !important;">
                
                <div class="px-2.5 py-1 text-[11px] uppercase font-black tracking-wider text-indigo-300 flex items-center justify-between border-b border-slate-800/80 pb-1.5 mb-1">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-boxes-packing text-indigo-400"></i>
                        <span>Hàng Hóa & Đổi Trả</span>
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">4 nghiệp vụ</span>
                </div>

                <!-- 1. Hàng Kho Nhân Sự (Kho Ôm / Xe) -->
                <button type="button" @click="showHoldingStockModal = true; menuOpen = false"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-emerald-500/50 text-white transition flex items-center justify-between gap-2.5 cursor-pointer group shadow-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/25 text-emerald-300 border border-emerald-500/40 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </span>
                        <div class="text-left min-w-0">
                            <div class="text-[13px] font-black text-white truncate">Kho Ôm (Kho Nhân Sự)</div>
                            <div class="text-[11px] text-slate-300 truncate">Hàng tồn của nhân sự quản lý xe</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-mono text-[11px] font-black bg-emerald-500 text-slate-950 shadow-xs shrink-0"
                          x-text="holdingStocks.length">0</span>
                </button>

                <!-- 2. Hàng Trả Về -->
                <button type="button" @click="showReturnsModal = true; menuOpen = false"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-rose-500/50 text-white transition flex items-center justify-between gap-2.5 cursor-pointer group shadow-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-rose-500/25 text-rose-300 border border-rose-500/40 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-rotate-left"></i>
                        </span>
                        <div class="text-left min-w-0">
                            <div class="text-[13px] font-black text-white truncate">Hàng Khách Trả Về</div>
                            <div class="text-[11px] text-slate-300 truncate">Danh sách đơn từ chối hoặc đổi trả</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-mono text-[11px] font-black bg-rose-500 text-white shadow-xs shrink-0"
                          x-text="returnsList.length">0</span>
                </button>

                <!-- 3. Hoàn Hàng Vào Kho -->
                <button type="button" @click="showIntakeWarehouseModal = true; menuOpen = false"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-amber-500/50 text-white transition flex items-center justify-between gap-2.5 cursor-pointer group shadow-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-amber-500/25 text-amber-300 border border-amber-500/40 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-warehouse"></i>
                        </span>
                        <div class="text-left min-w-0">
                            <div class="text-[13px] font-black text-white truncate">Hoàn Hàng Vào Kho</div>
                            <div class="text-[11px] text-slate-300 truncate">Nhập hàng trả về lại kho công ty</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-amber-300"></i>
                </button>

                <!-- 4. Phản Ánh Thừa/Thiếu Hàng (Lệch Hàng) -->
                <button type="button" @click="openDiscrepancyModal(); menuOpen = false"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-amber-500/40 hover:border-amber-400 text-white transition flex items-center justify-between gap-2.5 cursor-pointer group shadow-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-amber-500/30 text-amber-300 border border-amber-400/50 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-scale-unbalanced"></i>
                        </span>
                        <div class="text-left min-w-0">
                            <div class="text-[13px] font-black text-amber-300 truncate">Lệch Hàng (Thừa / Thiếu)</div>
                            <div class="text-[11px] text-slate-200 truncate">Báo lệch xe tới Người bốc hàng</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-mono text-[11px] font-black bg-amber-400 text-slate-950 shadow-xs shrink-0"
                          x-text="discrepancies.length">0</span>
                </button>

                <div class="border-t border-slate-800 my-1 pt-1.5">
                    <div class="px-2.5 py-1 text-[11px] uppercase font-black tracking-wider text-purple-300 flex items-center justify-between border-b border-slate-800/80 pb-1.5 mb-1">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-coins text-purple-400"></i>
                            <span>Tài Chính & Quyết Toán</span>
                        </span>
                        <span class="text-[10px] font-mono text-slate-400">3 nghiệp vụ</span>
                    </div>
                </div>

                <!-- 5. Kiểm Đếm & Duyệt COD -->
                <button type="button" @click="showCodApprovalModal = true; menuOpen = false"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-indigo-500/50 text-white transition flex items-center justify-between gap-2.5 cursor-pointer group shadow-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-indigo-500/25 text-indigo-300 border border-indigo-500/40 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </span>
                        <div class="text-left min-w-0">
                            <div class="text-[13px] font-black text-white truncate">Kiểm Đếm & Duyệt COD</div>
                            <div class="text-[11px] text-slate-300 truncate">Xác nhận thu tiền mặt & chuyển khoản</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-indigo-300"></i>
                </button>

                <!-- 6. Quyết Toán Quỹ -->
                <button type="button" @click="openHandoverModal(); menuOpen = false"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-fuchsia-500/50 text-white transition flex items-center justify-between gap-2.5 cursor-pointer group shadow-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-fuchsia-500/25 text-fuchsia-300 border border-fuchsia-500/40 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-wallet"></i>
                        </span>
                        <div class="text-left min-w-0">
                            <div class="text-[13px] font-black text-white truncate">Quyết Toán Quỹ Tiền Mặt</div>
                            <div class="text-[11px] text-slate-300 truncate">Nộp tiền cho thủ quỹ / kế toán</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-fuchsia-300"></i>
                </button>

                <!-- 7. Bàn Giao Nợ Cho Sale -->
                <button type="button" @click="openDebtHandoverModal(); menuOpen = false"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-purple-500/50 text-white transition flex items-center justify-between gap-2.5 cursor-pointer group shadow-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-purple-500/25 text-purple-300 border border-purple-500/40 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-share-from-square"></i>
                        </span>
                        <div class="text-left min-w-0">
                            <div class="text-[13px] font-black text-white truncate">Bàn Giao Nợ Cho Sale</div>
                            <div class="text-[11px] text-slate-300 truncate">Chuyển nợ bảo lãnh cho NVKD</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-purple-300"></i>
                </button>

            </div>
        </div>

        <!-- Bottom Row: Thông tin Nhân Sự & Lộ Trình Phương Tiện (Dark Glass Ribbon) -->
        <div class="bg-slate-900/60 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-slate-800/80 flex flex-wrap items-center justify-between gap-y-1.5 gap-x-4 text-[11px] sm:text-xs">
            <!-- Lái xe & Phụ xe -->
            <div class="flex flex-wrap items-center gap-x-3 sm:gap-x-4 gap-y-1">
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="w-5 h-5 sm:w-6 sm:h-6 rounded-md sm:rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/30 flex items-center justify-center font-bold text-[10px] sm:text-[11px]">
                        <i class="fa-solid fa-user-tie"></i>
                    </span>
                    <span class="text-slate-400">Lái xe:</span>
                    <strong class="text-white font-bold" x-text="selectedVehicle?.driver?.name || '---'"></strong>
                    <template x-if="selectedVehicle?.driver?.phone">
                        <a :href="'tel:' + selectedVehicle.driver.phone"
                           class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] sm:text-[11px] font-mono font-bold text-purple-300 bg-purple-950/60 border border-purple-700/50 hover:bg-purple-800/60 transition-colors"
                           title="Gọi điện cho lái xe">
                            <i class="fa-solid fa-phone text-[8px] sm:text-[9px]"></i>
                            <span x-text="selectedVehicle.driver.phone"></span>
                        </a>
                    </template>
                </div>

                <div class="flex items-center gap-1 text-slate-400">
                    <span>•</span>
                    <span>Phụ xe:</span>
                    <strong class="text-slate-200 font-semibold" x-text="selectedVehicle?.copilot_name || 'Không có'"></strong>
                </div>
            </div>

            <!-- Lộ trình & Thông số xe -->
            <div class="flex flex-wrap items-center gap-x-3 sm:gap-x-4 gap-y-1 text-slate-400">
                <div class="flex items-center gap-1">
                    <i class="fa-solid fa-location-dot text-rose-400 text-[10px] sm:text-xs"></i>
                    <span>Lộ trình: <strong class="text-slate-200" x-text="selectedVehicle?.current_route_name || selectedVehicle?.delivery_route?.name || 'Theo điều phối thực tế'"></strong></span>
                </div>
                <div class="hidden sm:flex items-center gap-1">
                    <span>•</span>
                    <span>Kho xuất: <strong class="text-slate-200" x-text="selectedVehicle?.warehouse?.name || '---'"></strong></span>
                </div>
                <div class="hidden sm:flex items-center gap-1">
                    <span>•</span>
                    <span>Tải trọng: <strong class="text-slate-200 font-mono" x-text="selectedVehicle?.capacity_kg ? (formatNumber(selectedVehicle.capacity_kg) + ' kg') : '---'"></strong></span>
                </div>
            </div>
        </div>

    </div>
