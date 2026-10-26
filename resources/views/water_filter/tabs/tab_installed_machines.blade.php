        <!-- ============================================================== -->
        <!-- TAB: MÁY ĐÃ LẮP & QUẢN LÝ BẢO HÀNH                             -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- TAB: MÁY ĐÃ LẮP, TEM QR & QUẢN LÝ BẢO HÀNH                     -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'installed_machines'" class="p-4 sm:p-6 space-y-4">
            <!-- 6 KPI Sub-cards for Warranty & QR Activation Lifecycle -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Card 1: Tổng máy -->
                <div @click="installedWarrantyStatus = ''; fetchInstalledMachines()" class="bg-gradient-to-br from-slate-50 to-white p-3 rounded-xl border border-slate-200/80 shadow-2xs cursor-pointer hover:border-slate-400 transition" :class="installedWarrantyStatus === '' ? 'ring-2 ring-slate-500/30 border-slate-500' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-slate-500">Tổng thiết bị</span>
                        <i class="fa-solid fa-faucet-drip text-slate-400 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-slate-800" x-text="installedMachinesStats.total"></span>
                        <span class="text-[10px] text-slate-400">máy/tem</span>
                    </div>
                </div>

                <!-- Card 2: Tem QR trống (chưa kích hoạt) -->
                <div @click="installedWarrantyStatus = 'unactivated'; fetchInstalledMachines()" class="bg-gradient-to-br from-slate-100/60 to-white p-3 rounded-xl border border-slate-300 shadow-2xs cursor-pointer hover:border-amber-400 transition" :class="installedWarrantyStatus === 'unactivated' ? 'ring-2 ring-amber-500/30 border-amber-500 bg-amber-50/40' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-slate-600">Tem QR trống</span>
                        <i class="fa-solid fa-qrcode text-amber-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-amber-600" x-text="installedMachinesStats.unactivated"></span>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.2 rounded">Chờ dán</span>
                    </div>
                </div>

                <!-- Card 3: Chờ duyệt kích hoạt -->
                <div @click="installedWarrantyStatus = 'pending_activation'; fetchInstalledMachines()" class="bg-gradient-to-br from-orange-50/60 to-white p-3 rounded-xl border border-orange-200 shadow-2xs cursor-pointer hover:border-orange-500 transition relative" :class="installedWarrantyStatus === 'pending_activation' ? 'ring-2 ring-orange-500/30 border-orange-500 bg-orange-50' : ''">
                    <template x-if="installedMachinesStats.pending_activation > 0">
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
                        </span>
                    </template>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-orange-700">Chờ duyệt KH</span>
                        <i class="fa-solid fa-clock text-orange-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-orange-600" x-text="installedMachinesStats.pending_activation"></span>
                        <span class="text-[10px] font-bold text-orange-800 bg-orange-100 px-1.5 py-0.2 rounded">KTV gửi</span>
                    </div>
                </div>

                <!-- Card 4: Đang còn bảo hành -->
                <div @click="installedWarrantyStatus = 'active'; fetchInstalledMachines()" class="bg-gradient-to-br from-emerald-50/50 to-white p-3 rounded-xl border border-emerald-200/80 shadow-2xs cursor-pointer hover:border-emerald-500 transition" :class="installedWarrantyStatus === 'active' ? 'ring-2 ring-emerald-500/30 border-emerald-500 bg-emerald-50/70' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-emerald-700">Đang bảo hành</span>
                        <i class="fa-solid fa-shield-halved text-emerald-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-emerald-600" x-text="installedMachinesStats.active"></span>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded">Trong hạn</span>
                    </div>
                </div>

                <!-- Card 5: Sắp hết hạn (≤ 30 ngày) -->
                <div @click="installedWarrantyStatus = 'expiring_soon'; fetchInstalledMachines()" class="bg-gradient-to-br from-amber-50/50 to-white p-3 rounded-xl border border-amber-200/80 shadow-2xs cursor-pointer hover:border-amber-500 transition" :class="installedWarrantyStatus === 'expiring_soon' ? 'ring-2 ring-amber-500/30 border-amber-500 bg-amber-50/70' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-amber-700">Sắp hết hạn</span>
                        <i class="fa-solid fa-hourglass-half text-amber-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-amber-600" x-text="installedMachinesStats.expiring_soon"></span>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.2 rounded">≤ 30 ngày</span>
                    </div>
                </div>

                <!-- Card 6: Đã hết bảo hành -->
                <div @click="installedWarrantyStatus = 'expired'; fetchInstalledMachines()" class="bg-gradient-to-br from-rose-50/50 to-white p-3 rounded-xl border border-rose-200/80 shadow-2xs cursor-pointer hover:border-rose-500 transition" :class="installedWarrantyStatus === 'expired' ? 'ring-2 ring-rose-500/30 border-rose-500 bg-rose-50/70' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-rose-700">Đã hết hạn</span>
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-xs"></i>
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-black text-rose-600" x-text="installedMachinesStats.expired"></span>
                        <span class="text-[10px] font-bold text-rose-700 bg-rose-100 px-1.5 py-0.2 rounded">Tính phí</span>
                    </div>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs bg-slate-50/80 p-3 rounded-xl border border-slate-200/80">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        <input 
                            type="text" 
                            x-model="installedSearch" 
                            @input.debounce.300ms="fetchInstalledMachines()" 
                            placeholder="Tìm máy: mã QR, model, khách, SĐT, serial..." 
                            class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs font-medium"
                        >
                    </div>

                    <select 
                        x-model="installedWarrantyStatus" 
                        @change="fetchInstalledMachines()" 
                        class="bg-white border border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs font-bold text-slate-700 shadow-2xs"
                    >
                        <option value="">-- Tất cả trạng thái máy / QR --</option>
                        <option value="unactivated">⚪ Tem QR trống (chưa kích hoạt)</option>
                        <option value="pending_activation">🟠 Chờ KTV / Quản lý duyệt kích hoạt</option>
                        <option value="active">🟢 Đang còn hạn bảo hành</option>
                        <option value="expiring_soon">🟡 Sắp hết hạn bảo hành (≤ 30 ngày)</option>
                        <option value="expired">🔴 Đã hết thời hạn bảo hành</option>
                    </select>

                    <select 
                        x-model="installedTechId" 
                        @change="fetchInstalledMachines()" 
                        class="bg-white border border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 text-xs font-medium text-slate-700 shadow-2xs"
                    >
                        <option value="">-- Tất cả KTV --</option>
                        <template x-for="t in technicians" :key="t.id">
                            <option :value="t.id" x-text="t.name + ' (' + (t.department || 'KTV') + ')'"></option>
                        </template>
                    </select>

                    <button 
                        type="button" 
                        @click="fetchInstalledMachines()" 
                        class="px-3 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs" 
                        title="Tải lại dữ liệu"
                    >
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Tải lại</span>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Batch Print Selected Stickers -->
                    <button 
                        type="button"
                        x-show="selectedMachineIds.length > 0"
                        @click="printSelectedStickers()"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition shadow-xs cursor-pointer"
                    >
                        <i class="fa-solid fa-print"></i>
                        <span>In tem đã chọn (<span x-text="selectedMachineIds.length"></span>)</span>
                    </button>

                    <!-- Print All QR Stickers Button -->
                    <a 
                        href="{{ url('/bao-hanh-may-loc/print-qr') }}" 
                        target="_blank" 
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold transition shadow-2xs cursor-pointer"
                    >
                        <i class="fa-solid fa-qrcode text-blue-600"></i>
                        <span>In bảng tem dán QR</span>
                    </a>

                    <!-- Batch Generate Blank QR Codes -->
                    <button 
                        type="button" 
                        @click="openBatchQrModal()" 
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold transition shadow-xs cursor-pointer"
                    >
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>+ Tạo mã QR trống</span>
                    </button>
                </div>
            </div>

            <!-- Table of Installed Machines & QR Stickers -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs bg-white">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/90 text-slate-700 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3 w-8 text-center">
                                <input type="checkbox" @change="toggleSelectAll($event)" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                            </th>
                            <th class="py-3 px-3">Mã máy & Tem QR</th>
                            <th class="py-3 px-3">Trạng thái QR</th>
                            <th class="py-3 px-3">Model & Serial</th>
                            <th class="py-3 px-3">Khách hàng & Địa chỉ</th>
                            <th class="py-3 px-3">Kỹ thuật viên & Lắp đặt</th>
                            <th class="py-3 px-3">Bảo hành & Thay lõi</th>
                            <th class="py-3 px-3 text-center">Lượt quét</th>
                            <th class="py-3 px-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-if="installedMachines.length === 0">
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-qrcode text-4xl mb-2 text-slate-300 block"></i>
                                    <span class="font-semibold">Chưa có máy lọc hoặc tem QR nào phù hợp điều kiện lọc.</span>
                                    <div class="mt-2">
                                        <button type="button" @click="openBatchQrModal()" class="text-xs text-blue-600 font-bold hover:underline">
                                            + Bấm vào đây để tạo lô mã QR thông tin trống mới
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-for="item in installedMachines" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition" :class="item.status === 'pending_activation' ? 'bg-orange-50/40' : ''">
                                <!-- Col 0: Checkbox -->
                                <td class="py-3 px-3 align-top text-center">
                                    <input type="checkbox" :value="item.id" x-model="selectedMachineIds" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                                </td>

                                <!-- Col 1: Mã máy & Tem QR -->
                                <td class="py-3 px-3 align-top min-w-[140px]">
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" @click="openQrPreview(item)" class="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Xem mã QR to & Quét thử">
                                            <i class="fa-solid fa-qrcode text-sm text-blue-600"></i>
                                        </button>
                                        <div>
                                            <div class="font-mono font-bold text-slate-900" x-text="item.machine_code"></div>
                                            <div class="text-[10px] font-mono text-slate-500" x-text="item.qr_code"></div>
                                        </div>
                                    </div>
                                    <template x-if="item.warranty_card_code">
                                        <div class="text-[10px] font-mono font-bold text-emerald-700 mt-1 flex items-center gap-1 bg-emerald-50 px-1.5 py-0.5 rounded w-max border border-emerald-200/60">
                                            <i class="fa-solid fa-id-card text-[9px]"></i>
                                            <span x-text="item.warranty_card_code"></span>
                                        </div>
                                    </template>
                                </td>

                                <!-- Col 2: Trạng thái QR & Kích hoạt -->
                                <td class="py-3 px-3 align-top min-w-[130px]">
                                    <template x-if="item.status === 'unactivated'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Tem QR trống
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'pending_activation'">
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-300 animate-pulse">
                                                <i class="fa-solid fa-clock text-[9px]"></i>
                                                Chờ duyệt KH
                                            </span>
                                            <button type="button" @click="openActivateModal(item)" class="block text-[10px] font-bold text-orange-700 hover:text-orange-900 underline">
                                                Duyệt ngay &raquo;
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="item.status === 'activated'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="getWarrantyBadgeClass(item.warranty_status)">
                                            <i class="fa-solid fa-circle text-[5px]"></i>
                                            <span x-text="getWarrantyStatusLabel(item.warranty_status)"></span>
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'expired'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            Hết hạn BH
                                        </span>
                                    </template>
                                </td>

                                <!-- Col 3: Model & Serial -->
                                <td class="py-3 px-3 align-top min-w-[170px]">
                                    <div class="font-extrabold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-faucet text-cyan-600"></i>
                                        <span x-text="item.machine_model || '(Chưa gán model)'"></span>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-600 mt-0.5 flex items-center gap-1">
                                        <span class="text-slate-400 text-[10px]">Serial:</span>
                                        <span class="font-bold text-slate-700" x-text="item.machine_serial || '(Chưa có)'"></span>
                                    </div>
                                    <template x-if="item.accessories">
                                        <div class="text-[10px] text-teal-800 mt-1 flex items-center gap-1 bg-teal-50 border border-teal-200/70 rounded px-1.5 py-0.5" :title="'Phụ kiện: ' + item.accessories">
                                            <i class="fa-solid fa-boxes-stacked text-[9px] text-teal-600"></i>
                                            <span class="truncate max-w-[150px]" x-text="item.accessories"></span>
                                        </div>
                                    </template>
                                    <template x-if="item.repairs_count > 0">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded mt-1">
                                            <i class="fa-solid fa-wrench text-[8px]"></i>
                                            <span x-text="item.repairs_count + ' lần bảo hành/sửa'"></span>
                                        </span>
                                    </template>
                                </td>

                                <!-- Col 4: Khách hàng & Địa chỉ -->
                                <td class="py-3 px-3 align-top min-w-[190px]">
                                    <template x-if="item.customer_name">
                                        <div>
                                            <div class="font-bold text-slate-800" x-text="item.customer_name"></div>
                                            <div class="text-[11px] font-semibold text-sky-600 flex items-center gap-1 mt-0.5">
                                                <i class="fa-solid fa-phone text-[10px]"></i>
                                                <a :href="'tel:' + item.customer_phone" class="hover:underline" x-text="item.customer_phone"></a>
                                            </div>
                                            <div class="text-[11px] text-slate-500 line-clamp-2 mt-0.5" :title="item.customer_address" x-text="item.customer_address"></div>
                                        </div>
                                    </template>
                                    <template x-if="!item.customer_name">
                                        <span class="text-[11px] text-slate-400 italic">Chưa liên kết khách hàng</span>
                                    </template>
                                </td>

                                <!-- Col 5: Kỹ thuật viên & Lắp đặt -->
                                <td class="py-3 px-3 align-top min-w-[150px]">
                                    <div class="font-bold text-slate-800 flex items-center gap-1">
                                        <i class="fa-regular fa-calendar-check text-emerald-600"></i>
                                        <span x-text="item.installed_at || '-'"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 mt-1 flex items-center gap-1">
                                        <i class="fa-solid fa-user-gear text-slate-400 text-[10px]"></i>
                                        <span class="font-semibold" x-text="item.technician_name || 'Chưa gán KTV'"></span>
                                    </div>
                                </td>

                                <!-- Col 6: Bảo hành & Thay lõi -->
                                <td class="py-3 px-3 align-top min-w-[150px]">
                                    <div class="text-[11px] font-bold text-slate-700">
                                        <span>Hạn BH: </span>
                                        <strong class="text-slate-900" x-text="item.warranty_until || '-'"></strong>
                                    </div>
                                    <div class="text-[10px] mt-0.5 font-semibold" :class="item.warranty_status === 'active' ? 'text-emerald-600' : (item.warranty_status === 'expiring_soon' ? 'text-amber-600' : 'text-slate-400')">
                                        <template x-if="item.warranty_status === 'active'">
                                            <span>Còn <strong x-text="item.warranty_days_remaining"></strong> ngày</span>
                                        </template>
                                        <template x-if="item.warranty_status === 'expiring_soon'">
                                            <span>Sắp hết! Còn <strong x-text="item.warranty_days_remaining"></strong> ngày</span>
                                        </template>
                                    </div>
                                    <div class="text-slate-600 text-[10px] mt-1 flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-spin text-teal-600"></i>
                                        <span x-text="'Thay lõi tới: ' + (item.next_maintenance_date || '-')"></span>
                                    </div>
                                </td>

                                <!-- Col 7: Lượt quét -->
                                <td class="py-3 px-3 align-top text-center min-w-[80px]">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span x-text="item.view_count"></span>
                                    </span>
                                    <template x-if="item.last_scanned_at">
                                        <div class="text-[9px] text-slate-400 mt-1 line-clamp-1" :title="'Lần quét gần nhất: ' + item.last_scanned_at" x-text="item.last_scanned_at"></div>
                                    </template>
                                </td>

                                <!-- Col 8: Thao tác -->
                                <td class="py-3 px-3 align-top text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Xem / Quét thử trang QR -->
                                        <a 
                                            :href="'/m/' + item.qr_token" 
                                            target="_blank"
                                            title="Mở trang quét mã QR (/m/...)" 
                                            class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>

                                        <!-- Duyệt kích hoạt (nếu đang chờ duyệt hoặc chưa kích hoạt) -->
                                        <template x-if="item.status === 'pending_activation' || item.status === 'unactivated'">
                                            <button 
                                                @click="openActivateModal(item)" 
                                                title="Duyệt kích hoạt bảo hành máy" 
                                                class="p-1.5 rounded-lg bg-orange-100 hover:bg-orange-200 text-orange-800 transition cursor-pointer font-bold"
                                            >
                                                <i class="fa-solid fa-circle-check"></i>
                                            </button>
                                        </template>

                                        <!-- Sửa thông tin máy & bảo hành -->
                                        <button 
                                            @click="openMachineEditModal(item)" 
                                            title="Chỉnh sửa thông tin máy & bảo hành" 
                                            class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- In tem dán máy này -->
                                        <a 
                                            :href="'/bao-hanh-may-loc/print-qr?ids=' + item.id" 
                                            target="_blank"
                                            title="In tem QR dán máy này" 
                                            class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-print"></i>
                                        </a>

                                        <!-- Tiếp nhận sửa chữa -->
                                        <button 
                                            @click="quickCreateRepairFromInstalled(item)" 
                                            title="Tiếp nhận bảo dưỡng / sửa chữa" 
                                            class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-wrench"></i>
                                        </button>

                                        <!-- Xóa máy -->
                                        <button 
                                            @click="deleteInstalledMachine(item.id)" 
                                            title="Xóa máy khỏi danh sách" 
                                            class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

