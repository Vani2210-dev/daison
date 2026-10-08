<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'author',
        'author_role',
        'priority',
        'target_department',
        'read_by',
    ];

    protected $casts = [
        'read_by' => 'array',
    ];
}
