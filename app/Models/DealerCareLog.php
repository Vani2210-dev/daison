<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealerCareLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'dealer_id',
        'sales_rep_id',
        'visited_date',
        'lat',
        'lng',
        'distance_meters',
        'is_within_radius',
        'purpose',
        'marketFeedback',
        'competitor_info',
        'next_order_estimate',
        'satisfaction_rating',
        'photo_url',
        'notes',
        'kpi_credited',
    ];

    protected $casts = [
        'visited_date' => 'datetime',
        'lat' => 'float',
        'lng' => 'float',
        'is_within_radius' => 'boolean',
        'kpi_credited' => 'boolean',
    ];

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }
}
