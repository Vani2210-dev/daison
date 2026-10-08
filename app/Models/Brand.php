<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'country',
        'logo_url',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Mối quan hệ N-N với Loại hàng
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_brands')->withTimestamps();
    }

    /**
     * Danh sách sản phẩm thuộc thương hiệu này
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Danh sách nhóm nhỏ thuộc thương hiệu này
     */
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }
}
