<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'tax_code',
        'contact_name',
        'address',
        'bank_name',
        'bank_account',
        'supplier_debt_to_company',
        'company_debt_to_supplier',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'supplier_debt_to_company' => 'decimal:2',
        'company_debt_to_supplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(SupplierOrder::class);
    }

    public function debtLogs()
    {
        return $this->hasMany(SupplierDebtLog::class);
    }
}
