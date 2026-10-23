<!DOCTYPE html>
<html>
<head>
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background: #f0ede8;
            color: #1D1D1D;
        }
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border: 1px solid #c8bdb5;
            border-top: 5px solid #F59115;
            padding: 0;
            box-shadow: 0 4px 16px rgba(70,51,46,0.15);
        }
        .header {
            background: #1D1D1D;
            text-align: center;
            padding: 20px;
            border-bottom: 3px solid #F59115;
        }
        .header h2 {
            margin: 0 0 6px 0;
            font-size: 18px;
            color: #F59115;
            letter-spacing: 2px;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
            color: #ccc;
        }
        .info {
            padding: 14px 18px;
            border-bottom: 1px dashed #c8bdb5;
            background: #faf8f5;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .info-row span:first-child {
            color: #46332E;
            font-weight: 700;
        }
        .info-row span:last-child {
            color: #1D1D1D;
            font-weight: 600;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .items-table thead tr {
            background: #46332E;
        }
        .items-table th {
            padding: 7px 10px;
            color: #F59115;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }
        .items-table th:last-child,
        .items-table td:last-child { text-align: right; }
        .items-table th:nth-child(2),
        .items-table td:nth-child(2) { text-align: center; }
        .items-table td {
            border-bottom: 1px solid #f0ede8;
            padding: 7px 10px;
            font-size: 11px;
            color: #1D1D1D;
        }
        .items-table tbody tr:hover { background: #fdf6ec; }
        .totals {
            padding: 14px 18px;
            border-top: 1px dashed #c8bdb5;
            background: #faf8f5;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
            color: #46332E;
        }
        .grand-total {
            border-top: 2px solid #F59115;
            padding-top: 8px;
            margin-top: 4px;
            font-weight: 700;
            font-size: 13px;
            color: #1D1D1D;
        }
        .grand-total span:last-child {
            color: #F59115;
        }
        .footer {
            text-align: center;
            padding: 14px 18px;
            font-size: 11px;
            background: #1D1D1D;
            color: #ccc;
            border-top: 3px solid #F59115;
        }
        .footer p { margin: 4px 0; }
        .footer .due-notice {
            color: #ff6b6b;
            font-weight: 700;
            margin-top: 8px;
            padding: 6px 10px;
            background: rgba(255,107,107,0.1);
            border: 1px solid rgba(255,107,107,0.3);
            border-radius: 2px;
        }
        @media print {
            body { padding: 0; background: white; }
            .receipt { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>SALES RECEIPT</h2>
            <p>{{ config('app.name') }}</p>
            <p>{{ config('app.address', 'Your Address') }}</p>
            <p>{{ config('app.phone', 'Your Phone') }}</p>
        </div>

        <div class="info">
            <div class="info-row">
                <span>Invoice #:</span>
                <span>{{ $sale->invoice_number }}</span>
            </div>
            <div class="info-row">
                <span>Date:</span>
                <span>{{ $sale->created_at->format('M d, Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span>Customer:</span>
                <span>{{ $sale->customer_name ?: 'Walk-in Customer' }}</span>
            </div>
            @if($sale->customer_phone)
            <div class="info-row">
                <span>Phone:</span>
                <span>{{ $sale->customer_phone }}</span>
            </div>
            @endif
            <div class="info-row">
                <span>Payment:</span>
                <span>{{ ucfirst($sale->payment_method) }}</span>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ number_format($sale->subtotal, 2) }}</span>
            </div>
            @if($sale->tax_amount > 0)
            <div class="total-row">
                <span>Tax:</span>
                <span>{{ number_format($sale->tax_amount, 2) }}</span>
            </div>
            @endif
            @if($sale->discount_amount > 0)
            <div class="total-row">
                <span>Discount:</span>
                <span>-{{ number_format($sale->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($sale->shipping_charge > 0)
            <div class="total-row">
                <span>Shipping:</span>
                <span>{{ number_format($sale->shipping_charge, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>{{ number_format($sale->total_amount, 2) }}</span>
            </div>
            @if($sale->paid_amount > 0)
            <div class="total-row" style="margin-top: 4px;">
                <span>Paid:</span>
                <span style="color: #2e7d32; font-weight: 700;">{{ number_format($sale->paid_amount, 2) }}</span>
            </div>
            @endif
            @if($sale->change_amount > 0)
            <div class="total-row">
                <span>Change:</span>
                <span>{{ number_format($sale->change_amount, 2) }}</span>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Please come again</p>
            @if($sale->due_amount > 0)
            <p class="due-notice">Due Amount: {{ number_format($sale->due_amount, 2) }}</p>
            @endif
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>