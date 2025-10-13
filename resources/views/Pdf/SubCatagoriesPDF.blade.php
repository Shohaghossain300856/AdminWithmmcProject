<!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8" />
  <title>মাঠ পর্যায়ের অফিস ওয়ারি বিবরণ - বিস্তারিত</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body{margin:0;background:#f7f7f7;font-family:Arial,Helvetica,sans-serif}
    .document-container{background:#fff;max-width:1200px;margin:24px auto;padding:20px;box-shadow:0 0 10px rgba(0,0,0,.1)}
    .header{text-align:center;margin-bottom:20px;border-bottom:2px solid #333;padding-bottom:15px}
    .header h1{font-size:18px;font-weight:700;margin:0 0 6px}
    .header h2{font-size:14px;margin:0 0 10px;font-weight:600}
  .meta {
   display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #333;
  margin: 0 0 8px;
}

.meta-row {

  margin-bottom: 4px;
}
.meta-row strong {
 padding: 1px 10px 7px 7px;
}
    table.main-table{width:100%;border-collapse:collapse;font-size:11px;margin-top:10px}
    .main-table th,.main-table td{border:1px solid #000;padding:4px 6px;vertical-align:middle}
    .main-table th{background:#f0f0f0;font-weight:700;text-align:left}
    .text-right{text-align:right}
    .row-section{font-weight:700;background:#fafafa}
    .row-underline{border-bottom:2px solid #000 !important}
    .row-strong-bottom{border-bottom:2px solid #423d3d !important;font-weight:700}
    @media print{body{background:#fff}.document-container{box-shadow:none;margin:0;padding:10px}}
  </style>
</head>
<body>
@php
  // helper re-declare error ঠেকাতে guard ব্যবহার
  if (!function_exists('nf')) {
      function nf($v){ return number_format((float)$v, 2, '.', ','); }
  }
  if (!function_exists('bn')) {
      function bn($s){
        $en = ['0','1','2','3','4','5','6','7','8','9',',','.'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯',',','.'];
        return strtr((string)$s, array_combine($en,$bn));
      }
  }

  // report meta নেবো প্রথম ফান্ডের প্রথম ক্যাটেগরির প্রথম আইটেম থেকে (থাকলে)
  $firstItem = null;
  if (isset($tree) && count($tree)) {
      $firstFund = $tree[0] ?? null;
      $firstCat  = $firstFund['categories'][0] ?? null;
      $firstItem = $firstCat['items'][0] ?? null;
  }

  // গ্রুপ সাম (একটি items আরে'র উপর)
  $sumCols = ['budget','revised','disbursement','withdrawal','total','expense_pending','actual_expense','balance'];
  $sumOf = function(array $items, string $key){
      $s = 0; foreach ($items as $i) { $s += (float)($i[$key] ?? 0); } return $s;
  };
  $rateAvg = function(array $items){
      // rate সাধারণত % — মোটের গড় ধরা হলো (প্রয়োজনে বদলাও)
      if (!count($items)) return 0;
      $s = 0; foreach ($items as $i) { $s += (float)($i['rate'] ?? 0); }
      return $s / count($items);
  };
@endphp

<div class="document-container">
  <div class="header">
    <h1>মাঠ পর্যায়ের অফিস ওয়ারি বিবরণ - বিস্তারিত</h1>
    <h2>১৬২ - স্বাস্থ্য শিক্ষা ও পরিবার কল্যাণ বিভাগ</h2>
  </div>

<div class="meta">
  <div class="meta-row">
    <strong>প্রাতিষ্ঠানিক কোড - 53543,</strong>
    <strong>পরিচালন ইউনিট - 4235,</strong>
    <strong>মাঠ অফিস - 54325</strong>
    <strong>অর্থনৈতিক কোড - 5343</strong>
  </div>
  
  <div class="meta-row">
    <strong>মেমো:</strong> {{ $firstItem['memo_no'] ?? '—' }} ,
    <strong>তারিখ:</strong> {{ $firstItem['date'] ?? '—' }}
  </div>
</div>



  <table class="main-table">
    <thead>
      <tr>
        <th rowspan="2">বিবরণ</th>
        <th colspan="9">অনুমোদিত</th>
        <th colspan="2">অনঅনুমোদিত</th>
      </tr>
      <tr>
        <th>বাজেট</th>
        <th>সংশোধিত</th>
        <th>বিতরণ</th>
        <th>প্রত্যাহার</th>
        <th>মোট</th>
        <th>ব্যয় <br> প্রক্রিয়াধীন</th>
        <th>প্রকৃত ব্যয়</th>
        <th>অবশিষ্ট</th>
        <th>বিতর্কিত অর্থভাবে <br> ব্যয়ের হার (%)</th>
        <th>বিবরণ</th>
        <th>প্রত্যাহার</th>
      </tr>
    </thead>
<tbody>
  @php
    // Helpers (same as yours)
    if (!function_exists('nf')) { function nf($v){ return number_format((float)$v, 2, '.', ','); } }
    if (!function_exists('bn')) {
        function bn($s){
          $en = ['0','1','2','3','4','5','6','7','8','9',',','.'];
          $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯',',','.'];
          return strtr((string)$s, array_combine($en,$bn));
        }
    }

    // Grand totals across ALL categories (optional)
    $grand = [
      'budget'=>0,'revised'=>0,'disbursement'=>0,'withdrawal'=>0,'total'=>0,
      'expense_pending'=>0,'actual_expense'=>0,'balance'=>0,'rate_sum'=>0,'rate_count'=>0
    ];
  @endphp

  @forelse($tree as $cat)
    {{-- Category header --}}
    <tr class="row-strong-bottom">
      <td colspan="12">
        (কোড: {{ $cat['category_code'] ?? '—' }}) ক্যাটেগরি: {{ $cat['category_name'] ?? '—' }}
      </td>
    </tr>

    {{-- Items (each is a sub-category row) --}}
    @foreach($cat['items'] as $row)
      <tr>
        <td>( {{ $row['code'] ?? 0 }} ) - {{ $row['sub_category'] }}</td>
        <td class="text-right">{{ bn(nf($row['budget'] ?? 0)) }}</td>
        <td class="text-right">{{ bn(nf($row['revised'] ?? 0)) }}</td>
        <td class="text-right">{{ bn(nf($row['disbursement'] ?? 0)) }}</td>
        <td class="text-right">{{ bn(nf($row['withdrawal'] ?? 0)) }}</td>
        <td class="text-right">{{ bn(nf($row['total'] ?? 0)) }}</td>
        <td class="text-right">{{ bn(nf($row['expense_pending'] ?? 0)) }}</td>
        <td class="text-right">{{ bn(nf($row['actual_expense'] ?? 0)) }}</td>
        <td class="text-right">{{ bn(nf($row['balance'] ?? 0)) }}</td>
        <td class="text-right">{{ bn(nf($row['rate'] ?? 0)) }}</td>
        <td class="text-right">—</td>
        <td class="text-right">—</td>
      </tr>
    @endforeach

    {{-- Subtotal for this Category --}}
    @php
      $s = $cat['sums'];
      // accumulate to grand totals
      $grand['budget']          += $s['budget'];
      $grand['revised']         += $s['revised'];
      $grand['disbursement']    += $s['disbursement'];
      $grand['withdrawal']      += $s['withdrawal'];
      $grand['total']           += $s['total'];
      $grand['expense_pending'] += $s['expense_pending'];
      $grand['actual_expense']  += $s['actual_expense'];
      $grand['balance']         += $s['balance'];
      if (($catCount = count($cat['items'])) > 0) {
        $grand['rate_sum']   += $s['rate_avg'] * $catCount;
        $grand['rate_count'] += $catCount;
      }
    @endphp

    <tr class="row-section row-underline">
      <td>উপমোট - ক্যাটেগরি: {{ $cat['category_name'] ?? '—' }}</td>
      <td class="text-right">{{ bn(nf($s['budget'])) }}</td>
      <td class="text-right">{{ bn(nf($s['revised'])) }}</td>
      <td class="text-right">{{ bn(nf($s['disbursement'])) }}</td>
      <td class="text-right">{{ bn(nf($s['withdrawal'])) }}</td>
      <td class="text-right">{{ bn(nf($s['total'])) }}</td>
      <td class="text-right">{{ bn(nf($s['expense_pending'])) }}</td>
      <td class="text-right">{{ bn(nf($s['actual_expense'])) }}</td>
      <td class="text-right">{{ bn(nf($s['balance'])) }}</td>
      <td class="text-right">{{ bn(nf($s['rate_avg'])) }}</td>
      <td class="text-right">—</td>
      <td class="text-right">—</td>
    </tr>

  @empty
    <tr>
      <td colspan="12" class="text-right">কোনো তথ্য পাওয়া যায়নি</td>
    </tr>
  @endforelse

  {{-- Optional: GRAND TOTAL across all categories --}}
  @php
    $grandRate = $grand['rate_count'] ? ($grand['rate_sum'] / $grand['rate_count']) : 0;
  @endphp
  @if(!empty($tree) && count($tree))
    <tr class="row-strong-bottom">
      <td>সর্বমোট</td>
      <td class="text-right">{{ bn(nf($grand['budget'])) }}</td>
      <td class="text-right">{{ bn(nf($grand['revised'])) }}</td>
      <td class="text-right">{{ bn(nf($grand['disbursement'])) }}</td>
      <td class="text-right">{{ bn(nf($grand['withdrawal'])) }}</td>
      <td class="text-right">{{ bn(nf($grand['total'])) }}</td>
      <td class="text-right">{{ bn(nf($grand['expense_pending'])) }}</td>
      <td class="text-right">{{ bn(nf($grand['actual_expense'])) }}</td>
      <td class="text-right">{{ bn(nf($grand['balance'])) }}</td>
      <td class="text-right">{{ bn(nf($grandRate)) }}</td>
      <td class="text-right">—</td>
      <td class="text-right">—</td>
    </tr>
  @endif
</tbody>

  </table>
</div>
</body>
</html>
