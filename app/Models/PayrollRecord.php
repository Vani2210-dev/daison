<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'base_salary',
        'work_days',
        'kpi_bonus',
        'sales_commission',
        'allowances',
        'deductions',
        'net_salary',
        'status',
        'approved_by_id',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'work_days' => 'decimal:1',
        'kpi_bonus' => 'decimal:2',
        'sales_commission' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
