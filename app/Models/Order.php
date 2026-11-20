<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const PICKING_TRANSFERRED_STATUSES = [
        'Đã bốc xong',
        'Kiểm hàng',
        'Đang kiểm hàng',
        'Đã kiểm hàng',
        'Đã kiểm',
        'Sẵn sàng xuất xe',
        'Đã kiểm hàng (Sẵn sàng xuất xe)',
        'Kho đã xuất đủ',
        'Đang vận chuyển',
        'Đang giao hàng',
        'Đã giao',
        'Giao thành công',
    ];

    protected $fillable = [
        'order_code',
        'dealer_id',
        'customer_name',
        'recipient_name',
        'customer_phone',
        'customer_address',
        'warehouse',
        'delivery_date',
        'picking_date',
        'creator_id',
        'order_creator',
        'sales_rep_id',
        'sales_accountant_id',
        'warehouse_staff_id',
        'assigned_driver_id',
        'assigned_vehicle_plate',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'status',
        'stock_deducted',
        'payment_status',
        'payment_method',
        'driver_debt_amount',
        'has_returns',
        'total_returned_amount',
        'return_slip_code',
        'settled_with_accountant',
        'order_type',
        'project_name',
        'contract_number',
        'site_contact_name',
        'site_contact_phone',
        'notes',
        'delivery_documents',
        'shortage_reported',
        'shortage_reported_at',
        'shortage_reported_note',
        'failed_delivery_reason',
        'failed_delivery_note',
        'failed_delivery_at',
        'failed_delivery_by',
        'rescheduled_date',
        'credit_overrun_status',
        'credit_overrun_amount',
        'credit_overrun_approved_by',
        'credit_overrun_approved_name',
        'credit_overrun_approved_at',
        'credit_overrun_reason',
        'credit_overrun_payment_due_date',
        'credit_overrun_note_for_dealer',
        'parent_order_id',
        'parent_order_code',
        'backorder_status',
        'has_shortage_warning',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'driver_debt_amount' => 'decimal:2',
        'has_returns' => 'boolean',
        'total_returned_amount' => 'decimal:2',
        'settled_with_accountant' => 'boolean',
        'shortage_reported' => 'boolean',
        'has_shortage_warning' => 'boolean',
        'stock_deducted' => 'boolean',
        'delivery_documents' => 'array',
    ];

    public function parentOrder()
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function backOrders()
    {
        return $this->hasMany(Order::class, 'parent_order_id');
    }

    public function deliveryReturns()
    {
        return $this->hasMany(DeliveryReturn::class);
    }

    /**
     * Scope lọc đơn hàng theo phân quyền truy cập:
     * - Quản lý / Kế toán / Có quyền orders.view_all: Xem toàn bộ đơn hàng tổng công ty
     * - Nhân viên: Chỉ xem đơn hàng do mình tạo hoặc phụ trách
     */
    public function scopeAccessibleBy($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return $query;
        }

        if ($user->hasRole('Manager') || $user->hasRole('Admin') || $user->hasPermission('orders.view_all')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
              ->orWhere('sales_rep_id', $user->id);
            if (!empty($user->name)) {
                $q->orWhere('order_creator', $user->name);
            }
            if (!empty($user->code)) {
                $q->orWhere('order_creator', $user->code);
            }
        });
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function salesAccountant()
    {
        return $this->belongsTo(User::class, 'sales_accountant_id');
    }

    public function warehouseStaff()
    {
        return $this->belongsTo(User::class, 'warehouse_staff_id');
    }

    public function assignedDriver()
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function discrepancies()
    {
        return $this->hasMany(DeliveryDiscrepancy::class, 'order_id');
    }

    public function vehicleTransfers()
    {
        return $this->hasMany(VehicleOrderTransfer::class, 'order_id');
    }

    /**
     * Scope chỉ lấy các đơn hàng xuất bán của công ty (loại trừ đơn hàng xuất bán từ kho hàng ôm của nhân sự)
     */
    public function scopeCompanyOrders($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('order_type')
              ->orWhere('order_type', '!=', 'holding_sale');
        })
        ->where(function ($q) {
            $q->whereNull('warehouse')
              ->orWhere(function ($sub) {
                  $sub->where('warehouse', 'not like', '%Kho Hàng Ôm%')
                      ->where('warehouse', 'not like', 'KHO-OM-%');
              });
        });
    }

    /**
     * Ghi nhật ký thao tác nghiệp vụ cho đơn hàng vào file log (storage/logs/orders/{order_code}.log)
     * và AuditLog, tuyệt đối không lưu đè hoặc tự động chèn vào trường `notes` (ghi chú đơn hàng của khách).
     */
    public function logOperation(string $action, string $details, ?string $performerName = null, ?string $oldValue = null, ?string $newValue = null)
    {
        $performer = $performerName ?? (auth()->user()?->name ?? 'Hệ thống');
        $now = now();
        $dateStr = $now->format('Y-m-d H:i:s');

        // 1. Ghi vào file log riêng cho đơn hàng: storage/logs/orders/{order_code}.log
        try {
            $logDir = storage_path('logs/orders');
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $this->order_code ?: ('ORDER_' . $this->id));
            $logFile = $logDir . '/' . $safeCode . '.log';
            $logEntry = sprintf(
                "[%s] [%s] [Người thực hiện: %s] [Trạng thái: %s -> %s]\nChi tiết: %s\n--------------------------------------------------------------------------------\n",
                $dateStr,
                $action,
                $performer,
                $oldValue ?? '-',
                $newValue ?? $this->status,
                $details
            );
            @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

            // Ghi thêm vào file tổng hợp order_operations.log
            $generalLog = storage_path('logs/order_operations.log');
            @file_put_contents($generalLog, "[{$dateStr}] [ĐƠN {$this->order_code}] [{$action}] ({$performer}): {$details}\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Lỗi ghi file log đơn hàng {$this->order_code}: " . $e->getMessage());
        }

        // 2. Ghi vào AuditLog trong database
        try {
            AuditLog::create([
                'timestamp'            => $dateStr,
                'performer_name'       => $performer,
                'user_id'              => auth()->id(),
                'action'               => $action,
                'module'               => 'Đơn Hàng & Vận Tải',
                'target_document_code' => $this->order_code,
                'field_name'           => 'Trạng thái / Thao tác',
                'old_value'            => $oldValue,
                'new_value'            => $newValue ?? $this->status,
                'notes'                => $details,
            ]);
        } catch (\Throwable $e) {}
    }

    /**
     * Tự động giải mã HTML entities nếu customer_name bị mã hóa nhiều lần (ví dụ &amp;amp;)
     */
    public function getCustomerNameAttribute($value): string
    {
        if (empty($value)) {
            return '';
        }
        $decoded = (string)$value;
        while (str_contains($decoded, '&amp;') || str_contains($decoded, '&quot;') || str_contains($decoded, '&#039;')) {
            $prev = $decoded;
            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $prev) break;
        }
        return $decoded;
    }

    /**
     * Lấy danh sách lịch sử thao tác từ file log riêng của đơn hàng
     */
    public function getOperationLogs(): array
    {
        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $this->order_code ?: ('ORDER_' . $this->id));
        $logFile = storage_path('logs/orders/' . $safeCode . '.log');
        if (!file_exists($logFile)) {
            return [];
        }
        $content = @file_get_contents($logFile);
        if (!$content) return [];
        return array_values(array_filter(array_map('trim', explode('--------------------------------------------------------------------------------', $content))));
    }

    /**
     * Lấy toàn bộ nội dung file log dạng raw text
     */
    public function getOperationLogsRaw(): string
    {
        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $this->order_code ?: ('ORDER_' . $this->id));
        $logFile = storage_path('logs/orders/' . $safeCode . '.log');
        if (!file_exists($logFile)) {
            return '';
        }
        return @file_get_contents($logFile) ?: '';
    }

    /**
     * Kiểm tra đơn hàng đã được bên Bốc hoàn tất / bấm Chuyển kiểm hay chưa.
     */
    public function hasPickingTransferred(): bool
    {
        return in_array($this->status, self::PICKING_TRANSFERRED_STATUSES, true);
    }

    /**
     * Kiểm tra đơn hàng đã có dữ liệu bốc/kiểm từ kho chưa (không phụ thuộc trạng thái hiện tại
     * để hoàn/hủy đơn sau khi đã kiểm hàng vẫn đọc đúng số lượng thực tế đã bốc).
     */
    public function hasWarehousePickingData(): bool
    {
        $this->loadMissing('items');
        if ($this->hasPickingTransferred()) {
            return true;
        }
        return $this->items->contains(function ($it) {
            return (bool)$it->is_picked
                || (float)($it->picked_qty ?? 0) > 0
                || in_array($it->item_status, ['Đủ hàng', 'Đang bốc', 'Thiếu hàng'], true)
                || (bool)$it->is_checked
                || (float)($it->checked_qty ?? 0) > 0;
        });
    }

    /**
     * Tính toán số lượng & thành tiền thực tế giao theo số lượng đã bốc (picked_qty).
     */
    public function computePickedDeliveryBreakdown(): array
    {
        $this->loadMissing('items');
        $usePicked = $this->hasWarehousePickingData();
        $alreadyCompleted = in_array($this->status, ['Đã giao', 'Giao thành công'], true);

        $itemsMap = [];
        $hasShortage = false;
        $origItemsSubtotal = 0.0;
        $newSubtotal = 0.0;

        foreach ($this->items as $it) {
            $origSold = (float)($it->quantity ?? 0);
            $origGift = (float)($it->gift_quantity ?? 0);
            $origAmt = (float)($it->amount ?? 0);
            $origItemsSubtotal += $origAmt;

            if (!$usePicked || $alreadyCompleted) {
                $newSold = $origSold;
                $newGift = $origGift;
                $newDisc = (float)($it->discount_amount ?? 0);
                $newAmt = $origAmt;
            } else {
                $pickedTotal = max(0.0, (float)($it->picked_qty ?? 0));
                $newSold = min($origSold, $pickedTotal);
                $newGift = min($origGift, max(0.0, $pickedTotal - $origSold));

                if (abs($newSold - $origSold) > 0.0001 || abs($newGift - $origGift) > 0.0001) {
                    $hasShortage = true;
                }

                $unitPrice = (float)($it->unit_price ?? 0);
                $discPct = (float)($it->discount_percent ?? 0);
                $origDiscAmt = (float)($it->discount_amount ?? 0);

                if (abs($newSold - $origSold) > 0.0001 && $origSold > 0) {
                    if ($discPct > 0) {
                        $newDisc = round(($newSold * $unitPrice * $discPct) / 100, 2);
                    } elseif ($origDiscAmt > 0) {
                        $newDisc = round($origDiscAmt * ($newSold / $origSold), 2);
                    } else {
                        $newDisc = 0.0;
                    }
                    $newAmt = ($unitPrice == 0.0) ? 0.0 : max(0.0, round(($newSold * $unitPrice) - $newDisc, 2));
                } else {
                    $newDisc = $origDiscAmt;
                    $newAmt = $origAmt;
                }
            }

            $newSubtotal += $newAmt;
            $itemsMap[$it->id] = [
                'item_id'        => $it->id,
                'sku'            => $it->sku,
                'name'           => $it->name,
                'orig_sold_qty'  => $origSold,
                'orig_gift_qty'  => $origGift,
                'sold_qty'       => $newSold,
                'gift_qty'       => $newGift,
                'picked_qty'     => (float)($it->picked_qty ?? 0),
                'line_discount'  => $newDisc,
                'line_amount'    => $newAmt,
                'changed'        => (abs($newSold - $origSold) > 0.0001 || abs($newGift - $origGift) > 0.0001),
            ];
        }

        if (!$hasShortage) {
            return [
                'has_shortage'    => false,
                'items'           => $itemsMap,
                'subtotal'        => (float)($this->subtotal > 0 ? $this->subtotal : $origItemsSubtotal),
                'discount_amount' => (float)($this->discount_amount ?? 0),
                'tax_amount'      => (float)($this->tax_amount ?? 0),
                'total_amount'    => (float)($this->total_amount ?? 0),
            ];
        }

        $origSubtotal = (float)($this->subtotal > 0 ? $this->subtotal : $origItemsSubtotal);
        $newSubtotal = round($newSubtotal, 2);

        $orderDiscPct = (float)($this->discount_percent ?? 0);
        $origOrderDisc = (float)($this->discount_amount ?? 0);
        if ($orderDiscPct > 0) {
            $newDiscount = round(($newSubtotal * $orderDiscPct) / 100, 2);
        } elseif ($origOrderDisc > 0 && $origSubtotal > 0) {
            $newDiscount = round($origOrderDisc * ($newSubtotal / $origSubtotal), 2);
        } else {
            $newDiscount = 0.0;
        }

        $origTax = (float)($this->tax_amount ?? 0);
        if ($origTax > 0 && $origSubtotal > 0) {
            $newTax = round($origTax * ($newSubtotal / $origSubtotal), 2);
        } else {
            $newTax = 0.0;
        }

        $newTotal = max(0.0, round($newSubtotal - $newDiscount + $newTax, 2));

        return [
            'has_shortage'    => true,
            'items'           => $itemsMap,
            'subtotal'        => $newSubtotal,
            'discount_amount' => $newDiscount,
            'tax_amount'      => $newTax,
            'total_amount'    => $newTotal,
        ];
    }

    /**
     * Khi hoàn thành giao đơn: tự động chỉnh sửa lại số lượng & thành tiền trên đơn hàng theo số lượng đã bốc.
     */
    public function applyPickedQuantitiesAdjustment(?string $performerName = null): array
    {
        $breakdown = $this->computePickedDeliveryBreakdown();
        if (empty($breakdown['has_shortage'])) {
            return ['adjusted' => false, 'changes' => [], 'breakdown' => $breakdown];
        }

        $changes = [];
        foreach ($this->items as $it) {
            $info = $breakdown['items'][$it->id] ?? null;
            if (!$info) continue;

            if ($info['changed']) {
                $changes[] = sprintf(
                    '[%s] %s: Đặt %s -> Thực giao %s',
                    $it->sku,
                    $it->name,
                    rtrim(rtrim(number_format($info['orig_sold_qty'], 2, '.', ''), '0'), '.'),
                    rtrim(rtrim(number_format($info['sold_qty'], 2, '.', ''), '0'), '.')
                );

                $it->quantity = $info['sold_qty'];
                $it->gift_quantity = $info['gift_qty'];
                $it->discount_amount = $info['line_discount'];
                $it->amount = $info['line_amount'];

                $totalActual = $info['sold_qty'] + $info['gift_qty'];
                $it->picked_qty = $totalActual;
                $it->is_picked = $totalActual > 0;
                $it->item_status = $totalActual > 0 ? 'Đủ hàng' : 'Chờ bốc';
                $retQ = (float)($it->returned_qty ?? 0);
                if ($retQ > $info['sold_qty']) {
                    $retQ = $info['sold_qty'];
                    $it->returned_qty = $retQ;
                }
                $it->delivered_qty = max(0.0, $info['sold_qty'] - $retQ);
                $it->save();
            }
        }

        if (!empty($changes)) {
            $oldTotal = (float)($this->total_amount ?? 0);
            $this->subtotal = $breakdown['subtotal'];
            $this->discount_amount = $breakdown['discount_amount'];
            $this->tax_amount = $breakdown['tax_amount'];
            $this->total_amount = $breakdown['total_amount'];
            $this->save();

            $this->logOperation(
                'Tự Động Chỉnh SL Theo Thực Bốc',
                "Hoàn thành giao hàng: Tự động chỉnh sửa số lượng đơn theo thực bốc (" . implode('; ', $changes) . "). Tổng đơn: " . number_format($oldTotal, 0, ',', '.') . " đ -> " . number_format($this->total_amount, 0, ',', '.') . " đ.",
                $performerName ?? (auth()->user()?->name ?? 'Hệ thống'),
                $this->status,
                $this->status
            );
        }

        return [
            'adjusted'  => !empty($changes),
            'changes'   => $changes,
            'breakdown' => $breakdown,
        ];
    }
}