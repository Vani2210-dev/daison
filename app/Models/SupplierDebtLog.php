<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierDebtLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'supplier_order_id',
        'type', // 'increase' (NCC nợ thêm do thiếu hàng), 'deduct' (Trừ nợ vào đơn mới), 'refund' (Hoàn tiền)
        'amount',
        'balance_before',
        'balance_after',
        'reason',
        'created_by_id',
        'created_by_name',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function order()
    {
        return $this->belongsTo(SupplierOrder::class, 'supplier_order_id');
    }
}
