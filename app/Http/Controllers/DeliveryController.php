<?php

namespace App\Http\Controllers;

use App\Models\DeliveryDispatch;
use App\Models\DeliveryRoute;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\DriverCashHandover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    private function formatDeliveryDispatch(DeliveryDispatch $d): array
    {
        if (!empty($d->details) && is_array($d->details)) {
            $details = $d->details;
            $details['id'] = (string) $d->id;
            $details['tripCode'] = $d->code;
            $details['status'] = $d->status;
            return $details;
        }

        return [
            'id'                     => (string) $d->id,
            'tripCode'               => $d->code,
            'routeName'              => $d->route_summary ?? 'Tuyến giao hàng',
            'departureWarehouseId'   => 'wh-001',
            'departureWarehouseName' => 'Kho Tổng Mê Linh (MQ)',
            'vehicleId'              => $d->vehicle_id ? (string) $d->vehicle_id : null,
            'vehiclePlate'           => $d->vehicle?->plate_number ?? null,
            'vehicleType'            => $d->vehicle?->vehicle_type ?? null,
            'vehicleMaxCapacityKg'   => (float) ($d->vehicle?->capacity_kg ?? 0),
            'mainDriverId'           => $d->driver_id ? (string) $d->driver_id : null,
            'mainDriverName'         => $d->driver?->name ?? null,
            'mainDriverPhone'        => $d->driver?->phone ?? null,
            'dispatchDate'           => $d->departure_time ? $d->departure_time->format('Y-m-d') : date('Y-m-d'),
            'departureTime'          => $d->departure_time ? $d->departure_time->format('H:i') : null,
            'estimatedReturnTime'    => $d->return_time ? $d->return_time->format('H:i') : null,
            'status'                 => $d->status,
            'stops'                  => [],
        ];
    }

    /**
     * Bootstrap data for Vehicles in SPA
     */
    public function getVehiclesBootstrapData()
    {
        return Vehicle::with(['driver', 'deliveryRoute'])->get()->map(function ($v) {
            $copilot = $v->copilot_name ? User::where('name', $v->copilot_name)->first() : null;
            return [
                'id'                  => (string) $v->id,
                'plateNumber'         => $v->plate_number,
                'vehicleType'         => $v->vehicle_type,
                'driverId'            => $v->driver_id ? (string) $v->driver_id : null,
                'driverName'          => $v->driver?->name ?? null,
                'driverPhone'         => $v->driver?->phone ?? null,
                'copilotId'           => $copilot ? (string) $copilot->id : null,
                'copilotName'         => $v->copilot_name,
                'copilotPhone'        => $copilot?->phone ?? '',
                'status'              => $v->status,
                'dispatchStatus'      => $v->dispatch_status ?? ($v->status === 'Đang di chuyển' ? 'Đang giao hàng' : 'Rảnh'),
                'departureTime'       => $v->departure_time,
                'currentRouteName'    => $v->current_route_name ?? $v->deliveryRoute?->name,
                'capacityKg'          => (float) $v->capacity_kg,
                'deliveryRouteId'     => $v->delivery_route_id ? (string) $v->delivery_route_id : null,
                'deliveryRouteName'   => $v->deliveryRoute?->name,
                'kmOdometer'          => (int) $v->km_odometer,
                'nextInspectionDate'  => $v->next_inspection_date ? $v->next_inspection_date->format('Y-m-d') : null,
                'lastMaintenanceDate' => $v->last_maintenance_date ? $v->last_maintenance_date->format('Y-m-d') : null,
            ];
        });
    }

    /**
     * Bootstrap data for Delivery Dispatches in SPA
     */
    public function getDispatchesBootstrapData()
    {
        return DeliveryDispatch::with(['vehicle', 'driver'])
            ->latest('id')
            ->get()
            ->map(fn($d) => $this->formatDeliveryDispatch($d));
    }

    /**
     * Bootstrap data for Delivery Routes in SPA
     */
    public function getRoutesBootstrapData()
    {
        return DeliveryRoute::latest('id')->get()->map(function ($r) {
            return [
                'id'                  => (string) $r->id,
                'code'                => $r->code,
                'name'                => $r->name,
                'departureWarehouse'  => $r->departure_warehouse,
                'stops'               => $r->stops ?? [],
                'distanceKm'          => (float) $r->distance_km,
                'estimatedTimeHours'  => (float) $r->estimated_time_hours,
                'fuelLiters'          => (float) $r->fuel_liters,
                'tollFee'             => (float) $r->toll_fee,
                'assignedVehiclePlate'=> $r->assigned_vehicle_plate,
                'assignedDriverName'  => $r->assigned_driver_name,
                'isActive'            => (bool) $r->is_active,
                'notes'               => $r->notes ?? '',
            ];
        });
    }

    /**
     * API: Get all delivery dispatches
     */
    public function getDeliveryDispatches(Request $request)
    {
        return response()->json([
            'success'            => true,
            'deliveryDispatches' => $this->getDispatchesBootstrapData(),
        ]);
    }

    /**
     * API: Get all delivery routes
     */
    public function getDeliveryRoutes()
    {
        return response()->json([
            'success' => true,
            'routes'  => $this->getRoutesBootstrapData(),
        ]);
    }

    /**
     * API: Save / Create Delivery Dispatch
     */
    public function saveDeliveryDispatch(Request $request)
    {
        $tripData = $request->input('trip') ?: $request->all();
        $tripCode = $tripData['tripCode'] ?? ('CX-' . date('Y') . '-' . str_pad((string) (DeliveryDispatch::count() + 83), 3, '0', STR_PAD_LEFT));

        $vehicleId = null;
        if (!empty($tripData['vehicleId']) && is_numeric($tripData['vehicleId'])) {
            $vehicleId = (int) $tripData['vehicleId'];
        } elseif (!empty($tripData['vehiclePlate'])) {
            $veh = Vehicle::where('plate_number', $tripData['vehiclePlate'])->first();
            $vehicleId = $veh?->id;
        }
        // vehicleId can be null if not selected yet

        $driverId = null;
        if (!empty($tripData['mainDriverId']) && is_numeric($tripData['mainDriverId'])) {
            $driverId = (int) $tripData['mainDriverId'];
        }

        $departureTime = null;
        if (!empty($tripData['dispatchDate'])) {
            $timeStr = $tripData['departureTime'] ?? '08:00';
            $departureTime = date('Y-m-d H:i:s', strtotime($tripData['dispatchDate'] . ' ' . $timeStr));
        }

        $dispatch = DeliveryDispatch::updateOrCreate(
            ['code' => $tripCode],
            [
                'vehicle_id'     => $vehicleId,
                'driver_id'      => $driverId,
                'departure_time' => $departureTime,
                'status'         => $tripData['status'] ?? 'Đang giao hàng',
                'route_summary'  => $tripData['routeName'] ?? 'Tuyến giao hàng',
                'details'        => $tripData,
            ]
        );

        return response()->json([
            'success'  => true,
            'dispatch' => $this->formatDeliveryDispatch($dispatch),
            'message'  => 'Lưu điều vận thành công',
        ]);
    }

    /**
     * API: Update Delivery Stop Status
     */
    public function updateDeliveryStop(Request $request)
    {
        $tripId = $request->input('tripId');
        $stopId = $request->input('stopId');
        $newStatus = $request->input('status'); // Giao thành công, Khách hẹn lại, Không nhận hàng

        $dispatch = DeliveryDispatch::find($tripId);
        if ($dispatch && !empty($dispatch->details)) {
            $details = $dispatch->details;
            if (!empty($details['stops']) && is_array($details['stops'])) {
                foreach ($details['stops'] as &$stop) {
                    if (($stop['id'] ?? '') === $stopId) {
                        $stop['status'] = $newStatus;
                        $stop['actualArrivalTime'] = date('H:i');
                    }
                }
                $dispatch->details = $details;
                $dispatch->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Cập nhật điểm dừng thành công']);
    }

    /**
     * Bootstrap data for Driver Cash Handovers in SPA
     */
    public function getDriverCashHandoversBootstrapData()
    {
        return DriverCashHandover::latest('id')
            ->limit(50)
            ->get()
            ->map(function ($h) {
                return [
                    'id'                => (string) $h->id,
                    'code'              => $h->code,
                    'vehiclePlate'      => $h->vehicle_plate,
                    'driverId'          => $h->driver_id ? (string) $h->driver_id : null,
                    'driverName'        => $h->driver_name,
                    'copilotName'       => $h->copilot_name,
                    'accountantId'      => $h->accountant_id ? (string) $h->accountant_id : null,
                    'accountantName'    => $h->accountant_name,
                    'handoverAmount'    => (float) $h->handover_amount,
                    'paymentMethod'     => $h->payment_method,
                    'orderCodes'        => is_array($h->order_codes) ? $h->order_codes : (!empty($h->order_codes) ? (json_decode($h->order_codes, true) ?: []) : []),
                    'orderCount'        => (int) $h->order_count,
                    'notes'             => $h->notes,
                    'receiptCode'       => $h->receipt_code,
                    'status'            => $h->status,
                    'timestamp'         => $h->created_at ? $h->created_at->format('H:i d/m/Y') : date('H:i d/m/Y'),
                    'approvedAt'        => $h->approved_at ? $h->approved_at->format('H:i d/m/Y') : null,
                    'approvedByName'    => $h->approved_by_name,
                    'approvalNotes'     => $h->approval_notes,
                    'rejectedReason'    => $h->rejected_reason,
                    'rejectedAt'        => $h->rejected_at ? $h->rejected_at->format('H:i d/m/Y') : null,
                    'rejectedByName'    => $h->rejected_by_name,
                    'orderReceiptCodes' => is_array($h->order_receipt_codes) ? $h->order_receipt_codes : (!empty($h->order_receipt_codes) ? (json_decode($h->order_receipt_codes, true) ?: (object)[]) : (object)[]),
                ];
            });
    }

    /**
     * API: Get Driver Cash Handovers
     */
    public function getDriverCashHandovers()
    {
        return response()->json(['handovers' => $this->getDriverCashHandoversBootstrapData()]);
    }

    /**
     * API: Settle driver cash / Lập biên bản quyết toán, nộp tiền mặt COD của lái xe cho kế toán/thủ quỹ
     */
    public function settleDriverCash(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $code = $data['code'] ?? null;
        $existingHandover = $code ? DriverCashHandover::where('code', $code)->first() : null;
        if (!$code) {
            $code = 'BG-TIEN-' . date('Ymd') . '-' . str_pad((string) rand(100, 999), 3, '0', STR_PAD_LEFT);
        }

        $action = $data['action'] ?? ($data['status'] === 'Chờ kế toán duyệt' ? 'submit' : 'approve');
        $tripId = $data['tripId'] ?? null;

        $vehiclePlate = $data['vehiclePlate'] ?? ($existingHandover?->vehicle_plate ?? 'Xe giao hàng');
        $driverName = $data['driverName'] ?? ($existingHandover?->driver_name ?? (auth()->user()?->name ?? 'Lái xe'));
        $copilotName = $data['copilotName'] ?? ($existingHandover?->copilot_name ?? null);
        $accountantId = $data['accountantId'] ?? ($existingHandover?->accountant_id ?? null);
        $accountantName = $data['accountantName'] ?? ($existingHandover?->accountant_name ?? null);
        if ($accountantId && empty($accountantName)) {
            $accountantName = \App\Models\User::find($accountantId)?->name;
        }
        if (empty($accountantName)) {
            $accountantName = auth()->user()?->name ?? 'Kế toán quỹ';
        }

        if (isset($data['handoverAmount'])) {
            $amount = (float) $data['handoverAmount'];
        } elseif (isset($data['amount'])) {
            $amount = (float) $data['amount'];
        } else {
            $amount = (float) ($existingHandover?->handover_amount ?? 0);
        }

        // Khi duyệt: Tuyệt đối cố định số tiền theo biên bản nộp ban đầu của tài xế
        if ($action === 'approve' && $existingHandover) {
            $amount = (float) $existingHandover->handover_amount;
        }

        $paymentMethod = $data['paymentMethod'] ?? ($existingHandover?->payment_method ?? 'Tiền mặt');

        $orderCodes = $data['orderCodes'] ?? [];
        if (empty($orderCodes) && $existingHandover) {
            $orderCodes = is_array($existingHandover->order_codes) ? $existingHandover->order_codes : (json_decode($existingHandover->order_codes, true) ?: []);
        }
        if (empty($orderCodes) && $vehiclePlate && $vehiclePlate !== 'Xe giao hàng') {
            $orderCodes = \App\Models\Order::where('assigned_vehicle_plate', $vehiclePlate)
                ->whereIn('status', ['Đã giao', 'Giao thành công'])
                ->pluck('order_code')
                ->toArray();
        }

        $notes = $data['notes'] ?? ($existingHandover?->notes ?? '');
        $rejectedReason = $data['rejectedReason'] ?? '';

        // TRƯỜNG HỢP 1: Lái xe gửi bàn giao tiền chờ kế toán duyệt
        if ($action === 'submit' || $action === 'pending') {
            if (!empty($orderCodes) && is_array($orderCodes)) {
                \App\Models\Order::whereIn('order_code', $orderCodes)->update([
                    'payment_status' => 'Chờ duyệt nộp quỹ',
                ]);
            }

            // Lưu bản ghi vào database với trạng thái "Chờ kế toán duyệt" (chưa giải phóng đơn hàng)
            DriverCashHandover::updateOrCreate(
                ['code' => $code],
                [
                    'vehicle_plate'       => $vehiclePlate,
                    'driver_id'           => auth()->id(),
                    'driver_name'         => $driverName,
                    'copilot_name'        => $copilotName,
                    'accountant_id'       => $accountantId,
                    'accountant_name'     => $accountantName,
                    'handover_amount'     => $amount,
                    'payment_method'      => $paymentMethod,
                    'order_codes'         => $orderCodes,
                    'order_count'         => count($orderCodes),
                    'notes'               => $notes,
                    'status'              => 'Chờ kế toán duyệt',
                ]
            );

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => now()->format('Y-m-d H:i:s'),
                    'performer_name'       => auth()->user()?->name ?? $driverName,
                    'user_id'              => auth()->id(),
                    'action'               => 'Gửi Bàn Giao Tiền COD Cho Kế Toán',
                    'module'               => 'Tài Chính & Quỹ',
                    'target_document_code' => $code,
                    'field_name'           => 'Biên bản bàn giao tiền COD',
                    'old_value'            => 'Lái xe đang cầm tiền',
                    'new_value'            => 'Chờ kế toán duyệt nộp quỹ',
                    'notes'                => "Lái xe {$driverName} (Xe {$vehiclePlate}) đã lập biên bản nộp " . number_format($amount, 0, ',', '.') . " đ (" . count($orderCodes) . " đơn hàng) gửi Kế toán {$accountantName} chờ duyệt." . ($notes ? " Ghi chú: {$notes}" : ''),
                ]);
            } catch (\Throwable $e) {}

            try {
                \App\Models\Announcement::create([
                    'title'             => "💸 Lái xe nộp tiền COD: Xe {$vehiclePlate} - " . number_format($amount, 0, ',', '.') . " đ (Chờ duyệt)",
                    'content'           => "Lái xe {$driverName}" . ($copilotName ? " & {$copilotName}" : '') . " (Xe {$vehiclePlate}) vừa lập biên bản {$code} nộp " . number_format($amount, 0, ',', '.') . " đ (" . count($orderCodes) . " đơn COD) chỉ định Kế toán: {$accountantName}.\nĐề nghị Kế toán vào menu Bán hàng > Kiểm COD để kiểm đếm và bấm 'Duyệt' giải phóng đơn hàng." . ($notes ? "\nGhi chú: {$notes}" : ''),
                    'author'            => $driverName,
                    'author_role'       => 'Lái xe vận tải',
                    'priority'          => 'Khẩn cấp',
                    'target_department' => 'Kế toán',
                    'read_by'           => [],
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'success'   => true,
                'message'   => "Đã gửi biên bản {$code} nộp tiền về cho Kế toán {$accountantName} chờ duyệt.",
                'code'      => $code,
                'status'    => 'Chờ kế toán duyệt',
                'handovers' => $this->getDriverCashHandoversBootstrapData(),
            ]);
        }

        // Kiểm tra quyền hạn: Kế toán hoặc Ban giám đốc có quyền duyệt / từ chối
        if ($action === 'reject' || $action === 'approve') {
            $user = auth()->user();
            if ($user && ($user->hasRole('Fleet') || $user->hasRole('Warehouse_Staff'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lái xe / Phụ kho không có quyền phê duyệt hoặc từ chối nộp tiền. Thao tác này chỉ dành cho Kế toán được chỉ định.',
                ], 403);
            }

            if ($existingHandover) {
                $targetAccountantName = $existingHandover->accountant_name;
                $targetAccountantId = $existingHandover->accountant_id;
                $isDesignated = false;

                if ($user) {
                    if ($user->hasRole('Executive') || $user->hasRole('Admin') || $user->hasRole('Finance_Accountant') || $user->hasRole('Sales_Accountant') || $user->hasRole('Manager') || $user->is_super_admin) {
                        $isDesignated = true;
                    }
                    if ($user->department === 'Accounting' || stripos($user->department, 'Kế toán') !== false || stripos($user->department, 'Executive') !== false || stripos($user->department, 'Ban Giám Đốc') !== false) {
                        $isDesignated = true;
                    }
                    if ($targetAccountantId && $user->id == $targetAccountantId) {
                        $isDesignated = true;
                    }
                    if ($targetAccountantName && (
                        strcasecmp(trim($user->name), trim($targetAccountantName)) === 0 ||
                        stripos($targetAccountantName, $user->name) !== false ||
                        stripos($user->name, $targetAccountantName) !== false
                    )) {
                        $isDesignated = true;
                    }
                }

                $clientAccountantName = $data['accountantName'] ?? null;
                if ($clientAccountantName && $targetAccountantName && (
                    strcasecmp(trim($clientAccountantName), trim($targetAccountantName)) === 0 ||
                    stripos($targetAccountantName, $clientAccountantName) !== false ||
                    stripos($clientAccountantName, $targetAccountantName) !== false
                )) {
                    $isDesignated = true;
                }

                if (!$isDesignated) {
                    return response()->json([
                        'success' => false,
                        'message' => "Biên bản nộp tiền này được chỉ định cho Kế toán [{$targetAccountantName}]. Vui lòng đăng nhập đúng tài khoản hoặc nhờ Kế toán phụ trách duyệt.",
                    ], 403);
                }
            }
        }

        // TRƯỜNG HỢP 2: Kế toán từ chối biên bản nộp tiền
        if ($action === 'reject') {
            if (!empty($orderCodes) && is_array($orderCodes)) {
                \App\Models\Order::whereIn('order_code', $orderCodes)->update([
                    'payment_status' => 'Đã thu tiền mặt',
                ]);
            }

            // Cập nhật database
            DriverCashHandover::where('code', $code)->update([
                'status'              => 'Đã từ chối',
                'rejected_reason'     => $rejectedReason,
                'rejected_at'         => now(),
                'rejected_by_name'    => auth()->user()?->name ?? $accountantName,
                'rejected_by_user_id' => auth()->id(),
            ]);

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => now()->format('Y-m-d H:i:s'),
                    'performer_name'       => auth()->user()?->name ?? $accountantName,
                    'user_id'              => auth()->id(),
                    'action'               => 'Từ Chối Bàn Giao Tiền COD',
                    'module'               => 'Tài Chính & Quỹ',
                    'target_document_code' => $code,
                    'field_name'           => 'Biên bản bàn giao tiền COD',
                    'old_value'            => 'Chờ kế toán duyệt',
                    'new_value'            => 'Đã từ chối',
                    'notes'                => "Kế toán {$accountantName} từ chối biên bản nộp tiền {$code} từ Lái xe {$driverName}. Lý do: {$rejectedReason}",
                ]);
            } catch (\Throwable $e) {}

            try {
                \App\Models\Announcement::create([
                    'title'             => "❌ Từ chối biên bản nộp tiền COD: {$code} (Xe {$vehiclePlate})",
                    'content'           => "Kế toán " . (auth()->user()?->name ?? $accountantName) . " đã từ chối nhận tiền biên bản {$code} từ Lái xe {$driverName}.\nLý do: {$rejectedReason}.\nLái xe vui lòng kiểm đếm lại tiền mặt thực tế và các đơn COD.",
                    'author'            => auth()->user()?->name ?? $accountantName,
                    'author_role'       => 'Kế toán',
                    'priority'          => 'Khẩn cấp',
                    'target_department' => 'Vận tải',
                    'read_by'           => [],
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'success'   => true,
                'message'   => "Đã từ chối biên bản nộp tiền {$code}.",
                'code'      => $code,
                'status'    => 'Đã từ chối',
                'handovers' => $this->getDriverCashHandoversBootstrapData(),
            ]);
        }

        // TRƯỜNG HỢP 3: Kế toán duyệt & Tự động tạo phiếu thu cho các đơn hàng
        $fund = \App\Models\FinancialFund::where('name', 'LIKE', '%tiền mặt%')->first() ?: \App\Models\FinancialFund::first();
        $createdTransactions = [];
        $orderReceiptCodes = [];

        if (!empty($orderCodes) && is_array($orderCodes)) {
            $ordersToSettle = \App\Models\Order::with(['items', 'dealer'])->whereIn('order_code', $orderCodes)->get();
            $totalCalculatedAmount = 0;

            foreach ($ordersToSettle as $ord) {
                $ordAmount = (float) ($ord->driver_debt_amount ?: $ord->total_amount);
                $totalCalculatedAmount += $ordAmount;
                $ordTxCode = 'PT-' . $ord->order_code;
                $orderReceiptCodes[$ord->order_code] = $ordTxCode;

                // Chi tiết các mặt hàng trong đơn hàng
                $orderItems = [];
                if ($ord->items) {
                    foreach ($ord->items as $it) {
                        $orderItems[] = [
                            'sku'        => $it->sku ?? $it->product_sku ?? '',
                            'name'       => $it->name ?? $it->product_name ?? 'Sản phẩm',
                            'unit'       => $it->unit ?? 'Cái',
                            'quantity'   => (float) ($it->quantity ?? 1),
                            'unitPrice'  => (float) ($it->unit_price ?? 0),
                            'totalPrice' => (float) ($it->amount ?? (($it->quantity ?? 1) * ($it->unit_price ?? 0))),
                        ];
                    }
                }

                $txDetails = [
                    'order' => [
                        'orderCode'    => $ord->order_code,
                        'dealerName'   => $ord->dealer_name ?: ($ord->customer_name ?: "Khách hàng {$ord->order_code}"),
                        'address'      => $ord->customer_address ?? ($ord->dealer?->address ?? ''),
                        'phone'        => $ord->customer_phone ?? ($ord->dealer?->phone ?? ''),
                        'orderTotal'   => (float) $ord->total_amount,
                        'collectedCod' => $ordAmount,
                        'items'        => $orderItems,
                    ],
                    'items'        => $orderItems,
                    'handoverCode' => $code,
                    'vehiclePlate' => $vehiclePlate,
                    'driverName'   => $driverName,
                    'copilotName'  => $copilotName,
                    'accountant'   => $accountantName,
                ];

                // Tự động tạo hoặc cập nhật phiếu thu cho từng đơn hàng kèm chi tiết đơn hàng & hàng hóa
                $tx = \App\Models\Transaction::updateOrCreate(
                    ['code' => $ordTxCode],
                    [
                        'type'                  => 'Thu',
                        'fund_id'               => $fund?->id ?? 1,
                        'category'              => 'Thu tiền mặt COD đơn hàng',
                        'amount'                => $ordAmount,
                        'customer_partner_name' => $ord->dealer_name ?: ($ord->customer_name ?: "Khách hàng {$ord->order_code}"),
                        'order_id'              => $ord->id,
                        'date'                  => now()->format('Y-m-d'),
                        'notes'                 => "Phiếu thu tiền mặt COD đơn {$ord->order_code} theo Biên bản bàn giao {$code} (Xe {$vehiclePlate}, Lái xe {$driverName}). Kế toán duyệt: {$accountantName}." . ($notes ? " Ghi chú: {$notes}" : ''),
                        'details'               => $txDetails,
                    ]
                );
                $createdTransactions[] = $tx;

                // Cập nhật trạng thái đơn hàng: status chỉ dừng ở "Đã giao", trạng thái thanh toán và giải phóng khỏi xe
                $newPaymentStatus = in_array($ord->payment_method, ['Ghi nợ', 'Ghi nợ đại lý', 'Ký nợ', 'Ký nợ đại lý']) 
                    ? 'Ghi nợ đại lý' 
                    : 'Đã thanh toán';

                $ord->update([
                    'status'                  => 'Đã giao',
                    'payment_status'          => $newPaymentStatus,
                    'settled_with_accountant' => true,
                    'assigned_vehicle_plate'  => null,
                ]);

                // Khấu trừ công nợ đại lý nếu có khoản thu vượt đơn cấn trừ nợ cũ
                if ($ord->dealer_id) {
                    $netOrder = max(0, (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0));
                    $collected = (float) ($ord->driver_debt_amount ?: $ord->total_amount);
                    $surplus = max(0, $collected - $netOrder);

                    if ($surplus > 0) {
                        $dealer = \App\Models\Dealer::find($ord->dealer_id) ?: \App\Models\Dealer::where('code', $ord->dealer_id)->first();
                        if ($dealer) {
                            $dealer->current_debt = max(0, (float) $dealer->current_debt - $surplus);
                            $dealer->save();
                        }
                    }
                }
            }

            // Tự động giải phóng tất cả các đơn hàng đã giao thành công của xe này khỏi xe (giữ status là 'Đã giao')
            if ($vehiclePlate) {
                $vehOrders = \App\Models\Order::where('assigned_vehicle_plate', $vehiclePlate)
                    ->whereIn('status', ['Đã giao', 'Giao thành công'])
                    ->get();

                foreach ($vehOrders as $vo) {
                    $pStatus = in_array($vo->payment_method, ['Ghi nợ', 'Ghi nợ đại lý', 'Ký nợ', 'Ký nợ đại lý']) 
                        ? 'Ghi nợ đại lý' 
                        : 'Đã thanh toán';

                    $vo->update([
                        'status'                  => 'Đã giao',
                        'payment_status'          => $pStatus,
                        'settled_with_accountant' => true,
                        'assigned_vehicle_plate'  => null,
                    ]);
                }

                // Kiểm tra nếu xe không còn đơn nào thì đưa xe về Sẵn sàng / Rảnh
                $remainingVehOrders = \App\Models\Order::where('assigned_vehicle_plate', $vehiclePlate)
                    ->whereNotIn('status', ['Đã hoàn kho', 'Đã hủy', 'Hủy'])
                    ->count();
                if ($remainingVehOrders === 0) {
                    \App\Models\Vehicle::where('plate_number', $vehiclePlate)->update([
                        'status'          => 'Sẵn sàng',
                        'dispatch_status' => 'Rảnh',
                        'departure_time'  => null,
                    ]);
                }
            }

            // Tăng quỹ theo tổng số tiền thực thu của các đơn
            $incrementAmt = $totalCalculatedAmount > 0 ? $totalCalculatedAmount : $amount;
            if ($fund && $incrementAmt > 0) {
                $fund->increment('current_balance', $incrementAmt);
            }
        } elseif ($amount > 0) {
            // Trường hợp không có orderCodes nhưng có số tiền tổng
            $txCode = 'PT-COD-' . date('Ymd') . '-' . str_pad((string) rand(10, 99), 2, '0', STR_PAD_LEFT);
            $tx = \App\Models\Transaction::updateOrCreate(
                ['code' => $txCode],
                [
                    'type'                  => 'Thu',
                    'fund_id'               => $fund?->id ?? 1,
                    'category'              => 'Nộp tiền mặt COD giao hàng',
                    'amount'                => $amount,
                    'customer_partner_name' => "Lái xe: {$driverName} (Xe {$vehiclePlate})",
                    'date'                  => now()->format('Y-m-d'),
                    'notes'                 => "Bàn giao tiền COD xe {$vehiclePlate} cho Kế toán {$accountantName}. Biên bản: {$code}." . ($notes ? " Ghi chú: {$notes}" : ''),
                    'details'               => [
                        'handoverCode' => $code,
                        'vehiclePlate' => $vehiclePlate,
                        'driverName'   => $driverName,
                        'copilotName'  => $copilotName,
                        'accountant'   => $accountantName,
                        'items'        => [],
                    ],
                ]
            );
            $createdTransactions[] = $tx;

            if ($fund) {
                $fund->increment('current_balance', $amount);
            }
        }

        // Tự động đối trừ giảm công nợ cho Lái xe khi nộp tiền mặt/COD về quỹ thành công
        $settledAmount = $totalCalculatedAmount > 0 ? $totalCalculatedAmount : $amount;
        $driverUserId = $existingHandover?->driver_id ?? ($data['driverId'] ?? null);
        $driverUser = $driverUserId ? \App\Models\User::find($driverUserId) : null;
        if (!$driverUser && $driverName) {
            $driverUser = \App\Models\User::where('name', $driverName)->first();
        }
        if ($driverUser && $settledAmount > 0) {
            $driverUser->current_debt = max(0, (float)$driverUser->current_debt - $settledAmount);
            $driverUser->save();
        }

        // Cập nhật lại công nợ các Đại lý có đơn hàng trong đợt thu tiền này
        if (!empty($ordersToSettle)) {
            $settledDealerIds = $ordersToSettle->pluck('dealer_id')->filter()->unique();
            foreach ($settledDealerIds as $dId) {
                $dl = \App\Models\Dealer::find($dId) ?: \App\Models\Dealer::where('code', $dId)->first();
                if ($dl) {
                    $dl->recalculateCurrentDebt();
                }
            }
        }

        // Cập nhật DriverCashHandover trong DB
        $firstReceiptCode = !empty($orderReceiptCodes) ? reset($orderReceiptCodes) : ('PT-COD-' . date('Ymd'));
        DriverCashHandover::updateOrCreate(
            ['code' => $code],
            [
                'vehicle_plate'       => $vehiclePlate,
                'driver_name'         => $driverName,
                'copilot_name'        => $copilotName,
                'accountant_name'     => $accountantName,
                'handover_amount'     => $amount,
                'payment_method'      => $paymentMethod,
                'order_codes'         => $orderCodes,
                'order_count'         => count($orderCodes),
                'status'              => 'Đã hoàn thành',
                'approved_at'         => now(),
                'approved_by_name'    => auth()->user()?->name ?? $accountantName,
                'approved_by_user_id' => auth()->id(),
                'approval_notes'      => $notes,
                'order_receipt_codes' => $orderReceiptCodes,
                'receipt_code'        => $firstReceiptCode,
            ]
        );

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => now()->format('Y-m-d H:i:s'),
                    'performer_name'       => auth()->user()?->name ?? $accountantName,
                    'user_id'              => auth()->id(),
                    'action'               => 'Kế Toán Duyệt Bàn Giao Tiền COD & Lập Phiếu Thu',
                    'module'               => 'Tài Chính & Quỹ',
                    'target_document_code' => $code,
                    'field_name'           => 'Biên bản bàn giao tiền COD',
                    'old_value'            => 'Chờ kế toán duyệt',
                    'new_value'            => 'Đã duyệt nộp quỹ & Lập phiếu thu các đơn hàng',
                    'notes'                => "Kế toán {$accountantName} đã duyệt biên bản {$code} (" . number_format($amount, 0, ',', '.') . " đ) từ Lái xe {$driverName} và tự động tạo " . count($orderCodes) . " phiếu thu cho các đơn hàng.",
                ]);
            } catch (\Throwable $e) {}

            try {
                \App\Models\Announcement::create([
                    'title'             => "✅ Đã duyệt nộp quỹ COD: Biên bản {$code} (Xe {$vehiclePlate})",
                    'content'           => "Kế toán " . (auth()->user()?->name ?? $accountantName) . " đã kiểm đếm và duyệt nhập quỹ số tiền " . number_format($amount, 0, ',', '.') . " đ từ Lái xe {$driverName} (Xe {$vehiclePlate}). " . count($createdTransactions) . " phiếu thu đã được tự động hạch toán vào Quỹ tiền mặt.",
                    'author'            => auth()->user()?->name ?? $accountantName,
                    'author_role'       => 'Kế toán',
                    'priority'          => 'Quan trọng',
                    'target_department' => 'Tất cả',
                    'read_by'           => [],
                ]);
            } catch (\Throwable $e) {}

        // Khi tất toán với kế toán xong thì xe về trạng thái Rảnh / Sẵn sàng
        if ($vehiclePlate) {
            $veh = Vehicle::where('plate_number', $vehiclePlate)->first();
            if ($veh) {
                $veh->status = 'Sẵn sàng';
                $veh->dispatch_status = 'Rảnh';
                $veh->departure_time = null;
                $veh->save();
            }
        }

        return response()->json([
            'success'           => true,
            'message'           => 'Kế toán đã duyệt nộp tiền và tự động lập phiếu thu cho các đơn hàng thành công!',
            'code'              => $code,
            'orderReceiptCodes' => $orderReceiptCodes,
            'transactions'      => $createdTransactions,
            'status'            => 'Đã hoàn thành',
            'handovers'         => $this->getDriverCashHandoversBootstrapData(),
        ]);
    }


    /**
     * API: Update Vehicle Status
     */
    public function updateVehicleStatus(Request $request)
    {
        $vehicleId = $request->input('vehicleId') ?: $request->input('id');
        $plateNumber = $request->input('plateNumber');
        $status = $request->input('status');
        $dispatchStatus = $request->input('dispatchStatus') ?: $request->input('dispatch_status');
        $departureTime = $request->input('departureTime') ?: $request->input('departure_time');
        $currentRouteName = $request->input('currentRouteName') ?: $request->input('current_route_name');

        $veh = $vehicleId ? Vehicle::find($vehicleId) : ($plateNumber ? Vehicle::where('plate_number', $plateNumber)->first() : null);
        if ($veh) {
            // Chặn xuất bến nếu còn đơn hàng chưa "Đã kiểm hàng" (Chỉ áp dụng khi xe Xuất Bến từ bãi đi giao, KHÔNG áp dụng khi xe Đang về hoặc Rảnh)
            $isDeparting = ($dispatchStatus === 'Đang giao hàng' || ($status === 'Đang di chuyển' && empty($dispatchStatus)))
                && !in_array($veh->dispatch_status, ['Đang giao hàng', 'Trên đường về']);

            if ($isDeparting) {
                $pendingOrders = \App\Models\Order::where('assigned_vehicle_plate', $veh->plate_number)
                    ->whereNotIn('status', [
                        'Đã kiểm hàng', 
                        'Đã kiểm', 
                        'Đang vận chuyển', 
                        'Đang giao hàng', 
                        'Đã giao', 
                        'Giao thành công', 
                        'Đã quyết toán', 
                        'Đã hoàn kho', 
                        'Đã hoàn thành', 
                        'Đã hủy', 
                        'Hủy', 
                        'Giao không thành công', 
                        'Giao thất bại'
                    ])
                    ->get();

                if ($pendingOrders->count() > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Không thể xuất bến xe {$veh->plate_number}! Còn {$pendingOrders->count()} đơn hàng chưa hoàn thành kiểm hàng.",
                        'pending_orders' => $pendingOrders->pluck('order_code')
                    ], 422);
                }
            }

            if ($status) {
                $veh->status = $status;
            }
            if ($dispatchStatus) {
                $veh->dispatch_status = $dispatchStatus;
            } elseif ($status === 'Đang di chuyển') {
                $veh->dispatch_status = 'Đang giao hàng';
            }

            if ($departureTime) {
                $veh->departure_time = $departureTime;
            } elseif ($isDeparting && empty($veh->departure_time)) {
                $veh->departure_time = now()->format('H:i d/m/Y');
            } elseif ($dispatchStatus === 'Rảnh' || $status === 'Sẵn sàng') {
                $veh->departure_time = null;
            }

            if ($currentRouteName) {
                $veh->current_route_name = $currentRouteName;
            }
            $veh->save();

            // Nếu xe xuất bến, tự động cập nhật các đơn hàng chưa giao trên xe sang trạng thái "Đang vận chuyển"
            $updatedOrderCodes = [];
            if ($isDeparting) {
                $assignedOrders = \App\Models\Order::where('assigned_vehicle_plate', $veh->plate_number)
                    ->whereNotIn('status', ['Đã giao', 'Giao thành công', 'Đã quyết toán', 'Đã hủy', 'Giao không thành công', 'Giao thất bại'])
                    ->get();

                foreach ($assignedOrders as $ord) {
                    $oldStatus = $ord->status;
                    $ord->status = 'Đang vận chuyển';
                    $ord->save();
                    $updatedOrderCodes[] = $ord->order_code;

                    try {
                        \App\Models\AuditLog::create([
                            'timestamp'            => now()->format('Y-m-d H:i:s'),
                            'performer_name'       => auth()->user()?->name ?? ($veh->driver?->name ?? 'Lái xe'),
                            'user_id'              => auth()->id(),
                            'action'               => 'Xuất Bến Vận Chuyển',
                            'module'               => 'Vận Tải & Giao Nhận',
                            'target_document_code' => $ord->order_code,
                            'field_name'           => 'Trạng thái đơn hàng',
                            'old_value'            => $oldStatus,
                            'new_value'            => 'Đang vận chuyển',
                            'notes'                => "Xe {$veh->plate_number} xuất bến lúc {$veh->departure_time}. Đơn hàng chuyển sang Đang vận chuyển.",
                        ]);
                    } catch (\Throwable $e) {}
                }
            }

            $copilot = $veh->copilot_name ? User::where('name', $veh->copilot_name)->first() : null;
            $vehicleData = [
                'id'                  => (string) $veh->id,
                'plateNumber'         => $veh->plate_number,
                'vehicleType'         => $veh->vehicle_type,
                'driverId'            => $veh->driver_id ? (string) $veh->driver_id : null,
                'driverName'          => $veh->driver?->name ?? null,
                'driverPhone'         => $veh->driver?->phone ?? null,
                'copilotId'           => $copilot ? (string) $copilot->id : null,
                'copilotName'         => $veh->copilot_name,
                'copilotPhone'        => $copilot?->phone ?? '',
                'status'              => $veh->status,
                'dispatchStatus'      => $veh->dispatch_status ?? ($veh->status === 'Đang di chuyển' ? 'Đang giao hàng' : 'Rảnh'),
                'departureTime'       => $veh->departure_time,
                'currentRouteName'    => $veh->current_route_name ?? $veh->deliveryRoute?->name,
                'capacityKg'          => (float) $veh->capacity_kg,
                'deliveryRouteId'     => $veh->delivery_route_id ? (string) $veh->delivery_route_id : null,
                'deliveryRouteName'   => $veh->deliveryRoute?->name,
                'kmOdometer'          => (int) $veh->km_odometer,
                'nextInspectionDate'  => $veh->next_inspection_date ? $veh->next_inspection_date->format('Y-m-d') : null,
                'lastMaintenanceDate' => $veh->last_maintenance_date ? $veh->last_maintenance_date->format('Y-m-d') : null,
            ];

            return response()->json([
                'success' => true, 
                'vehicle' => $vehicleData,
                'updatedOrders' => $updatedOrderCodes
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy xe'], 404);
    }

    /**
     * API: Save Vehicle
     */
    public function saveVehicle(Request $request)
    {
        $id = $request->input('id');
        $veh = $id ? Vehicle::find($id) : new Vehicle();
        if (!$veh) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy xe'], 404);
        }

        if ($request->has('plateNumber')) $veh->plate_number = $request->input('plateNumber');
        if ($request->has('vehicleType')) $veh->vehicle_type = $request->input('vehicleType');
        if ($request->has('capacityKg')) $veh->capacity_kg = (float) $request->input('capacityKg', 2500);
        if ($request->has('capacityVolumeM3')) $veh->capacity_volume_m3 = (float) $request->input('capacityVolumeM3', 12);
        if ($request->has('fuelConsumptionPer100Km')) $veh->fuel_consumption_per_100km = (float) $request->input('fuelConsumptionPer100Km', 12);
        if ($request->has('status')) $veh->status = $request->input('status', 'Sẵn sàng');
        if ($request->has('driverId')) $veh->driver_id = $request->input('driverId') ?: null;
        if ($request->has('copilotName')) $veh->copilot_name = $request->input('copilotName') ?: null;
        if ($request->has('deliveryRouteId')) {
            $veh->delivery_route_id = $request->input('deliveryRouteId') ?: null;
        }
        if ($request->has('dispatchStatus') || $request->has('dispatch_status')) {
            $veh->dispatch_status = $request->input('dispatchStatus', $request->input('dispatch_status'));
        }
        if ($request->has('departureTime') || $request->has('departure_time')) {
            $veh->departure_time = $request->input('departureTime', $request->input('departure_time'));
        }
        if ($request->has('currentRouteName') || $request->has('current_route_name')) {
            $veh->current_route_name = $request->input('currentRouteName', $request->input('current_route_name'));
        }

        $veh->save();

        // Đồng bộ biển số sang tuyến nếu được chọn
        if ($veh->delivery_route_id && $veh->plate_number) {
            $driverName = $veh->driver?->name;
            DeliveryRoute::where('id', $veh->delivery_route_id)->update([
                'assigned_vehicle_plate' => $veh->plate_number,
                'assigned_driver_name' => $driverName
            ]);
        }

        return response()->json([
            'success' => true, 
            'vehicle' => $this->getVehiclesBootstrapData()->firstWhere('id', (string)$veh->id) ?: $veh
        ]);
    }

    /**
     * API: Save Delivery Route
     */
    public function saveDeliveryRoute(Request $request)
    {
        $id = $request->input('id');
        $route = $id ? DeliveryRoute::find($id) : new DeliveryRoute();
        if (!$route) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tuyến'], 404);
        }

        if ($request->has('code')) $route->code = $request->input('code');
        if ($request->has('name')) $route->name = $request->input('name');
        if ($request->has('departureWarehouse')) $route->departure_warehouse = $request->input('departureWarehouse');
        if ($request->has('distanceKm')) $route->distance_km = (float) $request->input('distanceKm');
        if ($request->has('estimatedTimeHours')) $route->estimated_time_hours = (float) $request->input('estimatedTimeHours');
        if ($request->has('fuelLiters')) $route->fuel_liters = (float) $request->input('fuelLiters');
        if ($request->has('tollFee')) $route->toll_fee = (float) $request->input('tollFee');
        if ($request->has('assignedVehiclePlate')) $route->assigned_vehicle_plate = $request->input('assignedVehiclePlate');
        if ($request->has('assignedDriverName')) $route->assigned_driver_name = $request->input('assignedDriverName');
        if ($request->has('isActive')) $route->is_active = (bool) $request->input('isActive');
        if ($request->has('notes')) $route->notes = $request->input('notes');
        if ($request->has('stops')) $route->stops = $request->input('stops');

        if (!$route->code) {
            $route->code = 'TUYEN-' . str_pad((string) (DeliveryRoute::count() + 1), 2, '0', STR_PAD_LEFT);
        }
        $route->save();

        // Đồng bộ 2 chiều: Cập nhật delivery_route_id cho Vehicle tương ứng
        if (!empty($route->assigned_vehicle_plate)) {
            Vehicle::where('plate_number', $route->assigned_vehicle_plate)->update(['delivery_route_id' => $route->id]);
        }

        return response()->json(['success' => true, 'route' => $route]);
    }

    /**
     * API: Toggle Route Active
     */
    public function toggleDeliveryRoute(Request $request)
    {
        $id = $request->input('id');
        $route = DeliveryRoute::find($id);
        if ($route) {
            $route->is_active = !$route->is_active;
            $route->save();
            return response()->json(['success' => true, 'route' => $route]);
        }
        return response()->json(['success' => false, 'message' => 'Không tìm thấy tuyến'], 404);
    }

    /**
     * API: Delete Delivery Route
     */
    public function deleteDeliveryRoute($id)
    {
        $route = DeliveryRoute::find($id);
        if ($route) {
            $route->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa tuyến giao hàng']);
        }
        return response()->json(['success' => false, 'message' => 'Không tìm thấy tuyến'], 404);
    }

    /**
     * Bootstrap data for Vehicle Assignments in SPA
     */
    public function getVehicleAssignmentsBootstrapData()
    {
        return VehicleAssignment::latest('id')->get()->map(function ($asg) {
            return [
                'id'            => (string) ($asg->code ?: "PC-{$asg->id}"),
                'vehicleId'     => (string) ($asg->vehicle_id ?? ''),
                'vehiclePlate'  => $asg->vehicle_plate,
                'driverName'    => $asg->driver_name,
                'driverPhone'   => $asg->driver_phone ?? '',
                'copilotName'   => $asg->copilot_name ?? '',
                'copilotPhone'  => $asg->copilot_phone ?? '',
                'managerName'   => $asg->manager_name ?? '',
                'managerPhone'  => $asg->manager_phone ?? '',
                'assignedDate'  => $asg->assigned_date ? $asg->assigned_date->format('Y-m-d') : date('Y-m-d'),
                'effectiveFrom' => $asg->effective_from ? $asg->effective_from->format('Y-m-d') : date('Y-m-d'),
                'effectiveTo'   => $asg->effective_to ?? 'Không thời hạn',
                'assignedBy'    => $asg->assigned_by ?? 'Hoàng Văn Khoa (Trưởng đội xe)',
                'shift'         => $asg->shift ?? 'Toàn thời gian (07:30 - 17:30)',
                'routeId'       => (string) ($asg->route_id ?? '1'),
                'routeZone'     => $asg->route_zone ?? 'Tuyến vận tải Mê Linh',
                'status'        => $asg->status ?? 'Đang áp dụng',
                'notes'         => $asg->notes ?? '',
            ];
        });
    }

    /**
     * API & Web: Get vehicle assignments
     */
    public function getVehicleAssignments(Request $request)
    {
        if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json(['assignments' => $this->getVehicleAssignmentsBootstrapData()]);
        }
        $query = http_build_query(array_merge(['tab' => 'vehicles', 'action' => 'assignment'], $request->all()));
        return redirect('/cat_vehicles?' . $query);
    }

    /**
     * API: Save vehicle assignment
     */
    public function saveVehicleAssignment(Request $request)
    {
        $id = $request->input('id');
        $code = $request->input('code') ?: ('PC-' . date('Y') . '-' . str_pad((string)(VehicleAssignment::count() + 82), 3, '0', STR_PAD_LEFT));

        $vehiclePlate = $request->input('vehiclePlate');
        $vehicleId = $request->input('vehicleId');
        $driverName = trim($request->input('driverName', ''));
        $driverPhone = trim($request->input('driverPhone', ''));
        $copilotName = trim($request->input('copilotName', ''));
        $copilotPhone = trim($request->input('copilotPhone', ''));
        $managerName = trim($request->input('managerName', ''));
        $managerPhone = trim($request->input('managerPhone', ''));
        $assignedDate = $request->input('assignedDate', date('Y-m-d'));
        $effectiveFrom = $request->input('effectiveFrom', date('Y-m-d'));
        $effectiveTo = $request->input('effectiveTo', 'Không thời hạn');
        $assignedBy = $request->input('assignedBy') ?: 'Hoàng Văn Khoa (Trưởng đội xe)';
        $shift = $request->input('shift') ?: 'Toàn thời gian (07:30 - 17:30)';
        $routeId = $request->input('routeId');
        $routeZone = $request->input('routeZone');
        $status = $request->input('status', 'Đang áp dụng');
        $notes = $request->input('notes', '');

        // If status is "Đang áp dụng", close previous active assignments for this vehicle
        if ($status === 'Đang áp dụng' && $vehiclePlate) {
            VehicleAssignment::where(function($q) use ($vehiclePlate, $vehicleId) {
                $q->where('vehicle_plate', $vehiclePlate);
                if ($vehicleId) {
                    $q->orWhere('vehicle_id', $vehicleId);
                }
            })
            ->where('status', 'Đang áp dụng')
            ->when($id, fn($q) => $q->where('id', '!=', $id)->where('code', '!=', $id))
            ->update([
                'status' => 'Hoàn thành',
                'effective_to' => date('Y-m-d')
            ]);
        }

        $data = [
            'code'           => $code,
            'vehicle_id'     => $vehicleId,
            'vehicle_plate'  => $vehiclePlate,
            'driver_name'    => $driverName,
            'driver_phone'   => $driverPhone,
            'copilot_name'   => $copilotName,
            'copilot_phone'  => $copilotPhone,
            'manager_name'   => $managerName,
            'manager_phone'  => $managerPhone,
            'assigned_date'  => $assignedDate,
            'effective_from' => $effectiveFrom,
            'effective_to'   => $effectiveTo,
            'assigned_by'    => $assignedBy,
            'shift'          => $shift,
            'route_id'       => $routeId,
            'route_zone'     => $routeZone,
            'status'         => $status,
            'notes'          => $notes,
        ];

        if ($id) {
            $asg = VehicleAssignment::where('id', $id)->orWhere('code', $id)->first();
            if ($asg) {
                $asg->update($data);
            } else {
                $asg = VehicleAssignment::create($data);
            }
        } else {
            $asg = VehicleAssignment::create($data);
        }

        // Update corresponding Vehicle model in DB
        $vehicle = null;
        if ($vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
        }
        if (!$vehicle && $vehiclePlate) {
            $vehicle = Vehicle::where('plate_number', $vehiclePlate)->first();
        }

        if ($vehicle) {
            $driverUser = User::where('name', $driverName)->first();
            if ($driverUser) {
                $vehicle->driver_id = $driverUser->id;
            }
            $vehicle->copilot_name = $copilotName;
            if ($routeId) {
                // Clear this route from any other vehicle to avoid conflict
                Vehicle::where('delivery_route_id', $routeId)->where('id', '!=', $vehicle->id)->update(['delivery_route_id' => null]);
                $vehicle->delivery_route_id = $routeId;
            }
            $vehicle->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Lưu và áp dụng phân công xe thành công!',
            'assignment' => [
                'id'            => (string) ($asg->code ?: "PC-{$asg->id}"),
                'vehicleId'     => (string) ($asg->vehicle_id ?? ''),
                'vehiclePlate'  => $asg->vehicle_plate,
                'driverName'    => $asg->driver_name,
                'driverPhone'   => $asg->driver_phone ?? '',
                'copilotName'   => $asg->copilot_name ?? '',
                'copilotPhone'  => $asg->copilot_phone ?? '',
                'managerName'   => $asg->manager_name ?? '',
                'managerPhone'  => $asg->manager_phone ?? '',
                'assignedDate'  => $asg->assigned_date ? $asg->assigned_date->format('Y-m-d') : date('Y-m-d'),
                'effectiveFrom' => $asg->effective_from ? $asg->effective_from->format('Y-m-d') : date('Y-m-d'),
                'effectiveTo'   => $asg->effective_to ?? 'Không thời hạn',
                'assignedBy'    => $asg->assigned_by ?? 'Hoàng Văn Khoa (Trưởng đội xe)',
                'shift'         => $asg->shift ?? 'Toàn thời gian (07:30 - 17:30)',
                'routeId'       => (string) ($asg->route_id ?? '1'),
                'routeZone'     => $asg->route_zone ?? 'Tuyến vận tải Mê Linh',
                'status'        => $asg->status ?? 'Đang áp dụng',
                'notes'         => $asg->notes ?? '',
            ],
            'vehicle' => $vehicle ? $vehicle->load(['driver', 'deliveryRoute']) : null
        ]);
    }

    /**
     * API: Terminate assignment
     */
    public function terminateVehicleAssignment(Request $request, $id = null)
    {
        $id = $id ?: $request->input('id');
        $asg = VehicleAssignment::where('id', $id)->orWhere('code', $id)->first();
        if (!$asg) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bản ghi phân công!'], 404);
        }
        $asg->update([
            'status' => 'Hoàn thành',
            'effective_to' => date('Y-m-d')
        ]);
        return response()->json(['success' => true, 'message' => 'Đã kết thúc đợt phân công!']);
    }

    /**
     * API: Delete assignment
     */
    public function deleteVehicleAssignment(Request $request, $id = null)
    {
        $id = $id ?: $request->input('id');
        $asg = VehicleAssignment::where('id', $id)->orWhere('code', $id)->first();
        if (!$asg) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bản ghi phân công!'], 404);
        }
        $asg->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa bản ghi phân công thành công!']);
    }

    /**
     * Bootstrap data for Delivery Returns in SPA
     */
    public function getDeliveryReturnsBootstrapData()
    {
        return \App\Models\DeliveryReturn::with(['items', 'order'])
            ->latest('id')
            ->get()
            ->map(function ($r) {
                return [
                    'id'                  => (string) $r->id,
                    'slipCode'            => $r->slip_code,
                    'orderId'             => (string) $r->order_id,
                    'orderCode'           => $r->order_code,
                    'dealerName'          => $r->dealer_name,
                    'vehiclePlate'        => $r->vehicle_plate,
                    'driverName'          => $r->driver_name,
                    'copilotName'         => $r->copilot_name,
                    'totalReturnedAmount' => (float) $r->total_returned_amount,
                    'status'              => $r->status,
                    'timestamp'           => $r->created_at ? $r->created_at->format('H:i d/m/Y') : date('H:i d/m/Y'),
                    'notes'               => $r->notes,
                    'items'               => $r->items->map(function ($it) {
                        return [
                            'sku'         => $it->sku,
                            'name'        => $it->name,
                            'unit'        => $it->unit,
                            'returnedQty' => (float) $it->returned_qty,
                            'unitPrice'   => (float) $it->unit_price,
                            'amount'      => (float) $it->amount,
                            'reason'      => $it->reason,
                        ];
                    }),
                ];
            });
    }

    /**
     * API: Get Delivery Returns
     */
    public function getDeliveryReturns()
    {
        return response()->json(['returns' => $this->getDeliveryReturnsBootstrapData()]);
    }

    /**
     * API: Save or Update Delivery Return
     */
    public function saveDeliveryReturn(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $slipCode = $data['slipCode'] ?? ($data['slip_code'] ?? null);
        $orderCode = $data['orderCode'] ?? ($data['order_code'] ?? null);

        if (!$slipCode && $orderCode) {
            $slipCode = 'TH-' . $orderCode;
        }

        $order = Order::where('order_code', $orderCode)
            ->orWhere('id', $data['orderId'] ?? ($data['order_id'] ?? 0))
            ->first();

        $returnSlip = \App\Models\DeliveryReturn::updateOrCreate(
            ['slip_code' => $slipCode],
            [
                'order_id'              => $order?->id ?? ($data['orderId'] ?? ($data['order_id'] ?? null)),
                'order_code'            => $orderCode ?: ($order?->order_code ?? null),
                'dealer_id'             => $order?->dealer_id,
                'dealer_name'           => $data['dealerName'] ?? ($order?->customer_name ?? null),
                'vehicle_plate'         => $data['vehiclePlate'] ?? ($order?->assigned_vehicle_plate ?? null),
                'driver_id'             => $order?->assigned_driver_id,
                'driver_name'           => $data['driverName'] ?? ($order?->assignedDriver?->name ?? null),
                'copilot_name'          => $data['copilotName'] ?? null,
                'total_returned_amount' => (float) ($data['totalReturnedAmount'] ?? 0),
                'status'                => $data['status'] ?? 'Chở trên xe',
                'notes'                 => $data['notes'] ?? null,
            ]
        );

        if (!empty($data['items']) && is_array($data['items'])) {
            $returnSlip->items()->delete();
            foreach ($data['items'] as $it) {
                \App\Models\DeliveryReturnItem::create([
                    'delivery_return_id' => $returnSlip->id,
                    'sku'                => $it['sku'] ?? 'SKU-PVC',
                    'name'               => $it['name'] ?? 'Vật tư',
                    'unit'               => $it['unit'] ?? 'cái',
                    'returned_qty'       => (float) ($it['returnedQty'] ?? ($it['returned_qty'] ?? 1)),
                    'unit_price'         => (float) ($it['unitPrice'] ?? ($it['unit_price'] ?? 0)),
                    'amount'             => (float) ($it['amount'] ?? 0),
                    'reason'             => $it['reason'] ?? 'Khách hoàn trả',
                ]);
            }
        }

        // Cập nhật lại đơn hàng
        if ($order) {
            $order->has_returns = true;
            $order->total_returned_amount = (float) ($data['totalReturnedAmount'] ?? 0);
            $order->return_slip_code = $slipCode;
            $order->save();

            // Cập nhật số lượng hoàn trả và giao thực tế cho từng item của đơn
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $it) {
                    $rQty = (float) ($it['returnedQty'] ?? ($it['returned_qty'] ?? 0));
                    if ($rQty > 0) {
                        $orderItem = $order->items->firstWhere('sku', $it['sku'] ?? '')
                            ?? (!empty($it['order_item_id']) ? $order->items->firstWhere('id', $it['order_item_id']) : null)
                            ?? $order->items->firstWhere('name', $it['name'] ?? '');
                        if ($orderItem) {
                            $orderItem->returned_qty = $rQty;
                            $orderItem->delivered_qty = max(0, (float)$orderItem->quantity - $rQty);
                            $orderItem->return_reason = $it['reason'] ?? 'Khách hoàn trả';
                            $orderItem->save();
                        }
                    }
                }
            }

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => now()->format('Y-m-d H:i:s'),
                    'performer_name'       => $data['driverName'] ?? ($order->assignedDriver?->name ?? 'Lái xe giao hàng'),
                    'user_id'              => $order->assigned_driver_id ?? auth()->id(),
                    'action'               => 'Lập Phiếu Hoàn Hàng',
                    'module'               => 'Vận Tải & Giao Nhận',
                    'target_document_code' => $order->order_code,
                    'field_name'           => 'Phiếu hoàn hàng tại điểm giao',
                    'old_value'            => null,
                    'new_value'            => $slipCode,
                    'notes'                => "Lập phiếu hoàn trả [{$slipCode}] gồm " . count($data['items'] ?? []) . " mặt hàng. Tổng tiền hoàn: " . number_format((float)($data['totalReturnedAmount'] ?? 0), 0, ',', '.') . " đ mang về kho nhập lại.",
                ]);
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => 'Lưu phiếu hoàn trả thành công',
            'return'  => $this->getDeliveryReturnsBootstrapData()->firstWhere('slipCode', $slipCode)
        ]);
    }

    /**
     * API: Xác nhận hàng hoàn trả đã được đưa vào nhập kho lại
     */
    public function intakeReturnWarehouse(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $id = $data['id'] ?? ($data['slipId'] ?? null);
        $slipCode = $data['slipCode'] ?? ($data['code'] ?? null);
        $slipIds = $data['slipIds'] ?? [];
        $vehiclePlate = $data['vehiclePlate'] ?? null;
        $action = $data['action'] ?? ($data['status'] === 'Chờ thủ kho duyệt nhập' ? 'submit' : 'approve');

        $intakeUserId = $data['intakeUserId'] ?? auth()->id();
        $intakeUser = $intakeUserId ? \App\Models\User::find($intakeUserId) : auth()->user();
        $intakeUserName = $data['intakeUserName'] ?? $intakeUser?->name ?? auth()->user()?->name ?? 'Thủ kho';
        $intakeUserId = $intakeUser?->id ?? auth()->id();

        $warehouseId = $data['warehouseId'] ?? null;
        $warehouse = $warehouseId ? \App\Models\Warehouse::find($warehouseId) : null;
        if (!$warehouse) {
            $warehouse = \App\Models\Warehouse::first();
        }
        $warehouseId = $warehouse?->id;
        $warehouseName = $warehouse?->name ?? 'Kho Tổng Mê Linh (MQ)';

        $query = \App\Models\DeliveryReturn::with('items');
        if (!empty($slipIds) && is_array($slipIds)) {
            $query->whereIn('id', $slipIds);
        } elseif ($id) {
            $query->where('id', $id);
        } elseif ($slipCode) {
            $query->where('slip_code', $slipCode);
        } elseif ($vehiclePlate) {
            $query->where('vehicle_plate', $vehiclePlate)
                  ->whereIn('status', ['Chở trên xe', 'Chờ thủ kho duyệt nhập', 'Chờ nhập kho lại']);
        }

        $slips = $query->get();
        if ($slips->isEmpty() && $vehiclePlate) {
            $slips = \App\Models\DeliveryReturn::with('items')
                ->where('vehicle_plate', $vehiclePlate)
                ->whereIn('status', ['Chở trên xe', 'Chờ thủ kho duyệt nhập', 'Chờ nhập kho lại'])
                ->get();
        }

        if ($slips->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu trả hàng cần xử lý'], 404);
        }

        // TRƯỜNG HỢP 1: Lái xe gửi yêu cầu bàn giao hàng hoàn trả cho thủ kho duyệt
        if ($action === 'submit' || $action === 'pending') {
            foreach ($slips as $returnSlip) {
                $returnSlip->update([
                    'status'              => 'Chờ thủ kho duyệt nhập',
                    'intake_by_user_id'   => $intakeUserId,
                    'intake_warehouse_id' => $warehouseId,
                    'notes'               => !empty($data['notes']) ? $data['notes'] : $returnSlip->notes,
                ]);

                try {
                    \App\Models\AuditLog::create([
                        'timestamp'            => now()->format('Y-m-d H:i:s'),
                        'performer_name'       => auth()->user()?->name ?? $returnSlip->driver_name ?? 'Lái xe',
                        'user_id'              => auth()->id(),
                        'action'               => 'Gửi Bàn Giao Hàng Hoàn Trả Cho Thủ Kho',
                        'module'               => 'Kho Vận & Bốc Xếp',
                        'target_document_code' => $returnSlip->slip_code,
                        'field_name'           => 'Bàn giao hàng trả về',
                        'old_value'            => 'Chở trên xe',
                        'new_value'            => 'Chờ thủ kho duyệt nhập',
                        'notes'                => "Xe {$returnSlip->vehicle_plate} đã lập biên bản gửi bàn giao {$returnSlip->slip_code} (" . number_format($returnSlip->total_returned_amount, 0, ',', '.') . " đ) cho Thủ kho [{$intakeUserName}] tại {$warehouseName} chờ kiểm đếm và duyệt nhập kho.",
                    ]);
                } catch (\Throwable $e) {}
            }

            return response()->json([
                'success'        => true,
                'action'         => 'submit',
                'message'        => "Đã gửi biên bản bàn giao {$slips->count()} phiếu hàng hoàn trả cho Thủ kho {$intakeUserName} chờ kiểm đếm và duyệt nhập kho.",
                'intakeUserName' => $intakeUserName,
                'count'          => $slips->count(),
            ]);
        }

        // TRƯỜNG HỢP 2: Thủ kho từ chối nhập kho
        if ($action === 'reject') {
            $rejectReason = $data['rejectReason'] ?? ($data['reason'] ?? 'Không đủ điều kiện nhập kho hoặc sai lệch số lượng');
            foreach ($slips as $returnSlip) {
                $rejectNote = "[TỪ CHỐI NHẬP KHO " . now()->format('H:i d/m/Y') . " - {$intakeUserName}]: " . $rejectReason;
                $newNotes = $returnSlip->notes ? $returnSlip->notes . "\n" . $rejectNote : $rejectNote;
                $returnSlip->update([
                    'status' => 'Đã từ chối nhập kho',
                    'notes'  => $newNotes,
                ]);

                try {
                    \App\Models\AuditLog::create([
                        'timestamp'            => now()->format('Y-m-d H:i:s'),
                        'performer_name'       => $intakeUserName,
                        'user_id'              => auth()->id(),
                        'action'               => 'Từ Chối Nhập Kho Hàng Hoàn Trả',
                        'module'               => 'Kho Vận & Bốc Xếp',
                        'target_document_code' => $returnSlip->slip_code,
                        'field_name'           => 'Từ chối nhập kho',
                        'old_value'            => $returnSlip->status,
                        'new_value'            => 'Đã từ chối nhập kho',
                        'notes'                => "Thủ kho {$intakeUserName} từ chối nhập kho phiếu {$returnSlip->slip_code}. Lý do: {$rejectReason}",
                    ]);
                } catch (\Throwable $e) {}
            }

            return response()->json([
                'success' => true,
                'action'  => 'reject',
                'message' => "Đã từ chối {$slips->count()} phiếu hàng hoàn trả.",
                'count'   => $slips->count(),
            ]);
        }

        // TRƯỜNG HỢP 3: Thủ kho xác nhận duyệt nhập kho -> TỰ ĐỘNG CỘNG LẠI TỒN KHO & ĐỒNG BỘ BẢNG KHO CHUNG
        foreach ($slips as $returnSlip) {
            $returnSlip->update([
                'status'              => 'Đã nhập kho lại',
                'intake_by_user_id'   => auth()->id() ?? $intakeUserId,
                'intake_warehouse_id' => $warehouseId,
                'intake_at'           => now(),
                'notes'               => !empty($data['notes']) ? $data['notes'] : $returnSlip->notes,
            ]);

            // Tìm kho xe lưu động nguồn
            $carWh = null;
            if (!empty($returnSlip->vehicle_plate)) {
                $carWh = \App\Models\Warehouse::where('code', 'KHO-XE-' . $returnSlip->vehicle_plate)->first();
            }

            // Dùng chung bảng inventory_transactions: Ghi nhận phiếu Nhập Kho Hoàn Xe
            $invTx = null;
            try {
                $invTx = \App\Models\InventoryTransaction::create([
                    'code'                 => 'NK-HOAN-' . $returnSlip->slip_code,
                    'type'                 => 'import',
                    'warehouse_id'         => $warehouseId,
                    'target_warehouse_id'  => $carWh?->id,
                    'created_by_id'        => auth()->id() ?? $intakeUserId ?? 1,
                    'approved_by_id'       => auth()->id() ?? $intakeUserId ?? 1,
                    'status'               => 'Hoàn thành',
                    'supplier_name'        => 'Hoàn xe: ' . ($returnSlip->vehicle_plate ?: 'Xe lưu động') . ($returnSlip->driver_name ? ' (' . $returnSlip->driver_name . ')' : ''),
                    'reference_order_code' => $returnSlip->order_code ?: $returnSlip->slip_code,
                    'total_amount'         => (float)($returnSlip->total_returned_amount ?? 0),
                    'notes'                => "[Nhập Hoàn Hàng Xe] Phiếu: {$returnSlip->slip_code} - Khách trả lại từ xe {$returnSlip->vehicle_plate}",
                ]);
            } catch (\Throwable $e) {}

            // Tự động cộng lại số lượng vào tồn kho cho từng sản phẩm & cập nhật bảng warehouse_stocks
            foreach ($returnSlip->items as $item) {
                $qty = (float) ($item->returned_qty ?? 0);
                if ($qty > 0) {
                    $prod = null;
                    if ($item->product_id) {
                        $prod = \App\Models\Product::find($item->product_id);
                    } elseif (!empty($item->sku)) {
                        $prod = \App\Models\Product::where('sku', $item->sku)->first();
                    }

                    if ($prod) {
                        // 1. Tăng tồn kho tổng
                        $prod->increment('stock_quantity', $qty);

                        // 2. Tăng tồn kho tại kho đích trong warehouse_stocks
                        $targetStock = \App\Models\WarehouseStock::firstOrCreate(
                            ['product_id' => $prod->id, 'warehouse_id' => $warehouseId],
                            ['stock_quantity' => 0, 'reserved_quantity' => 0]
                        );
                        $targetStock->increment('stock_quantity', $qty);

                        // 3. Giảm tồn kho tại kho xe lưu động (nếu có tồn trên xe)
                        if ($carWh) {
                            $carStock = \App\Models\WarehouseStock::where('product_id', $prod->id)
                                ->where('warehouse_id', $carWh->id)
                                ->first();
                            if ($carStock && $carStock->stock_quantity > 0) {
                                $carStock->decrement('stock_quantity', min($qty, $carStock->stock_quantity));
                            }
                        }

                        // 4. Lưu chi tiết vào bảng inventory_transaction_items
                        if ($invTx) {
                            \App\Models\InventoryTransactionItem::create([
                                'inventory_transaction_id' => $invTx->id,
                                'product_id'               => $prod->id,
                                'quantity'                 => $qty,
                                'import_price'             => (float)($item->unit_price ?? $prod->cost_price ?? 0),
                                'cost_price_at_transaction' => (float)($item->unit_price ?? $prod->cost_price ?? 0),
                                'notes'                    => "Nhập hoàn từ xe {$returnSlip->vehicle_plate} (phiếu {$returnSlip->slip_code})",
                            ]);
                        }
                    }
                }
            }

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => now()->format('Y-m-d H:i:s'),
                    'performer_name'       => $intakeUserName,
                    'user_id'              => auth()->id() ?? $intakeUserId,
                    'action'               => 'Nhập Kho Hàng Hoàn Trả',
                    'module'               => 'Kho Vận & Bốc Xếp',
                    'target_document_code' => $returnSlip->slip_code,
                    'field_name'           => 'Duyệt nhập kho hoàn trả',
                    'old_value'            => $returnSlip->status,
                    'new_value'            => 'Đã nhập kho lại',
                    'notes'                => "{$warehouseName} ({$intakeUserName}) đã kiểm đếm và duyệt nhập kho lại hàng hoàn trả từ xe {$returnSlip->vehicle_plate} theo phiếu {$returnSlip->slip_code} (" . number_format($returnSlip->total_returned_amount, 0, ',', '.') . " đ). Đã tự động cộng lại tồn kho cho các sản phẩm và đồng bộ sổ kho chung.",
                ]);
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success'        => true,
            'action'         => 'approve',
            'message'        => "Đã xác nhận duyệt nhập kho thành công {$slips->count()} phiếu trả hàng bởi {$intakeUserName}. Tồn kho các sản phẩm đã được tự động cộng lại.",
            'intakeUserName' => $intakeUserName,
            'count'          => $slips->count(),
        ]);
    }

    /**
     * Màn hình Quản lý Nhập Kho Hàng Hoàn Trả (Khách trả hàng)
     */
    public function returnsIntakePage(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = trim($request->query('search', ''));
        $warehouseId = $request->query('warehouse_id');

        $query = \App\Models\DeliveryReturn::with(['items', 'order.dealer']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($warehouseId) {
            $query->where('intake_warehouse_id', $warehouseId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('slip_code', 'like', "%{$search}%")
                  ->orWhere('order_code', 'like', "%{$search}%")
                  ->orWhere('vehicle_plate', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('dealer_name', 'like', "%{$search}%");
            });
        }

        $returns = $query->latest('id')->paginate(15)->withQueryString();

        // Thống kê nhanh theo số lượng phiếu và số lượng hàng hóa (không cần tiền)
        $totalPendingCount = \App\Models\DeliveryReturn::where('status', 'Chờ thủ kho duyệt nhập')->count();
        $totalPendingAmount = (float) \App\Models\DeliveryReturn::where('status', 'Chờ thủ kho duyệt nhập')->sum('total_returned_amount');
        $totalPendingItemsCount = (float) \App\Models\DeliveryReturnItem::whereHas('deliveryReturn', function($q) {
            $q->where('status', 'Chờ thủ kho duyệt nhập');
        })->sum('returned_qty');

        $approvedTodayCount = \App\Models\DeliveryReturn::where('status', 'Đã nhập kho lại')
            ->whereDate('intake_at', now()->toDateString())
            ->count();
        $approvedTodayAmount = (float) \App\Models\DeliveryReturn::where('status', 'Đã nhập kho lại')
            ->whereDate('intake_at', now()->toDateString())
            ->sum('total_returned_amount');
        $approvedTodayItemsCount = (float) \App\Models\DeliveryReturnItem::whereHas('deliveryReturn', function($q) {
            $q->where('status', 'Đã nhập kho lại')
              ->whereDate('intake_at', now()->toDateString());
        })->sum('returned_qty');

        $totalMonthApprovedCount = \App\Models\DeliveryReturn::where('status', 'Đã nhập kho lại')
            ->whereMonth('intake_at', now()->month)
            ->whereYear('intake_at', now()->year)
            ->count();
        $totalMonthAmount = (float) \App\Models\DeliveryReturn::where('status', 'Đã nhập kho lại')
            ->whereMonth('intake_at', now()->month)
            ->whereYear('intake_at', now()->year)
            ->sum('total_returned_amount');
        $totalMonthItemsCount = (float) \App\Models\DeliveryReturnItem::whereHas('deliveryReturn', function($q) {
            $q->where('status', 'Đã nhập kho lại')
              ->whereMonth('intake_at', now()->month)
              ->whereYear('intake_at', now()->year);
        })->sum('returned_qty');

        \App\Models\Warehouse::syncVehicleWarehouses();

        $warehouses = \App\Models\Warehouse::all();
        $physicalWarehouses = \App\Models\Warehouse::where('type', '!=', 'Kho xe lưu động')->where('is_active', true)->get();
        $warehouseStaff = \App\Models\User::where(function($q) {
            $q->whereIn('department', ['Warehouse', 'Kho', 'Kho vận', 'Vận hành', 'Executive', 'Ban Giám Đốc'])
              ->orWhereHas('roles', function($rq) {
                  $rq->whereIn('slug', ['warehouse', 'warehouse_staff', 'stocker', 'admin', 'manager', 'Director', 'Admin']);
              });
        })->where('status', '!=', 'Đã nghỉ việc')->get(['id', 'name', 'department', 'phone']);

        // 2. Dữ liệu Xuất Hàng Lẻ Lên Xe (Bảng van_sale_dispatches & inventory_transactions)
        $vanDispatches = \App\Models\VanSaleDispatch::with(['items.product', 'driver', 'warehouse'])
            ->latest('id')
            ->take(60)
            ->get();
        $totalVanDispatchesCount = \App\Models\VanSaleDispatch::count();
        $totalVanDispatchesAmount = (float) \App\Models\VanSaleDispatch::sum('total_amount');
        $totalVanDispatchesToday = \App\Models\VanSaleDispatch::whereDate('dispatch_date', today())->count();

        // 3. Dữ liệu Tồn Kho Xe Lưu Động (Dùng chung bảng warehouses & warehouse_stocks)
        $vehicleWarehouses = \App\Models\Warehouse::where('type', 'Kho xe lưu động')
            ->with(['stocks.product'])
            ->get()
            ->map(function ($wh) {
                $stocks = $wh->stocks->filter(fn($st) => (float)$st->stock_quantity > 0);
                $plate = str_replace('KHO-XE-', '', $wh->code);
                $totalQty = $stocks->sum('stock_quantity');
                $totalVal = $stocks->sum(function($st) {
                    return (float)$st->stock_quantity * (float)($st->product?->cost_price ?: 0);
                });
                return (object)[
                    'id'           => $wh->id,
                    'code'         => $wh->code,
                    'name'         => $wh->name,
                    'plate_number' => $plate,
                    'driver_name'  => $wh->manager_name,
                    'total_skus'   => $stocks->count(),
                    'total_qty'    => $totalQty,
                    'total_val'    => $totalVal,
                    'stocks'       => $stocks,
                ];
            });

        $vehicles = \App\Models\Vehicle::with('driver')->get();
        $productsCatalog = \App\Models\Product::select('id', 'name', 'sku', 'unit', 'cost_price', 'price_tiers', 'stock_quantity')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $p->retail_price = (float)($p->getPriceForTier('Đại lý Cấp 1') ?: ($p->getPriceForTier('Bán Lẻ') ?: $p->cost_price));
                return $p;
            });

        $activeTab = $request->query('tab', 'returns');

        return view('inventory.import', compact(
            'returns',
            'status',
            'search',
            'warehouseId',
            'totalPendingCount',
            'totalPendingAmount',
            'totalPendingItemsCount',
            'approvedTodayCount',
            'approvedTodayAmount',
            'approvedTodayItemsCount',
            'totalMonthApprovedCount',
            'totalMonthAmount',
            'totalMonthItemsCount',
            'warehouses',
            'physicalWarehouses',
            'warehouseStaff',
            'vanDispatches',
            'totalVanDispatchesCount',
            'totalVanDispatchesAmount',
            'totalVanDispatchesToday',
            'vehicleWarehouses',
            'vehicles',
            'productsCatalog',
            'activeTab'
        ));
    }

    /**
     * API: Hoàn đơn hàng còn trên xe về kho (trả hàng, cộng lại tồn kho, gỡ khỏi xe)
     */
    public function returnOrderToWarehouse(Request $request)
    {
        $orderId = $request->input('orderId');
        $orderCode = $request->input('orderCode');
        $warehouseName = $request->input('warehouseName', 'Kho Tổng Mê Linh (MQ)');
        $reason = $request->input('reason', 'Không giao được hàng, hoàn nhập lại kho');
        $performerName = auth()->user()?->name ?? 'Lái xe / Điều phối';
        $performerId = auth()->id();

        $order = Order::with('items')->where(function ($q) use ($orderId, $orderCode) {
            if ($orderId) $q->where('id', $orderId)->orWhere('order_code', $orderId);
            if ($orderCode) $q->orWhere('order_code', $orderCode);
        })->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Hoàn lại số lượng tồn kho nếu đơn đã từng bị trừ kho
        if ($order->stock_deducted) {
            app(\App\Http\Controllers\OrderController::class)->restoreOrderStock($order);
        }

        $oldStatus = $order->status;
        $oldVehiclePlate = $order->assigned_vehicle_plate;

        $order->status = 'Đã hoàn kho';
        $order->assigned_vehicle_plate = null;
        $order->assigned_driver_id = null;
        $order->notes = ($order->notes ? $order->notes . "\n" : '') . 
            "[HOÀN KHO " . now()->format('H:i d/m/Y') . "]: Nhập lại {$warehouseName} từ xe {$oldVehiclePlate}. Lý do: {$reason}. Người thực hiện: {$performerName}.";
        $order->save();

        // Cập nhật lại công nợ đại lý nếu có
        if ($order->dealer_id) {
            $dealer = \App\Models\Dealer::find($order->dealer_id);
            if ($dealer) {
                $dealer->recalculateCurrentDebt();
            }
        }

        // Ghi nhật ký audit
        try {
            \App\Models\AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $performerName,
                'user_id'              => $performerId,
                'action'               => 'Hoàn Hàng Về Kho',
                'module'               => 'Kho Vận & Giao Nhận',
                'target_document_code' => $order->order_code,
                'field_name'           => 'Trạng thái & Phân bổ xe',
                'old_value'            => "{$oldStatus} (Xe {$oldVehiclePlate})",
                'new_value'            => "Đã hoàn kho ({$warehouseName})",
                'notes'                => "Hoàn trả hàng về kho {$warehouseName}. Tồn kho đã được cộng lại. Lý do: {$reason}.",
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Đã hoàn hàng đơn {$order->order_code} về kho thành công và cập nhật tồn kho.",
            'order'   => [
                'id'                   => (string) $order->id,
                'orderCode'            => $order->order_code,
                'status'               => $order->status,
                'assignedVehiclePlate' => null,
                'assignedDriverId'     => null,
                'stockDeducted'        => (bool) $order->stock_deducted,
            ]
        ]);
    }

    /**
     * API: Điều chuyển đơn hàng còn trên xe sang xe khác
     */
    public function transferOrderVehicle(Request $request)
    {
        $orderId = $request->input('orderId');
        $orderCode = $request->input('orderCode');
        $orderIds = $request->input('orderIds', []);
        $orderCodes = $request->input('orderCodes', []);
        $targetVehiclePlate = trim($request->input('targetVehiclePlate') ?? '');
        $targetDriverId = $request->input('targetDriverId');
        $reason = $request->input('reason', 'Xếp đơn vào xe điều phối');
        $performerName = auth()->user()?->name ?? 'Điều phối / Lái xe';
        $performerId = auth()->id();

        // Build list of order identifiers
        $idsToFind = [];
        if (!empty($orderIds) && is_array($orderIds)) {
            $idsToFind = array_merge($idsToFind, $orderIds);
        }
        if (!empty($orderCodes) && is_array($orderCodes)) {
            $idsToFind = array_merge($idsToFind, $orderCodes);
        }
        if ($orderId) $idsToFind[] = $orderId;
        if ($orderCode) $idsToFind[] = $orderCode;
        $idsToFind = array_unique(array_filter($idsToFind));

        if (empty($idsToFind)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn đơn hàng cần điều phối'], 422);
        }

        $orders = Order::where(function ($q) use ($idsToFind) {
            $q->whereIn('id', $idsToFind)->orWhereIn('order_code', $idsToFind);
        })->get();

        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng trong hệ thống'], 404);
        }

        $targetVeh = null;
        if (!empty($targetVehiclePlate)) {
            $targetVeh = Vehicle::where('plate_number', $targetVehiclePlate)->first();
            if (!$targetDriverId && $targetVeh && $targetVeh->driver_id) {
                $targetDriverId = $targetVeh->driver_id;
            }
        }

        $updatedOrders = [];
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($orders as $order) {
                $oldPlate = $order->assigned_vehicle_plate;

                if (empty($targetVehiclePlate)) {
                    // Rút đơn khỏi xe về hàng chờ
                    $order->assigned_vehicle_plate = null;
                    $order->assigned_driver_id = null;
                    if ($order->status === 'Đang vận chuyển') {
                        $order->status = 'Đã bốc xong';
                    }
                    $order->notes = ($order->notes ? $order->notes . "\n" : '') . 
                        "[RÚT KHỎI XE " . now()->format('H:i d/m/Y') . "]: Rút đơn khỏi xe " . ($oldPlate ?: 'cũ') . " về danh sách chờ xếp xe. Lý do: {$reason}. Người thực hiện: {$performerName}.";
                    $order->save();

                    try {
                        \App\Models\AuditLog::create([
                            'timestamp'            => now()->format('Y-m-d H:i:s'),
                            'performer_name'       => $performerName,
                            'user_id'              => $performerId,
                            'action'               => 'Rút Đơn Khỏi Xe',
                            'module'               => 'Vận Tải & Giao Nhận',
                            'target_document_code' => $order->order_code,
                            'field_name'           => 'Phân bổ xe giao hàng',
                            'old_value'            => $oldPlate ? "Xe {$oldPlate}" : 'Chưa xếp xe',
                            'new_value'            => 'Hàng chờ xếp xe',
                            'notes'                => "Rút đơn về hàng chờ. Lý do: {$reason}.",
                        ]);
                    } catch (\Throwable $e) {}

                } else {
                    // Xếp đơn vào xe
                    $order->assigned_vehicle_plate = $targetVehiclePlate;
                    if ($targetDriverId) {
                        $order->assigned_driver_id = $targetDriverId;
                    }

                    if ($targetVeh && in_array($targetVeh->status, ['Đang di chuyển', 'Đang giao hàng'])) {
                        $order->status = 'Đang vận chuyển';
                    }
                    $order->failed_delivery_reason = null;
                    $order->failed_delivery_note = null;
                    $order->notes = ($order->notes ? $order->notes . "\n" : '') . 
                        "[XẾP XE " . now()->format('H:i d/m/Y') . "]: Xếp vào xe {$targetVehiclePlate}" . ($oldPlate ? " (từ xe {$oldPlate})" : "") . ". Lý do: {$reason}. Người thực hiện: {$performerName}.";
                    $order->save();

                    try {
                        \App\Models\AuditLog::create([
                            'timestamp'            => now()->format('Y-m-d H:i:s'),
                            'performer_name'       => $performerName,
                            'user_id'              => $performerId,
                            'action'               => 'Điều Phối Xe Giao Hàng',
                            'module'               => 'Vận Tải & Giao Nhận',
                            'target_document_code' => $order->order_code,
                            'field_name'           => 'Phân bổ xe giao hàng',
                            'old_value'            => $oldPlate ? "Xe {$oldPlate}" : 'Chưa xếp xe',
                            'new_value'            => "Xe {$targetVehiclePlate}",
                            'notes'                => "Xếp đơn vào xe {$targetVehiclePlate}. Lý do: {$reason}.",
                        ]);
                    } catch (\Throwable $e) {}
                }

                $updatedOrders[] = [
                    'id'                   => (string) $order->id,
                    'orderCode'            => $order->order_code,
                    'status'               => $order->status,
                    'assignedVehiclePlate' => $order->assigned_vehicle_plate,
                    'assignedDriverId'     => $order->assigned_driver_id ? (string) $order->assigned_driver_id : null,
                ];
            }
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $ex) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi khi lưu điều phối: ' . $ex->getMessage()], 500);
        }

        $count = count($updatedOrders);
        $msg = empty($targetVehiclePlate)
            ? ($count === 1 ? "Đã rút đơn {$updatedOrders[0]['orderCode']} về hàng chờ thành công!" : "Đã rút {$count} đơn hàng về hàng chờ thành công!")
            : ($count === 1 ? "Đã xếp đơn {$updatedOrders[0]['orderCode']} vào xe {$targetVehiclePlate} thành công!" : "Đã xếp {$count} đơn hàng vào xe {$targetVehiclePlate} thành công!");

        return response()->json([
            'success' => true,
            'message' => $msg,
            'count'   => $count,
            'orders'  => $updatedOrders,
            'order'   => $updatedOrders[0] ?? null,
        ]);
    }

    /**
     * API: Giải phóng các đơn hàng hoàn thành ra khỏi xe trong điều phối
     */
    public function releaseCompletedOrders(Request $request)
    {
        $vehiclePlate = $request->input('vehiclePlate');
        $orderCodes = $request->input('orderCodes', []);

        $query = Order::query();
        if ($vehiclePlate) {
            $query->where('assigned_vehicle_plate', $vehiclePlate);
        } else {
            $query->whereNotNull('assigned_vehicle_plate');
        }

        if (!empty($orderCodes) && is_array($orderCodes)) {
            $query->whereIn('order_code', $orderCodes);
        } else {
            $query->where(function ($q) {
                $q->whereIn('status', [
                    'Đã giao',
                    'Giao thành công',
                    'Đã hoàn thành',
                    'Đã quyết toán',
                    'Đã hoàn kho',
                    'Đã hủy',
                    'Hủy'
                ])->orWhere('settled_with_accountant', true);
            });
        }

        $orders = $query->get();
        $count = $orders->count();
        $affectedPlates = $orders->pluck('assigned_vehicle_plate')->filter()->unique();

        foreach ($orders as $ord) {
            $oldPlate = $ord->assigned_vehicle_plate;
            $ord->assigned_vehicle_plate = null;
            $ord->save();

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => now()->format('Y-m-d H:i:s'),
                    'performer_name'       => auth()->user()?->name ?? 'Điều Phối Xe',
                    'user_id'              => auth()->id(),
                    'action'               => 'Giải Phóng Đơn Hoàn Thành Khỏi Xe',
                    'module'               => 'Điều Phối Xe',
                    'target_document_code' => $ord->order_code,
                    'field_name'           => 'Xe vận tải',
                    'old_value'            => $oldPlate,
                    'new_value'            => null,
                    'notes'                => "Đưa đơn hàng đã hoàn thành ra khỏi xe [{$oldPlate}].",
                ]);
            } catch (\Throwable $e) {}
        }

        // Cập nhật trạng thái xe nếu không còn đơn nào đang giao
        foreach ($affectedPlates as $plate) {
            $remaining = Order::where('assigned_vehicle_plate', $plate)
                ->whereNotIn('status', [
                    'Đã giao',
                    'Giao thành công',
                    'Đã quyết toán',
                    'Đã hoàn kho',
                    'Đã hoàn thành',
                    'Đã hủy',
                    'Hủy'
                ])
                ->where('settled_with_accountant', false)
                ->count();

            if ($remaining === 0) {
                Vehicle::where('plate_number', $plate)->update([
                    'status' => 'Sẵn sàng',
                    'dispatch_status' => 'Rảnh',
                    'departure_time' => null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'count'   => $count,
            'message' => "Đã giải phóng {$count} đơn hàng hoàn thành ra khỏi xe.",
        ]);
    }

    /**
     * View: Giao Hàng & Thu Tiền (Dành cho Lái xe / Điều phối)
     */
    public function deliveryExecution(Request $request)
    {
        $user = auth()->user();

        // 1. Tìm các xe do user hiện tại phụ trách (Lái xe chính hoặc Phụ xe)
        $assignedVehicles = Vehicle::with(['driver', 'deliveryRoute'])
            ->where(function($q) use ($user) {
                if ($user) {
                    $q->where('driver_id', $user->id)
                      ->orWhere('copilot_name', $user->name);
                }
            })
            ->get();

        // Kiểm tra thêm theo bản ghi phân công xe đang áp dụng (VehicleAssignment)
        if ($user) {
            $assignmentPlates = \App\Models\VehicleAssignment::where('status', 'Đang áp dụng')
                ->where(function($q) use ($user) {
                    $q->where('driver_name', $user->name)
                      ->orWhere('copilot_name', $user->name);
                })
                ->pluck('vehicle_plate')
                ->filter()
                ->all();

            if (!empty($assignmentPlates)) {
                $moreVehicles = Vehicle::with(['driver', 'deliveryRoute'])
                    ->whereIn('plate_number', $assignmentPlates)
                    ->get();
                $assignedVehicles = $assignedVehicles->merge($moreVehicles)->unique('id');
            }
        }

        // Nếu user có xe phụ trách thì CHỈ hiển thị danh sách xe của user đó
        if ($assignedVehicles->isNotEmpty()) {
            $vehicles = $assignedVehicles;
            $isAssignedUser = true;
        } else {
            // Nếu là tài khoản quản trị/điều phối không trực tiếp lái xe thì hiển thị danh sách xe
            $vehicles = Vehicle::with(['driver', 'deliveryRoute'])->get();
            $isAssignedUser = false;
        }

        $selectedPlate = $request->query('vehicle') 
            ?? $vehicles->firstWhere('status', 'Đang di chuyển')?->plate_number 
            ?? $vehicles->first()?->plate_number;

        $selectedVehicle = $vehicles->firstWhere('plate_number', $selectedPlate) ?? $vehicles->first();

        $orders = [];
        if ($selectedVehicle) {
            $orders = Order::with(['items.product', 'dealer'])
                ->where('assigned_vehicle_plate', $selectedVehicle->plate_number)
                ->whereNotIn('status', ['Đã hoàn kho', 'Hủy', 'Đã hủy'])
                ->orderByRaw("CASE WHEN status IN ('Đã giao', 'Giao thành công') THEN 1 ELSE 0 END")
                ->orderBy('id', 'asc')
                ->get();

            // Gắn thông tin trả hàng và số lượng giao thực tế cho từng item của từng đơn
            $orderIds = $orders->pluck('id');
            $allReturns = \App\Models\DeliveryReturn::with('items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            foreach ($orders as $ord) {
                $orderRetSlips = $allReturns->get($ord->id, collect());
                $retItems = $orderRetSlips->flatMap->items;
                $hasRet = (bool)($ord->has_returns || $retItems->isNotEmpty() || $ord->items->contains(fn($it) => (float)($it->returned_qty ?? 0) > 0));
                if ($hasRet) {
                    $ord->has_returns = true;
                }

                foreach ($ord->items as $item) {
                    if (empty($item->returned_qty) || (float)$item->returned_qty == 0) {
                        $matchRet = $retItems->firstWhere('sku', $item->sku) 
                            ?? $retItems->firstWhere('name', $item->name);
                        if ($matchRet) {
                            $item->returned_qty = (float)$matchRet->returned_qty;
                        } else {
                            $item->returned_qty = 0;
                        }
                    }
                    $retQ = (float)($item->returned_qty ?? 0);
                    $item->delivered_qty = max(0, (float)$item->quantity - $retQ);
                }
            }
        }

        $accountants = \App\Models\User::where(function($q) {
            $q->whereIn('department', ['Accounting', 'Kế toán', 'Executive', 'Ban Giám Đốc'])
              ->orWhereHas('roles', function($rq) {
                  $rq->whereIn('slug', ['accountant', 'finance_accountant', 'sales_accountant', 'admin', 'manager', 'Director', 'Admin']);
              });
        })->where('status', '!=', 'Đã nghỉ việc')->get(['id', 'name', 'department', 'phone']);

        $warehouseStaff = \App\Models\User::where(function($q) {
            $q->whereIn('department', ['Warehouse', 'Kho', 'Executive', 'Ban Giám Đốc'])
              ->orWhereHas('roles', function($rq) {
                  $rq->whereIn('slug', ['warehouse', 'warehouse_staff', 'stocker', 'admin', 'manager', 'Director', 'Admin']);
              });
        })->where('status', '!=', 'Đã nghỉ việc')->get(['id', 'name', 'department', 'phone']);

        $deliveryReturns = \App\Models\DeliveryReturn::with('items')
            ->where('vehicle_plate', $selectedVehicle?->plate_number)
            ->whereIn('status', ['Chở trên xe', 'Chờ thủ kho duyệt nhập', 'Chờ nhập kho lại'])
            ->latest('id')
            ->get()
            ->map(function ($r) {
                return [
                    'id'                  => (string) $r->id,
                    'slipCode'            => $r->slip_code,
                    'slip_code'           => $r->slip_code,
                    'orderId'             => (string) $r->order_id,
                    'orderCode'           => $r->order_code,
                    'order_code'          => $r->order_code,
                    'dealerName'          => $r->dealer_name,
                    'dealer_name'         => $r->dealer_name,
                    'vehiclePlate'        => $r->vehicle_plate,
                    'driverName'          => $r->driver_name,
                    'copilotName'         => $r->copilot_name,
                    'totalReturnedAmount' => (float) $r->total_returned_amount,
                    'total_returned_amount' => (float) $r->total_returned_amount,
                    'status'              => $r->status,
                    'intakeUserId'        => $r->intake_by_user_id,
                    'intakeWarehouseId'   => $r->intake_warehouse_id,
                    'timestamp'           => $r->created_at ? $r->created_at->format('H:i d/m/Y') : date('H:i d/m/Y'),
                    'notes'               => $r->notes,
                    'items'               => $r->items->map(function ($it) {
                        return [
                            'id'          => $it->id,
                            'sku'         => $it->sku,
                            'name'        => $it->name,
                            'unit'        => $it->unit,
                            'returnedQty' => (float) $it->returned_qty,
                            'returned_qty' => (float) $it->returned_qty,
                            'unitPrice'   => (float) $it->unit_price,
                            'unit_price'  => (float) $it->unit_price,
                            'amount'      => (float) $it->amount,
                            'reason'      => $it->reason,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();

        $cashHandovers = DriverCashHandover::where('vehicle_plate', $selectedVehicle?->plate_number)
            ->latest('id')
            ->limit(20)
            ->get();

        $routes = DeliveryRoute::all();
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();

        // Hàng lẻ bán dạo / Hàng lưu động trên xe của tài xế
        $driverVanDispatches = collect();
        if ($selectedVehicle || $user) {
            $driverVanDispatches = \App\Models\VanSaleDispatch::with('items')
                ->where(function($q) use ($selectedVehicle, $user) {
                    if ($selectedVehicle) {
                        $q->where('vehicle_plate', $selectedVehicle->plate_number);
                    }
                    if ($user) {
                        $q->orWhere('driver_id', $user->id);
                    }
                })
                ->whereIn('status', ['dispatched', 'pending_settle'])
                ->latest('id')
                ->get();
        }
        $totalVanSaleDebt = (float)$driverVanDispatches->sum('total_amount');

        return view('delivery.execution', compact(
            'vehicles',
            'selectedVehicle',
            'selectedPlate',
            'orders',
            'deliveryReturns',
            'cashHandovers',
            'routes',
            'warehouses',
            'isAssignedUser',
            'accountants',
            'warehouseStaff',
            'driverVanDispatches',
            'totalVanSaleDebt'
        ));
    }

    /**
     * Web View: Kiểm COD / Kiểm đếm & Duyệt tiền COD từ lái xe (Menu Bán Hàng)
     */
    public function codVerificationPage(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = trim($request->query('search', ''));

        $query = DriverCashHandover::query();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('vehicle_plate', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('accountant_name', 'like', "%{$search}%")
                  ->orWhere('receipt_code', 'like', "%{$search}%");
            });
        }

        $handovers = $query->latest('id')->paginate(15)->withQueryString();

        // Thống kê nhanh
        $totalPendingCount = DriverCashHandover::where('status', 'Chờ kế toán duyệt')->count();
        $totalPendingAmount = (float) DriverCashHandover::where('status', 'Chờ kế toán duyệt')->sum('handover_amount');
        $approvedTodayCount = DriverCashHandover::where('status', 'Đã hoàn thành')
            ->whereDate('approved_at', now()->toDateString())
            ->count();
        $approvedTodayAmount = (float) DriverCashHandover::where('status', 'Đã hoàn thành')
            ->whereDate('approved_at', now()->toDateString())
            ->sum('handover_amount');
        $totalMonthAmount = (float) DriverCashHandover::where('status', 'Đã hoàn thành')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->sum('handover_amount');

        // Preload danh sách các mã đơn hàng để lấy thông tin chi tiết
        $allOrderCodes = [];
        foreach ($handovers as $h) {
            $codes = is_array($h->order_codes) ? $h->order_codes : (json_decode($h->order_codes, true) ?: []);
            $allOrderCodes = array_merge($allOrderCodes, $codes);
        }
        $allOrderCodes = array_unique(array_filter($allOrderCodes));

        $ordersMap = \App\Models\Order::whereIn('order_code', $allOrderCodes)
            ->with(['dealer', 'items'])
            ->get()
            ->keyBy('order_code');

        $accountants = \App\Models\User::where(function($q) {
            $q->whereIn('department', ['Accounting', 'Kế toán', 'Executive', 'Ban Giám Đốc'])
              ->orWhereHas('roles', function($rq) {
                  $rq->whereIn('slug', ['accountant', 'finance_accountant', 'sales_accountant', 'admin', 'manager', 'Director', 'Admin']);
              });
        })->where('status', '!=', 'Đã nghỉ việc')->get(['id', 'name', 'department', 'phone']);

        return view('orders.cod_verification', compact(
            'handovers',
            'ordersMap',
            'status',
            'search',
            'totalPendingCount',
            'totalPendingAmount',
            'approvedTodayCount',
            'approvedTodayAmount',
            'totalMonthAmount',
            'accountants'
        ));
    }

    /**
     * API: Bấm xuất bến (Lái xe bắt đầu lộ trình đi giao)
     */
    public function departTrip(Request $request)
    {
        $plate = $request->input('vehiclePlate');
        $vehicle = Vehicle::where('plate_number', $plate)->first();
        if (!$vehicle) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy xe'], 404);
        }

        // Lấy danh sách các đơn hàng còn trên xe cần giao
        $assignedOrders = Order::where('assigned_vehicle_plate', $plate)
            ->whereNotIn('status', ['Đã hoàn kho', 'Hủy', 'Đã hủy', 'Đã giao', 'Giao thành công'])
            ->get();

        if ($assignedOrders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xuất bến xe {$vehicle->plate_number}! Xe chưa có đơn hàng nào được xếp."
            ], 422);
        }

        // Kiểm tra xem tất cả các đơn hàng đã được kiểm xong chưa (phải là "Đã kiểm hàng")
        $uncheckedOrders = $assignedOrders->filter(function ($ord) {
            return !in_array($ord->status, ['Đã kiểm hàng', 'Đã kiểm', 'Đang vận chuyển']);
        });

        if ($uncheckedOrders->isNotEmpty()) {
            $codes = $uncheckedOrders->pluck('order_code')->implode(', ');
            return response()->json([
                'success' => false,
                'message' => "Chưa đủ điều kiện xuất bến xe {$vehicle->plate_number}! Còn {$uncheckedOrders->count()} đơn hàng chưa kiểm xong: [{$codes}]. Toàn bộ đơn hàng phải ở trạng thái \"Đã kiểm hàng\".",
                'unchecked_orders' => $uncheckedOrders->pluck('order_code')
            ], 422);
        }

        $now = now();
        $vehicle->status = 'Đang di chuyển';
        $vehicle->dispatch_status = 'Đang giao hàng';
        $vehicle->departure_time = $now;
        $vehicle->save();

        Order::where('assigned_vehicle_plate', $plate)
            ->whereIn('status', ['Đã kiểm hàng', 'Đã kiểm'])
            ->update([
                'status' => 'Đang vận chuyển'
            ]);

        try {
            \App\Models\AuditLog::create([
                'timestamp'            => $now->format('Y-m-d H:i:s'),
                'performer_name'       => auth()->user()?->name ?? ($vehicle->driver?->name ?? 'Lái xe'),
                'user_id'              => auth()->id() ?? $vehicle->driver_id,
                'action'               => 'Xuất Bến Giao Hàng',
                'module'               => 'Vận Tải & Giao Nhận',
                'target_document_code' => $vehicle->plate_number,
                'field_name'           => 'Trạng thái xe',
                'old_value'            => 'Sẵn sàng xuất bến',
                'new_value'            => 'Đang giao hàng',
                'notes'                => "Xe {$vehicle->plate_number} đã xuất bến bắt đầu hành trình giao hàng.",
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Xe {$vehicle->plate_number} đã xuất bến thành công! Chúc bạn chuyến đi an toàn.",
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * View: Trang riêng Xác Nhận Giao Đơn & Thu Tiền (Tách riêng trang độc lập)
     */
    public function confirmDeliveryPage($id)
    {
        $order = Order::with(['items.product', 'dealer'])->where('id', $id)->orWhere('order_code', $id)->firstOrFail();
        $dealer = $order->dealer;
        $previousDebt = $dealer ? $dealer->getDebtExcludingOrder($order->id) : 0;
        $creditLimit = (float)($dealer?->credit_limit ?? 0);
        $vehiclePlate = $order->assigned_vehicle_plate;
        $vehicle = $vehiclePlate ? Vehicle::where('plate_number', $vehiclePlate)->first() : null;

        $user = auth()->user();
        $roleSlug = strtolower($user?->roles?->first()?->slug ?? ($user?->role ?? ''));
        $userRole = strtolower($user?->role ?? '');
        $canApproveCredit = $user && (
            in_array($roleSlug, ['admin', 'director', 'manager', 'finance_accountant', 'sales_accountant', 'accountant']) ||
            in_array($userRole, ['admin', 'director', 'manager', 'finance_accountant', 'sales_accountant', 'accountant']) ||
            !empty($user->is_admin)
        );

        $salesStaff = User::whereIn('staff_type', ['company_sales', 'dealer_sales'])
            ->orWhere('department', 'LIKE', '%Kinh doanh%')
            ->select('id', 'name', 'phone', 'staff_type', 'dealer_id')
            ->orderBy('name')
            ->get();

        return view('delivery.confirm', compact('order', 'dealer', 'previousDebt', 'creditLimit', 'vehicle', 'vehiclePlate', 'canApproveCredit', 'salesStaff'));
    }

    /**
     * API: Lái xe xác nhận giao hàng & thu tiền (Màn hình chi tiết)
     */
    public function completeDeliveryOrder(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $orderId = $data['orderId'] ?? ($data['order_id'] ?? null);
        $orderCode = $data['orderCode'] ?? ($data['order_code'] ?? null);

        $order = Order::with('items')->where(function ($q) use ($orderId, $orderCode) {
            if ($orderId) $q->where('id', $orderId)->orWhere('order_code', $orderId);
            if ($orderCode) $q->orWhere('order_code', $orderCode);
        })->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $recipientName = $data['recipientName'] ?? ($data['customer_name'] ?? $order->customer_name);
        $deliveryNotes = $data['deliveryNotes'] ?? ($data['notes'] ?? '');
        $paymentMethod = $data['paymentMethod'] ?? ($data['payment_method'] ?? 'Tiền mặt');
        $customPaymentStatus = $data['paymentStatus'] ?? ($data['payment_status'] ?? null);
        $collectedAmount = (float)($data['collectedAmount'] ?? 0);
        $isDebt = !empty($data['isDebt']) || in_array($paymentMethod, ['Ký nợ', 'Ghi nợ', 'Ghi nợ đại lý', 'debt']) || in_array($customPaymentStatus, ['Ghi nợ', 'Ghi nợ đại lý']);
        $hasReturns = !empty($data['hasReturns']);
        $returnedItems = $data['returnedItems'] ?? [];

        // Hỗ trợ Phương án 2: Thanh toán phần vượt + Ký nợ phần trong hạn mức
        $isSplitPayment = !empty($data['isSplitPayment']);
        $splitCollectMethod = $data['splitCollectMethod'] ?? 'Tiền mặt';
        $splitCollectAmount = (float)($data['splitCollectAmount'] ?? 0);
        $splitDebtAmount = (float)($data['splitDebtAmount'] ?? 0);

        $totalReturned = 0;
        $returnSlipCode = null;

        // Xử lý hàng trả lại nếu có
        if ($hasReturns && !empty($returnedItems) && is_array($returnedItems)) {
            $returnSlipCode = 'TH-' . $order->order_code;
            foreach ($returnedItems as $rit) {
                $orderItem = (!empty($rit['order_item_id']) ? $order->items->firstWhere('id', (int)$rit['order_item_id']) : null)
                    ?? $order->items->firstWhere('sku', $rit['sku'] ?? '');
                $maxQty = $orderItem ? (float)$orderItem->quantity : 999999;
                $rQty = max(0, min((float)($rit['returnedQty'] ?? ($rit['returned_qty'] ?? 0)), $maxQty));
                $rPrice = (float)($rit['unitPrice'] ?? ($rit['unit_price'] ?? ($orderItem?->unit_price ?? 0)));
                if ($rQty > 0) {
                    $totalReturned += ($rQty * $rPrice);
                }
            }

            $returnSlip = \App\Models\DeliveryReturn::updateOrCreate(
                ['slip_code' => $returnSlipCode],
                [
                    'order_id'              => $order->id,
                    'order_code'            => $order->order_code,
                    'dealer_id'             => $order->dealer_id,
                    'dealer_name'           => $order->customer_name,
                    'vehicle_plate'         => $order->assigned_vehicle_plate,
                    'driver_id'             => $order->assigned_driver_id,
                    'driver_name'           => $order->assignedDriver?->name ?? 'Lái xe',
                    'total_returned_amount' => $totalReturned,
                    'status'                => 'Chở trên xe',
                    'notes'                 => "Hàng khách trả lại lúc giao hàng đơn {$order->order_code}",
                ]
            );

            $returnSlip->items()->delete();
            foreach ($returnedItems as $rit) {
                $orderItem = (!empty($rit['order_item_id']) ? $order->items->firstWhere('id', (int)$rit['order_item_id']) : null)
                    ?? $order->items->firstWhere('sku', $rit['sku'] ?? '');
                $maxQty = $orderItem ? (float)$orderItem->quantity : 999999;
                $rQty = max(0, min((float)($rit['returnedQty'] ?? ($rit['returned_qty'] ?? 0)), $maxQty));
                $unitPrice = (float)($rit['unitPrice'] ?? ($rit['unit_price'] ?? ($orderItem?->unit_price ?? 0)));

                if ($rQty > 0) {
                    \App\Models\DeliveryReturnItem::create([
                        'delivery_return_id' => $returnSlip->id,
                        'order_item_id'      => $rit['order_item_id'] ?? ($orderItem?->id),
                        'product_id'         => $rit['product_id'] ?? ($orderItem?->product_id),
                        'sku'                => $rit['sku'] ?? ($orderItem?->sku ?? ''),
                        'name'               => $rit['name'] ?? ($orderItem?->name ?? ($orderItem?->product_name ?? 'Sản phẩm')),
                        'unit'               => $rit['unit'] ?? ($orderItem?->unit ?? 'cái'),
                        'returned_qty'       => $rQty,
                        'unit_price'         => $unitPrice,
                        'amount'             => $rQty * $unitPrice,
                        'reason'             => $rit['reason'] ?? ($rit['returnReason'] ?? 'Khách trả hàng'),
                    ]);

                    if ($orderItem) {
                        $orderItem->returned_qty = $rQty;
                        $orderItem->delivered_qty = max(0, (float)$orderItem->quantity - $rQty);
                        $orderItem->return_reason = $rit['reason'] ?? ($rit['returnReason'] ?? 'Khách trả hàng');
                        $orderItem->save();
                    }
                }
            }

            $order->has_returns = true;
            $order->total_returned_amount = $totalReturned;
            $order->return_slip_code = $returnSlipCode;
        }

        // Cập nhật trạng thái đơn hàng & thanh toán
        $finalOrderAmount = max(0, (float)$order->total_amount - $totalReturned);
        $order->status = 'Đã giao';

        if ($isSplitPayment) {
            $isDebt = false;
            $order->payment_method = ($splitCollectMethod === 'VietQR' ? 'VietQR' : 'Tiền mặt') . ($splitDebtAmount > 0 ? ' + Ghi nợ' : '');
            $order->payment_status = ($splitDebtAmount > 0) ? 'Thanh toán một phần' : 'Đã thanh toán';
            if ($splitCollectMethod === 'VietQR') {
                $order->driver_debt_amount = 0;
            } else {
                $order->driver_debt_amount = $splitCollectAmount;
            }

            // Ghi nhận Phiếu thu đối với khoản tiền thu ngay để cấn trừ nợ chính xác
            if ($splitCollectAmount > 0) {
                try {
                    $note = "Thu tiền khi giao đơn {$order->order_code}: " . number_format($splitCollectAmount, 0, ',', '.') . " đ ({$splitCollectMethod}).";
                    if ($splitDebtAmount > 0) {
                        $note .= " Còn ghi nợ đơn: " . number_format($splitDebtAmount, 0, ',', '.') . " đ";
                    } else {
                        $note .= " Đơn hàng thanh toán đủ.";
                    }
                    \App\Models\Transaction::create([
                        'code'                  => 'PT-' . $order->order_code . '-PART',
                        'type'                  => 'Thu',
                        'fund_id'               => ($splitCollectMethod === 'VietQR' ? 2 : 1),
                        'created_by_id'         => auth()->id() ?? 1,
                        'category'              => 'Thu nợ đại lý',
                        'amount'                => $splitCollectAmount,
                        'customer_partner_name' => $order->customer_name,
                        'order_id'              => $order->id,
                        'date'                  => now()->format('Y-m-d'),
                        'notes'                 => $note,
                    ]);
                } catch (\Throwable $e) {}
            }
        } elseif ($isDebt) {
            $order->payment_status = $customPaymentStatus ?: 'Ghi nợ đại lý';
            $order->payment_method = $paymentMethod ?: 'Ghi nợ đại lý';
            $order->driver_debt_amount = 0;

            // Xử lý bàn giao công nợ cho Sale công ty hoặc Sale đại lý phụ thuộc (2 bước)
            $handoverToUserId = $data['handoverToUserId'] ?? ($data['handover_to_user_id'] ?? null);
            if ($handoverToUserId) {
                $toUser = \App\Models\User::find($handoverToUserId);
                if ($toUser) {
                    $handoverCode = 'BGCN-' . date('Ymd') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);
                    $targetDealerId = ($toUser->staff_type === 'dealer_sales') ? $toUser->dealer_id : null;
                    $targetDealerName = $targetDealerId ? \App\Models\Dealer::find($targetDealerId)?->name : null;

                    \App\Models\DebtHandover::create([
                        'code'               => $handoverCode,
                        'order_id'           => $order->id,
                        'order_code'         => $order->order_code,
                        'from_user_id'       => auth()->id() ?? $order->assigned_driver_id,
                        'from_user_name'     => auth()->user()?->name ?? ($order->assignedDriver?->name ?? 'Lái xe'),
                        'to_user_id'         => $toUser->id,
                        'to_user_name'       => $toUser->name,
                        'to_staff_type'      => $toUser->staff_type ?? 'company_sales',
                        'target_dealer_id'   => $targetDealerId,
                        'target_dealer_name' => $targetDealerName,
                        'amount'             => $finalOrderAmount,
                        'handover_type'      => 'order_debt',
                        'status'             => 'pending',
                        'notes'              => "Lái xe bàn giao công nợ sau khi giao hàng đơn {$order->order_code} cho [" . $toUser->name . "].",
                        'created_by_id'      => auth()->id(),
                    ]);

                    \App\Models\Announcement::create([
                        'title'             => "📑 Bàn giao nợ đơn {$order->order_code}: " . number_format($finalOrderAmount, 0, ',', '.') . " đ (Chờ xác nhận)",
                        'content'           => "Lái xe " . (auth()->user()?->name ?? 'Giao hàng') . " vừa giao xong đơn {$order->order_code} và lập biên bản {$handoverCode} bàn giao nợ cho bạn.\nVui lòng vào menu 'Quản lý Công nợ' để bấm 'Xác nhận nhận nợ'.",
                        'author'            => auth()->user()?->name ?? 'Lái xe',
                        'author_role'       => 'Vận tải',
                        'priority'          => 'Khẩn cấp',
                        'target_department' => 'Kinh doanh',
                        'read_by'           => [],
                    ]);
                }
            }
        } elseif (in_array($paymentMethod, ['VietQR', 'Chuyển khoản VietQR', 'Chuyển khoản', 'bank'])) {
            $order->payment_status = $customPaymentStatus ?: 'Đã thanh toán';
            $order->payment_method = 'Chuyển khoản VietQR';
            $order->driver_debt_amount = 0;
        } else {
            $order->payment_method = $paymentMethod ?: 'Tiền mặt';
            $order->driver_debt_amount = $collectedAmount;
            if ($customPaymentStatus) {
                $order->payment_status = $customPaymentStatus;
            } elseif ($collectedAmount >= $finalOrderAmount && $finalOrderAmount > 0) {
                $order->payment_status = 'Đã thanh toán';
            } elseif ($collectedAmount > 0) {
                $order->payment_status = 'Thanh toán một phần';
            } else {
                $order->payment_status = 'Chưa thanh toán';
            }
        }

        $nowStr = now()->format('H:i d/m/Y');
        $noteAppend = "[GIAO HÀNG THÀNH CÔNG {$nowStr}]: Người nhận: {$recipientName}. " .
            ($hasReturns ? "Có trả hàng: " . number_format($totalReturned, 0, ',', '.') . " đ. " : "Đã giao đủ 100%. ") .
            ($isSplitPayment 
                ? "Thanh toán kết hợp: Đã thu phần vượt " . number_format($splitCollectAmount, 0, ',', '.') . " đ ({$splitCollectMethod}), ghi nợ " . number_format($splitDebtAmount, 0, ',', '.') . " đ. "
                : "Thu: " . number_format($collectedAmount, 0, ',', '.') . " đ ({$order->payment_method}). ") .
            ($order->credit_overrun_status === 'Đã duyệt' ? "[Bảo lãnh nợ duyệt bởi {$order->credit_overrun_approved_name}] " : "") .
            ($deliveryNotes ? "Ghi chú: {$deliveryNotes}" : "");
        $order->notes = ($order->notes ? $order->notes . "\n" : '') . $noteAppend;
        $order->save();

        if ($order->dealer_id) {
            $dealer = \App\Models\Dealer::find($order->dealer_id);
            if ($dealer) {
                $dealer->recalculateCurrentDebt();
            }
        }

        try {
            \App\Models\AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => auth()->user()?->name ?? ($order->assignedDriver?->name ?? 'Lái xe giao hàng'),
                'user_id'              => auth()->id() ?? $order->assigned_driver_id,
                'action'               => 'Xác Nhận Giao Hàng & Thu Tiền',
                'module'               => 'Vận Tải & Giao Nhận',
                'target_document_code' => $order->order_code,
                'field_name'           => 'Giao hàng',
                'old_value'            => 'Đang vận chuyển',
                'new_value'            => 'Đã giao',
                'notes'                => "Lái xe xác nhận giao đơn {$order->order_code} cho [{$recipientName}]. Phương thức: {$order->payment_method}. " .
                    ($isSplitPayment ? "Thu ngay: " . number_format($splitCollectAmount) . " đ, nợ: " . number_format($splitDebtAmount) . " đ" : "Tiền thu: " . number_format($collectedAmount) . " đ"),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Đã xác nhận giao hàng đơn {$order->order_code} thành công!",
            'order'   => [
                'id'                 => (string) $order->id,
                'orderCode'          => $order->order_code,
                'status'             => $order->status,
                'paymentStatus'      => $order->payment_status,
                'paymentMethod'      => $order->payment_method,
                'driverDebtAmount'   => (float) $order->driver_debt_amount,
                'hasReturns'         => (bool) $order->has_returns,
                'totalReturnedAmount'=> (float) $order->total_returned_amount,
            ]
        ]);
    }

    /**
     * API: Lái xe gửi yêu cầu phê duyệt bảo lãnh nợ vượt hạn mức
     */
    public function requestCreditOverride(Request $request, $id)
    {
        $order = Order::where('id', $id)->orWhere('order_code', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $reason = $request->input('reason', 'Đại lý xin nợ vượt hạn mức');
        $overrunAmount = (float) $request->input('overrunAmount', 0);

        $order->credit_overrun_status = 'Chờ duyệt';
        $order->credit_overrun_amount = $overrunAmount;
        $order->credit_overrun_reason = $reason;
        $order->save();

        $orderCreatorName = $order->order_creator 
            ?: ($order->creator?->name 
            ?: ($order->salesRep?->name 
            ?: ($order->dealer?->staff_in_charge ?: 'Người lên đơn')));
        $orderCreatorId = $order->creator_id ?: ($order->sales_rep_id ?: null);
        $driverName = auth()->user()?->name ?? 'Lái xe giao hàng';

        try {
            \App\Models\AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $driverName,
                'user_id'              => auth()->id(),
                'action'               => 'Gửi Yêu Cầu Duyệt Nợ Vượt Hạn Mức',
                'module'               => 'Vận Tải & Công Nợ',
                'target_document_code' => $order->order_code,
                'field_name'           => 'credit_overrun_status',
                'old_value'            => null,
                'new_value'            => 'Chờ duyệt',
                'notes'                => "Yêu cầu duyệt vượt hạn mức " . number_format($overrunAmount, 0, ',', '.') . " đ cho đơn {$order->order_code}. Lý do: {$reason}",
            ]);
        } catch (\Throwable $e) {}

        // Gửi thông báo Realtime PickingSyncEvent về cho tài khoản lên đơn (người tạo đơn / sales rep) và kế toán
        try {
            \App\Models\PickingSyncEvent::create([
                'order_id'            => (string) $order->id,
                'event_type'          => 'CREDIT_OVERRUN_REQUESTED',
                'performer_name'      => (string) $driverName,
                'assigned_staff'      => (string) $orderCreatorName,
                'assigned_staff_list' => array_values(array_unique(array_filter([
                    $orderCreatorName,
                    $order->order_creator,
                    $order->creator?->name,
                    $order->salesRep?->name
                ]))),
                'order_status'        => 'Chờ duyệt vượt hạn mức',
                'shortage_reported'   => false,
                'extra_data'          => [
                    'orderId'         => (int) $order->id,
                    'orderCode'       => $order->order_code,
                    'customerName'    => $order->dealer?->name ?? $order->customer_name,
                    'customerPhone'   => $order->customer_phone,
                    'dealerName'      => $order->dealer?->name ?? $order->customer_name,
                    'orderCreator'    => $orderCreatorName,
                    'orderCreatorId'  => $orderCreatorId,
                    'driverName'      => $driverName,
                    'overrunAmount'   => (float) $overrunAmount,
                    'finalAmount'     => (float) $order->total_amount,
                    'reason'          => $reason,
                    'requestedAt'     => now()->format('H:i:s d/m/Y'),
                    'message'         => "Lái xe {$driverName} xin duyệt bảo lãnh nợ vượt hạn mức " . number_format($overrunAmount, 0, ',', '.') . " đ cho đơn {$order->order_code} của đại lý " . ($order->dealer?->name ?? $order->customer_name),
                ],
                'created_at'          => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Lỗi dispatch realtime CREDIT_OVERRUN_REQUESTED: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi yêu cầu duyệt bảo lãnh nợ vượt hạn mức thành công! Đang chờ Kế toán hoặc Ban Giám Đốc xác nhận.',
            'status'  => 'Chờ duyệt',
            'order'   => [
                'id'                   => (string) $order->id,
                'orderCode'            => $order->order_code,
                'credit_overrun_status'=> $order->credit_overrun_status,
                'credit_overrun_amount'=> (float)$order->credit_overrun_amount,
                'credit_overrun_reason'=> $order->credit_overrun_reason,
            ]
        ]);
    }

    /**
     * API: Lấy danh sách các yêu cầu bảo lãnh nợ vượt hạn mức đang chờ duyệt
     * Giúp người tạo đơn / Ban Giám Đốc / Kế toán nhận được ngay danh sách chờ duyệt trên mọi trang
     */
    public function getPendingCreditOverrides(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'orders' => []]);
        }

        $canApproveAll = $user->id === 1 
            || $user->hasRole('admin') 
            || $user->hasRole('Admin') 
            || $user->hasRole('manager') 
            || $user->hasRole('Manager')
            || $user->hasRole('accountant')
            || $user->hasRole('finance_accountant')
            || $user->hasRole('sales_accountant')
            || $user->hasPermission('orders.approve')
            || $user->hasPermission('debt.approve')
            || in_array($user->department, ['Executive', 'Ban Giám Đốc', 'Quản Lý', 'Kế Toán']);

        $query = Order::with(['dealer', 'creator', 'salesRep', 'assignedDriver'])
            ->where('credit_overrun_status', 'Chờ duyệt');

        if (!$canApproveAll) {
            // Lọc các đơn do user này tạo, bán hoặc phụ trách
            $userName = $user->name;
            $query->where(function($q) use ($user, $userName) {
                $q->where('creator_id', $user->id)
                  ->orWhere('sales_rep_id', $user->id)
                  ->orWhere('order_creator', $userName)
                  ->orWhereHas('dealer', function($dq) use ($userName) {
                      $dq->where('staff_in_charge', $userName);
                  });
            });
        }

        $pendingOrders = $query->orderBy('updated_at', 'desc')->limit(20)->get();

        $formatted = $pendingOrders->map(function ($order) {
            $creatorName = $order->order_creator 
                ?: ($order->creator?->name 
                ?: ($order->salesRep?->name 
                ?: ($order->dealer?->staff_in_charge ?: 'Người lên đơn')));

            $driverName = $order->assignedDriver?->name 
                ?: ($order->assigned_driver_id ?: 'Lái xe giao hàng');

            return [
                'orderId'       => $order->id,
                'orderCode'     => $order->order_code,
                'customerName'  => $order->dealer?->name ?? $order->customer_name,
                'customerPhone' => $order->customer_phone,
                'orderCreator'  => $creatorName,
                'orderCreatorId'=> $order->creator_id ?: ($order->sales_rep_id ?: null),
                'driverName'    => $driverName,
                'overrunAmount' => (float) $order->credit_overrun_amount,
                'finalAmount'   => (float) $order->total_amount,
                'reason'        => $order->credit_overrun_reason,
                'requestedAt'   => $order->updated_at ? $order->updated_at->format('H:i:s d/m/Y') : now()->format('H:i:s d/m/Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'orders'  => $formatted
        ]);
    }

    /**
     * API: Kế toán / Giám đốc duyệt hoặc từ chối bảo lãnh nợ vượt hạn mức
     */
    public function approveCreditOverride(Request $request, $id)
    {
        $order = Order::where('id', $id)->orWhere('order_code', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $user = auth()->user();
        $decision = $request->input('decision', 'approve');
        $notes = $request->input('note', '');

        $approverName = $user?->name ?? 'Kế toán/Quản lý';
        $now = now();

        if ($decision === 'approve') {
            $order->credit_overrun_status = 'Đã duyệt';
            $order->credit_overrun_approved_by = $user?->id;
            $order->credit_overrun_approved_name = $approverName;
            $order->credit_overrun_approved_at = $now;
            if ($notes) {
                $order->credit_overrun_reason = ($order->credit_overrun_reason ? $order->credit_overrun_reason . ' | ' : '') . "Duyệt: {$notes}";
            }
            $order->save();

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => $now->format('Y-m-d H:i:s'),
                    'performer_name'       => $approverName,
                    'user_id'              => $user?->id,
                    'action'               => 'Phê Duyệt Bảo Lãnh Vượt Hạn Mức',
                    'module'               => 'Vận Tải & Công Nợ',
                    'target_document_code' => $order->order_code,
                    'field_name'           => 'credit_overrun_status',
                    'old_value'            => 'Chờ duyệt',
                    'new_value'            => 'Đã duyệt',
                    'notes'                => "{$approverName} đã duyệt bảo lãnh nợ vượt hạn mức cho đơn {$order->order_code}." . ($notes ? " Ghi chú: {$notes}" : ""),
                ]);
            } catch (\Throwable $e) {}

            try {
                \App\Models\PickingSyncEvent::create([
                    'order_id'            => (string) $order->id,
                    'event_type'          => 'CREDIT_OVERRUN_DECIDED',
                    'performer_name'      => (string) $approverName,
                    'assigned_staff'      => (string) ($order->assigned_driver_id ?? 'Lái xe giao hàng'),
                    'order_status'        => 'Đã duyệt',
                    'shortage_reported'   => false,
                    'extra_data'          => [
                        'orderId'         => (int) $order->id,
                        'orderCode'       => $order->order_code,
                        'decision'        => 'approve',
                        'status'          => 'Đã duyệt',
                        'approverName'    => $approverName,
                        'approvedAt'      => $now->format('H:i d/m/Y'),
                        'notes'           => $notes,
                        'message'         => "Yêu cầu bảo lãnh nợ đơn {$order->order_code} đã được duyệt bởi {$approverName}",
                    ],
                    'created_at'          => now(),
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => "Đã phê duyệt bảo lãnh nợ vượt hạn mức cho đơn {$order->order_code} thành công!",
                'status'  => 'Đã duyệt',
                'approvedByName' => $approverName,
                'approvedAt' => $now->format('H:i d/m/Y'),
            ]);
        } else {
            $order->credit_overrun_status = 'Từ chối';
            $order->credit_overrun_approved_by = $user?->id;
            $order->credit_overrun_approved_name = $approverName;
            $order->credit_overrun_approved_at = $now;
            if ($notes) {
                $order->credit_overrun_reason = ($order->credit_overrun_reason ? $order->credit_overrun_reason . ' | ' : '') . "Từ chối: {$notes}";
            }
            $order->save();

            try {
                \App\Models\AuditLog::create([
                    'timestamp'            => $now->format('Y-m-d H:i:s'),
                    'performer_name'       => $approverName,
                    'user_id'              => $user?->id,
                    'action'               => 'Từ Chối Bảo Lãnh Vượt Hạn Mức',
                    'module'               => 'Vận Tải & Công Nợ',
                    'target_document_code' => $order->order_code,
                    'field_name'           => 'credit_overrun_status',
                    'old_value'            => 'Chờ duyệt',
                    'new_value'            => 'Từ chối',
                    'notes'                => "{$approverName} từ chối bảo lãnh nợ vượt hạn mức cho đơn {$order->order_code}." . ($notes ? " Lý do: {$notes}" : ""),
                ]);
            } catch (\Throwable $e) {}

            try {
                \App\Models\PickingSyncEvent::create([
                    'order_id'            => (string) $order->id,
                    'event_type'          => 'CREDIT_OVERRUN_DECIDED',
                    'performer_name'      => (string) $approverName,
                    'assigned_staff'      => (string) ($order->assigned_driver_id ?? 'Lái xe giao hàng'),
                    'order_status'        => 'Từ chối',
                    'shortage_reported'   => false,
                    'extra_data'          => [
                        'orderId'         => (int) $order->id,
                        'orderCode'       => $order->order_code,
                        'decision'        => 'reject',
                        'status'          => 'Từ chối',
                        'approverName'    => $approverName,
                        'approvedAt'      => $now->format('H:i d/m/Y'),
                        'notes'           => $notes,
                        'message'         => "Yêu cầu bảo lãnh nợ đơn {$order->order_code} đã bị từ chối bởi {$approverName}",
                    ],
                    'created_at'          => now(),
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => "Đã từ chối bảo lãnh nợ vượt hạn mức cho đơn {$order->order_code}.",
                'status'  => 'Từ chối',
                'approvedByName' => $approverName,
                'approvedAt' => $now->format('H:i d/m/Y'),
            ]);
        }
    }

    /**
     * API: Tra cứu trạng thái phê duyệt bảo lãnh nợ vượt hạn mức (polling realtime)
     */
    public function getCreditOverrideStatus($id)
    {
        $order = Order::where('id', $id)->orWhere('order_code', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        return response()->json([
            'success'               => true,
            'status'                => $order->credit_overrun_status,
            'amount'                => (float) $order->credit_overrun_amount,
            'approvedByName'        => $order->credit_overrun_approved_name,
            'approvedAt'            => $order->credit_overrun_approved_at ? \Carbon\Carbon::parse($order->credit_overrun_approved_at)->format('H:i d/m/Y') : null,
            'reason'                => $order->credit_overrun_reason,
        ]);
    }

    /**
     * API: Báo giao hàng không thành công
     */
    public function failDeliveryOrder(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $orderId = $data['orderId'] ?? ($data['order_id'] ?? null);
        $orderCode = $data['orderCode'] ?? ($data['order_code'] ?? null);

        $order = Order::where(function ($q) use ($orderId, $orderCode) {
            if ($orderId) $q->where('id', $orderId)->orWhere('order_code', $orderId);
            if ($orderCode) $q->orWhere('order_code', $orderCode);
        })->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $reason = $data['reason'] ?? 'Khách hẹn giao lại';
        $notes = $data['notes'] ?? '';
        $rescheduledDate = $data['rescheduledDate'] ?? null;
        $now = now();

        $order->status = 'Không thành công';
        $order->failed_delivery_reason = $reason;
        $order->failed_delivery_note = $notes;
        $order->failed_delivery_at = $now;
        $order->failed_delivery_by = auth()->user()?->name ?? ($order->assignedDriver?->name ?? 'Lái xe');
        $order->rescheduled_date = $rescheduledDate;

        $noteAppend = "[GIAO KHÔNG THÀNH CÔNG " . $now->format('H:i d/m/Y') . "]: Lý do: {$reason}. " .
            ($rescheduledDate ? "Hẹn lại: {$rescheduledDate}. " : "") .
            ($notes ? "Ghi chú: {$notes}" : "");
        $order->notes = ($order->notes ? $order->notes . "\n" : '') . $noteAppend;
        $order->save();

        try {
            \App\Models\AuditLog::create([
                'timestamp'            => $now->format('Y-m-d H:i:s'),
                'performer_name'       => auth()->user()?->name ?? ($order->assignedDriver?->name ?? 'Lái xe'),
                'user_id'              => auth()->id() ?? $order->assigned_driver_id,
                'action'               => 'Báo Giao Thất Bại',
                'module'               => 'Vận Tải & Giao Nhận',
                'target_document_code' => $order->order_code,
                'field_name'           => 'Trạng thái giao hàng',
                'old_value'            => 'Đang vận chuyển',
                'new_value'            => 'Không thành công',
                'notes'                => "Báo giao thất bại đơn {$order->order_code}. Lý do: {$reason}.",
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Đã ghi nhận báo giao hàng không thành công cho đơn {$order->order_code}.",
            'order'   => [
                'id'                   => (string) $order->id,
                'orderCode'            => $order->order_code,
                'status'               => $order->status,
                'failedDeliveryReason' => $order->failed_delivery_reason,
            ]
        ]);
    }
}



