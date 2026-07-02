<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{protected $fillable = ['product_id', 'image', 'is_primary'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('uploads/product/small/' . $this->image);
        }
        return null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}