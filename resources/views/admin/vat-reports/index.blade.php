@extends('adminlte::page')

@section('title', 'VAT Reports | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">
        <i class="fas fa-file-invoice-dollar mr-2 title-icon"></i> VAT Reports
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
        <!-- Output VAT Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card output-vat-card">
                <div class="summary-icon-wrap output-vat-icon-bg">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="summary-info">
                    <p class="summary-label">Output VAT</p>
                    <h4 class="summary-value">{{ number_format($totalVat, 2) }}</h4>
                    <small class="text-muted">{{ $totalSalesWithVat }} transactions</small>
                </div>
            </div>
        </div>

        <!-- Input VAT Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card input-vat-card">
                <div class="summary-icon-wrap input-vat-icon-bg">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="summary-info">
                    <p class="summary-label">Input VAT</p>
                    <h4 class="summary-value">{{ number_format($purchaseVat['total_vat'] + $expenseVat['total_vat'], 2) }}</h4>
                    <small class="text-muted">{{ $purchaseVat['count'] + $expenseVat['count'] }} transactions</small>
                </div>
            </div>
        </div>

        <!-- Net VAT Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card net-vat-card">
                <div class="summary-icon-wrap net-vat-icon-bg">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="summary-info">
                    <p class="summary-label">Net VAT</p>
                    <h4 class="summary-value {{ $netVat >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format(abs($netVat), 2) }}
                    </h4>
                    <small class="{{ $netVat >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $netVat >= 0 ? 'Payable' : 'Receivable' }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Taxable Amount Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card taxable-card">
                <div class="summary-icon-wrap taxable-icon-bg">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="summary-info">
                    <p class="summary-label">Taxable Amount</p>
                    <h4 class="summary-value">{{ number_format($totalTaxableAmount, 2) }}</h4>
                    <small class="text-muted">Net of VAT</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('vat-reports.index') }}" class="d-flex align-items-end flex-wrap" style="gap:12px;">
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
                            <a href="{{ route('vat-reports.index') }}" class="btn btn-outline-secondary filter-btn">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- VAT Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="main-card">
                <div class="card-header bg-white p-0" style="border-radius:10px 10px 0 0; border-bottom:1px solid #f0f0f0;">
                    <ul class="nav nav-tabs border-0" id="vatTab">
                        <li class="nav-item">
                            <a class="nav-link active px-4 py-3 font-weight-bold" data-toggle="tab" href="#outputVatTab">
                                <i class="fas fa-arrow-up mr-1"></i> Output VAT (Sales)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-4 py-3 text-muted" data-toggle="tab" href="#inputVatTab">
                                <i class="fas fa-arrow-down mr-1"></i> Input VAT (Purchases)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-4 py-3 text-muted" data-toggle="tab" href="#expenseVatTab">
                                <i class="fas fa-receipt mr-1"></i> VAT on Expenses
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body tab-content p-0">

                    <!-- Output VAT Tab -->
                    <div class="tab-pane active" id="outputVatTab">
                        <div class="table-responsive">
                            <table class="table vat-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-4">SL.</th>
                                        <th>Date</th>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Total Amount</th>
                                        <th>Taxable Amount</th>
                                        <th>VAT Amount</th>
                                        <th>VAT Rate</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vatReport as $index => $vat)
                                        <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                            <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($vat['date'])->format('d M, Y') }}</td>
                                            <td><span class="invoice-badge">{{ $vat['invoice'] }}</span></td>
                                            <td>
                                                <div class="customer-info">
                                                    <span class="customer-avatar">{{ strtoupper(substr($vat['customer'], 0, 1)) }}</span>
                                                    <div>
                                                        <span class="customer-name">{{ $vat['customer'] }}</span>
                                                        <small class="customer-id">ID: {{ $vat['customer_id'] }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fw-semibold">{{ number_format($vat['total_amount'], 2) }}</td>
                                            <td class="fw-semibold">{{ number_format($vat['taxable_amount'], 2) }}</td>
                                            <td class="fw-semibold text-success">{{ number_format($vat['vat_amount'], 2) }}</td>
                                            <td><span class="rate-badge">{{ $vat['vat_rate'] }}%</span></td>
                                            <td>
                                                <span class="status-badge {{ $vat['payment_status'] }}">
                                                    {{ ucfirst($vat['payment_status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 empty-state">
                                                <i class="fas fa-arrow-up fa-3x mb-3 text-muted"></i>
                                                <p class="text-muted mb-0">No output VAT transactions found for the selected period.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Input VAT Tab -->
                    <div class="tab-pane" id="inputVatTab">
                        <div class="table-responsive">
                            <table class="table vat-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-4">SL.</th>
                                        <th>Date</th>
                                        <th>Invoice</th>
                                        <th>Supplier</th>
                                        <th>Total Amount</th>
                                        <th>VAT Amount</th>
                                        <th>VAT Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchaseVat['purchases'] as $index => $purchase)
                                        <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                            <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($purchase['date'])->format('d M, Y') }}</td>
                                            <td><span class="invoice-badge">{{ $purchase['invoice'] }}</span></td>
                                            <td>
                                                <div class="supplier-info">
                                                    <span class="supplier-avatar">{{ strtoupper(substr($purchase['supplier'], 0, 1)) }}</span>
                                                    <span class="supplier-name">{{ $purchase['supplier'] }}</span>
                                                </div>
                                            </td>
                                            <td class="fw-semibold">{{ number_format($purchase['total_amount'], 2) }}</td>
                                            <td class="fw-semibold text-danger">{{ number_format($purchase['vat_amount'], 2) }}</td>
                                            <td><span class="type-badge input-vat">Input VAT</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 empty-state">
                                                <i class="fas fa-arrow-down fa-3x mb-3 text-muted"></i>
                                                <p class="text-muted mb-0">No input VAT transactions found for the selected period.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Expense VAT Tab -->
                    <div class="tab-pane" id="expenseVatTab">
                        <div class="table-responsive">
                            <table class="table vat-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-4">SL.</th>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>VAT Amount</th>
                                        <th>VAT Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenseVat['expenses'] as $index => $expense)
                                        <tr class="table-row-animate" style="animation-delay: {{ $index * 0.05 }}s">
                                            <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($expense['date'])->format('d M, Y') }}</td>
                                            <td><span class="category-badge">{{ $expense['category'] }}</span></td>
                                            <td>{{ $expense['description'] }}</td>
                                            <td class="fw-semibold">{{ number_format($expense['amount'], 2) }}</td>
                                            <td class="fw-semibold text-warning">{{ number_format($expense['vat_amount'], 2) }}</td>
                                            <td><span class="type-badge expense-vat">Expense VAT</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 empty-state">
                                                <i class="fas fa-receipt fa-3x mb-3 text-muted"></i>
                                                <p class="text-muted mb-0">No expense VAT transactions found for the selected period.</p>
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
    .title-icon { color: #8b5cf6; }
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
    .output-vat-card { background: #f0fdf4; border-color: #bbf7d0; }
    .input-vat-card  { background: #fef3c7; border-color: #fed7aa; }
    .net-vat-card    { background: #f3e8ff; border-color: #ddd6fe; }
    .taxable-card    { background: #ecfdf5; border-color: #a7f3d0; }

    .summary-icon-wrap {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .output-vat-icon-bg { background: #86efac; color: #14532d; }
    .input-vat-icon-bg  { background: #fcd34d; color: #92400e; }
    .net-vat-icon-bg    { background: #c4b5fd; color: #5b21b6; }
    .taxable-icon-bg    { background: #34d399; color: #065f46; }

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

    /* ========== Tabs ========== */
    .nav-tabs .nav-link {
        border: none !important;
        border-radius: 0 !important;
        font-size: 0.85rem !important;
        color: #6b7280 !important;
        transition: all 0.2s;
    }
    .nav-tabs .nav-link.active {
        color: #8b5cf6 !important;
        border-bottom: 2px solid #8b5cf6 !important;
        background: transparent !important;
    }
    .nav-tabs .nav-link:hover {
        color: #8b5cf6 !important;
    }

    /* ========== Table ========== */
    .vat-table { font-size: 0.875rem; }
    .vat-table thead tr { background: #f8fafc; }
    .vat-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        font-weight: 700;
        border: none !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .vat-table tbody td {
        vertical-align: middle !important;
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 14px;
        color: #374151;
    }
    .vat-table tbody tr { transition: background 0.15s; }
    .vat-table tbody tr:hover { background: #fafbff !important; }

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
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .customer-name { display: block; font-weight: 500; color: #111827; font-size: 0.875rem; }
    .customer-id { display: block; color: #9ca3af; font-size: 0.75rem; font-family: 'DM Mono', monospace; }

    /* Supplier */
    .supplier-info { display: flex; align-items: center; gap: 10px; }
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
    .supplier-name { font-weight: 500; color: #111827; font-size: 0.875rem; }

    /* Badges */
    .rate-badge, .category-badge, .type-badge {
        font-family: 'DM Mono', monospace;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .rate-badge { background: #e0e7ff; color: #3730a3; }
    .category-badge { background: #f3f4f6; color: #374151; }
    .type-badge { background: #fef3c7; color: #92400e; }
    .input-vat { background: #fee2e2; color: #991b1b; }
    .expense-vat { background: #fef3c7; color: #92400e; }

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
        .vat-table { font-size: 0.8rem; }
        .vat-table thead th { font-size: 0.65rem; }
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
