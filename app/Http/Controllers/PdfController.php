<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Backend\Subcategorie;
use App\Models\Backend\Catagory;
use App\Models\Backend\Fund;
use Illuminate\Support\Facades\DB;

class PdfController extends Controller
{

public function Subcategoriespdf()
{
    // 🔹 সব সাব-ক্যাটেগরির রেকর্ড লোড করি (সাথে ফান্ড ও ক্যাটেগরি সম্পর্ক)
    $rows = Subcategorie::with([
        'fund:id,fund',
        'category:id,fund_id,code,name'
    ])
    ->orderBy('categorie_id') // ক্যাটেগরি অনুযায়ী সাজাই
    ->orderBy('sn')           // আপনার সিরিয়াল/অর্ডার কলাম
    ->get();

    // 🔹 ক্যাটেগরি-আইডি দিয়ে গ্রুপ করে ক্যাটেগরি-ভিত্তিক ট্রি বানাই
    $tree = $rows->groupBy('categorie_id')->map(function ($byCat, $catId) {
        $first = $byCat->first(); // এই গ্রুপের প্রথম রেকর্ড (ক্যাটেগরির নাম/কোড নেয়ার জন্য)

        // 🔹 আইটেমগুলো (প্রতিটি সাব-ক্যাটেগরির এন্ট্রি)
        $items = $byCat->map(function ($m) {
            return [
                'id'              => $m->id,
                'memo_no'         => $m->memo_no,
                'date'            => $m->date,
                'fund_id'         => $m->fund_id,
                'code'            => $m->code,          // সাব-ক্যাটেগরির কোড
                'sub_category'    => $m->sub_category,  // সাব-ক্যাটেগরির নাম
                'categorie_id'    => $m->categorie_id,

                // নিচের ফিল্ডগুলো সংখ্যাসূচক; ক্যালকুলেশন সহজ করতে float কাস্ট করলাম
                'total_budget'    => (float) $m->total_budget,
                'total_pending'   => (float) $m->total_pending,
                'total_balance'   => (float) $m->total_balance,
                'sn'              => $m->sn,
                'budget'          => (float) $m->budget,
                'revised'         => (float) $m->revised,
                'disbursement'    => (float) $m->disbursement,
                'withdrawal'      => (float) $m->withdrawal,
                'total'           => (float) $m->total,
                'expense_pending' => (float) $m->expense_pending,
                'actual_expense'  => (float) $m->actual_expense,
                'balance'         => (float) $m->balance,
                'rate'            => (float) $m->rate,

                // রিলেশনাল তথ্য (ফান্ড/ক্যাটেগরি)
                'fund'            => [
                    'id'   => $m->fund->id ?? $m->fund_id,
                    'fund' => $m->fund->fund ?? null,
                ],
                'category'        => [
                    'id'      => $m->category->id ?? $m->categorie_id,
                    'fund_id' => $m->category->fund_id ?? $m->fund_id,
                    'code'    => $m->category->code ?? null,
                    'name'    => $m->category->name ?? null,
                ],
            ];
        })->values();

        // 🔹 এই ক্যাটেগরির উপমোট/সামগুলো আগে থেকেই বের করে রাখি (Blade-এ কাজ সহজ হবে)
        $sums = [
            'budget'          => $byCat->sum('budget'),
            'revised'         => $byCat->sum('revised'),
            'disbursement'    => $byCat->sum('disbursement'),
            'withdrawal'      => $byCat->sum('withdrawal'),
            'total'           => $byCat->sum('total'),
            'expense_pending' => $byCat->sum('expense_pending'),
            'actual_expense'  => $byCat->sum('actual_expense'),
            'balance'         => $byCat->sum('balance'),
            'rate_avg'        => (float) ($byCat->avg('rate') ?? 0), // রেট গড় হিসেবে
        ];

        return [
            'category_id'   => (int) $catId,
            'category_code' => $first->category->code ?? '',
            'category_name' => $first->category->name ?? '',
            'items'         => $items->all(), // এই ক্যাটেগরির সব সাব-ক্যাটেগরি-রো
            'sums'          => $sums,         // এই ক্যাটেগরির উপমোট
        ];
    })->values();

    // 🔹 Category-first ট্রি Blade-এ পাঠাই
    return view('Pdf.SubCatagoriesPDF', compact('tree'));
}


}