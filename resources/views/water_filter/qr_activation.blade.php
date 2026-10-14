<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kích Hoạt Bảo Hành Máy Lọc Nước - Đại Sơn Water</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Be Vietnam Pro"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="min-h-full font-sans text-slate-800 antialiased bg-slate-100 flex flex-col justify-between"
      x-data="qrActivationApp()">

    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-700 via-indigo-700 to-cyan-600 text-white shadow-lg sticky top-0 z-30">
        <div class="max-w-xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-white shadow-inner">
                    <i class="fa-solid fa-faucet-drip text-xl text-cyan-300"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight leading-tight flex items-center gap-1.5">
                        ĐẠI SƠN WATER
                        <span class="inline-block px-1.5 py-0.5 text-[10px] font-semibold bg-cyan-400 text-blue-950 rounded">PRO</span>
                    </h1>
                    <p class="text-xs text-blue-100 font-medium">Hệ thống kích hoạt máy & bảo hành</p>
                </div>
            </div>

            @if(Auth::check())
            <div class="flex items-center gap-2">
                <span class="text-xs bg-white/15 px-2 py-1 rounded-full border border-white/20 font-medium flex items-center gap-1">
                    <i class="fa-solid fa-user-shield text-cyan-300"></i>
                    {{ Str::limit(Auth::user()->name, 12) }}
                </span>
            </div>
            @else
            <a href="{{ route('login') }}" class="text-xs bg-white/20 hover:bg-white/30 text-white font-medium px-2.5 py-1 rounded-lg transition border border-white/30">
                Đăng nhập
            </a>
            @endif
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-xl mx-auto w-full px-4 py-4 space-y-4 flex-grow">

        <!-- Status Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div id="qrCodeContainer" class="p-1.5 bg-slate-50 border border-slate-200 rounded-xl shadow-xs"></div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">{{ $machine->machine_code }}</span>
                            @if($machine->status === 'unactivated')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Tem QR trống
                                </span>
                            @elseif($machine->status === 'pending_activation')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-orange-50 text-orange-700 border border-orange-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                    Chờ duyệt kích hoạt
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    Đã kích hoạt
                                </span>
                            @endif
                        </div>
                        <h2 class="text-lg font-bold text-slate-800 mt-0.5">
                            {{ $machine->machine_model ?: 'Máy lọc nước chưa liên kết' }}
                        </h2>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                            <i class="fa-solid fa-qrcode text-blue-500"></i>
                            Mã tem: <strong class="text-slate-700 font-mono">{{ $machine->qr_code }}</strong>
                            <span class="text-slate-300">•</span>
                            <span class="text-[11px] text-slate-400">Đã quét {{ $machine->view_count }} lần</span>
                        </p>
                    </div>
                </div>
            </div>

            @if($machine->status === 'pending_activation')
            <div class="mt-3.5 p-3 rounded-xl bg-orange-50/80 border border-orange-200/80 text-xs text-orange-900 flex items-start gap-2.5">
                <i class="fa-solid fa-clock-rotate-left text-orange-600 mt-0.5 text-sm"></i>
                <div class="leading-relaxed">
                    <strong>Thông tin đã được KTV gửi lên hệ thống.</strong> Đang chờ Quản trị viên kiểm duyệt để chính thức kích hoạt phiếu bảo hành cho khách hàng. Bạn có thể cập nhật lại thông tin nếu cần thiết.
                </div>
            </div>
            @else
            <div class="mt-3.5 p-3 rounded-xl bg-blue-50/80 border border-blue-200/80 text-xs text-blue-900 flex items-start gap-2.5">
                <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 text-sm"></i>
                <div class="leading-relaxed">
                    <strong>Tem QR mới sẵn sàng kích hoạt:</strong> Kỹ thuật viên sau khi lắp máy tại nhà khách hàng vui lòng điền đầy đủ thông tin bên dưới và bấm nút <em>"Gửi yêu cầu kích hoạt"</em>.
                </div>
            </div>
            @endif
        </div>

        <!-- Activation Form -->
        <form @submit.prevent="submitActivation" class="space-y-4">
            @csrf

            <!-- Section 1: Customer Information -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 space-y-3.5">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                    <h3 class="text-sm font-bold text-slate-800">Thông tin khách hàng & Địa điểm lắp</h3>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Họ và tên khách hàng <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-user text-xs"></i>
                        </span>
                        <input type="text" x-model="form.customer_name" required placeholder="Ví dụ: Nguyễn Văn An"
                               class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Số điện thoại khách hàng <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </span>
                            <input type="tel" x-model="form.customer_phone" required placeholder="0912 xxx xxx"
                                   class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nguồn nước sử dụng</label>
                        <div class="relative">
                            <select x-model="form.water_source_type"
                                    class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 font-medium">
                                <option value="Nước máy thành phố">Nước máy thành phố</option>
                                <option value="Nước giếng khoan / Nước ngầm">Nước giếng khoan / Nước ngầm</option>
                                <option value="Nước mưa / Bể trữ ngầm">Nước mưa / Bể trữ ngầm</option>
                                <option value="Khác">Nguồn nước khác</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Địa chỉ lắp đặt <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 pt-2 text-slate-400">
                            <i class="fa-solid fa-location-dot text-xs"></i>
                        </span>
                        <textarea x-model="form.customer_address" required rows="2" placeholder="Số nhà, đường, thôn/ấp, xã/phường, quận/huyện..."
                                  class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 font-medium"></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Machine Details -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 space-y-3.5">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">2</span>
                    <h3 class="text-sm font-bold text-slate-800">Thông tin Máy Lọc Nước</h3>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Model máy lọc nước <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <select x-model="form.machine_model_id" @change="onModelChange($event)"
                                class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 font-medium">
                            <option value="">-- Chọn Model máy trong danh mục --</option>
                            @foreach($machineModels as $model)
                                <option value="{{ $model->id }}"
                                        data-name="{{ $model->name }}"
                                        data-warranty="{{ $model->warranty_months ?? 24 }}"
                                        {{ $machine->machine_model_id == $model->id ? 'selected' : '' }}>
                                    {{ $model->name }} ({{ $model->brand ?: 'Đại Sơn' }}) - {{ $model->stages_count ? $model->stages_count . ' cấp lọc' : 'RO Pro' }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" x-model="form.machine_model" required placeholder="Hoặc nhập tên Model máy..."
                               class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Số Serial / Mã máy</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i class="fa-solid fa-barcode text-xs"></i>
                            </span>
                            <input type="text" x-model="form.machine_serial" placeholder="VD: SN-202610-88"
                                   class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 font-medium uppercase">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ngày lắp đặt</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i class="fa-regular fa-calendar-check text-xs"></i>
                            </span>
                            <input type="datetime-local" x-model="form.installed_at"
                                   class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 font-medium">
                        </div>
                    </div>
                </div>

                <!-- Warranty months & Filter replace cycle -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Thời hạn bảo hành (Tháng) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" x-model.number="form.warranty_months" min="1" max="120"
                                   class="w-20 px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl font-bold text-center focus:bg-white focus:ring-2 focus:ring-indigo-500/30">
                            <div class="flex items-center gap-1">
                                <button type="button" @click="form.warranty_months = 12"
                                        :class="form.warranty_months == 12 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg transition">12T</button>
                                <button type="button" @click="form.warranty_months = 24"
                                        :class="form.warranty_months == 24 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg transition">24T</button>
                                <button type="button" @click="form.warranty_months = 36"
                                        :class="form.warranty_months == 36 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg transition">36T</button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Chu kỳ nhắc thay lõi (Tháng)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" x-model.number="form.filter_replacement_months" min="1" max="36"
                                   class="w-20 px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl font-bold text-center focus:bg-white focus:ring-2 focus:ring-indigo-500/30">
                            <div class="flex items-center gap-1">
                                <button type="button" @click="form.filter_replacement_months = 3"
                                        :class="form.filter_replacement_months == 3 ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg transition">3T</button>
                                <button type="button" @click="form.filter_replacement_months = 6"
                                        :class="form.filter_replacement_months == 6 ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg transition">6T</button>
                                <button type="button" @click="form.filter_replacement_months = 9"
                                        :class="form.filter_replacement_months == 9 ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg transition">9T</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Technical Specs & Technician -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 space-y-3.5">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">3</span>
                    <h3 class="text-sm font-bold text-slate-800">Chỉ số kỹ thuật & Kỹ thuật viên</h3>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">TDS Đầu vào</label>
                        <div class="relative">
                            <input type="number" x-model.number="form.tds_in" placeholder="ppm"
                                   class="w-full px-2.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl text-center focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1 text-emerald-700">TDS Tinh khiết</label>
                        <div class="relative">
                            <input type="number" x-model.number="form.tds_out" placeholder="ppm"
                                   class="w-full px-2.5 py-2 text-sm bg-emerald-50/50 border border-emerald-300 rounded-xl text-center text-emerald-800 font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Áp lực nước</label>
                        <div class="relative">
                            <input type="text" x-model="form.pressure" placeholder="kg/cm²"
                                   class="w-full px-2.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl text-center focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 font-medium">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kỹ thuật viên lắp đặt</label>
                        <select x-model="form.technician_id" @change="onTechnicianChange($event)"
                                class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 font-medium">
                            <option value="">-- Chọn Kỹ thuật viên --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" data-name="{{ $tech->name }}"
                                        {{ ($machine->technician_id == $tech->id || (Auth::check() && Auth::id() == $tech->id)) ? 'selected' : '' }}>
                                    {{ $tech->name }} {{ $tech->phone ? '(' . $tech->phone . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ghi chú lắp đặt & Dặn dò</label>
                        <input type="text" x-model="form.activation_notes" placeholder="Dặn dò KH xả nước đầu, v.v..."
                               class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 font-medium">
                    </div>
                </div>
            </div>

            <!-- Submit Button Area -->
            <div class="pt-2">
                <button type="submit" :disabled="submitting"
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-white shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2 transition-all transform active:scale-[0.99]"
                        :class="submitting ? 'bg-slate-400 cursor-not-allowed' : 'bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700'">
                    <template x-if="submitting">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                            Đang xử lý dữ liệu...
                        </span>
                    </template>
                    <template x-if="!submitting">
                        <span class="flex items-center gap-2 text-base">
                            <i class="fa-solid fa-paper-plane"></i>
                            @if(Auth::check() && (Auth::user()->hasPermission('water_filter.view') || Auth::user()->hasRole('admin')))
                                Kích Hoạt & Cấp Bảo Hành Ngay
                            @else
                                Gửi Yêu Cầu Kích Hoạt Máy
                            @endif
                        </span>
                    </template>
                </button>
            </div>
        </form>

    </main>

    <!-- Footer -->
    <footer class="mt-8 border-t border-slate-200/80 bg-white py-4 text-center text-xs text-slate-500">
        <p class="font-semibold text-slate-700">CÔNG TY CỔ PHẦN CÔNG NGHỆ & THƯƠNG MẠI ĐẠI SƠN</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Tổng đài hỗ trợ kỹ thuật: 1900 xxxx • Hotline: 0988 xxx xxx</p>
    </footer>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity.duration.300ms
         class="fixed bottom-5 left-1/2 transform -translate-x-1/2 z-50 max-w-sm w-full px-4"
         style="display: none;">
        <div class="p-3.5 rounded-2xl shadow-xl flex items-center gap-3 border text-sm font-semibold"
             :class="toast.type === 'success' ? 'bg-emerald-600 text-white border-emerald-500 shadow-emerald-500/20' : 'bg-rose-600 text-white border-rose-500 shadow-rose-500/20'">
            <i class="fa-solid" :class="toast.type === 'success' ? 'fa-circle-check text-lg' : 'fa-circle-exclamation text-lg'"></i>
            <span x-text="toast.message" class="leading-snug"></span>
        </div>
    </div>

    <script>
        function qrActivationApp() {
            return {
                submitting: false,
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                form: {
                    customer_name: @json($machine->customer_name ?? ''),
                    customer_phone: @json($machine->customer_phone ?? ''),
                    customer_address: @json($machine->customer_address ?? ''),
                    water_source_type: @json($machine->water_source_type ?? 'Nước máy thành phố'),
                    machine_model_id: @json($machine->machine_model_id ?? ''),
                    machine_model: @json($machine->machine_model ?? ''),
                    machine_serial: @json($machine->machine_serial ?? ''),
                    installed_at: @json($machine->installed_at ? date('Y-m-d\TH:i', strtotime($machine->installed_at)) : date('Y-m-d\TH:i')),
                    warranty_months: @json($machine->warranty_months ?: 24),
                    filter_replacement_months: @json($machine->filter_replacement_months ?: 6),
                    tds_in: @json($machine->tds_in ?? ''),
                    tds_out: @json($machine->tds_out ?? ''),
                    pressure: @json($machine->pressure ?? ''),
                    technician_id: @json($machine->technician_id ?? (Auth::check() ? Auth::id() : '')),
                    technician_name: @json($machine->technician_name ?? (Auth::check() ? Auth::user()->name : '')),
                    activation_notes: @json($machine->activation_notes ?? '')
                },

                init() {
                    // Render QR Code in thumbnail box
                    const container = document.getElementById('qrCodeContainer');
                    if (container) {
                        new QRCode(container, {
                            text: @json(url('/m/' . $machine->qr_token)),
                            width: 64,
                            height: 64,
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    }
                },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                },

                onModelChange(event) {
                    const sel = event.target;
                    const opt = sel.options[sel.selectedIndex];
                    if (opt && opt.value) {
                        this.form.machine_model = opt.dataset.name || opt.text;
                        if (opt.dataset.warranty) {
                            this.form.warranty_months = parseInt(opt.dataset.warranty);
                        }
                    }
                },

                onTechnicianChange(event) {
                    const sel = event.target;
                    const opt = sel.options[sel.selectedIndex];
                    if (opt && opt.value) {
                        this.form.technician_name = opt.dataset.name || '';
                    } else {
                        this.form.technician_name = '';
                    }
                },

                async submitActivation() {
                    if (this.submitting) return;
                    this.submitting = true;

                    try {
                        const response = await fetch(@json(url('/m/' . $machine->qr_token . '/activate')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.form)
                        });

                        const res = await response.json();
                        if (res.success) {
                            this.showToast(res.message, 'success');
                            setTimeout(() => {
                                window.location.href = res.redirect || @json(url('/m/' . $machine->qr_token));
                            }, 1200);
                        } else {
                            this.showToast(res.message || 'Có lỗi xảy ra khi gửi yêu cầu!', 'error');
                            this.submitting = false;
                        }
                    } catch (err) {
                        console.error(err);
                        this.showToast('Không thể kết nối đến máy chủ. Vui lòng thử lại!', 'error');
                        this.submitting = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
