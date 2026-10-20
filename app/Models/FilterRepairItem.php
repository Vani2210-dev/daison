<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterRepairItem extends Model
{
    use HasFactory;

    protected $table = 'filter_repair_items';

    protected $fillable = [
        'repair_id',
        'part_id',
        'part_code',
        'part_name',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'is_warranty',
        'note',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_warranty' => 'boolean',
    ];

    public function repair()
    {
        return $this->belongsTo(FilterRepair::class, 'repair_id');
    }

    public function part()
    {
        return $this->belongsTo(FilterPart::class, 'part_id');
    }
}
