<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterRepair extends Model
{
    use HasFactory;

    protected $table = 'filter_repairs';

    protected $fillable = [
        'code',
        'customer_name',
        'customer_phone',
        'customer_address',
        'repair_type',
        'machine_model',
        'machine_serial',
        'warranty_type',
        'symptom',
        'diagnosis',
        'solution',
        'technician_id',
        'technician_name',
        'status',
        'labor_fee',
        'parts_fee',
        'discount',
        'total_amount',
        'payment_status',
        'received_at',
        'completed_at',
        'delivered_at',
        'warranty_until',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'labor_fee'      => 'decimal:2',
        'parts_fee'      => 'decimal:2',
        'discount'       => 'decimal:2',
        'total_amount'   => 'decimal:2',
        'received_at'    => 'datetime',
        'completed_at'   => 'datetime',
        'delivered_at'   => 'datetime',
        'warranty_until' => 'date',
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(FilterRepairItem::class, 'repair_id');
    }
}
