<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_lat',
        'check_in_lng',
        'check_in_address',
        'selfie_url',
        'status',
        'work_hours',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_lat' => 'float',
        'check_in_lng' => 'float',
        'work_hours' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
