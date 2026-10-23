@extends('adminlte::page')

@section('title', 'Supplier Due Reports | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">Supplier Due Reports</h1>
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
            <div class="summary-card total-due-card">
                <div class="summary-info">
                    <p class="summary-label">Total Due</p>
                    <h4 class="summary-value">  {{ number_format($totalSupplierDue, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card suppliers-card">
                <div class="summary-info">
                    <p class="summary-label">Suppliers</p>
                    <h4 class="summary-value">{{ $supplierCount }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card average-due-card">
                <div class="summary-info">
                    <p class="summary-label">Average Due</p>
                    <h4 class="summary-value">  {{ number_format($averageDuePerSupplier, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card total-purchases-card">
                <div class="summary-info">
                    <p class="summary-label">Total Purchases</p>
                    <h4 class="summary-value">  {{ number_format($totalPurchaseAmount, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('supplier-due-reports.index') }}" class="d-flex align-items-end flex-wrap" style="gap:12px;">
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
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary filter-btn">
                                Filter
                            </button>
                            <a href="{{ route('supplier-due-reports.index') }}" class="btn btn-outline-secondary filter-btn">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Breakdown -->
    @if($supplierBreakdown->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="summary-card supplier-breakdown-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Supplier Breakdown</h5>
                    <div class="row">
                        @foreach($supplierBreakdown as $supplier)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="supplier-item">
                                <div class="supplier-header">
                                    <span class="supplier-avatar">{{ strtoupper(substr($supplier['name'], 0, 1)) }}</span>
                                    <span class="supplier-name">{{ $supplier['name'] }}</span>
                                </div>
                                <div class="supplier-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Total Due:</span>
                                        <span class="stat-value">  {{ number_format($supplier['total_due'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Total Amount:</span>
                                        <span class="stat-value">  {{ number_format($supplier['total_amount'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Paid:</span>
                                        <span class="stat-value">  {{ number_format($supplier['paid_amount'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Transactions:</span>
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

    <!-- Supplier Due Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="main-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table supplier-due-table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">SL.</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Supplier</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Due Amount</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplierDueReport as $index => $due)
                                    <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                        <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($due['date'])->format('d M, Y') }}</td>
                                        <td><span class="invoice-badge">{{ $due['invoice'] }}</span></td>
                                        <td>
                                            <div class="supplier-info">
                                                <span class="supplier-avatar">{{ strtoupper(substr($due['supplier'], 0, 1)) }}</span>
                                                <div>
                                                    <span class="supplier-name">{{ $due['supplier'] }}</span>
                                                    <small class="supplier-id">ID: {{ $due['supplier_id'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">  {{ number_format($due['total_amount'], 2) }}</td>
                                        <td class="fw-semibold text-success">  {{ number_format($due['paid_amount'], 2) }}</td>
                                        <td class="fw-semibold text-danger">  {{ number_format($due['due_amount'], 2) }}</td>
                                        <td>
                                            @if($due['payment_status'] == 'paid')
                                                <span class="status-badge status-paid">Paid</span>
                                            @elseif($due['payment_status'] == 'partial')
                                                <span class="status-badge status-partial">Partial</span>
                                            @else
                                                <span class="status-badge status-unpaid">Unpaid</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="notes-cell">
                                                @if($due['notes'])
                                                    <span class="notes-text">{{ Str::limit($due['notes'], 50) }}</span>
                                                @else
                                                    <span class="text-muted">No notes</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 empty-state">
                                            <p class="text-muted mb-0">No supplier due amounts found for the selected period.</p>
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
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245,158,11,0.12) !important;
        outline: none !important;
    }
    .filter-btn {
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        padding: 6px 16px !important;
        font-weight: 500 !important;
    }
    .btn-primary { background: #f59e0b !important; border-color: #f59e0b !important; }
    .btn-primary:hover { background: #d97706 !important; border-color: #d97706 !important; }

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
    .total-due-card        { background: #fef2f2; border-color: #fecaca; }
    .suppliers-card        { background: #fef3c7; border-color: #fed7aa; }
    .average-due-card      { background: #eff6ff; border-color: #bfdbfe; }
    .total-purchases-card  { background: #f0fdf4; border-color: #bbf7d0; }
    .supplier-breakdown-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }

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

    /* ========== Supplier Items ========== */
    .supplier-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        transition: all 0.2s;
    }
    .supplier-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .supplier-header { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        margin-bottom: 8px; 
    }
    .supplier-avatar {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .supplier-name { font-weight: 600; color: #374151; font-size: 0.9rem; }
    .supplier-stats { display: flex; flex-direction: column; gap: 4px; }
    .stat-item { display: flex; align-items: center; gap: 4px; }
    .stat-label { font-size: 0.75rem; color: #6b7280; }
    .stat-value { font-weight: 600; color: #111827; font-size: 0.85rem; font-family: 'DM Mono', monospace; }

    /* ========== Main Card ========== */
    .main-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* ========== Table ========== */
    .supplier-due-table { font-size: 0.875rem; }
    .supplier-due-table thead tr { background: #f8fafc; }
    .supplier-due-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        font-weight: 700;
        border: none !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .supplier-due-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 14px;
        color: #374151;
    }
    .supplier-due-table tbody tr { transition: background 0.15s; }
    .supplier-due-table tbody tr:hover { background: #fafbff !important; }

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
    .supplier-info .supplier-avatar {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .supplier-info .supplier-name { display: block; font-weight: 500; color: #111827; font-size: 0.875rem; }
    .supplier-info .supplier-id { display: block; color: #9ca3af; font-size: 0.75rem; font-family: 'DM Mono', monospace; }

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

    /* Notes Cell */
    .notes-cell { max-width: 200px; }
    .notes-text { font-size: 0.8rem; color: #6b7280; }

    /* Empty State */
    .empty-state { color: #9ca3af; }

    /* ========== Responsive ========== */
    @media (max-width: 768px) {
        .summary-value { font-size: 1.1rem; }
        .d-flex.align-items-end.flex-wrap { flex-direction: column; align-items: stretch !important; }
        .filter-btn { width: 100%; margin-top: 10px; }
        .supplier-due-table { font-size: 0.8rem; }
        .supplier-due-table thead th { font-size: 0.65rem; }
        .supplier-stats { gap: 2px; }
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
