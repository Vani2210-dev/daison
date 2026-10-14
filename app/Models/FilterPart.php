<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterPart extends Model
{
    use HasFactory;

    protected $table = 'filter_parts';

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'in_price',
        'out_price',
        'stock_qty',
        'min_stock_alert',
        'location',
        'description',
        'status',
    ];

    protected $casts = [
        'in_price'        => 'decimal:2',
        'out_price'       => 'decimal:2',
        'stock_qty'       => 'integer',
        'min_stock_alert' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(FilterPartTransaction::class, 'part_id');
    }

    public function installationItems()
    {
        return $this->hasMany(FilterInstallationItem::class, 'part_id');
    }

    public function repairItems()
    {
        return $this->hasMany(FilterRepairItem::class, 'part_id');
    }
}
