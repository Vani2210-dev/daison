<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\StaffGroup;
use App\Models\StaffGroupClassification;
use App\Models\User;
use Illuminate\Http\Request;

class StaffGroupController extends Controller
{
    /**
     * Danh sách nhóm nhân sự
     */
    public function index(Request $request)
    {
        $query = StaffGroup::with(['leader', 'classification'])->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('leader', function ($lq) use ($search) {
                      $lq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->whereJsonContains('category_ids', (int)$categoryId);
        }

        if ($classificationId = $request->input('classification_id')) {
            $query->where('classification_id', $classificationId);
        }

        if ($leaderId = $request->input('user_id')) {
            $query->where('user_id', $leaderId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $groups = $query->paginate(20)->withQueryString();

        $categories = Category::select('id', 'name', 'code', 'is_active')->orderBy('id')->get();
        $classifications = StaffGroupClassification::withCount('groups')->get();
        $users = User::select('id', 'name', 'email', 'phone', 'department', 'branch', 'avatar')
            ->orderBy('name')
            ->get();

        // Thống kê tổng hợp
        $allGroups = StaffGroup::all();
        $totalGroups = $allGroups->count();
        $activeGroups = $allGroups->where('status', 'active')->count();

        // Đếm tổng số nhân sự duy nhất tham gia trong các nhóm
        $uniqueMemberIds = [];
        foreach ($allGroups as $g) {
            if (is_array($g->user_ids)) {
                foreach ($g->user_ids as $uid) {
                    $uniqueMemberIds[(int)$uid] = true;
                }
            }
            if ($g->user_id) {
                $uniqueMemberIds[(int)$g->user_id] = true;
            }
        }
        $totalMembers = count($uniqueMemberIds);
        $totalClassifications = $classifications->count();
        $totalCategories = $categories->count();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'groups' => $groups,
                'categories' => $categories,
                'classifications' => $classifications,
                'stats' => compact('totalGroups', 'activeGroups', 'totalMembers', 'totalClassifications', 'totalCategories')
            ]);
        }

        return view('staff_groups.index', compact(
            'groups',
            'categories',
            'classifications',
            'users',
            'totalGroups',
            'activeGroups',
            'totalMembers',
            'totalClassifications',
            'totalCategories'
        ));
    }

