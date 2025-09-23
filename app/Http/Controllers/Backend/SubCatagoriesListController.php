<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Subcategorie;
class SubCatagoriesListController extends Controller
{
    public function index()
    {
        return view('backend.Catagories.List');
    }

public function create()
{
    $getsubcatagories = Subcategorie::with('fund','category')->get();

    return response()->json([
        'status'  => true,
        'message' => 'Subcategories fetched successfully',
        'data'    => $getsubcatagories
    ], 200);
}




}
