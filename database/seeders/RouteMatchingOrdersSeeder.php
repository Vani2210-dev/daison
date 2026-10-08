<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dealer;
use App\Models\User;
use App\Models\Product;
use App\Models\Vehicle;

class RouteMatchingOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $salesRep = User::where('department', 'like', '%Sales%')->orWhere('department', 'like', '%Kinh doanh%')->first() ?? $admin;
        $accountant = User::where('department', 'like', '%Account%')->orWhere('department', 'like', '%Kế toán%')->first() ?? $admin;
        $warehouseUser = User::where('department', 'like', '%Warehouse%')->orWhere('department', 'like', '%Kho%')->first() ?? $admin;
        
        $driverThanh = User::where('name', 'like', '%Thành%')->first() ?? $admin;
        $driverKien = User::where('name', 'like', '%Kiên%')->first() ?? $driverThanh;
        $driverDat = User::where('name', 'like', '%Đạt%')->first() ?? $driverThanh;

        $allProducts = Product::all();
        $getRandomProduct = function () use ($allProducts) {
            return $allProducts->isNotEmpty() ? $allProducts->random() : null;
        };

        // Danh sách 16 đơn hàng tương ứng sát theo 4 lộ tuyến vận tải
        $ordersData = [
            // ==========================================================
            // LỘ TUYẾN 01: Kho Mê Linh - Giải Phóng - Trường Chinh - Nguyễn Trãi (Trục Nam Hà Nội)
            // Xe 29C-888.99 (Đang giao hàng) - Tài xế: Bùi Văn Thành
            // Các điểm dừng: Thanh Xuân, Giải Phóng, Trường Chinh, Nguyễn Trãi, Hà Đông
            // ==========================================================
            [
                'order_code' => 'DH-2026-0101',
                'customer_name' => 'Đại Lý Điện Nước Minh Quang',
                'customer_phone' => '0912 888 123',
                'customer_address' => '158 Đường Giải Phóng, Phương Liệt, Thanh Xuân, Hà Nội',
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-888.99',
                'subtotal' => 15450000,
                'discount_amount' => 1820000,
                'tax_amount' => 1090400,
                'total_amount' => 14720400,
                'status' => 'Đang giao hàng',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 14720400,
                'notes' => '[Tuyến 01 - Điểm 1: Giải Phóng] Khách hẹn giao trước 10h sáng, hỗ trợ hạ ống vào kho tầng 1.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ống nhựa PVC-U Tiền Phong Ø21 x 1.6mm', 'unit' => 'mét', 'qty' => 500, 'price' => 14500],
                    ['name' => 'Co 90 độ PVC Tiền Phong Ø21', 'unit' => 'cái', 'qty' => 200, 'price' => 3200],
                    ['name' => 'Dây điện CADIVI VCm 2x2.5 mm2', 'unit' => 'cuộn', 'qty' => 5, 'price' => 1360000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0102',
                'customer_name' => 'Đại Lý Thiết Bị Điện Nước Hưng Thịnh',
                'customer_phone' => '0978 222 333',
                'customer_address' => '512 Đường Nguyễn Trãi, Thanh Xuân Trung, Thanh Xuân, Hà Nội',
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-888.99',
                'subtotal' => 29800000,
                'discount_amount' => 2800000,
                'tax_amount' => 1500000,
                'total_amount' => 28500000,
                'status' => 'Đang giao hàng',
                'payment_status' => 'Đã thu tiền mặt',
                'payment_method' => 'Tiền mặt',
                'driver_debt_amount' => 28500000,
                'notes' => '[Tuyến 01 - Điểm 2: Nguyễn Trãi] Đã bàn giao đủ 3 bó ống HDPE, tài xế thu tiền mặt tại quầy.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ống nhựa HDPE Tiền Phong Ø90 PN10', 'unit' => 'mét', 'qty' => 120, 'price' => 145000],
                    ['name' => 'Tê đều PVC Tiền Phong Ø90', 'unit' => 'cái', 'qty' => 30, 'price' => 68000],
                    ['name' => 'Van bi đồng tay gạt Miha Ø27', 'unit' => 'cái', 'qty' => 45, 'price' => 195000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0103',
                'customer_name' => 'Cửa Hàng Vật Tư Nước Tân Phát',
                'customer_phone' => '0934 555 666',
                'customer_address' => '182 Đường Trường Chinh, Khương Mai, Thanh Xuân, Hà Nội',
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-888.99',
                'subtotal' => 10500000,
                'discount_amount' => 1200000,
                'tax_amount' => 550000,
                'total_amount' => 9850000,
                'status' => 'Đang giao hàng',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 01 - Điểm 3: Trường Chinh] Giao kèm phụ kiện PPR nước nóng và keo dán chuyên dụng.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ống nước nóng PPR Tiền Phong Ø25 PN20', 'unit' => 'cây 4m', 'qty' => 40, 'price' => 165000],
                    ['name' => 'Co ren trong PPR Ø25 x 1/2"', 'unit' => 'cái', 'qty' => 60, 'price' => 38000],
                    ['name' => 'Băng keo tan Tombo 9082 Nhật Bản', 'unit' => 'cuộn', 'qty' => 100, 'price' => 9500],
                ]
            ],
            [
                'order_code' => 'DH-2026-0104',
                'customer_name' => 'Đại Lý Điện Nước & Kim Khí Hà Đông',
                'customer_phone' => '0989 777 888',
                'customer_address' => '45 Phố Quang Trung, P. Quang Trung, Hà Đông, Hà Nội',
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-888.99',
                'subtotal' => 19200000,
                'discount_amount' => 2000000,
                'tax_amount' => 1000000,
                'total_amount' => 18200000,
                'status' => 'Đang giao hàng',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 18200000,
                'notes' => '[Tuyến 01 - Điểm 4: Hà Đông] Điểm dừng cuối của Tuyến 01 trước khi xe vòng về kho tổng.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ống thoát nước PVC Tiền Phong C2 Ø110 x 3.2mm', 'unit' => 'cây 4m', 'qty' => 50, 'price' => 285000],
                    ['name' => 'Keo dán ống PVC Tiền Phong 500g', 'unit' => 'hộp', 'qty' => 20, 'price' => 95000],
                    ['name' => 'Lưới lọc rác Inox 304 chống mùi', 'unit' => 'cái', 'qty' => 35, 'price' => 60000],
                ]
            ],

            // ==========================================================
            // LỘ TUYẾN 02: Kho Đông Anh - Cầu Thăng Long - Mê Linh - Phúc Yên
            // Xe 29C-777.66 (Đang bốc hàng) - Tài xế: Bùi Văn Thành
            // Các điểm dừng: Đông Anh, KCN Thăng Long, Mê Linh, Phúc Yên
            // ==========================================================
            [
                'order_code' => 'DH-2026-0201',
                'customer_name' => 'Nhà Phân Phối Điện Nước Đông Anh',
                'customer_phone' => '0962 111 222',
                'customer_address' => 'Tổ 12 Thị Trấn Đông Anh, Huyện Đông Anh, Hà Nội',
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-777.66',
                'subtotal' => 36500000,
                'discount_amount' => 3500000,
                'tax_amount' => 1600000,
                'total_amount' => 34600000,
                'status' => 'Kho đã xuất đủ',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Ghi nợ đại lý',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 02 - Điểm 1: Đông Anh] Hàng đã xếp vào khoang sau xe 29C-777.66, chờ xuất bãi.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ống luồn dây điện chống cháy Sino SP9020 Ø20', 'unit' => 'cây 2.92m', 'qty' => 400, 'price' => 32000],
                    ['name' => 'Hộp nối chia ngả Sino 1 đường / 2 đường Ø20', 'unit' => 'cái', 'qty' => 300, 'price' => 8500],
                    ['name' => 'Ống PVC Tiền Phong C1 Ø110', 'unit' => 'cây 4m', 'qty' => 70, 'price' => 275000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0202',
                'customer_name' => 'Công Trình Cơ Điện KCN Thăng Long',
                'customer_phone' => '0915 667 889',
                'customer_address' => 'Lô G3, Đường N2, KCN Thăng Long, Kim Chung, Đông Anh, Hà Nội',
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-777.66',
                'subtotal' => 65000000,
                'discount_amount' => 5000000,
                'tax_amount' => 2400000,
                'total_amount' => 62400000,
                'status' => 'Kho đã xuất đủ',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 02 - Điểm 2: KCN Thăng Long] Đơn hàng dự án nhà xưởng công nghiệp. Xuất hóa đơn VAT.',
                'order_type' => 'project',
                'items' => [
                    ['name' => 'Ống xoắn HDPE luồn cáp ngầm Ba An Ø65/50', 'unit' => 'cuộn 200m', 'qty' => 6, 'price' => 7800000],
                    ['name' => 'Măng sông nối ống xoắn HDPE Ø65/50', 'unit' => 'bộ', 'qty' => 40, 'price' => 85000],
                    ['name' => 'Băng cảnh báo cáp điện ngầm 15cm', 'unit' => 'cuộn 500m', 'qty' => 5, 'price' => 2440000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0203',
                'customer_name' => 'Cửa Hàng Điện Nước Mê Linh Plaza',
                'customer_phone' => '0944 333 888',
                'customer_address' => 'Khu Đô Thị Tiền Phong, Huyện Mê Linh, Hà Nội',
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-777.66',
                'subtotal' => 17500000,
                'discount_amount' => 1500000,
                'tax_amount' => 800000,
                'total_amount' => 16800000,
                'status' => 'Kho đang soạn',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 16800000,
                'notes' => '[Tuyến 02 - Điểm 3: Mê Linh] Kho chi nhánh đang xếp kiện thiết bị đóng cắt.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Aptomat 2P 32A 6kA Panasonic BKH-D0232', 'unit' => 'cái', 'qty' => 50, 'price' => 195000],
                    ['name' => 'Aptomat chống giật RCBO 30mA Panasonic', 'unit' => 'cái', 'qty' => 20, 'price' => 310000],
                    ['name' => 'Tủ điện âm tường 4-8 module Sino', 'unit' => 'chiếc', 'qty' => 15, 'price' => 55000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0204',
                'customer_name' => 'Đại Lý Vật Tư Nước Phúc Yên',
                'customer_phone' => '0903 445 566',
                'customer_address' => 'Số 26 Đường Hai Bà Trưng, Tiền Châu, TP. Phúc Yên, Vĩnh Phúc',
                'assigned_driver_id' => $driverThanh->id,
                'assigned_vehicle_plate' => '29C-777.66',
                'subtotal' => 43500000,
                'discount_amount' => 4000000,
                'tax_amount' => 1700000,
                'total_amount' => 41200000,
                'status' => 'Kho đã xuất đủ',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Ghi nợ đại lý',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 02 - Điểm 4: Phúc Yên] Điểm cuối tuyến Vĩnh Phúc, đại lý ký nhận biên bản giao nhận.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ống PVC Bình Minh C2 Ø90 x 3.0mm', 'unit' => 'cây 4m', 'qty' => 100, 'price' => 245000],
                    ['name' => 'Đồng hồ đo lưu lượng nước lạnh Asahi GMK15', 'unit' => 'chiếc', 'qty' => 25, 'price' => 580000],
                    ['name' => 'Van bướm tay gạt gang cánh Inox DN80', 'unit' => 'chiếc', 'qty' => 4, 'price' => 550000],
                ]
            ],

            // ==========================================================
            // LỘ TUYẾN 03: Cầu Giấy - Hoàng Quốc Việt - Bắc Từ Liêm - Nhổn - Hoài Đức (Trục Tây Hà Nội)
            // Xe 29H-555.44 (Trên đường về) - Tài xế: Trần Văn Kiên
            // Các điểm dừng: Cầu Giấy, Hoàng Quốc Việt, Bắc Từ Liêm, Nhổn, Hoài Đức
            // ==========================================================
            [
                'order_code' => 'DH-2026-0301',
                'customer_name' => 'Đại Lý Điện Dân Dụng Cầu Giấy',
                'customer_phone' => '0973 888 999',
                'customer_address' => '136 Đường Xuân Thủy, Dịch Vọng Hậu, Cầu Giấy, Hà Nội',
                'assigned_driver_id' => $driverKien->id,
                'assigned_vehicle_plate' => '29H-555.44',
                'subtotal' => 13200000,
                'discount_amount' => 1500000,
                'tax_amount' => 600000,
                'total_amount' => 12300000,
                'status' => 'Giao thành công',
                'payment_status' => 'Đã thanh toán QR',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 03 - Điểm 1: Cầu Giấy] Đã hoàn tất giao lúc 09:30, khách quét VietQR thanh toán đủ.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ổ cắm đôi 3 chấu có màn che Panasonic Wide', 'unit' => 'cái', 'qty' => 120, 'price' => 55000],
                    ['name' => 'Công tắc đơn 1 chiều Panasonic Wide', 'unit' => 'cái', 'qty' => 150, 'price' => 26000],
                    ['name' => 'Dây đôi mềm dẹt CADIVI VCmd 2x1.5 mm2', 'unit' => 'cuộn', 'qty' => 4, 'price' => 450000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0302',
                'customer_name' => 'Công Ty CP Cơ Điện Hoàng Quốc Việt',
                'customer_phone' => '0983 999 444',
                'customer_address' => '215 Đường Hoàng Quốc Việt, Nghĩa Tân, Cầu Giấy, Hà Nội',
                'assigned_driver_id' => $driverKien->id,
                'assigned_vehicle_plate' => '29H-555.44',
                'subtotal' => 23500000,
                'discount_amount' => 2500000,
                'tax_amount' => 900000,
                'total_amount' => 21900000,
                'status' => 'Giao thành công',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Ghi nợ đại lý',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 03 - Điểm 2: Hoàng Quốc Việt] Giao lúc 10:45, ký biên bản nghiệm thu vật tư công trình.',
                'order_type' => 'project',
                'items' => [
                    ['name' => 'Ống luồn chịu lực PVC EuroPipe Ø25', 'unit' => 'cây 2.92m', 'qty' => 250, 'price' => 42000],
                    ['name' => 'Hộp chia ngả 4 đường kèm nắp đậy', 'unit' => 'cái', 'qty' => 150, 'price' => 14000],
                    ['name' => 'Măng sông nối trơn PVC Ø25', 'unit' => 'cái', 'qty' => 500, 'price' => 2500],
                ]
            ],
            [
                'order_code' => 'DH-2026-0303',
                'customer_name' => 'Cửa Hàng Vật Tư Điện Nước Diễn Nhổn',
                'customer_phone' => '0918 333 444',
                'customer_address' => '68 Đường Cầu Diễn, P. Phúc Diễn, Bắc Từ Liêm, Hà Nội',
                'assigned_driver_id' => $driverKien->id,
                'assigned_vehicle_plate' => '29H-555.44',
                'subtotal' => 16500000,
                'discount_amount' => 1800000,
                'tax_amount' => 700000,
                'total_amount' => 15400000,
                'status' => 'Giao thành công',
                'payment_status' => 'Đã thu tiền mặt',
                'payment_method' => 'Tiền mặt',
                'driver_debt_amount' => 15400000,
                'notes' => '[Tuyến 03 - Điểm 3: Nhổn] Đã thu 15.4 triệu tiền mặt, tài xế đang giữ trên đường về nộp quỹ.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ống hàn nhiệt PPR Đệ Nhất Ø20 x 2.3mm', 'unit' => 'cây 4m', 'qty' => 60, 'price' => 88000],
                    ['name' => 'Co cút vuông 90 độ PPR Đệ Nhất Ø20', 'unit' => 'cái', 'qty' => 120, 'price' => 9500],
                    ['name' => 'Máy bơm nước tăng áp điện tử Panasonic A-130JAK', 'unit' => 'chiếc', 'qty' => 5, 'price' => 1790000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0304',
                'customer_name' => 'Công Trình Biệt Thự Hinode Royal Park',
                'customer_phone' => '0966 777 999',
                'customer_address' => 'Khu Đô Thị Kim Chung Di Trạch, Quốc Lộ 32, Hoài Đức, Hà Nội',
                'assigned_driver_id' => $driverKien->id,
                'assigned_vehicle_plate' => '29H-555.44',
                'subtotal' => 41000000,
                'discount_amount' => 3800000,
                'tax_amount' => 1500000,
                'total_amount' => 38700000,
                'status' => 'Giao thành công',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Chuyển khoản',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 03 - Điểm 4: Hoài Đức] Điểm cuối tuyến Tây Hà Nội, đã hạ hàng và xe đang trên đường về kho.',
                'order_type' => 'project',
                'items' => [
                    ['name' => 'Ống thoát sàn PVC D110 Tiền Phong C2', 'unit' => 'cây 4m', 'qty' => 60, 'price' => 295000],
                    ['name' => 'Ống cấp nước nóng PPR Tiền Phong D32 PN20', 'unit' => 'cây 4m', 'qty' => 45, 'price' => 240000],
                    ['name' => 'Ga thoát sàn chống mùi Inox SUS304 cao cấp', 'unit' => 'bộ', 'qty' => 30, 'price' => 340000],
                ]
            ],

            // ==========================================================
            // LỘ TUYẾN 04: Nội đô Hà Nội (Hoàn Kiếm - Ba Đình - Đống Đa - Hai Bà Trưng)
            // Xe 29H-333.22 (Rảnh - Sẵn sàng nhận chuyến) - Tài xế: Nguyễn Tiến Đạt
            // Các điểm dừng: Ba Đình, Đống Đa, Hoàn Kiếm, Hai Bà Trưng
            // ==========================================================
            [
                'order_code' => 'DH-2026-0401',
                'customer_name' => 'Showroom Thiết Bị Vệ Sinh Phố Huế',
                'customer_phone' => '0904 888 777',
                'customer_address' => '88 Phố Huế, P. Hàng Bài, Hoàn Kiếm, Hà Nội',
                'assigned_driver_id' => $driverDat->id,
                'assigned_vehicle_plate' => '29H-333.22',
                'subtotal' => 9200000,
                'discount_amount' => 900000,
                'tax_amount' => 300000,
                'total_amount' => 8600000,
                'status' => 'Chốt đơn',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 04 - Điểm 1: Hoàn Kiếm] Đơn hàng đã chốt, chờ bốc xếp lên xe 29H-333.22.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Vòi sen tắm đứng nóng lạnh Inox SUS304', 'unit' => 'bộ', 'qty' => 4, 'price' => 1450000],
                    ['name' => 'Dây cấp nước mềm Inox chống xoắn 40cm', 'unit' => 'sợi', 'qty' => 30, 'price' => 45000],
                    ['name' => 'Bộ xi phông chậu rửa mặt chống tràn', 'unit' => 'bộ', 'qty' => 15, 'price' => 95000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0402',
                'customer_name' => 'Đại Lý Điện Nước Đống Đa',
                'customer_phone' => '0913 222 111',
                'customer_address' => '142 Tôn Đức Thắng, Hàng Bột, Đống Đa, Hà Nội',
                'assigned_driver_id' => $driverDat->id,
                'assigned_vehicle_plate' => '29H-333.22',
                'subtotal' => 6900000,
                'discount_amount' => 700000,
                'tax_amount' => 250000,
                'total_amount' => 6450000,
                'status' => 'Chờ kế toán duyệt',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Ghi nợ đại lý',
                'driver_debt_amount' => 0,
                'notes' => '[Tuyến 04 - Điểm 2: Đống Đa] Đơn hàng giao bán lẻ phụ kiện bóng đèn & dây luồn dẻo.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Bóng đèn LED Bulb Rạng Đông 40W E27', 'unit' => 'bóng', 'qty' => 30, 'price' => 85000],
                    ['name' => 'Ống luồn đàn hồi ruột gà Sino SP9016 Ø16', 'unit' => 'cuộn 50m', 'qty' => 6, 'price' => 380000],
                    ['name' => 'Băng dính cách điện nano chống cháy', 'unit' => 'cây 10 cuộn', 'qty' => 10, 'price' => 92000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0403',
                'customer_name' => 'Cửa Hàng Kim Khí & Điện Nước Ba Đình',
                'customer_phone' => '0936 123 456',
                'customer_address' => '35 Đội Cấn, Liễu Giai, Ba Đình, Hà Nội',
                'assigned_driver_id' => $driverDat->id,
                'assigned_vehicle_plate' => '29H-333.22',
                'subtotal' => 8400000,
                'discount_amount' => 900000,
                'tax_amount' => 300000,
                'total_amount' => 7800000,
                'status' => 'Đã chuyển Kho',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Tiền mặt',
                'driver_debt_amount' => 7800000,
                'notes' => '[Tuyến 04 - Điểm 3: Ba Đình] Lưu ý tuyến đường cấm đỗ giờ cao điểm 16h30-19h.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Van phao cơ ngắt nước thông minh thế hệ mới Ø27', 'unit' => 'cái', 'qty' => 20, 'price' => 145000],
                    ['name' => 'Kép ren đồng thau Minh Hòa Ø21', 'unit' => 'cái', 'qty' => 80, 'price' => 22000],
                    ['name' => 'Cút ren trong đồng thau Minh Hòa Ø21', 'unit' => 'cái', 'qty' => 60, 'price' => 28000],
                ]
            ],
            [
                'order_code' => 'DH-2026-0404',
                'customer_name' => 'Đại Lý Điện Nước Bách Khoa - Hai Bà Trưng',
                'customer_phone' => '0972 555 444',
                'customer_address' => '75 Phố Lê Thanh Nghị, Bách Khoa, Hai Bà Trưng, Hà Nội',
                'assigned_driver_id' => $driverDat->id,
                'assigned_vehicle_plate' => '29H-333.22',
                'subtotal' => 12000000,
                'discount_amount' => 1200000,
                'tax_amount' => 400000,
                'total_amount' => 11200000,
                'status' => 'Kho đang soạn',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'VietQR',
                'driver_debt_amount' => 11200000,
                'notes' => '[Tuyến 04 - Điểm 4: Hai Bà Trưng] Chuẩn bị sẵn để xuất xe vào ca sáng mai.',
                'order_type' => 'normal',
                'items' => [
                    ['name' => 'Ống nhựa PVC Bình Minh C1 Ø34 x 1.5mm', 'unit' => 'cây 4m', 'qty' => 60, 'price' => 78000],
                    ['name' => 'Dây điện đơn ruột mềm CADISUN VAF 1x4.0 mm2', 'unit' => 'cuộn 100m', 'qty' => 3, 'price' => 1450000],
                    ['name' => 'Hộp át nổi chống cháy 1-2 cực Sino', 'unit' => 'cái', 'qty' => 40, 'price' => 25000],
                ]
            ],
        ];

        foreach ($ordersData as $oData) {
            $itemsData = $oData['items'] ?? [];
            unset($oData['items']);

            $dealer = Dealer::where('name', $oData['customer_name'])->first();
            if ($dealer) {
                $oData['dealer_id'] = $dealer->id;
            }

            $oData['sales_rep_id'] = $salesRep->id;
            $oData['sales_accountant_id'] = $accountant->id;
            $oData['warehouse_staff_id'] = $warehouseUser->id;
            $oData['creator_id'] = $salesRep->id;
            $oData['order_creator'] = $salesRep->name;

            $order = Order::updateOrCreate(
                ['order_code' => $oData['order_code']],
                $oData
            );

            // Xóa items cũ và tạo items mới
            $order->items()->delete();

            foreach ($itemsData as $it) {
                $matchedProduct = Product::where('name', 'like', '%' . substr($it['name'], 0, 10) . '%')->first() ?? $getRandomProduct();
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $matchedProduct ? $matchedProduct->id : 1,
                    'sku' => $matchedProduct ? $matchedProduct->sku : 'SP-PVC-' . rand(100, 999),
                    'name' => $it['name'],
                    'unit' => $it['unit'],
                    'quantity' => $it['qty'],
                    'cost_price' => $it['price'] * 0.8,
                    'unit_price' => $it['price'],
                    'original_price' => $it['price'] * 1.15,
                    'applied_tier_policy' => 'Chính sách Đại Lý Lộ Tuyến',
                    'amount' => $it['qty'] * $it['price'],
                ]);
            }
        }

        echo "Successfully seeded " . count($ordersData) . " route-matching orders into database!\n";
    }
}
