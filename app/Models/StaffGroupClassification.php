<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffGroupClassification extends Model
{
    use HasFactory;

    protected $table = 'staff_group_classifications';

    protected $fillable = [
        'name',
        'code',
        'color',
        'description',
    ];

    /**
     * Danh sách nhóm nhân sự thuộc phân loại này
     */
    public function groups()
    {
        return $this->hasMany(StaffGroup::class, 'classification_id');
    }

    /**
     * Helper lấy CSS class theo màu sắc
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->color) {
            'emerald', 'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'amber', 'yellow'  => 'bg-amber-50 text-amber-700 border-amber-200',
            'rose', 'red'      => 'bg-rose-50 text-rose-700 border-rose-200',
            'purple', 'violet' => 'bg-purple-50 text-purple-700 border-purple-200',
            'indigo'           => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'sky', 'cyan'      => 'bg-sky-50 text-sky-700 border-sky-200',
            'slate', 'gray'    => 'bg-slate-100 text-slate-700 border-slate-200',
            default            => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }
}
