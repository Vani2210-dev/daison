<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCashHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vehicle_plate',
        'driver_id',
        'driver_name',
        'copilot_name',
        'accountant_id',
        'accountant_name',
        'handover_amount',
        'payment_method',
        'order_codes',
        'order_count',
        'notes',
        'receipt_code',
        'status',
        'approved_at',
        'approved_by_name',
        'approved_by_user_id',
        'approval_notes',
        'rejected_reason',
        'rejected_at',
        'rejected_by_name',
        'rejected_by_user_id',
        'order_receipt_codes',
    ];

    protected $casts = [
        'handover_amount'     => 'decimal:2',
        'order_codes'         => 'array',
        'order_receipt_codes' => 'array',
        'approved_at'         => 'datetime',
        'rejected_at'         => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function accountant()
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }
}
