<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'stock', 'price', 'discount'
    ];

    public function reviews(){
        return $this->hasMany('App\Model\Review');
    }
}
