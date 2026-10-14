<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterInstallationItem extends Model
{
    use HasFactory;

    protected $table = 'filter_installation_items';

    protected $fillable = [
        'installation_id',
        'part_id',
        'part_code',
        'part_name',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'note',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function installation()
    {
        return $this->belongsTo(FilterInstallation::class, 'installation_id');
    }

    public function part()
    {
        return $this->belongsTo(FilterPart::class, 'part_id');
    }
}
