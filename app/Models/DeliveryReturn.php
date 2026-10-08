<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'slip_code',
        'order_id',
        'order_code',
        'dealer_id',
        'dealer_name',
        'vehicle_plate',
        'driver_id',
        'driver_name',
        'copilot_name',
        'total_returned_amount',
        'status',
        'notes',
        'intake_warehouse_id',
        'intake_by_user_id',
        'intake_at',
    ];

    protected $casts = [
        'total_returned_amount' => 'decimal:2',
        'intake_at'             => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function intakeWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'intake_warehouse_id');
    }

    public function intakeUser()
    {
        return $this->belongsTo(User::class, 'intake_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(DeliveryReturnItem::class, 'delivery_return_id');
    }
}
