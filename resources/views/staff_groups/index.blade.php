@php $authUser = Auth::user(); @endphp
@extends('layouts.app')

@section('title', 'Quản Lý Nhóm Nhân Sự | ERP Đại Sơn')

@section('content')
<div x-data="staffGroupApp()" x-init="initApp()" class="w-full space-y-4 pb-16">

    <!-- Toast Notification -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-[9999] px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold border"
         :class="toast.type === 'success' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-rose-600 text-white border-rose-500'">
        <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check text-base' : 'fa-solid fa-circle-exclamation text-base'"></i>
        <span x-text="toast.message"></span>
    </div>

    <!-- 1. BREADCRUMB & HEADER -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ url('/dashboard') }}" class="hover:text-slate-600">Trang chủ</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <a href="{{ url('/staff') }}" class="hover:text-slate-600">Nhân sự</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-indigo-600 font-bold">Nhóm nhân sự</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-base shadow-2xs">
                    <i class="fa-solid fa-people-group"></i>
                </div>
                <span>Quản Lý Nhóm Nhân Sự</span>
                <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-indigo-100 text-indigo-800 border border-indigo-200" x-text="filteredGroups.length + ' nhóm'"></span>
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Phân chia đội nhóm, chỉ định trưởng nhóm và quản lý danh sách thành viên linh hoạt</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Quản lý Ngành hàng / Phân loại Categories -->
            <a href="{{ route('categories.index') }}"
               class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 transition-all cursor-pointer border border-slate-300 shadow-2xs">
                <i class="fa-solid fa-layer-group text-indigo-600"></i>
                <span>Xem Ngành Hàng ({{ $totalCategories }})</span>
            </a>

            <!-- Thêm Nhóm Mới Button -->
            <button type="button" @click="openCreateModal()"
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-2 transition-all cursor-pointer shadow-md shadow-indigo-600/20">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Thêm Nhóm Nhân Sự</span>
            </button>
        </div>
    </div>

    <!-- 2. STATISTIC CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Card 1: Tổng số nhóm -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tổng số nhóm</p>
                <p class="text-2xl font-black text-slate-900 mt-1 font-mono" x-text="stats.totalGroups">{{ $totalGroups }}</p>
                <p class="text-[11px] text-slate-400 font-semibold mt-0.5">Tất cả đội nhóm đã lập</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xl shadow-2xs">
                <i class="fa-solid fa-people-group"></i>
            </div>
        </div>

        <!-- Card 2: Nhóm đang hoạt động -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Đang hoạt động</p>
                <p class="text-2xl font-black text-emerald-600 mt-1 font-mono" x-text="stats.activeGroups">{{ $activeGroups }}</p>
                <p class="text-[11px] text-emerald-600 font-semibold mt-0.5 flex items-center gap-1">
                    <i class="fa-solid fa-circle-check text-[10px]"></i> Sẵn sàng làm việc
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xl shadow-2xs">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <!-- Card 3: Tổng số nhân sự tham gia -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nhân sự trong nhóm</p>
                <p class="text-2xl font-black text-blue-600 mt-1 font-mono" x-text="stats.totalMembers">{{ $totalMembers }}</p>
                <p class="text-[11px] text-slate-400 font-semibold mt-0.5">Được phân bổ vào các nhóm</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xl shadow-2xs">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Card 4: Số lượng phân loại (Categories) -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Phân loại (Categories)</p>
                <p class="text-2xl font-black text-amber-600 mt-1 font-mono" x-text="stats.totalCategories">{{ $totalCategories }}</p>
                <p class="text-[11px] text-slate-400 font-semibold mt-0.5">Danh mục ngành hàng</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 text-xl shadow-2xs">
                <i class="fa-solid fa-layer-group"></i>
            </div>
        </div>
    </div>

    <!-- 3. SEARCH & FILTERS BAR -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            <input type="text" x-model="searchKeyword"
                   placeholder="Tìm theo tên nhóm, mã nhóm, tên trưởng nhóm..."
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 font-medium text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:border-indigo-500 transition-all placeholder:text-slate-400 shadow-2xs">
        </div>

        <!-- Filters Group -->
        <div class="flex flex-wrap items-center gap-2.5 text-xs">
            <!-- Filter Phân loại (Category ID) -->
            <select x-model="filterCategory" class="px-3 py-2 bg-white rounded-xl border border-slate-200 font-semibold text-slate-700 focus:outline-none focus:border-indigo-500 cursor-pointer shadow-2xs">
                <option value="">-- Tất cả phân loại (Categories) --</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name + ' (ID: ' + cat.id + ')'"></option>
                </template>
            </select>

            <!-- Filter Trưởng nhóm (user_id) -->
            <select x-model="filterLeader" class="px-3 py-2 bg-white rounded-xl border border-slate-200 font-semibold text-slate-700 focus:outline-none focus:border-indigo-500 cursor-pointer shadow-2xs">
                <option value="">-- Tất cả trưởng nhóm --</option>
                <template x-for="u in users" :key="u.id">
                    <option :value="u.id" x-text="u.name"></option>
                </template>
            </select>

            <!-- Filter Trạng thái -->
            <select x-model="filterStatus" class="px-3 py-2 bg-white rounded-xl border border-slate-200 font-semibold text-slate-700 focus:outline-none focus:border-indigo-500 cursor-pointer shadow-2xs">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="active">Đang hoạt động</option>
                <option value="inactive">Tạm ngưng</option>
            </select>

            <!-- View Mode Switcher -->
            <div class="flex items-center bg-slate-100 p-0.5 rounded-xl border border-slate-200/80 shadow-2xs text-xs">
                <button type="button" @click="viewMode = 'grid'"
                        class="px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-all font-bold cursor-pointer"
                        :class="viewMode === 'grid' ? 'bg-white text-indigo-700 shadow-xs border border-slate-200/60' : 'text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-grip text-xs"></i>
                    <span class="hidden sm:inline">Dạng thẻ</span>
                </button>
                <button type="button" @click="viewMode = 'table'"
                        class="px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-all font-bold cursor-pointer"
                        :class="viewMode === 'table' ? 'bg-white text-indigo-700 shadow-xs border border-slate-200/60' : 'text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-table-list text-xs"></i>
                    <span class="hidden sm:inline">Dạng bảng</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 4. VIEW MODE: GRID (DẠNG THẺ HIỆN ĐẠI) -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="g in filteredGroups" :key="g.id">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group">
                <!-- Card Header -->
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <span class="px-2.5 py-1 rounded-lg font-mono font-bold text-[11px] bg-slate-100 text-slate-700 border border-slate-200" x-text="g.code || ('NNS-' + g.id)"></span>
                        <div class="flex items-center gap-2">
                            <!-- Status Badge -->
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                  :class="g.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="g.status === 'active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                <span x-text="g.status === 'active' ? 'Hoạt động' : 'Tạm ngưng'"></span>
                            </span>
                        </div>
                    </div>

                    <h3 class="text-base font-black text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1" x-text="g.name"></h3>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 min-h-[32px]" x-text="g.description || 'Chưa có mô tả nhiệm vụ cho nhóm nhân sự này.'"></p>

                    <!-- Phân loại ID (Categories) Badges -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1 text-indigo-700 font-black">
                                <i class="fa-solid fa-tags text-indigo-500"></i> Phân loại ID (Categories):
                            </span>
                            <span class="text-[10px] font-mono text-slate-400" x-text="(g.category_ids ? g.category_ids.length : 0) + ' phân loại'"></span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="cat in getGroupCategories(g)" :key="cat.id">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs">
                                    <span x-text="cat.name"></span>
                                    <span class="text-[10px] font-mono text-indigo-500 font-black" x-text="'(ID: ' + cat.id + ')'"></span>
                                </span>
                            </template>
                            <template x-if="!g.category_ids || g.category_ids.length === 0">
                                <span class="text-[11px] text-slate-400 italic">Chưa chọn phân loại</span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Card Body: Leader & Members -->
                <div class="p-5 space-y-4 flex-1 bg-slate-50/40">
                    <!-- Trưởng Nhóm Box -->
                    <div class="bg-white rounded-xl p-3 border border-slate-200/80 shadow-2xs">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                            <span class="flex items-center gap-1.5 text-amber-700">
                                <i class="fa-solid fa-crown text-amber-500"></i> Trưởng nhóm
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">user_id: <span class="font-mono text-slate-600" x-text="g.user_id || 'Chưa gán'"></span></span>
                        </div>
                        <template x-if="g.leader">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 border-2 border-white shadow-2xs flex items-center justify-center font-black text-indigo-700 text-sm shrink-0 overflow-hidden">
                                    <template x-if="g.leader.avatar">
                                        <img :src="g.leader.avatar" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!g.leader.avatar">
                                        <span x-text="getInitials(g.leader.name)"></span>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-slate-900 truncate" x-text="g.leader.name"></div>
                                    <div class="text-[11px] text-slate-500 truncate" x-text="g.leader.department || 'Nhân sự'"></div>
                                    <div class="text-[10px] text-slate-400 font-mono" x-text="g.leader.phone || g.leader.email"></div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!g.leader">
                            <div class="text-xs text-amber-700 font-semibold py-1 flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation text-amber-500"></i>
                                <span>Chưa chỉ định trưởng nhóm</span>
                            </div>
                        </template>
                    </div>

                    <!-- Thành viên (user_ids) Box -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-600 mb-2">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-users text-slate-400"></i>
                                <span>Thành viên:</span>
                                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[11px] font-black font-mono" x-text="(g.user_ids ? g.user_ids.length : 0) + ' người'"></span>
                            </span>
                            <button type="button" @click="viewGroupMembers(g)" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-bold underline cursor-pointer">
                                Xem tất cả
                            </button>
                        </div>

                        <!-- Avatars Stack -->
                        <div class="flex items-center -space-x-2 overflow-hidden py-1">
                            <template x-for="(m, idx) in getGroupMemberUsers(g).slice(0, 6)" :key="m.id">
                                <div class="inline-block w-8 h-8 rounded-full ring-2 ring-white bg-slate-200 text-[10px] font-bold text-slate-700 flex items-center justify-center shadow-2xs cursor-pointer overflow-hidden"
                                     :title="m.name + ' (' + (m.department || 'Kho') + ')'">
                                    <template x-if="m.avatar">
                                        <img :src="m.avatar" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!m.avatar">
                                        <span x-text="getInitials(m.name)"></span>
                                    </template>
                                </div>
                            </template>
                            <template x-if="g.user_ids && g.user_ids.length > 6">
                                <div class="inline-block w-8 h-8 rounded-full ring-2 ring-white bg-indigo-600 text-white text-[10px] font-black flex items-center justify-center shadow-2xs cursor-pointer"
                                     @click="viewGroupMembers(g)">
                                    <span x-text="'+' + (g.user_ids.length - 6)"></span>
                                </div>
                            </template>
                            <template x-if="!g.user_ids || g.user_ids.length === 0">
                                <span class="text-xs text-slate-400 italic">Chưa có thành viên</span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Card Footer / Actions -->
                <div class="px-5 py-3.5 bg-white border-t border-slate-100 flex items-center justify-between gap-2">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold"
                          :class="g.status === 'active' ? 'text-emerald-700' : 'text-slate-400'">
                        <i class="fa-solid fa-circle text-[8px]" :class="g.status === 'active' ? 'text-emerald-500' : 'text-slate-300'"></i>
                        <span x-text="g.status === 'active' ? 'Đang hoạt động' : 'Tạm ngưng'"></span>
                    </span>

                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="viewGroupMembers(g)"
                                class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors cursor-pointer"
                                title="Xem chi tiết nhóm">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </button>
                        <button type="button" @click="openEditModal(g)"
                                class="p-2 rounded-xl text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer"
                                title="Chỉnh sửa nhóm">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>
                        <button type="button" @click="deleteGroup(g)"
                                class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                title="Xóa nhóm">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- 5. VIEW MODE: TABLE (DẠNG BẢNG CHI TIẾT) -->
    <div x-show="viewMode === 'table'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[950px]">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-700 font-bold text-[11px] uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4 w-16 text-center">STT</th>
                        <th class="py-3.5 px-4">MÃ & TÊN NHÓM</th>
                        <th class="py-3.5 px-4">PHÂN LOẠI ID (CATEGORIES)</th>
                        <th class="py-3.5 px-4">TRƯỞNG NHÓM (USER_ID)</th>
                        <th class="py-3.5 px-4 text-center">SỐ THÀNH VIÊN (USER_IDS)</th>
                        <th class="py-3.5 px-4">MÔ TẢ NHIỆM VỤ</th>
                        <th class="py-3.5 px-4 text-center">TRẠNG THÁI</th>
                        <th class="py-3.5 px-4 text-center w-28">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(g, index) in filteredGroups" :key="g.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold" x-text="index + 1"></td>
                            <td class="py-3.5 px-4">
                                <div class="font-black text-slate-900 text-sm" x-text="g.name"></div>
                                <div class="text-[11px] font-mono font-bold text-slate-400 mt-0.5" x-text="g.code || ('NNS-' + g.id)"></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    <template x-for="cat in getGroupCategories(g)" :key="cat.id">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs">
                                            <span x-text="cat.name"></span>
                                            <span class="text-[10px] font-mono text-indigo-500 font-black" x-text="'(ID: ' + cat.id + ')'"></span>
                                        </span>
                                    </template>
                                    <template x-if="!g.category_ids || g.category_ids.length === 0">
                                        <span class="text-slate-400 italic text-xs">Chưa chọn</span>
                                    </template>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <template x-if="g.leader">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden shadow-2xs">
                                            <template x-if="g.leader.avatar">
                                                <img :src="g.leader.avatar" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!g.leader.avatar">
                                                <span x-text="getInitials(g.leader.name)"></span>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                                <span x-text="g.leader.name"></span>
                                                <i class="fa-solid fa-crown text-amber-500 text-[10px]" title="Trưởng nhóm"></i>
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono">user_id: <span x-text="g.user_id"></span></div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!g.leader">
                                    <span class="text-amber-600 font-semibold text-xs">Chưa phân công</span>
                                </template>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" @click="viewGroupMembers(g)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold font-mono transition-colors cursor-pointer shadow-2xs">
                                    <i class="fa-solid fa-users text-[11px]"></i>
                                    <span x-text="(g.user_ids ? g.user_ids.length : 0) + ' người'"></span>
                                </button>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-slate-600 truncate" x-text="g.description || '-'"></td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                      :class="g.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'">
                                    <i class="fa-solid fa-circle text-[7px]" :class="g.status === 'active' ? 'text-emerald-500' : 'text-slate-400'"></i>
                                    <span x-text="g.status === 'active' ? 'Hoạt động' : 'Tạm ngưng'"></span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="viewGroupMembers(g)"
                                            class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors cursor-pointer"
                                            title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button type="button" @click="openEditModal(g)"
                                            class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors cursor-pointer"
                                            title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="deleteGroup(g)"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="filteredGroups.length === 0" class="py-16 text-center bg-white rounded-2xl border border-slate-200">
        <div class="w-16 h-16 rounded-3xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl mx-auto mb-3">
            <i class="fa-solid fa-people-group"></i>
        </div>
        <p class="font-bold text-slate-700 text-sm">Không tìm thấy nhóm nhân sự nào phù hợp</p>
        <p class="text-xs text-slate-400 mt-1">Hãy thử thay đổi bộ lọc tìm kiếm hoặc thêm nhóm nhân sự mới</p>
        <button type="button" @click="openCreateModal()" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs cursor-pointer shadow-md">
            + Thêm Nhóm Nhân Sự Mới
        </button>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 1: THÊM MỚI / CHỈNH SỬA NHÓM NHÂN SỰ -->
    <!-- ===================================================== -->
    <div x-show="showGroupModal" x-cloak class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showGroupModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 overflow-hidden z-10 border border-slate-100">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-people-group"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900" x-text="modalMode === 'create' ? 'Thêm Nhóm Nhân Sự Mới' : 'Chỉnh Sửa Nhóm Nhân Sự'"></h3>
                            <p class="text-xs text-slate-400 font-medium">Thiết lập trưởng nhóm (user_id), phân loại (ID) và danh sách thành viên (user_ids)</p>
                        </div>
                    </div>
                    <button type="button" @click="showGroupModal = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl cursor-pointer">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <!-- Form Fields -->
                <div class="space-y-4">
                    <!-- Tên nhóm & Mã nhóm -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tên nhóm nhân sự <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="formData.name" placeholder="Vd: Tổ Bốc Xếp & Soạn Hàng Kho 1"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 font-bold text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mã nhóm</label>
                            <input type="text" x-model="formData.code" placeholder="Tự sinh nếu trống"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 font-mono font-bold text-slate-800 uppercase focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Trưởng nhóm (user_id) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            <span class="text-amber-700 flex items-center gap-1">
                                <i class="fa-solid fa-crown text-amber-500"></i> Trưởng nhóm (user_id)
                            </span>
                        </label>
                        <select x-model="formData.user_id"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-amber-300 bg-amber-50/40 font-bold text-slate-800 focus:outline-none focus:border-amber-500">
                            <option value="">-- Chưa chỉ định trưởng nhóm --</option>
                            <template x-for="u in users" :key="u.id">
                                <option :value="u.id" x-text="u.name + ' (' + (u.department || 'Kho') + ' - ID: ' + u.id + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Phân loại ID (Categories) - Có thể chọn nhiều ID -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-tags text-indigo-600"></i>
                                <span>Phân loại ID (Categories / Ngành hàng)</span>
                                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-black font-mono"
                                      x-text="formData.category_ids.length + ' đã chọn'"></span>
                            </label>
                            <div class="flex items-center gap-2 text-[11px]">
                                <button type="button" @click="selectAllCategories()" class="text-indigo-600 hover:underline font-bold cursor-pointer">Chọn tất cả</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="formData.category_ids = []" class="text-rose-600 hover:underline font-bold cursor-pointer">Bỏ chọn</button>
                            </div>
                        </div>

                        <!-- Selected Preview Chips -->
                        <div x-show="formData.category_ids.length > 0" class="flex flex-wrap gap-1.5 p-2 rounded-xl bg-indigo-50/60 border border-indigo-100">
                            <template x-for="catId in formData.category_ids" :key="catId">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-indigo-700 border border-indigo-200 shadow-2xs">
                                    <i class="fa-solid fa-circle-check text-indigo-500 text-[10px]"></i>
                                    <span x-text="getCategoryName(catId)"></span>
                                    <span class="text-[10px] font-mono text-indigo-500 font-black" x-text="'(ID: ' + catId + ')'"></span>
                                    <button type="button" @click="toggleCategory(catId)" class="hover:text-rose-600 ml-0.5 text-slate-400 cursor-pointer">
                                        <i class="fa-solid fa-xmark text-[10px]"></i>
                                    </button>
                                </span>
                            </template>
                        </div>

                        <!-- Category Multi-Select Checkboxes Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 max-h-44 overflow-y-auto">
                            <template x-for="cat in categories" :key="cat.id">
                                <label class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer select-none"
                                       :class="formData.category_ids.includes(Number(cat.id)) ? 'bg-indigo-50/90 border-indigo-300 shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50'">
                                    <div class="flex items-center gap-2.5">
                                        <input type="checkbox" :value="cat.id"
                                               :checked="formData.category_ids.includes(Number(cat.id))"
                                               @change="toggleCategory(cat.id)"
                                               class="w-4 h-4 rounded text-indigo-600 focus:ring-0 cursor-pointer">
                                        <span class="text-xs font-bold text-slate-800" x-text="cat.name"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold" x-text="cat.code"></span>
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded font-black"
                                              :class="formData.category_ids.includes(Number(cat.id)) ? 'bg-indigo-200 text-indigo-900' : 'bg-slate-200 text-slate-700'"
                                              x-text="'ID: ' + cat.id"></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Nhóm nhân sự này có thể phụ trách một hoặc nhiều phân loại ngành hàng (ID).</p>
                    </div>

                    <!-- Thành viên (user_ids) Smart Multi-select -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-users text-indigo-600"></i>
                                <span>Danh sách thành viên (user_ids)</span>
                                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-black" x-text="formData.user_ids.length + ' đã chọn'"></span>
                            </label>
                            <div class="flex items-center gap-2 text-[11px]">
                                <button type="button" @click="selectAllMembers()" class="text-indigo-600 hover:underline font-bold cursor-pointer">Chọn tất cả</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="formData.user_ids = []" class="text-rose-600 hover:underline font-bold cursor-pointer">Bỏ chọn</button>
                            </div>
                        </div>

                        <!-- User search & filter in picker -->
                        <div class="border border-slate-200 rounded-2xl p-3 bg-slate-50 space-y-2.5">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                <input type="text" x-model="memberSearchKeyword" placeholder="Lọc nhanh nhân viên để thêm..."
                                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white font-medium focus:outline-none focus:border-indigo-500">
                            </div>

                            <!-- User Checklist -->
                            <div class="max-h-48 overflow-y-auto space-y-1 pr-1 divide-y divide-slate-100">
                                <template x-for="u in filteredPickerUsers" :key="u.id">
                                    <label class="flex items-center justify-between p-2 rounded-xl hover:bg-white transition-colors cursor-pointer select-none">
                                        <div class="flex items-center gap-2.5">
                                            <input type="checkbox" :value="u.id"
                                                   :checked="formData.user_ids.includes(u.id)"
                                                   @change="toggleMember(u.id)"
                                                   class="w-4 h-4 rounded text-indigo-600 focus:ring-0 cursor-pointer">
                                            <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0 overflow-hidden">
                                                <template x-if="u.avatar">
                                                    <img :src="u.avatar" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!u.avatar">
                                                    <span x-text="getInitials(u.name)"></span>
                                                </template>
                                            </div>
                                            <div>
                                                <span class="font-bold text-xs text-slate-800" x-text="u.name"></span>
                                                <span class="text-[10px] text-slate-400 font-mono ml-1" x-text="'(ID: ' + u.id + ')'"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] px-2 py-0.5 rounded-md font-semibold"
                                                  :class="formData.user_id == u.id ? 'bg-amber-100 text-amber-900 border border-amber-300 font-bold' : 'bg-slate-100 text-slate-600'"
                                                  x-text="formData.user_id == u.id ? '👑 Trưởng nhóm' : (u.department || 'Kho')"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Mô tả nhiệm vụ & Trạng thái -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mô tả / Nhiệm vụ của nhóm</label>
                            <input type="text" x-model="formData.description" placeholder="Vd: Chuyên trách soạn hàng tuyến miền Tây và đóng pallet"
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 font-medium text-slate-800 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Trạng thái</label>
                            <select x-model="formData.status"
                                    class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 font-bold text-slate-800 bg-white focus:outline-none focus:border-indigo-500">
                                <option value="active">Đang hoạt động</option>
                                <option value="inactive">Tạm ngưng</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showGroupModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-bold transition-all cursor-pointer">
                        Hủy bỏ
                    </button>
                    <button type="button" @click="saveGroup()" :disabled="isSubmitting"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-2 transition-all cursor-pointer shadow-md shadow-indigo-600/20 disabled:opacity-50">
                        <i class="fa-solid fa-spinner fa-spin" x-show="isSubmitting"></i>
                        <span x-text="modalMode === 'create' ? 'Lưu Nhóm Mới' : 'Cập Nhật Nhóm'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 2: XEM CHI TIẾT NHÓM & DANH SÁCH THÀNH VIÊN -->
    <!-- ===================================================== -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showDetailModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 overflow-hidden z-10 border border-slate-100" x-show="selectedGroup">
                <!-- Header -->
                <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-xs font-bold text-slate-500" x-text="selectedGroup?.code"></span>
                            <template x-if="selectedGroup?.classification">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                                      :class="getClassificationBadgeClass(selectedGroup?.classification?.color)"
                                      x-text="selectedGroup?.classification?.name"></span>
                            </template>
                        </div>
                        <h3 class="text-xl font-black text-slate-900" x-text="selectedGroup?.name"></h3>
                        <p class="text-xs text-slate-500 mt-1" x-text="selectedGroup?.description || 'Chưa có mô tả chi tiết'"></p>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl cursor-pointer">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <!-- Leader Card -->
                <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-4 mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-md shadow-amber-500/20">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Trưởng nhóm phụ trách</div>
                            <div class="text-base font-black text-slate-900" x-text="selectedGroup?.leader ? selectedGroup.leader.name : 'Chưa chỉ định'"></div>
                            <div class="text-xs text-slate-500" x-text="selectedGroup?.leader ? (selectedGroup.leader.department + ' • ' + (selectedGroup.leader.phone || selectedGroup.leader.email)) : ''"></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-mono font-bold text-amber-900 px-2.5 py-1 bg-amber-100 rounded-lg border border-amber-300"
                              x-text="'user_id: ' + (selectedGroup?.user_id || 'null')"></span>
                    </div>
                </div>

                <!-- Phân loại phụ trách (Categories) Box -->
                <div class="bg-indigo-50/60 border border-indigo-200 rounded-2xl p-4 mb-5">
                    <div class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-tags text-indigo-600"></i> Phân loại phụ trách (Categories)
                        </span>
                        <span class="text-[10px] font-mono font-bold text-indigo-600"
                              x-text="'category_ids: [' + (selectedGroup?.category_ids || []).join(', ') + ']'"></span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="cat in getGroupCategories(selectedGroup)" :key="cat.id">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white text-indigo-800 border border-indigo-200 shadow-2xs">
                                <i class="fa-solid fa-layer-group text-indigo-500 text-[11px]"></i>
                                <span x-text="cat.name"></span>
                                <span class="text-[10px] font-mono font-semibold text-slate-500" x-text="'[' + cat.code + ']'"></span>
                                <span class="text-[10px] font-mono font-black text-indigo-600 bg-indigo-100 px-1.5 py-0.5 rounded" x-text="'ID: ' + cat.id"></span>
                            </span>
                        </template>
                        <template x-if="!selectedGroup?.category_ids || selectedGroup.category_ids.length === 0">
                            <span class="text-xs text-slate-400 italic">Chưa gán phân loại ngành hàng nào</span>
                        </template>
                    </div>
                </div>

                <!-- Members List -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-users text-indigo-600"></i>
                            <span>Danh sách thành viên</span>
                            <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[11px] font-mono font-bold"
                                  x-text="(selectedGroupMembers.length) + ' nhân sự'"></span>
                        </h4>
                        <span class="text-[11px] text-slate-400 font-mono" x-text="'user_ids: [' + (selectedGroup?.user_ids || []).join(', ') + ']'"></span>
                    </div>

                    <div class="max-h-64 overflow-y-auto rounded-2xl border border-slate-200 divide-y divide-slate-100 bg-slate-50/50">
                        <template x-for="m in selectedGroupMembers" :key="m.id">
                            <div class="p-3 bg-white flex items-center justify-between hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden shadow-2xs">
                                        <template x-if="m.avatar">
                                            <img :src="m.avatar" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!m.avatar">
                                            <span x-text="getInitials(m.name)"></span>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                            <span x-text="m.name"></span>
                                            <template x-if="selectedGroup?.user_id == m.id">
                                                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[9px] font-black border border-amber-300">TRƯỞNG NHÓM</span>
                                            </template>
                                        </div>
                                        <div class="text-[11px] text-slate-500" x-text="(m.department || 'Kho') + ' • ' + (m.branch || 'Trụ sở chính')"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-mono font-bold text-slate-700" x-text="m.phone || '-'"></div>
                                    <div class="text-[10px] text-slate-400 font-mono" x-text="'ID: ' + m.id"></div>
                                </div>
                            </div>
                        </template>
                        <template x-if="selectedGroupMembers.length === 0">
                            <div class="py-8 text-center text-slate-400 text-xs italic">
                                Nhóm này chưa có thành viên nào.
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400">
                        Khởi tạo lúc: <span class="font-mono" x-text="selectedGroup?.created_at ? new Date(selectedGroup.created_at).toLocaleDateString('vi-VN') : '-'"></span>
                    </span>
                    <button type="button" @click="showDetailModal = false; openEditModal(selectedGroup)"
                            class="px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Chỉnh sửa thông tin</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 3: QUẢN LÝ PHÂN LOẠI NHÓM NHÂN SỰ -->
    <!-- ===================================================== -->
    <div x-show="showClassificationModal" x-cloak class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showClassificationModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 sm:p-8 overflow-hidden z-10 border border-slate-100">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-tags"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900">Phân Loại Nhóm Nhân Sự</h3>
                            <p class="text-xs text-slate-400 font-medium">Danh mục phân loại để gán cho các nhóm (phân loại ID)</p>
                        </div>
                    </div>
                    <button type="button" @click="showClassificationModal = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl cursor-pointer">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <!-- Danh sách phân loại hiện có -->
                <div class="space-y-3 mb-6">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Danh sách hiện tại:</h4>
                    <div class="max-h-48 overflow-y-auto space-y-2 pr-1">
                        <template x-for="cl in classifications" :key="cl.id">
                            <div class="p-3 rounded-2xl border border-slate-200 bg-slate-50/50 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-3 h-3 rounded-full" :class="'bg-' + cl.color + '-500'"></span>
                                    <div>
                                        <div class="font-bold text-xs text-slate-900 flex items-center gap-2">
                                            <span x-text="cl.name"></span>
                                            <span class="text-[10px] font-mono px-1.5 py-0.5 bg-slate-200 rounded text-slate-700" x-text="cl.code || 'ID: ' + cl.id"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 line-clamp-1" x-text="cl.description || 'Không có mô tả'"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-bold text-slate-500 font-mono" x-text="(cl.groups_count || 0) + ' nhóm'"></span>
                                    <button type="button" @click="deleteClassification(cl)"
                                            class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg transition-colors cursor-pointer" title="Xóa phân loại">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Form Thêm mới phân loại -->
                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-3">
                    <h4 class="text-xs font-bold text-indigo-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Thêm phân loại mới</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <input type="text" x-model="newClassification.name" placeholder="Tên phân loại *"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-indigo-200 bg-white font-bold text-slate-800 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <input type="text" x-model="newClassification.code" placeholder="Mã (vd: KD, KV)"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-indigo-200 bg-white font-mono font-bold uppercase text-slate-800 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <select x-model="newClassification.color" class="px-3 py-2 text-xs rounded-xl border border-indigo-200 bg-white font-bold text-slate-700">
                            <option value="blue">Xanh dương (Blue)</option>
                            <option value="emerald">Xanh lục (Emerald)</option>
                            <option value="amber">Hổ phách (Amber)</option>
                            <option value="purple">Tím (Purple)</option>
                            <option value="rose">Đỏ hồng (Rose)</option>
                            <option value="indigo">Chàm (Indigo)</option>
                        </select>
                        <input type="text" x-model="newClassification.description" placeholder="Mô tả chức năng phân loại..."
                               class="flex-1 px-3 py-2 text-xs rounded-xl border border-indigo-200 bg-white font-medium text-slate-800 focus:outline-none focus:border-indigo-500">
                        <button type="button" @click="saveClassification()"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors cursor-pointer shrink-0">
                            Thêm
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-5 text-right">
                    <button type="button" @click="showClassificationModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Alpine.js Application Logic -->