    /**
     * Thêm mới nhóm nhân sự
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'code'              => 'nullable|string|max:50|unique:staff_groups,code',
            'category_ids'      => 'nullable',
            'classification_id' => 'nullable|exists:staff_group_classifications,id',
            'user_id'           => 'nullable|exists:users,id',
            'user_ids'          => 'nullable',
            'description'       => 'nullable|string',
            'status'            => 'nullable|string|in:active,inactive',
        ]);

        // Xử lý danh sách category_ids (Phân loại ID là ID của categories, có thể chọn nhiều ID)
        $categoryIds = $request->input('category_ids', []);
        if (is_string($categoryIds)) {
            $categoryIds = json_decode($categoryIds, true) ?: [];
        }
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', (array)$categoryIds))));

        // Xử lý danh sách user_ids
        $userIds = $request->input('user_ids', []);
        if (is_string($userIds)) {
            $userIds = json_decode($userIds, true) ?: [];
        }
        $userIds = array_values(array_unique(array_filter(array_map('intval', (array)$userIds))));

        // Nếu có chọn trưởng nhóm (user_id), tự động thêm vào user_ids nếu chưa có
        if (!empty($validated['user_id']) && !in_array((int)$validated['user_id'], $userIds)) {
            $userIds[] = (int)$validated['user_id'];
        }

        // Tự động sinh mã nhóm nếu chưa nhập
        $code = $validated['code'] ?? null;
        if (empty($code)) {
            $nextId = (StaffGroup::max('id') ?? 0) + 1;
            $code = 'NNS-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        $group = StaffGroup::create([
            'name'              => $validated['name'],
            'code'              => $code,
            'category_ids'      => $categoryIds,
            'classification_id' => $validated['classification_id'] ?? null,
            'user_id'           => $validated['user_id'] ?? null,
            'user_ids'          => $userIds,
            'description'       => $validated['description'] ?? null,
            'status'            => $validated['status'] ?? 'active',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã tạo nhóm nhân sự thành công!',
                'group'   => $group->load(['leader', 'classification'])
            ]);
        }

        return redirect()->route('staff_groups.index')->with('success', 'Đã tạo nhóm nhân sự "' . $group->name . '" thành công!');
    }

    /**
     * Cập nhật nhóm nhân sự
     */
    public function update(Request $request, $id)
    {
        $group = StaffGroup::findOrFail($id);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'code'              => 'nullable|string|max:50|unique:staff_groups,code,' . $id,
            'category_ids'      => 'nullable',
            'classification_id' => 'nullable|exists:staff_group_classifications,id',
            'user_id'           => 'nullable|exists:users,id',
            'user_ids'          => 'nullable',
            'description'       => 'nullable|string',
            'status'            => 'nullable|string|in:active,inactive',
        ]);

        // Xử lý danh sách category_ids
        $categoryIds = $request->input('category_ids', []);
        if (is_string($categoryIds)) {
            $categoryIds = json_decode($categoryIds, true) ?: [];
        }
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', (array)$categoryIds))));

        // Xử lý danh sách user_ids
        $userIds = $request->input('user_ids', []);
        if (is_string($userIds)) {
            $userIds = json_decode($userIds, true) ?: [];
        }
        $userIds = array_values(array_unique(array_filter(array_map('intval', (array)$userIds))));

        if (!empty($validated['user_id']) && !in_array((int)$validated['user_id'], $userIds)) {
            $userIds[] = (int)$validated['user_id'];
        }

        $group->update([
            'name'              => $validated['name'],
            'code'              => (!empty($validated['code'])) ? $validated['code'] : $group->code,
            'category_ids'      => $categoryIds,
            'classification_id' => $validated['classification_id'] ?? null,
            'user_id'           => $validated['user_id'] ?? null,
            'user_ids'          => $userIds,
            'description'       => $validated['description'] ?? null,
            'status'            => $validated['status'] ?? $group->status,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật nhóm nhân sự thành công!',
                'group'   => $group->load(['leader', 'classification'])
            ]);
        }

        return redirect()->route('staff_groups.index')->with('success', 'Đã cập nhật thông tin nhóm nhân sự "' . $group->name . '"!');
    }

    /**
     * Xóa nhóm nhân sự
     */
    public function destroy(Request $request, $id)
    {
        $group = StaffGroup::findOrFail($id);
        $name = $group->name;
        $group->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa nhóm nhân sự "' . $name . '" thành công!'
            ]);
        }

        return redirect()->route('staff_groups.index')->with('success', 'Đã xóa nhóm nhân sự "' . $name . '"!');
    }

    /**
     * Thêm mới phân loại nhóm nhân sự
     */
    public function storeClassification(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $classification = StaffGroupClassification::create([
            'name'        => $validated['name'],
            'code'        => $validated['code'] ?? null,
            'color'       => $validated['color'] ?? 'blue',
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Đã tạo phân loại nhóm thành công!',
                'classification' => $classification
            ]);
        }

        return back()->with('success', 'Đã tạo phân loại nhóm "' . $classification->name . '"!');
    }

    /**
     * Xóa phân loại nhóm nhân sự
     */
    public function destroyClassification(Request $request, $id)
    {
        $classification = StaffGroupClassification::findOrFail($id);
        $name = $classification->name;

        // Bỏ gán phân loại cho các nhóm trước khi xóa
        StaffGroup::where('classification_id', $id)->update(['classification_id' => null]);
        $classification->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa phân loại "' . $name . '"!'
            ]);
        }

        return back()->with('success', 'Đã xóa phân loại "' . $name . '"!');
    }
}
