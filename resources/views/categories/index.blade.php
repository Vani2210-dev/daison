@extends('layouts.app')

@section('title', 'Quản lý Danh mục Hệ thống - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="categoryManager()" x-init="initData()">
    <!-- Header Banner -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ url('/dashboard') }}" class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-folder-tree text-[#1b4a8c]"></i>
                        <span>Quản Lý Danh Mục Hệ Thống ERP</span>
                    </h1>
                    <p class="text-xs text-slate-500">Cấu hình kho bãi, chi nhánh, loại quỹ, thương hiệu, tuyến giao hàng, xe vận tải và chính sách bán hàng</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="button" @click="openImportExcelModal()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Import Excel Phân Nhóm</span>
                </button>
                <a href="{{ url('/cat_products') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                    <span>Xem Danh mục Sản phẩm & Giá</span>
                </a>
                <a href="{{ url('/cat_price_tiers') }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-xs font-bold text-indigo-700 hover:bg-indigo-100 flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-tags text-indigo-600"></i>
                    <span>Cấu hình giá</span>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pt-4 mt-3 border-t border-slate-100 text-xs font-semibold scrollbar-thin">
            <button @click="switchTab('warehouses')" :class="activeTab === 'warehouses' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-warehouse"></i>
                <span>Kho hàng (<span x-text="warehouses.length"></span>)</span>
            </button>
            <button @click="switchTab('branches')" :class="activeTab === 'branches' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-code-branch"></i>
                <span>Chi nhánh (<span x-text="branches.length"></span>)</span>
            </button>
            <button @click="switchTab('fund_types')" :class="activeTab === 'fund_types' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-wallet"></i>
                <span>Loại quỹ (<span x-text="funds.length"></span>)</span>
            </button>
            <button @click="switchTab('categories')" :class="activeTab === 'categories' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-layer-group"></i>
                <span>Phân loại (<span x-text="categories.length"></span>)</span>
            </button>
            <button @click="switchTab('brands')" :class="activeTab === 'brands' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-award"></i>
                <span>Nhãn hiệu (<span x-text="brands.length"></span>)</span>
            </button>
            <button @click="switchTab('subcategories')" :class="activeTab === 'subcategories' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-sitemap"></i>
                <span>Nhóm nhỏ (<span x-text="subcategories.length"></span>)</span>
            </button>
            <button @click="switchTab('routes')" :class="activeTab === 'routes' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-route"></i>
                <span>Tuyến GH (<span x-text="routes.length"></span>)</span>
            </button>
            <button @click="switchTab('vehicles')" :class="activeTab === 'vehicles' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-truck"></i>
                <span>Xe giao hàng (<span x-text="vehicles.length"></span>)</span>
            </button>
            <a href="{{ url('/cat_price_tiers') }}" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold border border-indigo-200">
                <i class="fa-solid fa-tags"></i>
                <span>Cấu hình giá</span>
            </a>
            <button @click="switchTab('policies')" :class="activeTab === 'policies' ? 'bg-[#1b4a8c] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-percent"></i>
                <span>Chính sách chiết khấu (<span x-text="policies.length"></span>)</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: KHO HÀNG -->
    <div x-show="activeTab === 'warehouses'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-sky-600"></i>
                    <span>Danh Sách Kho Bãi Trữ Hàng Nhựa & Vật Tư</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800">
                    <span x-text="filteredWarehouses.length"></span> / <span x-text="warehouses.length"></span> kho
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Tìm theo mã, tên kho..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-sky-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="button" @click="openCreate('warehouses')" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm Kho Hàng</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Kho</th>
                        <th class="py-2.5 px-3">Tên Kho Bãi</th>
                        <th class="py-2.5 px-3">Chi Nhánh Quản Lý</th>
                        <th class="py-2.5 px-3">Địa Chỉ / Vị Trí</th>
                        <th class="py-2.5 px-3">Người Quản Lý</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(w, index) in filteredWarehouses" :key="w.id">
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                            <td class="py-2.5 px-3 font-bold text-sky-700" x-text="w.code || ('KHO-0' + (index+1))"></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900" x-text="w.name"></td>
                            <td class="py-2.5 px-3 text-slate-600" x-text="w.branch ? w.branch.name : (getBranchName(w.branch_id) || '---')"></td>
                            <td class="py-2.5 px-3 text-slate-600" x-text="w.address || 'Nghệ An / Hà Tĩnh'"></td>
                            <td class="py-2.5 px-3 text-slate-800" x-text="w.manager_name || 'Thủ kho phụ trách'"></td>
                            <td class="py-2.5 px-3 text-center">
                                <span :class="w.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="w.is_active ? 'Hoạt động' : 'Tạm dừng'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit('warehouses', w)" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="deleteItem('warehouses', w)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredWarehouses.length === 0">
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-2xl mb-1 text-slate-300 block"></i>
                                Không tìm thấy kho bãi nào phù hợp
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: CHI NHÁNH -->
    <div x-show="activeTab === 'branches'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-code-branch text-purple-600"></i>
                    <span>Danh Sách Chi Nhánh Phân Phối Đại Sơn</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800">
                    <span x-text="filteredBranches.length"></span> / <span x-text="branches.length"></span> chi nhánh
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Tìm theo mã, tên chi nhánh..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-purple-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="button" @click="openCreate('branches')" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm Chi Nhánh</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Chi Nhánh</th>
                        <th class="py-2.5 px-3">Tên Chi Nhánh</th>
                        <th class="py-2.5 px-3">Loại Hình</th>
                        <th class="py-2.5 px-3">Địa Chỉ Trụ Sở</th>
                        <th class="py-2.5 px-3">Giám Đốc / Phụ Trách</th>
                        <th class="py-2.5 px-3">Hotline</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(b, index) in filteredBranches" :key="b.id">
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                            <td class="py-2.5 px-3 font-bold text-purple-700" x-text="b.code || ('CN-0' + (index+1))"></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900" x-text="b.name"></td>
                            <td class="py-2.5 px-3 text-slate-600" x-text="b.type || 'Chi nhánh chính'"></td>
                            <td class="py-2.5 px-3 text-slate-600" x-text="b.address || 'Khu vực Bắc Miền Trung'"></td>
                            <td class="py-2.5 px-3 text-slate-800" x-text="b.manager || 'Trưởng chi nhánh'"></td>
                            <td class="py-2.5 px-3 text-slate-600 font-mono" x-text="b.phone || '---'"></td>
                            <td class="py-2.5 px-3 text-center">
                                <span :class="b.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="b.is_active ? 'Hoạt động' : 'Tạm dừng'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit('branches', b)" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="deleteItem('branches', b)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredBranches.length === 0">
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-building text-2xl mb-1 text-slate-300 block"></i>
                                Không tìm thấy chi nhánh nào phù hợp
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: LOẠI QUỸ -->
    <div x-show="activeTab === 'fund_types'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-emerald-600"></i>
                    <span>Tài Khoản Quỹ Tiền Mặt & Ngân Hàng Doanh Nghiệp</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                    <span x-text="filteredFunds.length"></span> / <span x-text="funds.length"></span> tài khoản quỹ
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Tìm theo mã, tên quỹ, số TK..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="button" @click="openCreate('fund_types')" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm Loại Quỹ</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Quỹ</th>
                        <th class="py-2.5 px-3">Tên Tài Khoản Quỹ</th>
                        <th class="py-2.5 px-3">Ngân Hàng</th>
                        <th class="py-2.5 px-3">Số Tài Khoản</th>
                        <th class="py-2.5 px-3 text-right">Số Dư Hiện Tại</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(f, index) in filteredFunds" :key="f.id">
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                            <td class="py-2.5 px-3 font-bold text-emerald-700" x-text="f.code || ('Q-0' + (index+1))"></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900" x-text="f.name"></td>
                            <td class="py-2.5 px-3 text-slate-600" x-text="f.bank_name || 'Tiền mặt nội bộ'"></td>
                            <td class="py-2.5 px-3 text-slate-700 font-mono font-bold" x-text="f.account_number || '---'"></td>
                            <td class="py-2.5 px-3 text-right font-bold text-slate-900" x-text="formatCurrency(f.current_balance || 0)"></td>
                            <td class="py-2.5 px-3 text-center">
                                <span :class="f.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="f.is_active ? 'Sẵn sàng' : 'Khóa'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit('fund_types', f)" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="deleteItem('fund_types', f)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredFunds.length === 0">
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-wallet text-2xl mb-1 text-slate-300 block"></i>
                                Không tìm thấy loại quỹ nào phù hợp
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: PHÂN LOẠI -->
    <div x-show="activeTab === 'categories'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-blue-600"></i>
                    <span>Danh Mục Phân Loại Ngành Hàng (Ống Nhựa, Phụ Tùng, Thiết Bị Điện Nước)</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                    <span x-text="filteredCategories.length"></span> / <span x-text="categories.length"></span> nhóm hàng
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Tìm theo mã, tên phân loại..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="button" @click="openImportExcelModal()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Import Excel</span>
                </button>
                <button type="button" @click="openCreate('categories')" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm Phân Loại</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Nhóm</th>
                        <th class="py-2.5 px-3">Tên Phân Loại Ngành Hàng</th>
                        <th class="py-2.5 px-3">Người Phụ Trách</th>
                        <th class="py-2.5 px-3">Mô Tả / Quy Cách</th>
                        <th class="py-2.5 px-3 text-center">Số Mặt Hàng</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(c, index) in filteredCategories" :key="c.id">
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                            <td class="py-2.5 px-3 font-bold text-blue-700" x-text="c.code || ('PL-0' + (index+1))"></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900" x-text="c.name"></td>
                            <td class="py-2.5 px-3 text-slate-800">
                                <div class="flex items-center gap-1.5 font-medium">
                                    <i class="fa-solid fa-user-tie text-blue-600 text-[11px]"></i>
                                    <span x-text="c.manager_name || 'Chưa phân công'"></span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-500" x-text="c.description || 'Vật tư phân phối chính hãng'"></td>
                            <td class="py-2.5 px-3 text-center font-bold text-slate-800" x-text="c.products_count || 0"></td>
                            <td class="py-2.5 px-3 text-center">
                                <span :class="c.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="c.is_active ? 'Hiển thị' : 'Ẩn'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit('categories', c)" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="deleteItem('categories', c)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredCategories.length === 0">
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-layer-group text-2xl mb-1 text-slate-300 block"></i>
                                Không tìm thấy phân loại nào phù hợp
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 5: NHÃN HIỆU -->
    <div x-show="activeTab === 'brands'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-award text-amber-600"></i>
                    <span>Thương Hiệu & Nhà Sản Xuất (Tiền Phong, Hoa Sen, Bình Minh, Sino, Panasonic)</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                    <span x-text="filteredBrands.length"></span> / <span x-text="brands.length"></span> thương hiệu
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Tìm theo mã, tên nhãn hiệu..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-amber-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="button" @click="openImportExcelModal()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Import Excel</span>
                </button>
                <button type="button" @click="openCreate('brands')" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm Nhãn Hiệu</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Hiệu</th>
                        <th class="py-2.5 px-3">Tên Thương Hiệu</th>
                        <th class="py-2.5 px-3">Xuất Xứ / Nhà Máy</th>
                        <th class="py-2.5 px-3">Mô Tả / Chính Sách</th>
                        <th class="py-2.5 px-3 text-center">Số Mặt Hàng</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(br, index) in filteredBrands" :key="br.id">
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                            <td class="py-2.5 px-3 font-bold text-slate-600" x-text="br.code || ('TH-0' + (index+1))"></td>
                            <td class="py-2.5 px-3 font-bold text-slate-900 flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-amber-50 text-amber-700 flex items-center justify-center font-black text-xs border border-amber-200" x-text="br.name ? br.name.charAt(0) : 'B'"></span>
                                <span x-text="br.name"></span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600" x-text="br.country || 'Việt Nam'"></td>
                            <td class="py-2.5 px-3 text-slate-500" x-text="br.description || 'Chính sách đại lý chính thức'"></td>
                            <td class="py-2.5 px-3 text-center font-bold text-slate-800" x-text="br.products_count || 0"></td>
                            <td class="py-2.5 px-3 text-center">
                                <span :class="br.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="br.is_active ? 'Chính hãng' : 'Ẩn'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit('brands', br)" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="deleteItem('brands', br)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredBrands.length === 0">
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-award text-2xl mb-1 text-slate-300 block"></i>
                                Không tìm thấy thương hiệu nào phù hợp
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 6: NHÓM NHỎ (SUBCATEGORIES) -->
    <div x-show="activeTab === 'subcategories'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-sitemap text-teal-600"></i>
                    <span>Danh Mục Nhóm Nhỏ / Nhóm Con Theo Từng Ngành Hàng & Thương Hiệu</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800">
                    <span x-text="filteredSubcategories.length"></span> / <span x-text="subcategories.length"></span> nhóm nhỏ
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Tìm theo tên nhóm, mã, hiệu..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="button" @click="openImportExcelModal()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Import Excel</span>
                </button>
                <button type="button" @click="openCreate('subcategories')" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm Nhóm Nhỏ</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Nhóm Con</th>
                        <th class="py-2.5 px-3">Tên Nhóm Nhỏ / Con</th>
                        <th class="py-2.5 px-3">Ngành Hàng (Loại)</th>
                        <th class="py-2.5 px-3">Thương Hiệu (Nhà SX)</th>
                        <th class="py-2.5 px-3">Mô Tả / Quy Cách</th>
                        <th class="py-2.5 px-3 text-center">Số Mặt Hàng</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(sub, index) in filteredSubcategories" :key="sub.id">
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                            <td class="py-2.5 px-3 font-bold text-teal-700" x-text="sub.code || ('NN-' + (index+1))"></td>
                            <td class="py-2.5 px-3 font-bold text-slate-900" x-text="sub.name"></td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold border border-blue-200 text-[11px]" x-text="sub.category ? sub.category.name : getCategoryName(sub.category_id)"></span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 font-semibold border border-amber-200 text-[11px]" x-text="sub.brand ? sub.brand.name : (getBrandName(sub.brand_id) || 'Chung')"></span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-500" x-text="sub.description || '---'"></td>
                            <td class="py-2.5 px-3 text-center font-bold text-slate-800" x-text="sub.products_count || 0"></td>
                            <td class="py-2.5 px-3 text-center">
                                <span :class="sub.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="sub.is_active ? 'Hoạt động' : 'Tạm dừng'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit('subcategories', sub)" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="deleteItem('subcategories', sub)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredSubcategories.length === 0">
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-sitemap text-2xl mb-1 text-slate-300 block"></i>
                                Không tìm thấy nhóm nhỏ nào phù hợp
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 6: TUYẾN GIAO HÀNG -->
    <div x-show="activeTab === 'routes'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-route text-sky-600"></i>
                    <span>Tuyến Đường Vận Chuyển & Giao Hàng Cho Đại Lý</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800">
                    <span x-text="filteredRoutes.length"></span> / <span x-text="routes.length"></span> tuyến
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Tìm theo mã, tên tuyến, địa bàn..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-sky-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <button type="button" @click="openCreate('routes')" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm Tuyến GH</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã Tuyến</th>
                        <th class="py-2.5 px-3">Tên Tuyến Giao Hàng</th>
                        <th class="py-2.5 px-3">Kho Xuất Phát</th>
                        <th class="py-2.5 px-3">Các Địa Bàn / Điểm Dừng Đi Qua</th>
                        <th class="py-2.5 px-3 text-right">Khoảng Cách (km)</th>
                        <th class="py-2.5 px-3">Xe Phụ Trách</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(r, index) in filteredRoutes" :key="r.id">
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                            <td class="py-2.5 px-3 font-bold text-sky-700" x-text="r.code || ('TGH-0' + (index+1))"></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900" x-text="r.name"></td>
                            <td class="py-2.5 px-3 text-slate-600" x-text="r.departure_warehouse || 'Kho Tổng Vinh'"></td>
                            <td class="py-2.5 px-3 text-slate-700" x-text="formatStops(r.stops)"></td>
                            <td class="py-2.5 px-3 text-right font-semibold text-slate-800" x-text="r.distance_km ? (r.distance_km + ' km') : 'Tùy đơn'"></td>
                            <td class="py-2.5 px-3 text-slate-700 font-medium" x-text="r.assigned_vehicle_plate || 'Chưa gán'"></td>
                            <td class="py-2.5 px-3 text-center">
                                <span :class="r.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="r.is_active ? 'Lưu thông' : 'Tạm dừng'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit('routes', r)" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" @click="deleteItem('routes', r)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredRoutes.length === 0">
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-route text-2xl mb-1 text-slate-300 block"></i>
                                Không tìm thấy tuyến giao hàng nào phù hợp
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 7: XE GIAO HÀNG & PHÂN CÔNG ĐỘI XE -->
    <div x-show="activeTab === 'vehicles'" class="space-y-4">
        <!-- 7.1 DANH SÁCH XE (Hiện khi KHÔNG trong chế độ xem phân công) -->
        <div x-show="!selectedVehicleForAssignment" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-truck text-sky-600"></i>
                        <span>Danh Sách Xe Giao Hàng & Đội Xe Vận Tải Đại Sơn</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800">
                        <span x-text="filteredVehicles.length"></span> / <span x-text="vehicles.length"></span> phương tiện
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" x-model="search" placeholder="Tìm theo biển số, tài xế..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-sky-500 bg-white">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                    <button type="button" @click="openCreate('vehicles')" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>Thêm Xe Giao Hàng</span>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                            <th class="py-2.5 px-3 w-12 text-center">STT</th>
                            <th class="py-2.5 px-3">Biển Số Xe</th>
                            <th class="py-2.5 px-3">Loại Xe / Tải Trọng</th>
                            <th class="py-2.5 px-3">Tài Xế Phụ Trách</th>
                            <th class="py-2.5 px-3">Số Điện Thoại</th>
                            <th class="py-2.5 px-3">Tuyến Phụ Trách</th>
                            <th class="py-2.5 px-3 text-center">Trạng Thái Xe</th>
                            <th class="py-2.5 px-3 text-center w-36">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <template x-for="(v, index) in filteredVehicles" :key="v.id">
                            <tr class="hover:bg-sky-50/50 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                                <td class="py-2.5 px-3 font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded bg-slate-800 text-amber-300 font-mono text-[11px] font-black border border-slate-700 tracking-wider" x-text="v.plate_number"></span>
                                </td>
                                <td class="py-2.5 px-3 text-slate-700 font-medium">
                                    <span x-text="v.vehicle_type || 'Xe tải vận chuyển'"></span>
                                    <span class="text-slate-400 font-normal" x-show="v.capacity_kg"> (<span x-text="v.capacity_kg"></span> kg)</span>
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800" x-text="v.driver ? v.driver.name : (getDriverName(v.driver_id) || 'Chưa phân công')"></td>
                                <td class="py-2.5 px-3 text-slate-600 font-mono" x-text="v.driver ? v.driver.phone : (getDriverPhone(v.driver_id) || '---')"></td>
                                <td class="py-2.5 px-3 text-slate-600" x-text="v.delivery_route ? v.delivery_route.name : (getRouteName(v.delivery_route_id) || '---')"></td>
                                <td class="py-2.5 px-3 text-center">
                                    <span :class="{
                                        'bg-emerald-100 text-emerald-800': v.status === 'Sẵn sàng' || !v.status,
                                        'bg-amber-100 text-amber-800': v.status === 'Đang giao hàng',
                                        'bg-rose-100 text-rose-800': v.status === 'Bảo dưỡng'
                                    }" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="v.status || 'Sẵn sàng'"></span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openAssignment(v)" class="px-2 py-1 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] flex items-center gap-1 transition-colors cursor-pointer shadow-2xs border border-indigo-200/70" title="Thiết lập phân công lái xe, phụ xe & tuyến">
                                            <i class="fa-solid fa-user-check text-[11px]"></i>
                                            <span>Phân công</span>
                                        </button>
                                        <button type="button" @click="openEdit('vehicles', v)" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="deleteItem('vehicles', v)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredVehicles.length === 0">
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-truck text-2xl mb-1 text-slate-300 block"></i>
                                    Không tìm thấy xe giao hàng nào phù hợp
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 7.2 GIAO DIỆN PHÂN CÔNG XE (Đúng chuẩn thiết kế Ảnh 1 & Ảnh 2) -->
        <div x-show="selectedVehicleForAssignment" class="space-y-4 animate-in fade-in duration-200">
            
            <!-- Top Navigation Bar & Quick Vehicle Switcher -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <button type="button" @click="selectedVehicleForAssignment = null" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all flex items-center gap-1.5 font-bold text-xs cursor-pointer active:scale-95 shrink-0" title="Quay lại danh mục xe giao hàng">
                        <i class="fa-solid fa-arrow-left text-slate-600"></i>
                        <span class="hidden sm:inline">Quay Lại Danh Mục</span>
                    </button>
                    <div class="h-6 w-px bg-slate-200"></div>
                    <div>
                        <div class="flex items-center space-x-2 text-[11px] text-slate-500 font-medium">
                            <span>Danh mục</span>
                            <span>/</span>
                            <span>Xe giao hàng</span>
                            <span>/</span>
                            <span class="text-indigo-600 font-bold">Phân công xe</span>
                        </div>
                        <h1 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-user-check text-indigo-600"></i>
                            <span>Phân Công Lái Xe & Phụ Xe Cho Đội Xe Vận Tải</span>
                        </h1>
                    </div>
                </div>

                <!-- Quick Vehicle Switcher Dropdown -->
                <div class="flex items-center space-x-2 w-full md:w-auto justify-end">
                    <span class="text-xs text-slate-500 font-medium shrink-0">Đang chọn xe:</span>
                    <div class="relative">
                        <select :value="selectedVehicleForAssignment?.id" @change="switchAssignmentVehicle($event.target.value)" class="pl-3 pr-8 py-2 bg-indigo-50/80 border border-indigo-200 rounded-xl text-xs font-mono font-extrabold text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 cursor-pointer appearance-none">
                            <template x-for="veh in vehicles" :key="veh.id">
                                <option :value="veh.id" x-text="veh.plate_number + ' - ' + (veh.vehicle_type || 'Xe tải')"></option>
                            </template>
                        </select>
                        <i class="fa-solid fa-chevron-down text-[10px] absolute right-2.5 top-3 text-indigo-600 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <!-- Vehicle Summary Banner Card -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-5 shadow-lg border border-slate-800">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    
                    <!-- Col 1: Plate & Vehicle details -->
                    <div class="flex items-start space-x-3.5 sm:border-r border-slate-800/80 sm:pr-4">
                        <div class="px-3 py-2 bg-amber-300 text-slate-950 font-black font-mono text-base rounded-xl border-2 border-slate-900 shadow-md tracking-wider flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-truck text-slate-900"></i>
                            <span x-text="selectedVehicleForAssignment?.plate_number"></span>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-white" x-text="selectedVehicleForAssignment?.vehicle_type || 'Xe tải vận chuyển'"></h3>
                            <p class="text-xs text-slate-400 mt-0.5">Tải trọng: <span class="text-white font-mono font-bold" x-text="Number(selectedVehicleForAssignment?.capacity_kg || 2500).toLocaleString('vi-VN') + ' kg'"></span></p>
                            <div class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-400/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span x-text="selectedVehicleForAssignment?.status || 'Sẵn sàng'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Col 2: Current Assigned Route -->
                    <div class="space-y-1 sm:border-r border-slate-800/80 sm:pr-4">
                        <div class="flex items-center space-x-1.5 text-xs text-slate-400">
                            <i class="fa-solid fa-paper-plane text-emerald-400 text-[11px]"></i>
                            <span class="font-semibold uppercase text-[10px] tracking-wider text-emerald-300">Tuyến GH Phụ Trách</span>
                        </div>
                        <template x-if="getCurrentAssignedRoute()">
                            <div class="space-y-0.5">
                                <p class="text-xs font-extrabold text-emerald-300 leading-snug line-clamp-2" :title="getCurrentAssignedRoute()?.name" x-text="getCurrentAssignedRoute()?.name"></p>
                                <p class="text-[10.5px] text-slate-300 line-clamp-1" x-text="getCurrentAssignedRoute()?.stops || getCurrentAssignedRoute()?.departure_warehouse || ''"></p>
                                <p class="text-[10px] font-mono text-emerald-400/90 font-bold" x-show="getCurrentAssignedRoute()?.distance_km" x-text="'Cự ly: ' + getCurrentAssignedRoute()?.distance_km + ' km'"></p>
                            </div>
                        </template>
                        <template x-if="!getCurrentAssignedRoute()">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-slate-400 italic">-- Chưa gán tuyến --</p>
                                <p class="text-[10px] text-slate-500">Chọn tuyến cố định ở form bên dưới</p>
                            </div>
                        </template>
                    </div>

                    <!-- Col 3: Person in Charge -->
                    <div class="space-y-1 lg:border-r border-slate-800/80 lg:pr-4">
                        <div class="flex items-center space-x-1.5 text-xs text-slate-400">
                            <i class="fa-solid fa-shield-halved text-sky-400 text-[11px]"></i>
                            <span class="font-semibold uppercase text-[10px] tracking-wider text-sky-300">Người Phụ Trách Quản Lý</span>
                        </div>
                        <p class="text-sm font-extrabold text-white" x-text="asgForm.manager_name || 'Hoàng Văn Khoa'"></p>
                        <p class="text-xs font-mono text-slate-300 flex items-center gap-1">
                            <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                            <span x-text="asgForm.manager_phone || '0988 222 111'"></span>
                        </p>
                        <p class="text-[11px] text-slate-400 italic">Chịu trách nhiệm bảo quản & điều động</p>
                    </div>

                    <!-- Col 4: Current Driver -->
                    <div class="space-y-1 sm:border-r border-slate-800/80 sm:pr-4">
                        <div class="flex items-center space-x-1.5 text-xs text-slate-400">
                            <i class="fa-solid fa-user text-indigo-400 text-[11px]"></i>
                            <span class="font-semibold uppercase text-[10px] tracking-wider text-indigo-300">Lái Xe Chính Hiện Tại</span>
                        </div>
                        <p class="text-sm font-extrabold text-white" x-text="selectedVehicleForAssignment?.driver ? selectedVehicleForAssignment.driver.name : (getDriverName(selectedVehicleForAssignment?.driver_id) || asgForm.driver_name || 'Chưa phân công')"></p>
                        <p class="text-xs font-mono text-slate-300 flex items-center gap-1">
                            <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                            <span x-text="selectedVehicleForAssignment?.driver ? selectedVehicleForAssignment.driver.phone : (getDriverPhone(selectedVehicleForAssignment?.driver_id) || asgForm.driver_phone || '---')"></span>
                        </p>
                        <p class="text-[11px] text-slate-400" x-text="getCurrentActiveAssignment() ? ('Bắt đầu: ' + getCurrentActiveAssignment().effectiveFrom) : 'Phụ trách xe giao hàng'"></p>
                    </div>

                    <!-- Col 5: Current Copilot -->
                    <div class="space-y-1">
                        <div class="flex items-center space-x-1.5 text-xs text-slate-400">
                            <i class="fa-solid fa-users text-purple-400 text-[11px]"></i>
                            <span class="font-semibold uppercase text-[10px] tracking-wider text-purple-300">Phụ Xe Hiện Tại</span>
                        </div>
                        <p class="text-sm font-extrabold text-white" x-text="selectedVehicleForAssignment?.copilot_name || asgForm.copilot_name || 'Không có phụ xe'"></p>
                        <p class="text-xs font-mono text-slate-300 flex items-center gap-1">
                            <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                            <span x-text="asgForm.copilot_phone || '---'"></span>
                        </p>
                        <p class="text-[11px] text-slate-400">Hỗ trợ bốc dỡ & kiểm đếm hàng</p>
                    </div>

                </div>
            </div>

            <!-- Main Grid: Form Thiết Lập Phân Công (Trái) & Bảng Lịch Sử (Phải) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                
                <!-- Left Column: Form Thiết Lập Phân Công Mới (5 cols - Ảnh 1) -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                        
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-user-check text-base"></i>
                                </div>
                                <div>
                                    <h2 class="font-extrabold text-slate-900 text-sm">Thiết Lập Phân Công Mới</h2>
                                    <p class="text-xs text-slate-500">Cập nhật tài xế, phụ xe & ca làm việc cho xe <strong class="text-indigo-900 font-mono" x-text="selectedVehicleForAssignment?.plate_number"></strong></p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="saveAssignment()" class="space-y-3.5 text-xs">
                            
                            <!-- 1. Lái Xe Chính (Tài Xế) * (Bắt buộc) -->
                            <div class="p-3.5 bg-indigo-50/50 border border-indigo-100 rounded-xl space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="font-extrabold text-indigo-950 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user text-indigo-600 text-xs"></i>
                                        <span>1. Lái Xe Chính (Tài Xế) *</span>
                                    </label>
                                    <span class="text-[10px] text-indigo-700 font-bold bg-indigo-100/80 px-2 py-0.5 rounded">
                                        Bắt buộc
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-[11px] text-slate-500 font-medium mb-1">Chọn từ danh sách tài xế công ty:</label>
                                    <select x-model="asgForm.driver_select" @change="handleSelectDriverPreset($event.target.value)" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 cursor-pointer">
                                        <option value="CUSTOM">-- Nhập tên tài xế tùy chỉnh bên dưới --</option>
                                        <template x-for="d in asgPresetDrivers" :key="d.name">
                                            <option :value="d.name" x-text="d.name + ' (' + d.phone + ') - ' + d.license"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
                                    <div>
                                        <label class="block text-[11px] text-slate-600 font-bold mb-1">Họ & Tên Lái Xe *</label>
                                        <input type="text" required placeholder="VD: Bùi Văn Thành..." x-model="asgForm.driver_name" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-slate-600 font-bold mb-1">Số Điện Thoại</label>
                                        <input type="text" placeholder="0966 334 455..." x-model="asgForm.driver_phone" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Phụ Xe (Lái Phụ / Giao Nhận) (Tùy chọn) -->
                            <div class="p-3.5 bg-purple-50/50 border border-purple-100 rounded-xl space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="font-extrabold text-purple-950 flex items-center gap-1.5">
                                        <i class="fa-solid fa-users text-purple-600 text-xs"></i>
                                        <span>2. Phụ Xe (Lái Phụ / Giao Nhận)</span>
                                    </label>
                                    <span class="text-[10px] text-purple-700 font-bold bg-purple-100/80 px-2 py-0.5 rounded">
                                        Tùy chọn
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-[11px] text-slate-500 font-medium mb-1">Chọn từ danh sách phụ xe:</label>
                                    <select x-model="asgForm.copilot_select" @change="handleSelectCopilotPreset($event.target.value)" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500 cursor-pointer">
                                        <option value="CUSTOM">-- Nhập tên phụ xe tùy chỉnh bên dưới --</option>
                                        <template x-for="c in asgPresetCopilots" :key="c.name">
                                            <option :value="c.name" x-text="c.name + ' (' + c.phone + ') - ' + c.role"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
                                    <div>
                                        <label class="block text-[11px] text-slate-600 font-bold mb-1">Họ & Tên Phụ Xe</label>
                                        <input type="text" placeholder="VD: Lê Văn Nam..." x-model="asgForm.copilot_name" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-slate-600 font-bold mb-1">Số Điện Thoại</label>
                                        <input type="text" placeholder="0978 222 333..." x-model="asgForm.copilot_phone" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-medium text-slate-900 focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Người Phụ Trách Quản Lý Xe -->
                            <div class="p-3.5 bg-sky-50/50 border border-sky-100 rounded-xl space-y-2">
                                <label class="font-extrabold text-sky-950 flex items-center gap-1.5">
                                    <i class="fa-solid fa-shield-halved text-sky-600 text-xs"></i>
                                    <span>3. Người Phụ Trách Quản Lý Xe</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div>
                                        <select x-model="asgForm.manager_select" @change="handleSelectManagerPreset($event.target.value)" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 cursor-pointer">
                                            <template x-for="m in asgPresetManagers" :key="m.name">
                                                <option :value="m.name" x-text="m.name + ' (' + m.phone + ')'"></option>
                                            </template>
                                            <option value="CUSTOM">-- Người phụ trách khác --</option>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="text" placeholder="SĐT người phụ trách..." x-model="asgForm.manager_phone" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-medium text-slate-900">
                                    </div>
                                </div>
                            </div>

                            <!-- Timing & Effective Date -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1 flex items-center gap-1">
                                        <i class="fa-solid fa-calendar-days text-slate-500 text-xs"></i>
                                        <span>Áp Dụng Từ Ngày *</span>
                                    </label>
                                    <input type="date" required x-model="asgForm.effective_from" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500/30">
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="font-bold text-slate-700 flex items-center gap-1">
                                            <i class="fa-solid fa-clock text-slate-500 text-xs"></i>
                                            <span>Đến Ngày</span>
                                        </label>
                                        <label class="flex items-center space-x-1 cursor-pointer select-none">
                                            <input type="checkbox" x-model="asgForm.is_indefinite" class="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                                            <span class="text-[10.5px] text-indigo-700 font-bold">Vô thời hạn</span>
                                        </label>
                                    </div>
                                    <input type="date" :disabled="asgForm.is_indefinite" x-model="asgForm.effective_to" class="w-full px-3 py-2 border rounded-xl text-xs font-medium" :class="asgForm.is_indefinite ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : 'border-slate-300 focus:ring-2 focus:ring-indigo-500/30'">
                                </div>
                            </div>

                            <!-- 4. Tuyến Giao Hàng Phụ Trách * (Chọn từ dữ liệu tuyến) -->
                            <div class="p-3.5 bg-emerald-50/60 border border-emerald-200/80 rounded-xl space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="font-extrabold text-emerald-950 flex items-center gap-1.5">
                                        <i class="fa-solid fa-paper-plane text-emerald-600 text-xs"></i>
                                        <span>4. Tuyến Giao Hàng Phụ Trách *</span>
                                    </label>
                                    <span class="text-[10px] text-emerald-700 font-bold bg-emerald-100/90 px-2 py-0.5 rounded">
                                        Chọn từ dữ liệu tuyến
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-[11px] text-slate-500 font-medium mb-1">
                                        Chọn tuyến giao hàng phụ trách cố định cho xe:
                                    </label>
                                    <select x-model="asgForm.route_id" @change="handleSelectRoute($event.target.value)" class="w-full px-3 py-2.5 bg-white border border-emerald-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 cursor-pointer shadow-2xs">
                                        <option value="">-- Chọn tuyến giao hàng từ dữ liệu hệ thống --</option>
                                        <template x-for="r in routes" :key="r.id">
                                            <option :value="r.id" x-text="r.name + (getConflictVehicle(r.id) ? ' (Hiện gán xe ' + getConflictVehicle(r.id).plate_number + ')' : '')"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Card chi tiết lộ trình tuyến đã chọn -->
                                <template x-if="getSelectedRouteObj()">
                                    <div class="bg-white p-3 rounded-xl border border-emerald-200 text-xs space-y-1.5 shadow-2xs animate-in fade-in">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5 font-extrabold text-emerald-900 text-xs truncate">
                                                <i class="fa-solid fa-location-dot text-emerald-600 shrink-0"></i>
                                                <span class="truncate" x-text="getSelectedRouteObj()?.name"></span>
                                            </div>
                                            <span class="text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded shrink-0" x-text="getSelectedRouteObj()?.code || 'TUYEN'"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-snug" x-show="getSelectedRouteObj()?.stops || getSelectedRouteObj()?.departure_warehouse">
                                            <span class="font-bold text-slate-700">Khu vực / Trạm dừng:</span>
                                            <span x-text="getSelectedRouteObj()?.stops || getSelectedRouteObj()?.departure_warehouse"></span>
                                        </p>
                                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                                            <span x-show="getSelectedRouteObj()?.distance_km">Cự ly ước tính: <strong class="text-slate-800" x-text="getSelectedRouteObj()?.distance_km + ' km'"></strong></span>
                                            <span x-show="getSelectedRouteObj()?.toll_fee">Phí cầu đường: <strong class="text-emerald-700" x-text="Number(getSelectedRouteObj()?.toll_fee).toLocaleString('vi-VN') + ' đ'"></strong></span>
                                        </div>
                                        <template x-if="getConflictVehicle(asgForm.route_id)">
                                            <div class="p-2 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-800 flex items-start gap-1.5 mt-1">
                                                <i class="fa-solid fa-triangle-exclamation text-amber-600 shrink-0 mt-0.5"></i>
                                                <span>
                                                    Tuyến này hiện đang gán cho xe <strong class="font-mono text-amber-950" x-text="getConflictVehicle(asgForm.route_id)?.plate_number"></strong>. Khi lưu phân công, tuyến sẽ tự động chuyển sang xe <strong x-text="selectedVehicleForAssignment?.plate_number"></strong>.
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!getSelectedRouteObj()">
                                    <p class="text-[11px] text-slate-400 italic">
                                        Vui lòng chọn 1 tuyến giao hàng từ danh sách có sẵn phía trên thay vì nhập văn bản tự do.
                                    </p>
                                </template>
                            </div>

                            <!-- 5. Ca Làm Việc -->
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                                <label class="block font-bold text-slate-700 text-xs">5. Ca Làm Việc</label>
                                <select x-model="asgForm.shift" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium bg-white focus:ring-2 focus:ring-indigo-500/30 cursor-pointer">
                                    <option value="Toàn thời gian (07:30 - 17:30)">Toàn thời gian (07:30 - 17:30)</option>
                                    <option value="Ca ngày (07:00 - 15:00)">Ca ngày (07:00 - 15:00)</option>
                                    <option value="Ca chiều (14:00 - 22:00)">Ca chiều (14:00 - 22:00)</option>
                                    <option value="Theo chuyến liên tỉnh">Theo chuyến liên tỉnh</option>
                                </select>
                            </div>

                            <!-- Người Giao Việc / Phê Duyệt -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Người Giao Việc / Phê Duyệt</label>
                                <input type="text" x-model="asgForm.assigned_by" placeholder="Họ tên người phân công..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500/30">
                            </div>

                            <!-- Ghi Chú & Bàn Giao Xe -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Ghi Chú & Bàn Giao Xe</label>
                                <textarea rows="2" x-model="asgForm.notes" placeholder="Ghi chú bàn giao chìa khóa xe, dụng cụ ràng buộc hàng, tình trạng xăng dầu..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500/30"></textarea>
                            </div>

                            <!-- Action Submit Button -->
                            <div class="pt-1">
                                <button type="submit" :disabled="isSavingAsg" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2 cursor-pointer active:scale-98 disabled:opacity-50">
                                    <i x-show="isSavingAsg" class="fa-solid fa-spinner fa-spin"></i>
                                    <i x-show="!isSavingAsg" class="fa-solid fa-user-check"></i>
                                    <span x-text="isSavingAsg ? 'Đang lưu áp dụng...' : 'Xác Nhận & Lưu Áp Dụng Phân Công'"></span>
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

                <!-- Right Column: Lịch Sử Phân Công Lái Xe & Phụ Xe (7 cols - Ảnh 2) -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col h-full">
                        
                        <!-- History Header & Controls -->
                        <div class="p-4 border-b border-slate-200 space-y-3 bg-slate-50/60">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                        <i class="fa-solid fa-clipboard-list text-base"></i>
                                    </div>
                                    <div>
                                        <h2 class="font-extrabold text-slate-900 text-sm">Lịch Sử Phân Công Lái Xe & Phụ Xe</h2>
                                        <p class="text-xs text-slate-500">
                                            Tổng cộng <strong class="text-indigo-600 font-bold" x-text="filteredAssignments.length"></strong> đợt phân công
                                        </p>
                                    </div>
                                </div>

                                <!-- Filter toggle: only current vehicle vs all -->
                                <div class="flex items-center space-x-2 self-start sm:self-auto">
                                    <label class="flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold cursor-pointer shadow-2xs hover:bg-slate-50 select-none">
                                        <input type="checkbox" x-model="viewOnlyCurrentVehicle" class="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                                        <span class="text-slate-700">Chỉ xe <span class="font-mono font-bold" x-text="selectedVehicleForAssignment?.plate_number"></span></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Filters Bar: Search & Status Pills -->
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-1">
                                <div class="relative w-full sm:w-64">
                                    <i class="fa-solid fa-magnifying-glass text-xs absolute left-3 top-2.5 text-slate-400"></i>
                                    <input type="text" placeholder="Tìm theo tài xế, phụ xe, tuyến..." x-model="asgSearch" class="w-full pl-8 pr-7 py-1.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-medium">
                                    <button type="button" x-show="asgSearch" @click="asgSearch = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>

                                <div class="flex items-center space-x-1 bg-slate-200/70 p-1 rounded-xl text-[11px] font-bold w-full sm:w-auto justify-center sm:justify-start">
                                    <button type="button" @click="asgStatusFilter = 'all'" class="px-2.5 py-1 rounded-lg transition-all cursor-pointer" :class="asgStatusFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                        Tất cả
                                    </button>
                                    <button type="button" @click="asgStatusFilter = 'Đang áp dụng'" class="px-2.5 py-1 rounded-lg transition-all cursor-pointer" :class="asgStatusFilter === 'Đang áp dụng' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                        Đang áp dụng
                                    </button>
                                    <button type="button" @click="asgStatusFilter = 'Hoàn thành'" class="px-2.5 py-1 rounded-lg transition-all cursor-pointer" :class="asgStatusFilter === 'Hoàn thành' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                        Hoàn thành
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- History Table -->
                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                                        <th class="py-3 px-3 w-10 text-center">STT</th>
                                        <th class="py-3 px-3">Xe & Mã</th>
                                        <th class="py-3 px-3">Lái Xe Chính</th>
                                        <th class="py-3 px-3">Phụ Xe</th>
                                        <th class="py-3 px-3">Thời Gian</th>
                                        <th class="py-3 px-3">Tuyến / Ca</th>
                                        <th class="py-3 px-3 text-center">Trạng Thái</th>
                                        <th class="py-3 px-3 text-center w-20">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                    <template x-for="(rec, idx) in filteredAssignments" :key="rec.id">
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <!-- STT -->
                                            <td class="py-3 px-3 text-center text-slate-400 font-bold align-middle" x-text="idx + 1"></td>

                                            <!-- Xe & Mã -->
                                            <td class="py-3 px-3 align-middle">
                                                <div class="flex flex-col">
                                                    <span class="font-mono font-extrabold text-indigo-950 text-xs" x-text="rec.vehiclePlate"></span>
                                                    <span class="font-mono text-[10px] text-slate-400" x-text="rec.id"></span>
                                                </div>
                                            </td>

                                            <!-- Lái xe chính -->
                                            <td class="py-3 px-3 align-middle">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-slate-900 text-xs flex items-center gap-1">
                                                        <i class="fa-solid fa-user text-indigo-600 text-[10px]"></i>
                                                        <span x-text="rec.driverName"></span>
                                                    </span>
                                                    <span class="text-[11px] font-mono text-slate-500" x-show="rec.driverPhone" x-text="rec.driverPhone"></span>
                                                </div>
                                            </td>

                                            <!-- Phụ xe -->
                                            <td class="py-3 px-3 align-middle">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-slate-800 text-xs flex items-center gap-1">
                                                        <i class="fa-solid fa-users text-purple-600 text-[10px]"></i>
                                                        <span x-text="rec.copilotName || 'Không có phụ xe'"></span>
                                                    </span>
                                                    <span class="text-[11px] font-mono text-slate-500" x-show="rec.copilotPhone" x-text="rec.copilotPhone"></span>
                                                </div>
                                            </td>

                                            <!-- Thời gian -->
                                            <td class="py-3 px-3 align-middle text-xs">
                                                <div class="flex flex-col">
                                                    <span class="text-slate-800 font-semibold" x-text="rec.effectiveFrom"></span>
                                                    <span class="text-[11px] text-slate-400" x-text="'đến ' + (rec.effectiveTo || 'Không thời hạn')"></span>
                                                </div>
                                            </td>

                                            <!-- Tuyến / Ca -->
                                            <td class="py-3 px-3 align-middle max-w-[190px]">
                                                <div class="flex flex-col space-y-0.5">
                                                    <span class="text-slate-900 font-bold truncate flex items-center gap-1 text-xs" :title="rec.routeZone">
                                                        <i class="fa-solid fa-paper-plane text-emerald-600 text-[10px] shrink-0"></i>
                                                        <span class="truncate" x-text="rec.routeZone || 'Chưa gán tuyến'"></span>
                                                    </span>
                                                    <span class="text-[10px] text-slate-500 truncate pl-4" x-text="rec.shift || 'Toàn thời gian'"></span>
                                                </div>
                                            </td>

                                            <!-- Trạng thái -->
                                            <td class="py-3 px-3 text-center align-middle">
                                                <template x-if="rec.status === 'Đang áp dụng'">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-[10px]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        <span>Đang áp dụng</span>
                                                    </span>
                                                </template>
                                                <template x-if="rec.status === 'Hoàn thành'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full font-bold text-[10px]">
                                                        <span>Hoàn thành</span>
                                                    </span>
                                                </template>
                                                <template x-if="rec.status !== 'Đang áp dụng' && rec.status !== 'Hoàn thành'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-600 border border-slate-300 rounded-full font-bold text-[10px]" x-text="rec.status"></span>
                                                </template>
                                            </td>

                                            <!-- Thao tác -->
                                            <td class="py-3 px-3 text-center align-middle">
                                                <div class="flex items-center justify-center space-x-1">
                                                    <button type="button" x-show="rec.status === 'Đang áp dụng'" @click="terminateAssignment(rec.id)" class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg transition-colors cursor-pointer" title="Kết thúc đợt phân công này">
                                                        <i class="fa-solid fa-rotate-left text-xs"></i>
                                                    </button>
                                                    <button type="button" @click="deleteAssignment(rec.id)" class="p-1.5 bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-700 rounded-lg transition-colors cursor-pointer" title="Xóa bản ghi khỏi lịch sử">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>

                                    <template x-if="filteredAssignments.length === 0">
                                        <tr>
                                            <td colspan="8" class="py-12 text-center text-slate-400">
                                                <div class="flex flex-col items-center justify-center space-y-2">
                                                    <i class="fa-solid fa-clipboard-list text-3xl text-slate-300"></i>
                                                    <p class="text-sm font-semibold">Chưa có bản ghi lịch sử phân công nào phù hợp</p>
                                                    <p class="text-xs text-slate-400">Thiết lập phân công mới ở bảng bên trái để thêm bản ghi.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer Summary -->
                        <div class="p-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                            <span>* Tất cả các lần phân công được lưu vết phục vụ kiểm tra và tính định mức công nợ giao hàng.</span>
                            <span class="font-bold text-slate-700">Xe: <span class="font-mono text-indigo-900" x-text="selectedVehicleForAssignment?.plate_number"></span></span>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- TAB 8: CHÍNH SÁCH BÁN HÀNG -->
    <div x-show="activeTab === 'policies'" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-percent text-emerald-600"></i>
                    <span>Chính Sách Chiết Khấu Đại Lý Cấp 1, Cấp 2, Bán Lẻ & Dự Án</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                    <span x-text="filteredPolicies.length"></span> / <span x-text="policies.length"></span> chính sách
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Tìm theo tên chính sách, đối tượng..." class="w-48 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
                <a href="{{ url('/cat_sales_policies/create') }}" class="px-3 py-1.5 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm Chính Sách Mới</span>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#dce6f2] text-slate-700 font-bold uppercase text-[11px] border-b border-slate-300">
                        <th class="py-2.5 px-3 w-12 text-center">STT</th>
                        <th class="py-2.5 px-3">Mã CS</th>
                        <th class="py-2.5 px-3">Tên Chính Sách Bán Hàng</th>
                        <th class="py-2.5 px-3">Đối Tượng Áp Dụng</th>
                        <th class="py-2.5 px-3 text-right">Mức Ưu Đãi / Chiết Khấu</th>
                        <th class="py-2.5 px-3 text-right">Đơn Tối Thiểu</th>
                        <th class="py-2.5 px-3">Thời Gian Áp Dụng</th>
                        <th class="py-2.5 px-3 text-center">Trạng Thái</th>
                        <th class="py-2.5 px-3 text-center w-28">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <template x-for="(p, index) in filteredPolicies" :key="p.id">
                        <tr class="hover:bg-sky-50/50 transition-colors">
                            <td class="py-2.5 px-3 text-center text-slate-400" x-text="index + 1"></td>
                            <td class="py-2.5 px-3 font-bold text-slate-700 font-mono" x-text="p.code || ('CS-0' + (index+1))"></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900">
                                <a :href="'{{ url('/cat_sales_policies') }}/' + p.id + '/edit'" class="hover:text-sky-600 hover:underline cursor-pointer" x-text="p.name"></a>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                          :class="p.apply_scope === 'order' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800'"
                                          x-text="p.apply_scope === 'order' ? 'Giảm trên đơn' : (p.apply_scope === 'product' ? 'Giảm từng SP' : (p.apply_scope || 'Toàn hệ thống'))">
                                    </span>
                                    <span class="text-[11px] text-slate-500" x-text="p.target_customer_ids && p.target_customer_ids.length > 0 ? (p.target_customer_ids.length + ' đại lý') : 'Toàn bộ khách'"></span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-emerald-600">
                                <span x-show="p.policy_type === 'quantity'" class="text-sky-700 font-bold" x-text="'Mua ' + (p.buy_qty || 0) + ' tặng ' + (p.free_qty || 0)"></span>
                                <span x-show="p.policy_type === 'amount'" class="text-amber-700 font-bold" x-text="formatCurrency(p.discount_amount || 0)"></span>
                                <span x-show="!p.policy_type || p.policy_type === 'percentage'" x-text="(p.discount_percent || 0) + '%'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-right text-slate-700 font-medium" x-text="p.min_order_value ? formatCurrency(p.min_order_value) : 'Không giới hạn'"></td>
                            <td class="py-2.5 px-3 text-slate-500">
                                <span x-text="p.start_date ? p.start_date.substring(0, 10) : 'Năm 2026'"></span>
                                <span x-show="p.end_date"> - <span x-text="p.end_date.substring(0, 10)"></span></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span :class="(p.status === 'active' || p.status === 'Hoạt động' || p.status === 'Kích hoạt') ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold" x-text="(p.status === 'active' || p.status === 'Hoạt động' || p.status === 'Kích hoạt') ? 'Hoạt động' : 'Tạm ngưng'"></span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a :href="'{{ url('/cat_sales_policies') }}/' + p.id + '/edit'" class="w-7 h-7 rounded-md bg-sky-50 hover:bg-sky-100 text-sky-700 flex items-center justify-center transition-colors cursor-pointer" title="Chỉnh sửa chính sách">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" @click="deleteItem('policies', p)" class="w-7 h-7 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-colors cursor-pointer" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredPolicies.length === 0">
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-percent text-2xl mb-1 text-slate-300 block"></i>
                                Không tìm thấy chính sách bán hàng nào phù hợp
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DYNAMIC CRUD MODAL -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showModal = false" 
             :class="modalType === 'routes' ? 'max-w-3xl' : 'max-w-xl'"
             class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full overflow-hidden animate-fade-in my-8" x-transition>
            
            <!-- Modal Header for Routes (Thiết kế chuẩn theo mẫu Tuyến GH) -->
            <template x-if="modalType === 'routes'">
                <div class="px-6 py-5 bg-white border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-500 border border-sky-100 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-regular fa-paper-plane"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight" x-text="formData.id ? 'Chỉnh Sửa Tuyến GH' : 'Thêm Mới Tuyến GH'"></h3>
                            <p class="text-xs text-slate-400 mt-0.5">Cấu hình tham số và thiết lập thông tin danh mục hệ thống</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </template>

            <!-- Modal Header for Other Types -->
            <template x-if="modalType !== 'routes'">
                <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-[#1b4a8c] text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-amber-400">
                            <i :class="modalIcon"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white" x-text="modalTitle"></h3>
                            <p class="text-[11px] text-sky-200">Điền thông tin và nhấn Lưu dữ liệu để cập nhật hệ thống ERP</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="text-white/70 hover:text-white p-1 transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </template>

            <!-- Modal Form -->
            <form @submit.prevent="submitForm()" class="p-6 space-y-4 text-xs">
                
                <!-- 1. FORM KHO HÀNG -->
                <template x-if="modalType === 'warehouses'">
                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Mã Kho <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.code" required placeholder="VD: KHO-01" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tên Kho Bãi <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.name" required placeholder="VD: Kho Tổng Nghi Phú, Vinh" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Chi Nhánh Trực Thuộc</label>
                                <select x-model="formData.branch_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option value="">-- Chọn chi nhánh quản lý --</option>
                                    <template x-for="b in branches" :key="b.id">
                                        <option :value="b.id" x-text="b.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Loại Kho Hàng</label>
                                <input type="text" x-model="formData.type" placeholder="VD: Kho tổng trữ ống nhựa PVC" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Địa Chỉ Chi Tiết</label>
                            <input type="text" x-model="formData.address" placeholder="VD: Lô B4-12 KCN VSIP Nghệ An, Hưng Nguyên" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Người Quản Lý / Thủ Kho</label>
                                <input type="text" x-model="formData.manager_name" placeholder="Họ và tên thủ kho" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Trạng Thái Hoạt Động</label>
                                <select x-model="formData.is_active" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option :value="true">Hoạt động bình thường</option>
                                    <option :value="false">Tạm dừng tiếp nhận</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- 2. FORM CHI NHÁNH -->
                <template x-if="modalType === 'branches'">
                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Mã Chi Nhánh <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.code" required placeholder="VD: CN-NA, CN-HT" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tên Chi Nhánh <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.name" required placeholder="VD: Chi Nhánh Nghệ An" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Loại Hình Chi Nhánh</label>
                                <input type="text" x-model="formData.type" placeholder="VD: Chi nhánh trung tâm, Đại lý cấp tỉnh" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Hotline / Điện Thoại</label>
                                <input type="text" x-model="formData.phone" placeholder="VD: 0238.3888.999" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Địa Chỉ Trụ Sở</label>
                            <input type="text" x-model="formData.address" placeholder="VD: Số 45 Mai Hắc Đế, TP. Vinh, Nghệ An" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Giám Đốc / Phụ Trách</label>
                                <input type="text" x-model="formData.manager" placeholder="Họ và tên người đại diện" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Trạng Thái</label>
                                <select x-model="formData.is_active" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option :value="true">Đang hoạt động</option>
                                    <option :value="false">Tạm đóng</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- 3. FORM LOẠI QUỸ -->
                <template x-if="modalType === 'fund_types'">
                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Mã Tài Khoản Quỹ <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.code" required placeholder="VD: QTM-01, VCB-01" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tên Quỹ / Tài Khoản <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.name" required placeholder="VD: Quỹ Tiền Mặt Tại Két, Vietcombank Nghệ An" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Ngân Hàng / Loại Quỹ</label>
                                <input type="text" x-model="formData.bank_name" placeholder="VD: Vietcombank, Techcombank, Tiền mặt" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Số Tài Khoản Ngân Hàng</label>
                                <input type="text" x-model="formData.account_number" placeholder="VD: 0101000889988 hoặc Quỹ nội bộ" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Số Dư Hiện Tại (VNĐ)</label>
                                <input type="text" inputmode="numeric" x-money="formData.current_balance" placeholder="0" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono font-bold text-xs text-right">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Trạng Thái</label>
                                <select x-model="formData.is_active" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option :value="true">Sẵn sàng giao dịch</option>
                                    <option :value="false">Tạm khóa tài khoản</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- 4. FORM PHÂN LOẠI -->
                <template x-if="modalType === 'categories'">
                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Mã Nhóm Ngành <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.code" required placeholder="VD: PL-PVC, PL-PK" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tên Phân Loại Ngành Hàng <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.name" required placeholder="VD: Ống Nhựa Thoát Nước uPVC" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Người Phụ Trách Ngành Hàng</label>
                                <select x-model="formData.manager_name" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option value="">-- Chọn người phụ trách --</option>
                                    <template x-for="u in users" :key="u.id">
                                        <option :value="u.name" x-text="u.name + (u.department ? ' (' + u.department + ')' : '')"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Trạng Thái Hiển Thị</label>
                                <select x-model="formData.is_active" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option :value="true">Hiển thị trong danh mục sản phẩm</option>
                                    <option :value="false">Ẩn danh mục</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Mô Tả Quy Cách / Ứng Dụng</label>
                            <textarea x-model="formData.description" rows="2" placeholder="VD: Ống nhựa uPVC chuyên dùng dẫn thoát nước thải dân dụng và hạ tầng công nghiệp" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs"></textarea>
                        </div>
                    </div>
                </template>

                <!-- 5. FORM NHÃN HIỆU -->
                <template x-if="modalType === 'brands'">
                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Mã Nhãn Hiệu <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.code" required placeholder="VD: MQ-PVC, TP, HS" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tên Thương Hiệu / Nhà SX <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.name" required placeholder="VD: Nhựa Tiền Phong, Hoa Sen, Đại Sơn" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Xuất Xứ / Nhà Máy Sản Xuất</label>
                                <input type="text" x-model="formData.country" placeholder="VD: Việt Nam, Nhật Bản, Đức" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Đường Dẫn Logo (Tùy chọn)</label>
                                <input type="text" x-model="formData.logo_url" placeholder="https://..." class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Ghi Chú Chiết Khấu / Giới Thiệu Hãng</label>
                            <textarea x-model="formData.description" rows="3" placeholder="VD: Hợp đồng phân phối đại lý cấp 1 độc quyền khu vực Bắc Trung Bộ" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs"></textarea>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Trạng Thái</label>
                            <select x-model="formData.is_active" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                <option :value="true">Chính hãng (Hiển thị)</option>
                                <option :value="false">Tạm ngưng phân phối</option>
                            </select>
                        </div>
                    </div>
                </template>

                <!-- 6. FORM TUYẾN GIAO HÀNG (CHỌN THEO ĐỊA GIỚI HÀNH CHÍNH) -->
                <template x-if="modalType === 'routes'">
                    <div class="space-y-4">
                        <!-- Tên Tuyến Giao Hàng * -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-slate-700 text-xs">
                                    Tên Tuyến Giao Hàng <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" @click="suggestRouteName()" 
                                        class="px-3 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs rounded-xl border border-purple-200/80 transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                    <i class="fa-solid fa-wand-magic-sparkles text-purple-600 text-xs"></i>
                                    <span>Gợi ý tên tuyến theo khu vực</span>
                                </button>
                            </div>
                            <input type="text" x-model="formData.name" required
                                   placeholder="VD: Tuyến 01: Kho Mê Linh - Giải Phóng - Trường Chinh - Nguyễn Trãi"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-200 focus:border-purple-500 rounded-xl font-bold text-slate-900 text-xs outline-none shadow-2xs transition-all">
                        </div>

                        <!-- Cự Ly & Định Mức Phụ Cấp -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1 text-xs">Cự Ly Trung Bình (km)</label>
                                <div class="relative">
                                    <input type="number" step="0.1" x-model="formData.distance_km" placeholder="38"
                                           class="w-full pl-3.5 pr-12 py-2.5 bg-white border border-slate-200 focus:border-purple-500 rounded-xl font-bold text-slate-900 text-xs outline-none shadow-2xs transition-all">
                                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">km</span>
                                </div>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1 text-xs">Định Mức Xăng Xe / Phụ Cấp (đ)</label>
                                <div class="relative">
                                    <input type="text" x-model="formData.toll_fee_display" @input="updateRouteTollFee($event.target.value)" placeholder="250.000"
                                           class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-slate-200 focus:border-purple-500 rounded-xl font-bold text-emerald-700 text-xs outline-none shadow-2xs transition-all">
                                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-emerald-600">đ</span>
                                </div>
                            </div>
                        </div>

                        <!-- KHU VỰC / QUẬN HUYỆN CŨ PHỤ TRÁCH -->
                        <div class="border border-purple-200/80 bg-white rounded-2xl p-4 sm:p-5 space-y-3.5 shadow-2xs">
                            <!-- Card Header -->
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-purple-600 text-sm"></i>
                                    <span class="font-extrabold text-slate-800 text-xs sm:text-sm">Khu Vực / Quận Huyện Cũ Phụ Trách</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200"
                                          x-text="routeStops.length + ' quận/huyện đã chọn'"></span>
                                </div>
                                <button type="button" @click="clearAllRouteStops()" class="text-xs font-bold text-rose-500 hover:text-rose-700 cursor-pointer">
                                    Xóa tất cả
                                </button>
                            </div>

                            <!-- Selected Tags Box -->
                            <div class="min-h-[46px] p-2.5 bg-purple-50/30 rounded-xl border border-dashed border-purple-200 flex flex-wrap gap-2 items-center">
                                <template x-for="(stop, idx) in routeStops" :key="idx">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-100 text-purple-900 border border-purple-200 text-xs font-bold shadow-2xs animate-fade-in">
                                        <i class="fa-solid fa-location-dot text-purple-600 text-[11px]"></i>
                                        <span x-text="stop"></span>
                                        <button type="button" @click="removeRouteStop(stop)" class="text-purple-400 hover:text-rose-600 ml-0.5 cursor-pointer">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </span>
                                </template>
                                <span x-show="routeStops.length === 0" class="text-xs text-slate-400 italic pl-1">
                                    Chưa chọn quận/huyện nào. Hãy chọn từ danh sách bên dưới hoặc gõ tìm kiếm.
                                </span>
                            </div>

                            <!-- Filter & Search Row -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                <!-- Province Select -->
                                <div class="relative w-full sm:w-60 shrink-0" @click.outside="openRouteProvinceDropdown = false">
                                    <button type="button" @click="openRouteProvinceDropdown = !openRouteProvinceDropdown"
                                            class="w-full px-3 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 flex items-center justify-between transition-colors">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <i class="fa-solid fa-location-pin text-rose-500 text-xs"></i>
                                            <span x-text="selectedRouteProvince ? selectedRouteProvince.name : 'TP. Hà Nội (Hệ thống cũ)'"></span>
                                        </div>
                                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="openRouteProvinceDropdown ? 'rotate-180' : ''"></i>
                                    </button>

                                    <!-- Dropdown list -->
                                    <div x-show="openRouteProvinceDropdown" x-cloak
                                         class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 py-1 text-xs">
                                        <template x-for="p in routeProvinces" :key="p.code">
                                            <div @click="changeRouteProvince(p)" 
                                                 class="px-3 py-2 hover:bg-purple-50 hover:text-purple-700 cursor-pointer flex items-center justify-between transition-colors"
                                                 :class="selectedRouteProvince && selectedRouteProvince.code === p.code ? 'bg-purple-50 font-bold text-purple-700' : 'text-slate-700'">
                                                <span x-text="p.name"></span>
                                                <span class="text-[10px] text-slate-400 font-mono" x-text="p.short_name"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Fast District Search -->
                                <div class="relative flex-1">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" x-model="routeDistrictSearch" @input="filterRouteDistricts()"
                                           placeholder="Gõ tìm nhanh Quận / Huyện cũ (vd: mê linh, đông anh, thanh xuân, phúc yên, từ sơn)..."
                                           class="w-full pl-9 pr-8 py-2 bg-slate-50 focus:bg-white border border-slate-200 focus:border-purple-500 rounded-xl text-xs font-semibold text-slate-800 outline-none transition-all">
                                    <button type="button" x-show="routeDistrictSearch" @click="routeDistrictSearch = ''; filterRouteDistricts()"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-0.5">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Region Filter Tabs & Quick Buttons -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-1 border-t border-slate-100">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-bold text-slate-500">Vùng:</span>
                                    <button type="button" @click="setRouteRegionFilter('all')"
                                            :class="routeRegionFilter === 'all' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                                            class="px-2.5 py-1 rounded-lg text-[11px] transition-all cursor-pointer">
                                        Tất cả (<span x-text="routeDistricts.length"></span>)
                                    </button>
                                    <button type="button" @click="setRouteRegionFilter('urban')"
                                            :class="routeRegionFilter === 'urban' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                                            class="px-2.5 py-1 rounded-lg text-[11px] transition-all cursor-pointer">
                                        12 Quận nội thành
                                    </button>
                                    <button type="button" @click="setRouteRegionFilter('north')"
                                            :class="routeRegionFilter === 'north' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                                            class="px-2.5 py-1 rounded-lg text-[11px] transition-all cursor-pointer">
                                        Bắc HN (Mê Linh, Đông Anh...)
                                    </button>
                                    <button type="button" @click="setRouteRegionFilter('west_south')"
                                            :class="routeRegionFilter === 'west_south' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                                            class="px-2.5 py-1 rounded-lg text-[11px] transition-all cursor-pointer">
                                        Tây & Nam HN
                                    </button>
                                    <button type="button" @click="setRouteRegionFilter('nearby')"
                                            :class="routeRegionFilter === 'nearby' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                                            class="px-2.5 py-1 rounded-lg text-[11px] transition-all cursor-pointer">
                                        Lân cận (Phúc Yên, Từ Sơn...)
                                    </button>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" @click="selectAllDistrictsInProvince()"
                                            class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold flex items-center gap-1 shadow-2xs transition-colors cursor-pointer">
                                        <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                                        <span>Chọn Toàn TP. Hà Nội</span>
                                    </button>
                                    <button type="button" @click="selectUrbanDistricts()"
                                            class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 rounded-lg text-xs font-bold flex items-center gap-1 shadow-2xs transition-colors cursor-pointer">
                                        <i class="fa-solid fa-check text-purple-600 text-xs"></i>
                                        <span>Chọn 12 quận nội thành</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Scrollable District Selection Box -->
                            <div class="max-h-56 overflow-y-auto p-3 bg-slate-50/70 rounded-xl border border-slate-200/80 flex flex-wrap gap-2">
                                <template x-for="d in filteredRouteDistricts" :key="d.code || d.short_name">
                                    <button type="button" @click="toggleRouteStop(d.short_name)"
                                            :class="routeStops.includes(d.short_name) ? 'bg-purple-600 text-white border-purple-700 shadow-xs' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200'"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-xl text-xs font-semibold cursor-pointer active:scale-95 transition-all">
                                        <i x-show="routeStops.includes(d.short_name)" class="fa-solid fa-check text-xs"></i>
                                        <span x-show="!routeStops.includes(d.short_name)" class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        <span x-text="d.short_name"></span>
                                        <span :class="routeStops.includes(d.short_name) ? 'bg-purple-700/80 text-purple-100' : 'bg-slate-100 text-slate-500'"
                                              class="text-[10px] px-1.5 py-0.2 rounded font-normal" x-text="d.type || 'quận'"></span>
                                    </button>
                                </template>
                                <div x-show="filteredRouteDistricts.length === 0" class="w-full py-4 text-center text-slate-400 italic">
                                    Không tìm thấy quận/huyện phù hợp
                                </div>
                            </div>

                            <!-- Custom Area Input Row -->
                            <div class="flex items-center gap-2 pt-1">
                                <input type="text" x-model="customStopInput" @keydown.enter.prevent="addCustomStop()"
                                       placeholder="Nhập thêm khu vực / địa bàn đặc thù (VD: KCN Thăng Long, Cụm CN Bát Tràng)..."
                                       class="flex-1 px-3.5 py-2 bg-slate-50 focus:bg-white border border-slate-200 focus:border-purple-500 rounded-xl text-xs font-medium text-slate-800 outline-none transition-all">
                                <button type="button" @click="addCustomStop()"
                                        class="px-4 py-2 bg-slate-100 hover:bg-purple-50 text-slate-700 hover:text-purple-700 font-bold text-xs rounded-xl border border-slate-200 hover:border-purple-300 transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span>Thêm khu vực</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- 7. FORM XE GIAO HÀNG -->
                <template x-if="modalType === 'vehicles'">
                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Biển Kiểm Soát Xe <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.plate_number" required placeholder="VD: 37C-289.88" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono font-bold text-xs uppercase">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Loại Xe / Thùng Xe <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.vehicle_type" required placeholder="VD: Xe tải thùng dài 6m" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tải Trọng Tối Đa (kg)</label>
                                <input type="text" inputmode="numeric" x-money="formData.capacity_kg" placeholder="VD: 3.500" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tài Xế Phụ Trách</label>
                                <select x-model="formData.driver_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option value="">-- Phân công tài xế --</option>
                                    <template x-for="u in users" :key="u.id">
                                        <option :value="u.id" x-text="u.name + (u.phone ? (' - ' + u.phone) : '')"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tuyến Giao Hàng Cố Định</label>
                                <select x-model="formData.delivery_route_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option value="">-- Chọn tuyến đường phụ trách --</option>
                                    <template x-for="r in routes" :key="r.id">
                                        <option :value="r.id" x-text="r.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tên Phụ Xe / Lơ Xe</label>
                                <input type="text" x-model="formData.copilot_name" placeholder="Họ và tên phụ xe" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tình Trạng Vận Hành</label>
                            <select x-model="formData.status" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                <option value="Sẵn sàng">Sẵn sàng điều phối</option>
                                <option value="Đang giao hàng">Đang giao hàng trên tuyến</option>
                                <option value="Bảo dưỡng">Đang sửa chữa / Bảo dưỡng định kỳ</option>
                            </select>
                        </div>
                    </div>
                </template>

                <!-- 8. FORM CHÍNH SÁCH BÁN HÀNG -->
                <template x-if="modalType === 'policies'">
                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Mã Chính Sách <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.code" required placeholder="VD: CS-C1-2026" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tên Chính Sách Bán Hàng <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.name" required placeholder="VD: Chiết khấu Đại lý Cấp 1 năm 2026" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Đối Tượng Áp Dụng</label>
                                <select x-model="formData.apply_scope" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option value="Đại lý Cấp 1">Đại lý Cấp 1</option>
                                    <option value="Đại lý Cấp 2">Đại lý Cấp 2</option>
                                    <option value="Khách Mua Dự Án">Khách Mua Dự Án</option>
                                    <option value="Thợ Lắp Đặt">Thợ Lắp Đặt & Công Trình</option>
                                    <option value="Đại lý toàn hệ thống">Toàn bộ hệ thống</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tỷ Lệ Chiết Khấu (%) <span class="text-rose-500">*</span></label>
                                <input type="number" x-model="formData.discount_percent" min="0" max="100" step="0.1" required placeholder="VD: 15.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-bold text-emerald-600 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Đơn Hàng Tối Thiểu (VNĐ)</label>
                                <input type="text" inputmode="numeric" x-money="formData.min_order_value" placeholder="VD: 20.000.000" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono font-bold text-xs text-right">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Trạng Thái Hiệu Lực</label>
                                <select x-model="formData.status" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option value="active">Kích hoạt áp dụng</option>
                                    <option value="inactive">Tạm ngưng / Hết hạn</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Ngày Bắt Đầu</label>
                                <input type="date" x-model="formData.start_date" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Ngày Kết Thúc</label>
                                <input type="date" x-model="formData.end_date" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Ghi Chú Điều Kiện Đi Kèm</label>
                            <textarea x-model="formData.notes" rows="2" placeholder="VD: Áp dụng thanh toán trước hoặc đúng hạn trong 15 ngày" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs"></textarea>
                        </div>
                    </div>
                </template>

                <!-- 9. FORM NHÓM NHỎ (SUBCATEGORIES) -->
                <template x-if="modalType === 'subcategories'">
                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Mã Nhóm Nhỏ <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.code" required placeholder="VD: NN-001" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tên Nhóm Nhỏ / Con <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.name" required placeholder="VD: Ống Luồn điện TP" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs font-semibold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Thuộc Ngành Hàng (Loại) <span class="text-rose-500">*</span></label>
                                <select x-model="formData.category_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option value="">-- Chọn phân loại ngành hàng --</option>
                                    <template x-for="c in categories" :key="c.id">
                                        <option :value="c.id" x-text="c.name + ' (' + c.code + ')'"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Thuộc Thương Hiệu / Nhà SX</label>
                                <select x-model="formData.brand_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs bg-white">
                                    <option value="">-- Chọn thương hiệu (nếu có) --</option>
                                    <template x-for="b in brands" :key="b.id">
                                        <option :value="b.id" x-text="b.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Mô Tả / Đặc Tính Kỹ Thuật</label>
                            <textarea x-model="formData.description" rows="2" placeholder="Ghi chú chi tiết nhóm nhỏ..." class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-sky-500 text-xs"></textarea>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" id="subcat_active" x-model="formData.is_active" class="rounded text-sky-600 focus:ring-sky-500 cursor-pointer">
                            <label for="subcat_active" class="font-semibold text-slate-700 text-xs cursor-pointer">Kích hoạt hiển thị nhóm nhỏ này</label>
                        </div>
                    </div>
                </template>

                <!-- Modal Footer Buttons -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <!-- Left: Status toggle for routes -->
                    <template x-if="modalType === 'routes'">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-600">Trạng thái:</span>
                            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200">
                                <button type="button" @click="formData.is_active = true"
                                        :class="formData.is_active ? 'bg-emerald-500 text-white font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                                    Hoạt động
                                </button>
                                <button type="button" @click="formData.is_active = false"
                                        :class="!formData.is_active ? 'bg-slate-500 text-white font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                                    Tạm ngưng
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="modalType !== 'routes'">
                        <div></div>
                    </template>

                    <!-- Right Buttons -->
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showModal = false" 
                                :class="modalType === 'routes' ? 'px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs' : 'px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold'"
                                class="transition-colors cursor-pointer">
                            Hủy Bỏ
                        </button>
                        <button type="submit" :disabled="isSaving" 
                                :class="modalType === 'routes' ? 'px-6 py-2.5 rounded-xl bg-[#00a2ff] hover:bg-sky-600 text-white font-black text-xs shadow-md shadow-sky-600/20' : 'px-5 py-2 rounded-lg bg-[#1b4a8c] hover:bg-[#153a6f] text-white font-bold shadow-md'"
                                class="transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50">
                            <i x-show="isSaving" class="fa-solid fa-spinner fa-spin"></i>
                            <i x-show="!isSaving && modalType !== 'routes'" class="fa-solid fa-floppy-disk"></i>
                            <span x-text="isSaving ? 'Đang lưu...' : (modalType === 'routes' ? 'Lưu Cập Nhật' : 'Lưu Dữ Liệu')"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL IMPORT EXCEL -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden animate-scale-up" @click.outside="if(!isImporting) showImportModal = false">
            <div class="p-4 bg-emerald-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-file-excel text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">Import Excel: Loại, Thương Hiệu & Nhóm Nhỏ</h3>
                        <p class="text-[11px] text-emerald-100">Đồng bộ tự động cây phân cấp sản phẩm từ file Excel</p>
                    </div>
                </div>
                <button type="button" @click="if(!isImporting) showImportModal = false" class="text-white/80 hover:text-white transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form @submit.prevent="submitImportExcel()" class="p-5 space-y-4 text-xs">
                <!-- Instruction box -->
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 space-y-1.5">
                    <div class="font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-emerald-600"></i>
                        <span>Cấu trúc chuẩn file Excel:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-emerald-800">
                        <li><strong>Cột Nhóm (Loại):</strong> ỐNG, PHỤ KIỆN, MÁY BƠM NƯỚC, VẬT TƯ ĐIỆN...</li>
                        <li><strong>Cột nhóm cha (Thương hiệu):</strong> Tiền Phong, Dismy, Kohan, Phú Thịnh...</li>
                        <li><strong>Cột nhóm con (Nhóm nhỏ):</strong> Ống PVC, Ống PPR, Phụ kiện PVC...</li>
                        <li>Hệ thống hỗ trợ tự động fill-down các ô merge / ô trống phía dưới.</li>
                    </ul>
                    <div class="pt-1">
                        <a href="{{ url('/api/categories/template-excel') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:text-emerald-900 underline">
                            <i class="fa-solid fa-download"></i>
                            <span>Tải file Excel mẫu chuẩn (PHÂN NHÓM SẢN PHẨM.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- File Drag & Drop Zone -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Chọn file Excel (.xlsx, .xls) <span class="text-rose-500">*</span></label>
                    <div 
                        class="border-2 border-dashed rounded-xl p-5 text-center transition-colors cursor-pointer"
                        :class="importFile ? 'border-emerald-500 bg-emerald-50/40' : 'border-slate-300 hover:border-emerald-400 bg-slate-50'"
                        @click="$refs.excelFileInput.click()"
                        @dragover.prevent
                        @drop.prevent="handleFileDrop($event)"
                    >
                        <input 
                            type="file" 
                            x-ref="excelFileInput" 
                            accept=".xlsx,.xls" 
                            class="hidden" 
                            @change="handleFileSelect($event)"
                        >
                        <template x-if="!importFile">
                            <div class="space-y-1.5">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400"></i>
                                <p class="font-semibold text-slate-700">Kéo thả file Excel vào đây hoặc <span class="text-emerald-600 underline">nhấn để duyệt file</span></p>
                                <p class="text-[11px] text-slate-400">Dung lượng tối đa 20MB (.xlsx)</p>
                            </div>
                        </template>
                        <template x-if="importFile">
                            <div class="flex items-center justify-center gap-3 text-emerald-800">
                                <i class="fa-solid fa-file-excel text-2xl text-emerald-600"></i>
                                <div class="text-left">
                                    <p class="font-bold text-xs" x-text="importFileName"></p>
                                    <p class="text-[11px] text-emerald-600" x-text="formatFileSize(importFile.size)"></p>
                                </div>
                                <button type="button" @click.stop="clearImportFile()" class="ml-2 w-6 h-6 rounded-full bg-slate-200 hover:bg-rose-100 hover:text-rose-600 text-slate-600 flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" :disabled="isImporting" @click="showImportModal = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold transition-colors cursor-pointer disabled:opacity-50">
                        Đóng
                    </button>
                    <button type="submit" :disabled="!importFile || isImporting" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer disabled:opacity-50">
                        <i x-show="isImporting" class="fa-solid fa-spinner fa-spin"></i>
                        <i x-show="!isImporting" class="fa-solid fa-upload"></i>
                        <span x-text="isImporting ? 'Đang phân tích & import...' : 'Bắt Đầu Import'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 for polished confirmations and alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function categoryManager() {
    return {
        activeTab: '{{ $activeTab ?? 'warehouses' }}',
        search: '',
        showModal: false,
        showImportModal: false,
        isImporting: false,
        importFile: null,
        importFileName: '',
        modalTitle: '',
        modalType: '',
        modalIcon: 'fa-solid fa-folder-open',
        isSaving: false,
        formData: {},

        // Geo & Route Stops Configuration (Chọn tuyến theo địa giới)
        routeProvinces: [],
        selectedRouteProvince: null,
        openRouteProvinceDropdown: false,
        routeDistricts: [],
        filteredRouteDistricts: [],
        routeRegionFilter: 'all', // 'all', 'urban', 'north', 'west_south', 'nearby'
        routeDistrictSearch: '',
        routeStops: [],
        customStopInput: '',

        // Initial Data Collections
        warehouses: @json($warehouses ?? []),
        branches: @json($branches ?? []),
        funds: @json($funds ?? []),
        categories: @json($categories ?? []),
        brands: @json($brands ?? []),
        subcategories: @json($subcategories ?? []),
        routes: @json($routes ?? []),
        vehicles: @json($vehicles ?? []),
        policies: @json($policies ?? []),
        users: @json($users ?? []),

        // Vehicle Assignment state (Ảnh 1 & Ảnh 2)
        selectedVehicleForAssignment: null,
        assignments: @json($assignments ?? []),
        viewOnlyCurrentVehicle: true,
        asgSearch: '',
        asgStatusFilter: 'all',
        isSavingAsg: false,

        asgPresetDrivers: [
            { name: 'Bùi Văn Thành', phone: '0966 334 455', license: 'Bằng B2', experience: '3 năm kinh nghiệm' },
            { name: 'Vũ Quốc Nam', phone: '0933 111 222', license: 'Bằng C', experience: '5 năm kinh nghiệm' },
            { name: 'Nguyễn Văn Định', phone: '0981 222 333', license: 'Bằng C', experience: '7 năm kinh nghiệm' },
            { name: 'Trần Văn Mạnh', phone: '0915 444 555', license: 'Bằng C', experience: '4 năm kinh nghiệm' },
            { name: 'Phạm Văn Hùng', phone: '0966 777 888', license: 'Bằng FC (Container)', experience: '8 năm kinh nghiệm' },
            { name: 'Bùi Hoàng Long', phone: '0944 333 777', license: 'Bằng C', experience: '6 năm kinh nghiệm' },
            { name: 'Vũ Đình Trọng', phone: '0975 555 666', license: 'Bằng C', experience: '4 năm kinh nghiệm' },
            { name: 'Hoàng Văn Cường', phone: '0977 777 888', license: 'Bằng C', experience: '4 năm kinh nghiệm' },
            { name: 'Nguyễn Văn Hùng', phone: '0988 123 456', license: 'Bằng C', experience: '5 năm kinh nghiệm' },
        ],
        asgPresetCopilots: [
            { name: 'Lê Văn Nam', phone: '0971 111 222', role: 'Phụ xe chuyên tuyến' },
            { name: 'Nguyễn Tiến Đạt', phone: '0978 222 333', role: 'Phụ xe chuyên tuyến' },
            { name: 'Trần Văn Bình', phone: '0983 456 789', role: 'Phụ xe chuyên tuyến' },
            { name: 'Lê Văn Kiên', phone: '0912 345 678', role: 'Phụ xe & bốc dỡ' },
            { name: 'Hoàng Văn Thái', phone: '0989 555 444', role: 'Phụ xe đường dài' },
            { name: 'Nguyễn Văn Huy', phone: '0904 111 888', role: 'Phụ xe kho tổng' },
            { name: 'Vũ Đình Trọng', phone: '0973 888 999', role: 'Phụ xe & giao nhận' },
            { name: 'Phạm Quốc Hùng', phone: '0973 333 444', role: 'Phụ xe giao nhận' },
        ],
        asgPresetManagers: [
            { name: 'Hoàng Văn Khoa', phone: '0988 222 111', title: 'Trưởng đội xe & Điều vận Logistics' },
            { name: 'Lê Hoàng Khoa', phone: '0977 654 321', title: 'Quản lý Kho Tổng & Đội xe' },
            { name: 'Bùi Văn Thành', phone: '0966 334 455', title: 'Tổ trưởng Tổ lái xe' },
            { name: 'Nguyễn Văn Hùng', phone: '0988 123 456', title: 'Giám đốc Vận hành' },
        ],

        asgForm: {
            driver_select: 'CUSTOM',
            driver_name: '',
            driver_phone: '',
            copilot_select: 'CUSTOM',
            copilot_name: '',
            copilot_phone: '',
            manager_select: 'Hoàng Văn Khoa',
            manager_name: 'Hoàng Văn Khoa',
            manager_phone: '0988 222 111',
            effective_from: new Date().toISOString().split('T')[0],
            effective_to: '',
            is_indefinite: true,
            route_id: '',
            route_zone: '',
            shift: 'Toàn thời gian (07:30 - 17:30)',
            assigned_by: 'Hoàng Văn Khoa (Trưởng đội xe)',
            notes: '',
        },

        initData() {
            // Keep active tab state synced with URL parameter if passed
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            const actionParam = urlParams.get('action') || '{{ $initialAction ?? '' }}';
            const vehIdParam = urlParams.get('vehicle_id') || urlParams.get('vehicleId') || '{{ $initialVehicleId ?? '' }}';
            const plateParam = urlParams.get('plate') || '{{ $initialPlate ?? '' }}';

            if (tabParam) {
                this.activeTab = tabParam;
            }
            if (actionParam === 'assignment' || vehIdParam || plateParam) {
                this.activeTab = 'vehicles';
                let target = null;
                if (vehIdParam) {
                    target = this.vehicles.find(v => String(v.id) === String(vehIdParam));
                }
                if (!target && plateParam) {
                    target = this.vehicles.find(v => v.plate_number === plateParam);
                }
                if (!target && this.vehicles.length > 0) {
                    target = this.vehicles[0];
                }
                if (target) {
                    this.openAssignment(target);
                }
            }
        },

        // --- GEO & DELIVERY ROUTE HANDLERS ---
        async initRouteGeo() {
            if (this.routeProvinces.length === 0) {
                try {
                    const res = await fetch('{{ url("/api/geo/provinces-old") }}');
                    this.routeProvinces = await res.json();
                } catch(e) {
                    console.error(e);
                }
            }

            // Mặc định chọn Hà Nội nếu chưa có
            if (!this.selectedRouteProvince) {
                const hn = this.routeProvinces.find(p => p.short_name === 'Hà Nội' || p.name.includes('Hà Nội')) || { code: 1, name: 'TP. Hà Nội (Hệ thống cũ)', short_name: 'Hà Nội' };
                this.selectedRouteProvince = hn;
            }

            await this.loadRouteDistrictsForProvince(this.selectedRouteProvince);
        },

        async loadRouteDistrictsForProvince(province) {
            this.selectedRouteProvince = province;
            const pName = province.short_name || province.name;

            // Nếu là Hà Nội, khởi tạo sẵn danh sách đầy đủ đúng phân vùng như mẫu giao diện
            if (pName.includes('Hà Nội')) {
                const hanoiDistricts = [
                    { short_name: 'Toàn TP. Hà Nội', name: 'Toàn Thành phố Hà Nội', type: 'toàn tỉnh', region: 'all' },
                    { short_name: 'Ba Đình', name: 'Quận Ba Đình', type: 'quận', region: 'urban' },
                    { short_name: 'Hoàn Kiếm', name: 'Quận Hoàn Kiếm', type: 'quận', region: 'urban' },
                    { short_name: 'Đống Đa', name: 'Quận Đống Đa', type: 'quận', region: 'urban' },
                    { short_name: 'Hai Bà Trưng', name: 'Quận Hai Bà Trưng', type: 'quận', region: 'urban' },
                    { short_name: 'Cầu Giấy', name: 'Quận Cầu Giấy', type: 'quận', region: 'urban' },
                    { short_name: 'Thanh Xuân', name: 'Quận Thanh Xuân', type: 'quận', region: 'urban' },
                    { short_name: 'Hoàng Mai', name: 'Quận Hoàng Mai', type: 'quận', region: 'urban' },
                    { short_name: 'Long Biên', name: 'Quận Long Biên', type: 'quận', region: 'urban' },
                    { short_name: 'Tây Hồ', name: 'Quận Tây Hồ', type: 'quận', region: 'urban' },
                    { short_name: 'Nam Từ Liêm', name: 'Quận Nam Từ Liêm', type: 'quận', region: 'urban' },
                    { short_name: 'Bắc Từ Liêm', name: 'Quận Bắc Từ Liêm', type: 'quận', region: 'urban' },
                    { short_name: 'Hà Đông', name: 'Quận Hà Đông', type: 'quận', region: 'urban' },
                    { short_name: 'Mê Linh', name: 'Huyện Mê Linh', type: 'huyện', region: 'north' },
                    { short_name: 'Đông Anh', name: 'Huyện Đông Anh', type: 'huyện', region: 'north' },
                    { short_name: 'Sóc Sơn', name: 'Huyện Sóc Sơn', type: 'huyện', region: 'north' },
                    { short_name: 'Gia Lâm', name: 'Huyện Gia Lâm', type: 'huyện', region: 'west_south' },
                    { short_name: 'Hoài Đức', name: 'Huyện Hoài Đức', type: 'huyện', region: 'west_south' },
                    { short_name: 'Đan Phượng', name: 'Huyện Đan Phượng', type: 'huyện', region: 'west_south' },
                    { short_name: 'Thanh Trì', name: 'Huyện Thanh Trì', type: 'huyện', region: 'west_south' },
                    { short_name: 'Thường Tín', name: 'Huyện Thường Tín', type: 'huyện', region: 'west_south' },
                    { short_name: 'Chương Mỹ', name: 'Huyện Chương Mỹ', type: 'huyện', region: 'west_south' },
                    { short_name: 'Quốc Oai', name: 'Huyện Quốc Oai', type: 'huyện', region: 'west_south' },
                    { short_name: 'Thạch Thất', name: 'Huyện Thạch Thất', type: 'huyện', region: 'west_south' },
                    { short_name: 'Phúc Thọ', name: 'Huyện Phúc Thọ', type: 'huyện', region: 'west_south' },
                    { short_name: 'Sơn Tây', name: 'Thị xã Sơn Tây', type: 'thị xã', region: 'west_south' },
                    { short_name: 'Phú Xuyên', name: 'Huyện Phú Xuyên', type: 'huyện', region: 'west_south' },
                    { short_name: 'Thanh Oai', name: 'Huyện Thanh Oai', type: 'huyện', region: 'west_south' },
                    { short_name: 'Ứng Hòa', name: 'Huyện Ứng Hòa', type: 'huyện', region: 'west_south' },
                    { short_name: 'Mỹ Đức', name: 'Huyện Mỹ Đức', type: 'huyện', region: 'west_south' },
                    { short_name: 'Ba Vì', name: 'Huyện Ba Vì', type: 'huyện', region: 'west_south' },
                    { short_name: 'Phúc Yên', name: 'TP Phúc Yên (Vĩnh Phúc)', type: 'lân cận', region: 'nearby' },
                    { short_name: 'Từ Sơn', name: 'TP Từ Sơn (Bắc Ninh)', type: 'lân cận', region: 'nearby' },
                ];
                this.routeDistricts = hanoiDistricts;
                this.filterRouteDistricts();
                return;
            }

            // Nếu là tỉnh khác (Nghệ An, Hà Tĩnh, Hải Phòng...), tải từ Geo API
            try {
                const res = await fetch(`{{ url("/api/geo/districts-old") }}?province_code=${province.code || ''}&province_name=${encodeURIComponent(province.name)}`);
                const list = await res.json();
                this.routeDistricts = [
                    { short_name: `Toàn ${province.name}`, name: `Toàn ${province.name}`, type: 'toàn tỉnh', region: 'all' },
                    ...list.map(d => {
                        let t = 'huyện';
                        if (d.name.includes('Quận')) t = 'quận';
                        else if (d.name.includes('Thị xã')) t = 'thị xã';
                        else if (d.name.includes('Thành phố')) t = 'TP';
                        return {
                            short_name: d.short_name,
                            name: d.name,
                            type: t,
                            region: 'all'
                        };
                    })
                ];
                this.filterRouteDistricts();
            } catch(e) {
                console.error(e);
            }
        },

        changeRouteProvince(p) {
            this.selectedRouteProvince = p;
            this.openRouteProvinceDropdown = false;
            this.routeDistrictSearch = '';
            this.routeRegionFilter = 'all';
            this.loadRouteDistrictsForProvince(p);
        },

        setRouteRegionFilter(region) {
            this.routeRegionFilter = region;
            this.filterRouteDistricts();
        },

        filterRouteDistricts() {
            const q = (this.routeDistrictSearch || '').trim().toLowerCase();
            this.filteredRouteDistricts = this.routeDistricts.filter(d => {
                // Filter theo vùng
                if (this.routeRegionFilter !== 'all') {
                    if (this.routeRegionFilter === 'urban' && d.region !== 'urban') return false;
                    if (this.routeRegionFilter === 'north' && d.region !== 'north') return false;
                    if (this.routeRegionFilter === 'west_south' && d.region !== 'west_south') return false;
                    if (this.routeRegionFilter === 'nearby' && d.region !== 'nearby') return false;
                }
                // Filter theo từ khóa
                if (q) {
                    return d.short_name.toLowerCase().includes(q) || d.name.toLowerCase().includes(q);
                }
                return true;
            });
        },

        toggleRouteStop(stopName) {
            const idx = this.routeStops.indexOf(stopName);
            if (idx > -1) {
                this.routeStops.splice(idx, 1);
            } else {
                this.routeStops.push(stopName);
            }
        },

        removeRouteStop(stopName) {
            const idx = this.routeStops.indexOf(stopName);
            if (idx > -1) {
                this.routeStops.splice(idx, 1);
            }
        },

        clearAllRouteStops() {
            this.routeStops = [];
        },

        selectAllDistrictsInProvince() {
            const pName = this.selectedRouteProvince ? this.selectedRouteProvince.name : 'TP. Hà Nội';
            const label = `Toàn ${pName}`;
            if (!this.routeStops.includes(label)) {
                this.routeStops.push(label);
            }
        },

        selectUrbanDistricts() {
            const urbanNames = ['Ba Đình', 'Hoàn Kiếm', 'Đống Đa', 'Hai Bà Trưng', 'Cầu Giấy', 'Thanh Xuân', 'Hoàng Mai', 'Long Biên', 'Tây Hồ', 'Nam Từ Liêm', 'Bắc Từ Liêm', 'Hà Đông'];
            urbanNames.forEach(name => {
                if (!this.routeStops.includes(name)) {
                    this.routeStops.push(name);
                }
            });
        },

        addCustomStop() {
            const text = (this.customStopInput || '').trim();
            if (!text) return;
            if (!this.routeStops.includes(text)) {
                this.routeStops.push(text);
            }
            this.customStopInput = '';
        },

        suggestRouteName() {
            const prefix = this.formData.departure_warehouse || 'Kho Mê Linh';
            if (this.routeStops.length === 0) {
                this.formData.name = `Tuyến 01: ${prefix} - Toàn Địa Bàn`;
                return;
            }
            const sampleStops = this.routeStops.slice(0, 4).join(' - ');
            this.formData.name = `Tuyến 01: ${prefix} - ${sampleStops}`;
        },

        updateRouteTollFee(val) {
            const clean = String(val || '').replace(/\D/g, '');
            const num = clean ? parseInt(clean, 10) : 0;
            this.formData.toll_fee = num;
            this.formData.toll_fee_display = new Intl.NumberFormat('vi-VN').format(num);
        },

        switchTab(tab) {
            this.activeTab = tab;
            this.search = '';
        },

        // Formatters & Lookups
        formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount || 0) + ' đ';
        },

        formatStops(stops) {
            if (!stops) return 'Toàn tuyến';
            if (Array.isArray(stops)) return stops.join(' ➔ ');
            return stops;
        },

        getBranchName(branchId) {
            const b = this.branches.find(x => x.id == branchId);
            return b ? b.name : '';
        },

        getCategoryName(catId) {
            const c = this.categories.find(x => x.id == catId);
            return c ? c.name : '---';
        },

        getBrandName(brandId) {
            const b = this.brands.find(x => x.id == brandId);
            return b ? b.name : '';
        },

        getDriverName(driverId) {
            const u = this.users.find(x => x.id == driverId);
            return u ? u.name : '';
        },

        getDriverPhone(driverId) {
            const u = this.users.find(x => x.id == driverId);
            return u ? u.phone : '';
        },

        getRouteName(routeId) {
            const r = this.routes.find(x => x.id == routeId);
            return r ? r.name : '';
        },

        // Filtered Getters per tab
        get filteredWarehouses() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.warehouses;
            return this.warehouses.filter(w => 
                (w.code && w.code.toLowerCase().includes(q)) ||
                (w.name && w.name.toLowerCase().includes(q)) ||
                (w.address && w.address.toLowerCase().includes(q)) ||
                (w.manager_name && w.manager_name.toLowerCase().includes(q))
            );
        },

        get filteredBranches() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.branches;
            return this.branches.filter(b => 
                (b.code && b.code.toLowerCase().includes(q)) ||
                (b.name && b.name.toLowerCase().includes(q)) ||
                (b.address && b.address.toLowerCase().includes(q)) ||
                (b.manager && b.manager.toLowerCase().includes(q))
            );
        },

        get filteredFunds() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.funds;
            return this.funds.filter(f => 
                (f.code && f.code.toLowerCase().includes(q)) ||
                (f.name && f.name.toLowerCase().includes(q)) ||
                (f.bank_name && f.bank_name.toLowerCase().includes(q)) ||
                (f.account_number && f.account_number.toLowerCase().includes(q))
            );
        },

        get filteredCategories() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.categories;
            return this.categories.filter(c => 
                (c.code && c.code.toLowerCase().includes(q)) ||
                (c.name && c.name.toLowerCase().includes(q)) ||
                (c.manager_name && c.manager_name.toLowerCase().includes(q)) ||
                (c.description && c.description.toLowerCase().includes(q))
            );
        },

        get filteredBrands() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.brands;
            return this.brands.filter(b => 
                (b.code && b.code.toLowerCase().includes(q)) ||
                (b.name && b.name.toLowerCase().includes(q)) ||
                (b.country && b.country.toLowerCase().includes(q)) ||
                (b.description && b.description.toLowerCase().includes(q))
            );
        },

        get filteredSubcategories() {
            const q = this.search.trim().toLowerCase();
            return this.subcategories.filter(s => {
                if (!q) return true;
                const catName = s.category ? s.category.name : this.getCategoryName(s.category_id);
                const brandName = s.brand ? s.brand.name : this.getBrandName(s.brand_id);
                return (s.code && s.code.toLowerCase().includes(q)) ||
                    (s.name && s.name.toLowerCase().includes(q)) ||
                    (catName && catName.toLowerCase().includes(q)) ||
                    (brandName && brandName.toLowerCase().includes(q)) ||
                    (s.description && s.description.toLowerCase().includes(q));
            });
        },

        get filteredRoutes() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.routes;
            return this.routes.filter(r => 
                (r.code && r.code.toLowerCase().includes(q)) ||
                (r.name && r.name.toLowerCase().includes(q)) ||
                (r.departure_warehouse && r.departure_warehouse.toLowerCase().includes(q)) ||
                (r.assigned_vehicle_plate && r.assigned_vehicle_plate.toLowerCase().includes(q))
            );
        },

        get filteredVehicles() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.vehicles;
            return this.vehicles.filter(v => 
                (v.plate_number && v.plate_number.toLowerCase().includes(q)) ||
                (v.vehicle_type && v.vehicle_type.toLowerCase().includes(q)) ||
                (v.driver && v.driver.name && v.driver.name.toLowerCase().includes(q))
            );
        },

        get filteredPolicies() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.policies;
            return this.policies.filter(p => 
                (p.code && p.code.toLowerCase().includes(q)) ||
                (p.name && p.name.toLowerCase().includes(q)) ||
                (p.apply_scope && p.apply_scope.toLowerCase().includes(q))
            );
        },

        // Excel Import methods
        openImportExcelModal() {
            this.importFile = null;
            this.importFileName = '';
            this.showImportModal = true;
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.importFile = file;
                this.importFileName = file.name;
            }
        },

        handleFileDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file) {
                this.importFile = file;
                this.importFileName = file.name;
            }
        },

        clearImportFile() {
            this.importFile = null;
            this.importFileName = '';
            if (this.$refs.excelFileInput) {
                this.$refs.excelFileInput.value = '';
            }
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },

        async submitImportExcel() {
            if (!this.importFile) {
                Swal.fire({ icon: 'warning', title: 'Chưa chọn file', text: 'Vui lòng chọn một file Excel (.xlsx)!' });
                return;
            }

            this.isImporting = true;
            const formData = new FormData();
            formData.append('file', this.importFile);

            try {
                const response = await fetch('{{ url('/api/categories/import-excel') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    this.showImportModal = false;
                    await Swal.fire({
                        icon: 'success',
                        title: 'Import Dữ Liệu Thành Công!',
                        html: `
                            <div class="text-left text-xs space-y-2 p-3 bg-slate-50 rounded-xl border border-slate-200 mt-2">
                                <div class="flex justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-600">Ngành hàng (Loại) thêm mới:</span>
                                    <strong class="text-blue-700">${result.stats.categories_created}</strong>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-600">Thương hiệu thêm mới:</span>
                                    <strong class="text-amber-700">${result.stats.brands_created}</strong>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-600">Liên kết Loại - Thương hiệu:</span>
                                    <strong class="text-emerald-700">${result.stats.relations_created}</strong>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-600">Nhóm nhỏ tạo mới:</span>
                                    <strong class="text-teal-700">${result.stats.subcategories_created}</strong>
                                </div>
                                <div class="pt-1 text-[11px] text-slate-500 font-semibold text-center">
                                    Tổng hệ thống: ${result.stats.total_categories} Loại | ${result.stats.total_brands} Thương hiệu | ${result.stats.total_subcategories} Nhóm nhỏ
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Đồng ý & Xem kết quả'
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Import thất bại',
                        text: result.message || 'Không thể đọc nội dung file Excel.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi máy chủ',
                    text: 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại!'
                });
            } finally {
                this.isImporting = false;
            }
        },

        // Open Create Modal
        openCreate(type) {
            this.modalType = type;
            const names = {
                warehouses: { title: 'Thêm Mới Kho Hàng', icon: 'fa-solid fa-warehouse', prefix: 'KHO' },
                branches: { title: 'Thêm Mới Chi Nhánh', icon: 'fa-solid fa-code-branch', prefix: 'CN' },
                fund_types: { title: 'Thêm Mới Loại Quỹ / Tài Khoản', icon: 'fa-solid fa-wallet', prefix: 'QTM' },
                categories: { title: 'Thêm Mới Phân Loại Ngành Hàng', icon: 'fa-solid fa-layer-group', prefix: 'PL' },
                brands: { title: 'Thêm Mới Nhãn Hiệu / Thương Hiệu', icon: 'fa-solid fa-award', prefix: 'TH' },
                subcategories: { title: 'Thêm Mới Nhóm Nhỏ / Con', icon: 'fa-solid fa-sitemap', prefix: 'NN' },
                routes: { title: 'Thêm Mới Tuyến Giao Hàng', icon: 'fa-solid fa-route', prefix: 'TGH' },
                vehicles: { title: 'Thêm Mới Xe Giao Hàng', icon: 'fa-solid fa-truck', prefix: '' },
                policies: { title: 'Thêm Mới Chính Sách Chiết Khấu', icon: 'fa-solid fa-percent', prefix: 'CS' }
            };

            const config = names[type] || { title: 'Thêm Mới', icon: 'fa-solid fa-plus', prefix: '' };
            this.modalTitle = config.title;
            this.modalIcon = config.icon;

            const randomSuffix = Math.floor(100 + Math.random() * 900);
            this.formData = {
                id: null,
                code: config.prefix ? `${config.prefix}-${randomSuffix}` : '',
                category_id: this.categories.length > 0 ? this.categories[0].id : '',
                brand_id: '',
                manager_name: '',
                is_active: true,
                status: type === 'vehicles' ? 'Sẵn sàng' : 'active',
                current_balance: 0,
                discount_percent: 10,
                capacity_kg: 2500,
                apply_scope: 'Đại lý Cấp 1',
                country: 'Việt Nam'
            };

            if (type === 'routes') {
                this.routeStops = ['Thanh Xuân', 'Giải Phóng', 'Trường Chinh', 'Nguyễn Trãi', 'Hà Đông'];
                this.formData.distance_km = 38;
                this.formData.toll_fee = 250000;
                this.formData.toll_fee_display = '250.000';
                this.formData.departure_warehouse = 'Kho Mê Linh';
                this.formData.name = 'Tuyến 01: Kho Mê Linh - Giải Phóng - Trường Chinh - Nguyễn Trãi';
                this.initRouteGeo();
            }

            this.showModal = true;
        },

        // Open Edit Modal
        openEdit(type, item) {
            this.modalType = type;
            const titles = {
                warehouses: 'Chỉnh Sửa Kho Hàng: ' + item.name,
                branches: 'Chỉnh Sửa Chi Nhánh: ' + item.name,
                fund_types: 'Chỉnh Sửa Loại Quỹ: ' + item.name,
                categories: 'Chỉnh Sửa Phân Loại: ' + item.name,
                brands: 'Chỉnh Sửa Nhãn Hiệu: ' + item.name,
                subcategories: 'Chỉnh Sửa Nhóm Nhỏ: ' + item.name,
                routes: 'Chỉnh Sửa Tuyến GH',
                vehicles: 'Chỉnh Sửa Xe Giao Hàng: ' + item.plate_number,
                policies: 'Chỉnh Sửa Chính Sách: ' + item.name
            };

            this.modalTitle = titles[type] || 'Chỉnh Sửa';
            this.formData = JSON.parse(JSON.stringify(item));

            if (type === 'routes') {
                if (Array.isArray(this.formData.stops)) {
                    this.routeStops = [...this.formData.stops];
                } else if (typeof this.formData.stops === 'string') {
                    this.routeStops = this.formData.stops.split(',').map(s => s.trim()).filter(Boolean);
                } else {
                    this.routeStops = [];
                }
                this.formData.toll_fee_display = new Intl.NumberFormat('vi-VN').format(this.formData.toll_fee || 250000);
                this.initRouteGeo();
            }

            this.showModal = true;
        },

        // Submit Form
        async submitForm() {
            this.isSaving = true;

            const payload = {
                ...this.formData,
                type: this.modalType
            };

            if (this.modalType === 'routes') {
                payload.stops = this.routeStops;
                payload.stops_text = this.routeStops.join(', ');
                payload.distance_km = this.formData.distance_km || 0;
                payload.toll_fee = this.formData.toll_fee || 0;
            }

            try {
                const response = await fetch('{{ url('/api/categories/save-item') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    const savedItem = result.item;
                    const listKey = this.modalType === 'fund_types' ? 'funds' : this.modalType;

                    if (this.formData.id) {
                        // Update existing item in local array
                        const idx = this[listKey].findIndex(x => x.id === savedItem.id);
                        if (idx !== -1) {
                            this[listKey][idx] = savedItem;
                        }
                    } else {
                        // Prepend newly created item
                        this[listKey].unshift(savedItem);
                    }

                    this.showModal = false;
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: result.message || 'Lưu thành công!',
                        showConfirmButton: false,
                        timer: 2500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể lưu',
                        text: result.message || 'Có lỗi xảy ra khi lưu dữ liệu'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối',
                    text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại!'
                });
            } finally {
                this.isSaving = false;
            }
        },

        // Delete Item with SweetAlert2 Confirmation
        async deleteItem(type, item) {
            const typeLabel = {
                warehouses: 'kho hàng',
                branches: 'chi nhánh',
                fund_types: 'tài khoản quỹ',
                categories: 'phân loại hàng',
                brands: 'nhãn hiệu',
                subcategories: 'nhóm nhỏ',
                routes: 'tuyến giao hàng',
                vehicles: 'xe giao hàng',
                policies: 'chính sách bán hàng'
            }[type] || 'mục';

            const itemName = item.name || item.plate_number || item.code || 'này';

            const confirmResult = await Swal.fire({
                title: `Xác nhận xóa ${typeLabel}?`,
                html: `Bạn có chắc chắn muốn xóa <strong>${itemName}</strong> khỏi hệ thống? Thao tác này không thể hoàn tác.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy bỏ',
                reverseButtons: true
            });

            if (!confirmResult.isConfirmed) return;

            try {
                const response = await fetch(`{{ url('/api/categories/delete-item') }}/${type}/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    const listKey = type === 'fund_types' ? 'funds' : type;
                    this[listKey] = this[listKey].filter(x => x.id !== item.id);

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: result.message || 'Đã xóa thành công!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể xóa',
                        text: result.message || 'Bản ghi đang được sử dụng ở phân hệ khác.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi máy chủ',
                    text: 'Không thể xóa vào lúc này. Vui lòng thử lại!'
                });
            }
        },

        // --- VEHICLE ASSIGNMENT HANDLERS (Ảnh 1 & Ảnh 2) ---
        getDriverName(id) {
            if (!id) return '';
            const u = this.users.find(x => x.id == id);
            return u ? u.name : '';
        },
        getDriverPhone(id) {
            if (!id) return '';
            const u = this.users.find(x => x.id == id);
            return u ? u.phone : '';
        },
        getRouteName(id) {
            if (!id) return '';
            const r = this.routes.find(x => x.id == id);
            return r ? r.name : '';
        },
        getConflictVehicle(routeId) {
            if (!routeId || !this.selectedVehicleForAssignment) return null;
            return this.vehicles.find(v => 
                String(v.id) !== String(this.selectedVehicleForAssignment.id) &&
                String(v.delivery_route_id) === String(routeId)
            ) || null;
        },
        getSelectedRouteObj() {
            if (!this.asgForm.route_id) return null;
            return this.routes.find(r => String(r.id) === String(this.asgForm.route_id)) || null;
        },
        getCurrentAssignedRoute() {
            if (!this.selectedVehicleForAssignment) return null;
            const routeId = this.selectedVehicleForAssignment.delivery_route_id;
            if (routeId) {
                const found = this.routes.find(r => String(r.id) === String(routeId));
                if (found) return found;
            }
            if (this.selectedVehicleForAssignment.current_route_name) {
                return { name: this.selectedVehicleForAssignment.current_route_name };
            }
            const activeAss = this.assignments.find(a => 
                (a.vehiclePlate === this.selectedVehicleForAssignment.plate_number || a.vehicleId == this.selectedVehicleForAssignment.id) && 
                a.status === 'Đang áp dụng'
            );
            if (activeAss && activeAss.routeZone) {
                const found = this.routes.find(r => r.name === activeAss.routeZone);
                if (found) return found;
                return { name: activeAss.routeZone };
            }
            return null;
        },
        getCurrentActiveAssignment() {
            if (!this.selectedVehicleForAssignment) return null;
            return this.assignments.find(a => 
                (a.vehiclePlate === this.selectedVehicleForAssignment.plate_number || a.vehicleId == this.selectedVehicleForAssignment.id) && 
                a.status === 'Đang áp dụng'
            ) || null;
        },
        get filteredAssignments() {
            return this.assignments.filter(item => {
                if (this.viewOnlyCurrentVehicle && this.selectedVehicleForAssignment) {
                    if (item.vehiclePlate !== this.selectedVehicleForAssignment.plate_number && item.vehicleId != this.selectedVehicleForAssignment.id) {
                        return false;
                    }
                }
                if (this.asgStatusFilter !== 'all' && item.status !== this.asgStatusFilter) {
                    return false;
                }
                if (this.asgSearch) {
                    const q = this.asgSearch.toLowerCase().trim();
                    const str = `${item.id || ''} ${item.vehiclePlate || ''} ${item.driverName || ''} ${item.copilotName || ''} ${item.managerName || ''} ${item.routeZone || ''} ${item.notes || ''}`.toLowerCase();
                    if (!str.includes(q)) return false;
                }
                return true;
            });
        },
        openAssignment(veh) {
            this.activeTab = 'vehicles';
            this.selectedVehicleForAssignment = veh;
            const today = new Date().toISOString().split('T')[0];

            const driverName = veh.driver ? veh.driver.name : (this.getDriverName(veh.driver_id) || '');
            const driverPhone = veh.driver ? veh.driver.phone : (this.getDriverPhone(veh.driver_id) || '');
            const copilotName = veh.copilot_name || '';

            // Check if matches preset driver
            const matchedDrv = this.asgPresetDrivers.find(d => d.name === driverName);
            // Check if matches preset copilot
            const matchedCop = this.asgPresetCopilots.find(c => c.name === copilotName);

            // Active assignment if any
            const activeAss = this.assignments.find(a => (a.vehiclePlate === veh.plate_number || a.vehicleId == veh.id) && a.status === 'Đang áp dụng');

            let matchedRouteId = veh.delivery_route_id ? String(veh.delivery_route_id) : (activeAss?.routeId ? String(activeAss.routeId) : '');
            let matchedRouteZone = '';
            if (matchedRouteId) {
                const r = this.routes.find(x => String(x.id) === String(matchedRouteId));
                if (r) matchedRouteZone = r.name;
            }

            this.asgForm = {
                driver_select: matchedDrv ? matchedDrv.name : (driverName ? 'CUSTOM' : (this.asgPresetDrivers[0]?.name || 'CUSTOM')),
                driver_name: driverName || (this.asgPresetDrivers[0]?.name || 'Bùi Văn Thành'),
                driver_phone: driverPhone || (this.asgPresetDrivers[0]?.phone || '0966 334 455'),
                copilot_select: matchedCop ? matchedCop.name : (copilotName ? 'CUSTOM' : 'CUSTOM'),
                copilot_name: copilotName,
                copilot_phone: activeAss?.copilotPhone || '',
                manager_select: activeAss?.managerName || 'Hoàng Văn Khoa',
                manager_name: activeAss?.managerName || 'Hoàng Văn Khoa',
                manager_phone: activeAss?.managerPhone || '0988 222 111',
                effective_from: today,
                effective_to: '',
                is_indefinite: true,
                route_id: matchedRouteId,
                route_zone: matchedRouteZone,
                shift: activeAss?.shift || 'Toàn thời gian (07:30 - 17:30)',
                assigned_by: activeAss?.assignedBy || 'Hoàng Văn Khoa (Trưởng đội xe)',
                notes: ''
            };

            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        switchAssignmentVehicle(vehId) {
            const v = this.vehicles.find(x => String(x.id) === String(vehId));
            if (v) {
                this.openAssignment(v);
            }
        },
        handleSelectDriverPreset(val) {
            if (val === 'CUSTOM') return;
            const found = this.asgPresetDrivers.find(d => d.name === val);
            if (found) {
                this.asgForm.driver_name = found.name;
                this.asgForm.driver_phone = found.phone;
            }
        },
        handleSelectCopilotPreset(val) {
            if (val === 'CUSTOM') return;
            const found = this.asgPresetCopilots.find(c => c.name === val);
            if (found) {
                this.asgForm.copilot_name = found.name;
                this.asgForm.copilot_phone = found.phone;
            }
        },
        handleSelectManagerPreset(val) {
            if (val === 'CUSTOM') return;
            const found = this.asgPresetManagers.find(m => m.name === val);
            if (found) {
                this.asgForm.manager_name = found.name;
                this.asgForm.manager_phone = found.phone;
            }
        },
        handleSelectRoute(routeId) {
            this.asgForm.route_id = routeId;
            if (!routeId) {
                this.asgForm.route_zone = '';
                return;
            }
            const found = this.routes.find(r => String(r.id) === String(routeId));
            if (found) {
                this.asgForm.route_zone = found.name;
            }
        },
        async saveAssignment() {
            if (!this.asgForm.driver_name.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Thiếu thông tin',
                    text: 'Vui lòng chọn hoặc nhập tên Lái xe chính!'
                });
                return;
            }

            const currentVeh = this.selectedVehicleForAssignment;
            if (!currentVeh) return;

            const selectedRoute = this.getSelectedRouteObj();
            const routeZoneName = selectedRoute ? selectedRoute.name : (this.asgForm.route_zone || 'Tuyến tự do / Nội bộ');

            const payload = {
                vehicleId: currentVeh.id,
                vehiclePlate: currentVeh.plate_number,
                driverName: this.asgForm.driver_name.trim(),
                driverPhone: this.asgForm.driver_phone.trim(),
                copilotName: this.asgForm.copilot_name.trim() || 'Không có phụ xe',
                copilotPhone: this.asgForm.copilot_phone.trim(),
                managerName: this.asgForm.manager_name.trim() || 'Hoàng Văn Khoa',
                managerPhone: this.asgForm.manager_phone.trim() || '0988 222 111',
                assignedDate: new Date().toISOString().split('T')[0],
                effectiveFrom: this.asgForm.effective_from,
                effectiveTo: this.asgForm.is_indefinite ? 'Không thời hạn' : (this.asgForm.effective_to || 'Không thời hạn'),
                assignedBy: this.asgForm.assigned_by.trim() || 'Hoàng Văn Khoa (Trưởng đội xe)',
                shift: this.asgForm.shift,
                routeId: this.asgForm.route_id || null,
                routeZone: routeZoneName,
                status: 'Đang áp dụng',
                notes: this.asgForm.notes.trim()
            };

            this.isSavingAsg = true;
            try {
                const response = await fetch('{{ url("/vehicle-assignments/save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.success) {
                    const todayStr = new Date().toISOString().split('T')[0];
                    // Mark previous active assignment of this vehicle as completed
                    this.assignments.forEach(a => {
                        if (a.vehiclePlate === currentVeh.plate_number && a.status === 'Đang áp dụng') {
                            a.status = 'Hoàn thành';
                            a.effectiveTo = todayStr;
                        }
                    });

                    // Add new assignment to top of list
                    if (res.assignment) {
                        this.assignments.unshift(res.assignment);
                    }

                    // Update local vehicle object
                    const vIdx = this.vehicles.findIndex(v => v.id == currentVeh.id);
                    if (vIdx !== -1) {
                        if (res.vehicle) {
                            this.vehicles[vIdx] = res.vehicle;
                            this.selectedVehicleForAssignment = res.vehicle;
                        } else {
                            this.vehicles[vIdx].copilot_name = payload.copilotName;
                            this.vehicles[vIdx].delivery_route_id = payload.routeId;
                            this.selectedVehicleForAssignment = { ...this.vehicles[vIdx] };
                        }
                        // If route assigned, clear on other vehicles
                        if (payload.routeId) {
                            this.vehicles.forEach(v => {
                                if (v.id != currentVeh.id && v.delivery_route_id == payload.routeId) {
                                    v.delivery_route_id = null;
                                }
                            });
                        }
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Đã lưu & áp dụng phân công tuyến ${routeZoneName} cho xe ${currentVeh.plate_number}!`,
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể lưu',
                        text: res.message || 'Lỗi khi lưu phân công xe.'
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối',
                    text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại!'
                });
            } finally {
                this.isSavingAsg = false;
            }
        },
        async terminateAssignment(recId) {
            const confirmResult = await Swal.fire({
                title: 'Kết thúc đợt phân công?',
                text: 'Bạn có chắc chắn muốn kết thúc đợt phân công này không?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Đồng ý kết thúc',
                cancelButtonText: 'Hủy bỏ'
            });
            if (!confirmResult.isConfirmed) return;

            try {
                const response = await fetch('{{ url("/vehicle-assignments/terminate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: recId })
                });
                const res = await response.json();
                if (res.success) {
                    const todayStr = new Date().toISOString().split('T')[0];
                    const rec = this.assignments.find(a => a.id == recId);
                    if (rec) {
                        rec.status = 'Hoàn thành';
                        rec.effectiveTo = todayStr;
                    }
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Đã kết thúc đợt phân công!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } catch (e) {
                console.error(e);
            }
        },
        async deleteAssignment(recId) {
            const confirmResult = await Swal.fire({
                title: 'Xóa bản ghi phân công?',
                text: 'Bạn có chắc chắn muốn xóa bản ghi phân công này khỏi lịch sử?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy bỏ'
            });
            if (!confirmResult.isConfirmed) return;

            try {
                const response = await fetch(`{{ url('/vehicle-assignments') }}/${recId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const res = await response.json();
                if (res.success) {
                    this.assignments = this.assignments.filter(a => a.id != recId);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Đã xóa bản ghi phân công!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } catch (e) {
                console.error(e);
            }
        }
    };
}
</script>
@endsection
