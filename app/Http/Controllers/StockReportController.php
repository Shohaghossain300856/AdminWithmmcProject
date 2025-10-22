<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockReportController extends Controller
{
    public function index()
    {
    return view('backend.reports.stockReport');
    }
}
