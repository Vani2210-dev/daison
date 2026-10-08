<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryDispatch;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Order;

class DeliveryDispatchSeeder extends Seeder
{
    public function run(): void
    {
        $v1 = Vehicle::where('plate_number', '29C-888.99')->first() ?? Vehicle::first();
        $v2 = Vehicle::where('plate_number', '29C-777.66')->first() ?? Vehicle::skip(1)->first() ?? $v1;
        $d1 = User::where('id', 8)->first() ?? User::where('role', 'Driver')->first() ?? User::first();
        $d2 = User::where('id', 7)->first() ?? User::skip(1)->first() ?? $d1;

        $trip1Stops = [
            [
                'id' => 'stp-101',
                'orderId' => '1',
                'orderCode' => 'DH-2026-0001',
                'customerName' => 'Đại Lý Điện Nước Minh Quang',
                'customerPhone' => '0912 888 123',
                'deliveryAddress' => '158 Đường Giải Phóng, Phương Liệt, Thanh Xuân, Hà Nội',
                'destinationZone' => 'Khu vực Giải Phóng',
                'sequence' => 1,
                'estimatedArrival' => '09:15',
                'actualArrival' => '09:20',
                'itemsSummary' => 'Ống PVC Bình Minh Ø21 (500m), Co 90 độ (200 cái)',
                'totalWeightKg' => 480,
                'orderAmount' => 14720400,
                'amountToCollect' => 14720400,
                'collectedAmount' => 0,
                'status' => 'Đang giao',
                'notes' => 'Hạ hàng tại cửa hàng phía trước, đại lý hẹn kiểm đếm xong trả tiền mặt',
            ],
            [
                'id' => 'stp-102',
                'orderId' => '3',
                'orderCode' => 'DH-2026-089',
                'customerName' => 'Đại Lý Điện Nước Hoàn Mỹ',
                'customerPhone' => '0912 345 678',
                'deliveryAddress' => 'Kho Tổng Mê Linh (MQ)',
                'destinationZone' => 'Khu vực Mê Linh',
                'sequence' => 2,
                'estimatedArrival' => '10:30',
                'actualArrival' => '10:25',
                'itemsSummary' => 'Ống PVC Tiền Phong Ø110, Co 90 độ, Dây điện Cadivi',
                'totalWeightKg' => 350,
                'orderAmount' => 6917400,
                'amountToCollect' => 6917400,
                'collectedAmount' => 6917400,
                'collectedMethod' => 'VietQR',
                'status' => 'Giao thành công',
                'notes' => 'Chủ đại lý quét VietQR MBBank thành công tại chỗ, tiền về TK công ty',
            ]
        ];

        $trip1Details = [
            'id' => '1',
            'tripCode' => 'CX-2026-081',
            'routeName' => 'Tuyến 01: Kho Mê Linh - Giải Phóng - Nguyễn Trãi - Hà Đông',
            'departureWarehouseId' => 'wh-001',
            'departureWarehouseName' => 'Kho Tổng Mê Linh (MQ)',
            'vehicleId' => (string) ($v1->id ?? 1),
            'vehiclePlate' => $v1->plate_number ?? '29C-888.99',
            'vehicleType' => $v1->vehicle_type ?? 'Xe Tải Isuzu 2.5T',
            'vehicleMaxCapacityKg' => (float) ($v1->capacity_kg ?? 2500),
            'mainDriverId' => (string) ($d1->id ?? 8),
            'mainDriverName' => $d1->name ?? 'Bùi Văn Thành',
            'mainDriverPhone' => $d1->phone ?? '0966 334 455',
            'coDriverId' => (string) ($d2->id ?? 7),
            'coDriverName' => ($d2->name ?? 'Hoàng Minh Đức') . ' (Phụ xe)',
            'coDriverPhone' => $d2->phone ?? '0978 222 333',
            'dispatchDate' => '2026-09-07',
            'departureTime' => '08:30',
            'estimatedReturnTime' => '13:00',
            'estimatedDistanceKm' => 38,
            'estimatedFuelLiters' => 6.5,
            'tollFeesEstimated' => 35000,
            'stops' => $trip1Stops,
            'totalWeightKg' => 830,
            'totalOrderCount' => 2,
            'totalGoodsValue' => 21637800,
            'initialDriverDebt' => 21637800,
            'collectedByCash' => 0,
            'collectedByQR' => 6917400,
            'uncollectedAmount' => 14720400,
            'settledAmountWithCashier' => 0,
            'remainingDriverDebt' => 14720400,
            'status' => 'Đang giao hàng',
            'createdAt' => '2026-09-07 08:00:00',
            'updatedAt' => '2026-09-07 10:30:00',
            'notes' => 'Chở kèm ống dài 4m cẩn thận buộc chặt đuôi xe'
        ];

        DeliveryDispatch::updateOrCreate(
            ['code' => 'CX-2026-081'],
            [
                'vehicle_id' => $v1->id ?? 1,
                'driver_id' => $d1->id ?? 8,
                'status' => 'Đang giao hàng',
                'departure_time' => '2026-09-07 08:30:00',
                'total_weight_kg' => 830,
                'total_distance_km' => 38,
                'route_summary' => 'Tuyến 01: Kho Mê Linh - Giải Phóng - Nguyễn Trãi - Hà Đông',
                'notes' => 'Chở kèm ống dài 4m cẩn thận buộc chặt đuôi xe',
                'details' => $trip1Details,
            ]
        );

        $trip2Stops = [
            [
                'id' => 'stp-201',
                'orderId' => '4',
                'orderCode' => 'DH-2026-092',
                'customerName' => 'Công Ty Xây Lắp & Cơ Điện Thăng Long',
                'customerPhone' => '0977 654 321',
                'deliveryAddress' => 'Khu Công Nghiệp Thăng Long, Đông Anh, Hà Nội',
                'destinationZone' => 'Khu vực Đông Anh',
                'sequence' => 1,
                'estimatedArrival' => '09:45',
                'actualArrival' => '09:40',
                'itemsSummary' => 'Ống HDPE Tiền Phong Ø63 (200m), Van cổng đồng Minh Hòa',
                'totalWeightKg' => 650,
                'orderAmount' => 19700000,
                'amountToCollect' => 19700000,
                'collectedAmount' => 19700000,
                'collectedMethod' => 'Tiền mặt',
                'status' => 'Giao thành công',
                'notes' => 'Kế toán công trình đã thanh toán đủ tiền mặt cho tài xế Bùi Văn Thành',
            ]
        ];

        $trip2Details = [
            'id' => '2',
            'tripCode' => 'CX-2026-082',
            'routeName' => 'Tuyến 02: Kho Đông Anh - Cầu Thăng Long - Mê Linh - Phúc Yên',
            'departureWarehouseId' => 'wh-002',
            'departureWarehouseName' => 'Kho Phụ Vân Trì (Đông Anh)',
            'vehicleId' => (string) ($v2->id ?? 2),
            'vehiclePlate' => $v2->plate_number ?? '29C-777.66',
            'vehicleType' => $v2->vehicle_type ?? 'Xe Tải Hino 5T',
            'vehicleMaxCapacityKg' => (float) ($v2->capacity_kg ?? 5000),
            'mainDriverId' => (string) ($d1->id ?? 8),
            'mainDriverName' => $d1->name ?? 'Bùi Văn Thành',
            'mainDriverPhone' => $d1->phone ?? '0966 334 455',
            'dispatchDate' => '2026-09-07',
            'departureTime' => '07:30',
            'estimatedReturnTime' => '11:45',
            'actualReturnTime' => '11:50',
            'estimatedDistanceKm' => 45,
            'estimatedFuelLiters' => 8.0,
            'tollFeesEstimated' => 45000,
            'stops' => $trip2Stops,
            'totalWeightKg' => 650,
            'totalOrderCount' => 1,
            'totalGoodsValue' => 19700000,
            'initialDriverDebt' => 19700000,
            'collectedByCash' => 19700000,
            'collectedByQR' => 0,
            'uncollectedAmount' => 0,
            'settledAmountWithCashier' => 19700000,
            'remainingDriverDebt' => 0,
            'status' => 'Đã về bến (Đã quyết toán)',
            'cashSettlementVoucherCode' => 'PT-2026-0907-01',
            'settledAt' => '2026-09-07 13:15',
            'settledByCashierName' => 'Trần Thị Thu Hà (Thủ quỹ)',
            'createdAt' => '2026-09-07 07:00:00',
            'updatedAt' => '2026-09-07 13:15:00',
            'notes' => 'Xe giao xong về kho trước 12h, đã đối soát phiếu thu và hóa đơn VAT'
        ];

        DeliveryDispatch::updateOrCreate(
            ['code' => 'CX-2026-082'],
            [
                'vehicle_id' => $v2->id ?? 2,
                'driver_id' => $d1->id ?? 8,
                'status' => 'Đã về bến (Đã quyết toán)',
                'departure_time' => '2026-09-07 07:30:00',
                'return_time' => '2026-09-07 11:50:00',
                'total_weight_kg' => 650,
                'total_distance_km' => 45,
                'route_summary' => 'Tuyến 02: Kho Đông Anh - Cầu Thăng Long - Mê Linh - Phúc Yên',
                'notes' => 'Xe giao xong về kho trước 12h, đã đối soát phiếu thu và hóa đơn VAT',
                'details' => $trip2Details,
            ]
        );
    }
}
