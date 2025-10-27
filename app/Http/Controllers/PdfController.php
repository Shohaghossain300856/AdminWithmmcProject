<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Backend\Subcategorie;
use App\Models\Backend\Catagory;
use App\Models\Backend\Fund;
use App\Models\Backend\Product;
use App\Models\PreorderItem;
use App\Models\Preorder;
use Illuminate\Support\Facades\DB;

class PdfController extends Controller
{

public function Subcategoriespdf()
{
    $rows = Subcategorie::with([
        'fund:id,fund',
        'category:id,fund_id,code,name'
    ])
    ->orderBy('categorie_id') 
    ->orderBy('sn')           
    ->get();

  
    $tree = $rows->groupBy('categorie_id')->map(function ($byCat, $catId) {
        $first = $byCat->first();

       
        $items = $byCat->map(function ($m) {
            return [
                'id'              => $m->id,
                'memo_no'         => $m->memo_no,
                'date'            => $m->date,
                'fund_id'         => $m->fund_id,
                'code'            => $m->code,          
                'sub_category'    => $m->sub_category, 
                'categorie_id'    => $m->categorie_id,

              
                'total_budget'    => (float) $m->total_budget,
                'total_pending'   => (float) $m->total_pending,
                'total_balance'   => (float) $m->total_balance,
                'sn'              => $m->sn,
                'budget'          => (float) $m->budget,
                'revised'         => (float) $m->revised,
                'disbursement'    => (float) $m->disbursement,
                'withdrawal'      => (float) $m->withdrawal,
                'total'           => (float) $m->total,
                'expense_pending' => (float) $m->expense_pending,
                'actual_expense'  => (float) $m->actual_expense,
                'balance'         => (float) $m->balance,
                'rate'            => (float) $m->rate,

                'fund'            => [
                    'id'   => $m->fund->id ?? $m->fund_id,
                    'fund' => $m->fund->fund ?? null,
                ],
                'category'        => [
                    'id'      => $m->category->id ?? $m->categorie_id,
                    'fund_id' => $m->category->fund_id ?? $m->fund_id,
                    'code'    => $m->category->code ?? null,
                    'name'    => $m->category->name ?? null,
                ],
            ];
        })->values();
        $sums = [
            'budget'          => $byCat->sum('budget'),
            'revised'         => $byCat->sum('revised'),
            'disbursement'    => $byCat->sum('disbursement'),
            'withdrawal'      => $byCat->sum('withdrawal'),
            'total'           => $byCat->sum('total'),
            'expense_pending' => $byCat->sum('expense_pending'),
            'actual_expense'  => $byCat->sum('actual_expense'),
            'balance'         => $byCat->sum('balance'),
            'rate_avg'        => (float) ($byCat->avg('rate') ?? 0), 
        ];

        return [
            'category_id'   => (int) $catId,
            'category_code' => $first->category->code ?? '',
            'category_name' => $first->category->name ?? '',
            'items'         => $items->all(), 
            'sums'          => $sums,         
        ];
    })->values();
    return view('Pdf.SubCatagoriesPDF', compact('tree'));
}


public function preorderpdf($preOrder)
{
    $preorder = Preorder::with('fund', 'supplier')->findOrFail($preOrder);

    $items = PreorderItem::with([
        'product.category',
        'product.subcategory',
        'product.country'
    ])->where('preorder_id', $preOrder)->get();
    
  // return response()->json([
  //       'status' => true,
  //       'message' => 'Fetch data successfully',
  //       'preorder' => $preorder,
  //       'items' => $items,
  //   ]);


    return view('Pdf.pre-order', compact('preorder', 'items'));
}






}
