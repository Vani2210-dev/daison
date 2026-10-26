<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_order_id',
        'product_id',
        'product_name',
        'sku',
        'unit',
        'ordered_quantity',
        'received_quantity',
        'remaining_quantity',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'amount',
        'notes',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(SupplierOrder::class, 'supplier_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
