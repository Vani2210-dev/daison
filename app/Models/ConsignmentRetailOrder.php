<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentRetailOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'consignment_order_id',
        'staff_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'total_amount',
        'payment_method',
        'payment_status',
        'status',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'sold_at'      => 'datetime',
    ];

    // ── Relationships ──

    public function consignmentOrder()
    {
        return $this->belongsTo(ConsignmentOrder::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function items()
    {
        return $this->hasMany(ConsignmentRetailOrderItem::class);
    }

    // ── Helpers ──

    /**
     * Tạo mã đơn lẻ tự động: OL-2026-0001
     */
    public static function generateCode(): string
    {
        $year = date('Y');
        $prefix = "OL-{$year}-";
        $lastOrder = static::where('code', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastOrder) {
            $lastNumber = (int) str_replace($prefix, '', $lastOrder->code);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
