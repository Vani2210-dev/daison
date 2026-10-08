<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Dealer;
use App\Models\User;

class ResetAndFakeOrdersSeeder extends Seeder
{
    public function run()
    {
        // 1. Clear existing orders and related records safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (DB::getSchemaBuilder()->hasTable('delivery_dispatch_orders')) {
            DB::table('delivery_dispatch_orders')->truncate();
        }
        if (DB::getSchemaBuilder()->hasTable('delivery_returns')) {
            DB::table('delivery_returns')->truncate();
        }
        if (DB::getSchemaBuilder()->hasTable('picking_sync_events')) {
            DB::table('picking_sync_events')->truncate();
        }
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Fetch dependencies
        $dealers = Dealer::all();
        $products = Product::where('is_active', true)->get();
        if ($products->isEmpty()) {
            $products = Product::all();
        }
        $salesReps = User::whereHas('roles', function($q) {
            $q->whereIn('slug', ['Sales_Rep', 'Sales_Accountant', 'Manager']);
        })->get();
        if ($salesReps->isEmpty()) {
            $salesReps = User::take(5)->get();
        }

        $destinations = [
            [
                'dealer_name' => 'Đại Lý Điện Nước Thành Vinh',
                'phone' => '0912 345 678',
                'address' => 'Số 15 Quang Trung, TP. Vinh, Nghệ An',
                'notes' => 'Giao buổi sáng trước 11h, đường lớn xe tải vào tận kho.',
                'order_type' => 'normal',
                'payment_method' => 'Ghi nợ đại lý'
            ],
            [
                'dealer_name' => 'Nhà Thầu Cơ Điện & Ống Nhựa An Bình',
                'phone' => '0936 222 777',
                'address' => 'Khu Đô Thị Mới Nam Đàn, Huyện Nam Đàn, Nghệ An',
                'notes' => 'Đơn cấp dự án công trình cấp thoát nước Nam Đàn.',
                'order_type' => 'project',
                'payment_method' => 'Chuyển khoản'
            ],
            [
                'dealer_name' => 'Cửa Hàng Vật Tư Xây Dựng Hưng Nguyên',
                'phone' => '0978 111 222',
                'address' => 'Khối 3 Thị Trấn Hưng Nguyên, Huyện Hưng Nguyên, Nghệ An',
                'notes' => 'Hàng ống PVC kèm phụ kiện cút láng tê thu.',
                'order_type' => 'normal',
                'payment_method' => 'Tiền mặt khi nhận hàng (COD)'
            ],
            [
                'dealer_name' => 'Công Ty TNHH MTV Cơ Điện Bắc Hà Tĩnh',
                'phone' => '0945 888 999',
                'address' => 'Số 88 Đường Trần Phú, TX. Hồng Lĩnh, Hà Tĩnh',
                'notes' => 'Khách đại lý cấp 1, chiết khấu theo chính sách quý.',
                'order_type' => 'normal',
                'payment_method' => 'Ghi nợ đại lý'
            ],
            [
                'dealer_name' => 'Đại Lý Ống Cấp Thoát Nước Nghi Xuân',
                'phone' => '0982 333 444',
                'address' => 'Thị Trấn Tiên Điền, Huyện Nghi Xuân, Hà Tĩnh',
                'notes' => 'Cần giao sớm để khách kịp bàn giao công trình dân dụng.',
                'order_type' => 'normal',
                'payment_method' => 'Chuyển khoản'
            ],
            [
                'dealer_name' => 'Nhà Phân Phối Điện Nước Diễn Châu',
                'phone' => '0915 666 777',
                'address' => 'Ngã Ba Diễn Châu, Quốc Lộ 1A, Huyện Diễn Châu, Nghệ An',
                'notes' => 'Xe tải đỗ bên hông kho đại lý bốc dỡ.',
                'order_type' => 'normal',
                'payment_method' => 'Ghi nợ đại lý'
            ],
            [
                'dealer_name' => 'Trung Tâm Vật Liệu Xây Dựng Cửa Lò',
                'phone' => '0903 555 888',
                'address' => 'Đường Bình Minh, Phường Nghi Hương, Thị Xã Cửa Lò, Nghệ An',
                'notes' => 'Giao ống chịu nhiệt PPR cho khu resort ven biển.',
                'order_type' => 'project',
                'payment_method' => 'Chuyển khoản'
            ],
            [
                'dealer_name' => 'Cửa Hàng Thiết Bị Vệ Sinh & Ống Nước Nghi Lộc',
                'phone' => '0977 444 333',
                'address' => 'Quán Hành, Huyện Nghi Lộc, Nghệ An',
                'notes' => 'Liên hệ chủ cửa hàng trước 30 phút khi xe xuất bến.',
                'order_type' => 'normal',
                'payment_method' => 'Tiền mặt khi nhận hàng (COD)'
            ],
            [
                'dealer_name' => 'Đại Lý Thiết Bị Điện Nước Kỳ Anh',
                'phone' => '0988 999 111',
                'address' => 'Khu Kinh Tế Vũng Áng, Phường Kỳ Long, TX. Kỳ Anh, Hà Tĩnh',
                'notes' => 'Cung cấp ống luồn dây điện và phụ kiện công nghiệp.',
                'order_type' => 'project',
                'payment_method' => 'Chuyển khoản'
            ],
            [
                'dealer_name' => 'Doanh Nghiệp Tư Nhân Cẩm Xuyên Phát',
                'phone' => '0932 777 666',
                'address' => 'Thị Trấn Cẩm Xuyên, Huyện Cẩm Xuyên, Hà Tĩnh',
                'notes' => 'Đơn bổ sung tồn kho bán lẻ cuối tuần.',
                'order_type' => 'normal',
                'payment_method' => 'Ghi nợ đại lý'
            ],
        ];

        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');
        $dayAfter = now()->addDays(2)->format('Y-m-d');

