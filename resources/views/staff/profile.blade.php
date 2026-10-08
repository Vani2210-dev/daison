@extends('layouts.app')

@section('title', 'Hồ Sơ Nhân Sự & Trang Cá Nhân - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="{
    activeTab: 'overview'
}">
    <!-- Header Banner -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#1b4a8c] via-[#215ba6] to-sky-500 text-white font-black text-2xl flex items-center justify-center shadow-md">
                    {{ substr(auth()->user()->name ?? 'NV', 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-black text-slate-900">{{ auth()->user()->name ?? 'Cán Bộ Nhân Viên' }}</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">
                            {{ auth()->user()->role ?? 'Nhân viên kinh doanh' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            Đang làm việc
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 mt-1">
                        <span><i class="fa-solid fa-id-card text-slate-400 mr-1"></i> Mã NV: <strong class="text-slate-700">{{ auth()->user()->code ?? ('MQ-' . auth()->id()) }}</strong></span>
                        <span><i class="fa-solid fa-envelope text-slate-400 mr-1"></i> {{ auth()->user()->email ?? '---' }}</span>
                        <span><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ auth()->user()->phone ?? '---' }}</span>
                        <span><i class="fa-solid fa-building text-slate-400 mr-1"></i> Trụ sở chính & Kho trung tâm Nghệ An</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ url('/attendance') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Chấm Công GPS Hôm Nay</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- Left: Quick Info -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
                <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-user-check text-sky-600"></i>
                    <span>Thông Tin Nhân Sự</span>
                </h3>
                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-400">Phòng ban:</span>
                        <strong class="text-slate-800">{{ auth()->user()->department ?? 'Phòng Kinh Doanh Thị Trường' }}</strong>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-400">Chức vụ:</span>
                        <strong class="text-slate-800">{{ auth()->user()->position ?? 'Chuyên viên quản lý đại lý' }}</strong>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-400">Ngày gia nhập:</span>
                        <span class="text-slate-800">{{ auth()->user()->created_at ? auth()->user()->created_at->format('d/m/Y') : '15/01/2023' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-400">Hình thức:</span>
                        <span class="text-slate-800 font-semibold">Chính thức (Toàn thời gian)</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-slate-400">Trạng thái tài khoản:</span>
                        <span class="font-bold text-emerald-600">Bảo mật 2 lớp kích hoạt</span>
                    </div>
                </div>
            </div>

            <!-- KPI Summary -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-bullseye text-amber-600"></i>
                        <span>Chỉ Tiêu KPI Tháng {{ now()->format('m/Y') }}</span>
                    </h3>
                    <a href="{{ url('/kpi') }}" class="text-[11px] font-bold text-sky-600 hover:text-sky-800">Chi tiết</a>
                </div>
                <div class="space-y-3 text-xs">
                    <div>
                        <div class="flex justify-between text-[11px] font-semibold mb-1">
                            <span>Chỉ tiêu doanh số:</span>
                            <span class="font-bold text-slate-900">85% hoàn tất</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-sky-600 h-full rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] font-semibold mb-1">
                            <span>Chăm sóc thực địa đại lý GPS:</span>
                            <span class="font-bold text-slate-900">18 / 20 lượt</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 90%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Recent Tasks & Work History -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Quick Links -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="{{ url('/tasks') }}" class="p-3.5 bg-white rounded-xl shadow-xs border border-slate-200 hover:border-sky-500 hover:shadow-sm transition-all text-center group">
                    <i class="fa-solid fa-tasks text-xl text-sky-600 group-hover:scale-110 transition-transform mb-1"></i>
                    <p class="text-xs font-bold text-slate-800">Công Việc Của Tôi</p>
                    <span class="text-[10px] text-slate-400">Xem tiến độ</span>
                </a>
                <a href="{{ url('/attendance') }}" class="p-3.5 bg-white rounded-xl shadow-xs border border-slate-200 hover:border-emerald-500 hover:shadow-sm transition-all text-center group">
                    <i class="fa-solid fa-map-pin text-xl text-emerald-600 group-hover:scale-110 transition-transform mb-1"></i>
                    <p class="text-xs font-bold text-slate-800">Bảng Chấm Công</p>
                    <span class="text-[10px] text-slate-400">GPS thực địa</span>
                </a>
                <a href="{{ url('/payroll') }}" class="p-3.5 bg-white rounded-xl shadow-xs border border-slate-200 hover:border-amber-500 hover:shadow-sm transition-all text-center group">
                    <i class="fa-solid fa-money-check-dollar text-xl text-amber-600 group-hover:scale-110 transition-transform mb-1"></i>
                    <p class="text-xs font-bold text-slate-800">Phiếu Lương</p>
                    <span class="text-[10px] text-slate-400">Thu nhập & Thưởng</span>
                </a>
                <a href="{{ url('/proposals') }}" class="p-3.5 bg-white rounded-xl shadow-xs border border-slate-200 hover:border-purple-500 hover:shadow-sm transition-all text-center group">
                    <i class="fa-solid fa-file-circle-check text-xl text-purple-600 group-hover:scale-110 transition-transform mb-1"></i>
                    <p class="text-xs font-bold text-slate-800">Đề Xuất Của Tôi</p>
                    <span class="text-[10px] text-slate-400">Trình ký duyệt</span>
                </a>
            </div>

            <!-- Recent Assigned Orders / Work -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-list text-sky-600"></i>
                        <span>Nhiệm Vụ & Giao Dịch Gần Nhất Phụ Trách</span>
                    </h3>
                    <a href="{{ url('/orders') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">Xem tất cả đơn</a>
                </div>
                <div class="p-4 space-y-3">
                    <div class="p-3 bg-sky-50/50 rounded-xl border border-sky-100 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-sky-600 text-white flex items-center justify-center font-bold">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">Đơn hàng mới tạo cho Đại lý Hồng Hà</h4>
                                <p class="text-[11px] text-slate-500">Ống nhựa PVC C2 D110, D90 & Phụ tùng phụ kiện</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Đang giao hàng</span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">Chấm công GPS tại Chi nhánh Kho Hải</h4>
                                <p class="text-[11px] text-slate-500">Check-in lúc 07:45 sáng &bull; Tọa độ khớp 100%</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Hợp lệ</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
