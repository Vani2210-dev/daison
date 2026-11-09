<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventory:sync-availability', function () {
    $this->info('Đang đồng bộ số lượng tồn kho khả dụng & hàng tạm giữ...');
    $res = \App\Services\InventoryAvailabilityService::recalculateAndSyncDatabase();
    if ($res['success']) {
        $this->info("Đồng bộ thành công! Đã cập nhật {$res['total_products_reserved']} sản phẩm và {$res['total_warehouses']} kho.");
    } else {
        $this->error("Lỗi: " . ($res['message'] ?? 'Không xác định'));
    }
})->purpose('Đồng bộ số lượng hàng đang tạm giữ (reserved_quantity) và làm mới cache tồn kho khả dụng');

Artisan::command('db:wipe-except-auth {--force : Bỏ qua bước xác nhận cảnh báo}', function () {
    if (!$this->option('force') && !$this->confirm('⚠️ BẠN CÓ CHẮC CHẮN MUỐN XÓA TOÀN BỘ DỮ LIỆU CÁC BẢNG (CHỈ GIỮ LẠI PHÂN QUYỀN VÀ TÀI KHOẢN ADMIN)?')) {
        $this->warn('Đã hủy thao tác.');
        return 0;
    }

    $this->info('🚀 Đang bắt đầu dọn dẹp cơ sở dữ liệu...');

    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    // 1. Tìm tài khoản Admin
    $admin = \App\Models\User::where('code', 'NV-ADMIN')
        ->orWhere('email', 'admin@kbtech.com')
        ->orWhere('id', 1)
        ->first();

    $adminId = $admin ? $admin->id : 1;

    // 2. Xóa các tài khoản khác, giữ lại tài khoản admin
    $deletedUsers = \App\Models\User::where('id', '!=', $adminId)->delete();
    \Illuminate\Support\Facades\DB::table('user_roles')->where('user_id', '!=', $adminId)->delete();
    \Illuminate\Support\Facades\DB::table('user_role_permissions')->where('user_id', '!=', $adminId)->delete();

    $this->info("✓ Đã giữ lại tài khoản Admin (ID: {$adminId}, Code: " . ($admin->code ?? 'NV-ADMIN') . ", Email: " . ($admin->email ?? 'admin@kbtech.com') . ") và xóa {$deletedUsers} tài khoản nhân viên khác.");

    // 3. Các bảng hệ thống và phân quyền cần bảo toàn
    $keepTables = [
        'users',
        'roles',
        'permissions',
        'role_permissions',
        'user_roles',
        'user_role_permissions',
        'migrations',
    ];

    // 4. Lấy danh sách toàn bộ các bảng trong database
    $dbName = config('database.connections.mysql.database');
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    $tableKey = 'Tables_in_' . $dbName;

    $truncatedCount = 0;
    foreach ($tables as $t) {
        $table = $t->$tableKey ?? reset($t);
        if (!in_array($table, $keepTables)) {
            \Illuminate\Support\Facades\DB::table($table)->truncate();
            $truncatedCount++;
            $this->line(" • Đã làm sạch dữ liệu bảng: {$table}");
        }
    }

    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

    $this->info("🎉 Hoàn tất! Đã dọn sạch {$truncatedCount} bảng dữ liệu nghiệp vụ.");
    $this->info("🔒 Phân quyền (roles, permissions, role_permissions) và tài khoản Admin được bảo toàn nguyên vẹn.");

    return 0;
})->purpose('Xóa toàn bộ dữ liệu nghiệp vụ các bảng, chỉ giữ lại bảng phân quyền và tài khoản admin');

