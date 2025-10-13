<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Fund;
use App\Models\Backend\Subcategory;

class Catagory extends Model
{
    use HasFactory;

     protected $fillable = [
        'fund_id',
        'code',
        'name',
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

       public function subcategories()
    {
        return $this->hasMany(Subcategory::class, 'category_id', 'id');
    }
}
