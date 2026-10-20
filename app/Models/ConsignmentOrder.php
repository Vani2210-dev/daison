<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'staff_id',
        'staff_name',
        'warehouse_id',
        'warehouse_name',
        'total_amount',
        'sold_amount',
        'returned_amount',
        'remaining_amount',
        'status',
        'approved_by',
        'approved_by_name',
        'approved_at',
        'rejected_reason',
        'rejected_at',
        'settled_at',
        'settled_by',
        'settled_by_name',
        'notes',
        'created_by_id',
    ];

    protected $casts = [
        'total_amount'     => 'decimal:2',
        'sold_amount'      => 'decimal:2',
        'returned_amount'  => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'approved_at'      => 'datetime',
        'rejected_at'      => 'datetime',
        'settled_at'       => 'datetime',
    ];

    // ── Relationships ──

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(ConsignmentOrderItem::class);
    }

    public function retailOrders()
    {
        return $this->hasMany(ConsignmentRetailOrder::class);
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function settledByUser()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    // ── Helpers ──

    /**
     * Tạo mã đơn ôm tự động: OM-2026-0001
     */
    public static function generateCode(): string
    {
        $year = date('Y');
        $prefix = "OM-{$year}-";
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

    /**
     * Lấy nhãn trạng thái tiếng Việt
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'       => 'Bản nháp',
            'pending'     => 'Chờ duyệt',
            'approved'    => 'Đã duyệt',
            'in_progress' => 'Đang bán',
            'settled'     => 'Đã quyết toán',
            'cancelled'   => 'Đã hủy',
            default       => $this->status,
        };
    }

    /**
     * Lấy màu badge trạng thái
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'       => 'slate',
            'pending'     => 'amber',
            'approved'    => 'sky',
            'in_progress' => 'indigo',
            'settled'     => 'emerald',
            'cancelled'   => 'red',
            default       => 'slate',
        };
    }

    /**
     * Tự động đồng bộ tồn kho hàng ôm của nhân viên (WarehouseStock) theo các đơn ôm đang giữ
     */
    public static function recalculateEmployeeHoldingStock(int $staffId): ?Warehouse
    {
        $staff = User::find($staffId);
        if (!$staff) return null;

        // Tìm hoặc tạo kho hàng ôm của nhân viên
        $wh = Warehouse::where('code', 'KHO-OM-' . $staff->id)->first();
        if (!$wh) {
            $wh = Warehouse::where('manager_name', $staff->name)->where('type', 'Kho hàng ôm')->first();
        }
        if (!$wh) {
            $wh = Warehouse::create([
                'code'         => 'KHO-OM-' . $staff->id,
                'name'         => 'Kho Hàng Ôm - ' . $staff->name,
                'type'         => 'Kho hàng ôm',
                'manager_name' => $staff->name,
                'address'      => 'Kho lưu động nhân viên ' . $staff->name,
                'is_active'    => true,
            ]);
        } else {
            if ($wh->type !== 'Kho hàng ôm') {
                $wh->update(['type' => 'Kho hàng ôm']);
            }
        }

        // Lấy tất cả các mặt hàng còn lại từ các đơn ôm đang hoạt động
        $activeItemSums = ConsignmentOrderItem::whereHas('consignmentOrder', function ($q) use ($staffId) {
            $q->where('staff_id', $staffId)
              ->whereIn('status', ['approved', 'in_progress']);
        })
        ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(remaining_quantity) as total_remaining'))
        ->groupBy('product_id')
        ->pluck('total_remaining', 'product_id');

        // Cập nhật hoặc thêm mới vào WarehouseStock
        $existingStocks = WarehouseStock::where('warehouse_id', $wh->id)->get()->keyBy('product_id');

        foreach ($activeItemSums as $productId => $remainingQty) {
            $qty = max(0, (float)$remainingQty);
            if ($existingStocks->has($productId)) {
                $existingStocks[$productId]->update(['stock_quantity' => $qty]);
            } else {
                WarehouseStock::create([
                    'warehouse_id'      => $wh->id,
                    'product_id'        => $productId,
                    'stock_quantity'    => $qty,
                    'reserved_quantity' => 0,
                    'location'          => 'Hàng ôm NV',
                ]);
            }
        }

        // Đặt về 0 các mặt hàng mà nhân viên không còn ôm trong đơn nào
        foreach ($existingStocks as $productId => $stockRow) {
            if (!$activeItemSums->has($productId) && (float)$stockRow->stock_quantity > 0) {
                $stockRow->update(['stock_quantity' => 0]);
            }
        }

        return $wh;
    }

    /**
     * Tự động đồng bộ tồn kho hàng ôm cho toàn bộ nhân viên có đơn ôm đang hoạt động
     */
    public static function syncAllHoldingStocks(): void
    {
        $staffIds = static::whereIn('status', ['approved', 'in_progress'])
            ->distinct()
            ->pluck('staff_id');

        foreach ($staffIds as $staffId) {
            static::recalculateEmployeeHoldingStock((int)$staffId);
        }
    }
}
