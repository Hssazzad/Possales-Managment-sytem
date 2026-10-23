@extends('adminlte::page')

@section('title', 'Purchase Reports | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">Purchase Reports</h1>
    <span class="date-badge">
        {{ now()->format('l, d F Y') }}
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="summary-card total-purchase-card">
                <div class="summary-info">
                    <p class="summary-label">Total Purchase</p>
                    <h4 class="summary-value">{{ number_format($totalPurchases, 2) }}</h4>
                    <small class="text-muted">{{ $purchaseCount }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card total-paid-card">
                <div class="summary-info">
                    <p class="summary-label">Total Paid</p>
                    <h4 class="summary-value">{{ number_format($totalPaid, 2) }}</h4>
                    <small class="text-muted">Amount paid</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card total-due-card">
                <div class="summary-info">
                    <p class="summary-label">Total Due</p>
                    <h4 class="summary-value">{{ number_format($totalDue, 2) }}</h4>
                    <small class="text-muted">Outstanding amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card avg-purchase-card">
                <div class="summary-info">
                    <p class="summary-label">Average Purchase</p>
                    <h4 class="summary-value">{{ number_format($purchaseCount > 0 ? $totalPurchases / $purchaseCount : 0, 2) }}</h4>
                    <small class="text-muted">Per transaction</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('purchase-reports.index') }}" class="d-flex align-items-end flex-wrap" style="gap:12px;">
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control custom-select-styled" value="{{ request('start_date') }}">
                        </div>
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">End Date</label>
                            <input type="date" name="end_date" class="form-control custom-select-styled" value="{{ request('end_date') }}">
                        </div>
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Supplier</label>
                            <select name="supplier" class="form-control custom-select-styled">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Payment Status</label>
                            <select name="payment_status" class="form-control custom-select-styled">
                                <option value="">All Status</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary filter-btn">
                                Filter
                            </button>
                            <a href="{{ route('purchase-reports.index') }}" class="btn btn-outline-secondary filter-btn">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Purchase Overview -->
    @if(count($monthlyPurchases) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="summary-card monthly-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Monthly Purchase Overview</h5>
                    <div class="row">
                        @foreach($monthlyPurchases as $monthly)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="monthly-item">
                                <div class="monthly-header">
                                    <span class="monthly-icon">P</span>
                                    <span class="monthly-month">{{ $monthly['month'] }}</span>
                                </div>
                                <div class="monthly-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Amount:</span>
                                        <span class="stat-value">{{ number_format($monthly['total'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Count:</span>
                                        <span class="stat-value">{{ $monthly['count'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Supplier Breakdown -->
    @if($supplierBreakdown->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="summary-card supplier-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Supplier Breakdown</h5>
                    <div class="row">
                        @foreach($supplierBreakdown as $supplier)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="supplier-item">
                                <div class="supplier-header">
                                    <span class="supplier-avatar">{{ strtoupper(substr($supplier['supplier_name'], 0, 1)) }}</span>
                                    <span class="supplier-name">{{ $supplier['supplier_name'] }}</span>
                                </div>
                                <div class="supplier-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Total:</span>
                                        <span class="stat-value">{{ number_format($supplier['total_amount'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Paid:</span>
                                        <span class="stat-value">{{ number_format($supplier['paid_amount'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Due:</span>
                                        <span class="stat-value">{{ number_format($supplier['due_amount'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Count:</span>
                                        <span class="stat-value">{{ $supplier['count'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Purchase Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="main-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table purchase-table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">SL.</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Supplier</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Due Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $index => $purchase)
                                    <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                        <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('d M, Y') }}</td>
                                        <td><span class="invoice-badge">{{ $purchase->invoice_number }}</span></td>
                                        <td>
                                            <div class="supplier-info">
                                                <span class="supplier-avatar">{{ strtoupper(substr($purchase->supplier->name ?? 'Unknown', 0, 1)) }}</span>
                                                <div>
                                                    <span class="supplier-name">{{ $purchase->supplier->name ?? 'Unknown Supplier' }}</span>
                                                    <small class="supplier-id">ID: {{ $purchase->supplier_id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">{{ number_format($purchase->total_amount, 2) }}</td>
                                        <td class="fw-semibold text-success">{{ number_format($purchase->paid_amount, 2) }}</td>
                                        <td class="fw-semibold {{ $purchase->total_amount - $purchase->paid_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}
                                        </td>
                                        <td>
                                            <span class="method-badge">{{ ucfirst($purchase->payment_method ?? 'N/A') }}</span>
                                        </td>
                                        <td>
                                            @if($purchase->payment_status == 'paid')
                                                <span class="status-badge status-paid">Paid</span>
                                            @elseif($purchase->payment_status == 'partial')
                                                <span class="status-badge status-partial">Partial</span>
                                            @else
                                                <span class="status-badge status-unpaid">Unpaid</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 empty-state">
                                            <p class="text-muted mb-0">No purchases found for the selected period.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    /* ========== Base ========== */
    body, .content-wrapper {
        background: #f0f2f5 !important;
        font-family: 'DM Sans', sans-serif !important;
    }

    /* ========== Header ========== */
    .page-title { color: #111827; font-size: 1.25rem; }
    .date-badge {
        background: #f3f4f6;
        color: #6b7280;
        font-size: 0.8rem;
        padding: 5px 12px;
        border-radius: 20px;
        font-family: 'DM Mono', monospace;
    }

    /* ========== Filter Card ========== */
    .filter-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .filter-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        display: block;
    }
    .custom-select-styled {
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        font-size: 0.875rem !important;
        color: #374151 !important;
        background-color: #f9fafb !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .custom-select-styled:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12) !important;
        outline: none !important;
    }
    .filter-btn {
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        padding: 6px 16px !important;
        font-weight: 500 !important;
    }
    .btn-primary { background: #3b82f6 !important; border-color: #3b82f6 !important; }
    .btn-primary:hover { background: #2563eb !important; border-color: #2563eb !important; }

    /* ========== Summary Cards ========== */
    .summary-card {
        padding: 18px 20px;
        border-radius: 14px;
        border: 1px solid transparent;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
    }
    .total-purchase-card { background: #eff6ff; border-color: #bfdbfe; }
    .total-paid-card     { background: #f0fdf4; border-color: #bbf7d0; }
    .total-due-card      { background: #fef3c7; border-color: #fed7aa; }
    .avg-purchase-card   { background: #f8fafc; border-color: #e2e8f0; }
    .monthly-card        { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
    .supplier-card       { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }

    .summary-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #6b7280;
        margin-bottom: 3px;
    }
    .summary-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
        font-family: 'DM Mono', monospace;
    }

    /* ========== Monthly/Supplier Items ========== */
    .monthly-item, .supplier-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        transition: all 0.2s;
    }
    .monthly-item:hover, .supplier-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .monthly-header, .supplier-header { 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        margin-bottom: 8px; 
    }
    .monthly-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .monthly-month, .supplier-name { font-weight: 600; color: #374151; font-size: 0.9rem; }
    .monthly-stats, .supplier-stats { display: flex; gap: 16px; flex-wrap: wrap; }
    .stat-item { display: flex; align-items: center; gap: 4px; }
    .stat-label { font-size: 0.75rem; color: #6b7280; }
    .stat-value { font-weight: 600; color: #111827; font-size: 0.85rem; font-family: 'DM Mono', monospace; }

    /* ========== Supplier Avatar ========== */
    .supplier-avatar {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    /* ========== Main Card ========== */
    .main-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* ========== Table ========== */
    .purchase-table { font-size: 0.875rem; }
    .purchase-table thead tr { background: #f8fafc; }
    .purchase-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        font-weight: 700;
        border: none !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .purchase-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 14px;
        color: #374151;
    }
    .purchase-table tbody tr { transition: background 0.15s; }
    .purchase-table tbody tr:hover { background: #fafbff !important; }

    /* Row animation */
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .table-row-animate {
        animation: fadeSlideIn 0.3s ease both;
    }

    /* Invoice Badge */
    .invoice-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.78rem;
        font-weight: 500;
        background: #f3f4f6;
        color: #374151;
        padding: 3px 8px;
        border-radius: 6px;
    }

    /* Supplier Info */
    .supplier-info { display: flex; align-items: center; gap: 10px; }
    .supplier-name { display: block; font-weight: 500; color: #111827; font-size: 0.875rem; }
    .supplier-id { display: block; color: #9ca3af; font-size: 0.75rem; font-family: 'DM Mono', monospace; }

    /* Method Badge */
    .method-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.75rem;
        font-weight: 500;
        background: #f3f4f6;
        color: #374151;
        padding: 3px 8px;
        border-radius: 6px;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-badge.paid    { background: #d1fae5; color: #065f46; }
    .status-badge.partial { background: #fef3c7; color: #92400e; }
    .status-badge.unpaid  { background: #fee2e2; color: #991b1b; }

    /* Empty State */
    .empty-state { color: #9ca3af; }

    /* ========== Responsive ========== */
    @media (max-width: 768px) {
        .summary-value { font-size: 1.1rem; }
        .d-flex.align-items-end.flex-wrap { flex-direction: column; align-items: stretch !important; }
        .filter-btn { width: 100%; margin-top: 10px; }
        .purchase-table { font-size: 0.8rem; }
        .purchase-table thead th { font-size: 0.65rem; }
        .monthly-stats, .supplier-stats { flex-direction: column; gap: 4px; }
    }
</style>
@endsection

@section('js')
<script>
// Set today's date as default for empty filters
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    if (!startDate.value && !endDate.value) {
        const today = new Date().toISOString().split('T')[0];
        startDate.value = today;
        endDate.value = today;
    }
});
</script>
@endsection
