<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VanSaleDispatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'van_sale_dispatch_id',
        'product_id',
        'sku',
        'name',
        'unit',
        'quantity',
        'unit_price',
        'amount',
        'sold_quantity',
        'returned_quantity',
    ];

    protected $casts = [
        'quantity'          => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'amount'            => 'decimal:2',
        'sold_quantity'     => 'decimal:2',
        'returned_quantity' => 'decimal:2',
    ];

    public function dispatch()
    {
        return $this->belongsTo(VanSaleDispatch::class, 'van_sale_dispatch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
