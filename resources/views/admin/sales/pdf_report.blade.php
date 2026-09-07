<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .company-details {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #00331c;
            color: white;
        }
        .total {
            line-height: normal;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="company-details">
            <h1>Sales Report</h1>
            <p><strong>NRBC Pharmacy Shop</strong></p>
            <p>Company Address: Shop 8, Road 12, Sector 10, Uttara, Dhaka-1230</p>
            <p>Email: {{ config('mail.from.address') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Total Price </th>
                    <th>Purchase Cost </th>
                    <th>Sold Price(Unit) </th>
                    <th>Profit(Unit)</th>
                    <th>Sold By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    @if ($sale->product->purchase) <!-- Ensure product has purchase information -->
                        <tr>
                            <td>{{ $sale->product->purchase->product }}</td>
                            <td>{{ $sale->product->purchase->supplier->name }}</td>
                            <td>{{ $sale->quantity }}</td>
                            <td>{{ AppSettings::get('app_currency', 'BDT') }} {{ number_format($sale->total_price, 2) }}</td>
                            <td>{{ AppSettings::get('app_currency', 'BDT') }} {{ number_format($sale->product->purchase->cost_price, 2) }}</td>
                            <td>{{ AppSettings::get('app_currency', 'BDT') }} {{ number_format(($sale->total_price / $sale->quantity ), 2) }}</td>
                            <td>{{ AppSettings::get('app_currency', 'BDT') }} {{ number_format(($sale->total_price - ($sale->product->purchase->cost_price * $sale->quantity)), 2) }}</td>
                            <td>{{ $sale->sold_by }}</td>
                            <td>{{ date_format(date_create($sale->created_at), "d M, Y") }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="total">
            <p>Total Expenses: {{ AppSettings::get('app_currency', 'BDT') }} {{ number_format($totalCost, 2) }}</p>
            <p>Total Sell: {{ AppSettings::get('app_currency', 'BDT') }} {{ number_format($totalSoldPrice, 2) }}</p>
            <p>Total Profit: {{ AppSettings::get('app_currency', 'BDT') }} {{ number_format($totalProfit, 2) }}</p>
        </div>
    </div>
</body>
</html>
