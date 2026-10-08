<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $transaction->type === 'import' ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO' }} - {{ $transaction->code }} | Máy lọc nước Đại Sơn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            body { font-size: 12pt; background: #fff !important; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            @page { margin: 12mm 15mm; size: A4 portrait; }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-4">
    <!-- Action Bar (Không in) -->
    <div class="max-w-4xl mx-auto mb-4 flex items-center justify-between no-print">
        <a href="javascript:window.history.back()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-xl font-bold text-xs flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2.5 bg-[#1b4a8c] hover:bg-blue-800 text-white rounded-xl font-bold text-xs flex items-center gap-2 shadow-md cursor-pointer">
                <i class="fa-solid fa-print text-sm"></i> In Phiếu (A4/A5)
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-xl font-bold text-xs border border-rose-200">
                Đóng
            </button>
        </div>
    </div>

    <!-- Trang in tiêu chuẩn -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 print:shadow-none print:border-none print:p-0">
        <!-- Header: Tên công ty & Tiêu đề phiếu -->
        <div class="flex justify-between items-start border-b-2 border-slate-800 pb-4 mb-6">
            <div>
                <h2 class="font-black text-base uppercase tracking-wider text-slate-900">CÔNG TY TNHH MÁY LỌC NƯỚC ĐẠI SƠN</h2>
                <p class="text-xs text-slate-600 mt-1">Chuyên sản xuất & phân phối máy lọc nước RO, lõi lọc, linh phụ kiện lọc nước</p>
                <p class="text-xs text-slate-600">Địa chỉ: {{ $transaction->warehouse?->address ?? 'Hà Nội' }} | Hotline: 0988 123 456</p>
            </div>
            <div class="text-right">
                <div class="font-bold text-xs text-slate-500">Mẫu số: 01-VT</div>
                <div class="text-[11px] text-slate-500 italic">(Ban hành theo TT số 133/2016/TT-BTC)</div>
                <div class="mt-2 font-mono font-black text-sm text-[#1b4a8c]">{{ $transaction->code }}</div>
            </div>
        </div>

        <!-- Tên Phiếu & Ngày tháng -->
        <div class="text-center my-6">
            <h1 class="text-2xl font-black uppercase tracking-wider {{ $transaction->type === 'import' ? 'text-emerald-800' : 'text-amber-800' }}">
                {{ $transaction->type === 'import' ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO' }}
            </h1>
            <p class="text-xs italic text-slate-600 mt-1">
                Ngày {{ $transaction->created_at->format('d') }} tháng {{ $transaction->created_at->format('m') }} năm {{ $transaction->created_at->format('Y') }}
            </p>
            <p class="text-[11px] text-slate-500 font-mono mt-0.5">Số: <span class="font-bold text-slate-800">{{ $transaction->code }}</span></p>
        </div>

        <!-- Thông tin giao dịch -->
        <div class="grid grid-cols-2 gap-4 text-xs mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div class="space-y-1.5">
                <p>
                    <span class="font-bold text-slate-700">{{ $transaction->type === 'import' ? 'Họ tên người giao / NCC:' : 'Họ tên người nhận / Bộ phận:' }}</span>
                    <span class="font-semibold text-slate-900">{{ $transaction->supplier_name ?: 'Nội bộ công ty' }}</span>
                </p>
                <p>
                    <span class="font-bold text-slate-700">Theo chứng từ số:</span>
                    <span class="text-slate-900">{{ $transaction->reference_order_code ?: 'Không có' }}</span>
                </p>
                <p>
                    <span class="font-bold text-slate-700">Lý do {{ $transaction->type === 'import' ? 'nhập' : 'xuất' }}:</span>
                    <span class="text-slate-900">{{ $transaction->notes ?: ($transaction->type === 'import' ? 'Nhập kho hàng hóa / vật tư' : 'Xuất kho sử dụng / sản xuất') }}</span>
                </p>
            </div>
            <div class="space-y-1.5 text-right sm:text-left">
                <p>
                    <span class="font-bold text-slate-700">{{ $transaction->type === 'import' ? 'Nhập tại kho:' : 'Xuất tại kho:' }}</span>
                    <span class="font-bold text-[#1b4a8c]">{{ $transaction->warehouse?->name ?? 'Kho Tổng' }}</span>
                </p>
                <p>
                    <span class="font-bold text-slate-700">Địa điểm kho:</span>
                    <span class="text-slate-900">{{ $transaction->warehouse?->address ?? 'Hà Nội' }}</span>
                </p>
                <p>
                    <span class="font-bold text-slate-700">Trạng thái:</span>
                    <span class="px-2 py-0.5 rounded font-bold {{ $transaction->status === 'Đã hủy' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $transaction->status }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Bảng danh sách hàng hóa -->
        <table class="w-full text-xs border-collapse border border-slate-400 mb-6">
            <thead>
                <tr class="bg-slate-100 text-slate-800 font-bold text-center">
                    <th class="border border-slate-400 p-2 w-10">STT</th>
                    <th class="border border-slate-400 p-2 text-left">Tên, nhãn hiệu, quy cách hàng hóa</th>
                    <th class="border border-slate-400 p-2 w-24">Mã SKU</th>
                    <th class="border border-slate-400 p-2 w-16">ĐVT</th>
                    <th class="border border-slate-400 p-2 w-20">Số lượng</th>
                    <th class="border border-slate-400 p-2 w-28 text-right">Đơn giá (đ)</th>
                    <th class="border border-slate-400 p-2 w-32 text-right">Thành tiền (đ)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @foreach($transaction->items as $idx => $item)
                @php
                    $qty = (float)$item->quantity;
                    $price = (float)($item->import_price > 0 ? $item->import_price : $item->cost_price_at_transaction);
                    $subtotal = $qty * $price;
                    $totalQty += $qty;
                @endphp
                <tr>
                    <td class="border border-slate-400 p-2 text-center">{{ $idx + 1 }}</td>
                    <td class="border border-slate-400 p-2">
                        <div class="font-bold text-slate-900">{{ $item->product?->name ?? 'Sản phẩm đã xóa' }}</div>
                        @if($item->notes)
                            <div class="text-[10px] text-slate-500 italic">{{ $item->notes }}</div>
                        @endif
                    </td>
                    <td class="border border-slate-400 p-2 text-center font-mono font-bold">{{ $item->product?->sku ?? '-' }}</td>
                    <td class="border border-slate-400 p-2 text-center">{{ $item->product?->unit ?? 'cái' }}</td>
                    <td class="border border-slate-400 p-2 text-center font-bold font-mono">{{ number_format($qty, 2) }}</td>
                    <td class="border border-slate-400 p-2 text-right font-mono">{{ number_format($price, 0, ',', '.') }}</td>
                    <td class="border border-slate-400 p-2 text-right font-mono font-bold">{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50 font-bold">
                    <td colspan="4" class="border border-slate-400 p-2 text-center uppercase">Tổng cộng</td>
                    <td class="border border-slate-400 p-2 text-center font-mono font-black">{{ number_format($totalQty, 2) }}</td>
                    <td class="border border-slate-400 p-2"></td>
                    <td class="border border-slate-400 p-2 text-right font-mono font-black text-sm text-[#1b4a8c]">
                        {{ number_format((float)$transaction->total_amount, 0, ',', '.') }} đ
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Chữ ký 4 bên -->
        <div class="grid grid-cols-4 gap-2 text-center text-xs mt-10">
            <div>
                <div class="font-bold uppercase text-slate-800">Người lập phiếu</div>
                <div class="text-[11px] text-slate-500 italic mb-16">(Ký, họ tên)</div>
                <div class="font-bold text-slate-900">{{ $transaction->createdBy?->name ?? 'Người lập' }}</div>
            </div>
            <div>
                <div class="font-bold uppercase text-slate-800">{{ $transaction->type === 'import' ? 'Người giao hàng' : 'Người nhận hàng' }}</div>
                <div class="text-[11px] text-slate-500 italic mb-16">(Ký, họ tên)</div>
                <div class="font-bold text-slate-900">{{ $transaction->supplier_name ?: '...' }}</div>
            </div>
            <div>
                <div class="font-bold uppercase text-slate-800">Thủ kho</div>
                <div class="text-[11px] text-slate-500 italic mb-16">(Ký, họ tên)</div>
                <div class="font-bold text-slate-900">{{ $transaction->warehouse?->manager_name ?? 'Thủ kho' }}</div>
            </div>
            <div>
                <div class="font-bold uppercase text-slate-800">Kế toán / Giám đốc</div>
                <div class="text-[11px] text-slate-500 italic mb-16">(Ký, họ tên, đóng dấu)</div>
                <div class="font-bold text-slate-900">...</div>
            </div>
        </div>

        <!-- Footer in ấn -->
        <div class="mt-12 pt-4 border-t border-slate-200 text-center text-[10px] text-slate-400 italic">
            In lúc {{ now()->format('H:i:s d/m/Y') }} - Hệ thống Quản trị Doanh nghiệp Máy lọc nước Đại Sơn (ERP Daison)
        </div>
    </div>
</body>
</html>