<script>
function staffGroupApp() {
    return {
        viewMode: 'grid', // 'grid' | 'table'
        searchKeyword: '',
        filterCategory: '',
        filterClassification: '',
        filterLeader: '',
        filterStatus: '',
        memberSearchKeyword: '',

        // Data from server
        groups: @json($groups->items()),
        categories: @json($categories),
        classifications: @json($classifications),
        users: @json($users),
        stats: {
            totalGroups: {{ $totalGroups }},
            activeGroups: {{ $activeGroups }},
            totalMembers: {{ $totalMembers }},
            totalClassifications: {{ $totalClassifications }},
            totalCategories: {{ $totalCategories }}
        },

        // Modals state
        showGroupModal: false,
        showDetailModal: false,
        showClassificationModal: false,
        modalMode: 'create', // 'create' | 'edit'
        isSubmitting: false,
        selectedGroup: null,

        // Form state
        formData: {
            id: null,
            name: '',
            code: '',
            category_ids: [],
            classification_id: '',
            user_id: '',
            user_ids: [],
            description: '',
            status: 'active'
        },

        newClassification: {
            name: '',
            code: '',
            color: 'blue',
            description: ''
        },

        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        initApp() {
            console.log('Staff Groups App initialized with ' + this.groups.length + ' groups, ' + this.categories.length + ' categories');
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        get filteredGroups() {
            return this.groups.filter(g => {
                if (this.filterCategory) {
                    const catId = Number(this.filterCategory);
                    const catIds = Array.isArray(g.category_ids) ? g.category_ids.map(Number) : [];
                    if (!catIds.includes(catId)) {
                        return false;
                    }
                }
                if (this.filterClassification && String(g.classification_id) !== String(this.filterClassification)) {
                    return false;
                }
                if (this.filterLeader && String(g.user_id) !== String(this.filterLeader)) {
                    return false;
                }
                if (this.filterStatus && g.status !== this.filterStatus) {
                    return false;
                }
                if (this.searchKeyword) {
                    const q = this.searchKeyword.toLowerCase().trim();
                    const name = (g.name || '').toLowerCase();
                    const code = (g.code || '').toLowerCase();
                    const leader = (g.leader?.name || '').toLowerCase();
                    return name.includes(q) || code.includes(q) || leader.includes(q);
                }
                return true;
            });
        },

        get filteredPickerUsers() {
            if (!this.memberSearchKeyword) return this.users;
            const q = this.memberSearchKeyword.toLowerCase().trim();
            return this.users.filter(u => {
                return (u.name || '').toLowerCase().includes(q) ||
                       (u.department || '').toLowerCase().includes(q) ||
                       String(u.id) === q;
            });
        },

        get selectedGroupMembers() {
            if (!this.selectedGroup || !Array.isArray(this.selectedGroup.user_ids)) return [];
            const ids = this.selectedGroup.user_ids.map(Number);
            return this.users.filter(u => ids.includes(Number(u.id)));
        },

        getGroupMemberUsers(g) {
            if (!g || !Array.isArray(g.user_ids)) return [];
            const ids = g.user_ids.map(Number);
            return this.users.filter(u => ids.includes(Number(u.id)));
        },

        getGroupCategories(g) {
            if (!g || !Array.isArray(g.category_ids)) return [];
            const ids = g.category_ids.map(Number);
            return this.categories.filter(c => ids.includes(Number(c.id)));
        },

        getCategoryName(catId) {
            const cat = this.categories.find(c => Number(c.id) === Number(catId));
            return cat ? cat.name : ('ID: ' + catId);
        },

        toggleCategory(catId) {
            const id = Number(catId);
            const idx = this.formData.category_ids.indexOf(id);
            if (idx > -1) {
                this.formData.category_ids.splice(idx, 1);
            } else {
                this.formData.category_ids.push(id);
            }
        },

        selectAllCategories() {
            this.formData.category_ids = this.categories.map(c => Number(c.id));
        },

        getInitials(name) {
            if (!name) return 'NV';
            const parts = name.trim().split(' ');
            if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        },

        getClassificationBadgeClass(color) {
            switch(color) {
                case 'emerald': case 'green': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
                case 'amber': case 'yellow':  return 'bg-amber-50 text-amber-700 border-amber-200';
                case 'purple': case 'violet': return 'bg-purple-50 text-purple-700 border-purple-200';
                case 'rose': case 'red':      return 'bg-rose-50 text-rose-700 border-rose-200';
                case 'indigo':                return 'bg-indigo-50 text-indigo-700 border-indigo-200';
                default:                      return 'bg-blue-50 text-blue-700 border-blue-200';
            }
        },

        openCreateModal() {
            this.modalMode = 'create';
            this.formData = {
                id: null,
                name: '',
                code: '',
                category_ids: [],
                classification_id: '',
                user_id: '',
                user_ids: [],
                description: '',
                status: 'active'
            };
            this.memberSearchKeyword = '';
            this.showGroupModal = true;
        },

        openEditModal(g) {
            this.modalMode = 'edit';
            this.formData = {
                id: g.id,
                name: g.name,
                code: g.code || '',
                category_ids: Array.isArray(g.category_ids) ? [...g.category_ids.map(Number)] : [],
                classification_id: g.classification_id || '',
                user_id: g.user_id || '',
                user_ids: Array.isArray(g.user_ids) ? [...g.user_ids.map(Number)] : [],
                description: g.description || '',
                status: g.status || 'active'
            };
            this.memberSearchKeyword = '';
            this.showGroupModal = true;
        },

        viewGroupMembers(g) {
            this.selectedGroup = g;
            this.showDetailModal = true;
        },

        toggleMember(userId) {
            const id = Number(userId);
            const idx = this.formData.user_ids.indexOf(id);
            if (idx > -1) {
                this.formData.user_ids.splice(idx, 1);
            } else {
                this.formData.user_ids.push(id);
            }
        },

        selectAllMembers() {
            this.formData.user_ids = this.users.map(u => Number(u.id));
        },

        async saveGroup() {
            if (!this.formData.name || !this.formData.name.trim()) {
                this.showToast('Vui lòng nhập tên nhóm nhân sự!', 'error');
                return;
            }

            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const url = this.modalMode === 'create' ? '/staff-groups' : ('/staff-groups/' + this.formData.id);
                const method = this.modalMode === 'create' ? 'POST' : 'PUT';

                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message);
                    this.showGroupModal = false;

                    if (this.modalMode === 'create') {
                        this.groups.unshift(data.group);
                        this.stats.totalGroups++;
                        if (data.group.status === 'active') this.stats.activeGroups++;
                    } else {
                        const idx = this.groups.findIndex(g => g.id === this.formData.id);
                        if (idx > -1) {
                            this.groups[idx] = data.group;
                        }
                    }
                    this.recalcStats();
                } else {
                    this.showToast(data.message || 'Lỗi khi lưu nhóm nhân sự', 'error');
                }
            } catch(e) {
                console.error('Error saving group:', e);
                this.showToast('Lỗi kết nối máy chủ', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteGroup(g) {
            if (!confirm('Bạn có chắc chắn muốn xóa nhóm nhân sự "' + g.name + '" không?')) {
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/staff-groups/' + g.id, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    }
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message);
                    this.groups = this.groups.filter(item => item.id !== g.id);
                    this.recalcStats();
                } else {
                    this.showToast(data.message || 'Không thể xóa nhóm', 'error');
                }
            } catch(e) {
                console.error('Error deleting group:', e);
                this.showToast('Lỗi kết nối máy chủ', 'error');
            }
        },

        async saveClassification() {
            if (!this.newClassification.name || !this.newClassification.name.trim()) {
                this.showToast('Vui lòng nhập tên phân loại!', 'error');
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/staff-group-classifications', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(this.newClassification)
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message);
                    this.classifications.push(data.classification);
                    this.stats.totalClassifications++;
                    this.newClassification = { name: '', code: '', color: 'blue', description: '' };
                } else {
                    this.showToast(data.message || 'Lỗi tạo phân loại', 'error');
                }
            } catch(e) {
                console.error('Error saving classification:', e);
                this.showToast('Lỗi kết nối máy chủ', 'error');
            }
        },

        async deleteClassification(cl) {
            if (!confirm('Xóa phân loại "' + cl.name + '"? Các nhóm thuộc phân loại này sẽ chuyển về chưa phân loại.')) {
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/staff-group-classifications/' + cl.id, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    }
                });

                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message);
                    this.classifications = this.classifications.filter(c => c.id !== cl.id);
                    this.stats.totalClassifications = this.classifications.length;
                    this.groups.forEach(g => {
                        if (g.classification_id === cl.id) {
                            g.classification_id = null;
                            g.classification = null;
                        }
                    });
                } else {
                    this.showToast(data.message || 'Không thể xóa phân loại', 'error');
                }
            } catch(e) {
                console.error('Error deleting classification:', e);
                this.showToast('Lỗi kết nối máy chủ', 'error');
            }
        },

        recalcStats() {
            this.stats.totalGroups = this.groups.length;
            this.stats.activeGroups = this.groups.filter(g => g.status === 'active').length;
            const unique = {};
            this.groups.forEach(g => {
                if (Array.isArray(g.user_ids)) {
                    g.user_ids.forEach(uid => { unique[uid] = true; });
                }
                if (g.user_id) unique[g.user_id] = true;
            });
            this.stats.totalMembers = Object.keys(unique).length;
        }
    };
}
</script>
@endsection
