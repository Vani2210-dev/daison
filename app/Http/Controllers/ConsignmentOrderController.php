<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentOrder;
use App\Models\ConsignmentOrderItem;
use App\Models\ConsignmentRetailOrder;
use App\Models\ConsignmentRetailOrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ConsignmentOrderController extends Controller
{
    /**
     * Dữ liệu danh mục phục vụ lập đơn hàng ôm (Nhân viên, Kho, Sản phẩm)
     */
    public function masterData()
    {
        $staff = User::where('status', 'Đang làm việc')
            ->orWhereNull('status')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'phone', 'current_debt', 'department', 'branch'])
            ->map(function ($u) {
                return [
                    'id'           => $u->id,
                    'code'         => $u->code,
                    'name'         => $u->name,
                    'phone'        => $u->phone,
                    'current_debt' => (float)($u->current_debt ?? 0),
                    'department'   => $u->department,
                ];
            });

        $warehouses = Warehouse::where('is_active', true)
            ->where('type', '!=', 'Kho xe lưu động')
            ->where('name', 'not like', 'Kho Hàng Ôm%')
            ->where('code', 'not like', 'KHO-XE-%')
            ->where('code', 'not like', 'KHO-OM-%')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'address']);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $costPrice = (float)($p->cost_price ?? $p->latest_cost_price ?? 0);
                $sellPrice = (float)($p->price ?: $costPrice);

                return [
                    'id'             => $p->id,
                    'sku'            => $p->sku,
                    'name'           => $p->name,
                    'unit'           => $p->unit ?? 'cái',
                    'cost_price'     => $costPrice,
                    'price'          => $sellPrice,
                    'sell_price'     => $sellPrice,
                    'stock_quantity' => (float)($p->stock_quantity ?? 0),
                ];
            });

        return response()->json([
            'success'    => true,
            'staff'      => $staff,
            'warehouses' => $warehouses,
            'products'   => $products,
        ]);
    }

    /**
     * Danh sách đơn hàng ôm (JSON API)
     */
    public function index(Request $request)
    {
        $query = ConsignmentOrder::with(['staff', 'items.product']);

        // Lọc theo nhân viên
        if ($staffId = $request->input('staff_id')) {
            $query->where('staff_id', $staffId);
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Tìm kiếm
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('staff_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest('id')->paginate(20)->withQueryString();

        return response()->json($orders);
    }

    /**
     * Mở trang lập đơn hàng ôm ở trang riêng
     */
    public function create(Request $request)
    {
        $currentUser = Auth::user();
        $isAccountant = $currentUser?->isAccountant() ?? false;

        $staffQuery = User::where(function ($q) {
            $q->where('status', 'Đang làm việc')->orWhereNull('status');
        })->orderBy('name');

        if (!$isAccountant && $currentUser) {
            $staffQuery->where('id', $currentUser->id);
        }

        $staff = $staffQuery->get(['id', 'code', 'name', 'phone', 'current_debt', 'department', 'branch']);

        // Chỉ lấy kho công ty, không lấy kho ôm nhân sự hay kho xe
        $warehouses = Warehouse::where('is_active', true)
            ->where('type', '!=', 'Kho xe lưu động')
            ->where('name', 'not like', 'Kho Hàng Ôm%')
            ->where('code', 'not like', 'KHO-XE-%')
            ->where('code', 'not like', 'KHO-OM-%')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'address']);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'unit', 'price', 'cost_price', 'latest_cost_price', 'stock_quantity'])
            ->map(function ($p) {
                $costPrice = (float)($p->cost_price ?? $p->latest_cost_price ?? 0);
                $sellPrice = (float)($p->price ?: $costPrice);
                return [
                    'id'             => $p->id,
                    'sku'            => $p->sku,
                    'name'           => $p->name,
                    'unit'           => $p->unit ?? 'cái',
                    'cost_price'     => $costPrice,
                    'price'          => $sellPrice,
                    'sell_price'     => $sellPrice,
                    'stock_quantity' => (float)($p->stock_quantity ?? 0),
                ];
            });

        return view('orders.consignment_create', compact('staff', 'warehouses', 'products', 'isAccountant', 'currentUser'));
    }

    /**
     * Chi tiết đơn hàng ôm
     */
    public function show($id)
    {
        $order = ConsignmentOrder::with([
            'staff',
            'items.product',
            'retailOrders.items',
        ])->findOrFail($id);

        return response()->json($order);
    }

    /**
     * Tạo đơn hàng ôm mới
     * - Nhân sự tự đặt: status = pending (chờ kế toán duyệt)
     * - Kế toán lập: tự động approved (sẵn sàng bốc hàng)
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $isAccountant = $currentUser?->isAccountant() ?? false;

        $staffId = ($isAccountant && $request->filled('staff_id'))
            ? $request->input('staff_id')
            : ($currentUser?->id ?? $request->input('staff_id'));

        $request->merge(['staff_id' => $staffId]);

        $request->validate([
            'staff_id'    => 'required|exists:users,id',
            'warehouse_id'=> 'nullable|exists:warehouses,id',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'notes'       => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $staffId, $currentUser, $isAccountant) {
            $staff = User::findOrFail($staffId);
            $warehouse = $request->warehouse_id ? Warehouse::find($request->warehouse_id) : null;

            $status = $isAccountant ? 'approved' : 'pending';
            $approvedBy = $isAccountant ? $currentUser?->id : null;
            $approvedByName = $isAccountant ? $currentUser?->name : null;
            $approvedAt = $isAccountant ? now() : null;

            $order = ConsignmentOrder::create([
                'code'               => ConsignmentOrder::generateCode(),
                'staff_id'           => $staff->id,
                'staff_name'         => $staff->name,
                'warehouse_id'       => $warehouse?->id,
                'warehouse_name'     => $warehouse?->name,
                'status'             => $status,
                'picking_date'       => now()->format('Y-m-d'),
                'delivery_date'      => now()->format('Y-m-d'),
                'notes'              => $request->notes,
                'created_by_id'      => $currentUser?->id,
                'approved_by'        => $approvedBy,
                'approved_by_name'   => $approvedByName,
                'approved_at'        => $approvedAt,
                'stock_deducted'     => false,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $unitPrice = isset($itemData['unit_price']) ? (float)$itemData['unit_price'] : (float)($product->cost_price ?: $product->price ?: 0);
                $sellPrice = isset($itemData['sell_price']) ? (float)$itemData['sell_price'] : (float)($product->price ?: $unitPrice);
                $quantity = (float) $itemData['quantity'];
                $amount = $unitPrice * $quantity;

                ConsignmentOrderItem::create([
                    'consignment_order_id' => $order->id,
                    'product_id'           => $product->id,
                    'sku'                  => $product->sku ?? '',
                    'name'                 => $product->name,
                    'unit'                 => $itemData['unit'] ?? $product->unit ?? 'cái',
                    'quantity'             => $quantity,
                    'unit_price'           => $unitPrice,
                    'sell_price'           => $sellPrice,
                    'amount'               => $amount,
                    'sold_quantity'        => 0,
                    'returned_quantity'    => 0,
                    'remaining_quantity'   => $quantity,
                ]);

                $totalAmount += $amount;
            }

            $order->update([
                'total_amount'     => $totalAmount,
                'remaining_amount' => $totalAmount,
            ]);

            $order->load('items.product');

            $msg = $isAccountant
                ? "Tạo đơn hàng ôm {$order->code} thành công! Đơn đã tự động được duyệt và chuyển sang bộ phận bốc hàng."
                : "Tạo đơn hàng ôm {$order->code} thành công! Đơn đã gửi chờ Kế toán duyệt trước khi chuyển sang bộ phận bốc hàng.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => $msg,
                    'order'    => $order,
                    'redirect' => route('orders.index', ['tab' => 'consignments']),
                ]);
            }

            return redirect()->route('orders.index', ['tab' => 'consignments'])->with('success', $msg);
        });
    }

    /**
     * Kế toán duyệt đơn hàng ôm → Chuyển sang bốc hàng (approved)
     * Khi duyệt xong sẽ hiển thị bên màn hình bốc hàng (/inventory/picking)
     */
    public function approve($id, Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->isAccountant()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền duyệt đơn hàng ôm. Quyền này chỉ dành riêng cho Kế toán / Ban Quản Lý!'
            ], 403);
        }

        return DB::transaction(function () use ($id, $currentUser) {
            $order = ConsignmentOrder::with('items')->findOrFail($id);

            if (!in_array($order->status, ['pending', 'draft'])) {
                return response()->json(['success' => false, 'message' => 'Đơn không ở trạng thái chờ duyệt!'], 422);
            }

            // Cập nhật trạng thái sang approved (hiển thị ngay bên bốc hàng)
            $order->update([
                'status'           => 'approved',
                'approved_by'      => $currentUser->id,
                'approved_by_name' => $currentUser->name,
                'approved_at'      => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Kế toán duyệt đơn {$order->code} thành công! Đơn đã được chuyển sang bộ phận bốc hàng.",
                'order'   => $order,
            ]);
        });
    }

    /**
     * Xuất kho trực tiếp vào kho ôm nhân viên (dành cho Kế toán / Thủ kho)
     */
    public function dispatchStock($id, Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->isAccountant()) {
            return response()->json(['success' => false, 'message' => 'Chỉ Kế toán / Thủ kho mới có quyền xác nhận xuất kho!'], 403);
        }

        $order = ConsignmentOrder::with(['items', 'warehouse'])->findOrFail($id);
        if ($order->stock_deducted) {
            return response()->json(['success' => false, 'message' => 'Đơn này đã được xuất kho và cộng vào kho ôm của NV!'], 422);
        }

        $order->transferStockToEmployeeHoldingWarehouse($currentUser->id, $currentUser->name);

        return response()->json([
            'success' => true,
            'message' => "Đã xuất kho cho đơn {$order->code}! Hàng đã được cộng vào Kho Hàng Ôm của nhân viên {$order->staff_name}.",
        ]);
    }

    /**
     * Từ chối đơn hàng ôm
     */
    public function reject($id, Request $request)
    {
        $request->validate(['reason' => 'nullable|string']);

        $order = ConsignmentOrder::findOrFail($id);

        if (!in_array($order->status, ['pending', 'draft'])) {
            return response()->json(['success' => false, 'message' => 'Đơn không ở trạng thái chờ duyệt!'], 422);
        }

        $order->update([
            'status'          => 'cancelled',
            'rejected_reason' => $request->reason ?? 'Không duyệt',
            'rejected_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã từ chối đơn {$order->code}.",
        ]);
    }

    /**
     * Lên đơn bán lẻ từ hàng ôm
     */
    public function createRetailOrder($id, Request $request)
    {
        $request->validate([
            'customer_name'  => 'nullable|string|max:200',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.consignment_order_item_id' => 'required|exists:consignment_order_items,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        return DB::transaction(function () use ($id, $request) {
            $consignmentOrder = ConsignmentOrder::with('items')->findOrFail($id);

            if (!in_array($consignmentOrder->status, ['in_progress', 'approved'])) {
                return response()->json(['success' => false, 'message' => 'Đơn ôm không ở trạng thái đang bán!'], 422);
            }

            $retailOrder = ConsignmentRetailOrder::create([
                'code'                  => ConsignmentRetailOrder::generateCode(),
                'consignment_order_id'  => $consignmentOrder->id,
                'staff_id'              => $consignmentOrder->staff_id,
                'customer_name'         => $request->customer_name,
                'customer_phone'        => $request->customer_phone,
                'customer_address'      => $request->customer_address,
                'payment_method'        => $request->payment_method ?? 'cash',
                'payment_status'        => 'paid',
                'status'                => 'completed',
                'notes'                 => $request->notes,
                'sold_at'               => now(),
            ]);

            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                $consignmentItem = ConsignmentOrderItem::findOrFail($itemData['consignment_order_item_id']);
                $quantity = (float) $itemData['quantity'];

                // Kiểm tra SL ôm còn lại
                if ($quantity > (float) $consignmentItem->remaining_quantity) {
                    throw new \Exception("Sản phẩm \"{$consignmentItem->name}\" không đủ (còn: {$consignmentItem->remaining_quantity}, cần bán: {$quantity})");
                }

                $unitPrice = $itemData['unit_price'] ?? $consignmentItem->sell_price;
                $amount = $unitPrice * $quantity;

                ConsignmentRetailOrderItem::create([
                    'consignment_retail_order_id' => $retailOrder->id,
                    'consignment_order_item_id'   => $consignmentItem->id,
                    'product_id'                  => $consignmentItem->product_id,
                    'sku'                         => $consignmentItem->sku,
                    'name'                        => $consignmentItem->name,
                    'unit'                        => $consignmentItem->unit,
                    'quantity'                    => $quantity,
                    'unit_price'                  => $unitPrice,
                    'amount'                      => $amount,
                ]);

                // Cập nhật SL đã bán & còn lại trên item ôm
                $consignmentItem->increment('sold_quantity', $quantity);
                $consignmentItem->decrement('remaining_quantity', $quantity);

                $totalAmount += $amount;
            }

            $retailOrder->update(['total_amount' => $totalAmount]);

            // Cập nhật tổng đã bán trên đơn ôm
            $consignmentOrder->increment('sold_amount', $totalAmount);
            $consignmentOrder->remaining_amount = (float)$consignmentOrder->total_amount - (float)$consignmentOrder->sold_amount - (float)$consignmentOrder->returned_amount;
            $consignmentOrder->save();

            // Đồng bộ số dư tồn kho ôm của nhân viên
            ConsignmentOrder::recalculateEmployeeHoldingStock($consignmentOrder->staff_id);

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn bán lẻ thành công!',
                'retail_order' => $retailOrder->load('items'),
            ]);
        });
    }

    /**
     * NV trả hàng thừa từ đơn ôm
     */
    public function returnItems($id, Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.consignment_order_item_id' => 'required|exists:consignment_order_items,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
        ]);

        return DB::transaction(function () use ($id, $request) {
            $order = ConsignmentOrder::with('items')->findOrFail($id);

            if (!in_array($order->status, ['in_progress', 'approved'])) {
                return response()->json(['success' => false, 'message' => 'Đơn ôm không ở trạng thái đang bán!'], 422);
            }

            $returnedAmount = 0;

            foreach ($request->items as $itemData) {
                $consignmentItem = ConsignmentOrderItem::findOrFail($itemData['consignment_order_item_id']);
                $quantity = (float) $itemData['quantity'];

                if ($quantity > (float) $consignmentItem->remaining_quantity) {
                    throw new \Exception("Trả quá SL còn lại của \"{$consignmentItem->name}\" (còn: {$consignmentItem->remaining_quantity})");
                }

                // Cộng lại tồn kho
                if ($order->warehouse_id) {
                    WarehouseStock::firstOrCreate(
                        ['warehouse_id' => $order->warehouse_id, 'product_id' => $consignmentItem->product_id],
                        ['stock_quantity' => 0]
                    )->increment('stock_quantity', $quantity);
                }
                Product::where('id', $consignmentItem->product_id)
                    ->increment('stock_quantity', $quantity);

                // Cập nhật item
                $consignmentItem->increment('returned_quantity', $quantity);
                $consignmentItem->decrement('remaining_quantity', $quantity);

                $returnedAmount += $consignmentItem->unit_price * $quantity;
            }

            // Giảm công nợ NV
            $staff = User::findOrFail($order->staff_id);
            $staff->decrement('current_debt', $returnedAmount);

            // Cập nhật tổng trả lại trên đơn ôm
            $order->increment('returned_amount', $returnedAmount);
            $order->remaining_amount = (float)$order->total_amount - (float)$order->sold_amount - (float)$order->returned_amount;
            $order->save();

            // Đồng bộ số dư tồn kho ôm của nhân viên
            ConsignmentOrder::recalculateEmployeeHoldingStock($order->staff_id);

            return response()->json([
                'success' => true,
                'message' => 'Trả hàng thành công! Đã giảm ' . number_format($returnedAmount) . 'đ công nợ NV.',
            ]);
        });
    }

    /**
     * Quyết toán đơn ôm
     */
    public function settle($id)
    {
        return DB::transaction(function () use ($id) {
            $order = ConsignmentOrder::with('items')->findOrFail($id);

            if ($order->status !== 'in_progress') {
                return response()->json(['success' => false, 'message' => 'Đơn ôm chưa ở trạng thái đang bán!'], 422);
            }

            // Kiểm tra còn hàng tồn ôm chưa trả
            $remainingItems = $order->items->where('remaining_quantity', '>', 0);
            if ($remainingItems->count() > 0) {
                $names = $remainingItems->pluck('name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Vẫn còn hàng chưa bán/trả: {$names}. Vui lòng trả hàng thừa trước khi quyết toán!",
                ], 422);
            }

            // Trừ công nợ NV (phần đã bán)
            $staff = User::findOrFail($order->staff_id);
            $soldAmount = (float) $order->sold_amount;
            if ($soldAmount > 0) {
                $staff->decrement('current_debt', $soldAmount);
            }

            $user = Auth::user();
            $order->update([
                'status'          => 'settled',
                'settled_at'      => now(),
                'settled_by'      => $user?->id,
                'settled_by_name' => $user?->name,
                'remaining_amount' => 0,
            ]);

            // Đồng bộ số dư tồn kho ôm của nhân viên
            ConsignmentOrder::recalculateEmployeeHoldingStock($order->staff_id);

            return response()->json([
                'success' => true,
                'message' => "Quyết toán đơn {$order->code} thành công! Đã trừ " . number_format($soldAmount) . "đ công nợ NV {$staff->name}.",
            ]);
        });
    }

    /**
     * Danh sách đơn bán lẻ của 1 đơn ôm
     */
    public function retailOrders($id)
    {
        $retailOrders = ConsignmentRetailOrder::with('items')
            ->where('consignment_order_id', $id)
            ->latest('id')
            ->get();

        return response()->json($retailOrders);
    }
}
