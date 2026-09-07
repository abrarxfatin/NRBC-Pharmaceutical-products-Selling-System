<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        header { text-align: center; margin-bottom: 20px; }
        .logo { width: 100px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
        .total { margin-top: 20px; font-size: 16px; font-weight: bold; }
        .sold-by { margin-top: 10px; font-size: 14px; font-weight: bold; }
        .date { margin-bottom: 10px; text-align: right; font-size: 12px; }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <h2>{{ config('app.name') }}</h2>
        <p>Company Address: Shop 8, Road 12, Sector 10, Uttara, Dhaka-1230</p>
        <p>Email: {{ config('mail.from.address') }}</p>
    </header>

    <!-- Date Section -->
    <div class="date">
        <strong>Date:</strong> {{ now()->format('d-m-Y H:i:s') }}
    </div>

    <!-- Receipt Table -->
    <h3>Receipt</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price (BDT)</th>
                <th>Total (BDT)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salesData as $sale)
            <tr>
                <td>{{ $sale->product->purchase->product }}</td> <!-- Product name from Purchase table -->
                <td>{{ $sale->quantity }}</td>
                <td>{{ $sale->product->price }}</td>
                <td>{{ $sale->total_price }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Amount -->
    <div class="total">
        <strong>Total Amount: BDT {{ $totalAmount }}</strong>
    </div>

    <!-- Sold By Section -->
    <div class="sold-by">
        <strong>Sold By:</strong> {{ Auth::user()->name }}
    </div>
</body>
</html>
