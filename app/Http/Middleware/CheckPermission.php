<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        // 1. Xác định User đang gửi request từ Auth session Laravel
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu đăng nhập tài khoản để thực hiện chức năng này.',
                ], 401);
            }
            return redirect()->guest('/login');
        }

        // Ban Giám Đốc / Manager / Admin có toàn quyền truy cập
        if ($user->hasRole('Manager') || $user->hasRole('Admin') || (isset($user->department) && in_array($user->department, ['Ban Giám Đốc', 'Quản Lý']))) {
            return $next($request);
        }

        // Cho phép cập nhật đơn hàng, giao hàng, bốc hàng, hoàn trả hàng không bị chặn 403
        if ($request->is('api/orders/update-status') || $request->is('orders/update-status') || $request->is('api/orders/save') || $request->is('orders/save') || $request->is('api/delivery-returns*')) {
            return $next($request);
        }

        // Cho phép người dùng tự cập nhật thông tin cá nhân và mật khẩu của chính mình
        if ($request->is('api/users/save')) {
            $reqId = $request->input('id');
            $reqEmail = $request->input('email');
            if (($reqId && (string)$reqId === (string)$user->id) || ($reqEmail && strtolower(trim($reqEmail)) === strtolower(trim($user->email)))) {
                return $next($request);
            }
        }

        // Nếu không yêu cầu quyền cụ thể nào -> cho qua
        if (empty($permissions)) {
            return $next($request);
        }

        // Kiểm tra xem User có ít nhất 1 trong các quyền được khai báo
        $hasAnyPermission = false;
        foreach ($permissions as $permGroup) {
            $subPerms = explode(',', $permGroup);
            foreach ($subPerms as $perm) {
                $perm = trim($perm);
                if ($perm === 'all' || $perm === 'ALL' || $user->hasPermission($perm)) {
                    $hasAnyPermission = true;
                    break 2;
                }
            }
        }

        if (!$hasAnyPermission) {
            $pageName = $this->getPageName($permissions, $request);
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "Bạn không có quyền truy cập trang {$pageName}.",
                ], 403);
            }
            return redirect('/dashboard')->with('error', "Bạn không có quyền truy cập trang {$pageName}.");
        }

        return $next($request);
    }

    /**
     * Map permissions or request path to human-readable page name
     */
    protected function getPageName(array $permissions, Request $request): string
    {
        $pageMap = [
            'staff_list.view'       => 'Danh Sách Nhân Sự',
            'staff_list.create'     => 'Thêm Mới Nhân Sự',
            'staff_list.edit'       => 'Chỉnh Sửa Hồ Sơ Nhân Sự',
            'staff_list.lock'       => 'Khóa Tài Khoản Nhân Sự',
            'permissions.view'      => 'Phân Quyền Vai Trò & Hệ Thống',
            'permissions.manage_role' => 'Cấu Hình Quyền Vai Trò',
            'permissions.manage_user' => 'Phân Quyền Nhân Sự',
            'orders.view'           => 'Quản Lý Đơn Hàng',
            'orders.create'         => 'Lên Đơn Hàng Mới',
            'orders.edit'           => 'Chỉnh Sửa Đơn Hàng',
            'orders.approve'        => 'Phê Duyệt Đơn Hàng',
            'orders.cancel'         => 'Hủy Đơn Hàng',
            'orders.status'         => 'Cập Nhật Trạng Thái Đơn Hàng',
            'orders.export'         => 'In & Xuất Đơn Hàng',
            'quotation.view'        => 'Danh Sách Báo Giá',
            'quotation.create'      => 'Lập Báo Giá',
            'quotation.edit'        => 'Sửa Báo Giá',
            'quotation.convert'     => 'Chuyển Đơn Báo Giá',
            'quotation.export'      => 'In & Xuất Báo Giá',
            'returns.view'          => 'Khách Trả Hàng',
            'returns.create'        => 'Tạo Phiếu Trả Hàng',
            'returns.approve'       => 'Duyệt Nhận Hàng Trả',
            'supplier_orders.view'  => 'Đơn Đặt Hàng Nhà Cung Cấp',
            'supplier_orders.create'=> 'Tạo Đơn Đặt NCC',
            'supplier_orders.approve'=> 'Duyệt Đơn Đặt NCC',
            'inventory.view'        => 'Kho Hàng & Tồn Kho',
            'inventory.update_price'=> 'Bảng Giá & Trần Chiết Khấu',
            'inventory.export'      => 'Xuất Báo Cáo Tồn Kho',
            'inv_import.view'       => 'Nhập Kho',
            'inv_import.create'     => 'Tạo Phiếu Nhập Kho',
            'inv_import.approve'    => 'Xác Nhận Nhập Kho',
            'inv_export.view'       => 'Xuất Kho',
            'inv_export.create'     => 'Tạo Phiếu Xuất Kho',
            'inv_export.approve'    => 'Xác Nhận Xuất Kho',
            'inv_transfer.view'     => 'Điều Chuyển Kho',
            'inv_audit.view'        => 'Kiểm Kê Kho',
            'delivery_dispatch.view'=> 'Điều Phối Xe & Giao Hàng',
            'inv_picking.view'      => 'Soạn Hàng & Bốc Hàng',
            'inv_picking.view_all_categories' => 'Xem Bốc Xếp Không Phân Loại',
            'inv_picking.pick'      => 'Bốc Hàng & Soạn Đơn',
            'inv_qc.view'           => 'Kiểm Hàng QC',
            'funds.view'            => 'Sổ Quỹ & Tài Chính',
            'funds.create_receipt'  => 'Phiếu Thu Tiền',
            'funds.create_payment'  => 'Phiếu Chi Tiền',
            'funds.approve'         => 'Duyệt Chi Tiền',
            'fleet.view'            => 'Đội Xe & Chi Phí Xe',
            'driver_debt.view'      => 'Công Nợ Lái Xe',
            'dealers.view'          => 'Danh Bạ Đại Lý',
            'dealers.create'        => 'Thêm Mới Đại Lý',
            'dealers.edit'          => 'Chỉnh Sửa Đại Lý',
            'dealers.delete'        => 'Xóa Đại Lý',
            'dealer_care.view'      => 'Chăm Sóc Đại Lý (GPS)',
            'debt.view'             => 'Công Nợ Đại Lý',
            'sales_policy.view'     => 'Chính Sách Bán Hàng',
            'payroll.view'          => 'Bảng Lương Nhân Sự',
            'attendance.view'       => 'Chấm Công Toàn Công Ty',
            'attendance.checkin'    => 'Chấm Công Vào / Ra Ca',
            'kpi.view'              => 'Báo Cáo KPI',
            'proposals.view'        => 'Đề Xuất & Tờ Trình',
            'reports.view'          => 'Báo Cáo Hoạt Động',
            'danhmuc.view'          => 'Danh Mục Hệ Thống',
        ];

        // 1. Check mapped permission
        foreach ($permissions as $permGroup) {
            $subPerms = explode(',', $permGroup);
            foreach ($subPerms as $perm) {
                $perm = trim($perm);
                if (isset($pageMap[$perm])) {
                    return $pageMap[$perm];
                }
            }
        }

        // 2. Check Database permission name
        $firstPerm = trim(explode(',', $permissions[0] ?? '')[0]);
        if ($firstPerm) {
            $dbName = \App\Models\Permission::where('slug', $firstPerm)->value('name');
            if ($dbName) {
                return $dbName;
            }
        }

        // 3. Fallback to path mapping
        $path = trim($request->path(), '/');
        $pathMap = [
            'staff/create'     => 'Thêm Mới Nhân Sự',
            'staff'            => 'Danh Sách Nhân Sự',
            'roles'            => 'Phân Quyền Vai Trò & Hệ Thống',
            'orders/create'    => 'Lên Đơn Hàng Mới',
            'orders'           => 'Quản Lý Đơn Hàng',
            'quotations'       => 'Báo Giá',
            'inventory/import' => 'Nhập Kho',
            'inventory/export' => 'Xuất Kho',
            'inventory'        => 'Kho Hàng & Tồn Kho',
            'picking'          => 'Soạn Hàng & Bốc Hàng',
            'delivery'         => 'Điều Phối Xe & Giao Hàng',
            'transactions'     => 'Sổ Quỹ & Tài Chính',
            'dealers'          => 'Danh Bạ Đại Lý',
            'debt'             => 'Công Nợ Đại Lý',
            'payroll'          => 'Bảng Lương Nhân Sự',
            'attendance'       => 'Chấm Công GPS',
            'kpi'              => 'Báo Cáo KPI',
            'proposals'        => 'Đề Xuất & Tờ Trình',
        ];

        foreach ($pathMap as $p => $name) {
            if ($path === $p || str_starts_with($path, $p . '/')) {
                return $name;
            }
        }

        return 'Chức Năng Này';
    }
}
