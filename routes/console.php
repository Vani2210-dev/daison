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

