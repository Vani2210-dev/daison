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
        'stock_deducted' => 'boolean',
    ];

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
}
