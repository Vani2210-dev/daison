<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'owner_name',
        'phone',
        'tax_code',
        'referrer',
        'address',
        'district',
        'city',
        'old_ward',
        'old_district',
        'old_city',
        'new_ward',
        'new_district',
        'new_city',
        'tier',
        'lat',
        'lng',
        'credit_limit',
        'initial_debt',
        'current_debt',
        'payment_term_days',
        'overdue_days',
        'sales_rep_id',
        'status',
        'truck_accessible',
        'storefront_width_meters',
        'expansion_notes',
        'avatar_url',
        'last_visited_at',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'credit_limit' => 'decimal:2',
        'initial_debt' => 'decimal:2',
        'current_debt' => 'decimal:2',
        'truck_accessible' => 'boolean',
        'last_visited_at' => 'datetime',
    ];

    public function getTierAttribute($value)
    {
        if (!$value) {
            return $value;
        }
        $map = [
            'GT - Khách hàng L? l?n' => 'GT - Khách hàng Lẻ lớn',
            'GT - Khách hàng L? nh?' => 'GT - Khách hàng Lẻ nhỏ',
            'GT - Khách hàng S?' => 'GT - Khách hàng Sỉ',
        ];
        if (isset($map[$value])) {
            return $map[$value];
        }
        return str_replace(
            ['Khách hàng L? l?n', 'Khách hàng L? nh?', 'Khách hàng S?'],
            ['Khách hàng Lẻ lớn', 'Khách hàng Lẻ nhỏ', 'Khách hàng Sỉ'],
            $value
        );
    }

    public function setTierAttribute($value)
    {
        if ($value) {
            $map = [
                'GT - Khách hàng L? l?n' => 'GT - Khách hàng Lẻ lớn',
                'GT - Khách hàng L? nh?' => 'GT - Khách hàng Lẻ nhỏ',
                'GT - Khách hàng S?' => 'GT - Khách hàng Sỉ',
            ];
            $value = $map[$value] ?? str_replace(
                ['Khách hàng L? l?n', 'Khách hàng L? nh?', 'Khách hàng S?'],
                ['Khách hàng Lẻ lớn', 'Khách hàng Lẻ nhỏ', 'Khách hàng Sỉ'],
                $value
            );
        }
        $this->attributes['tier'] = $value;
    }

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function careLogs()
    {
        return $this->hasMany(DealerCareLog::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope lọc đại lý theo phân quyền:
     * - Quản lý / Kế toán / Có quyền dealers.view_all: Xem toàn bộ khách hàng đại lý
     * - Nhân viên thị trường: Chỉ xem các đại lý do mình phụ trách
     */
    public function scopeAccessibleBy($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return $query;
        }

        if ($user->hasRole('Manager') || $user->hasRole('Admin') || $user->hasPermission('dealers.view_all')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('sales_rep_id', $user->id);
            if (!empty($user->dealer_id)) {
                $q->orWhere('id', $user->dealer_id);
            }
        });
    }

    /**
     * Tính toán công nợ hiện tại của đại lý (có thể loại trừ một đơn hàng đang xử lý để tránh tính trùng)
     */
    public function getDebtExcludingOrder($excludeOrderId = null): float
    {
        $initial = (float) ($this->initial_debt ?? 0);
        
        $orders = Order::where(function($q) {
            $q->where('dealer_id', $this->id);
            if ($this->code) {
                $q->orWhere('dealer_id', $this->code);
            }
        })
        ->when($excludeOrderId, function($q) use ($excludeOrderId) {
            $q->where('id', '!=', $excludeOrderId)
              ->where('order_code', '!=', $excludeOrderId);
        })
        ->get();

        if ($orders->isEmpty()) {
            $generalTxPaid = (float) Transaction::where('type', 'Thu')
                ->whereNull('order_id')
                ->where('customer_partner_name', $this->name)
                ->sum('amount');
            return max(0, $initial - $generalTxPaid);
        }

        $orderIds = $orders->pluck('id')->filter()->all();
        $orderCodes = $orders->pluck('order_code')->filter()->all();

        // 1. Tối ưu: Batch load toàn bộ phiếu thu theo order_id trong 1 truy vấn duy nhất
        $txByOrderId = Transaction::where('type', 'Thu')
            ->whereIn('order_id', $orderIds)
            ->groupBy('order_id')
            ->selectRaw('order_id, SUM(amount) as total_paid')
            ->pluck('total_paid', 'order_id')
            ->all();

        // 2. Batch load phiếu thu theo mã PT- trong 1 truy vấn duy nhất
        $ptCodes = array_map(fn($c) => 'PT-' . $c, $orderCodes);
        $txByCode = !empty($ptCodes) ? Transaction::where('type', 'Thu')
            ->whereIn('code', $ptCodes)
            ->groupBy('code')
            ->selectRaw('code, SUM(amount) as total_paid')
            ->pluck('total_paid', 'code')
            ->all() : [];

        $unpaidFromOrders = 0;
        foreach ($orders as $order) {
            $status = mb_strtolower(trim($order->status ?? ''));
            // Đơn báo giá, hủy hoặc hoàn kho không tính vào công nợ
            if (str_contains($status, 'báo giá') || str_contains($status, 'hủy') || str_contains($status, 'hoàn kho')) {
                continue;
            }

            $pStatus = mb_strtolower(trim($order->payment_status ?? ''));
            $isDelivered = str_contains($status, 'đã giao') || str_contains($status, 'giao thành công') || str_contains($status, 'hoàn tất');
            $isExplicitDebt = str_contains($pStatus, 'ghi nợ') || str_contains($pStatus, 'ký nợ');

            // Chỉ tính vào công nợ thực tế nếu đơn đã giao hoặc đã được xác nhận ghi nợ
            if (!$isDelivered && !$isExplicitDebt) {
                continue;
            }

            $total = max(0, (float) ($order->total_amount ?? 0) - (float) ($order->total_returned_amount ?? 0));

            $paid = 0;
            if (
                str_contains($pStatus, 'đã thanh toán') || 
                str_contains($pStatus, 'đã thu tiền mặt') || 
                str_contains($pStatus, 'qr') ||
                str_contains($pStatus, 'đã nộp quỹ') ||
                str_contains($pStatus, 'hoàn thành') ||
                (bool) ($order->settled_with_accountant ?? false)
            ) {
                $paid = $total;
            } elseif (str_contains($pStatus, 'đã cọc')) {
                $paid = round($total * 0.3);
            }

            // Phiếu thu gán trực tiếp theo đơn hàng (tra cứu O(1) từ batch map đã tải trước)
            $txPaid1 = (float) ($txByOrderId[$order->id] ?? 0);
            $txPaid2 = $order->order_code ? (float) ($txByCode['PT-' . $order->order_code] ?? 0) : 0;
            $txPaid = max($txPaid1, $txPaid2);

            $paid = min($total, max($paid, $txPaid));

            $remaining = max(0, $total - $paid);
            $unpaidFromOrders += $remaining;
        }

        // Các phiếu thu nợ đại lý không gắn riêng theo từng đơn (dùng so khớp chính xác thay cho LIKE full table scan)
        $generalTxPaid = (float) Transaction::where('type', 'Thu')
            ->whereNull('order_id')
            ->where(function($q) {
                $q->where('customer_partner_name', $this->name);
                if (!empty($this->phone)) {
                    $q->orWhere('customer_partner_name', $this->phone);
                }
            })
            ->sum('amount');

        return max(0, $initial + $unpaidFromOrders - $generalTxPaid);
    }

    /**
     * Tự động tính toán lại công nợ hiện tại theo các đơn hàng thực tế và phiếu thu
     */
    public function recalculateCurrentDebt(): float
    {
        $liveDebt = $this->getDebtExcludingOrder(null);
        $this->current_debt = $liveDebt;
        $this->save();

        // Đồng bộ công nợ cho Nhân viên đại lý (gắn cho đại lý thì công nợ vào đại lý VÀ nhân viên đó luôn)
        User::where('dealer_id', $this->id)
            ->where(function($q) {
                $q->whereIn('staff_type', ['brand', 'brand_staff', 'dealer_sales', 'hang'])
                  ->orWhereNotNull('dealer_id');
            })
            ->update(['current_debt' => $liveDebt]);

        return $liveDebt;
    }
}

