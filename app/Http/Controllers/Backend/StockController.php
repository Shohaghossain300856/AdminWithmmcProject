<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Backend\Catagory;
use App\Models\Backend\Fund;
use App\Models\Backend\Product;
use App\Models\Stock;
use App\Models\StockPurchase;
use App\Models\StockValidity;

class StockController extends Controller
{
    public  function index()
    {
        return view('backend.Stock.create');
    }

public function create()
{
    $rows = DB::table('stocks')
        ->join('stock_purchases', 'stock_purchases.id', '=', 'stocks.stock_purchase_id')
        ->join('products', 'products.id', '=', 'stocks.product_id')
        ->select([
            'stocks.product_id',
            'stock_purchases.fund_id',
            'products.category_id',
            DB::raw('SUM(stocks.qty) as total_qty'),
        ])
        ->groupBy('stocks.product_id', 'stock_purchases.fund_id', 'products.category_id')
        ->get();

    $productIds  = $rows->pluck('product_id')->unique();
    $fundIds     = $rows->pluck('fund_id')->unique();
    $categoryIds = $rows->pluck('category_id')->unique();

    $products  = Product::whereIn('id', $productIds)
                    ->select('id','product','category_id','subCatagorie_id','unit')
                    ->get()->keyBy('id');
    $funds     = Fund::whereIn('id', $fundIds)
                    ->select('id','fund') 
                    ->get()->keyBy('id');
    $categories= Catagory::whereIn('id', $categoryIds)
                    ->select('id','name')
                    ->get()->keyBy('id');

    $data = $rows->map(function ($r) use ($products,$funds,$categories) {
        return [
            'product_id'   => $r->product_id,
            'fund_id'      => $r->fund_id,
            'category_id'  => $r->category_id,
            'total_qty'    => (float) $r->total_qty,
            'product'      => $products[$r->product_id] ?? null,
            'fund'         => $funds[$r->fund_id] ?? null,
            'category'     => $categories[$r->category_id] ?? null,
        ];
    })->values();

    return response()->json([
        'status'  => true,
        'message' => 'Grouped stock data loaded successfully',
        'data'    => $data,
    ], 200);
}



public function store(Request $req)
{
    $data = $req->validate([
        'fund_id'                    => ['required', 'integer', 'exists:funds,id'],
        'supplier_id'                => ['required', 'integer', 'exists:suppliers,id'],
        'memo_no'                    => ['nullable', 'string', 'max:64'],
        'date'                       => ['required', 'date'],
        'products'                   => ['required', 'array', 'min:1'],

        'products.*.product_id'      => ['required', 'integer', 'exists:products,id'],
        'products.*.qty'             => ['required', 'integer', 'min:1'],

        'products.*.warranty_start'  => ['nullable', 'date'],
        'products.*.warranty_end'    => ['nullable', 'date'],

        'products.*.validity_start'  => ['nullable', 'date'],
        'products.*.validity_end'    => ['nullable', 'date'],
    ]);

    foreach ($data['products'] as $p) {
        if (!empty($p['warranty_start']) && !empty($p['warranty_end']) && $p['warranty_end'] < $p['warranty_start']) {
            return response()->json([
                'status' => false,
                'message' => 'Warranty end date must be after or equal to warranty start date.',
            ], 422);
        }
        if (!empty($p['validity_start']) && !empty($p['validity_end']) && $p['validity_end'] < $p['validity_start']) {
            return response()->json([
                'status' => false,
                'message' => 'Validity end date must be after or equal to validity start date.',
            ], 422);
        }
    }

    DB::beginTransaction();
    try {
        $totalQty = collect($data['products'])->sum('qty');

        $purchase = StockPurchase::create([
            'fund_id'     => $data['fund_id'],
            'supplier_id' => $data['supplier_id'],
            'memo_no'     => $data['memo_no'] ?? null,
            'date'        => $data['date'],
            'initial_qty' => $totalQty,
            'user_id'     => auth()->id(),
        ]);

        foreach ($data['products'] as $product) {
            $stock = Stock::create([
                'stock_purchase_id' => $purchase->id,
                'product_id'        => $product['product_id'],
                'qty'               => $product['qty'],
                'avl_qty'               => $product['qty'],
            ]);

            StockValidity::create([
                'stock_purchase_id' => $purchase->id,
                'stock_id'          => $stock->id,
                'warranty_start'    => $product['warranty_start'] ?? null,
                'warranty_end'      => $product['warranty_end'] ?? null,
                'validity_start'    => $product['validity_start'] ?? null,
                'validity_end'      => $product['validity_end'] ?? null,
            ]);
        }

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Stock saved successfully',
            'data'    => $purchase->load(['stocks.validity', 'stocks.product', 'fund', 'supplier']),
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'status'  => false,
            'message' => 'Save failed',
            'error'   => $e->getMessage(),
            'file'    => $e->getFile(),  // 👈 add
            'line'    => $e->getLine(),  // 👈 add
        ], 422);
    }
}

public function show($id)
{
    $stock = StockPurchase::with([
        'fund',
        'supplier',
        'stocks.product.country',    
        'stocks.product.category',    
        'stocks.product.subcategory', 
        'stocks.validity',            
    ])->findOrFail($id);
    return view('backend.Stock.stock_details', compact('stock'));
}

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $stock = Stock::findOrFail($id);
            StockValidity::where('stock_id', $stock->id)->delete();
            $stock->delete();
            DB::commit();
            return response()->json(['status'=>true,'message'=>'Deleted successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['status'=>false,'message'=>'Delete failed'], 422);
        }
    }


}
