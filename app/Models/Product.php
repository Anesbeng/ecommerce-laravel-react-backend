<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('uploads/product/small/' . $this->image);
        }
        return null;
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }
    public function sizes()
{
    return $this->belongsToMany(Size::class, 'product_sizes');
}
}