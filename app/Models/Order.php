<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{ 
     protected $fillable = ['chargily_checkout_id', 'payment_method', 'payment_status'];

    public function orderItems() {
    return $this->hasMany(OrderItems::class);
}
    //
}