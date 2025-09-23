<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Catagory;
use App\Models\Backend\Subcategorie;
use Illuminate\Support\Facades\DB;

class SubCatagoriesController extends Controller
{

    public function index()
    {
         return view('backend.SubCatagories.index'); 
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
            'memo_no' => ['required','string'],
            'date' => ['required','date'],
            'fund_id' => ['required','integer'],
            'categorie_id' => ['required','integer'],

            'totals.total_budget' => ['required','numeric'],
            'totals.total_pending' => ['required','numeric'],
            'totals.total_balance' => ['required','numeric'],

            'rows' => ['required','array','min:1'],
            'rows.*.sn' => ['required','integer','min:1'],
            'rows.*.budget' => ['nullable','numeric'],
            'rows.*.revised' => ['nullable','numeric'],
            'rows.*.disbursement' => ['nullable','numeric'],
            'rows.*.withdrawal' => ['nullable','numeric'],
            'rows.*.total' => ['nullable','numeric'],
            'rows.*.expense_pending' => ['nullable','numeric'],
            'rows.*.actual_expense' => ['nullable','numeric'],
            'rows.*.balance' => ['nullable','numeric'],
            'rows.*.rate' => ['nullable','numeric'],
        ]);

        // Prepare bulk rows
        $rows = [];
        $now = now();

        foreach ($data['rows'] as $r) {
            $rows[] = [
                // header
                'memo_no'        => $data['memo_no'],
                'date'           => $data['date'],
                'fund_id'        => $data['fund_id'],
                'categorie_id'   => $data['categorie_id'],

                // grand totals (duplicated per row for simpler reporting/filters)
                'total_budget'   => $data['totals']['total_budget'],
                'total_pending'  => $data['totals']['total_pending'],
                'total_balance'  => $data['totals']['total_balance'],

                // row fields
                'sn'               => (int)($r['sn'] ?? 0),
                'budget'           => (float)($r['budget'] ?? 0),
                'revised'          => (float)($r['revised'] ?? 0),
                'disbursement'     => (float)($r['disbursement'] ?? 0),
                'withdrawal'       => (float)($r['withdrawal'] ?? 0),
                'total'            => (float)($r['total'] ?? 0),
                'expense_pending'  => (float)($r['expense_pending'] ?? 0),
                'actual_expense'   => (float)($r['actual_expense'] ?? 0),
                'balance'          => (float)($r['balance'] ?? 0),
                'rate'             => (float)($r['rate'] ?? 0),

                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        DB::transaction(function () use ($rows) {
            Subcategorie::insert($rows);
        });

        return response()->json([
            'message' => 'Saved all rows into a single table successfully',
            'count'   => count($rows),
            'memo_no' => $data['memo_no'],
        ]);
    }








}
