<?php

namespace App\Http\Controllers;

use App\Models\FilterPart;
use App\Models\FilterPartTransaction;
use App\Models\FilterInstallation;
use App\Models\FilterInstallationItem;
use App\Models\FilterRepair;
use App\Models\FilterRepairItem;
use App\Models\FilterMachineModel;
use App\Models\FilterInstalledMachine;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WaterFilterController extends Controller
{
    /**
     * Danh sách trạng thái đơn lắp đặt chi tiết
     */
    public static function getInstallationStatuses(): array
    {
        return [
            'pending'     => ['label' => 'Mới tiếp nhận (Chờ xử lý)', 'badge' => 'bg-amber-100 text-amber-800 border-amber-200'],
            'confirmed'   => ['label' => 'Đã xác nhận & Hẹn lịch', 'badge' => 'bg-sky-100 text-sky-800 border-sky-200'],
            'assigned'    => ['label' => 'Đã giao việc cho KTV', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200'],
            'preparing'   => ['label' => 'Đang chuẩn bị máy & vật tư', 'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200'],
            'dispatching' => ['label' => 'KTV đang di chuyển', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200'],
            'in_progress' => ['label' => 'Đang lắp đặt tại nhà khách', 'badge' => 'bg-cyan-100 text-cyan-800 border-cyan-200'],
            'inspecting'  => ['label' => 'Nghiệm thu & Đo chỉ số TDS', 'badge' => 'bg-teal-100 text-teal-800 border-teal-200'],
            'completed'   => ['label' => 'Hoàn thành nghiệm thu', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
            'rescheduled' => ['label' => 'Khách hẹn lại lịch khác', 'badge' => 'bg-orange-100 text-orange-800 border-orange-200'],
            'cancelled'   => ['label' => 'Đã hủy đơn', 'badge' => 'bg-rose-100 text-rose-800 border-rose-200'],
        ];
    }

    /**
     * Màn hình chính phân hệ Lắp đặt & Sửa chữa Máy lọc nước
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'installations');

        // Thống kê tổng quan nhanh
        $totalParts = FilterPart::where('status', 'active')->count();
        $lowStockCount = FilterPart::where('status', 'active')->whereColumn('stock_qty', '<=', 'min_stock_alert')->count();
        $pendingInstallCount = FilterInstallation::whereIn('status', ['pending', 'confirmed', 'assigned', 'preparing', 'dispatching', 'in_progress', 'inspecting'])->count();
        $activeRepairCount = FilterRepair::whereIn('status', ['received', 'inspecting', 'waiting_parts', 'repairing'])->count();

        // Danh sách kỹ thuật viên (hoặc nhân viên)
        $technicians = User::where('status', '!=', 'inactive')
            ->select('id', 'name', 'phone', 'department')
            ->orderBy('name')
            ->get();

        // Danh mục phụ tùng phục vụ cho modal chọn linh kiện
        $parts = FilterPart::where('status', 'active')
            ->orderBy('name')
            ->get();

        // Danh sách model máy lọc nước
        $machineModels = FilterMachineModel::where('is_active', true)
            ->orderBy('brand')
            ->orderBy('name')
            ->get();

        // Thống kê máy đã lắp & mã QR
        $installedCount = FilterInstalledMachine::count();
        $unactivatedCount = FilterInstalledMachine::where('status', 'unactivated')->count();
        $pendingActivationCount = FilterInstalledMachine::where('status', 'pending_activation')->count();
        $activeWarrantyCount = FilterInstalledMachine::where('status', 'activated')
            ->where(function($q) {
                $q->whereNull('warranty_until')->orWhere('warranty_until', '>=', today());
            })->count();

        $statuses = self::getInstallationStatuses();

        return view('water_filter.index', compact(
            'tab',
            'totalParts',
            'lowStockCount',
            'pendingInstallCount',
            'activeRepairCount',
            'installedCount',
            'unactivatedCount',
            'pendingActivationCount',
            'activeWarrantyCount',
            'technicians',
            'parts',
            'machineModels',
            'statuses'
        ));
    }

    /* -------------------------------------------------------------
     * 1. KHO PHỤ TÙNG & PHỤ KIỆN
     * ------------------------------------------------------------- */

    public function getParts(Request $request)
    {
        $query = FilterPart::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_qty', '<=', 'min_stock_alert');
        }

        $parts = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'parts'   => $parts,
        ]);
    }

    public function savePart(Request $request)
    {
        $data = $request->validate([
            'id'              => 'nullable|integer',
            'code'            => 'required|string|max:50',
            'name'            => 'required|string|max:255',
            'category'        => 'required|string|max:100',
            'unit'            => 'required|string|max:30',
            'in_price'        => 'required|numeric|min:0',
            'out_price'       => 'required|numeric|min:0',
            'stock_qty'       => 'nullable|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'location'        => 'nullable|string|max:100',
            'description'     => 'nullable|string',
            'status'          => 'nullable|string|in:active,inactive',
        ]);

        $partId = $data['id'] ?? null;
        unset($data['id']);

        if ($partId) {
            $part = FilterPart::findOrFail($partId);
            $part->update($data);
            $message = 'Cập nhật phụ tùng thành công!';
        } else {
            // Kiểm tra trùng mã
            if (FilterPart::where('code', $data['code'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Mã phụ tùng đã tồn tại trên hệ thống!'], 422);
            }
            $part = FilterPart::create($data);

            if (($part->stock_qty ?? 0) > 0) {
                FilterPartTransaction::create([
                    'part_id'        => $part->id,
                    'type'           => 'import',
                    'quantity'       => $part->stock_qty,
                    'balance_before' => 0,
                    'balance_after'  => $part->stock_qty,
                    'unit_price'     => $part->in_price,
                    'total_price'    => $part->in_price * $part->stock_qty,
                    'reference_code' => 'NK-BAN-DAU',
                    'note'           => 'Tồn khởi tạo ban đầu',
                    'created_by'     => Auth::id(),
                    'created_by_name'=> Auth::user()?->name ?? 'Admin',
                ]);
            }
            $message = 'Thêm mới phụ tùng thành công!';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'part'    => $part,
        ]);
    }

    public function deletePart($id)
    {
        $part = FilterPart::findOrFail($id);
        $part->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phụ tùng khỏi kho!',
        ]);
    }

    /**
     * Nhập kho phụ tùng từ nhà cung cấp
     */
    public function importStock(Request $request)
    {
        $data = $request->validate([
            'part_id'    => 'required|exists:filter_parts,id',
            'quantity'   => 'required|integer|min:1',
            'in_price'   => 'required|numeric|min:0',
            'supplier'   => 'nullable|string',
            'note'       => 'nullable|string',
        ]);

        return DB::transaction(function () use ($data) {
            $part = FilterPart::lockForUpdate()->findOrFail($data['part_id']);
            $oldStock = $part->stock_qty;
            $newStock = $oldStock + $data['quantity'];

            $part->stock_qty = $newStock;
            $part->in_price = $data['in_price'];
            $part->save();

            $refCode = 'NK-' . date('Ymd') . '-' . Str::upper(Str::random(4));

            FilterPartTransaction::create([
                'part_id'        => $part->id,
                'type'           => 'import',
                'quantity'       => $data['quantity'],
                'balance_before' => $oldStock,
                'balance_after'  => $newStock,
                'unit_price'     => $data['in_price'],
                'total_price'    => $data['in_price'] * $data['quantity'],
                'reference_code' => $refCode,
                'note'           => ($data['supplier'] ? "[NCC: {$data['supplier']}] " : '') . ($data['note'] ?? 'Nhập kho bổ sung'),
                'created_by'     => Auth::id(),
                'created_by_name'=> Auth::user()?->name ?? 'Admin',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Nhập kho thành công {$data['quantity']} {$part->unit} {$part->name}!",
                'part'    => $part,
            ]);
        });
    }

    public function getTransactions(Request $request)
    {
        $query = FilterPartTransaction::with('part')->latest('id');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($partId = $request->input('part_id')) {
            $query->where('part_id', $partId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_code', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('part', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->paginate(25);

        return response()->json([
            'success'      => true,
            'transactions' => $transactions,
        ]);
    }

    /* -------------------------------------------------------------
     * 1.1 DANH MỤC MODEL MÁY LỌC NƯỚC
     * ------------------------------------------------------------- */

    public function getMachineModels(Request $request)
    {
        $query = FilterMachineModel::query();
        if ($brand = $request->input('brand')) {
            $query->where('brand', $brand);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }
        $models = $query->orderBy('brand')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'models'  => $models,
        ]);
    }

    public function saveMachineModel(Request $request)
    {
        $data = $request->validate([
            'id'             => 'nullable|integer',
            'code'           => 'required|string|max:50',
            'name'           => 'required|string|max:255',
            'brand'          => 'nullable|string|max:100',
            'filter_stages'  => 'nullable|integer|min:1|max:20',
            'type'           => 'nullable|string|max:100',
            'standard_price' => 'nullable|numeric|min:0',
            'description'    => 'nullable|string',
        ]);

        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            $model = FilterMachineModel::findOrFail($id);
            $model->update($data);
            $message = 'Cập nhật model máy lọc thành công!';
        } else {
            if (FilterMachineModel::where('code', $data['code'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Mã model máy này đã tồn tại!'], 422);
            }
            $model = FilterMachineModel::create($data);
            $message = 'Thêm mới model máy lọc nước thành công!';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'model'   => $model,
        ]);
    }

    public function deleteMachineModel($id)
    {
        $model = FilterMachineModel::findOrFail($id);
        $model->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa model máy lọc!',
        ]);
    }

    /* -------------------------------------------------------------
     * 2. LÊN ĐƠN ĐI LẮP ĐẶT MÁY LỌC (TRANG RIÊNG BIỆT - FULL SCREEN)
     * ------------------------------------------------------------- */

    /**
     * Mở trang tạo mới đơn lắp đặt (Không dùng modal)
     */
    public function createInstallation(Request $request)
    {
        $technicians = User::where('status', '!=', 'inactive')
            ->select('id', 'name', 'phone', 'department')
            ->orderBy('name')
            ->get();
        $machineModels = FilterMachineModel::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
        $parts = FilterPart::where('status', 'active')->orderBy('name')->get();
        $statuses = self::getInstallationStatuses();
        $installation = null;

        return view('water_filter.installations.form', compact(
            'technicians',
            'machineModels',
            'parts',
            'statuses',
            'installation'
        ));
    }

    /**
     * Mở trang chỉnh sửa đơn lắp đặt (Không dùng modal)
     */
    public function editInstallationView(Request $request, $id)
    {
        $installation = FilterInstallation::with(['items', 'technician', 'modelCatalog'])->findOrFail($id);
        $technicians = User::where('status', '!=', 'inactive')
            ->select('id', 'name', 'phone', 'department')
            ->orderBy('name')
            ->get();
        $machineModels = FilterMachineModel::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
        $parts = FilterPart::where('status', 'active')->orderBy('name')->get();
        $statuses = self::getInstallationStatuses();

        return view('water_filter.installations.form', compact(
            'technicians',
            'machineModels',
            'parts',
            'statuses',
            'installation'
        ));
    }

    public function getInstallations(Request $request)
    {
        $query = FilterInstallation::with(['items', 'technician'])->latest('id');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($techId = $request->input('technician_id')) {
            $query->where('technician_id', $techId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_address', 'like', "%{$search}%")
                  ->orWhere('machine_model', 'like', "%{$search}%");
            });
        }

        $installations = $query->paginate(20);

        return response()->json([
            'success'       => true,
            'installations' => $installations,
        ]);
    }

    public function saveInstallation(Request $request)
    {
        $statusKeys = implode(',', array_keys(self::getInstallationStatuses()));

        // Pre-sanitize empty strings to null for nullable fields
        $input = $request->all();
        $nullIfEmpty = [
            'id', 'machine_model_id', 'technician_id', 'appointment_date',
            'installed_at', 'warranty_until', 'warranty_card_code', 'warranty_notes',
            'next_maintenance_date', 'warranty_months', 'filter_replacement_months',
            'tds_in', 'tds_out', 'pressure', 'customer_notes',
            'completion_notes', 'machine_serial', 'water_source_type'
        ];
        foreach ($nullIfEmpty as $key) {
            if (isset($input[$key]) && trim((string)$input[$key]) === '') {
                $input[$key] = null;
            }
        }
        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as &$it) {
                if (isset($it['part_id']) && trim((string)$it['part_id']) === '') {
                    $it['part_id'] = null;
                }
                if (isset($it['unit_price']) && trim((string)$it['unit_price']) === '') {
                    $it['unit_price'] = 0;
                }
            }
            unset($it);
        }
        $request->merge($input);

        $data = $request->validate([
            'id'                        => 'nullable|integer',
            'customer_name'             => 'required|string|max:255',
            'customer_phone'            => 'required|string|max:30',
            'customer_address'          => 'required|string',
            'machine_model_id'          => 'nullable|integer',
            'machine_model'             => 'nullable|string|max:255',
            'machine_serial'            => 'nullable|string|max:100',
            'water_source_type'         => 'nullable|string|max:100',
            'technician_id'             => 'nullable|integer',
            'appointment_date'          => 'nullable|date',
            'installed_at'              => 'nullable|date',
            'warranty_months'           => 'nullable|integer|min:0',
            'warranty_until'            => 'nullable|date',
            'warranty_card_code'        => 'nullable|string|max:50',
            'warranty_notes'            => 'nullable|string',
            'filter_replacement_months' => 'nullable|integer|min:0',
            'next_maintenance_date'     => 'nullable|date',
            'status'                    => 'nullable|string|in:' . $statusKeys,
            'service_fee'               => 'nullable|numeric|min:0',
            'payment_status'            => 'nullable|string|in:unpaid,paid,free',
            'customer_notes'            => 'nullable|string',
            'tds_in'                    => 'nullable|integer',
            'tds_out'                   => 'nullable|integer',
            'pressure'                  => 'nullable|string',
            'completion_notes'          => 'nullable|string',
            'items'                     => 'nullable|array',
            'items.*.part_id'           => 'nullable|integer',
            'items.*.part_name'         => 'required|string',
            'items.*.unit'              => 'nullable|string',
            'items.*.quantity'          => 'required|integer|min:1',
            'items.*.unit_price'        => 'nullable|numeric|min:0',
            'items.*.note'              => 'nullable|string',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $id = $data['id'] ?? null;
            $itemsData = $data['items'] ?? [];
            unset($data['id'], $data['items']);

            // Đảm bảo các trường nullable đúng kiểu và không bị rỗng ''
            $data['machine_model_id'] = !empty($data['machine_model_id']) ? (int)$data['machine_model_id'] : null;
            $data['technician_id'] = !empty($data['technician_id']) ? (int)$data['technician_id'] : null;
            $data['appointment_date'] = !empty($data['appointment_date']) ? date('Y-m-d H:i:s', strtotime($data['appointment_date'])) : null;
            $data['service_fee'] = isset($data['service_fee']) ? (float)$data['service_fee'] : 0;
            $data['status'] = $data['status'] ?? 'pending';
            $data['payment_status'] = $data['payment_status'] ?? 'unpaid';
            $data['warranty_months'] = isset($data['warranty_months']) && is_numeric($data['warranty_months']) ? (int)$data['warranty_months'] : 24;
            $data['filter_replacement_months'] = isset($data['filter_replacement_months']) && is_numeric($data['filter_replacement_months']) ? (int)$data['filter_replacement_months'] : 6;

            // Xử lý Ngày Lắp Đặt & Hạn Bảo Hành
            if (($data['status'] === 'completed' || !empty($data['tds_out'])) && empty($data['installed_at'])) {
                $data['installed_at'] = now();
            }

            if (!empty($data['installed_at'])) {
                $installedTime = strtotime($data['installed_at']);
                $data['installed_at'] = date('Y-m-d H:i:s', $installedTime);

                if (empty($data['warranty_until'])) {
                    $data['warranty_until'] = date('Y-m-d', strtotime("+{$data['warranty_months']} months", $installedTime));
                }
                if (empty($data['next_maintenance_date'])) {
                    $data['next_maintenance_date'] = date('Y-m-d', strtotime("+{$data['filter_replacement_months']} months", $installedTime));
                }
            }

            // Nếu có machine_model_id mà machine_model chưa có thì lấy từ danh mục model
            if (!empty($data['machine_model_id'])) {
                $mModel = FilterMachineModel::find($data['machine_model_id']);
                if ($mModel && empty($data['machine_model'])) {
                    $data['machine_model'] = $mModel->name;
                }
            }
            if (empty($data['machine_model'])) {
                $data['machine_model'] = 'Máy lọc nước RO tiêu chuẩn';
            }

            // Tính tổng tiền phụ kiện
            $partsFee = 0;
            foreach ($itemsData as $it) {
                $partsFee += ((int)($it['quantity'] ?? 1) * (float)($it['unit_price'] ?? 0));
            }
            $data['parts_fee'] = $partsFee;
            $data['total_amount'] = ($data['service_fee'] ?? 0) + $partsFee;

            if ($data['technician_id']) {
                $tech = User::find($data['technician_id']);
                $data['technician_name'] = $tech?->name;
                if (!isset($data['status']) || $data['status'] === 'pending') {
                    $data['status'] = 'assigned';
                }
            } else {
                $data['technician_name'] = null;
            }

            $oldTechId = null;
            if ($id) {
                $installation = FilterInstallation::findOrFail($id);
                $oldTechId = $installation->technician_id;
                $installation->update($data);
                $message = 'Cập nhật đơn lắp đặt thành công!';
            } else {
                $data['code'] = 'LD-' . date('Ymd') . '-' . sprintf('%03d', FilterInstallation::whereDate('created_at', today())->count() + 1);
                $data['created_by'] = Auth::id();
                $installation = FilterInstallation::create($data);
                $message = 'Tạo đơn lắp đặt máy lọc nước thành công!';
            }

            // Đồng bộ items
            $installation->items()->delete();
            foreach ($itemsData as $it) {
                $partId = !empty($it['part_id']) ? (int)$it['part_id'] : null;
                $part = $partId ? FilterPart::find($partId) : null;
                $installation->items()->create([
                    'part_id'    => $part?->id,
                    'part_code'  => $part?->code,
                    'part_name'  => $it['part_name'],
                    'unit'       => $it['unit'] ?? ($part?->unit ?? 'Cái'),
                    'quantity'   => (int)($it['quantity'] ?? 1),
                    'unit_price' => (float)($it['unit_price'] ?? 0),
                    'total_price'=> (int)($it['quantity'] ?? 1) * (float)($it['unit_price'] ?? 0),
                    'note'       => $it['note'] ?? null,
                ]);
            }

            // Bắn thông báo nổi cho KTV được phân công
            if ($installation->technician_id && (!$id || $oldTechId != $installation->technician_id)) {
                $timeText = $installation->appointment_date ? date('H:i d/m/Y', strtotime($installation->appointment_date)) : 'Chưa hẹn giờ';
                \App\Models\SystemNotification::send(
                    $installation->technician_id,
                    '🔧 Phân công đơn lắp đặt: ' . $installation->code,
                    "Bạn vừa được phân công lắp đặt máy lọc [{$installation->machine_model}] cho KH {$installation->customer_name} ({$installation->customer_phone}) tại {$installation->customer_address}. Lịch hẹn: {$timeText}.",
                    url('/water-filter/installations/' . $installation->id . '/edit'),
                    'installation_assigned',
                    $installation,
                    [
                        'installation_id'  => $installation->id,
                        'code'             => $installation->code,
                        'customer_name'    => $installation->customer_name,
                        'customer_phone'   => $installation->customer_phone,
                        'customer_address' => $installation->customer_address,
                        'machine_model'    => $installation->machine_model,
                        'appointment_date' => $installation->appointment_date ? date('Y-m-d H:i:s', strtotime($installation->appointment_date)) : null,
                    ]
                );
            }

            // Đồng bộ sang bảng Giao việc & Phân công công việc (TaskAssignment)
            if ($installation->technician_id) {
                \App\Models\TaskAssignment::updateOrCreate(
                    [
                        'related_order_code' => $installation->code,
                    ],
                    [
                        'code'             => 'CV-' . $installation->code,
                        'title'            => 'Lắp đặt máy lọc: ' . ($installation->machine_model ?? 'RO') . ' - KH ' . $installation->customer_name,
                        'description'      => "Khách hàng: {$installation->customer_name}\nĐiện thoại: {$installation->customer_phone}\nĐịa chỉ: {$installation->customer_address}\nNguồn nước: {$installation->water_source_type}\nGhi chú: " . ($installation->customer_notes ?? 'Lắp đặt và kích hoạt tem QR điện tử.'),
                        'category'         => 'water_filter_installation',
                        'assigned_to_id'   => $installation->technician_id,
                        'created_by_id'    => Auth::id() ?? 1,
                        'deadline'         => $installation->appointment_date,
                        'priority'         => 'high',
                        'status'           => in_array($installation->status, ['completed', 'inspected']) ? 'Hoàn thành' : 'Đang thực hiện',
                        'progress_percent' => in_array($installation->status, ['completed', 'inspected']) ? 100 : 0,
                    ]
                );
            }

            if (!$request->expectsJson() && !$request->ajax()) {
                session()->flash('success', $message);
                return redirect('/water-filter?tab=installations');
            }

            return response()->json([
                'success'      => true,
                'message'      => $message,
                'installation' => $installation->load('items'),
            ]);
        });
    }

    public function updateInstallationStatus(Request $request, $id)
    {
        $statusKeys = implode(',', array_keys(self::getInstallationStatuses()));

        $input = $request->all();
        foreach (['tds_in', 'tds_out', 'pressure', 'completion_notes'] as $k) {
            if (isset($input[$k]) && trim((string)$input[$k]) === '') {
                $input[$k] = null;
            }
        }
        $request->merge($input);

        $data = $request->validate([
            'status'           => 'required|string|in:' . $statusKeys,
            'tds_in'           => 'nullable|integer',
            'tds_out'          => 'nullable|integer',
            'pressure'         => 'nullable|string',
            'completion_notes' => 'nullable|string',
            'payment_status'   => 'nullable|string|in:unpaid,paid,free',
        ]);

        return DB::transaction(function () use ($id, $data) {
            $installation = FilterInstallation::with('items.part')->lockForUpdate()->findOrFail($id);
            $oldStatus = $installation->status;
            $newStatus = $data['status'];

            $installation->status = $newStatus;
            if (isset($data['tds_in'])) $installation->tds_in = $data['tds_in'];
            if (isset($data['tds_out'])) $installation->tds_out = $data['tds_out'];
            if (isset($data['pressure'])) $installation->pressure = $data['pressure'];
            if (isset($data['completion_notes'])) $installation->completion_notes = $data['completion_notes'];
            if (isset($data['payment_status'])) $installation->payment_status = $data['payment_status'];

            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $installation->completed_date = now();
                if (!$installation->installed_at) {
                    $installation->installed_at = now();
                }
                if (!$installation->warranty_until && $installation->installed_at) {
                    $wMonths = $installation->warranty_months ?: 24;
                    $installation->warranty_until = date('Y-m-d', strtotime("+{$wMonths} months", strtotime($installation->installed_at)));
                }
                if (!$installation->next_maintenance_date && $installation->installed_at) {
                    $rMonths = $installation->filter_replacement_months ?: 6;
                    $installation->next_maintenance_date = date('Y-m-d', strtotime("+{$rMonths} months", strtotime($installation->installed_at)));
                }

                // Tự động trừ kho phụ tùng xuất theo đơn lắp
                foreach ($installation->items as $item) {
                    if ($item->part_id) {
                        $part = FilterPart::lockForUpdate()->find($item->part_id);
                        if ($part) {
                            $oldStock = $part->stock_qty;
                            $newStock = max(0, $oldStock - $item->quantity);
                            $part->stock_qty = $newStock;
                            $part->save();

                            FilterPartTransaction::create([
                                'part_id'        => $part->id,
                                'type'           => 'export_install',
                                'quantity'       => -$item->quantity,
                                'balance_before' => $oldStock,
                                'balance_after'  => $newStock,
                                'unit_price'     => $item->unit_price,
                                'total_price'    => $item->total_price,
                                'reference_code' => $installation->code,
                                'note'           => "Xuất vật tư lắp đặt cho khách {$installation->customer_name}",
                                'created_by'     => Auth::id(),
                                'created_by_name'=> Auth::user()?->name ?? 'Admin',
                            ]);
                        }
                    }
                }

                // Tự động đồng bộ tạo / kích hoạt máy lọc nước trong filter_installed_machines
                $existingMachine = FilterInstalledMachine::where('installation_id', $installation->id)->first();
                if (!$existingMachine) {
                    $nextCodes = FilterInstalledMachine::generateNextCodes();
                    FilterInstalledMachine::create([
                        'machine_code'              => $nextCodes['machine_code'],
                        'qr_code'                   => $nextCodes['qr_code'],
                        'qr_token'                  => $nextCodes['qr_token'],
                        'installation_id'           => $installation->id,
                        'status'                    => 'activated',
                        'customer_name'             => $installation->customer_name,
                        'customer_phone'            => $installation->customer_phone,
                        'customer_address'          => $installation->customer_address,
                        'water_source_type'         => $installation->water_source_type ?? 'Nước máy thành phố',
                        'machine_model_id'          => $installation->machine_model_id,
                        'machine_model'             => $installation->machine_model,
                        'machine_serial'            => $installation->machine_serial,
                        'technician_id'             => $installation->technician_id,
                        'technician_name'           => $installation->technician_name,
                        'installed_at'              => $installation->installed_at,
                        'activated_at'              => now(),
                        'activated_by'              => Auth::id(),
                        'activated_by_name'         => Auth::user()?->name ?? 'Admin',
                        'warranty_months'           => $installation->warranty_months ?: 24,
                        'warranty_until'            => $installation->warranty_until,
                        'warranty_card_code'        => $installation->warranty_card_code ?: $installation->code,
                        'filter_replacement_months' => $installation->filter_replacement_months ?: 6,
                        'next_maintenance_date'     => $installation->next_maintenance_date,
                        'tds_in'                    => $installation->tds_in,
                        'tds_out'                   => $installation->tds_out,
                        'pressure'                  => $installation->pressure,
                        'created_by'                => Auth::id(),
                    ]);
                } else {
                    $existingMachine->update([
                        'status'                    => 'activated',
                        'customer_name'             => $installation->customer_name,
                        'customer_phone'            => $installation->customer_phone,
                        'customer_address'          => $installation->customer_address,
                        'machine_model'             => $installation->machine_model,
                        'machine_serial'            => $installation->machine_serial,
                        'technician_id'             => $installation->technician_id,
                        'technician_name'           => $installation->technician_name,
                        'installed_at'              => $installation->installed_at,
                        'activated_at'              => now(),
                        'warranty_months'           => $installation->warranty_months ?: 24,
                        'warranty_until'            => $installation->warranty_until,
                        'tds_in'                    => $installation->tds_in,
                        'tds_out'                   => $installation->tds_out,
                        'pressure'                  => $installation->pressure,
                    ]);
                }
            }

            $installation->save();

            return response()->json([
                'success'      => true,
                'message'      => "Đã cập nhật trạng thái đơn lắp đặt: {$newStatus}!",
                'installation' => $installation,
            ]);
        });
    }

    public function deleteInstallation($id)
    {
        $installation = FilterInstallation::findOrFail($id);
        $installation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa đơn lắp đặt máy lọc nước!',
        ]);
    }

    /* -------------------------------------------------------------
     * 3. TIẾP NHẬN SỬA CHỮA & BẢO HÀNH
     * ------------------------------------------------------------- */

    public function getRepairs(Request $request)
    {
        $query = FilterRepair::with(['items', 'technician'])->latest('id');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($warrantyType = $request->input('warranty_type')) {
            $query->where('warranty_type', $warrantyType);
        }

        if ($techId = $request->input('technician_id')) {
            $query->where('technician_id', $techId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('machine_model', 'like', "%{$search}%")
                  ->orWhere('symptom', 'like', "%{$search}%");
            });
        }

        $repairs = $query->paginate(20);

        return response()->json([
            'success' => true,
            'repairs' => $repairs,
        ]);
    }

    public function saveRepair(Request $request)
    {
        // Pre-sanitize empty strings to null for nullable fields
        $input = $request->all();
        $nullIfEmpty = [
            'id', 'customer_address', 'machine_serial', 'diagnosis', 'solution',
            'technician_id', 'labor_fee', 'discount', 'warranty_until', 'notes'
        ];
        foreach ($nullIfEmpty as $key) {
            if (isset($input[$key]) && trim((string)$input[$key]) === '') {
                $input[$key] = null;
            }
        }
        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as &$it) {
                if (isset($it['part_id']) && trim((string)$it['part_id']) === '') {
                    $it['part_id'] = null;
                }
                if (isset($it['unit_price']) && trim((string)$it['unit_price']) === '') {
                    $it['unit_price'] = 0;
                }
            }
            unset($it);
        }
        $request->merge($input);

        $data = $request->validate([
            'id'                => 'nullable|integer',
            'customer_name'     => 'required|string|max:255',
            'customer_phone'    => 'required|string|max:30',
            'customer_address'  => 'nullable|string',
            'repair_type'       => 'required|string|in:at_shop,at_home',
            'machine_model'     => 'required|string|max:255',
            'machine_serial'    => 'nullable|string|max:100',
            'warranty_type'     => 'required|string|in:in_warranty,out_warranty',
            'symptom'           => 'required|string',
            'diagnosis'         => 'nullable|string',
            'solution'          => 'nullable|string',
            'technician_id'     => 'nullable|integer',
            'status'            => 'nullable|string|in:received,inspecting,waiting_parts,repairing,completed,delivered,cancelled',
            'labor_fee'         => 'nullable|numeric|min:0',
            'discount'          => 'nullable|numeric|min:0',
            'payment_status'    => 'nullable|string|in:unpaid,paid,warranty_free',
            'warranty_until'    => 'nullable|date',
            'notes'             => 'nullable|string',
            'items'             => 'nullable|array',
            'items.*.part_id'   => 'nullable|integer',
            'items.*.part_name' => 'required|string',
            'items.*.unit'      => 'nullable|string',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.unit_price'=> 'nullable|numeric|min:0',
            'items.*.is_warranty'=> 'nullable|boolean',
            'items.*.note'      => 'nullable|string',
        ]);

        return DB::transaction(function () use ($data) {
            $id = $data['id'] ?? null;
            $itemsData = $data['items'] ?? [];
            unset($data['id'], $data['items']);

            // Đảm bảo kiểu dữ liệu an toàn
            $data['technician_id'] = !empty($data['technician_id']) ? (int)$data['technician_id'] : null;
            $data['warranty_until'] = !empty($data['warranty_until']) ? date('Y-m-d', strtotime($data['warranty_until'])) : null;
            $data['labor_fee'] = isset($data['labor_fee']) ? (float)$data['labor_fee'] : 0;
            $data['discount'] = isset($data['discount']) ? (float)$data['discount'] : 0;

            // Tính tiền phụ tùng (những mục không miễn phí bảo hành)
            $partsFee = 0;
            foreach ($itemsData as $it) {
                if (empty($it['is_warranty'])) {
                    $partsFee += ((int)($it['quantity'] ?? 1) * (float)($it['unit_price'] ?? 0));
                }
            }
            $data['parts_fee'] = $partsFee;

            $laborFee = $data['warranty_type'] === 'in_warranty' ? 0 : ($data['labor_fee'] ?? 0);
            $discount = $data['discount'] ?? 0;
            $data['total_amount'] = max(0, $laborFee + $partsFee - $discount);

            if ($data['warranty_type'] === 'in_warranty' && $partsFee == 0) {
                $data['payment_status'] = 'warranty_free';
            }

            if ($data['technician_id']) {
                $tech = User::find($data['technician_id']);
                $data['technician_name'] = $tech?->name;
            } else {
                $data['technician_name'] = null;
            }

            $oldTechId = null;
            if ($id) {
                $repair = FilterRepair::findOrFail($id);
                $oldTechId = $repair->technician_id;
                $repair->update($data);
                $message = 'Cập nhật phiếu sửa chữa thành công!';
            } else {
                $data['code'] = 'SC-' . date('Ymd') . '-' . sprintf('%03d', FilterRepair::whereDate('created_at', today())->count() + 1);
                $data['created_by'] = Auth::id();
                $data['received_at'] = now();
                $repair = FilterRepair::create($data);
                $message = 'Tiếp nhận sửa chữa máy lọc nước thành công!';
            }

            // Cập nhật items
            $repair->items()->delete();
            foreach ($itemsData as $it) {
                $partId = !empty($it['part_id']) ? (int)$it['part_id'] : null;
                $part = $partId ? FilterPart::find($partId) : null;
                $isWar = !empty($it['is_warranty']) || ($data['warranty_type'] === 'in_warranty');
                $repair->items()->create([
                    'part_id'    => $part?->id,
                    'part_code'  => $part?->code,
                    'part_name'  => $it['part_name'],
                    'unit'       => $it['unit'] ?? ($part?->unit ?? 'Cái'),
                    'quantity'   => (int)($it['quantity'] ?? 1),
                    'unit_price' => $isWar ? 0 : (float)($it['unit_price'] ?? 0),
                    'total_price'=> $isWar ? 0 : ((int)($it['quantity'] ?? 1) * (float)($it['unit_price'] ?? 0)),
                    'is_warranty'=> $isWar,
                    'note'       => $it['note'] ?? null,
                ]);
            }

            // Bắn thông báo nổi cho KTV được phân công sửa chữa
            if ($repair->technician_id && (!$id || $oldTechId != $repair->technician_id)) {
                \App\Models\SystemNotification::send(
                    $repair->technician_id,
                    '🛠️ Phân công sửa chữa máy lọc: ' . $repair->code,
                    "Bạn vừa được phân công tiếp nhận sửa máy lọc [{$repair->machine_model}] cho KH {$repair->customer_name} ({$repair->customer_phone}) tại {$repair->customer_address}. Bệnh máy: " . ($repair->symptom ?: 'Cần kiểm tra kỹ thuật'),
                    url('/water-filter?tab=repairs&search=' . $repair->code),
                    'repair_assigned',
                    $repair,
                    [
                        'repair_id'        => $repair->id,
                        'code'             => $repair->code,
                        'customer_name'    => $repair->customer_name,
                        'customer_phone'   => $repair->customer_phone,
                        'customer_address' => $repair->customer_address,
                        'machine_model'    => $repair->machine_model,
                        'symptom'          => $repair->symptom,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'repair'  => $repair->load('items'),
            ]);
        });
    }

    public function updateRepairStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status'         => 'required|string|in:received,inspecting,waiting_parts,repairing,completed,delivered,cancelled',
            'diagnosis'      => 'nullable|string',
            'solution'       => 'nullable|string',
            'payment_status' => 'nullable|string|in:unpaid,paid,warranty_free',
            'warranty_until' => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        return DB::transaction(function () use ($id, $data) {
            $repair = FilterRepair::with('items.part')->lockForUpdate()->findOrFail($id);
            $oldStatus = $repair->status;
            $newStatus = $data['status'];

            $repair->status = $newStatus;
            if (isset($data['diagnosis'])) $repair->diagnosis = $data['diagnosis'];
            if (isset($data['solution'])) $repair->solution = $data['solution'];
            if (isset($data['payment_status'])) $repair->payment_status = $data['payment_status'];
            if (isset($data['warranty_until'])) $repair->warranty_until = $data['warranty_until'];
            if (isset($data['notes'])) $repair->notes = $data['notes'];

            if ($newStatus === 'completed' && $oldStatus !== 'completed' && $oldStatus !== 'delivered') {
                $repair->completed_at = now();

                // Trừ tồn kho các phụ tùng thay thế
                foreach ($repair->items as $item) {
                    if ($item->part_id) {
                        $part = FilterPart::lockForUpdate()->find($item->part_id);
                        if ($part) {
                            $oldStock = $part->stock_qty;
                            $newStock = max(0, $oldStock - $item->quantity);
                            $part->stock_qty = $newStock;
                            $part->save();

                            FilterPartTransaction::create([
                                'part_id'        => $part->id,
                                'type'           => 'export_repair',
                                'quantity'       => -$item->quantity,
                                'balance_before' => $oldStock,
                                'balance_after'  => $newStock,
                                'unit_price'     => $item->unit_price,
                                'total_price'    => $item->total_price,
                                'reference_code' => $repair->code,
                                'note'           => "Xuất phụ tùng sửa chữa máy lọc cho khách {$repair->customer_name}" . ($item->is_warranty ? ' (Bảo hành miễn phí)' : ''),
                                'created_by'     => Auth::id(),
                                'created_by_name'=> Auth::user()?->name ?? 'Admin',
                            ]);
                        }
                    }
                }
            }

            if ($newStatus === 'delivered' && !$repair->delivered_at) {
                $repair->delivered_at = now();
            }

            $repair->save();

            return response()->json([
                'success' => true,
                'message' => "Đã cập nhật trạng thái phiếu sửa chữa: {$newStatus}!",
                'repair'  => $repair,
            ]);
        });
    }

    public function deleteRepair($id)
    {
        $repair = FilterRepair::findOrFail($id);
        $repair->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phiếu sửa chữa máy lọc nước!',
        ]);
    }

    /* -------------------------------------------------------------
     * 4. QUẢN LÝ MÁY ĐÃ LẮP & MÃ QR BẢO HÀNH ĐIỆN TỬ
     * ------------------------------------------------------------- */

    public function getInstalledMachines(Request $request)
    {
        $query = FilterInstalledMachine::with(['modelCatalog', 'technician'])->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('machine_code', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_address', 'like', "%{$search}%")
                  ->orWhere('machine_model', 'like', "%{$search}%")
                  ->orWhere('machine_serial', 'like', "%{$search}%")
                  ->orWhere('warranty_card_code', 'like', "%{$search}%");
            });
        }

        if ($techId = $request->input('technician_id')) {
            $query->where('technician_id', $techId);
        }

        $now = now()->startOfDay();
        $soon = now()->addDays(30)->endOfDay();

        if ($status = $request->input('status') ?: $request->input('warranty_status')) {
            if ($status === 'unactivated') {
                $query->where('status', 'unactivated');
            } elseif ($status === 'pending_activation') {
                $query->where('status', 'pending_activation');
            } elseif ($status === 'activated') {
                $query->where('status', 'activated');
            } elseif ($status === 'expired') {
                $query->where(function($q) use ($now) {
                    $q->where('status', 'expired')
                      ->orWhere(function($sub) use ($now) {
                          $sub->where('status', 'activated')
                              ->whereNotNull('warranty_until')
                              ->where('warranty_until', '<', $now);
                      });
                });
            } elseif ($status === 'expiring_soon') {
                $query->where('status', 'activated')
                      ->whereNotNull('warranty_until')
                      ->where('warranty_until', '>=', $now)
                      ->where('warranty_until', '<=', $soon);
            } elseif ($status === 'active') {
                $query->where('status', 'activated')
                      ->where(function($q) use ($soon) {
                          $q->whereNull('warranty_until')
                            ->orWhere('warranty_until', '>', $soon);
                      });
            }
        }

        $machines = $query->paginate(30);

        // Đính kèm số lần bảo hành / sửa chữa liên quan (nếu có)
        $phones = $machines->pluck('customer_phone')->filter()->unique()->all();
        $serials = $machines->pluck('machine_serial')->filter()->unique()->all();

        $repairsGrouped = FilterRepair::where(function($q) use ($phones, $serials) {
            if (!empty($phones)) $q->whereIn('customer_phone', $phones);
            if (!empty($serials)) $q->orWhereIn('machine_serial', $serials);
        })->get()->groupBy('customer_phone');

        $transformed = $machines->getCollection()->map(function ($m) use ($repairsGrouped, $now, $soon) {
            $wUntil = $m->warranty_until ? Carbon::parse($m->warranty_until)->startOfDay() : null;
            $wStatus = $m->status;
            $daysLeft = 0;

            if ($m->status === 'activated' && $wUntil) {
                if ($wUntil < $now) {
                    $wStatus = 'expired';
                    $daysLeft = (int) $now->diffInDays($wUntil, false);
                } elseif ($wUntil <= $soon) {
                    $wStatus = 'expiring_soon';
                    $daysLeft = (int) $now->diffInDays($wUntil);
                } else {
                    $wStatus = 'active';
                    $daysLeft = (int) $now->diffInDays($wUntil);
                }
            }

            $repairsCount = 0;
            if ($m->customer_phone && isset($repairsGrouped[$m->customer_phone])) {
                $repairsCount = $repairsGrouped[$m->customer_phone]->count();
            }

            return [
                'id'                       => $m->id,
                'machine_code'             => $m->machine_code,
                'qr_code'                  => $m->qr_code,
                'qr_token'                 => $m->qr_token,
                'qr_url'                   => $m->qr_url,
                'status'                   => $m->status,
                'installation_id'          => $m->installation_id,
                'customer_name'            => $m->customer_name ?: '(Chưa có thông tin)',
                'customer_phone'           => $m->customer_phone ?: '',
                'customer_address'         => $m->customer_address ?: '',
                'water_source_type'        => $m->water_source_type,
                'machine_model_id'         => $m->machine_model_id,
                'machine_model'            => $m->machine_model ?: '(Chưa gán model)',
                'machine_serial'           => $m->machine_serial ?: '',
                'technician_id'            => $m->technician_id,
                'technician_name'          => $m->technician_name ?: '',
                'installed_at'             => $m->installed_at ? $m->installed_at->format('d/m/Y H:i') : '-',
                'installed_at_raw'         => $m->installed_at ? $m->installed_at->format('Y-m-d') : '',
                'activated_at'             => $m->activated_at ? $m->activated_at->format('d/m/Y H:i') : '-',
                'activated_by_name'        => $m->activated_by_name ?: '',
                'warranty_months'          => (int) ($m->warranty_months ?: 24),
                'warranty_until'           => $m->warranty_until ? $m->warranty_until->format('d/m/Y') : '-',
                'warranty_until_raw'       => $m->warranty_until ? $m->warranty_until->format('Y-m-d') : '',
                'warranty_status'          => $wStatus,
                'warranty_days_remaining'  => $daysLeft,
                'warranty_card_code'       => $m->warranty_card_code ?: '',
                'filter_replacement_months'=> (int) ($m->filter_replacement_months ?: 6),
                'next_maintenance_date'    => $m->next_maintenance_date ? $m->next_maintenance_date->format('d/m/Y') : '-',
                'next_maintenance_raw'     => $m->next_maintenance_date ? $m->next_maintenance_date->format('Y-m-d') : '',
                'warranty_notes'           => $m->warranty_notes ?: '',
                'activation_notes'         => $m->activation_notes ?: '',
                'tds_in'                   => $m->tds_in,
                'tds_out'                  => $m->tds_out,
                'pressure'                 => $m->pressure,
                'accessories'              => $m->accessories ?: '',
                'view_count'               => $m->view_count,
                'last_scanned_at'          => $m->last_scanned_at ? $m->last_scanned_at->format('d/m/Y H:i') : null,
                'repairs_count'            => $repairsCount,
            ];
        });

        $machines->setCollection($transformed);

        // Stats tổng quan
        $totalCount = FilterInstalledMachine::count();
        $unactivatedCount = FilterInstalledMachine::where('status', 'unactivated')->count();
        $pendingActivationCount = FilterInstalledMachine::where('status', 'pending_activation')->count();
        $activatedBase = FilterInstalledMachine::where('status', 'activated');
        $expiredCount = (clone $activatedBase)->whereNotNull('warranty_until')->where('warranty_until', '<', $now)->count() + FilterInstalledMachine::where('status', 'expired')->count();
        $expiringSoonCount = (clone $activatedBase)->whereNotNull('warranty_until')->where('warranty_until', '>=', $now)->where('warranty_until', '<=', $soon)->count();
        $activeCount = (clone $activatedBase)->where(function($q) use ($soon) {
            $q->whereNull('warranty_until')->orWhere('warranty_until', '>', $soon);
        })->count();

        return response()->json([
            'success'  => true,
            'machines' => $machines,
            'stats'    => [
                'total'              => $totalCount,
                'unactivated'        => $unactivatedCount,
                'pending_activation' => $pendingActivationCount,
                'active'             => $activeCount,
                'expiring_soon'      => $expiringSoonCount,
                'expired'            => $expiredCount,
            ]
        ]);
    }

    /**
     * Tạo mã QR máy lọc nước thông tin trống (hàng loạt)
     */
    public function generateBlankQrCodes(Request $request)
    {
        $data = $request->validate([
            'quantity'         => 'required|integer|min:1|max:100',
            'machine_model_id' => 'nullable|integer',
            'warranty_months'  => 'nullable|integer|min:1',
            'admin_notes'      => 'nullable|string',
        ]);

        $qty = $data['quantity'];
        $model = !empty($data['machine_model_id']) ? FilterMachineModel::find($data['machine_model_id']) : null;
        $wMonths = !empty($data['warranty_months']) ? (int)$data['warranty_months'] : 24;
        $createdList = [];

        DB::transaction(function () use ($qty, $model, $wMonths, $data, &$createdList) {
            for ($i = 0; $i < $qty; $i++) {
                $codes = FilterInstalledMachine::generateNextCodes();
                $machine = FilterInstalledMachine::create([
                    'machine_code'              => $codes['machine_code'],
                    'qr_code'                   => $codes['qr_code'],
                    'qr_token'                  => $codes['qr_token'],
                    'status'                    => 'unactivated',
                    'machine_model_id'          => $model?->id,
                    'machine_model'             => $model?->name,
                    'warranty_months'           => $wMonths,
                    'filter_replacement_months' => 6,
                    'admin_notes'               => $data['admin_notes'] ?? 'Tạo tem QR trống dán máy',
                    'created_by'                => Auth::id(),
                ]);
                $createdList[] = $machine;
            }
        });

        return response()->json([
            'success'  => true,
            'message'  => "Đã tạo thành công {$qty} mã QR trống cho máy lọc nước!",
            'machines' => $createdList,
        ]);
    }

    /**
     * Quản trị viên duyệt / kích hoạt bảo hành cho máy
     */
    public function activateMachine(Request $request, $id)
    {
        $machine = FilterInstalledMachine::findOrFail($id);

        $data = $request->validate([
            'installed_at'              => 'nullable|date',
            'warranty_months'           => 'nullable|integer|min:1',
            'warranty_until'            => 'nullable|date',
            'warranty_card_code'        => 'nullable|string|max:50',
            'machine_serial'            => 'nullable|string|max:100',
            'filter_replacement_months' => 'nullable|integer|min:1',
            'next_maintenance_date'     => 'nullable|date',
            'admin_notes'               => 'nullable|string',
        ]);

        $installedAt = !empty($data['installed_at']) ? Carbon::parse($data['installed_at']) : ($machine->installed_at ?: now());
        $wMonths = !empty($data['warranty_months']) ? (int)$data['warranty_months'] : ($machine->warranty_months ?: 24);
        $wUntil = !empty($data['warranty_until']) ? Carbon::parse($data['warranty_until']) : (clone $installedAt)->addMonths($wMonths);
        $rMonths = !empty($data['filter_replacement_months']) ? (int)$data['filter_replacement_months'] : ($machine->filter_replacement_months ?: 6);
        $nextMaint = !empty($data['next_maintenance_date']) ? Carbon::parse($data['next_maintenance_date']) : (clone $installedAt)->addMonths($rMonths);

        $machine->update([
            'status'                    => 'activated',
            'installed_at'              => $installedAt,
            'activated_at'              => now(),
            'activated_by'              => Auth::id(),
            'activated_by_name'         => Auth::user()?->name ?? 'Admin',
            'warranty_months'           => $wMonths,
            'warranty_until'            => $wUntil,
            'warranty_card_code'        => $data['warranty_card_code'] ?? ($machine->warranty_card_code ?: $machine->qr_code),
            'machine_serial'            => $data['machine_serial'] ?? $machine->machine_serial,
            'filter_replacement_months' => $rMonths,
            'next_maintenance_date'     => $nextMaint,
            'admin_notes'               => $data['admin_notes'] ?? $machine->admin_notes,
        ]);

        if ($machine->technician_id) {
            SystemNotification::send(
                $machine->technician_id,
                '✅ Máy lọc đã kích hoạt: ' . $machine->machine_code,
                "Máy lọc [{$machine->machine_model}] của khách {$machine->customer_name} ({$machine->customer_phone}) đã được duyệt kích hoạt bảo hành chính hãng thành công.",
                url('/m/' . $machine->qr_token),
                'machine_activated',
                $machine
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã duyệt kích hoạt bảo hành máy lọc nước thành công!',
            'machine' => $machine,
        ]);
    }

    /**
     * Cập nhật thông tin chi tiết máy lọc nước
     */
    public function updateInstalledMachine(Request $request, $id)
    {
        $machine = FilterInstalledMachine::findOrFail($id);

        $data = $request->validate([
            'customer_name'             => 'nullable|string|max:255',
            'customer_phone'            => 'nullable|string|max:30',
            'customer_address'          => 'nullable|string',
            'water_source_type'         => 'nullable|string|max:100',
            'machine_model_id'          => 'nullable|integer',
            'machine_model'             => 'nullable|string|max:255',
            'machine_serial'            => 'nullable|string|max:100',
            'technician_id'             => 'nullable|integer',
            'installed_at'              => 'nullable|date',
            'warranty_months'           => 'nullable|integer|min:0',
            'warranty_until'            => 'nullable|date',
            'warranty_card_code'        => 'nullable|string|max:50',
            'filter_replacement_months' => 'nullable|integer|min:0',
            'next_maintenance_date'     => 'nullable|date',
            'warranty_notes'            => 'nullable|string',
            'tds_in'                    => 'nullable|integer',
            'tds_out'                   => 'nullable|integer',
            'pressure'                  => 'nullable|string|max:100',
            'accessories'               => 'nullable|string',
            'status'                    => 'nullable|string|in:unactivated,pending_activation,activated,expired',
            'admin_notes'               => 'nullable|string',
        ]);

        if (!empty($data['technician_id'])) {
            $tech = User::find($data['technician_id']);
            $data['technician_name'] = $tech?->name;
        }

        if (!empty($data['machine_model_id']) && empty($data['machine_model'])) {
            $m = FilterMachineModel::find($data['machine_model_id']);
            $data['machine_model'] = $m?->name;
        }

        if (!empty($data['installed_at'])) {
            $installedTime = strtotime($data['installed_at']);
            $data['installed_at'] = date('Y-m-d H:i:s', $installedTime);
            if (empty($data['warranty_until']) && !empty($data['warranty_months'])) {
                $data['warranty_until'] = date('Y-m-d', strtotime("+{$data['warranty_months']} months", $installedTime));
            }
        }

        $machine->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin máy lọc nước thành công!',
            'machine' => $machine,
        ]);
    }

    /**
     * Xóa bản ghi máy lọc nước
     */
    public function deleteInstalledMachine($id)
    {
        $machine = FilterInstalledMachine::findOrFail($id);
        $machine->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa máy lọc nước khỏi danh sách!',
        ]);
    }

    /**
     * Quét mã QR máy lọc nước (Public Route /m/{token})
     * - Nếu máy trống/chờ duyệt:
     *   + Nếu người quét KHÔNG PHẢI TỪ WEB MÌNH (chưa đăng nhập): thông báo sản phẩm chưa được kích hoạt.
     *   + Nếu là KTV/nhân viên nội bộ đã đăng nhập: mở trang kích hoạt.
     * - Nếu máy đã kích hoạt: mở trang Tra cứu bảo hành điện tử chính hãng cho Khách hàng.
     */
    public function scanQrMachine(Request $request, $token)
    {
        $machine = FilterInstalledMachine::with(['modelCatalog', 'technician'])
            ->where('qr_token', $token)
            ->firstOrFail();

        // Cập nhật thống kê quét mã
        $machine->increment('view_count');
        $machine->update(['last_scanned_at' => now()]);

        if (in_array($machine->status, ['unactivated', 'pending_activation'])) {
            // Nếu người quét không phải từ web mình (chưa đăng nhập tài khoản KTV / Nhân viên)
            if (!Auth::check()) {
                return view('water_filter.qr_not_activated', compact('machine'));
            }

            // KTV / Nhân viên từ web mình đã đăng nhập: Mở trang kích hoạt
            $machineModels = FilterMachineModel::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
            $technicians = User::where('status', '!=', 'inactive')->select('id', 'name', 'phone', 'department')->orderBy('name')->get();
            return view('water_filter.qr_activation', compact('machine', 'machineModels', 'technicians'));
        }

        // Đã kích hoạt: Trang Passport Bảo Hành cho Khách Hàng
        $repairs = FilterRepair::where(function($q) use ($machine) {
            if ($machine->customer_phone) $q->where('customer_phone', $machine->customer_phone);
            if ($machine->machine_serial) $q->orWhere('machine_serial', $machine->machine_serial);
        })->latest('id')->take(10)->get();

        return view('water_filter.qr_portal', compact('machine', 'repairs'));
    }

    /**
     * KTV gửi thông tin kích hoạt máy khi quét QR
     */
    public function submitActivationRequest(Request $request, $token)
    {
        $machine = FilterInstalledMachine::where('qr_token', $token)->firstOrFail();

        $data = $request->validate([
            'customer_name'             => 'required|string|max:255',
            'customer_phone'            => 'required|string|max:30',
            'customer_address'          => 'required|string',
            'water_source_type'         => 'nullable|string|max:100',
            'machine_model_id'          => 'nullable|integer',
            'machine_model'             => 'required|string|max:255',
            'machine_serial'            => 'nullable|string|max:100',
            'technician_id'             => 'nullable|integer',
            'technician_name'           => 'nullable|string|max:255',
            'installed_at'              => 'nullable|date',
            'warranty_months'           => 'required|integer|min:1',
            'tds_in'                    => 'nullable|integer',
            'tds_out'                   => 'nullable|integer',
            'pressure'                  => 'nullable|string|max:100',
            'accessories'               => 'nullable|string',
            'activation_notes'          => 'nullable|string',
            'filter_replacement_months' => 'nullable|integer|min:1',
        ]);

        $installedAt = !empty($data['installed_at']) ? Carbon::parse($data['installed_at']) : now();
        $wMonths = (int) $data['warranty_months'];
        $wUntil = (clone $installedAt)->addMonths($wMonths)->format('Y-m-d');
        $rMonths = !empty($data['filter_replacement_months']) ? (int)$data['filter_replacement_months'] : 6;
        $nextMaint = (clone $installedAt)->addMonths($rMonths)->format('Y-m-d');

        $techName = $data['technician_name'] ?? null;
        if (!empty($data['technician_id'])) {
            $tech = User::find($data['technician_id']);
            $techName = $tech?->name ?? $techName;
        }

        // Tự động kích hoạt ngay nếu người gửi đã đăng nhập quyền quản trị
        $autoActivate = Auth::check() && (Auth::user()->hasPermission('water_filter.view') || Auth::user()->hasRole('admin'));

        $machine->update([
            'customer_name'             => $data['customer_name'],
            'customer_phone'            => $data['customer_phone'],
            'customer_address'          => $data['customer_address'],
            'water_source_type'         => $data['water_source_type'] ?? 'Nước máy thành phố',
            'machine_model_id'          => $data['machine_model_id'] ?? null,
            'machine_model'             => $data['machine_model'],
            'machine_serial'            => $data['machine_serial'] ?? null,
            'technician_id'             => $data['technician_id'] ?? Auth::id(),
            'technician_name'           => $techName ?: (Auth::user()?->name ?? 'Kỹ thuật viên'),
            'installed_at'              => $installedAt,
            'warranty_months'           => $wMonths,
            'warranty_until'            => $wUntil,
            'filter_replacement_months' => $rMonths,
            'next_maintenance_date'     => $nextMaint,
            'tds_in'                    => $data['tds_in'] ?? null,
            'tds_out'                   => $data['tds_out'] ?? null,
            'pressure'                  => $data['pressure'] ?? null,
            'accessories'               => (function() use ($data, $machine, $installedAt) {
                $rawAcc = $data['accessories'] ?? $machine->accessories;
                if (empty($rawAcc)) return null;
                $parsedItems = [];
                if (is_array($rawAcc)) {
                    $parsedItems = $rawAcc;
                } elseif (is_string($rawAcc)) {
                    $trimmed = trim($rawAcc);
                    if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                        $dec = json_decode($trimmed, true);
                        if (is_array($dec)) $parsedItems = $dec;
                    }
                    if (empty($parsedItems) && !empty($trimmed)) {
                        $parsedItems = [[
                            'name'           => $trimmed,
                            'quantity'       => 1,
                            'installed_date' => $installedAt->format('Y-m-d'),
                            'unit'           => 'Cái',
                        ]];
                    }
                }
                if (!empty($parsedItems) && is_array($parsedItems)) {
                    $normalized = [];
                    foreach ($parsedItems as $item) {
                        if (is_array($item)) {
                            $name = trim($item['name'] ?? $item['part_name'] ?? '');
                            if (!empty($name)) {
                                $normalized[] = [
                                    'name'           => $name,
                                    'quantity'       => (int) ($item['quantity'] ?? $item['qty'] ?? 1),
                                    'installed_date' => !empty($item['installed_date']) ? substr($item['installed_date'], 0, 10) : $installedAt->format('Y-m-d'),
                                    'unit'           => $item['unit'] ?? 'Cái',
                                ];
                            }
                        } elseif (is_string($item) && !empty(trim($item))) {
                            $normalized[] = [
                                'name'           => trim($item),
                                'quantity'       => 1,
                                'installed_date' => $installedAt->format('Y-m-d'),
                                'unit'           => 'Cái',
                            ];
                        }
                    }
                    return json_encode($normalized, JSON_UNESCAPED_UNICODE);
                }
                return null;
            })(),
            'activation_notes'          => $data['activation_notes'] ?? null,
            'status'                    => $autoActivate ? 'activated' : 'pending_activation',
            'activated_at'              => $autoActivate ? now() : null,
            'activated_by'              => $autoActivate ? Auth::id() : null,
            'activated_by_name'         => $autoActivate ? Auth::user()->name : null,
        ]);

        // Gửi thông báo tới quản trị viên
        $admins = User::whereHas('roles', function($q) { $q->where('name', 'admin'); })->get();
        foreach ($admins as $admin) {
            SystemNotification::send(
                $admin->id,
                '📲 Yêu cầu kích hoạt QR máy lọc: ' . $machine->qr_code,
                "KTV {$machine->technician_name} vừa gửi thông tin máy [{$machine->machine_model}] cho KH {$machine->customer_name} ({$machine->customer_phone}). Vui lòng kiểm tra và duyệt.",
                url('/bao-hanh-may-loc'),
                'machine_activation_request',
                $machine
            );
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $autoActivate ? 'Đã kích hoạt bảo hành máy lọc nước thành công!' : 'Đã gửi yêu cầu kích hoạt bảo hành thành công. Vui lòng chờ quản lý duyệt!',
                'redirect'=> url('/m/' . $machine->qr_token),
                'machine' => $machine
            ]);
        }

        session()->flash('success', $autoActivate ? 'Đã kích hoạt bảo hành máy lọc nước thành công!' : 'Đã gửi yêu cầu kích hoạt thành công!');
        return redirect('/m/' . $machine->qr_token);
    }

    /**
     * Khách hàng quét mã QR gửi yêu cầu bảo hành / thay lõi
     */
    public function customerRequestService(Request $request, $token)
    {
        $machine = FilterInstalledMachine::where('qr_token', $token)->firstOrFail();

        $data = $request->validate([
            'customer_phone' => 'required|string|max:30',
            'service_type'   => 'required|string|in:warranty,maintenance,repair',
            'symptom'        => 'required|string|max:1000',
            'appointment_at' => 'nullable|string|max:100',
        ]);

        $typeMap = [
            'warranty'    => 'Yêu cầu bảo hành máy chính hãng',
            'maintenance' => 'Yêu cầu thay lõi lọc định kỳ',
            'repair'      => 'Báo sự cố máy hỏng / rò rỉ nước',
        ];

        $code = 'SC-' . date('Ymd') . '-' . sprintf('%03d', FilterRepair::whereDate('created_at', today())->count() + 1);

        $repair = FilterRepair::create([
            'code'             => $code,
            'customer_name'    => $machine->customer_name ?: 'Khách hàng quét QR',
            'customer_phone'   => $data['customer_phone'],
            'customer_address' => $machine->customer_address,
            'repair_type'      => 'at_home',
            'warranty_type'    => ($machine->warranty_status === 'active' || $machine->warranty_status === 'expiring_soon') ? 'in_warranty' : 'out_warranty',
            'machine_model_id' => $machine->machine_model_id,
            'machine_model'    => $machine->machine_model,
            'machine_serial'   => $machine->machine_serial,
            'technician_id'    => $machine->technician_id,
            'technician_name'  => $machine->technician_name,
            'symptom'          => "[{$typeMap[$data['service_type']]}] " . $data['symptom'] . ($data['appointment_at'] ? " - Hẹn: {$data['appointment_at']}" : ""),
            'status'           => 'received',
            'received_date'    => now(),
            'notes'            => "Yêu cầu tự động gửi từ khách hàng quét mã QR [{$machine->qr_code}]",
        ]);

        if ($machine->technician_id) {
            SystemNotification::send(
                $machine->technician_id,
                '🔔 Khách hàng quét QR gửi yêu cầu dịch vụ',
                "Khách {$machine->customer_name} ({$data['customer_phone']}) vừa gửi [{$typeMap[$data['service_type']]}] cho máy {$machine->machine_model}.",
                url('/sua-chua-may-loc'),
                'customer_service_requested',
                $repair
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi yêu cầu hỗ trợ thành công! Kỹ thuật viên Đại Sơn sẽ liên hệ với bạn trong thời gian sớm nhất.',
            'repair_code' => $repair->code,
        ]);
    }

    /**
     * In tem dán mã QR dán lên máy lọc nước
     */
    public function printQrStickers(Request $request)
    {
        $ids = $request->input('ids');
        if (!empty($ids)) {
            $idArray = is_array($ids) ? $ids : explode(',', $ids);
            $machines = FilterInstalledMachine::whereIn('id', $idArray)->get();
        } else {
            $machines = FilterInstalledMachine::latest('id')->take(24)->get();
        }

        return view('water_filter.qr_print', compact('machines'));
    }

    /**
     * KTV hoặc Quản trị viên quét mã QR / gán tem QR để cài đặt thông tin máy & kích hoạt bảo hành cho đơn lắp đặt
     */
    public function bindQrToInstallation(Request $request, $id = null)
    {
        $installationId = $id ?: $request->input('installation_id');
        $installation = FilterInstallation::findOrFail($installationId);

        $qrInput = trim($request->input('qr_code') ?: $request->input('qr_token') ?: '');
        if (empty($qrInput)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã QR hoặc quét tem dán.'
            ], 422);
        }

        // Nếu chuỗi chứa URL (vd: http://127.0.0.1:8001/m/ABC123456789), bóc tách mã
        if (str_contains($qrInput, '/m/')) {
            $parts = explode('/m/', $qrInput);
            $tokenCandidate = trim(end($parts), " /?#\t\n\r\0\x0B");
            $machine = FilterInstalledMachine::where('qr_token', $tokenCandidate)
                ->orWhere('qr_code', $tokenCandidate)
                ->first();
            $cleanCode = $machine?->qr_code ?: $tokenCandidate;
        } elseif (str_contains($qrInput, '/qr/')) {
            $parts = explode('/qr/', $qrInput);
            $codeCandidate = trim(end($parts), " /?#\t\n\r\0\x0B");
            $machine = FilterInstalledMachine::where('qr_code', $codeCandidate)
                ->orWhere('qr_token', $codeCandidate)
                ->first();
            $cleanCode = $codeCandidate;
        } else {
            $machine = FilterInstalledMachine::where('qr_code', $qrInput)
                ->orWhere('qr_token', $qrInput)
                ->first();
            $cleanCode = $qrInput;
        }

        // Nếu tem QR chưa tồn tại trong hệ thống, tự động tạo mới tem QR để kích hoạt ngay!
        if (!$machine) {
            $lastId = FilterInstalledMachine::max('id') ?? 0;
            $newCode = 'QR-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
            $machine = FilterInstalledMachine::create([
                'machine_code'      => 'MLN-' . date('Ymd') . '-' . sprintf('%03d', $lastId + 1),
                'qr_code'           => $cleanCode ?: $newCode,
                'qr_token'          => \Illuminate\Support\Str::random(12),
                'status'            => 'unactivated',
                'created_by'        => Auth::id(),
            ]);
        }

        // Cập nhật thông tin chi tiết máy lọc từ đơn lắp đặt & thông số kỹ thuật thực tế
        $installedAt = $request->input('installed_at') ? Carbon::parse($request->input('installed_at')) : now();
        $warrantyMonths = (int) ($request->input('warranty_months') ?: 24);
        $warrantyUntil = (clone $installedAt)->addMonths($warrantyMonths)->format('Y-m-d');
        $filterReplacementMonths = (int) ($request->input('filter_replacement_months') ?: 6);
        $nextMaintenanceDate = (clone $installedAt)->addMonths($filterReplacementMonths)->format('Y-m-d');

        $user = Auth::user();
        $techId = $request->input('technician_id', $installation->technician_id ?: $user?->id);
        $tech = $techId ? User::find($techId) : null;
        $techName = $tech?->name ?? ($installation->technician_name ?: $user?->name);

        $customerName = trim($request->input('customer_name') ?: $installation->customer_name);
        $customerPhone = trim($request->input('customer_phone') ?: $installation->customer_phone);
        $customerAddress = trim($request->input('customer_address') ?: $installation->customer_address);

        $machineModel = $request->input('machine_model', $installation->machine_model);
        $machineModelId = $request->input('machine_model_id', $installation->machine_model_id);
        $machineSerial = $request->input('machine_serial', $installation->machine_serial);
        $waterSource = $request->input('water_source_type', $installation->water_source_type ?? 'Nước máy thành phố');
        $tdsIn = $request->filled('tds_in') ? (int) $request->input('tds_in') : $installation->tds_in;
        $tdsOut = $request->filled('tds_out') ? (int) $request->input('tds_out') : $installation->tds_out;
        $pressure = $request->input('pressure', $installation->pressure);
        $notes = $request->input('activation_notes', $installation->completion_notes);

        // Chuẩn hóa phụ kiện kèm theo máy sang dạng JSON: [{name, quantity, installed_date, unit}]
        $accessoriesInput = $request->input('accessories');
        $accessoriesJson = null;
        if (!empty($accessoriesInput)) {
            $parsedItems = [];
            if (is_array($accessoriesInput)) {
                $parsedItems = $accessoriesInput;
            } elseif (is_string($accessoriesInput)) {
                $trimmed = trim($accessoriesInput);
                if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                    $decoded = json_decode($trimmed, true);
                    if (is_array($decoded)) {
                        $parsedItems = $decoded;
                    }
                }
                if (empty($parsedItems) && !empty($trimmed)) {
                    $parsedItems = [[
                        'name'           => $trimmed,
                        'quantity'       => 1,
                        'installed_date' => date('Y-m-d'),
                        'unit'           => 'Cái',
                    ]];
                }
            }

            if (!empty($parsedItems) && is_array($parsedItems)) {
                $normalized = [];
                foreach ($parsedItems as $item) {
                    if (is_array($item)) {
                        $name = trim($item['name'] ?? $item['part_name'] ?? '');
                        if (!empty($name)) {
                            $normalized[] = [
                                'name'           => $name,
                                'quantity'       => (int) ($item['quantity'] ?? $item['qty'] ?? 1),
                                'installed_date' => !empty($item['installed_date']) ? substr($item['installed_date'], 0, 10) : date('Y-m-d'),
                                'unit'           => $item['unit'] ?? 'Cái',
                            ];
                        }
                    } elseif (is_string($item) && !empty(trim($item))) {
                        $normalized[] = [
                            'name'           => trim($item),
                            'quantity'       => 1,
                            'installed_date' => date('Y-m-d'),
                            'unit'           => 'Cái',
                        ];
                    }
                }
                $accessoriesJson = json_encode($normalized, JSON_UNESCAPED_UNICODE);
            }
        }

        $machine->update([
            'installation_id'           => $installation->id,
            'customer_name'             => $customerName,
            'customer_phone'            => $customerPhone,
            'customer_address'          => $customerAddress,
            'water_source_type'         => $waterSource,
            'machine_model_id'          => $machineModelId,
            'machine_model'             => $machineModel,
            'machine_serial'            => $machineSerial,
            'technician_id'             => $techId,
            'technician_name'           => $techName,
            'installed_at'              => $installedAt,
            'activated_at'              => now(),
            'activated_by'              => $user?->id,
            'activated_by_name'         => $user?->name ?? 'Kỹ thuật viên',
            'warranty_months'           => $warrantyMonths,
            'warranty_until'            => $warrantyUntil,
            'warranty_card_code'        => $machine->qr_code,
            'filter_replacement_months' => $filterReplacementMonths,
            'next_maintenance_date'     => $nextMaintenanceDate,
            'tds_in'                    => $tdsIn,
            'tds_out'                   => $tdsOut,
            'pressure'                  => $pressure,
            'accessories'               => $accessoriesJson,
            'activation_notes'          => $notes,
            'status'                    => 'activated',
        ]);

        // Cập nhật trạng thái đơn lắp đặt thành 'completed' và đồng bộ thông tin khách hàng mới
        $installation->update([
            'status'           => 'completed',
            'completed_date'   => now(),
            'customer_name'    => $customerName,
            'customer_phone'   => $customerPhone,
            'customer_address' => $customerAddress,
            'machine_serial'   => $machineSerial,
            'water_source_type'=> $waterSource,
            'tds_in'           => $tdsIn,
            'tds_out'          => $tdsOut,
            'pressure'         => $pressure,
            'completion_notes' => $notes,
        ]);

        // Cập nhật TaskAssignment nếu có
        $task = \App\Models\TaskAssignment::where('related_order_code', $installation->code)->first();
        if ($task) {
            $task->update([
                'status'           => 'Hoàn thành',
                'progress_percent' => 100,
                'completed_at'     => now(),
                'feedback'         => 'Đã lắp đặt hoàn tất & kích hoạt tem QR: ' . $machine->qr_code,
            ]);
        }

        // Gửi thông báo nổi tới Quản trị viên
        $admins = User::whereHas('roles', function($q) { $q->where('name', 'admin'); })->get();
        foreach ($admins as $admin) {
            SystemNotification::send(
                $admin->id,
                '✅ KTV đã lắp đặt & kích hoạt QR: ' . $machine->qr_code,
                "KTV {$techName} vừa hoàn tất lắp đặt đơn [{$installation->code}] cho KH {$installation->customer_name} ({$installation->customer_phone}) và kích hoạt tem QR bảo hành.",
                url('/m/' . $machine->qr_token),
                'machine_activated',
                $machine
            );
        }

        return response()->json([
            'success'      => true,
            'message'      => "Đã liên kết mã QR [{$machine->qr_code}] và kích hoạt bảo hành cho đơn lắp đặt!",
            'machine'      => $machine,
            'installation' => $installation,
            'passport_url' => url('/m/' . $machine->qr_token),
            'qr_code'      => $machine->qr_code,
        ]);
    }
}