        // Phân loại sản phẩm theo ngành hàng để mỗi đơn đều có cả ống và phụ kiện
        $pipeProducts = $products->filter(fn($p) => (int)$p->category_id === 1);
        $fittingProducts = $products->filter(fn($p) => (int)$p->category_id === 2);
        $otherProducts = $products->filter(fn($p) => !in_array((int)$p->category_id, [1, 2]));

        foreach ($destinations as $idx => $dest) {
            $num = $idx + 1;
            $orderCode = 'DH-2026-' . str_pad((string) (1000 + $num), 4, '0', STR_PAD_LEFT);
            $dealer = $dealers->skip($idx % max(1, $dealers->count()))->first();
            $rep = $salesReps->skip($idx % max(1, $salesReps->count()))->first();

            // Chọn sản phẩm cho đơn: ít nhất 1 ống, 1 phụ kiện
            $selectedProducts = collect();
            if ($pipeProducts->isNotEmpty()) {
                $selectedProducts = $selectedProducts->merge($pipeProducts->shuffle()->take(rand(1, 2)));
            }
            if ($fittingProducts->isNotEmpty()) {
                $selectedProducts = $selectedProducts->merge($fittingProducts->shuffle()->take(rand(1, 2)));
            }
            if ($otherProducts->isNotEmpty()) {
                $selectedProducts = $selectedProducts->merge($otherProducts->shuffle()->take(rand(0, 1)));
            }
            if ($selectedProducts->isEmpty()) {
                $selectedProducts = $products->shuffle()->take(rand(2, 4));
            }

            $subtotal = 0;
            $itemsData = [];

            foreach ($selectedProducts as $p) {
                $qty = rand(5, 30) * 2; // 10, 20, 30...
                $unitPrice = ($p->cost_price && $p->cost_price > 0) ? round($p->cost_price * 1.25, -3) : 150000;
                $amount = $qty * $unitPrice;
                $subtotal += $amount;

                $itemsData[] = [
                    'product_id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'unit' => $p->unit ?: 'Cây',
                    'quantity' => $qty,
                    'picked_qty' => 0,
                    'is_picked' => false,
                    'is_checked' => false,
                    'checked_qty' => 0,
                    'unit_price' => $unitPrice,
                    'amount' => $amount,
                    'item_status' => 'Chờ soạn hàng',
                    'assigned_staff' => null,
                ];
            }

            $taxAmount = round($subtotal * 0.1, -2);
            $totalAmount = $subtotal + $taxAmount;

            // Ngày giao hàng: 6 đơn hôm nay, 2 đơn ngày mai, 2 đơn ngày kia
            $deliveryDate = match(true) {
                $num <= 6 => $today,
                $num <= 8 => $tomorrow,
                default => $dayAfter,
            };

            $order = Order::create([
                'order_code' => $orderCode,
                'dealer_id' => $dealer?->id,
                'customer_name' => $dest['dealer_name'],
                'customer_phone' => $dest['phone'],
                'customer_address' => $dest['address'],
                'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                'delivery_date' => $deliveryDate,
                'picking_date' => null, // Để trống ngày soạn đơn theo yêu cầu
                'creator_id' => $rep?->id,
                'order_creator' => $rep?->name ?? 'Nguyễn Văn Nam',
                'sales_rep_id' => $rep?->id,
                'assigned_driver_id' => null,
                'assigned_vehicle_plate' => null,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'Chốt đơn',
                'stock_deducted' => false,
                'payment_status' => 'Chưa thanh toán',
                'payment_method' => $dest['payment_method'],
                'order_type' => $dest['order_type'],
                'notes' => $dest['notes'],
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }
        }
    }
}
