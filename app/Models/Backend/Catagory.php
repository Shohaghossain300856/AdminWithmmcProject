<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Fund;

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
}
