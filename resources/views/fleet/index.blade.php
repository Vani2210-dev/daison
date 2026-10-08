@extends('layouts.app')

@section('title', 'Quản Lý Fleet Xe & Quyết Toán Lái Xe - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="{
    activeTab: 'vehicles'
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
                        <i class="fa-solid fa-truck text-purple-600"></i>
                        <span>Quản Lý Đội Xe (Fleet) & Quyết Toán Lái Xe</span>
                    </h1>
                    <p class="text-xs text-slate-500">Giám sát phương tiện vận tải, định vị GPS, nhiên liệu, bảo dưỡng và bàn giao tiền COD</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ url('/delivery-dispatch') }}" class="px-3.5 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#215ba6] text-white text-xs font-bold shadow-xs flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-route"></i>
                    <span>Sang Điều Phối Xe & Xếp Tuyến</span>
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-3 border-t border-slate-100">
            <div class="p-3 bg-sky-50/60 rounded-xl border border-sky-100">
                <span class="text-[11px] font-semibold text-sky-800">Tổng Số Xe Đội Vận Tải</span>
                <p class="text-xl font-bold text-sky-900 mt-1">{{ $totalVehicles ?? $vehicles->total() }} Xe</p>
            </div>
            <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100">
                <span class="text-[11px] font-semibold text-emerald-800">Xe Đang Sẵn Sàng</span>
                <p class="text-xl font-bold text-emerald-900 mt-1">{{ $readyVehicles ?? 0 }} Xe</p>
            </div>
            <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-100">
                <span class="text-[11px] font-semibold text-amber-800">Xe Đang Đi Giao Tuyến</span>
                <p class="text-xl font-bold text-amber-900 mt-1">{{ $deliveringVehicles ?? 0 }} Xe</p>
            </div>
            <div class="p-3 bg-rose-50/60 rounded-xl border border-rose-100">
                <span class="text-[11px] font-semibold text-rose-800">Bảo Dưỡng & Sửa Chữa</span>
                <p class="text-xl font-bold text-rose-900 mt-1">{{ $maintenanceVehicles ?? 0 }} Xe</p>
            </div>
        </div>
    </div>

    <!-- Vehicles List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($vehicles ?? [] as $v)
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 hover:shadow-md transition-all">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 rounded bg-slate-900 text-amber-400 font-mono font-black text-xs border border-slate-700 shadow-2xs">
                            {{ $v->plate_number }}
                        </span>
                        <span class="text-xs font-bold text-slate-800">{{ $v->type ?? 'Xe tải' }}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $v->status === 'Đang giao hàng' ? 'bg-amber-100 text-amber-800' : ($v->status === 'Bảo dưỡng' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800') }}">
                        {{ $v->status ?? 'Sẵn sàng' }}
                    </span>
                </div>

                <div class="py-3 space-y-2 text-xs text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Tài xế phụ trách:</span>
                        <strong class="text-slate-900 font-bold">{{ $v->driver->name ?? $v->driver_name ?? 'Chưa phân công' }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Điện thoại liên hệ:</span>
                        <span class="font-mono text-slate-700">{{ $v->driver->phone ?? $v->driver_phone ?? '---' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Tải trọng cho phép:</span>
                        <span class="font-semibold text-slate-800">{{ $v->payload_capacity ?? '2.5 tấn' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Tuyến đường chính:</span>
                        <span class="font-semibold text-sky-700">{{ $v->deliveryRoute->name ?? 'Tuyến Bắc Miền Trung' }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <span class="text-slate-500">Mức nhiên liệu ước tính:</span>
                        <span class="font-bold text-emerald-600">85% Bình</span>
                    </div>
                </div>

                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <a href="{{ url('/cat_vehicles?action=assignment&vehicle_id=' . $v->id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1" title="Phân công tài xế, phụ xe & tuyến">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Phân công</span>
                        </a>
                        <a href="{{ url('/delivery-dispatch') }}" class="text-sky-600 hover:text-sky-800 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-route"></i>
                            <span>Điều phối</span>
                        </a>
                    </div>
                    <button type="button" @click="alert('Xe: {{ $v->plate_number }} | Bảo dưỡng định kỳ: Còn 1.200 km')" class="text-slate-400 hover:text-slate-700" title="Chi tiết bảo dưỡng">
                        <i class="fa-solid fa-wrench"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl p-8 text-center text-slate-400 border border-slate-200">
                <i class="fa-solid fa-truck text-3xl mb-2 text-slate-300"></i>
                <p class="text-xs">Chưa có thông tin xe giao hàng trong hệ thống</p>
            </div>
        @endforelse
    </div>

    @if($vehicles->hasPages())
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 px-6 py-4">
            {{ $vehicles->links() }}
        </div>
    @endif
</div>
@endsection
