@extends('adminlte::page')

@section('title', 'Transaction History Reports | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">Transaction History Reports</h1>
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
            <div class="summary-card total-transactions-card">
                <div class="summary-info">
                    <p class="summary-label">Total Transactions</p>
                    <h4 class="summary-value">{{ $totalTransactions }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card total-amount-card">
                <div class="summary-info">
                    <p class="summary-label">Total Amount</p>
                    <h4 class="summary-value">{{ number_format($totalAmount, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card total-paid-card">
                <div class="summary-info">
                    <p class="summary-label">Total Paid</p>
                    <h4 class="summary-value">{{ number_format($totalPaid, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card total-due-card">
                <div class="summary-info">
                    <p class="summary-label">Total Due</p>
                    <h4 class="summary-value">{{ number_format($totalDue, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('transaction-history-reports.index') }}" class="d-flex align-items-end flex-wrap" style="gap:12px;">
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control custom-select-styled" value="{{ request('start_date') }}">
                        </div>
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">End Date</label>
                            <input type="date" name="end_date" class="form-control custom-select-styled" value="{{ request('end_date') }}">
                        </div>
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary filter-btn">
                                Filter
                            </button>
                            <a href="{{ route('transaction-history-reports.index') }}" class="btn btn-outline-secondary filter-btn">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Type Breakdown -->
    @if($typeBreakdown->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="summary-card type-breakdown-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Transaction Type Breakdown</h5>
                    <div class="row">
                        @foreach($typeBreakdown as $type)
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="type-item">
                                <div class="type-header">
                                    <span class="type-badge {{ strtolower($type['type']) }}">{{ $type['type'] }}</span>
                                    <span class="type-count">{{ $type['count'] }}</span>
                                </div>
                                <div class="type-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Total:</span>
                                        <span class="stat-value">{{ number_format($type['total_amount'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Paid:</span>
                                        <span class="stat-value">{{ number_format($type['paid_amount'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Due:</span>
                                        <span class="stat-value">{{ number_format($type['due_amount'], 2) }}</span>
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

    <!-- Status Breakdown -->
    @if($statusBreakdown->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="summary-card status-breakdown-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Payment Status Breakdown</h5>
                    <div class="row">
                        @foreach($statusBreakdown as $status)
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="status-item">
                                <div class="status-header">
                                    <span class="status-badge {{ $status['status'] }}">{{ ucfirst($status['status']) }}</span>
                                    <span class="status-count">{{ $status['count'] }}</span>
                                </div>
                                <div class="status-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Total Amount:</span>
                                        <span class="stat-value">{{ number_format($status['total_amount'], 2) }}</span>
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

    <!-- Transaction History Table -->
    <div class="row">
        <div class="col-12">
            <div class="main-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table transaction-table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">SL.</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Party</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Due Amount</th>
                                    <th>Status</th>
                                    <th>Payment Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $index => $transaction)
                                    <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                        <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('d M, Y') }}</td>
                                        <td>
                                            <span class="type-badge {{ strtolower($transaction['type']) }}">
                                                {{ $transaction['type'] }}
                                            </span>
                                        </td>
                                        <td><span class="reference-badge">{{ $transaction['reference'] }}</span></td>
                                        <td>
                                            <div class="party-info">
                                                <span class="party-avatar">{{ strtoupper(substr($transaction['party'], 0, 1)) }}</span>
                                                <div>
                                                    <span class="party-name">{{ $transaction['party'] }}</span>
                                                    @if($transaction['party_id'])
                                                        <small class="party-id">ID: {{ $transaction['party_id'] }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">{{ number_format($transaction['total_amount'], 2) }}</td>
                                        <td class="fw-semibold text-success">{{ number_format($transaction['paid_amount'], 2) }}</td>
                                        <td class="fw-semibold {{ $transaction['due_amount'] > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ number_format($transaction['due_amount'], 2) }}
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $transaction['payment_status'] }}">
                                                {{ ucfirst($transaction['payment_status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="method-badge">{{ ucfirst($transaction['payment_method']) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 empty-state">
                                            <p class="text-muted mb-0">No transactions found for the selected period.</p>
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
        border-color: #8b5cf6 !important;
        box-shadow: 0 0 0 3px rgba(139,92,246,0.12) !important;
        outline: none !important;
    }
    .filter-btn {
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        padding: 6px 16px !important;
        font-weight: 500 !important;
    }
    .btn-primary { background: #8b5cf6 !important; border-color: #8b5cf6 !important; }
    .btn-primary:hover { background: #7c3aed !important; border-color: #7c3aed !important; }

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
    .total-transactions-card { background: #eff6ff; border-color: #bfdbfe; }
    .total-amount-card       { background: #f0fdf4; border-color: #bbf7d0; }
    .total-paid-card         { background: #fef3c7; border-color: #fed7aa; }
    .total-due-card          { background: #fef2f2; border-color: #fecaca; }
    .type-breakdown-card     { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
    .status-breakdown-card   { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }

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

    /* ========== Type/Status Items ========== */
    .type-item, .status-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        transition: all 0.2s;
    }
    .type-item:hover, .status-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .type-header, .status-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 8px; 
    }
    .type-count, .status-count {
        font-size: 0.75rem;
        font-weight: 600;
        background: #f3f4f6;
        color: #6b7280;
        padding: 2px 6px;
        border-radius: 10px;
        display: inline-block;
    }
    .type-stats, .status-stats { display: flex; flex-direction: column; gap: 4px; }
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
    .transaction-table { font-size: 0.875rem; }
    .transaction-table thead tr { background: #f8fafc; }
    .transaction-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        font-weight: 700;
        border: none !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .transaction-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 14px;
        color: #374151;
    }
    .transaction-table tbody tr { transition: background 0.15s; }
    .transaction-table tbody tr:hover { background: #fafbff !important; }

    /* Row animation */
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .table-row-animate {
        animation: fadeSlideIn 0.3s ease both;
    }

    /* Type Badge */
    .type-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .type-badge.sale      { background: #dcfce7; color: #065f46; }
    .type-badge.purchase  { background: #dbeafe; color: #1e40af; }
    .type-badge.expense   { background: #fef3c7; color: #92400e; }
    .type-badge.return    { background: #fee2e2; color: #991b1b; }

    /* Reference Badge */
    .reference-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.78rem;
        font-weight: 500;
        background: #f3f4f6;
        color: #374151;
        padding: 3px 8px;
        border-radius: 6px;
    }

    /* Party Info */
    .party-info { display: flex; align-items: center; gap: 10px; }
    .party-avatar {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .party-name { display: block; font-weight: 500; color: #111827; font-size: 0.875rem; }
    .party-id { display: block; color: #9ca3af; font-size: 0.75rem; font-family: 'DM Mono', monospace; }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-badge.paid      { background: #d1fae5; color: #065f46; }
    .status-badge.partial    { background: #fef3c7; color: #92400e; }
    .status-badge.unpaid     { background: #fee2e2; color: #991b1b; }
    .status-badge.refunded   { background: #e0e7ff; color: #3730a3; }

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

    /* Empty State */
    .empty-state { color: #9ca3af; }

    /* ========== Responsive ========== */
    @media (max-width: 768px) {
        .summary-value { font-size: 1.1rem; }
        .d-flex.align-items-end.flex-wrap { flex-direction: column; align-items: stretch !important; }
        .filter-btn { width: 100%; margin-top: 10px; }
        .transaction-table { font-size: 0.8rem; }
        .transaction-table thead th { font-size: 0.65rem; }
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
