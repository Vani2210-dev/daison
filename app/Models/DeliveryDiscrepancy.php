<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryDiscrepancy extends Model
{
    use HasFactory;

    protected $table = 'delivery_discrepancies';

    protected $fillable = [
        'order_id',
        'order_code',
        'vehicle_plate',
        'driver_id',
        'driver_name',
        'checker_id',
        'checker_name',
        'product_id',
        'sku',
        'product_name',
        'unit',
        'expected_qty',
        'actual_qty',
        'difference_qty',
        'discrepancy_type',
        'driver_note',
        'images',
        'checker_feedback',
        'checker_feedback_at',
        'checker_feedback_by',
        'status',
        'resolution_note',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'expected_qty'        => 'float',
        'actual_qty'          => 'float',
        'difference_qty'      => 'float',
        'images'              => 'array',
        'checker_feedback_at' => 'datetime',
        'resolved_at'         => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function picker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function getPickerNameAttribute()
    {
        return $this->checker_name ?: 'Người bốc hàng';
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
