@extends('adminlte::page')

@section('title', 'Income Reports | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">
        <i class="fas fa-money-bill-wave mr-2 title-icon"></i> Income Reports
    </h1>
    <span class="date-badge">
        <i class="far fa-calendar-alt mr-1"></i> {{ now()->format('l, d F Y') }}
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Total Income Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card total-income-card">
                <div class="summary-icon-wrap total-income-icon-bg">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="summary-info">
                    <p class="summary-label">Total Income</p>
                    <h4 class="summary-value">{{ number_format($totalIncome, 2) }}</h4>
                    <small class="text-muted">{{ $totalSalesCount }} transactions</small>
                </div>
            </div>
        </div>

        <!-- Total Paid Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card paid-card">
                <div class="summary-icon-wrap paid-icon-bg">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="summary-info">
                    <p class="summary-label">Total Paid</p>
                    <h4 class="summary-value">{{ number_format($totalPaidAmount, 2) }}</h4>
                    <small class="text-muted">Received amount</small>
                </div>
            </div>
        </div>

        <!-- Total Due Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card due-card">
                <div class="summary-icon-wrap due-icon-bg">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="summary-info">
                    <p class="summary-label">Total Due</p>
                    <h4 class="summary-value">{{ number_format($totalDueAmount, 2) }}</h4>
                    <small class="text-muted">Outstanding amount</small>
                </div>
            </div>
        </div>

        <!-- Average Sale Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card avg-card">
                <div class="summary-icon-wrap avg-icon-bg">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="summary-info">
                    <p class="summary-label">Average Sale</p>
                    <h4 class="summary-value">{{ number_format($averageSaleAmount, 2) }}</h4>
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
                    <form method="GET" action="{{ route('income-reports.index') }}" class="d-flex align-items-end flex-wrap" style="gap:12px;">
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
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('income-reports.index') }}" class="btn btn-outline-secondary filter-btn">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Income Overview -->
    @if(count($monthlyIncome) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="summary-card monthly-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-calendar-alt mr-2"></i> Monthly Income Overview
                    </h5>
                    <div class="row">
                        @foreach($monthlyIncome as $monthly)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="monthly-item">
                                <div class="monthly-header">
                                    <i class="fas fa-chart-bar monthly-icon"></i>
                                    <span class="monthly-month">{{ $monthly['month'] }}</span>
                                </div>
                                <div class="monthly-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Income:</span>
                                        <span class="stat-value">{{ number_format($monthly['total'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Sales:</span>
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

    <!-- Payment Method Breakdown -->
    @if($paymentMethodBreakdown->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="summary-card payment-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-credit-card mr-2"></i> Payment Method Breakdown
                    </h5>
                    <div class="row">
                        @foreach($paymentMethodBreakdown as $payment)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="payment-item">
                                <div class="payment-header">
                                    <i class="fas fa-money-bill-wave payment-icon"></i>
                                    <span class="payment-method">{{ ucfirst($payment['method']) }}</span>
                                </div>
                                <div class="payment-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Amount:</span>
                                        <span class="stat-value">{{ number_format($payment['total'], 2) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Count:</span>
                                        <span class="stat-value">{{ $payment['count'] }}</span>
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

    <!-- Income Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="main-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table income-table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">SL.</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Due Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomeReport as $index => $income)
                                    <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                        <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($income['date'])->format('d M, Y') }}</td>
                                        <td><span class="invoice-badge">{{ $income['invoice'] }}</span></td>
                                        <td>
                                            <div class="customer-info">
                                                <span class="customer-avatar">{{ strtoupper(substr($income['customer'], 0, 1)) }}</span>
                                                <div>
                                                    <span class="customer-name">{{ $income['customer'] }}</span>
                                                    <small class="customer-id">ID: {{ $income['customer_id'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">{{ number_format($income['amount'], 2) }}</td>
                                        <td class="fw-semibold text-success">{{ number_format($income['paid_amount'], 2) }}</td>
                                        <td class="fw-semibold {{ $income['amount'] - $income['paid_amount'] > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ number_format($income['amount'] - $income['paid_amount'], 2) }}
                                        </td>
                                        <td>
                                            <span class="method-badge">{{ ucfirst($income['payment_method']) }}</span>
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $income['payment_status'] }}">
                                                {{ ucfirst($income['payment_status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 empty-state">
                                            <i class="fas fa-money-bill-wave fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted mb-0">No income records found for the selected period.</p>
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
    .title-icon { color: #059669; }
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
        border-color: #059669 !important;
        box-shadow: 0 0 0 3px rgba(5,150,105,0.12) !important;
        outline: none !important;
    }
    .filter-btn {
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        padding: 6px 16px !important;
        font-weight: 500 !important;
    }
    .btn-primary { background: #059669 !important; border-color: #059669 !important; }
    .btn-primary:hover { background: #047857 !important; border-color: #047857 !important; }

    /* ========== Summary Cards ========== */
    .summary-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        border-radius: 14px;
        border: 1px solid transparent;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
    }
    .total-income-card { background: #ecfdf5; border-color: #a7f3d0; }
    .paid-card         { background: #f0fdf4; border-color: #bbf7d0; }
    .due-card          { background: #fef3c7; border-color: #fed7aa; }
    .avg-card          { background: #eff6ff; border-color: #bfdbfe; }
    .monthly-card      { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
    .payment-card      { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }

    .summary-icon-wrap {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .total-income-icon-bg { background: #10b981; color: #065f46; }
    .paid-icon-bg          { background: #34d399; color: #14532d; }
    .due-icon-bg           { background: #fbbf24; color: #92400e; }
    .avg-icon-bg           { background: #60a5fa; color: #1e40af; }

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

    /* ========== Monthly/Payment Items ========== */
    .monthly-item, .payment-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        transition: all 0.2s;
    }
    .monthly-item:hover, .payment-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .monthly-header, .payment-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .monthly-icon, .payment-icon { color: #059669; font-size: 0.9rem; }
    .monthly-month, .payment-method { font-weight: 600; color: #374151; font-size: 0.9rem; }
    .monthly-stats, .payment-stats { display: flex; gap: 16px; }
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
    .income-table { font-size: 0.875rem; }
    .income-table thead tr { background: #f8fafc; }
    .income-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        font-weight: 700;
        border: none !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .income-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 14px;
        color: #374151;
    }
    .income-table tbody tr { transition: background 0.15s; }
    .income-table tbody tr:hover { background: #fafbff !important; }

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

    /* Customer */
    .customer-info { display: flex; align-items: center; gap: 10px; }
    .customer-avatar {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #059669, #047857);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .customer-name { display: block; font-weight: 500; color: #111827; font-size: 0.875rem; }
    .customer-id { display: block; color: #9ca3af; font-size: 0.75rem; font-family: 'DM Mono', monospace; }

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
        text-transform: capitalize;
    }
    .status-badge.paid    { background: #d1fae5; color: #065f46; }
    .status-badge.partial { background: #fef3c7; color: #92400e; }
    .status-badge.unpaid  { background: #fee2e2; color: #991b1b; }

    /* Empty State */
    .empty-state { color: #9ca3af; }
    .empty-state i { display: block; margin: 0 auto 10px; }

    /* ========== Responsive ========== */
    @media (max-width: 768px) {
        .summary-value { font-size: 1.1rem; }
        .d-flex.align-items-end.flex-wrap { flex-direction: column; align-items: stretch !important; }
        .filter-btn { width: 100%; margin-top: 10px; }
        .income-table { font-size: 0.8rem; }
        .income-table thead th { font-size: 0.65rem; }
        .monthly-stats, .payment-stats { flex-direction: column; gap: 4px; }
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
