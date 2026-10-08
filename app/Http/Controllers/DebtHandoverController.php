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

        $orderId = $data['order_id'] ?? ($data['orderId'] ?? null);
        $orderCode = $data['order_code'] ?? ($data['orderCode'] ?? null);
        $toUserId = $data['to_user_id'] ?? ($data['toUserId'] ?? null);
        $targetDealerId = $data['target_dealer_id'] ?? ($data['targetDealerId'] ?? null);
        $amount = (float)($data['amount'] ?? 0);
        $notes = $data['notes'] ?? '';

        $order = null;
        if ($orderId || $orderCode) {
            $order = Order::where(function ($q) use ($orderId, $orderCode) {
                if ($orderId) $q->where('id', $orderId)->orWhere('order_code', $orderId);
                if ($orderCode) $q->orWhere('order_code', $orderCode);
            })->first();
        }

        if ($order && $amount <= 0) {
            $amount = (float)($order->driver_debt_amount > 0 ? $order->driver_debt_amount : $order->total_amount);
        }

        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Số tiền công nợ bàn giao phải lớn hơn 0'], 422);
        }

        // Xác định người bàn giao (thường là Lái xe đang đăng nhập hoặc người phụ trách đơn)
        $fromUser = auth()->user();
        $fromUserId = $fromUser?->id;
        $fromUserName = $fromUser?->name ?? 'Người vận chuyển';
        if ($order && $order->assigned_driver_id && $fromUserId != $order->assigned_driver_id && !$fromUser?->hasRole('admin')) {
            $driverUser = User::find($order->assigned_driver_id);
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
        } elseif ($order && $order->dealer_id) {
            $targetDealer = Dealer::find($order->dealer_id);
            $targetDealerId = $targetDealer?->id;
        }

        $code = 'BGCN-' . date('Ymd') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $handover = DebtHandover::create([
                'code'               => $code,
                'order_id'           => $order?->id,
                'order_code'         => $order?->order_code ?? $orderCode,
                'from_user_id'       => $fromUserId,
                'from_user_name'     => $fromUserName,
                'to_user_id'         => $toUser?->id,
                'to_user_name'       => $toUserName ?? 'Chưa chỉ định',
                'to_staff_type'      => $toStaffType,
                'target_dealer_id'   => $targetDealerId,
                'target_dealer_name' => $targetDealer?->name,
                'amount'             => $amount,
                'handover_type'      => $order ? 'order_debt' : 'manual',
                'status'             => 'pending',
                'notes'              => $notes,
                'created_by_id'      => auth()->id(),
            ]);

            // Cập nhật trạng thái đơn hàng (nếu có)
            if ($order) {
                $order->notes = ($order->notes ? $order->notes . "\n" : '') .
                    "[BÀN GIAO NỢ CHỜ DUYỆT]: {$fromUserName} đã gửi biên bản {$code} bàn giao " . number_format($amount, 0, ',', '.') . " đ cho [" . ($toUserName ?: $targetDealer?->name) . "].";
                $order->save();
            }

            // Ghi Audit Log
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $fromUserName,
                'user_id'              => auth()->id(),
                'action'               => 'Lập Biên Bản Bàn Giao Công Nợ',
                'module'               => 'Công Nợ & Bàn Giao',
                'target_document_code' => $code,
                'field_name'           => 'Biên bản bàn giao nợ',
                'old_value'            => 'Lái xe chịu trách nhiệm',
                'new_value'            => 'Chờ xác nhận nhận nợ',
                'notes'                => "{$fromUserName} bàn giao công nợ " . number_format($amount, 0, ',', '.') . " đ (Đơn " . ($order?->order_code ?? 'N/A') . ") cho [{$toUserName}].",
            ]);

            // Gửi thông báo đến người nhận
            if ($toUser) {
                Announcement::create([
                    'title'             => "📑 Yêu cầu nhận bàn giao công nợ: {$code} - " . number_format($amount, 0, ',', '.') . " đ",
                    'content'           => "{$fromUserName} vừa gửi biên bản bàn giao công nợ số {$code} trị giá " . number_format($amount, 0, ',', '.') . " đ" . ($order ? " (Đơn hàng {$order->order_code})" : '') . " cho bạn.\nVui lòng vào mục Quản lý Công nợ để bấm 'Xác nhận nhận nợ' hoặc từ chối.",
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
            // TH A: Sale đại lý phụ thuộc -> Ghi nợ trực tiếp vào Đại lý phụ thuộc
            if ($handover->to_staff_type === 'dealer_sales' || $handover->target_dealer_id) {
                $dealerId = $handover->target_dealer_id;
                if (!$dealerId && $handover->to_user_id) {
                    $dealerId = User::find($handover->to_user_id)?->dealer_id;
                }
                if ($dealerId) {
                    $dealer = Dealer::find($dealerId);
                    if ($dealer) {
                        $dealer->current_debt = (float)$dealer->current_debt + $amount;
                        $dealer->save();
                    }
                }
            } else {
                // TH B: Sale công ty -> Ghi nợ vào công nợ của Sale công ty
                if ($handover->to_user_id) {
                    $toUser = User::find($handover->to_user_id);
                    if ($toUser) {
                        $toUser->current_debt = (float)$toUser->current_debt + $amount;
                        $toUser->save();
                    }
                }
            }

            // 3. Cập nhật đơn hàng (nếu có): giải phóng trách nhiệm lái xe
            if ($handover->order_id) {
                $order = Order::find($handover->order_id);
                if ($order) {
                    $order->driver_debt_amount = max(0, (float)$order->driver_debt_amount - $amount);
                    $order->payment_status = 'Ghi nợ đại lý / Sale';
                    $order->notes = ($order->notes ? $order->notes . "\n" : '') .
                        "[ĐÃ XÁC NHẬN BÀN GIAO NỢ]: {$approverName} đã xác nhận nhận công nợ " . number_format($amount, 0, ',', '.') . " đ theo biên bản {$handover->code}.";
                    $order->save();
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
        if ($handover->order_id) {
            $order = Order::find($handover->order_id);
            if ($order) {
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
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn Người vận chuyển / Tài xế'], 422);
        }
        if (empty($items) || !is_array($items)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 mặt hàng bốc lên xe'], 422);
        }

        $driver = User::find($driverId);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin tài xế'], 404);
        }

        $code = 'XHL-' . date('Ymd') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);
        $vehiclePlate = $data['vehicle_plate'] ?? ($data['vehiclePlate'] ?? 'Xe bán lưu động');
        $warehouseId = $data['warehouse_id'] ?? ($data['warehouseId'] ?? 1);
        $notes = $data['notes'] ?? 'Hàng lẻ bốc lên xe đi bán dạo';

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $dispatch = VanSaleDispatch::create([
                'code'          => $code,
                'driver_id'     => $driver->id,
                'driver_name'   => $driver->name,
                'vehicle_plate' => $vehiclePlate,
                'warehouse_id'  => $warehouseId,
                'warehouse_name'=> 'Kho Tổng Mê Linh',
                'dispatch_date' => now()->format('Y-m-d'),
                'total_amount'  => 0,
                'status'        => 'dispatched',
                'notes'         => $notes,
                'created_by_id' => auth()->id(),
            ]);

            foreach ($items as $it) {
                $prodId = $it['product_id'] ?? ($it['productId'] ?? null);
                $qty = (float)($it['quantity'] ?? ($it['qty'] ?? 1));
                $price = (float)($it['unit_price'] ?? ($it['unitPrice'] ?? 0));

                $prod = Product::find($prodId);
                if ($prod) {
                    if ($price <= 0) {
                        $price = (float)($prod->getPriceForTier('Đại lý Cấp 1', 0) ?: $prod->cost_price);
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

                    // Trừ kho thực tế
                    $prod->decrement('stock_quantity', $qty);
                }
            }

            $dispatch->total_amount = $totalAmount;
            $dispatch->save();

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
                'old_value'            => 'Tồn kho',
                'new_value'            => 'Công nợ tài xế',
                'notes'                => "Bốc hàng lẻ lên xe {$vehiclePlate} cho Lái xe [{$driver->name}]. Tổng giá trị: " . number_format($totalAmount, 0, ',', '.') . " đ (ghi nợ tài xế).",
            ]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Đã lập phiếu xuất hàng lẻ {$code} thành công! Đã ghi nhận công nợ " . number_format($totalAmount, 0, ',', '.') . " đ cho tài xế {$driver->name}.",
                'dispatch' => $dispatch->load('items'),
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
