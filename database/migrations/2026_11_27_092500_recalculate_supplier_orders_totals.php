<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Models\SupplierOrder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('supplier_orders') && Schema::hasTable('supplier_order_items')) {
            $orders = SupplierOrder::with('items')->get();
            foreach ($orders as $order) {
                $totalItemsAmount = 0;
                foreach ($order->items as $it) {
                    $qty = (float)$it->ordered_quantity;
                    $price = (float)$it->unit_price;
                    $subtotal = $qty * $price;
                    $discAmt = (float)$it->discount_amount;
                    $discPct = (float)$it->discount_percent;

                    if ($discPct > 0 && $discAmt == 0) {
                        $discAmt = round($subtotal * ($discPct / 100));
                    } elseif ($discAmt > 0 && $discPct == 0 && $subtotal > 0) {
                        $discPct = round(($discAmt / $subtotal) * 100, 2);
                    }
                    $discAmt = min($discAmt, $subtotal);
                    $amount = max(0, $subtotal - $discAmt);

                    $it->discount_percent = $discPct;
                    $it->discount_amount = $discAmt;
                    $it->amount = $amount;
                    $it->save();

                    $totalItemsAmount += $amount;
                }

                $discountAmount = (float)($order->discount_amount ?? 0);
                $otherFees = (float)($order->other_fees ?? 0);
                $totalAmount = max(0, $totalItemsAmount - $discountAmount + $otherFees);
                $debtDeducted = (float)($order->debt_deducted_amount ?? 0);
                $payableAmount = max(0, $totalAmount - $debtDeducted);

                $order->total_items_amount = $totalItemsAmount;
                $order->total_amount = $totalAmount;
                $order->payable_amount = $payableAmount;
                $order->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed
    }
};
