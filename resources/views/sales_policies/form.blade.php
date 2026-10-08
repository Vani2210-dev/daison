@extends('layouts.app')

@section('title', ($policy ? 'Chỉnh Sửa Chính Sách: ' . $policy->name : 'Thêm Mới Chính Sách Bán Hàng') . ' | ERP Đại Sơn')

@section('content')
<div class="space-y-4 w-full" x-data="salesPolicyForm()" @keydown.window="handleGlobalKeydown($event)">

    <!-- Top Action Header Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ url('/cat_sales_policies') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors shadow-2xs" title="Quay lại danh sách chính sách">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-percent"></i>
                    </span>
                    <span>{{ $policy ? 'Chỉnh Sửa Chính Sách Bán Hàng' : 'Thêm Mới Chính Sách Bán Hàng' }}</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Thiết lập chiết khấu %, tặng hàng (Mua X tặng Y), giảm giá tiền mặt, phạm vi đối tượng đại lý và thời hạn áp dụng.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ url('/cat_sales_policies') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                Hủy Bỏ
            </a>
            <button type="button" @click="submitForm()" :disabled="isSaving" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95">
                <i x-show="isSaving" class="fa-solid fa-spinner fa-spin"></i>
                <i x-show="!isSaving" class="fa-solid fa-floppy-disk"></i>
                <span x-text="isSaving ? 'Đang lưu...' : '{{ $policy ? 'Lưu Cập Nhật (F9)' : 'Xác Nhận Tạo Mới (F9)' }}'"></span>
            </button>
        </div>
    </div>

    <!-- Main Policy Form -->
    <form @submit.prevent="submitForm()" class="space-y-4">
        @csrf
        <input type="hidden" name="id" value="{{ $policy?->id }}">

        <!-- 1. THÔNG TIN ĐỊNH DANH & LOẠI HÌNH CHÍNH SÁCH -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xs">1</div>
                    <h2 class="text-sm font-extrabold text-slate-900">Thông Tin Định Danh & Loại Hình Chính Sách</h2>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-600">Trạng thái:</label>
                    <select x-model="formData.status" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold bg-white focus:outline-none focus:border-emerald-500 shadow-2xs"
                            :class="formData.status === 'Hoạt động' ? 'text-emerald-700' : 'text-slate-600'">
                        <option value="Hoạt động">🟢 Đang áp dụng (Hoạt động)</option>
                        <option value="Tạm ngưng">🔴 Tạm ngưng áp dụng</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 text-xs">
                <!-- Mã chính sách -->
                <div class="md:col-span-4">
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Mã Chính Sách <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="formData.code" 
                        required 
                        placeholder="VD: CSBH-001" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl font-mono text-xs font-bold text-sky-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 uppercase"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Mã duy nhất trong hệ thống quản trị ERP</p>
                </div>

                <!-- Tên chính sách bán hàng -->
                <div class="md:col-span-8">
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Tên Chính Sách Bán Hàng <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="formData.name" 
                        required 
                        placeholder="VD: Chiết khấu Đại lý Cấp 1 năm 2026 hoặc Mua 10 tặng 1 ống Tiền Phong" 
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Tên mô tả rõ ràng để nhân viên kinh doanh và kế toán dễ nhận diện</p>
                </div>
            </div>

            <!-- Lựa chọn hình thức ưu đãi (Card Selector) -->
            <div class="pt-2">
                <label class="block font-bold text-slate-700 mb-2">
                    Hình thức ưu đãi & Loại chính sách <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Option 1: Percentage -->
                    <label 
                        @click="setPolicyType('percentage')"
                        class="p-4 rounded-xl border-2 cursor-pointer transition-all flex flex-col justify-between"
                        :class="formData.policy_type === 'percentage' ? 'border-emerald-500 bg-emerald-50/50 shadow-xs ring-1 ring-emerald-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm"
                                     :class="formData.policy_type === 'percentage' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'">
                                    <i class="fa-solid fa-percent"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Chiết khấu %</div>
                                    <div class="text-[11px] text-slate-500">Giảm theo tỷ lệ phần trăm</div>
                                </div>
                            </div>
                            <input type="radio" name="policy_type_radio" value="percentage" x-model="formData.policy_type" @change="setPolicyType('percentage')" class="text-emerald-600 focus:ring-emerald-500 mt-1">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">Áp dụng tỷ lệ chiết khấu cố định trên đơn giá nguyên giá của sản phẩm hoặc toàn bộ đơn hàng.</p>
                    </label>

                    <!-- Option 2: Quantity (Mua X tặng Y) -->
                    <label 
                        @click="setPolicyType('quantity')"
                        class="p-4 rounded-xl border-2 cursor-pointer transition-all flex flex-col justify-between"
                        :class="formData.policy_type === 'quantity' ? 'border-sky-500 bg-sky-50/50 shadow-xs ring-1 ring-sky-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm"
                                     :class="formData.policy_type === 'quantity' ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-600'">
                                    <i class="fa-solid fa-gift"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Mua tặng hàng (Combo)</div>
                                    <div class="text-[11px] text-slate-500">Mua X tặng Y sản phẩm</div>
                                </div>
                            </div>
                            <input type="radio" name="policy_type_radio" value="quantity" x-model="formData.policy_type" @change="setPolicyType('quantity')" class="text-sky-600 focus:ring-sky-500 mt-1">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">Ví dụ mua 10 cuộn/cây tặng 1 cuộn/cây cùng loại hoặc tặng kèm sản phẩm phụ kiện khác.</p>
                    </label>

                    <!-- Option 3: Amount (Giảm tiền mặt) -->
                    <label 
                        @click="setPolicyType('amount')"
                        class="p-4 rounded-xl border-2 cursor-pointer transition-all flex flex-col justify-between"
                        :class="formData.policy_type === 'amount' ? 'border-amber-500 bg-amber-50/50 shadow-xs ring-1 ring-amber-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm"
                                     :class="formData.policy_type === 'amount' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600'">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Giảm tiền mặt (VNĐ)</div>
                                    <div class="text-[11px] text-slate-500">Trừ tiền trực tiếp trên đơn</div>
                                </div>
                            </div>
                            <input type="radio" name="policy_type_radio" value="amount" x-model="formData.policy_type" @change="setPolicyType('amount')" class="text-amber-600 focus:ring-amber-500 mt-1">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">Trừ một khoản tiền mặt cố định vào giá trị thanh toán của đơn hàng khi đạt điều kiện.</p>
                    </label>
                </div>
            </div>
        </div>

        <!-- 2. THIẾT LẬP MỨC ƯU ĐÃI & ĐIỀU KIỆN ĐƠN HÀNG -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">2</div>
                <h2 class="text-sm font-extrabold text-slate-900">Thiết Lập Mức Ưu Đãi & Giá Trị Khấu Trừ</h2>
            </div>

            <!-- Case A: Percentage -->
            <div x-show="formData.policy_type === 'percentage'" class="space-y-4">
                <!-- Chọn giảm trên đơn hay giảm trên từng sản phẩm -->
                <div>
                    <label class="block font-bold text-slate-700 mb-2">
                        Phương Thức Áp Dụng Chiết Khấu % <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label 
                            @click="formData.apply_scope = 'order'"
                            class="p-3.5 rounded-xl border-2 cursor-pointer transition-all flex items-center justify-between"
                            :class="formData.apply_scope === 'order' ? 'border-emerald-500 bg-emerald-50/50 shadow-2xs ring-1 ring-emerald-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs"
                                     :class="formData.apply_scope === 'order' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Giảm trên tổng đơn hàng</div>
                                    <div class="text-[11px] text-slate-500">Chiết khấu % trên tổng giá trị toàn bộ đơn hàng</div>
                                </div>
                            </div>
                            <input type="radio" name="apply_scope_percentage" value="order" x-model="formData.apply_scope" class="text-emerald-600 focus:ring-emerald-500">
                        </label>

                        <label 
                            @click="formData.apply_scope = 'product'"
                            class="p-3.5 rounded-xl border-2 cursor-pointer transition-all flex items-center justify-between"
                            :class="formData.apply_scope === 'product' ? 'border-emerald-500 bg-emerald-50/50 shadow-2xs ring-1 ring-emerald-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs"
                                     :class="formData.apply_scope === 'product' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Giảm trên từng sản phẩm</div>
                                    <div class="text-[11px] text-slate-500">Chiết khấu % áp dụng trực tiếp cho từng dòng sản phẩm mua</div>
                                </div>
                            </div>
                            <input type="radio" name="apply_scope_percentage" value="product" x-model="formData.apply_scope" class="text-emerald-600 focus:ring-emerald-500">
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Mức Chiết Khấu (%) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                x-model.number="formData.discount_percent" 
                                min="0" 
                                max="100" 
                                step="0.1" 
                                placeholder="VD: 5.5 hoặc 10" 
                                class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-slate-300 rounded-xl font-bold text-emerald-600 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                            >
                            <span class="absolute right-3.5 top-2.5 font-bold text-slate-400">%</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1" x-text="formData.apply_scope === 'order' ? 'Tỷ lệ chiết khấu tính trên tổng tiền đơn hàng' : 'Tỷ lệ chiết khấu trừ trực tiếp vào đơn giá từng sản phẩm'"></p>
                    </div>

                    <!-- Điều kiện: Đơn hàng (khi scope=order) hoặc Số lượng SP (khi scope=product) -->
                    <div x-show="formData.apply_scope === 'order'">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Giá Trị Đơn Hàng Tối Thiểu (VNĐ)
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                inputmode="numeric" 
                                x-money="formData.min_order_value" 
                                placeholder="VD: 10.000.000 (để 0 nếu không yêu cầu)" 
                                class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-300 rounded-xl font-bold font-mono text-slate-900 text-xs outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            >
                            <span class="absolute right-3.5 top-2.5 font-bold text-slate-400">VNĐ</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Đơn hàng phải đạt giá trị tối thiểu này mới được hưởng ưu đãi chiết khấu</p>
                    </div>
                    <div x-show="formData.apply_scope === 'product'">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Giá Trị Tối Thiểu Trên Từng Sản Phẩm (VNĐ)
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                inputmode="numeric" 
                                x-money="formData.min_order_value" 
                                placeholder="VD: 500.000 (để 0 nếu không yêu cầu)" 
                                class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-300 rounded-xl font-bold font-mono text-slate-900 text-xs outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            >
                            <span class="absolute right-3.5 top-2.5 font-bold text-slate-400">VNĐ</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Giá trị của từng sản phẩm phải đạt mức này mới được hưởng mức chiết khấu</p>
                    </div>
                </div>
            </div>

            <!-- Case B: Quantity (Mua X tặng Y) -->
            <div x-show="formData.policy_type === 'quantity'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Số Lượng Mua Tối Thiểu (X) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            inputmode="numeric" 
                            x-money="formData.buy_qty" 
                            placeholder="VD: 10" 
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl font-bold text-sky-900 text-xs outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Khách mua từ số lượng này trở lên</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Số Lượng Tặng Kèm (Y) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            inputmode="numeric" 
                            x-money="formData.free_qty" 
                            placeholder="VD: 1" 
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl font-bold text-emerald-600 text-xs outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Số lượng hàng được tặng miễn phí</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Sản Phẩm Tặng Kèm
                        </label>
                        <select x-model="formData.free_product_id" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:border-sky-500 focus:outline-none cursor-pointer">
                            <option value="">(Tặng chính sản phẩm khách mua)</option>
                            <template x-for="p in productsCatalog" :key="p.id">
                                <option :value="p.id" x-text="'[' + p.sku + '] ' + p.name + ' (' + p.unit + ')'"></option>
                            </template>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Để trống nếu tặng cùng loại sản phẩm khách mua</p>
                    </div>
                </div>
            </div>

            <!-- Case C: Amount (Giảm tiền mặt trực tiếp) -->
            <div x-show="formData.policy_type === 'amount'" class="space-y-4">
                <!-- Chọn giảm trên đơn hay giảm trên từng sản phẩm -->
                <div>
                    <label class="block font-bold text-slate-700 mb-2">
                        Phương Thức Giảm Tiền Mặt <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label 
                            @click="formData.apply_scope = 'order'"
                            class="p-3.5 rounded-xl border-2 cursor-pointer transition-all flex items-center justify-between"
                            :class="formData.apply_scope === 'order' ? 'border-amber-500 bg-amber-50/50 shadow-2xs ring-1 ring-amber-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs"
                                     :class="formData.apply_scope === 'order' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600'">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Giảm trên tổng đơn hàng</div>
                                    <div class="text-[11px] text-slate-500">Khấu trừ số tiền cố định trực tiếp vào tổng đơn hàng</div>
                                </div>
                            </div>
                            <input type="radio" name="apply_scope_amount" value="order" x-model="formData.apply_scope" class="text-amber-600 focus:ring-amber-500">
                        </label>

                        <label 
                            @click="formData.apply_scope = 'product'"
                            class="p-3.5 rounded-xl border-2 cursor-pointer transition-all flex items-center justify-between"
                            :class="formData.apply_scope === 'product' ? 'border-amber-500 bg-amber-50/50 shadow-2xs ring-1 ring-amber-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs"
                                     :class="formData.apply_scope === 'product' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600'">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Giảm trên từng sản phẩm</div>
                                    <div class="text-[11px] text-slate-500">Giảm số tiền cố định trên mỗi đơn vị sản phẩm mua</div>
                                </div>
                            </div>
                            <input type="radio" name="apply_scope_amount" value="product" x-model="formData.apply_scope" class="text-amber-600 focus:ring-amber-500">
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Số Tiền Giảm Trực Tiếp (VNĐ) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                inputmode="numeric" 
                                x-money="formData.discount_amount" 
                                placeholder="VD: 100.000" 
                                class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-300 rounded-xl font-bold font-mono text-amber-700 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                            >
                            <span class="absolute right-3.5 top-2.5 font-bold text-slate-400">VNĐ</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1" x-text="formData.apply_scope === 'order' ? 'Số tiền trừ trực tiếp vào tổng thanh toán đơn hàng' : 'Số tiền trừ vào đơn giá trên mỗi sản phẩm khách mua'"></p>
                    </div>

                    <!-- Điều kiện: Đơn hàng (khi scope=order) hoặc Số lượng SP (khi scope=product) -->
                    <div x-show="formData.apply_scope === 'order'">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Giá Trị Đơn Hàng Tối Thiểu (VNĐ)
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                inputmode="numeric" 
                                x-money="formData.min_order_value" 
                                placeholder="VD: 2.000.000 (để 0 nếu không yêu cầu)" 
                                class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-300 rounded-xl font-bold font-mono text-slate-900 text-xs outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            >
                            <span class="absolute right-3.5 top-2.5 font-bold text-slate-400">VNĐ</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Đơn hàng cần đạt tổng tiền này để được trừ tiền</p>
                    </div>
                    <div x-show="formData.apply_scope === 'product'">
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Giá Trị Tối Thiểu Trên Từng Sản Phẩm (VNĐ)
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                inputmode="numeric" 
                                x-money="formData.min_order_value" 
                                placeholder="VD: 200.000 (để 0 nếu không yêu cầu)" 
                                class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-300 rounded-xl font-bold font-mono text-slate-900 text-xs outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            >
                            <span class="absolute right-3.5 top-2.5 font-bold text-slate-400">VNĐ</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Giá trị của từng sản phẩm phải đạt mức này mới được trừ tiền</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. PHẠM VI ÁP DỤNG (ĐẠI LÝ & SẢN PHẨM) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-6">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">3</div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Phạm Vi Đối Tượng Áp Dụng (Đại Lý & Sản Phẩm)</h2>
                    <p class="text-[11px] text-slate-400">Thiết lập linh hoạt đối tượng khách hàng và danh mục mặt hàng được hưởng chính sách</p>
                </div>
            </div>

            <!-- 3.1. ĐỐI TƯỢNG ĐẠI LÝ / KHÁCH HÀNG -->
            <div class="space-y-3 text-xs border-b border-slate-100 pb-5">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <h3 class="font-bold text-slate-800 uppercase tracking-wide text-[11px]">3.1. Phân Khúc & Khách Hàng Áp Dụng</h3>
                </div>



                <!-- Danh sách khách hàng cụ thể (tùy chọn) -->
                <div class="pt-2">
                    <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-700">Chỉ định khách hàng cụ thể (nếu cần giới hạn):</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                                  :class="formData.target_customer_ids.length > 0 ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-500'">
                                Đã chọn: <span x-text="formData.target_customer_ids.length"></span> khách hàng
                                <span x-show="formData.target_customer_ids.length === 0">(Áp dụng tất cả khách theo phân khúc)</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectAllCustomers()" class="text-sky-600 hover:text-sky-800 font-semibold text-[11px] cursor-pointer">
                                Chọn tất cả
                            </button>
                            <span class="text-slate-300">&bull;</span>
                            <button type="button" @click="clearAllCustomers()" class="text-slate-500 hover:text-rose-600 font-semibold text-[11px] cursor-pointer">
                                Bỏ chọn tất cả
                            </button>
                        </div>
                    </div>

                    <!-- Search Customer Box -->
                    <div class="relative mb-2">
                        <input 
                            type="text" 
                            x-model="dealerSearch" 
                            placeholder="Tìm kiếm khách hàng theo tên, số điện thoại, cấp đại lý..." 
                            class="w-full pl-9 pr-4 py-2 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-indigo-500"
                        >
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                    </div>

                    <!-- Customer Checkbox Grid -->
                    <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-2 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-1.5 text-xs">
                        <template x-for="d in filteredDealers" :key="d.id">
                            <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white transition-colors cursor-pointer border border-transparent hover:border-slate-200">
                                <input 
                                    type="checkbox" 
                                    :value="String(d.id)" 
                                    x-model="formData.target_customer_ids" 
                                    class="rounded text-indigo-600 focus:ring-indigo-500"
                                >
                                <div class="truncate flex-1 min-w-0">
                                    <span class="font-bold text-slate-800" x-text="d.name"></span>
                                    <span class="text-[10px] text-slate-400 block truncate" x-text="(d.phone || '') + (d.tier ? ' &bull; ' + d.tier : '')"></span>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <!-- 3.2. PHẠM VI SẢN PHẨM ÁP DỤNG -->
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                        <h3 class="font-bold text-slate-800 uppercase tracking-wide text-[11px]">3.2. Phạm Vi Sản Phẩm Áp Dụng</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                              :class="productScope === 'all' ? 'bg-emerald-100 text-emerald-800' : 'bg-teal-100 text-teal-800'">
                            <i :class="productScope === 'all' ? 'fa-solid fa-boxes-stacked' : 'fa-solid fa-filter'" class="mr-1"></i>
                            <span x-text="productScope === 'all' ? 'Áp dụng cho TOÀN BỘ sản phẩm' : ('Đã chọn: ' + formData.target_product_ids.length + ' sản phẩm cụ thể')"></span>
                        </span>
                    </div>
                </div>

                <!-- Chế độ chọn sản phẩm: Toàn bộ vs Cụ thể -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label 
                        @click="productScope = 'all'"
                        class="p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center justify-between"
                        :class="productScope === 'all' ? 'border-emerald-500 bg-emerald-50/50 shadow-2xs ring-1 ring-emerald-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs"
                                 :class="productScope === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'">
                                <i class="fa-solid fa-globe"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-xs">Toàn bộ danh mục sản phẩm</div>
                                <div class="text-[11px] text-slate-500">Áp dụng cho tất cả hàng hóa trong kho</div>
                            </div>
                        </div>
                        <input type="radio" name="product_scope_radio" value="all" x-model="productScope" class="text-emerald-600 focus:ring-emerald-500">
                    </label>

                    <label 
                        @click="productScope = 'specific'"
                        class="p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center justify-between"
                        :class="productScope === 'specific' ? 'border-teal-500 bg-teal-50/50 shadow-2xs ring-1 ring-teal-200' : 'border-slate-200 hover:border-slate-300 bg-white'"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs"
                                 :class="productScope === 'specific' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'">
                                <i class="fa-solid fa-list-check"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-xs">Chỉ định sản phẩm / Ngành hàng</div>
                                <div class="text-[11px] text-slate-500">Chọn theo danh mục, thương hiệu hoặc mã SKU</div>
                            </div>
                        </div>
                        <input type="radio" name="product_scope_radio" value="specific" x-model="productScope" class="text-teal-600 focus:ring-teal-500">
                    </label>
                </div>

                <!-- Bảng chọn sản phẩm cụ thể (Chỉ hiện khi productScope === 'specific') -->
                <div x-show="productScope === 'specific'" x-cloak class="p-3.5 bg-slate-50/70 border border-slate-200 rounded-xl space-y-2.5 transition-all">
                    <!-- Filters: Category, Brand, Search Text -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <!-- Lọc Danh mục -->
                        <div>
                            <select x-model="categoryFilter" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:border-teal-500 focus:outline-none">
                                <option value="">Tất cả danh mục ngành hàng</option>
                                <template x-for="cat in categoriesCatalog" :key="cat.id">
                                    <option :value="String(cat.id)" x-text="cat.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Lọc Thương hiệu -->
                        <div>
                            <select x-model="brandFilter" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold focus:border-teal-500 focus:outline-none">
                                <option value="">Tất cả thương hiệu</option>
                                <template x-for="b in brandsCatalog" :key="b.id">
                                    <option :value="String(b.id)" x-text="b.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Ô tìm kiếm SKU / Tên -->
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="productSearch" 
                                placeholder="Tìm mã SKU hoặc tên sản phẩm..." 
                                class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs outline-none focus:border-teal-500"
                            >
                            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
                        </div>
                    </div>

                    <!-- Thanh thao tác chọn nhanh sản phẩm -->
                    <div class="flex items-center justify-between text-[11px] pt-1">
                        <div class="text-slate-500">
                            Hiển thị: <b class="text-slate-800" x-text="filteredProducts.length"></b> sản phẩm phù hợp
                            <span class="text-slate-300 mx-1">&bull;</span>
                            Đã chọn: <b class="text-teal-700 font-bold" x-text="formData.target_product_ids.length"></b> sản phẩm
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectAllFilteredProducts()" class="text-teal-600 hover:text-teal-800 font-bold cursor-pointer">
                                + Chọn tất cả theo bộ lọc
                            </button>
                            <span class="text-slate-300">&bull;</span>
                            <button type="button" @click="formData.target_product_ids = []" class="text-slate-500 hover:text-rose-600 font-semibold cursor-pointer">
                                Bỏ chọn tất cả
                            </button>
                        </div>
                    </div>

                    <!-- Product Checkbox Grid -->
                    <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-xl p-2 bg-white grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-1.5 text-xs">
                        <template x-for="p in filteredProducts" :key="p.id">
                            <label class="flex items-start gap-2 p-1.5 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer border border-transparent hover:border-slate-200"
                                   :class="formData.target_product_ids.includes(String(p.id)) ? 'bg-teal-50/60 border-teal-200' : ''">
                                <input 
                                    type="checkbox" 
                                    :value="String(p.id)" 
                                    x-model="formData.target_product_ids" 
                                    class="rounded text-teal-600 focus:ring-teal-500 mt-0.5"
                                >
                                <div class="truncate flex-1 min-w-0">
                                    <div class="font-bold text-slate-800 truncate">
                                        <span class="text-teal-600 font-mono mr-1" x-text="'[' + p.sku + ']'"></span>
                                        <span x-text="p.name"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                        <span x-text="'ĐVT: ' + (p.unit || 'cái')"></span>
                                        <span x-show="getBrandName(p.brand_id)">&bull; <span x-text="getBrandName(p.brand_id)"></span></span>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. THỜI GIAN ÁP DỤNG & ĐIỀU KIỆN GHI CHÚ -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">4</div>
                <h2 class="text-sm font-extrabold text-slate-900">Thời Gian Hiệu Lực & Ghi Chú Ràng Buộc</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Ngày bắt đầu -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Ngày Bắt Đầu Hiệu Lực <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        x-model="formData.start_date" 
                        required 
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 outline-none focus:border-sky-500"
                    >
                </div>

                <!-- Ngày kết thúc -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Ngày Kết Thúc Hiệu Lực
                    </label>
                    <input 
                        type="date" 
                        x-model="formData.end_date" 
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 outline-none focus:border-sky-500"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Để trống nếu chính sách áp dụng vô thời hạn</p>
                </div>
            </div>

            <!-- Ghi chú điều kiện ràng buộc -->
            <div>
                <label class="block font-bold text-slate-700 mb-1.5">
                    Ghi Chú / Điều Kiện Bổ Trợ
                </label>
                <textarea 
                    x-model="formData.notes" 
                    rows="3" 
                    placeholder="VD: Chỉ áp dụng khi đại lý thanh toán đúng hạn trong vòng 15 ngày kể từ ngày giao hàng..." 
                    class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs focus:border-sky-500 outline-none transition-colors"
                ></textarea>
            </div>
        </div>

        <!-- Bottom Action Buttons -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between gap-3">
            <div class="text-[11px] text-slate-400">
                <i class="fa-solid fa-circle-info text-sky-500 mr-1"></i>
                Nhấn phím <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono font-bold text-slate-700 text-[10px]">F9</kbd> để lưu nhanh chính sách bán hàng.
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ url('/cat_sales_policies') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                    Hủy Bỏ
                </a>
                <button type="submit" :disabled="isSaving" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95">
                    <i x-show="isSaving" class="fa-solid fa-spinner fa-spin"></i>
                    <i x-show="!isSaving" class="fa-solid fa-floppy-disk"></i>
                    <span x-text="isSaving ? 'Đang lưu...' : '{{ $policy ? 'Lưu Cập Nhật Chính Sách (F9)' : 'Tạo Mới Chính Sách (F9)' }}'"></span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function salesPolicyForm() {
    return {
        isSaving: false,
        dealerSearch: '',
        dealersCatalog: @json($dealers ?? []),
        categoriesCatalog: @json($categories ?? []),
        brandsCatalog: @json($brands ?? []),
        productsCatalog: @json($products ?? []),
        productScope: '{{ ($policy && $policy->target_product_ids && count($policy->target_product_ids) > 0) ? "specific" : "all" }}',
        productSearch: '',
        categoryFilter: '',
        brandFilter: '',
        selectedCustomerSegment: '{{ ($policy && $policy->target_customer_ids && count($policy->target_customer_ids) > 0) ? "Tùy chọn" : "Đại lý toàn hệ thống" }}',
        availableScopes: [
            'Đại lý toàn hệ thống',
            'Đại lý Cấp 1',
            'Đại lý Cấp 2',
            'Khách Dự Án'
        ],
        formData: {
            id: '{{ $policy ? $policy->id : "" }}',
            code: '{{ $policy ? $policy->code : ($nextCode ?? "CSBH-001") }}',
            name: '{{ $policy ? addslashes($policy->name) : "" }}',
            policy_type: '{{ $policy ? ($policy->policy_type ?: "percentage") : "percentage" }}',
            apply_scope: '{{ $policy ? ($policy->apply_scope === "product" ? "product" : "order") : "order" }}',
            status: '{{ $policy ? ($policy->status ?: "Hoạt động") : "Hoạt động" }}',
            buy_qty: {{ $policy && $policy->buy_qty !== null ? (float)$policy->buy_qty : 'null' }},
            free_qty: {{ $policy && $policy->free_qty !== null ? (float)$policy->free_qty : 'null' }},
            free_product_id: '{{ $policy && $policy->free_product_id ? $policy->free_product_id : "" }}',
            discount_percent: {{ $policy && $policy->discount_percent !== null ? (float)$policy->discount_percent : 'null' }},
            discount_amount: {{ $policy && $policy->discount_amount !== null ? (float)$policy->discount_amount : 'null' }},
            min_order_value: {{ $policy && $policy->min_order_value !== null ? (float)$policy->min_order_value : 'null' }},
            target_customer_ids: @json($policy && $policy->target_customer_ids ? array_map('strval', $policy->target_customer_ids) : []),
            target_product_ids: @json($policy && $policy->target_product_ids ? array_map('strval', $policy->target_product_ids) : []),
            start_date: '{{ $policy && $policy->start_date ? (is_string($policy->start_date) ? substr($policy->start_date, 0, 10) : $policy->start_date->format("Y-m-d")) : date("Y-m-d") }}',
            end_date: '{{ $policy && $policy->end_date ? (is_string($policy->end_date) ? substr($policy->end_date, 0, 10) : $policy->end_date->format("Y-m-d")) : "" }}',
            notes: '{{ $policy ? addslashes(str_replace(["\r", "\n"], ["", "\\n"], $policy->notes ?? "")) : "" }}',
        },

        setPolicyType(type) {
            this.formData.policy_type = type;
            if (type === 'quantity') {
                this.formData.apply_scope = 'product';
            } else if (!['order', 'product'].includes(this.formData.apply_scope)) {
                this.formData.apply_scope = 'order';
            }
        },

        selectCustomerSegment(scope) {
            this.selectedCustomerSegment = scope;
            if (scope === 'Đại lý toàn hệ thống' || scope === 'Tất cả khách hàng') {
                this.formData.target_customer_ids = [];
            } else {
                const q = scope.toLowerCase();
                const matched = this.dealersCatalog.filter(d => 
                    (d.tier && d.tier.toLowerCase().includes(q)) || 
                    (d.name && d.name.toLowerCase().includes(q))
                );
                this.formData.target_customer_ids = matched.map(d => String(d.id));
            }
        },

        selectAllCustomers() {
            this.formData.target_customer_ids = this.dealersCatalog.map(d => String(d.id));
            this.selectedCustomerSegment = 'Tùy chọn';
        },

        clearAllCustomers() {
            this.formData.target_customer_ids = [];
            this.selectedCustomerSegment = 'Đại lý toàn hệ thống';
        },

        get filteredDealers() {
            if (!this.dealerSearch.trim()) {
                return this.dealersCatalog.slice(0, 30);
            }
            const q = this.dealerSearch.toLowerCase().trim();
            return this.dealersCatalog.filter(d => 
                (d.name && d.name.toLowerCase().includes(q)) || 
                (d.phone && d.phone.includes(q)) || 
                (d.tier && d.tier.toLowerCase().includes(q))
            ).slice(0, 50);
        },

        get filteredProducts() {
            let list = this.productsCatalog || [];
            if (this.categoryFilter) {
                list = list.filter(p => String(p.category_id) === String(this.categoryFilter));
            }
            if (this.brandFilter) {
                list = list.filter(p => String(p.brand_id) === String(this.brandFilter));
            }
            if (this.productSearch.trim()) {
                const q = this.productSearch.toLowerCase().trim();
                list = list.filter(p => 
                    (p.sku && p.sku.toLowerCase().includes(q)) || 
                    (p.name && p.name.toLowerCase().includes(q))
                );
            }
            return list.slice(0, 100);
        },

        getBrandName(brandId) {
            if (!brandId) return '';
            const b = (this.brandsCatalog || []).find(item => String(item.id) === String(brandId));
            return b ? b.name : '';
        },

        selectAllFilteredProducts() {
            let list = this.productsCatalog || [];
            if (this.categoryFilter) {
                list = list.filter(p => String(p.category_id) === String(this.categoryFilter));
            }
            if (this.brandFilter) {
                list = list.filter(p => String(p.brand_id) === String(this.brandFilter));
            }
            if (this.productSearch.trim()) {
                const q = this.productSearch.toLowerCase().trim();
                list = list.filter(p => 
                    (p.sku && p.sku.toLowerCase().includes(q)) || 
                    (p.name && p.name.toLowerCase().includes(q))
                );
            }
            const newIds = list.map(p => String(p.id));
            this.formData.target_product_ids = Array.from(new Set([...this.formData.target_product_ids, ...newIds]));
        },

        handleGlobalKeydown(e) {
            if (e.key === 'F9' || (e.ctrlKey && e.key === 's')) {
                e.preventDefault();
                if (!this.isSaving) {
                    this.submitForm();
                }
            }
        },

        async submitForm() {
            if (!this.formData.code.trim()) {
                alert('Vui lòng nhập mã chính sách!');
                return;
            }
            if (!this.formData.name.trim()) {
                alert('Vui lòng nhập tên chính sách bán hàng!');
                return;
            }

            if (this.formData.policy_type === 'percentage') {
                if (this.formData.discount_percent === null || this.formData.discount_percent === '' || Number(this.formData.discount_percent) <= 0) {
                    alert('Vui lòng nhập tỷ lệ chiết khấu (%) hợp lệ!');
                    return;
                }
            } else if (this.formData.policy_type === 'quantity') {
                if (!this.formData.buy_qty || Number(this.formData.buy_qty) <= 0 || !this.formData.free_qty || Number(this.formData.free_qty) <= 0) {
                    alert('Vui lòng nhập đầy đủ số lượng mua (X) và số lượng tặng (Y)!');
                    return;
                }
            } else if (this.formData.policy_type === 'amount') {
                if (!this.formData.discount_amount || Number(this.formData.discount_amount) <= 0) {
                    alert('Vui lòng nhập số tiền giảm giá trực tiếp (VNĐ)!');
                    return;
                }
            }

            // Xử lý phạm vi sản phẩm
            if (this.productScope === 'all') {
                this.formData.target_product_ids = [];
            } else if (this.productScope === 'specific' && this.formData.target_product_ids.length === 0) {
                alert('Bạn đang chọn chế độ "Chỉ định sản phẩm / Ngành hàng", vui lòng tick chọn ít nhất 1 sản phẩm áp dụng!');
                return;
            }

            this.isSaving = true;

            try {
                const response = await fetch('{{ url("/cat_sales_policies/save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.formData)
                });

                const result = await response.json();

                if (result.success) {
                    if (window.Swal) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: result.message || 'Lưu chính sách bán hàng thành công!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(result.message || 'Lưu thành công!');
                    }
                    window.location.href = result.redirect || '{{ url("/cat_sales_policies") }}';
                } else {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Không thể lưu',
                            text: result.message || 'Có lỗi xảy ra khi lưu dữ liệu!'
                        });
                    } else {
                        alert(result.message || 'Có lỗi xảy ra!');
                    }
                    this.isSaving = false;
                }
            } catch (err) {
                console.error(err);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi kết nối',
                        text: 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại!'
                    });
                } else {
                    alert('Không thể kết nối đến máy chủ!');
                }
                this.isSaving = false;
            }
        }
    };
}
</script>
@endsection
