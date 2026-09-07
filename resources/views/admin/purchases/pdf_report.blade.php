<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Report</title>
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
            background-color: #00894B;
            color: white;
        }
        .total {
            font-size: 24px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="company-details">
            <h1>Purchases Report</h1>
            <p><strong>NRBC Pharmacy Shop</strong></p>
            <p>Company Address: Shop 8, Road 12, Sector 10, Uttara, Dhaka-1230</p>
            <p>Email: {{ config('mail.from.address') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Purchase Cost</th>
                    <th>Quantity</th>
                    <th>Expire Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                    <tr>
                        <td>{{$purchase->product}}</td>
                        <td>{{$purchase->category->name}}</td>
                        <td>{{$purchase->supplier->name}}</td>
                        <td>{{AppSettings::get('app_currency', 'BDT')}} {{$purchase->cost_price}}</td>
                        <td>{{$purchase->quantity}}</td>
                        <td>{{date_format(date_create($purchase->expiry_date), "d M, Y")}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            Total Amount:
            {{ AppSettings::get('app_currency', 'BDT') }}
            {{ $purchases->sum(function($purchase) { return $purchase->cost_price * $purchase->quantity; }) }}
        </div>
    </div>
</body>
</html>
