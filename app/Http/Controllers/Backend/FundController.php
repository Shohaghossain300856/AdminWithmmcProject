<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Fund; 

class FundController extends Controller
{
 public function index()
 {
    return view('backend.Fund.index');
 }

public function create()
{

    $funds = Fund::latest()->get();
    return response()->json([
        'status'  => true,          
        'message' => 'Successfully',
        'funds'   => $funds         
    ]);
}


 public function store(Request $request)
    {
      
        $request->validate([
      'fund' => 'required|string|max:255|unique:funds,fund',

        ]);

        $fund = Fund::create([
            'fund' => $request->fund,
        ]);
        return response()->json([
            'message' => 'Fund created successfully',
            'data' => $fund
        ]);
    }



public function update(Request $request, Fund $fund)
{
    // Validate the request
    $validated = $request->validate([
        'fund' => ['required', 'string', 'max:255'],
    ]);

    // Update the record
    $fund->update($validated);

    // Return response
    return response()->json([
        'status' => true,
        'message' => 'Fund updated successfully.',
        'data' => $fund,
    ], 200);
}

public function destroy(Fund $fund)
{
    $fund->delete();
    return response()->json([
        'status' => true,
        'message' => 'Fund deleted successfully.'
    ], 200);
}


}
