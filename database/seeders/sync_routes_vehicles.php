<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DeliveryRoute;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    echo "1. Đang đồng bộ lộ tuyến Nghệ An - Hà Tĩnh vào DB...\n";

    DB::table('vehicles')->update(['delivery_route_id' => null]);
    DB::table('delivery_routes')->delete();

    $r1 = DeliveryRoute::create([
        'code' => 'TUYEN-01',
        'name' => 'Tuyến 01: TP. Vinh - Hưng Nguyên - Nam Đàn',
        'departure_warehouse' => 'Kho Tổng Mê Linh (MQ)',
        'stops' => ['TP. Vinh', 'Huyện Hưng Nguyên', 'Huyện Nam Đàn'],
        'distance_km' => 38,
        'estimated_time_hours' => 4.0,
        'fuel_liters' => 6.0,
        'toll_fee' => 35000,
        'assigned_vehicle_plate' => '29C-888.99',
        'assigned_driver_name' => 'Bùi Văn Thành',
        'is_active' => true,
        'notes' => 'Tuyến trục chính khu vực Nam Nghệ An',
    ]);

    $r2 = DeliveryRoute::create([
        'code' => 'TUYEN-02',
        'name' => 'Tuyến 02: Bắc Hà Tĩnh (Nghi Xuân - TX Hồng Lĩnh - Can Lộc)',
        'departure_warehouse' => 'Kho Tổng Mê Linh (MQ)',
        'stops' => ['Huyện Nghi Xuân', 'Thị xã Hồng Lĩnh', 'Huyện Can Lộc'],
        'distance_km' => 45,
        'estimated_time_hours' => 4.5,
        'fuel_liters' => 7.0,
        'toll_fee' => 45000,
        'assigned_vehicle_plate' => '29C-777.66',
        'assigned_driver_name' => 'Bùi Văn Thành',
        'is_active' => true,
        'notes' => 'Tuyến giao hàng trục QL1A Bắc Hà Tĩnh qua cầu Bến Thủy',
    ]);

    $r3 = DeliveryRoute::create([
        'code' => 'TUYEN-03',
        'name' => 'Tuyến 03: Đông Bắc Nghệ An (Nghi Lộc - Diễn Châu - TX Cửa Lò)',
        'departure_warehouse' => 'Kho Tổng Mê Linh (MQ)',
        'stops' => ['Huyện Nghi Lộc', 'Huyện Diễn Châu', 'Thị xã Cửa Lò'],
        'distance_km' => 35,
        'estimated_time_hours' => 3.5,
        'fuel_liters' => 5.5,
        'toll_fee' => 0,
        'assigned_vehicle_plate' => '29H-555.44',
        'assigned_driver_name' => 'Trần Văn Kiên',
        'is_active' => true,
        'notes' => 'Tuyến công trình công nghiệp KCN Nam Cấm & ven biển',
    ]);

    $r4 = DeliveryRoute::create([
        'code' => 'TUYEN-04',
        'name' => 'Tuyến 04: Nam Hà Tĩnh (TP. Hà Tĩnh - Cẩm Xuyên - TX Kỳ Anh)',
        'departure_warehouse' => 'Kho Tổng Mê Linh (MQ)',
        'stops' => ['TP. Hà Tĩnh', 'Huyện Cẩm Xuyên', 'Thị xã Kỳ Anh'],
        'distance_km' => 65,
        'estimated_time_hours' => 5.0,
        'fuel_liters' => 9.0,
        'toll_fee' => 50000,
        'assigned_vehicle_plate' => '29H-333.22',
        'assigned_driver_name' => 'Nguyễn Tiến Đạt',
        'is_active' => true,
        'notes' => 'Tuyến đường dài cung ứng KKT Vũng Áng và Nam Hà Tĩnh',
    ]);

    echo "2. Đang kết nối xe với các tuyến giao hàng tương ứng...\n";
    Vehicle::where('plate_number', '29C-888.99')->update(['delivery_route_id' => $r1->id]);
    Vehicle::where('plate_number', '29C-777.66')->update(['delivery_route_id' => $r2->id]);
    Vehicle::where('plate_number', '29H-555.44')->update(['delivery_route_id' => $r3->id]);
    Vehicle::where('plate_number', '29H-333.22')->update(['delivery_route_id' => $r4->id]);

    DB::commit();
    echo "=== ĐỒNG BỘ THÀNH CÔNG LỘ TUYẾN VÀ XE GIAO HÀNG VÀO DATABASE! ===\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "LỖI: " . $e->getMessage() . "\n";
    exit(1);
}
