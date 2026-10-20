<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Polling endpoint: Trả về thông báo mới cần bật popup nổi + số lượng chưa đọc
     */
    public function poll(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success'      => true,
                'unread_count' => 0,
                'popups'       => [],
            ]);
        }

        $userId = $user->id;

        // 1. Lấy các thông báo chưa từng hiển thị popup nổi
        $popups = SystemNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->where('is_shown_popup', false)
            ->orderBy('id', 'asc')
            ->limit(5)
            ->get();

        // 2. Đánh dấu các thông báo này là đã hiển thị popup
        if ($popups->isNotEmpty()) {
            SystemNotification::whereIn('id', $popups->pluck('id'))
                ->update(['is_shown_popup' => true]);
        }

        // 3. Tổng số thông báo chưa đọc
        $unreadCount = SystemNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
            'popups'       => $popups->map(function ($n) {
                return [
                    'id'          => $n->id,
                    'type'        => $n->type,
                    'title'       => $n->title,
                    'message'     => $n->message,
                    'action_url'  => $n->action_url,
                    'data'        => $n->data,
                    'time_ago'    => $n->created_at ? $n->created_at->diffForHumans() : 'Vừa xong',
                    'created_at'  => $n->created_at ? $n->created_at->format('H:i d/m/Y') : '',
                ];
            }),
        ]);
    }

    /**
     * Lấy danh sách thông báo gần đây cho Bell Dropdown
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => true, 'notifications' => [], 'unread_count' => 0]);
        }

        $notifications = SystemNotification::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($n) {
                return [
                    'id'          => $n->id,
                    'type'        => $n->type,
                    'title'       => $n->title,
                    'message'     => $n->message,
                    'action_url'  => $n->action_url,
                    'is_read'     => (bool) $n->is_read,
                    'data'        => $n->data,
                    'time_ago'    => $n->created_at ? $n->created_at->diffForHumans() : 'Vừa xong',
                    'created_at'  => $n->created_at ? $n->created_at->format('H:i d/m/Y') : '',
                ];
            });

        $unreadCount = SystemNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'       => true,
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        }

        $notification = SystemNotification::where('user_id', $user->id)->findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        $unreadCount = SystemNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        }

        SystemNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read'        => true,
                'read_at'        => now(),
                'is_shown_popup' => true,
            ]);

        return response()->json([
            'success'      => true,
            'unread_count' => 0,
            'message'      => 'Đã đánh dấu tất cả thông báo là đã đọc',
        ]);
    }
}
