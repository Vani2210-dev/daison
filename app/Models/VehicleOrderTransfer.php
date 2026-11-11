<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleOrderTransfer extends Model
{
    protected $table = 'vehicle_order_transfers';

    protected $fillable = [
        'transfer_code',
        'order_id',
        'order_code',
        'customer_name',
        'customer_address',
        'order_amount',
        'from_vehicle_plate',
        'from_driver_id',
        'from_driver_name',
        'to_vehicle_plate',
        'to_driver_id',
        'to_driver_name',
        'reason',
        'status',
        'rejection_reason',
        'responded_at',
        'responded_by_id',
        'responded_by_name',
    ];

    protected $casts = [
        'order_amount' => 'float',
        'responded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function fromDriver()
    {
        return $this->belongsTo(User::class, 'from_driver_id');
    }

    public function toDriver()
    {
        return $this->belongsTo(User::class, 'to_driver_id');
    }
}

