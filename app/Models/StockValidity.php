<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockValidity extends Model
{
    use HasFactory;

     protected $fillable = [
        'stock_id','validity_start','validity_end','warranty_start','warranty_end'
    ];
    protected $casts = [
        'validity_start' => 'date',
        'validity_end'   => 'date',
        'warranty_start' => 'date',
        'warranty_end'   => 'date',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
