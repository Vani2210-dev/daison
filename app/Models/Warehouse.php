<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'branch_id',
        'address',
        'manager_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'warehouse_stocks')
            ->withPivot('stock_quantity', 'reserved_quantity', 'location')
            ->withTimestamps();
    }
}
