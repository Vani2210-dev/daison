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
        $order->loadMissing('items');

        // Tìm các mặt hàng bị thiếu (picked_qty < quantity)
        $itemsToSplit = [];
        foreach ($order->items as $it) {
            $matchedShortage = null;
            foreach ($shortageItems as $s) {
                if ((isset($s['id']) && $s['id'] == $it->id) || (isset($s['sku']) && $s['sku'] == $it->sku)) {
                    $matchedShortage = $s;
                    break;
                }
            }

            $currentPicked = $matchedShortage && isset($matchedShortage['picked_qty']) 
                ? (float)$matchedShortage['picked_qty'] 
                : (float)($it->picked_qty ?? 0);

            $reqQty = (float)$it->quantity;
            $shortageQty = max(0, $reqQty - $currentPicked);

            if ($shortageQty > 0) {
                $itemsToSplit[] = [
                    'item'         => $it,
                    'picked_qty'   => $currentPicked,
                    'shortage_qty' => $shortageQty,
                    'reason'       => $matchedShortage['reason'] ?? ($it->shortage_reason ?? 'Thiếu tồn kho thực tế'),
                ];
            }
        }

        if (empty($itemsToSplit)) {
            return [
                'success' => false,
                'message' => 'Không có sản phẩm nào bị thiếu để tách đơn giao sau.',
                'original_order' => $order,
                'backorder' => null
            ];
        }

        DB::beginTransaction();
        try {
            // 1. Tạo mã đơn giao sau mới
            $parentCode = $order->order_code;
            $suffixIndex = 1;
            $backOrderCode = $parentCode . '-NO' . $suffixIndex;
            while (Order::where('order_code', $backOrderCode)->exists()) {
                $suffixIndex++;
                $backOrderCode = $parentCode . '-NO' . $suffixIndex;
            }

            // 2. Tính toán tổng tiền cho đơn giao sau
            $backSubtotal = 0;
            $backTax = 0;
            $backOrderItemsData = [];

            foreach ($itemsToSplit as $sData) {
                $it = $sData['item'];
                $sQty = $sData['shortage_qty'];
                $unitPrice = (float)$it->unit_price;
                $vatRate = (float)($it->vat_percent ?? 10);
                $lineAmt = round($sQty * $unitPrice);
                $lineVat = round($lineAmt * ($vatRate / 100));

                $backSubtotal += $lineAmt;
                $backTax += $lineVat;

                $backOrderItemsData[] = [
                    'product_id'          => $it->product_id,
                    'sku'                 => $it->sku,
                    'name'                => $it->name,
                    'unit'                => $it->unit,
                    'quantity'            => $sQty,
                    'gift_quantity'       => 0,
                    'unit_price'          => $unitPrice,
                    'original_price'      => $it->original_price ?? $unitPrice,
                    'cost_price'          => $it->cost_price ?? 0,
                    'applied_tier_policy' => $it->applied_tier_policy,
                    'amount'              => $lineAmt + $lineVat,
                    'picked_qty'          => 0,
                    'item_status'         => 'Chờ hàng về',
                    'is_picked'           => false,
                    'shortage_reason'     => $sData['reason'],
                ];
            }

            $backTotal = $backSubtotal + $backTax;

            // 3. Tạo Đơn Giao Sau (Backorder)
            $backOrder = Order::create([
                'order_code'             => $backOrderCode,
                'parent_order_id'        => $order->id,
                'parent_order_code'      => $order->order_code,
                'dealer_id'              => $order->dealer_id,
                'creator_id'             => $order->creator_id,
                'order_creator'          => $order->order_creator,
                'sales_rep_id'           => $order->sales_rep_id,
                'customer_name'          => $order->customer_name,
                'customer_phone'         => $order->customer_phone,
                'customer_address'       => $order->customer_address,
                'warehouse'              => $order->warehouse,
                'order_type'             => 'backorder',
                'status'                 => 'Giao sau',
                'backorder_status'       => 'pending_stock',
                'payment_status'         => 'Chưa thanh toán',
                'payment_method'         => $order->payment_method ?? 'Ghi nợ đại lý',
                'subtotal'               => $backSubtotal,
                'discount_amount'        => 0,
                'tax_amount'             => $backTax,
                'total_amount'           => $backTotal,
                'notes'                  => "[ĐƠN GIAO SAU] Tự động tách từ đơn gốc {$parentCode} do kho thiếu hàng khi xuất.\n" . ($note ?? ''),
            ]);

            foreach ($backOrderItemsData as $bItemData) {
                $bItemData['order_id'] = $backOrder->id;
                OrderItem::create($bItemData);
            }

            // 4. Cắt giảm số lượng trên đơn gốc xuống số thực bốc
            $origSubtotal = 0;
            $origTax = 0;

            foreach ($order->items as $it) {
                $splitInfo = collect($itemsToSplit)->firstWhere('item.id', $it->id);
                if ($splitInfo) {
                    $newQty = $splitInfo['picked_qty'];
                    if ($newQty > 0) {
                        $it->quantity = $newQty;
                        $it->picked_qty = $newQty;
                        $it->item_status = 'Đủ hàng';
                        $it->is_picked = true;
                        $it->shortage_reason = null;
                        $vatRate = (float)($it->vat_percent ?? 10);
                        $lineAmt = round($newQty * (float)$it->unit_price);
                        $lineVat = round($lineAmt * ($vatRate / 100));
                        $it->amount = $lineAmt + $lineVat;
                        $it->save();

                        $origSubtotal += $lineAmt;
                        $origTax += $lineVat;
                    } else {
                        // Số thực bốc = 0 -> Xóa mặt hàng này khỏi đơn gốc (đã chuyển sang đơn nợ)
                        $it->delete();
                    }
                } else {
                    $origSubtotal += round((float)$it->quantity * (float)$it->unit_price);
                    $vatRate = (float)($it->vat_percent ?? 10);
                    $origTax += round(((float)$it->quantity * (float)$it->unit_price) * ($vatRate / 100));
                }
            }

            // Cập nhật lại tổng tiền đơn gốc
            $order->subtotal = $origSubtotal;
            $order->tax_amount = $origTax;
            $order->total_amount = max(0, $origSubtotal - (float)$order->discount_amount + $origTax);
            $order->shortage_reported = true;
            $order->shortage_reported_at = now();
            $order->shortage_reported_note = "Đã tự động tách phần thiếu sang đơn giao sau: {$backOrderCode}";
            $order->save();

            DB::commit();

            // Xóa cache danh mục sản phẩm
            InventoryAvailabilityService::clearCache();

            return [
                'success'        => true,
                'message'        => "Đã tự động cắt đơn gốc {$parentCode} và tạo Đơn Giao Sau {$backOrderCode} cho phần thiếu!",
                'original_order' => $order->fresh(['items']),
                'backorder'      => $backOrder->fresh(['items']),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error splitting backorder: ' . $e->getMessage(), ['exception' => $e]);
            return [
                'success' => false,
                'message' => 'Lỗi khi tách đơn giao sau: ' . $e->getMessage(),
                'original_order' => $order,
                'backorder' => null
            ];
        }
    }

    /**
     * Tự động kiểm tra và báo các Đơn Giao Sau đã đủ điều kiện xuất khi có hàng mới nhập về kho
     *
     * @param int|null $productId Lọc theo sản phẩm vừa nhập (null = kiểm tra toàn bộ)
     * @param int|null $warehouseId Lọc theo kho vừa nhập (null = kiểm tra toàn bộ)
     * @return array Danh sách các đơn giao sau vừa được chuyển sang 'ready_to_deliver'
     */
    public static function checkAndNotifyBackordersOnStockArrival(?int $productId = null, ?int $warehouseId = null): array
    {
        // Quét các đơn giao sau đang chờ hàng về
        $query = Order::with(['items.product', 'dealer'])
            ->where('order_type', 'backorder')
            ->where(function ($q) {
                $q->where('backorder_status', 'pending_stock')
                  ->orWhere('status', 'Giao sau')
                  ->orWhere('status', 'Chờ hàng về');
            })
            ->whereNotIn('status', ['Hủy', 'Đã hủy', 'Đã giao hàng', 'Hoàn tất']);

        if ($productId) {
            $query->whereHas('items', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        // Ưu tiên đơn nào đặt trước phục vụ trước (FIFO)
        $pendingBackorders = $query->orderBy('created_at', 'asc')->get();

        $readyOrders = [];

        foreach ($pendingBackorders as $bOrder) {
            $isAllItemsAvailable = true;
            $itemsStatus = [];

            foreach ($bOrder->items as $bItem) {
                $p = $bItem->product;
                if (!$p) {
                    $p = Product::where('sku', $bItem->sku)->first();
                }

                if (!$p) {
                    $isAllItemsAvailable = false;
                    break;
                }

                $stockData = InventoryAvailabilityService::checkItemsAvailability(
                    $bOrder->warehouse,
                    [['product_id' => $p->id, 'quantity' => (float)$bItem->quantity, 'unit' => $bItem->unit]],
                    $bOrder->id // Exclude chính đơn này khi tính khả dụng
                );

                if (!$stockData['is_valid']) {
                    $isAllItemsAvailable = false;
                    break;
                }
            }

            if ($isAllItemsAvailable && $bOrder->items->count() > 0) {
                // Đơn này đã đủ 100% hàng trong kho để giao!
                $bOrder->backorder_status = 'ready_to_deliver';
                $bOrder->status = 'Đã đủ hàng';
                $appendNote = "\n[TỰ ĐỘNG BÁO HÀNG VỀ " . now()->format('H:i d/m/Y') . "] Kho đã có đủ hàng để xuất giao!";
                if (!str_contains($bOrder->notes ?? '', '[TỰ ĐỘNG BÁO HÀNG VỀ')) {
                    $bOrder->notes = ($bOrder->notes ? $bOrder->notes : '') . $appendNote;
                }
                $bOrder->save();

                // Tạo sự kiện PickingSyncEvent để phát thông báo realtime tới toàn bộ hệ thống
                try {
                    PickingSyncEvent::create([
                        'order_id'            => (string) $bOrder->id,
                        'event_type'          => 'BACKORDER_STOCK_ARRIVED',
                        'performer_name'      => 'Hệ thống tự động',
                        'assigned_staff'      => (string) ($bOrder->order_creator ?: 'NVKD'),
                        'order_status'        => 'Đã đủ hàng',
                        'shortage_reported'   => false,
                        'extra_data'          => [
                            'orderId'        => (int) $bOrder->id,
                            'orderCode'      => $bOrder->order_code,
                            'customerName'   => $bOrder->customer_name,
                            'parentOrderCode'=> $bOrder->parent_order_code,
                            'warehouse'      => $bOrder->warehouse,
                            'message'        => "🔔 Đơn nợ hàng {$bOrder->order_code} (Đơn gốc: {$bOrder->parent_order_code}) của khách [{$bOrder->customer_name}] hiện đã có đủ hàng trong kho để giao!",
                            'readyAt'        => now()->format('H:i:s d/m/Y'),
                        ],
                        'created_at'          => now(),
                    ]);
                } catch (\Throwable $evErr) {
                    Log::warning('Could not create PickingSyncEvent: ' . $evErr->getMessage());
                }

                $readyOrders[] = [
                    'id'                => $bOrder->id,
                    'order_code'        => $bOrder->order_code,
                    'parent_order_code' => $bOrder->parent_order_code,
                    'customer_name'     => $bOrder->customer_name,
                    'warehouse'         => $bOrder->warehouse,
                    'total_amount'      => (float)$bOrder->total_amount,
                ];
            }
        }

        return $readyOrders;
    }
}
