<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FilterInstalledMachine extends Model
{
    use HasFactory;

    protected $table = 'filter_installed_machines';

    protected $fillable = [
        'machine_code',
        'qr_code',
        'qr_token',
        'installation_id',
        'status',
        'customer_name',
        'customer_phone',
        'customer_address',
        'water_source_type',
        'machine_model_id',
        'machine_model',
        'machine_serial',
        'technician_id',
        'technician_name',
        'installed_at',
        'activated_at',
        'activated_by',
        'activated_by_name',
        'warranty_months',
        'warranty_until',
        'warranty_card_code',
        'filter_replacement_months',
        'next_maintenance_date',
        'warranty_notes',
        'tds_in',
        'tds_out',
        'pressure',
        'accessories',
        'activation_notes',
        'admin_notes',
        'view_count',
        'last_scanned_at',
        'created_by',
    ];

    protected $casts = [
        'installed_at'              => 'datetime',
        'activated_at'              => 'datetime',
        'warranty_until'            => 'date',
        'next_maintenance_date'     => 'date',
        'last_scanned_at'           => 'datetime',
        'warranty_months'           => 'integer',
        'filter_replacement_months' => 'integer',
        'view_count'                => 'integer',
        'tds_in'                    => 'integer',
        'tds_out'                   => 'integer',
    ];

    protected $appends = [
        'qr_url',
        'warranty_status',
        'warranty_days_remaining',
        'accessories_list',
    ];

    public function modelCatalog()
    {
        return $this->belongsTo(FilterMachineModel::class, 'machine_model_id');
    }

    public function installation()
    {
        return $this->belongsTo(FilterInstallation::class, 'installation_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function getQrUrlAttribute(): string
    {
        return url('/m/' . $this->qr_token);
    }

    public function getAccessoriesListAttribute(): array
    {
        if (empty($this->accessories)) {
            return [];
        }
        if (is_array($this->accessories)) {
            return $this->accessories;
        }
        $decoded = json_decode($this->accessories, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return [[
            'name'           => (string) $this->accessories,
            'quantity'       => 1,
            'installed_date' => $this->installed_at ? $this->installed_at->format('Y-m-d') : null,
            'unit'           => 'Cái',
        ]];
    }

    public function getWarrantyStatusAttribute(): string
    {
        if ($this->status === 'unactivated') {
            return 'unactivated';
        }

        if ($this->status === 'pending_activation') {
            return 'pending_activation';
        }

        if (!$this->warranty_until) {
            return $this->status ?: 'unknown';
        }

        $now = Carbon::today();
        $until = Carbon::parse($this->warranty_until);

        if ($until->isPast()) {
            return 'expired';
        }

        if ($now->diffInDays($until, false) <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public function getWarrantyDaysRemainingAttribute(): int
    {
        if (!$this->warranty_until) {
            return 0;
        }

        $now = Carbon::today();
        $until = Carbon::parse($this->warranty_until);

        return (int) $now->diffInDays($until, false);
    }

    /**
     * Tạo mã Token định danh ngẫu nhiên duy nhất cho QR
     */
    public static function generateUniqueQrToken(): string
    {
        do {
            $token = Str::lower(Str::random(16));
        } while (self::where('qr_token', $token)->exists());

        return $token;
    }

    /**
     * Tự động sinh mã máy & mã QR tiếp theo
     */
    public static function generateNextCodes(): array
    {
        $prefix = date('Ym');
        $count = self::whereDate('created_at', today())->count() + 1;
        $seq = sprintf('%04d', $count);

        $machineCode = 'ML-' . $prefix . '-' . $seq;
        $qrCode = 'QR-' . $prefix . '-' . $seq;

        // Đảm bảo không trùng lặp
        $suffix = 1;
        while (self::where('machine_code', $machineCode)->exists()) {
            $machineCode = 'ML-' . $prefix . '-' . sprintf('%04d', $count + $suffix);
            $qrCode = 'QR-' . $prefix . '-' . sprintf('%04d', $count + $suffix);
            $suffix++;
        }

        return [
            'machine_code' => $machineCode,
            'qr_code'      => $qrCode,
            'qr_token'     => self::generateUniqueQrToken(),
        ];
    }
}
