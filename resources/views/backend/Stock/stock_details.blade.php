<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Supply of Surgical Instruments – Challan</title>
  <style>
    @page { size: A4; margin: 14mm; }
    * { box-sizing: border-box; }
    body {
      font-family: 'Times New Roman', serif;
      background: #f5f7fa;
      color: #111;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      padding: 32px 12px;
    }

    .sheet {
      max-width: 900px;
      margin: 0 auto;
      background: #fff;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      border-radius: 6px;
      overflow: hidden;
    }

    /* ============ HEADER ============ */
    .header {
      background: linear-gradient(90deg, #1f5da0, #4b89da);
      color: white;
      padding: 28px 30px;
      position: relative;
    }

    .header::after {
      content: "";
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 90%;
      height: 2px;
      background: rgba(255,255,255,0.5);
    }

    .header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .header-left img {
      width: 65px;
      height: 65px;
      object-fit: cover;
      border-radius: 8px;
      background: white;
      padding: 4px;
    }

    .header-left h1 {
      font-size: 26px;
      font-weight: 700;
      margin: 0;
      line-height: 1.2;
      color: #fff;
    }

    .header-left p {
      margin: 2px 0;
      font-size: 14px;
      color: #e8ecf2;
    }

    .header-right {
      text-align: right;
    }

    .header-right h2 {
      margin: 0;
      font-size: 20px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .header-right p {
      font-size: 14px;
      color: #e8ecf2;
      margin: 4px 0 0;
    }

    /* ============ BODY ============ */
    .sheet-inner { padding: 24px 28px 32px; }

    .info-line {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      margin-bottom: 8px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      border: 1px solid #333;
    }

    th, td {
      border: 1px solid #333;
      padding: 8px;
      font-size: 13px;
    }

    th {
      background: #f1f4f9;
      text-align: center;
      font-weight: 700;
    }

    td { vertical-align: top; }

    .col-sl { width: 50px; text-align: center; }
    .col-desc { width: auto; }
    .col-country { width: 120px; text-align: center; }
    .col-unit { width: 80px; text-align: center; }
    .col-qty { width: 70px; text-align: center; }

    tfoot td {
      font-weight: bold;
      background: #f7f9fb;
    }

    .sign {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      text-align: center;
      gap: 24px;
      margin-top: 50px;
    }

    .sig-line {
      border-top: 1px solid #333;
      margin-top: 40px;
      padding-top: 6px;
      font-size: 13px;
    }

    @media print {
      body { background: #fff; padding: 0; }
      .sheet { box-shadow: none; border-radius: 0; }
    }
  </style>
</head>
<body>
  <div class="sheet">
    <header class="header">
      <div class="header-inner">
        <div class="header-left">
          <div>
            <h1>Mymensingh Medical College</h1>
            <p>Mymensingh - 2200</p>
            <p>Sup Name :{{ $stock->supplier->supplier ?? 'N/A' }}</p>
            <p>Sup Phone :{{ $stock->supplier->phone ?? 'N/A' }}</p>
          </div>
        </div>
        <div class="header-right">
          <h2>Challan</h2>
          <p>Fund :{{ $stock->fund->fund ?? 'N/A' }}</p>
          <p>Sup Address :{{ $stock->supplier->address ?? 'N/A' }}</p>
        </div>
      </div>
    </header>

    <div class="sheet-inner">
      <div class="info-line">
        <span><strong>Memo No:</strong> {{ $stock->memo_no ?? 'N/A' }}</span>
        <span><strong>Date:</strong> {{ \Carbon\Carbon::parse($stock->date)->format('d.m.Y') }}</span>
      </div>

      <table>
        <thead>
          <tr>
            <th class="col-sl">SL</th>
            <th class="col-desc">Description of Item</th>
            <th class="col-country">Country of Origin</th>
            <th class="col-unit">Unit</th>
            <th class="col-qty">Qty</th>
          </tr>
        </thead>
        <tbody>
          @foreach($stock->stocks as $index => $item)
          <tr>
            <td class="col-sl">{{ $index + 1 }}</td>
            <td>{{ $item->product->product ?? 'N/A' }}</td>
            <td class="col-country">{{ $item->product->country->name ?? 'N/A' }}</td>
            <td class="col-unit">{{ $item->product->unit ?? 'N/A' }}</td>
            <td class="col-qty">{{ $item->qty ?? 0 }}</td>
          </tr>
          @endforeach

          @if($stock->stocks->isEmpty())
          <tr>
            <td colspan="5" style="text-align:center;">No products available</td>
          </tr>
          @endif
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" style="text-align:right;">Total Quantity</td>
            <td class="col-qty">{{ $stock->initial_qty ?? 0 }}</td>
          </tr>
        </tfoot>
      </table>

      <div class="sign">
        <div><div class="sig-line">Received By</div></div>
        <div><div class="sig-line">Delivered By</div></div>
        <div><div class="sig-line">Authorized Signatory</div></div>
      </div>
    </div>
  </div>
</body>
</html>
