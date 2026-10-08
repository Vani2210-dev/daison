<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryDispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vehicle_id',
        'driver_id',
        'status',
        'departure_time',
        'return_time',
        'total_weight_kg',
        'total_distance_km',
        'route_summary',
        'notes',
        'details',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'return_time' => 'datetime',
        'total_weight_kg' => 'decimal:2',
        'total_distance_km' => 'decimal:2',
        'details' => 'array',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'delivery_dispatch_orders')
            ->withPivot(['delivery_order', 'status'])
            ->withTimestamps();
    }

    public function stops()
    {
        return $this->orders();
    }
}
