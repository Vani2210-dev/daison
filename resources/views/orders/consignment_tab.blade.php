@php
    $currentUser = auth()->user();

    $initialStaff = \App\Models\User::where('status', 'Đang làm việc')
        ->orWhereNull('status')
        ->orderBy('name')
        ->get(['id', 'code', 'name', 'phone', 'current_debt', 'department'])
        ->map(fn($u) => [
            'id' => $u->id,
            'code' => $u->code,
            'name' => $u->name,
            'phone' => $u->phone,
            'current_debt' => (float)($u->current_debt ?? 0),
            'department' => $u->department,
        ]);

    $initialCurrentUser = $currentUser ? [
        'id'           => $currentUser->id,
        'code'         => $currentUser->code ?? 'NV',
        'name'         => $currentUser->name,
        'phone'        => $currentUser->phone ?? '',
        'current_debt' => (float)($currentUser->current_debt ?? 0),
        'department'   => $currentUser->department ?? '',
    ] : ($initialStaff->first() ?? null);

    $initialWarehouses = \App\Models\Warehouse::where('is_active', true)
        ->where('type', '!=', 'Kho xe lưu động')
        ->where('code', 'not like', 'KHO-XE-%')
        ->orderBy('name')
        ->get(['id', 'code', 'name', 'type', 'address']);

    $initialProducts = \App\Models\Product::where('is_active', true)
        ->orderBy('name')
        ->get()
        ->map(function ($p) {
            $costPrice = (float)($p->cost_price ?? $p->latest_cost_price ?? 0);
            $tiers = is_array($p->price_tiers) ? $p->price_tiers : json_decode($p->price_tiers, true);

            $consignmentPrice = $costPrice;
            $retailPrice = $costPrice;

            if (!empty($tiers) && is_array($tiers)) {
                if (isset($tiers[0]['price']) && (float)$tiers[0]['price'] > 0) {
                    $consignmentPrice = (float)$tiers[0]['price'];
                }
                $lastTier = end($tiers);
                if (isset($lastTier['price']) && (float)$lastTier['price'] > 0) {
                    $retailPrice = (float)$lastTier['price'];
                }
            }

            return [
                'id'             => $p->id,
                'sku'            => $p->sku,
                'name'           => $p->name,
                'unit'           => $p->unit ?? 'cái',
                'cost_price'     => $costPrice,
                'price'          => $consignmentPrice,
                'sell_price'     => $retailPrice,
                'stock_quantity' => (float)($p->stock_quantity ?? 0),
            ];
        });
@endphp

