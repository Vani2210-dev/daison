<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_return_id',
        'order_item_id',
        'product_id',
        'sku',
        'name',
        'unit',
        'returned_qty',
        'unit_price',
        'amount',
        'reason',
    ];

    protected $casts = [
        'returned_qty' => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'amount'       => 'decimal:2',
    ];

    public function deliveryReturn()
    {
        return $this->belongsTo(DeliveryReturn::class, 'delivery_return_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
