<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'dealer_id',
        'customer_name',
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
}