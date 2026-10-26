@extends('layouts.app')

@section('title', 'Quản Lý Ứng Khuyến Mại Của Kho | ERP Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased w-full pb-20" x-data="promotionApp()">

    <!-- 1. Sub-Section Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto">
        <a href="{{ url('/orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <span>Đơn hàng</span>
        </a>

        <a href="{{ url('/orders?tab=consignment') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-boxes-stacked text-sm"></i>
            <span>Đơn hàng ôm</span>
        </a>

        <a href="{{ url('/orders?tab=returns') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-rotate-left text-sm"></i>
            <span>Khách trả hàng</span>
        </a>

        <a href="{{ url('/supplier-orders') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200">
            <i class="fa-solid fa-cart-shopping text-sm"></i>
            <span>Đặt hàng NCC</span>
        </a>

        <a href="{{ url('/promotion-advances') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer bg-amber-600 text-white shadow-md shadow-amber-600/20">
            <i class="fa-solid fa-gift text-sm"></i>
            <span>Ứng khuyến mãi</span>
        </a>
    </div>

    <!-- 2. Header Banner -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/orders') }}" class="w-10 h-10 rounded-full bg-amber-600 hover:bg-amber-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-600/20 cursor-pointer transition-transform hover:scale-105" title="Quay lại Đơn hàng">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Ứng Khuyến Mại Của Kho</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold border border-amber-300">Quản lý nợ KM</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">NV ghi đơn ứng trước · Cty/Hãng trả sau · Theo dõi từng NV & Tổng của Hãng · Theo dõi số hàng KM công ty nợ</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Nút Hoàn ứng từ Hãng -->
            <button type="button" @click="openSettleByBrandModal()" class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold rounded-xl text-xs border border-emerald-200 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-hand-holding-dollar text-emerald-600"></i>
                <span>Hãng trả bù hàng KM</span>
            </button>

            <!-- Nút Tạo phiếu ứng KM mới -->
            <button type="button" @click="openCreateModal()" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-black rounded-xl text-xs transition-all shadow-md shadow-amber-600/20 flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>+ Lập phiếu ứng KM</span>
            </button>
        </div>
    </div>

    <!-- 3. KPI Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Card 1: Tổng Hàng KM Đang Ứng -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold">
                <span>Hàng KM đang ứng (Chưa hoàn)</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-boxes-stacked"></i></span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-amber-700 tracking-tight" x-text="formatNumber(kpi.total_advancing_qty) + ' SP'">
                {{ number_format($kpi['total_advancing_qty']) }} SP
            </div>
            <div class="text-[11px] text-slate-400 font-semibold" x-text="'Giá trị ước tính: ' + formatMoney(kpi.total_advancing_value)">
                Giá trị ước tính: {{ number_format($kpi['total_advancing_value']) }} đ
            </div>
        </div>

        <!-- Card 2: Hãng đang nợ công ty -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold">
                <span>Hãng đang nợ trả bù</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600"><i class="fa-solid fa-building"></i></span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-700 tracking-tight" x-text="kpi.total_brands_count + ' Hãng'">
                {{ $kpi['total_brands_count'] }} Hãng
            </div>
            <div class="text-[11px] text-slate-400 font-semibold">
                Cần đối soát đòi Hãng trả bù hàng
            </div>
        </div>

        <!-- Card 3: Số NV đang ứng -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold">
                <span>Nhân viên đang đứng tên ứng</span>
                <span class="p-2 rounded-xl bg-sky-50 text-sky-600"><i class="fa-solid fa-users"></i></span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-sky-700 tracking-tight" x-text="kpi.total_staff_count + ' Nhân viên'">
                {{ $kpi['total_staff_count'] }} Nhân viên
            </div>
            <div class="text-[11px] text-slate-400 font-semibold">
                Theo dõi trách nhiệm theo từng Sale
            </div>
        </div>

        <!-- Card 4: Đã hoàn ứng tháng này -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold">
                <span>Đã hoàn ứng tháng này</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-circle-check"></i></span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-700 tracking-tight" x-text="formatNumber(kpi.month_settled_qty) + ' SP'">
                {{ number_format($kpi['month_settled_qty']) }} SP
            </div>
            <div class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-arrow-trend-up text-[10px]"></i>
                <span>Tất toán nợ KM đều đặn</span>
            </div>
        </div>
    </div>

    <!-- 4. Main Content: 3 Tabs điều hướng -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        
        <!-- Tab Bar Header -->
        <div class="border-b border-slate-200 px-4 pt-3 flex items-center justify-between flex-wrap gap-2 bg-slate-50/50">
            <div class="flex items-center gap-1 sm:gap-2">
                <button type="button" @click="activeTab = 'staff'" 
                        :class="activeTab === 'staff' ? 'border-amber-600 text-amber-700 bg-white shadow-2xs font-black' : 'border-transparent text-slate-500 hover:text-slate-800 font-bold'"
                        class="px-4 py-2.5 border-b-2 text-xs flex items-center gap-2 rounded-t-xl transition-all cursor-pointer">
                    <i class="fa-solid fa-user-group text-sm"></i>
                    <span>1. Theo dõi theo Nhân viên</span>
                </button>

                <button type="button" @click="activeTab = 'brand'" 
                        :class="activeTab === 'brand' ? 'border-amber-600 text-amber-700 bg-white shadow-2xs font-black' : 'border-transparent text-slate-500 hover:text-slate-800 font-bold'"
                        class="px-4 py-2.5 border-b-2 text-xs flex items-center gap-2 rounded-t-xl transition-all cursor-pointer">
                    <i class="fa-solid fa-building-flag text-sm"></i>
                    <span>2. Tổng hợp theo Hãng</span>
                </button>

                <button type="button" @click="activeTab = 'details'" 
                        :class="activeTab === 'details' ? 'border-amber-600 text-amber-700 bg-white shadow-2xs font-black' : 'border-transparent text-slate-500 hover:text-slate-800 font-bold'"
                        class="px-4 py-2.5 border-b-2 text-xs flex items-center gap-2 rounded-t-xl transition-all cursor-pointer">
                    <i class="fa-solid fa-list-check text-sm"></i>
                    <span>3. Sổ chi tiết các khoản nợ KM</span>
                </button>
            </div>

            <!-- Export Excel Button -->
            <button type="button" @click="exportCurrentTabExcel()" class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold flex items-center gap-1.5 shadow-2xs cursor-pointer mb-2">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Xuất Excel</span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: THEO DÕI THEO TỪNG NHÂN VIÊN (SALE) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'staff'" class="p-4 sm:p-5 space-y-4">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>Danh sách nhân viên kinh doanh đứng tên ứng hàng khuyến mại từ kho:</span>
                <span class="font-bold text-slate-700" x-text="staffSummary.length + ' nhân viên'"></span>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-2xl shadow-2xs">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black text-[10.5px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">STT</th>
                            <th class="py-3 px-4">Nhân Viên Kinh Doanh</th>
                            <th class="py-3 px-4 text-center">Số Phiếu Ứng</th>
                            <th class="py-3 px-4 text-right">Tổng SL Đã Ứng</th>
                            <th class="py-3 px-4 text-right">Đã Hoàn Ứng</th>
                            <th class="py-3 px-4 text-right">Số Hàng Còn Nợ Kho</th>
                            <th class="py-3 px-4 text-right">Giá Trị Nợ Ước Tính</th>
                            <th class="py-3 px-4 text-center w-28">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(st, index) in staffSummary" :key="index">
                            <tr class="hover:bg-amber-50/40 transition-colors">
                                <td class="py-3 px-4 text-center font-bold text-slate-400" x-text="index + 1"></td>
                                <td class="py-3 px-4 font-black text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-black flex items-center justify-center text-xs" x-text="st.staff_name ? st.staff_name.charAt(0) : 'N'"></div>
                                        <div>
                                            <span x-text="st.staff_name || 'NVKD Chưa gán'"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-700">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-800 text-[11px]" x-text="st.total_advances_count + ' phiếu'"></span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-800" x-text="formatNumber(st.total_advance_qty)"></td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700" x-text="formatNumber(st.total_returned_qty)"></td>
                                <td class="py-3 px-4 text-right">
                                    <span :class="Number(st.total_remaining_qty) > 0 ? 'text-rose-700 font-black bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200' : 'text-emerald-700 font-bold'" 
                                          x-text="formatNumber(st.total_remaining_qty)"></span>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900" x-text="formatMoney(st.total_remaining_value)"></td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" @click="filterByStaff(st.staff_id, st.staff_name)" class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-[11px] font-bold cursor-pointer">
                                        Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="staffSummary.length === 0">
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 font-medium">Chưa có dữ liệu ứng khuyến mại nào theo nhân viên.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: TỔNG HỢP THEO HÃNG (BRAND SUMMARY)  -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'brand'" class="p-4 sm:p-5 space-y-4">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>Số lượng hàng khuyến mại công ty đã ứng trước cho các Hãng (Hãng nợ trả bù cho công ty):</span>
                <span class="font-bold text-slate-700" x-text="brandSummary.length + ' dòng sản phẩm Hãng'"></span>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-2xl shadow-2xs">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black text-[10.5px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Tên Hãng / Thương Hiệu</th>
                            <th class="py-3 px-4">Mã SP</th>
                            <th class="py-3 px-4">Sản Phẩm Khuyến Mại</th>
                            <th class="py-3 px-4 text-center">ĐVT</th>
                            <th class="py-3 px-4 text-right">Tổng Cty Đã Ứng</th>
                            <th class="py-3 px-4 text-right">Hãng Đã Trả Bù</th>
                            <th class="py-3 px-4 text-right">Hãng Còn Nợ Cty</th>
                            <th class="py-3 px-4 text-right">Giá Trị Nợ</th>
                            <th class="py-3 px-4 text-center w-36">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(br, index) in brandSummary" :key="index">
                            <tr class="hover:bg-amber-50/40 transition-colors">
                                <td class="py-3 px-4 font-black text-slate-900">
                                    <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-900 border border-purple-200 text-[11px] font-black" x-text="br.brand_name || 'Hãng Khác'"></span>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-600" x-text="br.product_sku || '---'"></td>
                                <td class="py-3 px-4 font-bold text-slate-800" x-text="br.product_name"></td>
                                <td class="py-3 px-4 text-center font-medium text-slate-500" x-text="br.unit || 'cái'"></td>
                                <td class="py-3 px-4 text-right font-bold text-slate-800" x-text="formatNumber(br.total_advance_qty)"></td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700" x-text="formatNumber(br.total_returned_qty)"></td>
                                <td class="py-3 px-4 text-right">
                                    <span :class="Number(br.total_remaining_qty) > 0 ? 'text-rose-700 font-black bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200' : 'text-emerald-700 font-bold'" 
                                          x-text="formatNumber(br.total_remaining_qty)"></span>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900" x-text="formatMoney(br.total_remaining_value)"></td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" @click="quickSettleForBrand(br)" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-bold cursor-pointer" title="Nhập hàng trả bù từ Hãng">
                                        <i class="fa-solid fa-arrow-down-long mr-1"></i>Hoàn ứng
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="brandSummary.length === 0">
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400 font-medium">Chưa có dữ liệu nợ khuyến mại nào theo Hãng.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: SỔ CHI TIẾT TẤT CẢ PHIẾU ỨNG KM     -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'details'" class="p-4 sm:p-5 space-y-4">
            
            <!-- Bộ lọc tìm kiếm -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tìm kiếm</label>
                    <input type="text" x-model="filters.search" @input.debounce.300ms="fetchData()" placeholder="Mã phiếu, mã đơn, tên SP, tên khách..." class="w-full px-3 py-1.5 rounded-xl border border-slate-300 font-medium bg-white focus:border-amber-500 outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Lọc theo Hãng</label>
                    <select x-model="filters.brand_name" @change="fetchData()" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 font-bold bg-white focus:border-amber-500 outline-none">
                        <option value="">-- Tất cả Hãng --</option>
                        @foreach($brandsList as $b)
                            <option value="{{ $b->name }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Lọc theo Nhân Viên</label>
                    <select x-model="filters.staff_id" @change="fetchData()" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 font-bold bg-white focus:border-amber-500 outline-none">
                        <option value="">-- Tất cả Nhân viên --</option>
                        @foreach($staffList as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Trạng thái nợ</label>
                    <select x-model="filters.status" @change="fetchData()" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 font-bold bg-white focus:border-amber-500 outline-none">
                        <option value="all">-- Tất cả trạng thái --</option>
                        <option value="pending">⏳ Đang nợ (Chưa hoàn)</option>
                        <option value="partially_settled">🔄 Đã hoàn một phần</option>
                        <option value="settled">✅ Đã hoàn tất (Hết nợ)</option>
                    </select>
                </div>
            </div>

            <!-- Bảng danh sách phiếu ứng chi tiết -->
            <div class="overflow-x-auto border border-slate-200 rounded-2xl shadow-2xs">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black text-[10.5px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-3.5">Mã Phiếu / Đơn</th>
                            <th class="py-3 px-3.5">Nhân Viên</th>
                            <th class="py-3 px-3.5">Khách Hàng</th>
                            <th class="py-3 px-3.5">Hãng (Brand)</th>
                            <th class="py-3 px-3.5">Sản Phẩm Khuyến Mại</th>
                            <th class="py-3 px-3.5 text-center">ĐVT</th>
                            <th class="py-3 px-3.5 text-right">SL Ứng</th>
                            <th class="py-3 px-3.5 text-right">Đã Hoàn</th>
                            <th class="py-3 px-3.5 text-right">Còn Nợ Kho</th>
                            <th class="py-3 px-3.5 text-center">Trạng Thái</th>
                            <th class="py-3 px-3.5 text-center w-28">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(it, index) in advancesList" :key="it.id">
                            <tr class="hover:bg-amber-50/40 transition-colors">
                                <td class="py-3 px-3.5 font-bold text-slate-900">
                                    <div class="font-mono text-amber-700" x-text="it.code"></div>
                                    <div class="text-[10px] text-slate-400" x-show="it.order_code" x-text="'Đơn: ' + it.order_code"></div>
                                </td>
                                <td class="py-3 px-3.5 font-bold text-slate-800" x-text="it.staff_name || '---'"></td>
                                <td class="py-3 px-3.5 text-slate-700" x-text="it.customer_name || 'Khách vãng lai'"></td>
                                <td class="py-3 px-3.5 font-black text-purple-900">
                                    <span class="px-2 py-0.5 rounded bg-purple-50 border border-purple-200 text-[10.5px]" x-text="it.brand_name || 'Hãng Khác'"></span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="font-bold text-slate-900" x-text="it.product_name"></div>
                                    <div class="text-[10.5px] font-mono text-slate-400" x-text="it.product_sku"></div>
                                </td>
                                <td class="py-3 px-3.5 text-center font-medium text-slate-500" x-text="it.unit"></td>
                                <td class="py-3 px-3.5 text-right font-bold text-slate-800" x-text="formatNumber(it.advance_quantity)"></td>
                                <td class="py-3 px-3.5 text-right font-bold text-emerald-700" x-text="formatNumber(it.returned_quantity)"></td>
                                <td class="py-3 px-3.5 text-right">
                                    <span :class="Number(it.remaining_quantity) > 0 ? 'text-rose-700 font-black bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200' : 'text-emerald-700 font-bold'" 
                                          x-text="formatNumber(it.remaining_quantity)"></span>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <span x-show="it.status === 'pending'" class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[10.5px] font-black border border-amber-300">⏳ Đang nợ</span>
                                    <span x-show="it.status === 'partially_settled'" class="px-2 py-0.5 rounded-full bg-sky-100 text-sky-900 text-[10.5px] font-black border border-sky-300">🔄 Hoàn 1 phần</span>
                                    <span x-show="it.status === 'settled'" class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900 text-[10.5px] font-black border border-emerald-300">✅ Đã hoàn đủ</span>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <button type="button" x-show="Number(it.remaining_quantity) > 0" @click="openSingleSettleModal(it)" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] cursor-pointer shadow-2xs">
                                        Hoàn ứng
                                    </button>
                                    <span x-show="Number(it.remaining_quantity) <= 0" class="text-slate-400 text-xs font-semibold">Đã xong</span>
                                </td>
                            </tr>
                        </template>
                        <template x-if="advancesList.length === 0">
                            <tr>
                                <td colspan="11" class="py-12 text-center text-slate-400 font-medium">Không tìm thấy phiếu ứng khuyến mại nào phù hợp.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: TẠO PHIẾU ỨNG KHUYẾN MẠI MỚI      -->
    <!-- ========================================== -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200" @click.outside="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fa-solid fa-gift"></i></span>
                    <h3 class="font-black text-slate-900 text-base">Lập Phiếu Ứng Khuyến Mại Từ Kho</h3>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form @submit.prevent="submitCreateAdvance()" class="space-y-3.5 text-xs">
                <!-- Product Search Combobox -->
                <div class="relative" @click.outside="isProductDropdownOpen = false">
                    <label class="block font-bold text-slate-700 mb-1">
                        Chọn Hàng Khuyến Mại Ứng Từ Kho <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input 
                            type="text" 
                            x-model="productSearchQuery" 
                            @focus="isProductDropdownOpen = true"
                            @input="isProductDropdownOpen = true"
                            placeholder="Gõ mã SKU hoặc tên sản phẩm để tìm..." 
                            class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-slate-300 font-bold text-xs bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all placeholder:font-normal placeholder:text-slate-400"
                            autocomplete="off"
                        >
                        <template x-if="createForm.product_id">
                            <button 
                                type="button" 
                                @click="clearSelectedProduct()" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                title="Bỏ chọn sản phẩm"
                            >
                                <i class="fa-solid fa-circle-xmark text-sm"></i>
                            </button>
                        </template>
                    </div>

                    <!-- Badge sản phẩm đã chọn -->
                    <template x-if="selectedProduct">
                        <div class="mt-1.5 p-2 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="px-2 py-0.5 rounded-md bg-amber-600 text-white font-mono font-bold text-[10px] shrink-0" x-text="selectedProduct.sku"></span>
                                <span class="font-bold text-slate-800 truncate" x-text="selectedProduct.name"></span>
                                <span class="text-slate-500 text-[11px] shrink-0" x-text="'(' + (selectedProduct.unit || 'cái') + ')'"></span>
                            </div>
                            <span class="font-extrabold text-amber-700 text-xs shrink-0 ml-2" x-text="formatCurrency(selectedProduct.cost_price || selectedProduct.price)"></span>
                        </div>
                    </template>

                    <!-- Dropdown danh sách kết quả -->
                    <div 
                        x-show="isProductDropdownOpen" 
                        x-cloak 
                        class="absolute left-0 right-0 top-full mt-1 z-50 bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs"
                    >
                        <template x-for="p in filteredProducts" :key="p.id">
                            <div 
                                @click="selectProduct(p)" 
                                class="p-2.5 hover:bg-amber-50/80 cursor-pointer flex items-center justify-between transition-colors group"
                                :class="createForm.product_id == p.id ? 'bg-amber-50 font-bold' : ''"
                            >
                                <div class="min-w-0 pr-2">
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 group-hover:bg-amber-200 text-slate-700 group-hover:text-amber-900 font-mono font-bold text-[10px]" x-text="p.sku"></span>
                                        <span class="text-slate-800 font-semibold group-hover:text-amber-900 truncate" x-text="p.name"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        ĐVT: <span class="font-semibold text-slate-600" x-text="p.unit || 'cái'"></span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-slate-700" x-text="formatCurrency(p.cost_price || p.price)"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="filteredProducts.length === 0">
                            <div class="p-4 text-center text-slate-400 italic">
                                Không tìm thấy sản phẩm nào khớp với "<span x-text="productSearchQuery"></span>"
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số lượng ứng <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" x-model="createForm.advance_quantity" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-black text-sm text-slate-900 bg-white focus:border-amber-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Hãng / Thương hiệu</label>
                        <select x-model="createForm.brand_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold bg-white focus:border-amber-500 outline-none">
                            <option value="">-- Chọn Hãng --</option>
                            @foreach($brandsList as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nhân viên đứng tên ứng</label>
                        <select x-model="createForm.staff_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold bg-white focus:border-amber-500 outline-none">
                            <option value="">-- Chọn Nhân viên --</option>
                            @foreach($staffList as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kho xuất ứng</label>
                        <select x-model="createForm.warehouse_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold bg-white focus:border-amber-500 outline-none">
                            @foreach($warehousesList as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã đơn hàng liên kết (nếu có)</label>
                        <input type="text" x-model="createForm.order_code" placeholder="VD: DH-2026-0001" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-medium bg-white focus:border-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Khách hàng nhận quà</label>
                        <input type="text" x-model="createForm.customer_name" placeholder="Tên đại lý / khách hàng" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-medium bg-white focus:border-amber-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú lý do ứng</label>
                    <textarea x-model="createForm.notes" rows="2" placeholder="Ví dụ: Ứng quà tặng CTKM Mua 10 tặng 1 cho khách, Hãng trả bù đợt cuối tháng..." class="w-full px-3 py-2 rounded-xl border border-slate-300 font-medium bg-white focus:border-amber-500 outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl border border-slate-300 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" :disabled="isSubmitting" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black cursor-pointer shadow-md shadow-amber-600/20 disabled:opacity-50">
                        <span x-show="!isSubmitting">Lưu Phiếu Ứng</span>
                        <span x-show="isSubmitting">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: HOÀN ỨNG CHO 1 PHIẾU CỤ THỂ      -->
    <!-- ========================================== -->
    <div x-show="showSingleSettleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200" @click.outside="showSingleSettleModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fa-solid fa-arrow-down-long"></i></span>
                    <h3 class="font-black text-slate-900 text-base">Hoàn Ứng Khuyến Mại</h3>
                </div>
                <button type="button" @click="showSingleSettleModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <template x-if="activeAdvance">
                <form @submit.prevent="submitSingleSettle()" class="space-y-3.5 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-bold">Mã phiếu:</span>
                            <span class="font-mono font-black text-amber-700" x-text="activeAdvance.code"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-bold">Sản phẩm:</span>
                            <span class="font-bold text-slate-800" x-text="activeAdvance.product_name"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-bold">Hãng / Nhân viên:</span>
                            <span class="font-bold text-purple-900" x-text="(activeAdvance.brand_name || 'Hãng') + ' · ' + (activeAdvance.staff_name || '')"></span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                            <span class="text-slate-600 font-bold">Số lượng còn nợ:</span>
                            <span class="font-black text-rose-700 text-sm" x-text="formatNumber(activeAdvance.remaining_quantity) + ' ' + activeAdvance.unit"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số lượng hoàn ứng đợt này <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" :max="activeAdvance.remaining_quantity" x-model="singleSettleForm.quantity" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-black text-base text-emerald-700 bg-white focus:border-emerald-500 outline-none" required>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mã chứng từ / Phiếu nhập kho trả bù</label>
                        <input type="text" x-model="singleSettleForm.reference_code" placeholder="VD: PNK-TRA-BU-001 hoặc số hóa đơn NCC" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-medium bg-white focus:border-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ghi chú đối soát</label>
                        <textarea x-model="singleSettleForm.notes" rows="2" placeholder="VD: Hãng đã trả đủ hàng đợt 2, đối soát tất toán..." class="w-full px-3 py-2 rounded-xl border border-slate-300 font-medium bg-white focus:border-emerald-500 outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="showSingleSettleModal = false" class="px-4 py-2 rounded-xl border border-slate-300 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Hủy</button>
                        <button type="submit" :disabled="isSubmitting" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black cursor-pointer shadow-md shadow-emerald-600/20 disabled:opacity-50">
                            <span x-show="!isSubmitting">Xác Nhận Hoàn Ứng</span>
                            <span x-show="isSubmitting">Đang xử lý...</span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 3: HÃNG TRẢ BÙ HÀNG LOẠT THEO HÃNG  -->
    <!-- ========================================== -->
    <div x-show="showSettleByBrandModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200" @click.outside="showSettleByBrandModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fa-solid fa-building-flag"></i></span>
                    <h3 class="font-black text-slate-900 text-base">Hãng Trả Bù Hàng Khuyến Mại</h3>
                </div>
                <button type="button" @click="showSettleByBrandModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form @submit.prevent="submitSettleByBrand()" class="space-y-3.5 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Chọn Hãng / Nhà Cung Cấp trả bù <span class="text-rose-500">*</span></label>
                    <select x-model="settleBrandForm.brand_name" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold bg-white focus:border-emerald-500 outline-none" required>
                        <option value="">-- Chọn Hãng --</option>
                        @foreach($brandsList as $b)
                            <option value="{{ $b->name }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Product Search Combobox for Settle by Brand -->
                <div class="relative" @click.outside="isSettleProductDropdownOpen = false">
                    <label class="block font-bold text-slate-700 mb-1">
                        Chọn Mặt Hàng KM Hãng trả về <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input 
                            type="text" 
                            x-model="settleProductSearchQuery" 
                            @focus="isSettleProductDropdownOpen = true"
                            @input="isSettleProductDropdownOpen = true"
                            placeholder="Gõ mã SKU hoặc tên mặt hàng Hãng trả..." 
                            class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-slate-300 font-bold text-xs bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all placeholder:font-normal placeholder:text-slate-400"
                            autocomplete="off"
                        >
                        <template x-if="settleBrandForm.product_id">
                            <button 
                                type="button" 
                                @click="clearSelectedSettleProduct()" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                title="Bỏ chọn mặt hàng"
                            >
                                <i class="fa-solid fa-circle-xmark text-sm"></i>
                            </button>
                        </template>
                    </div>

                    <!-- Badge mặt hàng đã chọn -->
                    <template x-if="selectedSettleProduct">
                        <div class="mt-1.5 p-2 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-600 text-white font-mono font-bold text-[10px] shrink-0" x-text="selectedSettleProduct.sku"></span>
                                <span class="font-bold text-slate-800 truncate" x-text="selectedSettleProduct.name"></span>
                                <span class="text-slate-500 text-[11px] shrink-0" x-text="'(' + (selectedSettleProduct.unit || 'cái') + ')'"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Dropdown danh sách kết quả -->
                    <div 
                        x-show="isSettleProductDropdownOpen" 
                        x-cloak 
                        class="absolute left-0 right-0 top-full mt-1 z-50 bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs"
                    >
                        <template x-for="p in filteredSettleProducts" :key="p.id">
                            <div 
                                @click="selectSettleProduct(p)" 
                                class="p-2.5 hover:bg-emerald-50/80 cursor-pointer flex items-center justify-between transition-colors group"
                                :class="settleBrandForm.product_id == p.id ? 'bg-emerald-50 font-bold' : ''"
                            >
                                <div class="min-w-0 pr-2">
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 group-hover:bg-emerald-200 text-slate-700 group-hover:text-emerald-900 font-mono font-bold text-[10px]" x-text="p.sku"></span>
                                        <span class="text-slate-800 font-semibold group-hover:text-emerald-900 truncate" x-text="p.name"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        ĐVT: <span class="font-semibold text-slate-600" x-text="p.unit || 'cái'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="filteredSettleProducts.length === 0">
                            <div class="p-4 text-center text-slate-400 italic">
                                Không tìm thấy mặt hàng nào khớp với "<span x-text="settleProductSearchQuery"></span>"
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tổng số lượng Hãng giao trả đợt này <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" x-model="settleBrandForm.quantity" placeholder="Nhập số lượng hàng KM Hãng trả" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-black text-base text-emerald-700 bg-white focus:border-emerald-500 outline-none" required>
                    <div class="text-[10.5px] text-slate-400 mt-1 italic leading-tight">
                        * Hệ thống sẽ tự động phân bổ hoàn ứng theo thứ tự đơn cũ nhất trước (FIFO) cho Hãng này.
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mã phiếu nhập kho trả bù</label>
                    <input type="text" x-model="settleBrandForm.reference_code" placeholder="VD: PNK-TRA-BU-20261024" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-medium bg-white focus:border-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi chú đối soát với Hãng</label>
                    <textarea x-model="settleBrandForm.notes" rows="2" placeholder="Ghi chú đối soát theo biên bản trả thưởng KM tháng..." class="w-full px-3 py-2 rounded-xl border border-slate-300 font-medium bg-white focus:border-emerald-500 outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showSettleByBrandModal = false" class="px-4 py-2 rounded-xl border border-slate-300 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" :disabled="isSubmitting" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black cursor-pointer shadow-md shadow-emerald-600/20 disabled:opacity-50">
                        <span x-show="!isSubmitting">Xác Nhận Hãng Trả Bù</span>
                        <span x-show="isSubmitting">Đang đối soát...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- SCRIPT CHO TRANG ỨNG KHUYẾN MẠI -->
<script>
function promotionApp() {
    return {
        activeTab: 'staff', // staff | brand | details
        kpi: @json($kpi),
        staffSummary: @json($staffSummary),
        brandSummary: @json($brandSummary),
        advancesList: @json($advances->items()),

        filters: {
            search: '{{ request("search") }}',
            brand_name: '{{ request("brand_name") }}',
            staff_id: '{{ request("staff_id") }}',
            status: '{{ request("status") ?: "all" }}',
        },

        // Modal states
        showCreateModal: false,
        showSingleSettleModal: false,
        showSettleByBrandModal: false,
        isSubmitting: false,
        activeAdvance: null,

        allProducts: @json($productsList),

        // 1. Tìm kiếm sản phẩm trong Modal Lập Phiếu Ứng
        productSearchQuery: '',
        isProductDropdownOpen: false,
        selectedProduct: null,

        get filteredProducts() {
            if (!this.productSearchQuery || !this.productSearchQuery.trim()) {
                return this.allProducts.slice(0, 50);
            }
            const q = this.productSearchQuery.toLowerCase().trim();
            return this.allProducts.filter(p => {
                const name = (p.name || '').toLowerCase();
                const sku = (p.sku || '').toLowerCase();
                return name.includes(q) || sku.includes(q);
            }).slice(0, 50);
        },

        selectProduct(p) {
            this.createForm.product_id = p.id;
            this.selectedProduct = p;
            this.productSearchQuery = p.sku + ' - ' + p.name;
            this.isProductDropdownOpen = false;
            if (p.brand_id) {
                this.createForm.brand_id = p.brand_id;
            }
        },

        clearSelectedProduct() {
            this.createForm.product_id = '';
            this.selectedProduct = null;
            this.productSearchQuery = '';
            this.isProductDropdownOpen = true;
        },

        // 2. Tìm kiếm sản phẩm trong Modal Hãng Trả Bù
        settleProductSearchQuery: '',
        isSettleProductDropdownOpen: false,
        selectedSettleProduct: null,

        get filteredSettleProducts() {
            if (!this.settleProductSearchQuery || !this.settleProductSearchQuery.trim()) {
                return this.allProducts.slice(0, 50);
            }
            const q = this.settleProductSearchQuery.toLowerCase().trim();
            return this.allProducts.filter(p => {
                const name = (p.name || '').toLowerCase();
                const sku = (p.sku || '').toLowerCase();
                return name.includes(q) || sku.includes(q);
            }).slice(0, 50);
        },

        selectSettleProduct(p) {
            this.settleBrandForm.product_id = p.id;
            this.selectedSettleProduct = p;
            this.settleProductSearchQuery = p.sku + ' - ' + p.name;
            this.isSettleProductDropdownOpen = false;
        },

        clearSelectedSettleProduct() {
            this.settleBrandForm.product_id = '';
            this.selectedSettleProduct = null;
            this.settleProductSearchQuery = '';
            this.isSettleProductDropdownOpen = true;
        },

        createForm: {
            product_id: '',
            advance_quantity: 1,
            brand_id: '',
            staff_id: '',
            warehouse_id: '{{ $warehousesList[0]->id ?? "" }}',
            order_code: '',
            customer_name: '',
            notes: ''
        },

        singleSettleForm: {
            quantity: 1,
            reference_code: '',
            notes: ''
        },

        settleBrandForm: {
            brand_name: '',
            product_id: '',
            quantity: 1,
            reference_code: '',
            notes: ''
        },

        openCreateModal() {
            this.createForm = {
                product_id: '',
                advance_quantity: 1,
                brand_id: '',
                staff_id: '',
                warehouse_id: '{{ $warehousesList[0]->id ?? "" }}',
                order_code: '',
                customer_name: '',
                notes: ''
            };
            this.productSearchQuery = '';
            this.selectedProduct = null;
            this.isProductDropdownOpen = false;
            this.showCreateModal = true;
        },

        async submitCreateAdvance() {
            if (!this.createForm.product_id || !this.createForm.advance_quantity) {
                alert('Vui lòng chọn sản phẩm và nhập số lượng ứng!');
                return;
            }
            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('{{ url("/promotion-advances") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json' },
                    body: JSON.stringify(this.createForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Lập phiếu ứng KM thành công!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi lưu phiếu ứng.');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ.');
            } finally {
                this.isSubmitting = false;
            }
        },

        openSingleSettleModal(item) {
            this.activeAdvance = item;
            this.singleSettleForm = {
                quantity: item.remaining_quantity,
                reference_code: '',
                notes: ''
            };
            this.showSingleSettleModal = true;
        },

        async submitSingleSettle() {
            if (!this.activeAdvance) return;
            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`{{ url("/promotion-advances") }}/${this.activeAdvance.id}/settle`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json' },
                    body: JSON.stringify(this.singleSettleForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Hoàn ứng thành công!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi khi hoàn ứng.');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ.');
            } finally {
                this.isSubmitting = false;
            }
        },

        openSettleByBrandModal() {
            this.settleBrandForm = {
                brand_name: '',
                product_id: '',
                quantity: 1,
                reference_code: 'PNK-TRA-BU-' + new Date().toISOString().slice(0,10).replace(/-/g,''),
                notes: ''
            };
            this.settleProductSearchQuery = '';
            this.selectedSettleProduct = null;
            this.isSettleProductDropdownOpen = false;
            this.showSettleByBrandModal = true;
        },

        quickSettleForBrand(brandRow) {
            this.settleBrandForm = {
                brand_name: brandRow.brand_name,
                product_id: brandRow.product_id,
                quantity: brandRow.total_remaining_qty,
                reference_code: 'PNK-TRA-BU-' + new Date().toISOString().slice(0,10).replace(/-/g,''),
                notes: `Hãng ${brandRow.brand_name} giao bù ${brandRow.product_name}`
            };
            const p = this.allProducts.find(x => x.id == brandRow.product_id);
            if (p) {
                this.selectedSettleProduct = p;
                this.settleProductSearchQuery = p.sku + ' - ' + p.name;
            } else {
                this.selectedSettleProduct = null;
                this.settleProductSearchQuery = (brandRow.product_sku || '') + ' - ' + (brandRow.product_name || '');
            }
            this.isSettleProductDropdownOpen = false;
            this.showSettleByBrandModal = true;
        },

        async submitSettleByBrand() {
            if (!this.settleBrandForm.brand_name || !this.settleBrandForm.product_id || !this.settleBrandForm.quantity) {
                alert('Vui lòng chọn Hãng, sản phẩm và nhập số lượng hoàn ứng!');
                return;
            }
            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('{{ url("/promotion-advances/settle-by-brand") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json' },
                    body: JSON.stringify(this.settleBrandForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Hãng trả bù thành công!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi khi trả bù từ Hãng.');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ.');
            } finally {
                this.isSubmitting = false;
            }
        },

        filterByStaff(staffId, staffName) {
            this.filters.staff_id = staffId || '';
            this.activeTab = 'details';
            this.fetchData();
        },

        async fetchData() {
            const params = new URLSearchParams();
            if (this.filters.search) params.append('search', this.filters.search);
            if (this.filters.brand_name) params.append('brand_name', this.filters.brand_name);
            if (this.filters.staff_id) params.append('staff_id', this.filters.staff_id);
            if (this.filters.status && this.filters.status !== 'all') params.append('status', this.filters.status);

            try {
                const res = await fetch(`{{ url("/promotion-advances") }}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success) {
                    this.advancesList = json.advances?.data || json.advances || [];
                }
            } catch (e) {
                console.error('Error fetching filtered data:', e);
            }
        },

        exportCurrentTabExcel() {
            let csvContent = "\uFEFF";
            let fileName = 'Bao_cao_ung_khuyen_mai.csv';

            if (this.activeTab === 'staff') {
                fileName = 'Bao_cao_KM_theo_nhan_vien.csv';
                csvContent += "STT,Nhan Vien,So Phieu Ung,Tong Da Ung,Da Hoan Ung,Con No Kho,Gia Tri No (d)\n";
                this.staffSummary.forEach((st, idx) => {
                    csvContent += `${idx + 1},"${(st.staff_name || '').replace(/"/g, '""')}",${st.total_advances_count},${st.total_advance_qty},${st.total_returned_qty},${st.total_remaining_qty},${st.total_remaining_value}\n`;
                });
            } else if (this.activeTab === 'brand') {
                fileName = 'Doi_soat_KM_voi_Hang.csv';
                csvContent += "STT,Hang / Thuong Hieu,Ma SP,Ten San Pham KM,DVT,Tong Cty Da Ung,Hang Da Tra Bu,Hang Con No Cty,Gia Tri No (d)\n";
                this.brandSummary.forEach((br, idx) => {
                    csvContent += `${idx + 1},"${(br.brand_name || '').replace(/"/g, '""')}","${br.product_sku || ''}","${(br.product_name || '').replace(/"/g, '""')}",${br.unit || 'cai'},${br.total_advance_qty},${br.total_returned_qty},${br.total_remaining_qty},${br.total_remaining_value}\n`;
                });
            } else {
                fileName = 'Chi_tiet_phieu_ung_khuyen_mai.csv';
                csvContent += "Ma Phieu,Ma Don,Nhan Vien,Khach Hang,Hang,Ma SP,Ten San Pham KM,DVT,SL Ung,Da Hoan,Con No,Trang Thai\n";
                this.advancesList.forEach(it => {
                    csvContent += `"${it.code}","${it.order_code || ''}","${(it.staff_name || '').replace(/"/g, '""')}","${(it.customer_name || '').replace(/"/g, '""')}","${it.brand_name || ''}","${it.product_sku || ''}","${(it.product_name || '').replace(/"/g, '""')}",${it.unit},${it.advance_quantity},${it.returned_quantity},${it.remaining_quantity},"${it.status}"\n`;
                });
            }

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        formatNumber(val) {
            if (val === null || val === undefined || isNaN(val)) return '0';
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val) * 100) / 100);
        },

        formatMoney(val) {
            if (val === null || val === undefined || isNaN(val)) return '0 đ';
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val))) + ' đ';
        }
    };
}
</script>
@endsection
