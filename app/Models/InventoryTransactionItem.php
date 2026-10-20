<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_transaction_id',
        'product_id',
        'batch_code',
        'manufacturing_date',
        'expiry_date',
        'quantity',
        'import_price',
        'cost_price_at_transaction',
        'actual_audit_quantity',
        'notes',
    ];

    protected $casts = [
        'manufacturing_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
        'quantity' => 'decimal:2',
        'import_price' => 'decimal:2',
        'cost_price_at_transaction' => 'decimal:2',
        'actual_audit_quantity' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(InventoryTransaction::class, 'inventory_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->hasOne(ProductBatch::class, 'inventory_transaction_item_id');
    }
}
