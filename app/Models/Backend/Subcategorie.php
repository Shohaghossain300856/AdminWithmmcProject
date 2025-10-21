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
    'sub_category',
    'sub_category_code',
];
}
