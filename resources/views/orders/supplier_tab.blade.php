@php
    $currentUser = auth()->user();
@endphp

<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="supplierOrdersApp()">

    <!-- 1. Header Banner -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/orders') }}" class="w-10 h-10 rounded-full bg-sky-600 hover:bg-sky-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-sky-600/20 cursor-pointer transition-transform hover:scale-105" title="Quay lại danh sách Đơn hàng">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Đặt Hàng Nhà Cung Cấp</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800 text-[11px] font-extrabold border border-sky-200">PO / Mua hàng NCC</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Lập đơn mua hàng từ NCC · Quản lý trạng thái thanh toán · Trừ công nợ · Nhập kho & Xử lý nợ hàng / nợ tiền khi thiếu hàng</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Nút Sổ nợ NCC -->
            <button type="button" @click="openDebtModal()" class="px-3.5 py-2.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs flex items-center gap-2 shadow-2xs transition-all cursor-pointer">
                <i class="fa-solid fa-hand-holding-dollar text-amber-600"></i>
                <span>Sổ nợ NCC (<span x-text="formatMoney(stats.total_supplier_debt || 0)">0 đ</span>)</span>
            </button>

            <!-- Nút Thêm NCC -->
            <button type="button" @click="openSupplierModal()" class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                <i class="fa-solid fa-building text-slate-500"></i>
                <span>Thêm NCC</span>
            </button>

            <!-- NÚT CHÍNH: Đặt hàng NCC (Mở trang riêng, không dùng modal) -->
            <a href="{{ url('/supplier-orders/create') }}" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black text-xs flex items-center gap-2 shadow-md shadow-sky-600/25 transition-all cursor-pointer hover:scale-[1.02] active:scale-95">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Đặt hàng NCC</span>
            </a>

            <button type="button" @click="fetchOrders()" class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Làm mới dữ liệu">
                <i class="fa-solid fa-arrows-rotate text-sky-600" :class="loading ? 'animate-spin' : ''"></i>
            </button>
        </div>
    </div>

    <!-- 2. KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Card 1: Tổng tiền đặt hàng -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tổng giá trị đơn đặt</div>
                <div class="text-lg sm:text-xl font-black text-slate-900 mt-1" x-text="formatMoney(stats.total_value || 0)">0 đ</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Tất cả đơn đặt hàng NCC</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg border border-sky-100">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
        </div>

        <!-- Card 2: Đã thanh toán NCC -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Đã thanh toán NCC</div>
                <div class="text-lg sm:text-xl font-black text-emerald-600 mt-1" x-text="formatMoney(stats.total_paid || 0)">0 đ</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Tiền đã chuyển trả cho NCC</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg border border-emerald-100">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
        </div>

        <!-- Card 3: NCC ĐANG NỢ CÔNG TY (Đặc biệt: Xử lý nợ tiền) -->
        <div class="bg-amber-50/70 p-4 rounded-2xl border border-amber-200 shadow-2xs flex items-center justify-between relative overflow-hidden">
            <div class="relative z-10">
                <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-hand-holding-dollar text-amber-600"></i>
                    <span>NCC nợ công ty (Được trừ nợ)</span>
                </div>
                <div class="text-lg sm:text-xl font-black text-amber-900 mt-1" x-text="formatMoney(stats.total_supplier_debt || 0)">0 đ</div>
                <div class="text-[11px] text-amber-700 mt-0.5 font-semibold">Tự động gợi ý trừ nợ khi đặt đơn mới</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg border border-amber-300">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>

        <!-- Card 4: Đơn chờ nhập kho -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Đơn cần nhập hàng</div>
                <div class="text-lg sm:text-xl font-black text-sky-600 mt-1" x-text="(stats.pending_receive_count || 0) + ' đơn'">0 đơn</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Đã duyệt hoặc nhập 1 phần</div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg border border-blue-100">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
        </div>
    </div>

    <!-- 3. Status Filter Navigation Pills -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-1.5 overflow-x-auto text-xs">
        <template x-for="st in statusTabs" :key="st.key">
            <button type="button" @click="setStatusFilter(st.key)"
                    :class="selectedStatus === st.key ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap">
                <span x-text="st.label"></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                      :class="selectedStatus === st.key ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      x-text="counts[st.key] || 0">0</span>
            </button>
        </template>
    </div>

    <!-- 4. Filter & Search Controls -->
    <div class="bg-white rounded-2xl p-3 border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 flex-1 min-w-[280px]">
            <!-- Search input -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="search" @input.debounce.400ms="fetchOrders()" placeholder="Tìm theo mã đơn ĐHNCC, nhà cung cấp, sản phẩm..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none">
            </div>

            <!-- Supplier filter -->
            <select x-model="selectedSupplier" @change="fetchOrders()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold bg-white text-slate-700 focus:border-sky-500 outline-none">
                <option value="">Tất cả Nhà Cung Cấp</option>
                <template x-for="s in suppliers" :key="s.id">
                    <option :value="s.id" x-text="s.name"></option>
                </template>
            </select>

            <!-- Payment status filter -->
            <select x-model="selectedPaymentStatus" @change="fetchOrders()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold bg-white text-slate-700 focus:border-sky-500 outline-none">
                <option value="">Tất cả trạng thái thanh toán</option>
                <option value="Chưa thanh toán">Chưa thanh toán</option>
                <option value="Thanh toán 1 phần">Thanh toán 1 phần</option>
                <option value="Đã thanh toán">Đã thanh toán</option>
            </select>
        </div>

        <div class="text-xs font-bold text-slate-500 flex items-center gap-2">
            <span>Hiển thị <span class="text-slate-900 font-extrabold" x-text="ordersList.length"></span> đơn đặt hàng</span>
        </div>
    </div>

    <!-- 5. Data Table: Danh sách đơn đặt hàng NCC -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black uppercase text-[10.5px] tracking-wider">
                        <th class="py-3 px-3.5">Mã Đơn & Ngày Đặt</th>
                        <th class="py-3 px-3.5">Nhà Cung Cấp</th>
                        <th class="py-3 px-3.5 text-right">Tổng Tiền / Cần Trả</th>
                        <th class="py-3 px-3.5 text-center">Trạng Thái Thanh Toán</th>
                        <th class="py-3 px-3.5 text-center">Trạng Thái Đơn</th>
                        <th class="py-3 px-3.5 text-center">Tiến Độ Nhập Kho</th>
                        <th class="py-3 px-3.5 text-center">Xử Lý Thiếu Hàng</th>
                        <th class="py-3 px-3.5 text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-spinner animate-spin text-2xl text-sky-600 mb-2"></i>
                                <p class="font-bold text-xs">Đang tải dữ liệu đơn đặt hàng NCC...</p>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && ordersList.length === 0">
                        <tr>
                            <td colspan="8" class="py-14 text-center text-slate-400 space-y-3">
                                <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto text-xl border border-sky-100">
                                    <i class="fa-solid fa-cart-flatbed"></i>
                                </div>
                                <div class="font-black text-slate-700 text-sm">Chưa có đơn đặt hàng NCC nào phù hợp</div>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto">Bạn có thể bấm nút "Đặt hàng NCC" ở góc trên để tạo đơn đặt hàng mua từ nhà máy Tiền Phong hoặc NCC khác.</p>
                                <a href="{{ url('/supplier-orders/create') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-black rounded-xl text-xs inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Tạo đơn đặt NCC đầu tiên</span>
                                </a>
                            </td>
                        </tr>
                    </template>

                    <template x-for="order in ordersList" :key="order.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Mã đơn & Ngày đặt -->
                            <td class="py-3 px-3.5 align-top">
                                <div class="font-black text-slate-900 font-mono text-xs flex items-center gap-1.5 cursor-pointer hover:text-sky-600" @click="openDetailModal(order)">
                                    <span x-text="order.code"></span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                    <i class="fa-regular fa-calendar text-[10px] text-slate-400"></i>
                                    <span x-text="formatDate(order.order_date)"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-show="order.creator_name">
                                    Lập bởi: <span class="font-bold text-slate-600" x-text="order.creator_name"></span>
                                </div>
                            </td>

                            <!-- Nhà cung cấp -->
                            <td class="py-3 px-3.5 align-top">
                                <div class="font-black text-slate-900 text-xs" x-text="order.supplier_name"></div>
                                <div class="text-[11px] text-slate-500 mt-0.5" x-show="order.warehouse_name">
                                    <i class="fa-solid fa-warehouse text-[10px] text-slate-400 mr-1"></i>Kho: <span class="font-semibold text-slate-700" x-text="order.warehouse_name"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-show="order.expected_delivery_date">
                                    Hẹn giao: <span class="font-semibold text-slate-600" x-text="formatDate(order.expected_delivery_date)"></span>
                                </div>
                            </td>

                            <!-- Giá trị đơn hàng & Trừ nợ -->
                            <td class="py-3 px-3.5 align-top text-right">
                                <div class="font-black text-slate-900 text-xs" x-text="formatMoney(order.total_amount)"></div>
                                <template x-if="order.debt_deducted_amount > 0">
                                    <div class="text-[10.5px] font-bold text-amber-700 mt-0.5 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 inline-block">
                                        Trừ nợ NCC: -<span x-text="formatMoney(order.debt_deducted_amount)"></span>
                                    </div>
                                </template>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Cần trả: <span class="font-extrabold text-slate-800" x-text="formatMoney(order.payable_amount)"></span>
                                </div>
                            </td>

                            <!-- Trạng thái thanh toán -->
                            <td class="py-3 px-3.5 align-top text-center">
                                <template x-if="order.payment_status === 'Đã thanh toán'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                        <span>Đã thanh toán</span>
                                    </span>
                                </template>
                                <template x-if="order.payment_status === 'Thanh toán 1 phần'">
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="fa-solid fa-chart-pie text-amber-600"></i>
                                            <span>Thanh toán 1 phần</span>
                                        </span>
                                        <div class="text-[10px] text-slate-500 mt-0.5 font-bold">
                                            Đã trả: <span class="text-emerald-700 font-black" x-text="formatMoney(order.paid_amount)"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="order.payment_status === 'Chưa thanh toán'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-regular fa-clock text-rose-500"></i>
                                        <span>Chưa thanh toán</span>
                                    </span>
                                </template>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="order.payment_method"></div>
                            </td>

                            <!-- Trạng thái đơn hàng -->
                            <td class="py-3 px-3.5 align-top text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black"
                                      :class="{
                                          'bg-slate-100 text-slate-700 border border-slate-200': order.status === 'Chờ duyệt',
                                          'bg-sky-100 text-sky-800 border border-sky-200': order.status === 'Đã duyệt',
                                          'bg-blue-100 text-blue-800 border border-blue-200': order.status === 'Đang giao hàng',
                                          'bg-amber-100 text-amber-800 border border-amber-300 animate-pulse': order.status === 'Nhập một phần',
                                          'bg-emerald-100 text-emerald-800 border border-emerald-200': order.status === 'Hoàn thành',
                                          'bg-rose-100 text-rose-700 border border-rose-200': order.status === 'Đã hủy'
                                      }">
                                    <span x-text="order.status"></span>
                                </span>
                            </td>

                            <!-- Tiến độ nhập kho -->
                            <td class="py-3 px-3.5 align-top text-center">
                                @php
                                    // Sẽ render qua Alpine logic
                                @endphp
                                <div class="w-full max-w-[120px] mx-auto">
                                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-600 mb-1">
                                        <span x-text="calcReceivedPercent(order) + '%'"></span>
                                        <span x-text="calcReceivedQtySummary(order)"></span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             :class="calcReceivedPercent(order) >= 100 ? 'bg-emerald-500' : 'bg-sky-500'"
                                             :style="'width: ' + calcReceivedPercent(order) + '%'"></div>
                                    </div>
                                    <div class="text-[9.5px] text-slate-400 mt-0.5" x-show="order.receipt_count > 0">
                                        <span x-text="order.receipt_count + ' lần nhập kho'"></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Xử lý thiếu hàng (Cơ chế nợ hàng vs nợ tiền) -->
                            <td class="py-3 px-3.5 align-top text-center">
                                <template x-if="order.shortage_resolution === 'supplier_owes_goods'">
                                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-1.5 text-center">
                                        <div class="text-[10.5px] font-black text-amber-800 flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-boxes-packing text-amber-600"></i>
                                            <span>NCC nợ hàng</span>
                                        </div>
                                        <div class="text-[10px] text-amber-700 font-semibold mt-0.5">
                                            Chờ giao bù đợt sau
                                        </div>
                                    </div>
                                </template>
                                <template x-if="order.shortage_resolution === 'supplier_owes_money'">
                                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-1.5 text-center">
                                        <div class="text-[10.5px] font-black text-emerald-800 flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-money-bill-transfer text-emerald-600"></i>
                                            <span>NCC nợ tiền</span>
                                        </div>
                                        <div class="text-[10px] text-emerald-700 font-extrabold mt-0.5">
                                            +<span x-text="formatMoney(order.refunded_to_supplier_debt)"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!order.shortage_resolution">
                                    <span class="text-slate-400 text-[11px]">—</span>
                                </template>
                            </td>

                            <!-- Thao tác -->
                            <td class="py-3 px-3.5 align-top text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Nút Tạo phiếu nhập hàng (Mở trang riêng, không dùng modal) -->
                                    <template x-if="['Đã duyệt', 'Đang giao hàng', 'Nhập một phần'].includes(order.status)">
                                        <a :href="'{{ url('/supplier-orders') }}/' + order.id + '/receipt'"
                                           class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-black text-[11px] shadow-sm flex items-center gap-1 cursor-pointer transition-transform hover:scale-105 active:scale-95"
                                           title="Tạo phiếu nhập hàng về kho (Mở trang riêng)">
                                            <i class="fa-solid fa-boxes-packing"></i>
                                            <span>Nhập hàng</span>
                                        </a>
                                    </template>

                                    <!-- Xem chi tiết -->
                                    <button type="button" @click="openDetailModal(order)"
                                            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center cursor-pointer transition-colors"
                                            title="Xem chi tiết đơn hàng">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </button>

                                    <!-- Duyệt đơn nếu Chờ duyệt -->
                                    <template x-if="order.status === 'Chờ duyệt'">
                                        <button type="button" @click="approveOrder(order.id)"
                                                class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center cursor-pointer transition-colors"
                                                title="Duyệt đơn đặt hàng">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                    </template>

                                    <!-- Cập nhật thanh toán -->
                                    <template x-if="order.payment_status !== 'Đã thanh toán' && order.status !== 'Đã hủy'">
                                        <button type="button" @click="openPaymentModal(order)"
                                                class="w-8 h-8 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center cursor-pointer transition-colors"
                                                title="Cập nhật thanh toán cho NCC">
                                            <i class="fa-solid fa-money-bill-wave text-xs"></i>
                                        </button>
                                    </template>

                                    <!-- Hủy đơn -->
                                    <template x-if="['Chờ duyệt', 'Đã duyệt'].includes(order.status)">
                                        <button type="button" @click="cancelOrder(order)"
                                                class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center cursor-pointer transition-colors"
                                                title="Hủy đơn đặt hàng">
                                            <i class="fa-regular fa-trash-can text-xs"></i>
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lưu ý: Chức năng Tạo đơn NCC và Nhập hàng đã được chuyển sang trang riêng biệt (Full Page) tại /supplier-orders/create và /supplier-orders/{id}/receipt -->


    <!-- ================================================================= -->
    <!-- MODAL 3: CHI TIẾT ĐƠN ĐẶT HÀNG NCC & LỊCH SỬ PHIẾU NHẬP KHO        -->
    <!-- ================================================================= -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="closeDetailModal()"></div>

        <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-4xl border border-slate-200 my-8">
                
                <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-black tracking-tight">Chi Tiết Đơn Đặt Hàng</h3>
                                <span class="px-2 py-0.5 rounded-full bg-sky-500/30 text-sky-200 font-mono text-xs font-bold" x-text="detailOrder?.code"></span>
                            </div>
                            <p class="text-[11px] text-slate-300">Nhà cung cấp: <span class="font-bold text-white" x-text="detailOrder?.supplier_name"></span></p>
                        </div>
                    </div>
                    <button type="button" @click="closeDetailModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-4 max-h-[75vh] overflow-y-auto" x-if="detailOrder">
                    <!-- Thông tin cơ bản & Tài chính -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold block uppercase">Ngày đặt hàng</span>
                            <span class="font-black text-slate-800" x-text="formatDate(detailOrder?.order_date)"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold block uppercase">Trạng thái đơn</span>
                            <span class="font-black text-sky-700" x-text="detailOrder?.status"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold block uppercase">Trạng thái thanh toán</span>
                            <span class="font-black text-emerald-700" x-text="detailOrder?.payment_status"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold block uppercase">Số tiền đã trả NCC</span>
                            <span class="font-black text-emerald-700" x-text="formatMoney(detailOrder?.paid_amount)"></span>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm -->
                    <div class="space-y-1.5">
                        <div class="text-xs font-black text-slate-800 uppercase tracking-wider">Danh Sách Mặt Hàng Đặt</div>
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black text-[10px]">
                                    <tr>
                                        <th class="py-2 px-3">Tên sản phẩm</th>
                                        <th class="py-2 px-2 text-center w-16">ĐVT</th>
                                        <th class="py-2 px-2 text-right w-24">SL Đặt</th>
                                        <th class="py-2 px-2 text-right w-24">SL Đã nhận</th>
                                        <th class="py-2 px-2 text-right w-24">SL Còn thiếu</th>
                                        <th class="py-2 px-3 text-right w-28">Đơn giá</th>
                                        <th class="py-2 px-3 text-right w-32">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="item in detailOrder?.items" :key="item.id">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="py-2 px-3 font-bold text-slate-800" x-text="item.product_name"></td>
                                            <td class="py-2 px-2 text-center text-slate-500" x-text="item.unit"></td>
                                            <td class="py-2 px-2 text-right font-black text-slate-800" x-text="item.ordered_quantity"></td>
                                            <td class="py-2 px-2 text-right font-black text-emerald-600" x-text="item.received_quantity"></td>
                                            <td class="py-2 px-2 text-right font-black" :class="item.remaining_quantity > 0 ? 'text-rose-600' : 'text-slate-400'" x-text="item.remaining_quantity"></td>
                                            <td class="py-2 px-3 text-right text-slate-600" x-text="formatMoney(item.unit_price)"></td>
                                            <td class="py-2 px-3 text-right font-black text-slate-900" x-text="formatMoney(item.amount)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Lịch sử các phiếu nhập kho đã tạo -->
                    <template x-if="detailOrder?.inventory_transactions && detailOrder.inventory_transactions.length > 0">
                        <div class="space-y-1.5 pt-2">
                            <div class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-boxes-packing text-sky-600"></i>
                                <span>Lịch Sử Các Phiếu Nhập Kho (<span x-text="detailOrder.inventory_transactions.length"></span> phiếu)</span>
                            </div>
                            <div class="space-y-2">
                                <template x-for="tx in detailOrder.inventory_transactions" :key="tx.id">
                                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between text-xs">
                                        <div>
                                            <span class="font-mono font-black text-sky-700" x-text="tx.code"></span>
                                            <span class="text-slate-500 text-[11px] ml-2" x-text="formatDate(tx.created_at)"></span>
                                            <p class="text-[11px] text-slate-600 mt-0.5" x-text="tx.notes"></p>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-black text-slate-900" x-text="formatMoney(tx.total_amount)"></div>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Hoàn thành</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 text-right">
                    <button type="button" @click="closeDetailModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL 4: SỔ THEO DÕI CÔNG NỢ NHÀ CUNG CẤP                         -->
    <!-- ================================================================= -->
    <div x-show="showDebtModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="closeDebtModal()"></div>

        <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-4xl border border-slate-200 my-8">
                
                <div class="bg-gradient-to-r from-amber-600 to-amber-800 px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black tracking-tight">Sổ Theo Dõi Công Nợ Nhà Cung Cấp</h3>
                            <p class="text-[11px] text-amber-200">Theo dõi số tiền các NCC nợ công ty (do nhận thiếu hàng đã thanh toán) · Cấn trừ nợ khi đặt hàng mới</p>
                        </div>
                    </div>
                    <button type="button" @click="closeDebtModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black text-[10.5px]">
                                <tr>
                                    <th class="py-2.5 px-3">Mã NCC</th>
                                    <th class="py-2.5 px-3">Tên Nhà Cung Cấp</th>
                                    <th class="py-2.5 px-3">SĐT / Liên hệ</th>
                                    <th class="py-2.5 px-3 text-right">NCC Đang Nợ Công Ty</th>
                                    <th class="py-2.5 px-3 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="s in suppliers" :key="s.id">
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-2.5 px-3 font-mono font-bold text-slate-600" x-text="s.code"></td>
                                        <td class="py-2.5 px-3 font-black text-slate-800" x-text="s.name"></td>
                                        <td class="py-2.5 px-3 text-slate-600" x-text="s.phone || '—'"></td>
                                        <td class="py-2.5 px-3 text-right font-black" :class="s.supplier_debt_to_company > 0 ? 'text-amber-700 font-extrabold text-sm' : 'text-slate-400'" x-text="formatMoney(s.supplier_debt_to_company)"></td>
                                        <td class="py-2.5 px-3 text-right">
                                            <a :href="'{{ url('/supplier-orders/create') }}?supplier_id=' + s.id" class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg text-xs font-bold border border-sky-200 cursor-pointer inline-flex items-center">
                                                <i class="fa-solid fa-plus mr-1"></i>Đặt hàng (Trừ nợ)
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 text-right">
                    <button type="button" @click="closeDebtModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL 5: CẬP NHẬT THANH TOÁN                                      -->
    <!-- ================================================================= -->
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="closePaymentModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative rounded-2xl bg-white shadow-2xl w-full max-w-md border border-slate-200 p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-black text-slate-900 text-sm">Cập Nhật Thanh Toán Cho NCC</h3>
                    <button type="button" @click="closePaymentModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="space-y-3 text-xs" x-if="paymentOrder">
                    <div class="bg-slate-50 p-3 rounded-xl space-y-1">
                        <div>Đơn hàng: <span class="font-mono font-black text-slate-900" x-text="paymentOrder?.code"></span></div>
                        <div>Tổng cần thanh toán: <span class="font-black text-slate-900" x-text="formatMoney(paymentOrder?.payable_amount)"></span></div>
                        <div>Đã trả trước đó: <span class="font-black text-emerald-700" x-text="formatMoney(paymentOrder?.paid_amount)"></span></div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Trạng thái thanh toán mới:</label>
                        <select x-model="paymentData.payment_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold text-xs bg-white">
                            <option value="Chưa thanh toán">Chưa thanh toán</option>
                            <option value="Thanh toán 1 phần">Thanh toán 1 phần</option>
                            <option value="Đã thanh toán">Đã thanh toán (100%)</option>
                        </select>
                    </div>

                    <div x-show="paymentData.payment_status === 'Thanh toán 1 phần'">
                        <label class="block font-bold text-slate-700 mb-1">Số tiền đã trả lũy kế:</label>
                        <input type="number" x-model="paymentData.paid_amount" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-black text-xs">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ghi chú giao dịch:</label>
                        <input type="text" x-model="paymentData.payment_note" placeholder="Ủy nhiệm chi, số hóa đơn..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="closePaymentModal()" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">Hủy</button>
                    <button type="button" @click="submitPaymentUpdate()" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black text-xs">Lưu thanh toán</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT LOGIC CHO SUPPLIER ORDERS APP -->
