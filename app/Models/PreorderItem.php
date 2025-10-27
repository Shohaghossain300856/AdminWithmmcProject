<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Preorder;
use App\Models\Backend\Product;
class PreorderItem extends Model
{
    use HasFactory;

   protected $fillable = [
        'preorder_id','product_id','qty','unit_price','line_total',
    ];

    public function preorder()
    {
        return $this->belongsTo(Preorder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    } 
}
