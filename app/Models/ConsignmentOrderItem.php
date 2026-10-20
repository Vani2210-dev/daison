<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'consignment_order_id',
        'product_id',
        'sku',
        'name',
        'unit',
        'quantity',
        'unit_price',
        'sell_price',
        'amount',
        'sold_quantity',
        'returned_quantity',
        'remaining_quantity',
    ];

    protected $casts = [
        'quantity'           => 'decimal:2',
        'unit_price'         => 'decimal:2',
        'sell_price'         => 'decimal:2',
        'amount'             => 'decimal:2',
        'sold_quantity'      => 'decimal:2',
        'returned_quantity'  => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
    ];

    public function consignmentOrder()
    {
        return $this->belongsTo(ConsignmentOrder::class);
    }

    public function order()
    {
        return $this->belongsTo(ConsignmentOrder::class, 'consignment_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function retailOrderItems()
    {
        return $this->hasMany(ConsignmentRetailOrderItem::class);
    }
}
