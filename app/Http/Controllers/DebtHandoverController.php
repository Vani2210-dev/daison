<?php

namespace App\Http\Controllers;

use App\Models\DebtHandover;
use App\Models\VanSaleDispatch;
use App\Models\VanSaleDispatchItem;
use App\Models\User;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\SystemNotification;
use App\Models\Transaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DebtHandoverController extends Controller
{
    /**
     * API: Lấy danh sách các biên bản bàn giao công nợ
     */
    public function getDebtHandovers(Request $request)
    {
        $query = DebtHandover::with(['order', 'fromUser', 'toUser', 'targetDealer', 'confirmedByUser', 'accountant'])
            ->latest('id');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($fromUserId = $request->input('from_user_id')) {
            $query->where('from_user_id', $fromUserId);
        }
        if ($toUserId = $request->input('to_user_id')) {
            $query->where('to_user_id', $toUserId);
        }
        if ($accountantId = $request->input('accountant_id')) {
            $query->where('accountant_id', $accountantId);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('order_code', 'like', "%{$search}%")
                  ->orWhere('from_user_name', 'like', "%{$search}%")
                  ->orWhere('to_user_name', 'like', "%{$search}%")
                  ->orWhere('accountant_name', 'like', "%{$search}%")
                  ->orWhere('target_dealer_name', 'like', "%{$search}%");
            });
        }

        $handovers = $query->paginate(20)->withQueryString();

        return response()->json([
            'success'   => true,
            'handovers' => $handovers,
        ]);
    }

    /**
     * Xử lý file chứng từ đính kèm (multipart file hoặc base64 string)
     */
    protected function handleDocumentUploads(Request $request): array
    {
        $savedPaths = [];
        $uploadDir = public_path('uploads/debt_handovers');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        // 1. Xử lý file tải lên multipart (documents[] hoặc files[])
        $files = $request->file('documents') ?: ($request->file('files') ?: []);
        if ($files && !is_array($files)) {
            $files = [$files];
        }
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = 'doc_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $file->move($uploadDir, $filename);
                $savedPaths[] = '/uploads/debt_handovers/' . $filename;
            }
        }

        // 2. Xử lý chuỗi Base64 hoặc đường dẫn đã có (JSON payload)
        $raw = json_decode($request->getContent(), true) ?: [];
        $docs = $request->input('documents') ?? ($raw['documents'] ?? []);
        if (is_string($docs)) {
            $decoded = json_decode($docs, true);
            $docs = is_array($decoded) ? $decoded : [$docs];
        }
        if (is_array($docs)) {
            foreach ($docs as $doc) {
                if (is_string($doc)) {
                    if (str_starts_with($doc, 'data:image/')) {
                        $parts = explode(',', $doc, 2);
                        if (count($parts) === 2) {
                            $meta = $parts[0];
                            $data = base64_decode($parts[1]);
                            $ext = 'jpg';
                            if (str_contains($meta, 'image/png')) $ext = 'png';
                            elseif (str_contains($meta, 'image/webp')) $ext = 'webp';
                            elseif (str_contains($meta, 'image/gif')) $ext = 'gif';

                            $filename = 'doc_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                            file_put_contents($uploadDir . DIRECTORY_SEPARATOR . $filename, $data);
                            $savedPaths[] = '/uploads/debt_handovers/' . $filename;
                        }
                    } elseif (str_starts_with($doc, '/uploads/') || str_starts_with($doc, 'http')) {
                        $savedPaths[] = $doc;
                    }
                }
            }
        }

        return array_values(array_unique($savedPaths));
    }

    /**
     * API: Tạo yêu cầu / biên bản bàn giao công nợ (Bước 1: Tài xế gửi kèm chứng từ cho Kế toán)
     */
    public function createDebtHandover(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $rawOrderIds = $data['order_ids'] ?? ($data['orderId'] ?? ($data['order_id'] ?? []));
        if (!is_array($rawOrderIds)) {
            $rawOrderIds = array_filter(array_map('trim', explode(',', (string)$rawOrderIds)));
        }
        $orderCode = trim((string)($data['order_code'] ?? ($data['orderCode'] ?? '')));
        $accountantId = $data['accountant_id'] ?? ($data['accountantId'] ?? null);
        $toUserId = $data['to_user_id'] ?? ($data['toUserId'] ?? null);
        $targetDealerId = $data['target_dealer_id'] ?? ($data['targetDealerId'] ?? null);
        $amount = (float)($data['amount'] ?? 0);
        $notes = $data['notes'] ?? '';

        // Xử lý tài liệu chứng từ đính kèm
        $documents = $this->handleDocumentUploads($request);

        $orders = collect([]);
        if (!empty($rawOrderIds) || $orderCode) {
            $orders = Order::where(function ($q) use ($rawOrderIds, $orderCode) {
                if (!empty($rawOrderIds)) {
                    $q->whereIn('id', $rawOrderIds)->orWhereIn('order_code', $rawOrderIds);
                }
                if ($orderCode) {
                    $codes = array_filter(array_map('trim', explode(',', $orderCode)));
                    $q->orWhereIn('order_code', $codes);
                }
            })->get();
        }

        $collectedOrderIds = $orders->pluck('id')->values()->toArray();
        $collectedOrderCodes = $orders->pluck('order_code')->values()->join(', ');
        $firstOrder = $orders->first();

        // Kiểm tra: Đơn nào đã bàn giao rồi và chưa bị từ chối thì KHÔNG được bàn giao lại
        if (!empty($collectedOrderIds)) {
            $existingActive = DebtHandover::whereIn('status', ['pending', 'pending_accountant', 'pending_sale', 'confirmed'])
                ->where(function ($q) use ($collectedOrderIds) {
                    $q->whereIn('order_id', $collectedOrderIds);
                    foreach ($collectedOrderIds as $oid) {
                        $q->orWhereJsonContains('order_ids', (int)$oid)
                          ->orWhereJsonContains('order_ids', (string)$oid);
                    }
                })
                ->first();

            if ($existingActive) {
                return response()->json([
                    'success' => false,
                    'message' => "Đơn hàng đã được lập biên bản bàn giao nợ [{$existingActive->code}] (Trạng thái: {$existingActive->status}). Đơn đã bàn giao không thể bàn giao lại trừ khi bị từ chối!"
                ], 422);
            }
        }

        if ($orders->isNotEmpty() && $amount <= 0) {
            $amount = (float)$orders->sum(function ($o) {
                return $o->driver_debt_amount > 0 ? $o->driver_debt_amount : $o->total_amount;
            });
        }

        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Số tiền công nợ bàn giao phải lớn hơn 0'], 422);
        }

        // Xác định người bàn giao (thường là Lái xe đang đăng nhập hoặc người phụ trách đơn)
        $fromUser = auth()->user();
        $fromUserId = $fromUser?->id;
        $fromUserName = $fromUser?->name ?? 'Người vận chuyển';
        $driverId = $orders->pluck('assigned_driver_id')->filter()->first();
        if ($driverId && $fromUserId != $driverId && !$fromUser?->hasRole('admin')) {
            $driverUser = User::find($driverId);
            if ($driverUser) {
                $fromUserId = $driverUser->id;
                $fromUserName = $driverUser->name;
            }
        }

        // Xác định Kế toán nhận kiểm tra chứng từ (Bước 1)
        $accountant = null;
        $accountantName = null;
        if ($accountantId) {
            $accountant = User::find($accountantId);
            $accountantName = $accountant?->name;
        }

        // Xác định Sale nhận nợ (dự kiến hoặc chỉ định)
        $toUser = null;
        $toUserName = null;
        $toStaffType = 'company_sales';
        if ($toUserId) {
            $toUser = User::with('dealer')->find($toUserId);
            if ($toUser) {
                $toUserName = $toUser->name;
                $toStaffType = $toUser->staff_type ?? 'company_sales';
                if ($toUser->staff_type === 'dealer_sales' && $toUser->dealer_id) {
                    $targetDealerId = $toUser->dealer_id;
                }
            }
        }

        // Xác định Đại lý đích
        $targetDealer = null;
        if ($targetDealerId) {
            $targetDealer = Dealer::find($targetDealerId);
        } elseif ($firstOrder && $firstOrder->dealer_id) {
            $targetDealer = Dealer::find($firstOrder->dealer_id);
            $targetDealerId = $targetDealer?->id;
        }

        // Trạng thái ban đầu:
        // Nếu có Kế toán phụ trách -> 'pending_accountant' (Chờ Kế toán duyệt chứng từ)
        // Nếu chỉ có Sale trực tiếp -> 'pending_sale'
        $initialStatus = $accountant ? 'pending_accountant' : ($toUser ? 'pending_sale' : 'pending_accountant');

        $code = 'BGCN-' . date('Ymd') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $handover = DebtHandover::create([
                'code'               => $code,
                'order_id'           => $firstOrder?->id,
                'order_ids'          => !empty($collectedOrderIds) ? $collectedOrderIds : null,
                'order_code'         => $collectedOrderCodes ?: ($orderCode ?: null),
                'from_user_id'       => $fromUserId,
                'from_user_name'     => $fromUserName,
                'accountant_id'      => $accountant?->id,
                'accountant_name'    => $accountantName,
                'to_user_id'         => $toUser?->id,
                'to_user_name'       => $toUserName ?? 'Chờ Kế toán chỉ định',
                'to_staff_type'      => $toStaffType,
                'target_dealer_id'   => $targetDealerId,
                'target_dealer_name' => $targetDealer?->name,
                'amount'             => $amount,
                'handover_type'      => $orders->isNotEmpty() ? 'order_debt' : 'manual',
                'status'             => $initialStatus,
                'notes'              => $notes,
                'documents'          => !empty($documents) ? $documents : null,
                'created_by_id'      => auth()->id(),
            ]);

            // Cập nhật trạng thái từng đơn hàng
            foreach ($orders as $ord) {
                $ordDebt = (float)($ord->driver_debt_amount > 0 ? $ord->driver_debt_amount : $ord->total_amount);
                $ord->save();
                $ord->logOperation(
                    'Bàn Giao Nợ Chờ Kế Toán Duyệt',
                    "{$fromUserName} đã gửi biên bản {$code} kèm " . count($documents) . " chứng từ cho Kế toán [" . ($accountantName ?: 'Kế toán') . "] kiểm tra trước khi chuyển giao cho Sale [" . ($toUserName ?: 'NVKD') . "].",
                    $fromUserName
                );
            }

            // Ghi Audit Log
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $fromUserName,
                'user_id'              => auth()->id(),
                'action'               => 'Lập Biên Bản Bàn Giao Nợ (Bước 1)',
                'module'               => 'Công Nợ & Bàn Giao',
                'target_document_code' => $code,
                'field_name'           => 'Biên bản bàn giao nợ',
                'old_value'            => 'Lái xe chịu trách nhiệm',
                'new_value'            => ($initialStatus === 'pending_accountant' ? 'Chờ Kế toán duyệt chứng từ' : 'Chờ Sale xác nhận'),
                'notes'                => "{$fromUserName} gửi biên bản nợ " . number_format($amount, 0, ',', '.') . " đ kèm " . count($documents) . " chứng từ cho Kế toán [{$accountantName}].",
            ]);

            // Gửi thông báo hệ thống trực tiếp đến Kế toán hoặc người nhận được chọn
            if ($accountant) {
                try {
                    SystemNotification::send(
                        (int)$accountant->id,
                        "📑 Yêu cầu duyệt nợ từ Lái xe: {$code}",
                        "Tài xế {$fromUserName} vừa gửi biên bản bàn giao nợ {$code} (" . number_format($amount, 0, ',', '.') . " đ) kèm " . count($documents) . " chứng từ đối soát. Bấm để kiểm tra & chuyển cho Sale.",
                        '/staff-debt',
                        'debt_handover_pending_accountant',
                        $handover,
                        [
                            'handover_code'   => $code,
                            'amount'          => $amount,
                            'from_user_name'  => $fromUserName,
                            'documents_count' => count($documents),
                            'order_code'      => $collectedOrderCodes ?: $orderCode,
                        ]
                    );
                } catch (\Throwable $e) {}

                Announcement::create([
                    'title'             => "📑 Yêu cầu duyệt chứng từ bàn giao nợ: {$code} - " . number_format($amount, 0, ',', '.') . " đ",
                    'content'           => "{$fromUserName} vừa gửi biên bản bàn giao công nợ số {$code} trị giá " . number_format($amount, 0, ',', '.') . " đ kèm " . count($documents) . " chứng từ ảnh phiếu ký nhận.\nVui lòng vào mục Quản lý Công nợ để kiểm tra tính hợp lệ và chuyển tiếp cho Sale phụ trách.",
                    'author'            => $fromUserName,
                    'author_role'       => 'Người vận chuyển',
                    'priority'          => 'Khẩn cấp',
                    'target_department' => 'Kế toán',
                    'read_by'           => [],
                ]);
            } elseif ($toUser) {
                try {
                    SystemNotification::send(
                        (int)$toUser->id,
                        "📑 Yêu cầu nhận bàn giao công nợ: {$code}",
                        "Tài xế {$fromUserName} vừa gửi biên bản bàn giao nợ {$code} (" . number_format($amount, 0, ',', '.') . " đ). Bấm để xác nhận nhận nợ.",
                        '/staff-debt',
                        'debt_handover_pending_sale',
                        $handover,
                        [
                            'handover_code'  => $code,
                            'amount'         => $amount,
                            'from_user_name' => $fromUserName,
                            'order_code'     => $collectedOrderCodes ?: $orderCode,
                        ]
                    );
                } catch (\Throwable $e) {}

                Announcement::create([
                    'title'             => "📑 Yêu cầu nhận bàn giao công nợ: {$code} - " . number_format($amount, 0, ',', '.') . " đ",
                    'content'           => "{$fromUserName} vừa gửi biên bản bàn giao công nợ số {$code} trị giá " . number_format($amount, 0, ',', '.') . " đ kèm chứng từ cho bạn.\nVui lòng vào mục Quản lý Công nợ để bấm 'Xác nhận nhận nợ'.",
                    'author'            => $fromUserName,
                    'author_role'       => 'Người vận chuyển',
                    'priority'          => 'Quan trọng',
                    'target_department' => 'Kinh doanh',
                    'read_by'           => [],
                ]);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Đã gửi bàn giao nợ {$code} thành công! Kế toán sẽ kiểm tra chứng từ và chuyển cho Sale.",
                'handover' => $handover,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi lập biên bản bàn giao: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Kế toán kiểm tra chứng từ & chuyển tiếp cho Sale (Bước 2: Kế toán duyệt)
     */
    public function accountantForward(Request $request, $id)
    {
        $handover = DebtHandover::where('id', $id)->orWhere('code', $id)->first();
        if (!$handover) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy biên bản bàn giao'], 404);
        }

        if (!in_array($handover->status, ['pending_accountant', 'pending'])) {
            return response()->json(['success' => false, 'message' => "Biên bản này không ở trạng thái Chờ Kế toán duyệt (hiện tại: {$handover->status})"], 422);
        }

        $raw = json_decode($request->getContent(), true) ?: [];
        $data = array_merge($request->all(), $raw);

        $toUserId = $data['to_user_id'] ?? ($data['toUserId'] ?? null);
        $accountantNotes = trim((string)($data['accountant_notes'] ?? ($data['accountantNotes'] ?? 'Đã kiểm tra chứng từ hợp lệ.')));

        if (!$toUserId) {
            $toUserId = $handover->to_user_id;
        }
        if (!$toUserId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn Nhân viên Kinh doanh (Sale) nhận nợ!'], 422);
        }

        $toUser = User::find($toUserId);
        if (!$toUser) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin Nhân viên Kinh doanh'], 404);
        }

        $accountant = auth()->user();
        $accountantName = $accountant?->name ?? 'Kế toán';

        $targetDealerId = ($toUser->staff_type === 'dealer_sales') ? $toUser->dealer_id : $handover->target_dealer_id;
        $targetDealerName = $targetDealerId ? Dealer::find($targetDealerId)?->name : $handover->target_dealer_name;

        DB::beginTransaction();
        try {
            $handover->update([
                'to_user_id'             => $toUser->id,
                'to_user_name'           => $toUser->name,
                'to_staff_type'          => $toUser->staff_type ?? 'company_sales',
                'target_dealer_id'       => $targetDealerId,
                'target_dealer_name'     => $targetDealerName,
                'accountant_id'          => $accountant?->id ?: $handover->accountant_id,
                'accountant_name'        => $accountantName,
                'accountant_notes'       => $accountantNotes,
                'accountant_verified_at' => now(),
                'status'                 => 'pending_sale',
            ]);

            // Cập nhật nhật ký đơn hàng
            $orderIds = $handover->order_ids ?: ($handover->order_id ? [$handover->order_id] : []);
            if (!empty($orderIds)) {
                $orders = Order::whereIn('id', $orderIds)->get();
                foreach ($orders as $ord) {
                    $ord->logOperation(
                        'Kế Toán Đã Duyệt Chứng Từ',
                        "Kế toán {$accountantName} đã duyệt chứng từ biên bản {$handover->code} và chuyển giao nợ cho Sale [{$toUser->name}]. Ghi chú: {$accountantNotes}",
                        $accountantName
                    );
                }
            }

            // Ghi AuditLog
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $accountantName,
                'user_id'              => auth()->id(),
                'action'               => 'Kế Toán Duyệt Chứng Từ & Chuyển Cho Sale',
                'module'               => 'Công Nợ & Bàn Giao',
                'target_document_code' => $handover->code,
                'field_name'           => 'Biên bản bàn giao nợ',
                'old_value'            => 'Chờ Kế toán duyệt chứng từ',
                'new_value'            => 'Chờ Sale xác nhận nhận nợ',
                'notes'                => "Kế toán {$accountantName} đã duyệt chứng từ nợ " . number_format($handover->amount, 0, ',', '.') . " đ và chuyển tiếp cho Sale [{$toUser->name}].",
            ]);

            // Gửi thông báo hệ thống trực tiếp đến Sale được chọn
            try {
                SystemNotification::send(
                    (int)$toUser->id,
                    "📑 Kế toán chuyển giao nhận nợ: {$handover->code}",
                    "Kế toán {$accountantName} đã duyệt chứng từ nợ {$handover->code} (" . number_format($handover->amount, 0, ',', '.') . " đ) từ lái xe {$handover->from_user_name} và chuyển cho bạn. Bấm để xác nhận nhận nợ.",
                    '/staff-debt',
                    'debt_handover_pending_sale',
                    $handover,
                    [
                        'handover_code'   => $handover->code,
                        'amount'          => (float)$handover->amount,
                        'accountant_name' => $accountantName,
                        'from_user_name'  => $handover->from_user_name,
                        'order_code'      => $handover->order_code,
                    ]
                );
            } catch (\Throwable $e) {}

            Announcement::create([
                'title'             => "📑 Kế toán đã duyệt chứng từ nợ: {$handover->code} - " . number_format($handover->amount, 0, ',', '.') . " đ",
                'content'           => "Kế toán {$accountantName} đã kiểm tra chứng từ hợp lệ từ lái xe {$handover->from_user_name} và chuyển giao nợ số {$handover->code} trị giá " . number_format($handover->amount, 0, ',', '.') . " đ cho bạn.\nVui lòng vào mục Quản lý Công nợ để bấm 'Xác nhận nhận nợ'.",
                'author'            => $accountantName,
                'author_role'       => 'Kế toán',
                'priority'          => 'Khẩn cấp',
                'target_department' => 'Kinh doanh',
                'read_by'           => [],
            ]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Đã duyệt chứng từ và chuyển giao công nợ {$handover->code} cho Sale [{$toUser->name}] thành công!",
                'handover' => $handover->fresh(['accountant', 'toUser', 'fromUser']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi khi Kế toán duyệt chứng từ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Xác nhận nhận nợ (Bước 3: Sale bấm 'Xác nhận nhận nợ')
     */
    public function confirmDebtHandover(Request $request, $id)
    {
        $handover = DebtHandover::where('id', $id)->orWhere('code', $id)->first();
        if (!$handover) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy biên bản bàn giao'], 404);
        }

        if (!in_array($handover->status, ['pending', 'pending_sale', 'pending_accountant'])) {
            return response()->json(['success' => false, 'message' => "Biên bản này đã ở trạng thái: {$handover->status}"], 422);
        }

        $user = auth()->user();
        $approverName = $user?->name ?? 'Người xác nhận';

        DB::beginTransaction();
        try {
            $amount = (float)$handover->amount;

            // 1. Giảm công nợ của Người bàn giao (Lái xe / Người vận chuyển)
            if ($handover->from_user_id) {
                $fromUser = User::find($handover->from_user_id);
                if ($fromUser) {
                    $fromUser->current_debt = max(0, (float)$fromUser->current_debt - $amount);
                    $fromUser->save();
                }
            }

            // 2. Chuyển công nợ sang bên nhận:
            $toUser = null;
            if ($handover->to_user_id) {
                $toUser = User::find($handover->to_user_id);
                if ($toUser) {
                    $toUser->current_debt = (float)$toUser->current_debt + $amount;
                    $toUser->save();
                }
            }

            // Ghi nhận công nợ vào Đại lý nếu thuộc đại lý
            $isDealerSales = ($handover->to_staff_type === 'dealer_sales') 
                || !empty($handover->target_dealer_id) 
                || ($toUser && (!empty($toUser->dealer_id) || $toUser->staff_type === 'dealer_sales'));

            if ($isDealerSales) {
                $dealerId = $handover->target_dealer_id ?: $toUser?->dealer_id;
                if ($dealerId) {
                    $dealer = Dealer::find($dealerId);
                    if ($dealer) {
                        $dealer->current_debt = (float)$dealer->current_debt + $amount;
                        $dealer->save();
                    }
                }
            }

            // 3. Cập nhật đơn hàng: chuyển quyền quản lý đơn & giải phóng trách nhiệm lái xe
            $orderIds = $handover->order_ids ?: ($handover->order_id ? [$handover->order_id] : []);
            if (!empty($orderIds)) {
                $orders = Order::whereIn('id', $orderIds)->get();
                foreach ($orders as $order) {
                    $order->driver_debt_amount = 0;
                    if ($toUser) {
                        $order->sales_rep_id = $toUser->id;
                    }
                    if ($handover->target_dealer_id) {
                        $order->dealer_id = $handover->target_dealer_id;
                    } elseif ($toUser && $toUser->dealer_id) {
                        $order->dealer_id = $toUser->dealer_id;
                    }
                    $order->notes = ($order->notes ? $order->notes . "\n" : '') .
                        "[ĐÃ XÁC NHẬN BÀN GIAO NỢ]: {$approverName} đã nhận bàn giao đơn hàng và công nợ " . number_format($order->total_amount, 0, ',', '.') . " đ theo biên bản {$handover->code}. Lái xe đã được giải phóng nợ.";
                    $order->save();

                    // Tự động tính toán lại công nợ đại lý
                    if ($order->dealer_id) {
                        $dl = Dealer::find($order->dealer_id) ?: Dealer::where('code', $order->dealer_id)->first();
                        if ($dl) {
                            $dl->recalculateCurrentDebt();
                        }
                    }
                }
            }

            // 4. Cập nhật biên bản bàn giao
            $handover->update([
                'status'            => 'confirmed',
                'confirmed_by'      => $user?->id,
                'confirmed_by_name' => $approverName,
                'confirmed_at'      => now(),
            ]);

            // 5. Ghi Audit Log
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => $approverName,
                'user_id'              => $user?->id,
                'action'               => 'Xác Nhận Nhận Bàn Giao Công Nợ (Bước 3)',
                'module'               => 'Công Nợ & Bàn Giao',
                'target_document_code' => $handover->code,
                'field_name'           => 'Biên bản bàn giao nợ',
                'old_value'            => 'Chờ xác nhận',
                'new_value'            => 'Đã xác nhận nhận nợ',
                'notes'                => "{$approverName} đã xác nhận nhận nợ " . number_format($amount, 0, ',', '.') . " đ từ [{$handover->from_user_name}]. Lái xe được giải phóng nợ.",
            ]);

            // 6. Gửi thông báo hệ thống trực tiếp đến Lái xe và Kế toán
            if ($handover->from_user_id) {
                try {
                    SystemNotification::send(
                        (int)$handover->from_user_id,
                        "✅ Đã giải phóng 100% công nợ: {$handover->code}",
                        "Sale {$approverName} đã xác nhận nhận công nợ {$handover->code} (" . number_format($amount, 0, ',', '.') . " đ). Bạn đã được giải phóng trách nhiệm công nợ.",
                        '/driver/portal',
                        'debt_handover_confirmed',
                        $handover,
                        [
                            'handover_code' => $handover->code,
                            'amount'        => $amount,
                            'approver_name' => $approverName,
                        ]
                    );
                } catch (\Throwable $e) {}
            }

            if ($handover->accountant_id && $handover->accountant_id != $handover->from_user_id) {
                try {
                    SystemNotification::send(
                        (int)$handover->accountant_id,
                        "✅ Sale đã nhận nợ hoàn tất: {$handover->code}",
                        "Sale {$approverName} đã xác nhận nhận nợ biên bản {$handover->code} (" . number_format($amount, 0, ',', '.') . " đ) do bạn duyệt chứng từ.",
                        '/staff-debt',
                        'debt_handover_confirmed',
                        $handover,
                        [
                            'handover_code' => $handover->code,
                            'amount'        => $amount,
                            'approver_name' => $approverName,
                        ]
                    );
                } catch (\Throwable $e) {}
            }

            Announcement::create([
                'title'             => "✅ Bàn giao nợ hoàn tất: {$handover->code} (" . number_format($amount, 0, ',', '.') . " đ)",
                'content'           => "{$approverName} đã xác nhận nhận công nợ đơn hàng ({$handover->code}) trị giá " . number_format($amount, 0, ',', '.') . " đ. Lái xe [{$handover->from_user_name}] đã được giải phóng 100% trách nhiệm công nợ.",
                'author'            => $approverName,
                'author_role'       => 'Kinh doanh',
                'priority'          => 'Bình thường',
                'target_department' => 'Vận tải',
                'read_by'           => [],
            ]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Đã xác nhận nhận công nợ {$handover->code} thành công! Công nợ đã được chuyển sang Sale và giải phóng lái xe.",
                'handover' => $handover,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi khi xác nhận bàn giao nợ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Từ chối nhận nợ (Kế toán hoặc Sale bấm 'Từ chối')
     */
    public function rejectDebtHandover(Request $request, $id)
    {
        $raw = json_decode($request->getContent(), true) ?: [];
        $reason = $request->input('reason') ?? ($raw['reason'] ?? 'Không khớp thông tin đơn hàng hoặc chứng từ không hợp lệ');

        $handover = DebtHandover::where('id', $id)->orWhere('code', $id)->first();
        if (!$handover) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy biên bản bàn giao'], 404);
        }

        if (!in_array($handover->status, ['pending', 'pending_accountant', 'pending_sale'])) {
            return response()->json(['success' => false, 'message' => "Biên bản này không thể từ chối (hiện tại: {$handover->status})"], 422);
        }

        $user = auth()->user();
        $rejecterName = $user?->name ?? 'Người từ chối';

        $handover->update([
            'status'          => 'rejected',
            'rejected_reason' => $reason,
            'rejected_at'     => now(),
        ]);

        // Cập nhật ghi chú đơn hàng
        $orderIds = $handover->order_ids ?: ($handover->order_id ? [$handover->order_id] : []);
        if (!empty($orderIds)) {
            $orders = Order::whereIn('id', $orderIds)->get();
            foreach ($orders as $order) {
                $order->notes = ($order->notes ? $order->notes . "\n" : '') .
                    "[TỪ CHỐI BÀN GIAO NỢ]: {$rejecterName} đã từ chối nhận nợ biên bản {$handover->code}. Lý do: {$reason}";
                $order->save();
            }
        }

        // Ghi Audit Log
        AuditLog::create([
            'timestamp'            => now()->format('Y-m-d H:i:s'),
            'performer_name'       => $rejecterName,
            'user_id'              => $user?->id,
            'action'               => 'Từ Chối Bàn Giao Công Nợ',
            'module'               => 'Công Nợ & Bàn Giao',
            'target_document_code' => $handover->code,
            'field_name'           => 'Biên bản bàn giao nợ',
            'old_value'            => 'Chờ xác nhận',
            'new_value'            => 'Đã từ chối',
            'notes'                => "{$rejecterName} từ chối biên bản nợ [{$handover->code}]. Lý do: {$reason}",
        ]);

        // Gửi thông báo hệ thống trực tiếp đến Lái xe
        if ($handover->from_user_id) {
            try {
                SystemNotification::send(
                    (int)$handover->from_user_id,
                    "❌ Từ chối biên bản bàn giao nợ: {$handover->code}",
                    "{$rejecterName} đã từ chối nhận nợ biên bản {$handover->code} (" . number_format($handover->amount, 0, ',', '.') . " đ). Lý do: {$reason}. Vui lòng kiểm tra lại chứng từ.",
                    '/driver/portal',
                    'debt_handover_rejected',
                    $handover,
                    [
                        'handover_code' => $handover->code,
                        'amount'        => (float)$handover->amount,
                        'rejecter_name' => $rejecterName,
                        'reason'        => $reason,
                    ]
                );
            } catch (\Throwable $e) {}
        }

        Announcement::create([
            'title'             => "❌ Từ chối biên bản bàn giao nợ: {$handover->code}",
            'content'           => "{$rejecterName} đã từ chối biên bản bàn giao nợ {$handover->code} trị giá " . number_format($handover->amount, 0, ',', '.') . " đ.\nLý do: {$reason}\nVui lòng kiểm tra lại chứng từ và lập lại biên bản.",
            'author'            => $rejecterName,
            'author_role'       => 'Hệ thống',
            'priority'          => 'Khẩn cấp',
            'target_department' => 'Vận tải',
            'read_by'           => [],
        ]);

        return response()->json([
            'success'  => true,
            'message'  => "Đã từ chối nhận bàn giao nợ {$handover->code}.",
            'handover' => $handover,
        ]);
    }

    /**
     * API: Lấy danh sách công nợ nhân viên & người vận chuyển
     */
    public function getStaffDebtList(Request $request)
    {
        $currentUser = auth()->user();
        $canViewAll = $currentUser && (
            $currentUser->hasRole('Manager') ||
            $currentUser->hasRole('Admin') ||
            $currentUser->hasPermission('staff_debt.view_all')
        );

        $query = User::with('dealer');
        if ($canViewAll) {
            $query->where(function ($q) {
                $q->whereIn('staff_type', ['driver', 'company_sales', 'dealer_sales'])
                  ->orWhere('department', 'LIKE', '%Kinh doanh%')
                  ->orWhere('department', 'LIKE', '%Vận tải%')
                  ->orWhere('current_debt', '>', 0);
            });
        } else {
            $query->where('id', $currentUser ? $currentUser->id : 0);
        }

        if ($type = $request->input('staff_type')) {
            $query->where('staff_type', $type);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderByDesc('current_debt')->paginate(20)->withQueryString();

        if ($canViewAll) {
            $totalStaffDebt = User::sum('current_debt');
            $driverDebt = User::where('staff_type', 'driver')->sum('current_debt');
            $salesDebt = User::where('staff_type', 'company_sales')->sum('current_debt');
            $dealerSalesDebt = User::where('staff_type', 'dealer_sales')->sum('current_debt');
        } else {
            $totalStaffDebt = (float)($currentUser?->current_debt ?? 0);
            $driverDebt = ($currentUser && $currentUser->staff_type === 'driver') ? (float)$currentUser->current_debt : 0;
            $salesDebt = ($currentUser && $currentUser->staff_type === 'company_sales') ? (float)$currentUser->current_debt : 0;
            $dealerSalesDebt = ($currentUser && $currentUser->staff_type === 'dealer_sales') ? (float)$currentUser->current_debt : 0;
        }

        return response()->json([
            'success'         => true,
            'staff'           => $staff,
            'totalStaffDebt'  => (float)$totalStaffDebt,
            'driverDebt'      => (float)$driverDebt,
            'salesDebt'       => (float)$salesDebt,
            'dealerSalesDebt' => (float)$dealerSalesDebt,
        ]);
    }

    /**
     * API: Lập Phiếu xuất hàng lẻ / lưu động lên xe cho tài xế đi bán dạo
     */
    public function createVanSaleDispatch(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $data = array_merge($request->all(), $raw);

        $driverId = $data['driver_id'] ?? ($data['driverId'] ?? null);
        $items = $data['items'] ?? [];

        if (!$driverId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn Nhân viên nhận hàng ôm'], 422);
        }
        if (empty($items) || !is_array($items)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 mặt hàng xuất giao ôm'], 422);
        }

        $driver = User::find($driverId);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin nhân viên'], 404);
        }

        $code = 'XOM-' . date('Ymd') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);
        $vehiclePlate = $data['vehicle_plate'] ?? ($data['vehiclePlate'] ?? ('NV: ' . $driver->name));
        $warehouseId = $data['warehouse_id'] ?? ($data['warehouseId'] ?? 1);
        $notes = $data['notes'] ?? 'Xuất hàng ôm cho nhân viên đi thị trường';

        // Tìm kho nguồn & kho hàng ôm nhân viên trong bảng warehouses
        $sourceWarehouse = Warehouse::find($warehouseId) ?: Warehouse::where('code', 'KHO-MQ-01')->first();
        $sourceWhId = $sourceWarehouse?->id ?: 1;
        $sourceWhName = $sourceWarehouse?->name ?: 'Kho Tổng Mê Linh';

        // Tìm hoặc tạo kho hàng ôm tương ứng trong bảng warehouses
        $targetWh = Warehouse::where('code', 'KHO-OM-' . $driver->id)->first();
        if (!$targetWh) {
            $targetWh = Warehouse::where('manager_name', $driver->name)->where('type', 'Kho hàng ôm')->first();
        }
        if (!$targetWh && !empty($vehiclePlate) && $vehiclePlate !== 'Xe bán lưu động') {
            $targetWh = Warehouse::where('code', 'KHO-XE-' . $vehiclePlate)->first();
        }
        if (!$targetWh) {
            $targetWh = Warehouse::create([
                'code'         => 'KHO-OM-' . $driver->id,
                'name'         => 'Kho Hàng Ôm - ' . $driver->name,
                'type'         => 'Kho hàng ôm',
                'manager_name' => $driver->name,
                'address'      => 'Hàng ôm nhân viên: ' . $driver->name . ($driver->phone ? ' (' . $driver->phone . ')' : ''),
                'is_active'    => true,
            ]);
        }
        $targetWhId = $targetWh?->id;

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $dispatch = VanSaleDispatch::create([
                'code'          => $code,
                'driver_id'     => $driver->id,
                'driver_name'   => $driver->name,
                'vehicle_plate' => $vehiclePlate,
                'warehouse_id'  => $sourceWhId,
                'warehouse_name'=> $sourceWhName,
                'dispatch_date' => now()->format('Y-m-d'),
                'total_amount'  => 0,
                'status'        => 'dispatched',
                'notes'         => $notes,
                'created_by_id' => auth()->id(),
            ]);

            // Dùng chung bảng inventory_transactions: Tạo phiếu Xuất Kho Hàng Ôm
            $invTx = \App\Models\InventoryTransaction::create([
                'code'                 => $code,
                'type'                 => 'export',
                'warehouse_id'         => $sourceWhId,
                'target_warehouse_id'  => $targetWhId,
                'created_by_id'        => auth()->id() ?: 1,
                'status'               => 'Hoàn thành',
                'supplier_name'        => 'Nhân viên ôm hàng: ' . $driver->name,
                'reference_order_code' => $code,
                'total_amount'         => 0,
                'notes'                => "[Xuất Hàng Ôm Nhân Viên] " . $notes,
            ]);

            foreach ($items as $it) {
                $prodId = $it['product_id'] ?? ($it['productId'] ?? null);
                $qty = (float)($it['quantity'] ?? ($it['qty'] ?? 1));
                $price = (float)($it['unit_price'] ?? ($it['unitPrice'] ?? 0));

                $prod = Product::find($prodId);
                if ($prod) {
                    if ($price <= 0) {
                        $price = (float)($prod->price ?: ($prod->getPriceForTier('Đại lý Cấp 1', 0) ?: $prod->cost_price));
                    }
                    $lineTotal = $qty * $price;
                    $totalAmount += $lineTotal;

                    VanSaleDispatchItem::create([
                        'van_sale_dispatch_id' => $dispatch->id,
                        'product_id'           => $prod->id,
                        'sku'                  => $prod->sku,
                        'name'                 => $prod->name,
                        'unit'                 => $prod->unit ?: 'cái',
                        'quantity'             => $qty,
                        'unit_price'           => $price,
                        'amount'               => $lineTotal,
                    ]);

                    // Dùng chung bảng inventory_transaction_items
                    \App\Models\InventoryTransactionItem::create([
                        'inventory_transaction_id' => $invTx->id,
                        'product_id'               => $prod->id,
                        'quantity'                 => $qty,
                        'import_price'             => $price,
                        'cost_price_at_transaction' => $price,
                        'notes'                    => "Xuất hàng ôm cho NV {$driver->name}",
                    ]);

                    // Trừ tồn kho tổng
                    $prod->decrement('stock_quantity', $qty);

                    // Trừ tồn kho tại kho xuất nguồn trong warehouse_stocks
                    $srcStock = \App\Models\WarehouseStock::firstOrCreate(
                        ['product_id' => $prod->id, 'warehouse_id' => $sourceWhId],
                        ['stock_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $srcStock->decrement('stock_quantity', $qty);

                    // CỘNG tồn kho vào Kho hàng ôm của nhân viên trong warehouse_stocks
                    if ($targetWhId) {
                        $empStock = \App\Models\WarehouseStock::firstOrCreate(
                            ['product_id' => $prod->id, 'warehouse_id' => $targetWhId],
                            ['stock_quantity' => 0, 'reserved_quantity' => 0, 'location' => 'Hàng ôm: ' . $driver->name]
                        );
                        $empStock->increment('stock_quantity', $qty);
                    }
                }
            }

            $dispatch->total_amount = $totalAmount;
            $dispatch->save();

            $invTx->total_amount = $totalAmount;
            $invTx->save();

            // Tự động cộng tổng giá trị hàng này vào công nợ tài xế chịu trách nhiệm
            $driver->current_debt = (float)$driver->current_debt + $totalAmount;
            $driver->save();

            // Ghi Audit Log
            AuditLog::create([
                'timestamp'            => now()->format('Y-m-d H:i:s'),
                'performer_name'       => auth()->user()?->name ?? 'Thủ kho',
                'user_id'              => auth()->id(),
                'action'               => 'Xuất Hàng Lẻ Lên Xe Lưu Động',
                'module'               => 'Kho & Vận Tải',
                'target_document_code' => $code,
                'field_name'           => 'Phiếu xuất hàng lẻ',
                'old_value'            => 'Tồn kho tổng',
                'new_value'            => 'Tồn kho xe & Công nợ tài xế',
                'notes'                => "Bốc hàng lẻ lên xe {$vehiclePlate} cho Lái xe [{$driver->name}]. Tổng giá trị: " . number_format($totalAmount, 0, ',', '.') . " đ (ghi nợ tài xế).",
            ]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Đã lập phiếu xuất hàng lẻ {$code} thành công! Đã tăng tồn kho xe {$vehiclePlate} và ghi nhận công nợ " . number_format($totalAmount, 0, ',', '.') . " đ cho tài xế {$driver->name}.",
                'dispatch' => $dispatch->load('items'),
                'tx_code'  => $invTx->code,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi xuất hàng lẻ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy danh sách phiếu xuất hàng lẻ trên xe
     */
    public function getVanSaleDispatches(Request $request)
    {
        $query = VanSaleDispatch::with(['items', 'driver'])->latest('id');

        if ($driverId = $request->input('driver_id')) {
            $query->where('driver_id', $driverId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $dispatches = $query->paginate(20)->withQueryString();

        return response()->json([
            'success'    => true,
            'dispatches' => $dispatches,
        ]);
    }
}