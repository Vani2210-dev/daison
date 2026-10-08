<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'sku',
        'name',
        'tax_name',
        'subcategory',
        'subcategory_id',
        'specification',
        'unit',
        'conversion_unit',
        'conversion_rate',
        'conversion_price',
        'cost_price',
        'latest_cost_price',
        'price_tiers',
        'vat_rate',
        'stock_quantity',
        'reserved_quantity',
        'min_stock_alert',
        'max_stock_alert',
        'location',
        'qr_code',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'latest_cost_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'conversion_rate' => 'decimal:4',
        'conversion_price' => 'decimal:2',
        'price_tiers' => 'array', // Tự động cast JSON <-> Array
        'stock_quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'min_stock_alert' => 'decimal:2',
        'max_stock_alert' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function subcategoryItem()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_stocks')
            ->withPivot('stock_quantity', 'reserved_quantity', 'location')
            ->withTimestamps();
    }

    public function getStockInWarehouse($warehouseIdentifier): float
    {
        $stocks = $this->relationLoaded('warehouseStocks') ? $this->warehouseStocks : $this->warehouseStocks()->with('warehouse')->get();
        $ws = $stocks->first(function ($item) use ($warehouseIdentifier) {
            if (!$item->warehouse) return false;
            return (string)$item->warehouse_id === (string)$warehouseIdentifier
                || strcasecmp($item->warehouse->name, $warehouseIdentifier) === 0
                || strcasecmp($item->warehouse->code, $warehouseIdentifier) === 0;
        });

        return $ws ? (float)$ws->stock_quantity : 0;
    }

    /**
     * Lấy giá bán theo tên phân khúc cụ thể (vd: "Đại lý Cấp 1", "Bán Lẻ", "Dự Án")
     */
    public function getPriceForTier(string $tierName, float $defaultPrice = 0): float
    {
        if (empty($this->price_tiers) || !is_array($this->price_tiers)) {
            return $defaultPrice;
        }

        foreach ($this->price_tiers as $tier) {
            if (strcasecmp($tier['name'] ?? '', $tierName) === 0) {
                return (float) ($tier['price'] ?? $defaultPrice);
            }
        }

        return $defaultPrice;
    }

    /**
     * Cập nhật hoặc thêm mới một phân khúc giá trong cột price_tiers (JSON)
     */
    public function setPriceForTier(string $tierName, float $price): self
    {
        $tiers = $this->price_tiers ?? [];
        $found = false;

        foreach ($tiers as &$tier) {
            if (strcasecmp($tier['name'] ?? '', $tierName) === 0) {
                $tier['price'] = $price;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $tiers[] = ['name' => $tierName, 'price' => $price];
        }

        $this->price_tiers = $tiers;
        return $this;
    }

    /**
     * Cập nhật Giá Vốn Bình Quân Gia Quyền Liên Hoàn (Moving Average Cost - Phương án 2)
     * Công thức: (Tồn cũ * Giá vốn cũ + Lượng nhập mới * Giá nhập mới) / (Tồn cũ + Lượng nhập mới)
     */
    public function recordGoodsReceipt(float $newQuantity, float $newImportPrice): self
    {
        $currentStock = (float) $this->stock_quantity;
        $currentCost = (float) $this->cost_price;

        $totalStock = $currentStock + $newQuantity;

        if ($totalStock > 0) {
            $newAvgCost = (($currentStock * $currentCost) + ($newQuantity * $newImportPrice)) / $totalStock;
            $this->cost_price = round($newAvgCost, 2);
        } else {
            $this->cost_price = $newImportPrice;
        }

        $this->latest_cost_price = $newImportPrice;
        $this->stock_quantity = $totalStock;
        $this->save();

        return $this;
    }

    /**
     * Tồn kho khả dụng (Tồn thực tế trừ đi số lượng đã giữ cho đơn hàng)
     */
    public function getAvailableStockAttribute(): float
    {
        return max(0, (float) ($this->stock_quantity - $this->reserved_quantity));
    }

    /**
     * Kiểm tra sản phẩm có đang ở mức cảnh báo cần nhập thêm hàng không (Tồn kho <= Mức tối thiểu)
     */
    public function isLowStock(): bool
    {
        return (float) $this->stock_quantity <= (float) $this->min_stock_alert;
    }

    /**
     * Scope lọc danh sách các sản phẩm đang chạm hoặc dưới mức cảnh báo tồn kho
     * Cách dùng: Product::lowStock()->get();
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_alert');
    }

    /**
     * Kiểm tra sản phẩm có cấu hình đơn vị chuyển đổi hay không
     */
    public function hasConversionUnit(): bool
    {
        return !empty($this->conversion_unit) && (float) $this->conversion_rate > 0;
    }

    /**
     * Quy đổi số lượng từ đơn vị gốc (vd: ống/cây) sang đơn vị chuyển đổi (vd: mét)
     * Ví dụ: 10 cây * hệ số 4 = 40 mét
     */
    public function toConversionQuantity(float $baseQuantity): float
    {
        if (!$this->hasConversionUnit()) {
            return $baseQuantity;
        }

        return $baseQuantity * (float) $this->conversion_rate;
    }

    /**
     * Quy đổi số lượng từ đơn vị chuyển đổi (vd: mét) ngược về đơn vị gốc (vd: ống/cây)
     * Ví dụ: 40 mét / hệ số 4 = 10 cây
     */
    public function toBaseQuantity(float $conversionQuantity): float
    {
        if (!$this->hasConversionUnit() || (float) $this->conversion_rate <= 0) {
            return $conversionQuantity;
        }

        return $conversionQuantity / (float) $this->conversion_rate;
    }
}
