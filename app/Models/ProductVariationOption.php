<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductVariationOption extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = ['variation_id','variation_type','variation_name','value','price','stock'];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('variation-option');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
