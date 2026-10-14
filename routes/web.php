<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PickingSyncController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StaffGroupController;
use App\Http\Controllers\BankingWebhookController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\DebtHandoverController;
use App\Http\Controllers\WarehouseTransactionController;
use App\Http\Controllers\WaterFilterController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::prefix('api')->group(function () {
    // Geo / Administrative Boundaries APIs
    Route::get('/geo/provinces-old', [GeoController::class, 'getOldProvinces']);
    Route::get('/geo/districts-old', [GeoController::class, 'getOldDistricts']);
    Route::get('/geo/wards-old', [GeoController::class, 'getOldWards']);
    Route::get('/geo/provinces-new', [GeoController::class, 'getNewProvinces']);
    Route::get('/geo/wards-new', [GeoController::class, 'getNewWards']);

    // 1. Authentication & Password Reset
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/forgot-password/send-otp', [AuthController::class, 'sendResetOtp']);
    Route::post('/auth/forgot-password/verify-otp', [AuthController::class, 'verifyResetOtp']);
    Route::post('/auth/forgot-password/reset-password', [AuthController::class, 'resetPassword']);

    // 2. Orders & Sales
    Route::get('/orders', [OrderController::class, 'getOrders'])->middleware('permission:orders.view');
    Route::post('/orders/save', [OrderController::class, 'saveOrder'])->middleware('permission:orders.create,orders.edit');
    Route::post('/orders/update-status', [OrderController::class, 'updateOrderStatus'])->middleware('permission:orders.status,orders.approve,orders.edit,inv_picking.pick,inv_picking.qc,delivery_dispatch.dispatch');
    Route::get('/orders/pending-debts-and-borrows', [OrderController::class, 'getPendingDebtsAndBorrows'])->middleware('permission:orders.view,orders.create,orders.edit');
    Route::post('/orders/mark-merged', [OrderController::class, 'markOrdersMerged'])->middleware('permission:orders.edit,orders.create');
    Route::get('/orders/{id}/print-data', [OrderController::class, 'getOrderPrintData'])->middleware('permission:orders.view');
    // Credit overrun approval workflow (lập đơn vượt hạn mức)
    Route::get('/dealers/{id}/credit-info', [OrderController::class, 'getDealerCreditInfo'])->middleware('permission:orders.view,dealers.view');
    Route::post('/orders/{id}/approve-credit-overrun', [OrderController::class, 'approveCreditOverrunAtCreation'])->middleware('permission:orders.approve,orders.edit,debt.approve');

    // 3. Inventory, Products, Categories & Brands
    Route::post('/products', [InventoryController::class, 'saveProduct'])->middleware('permission:inventory.update_price,danhmuc.manage');
    Route::delete('/products/{id}', [InventoryController::class, 'deleteProduct'])->middleware('permission:danhmuc.manage');
    Route::post('/categories', [InventoryController::class, 'saveCategory'])->middleware('permission:danhmuc.manage');
    Route::delete('/categories/{id}', [InventoryController::class, 'deleteCategory'])->middleware('permission:danhmuc.manage');
    Route::post('/brands', [InventoryController::class, 'saveBrand'])->middleware('permission:danhmuc.manage');
    Route::delete('/brands/{id}', [InventoryController::class, 'deleteBrand'])->middleware('permission:danhmuc.manage');
    Route::get('/branches', [InventoryController::class, 'getBranches']);
    Route::post('/branches', [InventoryController::class, 'saveBranch'])->middleware('permission:danhmuc.manage');
    Route::delete('/branches/{id}', [InventoryController::class, 'deleteBranch'])->middleware('permission:danhmuc.manage');
    Route::get('/warehouses', [InventoryController::class, 'getWarehouses']);
    Route::post('/warehouses', [InventoryController::class, 'saveWarehouse'])->middleware('permission:danhmuc.manage');
    Route::delete('/warehouses/{id}', [InventoryController::class, 'deleteWarehouse'])->middleware('permission:danhmuc.manage');
    Route::post('/inventory/transactions', [InventoryController::class, 'saveInventoryTransaction'])->middleware('permission:inv_import.create,inv_export.create,inv_transfer.create');
    Route::get('/inventory/picking/stream', [PickingSyncController::class, 'stream']);
    Route::get('/inventory/picking/events', [PickingSyncController::class, 'getEvents']);
    Route::get('/inventory/picking/latest-state', [PickingSyncController::class, 'getLatestState']);
    Route::post('/inventory/picking/update-item', [PickingSyncController::class, 'updateItem']);
    Route::post('/inventory/picking/report-shortage', [PickingSyncController::class, 'reportShortage']);

    // 4. Delivery & Logistics Dispatch
    Route::get('/delivery-dispatches', [DeliveryController::class, 'getDeliveryDispatches'])->middleware('permission:delivery_dispatch.view');
    Route::post('/delivery-dispatches', [DeliveryController::class, 'saveDeliveryDispatch'])->middleware('permission:delivery_dispatch.assign');
    Route::post('/delivery-dispatches/update-stop', [DeliveryController::class, 'updateDeliveryStop'])->middleware('permission:delivery_dispatch.dispatch');
    Route::post('/delivery-dispatches/settle-cash', [DeliveryController::class, 'settleDriverCash']);
    Route::get('/delivery-dispatches/driver-cash-handovers', [DeliveryController::class, 'getDriverCashHandovers']);
    Route::post('/vehicles/update-status', [DeliveryController::class, 'updateVehicleStatus']);
    Route::post('/vehicles/save', [DeliveryController::class, 'saveVehicle'])->middleware('permission:fleet.manage,danhmuc.manage');
    Route::get('/delivery-routes', [DeliveryController::class, 'getDeliveryRoutes'])->middleware('permission:delivery_dispatch.view,danhmuc.view');
    Route::post('/delivery-routes', [DeliveryController::class, 'saveDeliveryRoute'])->middleware('permission:danhmuc.manage');
    Route::post('/delivery-routes/toggle', [DeliveryController::class, 'toggleDeliveryRoute'])->middleware('permission:danhmuc.manage');
    Route::delete('/delivery-routes/{id}', [DeliveryController::class, 'deleteDeliveryRoute'])->middleware('permission:danhmuc.manage');
    Route::get('/vehicle-assignments', [DeliveryController::class, 'getVehicleAssignments'])->middleware('permission:delivery_dispatch.view,fleet.view,danhmuc.view');
    Route::post('/vehicle-assignments/save', [DeliveryController::class, 'saveVehicleAssignment'])->middleware('permission:delivery_dispatch.assign,fleet.manage,danhmuc.manage');
    Route::post('/vehicle-assignments/terminate', [DeliveryController::class, 'terminateVehicleAssignment'])->middleware('permission:delivery_dispatch.assign,fleet.manage,danhmuc.manage');
    Route::delete('/vehicle-assignments/{id}', [DeliveryController::class, 'deleteVehicleAssignment'])->middleware('permission:delivery_dispatch.assign,fleet.manage,danhmuc.manage');
    Route::get('/delivery-returns', [DeliveryController::class, 'getDeliveryReturns']);
    Route::post('/delivery-returns/save', [DeliveryController::class, 'saveDeliveryReturn']);
    Route::post('/delivery-returns/intake-warehouse', [DeliveryController::class, 'intakeReturnWarehouse']);
    Route::post('/delivery/return-order-to-warehouse', [DeliveryController::class, 'returnOrderToWarehouse']);
    Route::post('/delivery/transfer-order-vehicle', [DeliveryController::class, 'transferOrderVehicle']);
    Route::post('/delivery/release-completed-orders', [DeliveryController::class, 'releaseCompletedOrders']);
    Route::post('/delivery/depart', [DeliveryController::class, 'departTrip'])->middleware('permission:delivery_dispatch.dispatch,delivery_dispatch.assign');
    Route::post('/delivery/complete-order', [DeliveryController::class, 'completeDeliveryOrder'])->middleware('permission:delivery_dispatch.dispatch,delivery_dispatch.view');
    Route::post('/delivery/fail-order', [DeliveryController::class, 'failDeliveryOrder'])->middleware('permission:delivery_dispatch.dispatch,delivery_dispatch.view');

    // 5. Customers & Dealers
    Route::get('/dealers', [DealerController::class, 'getDealers'])->middleware('permission:dealers.view');
    Route::post('/dealers/save', [DealerController::class, 'saveDealer'])->middleware('permission:dealers.create,dealers.edit');
    Route::post('/dealers/update-debt', [DealerController::class, 'updateDebt']);
    Route::delete('/dealers/{id}', [DealerController::class, 'deleteDealer'])->middleware('permission:dealers.edit');

    // 5.0 Bàn Giao Công Nợ & Hàng Lẻ Lưu Động
    Route::get('/debt-handovers', [DebtHandoverController::class, 'getDebtHandovers']);
    Route::post('/debt-handovers', [DebtHandoverController::class, 'createDebtHandover']);
    Route::post('/debt-handovers/{id}/confirm', [DebtHandoverController::class, 'confirmDebtHandover']);
    Route::post('/debt-handovers/{id}/reject', [DebtHandoverController::class, 'rejectDebtHandover']);
    Route::get('/staff-debts', [DebtHandoverController::class, 'getStaffDebtList']);
    Route::get('/van-sale-dispatches', [DebtHandoverController::class, 'getVanSaleDispatches']);
    Route::post('/van-sale-dispatches', [DebtHandoverController::class, 'createVanSaleDispatch']);

    // 5.1 Banking & VietQR Webhooks (Tự động trừ công nợ khách hàng khi nhận tiền)
    Route::post('/banking/webhook', [BankingWebhookController::class, 'handleIncomingPayment']);
    Route::post('/banking/receive-payment', [BankingWebhookController::class, 'handleIncomingPayment']);
    Route::post('/banking/confirm-order-payment', [BankingWebhookController::class, 'checkOrConfirmOrderPayment']);

    // 6. Roles & Permissions
    Route::get('/roles', [RoleController::class, 'getRoles'])->middleware('permission:permissions.view');
    Route::post('/roles/save-permissions', [RoleController::class, 'saveRolePermissions'])->middleware('permission:permissions.manage_role');

    // 7. Users / Staff Management & Affiliates
    Route::get('/users', [UserController::class, 'getUsers'])->middleware('permission:staff_list.view');
    Route::post('/users/save', [UserController::class, 'saveUser'])->middleware('permission:staff_list.create,staff_list.edit');
    Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->middleware('permission:staff_list.lock');
    Route::post('/users/save-permissions', [RoleController::class, 'saveUserPermissions'])->middleware('permission:permissions.manage_user');
    Route::post('/users/{id}/reset-permissions', [RoleController::class, 'resetUserPermissions'])->middleware('permission:permissions.manage_user');
    Route::get('/affiliates', [UserController::class, 'getAffiliates'])->middleware('permission:staff_list.view');

    // 8. Attendance (GPS Check-in/out)
    Route::get('/attendance', [AttendanceController::class, 'getAttendance'])->middleware('permission:attendance.view');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->middleware('permission:attendance.checkin');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->middleware('permission:attendance.checkin');

    // 9. KPI & Performance
    Route::get('/kpi', [KpiController::class, 'getKpi'])->middleware('permission:kpi.view');
    Route::get('/kpi/departments', [KpiController::class, 'getDepartmentKpis'])->middleware('permission:kpi.view');
    Route::post('/kpi/save', [KpiController::class, 'saveEvaluation'])->middleware('permission:kpi.evaluate');

    // 10. Finance & Transactions
    Route::get('/transactions', [FinanceController::class, 'getTransactions'])->middleware('permission:funds.view');
    Route::post('/transactions/save', [FinanceController::class, 'saveTransaction']);

    // 11. Tasks & Work Management
    Route::get('/tasks', [TaskController::class, 'getTasks'])->middleware('permission:task_assignment.view');

    // 12. Payroll
    Route::get('/payroll', [PayrollController::class, 'getPayroll'])->middleware('permission:payroll.view');

    // 13. Proposals & Approvals
    Route::get('/proposals', [ProposalController::class, 'getProposals'])->middleware('permission:proposals.view');

    // 14. Audit Logs & Period Locks
    Route::get('/audit-logs', [AuditController::class, 'getAuditLogs'])->middleware('permission:audit.view');
    Route::post('/period-locks/toggle', [AuditController::class, 'togglePeriodLock'])->middleware('permission:audit.lock_period');

    // 15. Announcements
    Route::get('/announcements', [AnnouncementController::class, 'getAnnouncements']);
    Route::post('/announcements/save', [AnnouncementController::class, 'saveAnnouncement'])->middleware('permission:staff_list.create');

    // 16. Policy Preservations
    Route::get('/policies', [PolicyController::class, 'getPolicies'])->middleware('permission:sales_policy.view');
    Route::post('/policies/save', [PolicyController::class, 'savePolicy'])->middleware('permission:sales_policy.create,sales_policy.edit');

    // 17. Sales Policies (Chính sách bán hàng)
    Route::get('/sales-policies', [\App\Http\Controllers\SalesPolicyController::class, 'index'])->middleware('permission:sales_policy.view');
    Route::post('/sales-policies', [\App\Http\Controllers\SalesPolicyController::class, 'store'])->middleware('permission:sales_policy.create');
    Route::put('/sales-policies/{id}', [\App\Http\Controllers\SalesPolicyController::class, 'update'])->middleware('permission:sales_policy.edit');
    // 18. Notifications & Floating Alerts
    Route::get('/notifications/poll', [NotificationController::class, 'poll']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

// Non-prefixed aliases
Route::get('/notifications/poll', [NotificationController::class, 'poll']);
Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::post('/delivery-dispatches/settle-cash', [DeliveryController::class, 'settleDriverCash']);
Route::get('/delivery-dispatches/driver-cash-handovers', [DeliveryController::class, 'getDriverCashHandovers']);
Route::get('/delivery-returns', [DeliveryController::class, 'getDeliveryReturns']);
Route::post('/delivery-returns/intake-warehouse', [DeliveryController::class, 'intakeReturnWarehouse']);
Route::post('/roles/save-permissions', [RoleController::class, 'saveRolePermissions']);
Route::post('/debt-handovers/{id}/confirm', [DebtHandoverController::class, 'confirmDebtHandover']);
Route::post('/debt-handovers/{id}/reject', [DebtHandoverController::class, 'rejectDebtHandover']);
Route::post('/debt-handovers', [DebtHandoverController::class, 'createDebtHandover']);
Route::get('/debt-handovers', [DebtHandoverController::class, 'getDebtHandovers']);
Route::get('/staff-debts', [DebtHandoverController::class, 'getStaffDebtList']);
Route::post('/van-sale-dispatches', [DebtHandoverController::class, 'createVanSaleDispatch']);
Route::get('/van-sale-dispatches', [DebtHandoverController::class, 'getVanSaleDispatches']);

use App\Http\Controllers\WebViewController;

// Public QR Scan & Machine Activation / Warranty Portal
Route::get('/m/{token}', [WaterFilterController::class, 'scanQrMachine'])->name('water_filter.qr_scan');
Route::post('/m/{token}/activate', [WaterFilterController::class, 'submitActivationRequest'])->name('water_filter.qr_activate');
Route::post('/m/{token}/request-service', [WaterFilterController::class, 'customerRequestService'])->name('water_filter.qr_service');

// Web Authentication Routes (Blade)
Route::get('/login', [WebViewController::class, 'loginView'])->name('login');
Route::post('/login', [WebViewController::class, 'webLogin']);
Route::match(['get', 'post'], '/logout', [WebViewController::class, 'webLogout'])->name('logout');

// Authenticated Web Application Routes (Blade)
Route::middleware('auth')->group(function () {
    Route::get('/', [WebViewController::class, 'dashboard'])->name('home');
    Route::get('/dashboard', [WebViewController::class, 'dashboard'])->name('dashboard');

    // Products & Pricing Catalog (Dedicated create/edit pages, not modal)
    Route::get('/cat_products', [WebViewController::class, 'products'])->name('products.index')->middleware('permission:inventory.view');
    Route::get('/products', [WebViewController::class, 'products'])->middleware('permission:inventory.view');
    Route::get('/inventory', [WebViewController::class, 'products'])->name('inventory.index')->middleware('permission:inventory.view');
    Route::get('/cat_products/create', [WebViewController::class, 'productCreate'])->name('products.create')->middleware('permission:inventory.create');
    Route::get('/products/create', [WebViewController::class, 'productCreate'])->middleware('permission:inventory.create');
    Route::get('/cat_products/{id}/edit', [WebViewController::class, 'productEdit'])->name('products.edit')->middleware('permission:inventory.edit');
    Route::get('/products/{id}/edit', [WebViewController::class, 'productEdit'])->middleware('permission:inventory.edit');
    Route::post('/cat_products/save', [WebViewController::class, 'productSave'])->name('products.save')->middleware('permission:inventory.create,inventory.edit');
    Route::post('/products/save', [WebViewController::class, 'productSave'])->middleware('permission:inventory.create,inventory.edit');
    Route::delete('/cat_products/{id}', [WebViewController::class, 'productDelete'])->name('products.delete')->middleware('permission:inventory.delete');
    Route::post('/api/products/import-excel', [WebViewController::class, 'importProductsExcel'])->middleware('permission:inventory.create,inventory.edit');
    Route::get('/api/products/template-excel', [WebViewController::class, 'downloadProductsTemplate'])->middleware('permission:inventory.view');

    // Orders & Quoting & COD Verification
    Route::get('/orders', [WebViewController::class, 'orders'])->name('orders.index')->middleware('permission:orders.view');
    Route::get('/orders/create', [WebViewController::class, 'orderCreate'])->name('orders.create')->middleware('permission:orders.create,quotation.create');
    Route::get('/orders/cod-verification', [DeliveryController::class, 'codVerificationPage'])->name('orders.cod_verification')->middleware('permission:orders.view,delivery_dispatch.view,financial.view');
    Route::get('/sales/cod-verification', [DeliveryController::class, 'codVerificationPage']);
    Route::get('/cod-verification', [DeliveryController::class, 'codVerificationPage']);
    Route::get('/orders/{id}/edit', [WebViewController::class, 'orderEdit'])->name('orders.edit')->middleware('permission:orders.edit');

    // Dealers & Customers Management
    Route::get('/dealers', [WebViewController::class, 'dealers'])->name('dealers.index')->middleware('permission:dealers.view');
    Route::get('/dealers/create', [WebViewController::class, 'dealerCreate'])->name('dealers.create')->middleware('permission:dealers.create');
    Route::get('/dealers/{id}/edit', [WebViewController::class, 'dealerEdit'])->name('dealers.edit')->middleware('permission:dealers.edit');
    Route::post('/dealers/save', [WebViewController::class, 'dealerSave'])->name('dealers.save')->middleware('permission:dealers.create,dealers.edit');
    Route::delete('/dealers/{id}', [WebViewController::class, 'dealerDelete'])->name('dealers.delete')->middleware('permission:dealers.delete,dealers.edit');

    // Delivery Dispatch & Logistics
    Route::get('/delivery-dispatch', [WebViewController::class, 'deliveryDispatch'])->name('delivery.dispatch')->middleware('permission:delivery_dispatch.view');
    Route::get('/driver-portal', [DeliveryController::class, 'deliveryExecution'])->name('driver.portal')->middleware('permission:delivery_dispatch.view,fleet.view');
    Route::get('/delivery-execution', [DeliveryController::class, 'deliveryExecution'])->name('delivery.execution')->middleware('permission:delivery_dispatch.view,fleet.view');
    Route::get('/delivery/orders/{id}/confirm', [DeliveryController::class, 'confirmDeliveryPage'])->name('delivery.confirm')->middleware('permission:delivery_dispatch.view,fleet.view');
    Route::get('/delivery-orders/{id}/confirm', [DeliveryController::class, 'confirmDeliveryPage'])->middleware('permission:delivery_dispatch.view,fleet.view');
    Route::post('/delivery/transfer-order-vehicle', [DeliveryController::class, 'transferOrderVehicle'])->middleware('permission:delivery_dispatch.assign,delivery_dispatch.dispatch');
    Route::post('/delivery/release-completed-orders', [DeliveryController::class, 'releaseCompletedOrders'])->middleware('permission:delivery_dispatch.assign,delivery_dispatch.dispatch');
    Route::post('/vehicles/update-status', [DeliveryController::class, 'updateVehicleStatus'])->middleware('permission:delivery_dispatch.dispatch,fleet.manage');
    Route::post('/delivery/depart', [DeliveryController::class, 'departTrip'])->middleware('permission:delivery_dispatch.dispatch,delivery_dispatch.assign');
    Route::post('/delivery/complete-order', [DeliveryController::class, 'completeDeliveryOrder'])->middleware('permission:delivery_dispatch.dispatch,delivery_dispatch.view');
    Route::post('/delivery/fail-order', [DeliveryController::class, 'failDeliveryOrder'])->middleware('permission:delivery_dispatch.dispatch,delivery_dispatch.view');
    Route::post('/delivery/orders/{id}/request-credit-override', [DeliveryController::class, 'requestCreditOverride']);
    Route::post('/delivery/orders/{id}/approve-credit-override', [DeliveryController::class, 'approveCreditOverride']);
    Route::get('/delivery/orders/{id}/credit-override-status', [DeliveryController::class, 'getCreditOverrideStatus']);
    Route::get('/delivery/orders/pending-credit-overrides', [DeliveryController::class, 'getPendingCreditOverrides']);
    Route::get('/api/delivery/orders/pending-credit-overrides', [DeliveryController::class, 'getPendingCreditOverrides']);
    Route::post('/orders/update-status', [OrderController::class, 'updateOrderStatus'])->middleware('permission:orders.status,orders.approve,orders.edit,inv_picking.pick,inv_picking.qc,delivery_dispatch.dispatch');

    // Inventory Picking & Checking (Soạn đơn & Kiểm hàng xuất kho)
    Route::get('/inventory/picking', [PickingSyncController::class, 'pickingView'])->name('inventory.picking')->middleware('permission:inv_picking.view,inv_qc.view');
    Route::get('/picking', [PickingSyncController::class, 'pickingView'])->middleware('permission:inv_picking.view,inv_qc.view');
    Route::get('/orders/picking', [PickingSyncController::class, 'pickingView'])->middleware('permission:inv_picking.view,inv_qc.view');

    // 1. Quản lý Phiếu Nhập kho (Goods Receipt - PNK)
    Route::get('/inventory/receipts', [WarehouseTransactionController::class, 'receipts'])->name('inventory.receipts')->middleware('permission:inventory.view,inv_import.view');
    Route::get('/warehouse/receipts', [WarehouseTransactionController::class, 'receipts']);
    Route::get('/inventory/receipts/create', [WarehouseTransactionController::class, 'createReceipt'])->name('inventory.receipts.create')->middleware('permission:inv_import.view,inventory.create');
    Route::post('/inventory/receipts', [WarehouseTransactionController::class, 'storeReceipt'])->name('inventory.receipts.store')->middleware('permission:inv_import.view,inventory.create');

    // 2. Quản lý Phiếu Xuất kho (Goods Issue - PXK)
    Route::get('/inventory/issues', [WarehouseTransactionController::class, 'issues'])->name('inventory.issues')->middleware('permission:inventory.view,inv_export.view');
    Route::get('/warehouse/issues', [WarehouseTransactionController::class, 'issues']);
    Route::get('/inventory/issues/create', [WarehouseTransactionController::class, 'createIssue'])->name('inventory.issues.create')->middleware('permission:inv_export.view,inventory.create');
    Route::post('/inventory/issues', [WarehouseTransactionController::class, 'storeIssue'])->name('inventory.issues.store')->middleware('permission:inv_export.view,inventory.create');

    // 3. Chi tiết & In ấn & Hủy phiếu kho
    Route::get('/inventory/transactions/{id}', [WarehouseTransactionController::class, 'show'])->name('inventory.transactions.show')->middleware('permission:inventory.view');
    Route::get('/inventory/transactions/{id}/print', [WarehouseTransactionController::class, 'print'])->name('inventory.transactions.print')->middleware('permission:inventory.view');
    Route::post('/inventory/transactions/{id}/cancel', [WarehouseTransactionController::class, 'cancel'])->name('inventory.transactions.cancel')->middleware('permission:inventory.delete,inventory.edit');
    Route::get('/api/inventory/search-products', [WarehouseTransactionController::class, 'searchProducts']);

    // 4. Warehouse Returns Intake (Hoàn kho từ xe giao hàng)
    Route::get('/inventory/import', [DeliveryController::class, 'returnsIntakePage'])->name('inventory.import')->middleware('permission:inventory.view,inv_import.view,returns.view');
    Route::get('/warehouse/import', [DeliveryController::class, 'returnsIntakePage']);
    Route::get('/returns/intake', [DeliveryController::class, 'returnsIntakePage']);
    Route::get('/warehouse/returns-intake', [DeliveryController::class, 'returnsIntakePage']);

    // Financial Funds & Transactions
    Route::get('/funds', [WebViewController::class, 'funds'])->name('funds.index')->middleware('permission:funds.view');
    Route::get('/transactions', [WebViewController::class, 'funds'])->middleware('permission:funds.view');

    // Staff & Payroll
    Route::get('/staff', [WebViewController::class, 'staff'])->name('staff.index')->middleware('permission:staff_list.view');
    Route::get('/users', [WebViewController::class, 'staff'])->middleware('permission:staff_list.view');
    Route::get('/staff/create', [WebViewController::class, 'staffCreate'])->name('staff.create')->middleware('permission:staff_list.create');
    Route::get('/users/create', [WebViewController::class, 'staffCreate'])->middleware('permission:staff_list.create');
    Route::get('/staff/{id}/edit', [WebViewController::class, 'staffEdit'])->name('staff.edit')->middleware('permission:staff_list.edit');
    Route::get('/users/{id}/edit', [WebViewController::class, 'staffEdit'])->middleware('permission:staff_list.edit');
    Route::post('/staff/save', [WebViewController::class, 'staffSave'])->name('staff.save')->middleware('permission:staff_list.create,staff_list.edit');

    // Staff Groups (Nhóm nhân sự: user_ids, phân loại id, user_id trưởng nhóm)
    Route::get('/staff-groups', [StaffGroupController::class, 'index'])->name('staff_groups.index');
    Route::post('/staff-groups', [StaffGroupController::class, 'store'])->name('staff_groups.store');
    Route::put('/staff-groups/{id}', [StaffGroupController::class, 'update'])->name('staff_groups.update');
    Route::delete('/staff-groups/{id}', [StaffGroupController::class, 'destroy'])->name('staff_groups.destroy');
    Route::post('/staff-group-classifications', [StaffGroupController::class, 'storeClassification'])->name('staff_group_classifications.store');
    Route::delete('/staff-group-classifications/{id}', [StaffGroupController::class, 'destroyClassification'])->name('staff_group_classifications.destroy');

    Route::get('/payroll', [WebViewController::class, 'payroll'])->name('payroll.index')->middleware('permission:payroll.view');

    // Attendance GPS
    Route::get('/attendance', [WebViewController::class, 'attendance'])->name('attendance.index')->middleware('permission:attendance.view,attendance.checkin');

    // Proposals
    Route::get('/proposals', [WebViewController::class, 'proposals'])->name('proposals.index')->middleware('permission:proposals.view,proposals.create');

    // Tasks
    Route::get('/tasks', [WebViewController::class, 'tasks'])->name('tasks.index')->middleware('permission:task_assignment.view');

    // System Audit
    Route::get('/audit-logs', [WebViewController::class, 'auditLogs'])->name('audit.index')->middleware('permission:audit.view');
    Route::get('/audit', [WebViewController::class, 'auditLogs'])->middleware('permission:audit.view');

    // Categories Hub (Warehouses, Branches, Fund types, Brands, Categories, Routes, Vehicles, Policies)
    Route::get('/categories', [WebViewController::class, 'categories'])->name('categories.index')->middleware('permission:danhmuc.view');
    Route::get('/cat_warehouses', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'warehouses'); })->middleware('permission:danhmuc.view');
    Route::get('/cat_branches', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'branches'); })->middleware('permission:danhmuc.view');
    Route::get('/cat_fund_types', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'fund_types'); })->middleware('permission:danhmuc.view,funds.view');
    Route::get('/cat_categories', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'categories'); })->middleware('permission:danhmuc.view');
    Route::get('/cat_brands', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'brands'); })->middleware('permission:danhmuc.view');
    Route::get('/cat_suppliers', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'brands'); })->middleware('permission:danhmuc.view');
    Route::get('/cat_delivery_routes', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'routes'); })->middleware('permission:danhmuc.view,delivery_dispatch.view');
    Route::get('/cat_vehicles', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'vehicles'); })->middleware('permission:danhmuc.view,fleet.view');
    Route::get('/cat_price_tiers', [WebViewController::class, 'priceTiers'])->name('pricing.index')->middleware('permission:danhmuc.view');
    Route::get('/pricing', [WebViewController::class, 'priceTiers'])->middleware('permission:danhmuc.view');
    Route::post('/api/pricing/save-batch', [WebViewController::class, 'saveBatchPrices'])->middleware('permission:danhmuc.edit');
    Route::post('/api/price-tiers/save', [WebViewController::class, 'savePriceTier'])->middleware('permission:danhmuc.edit');
    Route::delete('/api/price-tiers/{id}', [WebViewController::class, 'deletePriceTier'])->middleware('permission:danhmuc.edit');
    Route::get('/cat_sales_policies', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'policies'); })->middleware('permission:sales_policy.view');
    Route::get('/cat_sales_policies/create', [WebViewController::class, 'salesPolicyCreate'])->name('sales_policies.create')->middleware('permission:sales_policy.create');
    Route::get('/sales-policies/create', [WebViewController::class, 'salesPolicyCreate'])->middleware('permission:sales_policy.create');
    Route::get('/cat_sales_policies/{id}/edit', [WebViewController::class, 'salesPolicyEdit'])->name('sales_policies.edit')->middleware('permission:sales_policy.edit');
    Route::get('/sales-policies/{id}/edit', [WebViewController::class, 'salesPolicyEdit'])->middleware('permission:sales_policy.edit');
    Route::post('/cat_sales_policies/save', [WebViewController::class, 'salesPolicySave'])->name('sales_policies.save')->middleware('permission:sales_policy.create,sales_policy.edit');
    Route::post('/sales-policies/save', [WebViewController::class, 'salesPolicySave'])->middleware('permission:sales_policy.create,sales_policy.edit');
    Route::get('/cat_subcategories', function(Request $request, WebViewController $controller) { return $controller->categories($request, 'subcategories'); })->middleware('permission:danhmuc.view');
    Route::post('/api/categories/import-excel', [WebViewController::class, 'importCategoriesExcel'])->middleware('permission:danhmuc.edit');
    Route::get('/api/categories/template-excel', [WebViewController::class, 'downloadCategoriesTemplate'])->middleware('permission:danhmuc.view');
    Route::post('/api/categories/save-item', [WebViewController::class, 'saveCategoryItem'])->middleware('permission:danhmuc.edit');
    Route::delete('/api/categories/delete-item/{type}/{id}', [WebViewController::class, 'deleteCategoryItem'])->middleware('permission:danhmuc.edit');

    // Roles & Permissions RBAC Matrix
    Route::get('/roles', [WebViewController::class, 'roles'])->name('roles.index')->middleware('permission:permissions.view');
    Route::get('/permissions', [WebViewController::class, 'roles'])->middleware('permission:permissions.view');
    Route::get('/workflow', [WebViewController::class, 'roles'])->middleware('permission:permissions.view,workflow.view');

    // KPI & Performance
    Route::get('/kpi', [WebViewController::class, 'kpi'])->name('kpi.index')->middleware('permission:kpi.view');

    // Fleet & Logistics Vehicles & Assignments
    Route::get('/fleet', [WebViewController::class, 'fleet'])->name('fleet.index')->middleware('permission:fleet.view');
    Route::get('/vehicle-assignments', [DeliveryController::class, 'getVehicleAssignments'])->name('vehicle.assignments')->middleware('permission:delivery_dispatch.view,fleet.view,danhmuc.view');
    Route::post('/vehicle-assignments/save', [DeliveryController::class, 'saveVehicleAssignment'])->middleware('permission:delivery_dispatch.assign,fleet.manage,danhmuc.manage');
    Route::post('/vehicle-assignments/terminate', [DeliveryController::class, 'terminateVehicleAssignment'])->middleware('permission:delivery_dispatch.assign,fleet.manage,danhmuc.manage');
    Route::delete('/vehicle-assignments/{id}', [DeliveryController::class, 'deleteVehicleAssignment'])->middleware('permission:delivery_dispatch.assign,fleet.manage,danhmuc.manage');

    // Customer Debt & Credit Limits
    Route::get('/debt', [WebViewController::class, 'debt'])->name('debt.index')->middleware('permission:debt.view');
    Route::get('/debt/{id}', [WebViewController::class, 'dealerDebtDetail'])->name('debt.detail')->middleware('permission:debt.view');
    Route::get('/dealers/{id}/debt', [WebViewController::class, 'dealerDebtDetail'])->name('dealers.debt_detail')->middleware('permission:debt.view,dealers.view');
    Route::get('/staff-debt', [WebViewController::class, 'staffDebt'])->name('staff.debt')->middleware('permission:debt.view,staff_list.view');

    // Reports Center
    Route::get('/reports', [WebViewController::class, 'reports'])->name('reports.index')->middleware('permission:dashboard.view,orders.view,inventory.view,funds.view,debt.view');
    Route::get('/reports/{type}', [WebViewController::class, 'reports'])->middleware('permission:dashboard.view,orders.view,inventory.view,funds.view,debt.view');

    // Staff Profile & Personal Dashboard
    Route::get('/profile', [WebViewController::class, 'profile'])->name('profile');
    Route::get('/staff/personal', [WebViewController::class, 'profile']);

    // Water Filter System (Phân hệ Lắp đặt & Sửa chữa Máy lọc nước - Bảng độc lập)
    Route::middleware('permission:water_filter.view,water_filter.install_view,water_filter.repair_view,water_filter.parts_view')->group(function () {
        Route::get('/water-filter', [WaterFilterController::class, 'index'])->name('water_filter.index');
        Route::get('/may-loc-nuoc', [WaterFilterController::class, 'index']);
        Route::get('/water-filter/installations/create', [WaterFilterController::class, 'createInstallation'])->name('water_filter.installations.create');
        Route::get('/water-filter/installations/{id}/edit', [WaterFilterController::class, 'editInstallationView'])->name('water_filter.installations.edit');
        Route::get('/lap-dat-may-loc/create', [WaterFilterController::class, 'createInstallation']);
        Route::get('/lap-dat-may-loc/{id}/edit', [WaterFilterController::class, 'editInstallationView']);
        Route::get('/lap-dat-may-loc', function(Request $request, WaterFilterController $controller) {
            $request->merge(['tab' => 'installations']);
            return $controller->index($request);
        });
        Route::get('/sua-chua-may-loc', function(Request $request, WaterFilterController $controller) {
            $request->merge(['tab' => 'repairs']);
            return $controller->index($request);
        });
        Route::get('/kho-phu-tung-may-loc', function(Request $request, WaterFilterController $controller) {
            $request->merge(['tab' => 'parts']);
            return $controller->index($request);
        });
        Route::get('/bao-hanh-may-loc', function(Request $request, WaterFilterController $controller) {
            $request->merge(['tab' => 'installed_machines']);
            return $controller->index($request);
        });
        Route::get('/bao-hanh-may-loc/print-qr', [WaterFilterController::class, 'printQrStickers'])->name('water_filter.qr_print');
        Route::get('/water-filter/print-qr', [WaterFilterController::class, 'printQrStickers']);

        // Water Filter APIs
        Route::prefix('api/water-filter')->group(function () {
            // Machine Models
            Route::get('/machine-models', [WaterFilterController::class, 'getMachineModels']);
            Route::post('/machine-models', [WaterFilterController::class, 'saveMachineModel']);
            Route::delete('/machine-models/{id}', [WaterFilterController::class, 'deleteMachineModel']);

            // Parts Warehouse
            Route::get('/parts', [WaterFilterController::class, 'getParts']);
            Route::post('/parts', [WaterFilterController::class, 'savePart']);
            Route::delete('/parts/{id}', [WaterFilterController::class, 'deletePart']);
            Route::post('/parts/import', [WaterFilterController::class, 'importStock']);
            Route::get('/transactions', [WaterFilterController::class, 'getTransactions']);

            // Installations
            Route::get('/installations', [WaterFilterController::class, 'getInstallations']);
            Route::post('/installations', [WaterFilterController::class, 'saveInstallation']);
            Route::post('/installations/{id}/status', [WaterFilterController::class, 'updateInstallationStatus']);
            Route::post('/installations/{id}/bind-qr', [WaterFilterController::class, 'bindQrToInstallation']);
            Route::post('/installations/bind-qr', [WaterFilterController::class, 'bindQrToInstallation']);
            Route::delete('/installations/{id}', [WaterFilterController::class, 'deleteInstallation']);

            // Installed Machines & Warranty Management
            Route::get('/installed-machines', [WaterFilterController::class, 'getInstalledMachines']);
            Route::post('/installed-machines/generate-qr', [WaterFilterController::class, 'generateBlankQrCodes']);
            Route::post('/installed-machines/{id}/activate', [WaterFilterController::class, 'activateMachine']);
            Route::post('/installed-machines/{id}/update', [WaterFilterController::class, 'updateInstalledMachine']);
            Route::delete('/installed-machines/{id}', [WaterFilterController::class, 'deleteInstalledMachine']);
            Route::post('/installed-machines/{id}/warranty', [WaterFilterController::class, 'updateMachineWarranty']);

            // Repairs
            Route::get('/repairs', [WaterFilterController::class, 'getRepairs']);
            Route::post('/repairs', [WaterFilterController::class, 'saveRepair']);
            Route::post('/repairs/{id}/status', [WaterFilterController::class, 'updateRepairStatus']);
            Route::delete('/repairs/{id}', [WaterFilterController::class, 'deleteRepair']);
        });
    });
});

// Fallback: If user accesses any unknown route, redirect to dashboard or login
Route::get('/{any?}', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
})->where('any', '.*');
