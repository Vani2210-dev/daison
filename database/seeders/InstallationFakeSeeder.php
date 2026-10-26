<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FilterInstallation;
use App\Models\FilterInstallationItem;
use App\Models\FilterInstalledMachine;
use App\Models\FilterMachineModel;
use App\Models\FilterPart;
use App\Models\User;
use Illuminate\Support\Str;

class InstallationFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tech = User::find(5) ?? User::where('department', 'like', '%thuật%')->orWhere('department', 'like', '%tech%')->first() ?? User::first();
        $admin = User::find(1) ?? User::first();

        $models = FilterMachineModel::all();
        if ($models->isEmpty()) {
            $this->command->warn('Chưa có danh mục máy lọc nào.');
            return;
        }

        $parts = FilterPart::all();

        // 1. Tạo thêm các tem QR chưa kích hoạt sẵn sàng để gán
        for ($q = 1; $q <= 10; $q++) {
            $numStr = sprintf('%04d', rand(1000, 9999));
            $qrCode = 'QR-' . date('Ym') . '-' . $numStr;
            $mCode = 'ML-' . date('Ym') . '-' . $numStr;
            $token = Str::lower(Str::random(16));

            FilterInstalledMachine::firstOrCreate(
                ['qr_code' => $qrCode],
                [
                    'machine_code' => $mCode,
                    'qr_token' => $token,
                    'status' => 'unactivated',
                    'water_source_type' => 'Nước máy thành phố',
                    'warranty_months' => 24,
                    'filter_replacement_months' => 6,
                    'admin_notes' => 'Tem QR in sẵn lô mới chờ dán máy khi lắp đặt',
                    'created_by' => $admin?->id ?? 1,
                ]
            );
        }

        // 2. Danh sách 10 khách hàng lắp đặt thực tế
        $customers = [
            [
                'name' => 'Chị Nguyễn Thị Mai',
                'phone' => '0988.234.567',
                'address' => 'Số 18 Ngõ 45 Lê Lợi, P. Hoàng Văn Thụ, TP. Bắc Giang',
                'water_source' => 'Nước máy thành phố',
                'notes' => 'Lắp đặt dưới gầm chậu rửa bát, gia đình có sẵn nguồn cấp nước và ổ cắm điện.',
                'status' => 'pending',
                'appointment_hours' => 2,
            ],
            [
                'name' => 'Anh Trần Văn Bình',
                'phone' => '0912.456.789',
                'address' => 'Căn 802 Tòa A Chung cư Quang Minh, P. Dĩnh Kế, TP. Bắc Giang',
                'water_source' => 'Nước máy chung cư',
                'notes' => 'Khách yêu cầu mang vòi inox cổ ngỗng cao cấp gạt ngang, lắp gọn gàng trên mặt đá.',
                'status' => 'pending',
                'appointment_hours' => 4,
            ],
            [
                'name' => 'Chị Hoàng Thu Thủy',
                'phone' => '0973.889.912',
                'address' => 'Số 92 Đường Hùng Vương, P. Ngô Quyền, TP. Bắc Giang',
                'water_source' => 'Nước máy thành phố',
                'notes' => 'Gia đình muốn khảo sát vị trí lắp tủ đứng phòng khách, đo áp lực nước đầu vào.',
                'status' => 'pending',
                'appointment_hours' => 6,
            ],
            [
                'name' => 'Anh Đặng Minh Tâm',
                'phone' => '0904.123.654',
                'address' => 'Biệt thự BT03 Khu đô thị Bách Việt Lake Garden, Dĩnh Kế, Bắc Giang',
                'water_source' => 'Nước máy đô thị',
                'notes' => 'Khách hẹn lắp vào đầu giờ chiều, thi công luồn dây cấp nước âm tường.',
                'status' => 'in_progress',
                'appointment_hours' => 1,
            ],
            [
                'name' => 'Anh Lê Quốc Bảo',
                'phone' => '0936.789.012',
                'address' => 'Thôn Song Khê 1, Xã Song Khê, TP. Bắc Giang',
                'water_source' => 'Nước giếng khoan qua bể lắng',
                'notes' => 'Nguồn nước giếng khoan đã qua lọc thô, cần kiểm tra TDS trước khi đấu nối máy.',
                'status' => 'pending',
                'appointment_hours' => 8,
            ],
            [
                'name' => 'Chị Phạm Hải Đăng',
                'phone' => '0968.334.556',
                'address' => 'Số 142 Nguyễn Thị Lưu, P. Ngô Quyền, TP. Bắc Giang',
                'water_source' => 'Nước máy thành phố',
                'notes' => 'Đổi từ máy RO cũ sang máy Hydrogen mới, nhờ kỹ thuật viên tháo dỡ máy cũ giúp.',
                'status' => 'in_progress',
                'appointment_hours' => 3,
            ],
            [
                'name' => 'Cô Vũ Thị Bích Ngọc',
                'phone' => '0982.556.778',
                'address' => 'Số 26 Minh Khai, P. Xương Giang, TP. Bắc Giang',
                'water_source' => 'Nước máy thành phố',
                'notes' => 'Khách lớn tuổi, hướng dẫn kỹ quy trình xả rửa ban đầu và lịch thay lõi định kỳ.',
                'status' => 'pending',
                'appointment_hours' => 12,
            ],
            [
                'name' => 'Anh Bùi Anh Tuấn',
                'phone' => '0915.678.234',
                'address' => 'Tổ 6 Phường Thọ Xương, TP. Bắc Giang',
                'water_source' => 'Nước máy thành phố',
                'notes' => 'Lắp đặt tại quầy pha chế quán cà phê gia đình, yêu cầu máy công suất lọc lớn.',
                'status' => 'pending',
                'appointment_hours' => 18,
            ],
            [
                'name' => 'Bác Dương Hồng Sơn',
                'phone' => '0979.112.334',
                'address' => 'Số 55 Thân Nhân Trung, P. Mỹ Độ, TP. Bắc Giang',
                'water_source' => 'Nước máy thành phố',
                'notes' => 'Đã lắp hoàn tất sáng nay, máy hoạt động êm ái, nước đầu ra tinh khiết đạt chuẩn.',
                'status' => 'completed',
                'appointment_hours' => -5,
            ],
            [
                'name' => 'Chị Đỗ Kim Ngân',
                'phone' => '0902.998.877',
                'address' => 'Khu dân cư số 2, Xã Dĩnh Trì, TP. Bắc Giang',
                'water_source' => 'Nước máy khu dân cư mới',
                'notes' => 'Đã nghiệm thu và bàn giao tem QR bảo hành điện tử chính hãng 24 tháng cho khách.',
                'status' => 'completed',
                'appointment_hours' => -24,
            ],
        ];

        $todayCount = FilterInstallation::whereDate('created_at', today())->count();

        foreach ($customers as $idx => $c) {
            $seq = $todayCount + $idx + 1;
            $code = 'LD-' . date('Ymd') . '-' . sprintf('%03d', $seq);

            $selectedModel = $models[$idx % $models->count()];
            $assignedTech = ($idx % 3 === 0) ? $admin : $tech;

            $apptDate = ($c['appointment_hours'] >= 0) 
                ? now()->addHours($c['appointment_hours']) 
                : now()->subHours(abs($c['appointment_hours']));

            $completedDate = ($c['status'] === 'completed') ? now()->subHours(1) : null;
            $installedAt = ($c['status'] === 'completed') ? now()->subHours(1) : null;

            $installation = FilterInstallation::create([
                'code' => $code,
                'customer_name' => $c['name'],
                'customer_phone' => $c['phone'],
                'customer_address' => $c['address'],
                'machine_model' => $selectedModel->name,
                'machine_model_id' => $selectedModel->id,
                'machine_serial' => 'SN-' . strtoupper(Str::slug($selectedModel->brand)) . '-' . date('Y') . '-' . rand(10000, 99999),
                'water_source_type' => $c['water_source'],
                'technician_id' => $assignedTech?->id,
                'technician_name' => $assignedTech?->name ?? 'KTV Lê Văn Trung',
                'appointment_date' => $apptDate,
                'completed_date' => $completedDate,
                'installed_at' => $installedAt,
                'status' => $c['status'],
                'parts_fee' => 70000,
                'total_amount' => 70000,
                'payment_status' => ($c['status'] === 'completed') ? 'paid' : 'unpaid',
                'customer_notes' => $c['notes'],
                'completion_notes' => ($c['status'] === 'completed') ? 'Máy chạy êm, xả bình nước đạt chuẩn, khách hài lòng.' : null,
                'warranty_months' => 24,
                'warranty_until' => ($c['status'] === 'completed') ? now()->addMonths(24) : null,
                'filter_replacement_months' => 6,
                'next_maintenance_date' => ($c['status'] === 'completed') ? now()->addMonths(6) : null,
                'created_by' => $admin?->id ?? 1,
            ]);

            // Thêm phụ kiện kèm theo mẫu (FilterInstallationItem)
            if ($parts->isNotEmpty()) {
                $cutPart = $parts->firstWhere('code', 'CUT-NOI-NHANH-6') ?? $parts->first();
                FilterInstallationItem::create([
                    'installation_id' => $installation->id,
                    'part_id' => $cutPart?->id,
                    'part_code' => $cutPart?->code ?? 'CUT-NOI-NHANH-6',
                    'part_name' => $cutPart?->name ?? 'Bộ cút nối nhanh phi 6',
                    'unit' => $cutPart?->unit ?? 'Bộ',
                    'quantity' => 1,
                    'unit_price' => $cutPart?->out_price ?? 70000,
                    'total_price' => $cutPart?->out_price ?? 70000,
                    'note' => 'Đấu nối vòi gạt trên mặt đá',
                ]);
            }

            // Với đơn đã completed: Gắn tem QR đã kích hoạt
            if ($c['status'] === 'completed') {
                $numStr = sprintf('%04d', rand(1000, 9999));
                $qrCode = 'QR-' . date('Ym') . '-' . $numStr;
                $mCode = 'ML-' . date('Ym') . '-' . $numStr;
                $token = Str::lower(Str::random(16));

                FilterInstalledMachine::create([
                    'machine_code' => $mCode,
                    'qr_code' => $qrCode,
                    'qr_token' => $token,
                    'installation_id' => $installation->id,
                    'status' => 'activated',
                    'customer_name' => $installation->customer_name,
                    'customer_phone' => $installation->customer_phone,
                    'customer_address' => $installation->customer_address,
                    'water_source_type' => $installation->water_source_type,
                    'machine_model_id' => $installation->machine_model_id,
                    'machine_model' => $installation->machine_model,
                    'machine_serial' => $installation->machine_serial,
                    'technician_id' => $installation->technician_id,
                    'technician_name' => $installation->technician_name,
                    'installed_at' => now()->subHours(1),
                    'activated_at' => now()->subHours(1),
                    'activated_by' => $assignedTech?->id,
                    'activated_by_name' => $assignedTech?->name,
                    'warranty_months' => 24,
                    'warranty_until' => now()->addMonths(24),
                    'filter_replacement_months' => 6,
                    'next_maintenance_date' => now()->addMonths(6),
                    'accessories' => json_encode([
                        [
                            'id' => 15,
                            'code' => 'CUT-NOI-NHANH-6',
                            'name' => 'Bộ cút nối nhanh chữ L, T, I phi 6',
                            'qty' => 1,
                            'installed_date' => date('Y-m-d')
                        ]
                    ]),
                    'activation_notes' => 'Đã kích hoạt tem QR và bàn giao phiếu bảo hành điện tử',
                    'created_by' => $admin?->id ?? 1,
                ]);
            }
        }

        $this->command->info('Đã tạo thành công 10 đơn hàng lắp đặt giả lập (FilterInstallation) cùng phụ kiện và tem QR!');
    }
}
