<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Bootstrap data for Attendance in SPA
     */
    public function getBootstrapData()
    {
        return AttendanceRecord::with('user')->get()->map(function ($a) {
            return [
                'id'          => (string) $a->id,
                'staffId'     => (string) $a->user_id,
                'staffName'   => $a->user?->name ?? 'Nhân viên',
                'department'  => $a->user?->department ?? 'Sales',
                'date'        => $a->date ? $a->date->format('Y-m-d') : date('Y-m-d'),
                'checkInTime' => $a->check_in_time ?? '08:00:00',
                'checkOutTime'=> $a->check_out_time,
                'checkInGps'  => [
                    'lat'     => (float) ($a->check_in_lat ?? 20.9982),
                    'lng'     => (float) ($a->check_in_lng ?? 105.8415),
                    'address' => $a->check_in_address ?? 'Văn phòng Trụ sở Chính',
                ],
                'status'      => $a->status,
                'workHours'   => (float) $a->work_hours,
                'notes'       => $a->notes ?? '',
            ];
        });
    }

    /**
     * API: Get attendance logs
     */
    public function getAttendance(Request $request)
    {
        $userId = $request->input('user_id');
        $month = $request->input('month', date('Y-m'));

        $query = AttendanceRecord::with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($month) {
            $query->where('date', 'like', "{$month}%");
        }

        $records = $query->latest('date')->get()->map(function ($rec) {
            return [
                'id'              => (string) $rec->id,
                'userId'          => (string) $rec->user_id,
                'userName'        => $rec->user?->name ?? 'Nhân viên',
                'date'            => $rec->date ? $rec->date->format('Y-m-d') : '',
                'checkInTime'     => $rec->check_in_time,
                'checkOutTime'    => $rec->check_out_time,
                'checkInLat'      => (float) $rec->check_in_lat,
                'checkInLng'      => (float) $rec->check_in_lng,
                'checkInAddress'  => $rec->check_in_address,
                'selfieUrl'       => $rec->selfie_url,
                'status'          => $rec->status,
                'workHours'       => (float) $rec->work_hours,
                'notes'           => $rec->notes,
            ];
        });

        return response()->json(['attendance' => $records]);
    }

    /**
     * API: Check-in with GPS location
     */
    public function checkIn(Request $request)
    {
        $userId = Auth::id() ?? $request->input('userId', 1);
        $today = date('Y-m-d');
        $now = date('H:i:s');

        $record = AttendanceRecord::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if ($record && $record->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã thực hiện chấm công vào hôm nay lúc ' . $record->check_in_time,
            ], 400);
        }

        if (!$record) {
            $record = new AttendanceRecord();
            $record->user_id = $userId;
            $record->date = $today;
        }

        $record->check_in_time = $now;
        $record->check_in_lat = $request->input('lat');
        $record->check_in_lng = $request->input('lng');
        $record->check_in_address = $request->input('address', 'Chi nhánh Mê Linh');
        $record->selfie_url = $request->input('selfieUrl');
        $record->status = 'Đúng giờ';
        $record->notes = $request->input('notes');
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Chấm công GPS vào ca thành công!',
            'record'  => $record,
        ]);
    }

    /**
     * API: Check-out with GPS location
     */
    public function checkOut(Request $request)
    {
        $userId = Auth::id() ?? $request->input('userId', 1);
        $today = date('Y-m-d');
        $now = date('H:i:s');

        $record = AttendanceRecord::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if (!$record || !$record->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa chấm công vào ca hôm nay',
            ], 400);
        }

        $record->check_out_time = $now;
        
        // Calculate work hours
        $inTimestamp = strtotime($today . ' ' . $record->check_in_time);
        $outTimestamp = strtotime($today . ' ' . $now);
        $diffHours = max(0, round(($outTimestamp - $inTimestamp) / 3600, 2));
        $record->work_hours = $diffHours;
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Chấm công GPS tan ca thành công! Số giờ làm việc: ' . $diffHours . 'h',
            'record'  => $record,
        ]);
    }
}
