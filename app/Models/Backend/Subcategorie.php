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
        'memo_no','date','fund_id','categorie_id',
        'total_budget','total_pending','total_balance',
        'sn','budget','revised','disbursement','withdrawal',
        'total','expense_pending','actual_expense','balance','rate',
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
