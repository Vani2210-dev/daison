<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Bootstrap data for Announcements
     */
    public function getBootstrapData()
    {
        return Announcement::latest('id')->get()->map(function ($ann) {
            return [
                'id'               => (string) $ann->id,
                'title'            => $ann->title,
                'content'          => $ann->content,
                'author'           => $ann->author,
                'authorRole'       => $ann->author_role,
                'createdAt'        => $ann->created_at ? $ann->created_at->format('Y-m-d H:i') : date('Y-m-d H:i'),
                'priority'         => $ann->priority,
                'targetDepartment' => $ann->target_department,
                'readBy'           => $ann->read_by ?? [],
            ];
        });
    }

    /**
     * API: Get announcements
     */
    public function getAnnouncements()
    {
        return response()->json(['announcements' => $this->getBootstrapData()]);
    }

    /**
     * API: Save (Create or Update) Announcement
     */
    public function saveAnnouncement(Request $request)
    {
        $id = $request->input('id');
        $data = [
            'title'             => $request->input('title'),
            'content'           => $request->input('content'),
            'author'            => $request->input('author', Auth::user()?->name ?? 'Ban Giám Đốc'),
            'author_role'       => $request->input('authorRole', 'Ban Quản Trị'),
            'priority'          => $request->input('priority', 'Bình thường'),
            'target_department' => $request->input('targetDepartment', 'Tất cả'),
        ];

        if ($id) {
            $ann = Announcement::find($id);
            if ($ann) {
                $ann->update($data);
                return response()->json(['success' => true, 'announcement' => $ann]);
            }
        }

        $ann = Announcement::create($data);
        return response()->json(['success' => true, 'announcement' => $ann]);
    }
}
