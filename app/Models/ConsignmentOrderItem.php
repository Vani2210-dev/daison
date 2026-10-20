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
        'quantity'           => 'decimal:2',
        'unit_price'         => 'decimal:2',
        'sell_price'         => 'decimal:2',
        'amount'             => 'decimal:2',
        'sold_quantity'      => 'decimal:2',
        'returned_quantity'  => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
        'picked_qty'         => 'decimal:2',
        'checked_qty'        => 'decimal:2',
        'is_picked'          => 'boolean',
        'is_checked'         => 'boolean',
        'assigned_staff_list'=> 'array',
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
