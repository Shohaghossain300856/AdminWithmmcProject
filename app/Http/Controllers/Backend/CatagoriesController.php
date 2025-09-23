<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Backend\Catagory;

class CatagoriesController extends Controller
{
    public function index()
    {
        return view('backend.Catagories.index');
    }


public function create()
{

    $getCategories = Catagory::with('fund')->latest()->get();
    return response()->json([
        'status'  => true,          
        'message' => 'Successfully',
        'data'   => $getCategories         
    ]);
}

public function store(Request $request)
{
    $request->validate([
        'name'    => 'required|string|max:255|unique:catagories,name',
        'code'    => 'required|string|max:20|unique:catagories,code',
        'fund_id' => 'required|integer|exists:funds,id',
    ]);

    $catagory = Catagory::create([
        'code'    => $request->code,
        'name'    => $request->name,
        'fund_id' => $request->fund_id,
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Category created successfully.',
        'data'    => $catagory,
    ], 201);
}




public function update(Request $request, $id)
{
    $validated = $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('catagories', 'name')->ignore($id),
        ],
        'code' => [
            'required',
            'string',
            'max:20',
            Rule::unique('catagories', 'code')->ignore($id),
        ],
        'fund_id' => 'required|integer|exists:funds,id',
    ]);

    $catagory = Catagory::findOrFail($id);
    $catagory->update($validated);

    return response()->json([
        'status' => true,
        'message' => 'Category updated successfully.',
        'data' => $catagory,
    ], 200);
}


public function destroy(Catagory $catagory)
{
    $catagory->delete();

    return response()->json([
        'status' => true,
        'message' => 'Category deleted successfully.'
    ], 200);
}

}
