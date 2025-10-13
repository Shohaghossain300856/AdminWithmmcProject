<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchase extends Model
{
    use HasFactory;

     protected $fillable = [
        'stock_id',
        'date',
        'ref_no',
        'purchase_qty',
        'unit_price',
    ];

    protected $casts = [
        'date' => 'date',
        'purchase_qty' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
