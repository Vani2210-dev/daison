@extends('layouts.app')

@section('title', 'Cấu Hình Hệ Thống & Thông Tin Công Ty | ERP Đại Sơn')

@section('content')
<div class="space-y-4 font-sans text-slate-800 antialiased w-full pb-20" x-data="settingsApp()">

    <!-- 1. Header Banner -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/dashboard') }}" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center shrink-0 transition-transform hover:scale-105 cursor-pointer" title="Quay lại Dashboard">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Cấu Hình Hệ Thống</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-800 text-[11px] font-extrabold border border-sky-300">Quản trị</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Cấu hình thông tin pháp lý công ty, thương hiệu và danh sách tài khoản ngân hàng nhận thanh toán</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="openCreateBankModal()" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-amber-600/20 flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Thêm Tài Khoản Ngân Hàng</span>
            </button>
        </div>
    </div>

    <!-- 2. Sub-Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200 shadow-2xs flex items-center gap-2 overflow-x-auto">
        <button type="button" @click="activeTab = 'company'" 
                :class="activeTab === 'company' ? 'bg-[#1b4a8c] text-white shadow-md shadow-[#1b4a8c]/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all cursor-pointer select-none">
            <i class="fa-solid fa-building"></i>
            <span>Thông tin Công ty</span>
        </button>

        <button type="button" @click="activeTab = 'banks'" 
                :class="activeTab === 'banks' ? 'bg-[#1b4a8c] text-white shadow-md shadow-[#1b4a8c]/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all cursor-pointer select-none">
            <i class="fa-solid fa-building-columns"></i>
            <span>Tài khoản Ngân hàng</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black" :class="activeTab === 'banks' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" x-text="bankAccounts.length"></span>
        </button>
    </div>

    <!-- 3. TAB 1: THÔNG TIN CÔNG TY -->
    <div x-show="activeTab === 'company'" class="space-y-4">
        <form action="{{ route('system_settings.update_company') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                
                <!-- Left Column: Form Fields (8 cols) -->
                <div class="lg:col-span-8 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs"><i class="fa-solid fa-id-card"></i></span>
                            <h2 class="font-black text-slate-900 text-sm sm:text-base">Hồ Sơ Pháp Lý & Nhận Diện Doanh Nghiệp</h2>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium">* Các mục có dấu sao là bắt buộc</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <!-- Tên công ty viết gọn -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tên công ty / Thương hiệu <span class="text-rose-500">*</span></label>
                            <input type="text" name="company_name" x-model="companyForm.company_name" required
                                   placeholder="VD: Công ty Cổ phần Đại Sơn" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition-all">
                        </div>

                        <!-- Tên pháp lý trên GPKD -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tên pháp lý đầy đủ trên ĐKKD</label>
                            <input type="text" name="company_legal_name" x-model="companyForm.company_legal_name"
                                   placeholder="VD: CÔNG TY CỔ PHẦN THƯƠNG MẠI & DỊCH VỤ ĐẠI SƠN" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition-all">
                        </div>

                        <!-- Mã số thuế -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Mã số thuế (MST)</label>
                            <div class="relative">
                                <i class="fa-solid fa-receipt absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="tax_code" x-model="companyForm.tax_code"
                                       placeholder="Nhập mã số thuế..." 
                                       class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-slate-900 bg-white focus:border-sky-500 outline-none">
                            </div>
                        </div>

                        <!-- Hotline / CSKH -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Hotline / Tổng đài CSKH</label>
                            <div class="relative">
                                <i class="fa-solid fa-headset absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="hotline" x-model="companyForm.hotline"
                                       placeholder="Nhập số hotline..." 
                                       class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 bg-white focus:border-sky-500 outline-none">
                            </div>
                        </div>

                        <!-- Số điện thoại văn phòng -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Điện thoại bàn / Văn phòng</label>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="phone" x-model="companyForm.phone"
                                       placeholder="Nhập số điện thoại..." 
                                       class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 bg-white focus:border-sky-500 outline-none">
                            </div>
                        </div>

                        <!-- Email liên hệ -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email doanh nghiệp</label>
                            <div class="relative">
                                <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="email" name="email" x-model="companyForm.email"
                                       placeholder="VD: contact@daison.vn" 
                                       class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 bg-white focus:border-sky-500 outline-none">
                            </div>
                        </div>

                        <!-- Website -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Website chính thức</label>
                            <div class="relative">
                                <i class="fa-solid fa-globe absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="website" x-model="companyForm.website"
                                       placeholder="VD: https://daison.vn" 
                                       class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 bg-white focus:border-sky-500 outline-none">
                            </div>
                        </div>

                        <!-- Người đại diện pháp luật -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Người đại diện pháp luật</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="representative_name" x-model="companyForm.representative_name"
                                       placeholder="Họ & Tên Giám đốc" 
                                       class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 bg-white focus:border-sky-500 outline-none">
                                <input type="text" name="representative_title" x-model="companyForm.representative_title"
                                       placeholder="Chức vụ (VD: Giám Đốc)" 
                                       class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-medium text-slate-700 bg-white focus:border-sky-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Địa chỉ trụ sở chính -->
                    <div class="text-xs">
                        <label class="block font-bold text-slate-700 mb-1">Địa chỉ trụ sở chính (Xuất hóa đơn VAT)</label>
                        <div class="relative">
                            <i class="fa-solid fa-location-dot absolute left-3.5 top-3 text-slate-400"></i>
                            <textarea name="address" x-model="companyForm.address" rows="2"
                                      placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố..." 
                                      class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 font-medium text-slate-900 bg-white focus:border-sky-500 outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Địa chỉ kho tổng -->
                    <div class="text-xs">
                        <label class="block font-bold text-slate-700 mb-1">Địa chỉ Kho Tổng / Trung tâm điều phối hàng</label>
                        <div class="relative">
                            <i class="fa-solid fa-warehouse absolute left-3.5 top-3 text-slate-400"></i>
                            <textarea name="warehouse_address" x-model="companyForm.warehouse_address" rows="2"
                                      placeholder="Địa chỉ kho nhận hàng & xuất kho..." 
                                      class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 font-medium text-slate-900 bg-white focus:border-sky-500 outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Nút Lưu -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black text-xs shadow-md shadow-sky-600/20 transition-all flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Lưu Cập Nhật Thông Tin</span>
                        </button>
                    </div>
                </div>

                <!-- Right Column: Logo & Preview Card (4 cols) -->
                <div class="lg:col-span-4 space-y-4">
                    
                    <!-- Logo Upload Card -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-2xs space-y-3">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-2.5">
                            <span class="w-6 h-6 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center text-xs"><i class="fa-solid fa-image"></i></span>
                            <h3 class="font-black text-slate-800 text-xs uppercase tracking-wider">Logo Doanh Nghiệp</h3>
                        </div>

                        <div class="flex flex-col items-center justify-center p-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl relative group hover:border-sky-400 transition-colors">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="max-h-24 max-w-full object-contain mb-2 drop-shadow-xs">
                            </template>
                            <template x-if="!logoPreview">
                                <img src="{{ asset($company->logo_url ?: 'images/logo-daison.png') }}" class="max-h-24 max-w-full object-contain mb-2 drop-shadow-xs" onerror="this.src='/images/logo.png'">
                            </template>

                            <label class="mt-2 px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 rounded-xl text-xs font-bold text-slate-700 cursor-pointer shadow-2xs flex items-center gap-1.5 transition-all">
                                <i class="fa-solid fa-cloud-arrow-up text-sky-600"></i>
                                <span>Tải logo mới</span>
                                <input type="file" name="logo" @change="previewImage($event)" accept="image/*" class="hidden">
                            </label>
                            <span class="text-[10px] text-slate-400 mt-1">Định dạng PNG, JPG, SVG (Tối đa 2MB)</span>
                        </div>
                    </div>

                    <!-- Live Preview Badge (Bản xem trước Tiêu đề Đơn hàng) -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 border border-slate-700 shadow-md space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-700/80 pb-2">
                            <span class="text-[10px] font-black uppercase text-amber-400 tracking-wider">Tiêu đề in ấn & Hóa đơn</span>
                            <span class="text-[10px] text-slate-400">Xem trước</span>
                        </div>

                        <div class="space-y-1.5 text-xs">
                            <div class="font-black text-sm text-white leading-tight" x-text="companyForm.company_name || 'Tên Công Ty'"></div>
                            <div class="text-[11px] text-slate-300 font-semibold uppercase leading-snug" x-text="companyForm.company_legal_name || ''"></div>
                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5 pt-1">
                                <i class="fa-solid fa-receipt text-[10px] text-amber-400"></i>
                                <span>MST: </span>
                                <span class="font-mono font-bold text-white" x-text="companyForm.tax_code || '--'"></span>
                            </div>
                            <div class="text-[11px] text-slate-400 flex items-start gap-1.5">
                                <i class="fa-solid fa-location-dot text-[10px] text-amber-400 mt-0.5 shrink-0"></i>
                                <span class="line-clamp-2" x-text="companyForm.address || 'Địa chỉ trụ sở...'"></span>
                            </div>
                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-phone text-[10px] text-amber-400"></i>
                                <span x-text="(companyForm.hotline || companyForm.phone) || '--'"></span>
                            </div>
                        </div>

                        <!-- Default Bank Account Preview Tag -->
                        <template x-if="defaultAccount">
                            <div class="mt-3 pt-3 border-t border-slate-700/80 bg-slate-800/80 p-2.5 rounded-xl border border-slate-600/50">
                                <div class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Tài khoản nhận thanh toán:</div>
                                <div class="font-bold text-emerald-400 text-xs" x-text="defaultAccount.bank_name"></div>
                                <div class="font-mono font-black text-white text-xs" x-text="defaultAccount.account_number + ' · ' + defaultAccount.account_holder"></div>
                            </div>
                        </template>
                    </div>

                </div>

            </div>
        </form>
    </div>

    <!-- 4. TAB 2: TÀI KHOẢN NGÂN HÀNG & VIETQR -->
    <div x-show="activeTab === 'banks'" class="space-y-4">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div>
                    <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Tổng số tài khoản</div>
                    <div class="text-xl font-black text-slate-900" x-text="bankAccounts.length + ' tài khoản'"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Tài khoản mặc định</div>
                    <div class="text-xs font-black text-emerald-700 truncate" x-text="defaultAccount ? (defaultAccount.bank_name + ' - ' + defaultAccount.account_number) : 'Chưa thiết lập'"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Mã VietQR 24/7</div>
                    <div class="text-xs font-black text-purple-800">Chuẩn NAPAS VietQR</div>
                </div>
            </div>
        </div>

        <!-- Bank Accounts List (Cards Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="acc in bankAccounts" :key="acc.id">
                <div class="bg-white rounded-2xl p-4 sm:p-5 border transition-all shadow-2xs flex flex-col justify-between gap-3 relative group"
                     :class="acc.is_default ? 'border-emerald-300 ring-2 ring-emerald-400/20 bg-emerald-50/20' : 'border-slate-200 hover:border-sky-300'">
                    
                    <!-- Top Bar: Bank Logo / Code + Default Badge -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-slate-100 font-bold text-xs flex items-center justify-center font-mono text-slate-800 shrink-0 border border-slate-200" x-text="acc.bank_code || 'NH'"></span>
                            <div class="min-w-0">
                                <h3 class="font-black text-slate-900 text-sm truncate" x-text="acc.bank_name"></h3>
                                <span class="text-[11px] text-slate-500 font-medium truncate block" x-text="acc.branch || 'Chi nhánh mặc định'"></span>
                            </div>
                        </div>

                        <template x-if="acc.is_default">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black border border-emerald-300 flex items-center gap-1 shrink-0">
                                <i class="fa-solid fa-check text-[9px]"></i> Mặc định
                            </span>
                        </template>
                    </div>

                    <!-- Account Number & Copy Button -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Số tài khoản:</span>
                            <span class="font-mono font-black text-base text-slate-900 tracking-wider" x-text="acc.account_number"></span>
                        </div>
                        <button type="button" @click="copyText(acc.account_number, 'stk_' + acc.id)" 
                                class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 cursor-pointer shadow-2xs flex items-center gap-1 transition-colors"
                                title="Sao chép số tài khoản">
                            <i class="fa-regular fa-copy"></i>
                            <span x-text="copiedId === ('stk_' + acc.id) ? 'Đã chép' : 'Chép'"></span>
                        </button>
                    </div>

                    <!-- Account Holder -->
                    <div class="text-xs">
                        <span class="text-[10.5px] text-slate-400 font-bold uppercase block">Chủ tài khoản:</span>
                        <span class="font-extrabold text-slate-800 tracking-wide" x-text="acc.account_holder"></span>
                    </div>

                    <!-- Notes -->
                    <template x-if="acc.notes">
                        <div class="text-[11px] text-slate-500 italic bg-slate-50/70 p-2 rounded-lg border border-slate-100" x-text="acc.notes"></div>
                    </template>

                    <!-- Bottom Action Buttons -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <!-- VietQR Preview Button -->
                        <button type="button" @click="openQrModal(acc)" class="text-purple-700 hover:text-purple-800 font-bold flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-qrcode"></i>
                            <span>Mã VietQR</span>
                        </button>

                        <div class="flex items-center gap-1.5">
                            <!-- Đặt làm mặc định -->
                            <template x-if="!acc.is_default">
                                <button type="button" @click="setDefault(acc)" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors cursor-pointer" title="Đặt làm tài khoản thanh toán mặc định">
                                    <i class="fa-regular fa-star"></i>
                                </button>
                            </template>

                            <!-- Sửa -->
                            <button type="button" @click="openEditBankModal(acc)" class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-colors cursor-pointer" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <!-- Xóa -->
                            <button type="button" @click="deleteAccount(acc)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Xóa tài khoản này">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        <template x-if="bankAccounts.length === 0">
            <div class="bg-white rounded-2xl p-10 border border-slate-200 text-center space-y-3">
                <i class="fa-solid fa-building-columns text-4xl text-slate-300"></i>
                <h3 class="font-black text-slate-700 text-base">Chưa có tài khoản ngân hàng nào</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">Vui lòng thêm tài khoản ngân hàng của doanh nghiệp để hiển thị trên hóa đơn và quét mã thanh toán VietQR.</p>
                <button type="button" @click="openCreateBankModal()" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-xl text-xs hover:bg-amber-700 cursor-pointer shadow-md shadow-amber-600/20">
                    <i class="fa-solid fa-plus mr-1"></i> Thêm Tài Khoản Đầu Tiên
                </button>
            </div>
        </template>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 1: THÊM / SỬA TÀI KHOẢN NGÂN HÀNG               -->
    <!-- ======================================================= -->
    <div x-show="showBankModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200" @click.outside="showBankModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fa-solid fa-building-columns"></i></span>
                    <h3 class="font-black text-slate-900 text-base" x-text="isEditMode ? 'Chỉnh Sửa Tài Khoản Ngân Hàng' : 'Thêm Tài Khoản Ngân Hàng Mới'"></h3>
                </div>
                <button type="button" @click="showBankModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form @submit.prevent="submitBankForm()" class="space-y-3.5 text-xs">
                <!-- Chọn Ngân hàng (Có tìm kiếm trực tiếp) -->
                <div class="relative" @click.outside="closeBankDropdown()">
                    <label class="block font-bold text-slate-700 mb-1">Ngân hàng <span class="text-rose-500">*</span></label>
                    <input type="hidden" x-model="bankForm.bank_code" required>
                    
                    <div class="relative">
                        <input type="text"
                               x-model="bankSearchText"
                               @focus="bankDropdownOpen = true; $event.target.select()"
                               @input="bankDropdownOpen = true"
                               @keydown.escape="bankDropdownOpen = false"
                               @keydown.enter.prevent="if (filteredSupportedBanks.length > 0) selectBank(filteredSupportedBanks[0])"
                               placeholder="Gõ tìm ngân hàng (VD: MB, VCB, Techcombank, BIDV...)"
                               class="w-full pl-9 pr-14 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-800 bg-white focus:border-amber-500 outline-none shadow-2xs cursor-text">
                        
                        <!-- Search Icon -->
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>

                        <!-- Action buttons: Clear or Chevron -->
                        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1">
                            <button type="button" x-show="bankSearchText" @click.stop="bankSearchText = ''; bankForm.bank_code = ''; bankDropdownOpen = true" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer" title="Xóa tìm kiếm">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                            <button type="button" @click.stop="bankDropdownOpen = !bankDropdownOpen" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                                <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="bankDropdownOpen ? 'rotate-180' : ''"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Dropdown danh sách ngân hàng -->
                    <div x-show="bankDropdownOpen" x-cloak
                         class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-slate-200 z-50 max-h-56 overflow-y-auto divide-y divide-slate-100">
                        <template x-for="b in filteredSupportedBanks" :key="b.code">
                            <div @click="selectBank(b)"
                                 class="px-3 py-2 hover:bg-amber-50/80 cursor-pointer flex items-center justify-between transition-colors"
                                 :class="bankForm.bank_code === b.code ? 'bg-amber-50 font-bold text-amber-900' : 'text-slate-700'">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-12 text-center py-0.5 rounded text-[10px] font-mono font-black bg-slate-100 text-slate-700 border border-slate-200 shrink-0" x-text="b.code"></span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs truncate" x-text="b.short_name"></div>
                                        <div class="text-[10.5px] text-slate-500 truncate" x-text="b.name"></div>
                                    </div>
                                </div>
                                <template x-if="bankForm.bank_code === b.code">
                                    <i class="fa-solid fa-check text-amber-600 text-xs shrink-0 ml-2"></i>
                                </template>
                            </div>
                        </template>
                        <div x-show="filteredSupportedBanks.length === 0" class="px-4 py-3 text-center text-slate-400 text-xs">
                            Không tìm thấy ngân hàng phù hợp với từ khóa "<span class="font-bold text-slate-600" x-text="bankSearchText"></span>"
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Tên hiển thị ngân hàng -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tên hiển thị <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="bankForm.bank_name" placeholder="VD: MB Bank hoặc Vietcombank" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold bg-white focus:border-amber-500 outline-none" required>
                    </div>

                    <!-- Số tài khoản -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số tài khoản <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="bankForm.account_number" placeholder="Nhập số tài khoản" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-mono font-black text-slate-900 bg-white focus:border-amber-500 outline-none" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Tên chủ tài khoản -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chủ tài khoản (In hoa không dấu) <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="bankForm.account_holder" @input="bankForm.account_holder = bankForm.account_holder.toUpperCase()" placeholder="VD: CONG TY CP TM VA DV DAI SON" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 bg-white focus:border-amber-500 outline-none" required>
                    </div>

                    <!-- Chi nhánh từ hệ thống (Có tìm kiếm) -->
                    <div class="relative" @click.outside="closeBranchDropdown()">
                        <label class="block font-bold text-slate-700 mb-1">Chi nhánh <span class="text-rose-500">*</span></label>
                        <input type="hidden" x-model="bankForm.branch_id" required>

                        <div class="relative">
                            <input type="text"
                                   x-model="branchSearchText"
                                   @focus="branchDropdownOpen = true; $event.target.select()"
                                   @input="branchDropdownOpen = true"
                                   @keydown.escape="branchDropdownOpen = false"
                                   @keydown.enter.prevent="if (filteredBranches.length > 0) selectBranch(filteredBranches[0])"
                                   placeholder="Gõ tìm chi nhánh hệ thống..."
                                   class="w-full pl-8 pr-12 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-800 bg-white focus:border-amber-500 outline-none shadow-2xs cursor-text">

                            <!-- Search Icon -->
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                            </span>

                            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                <button type="button" x-show="branchSearchText" @click.stop="branchSearchText = ''; bankForm.branch_id = ''; bankForm.branch = ''; branchDropdownOpen = true" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer" title="Xóa tìm kiếm">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                                <button type="button" @click.stop="branchDropdownOpen = !branchDropdownOpen" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                                    <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="branchDropdownOpen ? 'rotate-180' : ''"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown danh sách chi nhánh -->
                        <div x-show="branchDropdownOpen" x-cloak
                             class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-slate-200 z-50 max-h-52 overflow-y-auto divide-y divide-slate-100">
                            <template x-for="b in filteredBranches" :key="b.id">
                                <div @click="selectBranch(b)"
                                     class="px-3 py-2 hover:bg-amber-50/80 cursor-pointer flex items-center justify-between transition-colors"
                                     :class="String(bankForm.branch_id) === String(b.id) ? 'bg-amber-50 font-bold text-amber-900' : 'text-slate-700'">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs truncate" x-text="b.name"></div>
                                        <div class="text-[10.5px] text-slate-400 truncate" x-text="b.code ? ('Mã: ' + b.code) : ''"></div>
                                    </div>
                                    <template x-if="String(bankForm.branch_id) === String(b.id)">
                                        <i class="fa-solid fa-check text-amber-600 text-xs shrink-0 ml-2"></i>
                                    </template>
                                </div>
                            </template>
                            <div x-show="filteredBranches.length === 0" class="px-4 py-3 text-center text-slate-400 text-xs">
                                Không có chi nhánh phù hợp
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mục đích sử dụng / Ghi chú -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mục đích sử dụng / Ghi chú</label>
                    <input type="text" x-model="bankForm.notes" placeholder="VD: Nhận tiền chuyển khoản bán hàng & đại lý" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-medium bg-white focus:border-amber-500 outline-none">
                </div>

                <!-- Checkbox Mặc định & Hoạt động -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                        <input type="checkbox" x-model="bankForm.is_default" class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                        <span>Đặt làm tài khoản nhận thanh toán mặc định</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-600">
                        <input type="checkbox" x-model="bankForm.is_active" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <span>Đang kích hoạt sử dụng</span>
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showBankModal = false" class="px-4 py-2 rounded-xl border border-slate-300 font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Hủy</button>
                    <button type="submit" :disabled="isSubmitting" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black cursor-pointer shadow-md shadow-amber-600/20 disabled:opacity-50">
                        <span x-show="!isSubmitting" x-text="isEditMode ? 'Lưu Thay Đổi' : 'Thêm Tài Khoản'"></span>
                        <span x-show="isSubmitting">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 2: XEM & THỬ NGHIỆM QUÉT MÃ VIETQR 24/7        -->
    <!-- ======================================================= -->
    <div x-show="showQrModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-5 space-y-4 shadow-2xl border border-slate-200 text-center" @click.outside="showQrModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="font-black text-slate-900 text-sm">Mã Thanh Toán VietQR 24/7</h3>
                <button type="button" @click="showQrModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <template x-if="activeQrAccount">
                <div class="space-y-3">
                    <!-- Dynamic VietQR Image -->
                    <div class="p-2.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-center">
                        <img :src="getDynamicQrUrl()" class="max-w-[240px] max-h-[300px] rounded-xl shadow-xs object-contain" alt="VietQR Code">
                    </div>

                    <!-- Bank details -->
                    <div class="text-xs space-y-1">
                        <div class="font-black text-slate-900 text-sm" x-text="activeQrAccount.bank_name"></div>
                        <div class="font-mono font-bold text-amber-700 text-base" x-text="activeQrAccount.account_number"></div>
                        <div class="font-extrabold text-slate-700 uppercase text-[11px]" x-text="activeQrAccount.account_holder"></div>
                    </div>

                    <!-- Test custom amount / memo -->
                    <div class="p-2.5 bg-purple-50/70 border border-purple-200 rounded-xl space-y-2 text-left text-xs">
                        <span class="text-[10px] font-bold uppercase text-purple-900 block">Thử nghiệm số tiền & Nội dung:</span>
                        <input type="number" step="1000" x-model="qrTestAmount" placeholder="Số tiền (VD: 500000)" class="w-full px-2.5 py-1.5 rounded-lg border border-purple-300 font-mono font-bold text-xs bg-white outline-none">
                        <input type="text" x-model="qrTestMemo" placeholder="Nội dung CK (VD: DH2026-001)" class="w-full px-2.5 py-1.5 rounded-lg border border-purple-300 font-medium text-xs bg-white outline-none">
                    </div>

                    <button type="button" @click="showQrModal = false" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs cursor-pointer">
                        Đóng
                    </button>
                </div>
            </template>
        </div>
    </div>

</div>

<!-- ALPINE JS SCRIPT CHO CẤU HÌNH HỆ THỐNG -->
<script>
function settingsApp() {
    return {
        activeTab: '{{ $currentTab ?: "company" }}',
        companyForm: @json($company),
        bankAccounts: @json($bankAccounts),
        supportedBanks: @json($supportedBanks),
        branches: @json($branches),

        logoPreview: null,
        copiedId: null,

        // Modal Bank state
        showBankModal: false,
        isEditMode: false,
        isSubmitting: false,
        editingAccountId: null,

        // Searchable Combobox state
        bankSearchText: '',
        bankDropdownOpen: false,
        branchSearchText: '',
        branchDropdownOpen: false,

        bankForm: {
            bank_name: '',
            bank_code: '',
            account_number: '',
            account_holder: '',
            branch_id: '',
            branch: '',
            is_default: false,
            is_active: true,
            notes: ''
        },

        // Modal QR state
        showQrModal: false,
        activeQrAccount: null,
        qrTestAmount: 0,
        qrTestMemo: '',

        get defaultAccount() {
            return this.bankAccounts.find(a => a.is_default) || this.bankAccounts[0] || null;
        },

        get filteredSupportedBanks() {
            if (!this.bankSearchText || !this.bankSearchText.trim()) {
                return this.supportedBanks || [];
            }
            const q = this.bankSearchText.toLowerCase().trim();
            return (this.supportedBanks || []).filter(b => 
                (b.code && b.code.toLowerCase().includes(q)) ||
                (b.short_name && b.short_name.toLowerCase().includes(q)) ||
                (b.name && b.name.toLowerCase().includes(q))
            );
        },

        selectBank(b) {
            this.bankForm.bank_code = b.code;
            this.bankForm.bank_name = b.short_name;
            this.bankSearchText = `${b.code} - ${b.short_name} (${b.name})`;
            this.bankDropdownOpen = false;
        },

        closeBankDropdown() {
            this.bankDropdownOpen = false;
            const cur = (this.supportedBanks || []).find(b => b.code === this.bankForm.bank_code);
            if (cur) {
                this.bankSearchText = `${cur.code} - ${cur.short_name} (${cur.name})`;
            } else if (!this.bankForm.bank_code) {
                this.bankSearchText = '';
            }
        },

        get filteredBranches() {
            if (!this.branchSearchText || !this.branchSearchText.trim()) {
                return this.branches || [];
            }
            const q = this.branchSearchText.toLowerCase().trim();
            return (this.branches || []).filter(b => 
                (b.name && b.name.toLowerCase().includes(q)) ||
                (b.code && b.code.toLowerCase().includes(q)) ||
                (b.address && b.address.toLowerCase().includes(q))
            );
        },

        selectBranch(b) {
            this.bankForm.branch_id = b.id;
            this.bankForm.branch = b.name;
            this.branchSearchText = b.name + (b.code ? ' (' + b.code + ')' : '');
            this.branchDropdownOpen = false;
        },

        closeBranchDropdown() {
            this.branchDropdownOpen = false;
            const cur = (this.branches || []).find(b => String(b.id) === String(this.bankForm.branch_id));
            if (cur) {
                this.branchSearchText = cur.name + (cur.code ? ' (' + cur.code + ')' : '');
            } else if (!this.bankForm.branch_id) {
                this.branchSearchText = '';
            }
        },

        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                this.logoPreview = URL.createObjectURL(file);
            }
        },

        copyText(text, id) {
            navigator.clipboard.writeText(text);
            this.copiedId = id;
            setTimeout(() => { this.copiedId = null; }, 2000);
        },

        openCreateBankModal() {
            this.isEditMode = false;
            this.editingAccountId = null;
            this.bankForm = {
                bank_name: '',
                bank_code: '',
                account_number: '',
                account_holder: '',
                branch_id: '',
                branch: '',
                is_default: false,
                is_active: true,
                notes: ''
            };
            this.bankSearchText = '';
            this.branchSearchText = '';
            this.bankDropdownOpen = false;
            this.branchDropdownOpen = false;
            this.showBankModal = true;
        },

        openEditBankModal(acc) {
            this.isEditMode = true;
            this.editingAccountId = acc.id;
            let branchId = acc.branch_id || '';
            if (!branchId && acc.branch && this.branches) {
                const matched = this.branches.find(b => b.name === acc.branch);
                if (matched) branchId = matched.id;
            }
            const curBank = (this.supportedBanks || []).find(b => b.code === acc.bank_code);
            const curBranch = (this.branches || []).find(b => String(b.id) === String(branchId));

            this.bankForm = {
                bank_name: acc.bank_name,
                bank_code: acc.bank_code,
                account_number: acc.account_number,
                account_holder: acc.account_holder,
                branch_id: branchId,
                branch: acc.branch || '',
                is_default: Boolean(acc.is_default),
                is_active: Boolean(acc.is_active),
                notes: acc.notes || ''
            };
            this.bankSearchText = curBank ? `${curBank.code} - ${curBank.short_name} (${curBank.name})` : (acc.bank_code ? `${acc.bank_code} - ${acc.bank_name}` : '');
            this.branchSearchText = curBranch ? (curBranch.name + (curBranch.code ? ' (' + curBranch.code + ')' : '')) : (acc.branch || '');
            this.bankDropdownOpen = false;
            this.branchDropdownOpen = false;
            this.showBankModal = true;
        },

        async submitBankForm() {
            if (!this.bankForm.bank_code) {
                alert('Vui lòng chọn ngân hàng!');
                return;
            }
            if (!this.bankForm.branch_id) {
                alert('Vui lòng chọn chi nhánh trong hệ thống!');
                return;
            }
            this.isSubmitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const url = this.isEditMode 
                    ? `{{ url('/system-settings/bank-accounts') }}/${this.editingAccountId}` 
                    : `{{ url('/system-settings/bank-accounts') }}`;

                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json' },
                    body: JSON.stringify(this.bankForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Thành công!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi lưu tài khoản.');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async setDefault(acc) {
            if (!confirm(`Bạn có chắc muốn đặt ${acc.bank_name} - ${acc.account_number} làm tài khoản mặc định?`)) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`{{ url('/system-settings/bank-accounts') }}/${acc.id}/default`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.bankAccounts.forEach(a => a.is_default = (a.id === acc.id));
                }
            } catch (e) {
                alert('Lỗi kết nối.');
            }
        },

        async deleteAccount(acc) {
            if (!confirm(`Bạn có chắc chắn muốn xóa tài khoản ${acc.bank_name} - ${acc.account_number}?`)) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`{{ url('/system-settings/bank-accounts') }}/${acc.id}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.bankAccounts = this.bankAccounts.filter(a => a.id !== acc.id);
                }
            } catch (e) {
                alert('Lỗi kết nối.');
            }
        },

        openQrModal(acc) {
            this.activeQrAccount = acc;
            this.qrTestAmount = 0;
            this.qrTestMemo = 'TT DON HANG';
            this.showQrModal = true;
        },

        getDynamicQrUrl() {
            if (!this.activeQrAccount) return '';
            const bin = this.activeQrAccount.bank_code || 'MB';
            const accNum = (this.activeQrAccount.account_number || '').replace(/[^0-9]/g, '');
            let url = `https://img.vietqr.io/image/${bin}-${accNum}-compact2.png`;
            const params = [];
            if (this.qrTestAmount > 0) params.push(`amount=${encodeURIComponent(this.qrTestAmount)}`);
            if (this.qrTestMemo) params.push(`addInfo=${encodeURIComponent(this.qrTestMemo)}`);
            if (this.activeQrAccount.account_holder) params.push(`accountName=${encodeURIComponent(this.activeQrAccount.account_holder)}`);
            if (params.length > 0) url += '?' + params.join('&');
            return url;
        }
    }
}
</script>
@endsection
