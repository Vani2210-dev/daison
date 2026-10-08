<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentKpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'department_name',
        'overall_target_percent',
        'metrics',
        'month',
    ];

    protected $casts = [
        'overall_target_percent' => 'float',
        'metrics'                => 'array',
    ];
}
