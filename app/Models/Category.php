<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'code',
        'name',
        'manager_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Nhà cung cấp trực thuộc
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Mối quan hệ N-N với Thương hiệu
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'category_brands')->withTimestamps();
    }

    /**
     * Danh sách sản phẩm thuộc loại hàng này
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Danh sách nhóm nhỏ thuộc loại hàng này
     */
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    /**
     * Danh sách chiết khấu của các nhà cung cấp cho loại hàng này
     */
    public function supplierDiscounts()
    {
        return $this->hasMany(SupplierCategoryDiscount::class);
    }
}
