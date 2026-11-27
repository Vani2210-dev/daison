<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCategoryDiscount extends Model
{
    use HasFactory;

    protected $table = 'supplier_category_discounts';

    protected $fillable = [
        'supplier_id',
        'category_id',
        'discount_percent',
        'notes',
    ];

    protected $casts = [
        'discount_percent' => 'float',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
