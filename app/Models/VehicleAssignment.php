<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vehicle_id',
        'vehicle_plate',
        'driver_name',
        'driver_phone',
        'copilot_name',
        'copilot_phone',
        'manager_name',
        'manager_phone',
        'assigned_date',
        'effective_from',
        'effective_to',
        'assigned_by',
        'shift',
        'route_id',
        'route_zone',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_date'  => 'date',
        'effective_from' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
