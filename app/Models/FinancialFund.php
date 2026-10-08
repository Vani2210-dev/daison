<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'account_number',
        'bank_name',
        'current_balance',
        'is_active',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'fund_id');
    }
}
