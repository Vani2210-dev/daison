<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Models\Warehouse;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Bootstrap data for Inventory Products in SPA
     */
    public function getInventoryBootstrapData()
    {
        return Product::with(['category', 'brand'])->get()->map(function ($p) {
            $priceTiers = $p->price_tiers ?? [];
            $dealerPrice = $p->getPriceForTier('Đại lý Cấp 1', 0);
            $retailPrice = $p->getPriceForTier('Bán Lẻ', 0);

            if ($dealerPrice == 0 && !empty($priceTiers)) {
                $dealerPrice = (float) ($priceTiers[0]['price'] ?? 0);
            }
            if ($retailPrice == 0 && !empty($priceTiers)) {
                $retailPrice = (float) ($priceTiers[count($priceTiers) - 1]['price'] ?? 0);
            }

            $stock = (float) $p->stock_quantity;
            $mqStock = (float) round($stock * 0.55);
            $mvqStock = (float) round($stock * 0.25);
            $dxhStock = (float) round($stock * 0.15);
            $blStock = (float) max(0.0, $stock - $mqStock - $mvqStock - $dxhStock);

            $warehouseStocks = [
                'Kho Tổng Mê Linh (MQ)' => $mqStock,
                'KHO-MQ-01' => $mqStock,
                'Kho Chi Nhánh Vân Quần (MVQ)' => $mvqStock,
                'KHO-MVQ-01' => $mvqStock,
                'Kho Chi Nhánh Đông Anh (DXH)' => $dxhStock,
                'KHO-DXH-01' => $dxhStock,
                'Kho Bảo Lưu & Kiểm Kê' => $blStock,
                'KHO-BL-01' => $blStock,
            ];

            return [
                'id' => (string) $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'taxName' => $p->tax_name ?? $p->name,
                'category' => $p->category?->name ?? 'Ống nhựa PVC',
                'subcategory' => $p->subcategory ?? 'Tiêu chuẩn',
                'brand' => $p->brand?->name ?? 'Bình Minh',
                'specification' => $p->specification ?? '',
                'unit' => $p->unit,
                'conversionUnit' => $p->conversion_unit,
                'conversionRate' => (float) ($p->conversion_rate ?? 1),
                'conversionPrice' => (float) ($p->conversion_price ?? 0),
                'costPrice' => (float) $p->cost_price,
                'latestCostPrice' => (float) ($p->latest_cost_price ?? $p->cost_price),
                'vatRate' => (float) ($p->vat_rate ?? 10),
                'priceTiers' => $priceTiers,
                'dealerPrice' => $dealerPrice,
                'retailPrice' => $retailPrice,
                'stockQuantity' => (float) $p->stock_quantity,
                'warehouseStocks' => $warehouseStocks,
                'reservedQuantity' => (float) $p->reserved_quantity,
                'minStockAlert' => (float) $p->min_stock_alert,
                'location' => ($p->location && $p->location !== 'Kho Tổng') ? $p->location : 'Kho Tổng Mê Linh - Bãi Ngoài 02',
                'qrCode' => $p->qr_code ?? $p->sku,
                'updatedAt' => $p->updated_at ? $p->updated_at->format('Y-m-d') : date('Y-m-d'),
            ];
        });
    }

    /**
     * Bootstrap data for Categories
     */
    public function getCategoriesBootstrapData()
    {
        return Category::with('brands')->get()->map(function ($c) {
            return [
                'id' => (string) $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'description' => $c->description,
                'brands' => $c->brands->map(fn($b) => ['id' => (string) $b->id, 'code' => $b->code, 'name' => $b->name]),
            ];
        });
    }

    /**
     * Bootstrap data for Brands
     */
    public function getBrandsBootstrapData()
    {
        return Brand::with('categories')->get()->map(function ($b) {
            return [
                'id' => (string) $b->id,
                'code' => $b->code,
                'name' => $b->name,
                'country' => $b->country,
                'categories' => $b->categories->map(fn($c) => ['id' => (string) $c->id, 'code' => $c->code, 'name' => $c->name]),
            ];
        });
    }

    /**
     * Bootstrap data for Branches
     */
    public function getBranchesBootstrapData()
    {
        return Branch::orderBy('id')->get()->map(function ($b) {
            return [
                'id' => (string) $b->id,
                'code' => $b->code,
                'name' => $b->name,
                'type' => $b->type,
                'manager' => $b->manager,
                'phone' => $b->phone,
                'address' => $b->address,
                'status' => $b->is_active ? 'Hoạt động' : 'Tạm ngưng',
            ];
        });
    }

    /**
     * Bootstrap data for Warehouses
     */
    public function getWarehousesBootstrapData()
    {
        return Warehouse::with('branch')->get()->map(function ($w) {
            return [
                'id' => (string) $w->id,
                'code' => $w->code,
                'name' => $w->name,
                'type' => $w->type,
                'branchId' => $w->branch_id ? (string) $w->branch_id : '',
                'branch' => $w->branch?->name ?? 'Công ty chính MQ',
                'address' => $w->address ?? '',
                'manager' => $w->manager_name ?? '',
                'managerName' => $w->manager_name ?? '',
                'phone' => $w->branch?->phone ?? '0988 123 456',
                'status' => $w->is_active ? 'Hoạt động' : 'Tạm ngưng',
            ];
        });
    }

    /**
     * Bootstrap data for Inventory Transactions
     */
    public function getTransactionsBootstrapData()
    {
        return InventoryTransaction::with(['warehouse', 'targetWarehouse', 'createdBy', 'items.product'])
            ->latest('created_at')
            ->get()
            ->map(function ($t) {
                $firstItem = $t->items->first();
                $product = $firstItem?->product;
                $itemsCount = $t->items->count();

                $lineItems = $t->items->map(function ($it) {
                    $itemPrice = (float) ($it->import_price > 0 ? $it->import_price : $it->cost_price_at_transaction);
                    return [
                        'id' => (string) $it->id,
                        'productId' => (string) $it->product_id,
                        'sku' => $it->product?->sku ?? 'SP-00',
                        'name' => $it->product?->name ?? 'Vật tư',
                        'unit' => $it->product?->unit ?? 'cái',
                        'qty' => (float) $it->quantity,
                        'unitPrice' => $itemPrice,
                        'total' => (float) ($it->quantity * $itemPrice),
                        'actualAuditQty' => $it->actual_audit_quantity !== null ? (float) $it->actual_audit_quantity : null,
                        'notes' => $it->notes ?? '',
                    ];
                });

                $displayName = $product?->name ?? 'Vật tư tổng hợp';
                if ($itemsCount > 1) {
                    $displayName .= ' (+' . ($itemsCount - 1) . ' SP khác)';
                }

                return [
                    'id' => $t->code,
                    'dbId' => (string) $t->id,
                    'code' => $t->code,
                    'type' => $t->type,
                    'date' => $t->created_at ? $t->created_at->format('Y-m-d') : date('Y-m-d'),
                    'warehouse' => $t->warehouse?->name ?? 'Kho Tổng Mê Linh (MQ)',
                    'warehouseId' => (string) ($t->warehouse_id ?? ''),
                    'targetWarehouse' => $t->targetWarehouse?->name ?? '',
                    'targetWarehouseId' => (string) ($t->target_warehouse_id ?? ''),
                    'sourceWh' => $t->warehouse?->name ?? 'Kho Tổng Mê Linh (MQ)',
                    'targetWh' => $t->targetWarehouse?->name ?? '',
                    'supplier' => $t->supplier_name ?? '',
                    'supplierAddress' => $t->warehouse?->address ?? 'Hà Nội',
                    'customer' => $t->supplier_name ?? '',
                    'totalAmount' => (float) $t->total_amount,
                    'paymentMethod' => 'Chuyển khoản',
                    'creator' => $t->createdBy?->name ?? 'Admin KBTech',
                    'bookDate' => $t->created_at ? $t->created_at->format('H:i d/m/Y') : date('H:i d/m/Y'),
                    'booker' => $t->createdBy?->name ?? 'Admin KBTech',
                    'status' => $t->status,
                    'sku' => $product?->sku ?? '',
                    'name' => $displayName,
                    'qty' => (float) $t->items->sum('quantity'),
                    'unit' => $product?->unit ?? 'cái',
                    'unitPrice' => (float) ($firstItem?->import_price ?? 0),
                    'notes' => $t->notes ?? '',
                    'vehicle' => str_contains($t->supplier_name ?? '', '29C') ? $t->supplier_name : '29C-888.99',
                    'items' => $lineItems,
                ];
            });
    }

    /**
     * API: Save (Create or Update) Product in Database
     */
    public function saveProduct(Request $request)
    {
        $id = $request->input('id');
        $categoryId = $request->input('categoryId');
        $categoryName = $request->input('category');
        $brandId = $request->input('brandId');
        $brandName = $request->input('brand');

        if (!$categoryId && $categoryName) {
            $cat = Category::where('name', $categoryName)->first();
            $categoryId = $cat?->id;
        }
        if (!$categoryId) {
            $categoryId = Category::first()?->id ?? 1;
        }

        if (!$brandId && $brandName) {
            $br = Brand::where('name', $brandName)->first();
            $brandId = $br?->id;
        }
        if (!$brandId) {
            $brandId = Brand::first()?->id ?? 1;
        }

        $priceTiers = $request->input('priceTiers');
        if (empty($priceTiers)) {
            $retailPrice = (float) ($request->input('retailPrice') ?: 0);
            $dealerPrice = (float) ($request->input('dealerPrice') ?: round($retailPrice * 0.8));
            $tier2Price = (float) ($request->input('tier2Price') ?: round($retailPrice * 0.85));
            $projectPrice = (float) ($request->input('projectPrice') ?: round($retailPrice * 0.78));
            $priceTiers = [
                ['name' => 'Đại lý Cấp 1', 'price' => $dealerPrice],
                ['name' => 'Đại lý Cấp 2', 'price' => $tier2Price],
                ['name' => 'Khách Dự Án', 'price' => $projectPrice],
                ['name' => 'Bán Lẻ', 'price' => $retailPrice],
            ];
        }

        $data = [
            'category_id'       => $categoryId,
            'brand_id'          => $brandId,
            'sku'               => $request->input('sku') ?: ('SP-' . strtoupper(uniqid())),
            'name'              => $request->input('name'),
            'tax_name'          => $request->input('taxName') ?: $request->input('name'),
            'subcategory'       => $request->input('subcategory') ?: 'Tiêu chuẩn',
            'specification'     => $request->input('specification') ?: $request->input('spec') ?: '',
            'unit'              => $request->input('unit') ?: 'cái',
            'conversion_unit'   => $request->input('conversionUnit'),
            'conversion_rate'   => (float) ($request->input('conversionRate') ?: 1),
            'conversion_price'  => (float) ($request->input('conversionPrice') ?: 0),
            'cost_price'        => (float) ($request->input('costPrice') ?: 0),
            'dealer_price'      => (float) ($request->input('dealerPrice') ?: 0),
            'retail_price'      => (float) ($request->input('retailPrice') ?: 0),
            'vat_rate'          => (float) ($request->input('vatRate') ?: 0),
            'price_tiers'       => $priceTiers,
            'stock_quantity'    => (float) ($request->input('stockQuantity') ?: 0),
            'min_stock_alert'   => (float) ($request->input('minStockAlert') ?: 10),
            'location'          => $request->input('location') ?: 'Kho Tổng Mê Linh',
        ];

        if ($id && is_numeric($id)) {
            $product = Product::find($id);
            if ($product) {
                $product->update($data);
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật sản phẩm thành công',
                    'product' => $product
                ]);
            }
        }

        $product = Product::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Thêm mới sản phẩm thành công',
            'product' => $product
        ]);
    }

    /**
     * API: Delete Product
     */
    public function deleteProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm']);
        }
        return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm'], 404);
    }

    /**
     * API: Save Category
     */
    public function saveCategory(Request $request)
    {
        $id = $request->input('id');
        $data = [
            'name'        => $request->input('name'),
            'slug'        => $request->input('slug') ?: \Str::slug($request->input('name')),
            'description' => $request->input('description'),
            'color'       => $request->input('color') ?: 'sky',
        ];

        if ($id) {
            $category = Category::find($id);
            if ($category) {
                $category->update($data);
                return response()->json(['success' => true, 'category' => $category]);
            }
        }

        $category = Category::create($data);
        return response()->json(['success' => true, 'category' => $category]);
    }

    /**
     * API: Delete Category
     */
    public function deleteCategory($id)
    {
        $cat = Category::find($id);
        if ($cat) {
            $cat->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa danh mục']);
        }
        return response()->json(['success' => false, 'message' => 'Không tìm thấy danh mục'], 404);
    }

    /**
     * API: Save Brand
     */
    public function saveBrand(Request $request)
    {
        $id = $request->input('id');
        $data = [
            'name'         => $request->input('name'),
            'slug'         => $request->input('slug') ?: \Str::slug($request->input('name')),
            'country'      => $request->input('country') ?: 'Việt Nam',
            'market_share' => $request->input('marketShare') ?: 'Phổ biến',
            'description'  => $request->input('description'),
        ];

        if ($id) {
            $brand = Brand::find($id);
            if ($brand) {
                $brand->update($data);
                return response()->json(['success' => true, 'brand' => $brand]);
            }
        }

        $brand = Brand::create($data);
        return response()->json(['success' => true, 'brand' => $brand]);
    }

    /**
     * API: Delete Brand
     */
    public function deleteBrand($id)
    {
        $brand = Brand::find($id);
        if ($brand) {
            $brand->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa thương hiệu']);
        }
        return response()->json(['success' => false, 'message' => 'Không tìm thấy thương hiệu'], 404);
    }

    /**
     * API: Save Inventory Transaction (Nhập / Xuất kho)
     */
    public function saveInventoryTransaction(Request $request)
    {
        $type = $request->input('type'); // Nhập kho, Xuất kho, Chuyển kho, Kiểm kê
        $warehouseId = $request->input('warehouseId') ?: 1;
        $items = $request->input('items', []);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Danh sách sản phẩm không được để trống'], 422);
        }

        DB::beginTransaction();
        try {
            $tx = InventoryTransaction::create([
                'transaction_code' => $request->input('code') ?: ('PN-' . date('Ymd-His')),
                'type'             => $type ?: 'Nhập kho',
                'warehouse_id'     => $warehouseId,
                'note'             => $request->input('note') ?: 'Giao dịch kho',
                'status'           => 'Đã hoàn thành',
                'created_by_id'    => auth()->id() ?: 1,
            ]);

            foreach ($items as $it) {
                $productId = $it['productId'] ?? $it['id'] ?? null;
                $quantity = (float) ($it['quantity'] ?? 0);
                $unitPrice = (float) ($it['unitPrice'] ?? $it['price'] ?? 0);

                if ($productId) {
                    InventoryTransactionItem::create([
                        'inventory_transaction_id' => $tx->id,
                        'product_id'               => $productId,
                        'quantity'                 => $quantity,
                        'unit_price'               => $unitPrice,
                        'total_amount'             => $quantity * $unitPrice,
                    ]);

                    // Update stock
                    $prod = Product::find($productId);
                    if ($prod) {
                        if ($type === 'Xuất kho') {
                            $prod->decrement('stock_quantity', $quantity);
                        } else {
                            $prod->increment('stock_quantity', $quantity);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lưu phiếu kho thành công',
                'transaction' => $tx
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi lưu phiếu kho: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Get all branches
     */
    public function getBranches()
    {
        return response()->json(['branches' => $this->getBranchesBootstrapData()]);
    }

    /**
     * API: Create or Update Branch
     */
    public function saveBranch(Request $request)
    {
        $id = $request->input('id');
        $data = [
            'code'      => $request->input('code'),
            'name'      => $request->input('name'),
            'type'      => $request->input('type', 'Chi nhánh bán hàng'),
            'manager'   => $request->input('manager'),
            'phone'     => $request->input('phone'),
            'address'   => $request->input('address'),
            'is_active' => $request->input('status', 'Hoạt động') === 'Hoạt động',
        ];

        if ($id && !str_starts_with((string)$id, 'br-')) {
            $branch = Branch::find($id);
            if ($branch) {
                $branch->update($data);
            } else {
                $branch = Branch::create($data);
            }
        } else {
            $branch = Branch::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lưu thông tin chi nhánh thành công',
            'branch' => [
                'id'      => (string) $branch->id,
                'code'    => $branch->code,
                'name'    => $branch->name,
                'type'    => $branch->type,
                'manager' => $branch->manager,
                'phone'   => $branch->phone,
                'address' => $branch->address,
                'status'  => $branch->is_active ? 'Hoạt động' : 'Tạm ngưng',
            ]
        ]);
    }

    /**
     * API: Delete Branch
     */
    public function deleteBranch($id)
    {
        $branch = Branch::find($id);
        if ($branch) {
            $branch->delete();
        }
        return response()->json(['success' => true, 'message' => 'Xóa chi nhánh thành công']);
    }

    /**
     * API: Get all warehouses
     */
    public function getWarehouses()
    {
        return response()->json(['warehouses' => $this->getWarehousesBootstrapData()]);
    }

    /**
     * API: Create or Update Warehouse
     */
    public function saveWarehouse(Request $request)
    {
        $id = $request->input('id');
        $branchId = $request->input('branchId') ?: $request->input('branch_id');
        if ($branchId && str_starts_with((string)$branchId, 'br-')) {
            // Find branch by code or id
            $foundBranch = Branch::where('code', str_replace('br-', '', $branchId))->first();
            $branchId = $foundBranch ? $foundBranch->id : null;
        }

        $data = [
            'code'         => $request->input('code'),
            'name'         => $request->input('name'),
            'type'         => $request->input('type', 'Kho chi nhánh'),
            'branch_id'    => $branchId ?: null,
            'address'      => $request->input('address'),
            'manager_name' => $request->input('manager') ?: $request->input('manager_name'),
            'is_active'    => $request->input('status', 'Hoạt động') === 'Hoạt động',
        ];

        if ($id && !str_starts_with((string)$id, 'wh-')) {
            $wh = Warehouse::find($id);
            if ($wh) {
                $wh->update($data);
            } else {
                $wh = Warehouse::create($data);
            }
        } else {
            $wh = Warehouse::create($data);
        }

        $wh->load('branch');

        return response()->json([
            'success' => true,
            'message' => 'Lưu thông tin kho hàng thành công',
            'warehouse' => [
                'id'          => (string) $wh->id,
                'code'        => $wh->code,
                'name'        => $wh->name,
                'type'        => $wh->type,
                'branchId'    => $wh->branch_id ? (string) $wh->branch_id : '',
                'branch'      => $wh->branch?->name ?? 'Công ty chính MQ',
                'address'     => $wh->address ?? '',
                'manager'     => $wh->manager_name ?? '',
                'managerName' => $wh->manager_name ?? '',
                'phone'       => $wh->branch?->phone ?? '0988 123 456',
                'status'      => $wh->is_active ? 'Hoạt động' : 'Tạm ngưng',
            ]
        ]);
    }

    /**
     * API: Delete Warehouse
     */
    public function deleteWarehouse($id)
    {
        $wh = Warehouse::find($id);
        if ($wh) {
            $wh->delete();
        }
        return response()->json(['success' => true, 'message' => 'Xóa kho hàng thành công']);
    }
}
