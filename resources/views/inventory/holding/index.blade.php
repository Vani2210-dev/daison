@extends('layouts.app')

@section('title', 'Quản Lý Kho Hàng Ôm Nhân Viên (Hàng Ôm & Ký Gửi NVKD) | ERP Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased" x-data="employeeHoldingApp()">

<!-- 2. Header Banner -->
    <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-2xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <a href="{{ url('/cat_products') }}" class="w-8 h-8 rounded-xl bg-[#1b4a8c] hover:bg-blue-800 text-white flex items-center justify-center shrink-0 shadow-xs cursor-pointer transition-transform active:scale-95" title="Quay lại danh mục sản phẩm">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-[#1b4a8c]"></i>
                    <span>Kho Hàng Ôm Nhân Viên</span>
                </h1>
                <p class="text-[11px] text-slate-500 hidden sm:block">Theo dõi tồn kho hàng ôm, ký gửi thị trường và lịch sử xuất hàng ôm của từng nhân viên</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/orders?tab=consignment') }}" class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-file-invoice-dollar text-amber-600"></i>
                <span>Đơn giữ ({{ $totalConsignmentOrdersActive ?? 0 }})</span>
            </a>
            <a href="{{ route('inventory.holding_sale.create') }}" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-1.5 cursor-pointer shadow-xs no-underline">
                <i class="fa-solid fa-cart-shopping text-xs"></i>
                <span class="hidden sm:inline">Bán hàng</span> kho ôm
            </a>
            <button type="button" @click="openDispatchModal()" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 cursor-pointer shadow-xs">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Xuất ôm</span>
            </button>
            <button type="button" @click="window.location.reload()" class="p-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs cursor-pointer" title="Làm mới">
                <i class="fa-solid fa-arrows-rotate text-[#1b4a8c]"></i>
            </button>
        </div>
    </div>

<div class="bg-white rounded-2xl p-1.5 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto text-xs">
        <button type="button" @click="setMainTab('stocks')" 
                :class="activeMainTab === 'stocks' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap">
            <i class="fa-solid fa-boxes-stacked text-xs" :class="activeMainTab === 'stocks' ? 'text-emerald-200' : 'text-emerald-600'"></i>
            <span>Tồn kho hàng ôm theo nhân viên</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeMainTab === 'stocks' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800'">
                {{ count($employeeWarehouses ?? $vehicleWarehouses) }}
            </span>
        </button>

        <button type="button" @click="setMainTab('dispatches')" 
                :class="activeMainTab === 'dispatches' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap">
            <i class="fa-solid fa-dolly text-xs" :class="activeMainTab === 'dispatches' ? 'text-amber-200' : 'text-amber-600'"></i>
            <span>Lịch sử xuất hàng ôm (OM & PXK)</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeMainTab === 'dispatches' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">
                {{ $totalVanDispatchesCount ?? 0 }}
            </span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: TỒN KHO HÀNG ÔM CỦA NHÂN VIÊN                     -->
    <!-- ======================================================== -->
    <div x-show="activeMainTab === 'stocks'" x-cloak class="space-y-4">
        @if(($employeeWarehouses ?? $vehicleWarehouses)->isEmpty())
        <div class="bg-white rounded-2xl p-10 border border-slate-200 shadow-2xs text-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#1b4a8c] flex items-center justify-center mx-auto text-2xl border border-blue-100">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <h3 class="font-black text-base text-slate-800">Chưa có nhân viên nào đang giữ hàng ôm</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Hệ thống chỉ tự động hiển thị những kho nhân viên đang thực tế nắm giữ hàng hóa. Hiện tại tất cả nhân viên kinh doanh đều có số lượng tồn kho ôm bằng 0.
            </p>
            <div class="pt-2">
                <button type="button" @click="openDispatchModal()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs inline-flex items-center gap-2 shadow-xs cursor-pointer transition-colors">
                    <i class="fa-solid fa-plus"></i>
                    <span>+ Lập phiếu xuất hàng ôm cho nhân viên</span>
                </button>
            </div>
        </div>
        @else
        <!-- Thẻ thống kê các kho hàng ôm nhân viên có dữ liệu -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5">
            @foreach($employeeWarehouses ?? $vehicleWarehouses as $wh)
            <div @click="selectedVehicleWhId = {{ $wh->id }}"
                 :class="selectedVehicleWhId === {{ $wh->id }} ? 'ring-2 ring-[#1b4a8c] bg-blue-50/40 border-blue-400' : 'bg-white hover:border-slate-300'"
                 class="rounded-2xl p-4 border border-slate-200 shadow-2xs transition-all cursor-pointer space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-lg bg-blue-100 text-[#1b4a8c] font-bold text-[10.5px] border border-blue-200">
                        <i class="fa-solid fa-user-tag mr-1"></i>Hàng Ôm NVKD
                    </span>
                    <span class="font-mono text-xs font-black text-slate-900">{{ $wh->employee_code ?? ('KHO-OM-' . $wh->id) }}</span>
                </div>

                <div>
                    <div class="font-black text-slate-900 text-sm truncate">{{ $wh->employee_name ?? $wh->name }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center justify-between">
                        <span>{{ $wh->department ?? 'Kinh doanh' }}</span>
                        <span class="font-mono text-slate-700">{{ $wh->phone ?? '---' }}</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase font-bold">Số Mặt Hàng</div>
                        <div class="font-mono font-bold text-slate-800">{{ $wh->total_skus }} SP ({{ number_format($wh->total_qty) }})</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] text-slate-400 uppercase font-bold">Giá Trị Hàng Ôm</div>
                        <div class="font-mono font-bold text-emerald-700">{{ number_format($wh->total_val) }} đ</div>
                    </div>
                </div>

                @if(!empty($wh->debt) && (float)$wh->debt > 0)
                <div class="px-2.5 py-1 rounded-xl bg-amber-50/90 border border-amber-200/80 flex items-center justify-between text-xs">
                    <span class="text-[10.5px] font-bold text-amber-800 flex items-center gap-1">
                        <i class="fa-solid fa-file-invoice-dollar text-amber-600"></i> Công nợ NV:
                    </span>
                    <span class="font-mono font-black text-amber-900">{{ number_format($wh->debt) }} đ</span>
                </div>
                @endif

                @if(!empty($wh->active_orders) && count($wh->active_orders) > 0)
                <div class="pt-1 border-t border-slate-100 space-y-1">
                    <div class="text-[10px] text-slate-400 font-bold uppercase flex items-center justify-between">
                        <span>Đơn ôm đang giữ ({{ count($wh->active_orders) }}):</span>
                        <a href="{{ url('/orders?tab=consignment') }}" class="text-[#1b4a8c] hover:underline lowercase font-semibold">chi tiết →</a>
                    </div>
                    <div class="flex flex-wrap gap-1">
                        @foreach($wh->active_orders as $ord)
                        <a href="{{ url('/orders?tab=consignment') }}" @click.stop class="px-1.5 py-0.5 rounded bg-blue-100 hover:bg-blue-200 text-[#1b4a8c] font-mono text-[10px] font-bold border border-blue-200" title="Tổng: {{ number_format($ord->total_amount) }}đ - Còn lại: {{ number_format($ord->remaining_amount) }}đ">
                            {{ $ord->code }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="pt-1.5 grid grid-cols-2 gap-1.5">
                    <a href="{{ route('inventory.holding_sale.create', ['warehouse_id' => $wh->id]) }}" @click.stop
                       class="py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[11px] font-bold transition-colors flex items-center justify-center gap-1 shadow-xs cursor-pointer text-center no-underline" title="Chuyển đến trang tạo đơn bán ngay từ kho hàng ôm của nhân viên này">
                        <i class="fa-solid fa-cart-shopping text-[10px]"></i>
                        <span>Bán hàng</span>
                    </a>
                    <button type="button" @click.stop="openDispatchModalWithEmployee('{{ $wh->employee_id ?? $wh->id }}', '{{ $wh->employee_name ?? $wh->name }}')"
                            class="py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl text-[11px] font-bold border border-emerald-200 transition-colors flex items-center justify-center gap-1 cursor-pointer" title="Xuất thêm hàng từ kho công ty vào kho ôm của NV này">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Xuất ôm</span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Bảng chi tiết tồn kho hàng ôm của nhân viên đang chọn -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-[#1b4a8c]"></i>
                        <span>Chi Tiết Tồn Kho Hàng Ôm Của: <span class="text-blue-700 font-mono" x-text="getSelectedVehicleWh()?.employee_name || getSelectedVehicleWh()?.name || 'Vui lòng chọn nhân viên'"></span></span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Dữ liệu thực tế hàng hóa nhân viên đang nắm giữ để đi bán lẻ hoặc chào mẫu ngoài thị trường</p>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="getSelectedVehicleWh()">
                        <div class="flex items-center gap-2">
                            <a :href="'/inventory/holding-sale/create?warehouse_id=' + (getSelectedVehicleWh()?.id || '')"
                               class="px-3.5 py-1.5 bg-[#1b4a8c] hover:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-1.5 no-underline">
                                <i class="fa-solid fa-cart-shopping text-xs"></i>
                                <span>+ Bán Hàng Từ Kho Này</span>
                            </a>
                            <button type="button" @click="openDispatchModalWithEmployee(getSelectedVehicleWh()?.employee_id || getSelectedVehicleWh()?.id, getSelectedVehicleWh()?.employee_name || getSelectedVehicleWh()?.name)"
                                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid fa-plus text-xs"></i>
                                <span>+ Bốc Thêm Hàng Ôm</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-12">STT</th>
                            <th class="py-3.5 px-3">Mã Hàng (SKU)</th>
                            <th class="py-3.5 px-3">Tên Sản Phẩm</th>
                            <th class="py-3.5 px-3 text-center">ĐVT</th>
                            <th class="py-3.5 px-3 text-center">Tồn Đang Ôm</th>
                            <th class="py-3.5 px-3 text-right">Đơn Giá Bán</th>
                            <th class="py-3.5 px-3 text-right">Thành Tiền (Giá Bán)</th>
                            <th class="py-3.5 px-3 text-center">Vị Trí Lưu</th>
                            <th class="py-3.5 px-4 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <template x-for="(st, sIdx) in (getSelectedVehicleWh()?.stocks || [])" :key="st.id || sIdx">
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 text-center font-mono text-slate-400" x-text="sIdx + 1"></td>
                                <td class="py-3 px-3 font-mono font-bold text-slate-900" x-text="st.sku || st.product?.sku || '---'"></td>
                                <td class="py-3 px-3 font-semibold text-slate-800" x-text="st.name || st.product?.name || '---'"></td>
                                <td class="py-3 px-3 text-center" x-text="st.unit || st.product?.unit || 'cái'"></td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-mono font-bold text-xs" x-text="st.stock_quantity"></span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-slate-600" x-text="formatMoney(st.product?.price || st.cost_price || st.product?.cost_price || 0) + ' đ'"></td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-700" x-text="formatMoney((st.stock_quantity || 0) * (st.product?.price || st.cost_price || st.product?.cost_price || 0)) + ' đ'"></td>
                                <td class="py-3 px-3 text-center text-slate-500 italic text-[11px]" x-text="st.location || 'Hàng ôm NV'"></td>
                                <td class="py-3 px-4 text-center">
                                    <a :href="'/inventory/holding-sale/create?warehouse_id=' + (getSelectedVehicleWh()?.id || '') + '&product_id=' + (st.product_id || st.product?.id || '')"
                                       class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-[#1b4a8c] rounded-lg text-xs font-bold border border-blue-200 transition-colors cursor-pointer inline-flex items-center gap-1 no-underline">
                                        <i class="fa-solid fa-cart-shopping text-[10px]"></i>
                                        <span>Bán SP này</span>
                                    </a>
                                </td>
                            </tr>
                        </template>
                        <template x-if="!getSelectedVehicleWh() || (getSelectedVehicleWh()?.stocks || []).length === 0">
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-boxes-packing text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-xs font-semibold">Nhân viên này hiện chưa ôm mặt hàng nào trong kho.</p>
                                    <button type="button" @click="openDispatchModalWithEmployee(getSelectedVehicleWh()?.employee_id || getSelectedVehicleWh()?.id, getSelectedVehicleWh()?.employee_name || getSelectedVehicleWh()?.name)"
                                            class="mt-2 text-emerald-600 font-bold hover:underline cursor-pointer">
                                        + Bấm vào đây để xuất thêm hàng ôm cho nhân viên này
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: LỊCH SỬ XUẤT HÀNG ÔM CHO NHÂN VIÊN                 -->
    <!-- ======================================================== -->
    <div x-show="activeMainTab === 'dispatches'" x-cloak class="space-y-4">
        <!-- Metrics Xuất hàng ôm -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 rounded-2xl p-4 border border-amber-200 shadow-2xs space-y-1">
                <div class="flex items-center justify-between text-amber-800 font-bold text-xs">
                    <span>Đơn Hàng Ôm & Ký Gửi (OM)</span>
                    <i class="fa-solid fa-file-invoice-dollar text-amber-600 text-sm"></i>
                </div>
                <div class="text-2xl font-black text-amber-950 font-mono">
                    {{ $totalConsignmentOrdersCount ?? 0 }} <span class="text-sm font-bold text-amber-800">đơn</span>
                </div>
                <div class="text-[11px] text-amber-700 font-medium">Đang giữ: <strong class="text-amber-900">{{ $totalConsignmentOrdersActive ?? 0 }}</strong> đơn đang lưu hành ngoài thị trường</div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 rounded-2xl p-4 border border-blue-200 shadow-2xs space-y-1">
                <div class="flex items-center justify-between text-blue-800 font-bold text-xs">
                    <span>Tổng Giá Trị Hàng Ôm Đã Giao</span>
                    <i class="fa-solid fa-sack-dollar text-blue-600 text-sm"></i>
                </div>
                <div class="text-2xl font-black text-blue-950 font-mono">
                    {{ number_format($totalConsignmentOrdersAmount ?? $totalVanDispatchesAmount) }} <span class="text-sm font-bold text-blue-800">đ</span>
                </div>
                <div class="text-[11px] text-blue-700 font-medium">Tự động cộng dồn vào công nợ cá nhân của từng NVKD</div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-pink-50/50 rounded-2xl p-4 border border-purple-200 shadow-2xs space-y-1">
                <div class="flex items-center justify-between text-purple-800 font-bold text-xs">
                    <span>Liên Kết Đồng Bộ Tồn Kho Ôm</span>
                    <i class="fa-solid fa-arrows-rotate text-purple-600 text-sm"></i>
                </div>
                <div class="text-sm font-bold text-purple-950 mt-1 leading-snug">
                    Đồng bộ 2 chiều: <span class="font-mono bg-purple-200 px-1 py-0.5 rounded text-xs">consignment_orders</span> ↔ <span class="font-mono bg-purple-200 px-1 py-0.5 rounded text-xs">warehouse_stocks</span>
                </div>
                <div class="text-[11px] text-purple-700">Tự động tăng tồn kho KHO-OM khi duyệt đơn và trừ tồn khi bán lẻ</div>
            </div>
        </div>

<div class="flex items-center justify-between gap-3 flex-wrap bg-white p-3 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="flex items-center gap-2">
                <button type="button" @click="dispatchSubTab = 'consignment'"
                        :class="dispatchSubTab === 'consignment' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-amber-300"></i>
                    <span>1. Đơn Hàng Ôm & Ký Gửi (Mã OM)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="dispatchSubTab === 'consignment' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">
                        {{ count($consignmentOrders ?? []) }}
                    </span>
                </button>

                <button type="button" @click="dispatchSubTab = 'direct'"
                        :class="dispatchSubTab === 'direct' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-300"></i>
                    <span>2. Phiếu Bàn Giao Hàng Ôm Trực Tiếp (PXK-OM)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="dispatchSubTab === 'direct' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'">
                        {{ count($vanDispatches) }}
                    </span>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ url('/orders?tab=consignment') }}" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-600"></i>
                    <span>Mở Module Đơn Hàng Ôm</span>
                </a>
                <button type="button" @click="openDispatchModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>+ Lập Phiếu Xuất Hàng Ôm</span>
                </button>
            </div>
        </div>

        <!-- Bảng 1: Đơn Hàng Ôm & Ký Gửi (OM-xxxx) -->
        <div x-show="dispatchSubTab === 'consignment'" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="font-black text-slate-800 text-sm">Danh Sách Đơn Hàng Ôm & Ký Gửi Của NVKD</span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-xs">{{ count($consignmentOrders ?? []) }} đơn</span>
                </div>
                <div class="text-xs text-slate-500">
                    Dữ liệu liên kết trực tiếp với module <strong>Đơn hàng ôm & Ký gửi</strong>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Mã Đơn & Ngày Lập</th>
                            <th class="py-3.5 px-3">Nhân Viên Nhận & Kho Xuất</th>
                            <th class="py-3.5 px-3">Chi Tiết Mặt Hàng</th>
                            <th class="py-3.5 px-3 text-right">Tổng Tiền Đơn</th>
                            <th class="py-3.5 px-3 text-right">Còn Nợ / Đang Ôm</th>
                            <th class="py-3.5 px-3 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-4 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($consignmentOrders ?? [] as $cOrd)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-black text-[#1b4a8c] text-xs flex items-center gap-1.5">
                                    <span>{{ $cOrd->code }}</span>
                                    @if(in_array($cOrd->status, ['in_progress', 'approved']))
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    <i class="fa-regular fa-calendar text-[10px] mr-1"></i>{{ $cOrd->created_at ? $cOrd->created_at->format('d/m/Y H:i') : '---' }}
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-xs font-bold shrink-0">
                                        <i class="fa-solid fa-user-tag"></i>
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $cOrd->staff_name }}</div>
                                        <div class="text-[11px] text-slate-500">Từ: {{ $cOrd->warehouse_name ?: 'Kho Tổng Mê Linh' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="font-bold text-slate-800">
                                    {{ $cOrd->items->count() }} SP ({{ number_format($cOrd->items->sum('quantity')) }} ĐVT)
                                </div>
                                <div class="text-[11px] text-slate-500 truncate max-w-xs mt-0.5" title="{{ $cOrd->items->pluck('name')->join(', ') }}">
                                    {{ $cOrd->items->pluck('name')->take(2)->join(', ') }}@if($cOrd->items->count() > 2)...@endif
                                </div>
                            </td>

                            <td class="py-3.5 px-3 text-right">
                                <div class="font-mono font-bold text-slate-900 text-xs">{{ number_format($cOrd->total_amount) }} đ</div>
                            </td>

                            <td class="py-3.5 px-3 text-right">
                                <div class="font-mono font-black text-rose-600 text-xs">{{ number_format($cOrd->remaining_amount) }} đ</div>
                                @if((float)$cOrd->sold_amount > 0)
                                <div class="text-[10.5px] font-mono text-emerald-700">Đã bán: {{ number_format($cOrd->sold_amount) }} đ</div>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                @if($cOrd->status === 'in_progress')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        <i class="fa-solid fa-spinner fa-spin-pulse text-[9px] mr-1"></i>Đang bán
                                    </span>
                                @elseif($cOrd->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-sky-100 text-sky-800 border border-sky-200">
                                        <i class="fa-solid fa-check text-[9px] mr-1"></i>Đã duyệt
                                    </span>
                                @elseif($cOrd->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-clock text-[9px] mr-1"></i>Chờ duyệt
                                    </span>
                                @elseif($cOrd->status === 'settled')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-check-double text-[9px] mr-1"></i>Đã quyết toán
                                    </span>
                                @elseif($cOrd->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                        Đã hủy
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-slate-100 text-slate-800">
                                        {{ $cOrd->status_label ?? $cOrd->status }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ url('/orders?tab=consignment') }}"
                                       class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-[#1b4a8c] rounded-xl text-xs font-bold border border-blue-200 transition-colors inline-flex items-center gap-1 cursor-pointer" title="Xem chi tiết đơn và quản lý bán lẻ">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                        <span>Xem đơn</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-file-invoice-dollar text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold">Chưa có đơn hàng ôm & ký gửi nào.</p>
                                <a href="{{ url('/orders?tab=consignment') }}" class="mt-2 text-amber-600 font-bold hover:underline inline-block">
                                    + Bấm vào đây để tạo đơn ôm hàng mới
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bảng 2: Phiếu Bàn Giao Trực Tiếp (PXK-OM) -->
        <div x-show="dispatchSubTab === 'direct'" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="font-black text-slate-800 text-sm">Danh Sách Phiếu Bàn Giao Hàng Ôm Trực Tiếp (PXK-OM)</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">{{ count($vanDispatches) }} phiếu gần nhất</span>
                </div>
                <button type="button" @click="openDispatchModal()" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>+ Lập Phiếu Xuất Hàng Ôm Cho NV</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Mã Phiếu & Ngày Xuất</th>
                            <th class="py-3.5 px-3">Nhân Viên Nhận & Kho Xuất</th>
                            <th class="py-3.5 px-3">Bộ Phận & Liên Hệ</th>
                            <th class="py-3.5 px-3 text-center">Số Mặt Hàng</th>
                            <th class="py-3.5 px-3 text-right">Tổng Tiền Hàng Ôm</th>
                            <th class="py-3.5 px-3 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-4 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($vanDispatches as $disp)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-black text-emerald-700 text-xs">{{ $disp->code }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    <i class="fa-regular fa-calendar text-[10px] mr-1"></i>{{ $disp->dispatch_date ? $disp->dispatch_date->format('d/m/Y') : $disp->created_at->format('d/m/Y') }}
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold shrink-0">
                                        <i class="fa-solid fa-boxes-packing"></i>
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $disp->driver_name }}</div>
                                        <div class="text-[11px] text-slate-500">Từ: {{ $disp->warehouse_name ?: 'Kho Tổng Mê Linh' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-3">
                                <div class="font-bold text-slate-900">{{ $disp->driver?->department ?: 'Kinh doanh' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $disp->driver?->phone ?: ($disp->vehicle_plate ?: '---') }}</div>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-mono font-black text-xs border border-emerald-200">
                                    {{ $disp->items->count() }} SP ({{ number_format($disp->items->sum('quantity')) }})
                                </span>
                            </td>

                            <td class="py-3.5 px-3 text-right">
                                <div class="font-mono font-black text-rose-600 text-xs">{{ number_format($disp->total_amount) }} đ</div>
                                <div class="text-[10px] text-slate-400">trách nhiệm hàng ôm</div>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-check text-[9px] mr-1"></i>Đã giao hàng ôm
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openDispatchDetailModal(@js($disp))"
                                            class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer" title="Xem chi tiết các mặt hàng">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                    <button type="button" @click="printDispatchSlip(@js($disp))"
                                            class="p-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-xl border border-purple-200 transition-colors cursor-pointer" title="In phiếu xuất kho">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-boxes-packing text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold">Chưa có phiếu xuất hàng ôm nào cho nhân viên.</p>
                                <button type="button" @click="openDispatchModal()" class="mt-2 text-emerald-600 font-bold hover:underline cursor-pointer">
                                    + Bấm vào đây để lập phiếu bàn giao hàng ôm cho nhân viên
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: LẬP PHIẾU XUẤT HÀNG ÔM CHO NHÂN VIÊN (PXK-OM)      -->
    <!-- ======================================================== -->
    <div x-show="showDispatchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="closeDispatchModal()" class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full border border-slate-200 overflow-hidden flex flex-col max-h-[92vh]">
            <!-- Header Modal -->
            <div class="p-4 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </span>
                    <div>
                        <h3 class="font-black text-base">Lập Phiếu Xuất Hàng Ôm Cho Nhân Viên (PXK-OM)</h3>
                        <p class="text-xs text-emerald-100">Xuất hàng từ kho công ty giao vào kho ôm của nhân viên kinh doanh / thị trường để đi bán hoặc chào mẫu</p>
                    </div>
                </div>
                <button type="button" @click="closeDispatchModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-5 overflow-y-auto space-y-4 text-xs">
                <!-- Chọn Kho & Chọn Nhân Viên -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kho Xuất Nguồn (*):</label>
                        <select x-model="vanSaleForm.warehouse_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-emerald-600">
                            @foreach($physicalWarehouses as $pwh)
                                <option value="{{ $pwh->id }}">{{ $pwh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nhân Viên Nhận Hàng Ôm (*):</label>
                        <select x-model="vanSaleForm.driver_id" @change="onEmployeeSelected($event.target.value)" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-emerald-600">
                            <option value="">-- Chọn nhân viên kinh doanh --</option>
                            @foreach($salesStaff as $st)
                                <option value="{{ $st->id }}" data-name="{{ $st->name }}" data-phone="{{ $st->phone }}" data-dept="{{ $st->department }}">
                                    {{ $st->name }} ({{ $st->code ?: 'NV-'.$st->id }} - {{ $st->department ?: 'Kinh doanh' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phương Tiện / Biển Số Xe (Tùy chọn):</label>
                        <input type="text" x-model="vanSaleForm.vehicle_plate" placeholder="VD: 29C-123.45 hoặc Xe cá nhân..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800 focus:outline-none focus:border-emerald-600">
                    </div>
                </div>

                <!-- Bảng Mặt Hàng -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-black text-slate-800 text-xs">Danh Sách Mặt Hàng Bốc Lên Xe:</label>
                        <button type="button" @click="addVanItem()" class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg border border-emerald-200 cursor-pointer flex items-center gap-1">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Thêm dòng</span>
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-visible">
                        <table class="w-full text-left text-xs divide-y divide-slate-200">
                            <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] uppercase">
                                <tr>
                                    <th class="py-2.5 px-3 w-8 text-center">STT</th>
                                    <th class="py-2.5 px-3 min-w-[280px]">Sản Phẩm (Tìm kiếm chọn nhanh)</th>
                                    <th class="py-2.5 px-2 w-24 text-center">Số Lượng</th>
                                    <th class="py-2.5 px-2 w-32 text-right">Đơn Giá (đ)</th>
                                    <th class="py-2.5 px-3 w-32 text-right">Thành Tiền</th>
                                    <th class="py-2.5 px-2 w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(it, idx) in vanSaleForm.items" :key="it.id || idx">
                                    <tr class="align-middle">
                                        <td class="py-2.5 px-3 text-center font-mono text-slate-400" x-text="idx + 1"></td>
                                        <td class="py-2 px-3 relative min-w-[280px]">
                                            <div class="relative" @click.outside="if (activeProdDropdownIndex === idx) activeProdDropdownIndex = null">
                                                <input type="text"
                                                       x-model="it.searchQuery"
                                                       @focus="activeProdDropdownIndex = idx; prodHighlightIndex = 0"
                                                       @input="activeProdDropdownIndex = idx; prodHighlightIndex = 0; if(!it.searchQuery) { it.product_id = ''; it.unit_price = 0; }"
                                                       @keydown.down.prevent="
                                                           const prods = getFilteredProducts(it.searchQuery, idx);
                                                           if (prods.length > 0) {
                                                               prodHighlightIndex = (prodHighlightIndex + 1) % prods.length;
                                                           }
                                                       "
                                                       @keydown.up.prevent="
                                                           const prods = getFilteredProducts(it.searchQuery, idx);
                                                           if (prods.length > 0) {
                                                               prodHighlightIndex = (prodHighlightIndex - 1 + prods.length) % prods.length;
                                                           }
                                                       "
                                                       @keydown.enter.prevent="
                                                           if (activeProdDropdownIndex === idx) {
                                                               const prods = getFilteredProducts(it.searchQuery, idx);
                                                               if (prods.length > 0 && prods[prodHighlightIndex]) {
                                                                   selectProductForRow(idx, prods[prodHighlightIndex]);
                                                               }
                                                           }
                                                       "
                                                       @keydown.escape.prevent="activeProdDropdownIndex = null"
                                                       placeholder="Tìm mã hoặc tên sản phẩm (phím ↑ ↓ Enter)..."
                                                       class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:border-emerald-600 focus:outline-none shadow-2xs hover:border-slate-400 transition-colors">

                                                <!-- Menu Dropdown gợi ý sản phẩm -->
                                                <div x-show="activeProdDropdownIndex === idx && getFilteredProducts(it.searchQuery, idx).length > 0"
                                                     x-cloak
                                                     class="absolute left-0 top-full mt-1 w-[460px] max-w-[90vw] z-50 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-60 overflow-y-auto py-1 text-xs">
                                                    <div class="px-3 py-1 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                                        <span>Gợi ý sản phẩm (<span x-text="getFilteredProducts(it.searchQuery, idx).length"></span>)</span>
                                                        <span class="text-emerald-700 font-normal lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                                    </div>
                                                    <template x-for="(p, pIdx) in getFilteredProducts(it.searchQuery, idx)" :key="p.id">
                                                        <div @mousedown.prevent="selectProductForRow(idx, p)"
                                                             @mouseenter="prodHighlightIndex = pIdx"
                                                             class="px-3 py-2 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-50 last:border-0"
                                                             :class="prodHighlightIndex === pIdx ? 'bg-emerald-50 text-emerald-950 font-bold ring-1 ring-emerald-300' : 'hover:bg-slate-50'">
                                                            <div class="min-w-0 pr-3">
                                                                <div class="font-bold text-slate-900 truncate">
                                                                    <span class="text-emerald-700 font-mono mr-1" x-text="'[' + (p.sku || 'N/A') + ']'"></span>
                                                                    <span x-text="p.name"></span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                                    <span class="font-semibold text-slate-700" x-text="'ĐVT: ' + (p.unit || 'cái')"></span>
                                                                    <span>&bull;</span>
                                                                    <span :class="(p.stock_quantity || 0) > 0 ? 'text-emerald-600 font-semibold' : 'text-rose-500'" 
                                                                          x-text="'Tồn [Kho Tổng]: ' + formatMoney(p.stock_quantity || 0) + ' ' + (p.unit || 'cái')"></span>
                                                                </div>
                                                            </div>
                                                            <div class="shrink-0 text-right">
                                                                <div class="font-black text-emerald-700 text-xs" x-text="formatMoney(p.retail_price || p.cost_price || 0) + ' đ'"></div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <!-- Hiển thị badge tồn kho bên dưới khi đã chọn -->
                                            <template x-if="it.product_id">
                                                <div class="mt-1 flex items-center gap-1.5 text-[10px] text-emerald-700 font-bold">
                                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                                    <span x-text="'Đã chọn | Tồn kho tổng: ' + formatMoney(it.stock_quantity || 0) + ' ' + (it.unit || 'cái')"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="py-2 px-2 text-center">
                                            <input :id="'van-item-qty-' + idx" type="number" min="0.1" step="any" x-model.number="it.quantity" class="w-full px-2 py-1.5 text-center font-bold font-mono border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-600">
                                        </td>
                                        <td class="py-2 px-2 text-right">
                                            <input type="number" min="0" x-model.number="it.unit_price" class="w-full px-2 py-1.5 text-right font-bold font-mono border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-600">
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700" x-text="formatMoney((it.quantity || 0) * (it.unit_price || 0)) + ' đ'"></td>
                                        <td class="py-2.5 px-2 text-center">
                                            <button type="button" @click="removeVanItem(idx)" class="text-slate-400 hover:text-rose-600 p-1 cursor-pointer">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tổng tiền & Ghi chú -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ghi Chú Xuất Hàng Ôm:</label>
                        <textarea x-model="vanSaleForm.notes" rows="2" placeholder="VD: Giao hàng ôm mẫu / hàng bán thị trường..." class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-600"></textarea>
                    </div>

                    <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-200 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-900">TỔNG GIÁ TRỊ HÀNG ÔM XUẤT CHO NHÂN VIÊN:</span>
                            <span class="font-mono font-black text-emerald-900 text-lg" x-text="formatMoney(getVanSaleTotal()) + ' đ'"></span>
                        </div>
                        <p class="text-[11px] text-emerald-700 mt-1 leading-snug">
                            ⚠️ <strong>Quy tắc hàng ôm:</strong> Hàng xuất sẽ được chuyển vào <strong>Kho Hàng Ôm</strong> của nhân viên. Nhân viên có thể tạo đơn <strong>Bán hàng trực tiếp</strong> (trừ ngay kho ôm, không qua bốc hàng kho tổng) hoặc lập phiếu hoàn lại kho công ty.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs shrink-0">
                <button type="button" @click="closeDispatchModal()" class="px-4 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">
                    Hủy Bỏ
                </button>
                <button type="button" @click="submitVanSale()" :disabled="isSubmittingVanSale" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5 disabled:opacity-50">
                    <i class="fa-solid fa-check"></i>
                    <span x-text="isSubmittingVanSale ? 'Đang lưu...' : 'Xác Nhận & Xuất Giao Hàng Ôm'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: XEM CHI TIẾT PHIẾU XUẤT HÀNG ÔM CHO NHÂN VIÊN     -->
    <!-- ======================================================== -->
    <div x-show="showDispatchDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="showDispatchDetailModal = false" class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-4 bg-emerald-700 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing text-lg"></i>
                    <h3 class="font-bold text-sm">Chi Tiết Phiếu Xuất Hàng Ôm <span class="font-mono font-black" x-text="activeDispatch?.code"></span></h3>
                </div>
                <button type="button" @click="showDispatchDetailModal = false" class="text-white hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="p-4 overflow-y-auto space-y-4 text-xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">NHÂN VIÊN NHẬN ÔM</span>
                        <span class="font-bold text-slate-900" x-text="activeDispatch?.driver_name || '---'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">PHƯƠNG TIỆN / BIỂN SỐ</span>
                        <span class="font-mono font-bold text-slate-900" x-text="activeDispatch?.vehicle_plate || '---'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">KHO XUẤT NGUỒN</span>
                        <span class="font-bold text-slate-900" x-text="activeDispatch?.warehouse_name || 'Kho Tổng Mê Linh'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold">TỔNG GIÁ TRỊ HÀNG ÔM</span>
                        <span class="font-mono font-bold text-emerald-700" x-text="formatMoney(activeDispatch?.total_amount) + ' đ'"></span>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] uppercase">
                            <tr>
                                <th class="py-2.5 px-3">STT</th>
                                <th class="py-2.5 px-3">Mã SKU & Sản Phẩm</th>
                                <th class="py-2.5 px-3 text-center">ĐVT</th>
                                <th class="py-2.5 px-3 text-center">Số Lượng</th>
                                <th class="py-2.5 px-3 text-right">Đơn Giá</th>
                                <th class="py-2.5 px-3 text-right">Thành Tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(it, iIdx) in (activeDispatch?.items || [])" :key="it.id || iIdx">
                                <tr>
                                    <td class="p-2.5 text-center font-mono text-slate-400" x-text="iIdx + 1"></td>
                                    <td class="p-2.5">
                                        <div class="font-bold text-slate-800" x-text="it.name"></div>
                                        <div class="font-mono text-[10px] text-slate-400" x-text="it.sku"></div>
                                    </td>
                                    <td class="p-2.5 text-center" x-text="it.unit"></td>
                                    <td class="p-2.5 text-center font-mono font-bold text-slate-800" x-text="it.quantity"></td>
                                    <td class="p-2.5 text-right font-mono text-slate-600" x-text="formatMoney(it.unit_price) + ' đ'"></td>
                                    <td class="p-2.5 text-right font-mono font-bold text-emerald-700" x-text="formatMoney(it.amount || ((it.quantity || 0) * (it.unit_price || 0))) + ' đ'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs shrink-0">
                <button type="button" @click="printDispatchSlip(activeDispatch)" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-xl border border-purple-200 cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>In Phiếu Xuất Kho (PXK)</span>
                </button>
                <button type="button" @click="showDispatchDetailModal = false" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function employeeHoldingApp() {
    return {
        activeMainTab: '{{ $activeTab ?? "stocks" }}', // 'stocks', 'dispatches'
        dispatchSubTab: 'consignment', // 'consignment' (Đơn hàng ôm OM), 'direct' (PXK-OM)
        selectedVehicleWhId: {{ $employeeWarehouses->first()?->id ?? ($vehicleWarehouses->first()?->id ?? 'null') }},
        vehicleWarehousesData: @json($employeeWarehouses ?? ($vehicleWarehouses ?? [])),
        availableProducts: @json($productsCatalog ?? []),
        dealersList: @json($dealers ?? []),
        salesStaffList: @json($salesStaff ?? []),

        // Modal Xuất Hàng Ôm Nhân Viên
        showDispatchModal: false,
        isSubmittingVanSale: false,
        activeProdDropdownIndex: null,
        prodHighlightIndex: 0,
        vanSaleForm: {
            warehouse_id: 1,
            vehicle_plate: '',
            driver_id: '',
            notes: 'Xuất hàng ôm cho nhân viên',
            items: [
                { id: 1, product_id: '', searchQuery: '', sku: '', unit: '', stock_quantity: 0, quantity: 1, unit_price: 0 }
            ]
        },

        // Modal Chi tiết phiếu xuất lẻ
        showDispatchDetailModal: false,
        activeDispatch: null,

        setMainTab(tab) {
            this.activeMainTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },

        getSelectedVehicleWh() {
            return this.vehicleWarehousesData.find(w => w.id === this.selectedVehicleWhId) || this.vehicleWarehousesData[0] || null;
        },

        formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0));
        },

        formatDate(dateStr) {
            if (!dateStr) return '---';
            const d = new Date(dateStr);
            return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('vi-VN');
        },

        // --- Logic Xuất Hàng Ôm Cho Nhân Viên ---
        openDispatchModal() {
            this.showDispatchModal = true;
            this.activeProdDropdownIndex = null;
            this.prodHighlightIndex = 0;
            this.vanSaleForm.warehouse_id = '{{ $physicalWarehouses->first()?->id ?? 1 }}';
            this.vanSaleForm.vehicle_plate = '';
            this.vanSaleForm.driver_id = '';
            this.vanSaleForm.notes = 'Xuất hàng ôm cho nhân viên';
            this.vanSaleForm.items = [
                { id: Date.now(), product_id: '', searchQuery: '', sku: '', unit: '', stock_quantity: 0, quantity: 1, unit_price: 0 }
            ];
        },

        openDispatchModalWithEmployee(empId, empName) {
            this.openDispatchModal();
            this.vanSaleForm.driver_id = empId || '';
            this.vanSaleForm.vehicle_plate = empName ? ('NV: ' + empName) : '';
            this.vanSaleForm.notes = 'Xuất hàng ôm cho ' + (empName || 'nhân viên');
        },

        closeDispatchModal() {
            this.showDispatchModal = false;
            this.activeProdDropdownIndex = null;
        },

        onEmployeeSelected(empId) {
            this.vanSaleForm.driver_id = empId;
            const staff = (this.salesStaffList || []).find(s => String(s.id) === String(empId));
            if (staff && (!this.vanSaleForm.vehicle_plate || this.vanSaleForm.vehicle_plate.startsWith('NV:'))) {
                this.vanSaleForm.vehicle_plate = 'NV: ' + staff.name;
            }
        },

        addVanItem() {
            this.vanSaleForm.items.push({
                id: Date.now() + Math.floor(Math.random() * 1000),
                product_id: '',
                searchQuery: '',
                sku: '',
                unit: '',
                stock_quantity: 0,
                quantity: 1,
                unit_price: 0
            });
        },

        removeVanItem(idx) {
            if (this.vanSaleForm.items.length > 1) {
                this.vanSaleForm.items.splice(idx, 1);
            }
        },

        getFilteredProducts(query, currentIndex = -1) {
            const list = this.availableProducts || [];
            if (!query || !query.trim()) {
                return list.slice(0, 30);
            }
            const q = query.toLowerCase().trim();
            return list.filter(p => {
                const sku = (p.sku || '').toLowerCase();
                const name = (p.name || '').toLowerCase();
                return sku.includes(q) || name.includes(q);
            }).slice(0, 30);
        },

        selectProductForRow(idx, p) {
            const item = this.vanSaleForm.items[idx];
            if (!item || !p) return;
            item.product_id = p.id;
            item.sku = p.sku || '';
            item.unit = p.unit || 'cái';
            item.stock_quantity = p.stock_quantity || 0;
            item.searchQuery = '[' + (p.sku || 'N/A') + '] ' + p.name;
            const price = Number(p.retail_price || 0) > 0 ? Number(p.retail_price) : Number(p.cost_price || 0);
            item.unit_price = price;
            this.activeProdDropdownIndex = null;
            this.$nextTick(() => {
                const qtyEl = document.getElementById('van-item-qty-' + idx);
                if (qtyEl) {
                    qtyEl.focus();
                    qtyEl.select();
                }
            });
        },

        getVanSaleTotal() {
            return this.vanSaleForm.items.reduce((sum, it) => sum + (Number(it.quantity || 0) * Number(it.unit_price || 0)), 0);
        },

        async submitVanSale() {
            if (!this.vanSaleForm.driver_id) {
                alert('Vui lòng chọn nhân viên nhận hàng ôm!');
                return;
            }
            if (!this.vanSaleForm.vehicle_plate) {
                const staff = (this.salesStaffList || []).find(s => String(s.id) === String(this.vanSaleForm.driver_id));
                this.vanSaleForm.vehicle_plate = 'NV: ' + (staff?.name || 'Nhân viên');
            }

            const validItems = this.vanSaleForm.items
                .filter(i => i.product_id && Number(i.quantity) > 0)
                .map(i => ({
                    product_id: parseInt(i.product_id, 10) || i.product_id,
                    quantity: Number(i.quantity),
                    unit_price: Number(i.unit_price || 0)
                }));

            if (validItems.length === 0) {
                alert('Vui lòng chọn sản phẩm và nhập số lượng hợp lệ lớn hơn 0!');
                return;
            }

            this.isSubmittingVanSale = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/van-sale-dispatches', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        warehouse_id: this.vanSaleForm.warehouse_id,
                        vehicle_plate: this.vanSaleForm.vehicle_plate,
                        driver_id: this.vanSaleForm.driver_id,
                        notes: this.vanSaleForm.notes,
                        items: validItems
                    })
                });

                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã xuất hàng ôm cho nhân viên thành công!');
                    window.location.href = '/inventory/holding?tab=stocks';
                } else {
                    alert('Lỗi xuất hàng ôm: ' + (data.message || 'Không thể thực hiện'));
                }
            } catch(e) {
                alert('Lỗi kết nối máy chủ: ' + e.message);
            } finally {
                this.isSubmittingVanSale = false;
            }
        },

        openDispatchDetailModal(disp) {
            this.activeDispatch = disp;
            this.showDispatchDetailModal = true;
        },

        printDispatchSlip(disp) {
            if (!disp) return;
            const printWin = window.open('', '_blank');
            if (!printWin) {
                alert('Trình duyệt đang chặn cửa sổ pop-up. Vui lòng cho phép để in.');
                return;
            }

            let itemsRows = '';
            (disp.items || []).forEach((it, idx) => {
                const amt = Number(it.amount || ((it.quantity || 0) * (it.unit_price || 0)));
                itemsRows += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">${idx + 1}</td>
                        <td style="border: 1px solid #000; padding: 6px;"><strong>${it.name || ''}</strong><br><span style="font-size: 10px; color: #555;">${it.sku || ''}</span></td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">${it.unit || ''}</td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center; font-weight: bold;">${it.quantity}</td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: right;">${this.formatMoney(it.unit_price)} đ</td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">${this.formatMoney(amt)} đ</td>
                    </tr>
                `;
            });

            const html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>Phiếu Xuất Kho Hàng Ôm - ${disp.code}</title>
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #000; padding: 20px; }
                        h1 { text-align: center; font-size: 18px; margin-bottom: 4px; text-transform: uppercase; }
                        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                        th, td { border: 1px solid #000; padding: 6px; font-size: 11px; }
                        th { background-color: #f2f2f2; text-align: center; }
                        .info-grid { display: flex; justify-content: space-between; margin-top: 15px; }
                        .signatures { display: flex; justify-content: space-around; margin-top: 40px; text-align: center; }
                        .sign-box { width: 200px; height: 80px; }
                    </style>
                </head>
                <body>
                    <div style="text-align: center; border-bottom: 1px solid #000; padding-bottom: 8px;">
                        <h3 style="margin: 0; text-transform: uppercase;">CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN</h3>
                        <p style="margin: 2px 0; font-size: 10px;">Địa chỉ: KCN Quang Minh, Mê Linh, Hà Nội | Hotline: 098.888.8888</p>
                        <h1>PHIẾU XUẤT KHO HÀNG ÔM CHO NHÂN VIÊN (PXK-OM)</h1>
                        <p style="margin: 0; font-style: italic;">Mã phiếu: <strong>${disp.code}</strong> - Ngày xuất: ${disp.dispatch_date ? new Date(disp.dispatch_date).toLocaleDateString('vi-VN') : new Date().toLocaleDateString('vi-VN')}</p>
                    </div>

                    <div class="info-grid">
                        <div>
                            <div><strong>Kho xuất nguồn:</strong> ${disp.warehouse_name || 'Kho Tổng Mê Linh'}</div>
                            <div><strong>Phương tiện / Biển số:</strong> ${disp.vehicle_plate || '---'}</div>
                        </div>
                        <div>
                            <div><strong>Nhân viên nhận ôm:</strong> ${disp.driver_name}</div>
                            <div><strong>Ghi chú:</strong> ${disp.notes || 'Xuất hàng ôm cho nhân viên'}</div>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên Sản Phẩm & Quy Cách</th>
                                <th>ĐVT</th>
                                <th>Số Lượng</th>
                                <th>Đơn Giá</th>
                                <th>Thành Tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsRows}
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" style="text-align: right;">TỔNG CỘNG:</th>
                                <th style="text-align: center;">${(disp.items || []).reduce((s, i) => s + Number(i.quantity || 0), 0)}</th>
                                <th></th>
                                <th style="text-align: right; color: #b91c1c;">${this.formatMoney(disp.total_amount)} đ</th>
                            </tr>
                        </tfoot>
                    </table>

                    <p style="font-size: 11px; margin-top: 10px; font-style: italic;">
                        * Lưu ý: Hàng hóa được giao giữ trong <strong>Kho Hàng Ôm của nhân viên</strong>. Khi nhân viên bán lẻ trên thị trường, hệ thống sẽ tự động trừ tồn kho ôm. Nếu không bán hết có thể lập phiếu hoàn nhập lại kho công ty.
                    </p>

                    <div class="signatures">
                        <div>
                            <strong>Thủ Kho Xuất</strong>
                            <div class="sign-box"></div>
                            <div>(Ký, họ tên)</div>
                        </div>
                        <div>
                            <strong>Nhân Viên Nhận Hàng</strong>
                            <div class="sign-box"></div>
                            <div><strong>${disp.driver_name}</strong></div>
                        </div>
                        <div>
                            <strong>Kế Toán Kho</strong>
                            <div class="sign-box"></div>
                            <div>(Ký, họ tên)</div>
                        </div>
                    </div>
                </body>
                </html>
            `;
            printWin.document.open();
            printWin.document.write(html);
            printWin.document.close();
            printWin.focus();
            setTimeout(() => printWin.print(), 350);
        }
    };
}
</script>
@endsection

