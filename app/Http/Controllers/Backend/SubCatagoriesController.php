<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Backend\Catagory;
use App\Models\Backend\Subcategorie;

class SubCatagoriesController extends Controller
{

    public function index()
    {
         return view('backend.SubCatagories.index'); 
    }


public function create()
{
    $items = Subcategorie::latest()->get();

    return response()->json([
        'status'  => true,
        'message' => 'Sub Categories loaded successfully',
        'data'    => $items,
    ], 200);
}


 public function show(Request $request)
{
    $fundId = $request->fund_id;

    $categories = Catagory::where('fund_id', $fundId)->get();

    return response()->json($categories);
}


public function store(Request $request)
{

    $data = $request->validate([
        'sub_category'      => [
            'required', 'string', 'max:255',
            Rule::unique('subcategories', 'sub_category')
        ],
        'sub_category_code' => [
            'nullable', 'string', 'max:100',
            Rule::unique('subcategories', 'sub_category_code')
        ],
    ]);

    return DB::transaction(function () use ($data) {
        $row = Subcategorie::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'Sub Category created successfully!',
            'data'    => $row,
        ], 201);
    });
}



public function update(Request $request, $id)
{
    $row = Subcategorie::findOrFail($id);
    $data = $request->validate([
        'sub_category' => [
            'required', 'string', 'max:255',
            Rule::unique('subcategories', 'sub_category')->ignore($id),
        ],
        'sub_category_code' => [
            'nullable', 'string', 'max:100',
            Rule::unique('subcategories', 'sub_category_code')->ignore($id),
        ],
    ]);
    $row->update($data);

    return response()->json([
        'status'  => true,
        'message' => 'Sub Category updated successfully!',
        'data'    => $row,
    ], 200);
}

public function destroy($id)
{
    try {
        $row = Subcategorie::findOrFail($id);
        $row->delete();

        return response()->json([
            'success' => true,
            'id'      => (int) $id,
            'message' => 'Deleted successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Delete failed: ' . $e->getMessage()
        ], 500);
    }
}

}
