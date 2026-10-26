<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierDebtLog;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\ProductBatch;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierOrderController extends Controller
{
    /**
     * Display Supplier Orders listing / Tab view
     */
    public function index(Request $request)
    {
        $query = SupplierOrder::with(['supplier', 'warehouse', 'creator', 'items.product', 'debtLogs'])
            ->latest('id');

        // Filter: Status
        if ($status = $request->input('status')) {
            if ($status === 'Chờ duyệt') {
                $query->whereIn('status', ['Chờ duyệt', 'draft', 'pending_approval']);
            } elseif ($status === 'Đã duyệt') {
                $query->whereIn('status', ['Đã duyệt', 'approved']);
            } elseif ($status === 'Đang giao hàng') {
                $query->whereIn('status', ['Đang giao hàng', 'in_transit', 'shipping']);
            } elseif ($status === 'Nhập một phần') {
                $query->whereIn('status', ['Nhập một phần', 'partially_received']);
            } elseif ($status === 'Hoàn thành') {
                $query->whereIn('status', ['Hoàn thành', 'received', 'completed']);
            } elseif ($status === 'Đã hủy') {
                $query->whereIn('status', ['Đã hủy', 'cancelled']);
            } else {
                $query->where('status', $status);
            }
        }

        // Filter: Payment Status
        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        // Filter: Supplier
        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        // Filter: Search (code, supplier name, notes)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('product_name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter: Date Range
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('order_date', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('order_date', '<=', $toDate);
        }

        $orders = $query->paginate(20)->withQueryString();

        // Counts for Status Tabs
        $counts = [
            'Tất cả'          => SupplierOrder::count(),
            'Chờ duyệt'       => SupplierOrder::whereIn('status', ['Chờ duyệt', 'draft', 'pending_approval'])->count(),
            'Đã duyệt'        => SupplierOrder::whereIn('status', ['Đã duyệt', 'approved'])->count(),
            'Đang giao hàng'  => SupplierOrder::whereIn('status', ['Đang giao hàng', 'in_transit', 'shipping'])->count(),
            'Nhập một phần'   => SupplierOrder::whereIn('status', ['Nhập một phần', 'partially_received'])->count(),
            'Hoàn thành'      => SupplierOrder::whereIn('status', ['Hoàn thành', 'received', 'completed'])->count(),
            'Đã hủy'          => SupplierOrder::whereIn('status', ['Đã hủy', 'cancelled'])->count(),
        ];

        // Overall statistics
        $stats = [
            'total_value'          => (float) SupplierOrder::where('status', '!=', 'Đã hủy')->sum('total_amount'),
            'total_paid'           => (float) SupplierOrder::where('status', '!=', 'Đã hủy')->sum('paid_amount'),
            'total_debt_deducted'  => (float) SupplierOrder::where('status', '!=', 'Đã hủy')->sum('debt_deducted_amount'),
            'total_supplier_debt'  => (float) Supplier::sum('supplier_debt_to_company'),
            'pending_receive_count'=> SupplierOrder::whereIn('status', ['Đã duyệt', 'Đang giao hàng', 'Nhập một phần'])->count(),
        ];

        // Master Data
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::companyWarehouses()
            ->orderByRaw("CASE WHEN type = 'Kho tổng' THEN 1 WHEN type = 'Kho chi nhánh' THEN 2 ELSE 3 END, name ASC")
            ->get(['id', 'code', 'name', 'address']);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'unit', 'cost_price', 'latest_cost_price', 'stock_quantity'])
            ->map(fn($p) => [
                'id'             => $p->id,
                'sku'            => $p->sku,
                'name'           => $p->name,
                'unit'           => $p->unit ?: 'cái',
                'cost_price'     => (float)($p->latest_cost_price ?: $p->cost_price ?: 0),
                'stock_quantity' => (float)($p->stock_quantity ?? 0),
            ]);

        if ($request->wantsJson() && !$request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'orders'  => $orders,
                'counts'  => $counts,
                'stats'   => $stats,
            ]);
        }

        // Return view - can be embedded in /orders?tab=suppliers or standalone /supplier-orders
        return view('orders.supplier_orders_index', compact('orders', 'counts', 'stats', 'suppliers', 'warehouses', 'products'));
    }

    /**
     * Mở trang lập đơn đặt hàng NCC ở trang riêng (Dedicated Full Page)
     */
    public function create(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::companyWarehouses()
            ->orderByRaw("CASE WHEN type = 'Kho tổng' THEN 1 WHEN type = 'Kho chi nhánh' THEN 2 ELSE 3 END, name ASC")
            ->get(['id', 'code', 'name', 'address']);

        $products = Product::where('is_active', true)
            ->with('brand:id,name')
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'unit', 'cost_price', 'latest_cost_price', 'stock_quantity', 'brand_id'])
            ->map(fn($p) => [
                'id'             => $p->id,
                'sku'            => $p->sku,
                'name'           => $p->name,
                'unit'           => $p->unit ?: 'cái',
                'cost_price'     => (float)($p->latest_cost_price ?: $p->cost_price ?: 0),
                'stock_quantity' => (float)($p->stock_quantity ?? 0),
                'brand_name'     => $p->brand?->name ?? '',
            ]);

        $selectedSupplierId = $request->input('supplier_id');

        return view('orders.supplier_order_create', compact('suppliers', 'warehouses', 'products', 'selectedSupplierId'));
    }

    /**
     * Mở trang tạo phiếu nhập kho ở trang riêng (Dedicated Full Page)
     */
    public function receiptPage($id)
    {
        $order = SupplierOrder::with(['items.product', 'supplier', 'warehouse'])->findOrFail($id);

        $warehouses = Warehouse::companyWarehouses()
            ->orderByRaw("CASE WHEN type = 'Kho tổng' THEN 1 WHEN type = 'Kho chi nhánh' THEN 2 ELSE 3 END, name ASC")
            ->get(['id', 'code', 'name', 'address']);

        return view('orders.supplier_order_receipt', compact('order', 'warehouses'));
    }

    /**
     * Store new Supplier Order (Đặt hàng NCC)
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'warehouse_id'         => 'nullable|exists:warehouses,id',
            'order_date'           => 'required|date',
            'expected_delivery_date'=> 'nullable|date',
            'payment_status'       => 'required|in:Chưa thanh toán,Thanh toán 1 phần,Đã thanh toán',
            'items'                => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.ordered_quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'debt_deducted_amount' => 'nullable|numeric|min:0',
            'paid_amount'          => 'nullable|numeric|min:0',
        ], [
            'supplier_id.required' => 'Vui lòng chọn Nhà cung cấp',
            'items.required'       => 'Vui lòng thêm ít nhất một sản phẩm cần đặt hàng',
            'items.min'            => 'Vui lòng thêm ít nhất một sản phẩm cần đặt hàng',
            'items.*.ordered_quantity.min' => 'Số lượng đặt hàng phải lớn hơn 0',
        ]);

        DB::beginTransaction();
        try {
            $supplier = Supplier::findOrFail($request->supplier_id);
            $currentUser = Auth::user();

            // 1. Sinh mã đơn đặt hàng: ĐHNCC-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $countToday = SupplierOrder::whereDate('created_at', today())->count() + 1;
            $code = 'ĐHNCC-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
            while (SupplierOrder::where('code', $code)->exists()) {
                $countToday++;
                $code = 'ĐHNCC-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
            }

            // 2. Tính toán tiền hàng
            $totalItemsAmount = 0;
            foreach ($request->items as $it) {
                $qty = (float)($it['ordered_quantity'] ?? 1);
                $price = (float)($it['unit_price'] ?? 0);
                $disc = (float)($it['discount_amount'] ?? 0);
                $totalItemsAmount += ($qty * $price - $disc);
            }

            $discountAmount = (float)($request->discount_amount ?? 0);
            $otherFees = (float)($request->other_fees ?? 0);
            $totalAmount = max(0, $totalItemsAmount - $discountAmount + $otherFees);

            // 3. Xử lý Trừ nợ từ công nợ NCC nợ công ty
            $debtDeductedAmount = (float)($request->debt_deducted_amount ?? 0);
            $currentSupplierDebt = (float)$supplier->supplier_debt_to_company;

            if ($debtDeductedAmount > 0) {
                // Không được trừ nợ vượt quá số tiền NCC đang nợ
                if ($debtDeductedAmount > $currentSupplierDebt) {
                    $debtDeductedAmount = $currentSupplierDebt;
                }
                // Không được trừ nợ vượt quá tổng giá trị đơn hàng
                if ($debtDeductedAmount > $totalAmount) {
                    $debtDeductedAmount = $totalAmount;
                }

                // Cập nhật số dư nợ của NCC
                $balanceBefore = $supplier->supplier_debt_to_company;
                $supplier->supplier_debt_to_company = max(0, $supplier->supplier_debt_to_company - $debtDeductedAmount);
                $supplier->save();

                // Ghi log biến động công nợ
                SupplierDebtLog::create([
                    'supplier_id'       => $supplier->id,
                    'supplier_order_id' => null, // Sẽ update sau khi create order
                    'type'              => 'deduct',
                    'amount'            => $debtDeductedAmount,
                    'balance_before'    => $balanceBefore,
                    'balance_after'     => $supplier->supplier_debt_to_company,
                    'reason'            => "Cấn trừ nợ vào đơn đặt hàng {$code}",
                    'created_by_id'     => $currentUser?->id,
                    'created_by_name'   => $currentUser?->name,
                ]);
            }

            // 4. Số tiền còn lại phải thanh toán
            $payableAmount = max(0, $totalAmount - $debtDeductedAmount);

            // 5. Xác định số tiền thực tế đã thanh toán dựa theo trạng thái
            $paymentStatus = $request->payment_status;
            $paidAmount = 0;

            if ($paymentStatus === 'Đã thanh toán') {
                $paidAmount = $payableAmount;
            } elseif ($paymentStatus === 'Chưa thanh toán') {
                $paidAmount = 0;
            } elseif ($paymentStatus === 'Thanh toán 1 phần') {
                $inputPaid = (float)($request->paid_amount ?? 0);
                $paidAmount = min($payableAmount, max(0, $inputPaid));
                if ($paidAmount >= $payableAmount && $payableAmount > 0) {
                    $paymentStatus = 'Đã thanh toán';
                } elseif ($paidAmount <= 0) {
                    $paymentStatus = 'Chưa thanh toán';
                }
            }

            // Kho nhập
            $warehouse = $request->warehouse_id ? Warehouse::find($request->warehouse_id) : null;

            // 6. Tạo SupplierOrder
            $order = SupplierOrder::create([
                'code'                   => $code,
                'supplier_id'            => $supplier->id,
                'supplier_name'          => $supplier->name,
                'warehouse_id'           => $warehouse?->id,
                'warehouse_name'         => $warehouse?->name,
                'creator_id'             => $currentUser?->id,
                'creator_name'           => $currentUser?->name,
                'approver_id'            => $request->boolean('auto_approve') ? $currentUser?->id : null,
                'approver_name'          => $request->boolean('auto_approve') ? $currentUser?->name : null,
                'order_date'             => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status'                 => $request->boolean('auto_approve') ? 'Đã duyệt' : 'Chờ duyệt',
                'payment_status'         => $paymentStatus,
                'total_items_amount'     => $totalItemsAmount,
                'discount_amount'        => $discountAmount,
                'other_fees'             => $otherFees,
                'total_amount'           => $totalAmount,
                'debt_deducted_amount'   => $debtDeductedAmount,
                'payable_amount'         => $payableAmount,
                'paid_amount'            => $paidAmount,
                'payment_method'         => $request->payment_method ?: 'Chuyển khoản',
                'payment_note'           => $request->payment_note,
                'notes'                  => $request->notes,
            ]);

            // Cập nhật supplier_order_id trong debt log nếu có
            if ($debtDeductedAmount > 0) {
                SupplierDebtLog::where('supplier_id', $supplier->id)
                    ->where('reason', 'like', "%{$code}%")
                    ->whereNull('supplier_order_id')
                    ->update(['supplier_order_id' => $order->id]);
            }

            // 7. Tạo SupplierOrderItems
            foreach ($request->items as $it) {
                $qty = (float)($it['ordered_quantity'] ?? 1);
                $price = (float)($it['unit_price'] ?? 0);
                $discAmt = (float)($it['discount_amount'] ?? 0);
                $discPct = (float)($it['discount_percent'] ?? 0);
                if ($discPct > 0 && $discAmt == 0) {
                    $discAmt = ($qty * $price) * ($discPct / 100);
                }
                $amount = ($qty * $price) - $discAmt;

                SupplierOrderItem::create([
                    'supplier_order_id'  => $order->id,
                    'product_id'         => $it['product_id'] ?? null,
                    'product_name'       => $it['product_name'],
                    'sku'                => $it['sku'] ?? null,
                    'unit'               => $it['unit'] ?? 'cái',
                    'ordered_quantity'   => $qty,
                    'received_quantity'  => 0,
                    'remaining_quantity' => $qty,
                    'unit_price'         => $price,
                    'discount_percent'   => $discPct,
                    'discount_amount'    => $discAmt,
                    'amount'             => $amount,
                    'notes'              => $it['notes'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Tạo đơn đặt hàng NCC {$code} thành công!",
                'order'   => $order->load(['supplier', 'items']),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("SupplierOrder Store Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo đơn đặt hàng: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single order details
     */
    public function show($id)
    {
        $order = SupplierOrder::with([
            'supplier',
            'warehouse',
            'creator',
            'approver',
            'items.product',
            'debtLogs',
            'inventoryTransactions.items.product'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'order'   => $order,
        ]);
    }

    /**
     * Duyệt đơn đặt hàng
     */
    public function approve($id)
    {
        $order = SupplierOrder::findOrFail($id);
        if ($order->status !== 'Chờ duyệt') {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không ở trạng thái Chờ duyệt.',
            ], 422);
        }

        $order->status = 'Đã duyệt';
        $order->approver_id = Auth::id();
        $order->approver_name = Auth::user()?->name;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => "Đã duyệt đơn đặt hàng {$order->code} thành công!",
            'order'   => $order,
        ]);
    }

    /**
     * Cập nhật trạng thái thanh toán hoặc bổ sung thanh toán
     */
    public function updatePayment(Request $request, $id)
    {
        $order = SupplierOrder::findOrFail($id);

        $request->validate([
            'payment_status' => 'required|in:Chưa thanh toán,Thanh toán 1 phần,Đã thanh toán',
            'paid_amount'    => 'nullable|numeric|min:0',
        ]);

        $status = $request->payment_status;
        $payable = (float)$order->payable_amount;
        $paid = (float)$order->paid_amount;

        if ($status === 'Đã thanh toán') {
            $paid = $payable;
        } elseif ($status === 'Chưa thanh toán') {
            $paid = 0;
        } else {
            $inputPaid = (float)($request->paid_amount ?? 0);
            $paid = min($payable, max(0, $inputPaid));
            if ($paid >= $payable && $payable > 0) {
                $status = 'Đã thanh toán';
            } elseif ($paid <= 0) {
                $status = 'Chưa thanh toán';
            }
        }

        $order->payment_status = $status;
        $order->paid_amount = $paid;
        if ($request->payment_method) {
            $order->payment_method = $request->payment_method;
        }
        if ($request->payment_note) {
            $order->payment_note = $request->payment_note;
        }
        $order->save();

        return response()->json([
            'success' => true,
            'message' => "Cập nhật thanh toán đơn {$order->code} thành công!",
            'order'   => $order,
        ]);
    }

    /**
     * Hủy đơn đặt hàng NCC
     */
    public function cancel(Request $request, $id)
    {
        $order = SupplierOrder::with('supplier')->findOrFail($id);

        if (in_array($order->status, ['Hoàn thành', 'Nhập một phần'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy đơn hàng đã nhập kho một phần hoặc hoàn thành.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Nếu đơn hàng có trừ nợ NCC, hoàn trả lại số tiền trừ nợ cho NCC
            if ($order->debt_deducted_amount > 0 && $order->supplier) {
                $supplier = $order->supplier;
                $balanceBefore = $supplier->supplier_debt_to_company;
                $supplier->supplier_debt_to_company += (float)$order->debt_deducted_amount;
                $supplier->save();

                SupplierDebtLog::create([
                    'supplier_id'       => $supplier->id,
                    'supplier_order_id' => $order->id,
                    'type'              => 'refund',
                    'amount'            => $order->debt_deducted_amount,
                    'balance_before'    => $balanceBefore,
                    'balance_after'     => $supplier->supplier_debt_to_company,
                    'reason'            => "Hoàn trả nợ do hủy đơn đặt hàng {$order->code}",
                    'created_by_id'     => Auth::id(),
                    'created_by_name'   => Auth::user()?->name,
                ]);
            }

            $order->status = 'Đã hủy';
            $order->notes = ($order->notes ? $order->notes . ' | ' : '') . 'Lý do hủy: ' . ($request->reason ?: 'Người dùng hủy');
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã hủy đơn đặt hàng {$order->code} thành công!",
                'order'   => $order,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hủy đơn hàng: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * TẠO PHIẾU NHẬP HÀNG TỪ ĐƠN ĐẶT NCC
     * Xử lý trường hợp nhận thiếu hàng và đã thanh toán:
     * - Cơ chế 1: NCC nợ hàng (supplier_owes_goods) -> Đơn ở trạng thái Nhập một phần, giữ lại để giao tiếp
     * - Cơ chế 2: NCC nợ tiền (supplier_owes_money) -> Ghi nhận khoản thanh toán dư vào Công nợ NCC nợ công ty
     */
    public function createReceipt(Request $request, $id)
    {
        $order = SupplierOrder::with(['items.product', 'supplier'])->findOrFail($id);

        if (in_array($order->status, ['Hoàn thành', 'Đã hủy'])) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã hoàn thành hoặc đã bị hủy, không thể tạo thêm phiếu nhập kho.',
            ], 422);
        }

        $request->validate([
            'warehouse_id'               => 'required|exists:warehouses,id',
            'received_items'             => 'required|array|min:1',
            'received_items.*.item_id'   => 'required|exists:supplier_order_items,id',
            'received_items.*.quantity'  => 'required|numeric|min:0',
            'shortage_resolution'        => 'nullable|in:supplier_owes_goods,supplier_owes_money',
        ], [
            'warehouse_id.required'      => 'Vui lòng chọn kho nhập hàng',
            'received_items.required'    => 'Danh sách sản phẩm nhập kho không hợp lệ',
        ]);

        DB::beginTransaction();
        try {
            $warehouseId = $request->warehouse_id;
            $warehouse = Warehouse::findOrFail($warehouseId);
            $supplier = $order->supplier;
            $currentUser = Auth::user();

            // 1. Kiểm tra tổng số lượng nhập đợt này > 0
            $totalReceivedThisBatch = 0;
            foreach ($request->received_items as $ri) {
                $totalReceivedThisBatch += (float)($ri['quantity'] ?? 0);
            }
            if ($totalReceivedThisBatch <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập số lượng nhận lớn hơn 0 cho ít nhất một sản phẩm.',
                ], 422);
            }

            // 2. Sinh mã Phiếu nhập kho: PNK-NCC-YYYYMMDD-XXXX
            $today = date('Ymd');
            $txCount = InventoryTransaction::whereDate('created_at', today())->count() + 1;
            $txCode = 'PNK-NCC-' . $today . '-' . str_pad($txCount, 4, '0', STR_PAD_LEFT);
            while (InventoryTransaction::where('code', $txCode)->exists()) {
                $txCount++;
                $txCode = 'PNK-NCC-' . $today . '-' . str_pad($txCount, 4, '0', STR_PAD_LEFT);
            }

            // 3. Tính toán giá trị hàng nhập kho đợt này
            $batchTotalAmount = 0;
            $itemsMap = $order->items->keyBy('id');

            foreach ($request->received_items as $ri) {
                $itemId = $ri['item_id'];
                $item = $itemsMap->get($itemId);
                if (!$item) continue;

                $qty = (float)($ri['quantity'] ?? 0);
                if ($qty <= 0) continue;

                $batchTotalAmount += ($qty * (float)$item->unit_price);
            }

            // 4. Tạo InventoryTransaction
            $tx = InventoryTransaction::create([
                'code'                 => $txCode,
                'type'                 => 'import',
                'warehouse_id'         => $warehouseId,
                'created_by_id'        => $currentUser?->id ?: 1,
                'status'               => 'Hoàn thành',
                'supplier_name'        => $supplier?->name ?: $order->supplier_name,
                'reference_order_code' => $order->code,
                'total_amount'         => $batchTotalAmount,
                'notes'                => "Nhập hàng từ đơn đặt NCC {$order->code}" . ($request->receipt_notes ? " | " . $request->receipt_notes : ""),
            ]);

            // 5. Duyệt từng sản phẩm: Cập nhật tồn kho, lô hàng, và cập nhật số lượng đã nhận trong SupplierOrderItem
            foreach ($request->received_items as $ri) {
                $itemId = $ri['item_id'];
                $orderItem = $itemsMap->get($itemId);
                if (!$orderItem) continue;

                $qty = (float)($ri['quantity'] ?? 0);
                if ($qty <= 0) continue;

                // Cập nhật số lượng trong đơn đặt hàng
                $orderItem->received_quantity = (float)$orderItem->received_quantity + $qty;
                $orderItem->remaining_quantity = max(0, (float)$orderItem->ordered_quantity - (float)$orderItem->received_quantity);
                $orderItem->save();

                // Cập nhật tồn kho cho Product và WarehouseStock nếu có product_id
                $productId = $orderItem->product_id;
                $batchCode = !empty($ri['batch_code']) ? trim($ri['batch_code']) : null;
                $mfgDate = !empty($ri['manufacturing_date']) ? $ri['manufacturing_date'] : null;
                $expDate = !empty($ri['expiry_date']) ? $ri['expiry_date'] : null;

                if ($productId) {
                    $product = Product::find($productId);
                    $costBefore = $product ? (float)($product->latest_cost_price ?: $product->cost_price) : 0;
                    $importPrice = (float)$orderItem->unit_price;

                    // Tự động tạo mã lô nếu có HSD mà chưa nhập mã lô
                    if (!$batchCode && ($expDate || $mfgDate)) {
                        $batchCode = 'LÔ-' . date('ymd') . '-' . ($product ? $product->sku : $productId);
                    }

                    // Tạo chi tiết phiếu nhập kho
                    $txItem = InventoryTransactionItem::create([
                        'inventory_transaction_id'  => $tx->id,
                        'product_id'                => $productId,
                        'batch_code'                => $batchCode,
                        'manufacturing_date'        => $mfgDate,
                        'expiry_date'               => $expDate,
                        'quantity'                  => $qty,
                        'import_price'              => $importPrice,
                        'cost_price_at_transaction' => $costBefore,
                        'notes'                     => $ri['notes'] ?? $orderItem->product_name,
                    ]);

                    // Tăng tồn kho tổng
                    if ($product) {
                        $product->increment('stock_quantity', $qty);
                        if ($importPrice > 0) {
                            $product->latest_cost_price = $importPrice;
                            if ((float)$product->cost_price <= 0) {
                                $product->cost_price = $importPrice;
                            }
                            $product->save();
                        }
                    }

                    // Tăng tồn kho theo kho chỉ định
                    $whStock = WarehouseStock::firstOrCreate(
                        ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                        ['stock_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $whStock->increment('stock_quantity', $qty);

                    // Tạo ProductBatch nếu có thông tin lô / HSD
                    ProductBatch::create([
                        'product_id'                   => $productId,
                        'warehouse_id'                 => $warehouseId,
                        'inventory_transaction_item_id'=> $txItem->id,
                        'batch_code'                   => $batchCode ?: ('LÔ-' . date('ymd') . '-' . ($product ? $product->sku : $productId)),
                        'manufacturing_date'           => $mfgDate,
                        'expiry_date'                  => $expDate,
                        'import_quantity'              => $qty,
                        'remaining_quantity'           => $qty,
                        'cost_price'                   => $importPrice > 0 ? $importPrice : $costBefore,
                        'status'                       => 'active',
                        'notes'                        => "Nhập từ đơn đặt NCC {$order->code}",
                    ]);
                }
            }

            // 6. Tính toán lại tổng thể đơn hàng sau lần nhập này
            $order->refresh();
            $order->receipt_count = ($order->receipt_count ?? 0) + 1;
            
            // Tổng giá trị hàng thực tế đã nhận qua các lần
            $totalReceivedValue = 0;
            $totalRemainingQty = 0;
            $shortageAmount = 0;

            foreach ($order->items as $it) {
                $totalReceivedValue += ((float)$it->received_quantity * (float)$it->unit_price);
                $remaining = max(0, (float)$it->ordered_quantity - (float)$it->received_quantity);
                $totalRemainingQty += $remaining;
                $shortageAmount += ($remaining * (float)$it->unit_price);
            }

            $order->received_amount = $totalReceivedValue;
            $order->shortage_amount = $shortageAmount;

            $resolution = $request->input('shortage_resolution');

            // 7. Xử lý trạng thái và 2 cơ chế (NCC nợ hàng / NCC nợ tiền):
            if ($totalRemainingQty <= 0) {
                // ĐÃ NHẬP ĐỦ TOÀN BỘ SẢN PHẨM
                $order->status = 'Hoàn thành';
                $order->shortage_resolution = null;
            } else {
                // NHẬP KHÔNG ĐỦ SỐ LƯỢNG ĐẶT
                if ($resolution === 'supplier_owes_money') {
                    // ==========================================
                    // CƠ CHẾ 2: NCC NỢ TIỀN (Hoàn công nợ)
                    // ==========================================
                    // Người dùng đã thanh toán đơn hàng (hoặc cấn trừ nợ cũ):
                    // Tổng số tiền đã thanh toán + cấn trừ nợ thực tế cho đơn hàng
                    $paidAmount = (float)$order->paid_amount;
                    $debtDeducted = (float)$order->debt_deducted_amount;
                    $totalSettledAmount = $paidAmount + $debtDeducted;
                    $excessPaid = max(0, $totalSettledAmount - $totalReceivedValue);

                    $order->status = 'Hoàn thành'; // Chốt đơn kết thúc theo hàng thực tế
                    $order->shortage_resolution = 'supplier_owes_money';
                    $order->refunded_to_supplier_debt = $excessPaid;

                    if ($excessPaid > 0 && $supplier) {
                        $balanceBefore = (float)$supplier->supplier_debt_to_company;
                        $supplier->supplier_debt_to_company = $balanceBefore + $excessPaid;
                        $supplier->save();

                        // Ghi nhận lịch sử biến động nợ
                        $reasonDesc = "NCC nợ tiền do giao thiếu hàng đơn {$order->code} (Đã thanh toán " . number_format($paidAmount) . "đ";
                        if ($debtDeducted > 0) {
                            $reasonDesc .= " + cấn trừ nợ " . number_format($debtDeducted) . "đ = " . number_format($totalSettledAmount) . "đ";
                        }
                        $reasonDesc .= ", thực nhận " . number_format($totalReceivedValue) . "đ)";

                        SupplierDebtLog::create([
                            'supplier_id'       => $supplier->id,
                            'supplier_order_id' => $order->id,
                            'type'              => 'increase',
                            'amount'            => $excessPaid,
                            'balance_before'    => $balanceBefore,
                            'balance_after'     => $supplier->supplier_debt_to_company,
                            'reason'            => $reasonDesc,
                            'created_by_id'     => $currentUser?->id,
                            'created_by_name'   => $currentUser?->name,
                        ]);
                    }
                } else {
                    // ==========================================
                    // CƠ CHẾ 1: NCC NỢ HÀNG (Giao bù đợt sau)
                    // ==========================================
                    // Đơn hàng chuyển sang trạng thái "Nhập một phần"
                    // Giữ lại số lượng thiếu để khi hàng về tiếp thì tạo phiếu nhập hàng tiếp
                    $order->status = 'Nhập một phần';
                    $order->shortage_resolution = 'supplier_owes_goods';
                }
            }

            $order->save();

            DB::commit();
            \App\Services\InventoryAvailabilityService::clearCache();
            \App\Services\BackorderService::checkAndNotifyBackordersOnStockArrival();

            return response()->json([
                'success'        => true,
                'message'        => "Tạo phiếu nhập kho {$txCode} thành công!",
                'transaction_code'=> $txCode,
                'order'          => $order->load(['items', 'supplier']),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("SupplierOrder CreateReceipt Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo phiếu nhập kho: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy thông tin chi tiết NCC (Bao gồm số dư tiền NCC đang nợ và các mặt hàng NCC còn nợ)
     */
    public function getSupplierInfo($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Các mặt hàng NCC còn nợ từ các đơn "Nhập một phần" (cơ chế NCC nợ hàng)
        $owingGoodsOrders = SupplierOrder::where('supplier_id', $id)
            ->where('status', 'Nhập một phần')
            ->where('shortage_resolution', 'supplier_owes_goods')
            ->with('items')
            ->get();

        $owingItems = [];
        foreach ($owingGoodsOrders as $o) {
            foreach ($o->items as $it) {
                if ((float)$it->remaining_quantity > 0) {
                    $owingItems[] = [
                        'order_code'         => $o->code,
                        'order_date'         => $o->order_date?->format('d/m/Y'),
                        'product_name'       => $it->product_name,
                        'unit'               => $it->unit,
                        'ordered_quantity'   => (float)$it->ordered_quantity,
                        'received_quantity'  => (float)$it->received_quantity,
                        'remaining_quantity' => (float)$it->remaining_quantity,
                        'unit_price'         => (float)$it->unit_price,
                    ];
                }
            }
        }

        return response()->json([
            'success'     => true,
            'supplier'    => $supplier,
            'debt_balance'=> (float)$supplier->supplier_debt_to_company,
            'owing_items' => $owingItems,
        ]);
    }

    /**
     * Tạo mới hoặc lưu Nhà Cung Cấp
     */
    public function saveSupplier(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
        ], [
            'name.required' => 'Vui lòng nhập tên Nhà cung cấp',
        ]);

        $id = $request->input('id');
        if ($id) {
            $supplier = Supplier::findOrFail($id);
        } else {
            $code = $request->code ?: ('NCC-' . strtoupper(substr(md5(uniqid()), 0, 6)));
            $supplier = new Supplier();
            $supplier->code = $code;
        }

        $supplier->name = $request->name;
        $supplier->phone = $request->phone;
        $supplier->email = $request->email;
        $supplier->tax_code = $request->tax_code;
        $supplier->contact_name = $request->contact_name;
        $supplier->address = $request->address;
        $supplier->bank_name = $request->bank_name;
        $supplier->bank_account = $request->bank_account;
        $supplier->notes = $request->notes;
        $supplier->save();

        return response()->json([
            'success'  => true,
            'message'  => 'Lưu Nhà cung cấp thành công!',
            'supplier' => $supplier,
        ]);
    }

    /**
     * Lịch sử công nợ của NCC
     */
    public function supplierDebtHistory($id)
    {
        $supplier = Supplier::findOrFail($id);
        $logs = SupplierDebtLog::where('supplier_id', $id)
            ->with('order')
            ->latest('id')
            ->get();

        return response()->json([
            'success'  => true,
            'supplier' => $supplier,
            'logs'     => $logs,
        ]);
    }
}
