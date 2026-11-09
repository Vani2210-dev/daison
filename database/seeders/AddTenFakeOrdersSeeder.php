<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Thêm 10 đơn hàng giả (không xoá dữ liệu cũ).
 * Chạy: php artisan db:seed --class=AddTenFakeOrdersSeeder
 */
class AddTenFakeOrdersSeeder extends Seeder
{
    public function run()
    {
        $dealers = Dealer::inRandomOrder()->take(10)->get();
        $products = Product::where('is_active', true)->get();
        if ($products->isEmpty()) {
            $products = Product::all();
        }
        if ($products->isEmpty()) {
            $this->command?->error('Không có sản phẩm nào để tạo đơn hàng.');
            return;
        }

        $salesReps = User::whereHas('roles', function ($q) {
            $q->whereIn('slug', ['Sales_Rep', 'Sales_Accountant', 'Manager']);
        })->get();
        if ($salesReps->isEmpty()) {
            $salesReps = User::take(5)->get();
        }

        $fallbackCustomers = [
            ['Đại Lý Điện Nước Minh Phát', '0912 111 222', 'Số 20 Lê Lợi, TP. Vinh, Nghệ An'],
            ['Cửa Hàng Vật Tư Thanh Chương', '0936 333 444', 'Thị Trấn Thanh Chương, Nghệ An'],
            ['Đại Lý Ống Nhựa Đô Lương', '0978 555 666', 'Khối 5 Thị Trấn Đô Lương, Nghệ An'],
            ['Công Ty TNHH Cơ Điện Thạch Hà', '0945 777 888', 'Thị Trấn Thạch Hà, Hà Tĩnh'],
            ['Nhà Phân Phối Điện Nước Quỳnh Lưu', '0982 999 000', 'Thị Trấn Cầu Giát, Quỳnh Lưu, Nghệ An'],
            ['Đại Lý Vật Liệu Can Lộc', '0915 123 456', 'Thị Trấn Nghèn, Can Lộc, Hà Tĩnh'],
            ['Cửa Hàng Điện Nước Yên Thành', '0903 234 567', 'Thị Trấn Yên Thành, Nghệ An'],
            ['Tổng Kho Vật Tư Hương Sơn', '0977 345 678', 'Thị Trấn Phố Châu, Hương Sơn, Hà Tĩnh'],
            ['Đại Lý Thiết Bị Nước Anh Sơn', '0988 456 789', 'Thị Trấn Anh Sơn, Nghệ An'],
            ['DNTN Đức Thọ Phát', '0932 567 890', 'Thị Trấn Đức Thọ, Hà Tĩnh'],
        ];

        $notesPool = [
            'Giao buổi sáng trước 11h.',
            'Gọi trước 30 phút khi xe xuất bến.',
            'Hàng cồng kềnh, xếp sau xe.',
            'Kiểm tra kỹ quy cách ống trước khi giao.',
            'Đơn bổ sung tồn kho cuối tuần.',
        ];
        $paymentMethods = ['Ghi nợ đại lý', 'Chuyển khoản', 'Tiền mặt khi nhận hàng (COD)'];

        // Tìm số thứ tự mã đơn tiếp theo, tránh trùng mã cũ
        $year = now()->format('Y');
        $prefix = "DH-{$year}-";
        $maxNum = Order::where('order_code', 'like', $prefix . '%')
            ->pluck('order_code')
            ->map(fn ($c) => (int) substr($c, strlen($prefix)))
            ->max() ?? 2000;
        $nextNum = max($maxNum, 2000) + 1;

        DB::transaction(function () use ($dealers, $products, $salesReps, $fallbackCustomers, $notesPool, $paymentMethods, $prefix, &$nextNum) {
            for ($i = 0; $i < 10; $i++) {
                $dealer = $dealers->get($i);
                $rep = $salesReps->isNotEmpty() ? $salesReps->random() : null;
                $fb = $fallbackCustomers[$i];

                $subtotal = 0;
                $itemsData = [];
                foreach ($products->shuffle()->take(rand(2, 5)) as $p) {
                    $qty = rand(2, 20) * 5;
                    $unitPrice = ($p->cost_price && $p->cost_price > 0)
                        ? round($p->cost_price * 1.25, -3)
                        : rand(5, 50) * 10000;
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
                        'cost_price' => $p->cost_price ?? 0,
                        'unit_price' => $unitPrice,
                        'original_price' => $unitPrice,
                        'amount' => $amount,
                        'item_status' => 'Chờ soạn hàng',
                    ];
                }

                $taxAmount = round($subtotal * 0.1, -2);

                $order = Order::create([
                    'order_code' => $prefix . str_pad((string) $nextNum++, 4, '0', STR_PAD_LEFT),
                    'dealer_id' => $dealer?->id,
                    'customer_name' => $dealer?->name ?? $fb[0],
                    'customer_phone' => $dealer?->phone ?? $fb[1],
                    'customer_address' => $dealer?->address ?? $fb[2],
                    'warehouse' => 'Kho Tổng Mê Linh (MQ)',
                    'delivery_date' => now()->addDays(rand(0, 3))->format('Y-m-d'),
                    'picking_date' => null,
                    'creator_id' => $rep?->id,
                    'order_creator' => $rep?->name ?? 'Nguyễn Văn Nam',
                    'sales_rep_id' => $rep?->id,
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $subtotal + $taxAmount,
                    'status' => 'Chốt đơn',
                    'stock_deducted' => false,
                    'payment_status' => 'Chưa thanh toán',
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'order_type' => 'normal',
                    'notes' => $notesPool[array_rand($notesPool)],
                ]);

                foreach ($itemsData as $item) {
                    $order->items()->create($item);
                }

                $this->command?->info("Đã tạo đơn {$order->order_code} - {$order->customer_name}");
            }
        });
    }
}
