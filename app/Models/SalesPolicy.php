<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'policy_type',
        'apply_scope',
        'qty_group_mode',
        'conflict_group',
        'stacking_mode',
        'require_original_price',
        'priority',
        'buy_qty',
        'free_qty',
        'free_product_id',
        'gift_tiers',
        'discount_percent',
        'discount_amount',
        'discount_tiers',
        'min_order_value',
        'target_customer_ids',
        'target_customer_tiers',
        'target_product_ids',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_customer_ids' => 'array',
        'target_customer_tiers' => 'array',
        'target_product_ids' => 'array',
        'gift_tiers' => 'array',
        'discount_tiers' => 'array',
        'require_original_price' => 'boolean',
        'priority' => 'integer',
        'buy_qty' => 'decimal:2',
        'free_qty' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function freeProduct()
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
