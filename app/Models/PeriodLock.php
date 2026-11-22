<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'ledger_type',
        'from_date',
        'to_date',
        'status',
        'locked_by_id',
        'reason',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by_id');
    }

    /**
     * Kiểm tra một ngày giao dịch có rơi vào kỳ kế toán / kho đang bị khóa hay không.
     */
    public static function isLockedDate($date = null, ?string $ledgerType = null): bool
    {
        try {
            $d = $date ? \Carbon\Carbon::parse($date)->toDateString() : now()->toDateString();
            return static::whereIn('status', ['locked', 'Đã khóa', 'Khóa'])
                ->whereDate('from_date', '<=', $d)
                ->whereDate('to_date', '>=', $d)
                ->when($ledgerType, function ($q) use ($ledgerType) {
                    $q->where(function ($sub) use ($ledgerType) {
                        $sub->whereNull('ledger_type')
                            ->orWhere('ledger_type', 'all')
                            ->orWhere('ledger_type', $ledgerType);
                    });
                })
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
