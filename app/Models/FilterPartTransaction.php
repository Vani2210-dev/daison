<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterPartTransaction extends Model
{
    use HasFactory;

    protected $table = 'filter_part_transactions';

    protected $fillable = [
        'part_id',
        'type',
        'quantity',
        'balance_before',
        'balance_after',
        'unit_price',
        'total_price',
        'reference_code',
        'note',
        'created_by',
        'created_by_name',
    ];

    protected $casts = [
        'quantity'       => 'integer',
        'balance_before' => 'integer',
        'balance_after'  => 'integer',
        'unit_price'     => 'decimal:2',
        'total_price'    => 'decimal:2',
    ];

    public function part()
    {
        return $this->belongsTo(FilterPart::class, 'part_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
