<?php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Fund;
use App\Models\Backend\Catagory;

class Subcategorie extends Model
{
    use HasFactory;

protected $fillable = [
    'fund_id',
    'categorie_id',
    'sub_category',
    'sub_category_code',
];

    public function fund()
    {
      return $this->belongsTo(Fund::class);
    }

    public function category()
    {
        return $this->belongsTo(Catagory::class, 'categorie_id');
    }
}
