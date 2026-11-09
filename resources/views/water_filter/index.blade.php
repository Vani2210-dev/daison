@extends('layouts.app')

@section('title', 'Phân Hệ Lắp Đặt & Sửa Chữa Máy Lọc Nước | Đại Sơn ERP')

@section('content')
<div class="w-full space-y-4" x-data="waterFilterModule()">

    <!-- HEADER & TITLE (Chuyên biệt theo từng chức năng trang) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-xs border border-slate-200/80">
        <!-- 1. ĐƠN ĐI LẮP ĐẶT -->
        <template x-if="activeTab === 'installations'">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-cyan-500 text-white flex items-center justify-center shadow-md shadow-sky-500/20 text-xl shrink-0">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">ĐƠN ĐI LẮP ĐẶT MÁY LỌC NƯỚC</h1>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                            Điều phối KTV
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Quản lý tiến độ đơn lắp máy mới, giao việc cho kỹ thuật viên & vật tư phụ kiện mang theo
                    </p>
                </div>
            </div>
        </template>

        <!-- 2. MÁY ĐÃ LẮP & BẢO HÀNH QR -->
        <template x-if="activeTab === 'installed_machines'">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 text-xl shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">MÁY ĐÃ LẮP & BẢO HÀNH QR</h1>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Bảo hành điện tử
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Danh sách máy lọc nước đã bàn giao cho khách, tra cứu tem dán QR, kích hoạt và theo dõi hạn bảo hành
                    </p>
                </div>
            </div>
        </template>

        <!-- 3. LỊCH BẢO DƯỠNG & THAY LÕI ĐỊNH KỲ -->
        <template x-if="activeTab === 'maintenance'">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-500 text-white flex items-center justify-center shadow-md shadow-purple-500/20 text-xl shrink-0">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">LỊCH BẢO DƯỠNG & THAY LÕI ĐỊNH KỲ</h1>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                            Chăm sóc khách hàng
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Theo dõi danh sách máy lọc đến hạn thay lõi định kỳ, cảnh báo quá hạn và xếp lịch hẹn kỹ thuật
                    </p>
                </div>
            </div>
        </template>

        <!-- 4. TIẾP NHẬN SỬA CHỮA & BẢO HÀNH -->
        <template x-if="activeTab === 'repairs'">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-600 to-orange-500 text-white flex items-center justify-center shadow-md shadow-amber-500/20 text-xl shrink-0">
                    <i class="fa-solid fa-wrench"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">TIẾP NHẬN SỬA CHỮA & BẢO HÀNH</h1>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Dịch vụ kỹ thuật
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Quản lý các ca máy hỏng hóc, sự cố cần sửa chữa tại trạm hoặc tại nhà khách, thay thế linh kiện
                    </p>
                </div>
            </div>
        </template>

        <!-- 5. KHO PHỤ TÙNG & PHỤ KIỆN -->
        <template x-if="activeTab === 'parts'">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-500 text-white flex items-center justify-center shadow-md shadow-teal-500/20 text-xl shrink-0">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">KHO PHỤ TÙNG & PHỤ KIỆN MÁY LỌC</h1>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                            Kho chuyên dụng
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Quản lý danh mục lõi lọc nước, linh kiện thay thế, cảnh báo ngưỡng tồn kho an toàn & giá thành
                    </p>
                </div>
            </div>
        </template>

        <!-- 6. MODEL MÁY LỌC -->
        <template x-if="activeTab === 'models'">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-500 text-white flex items-center justify-center shadow-md shadow-cyan-500/20 text-xl shrink-0">
                    <i class="fa-solid fa-faucet-drip"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">DANH MỤC MODEL MÁY LỌC NƯỚC</h1>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">
                            Catalog
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Quản lý các dòng máy lọc nước, nhà sản xuất, cấu hình lõi lọc tiêu chuẩn & định mức vật tư đi kèm
                    </p>
                </div>
            </div>
        </template>

        <!-- 7. NHẬT KÝ KHO -->
        <template x-if="activeTab === 'transactions'">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-500 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 text-xl shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">NHẬT KÝ NHẬP / XUẤT KHO PHỤ TÙNG</h1>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                            Lịch sử kho
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Theo dõi toàn bộ biến động nhập phụ tùng mới, xuất phụ tùng theo đơn lắp đặt hoặc tiếp nhận sửa chữa
                    </p>
                </div>
            </div>
        </template>

        <!-- QUICK ACTION BUTTONS (Hiển thị nút hành động nhanh tương ứng từng trang) -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <template x-if="activeTab === 'installations'">
                <a href="{{ url('/water-filter/installations/create') }}" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <span>+ Lên đơn lắp đặt (Trang riêng)</span>
                </a>
            </template>
            <template x-if="activeTab === 'installed_machines'">
                <div class="flex items-center gap-2">
                    <a href="{{ url('/bao-hanh-may-loc/print-qr') }}" target="_blank" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                        <i class="fa-solid fa-print"></i>
                        <span>In bảng tem dán QR</span>
                    </a>
                    <button @click="openModal('activateModal')" type="button" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>+ Kích hoạt QR / Khai báo máy</span>
                    </button>
                </div>
            </template>
            <template x-if="activeTab === 'repairs'">
                <button @click="openModal('repairModal')" type="button" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                    <i class="fa-solid fa-wrench"></i>
                    <span>+ Tiếp nhận sửa chữa</span>
                </button>
            </template>
            <template x-if="activeTab === 'parts'">
                <div class="flex items-center gap-2">
                    <button @click="openModal('partModal')" type="button" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>+ Thêm phụ tùng</span>
                    </button>
                    <button @click="openModal('importModal')" type="button" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                        <i class="fa-solid fa-boxes-packing"></i>
                        <span>+ Nhập kho phụ tùng</span>
                    </button>
                </div>
            </template>
            <template x-if="activeTab === 'models'">
                <button @click="openModal('modelModal')" type="button" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold shadow-xs hover:shadow-md transition cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>+ Thêm Model máy</span>
                </button>
            </template>
        </div>
    </div>

    <!-- MAIN VIEW CONTAINER (Nội dung từng trang riêng biệt) -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">


        <!-- ============================================================== -->
        <!-- TAB CONTENTS                                                   -->
        <!-- ============================================================== -->
        @include('water_filter.tabs.tab_installations')
        @include('water_filter.tabs.tab_installed_machines')
        @include('water_filter.tabs.tab_maintenance')
        @include('water_filter.tabs.tab_repairs')
        @include('water_filter.tabs.tab_parts')
        @include('water_filter.tabs.tab_models')
        @include('water_filter.tabs.tab_transactions')
    </div>

    <!-- ============================================================== -->
    <!-- MODALS                                                         -->
    <!-- ============================================================== -->
    @include('water_filter.modals.modal_install')
    @include('water_filter.modals.modal_inspect')
    @include('water_filter.modals.modal_repair')
    @include('water_filter.modals.modal_repair_update')
    @include('water_filter.modals.modal_part')
    @include('water_filter.modals.modal_import')
    @include('water_filter.modals.modal_machine_model')
    @include('water_filter.modals.modal_warranty_edit')
    @include('water_filter.modals.modal_batch_qr')
    @include('water_filter.modals.modal_qr_preview')
    @include('water_filter.modals.modal_activate')
    @include('water_filter.modals.modal_machine_edit')
    @include('water_filter.modals.modal_maintenance_reschedule')
    @include('water_filter.modals.modal_maintenance_reminder')
</div>

<!-- ============================================================== -->
<!-- JAVASCRIPT & ALPINE.JS LOGIC                                   -->
<!-- ============================================================== -->
@include('water_filter.partials.scripts')
@endsection
