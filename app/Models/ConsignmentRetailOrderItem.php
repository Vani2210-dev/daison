<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentRetailOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'consignment_retail_order_id',
        'consignment_order_item_id',
        'product_id',
        'sku',
        'name',
        'unit',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount'     => 'decimal:2',
    ];

    public function retailOrder()
    {
        return $this->belongsTo(ConsignmentRetailOrder::class, 'consignment_retail_order_id');
    }

    public function consignmentOrderItem()
    {
        return $this->belongsTo(ConsignmentOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
