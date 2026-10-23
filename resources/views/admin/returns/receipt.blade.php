<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Return #{{ $return->id }} - {{ $shopName ?? env('SHOP_NAME', 'BongoBay') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1f2937;
            background: #e5e7eb;
            padding: 24px 12px 60px;
        }
        .sheet {
            max-width: 520px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d1d5db;
            border-top: 6px solid #1D1D1D;
            box-shadow: 0 4px 18px rgba(0,0,0,.12);
        }
        /* ── Shop header ── */
        .shop-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 22px 14px;
            border-bottom: 2px solid #1D1D1D;
        }
        .shop-header img {
            width: 64px; height: 64px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .shop-header .shop-info h1 {
            font-size: 22px;
            letter-spacing: 1px;
            color: #111;
            margin-bottom: 2px;
        }
        .shop-header .shop-info p {
            font-size: 11px;
            color: #6b7280;
            margin: 1px 0;
        }
        /* ── Title band ── */
        .title-band {
            background: #1D1D1D;
            color: #fff;
            text-align: center;
            padding: 9px 12px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 3px;
        }
        .title-band span { color: #F59115; }
        /* ── Meta ── */
        .meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 22px;
            border-bottom: 1px dashed #d1d5db;
            font-size: 12px;
        }
        .meta .col { flex: 1; }
        .meta .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .meta .label { color: #6b7280; font-weight: 600; }
        .meta .value { font-weight: 700; color: #111; text-align: right; }
        .status {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status.completed, .status.approved { background: #d1fae5; color: #065f46; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.rejected { background: #fee2e2; color: #b91c1c; }
        /* ── Items ── */
        table.items { width: 100%; border-collapse: collapse; }
        table.items th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #4b5563;
            padding: 8px 22px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        table.items th.num, table.items td.num { text-align: right; }
        table.items th.center, table.items td.center { text-align: center; }
        table.items td {
            padding: 10px 22px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }
        table.items td.product { font-weight: 700; }
        /* ── Totals ── */
        .totals { padding: 12px 22px; border-bottom: 1px dashed #d1d5db; }
        .totals .row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
        .totals .grand {
            border-top: 2px solid #1D1D1D;
            margin-top: 8px; padding-top: 8px;
            font-size: 15px; font-weight: 800;
        }
        .totals .grand .amount { color: #b91c1c; }
        /* ── Reason / notes ── */
        .notes { padding: 12px 22px; font-size: 12px; border-bottom: 1px dashed #d1d5db; }
        .notes p { margin-bottom: 6px; }
        .notes .label { color: #6b7280; font-weight: 600; }
        /* ── Signatures ── */
        .signatures {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            padding: 34px 22px 10px;
        }
        .signatures .sig { flex: 1; text-align: center; font-size: 11px; color: #6b7280; }
        .signatures .sig .line { border-top: 1px solid #111; margin-bottom: 4px; padding-top: 0; height: 22px; }
        /* ── Footer ── */
        .footer {
            text-align: center;
            padding: 12px 22px 16px;
            font-size: 11px;
            color: #6b7280;
            background: #f9fafb;
        }
        /* ── Screen-only toolbar ── */
        .toolbar {
            max-width: 520px;
            margin: 0 auto 14px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .toolbar button, .toolbar a {
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            padding: 9px 18px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
        }
        .toolbar .primary { background: #1D1D1D; color: #F59115; border-color: #1D1D1D; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { border: none; box-shadow: none; max-width: 100%; }
            .title-band { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('returns.show', $return->id) }}">← Back</a>
        <button type="button" class="primary" onclick="window.print()">🖨 Print</button>
    </div>

    <div class="sheet">
        {{-- Shop header with logo --}}
        <div class="shop-header">
            <img src="{{ asset('images/bongobay-logo.jpg') }}" alt="Shop Logo">
            <div class="shop-info">
                <h1>{{ $shopName ?? env('SHOP_NAME', config('app.name', 'BongoBay')) }}</h1>
                @if($shopAddress ?? env('SHOP_ADDRESS'))<p>{{ $shopAddress ?? env('SHOP_ADDRESS') }}</p>@endif
                @if($shopPhone ?? env('SHOP_PHONE'))<p>Phone: {{ $shopPhone ?? env('SHOP_PHONE') }}</p>@endif
                @if(env('SHOP_EMAIL'))<p>{{ env('SHOP_EMAIL') }}</p>@endif
            </div>
        </div>

        <div class="title-band">SALES RETURN <span>RECEIPT</span></div>

        {{-- Return + sale meta --}}
        <div class="meta">
            <div class="col">
                <div class="row"><span class="label">Return #</span><span class="value">#{{ $return->id }}</span></div>
                <div class="row"><span class="label">Return Date</span><span class="value">{{ optional($return->return_date)->format('d M Y') ?? $return->created_at->format('d M Y') }}</span></div>
                <div class="row"><span class="label">Status</span><span class="value"><span class="status {{ $return->status }}">{{ ucfirst($return->status) }}</span></span></div>
            </div>
            <div class="col">
                <div class="row"><span class="label">Sale Invoice</span><span class="value">{{ $return->sale?->invoice_number ?? ('Sale #'.$return->sale_id) }}</span></div>
                <div class="row"><span class="label">Sale Date</span><span class="value">{{ $return->sale?->created_at?->format('d M Y') ?? '—' }}</span></div>
                <div class="row"><span class="label">Customer</span><span class="value">{{ $return->customer?->name ?? $return->sale?->customer_name ?? 'Walk-in Customer' }}</span></div>
                @if($return->customer?->phone ?? $return->sale?->customer_phone)
                <div class="row"><span class="label">Phone</span><span class="value">{{ $return->customer?->phone ?? $return->sale?->customer_phone }}</span></div>
                @endif
            </div>
        </div>

        {{-- Returned items --}}
        <table class="items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="center">Qty</th>
                    <th class="num">Unit Refund</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="product">{{ $return->product?->name ?? 'Unknown Product' }}</td>
                    <td class="center">{{ $return->quantity }}</td>
                    <td class="num">৳{{ number_format($return->quantity > 0 ? (float) $return->refund_amount / (int) $return->quantity : 0, 2) }}</td>
                    <td class="num"><strong>৳{{ number_format((float) $return->refund_amount, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- Refund summary --}}
        <div class="totals">
            <div class="row"><span>Refund Method</span><span><strong>{{ ucfirst($return->refund_method) }}</strong></span></div>
            <div class="row grand"><span>Total Refunded</span><span class="amount">৳{{ number_format((float) $return->refund_amount, 2) }}</span></div>
        </div>

        {{-- Reason / notes --}}
        <div class="notes">
            <p><span class="label">Return Reason:</span> {{ $return->reason }}</p>
            @if($return->notes)<p><span class="label">Notes:</span> {{ $return->notes }}</p>@endif
        </div>

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sig"><div class="line"></div>Customer Signature</div>
            <div class="sig"><div class="line"></div>Authorized Signature</div>
        </div>

        <div class="footer">
            <p>Thank you for shopping with us!</p>
            <p>Printed on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</body>
</html>
