<?php

namespace App\Http\Controllers;

use App\Models\PromotionAdvance;
use App\Models\PromotionSettlementLog;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionAdvanceController extends Controller
{
    /**
     * Màn hình chính / API danh sách ứng khuyến mãi
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $staffId = $request->query('staff_id');
        $brandId = $request->query('brand_id');
        $brandName = $request->query('brand_name');
        $keyword = trim((string)$request->query('search'));

        // Query danh sách chi tiết
        $query = PromotionAdvance::with(['order', 'staff', 'brand', 'warehouse', 'product', 'settlementLogs'])
            ->orderBy('id', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($staffId) {
            $query->where('staff_id', $staffId);
        }
        if ($brandId) {
            $query->where('brand_id', $brandId);
        } elseif ($brandName) {
            $query->where('brand_name', 'like', "%{$brandName}%");
        }
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                  ->orWhere('order_code', 'like', "%{$keyword}%")
                  ->orWhere('product_name', 'like', "%{$keyword}%")
                  ->orWhere('product_sku', 'like', "%{$keyword}%")
                  ->orWhere('staff_name', 'like', "%{$keyword}%")
                  ->orWhere('customer_name', 'like', "%{$keyword}%")
                  ->orWhere('brand_name', 'like', "%{$keyword}%");
            });
        }

        $advances = $query->paginate(25)->withQueryString();

        // 1. Thống kê KPI tổng thể
        $kpi = [
            'total_advancing_qty'   => (float) PromotionAdvance::whereIn('status', ['pending', 'partially_settled'])->sum('remaining_quantity'),
            'total_advancing_value' => (float) PromotionAdvance::whereIn('status', ['pending', 'partially_settled'])->sum(DB::raw('remaining_quantity * unit_price')),
            'total_staff_count'     => PromotionAdvance::whereIn('status', ['pending', 'partially_settled'])->distinct('staff_name')->count('staff_name'),
            'total_brands_count'    => PromotionAdvance::whereIn('status', ['pending', 'partially_settled'])->distinct('brand_name')->count('brand_name'),
            'month_settled_qty'     => (float) PromotionSettlementLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('quantity'),
        ];

        // 2. Thống kê theo từng Nhân viên (Staff Summary)
        $staffSummary = PromotionAdvance::select(
                'staff_id',
                'staff_name',
                DB::raw('COUNT(*) as total_advances_count'),
                DB::raw('SUM(advance_quantity) as total_advance_qty'),
                DB::raw('SUM(returned_quantity) as total_returned_qty'),
                DB::raw('SUM(remaining_quantity) as total_remaining_qty'),
                DB::raw('SUM(remaining_quantity * unit_price) as total_remaining_value')
            )
            ->groupBy('staff_id', 'staff_name')
            ->orderByDesc('total_remaining_qty')
            ->get();

        // 3. Thống kê Tổng hợp theo Hãng / Nhà sản xuất (Brand Summary)
        $brandSummary = PromotionAdvance::select(
                'brand_id',
                'brand_name',
                'product_id',
                'product_sku',
                'product_name',
                'unit',
                DB::raw('COUNT(*) as total_orders_count'),
                DB::raw('SUM(advance_quantity) as total_advance_qty'),
                DB::raw('SUM(returned_quantity) as total_returned_qty'),
                DB::raw('SUM(remaining_quantity) as total_remaining_qty'),
                DB::raw('SUM(remaining_quantity * unit_price) as total_remaining_value')
            )
            ->groupBy('brand_id', 'brand_name', 'product_id', 'product_sku', 'product_name', 'unit')
            ->orderBy('brand_name')
            ->orderByDesc('total_remaining_qty')
            ->get();

        // Danh sách phụ trợ cho form lọc / tạo
        $staffList = User::where(function ($q) {
            $q->where('status', 'Đang làm việc')->orWhereNull('status');
        })->orderBy('name')->get(['id', 'name', 'phone']);
        $brandsList = Brand::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $warehousesList = Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $productsList = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'unit', 'cost_price', 'price', 'brand_id']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'kpi'          => $kpi,
                'staffSummary' => $staffSummary,
                'brandSummary' => $brandSummary,
                'advances'     => $advances,
            ]);
        }

        return view('orders.promotion_index', [
            'kpi'            => $kpi,
            'staffSummary'   => $staffSummary,
            'brandSummary'   => $brandSummary,
            'advances'       => $advances,
            'staffList'      => $staffList,
            'brandsList'     => $brandsList,
            'warehousesList' => $warehousesList,
            'productsList'   => $productsList,
        ]);
    }

    /**
     * Tạo mới phiếu ứng khuyến mãi (Trực tiếp hoặc bổ sung)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'advance_quantity' => 'required|numeric|min:0.01',
            'warehouse_id'     => 'nullable|exists:warehouses,id',
            'staff_id'         => 'nullable|exists:users,id',
        ]);

        try {
            $product = Product::with('brand')->findOrFail($request->product_id);
            $staff = $request->staff_id ? User::find($request->staff_id) : Auth::user();
            $warehouse = $request->warehouse_id ? Warehouse::find($request->warehouse_id) : Warehouse::first();
            $brand = $product->brand ?: ($request->brand_id ? Brand::find($request->brand_id) : null);

            $qty = (float)$request->advance_quantity;
            $unitPrice = (float)($request->unit_price ?: ($product->latest_cost_price ?: ($product->cost_price ?: $product->price)));
            $code = PromotionAdvance::generateCode();

            $advance = PromotionAdvance::create([
                'code'               => $code,
                'order_id'           => $request->order_id ?: null,
                'order_code'         => $request->order_code ?: null,
                'staff_id'           => $staff?->id,
                'staff_name'         => $staff?->name ?: 'NVKD',
                'customer_name'      => $request->customer_name ?: null,
                'brand_id'           => $brand?->id,
                'brand_name'         => $brand?->name ?: ($request->brand_name ?: 'Hãng Khác'),
                'warehouse_id'       => $warehouse?->id,
                'warehouse_name'     => $warehouse?->name ?: 'Kho Tổng',
                'product_id'         => $product->id,
                'product_sku'        => $product->sku,
                'product_name'       => $product->name,
                'unit'               => $product->unit ?: 'cái',
                'advance_quantity'   => $qty,
                'returned_quantity'  => 0,
                'remaining_quantity' => $qty,
                'unit_price'         => $unitPrice,
                'total_value'        => $qty * $unitPrice,
                'advance_type'       => $request->advance_type ?: 'warehouse_advance',
                'status'             => 'pending',
                'advance_date'       => $request->advance_date ?: now(),
                'notes'              => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Tạo phiếu ứng khuyến mãi {$code} thành công!",
                'advance' => $advance,
            ]);
        } catch (\Throwable $e) {
            Log::error("PromotionAdvance store error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi tạo phiếu ứng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hoàn ứng / Trả bù cho 1 phiếu ứng cụ thể
     */
    public function settle(Request $request, $id)
    {
        $advance = PromotionAdvance::findOrFail($id);

        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $repayQty = (float)$request->quantity;
        if ($repayQty > (float)$advance->remaining_quantity) {
            return response()->json([
                'success' => false,
                'message' => "Số lượng hoàn ứng ({$repayQty}) không được vượt quá số lượng còn nợ ({$advance->remaining_quantity})!"
            ], 422);
        }

        DB::beginTransaction();
        try {
            $currentUser = Auth::user();
            $newReturned = (float)$advance->returned_quantity + $repayQty;
            $newRemaining = max(0, (float)$advance->advance_quantity - $newReturned);

            $advance->returned_quantity = $newReturned;
            $advance->remaining_quantity = $newRemaining;
            $advance->status = ($newRemaining <= 0) ? 'settled' : 'partially_settled';
            if ($newRemaining <= 0) {
                $advance->settled_at = now();
            }
            $advance->save();

            // Ghi log hoàn ứng
            PromotionSettlementLog::create([
                'promotion_advance_id' => $advance->id,
                'settlement_type'      => $request->settlement_type ?: 'supplier_repaid',
                'quantity'             => $repayQty,
                'remaining_after'      => $newRemaining,
                'reference_code'       => $request->reference_code ?: null,
                'performer_id'         => $currentUser?->id,
                'performer_name'       => $currentUser?->name ?: 'Admin',
                'notes'                => $request->notes ?: "Hoàn ứng {$repayQty} {$advance->unit} cho phiếu {$advance->code}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã ghi nhận hoàn ứng {$repayQty} {$advance->unit} thành công!",
                'advance' => $advance->load('settlementLogs'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("PromotionAdvance settle error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hoàn ứng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hoàn ứng hàng loạt theo Hãng (FIFO) khi Hãng trả bù một lượng hàng KM
     */
    public function settleByBrand(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:0.01',
        ]);

        $brandName = trim($request->brand_name);
        $productId = (int)$request->product_id;
        $totalRepayQty = (float)$request->quantity;
        $refCode = $request->reference_code ?: 'PNK-TRA-BU-' . date('Ymd');
        $notes = $request->notes ?: "Hãng trả bù hàng KM";

        DB::beginTransaction();
        try {
            $currentUser = Auth::user();

            // Tìm các phiếu ứng còn nợ của Hãng và sản phẩm này theo thứ tự cũ nhất trước (FIFO)
            $advances = PromotionAdvance::where('brand_name', $brandName)
                ->where('product_id', $productId)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('id', 'asc')
                ->get();

            if ($advances->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy khoản nợ KM nào của Hãng [{$brandName}] đối với sản phẩm này!"
                ], 422);
            }

            $remainingToAllocate = $totalRepayQty;
            $settledCount = 0;

            foreach ($advances as $adv) {
                if ($remainingToAllocate <= 0) break;

                $curRemaining = (float)$adv->remaining_quantity;
                $deduct = min($remainingToAllocate, $curRemaining);

                $newReturned = (float)$adv->returned_quantity + $deduct;
                $newRem = max(0, (float)$adv->advance_quantity - $newReturned);

                $adv->returned_quantity = $newReturned;
                $adv->remaining_quantity = $newRem;
                $adv->status = ($newRem <= 0) ? 'settled' : 'partially_settled';
                if ($newRem <= 0) {
                    $adv->settled_at = now();
                }
                $adv->save();

                PromotionSettlementLog::create([
                    'promotion_advance_id' => $adv->id,
                    'settlement_type'      => 'supplier_repaid',
                    'quantity'             => $deduct,
                    'remaining_after'      => $newRem,
                    'reference_code'       => $refCode,
                    'performer_id'         => $currentUser?->id,
                    'performer_name'       => $currentUser?->name ?: 'Admin',
                    'notes'                => "[Hãng {$brandName} trả bù] " . $notes,
                ]);

                $remainingToAllocate -= $deduct;
                $settledCount++;
            }

            DB::commit();

            $actuallyDeducted = $totalRepayQty - $remainingToAllocate;

            return response()->json([
                'success' => true,
                'message' => "Đã đối soát hoàn ứng thành công {$actuallyDeducted} sản phẩm cho {$settledCount} phiếu ứng của Hãng {$brandName}!",
                'actually_settled' => $actuallyDeducted,
                'excess_repaid'    => max(0, $remainingToAllocate),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("PromotionAdvance settleByBrand error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hoàn ứng theo Hãng: ' . $e->getMessage()], 500);
        }
    }
}
