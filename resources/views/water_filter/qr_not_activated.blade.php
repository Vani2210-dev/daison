<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sản Phẩm Chưa Kích Hoạt - Đại Sơn Water</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Be Vietnam Pro"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="min-h-full font-sans text-slate-800 antialiased bg-slate-100 flex flex-col justify-between">

    <!-- Header Gradient Banner -->
    <header class="bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 text-white shadow-lg sticky top-0 z-30">
        <div class="max-w-xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-white shadow-inner">
                    <i class="fa-solid fa-faucet-drip text-xl text-amber-200"></i>
                </div>
                <div>
                    <h1 class="text-sm font-black tracking-tight leading-tight uppercase flex items-center gap-1.5">
                        ĐẠI SƠN WATER
                        <span class="inline-block px-1.5 py-0.5 text-[9px] font-extrabold bg-amber-300 text-amber-950 rounded tracking-normal">VERIFIED</span>
                    </h1>
                    <p class="text-xs text-amber-100 font-medium">Hệ thống Bảo hành Điện tử Chính hãng</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="text-xs bg-white/20 hover:bg-white/30 text-white font-semibold px-2.5 py-1 rounded-lg transition border border-white/30 flex items-center gap-1">
                    <i class="fa-solid fa-user-gear text-amber-200"></i>
                    <span>Đăng nhập KTV</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-xl mx-auto w-full px-4 py-5 space-y-4 flex-grow">

        <!-- Not Activated Notice Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-amber-200/80 overflow-hidden text-center p-6 space-y-4">
            
            <!-- Warning Badge & Icon -->
            <div class="relative mx-auto w-20 h-20 rounded-full bg-gradient-to-b from-amber-100 to-orange-100 border-2 border-amber-300 flex items-center justify-center shadow-inner">
                @if($machine->status === 'pending_activation')
                    <i class="fa-solid fa-clock-rotate-left text-3xl text-amber-600"></i>
                @else
                    <i class="fa-solid fa-shield-xmark text-3xl text-amber-600"></i>
                @endif
                <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-500 border-2 border-white flex items-center justify-center text-white text-[10px]">
                    <i class="fa-solid fa-exclamation"></i>
                </span>
            </div>

            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $machine->status === 'pending_activation' ? 'bg-orange-100 text-orange-800 border border-orange-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                    <span class="w-2 h-2 rounded-full {{ $machine->status === 'pending_activation' ? 'bg-orange-500' : 'bg-amber-500' }} animate-pulse"></span>
                    {{ $machine->status === 'pending_activation' ? 'Đang chờ duyệt kích hoạt' : 'Chưa được kích hoạt' }}
                </span>

                <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-2.5 tracking-tight">
                    Sản Phẩm Chưa Được Kích Hoạt
                </h2>

                <p class="text-xs text-slate-600 mt-2 leading-relaxed max-w-md mx-auto">
                    @if($machine->status === 'pending_activation')
                        Thiết bị này đã được gửi thông tin kích hoạt và đang trong quá trình <strong>chờ kiểm duyệt</strong> từ hệ thống Đại Sơn Water. Quý khách vui lòng chờ kỹ thuật viên và tổng đài xác nhận kích hoạt phiếu bảo hành.
                    @else
                        Mã tem QR này hiện <strong>chưa được kích hoạt bảo hành điện tử</strong> trên hệ thống chính hãng. Quý khách vui lòng liên hệ kỹ thuật viên lắp đặt hoặc tổng đài để được kích hoạt bảo hành.
                    @endif
                </p>
            </div>

            <!-- Machine Information Box -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200/90 p-4 text-left text-xs space-y-2.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 font-mono">
                    <span class="text-slate-500">Mã tem QR:</span>
                    <strong class="text-slate-800 font-bold">{{ $machine->qr_code }}</strong>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                    <span class="text-slate-500">Mã thiết bị:</span>
                    <strong class="text-slate-800 font-mono">{{ $machine->machine_code }}</strong>
                </div>

                <div class="flex items-start justify-between pb-2 border-b border-slate-200/60">
                    <span class="text-slate-500 shrink-0 mr-2">Model máy lọc:</span>
                    <strong class="text-slate-800 text-right">{{ $machine->machine_model ?: 'Máy lọc nước Đại Sơn' }}</strong>
                </div>

                @if(!empty($machine->accessories))
                <div class="flex items-start justify-between pt-1">
                    <span class="text-slate-500 shrink-0 mr-2 flex items-center gap-1 font-semibold text-emerald-700">
                        <i class="fa-solid fa-boxes-packing text-emerald-600"></i>
                        Phụ kiện kèm theo:
                    </span>
                    @if(count($machine->accessories_list) > 0)
                        <div class="text-right space-y-1">
                            @foreach($machine->accessories_list as $acc)
                                <div class="text-slate-800 text-xs font-medium">
                                    {{ is_array($acc) ? ($acc['name'] ?? '') : $acc }}
                                    @if(is_array($acc) && !empty($acc['quantity']))
                                        <span class="font-bold text-teal-700 font-mono">(x{{ $acc['quantity'] }}{{ !empty($acc['unit']) ? ' ' . $acc['unit'] : '' }})</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-slate-800 text-right font-medium leading-relaxed">{{ $machine->accessories }}</span>
                    @endif
                </div>
                @endif
            </div>

            <!-- Action Buttons for Customer -->
            <div class="space-y-2.5 pt-1">
                <a href="tel:0988000000"
                   class="w-full py-3.5 px-4 rounded-xl font-bold text-white shadow-lg shadow-amber-500/25 bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 hover:from-amber-700 hover:to-orange-700 flex items-center justify-center gap-2 transition active:scale-[0.99] text-sm">
                    <i class="fa-solid fa-phone-volume text-amber-200"></i>
                    Gọi Tổng Đài Hỗ Trợ Kích Hoạt
                </a>

                <div class="p-3 bg-blue-50/80 rounded-xl border border-blue-200/70 text-left text-xs text-blue-900 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 text-sm shrink-0"></i>
                    <div class="leading-relaxed">
                        <strong>Quyền lợi khi kích hoạt:</strong> Khách hàng sẽ được cấp Passport Thiết Bị, theo dõi thời hạn bảo hành điện tử chính hãng, nhắc lịch thay lõi lọc định kỳ và hỗ trợ kỹ thuật tận nơi 24/7.
                    </div>
                </div>
            </div>
        </div>

        <!-- Section for Technicians / Staff -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center text-xs space-y-2">
            <p class="font-bold text-slate-700 flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-user-shield text-sky-600"></i>
                Bạn là Kỹ thuật viên / Nhân viên Đại Sơn?
            </p>
            <p class="text-slate-500 text-[11px]">
                Đăng nhập tài khoản nội bộ để mở biểu mẫu kích hoạt tem QR này cho khách hàng.
            </p>
            <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-sky-50 text-sky-700 border border-slate-200 font-bold transition">
                <i class="fa-solid fa-right-to-bracket text-xs"></i>
                <span>Đăng nhập hệ thống để kích hoạt máy</span>
            </a>
        </div>

    </main>

    <!-- Footer -->
    <footer class="mt-8 border-t border-slate-200 bg-white py-4 text-center text-xs text-slate-500">
        <p class="font-bold text-slate-700">CÔNG TY CỔ PHẦN CÔNG NGHỆ & THƯƠNG MẠI ĐẠI SƠN</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Hotline CSKH: 0988.xxx.xxx • Bảo hành chính hãng toàn quốc</p>
    </footer>

</body>
</html>
