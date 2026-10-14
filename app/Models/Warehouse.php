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

    /**
     * Tự động đồng bộ các kho xe lưu động từ bảng vehicles
     */
    public static function syncVehicleWarehouses()
    {
        $vehicles = Vehicle::with('driver')->get();
        foreach ($vehicles as $v) {
            self::updateOrCreate(
                ['code' => 'KHO-XE-' . $v->plate_number],
                [
                    'name'         => 'Kho Xe ' . $v->plate_number . ($v->driver ? ' (' . $v->driver->name . ')' : ''),
                    'type'         => 'Kho xe lưu động',
                    'manager_name' => $v->driver?->name ?? 'Tài xế',
                    'address'      => 'Xe BKS ' . $v->plate_number,
                    'is_active'    => true,
                ]
            );
        }
    }

    /**
     * Scope lấy các kho xe lưu động
     */
    public function scopeVehicleWarehouses($query)
    {
        return $query->where('type', 'Kho xe lưu động');
    }
}
