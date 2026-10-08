<?php

namespace Database\Seeders;

use App\Models\StaffGroup;
use App\Models\StaffGroupClassification;
use Illuminate\Database\Seeder;

class StaffGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Phân loại nhóm
        $c1 = StaffGroupClassification::updateOrCreate(
            ['code' => 'KD'],
            [
                'name' => 'Khối Kinh Doanh & Thị Trường',
                'color' => 'blue',
                'description' => 'Đội ngũ phát triển thị trường, chăm sóc đại lý và phân phối sản phẩm'
            ]
        );

        $c2 = StaffGroupClassification::updateOrCreate(
            ['code' => 'KV'],
            [
                'name' => 'Khối Kho Vận & Soạn Hàng',
                'color' => 'amber',
                'description' => 'Đội ngũ quản lý kho, bốc xếp, kiểm hàng QC và soạn hàng xuất kho'
            ]
        );

        $c3 = StaffGroupClassification::updateOrCreate(
            ['code' => 'GV'],
            [
                'name' => 'Khối Giao Vận & Lái Xe',
                'color' => 'purple',
                'description' => 'Đội ngũ lái xe tải, giao hàng các tuyến và thu hồi công nợ COD'
            ]
        );

        $c4 = StaffGroupClassification::updateOrCreate(
            ['code' => 'KT'],
            [
                'name' => 'Khối Tài Chính & Kế Toán',
                'color' => 'emerald',
                'description' => 'Bộ phận kế toán công nợ, quản lý sổ quỹ và đối soát ngân hàng'
            ]
        );

        // 2. Nhóm nhân sự
        StaffGroup::updateOrCreate(
            ['code' => 'NNS-001'],
            [
                'name' => 'Tổ Bốc Xếp & Soạn Kho Mê Linh',
                'classification_id' => $c2->id,
                'user_id' => 6, // Lê Văn Trung
                'user_ids' => [6, 7, 8],
                'description' => 'Phụ trách bốc hàng, soạn đơn hàng, kiểm tra đủ số lượng và đóng gói tại Kho Tổng Mê Linh',
                'status' => 'active'
            ]
        );

        StaffGroup::updateOrCreate(
            ['code' => 'NNS-002'],
            [
                'name' => 'Đội Vận Tải & Giao Nhận Số 1',
                'classification_id' => $c3->id,
                'user_id' => 8, // Bùi Văn Thành
                'user_ids' => [8, 11, 12, 13, 14],
                'description' => 'Điều phối đội xe tải 3.5T - 5T, giao hàng tuyến Hà Nội - Nghệ An - Hà Tĩnh',
                'status' => 'active'
            ]
        );

        StaffGroup::updateOrCreate(
            ['code' => 'NNS-003'],
            [
                'name' => 'Nhóm Sales Phát Triển Đại Lý Miền Bắc',
                'classification_id' => $c1->id,
                'user_id' => 5, // Phạm Minh Tuấn
                'user_ids' => [5, 3],
                'description' => 'Mở rộng hệ thống đại lý Cấp 1, Cấp 2 và phát triển doanh số khu vực phía Bắc',
                'status' => 'active'
            ]
        );

        StaffGroup::updateOrCreate(
            ['code' => 'NNS-004'],
            [
                'name' => 'Tổ Kế Toán Công Nợ & Ngân Hàng',
                'classification_id' => $c4->id,
                'user_id' => 4, // Trần Thị Thu Hà
                'user_ids' => [4, 9],
                'description' => 'Kiểm soát công nợ đại lý, quyết toán COD lái xe và đối soát sao kê ngân hàng VietQR',
                'status' => 'active'
            ]
        );
    }
}
