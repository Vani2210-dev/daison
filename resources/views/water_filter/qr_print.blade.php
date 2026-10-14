<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Tem Dán Mã QR Máy Lọc Nước - Đại Sơn Water</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-break {
                page-break-after: always;
            }
            .sticker-card {
                break-inside: avoid;
                border: 1.5px solid #0f172a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-6 min-h-screen">

    <!-- Print Control Bar (Hidden when printing) -->
    <div class="max-w-5xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 no-print flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-qrcode text-blue-600"></i>
                Bản In Tem Dán Mã QR Máy Lọc Nước
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Tổng cộng <strong>{{ count($machines) }}</strong> tem dán đã chọn. Tem sẵn sàng dán lên thân máy lọc hoặc phiếu bảo hành.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ url('/bao-hanh-may-loc') }}"
               class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
            </a>
            <button onclick="window.print()"
                    class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> In Ngay (Ctrl + P)
            </button>
        </div>
    </div>

    <!-- Sticker Grid (Standard Sticker Sheet 2 or 3 per row) -->
    <div class="max-w-5xl mx-auto grid grid-cols-2 sm:grid-cols-3 gap-4" id="stickerSheet">
        @foreach($machines as $m)
        <div class="sticker-card bg-white border-2 border-slate-800 rounded-xl p-3 flex flex-col justify-between relative overflow-hidden shadow-xs"
             style="min-height: 220px;">
            <!-- Top Logo & Brand -->
            <div class="border-b-2 border-slate-800 pb-1.5 flex items-center justify-between">
                <div>
                    <div class="text-[12px] font-black tracking-tight text-blue-900 flex items-center gap-1">
                        <i class="fa-solid fa-shield-halved text-cyan-600"></i>
                        ĐẠI SƠN WATER
                    </div>
                    <div class="text-[9px] font-bold text-slate-600 uppercase tracking-wider">
                        TEM BẢO HÀNH ĐIỆN TỬ
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-1.5 py-0.5 text-[9px] font-mono font-extrabold bg-slate-900 text-white rounded">
                        {{ $m->qr_code }}
                    </span>
                </div>
            </div>

            <!-- Middle: QR Code & Machine Specs -->
            <div class="py-2.5 flex items-center justify-around gap-2">
                <div class="qr-container bg-white p-1 border border-slate-300 rounded-lg shadow-2xs"
                     data-url="{{ url('/m/' . $m->qr_token) }}"
                     id="qr-{{ $m->id }}"></div>

                <div class="flex-1 space-y-1 text-left">
                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Mã Thiết Bị</div>
                    <div class="text-xs font-mono font-black text-slate-800 leading-none">{{ $m->machine_code }}</div>

                    <div class="text-[10px] text-slate-500 font-semibold uppercase pt-1">Model Máy</div>
                    <div class="text-[11px] font-bold text-blue-800 line-clamp-1 leading-tight">
                        {{ $m->machine_model ?: 'Đại Sơn RO System' }}
                    </div>

                    @if($m->machine_serial)
                        <div class="text-[9px] text-slate-600 font-mono">SN: {{ $m->machine_serial }}</div>
                    @endif

                    <div class="pt-1">
                        <span class="inline-block text-[9px] font-bold px-1.5 py-0.5 rounded {{ $m->status === 'activated' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $m->status === 'activated' ? 'Bảo hành ' . ($m->warranty_months ?: 24) . 'T' : 'Tem trắng xuất xưởng' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bottom: Scanning Instruction & Hotline -->
            <div class="border-t border-slate-300 pt-1.5 flex items-center justify-between text-[9px] text-slate-600">
                <div class="font-medium flex items-center gap-1">
                    <i class="fa-solid fa-camera text-slate-400"></i>
                    Quét camera để kích hoạt / tra cứu
                </div>
                <div class="font-bold text-slate-800">
                    Hotline: 1900.xxxx
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.qr-container').forEach(function (el) {
                const url = el.getAttribute('data-url');
                new QRCode(el, {
                    text: url,
                    width: 84,
                    height: 84,
                    correctLevel: QRCode.CorrectLevel.M
                });
            });
        });
    </script>
</body>
</html>
