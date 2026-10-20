<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterInstallation extends Model
{
    use HasFactory;

    protected $table = 'filter_installations';

    protected $fillable = [
        'code',
        'customer_name',
        'customer_phone',
        'customer_address',
        'province',
        'district',
        'ward',
        'machine_model',
        'machine_model_id',
        'machine_serial',
        'water_source_type',
        'technician_id',
        'technician_name',
        'appointment_date',
        'completed_date',
        'status',
        'tds_in',
        'tds_out',
        'pressure',
        'service_fee',
        'parts_fee',
        'total_amount',
        'payment_status',
        'customer_notes',
        'completion_notes',
        'created_by',
        'installed_at',
        'warranty_months',
        'warranty_until',
        'warranty_card_code',
        'warranty_notes',
        'filter_replacement_months',
        'next_maintenance_date',
    ];

    protected $casts = [
        'appointment_date'          => 'datetime',
        'completed_date'            => 'datetime',
        'installed_at'              => 'datetime',
        'warranty_until'            => 'date',
        'next_maintenance_date'     => 'date',
        'warranty_months'           => 'integer',
        'filter_replacement_months' => 'integer',
        'service_fee'               => 'decimal:2',
        'parts_fee'                 => 'decimal:2',
        'total_amount'              => 'decimal:2',
        'tds_in'                    => 'integer',
        'tds_out'                   => 'integer',
    ];

    /**
     * Trạng thái bảo hành máy: active (còn hạn), expiring_soon (sắp hết trong 30 ngày), expired (hết hạn)
     */
    public function getWarrantyStatusAttribute(): string
    {
        if (!$this->warranty_until) {
            return 'unknown';
        }
        $today = now()->startOfDay();
        $until = $this->warranty_until->startOfDay();

        if ($until < $today) {
            return 'expired';
        }
        if ($until <= now()->addDays(30)->endOfDay()) {
            return 'expiring_soon';
        }
        return 'active';
    }

    /**
     * Số ngày còn lại của hạn bảo hành
     */
    public function getWarrantyDaysRemainingAttribute(): int
    {
        if (!$this->warranty_until) {
            return 0;
        }
        return (int) now()->startOfDay()->diffInDays($this->warranty_until->startOfDay(), false);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function modelCatalog()
    {
        return $this->belongsTo(FilterMachineModel::class, 'machine_model_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(FilterInstallationItem::class, 'installation_id');
    }

    public function installedMachine()
    {
        return $this->hasOne(FilterInstalledMachine::class, 'installation_id');
    }
}
