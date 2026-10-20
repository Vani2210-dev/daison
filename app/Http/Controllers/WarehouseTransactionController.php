<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WarehouseTransactionController extends Controller
{
    /**
     * Danh sách phiếu Nhập kho
     */
    public function receipts(Request $request)
    {
        $query = InventoryTransaction::with(['warehouse', 'createdBy', 'items.product'])
            ->where('type', 'import');

        // Bộ lọc kho
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Bộ lọc trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Bộ lọc nhà cung cấp
        if ($request->filled('supplier_name')) {
            $query->where('supplier_name', 'like', '%' . $request->supplier_name . '%');
        }

        // Bộ lọc ngày
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Tìm kiếm mã phiếu, số hóa đơn, ghi chú
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('reference_order_code', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('items.product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        $receipts = $query->latest('created_at')->paginate(15)->withQueryString();
        $warehouses = Warehouse::companyWarehouses()->orderBy('name')->get();

        // Thống kê nhanh
        $stats = [
            'total_count' => InventoryTransaction::where('type', 'import')->count(),
            'total_amount' => InventoryTransaction::where('type', 'import')->where('status', '!=', 'Đã hủy')->sum('total_amount'),
            'today_count' => InventoryTransaction::where('type', 'import')->whereDate('created_at', today())->count(),
            'today_amount' => InventoryTransaction::where('type', 'import')->whereDate('created_at', today())->where('status', '!=', 'Đã hủy')->sum('total_amount'),
        ];

        return view('inventory.receipts', compact('receipts', 'warehouses', 'stats'));
    }

    /**
     * Trang / Modal Tạo phiếu Nhập kho
     */
    public function createReceipt(Request $request)
    {
        $warehouses = Warehouse::companyWarehouses()->orderBy('name')->get();
        $code = $this->generateTransactionCode('import');

        return view('inventory.receipt_form', compact('warehouses', 'code'));
    }

    /**
     * Lưu phiếu Nhập kho
     */
    public function storeReceipt(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.import_price' => 'nullable|numeric|min:0',
        ], [
            'warehouse_id.required' => 'Vui lòng chọn kho nhập',
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm cần nhập kho',
            'items.min' => 'Vui lòng thêm ít nhất một sản phẩm cần nhập kho',
            'items.*.quantity.min' => 'Số lượng nhập phải lớn hơn 0',
        ]);

        DB::beginTransaction();
        try {
            $status = $request->input('status', 'Hoàn thành'); // Hoàn thành hoặc Bản nháp
            $code = $request->input('code') ?: $this->generateTransactionCode('import');

            // Đảm bảo mã phiếu duy nhất
            $counter = 1;
            $originalCode = $code;
            while (InventoryTransaction::where('code', $code)->exists()) {
                $code = $originalCode . '-' . $counter;
                $counter++;
            }

            $totalAmount = 0;
            foreach ($request->items as $it) {
                $qty = (float)($it['quantity'] ?? 0);
                $price = (float)($it['import_price'] ?? 0);
                $totalAmount += ($qty * $price);
            }

            $tx = InventoryTransaction::create([
                'code' => $code,
                'type' => 'import',
                'warehouse_id' => $request->warehouse_id,
                'created_by_id' => Auth::id() ?: 1,
                'status' => $status,
                'supplier_name' => $request->input('supplier_name', 'Nhà cung cấp / Đối tác'),
                'reference_order_code' => $request->input('reference_order_code'),
                'total_amount' => $totalAmount,
                'notes' => $request->input('notes'),
            ]);

            foreach ($request->items as $it) {
                $productId = $it['product_id'];
                $quantity = (float)$it['quantity'];
                $importPrice = (float)($it['import_price'] ?? 0);
                $notes = $it['notes'] ?? null;
                $batchCode = !empty($it['batch_code']) ? trim($it['batch_code']) : null;
                $mfgDate = !empty($it['manufacturing_date']) ? $it['manufacturing_date'] : null;
                $expDate = !empty($it['expiry_date']) ? $it['expiry_date'] : null;

                $product = Product::find($productId);
                $costBefore = $product ? (float)($product->latest_cost_price ?: $product->cost_price) : 0;

                // Tự động tạo mã lô nếu người dùng nhập HSD mà không điền mã lô
                if (!$batchCode && ($expDate || $mfgDate)) {
                    $batchCode = 'LÔ-' . date('ymd') . '-' . ($product ? $product->sku : $productId);
                }

                $txItem = InventoryTransactionItem::create([
                    'inventory_transaction_id' => $tx->id,
                    'product_id' => $productId,
                    'batch_code' => $batchCode,
                    'manufacturing_date' => $mfgDate,
                    'expiry_date' => $expDate,
                    'quantity' => $quantity,
                    'import_price' => $importPrice,
                    'cost_price_at_transaction' => $costBefore,
                    'notes' => $notes,
                ]);

                // Nếu hoàn thành -> Tăng số lượng tồn kho & tạo Lô hàng (ProductBatch)
                if ($status === 'Hoàn thành' && $product) {
                    $product->increment('stock_quantity', $quantity);

                    if ($importPrice > 0) {
                        $product->latest_cost_price = $importPrice;
                        // Có thể cập nhật giá vốn nếu chưa có giá vốn
                        if ((float)$product->cost_price <= 0) {
                            $product->cost_price = $importPrice;
                        }
                        $product->save();
                    }

                    // Tăng tồn kho theo kho
                    $whStock = WarehouseStock::firstOrCreate(
                        ['product_id' => $productId, 'warehouse_id' => $request->warehouse_id],
                        ['stock_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $whStock->increment('stock_quantity', $quantity);

                    // Ghi nhận Lô hàng & Hạn sử dụng vào bảng product_batches
                    ProductBatch::create([
                        'product_id' => $productId,
                        'warehouse_id' => $request->warehouse_id,
                        'inventory_transaction_item_id' => $txItem->id,
                        'batch_code' => $batchCode ?: ('LÔ-' . date('ymd') . '-' . $product->sku),
                        'manufacturing_date' => $mfgDate,
                        'expiry_date' => $expDate,
                        'import_quantity' => $quantity,
                        'remaining_quantity' => $quantity,
                        'cost_price' => $importPrice > 0 ? $importPrice : $costBefore,
                        'status' => 'active',
                        'notes' => $notes,
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lập phiếu nhập kho thành công!',
                    'code' => $tx->code,
                    'id' => $tx->id,
                ]);
            }

            return redirect()->route('inventory.receipts')
                ->with('success', "Lập phiếu nhập kho {$tx->code} thành công!");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Lỗi lưu phiếu nhập kho: ' . $e->getMessage());
        }
    }

    /**
     * Danh sách phiếu Xuất kho
     */
    public function issues(Request $request)
    {
        $query = InventoryTransaction::with(['warehouse', 'createdBy', 'items.product'])
            ->where('type', 'export');

        // Bộ lọc kho
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Bộ lọc trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Bộ lọc người nhận / lý do
        if ($request->filled('supplier_name')) {
            $query->where('supplier_name', 'like', '%' . $request->supplier_name . '%');
        }

        // Bộ lọc ngày
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('reference_order_code', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('items.product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        $issues = $query->latest('created_at')->paginate(15)->withQueryString();
        $warehouses = Warehouse::companyWarehouses()->orderBy('name')->get();

        // Thống kê nhanh
        $stats = [
            'total_count' => InventoryTransaction::where('type', 'export')->count(),
            'total_amount' => InventoryTransaction::where('type', 'export')->where('status', '!=', 'Đã hủy')->sum('total_amount'),
            'today_count' => InventoryTransaction::where('type', 'export')->whereDate('created_at', today())->count(),
            'today_amount' => InventoryTransaction::where('type', 'export')->whereDate('created_at', today())->where('status', '!=', 'Đã hủy')->sum('total_amount'),
        ];

        return view('inventory.issues', compact('issues', 'warehouses', 'stats'));
    }

    /**
     * Trang / Modal Tạo phiếu Xuất kho
     */
    public function createIssue(Request $request)
    {
        $warehouses = Warehouse::companyWarehouses()->orderBy('name')->get();
        $code = $this->generateTransactionCode('export');

        return view('inventory.issue_form', compact('warehouses', 'code'));
    }

    /**
     * Lưu phiếu Xuất kho
     */
    public function storeIssue(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason_type' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'warehouse_id.required' => 'Vui lòng chọn kho xuất',
            'reason_type.required' => 'Vui lòng chọn lý do xuất kho',
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm cần xuất kho',
            'items.min' => 'Vui lòng thêm ít nhất một sản phẩm cần xuất kho',
            'items.*.quantity.min' => 'Số lượng xuất phải lớn hơn 0',
        ]);

        DB::beginTransaction();
        try {
            $status = $request->input('status', 'Hoàn thành');
            $code = $request->input('code') ?: $this->generateTransactionCode('export');

            // Đảm bảo mã duy nhất
            $counter = 1;
            $originalCode = $code;
            while (InventoryTransaction::where('code', $code)->exists()) {
                $code = $originalCode . '-' . $counter;
                $counter++;
            }

            $warehouseId = $request->warehouse_id;
            $reasonType = $request->reason_type;
            $receiver = $request->input('supplier_name', 'Nội bộ công ty');
            $notes = "[Lý do: {$reasonType}] " . ($request->input('notes') ?? '');

            // Kiểm tra số lượng tồn kho trước khi xuất nếu hoàn thành
            if ($status === 'Hoàn thành') {
                foreach ($request->items as $it) {
                    $productId = $it['product_id'];
                    $qty = (float)$it['quantity'];

                    $whStock = WarehouseStock::where('product_id', $productId)
                        ->where('warehouse_id', $warehouseId)
                        ->first();
                    $currentStock = $whStock ? (float)$whStock->stock_quantity : 0;

                    // Nếu kho không đủ hàng và người dùng không cho phép xuất âm
                    if ($currentStock < $qty && !$request->boolean('allow_negative_stock')) {
                        $p = Product::find($productId);
                        $pName = $p ? "{$p->name} ({$p->sku})" : "Sản phẩm #{$productId}";
                        $wh = Warehouse::find($warehouseId);
                        $whName = $wh ? $wh->name : "Kho #{$warehouseId}";

                        DB::rollBack();
                        $errMsg = "Không đủ hàng để xuất: {$pName} tại {$whName} hiện chỉ còn tồn {$currentStock}, yêu cầu xuất {$qty}.";
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $errMsg], 422);
                        }
                        return back()->withInput()->with('error', $errMsg);
                    }
                }
            }

            $totalAmount = 0;
            foreach ($request->items as $it) {
                $qty = (float)$it['quantity'];
                $price = (float)($it['export_price'] ?? 0);
                if ($price <= 0) {
                    $p = Product::find($it['product_id']);
                    $price = $p ? (float)($p->cost_price ?: $p->latest_cost_price) : 0;
                }
                $totalAmount += ($qty * $price);
            }

            $tx = InventoryTransaction::create([
                'code' => $code,
                'type' => 'export',
                'warehouse_id' => $warehouseId,
                'created_by_id' => Auth::id() ?: 1,
                'status' => $status,
                'supplier_name' => $receiver,
                'reference_order_code' => $request->input('reference_order_code'),
                'total_amount' => $totalAmount,
                'notes' => $notes,
            ]);

            foreach ($request->items as $it) {
                $productId = $it['product_id'];
                $quantity = (float)$it['quantity'];
                $price = (float)($it['export_price'] ?? 0);
                $product = Product::find($productId);

                if ($price <= 0 && $product) {
                    $price = (float)($product->cost_price ?: $product->latest_cost_price);
                }

                InventoryTransactionItem::create([
                    'inventory_transaction_id' => $tx->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'import_price' => $price,
                    'cost_price_at_transaction' => $price,
                    'notes' => $it['notes'] ?? null,
                ]);

                // Nếu hoàn thành -> Giảm tồn kho
                if ($status === 'Hoàn thành' && $product) {
                    $product->decrement('stock_quantity', $quantity);

                    $whStock = WarehouseStock::firstOrCreate(
                        ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                        ['stock_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $whStock->decrement('stock_quantity', $quantity);
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lập phiếu xuất kho thành công!',
                    'code' => $tx->code,
                    'id' => $tx->id,
                ]);
            }

            return redirect()->route('inventory.issues')
                ->with('success', "Lập phiếu xuất kho {$tx->code} thành công!");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Lỗi lưu phiếu xuất kho: ' . $e->getMessage());
        }
    }

    /**
     * Chi tiết phiếu (JSON hoặc View)
     */
    public function show(Request $request, $id)
    {
        $transaction = InventoryTransaction::with(['warehouse', 'createdBy', 'approvedBy', 'items.product.category', 'items.product.brand'])
            ->findOrFail($id);

        if ($request->ajax() || $request->wantsJson() || !view()->exists('inventory.transaction_detail')) {
            return response()->json([
                'success' => true,
                'transaction' => $transaction,
            ]);
        }

        return view('inventory.transaction_detail', compact('transaction'));
    }

    /**
     * In Phiếu Nhập / Xuất kho (Khổ A4 / A5)
     */
    public function print(Request $request, $id)
    {
        $transaction = InventoryTransaction::with(['warehouse.branch', 'createdBy', 'approvedBy', 'items.product'])
            ->findOrFail($id);

        return view('inventory.print', compact('transaction'));
    }

    /**
     * Hủy phiếu kho (Hoàn lại số lượng tồn kho)
     */
    public function cancel(Request $request, $id)
    {
        $tx = InventoryTransaction::with('items')->findOrFail($id);

        if ($tx->status === 'Đã hủy') {
            return response()->json(['success' => false, 'message' => 'Phiếu này đã bị hủy trước đó!'], 400);
        }

        DB::beginTransaction();
        try {
            // Nếu phiếu đã hoàn thành, hoàn lại tồn kho
            if ($tx->status === 'Hoàn thành' || $tx->status === 'Đã nhận hàng') {
                foreach ($tx->items as $item) {
                    $prod = Product::find($item->product_id);
                    $whStock = WarehouseStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $tx->warehouse_id)
                        ->first();

                    if ($tx->type === 'import') {
                        // Phiếu nhập bị hủy -> trừ lại tồn kho
                        if ($prod) $prod->decrement('stock_quantity', $item->quantity);
                        if ($whStock) $whStock->decrement('stock_quantity', $item->quantity);
                        // Xóa các lô hàng phát sinh từ phiếu nhập bị hủy
                        ProductBatch::where('inventory_transaction_item_id', $item->id)->delete();
                    } elseif ($tx->type === 'export') {
                        // Phiếu xuất bị hủy -> cộng lại tồn kho
                        if ($prod) $prod->increment('stock_quantity', $item->quantity);
                        if ($whStock) $whStock->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            $tx->status = 'Đã hủy';
            $tx->notes = ($tx->notes ? $tx->notes . ' | ' : '') . '[Hủy bởi ' . (Auth::user()?->name ?? 'Admin') . ' lúc ' . now()->format('H:i d/m/Y') . ' - Lý do: ' . ($request->reason ?? 'Không có') . ']';
            $tx->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã hủy phiếu {$tx->code} và hoàn trả số lượng tồn kho thành công!",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi hủy phiếu: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Tìm kiếm sản phẩm nhanh (hỗ trợ load tồn kho của kho được chọn)
     */
    public function searchProducts(Request $request)
    {
        $q = trim($request->input('q', ''));
        $warehouseId = $request->input('warehouse_id');

        $query = Product::select('id', 'sku', 'name', 'unit', 'cost_price', 'latest_cost_price', 'stock_quantity')
            ->where('is_active', true);

        if (!empty($q)) {
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        $products = $query->limit(30)->get()->map(function ($p) use ($warehouseId) {
            $whStock = 0;
            if ($warehouseId) {
                $stockRecord = WarehouseStock::where('product_id', $p->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();
                $whStock = $stockRecord ? (float)$stockRecord->stock_quantity : 0;
            } else {
                $whStock = (float)$p->stock_quantity;
            }

            return [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'unit' => $p->unit ?? 'cái',
                'cost_price' => (float)($p->latest_cost_price ?: $p->cost_price),
                'total_stock' => (float)$p->stock_quantity,
                'warehouse_stock' => $whStock,
            ];
        });

        return response()->json(['products' => $products]);
    }

    /**
     * Sinh mã phiếu tự động
     */
    private function generateTransactionCode(string $type): string
    {
        $prefix = match($type) {
            'import' => 'PNK',
            'export' => 'PXK',
            'transfer' => 'PCK',
            'audit' => 'PKK',
            default => 'PKK'
        };

        $datePrefix = date('ymd'); // Ví dụ: 261005
        $countToday = InventoryTransaction::where('type', $type)
            ->whereDate('created_at', today())
            ->count();

        $num = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$datePrefix}-{$num}";
    }

    /**
     * Lấy danh sách Lô hàng & Hạn sử dụng của một sản phẩm
     */
    public function getProductBatches($productId)
    {
        $product = Product::select('id', 'name', 'sku', 'unit', 'stock_quantity')
            ->findOrFail($productId);

        $batches = ProductBatch::with(['warehouse:id,name,code', 'transactionItem.transaction:id,code,created_at,supplier_name'])
            ->where('product_id', $productId)
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC, id DESC')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'batch_code' => $b->batch_code ?: 'Mặc định',
                    'warehouse_name' => $b->warehouse?->name ?? 'Kho chính',
                    'warehouse_id' => $b->warehouse_id,
                    'manufacturing_date' => $b->manufacturing_date ? $b->manufacturing_date->format('Y-m-d') : null,
                    'expiry_date' => $b->expiry_date ? $b->expiry_date->format('Y-m-d') : null,
                    'import_quantity' => (float)$b->import_quantity,
                    'remaining_quantity' => (float)$b->remaining_quantity,
                    'cost_price' => (float)$b->cost_price,
                    'days_until_expiry' => $b->days_until_expiry,
                    'expiry_status' => $b->expiry_status_code,
                    'receipt_code' => $b->transactionItem?->transaction?->code ?? '-',
                    'receipt_date' => $b->transactionItem?->transaction?->created_at ? $b->transactionItem->transaction->created_at->format('d/m/Y') : ($b->created_at ? $b->created_at->format('d/m/Y') : '-'),
                    'supplier_name' => $b->transactionItem?->transaction?->supplier_name ?? '-',
                    'notes' => $b->notes,
                ];
            });

        // Thống kê nhanh theo sản phẩm
        $stats = [
            'total_batches' => $batches->count(),
            'active_batches' => $batches->where('remaining_quantity', '>', 0)->count(),
            'near_expired_count' => $batches->where('remaining_quantity', '>', 0)->where('expiry_status', 'near_expired')->count(),
            'expired_count' => $batches->where('remaining_quantity', '>', 0)->where('expiry_status', 'expired')->count(),
            'total_remaining_quantity' => (float)$batches->sum('remaining_quantity'),
        ];

        return response()->json([
            'success' => true,
            'product' => $product,
            'stats' => $stats,
            'batches' => $batches,
        ]);
    }

    /**
     * Tra cứu & Báo cáo Lô hàng & Hạn sử dụng trong toàn kho
     */
    public function getWarehouseBatches(Request $request)
    {
        $query = ProductBatch::with([
            'product:id,name,sku,unit',
            'warehouse:id,name,code',
            'transactionItem.transaction:id,code,created_at,supplier_name'
        ]);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $filterDate = $request->input('date_filter');
        if ($filterDate === 'near_expired') {
            $query->nearExpired(60);
        } elseif ($filterDate === 'expired') {
            $query->expired();
        } elseif ($filterDate === 'active') {
            $query->active();
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sq) use ($s) {
                $sq->where('batch_code', 'like', "%{$s}%")
                   ->orWhereHas('product', function ($pq) use ($s) {
                       $pq->where('name', 'like', "%{$s}%")
                          ->orWhere('sku', 'like', "%{$s}%");
                   });
            });
        }

        $batches = $query->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC, id DESC')
            ->paginate(20)
            ->withQueryString();

        return response()->json($batches);
    }

    /**
     * Màn hình Quản lý & Tra cứu Hạn Sử Dụng (Date) / Lô Hàng trong Kho
     */
    public function batchesView(Request $request)
    {
        $warehouses = Warehouse::companyWarehouses()->orderBy('name')->get();

        $query = ProductBatch::with([
            'product:id,name,sku,unit,stock_quantity',
            'warehouse:id,name,code',
            'transactionItem.transaction:id,code,created_at,supplier_name'
        ]);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('date_filter')) {
            $df = $request->date_filter;
            if ($df === 'near_expired') {
                $query->nearExpired(60);
            } elseif ($df === 'expired') {
                $query->expired();
            } elseif ($df === 'active') {
                $query->active();
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sq) use ($s) {
                $sq->where('batch_code', 'like', "%{$s}%")
                   ->orWhereHas('product', function ($pq) use ($s) {
                       $pq->where('name', 'like', "%{$s}%")
                          ->orWhere('sku', 'like', "%{$s}%");
                   });
            });
        }

        $batches = $query->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC, id DESC')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_batches' => ProductBatch::count(),
            'active_batches' => ProductBatch::where('remaining_quantity', '>', 0)->count(),
            'near_expired' => ProductBatch::nearExpired(60)->count(),
            'expired' => ProductBatch::expired()->count(),
        ];

        return view('inventory.batches', compact('batches', 'warehouses', 'stats'));
    }
}
