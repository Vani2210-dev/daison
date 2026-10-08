@extends('layouts.app')

@section('title', 'Quản Lý Công Nợ & Bàn Giao Nợ - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="{
    currentMainTab: 'dealers', // 'dealers', 'staff', 'handovers', 'vansales'
    search: '',
    handoverStatusFilter: 'all',
    
    // Modal chi tiết công nợ đại lý
    showModal: false,
    loading: false,
    activeTab: 'orders',
    activeDealer: null,
    orders: [],
    transactions: [],

    // Modal Bàn giao công nợ
    showHandoverModal: false,
    handoverForm: {
        order_code: '',
        to_user_id: '',
        amount: '',
        notes: ''
    },

    // Modal Xuất hàng lẻ lên xe
    showVanSaleModal: false,
    vanSaleForm: {
        driver_id: '',
        vehicle_plate: '',
        notes: '',
        items: [
            { product_id: '', quantity: 1, unit_price: 0 }
        ]
    },

    availableProducts: {{ Js::from($allProducts ?? []) }},

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
    },

    addVanItem() {
        this.vanSaleForm.items.push({ product_id: '', quantity: 1, unit_price: 0 });
    },

    removeVanItem(idx) {
        if (this.vanSaleForm.items.length > 1) {
            this.vanSaleForm.items.splice(idx, 1);
        }
    },

    onProductSelect(idx) {
        const item = this.vanSaleForm.items[idx];
        const prod = this.availableProducts.find(p => p.id == item.product_id);
        if (prod) {
            item.unit_price = Number(prod.cost_price || 0) * 1.2; // giá gợi ý
        }
    },

    getVanSaleTotal() {
        return this.vanSaleForm.items.reduce((sum, it) => sum + (Number(it.quantity || 0) * Number(it.unit_price || 0)), 0);
    },

    async submitHandover() {
        if (!this.handoverForm.to_user_id) {
            alert('Vui lòng chọn người nhận bàn giao nợ!');
            return;
        }
        if (!this.handoverForm.amount || Number(this.handoverForm.amount) <= 0) {
            alert('Vui lòng nhập số tiền công nợ hợp lệ!');
            return;
        }

        try {
            const res = await fetch('/debt-handovers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.handoverForm)
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Lỗi lập biên bản bàn giao');
            }
        } catch(e) {
            alert('Lỗi kết nối máy chủ!');
        }
    },

    async confirmHandover(id) {
        if (!confirm('Bạn có chắc chắn muốn XÁC NHẬN NHẬN NỢ cho biên bản này? Công nợ sẽ được chuyển chính thức.')) return;
        try {
            const res = await fetch(`/debt-handovers/${id}/confirm`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Không thể xác nhận');
            }
        } catch(e) {
            alert('Lỗi kết nối máy chủ');
        }
    },

    async rejectHandover(id) {
        const reason = prompt('Nhập lý do từ chối nhận nợ:');
        if (reason === null) return;
        try {
            const res = await fetch(`/debt-handovers/${id}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason: reason || 'Không đồng ý nhận nợ' })
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Không thể từ chối');
            }
        } catch(e) {
            alert('Lỗi kết nối máy chủ');
        }
    },

    async submitVanSale() {
        if (!this.vanSaleForm.driver_id) {
            alert('Vui lòng chọn tài xế / người vận chuyển nhận hàng lẻ!');
            return;
        }
        if (this.vanSaleForm.items.length === 0 || !this.vanSaleForm.items[0].product_id) {
            alert('Vui lòng chọn ít nhất 1 sản phẩm bốc lên xe!');
            return;
        }

        try {
            const res = await fetch('/van-sale-dispatches', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.vanSaleForm)
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Lỗi xuất hàng lẻ');
            }
        } catch(e) {
            alert('Lỗi kết nối máy chủ!');
        }
    }
}">
    <!-- Header Banner -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ url('/dashboard') }}" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar text-rose-600"></i>
                        <span>Trung Tâm Quản Lý Công Nợ & Bàn Giao Nợ</span>
                    </h1>
                    <p class="text-xs text-slate-500">Quản lý công nợ Đại lý, công nợ Nhân viên/Lái xe, luồng bàn giao nợ 2 bước và hàng lẻ lưu động</p>
                </div>
            </div>
            
            <div class="flex items-center flex-wrap gap-2">
                <button type="button" @click="showHandoverModal = true" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                    <i class="fa-solid fa-share-from-square"></i>
                    <span>Bàn Giao Công Nợ</span>
                </button>
                <button type="button" @click="showVanSaleModal = true" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Xuất Hàng Lẻ Xe</span>
                </button>
                <a href="{{ url('/funds') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <span>Thu Tiền</span>
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-3 border-t border-slate-100">
            <div class="p-3 bg-rose-50/70 rounded-xl border border-rose-100">
                <span class="text-[11px] font-semibold text-rose-800">Tổng Nợ Khách Hàng / ĐL</span>
                <p class="text-xl font-bold text-rose-900 mt-1">{{ number_format($totalDebt ?? 0, 0, ',', '.') }} đ</p>
            </div>
            <div class="p-3 bg-indigo-50/70 rounded-xl border border-indigo-100">
                <span class="text-[11px] font-semibold text-indigo-800">Công Nợ Nhân Viên & Lái Xe</span>
                <p class="text-xl font-bold text-indigo-900 mt-1">{{ number_format($totalStaffDebt ?? 0, 0, ',', '.') }} đ</p>
            </div>
            <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-100">
                <span class="text-[11px] font-semibold text-amber-800">Bàn Giao Chờ Xác Nhận</span>
                <p class="text-xl font-bold text-amber-900 mt-1">{{ $pendingHandoversCount ?? 0 }} Biên Bản</p>
            </div>
            <div class="p-3 bg-emerald-50/70 rounded-xl border border-emerald-100">
                <span class="text-[11px] font-semibold text-emerald-800">Tổng Hạn Mức Tín Dụng</span>
                <p class="text-xl font-bold text-emerald-900 mt-1">{{ number_format($totalCreditLimit ?? 0, 0, ',', '.') }} đ</p>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200 shadow-2xs overflow-x-auto text-xs font-bold">
        <button type="button" @click="currentMainTab = 'dealers'"
                class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 shrink-0 cursor-pointer"
                :class="currentMainTab === 'dealers' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'">
            <i class="fa-solid fa-store"></i>
            <span>1. Công Nợ Đại Lý / Khách Hàng</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentMainTab === 'dealers' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $dealers->total() ?? 0 }}</span>
        </button>

        <button type="button" @click="currentMainTab = 'staff'"
                class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 shrink-0 cursor-pointer"
                :class="currentMainTab === 'staff' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'">
            <i class="fa-solid fa-users-gear"></i>
            <span>2. Công Nợ Nhân Viên & Lái Xe</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentMainTab === 'staff' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ count($staffList ?? []) }}</span>
        </button>

        <button type="button" @click="currentMainTab = 'handovers'"
                class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 shrink-0 cursor-pointer"
                :class="currentMainTab === 'handovers' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'">
            <i class="fa-solid fa-share-nodes"></i>
            <span>3. Biên Bản Bàn Giao Nợ (2 Bước)</span>
            @if(($pendingHandoversCount ?? 0) > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-500 text-white animate-pulse">{{ $pendingHandoversCount }} Chờ duyệt</span>
            @else
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentMainTab === 'handovers' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ count($handovers ?? []) }}</span>
            @endif
        </button>

        <button type="button" @click="currentMainTab = 'vansales'"
                class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 shrink-0 cursor-pointer"
                :class="currentMainTab === 'vansales' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'">
            <i class="fa-solid fa-truck-moving"></i>
            <span>4. Hàng Lẻ Lưu Động Xuất Xe</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="currentMainTab === 'vansales' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ count($vanDispatches ?? []) }}</span>
        </button>
    </div>

    <!-- ==================== TAB 1: CÔNG NỢ ĐẠI LÝ / KHÁCH HÀNG ==================== -->
    <div x-show="currentMainTab === 'dealers'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã ĐL</th>
                        <th class="py-2.5 px-3">Tên Đại Lý / Nhà Phân Phối</th>
                        <th class="py-2.5 px-3">Cấp Đại Lý</th>
                        <th class="py-2.5 px-3 text-right">Hạn Mức Tín Dụng</th>
                        <th class="py-2.5 px-3 text-right">Dư Nợ Hiện Tại</th>
                        <th class="py-2.5 px-3 text-right">Còn Được Phép Nợ</th>
                        <th class="py-2.5 px-3 text-center">Tình Trạng</th>
                        <th class="py-2.5 px-3 text-center min-w-[130px]">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($dealers ?? [] as $index => $d)
                        @php
                            $limit = $d->credit_limit ?? 100000000;
                            $debt = $d->current_debt ?? $d->debt ?? 0;
                            $remain = max(0, $limit - $debt);
                        @endphp
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-3 font-bold text-sky-700">{{ $d->code ?? ('DL-0' . $d->id) }}</td>
                            <td class="py-2.5 px-3">
                                <a href="javascript:void(0)" @click="openDebtDetail({{ $d->id }})" class="font-bold text-slate-900 hover:text-sky-700 hover:underline flex items-center gap-1.5">
                                    <span>{{ $d->name }}</span>
                                    <i class="fa-solid fa-circle-info text-[10px] text-sky-500"></i>
                                </a>
                                <div class="text-[11px] text-slate-500">{{ $d->phone ?? '---' }} &bull; {{ $d->address ?? 'Khu vực Bắc Miền Trung' }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $d->tier ?? 'Đại lý C2' }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-700">{{ number_format($limit, 0, ',', '.') }} đ</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $debt > $limit ? 'text-rose-600' : 'text-slate-900' }}">{{ number_format($debt, 0, ',', '.') }} đ</td>
                            <td class="py-2.5 px-3 text-right font-mono font-semibold text-emerald-600">{{ number_format($remain, 0, ',', '.') }} đ</td>
                            <td class="py-2.5 px-3 text-center">
                                @if($debt > $limit)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Vượt hạn mức</span>
                                @elseif($debt > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Đang nợ</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Không nợ</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openDebtDetail({{ $d->id }})" class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-800 font-bold text-[11px] border border-sky-200 transition-colors inline-flex items-center gap-1 shadow-2xs cursor-pointer">
                                        <i class="fa-solid fa-file-invoice-dollar text-sky-600"></i>
                                        <span>Xem chi tiết</span>
                                    </button>
                                    <a href="{{ url('/debt/' . $d->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-sky-700 hover:bg-slate-100 transition-colors" title="Mở trang chi tiết toàn màn hình">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Không tìm thấy dữ liệu công nợ đại lý</td>
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

    <!-- ==================== TAB 2: CÔNG NỢ NHÂN VIÊN & NGƯỜI VẬN CHUYỂN ==================== -->
    <div x-show="currentMainTab === 'staff'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden" style="display: none;">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-user-tag text-indigo-600"></i>
                <span>Danh Sách Nhân Viên & Người Vận Chuyển Đảm Nhận Công Nợ</span>
            </span>
            <span class="text-xs text-slate-500 font-semibold">Tự động tăng khi bốc hàng lên xe / nhận bàn giao nợ từ đơn</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã NV</th>
                        <th class="py-2.5 px-3">Họ Tên Nhân Sự</th>
                        <th class="py-2.5 px-3">Phân Loại Vai Trò</th>
                        <th class="py-2.5 px-3">Đại Lý Phụ Thuộc (Nếu có)</th>
                        <th class="py-2.5 px-3 text-right">Hạn Mức Quản Lý</th>
                        <th class="py-2.5 px-3 text-right">Dư Nợ Đang Nắm Giữ</th>
                        <th class="py-2.5 px-3 text-center">Tình Trạng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($staffList ?? [] as $idx => $st)
                        @php
                            $stDebt = (float)($st->current_debt ?? 0);
                            $stLimit = (float)($st->credit_limit ?? 50000000);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-indigo-700">{{ $st->code ?: ('NV-' . str_pad($st->id, 3, '0', STR_PAD_LEFT)) }}</td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900">{{ $st->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $st->phone ?: 'Chưa có SĐT' }} &bull; {{ $st->department ?: 'Nội bộ' }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($st->staff_type === 'driver')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 flex items-center gap-1 w-max">
                                        <i class="fa-solid fa-truck"></i> Người vận chuyển / Lái xe
                                    </span>
                                @elseif($st->staff_type === 'dealer_sales')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 flex items-center gap-1 w-max">
                                        <i class="fa-solid fa-store"></i> Sale đại lý phụ thuộc
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 flex items-center gap-1 w-max">
                                        <i class="fa-solid fa-briefcase"></i> Sale công ty
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                @if($st->dealer)
                                    <div class="font-bold text-purple-900">{{ $st->dealer->name }}</div>
                                    <div class="text-[10px] text-purple-600 font-mono">{{ $st->dealer->code }} (Auto ghi nợ vào ĐL này khi lên đơn)</div>
                                @else
                                    <span class="text-slate-400">---</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-700">{{ number_format($stLimit, 0, ',', '.') }} đ</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $stDebt > 0 ? 'text-rose-700' : 'text-slate-900' }}">
                                {{ number_format($stDebt, 0, ',', '.') }} đ
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($stDebt > $stLimit && $stLimit > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Vượt hạn mức giữ</span>
                                @elseif($stDebt > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Đang giữ tiền / nợ</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Cân bằng</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Chưa có dữ liệu nhân viên đảm nhận công nợ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== TAB 3: BIÊN BẢN BÀN GIAO NỢ (2 BƯỚC) ==================== -->
    <div x-show="currentMainTab === 'handovers'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden" style="display: none;">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-800">Lọc trạng thái:</span>
                <select x-model="handoverStatusFilter" class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-300 bg-white">
                    <option value="all">Tất cả biên bản</option>
                    <option value="pending">⏳ Chờ xác nhận nhận nợ</option>
                    <option value="confirmed">✅ Đã xác nhận chuyển nợ</option>
                    <option value="rejected">❌ Đã từ chối</option>
                </select>
            </div>
            <button type="button" @click="showHandoverModal = true" class="px-3 py-1 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-lg shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Tạo Biên Bản Bàn Giao Nợ Mới</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-10 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Biên Bản</th>
                        <th class="py-2.5 px-3">Mã Đơn Hàng</th>
                        <th class="py-2.5 px-3">Bên Bàn Giao (Lái xe)</th>
                        <th class="py-2.5 px-3">Bên Nhận Nợ (Sale / Đại lý)</th>
                        <th class="py-2.5 px-3 text-right">Số Tiền Nợ</th>
                        <th class="py-2.5 px-3">Ghi Chú</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center min-w-[140px]">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($handovers ?? [] as $hIdx => $h)
                        <tr class="hover:bg-slate-50 transition-colors"
                            x-show="handoverStatusFilter === 'all' || handoverStatusFilter === '{{ $h->status }}'">
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $hIdx + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-sky-800">{{ $h->code }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">
                                @if($h->order_id)
                                    <a href="{{ url('/orders/' . $h->order_id . '/edit') }}" class="hover:underline text-sky-700" target="_blank">{{ $h->order_code }}</a>
                                @else
                                    {{ $h->order_code ?: 'N/A' }}
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900">{{ $h->from_user_name ?: 'Lái xe' }}</div>
                                <span class="text-[10px] text-slate-500">Giảm nợ khi duyệt</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-indigo-900">{{ $h->to_user_name }}</div>
                                @if($h->target_dealer_name)
                                    <div class="text-[10.5px] text-purple-700 font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-arrow-turn-down-right"></i>
                                        <span>Ghi nợ vào ĐL: {{ $h->target_dealer_name }}</span>
                                    </div>
                                @else
                                    <span class="text-[10px] text-slate-500">Ghi nợ Sale công ty</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-700">
                                {{ number_format($h->amount, 0, ',', '.') }} đ
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 max-w-xs truncate">{{ $h->notes ?: '---' }}</td>
                            <td class="py-2.5 px-3 text-center">
                                @if($h->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-clock"></i> Chờ xác nhận
                                    </span>
                                @elseif($h->status === 'confirmed')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-check"></i> Đã nhận nợ
                                    </span>
                                    <div class="text-[9px] text-slate-400 mt-0.5">{{ $h->confirmed_by_name }} ({{ $h->confirmed_at ? $h->confirmed_at->format('H:i d/m') : '' }})</div>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-xmark"></i> Từ chối
                                    </span>
                                    <div class="text-[9px] text-rose-600 mt-0.5 truncate max-w-[120px]" title="{{ $h->rejected_reason }}">{{ $h->rejected_reason }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($h->status === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="confirmHandover({{ $h->id }})" class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10.5px] transition-colors shadow-2xs cursor-pointer flex items-center gap-1" title="Bấm để xác nhận nhận nợ">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Nhận Nợ</span>
                                        </button>
                                        <button type="button" @click="rejectHandover({{ $h->id }})" class="px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[10.5px] transition-colors cursor-pointer" title="Từ chối">
                                            <span>Từ Chối</span>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">Đã kết thúc</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Chưa có biên bản bàn giao công nợ nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== TAB 4: HÀNG LẺ LƯU ĐỘNG XUẤT XE ==================== -->
    <div x-show="currentMainTab === 'vansales'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden" style="display: none;">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-boxes-packing text-emerald-600"></i>
                <span>Danh Sách Phiếu Bốc Hàng Lẻ Lưu Động Lên Xe (Tính Công Nợ Cho Tài Xế)</span>
            </span>
            <button type="button" @click="showVanSaleModal = true" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Xuất Thêm Hàng Lên Xe</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-10 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Phiếu Xuất</th>
                        <th class="py-2.5 px-3">Ngày Xuất</th>
                        <th class="py-2.5 px-3">Lái Xe / Người Chịu Nợ</th>
                        <th class="py-2.5 px-3">Biển Số Xe</th>
                        <th class="py-2.5 px-3">Số Mặt Hàng</th>
                        <th class="py-2.5 px-3 text-right">Tổng Tiền Hàng (Tính Nợ Xe)</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($vanDispatches ?? [] as $vIdx => $v)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $vIdx + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-emerald-700">{{ $v->code }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $v->dispatch_date ? $v->dispatch_date->format('d/m/Y') : '' }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">{{ $v->driver_name }}</td>
                            <td class="py-2.5 px-3 font-mono font-semibold text-slate-700">{{ $v->vehicle_plate ?: 'Xe lưu động' }}</td>
                            <td class="py-2.5 px-3">{{ count($v->items ?? []) }} mặt hàng</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-700">
                                {{ number_format($v->total_amount, 0, ',', '.') }} đ
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    Đã bốc lên xe (Đang nợ)
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Chưa có phiếu xuất hàng lẻ lưu động nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== MODAL: LẬP BIÊN BẢN BÀN GIAO CÔNG NỢ ==================== -->
    <div x-show="showHandoverModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.away="showHandoverModal = false">
            <div class="p-4 border-b border-slate-200 bg-sky-50/80 flex items-center justify-between">
                <h3 class="text-sm font-bold text-sky-950 flex items-center gap-2">
                    <i class="fa-solid fa-share-from-square text-sky-600"></i>
                    <span>Lập Biên Bản Bàn Giao Công Nợ (Quy trình 2 bước)</span>
                </h3>
                <button type="button" @click="showHandoverModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="p-4 space-y-3 text-xs">
                <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11.5px] leading-relaxed">
                    💡 <strong>Quy trình:</strong> Khi người vận chuyển giao đơn xong, lập biên bản chuyển công nợ sang Sale công ty hoặc Sale đại lý phụ thuộc. Công nợ chỉ chuyển chính thức sau khi Bên Nhận bấm <strong>'Xác nhận nhận nợ'</strong>.
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mã Đơn Hàng (Nếu bàn giao theo đơn):</label>
                    <input type="text" x-model="handoverForm.order_code" placeholder="VD: DH-2026-1001" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Người Nhận Bàn Giao Nợ (*):</label>
                    <select x-model="handoverForm.to_user_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500">
                        <option value="">-- Chọn nhân sự nhận nợ --</option>
                        @foreach($allStaff ?? [] as $st)
                            <option value="{{ $st->id }}">
                                {{ $st->name }} ({{ $st->staff_type === 'dealer_sales' ? 'Sale Đại Lý Phụ Thuộc' : ($st->staff_type === 'driver' ? 'Lái xe' : 'Sale Công Ty') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Số Tiền Công Nợ Bàn Giao (*):</label>
                    <input type="number" x-model="handoverForm.amount" placeholder="VD: 5000000" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono font-bold text-rose-700">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ghi Chú Bàn Giao:</label>
                    <textarea x-model="handoverForm.notes" rows="2" placeholder="VD: Khách hẹn thanh toán cho Sale vào ngày 15/10..." class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500"></textarea>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs">
                <button type="button" @click="showHandoverModal = false" class="px-4 py-1.5 rounded-lg border border-slate-300 font-bold text-slate-600 hover:bg-slate-100">
                    Hủy
                </button>
                <button type="button" @click="submitHandover()" class="px-4 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 font-bold text-white shadow-xs">
                    Gửi Yêu Cầu Bàn Giao
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: LẬP PHIẾU XUẤT HÀNG LẺ LÊN XE ==================== -->
    <div x-show="showVanSaleModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.away="showVanSaleModal = false">
            <div class="p-4 border-b border-slate-200 bg-emerald-50/80 flex items-center justify-between">
                <h3 class="text-sm font-bold text-emerald-950 flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
                    <span>Lập Phiếu Bốc Hàng Lẻ Lưu Động Lên Xe (Tính Nợ Xe)</span>
                </h3>
                <button type="button" @click="showVanSaleModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="p-4 space-y-3 text-xs max-h-[75vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Người Vận Chuyển / Lái Xe (*):</label>
                        <select x-model="vanSaleForm.driver_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-emerald-500 font-semibold">
                            <option value="">-- Chọn tài xế chịu nợ hàng --</option>
                            @foreach($allStaff ?? [] as $st)
                                @if($st->staff_type === 'driver')
                                    <option value="{{ $st->id }}">{{ $st->name }} (Lái xe)</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Biển Số Xe:</label>
                        <input type="text" x-model="vanSaleForm.vehicle_plate" placeholder="VD: 29C-888.99" class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="font-bold text-slate-800">Danh Sách Hàng Hóa Bốc Lên Xe:</label>
                        <button type="button" @click="addVanItem()" class="text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-circle-plus"></i> Thêm sản phẩm
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(it, idx) in vanSaleForm.items" :key="idx">
                            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="flex-1">
                                    <select x-model="it.product_id" @change="onProductSelect(idx)" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 bg-white text-xs">
                                        <option value="">-- Chọn sản phẩm / lõi lọc --</option>
                                        <template x-for="p in availableProducts" :key="p.id">
                                            <option :value="p.id" x-text="p.name + ' (Kho: ' + p.stock_quantity + ' ' + p.unit + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="w-20">
                                    <input type="number" x-model="it.quantity" min="1" placeholder="SL" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center font-bold">
                                </div>
                                <div class="w-28">
                                    <input type="number" x-model="it.unit_price" placeholder="Đơn giá" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 font-mono text-right font-bold text-slate-800">
                                </div>
                                <div class="w-6 text-center">
                                    <button type="button" @click="removeVanItem(idx)" class="text-rose-500 hover:text-rose-700">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-rose-900">Tổng Trị Giá Ghi Nợ Cho Lái Xe:</span>
                    <span class="font-mono font-black text-rose-800 text-base" x-text="formatNumber(getVanSaleTotal()) + ' đ'"></span>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs">
                <button type="button" @click="showVanSaleModal = false" class="px-4 py-1.5 rounded-lg border border-slate-300 font-bold text-slate-600 hover:bg-slate-100">
                    Hủy
                </button>
                <button type="button" @click="submitVanSale()" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 font-bold text-white shadow-xs">
                    Xác Nhận Xuất Hàng Lên Xe
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: CHI TIẾT CÔNG NỢ ĐẠI LÝ ==================== -->
    <div x-show="showModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
         style="display: none;">
        
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.away="showModal = false">
            
            <div class="p-4 border-b border-slate-200 flex items-center justify-between gap-3 bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-800 font-mono" x-text="activeDealer?.code || 'DL-***'"></span>
                            <h3 class="text-base font-bold text-slate-900" x-text="activeDealer?.name || 'Chi Tiết Công Nợ Khách Hàng'"></h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700" x-text="activeDealer?.tier || 'Đại lý Cấp 1'"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            <span x-text="activeDealer?.phone ? ('ĐT: ' + activeDealer.phone + ' • ') : ''"></span>
                            <span x-text="activeDealer?.address || 'Địa chỉ đại lý'"></span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="activeDealer?.id">
                        <a :href="'/debt/' + activeDealer.id" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-white flex items-center gap-1.5 transition-colors shadow-2xs" title="Mở trang chi tiết toàn màn hình để in ấn">
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            <span>Trang Riêng</span>
                        </a>
                    </template>
                    <button type="button" @click="showModal = false" class="w-8 h-8 rounded-lg bg-slate-200/70 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <div x-show="loading" class="p-12 text-center text-slate-500">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-sky-600 mb-2"></i>
                <p class="text-xs font-semibold">Đang tải dữ liệu công nợ & đơn hàng...</p>
            </div>

            <div x-show="!loading" class="flex-1 overflow-y-auto p-4 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-3 bg-sky-50 rounded-xl border border-sky-100">
                        <span class="text-[11px] font-semibold text-sky-800">Hạn Mức Tín Dụng</span>
                        <p class="text-lg font-bold font-mono text-sky-950 mt-0.5" x-text="formatNumber(activeDealer?.credit_limit) + ' đ'"></p>
                    </div>
                    <div class="p-3 rounded-xl border"
                         :class="(Number(activeDealer?.current_debt || 0) > Number(activeDealer?.credit_limit || 0)) ? 'bg-rose-50 border-rose-200' : 'bg-amber-50 border-amber-100'">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold"
                                  :class="(Number(activeDealer?.current_debt || 0) > Number(activeDealer?.credit_limit || 0)) ? 'text-rose-800' : 'text-amber-800'">
                                Dư Nợ Hiện Tại
                            </span>
                            <span x-show="Number(activeDealer?.current_debt || 0) > Number(activeDealer?.credit_limit || 0)"
                                  class="px-1.5 py-0.2 rounded text-[8px] font-black bg-rose-600 text-white uppercase">Vượt trần</span>
                        </div>
                        <p class="text-lg font-bold font-mono mt-0.5"
                           :class="(Number(activeDealer?.current_debt || 0) > Number(activeDealer?.credit_limit || 0)) ? 'text-rose-700' : 'text-slate-900'"
                           x-text="formatNumber(activeDealer?.current_debt) + ' đ'"></p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                        <span class="text-[11px] font-semibold text-emerald-800">Còn Được Phép Nợ</span>
                        <p class="text-lg font-bold font-mono text-emerald-900 mt-0.5" x-text="formatNumber(activeDealer?.remainingCredit) + ' đ'"></p>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <span class="text-[11px] font-semibold text-indigo-800">Doanh Thu Mua Hàng</span>
                        <p class="text-lg font-bold font-mono text-indigo-900 mt-0.5" x-text="formatNumber(activeDealer?.totalOrderAmount) + ' đ'"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-bold">
                    <button type="button" @click="activeTab = 'orders'"
                            class="pb-2.5 px-3 border-b-2 transition-colors flex items-center gap-1.5"
                            :class="activeTab === 'orders' ? 'border-sky-600 text-sky-700' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Đơn Hàng Ghi Nợ (<span x-text="orders.length"></span>)</span>
                    </button>
                    <button type="button" @click="activeTab = 'transactions'"
                            class="pb-2.5 px-3 border-b-2 transition-colors flex items-center gap-1.5"
                            :class="activeTab === 'transactions' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Lịch Sử Phiếu Thu (<span x-text="transactions.length"></span>)</span>
                    </button>
                </div>

                <!-- Tab Orders List -->
                <div x-show="activeTab === 'orders'" class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[10.5px] border-b border-slate-300">
                                    <th class="py-2.5 px-3 w-8 text-center">STT</th>
                                    <th class="py-2.5 px-3 min-w-[100px]">Mã Đơn</th>
                                    <th class="py-2.5 px-3 min-w-[90px]">Ngày Đặt</th>
                                    <th class="py-2.5 px-3 min-w-[120px]">Kho Xuất</th>
                                    <th class="py-2.5 px-3 text-right min-w-[110px]">Tổng Tiền</th>
                                    <th class="py-2.5 px-3 min-w-[110px]">Phương Thức</th>
                                    <th class="py-2.5 px-3 min-w-[110px]">Trạng Thái Thanh Toán</th>
                                    <th class="py-2.5 px-3 min-w-[100px]">Tiến Trình</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                <template x-for="(o, idx) in orders" :key="o.id">
                                    <tr class="hover:bg-sky-50/50 transition-colors">
                                        <td class="py-2 px-3 text-center text-slate-400" x-text="idx + 1"></td>
                                        <td class="py-2 px-3 font-mono font-bold text-sky-800">
                                            <a :href="'/orders/' + o.id + '/edit'" class="hover:underline" x-text="o.order_code"></a>
                                        </td>
                                        <td class="py-2 px-3 text-slate-600" x-text="formatDate(o.created_at)"></td>
                                        <td class="py-2 px-3 text-slate-700 truncate max-w-[130px]" x-text="o.warehouse || 'Kho Tổng'"></td>
                                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900" x-text="formatNumber(o.total_amount) + ' đ'"></td>
                                        <td class="py-2 px-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                                <span x-text="o.payment_method || 'Ghi nợ đại lý'"></span>
                                            </span>
                                        </td>
                                        <td class="py-2 px-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10.5px] font-black border shadow-2xs"
                                                  :class="['Đã thanh toán', 'Đã quyết toán'].includes(o.payment_status) ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-800 border-amber-300'">
                                                <span x-text="o.payment_status || 'Ghi nợ đại lý'"></span>
                                            </span>
                                        </td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700" x-text="o.status"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="orders.length === 0">
                                    <td colspan="8" class="py-6 text-center text-slate-400">Khách hàng chưa có đơn hàng nào</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Transactions List -->
                <div x-show="activeTab === 'transactions'" class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[10.5px] border-b border-slate-300">
                                    <th class="py-2.5 px-3 w-8 text-center">STT</th>
                                    <th class="py-2.5 px-3 min-w-[110px]">Mã Phiếu Thu</th>
                                    <th class="py-2.5 px-3 min-w-[95px]">Ngày Thu</th>
                                    <th class="py-2.5 px-3 min-w-[130px]">Loại Giao Dịch</th>
                                    <th class="py-2.5 px-3 text-right min-w-[110px]">Số Tiền Thu</th>
                                    <th class="py-2.5 px-3 min-w-[200px]">Nội Dung / Diễn Giải</th>
                                    <th class="py-2.5 px-3 text-center min-w-[100px]">Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                <template x-for="(t, idx) in transactions" :key="t.id">
                                    <tr class="hover:bg-emerald-50/40 transition-colors">
                                        <td class="py-2 px-3 text-center text-slate-400" x-text="idx + 1"></td>
                                        <td class="py-2 px-3 font-mono font-bold text-emerald-700" x-text="t.code || ('PT-' + t.id)"></td>
                                        <td class="py-2 px-3 text-slate-600" x-text="formatDate(t.date || t.created_at)"></td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800" x-text="t.category || 'Thu tiền hàng'"></span>
                                        </td>
                                        <td class="py-2 px-3 text-right font-mono font-black text-emerald-700" x-text="'+' + formatNumber(t.amount) + ' đ'"></td>
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
