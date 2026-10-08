<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'vehicle_type',
        'driver_id',
        'delivery_route_id',
        'copilot_name',
        'capacity_kg',
        'status',
        'dispatch_status',
        'departure_time',
        'current_route_name',
        'km_odometer',
        'next_inspection_date',
        'last_maintenance_date',
    ];

    protected $casts = [
        'capacity_kg' => 'decimal:2',
        'next_inspection_date' => 'date',
        'last_maintenance_date' => 'date',
    ];

    public function deliveryRoute()
    {
        return $this->belongsTo(DeliveryRoute::class, 'delivery_route_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function dispatches()
    {
        return $this->hasMany(DeliveryDispatch::class);
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class);
    }
}
