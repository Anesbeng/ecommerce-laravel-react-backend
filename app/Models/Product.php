<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $appends = ['image_url', 'total_qty'];
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('uploads/product/small/' . $this->image);
        }
        return null;
    }

    // Sum of every size's stock — for places that just need "is this in stock at all"
    public function getTotalQtyAttribute()
    {
        return $this->sizes->sum(function ($size) {
            return $size->pivot->qty;
        });
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }
    public function sizes()
{
    return $this->belongsToMany(Size::class, 'product_sizes')->withPivot('qty')->withTimestamps();
}
}