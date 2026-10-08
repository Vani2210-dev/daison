<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'ledger_type',
        'from_date',
        'to_date',
        'status',
        'locked_by_id',
        'reason',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by_id');
    }
}
