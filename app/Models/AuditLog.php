<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'timestamp',
        'performer_name',
        'user_id',
        'action',
        'module',
        'target_document_code',
        'field_name',
        'old_value',
        'new_value',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
