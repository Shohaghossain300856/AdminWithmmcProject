<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Supplier;

class SupplierController extends Controller
{
   public function index()
    {
        return view('backend.Supplier.index');
    }
    

    public function create()
    {
        $data = Supplier::latest()->get();

        return response()->json($data);
    }

     public function store(Request $request)
    {
        $data = $request->validate([
            'supplier' => ['required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:32'],
            'address'  => ['nullable', 'string', 'max:255'],
        ]);

        $supplier = Supplier::create($data);

        return response()->json([
            'status'   => true,
            'message'  => 'Supplier created successfully.',
            'supplier' => $supplier,
        ], 201);
    }



 public function update(Request $request, $id)
{
    $data = $request->validate([
        'supplier' => ['required', 'string', 'max:255'],
        'phone'    => ['nullable', 'string', 'max:32'],
        'address'  => ['nullable', 'string', 'max:255'],
    ]);

    $supplier = Supplier::findOrFail($id);
    $supplier->update($data);

    return response()->json([
        'status'   => true,
        'message'  => 'Supplier updated successfully.',
        'supplier' => $supplier->fresh(),
    ]);
}
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json([
            'status'  => true,
            'message' => 'Supplier deleted successfully.',
        ]);
    }
}
