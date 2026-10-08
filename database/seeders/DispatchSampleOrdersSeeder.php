<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Dealer;
use App\Models\Vehicle;

class DispatchSampleOrdersSeeder extends Seeder
{
    public function run()
    {
        $products = Product::take(20)->get();
        if ($products->isEmpty()) return;

        // 1. Order already completed on Vehicle 29C-888.99
        $completedOrder = Order::updateOrCreate(
            ['order_code' => 'DH-2026-8899'],
            [
                'dealer_id' => 1,
                'customer_name' => 'Đại Lý Điện Nước Thành Vinh (TP. Vinh)',
                'customer_phone' => '0912 345 678',
                'customer_address' => 'Số 15 Quang Trung, TP. Vinh',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'delivery_date' => now()->format('Y-m-d'),
                'picking_date' => now()->format('Y-m-d'),
                'assigned_vehicle_plate' => '29C-888.99',
                'assigned_driver_id' => 8,
                'subtotal' => 70000000,
                'discount_amount' => 0,
                'tax_amount' => 7000000,
                'total_amount' => 77000000,
                'status' => 'Giao thành công',
                'payment_status' => 'Đã thanh toán',
                'payment_method' => 'Tiền mặt khi nhận hàng (COD)',
                'order_type' => 'normal',
                'notes' => 'Đơn hoàn thành đã giao tại điểm 1',
            ]
        );
        if ($completedOrder->items()->count() === 0) {
            $p = $products->first();
            $completedOrder->items()->create([
                'product_id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'unit' => $p->unit ?? 'CÂY',
                'quantity' => 50,
                'picked_qty' => 50,
                'is_picked' => true,
                'is_checked' => true,
                'checked_qty' => 50,
                'unit_price' => 1400000,
                'amount' => 70000000,
                'item_status' => 'Đủ hàng',
                'assigned_staff' => 'Bùi Văn Thành',
            ]);
        }

        // 2. BG-2026-6619
        $bgOrder = Order::updateOrCreate(
            ['order_code' => 'BG-2026-6619'],
            [
                'dealer_id' => 16,
                'customer_name' => 'Nhà Thầu Cơ Điện & Ống Nhựa An Bình',
                'customer_phone' => '0936 222 777',
                'customer_address' => 'Lô C2 Khu Đô Thị Nam Trung Yên',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'delivery_date' => now()->format('Y-m-d'),
                'picking_date' => now()->format('Y-m-d'),
                'assigned_vehicle_plate' => null,
                'subtotal' => 67580000,
                'discount_amount' => 0,
                'tax_amount' => 6758000,
                'total_amount' => 74338000,
                'status' => 'Đã bốc xong',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Ghi nợ đại lý',
                'order_type' => 'project',
                'notes' => 'Ghi chú: Phiếu báo giá gửi khách hàng',
            ]
        );
        if ($bgOrder->items()->count() === 0) {
            foreach ($products->take(4) as $p) {
                $bgOrder->items()->create([
                    'product_id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'unit' => $p->unit ?? 'CÂY',
                    'quantity' => 10,
                    'picked_qty' => 10,
                    'is_picked' => true,
                    'is_checked' => true,
                    'checked_qty' => 10,
                    'unit_price' => 1689500,
                    'amount' => 16895000,
                    'item_status' => 'Đủ hàng',
                    'assigned_staff' => 'Nguyễn Văn Đạt',
                ]);
            }
        }

        // 3. ord-1738568436940
        $ordOrder = Order::updateOrCreate(
            ['order_code' => 'ord-1738568436940'],
            [
                'dealer_id' => 18,
                'customer_name' => 'Đại Lý Điện Nước Hoàn Mỹ',
                'customer_phone' => '0988 555 444',
                'customer_address' => 'Số 45 Đường Giải Phóng, TP. Vinh',
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'delivery_date' => now()->format('Y-m-d'),
                'picking_date' => now()->format('Y-m-d'),
                'assigned_vehicle_plate' => null,
                'subtotal' => 21500000,
                'discount_amount' => 0,
                'tax_amount' => 2150000,
                'total_amount' => 23650000,
                'status' => 'Đang bốc hàng',
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => 'Chuyển khoản VietQR',
                'order_type' => 'normal',
                'notes' => 'Giao trong buổi sáng, kiểm tra kỹ quy cách ống',
            ]
        );
        if ($ordOrder->items()->count() === 0) {
            foreach ($products->skip(4)->take(6) as $p) {
                $ordOrder->items()->create([
                    'product_id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'unit' => $p->unit ?? 'CÂY',
                    'quantity' => 5,
                    'picked_qty' => 3,
                    'is_picked' => false,
                    'unit_price' => 716666,
                    'amount' => 3583333,
                    'item_status' => 'Đang bốc',
                    'assigned_staff' => 'Lê Văn Nam',
                ]);
            }
        }

        // Additional 5 orders to make 9 unassigned orders total
        $moreOrders = [
            [
                'code' => 'DH-2026-6677',
                'dealer_id' => 5,
                'name' => 'Cửa Hàng Điện Dân Dụng Hưng Nguyên',
                'phone' => '0973 111 222',
                'address' => 'Thị trấn Hưng Nguyên, Huyện Hưng Nguyên',
                'amount' => 15200000,
                'status' => 'Đã kiểm hàng',
                'notes' => 'Giao hàng gần chợ Hưng Nguyên',
                'items_count' => 3
            ],
            [
                'code' => 'DH-2026-2244',
                'dealer_id' => 6,
                'name' => 'Đại Lý Nhựa Tiền Phong Nam Đàn',
                'phone' => '0982 333 444',
                'address' => 'Khối 4, Thị trấn Nam Đàn, Huyện Nam Đàn',
                'amount' => 28900000,
                'status' => 'Đã bốc xong',
                'notes' => 'Hàng cồng kềnh xếp sau xe',
                'items_count' => 5
            ],
            [
                'code' => 'DH-2026-3390',
                'dealer_id' => 9,
                'name' => 'Tổng Kho Điện Nước Hồng Lĩnh',
                'phone' => '0915 666 777',
                'address' => 'Số 120 Đường Trần Phú, TX Hồng Lĩnh, Hà Tĩnh',
                'amount' => 18450000,
                'status' => 'Đã bốc xong',
                'notes' => 'Giao qua cầu Bến Thủy 2',
                'items_count' => 4
            ],
            [
                'code' => 'DH-2026-5521',
                'dealer_id' => 4,
                'name' => 'Đại Lý Vật Tư Cửa Lò - Lam Giang',
                'phone' => '0944 888 999',
                'address' => 'Đường Bình Minh, Thị xã Cửa Lò, Nghệ An',
                'amount' => 38500000,
                'status' => 'Đã bốc xong',
                'notes' => 'Ưu tiên giao trước 11h',
                'items_count' => 6
            ],
            [
                'code' => 'DH-2026-1188',
                'dealer_id' => 12,
                'name' => 'Đại Lý Ống PVC Cẩm Xuyên - Đức Phát',
                'phone' => '0932 777 888',
                'address' => 'Thị trấn Cẩm Xuyên, Huyện Cẩm Xuyên, Hà Tĩnh',
                'amount' => 42100000,
                'status' => 'Đang bốc hàng',
                'notes' => 'Giao tuyến đường dài Nam Hà Tĩnh',
                'items_count' => 7
            ],
        ];

        foreach ($moreOrders as $mo) {
            $o = Order::updateOrCreate(
                ['order_code' => $mo['code']],
                [
                    'dealer_id' => $mo['dealer_id'],
                    'customer_name' => $mo['name'],
                    'customer_phone' => $mo['phone'],
                    'customer_address' => $mo['address'],
                    'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                    'delivery_date' => now()->format('Y-m-d'),
                    'picking_date' => now()->format('Y-m-d'),
                    'assigned_vehicle_plate' => null,
                    'subtotal' => $mo['amount'] * 0.9,
                    'discount_amount' => 0,
                    'tax_amount' => $mo['amount'] * 0.1,
                    'total_amount' => $mo['amount'],
                    'status' => $mo['status'],
                    'payment_status' => 'Chưa thanh toán',
                    'payment_method' => 'Ghi nợ đại lý',
                    'order_type' => 'normal',
                    'notes' => $mo['notes'],
                ]
            );
            if ($o->items()->count() === 0) {
                foreach ($products->take($mo['items_count']) as $p) {
                    $o->items()->create([
                        'product_id' => $p->id,
                        'sku' => $p->sku,
                        'name' => $p->name,
                        'unit' => $p->unit ?? 'CÂY',
                        'quantity' => 4,
                        'picked_qty' => $mo['status'] === 'Đã bốc xong' ? 4 : 2,
                        'is_picked' => $mo['status'] === 'Đã bốc xong',
                        'unit_price' => round($mo['amount'] / ($mo['items_count'] * 4)),
                        'amount' => round($mo['amount'] / $mo['items_count']),
                        'item_status' => $mo['status'] === 'Đã bốc xong' ? 'Đủ hàng' : 'Đang bốc',
                    ]);
                }
            }
        }
    }
}
