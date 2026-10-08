<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'score_percent',
        'grade',
        'metrics_breakdown',
        'notes',
    ];

    protected $casts = [
        'score_percent' => 'decimal:2',
        'metrics_breakdown' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
