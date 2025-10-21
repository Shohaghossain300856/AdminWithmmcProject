<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply of Surgical Instruments - Challan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            padding: 40px;
            background: #f5f5f5;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 16px;
            margin: 3px 0;
        }

        .address {
            text-align: left;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .reference {
            margin-bottom: 25px;
            line-height: 1.8;
            font-size: 14px;
        }

        .title {
            text-align: center;
            margin: 25px 0;
            font-size: 18px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .sl-no {
            text-align: center;
            width: 50px;
        }

        .description {
            width: 350px;
        }

        .country {
            text-align: center;
            width: 100px;
        }

        .unit {
            text-align: center;
            width: 80px;
        }

        .qty {
            text-align: center;
            width: 70px;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }
            
            .container {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mymensingh Medical College</h1>
            <p>Mymensingh-2200</p>
            <p>Supply of Surgical Instruments - Challan</p>
        </div>

        <div class="address">
            <p>Supplier,</p>
            <p><strong>{{ $stock->supplier->supplier ?? 'N/A' }}</strong></p>
            <p>{{ $stock->supplier->address ?? 'N/A' }}</p>
            <p>Phone: {{ $stock->supplier->phone ?? 'N/A' }}</p>
        </div>

        <div class="reference">
            <p>Memo No: {{ $stock->memo_no ?? 'N/A' }}, Date: {{ \Carbon\Carbon::parse($stock->date)->format('d.m.Y') }}</p>
            <p>Fund: {{ $stock->fund->fund ?? 'N/A' }}</p>
        </div>

        <div class="title">
            Supply of Products
        </div>

        <table>
            <thead>
                <tr>
                    <th class="sl-no">SL<br>No</th>
                    <th class="description">Description of Item</th>
                    <th class="country">Country<br>Origin</th>
                    <th class="unit">Unit</th>
                    <th class="qty">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stock->stocks as $index => $item)
                <tr>
                    <td class="sl-no">{{ $index + 1 }}</td>
                    <td>{{ $item->product->product ?? 'N/A' }}</td>
                    <td class="country">{{ $item->product->country->name ?? 'N/A' }}</td>
                    <td class="unit">{{ $item->product->unit ?? 'N/A' }}</td>
                    <td class="qty">{{ $item->qty ?? 0 }}</td>
                </tr>
                @endforeach

                @if($stock->stocks->isEmpty())
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">No products available</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 40px;">
            <p><strong>Total Quantity:</strong> {{ $stock->initial_qty ?? 0 }}</p>
        </div>
    </div>
</body>
</html>