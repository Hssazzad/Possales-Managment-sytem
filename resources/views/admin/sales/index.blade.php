@extends('adminlte::page')

@section('title', 'Sales Management | PosSales')

@section('adminlte_css')
    <!-- আমরা আগের বানানো গ্লোবাল CSS ফাইলটি এখানে লিংক করে দিচ্ছি -->
    <link rel="stylesheet" href="{{ asset('css/custom-list.css') }}">
    <style>
    /* ── Same-page print report (screen-e hidden, print-e visible) ── */
    .print-report { display: none; }
    @media print {
        .main-sidebar, .main-header, .main-footer,
        .list-container { display: none !important; }
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            min-height: auto !important;
        }
        .content { padding: 0 !important; }
        .pt-4 { padding: 0 !important; }
        .print-report { display: block !important; color: #1f2937; font-size: 12px; }
        .print-report .sheet { border-top: 6px solid #1D1D1D; }
        .print-report .shop-header {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 24px 14px;
            border-bottom: 2px solid #1D1D1D;
        }
        .print-report .shop-header img { width: 64px; height: 64px; object-fit: contain; }
        .print-report .shop-header h1 { font-size: 24px; letter-spacing: 1px; color: #111; margin: 0 0 2px; }
        .print-report .shop-header p { font-size: 11px; color: #6b7280; margin: 1px 0; }
        .print-report .title-band {
            background: #1D1D1D; color: #fff; text-align: center;
            padding: 9px 12px; font-size: 14px; font-weight: 700; letter-spacing: 3px;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .print-report .title-band span { color: #F59115; }
        .print-report .report-meta {
            display: flex; justify-content: space-between;
            padding: 10px 24px; border-bottom: 1px dashed #d1d5db; color: #4b5563;
        }
        .print-report table { width: 100%; border-collapse: collapse; }
        .print-report th {
            background: #f3f4f6; font-size: 10px; text-transform: uppercase;
            letter-spacing: .6px; color: #4b5563; padding: 8px 10px;
            text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .print-report th.num, .print-report td.num { text-align: right; white-space: nowrap; }
        .print-report th.center, .print-report td.center { text-align: center; }
        .print-report td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; }
        .print-report tbody tr:nth-child(even) { background: #f5f5f5; }
        .print-report .p-status {
            display: inline-block; padding: 2px 10px; border-radius: 20px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; white-space: nowrap;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .print-report .p-status.paid { background: #d1fae5; color: #065f46; }
        .print-report .p-status.returned { background: #fee2e2; color: #b91c1c; }
        .print-report .p-status.partial { background: #fef3c7; color: #92400e; }
        .print-report .p-status.unpaid { background: #e5e7eb; color: #374151; }
        .print-report .p-summary {
            display: flex; justify-content: flex-end;
            padding: 12px 24px; border-top: 2px solid #1D1D1D; background: #f9fafb;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .print-report .p-summary table { width: auto; min-width: 280px; }
        .print-report .p-summary td { padding: 4px 0 4px 16px; border: none; }
        .print-report .p-summary td:last-child { text-align: right; font-weight: 700; }
        .print-report .p-summary tr.grand td { font-size: 14px; border-top: 1px solid #d1d5db; padding-top: 8px; }
        .print-report .p-sign { display: flex; justify-content: space-between; gap: 30px; padding: 36px 24px 10px; }
        .print-report .p-sign div { flex: 1; text-align: center; font-size: 11px; color: #6b7280; }
        .print-report .p-sign .line { border-top: 1px solid #111; height: 22px; }
        .print-report .p-footer { text-align: center; padding: 12px 24px 16px; font-size: 11px; color: #6b7280; }
    }
    </style>
@stop

@section('content')
<div class="pt-4">
    <div class="list-container">
        
        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <!-- Search Form -->
            <form method="GET" action="{{ route('sales.index') }}" class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by invoice, customer or phone...">
                <!-- Enter চাপলেই যেন ফর্ম সাবমিট হয়, তাই কোনো আলাদা সার্চ বাটন রাখিনি, মডার্ন লুকের জন্য -->
            </form>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn-export" onclick="printSalesList()">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <a href="{{ route('returns.index') }}" class="btn-export">
                    <i class="fas fa-undo mr-1"></i> Return List
                </a>
                <a href="{{ route('pos.index') }}" class="btn-add-new">
                    <i class="fas fa-desktop mr-1"></i> Go to POS
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th class="text-center">Items</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Due</th>
                        <th class="text-center">Payment</th>
                        <th class="text-center">Status</th>
                        <th class="text-center no-print"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr class="sale-row">
                        <td class="font-weight-bold text-primary">{{ $sale->invoice_number }}</td>
                        <td>
                            <div class="text-dark">{{ $sale->created_at->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ $sale->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            @php
                                $customerName = $sale->customer_name ?: $sale->customer?->name;
                                $customerPhone = $sale->customer_phone ?: $sale->customer?->phone;
                            @endphp
                            <div class="font-weight-bold">{{ $customerName ?: 'Walk-in' }}</div>
                            @if($customerPhone)
                                <div class="text-muted small"><i class="fas fa-phone-alt mr-1"></i>{{ $customerPhone }}</div>
                            @endif
                        </td>
                        <td class="text-center font-weight-bold">{{ $sale->items->count() }}</td>
                        <td class="text-right font-weight-bold">৳{{ number_format($sale->total_amount, 2) }}</td>
                        <td class="text-right text-success font-weight-bold">৳{{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="text-right font-weight-bold {{ $sale->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            ৳{{ number_format($sale->due_amount, 2) }}
                        </td>
                        <td class="text-center">
                            @if($sale->payment_method == 'cash')
                                <span class="status-badge status-active">Cash</span>
                            @else
                                <span class="status-badge" style="background-color: #e0f2fe; color: #0284c7;">{{ ucfirst($sale->payment_method) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($sale->payment_status == 'paid')
                                <span class="status-badge status-active">Paid</span>
                            @elseif($sale->payment_status == 'returned')
                                <span class="status-badge" style="background-color:#fee2e2;color:#b91c1c;">Returned</span>
                            @elseif($sale->payment_status == 'partial')
                                <span class="status-badge" style="background-color:#fef3c7;color:#92400e;">Partial</span>
                            @else
                                <span class="status-badge status-inactive">Unpaid/Partial</span>
                            @endif
                        </td>
                        <td class="text-center no-print">
                            <!-- Inline action buttons (never clipped) -->
                            <div class="d-inline-flex" style="gap:6px;">
                                <a href="{{ route('sales.show', $sale->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('returns.create', ['sale_id' => $sale->id]) }}"
                                   class="btn btn-sm btn-outline-warning" title="Sale Return">
                                    <i class="fas fa-undo"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        title="Delete Sale" onclick="deleteSale({{ $sale->id }})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-receipt fa-3x mb-3 text-light"></i>
                                <h5>No Sales Found</h5>
                                <p>Start making sales from the POS system to see them listed here.</p>
                                <a href="{{ route('pos.index') }}" class="btn-add-new mt-2 d-inline-block">
                                    <i class="fas fa-desktop mr-1"></i> Go to POS
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Summary -->
        @if($sales && $sales->total() > 0)
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-4 pt-3" style="border-top: 1px solid #e5e7eb; gap: 1rem;">
                <div class="text-muted small">
                    Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} sales
                </div>

                @if($sales->hasPages())
                    <div class="sales-pagination">
                        {{ $sales->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>

{{-- ── Same-page print report (screen-e hidden, print-e oi style-e print hobe) ── --}}
<div class="print-report">
    <div class="sheet">
        <div class="shop-header">
            <img src="{{ asset('images/bongobay-logo.jpg') }}" alt="Shop Logo">
            <div>
                <h1>{{ $shopName ?? env('SHOP_NAME', config('app.name', 'BongoBay')) }}</h1>
                @if($shopAddress ?? env('SHOP_ADDRESS'))<p>{{ $shopAddress ?? env('SHOP_ADDRESS') }}</p>@endif
                @if($shopPhone ?? env('SHOP_PHONE'))<p>Phone: {{ $shopPhone ?? env('SHOP_PHONE') }}</p>@endif
                @if(env('SHOP_EMAIL'))<p>{{ env('SHOP_EMAIL') }}</p>@endif
            </div>
        </div>

        <div class="title-band">SALES <span>REPORT</span></div>

        <div class="report-meta">
            <span>Total Sales: <strong>{{ $printSummary['total'] ?? 0 }}</strong></span>
            <span>Printed: <strong>{{ now()->format('d M Y, h:i A') }}</strong></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="center">Items</th>
                    <th class="num">Total</th>
                    <th class="num">Paid</th>
                    <th class="num">Due</th>
                    <th class="center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($printSales ?? [] as $sale)
                @php
                    $customerName = $sale->customer_name ?: $sale->customer?->name;
                    $statusClass = in_array($sale->payment_status, ['paid', 'returned', 'partial']) ? $sale->payment_status : 'unpaid';
                @endphp
                <tr>
                    <td><strong>{{ $sale->invoice_number }}</strong></td>
                    <td style="white-space:nowrap;">{{ $sale->created_at->format('d M Y') }}<br><small style="color:#6b7280;">{{ $sale->created_at->format('h:i A') }}</small></td>
                    <td>{{ $customerName ?: 'Walk-in' }}</td>
                    <td class="center">{{ $sale->items->count() }}</td>
                    <td class="num">৳{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="num">৳{{ number_format($sale->paid_amount, 2) }}</td>
                    <td class="num">৳{{ number_format($sale->due_amount, 2) }}</td>
                    <td class="center"><span class="p-status {{ $statusClass }}">{{ ucfirst($sale->payment_status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center; padding:24px; color:#6b7280;">No sales found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-summary">
            <table>
                <tr><td>Total Sales</td><td>{{ $printSummary['total'] ?? 0 }}</td></tr>
                <tr><td>Total Amount</td><td>৳{{ number_format($printSummary['total_amount'] ?? 0, 2) }}</td></tr>
                <tr><td>Total Paid</td><td>৳{{ number_format($printSummary['paid_amount'] ?? 0, 2) }}</td></tr>
                <tr class="grand"><td>Total Due</td><td>৳{{ number_format($printSummary['due_amount'] ?? 0, 2) }}</td></tr>
            </table>
        </div>

        <div class="p-sign">
            <div><div class="line"></div>Prepared By</div>
            <div><div class="line"></div>Authorized Signature</div>
        </div>

        <div class="p-footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
// আপনার আগের deleteSale ফাংশনটি একদম ঠিক আছে, শুধু মেসেজ স্টাইলটা আরেকটু ক্লিন করেছি
function deleteSale(saleId) {
    if (confirm('⚠️ Are you sure you want to delete this sale?\n\nThis action will:\n✓ Delete the sale record\n✓ Restore stock quantities\n✓ Reverse payment records\n\nThis cannot be undone!')) {
        fetch(`/sales/${saleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Sale deleted successfully. Stock restored.');
                location.reload();
            } else {
                alert('❌ Error: ' + (data.message || 'Could not delete sale'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error deleting sale. Please try again.');
        });
    }
}

function printSalesList() {
    window.print();
}
</script>
@stop
