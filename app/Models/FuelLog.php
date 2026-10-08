<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_name',
        'date',
        'fuel_type',
        'liters',
        'price_per_liter',
        'total_cost',
        'odometer_km',
        'gas_station',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'liters' => 'decimal:2',
        'price_per_liter' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
