<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dealer;

class UpdateDealersAdministrativeAddressSeeder extends Seeder
{
    public function run(): void
    {
        $dealers = [
            'DL-HN-001' => [
                'old_ward' => 'Xã Định Công Thượng (cũ)',
                'old_district' => 'Huyện Thanh Trì (cũ)',
                'old_city' => 'Tỉnh Hà Tây (cũ)',
                'new_ward' => 'Phường Phương Liệt',
                'new_district' => null,
                'new_city' => 'TP. Hà Nội',
            ],
            'DL-HN-002' => [
                'old_ward' => 'Xã Dịch Vọng (cũ)',
                'old_district' => 'Huyện Từ Liêm (cũ)',
                'old_city' => 'TP. Hà Nội',
                'new_ward' => 'Phường Nghĩa Tân Mới',
                'new_district' => null,
                'new_city' => 'TP. Hà Nội',
            ],
            'DL-HN-003' => [
                'name' => 'Đại Lý Điện Nước Phát Tiến (Diễn Châu)',
                'address' => 'Ngã ba Cầu Bùng, Xã Minh Châu',
                'old_ward' => 'Xã Diễn Thắng (cũ)',
                'old_district' => 'Huyện Diễn Châu',
                'old_city' => 'Tỉnh Nghệ An',
                'new_ward' => 'Xã Minh Châu',
                'new_district' => null,
                'new_city' => 'Tỉnh Nghệ An',
            ],
            'DL-HN-004' => [
                'old_ward' => 'Xã Mễ Trì (cũ)',
                'old_district' => 'Huyện Từ Liêm (cũ)',
                'old_city' => 'TP. Hà Nội',
                'new_ward' => 'Phường Nam Trung Yên Mới',
                'new_district' => null,
                'new_city' => 'TP. Hà Nội',
            ],
            'DL-HCM-005' => [
                'old_ward' => 'Phường 14 (cũ)',
                'old_district' => 'Quận 10',
                'old_city' => 'TP. Hồ Chí Minh',
                'new_ward' => 'Phường 14 Sáp Nhập',
                'new_district' => null,
                'new_city' => 'TP. Hồ Chí Minh',
            ],
        ];

        foreach ($dealers as $code => $addr) {
            Dealer::where('code', $code)->update($addr);
        }

        // Cập nhật các đại lý khác nếu có
        Dealer::whereNull('new_city')->get()->each(function ($d) {
            $d->update([
                'old_ward' => $d->old_ward ?? 'Xã/Phường cũ',
                'old_district' => $d->old_district ?? ($d->district ?: 'Quận/Huyện cũ'),
                'old_city' => $d->old_city ?? ($d->city ?: 'Tỉnh/TP cũ'),
                'new_ward' => $d->new_ward ?? 'Xã/Phường mới',
                'new_district' => $d->new_district ?? ($d->district ?: 'Quận/Huyện mới'),
                'new_city' => $d->new_city ?? ($d->city ?: 'Tỉnh/TP mới'),
            ]);
        });

        echo "Updated administrative address fields for dealers successfully!\n";
    }
}
