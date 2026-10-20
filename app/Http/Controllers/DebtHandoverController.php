<?php

namespace App\Http\Controllers;

use App\Models\DebtHandover;
use App\Models\VanSaleDispatch;
use App\Models\VanSaleDispatchItem;
use App\Models\User;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtHandoverController extends Controller
{
    /**
     * API: Lấy danh sách các biên bản bàn giao công nợ
     */
    public function getDebtHandovers(Request $request)
    {
        $query = DebtHandover::with(['order', 'fromUser', 'toUser', 'targetDealer', 'confirmedByUser'])
            ->latest('id');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($fromUserId = $request->input('from_user_id')) {
            $query->where('from_user_id', $fromUserId);
        }
        if ($toUserId = $request->input('to_user_id')) {
            $query->where('to_user_id', $toUserId);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('order_code', 'like', "%{$search}%")
                  ->orWhere('from_user_name', 'like', "%{$search}%")
                  ->orWhere('to_user_name', 'like', "%{$search}%")
                  ->orWhere('target_dealer_name', 'like', "%{$search}%");
            });
        }

        $handovers = $query->paginate(20)->withQueryString();

        return response()->json([
            'success'   => true,
            'handovers' => $handovers,
        ]);
    }

    /**
     * API: Tạo yêu cầu / biên bản bàn giao công nợ (Bước 1: Chờ xác nhận)
     */
    public function createDebtHandover(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $rawOrderIds = $data['order_ids'] ?? ($data['orderId'] ?? ($data['order_id'] ?? []));
        if (!is_array($rawOrderIds)) {
            $rawOrderIds = array_filter(array_map('trim', explode(',', (string)$rawOrderIds)));
        }
        $orderCode = trim((string)($data['order_code'] ?? ($data['orderCode'] ?? '')));
        $toUserId = $data['to_user_id'] ?? ($data['toUserId'] ?? null);
        $targetDealerId = $data['target_dealer_id'] ?? ($data['targetDealerId'] ?? null);
        $amount = (float)($data['amount'] ?? 0);
        $notes = $data['notes'] ?? '';

        $orders = collect([]);
        if (!empty($rawOrderIds) || $orderCode) {
            $orders = Order::where(function ($q) use ($rawOrderIds, $orderCode) {
                if (!empty($rawOrderIds)) {
                    $q->whereIn('id', $rawOrderIds)->orWhereIn('order_code', $rawOrderIds);
                }
                if ($orderCode) {
                    $codes = array_filter(array_map('trim', explode(',', $orderCode)));
                    $q->orWhereIn('order_code', $codes);
                }
            })->get();
        }

        $collectedOrderIds = $orders->pluck('id')->values()->toArray();
        $collectedOrderCodes = $orders->pluck('order_code')->values()->join(', ');
        $firstOrder = $orders->first();

        if ($orders->isNotEmpty() && $amount <= 0) {
            $amount = (float)$orders->sum(function ($o) {
                return $o->driver_debt_amount > 0 ? $o->driver_debt_amount : $o->total_amount;
            });
        }

        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Số tiền công nợ bàn giao phải lớn hơn 0'], 422);
        }

        // Xác định người bàn giao (thường là Lái xe đang đăng nhập hoặc người phụ trách đơn)
        $fromUser = auth()->user();
        $fromUserId = $fromUser?->id;
        $fromUserName = $fromUser?->name ?? 'Người vận chuyển';
        $driverId = $orders->pluck('assigned_driver_id')->filter()->first();
        if ($driverId && $fromUserId != $driverId && !$fromUser?->hasRole('admin')) {
            $driverUser = User::find($driverId);
            if ($driverUser) {
                $fromUserId = $driverUser->id;
                $fromUserName = $driverUser->name;
            }
        }

        // Xác định người nhận nợ
        $toUser = null;
        $toUserName = null;
        $toStaffType = 'company_sales';
        if ($toUserId) {
            $toUser = User::with('dealer')->find($toUserId);
            if ($toUser) {
                $toUserName = $toUser->name;
                $toStaffType = $toUser->staff_type ?? 'company_sales';
                // Nếu người nhận là Sale đại lý phụ thuộc, tự động gán đại lý đích
                if ($toUser->staff_type === 'dealer_sales' && $toUser->dealer_id) {
                    $targetDealerId = $toUser->dealer_id;
                }
            }
        }

        // Xác định Đại lý đích
        $targetDealer = null;
        if ($targetDealerId) {
            $targetDealer = Dealer::find($targetDealerId);
        } elseif ($firstOrder && $firstOrder->dealer_id) {
            $targetDealer = Dealer::find($firstOrder->dealer_id);
            $targetDealerId = $targetDealer?->id;
        }

        $code = 'BGCN-' . date('Ymd') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $handover = DebtHandover::create([
                'code'               => $code,
                'order_id'           => $firstOrder?->id,
                'order_ids'          => !empty($collectedOrderIds) ? $collectedOrderIds : null,
                'order_code'         => $collectedOrderCodes ?: ($orderCode ?: null),
                'from_user_id'       => $fromUserId,
                'from_user_name'     => $fromUserName,
                'to_user_id'         => $toUser?->id,
                'to_user_name'       => $toUserName ?? 'Chưa chỉ định',
                'to_staff_type'      => $toStaffType,
                'target_dealer_id'   => $targetDealerId,
                'target_dealer_name' => $targetDealer?->name,
                'amount'             => $amount,
                'handover_type'      => $orders->isNotEmpty() ? 'order_debt' : 'manual',
                'status'             => 'pending',
                'notes'              => $notes,
                'created_by_id'      => auth()->id(),
            ]);

            // Cập nhật trạng thái từng đơn hàng (nếu có)
            foreach ($orders as $ord) {
                $ordDebt = (float)($ord->driver_debt_amount > 0 ? $ord->driver_debt_amount : $ord->total_amount);
                $ord->notes = ($ord->notes ? $ord->notes . "\n" : '') .
                    "[BÀN GIAO NỢ CHỜ DUYỆT]: {$fromUserName} đã gửi biên bản {$code} bàn giao nợ " . number_format($ordDebt, 0, ',', '.') . " đ cho [" . ($toUserName ?: $targetDealer?->name) . "].";
                $ord->save();
            }

            // Ghi Audit Log
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $fromUserName,
                'user_id'              => auth()->id(),
                'action'               => 'Lập Biên Bản Bàn Giao Công Nợ',
                'module'               => 'Công NỢ & Bàn Giao',
                'target_document_code' => $code,
                'field_name'           => 'Biên bản bàn giao nợ',
                'old_value'            => 'Lái xe chịu trách nhiệm',
                'new_value'            => 'Chờ xác nhận nhận nợ',
                'notes'                => "{$fromUserName} bàn giao công nợ " . number_format($amount, 0, ',', '.') . " đ (" . ($collectedOrderCodes ?: 'N/A') . ") cho [{$toUserName}].",
            ]);

            // Gửi thông báo đến người nhận
            if ($toUser) {
                Announcement::create([
                    'title'             => "📑 Yêu cầu nhận bàn giao công nợ: {$code} - " . number_format($amount, 0, ',', '.') . " đ",
                    'content'           => "{$fromUserName} vừa gửi biên bản bàn giao công nợ số {$code} trị giá " . number_format($amount, 0, ',', '.') . " đ" . ($collectedOrderCodes ? " (Đơn hàng: {$collectedOrderCodes})" : '') . " cho bạn.\nVui lòng vào mục Quản lý Công nợ để bấm 'Xác nhận nhận nợ' hoặc từ chối.",
                    'author'            => $fromUserName,
                    'author_role'       => 'Người vận chuyển',
                    'priority'          => 'Quan trọng',
                    'target_department' => 'Kinh doanh',
                    'read_by'           => [],
                ]);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Đã lập biên bản bàn giao {$code} thành công. Chờ bên nhận xác nhận!",
                'handover' => $handover,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi lập biên bản bàn giao: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Xác nhận nhận nợ (Bước 2: Sale hoặc Kế toán bấm 'Xác nhận nhận nợ')
     */
    public function confirmDebtHandover(Request $request, $id)
    {
        $handover = DebtHandover::where('id', $id)->orWhere('code', $id)->first();
        if (!$handover) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy biên bản bàn giao'], 404);
        }

        if ($handover->status !== 'pending') {
            return response()->json(['success' => false, 'message' => "Biên bản này đã ở trạng thái: {$handover->status}"], 422);
        }

        $user = auth()->user();
        $approverName = $user?->name ?? 'Người xác nhận';

        DB::beginTransaction();
        try {
            $amount = (float)$handover->amount;

            // 1. Giảm công nợ của Người bàn giao (Lái xe / Người vận chuyển)
            if ($handover->from_user_id) {
                $fromUser = User::find($handover->from_user_id);
                if ($fromUser) {
                    $fromUser->current_debt = max(0, (float)$fromUser->current_debt - $amount);
                    $fromUser->save();
                }
            }

            // 2. Chuyển công nợ sang bên nhận:
            // Luôn ghi nhận nợ cho nhân viên nhận nợ (nếu có to_user_id)
            $toUser = null;
            if ($handover->to_user_id) {
                $toUser = User::find($handover->to_user_id);
                if ($toUser) {
                    $toUser->current_debt = (float)$toUser->current_debt + $amount;
                    $toUser->save();
                }
            }

            // Nếu người nhận là Nhân viên Đại lý (thuộc đại lý) hoặc có gán Đại lý đích:
            // -> Ghi nhận công nợ vào Đại lý đó đồng thời (theo quy tắc: nợ nhân viên đại lý đi kèm nợ đại lý)
            $isDealerSales = ($handover->to_staff_type === 'dealer_sales') 
                || !empty($handover->target_dealer_id) 
                || ($toUser && (!empty($toUser->dealer_id) || $toUser->staff_type === 'dealer_sales'));

            if ($isDealerSales) {
                $dealerId = $handover->target_dealer_id ?: $toUser?->dealer_id;
                if ($dealerId) {
                    $dealer = Dealer::find($dealerId);
                    if ($dealer) {
                        $dealer->current_debt = (float)$dealer->current_debt + $amount;
                        $dealer->save();
                    }
                }
            }

            // 3. Cập nhật đơn hàng: chuyển quyền quản lý đơn & giải phóng trách nhiệm lái xe
            $orderIds = $handover->order_ids ?: ($handover->order_id ? [$handover->order_id] : []);
            if (!empty($orderIds)) {
                $orders = Order::whereIn('id', $orderIds)->get();
                foreach ($orders as $order) {
                    $order->driver_debt_amount = 0;
                    if ($toUser) {
                        $order->sales_rep_id = $toUser->id;
                    }
                    if ($targetDealerId) {
                        $order->dealer_id = $targetDealerId;
                    } elseif ($toUser && $toUser->dealer_id) {
                        $order->dealer_id = $toUser->dealer_id;
                    }
                    $order->notes = ($order->notes ? $order->notes . "\n" : '') .
                        "[ĐÃ XÁC NHẬN BÀN GIAO]: {$approverName} đã nhận bàn giao đơn hàng và công nợ " . number_format($order->total_amount, 0, ',', '.') . " đ theo biên bản {$handover->code}.";
                    $order->save();

                    // Tự động tính toán lại công nợ đại lý & nhân viên đại lý
                    if ($order->dealer_id) {
                        $dl = Dealer::find($order->dealer_id) ?: Dealer::where('code', $order->dealer_id)->first();
                        if ($dl) {
                            $dl->recalculateCurrentDebt();
                        }
                    }
                }
            }

            // 4. Cập nhật biên bản bàn giao
            $handover->update([
                'status'            => 'confirmed',
                'confirmed_by'      => $user?->id,
                'confirmed_by_name' => $approverName,
                'confirmed_at'      => now(),
            ]);

            // 5. Ghi Audit Log
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $approverName,
                'user_id'              => $user?->id,
                'action'               => 'Xác Nhận Nhận Bàn Giao Công Nợ',
                'module'               => 'Công Nợ & Bàn Giao',
                'target_document_code' => $handover->code,
                'field_name'           => 'Biên bản bàn giao nợ',
                'old_value'            => 'Chờ xác nhận',
                'new_value'            => 'Đã xác nhận',
                'notes'                => "{$approverName} đã xác nhận nhận nợ " . number_format($amount, 0, ',', '.') . " đ từ [{$handover->from_user_name}].",
            ]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Đã xác nhận nhận công nợ {$handover->code} thành công! Công nợ đã được chuyển.",
                'handover' => $handover,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi khi xác nhận bàn giao nợ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Từ chối nhận nợ (Sale hoặc Kế toán bấm 'Từ chối')
     */
    public function rejectDebtHandover(Request $request, $id)
    {
        $raw = json_decode($request->getContent(), true) ?: [];
        $reason = $request->input('reason') ?? ($raw['reason'] ?? 'Không khớp thông tin đơn hàng / không đồng ý nhận nợ');

        $handover = DebtHandover::where('id', $id)->orWhere('code', $id)->first();
        if (!$handover) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy biên bản bàn giao'], 404);
        }

        if ($handover->status !== 'pending') {
            return response()->json(['success' => false, 'message' => "Biên bản này không ở trạng thái Chờ xác nhận (hiện tại: {$handover->status})"], 422);
        }

        $user = auth()->user();
        $rejecterName = $user?->name ?? 'Người từ chối';

        $handover->update([
            'status'          => 'rejected',
            'rejected_reason' => $reason,
            'rejected_at'     => now(),
        ]);

        // Cập nhật ghi chú đơn hàng
        $orderIds = $handover->order_ids ?: ($handover->order_id ? [$handover->order_id] : []);
        if (!empty($orderIds)) {
            $orders = Order::whereIn('id', $orderIds)->get();
            foreach ($orders as $order) {
                $order->notes = ($order->notes ? $order->notes . "\n" : '') .
                    "[TỪ CHỐI BÀN GIAO NỢ]: {$rejecterName} đã từ chối nhận nợ biên bản {$handover->code}. Lý do: {$reason}";
                $order->save();
            }
        }

        // Ghi Audit Log
        AuditLog::create([
            'timestamp'            => now()->format('Y-m-d H:i:s'),
            'performer_name'       => $rejecterName,
            'user_id'              => $user?->id,
            'action'               => 'Từ Chối Bàn Giao Công Nợ',
            'module'               => 'Công Nợ & Bàn Giao',
            'target_document_code' => $handover->code,
            'field_name'           => 'Biên bản bàn giao nợ',
            'old_value'            => 'Chờ xác nhận',
            'new_value'            => 'Đã từ chối',
            'notes'                => "{$rejecterName} từ chối nhận nợ từ [{$handover->from_user_name}]. Lý do: {$reason}",
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã từ chối nhận bàn giao nợ {$handover->code}.",
            'handover' => $handover,
        ]);
    }

    /**
     * API: Lấy danh sách công nợ nhân viên & người vận chuyển
     */
    public function getStaffDebtList(Request $request)
    {
        $query = User::with('dealer')->where(function ($q) {
            $q->whereIn('staff_type', ['driver', 'company_sales', 'dealer_sales'])
              ->orWhere('department', 'LIKE', '%Kinh doanh%')
              ->orWhere('department', 'LIKE', '%Vận tải%')
              ->orWhere('current_debt', '>', 0);
        });

        if ($type = $request->input('staff_type')) {
            $query->where('staff_type', $type);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderByDesc('current_debt')->paginate(20)->withQueryString();

        $totalStaffDebt = User::sum('current_debt');
        $driverDebt = User::where('staff_type', 'driver')->sum('current_debt');
        $salesDebt = User::where('staff_type', 'company_sales')->sum('current_debt');
        $dealerSalesDebt = User::where('staff_type', 'dealer_sales')->sum('current_debt');

        return response()->json([
            'success'         => true,
            'staff'           => $staff,
            'totalStaffDebt'  => (float)$totalStaffDebt,
            'driverDebt'      => (float)$driverDebt,
            'salesDebt'       => (float)$salesDebt,
            'dealerSalesDebt' => (float)$dealerSalesDebt,
        ]);
    }

    /**
     * API: Lập Phiếu xuất hàng lẻ / lưu động lên xe cho tài xế đi bán dạo
     */
    public function createVanSaleDispatch(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $driverId = $data['driver_id'] ?? ($data['driverId'] ?? null);
        $items = $data['items'] ?? [];

        if (!$driverId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn Nhân viên nhận hàng ôm'], 422);
        }
        if (empty($items) || !is_array($items)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 mặt hàng xuất giao ôm'], 422);
        }

        $driver = User::find($driverId);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin nhân viên'], 404);
        }

        $code = 'XOM-' . date('Ymd') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);
        $vehiclePlate = $data['vehicle_plate'] ?? ($data['vehiclePlate'] ?? ('NV: ' . $driver->name));
        $warehouseId = $data['warehouse_id'] ?? ($data['warehouseId'] ?? 1);
        $notes = $data['notes'] ?? 'Xuất hàng ôm cho nhân viên đi thị trường';

        // Tìm kho nguồn & kho hàng ôm nhân viên trong bảng warehouses
        $sourceWarehouse = Warehouse::find($warehouseId) ?: Warehouse::where('code', 'KHO-MQ-01')->first();
        $sourceWhId = $sourceWarehouse?->id ?: 1;
        $sourceWhName = $sourceWarehouse?->name ?: 'Kho Tổng Mê Linh';

        // Tìm hoặc tạo kho hàng ôm tương ứng trong bảng warehouses
        $targetWh = Warehouse::where('code', 'KHO-OM-' . $driver->id)->first();
        if (!$targetWh) {
            $targetWh = Warehouse::where('manager_name', $driver->name)->where('type', 'Kho hàng ôm')->first();
        }
        if (!$targetWh && !empty($vehiclePlate) && $vehiclePlate !== 'Xe bán lưu động') {
            $targetWh = Warehouse::where('code', 'KHO-XE-' . $vehiclePlate)->first();
        }
        if (!$targetWh) {
            $targetWh = Warehouse::create([
                'code'         => 'KHO-OM-' . $driver->id,
                'name'         => 'Kho Hàng Ôm - ' . $driver->name,
                'type'         => 'Kho hàng ôm',
                'manager_name' => $driver->name,
                'address'      => 'Hàng ôm nhân viên: ' . $driver->name . ($driver->phone ? ' (' . $driver->phone . ')' : ''),
                'is_active'    => true,
            ]);
        }
        $targetWhId = $targetWh?->id;

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $dispatch = VanSaleDispatch::create([
                'code'          => $code,
                'driver_id'     => $driver->id,
                'driver_name'   => $driver->name,
                'vehicle_plate' => $vehiclePlate,
                'warehouse_id'  => $sourceWhId,
                'warehouse_name'=> $sourceWhName,
                'dispatch_date' => now()->format('Y-m-d'),
                'total_amount'  => 0,
                'status'        => 'dispatched',
                'notes'         => $notes,
                'created_by_id' => auth()->id(),
            ]);

            // Dùng chung bảng inventory_transactions: Tạo phiếu Xuất Kho Hàng Ôm
            $invTx = \App\Models\InventoryTransaction::create([
                'code'                 => $code,
                'type'                 => 'export',
                'warehouse_id'         => $sourceWhId,
                'target_warehouse_id'  => $targetWhId,
                'created_by_id'        => auth()->id() ?: 1,
                'status'               => 'Hoàn thành',
                'supplier_name'        => 'Nhân viên ôm hàng: ' . $driver->name,
                'reference_order_code' => $code,
                'total_amount'         => 0,
                'notes'                => "[Xuất Hàng Ôm Nhân Viên] " . $notes,
            ]);

            foreach ($items as $it) {
                $prodId = $it['product_id'] ?? ($it['productId'] ?? null);
                $qty = (float)($it['quantity'] ?? ($it['qty'] ?? 1));
                $price = (float)($it['unit_price'] ?? ($it['unitPrice'] ?? 0));

                $prod = Product::find($prodId);
                if ($prod) {
                    if ($price <= 0) {
                        $price = (float)($prod->price ?: ($prod->getPriceForTier('Đại lý Cấp 1', 0) ?: $prod->cost_price));
                    }
                    $lineTotal = $qty * $price;
                    $totalAmount += $lineTotal;

                    VanSaleDispatchItem::create([
                        'van_sale_dispatch_id' => $dispatch->id,
                        'product_id'           => $prod->id,
                        'sku'                  => $prod->sku,
                        'name'                 => $prod->name,
                        'unit'                 => $prod->unit ?: 'cái',
                        'quantity'             => $qty,
                        'unit_price'           => $price,
                        'amount'               => $lineTotal,
                    ]);

                    // Dùng chung bảng inventory_transaction_items
                    \App\Models\InventoryTransactionItem::create([
                        'inventory_transaction_id' => $invTx->id,
                        'product_id'               => $prod->id,
                        'quantity'                 => $qty,
                        'import_price'             => $price,
                        'cost_price_at_transaction' => $price,
                        'notes'                    => "Xuất hàng ôm cho NV {$driver->name}",
                    ]);

                    // Trừ tồn kho tổng
                    $prod->decrement('stock_quantity', $qty);

                    // Trừ tồn kho tại kho xuất nguồn trong warehouse_stocks
                    $srcStock = \App\Models\WarehouseStock::firstOrCreate(
                        ['product_id' => $prod->id, 'warehouse_id' => $sourceWhId],
                        ['stock_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $srcStock->decrement('stock_quantity', $qty);

                    // CỘNG tồn kho vào Kho hàng ôm của nhân viên trong warehouse_stocks
                    if ($targetWhId) {
                        $empStock = \App\Models\WarehouseStock::firstOrCreate(
                            ['product_id' => $prod->id, 'warehouse_id' => $targetWhId],
                            ['stock_quantity' => 0, 'reserved_quantity' => 0, 'location' => 'Hàng ôm: ' . $driver->name]
                        );
                        $empStock->increment('stock_quantity', $qty);
                    }
                }
            }

            $dispatch->total_amount = $totalAmount;
            $dispatch->save();

            $invTx->total_amount = $totalAmount;
            $invTx->save();

            // Tự động cộng tổng giá trị hàng này vào công nợ tài xế chịu trách nhiệm
            $driver->current_debt = (float)$driver->current_debt + $totalAmount;
            $driver->save();

            // Ghi Audit Log
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => auth()->user()?->name ?? 'Thủ kho',
                'user_id'              => auth()->id(),
                'action'               => 'Xuất Hàng Lẻ Lên Xe Lưu Động',
                'module'               => 'Kho & Vận Tải',
                'target_document_code' => $code,
                'field_name'           => 'Phiếu xuất hàng lẻ',
                'old_value'            => 'Tồn kho tổng',
                'new_value'            => 'Tồn kho xe & Công nợ tài xế',
                'notes'                => "Bốc hàng lẻ lên xe {$vehiclePlate} cho Lái xe [{$driver->name}]. Tổng giá trị: " . number_format($totalAmount, 0, ',', '.') . " đ (ghi nợ tài xế).",
            ]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Đã lập phiếu xuất hàng lẻ {$code} thành công! Đã tăng tồn kho xe {$vehiclePlate} và ghi nhận công nợ " . number_format($totalAmount, 0, ',', '.') . " đ cho tài xế {$driver->name}.",
                'dispatch' => $dispatch->load('items'),
                'tx_code'  => $invTx->code,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi xuất hàng lẻ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy danh sách phiếu xuất hàng lẻ trên xe
     */
    public function getVanSaleDispatches(Request $request)
    {
        $query = VanSaleDispatch::with(['items', 'driver'])->latest('id');

        if ($driverId = $request->input('driver_id')) {
            $query->where('driver_id', $driverId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $dispatches = $query->paginate(20)->withQueryString();

        return response()->json([
            'success'    => true,
            'dispatches' => $dispatches,
        ]);
    }
}
