<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Dealer;
use App\Models\DeliveryDispatch;
use App\Models\DeliveryRoute;
use App\Models\DepartmentKpi;
use App\Models\FinancialFund;
use App\Models\KpiEvaluation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\Role;
use App\Models\Permission;
use App\Models\SalesPolicy;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\CategoryExcelImportService;
use App\Services\ProductExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class WebViewController extends Controller
{
    /**
     * Display the Dashboard with KPI summary and recent orders
     */
    public function dashboard()
    {
        $today = now()->startOfDay();

        $todayOrdersCount = Order::where('created_at', '>=', $today)->count();
        $todayRevenue = Order::where('created_at', '>=', $today)
            ->whereNotIn('status', ['Hủy', 'Đã hủy', 'Báo giá'])
            ->sum('total_amount');

        $monthRevenue = Order::where('created_at', '>=', now()->startOfMonth())
            ->whereNotIn('status', ['Hủy', 'Đã hủy', 'Báo giá'])
            ->sum('total_amount');

        $totalProducts = Product::where('is_active', true)->count();
        $totalDealers = Dealer::count();
        $activeVehicles = Vehicle::where('status', '!=', 'Bảo dưỡng')->count();

        $recentOrders = Order::with(['dealer', 'items'])
            ->latest()
            ->take(10)
            ->get();

        $ordersByStatus = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('dashboard', compact(
            'todayOrdersCount',
            'todayRevenue',
            'monthRevenue',
            'totalProducts',
            'totalDealers',
            'activeVehicles',
            'recentOrders',
            'ordersByStatus'
        ));
    }

    /**
     * Display the Product catalog with all price tiers
     */
    public function products(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tax_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('specification', 'like', "%{$search}%");
            });
        }

        if ($catId = $request->input('category_id')) {
            $query->where('category_id', $catId);
        }

        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        $products = $query->latest('id')->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $priceTiers = PriceTier::where('status', 'Hoạt động')->orderBy('order_num')->orderBy('id')->get();

        return view('products.index', compact('products', 'categories', 'brands', 'priceTiers'));
    }

    /**
     * Create Product Form Page (Dedicated Page)
     */
    public function productCreate()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $subcategories = Subcategory::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $priceTiers = PriceTier::where('status', 'Hoạt động')->orderBy('order_num')->orderBy('id')->get();
        return view('products.form', [
            'product' => null,
            'categories' => $categories,
            'brands' => $brands,
            'subcategories' => $subcategories,
            'warehouses' => $warehouses,
            'priceTiers' => $priceTiers,
        ]);
    }

    /**
     * Edit Product Form Page (Dedicated Page)
     */
    public function productEdit($id)
    {
        $product = Product::with(['category', 'brand'])->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $subcategories = Subcategory::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $priceTiers = PriceTier::where('status', 'Hoạt động')->orderBy('order_num')->orderBy('id')->get();
        return view('products.form', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'subcategories' => $subcategories,
            'warehouses' => $warehouses,
            'priceTiers' => $priceTiers,
        ]);
    }

    /**
     * Save Product (Create or Update)
     */
    public function productSave(Request $request)
    {
        $id = $request->input('id');

        $request->validate([
            'sku' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit' => 'required|string|max:50',
        ]);

        $conversionRate = (float)$request->input('conversion_rate', 1);
        if ($conversionRate <= 0) {
            $conversionRate = 1;
        }

        $unit = trim($request->input('unit'));
        $conversionUnit = trim($request->input('conversion_unit', ''));
        $spec = trim($request->input('specification', ''));
        if (empty($spec) && !empty($conversionUnit) && $conversionRate > 1) {
            $spec = "1 {$unit} = {$conversionRate} {$conversionUnit}";
        }

        $costPrice = (float)round((float)$request->input('cost_price', 0));
        $convCostPrice = (float)$request->input('conversion_price', 0);
        if ($convCostPrice <= 0 && $costPrice > 0) {
            $convCostPrice = ($conversionRate > 1) ? (float)round($costPrice / $conversionRate) : $costPrice;
        } else {
            $convCostPrice = (float)round($convCostPrice);
        }

        $priceTiersInput = $request->input('price_tiers', []);
        $formattedPriceTiers = [];

        if (is_array($priceTiersInput)) {
            foreach ($priceTiersInput as $tier) {
                if (!empty($tier['name'])) {
                    $tierPrice = (float)round((float)($tier['price'] ?? 0));
                    $tierConvPrice = (float)($tier['conversion_price'] ?? 0);
                    if ($tierConvPrice <= 0 && $tierPrice > 0) {
                        $tierConvPrice = ($conversionRate > 1) ? (float)round($tierPrice / $conversionRate) : $tierPrice;
                    } else {
                        $tierConvPrice = (float)round($tierConvPrice);
                    }
                    $formattedPriceTiers[] = [
                        'name' => trim($tier['name']),
                        'price' => $tierPrice,
                        'conversion_price' => $tierConvPrice,
                    ];
                }
            }
        }

        $data = [
            'sku' => trim($request->input('sku')),
            'name' => trim($request->input('name')),
            'tax_name' => trim($request->input('tax_name')) ?: trim($request->input('name')),
            'category_id' => (int)$request->input('category_id'),
            'brand_id' => (int)$request->input('brand_id'),
            'subcategory' => trim($request->input('subcategory', '')),
            'subcategory_id' => $request->filled('subcategory_id') ? (int)$request->input('subcategory_id') : null,
            'specification' => $spec,
            'unit' => $unit,
            'conversion_unit' => $conversionUnit ?: null,
            'conversion_rate' => $conversionRate,
            'conversion_price' => $convCostPrice,
            'cost_price' => $costPrice,
            'vat_rate' => (float)$request->input('vat_rate', 10),
            'price_tiers' => $formattedPriceTiers,
            'stock_quantity' => (float)$request->input('stock_quantity', 0),
            'min_stock_alert' => (float)$request->input('min_stock_alert', 10),
            'max_stock_alert' => $request->filled('max_stock_alert') ? (float)$request->input('max_stock_alert') : null,
            'location' => trim($request->input('location', 'Kho Tổng Mê Linh')),
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($id) {
            $product = Product::findOrFail($id);
            $product->update($data);
            $message = 'Cập nhật sản phẩm thành công!';
        } else {
            $product = Product::create($data);
            $message = 'Thêm mới sản phẩm thành công!';
        }

        self::clearPosProductsCache();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'product' => $product->load(['category', 'brand']),
            ]);
        }

        return redirect('/cat_products')->with('success', $message);
    }

    /**
     * Delete Product
     */
    public function productDelete($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            self::clearPosProductsCache();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Import Products from Excel
     */
    public function importProductsExcel(Request $request, ProductExcelImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        try {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'zip'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Định dạng file không hợp lệ! Vui lòng tải lên file Excel (.xlsx)',
                ], 422);
            }

            $result = $importService->import($file->getRealPath());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi import file Excel sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel Template for Products
     */
    public function downloadProductsTemplate(Request $request)
    {
        $type = strtolower($request->query('type', 'hdpe'));
        if ($type === 'pvc') {
            $samplePath = base_path('IMPORT ỐNG PVC TP.xlsx');
            if (file_exists($samplePath)) {
                return response()->download($samplePath, 'Mau_Import_Ong_PVC_2_DonVi.xlsx');
            }
        }

        $samplePath = base_path('IMPORT ỐNG HDPE TP.xlsx');
        if (file_exists($samplePath)) {
            return response()->download($samplePath, 'Mau_Import_Ong_HDPE_1_DonVi.xlsx');
        }
        return response()->json(['error' => 'Template file not found'], 404);
    }

    /**
     * Display the Orders listing
     */
    public function orders(Request $request)
    {
        $query = Order::with(['dealer', 'items', 'creator', 'assignedDriver']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['Soạn hàng', 'Soạn bốc'])) {
                $query->whereIn('status', ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng']);
            } elseif ($status === 'Chốt đơn') {
                $query->whereIn('status', ['Chốt đơn', 'Đã duyệt', 'Chờ duyệt', 'Chờ kế toán duyệt']);
            } elseif ($status === 'Kiểm hàng') {
                $query->whereIn('status', ['Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng', 'Sẵn sàng xuất xe']);
            } elseif ($status === 'Đang vận chuyển') {
                $query->whereIn('status', ['Đang vận chuyển', 'Đang giao hàng']);
            } elseif ($status === 'Đã giao') {
                $query->whereIn('status', ['Đã giao', 'Giao thành công']);
            } elseif ($status === 'Hủy') {
                $query->whereIn('status', ['Hủy', 'Đã hủy']);
            } elseif (in_array($status, ['Đơn thiếu', 'Báo thiếu', 'Kho báo thiếu'])) {
                $query->where(function ($q) {
                    $q->where('shortage_reported', true)
                      ->orWhere('status', 'Kho báo thiếu hàng');
                });
            } elseif (in_array($status, ['Giao sau', 'Đơn giao sau', 'Đơn nợ', 'Nợ hàng'])) {
                $query->whereIn('status', ['Giao sau', 'Đơn giao sau', 'Nợ hàng']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($orderType = $request->input('order_type')) {
            if (in_array($orderType, ['backorder', 'debt'])) {
                $query->where(function ($q) {
                    $q->whereIn('order_type', ['backorder', 'debt'])
                      ->orWhereIn('status', ['Giao sau', 'Đơn giao sau', 'Nợ hàng']);
                });
            } else {
                $query->where('order_type', $orderType);
            }
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->latest('id')->paginate(20)->withQueryString();
        $dealers = Dealer::select('id', 'name', 'phone', 'tier', 'address')->get();

        return view('orders.index', compact('orders', 'dealers'));
    }

    /**
     * Create Order Form
     */
    /**
     * Get optimized, cached products catalog for Order creation / POS
     */
    public static function getPosProductsCatalog()
    {
        return Cache::remember('orders_products_catalog_v1', 900, function () {
            $warehouses = Warehouse::where('is_active', true)->get(['id', 'name', 'code'])->keyBy('id');
            $stocks = WarehouseStock::select('product_id', 'warehouse_id', 'stock_quantity')->get();
            $stockMap = [];
            foreach ($stocks as $s) {
                if (isset($warehouses[$s->warehouse_id])) {
                    $wh = $warehouses[$s->warehouse_id];
                    $stockMap[$s->product_id][$wh->name] = (float)$s->stock_quantity;
                    $stockMap[$s->product_id][$wh->code] = (float)$s->stock_quantity;
                    $stockMap[$s->product_id][$wh->id] = (float)$s->stock_quantity;
                }
            }

            return Product::where('is_active', true)
                ->select('id', 'sku', 'name', 'unit', 'conversion_unit', 'conversion_rate', 'conversion_price', 'cost_price', 'price_tiers', 'vat_rate', 'stock_quantity')
                ->orderBy('name')
                ->get()
                ->map(function ($p) use ($stockMap) {
                    return [
                        'id' => $p->id,
                        'sku' => $p->sku,
                        'name' => $p->name,
                        'unit' => $p->unit,
                        'conversion_unit' => $p->conversion_unit,
                        'conversion_rate' => (float)$p->conversion_rate,
                        'conversion_price' => (float)$p->conversion_price,
                        'cost_price' => (float)$p->cost_price,
                        'price_tiers' => $p->price_tiers,
                        'vat_rate' => (float)$p->vat_rate,
                        'stock_quantity' => (float)$p->stock_quantity,
                        'warehouse_stocks' => $stockMap[$p->id] ?? [],
                    ];
                })
                ->all();
        });
    }

    public static function clearPosProductsCache()
    {
        Cache::forget('orders_products_catalog_v1');
    }

    /**
     * Lấy danh sách kho khả dụng theo vai trò: Lái xe chỉ được xuất từ Kho Xe của mình, các user khác được chọn tất cả
     */
    private function getAvailableWarehousesForUser()
    {
        $user = auth()->user();
        $isDriver = false;
        $driverVehicle = null;

        if ($user) {
            $isDriver = ($user->staff_type === 'driver' || $user->department === 'Logistics' || (method_exists($user, 'hasRole') && ($user->hasRole('Fleet') || $user->hasRole('Driver'))));
            if ($isDriver) {
                $driverVehicle = Vehicle::where('driver_id', $user->id)->first();
                if (!$driverVehicle) {
                    $recentDispatch = \App\Models\VanSaleDispatch::where('driver_id', $user->id)->latest('id')->first();
                    if ($recentDispatch && $recentDispatch->vehicle_plate) {
                        $driverVehicle = Vehicle::where('plate_number', $recentDispatch->vehicle_plate)->first();
                    }
                }
            }
        }

        if ($isDriver) {
            // Lái xe: CHỈ có duy nhất Kho lưu động trên xe của chính mình
            $plate = $driverVehicle?->plate_number ?? '29C-888.99';
            $vehName = 'Kho Xe ' . $plate . ' (' . $user->name . ')';
            $warehouses = collect([
                (object)[
                    'id'                   => 'xe_' . ($driverVehicle?->id ?? $user->id),
                    'name'                 => $vehName,
                    'code'                 => $plate,
                    'is_vehicle_warehouse' => true,
                    'plate_number'         => $plate,
                    'driver_name'          => $user->name,
                ]
            ]);
        } else {
            // Admin, Quản lý, Sale, Kế toán: Có đầy đủ các kho công ty + các kho xe
            $physicalWarehouses = Warehouse::where('is_active', true)->orderBy('id')->get(['id', 'name', 'code'])->map(function($w) {
                return (object)[
                    'id'                   => $w->id,
                    'name'                 => $w->name,
                    'code'                 => $w->code,
                    'is_vehicle_warehouse' => false,
                    'plate_number'         => '',
                    'driver_name'          => '',
                ];
            });

            $vehicleWarehouses = Vehicle::with('driver:id,name')->get()->map(function($v) {
                return (object)[
                    'id'                   => 'xe_' . $v->id,
                    'name'                 => 'Kho Xe ' . $v->plate_number . ($v->driver ? ' (' . $v->driver->name . ')' : ''),
                    'code'                 => $v->plate_number,
                    'is_vehicle_warehouse' => true,
                    'plate_number'         => $v->plate_number,
                    'driver_name'          => $v->driver?->name ?? '',
                ];
            });

            $warehouses = $physicalWarehouses->concat($vehicleWarehouses);
        }

        return [$warehouses, $isDriver, $driverVehicle];
    }

    public function orderCreate()
    {
        $dealers = Dealer::select('id', 'name', 'code', 'phone', 'address', 'tier', 'current_debt', 'credit_limit')
            ->orderBy('name')
            ->get();
        [$warehouses, $isDriverUser, $driverVehicle] = $this->getAvailableWarehousesForUser();
        $products = self::getPosProductsCatalog();
        $drivers = User::select('id', 'name', 'phone')->get();
        $priceTiers = PriceTier::where('status', 'Hoạt động')
            ->orderByRaw("CASE WHEN name LIKE '%Nguyên Giá%' THEN 0 ELSE 1 END")
            ->orderBy('order_num')
            ->orderBy('id')
            ->get();
        $today = date('Y-m-d');
        $salesPolicies = SalesPolicy::with('freeProduct:id,name,sku,unit,cost_price,retail_price')
            ->where('status', 'Hoạt động')
            ->where(function($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->orderBy('id')
            ->get();

        return view('orders.form', [
            'order' => null,
            'dealers' => $dealers,
            'warehouses' => $warehouses,
            'isDriverUser' => $isDriverUser,
            'driverVehicle' => $driverVehicle,
            'products' => $products,
            'drivers' => $drivers,
            'priceTiers' => $priceTiers,
            'salesPolicies' => $salesPolicies,
        ]);
    }

    /**
     * Edit Order Form
     */
    public function orderEdit($id)
    {
        $order = Order::with(['items.product', 'dealer'])->findOrFail($id);
        $dealers = Dealer::select('id', 'name', 'code', 'phone', 'address', 'tier', 'current_debt', 'credit_limit')
            ->orderBy('name')
            ->get();
        [$warehouses, $isDriverUser, $driverVehicle] = $this->getAvailableWarehousesForUser();
        $products = self::getPosProductsCatalog();
        $drivers = User::select('id', 'name', 'phone')->get();
        $priceTiers = PriceTier::where('status', 'Hoạt động')
            ->orderByRaw("CASE WHEN name LIKE '%Nguyên Giá%' THEN 0 ELSE 1 END")
            ->orderBy('order_num')
            ->orderBy('id')
            ->get();
        $today = date('Y-m-d');
        $salesPolicies = SalesPolicy::with('freeProduct:id,name,sku,unit,cost_price,retail_price')
            ->where('status', 'Hoạt động')
            ->where(function($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->orderBy('id')
            ->get();

        return view('orders.form', [
            'order' => $order,
            'dealers' => $dealers,
            'warehouses' => $warehouses,
            'isDriverUser' => $isDriverUser,
            'driverVehicle' => $driverVehicle,
            'products' => $products,
            'drivers' => $drivers,
            'priceTiers' => $priceTiers,
            'salesPolicies' => $salesPolicies,
        ]);
    }

    /**
     * Display Dealers
     */
    public function dealers(Request $request)
    {
        $query = Dealer::with(['salesRep', 'orders']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($tier = $request->input('tier')) {
            $query->where('tier', $tier);
        }

        $dealers = $query->orderBy('name')->paginate(18)->withQueryString();
        $currentView = $request->input('view', 'card');

        return view('dealers.index', compact('dealers', 'currentView'));
    }

    /**
     * Dedicated Page: Create Dealer / Customer
     */
    public function dealerCreate()
    {
        $salesReps = User::select('id', 'name', 'phone', 'department', 'code')->orderBy('name')->get();
        $priceTiers = PriceTier::where('status', 'Hoạt động')->orderBy('order_num')->get();

        $dealersCount = Dealer::count() + 1;
        $nextCode = 'DL-HN-' . str_pad((string)$dealersCount, 3, '0', STR_PAD_LEFT);
        while (Dealer::where('code', $nextCode)->exists()) {
            $dealersCount++;
            $nextCode = 'DL-HN-' . str_pad((string)$dealersCount, 3, '0', STR_PAD_LEFT);
        }

        return view('dealers.form', [
            'dealer' => null,
            'isEdit' => false,
            'salesReps' => $salesReps,
            'priceTiers' => $priceTiers,
            'nextCode' => $nextCode,
        ]);
    }

    /**
     * Dedicated Page: Edit Dealer / Customer
     */
    public function dealerEdit($id)
    {
        $dealer = Dealer::with('salesRep')->findOrFail($id);
        $salesReps = User::select('id', 'name', 'phone', 'department', 'code')->orderBy('name')->get();
        $priceTiers = PriceTier::where('status', 'Hoạt động')->orderBy('order_num')->get();

        return view('dealers.form', [
            'dealer' => $dealer,
            'isEdit' => true,
            'salesReps' => $salesReps,
            'priceTiers' => $priceTiers,
            'nextCode' => $dealer->code,
        ]);
    }

    /**
     * Save Dealer / Customer (Create or Update)
     */
    public function dealerSave(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $id = $data['id'] ?? null;
        $name = trim($data['name'] ?? '');

        if (!$name) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Tên đại lý là bắt buộc'], 422);
            }
            return back()->withInput()->with('error', 'Tên đại lý là bắt buộc');
        }

        $creditLimit = (float) str_replace(['.', ',', ' '], '', (string)($data['credit_limit'] ?? ($data['creditLimit'] ?? 100000000)));
        $initialDebt = (float) str_replace(['.', ',', ' '], '', (string)($data['initial_debt'] ?? ($data['initialDebt'] ?? 0)));
        $salesRepId = !empty($data['sales_rep_id']) ? (int)$data['sales_rep_id'] : (!empty($data['salesRepId']) ? (int)$data['salesRepId'] : null);

        if ($id) {
            $dealer = Dealer::findOrFail($id);
            $dealerData = [
                'name'              => $name,
                'owner_name'        => $data['owner_name'] ?? ($data['ownerName'] ?? $dealer->owner_name),
                'phone'             => $data['phone'] ?? $dealer->phone,
                'tax_code'          => array_key_exists('tax_code', $data) ? $data['tax_code'] : (array_key_exists('taxCode', $data) ? $data['taxCode'] : $dealer->tax_code),
                'referrer'          => array_key_exists('referrer', $data) ? $data['referrer'] : $dealer->referrer,
                'sales_rep_id'      => $salesRepId !== null ? $salesRepId : $dealer->sales_rep_id,
                'address'           => $data['address'] ?? $dealer->address,
                'new_city'          => $data['new_city'] ?? ($data['newCity'] ?? $dealer->new_city),
                'new_ward'          => $data['new_ward'] ?? ($data['newWard'] ?? $dealer->new_ward),
                'new_district'      => $data['new_district'] ?? ($data['newDistrict'] ?? $dealer->new_district),
                'old_city'          => $data['old_city'] ?? ($data['oldCity'] ?? $dealer->old_city),
                'old_district'      => $data['old_district'] ?? ($data['oldDistrict'] ?? $dealer->old_district),
                'old_ward'          => $data['old_ward'] ?? ($data['oldWard'] ?? $dealer->old_ward),
                'city'              => $data['old_city'] ?? ($data['new_city'] ?? ($data['city'] ?? $dealer->city)),
                'district'          => $data['old_district'] ?? ($data['new_district'] ?? ($data['district'] ?? $dealer->district)),
                'tier'              => $data['tier'] ?? $dealer->tier,
                'credit_limit'      => (isset($data['credit_limit']) || isset($data['creditLimit'])) ? $creditLimit : (float)$dealer->credit_limit,
                'initial_debt'      => (isset($data['initial_debt']) || isset($data['initialDebt'])) ? $initialDebt : (float)$dealer->initial_debt,
                'payment_term_days' => (isset($data['payment_term_days']) || isset($data['paymentTermDays'])) ? (int)($data['payment_term_days'] ?? $data['paymentTermDays']) : (int)$dealer->payment_term_days,
                'expansion_notes'   => array_key_exists('notes', $data) ? $data['notes'] : (array_key_exists('expansion_notes', $data) ? $data['expansion_notes'] : $dealer->expansion_notes),
                'status'            => $data['status'] ?? $dealer->status,
            ];
            if (!empty($data['code'])) {
                $dealerData['code'] = $data['code'];
            }
            $dealer->update($dealerData);
            $msg = 'Cập nhật thông tin đại lý ' . $dealer->name . ' thành công!';
        } else {
            $dealerData = [
                'name'              => $name,
                'owner_name'        => $data['owner_name'] ?? ($data['ownerName'] ?? $name),
                'phone'             => $data['phone'] ?? '',
                'tax_code'          => $data['tax_code'] ?? ($data['taxCode'] ?? null),
                'referrer'          => $data['referrer'] ?? null,
                'sales_rep_id'      => $salesRepId,
                'address'           => $data['address'] ?? '',
                'new_city'          => $data['new_city'] ?? ($data['newCity'] ?? null),
                'new_ward'          => $data['new_ward'] ?? ($data['newWard'] ?? null),
                'new_district'      => $data['new_district'] ?? ($data['newDistrict'] ?? null),
                'old_city'          => $data['old_city'] ?? ($data['oldCity'] ?? ($data['city'] ?? null)),
                'old_district'      => $data['old_district'] ?? ($data['oldDistrict'] ?? ($data['district'] ?? null)),
                'old_ward'          => $data['old_ward'] ?? ($data['oldWard'] ?? null),
                'city'              => $data['old_city'] ?? ($data['new_city'] ?? ($data['city'] ?? 'Hà Nội')),
                'district'          => $data['old_district'] ?? ($data['new_district'] ?? ($data['district'] ?? '')),
                'tier'              => $data['tier'] ?? 'Đại lý Cấp 1',
                'credit_limit'      => $creditLimit,
                'initial_debt'      => $initialDebt,
                'payment_term_days' => (int) ($data['payment_term_days'] ?? ($data['paymentTermDays'] ?? 30)),
                'expansion_notes'   => $data['notes'] ?? ($data['expansion_notes'] ?? null),
                'status'            => $data['status'] ?? 'Hoạt động',
            ];
            $code = $data['code'] ?? null;
            if (!$code) {
                $count = Dealer::count() + 1;
                $code = 'DL-HN-' . str_pad((string)$count, 3, '0', STR_PAD_LEFT);
                while (Dealer::where('code', $code)->exists()) {
                    $count++;
                    $code = 'DL-HN-' . str_pad((string)$count, 3, '0', STR_PAD_LEFT);
                }
            }
            $dealerData['code'] = $code;
            $dealerData['current_debt'] = $initialDebt;
            $dealer = Dealer::create($dealerData);
            $msg = 'Thêm mới đại lý ' . $dealer->name . ' thành công!';
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'redirect' => url('/dealers'),
                'dealer' => $dealer
            ]);
        }

        return redirect('/dealers')->with('success', $msg);
    }

    /**
     * Delete Dealer / Customer
     */
    public function dealerDelete($id)
    {
        $dealer = Dealer::findOrFail($id);
        $orderCount = Order::where('dealer_id', $dealer->id)->orWhere('dealer_id', $dealer->code)->count();
        if ($orderCount > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể xóa đại lý [{$dealer->name}] vì đã có {$orderCount} đơn hàng liên kết. Hãy chuyển trạng thái sang Khóa hoặc Đóng hoạt động."
                ], 422);
            }
            return back()->with('error', "Không thể xóa đại lý [{$dealer->name}] vì đã có {$orderCount} đơn hàng liên kết!");
        }

        $dealerName = $dealer->name;
        $dealer->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã xóa đại lý [{$dealerName}] thành công!"
            ]);
        }

        return redirect('/dealers')->with('success', "Đã xóa đại lý [{$dealerName}] thành công!");
    }

    /**
     * Display Delivery Dispatch
     */
    public function deliveryDispatch()
    {
        $dispatches = DeliveryDispatch::with(['vehicle', 'driver', 'orders'])->latest()->paginate(20)->withQueryString();
        $vehicles = Vehicle::with(['driver', 'deliveryRoute'])->get();
        $routes = DeliveryRoute::all();
        $readyOrders = Order::whereIn('status', [
            'Chốt đơn', 'Đã duyệt', 'Soạn hàng', 'Đang bốc hàng', 'Đã bốc xong',
            'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Đang vận chuyển', 'Giao thành công'
        ])
            ->with(['dealer', 'items'])
            ->latest('id')
            ->get();

        // Dữ liệu Hàng Lẻ Xe (Bán dạo / Lưu động theo xe)
        $vanDispatches = \App\Models\VanSaleDispatch::with(['items', 'driver'])
            ->latest('id')
            ->take(60)
            ->get();
        $totalVanStockAmount = \App\Models\VanSaleDispatch::where('status', 'dispatched')->sum('total_amount');
        $allDrivers = \App\Models\User::where('staff_type', 'driver')
            ->orWhere('department', 'LIKE', '%Vận tải%')
            ->orWhere('department', 'LIKE', '%Lái xe%')
            ->orWhere('department', 'LIKE', '%Logistics%')
            ->orWhereHas('roles', function($q) { $q->where('slug', 'driver'); })
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'staff_type', 'department', 'current_debt']);
        $availableProducts = \App\Models\Product::where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'unit', 'cost_price', 'stock_quantity']);

        return view('delivery.dispatch', compact(
            'dispatches', 'vehicles', 'routes', 'readyOrders',
            'vanDispatches', 'totalVanStockAmount', 'allDrivers', 'availableProducts'
        ));
    }

    /**
     * Display Financial Funds & Transactions
     */
    public function funds(Request $request)
    {
        $query = \App\Models\Transaction::with(['fund', 'order']);
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_partner_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $transactions = $query->latest('id')->paginate(20)->withQueryString();
        $funds = \App\Models\FinancialFund::all();
        $totalThu = \App\Models\Transaction::where('type', 'Thu')->sum('amount');
        $totalChi = \App\Models\Transaction::where('type', 'Chi')->sum('amount');

        return view('funds.index', compact('transactions', 'funds', 'totalThu', 'totalChi'));
    }

    /**
     * Display Staff Directory
     */
    public function staff(Request $request)
    {
        $roles = \App\Models\Role::with('permissions')->get();
        $branches = \App\Models\Branch::all();
        $users = User::with(['roles.permissions', 'branch'])->get()->map(function ($u) {
            $hasCustom = \App\Models\UserRolePermission::where('user_id', $u->id)->exists();
            if ($hasCustom) {
                $u->custom_permissions = \App\Models\UserRolePermission::where('user_id', $u->id)
                    ->with('permission')
                    ->get()
                    ->pluck('permission.slug')
                    ->filter()
                    ->values()
                    ->toArray();
            } else {
                $u->custom_permissions = null;
            }
            return $u;
        });

        $query = User::with(['roles.permissions', 'branch']);
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $staff = $query->latest('id')->paginate(50)->withQueryString();
        return view('staff.index', compact('staff', 'users', 'roles', 'branches'));
    }

    /**
     * View: Create New Staff Member
     */
    public function staffCreate(Request $request)
    {
        $roles = \App\Models\Role::with('permissions')->get();
        $branches = \App\Models\Branch::all();
        $dealers = Dealer::select('id', 'name', 'code')->orderBy('name')->get();
        $user = new User([
            'status' => 'Đang làm việc',
            'department' => 'Sales',
            'staff_type' => 'company_sales',
            'base_salary' => 12000000,
            'annual_leave_days' => 12,
            'remaining_leave_days' => 12,
            'branch' => 'Trụ sở Chính - Vinh'
        ]);
        $isEdit = false;
        $userPermissions = [];

        return view('staff.form', compact('user', 'roles', 'branches', 'dealers', 'isEdit', 'userPermissions'));
    }

    /**
     * View: Edit Staff Member & Permissions
     */
    public function staffEdit(Request $request, $id)
    {
        $user = User::with(['roles.permissions', 'branch', 'dealer'])->findOrFail($id);
        $roles = \App\Models\Role::with('permissions')->get();
        $branches = \App\Models\Branch::all();
        $dealers = Dealer::select('id', 'name', 'code')->orderBy('name')->get();
        $isEdit = true;

        $hasCustom = \App\Models\UserRolePermission::where('user_id', $user->id)->exists();
        if ($hasCustom) {
            $userPermissions = \App\Models\UserRolePermission::where('user_id', $user->id)
                ->with('permission')
                ->get()
                ->pluck('permission.slug')
                ->filter()
                ->values()
                ->toArray();
        } else {
            $userPermissions = $user->roles->first()?->permissions->pluck('slug')->toArray() ?? [];
        }

        return view('staff.form', compact('user', 'roles', 'branches', 'dealers', 'isEdit', 'userPermissions'));
    }

    /**
     * Handle Save (Create/Update) Staff and Custom Permissions
     */
    public function staffSave(Request $request)
    {
        $id = $request->input('id');
        $roleSlug = $request->input('role', 'Sales_Rep');
        $permissions = $request->input('permissions', []);
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?: [];
        }

        $branchId = $request->input('branch_id');
        $branchName = $request->input('branch');
        if ($branchId && is_numeric($branchId)) {
            $b = \App\Models\Branch::find($branchId);
            if ($b) $branchName = $b->name;
        }

        $data = [
            'name'                 => $request->input('name'),
            'email'                => $request->input('email'),
            'code'                 => $request->input('code'),
            'phone'                => $request->input('phone'),
            'dob'                  => $request->input('dob') ?: null,
            'citizen_id'           => $request->input('citizen_id'),
            'department'           => $request->input('department', 'Sales'),
            'status'               => $request->input('status', 'Đang làm việc'),
            'staff_type'           => $request->input('staff_type', 'company_sales'),
            'dealer_id'            => $request->input('dealer_id') ?: null,
            'credit_limit'         => (float) str_replace(['.', ',', ' '], '', $request->input('credit_limit', 0)),
            'base_salary'          => (float) str_replace(['.', ',', ' '], '', $request->input('base_salary', 0)),
            'annual_leave_days'    => (int) $request->input('annual_leave_days', 12),
            'remaining_leave_days' => (float) $request->input('remaining_leave_days', 12),
            'branch'               => $branchName ?: 'Trụ sở Chính - Vinh',
            'branch_id'            => $branchId ?: null,
            'bank_account'         => $request->input('bank_account'),
            'bank_name'            => $request->input('bank_name'),
            'address'              => $request->input('address'),
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make(trim($request->input('password')));
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($id && is_numeric($id)) {
                $user = User::findOrFail($id);
                $user->update($data);
            } else {
                if (empty($data['password'])) {
                    $data['password'] = \Illuminate\Support\Facades\Hash::make('123456');
                }
                $user = User::create($data);
            }

            // Sync Role
            $role = \App\Models\Role::where('slug', $roleSlug)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);

                // Sync custom permissions for this user within this role
                \App\Models\UserRolePermission::where('user_id', $user->id)
                    ->where('role_id', $role->id)
                    ->delete();

                if (!empty($permissions) && is_array($permissions)) {
                    $rolePermissionIds = $role->permissions()->pluck('permissions.id')->toArray();
                    $allowedPermissionIds = \App\Models\Permission::whereIn('slug', $permissions)
                        ->whereIn('id', $rolePermissionIds)
                        ->pluck('id');

                    $records = [];
                    $now = now();
                    foreach ($allowedPermissionIds as $pId) {
                        $records[] = [
                            'user_id'       => $user->id,
                            'role_id'       => $role->id,
                            'permission_id' => $pId,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ];
                    }
                    if (!empty($records)) {
                        \App\Models\UserRolePermission::insert($records);
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lưu thông tin và phân quyền nhân sự thành công!',
                    'redirect' => url('/staff'),
                ]);
            }

            return redirect('/staff')->with('success', 'Đã lưu thông tin và cấu hình phân quyền cho nhân sự ' . $user->name . ' thành công!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Lỗi khi lưu nhân sự: ' . $e->getMessage());
        }
    }

    /**
     * Display Attendance Records (GPS Check-in)
     */
    public function attendance(Request $request)
    {
        $attendance = \App\Models\AttendanceRecord::with('user')->latest('id')->paginate(20)->withQueryString();
        return view('attendance.index', compact('attendance'));
    }

    /**
     * Display Proposals & Approvals
     */
    public function proposals(Request $request)
    {
        $proposals = \App\Models\Proposal::with('creator')->latest('id')->paginate(20)->withQueryString();
        return view('proposals.index', compact('proposals'));
    }

    /**
     * Display Task Assignments
     */
    public function tasks(Request $request)
    {
        $tasks = \App\Models\TaskAssignment::with('creator')->latest('id')->paginate(20)->withQueryString();
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Display System Audit Logs
     */
    public function auditLogs(Request $request)
    {
        $auditLogs = \App\Models\AuditLog::latest('id')->paginate(20)->withQueryString();
        return view('audit.index', compact('auditLogs'));
    }

    /**
     * Display Payroll Records
     */
    public function payroll(Request $request)
    {
        $payroll = \App\Models\PayrollRecord::latest('id')->paginate(20)->withQueryString();
        return view('payroll.index', compact('payroll'));
    }

    /**
     * Display Categories Hub (Warehouses, Branches, Fund types, Brands, Categories, Routes, Vehicles, Policies)
     */
    public function categories(Request $request, $tab = 'warehouses')
    {
        $warehouses = Warehouse::with('branch')->get();
        $branches = Branch::all();
        $funds = FinancialFund::all();
        $categories = Category::withCount('products')->get();
        $brands = Brand::withCount('products')->get();
        $subcategories = Subcategory::with(['category', 'brand'])->withCount('products')->get();
        $routes = DeliveryRoute::all();
        $vehicles = Vehicle::with(['driver', 'deliveryRoute'])->get();
        $policies = SalesPolicy::all();
        $users = User::select('id', 'name', 'phone', 'department')->orderBy('name')->get();
        $activeTab = $request->input('tab', $tab);

        $deliveryCtrl = new DeliveryController();
        $assignments = $deliveryCtrl->getVehicleAssignmentsBootstrapData();
        $initialVehicleId = $request->input('vehicle_id') ?? $request->input('vehicleId') ?? '';
        $initialPlate = $request->input('plate') ?? '';
        $initialAction = $request->input('action') ?? '';

        return view('categories.index', compact(
            'warehouses', 'branches', 'funds', 'categories',
            'brands', 'subcategories', 'routes', 'vehicles', 'policies', 'users', 'activeTab',
            'assignments', 'initialVehicleId', 'initialPlate', 'initialAction'
        ));
    }

    /**
     * Dedicated Page: Create Sales Policy
     */
    public function salesPolicyCreate()
    {
        $dealers = Dealer::select('id', 'name', 'code', 'phone', 'tier', 'address')->orderBy('name')->get();
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->select('id', 'sku', 'name', 'unit', 'cost_price', 'category_id', 'brand_id')
            ->orderBy('name')
            ->get();

        $count = SalesPolicy::count() + 1;
        $nextCode = 'CSBH-' . str_pad((string)$count, 3, '0', STR_PAD_LEFT);
        while (SalesPolicy::where('code', $nextCode)->exists()) {
            $count++;
            $nextCode = 'CSBH-' . str_pad((string)$count, 3, '0', STR_PAD_LEFT);
        }

        return view('sales_policies.form', [
            'policy' => null,
            'dealers' => $dealers,
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'nextCode' => $nextCode,
        ]);
    }

    /**
     * Dedicated Page: Edit Sales Policy
     */
    public function salesPolicyEdit($id)
    {
        $policy = SalesPolicy::with(['freeProduct', 'creator'])->findOrFail($id);
        $dealers = Dealer::select('id', 'name', 'code', 'phone', 'tier', 'address')->orderBy('name')->get();
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->select('id', 'sku', 'name', 'unit', 'cost_price', 'category_id', 'brand_id')
            ->orderBy('name')
            ->get();

        return view('sales_policies.form', [
            'policy' => $policy,
            'dealers' => $dealers,
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'nextCode' => $policy->code,
        ]);
    }

    /**
     * Save Sales Policy (Create or Update)
     */
    public function salesPolicySave(Request $request)
    {
        $id = $request->input('id');
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $code = $request->input('code');
        if (!$code) {
            $count = SalesPolicy::count() + 1;
            $code = 'CSBH-' . str_pad((string)$count, 3, '0', STR_PAD_LEFT);
            while (SalesPolicy::where('code', $code)->where('id', '!=', $id)->exists()) {
                $count++;
                $code = 'CSBH-' . str_pad((string)$count, 3, '0', STR_PAD_LEFT);
            }
        }

        $policyType = $request->input('policy_type', 'percentage');
        $applyScope = $request->input('apply_scope', 'order');
        if ($policyType === 'quantity') {
            $applyScope = 'product';
        } elseif (!in_array($applyScope, ['order', 'product'])) {
            $applyScope = 'order';
        }

        $customerIds = $request->input('target_customer_ids');
        if (is_string($customerIds)) {
            $customerIds = array_filter(array_map('trim', explode(',', $customerIds)));
        } elseif (!is_array($customerIds)) {
            $customerIds = [];
        }

        $productIds = $request->input('target_product_ids');
        if (is_string($productIds)) {
            $productIds = array_filter(array_map('trim', explode(',', $productIds)));
        } elseif (!is_array($productIds)) {
            $productIds = [];
        }

        $buyQty = $request->input('buy_qty');
        $freeQty = $request->input('free_qty');
        $freeProductId = $request->input('free_product_id');
        $discountPercent = $request->input('discount_percent');
        $discountAmount = $request->input('discount_amount');
        $minOrderValue = $request->input('min_order_value');

        $data = [
            'code' => $code,
            'name' => $request->input('name'),
            'policy_type' => $policyType,
            'apply_scope' => $applyScope,
            'buy_qty' => ($buyQty !== null && $buyQty !== '') ? (float)$buyQty : null,
            'free_qty' => ($freeQty !== null && $freeQty !== '') ? (float)$freeQty : null,
            'free_product_id' => $freeProductId ? (int)$freeProductId : null,
            'discount_percent' => ($discountPercent !== null && $discountPercent !== '') ? (float)$discountPercent : null,
            'discount_amount' => ($discountAmount !== null && $discountAmount !== '') ? (float)$discountAmount : null,
            'min_order_value' => ($minOrderValue !== null && $minOrderValue !== '') ? (float)$minOrderValue : null,
            'target_customer_ids' => array_values($customerIds),
            'target_product_ids' => array_values($productIds),
            'start_date' => $request->input('start_date') ?: null,
            'end_date' => $request->input('end_date') ?: null,
            'status' => $request->input('status', 'Hoạt động'),
            'notes' => $request->input('notes', ''),
        ];

        if ($id) {
            $policy = SalesPolicy::findOrFail($id);
            $policy->update($data);
            $msg = 'Cập nhật chính sách bán hàng thành công!';
        } else {
            $data['created_by'] = auth()->id();
            $policy = SalesPolicy::create($data);
            $msg = 'Tạo mới chính sách bán hàng thành công!';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'redirect' => url('/cat_sales_policies'),
                'policy' => $policy,
            ]);
        }

        return redirect('/cat_sales_policies')->with('success', $msg);
    }

    /**
     * API: Save (Create/Update) Category Item
     */
    public function saveCategoryItem(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        try {
            $item = null;
            switch ($type) {
                case 'warehouses':
                    $data = [
                        'code' => $request->input('code') ?: ('KHO-' . strtoupper(substr(uniqid(), -4))),
                        'name' => $request->input('name'),
                        'type' => $request->input('type_name', $request->input('type', 'Kho vật tư & phụ tùng')),
                        'branch_id' => $request->filled('branch_id') ? (int)$request->input('branch_id') : null,
                        'address' => $request->input('address'),
                        'manager_name' => $request->input('manager_name'),
                        'is_active' => $request->boolean('is_active', true),
                    ];
                    $item = Warehouse::updateOrCreate(['id' => $id], $data);
                    $item->load('branch');
                    break;

                case 'branches':
                    $data = [
                        'code' => $request->input('code') ?: ('CN-' . strtoupper(substr(uniqid(), -4))),
                        'name' => $request->input('name'),
                        'type' => $request->input('type_name', $request->input('type', 'Chi nhánh chính')),
                        'manager' => $request->input('manager'),
                        'phone' => $request->input('phone'),
                        'address' => $request->input('address'),
                        'is_active' => $request->boolean('is_active', true),
                    ];
                    $item = Branch::updateOrCreate(['id' => $id], $data);
                    break;

                case 'fund_types':
                    $data = [
                        'code' => $request->input('code') ?: ('Q-' . strtoupper(substr(uniqid(), -4))),
                        'name' => $request->input('name'),
                        'account_number' => $request->input('account_number'),
                        'bank_name' => $request->input('bank_name'),
                        'current_balance' => (float)($request->input('current_balance', 0)),
                        'is_active' => $request->boolean('is_active', true),
                    ];
                    $item = FinancialFund::updateOrCreate(['id' => $id], $data);
                    break;

                case 'categories':
                    $data = [
                        'code' => $request->input('code') ?: ('PL-' . strtoupper(substr(uniqid(), -4))),
                        'name' => $request->input('name'),
                        'manager_name' => $request->input('manager_name'),
                        'description' => $request->input('description'),
                        'is_active' => $request->boolean('is_active', true),
                    ];
                    $item = Category::updateOrCreate(['id' => $id], $data);
                    $item->loadCount('products');
                    break;

                case 'brands':
                    $data = [
                        'code' => $request->input('code') ?: ('TH-' . strtoupper(substr(uniqid(), -4))),
                        'name' => $request->input('name'),
                        'country' => $request->input('country', 'Việt Nam'),
                        'logo_url' => $request->input('logo_url'),
                        'description' => $request->input('description'),
                        'is_active' => $request->boolean('is_active', true),
                    ];
                    $item = Brand::updateOrCreate(['id' => $id], $data);
                    $item->loadCount('products');
                    break;

                case 'subcategories':
                    $data = [
                        'category_id' => (int)$request->input('category_id'),
                        'brand_id' => $request->filled('brand_id') ? (int)$request->input('brand_id') : null,
                        'code' => $request->input('code') ?: ('NN-' . strtoupper(substr(uniqid(), -4))),
                        'name' => $request->input('name'),
                        'description' => $request->input('description'),
                        'is_active' => $request->boolean('is_active', true),
                    ];
                    $item = Subcategory::updateOrCreate(['id' => $id], $data);
                    $item->load(['category', 'brand']);
                    $item->loadCount('products');
                    break;

                case 'routes':
                    $stopsInput = $request->input('stops');
                    if (is_string($stopsInput)) {
                        $stops = array_filter(array_map('trim', explode(',', $stopsInput)));
                        $stops = !empty($stops) ? array_values($stops) : [$stopsInput];
                    } else {
                        $stops = is_array($stopsInput) ? $stopsInput : [];
                    }
                    $data = [
                        'code' => $request->input('code') ?: ('TGH-' . strtoupper(substr(uniqid(), -4))),
                        'name' => $request->input('name'),
                        'departure_warehouse' => $request->input('departure_warehouse'),
                        'stops' => $stops,
                        'distance_km' => (float)($request->input('distance_km', 0)),
                        'estimated_time_hours' => (float)($request->input('estimated_time_hours', 0)),
                        'toll_fee' => (float)($request->input('toll_fee', 0)),
                        'assigned_vehicle_plate' => $request->input('assigned_vehicle_plate'),
                        'assigned_driver_name' => $request->input('assigned_driver_name'),
                        'is_active' => $request->boolean('is_active', true),
                        'notes' => $request->input('notes'),
                    ];
                    $item = DeliveryRoute::updateOrCreate(['id' => $id], $data);
                    break;

                case 'vehicles':
                    $data = [
                        'plate_number' => $request->input('plate_number'),
                        'vehicle_type' => $request->input('vehicle_type'),
                        'driver_id' => $request->filled('driver_id') ? (int)$request->input('driver_id') : null,
                        'delivery_route_id' => $request->filled('delivery_route_id') ? (int)$request->input('delivery_route_id') : null,
                        'copilot_name' => $request->input('copilot_name'),
                        'capacity_kg' => (float)($request->input('capacity_kg', 0)),
                        'status' => $request->input('status', 'Sẵn sàng'),
                    ];
                    $item = Vehicle::updateOrCreate(['id' => $id], $data);
                    $item->load(['driver', 'deliveryRoute']);
                    break;

                case 'policies':
                    $data = [
                        'code' => $request->input('code') ?: ('CS-' . strtoupper(substr(uniqid(), -4))),
                        'name' => $request->input('name'),
                        'policy_type' => $request->input('policy_type', 'Chiết khấu trực tiếp'),
                        'apply_scope' => $request->input('apply_scope', 'Đại lý toàn hệ thống'),
                        'discount_percent' => (float)($request->input('discount_percent', 0)),
                        'min_order_value' => (float)($request->input('min_order_value', 0)),
                        'start_date' => $request->input('start_date') ?: now()->toDateString(),
                        'end_date' => $request->input('end_date'),
                        'status' => $request->input('status', 'active'),
                        'notes' => $request->input('notes'),
                    ];
                    $item = SalesPolicy::updateOrCreate(['id' => $id], $data);
                    break;

                default:
                    return response()->json(['success' => false, 'message' => 'Loại danh mục không hợp lệ!'], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lưu dữ liệu thành công!',
                'item' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Delete Category Item
     */
    public function deleteCategoryItem(Request $request, $type, $id)
    {
        try {
            switch ($type) {
                case 'warehouses':
                    $record = Warehouse::findOrFail($id);
                    break;
                case 'branches':
                    $record = Branch::findOrFail($id);
                    break;
                case 'fund_types':
                    $record = FinancialFund::findOrFail($id);
                    break;
                case 'categories':
                    $record = Category::findOrFail($id);
                    break;
                case 'brands':
                    $record = Brand::findOrFail($id);
                    break;
                case 'subcategories':
                    $record = Subcategory::findOrFail($id);
                    break;
                case 'routes':
                    $record = DeliveryRoute::findOrFail($id);
                    break;
                case 'vehicles':
                    $record = Vehicle::findOrFail($id);
                    break;
                case 'policies':
                    $record = SalesPolicy::findOrFail($id);
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Loại danh mục không hợp lệ!'], 400);
            }

            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa bản ghi thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Import Categories, Brands, Relations & Subcategories from Excel
     */
    public function importCategoriesExcel(Request $request, CategoryExcelImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        try {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'zip'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Định dạng file không hợp lệ! Vui lòng tải lên file Excel (.xlsx)',
                ], 422);
            }

            $result = $importService->import($file->getRealPath());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi import file Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel Template for Categories, Brands, Subcategories
     */
    public function downloadCategoriesTemplate()
    {
        $samplePath = base_path('PHÂN NHÓM SẢN PHẨM.xlsx');
        if (file_exists($samplePath)) {
            return response()->download($samplePath, 'Mau_Import_Phan_Nhom_San_Pham.xlsx');
        }
        return response()->json(['error' => 'Template file not found'], 404);
    }

    /**
     * Display Price Configuration (Exact match to system master)
     */
    public function priceTiers(Request $request)
    {
        $priceTiers = PriceTier::orderBy('order_num')->orderBy('id')->get();
        return view('pricing.index', compact('priceTiers'));
    }

    /**
     * API: Save Price Tier
     */
    public function savePriceTier(Request $request)
    {
        $id = $request->input('id');
        $name = trim($request->input('name'));
        $code = strtoupper(trim($request->input('code', '')));
        $status = $request->input('status', 'Hoạt động');

        if (empty($name)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên mức giá!'], 422);
        }

        $tier = PriceTier::updateOrCreate(
            ['id' => $id],
            [
                'code' => $code ?: null,
                'name' => $name,
                'status' => $status
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Lưu cấu hình mức giá thành công!',
            'item' => $tier
        ]);
    }

    /**
     * API: Delete Price Tier
     */
    public function deletePriceTier(Request $request, $id)
    {
        try {
            $tier = PriceTier::findOrFail($id);
            $tier->delete();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa mức giá thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Save batch price updates
     */
    public function saveBatchPrices(Request $request)
    {
        $rawProducts = $request->input('products', []);
        foreach ($rawProducts as $item) {
            if (!empty($item['id'])) {
                $product = Product::find($item['id']);
                if ($product) {
                    if (isset($item['cost_price'])) {
                        $product->cost_price = (float)round((float)$item['cost_price']);
                    }
                    $tiers = [
                        ['name' => 'Đại lý Cấp 1', 'price' => (float)round((float)($item['tier_c1'] ?? 0))],
                        ['name' => 'Đại lý Cấp 2', 'price' => (float)round((float)($item['tier_c2'] ?? 0))],
                        ['name' => 'Bán Lẻ', 'price' => (float)round((float)($item['tier_retail'] ?? 0))],
                        ['name' => 'Dự Án', 'price' => (float)round((float)($item['tier_project'] ?? 0))],
                        ['name' => 'Thợ Lắp Đặt', 'price' => (float)round((float)($item['tier_contractor'] ?? 0))],
                    ];
                    $product->price_tiers = $tiers;
                    $product->save();
                }
            }
        }
        return response()->json(['success' => true, 'message' => 'Đã lưu cấu hình bảng giá thành công!']);
    }

    /**
     * Display Roles & Permissions RBAC Matrix
     */
    public function roles()
    {
        $users = User::all();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        return view('roles.index', compact('users', 'roles', 'permissions'));
    }

    /**
     * Display KPI Performance Evaluations
     */
    public function kpi()
    {
        $staffKpi = KpiEvaluation::with('user')->latest()->paginate(20)->withQueryString();
        $deptKpi = DepartmentKpi::all();
        return view('kpi.index', compact('staffKpi', 'deptKpi'));
    }

    /**
     * Display Fleet & Logistics Vehicles
     */
    public function fleet()
    {
        $totalVehicles = Vehicle::count();
        $readyVehicles = Vehicle::where('status', '!=', 'Bảo dưỡng')->count();
        $deliveringVehicles = Vehicle::where('status', 'Đang giao hàng')->count();
        $maintenanceVehicles = Vehicle::where('status', 'Bảo dưỡng')->count();
        $vehicles = Vehicle::with(['driver', 'deliveryRoute'])->paginate(20)->withQueryString();
        return view('fleet.index', compact('vehicles', 'totalVehicles', 'readyVehicles', 'deliveringVehicles', 'maintenanceVehicles'));
    }

    /**
     * Display Customer Debt & Credit Limits
     */
    public function debt(Request $request)
    {
        $query = Dealer::with(['orders' => function($q) {
            $q->latest('id');
        }]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($tier = $request->input('tier')) {
            $query->where('tier', $tier);
        }
        if ($debtFilter = $request->input('debt_status')) {
            if ($debtFilter === 'has_debt') {
                $query->where('current_debt', '>', 0);
            } elseif ($debtFilter === 'in_term') {
                $query->where('current_debt', '>', 0)->whereColumn('current_debt', '<=', 'credit_limit');
            } elseif ($debtFilter === 'overdue') {
                $query->where('current_debt', '>', 0)->whereColumn('current_debt', '>', 'credit_limit');
            } elseif ($debtFilter === 'no_debt') {
                $query->where('current_debt', '<=', 0);
            }
        }

        $dealers = $query->orderByDesc('current_debt')->paginate(20)->withQueryString();
        $totalDebt = Dealer::sum('current_debt');
        $overdueDebt = Dealer::where('current_debt', '>', 0)->whereColumn('current_debt', '>', 'credit_limit')->sum('current_debt');
        $totalCreditLimit = Dealer::sum('credit_limit');
        $inTermDealersCount = Dealer::where('current_debt', '>', 0)->whereColumn('current_debt', '<=', 'credit_limit')->count();

        $selectedDealerId = $request->input('dealer_id');
        $selectedDealer = null;
        if ($selectedDealerId) {
            $selectedDealer = Dealer::with(['orders' => function($q) { $q->latest('id'); }])->find($selectedDealerId);
        }

        return view('dealers.debt', compact(
            'dealers', 'totalDebt', 'overdueDebt', 'totalCreditLimit', 'inTermDealersCount', 'selectedDealer'
        ));
    }

    /**
     * Quản Lý Công Nợ Nhân Viên & Bàn Giao Nợ (Menu Nhân sự)
     */
    public function staffDebt(Request $request)
    {
        // 1. Dữ liệu Danh sách Nhân viên & Người vận chuyển
        $query = \App\Models\User::with('dealer')
            ->where(function ($q) {
                $q->whereIn('staff_type', ['driver', 'company_sales', 'dealer_sales'])
                  ->orWhere('department', 'LIKE', '%Kinh doanh%')
                  ->orWhere('department', 'LIKE', '%Vận tải%')
                  ->orWhere('current_debt', '>', 0);
            });

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($dept = $request->input('department')) {
            $query->where('department', 'LIKE', "%{$dept}%");
        }
        if ($staffType = $request->input('staff_type')) {
            $query->where('staff_type', $staffType);
        }

        $staffList = $query->orderByDesc('current_debt')->get();
        $totalStaffDebt = \App\Models\User::sum('current_debt');
        $debtStaffCount = \App\Models\User::where('current_debt', '>', 0)->count();

        // 2. Dữ liệu Biên bản Bàn giao công nợ
        $handovers = \App\Models\DebtHandover::with(['fromUser', 'toUser', 'targetDealer', 'order'])
            ->latest('id')
            ->take(60)
            ->get();
        $pendingHandoversCount = \App\Models\DebtHandover::where('status', 'pending')->count();

        // Danh sách hỗ trợ Dropdown trong các Modal
        $allStaff = \App\Models\User::select('id', 'name', 'code', 'phone', 'staff_type', 'dealer_id')->orderBy('name')->get();
        $allDealers = Dealer::select('id', 'name', 'code', 'phone', 'tier')->orderBy('name')->get();
        $allOrders = Order::select('id', 'order_code', 'customer_name', 'dealer_id', 'total_amount', 'driver_debt_amount', 'payment_status', 'status', 'created_at', 'warehouse', 'assigned_driver_id', 'creator_id', 'order_creator', 'assigned_vehicle_plate')
            ->latest('id')
            ->take(500)
            ->get()
            ->map(function ($ord) {
                $orderTotal = (float)$ord->total_amount;
                $isPaid = in_array($ord->payment_status, ['Đã thanh toán', 'Thanh toán đủ', 'paid', 'Thanh toán tiền mặt']);
                $isVehicle = str_contains($ord->warehouse ?? '', 'Kho Xe') || !empty($ord->assigned_vehicle_plate);
                return [
                    'id'                 => $ord->id,
                    'order_code'         => $ord->order_code,
                    'customer_name'      => $ord->customer_name ?: 'Khách lẻ',
                    'amount'             => $orderTotal,
                    'total_amount'       => $orderTotal,
                    'driver_debt_amount' => (float)$ord->driver_debt_amount,
                    'payment_status'     => $ord->payment_status ?: 'Chưa thanh toán',
                    'status'             => $ord->status ?: '---',
                    'warehouse'          => $ord->warehouse ?: '',
                    'is_paid'            => $isPaid,
                    'is_vehicle'         => $isVehicle,
                    'creator_name'       => $ord->order_creator ?: '',
                    'assigned_driver_id' => $ord->assigned_driver_id,
                ];
            });

        return view('staff.debt', compact(
            'staffList', 'totalStaffDebt', 'debtStaffCount', 'handovers', 'pendingHandoversCount', 
            'allStaff', 'allDealers', 'allOrders'
        ));
    }

    /**
     * Xem Chi Tiết Công Nợ Khách Hàng (Sổ công nợ, danh sách đơn hàng & lịch sử thanh toán)
     */
    public function dealerDebtDetail($id, Request $request)
    {
        $dealer = Dealer::where('id', $id)->orWhere('code', $id)->firstOrFail();

        $orders = Order::where(function($q) use ($dealer) {
            $q->where('dealer_id', $dealer->id);
            if ($dealer->code) {
                $q->orWhere('dealer_id', $dealer->code);
            }
        })->latest('id')->get();

        $orderIds = $orders->pluck('id')->filter()->toArray();
        $orderCodes = $orders->pluck('order_code')->filter()->toArray();

        $transactions = \App\Models\Transaction::where(function($q) use ($dealer, $orderIds, $orderCodes) {
            $q->where('customer_partner_name', 'like', "%{$dealer->name}%");
            if ($dealer->code) {
                $q->orWhere('customer_partner_name', 'like', "%{$dealer->code}%")
                  ->orWhere('notes', 'like', "%{$dealer->code}%");
            }
            if (!empty($orderIds)) {
                $q->orWhereIn('order_id', $orderIds);
            }
            if (!empty($orderCodes)) {
                foreach ($orderCodes as $oc) {
                    $q->orWhere('notes', 'like', "%{$oc}%");
                }
            }
        })->latest('date')->latest('id')->get();

        $totalOrderAmount = $orders->whereNotIn('status', ['Hủy', 'Đã hủy', 'Báo giá'])->sum('total_amount');
        $creditLimit = (float)($dealer->credit_limit ?? 0);
        $currentDebt = (float)($dealer->current_debt ?? 0);
        $remainingCredit = max(0, $creditLimit - $currentDebt);

        if ($request->wantsJson() || $request->ajax() || $request->query('format') === 'json') {
            return response()->json([
                'success'           => true,
                'dealer'            => $dealer,
                'orders'            => $orders,
                'transactions'      => $transactions,
                'totalOrderAmount'  => $totalOrderAmount,
                'creditLimit'       => $creditLimit,
                'currentDebt'       => $currentDebt,
                'remainingCredit'   => $remainingCredit,
            ]);
        }

        return view('dealers.debt_detail', compact(
            'dealer', 'orders', 'transactions', 
            'totalOrderAmount', 'creditLimit', 'currentDebt', 'remainingCredit'
        ));
    }

    /**
     * Display Reports Center
     */
    public function reports(Request $request, $type = 'sales')
    {
        $salesRevenue = Order::whereNotIn('status', ['Hủy', 'Đã hủy', 'Báo giá'])->sum('total_amount');
        $totalOrdersCount = Order::whereNotIn('status', ['Hủy', 'Đã hủy'])->count();
        $totalProducts = Product::count();
        $totalThu = \App\Models\Transaction::where('type', 'Thu')->sum('amount');
        $totalChi = \App\Models\Transaction::where('type', 'Chi')->sum('amount');
        $totalDebt = Dealer::sum('current_debt');

        return view('reports.index', compact(
            'salesRevenue', 'totalOrdersCount', 'totalProducts',
            'totalThu', 'totalChi', 'totalDebt', 'type'
        ));
    }

    /**
     * Display Personal Profile / Employee Dashboard
     */
    public function profile()
    {
        $user = Auth::user();
        return view('staff.profile', compact('user'));
    }

    /**
     * Display Login page
     */
    public function loginView()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle Web Login form submission
     */
    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $account = trim($credentials['email']);
        $password = $credentials['password'];
        $cleanPhone = preg_replace('/\s+/', '', $account);

        $user = User::where('email', $account)
            ->orWhere('code', $account)
            ->orWhere('phone', $account)
            ->orWhere('phone', $cleanPhone)
            ->orWhereRaw("REPLACE(phone, ' ', '') = ?", [$cleanPhone])
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withInput()->with('error', 'Tài khoản hoặc mật khẩu không chính xác.');
        }

        if ($user->status && $user->status !== 'Đang làm việc') {
            return back()->withInput()->with('error', 'Tài khoản hiện đang bị khóa: ' . $user->status);
        }

        Auth::login($user, $request->has('remember'));

        return redirect()->intended('/dashboard')->with('success', 'Đăng nhập thành công! Chào mừng ' . $user->name);
    }

    /**
     * Handle Web Logout
     */
    public function webLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Bạn đã đăng xuất an toàn.');
    }
}
