<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    public ?bool $_justModified = null;
    public ?bool $_forceUnlock = null;

    protected $fillable = [
        'order_id',
        'product_id',
        'sku',
        'name',
        'unit',
        'quantity',
        'gift_quantity',
        'returned_qty',
        'delivered_qty',
        'return_reason',
        'cost_price',
        'unit_price',
        'original_price',
        'applied_tier_policy',
        'amount',
        'picked_qty',
        'item_status',
        'assigned_staff',
        'assigned_staff_list',
        'shortage_reason',
        'is_picked',
        'is_checked',
        'checked_qty',
        'checked_by',
        'checked_at',
        'check_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'gift_quantity' => 'decimal:2',
        'returned_qty' => 'decimal:2',
        'delivered_qty' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'picked_qty' => 'decimal:2',
        'checked_qty' => 'decimal:2',
        'is_picked' => 'boolean',
        'is_checked' => 'boolean',
        'assigned_staff_list' => 'array',
    ];

    protected $appends = [
        'category_id',
        'category_name',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ID loại sản phẩm (Category ID)
     */
    public function getCategoryIdAttribute(): ?int
    {
        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->category_id ? (int)$this->product->category_id : null;
        }
        return $this->product?->category_id ? (int)$this->product->category_id : null;
    }

    /**
     * Tên loại sản phẩm (Category Name)
     */
    public function getCategoryNameAttribute(): string
    {
        if ($this->relationLoaded('product') && $this->product) {
            if ($this->product->relationLoaded('category') && $this->product->category) {
                return $this->product->category->name;
            }
            return $this->product->category?->name ?? 'Chưa phân loại';
        }
        return $this->product?->category?->name ?? 'Chưa phân loại';
    }

    /**
     * Lợi nhuận gộp trên dòng sản phẩm này
     */
    public function getGrossProfitAttribute(): float
    {
        return (float) ($this->amount - ($this->cost_price * $this->quantity));
    }
}
