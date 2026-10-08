<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\Announcement::updateOrCreate(
    ['title' => '💰 [YÊU CẦU DUYỆT TIỀN COD] Xe 29C-888.99 nộp 50.000.000 đ'],
    [
        'content'           => "Lái xe Bùi Văn Thành & Lê Văn Nam (Xe 29C-888.99) vừa lập biên bản nộp tiền BG-TIEN-20260927-268.\n• Số tiền nộp: 50.000.000 đ (1 đơn hàng DH-2026-BS01)\n• Kế toán chỉ định nhận: Admin KBTech\n• Hình thức nộp: Tiền mặt tại quỹ\n👉 Đề nghị Kế toán Admin KBTech kiểm đếm tiền mặt thực tế và bấm 'Duyệt & Tạo Phiếu Thu' vào Sổ Quỹ CSDL!",
        'author'            => 'Bùi Văn Thành',
        'author_role'       => 'Lái xe giao hàng',
        'priority'          => 'Khẩn cấp',
        'target_department' => 'Kế toán',
        'read_by'           => [],
    ]
);

echo "Total announcements: " . \App\Models\Announcement::count() . "\n";
