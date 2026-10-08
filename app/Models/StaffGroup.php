<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffGroup extends Model
{
    use HasFactory;

    protected $table = 'staff_groups';

    protected $fillable = [
        'name',
        'code',
        'category_ids',
        'classification_id',
        'user_id',
        'user_ids',
        'description',
        'status',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'user_ids' => 'array',
    ];

    /**
     * Trưởng nhóm (Leader)
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Phân loại nhóm (Classification)
     */
    public function classification(): BelongsTo
    {
        return $this->belongsTo(StaffGroupClassification::class, 'classification_id');
    }

    /**
     * Danh sách phân loại sản phẩm/ngành hàng (Categories)
     */
    public function getCategoriesAttribute()
    {
        $ids = $this->category_ids;
        if (empty($ids) || !is_array($ids)) {
            return collect();
        }
        return Category::whereIn('id', $ids)->get();
    }

    /**
     * Kiểm tra nhóm có phụ trách category này không
     */
    public function hasCategory(int|string $categoryId): bool
    {
        $ids = $this->category_ids ?: [];
        return in_array((int)$categoryId, array_map('intval', (array)$ids));
    }

    /**
     * Lấy danh sách đối tượng User là thành viên của nhóm
     */
    public function getMembersAttribute()
    {
        $ids = $this->user_ids;
        if (empty($ids) || !is_array($ids)) {
            return collect();
        }
        return User::whereIn('id', $ids)->get();
    }

    /**
     * Tổng số lượng thành viên trong nhóm
     */
    public function getMemberCountAttribute(): int
    {
        $ids = $this->user_ids;
        return is_array($ids) ? count($ids) : 0;
    }

    /**
     * Kiểm tra user có phải là thành viên trong nhóm không
     */
    public function hasMember(int|string $userId): bool
    {
        $ids = $this->user_ids ?: [];
        return in_array((int)$userId, array_map('intval', (array)$ids));
    }

    /**
     * Thêm thành viên vào nhóm
     */
    public function addMember(int|string $userId): void
    {
        $ids = array_map('intval', (array)($this->user_ids ?: []));
        $uId = (int)$userId;
        if (!in_array($uId, $ids)) {
            $ids[] = $uId;
            $this->user_ids = array_values($ids);
            $this->save();
        }
    }

    /**
     * Xóa thành viên khỏi nhóm
     */
    public function removeMember(int|string $userId): void
    {
        $ids = array_map('intval', (array)($this->user_ids ?: []));
        $uId = (int)$userId;
        $filtered = array_filter($ids, fn($id) => $id !== $uId);
        $this->user_ids = array_values($filtered);
        $this->save();
    }
}
