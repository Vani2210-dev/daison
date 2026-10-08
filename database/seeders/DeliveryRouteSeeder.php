<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryRoute;
use App\Models\Vehicle;

class DeliveryRouteSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            [
                'code' => 'TUYEN-01',
                'name' => 'Tuyến 01: Kho Mê Linh - Giải Phóng - Trường Chinh - Nguyễn Trãi',
                'departure_warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'stops' => ['Thanh Xuân', 'Giải Phóng', 'Trường Chinh', 'Nguyễn Trãi', 'Hà Đông'],
                'distance_km' => 38.0,
                'estimated_time_hours' => 4.5,
                'fuel_liters' => 6.5,
                'toll_fee' => 35000,
                'assigned_vehicle_plate' => '29C-888.99',
                'assigned_driver_name' => 'Bùi Văn Thành',
                'is_active' => true,
                'notes' => 'Tuyến trục chính khu vực Nam Hà Nội, mật độ đại lý điện nước dày đặc.',
            ],
            [
                'code' => 'TUYEN-02',
                'name' => 'Tuyến 02: Kho Đông Anh - Cầu Thăng Long - Mê Linh - Phúc Yên',
                'departure_warehouse' => 'Kho Chi Nhánh Đông Anh (DXH)',
                'stops' => ['Đông Anh', 'KCN Thăng Long', 'Mê Linh', 'Phúc Yên'],
                'distance_km' => 45.0,
                'estimated_time_hours' => 5.0,
                'fuel_liters' => 8.0,
                'toll_fee' => 45000,
                'assigned_vehicle_plate' => '29C-777.66',
                'assigned_driver_name' => 'Bùi Văn Thành',
                'is_active' => true,
                'notes' => 'Tuyến cung ứng vật tư ống công trình công nghiệp và các dự án KCN.',
            ],
            [
                'code' => 'TUYEN-03',
                'name' => 'Tuyến 03: Cầu Giấy - Hoàng Quốc Việt - Bắc Từ Liêm - Nhổn - Hoài Đức',
                'departure_warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'stops' => ['Cầu Giấy', 'Hoàng Quốc Việt', 'Bắc Từ Liêm', 'Nhổn', 'Hoài Đức'],
                'distance_km' => 32.0,
                'estimated_time_hours' => 3.5,
                'fuel_liters' => 5.5,
                'toll_fee' => 0,
                'assigned_vehicle_plate' => '29H-555.44',
                'assigned_driver_name' => 'Trần Văn Kiên',
                'is_active' => true,
                'notes' => 'Tuyến Tây Hà Nội phục vụ các cửa hàng phân phối ống nhựa Tiền Phong & phụ kiện.',
            ],
            [
                'code' => 'TUYEN-04',
                'name' => 'Tuyến 04: Nội đô Hà Nội (Hoàn Kiếm - Ba Đình - Đống Đa - Hai Bà Trưng)',
                'departure_warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'stops' => ['Ba Đình', 'Đống Đa', 'Hoàn Kiếm', 'Hai Bà Trưng'],
                'distance_km' => 26.0,
                'estimated_time_hours' => 3.0,
                'fuel_liters' => 4.5,
                'toll_fee' => 0,
                'assigned_vehicle_plate' => '29H-333.22',
                'assigned_driver_name' => 'Nguyễn Tiến Đạt',
                'is_active' => false,
                'notes' => 'Tuyến giao nhanh bán lẻ nội đô bằng xe bán tải, cấm giờ cao điểm.',
            ],
        ];

        foreach ($routes as $r) {
            DeliveryRoute::updateOrCreate(['code' => $r['code']], $r);
        }

        // Set realistic 4 dispatch statuses to vehicles
        $v1 = Vehicle::where('plate_number', '29C-888.99')->first();
        if ($v1) {
            $v1->update(['status' => 'Đang giao hàng']);
        }
        $v2 = Vehicle::where('plate_number', '29C-777.66')->first();
        if ($v2) {
            $v2->update(['status' => 'Đang bốc hàng']);
        }
        $v3 = Vehicle::where('plate_number', '29H-555.44')->first();
        if ($v3) {
            $v3->update(['status' => 'Trên đường về']);
        }
        $v4 = Vehicle::where('plate_number', '29H-333.22')->first();
        if ($v4) {
            $v4->update(['status' => 'Rảnh']);
        }
    }
}
