<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $table = 'company_settings';

    protected $fillable = [
        'company_name',
        'company_legal_name',
        'tax_code',
        'phone',
        'hotline',
        'email',
        'address',
        'warehouse_address',
        'website',
        'representative_name',
        'representative_title',
        'logo_url',
        'invoice_footer_note',
        'order_terms',
        'default_bank_account_id',
    ];

    /**
     * Lấy bản ghi cấu hình công ty (Singleton)
     */
    public static function getSettings(): self
    {
        $setting = self::first();
        if (!$setting) {
            $setting = self::create([
                'company_name'         => 'Công ty Cổ phần Đại Sơn',
                'company_legal_name'   => 'CÔNG TY CỔ PHẦN THƯƠNG MẠI VÀ DỊCH VỤ ĐẠI SƠN',
                'tax_code'             => '',
                'phone'                => '',
                'hotline'              => '',
                'email'                => 'contact@daison.vn',
                'address'              => '',
                'warehouse_address'    => '',
                'website'              => 'https://daison.vn',
                'representative_name'  => '',
                'representative_title' => '',
                'logo_url'             => '/images/logo-daison.png',
                'invoice_footer_note'  => '',
                'order_terms'          => '',
            ]);
        }
        return $setting;
    }

    public function defaultBankAccount()
    {
        return $this->belongsTo(CompanyBankAccount::class, 'default_bank_account_id');
    }
}
