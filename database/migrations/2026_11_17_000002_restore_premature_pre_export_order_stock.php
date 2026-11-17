<?php

use App\Http\Controllers\OrderController;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Restore physical on-hand stock for pre-export finalized orders that were
     * prematurely deducted at "Chốt đơn", converting them into reserved stock instead.
     */
    public function up(): void
    {
        $orderController = app(OrderController::class);

        $ordersToRestore = Order::with('items')
            ->where('stock_deducted', true)
            ->whereIn('status', [
                'Chốt đơn',
                'Đã duyệt',
                'Chờ duyệt',
                'Chờ duyệt hạn mức',
                'Chờ kế toán duyệt',
            ])
            ->get();

        foreach ($ordersToRestore as $order) {
            $orderController->restoreOrderStock($order);
        }

        OrderController::refreshAvailabilityCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left non-destructive.
    }
};
