<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'supplier_id',
        'supplier_name',
        'warehouse_id',
        'warehouse_name',
        'creator_id',
        'creator_name',
        'approver_id',
        'approver_name',
        'order_date',
        'expected_delivery_date',
        'status',
        'payment_status',
        'total_items_amount',
        'discount_amount',
        'other_fees',
        'total_amount',
        'debt_deducted_amount',
        'payable_amount',
        'paid_amount',
        'payment_method',
        'payment_note',
        'shortage_resolution',
        'shortage_amount',
        'refunded_to_supplier_debt',
        'received_amount',
        'receipt_count',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_items_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'debt_deducted_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'shortage_amount' => 'decimal:2',
        'refunded_to_supplier_debt' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'receipt_count' => 'integer',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function items()
    {
        return $this->hasMany(SupplierOrderItem::class);
    }

    public function debtLogs()
    {
        return $this->hasMany(SupplierDebtLog::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'reference_order_code', 'code');
    }
}
