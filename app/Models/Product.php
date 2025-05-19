<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [ 'name', 'slug', 'brand_id', 'product_type', 'short_description', 'long_description',
        'price', 'price', 'discount_rate', 'discount_price', 'gst_rate', 'gst_amount', 'total_price', 'stock', 'sku', 'barcode',
        'weight', 'dimensions','is_home', 'is_special', 'is_featured', 'is_active'
    ];
    
    public function product_subcategories()
    {
        return $this->hasMany(ProductCategory::class, 'product_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
