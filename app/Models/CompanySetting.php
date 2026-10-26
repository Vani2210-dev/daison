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
                'tax_code'             => '0109888999',
                'phone'                => '024 3888 9999',
                'hotline'              => '1900 8899',
                'email'                => 'contact@daison.vn',
                'address'              => 'Số 123 Đường Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội',
                'warehouse_address'    => 'Kho Tổng KCN Quang Minh, Mê Linh, Hà Nội',
                'website'              => 'https://daison.vn',
                'representative_name'  => 'Nguyễn Văn Đại',
                'representative_title' => 'Tổng Giám Đốc',
                'logo_url'             => '/images/logo-daison.png',
                'invoice_footer_note'  => 'Cảm ơn Quý khách hàng & Quý đối tác đã tin tưởng đồng hành cùng Đại Sơn!',
                'order_terms'          => 'Hàng hóa xuất kho được bảo hành theo tiêu chuẩn nhà sản xuất.',
            ]);
        }
        return $setting;
    }

    public function defaultBankAccount()
    {
        return $this->belongsTo(CompanyBankAccount::class, 'default_bank_account_id');
    }
}
