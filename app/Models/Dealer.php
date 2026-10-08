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

            $total = (float) ($order->total_amount ?? 0);

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

            // Phiếu thu gán trực tiếp theo đơn hàng
            $txPaid = (float) Transaction::where(function($q) use ($order) {
                $q->where('order_id', $order->id);
                if ($order->order_code) {
                    $q->orWhere('code', 'PT-' . $order->order_code)
                      ->orWhere('code', 'like', 'PT-' . $order->order_code . '%');
                }
            })->where('type', 'Thu')->sum('amount');
            $paid = min($total, max($paid, $txPaid));

            $remaining = max(0, $total - $paid);
            $unpaidFromOrders += $remaining;
        }

        // Các phiếu thu nợ đại lý không gắn riêng theo từng đơn
        $generalTxPaid = (float) Transaction::where('type', 'Thu')
            ->whereNull('order_id')
            ->where(function($q) {
                $q->where('customer_partner_name', $this->name)
                  ->orWhere('customer_partner_name', 'like', '%' . $this->name . '%');
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

        return $liveDebt;
    }
}