<script>
function supplierOrdersApp() {
    return {
        loading: false,
        saving: false,
        receiptSaving: false,
        ordersList: @json($orders->items() ?? []),
        counts: @json($counts ?? []),
        stats: @json($stats ?? []),
        suppliers: @json($suppliers ?? []),
        warehouses: @json($warehouses ?? []),
        catalogProducts: @json($products ?? []),

        selectedStatus: '{{ request("status", "") }}',
        selectedSupplier: '{{ request("supplier_id", "") }}',
        selectedPaymentStatus: '{{ request("payment_status", "") }}',
        search: '{{ request("search", "") }}',

        statusTabs: [
            { key: '', label: 'Tất cả' },
            { key: 'Chờ duyệt', label: 'Chờ duyệt' },
            { key: 'Đã duyệt', label: 'Đã duyệt' },
            { key: 'Đang giao hàng', label: 'Đang giao' },
            { key: 'Nhập một phần', label: 'Nhập 1 phần (Nợ hàng)' },
            { key: 'Hoàn thành', label: 'Hoàn thành' },
            { key: 'Đã hủy', label: 'Đã hủy' },
        ],

        // Modal States
        showCreateModal: false,
        showReceiptModal: false,
        showDetailModal: false,
        showDebtModal: false,
        showPaymentModal: false,

        selectedSupplierDebtBalance: 0,
        owingItemsList: [],

        // Form Data Tạo đơn
        formData: {
            supplier_id: '',
            warehouse_id: '',
            order_date: new Date().toISOString().slice(0, 10),
            expected_delivery_date: '',
            payment_status: 'Chưa thanh toán',
            paid_amount: 0,
            payment_method: 'Chuyển khoản',
            payment_note: '',
            apply_debt_deduction: false,
            debt_deducted_amount: 0,
            discount_amount: 0,
            other_fees: 0,
            notes: '',
            auto_approve: false,
            items: [
                { product_id: '', product_name: '', sku: '', unit: 'cái', ordered_quantity: 1, unit_price: 0, discount_percent: 0, discount_amount: 0 }
            ]
        },

        // Nhập kho data
        receiptOrder: null,
        receiptData: {
            warehouse_id: '',
            receipt_notes: '',
            shortage_resolution: 'supplier_owes_goods',
            items: []
        },
        receiptShortageCount: 0,
        receiptShortageValue: 0,
        receiptTotalReceivedValue: 0,
        receiptExcessPaidAmount: 0,

        // Chi tiết
        detailOrder: null,

        // Thanh toán
        paymentOrder: null,
        paymentData: { payment_status: 'Đã thanh toán', paid_amount: 0, payment_note: '' },

        init() {
            // Khởi tạo
        },

        setStatusFilter(status) {
            this.selectedStatus = status;
            this.fetchOrders();
        },

        fetchOrders() {
            this.loading = true;
            const params = new URLSearchParams();
            if (this.selectedStatus) params.append('status', this.selectedStatus);
            if (this.selectedSupplier) params.append('supplier_id', this.selectedSupplier);
            if (this.selectedPaymentStatus) params.append('payment_status', this.selectedPaymentStatus);
            if (this.search) params.append('search', this.search);

            fetch('{{ url("/supplier-orders") }}?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.ordersList = data.orders.data || data.orders;
                    this.counts = data.counts || this.counts;
                    this.stats = data.stats || this.stats;
                }
            })
            .catch(err => {
                console.error(err);
            })
            .finally(() => {
                this.loading = false;
            });
        },

        // ===================================
        // LOGIC TẠO ĐƠN & CÔNG NỢ NCC
        // ===================================
        openCreateModal(supplierId = null) {
            this.formData = {
                supplier_id: supplierId || '',
                warehouse_id: this.warehouses[0]?.id || '',
                order_date: new Date().toISOString().slice(0, 10),
                expected_delivery_date: '',
                payment_status: 'Chưa thanh toán',
                paid_amount: 0,
                payment_method: 'Chuyển khoản',
                payment_note: '',
                apply_debt_deduction: false,
                debt_deducted_amount: 0,
                discount_amount: 0,
                other_fees: 0,
                notes: '',
                auto_approve: false,
                items: [
                    { product_id: '', product_name: '', sku: '', unit: 'cái', ordered_quantity: 1, unit_price: 0, discount_percent: 0, discount_amount: 0 }
                ]
            };
            this.showCreateModal = true;
            if (supplierId) {
                this.onSupplierChange();
            }
        },

        closeCreateModal() {
            this.showCreateModal = false;
        },

        createOrderForSupplier(supId) {
            this.closeDebtModal();
            this.openCreateModal(supId);
        },

        onSupplierChange() {
            const supId = this.formData.supplier_id;
            this.selectedSupplierDebtBalance = 0;
            this.owingItemsList = [];
            this.formData.apply_debt_deduction = false;
            this.formData.debt_deducted_amount = 0;

            if (!supId) return;

            fetch(`{{ url('/api/suppliers') }}/${supId}/info`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.selectedSupplierDebtBalance = parseFloat(data.debt_balance) || 0;
                        this.owingItemsList = data.owing_items || [];
                        if (this.selectedSupplierDebtBalance > 0) {
                            this.formData.apply_debt_deduction = true;
                            this.applyMaxDebtDeduction();
                        }
                    }
                })
                .catch(e => console.error(e));
        },

        onToggleApplyDebt() {
            if (this.formData.apply_debt_deduction) {
                this.applyMaxDebtDeduction();
            } else {
                this.formData.debt_deducted_amount = 0;
                this.recalcTotals();
            }
        },

        applyMaxDebtDeduction() {
            const maxDeduct = Math.min(this.selectedSupplierDebtBalance, this.calcOrderTotal());
            this.formData.debt_deducted_amount = maxDeduct;
            this.recalcTotals();
        },

        addItemRow() {
            this.formData.items.push({
                product_id: '',
                product_name: '',
                sku: '',
                unit: 'cái',
                ordered_quantity: 1,
                unit_price: 0,
                discount_percent: 0,
                discount_amount: 0
            });
        },

        removeItemRow(idx) {
            if (this.formData.items.length > 1) {
                this.formData.items.splice(idx, 1);
                this.recalcTotals();
            }
        },

        onProductSelect(idx, prodId) {
            const p = this.catalogProducts.find(item => item.id == prodId);
            if (p) {
                this.formData.items[idx].product_id = p.id;
                this.formData.items[idx].product_name = p.name;
                this.formData.items[idx].sku = p.sku;
                this.formData.items[idx].unit = p.unit;
                this.formData.items[idx].unit_price = p.cost_price;
                this.recalcTotals();
            }
        },

        calcItemAmount(it) {
            const qty = parseFloat(it.ordered_quantity) || 0;
            const price = parseFloat(it.unit_price) || 0;
            const discPct = parseFloat(it.discount_percent) || 0;
            let discAmt = parseFloat(it.discount_amount) || 0;
            if (discPct > 0 && discAmt === 0) {
                discAmt = (qty * price) * (discPct / 100);
            }
            return Math.max(0, (qty * price) - discAmt);
        },

        calcItemsTotal() {
            return this.formData.items.reduce((sum, it) => sum + this.calcItemAmount(it), 0);
        },

        calcOrderTotal() {
            const itemsTot = this.calcItemsTotal();
            const disc = parseFloat(this.formData.discount_amount) || 0;
            const fees = parseFloat(this.formData.other_fees) || 0;
            return Math.max(0, itemsTot - disc + fees);
        },

        calcPayableTotal() {
            const total = this.calcOrderTotal();
            const deduct = parseFloat(this.formData.debt_deducted_amount) || 0;
            return Math.max(0, total - deduct);
        },

        calcActualPaidAmount() {
            if (this.formData.payment_status === 'Đã thanh toán') {
                return this.calcPayableTotal();
            } else if (this.formData.payment_status === 'Thanh toán 1 phần') {
                return parseFloat(this.formData.paid_amount) || 0;
            }
            return 0;
        },

        onPaymentStatusChange() {
            if (this.formData.payment_status === 'Đã thanh toán') {
                this.formData.paid_amount = this.calcPayableTotal();
            } else if (this.formData.payment_status === 'Chưa thanh toán') {
                this.formData.paid_amount = 0;
            }
        },

        recalcTotals() {
            if (this.formData.apply_debt_deduction) {
                const maxAllowed = Math.min(this.selectedSupplierDebtBalance, this.calcOrderTotal());
                if (this.formData.debt_deducted_amount > maxAllowed) {
                    this.formData.debt_deducted_amount = maxAllowed;
                }
            }
            if (this.formData.payment_status === 'Đã thanh toán') {
                this.formData.paid_amount = this.calcPayableTotal();
            }
        },

        submitCreateOrder() {
            if (!this.formData.supplier_id) {
                alert('Vui lòng chọn Nhà Cung Cấp');
                return;
            }
            const invalidItem = this.formData.items.find(it => !it.product_name || (parseFloat(it.ordered_quantity) || 0) <= 0);
            if (invalidItem) {
                alert('Vui lòng kiểm tra lại tên và số lượng sản phẩm');
                return;
            }

            this.saving = true;
            fetch('{{ url("/api/supplier-orders") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Tạo đơn đặt hàng NCC thành công!');
                    this.closeCreateModal();
                    this.fetchOrders();
                } else {
                    alert(data.message || 'Lỗi tạo đơn đặt hàng');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối máy chủ');
            })
            .finally(() => {
                this.saving = false;
            });
        },

        // ===================================
        // LOGIC NHẬP KHO & 2 CƠ CHẾ NỢ HÀNG/TIỀN
        // ===================================
        openReceiptModal(order) {
            this.receiptOrder = order;
            this.receiptData = {
                warehouse_id: order.warehouse_id || this.warehouses[0]?.id || '',
                receipt_notes: '',
                shortage_resolution: 'supplier_owes_goods',
                items: order.items.map(it => {
                    const remaining = parseFloat(it.remaining_quantity) > 0 ? parseFloat(it.remaining_quantity) : Math.max(0, parseFloat(it.ordered_quantity) - parseFloat(it.received_quantity));
                    return {
                        item_id: it.id,
                        product_name: it.product_name,
                        sku: it.sku,
                        unit: it.unit,
                        unit_price: parseFloat(it.unit_price) || 0,
                        ordered_quantity: parseFloat(it.ordered_quantity) || 0,
                        received_before: parseFloat(it.received_quantity) || 0,
                        quantity: remaining, // Mặc định điền số lượng còn thiếu
                        batch_code: '',
                        expiry_date: ''
                    };
                })
            };
            this.recalcReceiptStatus();
            this.showReceiptModal = true;
        },

        closeReceiptModal() {
            this.showReceiptModal = false;
            this.receiptOrder = null;
        },

        fillFullReceipt() {
            this.receiptData.items.forEach(it => {
                it.quantity = Math.max(0, it.ordered_quantity - it.received_before);
            });
            this.recalcReceiptStatus();
        },

        clearReceiptQuantities() {
            this.receiptData.items.forEach(it => {
                it.quantity = 0;
            });
            this.recalcReceiptStatus();
        },

        recalcReceiptStatus() {
            let shortageCount = 0;
            let shortageValue = 0;
            let totalReceivedVal = 0;

            this.receiptData.items.forEach(it => {
                const qtyThisBatch = parseFloat(it.quantity) || 0;
                const totalAfter = it.received_before + qtyThisBatch;
                const remaining = Math.max(0, it.ordered_quantity - totalAfter);
                if (remaining > 0) {
                    shortageCount += remaining;
                    shortageValue += (remaining * it.unit_price);
                }
                totalReceivedVal += (totalAfter * it.unit_price);
            });

            this.receiptShortageCount = shortageCount;
            this.receiptShortageValue = shortageValue;
            this.receiptTotalReceivedValue = totalReceivedVal;

            const paidAmt = parseFloat(this.receiptOrder?.paid_amount) || 0;
            this.receiptExcessPaidAmount = Math.max(0, paidAmt - totalReceivedVal);
        },

        calcBatchReceiptTotal() {
            if (!this.receiptData || !this.receiptData.items) return 0;
            return this.receiptData.items.reduce((sum, it) => sum + ((parseFloat(it.quantity) || 0) * it.unit_price), 0);
        },

        submitReceipt() {
            const hasQty = this.receiptData.items.some(it => (parseFloat(it.quantity) || 0) > 0);
            if (!hasQty) {
                alert('Vui lòng nhập số lượng nhận lớn hơn 0 cho ít nhất 1 sản phẩm');
                return;
            }

            if (this.receiptShortageCount > 0 && !this.receiptData.shortage_resolution) {
                alert('Đơn hàng giao thiếu. Vui lòng chọn Cơ chế 1 (NCC nợ hàng) hoặc Cơ chế 2 (NCC nợ tiền)!');
                return;
            }

            this.receiptSaving = true;
            fetch(`{{ url('/api/supplier-orders') }}/${this.receiptOrder.id}/receipt`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    warehouse_id: this.receiptData.warehouse_id,
                    receipt_notes: this.receiptData.receipt_notes,
                    shortage_resolution: this.receiptShortageCount > 0 ? this.receiptData.shortage_resolution : null,
                    received_items: this.receiptData.items.map(it => ({
                        item_id: it.item_id,
                        quantity: parseFloat(it.quantity) || 0,
                        batch_code: it.batch_code,
                        expiry_date: it.expiry_date
                    }))
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Tạo phiếu nhập kho thành công!');
                    this.closeReceiptModal();
                    this.fetchOrders();
                } else {
                    alert(data.message || 'Lỗi nhập kho');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối máy chủ');
            })
            .finally(() => {
                this.receiptSaving = false;
            });
        },

        // ===================================
        // CHI TIẾT & TIỆN ÍCH KHÁC
        // ===================================
        openDetailModal(order) {
            this.detailOrder = order;
            this.showDetailModal = true;
            fetch(`{{ url('/api/supplier-orders') }}/${order.id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.detailOrder = data.order;
                    }
                });
        },

        closeDetailModal() {
            this.showDetailModal = false;
            this.detailOrder = null;
        },

        openDebtModal() {
            this.showDebtModal = true;
        },

        closeDebtModal() {
            this.showDebtModal = false;
        },

        openSupplierModal() {
            const name = prompt('Nhập tên Nhà Cung Cấp mới:');
            if (!name) return;
            const phone = prompt('Số điện thoại liên hệ (nếu có):') || '';
            const address = prompt('Địa chỉ kho / nhà máy (nếu có):') || '';

            fetch('{{ url("/api/suppliers/save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, phone, address })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    this.suppliers.push(data.supplier);
                } else {
                    alert(data.message);
                }
            });
        },

        approveOrder(orderId) {
            if (!confirm('Bạn có chắc chắn muốn duyệt đơn đặt hàng này?')) return;
            fetch(`{{ url('/api/supplier-orders') }}/${orderId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    this.fetchOrders();
                } else {
                    alert(data.message);
                }
            });
        },

        openPaymentModal(order) {
            this.paymentOrder = order;
            this.paymentData = {
                payment_status: order.payment_status === 'Chưa thanh toán' ? 'Đã thanh toán' : order.payment_status,
                paid_amount: order.payable_amount,
                payment_note: ''
            };
            this.showPaymentModal = true;
        },

        closePaymentModal() {
            this.showPaymentModal = false;
            this.paymentOrder = null;
        },

        submitPaymentUpdate() {
            fetch(`{{ url('/api/supplier-orders') }}/${this.paymentOrder.id}/update-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.paymentData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    this.closePaymentModal();
                    this.fetchOrders();
                } else {
                    alert(data.message);
                }
            });
        },

        cancelOrder(order) {
            const reason = prompt('Nhập lý do hủy đơn đặt hàng này:');
            if (reason === null) return;

            fetch(`{{ url('/api/supplier-orders') }}/${order.id}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    this.fetchOrders();
                } else {
                    alert(data.message);
                }
            });
        },

        calcReceivedPercent(order) {
            if (!order.items || order.items.length === 0) return 0;
            let totOrdered = 0;
            let totReceived = 0;
            order.items.forEach(it => {
                totOrdered += parseFloat(it.ordered_quantity) || 0;
                totReceived += parseFloat(it.received_quantity) || 0;
            });
            if (totOrdered <= 0) return 0;
            return Math.min(100, Math.round((totReceived / totOrdered) * 100));
        },

        calcReceivedQtySummary(order) {
            if (!order.items || order.items.length === 0) return '0/0';
            let totOrdered = 0;
            let totReceived = 0;
            order.items.forEach(it => {
                totOrdered += parseFloat(it.ordered_quantity) || 0;
                totReceived += parseFloat(it.received_quantity) || 0;
            });
            return `${totReceived}/${totOrdered}`;
        },

        formatMoney(amount) {
            const val = parseFloat(amount) || 0;
            return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
        },

        formatDate(dateStr) {
            if (!dateStr) return '—';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear();
        }
    }
}
</script>
