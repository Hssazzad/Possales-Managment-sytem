@php
use Carbon\Carbon;
@endphp

@extends('adminlte::page')

@section('title', 'Profit & Loss | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold page-title">
        <i class="fas fa-chart-line mr-2 title-icon"></i> Profit & Loss
    </h1>
    <span class="date-badge">
        <i class="far fa-calendar-alt mr-1"></i> {{ now()->format('l, d F Y') }}
    </span>
</div>
@stop

@section('content')

<!-- Filters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="filter-card">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('profit-loss.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="filter-label">Year</label>
                            <select name="year" class="form-control form-control-sm custom-select-styled">
                                @for($y = 2026; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="filter-label">Month <span class="text-muted">(Optional)</span></label>
                            <select name="month" class="form-control form-control-sm custom-select-styled">
                                <option value="">All Months</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12 mb-2 d-flex align-items-end" style="gap:8px;">
                            <button type="submit" class="btn btn-primary btn-sm filter-btn">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('profit-loss.index') }}" class="btn btn-outline-secondary btn-sm filter-btn">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <!-- Loss Card -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="summary-card loss-card">
            <div class="summary-icon-wrap loss-icon-bg">
                <i class="fas fa-arrow-trend-down"></i>
            </div>
            <div class="summary-info">
                <p class="summary-label">Total Loss</p>
                <h4 class="summary-value">
                    @php
                        $totalLoss = collect($profitLossDetails)->where('type', 'loss')->sum('profit');
                        $totalLoss = $totalLoss < 0 ? abs($totalLoss) : 0;
                    @endphp
                    ৳{{ number_format($totalLoss, 2) }}
                </h4>
            </div>
        </div>
    </div>

    <!-- Profit Card -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="summary-card profit-card">
            <div class="summary-icon-wrap profit-icon-bg">
                <i class="fas fa-arrow-trend-up"></i>
            </div>
            <div class="summary-info">
                <p class="summary-label">Total Profit</p>
                <h4 class="summary-value">
                    ৳{{ number_format($netProfitLoss >= 0 ? $netProfitLoss : 0, 2) }}
                </h4>
            </div>
        </div>
    </div>

    <!-- Total Sales Count Card -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="summary-card sales-card">
            <div class="summary-icon-wrap sales-icon-bg">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="summary-info">
                <p class="summary-label">Total Sales</p>
                <h4 class="summary-value">
                    {{ count($profitLossDetails) }}
                </h4>
            </div>
        </div>
    </div>

    <!-- Total Amount Card -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="summary-card amount-card">
            <div class="summary-icon-wrap amount-icon-bg">
                <i class="fas fa-coins"></i>
            </div>
            <div class="summary-info">
                <p class="summary-label">Total Amount</p>
                <h4 class="summary-value">
                    ৳{{ number_format($totalSales, 2) }}
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="main-card">
    <div class="card-body p-0">

        <!-- Table Top Controls -->
        <div class="table-controls d-flex align-items-center justify-content-between flex-wrap px-3 pt-3 pb-2" style="gap:10px;">
            <div class="d-flex align-items-center" style="gap:6px;">
                <span class="control-label">Show</span>
                <select class="form-control form-control-sm per-page-select" id="perPageSelect" onchange="changePerPage(this.value)">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="control-label">entries</span>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                <div class="search-wrap">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="tableSearch" class="form-control form-control-sm search-input" placeholder="Search transactions...">
                </div>
                <div class="d-flex" style="gap:6px;">
                    <button class="export-btn excel-btn" title="Export Excel" onclick="exportTable('excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="export-btn csv-btn" title="Export CSV" onclick="exportTable('csv')">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                    <button class="export-btn print-btn" title="Print" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="table-responsive">
            <table class="table table-hover profit-table mb-0" id="profitLossTable">
                <thead>
                    <tr>
                        <th class="pl-4">SL.</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>P / L</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($profitLossDetails as $index => $detail)
                    <tr class="table-row-animate" style="animation-delay: {{ $index * 0.03 }}s">
                        <td class="pl-4 text-muted" style="font-weight:600;">{{ $index + 1 }}</td>
                        <td>
                            <span class="invoice-badge">{{ $detail['invoice'] }}</span>
                        </td>
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
                        <td class="fw-semibold">৳{{ number_format($detail['sales_amount'], 2) }}</td>
                        <td>
                            @if($detail['type'] == 'profit')
                                <span class="pl-badge profit-badge">
                                    <i class="fas fa-caret-up mr-1"></i>৳{{ number_format(abs($detail['profit']), 2) }}
                                </span>
                            @else
                                <span class="pl-badge loss-badge">
                                    <i class="fas fa-caret-down mr-1"></i>৳{{ number_format(abs($detail['profit']), 2) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-muted">{{ \Carbon\Carbon::parse($detail['date'])->format('d M, Y') }}</td>
                        <td>
                            @if($detail['payment_status'] == 'paid')
                                <span class="status-pill status-paid">Paid</span>
                            @elseif($detail['payment_status'] == 'partial')
                                <span class="status-pill status-partial">Partial</span>
                            @elseif($detail['payment_status'] == 'unpaid')
                                <span class="status-pill status-unpaid">Unpaid</span>
                            @else
                                <span class="status-pill status-other">{{ ucfirst($detail['payment_status']) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="action-dots" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right action-menu">
                                    <a class="dropdown-item action-item" href="#">
                                        <i class="fas fa-eye text-primary"></i> View Details
                                    </a>
                                    <a class="dropdown-item action-item" href="#">
                                        <i class="fas fa-print text-secondary"></i> Print Invoice
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 empty-state">
                            <i class="fas fa-chart-bar fa-3x mb-3 text-muted"></i>
                            <p class="text-muted mb-0">No transactions found for the selected period.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer Info -->
        <div class="px-3 py-2 table-footer d-flex justify-content-between align-items-center flex-wrap" style="gap:6px;">
            <span class="text-muted" style="font-size:0.82rem;" id="showingInfo">
                Showing <strong>1</strong> to <strong>{{ min(10, count($profitLossDetails)) }}</strong> of <strong>{{ count($profitLossDetails) }}</strong> entries
            </span>
            <span style="font-size:0.82rem; color:#6c757d;">
                Net: <strong class="{{ $netProfitLoss >= 0 ? 'text-success' : 'text-danger' }}">
                    ৳{{ number_format(abs($netProfitLoss), 2) }} {{ $netProfitLoss >= 0 ? 'Profit' : 'Loss' }}
                </strong>
            </span>
        </div>

    </div>
</div>

@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    /* ========== Base ========== */
    body, .content-wrapper {
        background: #f0f2f5 !important;
        font-family: 'DM Sans', sans-serif !important;
    }

    /* ========== Sidebar ========== */
    .main-sidebar, .main-sidebar::before { background: #1a1d23 !important; }
    .brand-link { background: #141720 !important; border-bottom: 1px solid #2d3139 !important; }
    .brand-link .brand-text { color: #fff !important; font-weight: 700 !important; }
    .nav-sidebar .nav-item > .nav-link { color: #8b92a5 !important; font-size: 0.875rem !important; transition: all 0.2s; border-radius: 6px !important; margin: 1px 8px !important; }
    .nav-sidebar .nav-item > .nav-link:hover { background: rgba(99,102,241,0.12) !important; color: #fff !important; }
    .nav-sidebar .nav-item > .nav-link.active { background: #6366f1 !important; color: #fff !important; }
    .nav-sidebar .nav-treeview .nav-link { color: #6b7280 !important; font-size: 0.82rem !important; }
    .nav-sidebar .nav-treeview .nav-link:hover { color: #fff !important; background: rgba(255,255,255,0.06) !important; }
    .nav-sidebar .nav-treeview .nav-link.active { color: #818cf8 !important; background: transparent !important; }

    /* ========== Header ========== */
    .main-header { border-bottom: 1px solid #e5e7eb !important; box-shadow: 0 1px 6px rgba(0,0,0,0.05) !important; background: #fff !important; }
    .page-title { color: #111827; font-size: 1.25rem; }
    .title-icon { color: #6366f1; }
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
    .loss-card   { background: #fff1f2; border-color: #fecdd3; }
    .profit-card { background: #f0fdf4; border-color: #bbf7d0; }
    .sales-card  { background: #fffbeb; border-color: #fed7aa; }
    .amount-card { background: #f5f3ff; border-color: #ddd6fe; }

    .summary-icon-wrap {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .loss-icon-bg   { background: #fda4af; color: #9f1239; }
    .profit-icon-bg { background: #86efac; color: #14532d; }
    .sales-icon-bg  { background: #fcd34d; color: #78350f; }
    .amount-icon-bg { background: #c4b5fd; color: #3b0764; }

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

    /* ========== Main Table Card ========== */
    .main-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* ========== Table Controls ========== */
    .control-label { font-size: 0.82rem; color: #6b7280; }
    .per-page-select {
        width: 70px !important;
        border-radius: 8px !important;
        border: 1px solid #d1d5db !important;
        font-size: 0.82rem !important;
    }
    .search-wrap { position: relative; }
    .search-icon {
        position: absolute; left: 10px; top: 50%;
        transform: translateY(-50%);
        color: #9ca3af; font-size: 0.75rem; z-index: 2;
    }
    .search-input {
        padding-left: 30px !important;
        border-radius: 8px !important;
        border: 1px solid #d1d5db !important;
        width: 220px;
        font-size: 0.82rem !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .search-input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
    }
    .export-btn {
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .excel-btn { color: #16a34a; border-color: #86efac; }
    .excel-btn:hover { background: #f0fdf4; }
    .csv-btn { color: #6b7280; border-color: #d1d5db; }
    .csv-btn:hover { background: #f9fafb; }
    .print-btn { color: #dc2626; border-color: #fca5a5; }
    .print-btn:hover { background: #fff1f2; }

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

    /* Invoice badge */
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

    /* P/L badges */
    .pl-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        font-family: 'DM Mono', monospace;
    }
    .profit-badge { background: #dcfce7; color: #15803d; }
    .loss-badge   { background: #fee2e2; color: #dc2626; }

    /* Status pills */
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
    .status-other   { background: #e0f2fe; color: #075985; }

    /* Action dots */
    .action-dots {
        background: none;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-dots:hover { background: #f3f4f6; border-color: #d1d5db; color: #374151; }
    .action-menu {
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
        min-width: 160px;
        padding: 6px !important;
    }
    .action-item {
        border-radius: 6px !important;
        font-size: 0.82rem !important;
        padding: 7px 12px !important;
        color: #374151 !important;
        transition: background 0.15s;
    }
    .action-item:hover { background: #f3f4f6 !important; }
    .action-item i { width: 16px; }

    /* Empty state */
    .empty-state { color: #9ca3af; }
    .empty-state i { display: block; margin: 0 auto 10px; }

    /* Table footer */
    .table-footer {
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
    }

    /* ========== Scrollbar ========== */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: #f9fafb; }
    ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }

    /* ========== Print ========== */
    @media print {
        .main-sidebar, .main-header, .content-header,
        .filter-card, .table-controls, .export-btn,
        .action-dots, .table-footer { display: none !important; }
        body, .content-wrapper { background: #fff !important; }
        .main-card { box-shadow: none !important; border: none !important; }
        .profit-table thead tr { background: #f3f4f6 !important; }
    }

    /* ========== Responsive ========== */
    @media (max-width: 576px) {
        .summary-value { font-size: 1.1rem; }
        .search-input { width: 160px; }
    }
</style>
@stop

@section('js')
<script>
    const allRows = Array.from(document.querySelectorAll('#tableBody tr'));
    let currentPerPage = 10;
    let currentSearch = '';

    function getFilteredRows() {
        return allRows.filter(row => row.textContent.toLowerCase().includes(currentSearch));
    }

    function renderRows() {
        const filtered = getFilteredRows();
        allRows.forEach(r => r.style.display = 'none');
        filtered.slice(0, currentPerPage).forEach(r => r.style.display = '');

        const total = allRows.length;
        const showing = Math.min(currentPerPage, filtered.length);
        document.getElementById('showingInfo').innerHTML =
            `Showing <strong>1</strong> to <strong>${showing}</strong> of <strong>${total}</strong> entries` +
            (currentSearch ? ` (filtered from ${total} total)` : '');
    }

    // Live search
    document.getElementById('tableSearch').addEventListener('input', function () {
        currentSearch = this.value.toLowerCase().trim();
        renderRows();
    });

    // Per page
    function changePerPage(val) {
        currentPerPage = parseInt(val);
        renderRows();
    }

    // Export placeholder
    function exportTable(type) {
        alert('Export as ' + type.toUpperCase() + ' — connect to your backend export route here.');
    }

    // Initial render
    renderRows();
</script>
@stop
