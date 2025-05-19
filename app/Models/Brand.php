<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'slug', 'description', 'is_visible', 'is_home', 'is_popular', 'is_menu', 'is_special'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
