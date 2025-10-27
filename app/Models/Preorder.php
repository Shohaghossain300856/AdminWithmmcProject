<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Fund;
use App\Models\Backend\Supplier;
use App\Models\PreorderItem;

class Preorder extends Model
{

    use HasFactory;

  protected $fillable = [
        'memo_no','fund_id','supplier_id','date','total_qty','total_amount',
    ];


    public function items()
    {
        return $this->hasMany(PreorderItem::class);
    }

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}


