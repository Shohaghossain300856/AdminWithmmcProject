<?php

namespace App\Http\Controllers\Backend;
use Illuminate\Support\Facades\Log;
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
    $getsubcatagories = Subcategorie::with(['fund','category'])->get();

    return response()->json([
        'status'  => true,
        'message' => 'Subcategories fetched successfully',
        'data'    => $getsubcatagories
    ], 200);
}


public function update(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string|in:active,panding'
    ]);

    $subCategory = Subcategorie::find($id);
    if (!$subCategory) {
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }
    $affected = Subcategorie::whereKey($id)->update(['status' => $request->status]);

    $subCategory->refresh();

    return response()->json([
        'success'  => (bool) $affected,
        'message'  => $affected ? 'Status updated successfully!' : 'Nothing changed',
        'changed'  => $affected > 0,
        'data'     => $subCategory,
    ]);
}





}
