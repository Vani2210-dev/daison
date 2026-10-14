@extends('layouts.app')

@section('title', 'Quản Lý Công Nợ Khách Hàng & Đại Lý - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="{
    // Modal chi tiết công nợ đại lý
    showModal: false,
    loading: false,
    activeTab: 'orders',
    activeDealer: null,
    orders: [],
    transactions: [],

    async openDebtDetail(dealerId) {
        this.showModal = true;
        this.loading = true;
        this.activeTab = 'orders';
        try {
            const res = await fetch(`/debt/${dealerId}?format=json`);
            const data = await res.json();
            if (data.success) {
                this.activeDealer = data.dealer;
                this.orders = data.orders || [];
                this.transactions = data.transactions || [];
                this.activeDealer.remainingCredit = data.remainingCredit;
                this.activeDealer.totalOrderAmount = data.totalOrderAmount;
            }
        } catch(e) {
            console.error('Error fetching debt details:', e);
        } finally {
            this.loading = false;
        }
    },

    formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0));
    },

    formatDate(dateStr) {
        if (!dateStr) return '---';
        const d = new Date(dateStr);
        return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('vi-VN');
    }
}">

    <!-- ==================== HEADER & TOP ACTION BAR ==================== -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span>Quản Lý Công Nợ Khách Hàng & Đại Lý</span>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                            Khách Hàng / Đại Lý
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500">Theo dõi hạn mức tín dụng, dư nợ hiện tại, nợ quá hạn và sổ theo dõi công nợ chi tiết của đại lý & khách hàng</p>
                </div>
            </div>
            
            <div class="flex items-center flex-wrap gap-2">
                <a href="{{ url('/staff-debt') }}" class="px-3.5 py-2 rounded-xl border border-purple-300 bg-purple-50 hover:bg-purple-100 text-purple-800 text-xs font-bold shadow-2xs flex items-center gap-1.5 transition-colors" title="Chuyển sang Quản lý công nợ nhân sự & biên bản bàn giao nợ">
                    <i class="fa-solid fa-user-tie text-purple-600"></i>
                    <span>Công Nợ Nhân Viên & Bàn Giao</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                <a href="{{ url('/dealers/create') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-user-plus text-sky-600"></i>
                    <span>+ Thêm Khách Hàng</span>
                </a>
                <a href="{{ url('/funds') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-all cursor-pointer hover:shadow-md">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Thu Tiền Khách Hàng</span>
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-3 border-t border-slate-100">
            <div class="p-3 bg-rose-50/70 rounded-xl border border-rose-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-rose-800">Tổng Nợ Khách Hàng</span>
                    <p class="text-lg font-bold text-rose-900 font-mono mt-0.5">{{ number_format($totalDebt ?? 0, 0, ',', '.') }} đ</p>
                </div>
            </div>
            <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-amber-800">Nợ Quá Hạn / Vượt Hạn Mức</span>
                    <p class="text-lg font-bold text-amber-900 font-mono mt-0.5">{{ number_format($overdueDebt ?? 0, 0, ',', '.') }} đ</p>
                </div>
            </div>
            <div class="p-3 bg-emerald-50/70 rounded-xl border border-emerald-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-emerald-800">Tổng Hạn Mức Tín Dụng</span>
                    <p class="text-lg font-bold text-emerald-900 font-mono mt-0.5">{{ number_format($totalCreditLimit ?? 0, 0, ',', '.') }} đ</p>
                </div>
            </div>
            <div class="p-3 bg-sky-50/70 rounded-xl border border-sky-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-sky-800">Đại Lý Trong Hạn Nợ</span>
                    <p class="text-lg font-bold text-sky-900 mt-0.5">{{ $inTermDealersCount ?? 0 }} đại lý</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== BẢNG CÔNG NỢ ĐẠI LÝ / KHÁCH HÀNG ==================== -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <form method="GET" action="{{ url('/debt') }}" class="p-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 bg-slate-50/70">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên, mã ĐL, SĐT..." 
                           class="px-3 py-1.5 pl-8 text-xs rounded-lg border border-slate-300 w-64 bg-white focus:ring-1 focus:ring-sky-500 font-medium">
                    <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <select name="tier" onchange="this.form.submit()" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white">
                    <option value="">Tất cả phân cấp</option>
                    <option value="Cấp 1" {{ request('tier') === 'Cấp 1' ? 'selected' : '' }}>Đại lý Cấp 1</option>
                    <option value="Cấp 2" {{ request('tier') === 'Cấp 2' ? 'selected' : '' }}>Đại lý Cấp 2</option>
                    <option value="Đại lý tỉnh" {{ request('tier') === 'Đại lý tỉnh' ? 'selected' : '' }}>Đại lý tỉnh</option>
                    <option value="Khách lẻ" {{ request('tier') === 'Khách lẻ' ? 'selected' : '' }}>Khách lẻ</option>
                </select>
                <select name="debt_status" onchange="this.form.submit()" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white">
                    <option value="">Tất cả tình trạng nợ</option>
                    <option value="has_debt" {{ request('debt_status') === 'has_debt' ? 'selected' : '' }}>Đang có công nợ (> 0)</option>
                    <option value="in_term" {{ request('debt_status') === 'in_term' ? 'selected' : '' }}>Nợ trong hạn mức</option>
                    <option value="overdue" {{ request('debt_status') === 'overdue' ? 'selected' : '' }}>Nợ vượt hạn mức</option>
                    <option value="no_debt" {{ request('debt_status') === 'no_debt' ? 'selected' : '' }}>Không có nợ (= 0)</option>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer">
                    Lọc
                </button>
                @if(request('search') || request('tier') || request('debt_status'))
                    <a href="{{ url('/debt') }}" class="px-2.5 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
                        Đặt lại
                    </a>
                @endif
            </div>
            <div class="text-xs text-slate-500">
                Tổng số: <span class="font-bold text-slate-800">{{ $dealers->total() ?? 0 }}</span> khách hàng
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Khách Hàng</th>
                        <th class="py-2.5 px-3">Tên Đại Lý / Khách Hàng</th>
                        <th class="py-2.5 px-3">Phân Cấp</th>
                        <th class="py-2.5 px-3 text-right">Hạn Mức Tín Dụng</th>
                        <th class="py-2.5 px-3 text-right">Dư Nợ Hiện Tại</th>
                        <th class="py-2.5 px-3 text-right">Hạn Mức Còn Lại</th>
                        <th class="py-2.5 px-3 text-center">Tình Trạng</th>
                        <th class="py-2.5 px-3 text-center min-w-[140px]">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($dealers as $idx => $d)
                        @php
                            $debt = (float)($d->current_debt ?? 0);
                            $limit = (float)($d->credit_limit ?? 0);
                            $remaining = max(0, $limit - $debt);
                            $isOverLimit = $debt > $limit && $limit > 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $dealers->firstItem() + $idx }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-sky-800">{{ $d->code }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">
                                <div>{{ $d->name }}</div>
                                <div class="text-[11px] text-slate-500 font-normal">{{ $d->phone }} &bull; {{ $d->address ?: 'Chưa có địa chỉ' }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $d->tier === 'Cấp 1' ? 'bg-amber-100 text-amber-800' : ($d->tier === 'Cấp 2' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $d->tier ?: 'Mặc định' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-700">{{ number_format($limit, 0, ',', '.') }} đ</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $debt > 0 ? 'text-rose-700' : 'text-slate-900' }}">
                                {{ number_format($debt, 0, ',', '.') }} đ
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-700 font-bold">
                                {{ number_format($remaining, 0, ',', '.') }} đ
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($isOverLimit)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 animate-pulse">Vượt hạn mức</span>
                                @elseif($debt > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Trong hạn mức</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Không có nợ</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openDebtDetail({{ $d->id }})" class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-800 font-bold text-[11px] border border-sky-200 transition-colors inline-flex items-center gap-1 shadow-2xs cursor-pointer">
                                        <i class="fa-solid fa-file-invoice-dollar text-sky-600"></i>
                                        <span>Xem sổ nợ</span>
                                    </button>
                                    <a href="{{ url('/debt/' . $d->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-sky-700 hover:bg-slate-100 transition-colors" title="Mở trang chi tiết toàn màn hình">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Không tìm thấy dữ liệu công nợ khách hàng</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dealers->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $dealers->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== MODAL: SỔ CHI TIẾT CÔNG NỢ ĐẠI LÝ ==================== -->
    <div x-show="showModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.away="showModal = false">
            
            <div class="p-4 border-b border-slate-200 bg-slate-50/80 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>Sổ Chi Tiết Công Nợ:</span>
                            <span class="text-sky-700" x-text="activeDealer?.name"></span>
                        </h3>
                        <p class="text-[11px] text-slate-500 font-mono">
                            Mã: <span x-text="activeDealer?.code"></span> | SĐT: <span x-text="activeDealer?.phone"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-lg hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="p-4 overflow-y-auto flex-1 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-500 text-[11px]">Hạn mức tín dụng</span>
                        <p class="font-mono font-bold text-slate-900 mt-0.5" x-text="formatNumber(activeDealer?.credit_limit) + ' đ'"></p>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[11px]">Dư nợ hiện tại</span>
                        <p class="font-mono font-bold text-rose-700 mt-0.5" x-text="formatNumber(activeDealer?.current_debt) + ' đ'"></p>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[11px]">Khả dụng còn lại</span>
                        <p class="font-mono font-bold text-emerald-700 mt-0.5" x-text="formatNumber(activeDealer?.remainingCredit) + ' đ'"></p>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[11px]">Tổng giá trị đơn hàng</span>
                        <p class="font-mono font-bold text-indigo-700 mt-0.5" x-text="formatNumber(activeDealer?.totalOrderAmount) + ' đ'"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-bold">
                    <button type="button" @click="activeTab = 'orders'"
                            class="pb-2.5 px-3 border-b-2 transition-colors flex items-center gap-1.5"
                            :class="activeTab === 'orders' ? 'border-sky-600 text-sky-600' : 'border-transparent text-slate-500 hover:text-slate-800'">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Đơn Hàng Ghi Nợ</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-sky-100 text-sky-800" x-text="orders.length"></span>
                    </button>
                    <button type="button" @click="activeTab = 'transactions'"
                            class="pb-2.5 px-3 border-b-2 transition-colors flex items-center gap-1.5"
                            :class="activeTab === 'transactions' ? 'border-sky-600 text-sky-600' : 'border-transparent text-slate-500 hover:text-slate-800'">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Lịch Sử Thu Tiền / Thanh Toán</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700" x-text="transactions.length"></span>
                    </button>
                </div>

                <!-- Tab Đơn Hàng -->
                <div x-show="activeTab === 'orders'">
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10.5px]">
                                <tr>
                                    <th class="py-2 px-3">Mã Đơn</th>
                                    <th class="py-2 px-3">Ngày Lập</th>
                                    <th class="py-2 px-3 text-right">Tổng Tiền</th>
                                    <th class="py-2 px-3 text-right">Nợ Tài Xế</th>
                                    <th class="py-2 px-3 text-center">Trạng Thái Đơn</th>
                                    <th class="py-2 px-3 text-center">Thanh Toán</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="ord in orders" :key="ord.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 font-mono font-bold text-sky-700">
                                            <a :href="'/orders/' + ord.id + '/edit'" target="_blank" class="hover:underline" x-text="ord.order_code"></a>
                                        </td>
                                        <td class="py-2 px-3 text-slate-500" x-text="formatDate(ord.created_at)"></td>
                                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900" x-text="formatNumber(ord.total_amount) + ' đ'"></td>
                                        <td class="py-2 px-3 text-right font-mono text-rose-600" x-text="formatNumber(ord.driver_debt_amount) + ' đ'"></td>
                                        <td class="py-2 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700" x-text="ord.status"></span>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                  :class="ord.payment_status === 'Đã thanh toán' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                                  x-text="ord.payment_status || 'Chưa thanh toán'"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="orders.length === 0">
                                    <td colspan="6" class="py-6 text-center text-slate-400">Không có đơn hàng nào cho đại lý này</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Thanh Toán -->
                <div x-show="activeTab === 'transactions'" style="display: none;">
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10.5px]">
                                <tr>
                                    <th class="py-2 px-3">Mã Phiếu Thu</th>
                                    <th class="py-2 px-3">Ngày Thu</th>
                                    <th class="py-2 px-3 text-right">Số Tiền</th>
                                    <th class="py-2 px-3">Hình Thức</th>
                                    <th class="py-2 px-3">Người Thu</th>
                                    <th class="py-2 px-3">Ghi Chú</th>
                                    <th class="py-2 px-3 text-center">Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="t in transactions" :key="t.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 font-mono font-bold text-emerald-700" x-text="t.code || ('PT-' + t.id)"></td>
                                        <td class="py-2 px-3 text-slate-500" x-text="formatDate(t.created_at)"></td>
                                        <td class="py-2 px-3 text-right font-mono font-bold text-emerald-800" x-text="formatNumber(t.amount) + ' đ'"></td>
                                        <td class="py-2 px-3 text-slate-700" x-text="t.payment_method || 'Tiền mặt'"></td>
                                        <td class="py-2 px-3 text-slate-700" x-text="t.creator_name || 'Kế toán'"></td>
                                        <td class="py-2 px-3 text-slate-600" x-text="t.notes || 'Thu công nợ khách hàng'"></td>
                                        <td class="py-2 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Đã hạch toán</span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="transactions.length === 0">
                                    <td colspan="7" class="py-6 text-center text-slate-400">Chưa có phiếu thu tiền nào cho đại lý này</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium" x-text="activeDealer?.name"></span>
                <button type="button" @click="showModal = false" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl transition-colors cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
