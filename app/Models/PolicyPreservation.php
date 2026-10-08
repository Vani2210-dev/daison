<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyPreservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'customer_id',
        'customer_name',
        'product_id',
        'product_name',
        'total_deposit_amount',
        'used_amount',
        'remaining_amount',
        'start_date',
        'end_date',
        'status',
        'created_by_name',
        'notes',
        'history_logs',
    ];

    protected $casts = [
        'total_deposit_amount' => 'float',
        'used_amount'          => 'float',
        'remaining_amount'     => 'float',
        'start_date'           => 'date',
        'end_date'             => 'date',
        'history_logs'         => 'array',
    ];
}
