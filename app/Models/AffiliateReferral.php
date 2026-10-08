<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'affiliate_code',
        'referrer_name',
        'referred_customer_name',
        'referred_customer_phone',
        'order_count',
        'total_revenue',
        'kpi_points',
        'bonus_vnd',
        'status',
        'date',
    ];

    protected $casts = [
        'order_count'   => 'integer',
        'total_revenue' => 'float',
        'kpi_points'    => 'float',
        'bonus_vnd'     => 'float',
        'date'          => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
