<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Backend\Catagory;
use App\Models\Backend\Fund;
use App\Models\Stock;
use App\Models\StockPurchase;
use App\Models\StockValidity;

class StockController extends Controller
{
    public  function index()
    {
        return view('backend.Stock.create');
    }

   public function create(Request $request) 
    {

        return response()->json($request->all());
        $fundId     = $request->query('fund_id');
        $categoryId = $request->query('category_id');

        $q = Stock::with([
            'fund:id,fund',
            'category:id,name',
            'purchases:id,stock_id,date,ref_no,purchase_qty',
            'validity:id,stock_id,validity_start,validity_end,warranty_start,warranty_end'
        ]);

        if ($fundId)     $q->where('fund_id', $fundId);
        if ($categoryId) $q->where('category_id', $categoryId);
        $rows = $q->orderByDesc('id')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Stock data loaded successfully',
            'data'    => $rows,
        ]);
    }


public function store(Request $request)
{
    $data = $request->validate([
        'fund_id'        => ['required', 'integer', 'exists:funds,id'],
        'category_id'    => ['required', 'integer', 'exists:catagories,id'],
        'invoice_no'     => ['required', 'string', 'max:100'],
        'date'           => ['nullable', 'date'],
        'ref_no'         => ['nullable', 'string', 'max:100'],
        'notes'          => ['nullable', 'string'],
        'discount'       => ['nullable', 'numeric', 'min:0'],
        'status'         => ['required', 'in:draft,final'],

        'validity_start' => ['nullable', 'date'],
        'validity_end'   => ['nullable', 'date', 'after_or_equal:validity_start'],
        'warranty_start' => ['nullable', 'date'],
        'warranty_end'   => ['nullable', 'date', 'after_or_equal:warranty_start'],

        'items'                  => ['required', 'array', 'min:1'],
        'items.*.item_id'        => ['required', 'integer', 'exists:subcategories,id'],
        'items.*.qty'            => ['required', 'numeric', 'min:1'],
        'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
    ]);

    DB::beginTransaction();

    try {
        $subTotal = 0;
        $result = [];

        foreach ($data['items'] as $li) {
            $qty = (int) $li['qty']; 
            $unitPrice = round((float) $li['unit_price'], 2);
            $lineTotal = round($qty * $unitPrice, 2);
            $subTotal += $lineTotal;

            $date = $data['date'] ?? now();
            $ref  = $data['ref_no'] ?? null;

            $stock = Stock::firstOrCreate(
                [
                    'fund_id'     => $data['fund_id'],
                    'category_id' => $data['category_id'],
                    'item_id'     => (int) $li['item_id'],
                ],
                ['qty' => 0]
            );

            $purchase = StockPurchase::create([
                'stock_id'     => $stock->id,
                'date'         => $date,
                'ref_no'       => $ref,
                'purchase_qty' => $qty,
                'unit_price'   => $unitPrice,
            ]);
            $stock->increment('qty', $qty);

            StockValidity::updateOrCreate(
                ['stock_id' => $stock->id],
                [
                    'validity_start' => $data['validity_start'] ?? null,
                    'validity_end'   => $data['validity_end'] ?? null,
                    'warranty_start' => $data['warranty_start'] ?? null,
                    'warranty_end'   => $data['warranty_end'] ?? null,
                ]
            );

            $result[] = [
                'stock_id'     => $stock->id,
                'purchase_id'  => $purchase->id,
                'item_id'      => $li['item_id'],
                'qty_added'    => $qty,
                'unit_price'   => $unitPrice,
                'line_total'   => $lineTotal,
            ];
        }
        $discount = round($data['discount'] ?? 0, 2);
        $grandTotal = max(0, round($subTotal - $discount, 2));

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => '✅ Stock & Purchase successfully saved.',
            'summary' => [
                'sub_total'   => round($subTotal, 2),
                'discount'    => $discount,
                'grand_total' => $grandTotal,
            ],
            'data' => $result,
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => '❌ Error: ' . $e->getMessage(),
        ], 500);
    }
}


    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'fund_id'        => ['required','integer','exists:funds,id'],
            'category_id'    => ['required','integer','exists:catagories,id'],
            'item_id'        => ['required','integer'],
            'purchase_qty'   => ['required','integer','min:0'],

            'purchase_id'    => ['nullable','integer','exists:stock_purchases,id'],

            'date'           => ['nullable','date'],
            'ref_no'         => ['nullable','string','max:100'],
            'validity_start' => ['nullable','date'],
            'validity_end'   => ['nullable','date','after_or_equal:validity_start'],
            'warranty_start' => ['nullable','date'],
            'warranty_end'   => ['nullable','date','after_or_equal:warranty_start'],
        ]);

        $uniqueTupleRule = Rule::unique('stocks')
            ->where(fn($q) => $q
                ->where('fund_id', $data['fund_id'])
                ->where('category_id', $data['category_id'])
                ->where('item_id', $data['item_id'])
            )
            ->ignore($id);

        $request->validate([
            'tuple' => [$uniqueTupleRule],
        ], [
            'tuple.unique' => 'A stock row already exists for this Fund + Category + Item.',
        ]);

        $payload = DB::transaction(function () use ($data, $id) {
            $stock = Stock::lockForUpdate()->findOrFail($id);

            // update tuple
            $stock->update([
                'fund_id'     => (int) $data['fund_id'],
                'category_id' => (int) $data['category_id'],
                'item_id'     => (int) $data['item_id'],
            ]);

            $purchaseLookup = ['stock_id' => $stock->id];
            if (!empty($data['purchase_id'])) {
                $purchaseLookup['id'] = (int) $data['purchase_id'];
            }

            $purchase = StockPurchase::updateOrCreate(
                $purchaseLookup,
                [
                    'date'         => $data['date']        ?? now()->toDateString(),
                    'ref_no'       => $data['ref_no']      ?? null,
                    'purchase_qty' => (int) $data['purchase_qty'],
                ]
            );
            $validity = StockValidity::updateOrCreate(
                ['stock_id' => $stock->id],
                [
                    'validity_start' => $data['validity_start'] ?? null,
                    'validity_end'   => $data['validity_end']   ?? null,
                    'warranty_start' => $data['warranty_start'] ?? null,
                    'warranty_end'   => $data['warranty_end']   ?? null,
                ]
            );

            $totalQty = (int) StockPurchase::where('stock_id', $stock->id)->sum('purchase_qty');
            $stock->update(['qty' => $totalQty]);

            $stock->load([
                'fund:id,fund',
                'category:id,name',
                'purchases:id,stock_id,date,ref_no,purchase_qty',
                'validity:id,stock_id,validity_start,validity_end,warranty_start,warranty_end',
            ]);

            return compact('stock','purchase','validity');
        });

        return response()->json([
            'status'  => true,
            'message' => 'Stock updated successfully.',
            'data'    => $payload,
        ], 200);
    }
public function destroy($id)
{
    try {
        DB::transaction(function () use ($id) {
            $stock = Stock::findOrFail($id);
            StockPurchase::where('stock_id', $stock->id)->delete();
            StockValidity::where('stock_id', $stock->id)->delete();
            $stock->delete();
        });
        
        return response()->json([
            'status'  => true,
            'message' => 'Stock and related records deleted successfully.'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


}
