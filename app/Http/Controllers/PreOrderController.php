<?php

namespace App\Http\Controllers;

use App\Models\Preorder;
use App\Models\PreorderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\Backend\Fund;
use App\Models\Backend\Catagory;
use App\Models\Backend\Subcategorie;
use App\Models\Backend\Supplier;
use App\Models\Backend\Product;

class PreOrderController extends Controller
{
    public function index()
    {
        return view('backend.PreOrder.PreOrderIndex');
    }

public function create()
{
   $data = Preorder::with('fund','supplier')->latest()->get();

  return response()->json([
    'status' => true,
    'message' => 'Fetch data successfully',
    'data' => $data,
 ]);

}

 public function store(Request $request)
    {
        $data = $request->validate([
            'fund_id'     => ['required','integer','exists:funds,id'],
            'supplier_id' => ['required','integer','exists:suppliers,id'],
            'memo_no'     => ['nullable','string','max:255', Rule::unique('preorders','memo_no')],
            'date'        => ['required','date'],

            'products'              => ['required','array','min:1'],
            'products.*.product_id' => ['required','integer','exists:products,id'],
            'products.*.qty'        => ['required','numeric','min:0.0001'],
            'products.*.unit_price' => ['required','numeric','min:0'],
        ]);

        if (empty($data['memo_no'])) {
            $data['memo_no'] = 'PRE-'.now()->format('Ymd').'-'.mt_rand(1000,9999);
        }

        $totalQty = 0; $totalAmount = 0;
        foreach ($data['products'] as $it) {
            $line = (float)$it['qty'] * (float)$it['unit_price'];
            $totalQty   += (float)$it['qty'];
            $totalAmount += $line;
        }

        $preorder = DB::transaction(function () use ($data, $totalQty, $totalAmount) {
            $preorder = Preorder::create([
                'memo_no'      => $data['memo_no'],
                'fund_id'      => $data['fund_id'],
                'supplier_id'  => $data['supplier_id'],
                'date'         => $data['date'],
                'total_qty'    => $totalQty,
                'total_amount' => $totalAmount,
            ]);

            $rows = [];
            foreach ($data['products'] as $it) {
                $qty   = (float)$it['qty'];
                $price = (float)$it['unit_price'];
                $line  = $qty * $price;

                $rows[] = [
                    'preorder_id' => $preorder->id,
                    'product_id'  => $it['product_id'],
                    'qty'         => $qty,
                    'unit_price'  => $price,
                    'line_total'  => $line,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
            PreorderItem::insert($rows);

            return $preorder->load(['items.product']);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Preorder saved successfully',
            'data'    => $preorder,
        ], 201);
    }

}
