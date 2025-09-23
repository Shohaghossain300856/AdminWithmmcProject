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
    .meta{margin:0 0 8px;font-size:12px}
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
  // optional: format numbers; keep as string if already formatted
  function nf($v){ return number_format((float)$v, 2, '.', ','); }
  // optional: convert to Bangla digits
  function bn($s){
    $en = ['0','1','2','3','4','5','6','7','8','9',',','.'];
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯',',','.'];
    return strtr((string)$s, array_combine($en,$bn));
  }
@endphp

  <div class="document-container">
    <div class="header">
      <h1>মাঠ পর্যায়ের অফিস ওয়ারি বিবরণ - বিস্তারিত</h1>
      <h2>১৬২ - স্বাস্থ্য শিক্ষা ও পরিবার কল্যাণ বিভাগ</h2>
    </div>

    <p class="meta">
      প্রাতিষ্ঠানিক কোড - , পরিচালন ইউনিট - , মাঠ অফিস - <br>
      <strong>মেমো:</strong> {{ $subcategory->memo_no }}
      &nbsp;|&nbsp; <strong>তারিখ:</strong> {{ $subcategory->date }}
      &nbsp;|&nbsp; <strong>ফান্ড:</strong> {{ $subcategory->fund->fund ?? '-' }}
      &nbsp;|&nbsp; <strong>ক্যাটেগরি:</strong> {{ $subcategory->category->name ?? '-' }}
    </p>

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

        <!-- আপনার ডাটার সারসংক্ষেপ / উপমোট (একই রোতে আপনার ফিল্ড বসালাম) -->
        <tr class="row-strong-bottom">
          <td>উপমোট - নির্বাচিত সাবক্যাটেগরি</td>
          <td class="text-right">{{ bn(nf($subcategory->budget)) }}</td>
          <td class="text-right">{{ bn(nf($subcategory->revised)) }}</td>
          <td class="text-right">{{ bn(nf($subcategory->disbursement)) }}</td>
          <td class="text-right">{{ bn(nf($subcategory->withdrawal)) }}</td>
          <td class="text-right">{{ bn(nf($subcategory->total)) }}</td>
          <td class="text-right">{{ bn(nf($subcategory->expense_pending)) }}</td>
          <td class="text-right">{{ bn(nf($subcategory->actual_expense)) }}</td>
          <td class="text-right">{{ bn(nf($subcategory->balance)) }}</td>
          <td class="text-right">{{ bn(nf($subcategory->rate)) }}</td>
          <td class="text-right">—</td>
          <td class="text-right">—</td>
        </tr>

        <!-- আপনি চাইলে নিচে আরও সেকশন দেখাতে পারেন বা বাদ দিতে পারেন -->
        <!-- উদাহরণস্বরূপ, শুধু একটি লাইন দেখাতে চাইলে বাকিগুলো মুছে দিন -->

      </tbody>
    </table>
  </div>
</body>
</html>
