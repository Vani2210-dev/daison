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
use App\Models\Dealer;
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
        // Nếu URL truyền thẳng install_id hoặc mã đơn cụ thể LD-... (từ thông báo), điều hướng thẳng đến đơn đó
        if ($request->filled('install_id')) {
            return redirect()->to(url('/water-filter/installations/' . $request->input('install_id') . '/edit'));
        }
        if ($request->filled('search') && str_starts_with(strtoupper(trim($request->input('search'))), 'LD-')) {
            $exactInst = FilterInstallation::where('code', trim($request->input('search')))->first();
            if ($exactInst && $request->input('tab', 'installations') === 'installations') {
                return redirect()->to(url('/water-filter/installations/' . $exactInst->id . '/edit'));
            }
        }

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

        $maintenanceDueCount = FilterInstalledMachine::whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<=', now()->addDays(30))
            ->where('status', '!=', 'unactivated')
            ->count();

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
            'maintenanceDueCount',
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
            'default_parts'  => 'nullable|array',
        ]);

        $id = $data['id'] ?? null;
        unset($data['id']);

        // Xử lý và làm sạch danh sách phụ tùng cấu hình mặc định
        if (isset($data['default_parts']) && is_array($data['default_parts'])) {
            $cleaned = [];
            foreach ($data['default_parts'] as $dp) {
                $name = trim($dp['part_name'] ?? '');
                $partId = !empty($dp['part_id']) ? (int)$dp['part_id'] : null;
                if (!empty($name) || !empty($partId)) {
                    $cleaned[] = [
                        'part_id'         => $partId,
                        'part_code'       => trim($dp['part_code'] ?? ''),
                        'part_name'       => $name,
                        'quantity'        => !empty($dp['quantity']) ? max(1, (int)$dp['quantity']) : 1,
                        'position'        => trim($dp['position'] ?? ''),
                        'lifespan_months' => !empty($dp['lifespan_months']) ? (int)$dp['lifespan_months'] : null,
                        'note'            => trim($dp['note'] ?? ''),
                    ];
                }
            }
            $data['default_parts'] = $cleaned;
        } else {
            $data['default_parts'] = [];
        }

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

    /**
     * Mở trang tạo mới Model máy lọc nước (Không dùng modal)
     */
    public function createModelView(Request $request)
    {
        $parts = FilterPart::where('status', 'active')->orderBy('name')->get();
        $model = null;

        return view('water_filter.models.form', compact('parts', 'model'));
    }

    /**
     * Mở trang chỉnh sửa Model máy lọc nước (Không dùng modal)
     */
    public function editModelView(Request $request, $id)
    {
        $model = FilterMachineModel::findOrFail($id);
        $parts = FilterPart::where('status', 'active')->orderBy('name')->get();

        return view('water_filter.models.form', compact('parts', 'model'));
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
        $customers = Dealer::select('id', 'code', 'name', 'phone', 'address', 'district', 'city')->orderBy('name')->get();
        $installation = null;

        return view('water_filter.installations.form', compact(
            'technicians',
            'machineModels',
            'parts',
            'statuses',
            'customers',
            'installation'
        ));
    }

    /**
     * Mở trang chỉnh sửa đơn lắp đặt (Không dùng modal)
     */
    public function editInstallationView(Request $request, $id)
    {
        $installation = FilterInstallation::with(['items', 'technician', 'modelCatalog', 'installedMachine', 'dealer'])->findOrFail($id);
        $technicians = User::where('status', '!=', 'inactive')
            ->select('id', 'name', 'phone', 'department')
            ->orderBy('name')
            ->get();
        $machineModels = FilterMachineModel::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
        $parts = FilterPart::where('status', 'active')->orderBy('name')->get();
        $statuses = self::getInstallationStatuses();
        $customers = Dealer::select('id', 'code', 'name', 'phone', 'address', 'district', 'city')->orderBy('name')->get();

        return view('water_filter.installations.form', compact(
            'technicians',
            'machineModels',
            'parts',
            'statuses',
            'customers',
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
            'id', 'dealer_id', 'machine_model_id', 'technician_id', 'appointment_date',
            'installed_at', 'warranty_until', 'warranty_card_code', 'warranty_notes',
            'next_maintenance_date', 'warranty_months', 'filter_replacement_months',
            'customer_notes', 'completion_notes', 'machine_serial',
            'machine_price', 'discount_amount', 'surcharge_amount'
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
                $isFree = !empty($it['is_free']) || (isset($it['unit_price']) && (float)$it['unit_price'] <= 0);
                if ($isFree) {
                    $it['unit_price'] = 0;
                    $it['is_free'] = true;
                } else {
                    $it['unit_price'] = (isset($it['unit_price']) && trim((string)$it['unit_price']) !== '') ? (float)$it['unit_price'] : 0;
                    $it['is_free'] = false;
                }
            }
            unset($it);
        }
        $request->merge($input);

        $data = $request->validate([
            'id'                        => 'nullable|integer',
            'dealer_id'                 => 'nullable|integer',
            'customer_name'             => 'required|string|max:255',
            'customer_phone'            => 'required|string|max:30',
            'customer_address'          => 'required|string',
            'machine_model_id'          => 'nullable|integer',
            'machine_model'             => 'nullable|string|max:255',
            'machine_price'             => 'nullable|numeric|min:0',
            'surcharge_amount'          => 'nullable|numeric|min:0',
            'discount_amount'           => 'nullable|numeric|min:0',
            'machine_serial'            => 'nullable|string|max:100',
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
            'payment_status'            => 'nullable|string|in:unpaid,paid,free',
            'customer_notes'            => 'nullable|string',
            'completion_notes'          => 'nullable|string',
            'items'                     => 'nullable|array',
            'items.*.part_id'           => 'nullable|integer',
            'items.*.part_name'         => 'required|string',
            'items.*.unit'              => 'nullable|string',
            'items.*.quantity'          => 'required|integer|min:1',
            'items.*.is_free'           => 'nullable|boolean',
            'items.*.unit_price'        => 'nullable|numeric|min:0',
            'items.*.note'              => 'nullable|string',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $id = $data['id'] ?? null;
            $itemsData = $data['items'] ?? [];
            unset($data['id'], $data['items']);

            // Đồng bộ & Lưu vào danh mục Khách hàng (dealers)
            $cName = trim($data['customer_name']);
            $cPhone = trim($data['customer_phone']);
            $cAddress = trim($data['customer_address']);
            $dealerId = !empty($data['dealer_id']) ? (int)$data['dealer_id'] : null;

            $dealer = null;
            if ($dealerId) {
                $dealer = Dealer::find($dealerId);
            }
            if (!$dealer && !empty($cPhone)) {
                $dealer = Dealer::where('phone', $cPhone)->first();
            }
            if (!$dealer && !empty($cName)) {
                $dealer = Dealer::where('name', $cName)->first();
            }

            if (!$dealer && (!empty($cName) || !empty($cPhone))) {
                // Tự động tạo mới khách hàng vào bảng dealers
                $count = Dealer::count() + 1;
                $code = 'KH-' . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
                while (Dealer::where('code', $code)->exists()) {
                    $count++;
                    $code = 'KH-' . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
                }
                $dealer = Dealer::create([
                    'code'              => $code,
                    'name'              => $cName,
                    'owner_name'        => $cName,
                    'phone'             => $cPhone,
                    'address'           => $cAddress,
                    'tier'              => 'Khách lẻ',
                    'credit_limit'      => 0,
                    'initial_debt'      => 0,
                    'current_debt'      => 0,
                    'payment_term_days' => 0,
                    'status'            => 'Hoạt động',
                ]);
            } elseif ($dealer) {
                // Cập nhật bổ sung địa chỉ hoặc SĐT nếu khách hàng cũ chưa có
                $updates = [];
                if (empty($dealer->address) && !empty($cAddress)) {
                    $updates['address'] = $cAddress;
                }
                if (empty($dealer->phone) && !empty($cPhone)) {
                    $updates['phone'] = $cPhone;
                }
                if (!empty($updates)) {
                    $dealer->update($updates);
                }
            }

            $data['dealer_id'] = $dealer?->id;

            // Đảm bảo các trường nullable đúng kiểu và không bị rỗng ''
            $data['machine_model_id'] = !empty($data['machine_model_id']) ? (int)$data['machine_model_id'] : null;
            $data['technician_id'] = !empty($data['technician_id']) ? (int)$data['technician_id'] : null;
            $data['appointment_date'] = !empty($data['appointment_date']) ? date('Y-m-d H:i:s', strtotime($data['appointment_date'])) : null;
            $data['status'] = $data['status'] ?? 'pending';
            $data['payment_status'] = $data['payment_status'] ?? 'unpaid';
            $data['warranty_months'] = isset($data['warranty_months']) && is_numeric($data['warranty_months']) ? (int)$data['warranty_months'] : 24;
            $data['filter_replacement_months'] = isset($data['filter_replacement_months']) && is_numeric($data['filter_replacement_months']) ? (int)$data['filter_replacement_months'] : 6;

            // Xử lý Ngày Lắp Đặt & Hạn Bảo Hành
            if ($data['status'] === 'completed' && empty($data['installed_at'])) {
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

            // [Phương án 3] Tính toán giá máy, phụ kiện phát sinh, phụ thu nâng cấp, chiết khấu và tổng cần thu
            $machinePrice = (float)($data['machine_price'] ?? 0);
            $surchargeAmount = (float)($data['surcharge_amount'] ?? 0);
            $discountAmount = (float)($data['discount_amount'] ?? 0);

            $partsFee = 0;
            foreach ($itemsData as &$it) {
                $isFree = !empty($it['is_free']) || ((float)($it['unit_price'] ?? 0) <= 0);
                $unitPrice = $isFree ? 0 : (float)($it['unit_price'] ?? 0);
                $it['unit_price'] = $unitPrice;
                $it['is_free'] = $isFree;
                $partsFee += ((int)($it['quantity'] ?? 1) * $unitPrice);
            }
            unset($it);

            $data['machine_price'] = $machinePrice;
            $data['parts_fee'] = $partsFee;
            $data['surcharge_amount'] = $surchargeAmount;
            $data['discount_amount'] = $discountAmount;
            $data['total_amount'] = max(0, $machinePrice + $partsFee + $surchargeAmount - $discountAmount);

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

                // Gửi thông báo cho người tạo đơn nếu người sửa là người khác
                if ($installation->created_by && $installation->created_by != Auth::id()) {
                    \App\Models\SystemNotification::send(
                        $installation->created_by,
                        '📝 Đơn lắp đặt [' . $installation->code . '] vừa được cập nhật',
                        (Auth::user()->name ?? 'Nhân viên') . " vừa cập nhật lại thông tin đơn lắp đặt [{$installation->code}] cho KH {$installation->customer_name}.",
                        url('/water-filter/installations/' . $installation->id . '/edit'),
                        'installation_updated',
                        $installation
                    );
                }
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
                    'is_free'    => !empty($it['is_free']),
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
                $taskDeadline = $installation->appointment_date 
                    ? date('Y-m-d H:i:s', strtotime($installation->appointment_date)) 
                    : now()->addDay()->setTime(18, 0, 0)->format('Y-m-d H:i:s');

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
                        'deadline'         => $taskDeadline,
                        'priority'         => 'high',
                        'status'           => in_array($installation->status, ['completed', 'inspected']) ? 'Hoàn thành' : 'Đang thực hiện',
                        'progress_percent' => in_array($installation->status, ['completed', 'inspected']) ? 100 : 0,
                    ]
                );
            }

            $returnUrl = $request->input('return_url');
            if (!$request->expectsJson() && !$request->ajax()) {
                session()->flash('success', $message);
                return redirect($returnUrl ?: '/water-filter?tab=installations');
            }

            return response()->json([
                'success'      => true,
                'message'      => $message,
                'redirect'     => $returnUrl ?: url('/water-filter?tab=installations'),
                'installation' => $installation->load('items'),
            ]);
        });
    }

    public function updateInstallationStatus(Request $request, $id)
    {
        $statusKeys = implode(',', array_keys(self::getInstallationStatuses()));

        $input = $request->all();
        if (isset($input['completion_notes']) && trim((string)$input['completion_notes']) === '') {
            $input['completion_notes'] = null;
        }
        $request->merge($input);

        $data = $request->validate([
            'status'           => 'required|string|in:' . $statusKeys,
            'completion_notes' => 'nullable|string',
            'payment_status'   => 'nullable|string|in:unpaid,paid,free',
        ]);

        return DB::transaction(function () use ($id, $data) {
            $installation = FilterInstallation::with('items.part')->lockForUpdate()->findOrFail($id);
            $oldStatus = $installation->status;
            $newStatus = $data['status'];

            $installation->status = $newStatus;
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
                        'dealer_id'                 => $installation->dealer_id,
                        'status'                    => 'activated',
                        'customer_name'             => $installation->customer_name,
                        'customer_phone'            => $installation->customer_phone,
                        'customer_address'          => $installation->customer_address,
                        'machine_model_id'          => $installation->machine_model_id,
                        'machine_model'             => $installation->machine_model,
                        'machine_serial'            => $installation->machine_serial,
                        // KTV phụ trách bảo dưỡng để trống (Chưa phân công) để người có quyền phân công hoặc KTV tự nhận việc
                        'technician_id'             => null,
                        'technician_name'           => null,
                        'installed_at'              => $installation->installed_at,
                        'activated_at'              => now(),
                        'activated_by'              => Auth::id(),
                        'activated_by_name'         => Auth::user()?->name ?? 'Admin',
                        'warranty_months'           => $installation->warranty_months ?: 24,
                        'warranty_until'            => $installation->warranty_until,
                        'warranty_card_code'        => $installation->warranty_card_code ?: $installation->code,
                        'filter_replacement_months' => $installation->filter_replacement_months ?: 6,
                        'next_maintenance_date'     => $installation->next_maintenance_date,
                        'created_by'                => Auth::id(),
                    ]);
                } else {
                    $existingMachine->update([
                        'status'                    => 'activated',
                        'dealer_id'                 => $installation->dealer_id,
                        'customer_name'             => $installation->customer_name,
                        'customer_phone'            => $installation->customer_phone,
                        'customer_address'          => $installation->customer_address,
                        'machine_model'             => $installation->machine_model,
                        'machine_serial'            => $installation->machine_serial,
                        // KTV phụ trách bảo dưỡng để trống (Chưa phân công) để người có quyền phân công hoặc KTV tự nhận việc
                        'technician_id'             => null,
                        'technician_name'           => null,
                        'installed_at'              => $installation->installed_at,
                        'activated_at'              => now(),
                        'warranty_months'           => $installation->warranty_months ?: 24,
                        'warranty_until'            => $installation->warranty_until,
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

        $canAssign = self::canUserAssignTasks(Auth::user());
        $currentUserId = Auth::id();

        if ($techId = $request->input('technician_id')) {
            if ($techId === 'unclaimed') {
                $query->where(function ($q) {
                    $q->whereNull('technician_id')->orWhere('technician_id', 0);
                });
            } elseif ($techId === 'my_jobs') {
                $query->where('technician_id', $currentUserId);
            } else {
                $query->where('technician_id', $techId);
            }
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

        $canAssign = self::canUserAssignTasks(Auth::user());
        $currentUserId = Auth::id();

        if ($techId = $request->input('technician_id')) {
            if ($techId === 'unclaimed') {
                $query->where(function ($q) {
                    $q->whereNull('technician_id')->orWhere('technician_id', 0);
                });
            } elseif ($techId === 'my_jobs') {
                $query->where('technician_id', $currentUserId);
            } else {
                $query->where('technician_id', $techId);
            }
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
            'machine_model_id'          => 'nullable|integer',
            'machine_model'             => 'required|string|max:255',
            'machine_serial'            => 'nullable|string|max:100',
            'technician_id'             => 'nullable|integer',
            'technician_name'           => 'nullable|string|max:255',
            'installed_at'              => 'nullable|date',
            'warranty_months'           => 'required|integer|min:1',
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
        $notes = $request->has('activation_notes') ? $request->input('activation_notes') : $installation->completion_notes;

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
                            $isFree = isset($item['is_free']) ? (bool)$item['is_free'] : (!empty($item['unit_price']) ? false : true);
                            $unitPrice = $isFree ? 0 : (float)($item['unit_price'] ?? 0);
                            $normalized[] = [
                                'name'               => $name,
                                'quantity'           => (int) ($item['quantity'] ?? $item['qty'] ?? 1),
                                'installed_date'     => !empty($item['installed_date']) ? substr($item['installed_date'], 0, 10) : date('Y-m-d'),
                                'unit'               => $item['unit'] ?? 'Cái',
                                'maintenance_months' => isset($item['maintenance_months']) ? (int) $item['maintenance_months'] : 6,
                                'is_free'            => $isFree,
                                'unit_price'         => $unitPrice,
                            ];
                        }
                    } elseif (is_string($item) && !empty(trim($item))) {
                        $normalized[] = [
                            'name'               => trim($item),
                            'quantity'           => 1,
                            'installed_date'     => date('Y-m-d'),
                            'unit'               => 'Cái',
                            'maintenance_months' => 6,
                            'is_free'            => true,
                            'unit_price'         => 0,
                        ];
                    }
                }
                $accessoriesJson = json_encode($normalized, JSON_UNESCAPED_UNICODE);
            }
        }

        // Ghi nhận các thông tin KTV đã thay đổi so với đơn ban đầu
        $changes = [];
        if (!empty($customerName) && $customerName !== $installation->customer_name) {
            $changes[] = "Tên KH: '{$installation->customer_name}' ➔ '{$customerName}'";
        }
        if (!empty($customerPhone) && $customerPhone !== $installation->customer_phone) {
            $changes[] = "SĐT: '{$installation->customer_phone}' ➔ '{$customerPhone}'";
        }
        if (!empty($customerAddress) && $customerAddress !== $installation->customer_address) {
            $changes[] = "Địa chỉ: '{$installation->customer_address}' ➔ '{$customerAddress}'";
        }
        if (!empty($machineModel) && $machineModel !== $installation->machine_model) {
            $changes[] = "Model máy: '{$installation->machine_model}' ➔ '{$machineModel}'";
        }
        if (!empty($machineSerial) && $machineSerial !== $installation->machine_serial) {
            $changes[] = "Số Serial: " . ($installation->machine_serial ?: '(trống)') . " ➔ '{$machineSerial}'";
        }
        if (!empty($normalized)) {
            $partNames = array_column($normalized, 'name');
            $changes[] = "Phụ kiện đi kèm (" . count($normalized) . "): " . implode(', ', array_slice($partNames, 0, 3)) . (count($partNames) > 3 ? '...' : '');
        }

        $machine->update([
            'installation_id'           => $installation->id,
            'customer_name'             => $customerName,
            'customer_phone'            => $customerPhone,
            'customer_address'          => $customerAddress,
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
            'accessories'               => $accessoriesJson,
            'activation_notes'          => $notes,
            'status'                    => 'activated',
        ]);

        // Cập nhật trạng thái đơn lắp đặt thành 'completed' và đồng bộ toàn bộ thông tin KTV đã điều chỉnh
        $installation->update([
            'status'                    => 'completed',
            'completed_date'            => now(),
            'customer_name'             => $customerName,
            'customer_phone'            => $customerPhone,
            'customer_address'          => $customerAddress,
            'machine_model_id'          => $machineModelId,
            'machine_model'             => $machineModel,
            'machine_serial'            => $machineSerial,
            'warranty_months'           => $warrantyMonths,
            'warranty_until'            => $warrantyUntil,
            'warranty_card_code'        => $machine->qr_code,
            'filter_replacement_months' => $filterReplacementMonths,
            'next_maintenance_date'     => $nextMaintenanceDate,
            'installed_at'              => $installedAt,
            'completion_notes'          => $notes,
        ]);

        // Đồng bộ phụ kiện KTV lắp đặt vào danh sách items của đơn hàng
        if (!empty($normalized)) {
            $existingItems = $installation->items()->get()->keyBy(function($it) {
                return mb_strtolower(trim($it->part_name));
            });

            $installation->items()->delete();
            $partsFee = 0;
            foreach ($normalized as $item) {
                $pName = trim($item['name'] ?? '');
                if (empty($pName)) continue;

                $existing = $existingItems->get(mb_strtolower($pName));
                $part = FilterPart::where('name', $pName)->orWhere('code', $pName)->first();

                // Xác định phụ kiện này có tính tiền thêm không hay kèm theo máy (0đ)
                $isFree = isset($item['is_free']) ? (bool)$item['is_free'] : ($existing ? (bool)$existing->is_free : true);
                if (isset($item['unit_price']) && (float)$item['unit_price'] > 0) {
                    $unitPrice = (float)$item['unit_price'];
                    $isFree = false;
                } else {
                    $unitPrice = $isFree ? 0 : ($existing ? (float)$existing->unit_price : ($part ? (float)$part->out_price : 0));
                }

                $qty = (int)($item['quantity'] ?? 1);
                $totalPrice = $isFree ? 0 : ($qty * $unitPrice);
                $partsFee += $totalPrice;

                $noteText = $existing?->note ?? ($item['note'] ?? null);

                $installation->items()->create([
                    'part_id'    => $existing?->part_id ?: $part?->id,
                    'part_code'  => $existing?->part_code ?: $part?->code,
                    'part_name'  => $pName,
                    'unit'       => $item['unit'] ?? ($existing?->unit ?: ($part?->unit ?? 'Cái')),
                    'quantity'   => $qty,
                    'is_free'    => $isFree,
                    'unit_price' => $unitPrice,
                    'total_price'=> $totalPrice,
                    'note'       => $noteText,
                ]);
            }
            $machinePrice = (float)($installation->machine_price ?? 0);
            $surchargeAmount = (float)($installation->surcharge_amount ?? 0);
            $discountAmount = (float)($installation->discount_amount ?? 0);
            $installation->update([
                'parts_fee'    => $partsFee,
                'total_amount' => max(0, $machinePrice + $partsFee + $surchargeAmount - $discountAmount),
            ]);
        }

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

        // 1. Xác định Người Tạo Đơn, Người Giao Việc & Ban Quản Trị để gửi thông báo
        $orderCreatorId = $installation->created_by;
        $taskCreatorId = $task?->created_by_id;

        $relatedSalesOrder = null;
        if (!empty($installation->code)) {
            $relatedSalesOrder = \App\Models\Order::where('order_code', $installation->code)->first();
        }
        if (!$relatedSalesOrder && !empty($installation->customer_phone)) {
            $relatedSalesOrder = \App\Models\Order::where('customer_phone', $installation->customer_phone)->latest('id')->first();
        }
        $salesOrderCreatorId = $relatedSalesOrder?->creator_id;

        // Tập hợp danh sách người nhận (loại bỏ trùng lặp và null)
        $creatorRecipients = collect([$orderCreatorId, $taskCreatorId, $salesOrderCreatorId])
            ->filter()
            ->unique();

        $admins = User::whereHas('roles', function($q) { $q->where('name', 'admin'); })->pluck('id');
        $allNotifyUserIds = $creatorRecipients->merge($admins)->unique();

        $hasChanges = !empty($changes);
        $notifyTitle = $hasChanges
            ? "📝 [{$installation->code}] KTV đã cập nhật thông tin đơn & nghiệm thu máy"
            : "✅ [{$installation->code}] KTV đã lắp đặt & kích hoạt QR: " . $machine->qr_code;

        $changeDetails = $hasChanges
            ? "\n• Thông tin được KTV điều chỉnh:\n  - " . implode("\n  - ", $changes)
            : "";

        $notifyMessage = "KTV {$techName} vừa hoàn tất nghiệm thu đơn lắp đặt [{$installation->code}] cho KH {$customerName} ({$customerPhone})."
            . $changeDetails
            . "\n• Mã tem QR bảo hành: {$machine->qr_code}"
            . ($machineSerial ? " | Serial: {$machineSerial}" : "");

        $actionUrl = url('/water-filter/installations/' . $installation->id . '/edit');

        foreach ($allNotifyUserIds as $targetUserId) {
            SystemNotification::send(
                $targetUserId,
                $notifyTitle,
                $notifyMessage,
                $actionUrl,
                $hasChanges ? 'order_info_updated' : 'machine_activated',
                $machine,
                [
                    'installation_id'   => $installation->id,
                    'installation_code' => $installation->code,
                    'qr_code'           => $machine->qr_code,
                    'qr_token'          => $machine->qr_token,
                    'changes'           => $changes,
                    'technician_name'   => $techName,
                    'passport_url'      => url('/m/' . $machine->qr_token),
                ]
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

    /* -------------------------------------------------------------
     * 7. LỊCH BẢO DƯỠNG & THAY LÕI ĐỊNH KỲ
     * ------------------------------------------------------------- */

    /**
     * Kiểm tra user có quyền phân công công việc / bảo dưỡng hay không
     */
    public static function canUserAssignTasks(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) return false;
        if ($user->role === 'admin' || $user->role === 'manager' || !empty($user->is_super_admin) || $user->hasRole('admin') || $user->hasRole('Admin') || $user->hasRole('Manager')) {
            return true;
        }
        return $user->hasPermission('water_filter.repair_assign') 
            || $user->hasPermission('water_filter.install_assign') 
            || $user->hasPermission('task_assignment.assign') 
            || $user->hasPermission('task_assignment.view_all');
    }

    /**
     * Trích xuất gợi ý khu vực (Xã / Phường / Đường / Huyện) từ địa chỉ khách hàng
     */
    public static function extractAreaFromAddress(?string $address, ?string $ward = null, ?string $district = null): string
    {
        if (!empty($ward)) {
            $w = trim($ward);
            return (preg_match('/^(xã|phường|thị trấn|p\.|tt\.)/ui', $w)) ? $w : 'P./Xã ' . $w;
        }

        if (empty($address)) {
            return !empty($district) ? trim($district) : 'Chưa rõ khu vực';
        }

        $clean = trim($address);

        // 1. Phường / P. (loại trừ TP.)
        if (preg_match('/(?<!T)(Phường|P\.)\s*([^,]+)/ui', $clean, $matches)) {
            return 'P. ' . trim($matches[2]);
        }
        // 2. Xã / Thị trấn / TT.
        if (preg_match('/(Xã|Thị trấn|TT\.)\s*([^,]+)/ui', $clean, $matches)) {
            $prefix = in_array(mb_strtolower($matches[1]), ['xã']) ? 'Xã ' : 'TT. ';
            return $prefix . trim($matches[2]);
        }
        // 3. Đường / Phố
        if (preg_match('/(Đường|Đ\.|Phố)\s*([^,]+)/ui', $clean, $matches)) {
            return 'Đường ' . trim($matches[2]);
        }
        // 4. Khu dân cư / Khu đô thị / Chung cư
        if (preg_match('/(Khu dân cư|Khu đô thị|KĐT|Chung cư)\s*([^,]+)/ui', $clean, $matches)) {
            return trim($matches[1]) . ' ' . trim($matches[2]);
        }
        // 5. Nếu có dấu phẩy: lấy phần gần cuối (thường là Xã/Phường/Quận/Huyện)
        $parts = array_map('trim', explode(',', $clean));
        if (count($parts) >= 2) {
            return $parts[count($parts) - 2];
        }

        return $parts[0];
    }

    /**
     * Xác định danh sách phụ tùng / lõi lọc cần bảo dưỡng cho máy
     */
    public static function getMaintenancePartsForMachine($machine, $allPartsByCode = [], $allPartsByName = []): array
    {
        $parts = [];

        // 1. Từ default_parts của danh mục Model máy
        if (!empty($machine->modelCatalog?->default_parts) && is_array($machine->modelCatalog->default_parts)) {
            foreach ($machine->modelCatalog->default_parts as $dp) {
                $code = $dp['part_code'] ?? '';
                $name = $dp['part_name'] ?? ($dp['name'] ?? '');
                $matchedPart = ($code && isset($allPartsByCode[$code])) 
                    ? $allPartsByCode[$code] 
                    : (isset($allPartsByName[$name]) ? $allPartsByName[$name] : null);

                $parts[] = [
                    'part_id'       => $dp['part_id'] ?? ($matchedPart?->id ?? null),
                    'part_code'     => $code ?: ($matchedPart?->code ?? ''),
                    'part_name'     => $name ?: ($matchedPart?->name ?? 'Linh kiện máy lọc'),
                    'quantity'      => (int) ($dp['quantity'] ?? 1),
                    'unit'          => $dp['unit'] ?? ($matchedPart?->unit ?? 'Cái'),
                    'stock_qty'     => $matchedPart ? (int) $matchedPart->stock_qty : null,
                    'in_stock'      => $matchedPart ? ($matchedPart->stock_qty > 0) : true,
                    'price'         => $matchedPart ? (float) $matchedPart->out_price : 0,
                    'note'          => $dp['note'] ?? ($dp['position'] ?? ''),
                ];
            }
        }

        // 2. Nếu Model chưa cấu hình, kiểm tra vật tư theo đơn lắp đặt ban đầu
        if (empty($parts) && $machine->installation && $machine->installation->items->count() > 0) {
            foreach ($machine->installation->items as $it) {
                $matchedPart = ($it->part_code && isset($allPartsByCode[$it->part_code])) 
                    ? $allPartsByCode[$it->part_code] 
                    : (isset($allPartsByName[$it->part_name]) ? $allPartsByName[$it->part_name] : null);

                $parts[] = [
                    'part_id'   => $it->part_id ?: ($matchedPart?->id ?? null),
                    'part_code' => $it->part_code ?: ($matchedPart?->code ?? ''),
                    'part_name' => $it->part_name,
                    'quantity'  => (int) ($it->quantity ?? 1),
                    'unit'      => $it->unit ?? ($matchedPart?->unit ?? 'Cái'),
                    'stock_qty' => $matchedPart ? (int) $matchedPart->stock_qty : null,
                    'in_stock'  => $matchedPart ? ($matchedPart->stock_qty > 0) : true,
                    'price'     => (float) ($it->unit_price ?: ($matchedPart?->out_price ?? 0)),
                    'note'      => 'Vật tư theo đơn lắp đặt',
                ];
            }
        }

        // 3. Nếu chưa có, kiểm tra danh sách phụ kiện accessories
        if (empty($parts) && !empty($machine->accessories_list)) {
            foreach ($machine->accessories_list as $acc) {
                $name = is_array($acc) ? ($acc['name'] ?? '') : (string) $acc;
                if ($name) {
                    $matchedPart = isset($allPartsByName[$name]) ? $allPartsByName[$name] : null;
                    $parts[] = [
                        'part_id'   => $matchedPart?->id ?? null,
                        'part_code' => $matchedPart?->code ?? '',
                        'part_name' => $name,
                        'quantity'  => is_array($acc) ? (int) ($acc['quantity'] ?? 1) : 1,
                        'unit'      => is_array($acc) ? ($acc['unit'] ?? 'Cái') : 'Cái',
                        'stock_qty' => $matchedPart ? (int) $matchedPart->stock_qty : null,
                        'in_stock'  => $matchedPart ? ($matchedPart->stock_qty > 0) : true,
                        'price'     => $matchedPart ? (float) $matchedPart->out_price : 0,
                        'note'      => 'Phụ kiện đi kèm máy',
                    ];
                }
            }
        }

        // 4. Mặc định tiêu chuẩn máy lọc nước: Bộ 3 lõi lọc thô 1, 2, 3
        if (empty($parts)) {
            $loi1 = $allPartsByCode['LOI-01-PP5M'] ?? null;
            $loi2 = $allPartsByCode['LOI-02-OCB'] ?? null;
            $loi3 = $allPartsByCode['LOI-03-CTO'] ?? null;

            $parts[] = [
                'part_id'   => $loi1?->id,
                'part_code' => $loi1?->code ?? 'LOI-01-PP5M',
                'part_name' => $loi1?->name ?? 'Lõi lọc số 1 PP 5 Micron (Lọc thô)',
                'quantity'  => 1,
                'unit'      => 'Lõi',
                'stock_qty' => $loi1?->stock_qty,
                'in_stock'  => $loi1 ? ($loi1->stock_qty > 0) : true,
                'price'     => $loi1?->out_price ?? 60000,
                'note'      => 'Định kỳ 3-6 tháng',
            ];
            $parts[] = [
                'part_id'   => $loi2?->id,
                'part_code' => $loi2?->code ?? 'LOI-02-OCB',
                'part_name' => $loi2?->name ?? 'Lõi lọc số 2 Than hoạt tính OCB/GAC',
                'quantity'  => 1,
                'unit'      => 'Lõi',
                'stock_qty' => $loi2?->stock_qty,
                'in_stock'  => $loi2 ? ($loi2->stock_qty > 0) : true,
                'price'     => $loi2?->out_price ?? 90000,
                'note'      => 'Định kỳ 6-9 tháng',
            ];
            $parts[] = [
                'part_id'   => $loi3?->id,
                'part_code' => $loi3?->code ?? 'LOI-03-CTO',
                'part_name' => $loi3?->name ?? 'Lõi lọc số 3 Than ép CTO 1 Micron',
                'quantity'  => 1,
                'unit'      => 'Lõi',
                'stock_qty' => $loi3?->stock_qty,
                'in_stock'  => $loi3 ? ($loi3->stock_qty > 0) : true,
                'price'     => $loi3?->out_price ?? 90000,
                'note'      => 'Định kỳ 9-12 tháng',
            ];

            // Nếu máy lắp trên 12 tháng, gợi ý thêm lõi T33 khoáng
            $installedMonths = $machine->installed_at ? $machine->installed_at->diffInMonths(now()) : 6;
            if ($installedMonths >= 12) {
                $loiT33 = $allPartsByCode['LOI-T33-GAC'] ?? null;
                $parts[] = [
                    'part_id'   => $loiT33?->id,
                    'part_code' => $loiT33?->code ?? 'LOI-T33-GAC',
                    'part_name' => $loiT33?->name ?? 'Lõi lọc T33 Than gáo dừa hoạt hóa',
                    'quantity'  => 1,
                    'unit'      => 'Lõi',
                    'stock_qty' => $loiT33?->stock_qty,
                    'in_stock'  => $loiT33 ? ($loiT33->stock_qty > 0) : true,
                    'price'     => $loiT33?->out_price ?? 150000,
                    'note'      => 'Khuyến nghị > 12 tháng',
                ];
            }
        }

        return $parts;
    }

    /**
     * Danh sách lịch bảo dưỡng & thay lõi định kỳ
     */
    public function getMaintenanceSchedule(Request $request)
    {
        $now = Carbon::today();
        $in7Days = (clone $now)->addDays(7);
        $in30Days = (clone $now)->addDays(30);
        $endOfMonth = (clone $now)->endOfMonth();

        // Tra cứu nhanh phụ tùng kho để gắn tồn kho thực tế
        $allParts = FilterPart::where('status', 'active')->get();
        $allPartsByCode = $allParts->keyBy('code');
        $allPartsByName = $allParts->keyBy('name');

        $query = FilterInstalledMachine::with(['modelCatalog', 'technician', 'installation.items'])
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->where('status', '!=', 'unactivated');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_address', 'like', "%{$search}%")
                  ->orWhere('machine_model', 'like', "%{$search}%")
                  ->orWhere('machine_serial', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        if ($areaFilter = $request->input('area')) {
            $query->where(function ($q) use ($areaFilter) {
                $q->where('customer_address', 'like', "%{$areaFilter}%")
                  ->orWhereHas('installation', function ($iq) use ($areaFilter) {
                      $iq->where('ward', 'like', "%{$areaFilter}%")
                         ->orWhere('district', 'like', "%{$areaFilter}%")
                         ->orWhere('province', 'like', "%{$areaFilter}%");
                  });
            });
        }

        $canAssign = self::canUserAssignTasks(Auth::user());
        $currentUserId = Auth::id();

        if ($techId = $request->input('technician_id')) {
            if ($techId === 'unclaimed') {
                $query->where(function ($q) {
                    $q->whereNull('technician_id')->orWhere('technician_id', 0);
                });
            } elseif ($techId === 'my_jobs') {
                $query->where('technician_id', $currentUserId);
            } else {
                $query->where('technician_id', $techId);
            }
        }

        $filterStatus = $request->input('filter_status', 'all');

        if ($filterStatus === 'overdue') {
            $query->whereNotNull('next_maintenance_date')->where('next_maintenance_date', '<', $now);
        } elseif ($filterStatus === 'due_7_days') {
            $query->whereNotNull('next_maintenance_date')->where('next_maintenance_date', '>=', $now)->where('next_maintenance_date', '<=', $in7Days);
        } elseif ($filterStatus === 'due_30_days') {
            $query->whereNotNull('next_maintenance_date')->where('next_maintenance_date', '>=', $now)->where('next_maintenance_date', '<=', $in30Days);
        } elseif ($filterStatus === 'this_month') {
            $query->whereNotNull('next_maintenance_date')->where('next_maintenance_date', '>=', $now)->where('next_maintenance_date', '<=', $endOfMonth);
        }

        $machines = $query->orderByRaw('CASE WHEN next_maintenance_date IS NULL THEN 2 ELSE 1 END')
                          ->orderBy('next_maintenance_date', 'asc')
                          ->latest('id')
                          ->paginate(30);

        $transformed = $machines->getCollection()->map(function ($m) use ($now, $in7Days, $in30Days, $allPartsByCode, $allPartsByName, $canAssign, $currentUserId) {
            $maintDate = $m->next_maintenance_date ? Carbon::parse($m->next_maintenance_date)->startOfDay() : null;
            $status = 'upcoming';
            $daysDiff = 0;

            if (!$maintDate) {
                $status = 'not_set';
            } elseif ($maintDate < $now) {
                $status = 'overdue';
                $daysDiff = (int) $now->diffInDays($maintDate, false);
            } elseif ($maintDate <= $in7Days) {
                $status = 'due_7_days';
                $daysDiff = (int) $now->diffInDays($maintDate);
            } elseif ($maintDate <= $in30Days) {
                $status = 'due_30_days';
                $daysDiff = (int) $now->diffInDays($maintDate);
            } else {
                $status = 'upcoming';
                $daysDiff = (int) $now->diffInDays($maintDate);
            }

            $defaultParts = $m->modelCatalog?->default_parts ?? [];
            $maintenanceParts = self::getMaintenancePartsForMachine($m, $allPartsByCode, $allPartsByName);
            $area = self::extractAreaFromAddress($m->customer_address, $m->installation?->ward, $m->installation?->district);

            return [
                'id'                        => $m->id,
                'customer_name'             => $m->customer_name ?: '(Chưa có tên)',
                'customer_phone'            => $m->customer_phone ?: '',
                'customer_address'          => $m->customer_address ?: '',
                'area'                      => $area,
                'machine_model'             => $m->machine_model ?: '(Chưa chọn model)',
                'machine_serial'            => $m->machine_serial ?: '',
                'qr_code'                   => $m->qr_code,
                'qr_token'                  => $m->qr_token,
                'qr_url'                    => $m->qr_url,
                'technician_id'             => $m->technician_id,
                'technician_name'           => $m->technician_name ?: ($m->technician?->name ?? 'Chưa phân công'),
                'is_unclaimed'              => empty($m->technician_id),
                'is_my_job'                 => ($m->technician_id == $currentUserId),
                'installed_at'              => $m->installed_at ? $m->installed_at->format('d/m/Y') : '-',
                'filter_replacement_months' => (int) ($m->filter_replacement_months ?: 6),
                'next_maintenance_date'     => $m->next_maintenance_date ? $m->next_maintenance_date->format('d/m/Y') : 'Chưa đặt lịch',
                'next_maintenance_raw'      => $m->next_maintenance_date ? $m->next_maintenance_date->format('Y-m-d') : '',
                'maintenance_status'        => $status,
                'days_diff'                 => $daysDiff,
                'warranty_until'            => $m->warranty_until ? $m->warranty_until->format('d/m/Y') : '-',
                'warranty_status'           => $m->warranty_status,
                'warranty_notes'            => $m->warranty_notes ?: '',
                'default_parts'             => $defaultParts,
                'maintenance_parts'         => $maintenanceParts,
            ];
        });

        $machines->setCollection($transformed);

        // Thống kê cơ bản
        $baseQ = FilterInstalledMachine::whereNotNull('customer_name')->where('customer_name', '!=', '')->where('status', '!=', 'unactivated');
        $overdueCount = (clone $baseQ)->whereNotNull('next_maintenance_date')->where('next_maintenance_date', '<', $now)->count();
        $due7Count = (clone $baseQ)->whereNotNull('next_maintenance_date')->where('next_maintenance_date', '>=', $now)->where('next_maintenance_date', '<=', $in7Days)->count();
        $due30Count = (clone $baseQ)->whereNotNull('next_maintenance_date')->where('next_maintenance_date', '>=', $now)->where('next_maintenance_date', '<=', $in30Days)->count();
        $unclaimedCount = (clone $baseQ)->where(function($q) {
            $q->whereNull('technician_id')->orWhere('technician_id', 0);
        })->count();
        $myJobsCount = $currentUserId ? (clone $baseQ)->where('technician_id', $currentUserId)->count() : 0;
        $totalCount = (clone $baseQ)->count();

        // TÍNH TOÁN GỢI Ý KHU VỰC TRONG VÒNG 1 THÁNG (Gồm đến hạn trong 30 ngày & quá hạn)
        $dueWithin30Machines = (clone $baseQ)
            ->with(['installation'])
            ->whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<=', $in30Days)
            ->get();

        $areaGroups = [];
        foreach ($dueWithin30Machines as $dm) {
            $maintDate = Carbon::parse($dm->next_maintenance_date)->startOfDay();
            $areaName = self::extractAreaFromAddress($dm->customer_address, $dm->installation?->ward, $dm->installation?->district);

            if (!isset($areaGroups[$areaName])) {
                $areaGroups[$areaName] = [
                    'area'          => $areaName,
                    'total'         => 0,
                    'overdue'       => 0,
                    'due_30_days'   => 0,
                    'customers'     => [],
                    'customer_ids'  => [],
                ];
            }

            $areaGroups[$areaName]['total']++;
            if ($maintDate < $now) {
                $areaGroups[$areaName]['overdue']++;
            } else {
                $areaGroups[$areaName]['due_30_days']++;
            }

            if (!in_array($dm->customer_name, $areaGroups[$areaName]['customers'])) {
                $areaGroups[$areaName]['customers'][] = $dm->customer_name;
            }
            $areaGroups[$areaName]['customer_ids'][] = $dm->id;
        }

        // Sắp xếp các khu vực có số lượng cần bảo dưỡng nhiều nhất lên đầu
        uasort($areaGroups, function ($a, $b) {
            if ($a['total'] === $b['total']) {
                return $b['overdue'] <=> $a['overdue'];
            }
            return $b['total'] <=> $a['total'];
        });

        $areaSuggestions = array_values($areaGroups);

        return response()->json([
            'success'          => true,
            'machines'         => $machines,
            'can_assign'       => $canAssign,
            'current_user_id'  => $currentUserId,
            'stats'            => [
                'total'       => $totalCount,
                'unclaimed'   => $unclaimedCount,
                'my_jobs'     => $myJobsCount,
                'overdue'     => $overdueCount,
                'due_7_days'  => $due7Count,
                'due_30_days' => $due30Count,
            ],
            'area_suggestions' => $areaSuggestions,
        ]);
    }

    /**
     * Dời / Đặt lịch bảo dưỡng
     */
    public function rescheduleMaintenance(Request $request, $id)
    {
        $machine = FilterInstalledMachine::findOrFail($id);
        $data = $request->validate([
            'next_maintenance_date'     => 'required|date',
            'technician_id'             => 'nullable|integer',
            'filter_replacement_months' => 'nullable|integer|min:1|max:36',
            'notes'                     => 'nullable|string',
        ]);

        $updates = [
            'next_maintenance_date' => $data['next_maintenance_date'],
        ];

        if (!empty($data['filter_replacement_months'])) {
            $updates['filter_replacement_months'] = (int) $data['filter_replacement_months'];
        }

        if (!empty($data['technician_id'])) {
            $tech = User::find($data['technician_id']);
            if ($tech) {
                $updates['technician_id'] = $tech->id;
                $updates['technician_name'] = $tech->name;
            }
        }

        if (!empty($data['notes'])) {
            $timestamp = now()->format('d/m/Y H:i');
            $user = Auth::user()?->name ?? 'Hệ thống';
            $log = "[{$timestamp} - {$user}]: Dời lịch hẹn bảo dưỡng sang ngày " . date('d/m/Y', strtotime($data['next_maintenance_date'])) . ". Ghi chú: {$data['notes']}";
            $updates['warranty_notes'] = ($machine->warranty_notes ? $machine->warranty_notes . "\n" : '') . $log;
        }

        $machine->update($updates);

        if ($machine->installation_id) {
            FilterInstallation::where('id', $machine->installation_id)->update([
                'next_maintenance_date' => $data['next_maintenance_date']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã dời ngày hẹn bảo dưỡng cho máy của KH {$machine->customer_name} thành công!",
            'machine' => $machine
        ]);
    }

    /**
     * Hoàn tất lần bảo dưỡng & tự động cộng kỳ hẹn tiếp theo, ghi nhận phụ tùng thay thế (tính phí hoặc miễn phí)
     */
    public function completeMaintenance(Request $request, $id)
    {
        $machine = FilterInstalledMachine::findOrFail($id);
        $data = $request->validate([
            'notes'                     => 'nullable|string',
            'parts_replaced'            => 'nullable|string',
            'filter_replacement_months' => 'nullable|integer|min:1|max:36',
            'next_maintenance_date'     => 'nullable|date',
            'items'                     => 'nullable|array',
            'items.*.part_id'           => 'nullable|integer',
            'items.*.part_code'         => 'nullable|string',
            'items.*.part_name'         => 'nullable|string',
            'items.*.unit'              => 'nullable|string',
            'items.*.quantity'          => 'nullable|integer|min:1',
            'items.*.unit_price'        => 'nullable|numeric|min:0',
            'items.*.is_free'           => 'nullable',
            'items.*.note'              => 'nullable|string',
            'labor_fee'                 => 'nullable|numeric|min:0',
            'discount'                  => 'nullable|numeric|min:0',
            'payment_status'            => 'nullable|string|in:paid,unpaid,free',
        ]);

        $rMonths = !empty($data['filter_replacement_months']) ? (int)$data['filter_replacement_months'] : ($machine->filter_replacement_months ?: 6);
        $nextDate = !empty($data['next_maintenance_date']) 
            ? date('Y-m-d', strtotime($data['next_maintenance_date']))
            : now()->addMonths($rMonths)->format('Y-m-d');

        $laborFee = (float)($data['labor_fee'] ?? 0);
        $discount = (float)($data['discount'] ?? 0);
        $items = $data['items'] ?? [];

        // Tính tiền phụ tùng
        $partsFee = 0;
        $itemsReplacedSummary = [];
        $sanitizedItems = [];

        foreach ($items as $it) {
            $name = trim($it['part_name'] ?? '');
            if (!$name) continue;

            $qty = max(1, (int)($it['quantity'] ?? 1));
            $isFree = filter_var($it['is_free'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $uPrice = $isFree ? 0 : (float)($it['unit_price'] ?? 0);
            $lineTotal = $isFree ? 0 : ($qty * $uPrice);
            $partsFee += $lineTotal;

            $feeText = $isFree ? 'Miễn phí' : number_format($lineTotal, 0, ',', '.') . 'đ';
            $itemsReplacedSummary[] = "{$name} (x{$qty} {$feeText})";

            $sanitizedItems[] = [
                'part_id'    => !empty($it['part_id']) ? (int)$it['part_id'] : null,
                'part_code'  => $it['part_code'] ?? null,
                'part_name'  => $name,
                'unit'       => $it['unit'] ?? 'Cái',
                'quantity'   => $qty,
                'unit_price' => $uPrice,
                'total_price'=> $lineTotal,
                'is_warranty'=> $isFree,
                'note'       => $it['note'] ?? null,
            ];
        }

        $totalAmount = max(0, $partsFee + $laborFee - $discount);
        $paymentStatus = $totalAmount == 0 ? 'free' : ($data['payment_status'] ?? 'paid');

        $partsSummaryString = !empty($itemsReplacedSummary) ? implode(', ', $itemsReplacedSummary) : (!empty($data['parts_replaced']) ? $data['parts_replaced'] : 'Không thay phụ tùng');
        $financialSummary = $totalAmount > 0 
            ? " | Thu phí: " . number_format($totalAmount, 0, ',', '.') . "đ ({$paymentStatus})"
            : " | Dịch vụ miễn phí (0đ)";

        $timestamp = now()->format('d/m/Y H:i');
        $user = Auth::user()?->name ?? 'Hệ thống';
        $log = "[{$timestamp} - {$user}]: Hoàn tất bảo dưỡng. Linh kiện thay thế: {$partsSummaryString}.{$financialSummary}. Hẹn kỳ tới (+{$rMonths} tháng): " . date('d/m/Y', strtotime($nextDate)) . (!empty($data['notes']) ? " | Ghi chú: {$data['notes']}" : '');

        // 1. Cập nhật linh kiện trong accessories của FilterInstalledMachine
        $existingAccessories = $machine->accessories_list;
        $updatedAccessories = $existingAccessories;

        foreach ($sanitizedItems as $sItem) {
            $found = false;
            foreach ($updatedAccessories as &$acc) {
                if (
                    (!empty($sItem['part_id']) && !empty($acc['part_id']) && $acc['part_id'] == $sItem['part_id']) ||
                    (mb_strtolower($acc['name'] ?? '') === mb_strtolower($sItem['part_name']))
                ) {
                    $acc['quantity'] = $sItem['quantity'];
                    $acc['installed_date'] = now()->format('Y-m-d');
                    $acc['is_free'] = $sItem['is_warranty'];
                    $acc['unit_price'] = $sItem['unit_price'];
                    $found = true;
                    break;
                }
            }
            unset($acc);

            if (!$found) {
                $updatedAccessories[] = [
                    'part_id'        => $sItem['part_id'],
                    'part_code'      => $sItem['part_code'],
                    'name'           => $sItem['part_name'],
                    'quantity'       => $sItem['quantity'],
                    'unit'           => $sItem['unit'],
                    'unit_price'     => $sItem['unit_price'],
                    'is_free'        => $sItem['is_warranty'],
                    'installed_date' => now()->format('Y-m-d'),
                    'maintenance_months' => $rMonths,
                ];
            }
        }

        // 2. Cập nhật FilterInstalledMachine
        $machine->update([
            'next_maintenance_date'     => $nextDate,
            'filter_replacement_months' => $rMonths,
            'accessories'               => $updatedAccessories,
            'warranty_notes'            => ($machine->warranty_notes ? $machine->warranty_notes . "\n" : '') . $log,
        ]);

        if ($machine->installation_id) {
            FilterInstallation::where('id', $machine->installation_id)->update([
                'next_maintenance_date' => $nextDate,
            ]);
        }

        // 3. Tạo phiếu FilterRepair và các FilterRepairItem
        $repairCode = 'BD-' . date('Ymd') . '-' . sprintf('%03d', FilterRepair::whereDate('created_at', today())->count() + 1);
        $repair = FilterRepair::create([
            'code'             => $repairCode,
            'customer_name'    => $machine->customer_name,
            'customer_phone'   => $machine->customer_phone,
            'customer_address' => $machine->customer_address,
            'repair_type'      => 'at_home',
            'warranty_type'    => ($totalAmount > 0) ? 'out_warranty' : 'in_warranty',
            'machine_model'    => $machine->machine_model,
            'machine_serial'   => $machine->machine_serial,
            'technician_id'    => $machine->technician_id ?: Auth::id(),
            'technician_name'  => $machine->technician_name ?: Auth::user()?->name,
            'symptom'          => "Bảo dưỡng & thay lõi lọc định kỳ (+{$rMonths} tháng)",
            'solution'         => !empty($data['notes']) ? $data['notes'] : "Đã kiểm tra chất lượng máy và thay thế linh kiện: {$partsSummaryString}",
            'status'           => 'completed',
            'completed_at'     => now(),
            'parts_fee'        => $partsFee,
            'labor_fee'        => $laborFee,
            'discount'         => $discount,
            'total_amount'     => $totalAmount,
            'payment_status'   => $paymentStatus,
            'notes'            => $log,
            'created_by'       => Auth::id(),
        ]);

        // Tạo FilterRepairItem & Trừ kho phụ tùng
        foreach ($sanitizedItems as $sItem) {
            $repair->items()->create([
                'part_id'     => $sItem['part_id'],
                'part_code'   => $sItem['part_code'],
                'part_name'   => $sItem['part_name'],
                'unit'        => $sItem['unit'],
                'quantity'    => $sItem['quantity'],
                'unit_price'  => $sItem['unit_price'],
                'total_price' => $sItem['total_price'],
                'is_warranty' => $sItem['is_warranty'],
                'note'        => $sItem['note'],
            ]);

            // Trừ kho nếu có part_id
            if (!empty($sItem['part_id'])) {
                $part = FilterPart::lockForUpdate()->find($sItem['part_id']);
                if ($part) {
                    $oldStock = $part->stock_qty;
                    $newStock = max(0, $oldStock - $sItem['quantity']);
                    $part->stock_qty = $newStock;
                    $part->save();

                    FilterPartTransaction::create([
                        'part_id'        => $part->id,
                        'type'           => 'export_repair',
                        'quantity'       => -$sItem['quantity'],
                        'balance_before' => $oldStock,
                        'balance_after'  => $newStock,
                        'unit_price'     => $sItem['unit_price'],
                        'total_price'    => $sItem['total_price'],
                        'reference_code' => $repair->code,
                        'note'           => "Xuất phụ tùng bảo dưỡng cho khách {$machine->customer_name}" . ($sItem['is_warranty'] ? ' (Miễn phí / Bảo hành)' : ' (Thu tiền)'),
                        'created_by'     => Auth::id(),
                        'created_by_name'=> Auth::user()?->name ?? 'Admin',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Đã ghi nhận bảo dưỡng hoàn tất cho KH {$machine->customer_name}!" . ($totalAmount > 0 ? " Tổng thu: " . number_format($totalAmount, 0, ',', '.') . "đ." : " (Miễn phí).") . " Lịch hẹn tiếp theo: " . date('d/m/Y', strtotime($nextDate)),
            'machine' => $machine,
            'repair'  => $repair->load('items'),
        ]);
    }

    /**
     * Kỹ thuật viên tự nhận đơn bảo dưỡng
     */
    public function claimMaintenance(Request $request, $id)
    {
        $machine = FilterInstalledMachine::findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để nhận việc!'], 401);
        }

        $canAssign = self::canUserAssignTasks($user);
        if ($machine->technician_id && $machine->technician_id != $user->id && !$canAssign) {
            return response()->json([
                'success' => false, 
                'message' => "Đơn này đã được KTV {$machine->technician_name} nhận trước đó!"
            ], 400);
        }

        $timestamp = now()->format('d/m/Y H:i');
        $log = "[{$timestamp}]: KTV {$user->name} đã tự nhận phụ trách đơn bảo dưỡng này.";

        $machine->update([
            'technician_id'   => $user->id,
            'technician_name' => $user->name,
            'warranty_notes'  => ($machine->warranty_notes ? $machine->warranty_notes . "\n" : '') . $log,
        ]);

        return response()->json([
            'success'         => true,
            'message'         => "Bạn đã nhận thành công đơn bảo dưỡng máy cho khách hàng {$machine->customer_name}!",
            'technician_id'   => $user->id,
            'technician_name' => $user->name,
            'machine'         => $machine,
        ]);
    }

    /**
     * Phân công KTV phụ trách bảo dưỡng (Dành cho người có quyền phân công)
     */
    public function assignMaintenance(Request $request, $id)
    {
        $machine = FilterInstalledMachine::findOrFail($id);
        $user = Auth::user();

        if (!self::canUserAssignTasks($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền phân công KTV cho đơn bảo dưỡng này!'
            ], 403);
        }

        $techId = $request->input('technician_id');
        $timestamp = now()->format('d/m/Y H:i');

        if (!empty($techId)) {
            $tech = User::findOrFail($techId);
            $log = "[{$timestamp} - {$user->name}]: Đã phân công KTV {$tech->name} phụ trách bảo dưỡng.";
            $machine->update([
                'technician_id'   => $tech->id,
                'technician_name' => $tech->name,
                'warranty_notes'  => ($machine->warranty_notes ? $machine->warranty_notes . "\n" : '') . $log,
            ]);
            $message = "Đã phân công thành công cho KTV {$tech->name}!";
        } else {
            $log = "[{$timestamp} - {$user->name}]: Hủy phân công, chuyển về đơn chưa ai nhận.";
            $machine->update([
                'technician_id'   => null,
                'technician_name' => null,
                'warranty_notes'  => ($machine->warranty_notes ? $machine->warranty_notes . "\n" : '') . $log,
            ]);
            $message = "Đã chuyển đơn về trạng thái Chưa phân công!";
        }

        return response()->json([
            'success'         => true,
            'message'         => $message,
            'technician_id'   => $machine->technician_id,
            'technician_name' => $machine->technician_name ?: 'Chưa phân công',
            'machine'         => $machine,
        ]);
    }

    /**
     * Hủy tự nhận việc (KTV nhả lại đơn về Chưa phân công)
     */
    public function unclaimMaintenance(Request $request, $id)
    {
        $machine = FilterInstalledMachine::findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập!'], 401);
        }

        $canAssign = self::canUserAssignTasks($user);
        if ($machine->technician_id != $user->id && !$canAssign) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể hủy đơn do kỹ thuật viên khác đang phụ trách!'
            ], 403);
        }

        $timestamp = now()->format('d/m/Y H:i');
        $log = "[{$timestamp}]: KTV {$user->name} đã hủy nhận đơn bảo dưỡng, chuyển về chưa ai nhận.";

        $machine->update([
            'technician_id'   => null,
            'technician_name' => null,
            'warranty_notes'  => ($machine->warranty_notes ? $machine->warranty_notes . "\n" : '') . $log,
        ]);

        return response()->json([
            'success'         => true,
            'message'         => "Đã hủy nhận việc thành công. Đơn hiện đang chờ người khác nhận!",
            'technician_id'   => null,
            'technician_name' => 'Chưa phân công',
            'machine'         => $machine,
        ]);
    }

    /**
     * Kỹ thuật viên tự tạo phiếu lấy phụ tùng từ kho để đi lắp đặt, bảo dưỡng, sửa chữa
     */
    public function exportPartsForTechnician(Request $request)
    {
        $data = $request->validate([
            'purpose'      => 'required|string|in:install,maintenance,repair,general',
            'target_name'  => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:1000',
            'items'        => 'required|array|min:1',
            'items.*.part_id'  => 'required|exists:filter_parts,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.note'     => 'nullable|string|max:255',
        ]);

        $purposeMap = [
            'install'     => 'Đi Lắp Đặt Máy Lọc Mới',
            'maintenance' => 'Đi Bảo Dưỡng & Thay Lõi Định Kỳ',
            'repair'      => 'Đi Sửa Chữa Máy Sự Cố / Hỏng',
            'general'     => 'Bổ Sung Túi Đồ Nghề / Vật Tư Dự Phòng',
        ];

        $purposeText = $purposeMap[$data['purpose']] ?? 'KTV Lấy Hàng Đi Làm';
        $targetInfo = !empty($data['target_name']) ? "Mục tiêu: {$data['target_name']}" : '';
        $user = Auth::user();
        $techName = $user?->name ?? 'KTV';
        $refCode = 'XK-KTV-' . date('ymd') . '-' . sprintf('%03d', FilterPartTransaction::whereDate('created_at', today())->where('type', 'like', 'export_tech%')->count() + 1);

        try {
            return DB::transaction(function () use ($data, $purposeText, $targetInfo, $refCode, $user, $techName) {
                $exportedItems = [];

                foreach ($data['items'] as $item) {
                    $qty = (int)$item['quantity'];
                    if ($qty <= 0) continue;

                    $part = FilterPart::lockForUpdate()->find($item['part_id']);
                    if (!$part) {
                        throw new \Exception("Không tìm thấy phụ tùng ID #{$item['part_id']}!");
                    }

                    if ($part->stock_qty < $qty) {
                        throw new \Exception("Linh kiện [{$part->name}] hiện chỉ còn tồn {$part->stock_qty} {$part->unit} trong kho, không đủ xuất {$qty} {$part->unit}!");
                    }

                    $oldStock = $part->stock_qty;
                    $newStock = $oldStock - $qty;
                    $part->stock_qty = $newStock;
                    $part->save();

                    $unitPrice = (float)($part->out_price ?: $part->in_price ?: 0);
                    $totalPrice = $unitPrice * $qty;

                    $itemNoteParts = [];
                    $itemNoteParts[] = "[{$purposeText}]";
                    if ($targetInfo) $itemNoteParts[] = $targetInfo;
                    if (!empty($item['note'])) $itemNoteParts[] = $item['note'];
                    if (!empty($data['notes'])) $itemNoteParts[] = "Ghi chú: " . $data['notes'];
                    $fullNote = implode(' | ', $itemNoteParts);

                    FilterPartTransaction::create([
                        'part_id'        => $part->id,
                        'type'           => 'export_tech_' . $data['purpose'],
                        'quantity'       => -$qty,
                        'balance_before' => $oldStock,
                        'balance_after'  => $newStock,
                        'unit_price'     => $unitPrice,
                        'total_price'    => $totalPrice,
                        'reference_code' => $refCode,
                        'note'           => $fullNote,
                        'created_by'     => $user?->id,
                        'created_by_name'=> $techName,
                    ]);

                    $exportedItems[] = [
                        'part_name'  => $part->name,
                        'part_code'  => $part->code,
                        'quantity'   => $qty,
                        'unit'       => $part->unit,
                        'unit_price' => $unitPrice,
                        'remaining'  => $newStock,
                    ];
                }

                return response()->json([
                    'success'        => true,
                    'message'        => "Đã xuất kho thành công phiếu [{$refCode}] cho KTV {$techName}! Tổng cộng: " . count($exportedItems) . " loại linh kiện.",
                    'reference_code' => $refCode,
                    'items'          => $exportedItems,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Kỹ thuật viên hoàn trả linh kiện thừa về kho
     */
    public function returnTechPartsToWarehouse(Request $request)
    {
        $data = $request->validate([
            'part_id'                 => 'required|exists:filter_parts,id',
            'quantity'                => 'required|integer|min:1',
            'original_reference_code' => 'nullable|string|max:50',
            'note'                    => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $techName = $user?->name ?? 'KTV';
        $refCode = 'NK-HOAN-' . date('ymd') . '-' . rand(100, 999);

        try {
            return DB::transaction(function () use ($data, $user, $techName, $refCode) {
                $part = FilterPart::lockForUpdate()->findOrFail($data['part_id']);
                $qty = (int)$data['quantity'];
                $oldStock = $part->stock_qty;
                $newStock = $oldStock + $qty;
                $part->stock_qty = $newStock;
                $part->save();

                $unitPrice = (float)($part->out_price ?: $part->in_price ?: 0);
                $totalPrice = $unitPrice * $qty;

                $origRef = !empty($data['original_reference_code']) ? "Từ phiếu xuất {$data['original_reference_code']}" : "Linh kiện mang đi làm còn dư";
                $note = "[KTV Hoàn kho: {$techName}] {$origRef}" . (!empty($data['note']) ? " | Ghi chú: {$data['note']}" : '');

                $tx = FilterPartTransaction::create([
                    'part_id'        => $part->id,
                    'type'           => 'return_tech_part',
                    'quantity'       => +$qty,
                    'balance_before' => $oldStock,
                    'balance_after'  => $newStock,
                    'unit_price'     => $unitPrice,
                    'total_price'    => $totalPrice,
                    'reference_code' => $refCode,
                    'note'           => $note,
                    'created_by'     => $user?->id,
                    'created_by_name'=> $techName,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Đã hoàn trả {$qty} {$part->unit} [{$part->name}] về kho thành công! Tồn kho hiện tại: {$newStock}.",
                    'transaction' => $tx,
                    'new_stock'   => $newStock,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
