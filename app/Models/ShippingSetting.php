<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    
    protected $fillable = ['rate', 'is_free'];

}