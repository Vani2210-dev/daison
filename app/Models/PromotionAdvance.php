<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'order_id',
        'order_code',
        'staff_id',
        'staff_name',
        'customer_name',
        'brand_id',
        'brand_name',
        'supplier_id',
        'supplier_name',
        'warehouse_id',
        'warehouse_name',
        'product_id',
        'product_sku',
        'product_name',
        'unit',
        'advance_quantity',
        'returned_quantity',
        'remaining_quantity',
        'unit_price',
        'total_value',
        'advance_type',
        'status',
        'advance_date',
        'settled_at',
        'notes',
    ];

    protected $casts = [
        'advance_quantity'   => 'decimal:2',
        'returned_quantity'  => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
        'unit_price'         => 'decimal:2',
        'total_value'        => 'decimal:2',
        'advance_date'       => 'datetime',
        'settled_at'         => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function settlementLogs()
    {
        return $this->hasMany(PromotionSettlementLog::class, 'promotion_advance_id')->orderBy('id', 'desc');
    }

    /**
     * Tự động sinh mã phiếu: UKM-YYYYMMDD-XXXX
     */
    public static function generateCode(): string
    {
        $today = date('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        $code = 'UKM-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        while (self::where('code', $code)->exists()) {
            $count++;
            $code = 'UKM-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }
        return $code;
    }
}
