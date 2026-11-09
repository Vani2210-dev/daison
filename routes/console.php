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

Artisan::command('db:import-products {file=Book1.xlsx : Đường dẫn file Excel} {--wipe : Xóa sạch toàn bộ sản phẩm và tồn kho cũ}', function ($file) {
    if ($this->option('wipe')) {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        \App\Models\WarehouseStock::truncate();
        \App\Models\Product::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        $this->info("✓ Đã xóa sạch dữ liệu sản phẩm và tồn kho cũ thành công.");
    }

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

    // Đảm bảo lấy đúng kho tổng Mê Linh (MQ)
    $mainWarehouse = \App\Models\Warehouse::where('code', 'KHO-MQ-01')
        ->orWhere('name', 'like', '%Mê Linh%')
        ->first() ?: \App\Models\Warehouse::first();

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

        // Tách đơn vị quy đổi nếu có (VD: Kiện 200 hộp -> conversion_unit = 'Kiện', conversion_rate = 200)
        $conversionUnit = null;
        $conversionRate = 1;
        if (preg_match('/(Kiện|Thùng|Bao)\s+(\d+)/ui', $p['specification'], $matches)) {
            $conversionUnit = $matches[1];
            $conversionRate = (float)$matches[2];
        }

        $conversionPrice = ($conversionUnit && $conversionRate > 1) ? round($p['price'] * $conversionRate) : null;
        $conversionPriceNpp = ($conversionUnit && $conversionRate > 1) ? round($priceNpp * $conversionRate) : null;
        $conversionPriceBuon = ($conversionUnit && $conversionRate > 1) ? round($priceBuon * $conversionRate) : null;

        $priceTiers = [
            ['name' => 'Giá Nhà Phân Phối / Cấp 1', 'price' => (float)$priceNpp, 'conversion_price' => (float)$conversionPriceNpp],
            ['name' => 'Giá Bán Buôn / Đại Lý', 'price' => (float)$priceBuon, 'conversion_price' => (float)$conversionPriceBuon],
            ['name' => 'Giá Bán Lẻ Tiêu Chuẩn', 'price' => (float)$p['price'], 'conversion_price' => (float)$conversionPrice],
        ];

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
                'conversion_price' => $conversionPrice,
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

Artisan::command('db:import-staff {file=Danh sách NV Đại Sơn.xlsx : Đường dẫn file Excel} {--wipe : Xóa sạch tài khoản nhân viên cũ (giữ admin)}', function ($file) {
    $filePath = base_path($file);
    if (!file_exists($filePath)) {
        $this->error("Không tìm thấy file: {$filePath}");
        return 1;
    }

    $zip = new \ZipArchive();
    if ($zip->open($filePath) !== true) {
        $this->error("Không thể mở file Excel định dạng .xlsx");
        return 1;
    }

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

    $sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml');
    if ($sheetIndex === false) {
        $this->error("Không tìm thấy dữ liệu Sheet1");
        $zip->close();
        return 1;
    }
    $sheetXml = simplexml_load_string($zip->getFromIndex($sheetIndex));

    $rawStaff = [];
    foreach ($sheetXml->sheetData->row as $row) {
        $rIndex = (int)$row['r'];
        if ($rIndex < 6) continue;

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
        $name = $cols['B'] ?? '';
        $chucVu = $cols['C'] ?? '';
        $dob = $cols['D'] ?? '';
        $cccd = $cols['E'] ?? '';
        $stk = $cols['F'] ?? '';
        $bank = $cols['G'] ?? '';
        $address = $cols['H'] ?? '';

        if (!empty($name) && is_numeric($stt)) {
            $rawStaff[] = [
                'stt' => (int)$stt,
                'name' => $name,
                'position' => $chucVu,
                'dob' => $dob,
                'cccd' => $cccd,
                'stk' => $stk,
                'bank' => $bank,
                'address' => $address,
            ];
        }
    }
    $zip->close();

    $this->info("Đã trích xuất " . count($rawStaff) . " nhân viên từ file Excel.");

    if ($this->option('wipe')) {
        $admin = \App\Models\User::where('code', 'NV-ADMIN')->orWhere('email', 'admin@kbtech.com')->first();
        $adminId = $admin ? $admin->id : 1;
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        \App\Models\User::where('id', '!=', $adminId)->delete();
        \Illuminate\Support\Facades\DB::table('user_roles')->where('user_id', '!=', $adminId)->delete();
        \Illuminate\Support\Facades\DB::table('user_role_permissions')->where('user_id', '!=', $adminId)->delete();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        $this->info("✓ Đã dọn dẹp các tài khoản nhân viên cũ (giữ lại tài khoản Admin ID: {$adminId}).");
    }

    // Helper convert date
    $parseDate = function ($dateVal) {
        if (empty($dateVal)) return null;
        if (str_contains($dateVal, '/')) {
            $parts = explode('/', $dateVal);
            if (count($parts) === 3) {
                return sprintf('%04d-%02d-%02d', (int)$parts[2], (int)$parts[1], (int)$parts[0]);
            }
        }
        if (is_numeric($dateVal)) {
            $ts = ($dateVal - 25569) * 86400;
            return gmdate('Y-m-d', $ts);
        }
        return null;
    };

    // Helper email
    $makeEmail = function ($fullName) {
        $translit = [
            'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a',
            'ă'=>'a','ằ'=>'a','ắ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a',
            'â'=>'a','ầ'=>'a','ấ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
            'đ'=>'d',
            'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e',
            'ê'=>'e','ề'=>'e','ế'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
            'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
            'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o',
            'ô'=>'o','ồ'=>'o','ố'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o',
            'ơ'=>'o','ờ'=>'o','ớ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
            'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
            'ư'=>'u','ừ'=>'u','ứ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
            'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y'
        ];
        $clean = mb_strtolower(trim($fullName));
        $clean = strtr($clean, $translit);
        $clean = preg_replace('/[^a-z\s]/', '', $clean);
        $words = array_values(array_filter(explode(' ', $clean)));
        if (empty($words)) return 'staff.' . uniqid() . '@daison.vn';
        $lastName = end($words);
        $initials = '';
        for ($i = 0; $i < count($words) - 1; $i++) {
            $initials .= substr($words[$i], 0, 1);
        }
        return $lastName . ($initials ? '.' . $initials : '') . '@daison.vn';
    };

    $roles = \App\Models\Role::all()->keyBy('slug');
    $brandHoaLinh = \App\Models\Brand::where('code', 'BR_HOALINH')->first();

    $imported = 0;
    foreach ($rawStaff as $s) {
        $pos = mb_strtolower($s['position']);
        $isBrand = str_contains($pos, 'hoa linh') || str_contains($pos, 'hãng');
        $staffType = $isBrand ? 'brand' : 'company';

        $department = 'Sales';
        $roleSlug = 'Sales_Rep';
        $assignedRoleSlugs = ['Sales_Rep'];

        if (str_contains($pos, 'giám đốc')) {
            $department = 'Executive';
            $assignedRoleSlugs = ['Manager'];
        } elseif (str_contains($pos, 'kế toán')) {
            $department = 'Accounting';
            $assignedRoleSlugs = ['Sales_Accountant', 'Finance_Accountant'];
        } elseif (str_contains($pos, 'thủ quỹ')) {
            $department = 'Accounting';
            $assignedRoleSlugs = ['Finance_Accountant'];
        } elseif (str_contains($pos, 'thủ kho') || str_contains($pos, 'kho')) {
            $department = 'Warehouse';
            $assignedRoleSlugs = ['Warehouse_Manager'];
        } elseif (str_contains($pos, 'giao hàng') || str_contains($pos, 'lái xe')) {
            $department = 'Logistics';
            $assignedRoleSlugs = ['Driver'];
        }

        $code = sprintf('NV-%03d', $s['stt']);
        $supplierId = null;
        if ($isBrand) {
            $code = sprintf('NV-HL%02d', $s['stt'] - 8);
            $supplierHoaLinh = \App\Models\Supplier::where('code', 'NCC-HL')->orWhere('name', 'like', '%Hoa Linh%')->first();
            $supplierId = $supplierHoaLinh ? $supplierHoaLinh->id : 3;
        }

        $email = $makeEmail($s['name']);
        // Tránh trùng email
        $existing = \App\Models\User::where('email', $email)->where('code', '!=', $code)->first();
        if ($existing) {
            $email = str_replace('@daison.vn', $s['stt'] . '@daison.vn', $email);
        }

        $user = \App\Models\User::updateOrCreate(
            ['code' => $code],
            [
                'name' => $s['name'],
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'department' => $department,
                'staff_type' => $staffType,
                'supplier_id' => $supplierId,
                'status' => 'Đang làm việc',
                'dob' => $parseDate($s['dob']),
                'citizen_id' => $s['cccd'] ?: null,
                'bank_account' => $s['stk'] ?: null,
                'bank_name' => $s['bank'] ?: null,
                'address' => $s['address'] ?: null,
                'branch' => 'Công ty chính (Nghệ An)',
                'credit_limit' => 50000000.00,
                'current_debt' => 0.00,
                'annual_leave_days' => 12,
                'remaining_leave_days' => 12.0,
            ]
        );

        // Gán Roles
        $roleIds = [];
        foreach ($assignedRoleSlugs as $slug) {
            if (isset($roles[$slug])) {
                $roleIds[] = $roles[$slug]->id;
            }
        }
        $user->roles()->sync($roleIds);

        // Gán permissions chi tiết từ roles
        \Illuminate\Support\Facades\DB::table('user_role_permissions')->where('user_id', $user->id)->delete();
        $perms = \Illuminate\Support\Facades\DB::table('role_permissions')
            ->whereIn('role_id', $roleIds)
            ->get();
        $userRolePermData = [];
        foreach ($perms as $p) {
            $userRolePermData[] = [
                'user_id' => $user->id,
                'role_id' => $p->role_id,
                'permission_id' => $p->permission_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($userRolePermData)) {
            \Illuminate\Support\Facades\DB::table('user_role_permissions')->insert($userRolePermData);
        }

        $roleNames = implode(', ', $assignedRoleSlugs);
        $typeLabel = $isBrand ? '🏪 Hãng (Hoa Linh)' : '🏢 Công ty';
        $this->line(" • [{$code}] {$s['name']} | {$typeLabel} | CV: {$s['position']} | Đăng nhập: {$email} (pass: password123)");
        $imported++;
    }

    $this->info("🎉 Hoàn tất! Đã nhập thành công {$imported} nhân viên vào hệ thống.");
    return 0;
})->purpose('Nhập danh sách cán bộ nhân viên từ file Danh sách NV Đại Sơn.xlsx');

Artisan::command('db:import-customers {file=khachhang1.xlsx : Đường dẫn file Excel} {--wipe : Xóa sạch danh sách khách hàng cũ}', function ($file) {
    if ($this->option('wipe')) {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        \App\Models\Dealer::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        $this->info("✓ Đã xóa sạch danh sách khách hàng cũ.");
    }

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
    if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
        $xml = simplexml_load_string($zip->getFromIndex($idx));
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
    $sheetIdx = $zip->locateName('xl/worksheets/sheet1.xml');
    if ($sheetIdx === false) {
        $this->error("Không tìm thấy dữ liệu Sheet1 trong file Excel");
        $zip->close();
        return 1;
    }
    $sheetXml = simplexml_load_string($zip->getFromIndex($sheetIdx));

    // Cache sales reps
    $users = \App\Models\User::all();
    $usersByName = [];
    foreach ($users as $u) {
        $usersByName[mb_strtolower(trim($u->name))] = $u->id;
    }

    $imported = 0;
    foreach ($sheetXml->sheetData->row as $row) {
        $rIndex = (int)$row['r'];
        if ($rIndex === 1) continue; // Skip header

        $cells = [];
        foreach ($row->c as $c) {
            $ref = (string)$c['r'];
            $colLetter = preg_replace('/[0-9]/', '', $ref);
            $val = '';
            if (isset($c->v)) {
                $raw = (string)$c->v;
                $val = ((string)$c['t'] === 's') ? ($sharedStrings[(int)$raw] ?? '') : $raw;
            } elseif (isset($c->is->t)) {
                $val = (string)$c->is->t;
            }
            $cells[$colLetter] = trim($val);
        }

        $code = $cells['B'] ?? '';
        $name = $cells['C'] ?? '';
        if (!$code || !$name) continue;

        $phone = $cells['E'] ?? ($cells['F'] ?? '');
        $owner = $cells['K'] ?: $name;
        $taxCode = $cells['G'] ?: null;
        $referrer = $cells['Y'] ?: null;
        $address = $cells['Z'] ?: ($cells['AI'] ?? '');
        $district = $cells['AD'] ?: ($cells['AC'] ?? '');
        $city = 'Nghệ An';

        $classification = trim($cells['AN'] ?? ($cells['AL'] ?? ''));
        $tier = $classification ?: 'GT - Khách hàng Lẻ nhỏ';

        $lat = (isset($cells['AF']) && is_numeric($cells['AF'])) ? (float)$cells['AF'] : null;
        $lng = (isset($cells['AG']) && is_numeric($cells['AG'])) ? (float)$cells['AG'] : null;
        $creditLimit = (isset($cells['R']) && is_numeric($cells['R']) && (float)$cells['R'] > 0) ? (float)$cells['R'] : 50000000;
        $paymentTermDays = (isset($cells['T']) && is_numeric($cells['T']) && (int)$cells['T'] > 0) ? (int)$cells['T'] : 30;

        $repName = mb_strtolower(trim($cells['AY'] ?? ''));
        $salesRepId = $usersByName[$repName] ?? null;

        $notes = [];
        if (!empty($cells['AI'])) $notes[] = "Mô tả: " . $cells['AI'];
        if (!empty($cells['AN'])) $notes[] = "Phân loại: " . $cells['AN'];
        if (!empty($cells['AH'])) $notes[] = "ĐC tọa độ: " . $cells['AH'];
        if (!empty($cells['AY'])) $notes[] = "NV phụ trách: " . $cells['AY'] . (!empty($cells['AX']) ? " ({$cells['AX']})" : '');
        $expansionNotes = implode(' | ', $notes);

        \App\Models\Dealer::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'owner_name' => $owner,
                'phone' => $phone,
                'tax_code' => $taxCode,
                'referrer' => $referrer,
                'address' => $address,
                'district' => $district,
                'city' => $city,
                'old_ward' => $district,
                'old_district' => $district,
                'old_city' => $city,
                'new_ward' => $district,
                'new_district' => $district,
                'new_city' => $city,
                'tier' => $tier,
                'lat' => $lat,
                'lng' => $lng,
                'credit_limit' => $creditLimit,
                'initial_debt' => 0,
                'current_debt' => 0,
                'payment_term_days' => $paymentTermDays,
                'overdue_days' => 0,
                'sales_rep_id' => $salesRepId,
                'status' => 'Hoạt động',
                'truck_accessible' => true,
                'expansion_notes' => $expansionNotes,
            ]
        );
        $imported++;
    }

    $zip->close();
    $this->info("🎉 Hoàn tất! Đã cập nhật thành công {$imported} khách hàng vào hệ thống.");
    return 0;
})->purpose('Nhập danh sách khách hàng từ file khachhang1.xlsx');




