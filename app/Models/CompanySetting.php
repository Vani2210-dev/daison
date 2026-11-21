<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CompanySetting extends Model
{
    use HasFactory;

    protected const CACHE_KEY = 'company_settings_singleton_v2';
    protected static ?self $cachedInstance = null;

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

    protected static function booted(): void
    {
        $flush = static function () {
            static::$cachedInstance = null;
            Cache::forget('company_settings_singleton_v1');
            Cache::forget(self::CACHE_KEY);
        };
        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Lấy bản ghi cấu hình công ty (Singleton)
     */
    public static function getSettings(): self
    {
        if (static::$cachedInstance !== null) {
            return static::$cachedInstance;
        }

        // Lưu dạng mảng thuần trong Cache (tránh lỗi unserialize __PHP_Incomplete_Class khi cache restricted)
        $attributes = Cache::remember(self::CACHE_KEY, 300, function () {
            $row = self::first();
            if (!$row) {
                $row = self::create([
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
            return $row->getAttributes();
        });

        if (is_array($attributes)) {
            $model = new self();
            $model->setRawAttributes($attributes, true);
            $model->exists = true;
            static::$cachedInstance = $model;
            return $model;
        }

        // Fallback an toàn nếu cache bị hỏng định dạng
        Cache::forget(self::CACHE_KEY);
        $fresh = self::firstOrCreate([], [
            'company_name'         => 'Công ty Cổ phần Đại Sơn',
            'company_legal_name'   => 'CÔNG TY CỔ PHẦN THƯƠNG MẠI VÀ DỊCH VỤ ĐẠI SƠN',
            'email'                => 'contact@daison.vn',
            'website'              => 'https://daison.vn',
            'logo_url'             => '/images/logo-daison.png',
        ]);
        static::$cachedInstance = $fresh;
        return $fresh;
    }

    public function defaultBankAccount()
    {
        return $this->belongsTo(CompanyBankAccount::class, 'default_bank_account_id');
    }
}
