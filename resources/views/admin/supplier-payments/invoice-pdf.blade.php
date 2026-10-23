<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Supplier Payment Receipt #{{ $payment->id }}</title>
    <style>
        @page { margin: 24px 32px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            line-height: 1.45;
        }
        .header {
            border-bottom: 3px solid #123866;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header h1 {
            margin: 0 0 4px;
            font-size: 20px;
            color: #123866;
        }
        .header .sub {
            margin: 0;
            font-size: 11px;
            color: #64748b;
        }
        .badge {
            display: inline-block;
            background: #d8a821;
            color: #123866;
            font-weight: bold;
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 3px;
        }
        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.meta td {
            padding: 6px 0;
            vertical-align: top;
        }
        table.meta td.label {
            width: 34%;
            color: #64748b;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .amount-box {
            background: #f4f7fb;
            border: 1px solid #d7dee8;
            border-radius: 6px;
            padding: 14px 16px;
            margin: 16px 0;
            text-align: center;
        }
        .amount-box .label { font-size: 11px; color: #64748b; margin-bottom: 4px; }
        .amount-box .value {
            font-size: 22px;
            font-weight: bold;
            color: #047857;
        }
        .note {
            background: #fffbeb;
            border: 1px solid #fde68a;
            padding: 10px 12px;
            border-radius: 4px;
            font-size: 11px;
            margin-top: 14px;
        }
        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $receiptNo = 'SP-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        $payDate = $payment->payment_date
            ? $payment->payment_date->format('d M Y')
            : optional($payment->created_at)->format('d M Y');
    @endphp

    <div class="header">
        <h1>{{ config('app.name', 'POS') }}</h1>
        <p class="sub">Supplier payment receipt / voucher</p>
        <p style="margin-top:10px;"><span class="badge">RECEIPT {{ $receiptNo }}</span></p>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Payment date</td>
            <td>{{ $payDate }}</td>
        </tr>
        <tr>
            <td class="label">Supplier</td>
            <td>
                <strong>{{ $payment->supplier?->name ?? '—' }}</strong>
                @if($payment->supplier?->company_name)
                    <br><span style="color:#475569;">{{ $payment->supplier->company_name }}</span>
                @endif
                @if($payment->supplier?->phone)
                    <br><span style="color:#475569;">Phone: {{ $payment->supplier->phone }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Purchase bill</td>
            <td>
                @if($payment->purchase)
                    {{ $payment->purchase->invoice_number ?? ('Purchase #' . $payment->purchase->id) }}
                @else
                    <span style="color:#64748b;">General payment (oldest dues / manual balance)</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Payment method</td>
            <td>{{ ucfirst($payment->payment_method) }}</td>
        </tr>
        <tr>
            <td class="label">Recorded by</td>
            <td>{{ $payment->user?->name ?? '—' }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="label">Amount paid</div>
        <div class="value">Tk {{ number_format((float) $payment->amount, 2) }}</div>
    </div>

    @if($payment->note)
        <div class="note">
            <strong>Note:</strong> {{ $payment->note }}
        </div>
    @endif

    <div class="footer">
        This is a system-generated receipt for supplier due payment. Printed {{ now()->format('d M Y, H:i') }}.
    </div>
</body>
</html>
