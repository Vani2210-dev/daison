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
        'price',
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
        'price' => 'decimal:2',
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

    public function batches()
    {
        return $this->hasMany(ProductBatch::class)->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC');
    }

    public function activeBatches()
    {
        return $this->hasMany(ProductBatch::class)->where('remaining_quantity', '>', 0)->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC');
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
     * Kiểm tra xem đơn vị quy đổi (conversion_unit) có phải là đơn vị lớn hơn đơn vị cơ bản (unit) hay không.
     * Ví dụ:
     * - conversion_unit = 'Kiện', 'Thùng', 'Bao', 'Lô', 'Pallet', 'Carton' trong khi unit là 'Chai', 'Lọ', 'Tuýp', 'Gói'...
     * => Đơn vị quy đổi LỚN HƠN đơn vị cơ bản (1 Kiện = 200 Chai).
     * Ngược lại (kiểu ống nhựa):
     * - unit = 'Cây', conversion_unit = 'Mét' => Đơn vị cơ bản LỚN HƠN đơn vị quy đổi (1 Cây = 4 Mét).
     */
    public function isConversionUnitLarger(): bool
    {
        if (!$this->hasConversionUnit()) {
            return false;
        }

        $convUnit = mb_strtolower(trim($this->conversion_unit));
        $baseUnit = mb_strtolower(trim($this->unit));

        // Các đơn vị đóng gói/thùng/kiện/bao/lô... luôn lớn hơn đơn vị con
        $bulkUnits = ['kiện', 'thùng', 'bao', 'lô', 'pallet', 'carton', 'két', 'thưng', 'lốc', 'dây'];
        if (in_array($convUnit, $bulkUnits)) {
            return true;
        }

        // Hộp lớn chứa chai/lọ/tuýp/gói/vỉ/cái/ống/viên/miếng
        if ($convUnit === 'hộp' && in_array($baseUnit, ['chai', 'lọ', 'tuýp', 'gói', 'vỉ', 'cái', 'viên', 'miếng', 'ống'])) {
            return true;
        }

        // Nếu đơn vị cơ bản là mét/cm/m2/g/gam mà đơn vị quy đổi là cuộn/cây/tấm
        if (in_array($baseUnit, ['mét', 'm', 'cm', 'gam', 'g']) && in_array($convUnit, ['cuộn', 'cây', 'tấm', 'hộp'])) {
            return true;
        }

        return false;
    }

    /**
     * Quy đổi số lượng từ đơn vị gốc (baseQuantity) sang đơn vị quy đổi (conversion_unit).
     * Ví dụ:
     * - Nếu conversion_unit lớn hơn (1 Kiện = 200 Chai): 200 Chai -> 1 Kiện (baseQuantity / rate).
     * - Nếu conversion_unit nhỏ hơn (1 Cây = 4 Mét): 10 Cây -> 40 Mét (baseQuantity * rate).
     */
    public function toConversionQuantity(float $baseQuantity): float
    {
        if (!$this->hasConversionUnit()) {
            return $baseQuantity;
        }

        $rate = (float) $this->conversion_rate;
        if ($rate <= 0) return $baseQuantity;

        if ($this->isConversionUnitLarger()) {
            return $baseQuantity / $rate;
        }

        return $baseQuantity * $rate;
    }

    /**
     * Quy đổi số lượng từ đơn vị quy đổi (conversionQuantity) về đơn vị gốc (unit).
     * Ví dụ:
     * - Nếu conversion_unit lớn hơn (1 Kiện = 200 Chai): 1 Kiện -> 200 Chai (conversionQuantity * rate).
     * - Nếu conversion_unit nhỏ hơn (1 Cây = 4 Mét): 40 Mét -> 10 Cây (conversionQuantity / rate).
     */
    public function toBaseQuantity(float $conversionQuantity): float
    {
        if (!$this->hasConversionUnit() || (float) $this->conversion_rate <= 0) {
            return $conversionQuantity;
        }

        $rate = (float) $this->conversion_rate;
        if ($rate <= 0) return $conversionQuantity;

        if ($this->isConversionUnitLarger()) {
            return $conversionQuantity * $rate;
        }

        return $conversionQuantity / $rate;
    }
}
