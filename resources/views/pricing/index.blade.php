@extends('layouts.app')

@section('title', 'Cấu hình giá - Đại Sơn ERP')

@section('content')
<div class="space-y-4" x-data="priceTierManager()">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left: Title & Icon -->
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 shrink-0 shadow-xs">
                    <i class="fa-solid fa-tag text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 leading-tight">Cấu hình giá</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Cấu hình danh mục tên và ký hiệu viết tắt các mức giá bán sản phẩm (Đại lý C1, Đại lý C2, Bán lẻ, Dự án, Thợ thi công...)</p>
                </div>
            </div>

            <!-- Right: Search, Filter Tabs & Add Button -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Tìm theo tên, ký hiệu..." 
                        class="pl-8 pr-3 py-1.5 text-xs rounded-full border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-white w-48 sm:w-60 shadow-2xs"
                    >
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>

                <!-- Status Filter Pills -->
                <div class="bg-slate-100/90 rounded-xl p-1 flex items-center gap-0.5 text-xs font-semibold border border-slate-200/60 shadow-2xs">
                    <button 
                        type="button" 
                        @click="statusFilter = 'all'" 
                        :class="statusFilter === 'all' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800'" 
                        class="px-3 py-1 rounded-lg transition-all cursor-pointer select-none"
                    >
                        Tất cả
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'Hoạt động'" 
                        :class="statusFilter === 'Hoạt động' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800'" 
                        class="px-3 py-1 rounded-lg transition-all cursor-pointer select-none"
                    >
                        Hoạt động
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'Tạm ngưng'" 
                        :class="statusFilter === 'Tạm ngưng' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800'" 
                        class="px-3 py-1 rounded-lg transition-all cursor-pointer select-none"
                    >
                        Tạm ngưng
                    </button>
                </div>

                <!-- Add Button -->
                <button 
                    type="button" 
                    @click="openCreateModal()" 
                    class="px-4 py-2 rounded-xl bg-[#0284c7] hover:bg-[#0369a1] text-white text-xs font-extrabold flex items-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer whitespace-nowrap"
                >
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Thêm Mới Cấu hình giá</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-white text-slate-800 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4 w-16 text-center text-slate-600">STT</th>
                        <th class="py-3.5 px-4 w-32 text-center text-slate-800 tracking-wider">KÝ HIỆU</th>
                        <th class="py-3.5 px-4 text-slate-800 tracking-wider">TÊN MỨC GIÁ</th>
                        <th class="py-3.5 px-4 text-center w-40 text-slate-800 tracking-wider">TRẠNG THÁI</th>
                        <th class="py-3.5 px-4 text-center w-28 text-slate-800 tracking-wider">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(item, index) in filteredItems" :key="item.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- STT -->
                            <td class="py-3 px-4 text-center text-slate-400 font-bold align-middle" x-text="index + 1"></td>
                            
                            <!-- KÝ HIỆU -->
                            <td class="py-3 px-4 text-center align-middle">
                                <span class="inline-block px-2.5 py-1 bg-sky-50 text-sky-800 border border-sky-200 rounded-lg font-mono font-bold text-xs uppercase" x-text="item.code || '---'"></span>
                            </td>

                            <!-- TÊN MỨC GIÁ -->
                            <td class="py-3 px-4 align-middle">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-tag text-sky-500 text-sm shrink-0"></i>
                                    <span class="font-extrabold text-slate-900 text-sm" x-text="item.name"></span>
                                </div>
                            </td>

                            <!-- TRẠNG THÁI -->
                            <td class="py-3 px-4 text-center align-middle">
                                <template x-if="item.status === 'Hoạt động'">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-[10px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Hoạt động</span>
                                    </span>
                                </template>
                                <template x-if="item.status !== 'Hoạt động'">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-600 border border-slate-300 rounded-full font-bold text-[10px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>Tạm ngưng</span>
                                    </span>
                                </template>
                            </td>

                            <!-- THAO TÁC -->
                            <td class="py-3 px-4 text-center align-middle">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button 
                                        type="button" 
                                        @click="openEditModal(item)" 
                                        class="p-1.5 bg-slate-100 hover:bg-sky-100 text-slate-500 hover:text-sky-700 rounded-lg transition-colors cursor-pointer" 
                                        title="Chỉnh sửa bản ghi"
                                    >
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="deleteItem(item)" 
                                        class="p-1.5 bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-700 rounded-lg transition-colors cursor-pointer" 
                                        title="Xóa bản ghi"
                                    >
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredItems.length === 0">
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-tag text-3xl mb-2 text-slate-300 block"></i>
                                <span>Không tìm thấy mức giá nào phù hợp</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm Mới / Chỉnh Sửa Cấu Hình Giá -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5">
        <div @click.away="showModal = false" class="bg-white rounded-2xl w-full max-w-xl flex flex-col p-6 shadow-2xl border border-slate-200 transition-all duration-200" x-transition>
            <!-- Modal Header -->
            <div class="shrink-0 flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-sky-100 text-sky-700 rounded-xl">
                        <i class="fa-solid fa-tag text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base" x-text="modalTitle"></h3>
                        <p class="text-xs text-slate-500">Cấu hình tham số và thiết lập thông tin danh mục hệ thống</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="showModal = false" 
                    class="text-slate-400 hover:text-slate-600 p-1.5 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer"
                >
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form @submit.prevent="submitForm()" class="flex-1 flex flex-col min-h-0 pt-4 space-y-4">
                <!-- Info Notice Box -->
                <div class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl flex items-start space-x-2.5">
                    <i class="fa-solid fa-tag text-blue-600 mt-0.5 shrink-0"></i>
                    <div class="text-xs text-blue-900 leading-relaxed">
                        <span class="font-bold">Cài đặt tên và ký hiệu mức giá:</span> Nhập tên và ký hiệu viết tắt dùng cho sản phẩm (ví dụ: <em>DLC1 - Giá Đại lý C1, DLC2 - Giá Đại lý C2, BL - Giá Bán lẻ, DA - Giá Dự án, THO - Giá Thợ...</em>).
                    </div>
                </div>

                <!-- Input Code & Name Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Input Code (Ký hiệu) -->
                    <div class="sm:col-span-1">
                        <label class="block font-bold text-slate-700 mb-1.5 text-xs">Ký Hiệu / Viết Tắt</label>
                        <input 
                            type="text" 
                            x-model="formData.code" 
                            placeholder="VD: DLC1, BL, DA..." 
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-900 uppercase focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500"
                        >
                        <p class="text-[10px] text-slate-400 mt-1">Ký hiệu viết tắt (VD: DLC1)</p>
                    </div>

                    <!-- Input Name (Tên mức giá) -->
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1.5 text-xs">Tên Mức Giá <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            required 
                            x-model="formData.name" 
                            placeholder="VD: Giá Bán lẻ, Giá Đại lý Cấp 1, Giá Dự án..." 
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500"
                        >
                        <p class="text-[10px] text-slate-400 mt-1">Tên đầy đủ của phân khúc giá</p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="shrink-0 pt-4 border-t border-slate-100 flex items-center justify-between mt-2 bg-white">
                    <!-- Status selector -->
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-slate-700 text-xs">Trạng thái:</span>
                        <button
                            type="button"
                            @click="formData.status = 'Hoạt động'"
                            :class="formData.status === 'Hoạt động' ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                        >
                            Hoạt động
                        </button>
                        <button
                            type="button"
                            @click="formData.status = 'Tạm ngưng'"
                            :class="formData.status === 'Tạm ngưng' ? 'bg-rose-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                        >
                            Tạm ngưng
                        </button>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all cursor-pointer"
                        >
                            Hủy Bỏ
                        </button>
                        <button
                            type="submit"
                            :disabled="isSaving"
                            class="px-5 py-2 bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-md transition-all active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            <span x-text="isSaving ? 'Đang lưu...' : (formData.id ? 'Lưu Cập Nhật' : 'Xác Nhận Thêm Mới')"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function priceTierManager() {
    return {
        search: '',
        statusFilter: 'all',
        showModal: false,
        modalTitle: 'Thêm Mới Cấu hình giá',
        isSaving: false,
        formData: {
            id: null,
            code: '',
            name: '',
            status: 'Hoạt động'
        },
        items: @json($priceTiers ?? []),

        get filteredItems() {
            let res = this.items;

            // Filter status
            if (this.statusFilter !== 'all') {
                res = res.filter(x => x.status === this.statusFilter);
            }

            // Search query
            const q = this.search.trim().toLowerCase();
            if (q) {
                res = res.filter(x => 
                    (x.name && x.name.toLowerCase().includes(q)) ||
                    (x.code && x.code.toLowerCase().includes(q))
                );
            }

            return res;
        },

        openCreateModal() {
            this.modalTitle = 'Thêm Mới Cấu hình giá';
            this.formData = {
                id: null,
                code: '',
                name: '',
                status: 'Hoạt động'
            };
            this.showModal = true;
        },

        openEditModal(item) {
            this.modalTitle = 'Chỉnh Sửa Cấu hình giá';
            this.formData = {
                id: item.id,
                code: item.code || '',
                name: item.name,
                status: item.status || 'Hoạt động'
            };
            this.showModal = true;
        },

        async submitForm() {
            this.isSaving = true;
            try {
                const response = await fetch('{{ url('/api/price-tiers/save') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.formData)
                });

                const result = await response.json();

                if (result.success) {
                    const saved = result.item;
                    if (this.formData.id) {
                        const idx = this.items.findIndex(x => x.id === saved.id);
                        if (idx !== -1) {
                            this.items[idx] = saved;
                        }
                    } else {
                        this.items.push(saved);
                    }

                    this.showModal = false;
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: result.message || 'Lưu thành công!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: result.message || 'Không thể lưu bản ghi!'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Không thể kết nối máy chủ.'
                });
            } finally {
                this.isSaving = false;
            }
        },

        async deleteItem(item) {
            const confirmResult = await Swal.fire({
                title: 'Xác nhận xóa mức giá?',
                html: `Bạn có chắc chắn muốn xóa <strong>${item.name}</strong> khỏi hệ thống?`,
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
                const response = await fetch(`{{ url('/api/price-tiers') }}/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.items = this.items.filter(x => x.id !== item.id);
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
                        text: result.message || 'Có lỗi xảy ra khi xóa'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Không thể kết nối máy chủ.'
                });
            }
        }
    };
}
</script>
@endsection
