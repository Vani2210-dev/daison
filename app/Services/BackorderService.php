<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\PickingSyncEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackorderService
{
    /**
     * Tự động tách đơn nợ hàng (Backorder) khi xuất kho / soạn hàng bị thiếu số lượng
     *
     * @param Order $order Đơn gốc
     * @param array $shortageItems Mảng chứa thông tin các sản phẩm thiếu [{ id, product_id, sku, picked_qty, reason }]
     * @param string|null $note Ghi chú lý do thiếu
     * @return array ['success' => bool, 'backorder' => Order|null, 'original_order' => Order, 'message' => string]
     */
    public static function splitOrderOnShortage(Order $order, array $shortageItems = [], ?string $note = null): array
    {
        return [
            'success'        => false,
            'message'        => 'Cơ chế tách đơn nợ hàng đã được gỡ bỏ.',
            'original_order' => $order,
            'backorder'      => null,
        ];
    }

    /**
     * Cơ chế đơn nợ / giao sau đã được gỡ bỏ nên không quét thông báo đơn nợ khi nhập hàng.
     */
    public static function checkAndNotifyBackordersOnStockArrival(?int $productId = null, ?int $warehouseId = null): array
    {
        return [];
    }
}
