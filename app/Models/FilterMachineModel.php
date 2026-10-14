<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterMachineModel extends Model
{
    use HasFactory;

    protected $table = 'filter_machine_models';

    protected $fillable = [
        'code',
        'name',
        'brand',
        'filter_stages',
        'type',
        'standard_price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'filter_stages'  => 'integer',
        'standard_price' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function installations()
    {
        return $this->hasMany(FilterInstallation::class, 'machine_model_id');
    }
}
