<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'manager',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'branch_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id');
    }
}
