<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyBankAccount extends Model
{
    use HasFactory;

    protected $table = 'company_bank_accounts';

    protected $fillable = [
        'bank_name',
        'bank_code',
        'account_number',
        'account_holder',
        'branch',
        'branch_id',
        'is_default',
        'is_active',
        'qr_template',
        'notes',
    ];

    public function branchRef()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Tạo đường dẫn ảnh mã QR VietQR động
     */
    public function getVietQrUrl(float $amount = 0, string $memo = ''): string
    {
        $binOrCode = $this->bank_code ?: 'MB';
        $acc = preg_replace('/[^0-9]/', '', (string)$this->account_number);
        $template = $this->qr_template ?: 'compact2';
        
        $url = "https://img.vietqr.io/image/{$binOrCode}-{$acc}-{$template}.png";
        $params = [];
        if ($amount > 0) {
            $params['amount'] = (int)$amount;
        }
        if (!empty($memo)) {
            $params['addInfo'] = $memo;
        }
        if (!empty($this->account_holder)) {
            $params['accountName'] = $this->account_holder;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}
