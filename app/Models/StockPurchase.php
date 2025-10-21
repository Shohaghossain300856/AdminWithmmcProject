<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stock;

use App\Models\Backend\Fund;
use App\Models\Backend\Supplier;

class StockPurchase extends Model
{
    use HasFactory;
    protected $fillable = [
        'fund_id',
        'supplier_id',
        'memo_no',
        'date',
        'initial_qty',
        'user_id',
    ];

 public function stocks()
{
    return $this->hasMany(\App\Models\Stock::class, 'stock_purchase_id', 'id');
}

       public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
       public function fund()
    {
        return $this->belongsTo(Fund::class);
    }
 
}
