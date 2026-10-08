<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickingSyncEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'item_id',
        'sku',
        'picked_qty',
        'req_qty',
        'item_status',
        'shortage_reason',
        'performer_name',
        'event_type',
        'assigned_staff',
        'assigned_staff_list',
        'order_status',
        'shortage_reported',
        'extra_data',
        'created_at',
    ];

    protected $casts = [
        'picked_qty'          => 'float',
        'req_qty'             => 'float',
        'shortage_reported'   => 'boolean',
        'assigned_staff_list' => 'array',
        'extra_data'          => 'array',
        'created_at'          => 'datetime',
    ];
}
