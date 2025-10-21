<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Product;
use App\Models\StockValidity;

class Stock extends Model
{
    use HasFactory;
   protected $fillable = [

        'stock_purchase_id', 
        'product_id', 
        'qty', 
        'avl_qty',
    ];

 public function product()
{
    return $this->belongsTo(\App\Models\Backend\Product::class, 'product_id');
}

public function validity()
{
    return $this->hasOne(\App\Models\StockValidity::class, 'stock_id', 'id');
}

}
