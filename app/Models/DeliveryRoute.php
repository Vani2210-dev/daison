<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'departure_warehouse',
        'stops',
        'distance_km',
        'estimated_time_hours',
        'fuel_liters',
        'toll_fee',
        'assigned_vehicle_plate',
        'assigned_driver_name',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'stops' => 'array',
        'distance_km' => 'decimal:2',
        'estimated_time_hours' => 'decimal:2',
        'fuel_liters' => 'decimal:2',
        'toll_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'delivery_route_id');
    }
}
