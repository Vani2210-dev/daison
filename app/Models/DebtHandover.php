<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'order_id',
        'order_code',
        'from_user_id',
        'from_user_name',
        'to_user_id',
        'to_user_name',
        'to_staff_type',
        'target_dealer_id',
        'target_dealer_name',
        'amount',
        'handover_type',
        'status',
        'notes',
        'confirmed_by',
        'confirmed_by_name',
        'confirmed_at',
        'rejected_reason',
        'rejected_at',
        'created_by_id',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'confirmed_at' => 'datetime',
        'rejected_at'  => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function targetDealer()
    {
        return $this->belongsTo(Dealer::class, 'target_dealer_id');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
