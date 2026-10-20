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

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    /**
     * Scope chỉ lấy các kho vật lý chính thức của công ty (loại trừ kho hàng ôm nhân viên và kho xe)
     */
    public function scopeCompanyWarehouses($query)
    {
        return $query->where('is_active', true)
            ->whereNotIn('type', ['Kho hàng ôm', 'Kho xe lưu động'])
            ->where('name', 'not like', '%Kho Hàng Ôm%')
            ->where('name', 'not like', '%Kho Xe%');
    }

    /**
     * Tự động đồng bộ các kho hàng ôm nhân viên từ bảng users
     */
    public static function syncEmployeeWarehouses()
    {
        // Chuyển đổi các kho xe cũ sang kho hàng ôm nếu có
        $oldVehicleWarehouses = self::where('type', 'Kho xe lưu động')->get();
        foreach ($oldVehicleWarehouses as $ow) {
            $matchedUser = User::where('name', $ow->manager_name)->first();
            if ($matchedUser) {
                $ow->update([
                    'code'         => 'KHO-OM-' . $matchedUser->id,
                    'name'         => 'Kho Hàng Ôm - ' . $matchedUser->name,
                    'type'         => 'Kho hàng ôm',
                    'manager_name' => $matchedUser->name,
                    'address'      => 'Hàng ôm nhân viên: ' . $matchedUser->name . ($matchedUser->phone ? ' (' . $matchedUser->phone . ')' : ''),
                    'is_active'    => true,
                ]);
            } else {
                $ow->update([
                    'name' => str_replace('Kho Xe', 'Kho Hàng Ôm', $ow->name),
                    'type' => 'Kho hàng ôm',
                ]);
            }
        }

        // Đồng bộ kho hàng ôm cho nhân viên kinh doanh, giao nhận, thị trường
        $staff = User::where('status', '!=', 'Đã nghỉ việc')
            ->where(function($q) {
                $q->whereIn('department', ['Sales', 'Logistics', 'Kinh doanh', 'Thị trường', 'Giao hàng', 'Executive'])
                  ->orWhere('staff_type', 'company_sales');
            })
            ->get();

        foreach ($staff as $u) {
            self::updateOrCreate(
                ['code' => 'KHO-OM-' . $u->id],
                [
                    'name'         => 'Kho Hàng Ôm - ' . $u->name,
                    'type'         => 'Kho hàng ôm',
                    'manager_name' => $u->name,
                    'address'      => 'Hàng ôm nhân viên: ' . $u->name . ($u->phone ? ' (' . $u->phone . ')' : ''),
                    'is_active'    => true,
                ]
            );
        }
    }

    /**
     * Tương thích ngược: alias cho syncEmployeeWarehouses
     */
    public static function syncVehicleWarehouses()
    {
        self::syncEmployeeWarehouses();
    }

    /**
     * Scope lấy các kho hàng ôm nhân viên
     */
    public function scopeEmployeeWarehouses($query)
    {
        return $query->whereIn('type', ['Kho hàng ôm', 'Kho xe lưu động']);
    }

    /**
     * Scope tương thích ngược
     */
    public function scopeVehicleWarehouses($query)
    {
        return $this->scopeEmployeeWarehouses($query);
    }
}
