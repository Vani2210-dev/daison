<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRolePermission extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'permission_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
