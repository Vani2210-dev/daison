<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ConsignmentOrder;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryAvailabilityService
{
    const CACHE_KEY_POS_CATALOG = 'orders_products_catalog_v1';
    const CACHE_TTL_SECONDS = 900; // 15 phút

    /**
     * Lấy bản đồ số lượng hàng đang tạm giữ (Reserved) theo Kho và theo Sản phẩm.
     * 
     * Hàng tạm giữ bao gồm:
     * 1. Đơn bán hàng (Order) chưa trừ kho (stock_deducted = false) và không thuộc diện 'Hủy' / 'Đã hủy'.
     * 2. Đơn hàng ôm (ConsignmentOrder) chưa trừ kho (stock_deducted = false) và không thuộc diện 'rejected' / 'cancelled'.
     * 
     * @param int|null $excludeOrderId Bỏ qua đơn hàng cụ thể (dùng khi đang chỉnh sửa đơn hàng cũ)
     * @return array [
     *    'by_warehouse'      => [warehouse_id => [product_id => reserved_qty]],
     *    'total_by_product'  => [product_id => total_reserved_qty],
     *    'order_details'     => [warehouse_id => [product_id => [ ['code' => ..., 'customer' => ..., 'qty' => ..., 'status' => ...] ]]]
     * ]
     */
    public static function getReservedQuantitiesMap(?int $excludeOrderId = null): array
    {
        $warehouses = Warehouse::all(['id', 'name', 'code']);
        $whNameToId = [];
        foreach ($warehouses as $wh) {
            $whNameToId[mb_strtolower(trim($wh->name))] = $wh->id;
            $whNameToId[mb_strtolower(trim($wh->code))] = $wh->id;
        }

        $defaultWh = Warehouse::where('name', 'like', '%Mê Linh%')->first() ?: $warehouses->first();
        $defaultWhId = $defaultWh?->id ?? 1;

        $products = Product::all(['id', 'sku', 'unit', 'conversion_unit', 'conversion_rate'])->keyBy('id');

        $byWarehouse = [];
        $totalByProduct = [];
        $orderDetails = [];

        // 1. Quét các đơn bán hàng thông thường (Order)
        $ordersQuery = Order::with(['items' => function($q) {
                $q->select('id', 'order_id', 'product_id', 'sku', 'name', 'unit', 'quantity', 'gift_quantity');
            }])
            ->where('stock_deducted', false)
            ->whereNotIn('status', ['Hủy', 'Đã hủy', 'Đã huỷ', 'Đã giao hàng', 'Hoàn tất']);

        if ($excludeOrderId) {
            $ordersQuery->where('id', '!=', $excludeOrderId);
        }

        $activeOrders = $ordersQuery->get(['id', 'order_code', 'customer_name', 'warehouse', 'status']);

        foreach ($activeOrders as $order) {
            $rawWh = mb_strtolower(trim($order->warehouse ?? ''));
            $resolvedWhId = $whNameToId[$rawWh] ?? null;

            if (!$resolvedWhId) {
                // Thử match gần đúng tên kho
                foreach ($whNameToId as $nameKey => $wId) {
                    if (str_contains($rawWh, $nameKey) || str_contains($nameKey, $rawWh)) {
                        $resolvedWhId = $wId;
                        break;
                    }
                }
            }
            if (!$resolvedWhId) {
                $resolvedWhId = $defaultWhId;
            }

            foreach ($order->items as $item) {
                $pId = $item->product_id;
                if (!$pId && $item->sku) {
                    $matchedProd = Product::where('sku', $item->sku)->first(['id']);
                    $pId = $matchedProd?->id;
                }
                if (!$pId) continue;

                $reqQty = (float)($item->quantity ?? 0) + (float)($item->gift_quantity ?? 0);
                if ($reqQty <= 0) continue;

                $prod = $products->get($pId);
                $baseQty = $reqQty;
                if ($prod && !empty($prod->conversion_unit) && (float)$prod->conversion_rate > 0) {
                    $itemUnit = mb_strtolower(trim($item->unit ?? ''));
                    $convUnit = mb_strtolower(trim($prod->conversion_unit));
                    $baseUnit = mb_strtolower(trim($prod->unit ?? ''));
                    if ($itemUnit === $convUnit && $convUnit !== $baseUnit) {
                        $baseQty = $reqQty / (float)$prod->conversion_rate;
                    }
                }

                $byWarehouse[$resolvedWhId][$pId] = ($byWarehouse[$resolvedWhId][$pId] ?? 0) + $baseQty;
                $totalByProduct[$pId] = ($totalByProduct[$pId] ?? 0) + $baseQty;

                $orderDetails[$resolvedWhId][$pId][] = [
                    'order_id'      => $order->id,
                    'order_code'    => $order->order_code,
                    'customer_name' => $order->customer_name,
                    'status'        => $order->status,
                    'quantity'      => round($reqQty, 2),
                    'unit'          => $item->unit ?: ($prod?->unit ?? 'cái'),
                    'base_quantity' => round($baseQty, 2),
                ];
            }
        }

        // 2. Quét các đơn xuất hàng ôm (ConsignmentOrder)
        $consignmentQuery = ConsignmentOrder::with(['items' => function($q) {
                $q->select('id', 'consignment_order_id', 'product_id', 'sku', 'name', 'unit', 'quantity');
            }])
            ->where('stock_deducted', false)
            ->whereNotIn('status', ['rejected', 'cancelled', 'Hủy', 'Đã hủy', 'Đã huỷ']);

        $activeConsignments = $consignmentQuery->get(['id', 'code', 'staff_name', 'warehouse_id', 'status']);

        foreach ($activeConsignments as $cOrder) {
            $resolvedWhId = $cOrder->warehouse_id ?: $defaultWhId;

            foreach ($cOrder->items as $item) {
                $pId = $item->product_id;
                if (!$pId && $item->sku) {
                    $matchedProd = Product::where('sku', $item->sku)->first(['id']);
                    $pId = $matchedProd?->id;
                }
                if (!$pId) continue;

                $reqQty = (float)($item->quantity ?? 0);
                if ($reqQty <= 0) continue;

                $prod = $products->get($pId);
                $baseQty = $reqQty;
                if ($prod && !empty($prod->conversion_unit) && (float)$prod->conversion_rate > 0) {
                    $itemUnit = mb_strtolower(trim($item->unit ?? ''));
                    $convUnit = mb_strtolower(trim($prod->conversion_unit));
                    $baseUnit = mb_strtolower(trim($prod->unit ?? ''));
                    if ($itemUnit === $convUnit && $convUnit !== $baseUnit) {
                        $baseQty = $reqQty / (float)$prod->conversion_rate;
                    }
                }

                $byWarehouse[$resolvedWhId][$pId] = ($byWarehouse[$resolvedWhId][$pId] ?? 0) + $baseQty;
                $totalByProduct[$pId] = ($totalByProduct[$pId] ?? 0) + $baseQty;

                $orderDetails[$resolvedWhId][$pId][] = [
                    'order_id'      => $cOrder->id,
                    'order_code'    => $cOrder->code,
                    'customer_name' => 'Xuất ôm: ' . ($cOrder->staff_name ?? 'Nhân viên'),
                    'status'        => $cOrder->status,
                    'quantity'      => round($reqQty, 2),
                    'unit'          => $item->unit ?: ($prod?->unit ?? 'cái'),
                    'base_quantity' => round($baseQty, 2),
                ];
            }
        }

        return [
            'by_warehouse'     => $byWarehouse,
            'total_by_product' => $totalByProduct,
            'order_details'    => $orderDetails,
        ];
    }

    /**
     * Lấy danh mục sản phẩm phục vụ POS / Lên đơn kèm chi tiết Kho Khả Dụng (On-hand, Reserved, Available)
     * 
     * @param int|null $excludeOrderId Bỏ qua đơn hàng cụ thể (dùng khi đang chỉnh sửa đơn hàng cũ)
     * @return array
     */
    public static function getPosCatalogWithAvailability(?int $excludeOrderId = null): array
    {
        // Khi không có excludeOrderId, sử dụng Cache để tối ưu hiệu năng
        if (!$excludeOrderId) {
            return Cache::remember(self::CACHE_KEY_POS_CATALOG, self::CACHE_TTL_SECONDS, function () {
                return self::buildPosCatalog();
            });
        }

        return self::buildPosCatalog($excludeOrderId);
    }

    /**
     * Xóa cache danh mục sản phẩm khi có thay đổi đơn hàng / nhập xuất kho
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_POS_CATALOG);
    }

    /**
     * Xây dựng dữ liệu danh mục sản phẩm kèm tồn kho khả dụng
     */
    private static function buildPosCatalog(?int $excludeOrderId = null): array
    {
        $warehouses = Warehouse::where('is_active', true)->get(['id', 'name', 'code'])->keyBy('id');
        $stocks = WarehouseStock::select('product_id', 'warehouse_id', 'stock_quantity', 'reserved_quantity')->get();

        $reservedData = self::getReservedQuantitiesMap($excludeOrderId);
        $reservedByWh = $reservedData['by_warehouse'];
        $reservedByProd = $reservedData['total_by_product'];
        $orderDetails = $reservedData['order_details'];

        // Nếu đơn hàng này đã được trừ kho trước đó (stock_deducted = true),
        // thì số lượng trong warehouse_stocks đã bị giảm đi phần hàng của đơn này rồi.
        // Đối với chính đơn này, cần cộng hoàn lại lượng đã trừ để không bị tính trùng (báo thiếu ảo) khi xem/sửa đơn!
        $deductedDeltasByWh = [];
        $deductedDeltasByProd = [];
        if ($excludeOrderId) {
            $exOrder = Order::with('items')->find($excludeOrderId);
            if ($exOrder && $exOrder->stock_deducted) {
                $targetWh = Warehouse::where('name', $exOrder->warehouse)->orWhere('code', $exOrder->warehouse)->first()
                    ?: Warehouse::where('name', 'like', '%Mê Linh%')->first();
                $targetWhId = $targetWh?->id;

                foreach ($exOrder->items as $it) {
                    $pId = $it->product_id;
                    if (!$pId && $it->sku) {
                        $pObj = Product::where('sku', $it->sku)->first();
                        if ($pObj) $pId = $pObj->id;
                    }
                    if (!$pId) continue;

                    $qty = (float)$it->quantity + (float)($it->gift_quantity ?? 0);
                    if ($qty <= 0) continue;

                    $pObj = Product::find($pId);
                    if ($pObj && $pObj->hasConversionUnit() && (float)$pObj->conversion_rate > 0) {
                        $itemUnit = mb_strtolower(trim($it->unit ?? ''));
                        $convUnit = mb_strtolower(trim($pObj->conversion_unit ?? ''));
                        $baseUnit = mb_strtolower(trim($pObj->unit ?? ''));
                        if ($itemUnit === $convUnit && $convUnit !== $baseUnit) {
                            $qty = $qty / (float)$pObj->conversion_rate;
                        }
                    }

                    if ($targetWhId) {
                        $deductedDeltasByWh[$targetWhId][$pId] = ($deductedDeltasByWh[$targetWhId][$pId] ?? 0) + $qty;
                    }
                    $deductedDeltasByProd[$pId] = ($deductedDeltasByProd[$pId] ?? 0) + $qty;
                }
            }
        }

        $stockMap = [];
        foreach ($stocks as $s) {
            if (!isset($warehouses[$s->warehouse_id])) continue;
            $wh = $warehouses[$s->warehouse_id];

            $restoredDeduction = (float)($deductedDeltasByWh[$s->warehouse_id][$s->product_id] ?? 0);
            $onHand = (float) $s->stock_quantity + $restoredDeduction;
            // Ưu tiên số lượng tính toán động từ các đơn hàng thực tế đang mở
            $reserved = (float) ($reservedByWh[$s->warehouse_id][$s->product_id] ?? (float)$s->reserved_quantity);
            $available = max(0, $onHand - $reserved);

            $detailList = $orderDetails[$s->warehouse_id][$s->product_id] ?? [];

            $info = [
                'on_hand'      => round($onHand, 2),
                'reserved'     => round($reserved, 2),
                'available'    => round($available, 2),
                'holding_orders' => array_slice($detailList, 0, 5), // Top 5 đơn đang giữ
            ];

            // Lưu theo Tên kho, Mã kho, và ID kho
            $stockMap[$s->product_id][$wh->name] = $info;
            $stockMap[$s->product_id][$wh->code] = $info;
            $stockMap[$s->product_id][$wh->id]   = $info;
        }

        return Product::where('is_active', true)
            ->select('id', 'sku', 'name', 'unit', 'conversion_unit', 'conversion_rate', 'conversion_price', 'cost_price', 'price', 'price_tiers', 'vat_rate', 'stock_quantity', 'reserved_quantity')
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($stockMap, $reservedByProd, $warehouses, $deductedDeltasByProd) {
                $totalOnHand = (float)$p->stock_quantity + (float)($deductedDeltasByProd[$p->id] ?? 0);
                $totalReserved = (float)($reservedByProd[$p->id] ?? (float)$p->reserved_quantity);
                $totalAvailable = max(0, $totalOnHand - $totalReserved);

                $pStockMap = $stockMap[$p->id] ?? [];

                // Đảm bảo mọi kho đang hoạt động đều có record đầy đủ ngay cả khi chưa có dòng trong warehouse_stocks
                foreach ($warehouses as $wh) {
                    if (!isset($pStockMap[$wh->name])) {
                        $pStockMap[$wh->name] = [
                            'on_hand'        => 0,
                            'reserved'       => 0,
                            'available'      => 0,
                            'holding_orders' => [],
                        ];
                        $pStockMap[$wh->code] = $pStockMap[$wh->name];
                        $pStockMap[$wh->id]   = $pStockMap[$wh->name];
                    }
                }

                return [
                    'id'               => $p->id,
                    'sku'              => $p->sku,
                    'name'             => $p->name,
                    'unit'             => $p->unit,
                    'conversion_unit'  => $p->conversion_unit,
                    'conversion_rate'  => (float)$p->conversion_rate,
                    'conversion_price' => (float)$p->conversion_price,
                    'cost_price'       => (float)$p->cost_price,
                    'price'            => (float)$p->price,
                    'price_tiers'      => $p->price_tiers,
                    'vat_rate'         => (float)$p->vat_rate,
                    // Tồn tổng toàn công ty
                    'stock_quantity'   => round($totalOnHand, 2),
                    'on_hand_stock'    => round($totalOnHand, 2),
                    'reserved_stock'   => round($totalReserved, 2),
                    'available_stock'  => round($totalAvailable, 2),
                    // Tồn chi tiết theo từng kho
                    'warehouse_stocks' => $pStockMap,
                ];
            })
            ->all();
    }

    /**
     * Tự động đồng bộ và ghi lại số lượng reserved_quantity chuẩn vào Database 
     * (bảng warehouse_stocks và bảng products) để đảm bảo toàn vẹn dữ liệu
     */
    public static function recalculateAndSyncDatabase(): array
    {
        $reservedData = self::getReservedQuantitiesMap();
        $byWh = $reservedData['by_warehouse'];
        $byProd = $reservedData['total_by_product'];

        DB::beginTransaction();
        try {
            // Reset toàn bộ reserved_quantity về 0 trước
            WarehouseStock::query()->update(['reserved_quantity' => 0]);
            Product::query()->update(['reserved_quantity' => 0]);

            // Cập nhật từng kho
            foreach ($byWh as $whId => $prodMap) {
                foreach ($prodMap as $pId => $qty) {
                    WarehouseStock::where('warehouse_id', $whId)
                        ->where('product_id', $pId)
                        ->update(['reserved_quantity' => round($qty, 2)]);
                }
            }

            // Cập nhật tổng sản phẩm
            foreach ($byProd as $pId => $qty) {
                Product::where('id', $pId)->update(['reserved_quantity' => round($qty, 2)]);
            }

            DB::commit();
            self::clearCache();

            return [
                'success' => true,
                'total_products_reserved' => count($byProd),
                'total_warehouses' => count($byWh),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Kiểm tra tồn kho khả dụng cho một danh sách các mặt hàng chuẩn bị đặt
     * 
     * @param string|int|null $targetWarehouse Tên, mã hoặc ID kho xuất
     * @param array $items Danh sách items [{ product_id, sku, quantity, gift_quantity, unit }]
     * @param int|null $excludeOrderId Bỏ qua đơn hàng đang cập nhật
     * @return array [
     *    'is_valid'         => bool,
     *    'has_over_available' => bool,
     *    'has_over_physical'  => bool,
     *    'shortages'        => [
     *        [
     *            'product_id' => ...,
     *            'sku'        => ...,
     *            'name'       => ...,
     *            'requested'  => ...,
     *            'available'  => ...,
     *            'on_hand'    => ...,
     *            'reserved'   => ...,
     *            'diff'       => ...,
     *            'is_physical_shortage' => bool
     *        ]
     *    ]
     * ]
     */
    public static function checkItemsAvailability($targetWarehouse, array $items, ?int $excludeOrderId = null): array
    {
        $catalog = collect(self::getPosCatalogWithAvailability($excludeOrderId))->keyBy('id');
        $targetWhKey = trim((string)$targetWarehouse);

        $shortages = [];
        $hasOverAvailable = false;
        $hasOverPhysical = false;

        foreach ($items as $it) {
            $pId = $it['product_id'] ?? ($it['id'] ?? null);
            if (!$pId && !empty($it['sku'])) {
                $p = $catalog->firstWhere('sku', $it['sku']);
                if ($p) $pId = $p['id'];
            }
            if (!$pId) continue;

            $prod = $catalog->get($pId);
            if (!$prod) continue;

            $reqQty = (float)($it['quantity'] ?? 0) + (float)($it['gift_quantity'] ?? 0);
            if ($reqQty <= 0) continue;

            // Quy đổi về đơn vị gốc nếu cần
            $baseReqQty = $reqQty;
            if (!empty($prod['conversion_unit']) && (float)$prod['conversion_rate'] > 0) {
                $itemUnit = mb_strtolower(trim($it['unit'] ?? ''));
                $convUnit = mb_strtolower(trim($prod['conversion_unit']));
                $baseUnit = mb_strtolower(trim($prod['unit'] ?? ''));
                if ($itemUnit === $convUnit && $convUnit !== $baseUnit) {
                    $baseReqQty = $reqQty / (float)$prod['conversion_rate'];
                }
            }

            // Tìm stock tại kho đích
            $whStocks = $prod['warehouse_stocks'] ?? [];
            $stockData = null;

            if (isset($whStocks[$targetWhKey])) {
                $stockData = $whStocks[$targetWhKey];
            } else {
                foreach ($whStocks as $k => $v) {
                    if (str_contains(mb_strtolower($k), mb_strtolower($targetWhKey)) || str_contains(mb_strtolower($targetWhKey), mb_strtolower($k))) {
                        $stockData = $v;
                        break;
                    }
                }
            }

            $onHand = is_array($stockData) ? (float)($stockData['on_hand'] ?? 0) : (float)$prod['stock_quantity'];
            $reserved = is_array($stockData) ? (float)($stockData['reserved'] ?? 0) : 0;
            $available = is_array($stockData) ? (float)($stockData['available'] ?? 0) : $onHand;

            // Chuyển lại về đơn vị mà nhân viên đang nhập trên form để hiển thị cảnh báo
            $rate = (!empty($prod['conversion_unit']) && (float)$prod['conversion_rate'] > 0 && mb_strtolower(trim($it['unit'] ?? '')) === mb_strtolower(trim($prod['conversion_unit'])))
                ? (float)$prod['conversion_rate']
                : 1;

            $displayAvailable = round($available * $rate, 2);
            $displayOnHand = round($onHand * $rate, 2);
            $displayReserved = round($reserved * $rate, 2);

            if ($reqQty > $displayAvailable) {
                $hasOverAvailable = true;
                $isPhysical = ($reqQty > $displayOnHand);
                if ($isPhysical) $hasOverPhysical = true;

                $shortages[] = [
                    'product_id'           => $pId,
                    'sku'                  => $prod['sku'],
                    'name'                 => $prod['name'],
                    'unit'                 => $it['unit'] ?? $prod['unit'],
                    'requested'            => $reqQty,
                    'available'            => $displayAvailable,
                    'on_hand'              => $displayOnHand,
                    'reserved'             => $displayReserved,
                    'diff'                 => round(max(0, $reqQty - $displayAvailable), 2),
                    'is_physical_shortage' => $isPhysical,
                    'holding_orders'       => is_array($stockData) ? ($stockData['holding_orders'] ?? []) : [],
                ];
            }
        }

        return [
            'is_valid'           => count($shortages) === 0,
            'has_over_available' => $hasOverAvailable,
            'has_over_physical'  => $hasOverPhysical,
            'shortages'          => $shortages,
        ];
    }
}
