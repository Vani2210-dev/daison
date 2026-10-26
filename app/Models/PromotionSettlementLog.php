<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionSettlementLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_advance_id',
        'settlement_type',
        'quantity',
        'remaining_after',
        'reference_code',
        'performer_id',
        'performer_name',
        'notes',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'remaining_after' => 'decimal:2',
    ];

    public function promotionAdvance()
    {
        return $this->belongsTo(PromotionAdvance::class, 'promotion_advance_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performer_id');
    }
}
