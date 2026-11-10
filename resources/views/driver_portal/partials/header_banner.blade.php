    <!-- ========================================================================= -->
    <!-- 1. HEADER BANNER CHUYẾN XE (Thiết kế Cockpit Hiện Đại & Tinh Gọn)          -->
    <!-- ========================================================================= -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-2xl sm:rounded-3xl p-3 sm:p-5 border border-slate-800 text-white shadow-2xl space-y-3 sm:space-y-4">
        
        <!-- Top Row: Tiêu đề, Biển số xe & Trạng thái xe -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            <!-- Left: Icon xe + Tiêu đề + Biển số + Trạng thái -->
            <div class="flex items-center justify-between md:justify-start gap-2.5 sm:gap-3.5">
                <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-tr from-purple-700 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-purple-600/30 border border-purple-400/30">
                        <i class="fa-solid fa-truck-fast text-base sm:text-xl"></i>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Biển số xe nổi bật -->
                            <span class="px-2 py-0.5 rounded-md sm:rounded-lg bg-purple-950/80 border border-purple-500/40 text-purple-200 font-mono font-black text-xs tracking-wider shadow-inner"
                                  x-text="selectedVehicle?.plate_number || '{{ $selectedPlate ?? '' }}'"></span>

                            <h1 class="text-base sm:text-xl font-black tracking-tight text-white truncate">
                                Giao Hàng & Thu Tiền
                            </h1>
                        </div>

                        <div class="flex items-center gap-2 text-[11px] sm:text-xs text-slate-400 mt-0.5">
                            <span>{{ \Carbon\Carbon::now()->locale('vi')->isoFormat('dddd, DD/MM/YYYY') }}</span>
                            <span>•</span>
                            <span class="text-slate-300 font-medium truncate max-w-[150px] sm:max-w-none" x-text="selectedVehicle?.vehicle_type || 'Xe tải vận chuyển'"></span>
                        </div>
                    </div>
                </div>

                <!-- Trạng thái xe badge (Góc phải trên Mobile, inline trên Desktop) -->
                <div class="shrink-0 md:hidden">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black tracking-wide"
                          :class="isReturning ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : (isDeparted ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-sky-500/20 text-sky-300 border border-sky-500/30')">
                        <span class="w-1.5 h-1.5 rounded-full" :class="isReturning ? 'bg-amber-400 animate-pulse' : (isDeparted ? 'bg-emerald-400 animate-pulse' : 'bg-sky-400')"></span>
                        <span x-text="isReturning ? 'Đang về' : (isDeparted ? 'Đang giao' : 'Sẵn sàng')"></span>
                    </span>
                </div>
            </div>

            <!-- Right (Desktop): Trạng thái xe & Selector chọn xe -->
            <div class="hidden md:flex items-center gap-2.5">
                <!-- Trạng thái xe (Desktop) -->
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black tracking-wide"
                      :class="isReturning ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : (isDeparted ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-sky-500/20 text-sky-300 border border-sky-500/30')">
                    <span class="w-1.5 h-1.5 rounded-full" :class="isReturning ? 'bg-amber-400 animate-pulse' : (isDeparted ? 'bg-emerald-400 animate-pulse' : 'bg-sky-400')"></span>
                    <span x-text="isReturning ? 'TRÊN ĐƯỜNG VỀ' : (isDeparted ? 'ĐANG ĐI GIAO HÀNG' : 'SẴN SÀNG NHẬN CHUYẾN')"></span>
                </span>

                @if(count($vehicles) > 1)
                    <!-- Selector chuyển xe khi quản lý nhiều xe -->
                    <div class="flex items-center gap-1.5 bg-slate-800/90 border border-slate-700/80 px-2.5 py-1.5 rounded-xl text-xs shadow-inner">
                        <i class="fa-solid fa-truck text-purple-400 text-xs"></i>
                        <select @change="changeVehicle($event.target.value)" class="bg-transparent font-bold text-white focus:outline-none cursor-pointer pr-1 text-xs">
                            @foreach($vehicles as $v)
                                <option value="{{ $v->plate_number }}" {{ ($selectedPlate ?? '') === $v->plate_number ? 'selected' : '' }} class="bg-slate-900 text-white font-semibold">
                                    {{ $v->plate_number }} ({{ $v->driver?->name ?? 'Lái xe' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-500/15 border border-purple-500/30 text-purple-200 text-xs font-bold shadow-2xs">
                        <i class="fa-solid fa-circle-check text-purple-400 text-xs"></i>
                        <span>Xe phụ trách</span>
                    </div>
                @endif
            </div>

        </div>

        <!-- Selector chuyển xe trên Mobile (nếu quản lý nhiều xe) -->
        @if(count($vehicles) > 1)
            <div class="md:hidden flex items-center gap-2 bg-slate-800/90 border border-slate-700/80 px-3 py-1.5 rounded-xl text-xs shadow-inner">
                <i class="fa-solid fa-truck text-purple-400 text-xs shrink-0"></i>
                <select @change="changeVehicle($event.target.value)" class="bg-transparent font-bold text-white focus:outline-none cursor-pointer text-xs w-full">
                    @foreach($vehicles as $v)
                        <option value="{{ $v->plate_number }}" {{ ($selectedPlate ?? '') === $v->plate_number ? 'selected' : '' }} class="bg-slate-900 text-white font-semibold">
                            {{ $v->plate_number }} ({{ $v->driver?->name ?? 'Lái xe' }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Action Buttons Toolbar: 3 Cột trên Mobile (2 hàng gọn gàng), Flex wrap trên Desktop -->
        <div class="grid grid-cols-3 sm:flex sm:flex-wrap items-center gap-1.5 sm:gap-2 pt-0.5">
            <!-- Button 0: Hàng Kho Nhân Sự (Kho Ôm / Xe) -->
            <button type="button" @click="showHoldingStockModal = true"
                    class="px-1.5 sm:px-3 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-200 hover:text-white text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center sm:justify-start gap-1 sm:gap-1.5 shadow-sm active:scale-95 cursor-pointer"
                    title="Xem hàng tồn kho của nhân sự quản lý xe">
                <i class="fa-solid fa-boxes-stacked text-emerald-400 text-xs shrink-0"></i>
                <span class="sm:hidden truncate">Kho Ôm</span>
                <span class="hidden sm:inline truncate">Kho Nhân Sự</span>
                <span class="px-1.5 py-0.2 rounded-full font-mono text-[10px] font-black bg-emerald-500/30 text-emerald-200 border border-emerald-500/40 shrink-0"
                      x-text="holdingStocks.length">0</span>
            </button>

            <!-- Button 1: Hàng Trả Về -->
            <button type="button" @click="showReturnsModal = true"
                    class="px-1.5 sm:px-3 py-1.5 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-200 hover:text-white text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center sm:justify-start gap-1 sm:gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                <i class="fa-solid fa-rotate-left text-rose-400 text-xs shrink-0"></i>
                <span class="sm:hidden truncate">Trả Về</span>
                <span class="hidden sm:inline truncate">Hàng Trả Về</span>
                <span class="px-1.5 py-0.2 rounded-full font-mono text-[10px] font-black bg-rose-500/30 text-rose-200 border border-rose-500/40 shrink-0" x-text="returnsList.length">0</span>
            </button>

            <!-- Button 2: Hoàn Hàng Vào Kho -->
            <button type="button" @click="showIntakeWarehouseModal = true"
                    class="px-1.5 sm:px-3 py-1.5 rounded-xl bg-slate-800/90 hover:bg-slate-700/90 border border-slate-700 text-slate-200 hover:text-white text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center sm:justify-start gap-1 sm:gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                <i class="fa-solid fa-warehouse text-amber-400 text-xs shrink-0"></i>
                <span class="truncate">Hoàn kho</span>
            </button>

            <!-- Button 3: Kiểm Đếm & Duyệt COD -->
            <button type="button" @click="showCodApprovalModal = true"
                    class="px-1.5 sm:px-3 py-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-200 hover:text-white text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center sm:justify-start gap-1 sm:gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                <i class="fa-solid fa-money-bill-transfer text-indigo-400 text-xs shrink-0"></i>
                <span class="truncate">Duyệt COD</span>
            </button>

            <!-- Button 4: Quyết Toán Quỹ -->
            <button type="button" @click="openHandoverModal()"
                    class="px-1.5 sm:px-3 py-1.5 rounded-xl bg-fuchsia-500/15 hover:bg-fuchsia-500/25 border border-fuchsia-500/30 text-fuchsia-200 hover:text-white text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center sm:justify-start gap-1 sm:gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                <i class="fa-solid fa-wallet text-fuchsia-400 text-xs shrink-0"></i>
                <span class="truncate">Quyết toán</span>
            </button>

            <!-- Button 5: Bàn Giao Nợ Cho Sale -->
            <button type="button" @click="openDebtHandoverModal()"
                    class="px-1.5 sm:px-3 py-1.5 rounded-xl bg-purple-500/15 hover:bg-purple-500/25 border border-purple-500/30 text-purple-200 hover:text-white text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center sm:justify-start gap-1 sm:gap-1.5 shadow-sm active:scale-95 cursor-pointer">
                <i class="fa-solid fa-share-from-square text-purple-400 text-xs shrink-0"></i>
                <span class="sm:hidden truncate">Nợ Sale</span>
                <span class="hidden sm:inline truncate">Bàn giao nợ Sale</span>
            </button>
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
                    <span>Kho xuất: <strong class="text-slate-200">Kho Mê Linh (Tổng MQ)</strong></span>
                </div>
                <div class="hidden sm:flex items-center gap-1">
                    <span>•</span>
                    <span>Tải trọng: <strong class="text-slate-200 font-mono" x-text="formatNumber(selectedVehicle?.capacity_kg || 2500) + ' kg'"></strong></span>
                </div>
            </div>
        </div>

    </div>
