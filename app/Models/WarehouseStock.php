<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'stock_quantity',
        'reserved_quantity',
        'location',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Tồn kho khả dụng của sản phẩm tại kho cụ thể này (Tồn thực tế - Tồn tạm giữ)
     */
    public function getAvailableStockAttribute(): float
    {
        return max(0, (float) ($this->stock_quantity - $this->reserved_quantity));
    }
}
