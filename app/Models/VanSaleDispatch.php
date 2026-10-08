<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VanSaleDispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'driver_id',
        'driver_name',
        'vehicle_plate',
        'warehouse_id',
        'warehouse_name',
        'dispatch_date',
        'total_amount',
        'status',
        'notes',
        'created_by_id',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'total_amount'  => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(VanSaleDispatchItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
