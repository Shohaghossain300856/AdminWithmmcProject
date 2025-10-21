<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockValidity extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_purchase_id',
        'stock_id',
        'warranty_start', 'warranty_end',
        'validity_start', 'validity_end',
    ];

}
