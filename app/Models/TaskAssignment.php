<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'category',
        'assigned_to_id',
        'created_by_id',
        'deadline',
        'priority',
        'status',
        'progress_percent',
        'related_order_code',
        'checklist',
        'feedback',
        'rating_score',
        'completed_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
        'checklist' => 'array',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function creator()
    {
        return $this->createdBy();
    }
}
