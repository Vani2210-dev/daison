<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dealer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Bootstrap data for SPA initial load
     */
    public function getBootstrapData()
    {
        return Order::with(['items.product', 'dealer', 'salesRep', 'creator', 'salesAccountant', 'assignedDriver', 'warehouseStaff'])
            ->latest('updated_at')
            ->get()
            ->map(function ($o) {
                $items = $o->items->map(function ($item) {
                    $isGift = ($item->applied_tier_policy === 'Hàng tặng khuyến mãi' || str_starts_with($item->name, '[TẶNG KÈM]'));
                    $isPicked = (bool) ($item->is_picked ?? ($item->picked_qty >= $item->quantity && $item->quantity > 0));
                    $isChecked = (bool) ($item->is_checked ?? false);
                    return [
                        'id'                => (string) $item->id,
                        'itemId'            => (string) $item->product_id,
                        'sku'               => $item->sku,
                        'name'              => $item->name,
                        'unit'              => $item->unit,
                        'quantity'          => (float) $item->quantity,
                        'giftQuantity'      => (float) ($item->gift_quantity ?? 0),
                        'gift_quantity'     => (float) ($item->gift_quantity ?? 0),
                        'reqQty'            => (float) ($item->quantity + ($item->gift_quantity ?? 0)),
                        'pickedQty'         => (float) ($item->picked_qty ?? 0),
                        'itemStatus'        => $item->item_status ?? ($isPicked ? 'Đủ hàng' : 'Chờ bốc'),
                        'assignedStaff'     => $item->assigned_staff ?? '',
                        'assignedStaffList' => $item->assigned_staff_list ?: ($item->assigned_staff ? [$item->assigned_staff] : []),
                        'shortageReason'    => $item->shortage_reason ?? '',
                        'isPicked'          => $isPicked,
                        'isChecked'         => $isChecked,
                        'checkedQty'        => (float) ($item->checked_qty ?? ($isChecked ? $item->quantity : 0)),
                        'checkedBy'         => $item->checked_by ?? '',
                        'checkedAt'         => $item->checked_at ?? '',
                        'checkNotes'        => $item->check_notes ?? '',
                        'unitPrice'         => (float) $item->unit_price,
                        'originalPrice'     => (float) $item->original_price,
                        'appliedTierPolicy' => $item->applied_tier_policy ?? '',
                        'amount'            => (float) $item->amount,
                        'isPromotionalGift' => $isGift,
                        'location'          => $item->product?->location,
                        'returnedQty'       => (float) ($item->returned_qty ?? 0),
                        'deliveredQty'      => $item->delivered_qty !== null ? (float) $item->delivered_qty : ((float) $item->quantity - (float) ($item->returned_qty ?? 0)),
                        'returnReason'      => $item->return_reason ?? null,
                    ];
                });

                $orderCreatorName = $o->order_creator ?? $o->creator?->name ?? $o->salesRep?->name ?? null;

                $isDebt = in_array($o->status, ['Giao sau', 'Đơn giao sau', 'Nợ hàng']) || ($o->order_type === 'backorder');
                $parentOrderCode = null;
                if (preg_match('/(?:Tách từ đơn gốc|Đơn gốc)\s*[:]?\s*([A-Za-z0-9\-_]+)/u', $o->notes ?? '', $matches)) {
                    $parentOrderCode = trim($matches[1]);
                }

                return [
                    'id'                    => (string) $o->id,
                    'orderCode'             => $o->order_code,
                    'dealerId'              => (string) ($o->dealer_id ?? ''),
                    'dealerName'            => $o->dealer?->name ?? $o->customer_name ?? null,
                    'dealerTier'            => $o->dealer?->tier ?? null,
                    'customerPhone'         => $o->customer_phone ?? $o->dealer?->phone ?? '',
                    'address'               => $o->customer_address ?? $o->dealer?->address ?? '',
                    'warehouse'             => $o->warehouse ?? null,
                    'salesRepName'          => $o->salesRep?->name ?? null,
                    'salesAccountantName'   => $o->salesAccountant?->name ?? null,
                    'orderCreator'          => $orderCreatorName,
                    'createdByName'         => $orderCreatorName,
                    'warehouseStaffName'    => $o->warehouseStaff?->name ?? null,
                    'assignedDriverId'      => $o->assigned_driver_id ? (string) $o->assigned_driver_id : null,
                    'assignedDriverName'    => $o->assignedDriver?->name ?? null,
                    'assignedVehiclePlate'  => $o->assigned_vehicle_plate ?: null,
                    'items'                 => $items,
                    'subtotal'              => (float) ($o->subtotal > 0 ? $o->subtotal : ($items->sum('amount') > 0 ? $items->sum('amount') : 0)),
                    'discountAmount'        => (float) $o->discount_amount,
                    'taxAmount'             => (float) $o->tax_amount,
                    'totalAmount'           => (float) ($o->total_amount > 0 ? $o->total_amount : max(0, ($o->subtotal > 0 ? $o->subtotal : $items->sum('amount')) - (float)$o->discount_amount + (float)$o->tax_amount)),
                    'status'                => $o->status,
                    'stockDeducted'         => (bool) ($o->stock_deducted ?? false),
                    'paymentStatus'         => $o->payment_status,
                    'paymentMethod'         => $o->payment_method,
                    'driverDebtAmount'      => (float) $o->driver_debt_amount,
                    'hasReturns'            => (bool) ($o->has_returns ?? false),
                    'totalReturnedAmount'   => (float) ($o->total_returned_amount ?? 0),
                    'returnSlipCode'        => $o->return_slip_code ?? null,
                    'settledWithAccountant' => (bool) $o->settled_with_accountant,
                    'orderType'             => $o->order_type,
                    'projectName'           => $o->project_name,
                    'contractNumber'        => $o->contract_number,
                    'notes'                 => $o->notes ?? '',
                    'isDebtOrder'           => $isDebt,
                    'parentOrderCode'       => $parentOrderCode,
                    'originalOrderId'       => $parentOrderCode,
                    'shortageReported'      => (bool) ($o->shortage_reported ?? false),
                    'shortageReportedAt'    => $o->shortage_reported_at,
                    'shortageReportedNote'  => $o->shortage_reported_note,
                    'failedDeliveryReason'  => $o->failed_delivery_reason,
                    'failedDeliveryNote'    => $o->failed_delivery_note,
                    'failedDeliveryAt'      => $o->failed_delivery_at,
                    'failedDeliveryBy'      => $o->failed_delivery_by,
                    'rescheduledDate'       => $o->rescheduled_date,
                    'createdAt'             => $o->created_at ? $o->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                    'updatedAt'             => $o->updated_at ? $o->updated_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * API: Get all orders for SPA refresh
     */
    public function getOrders()
    {
        return response()->json(['orders' => $this->getBootstrapData()]);
    }

    /**
     * API: Update Order Status
     */
    public function updateOrderStatus(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }

        $orderId = $request->input('orderId') 
            ?? $request->input('order_id') 
            ?? $request->input('id') 
            ?? ($raw['orderId'] ?? null)
            ?? ($raw['order_id'] ?? null)
            ?? ($raw['id'] ?? null);

        $status = $request->input('status') 
            ?? $request->input('newStatus') 
            ?? ($raw['status'] ?? null)
            ?? ($raw['newStatus'] ?? null);

        if (!$orderId || !$status) {
            return response()->json(['success' => false, 'message' => 'Missing orderId or status'], 422);
        }

        $order = Order::where('id', $orderId)
            ->orWhere('order_code', $orderId)
            ->first();

        if ($order) {
            $oldStatus = $order->status;

            // Chặn chuyển sang "Đã bốc xong" nếu các nhóm chưa hoàn thành 100% việc bốc hàng
            if (in_array($status, ['Đã bốc xong', 'Kho đã xuất đủ'])) {
                $order->load('items.product.category');
                $progress = \App\Http\Controllers\PickingSyncController::evaluateOrderGroupProgress($order);
                if (!$progress['all_groups_picking_completed']) {
                    $pendingStr = implode(', ', $progress['pending_picking_group_names']);
                    return response()->json([
                        'success' => false,
                        'message' => "Không thể chuyển sang trạng thái 'Đã bốc xong'. Còn nhóm chưa hoàn thành: {$pendingStr}!"
                    ], 422);
                }
            }

            // Chặn chuyển sang "Đã kiểm hàng" nếu các nhóm chưa hoàn thành 100% việc kiểm hàng
            if (in_array($status, ['Đã kiểm hàng', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Đã kiểm xong'])) {
                $order->load('items.product.category');
                $progress = \App\Http\Controllers\PickingSyncController::evaluateOrderGroupProgress($order);
                if (!$progress['all_groups_qc_completed']) {
                    $pendingStr = implode(', ', $progress['pending_qc_group_names']);
                    return response()->json([
                        'success' => false,
                        'message' => "Không thể chuyển sang trạng thái 'Đã kiểm hàng'. Còn nhóm chưa hoàn thành kiểm tra (QC): {$pendingStr}!"
                    ], 422);
                }
            }

            $order->status = $status;
            $order->save();

            // Record into audit_logs table
            $authUser = auth()->user();
            $performerName = $authUser?->name ?? 'Người dùng';
            $performerId = $authUser?->id;

            $actionName = match($status) {
                'Chốt đơn', 'Đã duyệt' => 'Phê Duyệt & Chốt Đơn',
                'Soạn bốc', 'Soạn hàng', 'Đang bốc hàng', 'Kho đang soạn' => 'Soạn Bốc Kho',
                'Đã bốc xong', 'Kho đã xuất đủ' => 'Xác Nhận Đã Bốc Xong',
                'Kiểm hàng', 'Đang kiểm hàng' => 'Kiểm Hàng (QC)',
                'Đã kiểm hàng', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Đã kiểm xong' => 'Xác Nhận Đã Kiểm Hàng (Sẵn Sàng Xuất Xe)',
                'Đang vận chuyển', 'Đang giao hàng' => 'Xuất Bến & Giao Vận',
                'Đã giao', 'Giao thành công' => 'Hoàn Tất Giao Hàng & Thu Tiền',
                'Giao không thành công', 'Giao thất bại' => 'Giao Hàng Không Thành Công',
                'Hủy', 'Đã hủy' => 'Hủy Chứng Từ',
                default => 'Chuyển Trạng Thái Đơn Hàng'
            };

            if (in_array($status, ['Giao không thành công', 'Giao thất bại'])) {
                $order->failed_delivery_reason = $request->input('failedDeliveryReason') ?? ($raw['failedDeliveryReason'] ?? 'Khách không ở cửa hàng');
                $order->failed_delivery_note = $request->input('failedDeliveryNote') ?? ($raw['failedDeliveryNote'] ?? null);
                $order->failed_delivery_at = $request->input('failedDeliveryAt') ?? ($raw['failedDeliveryAt'] ?? now()->format('d/m/Y H:i'));
                $order->failed_delivery_by = $request->input('failedDeliveryBy') ?? ($raw['failedDeliveryBy'] ?? $performerName);
                $order->rescheduled_date = $request->input('rescheduledDate') ?? ($raw['rescheduledDate'] ?? null);
            } elseif (in_array($status, ['Đã giao', 'Giao thành công'])) {
                $order->failed_delivery_reason = null;
            }

            if ($request->has('payment_status') || $request->has('paymentStatus') || isset($raw['paymentStatus']) || isset($raw['payment_status'])) {
                $order->payment_status = $request->input('paymentStatus') ?? $request->input('payment_status') ?? ($raw['paymentStatus'] ?? $raw['payment_status']);
            }
            if ($request->has('picking_date') || $request->has('pickingDate') || isset($raw['pickingDate']) || isset($raw['picking_date'])) {
                $order->picking_date = $request->input('picking_date') ?? $request->input('pickingDate') ?? ($raw['pickingDate'] ?? $raw['picking_date']);
            }
            if ($request->has('driverDebtAmount') || $request->has('driver_debt_amount') || isset($raw['driverDebtAmount']) || isset($raw['driver_debt_amount'])) {
                $order->driver_debt_amount = (float)($request->input('driverDebtAmount') ?? $request->input('driver_debt_amount') ?? ($raw['driverDebtAmount'] ?? $raw['driver_debt_amount']));
            }
            if ($request->has('settledWithAccountant') || $request->has('settled_with_accountant') || isset($raw['settledWithAccountant']) || isset($raw['settled_with_accountant'])) {
                $order->settled_with_accountant = (bool)($request->input('settledWithAccountant') ?? $request->input('settled_with_accountant') ?? ($raw['settledWithAccountant'] ?? $raw['settled_with_accountant']));
            }

            $order->save();

            // Quản lý trừ / hoàn tồn kho tự động theo quy trình Chốt đơn & Hủy đơn
            $isFinalizedStatus = !in_array($status, ['Báo giá', 'Bản nháp', 'Chờ duyệt', 'Chờ kế toán duyệt', 'Hủy', 'Đã hủy', 'Đã huỷ']);
            $isCancelledStatus = in_array($status, ['Hủy', 'Đã hủy', 'Đã huỷ']);

            if ($isFinalizedStatus && !$order->stock_deducted) {
                $this->deductOrderStock($order);
            } elseif ($isCancelledStatus && $order->stock_deducted) {
                $this->restoreOrderStock($order);
            }

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => now()->format('Y-m-d H:i:s'),
                    'performer_name'       => $performerName,
                    'user_id'              => $performerId,
                    'action'               => $actionName,
                    'module'               => 'Đơn Hàng & Bán Hàng',
                    'target_document_code' => $order->order_code,
                    'field_name'           => 'Trạng thái chứng từ',
                    'old_value'            => $oldStatus,
                    'new_value'            => $status,
                    'notes'                => $request->input('notes', "Chuyển tiến trình từ [{$oldStatus}] sang [{$status}]"),
                ]);

                // Bắn sự kiện đồng bộ PickingSyncEvent khi trạng thái đơn đổi
                \App\Models\PickingSyncEvent::create([
                    'order_id'            => (string) $order->id,
                    'event_type'          => 'ORDER_STATUS_CHANGED',
                    'performer_name'      => $performerName,
                    'order_status'        => $order->status,
                    'extra_data'          => [
                        'orderId'      => (int) $order->id,
                        'orderCode'    => $order->order_code,
                        'oldStatus'    => $oldStatus,
                        'newStatus'    => $status,
                        'customerName' => $order->customer_name,
                        'warehouse'    => $order->warehouse,
                        'performer'    => $performerName,
                        'modifiedAt'   => now()->format('H:i:s d/m/Y'),
                    ],
                    'created_at'          => now(),
                ]);
            } catch (\Throwable $e) {
                // Ignore audit log / sync error
            }

            // Tự động tính toán lại công nợ đại lý
            $updatedDealerDebt = null;
            if ($order->dealer_id) {
                $dealer = Dealer::find($order->dealer_id);
                if ($dealer) {
                    $updatedDealerDebt = $dealer->recalculateCurrentDebt();
                }
            }

            $latestAuditLogs = \App\Models\AuditLog::where('target_document_code', $order->order_code)
                ->latest('id')
                ->get()
                ->map(function ($log) {
                    return [
                        'id'                 => (string) $log->id,
                        'timestamp'          => $log->timestamp ?: ($log->created_at ? $log->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s')),
                        'performerName'      => $log->performer_name,
                        'userId'             => (string) ($log->user_id ?? ''),
                        'action'             => $log->action,
                        'module'             => $log->module,
                        'targetDocumentCode' => $log->target_document_code,
                        'fieldName'          => $log->field_name,
                        'oldValue'           => $log->old_value,
                        'newValue'           => $log->new_value,
                        'notes'              => $log->notes,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'order' => [
                    'id'            => (string) $order->id,
                    'orderCode'     => $order->order_code,
                    'status'        => $order->status,
                    'stockDeducted' => (bool) $order->stock_deducted,
                ],
                'dealerId'   => $order->dealer_id ? (string) $order->dealer_id : null,
                'dealerDebt' => $updatedDealerDebt,
                'auditLogs'  => $latestAuditLogs,
            ]);
        }

        $newOrder = Order::create([
            'order_code'    => $orderId,
            'customer_name' => $request->input('customerName', 'Đại Lý Điện Nước Hoàn Mỹ'),
            'status'        => $status,
            'subtotal'      => 23650000,
            'total_amount'  => 23650000,
            'order_type'    => 'normal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo mới và cập nhật trạng thái đơn hàng',
            'order' => [
                'id'        => (string) $newOrder->id,
                'orderCode' => $newOrder->order_code,
                'status'    => $newOrder->status,
            ]
        ]);
    }

    /**
     * API: Save or Update Full Order with Items
     */
    public function saveOrder(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $orderId = $data['id'] ?? null;
        $orderCode = $data['orderCode'] ?? ($data['order_code'] ?? null);

        $order = null;
        if ($orderId) {
            $order = Order::find($orderId);
        }
        if (!$order && $orderCode) {
            $order = Order::where('order_code', $orderCode)->first();
        }

        $isNewOrder = ($order === null);
        $oldStatus = $order?->status;
        $oldTotal = (float) ($order?->total_amount ?? 0);
        $oldVehiclePlate = $order?->assigned_vehicle_plate;
        $oldPaymentStatus = $order?->payment_status;

        $dealerId = null;
        if (!empty($data['dealerId']) && is_numeric($data['dealerId'])) {
            $dealerId = (int)$data['dealerId'];
        } elseif (!empty($data['dealer_id']) && is_numeric($data['dealer_id'])) {
            $dealerId = (int)$data['dealer_id'];
        } elseif ($order && !empty($order->dealer_id)) {
            $dealerId = (int)$order->dealer_id;
        } elseif (!empty($data['dealerName'])) {
            $matchedDealer = Dealer::where('name', $data['dealerName'])->first();
            if ($matchedDealer) {
                $dealerId = $matchedDealer->id;
            }
        }

        $creatorName = $data['orderCreator'] ?? ($data['createdByName'] ?? null);
        $creatorId = null;
        if (!empty($data['creatorId']) && is_numeric($data['creatorId'])) {
            $creatorId = (int)$data['creatorId'];
        } elseif (!empty($data['createdById']) && is_numeric($data['createdById'])) {
            $creatorId = (int)$data['createdById'];
        } elseif ($order && !empty($order->creator_id)) {
            $creatorId = (int)$order->creator_id;
        } elseif (auth()->check()) {
            $creatorId = auth()->id();
        }
        if (!$creatorName && auth()->check()) {
            $creatorName = auth()->user()->name;
        }

        // Tự động gán Đại lý nếu Người tạo đơn hoặc Sales phụ trách là Sale Đại Lý Phụ Thuộc
        $activeUser = $creatorId ? \App\Models\User::find($creatorId) : auth()->user();
        $salesRepId = $data['salesRepId'] ?? ($data['sales_rep_id'] ?? null);
        $salesRepUser = ($salesRepId && is_numeric($salesRepId)) ? \App\Models\User::find($salesRepId) : null;

        $dependentSales = null;
        if ($activeUser && ($activeUser->staff_type === 'dealer_sales' || $activeUser->dealer_id)) {
            $dependentSales = $activeUser;
        } elseif ($salesRepUser && ($salesRepUser->staff_type === 'dealer_sales' || $salesRepUser->dealer_id)) {
            $dependentSales = $salesRepUser;
        }

        if ($dependentSales && $dependentSales->dealer_id) {
            $dealerId = $dependentSales->dealer_id;
            $depDealer = \App\Models\Dealer::find($dealerId);
            if ($depDealer) {
                $data['dealerName'] = $depDealer->name;
                if (empty($data['customerPhone'])) $data['customerPhone'] = $depDealer->phone;
                if (empty($data['address'])) $data['address'] = $depDealer->address;
            }
        }

        $assignedDriverId = null;
        if (!empty($data['assignedDriverId']) && is_numeric($data['assignedDriverId'])) {
            $assignedDriverId = (int)$data['assignedDriverId'];
        } elseif (!empty($data['assigned_driver_id']) && is_numeric($data['assigned_driver_id'])) {
            $assignedDriverId = (int)$data['assigned_driver_id'];
        } elseif ($order && !empty($order->assigned_driver_id)) {
            $assignedDriverId = (int)$order->assigned_driver_id;
        }

        $vehiclePlate = !empty($data['assignedVehiclePlate']) 
            ? (string)$data['assignedVehiclePlate'] 
            : (!empty($data['assigned_vehicle_plate']) ? (string)$data['assigned_vehicle_plate'] : ($order?->assigned_vehicle_plate ?? null));

        // Ràng buộc kho cho Lái xe: Khi lái xe tạo đơn, bắt buộc chỉ được bán từ Kho Xe của họ
        $isDriverCreator = $activeUser && ($activeUser->staff_type === 'driver' || $activeUser->department === 'Logistics' || (method_exists($activeUser, 'hasRole') && ($activeUser->hasRole('Fleet') || $activeUser->hasRole('Driver'))));
        $warehouseName = $data['warehouse'] ?? ($data['warehouse_name'] ?? ($order?->warehouse ?? 'Kho Tổng Mê Linh (MQ)'));

        if ($isDriverCreator) {
            $assignedDriverId = $activeUser->id;
            $driverVeh = \App\Models\Vehicle::where('driver_id', $activeUser->id)->first();
            if (!$driverVeh) {
                $recentDisp = \App\Models\VanSaleDispatch::where('driver_id', $activeUser->id)->latest('id')->first();
                if ($recentDisp && $recentDisp->vehicle_plate) {
                    $driverVeh = \App\Models\Vehicle::where('plate_number', $recentDisp->vehicle_plate)->first();
                }
            }
            $vehiclePlate = $driverVeh?->plate_number ?? ($vehiclePlate ?: '29C-888.99');
            $warehouseName = 'Kho Xe ' . $vehiclePlate . ' (' . $activeUser->name . ')';
        } elseif (preg_match('/Kho Xe\s+([A-Z0-9\.\-]+)/ui', $warehouseName, $whMatches)) {
            $parsedPlate = trim($whMatches[1]);
            if (!$vehiclePlate) {
                $vehiclePlate = $parsedPlate;
            }
            if (!$assignedDriverId) {
                $vehObj = \App\Models\Vehicle::where('plate_number', $parsedPlate)->first();
                if ($vehObj && $vehObj->driver_id) {
                    $assignedDriverId = $vehObj->driver_id;
                }
            }
        }

        $items = $data['items'] ?? null;
        $calculatedItemsSum = 0;
        if (is_array($items) && count($items) > 0) {
            foreach ($items as $it) {
                $qty = (float) ($it['quantity'] ?? ($it['qty'] ?? 1));
                $uPrice = (float) ($it['unitPrice'] ?? ($it['unit_price'] ?? 0));
                $amt = (float) ($it['amount'] ?? ($it['lineTotal'] ?? ($qty * $uPrice)));
                $calculatedItemsSum += $amt;
            }
        } elseif ($order) {
            $calculatedItemsSum = (float) $order->items()->sum('amount');
        }

        $subtotal = isset($data['subtotal']) && (float)$data['subtotal'] > 0
            ? (float)$data['subtotal']
            : (isset($data['subtotalAmount']) && (float)$data['subtotalAmount'] > 0
                ? (float)$data['subtotalAmount']
                : ($calculatedItemsSum > 0 ? $calculatedItemsSum : (float)($order?->subtotal ?? 0)));

        $discount = isset($data['discountAmount'])
            ? (float)$data['discountAmount']
            : (float)($order?->discount_amount ?? 0);

        $tax = isset($data['taxAmount'])
            ? (float)$data['taxAmount']
            : (float)($order?->tax_amount ?? 0);

        $total = isset($data['totalAmount']) && (float)$data['totalAmount'] > 0
            ? (float)$data['totalAmount']
            : ($calculatedItemsSum > 0
                ? max(0, $calculatedItemsSum - $discount + $tax)
                : ((float)($order?->total_amount ?? 0) > 0
                    ? (float)$order->total_amount
                    : max(0, $subtotal - $discount + $tax)));

        $notesPayload = (string) ($data['notes'] ?? ($order?->notes ?? ''));
        $isBackOrder = (($data['orderType'] ?? ($data['order_type'] ?? '')) === 'backorder') 
            || str_contains($notesPayload, '[ĐƠN NỢ HÀNG');

        $defaultPrefix = $isBackOrder ? 'DH-NO-' : 'DH-';
        $resolvedOrderType = $isBackOrder ? 'backorder' : ($data['orderType'] ?? ($data['order_type'] ?? ($order?->order_type ?? 'normal')));

        $orderAttributes = [
            'order_code'             => $orderCode ?: ($order ? $order->order_code : $defaultPrefix . date('Y') . '-' . rand(1000, 9999)),
            'dealer_id'              => $dealerId,
            'creator_id'             => $creatorId,
            'order_creator'          => $creatorName ?: ($order?->order_creator ?? null),
            'customer_name'          => $data['dealerName'] ?? ($data['customer_name'] ?? ($order?->customer_name ?? 'Khách lẻ')),
            'customer_phone'         => $data['customerPhone'] ?? ($data['customer_phone'] ?? ($order?->customer_phone ?? '')),
            'customer_address'       => $data['address'] ?? ($data['customer_address'] ?? ($order?->customer_address ?? '')),
            'warehouse'              => $data['warehouse'] ?? ($data['warehouse_name'] ?? ($order?->warehouse ?? 'Kho Tổng Mê Linh (MQ)')),
            'delivery_date'          => array_key_exists('deliveryDate', $data) 
                ? ($data['deliveryDate'] ?: null) 
                : (array_key_exists('delivery_date', $data) 
                    ? ($data['delivery_date'] ?: null) 
                    : ($order?->delivery_date ?? null)),
            'picking_date'           => array_key_exists('pickingDate', $data) 
                ? ($data['pickingDate'] ?: null) 
                : (array_key_exists('picking_date', $data) 
                    ? ($data['picking_date'] ?: null) 
                    : ($order?->picking_date ?? null)),
            'order_type'             => $resolvedOrderType,
            'project_name'           => $data['projectName'] ?? ($data['project_name'] ?? null),
            'contract_number'        => $data['contractNumber'] ?? ($data['contract_number'] ?? null),
            'assigned_driver_id'     => $assignedDriverId,
            'assigned_vehicle_plate' => $vehiclePlate,
            'driver_debt_amount'     => (float) ($data['driverDebtAmount'] ?? ($data['driver_debt_amount'] ?? ($order?->driver_debt_amount ?? 0))),
            'has_returns'            => !empty($data['hasReturns']) || !empty($data['has_returns']),
            'total_returned_amount'  => (float) ($data['totalReturnedAmount'] ?? ($data['total_returned_amount'] ?? 0)),
            'return_slip_code'       => $data['returnSlipCode'] ?? ($data['return_slip_code'] ?? null),
            'status'                 => (function() use ($data, $order, $isBackOrder) {
                if (!empty($data['status'])) {
                    if ($isBackOrder && in_array($data['status'], ['Chốt đơn', 'Báo giá', 'Giao sau', 'Nợ hàng'])) {
                        return 'Giao sau';
                    }
                    return $data['status'];
                }
                if ($isBackOrder) return 'Giao sau';
                return $order?->status ?? 'Báo giá';
            })(),
            'payment_status'          => $data['paymentStatus'] ?? ($data['payment_status'] ?? 'Chưa thanh toán'),
            'payment_method'          => $data['paymentMethod'] ?? ($data['payment_method'] ?? 'Ghi nợ đại lý'),
            'settled_with_accountant' => isset($data['settledWithAccountant']) 
                ? (bool)$data['settledWithAccountant'] 
                : (isset($data['settled_with_accountant']) 
                    ? (bool)$data['settled_with_accountant'] 
                    : ($order?->settled_with_accountant ?? false)),
            'subtotal'               => $subtotal,
            'discount_amount'        => $discount,
            'tax_amount'             => $tax,
            'total_amount'           => $total,
            'shortage_reported'      => isset($data['shortageReported']) ? (bool)$data['shortageReported'] : (isset($data['shortage_reported']) ? (bool)$data['shortage_reported'] : ($order?->shortage_reported ?? false)),
            'shortage_reported_at'   => $data['shortageReportedAt'] ?? ($data['shortage_reported_at'] ?? ($order?->shortage_reported_at ?? null)),
            'shortage_reported_note' => $data['shortageReportedNote'] ?? ($data['shortage_reported_note'] ?? ($order?->shortage_reported_note ?? null)),
            'failed_delivery_reason' => $data['failedDeliveryReason'] ?? ($data['failed_delivery_reason'] ?? ($order?->failed_delivery_reason ?? null)),
            'failed_delivery_note'   => $data['failedDeliveryNote'] ?? ($data['failed_delivery_note'] ?? ($order?->failed_delivery_note ?? null)),
            'failed_delivery_at'     => $data['failedDeliveryAt'] ?? ($data['failed_delivery_at'] ?? ($order?->failed_delivery_at ?? null)),
            'failed_delivery_by'     => $data['failedDeliveryBy'] ?? ($data['failed_delivery_by'] ?? ($order?->failed_delivery_by ?? null)),
            'rescheduled_date'       => $data['rescheduledDate'] ?? ($data['rescheduled_date'] ?? ($order?->rescheduled_date ?? null)),
            'notes'                  => (function() use ($data) {
                $n = $data['notes'] ?? '';
                if (!empty($data['parentOrderCode']) && !str_contains($n, $data['parentOrderCode'])) {
                    $n = "[ĐƠN NỢ HÀNG] Tách từ đơn gốc " . $data['parentOrderCode'] . " do kho thiếu hàng.\n" . $n;
                }
                return $n;
            })(),
        ];

        // Cache existing items if updating so we don't accidentally wipe picking status if not provided
        $existingItemsMap = $order ? $order->items->keyBy(function($item) {
            return $item->sku ?: (string)$item->id;
        }) : collect();

        // Phát hiện các mặt hàng và số lượng bị thay đổi / bổ sung
        $detectedItemChanges = [];
        $incomingItemKeys = [];
        $hasIncreasedPickingQty = false;

        $items = $data['items'] ?? null;
        if (is_array($items)) {
            foreach ($items as $it) {
                $itSku = $it['sku'] ?? ($it['product_sku'] ?? '');
                $itName = $it['name'] ?? ($it['product_name'] ?? '');
                $itUnit = $it['unit'] ?? 'cây';
                $qty = (float) ($it['quantity'] ?? ($it['qty'] ?? 1));
                $giftQty = (float) ($it['gift_quantity'] ?? ($it['giftQty'] ?? 0));
                $newTotalQty = $qty + $giftQty;
                $lookupKey = $itSku ?: (string)($it['id'] ?? '');
                if ($lookupKey) $incomingItemKeys[] = $lookupKey;

                $oldItem = $existingItemsMap->get($itSku) ?? (!empty($it['id']) ? $existingItemsMap->get((string)$it['id']) : null);
                if (!$isNewOrder && !$oldItem) {
                    $detectedItemChanges[] = [
                        'type'           => 'added',
                        'sku'            => $itSku ?: 'SP-MOI',
                        'name'           => $itName ?: 'Sản phẩm mới bổ sung',
                        'unit'           => $itUnit,
                        'old_qty'        => 0,
                        'new_qty'        => $newTotalQty,
                        'delta'          => +$newTotalQty,
                        'assigned_staff' => null,
                        'message'        => "Bổ sung mới: +{$newTotalQty} {$itUnit}",
                    ];
                    $hasIncreasedPickingQty = true;
                } elseif (!$isNewOrder && $oldItem) {
                    $oldTotalQty = (float)($oldItem->quantity + ($oldItem->gift_quantity ?? 0));
                    if (abs($oldTotalQty - $newTotalQty) > 0.001) {
                        $delta = $newTotalQty - $oldTotalQty;
                        if ($delta > 0) $hasIncreasedPickingQty = true;
                        $detectedItemChanges[] = [
                            'type'                => 'quantity_changed',
                            'sku'                 => $itSku ?: $oldItem->sku,
                            'name'                => $oldItem->name,
                            'unit'                => $oldItem->unit ?: $itUnit,
                            'old_qty'             => $oldTotalQty,
                            'new_qty'             => $newTotalQty,
                            'delta'               => $delta,
                            'assigned_staff'      => $oldItem->assigned_staff,
                            'assigned_staff_list' => $oldItem->assigned_staff_list,
                            'is_picked'           => $oldItem->is_picked,
                            'picked_qty'          => (float)$oldItem->picked_qty,
                            'message'             => ($delta > 0 ? "Tăng số lượng: +{$delta}" : "Giảm số lượng: {$delta}") . " {$oldItem->unit} (từ {$oldTotalQty} thành {$newTotalQty})",
                        ];
                    }
                }
            }

            if (!$isNewOrder) {
                foreach ($existingItemsMap as $oldKey => $oldItem) {
                    $oldSku = (string)$oldItem->sku;
                    $oldId = (string)$oldItem->id;
                    if (!in_array($oldSku, $incomingItemKeys) && !in_array($oldId, $incomingItemKeys)) {
                        $oldTotalQty = (float)($oldItem->quantity + ($oldItem->gift_quantity ?? 0));
                        $detectedItemChanges[] = [
                            'type'           => 'removed',
                            'sku'            => $oldItem->sku,
                            'name'           => $oldItem->name,
                            'unit'           => $oldItem->unit,
                            'old_qty'        => $oldTotalQty,
                            'new_qty'        => 0,
                            'delta'          => -$oldTotalQty,
                            'assigned_staff' => $oldItem->assigned_staff,
                            'message'        => "Đã xóa sản phẩm khỏi đơn (trước đó {$oldTotalQty} {$oldItem->unit})",
                        ];
                    }
                }
            }
        }

        if ($order) {
            $order->update($orderAttributes);
        } else {
            $order = Order::create($orderAttributes);
        }

        // Sync items if provided
        $items = $data['items'] ?? null;
        if (is_array($items) && count($items) > 0) {
            $order->items()->delete();
            foreach ($items as $it) {
                $itName = $it['name'] ?? ($it['product_name'] ?? '');
                $itSku = $it['sku'] ?? ($it['product_sku'] ?? '');
                $prodId = null;
                if (!empty($it['itemId']) && is_numeric($it['itemId'])) {
                    $prodId = (int)$it['itemId'];
                } elseif (!empty($it['item_id']) && is_numeric($it['item_id'])) {
                    $prodId = (int)$it['item_id'];
                } elseif (!empty($it['product_id']) && is_numeric($it['product_id'])) {
                    $prodId = (int)$it['product_id'];
                }

                if ($prodId && !\App\Models\Product::where('id', $prodId)->exists()) {
                    $prodId = null;
                }

                if (!$prodId && ($itSku || $itName)) {
                    $prod = \App\Models\Product::where('sku', $itSku)->orWhere('name', $itName)->first();
                    if ($prod) $prodId = $prod->id;
                }

                if (!$prodId) {
                    $prodId = \App\Models\Product::first()?->id ?? 1;
                }

                $qty = (float) ($it['quantity'] ?? ($it['qty'] ?? 1));
                $giftQty = (float) ($it['gift_quantity'] ?? ($it['giftQty'] ?? 0));
                $retQty = (float) ($it['returnedQty'] ?? ($it['returned_qty'] ?? 0));
                $delivQty = isset($it['deliveredQty']) ? (float) $it['deliveredQty'] : (isset($it['delivered_qty']) ? (float) $it['delivered_qty'] : max(0, $qty - $retQty));
                $uPrice = (float) ($it['unitPrice'] ?? ($it['unit_price'] ?? 0));
                $amt = (float) ($it['amount'] ?? ($it['lineTotal'] ?? ($qty * $uPrice)));

                // Resolve picking & QC fields
                $oldItem = $existingItemsMap->get($itSku) ?? $existingItemsMap->get($it['id'] ?? '');

                $assignedStaff = $it['assignedStaff'] ?? ($it['assigned_staff'] ?? ($oldItem?->assigned_staff ?? null));
                $assignedList = $it['assignedStaffList'] ?? ($it['assigned_staff_list'] ?? ($oldItem?->assigned_staff_list ?? ($assignedStaff ? [$assignedStaff] : [])));
                $pickedQty = isset($it['pickedQty']) 
                    ? (float)$it['pickedQty'] 
                    : (isset($it['picked_qty']) 
                        ? (float)$it['picked_qty'] 
                        : (float)($oldItem?->picked_qty ?? 0));

                $reqTotal = $qty + $giftQty;
                // Nếu số lượng cần bốc tăng lên và số lượng đã bốc < số lượng cần, đưa trạng thái về Chờ bốc/Đang bốc
                if ($reqTotal > 0 && $pickedQty < $reqTotal) {
                    $isPicked = false;
                    $itemStatus = ($pickedQty > 0) ? 'Đang bốc' : 'Chờ bốc';
                    $shortageReason = $it['shortageReason'] ?? ($it['shortage_reason'] ?? ($oldItem?->shortage_reason ?? null));
                } else if ($reqTotal > 0 && $pickedQty >= $reqTotal) {
                    $isPicked = true;
                    $itemStatus = 'Đủ hàng';
                    $shortageReason = null; // Đã bốc đủ (hoặc đã cắt đơn cho vừa đủ), tự động xóa cờ báo thiếu
                } else {
                    $isPicked = !empty($it['isPicked']) || !empty($it['is_picked']) || (bool)($oldItem?->is_picked ?? false);
                    $itemStatus = $it['itemStatus'] ?? ($it['item_status'] ?? ($oldItem?->item_status ?? ($isPicked ? 'Đủ hàng' : 'Chờ bốc')));
                    $shortageReason = $it['shortageReason'] ?? ($it['shortage_reason'] ?? ($oldItem?->shortage_reason ?? null));
                }

                $isChecked = !empty($it['isChecked']) || !empty($it['is_checked']) || (bool)($oldItem?->is_checked ?? false);
                $checkedQty = isset($it['checkedQty']) 
                    ? (float)$it['checkedQty'] 
                    : (isset($it['checked_qty']) 
                        ? (float)$it['checked_qty'] 
                        : (float)($oldItem?->checked_qty ?? ($isChecked ? $reqTotal : 0)));
                if ($reqTotal > 0 && $checkedQty < $reqTotal) {
                    $isChecked = false;
                }
                $checkedBy = $it['checkedBy'] ?? ($it['checked_by'] ?? ($oldItem?->checked_by ?? null));
                $checkedAt = $it['checkedAt'] ?? ($it['checked_at'] ?? ($oldItem?->checked_at ?? null));
                $checkNotes = $it['checkNotes'] ?? ($it['check_notes'] ?? ($oldItem?->check_notes ?? null));

                OrderItem::create([
                    'order_id'            => $order->id,
                    'product_id'          => $prodId ?: 1,
                    'sku'                 => $itSku ?: 'SKU-PVC',
                    'name'                => $itName ?: 'Vật tư PVC',
                    'unit'                => $it['unit'] ?? 'cây',
                    'quantity'            => $qty,
                    'gift_quantity'       => $giftQty,
                    'picked_qty'          => $pickedQty,
                    'item_status'         => $itemStatus,
                    'assigned_staff'      => $assignedStaff,
                    'assigned_staff_list' => is_array($assignedList) ? $assignedList : ($assignedList ? [$assignedList] : []),
                    'shortage_reason'     => $shortageReason,
                    'is_picked'           => $isPicked,
                    'is_checked'          => $isChecked,
                    'checked_qty'         => $checkedQty,
                    'checked_by'          => $checkedBy,
                    'checked_at'          => $checkedAt,
                    'check_notes'         => $checkNotes,
                    'returned_qty'        => $retQty,
                    'delivered_qty'       => $delivQty,
                    'return_reason'       => $it['returnReason'] ?? ($it['return_reason'] ?? null),
                    'cost_price'          => (float) ($it['costPrice'] ?? ($it['cost_price'] ?? ($uPrice * 0.8))),
                    'unit_price'          => $uPrice,
                    'original_price'      => (float) ($it['originalPrice'] ?? ($it['original_price'] ?? $uPrice)),
                    'applied_tier_policy' => $it['appliedTierPolicy'] ?? ($it['applied_tier_policy'] ?? 'Đại lý Cấp 1'),
                    'amount'              => $amt,
                ]);
            }
        }

        // Tự động kiểm tra trạng thái đơn hàng sau khi sửa/cắt đơn
        $order->load('items');
        $allOrderItems = $order->items;
        if ($allOrderItems->isNotEmpty()) {
            $hasAnyShortage = $allOrderItems->contains(function ($i) {
                $r = (float)($i->quantity + ($i->gift_quantity ?? 0));
                return $r > 0 && ($i->picked_qty < $r && ($i->item_status === 'Thiếu hàng' || !empty($i->shortage_reason)));
            });
            $allArePicked = $allOrderItems->every(function ($i) {
                $r = (float)($i->quantity + ($i->gift_quantity ?? 0));
                return $r > 0 && $i->picked_qty >= $r;
            });

            if (!$hasAnyShortage && $order->status === 'Kho báo thiếu hàng') {
                $order->status = $allArePicked ? 'Đã bốc xong' : 'Soạn hàng';
                $order->shortage_reported = false;
                $order->shortage_reported_at = null;
                $order->shortage_reported_note = null;
                $order->save();
            }
        }

        // Tự động chuyển đơn về "Soạn hàng" nếu phát sinh tăng số lượng cần bốc khi đơn đã hoàn thành trước đó
        if ($hasIncreasedPickingQty && in_array($order->status, ['Đã bốc xong', 'Đã kiểm hàng', 'Sẵn sàng xuất xe'])) {
            $order->status = 'Soạn hàng';
            $order->save();
        }

        // Phát sự kiện đồng bộ Realtime PickingSyncEvent
        try {
            $order->load('items');
            $assignedPickers = [];
            foreach ($order->items as $oIt) {
                if ($oIt->assigned_staff) $assignedPickers[] = $oIt->assigned_staff;
                if (is_array($oIt->assigned_staff_list)) {
                    foreach ($oIt->assigned_staff_list as $st) {
                        if ($st) $assignedPickers[] = $st;
                    }
                }
            }
            $assignedPickers = array_values(array_unique(array_filter($assignedPickers)));
            $supervisor = $order->order_creator ?: 'Admin Kho';
            $formattedItemsForEvent = $order->items->map(function($i) {
                return [
                    'id'                  => (int) $i->id,
                    'order_id'            => (int) $i->order_id,
                    'product_id'          => (int) $i->product_id,
                    'sku'                 => $i->sku,
                    'name'                => $i->name,
                    'unit'                => $i->unit,
                    'quantity'            => (float) $i->quantity,
                    'gift_quantity'       => (float) ($i->gift_quantity ?? 0),
                    'picked_qty'          => (float) $i->picked_qty,
                    'item_status'         => $i->item_status,
                    'assigned_staff'      => $i->assigned_staff,
                    'assigned_staff_list' => $i->assigned_staff_list,
                    'is_picked'           => (bool) $i->is_picked,
                    'is_checked'          => (bool) $i->is_checked,
                    'checked_qty'         => (float) $i->checked_qty,
                    'shortage_reason'     => $i->shortage_reason,
                ];
            })->toArray();

            if (!$isNewOrder && count($detectedItemChanges) > 0) {
                \App\Models\PickingSyncEvent::create([
                    'order_id'            => (string) $order->id,
                    'event_type'          => 'ORDER_ITEMS_MODIFIED',
                    'performer_name'      => $creatorName ?: 'Admin KBTech',
                    'assigned_staff'      => implode(', ', $assignedPickers),
                    'assigned_staff_list' => $assignedPickers,
                    'order_status'        => $order->status,
                    'extra_data'          => [
                        'orderId'            => (int) $order->id,
                        'orderCode'          => $order->order_code,
                        'customerName'       => $order->customer_name,
                        'pickingDate'        => $order->picking_date,
                        'warehouse'          => $order->warehouse,
                        'status'             => $order->status,
                        'changes'            => $detectedItemChanges,
                        'items'              => $formattedItemsForEvent,
                        'assignedPickers'    => $assignedPickers,
                        'orderCreator'       => $order->order_creator,
                        'supervisor'         => $supervisor,
                        'performer'          => $creatorName ?: 'Admin KBTech',
                        'modifiedAt'         => now()->format('H:i:s d/m/Y'),
                    ],
                    'created_at'          => now(),
                ]);
            } elseif (!$isNewOrder) {
                \App\Models\PickingSyncEvent::create([
                    'order_id'            => (string) $order->id,
                    'event_type'          => 'ORDER_UPDATED',
                    'performer_name'      => $creatorName ?: 'Admin KBTech',
                    'assigned_staff'      => implode(', ', $assignedPickers),
                    'assigned_staff_list' => $assignedPickers,
                    'order_status'        => $order->status,
                    'extra_data'          => [
                        'orderId'            => (int) $order->id,
                        'orderCode'          => $order->order_code,
                        'customerName'       => $order->customer_name,
                        'pickingDate'        => $order->picking_date,
                        'warehouse'          => $order->warehouse,
                        'status'             => $order->status,
                        'changes'            => [[
                            'type'    => 'order_saved',
                            'message' => 'Lưu cập nhật thông tin đơn hàng',
                        ]],
                        'items'              => $formattedItemsForEvent,
                        'assignedPickers'    => $assignedPickers,
                        'orderCreator'       => $order->order_creator,
                        'supervisor'         => $supervisor,
                        'performer'          => $creatorName ?: 'Admin KBTech',
                        'modifiedAt'         => now()->format('H:i:s d/m/Y'),
                    ],
                    'created_at'          => now(),
                ]);
            } elseif ($isNewOrder) {
                \App\Models\PickingSyncEvent::create([
                    'order_id'            => (string) $order->id,
                    'event_type'          => 'ORDER_CREATED',
                    'performer_name'      => $creatorName ?: 'Admin KBTech',
                    'assigned_staff'      => implode(', ', $assignedPickers),
                    'assigned_staff_list' => $assignedPickers,
                    'order_status'        => $order->status,
                    'extra_data'          => [
                        'order' => [
                            'id'                     => (int) $order->id,
                            'order_code'             => $order->order_code,
                            'customer_name'          => $order->customer_name,
                            'customer_phone'         => $order->customer_phone,
                            'customer_address'       => $order->customer_address,
                            'warehouse'              => $order->warehouse,
                            'status'                 => $order->status,
                            'picking_date'           => $order->picking_date,
                            'delivery_date'          => $order->delivery_date,
                            'order_type'             => $order->order_type,
                            'total_amount'           => (float) $order->total_amount,
                            'order_creator'          => $order->order_creator,
                            'created_at'             => $order->created_at ? $order->created_at->toISOString() : now()->toISOString(),
                            'items'                  => $formattedItemsForEvent,
                        ]
                    ],
                    'created_at'          => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('PickingSyncEvent dispatch error: ' . $e->getMessage());
        }

        // Tự động đồng bộ Phiếu Hoàn Hàng delivery_returns vào Database nếu đơn có phát sinh hoàn hàng
        if ($order->has_returns && $order->return_slip_code) {
            $slip = \App\Models\DeliveryReturn::updateOrCreate(
                ['slip_code' => $order->return_slip_code],
                [
                    'order_id'              => $order->id,
                    'order_code'            => $order->order_code,
                    'dealer_id'             => $order->dealer_id,
                    'dealer_name'           => $order->customer_name ?? $order->dealer?->name ?? 'Đại lý',
                    'vehicle_plate'         => $order->assigned_vehicle_plate ?? '29C-888.99',
                    'driver_id'             => $order->assigned_driver_id,
                    'driver_name'           => $order->assignedDriver?->name ?? 'Lái xe',
                    'copilot_name'          => $data['copilotName'] ?? null,
                    'total_returned_amount' => (float) $order->total_returned_amount,
                    'status'                => 'Chở trên xe',
                    'notes'                 => $data['notes'] ?? 'Hàng hoàn trả từ đơn hàng ' . $order->order_code,
                ]
            );

            // Ghi nhận chi tiết từng dòng hàng hoàn trả vào delivery_return_items
            $slip->items()->delete();
            foreach ($order->items as $orderItem) {
                if ($orderItem->returned_qty > 0) {
                    \App\Models\DeliveryReturnItem::create([
                        'delivery_return_id' => $slip->id,
                        'order_item_id'      => $orderItem->id,
                        'product_id'         => $orderItem->product_id,
                        'sku'                => $orderItem->sku,
                        'name'               => $orderItem->name,
                        'unit'               => $orderItem->unit,
                        'returned_qty'       => $orderItem->returned_qty,
                        'unit_price'         => $orderItem->unit_price,
                        'amount'             => $orderItem->returned_qty * $orderItem->unit_price,
                        'reason'             => $orderItem->return_reason ?? 'Khách hoàn trả',
                    ]);
                }
            }
        }

        // Quản lý trừ / hoàn tồn kho tự động theo quy trình Chốt đơn & Hủy đơn
        $isFinalizedStatus = !in_array($order->status, ['Báo giá', 'Bản nháp', 'Chờ duyệt', 'Chờ kế toán duyệt', 'Hủy', 'Đã hủy', 'Đã huỷ']);
        $isCancelledStatus = in_array($order->status, ['Hủy', 'Đã hủy', 'Đã huỷ']);

        if ($isFinalizedStatus && !$order->stock_deducted) {
            $this->deductOrderStock($order);
        } elseif ($isCancelledStatus && $order->stock_deducted) {
            $this->restoreOrderStock($order);
        }

        // Tự động tính toán lại công nợ đại lý
        $updatedDealerDebt = null;
        if ($order->dealer_id) {
            $dealer = Dealer::find($order->dealer_id);
            if ($dealer) {
                $updatedDealerDebt = $dealer->recalculateCurrentDebt();
            }
        }

        // Tự động ghi nhận Nhật Ký Truy Vết & Tiến Trình (audit_logs) vào Cơ Sở Dữ Liệu
        $performer = auth()->user()?->name ?? $data['performerName'] ?? $creatorName ?? 'Admin KBTech';
        $perfId = auth()->id() ?? $creatorId ?? null;

        try {
            if ($isNewOrder) {
                \App\Models\AuditLog::create([
                    'timestamp'            => now()->format('Y-m-d H:i:s'),
                    'performer_name'       => $performer,
                    'user_id'              => $perfId,
                    'action'               => 'Khởi Tạo Đơn Hàng',
                    'module'               => 'Đơn Hàng & Bán Hàng',
                    'target_document_code' => $order->order_code,
                    'field_name'           => 'Khởi tạo mới',
                    'old_value'            => null,
                    'new_value'            => $order->status,
                    'notes'                => "Khởi tạo đơn hàng mới cho đại lý [{$order->customer_name}]. Trị giá: " . number_format($order->total_amount, 0, ',', '.') . " đ (" . count($items ?? []) . " mặt hàng).",
                ]);
            } else {
                // A. Chuyển trạng thái tiến trình
                if ($oldStatus && $oldStatus !== $order->status) {
                    $actionName = match($order->status) {
                        'Chốt đơn', 'Đã duyệt' => 'Phê Duyệt & Chốt Đơn',
                        'Soạn bốc', 'Soạn hàng', 'Đang bốc hàng', 'Kho đang soạn' => 'Soạn Bốc Kho',
                        'Đã bốc xong', 'Kho đã xuất đủ' => 'Xác Nhận Đã Bốc Xong',
                        'Kiểm hàng', 'Đang kiểm hàng' => 'Kiểm Hàng (QC)',
                        'Đã kiểm hàng', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Đã kiểm xong' => 'Xác Nhận Đã Kiểm Hàng (Sẵn Sàng Xuất Xe)',
                        'Đang vận chuyển', 'Đang giao hàng' => 'Xuất Bến & Giao Vận',
                        'Đã giao', 'Giao thành công' => 'Hoàn Tất Giao Hàng & Thu Tiền',
                        'Hủy', 'Đã hủy' => 'Hủy Chứng Từ',
                        default => 'Chuyển Trạng Thái Đơn Hàng'
                    };
                    \App\Models\AuditLog::create([
                        'timestamp'            => now()->format('Y-m-d H:i:s'),
                        'performer_name'       => $performer,
                        'user_id'              => $perfId,
                        'action'               => $actionName,
                        'module'               => 'Đơn Hàng & Bán Hàng',
                        'target_document_code' => $order->order_code,
                        'field_name'           => 'Trạng thái đơn hàng',
                        'old_value'            => $oldStatus,
                        'new_value'            => $order->status,
                        'notes'                => "Chuyển tiến trình từ [{$oldStatus}] sang [{$order->status}]." . ($order->status === 'Đã giao' ? " Phương thức: {$order->payment_method}." : ''),
                    ]);
                }

                // B. Chỉnh sửa giá trị / chi tiết mặt hàng
                if ($oldTotal > 0 && abs($oldTotal - (float)$order->total_amount) > 100) {
                    \App\Models\AuditLog::create([
                        'timestamp'            => now()->format('Y-m-d H:i:s'),
                        'performer_name'       => $performer,
                        'user_id'              => $perfId,
                        'action'               => 'Chỉnh Sửa Giá Trị Đơn',
                        'module'               => 'Đơn Hàng & Bán Hàng',
                        'target_document_code' => $order->order_code,
                        'field_name'           => 'Tổng tiền đơn hàng',
                        'old_value'            => number_format($oldTotal, 0, ',', '.') . ' đ',
                        'new_value'            => number_format($order->total_amount, 0, ',', '.') . ' đ',
                        'notes'                => "Chỉnh sửa số lượng hoặc đơn giá vật tư trong đơn.",
                    ]);
                }

                // C. Phân bổ xe giao hàng
                if (!empty($order->assigned_vehicle_plate) && $oldVehiclePlate !== $order->assigned_vehicle_plate) {
                    \App\Models\AuditLog::create([
                        'timestamp'            => now()->format('Y-m-d H:i:s'),
                        'performer_name'       => $performer,
                        'user_id'              => $perfId,
                        'action'               => 'Điều Phối Xe Giao Hàng',
                        'module'               => 'Vận Tải & Giao Nhận',
                        'target_document_code' => $order->order_code,
                        'field_name'           => 'Xe tải phụ trách',
                        'old_value'            => $oldVehiclePlate ?: 'Chưa phân xe',
                        'new_value'            => $order->assigned_vehicle_plate,
                        'notes'                => "Gán xe [{$order->assigned_vehicle_plate}] thực hiện giao nhận đơn.",
                    ]);
                }

                // D. Ghi nhận hoàn hàng
                if ($order->has_returns && $order->return_slip_code) {
                    \App\Models\AuditLog::create([
                        'timestamp'            => now()->format('Y-m-d H:i:s'),
                        'performer_name'       => $performer,
                        'user_id'              => $perfId,
                        'action'               => 'Ghi Nhận Hoàn Hàng Tại Điểm Giao',
                        'module'               => 'Vận Tải & Giao Nhận',
                        'target_document_code' => $order->order_code,
                        'field_name'           => 'Hàng hoàn trả',
                        'old_value'            => '0 đ',
                        'new_value'            => '-' . number_format($order->total_returned_amount, 0, ',', '.') . ' đ',
                        'notes'                => "Lập phiếu hoàn trả [#{$order->return_slip_code}] giảm trừ công nợ / COD.",
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore audit log error
        }

        $latestAuditLogs = \App\Models\AuditLog::where('target_document_code', $order->order_code)
            ->latest('id')
            ->get()
            ->map(function ($log) {
                return [
                    'id'                 => (string) $log->id,
                    'timestamp'          => $log->timestamp ?: ($log->created_at ? $log->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s')),
                    'performerName'      => $log->performer_name,
                    'userId'             => (string) ($log->user_id ?? ''),
                    'action'             => $log->action,
                    'module'             => $log->module,
                    'targetDocumentCode' => $log->target_document_code,
                    'fieldName'          => $log->field_name,
                    'oldValue'           => $log->old_value,
                    'newValue'           => $log->new_value,
                    'notes'              => $log->notes,
                ];
            });

        // ===== PHÁT HIỆN VƯỢT HẠN MỨC CÔNG NỢ KHI LẬP ĐƠN =====
        $creditWarning = null;
        $isDebtOrder = in_array($order->payment_method, ['Ghi nợ', 'Ghi nợ đại lý'])
            || in_array($order->payment_status, ['Ghi nợ', 'Ghi nợ đại lý', 'Chưa thanh toán']);

        if ($isNewOrder && $isDebtOrder && $order->dealer_id) {
            $dealer = Dealer::find($order->dealer_id);
            if ($dealer && (float)($dealer->credit_limit ?? 0) > 0) {
                $prevDebt = $dealer->getDebtExcludingOrder($order->id);
                $creditLimit = (float)$dealer->credit_limit;
                $newTotal    = (float)$order->total_amount;
                $projectedDebt = $prevDebt + $newTotal;

                if ($projectedDebt > $creditLimit) {
                    $excessAmount = $projectedDebt - $creditLimit;

                    // Đặt đơn vào trạng thái "Chờ duyệt hạn mức"
                    $order->update([
                        'status'                    => 'Chờ duyệt hạn mức',
                        'credit_overrun_status'     => 'Chờ duyệt',
                        'credit_overrun_amount'     => $excessAmount,
                        'credit_overrun_reason'     => "Đơn vượt hạn mức: Nợ cũ {$prevDebt} + Đơn mới {$newTotal} = {$projectedDebt} > Hạn mức {$creditLimit}",
                    ]);

                    // Ghi audit log
                    try {
                        \App\Models\AuditLog::create([
                            'timestamp'            => now()->format('Y-m-d H:i:s'),
                            'performer_name'       => $performer,
                            'user_id'              => $perfId,
                            'action'               => 'Vượt Hạn Mức Công Nợ – Chờ Kế Toán Duyệt',
                            'module'               => 'Đơn Hàng & Bán Hàng',
                            'target_document_code' => $order->order_code,
                            'field_name'           => 'credit_overrun_status',
                            'old_value'            => null,
                            'new_value'            => 'Chờ duyệt',
                            'notes'                => "Đơn vượt hạn mức " . number_format($excessAmount, 0, ',', '.') . " đ. Cần Kế toán phê duyệt trước khi soạn hàng.",
                        ]);
                    } catch (\Throwable $e) { /* ignore */ }

                    $creditWarning = [
                        'isOverLimit'    => true,
                        'prevDebt'       => $prevDebt,
                        'creditLimit'    => $creditLimit,
                        'orderAmount'    => $newTotal,
                        'projectedDebt'  => $projectedDebt,
                        'excessAmount'   => $excessAmount,
                        'message'        => "Đơn hàng [{$order->order_code}] vượt hạn mức công nợ " . number_format($excessAmount, 0, ',', '.') . " đ. Đơn đang CHỜ KẾ TOÁN PHÊ DUYỆT trước khi kho soạn hàng.",
                        'newOrderStatus' => 'Chờ duyệt hạn mức',
                    ];
                }
            }
        }
        // ===== END CREDIT OVERRUN DETECTION =====

        \App\Http\Controllers\WebViewController::clearPosProductsCache();

        return response()->json([
            'success'        => true,
            'message'        => $creditWarning
                ? '⚠️ Đơn hàng đã lưu nhưng VƯỢT HẠN MỨC CÔNG NỢ – Đang chờ Kế toán phê duyệt!'
                : 'Lưu đơn hàng thành công',
            'creditWarning'  => $creditWarning,
            'order'          => [
                'id'            => (string) $order->id,
                'orderCode'     => $order->order_code,
                'status'        => $order->status,
                'totalAmount'   => (float) $order->total_amount,
                'stockDeducted' => (bool) $order->stock_deducted,
            ],
            'dealerId'   => $order->dealer_id ? (string) $order->dealer_id : null,
            'dealerDebt' => $updatedDealerDebt,
            'auditLogs'  => $latestAuditLogs,
        ]);
    }

    /**
     * API: Lấy thông tin công nợ / hạn mức của đại lý (dùng realtime trên form lập đơn)
     */
    public function getDealerCreditInfo(Request $request, $dealerId)
    {
        $dealer = Dealer::find($dealerId);
        if (!$dealer) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đại lý'], 404);
        }

        $currentDebt  = $dealer->getDebtExcludingOrder(null);
        $creditLimit  = (float)($dealer->credit_limit ?? 0);
        $remaining    = $creditLimit > 0 ? max(0, $creditLimit - $currentDebt) : null;

        return response()->json([
            'success'         => true,
            'dealerId'        => $dealer->id,
            'dealerName'      => $dealer->name,
            'dealerCode'      => $dealer->code,
            'currentDebt'     => $currentDebt,
            'creditLimit'     => $creditLimit,
            'remainingCredit' => $remaining,
            'isOverLimit'     => $creditLimit > 0 && $currentDebt > $creditLimit,
            'overrunAmount'   => $creditLimit > 0 ? max(0, $currentDebt - $creditLimit) : 0,
            'paymentTermDays' => (int)($dealer->payment_term_days ?? 0),
        ]);
    }

    /**
     * API: Kế toán / Quản lý phê duyệt hoặc từ chối đơn vượt hạn mức lúc lập đơn
     * Cho phép đặt ngày trả tiền và ghi chú riêng cho đại lý
     */
    public function approveCreditOverrunAtCreation(Request $request, $orderId)
    {
        $raw = json_decode($request->getContent(), true) ?: [];
        $data = array_merge($request->all(), $raw);

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Kiểm tra quyền: chỉ Admin, Giám đốc, Kế toán trưởng mới được duyệt
        $user = auth()->user();
        $canApprove = false;
        if ($user) {
            $userRoleSlugs = $user->roles ? $user->roles->pluck('slug')->toArray() : [];
            $approverSlugs = ['admin', 'director', 'manager', 'accountant', 'ke_toan', 'giam_doc', 'ke_toan_truong'];
            $canApprove = !empty(array_intersect($userRoleSlugs, $approverSlugs))
                || $user->hasAnyPermission(['orders.approve', 'debt.approve', 'orders.edit']);
        }
        if (!$canApprove) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền phê duyệt hạn mức công nợ.'], 403);
        }

        $decision        = $data['decision'] ?? 'approve'; // 'approve' | 'reject'
        $paymentDueDate  = $data['paymentDueDate'] ?? ($data['payment_due_date'] ?? null);
        $noteForDealer   = $data['noteForDealer'] ?? ($data['note_for_dealer'] ?? null);
        $internalNote    = $data['internalNote'] ?? ($data['internal_note'] ?? null);

        $approverName = $user?->name ?? 'Kế toán';
        $approverId   = $user?->id;

        if ($decision === 'approve') {
            $order->update([
                'status'                             => 'Chốt đơn',
                'credit_overrun_status'              => 'Đã duyệt',
                'credit_overrun_approved_by'         => $approverId,
                'credit_overrun_approved_name'       => $approverName,
                'credit_overrun_approved_at'         => now(),
                'credit_overrun_payment_due_date'    => $paymentDueDate ?: null,
                'credit_overrun_note_for_dealer'     => $noteForDealer ?: null,
            ]);
            $auditAction  = 'Phê Duyệt Vượt Hạn Mức Công Nợ';
            $auditNotes   = "Kế toán [{$approverName}] phê duyệt vượt hạn mức. Ngày trả tiền: " . ($paymentDueDate ?: 'Không xác định') . ". Ghi chú: " . ($internalNote ?: 'Không có');
            $responseMsg  = '✅ Đã phê duyệt! Đơn hàng chuyển sang Chốt đơn và kho có thể soạn hàng.';
        } else {
            $order->update([
                'status'                             => 'Từ chối hạn mức',
                'credit_overrun_status'              => 'Từ chối',
                'credit_overrun_approved_by'         => $approverId,
                'credit_overrun_approved_name'       => $approverName,
                'credit_overrun_approved_at'         => now(),
                'credit_overrun_reason'              => $internalNote ?: 'Vượt hạn mức công nợ, từ chối phê duyệt.',
            ]);
            $auditAction  = 'Từ Chối Vượt Hạn Mức Công Nợ';
            $auditNotes   = "Kế toán [{$approverName}] từ chối. Lý do: " . ($internalNote ?: 'Vượt hạn mức.');
            $responseMsg  = '❌ Đã từ chối. Đơn hàng cần được điều chỉnh hoặc Sales liên hệ lại với đại lý.';
        }

        // Ghi audit log
        try {
            \App\Models\AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $approverName,
                'user_id'              => $approverId,
                'action'               => $auditAction,
                'module'               => 'Đơn Hàng & Bán Hàng',
                'target_document_code' => $order->order_code,
                'field_name'           => 'credit_overrun_status',
                'old_value'            => 'Chờ duyệt',
                'new_value'            => $decision === 'approve' ? 'Đã duyệt' : 'Từ chối',
                'notes'                => $auditNotes,
            ]);
        } catch (\Throwable $e) { /* ignore */ }

        // Tái tính công nợ
        $updatedDealerDebt = null;
        if ($order->dealer_id) {
            $dealerObj = Dealer::find($order->dealer_id);
            if ($dealerObj) $updatedDealerDebt = $dealerObj->recalculateCurrentDebt();
        }

        return response()->json([
            'success'         => true,
            'message'         => $responseMsg,
            'decision'        => $decision,
            'newOrderStatus'  => $order->status,
            'overrunStatus'   => $order->credit_overrun_status,
            'approvedByName'  => $approverName,
            'approvedAt'      => now()->format('H:i d/m/Y'),
            'paymentDueDate'  => $paymentDueDate,
            'dealerDebt'      => $updatedDealerDebt,
        ]);
    }

    /**
     * Deduct stock for order items when order is finalized (Chốt đơn)
     */
    public function deductOrderStock(Order $order)
    {
        if ($order->stock_deducted) {
            return;
        }

        $targetWarehouse = null;
        if (!empty($order->warehouse)) {
            $targetWarehouse = \App\Models\Warehouse::where('name', $order->warehouse)
                ->orWhere('code', $order->warehouse)
                ->first();
        }
        if (!$targetWarehouse) {
            $targetWarehouse = \App\Models\Warehouse::where('name', 'like', '%Mê Linh%')->first()
                ?: \App\Models\Warehouse::first();
        }

        $order->loadMissing('items');
        foreach ($order->items as $item) {
            $qty = (float) $item->quantity;
            if ($qty <= 0) continue;

            $product = null;
            if ($item->product_id) {
                $product = \App\Models\Product::find($item->product_id);
            }
            if (!$product && $item->sku) {
                $product = \App\Models\Product::where('sku', $item->sku)->first();
            }

            if ($product) {
                $baseQty = $qty;
                if ($product->hasConversionUnit() && (float)$product->conversion_rate > 0) {
                    $itemUnit = mb_strtolower(trim($item->unit ?? ''));
                    $convUnit = mb_strtolower(trim($product->conversion_unit ?? ''));
                    $baseUnit = mb_strtolower(trim($product->unit ?? ''));
                    if ($itemUnit === $convUnit && $convUnit !== $baseUnit) {
                        $baseQty = $qty / (float)$product->conversion_rate;
                    }
                }

                $product->decrement('stock_quantity', $baseQty);

                if ($targetWarehouse) {
                    $whStock = \App\Models\WarehouseStock::firstOrCreate(
                        ['product_id' => $product->id, 'warehouse_id' => $targetWarehouse->id],
                        ['stock_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $whStock->decrement('stock_quantity', $baseQty);
                }
            }
        }

        $order->stock_deducted = true;
        $order->save();
    }

    /**
     * Restore stock for order items when order is cancelled (Hủy)
     */
    public function restoreOrderStock(Order $order)
    {
        if (!$order->stock_deducted) {
            return;
        }

        $targetWarehouse = null;
        if (!empty($order->warehouse)) {
            $targetWarehouse = \App\Models\Warehouse::where('name', $order->warehouse)
                ->orWhere('code', $order->warehouse)
                ->first();
        }
        if (!$targetWarehouse) {
            $targetWarehouse = \App\Models\Warehouse::where('name', 'like', '%Mê Linh%')->first()
                ?: \App\Models\Warehouse::first();
        }

        $order->loadMissing('items');
        foreach ($order->items as $item) {
            $qty = (float) $item->quantity;
            if ($qty <= 0) continue;

            $product = null;
            if ($item->product_id) {
                $product = \App\Models\Product::find($item->product_id);
            }
            if (!$product && $item->sku) {
                $product = \App\Models\Product::where('sku', $item->sku)->first();
            }

            if ($product) {
                $baseQty = $qty;
                if ($product->hasConversionUnit() && (float)$product->conversion_rate > 0) {
                    $itemUnit = mb_strtolower(trim($item->unit ?? ''));
                    $convUnit = mb_strtolower(trim($product->conversion_unit ?? ''));
                    $baseUnit = mb_strtolower(trim($product->unit ?? ''));
                    if ($itemUnit === $convUnit && $convUnit !== $baseUnit) {
                        $baseQty = $qty / (float)$product->conversion_rate;
                    }
                }

                $product->increment('stock_quantity', $baseQty);

                if ($targetWarehouse) {
                    $whStock = \App\Models\WarehouseStock::firstOrCreate(
                        ['product_id' => $product->id, 'warehouse_id' => $targetWarehouse->id],
                        ['stock_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $whStock->increment('stock_quantity', $baseQty);
                }
            }
        }

        $order->stock_deducted = false;
        $order->save();
    }

    /**
     * API: Get pending debt/borrow orders for a customer to prompt for merging
     */
    public function getPendingDebtsAndBorrows(Request $request)
    {
        $dealerId = $request->query('dealer_id');
        $phone = trim((string)$request->query('customer_phone'));
        $name = trim((string)$request->query('customer_name'));
        $excludeId = $request->query('exclude_id');

        if (!$dealerId && !$phone && !$name) {
            return response()->json(['success' => true, 'orders' => []]);
        }

        $query = Order::with('items')
            ->whereNotIn('status', ['Đã giao', 'Giao thành công', 'Hủy', 'Đã hủy', 'Đã gộp đơn']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $query->where(function ($q) use ($dealerId, $phone, $name) {
            if ($dealerId) {
                $q->where('dealer_id', $dealerId);
                if ($phone) $q->orWhere('customer_phone', $phone);
            } elseif ($phone) {
                $q->where('customer_phone', $phone);
                if ($name) $q->orWhere('customer_name', $name);
            } else {
                $q->where('customer_name', $name);
            }
        });

        $query->where(function ($q) {
            $q->whereIn('status', ['Giao sau', 'Đơn giao sau', 'Nợ hàng'])
              ->orWhereIn('order_type', ['backorder']);
        });

        $orders = $query->with(['items.product'])->latest('id')->get()->map(function ($o) {
            return [
                'id' => $o->id,
                'order_code' => $o->order_code,
                'status' => $o->status,
                'order_type' => 'backorder',
                'type_label' => 'Đơn giao sau (nợ hàng)',
                'created_at' => $o->created_at ? $o->created_at->format('d/m/Y H:i') : '',
                'notes' => $o->notes,
                'total_amount' => (float)$o->total_amount,
                'items_count' => $o->items->count(),
                'items' => $o->items->map(function ($it) {
                    $name = $it->name ?: ($it->product->name ?? 'Sản phẩm #' . $it->product_id);
                    $sku = $it->sku ?: ($it->product->sku ?? '');
                    return [
                        'id' => $it->id,
                        'product_id' => $it->product_id,
                        'name' => $name,
                        'sku' => $sku,
                        'unit' => $it->unit ?: ($it->product->unit ?? 'cái'),
                        'quantity' => (float)$it->quantity,
                        'picked_qty' => (float)$it->picked_qty,
                        'unit_price' => (float)$it->unit_price,
                        'amount' => (float)$it->amount,
                        'vat_percent' => (float)($it->vat_percent ?? 0),
                        'vat_amount' => (float)($it->vat_amount ?? 0),
                        'discount_amount' => (float)($it->discount_amount ?? 0),
                        'discount_percent' => (float)($it->discount_percent ?? 0),
                        'price_type' => $it->applied_tier_policy ?: 'Theo phiếu',
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * API: Mark merged orders when saving a new order that absorbed them
     */
    public function markOrdersMerged(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $mergedIds = $data['merged_order_ids'] ?? [];
        $targetOrderCode = $data['target_order_code'] ?? 'đơn hàng mới';

        if (!empty($mergedIds) && is_array($mergedIds)) {
            foreach ($mergedIds as $id) {
                $order = Order::find($id);
                if ($order && $order->status !== 'Đã gộp đơn') {
                    $order->status = 'Đã gộp đơn';
                    $order->notes = ($order->notes ? $order->notes . "\n" : '') . "[Đã gộp vào đơn {$targetOrderCode} lúc " . date('H:i d/m/Y') . "]";
                    $order->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu các đơn được gộp thành công'
        ]);
    }

    /**
     * Chuyển đổi số tiền thành chữ Tiếng Việt
     */
    public static function convertNumberToVietnameseWords($number): string
    {
        $number = (float)$number;
        if ($number == 0) return 'KHÔNG ĐỒNG';

        $digits = ['', 'MỘT', 'HAI', 'BA', 'BỐN', 'NĂM', 'SÁU', 'BẢY', 'TÁM', 'CHÍN'];
        $units = ['', 'NGHÌN', 'TRIỆU', 'TỶ', 'NGHÌN TỶ', 'TRIỆU TỶ'];

        $readThreeDigits = function ($n, $isLastGroup = false) use ($digits) {
            $hundreds = intdiv($n, 100);
            $remainder = $n % 100;
            $tens = intdiv($remainder, 10);
            $ones = $remainder % 10;
            $res = [];

            if ($hundreds > 0 || !$isLastGroup) {
                $res[] = $digits[$hundreds] . ' TRĂM';
            }

            if ($tens > 1) {
                $res[] = $digits[$tens] . ' MƯƠI';
                if ($ones == 1) $res[] = 'MỐT';
                elseif ($ones == 4) $res[] = 'TƯ';
                elseif ($ones == 5) $res[] = 'LĂM';
                elseif ($ones > 0) $res[] = $digits[$ones];
            } elseif ($tens == 1) {
                $res[] = 'MƯỜI';
                if ($ones == 5) $res[] = 'LĂM';
                elseif ($ones > 0) $res[] = $digits[$ones];
            } else {
                if ($ones > 0) {
                    if ($hundreds > 0 || !$isLastGroup) {
                        $res[] = 'LINH';
                    }
                    $res[] = $digits[$ones];
                }
            }
            return implode(' ', $res);
        };

        $groups = [];
        $temp = abs((int)round($number));
        while ($temp > 0) {
            $groups[] = $temp % 1000;
            $temp = intdiv($temp, 1000);
        }

        $words = [];
        $count = count($groups);
        for ($i = $count - 1; $i >= 0; $i--) {
            $g = $groups[$i];
            if ($g > 0) {
                $words[] = $readThreeDigits($g, $i === $count - 1) . ' ' . $units[$i];
            }
        }

        $res = trim(preg_replace('/\s+/', ' ', implode(' ', $words))) . ' ĐỒNG';
        return mb_strtoupper($res, 'UTF-8');
    }

    /**
     * API: Get detailed order data for Invoice Preview & Printing modal
     */
    public function getOrderPrintData($id)
    {
        $order = Order::with(['items.product.category', 'dealer', 'creator', 'assignedDriver', 'warehouseStaff', 'salesRep'])
            ->where('id', $id)
            ->orWhere('order_code', $id)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng!'], 404);
        }

        $dealer = $order->dealer;
        $customerCode = $dealer?->code ?: ($order->dealer_id ? sprintf('%06d', $order->dealer_id) : '001120');
        $customerName = $dealer?->name ?: ($order->customer_name ?: 'Quý Khách Hàng');
        $customerAddress = $order->customer_address ?: ($dealer?->address ?: '');
        $customerPhone = $order->customer_phone ?: ($dealer?->phone ?: '');

        $subtotal = 0;
        $totalDiscount = 0;
        $totalItemsAmount = 0;

        $priceTiers = \App\Models\PriceTier::where('status', 'Hoạt động')->orWhereNull('status')->get();
        // Map: lowercase(name) → code và lowercase(code) → code (exact match)
        $tierCodeMap = [];
        // Mảng để fuzzy match: [[keywords_from_name, code], ...]
        $tierFuzzyList = [];
        foreach ($priceTiers as $pt) {
            if (empty($pt->code)) continue;
            $nameLower = mb_strtolower(trim($pt->name));
            $codeLower = mb_strtolower(trim($pt->code));
            $tierCodeMap[$nameLower] = $pt->code;
            $tierCodeMap[$codeLower] = $pt->code;
            // Tách từ khóa từ tên để fuzzy match (bỏ stopword)
            $tierFuzzyList[] = ['name' => $nameLower, 'code' => $pt->code];
        }

        /**
         * Hàm nội bộ: tìm code của tier từ chuỗi input
         */
        $resolveTierCode = function(string $rawInput) use ($tierCodeMap, $tierFuzzyList): string {
            $clean = $rawInput;
            // Trích xuất nội dung trong ngoặc đơn nếu có: "Theo phiếu (Nguyên Giá)" → "Nguyên Giá"
            if (preg_match('/\(([^)]+)\)/', $rawInput, $m)) {
                $clean = trim($m[1]);
            }
            $sLower = mb_strtolower(trim($clean));
            if (empty($sLower)) return '';

            // 1. Exact match từ DB
            if (isset($tierCodeMap[$sLower])) return $tierCodeMap[$sLower];

            // 2. Fuzzy: kiểm tra xem $sLower có chứa trong name của tier DB không (hoặc ngược lại)
            foreach ($tierFuzzyList as $entry) {
                if (str_contains($entry['name'], $sLower) || str_contains($sLower, $entry['name'])) {
                    return $entry['code'];
                }
            }

            // 3. Hardcode fallback nếu DB không có cấu hình phù hợp
            if (str_contains($sLower, 'nguyên giá') || str_contains($sLower, 'nguyen gia') || $sLower === 'ng') return 'NG';
            if (str_contains($sLower, 'bán lẻ') || str_contains($sLower, 'ban le') || $sLower === 'bl') return 'BL';
            if (str_contains($sLower, 'khuyến mại') || str_contains($sLower, 'tặng') || $sLower === 'km') return 'KM';
            if (str_contains($sLower, 'cấp 1') || str_contains($sLower, 'cap 1') || $sLower === 'bc1') return 'BC1';
            if (str_contains($sLower, 'cấp 2') || str_contains($sLower, 'cap 2') || $sLower === 'bc2') return 'BC2';
            if (str_contains($sLower, 'cấp 3') || str_contains($sLower, 'cap 3') || $sLower === 'bc3') return 'BC3';

            // 4. Trả về chính code viết hoa nếu không tìm được
            return mb_strtoupper($clean);
        };

        $items = [];
        $idx = 1;
        foreach ($order->items as $it) {
            $qty     = (float)($it->quantity ?? 0);
            $giftQty = (float)($it->gift_quantity ?? 0);
            $totalQty = $qty + $giftQty;

            $origPrice = (float)($it->original_price > 0 ? $it->original_price : ($it->unit_price > 0 ? $it->unit_price : ($it->product?->cost_price ?: 0)));
            $unitPrice = (float)($it->unit_price > 0 ? $it->unit_price : $origPrice);

            $itemSubtotal     = $origPrice * $totalQty;
            $itemActualAmount = (float)($it->amount > 0 ? $it->amount : ($unitPrice * $qty));
            $itemDiscount     = max(0, $itemSubtotal - $itemActualAmount);

            $subtotal         += $itemSubtotal;
            $totalDiscount    += $itemDiscount;
            $totalItemsAmount += $itemActualAmount;

            // Xác định tier thực tế: item override > dealer tier > fallback
            $rawTierInput = ($it->applied_tier_policy === 'Theo phiếu' || empty($it->applied_tier_policy))
                ? ($dealer?->tier ?: 'Nguyên Giá')
                : $it->applied_tier_policy;

            $tierCode = $resolveTierCode($rawTierInput);

            $items[] = [
                'tt'               => $idx++,
                'id'               => $it->id,
                'sku'              => $it->sku,
                'name'             => $it->name,
                'tax_name'         => $it->product?->tax_name ?: ($it->name),
                'unit'             => $it->unit ?: 'Cây',
                'unit_price'       => $origPrice > 0 ? $origPrice : $unitPrice,
                'sale_price'       => $unitPrice,
                'quantity'         => $totalQty,
                'sold_quantity'    => $qty,
                'gift_quantity'    => $giftQty,
                'amount'           => $itemSubtotal,
                'discount'         => $itemDiscount,
                'discount_percent' => ($itemSubtotal > 0 && $itemDiscount > 0 ? round(($itemDiscount / $itemSubtotal) * 100, 1) : 0),
                'total'            => $itemActualAmount,
                'customer_price'   => $origPrice > 0 ? $origPrice : $unitPrice,
                'price_tier_code'  => $tierCode,
                'price_type'       => ($it->applied_tier_policy === 'Theo phiếu' || empty($it->applied_tier_policy))
                    ? ('Theo phiếu (' . ($dealer?->tier ?: 'Nguyên Giá') . ')')
                    : $it->applied_tier_policy,
            ];
        }

        $extraDiscount = (float)($order->discount_amount ?? 0);
        $finalTotal = (float)($order->total_amount > 0 ? $order->total_amount : max(0, $totalItemsAmount - $extraDiscount));

        $orderCreatorName = $order->order_creator ?: ($order->creator?->name ?: ($order->salesRep?->name ?: ''));
        
        $latestAudit = \Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('target_document_code', $order->order_code)
            ->orderByDesc('id')
            ->first();
        $updaterName = $latestAudit?->performer_name ?: ($order->order_creator ?: ($order->creator?->name ?: ''));

        return response()->json([
            'success' => true,
            'order' => [
                'id'                    => $order->id,
                'dealer_tier'           => $dealer?->tier ?: 'Nguyên Giá',
                'order_code'            => $order->order_code,
                'created_date'          => $order->created_at ? $order->created_at->format('d/m/Y H:i') : date('d/m/Y H:i'),
                'customer_code'         => $customerCode,
                'customer_name'         => $customerName,
                'customer_display'      => $customerName ?: $customerCode,
                'customer_phone'        => $customerPhone,
                'customer_address'      => $customerAddress,
                'warehouse'             => $order->warehouse ?: '',
                'notes'                 => $order->notes ?: '',
                'status'                => $order->status,
                'stamp_status'          => '',
                'payment_method'        => $order->payment_method ?: '',
                'payment_status'        => $order->payment_status ?: '',
                'delivery_status'       => $order->status ?: '',
                'delivery_driver'       => $order->assignedDriver?->name ?: '',
                'delivery_vehicle'      => $order->assigned_vehicle_plate ?: '',
                'delivery_date'         => $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') : '',
                'creator_name'          => $orderCreatorName,
                'updater_name'          => $updaterName,
                'subtotal'              => (float)($order->subtotal > 0 ? $order->subtotal : ($subtotal > 0 ? $subtotal : $totalItemsAmount)),
                'total_discount'        => (float)($totalDiscount > 0 ? $totalDiscount : 0),
                'total_items_amount'    => (float)($totalItemsAmount > 0 ? $totalItemsAmount : $order->subtotal),
                'extra_discount'        => $extraDiscount,
                'final_total'           => (float)($order->total_amount > 0 ? $order->total_amount : $finalTotal),
                'final_total_words'     => self::convertNumberToVietnameseWords((float)($order->total_amount > 0 ? $order->total_amount : $finalTotal)),
                'items'                 => $items,
            ]
        ]);
    }
}
