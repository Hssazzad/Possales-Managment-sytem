@php
use Carbon\Carbon;
@endphp

@extends('adminlte::page')

@section('title', 'Loss/Profit Details | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">Loss/Profit Details</h1>
    <span class="date-badge">
        {{ now()->format('l, d F Y') }}
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('loss-profit-details.index') }}" class="d-flex align-items-end flex-wrap" style="gap:12px;">
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Year</label>
                            <select name="year" class="form-control custom-select-styled">
                                @for($y = 2026; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex-grow-1" style="min-width:150px;">
                            <label class="filter-label">Month (Optional)</label>
                            <select name="month" class="form-control custom-select-styled">
                                <option value="">All Months</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary filter-btn">
                                Filter
                            </button>
                            <a href="{{ route('loss-profit-details.index') }}" class="btn btn-outline-secondary filter-btn">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="summary-card profit-card">
                <div class="summary-info">
                    <p class="summary-label">Total Profit</p>
                    <h4 class="summary-value">{{ number_format($totalProfit, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card loss-card">
                <div class="summary-info">
                    <p class="summary-label">Total Loss</p>
                    <h4 class="summary-value">{{ number_format($totalLoss, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card expense-card">
                <div class="summary-info">
                    <p class="summary-label">Total Expenses</p>
                    <h4 class="summary-value">{{ number_format($totalExpenses, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="summary-card net-card {{ $netProfitLoss >= 0 ? 'net-profit' : 'net-loss' }}">
                <div class="summary-info">
                    <p class="summary-label">Net {{ $netProfitLoss >= 0 ? 'Profit' : 'Loss' }}</p>
                    <h4 class="summary-value">{{ number_format(abs($netProfitLoss), 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit/Loss Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="main-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table profit-table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">SL.</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Sales Amount</th>
                                    <th>Cost</th>
                                    <th>Profit/Loss</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($profitLossDetails as $index => $detail)
                                    <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                        <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($detail['date'])->format('d M, Y') }}</td>
                                        <td><span class="invoice-badge">{{ $detail['invoice'] }}</span></td>
                                        <td>
                                            <div class="customer-info">
                                                <span class="customer-avatar">{{ strtoupper(substr($detail['customer'], 0, 1)) }}</span>
                                                <div>
                                                    <span class="customer-name">{{ $detail['customer'] }}</span>
                                                    @if($detail['customer_id'])
                                                        <small class="customer-id">ID: {{ $detail['customer_id'] }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">{{ number_format($detail['sales_amount'], 2) }}</td>
                                        <td class="fw-semibold">{{ number_format($detail['cost'], 2) }}</td>
                                        <td class="fw-semibold {{ $detail['type'] == 'profit' ? 'text-success' : 'text-danger' }}">
                                            {{ number_format(abs($detail['profit']), 2) }}
                                        </td>
                                        <td>
                                            @if($detail['payment_status'] == 'paid')
                                                <span class="status-pill status-paid">Paid</span>
                                            @elseif($detail['payment_status'] == 'partial')
                                                <span class="status-pill status-partial">Partial</span>
                                            @else
                                                <span class="status-pill status-unpaid">Unpaid</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 empty-state">
                                            <p class="text-muted mb-0">No profit/loss details found for the selected period.</p>
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
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.12) !important;
        outline: none !important;
    }
    .filter-btn {
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        padding: 6px 16px !important;
        font-weight: 500 !important;
    }
    .btn-primary { background: #6366f1 !important; border-color: #6366f1 !important; }
    .btn-primary:hover { background: #4f46e5 !important; border-color: #4f46e5 !important; }

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
    .profit-card { background: #f0fdf4; border-color: #bbf7d0; }
    .loss-card   { background: #fef2f2; border-color: #fecaca; }
    .expense-card { background: #fef3c7; border-color: #fed7aa; }
    .net-profit  { background: #f0fdf4; border-color: #bbf7d0; }
    .net-loss    { background: #fef2f2; border-color: #fecaca; }

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

    /* ========== Main Card ========== */
    .main-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* ========== Table ========== */
    .profit-table { font-size: 0.875rem; }
    .profit-table thead tr { background: #f8fafc; }
    .profit-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        font-weight: 700;
        border: none !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .profit-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 14px;
        color: #374151;
    }
    .profit-table tbody tr { transition: background 0.15s; }
    .profit-table tbody tr:hover { background: #fafbff !important; }

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
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .customer-name { display: block; font-weight: 500; color: #111827; font-size: 0.875rem; }
    .customer-id { display: block; color: #9ca3af; font-size: 0.75rem; font-family: 'DM Mono', monospace; }

    /* Status Pills */
    .status-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-paid    { background: #d1fae5; color: #065f46; }
    .status-partial { background: #fef3c7; color: #92400e; }
    .status-unpaid  { background: #fee2e2; color: #991b1b; }

    /* Empty State */
    .empty-state { color: #9ca3af; }

    /* ========== Responsive ========== */
    @media (max-width: 768px) {
        .summary-value { font-size: 1.1rem; }
        .d-flex.align-items-end.flex-wrap { flex-direction: column; align-items: stretch !important; }
        .filter-btn { width: 100%; margin-top: 10px; }
        .profit-table { font-size: 0.8rem; }
        .profit-table thead th { font-size: 0.65rem; }
    }
</style>
@endsection

@section('js')
<script>
// Set today's date as default for empty filters
document.addEventListener('DOMContentLoaded', function() {
    const yearSelect = document.querySelector('select[name="year"]');
    if (yearSelect && !yearSelect.value) {
        yearSelect.value = new Date().getFullYear();
    }
});
</script>
@endsection