<div x-data="consignmentOrdersApp()" x-init="init()" class="space-y-4">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                <i class="fa-solid fa-boxes-stacked text-base"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Đơn Hàng Ôm & Ký Gửi</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold border border-amber-200">Bán hàng & Công nợ NV</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Nhân viên nhận hàng ôm từ kho, cộng vào công nợ cá nhân và phân phối lẻ cho các khách hàng</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/inventory/import?tab=stocks') }}" class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-[#1b4a8c] border border-blue-200 text-xs font-black shadow-2xs transition-all cursor-pointer" title="Xem chi tiết tồn kho hàng ôm thực tế của nhân viên">
                <i class="fa-solid fa-boxes-stacked text-xs"></i>
                <span>Xem Kho Hàng Ôm Thực Tế</span>
            </a>
            <a href="{{ route('orders.consignments.create') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shadow-md shadow-amber-600/20 transition-all cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Lập đơn ôm hàng mới</span>
            </a>
            <button type="button" @click="fetchOrders()" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors cursor-pointer" title="Làm mới">
                <i class="fa-solid fa-rotate-right text-xs" :class="{'fa-spin': loading}"></i>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tổng đơn hàng ôm</div>
            <div class="text-lg sm:text-xl font-black text-slate-900 mt-1" x-text="stats.totalOrders || 0">0</div>
            <div class="text-[11px] text-slate-500 mt-0.5" x-text="formatMoney(stats.totalAmount || 0)">0 đ</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Chờ kế toán duyệt</div>
            <div class="text-lg sm:text-xl font-black text-amber-600 mt-1" x-text="stats.pendingCount || 0">0</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Cần duyệt xuất kho & nợ</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Đang ôm / Chưa bán hết</div>
            <div class="text-lg sm:text-xl font-black text-sky-600 mt-1" x-text="stats.inProgressCount || 0">0</div>
            <div class="text-[11px] text-sky-700 mt-0.5 font-bold" x-text="formatMoney(stats.remainingAmount || 0)">0 đ</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Doanh thu bán lẻ đã ra</div>
            <div class="text-lg sm:text-xl font-black text-emerald-600 mt-1" x-text="formatMoney(stats.retailSoldAmount || 0)">0 đ</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Đã xuất cho khách lẻ</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl p-3 border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-1 min-w-[240px]">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="search" @input.debounce.400ms="fetchOrders()" placeholder="Tìm theo mã đơn ôm, tên nhân viên..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
            </div>
            <select x-model="selectedStatus" @change="fetchOrders()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold bg-white text-slate-700 focus:border-amber-500 outline-none">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ duyệt</option>
                <option value="in_progress">Đang ôm hàng</option>
                <option value="completed">Đã hoàn tất</option>
                <option value="cancelled">Đã hủy / Từ chối</option>
            </select>
        </div>

        <div class="text-xs font-bold text-slate-500">
            Hiển thị <span class="text-slate-900" x-text="orders.length"></span> đơn
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-black uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-3">Mã đơn ôm</th>
                        <th class="py-3 px-3">Nhân viên ôm hàng</th>
                        <th class="py-3 px-3">Kho xuất</th>
                        <th class="py-3 px-3 text-right">Tổng giá trị</th>
                        <th class="py-3 px-3 text-right">Còn lại chưa bán</th>
                        <th class="py-3 px-3 text-center">Tiến độ bán</th>
                        <th class="py-3 px-3 text-center">Trạng thái</th>
                        <th class="py-3 px-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-spinner fa-spin text-xl text-amber-500"></i>
                                <div class="mt-2 font-medium">Đang tải danh sách đơn hàng ôm...</div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && orders.length === 0">
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-boxes-stacked text-3xl text-slate-300 mb-2"></i>
                                <div class="font-bold text-slate-600">Chưa có đơn hàng ôm nào</div>
                                <div class="text-[11px] mt-1 text-slate-400">Bấm "Lập đơn ôm hàng mới" để nhân viên lên đơn ôm từ kho</div>
                            </td>
                        </tr>
                    </template>

                    <template x-for="order in orders" :key="order.id">
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-3 font-mono font-bold text-amber-600 cursor-pointer" @click="viewDetail(order)">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-box text-slate-400 text-[10px]"></i>
                                    <span x-text="order.code"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-sans mt-0.5" x-text="formatDate(order.created_at)"></div>
                            </td>

                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900" x-text="order.staff_name || (order.staff ? order.staff.name : '---')"></div>
                                <div class="text-[10px] text-slate-400">
                                    Nợ hiện tại: <span class="font-semibold text-rose-600" x-text="formatMoney(order.staff?.current_debt || 0)"></span>
                                </div>
                            </td>

                            <td class="py-3 px-3 text-slate-700">
                                <span class="px-2 py-0.5 bg-slate-100 rounded-md font-medium text-[11px]" x-text="order.warehouse_name || 'Kho chính'"></span>
                            </td>

                            <td class="py-3 px-3 text-right font-black text-slate-900" x-text="formatMoney(order.total_amount)"></td>

                            <td class="py-3 px-3 text-right font-black text-amber-600" x-text="formatMoney(order.remaining_amount)"></td>

                            <td class="py-3 px-3 text-center">
                                <div class="w-24 mx-auto bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-2 rounded-full transition-all" :style="'width: ' + calcProgress(order) + '%'"></div>
                                </div>
                                <div class="text-[10px] font-bold text-slate-500 mt-1" x-text="calcProgress(order) + '% đã bán'"></div>
                            </td>

                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-amber-100 text-amber-800 border border-amber-200': order.status === 'pending',
                                        'bg-blue-100 text-blue-800 border border-blue-200': order.status === 'approved',
                                        'bg-sky-100 text-sky-800 border border-sky-200': order.status === 'in_progress',
                                        'bg-emerald-100 text-emerald-800 border border-emerald-200': order.status === 'completed' || order.status === 'settled',
                                        'bg-rose-100 text-rose-800 border border-rose-200': order.status === 'cancelled'
                                    }">
                                    <span x-text="getStatusLabel(order.status)"></span>
                                </span>
                            </td>

                            <td class="py-3 px-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Xem chi tiết -->
                                    <button type="button" @click="viewDetail(order)" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 cursor-pointer" title="Xem chi tiết đơn">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    <!-- Lên đơn bán lẻ (Chỉ khi in_progress) -->
                                    <template x-if="order.status === 'in_progress'">
                                        <button type="button" @click="openRetailModal(order)" class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs cursor-pointer" title="Lên đơn bán lẻ từ đơn ôm">
                                            <i class="fa-solid fa-cart-plus text-[10px]"></i>
                                            <span>Bán lẻ</span>
                                        </button>
                                    </template>

                                    <!-- Duyệt đơn (Chỉ khi pending và user có quyền kế toán) -->
                                    <template x-if="order.status === 'pending' && isAccountant">
                                        <button type="button" @click="approveOrder(order)" class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-[11px] shadow-xs cursor-pointer" title="Kế toán duyệt đơn để chuyển sang bốc hàng">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                            <span>Duyệt đơn</span>
                                        </button>
                                    </template>

                                    <!-- Từ chối đơn (Chỉ khi pending và user có quyền kế toán) -->
                                    <template x-if="order.status === 'pending' && isAccountant">
                                        <button type="button" @click="rejectOrder(order)" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 cursor-pointer" title="Từ chối đơn">
                                            <i class="fa-solid fa-xmark text-xs"></i>
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

    <!-- MODAL 1: TẠO ĐƠN HÀNG ÔM MỚI -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;" @keydown.escape.window="showCreateModal = false">
        <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center">
                        <i class="fa-solid fa-boxes-stacked text-xs"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Lập Đơn Hàng Ôm Mới</h3>
                        <p class="text-[11px] text-slate-400">Sau khi duyệt, đơn hàng sẽ trừ tồn kho xuất và cộng vào công nợ của nhân viên</p>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">Nhân viên nhận hàng ôm <span class="text-rose-500">*</span></label>
                        <button type="button" @click="toggleStaffSelection()" class="text-[11px] font-bold text-sky-600 hover:text-sky-700 cursor-pointer">
                            <span x-show="!showSelectOtherStaff">Đổi nhân viên</span>
                            <span x-show="showSelectOtherStaff">Dùng tài khoản của tôi</span>
                        </button>
                    </div>

                    <!-- Hiển thị tài khoản nhân viên đang đăng nhập (Mặc định) -->
                    <div x-show="!showSelectOtherStaff" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                            <span class="font-bold text-slate-900 truncate" x-text="selectedStaffDisplay?.name || currentUser?.name || '---'"></span>
                            <span class="text-slate-400 font-medium" x-show="selectedStaffDisplay?.code || currentUser?.code" x-text="'(' + (selectedStaffDisplay?.code || currentUser?.code) + ')'"></span>
                        </div>
                    </div>

                    <!-- Dropdown chọn nhân viên khác (khi bấm Đổi nhân viên) -->
                    <div x-show="showSelectOtherStaff" style="display: none;">
                        <select x-model="createForm.staff_id" @change="onStaffChanged()" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-semibold outline-none focus:border-amber-500 bg-white">
                            <option value="">-- Chọn nhân viên --</option>
                            <template x-for="u in usersList" :key="u.id">
                                <option :value="u.id" x-text="u.name + ' (' + (u.code || 'NV') + (u.phone ? ' - ' + u.phone : '') + ')'"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kho xuất hàng <span class="text-rose-500">*</span></label>
                    <select x-model="createForm.warehouse_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-semibold outline-none focus:border-amber-500 bg-white">
                        <option value="">-- Chọn kho xuất --</option>
                        <template x-for="wh in warehousesList" :key="wh.id">
                            <option :value="wh.id" x-text="wh.name + (wh.type ? ' (' + wh.type + ')' : '')"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1 text-xs">Ghi chú đơn ôm</label>
                <input type="text" x-model="createForm.notes" placeholder="VD: Ôm hàng bán sự kiện tuần này..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold outline-none focus:border-amber-500">
            </div>

            <!-- Bảng chọn mặt hàng ôm -->
            <div class="border border-slate-200 rounded-2xl p-3 bg-slate-50/50 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800 text-xs">Danh sách sản phẩm ôm</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Gõ tên hoặc mã sản phẩm để tìm kiếm, dùng phím &uarr; &darr; &bull; Enter để chọn nhanh</p>
                    </div>
                    <button type="button" @click="addCreateItemRow()" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs cursor-pointer transition-colors shadow-2xs">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Thêm sản phẩm</span>
                    </button>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(item, index) in createForm.items" :key="index">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 flex flex-wrap sm:flex-nowrap items-start sm:items-center gap-2 text-xs relative">
                            <!-- Ô nhập tìm kiếm sản phẩm với Autocomplete Dropdown -->
                            <div class="flex-1 min-w-[220px] relative" @click.outside="if (activeProductRowDropdown === index) activeProductRowDropdown = null">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                    <input type="text"
                                           :id="'item-search-input-' + index"
                                           x-model="item.searchQuery"
                                           @focus="activeProductRowDropdown = index; highlightedProductIndex = 0"
                                           @input="activeProductRowDropdown = index; highlightedProductIndex = 0; onSearchInput(item)"
                                           @keydown.down.prevent="
                                               activeProductRowDropdown = index;
                                               const prods = getFilteredProducts(item.searchQuery, index);
                                               if (prods.length > 0) {
                                                   highlightedProductIndex = Math.min(highlightedProductIndex + 1, prods.length - 1);
                                                   scrollProductIntoView(index, highlightedProductIndex);
                                               }
                                           "
                                           @keydown.up.prevent="
                                               const prods = getFilteredProducts(item.searchQuery, index);
                                               if (prods.length > 0) {
                                                   highlightedProductIndex = Math.max(highlightedProductIndex - 1, 0);
                                                   scrollProductIntoView(index, highlightedProductIndex);
                                               }
                                           "
                                           @keydown.enter.prevent="
                                               if (activeProductRowDropdown === index) {
                                                   const prods = getFilteredProducts(item.searchQuery, index);
                                                   if (prods.length > 0 && prods[highlightedProductIndex]) {
                                                       selectProductForItem(item, prods[highlightedProductIndex], index);
                                                   }
                                               }
                                           "
                                           @keydown.escape.prevent="activeProductRowDropdown = null"
                                           placeholder="-- Nhập mã hoặc tên sản phẩm để tìm --"
                                           autocomplete="off"
                                           spellcheck="false"
                                           class="w-full pl-8 pr-7 py-1.5 rounded-lg border text-xs font-semibold outline-none transition-colors"
                                           :class="item.product_id ? 'border-amber-400 bg-amber-50/30 text-slate-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500' : 'border-slate-300 bg-white text-slate-800 placeholder-slate-400 focus:border-amber-500 focus:ring-1 focus:ring-amber-500'">

                                    <!-- Nút xóa nhanh để chọn lại -->
                                    <button type="button" 
                                            x-show="item.searchQuery || item.product_id" 
                                            @click.stop="clearProductForItem(item, index)" 
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-0.5 rounded cursor-pointer transition-colors"
                                            title="Xóa để chọn lại sản phẩm khác">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>

                                <!-- Thông tin tóm tắt sản phẩm đã chọn bên dưới ô nhập -->
                                <div x-show="item.product_id" class="text-[10px] text-slate-500 mt-1 flex flex-wrap items-center gap-2 pl-1">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fa-solid fa-boxes-stacked text-[9px] text-slate-400"></i>
                                        Tồn kho: <strong :class="Number(item.stock_quantity) > 0 ? 'text-emerald-600' : 'text-rose-500'" x-text="(item.stock_quantity || 0) + ' ' + (item.unit || '')"></strong>
                                    </span>
                                    <span>&bull;</span>
                                    <span>ĐVT: <strong class="text-slate-700" x-text="item.unit || 'cái'"></strong></span>
                                </div>

                                <!-- Dropdown danh sách gợi ý sản phẩm -->
                                <div x-show="activeProductRowDropdown === index"
                                     x-cloak
                                     class="absolute left-0 top-full mt-1.5 w-full min-w-[320px] sm:min-w-[460px] max-w-[95vw] z-50 bg-white rounded-2xl shadow-2xl border border-slate-200 max-h-64 overflow-y-auto text-xs py-1"
                                     style="display: none;">
                                    <div class="px-3 py-1.5 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between sticky top-0 z-10">
                                        <span>Gợi ý sản phẩm (<span x-text="getFilteredProducts(item.searchQuery, index).length"></span>)</span>
                                        <span class="text-amber-600 font-medium lowercase">dùng phím &uarr; &darr; &bull; Enter chọn</span>
                                    </div>

                                    <template x-for="(p, pIdx) in getFilteredProducts(item.searchQuery, index)" :key="p.id">
                                        <div :id="'prod-opt-' + index + '-' + pIdx"
                                             @mousedown.prevent="selectProductForItem(item, p, index)"
                                             @mouseenter="highlightedProductIndex = pIdx"
                                             class="px-3 py-2 cursor-pointer flex items-center justify-between gap-2.5 transition-colors border-b border-slate-50 last:border-0"
                                             :class="highlightedProductIndex === pIdx ? 'bg-amber-100 text-amber-950 font-bold border-l-4 border-amber-600 pl-2' : 'hover:bg-slate-50 text-slate-800'">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold truncate text-slate-900 flex items-center gap-1.5">
                                                    <span x-show="p.sku" class="px-1.5 py-0.5 rounded bg-slate-100 text-amber-800 font-mono text-[10px] font-extrabold" x-text="p.sku"></span>
                                                    <span x-text="p.name"></span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                    <span class="font-medium" x-text="'ĐVT: ' + (p.unit || 'cái')"></span>
                                                    <span>&bull;</span>
                                                    <span :class="Number(p.stock_quantity) > 0 ? 'text-emerald-600 font-semibold' : 'text-rose-500 font-semibold'" 
                                                          x-text="'Tồn: ' + (p.stock_quantity || 0) + ' ' + (p.unit || '')"></span>
                                                </div>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                <div class="font-bold text-amber-600 text-xs" x-text="'Giá: ' + formatMoney(p.price)"></div>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="getFilteredProducts(item.searchQuery, index).length === 0" class="px-4 py-6 text-center text-slate-400">
                                        <i class="fa-solid fa-magnifying-glass text-xl text-slate-300 mb-1.5"></i>
                                        <div class="font-bold text-slate-600 text-xs">Không tìm thấy sản phẩm nào</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">Thử tìm bằng từ khóa hoặc mã SKU khác</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cột số lượng -->
                            <div class="w-24">
                                <label class="block text-[10px] text-slate-400 mb-0.5 sm:hidden">Số lượng</label>
                                <input type="number"
                                       :id="'item-qty-input-' + index"
                                       x-model.number="item.quantity"
                                       min="1"
                                       placeholder="SL"
                                       @input="calcCreateItemTotal(item)"
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 font-bold text-center outline-none focus:border-amber-500 text-xs">
                            </div>

                            <!-- Cột giá bán -->
                            <div class="w-28">
                                <label class="block text-[10px] text-slate-400 mb-0.5 sm:hidden">Giá bán</label>
                                <input type="number"
                                       x-model.number="item.unit_price"
                                       placeholder="Giá bán"
                                       @input="calcCreateItemTotal(item)"
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 font-bold text-right outline-none focus:border-amber-500 text-xs">
                            </div>

                            <!-- Thành tiền -->
                            <div class="w-28 text-right font-black text-slate-900 pt-1 sm:pt-0" x-text="formatMoney((item.quantity || 0) * (item.unit_price || 0))"></div>

                            <!-- Nút xóa dòng -->
                            <button type="button" @click="removeCreateItemRow(index)" class="p-1.5 text-rose-500 hover:text-rose-700 cursor-pointer" title="Xóa dòng">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600">Tổng tiền đơn ôm:</span>
                    <span class="text-base font-black text-amber-600" x-text="formatMoney(calcCreateTotal())"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">Hủy</button>
                <button type="button" @click="submitCreateOrder()" :disabled="submitting" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs shadow-md shadow-amber-600/20 cursor-pointer disabled:opacity-50">
                    <span x-show="!submitting">Xác nhận tạo đơn ôm</span>
                    <span x-show="submitting"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang lưu...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: LÊN ĐƠN BÁN LẺ TỪ ĐƠN ÔM -->
    <div x-show="showRetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;" @keydown.escape.window="showRetailModal = false">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                        <i class="fa-solid fa-cart-plus text-xs"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Lên Đơn Bán Lẻ Từ Hàng Ôm</h3>
                        <p class="text-[11px] text-slate-400">Xuất bán lẻ cho khách hàng, trừ số lượng tồn của đơn ôm <strong x-text="activeOrder?.code"></strong></p>
                    </div>
                </div>
                <button type="button" @click="showRetailModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tên khách hàng / Đại lý lẻ <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="retailForm.customer_name" placeholder="Nguyễn Văn A..." class="w-full px-3 py-2 rounded-xl border border-slate-200 font-semibold outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Số điện thoại</label>
                    <input type="text" x-model="retailForm.customer_phone" placeholder="0987..." class="w-full px-3 py-2 rounded-xl border border-slate-200 font-semibold outline-none focus:border-emerald-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Địa chỉ giao hàng</label>
                    <input type="text" x-model="retailForm.customer_address" placeholder="Địa chỉ giao..." class="w-full px-3 py-2 rounded-xl border border-slate-200 font-semibold outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Danh sách sản phẩm được phép bán lẻ từ lô ôm -->
            <div class="border border-slate-200 rounded-2xl p-3 bg-slate-50/50 space-y-3">
                <span class="font-bold text-slate-800 text-xs">Chọn số lượng xuất bán từ lô ôm</span>
                <div class="space-y-2">
                    <template x-for="(item, index) in retailForm.items" :key="index">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 flex items-center justify-between gap-3 text-xs">
                            <div class="flex-1">
                                <div class="font-bold text-slate-900" x-text="item.name"></div>
                                <div class="text-[10px] text-slate-400">
                                    Đang ôm còn: <span class="font-bold text-amber-600" x-text="item.max_quantity"></span> <span x-text="item.unit"></span>
                                </div>
                            </div>

                            <div class="w-24">
                                <label class="block text-[10px] text-slate-500 mb-0.5">SL bán lẻ</label>
                                <input type="number" x-model.number="item.quantity" min="0" :max="item.max_quantity" class="w-full px-2 py-1 rounded-lg border border-slate-200 font-bold text-center outline-none focus:border-emerald-500">
                            </div>

                            <div class="w-28">
                                <label class="block text-[10px] text-slate-500 mb-0.5">Giá bán lẻ</label>
                                <input type="number" x-model.number="item.unit_price" class="w-full px-2 py-1 rounded-lg border border-slate-200 font-bold text-right outline-none focus:border-emerald-500">
                            </div>

                            <div class="w-28 text-right font-black text-emerald-600 pt-3" x-text="formatMoney((item.quantity || 0) * (item.unit_price || 0))"></div>
                        </div>
                    </template>
                </div>

                <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600">Tổng thu đơn bán lẻ:</span>
                    <span class="text-base font-black text-emerald-600" x-text="formatMoney(calcRetailTotal())"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showRetailModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">Hủy</button>
                <button type="button" @click="submitRetailOrder()" :disabled="submitting" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/20 cursor-pointer disabled:opacity-50">
                    <span x-show="!submitting">Xác nhận xuất bán lẻ</span>
                    <span x-show="submitting"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang lưu...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: CHI TIẾT ĐƠN HÀNG ÔM -->
    <div x-show="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;" @keydown.escape.window="showDetailModal = false">
        <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto" x-show="activeOrder">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center">
                        <i class="fa-solid fa-file-invoice text-xs"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Chi Tiết Đơn Hàng Ôm <span class="text-amber-600" x-text="activeOrder?.code"></span></h3>
                        <p class="text-[11px] text-slate-400" x-text="'Tạo ngày: ' + formatDate(activeOrder?.created_at)"></p>
                    </div>
                </div>
                <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Thông tin tóm tắt -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-2xl text-xs">
                <div>
                    <div class="text-slate-400 text-[10px] uppercase font-bold">Nhân viên ôm</div>
                    <div class="font-black text-slate-900 mt-0.5" x-text="activeOrder?.staff_name || activeOrder?.staff?.name"></div>
                </div>
                <div>
                    <div class="text-slate-400 text-[10px] uppercase font-bold">Kho xuất</div>
                    <div class="font-black text-slate-900 mt-0.5" x-text="activeOrder?.warehouse_name || 'Kho chính'"></div>
                </div>
                <div>
                    <div class="text-slate-400 text-[10px] uppercase font-bold">Trạng thái</div>
                    <div class="font-black mt-0.5" x-text="getStatusLabel(activeOrder?.status)"></div>
                </div>
                <div>
                    <div class="text-slate-400 text-[10px] uppercase font-bold">Tổng tiền ôm</div>
                    <div class="font-black text-amber-600 mt-0.5" x-text="formatMoney(activeOrder?.total_amount)"></div>
                </div>
            </div>

            <!-- Bảng sản phẩm trong lô ôm -->
            <div>
                <h4 class="font-bold text-slate-800 text-xs mb-2">Danh sách sản phẩm ôm</h4>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-100 text-[10px] font-bold text-slate-600 uppercase">
                            <tr>
                                <th class="p-2.5">Sản phẩm</th>
                                <th class="p-2.5 text-center">SL ôm</th>
                                <th class="p-2.5 text-center">Đã bán lẻ</th>
                                <th class="p-2.5 text-center">Còn lại</th>
                                <th class="p-2.5 text-right">Giá bán</th>
                                <th class="p-2.5 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in (activeOrder?.items || [])" :key="item.id">
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-800" x-text="item.name"></td>
                                    <td class="p-2.5 text-center font-bold" x-text="item.quantity + ' ' + (item.unit || '')"></td>
                                    <td class="p-2.5 text-center font-bold text-emerald-600" x-text="item.sold_quantity || 0"></td>
                                    <td class="p-2.5 text-center font-bold text-amber-600" x-text="item.remaining_quantity"></td>
                                    <td class="p-2.5 text-right font-medium" x-text="formatMoney(item.unit_price)"></td>
                                    <td class="p-2.5 text-right font-black text-slate-900" x-text="formatMoney(item.amount)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lịch sử các đơn bán lẻ đã xuất từ lô này -->
            <div x-show="activeOrder?.retail_orders && activeOrder.retail_orders.length > 0">
                <h4 class="font-bold text-slate-800 text-xs mb-2">Các đơn bán lẻ đã xuất từ đơn ôm này</h4>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-100 text-[10px] font-bold text-slate-600 uppercase">
                            <tr>
                                <th class="p-2">Mã đơn lẻ</th>
                                <th class="p-2">Khách hàng</th>
                                <th class="p-2">Ngày xuất</th>
                                <th class="p-2 text-right">Tổng tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="ro in (activeOrder?.retail_orders || [])" :key="ro.id">
                                <tr>
                                    <td class="p-2 font-mono font-bold text-emerald-600" x-text="ro.code"></td>
                                    <td class="p-2 font-medium" x-text="ro.customer_name"></td>
                                    <td class="p-2 text-slate-400" x-text="formatDate(ro.created_at)"></td>
                                    <td class="p-2 text-right font-bold text-slate-900" x-text="formatMoney(ro.total_amount)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end pt-2 border-t border-slate-100">
                <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function consignmentOrdersApp() {
    return {
        loading: false,
        submitting: false,
        orders: [],
        search: '',
        selectedStatus: '',
        isAccountant: {{ ($currentUser && $currentUser->isAccountant()) ? 'true' : 'false' }},
        usersList: @json($initialStaff),
        warehousesList: @json($initialWarehouses),
        productsList: @json($initialProducts),
        currentUser: @json($initialCurrentUser),
        showSelectOtherStaff: false,
        selectedStaffDisplay: @json($initialCurrentUser),
        stats: {
            totalOrders: 0,
            totalAmount: 0,
            pendingCount: 0,
            inProgressCount: 0,
            remainingAmount: 0,
            retailSoldAmount: 0
        },

        showCreateModal: false,
        showRetailModal: false,
        showDetailModal: false,
        activeOrder: null,

        activeProductRowDropdown: null,
        highlightedProductIndex: 0,

        createForm: {
            staff_id: '',
            warehouse_id: '',
            notes: '',
            items: []
        },

        retailForm: {
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            payment_method: 'Tiền mặt',
            notes: '',
            items: []
        },

        async init() {
            await Promise.all([
                this.fetchOrders(),
                this.loadMasterData()
            ]);
        },

        async loadMasterData() {
            try {
                const res = await fetch('/api/consignment-orders/master-data');
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        if (data.staff && data.staff.length > 0) this.usersList = data.staff;
                        if (data.warehouses && data.warehouses.length > 0) this.warehousesList = data.warehouses;
                        if (data.products && data.products.length > 0) this.productsList = data.products;
                    }
                }
            } catch (e) {
                console.error('Error refreshing master data:', e);
            }
        },

        async fetchOrders() {
            this.loading = true;
            try {
                let url = '/api/consignment-orders?';
                if (this.search) url += 'search=' + encodeURIComponent(this.search) + '&';
                if (this.selectedStatus) url += 'status=' + encodeURIComponent(this.selectedStatus) + '&';

                const res = await fetch(url);
                const data = await res.json();
                this.orders = data.data || (Array.isArray(data) ? data : []);
                this.calculateStats();
            } catch (e) {
                console.error('Error fetching consignment orders:', e);
            } finally {
                this.loading = false;
            }
        },

        calculateStats() {
            let totalOrders = this.orders.length;
            let totalAmount = 0;
            let pendingCount = 0;
            let inProgressCount = 0;
            let remainingAmount = 0;
            let retailSoldAmount = 0;

            this.orders.forEach(o => {
                totalAmount += Number(o.total_amount || 0);
                if (o.status === 'pending') pendingCount++;
                if (o.status === 'in_progress') {
                    inProgressCount++;
                    remainingAmount += Number(o.remaining_amount || 0);
                }
                retailSoldAmount += (Number(o.total_amount || 0) - Number(o.remaining_amount || 0));
            });

            this.stats = {
                totalOrders,
                totalAmount,
                pendingCount,
                inProgressCount,
                remainingAmount,
                retailSoldAmount: Math.max(0, retailSoldAmount)
            };
        },

        calcProgress(order) {
            const total = Number(order.total_amount || 0);
            if (total <= 0) return 0;
            const remaining = Number(order.remaining_amount || 0);
            const sold = total - remaining;
            return Math.min(100, Math.max(0, Math.round((sold / total) * 100)));
        },

        getStatusLabel(status) {
            const map = {
                'draft': 'Bản nháp',
                'pending': 'Chờ kế toán duyệt',
                'approved': 'Đã duyệt (Chờ bốc)',
                'in_progress': 'Đang ôm hàng',
                'completed': 'Hoàn tất',
                'settled': 'Đã quyết toán',
                'cancelled': 'Đã hủy'
            };
            return map[status] || status || 'Chờ duyệt';
        },

        openCreateModal() {
            const defaultWh = (this.warehousesList && this.warehousesList.length > 0) ? this.warehousesList[0].id : '';
            const myStaffId = this.currentUser ? this.currentUser.id : '';
            this.activeProductRowDropdown = null;
            this.highlightedProductIndex = 0;
            this.createForm = {
                staff_id: myStaffId,
                warehouse_id: defaultWh,
                notes: '',
                items: [
                    { product_id: '', searchQuery: '', quantity: 1, unit_price: 0, sell_price: 0, unit: 'cái', stock_quantity: 0 }
                ]
            };
            this.selectedStaffDisplay = this.currentUser;
            this.showSelectOtherStaff = false;
            this.showCreateModal = true;
            this.$nextTick(() => {
                const firstInput = document.getElementById('item-search-input-0');
                if (firstInput) firstInput.focus();
            });
        },

        toggleStaffSelection() {
            this.showSelectOtherStaff = !this.showSelectOtherStaff;
            if (!this.showSelectOtherStaff) {
                this.createForm.staff_id = this.currentUser ? this.currentUser.id : '';
                this.selectedStaffDisplay = this.currentUser;
            }
        },

        onStaffChanged() {
            const found = (this.usersList || []).find(u => u.id == this.createForm.staff_id);
            if (found) {
                this.selectedStaffDisplay = found;
            }
        },

        addCreateItemRow() {
            this.createForm.items.push({
                product_id: '',
                searchQuery: '',
                quantity: 1,
                unit_price: 0,
                sell_price: 0,
                unit: 'cái',
                stock_quantity: 0
            });
            const newIndex = this.createForm.items.length - 1;
            this.activeProductRowDropdown = newIndex;
            this.highlightedProductIndex = 0;
            this.$nextTick(() => {
                const nextInput = document.getElementById('item-search-input-' + newIndex);
                if (nextInput) nextInput.focus();
            });
        },

        removeCreateItemRow(index) {
            if (this.createForm.items.length > 1) {
                this.createForm.items.splice(index, 1);
            } else {
                this.createForm.items = [
                    { product_id: '', searchQuery: '', quantity: 1, unit_price: 0, sell_price: 0, unit: 'cái', stock_quantity: 0 }
                ];
            }
            this.activeProductRowDropdown = null;
        },

        removeAccents(str) {
            if (!str) return '';
            return str
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'D')
                .toLowerCase()
                .trim();
        },

        getFilteredProducts(query, currentIndex = -1) {
            const list = this.productsList || [];
            if (!query || !query.trim()) {
                return list.slice(0, 40);
            }

            const cleanQ = this.removeAccents(query);
            const rawQ = query.toLowerCase().trim();
            const words = cleanQ.split(/\s+/).filter(Boolean);

            return list.filter(p => {
                const sku = (p.sku || '').toLowerCase();
                const name = (p.name || '').toLowerCase();
                const cleanName = this.removeAccents(p.name || '');
                const cleanSku = this.removeAccents(p.sku || '');
                const fullClean = `${cleanSku} ${cleanName}`;

                // Khớp chuỗi trực tiếp hoặc toàn bộ từ khóa không dấu
                if (sku.includes(rawQ) || name.includes(rawQ) || cleanSku.includes(cleanQ) || cleanName.includes(cleanQ)) {
                    return true;
                }

                return words.every(w => fullClean.includes(w));
            }).slice(0, 50);
        },

        selectProductForItem(item, prod, index) {
            if (!prod) return;
            item.product_id = prod.id;
            item.searchQuery = (prod.sku ? `[${prod.sku}] ` : '') + prod.name;
            item.unit_price = Number(prod.price || prod.cost_price || 0);
            item.sell_price = Number(prod.sell_price || prod.price || 0);
            item.unit = prod.unit || 'cái';
            item.stock_quantity = prod.stock_quantity || 0;
            this.activeProductRowDropdown = null;

            this.$nextTick(() => {
                const qtyInput = document.getElementById('item-qty-input-' + index);
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.select();
                }
            });
        },

        clearProductForItem(item, index) {
            item.product_id = '';
            item.searchQuery = '';
            item.unit_price = 0;
            item.sell_price = 0;
            item.unit = 'cái';
            item.stock_quantity = 0;
            this.activeProductRowDropdown = index;
            this.highlightedProductIndex = 0;
            this.$nextTick(() => {
                const input = document.getElementById('item-search-input-' + index);
                if (input) input.focus();
            });
        },

        onSearchInput(item) {
            if (!item.searchQuery || !item.searchQuery.trim()) {
                item.product_id = '';
                item.unit_price = 0;
                item.sell_price = 0;
                item.stock_quantity = 0;
            } else if (item.product_id) {
                const curProd = (this.productsList || []).find(p => p.id == item.product_id);
                const curLabel = curProd ? ((curProd.sku ? `[${curProd.sku}] ` : '') + curProd.name) : '';
                if (item.searchQuery !== curLabel) {
                    item.product_id = '';
                }
            }
        },

        scrollProductIntoView(index, pIdx) {
            this.$nextTick(() => {
                const el = document.getElementById(`prod-opt-${index}-${pIdx}`);
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },

        onSelectProduct(item) {
            const prod = (this.productsList || []).find(p => p.id == item.product_id);
            if (prod) {
                item.searchQuery = (prod.sku ? `[${prod.sku}] ` : '') + prod.name;
                item.unit_price = Number(prod.price || prod.cost_price || 0);
                item.sell_price = Number(prod.sell_price || prod.price || 0);
                item.unit = prod.unit || 'cái';
                item.stock_quantity = prod.stock_quantity || 0;
            }
        },

        calcCreateItemTotal(item) {
            return (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
        },

        calcCreateTotal() {
            return (this.createForm.items || []).reduce((sum, it) => {
                return sum + ((Number(it.quantity) || 0) * (Number(it.unit_price) || 0));
            }, 0);
        },

        async submitCreateOrder() {
            if (!this.createForm.staff_id) {
                alert('Vui lòng chọn nhân viên nhận hàng ôm!');
                return;
            }
            if (!this.createForm.warehouse_id) {
                alert('Vui lòng chọn kho xuất hàng!');
                return;
            }

            const validItems = (this.createForm.items || []).filter(it => it.product_id && Number(it.quantity) > 0);
            if (validItems.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm và nhập số lượng hợp lệ!');
                return;
            }

            this.submitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const payload = {
                    staff_id: this.createForm.staff_id,
                    warehouse_id: this.createForm.warehouse_id,
                    notes: this.createForm.notes,
                    items: validItems.map(it => ({
                        product_id: it.product_id,
                        quantity: Number(it.quantity),
                        unit_price: Number(it.unit_price),
                        sell_price: Number(it.sell_price || it.unit_price),
                        unit: it.unit || 'cái'
                    }))
                };

                const res = await fetch('/api/consignment-orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    alert('Lập đơn hàng ôm thành công!');
                    this.showCreateModal = false;
                    await this.fetchOrders();
                } else {
                    alert(data.message || 'Lỗi khi tạo đơn hàng ôm');
                }
            } catch (e) {
                alert('Lỗi kết nối khi gửi đơn hàng ôm');
            } finally {
                this.submitting = false;
            }
        },

        async approveOrder(order) {
            if (!confirm(`Bạn có chắc muốn KẾ TOÁN DUYỆT đơn ${order.code}?\n\nSau khi duyệt, đơn sẽ xuất hiện bên màn hình Bốc hàng để bộ phận kho chuẩn bị hàng xuất.`)) {
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/consignment-orders/${order.id}/approve`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || '' 
                    }
                });
                const data = await res.json().catch(() => ({ success: false, message: 'Phản hồi không hợp lệ từ máy chủ' }));
                if (data.success) {
                    alert(data.message || 'Kế toán duyệt đơn ôm thành công!');
                    await this.fetchOrders();
                } else {
                    alert(data.message || 'Lỗi duyệt đơn');
                }
            } catch (e) {
                console.error('Approve error:', e);
                alert('Lỗi khi duyệt đơn: ' + (e?.message || 'Không thể kết nối đến máy chủ'));
            }
        },

        async rejectOrder(order) {
            const reason = prompt('Nhập lý do từ chối đơn hàng ôm:');
            if (reason === null) return;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/consignment-orders/${order.id}/reject`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || '' 
                    },
                    body: JSON.stringify({ reason })
                });
                const data = await res.json().catch(() => ({ success: false, message: 'Phản hồi không hợp lệ từ máy chủ' }));
                if (data.success) {
                    alert('Đã từ chối đơn hàng ôm!');
                    await this.fetchOrders();
                } else {
                    alert(data.message || 'Lỗi từ chối đơn');
                }
            } catch (e) {
                console.error('Reject error:', e);
                alert('Lỗi kết nối: ' + (e?.message || 'Không thể kết nối đến máy chủ'));
            }
        },

        openRetailModal(order) {
            this.activeOrder = order;
            this.retailForm = {
                customer_name: '',
                customer_phone: '',
                customer_address: '',
                payment_method: 'Tiền mặt',
                notes: '',
                items: (order.items || []).map(it => ({
                    consignment_order_item_id: it.id,
                    name: it.name,
                    unit: it.unit || 'cái',
                    max_quantity: Number(it.remaining_quantity || 0),
                    quantity: 0,
                    unit_price: Number(it.sell_price || it.unit_price || 0)
                }))
            };
            this.showRetailModal = true;
        },

        calcRetailTotal() {
            return (this.retailForm.items || []).reduce((sum, it) => {
                return sum + ((Number(it.quantity) || 0) * (Number(it.unit_price) || 0));
            }, 0);
        },

        async submitRetailOrder() {
            const hasQuantity = this.retailForm.items.some(it => Number(it.quantity) > 0);
            if (!hasQuantity) {
                alert('Vui lòng nhập số lượng bán lẻ cho ít nhất một sản phẩm!');
                return;
            }

            const validItems = this.retailForm.items
                .filter(it => Number(it.quantity) > 0)
                .map(it => ({
                    consignment_order_item_id: it.consignment_order_item_id,
                    quantity: Number(it.quantity),
                    unit_price: Number(it.unit_price)
                }));

            this.submitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/api/consignment-orders/${this.activeOrder.id}/retail-orders`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
                    body: JSON.stringify({
                        customer_name: this.retailForm.customer_name,
                        customer_phone: this.retailForm.customer_phone,
                        customer_address: this.retailForm.customer_address,
                        payment_method: this.retailForm.payment_method,
                        notes: this.retailForm.notes,
                        items: validItems
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Lên đơn bán lẻ từ hàng ôm thành công!');
                    this.showRetailModal = false;
                    await this.fetchOrders();
                } else {
                    alert(data.message || 'Lỗi khi tạo đơn bán lẻ');
                }
            } catch (e) {
                alert('Lỗi kết nối khi gửi đơn bán lẻ');
            } finally {
                this.submitting = false;
            }
        },

        async viewDetail(order) {
            try {
                const res = await fetch(`/api/consignment-orders/${order.id}`);
                const data = await res.json();
                this.activeOrder = data;
            } catch (e) {
                this.activeOrder = order;
            }
            this.showDetailModal = true;
        },

        formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0)) + ' đ';
        },

        formatDate(dateStr) {
            if (!dateStr) return '---';
            try {
                const d = new Date(dateStr);
                return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
            } catch (e) {
                return dateStr;
            }
        }
    };
}
</script>
