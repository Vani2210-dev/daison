<!-- Component Sub-Section Navigation Tabs cho Phân Hệ Phân Phối Hàng Tiêu Dùng Đại Sơn -->
<div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto text-xs">
    <!-- 1. Tồn kho -->
    <a href="{{ url('/cat_products') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap {{ request()->is('cat_products*') ? 'bg-[#1b4a8c] text-white shadow-md shadow-blue-900/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
        <i class="fa-solid fa-boxes-stacked text-sm"></i>
        <span>Danh sách tồn kho</span>
    </a>

    <!-- 2. Phiếu Nhập kho (Mới) -->
    <a href="{{ route('inventory.receipts') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap {{ request()->is('inventory/receipts*') || request()->is('warehouse/receipts*') ? 'bg-[#1b4a8c] text-white shadow-md shadow-blue-900/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
        <i class="fa-solid fa-arrow-down-left-across-segments text-sm {{ request()->is('inventory/receipts*') ? 'text-emerald-300' : 'text-emerald-600' }}"></i>
        <span>Phiếu Nhập kho</span>
        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ request()->is('inventory/receipts*') ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">PNK</span>
    </a>

    <!-- 3. Phiếu Xuất kho (Mới) -->
    <a href="{{ route('inventory.issues') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap {{ request()->is('inventory/issues*') || request()->is('warehouse/issues*') ? 'bg-[#1b4a8c] text-white shadow-md shadow-blue-900/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
        <i class="fa-solid fa-arrow-up-right-from-square text-sm {{ request()->is('inventory/issues*') ? 'text-amber-300' : 'text-amber-600' }}"></i>
        <span>Phiếu Xuất kho</span>
        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ request()->is('inventory/issues*') ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">PXK</span>
    </a>

    <!-- 4. Soạn đơn & Bốc hàng xuất bán -->
    <a href="{{ url('/inventory/picking') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap {{ request()->is('inventory/picking*') || request()->is('picking*') ? 'bg-[#1b4a8c] text-white shadow-md shadow-blue-900/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
        <i class="fa-solid fa-boxes-packing text-sm {{ request()->is('inventory/picking*') ? 'text-purple-300' : 'text-purple-600' }}"></i>
        <span>Bốc hàng & Kiểm đơn bán</span>
    </a>

    <!-- 5. Kho hàng ôm nhân viên (Nhập hoàn & Xuất ôm) -->
    <a href="{{ url('/inventory/import') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap {{ request()->is('inventory/import*') || request()->is('returns*') ? 'bg-[#1b4a8c] text-white shadow-md shadow-blue-900/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
        <i class="fa-solid fa-boxes-packing text-sm {{ request()->is('inventory/import*') ? 'text-amber-300' : 'text-amber-600' }}"></i>
        <span>Kho hàng ôm nhân viên</span>
        @if(($pendingReturnBadge ?? 0) > 0)
            <span class="px-2 py-0.5 bg-rose-500 text-white rounded-full text-[10px] font-bold animate-pulse">{{ $pendingReturnBadge }}</span>
        @endif
    </a>

    <!-- 6. Đơn xuất kho tổng -->
    <a href="{{ url('/orders') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black transition-all cursor-pointer whitespace-nowrap bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200 ml-auto">
        <i class="fa-solid fa-file-invoice text-sm"></i>
        <span>Đơn bán hàng</span>
    </a>
</div>
