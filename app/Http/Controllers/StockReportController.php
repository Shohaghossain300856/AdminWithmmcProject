<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Backend\Catagory;
use App\Models\Backend\Subcategorie;
use App\Models\Backend\Fund;
use App\Models\Backend\Product;
use App\Models\Stock;
use App\Models\StockPurchase;
use App\Models\StockValidity;
use App\Models\Country;  

class StockReportController extends Controller
{
    public function index()
    {

    return view('backend.reports.stockReport');

    }


public function create(Request $request)
{
    $fundId          = $request->input('fund_id');
    $subCategorie_id = $request->input('subCategorie_id'); 
    $startDate       = $request->input('start_date');
    $endDate         = $request->input('end_date');

    $query = DB::table('stocks')
        ->join('stock_purchases', 'stock_purchases.id', '=', 'stocks.stock_purchase_id')
        ->join('products', 'products.id', '=', 'stocks.product_id')
        ->select([
            'stocks.product_id',
            'stock_purchases.fund_id',
            'products.category_id',
            'products.subCatagorie_id', 
            'products.unit',
            DB::raw('SUM(stocks.qty) as total_qty'),
        ]);

    if (!empty($fundId)) {
        $query->where('stock_purchases.fund_id', $fundId);
    }

    if (!empty($subCategorie_id)) {
        $query->where('products.subCatagorie_id', $subCategorie_id);
    }

    if (!empty($startDate) || !empty($endDate)) {
        $start = $startDate ? Carbon::parse($startDate)->toDateString() : null;
        $end   = $endDate   ? Carbon::parse($endDate)->toDateString()   : null;

        $query->where(function ($q) use ($start, $end) {
            $applyRange = function ($qq, $column, $start, $end) {
                if ($start && $end) {
                    $qq->whereDate($column, '>=', $start)->whereDate($column, '<=', $end);
                } elseif ($start) {
                    $qq->whereDate($column, '>=', $start);
                } elseif ($end) {
                    $qq->whereDate($column, '<=', $end);
                }
            };

            $q->where(function ($qq) use ($applyRange, $start, $end) {
                $applyRange($qq, 'stock_purchases.date', $start, $end);
            })
            ->orWhere(function ($qq) use ($applyRange, $start, $end) {
                $applyRange($qq, 'stocks.created_at', $start, $end);
            });
        });
    }

    $rows = $query
        ->groupBy(
            'stocks.product_id',
            'stock_purchases.fund_id',
            'products.category_id',
            'products.subCatagorie_id',
            'products.unit'
        )
        ->orderBy('stock_purchases.fund_id')
        ->orderBy('products.category_id')
        ->orderBy('products.subCatagorie_id')
        ->orderBy('stocks.product_id')
        ->get();

    $productIds     = $rows->pluck('product_id')->unique()->filter()->values();
    $fundIds        = $rows->pluck('fund_id')->unique()->filter()->values();
    $categoryIds    = $rows->pluck('category_id')->unique()->filter()->values();
    $subCategoryIds = $rows->pluck('subCatagorie_id')->unique()->filter()->values();

    $products = $productIds->isEmpty()
        ? collect()
        : Product::whereIn('id', $productIds)
            ->select('id','product','category_id','subCatagorie_id','unit','country_id')
            ->get()->keyBy('id');

    $funds = $fundIds->isEmpty()
        ? collect()
        : Fund::whereIn('id', $fundIds)->select('id','fund')->get()->keyBy('id');

    $categories = $categoryIds->isEmpty()
        ? collect()
        : Catagory::whereIn('id', $categoryIds)->select('id','name')->get()->keyBy('id');

    $subcategories = $subCategoryIds->isEmpty()
        ? collect()
        : Subcategorie::whereIn('id', $subCategoryIds)
            ->select('id','sub_category','sub_category_code')
            ->get()->keyBy('id');

    $countryIds = $products->pluck('country_id')->unique()->filter()->values();
    $countries = $countryIds->isEmpty()
        ? collect()
        : Country::whereIn('id', $countryIds)->select('id','name')->get()->keyBy('id');

    $data = $rows->map(function ($r) use ($products, $funds, $categories, $subcategories, $countries) {
        $product    = $products->get($r->product_id);
        $fund       = $funds->get($r->fund_id);
        $category   = $categories->get($r->category_id);
        $unit       = $r->unit ?? ($product->unit ?? null);

        $subcategoryId   = $r->subCatagorie_id ?? ($product->subCatagorie_id ?? null);
        $subcategory     = $subcategoryId ? $subcategories->get($subcategoryId) : null;
        $countryId       = $product->country_id ?? null;
        $country         = $countryId ? ($countries->get($countryId)->name ?? $countryId) : null;

        return [
            'product_id'       => (int) $r->product_id,
            'fund_id'          => (int) $r->fund_id,
            'category_id'      => (int) $r->category_id,
            'subCatagorie_id'  => $subcategoryId ? (int) $subcategoryId : null,
            'product'          => $product->product ?? null,
            'fund'             => $fund->fund ?? null,
            'category'         => $category->name ?? null,

            'subcategory'      => $subcategory->sub_category ?? null,
            'sub_category'     => $subcategory->sub_category_code ?? null,

            'unit'             => $unit,
            'country'          => $country,
            'qty'              => (float) $r->total_qty,
        ];
    })->values();

    return response()->json([
        'status'  => true,
        'message' => 'Grouped stock data loaded successfully',
        'data'    => $data,
    ], 200);
}
}
