<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockPurchase;

class StockListController extends Controller
{
    public function index()
    {
        return view('backend.Stock.stockList');
    }

    public function create()
    {
        $data = StockPurchase::with(['fund','stocks.product', 'stocks.validity'])
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Stock data loaded successfully',
            'data'    => $data,
        ], 200);
    }

}
