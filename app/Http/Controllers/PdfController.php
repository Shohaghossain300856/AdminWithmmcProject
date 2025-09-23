<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Backend\Subcategorie;
use App\Models\Backend\Catagory;
use App\Models\Backend\Fund;

class PdfController extends Controller
{

public function Subcategoriespdf($id, $fund_id, $categorie_id)
{
    $subcategory = Subcategorie::with([
        'fund:id,fund',
        'category:id,name'
    ])
    ->where('id', $id)
    ->where('fund_id', $fund_id)
    ->where('categorie_id', $categorie_id)
    ->firstOrFail();
    return view('Pdf.SubCatagoriesPDF', compact('subcategory'));
}

public function subcategoriesAllPdf()
{
    $subcategoryAllPdf = Subcategorie::with(['fund:id,fund', 'category:id,name'
    ])->get();

    return $subcategoryAllPdf;
    return view('Pdf.subcategoryAllPdf', compact('subcategoryAllPdf'));
}

}
