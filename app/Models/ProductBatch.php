<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'inventory_transaction_item_id',
        'batch_code',
        'manufacturing_date',
        'expiry_date',
        'import_quantity',
        'remaining_quantity',
        'cost_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'manufacturing_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
        'import_quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    protected $appends = [
        'days_until_expiry',
        'expiry_status_code',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function transactionItem()
    {
        return $this->belongsTo(InventoryTransactionItem::class, 'inventory_transaction_item_id');
    }

    /**
     * Số ngày còn lại đến khi hết hạn
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        $now = Carbon::today();
        $expiry = Carbon::parse($this->expiry_date)->startOfDay();

        return (int) $now->diffInDays($expiry, false);
    }

    /**
     * Mã trạng thái Date: 'expired' (quá hạn), 'near_expired' (<= 60 ngày), 'good' (> 60 ngày), 'no_expiry' (không có date)
     */
    public function getExpiryStatusCodeAttribute(): string
    {
        if (!$this->expiry_date) {
            return 'no_expiry';
        }

        $days = $this->days_until_expiry;
        if ($days < 0) {
            return 'expired';
        }
        if ($days <= 60) {
            return 'near_expired';
        }
        return 'good';
    }

    /**
     * Scope chỉ lấy lô còn tồn hàng
     */
    public function scopeActive($query)
    {
        return $query->where('remaining_quantity', '>', 0);
    }

    /**
     * Scope sắp hết hạn
     */
    public function scopeNearExpired($query, $days = 60)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', Carbon::today()->addDays($days))
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->where('remaining_quantity', '>', 0);
    }

    /**
     * Scope đã quá hạn sử dụng
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', Carbon::today())
            ->where('remaining_quantity', '>', 0);
    }

    /**
     * Scope ưu tiên FEFO (First Expired First Out)
     */
    public function scopeFefo($query)
    {
        return $query->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC, id ASC');
    }
}
