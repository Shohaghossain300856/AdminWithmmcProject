<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Catagory;
use App\Models\Backend\Fund;

class Stock extends Model
{
    use HasFactory;

 protected $fillable = [
        'fund_id',
        'category_id',
        'item_id',  
        'qty',
    ];

    public function purchases()
    {
        return $this->hasMany(StockPurchase::class);
    }

    public function validity()
    {
        return $this->hasOne(StockValidity::class);
    }

    

    public function fund()    { return $this->belongsTo(Fund::class); }
    public function category(){ return $this->belongsTo(Catagory::class); }
}
