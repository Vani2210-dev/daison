<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'fund_id',
        'category',
        'amount',
        'customer_partner_name',
        'order_id',
        'created_by_id',
        'date',
        'notes',
        'receipt_url',
        'has_vat_invoice',
        'vat_invoice_no',
        'tax_code',
        'vat_rate',
        'vat_amount',
        'total_with_vat',
        'details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_with_vat' => 'decimal:2',
        'has_vat_invoice' => 'boolean',
        'date' => 'date',
        'details' => 'array',
    ];

    public function fund()
    {
        return $this->belongsTo(FinancialFund::class, 'fund_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
