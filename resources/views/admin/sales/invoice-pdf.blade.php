<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        @page { margin: 22px 28px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .top {
            border-bottom: 3px solid #123866;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .top h1 {
            margin: 0 0 4px;
            font-size: 18px;
            color: #123866;
        }
        .top .muted { margin: 0; font-size: 10px; color: #64748b; }
        .badge {
            display: inline-block;
            background: #d8a821;
            color: #123866;
            font-weight: bold;
            padding: 3px 8px;
            font-size: 10px;
            margin-top: 6px;
        }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.meta td { padding: 4px 0; vertical-align: top; }
        table.meta .k { width: 28%; color: #64748b; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.items th {
            background: #123866;
            color: #fff;
            padding: 6px 8px;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
        }
        table.items th:nth-child(3),
        table.items th:nth-child(4),
        table.items td:nth-child(3),
        table.items td:nth-child(4) { text-align: right; }
        table.items td {
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 10px;
        }
        .var { font-size: 9px; color: #475569; }
        .totals { width: 100%; margin-top: 12px; }
        .totals td { padding: 3px 0; font-size: 10px; }
        .totals .k { text-align: right; color: #475569; width: 78%; padding-right: 10px; }
        .totals .v { text-align: right; font-weight: bold; }
        .grand td { border-top: 2px solid #123866; padding-top: 8px; font-size: 12px; }
        .footer {
            margin-top: 22px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $cName = $sale->customer_name ?: ($sale->customer?->name ?? 'Walk-in Customer');
        $cPhone = $sale->customer_phone ?: ($sale->customer?->phone ?? '');
    @endphp

    <div class="top">
        <h1>{{ config('app.name', 'POS') }}</h1>
        <p class="muted">{{ config('app.address', '') }} @if(config('app.phone')) | {{ config('app.phone') }} @endif</p>
        <span class="badge">TAX INVOICE — {{ $sale->invoice_number }}</span>
    </div>

    <table class="meta">
        <tr>
            <td class="k">Date</td>
            <td>{{ $sale->created_at->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td class="k">Customer</td>
            <td><strong>{{ $cName }}</strong>@if($cPhone)<br><span style="color:#475569;">{{ $cPhone }}</span>@endif</td>
        </tr>
        <tr>
            <td class="k">Payment</td>
            <td>{{ ucfirst($sale->payment_method) }} — <strong>{{ ucfirst($sale->payment_status) }}</strong></td>
        </tr>
        @if($sale->user)
        <tr>
            <td class="k">Cashier</td>
            <td>{{ $sale->user->name }}</td>
        </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:46%;">Item</th>
                <th style="width:14%; text-align:center;">Qty</th>
                <th style="width:20%;">Unit</th>
                <th style="width:20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                @php
                    $varLabel = $item->variation?->variation_value;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($varLabel)
                            <div class="var">{{ $varLabel }}</div>
                        @endif
                    </td>
                    <td style="text-align:center;">{{ $item->quantity }}</td>
                    <td>Tk {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>Tk {{ number_format((float) $item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="k">Subtotal</td>
            <td class="v">Tk {{ number_format((float) $sale->subtotal, 2) }}</td>
        </tr>
        @if((float) $sale->tax_amount > 0)
        <tr>
            <td class="k">Tax</td>
            <td class="v">Tk {{ number_format((float) $sale->tax_amount, 2) }}</td>
        </tr>
        @endif
        @if((float) $sale->discount_amount > 0)
        <tr>
            <td class="k">Discount</td>
            <td class="v">-Tk {{ number_format((float) $sale->discount_amount, 2) }}</td>
        </tr>
        @endif
        @if((float) ($sale->shipping_charge ?? 0) > 0)
        <tr>
            <td class="k">Shipping</td>
            <td class="v">Tk {{ number_format((float) $sale->shipping_charge, 2) }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td class="k"><strong>Grand total</strong></td>
            <td class="v"><strong>Tk {{ number_format((float) $sale->total_amount, 2) }}</strong></td>
        </tr>
        <tr>
            <td class="k">Paid</td>
            <td class="v">Tk {{ number_format((float) $sale->paid_amount, 2) }}</td>
        </tr>
        @if((float) $sale->due_amount > 0)
        <tr>
            <td class="k" style="color:#b91c1c;">Due</td>
            <td class="v" style="color:#b91c1c;">Tk {{ number_format((float) $sale->due_amount, 2) }}</td>
        </tr>
        @endif
        @if((float) ($sale->change_amount ?? 0) > 0)
        <tr>
            <td class="k">Change</td>
            <td class="v">Tk {{ number_format((float) $sale->change_amount, 2) }}</td>
        </tr>
        @endif
    </table>

    <div class="footer">
        System-generated invoice. Printed {{ now()->format('d M Y, H:i') }}.
    </div>
</body>
</html>
