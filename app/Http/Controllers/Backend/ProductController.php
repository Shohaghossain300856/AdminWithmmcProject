<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Backend\Product;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
class ProductController extends Controller
{
    public function index()
    {
        return view('backend.product.index');
    }


       public function create()
    {
        try {
            $products = Product::with(['country','fund', 'category', 'subcategory'])
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Products fetched successfully',
                'data'    => $products,
            ], 200);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to load products',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

public function store(Request $request)
{

    Log::info('Incoming product payload', $request->all());

    $validated = $request->validate([
        'country_id'      => ['nullable', 'integer', 'exists:countries,id'],
        'unit'            => ['nullable', 'string', 'max:50'],

        'category_id'     => ['required', 'integer', 'exists:catagories,id'], 
      'subCatagorie_id' => ['required', 'integer', 'exists:subcategories,id'],

        'type'            => ['required', 'integer', Rule::in([1, 2])],
        'Product'         => ['required', 'string', 'max:191', Rule::unique('products', 'product')],
    ]);

    try {
        $product = DB::transaction(function () use ($validated) {
            $created = Product::create([
                'category_id'     => $validated['category_id'],
                'subCatagorie_id' => $validated['subCatagorie_id'],
                'type'            => $validated['type'],
                'product'         => $validated['Product'],     
                'country_id'      => $validated['country_id'] ?? null,
                'unit'            => $validated['unit'] ?? null,
            ]);

            return $created->load(['category', 'subcategory', 'country']);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Created successfully',
            'data'    => $product,
        ], 201);

    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'status'  => false,
            'message' => 'Failed to create product',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

  public function update(Request $request, Product $product)
{
    Log::info('Incoming product update payload', [
        'product_id' => $product->id,
        'payload'    => $request->all(),
    ]);

    $validated = $request->validate([
        'country_id'      => ['nullable', 'integer', 'exists:countries,id'],
        'unit'            => ['nullable', 'string', 'max:50'],

        'category_id'     => ['required', 'integer', 'exists:catagories,id'],
        'subCatagorie_id' => ['required', 'integer', 'exists:subcategories,id'],

        'type'            => ['required', 'integer', Rule::in([1, 2])],
        'Product'         => [
            'required', 'string', 'max:191',
            Rule::unique('products', 'product')->ignore($product->id),
        ],
    ]);

    try {
        $updated = DB::transaction(function () use ($product, $validated) {
            $product->update([
                'category_id'     => $validated['category_id'],
                'subCatagorie_id' => $validated['subCatagorie_id'],
                'type'            => $validated['type'],
                'product'         => $validated['Product'],  
                'country_id'      => $validated['country_id'] ?? null,
                'unit'            => $validated['unit'] ?? null,
            ]);

            return $product->load(['category', 'subcategory', 'country']);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Updated successfully',
            'data'    => $updated,
        ], 200);

    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'status'  => false,
            'message' => 'Failed to update product',
            'error'   => $e->getMessage(),
        ], 500);
    }
}




    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Deleted successfully',
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'status'  => false,
                'message' => 'Delete failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



}
