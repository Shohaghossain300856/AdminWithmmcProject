<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Catagory;
use App\Models\Backend\Fund;
use App\Models\Backend\Subcategorie;
use App\Models\Country;
use App\Models\Stock;

class Product extends Model
{
    use HasFactory;

   protected $fillable = [

    'country_id',
    'category_id',
    'subCatagorie_id',
    'type',
    'unit',
    'product',
];

    public function fund()
    {
        return $this->belongsTo(Fund::class, 'fund_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_id');
    }

public function country()
{
    return $this->belongsTo(\App\Models\Country::class, 'country_id');
}

public function category()
{
    return $this->belongsTo(\App\Models\Backend\Catagory::class, 'category_id');
}

public function subcategory()
{
    return $this->belongsTo(\App\Models\Backend\Subcategorie::class, 'subCatagorie_id');
}
}