Artisan::command('db:import-products {file=Book1.xlsx : Đường dẫn file Excel}', function ($file) {
    $filePath = base_path($file);
    if (!file_exists($filePath)) {
        $this->error("Không tìm thấy file: {$filePath}");
        return 1;
    }

    $this->info("Đang đọc file Excel: {$filePath}...");

    $zip = new \ZipArchive();
    if ($zip->open($filePath) !== true) {
        $this->error("Không thể mở file Excel định dạng .xlsx");
        return 1;
    }

    // 1. Đọc sharedStrings
    $sharedStrings = [];
    if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
        $xml = simplexml_load_string($zip->getFromIndex($index));
        foreach ($xml->si as $si) {
            $t = '';
            if (isset($si->t)) $t = (string)$si->t;
            elseif (isset($si->r)) {
                foreach ($si->r as $r) $t .= (string)$r->t;
            }
            $sharedStrings[] = $t;
        }
    }

    // 2. Đọc sheet1
    $sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml');
    if ($sheetIndex === false) {
        $this->error("Không tìm thấy dữ liệu Sheet1 trong file Excel");
        $zip->close();
        return 1;
    }
    $sheetXml = simplexml_load_string($zip->getFromIndex($sheetIndex));

    $rawProducts = [];
    foreach ($sheetXml->sheetData->row as $row) {
        $rIndex = (int)$row['r'];
        if ($rIndex < 8 || $rIndex > 93) continue;

        $cols = [];
        foreach ($row->c as $c) {
            $cellRef = (string)$c['r'];
            $colLetter = preg_replace('/[0-9]/', '', $cellRef);
            $type = (string)$c['t'];
            $val = (string)$c->v;
            if ($type === 's') $val = $sharedStrings[(int)$val] ?? $val;
            $cols[$colLetter] = trim($val);
        }

        $stt = $cols['A'] ?? '';
        if (!is_numeric($stt)) continue;

        $sku = $cols['C'] ?? ($cols['B'] ?? '');
        $name = $cols['D'] ?? '';
        $unit = $cols['E'] ?? '';
        $spec = $cols['F'] ?? '';
        $price = (float)($cols['H'] ?? 0);
        $priceNpp = (float)($cols['M'] ?? 0);

        if (!empty($name) && !empty($sku)) {
            $rawProducts[] = [
                'stt' => (int)$stt,
                'sku' => $sku,
                'name' => $name,
                'unit' => mb_convert_case($unit, MB_CASE_TITLE, "UTF-8"),
                'specification' => $spec,
                'price' => $price,
                'price_npp' => $priceNpp,
            ];
        }
    }
    $zip->close();

    $this->info("Đã trích xuất thành công " . count($rawProducts) . " sản phẩm từ file Excel.");

    // 3. Đảm bảo Categories & Brands tồn tại
    $catCsrm = \App\Models\Category::firstOrCreate(
        ['code' => 'CAT_CSRM'],
        ['name' => 'Chăm sóc răng miệng', 'manager_name' => 'Ban Giám Đốc', 'is_active' => true]
    );

    $catVspn = \App\Models\Category::firstOrCreate(
        ['code' => 'CAT_VSPN'],
        ['name' => 'Vệ sinh phụ nữ & Cơ thể', 'manager_name' => 'Ban Giám Đốc', 'is_active' => true]
    );

    $catDvcp = \App\Models\Category::firstOrCreate(
        ['code' => 'CAT_DVCP'],
        ['name' => 'Dịch vụ & Chi phí mua hàng', 'manager_name' => 'Ban Giám Đốc', 'is_active' => true]
    );

    $brandNgocChau = \App\Models\Brand::firstOrCreate(
        ['code' => 'BR_NGOCCHAU'],
        ['name' => 'Ngọc Châu', 'country' => 'Việt Nam', 'is_active' => true]
    );

    $brandDaHuong = \App\Models\Brand::firstOrCreate(
        ['code' => 'BR_DAHUONG'],
        ['name' => 'Dạ Hương', 'country' => 'Việt Nam', 'is_active' => true]
    );

    $brandHoaLinh = \App\Models\Brand::firstOrCreate(
        ['code' => 'BR_HOALINH'],
        ['name' => 'Dược Phẩm Hoa Linh', 'country' => 'Việt Nam', 'is_active' => true]
    );

    // Gắn quan hệ category_brands nếu chưa có
    $catCsrm->brands()->syncWithoutDetaching([$brandNgocChau->id, $brandHoaLinh->id]);
    $catVspn->brands()->syncWithoutDetaching([$brandDaHuong->id, $brandHoaLinh->id]);

    // Đảm bảo có ít nhất 1 kho tổng
    $mainWarehouse = \App\Models\Warehouse::firstOrCreate(
        ['code' => 'KHO-TONG'],
        [
            'name' => 'Kho Tổng Mê Linh (MQ)',
            'type' => 'Kho công ty',
            'manager_name' => 'Nguyễn Văn Hùng',
            'address' => 'KCN Quang Minh, Mê Linh, Hà Nội',
            'is_active' => true,
        ]
    );

    // 4. Import / Update từng sản phẩm
    $importedCount = 0;
    foreach ($rawProducts as $p) {
        $skuPrefix = strtoupper(substr($p['sku'], 0, 2));

        // Phân loại Brand
        if (in_array($skuPrefix, ['BC', 'NC']) || str_contains($p['name'], 'Ngọc Châu') || str_contains($p['name'], 'Ngọc châu')) {
            $brandId = $brandNgocChau->id;
        } elseif (in_array($skuPrefix, ['DH', 'VS']) || str_contains($p['name'], 'Dạ Hương') || str_contains($p['name'], 'Dạ hương')) {
            $brandId = $brandDaHuong->id;
        } else {
            $brandId = $brandHoaLinh->id;
        }

        // Phân loại Category
        if (in_array($skuPrefix, ['BC', 'NC', 'GR', 'HB']) || str_contains($p['name'], 'răng') || str_contains($p['name'], 'súc miệng') || str_contains($p['name'], 'Bàn chải')) {
            $categoryId = $catCsrm->id;
        } else {
            $categoryId = $catVspn->id;
        }

        // Price tiers
        $priceNpp = $p['price_npp'] > 0 ? $p['price_npp'] : round($p['price'] * 0.97);
        $priceBuon = round($p['price'] * 0.95);
        $priceTiers = [
            ['name' => 'Giá Nhà Phân Phối / Cấp 1', 'price' => (float)$priceNpp],
            ['name' => 'Giá Bán Buôn / Đại Lý', 'price' => (float)$priceBuon],
            ['name' => 'Giá Bán Lẻ Tiêu Chuẩn', 'price' => (float)$p['price']],
        ];

        // Tách đơn vị quy đổi nếu có (VD: Kiện 200 hộp -> conversion_unit = 'Kiện', conversion_rate = 200)
        $conversionUnit = null;
        $conversionRate = 1;
        if (preg_match('/(Kiện|Thùng)\s+(\d+)/ui', $p['specification'], $matches)) {
            $conversionUnit = $matches[1];
            $conversionRate = (float)$matches[2];
        }

        $product = \App\Models\Product::updateOrCreate(
            ['sku' => $p['sku']],
            [
                'name' => $p['name'],
                'tax_name' => $p['name'],
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'unit' => $p['unit'] ?: 'Chai',
                'specification' => $p['specification'],
                'conversion_unit' => $conversionUnit,
                'conversion_rate' => $conversionRate,
                'price' => $p['price'],
                'cost_price' => $priceNpp,
                'latest_cost_price' => $priceNpp,
                'price_tiers' => $priceTiers,
                'vat_rate' => 8.00,
                'stock_quantity' => 200.00,
                'min_stock_alert' => 20.00,
                'max_stock_alert' => 1000.00,
                'is_active' => true,
            ]
        );

        // Tạo tồn kho ban đầu trong Kho Tổng
        if ($mainWarehouse) {
            \App\Models\WarehouseStock::updateOrCreate(
                [
                    'warehouse_id' => $mainWarehouse->id,
                    'product_id' => $product->id,
                ],
                [
                    'stock_quantity' => 200.00,
                    'reserved_quantity' => 0.00,
                ]
            );
        }

        $importedCount++;
    }

    $this->info("🎉 Đã nhập thành công {$importedCount} sản phẩm vào cơ sở dữ liệu!");
    return 0;
})->purpose('Import danh sách sản phẩm từ file Book1.xlsx');

Artisan::command('db:fix-conversion-prices', function () {
    $products = \App\Models\Product::whereNotNull('conversion_unit')->get();
    $updated = 0;
    foreach ($products as $p) {
        if ($p->isConversionUnitLarger() && (float)$p->conversion_rate > 1) {
            // Nếu conversion_price cũ đang bị chia nhỏ hơn giá đơn vị chuẩn
            if ((float)$p->conversion_price > 0 && (float)$p->conversion_price < (float)$p->price) {
                $p->conversion_price = round((float)$p->price * (float)$p->conversion_rate);
                $p->save();
                $updated++;
            }
        }
    }
    $this->info("Đã cập nhật conversion_price cho {$updated} sản phẩm.");
    return 0;
})->purpose('Sửa lại conversion_price cho các sản phẩm có đơn vị quy đổi lớn hơn (Kiện, Thùng...)');



