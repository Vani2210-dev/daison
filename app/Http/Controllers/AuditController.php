<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PeriodLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    /**
     * Bootstrap data for Audit Logs & Period Locks
     */
    public function getBootstrapData()
    {
        $auditLogs = AuditLog::latest('id')->get()->map(function ($log) {
            return [
                'id'                 => (string) $log->id,
                'timestamp'          => $log->timestamp ?: ($log->created_at ? $log->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s')),
                'performerName'      => $log->performer_name,
                'userId'             => (string) ($log->user_id ?? ''),
                'action'             => $log->action,
                'module'             => $log->module,
                'targetDocumentCode' => $log->target_document_code,
                'fieldName'          => $log->field_name,
                'oldValue'           => $log->old_value,
                'newValue'           => $log->new_value,
                'notes'              => $log->notes,
            ];
        });

        $periodLocks = PeriodLock::with('lockedBy')->latest('id')->get()->map(function ($lock) {
            return [
                'id'         => (string) $lock->id,
                'ledgerType' => $lock->ledger_type,
                'fromDate'   => $lock->from_date ? $lock->from_date->format('Y-m-d') : '',
                'toDate'     => $lock->to_date ? $lock->to_date->format('Y-m-d') : '',
                'status'     => $lock->status,
                'lockedById' => (string) ($lock->locked_by_id ?? ''),
                'lockedBy'   => $lock->lockedBy?->name ?? 'Ban Giám Đốc',
                'reason'     => $lock->reason,
            ];
        });

        return [
            'auditLogs'   => $auditLogs,
            'periodLocks' => $periodLocks,
        ];
    }

    /**
     * API: Get audit logs
     */
    public function getAuditLogs()
    {
        $data = $this->getBootstrapData();
        return response()->json(['auditLogs' => $data['auditLogs']]);
    }

    /**
     * API: Toggle period lock status
     */
    public function togglePeriodLock(Request $request)
    {
        $id = $request->input('id');
        $lock = PeriodLock::find($id);

        if (!$lock) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy kỳ kế toán'], 404);
        }

        $newStatus = $lock->status === 'locked' ? 'unlocked' : 'locked';
        $lock->status = $newStatus;
        if ($newStatus === 'locked') {
            $lock->locked_by_id = Auth::id() ?? 1;
        }
        $lock->save();

        // Record audit log
        AuditLog::create([
            'timestamp'            => date('Y-m-d H:i:s'),
            'performer_name'       => Auth::user()?->name ?? 'Ban Giám Đốc',
            'user_id'              => Auth::id() ?? 1,
            'action'               => $newStatus === 'locked' ? 'Khóa Sổ Kỳ' : 'Mở Khóa Kỳ',
            'module'               => 'Kế toán',
            'target_document_code' => "LOCK-{$lock->id}",
            'field_name'           => 'Khóa kỳ kế toán',
            'old_value'            => $lock->status === 'locked' ? 'Mở' : 'Đã khóa',
            'new_value'            => $newStatus === 'locked' ? 'Đã khóa hoàn toàn' : 'Mở Khởi Tạo',
            'notes'                => "Chuyển trạng thái {$lock->ledger_type} sang {$newStatus}",
        ]);

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'locked' ? 'Đã khóa kỳ kế toán thành công' : 'Đã mở khóa kỳ kế toán thành công',
            'lock'    => $lock,
        ]);
    }
}
