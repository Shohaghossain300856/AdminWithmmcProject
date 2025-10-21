<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Item;  
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    public function index()
    {
        return view('backend.Item.index');
    }

public function create(): JsonResponse
{
    $items = Item::latest()->get();

    return response()->json([
        'status'  => true,
        'message' => 'Items fetched successfully',
        'data'    => $items,
    ], 200);
}



public function store(Request $request)
{
    $data = $request->validate([
        'item' => [
            'required', 'string', 'max:255',
            Rule::unique('items', 'item'),
        ],
        'code' => [
            'nullable', 'string', 'max:100',
            Rule::unique('items', 'code'),
        ],
    ]);

    $row = DB::transaction(function () use ($data) {
        return Item::create($data);
    });

    // 3) Response
    return response()->json([
        'status'  => true,
        'message' => 'Item created successfully!',
        'data'    => $row,
    ], 201);
}

public function update(Request $request, Item $item)
{
    $data = $request->validate([
        'item' => 'required|string|max:255',
        'code' => 'nullable|string|max:255',
    ]);

    $item->update($data);

    return response()->json([
        'status' => true,
        'message' => 'Updated',
        'data' => $item,
    ]);
}

public function destroy(Item $item)
{
    $item->delete();

    return response()->json([
        'status' => true,
        'message' => 'Deleted',
    ]);
}


}
