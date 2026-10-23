<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->bill_no }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }

        .invoice-header h1 {
            color: #007bff;
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .invoice-header .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .invoice-details {
            margin-bottom: 30px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .detail-item {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            border-left: 3px solid #007bff;
        }

        .detail-item label {
            font-weight: 600;
            color: #333;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 5px;
        }

        .detail-item .value {
            font-size: 16px;
            color: #007bff;
            font-weight: 700;
        }

        .items-table {
            width: 100%;
            text-align: left;
            margin: 30px 0;
        }

        .items-table thead {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .items-table th {
            padding: 12px;
            border: none;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .items-table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .amount {
            text-align: right;
            font-weight: 600;
        }

        .summary-section {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .summary-row label {
            font-weight: 600;
            color: #333;
        }

        .summary-row .value {
            text-align: right;
            width: 30%;
        }

        .summary-row.total {
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
            padding: 12px 0;
            font-size: 16px;
            margin: 15px 0;
        }

        .summary-row.total label {
            font-size: 16px;
            font-weight: 700;
        }

        .summary-row.total .value {
            font-size: 16px;
            color: #007bff;
            font-weight: 700;
        }

        .due-amount {
            color: #dc3545;
            font-weight: 700;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #666;
            font-size: 12px;
        }

        .footer-text {
            margin: 5px 0;
        }

        .print-button {
            text-align: center;
            margin-top: 20px;
        }

        .print-button .btn {
            margin: 0 5px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                padding: 0;
            }

            .print-button {
                display: none;
            }

            a {
                color: inherit;
                text-decoration: none;
            }
        }

        .badge-status {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 12px;
        }

        .status-paid {
            background-color: #28a745;
            color: white;
        }

        .status-due {
            background-color: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <h1>🏥 HOSPITAL BILLING SYSTEM</h1>
            <div class="subtitle">Invoice Receipt</div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="detail-row">
                <div class="detail-item">
                    <label>Invoice Number</label>
                    <div class="value">{{ $invoice->bill_no }}</div>
                </div>
                <div class="detail-item">
                    <label>Invoice Date</label>
                    <div class="value">{{ $invoice->created_at->format('M d, Y') }}</div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-item">
                    <label>Patient Name</label>
                    <div class="value">{{ $invoice->patient->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <label>Patient Code</label>
                    <div class="value">{{ $invoice->patient_code }}</div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 50%;">Investigation</th>
                    <th style="width: 20%;">Category</th>
                    <th style="width: 25%;" class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->billItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td>
                            <span class="badge badge-info">{{ $item->main_code }}</span>
                        </td>
                        <td class="amount">${{ number_format($item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No items found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-row">
                <label>Total Amount:</label>
                <div class="value">${{ number_format($invoice->total, 2) }}</div>
            </div>

            <div class="summary-row">
                <label>Paid Amount:</label>
                <div class="value">
                    @if($invoice->paid > 0)
                        <span class="status-paid badge badge-status">
                            ${{ number_format($invoice->paid, 2) }}
                        </span>
                    @else
                        <span>$0.00</span>
                    @endif
                </div>
            </div>

            <div class="summary-row total">
                <label>Due Amount:</label>
                <div class="value">
                    @if($invoice->due > 0)
                        <span class="due-amount status-due badge badge-status">
                            ${{ number_format($invoice->due, 2) }}
                        </span>
                    @else
                        <span style="color: #28a745;">PAID</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                <strong>Thank you for your visit!</strong>
            </div>
            <div class="footer-text">
                This is an automated invoice. Please retain for your records.
            </div>
            <div class="footer-text">
                For queries, please contact the billing department.
            </div>
            <div class="footer-text" style="margin-top: 10px; font-style: italic;">
                Generated on {{ now()->format('M d, Y H:i A') }}
            </div>
        </div>

        <!-- Print Button (hidden on print) -->
        <div class="print-button">
            <button class="btn btn-primary" onclick="window.print();">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            <a href="{{ route('invoice.create') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Billing
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
